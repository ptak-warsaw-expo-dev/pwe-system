---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Bootstrap i lifecycle

## Start pluginu

`pwe-system.php`:

1. blokuje bezpośrednie wykonanie bez `ABSPATH`,
2. definiuje `PWE_SYSTEM_VERSION`, `PWE_SYSTEM_FILE`, `PWE_SYSTEM_PATH`, `PWE_SYSTEM_URL` i `PWE_LANG`,
3. ładuje `PWE_System_Functions`, `PWE_System` i updater,
4. tworzy `PWE_System_Updater`,
5. rejestruje `PWE_System::activate()` jako activation hook,
6. rejestruje `PWE_System::init()` na `plugins_loaded` z priorytetem 20.

## `PWE_System::init()`

Metoda jest idempotentna. Ładuje admin access/UI/admin, DOC Manager, Replace Content i Resend, synchronizuje capability oraz inicjalizuje moduły. Następnie dołącza Forms Audit i warunkowo warstwę shortcode.

## Aktywacja

`PWE_System::activate()` synchronizuje capability, zapewnia katalog `/doc` i tworzy tabelę logów DOC Managera.

## Capability

`sync_capabilities()` nadaje `pwe_manage_doc` rolom `administrator` i `logotype_edytor`; dodatkowo synchronizacja wraca na hooku `init` priority 99.
