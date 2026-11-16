# WordPress migration: how it works and how to run it

Started 16 Nov 2026. The brief (phases 1–9, owner actions, hard rules) is the owner's migration prompt; this file records
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
| "Page heading and SEO" panel in the editor sidebar | `editor.js` | h1, meta title, description, focus keyword, canonical, noindex, automatic schema switch, extra JSON-LD, social image (post meta `_artificia_*`). To be mapped to the chosen SEO plugin in Phase 4. |
| Page rendering | `inc/site.php`, `index.php` | `render_page()`. Uses `render_page_body()`, `render_layout()`, `schema_for_page()` unchanged. No `wp_head()`: WordPress adds nothing to the page. |
| `config()`, `settings()`, `image()`, `posts_recent()` | `inc/adapter.php` | The old config file, settings table, media table and posts query. `image()` and `posts_recent()` are "pluggable" in `app/lib` (`if (!function_exists(...))`). |
| `POST /lead` | `inc/site.php` | Calls the old `lead_endpoint()` unchanged: validation, honeypot, signed timestamp, rate limit, Meta CAPI, alert email, n8n with the same payload and `X-Artificia-Token`. Tables `leads` and `lead_rate` (same columns, no prefix) live in the WordPress database. |
| Leads screen, Settings → Artificia | `inc/admin.php` | The old leads inbox (list, detail, spam flag, resend to n8n, CSV in the same columns) and settings screen. Old links `/admin/leads/{id}` and `/admin/resume?f=` redirect to / are served by WordPress. |
| `/sitemap.xml`, `/robots.txt`, `/llms.txt` | `inc/site.php` | Same generators (`sitemap_xml_for`, `robots_txt`, `llms_txt_for`). WordPress's own sitemap is off. |
| URL behaviour | `inc/site.php` | `/about` and `/about/` both 200 (no slash redirect), `/home` → `/`, query strings ignored, WordPress-only addresses (feeds, archives, authors, `/about/2`) 404. |
| Importer | `inc/cli.php` | `wp artificia import`, see below. |

Permalinks: `/blog/%postname%` (posts at `/blog/{slug}`, pages at their path with no trailing slash, as today).
Uploads: `define('UPLOADS', 'uploads')`, so media lives at `/uploads/...` exactly as before; imported images keep their files and URLs.

## Import (repeatable)

```sh
wp artificia import --config=/path/to/artificia-config.php --uploads=/path/to/live/public/uploads --resumes=/path/to/live/storage/resumes
```

Reads the old database only. Matches everything by its old id, so re-running updates in place (run it again just
before go-live to catch late edits). Imports settings, media (as attachments keeping their `/uploads` files), pages and
posts (parents first; a draft placeholder page holds an address segment that had no page, e.g. `/case-studies`),
templates (as block patterns), leads (same ids) and résumés. Not yet: redirects (Phase 4, into the chosen plugin).

After a fresh WordPress install, before the first import: delete the sample page, "Hello world" post and the draft
privacy page (`wp post delete 1 2 3 --force`), set permalinks to `/blog/%postname%` and timezone `Asia/Kolkata`.

## Server layout (planned for Phase 1, needs SSH)

- A second clone of this repo **outside** `public_html`: `/home/u447231494/domains/artificiaai.com/artificia-wp`.
  `git pull` there deploys the theme. `live/` (the current site) is never touched.
- Staging WordPress from hPanel's Auto Installer on `new.artificiaai.com`.
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
