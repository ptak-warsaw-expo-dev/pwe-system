# `modules/replace-content/core/language-helper.php`

Plik first-party PWE System w kategorii `replace-content`.

## Metadane

- **Kategoria:** `replace-content`
- **Rozmiar:** 1333 B
- **Liczba linii:** 39
- **Źródło:** `modules/replace-content/core/language-helper.php`

## Typy i metody

### class `PWE_System_Replace_Content_Language_Helper` — linia 7

  - `public static get_active_languages()` — linia 9
  - `public static get_language_label(string $lang_code, array $active_languages)` — linia 14
  - `public static get_flag_url(string $lang_code, array $active_languages)` — linia 28

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `PWE_System_Replace_Content_Wpml_Gateway::get_active_languages()`

## API WordPress / GF rozpoznane heurystycznie

- `sanitize_key()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
