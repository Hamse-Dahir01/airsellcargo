# Airsell Cargo portal

Responsive front-end prototype and an initial PostgreSQL-backed Express API for Airsell Cargo Services, Hargeisa Airport (HGA), Somaliland.

## Included

- `index.html` — public website and shipment enquiry form
- `dashboard.html` and `admin.html` — illustrative dashboard mockups (not protected, not real data)
- `server.js` — same-origin API with PostgreSQL persistence, staff session login, CSRF checks, role-gated staff routes, and rate limits
- `docs/database-schema.sql` — initial PostgreSQL schema
- `scripts/migrate.js` — applies the initial schema
- `scripts/create-staff.js` — creates the first staff administrator

## Local setup

Requirements: Node.js 20+ and PostgreSQL 14+.

1. Create a PostgreSQL database and user with permission to create the schema objects (including `pgcrypto`).
2. Copy `.env.example` to `.env`, then set `DATABASE_URL`, a random `SESSION_SECRET` of at least 32 characters, and `APP_ORIGIN` to the exact origin where the app will be served.
3. Install packages: `npm install`.
4. Apply the initial schema: `npm run db:migrate`.
5. Create the first staff administrator by setting `STAFF_EMAIL`, `STAFF_NAME`, and `STAFF_PASSWORD` (minimum 14 characters) in the environment and running `npm run staff:create`. Do not commit credentials or put them in source control.
6. Start the server: `npm run dev`, then open `http://localhost:3000`.

For production behind one trusted reverse proxy, set `NODE_ENV=production` and `TRUST_PROXY=1` only if exactly one trusted proxy is in front of the app. HTTPS is required so secure session cookies work.

## API routes

- `GET /api/v1/health` — database health check
- `POST /api/v1/auth/login` — staff login; returns session cookie and CSRF token
- `GET /api/v1/auth/me` — current staff session
- `POST /api/v1/auth/logout` — logout; send `x-csrf-token`
- `POST /api/v1/enquiries` — public, rate-limited enquiry submission
- `GET /api/v1/staff/enquiries` — sales/operations/admin session required
- `POST /api/v1/staff/enquiries/:id/quotes` — sales/admin session and CSRF token required
- `GET /api/v1/staff/shipments` — operations/documentation/admin session required
- `PATCH /api/v1/staff/shipments/:id/status` — operations/admin session and CSRF token required

The browser must use same-origin requests. Send the session cookie automatically and the returned CSRF token in `x-csrf-token` for state-changing authenticated requests.

## Security and production limitations

This is an implementation foundation, not a fully launched cargo portal. It supports initial staff login but has no customer login/registration, quote acceptance, shipment conversion, file upload, invoices, or payments. Dashboard HTML contains illustrative sample records and is not protected; do not enter or display real customer data there. The public enquiry endpoint needs production-grade abuse controls and a verified privacy/retention policy before public launch. Review data schema, authorization, logging, backups, restore procedures, and deployment configuration with a qualified engineer. Never expose the database directly to the internet or commit `.env`.
