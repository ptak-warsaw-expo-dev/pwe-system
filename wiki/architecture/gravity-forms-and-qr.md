---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Gravity Forms, QR i powiadomienia

PWE System wykorzystuje Gravity Forms w dwóch głównych obszarach: Resend oraz Forms Audit.

Resend działa bez PWE QR Gravity Forms: pobiera formularze/wpisy przez GFAPI, dopasowuje powiadomienia i uruchamia `GFCommon::send_notification(s)`.

Forms Audit jest zależny od PWE QR Gravity Forms, ponieważ pobiera z niego obiekt generatora QR. Audyt czyta feedy `pwe_qr`, zapisane metadane QR i historię powiadomień. Narzędzie backfill generuje brakujące `pwe_qr_code_url` bez ponownego uruchamiania całego submission flow.
