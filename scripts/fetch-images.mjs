/**
 * Pulls real, freely-licensed photography of the actual destinations from
 * Wikimedia Commons, resizes it to the sizes the layouts need, and emits
 * WebP + JPG pairs plus src/img/CREDITS.md.
 *
 * Why Commons and not Google Images: Commons results carry an explicit licence
 * (public domain or CC), so they are safe on a commercial site. Google Images
 * results are mostly rights-reserved stock.
 *
 * Re-runnable: `npm run images`
 */
import { writeFile, mkdir } from 'node:fs/promises';
import sharp from 'sharp';

const UA = 'GangotriExpeditions-SiteBuild/1.0 (contact: info@gangotriexpeditions.in)';
const API = 'https://commons.wikimedia.org/w/api.php';

// Licences we will ship. Anything else is skipped.
const OK_LICENCE = /^(public domain|cc0|cc by(-sa)? ?[0-9.]*|pd-)/i;

const WANTED = [
  // Hero slider - wide
  { slug: 'hero-1', dir: 'hero', w: 1920, h: 1080, q: 'Kedarnath temple Himalaya' },
  { slug: 'hero-2', dir: 'hero', w: 1920, h: 1080, q: 'Tungnath temple Chopta' },
  { slug: 'hero-3', dir: 'hero', w: 1920, h: 1080, q: 'Gangotri Uttarakhand' },

  // Yatra cards - 4:3
  { slug: 'kedarnath-yatra',       dir: 'yatras', w: 800, h: 600, q: 'Kedarnath Temple' },
  { slug: 'badrinath-yatra',       dir: 'yatras', w: 800, h: 600, q: 'Badrinath Temple' },
  { slug: 'chopta-tungnath-yatra', dir: 'yatras', w: 800, h: 600, q: 'Chandrashila Tungnath trek' },
  { slug: 'har-ki-dun-trek',       dir: 'yatras', w: 800, h: 600, q: 'Har Ki Dun valley' },
  { slug: 'valley-of-flowers',     dir: 'yatras', w: 800, h: 600, q: 'Valley of Flowers Uttarakhand' },
  { slug: 'char-dham-yatra',       dir: 'yatras', w: 800, h: 600, q: 'Gangotri temple Uttarakhand' },

  // Gallery - square
  { slug: 'gallery-1', dir: 'gallery', w: 800, h: 800, q: 'Yamunotri temple' },
  { slug: 'gallery-2', dir: 'gallery', w: 800, h: 800, q: 'Uttarakhand trekking Himalaya' },
  { slug: 'gallery-3', dir: 'gallery', w: 800, h: 800, q: 'Rishikesh Ganga Uttarakhand' },
  { slug: 'gallery-4', dir: 'gallery', w: 800, h: 800, q: 'Deoria Tal Uttarakhand' },
  { slug: 'gallery-5', dir: 'gallery', w: 800, h: 800, q: 'Nanda Devi Himalaya peak' },
  { slug: 'gallery-6', dir: 'gallery', w: 800, h: 800, q: 'Auli Uttarakhand snow' },
];

const strip = (html = '') => html.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();

/** Commons occasionally resets a connection mid-download; retry with backoff. */
async function fetchRetry(url, tries = 4) {
  for (let i = 1; i <= tries; i++) {
    try {
      const res = await fetch(url, { headers: { 'User-Agent': UA } });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res;
    } catch (err) {
      if (i === tries) throw err;
      await new Promise((r) => setTimeout(r, i * 800));
    }
  }
}

async function search(query) {
  const url = `${API}?${new URLSearchParams({
    action: 'query', generator: 'search', gsrsearch: query,
    gsrnamespace: '6', gsrlimit: '12',
    prop: 'imageinfo', iiprop: 'url|size|extmetadata', iiurlwidth: '1920',
    format: 'json', formatversion: '2',
  })}`;
  const res = await fetchRetry(url);
  return (await res.json()).query?.pages ?? [];
}

/** First landscape-ish, large-enough, correctly-licensed candidate. */
function pick(pages, minW, minH) {
  for (const page of pages) {
    const info = page.imageinfo?.[0];
    if (!info) continue;
    if (!/\.(jpe?g|png)$/i.test(page.title)) continue;
    if (info.width < minW || info.height < minH) continue;

    const licence = strip(info.extmetadata?.LicenseShortName?.value);
    if (!OK_LICENCE.test(licence)) continue;

    return {
      title: page.title.replace(/^File:/, ''),
      url: info.thumburl ?? info.url,
      page: info.descriptionurl,
      licence,
      author: strip(info.extmetadata?.Artist?.value) || 'Unknown',
    };
  }
  return null;
}

const credits = [];
let ok = 0;

for (const item of WANTED) {
  const pages = await search(item.q);
  const hit = pick(pages, item.w, Math.round(item.h * 0.75));

  if (!hit) {
    console.warn(`  ! no licensed match for "${item.q}" (${item.slug})`);
    continue;
  }

  const buf = Buffer.from(await (await fetchRetry(hit.url)).arrayBuffer());
  await mkdir(`src/img/${item.dir}`, { recursive: true });

  const base = sharp(buf).resize(item.w, item.h, { fit: 'cover', position: 'attention' });

  // The hero is the LCP element, so it gets a hard byte budget: step the
  // quality down until it fits. Busy images (snow, foliage) need the help.
  const isHero = item.dir === 'hero';
  const budget = isHero ? 200 * 1024 : Infinity;

  const encode = async (fmt, q) => fmt === 'webp'
    ? base.clone().webp({ quality: q }).toBuffer()
    : base.clone().jpeg({ quality: q, mozjpeg: true }).toBuffer();

  const underBudget = async (fmt, start) => {
    let out;
    for (let q = start; q >= 45; q -= 7) {
      out = await encode(fmt, q);
      if (out.length <= budget) return out;
    }
    return out;
  };

  const webp = await underBudget('webp', isHero ? 68 : 76);
  const jpg = await underBudget('jpg', isHero ? 74 : 80);

  await writeFile(`src/img/${item.dir}/${item.slug}.webp`, webp);
  await writeFile(`src/img/${item.dir}/${item.slug}.jpg`, jpg);

  console.log(`  ${item.dir}/${item.slug}  ${item.w}x${item.h}  webp ${(webp.length / 1024).toFixed(0)}KB / jpg ${(jpg.length / 1024).toFixed(0)}KB  [${hit.licence}]`);
  credits.push({ ...item, ...hit });
  ok++;
}

const md = `# Image credits

Photography sourced from Wikimedia Commons. Every file below is public domain or
Creative Commons licensed and cleared for commercial use. CC BY / CC BY-SA files
require the attribution shown here to stay published somewhere on the site
(the footer credits link is enough).

Replace any of these with the client's own photography by dropping a file with
the same name into the same folder - no markup changes needed.

| File | Source | Author | Licence |
| --- | --- | --- | --- |
${credits.map(c => `| \`img/${c.dir}/${c.slug}\` | [${c.title}](${c.page}) | ${c.author} | ${c.licence} |`).join('\n')}
`;

await writeFile('src/img/CREDITS.md', md);
console.log(`\n${ok}/${WANTED.length} images -> src/img/  (credits written to src/img/CREDITS.md)`);
