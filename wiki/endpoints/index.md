---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Endpointy i handlery HTTP

`endpoints.json` obejmuje bezpośrednie API, AJAX, `admin-post` oraz formularzowe wejście Resend.

| ID | Typ | Trigger | Callback / entrypoint | Źródło |
|---|---|---|---|---|
| [`cap-graphics`](cap-graphics.md) | `direct-http` | `/wp-content/plugins/pwe-system/api/cap/doc.php` | `file scope` | `api/cap/doc.php` |
| [`news-sync`](news-sync.md) | `direct-http` | `/wp-content/plugins/pwe-system/api/news/index.php` | `file scope` | `api/news/index.php` |
| [`doc-list`](doc-list.md) | `wp-ajax` | `admin-ajax.php?action=pwe_system_doc_list` | `PWE_System_Doc_Manager::ajax_list` | `modules/doc-manager/class-pwe-system-doc-manager.php` |
| [`doc-mkdir`](doc-mkdir.md) | `wp-ajax` | `admin-ajax.php?action=pwe_system_doc_mkdir` | `PWE_System_Doc_Manager::ajax_mkdir` | `modules/doc-manager/class-pwe-system-doc-manager.php` |
| [`doc-rename`](doc-rename.md) | `wp-ajax` | `admin-ajax.php?action=pwe_system_doc_rename` | `PWE_System_Doc_Manager::ajax_rename` | `modules/doc-manager/class-pwe-system-doc-manager.php` |
| [`doc-delete`](doc-delete.md) | `wp-ajax` | `admin-ajax.php?action=pwe_system_doc_delete` | `PWE_System_Doc_Manager::ajax_delete` | `modules/doc-manager/class-pwe-system-doc-manager.php` |
| [`doc-move`](doc-move.md) | `wp-ajax` | `admin-ajax.php?action=pwe_system_doc_move` | `PWE_System_Doc_Manager::ajax_move` | `modules/doc-manager/class-pwe-system-doc-manager.php` |
| [`doc-upload`](doc-upload.md) | `wp-ajax` | `admin-ajax.php?action=pwe_system_doc_upload` | `PWE_System_Doc_Manager::ajax_upload` | `modules/doc-manager/class-pwe-system-doc-manager.php` |
| [`doc-replace`](doc-replace.md) | `wp-ajax` | `admin-ajax.php?action=pwe_system_doc_replace` | `PWE_System_Doc_Manager::ajax_replace` | `modules/doc-manager/class-pwe-system-doc-manager.php` |
| [`doc-unzip`](doc-unzip.md) | `wp-ajax` | `admin-ajax.php?action=pwe_system_doc_unzip` | `PWE_System_Doc_Manager::ajax_unzip` | `modules/doc-manager/class-pwe-system-doc-manager.php` |
| [`replace-content-start`](replace-content-start.md) | `wp-ajax` | `admin-ajax.php?action=pwe_replace_content_start` | `PWE_System_Replace_Content_Ajax::start` | `modules/replace-content/core/ajax-actions.php` |
| [`replace-content-step`](replace-content-step.md) | `wp-ajax` | `admin-ajax.php?action=pwe_replace_content_step` | `PWE_System_Replace_Content_Ajax::step` | `modules/replace-content/core/ajax-actions.php` |
| [`forms-audit-load`](forms-audit-load.md) | `wp-ajax` | `admin-ajax.php?action=pwe_system_forms_audit_load` | `PWE_System_Forms_Audit_Tool::ajax_load_audit` | `modules/forms-audit/pwe-forms-audit-module.php` |
| [`forms-audit-resend`](forms-audit-resend.md) | `wp-ajax` | `admin-ajax.php?action=pwe_qr_resend_notifications` | `PWE_System_Forms_Audit_Tool::ajax_resend_notifications` | `modules/forms-audit/traits/trait-pwe-forms-audit-notifications.php` |
| [`forms-audit-language-preview`](forms-audit-language-preview.md) | `wp-ajax` | `admin-ajax.php?action=pwe_qr_bulk_language_preview` | `PWE_System_Forms_Audit_Tool::ajax_bulk_language_preview` | `modules/forms-audit/traits/trait-pwe-forms-audit-notifications.php` |
| [`forms-audit-export`](forms-audit-export.md) | `admin-post` | `admin-post.php?action=pwe_qr_export_mismatches` | `PWE_System_Forms_Audit_Tool::export_mismatches_csv` | `modules/forms-audit/traits/trait-pwe-forms-audit-export.php` |
| [`forms-backfill-scan`](forms-backfill-scan.md) | `wp-ajax` | `admin-ajax.php?action=pwe_system_forms_backfill_scan` | `PWE_System_Forms_Backfill_Tool::ajax_scan` | `modules/forms-audit/tools/class-pwe-system-forms-backfill-tool.php` |
| [`forms-backfill-generate`](forms-backfill-generate.md) | `wp-ajax` | `admin-ajax.php?action=pwe_system_forms_backfill_generate` | `PWE_System_Forms_Backfill_Tool::ajax_generate` | `modules/forms-audit/tools/class-pwe-system-forms-backfill-tool.php` |
| [`resend-admin-action`](resend-admin-action.md) | `admin-form` | `admin.php?page=pwe-system-resend + POST pwe_resend_action` | `PWE_System_Resend_Actions::handle` | `modules/resend/core/resend-actions.php` |
