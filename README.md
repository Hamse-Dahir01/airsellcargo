# Airsell Cargo portal

Responsive Airsell Cargo website and initial PostgreSQL-backed Express API, for local development and deployment planning.

## Run with Docker

Requirements: Docker Compose. Copy `.env.docker.example` to `.env.docker`, replace the sample password and session secret with strong random values, then run:

```sh
docker compose --env-file .env.docker up --build -d
docker compose --env-file .env.docker exec app npm run db:migrate
```

Open `http://localhost:3000`. To create the first staff administrator, run the command below with `STAFF_EMAIL`, `STAFF_NAME`, and `STAFF_PASSWORD` supplied only in your shell environment (use at least 14 characters for the password):

```sh
docker compose --env-file .env.docker exec -e STAFF_EMAIL -e STAFF_NAME -e STAFF_PASSWORD app npm run staff:create
```

The database volume persists between restarts. Back it up and test restore procedures before using real data. For a public deployment, configure `APP_ORIGIN` to the exact HTTPS portal URL, terminate TLS at a trusted reverse proxy, set `TRUST_PROXY=1` only when there is exactly one trusted proxy, and use managed secrets. This container setup does not publish the site or configure DNS/TLS for you.

## Local Node setup

Requirements: Node.js 20+ and PostgreSQL 14+.

1. Install dependencies: `npm install`.
2. Copy `.env.example` to `.env`; configure `DATABASE_URL`, `SESSION_SECRET` (32+ random characters), and `APP_ORIGIN`.
3. Run `npm run db:migrate`.
4. Create an initial staff administrator with `STAFF_EMAIL`, `STAFF_NAME`, and `STAFF_PASSWORD` set in the environment, then run `npm run staff:create`.
5. Start with `npm run dev`.

## Current application behavior

- The public enquiry form posts to `POST /api/v1/enquiries`; successful submissions receive a reference and are saved to PostgreSQL.
- Staff login and role-checked API routes are available in `server.js`.
- Dashboard HTML still contains illustrative sample records and is not an authenticated customer/staff application. Do not use it to display real customer data.
- Customer login, customer shipment access, quote acceptance/conversion, private document uploads, invoices, and payments are not implemented yet.

## Important production limitations

This work connects the public enquiry form to the API and adds a container deployment scaffold; it does not mean the portal has been deployed. Do not point a public subdomain at this prototype until HTTPS, origin configuration, database backups, rate-limit strategy, monitoring, privacy/retention policies, staff authentication testing, and operational procedures are reviewed. Store `.env` files and all real credentials outside source control. The local Compose database and sample credentials are for development only.
