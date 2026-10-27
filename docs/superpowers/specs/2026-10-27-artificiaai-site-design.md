# artificiaai.com rebuild: one site, a self-serve SEO admin, safe leads

Date: 27 Oct 2026 · Owner: Hardik Aseri (Artificia Intellec Pvt Ltd) · Status: approved by Hardik, 27 Oct 2026

Items marked **(our call)** were proposed during design and not explicitly decided by Hardik. Everything
else was decided by him.

---

## 1. Why this exists

artificiaai.com today is three unrelated things:

- **The main site** is a 2025 Bootstrap template. It's backed up in this repo at tag `legacy-live-2025-09-17`. It
  pitches recruitment and "AI R&D", which Artificia doesn't sell, and carries contradictory stats and
  unverifiable testimonials.
- **`/landing-page/`** is the Meta-ads page for the individual AI Masterclass. It lives on the server
  only and isn't in this repo.
- **`/co-orporate/`** is the corporate training page, also server-only. It contains invented
  testimonials, a logo wall of brands that were never clients, and a rupee value on a free audit.

The rebuild replaces all three with **one site** that tells the truth about the 2026 business. The single
most important outcome:

> **An SEO expert can run the site's SEO on their own — create pages for new audiences, edit titles,
> text and images, write posts, fix redirects — without Hardik and without a developer.**

Second: **every lead is kept and counted.** Every lead is saved, never silently lost, and reported to Google
and Meta so ad spend learns from real enquiries.

## 2. What Artificia sells (the site's content spine)

1. **AI Masterclass for individuals.** Offline and live in Vadodara, 1 hour a day. There's a
   **30-day Foundation** track and a **60-day Full** program (Foundation plus agents, MCP, RAG,
   voice agents, deployment, Claude Code, local LLMs). It's for students, working professionals,
   founders and freelancers. No coding needed.
2. **Corporate AI training.** Every program is custom-built for the client: discovery, then a
   curriculum built around the client's own documents and departments, then sessions that each end
   in a working deliverable.
   - **Length and format are set by the client's needs.** It can be on-site, or online over
     Microsoft Teams and similar tools.
   - **Don't state a fixed duration** (no "60 days" and no session count) for corporate training.
   - The next step offered is a **free 2-hour pilot session**.
