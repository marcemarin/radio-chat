---
name: Radio Chat
description: Broadcast intercom keypanel for a live radio producer; every message is a backlit key, every state a tally lamp.
colors:
  panel: "#1f2226"
  panel-deep: "#181b1f"
  key-face: "#2b3036"
  key-face-hover: "#333940"
  key-rim: "#1a1d21"
  edge: "#30353b"
  edge-soft: "#3a4047"
  strip: "#15181b"
  lamp-off: "#111417"
  lamp-off-rim: "#0a0c0e"
  text: "#e9e6df"
  text-engraved: "#c4c9cf"
  text-mute: "#9aa3ad"
  tally-green: "#35d07f"
  tally-amber: "#f2b632"
  tally-red: "#ff3b30"
  key-amber-hover: "#ffc648"
  key-amber-ink: "#1f1a0a"
  key-amber-rim: "#8a6a1e"
  key-red: "#d9332a"
  key-red-hover: "#e63b31"
  key-red-rim: "#8f221b"
  label-red-text: "#ff8a80"
  label-red-rim: "#5a2b27"
  label-amber-rim: "#5c4a1a"
  night-paper: "#f2f0ea"
  night-ink2: "#d6d2c8"
  night-mute: "#8f97a1"
  night-line: "#262c33"
  night-line2: "#3a424b"
  night-air: "#ff2d20"
  night-good: "#4ade80"
  night-warn: "#e0b060"
  night-ground: "#101418"
  night-panel: "#151a20"
  night-panel2: "#1b2128"
typography:
  display:
    fontFamily: "Instrument Serif, Georgia, serif"
    fontSize: "clamp(40px, 4.4vw, 64px)"
    fontWeight: 400
    lineHeight: 1.1
    letterSpacing: "-0.025em"
  headline:
    fontFamily: "Schibsted Grotesk, Helvetica Neue, system-ui, sans-serif"
    fontSize: "22px"
    fontWeight: 500
    lineHeight: 1.25
  title:
    fontFamily: "Schibsted Grotesk, Helvetica Neue, system-ui, sans-serif"
    fontSize: "17px"
    fontWeight: 500
    lineHeight: 1.4
  body:
    fontFamily: "Schibsted Grotesk, Helvetica Neue, system-ui, sans-serif"
    fontSize: "16px"
    fontWeight: 400
    lineHeight: 1.4
  body-compact:
    fontFamily: "Schibsted Grotesk, Helvetica Neue, system-ui, sans-serif"
    fontSize: "15px"
    fontWeight: 400
    lineHeight: 1.375
  control:
    fontFamily: "Schibsted Grotesk, Helvetica Neue, system-ui, sans-serif"
    fontSize: "14px"
    fontWeight: 600
    lineHeight: 1
  meta:
    fontFamily: "Schibsted Grotesk, Helvetica Neue, system-ui, sans-serif"
    fontSize: "13px"
    fontWeight: 400
    lineHeight: 1.4
  strip-name:
    fontFamily: "Schibsted Grotesk, Helvetica Neue, system-ui, sans-serif"
    fontSize: "12px"
    fontWeight: 400
    letterSpacing: "0.04em"
  label:
    fontFamily: "Schibsted Grotesk, Helvetica Neue, system-ui, sans-serif"
    fontSize: "11px"
    fontWeight: 400
    letterSpacing: "0.06em"
  section-caps:
    fontFamily: "Schibsted Grotesk, Helvetica Neue, system-ui, sans-serif"
    fontSize: "11px"
    fontWeight: 600
    letterSpacing: "0.14em"
rounded:
  label: "3px"
  strip: "4px"
  key: "6px"
  image: "6px"
  lamp: "50%"
spacing:
  xs: "4px"
  sm: "6px"
  md: "8px"
  lg: "12px"
  xl: "16px"
  gutter: "20px"
  row-y: "14px"
  bar: "40px"
  header: "56px"
  key-state: "56px"
  control-h: "44px"
  key-sm-h: "36px"
  aside-w: "400px"
