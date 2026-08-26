/**
 * Gallery category tabs plus a lightbox built on <dialog>.
 *
 * Both are progressive: with JavaScript off every photo is still visible and
 * the grid still reads, because filtering only toggles `hidden` and the
 * lightbox only enhances buttons that already sit in the markup.
 */

function initTabs() {
  const tabs = document.querySelector('[data-gallery-tabs]');
  const grid = document.querySelector('[data-gallery-grid]');
  if (!tabs || !grid) return;

  const buttons = [...tabs.querySelectorAll('[data-gallery-filter]')];
  const items = [...grid.children];

  tabs.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-gallery-filter]');
    if (!btn) return;

    const cat = btn.dataset.galleryFilter;

    for (const b of buttons) {
      const active = b === btn;
      b.classList.toggle('is-active', active);
      b.setAttribute('aria-selected', String(active));
    }

    for (const li of items) {
      li.hidden = cat !== 'all' && li.dataset.cat !== cat;
    }
  });
}

function initLightbox() {
  const dialog = document.querySelector('[data-lightbox-dialog]');
  if (!dialog || typeof dialog.showModal !== 'function') return;

  const img = dialog.querySelector('[data-lightbox-img]');
  const caption = dialog.querySelector('[data-lightbox-caption]');
  const triggers = [...document.querySelectorAll('[data-lightbox]')];
  if (!triggers.length) return;

  let index = 0;

  function show(i) {
    // Wrap, and skip anything the category filter has hidden.
    const visible = triggers.filter((t) => !t.closest('[hidden]'));
    if (!visible.length) return;

    index = (i + visible.length) % visible.length;
    const trigger = visible[index];

    img.src = trigger.dataset.full;
    img.alt = trigger.querySelector('img')?.alt ?? '';
    caption.textContent = trigger.dataset.caption ?? '';
  }

  for (const [i, trigger] of triggers.entries()) {
    trigger.addEventListener('click', () => {
      const visible = triggers.filter((t) => !t.closest('[hidden]'));
      show(visible.indexOf(trigger) === -1 ? i : visible.indexOf(trigger));
      dialog.showModal();
    });
  }

  dialog.querySelector('[data-lightbox-prev]')?.addEventListener('click', () => show(index - 1));
  dialog.querySelector('[data-lightbox-next]')?.addEventListener('click', () => show(index + 1));
  dialog.querySelector('[data-lightbox-close]')?.addEventListener('click', () => dialog.close());

  dialog.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') show(index - 1);
    if (e.key === 'ArrowRight') show(index + 1);
  });

  // Clicking the backdrop closes; clicking the photo itself must not.
  dialog.addEventListener('click', (e) => {
    if (e.target === dialog) dialog.close();
  });

  // Release the image so a large file is not held in memory once closed.
  dialog.addEventListener('close', () => { img.removeAttribute('src'); });
}

export function initGallery() {
  initTabs();
  initLightbox();
}
