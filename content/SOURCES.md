# Where the shipped files come from

## Images

| File | Source |
|---|---|
| `public/assets/img/showcase/orkay-desktop.webp`, `orkay-mobile.webp` | Real viewport screenshots of https://www.orkaytiles.com/, captured 31 Oct 2026 at the user's request (1440x960 desktop / 390x844 mobile). Necessary-only cookie choice dismissed the consent overlay before capture. Encoded as WebP; no generated or reconstructed website content. Orkay is the named client already approved in the case study. |
| `public/assets/img/showcase/contours.svg` | Original periodic contour field generated in code for the gallery background. Static SVG retained as the fallback for the gallery's animated canvas and the homepage Solutions contour background. |
| `public/assets/img/artificia-logo.png` | Artificia's own logo, `ARTIFICIA STUDY MATERIAL/artificia-brand-docs/assets/logo.png` (Brand Bible: use as supplied). |
| `public/assets/img/favicon.png` | The old site's favicon, `legacy/landing-page/images/favicon.jpg.jpeg`, resized to 64 px. |
| `content/seed/media/vijay-linju-raghavan.jpg` | `legacy/assets/images/all_images/about/vijay.jpg`. |
| `content/seed/media/vijay-linju-raghavan-camera.jpg` | `legacy/assets/images/all_images/about/vijay_top.jpg` (the old About page's founder-with-camera photo), copied unchanged. Only 275×255, so it is shown small. |
| `content/seed/media/alphonsa-n-p.jpg` | `legacy/landing-page/images/testimonials/`, resized. The old page's photos labelled Haritha Nair and Nilesh Chaudasama are byte-identical to the template's stock team photos and remain excluded. Their cards now use explicitly labelled generated illustrative portraits (13 Nov), not reviewer identity photos. |
| `content/seed/media/hatim-sorangwala.jpg`, `dev-chokshi.jpg` | Google Drive thumbnails of their own testimonial videos (the Drive links in `legacy/landing-page/index.html`). Khushi Dodiya's video link no longer opens, so her testimonial was removed (28 Oct 2026). |
| `content/seed/media/orkay-website-hero.webp` | Byte copy of `public/assets/img/showcase/orkay-desktop.webp` (the live orkaytiles.com hero with Orkay's brand ambassador, 31 Oct 2026 capture above), added 4 Nov 2026 at the user's request for the Solutions page case study. |
| `content/seed/media/orkay-website.jpg` | Screenshot of the live orkaytiles.com (the "Morbi-made" section, no people), 27 Oct 2026. |
| `content/seed/media/artificia-hero.jpg` | The old landing page's hero, `legacy/landing-page/images/cover-hero.jpg`, with the old baked-in logo removed, mirrored and resized to 2400 px. |
| `content/seed/media/classroom-instructor.jpg`, `corporate-session.jpg`, `business-owner-desk.jpg`, `professional-office.jpg` | Illustrations from the old corporate page, `legacy/co-orporate/`. Used as illustrations, never as proof of a real class or client. |
| `content/seed/media/audience-*.jpg`, `learner-lime.jpg` | Illustrations from the old landing page, `legacy/landing-page/images/`. |
| `content/seed/media/industry-*.jpg` | Industry photos from the old corporate page, `legacy/co-orporate/ind_*.jpg`. |
| `public/assets/img/versus/person-profile-mature.webp`, `person-profile-mature-small.webp` | Generated 30 Oct 2026 with the built-in image-generation tool, using the original `person.webp` as the appearance reference after the user requested a similar middle-aged man. Preserves the mature face, short swept-back hair, salt-and-pepper beard, right-facing pose and navy textured shirt, with a clean transparent background. Original PNG retained in the generation tool's output directory; resized/re-encoded only, alpha preserved: 800×1067 (123,730 bytes) / 420×560 (39,488 bytes). Served responsively and lazy-loaded. Decorative illustration, not proof of a real team member or customer. |
| `public/assets/img/versus/person-profile.webp`, `person-profile-small.webp` | Generated with the built-in image-generation tool on 30 Oct 2026, at the user's request to replace the faulty cutout: an illustrative Indian professional in right-facing profile (superseded by the mature portrait below the user's latest reference), with a genuine transparent background. Original PNG retained under the generation tool's output directory. Resized/re-encoded only, preserving alpha: 800×1067 (94,122 bytes) and 420×560 (28,558 bytes), served responsively and lazy-loaded. Not a client, employee or testimonial subject. |
| `public/assets/img/versus/robot.webp` | Profile from `content/seed/media/artificia-hero.jpg`. Head and neck cut out with rembg; on 30 Oct 2026 the lower body (shoulder, chest and joint, which rembg had left see-through) was re-cut from the same photo with a hand trace snapped by OpenCV GrabCut. 883×1200, 160,364 bytes. The source photo ends at the shoulder, so no lower torso exists in this image. Illustration. The former `person.webp` desk-photo cutout is retained as an unused older asset; the built-in person figure now points to `person-profile-mature.webp`. |
| `public/assets/img/footer/robot-neon-plain.webp`, `robot-neon-plain-640.webp` | Edited 2 Nov 2026 with built-in image_gen at the user's request to remove the suit design. Uses `storage/footer-figure/robot-neon-generated.png` as the edit target; jacket is plain charcoal fabric, retaining the robot, neon satin tie, white shirt, pose, reflections and transparent background. Source: `storage/footer-figure/robot-neon-plain-generated.png`; framed PNG: `robot-neon-plain-cutout.png`. Same resize/framing transform and 1220x1150 canvas; alpha preserved. Desktop WebP: 106,060 bytes; mobile 640x603: 34,318 bytes. Exact edit prompt: `docs/ref/footer-robot-plain-prompt.txt`. Decorative robot illustration. |
| `public/assets/img/footer/robot-neon.webp`, `robot-neon-640.webp` (superseded, unused) | Generated 2 Nov 2026 with the built-in image_gen tool, at the user's request to replace the assembled footer robot with one coherent transparent cutout. References: the previous Canva `storage/footer-figure/robot-suit.png` and the existing hero robot. Neon satin tie, lime reflections/rim lighting and contour-patterned suit are intrinsic to the generated image. Original RGBA PNG retained at `storage/footer-figure/robot-neon-generated.png`; framed transparent PNG at `robot-neon-cutout.png`. Uniformly resized/framed onto the existing 1220x1150 canvas and encoded as WebP, preserving alpha and generated colors; no tie recoloring or pattern overlay. Desktop 1220x1150: 166,812 bytes; mobile 640x603: 42,110 bytes. Original prompt and processing notes: `docs/ref/footer-robot-neon-prompt.txt`. Later replaced by the plain-suit edit above. |
| `public/assets/img/footer/robot-body*.webp`, `robot-head*.webp`, `robot-suit-mask.webp` (superseded, unused) | Generated 2 Nov 2026 with Canva's image generator (media `MAHZvjDh3E8`), using `public/assets/img/hero/robot.webp` as the reference, so the footer figure is the same robot in a suit, after the logo's own character. Exported through a helper Canva design (`DAHZvmgFp24`, page 2). Cut out locally with rembg plus a fitted model of the backdrop. The neon tie, neon rim light, olive jacket grade, separate head layer and jacket mask were made in code. Source PNG and scripts: `storage/footer-figure/` (local, not committed). Replaced the same day by the unified generated cutout above, at the user's request. |
| `content/seed/media/ai-*.jpg` | Artificia's corporate-training brand illustrations, `ARTIFICIA CO-OPERATE TRAINING/assets/brand/`. |

Alt text for every seed image lives in `content/seed/media/alt.json`.

The lime sketches in `app/views/drawings/` are drawn in code for this site; no rasters.

The Solutions illustrations in `app/views/solution-visual.php` are original inline SVG drawings for this site (30 Oct 2026): connected workflow, agent/tool review loop, software interfaces, CRM/follow-up, search visibility, cloud/private deployment and document knowledge. Six appear in the homepage gallery; document knowledge remains an additional CMS illustration option. They illustrate capabilities rather than client work. The gallery composition takes direction from the Helmets section of [landonorris.com](https://landonorris.com/); no helmet images, proprietary typefaces or site code were copied.

## Tool logos

`app/views/brands.php` holds each tool's official mark as single-colour SVG path data:

- [Simple Icons](https://simpleicons.org) 15.22.0 (CC0): ChatGPT (OpenAI), Claude, Gemini, Perplexity, NotebookLM, ElevenLabs, Canva, n8n, Supabase, WhatsApp, Meta, Google Sheets, Google Drive, Google Analytics, Google Search Console, Google Cloud, Next.js, PHP.
- [Lobe Icons](https://github.com/lobehub/lobe-icons) 1.101.0 (MIT): Copilot, Claude Code, Runway, Lovable, Antigravity, AWS.
- The brand's own website, 28 Oct 2026: Pinecone and Gamma (the mark from the header logo on pinecone.io and gamma.app), GoHighLevel (the three-arrow mark from gohighlevel.com's header logo), HeyGen (traced from heygen.com's logo image).

The marks belong to their owners and are shown only to name the tools taught or used.

## Fonts, icons and code

- Public reading/UI font (2 Nov user request): `public/assets/fonts/artificia-sans-variable.woff2`, a renamed derivative of Mona Sans from the same official Google Fonts source used for the statement (https://github.com/google/fonts/tree/main/ofl/monasans). Local source `MonaSans[wdth,wght].ttf` SHA-256 `fd6e79634b5ae804a45aac7e2e3c2a325b41291fba59034f4732b0135b8475b3`. Width pinned to 93, weights 200-900 retained with default 400; Latin and supported punctuation/symbols subset, 561 glyphs / 365 Unicode characters, 68,604 bytes. Renamed Artificia Sans; copyright, OFL and attribution name records retained. Licence: `public/assets/fonts/monasans-OFL.txt`. Built locally with FontTools; build/provenance in ignored `storage/prepare-site-font.py` and `storage/artificia-sans-variable-provenance.json`. The existing fixed display face below remains unchanged to preserve exact statement metrics.
- Statement sans: Mona Sans from the official Google Fonts source (https://github.com/google/fonts/tree/main/ofl/monasans), SIL Open Font License 1.1. Pinned to weight 660 / width 93, Latin subset, renamed Artificia Statement Sans to respect the reserved name. `statement-sans.woff2`: 17,896 bytes; upstream licence in `public/assets/fonts/monasans-OFL.txt`. Copyright retained in the font.
- Statement serif: Bodoni Moda from the official Google Fonts source (https://github.com/google/fonts/tree/main/ofl/bodonimoda), SIL Open Font License 1.1. Pinned to weight 500 / optical size 72, Latin subset, renamed Artificia Statement Serif. `statement-serif.woff2`: 13,036 bytes; licence in `public/assets/fonts/bodonimoda-OFL.txt`. Close substitute for commercial Brier Bold; Brier was not copied or installed. An exact match requires a licensed Brier font.

- Legacy Roboto assets remain under `public/assets/fonts/` (Google Fonts, SIL Open Font License 1.1); admin uses Roboto Flex. Public page CSS/preloads now use the statement's Mona Sans/Bodoni system instead of Roboto.
- Phosphor Icons, regular weight, MIT licence (`app/views/icons.php`).
- GSAP 3.13.0 and ScrollTrigger, GreenSock standard "no charge" licence (`public/assets/vendor/`).
- Quill 2.0.3 rich-text editor (admin page editor only): `dist/quill.js` and `dist/quill.snow.css` from the npm package `quill@2.0.3` (sha512 `xEYQBqfYx/sfb33VJiKnSJp8ehloavImQ2A6564GAbqG55PGw1dAWUn1MUbQB62t0azawUS2CZZhWCjO8gRvTw==`), unchanged, in `public/assets/vendor/quill/`. BSD 3-Clause licence (the package's own `LICENSE`, copied alongside), © Slab, Jason Chen and salesforce.com.

### Work gallery visuals (31 Oct 2026)

The unbranded service panels are original HTML/CSS illustrations in `app/views/work-visual.php`. The homepage now uses two service-offering panels covering AI automation, Agentic AI, marketing automation, software/apps, cloud/private AI, SEO/AEO/GEO and corporate AI training, plus a separate AI Voice Agents waveform. They replace the generic chat and document-search examples and describe capabilities rather than named customer deployments. The workflow illustration remains available in the section library. The public website preview, GoHighLevel panel and IT Support Team panel identify Orkay as separately documented below. The gallery borrows its open composition and desktop horizontal scroll behavior from the reference https://landonorris.com/; no reference photographs, logo or signature were copied.

### Orkay GoHighLevel gallery panel (31 Oct 2026)

The horizontal GoHighLevel panel in `app/views/work-visual.php` is an original interface illustration, not a screenshot of a private CRM account. It has no contact records, customer messages or invented performance metrics. Setup and management are already documented in the Orkay case study; ongoing support was explicitly confirmed by the user on 31 Oct. It replaces the mobile website capture on the homepage; the desktop website preview remains.

- The gallery's Orkay IT Support Team illustration (31 Oct) is an original inline SVG monitor, headset and helpdesk graphic in `app/views/work-visual.php`. It replaces the workflow diagram on that card and contains no real support-ticket or customer data.

### Gallery reference effects (31 Oct 2026)

Measured https://landonorris.com/ directly in the browser: dark-to-light background colors #282C20 / #363B25 to #F4F4ED / #D4D7CA, power1.out color progression, horizontal scrub 1, four-rem photo parallax and .6s reveal/.6s retract with a .3s offset for lime caption wipes. The reference's two-stage noise/outline rendering was studied; the shipped single-pass contour shader is original code using this site's existing noise utility, with capped rendering and static fallback. No reference photo, font, logo, signature or proprietary runtime was added. Existing Orkay content remains unchanged.

- Gallery seam refinement (31 Oct): its entrance reuses the site's original contour equation, page anchor, live frame time and alpha compositing before morphing over 420px into the broad field. The existing SVG is shared in fixed viewport coordinates across the three adjacent olive sections for static fallbacks. No new assets or third-party code.

### Testimonial fan (31 Oct 2026)

**13 Nov 2026 update:** At the user's request, the two outer written-review cards now show natural-looking generated illustrative portraits instead of initials. `content/seed/media/illustrative-woman-portrait-v1.webp` and `illustrative-man-portrait-v1.webp` were generated separately with the built-in image_gen tool, then resized/re-encoded at 960x1280; source PNGs remain in ignored storage as `testimonial-illustrative-{woman,man}-v1-source.png`. Seed assets are 123,156 and 120,252 bytes; the standard media pipeline creates 480/960px WebP renditions with lazy loading and srcset. Full prompts are in `docs/assets/testimonial-illustrative-portraits.json`. These depict fictional people, not Haritha or Nilesh; badge, section hint, alt text, accessible labels and dialog/no-JS notes disclose this. Quotes and real Hatim/Dev/Alphonsa photos are unchanged. This supersedes the initials state described in the historical 2 Nov correction below.

The homepage testimonial layout takes its overlapping portrait-card composition, rounded corners, paper-white #F4F4ED ground and two-line sans/serif heading treatment from the social section of https://landonorris.com/. The implementation is original PHP/CSS/JS and uses the existing licensed Mona Sans/Bodoni Moda subsets; no reference portraits, proprietary fonts or runtime were copied.

The video links for Hatim Sorangwala (Drive file 103bVzroX5C0fOxDYioCAzEwGQKQQfOwj) and Dev Chokshi (1c5KXs4l68QmhFu3rUaCY7r6I3NWdkklw), plus their existing seed portraits, come from legacy/landing-page/index.html. Connected Drive metadata confirms hatim.mp4 and dev.mp4; the Hatim embedded preview loaded its Play control in browser QA. The old Khushi Dodiya file 1pSwvcA3zmFFytjbKGpuf6MniX58VhVF2 returned 404 through the connected account and is not included in the new homepage fan. No raw video files were found in this project checkout. Players load only after a card is clicked, with a direct-link fallback.

Written reviews are the existing sourced quotations from Alphonsa N. P, Haritha Nair and Nilesh Chaudasama, unchanged. Alphonsa uses the existing seed portrait. **Correction before publication, 2 Nov:** SHA-256 comparison confirms the legacy Haritha/Nilesh portraits are byte-identical to stock team photos (`legacy/assets/images/team_image1.jpg` and `team_image2.jpg`). They are unsuitable as reviewer identities. Both seed/sample photo references and alt-text entries have been removed; the cards use initials and quote excerpts, with full reviews in the dialog/no-JS section. The resized stock files are retained only in ignored local `storage/testimonial-stock-reference/` and are excluded from GitHub.


### AI Masterclass course page (2 Nov 2026)

Curriculum source: user-supplied `D:/purnima/finalcourse.xlsx`, sheet `60-Day Curriculum`, A1:D61 (header and 60 days). Grouped into 12 modules without changing day order or the 30-day boundary; daily titles lightly normalized for capitalization/encoding. Module descriptions summarize source activities. The original 16 featured tools were expanded on 3 Nov to the complete named catalog described below. Product features and individual model versions are folded into their parent tool; no version-tier claims are made. User explicitly confirmed 30 days = ₹20,000 and 60 days = ₹40,000. These supersede the older CSV for the main course page and the prior prohibition on these two public fees.

Hero graphic is first-party HTML/CSS using the existing sourced tool marks and Phosphor icons. No new external image/font/library assets. Syllabus reading used read-only extraction; the supplied workbook is unchanged and is not copied into the public directory.

### Course hero project studio revision (2 Nov 2026)

The presentation, website and automation visual previews were first-party HTML/CSS compositions, subsequently replaced by the learning ribbon on 3 Nov. Existing sourced Phosphor icons and tool marks were reused. The syllabus supplied the project themes and day ranges. No new bitmap, font or library assets were introduced. Local-intent heading, server-rendered course facts and visible semantic breadcrumbs follow Google Search Central guidance: https://developers.google.com/search/docs/essentials and https://developers.google.com/search/docs/crawling-indexing/javascript/javascript-seo-basics. BreadcrumbList uses the visible Home / AI Masterclass hierarchy; no rich-result or ranking promise is made.

### Course learning ribbon (3 Nov 2026)

`app/views/course-learning-sculpture.php` is a first-party, server-rendered SVG composition: a continuous cubic ribbon from scattered question/prompt sheets to a website/chart and connected automation/check. The two illustrated endpoints describe the course's practical learning direction; they are not client results or testimonials. Geometry, gradients and icons are authored directly in code; no external art or new dependencies. Per the user's later 3 Nov request, a single 1.8-second sweep replaces the day-by-day journey, with Replay and static reduced-motion/no-JS output. The source-backed curriculum below gives a module overview (the day-by-day lesson list was removed at the user's request on 4 Nov), and fees come from existing plan data.

The added path motion (3 Nov) is also first-party SVG/CSS/JS: a clipped sheen and light trail, three sparks, rim glow, arrival ring and check draw. It introduces no media assets, libraries or external services. Pause/Play, offscreen/background stopping and reduced-motion/static fallbacks control these decorative effects; endpoint artwork remains opaque.

### Complete course toolkit (3 Nov 2026)

The symbols-and-names catalog immediately below the hero contains all 222 normalized products, platforms, libraries and model families named in the workbook's Tools column or activities. It includes the source's advanced options, alternatives and capstone stack examples; it is not a promise of separate hands-on mastery of every product. Concepts such as RAG, generic APIs and CRUD are not separate tool entries. Day-level evidence and asset provenance are recorded in [course-toolkit-sources.json](course-toolkit-sources.json).

The catalog is presented as two horizontal rows at the user's request. Each of the 222 tools is server rendered once in the accessible lists; inert, aria-hidden copies supply the visual loop. Copied SVG IDs are namespaced. The existing marks, source names and attribution remain unchanged; no new assets or dependencies were introduced for the moving layout.

142 displayed names use legitimate existing/new brand marks, including documented parent identities; 80 use neutral Phosphor functional symbols where a precise mark or identity is unavailable. In particular, Emily, Jerry and Atlas retain their supplied names without an invented vendor logo. The 101 new inline SVG marks come from Simple Icons 15.22.0 (CC0), `@lobehub/icons-static-svg` 1.101.0 (MIT), and the official Vapi/Retell header wordmarks. Exact URLs, SHA-256 values and transformations are in the provenance JSON. The Lobe MIT notice is retained in `app/views/brands.php`; pinned upstream commit `aea0ecb0fe1439467219197c52ffaeb579dfe33f`. Marks are self-contained, use the page's foreground color and make no external image requests. All marks identify the curriculum tools, without implying partnerships.


### Course project showcase (3 Nov 2026)

`app/views/course-project-visual.php` contains four first-party SVG scenes: a website, presentation/social/video content kit, document assistant and connected form/AI/sheet workflow. They use no external images, libraries or SVG IDs. They are labelled course-project examples, with a reserved `.example` browser address. The previews demonstrate the syllabus's project direction rather than client or student results.

Copy, tools and day captions are backed by the supplied workbook: website building/publishing on days18-26 (publishing on day26), creative content on days11-16, document/RAG assistant on days45-46, and connected n8n workflows on days36-39. The capstone is student-selected, so the footer says students can build around their own idea. Motion is a short one-time UI assembly/connection/check reveal; reduced motion and no-JS keep every scene complete.

- Corporate training workshop hero (4 Nov 2026): original code-authored SVG in `app/views/corporate-workplace.php`. Stylised training illustration, not a photograph or client proof. No third-party assets.

- Corporate hero image (4 Nov 2026): generated with the built-in imagegen tool; conceptual editorial illustration, not a real Artificia/client training photograph. Production WebP variants: `public/assets/img/corporate/ai-workshop-v1-{480,800,1200}.webp`; source `storage/corporate-ai-workshop-v1-source.png`; exact prompt `docs/assets/corporate-ai-workshop-prompt.txt`. Forest green/lime Indian corporate team workshop, own documents and practical AI workflows. Supersedes the SVG hero scene; original SVG retained unused.

- Corporate hero final direction (4 Nov 2026): user requested animation only. Page again renders the original animated SVG workshop in `app/views/corporate-workplace.php`. Generated workshop photos and prompt are retained unused and are not loaded by this page.

- Corporate workshop refinement (4 Nov 2026): original SVG scene and five original seated-character drawings in `app/views/corporate-workplace.php` and `app/views/corporate-people.php`. Code-authored materials, furniture, UI and coordinated document-to-reviewed-workflow animation. Conceptual illustration; no third-party assets or real-client implication.

- Corporate workshop motion expansion (4 Nov 2026): original code-authored document-transfer sheets, workflow UI/cursor and character gesture rigs added to the existing SVG. Three colleagues, coordinated 10-second story, no additional media/library dependencies.

- Corporate hero simplified (4 Nov 2026): original HTML/CSS customer-support example in corporate-workplace.php replaces the boardroom SVG. Three purposeful CSS motions; no third-party image or library. Product guide/FAQs/brand voice and reviewed reply are illustrative training inputs/output, not client proof.
