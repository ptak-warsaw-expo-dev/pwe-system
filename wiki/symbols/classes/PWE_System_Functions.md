# Class `PWE_System_Functions`

**Źródło:** `core/class-pwe-system-functions.php:4`  
**Typ:** `class`  
**Metody:** 79

## Rola

Symbol jest zdefiniowany w `core/class-pwe-system-functions.php`. Status runtime pliku można sprawdzić w `inventory/load-graph.json`.

## Metody

- [`private static elements_plugin_path()`](../methods/PWE_System_Functions/elements_plugin_path.md) — linia 12
- [`private static elements_plugin_url()`](../methods/PWE_System_Functions/elements_plugin_url.md) — linia 25
- [`public static set_translation_context($element_slug, $group, $element_type = 'main')`](../methods/PWE_System_Functions/set_translation_context.md) — linia 43
- [`public static multi_translation($key)`](../methods/PWE_System_Functions/multi_translation.md) — linia 51
- [`private static load_translation_file($file_path)`](../methods/PWE_System_Functions/load_translation_file.md) — linia 139
- [`public static assets_per_element($element_slug, $element_type = 'main', $folder = 'elements')`](../methods/PWE_System_Functions/assets_per_element.md) — linia 157
- [`public static assets_per_group($element_slug, $group, $element_type = 'main', $folder = 'elements', $atts = null)`](../methods/PWE_System_Functions/assets_per_group.md) — linia 190
- [`public static exhibitor_logos($count = 16, $shuffle = true)`](../methods/PWE_System_Functions/exhibitor_logos.md) — linia 235
- [`public static get_gf_form_id(string $base_title)`](../methods/PWE_System_Functions/get_gf_form_id.md) — linia 386
- [`public static render_component($slug, $group = 'all', $params = [])`](../methods/PWE_System_Functions/render_component.md) — linia 502
- [`public static get_website_translation_files()`](../methods/PWE_System_Functions/get_website_translation_files.md) — linia 538
- [`public static is_pwe_session_page()`](../methods/PWE_System_Functions/is_pwe_session_page.md) — linia 551
- [`public static id_rnd()`](../methods/PWE_System_Functions/id_rnd.md) — linia 624
- [`public static lang()`](../methods/PWE_System_Functions/lang.md) — linia 632
- [`public static add_log($message, $filename = 'logs')`](../methods/PWE_System_Functions/add_log.md) — linia 656
- [`private static debug_log($message, $type = 'log')`](../methods/PWE_System_Functions/debug_log.md) — linia 674
- [`public static output_db_connection_logs()`](../methods/PWE_System_Functions/output_db_connection_logs.md) — linia 700
- [`private static resolve_server_addr_fallback()`](../methods/PWE_System_Functions/resolve_server_addr_fallback.md) — linia 726
- [`private static get_database_servers()`](../methods/PWE_System_Functions/get_database_servers.md) — linia 745
- [`public static connect_database()`](../methods/PWE_System_Functions/connect_database.md) — linia 804
- [`public static set_db_timeout()`](../methods/PWE_System_Functions/set_db_timeout.md) — linia 894
- [`private static get_database_json_cache_dir()`](../methods/PWE_System_Functions/get_database_json_cache_dir.md) — linia 915
- [`private static get_database_json_cache_path($source, $cache_key)`](../methods/PWE_System_Functions/get_database_json_cache_path.md) — linia 947
- [`private static pack_database_json_value($value)`](../methods/PWE_System_Functions/pack_database_json_value.md) — linia 961
- [`private static unpack_database_json_value($value)`](../methods/PWE_System_Functions/unpack_database_json_value.md) — linia 985
- [`private static read_database_json_cache($source, $cache_key)`](../methods/PWE_System_Functions/read_database_json_cache.md) — linia 1012
- [`private static write_database_json_cache($source, $cache_key, $data, array $args = [])`](../methods/PWE_System_Functions/write_database_json_cache.md) — linia 1043
- [`public static refresh_database_json_cache($domain = null)`](../methods/PWE_System_Functions/refresh_database_json_cache.md) — linia 1104
- [`public static get_database_fairs_data($fair_domain = null)`](../methods/PWE_System_Functions/get_database_fairs_data.md) — linia 1500
- [`public static get_database_fairs_data_adds($fair_domain = null)`](../methods/PWE_System_Functions/get_database_fairs_data_adds.md) — linia 1746
- [`public static get_database_translations_data($fair_domain = null)`](../methods/PWE_System_Functions/get_database_translations_data.md) — linia 1869
- [`public static get_database_associates_data($fair_domain = null, bool $fair_block = false)`](../methods/PWE_System_Functions/get_database_associates_data.md) — linia 2092
- [`public static get_database_store_data()`](../methods/PWE_System_Functions/get_database_store_data.md) — linia 2226
- [`public static get_database_store_packages_data()`](../methods/PWE_System_Functions/get_database_store_packages_data.md) — linia 2319
- [`public static get_database_meta_data($data_id = null, $domain = null)`](../methods/PWE_System_Functions/get_database_meta_data.md) — linia 2407
- [`public static get_database_groups_contacts_data()`](../methods/PWE_System_Functions/get_database_groups_contacts_data.md) — linia 2520
- [`public static get_database_groups_callcenter_data()`](../methods/PWE_System_Functions/get_database_groups_callcenter_data.md) — linia 2600
- [`public static get_database_groups_data()`](../methods/PWE_System_Functions/get_database_groups_data.md) — linia 2679
- [`public static get_database_week_data($fair_domain = null)`](../methods/PWE_System_Functions/get_database_week_data.md) — linia 2758
- [`public static get_database_week_all($fair_domain = null)`](../methods/PWE_System_Functions/get_database_week_all.md) — linia 2845
- [`public static get_all_week_domains()`](../methods/PWE_System_Functions/get_all_week_domains.md) — linia 2933
- [`public static get_database_logotypes_data($fair_domain = null)`](../methods/PWE_System_Functions/get_database_logotypes_data.md) — linia 3020
- [`public static get_database_conferences_data($domain = null)`](../methods/PWE_System_Functions/get_database_conferences_data.md) — linia 3139
- [`public static get_database_conference_adds_data($conf_id)`](../methods/PWE_System_Functions/get_database_conference_adds_data.md) — linia 3241
- [`public static get_database_fairs_data_profiles($fair_domain = null)`](../methods/PWE_System_Functions/get_database_fairs_data_profiles.md) — linia 3337
- [`public static get_database_premieres_data($fair_domain = null)`](../methods/PWE_System_Functions/get_database_premieres_data.md) — linia 3412
- [`public static get_database_fairs_data_opinions($fair_domain = null)`](../methods/PWE_System_Functions/get_database_fairs_data_opinions.md) — linia 3495
- [`public static get_database_fairs_data_sectors($fair_domain = null)`](../methods/PWE_System_Functions/get_database_fairs_data_sectors.md) — linia 3580
- [`public static get_database_fairs_data_tickets($fair_domain = null)`](../methods/PWE_System_Functions/get_database_fairs_data_tickets.md) — linia 3664
- [`public static get_database_fairs_data_speakers($fair_domain = null)`](../methods/PWE_System_Functions/get_database_fairs_data_speakers.md) — linia 3748
- [`public static get_database_fairs_data_guests($fair_domain = null)`](../methods/PWE_System_Functions/get_database_fairs_data_guests.md) — linia 3837
- [`public static get_database_fairs_data_attractions($fair_domain = null)`](../methods/PWE_System_Functions/get_database_fairs_data_attractions.md) — linia 3924
- [`public static get_database_fairs_data_files($fair_domain = null)`](../methods/PWE_System_Functions/get_database_fairs_data_files.md) — linia 4011
- [`public static get_database_elements_data()`](../methods/PWE_System_Functions/get_database_elements_data.md) — linia 4098
- [`public static get_database_elements_order_data()`](../methods/PWE_System_Functions/get_database_elements_order_data.md) — linia 4191
- [`private static remove_logo_duplicates(array $logos)`](../methods/PWE_System_Functions/remove_logo_duplicates.md) — linia 4286
- [`public static pwe_color($color)`](../methods/PWE_System_Functions/pwe_color.md) — linia 4317
- [`public static generate_fair_data($fair)`](../methods/PWE_System_Functions/generate_fair_data.md) — linia 4342
- [`public static generate_fair_translation_data($fair)`](../methods/PWE_System_Functions/generate_fair_translation_data.md) — linia 4408
- [`public static json_fairs()`](../methods/PWE_System_Functions/json_fairs.md) — linia 4446
- [`public static transform_dates($start_date, $end_date, $include_hours = true)`](../methods/PWE_System_Functions/transform_dates.md) — linia 4548
- [`public static decode_clean_content($encoded_content)`](../methods/PWE_System_Functions/decode_clean_content.md) — linia 4583
- [`public static json_decode($encoded_variable)`](../methods/PWE_System_Functions/json_decode.md) — linia 4592
- [`public static findColor($primary, $secondary, $default = '')`](../methods/PWE_System_Functions/findcolor.md) — linia 4601
- [`public static findPalletColorsStatic()`](../methods/PWE_System_Functions/findpalletcolorsstatic.md) — linia 4616
- [`public findPalletColors()`](../methods/PWE_System_Functions/findpalletcolors.md) — linia 4646
- [`public static lang_pl()`](../methods/PWE_System_Functions/lang_pl.md) — linia 4676
- [`public static languageChecker($pl, $en = '', $de = '')`](../methods/PWE_System_Functions/languagechecker.md) — linia 4687
- [`public static adjustBrightness($hex, $steps)`](../methods/PWE_System_Functions/adjustbrightness.md) — linia 4702
- [`public findFormsGF($mode = '')`](../methods/PWE_System_Functions/findformsgf.md) — linia 4725
- [`public static findFormsID($form_name)`](../methods/PWE_System_Functions/findformsid.md) — linia 4750
- [`public static checkForMobile()`](../methods/PWE_System_Functions/checkformobile.md) — linia 4769
- [`public static findBestLogo($logo_color = false)`](../methods/PWE_System_Functions/findbestlogo.md) — linia 4779
- [`public static findAllImages($firstPath, $image_count = false, $secondPath = '/doc/galeria')`](../methods/PWE_System_Functions/findallimages.md) — linia 4829
- [`public static findBestFile($file_path)`](../methods/PWE_System_Functions/findbestfile.md) — linia 4857
- [`public static isTradeDateExist()`](../methods/PWE_System_Functions/istradedateexist.md) — linia 4876
- [`public static inputRange()`](../methods/PWE_System_Functions/inputrange.md) — linia 4893
- [`public static input_range_field_html($settings, $value)`](../methods/PWE_System_Functions/input_range_field_html.md) — linia 4898
- [`public static gravity_forms_smtp_monitor($is_success = null, $to = '', $subject = '', $message = '', $headers = [], $attachments = [], $message_format = '', $from = '', $from_name = '', $bcc = '', $reply_to = '', $entry = false)`](../methods/PWE_System_Functions/gravity_forms_smtp_monitor.md) — linia 4918

## Dokument pliku

- [Otwórz dokumentację `core/class-pwe-system-functions.php`](../../files/core/class-pwe-system-functions.php.md)
