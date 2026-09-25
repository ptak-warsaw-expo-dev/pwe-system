# `PWE_System_Resend_Actions::init()`

**Źródło:** `modules/resend/core/resend-actions.php:13`  
**Typ właściciela:** `class`  
**Sygnatura:** `public static init()`

## Kontekst

- Symbol właściciela: [`PWE_System_Resend_Actions`](../../classes/PWE_System_Resend_Actions.md)
- Plik: [dokument pliku](../../../files/modules/resend/core/resend-actions.php.md)

## Wykryte zależności pliku

- `PWE_System_Admin_Access::is_allowed()`
- `PWE_System_Resend_Job::create()`
- `self::safe_redirect()`
- `self::admin_url()`
- `PWE_System_Resend_Job::mutate()`
- `PWE_System_Resend_Job_Runner::run()`
- `PWE_System_Resend_Job::clear_block()`
- `PWE_System_Resend_Job::delete()`

## Uwagi

Dokładny graf wywołań buduje osobny Code Indexer z AST. Przy traitach call graph wymaga dodatkowej świadomości `use Trait` w klasie konsumującej.
