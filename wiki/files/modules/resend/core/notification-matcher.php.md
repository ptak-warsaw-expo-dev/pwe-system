# `modules/resend/core/notification-matcher.php`

Plik first-party PWE System w kategorii `resend`.

## Metadane

- **Kategoria:** `resend`
- **Rozmiar:** 3474 B
- **Liczba linii:** 111
- **Źródło:** `modules/resend/core/notification-matcher.php`

## Typy i metody

### class `PWE_System_Resend_Matcher` — linia 9

  - `public static get_entry_lang(array $form, array $entry)` — linia 11
  - `public static get_entry_location(array $form, array $entry)` — linia 18
  - `public static entry_has_platyna(array $form, array $entry)` — linia 23
  - `public static notification_is_platyna(array $notification)` — linia 28
  - `public static extract_lang_from_notification_name(string $name)` — linia 33
  - `public static is_entry_matching_notification(array $form, array $entry, array $notification)` — linia 38
  - `public static notification_should_send(array $notification, array $form, array $entry)` — linia 56
  - `public static entry_has_valid_email(array $form, array $entry)` — linia 74
  - `public static sent_meta_key(array $notification)` — linia 86
  - `public static skipped_meta_key(array $notification)` — linia 93
  - `public static is_already_processed(array $entry, array $notification)` — linia 98

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `PWE_System_Resend_Field_Locator::value()`
- `self::get_entry_location()`
- `PWE_System_Resend_Notification_Name::strict_language()`
- `self::extract_lang_from_notification_name()`
- `self::get_entry_lang()`
- `self::entry_has_platyna()`
- `self::notification_is_platyna()`
- `self::is_entry_matching_notification()`
- `PWE_System_Resend_Conditional_Logic::compile()`
- `PWE_System_Resend_Conditional_Logic::evaluate()`
- `self::sent_meta_key()`
- `self::skipped_meta_key()`

## API WordPress / GF rozpoznane heurystycznie

- `sanitize_key()`
- `gform_get_meta()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
