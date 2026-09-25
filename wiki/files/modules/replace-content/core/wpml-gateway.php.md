# `modules/replace-content/core/wpml-gateway.php`

Plik first-party PWE System w kategorii `replace-content`.

## Metadane

- **Kategoria:** `replace-content`
- **Rozmiar:** 1651 B
- **Liczba linii:** 55
- **Źródło:** `modules/replace-content/core/wpml-gateway.php`

## Typy i metody

### class `PWE_System_Replace_Content_Wpml_Gateway` — linia 7

  - `public static is_active()` — linia 9
  - `public static get_active_languages()` — linia 14
  - `public static get_element_type(string $post_type = 'page')` — linia 20
  - `public static get_trid(int $post_id, string $element_type)` — linia 25
  - `public static get_element_translations(int $trid, string $element_type)` — linia 30
  - `public static get_element_language(int $post_id, string $element_type)` — linia 39

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- Brak.

## API WordPress / GF rozpoznane heurystycznie

- `apply_filters()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
