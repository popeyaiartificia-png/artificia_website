# artificiaai.com Step 1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended)
> or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`)
> syntax for tracking.

**Goal:** Ship Step 1 of the spec. That's the new artificiaai.com with designed, section-based pages, the
PHP admin, lead capture and tracking, redirects, a sitemap and robots.txt, and schema. It's built and
reviewed locally, then deployed to Hostinger staging, then switched over.

**Architecture:**
- Plain PHP 8.x with no framework and no Composer. PDO/MySQL and a single front controller.
- A page is a row whose `sections` JSON is an ordered list of `{type, fields}` taken from a section
  registry. Each section type has one PHP partial.
- The admin builds its forms from the same registry.
- The repo deploys into `public_html/live`. Only `public/` is web-served; the other folders are denied.

**Tech stack:**

- **Server and storage:** PHP 8.2+ with the `gd`, `pdo_mysql`, `curl`, `dom`, `mbstring` and `fileinfo`
  extensions, and MariaDB/MySQL.
- **Front end:** vanilla JS, with GSAP 3.13.0 self-hosted, and Quill 2.0.3 (admin rich text only).
- **Tests:** a ~40-line PHP test runner. There's no PHPUnit, to keep zero dependencies.

**Spec:** `docs/superpowers/specs/2026-10-27-artificiaai-site-design.md`. Read it before starting any
task.

**Out of scope:**
- **Step 2 work:** the spreadsheet upload, the restore UI, the SEO score check, the broken-link check and
  the internal-link picker. Revision rows *are* stored from Step 1.
- **The n8n lead workflow,** which is a separate project. It consumes the payload defined in Task 9.

## Global Constraints

**Hosting and paths**
- Hostinger Premium. There's no Node.js on the server. Account `u447231494`, document root
  `/home/u447231494/domains/artificiaai.com/public_html`.
- Never touch `domains/popeyai.com` or `domains/popeyai.io`. Never call the Hostinger `hosting_deploy`
  operation, because it overwrites the whole site.
- The config file sits outside the repo and outside `public_html`. Its path is
  `getenv('ARTIFICIA_CONFIG') ?: dirname(__DIR__, 3) . '/artificia-config.php'`, resolved from `app/`.
- The app connects to MySQL at `127.0.0.1:3306` (locally and on Hostinger).
- `public/uploads/` and `storage/` are gitignored and must survive every deploy.

**Content**
- No prices, rupee values, "best", "#1", "lowest", fake scarcity, placement or internship promises, or
  Modi/Altman quotes.
- Stats are limited to the five approved ones as "typical outcomes", plus "100+ professionals trained
  through corporate training sessions".
- Corporate training has no fixed duration.

**Front end**
- Pages are server-rendered HTML, and no body content is injected by JS.
- `prefers-reduced-motion` disables motion.
- No 3D or decorative WebGL libraries.

**Leads**
- Never report success to the visitor without a saved `leads` row.

**Orkay**
- Read-only. Nothing under `ARTIFICIA/NEW ORKAY WEBSITE` or `ARTIFICIA/ORKAY TILES GHL` is modified.

**Git**
- Work on the local branch `site-v1`, with a local commit at the end of each task.
- **Nothing is pushed to GitHub until Hardik approves the local site (Task 11).**

## Review Focus

1. **The database insert fails during a lead submit.** The visitor gets `{ok:false}` with the WhatsApp
   link and never a thank-you. Tested in Task 9, `test_lead_db_failure_is_not_success`.
2. **Browser autofill or a password manager fills the honeypot.** The lead is saved with `spam=1`,
   shown in the admin Spam view, not forwarded and not lost. Tested in Task 9,
   `test_honeypot_saves_as_spam`.
3. **The SEO editor makes a bad slug or redirect:** a slug that collides with a reserved path or another
   page, or a redirect to itself or into a loop. It gets a validation error and the site keeps working.
   Tested in Tasks 7 and 8, `test_slug_rules` and `test_redirect_loop_rejected`.
4. **A huge photo (6000 px, 12 MB) or a renamed non-image (PDF or PHP saved as `.jpg`) is uploaded.**
   The photo is resized to at most 1600 px wide WebP; the fake is rejected with a message. Tested in
   Task 6, `test_media_resizes_large` and `test_media_rejects_fake_image`.
5. **Rich text pasted from Word or Google Docs** with `<script>`, `style=`, `onclick=` or `javascript:`
   links. It's stored and rendered clean. Tested in Task 2, `test_sanitize_word_paste`.

---

## File structure (locked)

```
.htaccess                    repo-root: rewrite all to public/, deny every other top-level dir
public/index.php             front controller → app/bootstrap.php → dispatch()
public/.htaccess             non-file requests → index.php; deny dotfiles
public/assets/css/site.css   design system + sections (Task 4)
public/assets/css/admin.css
public/assets/js/site.js     forms, attribution cookie, video facade, motion init
public/assets/js/admin.js    sections editor (add/remove/reorder, list items), Quill wiring
public/assets/vendor/        gsap.min.js, ScrollTrigger.min.js (3.13.0), quill 2.0.3
public/assets/fonts/         self-hosted woff2, ≤ 2 families
public/uploads/              gitignored; media library output
app/bootstrap.php            config, db(), migrate on boot, session for /admin
app/lib/http.php             e(), redirect(), json_out(), request_path()
app/lib/db.php               db(), migrate()
app/lib/settings.php         settings(), setting_set()
app/lib/html.php             sanitize_html()
app/lib/pages.php            slug_valid(), page_find_published(), page_save(), page_publish_errors()
app/lib/render.php           render_page(), render_section(), field helpers
app/lib/seo.php              schema_graph(), sitemap_xml(), robots_txt()
app/lib/redirects.php        redirect_match(), redirect_save()
app/lib/media.php            media_store(), img_tag()
app/lib/auth.php             auth_*(), csrf_*(), password reset
app/lib/mail.php             send_mail()
app/lib/leads.php            lead_*(), meta_capi_payload(), n8n_payload()
app/lib/seed.php             seed_import()
app/lib/router.php           dispatch()
app/sections/registry.php    section types and their fields
app/sections/<type>.php      one partial per section type (Task 4)
app/views/layout.php, page-header.php, 404.php, design-gallery.php
app/admin/                   admin controllers + views (Tasks 5–9)
db/migrations/001_init.sql
bin/serve                    local dev server
bin/create-admin.php, bin/seed.php
content/seed/                pages/*.json, templates.json, redirects.json, media/*
content/design-samples.json  sample fields for every section type (gallery)
tests/run.php, tests/*Test.php
legacy/                      old site mirror (reference only; denied by root .htaccess)
storage/                     gitignored; local mail.log
```

---

### Task 1: Foundation: branch, legacy move, config, DB, migrations, test runner, dev server

**Files:**
- Create: `.gitignore`, `.htaccess`, `public/index.php`, `public/.htaccess`, `app/bootstrap.php`,
  `app/lib/db.php`, `app/lib/http.php`, `app/lib/settings.php`, `db/migrations/001_init.sql`,
  `bin/serve`, `tests/run.php`, `tests/DbTest.php`, `README.md` (rewrite)
- Move: every legacy root file and folder → `legacy/` (`git mv`)
- Create: `legacy/landing-page/`, `legacy/co-orporate/`

**Interfaces:**
- Produces:
  - `db(): PDO` (singleton, `ERRMODE_EXCEPTION`, utf8mb4)
  - `migrate(PDO $db, string $dir): array` (the filenames applied this call)
  - `e(?string $s): string`
  - `redirect(string $to, int $code = 302): never`
  - `json_out(array $data, int $code = 200): never`
  - `request_path(): string` (decoded path, no query string, `/` for root)
  - `settings(PDO $db): array<string,string>`
  - `setting_set(PDO $db, string $key, string $value): void`
  - `config(): array`
- Test helpers in `tests/run.php`: `eq(mixed $a, mixed $b, string $msg = '')`,
  `ok(bool $c, string $msg = '')` and `test_db(): PDO`. `test_db()` uses the config's database
  credentials, drops and recreates the `artificia_test` database, and runs every migration.

**Steps:**

- [ ] **Step 1: Create the branch and move the legacy site.**
  - `git switch -c site-v1`
  - `git mv` every tracked root file and folder except `docs/` and `README.md` into `legacy/`.
  - Mirror the public files of `https://artificiaai.com/landing-page/` and `/co-orporate/` into
    `legacy/landing-page/` and `legacy/co-orporate/` with curl. Include the HTML, CSS, JS, the logo,
    `images/hardik.jpg.jpeg` and `images/testimonials/*`. **Skip `images/phase-*.jpg`** (56 MB).
  - Check: `git status` shows only renames plus the new `legacy/landing-page/` and
    `legacy/co-orporate/` folders.
- [ ] **Step 2: Write `001_init.sql`** with these tables (InnoDB, utf8mb4):
  - `schema_migrations(filename PK, applied_at)`
  - `settings(k PK, v TEXT)`
  - `users(id, email UNIQUE, password_hash, role ENUM('admin','seo'), active TINYINT, created_at)`
  - `login_attempts(id, email, ip, created_at)`
  - `password_resets(token_hash PK, user_id, expires_at)`
  - `pages(id, kind ENUM('page','post'), slug UNIQUE, title, h1, meta_title, meta_description,
    og_image_id NULL, excerpt NULL, sections JSON, template_id NULL, status ENUM('draft','published'),
    published_at NULL, created_at, updated_at, updated_by)`
  - `page_revisions(id, page_id, snapshot JSON, user_id, created_at)`
  - `templates(id, name UNIQUE, sections JSON, unique_fields JSON, created_at)`
  - `media(id, base_name, alt, width, height, created_at)`
  - `redirects(id, from_path UNIQUE, to_path, match_type ENUM('exact','prefix'), code SMALLINT DEFAULT 301)`
  - `leads(id, event_id CHAR(36) UNIQUE, form_type, name, email, phone, company, fields JSON, page_url,
    referrer, utm_source, utm_medium, utm_campaign, utm_term, utm_content, gclid, fbclid, fbp, fbc, ip,
    user_agent, consent_at, spam TINYINT, forwarded_at NULL, forward_error NULL, emailed_at NULL,
    capi_sent_at NULL, capi_error NULL, created_at)`
  - `lead_rate(id, ip, created_at, INDEX(ip, created_at))`
- [ ] **Step 3: Write the failing tests.** In `tests/DbTest.php`:
  - `test_migrate_applies_once`: the first `migrate()` returns `['001_init.sql']` and the second
    returns `[]`.
  - `test_settings_roundtrip`: after `setting_set($db,'ga4_id','G-X')`, `settings($db)['ga4_id']`
    equals `'G-X'`.
- [ ] **Step 4: Run the tests and confirm they fail.** Run `php tests/run.php tests/DbTest.php`; it
  fails with "Call to undefined function migrate".
- [ ] **Step 5: Implement the Interfaces above.**
  - `bootstrap.php` loads the config, then calls `migrate()` when the migration count differs from
    the files. Wrap the call in MySQL `GET_LOCK('artificia_migrate', 10)` so two requests can't race.
  - Config keys:
    - `env`: `local`, `staging` or `production`
    - `base_url`
    - `db`: `dsn`, `user`, `pass`
    - `app_secret` (64 hex characters)
    - `staging_auth`: `user`, `pass`
    - `setup_token`
    - `mail_from`
  - `bin/serve` runs `ARTIFICIA_CONFIG=${ARTIFICIA_CONFIG:-$HOME/.config/artificia/config.php} php -S 127.0.0.1:8000 -t public public/index.php`.
  - In `public/index.php`, return `false` for existing files under the built-in server so assets
    are served directly.
- [ ] **Step 6: Run the tests and confirm they pass.**
  - `brew services start mariadb` (it's installed but not running).
  - Create the local `artificia` and `artificia_test` databases and the local config file.
  - Run `php tests/run.php`. Expected: `2 passed, 0 failed`.
- [ ] **Step 7: Commit locally.**
  `git add -A && git commit -m "Restructure repo for the new site; legacy mirror moved to legacy/"`

### Task 2: Section registry and rich-text sanitizer

**Files:**
- Create: `app/sections/registry.php`, `app/lib/html.php`, `tests/RegistryTest.php`, `tests/HtmlTest.php`

**Interfaces:**
- Produces:
  - `section_registry(): array<string, array{label:string, fields:array<string, array>}>`. A field is
    `{type: 'text'|'textarea'|'richtext'|'image'|'select'|'list', label, required?: bool,
    options?: string[], fields?: array}`. Only `list` fields have `fields`.
  - `sanitize_html(string $html): string`.

The section types and their fields are fixed for Step 1 (`*` means required):

| type | fields |
|---|---|
| `hero` | eyebrow, subheading(textarea), primary_cta_label, primary_cta_link, secondary_cta_label, secondary_cta_link, image(image), variant(select: split, centered). The heading is the page's `h1`. |
| `rich_text` | heading, body*(richtext) |
| `text_image` | heading*, body*(richtext), image*(image), image_side(select: left, right), cta_label, cta_link |
| `card_grid` | heading*, intro(textarea), columns(select: 2, 3, 4), cards*(list: icon(select), title*, text*(textarea), link) |
| `use_cases` | heading*, intro(textarea), items*(list: title*, before(textarea), after(textarea)) |
| `process_steps` | heading*, intro(textarea), steps*(list: title*, text*(textarea)) |
| `tools` | heading*, intro(textarea), tools*(list: name*, logo(image), note) |
| `curriculum` | heading*, intro(textarea), modules*(list: track*(select: foundation, full), day_range*, title*, topics(textarea), tools) |
| `stats` | heading, note (default "Typical outcomes for AI-enabled teams."), items*(list: value*, label*) |
| `testimonials` | heading*, items*(list: name*, role*, quote(textarea), photo(image), video_url) |
| `case_study` | heading*, client*, summary*(textarea), challenge(richtext), built*(list: title*, text*(textarea)), results(list: value, label), logo(image), link_label, link |
| `faqs` | heading*, items*(list: question*, answer*(textarea)) |
| `lead_form` | heading*, intro(textarea), form_type*(select: masterclass, corporate, services, contact), button_label, side_points(list: text*) |
| `cta` | heading*, text(textarea), button_label*, button_link* |
| `people` | heading*, items*(list: name*, role*, bio(textarea), photo(image), linkedin) |
| `map_contact` | heading, show_map(select: yes, no). Address, phone and email come from settings. |
| `blog_list` | heading, count(select: 3, 6, 12) |

The `icon` options are a fixed set of about 24 inline-SVG names, chosen in Task 4.

**Steps:**

- [ ] **Step 1: Write the failing tests.**
  - `test_registry_has_all_types`: the 17 type keys above, exactly.
  - `test_every_list_has_subfields`.
  - `test_sanitize_strips_dangerous`: `<p onclick="x">a<script>b</script></p>` becomes `<p>a</p>`,
    and `<a href="javascript:alert(1)">x</a>` becomes `<a>x</a>`.
  - `test_sanitize_keeps_allowed`: `p h2 h3 ul ol li strong em a[href^=https:|http:|mailto:|tel:|/] br`
    survive, and links gain `rel="noopener"` when absolute.
  - `test_sanitize_word_paste`: pasted HTML with `<span style>`, `<o:p>`, `class=` and `<font>` is
    reduced to text inside allowed tags.
- [ ] **Step 2: Run** `php tests/run.php tests/HtmlTest.php tests/RegistryTest.php` and confirm it
  fails.
- [ ] **Step 3: Implement.**
  - `sanitize_html` uses `DOMDocument::loadHTML` (UTF-8 wrapped). It walks the tree, unwraps
    disallowed elements but keeps their text, drops `script`, `style` and `iframe` with their content,
    and removes every attribute except a vetted `href`.
- [ ] **Step 4: Run all tests and confirm they pass.**
- [ ] **Step 5: Commit locally:** `Add section registry and HTML sanitizer`.

### Task 3: Rendering, router, redirects engine, SEO files, schema, staging guard

**Files:**
- Create: `app/lib/router.php`, `app/lib/pages.php` (read parts), `app/lib/render.php`,
  `app/lib/seo.php`, `app/lib/redirects.php` (match only), `app/views/layout.php`,
  `app/views/page-header.php`, `app/views/404.php`, `app/sections/*.php` (minimal unstyled stubs that
  print fields semantically; Task 4 replaces their markup)
- Test: `tests/RenderTest.php`, `tests/SeoTest.php`, `tests/RedirectTest.php`

**Interfaces:**
- Consumes: `section_registry()`, `sanitize_html()`, `db()`, `settings()`.
- Produces:
  - `page_find_published(PDO $db, string $slug): ?array`. `sections` is decoded to an array. Home
    has the slug `home`.
  - `render_page(array $page, array $settings): string` and `render_section(array $section): string`.
  - `schema_graph(array $page, array $settings, string $baseUrl): array`, the JSON-LD `@graph` list.
  - `sitemap_xml(PDO $db, string $baseUrl): string` and `robots_txt(string $env, string $baseUrl): string`.
  - `redirect_match(PDO $db, string $path): ?array{to:string, code:int}`. Exact matches beat
    prefix matches, and the longest prefix wins.
  - `dispatch(string $method, string $path): void`.

**Routing order:**

1. `/sitemap.xml`
2. `/robots.txt`
3. `POST /lead` (Task 9)
4. `/admin*` (Task 5)
5. `/_design` (Task 4; only when `env=local` or an admin is logged in)
6. `/blog/{slug}`, which loads the post
7. `/`, which loads `home`
8. Any other path: the page with that slug, otherwise `redirect_match`, otherwise the 404 page

**Rendering rules:**
- **Exactly one `<h1>`.** If the first section is `hero`, it renders the page `h1`. Otherwise
  `page-header.php` renders the `h1` first.
- **Every other section heading is an `<h2>`.**
- **`<head>` contents:**
  - the `meta_title` (falling back to the title)
  - the meta description
  - a canonical link of `base_url` plus the path
  - OG and Twitter tags, using `og_image_id` and the 1600 px rendition
  - JSON-LD
  - GA4 (`gtag.js` with the `ga4_id` setting)
  - the Meta pixel base code (with the `meta_pixel_id` setting)
  - the GSC verification meta tag
- **Each tracking snippet is printed only when its setting isn't empty.**
- **Staging guard:** when `env=staging`, every non-asset request needs HTTP Basic auth from
  `staging_auth` and sends `X-Robots-Tag: noindex, nofollow`.

**Steps:**

- [ ] **Step 1: Write the failing tests.**
  - `test_single_h1_with_hero` and `test_single_h1_without_hero`: count `<h1` equals 1.
  - `test_unpublished_page_404s`.
  - `test_redirect_exact_beats_prefix`: rows `/co-orporate/` (prefix) and `/co-orporate/x.html`
    (exact). `/co-orporate/x.html` takes the exact row, and `/co-orporate/anything` takes the prefix
    row.
  - `test_sitemap_lists_only_published`: it includes `/blog/<slug>` for posts and excludes drafts.
  - `test_robots_staging_disallows_all`: it's `Disallow: /` in staging. In production it allows
    everything and points at the sitemap.
  - `test_schema_faq_from_faqs_section`: an `FAQPage` with one `Question` per item.
  - `test_schema_course_when_curriculum`: a `Course` with `provider.name` "Artificia AI" and **no
    `offers.price`**.
  - `test_schema_home_has_org_and_local_business`: `EducationalOrganization` with the address, and
    `sameAs` includes the `gbp_url` setting.
  - `test_schema_article_for_posts`.
- [ ] **Step 2: Run them and confirm they fail.**
- [ ] **Step 3: Implement the Interfaces above.**
- [ ] **Step 4: Run all tests and confirm they pass.**
  - Then `bin/serve`, insert a test home page by SQL, and check that
    `curl -s 127.0.0.1:8000/ | grep -c '<h1'` prints `1`.
- [ ] **Step 5: Commit locally:** `Add router, page rendering, redirects, sitemap, robots, schema`.

### Task 4: Design: section library, tokens, motion (gate: Hardik approves before Task 10)

**Files:**
- Create or modify: `app/sections/*.php` (final markup), `public/assets/css/site.css`,
  `public/assets/js/site.js` (motion parts), `public/assets/vendor/*`, `public/assets/fonts/*`,
  `app/views/design-gallery.php`, `content/design-samples.json`

**Interfaces:**
- Consumes: the field names in `section_registry()`. **The design must not change them**; any new field
  is added to the registry in this task, along with a test.
- Produces: the CSS class contract that Task 9's form JS hooks onto: `.lead-form`, `[data-form-type]`,
  `.lead-form__status`.

**Requirements:**
- **Design skills:** use `taste-skill:design-taste-frontend`, `impeccable:impeccable`,
  `frontend-design:frontend-design`, and `emil-design-eng` for motion polish.
- **Brand anchors:** lime `#AED534` on charcoal `#2A2E2E` / `#08080d` with white text. At most 2
  self-hosted font families.
- **Motion:**
  - GSAP 3.13.0 plus ScrollTrigger, self-hosted.
  - Code-driven motion graphics: section reveals, number count-ups in `stats`, an animated SVG
    workflow diagram (Orkay and automation), and scroll-driven `process_steps`.
  - Under `prefers-reduced-motion: reduce`, nothing animates and everything is visible immediately.
- **Testimonial videos** use a facade: a poster image and a play button. The Drive or YouTube iframe
  loads only on click.
- **Budgets on the gallery page (mobile Lighthouse):**
  - Performance ≥ 90, Accessibility ≥ 95.
  - Total CSS + JS under 150 KB gzipped, excluding images.
  - The hero image under 200 KB, eager-loaded; every other image lazy-loaded.
- **The `/_design` gallery** renders every section type with `content/design-samples.json`, in order,
  with an anchor list at the top.

**Steps:**

- [ ] **Step 1: Run the design skills** to set the direction (type scale, spacing, colour tokens, motion
  language). Record the tokens as CSS custom properties at the top of `site.css`.
- [ ] **Step 2: Build every partial and `design-samples.json`.** Then `bin/serve` and open
  `http://127.0.0.1:8000/_design`.
- [ ] **Step 3: Screenshot the gallery at 390 px and 1440 px** with Playwright into `storage/design/`.
  Run the Lighthouse mobile audit on `/_design`. Expected: the budgets above are met.
- [ ] **Step 4: Check that `php tests/run.php` still passes**, including the single-H1 tests.
- [ ] **Step 5: Commit locally:** `Design: section library, tokens and motion`.
- [ ] **Step 6: GATE.** Tell Hardik that the design is ready at `http://127.0.0.1:8000/_design`, with
  the screenshots. Apply his changes. **Task 10 (content) doesn't start until he approves.** Tasks 5–9
  can continue meanwhile.

### Task 5: Auth, admin shell, users, settings, first-run setup, mail

**Files:**
- Create: `app/lib/auth.php`, `app/lib/mail.php`, `app/admin/routes.php`,
  `app/admin/views/{layout,login,forgot,reset,setup,dashboard,users,settings}.php`,
  `public/assets/css/admin.css`, `bin/create-admin.php`
- Test: `tests/AuthTest.php`

**Interfaces:**
- Produces:
  - `auth_login(PDO $db, string $email, string $password, string $ip): true|'invalid'|'locked'`
  - `auth_user(): ?array`
  - `auth_require(string $minRole = 'seo'): array`. `admin` outranks `seo`; a failure redirects to
    `/admin/login`, or returns 403 for the wrong role.
  - `csrf_token(): string` and `csrf_check(?string $token): bool`
  - `password_reset_request(PDO $db, string $email): void` and
    `password_reset_apply(PDO $db, string $token, string $newPassword): bool`
  - `send_mail(string $to, string $subject, string $text): bool`. It uses PHP `mail()` with `From`
    set to `mail_from`. In local env it appends to `storage/mail.log` instead.
- **Settings keys (admin only):**
  - tracking: `ga4_id`, `gsc_verification`, `meta_pixel_id`, `meta_capi_token`,
    `meta_test_event_code`
  - lead routing: `n8n_webhook_url`, `n8n_webhook_token`, `lead_alert_email`
  - business details: `gbp_url`, `whatsapp_number` (default `919104488859`), `org_phone`,
    `org_email`, `org_address`

**Rules:**
- **Passwords:** `password_hash` with the default algorithm, at least 10 characters.
- **Lockout:** 5 failed logins per email+IP within 15 minutes returns `'locked'`.
- **Session cookie:** `HttpOnly`, `SameSite=Lax`, and `Secure` when not local.
- **CSRF:** every admin POST checks it.
- **Password reset tokens** are 32 random bytes. Only their SHA-256 hash is stored, they expire after
  1 hour, and they work once.
- **`/admin/setup?token=…`** works only while `users` is empty and the token equals `setup_token`. It
  creates the first admin.
- **The SEO role is blocked** from `/admin/settings`, `/admin/users`, lead export, lead delete and
  forward retry.

**Steps:**

- [ ] **Step 1: Write the failing tests.**
  - `test_login_ok_and_invalid`.
  - `test_lockout_after_5_failures`.
  - `test_csrf_rejects_missing_and_wrong`.
  - `test_reset_token_single_use_and_expiry`.
  - `test_setup_only_when_no_users`.
  - `test_seo_role_cannot_reach_settings`: render `/admin/settings` as an SEO user and expect 403.
- [ ] **Step 2: Run them and confirm they fail.**
- [ ] **Step 3: Implement.**
- [ ] **Step 4: Run them and confirm they pass.** Then manually log in at `127.0.0.1:8000/admin`
  after `php bin/create-admin.php hardikartificia@gmail.com`.
- [ ] **Step 5: Commit locally:** `Admin auth, users, settings, setup`.

### Task 6: Media library

**Files:**
- Create: `app/lib/media.php`, `app/admin/views/media.php`
- Test: `tests/MediaTest.php` (generates its fixtures with GD at runtime)

**Interfaces:**
- Produces:
  - `media_store(PDO $db, string $tmpPath, string $originalName, string $alt, string $uploadsDir): int`.
    It throws `InvalidArgumentException` with a user-facing message.
  - `img_tag(PDO $db, int $id, string $sizes, bool $eager = false): string`, which outputs `<img>` with
    `srcset`, `width`, `height`, `alt`, and `loading="lazy" decoding="async"` unless eager.
- **Rules:**
  - The type is checked by `finfo`, not by extension. JPEG, PNG and WebP only; maximum 15 MB.
  - Alt text is required.
  - Output is WebP at quality 80, at widths `[480, 960, 1600]`. Widths larger than the source are
    skipped.
  - Files are saved to `uploads/YYYY/MM/{id}-{w}.webp`. The original isn't kept.

**Steps:**

- [ ] **Step 1: Write the failing tests.**
  - `test_media_resizes_large`: a 6000×4000 JPEG gives files of 480, 960 and 1600 px width, each
    `image/webp`.
  - `test_media_rejects_fake_image`: a PHP file named `x.jpg` throws.
  - `test_media_requires_alt`.
  - `test_small_image_not_upscaled`: a 300 px source gives one 300 px file.
  - `test_img_tag_has_dimensions_and_lazy`.
- [ ] **Step 2: Run them and confirm they fail.**
- [ ] **Step 3: Implement**, including the admin media page (upload form, grid, edit alt).
- [ ] **Step 4: Run them and confirm they pass.**
- [ ] **Step 5: Commit locally:** `Media library with resize and WebP`.

### Task 7: Page and post editor, templates, duplicate, revisions

**Files:**
- Modify: `app/lib/pages.php`
- Create: `app/admin/views/{pages,page-edit,templates}.php`, `public/assets/js/admin.js`
- Test: `tests/PagesTest.php`

**Interfaces:**
- Consumes: `section_registry()`, `sanitize_html()`, `media_store()`, `auth_require()`, `csrf_check()`.
- Produces:
  - `slug_valid(string $slug): bool`.
    - The pattern is `^[a-z0-9]+(-[a-z0-9]+)*(/[a-z0-9]+(-[a-z0-9]+)*){0,2}$`.
    - These first segments are reserved: `admin`, `blog`, `_design`, `assets`, `uploads`, `lead`,
      `sitemap.xml`, `robots.txt`, `live`, `legacy`.
    - `home` is the one allowed special slug.
  - `page_save(PDO $db, array $page, int $userId): int`. It sanitizes every `richtext` field, checks
    every field against the registry and drops unknown ones, inserts a `page_revisions` snapshot, and
    returns the page id.
  - `page_publish_errors(PDO $db, array $page): string[]`. These cover the required section fields,
    `h1`, `meta_title` and `meta_description` being non-empty, and, for pages with a `template_id`,
    every `unique_fields` path (`"<sectionIndex>.<field>"`) being non-empty and different from the
    template's value.
  - `page_duplicate(PDO $db, int $id, string $newSlug, int $userId): int`.
  - `template_from_page(PDO $db, int $pageId, string $name, array $uniqueFields): int`.
  - `page_from_template(PDO $db, int $templateId, string $slug, string $title, int $userId): int`.

**Editor UI (admin.js):**
- Add a section by type, using a server-rendered blank form from `/admin/section-form?type=`. Remove
  and move sections up or down.
- Add and remove list items.
- Quill on `richtext` fields, synced to a hidden textarea.
- An image picker that opens the media library.
- Preview at `/admin/preview/{id}`.
- Field names follow `sections[i][field]` and `sections[i][items][j][title]`; the server re-indexes them.
- Posts: `kind=post`, plus `excerpt` and a cover via `og_image_id`. The blog index is the page `blog`
  with a `blog_list` section.

**Steps:**

- [ ] **Step 1: Write the failing tests.**
  - `test_slug_rules`: `services/gohighlevel` and `ai-masterclass/students` are valid. `admin/x`,
    `Blog`, `a--b`, `a/b/c/d` and an existing slug are invalid.
  - `test_save_sanitizes_richtext_and_drops_unknown_fields`.
  - `test_save_writes_revision`.
  - `test_publish_blocked_when_unique_field_unchanged`.
  - `test_publish_blocked_without_meta`.
  - `test_duplicate_copies_sections_new_slug_draft`.
  - `test_template_roundtrip`.
- [ ] **Step 2: Run them and confirm they fail.**
- [ ] **Step 3: Implement.**
- [ ] **Step 4: Run them and confirm they pass.** Then manually build a page with 5 section types,
  reorder it, publish it and view it.
- [ ] **Step 5: Commit locally:** `Section-based page and post editor with templates`.

### Task 8: Redirects admin and the old-URL map

**Files:**
- Modify: `app/lib/redirects.php`
- Create: `app/admin/views/redirects.php`, `content/seed/redirects.json`
- Test: `tests/RedirectTest.php` (extend)

**Interfaces:**
- Produces: `redirect_save(PDO $db, array{from_path, to_path, match_type, code} $r): int`. It throws
  `InvalidArgumentException` when `from == to`, when following `to_path` through the existing rows
  gets back to `from_path` within 10 hops, or when `from_path` is a live page's slug.

**`redirects.json`** holds every row of spec §7 exactly. `/landing-page/` and `/co-orporate/` are
`prefix` rows; the rest are `exact`.

**Steps:**

- [ ] **Step 1: Write the failing tests.**
  - `test_redirect_loop_rejected`: with `/a`→`/b` in place, saving `/b`→`/a` throws.
  - `test_redirect_to_self_rejected`.
  - `test_redirect_cannot_shadow_page`.
  - `test_seed_map_complete`: every old path from spec §7 resolves via `redirect_match`, with code 301.
- [ ] **Step 2: Run them and confirm they fail.**
- [ ] **Step 3: Implement.**
- [ ] **Step 4: Run them and confirm they pass.**
- [ ] **Step 5: Commit locally:** `Redirects admin and old-URL map`.

### Task 9: Leads: endpoint, spam checks, tracking, alerts, n8n, inbox

**Files:**
- Create: `app/lib/leads.php`, `app/admin/views/{leads,lead}.php`
- Modify: `public/assets/js/site.js` (forms and attribution), `app/sections/lead_form.php`
  (the hidden fields and consent line)
- Test: `tests/LeadsTest.php`

**Interfaces:**

**Signatures:**
- `lead_validate(string $type, array $in): array{ok:bool, errors:array<string,string>, data:array}`
- `normalize_phone(string $raw): ?string`
  - A 10-digit number starting 6–9 becomes `+91…`.
  - `91` plus 10 digits becomes `+91…`.
  - A `+` plus 8–15 digits is kept as it is.
  - Anything else gives `null`.
- `lead_spam_flags(array $in, string $ip, PDO $db, string $secret): array{spam:bool, rate_limited:bool}`
  - `spam` is true when the honeypot `website_url` isn't empty, or `ts` is under 3 s or over 24 h old.
    `ts` is `time().'.'.hmac`, issued in `lead_form.php`.
  - `rate_limited` is true for more than 5 submissions from one IP in 10 minutes.
- `lead_save(PDO $db, array $lead): array{id:int, event_id:string}`. `event_id` is a UUID v4.
- `meta_capi_payload(array $lead, string $pageUrl, string $testCode = ''): array`.
  - The payload has `event_name` `Lead`, `action_source` `website`, `event_id`, `event_time`,
    `event_source_url` and `user_data`.
  - `user_data` holds `em` (SHA-256 of the trimmed, lowercased email), `ph` (SHA-256 of the digits
    only, with country code), `client_ip_address`, `client_user_agent`, `fbp`, and `fbc` (taken from
    the `_fbc` cookie, or built as `fb.1.<ms>.<fbclid>`).
  - It's sent to `https://graph.facebook.com/v23.0/{pixel}/events`.
- `n8n_payload(array $lead, string $baseUrl): array`. Its shape is below. **This is the contract for
  the separate n8n workflow.**

**Form fields** (`*` means required; the selects use these exact options):

- **Common to every form:** a consent checkbox* and the hidden fields `website_url`, `ts`, `page_url`,
  `referrer`, the five `utm_*` fields, `gclid`, `fbclid`, `fbp` and `fbc`. The consent wording is
  "I agree to be contacted about my enquiry by WhatsApp, phone or email, as described in the Privacy
  Policy."
- **`masterclass`:**
  - name*, phone* (WhatsApp), email*
  - `i_am`* options: Student / Recent graduate, Working professional / Manager, Business owner /
    Founder, Freelancer / Creator, Job seeker, Other
  - `track` options: 30-day Foundation, 60-day Full program, Not sure yet
- **`corporate`:**
  - name*, email* (work), phone*, company*
  - `employees`* options: 1–10, 11–25, 26–50, 51–100, 100+
  - `adoption`* options: Not using AI yet, Exploring (ChatGPT, Claude), Some tools or automations,
    Custom automations or models
  - `location`*
  - `objectives[]` options: Automate repetitive work, Upskill the team on AI, Build AI agents or
    chatbots, Integrate n8n / Make / APIs, Something else
  - challenge
- **`services`:**
  - name*, phone*, email*, company
  - `service`* options: GoHighLevel setup, Website development, Content and marketing, RAG or custom
    automation, AI automation, Not sure yet
  - message
- **`contact`:** name*, phone*, email, message*

**`POST /lead` processing order (spec §6.6):**

1. Validate. Failures return 422 with `{ok:false, errors}`.
2. Rate limit. Over the limit returns 429 with `{ok:false, message, whatsapp}`.
3. Save the lead, with `spam` set from the flags. A save failure returns 500 with
   `{ok:false, whatsapp}`.
4. Return `{ok:true, event_id}` (for spam rows too).
5. Then call `litespeed_finish_request()` or `fastcgi_finish_request()` if either exists.
6. If the lead isn't spam, run each of these with a 5 s curl timeout, writing its result columns:
   - the CAPI call, but only if both the pixel and the token are set
   - the alert email, to `lead_alert_email`, including the admin link
   - the n8n POST, with the `X-Artificia-Token: <n8n_webhook_token>` header, but only if the URL is
     set. If no URL is set, write `forward_error='no webhook configured'`.

**The n8n payload:**

```json
{"lead_id":123,"event_id":"uuid","form_type":"masterclass","name":"","email":"","phone":"+91…","company":"",
 "fields":{},"page_url":"","referrer":"","utm":{"source":"","medium":"","campaign":"","term":"","content":""},
 "gclid":"","fbclid":"","submitted_at":"2026-10-27T15:30:00+05:30","admin_url":"https://artificiaai.com/admin/leads/123"}
```

**The `site.js` form flow:**
- On every page, save the first-touch `utm_*`, `gclid` and `fbclid` from the URL into an `atk`
  cookie (30 days, `SameSite=Lax`), and fill the hidden fields from it.
- On submit: disable the button and `fetch` `/lead`.
- Show the success state **only on `ok:true`**. Then fire `gtag('event','generate_lead',{form_type})`
  and `fbq('track','Lead',{content_category: form_type},{eventID: event_id})`, each only if the
  function exists.
- On an error, show the message and a `https://wa.me/<whatsapp_number>` link, and re-enable the button.
- A floating WhatsApp button appears on every page.

**The admin leads inbox:**
- A list with filters for form type, page, source (`utm_source`) and date range, plus tabs for All,
  Not forwarded and Spam.
- Lead detail.
- CSV export (admin only, UTF-8 with BOM for Excel).
- Delete (admin only).
- Retry forward (admin only).

**Steps:**

- [ ] **Step 1: Write the failing tests.**
  - `test_validate_required_per_type`, with one case per form type.
  - `test_normalize_phone`:
    - `98765 43210` → `+919876543210`
    - `919876543210` → `+919876543210`
    - `+971501234567` kept as is
    - `12345` → `null`
  - `test_honeypot_saves_as_spam`: the row has `spam=1`, the response has `ok:true`, and there's no
    forward attempt.
  - `test_fast_submit_is_spam`: a `ts` 1 s old.
  - `test_rate_limit_sixth_request`: the 6th request returns 429.
  - `test_lead_db_failure_is_not_success`: a PDO whose insert throws gives HTTP 500 with `ok:false`.
  - `test_capi_payload_hashes`: `em` equals `hash('sha256','a@b.com')` for the input `" A@B.com "`.
    `ph` hashes `919876543210`. `fbc` is built from `fbclid`.
  - `test_n8n_payload_shape`: exactly the keys above.
  - `test_forward_failure_keeps_lead`: an unreachable URL sets `forward_error` and keeps the row.
  - `test_no_webhook_marks_not_forwarded`.
  - `test_seo_cannot_export_or_delete`.
- [ ] **Step 2: Run them and confirm they fail.**
- [ ] **Step 3: Implement.**
- [ ] **Step 4: Run them and confirm they pass.**
- [ ] **Step 5: Check end to end locally.**
  - Point `n8n_webhook_url` at a local catcher, such as `php -S 127.0.0.1:8099 storage/catcher.php`
    that writes the body to `storage/n8n.log`.
  - Submit each of the four forms in the browser.
  - Expected: four rows in the admin inbox, four bodies in `storage/n8n.log`, and four mails in
    `storage/mail.log`.
- [ ] **Step 6: Commit locally:** `Leads: endpoint, spam checks, tracking, alerts, n8n, inbox`.

### Task 10: Content: seed pages, templates, media, content-rules lint, seed importer

Runs after the Task 4 gate.

**Files:**
- Create: `app/lib/seed.php`, `bin/seed.php`, `content/seed/pages/*.json`, `content/seed/templates.json`,
  `content/seed/media/*`
- Test: `tests/ContentRulesTest.php`, `tests/SeedTest.php`

**Interfaces:**
- Produces: `seed_import(PDO $db, string $dir, string $uploadsDir, bool $force = false): array{pages:int, templates:int, redirects:int, media:int}`.
  - It upserts by slug or name.
  - Media is referenced by filename in the JSON and swapped for a media id on import.
  - It refuses to run on a database that already has pages unless `$force` is set.
  - It's callable from `bin/seed.php`, and from the admin Settings button "Import seed content",
    which is admin only and shown only when no pages exist.

**Pages (slug: source):**

| Slug | Source |
|---|---|
| `home` | spec §2, all three offers, real testimonials |
| `ai-masterclass` | `Artificia final course/CLAUDE.md` + `Artificia_AI_60_Day_Curriculum.csv`, Hardik as instructor, the masterclass FAQs adapted from `legacy/landing-page/index.html` with placement and pricing claims removed |
| `ai-masterclass/students`, `ai-masterclass/working-professionals`, `ai-masterclass/business-owners` | the "Masterclass by audience" template |
| `corporate-ai-training` | `MASTER_CORPORATE_TRAINING_SPEC.md` and the sales-kit one-pagers; custom length and format, on-site or online over Microsoft Teams; the free 2-hour pilot; "100+ professionals trained through corporate training sessions" |
| `corporate-ai-training/manufacturing`, `corporate-ai-training/healthcare` | the "Corporate by industry" template |
| `services` | an overview of the four lead services, then the catalogue pillars from `AUTOMATION_SERVICES_CATALOGUE.md`; the free 15-minute flow audit |
| `services/gohighlevel`, `services/website-development`, `services/content-marketing`, `services/rag-custom-automation` | the "Service or use case" template |
| `case-studies/orkay-tiles` | `ORKAY TILES GHL/knowledge/` (live and verified facts only) + `NEW ORKAY WEBSITE/` |
| `about` | the company, Hardik Aseri, Vijay Linju Raghavan |
| `contact`, `blog`, `privacy`, `terms` | `privacy` and `terms` are rewritten with `info@artificiaai.com` and cover DPDP consent, the purpose of collecting leads, retention, and how to request deletion |

**Media:**
- `legacy/landing-page/images/testimonials/*`
- `legacy/landing-page/images/hardik.jpg.jpeg`
- `legacy/assets/images/all_images/about/vijay.jpg`
- the logo
- Testimonial videos keep the three Google Drive preview URLs from `legacy/landing-page/index.html`.

**The content-rules lint** (`tests/ContentRulesTest.php`) scans every string in `content/seed/**/*.json`
and fails on any of the following:

- **Banned words**, case-insensitive: `\bbest\b`, `#\s?1\b`, `\bno\.?\s?1\b`, `lowest`,
  `seats? (left|available)`, `placement`, `internship`, `guarantee`, `₹`, `\bRs\.?\s`, `\bINR\b`,
  `valued at`, `\bLPA\b`, `Modi`, `Altman`.
