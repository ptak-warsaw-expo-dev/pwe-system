---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Zgodność i zależności cross-plugin

## PWE Elements → PWE System

`PWE_System_Functions` jest implementacją wspólnych funkcji. Na końcu pliku tworzony jest alias `PWE_Functions`, jeżeli taka klasa jeszcze nie istnieje. Dzięki temu starszy kod PWE może używać poprzedniej nazwy.

## AutoSwitch

`PWE_System_Functions::elements_plugin_path()` i `elements_plugin_url()` wskazują na `pwe-elements-auto-switch` (lub stałe `PWE_PLUGIN_PATH/PWE_PLUGIN_FILE`). Replace Content zapisuje na stronach shortcody `pwe-elements-auto-switch-page-*`.

AutoSwitch może z kolei posiadać bridge HTTP do systemowych `api/cap/doc.php` i `api/news/index.php`. To zależność przychodząca, której nie widać w lokalnym Call Graphie PWE System.

## PWE QR Gravity Forms

Forms Audit nie tworzy własnego generatora QR. `PWE_System_Forms_Audit_Module::boot()` pobiera singleton `PWE_QR_Gravity_Forms`, następnie używa `$plugin->qr`. Bez tej zależności narzędzia audytu/backfillu nie są inicjalizowane.

## PWE Multilang

Dla dynamicznych shortcode `url_*` preferowany jest `wp-content/plugins/pwe-multilang/website-translation.json`, z fallbackiem do `PWE System/data/website-translation.json`.
