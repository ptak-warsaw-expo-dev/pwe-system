# `PWE_System_Forms_Audit_Notifications_Trait::has_sent_notification()`

**Źródło:** `modules/forms-audit/traits/trait-pwe-forms-audit-notifications.php:1274`  
**Typ właściciela:** `trait`  
**Sygnatura:** `private has_sent_notification($entry_id, $form = null)`

## Kontekst

- Symbol właściciela: [`PWE_System_Forms_Audit_Notifications_Trait`](../../classes/PWE_System_Forms_Audit_Notifications_Trait.md)
- Plik: [dokument pliku](../../../files/modules/forms-audit/traits/trait-pwe-forms-audit-notifications.php.md)

## Wykryte zależności pliku

- `GFAPI::get_form()`
- `GFAPI::get_entries()`
- `GFAPI::get_entry()`
- `GFCommon::send_notification()`
- `GFAPI::add_note()`
- `GFAPI::get_notes()`
- `GFCommon::evaluate_conditional_logic()`
- `GFCommon::replace_variables()`

## Uwagi

Dokładny graf wywołań buduje osobny Code Indexer z AST. Przy traitach call graph wymaga dodatkowej świadomości `use Trait` w klasie konsumującej.