- **Numbers that aren't on the approved list.** Any `\d+\s?%` or `\d+\s?[x×]\b` must be one of 92%,
  65%, 500% or 4×.
- **A fixed duration for corporate training.** No slug starting with `corporate-ai-training` may
  contain `60[- ]day` or `\d+\s+sessions`.
- **Seed pages that wouldn't pass their own publish rules.** `page_publish_errors()` must be empty for
  every seed page (unique fields filled and different from their template).

**Steps:**

- [ ] **Step 1: Write the failing tests.** That's the lint above, plus:
  - `test_seed_import_idempotent`: a second run with `force` keeps the same counts.
  - `test_seed_refuses_nonempty_without_force`.
- [ ] **Step 2: Run them and confirm they fail.**
- [ ] **Step 3: Implement the importer and write all the copy and JSON.**
  - Use only the spec §8 sources.
  - Write the stats note as "Typical outcomes for AI-enabled teams."
- [ ] **Step 4: Run them and confirm they pass.** Then run `php bin/seed.php` and browse every page
  locally.
- [ ] **Step 5: Commit locally:** `Seed content for launch pages`.

### Task 11: Local review (gate before anything leaves the machine)

- [ ] **Step 1: Run `php tests/run.php`.** Expected: everything passes, with 0 failures.
- [ ] **Step 2: Run Lighthouse mobile** on `/`, `/ai-masterclass`, `/corporate-ai-training` and
  `/services/gohighlevel`. Expected: Performance ≥ 90, Accessibility ≥ 95, SEO ≥ 95 and Best
  Practices ≥ 90.
