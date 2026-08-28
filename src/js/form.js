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

/**
 * Where submissions go.
 *
 * Two sources, checked in that order. FORM_ENDPOINT is substituted at build
 * time and is what the static build used - empty there, because there was no
 * server behind it. Under WordPress the URL is not known until runtime (it
 * depends on the domain the theme is installed on), so inc/assets.php prints
 * window.GE_AJAX ahead of this bundle and that wins.
 *
 * Still empty means no server at all, and send() is never reached - the form
 * says so plainly rather than showing a success message for a submission that
 * went nowhere.
 */
const ENDPOINT = FORM_ENDPOINT || globalThis.GE_AJAX?.url || '';

function setStatus(status, state, message) {
  status.hidden = false;
  status.className = `sm:col-span-2 form-status is-${state}`;
  status.textContent = message;
}

async function send(form) {
  const body = new FormData(form);
  body.append('action', 'ge_enquiry'); // WordPress dispatches on this

  const res = await fetch(ENDPOINT, {
    method: 'POST',
    body,
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
  });

  if (!res.ok) throw new Error(`HTTP ${res.status}`);

  // WP's wp_send_json_success / _error shape.
  const data = await res.json().catch(() => ({ success: res.ok }));
  if (data.success === false) throw new Error(data.data?.message ?? 'Rejected');

  return data;
}

function initEnquiryForm(form) {
  const status = form.querySelector('[data-form-status]');
  const submit = form.querySelector('button[type="submit"]');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // A filled honeypot means a bot: accept silently rather than explaining.
    if (form.elements.company.value) {
      setStatus(status, 'ok', 'Thank you - we will be in touch.');
      return;
    }

    if (!validate(form)) return;

    submit.disabled = true;
    setStatus(status, 'pending', 'Sending your enquiry...');

    // No endpoint yet: the static build has no server. Say so plainly rather
    // than showing a success message for a submission that went nowhere.
    if (!ENDPOINT) {
      submit.disabled = false;
      setStatus(
        status,
        'pending',
        'Form is not connected yet - this gets wired up when the site moves to WordPress.',
      );
      return;
    }

    try {
      await send(form);
      form.reset();
      setStatus(status, 'ok', 'Thank you. We have your details and will call you shortly.');
    } catch {
      setStatus(
        status,
        'error',
        'Something went wrong sending that. Please call or WhatsApp us instead.',
      );
    } finally {
      submit.disabled = false;
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
