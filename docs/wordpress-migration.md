# WordPress migration: how it works and how to run it

Started 16 Nov 2026; WordPress is live on artificiaai.com since 17 Nov 2026, 14:17 IST. The brief (phases 1–9, owner actions, hard rules) is the owner's migration prompt; this file records
how it is being built and the commands to run. Status lives in [CONTEXT.md](../CONTEXT.md) §6.4 and [PROGRESS.md](../PROGRESS.md).

## The idea in one paragraph

WordPress stores the content, media, users, settings and leads. It does **not** re-implement the design. The theme
[`wordpress/artificia`](../wordpress/artificia) is a thin adapter that feeds WordPress content to the PHP site's own code
in the same checkout: `app/sections` and `app/views` (every section and page template), `app/lib` (helpers, schema,
robots.txt, llms.txt and the whole lead pipeline) and `public/assets` (CSS, JS, fonts, images, still served at
`/assets/...`). So visitors get the same HTML, byte for byte, and any design change made in this repo shows on both
sites until the old one is retired.

| WordPress side | Where | What it replaces |
|---|---|---|
| One block per section type (`artificia/hero`, `artificia/statement`, ...) | `inc/blocks.php`, `editor.js` | The old admin's section editor. Fields come from `app/sections/registry.php`; a block stores them in one `fields` attribute, the same shape the old site saved. Rich-text fields (Text block body, Text and image body, Case study challenge) are edited as paragraph / heading (h2, h3) / list blocks inside the section. No other blocks are allowed, so editors cannot break the markup. |
| "Page heading and SEO" panel in the editor sidebar | `editor.js` | h1, meta title, description, focus keyword, canonical, noindex, automatic schema switch, extra JSON-LD, social image (post meta `_artificia_*`). These native fields remain the authoritative SEO controls; no new SEO plugin was added, preserving the original public output. |
| Page rendering | `inc/site.php`, `index.php` | `render_page()`. Uses `render_page_body()`, `render_layout()`, `schema_for_page()` unchanged. No `wp_head()`: WordPress adds nothing to the page. |
| `config()`, `settings()`, `image()`, `posts_recent()` | `inc/adapter.php` | The old config file, settings table, media table and posts query. `image()` and `posts_recent()` are "pluggable" in `app/lib` (`if (!function_exists(...))`). |
| `POST /lead` | `inc/site.php` | Calls the old `lead_endpoint()` unchanged: validation, honeypot, signed timestamp, rate limit, Meta CAPI, alert email, n8n with the same payload and `X-Artificia-Token`. Tables `leads` and `lead_rate` (same columns, no prefix) live in the WordPress database. |
| Leads screen, Settings → Artificia | `inc/admin.php` | The old leads inbox (list, detail, spam flag, resend to n8n, CSV in the same columns) and settings screen. Old links `/admin/leads/{id}` and `/admin/resume?f=` redirect to / are served by WordPress. |
| `/sitemap.xml`, `/robots.txt`, `/llms.txt` | `inc/site.php` | Same generators (`sitemap_xml_for`, `robots_txt`, `llms_txt_for`). WordPress's own sitemap is off. |
| URL behaviour | `inc/site.php` | `/about` and `/about/` both 200 (no slash redirect), `/home` → `/`, query strings ignored, WordPress-only addresses (feeds, archives, authors, `/about/2`) 404. |
| Importer | `inc/cli.php` | `wp artificia import`, see below. |

Permalinks: `/blog/%postname%` (posts at `/blog/{slug}`, pages at their path with no trailing slash, as today).
Uploads: `define('UPLOADS', 'uploads')`, so media lives at `/uploads/...` exactly as before; imported images keep their files and URLs.

## Import (repeatable before launch; protect new production edits)

```sh
wp artificia import --config=/path/to/artificia-config.php --uploads=/path/to/live/public/uploads --resumes=/path/to/live/storage/resumes
```

Reads the old database only. Matches everything by its old id, so re-running updates in place before go-live catches late edits. **Do not rerun the full import after launch:** it would overwrite new WordPress content/settings or old-ID lead edits. Use a reviewed delta reconciliation instead. Imports settings, media (as attachments keeping their `/uploads` files), pages and
posts (parents first; a draft placeholder page holds an address segment that had no page, e.g. `/case-studies`),
templates (as block patterns), leads (same ids) and résumés. Redirects are imported into the separate WordPress database with the original schema and shared lookup behavior. The importer refuses to run if its source and destination database identities match.

After a fresh WordPress install, before the first import: delete the sample page, "Hello world" post and the draft
privacy page (`wp post delete 1 2 3 --force`), set permalinks to `/blog/%postname%` and timezone `Asia/Kolkata`.

## Server layout (17 Nov hosting continuation)

- Shared code **outside** `public_html`: `/home/u447231494/domains/artificiaai.com/artificia-wp`.
  Root auto-deployment is disabled because the earlier hPanel deployment replaced `public_html`, including untracked uploads and resumes. The current PHP site is now directly in `public_html`; keep its files and database for rollback. Use controlled updates to shared code, never a static/root replacement deployment.
