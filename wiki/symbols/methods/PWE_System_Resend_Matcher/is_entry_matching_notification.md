# `PWE_System_Resend_Matcher::is_entry_matching_notification()`

**Źródło:** `modules/resend/core/notification-matcher.php:38`  
**Typ właściciela:** `class`  
**Sygnatura:** `public static is_entry_matching_notification(array $form, array $entry, array $notification)`

## Kontekst

- Symbol właściciela: [`PWE_System_Resend_Matcher`](../../classes/PWE_System_Resend_Matcher.md)
- Plik: [dokument pliku](../../../files/modules/resend/core/notification-matcher.php.md)

## Wykryte zależności pliku

- `PWE_System_Resend_Field_Locator::value()`
- `self::get_entry_location()`
- `PWE_System_Resend_Notification_Name::strict_language()`
- `self::extract_lang_from_notification_name()`
- `self::get_entry_lang()`
- `self::entry_has_platyna()`
- `self::notification_is_platyna()`
- `self::is_entry_matching_notification()`
- `PWE_System_Resend_Conditional_Logic::compile()`
- `PWE_System_Resend_Conditional_Logic::evaluate()`
- `self::sent_meta_key()`
- `self::skipped_meta_key()`

## Uwagi

Dokładny graf wywołań buduje osobny Code Indexer z AST. Przy traitach call graph wymaga dodatkowej świadomości `use Trait` w klasie konsumującej.
