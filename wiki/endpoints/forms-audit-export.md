---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# `forms-audit-export`

- **Typ:** `admin-post`
- **Trigger/route:** `admin-post.php?action=pwe_qr_export_mismatches`
- **Metody:** `GET`
- **Źródło:** `modules/forms-audit/traits/trait-pwe-forms-audit-export.php`
- **Callback:** `PWE_System_Forms_Audit_Tool::export_mismatches_csv`
- **Hook:** `admin_post_pwe_qr_export_mismatches`
- **Autoryzacja wg kodu:** manage_options + admin nonce pwe_qr_export_mismatches.

## Uwagi

Runtime callback jest metodą klasy przez trait; definicja: PWE_System_Forms_Audit_Export_Trait::export_mismatches_csv.

## Nawigacja techniczna

Dalszy przepływ należy śledzić od callbacku/scope pliku przez Symbol → Calls / Called by. Dla entrypointów cross-plugin pomocne są `inventory/integrations.json` i `dependencies.json`.
