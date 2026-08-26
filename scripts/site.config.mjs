/**
 * Single source of truth for business details.
 *
 * Everything here becomes a {{site.key}} token in HTML. In Phase 7 this file
 * maps 1:1 onto the WordPress Theme Options page, so the client can edit the
 * same values without touching templates.
 */
export const site = {
  name: 'Gangotri Expeditions',
  tagline: 'Spiritual Journeys. Timeless Memories.',
  description:
    'Curated Char Dham yatras and Himalayan treks in Uttarakhand - Kedarnath, Badrinath, Chopta Tungnath and Har Ki Dun - with expert guides, comfortable stays and safe, well-planned itineraries.',

  phone: '+91 7010033899',
  phoneRaw: '+917010033899',
  whatsapp: '917010033899',
  email: 'info@gangotriexpeditions.in',

  address: 'Rishikesh, Uttarakhand, India',
  locality: 'Rishikesh',
  region: 'Uttarakhand',
  postalCode: '249201',
  country: 'IN',

  // Flat on purpose: the {{site.key}} token resolver is one level deep.
  instagram: 'https://instagram.com/',
  facebook: 'https://facebook.com/',
  youtube: 'https://youtube.com/',

  // `canonicalHost` is the one hostname the site answers on. Every other
  // spelling (www / non-www, http) 301s to it, so links and rankings never
  // split across two versions of the same page.
  //
  // prod is non-www on purpose: the live SSL certificate currently covers
  // gangotriexpeditions.in only, and www fails with a certificate error.
  // Switch this to www.gangotriexpeditions.in *after* the cert is reissued to
  // include www - not before.
  dev: {
    url: 'https://www.gangroti.codevani.com',
    canonicalHost: 'www.gangroti.codevani.com',
  },
  prod: {
    url: 'https://gangotriexpeditions.in',
    canonicalHost: 'gangotriexpeditions.in',
  },
};
