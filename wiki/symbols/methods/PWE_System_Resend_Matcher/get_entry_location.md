# `PWE_System_Resend_Matcher::get_entry_location()`

**Źródło:** `modules/resend/core/notification-matcher.php:18`  
**Typ właściciela:** `class`  
**Sygnatura:** `public static get_entry_location(array $form, array $entry)`

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