components:
  key:
    backgroundColor: "{colors.key-face}"
    textColor: "{colors.text}"
    rounded: "{rounded.key}"
    typography: "{typography.control}"
    padding: "0 16px"
    height: "{spacing.control-h}"
  key-hover:
    backgroundColor: "{colors.key-face-hover}"
  key-disabled:
    backgroundColor: "{colors.key-face}"
    textColor: "{colors.text-mute}"
  key-channel:
    backgroundColor: "{colors.key-face}"
    textColor: "{colors.text}"
    rounded: "{rounded.key}"
    typography: "{typography.meta}"
    padding: "0 12px"
    height: "{spacing.key-sm-h}"
  key-lit:
    backgroundColor: "{colors.text}"
    textColor: "{colors.panel}"
    rounded: "{rounded.key}"
  key-amber:
    backgroundColor: "{colors.tally-amber}"
    textColor: "{colors.key-amber-ink}"
    rounded: "{rounded.key}"
    size: "44px"
  key-amber-hover:
    backgroundColor: "{colors.key-amber-hover}"
  key-red:
    backgroundColor: "{colors.key-red}"
    textColor: "#ffffff"
    rounded: "{rounded.key}"
    typography: "{typography.control}"
    padding: "0 16px"
    height: "{spacing.control-h}"
  key-red-hover:
    backgroundColor: "{colors.key-red-hover}"
  key-state:
    backgroundColor: "{colors.key-face}"
    rounded: "{rounded.key}"
    size: "{spacing.key-state}"
  strip:
    backgroundColor: "{colors.strip}"
    textColor: "{colors.text-engraved}"
    rounded: "{rounded.strip}"
    typography: "{typography.strip-name}"
    padding: "4px 10px"
  label:
    backgroundColor: "transparent"
    textColor: "{colors.text-mute}"
    rounded: "{rounded.label}"
    typography: "{typography.label}"
    padding: "2px 7px"
  label-red:
    textColor: "{colors.label-red-text}"
  label-amber:
    textColor: "{colors.tally-amber}"
  lamp:
    backgroundColor: "{colors.lamp-off}"
    rounded: "{rounded.lamp}"
    size: "10px"
  lamp-green:
    backgroundColor: "{colors.tally-green}"
  lamp-amber:
    backgroundColor: "{colors.tally-amber}"
  lamp-red:
    backgroundColor: "{colors.tally-red}"
  led-digits:
    textColor: "{colors.tally-green}"
    typography: "{typography.meta}"
  player-paper:
    backgroundColor: "{colors.text}"
    textColor: "{colors.panel}"
    rounded: "{rounded.lamp}"
    size: "32px"
  player-air:
    backgroundColor: "{colors.tally-red}"
    textColor: "#ffffff"
    rounded: "{rounded.lamp}"
    size: "44px"
---

# Design System: Radio Chat

## Overview

**Creative North Star: "The Intercom Keypanel"**

The producer's board is a studio intercom panel: a matte charcoal slab with rows of backlit keys, tally lamps and engraved scribble strips. Every listener message is a key you press to send it to the host; every state is a lamp. The panel is not a dashboard of cards and not an editorial page; it is hardware, read at arm's length in bursts while the show is on air. The world is recognizable with the content stripped out: rows of keys and lamps on a panel.

Color is rationed like on real broadcast gear. All text is achromatic (warm paper on charcoal, engraved grey for names, cool grey for meta); the only saturated color lives in lamps, lit key faces and 1px filets. Green means ready, amber means queued, red means on air, unlit means new, and a dimmed row means spam. Motion is limited to state changes (150 to 400 ms) and never to page choreography.

Two sibling themes share the same token names in `resources/css/app.css`: `.theme-panel` (this world, the producer board at `/`), `.theme-night` (the host screen at `/aire`, a darker studio-night look with a breathing red lamp and a serif display size), and `.theme-paper` (a legacy warm-paper theme, currently unused by any route). The panel is the house style; night is recorded as the sibling screen it is, not as a second house style.

