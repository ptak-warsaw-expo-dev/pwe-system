# `modules/resend/core/field-locator.php`

Plik first-party PWE System w kategorii `resend`.

## Metadane

- **Kategoria:** `resend`
- **Rozmiar:** 3994 B
- **Liczba linii:** 120
- **Źródło:** `modules/resend/core/field-locator.php`

## Typy i metody

### class `PWE_System_Resend_Field_Locator` — linia 9

  - `public static find_id(array $form, array $names = [], array $types = [])` — linia 18
  - `public static value(array $form, array $entry, array $names, array $types = [])` — linia 60
  - `public static clear_cache()` — linia 71
  - `private static cache_key(array $form, array $names, array $types)` — linia 85
  - `private static property($field, string $name)` — linia 113

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `self::cache_key()`
- `self::property()`
- `self::find_id()`

## API WordPress / GF rozpoznane heurystycznie

- `wp_json_encode()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
