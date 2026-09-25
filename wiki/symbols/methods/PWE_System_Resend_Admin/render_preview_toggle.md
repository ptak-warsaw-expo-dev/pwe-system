# `PWE_System_Resend_Admin::render_preview_toggle()`

**Źródło:** `modules/resend/core/admin-page.php:77`  
**Typ właściciela:** `class`  
**Sygnatura:** `private static render_preview_toggle(bool $preview_all)`

## Kontekst

- Symbol właściciela: [`PWE_System_Resend_Admin`](../../classes/PWE_System_Resend_Admin.md)
- Plik: [dokument pliku](../../../files/modules/resend/core/admin-page.php.md)

## Wykryte zależności pliku

- `PWE_System_Admin_Access::is_allowed()`
- `PWE_System_Resend_Job::get()`
- `self::render_job_screen()`
- `self::render_start_screen()`
- `GFAPI::get_forms()`
- `self::render_preview_toggle()`
- `self::build_form_rows()`
- `self::render_settings_bar()`
- `self::render_form_card()`
- `PWE_System_Admin_UI::button()`
- `PWE_System_Resend_Actions::admin_url()`
- `GFAPI::get_form()`
- `PWE_System_Resend_Gravity::get_resend_notifications()`
- `PWE_System_Resend_Gravity::scan_form_stats()`
- `self::group_notifications_for_display()`
- `PWE_System_Admin_UI::checkbox()`
- `self::render_notification_groups()`
- `PWE_System_Resend_Job::stats()`
- `self::render_job_actions()`
- `PWE_System_Resend_Actions::action_form()`
- `self::render_recent_log()`
- `PWE_System_Admin_UI::status()`
- `PWE_System_Resend_Matcher::extract_lang_from_notification_name()`
- `self::strip_lang_suffix()`
- `self::notification_lang_count()`

## Uwagi

Dokładny graf wywołań buduje osobny Code Indexer z AST. Przy traitach call graph wymaga dodatkowej świadomości `use Trait` w klasie konsumującej.