**Key Characteristics:**
- Charcoal panel ground with a deeper host column; no white surfaces anywhere.
- Keys: 6px radius, 1px top highlight, offset blurred drop shadow, 1px press-down.
- Tally lamps in green / amber / red with a soft glow; unlit lamps are near-black wells.
- One workhorse grotesk (Schibsted Grotesk) for everything; tabular numerals for every counter.
- Labels are engraved: small tracked caps with a 1px dark text-shadow, never filled chips.
- State is text in the row ("listo", "en cola", "al aire", "transcribiendo…"), never a spinner.

## Colors

Achromatic panel materials with three tally colors that carry every meaning; the palette is charcoal, paper, and lamps.

### Primary
- **Tally Red** (`{colors.tally-red}`): the on-air lamp, its glow, the on-air section's top gradient, and the "air" Player. Means one thing: this message is on air right now.
- **Key Red** (`{colors.key-red}`): the face of the on-air action key ("Ya salió, siguiente", "Poner al aire") and the state key of the message on air. Darker than the lamp so white text passes contrast; hover lifts to `{colors.key-red-hover}`, rim `{colors.key-red-rim}`.

### Secondary
- **Tally Amber** (`{colors.tally-amber}`): queued. The amber lamp, the numbered queue keys, the "revisar" label text, text selection tint, and the keyboard focus ring. Amber key faces carry near-black ink (`{colors.key-amber-ink}`) and an olive rim (`{colors.key-amber-rim}`); hover brightens to `{colors.key-amber-hover}`.

### Tertiary
- **Tally Green** (`{colors.tally-green}`): ready. The green lamp, the LED-style counters (`led-digits`), the connection lamp, and the on-air-score fill inside the state key at 28% alpha.

### Neutral
- **Panel** (`{colors.panel}`): the board's ground.
- **Panel Deep** (`{colors.panel-deep}`): the header, the host column ("Al conductor") and the scrollbar track; one step cooler and darker so the aside reads as a separate module.
- **Key Face** (`{colors.key-face}`): resting key surface and scrollbar thumb; hover steps to `{colors.key-face-hover}`; keys are rimmed with `{colors.key-rim}`.
- **Edge** (`{colors.edge}`): every 1px divider and label border. `{colors.edge-soft}` exists as `--line2` but the panel ships no rule that uses it.
- **Scribble Strip** (`{colors.strip}`): the darkest surface; the recessed strip behind the program name and listener names.
- **Lamp Well** (`{colors.lamp-off}` with rim `{colors.lamp-off-rim}`): an unlit lamp.
- **Paper** (`{colors.text}`): primary text, the lit key face, and the "paper" Player disc.
- **Engraved** (`{colors.text-engraved}`): text on scribble strips.
- **Mute** (`{colors.text-mute}`): meta, timestamps, labels, state captions, empty-state prose, section caps.
- **Label Red** (`{colors.label-red-text}` on rim `{colors.label-red-rim}`) and **Label Amber** (`{colors.tally-amber}` on rim `{colors.label-amber-rim}`): moderation and "revisar" labels; colored text and filet only, no fill.

### Night theme (sibling screen, `/aire`)
`{colors.night-ground}` ground, `{colors.night-panel}` / `{colors.night-panel2}` panels, `{colors.night-paper}` text, `{colors.night-ink2}` secondary, `{colors.night-mute}` meta, `{colors.night-line}` / `{colors.night-line2}` rules, `{colors.night-air}` lamp, `{colors.night-good}` / `{colors.night-warn}` status text. Same variable names, different values; do not mix the two sets on one screen.

### Named Rules
**The Lamps-Only Rule.** Text is achromatic. Green, amber and red appear only in lamps, lit key faces, LED digits, 1px filets and the score fill. A colored headline or a tinted card background is out of world.
**The Two Reds Rule.** The lamp is `{colors.tally-red}`; the key face is `{colors.key-red}`. Never put white text on the lamp red, and never use the key red as a glow.
**The Meaning Rule.** Green = ready, amber = queued, red = on air, unlit = new or unclassified, 45% opacity row = spam. No color is decorative and no state borrows another state's color.

