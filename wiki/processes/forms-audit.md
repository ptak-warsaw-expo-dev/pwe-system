---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Proces: audyt formularzy, QR i powiadomień

## Boot

`PWE_System_Forms_Audit_Module::boot()` wymaga aktywnego `PWE_QR_Gravity_Forms` i obiektu `$plugin->qr`. Tworzy `PWE_System_Forms_Audit_Tool` oraz `PWE_System_Forms_Backfill_Tool`.

## Odczyt audytu

AJAX `pwe_system_forms_audit_load` pobiera formularze GF, ogranicza się do formularzy z feedami PWE QR, buduje/odświeża cache sesyjny audytu i renderuje tabele formularzy oraz entries.

Audyt porównuje konfigurację feedów, zapisany QR, wyliczoną wartość, język, aktywne powiadomienia, notatki GF i meta resendu.

## Akcje

- bulk language preview grupuje entries według zestawu oczekiwanych powiadomień,
- resend notifications wysyła małe partie i ponownie weryfikuje zestaw notification IDs,
- export mismatches generuje CSV przez `admin-post`.

Akcje wymagają `manage_options` i odpowiednich nonce.
