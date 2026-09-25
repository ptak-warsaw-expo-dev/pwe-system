# `PWE_System_Replace_Content_Ajax::lock_key()`

**Źródło:** `modules/replace-content/core/ajax-actions.php:335`  
**Typ właściciela:** `class`  
**Sygnatura:** `private static lock_key(string $token)`

## Kontekst

- Symbol właściciela: [`PWE_System_Replace_Content_Ajax`](../../classes/PWE_System_Replace_Content_Ajax.md)
- Plik: [dokument pliku](../../../files/modules/replace-content/core/ajax-actions.php.md)

## Wykryte zależności pliku

- `self::authorize()`
- `PWE_System_Replace_Content_Config::get_map()`
- `PWE_System_Replace_Content_Service::build_plan()`
- `PWE_System_Replace_Content_Service::prepare_job()`
- `PWE_System_Replace_Content_Service::empty_result()`
- `self::job_key()`
- `self::is_valid_job()`
- `self::response_from_job()`
- `self::acquire_lock()`
- `self::error_response_data()`
- `self::process_job_step()`
- `self::release_lock()`
- `PWE_System_Replace_Content_Service::recover_page_result()`
- `PWE_System_Replace_Content_Service::process_page()`
- `PWE_System_Replace_Content_Service::add_page_result()`
- `PWE_System_Admin_Access::is_allowed()`
- `self::lock_key()`

## Uwagi

Dokładny graf wywołań buduje osobny Code Indexer z AST. Przy traitach call graph wymaga dodatkowej świadomości `use Trait` w klasie konsumującej.
