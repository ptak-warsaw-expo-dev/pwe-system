# PWE System Functions

`core/class-pwe-system-functions.php` is the entry point. It loads the traits below
and composes them into the existing `PWE_System_Functions` class.
The `PWE_Functions` compatibility alias and `wp_footer` diagnostics hook are preserved.

## Installation

Copy the archive contents into the existing `pwe-system` plugin directory.
Replace `core/class-pwe-system-functions.php` and add the complete `core/functions/`
directory together. The loader requires every listed trait.
The plugin bootstrap and existing callers need no changes.

## File map

All paths below are relative to `core/functions/`.

| File | Responsibility | Methods |
| --- | --- | ---: |
| [database-connection.php](database-connection.php) | CAP database server selection, connection reuse and timeouts. | 4 |
| [database-cache.php](database-cache.php) | Persistent JSON cache, serialization and cron refresh. | 7 |
| [database-data.php](database-data.php) | All CAP database getters and their runtime caches. | 27 |
| [elements.php](elements.php) | Element paths, asset loading and component rendering. | 5 |
| [translations.php](translations.php) | Translation context, JSON translations, locale and URL maps. | 7 |
| [gravity-forms.php](gravity-forms.php) | Gravity Forms lookup, form lists and SMTP incident monitoring. | 4 |
| [sessions.php](sessions.php) | Registration page detection for session handling. | 1 |
| [logging.php](logging.php) | File logging and aggregated database console diagnostics. | 3 |
| [media.php](media.php) | Exhibitor catalogs, logo deduplication and image file discovery. | 5 |
| [colors.php](colors.php) | Fair colors, Uncode palettes and brightness adjustment. | 5 |
| [fairs.php](fairs.php) | Fair data normalization, translated fields and combined fair JSON. | 3 |
| [dates.php](dates.php) | Date range formatting and seasonal date placeholder detection. | 2 |
| [utilities.php](utilities.php) | Content decoding, random identifiers and mobile user-agent detection. | 4 |
| [wpbakery.php](wpbakery.php) | WPBakery range parameter registration and HTML rendering. | 2 |

## Compatibility and maintenance

- All 79 original methods and 33 original properties remain members of
  `PWE_System_Functions`; traits do not create separate helper objects.
- Preserve existing `public`, `private`, static and instance declarations.
  `findFormsGF()` and `findPalletColors()` remain instance methods.
- Database getters and their per-source runtime caches live in `database-data.php`.
  Shared connection state lives in `database-connection.php`; forced-refresh state
  and persistent cache handling live in `database-cache.php`.
- Methods can continue calling private helpers in other traits through `self::`.
- Document methods and properties with English PHPDoc blocks. Use short English
  `//` comments for implementation steps, with no decorative banner separators.
- When adding a method, select its existing subject file and update this index.
  If adding a new trait, also add its require and use statements to the entry point.
- Runtime strings, SQL, email content and existing behavior are retained.

## Method index

