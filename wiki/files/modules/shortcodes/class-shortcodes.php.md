# `modules/shortcodes/class-shortcodes.php`

Wyższopoziomowe shortcody kompatybilne z AutoSwitch.

## Metadane

- **Kategoria:** `shortcodes`
- **Rozmiar:** 194805 B
- **Liczba linii:** 4356
- **Źródło:** `modules/shortcodes/class-shortcodes.php`

## Typy i metody

### class `PWE_Shortcodes` — linia 5

  - `public static init()` — linia 10
  - `private __construct()` — linia 17
  - `private get_shortcodes_map()` — linia 34
  - `private get_gf_shortcodes_map()` — linia 149
  - `private get_yoast_shortcodes_map()` — linia 238
  - `private other_shortcodes_map()` — linia 262
  - `private translates_shortcodes_map()` — linia 322
  - `public register_shortcodes()` — linia 340
  - `private shorten_value($value, $length = 30)` — linia 353
  - `public enqueue_admin_assets($hook)` — linia 364
  - `private get_shortcode_description($tag)` — linia 383
  - `private render_shortcode_list_item($syntax, $tag, $value = '', $description = '')` — linia 482
  - `public add_menu()` — linia 501
  - `public theme_options_page()` — linia 512
  - `public register_settings()` — linia 753
  - `public header_section()` — linia 901
  - `public display_trade_fair_name()` — linia 907
  - `public display_trade_fair_name_eng()` — linia 924
  - `public display_trade_fair_desc()` — linia 943
  - `public display_trade_fair_desc_eng()` — linia 960
  - `public display_trade_fair_desc_short()` — linia 977
  - `public display_trade_fair_desc_short_eng()` — linia 994
  - `public get_trade_fair_dates()` — linia 1011
  - `public format_trade_fair_date($start_date, $end_date, $lang = "pl")` — linia 1030
  - `public display_trade_fair_date_field($lang = "pl")` — linia 1375
  - `public display_trade_fair_date()` — linia 1404
  - `public display_trade_fair_date_eng()` — linia 1408
  - `public display_trade_fair_datetotimer()` — linia 1412
  - `public display_trade_fair_enddata()` — linia 1444
  - `public display_trade_fair_date_custom_format()` — linia 1476
  - `public display_trade_fair_date_multilang()` — linia 1506
  - `private get_trade_fair_days()` — linia 1521
  - `public display_trade_fair_first_day()` — linia 1543
  - `public display_trade_fair_second_day()` — linia 1564
  - `public display_trade_fair_third_day()` — linia 1585
  - `public display_trade_fair_catalog()` — linia 1607
  - `public display_trade_fair_catalog_id()` — linia 1624
  - `public display_trade_fair_catalog_archive()` — linia 1641
  - `public display_trade_fair_catalog_id_archive()` — linia 1658
  - `public display_trade_fair_catalog_year()` — linia 1675
  - `public display_trade_fair_conference()` — linia 1693
  - `public display_trade_fair_conference_title()` — linia 1710
  - `public display_trade_fair_conference_title_eng()` — linia 1727
  - `public display_trade_fair_1stbuildday()` — linia 1744
  - `public display_trade_fair_2ndbuildday()` — linia 1762
  - `public display_trade_fair_1stdismantlday()` — linia 1780
  - `public display_trade_fair_2nddismantlday()` — linia 1798
  - `public display_trade_fair_actualyear()` — linia 1816
  - `public display_trade_fair_branzowy_field($lang = "pl")` — linia 1825
  - `public display_trade_fair_branzowy()` — linia 1870
  - `public display_trade_fair_branzowy_eng()` — linia 1874
  - `public display_trade_fair_hall()` — linia 1878
  - `public display_trade_fair_hall_entrance()` — linia 1895
  - `public display_trade_fair_edition()` — linia 1912
  - `public display_trade_fair_accent()` — linia 1929
  - `public display_trade_fair_main2()` — linia 1946
  - `public display_trade_fair_badge()` — linia 1962
  - `public display_trade_fair_feed_prefix()` — linia 1979
  - `public display_trade_fair_domainadress()` — linia 1997
  - `public display_trade_fair_facebook()` — linia 2006
  - `public display_trade_fair_instagram()` — linia 2023
  - `public display_trade_fair_linkedin()` — linia 2040
  - `public display_trade_fair_youtube()` — linia 2057
  - `private get_group_contact_default_value($groups_slug, $field = 'email')` — linia 2075
  - `private show_contact_field_with_default($option_name, $groups_slug, $field = 'email')` — linia 2122
  - `private display_contact_field_with_default($option_name, $default_value = '')` — linia 2132
  - `public display_trade_fair_rejestracja()` — linia 2146
  - `public display_trade_fair_contact()` — linia 2155
  - `public display_trade_fair_contact_service_name()` — linia 2160
  - `public display_trade_fair_contact_service_phone()` — linia 2164
  - `public display_trade_fair_contact_service_email()` — linia 2168
  - `public display_trade_fair_contact_media_phone()` — linia 2172
  - `public display_trade_fair_contact_media_name()` — linia 2176
  - `public display_trade_fair_contact_media_person_name()` — linia 2180
  - `public display_trade_fair_contact_media_person_phone()` — linia 2184
  - `public display_trade_fair_contact_media_person_email()` — linia 2188
  - `public display_trade_fair_contact_media_person_name_2()` — linia 2192
  - `public display_trade_fair_contact_media_person_phone_2()` — linia 2196
  - `public display_trade_fair_contact_media_person_email_2()` — linia 2200
  - `public display_trade_fair_contact_media_person_name_3()` — linia 2204
  - `public display_trade_fair_contact_media_person_phone_3()` — linia 2208
  - `public display_trade_fair_contact_media_person_email_3()` — linia 2212
  - `public display_trade_fair_contact_tech()` — linia 2216
  - `public display_trade_fair_contact_media()` — linia 2220
  - `public display_trade_fair_lidy()` — linia 2224
  - `public display_trade_fair_contact_email_vip()` — linia 2228
  - `public display_trade_fair_contact_phone_vip()` — linia 2232
  - `public display_trade_fair_contact_medal_ceremony_email()` — linia 2236
  - `public days_difference()` — linia 2240
  - `public display_trade_fair_registration_benefits_pl()` — linia 2261
  - `public display_trade_fair_registration_benefits_en()` — linia 2279
  - `public display_trade_fair_ticket_benefits_pl()` — linia 2297
  - `public display_trade_fair_ticket_benefits_en()` — linia 2318
  - `public display_trade_fair_group()` — linia 2339
  - `public show_trade_fair_name()` — linia 2367
  - `public show_trade_fair_name_eng()` — linia 2377
  - `public show_trade_fair_desc()` — linia 2389
  - `public show_trade_fair_desc_eng()` — linia 2396
  - `public show_trade_fair_desc_short()` — linia 2403
  - `public show_trade_fair_desc_short_eng()` — linia 2414
  - `public show_trade_fair_datetotimer()` — linia 2424
  - `public show_trade_fair_enddata()` — linia 2443
  - `public show_trade_fair_date_custom_format()` — linia 2462
  - `public show_trade_fair_date()` — linia 2476
  - `public show_trade_fair_date_eng()` — linia 2490
  - `public show_trade_fair_date_multilang($atts = [])` — linia 2504
  - `private get_trade_fair_day(int $offset = 0)` — linia 2612
  - `public show_trade_fair_first_day()` — linia 2666
  - `public show_trade_fair_second_day()` — linia 2674
  - `public show_trade_fair_third_day()` — linia 2682
  - `public show_trade_fair_catalog()` — linia 2690
  - `public show_trade_fair_catalog_id()` — linia 2697
  - `public show_trade_fair_catalog_archive()` — linia 2704
  - `public show_trade_fair_catalog_id_archive()` — linia 2711
  - `public show_trade_fair_catalog_year()` — linia 2718
  - `public show_trade_fair_conference()` — linia 2725
  - `public show_trade_fair_conference_title()` — linia 2735
  - `public show_trade_fair_conference_title_eng()` — linia 2745
  - `public show_trade_fair_1stbuildday()` — linia 2755
  - `public show_trade_fair_2ndbuildday()` — linia 2763
  - `public show_trade_fair_1stdismantlday()` — linia 2771
  - `public show_trade_fair_2nddismantlday()` — linia 2782
  - `public show_trade_fair_hall()` — linia 2793
  - `public show_trade_fair_hall_entrance()` — linia 2801
  - `public show_trade_fair_edition($entry = null, $fields = null)` — linia 2809
  - `public show_trade_fair_accent()` — linia 2827
  - `public show_trade_fair_main2()` — linia 2834
  - `public trade_fair_branzowy_result($lang = "pl")` — linia 2841
  - `public show_trade_fair_branzowy()` — linia 2868
  - `public show_trade_fair_branzowy_eng()` — linia 2877
  - `public show_trade_fair_badge()` — linia 2886
  - `public show_trade_fair_feed_prefix()` — linia 2893
  - `public show_trade_fair_facebook()` — linia 2902
  - `public show_trade_fair_instagram()` — linia 2912
  - `public show_trade_fair_linkedin()` — linia 2922
  - `public show_trade_fair_youtube()` — linia 2932
  - `public show_trade_fair_domainadress()` — linia 2942
  - `get_lang_domain($atts = [])` — linia 2950
  - `public show_trade_fair_actualyear()` — linia 2996
  - `public show_trade_fair_rejestracja()` — linia 3001
  - `public show_trade_fair_contact()` — linia 3011
  - `public show_trade_fair_contact_service_name()` — linia 3015
  - `public show_trade_fair_contact_service_phone()` — linia 3019
  - `public show_trade_fair_contact_service_email()` — linia 3023
  - `public show_trade_fair_contact_media_phone()` — linia 3027
  - `public show_trade_fair_contact_media_name()` — linia 3031
  - `public show_trade_fair_contact_media_person_name()` — linia 3035
  - `public show_trade_fair_contact_media_person_phone()` — linia 3039
  - `public show_trade_fair_contact_media_person_email()` — linia 3043
  - `public show_trade_fair_contact_tech()` — linia 3047
  - `public show_trade_fair_contact_media()` — linia 3051
  - `public show_trade_fair_lidy()` — linia 3055
  - `public show_trade_fair_contact_email_vip()` — linia 3059
  - `public show_trade_fair_contact_phone_vip()` — linia 3063
  - `public show_trade_fair_contact_medal_ceremony_email()` — linia 3067
  - `public show_trade_fair_group()` — linia 3071
  - `public show_trade_fair_registration_benefits_pl()` — linia 3083
  - `public show_trade_fair_registration_benefits_en()` — linia 3097
  - `public show_trade_fair_ticket_benefits_pl()` — linia 3111
  - `public show_trade_fair_ticket_benefits_en()` — linia 3128
  - `public show_trade_fair_exhibitor_generator_icons()` — linia 3145
  - `public show_trade_fair_exhibitor_generator_text()` — linia 3356
  - `public show_trade_fair_exhibitor_generator_header_url()` — linia 3394
  - `public show_trade_fair_exhibitor_generator_badge_url()` — linia 3433
  - `public sc_pwe_trade_fair_full_desc()` — linia 3458
  - `get_translated_field($fair, $field_base_name)` — linia 3464
  - `get_pwe_shortcode($shortcode, $domain)` — linia 3482
  - `check_available_pwe_shortcode($shortcodes_active, $shortcode)` — linia 3488
  - `public sc_pwe_text_news()` — linia 3510
  - `public sc_pwe_text_for_visitors()` — linia 3518
  - `public sc_pwe_text_for_exhibitors()` — linia 3526
  - `public sc_pwe_text_add_calendar()` — linia 3534
  - `public sc_pwe_text_gallery()` — linia 3542
  - `public sc_pwe_text_org_info()` — linia 3550
  - `public sc_pwe_text_exh_catalog()` — linia 3558
  - `public sc_pwe_text_events()` — linia 3566
  - `public sc_pwe_text_contact()` — linia 3574
  - `public sc_pwe_text_fair_plan()` — linia 3582
  - `public sc_pwe_text_registration()` — linia 3590
  - `public sc_pwe_text_promote_yourself()` — linia 3598
  - `public sc_pwe_text_become_an_exhibitor()` — linia 3606
  - `public sc_pwe_text_store()` — linia 3614
  - `public wpseo_register_extra_replacements()` — linia 3622
  - `public wpseo_replacements($replacements)` — linia 3630
  - `public replace_multilang_date_in_notification($notification, $form, $entry)` — linia 3649
  - `private get_language_from_notification_name($notification_name)` — linia 3727
  - `public replace_gf_merge_tags($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format)` — linia 3794
  - `private read_urls_json_file($json_file)` — linia 3841
  - `private get_urls_data()` — linia 3888
  - `private get_url_shortcode_language($requested_lang = '')` — linia 3930
  - `private get_url_language_data(array $url_entry, $lang)` — linia 3973
  - `public show_multilang_url($atts = [], $content = null, $shortcode_tag = '')` — linia 4021
  - `private register_url_shortcodes()` — linia 4148
  - `private get_gf_url_shortcodes_map()` — linia 4177
  - `public show_pwe_mailing_header_url()` — linia 4209
  - `public show_pwe_mailing_header_platyna_url($requested_lang = '')` — linia 4277

