# `core/class-pwe-system-functions.php`

Centralna warstwa wspólnych funkcji, danych CAP i zgodności PWE_Functions.

## Metadane

- **Kategoria:** `core`
- **Rozmiar:** 194567 B
- **Liczba linii:** 5108
- **Źródło:** `core/class-pwe-system-functions.php`

## Typy i metody

### class `PWE_System_Functions` — linia 4

  - `private static elements_plugin_path()` — linia 12
  - `private static elements_plugin_url()` — linia 25
  - `public static set_translation_context($element_slug, $group, $element_type = 'main')` — linia 43
  - `public static multi_translation($key)` — linia 51
  - `private static load_translation_file($file_path)` — linia 139
  - `public static assets_per_element($element_slug, $element_type = 'main', $folder = 'elements')` — linia 157
  - `public static assets_per_group($element_slug, $group, $element_type = 'main', $folder = 'elements', $atts = null)` — linia 190
  - `public static exhibitor_logos($count = 16, $shuffle = true)` — linia 235
  - `public static get_gf_form_id(string $base_title)` — linia 386
  - `public static render_component($slug, $group = 'all', $params = [])` — linia 502
  - `public static get_website_translation_files()` — linia 538
  - `public static is_pwe_session_page()` — linia 551
  - `public static id_rnd()` — linia 624
  - `public static lang()` — linia 632
  - `public static add_log($message, $filename = 'logs')` — linia 656
  - `private static debug_log($message, $type = 'log')` — linia 674
  - `public static output_db_connection_logs()` — linia 700
  - `private static resolve_server_addr_fallback()` — linia 726
  - `private static get_database_servers()` — linia 745
  - `public static connect_database()` — linia 804
  - `public static set_db_timeout()` — linia 894
  - `private static get_database_json_cache_dir()` — linia 915
  - `private static get_database_json_cache_path($source, $cache_key)` — linia 947
  - `private static pack_database_json_value($value)` — linia 961
  - `private static unpack_database_json_value($value)` — linia 985
  - `private static read_database_json_cache($source, $cache_key)` — linia 1012
  - `private static write_database_json_cache($source, $cache_key, $data, array $args = [])` — linia 1043
  - `public static refresh_database_json_cache($domain = null)` — linia 1104
  - `public static get_database_fairs_data($fair_domain = null)` — linia 1500
  - `public static get_database_fairs_data_adds($fair_domain = null)` — linia 1746
  - `public static get_database_translations_data($fair_domain = null)` — linia 1869
  - `public static get_database_associates_data($fair_domain = null, bool $fair_block = false)` — linia 2092
  - `public static get_database_store_data()` — linia 2226
  - `public static get_database_store_packages_data()` — linia 2319
  - `public static get_database_meta_data($data_id = null, $domain = null)` — linia 2407
  - `public static get_database_groups_contacts_data()` — linia 2520
  - `public static get_database_groups_callcenter_data()` — linia 2600
  - `public static get_database_groups_data()` — linia 2679
  - `public static get_database_week_data($fair_domain = null)` — linia 2758
  - `public static get_database_week_all($fair_domain = null)` — linia 2845
  - `public static get_all_week_domains()` — linia 2933
  - `public static get_database_logotypes_data($fair_domain = null)` — linia 3020
  - `public static get_database_conferences_data($domain = null)` — linia 3139
  - `public static get_database_conference_adds_data($conf_id)` — linia 3241
  - `public static get_database_fairs_data_profiles($fair_domain = null)` — linia 3337
  - `public static get_database_premieres_data($fair_domain = null)` — linia 3412
  - `public static get_database_fairs_data_opinions($fair_domain = null)` — linia 3495
  - `public static get_database_fairs_data_sectors($fair_domain = null)` — linia 3580
  - `public static get_database_fairs_data_tickets($fair_domain = null)` — linia 3664
  - `public static get_database_fairs_data_speakers($fair_domain = null)` — linia 3748
  - `public static get_database_fairs_data_guests($fair_domain = null)` — linia 3837
  - `public static get_database_fairs_data_attractions($fair_domain = null)` — linia 3924
  - `public static get_database_fairs_data_files($fair_domain = null)` — linia 4011
  - `public static get_database_elements_data()` — linia 4098
  - `public static get_database_elements_order_data()` — linia 4191
  - `private static remove_logo_duplicates(array $logos)` — linia 4286
  - `public static pwe_color($color)` — linia 4317
  - `public static generate_fair_data($fair)` — linia 4342
  - `public static generate_fair_translation_data($fair)` — linia 4408
  - `public static json_fairs()` — linia 4446
  - `public static transform_dates($start_date, $end_date, $include_hours = true)` — linia 4548
  - `public static decode_clean_content($encoded_content)` — linia 4583
  - `public static json_decode($encoded_variable)` — linia 4592
  - `public static findColor($primary, $secondary, $default = '')` — linia 4601
  - `public static findPalletColorsStatic()` — linia 4616
  - `public findPalletColors()` — linia 4646
  - `public static lang_pl()` — linia 4676
  - `public static languageChecker($pl, $en = '', $de = '')` — linia 4687
  - `public static adjustBrightness($hex, $steps)` — linia 4702
  - `public findFormsGF($mode = '')` — linia 4725
  - `public static findFormsID($form_name)` — linia 4750
  - `public static checkForMobile()` — linia 4769
  - `public static findBestLogo($logo_color = false)` — linia 4779
  - `public static findAllImages($firstPath, $image_count = false, $secondPath = '/doc/galeria')` — linia 4829
  - `public static findBestFile($file_path)` — linia 4857
  - `public static isTradeDateExist()` — linia 4876
  - `public static inputRange()` — linia 4893
  - `public static input_range_field_html($settings, $value)` — linia 4898
  - `public static gravity_forms_smtp_monitor($is_success = null, $to = '', $subject = '', $message = '', $headers = [], $attachments = [], $message_format = '', $from = '', $from_name = '', $bcc = '', $reply_to = '', $entry = false)` — linia 4918

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- **filter:** `wpdb_connect_timeout` — linia 818
- **action:** `phpmailer_init` — linia 4998
- **action:** `phpmailer_init` — linia 5089
- **action:** `wp_footer` — linia 5100