## Typography

**Display Font:** Instrument Serif (with Georgia) — night theme only, the on-air transcript on `/aire`.
**Body Font:** Schibsted Grotesk (with Helvetica Neue, system-ui) — everything on the panel.
**Label/Mono Font:** none; counters use Schibsted Grotesk with `font-variant-numeric: tabular-nums`.

Fonts load from fonts.bunny.net in `resources/views/sala.blade.php` (Schibsted Grotesk 400/500/600/700, Newsreader 400/500/600, Instrument Serif 400). Newsreader is the `--font-serif` fallback family and is not used by the panel.

**Character:** one plain grotesk doing every job, differentiated by size, weight, tracking and case rather than by family. Nothing on the panel is set larger than 22px; hierarchy comes from lamps and key faces, not from type scale.

### Hierarchy
- **Display** (400, `clamp(40px, 4.4vw, 64px)`, 1.1, tracking -0.025em): the on-air transcript on the night screen only. Never on the panel.
- **Headline** (500, 22px, 1.25): the on-air transcript in the host column; drops to 17px under 768px.
- **Title** (500, 17px, 1.4): the empty-state lead line ("Cuando lleguen mensajes, se encienden acá.").
- **Body** (400, 16px, 1.4): a message's transcript in its row, clamped to three lines until "Ver completo".
- **Body compact** (400, 15px, 1.375): queue item transcripts, empty-state prose.
- **Control** (600, 14px): text on action keys ("A la cola", "Ya salió, siguiente"). Secondary keys drop to weight 500. Channel keys use 13px/500.
- **Meta** (400, 13px): timestamps, message counts, "primera vez", "desde hace…", LED counters in the header.
- **Strip name** (400, 12px, tracking 0.04em): listener name and place on the scribble strip; the program name on the header strip is 13px/600 caps tracked 0.06em.
- **Label** (400, 11px, tracking 0.06em, uppercase): engraved classification labels and the state caption under the state key.
- **Section caps** (600, 11–12px, tracking 0.14em, uppercase): "Al aire", "Al conductor". "Ahora hablan de" uses the same weight at 0.06em.

### Named Rules
**The Tabular Rule.** Every number that can change (counts, queue positions, durations, clocks) is set `tabular-nums`. Counters that read as LEDs are green with a 6px green text-shadow; on a lit key they turn charcoal with no glow.
**The 22px Ceiling Rule.** The panel's largest type is the 22px on-air transcript. Emphasis is a lamp or a lit key, never bigger type.

## Layout

Full-viewport panel: `100vh` split into an auto-height header (56px on desktop) and a two-column body with `minmax(0,1fr)` for the message feed and a fixed 400px host column. Nothing scrolls except the feed and the queue; the on-air section stays in view.

- **Header (56px):** program scribble strip (min 200px) on the left, channel keys (36px tall, 6px gap, horizontal scroll with hidden scrollbar) in the middle, live lamp + count + "Pantalla de aire" key on the right; 20px horizontal padding, 24px column gap.
- **Topics bar (40px):** "Ahora hablan de" plus up to five topic labels with LED counts, 16px gap, 1px bottom edge.
- **Message row:** grid `56px | 1fr | auto`, 16px column gap, 20px horizontal / 14px vertical padding, 1px edge below. The state key sits in the 56px column, the action key hangs at the right edge, content sits between with 6px vertical rhythm.
- **Host column (400px):** deeper panel; on-air section with 20px padding (16px top, 20px bottom, 12px gap); "Al conductor" bar (40px); queue items on a `44px | 1fr` grid with 12px column gap and 14px vertical padding.
- **Spacing rhythm:** 4 / 6 / 8 / 12 / 16 / 20px; bars are 40px, controls 44px, the state key 56px, channel keys 36px.
- **Responsive (under 768px):** header becomes `1fr auto` with the channel keys wrapping to a second line and the "Pantalla de aire" key hidden; the body stacks with the host column first, capped at 50vh, its border moving from left to bottom; the queue collapses behind a "Ver cola / Ocultar" toggle; the on-air transcript drops to 17px and the "N mensajes" meta hides. Mobile is secondary; the producer uses a notebook.

