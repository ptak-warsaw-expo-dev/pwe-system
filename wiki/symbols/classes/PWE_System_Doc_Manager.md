# Class `PWE_System_Doc_Manager`

**Źródło:** `modules/doc-manager/class-pwe-system-doc-manager.php:6`  
**Typ:** `class`  
**Metody:** 38

## Rola

Symbol jest zdefiniowany w `modules/doc-manager/class-pwe-system-doc-manager.php`. Status runtime pliku można sprawdzić w `inventory/load-graph.json`.

## Metody

- [`public static init()`](../methods/PWE_System_Doc_Manager/init.md) — linia 10
- [`public static register_menu()`](../methods/PWE_System_Doc_Manager/register_menu.md) — linia 30
- [`public static enqueue_assets(string $hook)`](../methods/PWE_System_Doc_Manager/enqueue_assets.md) — linia 41
- [`public static capability()`](../methods/PWE_System_Doc_Manager/capability.md) — linia 73
- [`public static ensure_doc_directory()`](../methods/PWE_System_Doc_Manager/ensure_doc_directory.md) — linia 77
- [`public static install_log_table()`](../methods/PWE_System_Doc_Manager/install_log_table.md) — linia 84
- [`public static render_page()`](../methods/PWE_System_Doc_Manager/render_page.md) — linia 105
- [`public static ajax_list()`](../methods/PWE_System_Doc_Manager/ajax_list.md) — linia 192
- [`public static ajax_mkdir()`](../methods/PWE_System_Doc_Manager/ajax_mkdir.md) — linia 250
- [`public static ajax_rename()`](../methods/PWE_System_Doc_Manager/ajax_rename.md) — linia 267
- [`public static ajax_delete()`](../methods/PWE_System_Doc_Manager/ajax_delete.md) — linia 291
- [`public static ajax_move()`](../methods/PWE_System_Doc_Manager/ajax_move.md) — linia 304
- [`public static ajax_upload()`](../methods/PWE_System_Doc_Manager/ajax_upload.md) — linia 328
- [`public static ajax_replace()`](../methods/PWE_System_Doc_Manager/ajax_replace.md) — linia 333
- [`public static ajax_unzip()`](../methods/PWE_System_Doc_Manager/ajax_unzip.md) — linia 338
- [`private static handle_upload(bool $force_replace)`](../methods/PWE_System_Doc_Manager/handle_upload.md) — linia 509
- [`private static guard()`](../methods/PWE_System_Doc_Manager/guard.md) — linia 564
- [`private static base_dir()`](../methods/PWE_System_Doc_Manager/base_dir.md) — linia 572
- [`private static base_real()`](../methods/PWE_System_Doc_Manager/base_real.md) — linia 576
- [`private static clean_rel(string $path)`](../methods/PWE_System_Doc_Manager/clean_rel.md) — linia 582
- [`private static clean_name(string $name)`](../methods/PWE_System_Doc_Manager/clean_name.md) — linia 600
- [`private static resolve_existing(string $rel, bool $must_be_dir)`](../methods/PWE_System_Doc_Manager/resolve_existing.md) — linia 609
- [`private static ensure_directory_path(string $rel)`](../methods/PWE_System_Doc_Manager/ensure_directory_path.md) — linia 618
- [`private static path_is_inside(string $path, string $parent, bool $allow_same = false)`](../methods/PWE_System_Doc_Manager/path_is_inside.md) — linia 628
- [`private static parent_rel(string $rel)`](../methods/PWE_System_Doc_Manager/parent_rel.md) — linia 635
- [`private static join_rel(string $a, string $b)`](../methods/PWE_System_Doc_Manager/join_rel.md) — linia 640
- [`private static is_allowed_filename(string $name)`](../methods/PWE_System_Doc_Manager/is_allowed_filename.md) — linia 644
- [`private static image_dimensions(string $path, string $extension)`](../methods/PWE_System_Doc_Manager/image_dimensions.md) — linia 657
- [`private static svg_dimension(string $svg, string $attribute)`](../methods/PWE_System_Doc_Manager/svg_dimension.md) — linia 694
- [`private static clean_zip_entry(string $name)`](../methods/PWE_System_Doc_Manager/clean_zip_entry.md) — linia 702
- [`private static zip_entry_is_symlink(ZipArchive $zip, int $index)`](../methods/PWE_System_Doc_Manager/zip_entry_is_symlink.md) — linia 730
- [`private static delete_tree(string $path)`](../methods/PWE_System_Doc_Manager/delete_tree.md) — linia 746
- [`private static breadcrumbs(string $dir)`](../methods/PWE_System_Doc_Manager/breadcrumbs.md) — linia 757
- [`private static file_url(string $rel)`](../methods/PWE_System_Doc_Manager/file_url.md) — linia 768
- [`private static post(string $key)`](../methods/PWE_System_Doc_Manager/post.md) — linia 773
- [`private static error(string $message, int $status = 400)`](../methods/PWE_System_Doc_Manager/error.md) — linia 777
- [`private static upload_error(int $code)`](../methods/PWE_System_Doc_Manager/upload_error.md) — linia 781
- [`private static log(string $action, ?string $source, ?string $target, array $details = [])`](../methods/PWE_System_Doc_Manager/log.md) — linia 801

## Dokument pliku

- [Otwórz dokumentację `modules/doc-manager/class-pwe-system-doc-manager.php`](../../files/modules/doc-manager/class-pwe-system-doc-manager.php.md)
