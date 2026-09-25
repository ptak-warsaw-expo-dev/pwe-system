---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Shortcode `[pwe_exhibitors]`

- **Typ:** `dynamic-map`
- **Kategoria:** PWE data / CAP
- **Źródło:** `modules/shortcodes/backend-shortcodes.php:165`
- **Callback / dispatcher:** `handle_fair_shortcode($atts, "fair_exhibitors_current")`
- **Callback mode:** `closure-dispatch`

## Opis

Zwraca pole `fair_exhibitors_current` danych targów. Tag należy do mapy `pwe_get_shortcode_map()` i jest rejestrowany przez `register_dynamic_shortcodes()`.

## Opcje

`domain`
