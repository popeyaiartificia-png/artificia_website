---
name: Artificia AI, The Build Notebook
description: A charcoal graph-paper notebook in which a lime pen records how Artificia builds AI for a business.
colors:
  binding: "#08080d"
  charcoal-1: "#121416"
  mat: "#1b1e1f"
  page: "#2A2E2E"
  grid-ruling: "#343939"
  rule: "#4b5252"
  secondary-text: "#9ba2a1"
  lede-text: "#c8cdca"
  ink: "#F2F3EE"
  lime: "#AED534"
  lime-hi: "#c3e651"
  lime-ink: "#08080d"
  margin-rule: "#56652a"
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
  base: "2px"
spacing:
  grid: "18px"
  section: "clamp(4rem, 2.75rem + 5.5vw, 8.5rem)"
  inset-left: "104px"
  inset-right: "48px"
components:
  button-primary:
    backgroundColor: "{colors.lime}"
    textColor: "{colors.lime-ink}"
    rounded: "{rounded.base}"
    height: "52px"
    padding: "0 21.6px"
  button-primary-hover:
    backgroundColor: "{colors.lime-hi}"
  button-ghost:
    backgroundColor: "transparent"
    textColor: "{colors.ink}"
    rounded: "{rounded.base}"
    height: "52px"
  input:
    backgroundColor: "{colors.mat}"
    textColor: "{colors.ink}"
    rounded: "{rounded.base}"
    height: "50px"
  form-plate:
    backgroundColor: "{colors.binding}"
    rounded: "{rounded.base}"
---

# Design System: Artificia AI, The Build Notebook

## Overview

**Creative North Star: "The Build Notebook"**

The site is the working notebook of a team that builds AI: charcoal graph paper on an 18px ruling, a thin lime margin rule down the left, and a single lime pen that draws, ticks and underlines. Black "binding" bars (nav, footer, CTA band, form plate) hold the pages together. Everything is flat, ruled and annotated rather than carded and shadowed.

Type is wide, heavy, uppercase Roboto Flex for headings and plain Roboto Flex for reading. Structure comes from rules (1.5px ink for the opening rule of a list, 1px grey for dividers), not boxes. Information is presented as notebook entries: a title, then a metadata line under it.

Where the build departs from intent: three soft ambient shadows exist (see Elevation). The build is recorded as shipped.

**Key Characteristics:**
- One charcoal ramp plus ink; lime is the only chroma.
- Wide uppercase display type, 2px corners, ruled lists instead of cards.
- Lime single-stroke drawings that take the form of the question they answer.
- Motion is drawing: strokes, rules and ticks write themselves in once.

## Colors

Near-black charcoal paper, off-white ink, one lime pen. The brand lime #AED534 on charcoal is a PRODUCT.md commitment.

