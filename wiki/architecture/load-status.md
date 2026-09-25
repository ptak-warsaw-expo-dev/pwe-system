---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Load status

`inventory/load-graph.json` opisuje, czy plik jest bootstrapem, aktywną częścią runtime, modułem warunkowym, direct entrypointem czy biblioteką third-party.

Najważniejsze warunki:

- backend shortcode nie jest dołączany, jeśli `pwe_get_shortcode_map()` już istnieje,
- `PWE_Shortcodes` nie jest dołączany, jeśli klasa o tej nazwie już istnieje,
- Forms Audit uruchamia narzędzia tylko przy gotowym `PWE_QR_Gravity_Forms`,
- API `cap` i `news` nie przechodzą przez `PWE_System::init()` jako entrypoint — same ładują WordPress.
