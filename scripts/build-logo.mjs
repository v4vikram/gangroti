/**
 * Turns the supplied logo artwork into web assets.
 *
 * The original src/img/logo.svg is not really vector - it is a 1024x1024 PNG
 * base64'd inside an <svg> wrapper, which makes it 413 KB on every page and
 * blurry at small sizes. It also bakes the "GANGOTRI EXPEDITIONS" wordmark into
 * the image, which the header then repeats as live text.
 *
 * So we split it: the triangle mark becomes the header/footer icon, and the
 * wordmark stays as real text (selectable, translatable, styleable, free).
 *
 * Re-runnable: `npm run logo`
 */
import { readFile, writeFile } from 'node:fs/promises';
import sharp from 'sharp';

const SRC = 'src/img/logo.svg';

/**
 * Knocks the white background out, so the mark can sit on the dark green
 * footer as well as on white.
 *
 * The ink here is opaque artwork photographed onto white, not semi-transparent
 * paint, so this is a mask - not an un-premultiply. Un-premultiplying would
 * read gold (#E0B235) as only 79% opaque and leave the whole mark washed out.
 *
 * Distance from white drives the mask: past HI it is solid ink, below LO it is
 * background, and the narrow ramp between keeps the anti-aliased edges smooth.
 */
const LO = 8;
const HI = 40;

async function whiteToAlpha(input) {
  const { data, info } = await sharp(input)
    .ensureAlpha()
    .raw()
    .toBuffer({ resolveWithObject: true });

  const out = Buffer.from(data);

  for (let i = 0; i < out.length; i += 4) {
    const d = Math.max(255 - out[i], 255 - out[i + 1], 255 - out[i + 2]);
    out[i + 3] = d <= LO ? 0
      : d >= HI ? 255
      : Math.round(((d - LO) / (HI - LO)) * 255);
  }

  return sharp(out, { raw: { width: info.width, height: info.height, channels: 4 } })
    .png()
    .toBuffer();
}

/**
 * Snaps every pixel to the nearest brand colour.
 *
 * The supplied artwork is a soft render: what should be three flat colours is
 * actually thousands of near-identical shades, which is why it would not
 * compress (40 KB for a 256px logo). Snapping removes that noise, so the mark
 * both looks sharper and drops to a few KB. Alpha is left untouched, so the
 * anti-aliased edges stay smooth.
 */
const BRAND = [
  [0x1e, 0x5a, 0x3a], // primary green
  [0x1c, 0x3d, 0x5a], // deep blue
  [0xe0, 0xb2, 0x35], // accent gold
];

async function snapToBrand(input) {
  const { data, info } = await sharp(input).ensureAlpha().raw()
    .toBuffer({ resolveWithObject: true });

  const out = Buffer.from(data);

  for (let i = 0; i < out.length; i += 4) {
    if (out[i + 3] === 0) continue;

    let best = 0;
    let bestDist = Infinity;
    for (let c = 0; c < BRAND.length; c++) {
      const [r, g, b] = BRAND[c];
      const d = (out[i] - r) ** 2 + (out[i + 1] - g) ** 2 + (out[i + 2] - b) ** 2;
      if (d < bestDist) { bestDist = d; best = c; }
    }

    [out[i], out[i + 1], out[i + 2]] = BRAND[best];
  }

  return sharp(out, { raw: { width: info.width, height: info.height, channels: 4 } })
    .png()
    .toBuffer();
}

/** Same silhouette, painted flat white - for use on the dark footer. */
async function toWhite(input) {
  const { data, info } = await sharp(input).ensureAlpha().raw()
    .toBuffer({ resolveWithObject: true });

  const out = Buffer.from(data);
  for (let i = 0; i < out.length; i += 4) {
    out[i] = out[i + 1] = out[i + 2] = 255;
  }

  return sharp(out, { raw: { width: info.width, height: info.height, channels: 4 } })
    .png()
    .toBuffer();
}

const svg = await readFile(SRC, 'utf8');
const original = Buffer.from(svg.match(/base64,([^"']+)/)[1], 'base64');

// Row bands measured from the artwork: mark 201-657, GANGOTRI 692-768,
// EXPEDITIONS 788-857. Crop the mark with a little air, then trim the rest.
const mark = await sharp(original)
  .extract({ left: 0, top: 185, width: 1024, height: 490 })
  .toBuffer();

// Rendered at ~56px, so 256px covers 2x and 3x screens with room to spare.
// Resize BEFORE snapping: resampling interpolates between colours, so snapping
// first and scaling after would just reintroduce the in-between shades.
const transparent = await snapToBrand(
  await sharp(await whiteToAlpha(mark))
    .trim({ threshold: 5 })
    .resize({ height: 256, fit: 'inside' })
    .toBuffer(),
);

const { width, height } = await sharp(transparent).metadata();
console.log(`  mark cropped, trimmed, snapped: ${width}x${height}`);

const variants = [
  { name: 'logo-mark',       buf: transparent },
  { name: 'logo-mark-white', buf: await toWhite(transparent) },
];

// Lossless: three flat colours compress to almost nothing, and lossy would
// reintroduce exactly the fringing the snap just removed.
for (const v of variants) {
  const webp = await sharp(v.buf).webp({ lossless: true, effort: 6 }).toBuffer();
  await writeFile(`src/img/${v.name}.webp`, webp);
  console.log(`  ${v.name}.webp`.padEnd(28) + `${(webp.length / 1024).toFixed(1)} KB`);
}

// Full lockup for schema.org / Open Graph only - never rendered inline.
// Search engines want a raster URL here, and PNG is the safest thing to hand
// them, so this one stays PNG.
const lockup = await sharp(await whiteToAlpha(original))
  .trim({ threshold: 5 })
  .resize({ width: 512, fit: 'inside' })
  .flatten({ background: '#ffffff' }) // OG previews render on unknown backgrounds
  .png({ compressionLevel: 9, palette: true, quality: 90 })
  .toBuffer();

await writeFile('src/img/logo-lockup.png', lockup);
console.log('  logo-lockup.png'.padEnd(28) + `${(lockup.length / 1024).toFixed(1)} KB`);

console.log(`\n  was: logo.svg ${(Buffer.byteLength(svg) / 1024).toFixed(0)} KB on every page`);
