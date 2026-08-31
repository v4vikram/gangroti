/**
 * Builds the front-end assets into the WordPress theme.
 *
 *   node scripts/build-theme.mjs          unminified
 *   node scripts/build-theme.mjs --prod   minified
 *
 * The theme shares this pipeline with the static build rather than carrying a
 * copy of the CSS and JS. That is the one thing kept in sync after the
 * conversion: markup moves to PHP once and stays there, but a design change
 * still means editing one stylesheet, not two.
 *
 * Filenames are content-hashed and recorded in assets/manifest.json, which
 * inc/assets.php reads - so assets can be cached for a year and a deploy still
 * takes effect on the next request.
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
const THEME = 'wp-theme/gangotri';
const OUT = join(THEME, 'assets');

const hash = (buf) => createHash('sha256').update(buf).digest('hex').slice(0, 8);
const kb = (n) => `${(n / 1024).toFixed(1)} KB`;

const t0 = Date.now();

// Only the build's own output is cleared. Anything hand-placed in the theme -
// screenshot.png, languages - is left alone.
await rm(OUT, { recursive: true, force: true });
await mkdir(join(OUT, 'css'), { recursive: true });
await mkdir(join(OUT, 'js'), { recursive: true });

/* ------------------------------------------------------------------- css -- */

const cssTmp = join(OUT, 'css/main.css');
await run(process.execPath, [
  'node_modules/@tailwindcss/cli/dist/index.mjs',
  '-i', join(SRC, 'css/main.css'),
  '-o', cssTmp,
  ...(PROD ? ['--minify'] : []),
]);

const cssBuf = await readFile(cssTmp);
const cssName = `main.${hash(cssBuf)}.css`;
await writeFile(join(OUT, 'css', cssName), cssBuf);
await rm(cssTmp);

/* -------------------------------------------------------------------- js -- */

const jsResult = await esbuild.build({
  entryPoints: [join(SRC, 'js/main.js')],
  bundle: true,
  format: 'esm',
  target: ['chrome111', 'firefox128', 'safari16.4'],
  minify: PROD,
  entryNames: 'main.[hash]',
  outdir: join(OUT, 'js'),
  metafile: true,
  legalComments: 'none',
  // Empty on purpose: in WordPress the endpoint and nonce arrive at runtime
  // from wp_localize_script, so one bundle works on staging and production
  // without a rebuild. This constant is only the static build's fallback.
  define: { FORM_ENDPOINT: '""' },
});

const [jsPath] = Object.keys(jsResult.metafile.outputs).filter((f) => f.endsWith('.js'));
const jsBuf = await readFile(jsPath);
const jsName = jsPath.split(/[\\/]/).pop();

/* ---------------------------------------------------------------- static -- */

for (const dir of ['fonts', 'img']) {
  if (existsSync(join(SRC, dir))) {
    await cp(join(SRC, dir), join(OUT, dir), {
      recursive: true,
      // Notes that live beside the assets stay out of the theme.
      filter: (src) => !/\.(md|txt)$/i.test(src),
    });
  }
}

if (existsSync(join(SRC, 'icons'))) {
  await cp(join(SRC, 'icons'), join(OUT, 'icons'), { recursive: true });
}

/* -------------------------------------------------------------- manifest -- */

// Only the faces the first paint needs are preloaded. Preloading every weight
// would compete with the hero image for the same early bandwidth.
const fonts = existsSync(join(OUT, 'fonts'))
  ? (await readdir(join(OUT, 'fonts'))).filter((f) => /^(inter-var|poppins-700)\.woff2$/.test(f))
  : [];

await writeFile(
  join(OUT, 'manifest.json'),
  `${JSON.stringify({ css: `css/${cssName}`, js: `js/${jsName}`, fonts }, null, 2)}\n`,
);

console.log(
  `${PROD ? 'prod' : 'dev '} theme  ${Date.now() - t0}ms  |  ` +
  `css ${kb(cssBuf.length)}  js ${kb(jsBuf.length)}  fonts ${fonts.length}  -> ${OUT}`,
);
