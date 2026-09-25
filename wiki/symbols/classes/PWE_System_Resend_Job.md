# Class `PWE_System_Resend_Job`

**Źródło:** `modules/resend/core/resend-job.php:9`  
**Typ:** `class`  
**Metody:** 18

## Rola

Symbol jest zdefiniowany w `modules/resend/core/resend-job.php`. Status runtime pliku można sprawdzić w `inventory/load-graph.json`.

## Metody

- [`public static get()`](../methods/PWE_System_Resend_Job/get.md) — linia 14
- [`public static save(array $job)`](../methods/PWE_System_Resend_Job/save.md) — linia 34
- [`public static checkpoint(array $job, string $lock_token)`](../methods/PWE_System_Resend_Job/checkpoint.md) — linia 39
- [`public static delete()`](../methods/PWE_System_Resend_Job/delete.md) — linia 49
- [`public static create(array $form_ids, int $batch_size, int $delay, bool $include_all_entries, int $min_age_days)`](../methods/PWE_System_Resend_Job/create.md) — linia 64
- [`public static mutate(callable $callback)`](../methods/PWE_System_Resend_Job/mutate.md) — linia 136
- [`public static block_for_entries_error(array &$job, string $message, int $form_id = 0)`](../methods/PWE_System_Resend_Job/block_for_entries_error.md) — linia 164
- [`public static block_for_delivery_error(array &$job, int $entry_id, string $notification, string $message)`](../methods/PWE_System_Resend_Job/block_for_delivery_error.md) — linia 177
- [`public static clear_block(array &$job)`](../methods/PWE_System_Resend_Job/clear_block.md) — linia 195
- [`public static complete(array &$job)`](../methods/PWE_System_Resend_Job/complete.md) — linia 208
- [`public static append_log(array &$job, int $entry_id, string $notification, string $status, string $message)`](../methods/PWE_System_Resend_Job/append_log.md) — linia 221
- [`public static stats(array $job)`](../methods/PWE_System_Resend_Job/stats.md) — linia 242
- [`public static empty_cursor()`](../methods/PWE_System_Resend_Job/empty_cursor.md) — linia 257
- [`private static migrate_legacy_total(array $fallback_job)`](../methods/PWE_System_Resend_Job/migrate_legacy_total.md) — linia 262
- [`private static count_remaining(array $job, string $lock_token)`](../methods/PWE_System_Resend_Job/count_remaining.md) — linia 341
- [`private static completed_count(array $job)`](../methods/PWE_System_Resend_Job/completed_count.md) — linia 388
- [`private static normalise(array $job)`](../methods/PWE_System_Resend_Job/normalise.md) — linia 395
- [`private static normalise_cursor($cursor)`](../methods/PWE_System_Resend_Job/normalise_cursor.md) — linia 430

## Dokument pliku

- [Otwórz dokumentację `modules/resend/core/resend-job.php`](../../files/modules/resend/core/resend-job.php.md)
