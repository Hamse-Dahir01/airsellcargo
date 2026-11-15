const crypto = require('node:crypto');
const path = require('node:path');
const express = require('express');
const helmet = require('helmet');
const session = require('express-session');
const connectPgSimple = require('connect-pg-simple');
const { rateLimit } = require('express-rate-limit');
const { Pool } = require('pg');
require('dotenv').config();

const requiredEnv = ['DATABASE_URL', 'SESSION_SECRET'];
for (const name of requiredEnv) {
  if (!process.env[name]) throw new Error(`${name} must be configured before starting the API.`);
}
if (process.env.SESSION_SECRET.length < 32) {
  throw new Error('SESSION_SECRET must contain at least 32 characters.');
}

const app = express();
const pool = new Pool({ connectionString: process.env.DATABASE_URL });
const port = Number(process.env.PORT || 3000);
const rolesByKind = {
  sales: ['sales', 'admin'],
  operations: ['operations', 'admin'],
  documentation: ['documentation', 'operations', 'admin'],
  finance: ['finance', 'admin'],
  admin: ['admin']
};

if (process.env.TRUST_PROXY === '1') app.set('trust proxy', 1);
app.disable('x-powered-by');
app.use(helmet({ contentSecurityPolicy: false }));
app.use(express.json({ limit: '100kb', type: 'application/json' }));

const PgSession = connectPgSimple(session);
app.use(session({
  name: 'airsell.sid',
  store: new PgSession({ pool, createTableIfMissing: true, tableName: 'user_sessions' }),
  secret: process.env.SESSION_SECRET,
  resave: false,
  saveUninitialized: false,
  rolling: true,
  cookie: {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'strict',
    maxAge: 8 * 60 * 60 * 1000
  }
}));

const apiLimiter = rateLimit({ windowMs: 15 * 60 * 1000, limit: 120, standardHeaders: 'draft-7', legacyHeaders: false });
const publicEnquiryLimiter = rateLimit({ windowMs: 15 * 60 * 1000, limit: 5, standardHeaders: 'draft-7', legacyHeaders: false, message: { error: 'Too many requests. Please try again later.' } });
const loginLimiter = rateLimit({ windowMs: 15 * 60 * 1000, limit: 8, standardHeaders: 'draft-7', legacyHeaders: false, message: { error: 'Too many sign-in attempts. Please try again later.' } });
app.use('/api/', apiLimiter);

function checkSameOrigin(req, res, next) {
  const origin = req.get('origin');
  if (!origin) return next(); // Non-browser clients may omit Origin; authentication and CSRF checks still apply.
  let received;
  try { received = new URL(origin); } catch { return res.status(403).json({ error: 'Invalid request origin.' }); }
  const expected = process.env.APP_ORIGIN
    ? new URL(process.env.APP_ORIGIN)
    : new URL(`${req.protocol}://${req.get('host')}`);
  if (received.origin !== expected.origin) return res.status(403).json({ error: 'Cross-origin request rejected.' });
  next();
}

function newReference(prefix) {
  return `${prefix}-${crypto.randomBytes(5).toString('hex').toUpperCase()}`;
}

function safeText(value, maxLength) {
  if (typeof value !== 'string') return null;
  const normalized = value.trim();
  return normalized && normalized.length <= maxLength ? normalized : null;
}

function requireLogin(req, res, next) {
  if (!req.session.userId) return res.status(401).json({ error: 'Authentication required.' });
  next();
}

async function loadStaff(req, res, next) {
  try {
    const result = await pool.query(
      `SELECT u.id, u.email, u.full_name, u.account_kind, u.is_active,
              COALESCE(array_agg(r.role) FILTER (WHERE r.role IS NOT NULL), '{}') AS roles
         FROM users u LEFT JOIN user_staff_roles r ON r.user_id = u.id
        WHERE u.id = $1 GROUP BY u.id`,
      [req.session.userId]
    );
    const user = result.rows[0];
    if (!user || !user.is_active || user.account_kind !== 'staff' || user.roles.length === 0) {
      return res.status(403).json({ error: 'Staff access required.' });
    }
    req.staff = user;
    next();
  } catch (error) {
    next(error);
  }
}

