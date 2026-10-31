# CONTEXT.md: Artificia AI website, full project context

**Read this first.** It is the single handover file for any AI assistant or developer joining the artificiaai.com rebuild. It covers what the business is, what has been built, the design direction and why, what was rejected, and what comes next.

GitHub publication (31 Oct 2026): this checkout uses repository-local author name and preferred GitHub username `jaytishah`, retaining `jaytishah20@gmail.com`; global Git settings are unchanged. GitHub authentication was verified as `jaytishah`, but the configured `popeyaiartificia-png/artificiaai-website` repository returned 404 for that account. Push remains pending an accessible destination. Pre-commit PHP/JS syntax checks passed; scoped tests returned 27 passes and two environment-related failures (PHP GD unavailable and the documented Windows `.htaccess` assertion).

**Rule for whoever works on this repo (human or AI):** after *every* change, however small, run the checkpoint. (1) Update this file: fix any section the change makes untrue, and add one dated line to the **Changelog** at the bottom. (2) Add to today's entry in [PROGRESS.md](PROGRESS.md), the daily note: Done / Current lead / Next / Blocked. In Claude Code a Stop hook (`.claude/hooks/checkpoint.sh`) enforces this. Last updated: **31 Oct 2026**.

Deeper detail lives in these files; this one summarises them and records decisions that are not written anywhere else:

| File | What it holds |
|---|---|
| [PROGRESS.md](PROGRESS.md) | **The note of the day:** what got done each day, the current lead, what's next, and what's blocked. Read the top entry to know where things stand right now. |
| [docs/ref/Artificia_Website_Revamp_UIUX_Development_Blueprint.pdf](docs/ref/Artificia_Website_Revamp_UIUX_Development_Blueprint.pdf) | **Management's revamp blueprint (29 Oct 2026).** Positioning, sitemap, homepage order, service pages, courses, portal, SEO. The controlling spec for content and order. |
| [docs/superpowers/specs/2026-10-27-artificiaai-site-design.md](docs/superpowers/specs/2026-10-27-artificiaai-site-design.md) | The original approved build spec (27 Oct 2026): platform, admin, leads, redirects, content rules. |
| [PRODUCT.md](PRODUCT.md) | Users, positioning, hard content rules, evidence on hand. |
| [DESIGN.md](DESIGN.md) | The design system in detail: tokens, every component, the stage hero, the loader, admin UI. |
| [content/SOURCES.md](content/SOURCES.md) | Where every image, logo, font and library came from (licences). |
| [README.md](README.md) | Local setup. |
| [MD_OVERVIEW.md](MD_OVERVIEW.md) | Markdown inventory, engineering orientation, documentation conflicts and recommended next work (30 Oct 2026; documentation review, not runtime certification). |

---

## 1. The business in one paragraph

**Artificia AI** (company: Artificia Intellec Pvt Ltd, Vadodara, Gujarat, India) builds AI automation and business software for companies, trains corporate teams to use AI, and runs offline AI courses. Per the blueprint, the website must present it **as a technology and AI automation company first**, with **corporate AI training second** and **courses third**. It must not read as an AI course institute.

