/**
 * Re-encodes the images in src/img to a byte budget, and emits the extra hero
 * widths the srcset needs.
 *
 *   node scripts/optimize-images.mjs           re-encode what is over budget
 *   node scripts/optimize-images.mjs --force   re-encode everything
 *   node scripts/optimize-images.mjs --dry     report only, write nothing
 *
 * Why this exists separately from fetch-images.mjs: that script skips any slug
 * whose file already exists, so the masters it wrote on the first run were
 * never revisited. Several of them drifted badly - three .webp files were
 * LARGER than their .jpg twin, which only happens when the encoder is asked for
 * a quality the source cannot justify. This script owns encoding; fetch-images
 * owns sourcing.
 *
 * It re-encodes from whichever of the .webp/.jpg pair carries the most data, so
 * we lose one generation rather than compounding it, and it never writes a file
 * bigger than the one already on disk.
 */
import sharp from 'sharp';
import { readdir, readFile, writeFile, stat } from 'node:fs/promises';
import { join, extname, basename } from 'node:path';

const args = process.argv.slice(2);
const FORCE = args.includes('--force');
const DRY = args.includes('--dry');

const IMG = 'src/img';

/**
 * Byte budgets per directory. These are the numbers a 4G phone can pull without
 * the hero pushing LCP past 2.5s, not arbitrary round figures: the hero is the
 * only image that blocks the largest paint, so it gets the biggest allowance
 * and everything below the fold gets far less.
 */
const CLASSES = {
  hero:    { webp: 130 * 1024, jpg: 180 * 1024, qWebp: 74, qJpg: 78 },
  gallery: { webp:  55 * 1024, jpg:  80 * 1024, qWebp: 76, qJpg: 80 },
  yatras:  { webp:  50 * 1024, jpg:  75 * 1024, qWebp: 76, qJpg: 80 },
};

/** Extra widths emitted for the hero so phones stop downloading a 1920px file. */
const HERO_WIDTHS = [960, 1440];

const kb = (n) => `${(n / 1024).toFixed(0)}KB`;

const encode = (pipeline, fmt, q) => (fmt === 'webp'
  ? pipeline.clone().webp({ quality: q, effort: 6, smartSubsample: true }).toBuffer()
  : pipeline.clone().jpeg({ quality: q, mozjpeg: true, progressive: true }).toBuffer());

/**
 * Fits the image to its budget, spending grain before it spends quality.
 *
 * These masters came off Wikimedia and several are heavily grained. On a grainy
 * source the quality slider barely moves the file size - the encoder is paying
 * for noise, not detail - so dropping to q42 mangles the picture and still
 * misses the budget. A 3x3 median pass removes the grain and takes 40-55% off
 * at the SAME quality, which is why it is tried before the quality ladder
 * rather than after it. Clean images never reach that branch and stay pristine.
 */
async function encodeToBudget(pipeline, fmt, startQ, budget) {
  let out = await encode(pipeline, fmt, startQ);
  if (out.length <= budget) return { buf: out, q: startQ, denoised: false };

  const denoised = pipeline.clone().median(3);
  out = await encode(denoised, fmt, startQ);
  if (out.length <= budget) return { buf: out, q: startQ, denoised: true };

  for (let q = startQ - 6; q >= 48; q -= 6) {
    out = await encode(denoised, fmt, q);
    if (out.length <= budget) return { buf: out, q, denoised: true };
  }
  return { buf: out, q: 48, denoised: true };
}

let saved = 0;
let rewritten = 0;

for (const [dir, budgets] of Object.entries(CLASSES)) {
  const files = (await readdir(join(IMG, dir))).sort();

  // Pair the two encodes of each image up, so a slug is handled once.
  const slugs = [...new Set(files
    .filter((f) => /\.(webp|jpg)$/i.test(f))
    // Width-suffixed heroes are outputs of this script, not masters.
    .filter((f) => !/-\d+w\.(webp|jpg)$/i.test(f))
    .map((f) => basename(f, extname(f))))];

  for (const slug of slugs) {
    const paths = { webp: join(IMG, dir, `${slug}.webp`), jpg: join(IMG, dir, `${slug}.jpg`) };
    const before = {};
    for (const [fmt, p] of Object.entries(paths)) {
      before[fmt] = await stat(p).then((s) => s.size).catch(() => 0);
    }

    const overBudget = (before.webp > budgets.webp) || (before.jpg > budgets.jpg);
    const needsWidths = dir === 'hero';
    if (!overBudget && !needsWidths && !FORCE) continue;

    // Re-encode from the fattest surviving copy: it holds the most detail.
    const source = before.webp >= before.jpg ? paths.webp : paths.jpg;
    const master = sharp(await readFile(source));
    const meta = await master.metadata();

    if (overBudget || FORCE) {
      for (const fmt of ['webp', 'jpg']) {
        if (!before[fmt]) continue;
        const { buf, q, denoised } = await encodeToBudget(master, fmt, budgets[`q${fmt === 'webp' ? 'Webp' : 'Jpg'}`], budgets[fmt]);

        // Never trade a smaller file for a bigger one just because we re-ran.
        if (buf.length >= before[fmt]) {
          console.log(`  keep  ${dir}/${slug}.${fmt}  ${kb(before[fmt])} (re-encode was ${kb(buf.length)})`);
          continue;
        }
        if (!DRY) await writeFile(paths[fmt], buf);
        saved += before[fmt] - buf.length;
        rewritten++;
        console.log(`  ${DRY ? 'would' : 'write'} ${dir}/${slug}.${fmt}  ${kb(before[fmt])} -> ${kb(buf.length)}  q${q}${denoised ? ' denoised' : ''}`);
      }
    }

    // The hero <img> carries a srcset; the narrow widths are generated here so
    // a phone pulls ~40KB instead of the full 1920px master.
    if (needsWidths) {
      for (const w of HERO_WIDTHS) {
        if (w >= meta.width) continue;
        const scaled = sharp(await readFile(source)).resize(w);
        const budget = Math.round(budgets.webp * (w / meta.width) ** 1.4);
        const { buf } = await encodeToBudget(scaled, 'webp', budgets.qWebp, budget);
        const out = join(IMG, dir, `${slug}-${w}w.webp`);
        if (!DRY) await writeFile(out, buf);
        console.log(`  ${DRY ? 'would' : 'write'} ${dir}/${slug}-${w}w.webp  ${kb(buf.length)}`);
      }
    }
  }
}

console.log(`\n  ${DRY ? 'would rewrite' : 'rewrote'} ${rewritten} file(s), saving ${kb(saved)}\n`);