- [ ] **Step 3: Check every redirect.** For each row in spec §7, `curl -sI 127.0.0.1:8000<old>` returns
  a 301 to the new path.
- [ ] **Step 4: Check the editor's reach.** Log in as an SEO user. Create the page
  "AI automation for CA firms" from the "Service or use case" template, edit it, publish it, and see
  it in `/sitemap.xml`. All of this happens without touching code.
- [ ] **Step 5: GATE.** Hand Hardik `http://127.0.0.1:8000` and the admin login, apply his changes, and
  get an explicit OK to push and deploy.

### Task 12: Staging on Hostinger (after Hardik's OK)

- [ ] **Step 1: Push the code.** Run `git push -u origin site-v1`.
- [ ] **Step 2: Connect Hostinger to GitHub.** Call `hosting_git_generate-ssh-key` (username
  `u447231494`), then add the key as a read-only deploy key with
  `gh repo deploy-key add <keyfile> -R popeyaiartificia-png/artificiaai-website -t hostinger-u447231494`.
- [ ] **Step 3: Create the database.** Call `hosting_databases_create` with name `artsite`, user
  `artsite`, a generated password and `website_domain` `artificiaai.com`. The result is
  `u447231494_artsite`.
- [ ] **Step 4: Have Hardik create the config file.** He creates
  `/home/u447231494/domains/artificiaai.com/artificia-config.php` in hPanel File Manager with the
  content Claude provides once:
  - `env` = `staging`
  - `base_url` = `https://staging.artificiaai.com`
  - the database credentials
  - a new `app_secret`, `staging_auth` and `setup_token`
  - `mail_from` = `info@artificiaai.com`
