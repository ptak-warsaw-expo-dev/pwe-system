# `PWE_System_Resend_Gravity::scan_form_stats()`

**Źródło:** `modules/resend/core/gravity-entries.php:73`  
**Typ właściciela:** `class`  
**Sygnatura:** `public static scan_form_stats(array $form, array $notifications, bool $include_all_entries, int $min_age_days = PWE_System_Resend::DEFAULT_MIN_AGE_DAYS, bool $only_unsent = false)`

## Kontekst

- Symbol właściciela: [`PWE_System_Resend_Gravity`](../../classes/PWE_System_Resend_Gravity.md)
- Plik: [dokument pliku](../../../files/modules/resend/core/gravity-entries.php.md)

## Wykryte zależności pliku

- `GFAPI::get_entries()`
- `self::entries_error_message()`
- `self::get_entries_page()`
- `PWE_System_Resend_Matcher::is_entry_matching_notification()`
- `PWE_System_Resend_Matcher::is_already_processed()`
- `self::notification_group_key()`
- `PWE_System_Resend_Matcher::extract_lang_from_notification_name()`
- `PWE_System_Resend_Notification_Name::base_title()`

## Uwagi

Dokładny graf wywołań buduje osobny Code Indexer z AST. Przy traitach call graph wymaga dodatkowej świadomości `use Trait` w klasie konsumującej.
