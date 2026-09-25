# `core/class-pwe-system-admin.php`

Plik first-party PWE System w kategorii `core`.

## Metadane

- **Kategoria:** `core`
- **Rozmiar:** 11753 B
- **Liczba linii:** 256
- **Źródło:** `core/class-pwe-system-admin.php`

## Typy i metody

### class `PWE_System_Admin` — linia 6

  - `public static init()` — linia 7
  - `public static register_menu()` — linia 13
  - `public static render_replace_content()` — linia 56
  - `public static render_resend()` — linia 79
  - `public static render_module_header(string $kicker, string $title, string $description, string $icon = '')` — linia 102
  - `public static enqueue_assets(string $hook)` — linia 120
  - `public static admin_body_class(string $classes)` — linia 158
  - `public static render_dashboard()` — linia 166

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- **action:** `admin_menu` — linia 8
- **action:** `admin_enqueue_scripts` — linia 9
- **filter:** `admin_body_class` — linia 10

## Wybrane wywołania statyczne

- `self::render_module_header()`
- `PWE_System_Replace_Content::render_admin_page()`
- `PWE_System_Resend::render_admin_page()`
- `PWE_System_Forms_Audit_Module::is_ready()`

## API WordPress / GF rozpoznane heurystycznie

- `add_action()`
- `add_filter()`
- `current_user_can()`
- `wp_die()`
- `esc_url()`
- `esc_html()`
- `esc_attr()`
- `wp_enqueue_style()`
- `sanitize_key()`
- `wp_enqueue_script()`
- `admin_url()`
- `plugins_url()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- Brak statycznie rozpoznanych.

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
