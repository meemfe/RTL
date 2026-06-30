# RTL

A PocketMine-MP plugin that corrects reversed RTL text in Minecraft: Bedrock Edition.

Bedrock renders Right-To-Left scripts with their characters reversed.

This plugin works around it by pre-reversing the text on the server.

## What it fixes

**Chat UI and signs only.**

- Chat messages (player chat, `/say`, `/tell`, tips, popups, system messages, announcements)
- Sign text edited by players

Form UIs, name tags, boss bars, books, scoreboards, titles, and toasts are not handled.

## How it works

Hooks into `DataPacketSendEvent` (chat, tips, popups, system messages) and `SignChangeEvent` (signs). For each text containing RTL characters:

1. Finds all RTL segments
2. Reverses characters in each segment (cancels Bedrock's char reversal)
3. Reverses their order (cancels Bedrock's swap around LTR words)

Non-RTL text is untouched.

## Supported scripts

Arabic (incl. Kurdish, Pashto, Persian, Sindhi, Urdu), Hebrew (incl. Yiddish), Syriac, Thaana, NKo, Samaritan, Mandaic.

## Limitations
- **Chat input box** — text appears reversed while typing. Server can't access the input box.
- **Hindi/Thai/Tibetan** — these need a text shaping engine (HarfBuzz) that Bedrock lacks. Unfixable server-side.
- **Multi-line wrapping** — long messages may have lines in reverse order. Depends on client screen size.