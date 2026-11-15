require('dotenv').config();
const argon2 = require('argon2');
const { Pool } = require('pg');

async function main() {
  const { DATABASE_URL, STAFF_EMAIL, STAFF_NAME, STAFF_PASSWORD } = process.env;
  if (!DATABASE_URL || !STAFF_EMAIL || !STAFF_NAME || !STAFF_PASSWORD) {
    throw new Error('Set DATABASE_URL, STAFF_EMAIL, STAFF_NAME, and STAFF_PASSWORD in the environment.');
  }
  if (STAFF_PASSWORD.length < 14) throw new Error('STAFF_PASSWORD must be at least 14 characters.');
  const email = STAFF_EMAIL.trim().toLowerCase();
  const fullName = STAFF_NAME.trim();
  if (!email.includes('@') || !fullName || fullName.length > 200) throw new Error('Provide a valid staff email and name.');

  const pool = new Pool({ connectionString: DATABASE_URL });
  try {
    const passwordHash = await argon2.hash(STAFF_PASSWORD, { type: argon2.argon2id });
    const client = await pool.connect();
    try {
      await client.query('BEGIN');
      const result = await client.query(
        `INSERT INTO users (email, full_name, account_kind, password_hash)
         VALUES ($1, $2, 'staff', $3)
         ON CONFLICT (email) DO NOTHING
         RETURNING id`,
        [email, fullName, passwordHash]
      );
      if (!result.rowCount) throw new Error('That email already exists; no account or role was changed.');
      await client.query("INSERT INTO user_staff_roles (user_id, role) VALUES ($1, 'admin')", [result.rows[0].id]);
      await client.query('COMMIT');
      console.log(`Created initial staff administrator: ${email}`);
    } catch (error) {
      await client.query('ROLLBACK').catch(() => {});
      throw error;
    } finally { client.release(); }
  } finally { await pool.end(); }
}

main().catch((error) => {
  console.error('Staff account setup failed:', error.message);
  process.exitCode = 1;
});
