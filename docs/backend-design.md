# Backend design notes

## Scope and current state

This repository is a static HTML/CSS/JavaScript prototype. The SQL schema in `database-schema.sql` and the API outline below describe a proposed next phase; they do not constitute a running backend. All dashboard data currently shown in the HTML is sample content. Keep those pages clearly marked as a prototype until real authentication and API authorization are implemented.

## Proposed architecture

- **Web client:** existing static pages initially; later connect a maintained frontend framework if needed.
- **API:** a server-side application exposing versioned JSON endpoints (for example, `/api/v1`). Choose a framework the implementation team can maintain; no backend framework is configured in this repository yet.
- **Database:** PostgreSQL using schema migrations, least-privilege database credentials, automated backups, and tested restore procedures.
- **Document storage:** private object storage, separate from the web server. Store only object keys and metadata in PostgreSQL. Use short-lived authorized download links and enforce shipment/customer access on the API.
- **Notifications:** send email or messaging notifications from a server-side job after an authorized state change; do not put provider credentials in browser code.

## Initial workflow

1. A prospective customer submits a shipment enquiry. The server validates and rate-limits the request and creates an enquiry record with status `new`.
2. Authorized sales staff review it, record quote items and terms, and send a quote. Sending a quote creates an immutable sent version or otherwise preserves the exact terms shown to the customer.
3. The customer accepts or declines through an authenticated, authorized flow. Acceptance must be checked transactionally so the quote cannot be accepted twice or after expiry.
4. Authorized staff convert an accepted quote to a shipment. Record the shipment's initial status event in the same transaction.
5. Operations staff create timestamped tracking events. Customer views expose only customer-safe notes and events for that customer's shipment.
6. Authorized users upload documents through a private storage flow. Save metadata and review status; never trust a client-supplied filename, content type, size, or shipment ID as authorization.

## API outline

All endpoints are proposals. Require HTTPS in deployment and validate input on the server.

### Public

- `POST /api/v1/enquiries` — submit an enquiry. Accept only the minimum fields needed; return a random public reference, not a sequential database ID. Apply rate limits, spam controls, server-side validation, and safe error messages.

### Customer-authenticated

- `GET /api/v1/me/shipments` — list only shipments belonging to the authenticated user's organization.
- `GET /api/v1/shipments/{shipmentId}` — return customer-visible shipment information after organization-level authorization.
- `GET /api/v1/shipments/{shipmentId}/documents` — list authorized document metadata, never public object URLs.
- `POST /api/v1/quotes/{quoteId}/accept` and `/decline` — permit only the intended customer organization and valid quote state; record actor and time.

### Staff-authenticated

- `GET /api/v1/staff/enquiries` — sales/operations access according to role.
- `PATCH /api/v1/staff/enquiries/{enquiryId}` — update allowed enquiry fields and assign authorized staff.
- `POST /api/v1/staff/enquiries/{enquiryId}/quotes` — create and send quotes with explicit currency, validity, inclusions, and exclusions.
- `POST /api/v1/staff/quotes/{quoteId}/convert` — convert an accepted quote into one shipment once only.
- `POST /api/v1/staff/shipments/{shipmentId}/events` — add a status event; distinguish internal notes from customer-visible descriptions.
- `POST /api/v1/staff/shipments/{shipmentId}/documents/{documentId}/review` — update review status with reviewer and timestamp.

Use pagination for lists. Define request and response schemas, error codes, idempotency behavior, and field-level permissions before implementation. Do not expose internal notes or other customers' data.

## Security and privacy requirements

- Hash passwords using a maintained password-hashing library (Argon2id recommended); never store plaintext passwords or create a custom authentication scheme.
- Enforce authorization on every API request. Customer access must be scoped to organization membership; staff actions must check assigned roles and least privilege. Hiding a link in HTML is not access control.
- Use secure, HttpOnly, SameSite cookies or a vetted identity provider, CSRF defenses where cookie authentication applies, session expiration, and a documented account recovery process.
- Keep secrets out of the repository and client-side JavaScript. Rotate credentials and use separate development and production secrets.
- Validate and normalize all input server-side. Parameterize SQL. Add rate limits and abuse protection to public endpoints.
- For uploads, allowlist formats and size limits, inspect content, generate storage keys server-side, scan files where practical, and serve only after authorization through short-lived links. Do not store sensitive cargo documents in a public static directory.
- Minimize personal data, define retention and deletion procedures, publish an accurate privacy notice, and restrict logs so they do not contain passwords, tokens, or document contents.
- Record sensitive state changes in the audit table. Restrict access to audit logs and never treat them as a substitute for authorization.
- Before production, add automated tests for cross-organization access denial, role permissions, quote state transitions, and upload access. Review local privacy, customs, aviation, and data-handling obligations with qualified advisers.

## Schema notes

The SQL is a proposed initial migration for PostgreSQL 14 or newer. It stores document metadata only, not file bytes. Apply it in a development database first and review status values and required fields with Airsell's actual workflow. Use a migration tool in implementation; do not manually edit production tables. The migration does not configure database row-level security or create application users/roles: those must be designed and enabled for the chosen hosting and API architecture before real data is used.
