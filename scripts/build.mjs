/**
 * Static build for the Gangotri Expeditions front end.
 *
 *   node scripts/build.mjs            dev build  (unminified, noindex)
 *   node scripts/build.mjs --prod     prod build (minified, indexable)
 *   node scripts/build.mjs --watch --serve
 *
 * HTML authoring features:
 *   <!--@include header.html-->   inlines src/partials/header.html (recursive)
 *   <!--@sprite-->                inlines the SVG icon sprite
 *   {{site.phone}}                substitutes from site.config.mjs
 *   %%CSS%% / %%JS%%              content-hashed asset paths (cache busting)
 *   %%ROBOTS%%                    noindex meta on dev, nothing on prod
 */
import { readFile, writeFile, mkdir, rm, cp, readdir } from 'node:fs/promises';
import { existsSync, watch } from 'node:fs';
import { createHash } from 'node:crypto';
import { createServer } from 'node:http';
import { dirname, extname, join } from 'node:path';
import { execFile } from 'node:child_process';
import { promisify } from 'node:util';
import { minify as minifyHtml } from 'html-minifier-terser';
import * as esbuild from 'esbuild';
import { site } from './site.config.mjs';

const run = promisify(execFile);
const args = process.argv.slice(2);
const PROD = args.includes('--prod');
const WATCH = args.includes('--watch');
const SERVE = args.includes('--serve');

const SRC = 'src';
const OUT = 'dist';
const env = PROD ? site.prod : site.dev;

/** Data-driven pages: one output per entry, rendered from a shared template. */
const COLLECTIONS = [
  { data: 'yatras', template: 'yatra.html', path: (item) => `yatras/${item.slug}.html` },
];

const hash = (buf) => createHash('sha256').update(buf).digest('hex').slice(0, 8);

/** Built on dev, withheld from prod. Kept out of the sitemap either way. */
const NON_PUBLIC = new Set(['styleguide.html']);

/** No canonical URL, so no sitemap entry - these are not landing pages. */
const UNLISTED = new Set(['404.html', ...NON_PUBLIC]);

/* ------------------------------------------------------------------ steps */

async function buildCss() {
  const out = join(OUT, 'css/main.css');
  await run(process.execPath, [
    'node_modules/@tailwindcss/cli/dist/index.mjs',
    '-i', join(SRC, 'css/main.css'),
    '-o', out,
    ...(PROD ? ['--minify'] : []),
  ]);
  const buf = await readFile(out);
  const name = `main.${hash(buf)}.css`;
  await writeFile(join(OUT, 'css', name), buf);
  await rm(out);
  return { path: `/css/${name}`, size: buf.length };
}

async function buildJs() {
  const result = await esbuild.build({
    entryPoints: [join(SRC, 'js/main.js')],
    bundle: true,
    format: 'esm',
    target: ['chrome111', 'firefox128', 'safari16.4'],
    minify: PROD,
    sourcemap: !PROD,
    entryNames: 'main.[hash]', // esbuild appends the .js itself
    outdir: join(OUT, 'js'),
    metafile: true,
    legalComments: 'none',
    // Compile-time constant, so the dead branch is dropped from the bundle
    // rather than shipped. Empty until there is a server (see site.config.mjs).
    define: { FORM_ENDPOINT: JSON.stringify(env.formEndpoint ?? '') },
  });
  const [file] = Object.keys(result.metafile.outputs).filter((f) => f.endsWith('.js'));
  const buf = await readFile(file);
  return { path: '/' + file.split('/').slice(1).join('/'), size: buf.length };
}

async function copyStatic() {
  // Notes that live beside the assets (CREDITS.md, README.txt) stay out of dist.
  const shipped = (src) => !/\.(md|txt)$/i.test(src);

  for (const dir of ['fonts', 'img']) {
    if (existsSync(join(SRC, dir))) {
      await cp(join(SRC, dir), join(OUT, dir), { recursive: true, filter: shipped });
    }
  }
  if (existsSync(join(SRC, 'icons'))) {
    await cp(join(SRC, 'icons'), join(OUT, 'icons'), { recursive: true });
  }
}

/**
 * `<!--@each yatras:yatra-card.html limit=3-->` renders src/partials/yatra-card.html
 * once per entry in src/data/yatras.json, substituting {{item.key}}.
 *
 * This exists so a card is authored once. In Phase 7 the same partial becomes
 * the WordPress template part and the JSON becomes the `yatra` post type, so
 * the loop is the only thing that changes.
 */