| Method | File |
| --- | --- |
| `add_log()` | [logging.php](logging.php) |
| `adjustBrightness()` | [colors.php](colors.php) |
| `assets_per_element()` | [elements.php](elements.php) |
| `assets_per_group()` | [elements.php](elements.php) |
| `checkForMobile()` | [utilities.php](utilities.php) |
| `connect_database()` | [database-connection.php](database-connection.php) |
| `debug_log()` | [logging.php](logging.php) |
| `decode_clean_content()` | [utilities.php](utilities.php) |
| `elements_plugin_path()` | [elements.php](elements.php) |
| `elements_plugin_url()` | [elements.php](elements.php) |
| `exhibitor_logos()` | [media.php](media.php) |
| `findAllImages()` | [media.php](media.php) |
| `findBestFile()` | [media.php](media.php) |
| `findBestLogo()` | [media.php](media.php) |
| `findColor()` | [colors.php](colors.php) |
| `findFormsGF()` | [gravity-forms.php](gravity-forms.php) |
| `findFormsID()` | [gravity-forms.php](gravity-forms.php) |
| `findPalletColors()` | [colors.php](colors.php) |
| `findPalletColorsStatic()` | [colors.php](colors.php) |
| `generate_fair_data()` | [fairs.php](fairs.php) |
| `generate_fair_translation_data()` | [fairs.php](fairs.php) |
| `get_all_week_domains()` | [database-data.php](database-data.php) |
| `get_database_associates_data()` | [database-data.php](database-data.php) |
| `get_database_conference_adds_data()` | [database-data.php](database-data.php) |
| `get_database_conferences_data()` | [database-data.php](database-data.php) |
| `get_database_elements_data()` | [database-data.php](database-data.php) |
| `get_database_elements_order_data()` | [database-data.php](database-data.php) |
| `get_database_fairs_data()` | [database-data.php](database-data.php) |
| `get_database_fairs_data_adds()` | [database-data.php](database-data.php) |
| `get_database_fairs_data_attractions()` | [database-data.php](database-data.php) |
| `get_database_fairs_data_files()` | [database-data.php](database-data.php) |
| `get_database_fairs_data_guests()` | [database-data.php](database-data.php) |
| `get_database_fairs_data_opinions()` | [database-data.php](database-data.php) |
| `get_database_fairs_data_profiles()` | [database-data.php](database-data.php) |
| `get_database_fairs_data_sectors()` | [database-data.php](database-data.php) |
| `get_database_fairs_data_speakers()` | [database-data.php](database-data.php) |
| `get_database_fairs_data_tickets()` | [database-data.php](database-data.php) |
| `get_database_groups_callcenter_data()` | [database-data.php](database-data.php) |
| `get_database_groups_contacts_data()` | [database-data.php](database-data.php) |
| `get_database_groups_data()` | [database-data.php](database-data.php) |
| `get_database_json_cache_dir()` | [database-cache.php](database-cache.php) |
| `get_database_json_cache_path()` | [database-cache.php](database-cache.php) |
| `get_database_logotypes_data()` | [database-data.php](database-data.php) |
| `get_database_meta_data()` | [database-data.php](database-data.php) |
| `get_database_premieres_data()` | [database-data.php](database-data.php) |
| `get_database_servers()` | [database-connection.php](database-connection.php) |
| `get_database_store_data()` | [database-data.php](database-data.php) |
| `get_database_store_packages_data()` | [database-data.php](database-data.php) |
| `get_database_translations_data()` | [database-data.php](database-data.php) |
| `get_database_week_all()` | [database-data.php](database-data.php) |
| `get_database_week_data()` | [database-data.php](database-data.php) |
| `get_gf_form_id()` | [gravity-forms.php](gravity-forms.php) |
| `get_website_translation_files()` | [translations.php](translations.php) |
| `gravity_forms_smtp_monitor()` | [gravity-forms.php](gravity-forms.php) |
| `id_rnd()` | [utilities.php](utilities.php) |
| `input_range_field_html()` | [wpbakery.php](wpbakery.php) |
| `inputRange()` | [wpbakery.php](wpbakery.php) |
| `is_pwe_session_page()` | [sessions.php](sessions.php) |
| `isTradeDateExist()` | [dates.php](dates.php) |
| `json_decode()` | [utilities.php](utilities.php) |
| `json_fairs()` | [fairs.php](fairs.php) |
| `lang()` | [translations.php](translations.php) |
| `lang_pl()` | [translations.php](translations.php) |
| `languageChecker()` | [translations.php](translations.php) |
| `load_translation_file()` | [translations.php](translations.php) |
| `multi_translation()` | [translations.php](translations.php) |
| `output_db_connection_logs()` | [logging.php](logging.php) |
| `pack_database_json_value()` | [database-cache.php](database-cache.php) |
| `pwe_color()` | [colors.php](colors.php) |
| `read_database_json_cache()` | [database-cache.php](database-cache.php) |
| `refresh_database_json_cache()` | [database-cache.php](database-cache.php) |
| `remove_logo_duplicates()` | [media.php](media.php) |
| `render_component()` | [elements.php](elements.php) |
| `resolve_server_addr_fallback()` | [database-connection.php](database-connection.php) |
| `set_db_timeout()` | [database-connection.php](database-connection.php) |
| `set_translation_context()` | [translations.php](translations.php) |
| `transform_dates()` | [dates.php](dates.php) |
| `unpack_database_json_value()` | [database-cache.php](database-cache.php) |
| `write_database_json_cache()` | [database-cache.php](database-cache.php) |

## Property index

| Property | File |
| --- | --- |
| `$all_week_domains_cache` | [database-data.php](database-data.php) |
| `$associates_cache` | [database-data.php](database-data.php) |
| `$cached_db_connection` | [database-connection.php](database-connection.php) |
| `$conference_adds_cache` | [database-data.php](database-data.php) |
| `$conferences_cache` | [database-data.php](database-data.php) |
| `$database_cache_force_refresh` | [database-cache.php](database-cache.php) |
| `$debug_logs` | [logging.php](logging.php) |
| `$elements_cache` | [database-data.php](database-data.php) |
| `$elements_order_cache` | [database-data.php](database-data.php) |
| `$fairs_adds_cache` | [database-data.php](database-data.php) |
| `$fairs_attractions_cache` | [database-data.php](database-data.php) |
| `$fairs_cache` | [database-data.php](database-data.php) |
| `$fairs_files_cache` | [database-data.php](database-data.php) |
| `$fairs_guests_cache` | [database-data.php](database-data.php) |
| `$fairs_opinions_cache` | [database-data.php](database-data.php) |
| `$fairs_profiles_cache` | [database-data.php](database-data.php) |
| `$fairs_sectors_cache` | [database-data.php](database-data.php) |
| `$fairs_speakers_cache` | [database-data.php](database-data.php) |
| `$fairs_tickets_cache` | [database-data.php](database-data.php) |
| `$gf_form_resolver_cache` | [gravity-forms.php](gravity-forms.php) |
| `$groups_cache` | [database-data.php](database-data.php) |
| `$groups_callcenter_cache` | [database-data.php](database-data.php) |
| `$groups_contacts_cache` | [database-data.php](database-data.php) |
| `$logotypes_cache` | [database-data.php](database-data.php) |
| `$meta_cache` | [database-data.php](database-data.php) |
| `$premieres_cache` | [database-data.php](database-data.php) |
| `$store_cache` | [database-data.php](database-data.php) |
| `$store_packages_cache` | [database-data.php](database-data.php) |
| `$translation_cache` | [translations.php](translations.php) |
| `$translation_context` | [translations.php](translations.php) |
| `$translations_cache` | [database-data.php](database-data.php) |
| `$week_all_cache` | [database-data.php](database-data.php) |
| `$week_data_cache` | [database-data.php](database-data.php) |
