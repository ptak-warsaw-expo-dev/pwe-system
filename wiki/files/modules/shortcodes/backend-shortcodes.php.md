# `modules/shortcodes/backend-shortcodes.php`

Proceduralna warstwa danych i shortcode `[pwe_*]`.

## Metadane

- **Kategoria:** `shortcodes`
- **Rozmiar:** 11425 B
- **Liczba linii:** 296
- **Źródło:** `modules/shortcodes/backend-shortcodes.php`

## Typy i metody

- Brak klas/traitów.

## Funkcje globalne

- `get_fair_data($specific_domain = null)` — linia 7
- `pwe_get_shortcode_map()` — linia 146
- `register_dynamic_shortcodes()` — linia 236
- `handle_fair_shortcode($atts, $field)` — linia 241
- `PWE_GF_shortcodes($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format)` — linia 259

## Rejestracje WordPress / GF

- **action:** `init` — linia 255
- **filter:** `gform_replace_merge_tags` — linia 257

## Wybrane wywołania statyczne

- `PWE_Functions::get_database_fairs_data()`
- `PWE_Functions::get_database_translations_data()`
- `PWE_Functions::generate_fair_data()`
- `PWE_Functions::generate_fair_translation_data()`

## API WordPress / GF rozpoznane heurystycznie

- `current_user_can()`
- `is_admin()`
- `home_url()`
- `apply_filters()`
- `shortcode_atts()`
- `add_shortcode()`
- `add_action()`
- `add_filter()`
- `esc_html()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- `JSON`

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
