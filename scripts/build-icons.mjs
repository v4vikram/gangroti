/**
 * Builds src/icons/sprite.svg from lucide-static (line UI icons) + simple-icons
 * (brand marks) + a hand-drawn trishul. Only the icons listed here ship.
 * Re-runnable: `npm run icons`
 */
import { readFile, writeFile, mkdir } from 'node:fs/promises';
import * as simpleIcons from 'simple-icons';

// UI icons — lucide, stroke-based (matches the brand guide's "minimal, line-based" note)
const LUCIDE = [
  // brand pillars
  'mountain', 'mountain-snow', 'tent', 'footprints', 'backpack', 'compass', 'route',
  // contact / CTA
  'phone', 'phone-call', 'mail', 'map-pin', 'send', 'message-circle', 'external-link',
  // trip facts
  'calendar', 'clock', 'users', 'indian-rupee', 'trending-up', 'thermometer',
  'sun', 'snowflake', 'bed', 'utensils', 'bus', 'ticket',
  // trust / proof
  'star', 'shield-check', 'badge-check', 'award', 'circle-check-big', 'heart', 'quote', 'camera',
  // interface
  'search', 'menu', 'x', 'check', 'plus', 'minus', 'info', 'triangle-alert',
  'arrow-right', 'arrow-left', 'chevron-down', 'chevron-left', 'chevron-right',
];

// Brand marks — simple-icons, solid fill
const BRANDS = { whatsapp: 'siWhatsapp', instagram: 'siInstagram', facebook: 'siFacebook', youtube: 'siYoutube' };

// Custom — not in any icon set. Trishul (trident): the brand's spirituality mark.
const CUSTOM = {
  trishul: `<path d="M12 3v18"/><path d="M9.9 5.6 12 3l2.1 2.6"/><path d="M6 8v5"/><path d="M4.4 9.6 6 8l1.6 1.6"/><path d="M18 8v5"/><path d="M16.4 9.6 18 8l1.6 1.6"/><path d="M6 13c0 2.4 2.7 3.6 6 3.6s6-1.2 6-3.6"/>`,
};

const STROKE = 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';
const symbols = [];

for (const name of LUCIDE) {
  const svg = await readFile(`node_modules/lucide-static/icons/${name}.svg`, 'utf8');
  const inner = svg.match(/<svg[^>]*>([\s\S]*)<\/svg>/)[1].trim();
  symbols.push(`<symbol id="i-${name}" viewBox="0 0 24 24" ${STROKE}>${inner}</symbol>`);
}

for (const [name, key] of Object.entries(BRANDS)) {
  const icon = simpleIcons[key];
  if (!icon) throw new Error(`simple-icons: ${key} not found`);
  symbols.push(`<symbol id="i-${name}" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="${icon.path}"/></symbol>`);
}

for (const [name, inner] of Object.entries(CUSTOM)) {
  symbols.push(`<symbol id="i-${name}" viewBox="0 0 24 24" ${STROKE}>${inner}</symbol>`);
}

await mkdir('src/icons', { recursive: true });
const sprite = `<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden">${symbols.join('')}</svg>`;
await writeFile('src/icons/sprite.svg', sprite);

console.log(`${symbols.length} icons -> src/icons/sprite.svg  (${(Buffer.byteLength(sprite) / 1024).toFixed(1)} KB)`);