const dataCache = new Map();

async function loadData(name) {
  if (!dataCache.has(name)) {
    const items = JSON.parse(await readFile(join(SRC, 'data', `${name}.json`), 'utf8'));

    for (const item of items) {
      if (typeof item.price === 'number') {
        item.priceFormatted = item.price.toLocaleString('en-IN');
      }

      // Built here rather than in the template: a repeated block cannot emit a
      // comma-separated JSON array without a trailing comma, and invalid
      // JSON-LD is silently dropped by search engines.
      if (Array.isArray(item.itinerary)) {
        item.itineraryLd = JSON.stringify(
          item.itinerary.map((day, i) => ({
            '@type': 'ListItem',
            position: i + 1,
            name: day.title,
            description: day.text,
          })),
        );
      }
    }
    dataCache.set(name, items);
  }
  return dataCache.get(name);
}

/**
 * `<!--@options yatras:destination-->` emits an <option> per distinct value of
 * that field across the collection.
 *
 * Filter and enquiry dropdowns are generated rather than hand-written so they
 * cannot drift from the packages that actually exist - a filter offering a
 * destination with no packages behind it just returns an empty grid.
 */
async function expandOptions(html) {
  const re = /<!--\s*@options\s+(\w+):(\w+)\s*-->/g;
  let out = html;

  for (const [tag, name, field] of [...html.matchAll(re)]) {
    const values = [...new Set((await loadData(name)).map((item) => item[field]))]
      .filter(Boolean)
      .sort();

    out = out.replace(
      tag,
      values.map((v) => `<option value="${v}">${v}</option>`).join('\n            '),
    );
  }
  return out;
}

async function expandEach(html) {
  const re = /<!--\s*@each\s+(\w+):([\w.-]+)(?:\s+limit=(\d+))?\s*-->/g;
  let out = html;

  for (const [tag, name, partial, limit] of [...html.matchAll(re)]) {
    const items = await loadData(name);
    const tpl = await readFile(join(SRC, 'partials', partial), 'utf8');
    const slice = limit ? items.slice(0, Number(limit)) : items;

    out = out.replace(tag, slice.map((item) => renderItem(tpl, item)).join('\n'));
  }
  return out;
}

/**
 * `<!--@list itinerary-->...<!--@endlist-->` repeats its body once per entry in
 * that array on the current item, exposing each entry as {{it.key}} (or {{it}}
 * for an array of plain strings) and its 1-based position as {{n}}.
 */
function expandLists(tpl, item) {
  return tpl.replace(
    /<!--\s*@list\s+(\w+)\s*-->([\s\S]*?)<!--\s*@endlist\s*-->/g,
    (_, key, body) => {
      const entries = item[key];
      if (!Array.isArray(entries)) return '';

      return entries
        .map((entry, i) =>
          body
            .replace(/\{\{it\.(\w+)\}\}/g, (m, k) => entry?.[k] ?? '')
            .replace(/\{\{it\}\}/g, typeof entry === 'string' ? entry : '')
            .replace(/\{\{n\}\}/g, String(i + 1)),
        )
        .join('');
    },
  );
}

function renderItem(tpl, item) {
  return expandLists(tpl, item).replace(
    /\{\{item\.(\w+)\}\}/g,
    (m, key) => (item[key] == null ? '' : item[key]),
  );
}

/**
 * `<!--@include enquiry-form.html prefix=home-->` inlines the partial and
 * substitutes {{prefix}} inside it.
 *
 * The parameters exist so one partial can appear twice on a page without
 * clashing: element ids have to stay unique, or a <label for> points at the
 * wrong input and screen readers follow it to the wrong field.
 */
async function expandIncludes(html, depth = 0) {
  if (depth > 5) throw new Error('@include nested more than 5 deep - probably a loop');
  const re = /<!--\s*@include\s+([\w./-]+)((?:\s+\w+=[\w-]+)*)\s*-->/g;
  if (!re.test(html)) return html;
  re.lastIndex = 0;

  let out = html;
  for (const [tag, file, rawParams] of [...html.matchAll(re)]) {
    let partial = await readFile(join(SRC, 'partials', file), 'utf8');

    for (const pair of rawParams.trim().split(/\s+/).filter(Boolean)) {
      const [key, value] = pair.split('=');
      partial = partial.replaceAll(`{{${key}}}`, value);
    }

    out = out.replace(tag, await expandIncludes(partial, depth + 1));
  }
  return out;
}

