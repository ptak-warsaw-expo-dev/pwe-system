# `modules/replace-content/core/replace-content-service.php`

Plik first-party PWE System w kategorii `replace-content`.

## Metadane

- **Kategoria:** `replace-content`
- **Rozmiar:** 14120 B
- **Liczba linii:** 406
- **Źródło:** `modules/replace-content/core/replace-content-service.php`

## Typy i metody

### class `PWE_System_Replace_Content_Service` — linia 9

  - `public static build_plan(array $map)` — linia 15
  - `public static prepare_job(array $plan)` — linia 54
  - `public static execute(array $plan)` — linia 136
  - `public static process_page(int $post_id, string $shortcode)` — linia 168
  - `public static empty_result()` — linia 244
  - `public static add_page_result(array &$result, array $page_result)` — linia 258
  - `public static recover_page_result(array $task)` — linia 280
  - `private static get_wpml_translations(WP_Post $source_page, string $shortcode)` — linia 307
  - `private static add_translation(array &$translations, int $post_id, string $language, string $shortcode, bool $is_original = false)` — linia 363
  - `private static get_page_language(int $post_id, string $element_type)` — linia 401

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `PWE_System_Replace_Content_Page_Locator::find_by_url()`
- `self::get_wpml_translations()`
- `self::empty_result()`
- `self::prepare_job()`
- `self::process_page()`
- `self::add_page_result()`
- `PWE_System_Replace_Content_Page_Meta_Adapter::inspect_replacement_state()`
- `PWE_System_Replace_Content_Page_Meta_Adapter::set_uncode_header_none()`
- `PWE_System_Replace_Content_Page_Meta_Adapter::set_uncode_show_title_off()`
- `PWE_System_Replace_Content_Page_Meta_Adapter::verify_replacement_state()`
- `PWE_System_Replace_Content_Wpml_Gateway::get_element_type()`
- `PWE_System_Replace_Content_Wpml_Gateway::get_trid()`
- `PWE_System_Replace_Content_Wpml_Gateway::get_element_translations()`
- `self::add_translation()`
- `self::get_page_language()`
- `PWE_System_Replace_Content_Wpml_Gateway::get_element_language()`

## API WordPress / GF rozpoznane heurystycznie

- `get_post()`
- `wp_update_post()`
- `wp_slash()`
- `is_wp_error()`
- `clean_post_cache()`
- `do_action()`
- `get_the_title()`
- `get_permalink()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
