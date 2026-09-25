# `modules/resend/core/admin-page.php`

Plik first-party PWE System w kategorii `resend`.

## Metadane

- **Kategoria:** `resend`
- **Rozmiar:** 20293 B
- **Liczba linii:** 503
- **Źródło:** `modules/resend/core/admin-page.php`

## Typy i metody

### class `PWE_System_Resend_Admin` — linia 7

  - `public static render()` — linia 9
  - `private static render_start_screen()` — linia 33
  - `private static render_preview_toggle(bool $preview_all)` — linia 77
  - `private static build_form_rows(array $forms, bool $preview_all)` — linia 90
  - `private static render_form_card(array $row)` — linia 133
  - `private static render_settings_bar()` — linia 178
  - `private static render_job_screen(array $job)` — linia 252
  - `private static render_job_actions(array $job)` — linia 312
  - `private static group_notifications_for_display(array $notifications)` — linia 389
  - `private static strip_lang_suffix(string $name)` — linia 418
  - `private static render_notification_groups(array $groups, array $notification_lang_counts)` — linia 423
  - `private static notification_lang_count(array $notification_lang_counts, string $group_key, string $lang_key)` — linia 458
  - `private static render_recent_log(array $job)` — linia 467

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `PWE_System_Admin_Access::is_allowed()`
- `PWE_System_Resend_Job::get()`
- `self::render_job_screen()`
- `self::render_start_screen()`
- `GFAPI::get_forms()`
- `self::render_preview_toggle()`
- `self::build_form_rows()`
- `self::render_settings_bar()`
- `self::render_form_card()`
- `PWE_System_Admin_UI::button()`
- `PWE_System_Resend_Actions::admin_url()`
- `GFAPI::get_form()`
- `PWE_System_Resend_Gravity::get_resend_notifications()`
- `PWE_System_Resend_Gravity::scan_form_stats()`
- `self::group_notifications_for_display()`
- `PWE_System_Admin_UI::checkbox()`
- `self::render_notification_groups()`
- `PWE_System_Resend_Job::stats()`
- `self::render_job_actions()`
- `PWE_System_Resend_Actions::action_form()`
- `self::render_recent_log()`
- `PWE_System_Admin_UI::status()`
- `PWE_System_Resend_Matcher::extract_lang_from_notification_name()`
- `self::strip_lang_suffix()`
- `self::notification_lang_count()`

## API WordPress / GF rozpoznane heurystycznie

- `wp_die()`
- `wp_nonce_field()`
- `esc_url()`
- `esc_html()`
- `esc_attr()`
- `wp_create_nonce()`

## Dołączane pliki / wyrażenia include

- `_all_entries',`
- `_all_entries']) ? 'wszystkie wpisy' : 'starsze niz ' . esc_html((string) $min_age_days) . ' dni') . '</strong>'`

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
