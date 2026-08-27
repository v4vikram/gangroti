/**
 * Enquiry form and click-to-load map.
 *
 * On the static build there is no server, so a valid submission is handed to
 * WhatsApp with the answers pre-written into the message. In Phase 7 the same
 * markup posts to admin-ajax instead - only `send()` changes, and the field
 * names are already the ones the WordPress handler will read.
 */

const REQUIRED = ['name', 'phone'];

function setError(input, message) {
  const field = input.closest('div');
  let note = field.querySelector('.field-error');

  if (!message) {
    input.removeAttribute('aria-invalid');
    note?.remove();
    return false;
  }

  input.setAttribute('aria-invalid', 'true');
  if (!note) {
    note = document.createElement('p');
    note.className = 'field-error';
    field.append(note);
  }
  note.textContent = message;
  return true;
}

function validate(form) {
  let firstBad = null;

  for (const name of REQUIRED) {
    const input = form.elements[name];
    const bad = setError(input, input.value.trim() ? '' : 'This one we do need.');
    if (bad && !firstBad) firstBad = input;
  }

  const email = form.elements.email;
  if (email.value.trim() && !email.checkValidity()) {
    const bad = setError(email, 'That email address does not look right.');
    if (bad && !firstBad) firstBad = email;
  } else {
    setError(email, '');
  }

  const phone = form.elements.phone;
  if (phone.value.trim() && !phone.checkValidity()) {
    const bad = setError(phone, 'Please include a full phone number.');
    if (bad && !firstBad) firstBad = phone;
  }

  firstBad?.focus();
  return !firstBad;
}

function compose(form) {
  const get = (n) => form.elements[n]?.value.trim();

  const lines = [
    'Namaste! I would like to enquire about a yatra.',
    '',
    `Name: ${get('name')}`,
    `Phone: ${get('phone')}`,
  ];

  const optional = {
    Email: get('email'),
    Yatra: get('yatra'),
    Travellers: get('travellers'),
    'Start date': get('date'),
    Budget: get('budget'),
  };

  for (const [label, value] of Object.entries(optional)) {
    if (value) lines.push(`${label}: ${value}`);
  }

  if (get('message')) lines.push('', get('message'));

  return lines.join('\n');
}

function initEnquiryForm(form) {

  const status = form.querySelector('[data-form-status]');
  const whatsapp = document.querySelector('a[href*="wa.me/"]')?.href.match(/wa\.me\/(\d+)/)?.[1];

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    // A filled honeypot means a bot: accept silently rather than explaining.
    if (form.elements.company.value) {
      status.hidden = false;
      status.textContent = 'Thank you - we will be in touch.';
      return;
    }

    if (!validate(form)) return;

    status.hidden = false;
    status.className = 'sm:col-span-2 form-status is-ok';
    status.textContent = 'Opening WhatsApp with your details filled in...';

    if (whatsapp) {
      window.open(`https://wa.me/${whatsapp}?text=${encodeURIComponent(compose(form))}`, '_blank', 'noopener');
    }
  });

  // Clear an error as soon as the person starts fixing it.
  for (const el of form.elements) {
    el.addEventListener('input', () => {
      if (el.hasAttribute('aria-invalid')) setError(el, '');
    });
  }
}

function initMap() {
  const box = document.querySelector('[data-map]');
  if (!box) return;

  box.querySelector('[data-map-load]')?.addEventListener('click', () => {
    const iframe = document.createElement('iframe');
    iframe.src = box.dataset.src;
    iframe.title = 'Our location on Google Maps';
    iframe.loading = 'lazy';
    iframe.referrerPolicy = 'no-referrer-when-downgrade';
    iframe.allowFullscreen = true;
    box.replaceChildren(iframe);
  });
}

/**
 * The enquiry popup. <dialog> gives us the backdrop, focus trapping, inert
 * background and Escape handling for free, so this only wires the triggers.
 */
function initEnquiryModal() {
  const modal = document.querySelector('[data-enquiry-modal]');
  if (!modal || typeof modal.showModal !== 'function') return;

  for (const trigger of document.querySelectorAll('[data-enquiry-open]')) {
    trigger.addEventListener('click', () => {
      modal.showModal();
      // Land on the first real field, not on the close button.
      modal.querySelector('input[name="name"]')?.focus();
    });
  }

  modal.querySelector('[data-enquiry-close]')?.addEventListener('click', () => modal.close());

  // Backdrop click closes; a click inside the panel must not.
  modal.addEventListener('click', (e) => {
    if (e.target === modal) modal.close();
  });
}

export function initForms() {
  for (const form of document.querySelectorAll('[data-enquiry-form]')) initEnquiryForm(form);
  initEnquiryModal();
  initMap();
}
