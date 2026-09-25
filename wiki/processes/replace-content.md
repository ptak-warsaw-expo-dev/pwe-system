---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Proces: Replace Content

1. `replace-content-map.php` definiuje URL → shortcode AutoSwitch.
2. `PWE_System_Replace_Content_Service::build_plan()` odnajduje stronę i jej tłumaczenia WPML.
3. `prepare_job()` deduplikuje posty, wykrywa konflikty i pomija strony już zgodne.
4. AJAX `pwe_replace_content_start` zapisuje job w transient.
5. Kolejne `pwe_replace_content_step` pobiera advisory lock MySQL (`GET_LOCK`) i wykonuje jedną pozycję.
6. `process_page()` aktualizuje `post_content`, ustawia Uncode header `none` i title `off`, czyści cache postu/WP Rocket i weryfikuje wynik.
7. Kursor i wynik są zapisywane po każdym kroku; inflight marker umożliwia recovery po utracie odpowiedzi.

Operacja wymaga `manage_options` i nonce `pwe_system_replace_content`.
