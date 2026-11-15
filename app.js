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

document.querySelector('#year').textContent = new Date().getFullYear();

const quoteForm = document.querySelector('#quote-form');
const feedback = document.querySelector('#form-feedback');

quoteForm?.addEventListener('submit', (event) => {
  event.preventDefault();
  if (!quoteForm.reportValidity()) return;

  // This static-site prototype has no server, so do not imply that the enquiry
  // was sent or persist personal data in the visitor's browser.
  feedback.textContent = 'Thanks — the form is working as a preview, but this enquiry was not sent. Connect a secure backend and Airsell contact channel before publishing this form for customers.';
  feedback.classList.add('visible');
  feedback.focus?.();
});
