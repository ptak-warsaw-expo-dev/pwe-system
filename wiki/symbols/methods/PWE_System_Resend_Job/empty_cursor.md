# `PWE_System_Resend_Job::empty_cursor()`

**Źródło:** `modules/resend/core/resend-job.php:257`  
**Typ właściciela:** `class`  
**Sygnatura:** `public static empty_cursor()`

## Kontekst

- Symbol właściciela: [`PWE_System_Resend_Job`](../../classes/PWE_System_Resend_Job.md)
- Plik: [dokument pliku](../../../files/modules/resend/core/resend-job.php.md)

## Wykryte zależności pliku

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

## Uwagi

Dokładny graf wywołań buduje osobny Code Indexer z AST. Przy traitach call graph wymaga dodatkowej świadomości `use Trait` w klasie konsumującej.
