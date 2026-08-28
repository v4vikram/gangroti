# Gangotri Expeditions - WordPress theme

Classic PHP theme. No page builder, no ACF, no plugin dependencies of any
kind - the package fields, the repeaters and the enquiry endpoint are all in
`inc/`.

## Build the assets first

The theme ships without its stylesheet; both are generated from `src/`.

```bash
npm install
npm run build:theme:prod
```

That writes `assets/css/main.<hash>.css`, `assets/js/main.<hash>.js`,
`assets/manifest.json`, the fonts, images and icon sprite, and a copy of
`src/data/yatras.json` for the importer. `inc/assets.php` enqueues whatever the
manifest names, so a stale file from an earlier build can never be served.

If you deploy without running it, the admin says so on every screen rather than
quietly serving an unstyled site.

## Install

1. Copy `theme/gangotri-expeditions/` to `wp-content/themes/` and activate it.
   Activation registers the post types and flushes the rewrite rules once.
2. **Settings -> Permalinks**: choose "Post name". The package URLs
   (`/yatras/madmaheshwar-trek/`) need pretty permalinks.
3. **Tools -> Import packages**: creates the packages from the bundled JSON.
   Safe to re-run - it matches on slug and updates rather than duplicating.
   With WP-CLI: `wp gangotri import`.
4. **Appearance -> Customize -> Business details**: phone, email, WhatsApp,
   address and the social links. Everything the old `site.config.mjs` held.
5. Create the pages and assign their templates in **Page Attributes**:

   | Page       | Template | Slug           |
   | ---------- | -------- | -------------- |
   | About Us   | About    | `about`        |
   | Services   | Services | `services`     |
   | Gallery    | Gallery  | `gallery`      |
   | Contact    | Contact  | `contact`      |
   | FAQ        | FAQ      | `faq`          |
   | Privacy Policy, Terms, Cancellation Policy | *(default)* | `privacy`, `terms`, `cancellation` |

   The slugs matter: the header, footer and enquiry form link to them directly.
6. **Appearance -> Menus**: build the Primary menu. Until you do, the theme
   falls back to the six links above so the site is never headless.
7. **Settings -> Reading**: set the homepage to a static page and the posts page
   to a page called Journal (or whatever you prefer) for the blog.

## Where things live

| What | Where |
| ---- | ----- |
| Package fields (the schema) | `inc/fields.php` |
| Package editing UI | `inc/meta-boxes.php` + `assets/admin/` |
| CPT and taxonomies | `inc/post-types.php` |
| Archive filtering and sorting | `inc/query.php` |
| Enquiry endpoint, storage, email | `inc/enquiry.php` |
| JSON-LD and Open Graph | `inc/schema.php` |
| Business details | `inc/options.php` |
| Helpers used by templates | `inc/template-tags.php` |

**Adding a package field** means editing `ge_fields()` and nothing else. The
meta box, the sanitising and the importer all read that array.

## Enquiries

The form posts to `admin-ajax.php` as `action=ge_enquiry`. Every submission is
stored as a private `ge_enquiry` post **before** the email is attempted, so a
misconfigured mail host loses a notification rather than a customer. Read them
under **Enquiries** in the admin.

Protections: a nonce, a honeypot field, and a per-IP rate limit of 8 an hour.

## Notes for whoever maintains this

- **The static build in `src/` is the origin, not a parallel copy.** Templates
  are PHP now; `src/` still owns the CSS, the JavaScript and the images. Do not
  edit `src/*.html` expecting it to change the site.
- **Tailwind scans the PHP.** `src/css/main.css` has
  `@source "../../theme/**/*.php"`. A class used only in PHP would otherwise be
  purged out of the stylesheet.
- **The icon sprite is inlined whole** on every page (~18 KB raw, ~4 KB
  compressed). The static build subset it per page against the finished markup;
  PHP cannot do that without buffering the whole response, which is not worth
  it at this size.
- **Images**: uploads get their srcset from the sizes registered in
  `inc/setup.php`. `scripts/optimize-images.mjs` now only covers the theme's own
  bundled assets - the hero, the gallery placeholders and the logos.