/**
 * Cuts the icon sprite down to the symbols a given page actually uses.
 *
 * The sprite is inlined into every page - which is right, an external sprite
 * costs a blocking request before any icon paints - but all 52 symbols were
 * going into all 12 pages, and no page references more than about half of
 * them. Subsetting keeps the zero-request win and drops the rest.
 *
 * Safe because nothing builds a <use href> at runtime: the only references are
 * the ones already in the markup at this point, after every partial and loop
 * has been expanded.
 */
function subsetSprite(sprite, html) {
  if (!sprite) return '';

  const used = new Set([...html.matchAll(/href="#(i-[\w-]+)"/g)].map((m) => m[1]));
  if (!used.size) return '';

  const open = sprite.slice(0, sprite.indexOf('>') + 1);
  const symbols = [...sprite.matchAll(/<symbol id="(i-[\w-]+)"[\s\S]*?<\/symbol>/g)];
  const kept = symbols.filter(([, id]) => used.has(id)).map(([markup]) => markup);

  return `${open}${kept.join('')}</svg>`;
}

async function buildHtml(assets) {
  const sprite = existsSync(join(SRC, 'icons/sprite.svg'))
    ? await readFile(join(SRC, 'icons/sprite.svg'), 'utf8')
    : '';

  // Standalone pages, plus one page per entry in a data collection. The
  // collection templates are the ones that become single-*.php in Phase 7.
  const jobs = (await readdir(SRC))
    .filter((f) => f.endsWith('.html'))
    // The styleguide is a working reference for us, not a page for visitors.
    // On dev it is handy and noindexed anyway; shipping it to prod would put
    // 56 KB of component swatches in Google's index under a real URL.
    .filter((f) => !(PROD && NON_PUBLIC.has(f)))
    .map((file) => ({ out: file, src: join(SRC, file) }));

  for (const { data, template, path } of COLLECTIONS) {
    const src = join(SRC, 'templates', template);
    if (!existsSync(src)) continue; // template not written yet - skip, don't crash

    for (const item of await loadData(data)) {
      jobs.push({ out: path(item), src, item });
    }
  }

  let total = 0;

  for (const job of jobs) {
    let html = await readFile(job.src, 'utf8');

    if (job.item) html = renderItem(html, job.item);
    // Order matters: includes first, so that @each and @options directives
    // living inside a partial (the footer package list, the form's yatra
    // dropdown) are still expanded rather than shipped as raw comments.
    html = await expandIncludes(html);
    html = await expandEach(html);
    html = await expandOptions(html);

    html = html
      .replace(/<!--\s*@sprite\s*-->/g, subsetSprite(sprite, html))
      .replace(/%%CSS%%/g, assets.css.path)
      .replace(/%%JS%%/g, assets.js.path)
      .replace(/%%SITE_URL%%/g, env.url)
      .replace(/%%ROBOTS%%/g, PROD ? '' : '<meta name="robots" content="noindex, nofollow">')
      .replace(/\{\{site\.(\w+)\}\}/g, (m, key) => site[key] ?? m);

    if (WATCH && SERVE) html = html.replace('</body>', `${LIVE_RELOAD}</body>`);

    if (PROD) {
      html = await minifyHtml(html, {
        collapseWhitespace: true,
        removeComments: true,
        minifyCSS: true,
        minifyJS: true,
        removeRedundantAttributes: true,
        sortAttributes: true,
        sortClassName: true,
      });
    }

    const dest = join(OUT, job.out);
    await mkdir(dirname(dest), { recursive: true });
    await writeFile(dest, html);
    total += Buffer.byteLength(html);
  }

  return { pages: jobs.length, size: total, paths: jobs.map((j) => j.out) };
}

/**
 * Deletes anything under dist/img that no built page or stylesheet points at.
 *
 * src/img holds working masters as well as shipped assets - logo.svg is a
 * 1024px PNG in an SVG wrapper that build-logo.mjs slices into the real logos,
 * and several .jpg twins lost their last reference when the markup moved to
 * plain <img src="*.webp">. copyStatic cannot tell those apart, so it copies
 * the lot and we sweep afterwards against what the output actually references.
 *
 * Nothing in src/js reaches for an image path, so the built HTML and CSS are
 * the complete picture. If that ever changes, add the source to `haystack`.
 */