3. **Services for businesses.** The site leads with:
   - GoHighLevel setup and deployment
   - website development
   - content development and marketing
   - RAG-based custom automation (AI that answers from a company's own documents)

   Below those sits the AI automation catalogue: workflow and system integration, document and data
   processing, departmental AI assistants, and agents. The next step offered is a **free 15-minute
   flow audit**, with no rupee value attached.

**Proof that may be used:**

- **Orkay Tiles International, named as a client.** Hardik has their permission. They're the first
  case study: Artificia set up and runs their GoHighLevel accounts, and also built their website.
- **Six real testimonials.** There are three videos, from Hatim Sorangwala, Khushi Dodiya and Dev
  Chokshi, and three written quotes:
  - Alphonsa N. P, Chief Nursing Administrator at Aadicura Super Speciality Hospital
  - Haritha Nair, AI design student
  - Nilesh Chaudasama, working professional
- **The five approved outcome stats**, always labelled as *typical outcomes for AI-enabled teams* and
  never as results Artificia produced:
  - 92% faster lead handling
  - 10–20 hrs a week saved per employee
  - 65% less repetitive work
  - 500% boost in customer response
  - 4× faster reporting
- **"100+ professionals trained through corporate training sessions."** Hardik confirmed it; use it with
  that wording.

## 3. Goals

- **One site** at artificiaai.com, with pages for each offer plus pages for specific professions,
  industries and use cases.
- **A PHP admin area** where Hardik and the SEO expert log in. The first version lets the SEO
  expert do all of the following alone:
  - **Pages.** Build any page, Home included, from a **library of designed sections** (§6.4): add,
    remove and reorder sections, start from a template, or duplicate a page. Save a section line-up
    as a new template. Edit any page's title, meta description, web address (slug), headings, text,
    images (alt text required), FAQs and its social share image.
  - **Pages from a spreadsheet.** Create many pages at once by uploading a CSV (exported from
    Excel) against a master template. Each row has to fill the fields that make that page different
    from the others (§6.4).
  - **Blog.** Write and publish blog posts.
  - **Redirects.** Add, edit and remove them.
  - **Automatic files.** The sitemap.xml and robots.txt update themselves when pages change.
  - **Schema.** Structured data for Course, FAQPage, LocalBusiness/EducationalOrganization,
    Organization and Article. Each is generated from page content, with per-page overrides where it
    matters.
  - **Links.** An internal-link picker and a broken-link check.
  - **Tracking codes.** Paste in GA4, Search Console verification, the Meta pixel ID and a Meta
    Conversions API token, without editing code.
  - **Saved versions.** Every page keeps its versions, and any of them can be restored.
  - **SEO score check.** Each page gets a list of what's wrong and how to fix it (§6.5).
  - **Leads.** See the leads each page brought in.
- **Leads are saved first, then forwarded** (§6.6). This includes spam protection, Google and Meta lead
  tracking, an email alert, a CSV export, and forwarding to an n8n webhook URL set in the admin.
- **Fast on a phone.** Most traffic comes from Meta ads.
  - Pages are server-rendered HTML.
  - Images are automatically resized and converted to WebP on upload.
  - No decorative 3D libraries.
- **Great motion.** Animations, transitions and motion graphics on top of the Claude Design design. They
  stay off when the visitor's device asks for reduced motion.
- **No broken old links.** Every old URL sends visitors on to its new equivalent with a 301 redirect
  (§7).

## 4. Non-goals for the first version

- Popups, an AI writing helper, author profiles, and extra content types (news, glossary, white
  papers). Hardik will add to the SEO list after talking to the SEO expert.
- Batch dates, seat counters or any countdown.
- Prices, fee ranges or rupee values anywhere on the site.
- Payments, student logins or an LMS.
- **The new n8n lead workflow.** That's a separate project after this one. The site only POSTs to
  whatever webhook URL the admin holds.
- Creating the Google Business Profile. Hardik or the SEO expert will set up a new profile; the site
  only links to it and includes it in its schema.
- Hindi or Gujarati pages **(our call)**.
- Recruitment or "AI talent supply". It isn't a confirmed line of business.
- Moving hosting off Hostinger, or anything that needs Node.js.

## 5. Anti-goals: a failure even if everything technically works

- **Invented proof** of any kind:
  - logos of brands that never gave written permission (the 13-logo wall is gone for good)
  - testimonials beyond the six real ones
  - unsourced quotes (the Modi and Sam Altman quotes are gone)
  - unsourced statistics
  - "best", "#1" or "lowest price" claims
  - fake scarcity ("only 15 seats left")
  - a placement or internship promise (Artificia doesn't offer one)
- **A lead lost silently.** A visitor sees "thank you" when nothing was saved. The current corporate page
  does exactly this.
- **Near-duplicate audience pages** with only the profession or city swapped. Google treats these as
  doorway pages and can push the whole site down.
- **Content that only exists in JavaScript.** FAQs, testimonials and body text must be in the HTML the
  server sends.
- **Heavy pages.** The current landing page loads about 75 MB of images.
- **A deploy that wipes the server's** uploaded images, config file or database.
- **Any change to Orkay.** Orkay's code, folders, GoHighLevel accounts or live sites must not be touched.
  Its admin panel is a source of ideas, read-only.
- **The SEO expert has to call a developer** for anything on the v1 list.

## 6. How it gets built

### 6.1 Platform (decided)

- **Hosting:** Hostinger **Premium** web hosting (`hostinger_premium_v2`, order 1007615865). There's
  no Node.js on this plan, so there's no Next.js.
  - Account user: `u447231494`.
  - Site root: `/home/u447231494/domains/artificiaai.com/public_html`.
  - The server is LiteSpeed, so `.htaccess` rules work.
  - **The same account also hosts `popeyai.com` and `popeyai.io`**, as addon domains in their own
    `domains/<domain>/public_html` folders. Backups, deploys and the switchover touch only
    `domains/artificiaai.com/`, and the new database gets its own name and user.
- **Visitor pages:** HTML, CSS and JavaScript, rendered by PHP from the database.
- **Admin and leads:** PHP with MySQL on the same plan. The app connects to MySQL at `127.0.0.1:3306`
  or `localhost`. The external `srvNNNN.hstgr.io` host is not for use from the app.
- **Credentials:** they live in a config file on the server **outside the repo and outside the
  web-served folder**, and are never committed.
- **Changes flow GitHub → Hostinger.** The repo is `popeyaiartificia-png/artificiaai-website`
  (private).
  - **Never use the Hostinger plugin's static deploy.** It wipes the whole site root.
  - Uploaded images, the config file and the database must survive every deploy.
- **Design:** **Claude makes it**, using the installed design skills (`taste-skill`, `impeccable`,
  `frontend-design` and related ones), as the section library from §6.4 at phone and desktop widths.
  Animations, transitions and motion graphics are added on top.
  - **Hardik approves the design before any front-end build starts.**
  - It's made in Claude: a Design artifact, optionally kept in sync with a design-system project
    through `/design-sync`. Standalone claude.ai/design closes on **14 Dec 2026**, so nothing
    depends on it.
  - The brand anchors are lime `#AED534` on dark charcoal (`#2A2E2E` / `#08080d`) with white text,
    per `MASTER_CORPORATE_TRAINING_SPEC.md` §10.
- **Motion:** GSAP (free since 2025) plus CSS animations **(our call)**.
  - In v1, motion graphics are built in code: scroll-driven sequences, animated SVG diagrams of
    workflows and counters. There are no separately produced videos or Lottie files in v1 **(our
    call)**. Rendered clips (for example made with HyperFrames) can be added later. Any that are
    added load only when they scroll into view.
  - `prefers-reduced-motion` turns movement off.

### 6.2 Build, stage, then switch over (our call)

1. **Back up the live server first.** The two landing folders, the PHP form handlers
   (`contact-form.php`, `registration-form.php`) and `.htaccess` were never in this repo. Copy the
   whole current `public_html` before anything else.
2. **Build on a staging subdomain** of the same plan, password-protected and `noindex`, so the live site
   keeps running and keeps taking leads.
3. **Switch over** once Hardik approves the content: swap the site root and turn on the redirects
   (§7). Then submit the sitemap in Search Console.

**Two launch steps, with no fixed date (our call).** This gets the invented proof and the lead-losing
corporate page off the live site sooner.

- **Step 1** goes live with:
  - the design and all the core and seed pages
  - the section-based page editing, with its title, meta and image controls
  - the blog
  - the forms, with saving, spam checks, Google and Meta tracking, the email alert and n8n
    forwarding
  - the leads inbox and CSV export
  - the redirects, plus the automatic sitemap and robots.txt
  - schema and the tracking-code settings
- **Step 2** follows, without another switchover:
  - the spreadsheet upload
  - saved versions and restore
  - the SEO score check
  - the broken-link check
  - the internal-link picker
  - anything the SEO expert adds after the first conversation

### 6.3 Admin users (our call)

- **Admin (Hardik):** everything, including settings, webhook URL, tracking codes, users, lead export
  and lead deletion.
- **SEO editor:** all content and SEO tools, and read-only access to leads (to see which pages
  convert). Can't export or delete leads, or change the webhook or users.
- **Security:**
  - hashed passwords
  - rate-limited login
  - forgot-password by email
  - CSRF protection on every admin form
  - server-side validation of every upload (type and size)

### 6.4 Sections, templates, and audience pages without doorway-page risk

**Page model (decided: option b).**

- Every page is an ordered list of sections taken from a designed section library. Typical sections:
  - hero
  - use cases
  - tools
  - curriculum
  - FAQs
  - testimonials
  - case study
  - lead form
  - text with image
  - stats ("typical outcomes")
  - call to action
- The SEO editor can add, remove and reorder sections on any page and edit each section's settings,
  but can't design new section types. New section types are developer work.
- Saved versions store the whole section list.
- **A template is a saved section line-up.** The SEO editor can save any page's line-up as a new
  template, and marks which fields must be **unique per page**. For example: an intro for this
  audience, 3–5 use cases specific to them, the tools they'd use, 3–5 FAQs specific to them, and a
  relevant testimonial if one exists.
- The spreadsheet upload creates pages from one chosen template. Its columns map onto that template's
  fields, and image columns take a media-library file name or URL.
- A spreadsheet row that leaves a required unique field empty isn't published.
- The score check warns when two pages are mostly the same text.
- So the design is delivered as **a section library**, with each section designed at phone and
  desktop widths, not as fixed full-page mockups.

**Seed templates (our call):** masterclass by audience, corporate training by industry, and service or
use case. The third covers pages like "GoHighLevel for real-estate agencies" or "AI automation for CA
firms".

**Seed pages Artificia launches with (our call)**, so the templates are proven before the SEO
expert adds more:
  - masterclass pages for students, working professionals, and business owners and founders (the
    same options as the current form's "I am a…")
  - corporate training pages for manufacturing and for healthcare (Alphonsa's testimonial fits
    healthcare)
  - one service or use-case page: GoHighLevel setup for businesses, backed by the Orkay case study

### 6.5 SEO score check (per page)

Each check shows what to fix.

- Title length and uniqueness, and meta-description length and uniqueness, across the whole site.
- Exactly one H1.
- Every image has alt text.
- A minimum word count.
- At least N internal links in and out.
- Valid schema.
- A readable slug.
- No near-duplicate of another page.

### 6.6 Leads

**Form types (our call):**

- **Masterclass:** name, WhatsApp, email, I am a…
- **Corporate:** name, work email, WhatsApp, company, number of employees to train, current AI adoption,
  location, objectives, biggest challenge.
- **Services:** name, phone, email, company, service of interest, message.
- **Contact:** name, phone, email, message.

The SEO expert can place any of these four on any page. Adding new field types is out of scope for v1.

**Every form shows a consent line** that links to the rewritten privacy policy. This is DPDP: the rules
were notified on 13 Nov 2025, with hard enforcement from 13 May 2027.

**What happens on submit, in this order:**

1. **Check for spam.** Use a honeypot field, a minimum time-to-submit and a per-IP rate limit
   **(our call)**. Cloudflare Turnstile can be added later if spam gets through.
2. **Save the lead to MySQL**, recording:
   - the form type, the page URL and the referrer
   - the UTM parameters, `gclid`, `fbclid`, and Meta's `_fbp` / `_fbc` cookies
   - a timestamp and an `event_id`

   If the save fails, the visitor sees an error and a WhatsApp link. **Never show success
   without a saved row.**
3. **Tell Google and Meta.** Fire the browser events (GA4 `generate_lead` and Meta Pixel `Lead`, both
   carrying the `event_id`), and send the same event server-side through the **Meta Conversions
   API** from PHP, using the same `event_id` so Meta counts it once. Hardik called lead tracking
   "really important".
4. **Send an email alert** to an address set in the admin, so leads are seen even before the n8n
   workflow exists **(our call)**. Send it through Hostinger SMTP from an `@artificiaai.com`
   mailbox; the domain's MX and SPF already point at Hostinger.
5. **Forward to n8n.** POST the lead as JSON to the **single** n8n webhook URL in the admin. The
   payload carries `form_type`, so n8n does the routing.
   - If the POST fails, the lead stays saved and is marked "not forwarded", and the admin can retry.
   - The two existing webhooks (`fb-leads-to-crm`, `artificia-website-leads`) are **not** fed or
     matched. Hardik doesn't believe the old flow works, and a new workflow replaces it.
   - Launch does not wait for that workflow. Until it exists, the admin inbox and the email alert
     are how leads are seen.

**The admin leads inbox:**

- filters by form type, page, source and date
- a CSV export
- delete on request (data-subject requests)
- a "not forwarded" view

**Site-wide:** a floating WhatsApp button linking to `https://wa.me/919104488859`.

### 6.7 Content rules for every page

- Use only the sources in §8. No prices. Use "typical outcomes" wording for the five stats. Make no
  claims beyond the anti-goals list.
- Use "AI training institute in Vadodara" / "AI course in Vadodara" as search targets, not as "best"
  claims.
- **The Orkay case study** describes only what's verified as live in the Orkay knowledge base (§8):
  - two GoHighLevel accounts (India dealer acquisition over WhatsApp, and International export by
    email)
  - leads routed to the rep who speaks the lead's language
  - the Arya AI voice agent, which calls new leads in Hindi, books callbacks on the rep's calendar or
    transfers live
  - Meta Conversions API lead events
  - follow-up and reassignment automations
  - a daily status report
  - the website build

  Orkay has approved being named and the case study's use. Figures may come from the knowledge base,
  but only ones marked as live or verified there, never ones marked *inferred*.
- **Masterclass curriculum** comes from the CSV, not from the old landing page. The old page described a
  12-week, 6-phase course that doesn't exist. Current tool names only.
- **Rewrite the privacy and terms pages.** The old ones say `info@artificia.com`, which is the wrong
  domain. The real address is `info@artificiaai.com`.
- Claude drafts all copy from the sources. **Hardik approves before launch.**

## 7. Pages and old-URL redirects

**Core pages:**

- Home
- AI Masterclass (with 30-day and 60-day sections)
- Corporate AI Training
- Services, with a sub-page each for GoHighLevel, Website Development, Content and Marketing, and
  RAG and Custom Automation
- Case study: Orkay Tiles
- About (the company, Hardik Aseri, Vijay Linju Raghavan)
- Contact, with the map
- Blog
- Privacy
- Terms
- The audience pages from §6.4

**Redirects (301):**

| Old URL | Goes to |
|---|---|
| `/index.html` | `/` |
| `/about.html`, `/team.html` | About |
| `/contact.html`, `/registration.html` | Contact |
| `/trainingcourses.html`, `/landing-page/` (and anything under it) | AI Masterclass |
| `/co-orporate/` (and anything under it) | Corporate AI Training |
| `/projects.html` | Orkay case study |
| `/faqs.html` | AI Masterclass FAQ section |
| `/hiring.html`, `/services.html` | Home and Services respectively |
| `/privacypolicy.html`, `/Termsandcondition.html` | Privacy, Terms |

Meta ads currently point at `/landing-page/`. Those ads keep working through the redirect, but should
be repointed to the new URL after launch.

## 8. Sources: read-only, never edit

| For | Read |
|---|---|
| Masterclass format and curriculum | `ARTIFICIA/Artificia final course/CLAUDE.md`, `Artificia_AI_60_Day_Curriculum.csv` |
| Corporate training | `ARTIFICIA CO-OPERATE TRAINING/Artificia/corporate-training/MASTER_CORPORATE_TRAINING_SPEC.md`; `sales-kit/` one-pagers (LEAD, CORE, CHAMPIONS, SUSTAIN) and Company Introduction |
| Automation services | `.../use-cases/AUTOMATION_SERVICES_CATALOGUE.md`, `UNIVERSAL_AUTOMATION_SERVICES_SPEC.md` |
| Pricing **rules** (internal, never quoted) | `.../sales-kit/ARTIFICIA_ENGAGEMENT_LADDER_AND_PRICING_v1.md` |
| Orkay case study | `ARTIFICIA/ORKAY TILES GHL/knowledge/`; `ARTIFICIA/NEW ORKAY WEBSITE/` |
| Admin ideas (inspiration only) | `ARTIFICIA/NEW ORKAY WEBSITE/site/SEO-DESK-GUIDE.md` and its `app/admin/` screens |
| FAQ wording and testimonial assets | the live `/landing-page/` (back it up first, §6.2) |
| Contact facts | Artificia Intellec Pvt Ltd, Office No. 11, 2nd Floor, Darshanam Arise, Gotri-Sevasi Rd, opposite Osia Mall, Vadodara 391101 · +91 91044 88859 · info@artificiaai.com |

## 9. Left to the builder

- **PHP structure:** routing, templating, database schema, and whether to use a small framework or plain
  PHP. It must run on Hostinger shared hosting, with dependencies kept minimal.
- **Rendering:** render on request with caching, or write static files on publish. Either works, as
  long as visitors get complete HTML.
- **The rich-text or block editor** for the admin.
- **The exact deploy mechanism**, within §6.1.
- **Animation choreography**, following the design.

## 10. Order of work (decided)

1. Build the design and the pages **locally first**, so Hardik can see them on his machine.
2. Hardik reviews them. Tell him every time the design changes or new pages are built.
3. Then commit to GitHub and deploy to Hostinger (staging, then switchover, per §6.2).

The n8n lead workflow is built separately. Hardik imports its JSON into his own n8n container and
sends back the webhook URL, which goes into the admin settings (§6.6).
