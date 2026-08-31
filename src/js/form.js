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
 * Where submissions go. Injected at build time.
 *
 * On the static build this is empty, because there is no server behind it.
 * In Phase 7 it becomes the WordPress admin-ajax endpoint and nothing else in
 * this file changes - the field names below are already the ones the PHP
 * handler will read out of $_POST.
 */
const ENDPOINT = FORM_ENDPOINT;

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
      // Someone who has just enquired should not then be shown the popup.
      markShown();
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
/**
 * Auto-open settings.
 *
 * Deliberately not on page load. Google treats a popup that covers the content
 * of a page arrived at from search as an intrusive interstitial, and demotes
 * the page for it on mobile - which would work directly against the point of
 * this site. Waiting for the visitor to show interest first (time on page, or
 * scrolling into the page) sidesteps that, and converts better anyway.
 */
const AUTO = {
  delay: 25000,   // ms on the page
  scroll: 0.45,   // or this much of the page read, whichever comes first
  key: 'ge-enquiry-seen',
};

/**
 * Both thresholds can be overridden per site from the markup:
 *   <dialog data-enquiry-modal data-auto-delay="25000" data-auto-scroll="0.45">
 * In Phase 7 those two attributes are filled from the Theme Options page, so
 * the client can retune the popup without a developer.
 */
function autoSettings(modal) {
  return {
    delay: Number(modal.dataset.autoDelay) || AUTO.delay,
    scroll: Number(modal.dataset.autoScroll) || AUTO.scroll,
  };
}

/** Once per browser session - not once per page view. */
function alreadyShown() {
  try {
    return sessionStorage.getItem(AUTO.key) === '1';
  } catch {
    // Private mode or blocked storage: treat as shown, so a visitor who cannot
    // be remembered is not hit by the popup on every single page.
    return true;
  }
}

function markShown() {
  try {
    sessionStorage.setItem(AUTO.key, '1');
  } catch { /* nothing we can do, and nothing that needs saying */ }
}

function initEnquiryModal() {
  const modal = document.querySelector('[data-enquiry-modal]');
  if (!modal || typeof modal.showModal !== 'function') return;

  function open({ auto = false } = {}) {
    if (modal.open) return;
    if (auto && alreadyShown()) return;

    markShown();
    modal.showModal();
    // Land on the first real field, not on the close button.
    modal.querySelector('input[name="name"]')?.focus();
  }

  for (const trigger of document.querySelectorAll('[data-enquiry-open]')) {
    trigger.addEventListener('click', () => open());
  }

  modal.querySelector('[data-enquiry-close]')?.addEventListener('click', () => modal.close());

  // Backdrop click closes; a click inside the panel must not.
  modal.addEventListener('click', (e) => {
    if (e.target === modal) modal.close();
  });

  /* ---------------------------------------------------------- auto-open --- */

  if (alreadyShown()) return;

  const inlineForm = document.querySelector('#enquiry');
  let inlineVisible = false;

  // Never interrupt someone who is already looking at the enquiry form.
  if (inlineForm) {
    new IntersectionObserver(([entry]) => { inlineVisible = entry.isIntersecting; })
      .observe(inlineForm);
  }

  const { delay, scroll } = autoSettings(modal);
  const timer = setTimeout(tryOpen, delay);

  function tryOpen() {
    clearTimeout(timer);
    removeEventListener('scroll', onScroll);

    if (inlineVisible) return; // they found the form on their own
    open({ auto: true });
  }

  function onScroll() {
    const read = scrollY / Math.max(1, document.body.scrollHeight - innerHeight);
    if (read >= scroll) tryOpen();
  }

  addEventListener('scroll', onScroll, { passive: true });
}

export function initForms() {
  for (const form of document.querySelectorAll('[data-enquiry-form]')) initEnquiryForm(form);
  initEnquiryModal();
  initMap();
}
