---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Kandydaci do cross-plugin graphu

## PWE System → AutoSwitch

- `PWE_System_Functions::elements_plugin_path()` / `elements_plugin_url()` wskazują na AutoSwitch,
- renderowanie components/elements korzysta z plików AutoSwitch,
- Replace Content zapisuje shortcody `pwe-elements-auto-switch-page-*`.

## AutoSwitch → PWE System

W wersjach migracyjnych AutoSwitch może bridge'ować `api/cap/doc.php` i `api/news/index.php` do PWE System. To relacja przychodząca, więc wymaga globalnego indeksu wielu pluginów.

## PWE System → PWE QR Gravity Forms

`PWE_System_Forms_Audit_Module::boot()` wywołuje `PWE_QR_Gravity_Forms::get_instance()` i konsumuje `$plugin->qr`.

## PWE Elements → PWE System

PWE Elements może delegować wspólne funkcje i backend `[pwe_*]` do PWE System. Alias `PWE_Functions` jest utrzymany właśnie pod ten kontrakt.