## Funkcje globalne

- Brak funkcji globalnych.

## Rejestracje WordPress / GF

- **action:** `admin_menu` — linia 19
- **action:** `admin_init` — linia 20
- **action:** `admin_enqueue_scripts` — linia 21
- **action:** `init` — linia 23
- **filter:** `wpseo_register_extra_replacements` — linia 25
- **filter:** `wpseo_replacements` — linia 26
- **filter:** `gform_replace_merge_tags` — linia 27
- **filter:** `gform_notification` — linia 29

## Wybrane wywołania statyczne

- `PWE_Functions::lang()`
- `PWE_System_Admin::render_module_header()`
- `PWE_Functions::transform_dates()`
- `PWE_Functions::get_database_groups_data()`
- `PWE_Functions::get_database_groups_contacts_data()`
- `DateTime::createFromFormat()`
- `PWE_Functions::get_database_fairs_data_files()`
- `PWE_Functions::get_database_translations_data()`
- `PWE_System_Functions::get_website_translation_files()`
- `PWE_Shortcodes::init()`

## API WordPress / GF rozpoznane heurystycznie

- `add_action()`
- `add_filter()`
- `shortcode_exists()`
- `remove_shortcode()`
- `add_shortcode()`
- `wp_strip_all_tags()`
- `sanitize_key()`
- `wp_unslash()`
- `wp_enqueue_script()`
- `esc_attr()`
- `esc_html()`
- `esc_url()`
- `admin_url()`
- `do_shortcode()`
- `get_option()`
- `home_url()`
- `shortcode_atts()`
- `determine_locale()`
- `get_locale()`
- `wp_parse_url()`
- `plugins_url()`
- `trailingslashit()`
- `wp_kses()`

## Dołączane pliki / wyrażenia include

- Brak.

## Tabele / źródła SQL

- `conferences`
- `free`
- `the`

## Uwagi

- Symbole są wyciągane przez `token_get_all`; traity są zachowane z `kind=trait`.
- Dynamiczne rejestracje AJAX z map/stałych są rozwinięte w `hooks.json` i `entrypoints.json`.
- Dokładny Call Graph powstaje niezależnie w Code Indexerze.
