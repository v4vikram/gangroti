/**
 * Pulls the prose out of the static pages into src/data/pages.json, which
 * scripts/setup-site.php then imports as WordPress pages.
 *
 * Only the copy is extracted, not the section scaffolding: pages whose layout
 * matters (About, Services, Gallery, Contact) get a PHP page template instead,
 * so their design survives the editor. What lands in post_content is the text a
 * client should be able to edit without breaking anything.
 *
 *   node scripts/extract-pages.mjs
 */
import { readFile, writeFile } from 'node:fs/promises';

/**
 * @type {Array<{slug:string,title:string,template?:string,from?:string,selector?:'prose'|'faq'}>}
 */
const PAGES = [
  // Layout-driven: the template owns the markup, the page just needs to exist
  // so it has a URL, a menu entry and a title.
  { slug: 'about', title: 'About Us', template: 'page-about.php' },
  { slug: 'services', title: 'Services', template: 'page-services.php' },
  { slug: 'gallery', title: 'Gallery', template: 'page-gallery.php' },
  { slug: 'contact', title: 'Contact', template: 'page-contact.php' },
  { slug: 'faq', title: 'Frequently Asked Questions', template: 'page-faq.php' },

  // Prose: the text is the page, so it goes into the editor where it can be
  // corrected by a lawyer without touching a template.
  { slug: 'privacy', title: 'Privacy Policy', from: 'src/privacy.html', selector: 'prose' },
  { slug: 'terms', title: 'Terms & Conditions', from: 'src/terms.html', selector: 'prose' },
  { slug: 'cancellation', title: 'Cancellation & Refund Policy', from: 'src/cancellation.html', selector: 'prose' },
];

/** Everything inside the first <div class="... prose-legal ..."> block. */
function extractProse(html) {
  const open = html.search(/<div class="[^"]*prose-legal[^"]*">/);
  if (open === -1) return '';

  const start = html.indexOf('>', open) + 1;

  // Walk the tree rather than regex-matching a closing tag: the block contains
  // nested divs (the table wrapper), so the first </div> is not the end of it.
  let depth = 1;
  let i = start;

  while (depth > 0 && i < html.length) {
    const nextOpen = html.indexOf('<div', i);
    const nextClose = html.indexOf('</div>', i);

    if (nextClose === -1) break;

    if (nextOpen !== -1 && nextOpen < nextClose) {
      depth++;
      i = nextOpen + 4;
    } else {
      depth--;
      i = nextClose + 6;
    }
  }

  return html
    .slice(start, i - 6)
    // The build's token syntax means nothing to WordPress.
    .replace(/\{\{site\.phoneRaw\}\}/g, '')
    .replace(/\{\{site\.phone\}\}/g, '')
    .replace(/\{\{site\.email\}\}/g, '')
    .replace(/\{\{site\.name\}\}/g, 'Gangotri Expeditions')
    .replace(/\{\{site\.address\}\}/g, '')
    .trim();
}

const out = [];

for (const page of PAGES) {
  let content = '';

  if (page.from) {
    content = extractProse(await readFile(page.from, 'utf8'));

    if (!content) {
      console.warn(`  ! no prose block found in ${page.from}`);
    }
  }

  out.push({
    slug: page.slug,
    title: page.title,
    template: page.template ?? '',
    content,
  });

  console.log(
    `  ${page.slug.padEnd(14)} ${page.template ? `template ${page.template}` : `${content.length} chars of copy`}`,
  );
}

await writeFile('src/data/pages.json', `${JSON.stringify(out, null, 2)}\n`);
console.log(`\n${out.length} pages -> src/data/pages.json`);
