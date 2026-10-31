# Product

<!-- impeccable:product-schema 1 -->

Source: the approved spec `docs/superpowers/specs/2026-10-27-artificiaai-site-design.md` (Hardik Aseri, 27 Oct 2026). Every fact below comes from it or from the sources it names.

## Platform

web

## Stack

Decided by Hardik: PHP 8 server-rendered pages, plain CSS and JavaScript, GSAP 3.13 self-hosted, MySQL, on Hostinger Premium shared hosting (no Node.js). No framework, no build step.

## Users

- **Individuals in and around Vadodara**: students, recent graduates, working professionals, founders and freelancers. Most arrive from Meta ads on a phone, often in the evening, and decide whether to enquire about an offline AI masterclass.
- **Business decision makers** (founders, HR and L&D heads, operations heads) at Gujarat companies such as manufacturers and hospitals. They search Google for corporate AI training and judge whether Artificia can train their teams on their own work.
- **Business owners who want systems built**: GoHighLevel setup, a website, content and marketing, or AI that answers from their own documents.
- **The SEO expert** runs the admin area alone (an Operate surface, separate from these visitor pages).

## Product Purpose

One website, artificiaai.com, that tells the truth about Artificia AI's 2026 business and turns visitors into saved enquiries. Success: every enquiry is saved and counted in Google and Meta, and the SEO expert can grow the site without a developer.

## Positioning

Artificia teaches AI in person in Vadodara, one hour a day, and the same team builds and runs AI systems for real businesses (Orkay Tiles International's GoHighLevel accounts and website). Corporate programs are built around the client's own documents and departments, and each session ends in a working deliverable.

## Operating Context

- **AI Masterclass:** offline and live in Vadodara, 1 hour a day. A 30-day Foundation track and a 60-day Full program (Foundation plus agents, MCP, RAG, voice agents, deployment, Claude Code, local LLMs). No coding needed. Curriculum source: `Artificia_AI_60_Day_Curriculum.csv`.
- **Corporate AI training:** custom length and format, on-site or online over Microsoft Teams and similar tools. Next step: a free 2-hour pilot session.
- **Services:** all six blueprint families confirmed available by the user on 30 Oct 2026: AI Automation, Agentic AI, Software & Apps, Marketing Automation, SEO/AEO/GEO, and Cloud & Private AI. Includes GoHighLevel, websites and RAG knowledge assistants. Next step: a free 15-minute flow audit. Availability confirmation does not establish client results or security certifications.
- WhatsApp is the visitor's default contact channel (number 919104488859).

## Capabilities and Constraints

- Four enquiry forms (masterclass, corporate, services, contact); a lead is never shown "thank you" unless it was saved.
- Pages are built from a library of 20 designed section types that the SEO expert arranges; the design must hold up for any order of sections and any length of real copy.
- Fast on a mid-range phone: server-rendered HTML, CSS + JS under 150 KB gzipped, hero image under 200 KB, Lighthouse mobile Performance 90+ and Accessibility 95+.
- Motion is code-driven (GSAP, CSS, SVG); `prefers-reduced-motion` turns it off. No 3D or decorative WebGL.
- Never on the site: prices or rupee values, batch dates, seat counters, countdowns, "best", "#1", "lowest", placement or internship promises, a fixed duration for corporate training.

## Brand Commitments

- Name: **Artificia AI** (company Artificia Intellec Pvt Ltd). **Always the real logo, never a text wordmark** (Hardik, 27 Oct 2026). The current lockup (tagline "AI-Driven, Future-Focused.", supplied 29 Oct 2026 as a 4884 px square on black, `public/logo.png`) is used as `public/assets/img/artificia-logo.png`: cropped and keyed to transparency at 492×138 (3× its 34–42 px display height), top-left, at least 120 px wide, never redrawn. Its figure is #AED534. A vector version would still be sharper.
- Brand Bible (`ARTIFICIA STUDY MATERIAL/CLAUDE.md`): Roboto is the shared web typeface. User-requested exception, 30 Oct 2026: the large homepage statement uses Mona Sans plus a Bodoni Moda serif substitute to match the reference typography; exact Brier use requires a licensed font.
- Brand anchors: lime `#AED534` on dark charcoal (`#2A2E2E` / `#08080d`) with white text (`MASTER_CORPORATE_TRAINING_SPEC.md` §10).
- Voice: plain and factual; claims are sourced or not made.

## Evidence on Hand

- **Orkay Tiles International**, named client with permission: Artificia set up and runs their GoHighLevel accounts and built their website.
- **Six real testimonials:** videos from Hatim Sorangwala, Khushi Dodiya and Dev Chokshi (Google Drive links in `legacy/landing-page/index.html`); written quotes from Alphonsa N. P (Chief Nursing Administrator, Aadicura Super Speciality Hospital), Haritha Nair (AI design student) and Nilesh Chaudasama (working professional). Photos in `legacy/landing-page/images/testimonials/`.
- **Five typical outcomes for AI-enabled teams** (never presented as Artificia's results): 92% faster lead handling, 10–20 hrs a week saved per employee, 65% less repetitive work, 500% boost in customer response, 4× faster reporting.
- **"100+ professionals trained through corporate training sessions."**
- People: Hardik Aseri (instructor, `legacy/landing-page/images/hardik.jpg.jpeg`) and Vijay Linju Raghavan (`legacy/assets/images/all_images/about/vijay.jpg`).
- **Absent, never to be invented:** other client logos or case studies, prices, batch dates, placement records, classroom photography (none supplied yet).

## Product Principles

1. Every claim on the page can be traced to a source; when in doubt, leave it out.
2. Every enquiry is kept: saving beats every other step.
3. A phone on a mobile network is the main screen.
4. One site, many doors: each audience page must earn its place with content of its own.
5. In-person and practitioner-led is the difference; show it rather than say it.

## Accessibility & Inclusion

WCAG 2.2 AA contrast and keyboard access; reduced motion respected; readable for visitors whose first language is Gujarati or Hindi (plain English, short sentences).
