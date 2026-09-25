---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Shortcody i tłumaczenia

## `[pwe_*]`

`modules/shortcodes/backend-shortcodes.php` buduje mapę pole danych → tag. `register_dynamic_shortcodes()` rejestruje closure delegujące do `handle_fair_shortcode()`. Dane są ładowane przez `get_fair_data()` z `PWE_Functions`/CAP DB, a przy braku danych istnieje fallback JSON.

Dodatkowe języki generują dziewięć rodzin `pwe_*_{lang}` na podstawie WPML lub fallbackowej listy języków.

## `PWE_Shortcodes`

Klasa rejestruje wyższopoziomowe shortcody kompatybilne z AutoSwitch, integruje je z Gravity Forms merge tags i Yoast replacements oraz obsługuje dynamiczne `url_*`.
