---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Proces: `PWE_Shortcodes`

Klasa zachowuje nazwę z AutoSwitch dla kompatybilności. Konstruktor rejestruje menu/settings, `register_shortcodes()` na `init`, integrację Yoast i dwa filtry Gravity Forms.

`register_shortcodes()` usuwa istniejące tagi o tych samych nazwach i rejestruje własną mapę callbacków. Następnie tworzy dynamiczne `url_*` z danych `website-translation.json`.

Warstwa obejmuje dane targów, daty, katalog, konferencje, dni montażu/demontażu, kontakty, social media, benefity, generator wystawców, teksty SEO oraz nagłówki mailingowe.
