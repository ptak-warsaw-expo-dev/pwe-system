# `modules/resend/core/notification-name.php`

Plik first-party PWE System w kategorii `resend`.

## Metadane

- **Kategoria:** `resend`
- **Rozmiar:** 2512 B
- **Liczba linii:** 91
- **Źródło:** `modules/resend/core/notification-name.php`

## Typy i metody

### class `PWE_System_Resend_Notification_Name` — linia 12

  - `public static strict_language(string $name)` — linia 14
  - `public static detect_language(string $name, array $languages, string $template = '', string $message = '')` — linia 29
  - `public static base_title(string $name, string $language = '')` — linia 63
  - `private static is_allowed(string $language, array $languages)` — linia 80

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `self::strict_language()`
- `self::is_allowed()`

## API WordPress / GF rozpoznane heurystycznie

- `wp_strip_all_tags()`
- `sanitize_key()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
