---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Architektura — overview

## Warstwy

1. `pwe-system.php` definiuje stałe, ładuje warstwę funkcji, rdzeń i updater.
2. `PWE_System::init()` na `plugins_loaded` ładuje administrację i moduły.
3. DOC Manager, Replace Content i Resend są inicjalizowane bezpośrednio.
4. Forms Audit jest ładowany, lecz jego narzędzia uruchamiają się dopiero po znalezieniu `PWE_QR_Gravity_Forms` z obiektem generatora QR.
5. Proceduralne `[pwe_*]` i `PWE_Shortcodes` są ładowane warunkowo, aby nie redeklarować starszych implementacji.
6. `api/cap/doc.php` i `api/news/index.php` są niezależnymi file-entrypointami HTTP.

## Granice systemu

PWE System jest centralnym pluginem, ale nie jest samowystarczalny dla wszystkich funkcji. `PWE_System_Functions` nadal operuje na elementach/components z AutoSwitch, Forms Audit używa generatora z PWE QR Gravity Forms, a PWE Elements może delegować tutaj swoją warstwę funkcji i shortcode'ów.
