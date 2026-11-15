# Airsell Cargo portal — first frontend milestone

Responsive, single-page public website and shipment-enquiry form for Airsell Cargo Services, based at Hargeisa Airport (HGA), Somaliland.

## Files

- `index.html` — public landing page and quote request form
- `styles.css` — responsive visual design
- `app.js` — mobile navigation and client-side form preview

## Preview locally

Open `index.html` in a browser, or serve this directory with any static web server. No build step is required.

## Important: prototype only

The quote form currently validates fields in the browser and displays a preview message. It does **not** transmit or save enquiries, authenticate customers, securely upload documents, create bookings, or provide shipment tracking. Do not collect customer shipment data through this prototype as if it were a live portal.

Before launch, connect a secure backend and agreed Airsell contact channel; add server-side validation, spam protection, privacy notice/consent, and secure access controls for customer and staff data. Do not add document uploads until private storage and authorization are in place.

## Next implementation milestones

1. Add a backend API and persistent database for enquiries and staff-managed quotes.
2. Add staff authentication, role-based access, and an operations dashboard.
3. Add customer accounts, shipment milestones, and secure document storage.
4. Configure real Airsell contact details, service descriptions, and operational policies before launch.
