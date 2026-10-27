---
name: Artificia AI, Lime Light on Black Glass
description: A near-black page lit by two slow lime fields, with liquid-glass panes in front and a lime pen that draws, ticks and underlines.
colors:
  black: "#030304"
  black-1: "#0a0b0d"
  black-2: "#111316"
  black-3: "#191b1e"
  hairline: "#24272b"
  rule: "#3b3f44"
  secondary-text: "#9ba2a1"
  lede-text: "#c8cdca"
  ink: "#F2F3EE"
  lime: "#AED534"
  lime-hi: "#c3e651"
  lime-ink: "#08080d"
  warn: "#ff9a85"
typography:
  display:
    fontFamily: "Roboto Flex, Roboto, system-ui, sans-serif"
    fontSize: "clamp(2.15rem, 1.15rem + 4.3vw, 5.5rem)"
    fontWeight: 820
    lineHeight: 1.02
    letterSpacing: "-0.012em"
  headline:
    fontFamily: "Roboto Flex, Roboto, system-ui, sans-serif"
    fontSize: "clamp(1.7rem, 1.05rem + 2.75vw, 3.4rem)"
    fontWeight: 790
    lineHeight: 1.04
    letterSpacing: "-0.008em"
  title:
    fontFamily: "Roboto Flex, Roboto, system-ui, sans-serif"
    fontSize: "1.25rem"
    fontWeight: 720
    lineHeight: 1.25
  body:
    fontFamily: "Roboto Flex, Roboto, system-ui, sans-serif"
    fontSize: "1.0625rem"
    fontWeight: 400
    lineHeight: 1.6
  label:
    fontFamily: "Roboto Flex, Roboto, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 650
    lineHeight: 1.35
    letterSpacing: "0.09em"
rounded:
  none: "0"
spacing:
  gutter: "10px"
  gutter-tablet: "16px"
  gutter-desktop: "20px"
  section: "clamp(4rem, 2.75rem + 5.5vw, 8.5rem)"
  max-width: "1720px"
  inset-left: "44px"
  inset-right: "16px"
components:
  button-primary:
    backgroundColor: "{colors.lime}"
    textColor: "{colors.lime-ink}"
    rounded: "{rounded.none}"
    height: "52px"
    padding: "0 21.6px"
  button-primary-hover:
    backgroundColor: "{colors.lime-hi}"
  button-glass:
    backgroundColor: "rgba(255,255,255,.06)"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
    height: "52px"
  input:
    backgroundColor: "rgba(255,255,255,.05)"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
    height: "50px"
  glass-pane:
    rounded: "{rounded.none}"
---

# Design System: Artificia AI, Lime Light on Black Glass

## Overview

**Creative North Star: "Lime Light on Black Glass"**