## Elevation & Depth

Depth is physical, not tonal: a key is a raised cap with a 1px inner top highlight and an offset, blurred drop shadow; a strip is a recessed well with an inset top shadow; a lamp is a sunk well until lit, then a glowing source. Surfaces themselves are flat and stacked by value (strip < panel-deep < panel < key face). Lit elements glow outward with a colored blur, which is the only "elevation" that carries meaning.

### Shadow Vocabulary
- **Key at rest** (`box-shadow: inset 0 1px 0 rgba(255,255,255,.07), 0 2px 3px rgba(0,0,0,.45), 0 6px 14px -6px rgba(0,0,0,.55)`): every key.
- **Key hover** (`inset 0 1px 0 rgba(255,255,255,.1), 0 3px 5px rgba(0,0,0,.45), 0 8px 18px -6px rgba(0,0,0,.55)`): the key lifts slightly.
- **Key pressed** (`inset 0 1px 0 rgba(0,0,0,.4), 0 1px 1px rgba(0,0,0,.4)` with `translateY(1px)`): the cap sinks.
- **Key disabled** (`inset 0 1px 0 rgba(255,255,255,.03)`): shadow removed, cap reads flush with the panel.
- **Key lit** (`inset 0 1px 0 rgba(255,255,255,.6), 0 0 0 1px rgba(233,230,223,.15), 0 0 18px -2px rgba(233,230,223,.45)`): paper glow for the active channel.
- **Key amber** (`inset 0 1px 0 rgba(255,255,255,.45), 0 2px 3px rgba(0,0,0,.4), 0 0 16px -2px rgba(242,182,50,.55)`).
- **Key red** (`inset 0 1px 0 rgba(255,255,255,.35), 0 2px 3px rgba(0,0,0,.4), 0 0 18px -2px rgba(255,59,48,.6)`).
- **Strip** (`inset 0 1px 0 rgba(0,0,0,.6), 0 1px 0 rgba(255,255,255,.07)`): recessed scribble strip.
- **Lamp off** (`inset 0 1px 2px rgba(0,0,0,.9), 0 1px 0 rgba(255,255,255,.07)`): sunk well.
- **Lamp lit** (`0 0 8px 1px <tally at .6–.65>, inset 0 -1px 2px rgba(0,0,0,.3)`): green, amber or red.
- **Lamp glow** (`0 0 14px 3px rgba(255,59,48,.65), 0 0 36px 8px rgba(255,59,48,.25), inset 0 -1px 2px rgba(0,0,0,.3)`): the large on-air lamp only.
- **Engraved text** (`text-shadow: 0 1px 0 rgba(0,0,0,.6–.7)`): labels and strip text.

### Named Rules
**The Hardware Shadow Rule.** Shadows describe a physical cap or well (top highlight plus offset dark blur, or inset top shadow). No ambient card shadows, no floating panels.
**The Glow Is State Rule.** A colored glow appears only on a lit lamp or a lit key. Nothing glows at rest.

## Shapes