function requireRoles(...allowedRoles) {
  return (req, res, next) => {
    if (!req.staff || !allowedRoles.some((role) => req.staff.roles.includes(role))) {
      return res.status(403).json({ error: 'You do not have permission to perform this action.' });
    }
    next();
  };
}

function requireCsrf(req, res, next) {
  const token = req.get('x-csrf-token');
  const expected = req.session.csrfToken;
  if (typeof token !== 'string' || typeof expected !== 'string') {
    return res.status(403).json({ error: 'A valid CSRF token is required.' });
  }
  const providedBuffer = Buffer.from(token);
  const expectedBuffer = Buffer.from(expected);
  if (providedBuffer.length !== expectedBuffer.length || !crypto.timingSafeEqual(providedBuffer, expectedBuffer)) {
    return res.status(403).json({ error: 'A valid CSRF token is required.' });
  }
  next();
}

app.get('/api/v1/health', async (_req, res, next) => {
  try {
    await pool.query('SELECT 1');
    res.json({ ok: true, service: 'Airsell Cargo API' });
  } catch (error) { next(error); }
});

app.post('/api/v1/auth/login', checkSameOrigin, loginLimiter, async (req, res, next) => {
  try {
    const email = safeText(req.body?.email, 320)?.toLowerCase();
    const password = typeof req.body?.password === 'string' ? req.body.password : '';
    if (!email || password.length < 1 || password.length > 200) {
      return res.status(400).json({ error: 'Email and password are required.' });
    }
    const result = await pool.query(
      `SELECT u.id, u.email, u.full_name, u.password_hash, u.account_kind, u.is_active,
              COALESCE(array_agg(r.role) FILTER (WHERE r.role IS NOT NULL), '{}') AS roles
         FROM users u LEFT JOIN user_staff_roles r ON r.user_id = u.id
        WHERE u.email = $1 GROUP BY u.id`,
      [email]
    );
    const user = result.rows[0];
    let validPassword = false;
    if (user?.password_hash && user.is_active && user.account_kind === 'staff') {
      const argon2 = require('argon2');
      validPassword = await argon2.verify(user.password_hash, password);
    }
    if (!validPassword || user.roles.length === 0) {
      return res.status(401).json({ error: 'Invalid email or password.' });
    }
    await new Promise((resolve, reject) => req.session.regenerate((error) => error ? reject(error) : resolve()));
    req.session.userId = user.id;
    req.session.csrfToken = crypto.randomBytes(32).toString('base64url');
    res.json({ user: { id: user.id, email: user.email, name: user.full_name, roles: user.roles }, csrfToken: req.session.csrfToken });
  } catch (error) { next(error); }
});

app.get('/api/v1/auth/me', requireLogin, loadStaff, (req, res) => {
  res.json({ user: { id: req.staff.id, email: req.staff.email, name: req.staff.full_name, roles: req.staff.roles }, csrfToken: req.session.csrfToken || null });
});

app.post('/api/v1/auth/logout', checkSameOrigin, requireLogin, requireCsrf, (req, res, next) => {
  req.session.destroy((error) => {
    if (error) return next(error);
    res.clearCookie('airsell.sid', { httpOnly: true, secure: process.env.NODE_ENV === 'production', sameSite: 'strict' });
    res.status(204).end();
  });
});

