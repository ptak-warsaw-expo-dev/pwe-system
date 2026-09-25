---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Proces: QR backfill

Narzędzie `PWE_System_Forms_Backfill_Tool` jest osadzane na ekranie audytu.

1. `ajax_scan()` przegląda formularze z aktywnym feedem `pwe_qr` i liczy aktywne entries bez `pwe_qr_code_url`.
2. `ajax_generate()` pobiera pojedynczy formularz i partię brakujących entry IDs.
3. Dla każdego wpisu pobiera dane QR z generatora przekazanego przez PWE QR Gravity Forms.
4. Buduje podpisany URL obrazka (`pwe_qr_img=1`, HMAC z `wp_salt('auth')`).
5. Zapisuje `pwe_qr_code_url` oraz `pwe_qr_code_url_encoded` przez `gform_update_meta()`.
6. Po wygenerowaniu emituje `pwe_system_forms_audit_cache_invalidate`.

Backfill celowo nie uruchamia pełnego submission flow ani pozostałych integracji formularza.
