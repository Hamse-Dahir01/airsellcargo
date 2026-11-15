# Airsell Cargo portal

Responsive front-end prototype for Airsell Cargo Services, based at Hargeisa Airport (HGA), Somaliland.

## Current frontend

- `index.html` — public landing page and shipment enquiry form
- `dashboard.html` — sample customer dashboard
- `admin.html` — sample operations dashboard
- `styles.css` — shared responsive styles
- `app.js` — navigation and prototype-only interactions

## Backend planning artifacts

- [`docs/database-schema.sql`](docs/database-schema.sql) — initial PostgreSQL schema for organizations, accounts, enquiries, quotes, shipments, tracking events, documents, invoices, and audit events
- [`docs/backend-design.md`](docs/backend-design.md) — proposed API, workflow, security requirements, and implementation sequence

## Preview the UI

Open `index.html` in a browser or serve this directory with a static web server. No build step is required.

## Prototype limitations — not production-ready

All dashboard figures and shipment records are illustrative sample data. The enquiry form only validates fields in the browser and displays a preview message; it does not transmit or save enquiries. There is no authentication, backend API, database connection, real shipment tracking, payment processing, or secure document storage. The dashboard pages are not protected and must not be used to expose real customer data.

The SQL and API design are planning artifacts, not an installed or tested backend. Do not collect customer shipment data through this prototype as if it were a live portal. Before launch, implement and test the backend, authentication and authorization, secure private file storage, server-side validation, abuse prevention, privacy notices, backups, and monitoring. Confirm actual services, contacts, operational processes, and applicable requirements with Airsell before publishing.

## Suggested implementation order

1. Review the data model and API plan with Airsell operations.
2. Implement the API and run the schema against a development PostgreSQL database.
3. Add staff authentication and role checks; protect operations routes before using real data.
4. Connect enquiry submission and staff-managed quotes.
5. Add shipment milestones and private document storage with per-customer access controls.
6. Test authorization, data validation, backups, and deployment before launch.