app.post('/api/v1/enquiries', checkSameOrigin, publicEnquiryLimiter, async (req, res, next) => {
  try {
    const body = req.body || {};
    const contactName = safeText(body.contactName, 200);
    const companyName = typeof body.companyName === 'string' ? body.companyName.trim().slice(0, 200) : null;
    const email = safeText(body.email, 320)?.toLowerCase();
    const phone = safeText(body.phone, 80);
    const direction = ['export', 'import', 'other'].includes(body.direction) ? body.direction : null;
    const service = safeText(body.service, 120);
    const origin = safeText(body.origin, 200);
    const destination = safeText(body.destination, 200);
    const cargoDescription = safeText(body.cargoDescription, 2000);
    const notes = typeof body.notes === 'string' ? body.notes.trim().slice(0, 5000) : null;
    const weightKg = body.weightKg === '' || body.weightKg == null ? null : Number(body.weightKg);
    if (!contactName || !email || !email.includes('@') || !phone || !direction || !service || !origin || !destination || !cargoDescription || body.consentGiven !== true || (weightKg !== null && (!Number.isFinite(weightKg) || weightKg <= 0))) {
      return res.status(400).json({ error: 'Please provide valid required shipment details and consent.' });
    }
    const reference = newReference('ASQ');
    const result = await pool.query(
      `INSERT INTO enquiries
        (public_reference, contact_name, company_name, email, phone, direction, requested_service, origin, destination, cargo_description, weight_kg, notes, consent_recorded_at)
       VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,now())
       RETURNING id, public_reference, status, created_at`,
      [reference, contactName, companyName, email, phone, direction, service, origin, destination, cargoDescription, weightKg, notes]
    );
    const enquiry = result.rows[0];
    await pool.query(`INSERT INTO audit_events (event_type, enquiry_id, details) VALUES ('enquiry_created', $1, $2)`, [enquiry.id, JSON.stringify({ publicReference: reference })]);
    res.status(201).json({ message: 'Enquiry received. Airsell Cargo will review your request and contact you.', reference: enquiry.public_reference, status: enquiry.status });
  } catch (error) { next(error); }
});

app.get('/api/v1/staff/enquiries', requireLogin, loadStaff, requireRoles('sales', 'operations', 'admin'), async (_req, res, next) => {
  try {
    const result = await pool.query(
      `SELECT id, public_reference, contact_name, company_name, email, phone, direction, requested_service,
              origin, destination, cargo_description, weight_kg, notes, status, created_at
         FROM enquiries ORDER BY created_at DESC LIMIT 200`
    );
    res.json({ enquiries: result.rows });
  } catch (error) { next(error); }
});

app.post('/api/v1/staff/enquiries/:id/quotes', checkSameOrigin, requireLogin, loadStaff, requireRoles('sales', 'admin'), requireCsrf, async (req, res, next) => {
  const client = await pool.connect();
  try {
    const totalAmount = Number(req.body?.totalAmount);
    const currency = safeText(req.body?.currency, 3)?.toUpperCase();
    if (!Number.isFinite(totalAmount) || totalAmount < 0 || !currency || !/^[A-Z]{3}$/.test(currency)) {
      return res.status(400).json({ error: 'Provide a valid amount and 3-letter currency code.' });
    }
    await client.query('BEGIN');
    const enquiryResult = await client.query('SELECT id, status FROM enquiries WHERE id = $1 FOR UPDATE', [req.params.id]);
    if (!enquiryResult.rowCount) {
      await client.query('ROLLBACK');
      return res.status(404).json({ error: 'Enquiry not found.' });
    }
    const versionResult = await client.query('SELECT COALESCE(MAX(version), 0) + 1 AS version FROM quotes WHERE enquiry_id = $1', [req.params.id]);
    const now = new Date();
    const quoteResult = await client.query(
      `INSERT INTO quotes (enquiry_id, version, status, currency, total_amount, inclusions, exclusions, terms, valid_until, created_by, sent_at)
       VALUES ($1,$2,'sent',$3,$4,$5,$6,$7,$8,$9,$10)
       RETURNING id, enquiry_id, version, status, currency, total_amount, valid_until, sent_at`,
      [req.params.id, versionResult.rows[0].version, currency, totalAmount,
        safeText(req.body?.inclusions, 5000), safeText(req.body?.exclusions, 5000), safeText(req.body?.terms, 5000),
        req.body?.validUntil || null, req.staff.id, now]
    );
    await client.query("UPDATE enquiries SET status = 'quoted', updated_at = now() WHERE id = $1", [req.params.id]);
    await client.query("INSERT INTO audit_events (event_type, actor_user_id, enquiry_id, quote_id) VALUES ('quote_sent', $1, $2, $3)", [req.staff.id, req.params.id, quoteResult.rows[0].id]);
    await client.query('COMMIT');
    res.status(201).json({ quote: quoteResult.rows[0] });
  } catch (error) {
    await client.query('ROLLBACK').catch(() => {});
    next(error);
  } finally { client.release(); }
});

