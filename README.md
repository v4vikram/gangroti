# Gangotri Expeditions

Static front end for [gangotriexpeditions.in](https://gangotriexpeditions.in).
Converts to a custom WordPress theme in Phase 7.

- **Dev:** https://www.gangroti.codevani.com (auto-deploys from `develop`, always `noindex`)
- **Prod:** https://gangotriexpeditions.in (deploys from `main`, manual approval)

## Stack

| | |
| --- | --- |
| CSS | Tailwind v4 (CSS-first config, no `tailwind.config.js`) + custom components |
| JS | Vanilla ES modules, bundled with esbuild. **No jQuery.** |
| Icons | Self-hosted SVG sprite, inlined at build (52 icons, ~4 KB gz) |
| Slider | Custom, `src/js/slider.js` (~3 KB gz) - no Owl, Slick or Splide |
| Fonts | Self-hosted woff2, latin subset only (62 KB total) |
| Images | WebP + JPG pairs, freely licensed (see `src/img/CREDITS.md`) |

Tailwind v4 targets Chrome 111+ / Safari 16.4+ / Firefox 128+.

## Commands

```bash
npm install
npm run dev          # build + watch + dev server on :3000 with live reload
npm run build        # dev build   -> dist/  (unminified, noindex)
npm run build:prod   # prod build  -> dist/  (minified, indexable)

npm run assets       # regenerate fonts + icons + images
npm run fonts        # re-download self-hosted woff2 files
npm run icons        # rebuild src/icons/sprite.svg
npm run images       # re-download licensed photography
```

## Authoring HTML

Pages live in `src/*.html`. The build understands:

| Token | Does |
| --- | --- |
| `<!--@include header.html-->` | inlines `src/partials/header.html` (nests up to 5 deep) |
| `<!--@sprite-->` | inlines the SVG icon sprite |
| `{{site.phone}}` | substitutes from `scripts/site.config.mjs` |
| `%%CSS%%` / `%%JS%%` | content-hashed asset paths |
| `%%SITE_URL%%` | dev or prod origin, per build |
| `%%ROBOTS%%` | `noindex` meta on dev, nothing on prod |

Business details (phone, WhatsApp, address, social) live **only** in
`scripts/site.config.mjs`. In Phase 7 that file maps onto the WordPress Theme
Options page so the client edits the same values.

## Why the dev site is noindex

`www.gangroti.codevani.com` serves the same content as the live domain. If Google
indexed both, they would compete as duplicates and split rankings. The dev build
therefore emits `Disallow: /` in `robots.txt`, a `noindex` meta tag, and an
`X-Robots-Tag` header via `.htaccess`. CI fails the build if any of those go
missing.

## Deployment

GitHub Actions builds on every push and PR, then deploys the WordPress theme
over FTPS. Only `wp-content/themes/gangotri/` is synced - WordPress core, the
uploads folder and the database belong to the server.

Required secrets. Add them as **repository** secrets: the deploy jobs declare a
GitHub environment, and a secret stored in the wrong environment is not an
error - the step just receives an empty string and fails with
`Input required and not supplied: server`.

| Secret | Used by |
| --- | --- |
| `DEV_FTP_SERVER`, `DEV_FTP_USERNAME`, `DEV_FTP_PASSWORD`, `DEV_FTP_THEME_PATH` | `develop` -> dev |
| `PROD_FTP_SERVER`, `PROD_FTP_USERNAME`, `PROD_FTP_PASSWORD`, `PROD_FTP_THEME_PATH` | `main` -> prod |

The `*_THEME_PATH` values are relative to where the FTP account lands, not to
the server root. A cPanel FTP account created for a domain opens inside that
domain's folder already, so the path is `/wp-content/themes/gangotri/` - adding
the domain name again points at a folder that does not exist.

Server and username come from cPanel -> FTP Accounts -> Configure FTP Client.
Passwords are not recorded anywhere in this repository and cannot be read back
out of GitHub; reset one in cPanel and update the secret.

Add required reviewers to the `production` environment in repo settings so live
deploys need an approval.

## Branches

```
main       production
develop    dev site
feature/*  PR into develop
```

## Images

`src/img/` ships real photography of the actual destinations, pulled from
Wikimedia Commons under public-domain or Creative Commons licences and cleared
for commercial use. Attributions are in `src/img/CREDITS.md` and must stay
published somewhere on the site (a footer credits link is enough).

To swap in the client's own photos, drop a file with the same name and
dimensions into the same folder - no markup changes needed. `src/img/logo.svg`
and `favicon.svg` are placeholders pending the real brand vector.
