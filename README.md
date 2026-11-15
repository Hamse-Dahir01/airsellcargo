# Airsell Cargo portal

Responsive front-end prototype for Airsell Cargo Services, based at Hargeisa Airport (HGA), Somaliland. This repository now includes a lightweight backend starter for a cargo portal workflow.

## Included

- `index.html` — public landing page and shipment enquiry form
- `dashboard.html` — customer shipment dashboard
- `admin.html` — operations dashboard
- `styles.css` — responsive styling
- `app.js` — UI interactions
- `server.js` — Express API starter for enquiries, quotes, shipments, and dashboard metrics
- `data/store.json` — seed data for local prototype persistence
- `docs/backend-design.md` and `docs/database-schema.sql` — architecture and schema planning artifacts

## Run locally

1. Install dependencies:
   npm install
2. Start the server:
   npm run dev
3. Open the app:
   http://localhost:3000

## Available API routes

### Health

- `GET /api/v1/health`

### Enquiries

- `GET /api/v1/enquiries`
- `POST /api/v1/enquiries`

### Quotes

- `GET /api/v1/quotes`
- `POST /api/v1/enquiries/:id/quotes`
- `POST /api/v1/quotes/:id/accept`

### Shipments

- `GET /api/v1/shipments`
- `PATCH /api/v1/shipments/:id/status`

### Dashboard

- `GET /api/v1/dashboard/overview`

## Important note

The UI and API are still a working prototype. They do not yet include secure authentication, role-based staff access, protected document uploads, or a production database. Use the API only for local development and guided internal testing until those safeguards are implemented.

## Next backend milestones

1. Add secure authentication and staff access control.
2. Add database-backed persistence instead of the local JSON file.
3. Add protected document uploads with private storage.
4. Add invoice, payment, and audit flows.
5. Deploy to a production environment with monitoring and backups.