Small, consistent radii that read as machined parts: keys 6px, scribble strips 4px, labels 3px, lamps and play discs fully round, inline images 6px. Every key carries a 1px rim in a darker tone of its face (`{colors.key-rim}` for charcoal, `{colors.key-amber-rim}`, `{colors.key-red-rim}`). Dividers are 1px `{colors.edge}` lines, never spacing alone. The state key is a 56px square with `overflow: hidden` so the score fill is clipped to the cap; the queue key is a 44px square. There are no pill buttons, no rounded cards and no borderless floating surfaces on the panel. (The night theme's controls use 8px `rounded-lg`; that is its own vocabulary.)

## Components

### Key (button)
The panel's only button. A raised charcoal cap with engraved-feeling text; pressing it sinks 1px.
- **Shape:** 6px radius, 1px rim.
- **Default:** `{components.key}`; 44px tall, 16px horizontal padding, 14px/600 text for the primary action, 14px/500 for secondary actions ("Volver a la cola"). Channel keys in the header are 36px tall with 12px padding, 13px/500 text and a green LED count.
- **Hover:** face brightens to `{colors.key-face-hover}` and the shadow lifts (180ms `cubic-bezier(.2,.8,.2,1)`).
- **Active / pressed:** `translateY(1px)`, sunk shadow (150ms). "A la cola" additionally runs the 220ms `bloom` animation: the face flashes amber and a 10px amber ring expands and fades.
- **Disabled:** text turns mute, shadow removed, `cursor: not-allowed`. Used for spam rows.
- **Lit:** paper face with charcoal text and a soft paper glow; the active channel key.
- **Amber:** `{components.key-amber}`; the numbered queue keys (44px square, 15px/600 LED digits in charcoal). Pressing puts that item on air.
- **Red:** `{components.key-red}`; the single on-air control ("Ya salió, siguiente" / "Poner al aire el primero de la cola"). One red key per screen.
- **Focus:** 2px amber outline, 2px offset (all keys and links).

### Key State (with fill)
A 56px non-interactive key in the row's left column that shows the message's state and on-air score.
- **Fill:** an absolutely positioned green layer (`rgba(53,208,127,.28)` with a `.75` green top edge) scaled from the base by `scaleY(score/100)`, 400ms ease-out. The fill only shows for unqueued, non-spam messages (`ready` and `idle` states); once queued or on air the key face itself turns amber or red and the fill drops to zero.
- **Lamp:** a 12px lamp centered on the key; green when ready, amber when queued, red when on air, unlit otherwise.
- **Caption:** 11px tracked caps in mute under the key: "listo", "en cola", "al aire", "salió", "spam", or the pipeline status with an ellipsis ("transcribiendo…"). Reserved at `min-height: 1.2em` so rows do not shift.
- **Hover:** none; the state key reasserts its resting face and shadow.

### Lamp
A 10px round well (12px `lamp-sm` inside state keys, 14px `lamp-lg` in the on-air header).
- **Unlit:** `{colors.lamp-off}` with inset shadow.
- **Green / Amber / Red:** the tally color with an 8px glow.
- **Glow:** the on-air lamp adds a two-layer red halo (14px + 36px) and the section behind it gets a top-down red wash (`linear-gradient(to bottom, rgba(255,59,48,.08), transparent)`).
- **Arrival blink:** a row younger than 20s gets `row-arrived` for 2s and its lamp blinks twice (700ms, `steps(1, end)`, 2 iterations) between near-black and paper.

### Strip (scribble strip)
A recessed name plate. `{components.strip}`: `{colors.strip}` ground, 4px radius, inset top shadow, engraved `{colors.text-engraved}` text with a 1px dark text-shadow. Header variant: 13px/600 uppercase program name over 11px mute station name, min 200px. Row variant: 12px listener name and place, `min-width: 6rem`, truncated.

### Label (engraved)
A classification tag. `{components.label}`: 11px uppercase tracked 0.06em, mute text, 1px `{colors.edge}` border, 3px radius, 2px/7px padding, `white-space: nowrap`, engraved text-shadow. No fill. Tones: **red** (moderation flags, "error") uses `{colors.label-red-text}` text on `{colors.label-red-rim}`; **amber** ("revisar: …") uses `{colors.tally-amber}` text on `{colors.label-amber-rim}`. Labels sit in a 28px-min row after the Player, wrap as a group, and name the intent, the topic (when it does not repeat the transcript's opening), moderation, review, and failure.

### LED digits
Any live counter: `{colors.tally-green}` text with a 6px green text-shadow, `tabular-nums`. Inside a lit key it becomes charcoal with no shadow; inside an amber key it inherits the key's charcoal ink.

### Player
A round play disc with a duration readout (`resources/js/sala/Player.jsx`). Three tones: **paper** (paper disc, charcoal glyph; row Player at 32px), **air** (tally-red disc, white glyph; on-air Player at 44px), **ink** (ink disc, white glyph; night/paper themes). The glyph is an inline SVG play triangle or pause bars at 32% of the disc. Hover: `brightness(1.1)`. The readout is 14px mute `tabular-nums`, showing `m:ss` at rest and `elapsed / total` while playing. With `showBar` (night theme only) a 260px, 4px progress bar draws in `--line` with an `--ink` fill. When there is no media URL it degrades to the text "audio m:ss".

### Text links (tertiary actions)
"Ver completo", "Mal transcripto", "Sacar" are plain text in mute (11–13px) that turns paper on hover; no border, no underline, no key. They are the lowest tier and never carry a primary action.

### Navigation
The channel row is the only navigation: eight channel keys ("Todo", "Audios", "Reclamos", "Pedidos", "Concurso", "Consultas", "Saludos", "Opiniones") with LED counts; the active one is lit; `aria-pressed` marks state. Under 768px the row wraps below the strip and scrolls horizontally with a hidden scrollbar. A key-shaped link ("Pantalla de aire") leads to `/aire`.

### Browser surfaces
Selection is amber at 35% alpha with paper text. Scrollbars are 10px with a `{colors.panel-deep}` track and a `{colors.key-face}` thumb inset by a 2px panel-deep border, 6px radius; `.no-bar` hides the scrollbar on horizontal key rows. Focus is a 2px amber outline offset 2px on every button and link. `prefers-reduced-motion` removes key transitions, the fill transition, the bloom and the arrival blink (the night lamp's breathing too).

### Copy conventions
Rioplatense Spanish, sentence case, voseo ("Apretá", "Tocá"). Controls name their action and its consequence ("A la cola", "Ya salió, siguiente", "Poner al aire el primero de la cola", "Volver a la cola", "Sacar"). State captions are lowercase words, not badges. Empty states teach in one sentence ("Cuando lleguen mensajes, se encienden acá."). Uncertainty is printed as "revisar: …", never hidden. Pipeline progress is text with an ellipsis ("descargando el audio…", "transcribiendo…", "leyendo…").

## Do's and Don'ts

### Do:
- **Do** make every clickable thing a key (6px radius, 1px rim, top highlight, offset blurred shadow, 1px press) or a plain mute text link; nothing in between.
- **Do** keep text achromatic (`{colors.text}`, `{colors.text-engraved}`, `{colors.text-mute}`) and put meaning in lamps, lit key faces and 1px filets.
- **Do** use `{colors.key-red}` for a red key face and `{colors.tally-red}` for the lamp and its glow.
- **Do** set every changing number in `tabular-nums`, green with a 6px glow when it reads as an LED.
- **Do** print state as lowercase text in the row ("en cola", "transcribiendo…") and keep the caption slot reserved so rows do not jump.
- **Do** limit motion to state changes at 150–400 ms with `cubic-bezier(.2,.8,.2,1)`, and honor `prefers-reduced-motion`.
- **Do** keep one red key per screen: the single on-air control.
- **Do** write controls as the action they perform, in Rioplatense sentence case.

### Don't:
- **Don't** introduce white or light surfaces; the lightest ground is `{colors.panel}` and the only light face is a lit key.
- **Don't** use rounded cards, pill buttons, ambient card shadows or floating panels on the board; depth is caps and wells only.
- **Don't** fill a label; labels are 1px filets with engraved text, and their color tones are red and amber only.
- **Don't** put white text on `{colors.tally-red}` or use a serif on the panel; Instrument Serif belongs to the night screen.
- **Don't** show spinners or progress chrome for pipeline states; the row says what is happening.
- **Don't** let a glow appear at rest or on hover; glow means a lit lamp or a lit key.
- **Don't** set panel type above 22px; the display size lives on `/aire`.
- **Don't** mix `.theme-night` values into `.theme-panel` or vice versa; each screen wears one theme.
