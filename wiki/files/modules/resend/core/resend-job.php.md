# `modules/resend/core/resend-job.php`

Plik first-party PWE System w kategorii `resend`.

## Metadane

- **Kategoria:** `resend`
- **Rozmiar:** 14929 B
- **Liczba linii:** 441
- **Źródło:** `modules/resend/core/resend-job.php`

## Typy i metody

### class `PWE_System_Resend_Job` — linia 9

  - `public static get()` — linia 14
  - `public static save(array $job)` — linia 34
  - `public static checkpoint(array $job, string $lock_token)` — linia 39
  - `public static delete()` — linia 49
  - `public static create(array $form_ids, int $batch_size, int $delay, bool $include_all_entries, int $min_age_days)` — linia 64
  - `public static mutate(callable $callback)` — linia 136
  - `public static block_for_entries_error(array &$job, string $message, int $form_id = 0)` — linia 164
  - `public static block_for_delivery_error(array &$job, int $entry_id, string $notification, string $message)` — linia 177
  - `public static clear_block(array &$job)` — linia 195
  - `public static complete(array &$job)` — linia 208
  - `public static append_log(array &$job, int $entry_id, string $notification, string $status, string $message)` — linia 221
  - `public static stats(array $job)` — linia 242
  - `public static empty_cursor()` — linia 257
  - `private static migrate_legacy_total(array $fallback_job)` — linia 262
  - `private static count_remaining(array $job, string $lock_token)` — linia 341
  - `private static completed_count(array $job)` — linia 388
  - `private static normalise(array $job)` — linia 395
  - `private static normalise_cursor($cursor)` — linia 430

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- Brak wykrytych/reprodukowanych rejestracji.

## Wybrane wywołania statyczne

- `self::normalise()`
- `self::migrate_legacy_total()`
- `PWE_System_Resend_Job_Lock::refresh()`
- `self::save()`
- `PWE_System_Resend_Job_Lock::acquire()`
- `PWE_System_Resend_Job_Lock::release()`
- `self::get()`
- `PWE_System_Resend_Form_Repository::get()`
- `PWE_System_Resend_Gravity::get_resend_notifications()`
- `self::empty_cursor()`
- `self::count_remaining()`
- `self::block_for_entries_error()`
- `self::completed_count()`
- `self::clear_block()`
- `PWE_System_Resend_Gravity::scan_form_stats()`
- `self::normalise_cursor()`

## API WordPress / GF rozpoznane heurystycznie

- `get_option()`
- `update_option()`
- `delete_option()`
- `wp_generate_uuid4()`

## Dołączane pliki / wyrażenia include

- `s_total_migration = (int) ($stored['schema_version'] ?? 0) < self::SCHEMA_VERSION`
- `s_total_migration) {`
- `_all_entries,`
- `_all_entries' => $include_all_entries,`
- `_all_entries'] ?? false),`
- `_all_entries'] = !empty($job['include_all_entries'])`

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
