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

  dev: { url: 'https://www.gangotri.codevani.com' },
  prod: { url: 'https://gangotriexpeditions.in' },
};
