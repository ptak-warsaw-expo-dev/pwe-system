# `modules/doc-manager/class-pwe-system-doc-manager.php`

Plik first-party PWE System w kategorii `doc-manager`.

## Metadane

- **Kategoria:** `doc-manager`
- **Rozmiar:** 34126 B
- **Liczba linii:** 822
- **Źródło:** `modules/doc-manager/class-pwe-system-doc-manager.php`

## Typy i metody

### class `PWE_System_Doc_Manager` — linia 6

  - `public static init()` — linia 10
  - `public static register_menu()` — linia 30
  - `public static enqueue_assets(string $hook)` — linia 41
  - `public static capability()` — linia 73
  - `public static ensure_doc_directory()` — linia 77
  - `public static install_log_table()` — linia 84
  - `public static render_page()` — linia 105
  - `public static ajax_list()` — linia 192
  - `public static ajax_mkdir()` — linia 250
  - `public static ajax_rename()` — linia 267
  - `public static ajax_delete()` — linia 291
  - `public static ajax_move()` — linia 304
  - `public static ajax_upload()` — linia 328
  - `public static ajax_replace()` — linia 333
  - `public static ajax_unzip()` — linia 338
  - `private static handle_upload(bool $force_replace)` — linia 509
  - `private static guard()` — linia 564
  - `private static base_dir()` — linia 572
  - `private static base_real()` — linia 576
  - `private static clean_rel(string $path)` — linia 582
  - `private static clean_name(string $name)` — linia 600
  - `private static resolve_existing(string $rel, bool $must_be_dir)` — linia 609
  - `private static ensure_directory_path(string $rel)` — linia 618
  - `private static path_is_inside(string $path, string $parent, bool $allow_same = false)` — linia 628
  - `private static parent_rel(string $rel)` — linia 635
  - `private static join_rel(string $a, string $b)` — linia 640
  - `private static is_allowed_filename(string $name)` — linia 644
  - `private static image_dimensions(string $path, string $extension)` — linia 657
  - `private static svg_dimension(string $svg, string $attribute)` — linia 694
  - `private static clean_zip_entry(string $name)` — linia 702
  - `private static zip_entry_is_symlink(ZipArchive $zip, int $index)` — linia 730
  - `private static delete_tree(string $path)` — linia 746
  - `private static breadcrumbs(string $dir)` — linia 757
  - `private static file_url(string $rel)` — linia 768
  - `private static post(string $key)` — linia 773
  - `private static error(string $message, int $status = 400)` — linia 777
  - `private static upload_error(int $code)` — linia 781
  - `private static log(string $action, ?string $source, ?string $target, array $details = [])` — linia 801

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- **action:** `admin_menu` — linia 11
- **action:** `admin_enqueue_scripts` — linia 12
- **action:** `wp_ajax_pwe_system_doc_delete` — linia 26
- **action:** `wp_ajax_pwe_system_doc_list` — linia 26
- **action:** `wp_ajax_pwe_system_doc_mkdir` — linia 26
- **action:** `wp_ajax_pwe_system_doc_move` — linia 26
- **action:** `wp_ajax_pwe_system_doc_rename` — linia 26
- **action:** `wp_ajax_pwe_system_doc_replace` — linia 26
- **action:** `wp_ajax_pwe_system_doc_unzip` — linia 26
- **action:** `wp_ajax_pwe_system_doc_upload` — linia 26

## Wybrane wywołania statyczne

- `self::capability()`
- `self::base_dir()`
- `self::ensure_doc_directory()`
- `self::guard()`
- `self::clean_rel()`
- `self::post()`
- `self::resolve_existing()`
- `self::error()`
- `self::image_dimensions()`
- `self::file_url()`
- `self::breadcrumbs()`
- `self::clean_name()`
- `self::log()`
- `self::join_rel()`
- `self::is_allowed_filename()`
- `self::parent_rel()`
- `self::delete_tree()`
- `self::path_is_inside()`
- `self::handle_upload()`
- `self::ensure_directory_path()`
- `self::clean_zip_entry()`
- `self::zip_entry_is_symlink()`
- `self::base_real()`
- `self::upload_error()`
- `self::svg_dimension()`
- `self::install_log_table()`

## API WordPress / GF rozpoznane heurystycznie

- `add_action()`
- `wp_enqueue_style()`
- `wp_enqueue_script()`
- `wp_localize_script()`
- `admin_url()`
- `wp_create_nonce()`
- `trailingslashit()`
- `site_url()`
- `wp_max_upload_size()`
- `apply_filters()`
- `wp_mkdir_p()`
- `dbDelta()`
- `current_user_can()`
- `wp_die()`
- `esc_url()`
- `wp_date()`
- `wp_send_json_success()`
- `sanitize_file_name()`
- `check_ajax_referer()`
- `wp_unslash()`
- `wp_getimagesize()`
- `wp_send_json_error()`
- `wp_get_current_user()`
- `get_current_user_id()`
- `wp_json_encode()`

## Dołączane pliki / wyrażenia include

- `_once ABSPATH . 'wp-admin/includes/upgrade.php'`

## Tabele / źródła SQL

- `prefix.LOG_TABLE_SUFFIX`

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