async function pruneAssets() {
  const files = [];
  const walk = async (dir) => {
    for (const entry of await readdir(dir, { withFileTypes: true })) {
      const full = join(dir, entry.name);
      if (entry.isDirectory()) await walk(full);
      else files.push(full);
    }
  };
  await walk(OUT);

  const haystack = (await Promise.all(
    files.filter((f) => /\.(html|css)$/.test(f)).map((f) => readFile(f, 'utf8')),
  )).join('\n');

  const referenced = new Set(haystack.match(/\/img\/[A-Za-z0-9._/-]+/g) ?? []);

  let freed = 0;
  let dropped = 0;
  for (const file of files) {
    const url = '/' + file.split(/[\\/]/).slice(1).join('/');
    if (!url.startsWith('/img/') || referenced.has(url)) continue;
    freed += (await readFile(file)).length;
    await rm(file);
    dropped++;
  }
  return { dropped, freed };
}

/**
 * robots.txt already promises a sitemap at this path; until now it 404ed.
 * Built from the pages that were actually emitted, so it cannot list a URL
 * that does not exist.
 */
async function buildSitemap(pages) {
  const today = new Date().toISOString().slice(0, 10);

  const urls = pages
    .filter((p) => !UNLISTED.has(p))
    // Must match the page's own <link rel="canonical"> character for character,
    // or the two disagree about which URL is the real one.
    .map((p) => (p === 'index.html' ? '/' : '/' + p.replace(/\\/g, '/').replace(/\.html$/, '')))
    .sort()
    .map((path) => `  <url>\n    <loc>${env.url}${path}</loc>\n    <lastmod>${today}</lastmod>\n  </url>`)
    .join('\n');

  await writeFile(
    join(OUT, 'sitemap.xml'),
    `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${urls}\n</urlset>\n`,
  );
}

async function buildRobots() {
  // The dev mirror must never be indexable - two live copies of the same
  // content would split rankings between codevani and the real domain.
  const robots = PROD
    ? `User-agent: *\nAllow: /\n\nSitemap: ${env.url}/sitemap.xml\n`
    : `User-agent: *\nDisallow: /\n`;
  await writeFile(join(OUT, 'robots.txt'), robots);
}

/**
 * Apache config for cPanel. Covers the three things shared hosting does not do
 * on its own: force one canonical hostname, compress text, and cache assets.
 */
async function buildHtaccess() {
  const host = env.canonicalHost;

  const noindex = PROD ? '' : `
# --- Dev mirror: never indexable -------------------------------------------
# Belt and braces alongside robots.txt and the meta tag, because this header
# also covers PDFs, images and anything else that has no <head>.
<IfModule mod_headers.c>
  Header set X-Robots-Tag "noindex, nofollow"
</IfModule>
`;

  await writeFile(join(OUT, '.htaccess'), `# Generated by scripts/build.mjs - do not edit on the server, it is overwritten
# on every deploy. Change scripts/build.mjs instead.

Options -Indexes
DirectoryIndex index.html
ErrorDocument 404 /404.html
${noindex}
# --- One canonical hostname, always HTTPS ----------------------------------
<IfModule mod_rewrite.c>
  RewriteEngine On

  RewriteCond %{HTTPS} !=on [OR]
  RewriteCond %{HTTP_HOST} !^${host.replace(/\./g, '\\.')}$ [NC]
  RewriteRule ^ https://${host}%{REQUEST_URI} [R=301,L]

  # /about -> /about.html, so URLs stay extensionless and match the future
  # WordPress permalinks. No redirect, so there is nothing to un-map later.
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME}.html -f
  RewriteRule ^(.*)$ $1.html [L]
</IfModule>

# --- Compression ------------------------------------------------------------
# Brotli first: it beats gzip by roughly 15-20% on this HTML, which is mostly
# repeated markup and an inline SVG sprite. Apache picks whichever the browser
# advertises, so the deflate block below stays as the fallback rather than a
# duplicate. Images are already compressed - running them through either filter
# burns CPU to add bytes.
<IfModule mod_brotli.c>
  AddOutputFilterByType BROTLI_COMPRESS text/html text/plain text/css text/javascript
  AddOutputFilterByType BROTLI_COMPRESS application/javascript application/json
  AddOutputFilterByType BROTLI_COMPRESS image/svg+xml application/xml
</IfModule>

<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript
  AddOutputFilterByType DEFLATE application/javascript application/json
  AddOutputFilterByType DEFLATE image/svg+xml application/xml
</IfModule>

# --- Caching ----------------------------------------------------------------
# CSS and JS filenames carry a content hash, so a year is safe: a change
# produces a new filename rather than a stale cache.
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresDefault                        "access plus 1 month"
  ExpiresByType text/html               "access plus 0 seconds"
  ExpiresByType text/css                "access plus 1 year"
  ExpiresByType application/javascript  "access plus 1 year"
  ExpiresByType text/javascript         "access plus 1 year"
  ExpiresByType font/woff2              "access plus 1 year"
  ExpiresByType image/webp              "access plus 6 months"
  ExpiresByType image/jpeg              "access plus 6 months"
  ExpiresByType image/png               "access plus 6 months"
  ExpiresByType image/svg+xml           "access plus 6 months"
</IfModule>

<IfModule mod_headers.c>
  <FilesMatch "\\.(css|js|woff2)$">
    Header set Cache-Control "public, max-age=31536000, immutable"
  </FilesMatch>

  # --- Security ------------------------------------------------------------
  Header set X-Content-Type-Options "nosniff"
  Header set Referrer-Policy "strict-origin-when-cross-origin"
  Header set X-Frame-Options "SAMEORIGIN"
</IfModule>
`);
}

