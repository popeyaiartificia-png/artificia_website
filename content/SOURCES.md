# Where the shipped files come from

## Images

| File | Source |
|---|---|
| `public/assets/img/showcase/orkay-desktop.webp`, `orkay-mobile.webp` | Real viewport screenshots of https://www.orkaytiles.com/, captured 31 Oct 2026 at the user's request (1440x960 desktop / 390x844 mobile). Necessary-only cookie choice dismissed the consent overlay before capture. Encoded as WebP; no generated or reconstructed website content. Orkay is the named client already approved in the case study. |
| `public/assets/img/showcase/contours.svg` | Original periodic contour field generated in code for the gallery background. Static SVG retained as the fallback for the gallery's new animated canvas. |
| `public/assets/img/artificia-logo.png` | Artificia's own logo, `ARTIFICIA STUDY MATERIAL/artificia-brand-docs/assets/logo.png` (Brand Bible: use as supplied). |
| `public/assets/img/favicon.png` | The old site's favicon, `legacy/landing-page/images/favicon.jpg.jpeg`, resized to 64 px. |
| `content/seed/media/vijay-linju-raghavan.jpg` | `legacy/assets/images/all_images/about/vijay.jpg`. |
| `content/seed/media/alphonsa-n-p.jpg` | `legacy/landing-page/images/testimonials/`, resized. (The old page's photos of Haritha Nair and Nilesh Chaudasama are byte-identical to the old template's stock team photos, `legacy/assets/images/team_image1.jpg` and `team_image2.jpg`, labelled "Joseph Carrion" and "Leon Brown" there, so they are not used; their cards show initials until real photos arrive.) |
| `content/seed/media/hatim-sorangwala.jpg`, `dev-chokshi.jpg` | Google Drive thumbnails of their own testimonial videos (the Drive links in `legacy/landing-page/index.html`). Khushi Dodiya's video link no longer opens, so her testimonial was removed (28 Oct 2026). |
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

- Statement sans: Mona Sans from the official Google Fonts source (https://github.com/google/fonts/tree/main/ofl/monasans), SIL Open Font License 1.1. Pinned to weight 660 / width 93, Latin subset, renamed Artificia Statement Sans to respect the reserved name. `statement-sans.woff2`: 17,896 bytes; upstream licence in `public/assets/fonts/monasans-OFL.txt`. Copyright retained in the font.
- Statement serif: Bodoni Moda from the official Google Fonts source (https://github.com/google/fonts/tree/main/ofl/bodonimoda), SIL Open Font License 1.1. Pinned to weight 500 / optical size 72, Latin subset, renamed Artificia Statement Serif. `statement-serif.woff2`: 13,036 bytes; licence in `public/assets/fonts/bodonimoda-OFL.txt`. Close substitute for commercial Brier Bold; Brier was not copied or installed. An exact match requires a licensed Brier font.

- Roboto Flex, Google Fonts, SIL Open Font License 1.1 (`public/assets/fonts/`).
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

The homepage testimonial layout takes its overlapping portrait-card composition, rounded corners, paper-white #F4F4ED ground and two-line sans/serif heading treatment from the social section of https://landonorris.com/. The implementation is original PHP/CSS/JS and uses the existing licensed Mona Sans/Bodoni Moda subsets; no reference portraits, proprietary fonts or runtime were copied.

The video links for Hatim Sorangwala (Drive file 103bVzroX5C0fOxDYioCAzEwGQKQQfOwj) and Dev Chokshi (1c5KXs4l68QmhFu3rUaCY7r6I3NWdkklw), plus their existing seed portraits, come from legacy/landing-page/index.html. Connected Drive metadata confirms hatim.mp4 and dev.mp4; the Hatim embedded preview loaded its Play control in browser QA. The old Khushi Dodiya file 1pSwvcA3zmFFytjbKGpuf6MniX58VhVF2 returned 404 through the connected account and is not included in the new homepage fan. No raw video files were found in this project checkout. Players load only after a card is clicked, with a direct-link fallback.

Written reviews are the existing sourced quotations from Alphonsa N. P, Haritha Nair and Nilesh Chaudasama, unchanged. Alphonsa uses the existing seed portrait. **Correction before publication, 2 Nov:** SHA-256 comparison confirms the legacy Haritha/Nilesh portraits are byte-identical to stock team photos (`legacy/assets/images/team_image1.jpg` and `team_image2.jpg`). They are unsuitable as reviewer identities. Both seed/sample photo references and alt-text entries have been removed; the cards use initials and quote excerpts, with full reviews in the dialog/no-JS section. The resized stock files are retained only in ignored local `storage/testimonial-stock-reference/` and are excluded from GitHub.
