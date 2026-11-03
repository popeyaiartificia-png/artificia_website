# Artificia AI: Markdown and project overview

Reviewed: 30 October 2026.

This is an engineering orientation to the workspace documentation. It summarises the documented project, highlights conflicts, and proposes the next work for you to assess as the engineer. Features marked as built are reported by the handover; this review does not certify their runtime behaviour or deployment readiness.

## 1. What this project is

Artificia AI is rebuilding artificiaai.com into one business website with a content-management admin and reliable enquiry capture. It replaces an old company site and two separate advertising funnels.

The current business priority is:

1. AI automation, business software and marketing automation.
2. Corporate AI training built around the customer's work.
3. Offline AI courses in Vadodara.

The main visitor action is to book a free AI consultation. WhatsApp is another contact route. The SEO editor should be able to create and maintain content without a developer, using reusable sections and templates.

**This is an existing implementation to complete and review.** The immediate documented task is finishing the homepage redesign, rather than starting the application architecture again.

## 2. What each Markdown file contains

Ten existing Markdown files were found, including one hidden design brief.

| File | Contents | How to use it |
|---|---|---|
| [CONTEXT.md](CONTEXT.md) | Business, latest decisions, architecture, design history, completed work, open questions, next steps and changelog. | Read first; strongest summary of current direction. |
| [PROGRESS.md](PROGRESS.md) | Daily Done / Current lead / Next / Blocked notes, including recent visual checks and reported test limitations. | Read the latest entry for the immediate handover. |
| [README.md](README.md) | Short repository introduction, folders, external configuration and local PHP/MySQL setup. | Setup starting point; commands need adapting to this Windows workspace. |
| [PRODUCT.md](PRODUCT.md) | Audiences, purpose, offers, constraints, performance targets, brand rules and evidence. | Product background; several facts have been superseded. |
| [DESIGN.md](DESIGN.md) | Design tokens, typography, layouts, components, motion and admin UI; newer stage-homepage additions. | Consult the current homepage additions alongside CONTEXT.md; much of the file describes the earlier paper design. |
| [CLAUDE.md](CLAUDE.md) | Instructions to read the handover and maintain the two checkpoint documents after changes. | Working convention, not a feature specification. |
| [content/SOURCES.md](content/SOURCES.md) | Image provenance, illustration labels, testimonial corrections, tool-logo sources and library/font licences. | Check when selecting or adding assets and proof. |
| [Original approved specification](docs/superpowers/specs/2026-10-27-artificiaai-site-design.md) | Original goals, exclusions, roles, sections, leads, SEO, redirects, sources and launch sequence. | Baseline functional scope; later documented decisions override older details. |
| [Step 1 implementation plan](docs/superpowers/plans/2026-10-27-artificiaai-site-step1.md) | Thirteen tasks covering foundation, rendering, design, admin, media, editor, redirects, leads, seeds, review, staging and switchover. Includes interfaces and test cases. | Implementation reference; unchecked boxes are not a reliable measure of current completion. |
| [Hidden visitor-surface brief](.impeccable/surfaces/app-views-layout-php.md) | Original dark “Build Notebook” visual concept and visitor-page constraints. | Historical design context; its visual direction is superseded. |

The handover also references a management blueprint at `docs/ref/Artificia_Website_Revamp_UIUX_Development_Blueprint.pdf`. The Markdown names it as the authority for content and order, subject to later user decisions. This overview summarises those Markdown references; it does not independently review the PDF or the external business source folders.

## 3. Architecture and engineering constraints

| Area | Documented approach |
|---|---|
| Runtime | Plain PHP 8; the implementation plan specifies PHP 8.2+ and required extensions. |
| Database | MySQL/MariaDB through PDO; migrations run on application boot. |
| Frontend | Server-rendered HTML, plain CSS/JavaScript, self-hosted GSAP and ScrollTrigger. |
| Admin editor | Forms generated from a section registry; Quill for rich text. |
| Content model | A page stores an ordered JSON list of sections; templates save reusable section arrangements. |
| Hosting | Hostinger shared hosting with LiteSpeed; no Node runtime required. |
| Dependencies | No framework, Composer or frontend build step. |
| Configuration | Secrets outside the repository and web-served directory. |
| Deployment | Preserve database, configuration and uploads; staging before production. |

Main folders:

- `public/`: web entry point, assets and uploads.
- `app/lib/`: routing, content, authentication, SEO, media and lead logic.
- `app/sections/`: reusable visitor sections and their registry.
- `app/views/`: shared layout, hero and drawing partials.
- `app/admin/`: content, media, users, settings, redirects and lead screens.
- `content/seed/`: initial pages, templates, redirects and media.
- `db/migrations/`: schema changes.
- `tests/`: custom PHP tests.
- `legacy/`: old website reference.

The current handover describes 20 section types. The section library has 21 PHP files in `app/sections/`, including `registry.php`, and 19 page JSON files under `content/seed/pages/`. File presence does not prove feature correctness.

## 4. What is documented as built

### Public website

- Seed pages for services, corporate training, masterclass audiences, About, Contact, Blog, legal pages and the Orkay case study.
- Reusable sections, page templates, redirects, sitemap, robots and structured data.
- New homepage loader, robot stage hero, statement, seven-step workflow, Before AI / With AI comparison, Problem → outcome and Solutions gallery.

### Admin and leads

