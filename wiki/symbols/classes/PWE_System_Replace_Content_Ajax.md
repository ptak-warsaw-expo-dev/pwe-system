# Class `PWE_System_Replace_Content_Ajax`

**Źródło:** `modules/replace-content/core/ajax-actions.php:9`  
**Typ:** `class`  
**Metody:** 12

## Rola

Symbol jest zdefiniowany w `modules/replace-content/core/ajax-actions.php`. Status runtime pliku można sprawdzić w `inventory/load-graph.json`.

## Metody

- [`public static init()`](../methods/PWE_System_Replace_Content_Ajax/init.md) — linia 18
- [`public static start()`](../methods/PWE_System_Replace_Content_Ajax/start.md) — linia 24
- [`public static step()`](../methods/PWE_System_Replace_Content_Ajax/step.md) — linia 72
- [`private static process_job_step(string $job_key, string $token, array $job, ?int $client_processed, string &$error_message)`](../methods/PWE_System_Replace_Content_Ajax/process_job_step.md) — linia 162
- [`private static is_valid_job($job)`](../methods/PWE_System_Replace_Content_Ajax/is_valid_job.md) — linia 258
- [`private static response_from_job(array $job, string $token)`](../methods/PWE_System_Replace_Content_Ajax/response_from_job.md) — linia 272
- [`private static error_response_data(string $message, string $token, $job, bool $retryable)`](../methods/PWE_System_Replace_Content_Ajax/error_response_data.md) — linia 305
- [`private static authorize()`](../methods/PWE_System_Replace_Content_Ajax/authorize.md) — linia 321
- [`private static job_key(string $token)`](../methods/PWE_System_Replace_Content_Ajax/job_key.md) — linia 330
- [`private static lock_key(string $token)`](../methods/PWE_System_Replace_Content_Ajax/lock_key.md) — linia 335
- [`private static acquire_lock(string $token)`](../methods/PWE_System_Replace_Content_Ajax/acquire_lock.md) — linia 340
- [`private static release_lock(string $token, string $lock_name)`](../methods/PWE_System_Replace_Content_Ajax/release_lock.md) — linia 352

## Dokument pliku

- [Otwórz dokumentację `modules/replace-content/core/ajax-actions.php`](../../files/modules/replace-content/core/ajax-actions.php.md)
