# Where the shipped files come from

## Images

| File | Source |
|---|---|
| `public/assets/img/artificia-logo.png` | Artificia's own logo, `ARTIFICIA STUDY MATERIAL/artificia-brand-docs/assets/logo.png` (Brand Bible: use as supplied). |
| `public/assets/img/favicon.png` | The old site's favicon, `legacy/landing-page/images/favicon.jpg.jpeg`, resized to 64 px. |
| `content/seed/media/hardik-aseri.jpg` | `legacy/landing-page/images/hardik.jpg.jpeg`, resized. |
| `content/seed/media/vijay-linju-raghavan.jpg` | `legacy/assets/images/all_images/about/vijay.jpg`. |
| `content/seed/media/alphonsa-n-p.jpg` | `legacy/landing-page/images/testimonials/`, resized. (The old page's photos of Haritha Nair and Nilesh Chaudasama were the old template's stock "team" images, so they are not used.) |
| `content/seed/media/hatim-sorangwala.jpg`, `dev-chokshi.jpg` | Google Drive thumbnails of their own testimonial videos (the Drive links in `legacy/landing-page/index.html`). Khushi Dodiya's video gave no thumbnail. |
| `content/seed/media/orkay-website.jpg` | Screenshot of the live orkaytiles.com (the "Morbi-made" section, no people), 27 Oct 2026. |
| `content/seed/media/classroom-instructor.jpg`, `corporate-session.jpg`, `business-owner-desk.jpg`, `professional-office.jpg` | Illustrations from the old corporate page, `legacy/co-orporate/`. Used as illustrations, never as proof of a real class or client. |
| `content/seed/media/audience-*.jpg`, `learner-lime.jpg` | Illustrations from the old landing page, `legacy/landing-page/images/`. |
| `content/seed/media/industry-*.jpg` | Industry photos from the old corporate page, `legacy/co-orporate/ind_*.jpg`. |
| `content/seed/media/ai-*.jpg` | Artificia's corporate-training brand illustrations, `ARTIFICIA CO-OPERATE TRAINING/assets/brand/`. |

Alt text for every seed image lives in `content/seed/media/alt.json`.

The lime sketches in `app/views/drawings/` are drawn in code for this site; no rasters.

## Fonts, icons and code

- Roboto Flex, Google Fonts, SIL Open Font License 1.1 (`public/assets/fonts/`).
- Phosphor Icons, regular weight, MIT licence (`app/views/icons.php`).
- GSAP 3.13.0 and ScrollTrigger, GreenSock standard "no charge" licence (`public/assets/vendor/`).
