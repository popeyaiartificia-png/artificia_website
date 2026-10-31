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

The four unbranded workflow, conversation, knowledge and voice panels are original HTML/CSS service illustrations in `app/views/work-visual.php`. They are not screenshots of a named customer account, actual customer messages or evidence of deployments. Only the desktop/mobile website previews name Orkay. The gallery borrows its open composition and desktop horizontal scroll behavior from the reference https://landonorris.com/; no reference photographs, logo or signature were copied.

### Orkay GoHighLevel gallery panel (31 Oct 2026)

The horizontal GoHighLevel panel in `app/views/work-visual.php` is an original interface illustration, not a screenshot of a private CRM account. It has no contact records, customer messages or invented performance metrics. Setup and management are already documented in the Orkay case study; ongoing support was explicitly confirmed by the user on 31 Oct. It replaces the mobile website capture on the homepage; the desktop website preview remains.

- The gallery's Orkay IT Support Team illustration (31 Oct) is an original inline SVG monitor, headset and helpdesk graphic in `app/views/work-visual.php`. It replaces the workflow diagram on that card and contains no real support-ticket or customer data.

### Gallery reference effects (31 Oct 2026)

Measured https://landonorris.com/ directly in the browser: dark-to-light background colors #282C20 / #363B25 to #F4F4ED / #D4D7CA, power1.out color progression, horizontal scrub 1, four-rem photo parallax and .6s reveal/.6s retract with a .3s offset for lime caption wipes. The reference's two-stage noise/outline rendering was studied; the shipped single-pass contour shader is original code using this site's existing noise utility, with capped rendering and static fallback. No reference photo, font, logo, signature or proprietary runtime was added. Existing Orkay content remains unchanged.
