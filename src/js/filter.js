/**
 * Client-side filtering for the yatra listing.
 *
 * The cards are already in the HTML - this only hides and shows them, so the
 * page still lists every yatra with JavaScript switched off and search engines
 * see the full set. Selections are mirrored into the URL, so a filtered view
 * can be linked or reloaded, and the home page search bar lands here with its
 * choices already applied.
 *
 * In Phase 7 this becomes a WP_Query with the same parameter names, so the
 * markup and the URLs carry over unchanged.
 */

const PARAMS = ['destination', 'duration', 'month', 'type', 'sort'];

function matches(card, state) {
  const { destination, duration, type } = state;

  if (destination && card.dataset.destination !== destination) return false;
  if (type && card.dataset.type !== type) return false;

  if (duration) {
    const [min, max] = duration.split('-').map(Number);
    const days = Number(card.dataset.days);
    if (days < min || days > max) return false;
  }

  // `month` deliberately does not filter: every listed yatra runs across a
  // season rather than a single month, so narrowing on it would hide valid
  // results. It is carried through to the enquiry instead.
  return true;
}

function sortCards(cards, mode) {
  const by = {
    'price-asc': (a, b) => a.dataset.price - b.dataset.price,
    'price-desc': (a, b) => b.dataset.price - a.dataset.price,
    'duration-asc': (a, b) => a.dataset.days - b.dataset.days,
    'duration-desc': (a, b) => b.dataset.days - a.dataset.days,
  }[mode];

  return by ? [...cards].sort(by) : [...cards];
}

export function initFilter() {
  const root = document.querySelector('[data-filter]');
  if (!root) return;

  const grid = document.querySelector('[data-filter-grid]');
  const empty = document.querySelector('[data-filter-empty]');
  const count = document.querySelector('[data-filter-count]');
  const reset = document.querySelector('[data-filter-reset]');
  const cards = [...grid.querySelectorAll('[data-yatra]')];

  const controls = PARAMS
    .map((name) => root.querySelector(`[name="${name}"]`))
    .filter(Boolean);

  function read() {
    return Object.fromEntries(controls.map((el) => [el.name, el.value]));
  }

  function apply({ push = true } = {}) {
    const state = read();

    let shown = 0;
    for (const card of cards) {
      const ok = matches(card, state);
      card.hidden = !ok;
      if (ok) shown++;
    }

    for (const card of sortCards(cards, state.sort)) grid.append(card);

    if (empty) empty.hidden = shown > 0;
    if (count) {
      count.textContent = shown === cards.length
        ? `${cards.length} yatras`
        : `${shown} of ${cards.length} yatras`;
    }

    if (!push) return;

    const url = new URL(location.href);
    for (const [key, value] of Object.entries(state)) {
      if (value) url.searchParams.set(key, value);
      else url.searchParams.delete(key);
    }
    history.replaceState(null, '', url);
  }

  // Adopt whatever the home page search bar (or a shared link) passed in.
  const incoming = new URLSearchParams(location.search);
  for (const el of controls) {
    const value = incoming.get(el.name);
    if (value && [...el.options].some((o) => o.value === value)) el.value = value;
  }

  for (const el of controls) el.addEventListener('change', () => apply());

  reset?.addEventListener('click', () => {
    for (const el of controls) el.value = '';
    apply();
  });

  // The form submits to this same page, so let JS handle it in place.
  root.addEventListener('submit', (e) => {
    e.preventDefault();
    apply();
  });

  apply({ push: false });
}
