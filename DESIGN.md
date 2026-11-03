---
name: Artificia AI, The Highlighted Textbook
description: Warm paper, ink type and the brand lime used the way a student uses a highlighter; a printed guide to AI that teaches as you read it.
colors:
  paper: "#F3EFE6"
  paper-2: "#EAE5D8"
  card: "#FBF9F3"
  ink: "#16170F"
  ink-2: "#3A3C32"
  muted: "#62645A"
  night: "#15160F"
  night-ink: "#F2EEE4"
  lime: "#AED534"
  lime-hi: "#C3E651"
  warn: "#A3271A"
  warn-night: "#FF9A85"
typography:
  display:
    fontFamily: "Roboto Serif, Georgia, serif"
    fontSize: "clamp(2.6rem, 1.5rem + 4.4vw, 5.5rem)"
    fontWeight: 320
    lineHeight: 0.99
    letterSpacing: "-0.026em"
  headline:
    fontFamily: "Roboto Serif, Georgia, serif"
    fontSize: "clamp(2rem, 1.35rem + 2.5vw, 3.6rem)"
    fontWeight: 320
    lineHeight: 1.04
    letterSpacing: "-0.026em"
  title:
    fontFamily: "Roboto Serif, Georgia, serif"
    fontSize: "clamp(1.3rem, 1.15rem + .5vw, 1.6rem)"
    fontWeight: 400
    lineHeight: 1.2
  body:
    fontFamily: "Roboto Flex, Roboto, system-ui, sans-serif"
    fontSize: "1.0625rem"
    fontWeight: 400
    lineHeight: 1.65
  label:
    fontFamily: "Roboto Mono, ui-monospace, monospace"
    fontSize: "0.72rem"
    fontWeight: 500
    lineHeight: 1.4
    letterSpacing: "0.06em"
rounded:
  none: "0"
spacing:
  pad: "clamp(20px, 4.4vw, 72px)"
  section: "clamp(4.5rem, 3rem + 6vw, 9.5rem)"
  max-width: "1400px"
  bar: "64px"
components:
  button-primary:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.paper}"
    rounded: "{rounded.none}"
    height: "52px"
  button-primary-hover:
    backgroundColor: "{colors.lime}"
    textColor: "{colors.ink}"
  button-primary-night:
    backgroundColor: "{colors.lime}"
    textColor: "{colors.ink}"
  button-outline:
    backgroundColor: "transparent"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
    height: "52px"
  input:
    backgroundColor: "transparent"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
    height: "48px"
---

# Design System: Artificia AI, The Highlighted Textbook

## Overview

**Creative North Star: "The Highlighted Textbook"**