The page is near-black (#030304). Behind it two soft lime light fields drift slowly (40s and 56s, alternate), and in front of them sit liquid-glass panes: a translucent white gradient, a bright top edge, a diagonal sheen. Over that, a single lime pen still draws sketches, ticks headings and underlines emphasis. The graph-paper grid and the margin rule of the earlier charcoal notebook are gone; the sketch, the tick and the entry-line habits of that notebook remain.

Type is wide, heavy, uppercase Roboto Flex for headings and plain Roboto Flex for reading. Content is organised as entries: a title, then a metadata line under it. Structure now comes from panes and 1px glass hairlines instead of ruled lines.

The build is recorded as shipped, including where it bends its own rules (see Colors and Do's and Don'ts).

**Key Characteristics:**
- One black ramp plus ink; lime is the only chroma, used as marker and as ambient light.
- Translucent, square-cornered glass panes with a lit top edge; buttons, inputs and photos are square too.
- Wide uppercase display type; lime single-stroke drawings that take the form of the question they answer.
- Motion is drawing: strokes, ticks and rails write themselves in once; nothing animates under reduced motion.

## Colors

Near-black ground, off-white ink, one lime. The brand lime #AED534 is a PRODUCT.md commitment.

### Primary
- **Marker Lime** (#AED534): sketch strokes, heading ticks, list ticks, the h2 emphasis underline, the h1's second clause, the primary action, focus rings, selection, caret, step rail, hover colour of links and titles.
- **Marker Lime, Under the Cursor** (#c3e651): hover of the primary action only.
- **Lime Ink** (#08080d): text on lime fills. Off the ramp (see Named Rules).

### Neutral (one black ramp, --c0 to --c7)
- **Black** (#030304, --c0): the page, scrollbar track.
- **Black 1** (#0a0b0d, --c1): step number fill, blank video poster.
- **Black 2** (#111316, --c2): solid fallback for every pane under reduced transparency; video mat.
- **Black 3** (#191b1e, --c3): defined, held in reserve.
- **Hairline** (#24272b, --c4): defined, held in reserve.
- **Rule** (#3b3f44, --c5): entry-line dividers, ruler ticks, step rail, scrollbar thumb.
- **Secondary Text** (#9ba2a1, --c6): metadata, notes, placeholders, footer heads.
- **Lede Text** (#c8cdca, --c7): ledes, supporting copy, dashed sketch guides.
- **Ink** (#F2F3EE): headings, body.
- **Warn** (#ff9a85): form errors only.

### Glass (translucent white over the ground, not ramp steps)
- Pane fill: `linear-gradient(160deg, rgba(255,255,255,.09), .025 at 55%, .045)`.
- Line `rgba(255,255,255,.12)`; bright top edge `rgba(255,255,255,.24)`.
- Floating header tint `rgba(20,22,25,.62)` to `rgba(10,11,13,.5)`; mobile menu `rgba(8,9,11,.82)`.

### Named Rules
**The Marker Rule.** Lime marks: strokes, ticks, underlines, the primary action, focus. It never fills a panel or colours body text. The one exception is light, not paint: the two body light fields and the soft lime glows pooled behind the hero sketch and the call to action (alpha .14-.22) are ambient and sit behind glass.
**The Ramp Rule.** Any solid grey is a ramp step. Translucent white is the glass material, not a grey. A new solid grey is a new ramp step, added once.
**The Warn Rule.** Warn appears only on invalid fields and form status errors.

## Typography

**Display, Body and Label Font:** Roboto Flex (self-hosted variable woff2, weight 100-1000, width 25-151%), falling back to Roboto then system sans. The width axis supplies the wide caps.

**Character:** A single family. Contrast comes from width (85% labels, 100% text, 110-125% display) and weight (400 text, 650-820 headings).

### Hierarchy
- **Display / h1** (820, stretch 125%, clamp 2.15-5.5rem, 1.02, uppercase, max 15ch): home heading; page variant 2-4.5rem, 18ch; split hero 2.75-4.4rem. The second clause is lime via em.
- **Headline / h2** (790, stretch 118%, clamp 1.7-3.4rem, 1.04, uppercase, max 24ch; 18ch in the CTA at 2-4.25rem): section headings. The emphasised word gets a lime underline (0.1em), not colour.
- **Title / h3** (720, 1.25rem, 1.25): item titles; in drawn cards 790 uppercase stretch 112%.
- **Body** (400, 1.0625rem, 1.6): prose capped at 66ch; ledes 1.0625-1.25rem in Lede Text, 58ch.
- **Label / entry** (650, 0.75rem, stretch 85%, tracking 0.09em, uppercase, tabular numerals): the entry line, footer heads, ruler labels, contact terms, hero foot.
- **Buttons** (760, 0.875rem, stretch 112%, tracking 0.05em, uppercase).

### Named Rules
**The Under Rule.** Entry and metadata lines sit under their title, never above. No eyebrow or kicker above any heading. The hero's `eyebrow` field renders as the foot line (.hero__foot) beneath the door chooser.
**The Wide Caps Rule.** Uppercase is for headings, buttons and labels; reading text stays sentence case.

## Layout

Full width. `.wrap` is capped at 1720px; panes run edge to edge inside a gutter of 10px (16px from 720px, 20px from 1024px). Content inside a pane is inset 44px left on mobile, 64px at 720px and clamp(80px, 6vw, 120px) at 1024px (right 16px, 32px, clamp(40px, 4vw, 88px)); the left inset leaves room for the heading tick, which sits 29px left of the heading. Sections have vertical rhythm clamp(4rem, 2.75rem + 5.5vw, 8.5rem). There is no grid ruling and no margin rule.

The header floats (fixed, gutter from top and sides, 60px high, 66px from 1024px). The hero is one pane at least `100svh - bar - 3 gutters` high. Breakpoints in use: 480, 640, 720, 900, 1024. Card and stat grids are 2, 3 or 4 up with the last row stretching so no cell is empty. Split layouts use 5/7, 5/6 or 4/8 ratios with a sticky heading on desktop (FAQ, steps, case study). The hero on mobile reorders to heading, sub, drawing, doors, foot. Under 1024px the nav becomes a full-screen glass overlay.

## Elevation & Depth

Depth is glass over light over black. Every pane carries the same stack: `inset 0 1px 0 rgba(255,255,255,.24)` (bright top edge), `inset 0 -1px 0 rgba(255,255,255,.03)` and the drop `0 30px 80px -40px rgba(0,0,0,.9)`, plus a diagonal sheen from a `::before` (118deg white wedge .09 to transparent at 40%, and a soft corner highlight). The floating WhatsApp button is lime with `0 12px 30px rgba(0,0,0,.55)` and a `.45` white top edge.

Backdrop blur (`blur(22px) saturate(165%)`) is applied only where content scrolls behind a pane: the floating header, the hero pane, the form plate and the CTA. The mobile menu and the video play chip also blur (22px and 12px). All other panes rely on tint alone, which keeps phones cheap.

### Named Rules
**The Frosting Rule.** Blur is for panes that content passes behind. A static card gets the gradient, edge and sheen, never a backdrop filter.
**The Solid Fallback Rule.** Under `prefers-reduced-transparency: reduce` every pane, the nav overlay and the play chip become solid Black 2 (#111316) with no blur.

## Shapes

Square and sharp. Every radius token is 0 (--r, --r-lg, --r-pill): glass panes, the floating header, buttons, inputs, checkboxes, photos, video mats, step numbers, avatars, the scrollbar thumb and the WhatsApp button all have square corners. Softness comes from the glass gradient and sheen, not from curves. Borders are 1px glass line on panes, 1px on buttons and inputs (rgba white .16-.24), 2px for focus, the lime step-number ring and the step rail; lime strokes are 2.25px in drawings. The checkbox tick is an SVG background.

### Named Rules
**The Square Rule.** No rounded corners anywhere. New components use the radius tokens (all 0) or none, never a literal radius.

## Components

### Buttons
- **Shape:** square, 52px high (42px in the header), uppercase wide label with a Phosphor regular arrow.
- **Primary (lime):** lime fill, Lime Ink text; the only filled lime control. Hover to lime-hi.
- **Glass (default):** `rgba(255,255,255,.06)` fill, 1px `rgba(255,255,255,.24)` border; hover turns border and text lime, arrow nudges 3px. Active presses 1px.
- **Disabled:** 55% opacity.

### Glass pane
The shared container for cards, steps track, parts list, curriculum tracks, stat cells, testimonials, FAQ list, form plate, people, contact list, map, post list, footer panel and image frames. 1px glass line, square corners, the shadow stack and sheen above, the glass gradient fill.

### Door chooser (hero)
Rows between 1px hairlines (`rgba(242,243,238,.28)`): large uppercase label, small note beneath, lime arrow. Hover draws a lime underline across the label and moves the arrow 6px.

### Cards (index)
Glass tiles in 2, 3 or 4 columns with a lime icon or lime drawing, title, text and a whole-tile link with a corner arrow. Hover lifts 3px, border turns lime at .5 alpha and the title turns lime. Photo cards put the print at the top of the pane. The drawn variant is one per row, drawing 5fr and text 7fr.

### Drawings
Lime single-stroke SVG (2.25px, round caps), dashed Lede Text guides, `rgba(0,0,0,.45)` box fills, ink 13px labels and Secondary Text 11.5px notes. Each takes the form of its question: day line (masterclass), department map (corporate), lead flow (services), documents to agent (RAG). Strokes write in on scroll.

### Stats (dimensions)
Glass cells: value centred between two lime dimension rules with extension ticks and arrowheads, label beneath. The caveat "Typical outcomes for AI-enabled teams." stays with the stats.

### Curriculum
A ruler of ticks with a lime line in segments and a hover highlight band (lime .22 fill, 1px lime edge); glass tracks of modules whose entry line sits under the module title.

### Steps, case study, testimonials
Steps run on a vertical rail inside a glass track that fills lime on scroll, with 32px square numbered markers. The case study uses glass flow nodes joined by lime arrows. Testimonials are glass entries: quote, then a hairline and signature; video thumbnails (4:5) load the iframe only on click.

### Inputs and form plate
Fill `rgba(255,255,255,.05)`, 1px `rgba(255,255,255,.16)` border, square, 50px high; hover border .32; focus lime border plus 1px lime ring; invalid border Warn. Checkboxes 20px, lime when checked. The plate is a blurred glass pane; on success the form gives way to a "logline" confirmation.

### Navigation and footer
Floating blurred glass bar with the real Artificia logo image (never a text wordmark; 32px high, 38px at 1024px). Links 0.9375rem, active or hover shows a 2px lime underline. The primary Enquire button sits on the right. Under 1024px the nav becomes a full-screen glass overlay with 1.5rem uppercase links and the toggle collapses to an icon under 480px. The footer is one glass panel with label-style column heads. A floating lime WhatsApp square hides when the form is in view.

### Map
A line-drawn facade; the map iframe loads only on click.

### Motion
GSAP 3.13 and ScrollTrigger, run once per element, only when the head script promised it (`.motion`, withdrawn if GSAP fails). Grammar: strokes draw (dashoffset, 0.5-0.8s power2), rules scale in, lists lift 12-16px and fade with 0.06-0.08s stagger, stat values count up, the step rail fills with scroll. CSS easing `cubic-bezier(.16, 1, .3, 1)` at 0.2-0.45s (photos 0.8s). The two light fields drift on CSS keyframes. Under prefers-reduced-motion no `.motion` class is set and a global rule collapses animation and transition durations to .01ms with one iteration, so the light fields stand still.

## Do's and Don'ts

### Do:
- **Do** keep every corner square (radius tokens are 0).
- **Do** put the real logo image top-left in the floating glass bar; use Roboto Flex throughout.
- **Do** use lime only as marker: strokes, ticks, h2 emphasis underline, the h1's second clause, the primary action, focus.
- **Do** keep solid greys on the ramp (--c0 to --c7, ink); build panes from the glass tokens (--glass, --glass-line, --glass-edge, --glass-shadow).
- **Do** blur only panes that content scrolls behind (header, hero, form plate, CTA).
- **Do** provide the solid Black 2 fallback for any new pane under prefers-reduced-transparency.
- **Do** draw a diagram in the form of the question it answers.
- **Do** put entry and metadata lines under titles.
- **Do** keep videos and the map behind a click.
- **Do** keep the "Typical outcomes for AI-enabled teams." caveat with any stats.

### Don't:
- **Don't** put an eyebrow or kicker above any heading.
- **Don't** round any corner: no radius, no pills.
- **Don't** use hard offset shadows, glyph icons (use Phosphor regular via icon()) or any other display face.
- **Don't** use lime for body text or panel fills; its only non-marker use is the ambient light behind glass.
- **Don't** animate under prefers-reduced-motion.
- **Don't** use Warn outside form errors.
- **Don't** reintroduce the graph-paper grid or the margin rule.
