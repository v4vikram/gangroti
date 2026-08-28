/**
 * Builds the WordPress theme's assets.
 *
 *   node scripts/build-theme.mjs           unminified, for local work
 *   node scripts/build-theme.mjs --prod    minified, for deploy
 *
 * The theme's PHP is authored by hand and lives in theme/; only the things a
 * browser downloads are generated. That split matters: a generated theme is a
 * theme nobody can hand-edit, and the whole point of moving to WordPress is
 * that the templates become the thing you edit.
 *
 * What this produces under theme/<name>/assets/:
 *
 *   css/main.<hash>.css   Tailwind, scanning the PHP rather than the old HTML
 *   js/main.<hash>.js     the same bundle the static site used
 *   manifest.json         names the current pair, so inc/assets.php enqueues
 *                         exactly those and never globs for leftovers
 *   fonts/ img/ icons/    copied from src/
 *   data/yatras.json      so inc/import.php can seed the CPT
 */
import { readFile, writeFile, mkdir, rm, cp, readdir } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { join } from 'node:path';
import { execFile } from 'node:child_process';
import { promisify } from 'node:util';
import * as esbuild from 'esbuild';

const run = promisify(execFile);
const PROD = process.argv.includes('--prod');

const SRC = 'src';
const THEME = 'theme/gangotri-expeditions';
const ASSETS = join(THEME, 'assets');

const hash = (buf) => createHash('sha256').update(buf).digest('hex').slice(0, 8);
const kb = (n) => `${(n / 1024).toFixed(1)} KB`;

/** Removes previous builds so an old hash cannot linger next to the new one. */
async function cleanHashed(dir) {
  if (!existsSync(dir)) return;
  for (const file of await readdir(dir)) {
    if (/^main\.[A-Za-z0-9]+\.(css|js|js\.map)$/.test(file)) {
      await rm(join(dir, file));
    }
  }
}

async function buildCss() {
  await mkdir(join(ASSETS, 'css'), { recursive: true });
  await cleanHashed(join(ASSETS, 'css'));

  const tmp = join(ASSETS, 'css/main.tmp.css');

  await run(process.execPath, [
    'node_modules/@tailwindcss/cli/dist/index.mjs',
    '-i', join(SRC, 'css/main.css'),
    '-o', tmp,
    ...(PROD ? ['--minify'] : []),
  ]);

  const buf = await readFile(tmp);
  const name = `main.${hash(buf)}.css`;
  await writeFile(join(ASSETS, 'css', name), buf);
  await rm(tmp);

  return { path: `css/${name}`, size: buf.length };
}

async function buildJs() {
  await mkdir(join(ASSETS, 'js'), { recursive: true });
  await cleanHashed(join(ASSETS, 'js'));

  const result = await esbuild.build({
    entryPoints: [join(SRC, 'js/main.js')],
    bundle: true,
    format: 'esm',
    target: ['chrome111', 'firefox128', 'safari16.4'],
    minify: PROD,
    sourcemap: !PROD,
    entryNames: 'main.[hash]',
    outdir: join(ASSETS, 'js'),
    metafile: true,
    legalComments: 'none',
    // Empty on purpose: the endpoint depends on the install's domain, so
    // inc/assets.php prints window.GE_AJAX ahead of the bundle and form.js
    // falls back to it. Baking a URL in would tie the build to one site.
    define: { FORM_ENDPOINT: JSON.stringify('') },
  });

  const [file] = Object.keys(result.metafile.outputs).filter((f) => f.endsWith('.js'));
  const buf = await readFile(file);

  return { path: file.split(/[\\/]/).slice(-2).join('/'), size: buf.length };
}

async function copyStatic() {
  // CREDITS.md and README.txt are notes to us, not assets to ship.
  const shipped = (src) => !/\.(md|txt)$/i.test(src);

  for (const dir of ['fonts', 'img']) {
    await rm(join(ASSETS, dir), { recursive: true, force: true });
    await cp(join(SRC, dir), join(ASSETS, dir), { recursive: true, filter: shipped });
  }

  await mkdir(join(ASSETS, 'icons'), { recursive: true });
  await cp(join(SRC, 'icons/sprite.svg'), join(ASSETS, 'icons/sprite.svg'));

  // The seed data for inc/import.php.
  await mkdir(join(THEME, 'data'), { recursive: true });
  await cp(join(SRC, 'data/yatras.json'), join(THEME, 'data/yatras.json'));
}

const t0 = Date.now();

const [css, js] = await Promise.all([buildCss(), buildJs()]);
await copyStatic();

await writeFile(
  join(ASSETS, 'manifest.json'),
  JSON.stringify({ css: css.path, js: js.path }, null, 2) + '\n',
);

console.log(
  `${PROD ? 'prod' : 'dev '} theme  ${Date.now() - t0}ms  |  ` +
  `css ${kb(css.size)}  js ${kb(js.size)}  ->  ${ASSETS}`,
);
