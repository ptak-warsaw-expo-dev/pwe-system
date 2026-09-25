# `modules/resend/core/job-runner.php`

Plik first-party PWE System w kategorii `resend`.

## Metadane

- **Kategoria:** `resend`
- **Rozmiar:** 9372 B
- **Liczba linii:** 280
- **Źródło:** `modules/resend/core/job-runner.php`

## Typy i metody

### class `PWE_System_Resend_Job_Runner` — linia 9

  - `public static run(array &$job, ?string $lock_token = null)` — linia 13
  - `private static run_locked(array &$job, string $lock_token)` — linia 40
  - `private static next_form(array $cursor)` — linia 192
  - `private static record_result(array &$job, array $entry, array $notification, $result)` — linia 202
  - `private static maybe_send_notification(array $form, array $entry, array $notification)` — linia 226

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- **action:** `wp_mail_failed` — linia 253

## Wybrane wywołania statyczne

- `PWE_System_Resend_Job_Lock::acquire()`
- `self::run_locked()`
- `PWE_System_Resend_Job_Lock::release()`
- `PWE_System_Resend_Job_Lock::refresh()`
- `PWE_System_Resend_Job::empty_cursor()`
- `PWE_System_Resend_Job::complete()`
- `PWE_System_Resend_Form_Repository::get()`
- `self::next_form()`
- `PWE_System_Resend_Gravity::get_resend_notifications()`
- `PWE_System_Resend_Gravity::get_entries_page()`
- `PWE_System_Resend_Job::block_for_entries_error()`
- `PWE_System_Resend_Job::append_log()`
- `self::maybe_send_notification()`
- `PWE_System_Resend_Job::block_for_delivery_error()`
- `self::record_result()`
- `PWE_System_Resend_Job::checkpoint()`
- `PWE_System_Resend_Matcher::is_entry_matching_notification()`
- `PWE_System_Resend_Matcher::is_already_processed()`
- `PWE_System_Resend_Matcher::notification_should_send()`
- `PWE_System_Resend_Matcher::entry_has_valid_email()`
- `PWE_System_Resend_Matcher::skipped_meta_key()`
- `GFCommon::send_notification()`
- `GFCommon::send_notifications()`
- `PWE_System_Resend_Matcher::sent_meta_key()`

## API WordPress / GF rozpoznane heurystycznie

- `gform_update_meta()`
- `add_action()`
- `remove_action()`

## Dołączane pliki / wyrażenia include

- `_all_entries'] ?? false),`

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
