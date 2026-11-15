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

quoteForm?.addEventListener('submit', (event) => {
  event.preventDefault();
  if (!quoteForm.reportValidity()) return;

  feedback.textContent = 'Thanks — this form is active as a front-end prototype. Connect the secure backend and Airsell contact flow before publishing this live.';
  feedback.classList.add('visible');
  feedback.focus?.();
});

const fieldFilter = document.querySelector('#field-filter');
const shipmentTable = document.querySelector('#shipment-table tbody');
const filterRows = () => {
  if (!fieldFilter || !shipmentTable) return;
  const query = fieldFilter.value.trim().toLowerCase();
  [...shipmentTable.querySelectorAll('tr')].forEach((row) => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(query) ? '' : 'none';
  });
};
fieldFilter?.addEventListener('input', filterRows);

const statusPills = document.querySelectorAll('[data-status]');
statusPills.forEach((pill) => {
  const status = pill.dataset.status;
  if (status === 'Booked') pill.classList.add('is-booked');
  if (status === 'In transit') pill.classList.add('is-transit');
  if (status === 'Documents') pill.classList.add('is-docs');
  if (status === 'Delivered') pill.classList.add('is-delivered');
  if (status === 'Pending') pill.classList.add('is-pending');
});

const analyticsBars = document.querySelectorAll('.mini-bar');
analyticsBars.forEach((bar) => {
  const width = bar.dataset.width || '50';
  bar.style.setProperty('--bar-width', width + '%');
});
