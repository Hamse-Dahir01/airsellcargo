const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('.main-nav');

menuButton?.addEventListener('click', () => {
  const isOpen = navigation.classList.toggle('open');
  menuButton.setAttribute('aria-expanded', String(isOpen));
  menuButton.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
});

navigation?.querySelectorAll('a').forEach((link) => {
  link.addEventListener('click', () => {
    navigation.classList.remove('open');
    menuButton?.setAttribute('aria-expanded', 'false');
    menuButton?.setAttribute('aria-label', 'Open navigation');
  });
});

document.querySelector('#year')?.textContent = new Date().getFullYear();

const quoteForm = document.querySelector('#quote-form');
const feedback = document.querySelector('#form-feedback');

quoteForm?.addEventListener('submit', async (event) => {
  event.preventDefault();
  if (!quoteForm.reportValidity()) return;

  const submitButton = quoteForm.querySelector('button[type="submit"]');
  const originalButtonText = submitButton?.innerHTML;
  const fields = new FormData(quoteForm);
  const directionLabel = fields.get('direction');
  const direction = directionLabel === 'Export from Hargeisa'
    ? 'export'
    : directionLabel === 'Import to Hargeisa' ? 'import' : 'other';

  const enquiry = {
    contactName: fields.get('name'),
    companyName: fields.get('company'),
    email: fields.get('email'),
    phone: fields.get('phone'),
    direction,
    service: fields.get('service'),
    origin: fields.get('origin'),
    destination: fields.get('destination'),
    cargoDescription: fields.get('cargo'),
    weightKg: fields.get('weight') || null,
    notes: fields.get('notes'),
    consentGiven: quoteForm.querySelector('[type="checkbox"]').checked
  };

  if (submitButton) {
    submitButton.disabled = true;
    submitButton.textContent = 'Sending enquiry…';
  }
  feedback.textContent = '';
  feedback.classList.remove('visible', 'form-error');

  try {
    const response = await fetch('/api/v1/enquiries', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify(enquiry)
    });
    const result = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(result.error || 'We could not submit your request. Please try again or contact Airsell Cargo.');

    feedback.textContent = `${result.message || 'Your enquiry was received.'} Reference: ${result.reference}. This is a request for review, not a confirmed booking.`;
    feedback.classList.add('visible');
    quoteForm.reset();
  } catch (error) {
    feedback.textContent = error.message || 'The service is temporarily unavailable. Please contact Airsell Cargo directly.';
    feedback.classList.add('visible', 'form-error');
  } finally {
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.innerHTML = originalButtonText;
    }
  }
});

const filterInput = document.querySelector('#field-filter');
const shipmentTable = document.querySelector('#shipment-table tbody');
filterInput?.addEventListener('input', () => {
  if (!shipmentTable) return;
  const query = filterInput.value.trim().toLowerCase();
  [...shipmentTable.querySelectorAll('tr')].forEach((row) => {
    row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
  });
});

const analyticsBars = document.querySelectorAll('.mini-bar');
analyticsBars.forEach((bar) => {
  bar.style.setProperty('--bar-width', `${bar.dataset.width || '50'}%`);
});

// Customer portal shipment search, status filtering, sorting, and page-size control.
const shipmentList = document.querySelector('#shipment-list');
if (shipmentList) {
  const search = document.querySelector('#shipment-search');
  const statusFilter = document.querySelector('#shipment-status');
  const sortSelect = document.querySelector('#shipment-sort');
  const pageSizeSelect = document.querySelector('#shipment-page-size');
  const results = document.querySelector('#shipment-results');
  const emptyState = document.querySelector('#shipment-empty');
  const cards = [...shipmentList.querySelectorAll('.shipment-card')];

  const updateShipmentList = () => {
    const query = search.value.trim().toLowerCase();
    const status = statusFilter.value;
    const sort = sortSelect.value;
    const pageSize = Number(pageSizeSelect.value);
    const matching = cards.filter((card) => {
      const matchesSearch = card.textContent.toLowerCase().includes(query);
      const matchesStatus = status === 'all' || card.dataset.status === status;
      return matchesSearch && matchesStatus;
    });

    matching.sort((a, b) => {
      if (sort === 'oldest') return Number(a.dataset.shipmentId) - Number(b.dataset.shipmentId);
      if (sort === 'destination') return a.dataset.destination.localeCompare(b.dataset.destination);
      return Number(b.dataset.shipmentId) - Number(a.dataset.shipmentId);
    });

    cards.forEach((card) => { card.hidden = true; });
    matching.forEach((card, index) => {
      if (index < pageSize) {
        card.hidden = false;
        shipmentList.insertBefore(card, emptyState);
      }
    });
    const shown = Math.min(matching.length, pageSize);
    results.textContent = `Showing ${shown} of ${matching.length} matching shipment${matching.length === 1 ? '' : 's'}.`;
    emptyState.hidden = matching.length > 0;
  };

  [search, statusFilter, sortSelect, pageSizeSelect].forEach((control) => {
    control.addEventListener(control === search ? 'input' : 'change', updateShipmentList);
  });
  updateShipmentList();
}
