---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Shortcode `[url_kontakt]`

- **Typ:** `dynamic-url`
- **Kategoria:** Adresy wielojęzyczne
- **Źródło:** `modules/shortcodes/class-shortcodes.php:4148`
- **Callback / dispatcher:** `PWE_Shortcodes::show_multilang_url`
- **Callback mode:** `dynamic-json-key`

## Opis

Dynamiczny URL dla klucza `kontakt`. Lista kluczy pochodzi z `pwe-multilang/website-translation.json`, a w razie braku/błędu z `PWE System/data/website-translation.json`.

## Opcje

`lang`, `absolute`
