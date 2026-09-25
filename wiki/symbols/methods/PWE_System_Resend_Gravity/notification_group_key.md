# `PWE_System_Resend_Gravity::notification_group_key()`

**Źródło:** `modules/resend/core/gravity-entries.php:172`  
**Typ właściciela:** `class`  
**Sygnatura:** `private static notification_group_key(string $name)`

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
