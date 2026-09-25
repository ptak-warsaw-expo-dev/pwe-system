# `modules/replace-content/core/page-locator.php`

Plik first-party PWE System w kategorii `replace-content`.

## Metadane

- **Kategoria:** `replace-content`
- **Rozmiar:** 1161 B
- **Liczba linii:** 38
- **Źródło:** `modules/replace-content/core/page-locator.php`

## Typy i metody

### class `PWE_System_Replace_Content_Page_Locator` — linia 7

  - `public static find_by_url(string $url)` — linia 9
  - `private static as_page(int $post_id)` — linia 32

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `self::as_page()`

## API WordPress / GF rozpoznane heurystycznie

- `wp_parse_url()`
- `get_option()`
- `home_url()`
- `get_post()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
