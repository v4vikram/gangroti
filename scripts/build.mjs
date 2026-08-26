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

async function expandIncludes(html, depth = 0) {
  if (depth > 5) throw new Error('@include nested more than 5 deep - probably a loop');
  const re = /<!--\s*@include\s+([\w./-]+)\s*-->/g;
  if (!re.test(html)) return html;
  re.lastIndex = 0;

  let out = html;
  for (const [tag, file] of [...html.matchAll(re)]) {
    const partial = await readFile(join(SRC, 'partials', file), 'utf8');
    out = out.replace(tag, await expandIncludes(partial, depth + 1));
  }
  return out;
}

async function buildHtml(assets) {
  const sprite = existsSync(join(SRC, 'icons/sprite.svg'))
    ? await readFile(join(SRC, 'icons/sprite.svg'), 'utf8')
    : '';

  // Standalone pages, plus one page per entry in a data collection. The
  // collection templates are the ones that become single-*.php in Phase 7.
  const jobs = (await readdir(SRC))
    .filter((f) => f.endsWith('.html'))
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
    html = await expandEach(html);
    html = await expandIncludes(html);

    html = html
      .replace(/<!--\s*@sprite\s*-->/g, sprite)
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

  return { pages: jobs.length, size: total };
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
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript
  AddOutputFilterByType DEFLATE application/javascript application/json
  AddOutputFilterByType DEFLATE image/svg+xml
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

  const kb = (n) => `${(n / 1024).toFixed(1)} KB`;
  console.log(
    `${PROD ? 'prod' : 'dev '} build  ${Date.now() - t0}ms  |  ` +
    `html ${html.pages}p ${kb(html.size)}  css ${kb(css.size)}  js ${kb(js.size)}` +
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
