# `PWE_System_Resend_Job_Runner::record_result()`

**Źródło:** `modules/resend/core/job-runner.php:202`  
**Typ właściciela:** `class`  
**Sygnatura:** `private static record_result(array &$job, array $entry, array $notification, $result)`

## Kontekst

- Symbol właściciela: [`PWE_System_Resend_Job_Runner`](../../classes/PWE_System_Resend_Job_Runner.md)
- Plik: [dokument pliku](../../../files/modules/resend/core/job-runner.php.md)

## Wykryte zależności pliku

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

## Uwagi

Dokładny graf wywołań buduje osobny Code Indexer z AST. Przy traitach call graph wymaga dodatkowej świadomości `use Trait` w klasie konsumującej.