- `new.artificiaai.com` points to `public_html/wp`; it was protected with HTTP Basic Auth and noindex during staging and now canonically redirects to the production domain. WordPress uses the separate database `u447231494_artificiawp`; the original `u447231494_artificia` database is read only during import.
- Symlinks: `wp-content/themes/artificia` → `artificia-wp/wordpress/artificia`, and `<wordpress>/assets` → `artificia-wp/public/assets`.
- `wp-config.php`: `WP_ENVIRONMENT_TYPE` (`staging`, later `production`), `UPLOADS` = `uploads`,
  `ARTIFICIA_APP_SECRET` and `ARTIFICIA_MAIL_FROM` copied from the old config (never printed or committed).
- Deny PHP execution in `<wordpress>/uploads` (as `public/uploads/.htaccess` does today).
- Go-live (Phase 8): WordPress in `public_html/wp/`, root `.htaccess` rewrite target `live/` → `wp/`. Rollback = point it back to `live/`.

## Local development

`D:\artificia-wp-local` (outside the repo): WordPress 7.1.4 core, `wp-cli.phar`, `router.php`, `LOCAL-LOGIN.txt`.
Junctions: `site\wp-content\themes\artificia` → this repo's `wordpress\artificia`, `site\assets` → `public\assets`.
Run `php -S 127.0.0.1:8090 -t site router.php` from that folder. The old site from this checkout runs on :8081
(`ARTIFICIA_CONFIG` set) for side-by-side checks. The local copy has the n8n webhook blanked so test leads stay local.

## Live routing, backups and validation (17 Nov)

The root `.htaccess` maps public requests into `wp/`, excluding `.well-known/`, blocking dot/private paths and handling `/wp-admin` before the internal rewrite. WordPress `home`/`siteurl` are `https://artificiaai.com`, environment `production`, timezone `Asia/Kolkata`, `blog_public=1`. Staging Basic Auth and the server-level noindex directive were removed at launch. The original public SEO renderer, native SEO fields and Tools > Redirects retain the prior output without a new SEO plugin.

The private source is a real main-branch Git checkout with the existing server read-only Git key. Core/theme/assets links resolve to this checkout. Its ignored `storage/resumes` and WordPress `uploads` survive Git pulls. Minor core auto-updates are enabled. Daily private SQL/files/config/routing backups run at 05:00 IST (`30 23 * * *` UTC). The SQL dumper uses consistent snapshots, UTC, utf8mb4, compatible SQL mode and checked compressed writes; a local restore matched all 15 tables and 721 rows/hashes. Private production backup execution and gzip validation passed. No archive is automatically deleted; review storage periodically.

Rollback command: `bash /home/u447231494/domains/artificiaai.com/rollback-wordpress.sh`. It restores `backups/pre-wordpress-20261117/root-before-wordpress.htaccess` verbatim, returning to the original PHP app directly in `public_html`. Retain both databases and old files for at least 30 days. Lead IDs after launch start in a reserved high range so a final reconciliation can preserve in-flight old-site submissions without collisions; all 9 original rows matched byte-for-value after cutover. Never discard newer WordPress leads when rolling back.

Validation: 46/46 URL statuses and redirect targets; all page SEO, sitemap/robots/llms, and all 80 assets match the baseline. Eight desktop/mobile screenshots match page dimensions and main geometry. The sole document difference is invisible whitespace between privacy-page block elements. Administrator login, 20 published pages plus draft parent, all 269 blocks, 15 redirects, and the 9 original leads pass. Five staged form handlers saved correctly; a real multipart careers PDF upload returned 200 and invalid input returned 422. Staging tests were removed; source settings are preserved. External email/n8n/CAPI delivery was not retested; its original settings/pipeline remain in place.

Single matched Lighthouse mobile runs: home Performance 39 -> 42, contact 52 -> 81; Accessibility/SEO stay 100/100. The existing Performance 90 goal remains unmet; no visual redesign was authorized. An initial parallel crawl produced six transient 500s, also seen once before migration; all passed on retry, followed by zero failures in 24 requests at concurrency 3 and a serial 46-address check. Private PHP logging is enabled; no cause was established from the available logs. Keep this observation in future hosting reviews rather than assuming it was fixed.

Three original career resume PDFs were already missing after the preceding Hostinger root replacement. Their database references were retained; the 13 Nov server archive contains no matching current files. No unrelated old resume was substituted. The original file archive/config/SQL and deployment tools remain privately under `backups/pre-wordpress-20261117/`.

Local QA evidence: `C:\Users\jayti\AppData\Local\Temp\artificia-baseline-20261117` and `artificia-lighthouse-baseline`. Owner login: `D:\artificia-wp-local\HOSTINGER-LOGIN.txt` (private, never committed).
