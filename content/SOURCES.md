# Where the shipped files come from

## Images

| File | Source |
|---|---|
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
| `content/seed/media/ai-*.jpg` | Artificia's corporate-training brand illustrations, `ARTIFICIA CO-OPERATE TRAINING/assets/brand/`. |

Alt text for every seed image lives in `content/seed/media/alt.json`.

The lime sketches in `app/views/drawings/` are drawn in code for this site; no rasters.

## Tool logos

`app/views/brands.php` holds each tool's official mark as single-colour SVG path data:

- [Simple Icons](https://simpleicons.org) 15.22.0 (CC0): ChatGPT (OpenAI), Claude, Gemini, Perplexity, NotebookLM, ElevenLabs, Canva, n8n, Supabase, WhatsApp, Meta, Google Sheets, Google Drive, Google Analytics, Google Search Console, Google Cloud, Next.js, PHP.
- [Lobe Icons](https://github.com/lobehub/lobe-icons) 1.101.0 (MIT): Copilot, Claude Code, Runway, Lovable, Antigravity, AWS.
- The brand's own website, 28 Oct 2026: Pinecone and Gamma (the mark from the header logo on pinecone.io and gamma.app), GoHighLevel (the three-arrow mark from gohighlevel.com's header logo), HeyGen (traced from heygen.com's logo image).

The marks belong to their owners and are shown only to name the tools taught or used.

## Fonts, icons and code

- Roboto Flex, Google Fonts, SIL Open Font License 1.1 (`public/assets/fonts/`).
- Phosphor Icons, regular weight, MIT licence (`app/views/icons.php`).
- GSAP 3.13.0 and ScrollTrigger, GreenSock standard "no charge" licence (`public/assets/vendor/`).
- Quill 2.0.3 rich-text editor (admin page editor only): `dist/quill.js` and `dist/quill.snow.css` from the npm package `quill@2.0.3` (sha512 `xEYQBqfYx/sfb33VJiKnSJp8ehloavImQ2A6564GAbqG55PGw1dAWUn1MUbQB62t0azawUS2CZZhWCjO8gRvTw==`), unchanged, in `public/assets/vendor/quill/`. BSD 3-Clause licence (the package's own `LICENSE`, copied alongside), © Slab, Jason Chen and salesforce.com.