The site reads like a beautifully printed guide to AI that a student has already started marking up. Warm paper (#F3EFE6) with a faint grain, ink type set in Roboto Serif, and the brand lime (#AED534) used only the way a highlighter is: swiped behind the words that matter. Notes, numbers and metadata are written in Roboto Mono, the pencil in the margin. The masthead, the footer, the numbers and the call to action are printed in reverse on near-black ink (#15160F), which bookends every page and gives the real logo (white lettering) the dark ground it needs.

The site also teaches while it sells. The hero can carry a live "next word" lesson (how a language model writes), and every AI word on a page (RAG, MCP, n8n, agents, prompting...) explains itself in plain English the first time it appears.

Built for students first: a comfortable, low-glare page for evening reading on a phone, plain sentences, nothing that looks like a template.

**Key Characteristics:**
- Paper, ink and one lime. Lime is a highlighter behind ink, never text on paper.
- Serif headlines in sentence case, light (320) and tight; the emphasised words go italic and get the highlighter.
- Every section opens on a ruled line with its number (01, 02...), like a chapter.
- Photographs are printed in monochrome ink on the paper, so the stock imagery stops looking generated.
- Square corners everywhere. Depth comes from paper sheets (card, plate), not glass or glow.

## Colors

### Paper (the page)
- **Paper** (#F3EFE6, `--paper`): the page, with a 220px SVG grain tile (`--grain`) on top.
- **Paper 2** (#EAE5D8, `--paper-2`): bands behind lists people scan (tools, testimonials), image wells, the map.
- **Card** (#FBF9F3, `--card`): a sheet laid on the page: the live demo, the form plate, flow nodes, diagram boxes.
- **Ink** (#16170F, `--ink`): headings, primary button fill, strong rules.
- **Ink 2** (#3A3C32, `--ink-2`): reading text.
- **Muted** (#62645A, `--muted`): notes, numbers, metadata, placeholders (5:1 on paper).
- **Lines**: `--line` ink at .12, `--line-2` ink at .26.

### Night (printed in reverse)
`.top, .foot, .sec--stats, .sec--cta, .gloss` redefine the same tokens: paper #15160F, paper-2 #1D1E17, card #212219, ink #F2EEE4, ink-2 #CDC9BD, muted #9D9A8E, lines in light ink. Components use the tokens, so they work on night without extra rules. On night the primary button is lime, and emphasis is lime text instead of a highlight band (`--mark: transparent; --em: var(--lime)`).

### Lime
- **Lime** (#AED534): the highlighter. Behind emphasised heading words, behind picked words in the demo, as the hover swipe on titles, doors and questions, list dabs, the "After" label, the steps rail, the form plate's top edge, the WhatsApp square, selection.
- **Lime Hi** (#C3E651): hover of the lime button on night and of the WhatsApp square.
- **Warn** (#A3271A on paper, #FF9A85 on night): form errors only.

### Named Rules
**The Highlighter Rule.** Lime sits behind ink or fills a small mark. It never colours text on paper (contrast is 1.5:1). The only lime text is on night.
**The Token Rule.** Components read `--paper`, `--ink`, `--line`... never literal colours, so a section can be switched to night by adding it to the night selector.
**The Warn Rule.** Warn appears only on invalid fields and form status errors.

## Typography

The shared design uses three self-hosted Roboto faces. The statement is a user-requested exception (30 Oct): Mona Sans and a Bodoni Moda serif substitute, detailed below.
- **Roboto Serif** (display, h1-h3, demo sentence, quotes, FAQ questions, footer statement). Cut at a fixed optical size of 96, roman and italic, ~73 KB and ~81 KB. The roman is preloaded; the italic loads when an emphasis appears.
- **Roboto Flex** (reading text, buttons, form labels, nav).
- **Roboto Mono** (labels: section numbers, entry lines, notes, ruler days, tool days, contact terms, footer heads).

### Hierarchy
- **Display / h1** Serif 320, clamp(2.6-5.5rem), line-height .99, -.026em, max 15ch; page variant 2.4-4.75rem, 20ch.
- **Headline / h2** Serif 320, clamp(2-3.6rem), 1.04, max 21ch; CTA 2.4-5.25rem, 16ch.
- **Emphasis** (`*word*` in a heading) italic 300 with the highlighter band at 86% height, .36em thick, `box-decoration-break: clone` so every line gets its own swipe.
- **Title / h3** Serif 400, 1.3-1.6rem.
- **Body** Flex 400, 1.0625rem, 1.65; prose max 66ch, its first paragraph 1.15em in ink as a standfirst; ledes 1.06-1.3rem, 54ch.
- **Label** Mono 500, .72rem, .06em tracking, uppercase.

### Named Rules
**The Sentence Case Rule.** Headings are sentence case. Uppercase belongs to mono labels only.
**The Under Rule.** Entry and metadata lines sit under their title. The only thing above a heading is the section's ruled number.

## Layout

`.wrap` caps content at 1400px with `--pad` side padding (20px to 72px). Sections have top padding .62 × `--sp` and bottom padding `--sp`, and each numbered section draws a full-width rule at its top with its number under the left end (`.sec::before`, a CSS counter). Sections without a heading keep the rule but take no number.

Split layouts use 7/5 (hero), 5/7 (steps, lead form), 4/8 (FAQ), 5/6 (case study) with sticky left columns on desktop. Card grids are 2, 3 or 4 columns, each card ruled at the top in ink and numbered. Breakpoints in use: 480, 640, 720, 900, 1024. Under 1024px the nav becomes a full-screen night contents page.

### Hero variants
- **Split** (default): copy 7, media 5. With a drawing the mobile order is heading, sub, drawing, doors, foot.
- **Centered**: copy centred, media below.
- **Cover**: an opening spread. Headline and standfirst side by side, the image as a wide 21:8 plate, the doors under it.
- **The eyebrow field** renders as the hero foot: a mono line with a lime dab, at the bottom.

## Elevation & Depth

Paper on paper. A sheet (`.predict`, `.plate`, `.cs__shot`) has a 1px `--line-2` border and a long soft drop `0 40px 70px -50px rgba(22,23,15,.55)`. No blur, no glass, no glow. The masthead is sticky and solid.

## Shapes

Square. No radius anywhere. Lines are 1px hairlines; strong rules are 1px or 2px ink; diagram strokes 1.6px ink.

## Components

### Buttons
Square, 52px (40px in the masthead), Flex 600 .94rem, sentence case, Phosphor arrow that nudges 4px on hover.
- **Primary** (`.btn--lime`): ink fill on paper, hover lime with ink text. Lime fill on night, hover Lime Hi.
- **Outline** (`.btn`): 1px ink border, hover ink fill.

### Doors (hero chooser) as contents
Rows between hairlines: mono number, serif label, dotted leader, mono note, arrow. Hover swipes the highlighter under the label. Under 640px the note drops under the label and the leader goes.

### Stage hero (`variant: stage`, the home page)
Modelled on landonorris.com's hero; the homepage is being moved to that direction one section at a time, and the rest of the site still uses the paper system above. `app/views/hero-stage.php`, styles under "Stage hero" in site.css, script under "The stage hero" in site.js.
- **Ground:** black #0A0B07, the robot photograph's own seamless backdrop, so its edges disappear; faint olive-grey topographic contour lines (marching squares over smooth noise) pass behind the robot, which the shader keys off that backdrop with a solid core over the head and neck.
- **Robot:** `public/assets/img/hero/robot.webp` (1136×1408, generated in Canva): head, neck, shoulders and a sliver of chest. It is centred and runs off the bottom of the screen (height 132%, bottom −20%) so the frame cuts just under the shoulders; on phones it fills the screen from the top and fades out above the corners. A WebGL layer redraws it in place every frame from the `<img>` box; without WebGL, or under reduced motion, the plain photograph shows.
- **The neon reveal (liquid ink):** two WebGL passes. A half-size ink mask persists between frames: each frame it drifts on a slow noise flow, softens (a 5-tap blur), fades (×0.99 − 0.0016, the constant so 8-bit values never stall) and takes the pointer's stroke since the last frame as one capsule, so a fast swipe is a single unbroken ribbon; the stroke follows the cursor a beat behind (lerp 0.28) and widens on a quick flick. The picture pass thresholds that ink with an fbm-eaten edge (fine erosion grows as the ink thins), so pools dissolve with crisp ragged edges, never fog. Inside the ink the robot becomes a dark x-ray drawn in `--neon` #D2FF00, and over the black a faint olive tone, so the shape carries on across the background without a cut; a neon hairline marks only fresh, steep edges. With nobody pointing for 2.2s (and on phones) a slow wanderer paints over the face on its own. With `robot-helmet.webp|png|jpg` in the same folder the ink reveals that layer instead (aligned by `HELM` in site.js), with a faint wireframe of it over the face at rest.
- **Always moving (the automation loop):** the hero never stands still, but plays one event at a time on a shared 7 s clock (`CYCLE`, `IN`, `OUT`, `SCAN` in site.js). (1) The ground is a live GPU contour field (fbm under a slow warp, lines from `fwidth`, 30 fps, paused off screen) that drifts and folds. (2) On screens 1180px and wider with room above the copy, three input chips on the left, everyday frictions from the blueprint (Manual tasks, Disconnected tools, Scattered data), light up and send neon pulses along curved hairline wires into the robot's left temple, where a ring pings; each then crosses the crown as a short neon comet lighting only the head's own seams (shader `uThink`), reaching the right temple as its result leaves. (3) The robot scans: a thin neon line sweeps down its surfaces only, leaving a short x-ray wake (shader `uScan`). (4) Pulses leave the right temple for the **live-run panel** on the right (`.run`), which balances the copy block: a glass card level with the robot's eyes and clear of the doors, headed "Live run · AI automation" with a step counter, three rows, input k's outcome on row k (Data entry automated / CRM & systems connected / Reports & dashboards ready, in the blueprint's own terms) whose status goes from an empty ring to a turning neon arc while its pulse travels to a neon tick when it lands, then a seven-bar report preview that rises once all three are done, and a footer labelled "Example run" with a progress bar for the cycle. Row states are set from the same clock in the frame loop (`updateRun`), so they never drift from the pulses. Wires, pulses and rings are SVG timed with the Web Animations API; chips also bob gently on CSS. The liquid wanderer lifts while the scan passes so two neon events never overlap, and the robot breathes with a 1.2% scale over 9 s. On phones only the terrain, scan and liquid run. Under reduced motion everything stands still and the terrain is the static drawing.
- **The livery band (the reference's helmet chin guard, retold):** two 2D canvases sandwich the WebGL robot inside the card (`.stage__rig--back` under it, `--front` over it), so the back half of the band passes behind the neck. A neon band (`BAND` in site.js: 0.6 down the photo, under the chin) wraps the neck, shaded as a cylinder, with "ARTIFICIA ✦ AI AUTOMATION ✦ AGENTIC WORKFLOWS ✦ BUSINESS SOFTWARE ✦ CORPORATE AI TRAINING" running right to left round it in compressed 3D letters, and a faint light ribbon sweeps from the lower left up behind the neck. After the page enters, the band rises in (1.7 s); then it bobs and leans a little toward the pointer. It follows the photograph's box every frame, so it rides the breathing and the scroll shrink. Nothing sits above the head. On short windows (under 150px above the copy) the input chips line up in a row instead of a column.
- **Copy, left of the face:** the eyebrow as a mono line with a neon dash; the h1 in the paper design's display type (Roboto Serif 320, sentence case, -.026em, 2.1–3.5rem) with its `*emphasis*` on its own line (hero-stage.php inserts a `<br>`) in italic with the **highlighter**: a neon band from mid-letter to just below the baseline. On dark ground each highlighted glyph is split so it stays readable, cream above the band and ink inside it (two `background-clip: text` layers over the band; plain neon text where unsupported), and the band is drawn in from the left after the page enters. The same treatment heads the trust strip; the light problem→outcome section uses the original ink-on-neon highlighter. Copy is kept minimal so the design leads: eyebrow, two short headline lines ("Automate more. / *Put AI to work.*", 2.6–4.6rem), no paragraph (the stage still renders a subheading if one is entered), then one neon button (primary CTA) and a quiet underlined text link (secondary CTA). The block's width is `min(40rem, 50vw − 20svh − pad − 2.5rem)`, which stops short of the head at every laptop size, and it sits above the shoulder. The doors stay as three small caps links bottom-right (notes as tooltips, neon on hover), and a small neon "Move to reveal" chip bottom-centre. On phones the copy follows the robot on a dark fade, then the doors under a hairline.
- **Scroll (900px and up):** pinned for 120% of the viewport, the corners lift away, the black turns olive (#232719), the robot pulls back into a black photo card and the headline runs past behind it as two giant marquee lines (emphasis in neon serif, the rest in pale sans).
- **Masthead on a stage page:** fixed and transparent, the nav folds into a menu button at every width, the Enquire button is neon caps with 6px corners. The colour logo shows over the black.

### Homepage after the stage (blueprint order)

**30 Oct marker refinement:** the With AI circle is smaller (90% of the word box, reduced again from 108% at the user's request), tilted −8° and drawn as an asymmetric open loop with a 6-unit stroke. It sits inside and crosses the letterforms instead of forming a large enclosing ring. The Before strike keeps its existing size and behaviour.

**30 Oct seamlessness refinement:** Before AI / With AI now uses one unpinned scroll timeline on desktop and phone: the curved light dawn, subtle figure entrance, contour fade and copy reveal share one clock. The preceding workflow keeps its olive surface. Figures now remain opaque to their lower crop (bottom fade removed at the user's request); central reading columns are narrower and the figures sit farther out to protect copy. Hover movement is reduced to 2–3%, with a subtle robot glow (later limited to the robot: the person no longer reacts to the pointer). GSAP matchMedia reverts the sequence across the 1100px breakpoint and when reduced motion changes. Keyboard focus reveals an unfinished arrival immediately. This replaces the earlier pinned desktop dawn and mobile colour-fade behaviour described below.
From 29 Oct 2026 the homepage follows `docs/ref/Artificia_Website_Revamp_UIUX_Development_Blueprint.pdf`: technology and AI automation first, corporate training second, courses third. Its sections are rebuilt one at a time in the stage look and scoped with `.hero--stage ~ .sec--X` (How we work: `.hero--stage ~ .tools--flow`, the class tools.php adds when the `flow` field is filled), so the same section types keep the paper design on every other page. Rebuilt sections drop the paper numbered rule. Every rebuilt section is set at the same large scale (serif headline 2.4–4.8rem, padding 4.5–8rem).
- **Statement** (`statement`, one textarea): directly after the stage on olive #232719, with the shared moving contour background. Reference-matched typography (30 Oct user request): Mona Sans pinned to weight 660 / width 93, off-white #DDE1D2, .9 line-height and tight tracking. Desktop width 74.166667vw; type size 7.349537vw, capped at 160px (141.111px at 1920; 112.889px at 1536). Tablet: 86.71vw width and 8.101852vw type. Phones below 480px: 93.0615vw width and 9.722222vw type, minimum 32px. Emphasis: high-contrast Bodoni Moda subset (weight 500, optical size 72), #B2C73A; 1.03937em on desktop, 1em below 992px, with closer tracking. This is a close substitute for licensed Brier Bold. The two renamed self-hosted OFL subsets total 30,932 bytes. Matched to the measured live reference on 31 Oct: each line starts .15s after the previous line. Its shared clip reveals the lime bar left-to-right over .6s (power2.out); the right-anchored bar retracts over .6s (power2.inOut), starting .3s after that line begins. Lines overlap in a staggered cascade. Seven lines take 1.8s, triggered once at top 90% of the viewport. Bar inset is 0 and reconstructed lines do not wrap, as on the reference. Original HTML stays accessible, the visual duplicate is aria-hidden, and font-ready measurement plus ResizeObserver rebuild the lines. Reduced motion and no-JS remain static.
- **Work gallery** (`work_gallery`, added 31 Oct): inserted after How we work and before Before AI / With AI, at the user's request for the reference's floating image section. An olive-to-ivory animated contour ground and a loose six-visual composition: a real Orkay Tiles desktop website preview, a horizontal GoHighLevel setup/management/support illustration linked to the Orkay case study, plus a monitor/headset/helpdesk illustration captioned "Orkay Tiles IT Support Team" and two unbranded service-offering panels: AI automation, Agentic AI and marketing automation in one; software/apps, cloud/private AI, SEO/AEO/GEO and corporate AI training in the other. A separate AI Voice Agents waveform remains. These three service cards link to /services. Heading: "Our work. Built around your business." (expanded to six words at the user's request) and a general gallery statement that can be replaced by a client review. The local preview now has clearly labelled draft wording for Pavin, Orkay Tiles: "One partner for our website, automation and AI." Captions describe capabilities; no invented client names, testimonials, deployments or performance results. Desktop (1100px+ wide, 720px+ high) uses a scroll-driven horizontal scene with parallax, a pinned viewport, persistent previous/next controls and focus-driven keyboard navigation. Smaller screens, reduced motion and no-JS keep every item in the document flow. Other CMS combinations also use the static grid, avoiding overlapping duplicate layouts. Captions, descriptions, links, visual choices and image replacements are editable in the section registry. The homepage now loads only the desktop website capture (about 145 KB); the former mobile capture remains available in the section library but is not displayed here; no new libraries, fonts or remote embeds. Its dedicated contour canvas renders only while visible. The local homepage was updated with a saved prior snapshot and page revision. The gallery now supports an optional approved review with a reviewer name/company, replacing the anonymous thought. At the user's request, draft wording is "One partner for our website, automation and AI." with Pavin, Orkay Tiles. A visible draft/pending-approval label appears in the local preview only. Only review_status=approved renders the review and attribution outside local; missing or unknown status falls back to the anonymous thought. The review layout uses a small byline instead of the decorative scribble, and longer reviews fall back to the open static layout if they overlap the website image. Matched to the live reference on 31 Oct: the gallery ground moves from #282C20 through grey to #F4F4ED, with contour ink #363B25 to #D4D7CA. Uses a single viewport-sized WebGL canvas in an absolute terrain holder with a sticky child, broad slowly warped contours and subtle pointer response. Rendering is capped at 30fps, DPR capped at 1, and pauses off screen or when the document is hidden. The existing SVG remains the fallback for reduced motion, disabled JavaScript, missing WebGL or context loss. Scroll color uses the measured power1.out progression; desktop color starts with the pinned scene so the added Artificia heading does not advance the transition early. The scene uses scrub 1 and scales its track to 2.9 viewport heights, with four-rem relative picture travel and the measured .6s/.6s/.3s lime caption wipes. Whole frames move gently so UI text and the real website screenshot stay intact. The review stays narrow and retains its visible draft label. Text/control colors adapt to the light ground; controls avoid the floating WhatsApp button. Horizontal layout requires at least 1100px width and 720px height and enough travel/quote clearance; other layouts keep normal flow. The gallery and following Before AI / With AI now share one .workscape-pair backdrop and a single viewport-sized sticky contour canvas. The shared ground resolves to #F4F4ED (reference paper white); the comparison is transparent with its duplicate contour renderer disabled. There is no second dawn ellipse, contour reset or masking fade at their boundary. The separate dark Solutions flood is retained. The entry from How we work is now continuous: the gallery's first 420px uses the preceding section's document coordinates, exact nine-level contour field, rendered clock and .19-alpha line compositing on #232719, then smoothly morphs into the broad reference-style field and #282C20 ground. An integer contour offset keeps the field means aligned during the density change, avoiding a horizontal band. No extra canvas or animation loop was added. Static/reduced/no-WebGL output uses one fixed SVG coordinate system across Statement, How we work and the gallery; successfully linked live shaders restore the original upstream canvases before the join enters view.
- **Before AI / With AI** (`versus`): two facing profile heads on #F4F6F1, inspired by the reference's two-sided comparison. The left figure is a clean transparent middle-aged man matching the user's supplied reference: swept-back dark hair, salt-and-pepper beard, navy textured shirt and a thoughtful right-facing pose. Uses `person-profile-mature.webp` (800×1067, ~124 KB) with a 420×560 (~40 KB) responsive version. The right figure remains the robot. Desktop person height `min(84%, 46.2vw)`, bottom `max(-1%, -.55vw)`; robot height `min(80%, 44vw)`. Both offset outward 8% to protect copy, with matched crowns/head size. Phone heights 88%/84%, person margin-top -2% to account for transparent padding. No bottom opacity mask is applied to either figure; both keep their natural transparent silhouette and remain opaque to the lower crop, as requested by the user. Small italic serif Before / With above huge heavy AI; the With circle rests drawn, Before hover/focus swaps to its strike. Hover moves only the robot, 2–3% with a subtle glow on With AI; the person never reacts to the pointer and stays still and sharp at full opacity (user's decision, 30 Oct). The shared unpinned scroll timeline raises the original white dawn directly over the olive, brings in figures and reveals copy while retaining the contour background; the workflow above stays olive. The comparison canvas uses grayscale(1) for soft neutral grey lines on white. It no longer fades away during the arrival; only the existing mask at the section foot softens the join below. Reduced motion keeps the same grey styling on its static drawing. The grey bridge was removed at the user's request to restore the original transition. Responsive matchMedia handles breakpoint cleanup; reduced motion is static, and keyboard focus reveals an unfinished arrival. Sides share grid rows for aligned copy/buttons on desktop and stack on phones. The With AI CTA remains a 68px neon square arrow with a descriptive aria-label. Before AI now has a static 68px Manual tile: Phosphor clock, mono uppercase label, #E4E7DF fill, #8C9387 border and #3C4538 ink. It is a paragraph, with no link, button role, tab stop or hover animation. Both occupy the same desktop grid row. The optional `status_label` field in the section registry replaces a side's link when filled, keeping this editable through the CMS. **Foot (rebuilt 31 Oct, at the user's request):** when the dark Solutions gallery follows, it floods this section like water rising over a survey map, replacing the shallow arc, which still read as a hard line. `.versus__dusk` holds a WebGL canvas drawn by the same shader and terrain as the contour lines (`FLOOD` in site.js). The dark's edge is a moving contour with one neon line on its crest, and stepped tones run from dark to paper like map elevation bands, bunching and spreading with the terrain. A slow swell keeps it moving when the page is still. Scroll drives how far it has climbed (`bottom 100%` to `bottom 12%`, `power4.in`, scrub 1, so it keeps flowing briefly after the page stops). It stays calm and late in the middle, where the copy sits, and livelier over the figures. Reduced motion, no JS and no WebGL fall back to a static CSS arc. A soft white-to-grey-to-black fade was rejected: it read as fog and dimmed the neon button.
- **How we work** (the tools section after the statement; the user wanted general steps here, not client proof): on the same olive. Large serif heading left (as Problem → outcome), intro right, then the blueprint's seven steps (Discover, Design, Build, Integrate, Deploy, Train, Optimise) as a **pipe** (the tools section's optional `flow` field) on a hairline, each a node, a mono number, a title (1.25–1.75rem) and a mono note. A neon pulse runs the line every 7 s and lights each node and number as it passes (CSS only: `--i`/`--n` set the delays; vertical under 1024px; still under reduced motion). Under it a mono "Built on" label and, full width below it, the platforms in one row of 24px marks and names (notes hidden). No client names, partner or certification claims here.
- **Solutions overview** (`solutions`, revamped 31 Oct): Editorial grid on #11150F, directly after Before AI / With AI. Three columns at 1100px and above, two at 640-1099px, one below 640px; all six services appear in the page flow. A ruled OUR SOLUTIONS eyebrow and dynamic service count introduce a large cream serif heading with plain lime italic emphasis. The six cards reuse three complete palettes: lime AI automation, dark olive Agentic AI, ivory Software & apps, ivory Marketing Automation, lime Search, and dark olive Cloud & Private AI. The second row shuffles the first row's colors; text, borders and illustration colors follow each palette. Each card has category metadata, CMS title/description, an original inline SVG interface illustration, capability caption, short outcome line and circular arrow. Hover straightens/lifts the drawing, rotates the small star, inverts the arrow and pulses connection routes. The existing staggered entrance and keyboard completion remain; reduced motion disables movement and no-JS retains every card. Horizontal scrolling, previous/next controls and the range counter are removed. Service routes and the preceding contour flood remain. All registry illustration choices, including knowledge, are supported. No new images, fonts or libraries.
- **Crossed-off list** (`crossoff`; removed from the homepage at the user's request on 30 Oct and on no page now, kept in the section library; `use_cases` keeps the paper rows on other pages): "the work AI should take off your desk" as a to-do list being crossed off, on #F4F6F1. Each problem is heavy Roboto Flex caps (weight 820, width 112%) fitted by site.js to the full column, its first and last letters on the edges; a title too long to stay readable wraps at the CSS size. A crossed-off word is hollow (fill over stroke, which hides the variable font's inner overlaps; one uniform outline) with a hand-shaped neon marker stroke set to `mix-blend-mode: multiply`, so the ink shows through like a real highlighter. Outcomes are light Roboto Serif italic, with one short line beside them. The last line stays solid and uncrossed: it is the visitor's own task, and hovering or focusing its neon button crosses it off too. Motion is one gesture per line, played once and in order: the word rises solid, the marker's edge wipes across it (solid ahead, hollow behind), the outcome rises beneath. No row hover, because nothing there is clickable. From 1100px a single ink line at the section's top gives the figures of Before AI / With AI an edge to stand behind. Reduced motion and no-JS show the finished state.
### Loader (every page, first page of a visit only)
Modelled on landonorris.com's loader. A full-screen neon field (`--neon` #D2FF00) with the real logo printed in one ink colour (#1B1E10) through a CSS mask of `artificia-logo.png` (the file itself, never redrawn), and at the bottom a "Loading intelligence" label over a 52px pill that fills as the fonts, the page's first picture and the page load arrive. It leaves with an upward clip-path wipe no sooner than 1.6s and no later than 3.5s, then the stage rises in (the robot fades up out of the dark, the copy lifts in). The head script stores `al` in sessionStorage so later pages of the visit skip it; without JS it never shows, and a CSS fallback wipes it at 6s if the script fails.

### The live demo (`drawing: predict`)
`app/views/drawings/predict.php`. A card sheet: mono caption ("How a chatbot writes / Odds are illustrative"), a serif sentence with a blinking caret, four next-word options with probability bars, a note. Picking a word appends it in italic with the highlighter and shows the next options; after two picks the note explains what happened and "Start again" appears. The tree lives in the PHP file as JSON; without JS the first step still reads. Choose it in the editor: Hero, Drawing: predict.

### Glossary
`site.js` (GLOSS). The first mention on a page of each AI term in reading text (ledes, prose, card text, steps, modules, answers, flow nodes; never headings, links or buttons) becomes a `button.term` with a dotted underline. Hover, focus or tap shows a night card (`.gloss`) with a lime top edge; the definition is also its `aria-describedby`. Escape, scroll or a tap elsewhere hides it. Add a term by adding a row: pattern, label, one plain sentence.

### Drawings
Ink single-stroke SVG (1.6px), dashed muted guides, card-filled boxes, the key box filled lime (`.bx.hi`), mono labels, Flex notes. Each takes the form of its question. Strokes draw in on scroll.

### Cards (index)
Numbered columns ruled in ink at the top; photo (4:3, monochrome), serif title, text, corner arrow on linked cards. Pointing at a linked card washes the photo lime (a multiply layer, which makes a lime duotone) and swipes the title.

### Photographs
`filter: grayscale(1) contrast(1.06)` plus `mix-blend-mode: multiply`, so whites take the paper colour. The case-study screenshot keeps its colour (it is evidence) inside a plain browser frame. Avatars are grayscale; a written quote without a photo gets initials in a lime square.

### Steps, curriculum, tools, use cases
Steps: 44px square mono numbers on a hairline rail that fills lime with scroll; a reached step's number fills lime. Curriculum: a day ruler (ink ticks, a lime Foundation segment and a lighter lime Full-program segment), then two syllabus columns of serif module titles with mono day lines and tool lines. Tools: an index with dotted leaders and mono days. Use cases: a before/after table; "After" is a lime label.

### Stats
Night. Serif 300 numbers, lime dimension rules either side, labels under, in a hairline grid. The caveat "Typical outcomes for AI-enabled teams." stays with the stats.

### Testimonials
On a paper-2 band. Videos 4:5 in grayscale with a paper "Play video" chip that turns lime; the iframe loads on click. Quotes in serif 300 with hanging punctuation, signature under a hairline.

### Form plate
A card sheet with a 6px lime top edge. Fields are lines to write on (bottom border 1.5px muted; focus: ink underline plus a faint lime wash). Textarea is boxed. Checkbox fills lime with an ink tick. On success the form gives way to one serif line ticked in a lime square.

### Masthead, menu and footer
Night masthead, sticky, 64px, real logo image left, Flex nav with a lime underline for hover and the current page, lime Enquire button. The mobile menu is a full-screen night contents page with mono numbers and serif links. The footer is its own composition (see Footer, below). The lime WhatsApp square hides while the hero or the footer is in view.

### Motion
GSAP 3.13 + ScrollTrigger, only when the head script set `.motion`. Highlighters swipe in as headings arrive (`.sec__head.is-lit`), the hero's on load; diagram strokes draw; photos are uncovered top-down (clip-path); lists lift and fade; stats count up; the steps rail fills with scroll. The demo's bars grow and picked words are swiped in with CSS. Under `prefers-reduced-motion` nothing moves and everything is shown in its final state.

## Admin

The admin (`/admin`, `public/assets/css/admin.css`, `public/assets/js/admin.js`) copies Orkay's admin layout and interactions in this theme (ruling R30). It is the one place eyebrows above headings are allowed. Components are documented in the comment block at the top of admin.css.

- **Tokens:** ground #030304, panel #0a0b0d, raised #111316, lines white at .12/.24/.42, ink #F2F3EE, muted #9ba2a1, lime #AED534 (primary fill with #030304 text, active nav, focus, progress, markers), warn #ff9a85 (errors, danger), amber #e6b34a (warnings), green #7fd18b (success only). Roboto Flex 14px, sentence case, radius 0, no shadows, no blur, dark only.
- **Shell:** fixed 236px rail with the logo image and an "Admin" caption, grouped nav from `admin_nav()` (shell.php; a link shows only if its route exists and the role allows), top bar shortcuts from `admin_shortcuts()`, main column max 1280px; at 900px and under the rail is an off-canvas drawer.
- **Page head:** `admin_page_head(eyebrow, title, count, description, actionHtml)`.
- **Buttons:** `.btn`, `.btn--primary`, `.btn--ghost`, `.btn--sm`, `.btn--danger`, `.btn--block`.
- **Cards and stat tiles:** `.card` (22x24 padding, h2 16px); `.stats > .stat` (34px tabular number, a link tile lights its border lime).
- **Tables:** `.table` (muted 12px caps header, row hover tint, `.num` tabular), `.table--stack` stacks to cards under 760px.
- **Pills:** `.pill--published` (lime outline), `--draft`, `--warn` (amber), `--error`, `--you`.
- **Forms:** `.field` (label, input, hint, per-field `.err`), `.form-grid`, `.filters`, `.tabs`, `.editor` with a sticky 320px `.side`, `.savebar` sticky footer.
- **Media:** `ul.media` grid (140px min columns, 4:3 thumbs, name and size under, amber "No alt" pill), `.dropzone` (dashed, lime on dragover), native `dialog.dlg` (620px; `.dlg--wide` 960px, backdrop .45). `MediaPicker.open()` in `assets/js/media.js` is the reusable image picker (reads `/admin/media.json`).
- **Lists and editor:** `.tabs` (links, or `role=tab` buttons, with `.tab__count`), `.pager` (32px squares, current lime), `.cell-title` + `.cell-sub` (path or "by" line). The page editor: title and `.loc` address bar on top, tabs Content / SEO & meta / Settings, collapsible `.sec-card`s (number, type, summary; Up / Down / Remove click-again; a 2px warn left edge when a section blocks publishing) with `.list-field` > `.list-item` and `.img-field` (96px thumb + picker), a `.snippet` Google preview (title #8ab4f8, Google's own blue), `.counter` "n/60" amber under the minimum and warn over the maximum, and the sticky `.pub` Publishing card. Rich text is Quill 2.0.3, restyled dark and square under `.editor`.
- **Leads:** `/admin/leads` is Orkay's inbox in this theme: tabs All / Not forwarded / Spam with counts, `.filters` (form, page, source, `.filters__date` from/to), a 50-row table with a forwarded-status pill, and a detail page of `.card`s holding `dl.facts` (label/value pairs). Export, Delete (click again) and Retry forward are admin-only. The nav badge counts non-spam leads from the last 24 hours.
- **Feedback:** toasts bottom-right (3 s ok, 6 s error, `role` status/alert) from `admin_redirect($to, $msg, $kind)` or `Admin.toast()`; `.notice`, `.empty`; `data-confirm` (click again, 4 s), `data-dirty-guard` (leave warning, Cmd/Ctrl+S, `data-dirty-note`), 3px lime progress bar, "Saving..." busy buttons.

## Do's and Don'ts

### Do:
- **Do** keep lime behind ink (highlighter) or as a small filled mark; use it as text only on night.
- **Do** build components from the tokens (`--paper`, `--ink`, `--line`...) so they work on night.
- **Do** put the real logo image top-left on the night masthead; never a text wordmark, never recoloured.
- **Do** set headings in Roboto Serif sentence case and mark one or two words with `*emphasis*`.
- **Do** print photographs in monochrome; keep evidence (screenshots of real work) in colour.
- **Do** keep every corner square.
- **Do** draw a diagram in the form of the question it answers, in ink, with one key box in lime.
- **Do** keep videos and the map behind a click, and the stats caveat with the stats.
- **Do** keep the demo's odds labelled as illustrative.

### Don't:
- **Don't** put lime text on paper, or fill panels with lime.
- **Don't** reintroduce glass, blur, glows or gradients; depth is paper on paper.
- **Don't** use uppercase outside mono labels, or put a kicker above a heading (the ruled section number is the only thing there).
- **Don't** round any corner.
- **Don't** animate under prefers-reduced-motion.
- **Don't** use Warn outside form errors.
- **Don't** add glossary definitions that make claims; one plain sentence of what the thing is.

- **31 Oct 2026: transition edge.** The animated dark flood between Before AI / With AI and Solutions has a clean dark edge; the neon-green crest highlight is removed. Motion, dark fill and subtle contour lines remain.

### Homepage testimonials (31 Oct 2026)

The existing testimonials section offers an optional CMS layout field: standard (the prior video/quote rows) or fan. The homepage uses fan directly after Solutions, replacing its previous later occurrence. Four-word heading: Hear what / people say, rendered in uppercase with the existing Artificia Statement Sans 660 and Statement Serif 500 subsets. Paper-white #F4F4ED and large rounded corners echo the reference's social composition. Hatim, Dev and Alphonsa use their existing portraits; Haritha and Nilesh use initials/quote cards because the legacy portraits are verified stock photos. Responsive quote excerpts stop before the byline; full text remains in the review dialog and no-JS section.

**2 Nov 2026: stack to fan.** At 900px+ wide and 680px+ high, the section holds for .85 viewport heights of scrolling. It opens with one centered portrait-led stack and a lime quotation stamp reading "Many stories. One Artificia." After a short hold, one inherited CSS spread variable moves all five cards into a symmetric fan: Hatim in the centre, Dev/Alphonsa alongside, Haritha/Nilesh framing the edges. The stamp fades as the fan opens; reverse scrolling closes it again. The card sizes also respond to viewport height, keeping the heading and featured story visible. Hover lifts/straightens a card without a green pointer outline; keyboard focus keeps a visible outline, completes the fan and releases its scroll pin. An explicit GSAP fromTo preserves the closed state through ScrollTrigger refresh. MatchMedia reverts styles and pins on resize or motion-preference changes. Below 900px the same cards remain a native swipe/scroll-snap row, with the two videos first. Short desktop screens, reduced motion, no GSAP and no JavaScript keep the fully opened static layout.

Clicking a card opens a native dialog containing one Drive player or the original written quote. Escape, close button and backdrop clicks close it; closing destroys the iframe and restores focus and body scrolling. Video links and all three written quotes remain available without JavaScript. No player is loaded before a click. No new font or JavaScript dependency is introduced.

**2 Nov 2026: larger, closer cards.** Following two requests for larger cards and a tighter group, desktop sizing now uses min(23vw, 40svh, 460px). Horizontal travel is 65% of the original fan reach, and the vertical drop is also reduced to 65%, bringing the portraits into a connected overlapping group. Desktop portraits use a wider 5:8 frame; pinned cards have a viewport-height cap so the featured story's controls remain visible. The gap above the fan is removed and its top offset is 4px; stage padding also adapts to shorter laptops so the featured card clears the footer hint. Phone/tablet swipe cards use min(88vw, 380px) with a .65rem gap, retaining the taller 20:35 portrait. Image sizes match the enlarged cards. Stack/fan timing, hover/focus elevation and dialog interactions are unchanged.

### Homepage enquiry and review join (2 Nov 2026)

The pinned review stage keeps its 100svh height and existing scroll sequence, with an added bottom tail of clamp(112px, 12svh, 160px) on the section. This reserves space for the rotated outer cards, which extended 33-42px below the stage on typical desktops and were previously clipped. Checks must measure every card's visible bounds, including after keyboard focus releases the pin. The form follows that reserved space without a negative margin or chapter rule. Both sections use the same solid #F4F4ED background.

The homepage form has a `lead--stage` variant, derived from the homepage slug; other pages retain their current form design. Its heading uses the same Artificia Statement Sans 660 / Statement Serif 500 pairing as the review section and footer, with dark olive serif emphasis and no paper-era highlighter. Body, labels and inputs use Roboto Flex; small labels use Roboto Mono. Left-hand copy and WhatsApp link align with the top of a rounded #232719 form panel. Fields have generous padding, a clear border, a 16px reading size, lime focus indicators and pale error text. The full-width submit button uses neon #D2FF00 and the display sans. The layout stacks below 1024px, and field rows stack below 640px.

The existing POST /lead action, required consent, optional email, timestamps, honeypot, attribution fields, validation and thank-you flow remain. No new network service or animation was added. Seven desktop/mobile geometry checks verify full card ends, shared background and no overflow; keyboard pin release, reduced motion, no-JS and the unchanged standalone contact form are checked separately. Error and success UI were tested with intercepted responses, without sending a real enquiry.

### Footer (2 Nov 2026, every page)

The closing screen, on the concept of landonorris.com's footer (a neon frame around a dark box, a big line, a figure, two link columns, a running strip, a legal strip) with nothing copied from it. `app/views/layout.php`; styles under "Footer: the neon frame" at the end of site.css; two script blocks in site.js. Corners here are rounded on purpose: this box is the reference's idea and the S-curves are what make it read, so it is an exception to the square rule, as the testimonial fan is.

**One axis carries it all.** The circled *AI* in the line, the robot's head, its neon tie, and the frame itself, which rises from the bottom strip as the Book button. All four share one centre at every desktop width (measured). The generated satin tie leads the eye to the button.

- **Frame:** `--neon` #D2FF00, 10-22px at the sides, a 40px strip at the foot holding the legal line. Panel #232719, radius 20-34px, flush with the top of the screen at rest so the masthead sits inside it.
- **Button:** the inverse of the reference's notch: the frame grows up into the panel on two bezier S-curves (`.foot__flare`), label "Book Free AI Consultation" in tar #16170F. It stays centered below the generated tie. The former CSS tie-bridge fillets are removed. Hover grows it out of the frame (height, not translate, so its feet never leave the panel's edge).
- **Line:** "Let's put *AI* to *work.*" in the statement's own type (Artificia Statement Sans 660, with Statement Serif 500 in #B2C73A). A neon marker loop, the same stroke as the With AI circle, rings the AI and draws itself on arrival. A three-column grid (1fr auto 1fr) keeps AI exactly centred whatever the words either side measure. The head stops just under the AI's baseline. It is a `<p>`, so every page keeps its own heading outline.
- **Figure (regenerated/edited 2 Nov):** the same robot in a plain charcoal suit, white shirt and neon satin tie, in one transparent cutout edited with the built-in image_gen tool. The user explicitly removed the suit's contour design; preserve the plain jacket. The tie retains generated fabric folds, highlights and reflected lime light. Same 1220x1150 canvas, centered scale and waist crop. Responsive assets: `robot-neon-plain.webp` (106,060 bytes) and `robot-neon-plain-640.webp` (34,318 bytes); alpha preserved. Exact edit prompt: `docs/ref/footer-robot-plain-prompt.txt`.
- **Suit:** solid charcoal fabric with natural texture, seams and folds. The background contour field continues independently; desktop footer has two canvases. The old suit weave canvas/mask remain unused.
- **Head:** part of the same image as the suit and tie. The entire figure keeps its scroll arrival; the former separate-head pointer animation is removed so the generated cutout stays coherent.
- **Links:** Explore on the left, Talk to us on the right, both on the masthead's edges, big caps in Statement Sans. A hovered or focused word turns over to its serif self in neon. Every link the old footer had is kept. Sub-pages open beside their word on hover or keyboard focus, on the right below 1700px wide and on the left above it, so they never reach the head; touch screens and stacked layouts list them under the word. Phone, email and address come from the site settings.
- **Running strip:** the six service families plus corporate training and the masterclass, passing behind the figure (decorative, `aria-hidden`). Words stand in for the reference's sponsor logos because Artificia may not imply partnerships.
- **Arrival:** neon floods up over the last section on the same terrain as the Solutions flood (`FLOOD`, now with its colours as parameters), with a moving contour edge and no crest. Then the frame closes in (panel scale .93 to 1), the robot rises (scrubbed), and once in view the line lifts in, the marker draws and the columns settle. Over the panel the masthead turns clear (`.is-over-foot`), and the WhatsApp square steps aside.
- **Stacked (below 1100px wide or 620px tall):** line, links, running strip, then the robot standing on the frame with its button, so the last screen is the figure and the action. Phones add no WebGL contexts: a drawn map, a resting neon arc and the plain-suit robot.
- **Fallbacks:** reduced motion, no WebGL and no JS all keep the full composition, still: the marker drawn, the strip stopped, a static arc in place of the flood.