/* ------------------------------------------------------------------ build */

async function build() {
  const t0 = Date.now();
  await rm(OUT, { recursive: true, force: true });
  await mkdir(join(OUT, 'css'), { recursive: true });

  const [css, js] = await Promise.all([buildCss(), buildJs()]);
  await copyStatic();
  const html = await buildHtml({ css, js });
  await buildRobots();
  await buildHtaccess();
  await buildSitemap(html.paths);
  // Last, so it sees every reference the finished output makes.
  const pruned = await pruneAssets();

  const kb = (n) => `${(n / 1024).toFixed(1)} KB`;
  console.log(
    `${PROD ? 'prod' : 'dev '} build  ${Date.now() - t0}ms  |  ` +
    `html ${html.pages}p ${kb(html.size)}  css ${kb(css.size)}  js ${kb(js.size)}` +
    `  |  pruned ${pruned.dropped} asset(s) ${kb(pruned.freed)}` +
    `${PROD ? '' : '  |  noindex'}`
  );
}

/* ------------------------------------------------- watch + dev server ---- */

const LIVE_RELOAD = `<script>new EventSource('/__reload').onmessage=()=>location.reload()</script>`;

const MIME = {
  '.html': 'text/html; charset=utf-8', '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8', '.svg': 'image/svg+xml',
  '.woff2': 'font/woff2', '.json': 'application/json', '.webp': 'image/webp',
  '.avif': 'image/avif', '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg',
  '.png': 'image/png', '.ico': 'image/x-icon', '.txt': 'text/plain; charset=utf-8',
  '.map': 'application/json',
};

function serve(port = 3000) {
  const clients = new Set();

  createServer(async (req, res) => {
    const url = decodeURIComponent(req.url.split('?')[0]);

    if (url === '/__reload') {
      res.writeHead(200, {
        'Content-Type': 'text/event-stream',
        'Cache-Control': 'no-cache',
        Connection: 'keep-alive',
      });
      res.write('\n');
      clients.add(res);
      req.on('close', () => clients.delete(res));
      return;
    }

    // /about -> /about.html, / -> /index.html
    const candidates = url.endsWith('/')
      ? [join(OUT, url, 'index.html')]
      : [join(OUT, url), join(OUT, `${url}.html`)];

    for (const file of candidates) {
      if (!existsSync(file) || !extname(file)) continue;
      res.writeHead(200, { 'Content-Type': MIME[extname(file)] ?? 'application/octet-stream' });
      return res.end(await readFile(file));
    }

    res.writeHead(404, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end(existsSync(join(OUT, '404.html')) ? await readFile(join(OUT, '404.html')) : 'Not found');
  }).listen(port, () => console.log(`\n  dev server  http://localhost:${port}\n`));

  return () => {
    for (const c of clients) c.write('data: reload\n\n');
  };
}

/* ------------------------------------------------------------------- boot */

await build();

if (WATCH) {
  const reload = SERVE ? serve() : () => {};
  let timer;
  watch(SRC, { recursive: true }, (_, file) => {
    if (file?.includes('fonts.css')) return; // generated, would loop
    clearTimeout(timer);
    timer = setTimeout(async () => {
      try {
        await build();
        reload();
      } catch (err) {
        console.error('build failed:', err.message);
      }
    }, 60);
  });
  console.log('  watching src/ ...');
}