- [ ] **Step 5: Deploy the code.** Call `hosting_git_deploy-website-repository` with:
  - `repository_url`: `git@github.com:popeyaiartificia-png/artificiaai-website.git`
  - `branch`: `site-v1`
  - `directory`: `live`

  Expected: `is_success: true`.
- [ ] **Step 6: Create the staging subdomain.** Call `hosting_domains_create-website-subdomain` with
  `subdomain` `staging` and `is_using_public_directory: true`. Then upload `public_html/.htaccess`
  through `hosting_files_generate-upload-url` (TUS curl). The server has no `.htaccess` today, so
  nothing gets overwritten:

  ```
  RewriteEngine On
  RewriteCond %{HTTP_HOST} ^staging\.artificiaai\.com$ [NC]
  RewriteCond %{REQUEST_URI} !^/live/
  RewriteCond %{REQUEST_URI} !^/\.well-known/
  RewriteRule ^(.*)$ /live/$1 [L]
  ```

  The legacy site on artificiaai.com is unaffected, because the rule only matches the staging host.
  This is the exact rule production will use, minus that host line. `/.well-known/` is left alone so
  the host's certificate renewal (HTTP-01) finds its challenge files in `public_html/.well-known/`.
- [ ] **Step 7: Set up the admin and content.**
  - Open `https://staging.artificiaai.com/admin/setup?token=…` and create Hardik's admin login.
  - Settings → Import seed content.
  - Enter the tracking IDs, with `meta_test_event_code` set. Set `lead_alert_email`, and the n8n URL
    and token if the workflow exists by then.