- Authentication, password reset, users and role permissions.
- Page/post editing, duplication, templates and saved revision snapshots.
- Media upload, resizing and WebP conversion.
- Per-page SEO checks, redirects and settings.
- Four enquiry types: masterclass, corporate, services and contact.
- Lead inbox, spam checks, attribution, email alerts, Meta/Google tracking, n8n forwarding, retry and admin-only export/deletion.

The critical lead contract is: **save the enquiry before reporting success**. Forwarding failures must leave the saved lead available for retry. The downstream n8n workflow is a separate project.

## 5. Current design and unfinished work

The homepage follows a landonorris.com-inspired direction: large sections, black-to-olive-to-light surfaces, Roboto type, neon emphasis, scroll transitions and a robot hero. Other pages still use the earlier warm-paper design.

Current rebuilt sequence:

`Hero → Statement → How we work → Before AI / With AI → Solutions` (Problem → outcome was removed at the user's request after this review, 30 Oct 2026)

The documented next sections are:

1. Free consultation / automation audit offer.
2. Corporate training band.
3. Case studies and clearly labelled demos.
4. Courses preview.
5. Why Artificia.
6. Resources / insights preview, once real content exists.

Older homepage sections remain below the rebuilt sequence and need replacement or removal as the redesign proceeds. The trust strip is deliberately omitted pending verified proof.

Other remaining work includes navigation, migration of inner pages, route decisions, mobile performance and accessibility checks, staging and launch verification. Later admin work includes CSV bulk page creation, revision restore UI, broken-link checking and an internal-link picker.

## 6. Conflicts and documentation gaps

| Topic | Conflict or gap | Working interpretation |
|---|---|---|
| Business positioning | Older files lead with training; the latest handover leads with automation and software. | Use the latest technology-first direction. |
| Visual direction | Hidden brief uses Build Notebook; much of DESIGN.md uses Highlighted Textbook; the current homepage uses the stage design. | Apply the stage direction to new homepage work; retain awareness of inner-page migration. |
| Section count | Older files said 17/19; the current registry and handover have 20. | Use the registry as the implementation authority. |
| People | Older product/spec/plan files include Hardik publicly. | Latest handover says Vijay is the only person on About and Hardik was removed publicly. |
| Testimonials | Older files say six and include Khushi. | SOURCES.md and CONTEXT.md record her removal because the video is unavailable; two other cards use initials instead of stock portraits. |
| Orkay capabilities | Original spec mentions callback booking, transfers and reassignment. | Latest handover limits claims to calls without booking/transfer and follow-up without reassignment. |
| WebGL | PRODUCT.md prohibits decorative WebGL; newer design documentation describes an implemented WebGL hero. | Record the current hero as an exception requiring performance validation and documentation alignment. |
| Metrics | Original files permit specified typical-outcome figures; the latest handover flags stricter blueprint evidence requirements. | Resolve evidence and placement before adding metrics to the homepage. |
| Local setup | README describes macOS/Homebrew and port 8000; the current handover says site preview is on 8080. | Verify the actual Windows PHP/MySQL setup and preview command. |
| SEO checks | The old plan puts these in Step 2; the latest handover says they are built. | Audit the implementation rather than inferring status from the old checklist. |
| Testing | PROGRESS.md reports 149 passes and 24 failures attributed to environment limitations. | Historical result only; reproduce and classify before release. |
| Deployment | No staging deployment is recorded in the handover. | Treat release readiness as unverified. |

`git status` confirms substantial modified and untracked work. The latest local commit is `90bf456` (“Final fix wave before staging…”). This review does not establish remote repository or live-server state.

## 7. Decisions for your engineering review

- Service availability resolved on 30 Oct: the user confirmed all six blueprint categories are available, including Agentic AI, SEO/AEO/GEO, and Cloud & Private AI. Individual case-study results still need evidence.
- What exactly does the consultation/audit include, and how does it relate to the corporate pilot?
- Keep `/services`, `/ai-masterclass` and `/corporate-ai-training`, or adopt the blueprint routes with redirects?
- Which course names and additional courses are approved?
- Which accent token should be standardised, and which tool logos should stay?
- Review the comparison section's new transparent middle-aged portrait matching the user's reference (replaced 30 Oct); no Canva download is needed for this asset.
- Keep the student portal, LMS and payments for a separate phase? The original v1 excludes them; the newer blueprint raises them.

## 8. Recommended next work

These are recommendations for your assessment, not additional approved scope.

1. **Establish a reproducible baseline.** Review existing changes, document the Windows start procedure, reproduce tests and save a local checkpoint of the current work without pushing or deploying.
2. **Align the requirements.** Resolve service availability, offers and route choices; then correct contradictory product/design documentation without deleting useful history.
3. **Build the free consultation offer next.** The Solutions overview is now implemented (30 Oct) after Problem → outcome as a dark gallery of all six user-confirmed blueprint service families, editable in the CMS and checked on desktop/mobile, keyboard, reduced motion and without JavaScript. Define the consultation's scope and limits before building its featured offer band.
4. **Finish the homepage one section at a time.** Replace the remaining older sections and update navigation, then migrate service pages, corporate training and course pages.
5. **Verify release behaviour.** Check form persistence and failure states, tracking deduplication, forwarding retry, permissions, uploads, redirects, mobile performance, keyboard use and contrast.
6. **Review staging before switchover.** Preserve persistent data, verify deployment rules and obtain the project's required review before production changes.

The main engineering risks to investigate are documentation drift, a homepage that currently mixes two designs, unreproduced environment-dependent checks, and the mobile cost of the animated hero.