app.get('/api/v1/staff/shipments', requireLogin, loadStaff, requireRoles('operations', 'documentation', 'admin'), async (_req, res, next) => {
  try {
    const result = await pool.query(
      `SELECT id, public_reference, organization_id, direction, origin, destination, cargo_description, weight_kg,
              status, estimated_departure, estimated_arrival, created_at
         FROM shipments ORDER BY created_at DESC LIMIT 200`
    );
    res.json({ shipments: result.rows });
  } catch (error) { next(error); }
});

app.patch('/api/v1/staff/shipments/:id/status', checkSameOrigin, requireLogin, loadStaff, requireRoles('operations', 'admin'), requireCsrf, async (req, res, next) => {
  const allowedStatuses = ['booked', 'pickup_scheduled', 'received_at_hga', 'documents_review', 'customs_processing', 'ready_for_dispatch', 'departed', 'arrived', 'delivery_in_progress', 'delivered', 'on_hold', 'delayed', 'cancelled', 'completed'];
  const status = req.body?.status;
  if (!allowedStatuses.includes(status)) return res.status(400).json({ error: 'Invalid shipment status.' });
  const client = await pool.connect();
  try {
    await client.query('BEGIN');
    const updated = await client.query('UPDATE shipments SET status = $1, updated_at = now() WHERE id = $2 RETURNING id, public_reference, status', [status, req.params.id]);
    if (!updated.rowCount) {
      await client.query('ROLLBACK');
      return res.status(404).json({ error: 'Shipment not found.' });
    }
    const customerMessage = safeText(req.body?.customerMessage, 1000);
    const internalNote = safeText(req.body?.internalNote, 3000);
    await client.query(
      `INSERT INTO shipment_events (shipment_id, status, customer_message, internal_note, is_customer_visible, created_by)
       VALUES ($1,$2,$3,$4,$5,$6)`,
      [req.params.id, status, customerMessage, internalNote, Boolean(customerMessage), req.staff.id]
    );
    await client.query("INSERT INTO audit_events (event_type, actor_user_id, shipment_id, details) VALUES ('shipment_status_changed', $1, $2, $3)", [req.staff.id, req.params.id, JSON.stringify({ status })]);
    await client.query('COMMIT');
    res.json({ shipment: updated.rows[0] });
  } catch (error) {
    await client.query('ROLLBACK').catch(() => {});
    next(error);
  } finally { client.release(); }
});

// Publish only these website files, not the application directory.
const publicFiles = {
  '/index.html': 'index.html',
  '/styles.css': 'styles.css',
  '/app.js': 'app.js',
  '/admin.html': 'admin.html',
  '/dashboard.html': 'dashboard.html'
};

for (const [route, filename] of Object.entries(publicFiles)) {
  app.get(route, (_req, res, next) => {
    res.sendFile(path.join(__dirname, filename), (error) => {
      if (error) next(error);
    });
  });
}
app.get('/', (_req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

app.use('/api', (_req, res) => {
  res.status(404).json({ error: 'API route not found.' });
});
// Show the homepage at the website's root address.
app.get('/', (_req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});


const server = app.listen(port, () => console.log(`Airsell Cargo API listening on port ${port}`));
async function shutdown() {
  server.close(async () => {
    await pool.end();
    process.exit(0);
  });
}
process.on('SIGTERM', shutdown);
process.on('SIGINT', shutdown);