## Wybrane wywołania statyczne

- `self::elements_plugin_path()`
- `self::load_translation_file()`
- `self::elements_plugin_url()`
- `PWE_Elements_Data::get_all_components()`
- `class::render()`
- `self::get_website_translation_files()`
- `self::resolve_server_addr_fallback()`
- `self::get_database_servers()`
- `self::add_log()`
- `self::debug_log()`
- `self::get_database_json_cache_dir()`
- `self::pack_database_json_value()`
- `self::unpack_database_json_value()`
- `self::get_database_json_cache_path()`
- `self::read_database_json_cache()`
- `self::connect_database()`
- `self::write_database_json_cache()`
- `self::remove_logo_duplicates()`
- `self::findPalletColorsStatic()`
- `self::get_database_fairs_data()`
- `self::get_database_translations_data()`
- `self::generate_fair_data()`
- `self::generate_fair_translation_data()`
- `DateTime::createFromFormat()`
- `GFAPI::get_forms()`
- `GFAPI::get_form()`

## API WordPress / GF rozpoznane heurystycznie

- `trailingslashit()`
- `apply_filters()`
- `plugin_dir_url()`
- `plugins_url()`
- `get_locale()`
- `wp_enqueue_style()`
- `wp_enqueue_script()`
- `wp_localize_script()`
- `do_shortcode()`
- `current_user_can()`
- `get_transient()`
- `set_transient()`
- `wp_doing_ajax()`
- `is_admin()`
- `wp_upload_dir()`
- `add_filter()`
- `wp_mkdir_p()`
- `sanitize_file_name()`
- `wp_json_encode()`
- `get_option()`
- `wp_die()`
- `esc_attr()`
- `add_action()`
- `wp_mail()`
- `remove_action()`
- `delete_transient()`

## Dołączane pliki / wyrażenia include

- `_once $file`
- `_hours = true) {`
- `_hours ? "Y/m/d H:i" : "Y/m/d"`

## Tabele / źródła SQL

- `all`
- `STATIC`
- `TRANSIENT`
- `JSON`
- `fairs`
- `fair_adds`
- `database`
- `translations`
- `associates`
- `shop`
- `shop_packs`
- `meta_data`
- `groups`
- `form_senders`
- `fair_weeks`
- `logos`
- `conferences`
- `conf_adds`
- `fair_profiles`
- `fair_premieres`
- `fair_opinions`
- `fair_sectors`
- `fair_tickets`
- `fair_lectures`
- `fair_guests`
- `fair_attractions`
- `fair_files`
- `pwelements`
- `pwe_order`
- `gf_form`

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
