/**
 * The package listing filter.
 *
 * This used to hide and re-sort the cards in place, which worked while every
 * package shipped in one page of HTML. On WordPress the archive is paginated,
 * and hiding rendered cards would quietly ignore everything on page two - a
 * filter that lies about how many results there are is worse than a filter
 * that costs a page load.
 *
 * So the server owns filtering now (inc/query.php reads the same parameter
 * names), and this only removes the "press Apply" step: changing a control
 * submits the form. With JavaScript off the noscript button does the same
 * thing, and the URLs are identical either way, so a filtered view stays
 * linkable and crawlable.
 */

export function initFilter() {
  const form = document.querySelector('[data-filter]');
  if (!form) return;

  // Let the browser navigate; the server renders the filtered page.
  for (const control of form.querySelectorAll('select, input[type="search"]')) {
    control.addEventListener('change', () => {
      // Drop empty controls so the URL carries only what is actually set.
      for (const el of form.elements) {
        if (el.name && !el.value) el.disabled = true;
      }
      form.submit();
    });
  }

  // A filtered page is a real URL, so "clear" is a link to the unfiltered one
  // and needs no JavaScript at all. Nothing to wire up here.
}