- [ ] **Step 8: Smoke test.**
  - Basic auth is required and `X-Robots-Tag: noindex` is present.
  - All four forms create leads, and the Meta Events Manager test-events panel shows each Lead once
    (browser + server deduped).
  - The alert email arrives.
  - `/live/app/bootstrap.php`, `/live/legacy/` and `/live/.git/` all return 403.
  - Upload an image in the admin media library, redeploy (Step 5 again), and confirm the image still
    loads: uploads must survive a deploy.
  - `/.well-known/acme-challenge/x` reaches the filesystem: it answers 404 (no such file), not 403.
  - Report to Hardik.

### Task 13: Switchover (after Hardik approves staging)

- [ ] **Step 1: Prepare.**
  - Hardik makes a fresh full backup in hPanel.
  - Confirm that `SETUP_GUIDE.md`, `Artificia.zip` and the resume in `uploads/` have been removed
    from `public_html`.
  - Change the config to `env` = `production` and `base_url` = `https://artificiaai.com`.
- [ ] **Step 2: Switch the traffic.** Re-upload `public_html/.htaccess` without the `HTTP_HOST` line,
  and add a 301 from `staging.artificiaai.com` to `https://artificiaai.com`. The app does this when
  `env=production` and the host isn't the `base_url` host.
- [ ] **Step 3: Verify.**
  - `https://artificiaai.com/`, `/landing-page/` (301 to `/ai-masterclass`), `/co-orporate/` (301)
    and `/sitemap.xml` all behave.
  - Submit one real test lead.
  - `robots.txt` allows indexing.
- [ ] **Step 4: Hand off to Hardik and the SEO expert:**
  - submit the sitemap in Search Console
  - repoint the Meta ads to the new URLs
  - link the new Google Business Profile in Settings (`gbp_url`)
- [ ] **Step 5: Merge `site-v1` into `main` and push.** From then on, each release is deployed with
  `hosting_git_deploy-website-repository` on `main` into `live`.