### Primary
- **Marker Lime** (#AED534): the pen. Sketch strokes, completion ticks, list ticks, the h2 emphasis underline, the h1's second clause, the primary action, focus rings, selection, the caret.
- **Marker Lime, Under the Cursor** (#c3e651): hover of the primary action only.
- **Lime Ink** (#08080d): text on lime fills.

### Neutral (one ramp, every grey is a step)
- **Binding** (#08080d): nav, footer, CTA band, form plate.
- **Charcoal 1** (#121416): gallery nav only.
- **Mat** (#1b1e1f): inputs, image frames, map and video mats.
- **Page** (#2A2E2E): the sheet.
- **Grid Ruling** (#343939): the 18px graph lines, dividers on binding.
- **Rule** (#4b5252): section borders, list dividers, input borders.
- **Secondary Text** (#9ba2a1): metadata, notes, placeholders (5.2:1 on the page).
- **Lede Text** (#c8cdca): ledes and supporting copy.
- **Ink** (#F2F3EE): headings, body, ruled-list opening rules.
- **Margin Rule** (#56652a): the dim lime line down the sheet's left edge.
- **Warn** (#ff9a85): form errors only.

### Named Rules
**The Marker Rule.** Lime marks; it never fills surfaces, backs panels or colours body text. Its jobs are the list above and nothing else.
**The Ramp Rule.** Any grey is a ramp step. A new grey is a new ramp step, added once, or it is not used.
**The Warn Rule.** Warn appears only on invalid fields and form status errors.

## Typography

**Display, Body and Label Font:** Roboto Flex (self-hosted variable woff2, weight 100-1000, width 25-151%), falling back to Roboto then system sans. Roboto per the Brand Bible; the width axis supplies the wide caps.

**Character:** A single family. Contrast comes from width (stretch 85% for labels, 100% for text, 110-125% for display) and weight (400 text, 650-820 headings).

### Hierarchy
- **Display / h1** (820, stretch 125%, clamp 2.15-5.5rem, 1.02, uppercase, max 15ch): home heading. Page variant 2-4.5rem, 18ch; split hero 2.75-4.4rem. The second clause is lime via em.
- **Headline / h2** (790, stretch 118%, clamp 1.7-3.4rem, 1.04, uppercase, max 20ch): section headings. The emphasised word (written *word* in content) gets a lime underline, 0.1em thick, not colour.
- **Title / h3** (720, 1.25rem, 1.25): item titles; in drawn indexes 790 uppercase stretch 112%.
- **Body** (400, 1.0625rem, 1.6): prose capped at 66ch; ledes 1.0625-1.25rem in Lede Text, 58ch.
- **Label / entry** (650, 0.75rem, stretch 85%, tracking 0.09em, uppercase, tabular numerals): the entry line (module length, case metadata, post date), foot headings, ruler labels, contact terms.
- **Buttons** (760, 0.875rem, stretch 112%, tracking 0.05em, uppercase).

### Named Rules
**The Under Rule.** Entry and metadata lines sit under their title, never above it. No eyebrow or kicker above any heading. The hero's `eyebrow` field renders as the page foot beneath the door chooser.
**The Wide Caps Rule.** Uppercase is for headings, buttons and labels; reading text stays sentence case.

## Layout

A notebook sheet: content column max 1280px, inset 44px left on mobile, 72px at 720px, 104px at 1024px (right 16/32/48px), leaving room for the margin rule drawn 18px left of content. The graph ruling is 18px on `.sheet`. Sections have a vertical rhythm of clamp(4rem, 2.75rem + 5.5vw, 8.5rem) and are divided by a 1px Rule line. Sticky bar 64px (72px at 1024px).

Breakpoints in use: 480, 640, 720, 900, 1024. Lists are ruled and columnar (2, 3 or 4 up at 1024px) with the last row stretching so no cell is ever empty. Split layouts use 5/7 or 5/6 column ratios, with the heading sticky on desktop (FAQ, steps, case study). Hero on mobile reorders to heading, sub, drawing, doors, foot.

Each section's heading carries a completion tick sitting on the margin rule.

## Elevation & Depth

Flat by default; depth is paper over binding (Page on Binding) and ruled edges. Three soft ambient drop shadows exist in the build and are the only ones: image frames (0 14px 40px rgba(0,0,0,.35)), the form plate (0 18px 50px rgba(0,0,0,.35)) and the floating WhatsApp button (0 10px 28px rgba(0,0,0,.5)). Hard offset shadows are not part of this world.

### Named Rules
**The Ruled Not Raised Rule.** Hierarchy is made with rules and ink, not shadows. A new shadow needs the same justification as those three: a surface that physically lies over the page.

## Shapes

Corners are 2px everywhere (--r), including buttons, inputs, checkboxes, frames, step numbers and video mats; there is no pill or large radius. Borders are 1px (dividers, frames), 1.5px (buttons, inputs, flow nodes, ink opening rules) or 2px (focus, step rail, lime strokes at 2.25px in drawings). Sketch boxes are drawn with single strokes, not filled shapes.

## Components

### Buttons
- **Shape:** 2px, 52px high (42px in the nav), uppercase wide label with a Phosphor regular arrow.
- **Primary:** lime fill, Lime Ink text; the only filled lime control. Hover to lime-hi.
- **Ghost:** 1.5px ink border, transparent; hover turns border and text lime, arrow nudges 3px.
- **Disabled:** 55% opacity.

### Door chooser (hero)
Ruled rows with a 1.5px ink rule above and below the set: large uppercase label, small note beneath, lime arrow. Hover draws a lime underline across the label and moves the arrow 6px.

### Ruled index (cards)
Not cards: columns divided by 1px Rule lines under a 1.5px ink opening rule, with a lime icon or lime drawing, title, text, and a whole-item link with a small corner arrow. The drawn variant is two columns (drawing 5fr, text 7fr).

### Drawings
Lime single-stroke SVG (2.25px, round caps), dashed Lede Text guide lines, Page-filled boxes, ink 13px labels and Secondary Text 11.5px notes. Each takes the form of its question: day line (masterclass), department map (corporate), lead flow (services), documents to agent (RAG). Drawing strokes write in on scroll.

### Stats (dimensions)
Draftsman dimension lines: value centred between two lime rules with extension ticks and arrowheads, label beneath. The caveat "Typical outcomes for AI-enabled teams." stays with the stats.

### Curriculum
A ruler of ticks with a lime line in segments and a hover highlight band; tracks of modules whose entry line sits under the module title.

### Steps, case study, testimonials
Steps run on a vertical rail that fills lime on scroll with 32px square numbered boxes. Case study uses bordered flow nodes joined by lime arrows. Testimonials are witnessed entries: quote, then a 1.5px ink rule and signature; video thumbnails (4:5) load the iframe only on click.

### Inputs and form plate
Mat fill, 1.5px Rule border, 2px corners, 50px high; hover border Secondary Text; focus lime border plus 1px lime ring; invalid border Warn. Checkboxes are 20px, lime when checked. The form sits on a Binding plate; on success it gives way to a "logline" confirmation.

### Navigation and binding
Sticky Binding bar with the real Artificia logo image (never a text wordmark; 34px high, 40px at 1024px, as supplied). Links 0.9375rem, active or hover shows a 2px lime underline. Under 1024px the nav becomes a full-screen Binding overlay with 1.5rem uppercase links; the toggle collapses to an icon under 480px. Footer is Binding with label-style column heads. A floating lime WhatsApp square sits bottom right and hides when the form is in view.

### Map
A line-drawn facade; the map iframe loads only on click.

### Motion
GSAP 3.13 and ScrollTrigger, run once per element, only when the head script promised it. Grammar: strokes draw (dashoffset, 0.5-0.8s power2), rules scale in from their origin, lists lift 12-16px and fade with 0.06-0.08s stagger, stat values count up, the step rail fills with scroll. Easing token `cubic-bezier(.16, 1, .3, 1)` for CSS transitions (0.2-0.45s). Under prefers-reduced-motion nothing animates: pre-states exist only under `.motion`, and a global rule collapses transition and animation durations.

## Do's and Don'ts

### Do:
- **Do** put the real logo image top-left on Binding; use Roboto Flex throughout.
- **Do** use lime only as marker: strokes, ticks, h2 emphasis underline, the h1's second clause, the primary action, focus.
- **Do** make every grey a ramp step (--c0 to --c7, ink).
- **Do** draw a diagram in the form of the question it answers.
- **Do** put entry and metadata lines under titles.
- **Do** keep videos and the map behind a click.
- **Do** keep the "Typical outcomes for AI-enabled teams." caveat with any stats.

### Don't:
- **Don't** put an eyebrow or kicker above any heading.
- **Don't** use a radius above 2px, cards with boxed borders and shadows, hard offset shadows, glyph icons (use Phosphor regular via icon()) or any other display face.
- **Don't** use lime for body text, panel fills or decoration without meaning.
- **Don't** animate under prefers-reduced-motion.
- **Don't** use Warn outside form errors.
