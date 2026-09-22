---
version: 1
slug: "resources-js-sala-redaccion-jsx"
primary_target: "resources/js/sala/Redaccion.jsx"
related_targets: ["resources/css/app.css"]
---

# Redacción — pantalla del productor (`/`, resources/js/sala/Redaccion.jsx)

Scope: the producer's live board. Mode: Operate. Audience: the show's producer on a 13–15" notebook next to the studio, normal light, arm's length, reading in bursts while the show is on air. Task: find the messages worth airing within seconds of arrival, queue them for the host, keep one on air. Content: real listener messages (voice notes with transcripts, text, images), classification (intent, on-air score, moderation, topic), queue states. Constraints: it is live software, not a document; not white; audio first; uncertainty shown as "revisar"; no brand; the host screen (`/aire`) stays as is.

Memorable moment: pressing a message's key lights it amber — the message is now "en cola" — with a short bloom, exactly like keying a talk button on an intercom panel.

Unresolved: per-station theming; mobile is secondary (producer uses a notebook).

## Direction contract

THESIS: The producer's board is a broadcast intercom keypanel: every message is a backlit key you press to send it to the host, and every state is a tally lamp — unlit new, green ready, amber queued, red on air, dim spam. It refuses the dark dashboard of rounded cards and the editorial page of serif text.

OWN-WORLD: Matte charcoal panel ground (#1f2226) with a cooler panel layer for the host column (#181b1f); key faces (#2b3036) with a 1px top highlight and an offset, blurred drop shadow; tally LEDs green #35d07f, amber #f2b632, red #ff3b30 with a soft glow; engraved labels in small tracked caps on scribble strips (#15181b); one workhorse sans (Schibsted Grotesk) for everything, tabular numerals for counters; all text achromatic (#e9e6df / #9aa3ad) — color lives only in lamps, key faces and 1px filets. Recognizable with the content removed: rows of keys and lamps on a panel.

STORY: The producer sees new keys light up as messages arrive and fill in, reads the transcript in a glance, sees which ones the system rates ready (green) and which need review (amber "revisar"), presses a key to queue it, and sees the red tally move when the host takes it. State is always visible; nothing needs training.

FIRST VIEWPORT (1440×900): A full-width charcoal panel. Top strip (56px): the program's scribble strip on the left, then the channel keys — Todo, Audios, Reclamos, Pedidos, Concurso, Consultas, Saludos, Opiniones — each a key with a tabular LED count, the active one lit; right end: the live lamp and message count. Below, two columns: left (~2/3) the message rows, each 84px+: a 56px state key with the on-air score as a green fill rising from its base, a scribble strip with name and place, the transcript at 16px in one to three lines, the play control for voice notes, then the classification as engraved labels; the primary action "A la cola" is a key at the row's right edge. Right column (400px) "Al conductor": the on-air message under a glowing red tally lamp with its transcript at 22px and "Ya salió, siguiente", then the queue as numbered amber keys. Empty states teach: "Cuando lleguen mensajes, se encienden acá."

FORM: Broadcast intercom keypanel — candidate 3 of 7 on the grounded list (rundown grid, ATC strip board, intercom keypanel, mixing console, lower thirds, wire terminal, teletext); seed key c83781ac; assigned by the roll, taken by the user. Raises kept from declined challengers: achromatic text with color only in lamps (cloud edge); states printed as text in the row, never chrome spinners (phosphor terminal); the on-air score as a severity fill inside the key (crisis wall). Signature interaction: key press → amber bloom (180 ms ease-out) and the row's state text flips to "en cola"; new message → its lamp blinks twice. Motion grammar: state changes only, 150–200 ms, no page choreography.

FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance.
