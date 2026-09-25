---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Proces: bootstrap i zgodność PWE

1. WordPress ładuje `pwe-system.php`.
2. Ładowane są `PWE_System_Functions`, rdzeń i updater.
3. Na `plugins_loaded` priority 20 `PWE_System::init()` uruchamia moduły.
4. `PWE_System_Functions` tworzy alias `PWE_Functions`, jeżeli starsza klasa nie istnieje.
5. Backend `[pwe_*]` jest dołączany tylko, gdy nie istnieje `pwe_get_shortcode_map()`.
6. `PWE_Shortcodes` jest dołączany tylko, gdy nie istnieje klasa o tej nazwie.
7. Forms Audit próbuje podpiąć generator QR z PWE QR Gravity Forms.

To jest główny proces migracyjno-kompatybilnościowy: system przejmuje implementacje, ale stara się nie powodować redeklaracji na stronach z wcześniejszymi pluginami.
