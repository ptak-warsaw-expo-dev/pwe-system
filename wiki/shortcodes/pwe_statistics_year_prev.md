---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Shortcode `[pwe_statistics_year_prev]`

- **Typ:** `dynamic-map`
- **Kategoria:** PWE data / CAP
- **Źródło:** `modules/shortcodes/backend-shortcodes.php:174`
- **Callback / dispatcher:** `handle_fair_shortcode($atts, "fair_year_previous")`
- **Callback mode:** `closure-dispatch`

## Opis

Zwraca pole `fair_year_previous` danych targów. Tag należy do mapy `pwe_get_shortcode_map()` i jest rejestrowany przez `register_dynamic_shortcodes()`.

## Opcje

`domain`
