/**
 * Site behaviour. Loaded with `defer`, so the DOM is ready on execution.
 * Every module is opt-in via a data attribute, so pages only pay for what
 * they actually use.
 */
import { initSliders } from './slider.js';

/* ------------------------------------------------------------------ header */

function initHeader() {
  const header = document.querySelector('[data-header]');
  if (!header) return;

  // Toggle a solid background once the hero is scrolled past.
  let ticking = false;
  const update = () => {
    header.classList.toggle('is-scrolled', window.scrollY > 24);
    ticking = false;
  };
  addEventListener('scroll', () => {
    if (!ticking) { ticking = true; requestAnimationFrame(update); }
  }, { passive: true });
  update();
}

/* -------------------------------------------------------------- mobile nav */

function initMobileNav() {
  const toggle = document.querySelector('[data-nav-toggle]');
  const panel = document.querySelector('[data-nav-panel]');
  if (!toggle || !panel) return;

  const FOCUSABLE = 'a[href], button:not([disabled]), input, select, textarea';
  let lastFocused = null;

  const open = () => {
    lastFocused = document.activeElement;
    panel.hidden = false;
    requestAnimationFrame(() => panel.classList.add('is-open'));
    toggle.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
    panel.querySelector(FOCUSABLE)?.focus();
  };

  const close = () => {
    panel.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    lastFocused?.focus();
    const done = () => { panel.hidden = true; };
    panel.addEventListener('transitionend', done, { once: true });
    setTimeout(done, 400); // fallback when reduced motion kills the transition
  };

  toggle.addEventListener('click', () => {
    toggle.getAttribute('aria-expanded') === 'true' ? close() : open();
  });

  panel.addEventListener('click', (e) => {
    if (e.target.closest('a, [data-nav-close]')) close();
  });

  document.addEventListener('keydown', (e) => {
    if (panel.hidden) return;
    if (e.key === 'Escape') return close();
    if (e.key !== 'Tab') return;

    // Keep focus inside the panel while it is open.
    const items = [...panel.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent);
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  });
}

/* -------------------------------------------------------------- accordion */

function initAccordions() {
  for (const acc of document.querySelectorAll('[data-accordion]')) {
    const single = acc.hasAttribute('data-accordion-single');
    const triggers = acc.querySelectorAll('[data-accordion-trigger]');

    for (const trigger of triggers) {
      const panel = document.getElementById(trigger.getAttribute('aria-controls'));
      if (!panel) continue;

      trigger.addEventListener('click', () => {
        const isOpen = trigger.getAttribute('aria-expanded') === 'true';

        if (single && !isOpen) {
          for (const other of triggers) {
            if (other === trigger) continue;
            other.setAttribute('aria-expanded', 'false');
            const p = document.getElementById(other.getAttribute('aria-controls'));
            if (p) p.style.height = '0px';
          }
        }

        trigger.setAttribute('aria-expanded', String(!isOpen));
        // Animate to the measured height, then release to `auto` so the panel
        // reflows correctly if its content or the viewport changes.
        panel.style.height = isOpen ? `${panel.scrollHeight}px` : '0px';
        requestAnimationFrame(() => {
          panel.style.height = isOpen ? '0px' : `${panel.scrollHeight}px`;
        });
      });

      panel.addEventListener('transitionend', (e) => {
        if (e.propertyName !== 'height') return;
        if (trigger.getAttribute('aria-expanded') === 'true') panel.style.height = 'auto';
      });
    }
  }
}

/* ------------------------------------------------------------- reveal ------ */

function initReveal() {
  const items = document.querySelectorAll('[data-reveal]');
  if (!items.length) return;

  if (matchMedia('(prefers-reduced-motion: reduce)').matches) {
    items.forEach((el) => el.classList.add('is-revealed'));
    return;
  }

  const io = new IntersectionObserver((entries) => {
    for (const entry of entries) {
      if (!entry.isIntersecting) continue;
      entry.target.classList.add('is-revealed');
      io.unobserve(entry.target);
    }
  }, { rootMargin: '0px 0px -10% 0px', threshold: 0.1 });

  items.forEach((el) => io.observe(el));
}

/* ------------------------------------------------------------------- misc */

function initYear() {
  const year = String(new Date().getFullYear());
  document.querySelectorAll('[data-year]').forEach((el) => { el.textContent = year; });
}

/* ------------------------------------------------------------------- boot */

initHeader();
initMobileNav();
initAccordions();
initReveal();
initSliders();
initYear();
