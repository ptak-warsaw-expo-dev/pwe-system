# Class `PWE_System_Resend_Job_Runner`

**Źródło:** `modules/resend/core/job-runner.php:9`  
**Typ:** `class`  
**Metody:** 5

## Rola

Symbol jest zdefiniowany w `modules/resend/core/job-runner.php`. Status runtime pliku można sprawdzić w `inventory/load-graph.json`.

## Metody

- [`public static run(array &$job, ?string $lock_token = null)`](../methods/PWE_System_Resend_Job_Runner/run.md) — linia 13
- [`private static run_locked(array &$job, string $lock_token)`](../methods/PWE_System_Resend_Job_Runner/run_locked.md) — linia 40
- [`private static next_form(array $cursor)`](../methods/PWE_System_Resend_Job_Runner/next_form.md) — linia 192
- [`private static record_result(array &$job, array $entry, array $notification, $result)`](../methods/PWE_System_Resend_Job_Runner/record_result.md) — linia 202
- [`private static maybe_send_notification(array $form, array $entry, array $notification)`](../methods/PWE_System_Resend_Job_Runner/maybe_send_notification.md) — linia 226

## Dokument pliku

- [Otwórz dokumentację `modules/resend/core/job-runner.php`](../../files/modules/resend/core/job-runner.php.md)