| Priority | Pillar | Main call to action |
|---|---|---|
| 1 | Technology, AI automation, software, GoHighLevel/marketing automation, websites | Book a free AI consultation |
| 2 | Corporate AI training (custom-built around the client's own documents and departments, on-site or online) | Request a corporate training consultation (free 2-hour pilot) |
| 3 | Courses: AI Masterclass, offline in Vadodara, 1 hour a day, 30-day Foundation and 60-day Full program, no coding needed | Explore / enquire about a course |

- **Founder shown on the site:** Vijay Linju Raghavan (the only person on the About page).
- **Project owner:** Hardik Aseri approved the spec and signs off on design. Hardik's name and photo were **removed from the public site** on 28 Oct 2026 at Hardik's request.
- **Admin user:** an SEO expert who runs the site's content and SEO alone, without a developer.
- **Contact facts:** Office No. 11, 2nd Floor, Darshanam Arise, Gotri-Sevasi Rd, opposite Osia Mall, Vadodara 391101 · +91 91044 88859 · info@artificiaai.com · WhatsApp 919104488859 (the default contact channel).
- **Visitors:** individuals from Meta ads on phones (evening, mobile network); business buyers from Google search (founders, HR/L&D, operations heads at Gujarat manufacturers, hospitals and similar).

## 2. Links

| What | Where |
|---|---|
| Live site (still the old 2025 site until switchover) | https://artificiaai.com |
| **Design reference for the new look** | **https://landonorris.com/** (hero stage, loader, scroll behaviour, surfaces, type) |
| GitHub repo (private) | `popeyaiartificia-png/artificiaai-website` |
| Hosting | Hostinger Premium shared hosting, LiteSpeed, **no Node.js**. Site root `/home/u447231494/domains/artificiaai.com/public_html`. The same account also hosts popeyai.com and popeyai.io: never touch their folders. |
| Old site backup | `legacy/` folder and git tag `legacy-live-2025-09-17` (reference only) |

## 3. Truth rules (non-negotiable)

These come from the spec, PRODUCT.md and the blueprint's accuracy rule. Breaking them is worse than leaving a section empty.

**Never on the site:** prices, fee ranges or rupee values; batch dates, seat counters, countdowns; "best", "#1", "lowest"; placement, job or income promises; a fixed duration for corporate training; invented clients, logos, testimonials, metrics, certifications, partner badges, security guarantees or ROI claims; a text wordmark in place of the real logo.

**Proof that may be used:**
- **Orkay Tiles International**, named client with permission: Artificia set up and runs their GoHighLevel accounts and built their website. Claims are limited to what is live (Arya calls with no booking or transfer; 18-hour follow-up with no reassignment).
- **Testimonials:** videos from Hatim Sorangwala and Dev Chokshi; written quotes from Alphonsa N. P (Chief Nursing Administrator, Aadicura Super Speciality Hospital), Haritha Nair and Nilesh Chaudasama. Khushi Dodiya's was removed because the video link is dead. Haritha's and Nilesh's old photos were stock images, so their cards show initials.
- **Five "typical outcomes for AI-enabled teams"** (never presented as Artificia's own results): 92% faster lead handling, 10–20 hrs a week saved per employee, 65% less repetitive work, 500% boost in customer response, 4× faster reporting.
- **"100+ professionals trained through corporate training sessions"** (confirmed wording).

**Service availability confirmed by the user, 30 Oct 2026:** all six blueprint categories are available: AI Automation, Agentic AI, Software & Apps, Marketing Automation, SEO/AEO/GEO, and Cloud & Private AI. This resolves the earlier availability question about agentic work, search optimisation and cloud/private deployment. It does not verify client results, metrics, security certifications or guarantees; those still need their own evidence.

## 4. Tech stack and repo

- **PHP 8, plain, no framework, no Composer, no build step.** MySQL. Server-rendered HTML. Plain CSS and JS. GSAP 3.13 + ScrollTrigger self-hosted. Quill 2.0.3 in the admin editor only. All fonts self-hosted.
- **Budgets (from PRODUCT.md):** CSS + JS under 150 KB gzipped (now about 99 KiB with GSAP); hero image under 200 KB (the robot is 50 KB); Lighthouse mobile Performance 90+ and Accessibility 95+. `prefers-reduced-motion` turns every animation off.
- **Run locally:** `bin/serve` serves http://127.0.0.1:8000; `php tests/run.php` runs the tests. The config file lives outside the repo (see README).
- **Deploy:** GitHub, then Hostinger. **Never use the Hostinger plugin's static deploy, because it wipes the site root.** Uploads, the config file and the database must survive every deploy. Plan: staging subdomain (password-protected, noindex), then switchover with 301 redirects.

```
public/            only web-served folder (index.php, assets/css/site.css, assets/js/site.js, assets/img, fonts, vendor)
app/lib/           router, render, pages, leads, media, seo, seo_checks, redirects, auth, mail, settings, seed
app/sections/      the section types (hero, statement, work_gallery, versus, solutions, tools, use_cases, crossoff, card_grid, process_steps, case_study, stats,
                   testimonials, faqs, cta, lead_form, curriculum, people, text_image, rich_text, map_contact, blog_list)
app/views/         layout.php, hero-stage.php (homepage hero), drawings/ (SVG sketches), brands.php (tool logos), icons.php
app/admin/         admin area (pages, posts, templates, media, leads, redirects, settings, users)
content/seed/      seed pages as JSON (home.json etc.), media, redirects, templates, settings
db/migrations/     schema (runs on boot)
tests/             plain-PHP tests
legacy/            the old site, reference only
docs/ref/          the blueprint PDF
```

**Page model:** every page is an ordered list of sections chosen from the library. The SEO expert arranges them in the admin, so every section must look right in any order and with any length of real copy.

## 5. Design direction

### 5.1 How it got here (so nobody re-proposes a rejected direction)

| Date | Direction | Status |
|---|---|---|
| 27 Oct | "Build Notebook": near-black ground, liquid glass panes, lime marker sketches of systems, square corners | Superseded |
| 28–29 Oct | "The Highlighted Textbook": warm paper #F3EFE6, ink type in Roboto Serif, lime used as a highlighter, mono labels, monochrome photos | **Rejected for the homepage.** Still on every other page until each is migrated. Fully documented in DESIGN.md. |
| **29 Oct → now** | **The landonorris.com look**, applied to the homepage **one section at a time**, with content and order taken from the blueprint | **Current direction** |

### 5.2 The current look (homepage)

The site should feel like a top agency / modern AI engineering company, not a course landing page and not a neon gaming brand.

- **Scale:** Lando-size, not compact (Replit-style) sections. Every section after the hero uses the same large type and spacing (serif headlines 2.4–4.8rem, section padding 4.5–8rem). Big type does nothing for SEO by itself; the words in the HTML do.
- **Surfaces:** black stage `#0A0B07` → olive `#232719` (where the hero's scroll ends, then the statement and "How we work") → light blueprint surface `#F4F6F1` with near-black text `#101114` → dark Solutions gallery `#11150F`.
- **Accent:** neon `#D2FF00` (the `--neon` token; used on the stage and the loader). The logo's own lime is `#AED534` and the blueprint suggests `#D7FF00`, so the final accent value is still open (see §8). On light backgrounds, emphasis is olive `#4E5D00` because neon fails contrast there.
- **Type (shared Roboto system, with a user-requested statement exception):** Roboto Serif for display headlines (light weight 320, sentence case, tight tracking); the `*emphasised*` words go italic with a **neon highlighter band**, and on dark ground each highlighted glyph is split cream above the band and ink inside it. Wide/expanded caps in Roboto Flex for section headings in the Lando style, with serif emphasis. Roboto Flex for body text, Roboto Mono for small labels.
- **Corners:** square everywhere (decided 27 Oct). Exceptions on the stage page: the masthead Enquire button (6px) and the loader's pill.
- **Logo:** always the real image (`public/assets/img/artificia-logo.png`, tagline "AI-Driven, Future-Focused.", lime figure #AED534). Never redrawn, never recoloured, never a text wordmark.
- **Imagery:** the robot appears **in the hero and, at the user's request (30 Oct), in the Before AI / With AI section**, nowhere else (the blueprint warns against "stock robot imagery on every section"). Prefer real workflow screenshots, diagrams and code-drawn SVG. New images are made in **Canva** (Higgsfield and Gamma are out of credits).
- **Motion:** purposeful, always showing a process (pulses along a pipeline, a live-run panel, scan lines). Scoped GSAP/CSS reveals and WebGL for the hero and visible contour surfaces. The work gallery canvas is capped at 30fps and pauses off screen. Everything is still under reduced motion.
- **Copy style:** minimal. The user wants the design to lead: short headline, no paragraph in the hero. Plain, factual English with short sentences (many readers' first language is Gujarati or Hindi).

### 5.3 What is built in the new look

1. **Loader** (every page, first page of a visit only): a neon full-screen field, the logo printed through a CSS mask, a "Loading intelligence" pill that fills, then an upward wipe.
2. **Stage hero** (`app/views/hero-stage.php`): a robot head on black; a WebGL **liquid neon ink** reveal that follows the pointer (and wanders on its own when idle or on phones); a live topographic contour field; a 7-second "automation loop" in the blueprint's own terms (input chips Manual tasks / Disconnected tools / Scattered data pulse into the robot's left ear; each crosses the crown as a short neon comet lighting the head's own seams; the robot scans; pulses go out of the right ear to a **"Live run · AI automation"** panel: Data entry automated / CRM & systems connected / Reports & dashboards ready, then a bar chart that holds for the last ~3 s; the page enters mid-way into the first cycle so it is busy from the first moment); a neon livery band around the neck (front half only, fading out at the sides) with running text "ARTIFICIA ✦ AI AUTOMATION ✦ AGENTIC WORKFLOWS ✦ BUSINESS SOFTWARE ✦ CORPORATE AI TRAINING". On scroll (900px and up) it pins, turns olive, and the robot shrinks into a card while the headline runs past as a giant marquee.
   - Copy: eyebrow "AI automation · Software · Training · Vadodara"; H1 **"Automate more. / *Put AI to work.*"**; primary CTA "Book Free AI Consultation"; secondary link "Explore our solutions"; three small "doors" (Automation & software / Corporate AI training / Courses).
3. **Statement** (`statement` section type, one textarea): the large mission line after the hero, on olive. At the user's request (30 Oct), its off-white type now uses Mona Sans at reference weight 660 / width 93 and exact desktop scale (141.111px at 1920; 112.889px at 1536), with matching spacing and #DDE1D2 colour. Lime words/bars use #B2C73A. The serif is a Bodoni Moda substitute; exact Brier Bold requires its own licence. New OFL font subsets total ~31 KB. Other section typography follows the shared system. **Updated 30 Oct:** shorter copy, ?We build *AI automation* and business software. Then we *train your team* to use it. Built in *Vadodara*.?. Updated to the live reference on 31 Oct: a staggered cascade, with new lines starting every .15s. Each line clips its lime bar left-to-right over .6s with power2.out; the right-anchored bar starts retracting .3s later over .6s with power2.inOut. Adjacent line animations overlap as they do on landonorris.com. Seven lines finish in 1.8s. Starts once at top 90% of the viewport, without pinning or a global speed multiplier. Bar bounds now match each line exactly. Copy, typography and the contour background stay unchanged. Font-ready line measurement and ResizeObserver keep the bars aligned on phones and after resize; reduced motion and no-JS output stay static. Original HTML remains available to screen readers with only the animated duplicate aria-hidden. The shared moving contours continue seamlessly from the hero through the workflow. Seed, design sample and the local homepage database are updated.
4. **How we work** (the `tools` section with its `flow` field, styled through the `tools--flow` class): on olive, at the large scale; heading "How we *automate your business*"; the blueprint's seven steps **Discover → Design → Build → Integrate → Deploy → Train → Optimise** as a pipe with a neon pulse (horizontal from 1024px, vertical below); then a quiet "Built on" row of platform logos (GoHighLevel, n8n, WhatsApp, AWS, Google Cloud, Claude, ChatGPT, Gemini). Only GoHighLevel, WhatsApp and AWS are named in the blueprint; the rest come from the site's own content (see §8).
5. **Work gallery** (`work_gallery`, added 31 Oct): inserted after How we work and before Before AI / With AI, at the user's request for the reference's floating image section. An olive-to-ivory animated contour ground and a loose six-visual composition: a real Orkay Tiles desktop website preview, a horizontal GoHighLevel setup/management/support illustration linked to the Orkay case study, plus a monitor/headset/helpdesk illustration captioned "Orkay Tiles IT Support Team" and unbranded conversation, knowledge and voice interfaces. Heading: "Our work. Built around your business." (expanded to six words at the user's request) and a general gallery statement that can be replaced by a client review. The local preview now has clearly labelled draft wording for Pavin, Orkay Tiles: "One partner for our website, automation and AI." Captions describe capabilities; no invented client names, testimonials, deployments or performance results. Desktop (1100px+ wide, 720px+ high) uses a scroll-driven horizontal scene with parallax, a pinned viewport, persistent previous/next controls and focus-driven keyboard navigation. Smaller screens, reduced motion and no-JS keep every item in the document flow. Other CMS combinations also use the static grid, avoiding overlapping duplicate layouts. Captions, descriptions, links, visual choices and image replacements are editable in the section registry. The homepage now loads only the desktop website capture (about 145 KB); the former mobile capture remains available in the section library but is not displayed here; no new libraries, fonts or remote embeds. Its dedicated contour canvas renders only while visible. The local homepage was updated with a saved prior snapshot and page revision.
6. **Before AI / With AI** (new `versus` section type, two sides; rebuilt 30 Oct after the user said the first pass was not designed properly): landonorris.com's "On track / Off track". Two heads in profile face each other across the page, cut by its edges and drawn at the same head size: a person on the left, the robot on the right, both in colour. Between them a small italic serif "Before" / "With" over a huge heavy "AI" on each side, set close like Lando's TRACK TRACK; a bold neon marker strikes out the AI before and circles the AI with (the circle rests drawn; hovering Before swaps them). On laptops and phones one continuous, unpinned scroll sequence raises the original curved white surface directly over the olive, brings the heads in subtly and reveals the copy. At the user's request, the contour lines now remain visible over the white in soft grey, including the static reduced-motion drawing; only the background's existing foot mask fades toward the next section. The grey-layer experiment was reverted at the user's request. The bottom fade has been removed from both figures at the user's request; narrower reading columns and outward figure offsets keep text clear. Hover moves only the robot (a 2–3% lean with a subtle glow); the man never reacts to the pointer and stays still and sharp. Phones keep the heads in a row on top and the sides stacked. Responsive GSAP matchMedia reverts the sequence across the 1100px breakpoint; reduced motion remains static and keyboard focus reveals any unfinished arrival. Text: Before → "Data copied by hand, tools that don't connect, and leads left waiting for a reply." (static clock tile labelled Manual, no link); With → "After a consultation, we build the automation and train your people to use it." (button: corporate AI training). **Person replaced after the user flagged the crop:** a clean transparent middle-aged profile closely matching the user's reference (swept-back hair, salt-and-pepper beard, navy textured shirt) now replaces the younger generated portrait. Uses `person-profile-mature.webp` (800×1067, ~124 KB) and a responsive 420×560 version (~40 KB). The figures retain their transparent cutouts but have no bottom fade; head scale and crown alignment are matched to the robot. Decorative illustration, not a real team member or client. The pending Canva image is no longer needed. **Robot body fixed (30 Oct):** the old cutout left the shoulder and joint see-through; they are now solid, re-cut from the same source photo, and run to the section edge like the man's body. That photo ends at the shoulder, so a lower torso would need a new image. On phones and tablets both figures now end on one shared line. **Foot (rebuilt 31 Oct, at the user's request):** when the dark Solutions gallery follows, it floods this section like water rising over a survey map, replacing the shallow arc, which still read as a hard line. `.versus__dusk` holds a WebGL canvas drawn by the same shader and terrain as the contour lines (`FLOOD` in site.js). The dark's edge is a moving contour with one neon line on its crest, and stepped tones run from dark to paper like map elevation bands, bunching and spreading with the terrain. A slow swell keeps it moving when the page is still. Scroll drives how far it has climbed (`bottom 100%` to `bottom 12%`, `power4.in`, scrub 1, so it keeps flowing briefly after the page stops). It stays calm and late in the middle, where the copy sits, and livelier over the figures. Reduced motion, no JS and no WebGL fall back to a static CSS arc. A soft white-to-grey-to-black fade was rejected: it read as fog and dimmed the neon button.
7. **Problem → outcome: removed from the homepage** (30 Oct, at the user's request, after seeing it rebuilt as a crossed-off to-do list). Before AI / With AI now runs straight into the dark Solutions gallery. The `crossoff` section type stays in the library, on no page, and the removed section's content is in `page_revisions`. The older `use_cases` type is unchanged and still used on 11 other pages.
8. **Solutions overview** (`solutions`, revamped 31 Oct): Editorial grid on #11150F, directly after Before AI / With AI. Three columns at 1100px and above, two at 640-1099px, one below 640px; all six services appear in the page flow. A ruled OUR SOLUTIONS eyebrow and dynamic service count introduce a large cream serif heading with plain lime italic emphasis. The six cards reuse three complete palettes: lime AI automation, dark olive Agentic AI, ivory Software & apps, ivory Marketing Automation, lime Search, and dark olive Cloud & Private AI. The second row shuffles the first row's colors; text, borders and illustration colors follow each palette. Each card has category metadata, CMS title/description, an original inline SVG interface illustration, capability caption, short outcome line and circular arrow. Hover straightens/lifts the drawing, rotates the small star, inverts the arrow and pulses connection routes. The existing staggered entrance and keyboard completion remain; reduced motion disables movement and no-JS retains every card. Horizontal scrolling, previous/next controls and the range counter are removed. Service routes and the preceding contour flood remain. All registry illustration choices, including knowledge, are supported. No new images, fonts or libraries. Agentic AI uses the existing RAG page; SEO/AEO/GEO and Cloud & Private AI link to /services#enquire pending dedicated detail pages.

Rebuilt sections are scoped in CSS with `.hero--stage ~ .sec--X` (How we work: `.hero--stage ~ .tools--flow`), so the same section types keep the paper look on other pages. The statement, Before AI / With AI, crossed-off list and Solutions gallery are their own types, so their looks are the same everywhere.

### 5.4 Decisions the user made (do not undo)

- **29 Oct:** follow the blueprint's order: technology first, training second, courses third.
- **29 Oct:** the paper + highlighter redesign is rejected for the homepage; use the Lando look.
- **30 Oct:** hero copy cut to the minimum (H1 + CTAs, no paragraph); headlines keep the old design's Roboto Serif + highlighter.
- **30 Oct:** **After How we work the page turns light (smooth, scroll-driven), into a Before AI / With AI split with one person and one robot**, modelled on Lando's "On track / Off track".
- **30 Oct:** **Lando scale, not Replit scale:** a large statement section follows the hero, and every section is built at the same large size.
- **30 Oct:** **The man in Before AI / With AI must never blur or fade, wherever the cursor is.** No hover effect may touch him.
- **30-31 Oct:** **No section may start abruptly, and the join must read as an animation.** Before AI / With AI hands over to Solutions through the flood, not a straight edge or a plain curve. Flat grey bands and soft grey fades have both been tried and not kept.
- **30 Oct:** **Problem → outcome (blueprint item 5) is removed from the homepage.** The user removed it after seeing the crossed-off list. Do not put it back unless asked.
- **30 Oct:** **no client info (Orkay) straight after the hero.** General content goes there instead. The blueprint's trust strip (item 4) is **omitted** until verified proof exists.
- **28 Oct:** Vijay is the founder; Hardik is removed from the site.
- **27 Oct:** square corners everywhere.
- **Tooling:** do not use the Claude in Chrome extension; check pages with headless Edge/Chrome from the command line (screenshots), or with curl.

## 6. Status

### 6.1 Homepage, section by section (blueprint order)

| # | Blueprint item | State |
|---|---|---|
| 2–3 | Hero + CTAs | ✅ Done (stage hero) |
| 4 | Trust/proof strip | ⛔ Omitted by choice until verified proof exists |
| — | Statement (Lando mission line; not a blueprint item) | ✅ Done (straight after the hero) |
| 7 | How we work | ✅ Done (after the statement, by choice; rescaled 30 Oct) |
| — | Before AI / With AI (Lando "On track / Off track"; not a blueprint item) | ✅ Done (after How we work; the turn to the light surface) |
| 5 | Problem → outcome | ⛔ Removed by the user on 30 Oct (the `crossoff` type stays in the library, on no page) |
| 6 | Solutions overview | ✅ All six blueprint service families, confirmed available by the user |
| 8 | Featured offer: free AI consultation & automation audit (scope and limits stated) | ⏭️ **Next** |
| 9 | Corporate training band | To do |
| 10 | Case studies / demo projects (Orkay, labelled; demos labelled as demos) | To do |
| 11 | Courses preview + "Browse all courses" | To do |
| 12 | Why Artificia | To do |
| 13 | Resources/insights preview | To do (no articles exist yet) |

Below the rebuilt sections, `content/seed/pages/home.json` **still carries old paper-design sections** that will be replaced or removed as the list above is built: "The tools you will learn", "Sixty days, in four stretches", the Orkay case study, "Who the masterclass is for", stats, testimonials, FAQs, the "free pilot" CTA and the lead form.

### 6.2 The rest of the site

- **Pages (seeded, paper design):** about, contact, privacy, terms, blog, services (+ gohighlevel, website-development, content-marketing, rag-custom-automation), corporate-ai-training (+ healthcare, manufacturing), ai-masterclass (+ students, working-professionals, business-owners), case-studies/orkay-tiles.
- **Admin (done):** login, forgot/reset password, users, settings and import, setup; media library (resize + WebP); section-based page and post editor with templates and duplicate (Quill); per-page SEO checks on the dashboard and in the editor; redirects; leads inbox with spam checks, Google/Meta tracking, email alert, n8n webhook forwarding, CSV export; error pages, sessions, canonical host, hardening.
- **Admin (not built yet; the spec's "Step 2"):** CSV bulk page creation, a restore UI for saved versions (snapshots are already written to `page_revisions`), broken-link check, internal-link picker.
- **Old-URL 301s** are seeded (e.g. `/landing-page/` → masterclass, `/co-orporate/` → corporate training). Meta ads still point at `/landing-page/` and should be repointed after launch.

### 6.3 Repo state

- The last commit is from **28 Oct 2026** ("Final fix wave before staging").
- **All work from 29–30 Oct is uncommitted:** the stage hero, loader, paper redesign, How we work, Before AI / With AI, Solutions, the new logo, new fonts and DESIGN.md updates. This should be committed soon.
- No staging deploy is recorded in the repo yet.

## 7. Next plans (recommended order)

1. **Commit the 29–30 Oct work** so two days of design aren't sitting only on one disk.
2. **Finish the homepage in blueprint order:** Free consultation offer → Corporate training band → Case studies/demos → Courses preview → Why Artificia → Resources. Then drop the leftover paper sections listed in §6.1. One section per round, shown to the user before moving on.
3. **Header/nav to the blueprint:** Solutions ▾ · Corporate Training ▾ · Courses ▾ · Resources ▾ · About, with one dominant "Book Free AI Consultation" button. Dropdowns must open on click and keyboard focus, not hover only.
4. **Migrate the other pages to the new look:** services/solutions first, then corporate training, then masterclass/courses, then about and contact.
5. **Decide the URL structure:** keep today's routes (`/services`, `/ai-masterclass`, `/corporate-ai-training`) or move to the blueprint's (`/solutions/...`, `/courses/...`, `/corporate-training/...`) with 301s. The redirects admin already supports either.
6. **Measure:** a Lighthouse mobile run on the homepage (the WebGL hero is the risk to the 90+ target), plus keyboard and contrast checks.
7. **Staging deploy → user review → switchover → Search Console sitemap.**
8. **Admin Step 2** features (§6.2).
9. **Later, separate phases:** student portal/LMS and payments (the blueprint wants them; the original spec ruled them out of v1), plus resources/articles for SEO and AEO.

## 8. Open decisions (need management or the user)

- **Final accent colour:** logo lime `#AED534`, stage neon `#D2FF00`, or the blueprint's `#D7FF00`. The blueprint says to validate it against the logo.
- **Service availability resolved:** the user confirmed all six blueprint service families are available on 30 Oct. Dedicated detail pages for Agentic AI, SEO/AEO/GEO, and Cloud & Private AI remain part of the service-page migration.
- **Course names:** the spec says "30-day Foundation / 60-day Full program"; the blueprint says "30-Day Basic Intensive / 60-Day Advanced Intensive", and it lists 13 more courses that all need confirming.
- **Free consultation vs free audit:** the scope of each, and whether the 15-minute flow audit and the 2-hour corporate pilot stay.
- **URL structure** (see §7.5) and **www vs non-www**.
- **Whether the five "typical outcome" stats and "100+ professionals trained" stay on the homepage.** The blueprint says to show no metrics until a single figure is verified with a source.
- **Student portal:** in scope now or later.
- **Before AI / With AI** (rebuilt 30 Oct, awaiting the user): the two lines of copy, the With AI link target (corporate AI training); Before AI now uses the approved static Manual tile, and the new generated transparent middle-aged person profile matching the user's reference (installed 30 Oct; the Canva download is no longer needed).
- **The "Built on" logo row under How we work** (asked 30 Oct, awaiting the user): trim to the four used on real client work (GoHighLevel, n8n, WhatsApp, AWS; recommended), keep all eight, or remove the row. A row of third-party logos can read as a partner claim.

## 9. How to work on this project (for AI assistants)

- Read this file, then DESIGN.md and PRODUCT.md, before changing anything. The blueprint PDF is the authority on content and order; the user's decisions in §5.4 override it where they differ.
- Build one homepage section at a time in the Lando look, and show the user each one. Tell the user every time the design changes or new pages are built.
- Stay on the PHP + plain CSS/JS + GSAP stack (no Node on the host). No new libraries without a reason.
- Never invent facts. If a claim has no source, leave it out and flag it.
- Check visually with a headless browser from the CLI, not the Claude in Chrome extension.
- Images: Canva. Record the source of every new raster in content/SOURCES.md.
- Commits: plain descriptive messages; changes the user asked for directly are suffixed "(Hardik's change)".
- **Checkpoint with every change:** update this file's status sections and Changelog, and today's PROGRESS.md entry. Edit those two files last, because the Stop hook checks that they are newer than every other repo file (it ignores `.git/`, `.claude/`, `storage/` and `public/uploads/`).

---

## Changelog

- **2026-10-31** Revamped Solutions at the user's request: replaced the horizontal gallery with a responsive six-card editorial grid, lime/ivory feature surfaces, a cleaner lime italic headline, original interface SVGs, category labels and refined hover/focus states. Actual :8080 Edge checks passed at ten widths (360-1920px), with no JS errors or overflow; links, keyboard, reduced motion and no-JS passed. PHP/JS syntax and 15 Registry/ContentRules checks passed (GD enabled). Desktop/tablet/phone previews are in storage/solutions-revamp-*.png.

- **2026-10-31** Hero primary action refined to "Book Free AI Consultation" in the home seed and local database, with its previous revision preserved. Lime button increased to 50px minimum height and slightly larger text/padding. Verified the served CTA, CSS and /services#enquire destination.

Newest first. One line per change.

- **2026-10-31** Rebuilt the Before AI / With AI to Solutions join as a flood over the contour map, after the user said the arc should be more seamless, elegant and animated: moving organic edge, neon crest, stepped tones, slow swell. Actual :8080 Edge checks at 11 widths from 2560 to 360px: no JS errors or overflow, scroll-back restores it, and the white is gone by the time the join reaches 23-29% of the screen. Pixel checks at 7 widths: it first touches the With AI button only near the top of the screen, with the button's top within about 10% of the viewport height (about 14% at 820px), and the button stays clickable. Reduced motion and no WebGL keep the CSS arc. Tests 149 pass; the 24 failures are environment only. Smoothness on a real phone is not measured.

- **2026-10-30** Removed Problem → outcome (the crossed-off list) from the homepage at the user's request: local DB (revision saved) and home seed. Before AI / With AI now meets the dark Solutions gallery directly. The `crossoff` type, its CSS and JS and the design sample stay in the library, on no page. Actual :8080 Edge checks at 1920/1536/1024/390px: section gone, no gap between the two, no JS errors or overflow. Tests 149 pass; the 24 failures are environment only.

- **2026-10-30** Blended Before AI / With AI into Solutions: the dark rises over the white on the same curve as the section's arrival, rests as a shallow arc so no straight edge shows, and carries the contour lines into the head of Solutions. More room under the stacked copy below 1100px. Actual :8080 Edge checks at 10 widths from 2560 to 360px: no JS errors or overflow, scrolling back restores the arc, and the arc overlaps the With AI button by at most 21px (1536px, as the button leaves under the masthead; it stays clickable). Reduced motion and no JS keep a resting arc. Tests 149 pass; the 24 failures are environment only. Real-phone scrolling performance not measured.

- **2026-10-30** Before AI / With AI: the man no longer reacts to the pointer (removed his hover fade to 80% and sideways shift, which read as blur). The robot's hover is unchanged. Actual :8080 Edge pixel checks at 1920/1536/1280px, cursor over both sides, text, tile, button and both figures, plus keyboard focus: his face changes by at most 1 of 255 levels, at opacity 1 with no transform or filter.

- **2026-10-30** Problem → outcome redesigned as a crossed-off to-do list: new `crossoff` section type (registry, template, CSS, site.js, test expectations, design sample, home seed, local DB with revision). Problems fitted to the full column and struck with a neon multiply marker; crossed-off words go hollow; outcomes use the blueprint's words; the Before/After labels are gone. Removed the old homepage-only `use_cases` overrides; other pages keep the paper rows. Actual :8080 Edge checks at 11 widths from 2560 to 360px: no JS errors or overflow, ink within about 1.5px of the column edges at 1536px. Also checked: lines play in order after a fast jump, resize, reduced motion, no JS, keyboard focus, long CMS titles. Tests 149 pass; the 24 failures are environment only.

- **2026-10-30** Before AI / With AI robot: replaced the see-through lower body with a solid shoulder, chest and joint re-cut from the source photo (head and neck unchanged; 160 KB, was 168 KB). Phone/tablet figure feet aligned with one CSS margin. Actual :8080 Edge checks at 1920/1536/1280/1100/820/390px: no JS errors or overflow, robot reaches the section foot on desktop, feet level below 1100px. Tests 149 pass; the 24 failures are environment only (GD, php-cgi, symlink, .htaccess CRLF).

- **2026-10-30** User confirmed all six blueprint services are available. Expanded the Solutions gallery to the six exact families; knowledge assistants are covered under Agentic AI. Added original agentic, search and cloud/private SVGs and CMS options. Seed, design sample and local DB updated with revision. Browser checks passed at 1536/820/390px and 1280px reduced motion, including all six cards, final counter, keyboard, resize, links and no-JS. PHP lint and 15 Registry/ContentRules tests passed. Removed stale availability blockers; dedicated service detail pages remain future work.

- **2026-10-30** Built the blueprint's next missing section: a dark Solutions gallery after Problem → outcome, using four confirmed services and original SVG illustrations. Replaced the old three-path card block; added editable CMS fields, native scroll/snap, arrows and keyboard support, staggered entrance and reduced-motion/no-JS fallbacks. Seed, design sample and local DB updated with revision. Actual :8080 browser checks passed at 1536/820/390px and 1280px reduced motion, including links, resizing and focus. PHP checks: 27 pass, one pre-existing Windows CRLF .htaccess regex failure (unchanged files; LF-normalised check passes). Next build is the scoped free consultation offer.

- **2026-10-30** Kept the Before AI / With AI contour background visible on white and made its lines neutral grey. Removed the arrival opacity fade and scoped grayscale to this canvas. Desktop, phone and reduced-motion browser checks passed, including scroll stages and visible grey pixels; JS syntax and diff checks passed.

- **2026-10-30** Replaced the Before AI CTA with the approved non-clickable Manual clock tile, matched to the With AI CTA's 68px square and alignment. Added an optional editable status_label field; updated seed, design sample and local DB with revision snapshot. Browser checks passed for semantics, unchanged With AI target, hover, alignment, mobile and reduced motion.

- **2026-10-30** Restored the original dark-to-white comparison transition at the user's request: removed the grey bridge and restored its preceding white-dawn, copy and circle timings. Current typography, portraits and removed figure fades are preserved. JS/PHP syntax and diff checks passed.

- **2026-10-30** Added the requested grey phase before the white comparison surface: a grey leading curve with a softened upper join, followed by the white curve on the same scroll clock. Delayed copy until the white is underneath. Actual :8080 desktop/mobile screenshots and pixel checks confirmed a visible grey stage, full white finish, no overflow/gaps or JS errors; figure-bottom masks remain removed, and static fallbacks pass.

- **2026-10-30** Statement typography matched to measured reference size, Mona Sans weight/width and off-white/lime colours. Installed small OFL font subsets (~31 KB); lime serif uses Bodoni Moda as a disclosed Brier substitute. Actual :8080 desktop/tablet/phone font, layout, sequential animation, resize and accessibility checks passed.

- **2026-10-30** Inspected landonorris.com's live line animation and changed the statement to its shared-mask technique: left-to-right reveal of a full-width bar, then right-anchored bar retraction over the underlying text. Blended the phases within each line while preserving strictly sequential lines (.60s each). Actual :8080 desktop/mobile sequence, resize, reduced-motion and no-JS checks passed.

- **2026-10-30** Presentation polish for the statement: removed holds/gaps, softened easing, synchronised the text clipping with the bar edge, and delayed entry until more of the heading is in view. Kept strict one-line-at-a-time playback (.68s per line). Actual :8080 desktop/mobile sequence, resize and accessibility-fallback checks passed.

- **2026-10-30** Corrected statement timing after user feedback: each line now completes its own bar → text reveal before the next line starts (.76s per line). Actual :8080 desktop/mobile checks assert exactly one active bar and the correct visible text prefix at every line; resize/reduced-motion/no-JS checks pass.

- **2026-10-30** Statement shortened to 18 words and rebuilt with a bar-first, line-based neon wipe matching the requested reference behaviour. Preserved static accessible HTML and responsive/reduced-motion fallback; local home saved with revision snapshot. Verified on the actual PHP/MySQL preview at :8080 (1536/390px, resize, reduced motion and no-JS); 15 content/registry checks passed.

- **2026-10-30** Removed the bottom gradient masks from both Before AI / With AI figures at the user's request. Portrait/robot keep their transparent silhouettes with no lower opacity fade; the dawn scroll transition is unchanged. Isolated desktop/mobile Edge checks confirmed mask-image: none, no JS errors and no overflow.

- **2026-10-30** With AI circle reduced again from 108% to 90% of the word box, centred inside the letters while retaining its asymmetric shape and −8° tilt. CSS-only adjustment; diff whitespace check passed.

- **2026-10-30** With AI marker made smaller and more abstract at the user's request: compact asymmetric loop, 108% word-box size, −8° tilt and lighter 6-unit stroke; now crosses the letterforms rather than enclosing them with a large ring. PHP lint and isolated Edge checks at 1903/1280/390px passed without JS errors or overflow.

- **2026-10-30** Replaced the younger generated portrait with a middle-aged man closely matching the user's reference: mature face, swept-back hair, salt-and-pepper beard and navy textured shirt. New responsive transparent WebPs (~124 KB / ~40 KB); head scale/alignment adjusted, existing seamless silhouette fade retained. PHP lint and isolated headless Edge desktop/mobile checks passed.

- **2026-10-30** Replaced the Before AI / With AI man with a newly generated transparent Indian profile after feedback on cutout borders. Responsive WebP (~94 KB desktop / ~29 KB phone), matched head alignment, and individual silhouette fades reaching zero before section clipping. PHP lint and isolated headless Edge preview checks passed; source provenance recorded.

- **2026-10-30** Refined Before AI / With AI for seamless flow: removed the full-screen scroll pin, synchronised dawn/figures/contour fade/copy in one responsive scroll sequence, softened hover and figure edges, protected reading columns, and retained static reduced-motion output. Verified using real PHP templates and homepage seed in an isolated headless Edge preview (1536/1280/390px and breakpoint resize); full application integration remains unverified.

- **2026-10-30** Added MD_OVERVIEW.md: reviewed the ten existing Markdown files, summarised project scope and documented status, flagged conflicting requirements, and proposed next work for the user's engineering review. No application changes or runtime tests.

- **2026-10-30** Before AI / With AI redesigned after user feedback: two heads in profile at the same size facing each other (person stand-in re-cut head-and-shoulders), small serif word over a huge "AI", bold neon strike/circle marks, heads close in on the olive and then a pinned dawn raises the light surface (phones keep the fade); fixed SVG marks squeezed by the global `max-width: 100%`. *(uncommitted)*

- **2026-10-30** New `versus` section "Before AI / With AI" after How we work: scroll-scrubbed turn from olive to light, cut-out man (greyscale) and robot sliding in from the edges, neon hover marks, robot glow; cut-outs made locally with rembg (`public/assets/img/versus/`). Contour lines now run through it; their fade moved to its foot. *(uncommitted)*

- **2026-10-30** Contour background extended behind How we work (seamless from the hero through the statement), fading out before the light section; it now renders a screen early so it never pops in. *(uncommitted)*

- **2026-10-30** Statement background: the hero's moving topographic contours now continue behind it without a seam (shared `topoField` in site.js, offset canvas, faded at the bottom). *(uncommitted)*

- **2026-10-30** Statement text reduced about 30% at the user's request (1.9–5.75rem, was 2.1–8rem; 92px on a 1920 screen, was 128px). Line breaks unchanged. *(uncommitted)*

- **2026-10-30** Lando scale: new `statement` section type (huge caps mission line, serif neon key words, word-by-word scroll fill) placed straight after the hero; How we work rescaled to the large size (heading 2.4–4.8rem, bigger steps and logos, "Built on" row full width) and re-scoped to a `tools--flow` class. *(uncommitted)*

- **2026-10-30** Stage hero polish: neck band shows only its front half as one seamless, edge-faded band; a neon "thinking" comet crosses the crown seams per input (shader `uThink`); loop copy now general, in the blueprint's terms (Manual tasks / Disconnected tools / Scattered data → "AI automation": Data entry automated / CRM & systems connected / Reports & dashboards ready); loop compressed so the chart shows at ~3.9 s and holds ~3 s, and the first cycle starts as the page enters (chart ~5.1 s after load, was ~6 s). *(uncommitted)*

- **2026-10-30** How we work: content made general (blueprint item 7's seven steps, no client info), pipe goes vertical under 1024px; hero run panel notes removed and copy column widened; stage marquee text about 40% smaller. PROGRESS.md started. *(uncommitted)*
- **2026-10-30** Checkpoint added: PROGRESS.md (daily note), plus a project Stop hook (`.claude/settings.json` → `.claude/hooks/checkpoint.sh`) that blocks the end of a turn until CONTEXT.md and PROGRESS.md are newer than every changed repo file. CLAUDE.md updated to match.
- **2026-10-30** CONTEXT.md created: full project handover (business, links, rules, design history, status, next plans, open decisions). CLAUDE.md added so every session reads it and keeps it updated.
- **2026-10-30** Hero copy made minimal ("Automate more. / *Put AI to work.*", no paragraph). Headlines use Roboto Serif + split neon highlighter. Orkay/trust strip removed from directly after the hero; "How we work" pipe placed there instead. *(uncommitted)*
- **2026-10-29** Homepage direction switched to the landonorris.com look, built in blueprint order. Built: loader, stage hero (WebGL liquid reveal, automation loop, livery band, scroll pin), Problem → outcome. New logo with the tagline "AI-Driven, Future-Focused." Blueprint PDF added to docs/ref. *(uncommitted)*
- **2026-10-29** "Highlighted Textbook" paper redesign made, then rejected for the homepage; still on the other pages. *(uncommitted)*
- **2026-10-28** Admin built: auth, users, settings, media, section editor with templates, SEO checks, redirects, leads (spam, tracking, email, n8n, inbox, export), hardening. Vijay set as founder; Hardik removed from the site. AWS and Google Cloud mentioned across services. Cover hero, testimonials and Orkay wording updated.
- **2026-10-27** Live site backed up into legacy/. New PHP app: router, rendering, 17-section library, media (WebP), redirects, sitemap, robots, schema, seed content for all launch pages. Design: black ground + liquid glass + lime sketches, square corners everywhere.

- **2026-10-31** Undid the statement speed redesign at the user's request; restored its previous typography, contour canvas and sequential line reveal.
- **2026-10-31** Statement timing only: added GSAP timeScale(4), reducing the seven-line reveal from 4.2s to 1.05s. Desktop/phone runtime checks passed; original fonts, canvas, layout and animation sequence retained.

- **2026-10-31** Corrected statement timing to clearly sequential .30s line starts and 2.04s total. Desktop/phone frame checks confirm only one active line and all later lines hidden.

- **2026-10-31** Inspected the live landonorris.com mission reveal and matched its actual timeline: .15s line stagger, .6s power2.out reveal, .6s power2.inOut bar retraction starting .3s later, top 90% trigger. Local seven-line timeline matches every reference tween. Checked 1920/1536/820/390/320px plus no-JS/reduced motion.

- **2026-10-31** Reused the first three Solutions card palettes for cards four to six in shuffled order: ivory, lime, dark olive. Shared complete palette tokens keep text and SVGs coordinated.
- **2026-10-31** Solutions palette verification passed at 1536/390px: each paired card matches all eight palette tokens, no overflow or JS errors; desktop grid visually reviewed.

- **2026-10-31** Added the requested reference-inspired floating gallery before Before AI / With AI, featuring real Orkay desktop/mobile website captures and four neutral service visuals. Desktop horizontal scroll, persistent controls, keyboard access, static/mobile fallbacks and CMS editing are included. Prior local homepage snapshot and revision saved.
- **2026-10-31** Work gallery verification: 10 widths from 320-1920px, desktop controls, all six keyboard links, resize out of the pin, reduced-motion and no-JS passed with no JS errors or overflow. Registry/content/render checks: 28 pass; one unrelated existing Windows .htaccess dot-rule assertion fails. CSS + JS including GSAP is about 95 KB gzip. Desktop/mobile screenshots reviewed.

- **2026-10-31** Renamed the floating gallery heading to "Our work", keeping the existing serif typography and lime emphasis. Home seed and section-library sample updated.

- **2026-10-31** Expanded the gallery heading to six words: "Our work. Built around your business." Increased the responsive display scale to 40-80px on larger screens and 36-48px on phones, retaining serif typography and lime emphasis.
- **2026-10-31** Expanded gallery title synced to the local preview with revision saved; actual browser checks at 1536/390/320px confirm the six-word heading and no overflow.

- **2026-10-31** Prepared a testimonial variant for the gallery and requested approved review copy for "Pavin, Orkay Tiles". The gallery now supports an optional approved review with a reviewer name/company, replacing the anonymous thought. Pavin and Orkay Tiles are prepared as the requested attribution; exact or approved review wording is still pending from the user, so the live general statement remains unattributed. The review layout uses a small byline instead of the decorative scribble, and longer reviews fall back to the open static layout if they overlap the website image.

- **2026-10-31** Drafted the requested one-stop IT review wording: "One partner for our website, automation and AI." for Pavin, Orkay Tiles. Status remains draft, visibly labelled in local preview; non-local output omits the draft and attribution until explicitly approved. Six render-state checks passed (local draft, non-local draft/missing/unknown status, approved, empty-copy fallback).
- **2026-10-31** Draft review saved in local homepage with prior revision retained. Browser checks at 1536/390px passed for wording, attribution and visible pending-approval label, with no overflow or JS errors.

- **2026-10-31** Replaced the gallery's mobile Orkay website mockup with a landscape GoHighLevel panel: setup, management and ongoing support (support confirmed by the user). Links to the existing Orkay case study. Added the ghl illustration option and retained desktop scrolling with a mobile-or-GHL first slot; the other five visuals are unchanged.
- **2026-10-31** GHL panel checks passed at 10 widths plus keyboard/navigation and reduced-motion/no-JS fallbacks. Confirmed landscape proportions and all support labels fit at 320px. No JS errors/overflow; 21 registry/render tests pass, with one unrelated existing Windows .htaccess assertion failure.

- **2026-10-31** Renamed the gallery workflow card caption from "Connected workflows" to "Orkay Tiles IT Support Team", exactly as requested. Home seed, design sample and local DB updated; rendered label verified and prior revision retained.

- **2026-10-31** Replaced the Orkay IT support card's workflow graphic with an original monitor, headset and helpdesk SVG. Added a dedicated support visual; original workflow remains in the library. Updated the shape guard and shared the previous card's desktop/mobile placement. Label, title and other gallery cards are unchanged; local DB revision saved.
- **2026-10-31** Support illustration verified at 1536/390px: old workflow nodes absent, new SVG visible, horizontal desktop scene retained, and no overflow or JS errors.

- **2026-10-31** Rebuilt gallery background and effects from live reference measurements. Matched to the live reference on 31 Oct: the gallery ground moves from #282C20 through grey to #F4F4ED, with contour ink #363B25 to #D4D7CA. Uses a single viewport-sized WebGL canvas in an absolute terrain holder with a sticky child, broad slowly warped contours and subtle pointer response. Rendering is capped at 30fps, DPR capped at 1, and pauses off screen or when the document is hidden. The existing SVG remains the fallback for reduced motion, disabled JavaScript, missing WebGL or context loss. Scroll color uses the measured power1.out progression; desktop color starts with the pinned scene so the added Artificia heading does not advance the transition early. The scene uses scrub 1 and scales its track to 2.9 viewport heights, with four-rem relative picture travel and the measured .6s/.6s/.3s lime caption wipes. Whole frames move gently so UI text and the real website screenshot stay intact. The review stays narrow and retains its visible draft label. Text/control colors adapt to the light ground; controls avoid the floating WhatsApp button. Horizontal layout requires at least 1100px width and 720px height and enough travel/quote clearance; other layouts keep normal flow. The following Before AI / With AI arrival starts on the gallery's final ivory ground, avoiding a flash back to olive.
