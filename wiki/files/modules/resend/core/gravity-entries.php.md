# `modules/resend/core/gravity-entries.php`

Plik first-party PWE System w kategorii `resend`.

## Metadane

- **Kategoria:** `resend`
- **Rozmiar:** 5892 B
- **Liczba linii:** 177
- **Źródło:** `modules/resend/core/gravity-entries.php`

## Typy i metody

### class `PWE_System_Resend_Gravity` — linia 9

  - `public static get_resend_notifications(array $form)` — linia 14
  - `public static get_entries_page(int $form_id, bool $include_all_entries, int $offset, int $page_size, int $min_age_days = PWE_System_Resend::DEFAULT_MIN_AGE_DAYS)` — linia 32
  - `public static scan_form_stats(array $form, array $notifications, bool $include_all_entries, int $min_age_days = PWE_System_Resend::DEFAULT_MIN_AGE_DAYS, bool $only_unsent = false)` — linia 73
  - `private static entries_error_message(int $form_id, string $details)` — linia 161
  - `private static notification_group_key(string $name)` — linia 172

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `GFAPI::get_entries()`
- `self::entries_error_message()`
- `self::get_entries_page()`
- `PWE_System_Resend_Matcher::is_entry_matching_notification()`
- `PWE_System_Resend_Matcher::is_already_processed()`
- `self::notification_group_key()`
- `PWE_System_Resend_Matcher::extract_lang_from_notification_name()`
- `PWE_System_Resend_Notification_Name::base_title()`

## API WordPress / GF rozpoznane heurystycznie

- `is_wp_error()`
- `wp_json_encode()`

## Dołączane pliki / wyrażenia include

- `_all_entries,`
- `_all_entries) {`

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
