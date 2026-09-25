# `modules/replace-content/core/page-meta-adapter.php`

Plik first-party PWE System w kategorii `replace-content`.

## Metadane

- **Kategoria:** `replace-content`
- **Rozmiar:** 2216 B
- **Liczba linii:** 55
- **Źródło:** `modules/replace-content/core/page-meta-adapter.php`

## Typy i metody

### class `PWE_System_Replace_Content_Page_Meta_Adapter` — linia 7

  - `public static inspect_replacement_state(int $post_id, string $shortcode)` — linia 13
  - `public static set_uncode_header_none(int $post_id)` — linia 36
  - `public static set_uncode_show_title_off(int $post_id)` — linia 43
  - `public static verify_replacement_state(int $post_id, string $shortcode)` — linia 49

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `self::inspect_replacement_state()`

## API WordPress / GF rozpoznane heurystycznie

- `get_post_field()`
- `get_post_meta()`
- `metadata_exists()`
- `delete_post_meta()`
- `update_post_meta()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
