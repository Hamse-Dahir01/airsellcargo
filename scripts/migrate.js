require('dotenv').config();
const { Pool } = require('pg');
const fs = require('node:fs/promises');
const path = require('node:path');

async function main() {
  if (!process.env.DATABASE_URL) throw new Error('DATABASE_URL must be configured.');
  const sql = await fs.readFile(path.join(__dirname, '..', 'docs', 'database-schema.sql'), 'utf8');
  const pool = new Pool({ connectionString: process.env.DATABASE_URL });
  try {
    await pool.query(sql);
    console.log('Initial Airsell Cargo schema applied.');
  } finally {
    await pool.end();
  }
}

main().catch((error) => {
  console.error('Migration failed:', error.message);
  process.exitCode = 1;
});
