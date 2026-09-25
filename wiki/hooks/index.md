---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Hooki WordPress / Gravity Forms

Inventory obejmuje literalne rejestracje oraz rozwinięte, deterministyczne hooki AJAX budowane z map/stałych (DOC Manager i Replace Content). Callbacki zmienne runtime mogą pozostać nierozwiązane przez ogólny parser — dlatego `entrypoints.json` zachowuje jawne mapowanie techniczne endpointów.

| Typ | Hook | Callback | Źródło |
|---|---|---|---|
| `filter` | `admin_body_class` | `self::admin_body_class` | `core/class-pwe-system-admin.php:10` |
| `action` | `admin_enqueue_scripts` | `self::enqueue_assets` | `core/class-pwe-system-admin.php:9` |
| `action` | `admin_enqueue_scripts` | `self::enqueue_assets` | `modules/doc-manager/class-pwe-system-doc-manager.php:12` |
| `action` | `admin_enqueue_scripts` | `$this->enqueue_assets` | `modules/forms-audit/pwe-forms-audit-module.php:50` |
| `action` | `admin_enqueue_scripts` | `$this->enqueue_assets` | `modules/forms-audit/tools/class-pwe-system-forms-backfill-tool.php:22` |
| `action` | `admin_enqueue_scripts` | `$this->enqueue_admin_assets` | `modules/shortcodes/class-shortcodes.php:21` |
| `action` | `admin_init` | `self::handle` | `modules/resend/core/resend-actions.php:20` |
| `action` | `admin_init` | `$this->register_settings` | `modules/shortcodes/class-shortcodes.php:20` |
| `action` | `admin_menu` | `self::register_menu` | `core/class-pwe-system-admin.php:8` |
| `action` | `admin_menu` | `self::register_menu` | `modules/doc-manager/class-pwe-system-doc-manager.php:11` |
| `action` | `admin_menu` | `$this->register_submenu` | `modules/forms-audit/pwe-forms-audit-module.php:45` |
| `action` | `admin_menu` | `$this->add_menu` | `modules/shortcodes/class-shortcodes.php:19` |
| `action` | `admin_post_pwe_qr_export_mismatches` | `$this->export_mismatches_csv` | `modules/forms-audit/pwe-forms-audit-module.php:46` |
| `action` | `gform_after_email` | `$after_email_callback` | `modules/forms-audit/traits/trait-pwe-forms-audit-notifications.php:420` |
| `filter` | `gform_notification` | `$this->replace_multilang_date_in_notification` | `modules/shortcodes/class-shortcodes.php:29` |
| `filter` | `gform_replace_merge_tags` | `PWE_GF_shortcodes` | `modules/shortcodes/backend-shortcodes.php:257` |
| `filter` | `gform_replace_merge_tags` | `$this->replace_gf_merge_tags` | `modules/shortcodes/class-shortcodes.php:27` |
| `action` | `init` | `self::sync_capabilities` | `core/class-pwe-system.php:23` |
| `action` | `init` | `register_dynamic_shortcodes` | `modules/shortcodes/backend-shortcodes.php:255` |
| `action` | `init` | `$this->register_shortcodes` | `modules/shortcodes/class-shortcodes.php:23` |
| `action` | `phpmailer_init` | `$phpmailer_config` | `core/class-pwe-system-functions.php:4998` |
| `action` | `phpmailer_init` | `$phpmailer_config` | `core/class-pwe-system-functions.php:5089` |
| `action` | `plugins_loaded` | `$this->setup_updater` | `core/class-pwe-system-updater.php:12` |
| `action` | `plugins_loaded` | `PWE_System_Forms_Audit_Module::boot` | `modules/forms-audit/pwe-forms-audit-module.php:216` |
| `action` | `plugins_loaded` | `PWE_System::init` | `pwe-system.php:38` |
| `action` | `pwe_system_forms_audit_cache_invalidate` | `$this->clear_audit_session_cache` | `modules/forms-audit/pwe-forms-audit-module.php:51` |
| `action` | `pwe_system_forms_audit_tools` | `$this->render_audit_section` | `modules/forms-audit/tools/class-pwe-system-forms-backfill-tool.php:21` |
| `action` | `wp_ajax_pwe_qr_bulk_language_preview` | `$this->ajax_bulk_language_preview` | `modules/forms-audit/pwe-forms-audit-module.php:48` |
| `action` | `wp_ajax_pwe_qr_resend_notifications` | `$this->ajax_resend_notifications` | `modules/forms-audit/pwe-forms-audit-module.php:47` |
| `action` | `wp_ajax_pwe_replace_content_start` | `PWE_System_Replace_Content_Ajax::start` | `modules/replace-content/core/ajax-actions.php:20` |
| `action` | `wp_ajax_pwe_replace_content_step` | `PWE_System_Replace_Content_Ajax::step` | `modules/replace-content/core/ajax-actions.php:21` |
| `action` | `wp_ajax_pwe_system_doc_delete` | `PWE_System_Doc_Manager::ajax_delete` | `modules/doc-manager/class-pwe-system-doc-manager.php:26` |
| `action` | `wp_ajax_pwe_system_doc_list` | `PWE_System_Doc_Manager::ajax_list` | `modules/doc-manager/class-pwe-system-doc-manager.php:26` |
| `action` | `wp_ajax_pwe_system_doc_mkdir` | `PWE_System_Doc_Manager::ajax_mkdir` | `modules/doc-manager/class-pwe-system-doc-manager.php:26` |
| `action` | `wp_ajax_pwe_system_doc_move` | `PWE_System_Doc_Manager::ajax_move` | `modules/doc-manager/class-pwe-system-doc-manager.php:26` |
| `action` | `wp_ajax_pwe_system_doc_rename` | `PWE_System_Doc_Manager::ajax_rename` | `modules/doc-manager/class-pwe-system-doc-manager.php:26` |
| `action` | `wp_ajax_pwe_system_doc_replace` | `PWE_System_Doc_Manager::ajax_replace` | `modules/doc-manager/class-pwe-system-doc-manager.php:26` |
| `action` | `wp_ajax_pwe_system_doc_unzip` | `PWE_System_Doc_Manager::ajax_unzip` | `modules/doc-manager/class-pwe-system-doc-manager.php:26` |
| `action` | `wp_ajax_pwe_system_doc_upload` | `PWE_System_Doc_Manager::ajax_upload` | `modules/doc-manager/class-pwe-system-doc-manager.php:26` |
| `action` | `wp_ajax_pwe_system_forms_audit_load` | `$this->ajax_load_audit` | `modules/forms-audit/pwe-forms-audit-module.php:49` |
| `action` | `wp_ajax_pwe_system_forms_backfill_generate` | `$this->ajax_generate` | `modules/forms-audit/tools/class-pwe-system-forms-backfill-tool.php:24` |
| `action` | `wp_ajax_pwe_system_forms_backfill_scan` | `$this->ajax_scan` | `modules/forms-audit/tools/class-pwe-system-forms-backfill-tool.php:23` |
| `action` | `wp_footer` | `PWE_System_Functions::output_db_connection_logs` | `core/class-pwe-system-functions.php:5100` |
| `action` | `wp_mail_failed` | `$capture` | `modules/resend/core/job-runner.php:253` |
| `filter` | `wpdb_connect_timeout` | `self::set_db_timeout` | `core/class-pwe-system-functions.php:818` |
| `filter` | `wpseo_register_extra_replacements` | `$this->wpseo_register_extra_replacements` | `modules/shortcodes/class-shortcodes.php:25` |
| `filter` | `wpseo_replacements` | `$this->wpseo_replacements` | `modules/shortcodes/class-shortcodes.php:26` |
