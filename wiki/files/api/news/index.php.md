# `api/news/index.php`

Bezpośredni News Sync API.

## Metadane

- **Kategoria:** `direct-api`
- **Rozmiar:** 11648 B
- **Liczba linii:** 366
- **Źródło:** `api/news/index.php`

## Typy i metody

- Brak klas/traitów.

## Funkcje globalne

- `pwe_system_api_news_json_response($status_code, $data)` — linia 7
- `pwe_system_api_news_assign_category($post_id, $language = 'pl')` — linia 17
- `pwe_system_api_news_prepare_uncode_raw_html($html)` — linia 65
- `pwe_system_api_news_set_featured_image_from_url($image_url, $post_id, $title)` — linia 83

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- Brak.

## API WordPress / GF rozpoznane heurystycznie

- `get_term_by()`
- `is_wp_error()`
- `wp_insert_term()`
- `apply_filters()`
- `wp_set_post_categories()`
- `get_post_thumbnail_id()`
- `get_post_meta()`
- `media_sideload_image()`
- `set_post_thumbnail()`
- `update_post_meta()`
- `sanitize_title()`
- `get_posts()`
- `wp_delete_post()`
- `sanitize_text_field()`
- `esc_url_raw()`
- `wp_update_post()`
- `wp_insert_post()`
- `clean_post_cache()`
- `do_action()`
- `get_permalink()`

## Dołączane pliki / wyrażenia include

- `_once __DIR__ . '/../../../../../wp-load.php'`
- `_once ABSPATH . 'wp-admin/includes/image.php'`
- `_once ABSPATH . 'wp-admin/includes/file.php'`
- `_once ABSPATH . 'wp-admin/includes/media.php'`

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
