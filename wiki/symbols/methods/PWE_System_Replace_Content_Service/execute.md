# `PWE_System_Replace_Content_Service::execute()`

**Źródło:** `modules/replace-content/core/replace-content-service.php:136`  
**Typ właściciela:** `class`  
**Sygnatura:** `public static execute(array $plan)`

## Kontekst

- Symbol właściciela: [`PWE_System_Replace_Content_Service`](../../classes/PWE_System_Replace_Content_Service.md)
- Plik: [dokument pliku](../../../files/modules/replace-content/core/replace-content-service.php.md)

## Wykryte zależności pliku

- `PWE_System_Replace_Content_Page_Locator::find_by_url()`
- `self::get_wpml_translations()`
- `self::empty_result()`
- `self::prepare_job()`
- `self::process_page()`
- `self::add_page_result()`
- `PWE_System_Replace_Content_Page_Meta_Adapter::inspect_replacement_state()`
- `PWE_System_Replace_Content_Page_Meta_Adapter::set_uncode_header_none()`
- `PWE_System_Replace_Content_Page_Meta_Adapter::set_uncode_show_title_off()`
- `PWE_System_Replace_Content_Page_Meta_Adapter::verify_replacement_state()`
- `PWE_System_Replace_Content_Wpml_Gateway::get_element_type()`
- `PWE_System_Replace_Content_Wpml_Gateway::get_trid()`
- `PWE_System_Replace_Content_Wpml_Gateway::get_element_translations()`
- `self::add_translation()`
- `self::get_page_language()`
- `PWE_System_Replace_Content_Wpml_Gateway::get_element_language()`

## Uwagi

Dokładny graf wywołań buduje osobny Code Indexer z AST. Przy traitach call graph wymaga dodatkowej świadomości `use Trait` w klasie konsumującej.
