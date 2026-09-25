---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Proces: warstwa danych `[pwe_*]`

`pwe_get_shortcode_map()` definiuje 50 bazowych tagów danych targów. WPML rozszerza mapę o dodatkowe języki. Na `init` `register_dynamic_shortcodes()` rejestruje closure dla każdego tagu.

Callback deleguje do `handle_fair_shortcode($atts, $field)`, a ten do `get_fair_data()`. Dane są budowane z `PWE_Functions::get_database_fairs_data()` i `get_database_translations_data()`. Jeśli warstwa CAP nie zwróci danych, kod ma fallback do zewnętrznego `pwe-data.json`.

Ten sam map jest używany przez filtr `gform_replace_merge_tags`, dzięki czemu `{pwe_xxx}` działa również w Gravity Forms.
