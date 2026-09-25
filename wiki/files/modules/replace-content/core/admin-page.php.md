# `modules/replace-content/core/admin-page.php`

Plik first-party PWE System w kategorii `replace-content`.

## Metadane

- **Kategoria:** `replace-content`
- **Rozmiar:** 11727 B
- **Liczba linii:** 292
- **Źródło:** `modules/replace-content/core/admin-page.php`

## Typy i metody

### class `PWE_System_Replace_Content_Admin_Page` — linia 9

  - `public static render()` — linia 13
  - `private static is_submit()` — linia 96
  - `private static render_result(?array $result)` — linia 105
  - `private static render_progress()` — linia 136
  - `private static get_summary(array $plan)` — linia 154
  - `private static render_plan_item(array $item, array $active_languages)` — linia 184
  - `private static render_translation(array $translation, array $active_languages)` — linia 226

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `PWE_System_Admin_Access::is_allowed()`
- `PWE_System_Replace_Content_Config::get_map()`
- `self::is_submit()`
- `PWE_System_Replace_Content_Service::execute()`
- `PWE_System_Replace_Content_Service::build_plan()`
- `PWE_System_Replace_Content_Language_Helper::get_active_languages()`
- `self::render_result()`
- `PWE_System_Admin_UI::notice()`
- `self::get_summary()`
- `self::render_plan_item()`
- `self::render_progress()`
- `PWE_System_Admin_UI::button()`
- `self::render_translation()`
- `PWE_System_Replace_Content_Language_Helper::get_language_label()`
- `PWE_System_Replace_Content_Language_Helper::get_flag_url()`

## API WordPress / GF rozpoznane heurystycznie

- `check_admin_referer()`
- `esc_html()`
- `esc_url()`
- `admin_url()`
- `esc_attr()`
- `wp_nonce_field()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
