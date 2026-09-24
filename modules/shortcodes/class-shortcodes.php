<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class PWE_Shortcodes {

    private static $instance;
    private $urls_data = null;

    public static function init() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // menu and settings
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        add_action('init', [$this, 'register_shortcodes'], 20);

        add_filter('wpseo_register_extra_replacements', [$this, 'wpseo_register_extra_replacements']);
        add_filter('wpseo_replacements', [$this, 'wpseo_replacements']);
        add_filter('gform_replace_merge_tags', [$this, 'replace_gf_merge_tags'], 10, 7);

        add_filter('gform_notification', [$this, 'replace_multilang_date_in_notification'], 10, 3);
    }

    // ALL SHORTCODES START <------------------------------------------------------------------------------<

    private function get_shortcodes_map() {
        $lang = PWE_LANG;
        return [
            'trade_fair_name' => 'show_trade_fair_name', // [trade_fair_name]
            'trade_fair_name_eng' => 'show_trade_fair_name_eng', // [trade_fair_name_eng]
            'trade_fair_desc' => 'show_trade_fair_desc',
            'trade_fair_desc_eng' => 'show_trade_fair_desc_eng',
            'trade_fair_desc_short' => 'show_trade_fair_desc_short',
            'trade_fair_desc_short_eng' => 'show_trade_fair_desc_short_eng',

            'trade_fair_datetotimer' => 'show_trade_fair_datetotimer',
            'trade_fair_enddata' => 'show_trade_fair_enddata',
            'trade_fair_date_custom_format' => 'show_trade_fair_date_custom_format',
            'trade_fair_date' => 'show_trade_fair_date',
            'trade_fair_date_eng' => 'show_trade_fair_date_eng',
            'trade_fair_date_multilang' => 'show_trade_fair_date_multilang', // [trade_fair_date_multilang lang="et"]
            'trade_fair_first_day' => 'show_trade_fair_first_day',
            'trade_fair_second_day' => 'show_trade_fair_second_day',
            'trade_fair_third_day' => 'show_trade_fair_third_day',

            'trade_fair_catalog' => 'show_trade_fair_catalog',
            'trade_fair_catalog_id' => 'show_trade_fair_catalog_id',
            'trade_fair_catalog_archive' => 'show_trade_fair_catalog_archive',
            'trade_fair_catalog_id_archive' => 'show_trade_fair_catalog_id_archive',
            'trade_fair_catalog_year' => 'show_trade_fair_catalog_year',

            'trade_fair_conference' => 'show_trade_fair_conference',
            'trade_fair_conference_title' => 'show_trade_fair_conference_title',
            'trade_fair_conference_title_eng' => 'show_trade_fair_conference_title_eng',

            'trade_fair_conferance' => 'show_trade_fair_conference_title', // OLD (get data from [trade_fair_conference_title])
            'trade_fair_conferance_eng' => 'show_trade_fair_conference_title_eng', // OLD (get data from [trade_fair_conference_title_eng])

            'trade_fair_1stbuildday' => 'show_trade_fair_1stbuildday',
            'trade_fair_2ndbuildday' => 'show_trade_fair_2ndbuildday',
            'trade_fair_1stdismantlday' => 'show_trade_fair_1stdismantlday',
            'trade_fair_2nddismantlday' => 'show_trade_fair_2nddismantlday',

            'trade_fair_hall' => 'show_trade_fair_hall',
            'trade_fair_hall_entrance' => 'show_trade_fair_hall_entrance',

            'trade_fair_group' => 'show_trade_fair_group',
            'trade_fair_edition' => 'show_trade_fair_edition',
            'trade_fair_accent' => 'show_trade_fair_accent',
            'trade_fair_main2' => 'show_trade_fair_main2',
            'trade_fair_branzowy' => 'show_trade_fair_branzowy',
            'trade_fair_branzowy_eng' => 'show_trade_fair_branzowy_eng',
            'trade_fair_badge' => 'show_trade_fair_badge',
            'trade_fair_feed_prefix' => 'show_trade_fair_feed_prefix',

            'trade_fair_facebook' => 'show_trade_fair_facebook',
            'trade_fair_instagram' => 'show_trade_fair_instagram',
            'trade_fair_linkedin' => 'show_trade_fair_linkedin',
            'trade_fair_youtube' => 'show_trade_fair_youtube',

            'pwe_lang_domain' => 'get_lang_domain',
            'trade_fair_domainadress' => 'show_trade_fair_domainadress',
            'trade_fair_actualyear' => 'show_trade_fair_actualyear',
            'trade_fair_rejestracja' => 'show_trade_fair_rejestracja',
            'trade_fair_contact' => 'show_trade_fair_contact',
            'trade_fair_contact_service_name' => 'show_trade_fair_contact_service_name',
            'trade_fair_contact_service_phone' => 'show_trade_fair_contact_service_phone',
            'trade_fair_contact_service_email' => 'show_trade_fair_contact_service_email',
            'trade_fair_contact_tech' => 'show_trade_fair_contact_tech',
            'trade_fair_contact_media' => 'show_trade_fair_contact_media',
            'trade_fair_contact_media_phone' => 'show_trade_fair_contact_media_phone',
            'trade_fair_contact_media_name' => 'show_trade_fair_contact_media_name',
            'trade_fair_contact_media_person_name' => 'show_trade_fair_contact_media_person_name',
            'trade_fair_contact_media_person_phone' => 'show_trade_fair_contact_media_person_phone',
            'trade_fair_contact_media_person_email' => 'show_trade_fair_contact_media_person_email',
            'trade_fair_contact_media_person_name_2' => 'show_trade_fair_contact_media_person_name_2',
            'trade_fair_contact_media_person_phone_2' => 'show_trade_fair_contact_media_person_phone_2',
            'trade_fair_contact_media_person_email_2' => 'show_trade_fair_contact_media_person_email_2',
            'trade_fair_contact_media_person_name_3' => 'show_trade_fair_contact_media_person_name_3',
            'trade_fair_contact_media_person_phone_3' => 'show_trade_fair_contact_media_person_phone_3',
            'trade_fair_contact_media_person_email_3' => 'show_trade_fair_contact_media_person_email_3',
            'trade_fair_contact_email_vip' => 'show_trade_fair_contact_email_vip',
            'trade_fair_contact_phone_vip' => 'show_trade_fair_contact_phone_vip',
            'trade_fair_lidy' => 'show_trade_fair_lidy',
            'trade_fair_contact_medal_ceremony_email' => 'show_trade_fair_contact_medal_ceremony_email',

            'trade_fair_registration_benefits_pl' => 'show_trade_fair_registration_benefits_pl',
            'trade_fair_registration_benefits_en' => 'show_trade_fair_registration_benefits_en',
            'trade_fair_ticket_benefits_pl' => 'show_trade_fair_ticket_benefits_pl',
            'trade_fair_ticket_benefits_en' => 'show_trade_fair_ticket_benefits_en',

            'trade_fair_exhibitor_generator_icons'         => 'show_trade_fair_exhibitor_generator_icons',
            'trade_fair_exhibitor_generator_text'          => 'show_trade_fair_exhibitor_generator_text',
            'trade_fair_exhibitor_generator_header_url'    => 'show_trade_fair_exhibitor_generator_header_url',
            'trade_fair_exhibitor_generator_badge_url'     => 'show_trade_fair_exhibitor_generator_badge_url',

            // shortcodes for Yoast SEO
            'trade_fair_full_desc' => 'sc_pwe_trade_fair_full_desc',
            'sc_pwe_trade_fair_conference_title' => $lang === 'pl' ? 'show_trade_fair_conference_title' : 'show_trade_fair_conference_title_eng',
            'sc_pwe_text_news' => 'sc_pwe_text_news',
            'sc_pwe_text_for_visitors' => 'sc_pwe_text_for_visitors',
            'sc_pwe_text_for_exhibitors' => 'sc_pwe_text_for_exhibitors',
            'sc_pwe_text_add_calendar' => 'sc_pwe_text_add_calendar',
            'sc_pwe_text_gallery' => 'sc_pwe_text_gallery',
            'sc_pwe_text_org_info' => 'sc_pwe_text_org_info',
            'sc_pwe_text_exh_catalog' => 'sc_pwe_text_exh_catalog',
            'sc_pwe_text_events' => 'sc_pwe_text_events',
            'sc_pwe_text_contact' => 'sc_pwe_text_contact',
            'sc_pwe_text_fair_plan' => 'sc_pwe_text_fair_plan',
            'sc_pwe_text_registration' => 'sc_pwe_text_registration',
            'sc_pwe_text_promote_yourself' => 'sc_pwe_text_promote_yourself',
            'sc_pwe_text_become_an_exhibitor' => 'sc_pwe_text_become_an_exhibitor',
            'sc_pwe_text_store' => 'sc_pwe_text_store',

            // other shortcodes
            'pwe_mailing_header_url' => 'show_pwe_mailing_header_url',
            'pwe_mailing_header_platyna_url' => 'show_pwe_mailing_header_platyna_url',
        ];
    }

    private function get_gf_shortcodes_map() {
        $lang = PWE_LANG;
        return [
            'trade_fair_name' => 'show_trade_fair_name', // {trade_fair_name}
            'trade_fair_name_eng' => 'show_trade_fair_name_eng', // {trade_fair_name_eng}
            'trade_fair_desc' => 'show_trade_fair_desc',
            'trade_fair_desc_eng' => 'show_trade_fair_desc_eng',
            'trade_fair_desc_short' => 'show_trade_fair_desc_short',
            'trade_fair_desc_short_eng' => 'show_trade_fair_desc_short_eng',

            'trade_fair_datetotimer' => 'show_trade_fair_datetotimer',
            'trade_fair_enddata' => 'show_trade_fair_enddata',
            'trade_fair_date' => 'show_trade_fair_date',
            'trade_fair_date_eng' => 'show_trade_fair_date_eng',
            'trade_fair_first_day' => 'show_trade_fair_first_day',
            'trade_fair_second_day' => 'show_trade_fair_second_day',
            'trade_fair_third_day' => 'show_trade_fair_third_day',

            'trade_fair_catalog' => 'show_trade_fair_catalog',
            'trade_fair_catalog_year' => 'show_trade_fair_catalog_year',
            'trade_fair_conference' => 'show_trade_fair_conference',
            'trade_fair_conference_title' => 'show_trade_fair_conference_title',
            'trade_fair_conference_title_eng' => 'show_trade_fair_conference_title_eng',

            'trade_fair_1stbuildday' => 'show_trade_fair_1stbuildday',
            'trade_fair_2ndbuildday' => 'show_trade_fair_2ndbuildday',
            'trade_fair_1stdismantlday' => 'show_trade_fair_1stdismantlday',
            'trade_fair_2nddismantlday' => 'show_trade_fair_2nddismantlday',

            'trade_fair_hall' => 'show_trade_fair_hall',
            'trade_fair_hall_entrance' => 'show_trade_fair_hall_entrance',
            'trade_fair_accent' => 'show_trade_fair_accent',
            'trade_fair_edition' => 'show_trade_fair_edition',
            'trade_fair_main2' => 'show_trade_fair_main2',
            'trade_fair_branzowy' => 'show_trade_fair_branzowy',
            'trade_fair_branzowy_eng' => 'show_trade_fair_branzowy_eng',
            'trade_fair_badge' => 'show_trade_fair_badge',
            'trade_fair_feed_prefix' => 'show_trade_fair_feed_prefix',

            'trade_fair_facebook' => 'show_trade_fair_facebook',
            'trade_fair_instagram' => 'show_trade_fair_instagram',
            'trade_fair_linkedin' => 'show_trade_fair_linkedin',
            'trade_fair_youtube' => 'show_trade_fair_youtube',

            'pwe_lang_domain' => 'get_lang_domain',
            'trade_fair_domainadress' => 'show_trade_fair_domainadress',
            'trade_fair_actualyear' => 'show_trade_fair_actualyear',

            'trade_fair_rejestracja' => 'show_trade_fair_rejestracja',
            'trade_fair_contact' => 'show_trade_fair_contact',
            'trade_fair_contact_service_name' => 'show_trade_fair_contact_service_name',
            'trade_fair_contact_service_phone' => 'show_trade_fair_contact_service_phone',
            'trade_fair_contact_service_email' => 'show_trade_fair_contact_service_email',
            'trade_fair_contact_tech' => 'show_trade_fair_contact_tech',
            'trade_fair_contact_media' => 'show_trade_fair_contact_media',
            'trade_fair_contact_media_phone' => 'show_trade_fair_contact_media_phone',
            'trade_fair_contact_media_name' => 'show_trade_fair_contact_media_name',
            'trade_fair_contact_media_person_name' => 'show_trade_fair_contact_media_person_name',
            'trade_fair_contact_media_person_phone' => 'show_trade_fair_contact_media_person_phone',
            'trade_fair_contact_media_person_email' => 'show_trade_fair_contact_media_person_email',
            'trade_fair_contact_media_person_name_2' => 'show_trade_fair_contact_media_person_name_2',
            'trade_fair_contact_media_person_phone_2' => 'show_trade_fair_contact_media_person_phone_2',
            'trade_fair_contact_media_person_email_2' => 'show_trade_fair_contact_media_person_email_2',
            'trade_fair_contact_media_person_name_2' => 'show_trade_fair_contact_media_person_name_2',
            'trade_fair_contact_media_person_phone_2' => 'show_trade_fair_contact_media_person_phone_2',
            'trade_fair_contact_media_person_email_2' => 'show_trade_fair_contact_media_person_email_2',
            'trade_fair_contact_media_person_name_3' => 'show_trade_fair_contact_media_person_name_3',
            'trade_fair_contact_media_person_phone_3' => 'show_trade_fair_contact_media_person_phone_3',
            'trade_fair_contact_media_person_email_3' => 'show_trade_fair_contact_media_person_email_3',
            'trade_fair_contact_email_vip' => 'show_trade_fair_contact_email_vip',
            'trade_fair_contact_phone_vip' => 'show_trade_fair_contact_phone_vip',
            'trade_fair_lidy' => 'show_trade_fair_lidy',
            'trade_fair_contact_medal_ceremony_email' => 'show_trade_fair_contact_medal_ceremony_email',

            'trade_fair_registration_benefits_pl' => 'show_trade_fair_registration_benefits_pl',
            'trade_fair_registration_benefits_en' => 'show_trade_fair_registration_benefits_en',
            'trade_fair_ticket_benefits_pl' => 'show_trade_fair_ticket_benefits_pl',
            'trade_fair_ticket_benefits_en' => 'show_trade_fair_ticket_benefits_en',

            'trade_fair_exhibitor_generator_icons'          => 'show_trade_fair_exhibitor_generator_icons',
            'trade_fair_exhibitor_generator_text'          => 'show_trade_fair_exhibitor_generator_text',
            'trade_fair_exhibitor_generator_header_url'          => 'show_trade_fair_exhibitor_generator_header_url',

            // other shortcodes
            'pwe_mailing_header_url' => 'show_pwe_mailing_header_url',
            'pwe_mailing_header_platyna_url' => 'show_pwe_mailing_header_platyna_url',
        ];
    }

    private function get_yoast_shortcodes_map() {
        $lang = PWE_LANG;
        return [
            'sc_pwe_trade_fair_year'             => 'show_trade_fair_catalog_year', // %%sc_pwe_trade_fair_year%% || [trade_fair_catalog_year]
            'sc_pwe_trade_fair_desc'             => $lang === 'pl' ? 'show_trade_fair_desc' : 'show_trade_fair_desc_eng', // %%sc_pwe_trade_fair_desc%% || [trade_fair_desc] && [trade_fair_desc_eng]
            'sc_pwe_trade_fair_conference_title' => $lang === 'pl' ? 'show_trade_fair_conference_title' : 'show_trade_fair_conference_title_eng', // %%sc_pwe_trade_fair_conference_title%% || [sc_pwe_trade_fair_conference_title]
            'sc_pwe_trade_fair_full_desc'        => 'sc_pwe_trade_fair_full_desc', // %%sc_pwe_trade_fair_full_desc%% || [trade_fair_full_desc]
            'sc_pwe_text_news'                   => 'sc_pwe_text_news', // %%sc_pwe_text_news%% || [sc_pwe_text_news]
            'sc_pwe_text_for_visitors'           => 'sc_pwe_text_for_visitors', // %%sc_pwe_text_for_visitors%% || [sc_pwe_text_for_visitors]
            'sc_pwe_text_for_exhibitors'         => 'sc_pwe_text_for_exhibitors', // %%sc_pwe_text_for_exhibitors%% || [sc_pwe_text_for_exhibitors]
            'sc_pwe_text_add_calendar'           => 'sc_pwe_text_add_calendar', // %%sc_pwe_text_add_calendar%% || [sc_pwe_text_add_calendar]
            'sc_pwe_text_gallery'                => 'sc_pwe_text_gallery', // %%sc_pwe_text_gallery%% || [sc_pwe_text_gallery]
            'sc_pwe_text_org_info'               => 'sc_pwe_text_org_info', // %%sc_pwe_text_org_info%% || [sc_pwe_text_org_info]
            'sc_pwe_text_exh_catalog'            => 'sc_pwe_text_exh_catalog', // %%sc_pwe_text_exh_catalog%% || [sc_pwe_text_exh_catalog]
            'sc_pwe_text_events'                 => 'sc_pwe_text_events', // %%sc_pwe_text_events%% || [sc_pwe_text_events]
            'sc_pwe_text_contact'                => 'sc_pwe_text_contact', // %%sc_pwe_text_contact%% || [sc_pwe_text_contact]
            'sc_pwe_text_fair_plan'              => 'sc_pwe_text_fair_plan', // %%sc_pwe_text_fair_plan%% || [sc_pwe_text_fair_plan]
            'sc_pwe_text_registration'           => 'sc_pwe_text_registration', // %%sc_pwe_text_registration%% || [sc_pwe_text_registration]
            'sc_pwe_text_promote_yourself'       => 'sc_pwe_text_promote_yourself', // %%sc_pwe_text_promote_yourself%% || [sc_pwe_text_promote_yourself]
            'sc_pwe_text_become_an_exhibitor'    => 'sc_pwe_text_become_an_exhibitor', // %%sc_pwe_text_become_an_exhibitor%% || [sc_pwe_text_become_an_exhibitor]
            'sc_pwe_text_store'                  => 'sc_pwe_text_store', // %%sc_pwe_text_store%% || [sc_pwe_text_store]
        ];
    }

    private function other_shortcodes_map() {
        $lang = PWE_LANG;

        return [
            'pwe_name_pl',
            'pwe_name_en',
            'pwe_desc_pl',
            'pwe_desc_en',
            'pwe_fair_id',
            'pwe_short_desc_pl',
            'pwe_short_desc_en',
            'pwe_full_desc_pl',
            'pwe_full_desc_en',
            'pwe_date_start',
            'pwe_date_start_hour',
            'pwe_date_end',
            'pwe_date_end_hour',
            'pwe_edition',
            'pwe_visitors',
            'pwe_visitors_foreign',
            'pwe_exhibitors',
            'pwe_countries',
            'pwe_area',
            'pwe_statistics_year_curr',
            'pwe_visitors_prev',
            'pwe_visitors_foreign_prev',
            'pwe_exhibitors_prev',
            'pwe_countries_prev',
            'pwe_area_prev',
            'pwe_statistics_year_prev',
            'pwe_hall',
            'pwe_hall_entrance',
            'pwe_color_accent',
            'pwe_color_main2',
            'pwe_badge',
            'pwe_facebook',
            'pwe_instagram',
            'pwe_linkedin',
            'pwe_youtube',
            'pwe_catalog',
            'pwe_catalog_id',
            'pwe_catalog_archive',
            'pwe_catalog_id_archive',
            'pwe_category_pl',
            'pwe_category_en',
            'pwe_industry',
            'pwe_group',
            'pwe_conference_name',
            'pwe_conference_title_pl',
            'pwe_conference_title_en',
            'pwe_conference_desc_pl',
            'pwe_conference_desc_en',
            'pwe_about_title_pl',
            'pwe_about_title_en',
            'pwe_about_desc_pl',
            'pwe_about_desc_en',
            'pwe_mailing_header_url'
        ];
    }

    private function translates_shortcodes_map() {
        $lang = PWE_Functions::lang();

        return [
            'pwe_name_'. $lang,
            'pwe_desc_'. $lang,
            'pwe_short_desc_'. $lang,
            'pwe_full_desc_'. $lang,
            'pwe_conference_title_'. $lang,
            'pwe_conference_desc_'. $lang,
            'pwe_about_title_'. $lang,
            'pwe_about_desc_'. $lang,
            'pwe_category_'. $lang
        ];
    }

    // ALL SHORTCODES END <------------------------------------------------------------------------------<

    public function register_shortcodes() {
        // If the shortcode already exists, remove it
        foreach ($this->get_shortcodes_map() as $tag => $callback) {
            if (shortcode_exists($tag)) {
                remove_shortcode($tag);
            }
            add_shortcode($tag, [$this, $callback]);
        }

        // Dynamic URL shortcodes
        $this->register_url_shortcodes();
    }

    private function shorten_value($value, $length = 30) {
        $value = wp_strip_all_tags((string) $value);
        $value = trim($value);

        if (mb_strlen($value) <= $length) {
            return $value;
        }

        return mb_substr($value, 0, $length) . '...';
    }

    public function enqueue_admin_assets($hook) {
        if (!isset($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== 'pwe-system-shortcodes') {
            return;
        }

        $script_file = PWE_SYSTEM_PATH . 'assets/js/shortcodes-admin.js';
        if (!is_file($script_file)) {
            return;
        }

        wp_enqueue_script(
            'pwe-system-shortcodes-admin',
            PWE_SYSTEM_URL . 'assets/js/shortcodes-admin.js',
            [],
            (string) filemtime($script_file),
            true
        );
    }

    private function get_shortcode_description($tag) {
        $tag = strtolower((string) $tag);

        $exact = [
            'trade_fair_name' => 'Nazwa targów w bieżącej wersji językowej.',
            'trade_fair_name_eng' => 'Nazwa targów w języku angielskim.',
            'trade_fair_desc' => 'Opis targów w języku polskim.',
            'trade_fair_desc_eng' => 'Opis targów w języku angielskim.',
            'trade_fair_desc_short' => 'Krótki opis targów w języku polskim.',
            'trade_fair_desc_short_eng' => 'Krótki opis targów w języku angielskim.',
            'trade_fair_date' => 'Data targów.',
            'trade_fair_date_multilang' => 'Data targów dopasowana do wskazanego języka.',
            'trade_fair_first_day' => 'Pierwszy dzień targów.',
            'trade_fair_second_day' => 'Drugi dzień targów.',
            'trade_fair_third_day' => 'Trzeci dzień targów.',
            'trade_fair_catalog' => 'Adres URL katalogu wystawców.',
            'trade_fair_catalog_id' => 'Identyfikator katalogu wystawców.',
            'trade_fair_catalog_archive' => 'Adres URL archiwalnego katalogu wystawców.',
            'trade_fair_catalog_id_archive' => 'Identyfikator archiwalnego katalogu wystawców.',
            'trade_fair_catalog_year' => 'Rok katalogu wystawców.',
            'pwe_name_pl' => 'Nazwa targów w języku polskim.',
            'pwe_name_en' => 'Nazwa targów w języku angielskim.',
            'pwe_desc_pl' => 'Opis targów w języku polskim.',
            'pwe_desc_en' => 'Opis targów w języku angielskim.',
            'pwe_short_desc_pl' => 'Krótki opis targów w języku polskim.',
            'pwe_short_desc_en' => 'Krótki opis targów w języku angielskim.',
            'pwe_full_desc_pl' => 'Pełny opis targów w języku polskim.',
            'pwe_full_desc_en' => 'Pełny opis targów w języku angielskim.',
            'pwe_date_start' => 'Data rozpoczęcia targów.',
            'pwe_date_start_hour' => 'Godzina rozpoczęcia targów.',
            'pwe_date_end' => 'Data zakończenia targów.',
            'pwe_date_end_hour' => 'Godzina zakończenia targów.',
            'pwe_fair_id' => 'Identyfikator targów w CAP.',
            'pwe_edition' => 'Numer lub nazwa edycji targów.',
            'pwe_visitors' => 'Liczba odwiedzających w bieżących statystykach.',
            'pwe_exhibitors' => 'Liczba wystawców w bieżących statystykach.',
            'pwe_countries' => 'Liczba reprezentowanych krajów.',
            'pwe_area' => 'Powierzchnia targów.',
            'pwe_hall' => 'Hala targowa.',
            'pwe_hall_entrance' => 'Wejście do hali targowej.',
            'pwe_facebook' => 'Adres profilu Facebook.',
            'pwe_instagram' => 'Adres profilu Instagram.',
            'pwe_linkedin' => 'Adres profilu LinkedIn.',
            'pwe_youtube' => 'Adres kanału YouTube.',
            'pwe_catalog' => 'Adres URL katalogu wystawców.',
            'pwe_catalog_id' => 'Identyfikator katalogu wystawców.',
            'pwe_group' => 'Grupa targowa.',
            'pwe_industry' => 'Branża targów.',
            'pwe_badge' => 'Badge / oznaczenie targów.',
        ];

        if (isset($exact[$tag])) {
            return $exact[$tag];
        }

        $rules = [
            'contact_email' => 'Adres e-mail kontaktowy.',
            'contact_phone' => 'Numer telefonu kontaktowego.',
            'contact_person' => 'Osoba kontaktowa.',
            'contact' => 'Dane kontaktowe targów.',
            'conference_title' => 'Tytuł konferencji.',
            'conference_desc' => 'Opis konferencji.',
            'conference' => 'Dane konferencji.',
            'registration' => 'Informacje dotyczące rejestracji.',
            'ticket' => 'Informacje dotyczące biletów.',
            'facebook' => 'Adres profilu Facebook.',
            'instagram' => 'Adres profilu Instagram.',
            'linkedin' => 'Adres profilu LinkedIn.',
            'youtube' => 'Adres kanału YouTube.',
            'catalog' => 'Dane katalogu wystawców.',
            'date_start' => 'Data rozpoczęcia targów.',
            'date_end' => 'Data zakończenia targów.',
            'date' => 'Data związana z targami.',
            'hall_entrance' => 'Wejście do hali targowej.',
            'hall' => 'Hala targowa.',
            'visitors' => 'Statystyka odwiedzających.',
            'exhibitors' => 'Statystyka wystawców.',
            'countries' => 'Statystyka krajów.',
            'area' => 'Powierzchnia targów.',
            'edition' => 'Edycja targów.',
            'category' => 'Kategoria targów.',
            'industry' => 'Branża targów.',
            'group' => 'Grupa targowa.',
            'color' => 'Kolor używany w identyfikacji targów.',
            'badge' => 'Badge / oznaczenie targów.',
            'name' => 'Nazwa związana z targami.',
            'desc' => 'Opis związany z targami.',
            'url_' => 'Adres URL / link używany na stronie.',
        ];

        foreach ($rules as $needle => $description) {
            if (strpos($tag, $needle) !== false) {
                return $description;
            }
        }

        return 'Dane systemowe PWE dostępne przez shortcode.';
    }

    private function render_shortcode_list_item($syntax, $tag, $value = '', $description = '') {
        if ($description === '') {
            $description = $this->get_shortcode_description($tag);
        }

        $search = trim($tag . ' ' . $description);
        ?>
        <li class="pwe-shortcode-item" data-shortcode-search="<?php echo esc_attr($search); ?>">
            <div class="pwe-shortcode-item__main">
                <code><?php echo esc_html($syntax); ?></code>
                <span class="pwe-shortcode-description"><?php echo esc_html($description); ?></span>
            </div>
            <?php if ($value !== '') : ?>
                <small class="pwe-shortcode-value"><?php echo esc_html($this->shorten_value($value)); ?></small>
            <?php endif; ?>
        </li>
        <?php
    }

    public function add_menu() {
        add_submenu_page(
            "pwe-system",
            "PWE Shortcodes",
            "Shortcodes",
            "manage_options",
            "pwe-system-shortcodes",
            [$this, 'theme_options_page']
        );
    }

    public function theme_options_page() {

        $allowed_tabs = ['main', 'dates', 'contact', 'social', 'catalog', 'other'];

        $active_tab = isset($_GET['tab'])
            ? sanitize_key(wp_unslash($_GET['tab']))
            : 'main';

        if (!in_array($active_tab, $allowed_tabs, true)) {
            $active_tab = 'main';
        }

        $settings_pages = [
            'main'    => 'pwe-code-checker',
            'dates'   => 'pwe-code-checker-dates',
            'contact' => 'pwe-code-checker-contact',
            'social'  => 'pwe-code-checker-social',
            'catalog' => 'pwe-code-checker-catalog',
            'other'   => 'pwe-code-checker-other',
        ];

        $current_settings_page = $settings_pages[$active_tab];

        ?>
        <div id="pweShortcodes" class="wrap pwe-system-wrap pwe-system-module-page pwe-shortcodes">
            <?php
            if (class_exists('PWE_System_Admin') && method_exists('PWE_System_Admin', 'render_module_header')) {
                PWE_System_Admin::render_module_header(
                    'PWE SYSTEM / SHORTCODES',
                    'Shortcody',
                    'Centralne dane PWE dostępne jako shortcody WordPress, pola Gravity Forms i zmienne dla SEO.',
                    'dashicons-editor-code'
                );
            }
            settings_errors();
            ?>

            <?php if ($active_tab === 'main') : ?>
                <div class="pwe-shortcode-searchbar">
                    <span class="dashicons dashicons-search" aria-hidden="true"></span>
                    <input type="search" id="pwe-shortcode-search" placeholder="Szukaj po nazwie lub opisie shortcodu…" autocomplete="off">
                    <span id="pwe-shortcode-search-count" class="pwe-shortcode-search-count"></span>
                </div>
            <?php endif; ?>

            <h2 class="nav-tab-wrapper">
                <a
                    href="<?php echo esc_url(admin_url('admin.php?page=pwe-system-shortcodes&tab=main')); ?>"
                    class="nav-tab <?php echo $active_tab === 'main' ? 'nav-tab-active' : ''; ?>"
                >
                    Główne
                </a>
                <a
                    href="<?php echo esc_url(admin_url('admin.php?page=pwe-system-shortcodes&tab=dates')); ?>"
                    class="nav-tab <?php echo $active_tab === 'dates' ? 'nav-tab-active' : ''; ?>"
                >
                    Daty
                </a>
                <a
                    href="<?php echo esc_url(admin_url('admin.php?page=pwe-system-shortcodes&tab=catalog')); ?>"
                    class="nav-tab <?php echo $active_tab === 'catalog' ? 'nav-tab-active' : ''; ?>"
                >
                    Katalog
                </a>
                <a
                    href="<?php echo esc_url(admin_url('admin.php?page=pwe-system-shortcodes&tab=social')); ?>"
                    class="nav-tab <?php echo $active_tab === 'social' ? 'nav-tab-active' : ''; ?>"
                >
                    Social Media
                </a>
                <a
                    href="<?php echo esc_url(admin_url('admin.php?page=pwe-system-shortcodes&tab=contact')); ?>"
                    class="nav-tab <?php echo $active_tab === 'contact' ? 'nav-tab-active' : ''; ?>"
                >
                    Kontakt
                </a>
                <a
                    href="<?php echo esc_url(admin_url('admin.php?page=pwe-system-shortcodes&tab=other')); ?>"
                    class="nav-tab <?php echo $active_tab === 'other' ? 'nav-tab-active' : ''; ?>"
                >
                    Inne
                </a>
            </h2>

            <?php if ($active_tab === 'main') : ?>
                <div class="postbox-container">
                    <div class="col-wrap">
                        <div class="postbox">
                            <div class="inside">
                                <div class="main">
                                    <p style="text-align:center;"><strong>O PWE Shortcodes</strong></p>
                                    <hr>
                                    <p>
                                        Wtyczka pobiera dane z bazy danych CAP (PWE Centralized Administration Panel) i na ich podstawie generuje shortcody,
                                        które można wykorzystać w dowolnym miejscu na stronie WordPress oraz w formularzach Gravity Forms.
                                    </p>
                                    <p><strong>Przykład użycia na stronie:</strong> <code>[trade_fair_name]</code></p>

                                    <details class="shortcodes-box">
                                        <summary>Dostępne shortkody (WordPress)</summary>
                                        <ul>
                                            <?php
                                            foreach ($this->get_shortcodes_map() as $tag => $callback) {

                                                $value = '';
                                                if (is_callable([$this, $callback])) {
                                                    $value = call_user_func([$this, $callback]);
                                                }
                                                $this->render_shortcode_list_item('[' . $tag . ']', $tag, $value);
                                            }
                                            ?>
                                        </ul>
                                    </details>

                                    <p><strong>Przykład użycia w formularzu Gravity Forms:</strong> <code>{trade_fair_name}</code></p>

                                    <details class="shortcodes-box">
                                        <summary>Dostępne shortkody Gravity Forms</summary>
                                        <ul>
                                            <?php
                                            foreach ($this->get_gf_shortcodes_map() as $tag => $callback) {

                                                $value = '';
                                                if (is_callable([$this, $callback])) {
                                                    $value = call_user_func([$this, $callback]);
                                                }
                                                $this->render_shortcode_list_item('{' . $tag . '}', $tag, $value, 'Pole Gravity Forms: ' . $this->get_shortcode_description($tag));
                                            }
                                            ?>
                                        </ul>
                                    </details>

                                    <p><strong>Przykład użycia w polach Yoast SEO:</strong> <code>%%sc_pwe_trade_fair_desc%%</code></p>

                                    <details class="shortcodes-box">
                                        <summary>Dostępne shortkody Yoast SEO</summary>
                                        <ul>
                                            <?php
                                            foreach ($this->get_yoast_shortcodes_map() as $key => $callback) {

                                                $value = '';

                                                if (is_callable([$this, $callback])) {
                                                    $value = call_user_func([$this, $callback]);
                                                }
                                                $this->render_shortcode_list_item('%%' . $key . '%%', $key, $value, 'Zmienne Yoast SEO: ' . $this->get_shortcode_description($key));
                                            }
                                            ?>
                                        </ul>
                                    </details>

                                    <p><strong>Dodatkowe shortkody:</strong> <code>[shortcode]</code></p>

                                    <details class="shortcodes-box">
                                        <summary>Dodatkowe shortkody</summary>
                                        <ul>
                                            <?php
                                            foreach ($this->other_shortcodes_map() as $shortcode) {
                                                $this->render_shortcode_list_item('[' . $shortcode . ']', $shortcode, do_shortcode('[' . $shortcode . ']'));
                                            }
                                            ?>
                                        </ul>

                                        <summary>Przetłumaczalne shortkody [pwe_name_{lang}]</summary>
                                        <ul>
                                            <?php
                                            foreach ($this->translates_shortcodes_map() as $shortcode) {
                                                $this->render_shortcode_list_item('[' . $shortcode . ']', $shortcode, do_shortcode('[' . $shortcode . ']'));
                                            }
                                            ?>
                                        </ul>

                                        <summary>
                                            Shortkody URL <code>[url_{key}]</code>
                                            <br><small>[url_{key} lang="de"] - wymusza język</small>
                                            <br><small>[url_{key} absolute="1"] - zwraca https://domena/url_{key}/</small>
                                        </summary>

                                        <ul>
                                            <?php
                                            $urls_data = $this->get_urls_data();

                                            foreach (array_keys($urls_data) as $url_key) {
                                                $url_key = sanitize_key($url_key);

                                                if ($url_key === '') {
                                                    continue;
                                                }

                                                $shortcode = 'url_' . $url_key;

                                                $relative_url = do_shortcode(
                                                    '[' . $shortcode . ']'
                                                );

                                                $absolute_url = do_shortcode(
                                                    '[' . $shortcode . ' absolute="1"]'
                                                );

                                                $this->render_shortcode_list_item(
                                                    '[' . $shortcode . ']',
                                                    $shortcode,
                                                    $relative_url,
                                                    'Adres URL / link dla klucza „' . $url_key . '”.'
                                                );
                                            }
                                            ?>
                                        </ul>
                                    </details>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="postbox-container">
                <div class="col-wrap">
                    <div class="form-wrap">
                        <form method="POST" action="options.php" enctype="multipart/form-data">
                            <div class="postbox">
                                <div class="inside">
                                    <div class="main">
                                        <?php
                                        settings_fields("pwe_code_checker");
                                        do_settings_sections($current_settings_page);
                                        submit_button();
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }


    public function register_settings() {

        // Main section
        add_settings_section("pwe_code_checker", "PWE Shortcodes", [$this, 'header_section'], "pwe-code-checker");
        add_settings_section("pwe_code_checker_dates", "Daty", [$this, 'header_section'], "pwe-code-checker-dates");
        add_settings_section("pwe_code_checker_contact", "Kontakt", [$this, 'header_section'], "pwe-code-checker-contact");
        add_settings_section("pwe_code_checker_social", "Social", [$this, 'header_section'], "pwe-code-checker-social");
        add_settings_section("pwe_code_checker_catalog", "Katalog", [$this, 'header_section'], "pwe-code-checker-catalog");
        add_settings_section("pwe_code_checker_other", "Inne", [$this, 'header_section'], "pwe-code-checker-other");

        // List of fields for registration - zakładka Główne
        $fields = [
            'trade_fair_name' => 'Nazwa targów PL<hr><p>[trade_fair_name]</p>',
            'trade_fair_name_eng' => 'Nazwa targów EN<hr><p>[trade_fair_name_eng]</p>',
            'trade_fair_desc' => 'Opis targów PL<hr><p>[trade_fair_desc]</p>',
            'trade_fair_desc_eng' => 'Opis targów EN<hr><p>[trade_fair_desc_eng]</p>',
            'trade_fair_desc_short' => 'Skrócony Opis targów PL<hr><p>[trade_fair_desc_short]</p>',
            'trade_fair_desc_short_eng' => 'Skrócony Opis targów EN<hr><p>[trade_fair_desc_short_eng]</p>',

            'trade_fair_hall' => 'Hala targów<hr><p>[trade_fair_hall]</p>',
            'trade_fair_hall_entrance' => 'Wejścia na targi <hr><p>[trade_fair_hall_entrance]</p>',

            'trade_fair_edition' => 'Numer Edycji targów<hr><p>[trade_fair_edition]</p>',
            'trade_fair_accent' => 'Kolor Accent (Main) strony<hr><p>[trade_fair_accent]</p>',
            'trade_fair_main2' => 'Kolor Main2 (secondary)<hr><p>[trade_fair_main2]</p>',
            'trade_fair_conference' => 'Główna nazwa konferencji <hr><p>[trade_fair_conference]</p>',
            'trade_fair_conference_title' => 'Tytuł konferencji (PL) <hr><p>[trade_fair_conference_title]</p>',
            'trade_fair_conference_title_eng' => 'Tytuł konferencji (EN) <hr><p>[trade_fair_conference_title_eng]</p>',
            'trade_fair_badge' => 'Początek nazwy badge -> ..._gosc_a6 <hr><p>[trade_fair_badge]</p>',
            'trade_fair_feed_prefix' => 'Prefiks feed formularzy<hr><p>[trade_fair_feed_prefix]</p>',

            'trade_fair_group' => 'Grupa targów<hr><p>[trade_fair_group]</p>',
            'trade_fair_domainadress' => 'Adres strony<hr><p>[trade_fair_domainadress]</p>',
            'trade_fair_actualyear' => 'Aktualny rok<hr><p>[trade_fair_actualyear]</p>',
        ];

        // Zakładka Daty
        $date_fields = [
            'trade_fair_datetotimer' => 'Data targów do licznika<hr><p>[trade_fair_datetotimer]</p>',
            'trade_fair_enddata' => 'Data zakończenia targów do licznika<hr><p>[trade_fair_enddata]</p>',
            'trade_fair_date_custom_format' => 'Data targów [D-D|M|Y]<hr><p>[trade_fair_date_custom_format]</p>',
            'trade_fair_date' => 'Data Targów PL<hr><p>[trade_fair_date]</p>',
            'trade_fair_date_eng' => 'Data Targów EN<hr><p>[trade_fair_date_eng]</p>',
            'trade_fair_date_multilang' => 'Data targów (język automatyczny)<hr><p>[trade_fair_date_multilang]</p>',
            'trade_fair_first_day' => 'Pierwszy dzień targów<hr><p>[trade_fair_first_day]</p>',
            'trade_fair_second_day' => 'Drugi dzień targów<hr><p>[trade_fair_second_day]</p>',
            'trade_fair_third_day' => 'Trzeci dzień targów<hr><p>[trade_fair_third_day]</p>',
            'trade_fair_1stbuildday' => 'Data pierwszego dnia zabudowy<hr><p>[trade_fair_1stbuildday]</p>',
            'trade_fair_2ndbuildday' => 'Data drugiego dnia zabudowy<hr><p>[trade_fair_2ndbuildday]</p>',
            'trade_fair_1stdismantlday' => 'Data pierwszego dnia rozbiórki<hr><p>[trade_fair_1stdismantlday]</p>',
            'trade_fair_2nddismantlday' => 'Data drugiego dnia rozbiórki<hr><p>[trade_fair_2nddismantlday]</p>',
            'trade_fair_branzowy' => 'Data dni branżowych targów<hr><p>[trade_fair_branzowy]</p>',
            'trade_fair_branzowy_eng' => 'Data dni branżowych targów (ENG)<hr><p>[trade_fair_branzowy_eng]</p>',
        ];

        // Zakładka Social
        $social_fields = [
            'trade_fair_facebook' => 'Adres wydarzenia na Facebook<hr><p>[trade_fair_facebook]</p>',
            'trade_fair_instagram' => 'Adres wydarzenia na Instagram<hr><p>[trade_fair_instagram]</p>',
            'trade_fair_linkedin' => 'Adres wydarzenia na LinkedIn<hr><p>[trade_fair_linkedin]</p>',
            'trade_fair_youtube' => 'Adres wydarzenia na YouTube<hr><p>[trade_fair_youtube]</p>',
        ];

        // Zakładka Katalog
        $catalog_fields = [
            'trade_fair_catalog' => 'ID/IDs katalogu/ów wystawców (OLD)<hr><p>[trade_fair_catalog]</p>',
            'trade_fair_catalog_archive' => 'Rok-ID archiwalnych katalogów wystawców (OLD)<hr><p>[trade_fair_catalog_archive]</p>',
            'trade_fair_catalog_id' => 'ID/IDs katalogu/ów wystawców (NEW)<hr><p>[trade_fair_catalog_id]</p>',
            'trade_fair_catalog_id_archive' => 'Rok-ID archiwalnych katalogów wystawców (NEW)<hr><p>[trade_fair_catalog_id_archive]</p>',
            'trade_fair_catalog_year' => 'Rok aktualnego katalogu wystawców<hr><p>[trade_fair_catalog_year]</p>',
        ];

        // Zakładka Kontakt
        $contact_fields = [
            'trade_fair_rejestracja' => 'Adres email do automatycznej odpowiedzi<hr><p>[trade_fair_rejestracja]</p>',
            'trade_fair_contact' => 'Adres email do formularza kontaktu<hr><p>[trade_fair_contact]</p>',

            'trade_fair_contact_service_name' => 'Imię i nazwisko osoby kontaktowej biura obsługi<hr><p>[trade_fair_contact_service_name]</p>',
            'trade_fair_contact_service_phone' => 'Numer telefonu osoby kontaktowej biura obsługi<hr><p>[trade_fair_contact_service_phone]</p>',
            'trade_fair_contact_service_email' => 'Adres email osoby kontaktowej biura obsługi<hr><p>[trade_fair_contact_service_email]</p>',

            'trade_fair_contact_tech' => 'Adres email do formularza kontaktu działu technicznego<hr><p>[trade_fair_contact_tech]</p>',

            'trade_fair_contact_media' => 'Adres email do formularza kontaktu działu marketingowego i media<hr><p>[trade_fair_contact_media]</p>',
            'trade_fair_contact_media_phone' => 'Numer telefonu do formularza kontaktu działu marketingowego i media<hr><p>[trade_fair_contact_media_phone]</p>',
            'trade_fair_contact_media_name' => 'Imię i nazwisko osoby kontaktowej działu marketingowego i media<hr><p>[trade_fair_contact_media_name]</p>',

            'trade_fair_contact_media_person_name' => 'Imię i nazwisko osoby kontaktowej działu marketingowego i media<hr><p>[trade_fair_contact_media_person_name]</p>',
            'trade_fair_contact_media_person_phone' => 'Numer telefonu osoby kontaktowej działu marketingowego i media<hr><p>[trade_fair_contact_media_person_phone]</p>',
            'trade_fair_contact_media_person_email' => 'Adres email osoby kontaktowej działu marketingowego i media<hr><p>[trade_fair_contact_media_person_email]</p>',

            'trade_fair_contact_media_person_name_2' => 'Imię i nazwisko osoby kontaktowej działu marketingowego i media 2<hr><p>[trade_fair_contact_media_person_name_2]</p>',
            'trade_fair_contact_media_person_phone_2' => 'Numer telefonu osoby kontaktowej działu marketingowego i media 2<hr><p>[trade_fair_contact_media_person_phone_2]</p>',
            'trade_fair_contact_media_person_email_2' => 'Adres email osoby kontaktowej działu marketingowego i media 2<hr><p>[trade_fair_contact_media_person_email_2]</p>',

            'trade_fair_contact_media_person_name_3' => 'Imię i nazwisko osoby kontaktowej działu marketingowego i media 3<hr><p>[trade_fair_contact_media_person_name_3]</p>',
            'trade_fair_contact_media_person_phone_3' => 'Numer telefonu osoby kontaktowej działu marketingowego i media 3<hr><p>[trade_fair_contact_media_person_phone_3]</p>',
            'trade_fair_contact_media_person_email_3' => 'Adres email osoby kontaktowej działu marketingowego i media 3<hr><p>[trade_fair_contact_media_person_email_3]</p>',

            'trade_fair_contact_medal_ceremony_email' => 'Adres email do obsługi ceremonii medalowej<hr><p>[trade_fair_contact_medal_ceremony_email]</p>',

            'trade_fair_contact_email_vip' => 'Adres email obsługi vip<hr><p>[trade_fair_contact_email_vip]</p>',
            'trade_fair_contact_phone_vip' => 'Numer telefonu obsługi vip<hr><p>[trade_fair_contact_phone_vip]</p>',

            'trade_fair_lidy' => 'Adres email do wysyłania lidów<hr><p>[trade_fair_lidy]</p>',
        ];

        // Zakładka Inne
        $other_fields = [
            'trade_fair_registration_benefits_pl' => 'Benefity rejestracyjne PL<hr><p>[trade_fair_registration_benefits_pl]</p>',
            'trade_fair_registration_benefits_en' => 'Benefity rejestracyjne EN<hr><p>[trade_fair_registration_benefits_en]</p>',
            'trade_fair_ticket_benefits_pl' => 'Benefity biletowe PL<hr><p>[trade_fair_ticket_benefits_pl]</p>',
            'trade_fair_ticket_benefits_en' => 'Benefity biletowe EN<hr><p>[trade_fair_ticket_benefits_en]</p>',
        ];

        // Rejestrujemy pola
        foreach ($fields as $key => $label) {
            add_settings_field($key, $label, [$this, "display_{$key}"], "pwe-code-checker", "pwe_code_checker");
            register_setting("pwe_code_checker", $key);
        }

        foreach ($date_fields as $key => $label) {
            add_settings_field($key, $label, [$this, "display_{$key}"], "pwe-code-checker-dates", "pwe_code_checker_dates");
            register_setting("pwe_code_checker", $key);
        }

        foreach ($contact_fields as $key => $label) {
            add_settings_field($key, $label, [$this, "display_{$key}"], "pwe-code-checker-contact", "pwe_code_checker_contact");
            register_setting("pwe_code_checker", $key);
        }

        foreach ($social_fields as $key => $label) {
            add_settings_field($key, $label, [$this, "display_{$key}"], "pwe-code-checker-social", "pwe_code_checker_social");
            register_setting("pwe_code_checker", $key);
        }

        foreach ($catalog_fields as $key => $label) {
            add_settings_field($key, $label, [$this, "display_{$key}"], "pwe-code-checker-catalog", "pwe_code_checker_catalog");
            register_setting("pwe_code_checker", $key);
        }

        foreach ($other_fields as $key => $label) {
            add_settings_field($key, $label, [$this, "display_{$key}"], "pwe-code-checker-other", "pwe_code_checker_other");
            register_setting("pwe_code_checker", $key);
        }
    }


    public function header_section() { echo ""; }



    // CREATE FIELDS <----------------------------------------------------------------------<

    public function display_trade_fair_name() {
        $pwe_name_pl = shortcode_exists("pwe_name_pl") ? do_shortcode('[pwe_name_pl]') : "";
        $pwe_name_pl_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_name_pl) && $pwe_name_pl !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_name_pl_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_name"
                    id="trade_fair_name"
                    value="<?php echo $pwe_name_pl_available ? $pwe_name_pl : get_option('trade_fair_name'); ?>"
                />
                <p><?php echo $pwe_name_pl_available ? "Dane pobrane z CAP DB" : "np. Warsaw Fleet Expo"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_name_eng() {
        $pwe_name_pl = shortcode_exists("pwe_name_pl") ? do_shortcode('[pwe_name_pl]') : "";
        $pwe_name_en = shortcode_exists("pwe_name_en") ? do_shortcode('[pwe_name_en]') : "";
        $pwe_name_en = !empty($pwe_name_en) ? $pwe_name_en : $pwe_name_pl;
        $pwe_name_en_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_name_en) && $pwe_name_en !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_name_en_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_name_eng"
                    id="trade_fair_name_eng"
                    value="<?php echo $pwe_name_en_available ? $pwe_name_en : get_option('trade_fair_name_eng'); ?>"
                />
                <p><?php echo $pwe_name_en_available ? "Dane pobrane z CAP DB" : "np. Warsaw Fleet Expo"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_desc() {
        $pwe_desc_pl = shortcode_exists("pwe_desc_pl") ? do_shortcode('[pwe_desc_pl]') : "";
        $pwe_desc_pl_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_desc_pl) && $pwe_desc_pl !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_desc_pl_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_desc"
                    id="trade_fair_desc"
                    value="<?php echo $pwe_desc_pl_available ? $pwe_desc_pl : get_option('trade_fair_desc'); ?>"
                />
                <p><?php echo $pwe_desc_pl_available ? "Dane pobrane z CAP DB" : "np. Międzynarodowe targi bla bla bla"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_desc_eng() {
        $pwe_desc_en = shortcode_exists("pwe_desc_en") ? do_shortcode('[pwe_desc_en]') : "";
        $pwe_desc_en_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_desc_en) && $pwe_desc_en !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_desc_en_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_desc_eng"
                    id="trade_fair_desc_eng"
                    value="<?php echo $pwe_desc_en_available ? $pwe_desc_en : get_option('trade_fair_desc_eng'); ?>"
                />
                <p><?php echo $pwe_desc_en_available ? "Dane pobrane z CAP DB" : "np. Międzynarodowe targi bla bla bla"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_desc_short() {
        $pwe_short_desc_pl = shortcode_exists("pwe_short_desc_pl") ? do_shortcode('[pwe_short_desc_pl]') : "";
        $pwe_short_desc_pl_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_short_desc_pl) && $pwe_short_desc_pl !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_short_desc_pl_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_desc_short"
                    id="trade_fair_desc_short"
                    value="<?php echo $pwe_short_desc_pl_available ? $pwe_short_desc_pl : get_option('trade_fair_desc_short'); ?>"
                />
                <p><?php echo $pwe_short_desc_pl_available ? "Dane pobrane z CAP DB" : "np. Międzynarodowe targi bla bla bla"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_desc_short_eng() {
        $pwe_short_desc_pl = shortcode_exists("pwe_short_desc_pl") ? do_shortcode('[pwe_short_desc_pl]') : "";
        $pwe_short_desc_pl_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_short_desc_pl) && $pwe_short_desc_pl !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_short_desc_pl_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_desc_short_eng"
                    id="trade_fair_desc_short_eng"
                    value="<?php echo $pwe_short_desc_pl_available ? $pwe_short_desc_pl : get_option('trade_fair_desc_short_eng'); ?>"
                />
                <p><?php echo $pwe_short_desc_pl_available ? "Dane pobrane z CAP DB" : "np. Międzynarodowe targi bla bla bla"; ?></p>
            </div>
        <?php
    }

    public function get_trade_fair_dates() {
        $pwe_shortcodes_available = empty(get_option("pwe_general_options", [])["pwe_dp_shortcodes_unactive"]);

        $pwe_date_start = shortcode_exists("pwe_date_start") ? do_shortcode("[pwe_date_start]") : "";
        $pwe_date_start_available = (empty(get_option("pwe_general_options", [])["pwe_dp_shortcodes_unactive"]) && !empty($pwe_date_start));
        $pwe_date_end = shortcode_exists("pwe_date_end") ? do_shortcode("[pwe_date_end]") : "";
        $pwe_date_end_available = (empty(get_option("pwe_general_options", [])["pwe_dp_shortcodes_unactive"]) && !empty($pwe_date_end));

        // Getting dates or default values
        $start_date = $pwe_date_start_available ? $pwe_date_start : get_option("trade_fair_datetotimer");
        $end_date = $pwe_date_end_available ? $pwe_date_end : get_option("trade_fair_enddata");

        // Remove time from date if exists
        $start_date = preg_replace("/^(\d{4}\/\d{2}\/\d{2}) \d{2}:\d{2}$/", "$1", $start_date);
        $end_date = preg_replace("/^(\d{4}\/\d{2}\/\d{2}) \d{2}:\d{2}$/", "$1", $end_date);

        return [$start_date, $end_date, $pwe_date_start_available, $pwe_date_end_available, $pwe_shortcodes_available];
    }

    public function format_trade_fair_date($start_date, $end_date, $lang = "pl") {
        $months = [
            "pl" => [
                "01" => "stycznia",
                "02" => "lutego",
                "03" => "marca",
                "04" => "kwietnia",
                "05" => "maja",
                "06" => "czerwca",
                "07" => "lipca",
                "08" => "sierpnia",
                "09" => "września",
                "10" => "października",
                "11" => "listopada",
                "12" => "grudnia",
            ],
            "en" => [
                "01" => "january",
                "02" => "february",
                "03" => "march",
                "04" => "april",
                "05" => "may",
                "06" => "june",
                "07" => "july",
                "08" => "august",
                "09" => "september",
                "10" => "october",
                "11" => "november",
                "12" => "december",
            ],
            "de" => [
                "01" => "januar",
                "02" => "februar",
                "03" => "märz",
                "04" => "april",
                "05" => "mai",
                "06" => "juni",
                "07" => "juli",
                "08" => "august",
                "09" => "september",
                "10" => "oktober",
                "11" => "november",
                "12" => "dezember",
            ],
            "it" => [
                "01" => "gennaio",
                "02" => "febbraio",
                "03" => "marzo",
                "04" => "aprile",
                "05" => "maggio",
                "06" => "giugno",
                "07" => "luglio",
                "08" => "agosto",
                "09" => "settembre",
                "10" => "ottobre",
                "11" => "novembre",
                "12" => "dicembre",
            ],
            "lt" => [
                "01" => "sausio",
                "02" => "vasario",
                "03" => "kovo",
                "04" => "balandžio",
                "05" => "gegužės",
                "06" => "birželio",
                "07" => "liepos",
                "08" => "rugpjūčio",
                "09" => "rugsėjo",
                "10" => "spalio",
                "11" => "lapkričio",
                "12" => "gruodžio",
            ],
            "lv" => [
                "01" => "janvāris",
                "02" => "februāris",
                "03" => "marts",
                "04" => "aprīlis",
                "05" => "maijs",
                "06" => "jūnijs",
                "07" => "jūlijs",
                "08" => "augusts",
                "09" => "septembris",
                "10" => "oktobris",
                "11" => "novembris",
                "12" => "decembris",
            ],
            "uk" => [
                "01" => "січня",
                "02" => "лютого",
                "03" => "березня",
                "04" => "квітня",
                "05" => "травня",
                "06" => "червня",
                "07" => "липня",
                "08" => "серпня",
                "09" => "вересня",
                "10" => "жовтня",
                "11" => "листопада",
                "12" => "грудня",
            ],
            "cs" => [
                "01" => "ledna",
                "02" => "února",
                "03" => "března",
                "04" => "dubna",
                "05" => "května",
                "06" => "června",
                "07" => "července",
                "08" => "srpna",
                "09" => "září",
                "10" => "října",
                "11" => "listopadu",
                "12" => "prosince",
            ],
            "sk" => [
                "01" => "januára",
                "02" => "februára",
                "03" => "marca",
                "04" => "apríla",
                "05" => "mája",
                "06" => "júna",
                "07" => "júla",
                "08" => "augusta",
                "09" => "septembra",
                "10" => "októbra",
                "11" => "novembra",
                "12" => "decembra",
            ],
            "ru" => [
                "01" => "января",
                "02" => "февраля",
                "03" => "марта",
                "04" => "апреля",
                "05" => "мая",
                "06" => "июня",
                "07" => "июля",
                "08" => "августа",
                "09" => "сентября",
                "10" => "октября",
                "11" => "ноября",
                "12" => "декабря",
            ],
            "ro" => [
                "01" => "ianuarie",
                "02" => "februarie",
                "03" => "martie",
                "04" => "aprilie",
                "05" => "mai",
                "06" => "iunie",
                "07" => "iulie",
                "08" => "august",
                "09" => "septembrie",
                "10" => "octombrie",
                "11" => "noiembrie",
                "12" => "decembrie",
            ],
            "et" => [
                "01" => "jaanuar",
                "02" => "veebruar",
                "03" => "märts",
                "04" => "aprill",
                "05" => "mai",
                "06" => "juuni",
                "07" => "juuli",
                "08" => "august",
                "09" => "september",
                "10" => "oktoober",
                "11" => "november",
                "12" => "detsember",
            ],
            "hu" => [
                "01" => "január",
                "02" => "február",
                "03" => "március",
                "04" => "április",
                "05" => "május",
                "06" => "június",
                "07" => "július",
                "08" => "augusztus",
                "09" => "szeptember",
                "10" => "október",
                "11" => "november",
                "12" => "december",
            ],
            "es" => [
                "01" => "enero",
                "02" => "febrero",
                "03" => "marzo",
                "04" => "abril",
                "05" => "mayo",
                "06" => "junio",
                "07" => "julio",
                "08" => "agosto",
                "09" => "septiembre",
                "10" => "octubre",
                "11" => "noviembre",
                "12" => "diciembre",
            ],

            "fr" => [
                "01" => "janvier",
                "02" => "février",
                "03" => "mars",
                "04" => "avril",
                "05" => "mai",
                "06" => "juin",
                "07" => "juillet",
                "08" => "août",
                "09" => "septembre",
                "10" => "octobre",
                "11" => "novembre",
                "12" => "décembre",
            ],
        ];

        $lang_key = strtoupper($lang);

        if (empty($start_date) || empty($end_date)) {
            return "";
        }

        $start_parts = explode("/", $start_date);
        $end_parts = explode("/", $end_date);

        $start_day = intval($start_parts[2]);
        $start_month = $start_parts[1];
        $start_year = $start_parts[0];

        $end_day = intval($end_parts[2]);
        $end_month = $end_parts[1];
        $end_year = $end_parts[0];

        $year = $start_year;

        $start_month_name = $months[$lang][$start_month] ?? "";
        $end_month_name = $months[$lang][$end_month] ?? "";

        /*
        * Single day event formatting
        */
        if (
            $start_day === $end_day &&
            $start_month === $end_month &&
            $start_year === $end_year
        ) {
            switch ($lang_key) {
                case "EN":
                    return "$start_month_name $start_day";

                case "DE":
                case "CS":
                case "SK":
                case "LV":
                    return "$start_day. $start_month_name";

                case "LT":
                    return "$start_month_name $start_day d.";

                case "ES":
                    return "$start_day de $start_month_name";

                case "PL":
                case "UK":
                case "RU":
                case "IT":
                case "FR":
                case "RO":
                case "ET":
                case "HU":
                default:
                    return "$start_day $start_month_name";
            }
        }

        /*
        * Multiple day event formatting
        */
        switch ($lang_key) {

            case "PL":
            case "UK":
            case "RU":
                if ($start_month === $end_month) {
                    return "$start_day - $end_day $start_month_name $year";
                }
                return "$start_day $start_month_name - $end_day $end_month_name $year";

            case "EN":
                if ($start_month === $end_month) {
                    return "$start_month_name $start_day-$end_day, $year";
                }
                return "$start_month_name $start_day - $end_month_name $end_day, $year";

            case "DE":
            case "CS":
                if ($start_month === $end_month) {
                    return "$start_day.-$end_day. $start_month_name $year";
                }
                return "$start_day. $start_month_name - $end_day. $end_month_name $year";

            case "IT":
                if ($start_month === $end_month) {
                    return "$start_day-$end_day $start_month_name $year";
                }
                return "$start_day $start_month_name - $end_day $end_month_name $year";

            case "SK":
                if ($start_month === $end_month) {
                    return "$start_day. - $end_day. $start_month_name $year";
                }
                return "$start_day. $start_month_name - $end_day. $end_month_name $year";

            case "LV":
                if ($start_month === $end_month) {
                    return "$start_day. - $end_day. $start_month_name $year";
                }
                return "$start_day. $start_month_name - $end_day. $end_month_name $year";

            case "LT":
                if ($start_month === $end_month) {
                    return "$year m. $start_month_name $start_day-$end_day d.";
                }
                return "$year m. $start_month_name $start_day d. - $end_month_name $end_day d.";

            case "ES":
                if ($start_month === $end_month) {
                    return "$start_day-$end_day de $start_month_name de $year";
                }
                return "$start_day de $start_month_name - $end_day de $end_month_name de $year";

            case "FR":
                if ($start_month === $end_month) {
                    return "$start_day-$end_day $start_month_name $year";
                }
                return "$start_day $start_month_name - $end_day $end_month_name $year";

            default:
                // fallback EN
                if ($start_month === $end_month) {
                    return "$start_month_name $start_day-$end_day, $year";
                }
                return "$start_month_name $start_day - $end_month_name $end_day, $year";
        }
    }

    public function display_trade_fair_date_field($lang = "pl") {
        list($start_date, $end_date, $pwe_date_start_available, $pwe_date_end_available, $pwe_shortcodes_available) = $this->get_trade_fair_dates();

        $current_time = strtotime("now");
        $new_date_comming_soon = ($lang === "pl") ? "Nowa data wkrótce" : "New date comming soon";
        $formatted_date = (empty($start_date) || (!empty($end_date) && (strtotime($end_date . " +20 hours")) < $current_time))
            ? $new_date_comming_soon
            : $this->format_trade_fair_date($start_date, $end_date, $lang);

        $option_name = ($lang === "pl") ? "trade_fair_date" : "trade_fair_date_eng";
        $placeholder = ($lang === "pl") ? "np. 15-16 grudnia 2026" : "e.g. December 15-16, 2026";

        ?>
        <div class="form-field">
            <input
                <?php echo $pwe_shortcodes_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                type="text"
                name="<?php echo $option_name; ?>"
                id="<?php echo $option_name; ?>"
                placeholder="<?php echo $pwe_shortcodes_available ? $formatted_date : get_option($option_name) ?>"
                value="<?php echo !$pwe_shortcodes_available ? get_option($option_name) : "" ?>"
            />
            <p>
                <?php echo ($pwe_date_start_available && $pwe_date_end_available) ? "Dane pobrane z CAP DB" : $placeholder; ?>
            </p>
        </div>
        <?php
    }

    public function display_trade_fair_date() {
        $this->display_trade_fair_date_field("pl");
    }

    public function display_trade_fair_date_eng() {
        $this->display_trade_fair_date_field("en");
    }

    public function display_trade_fair_datetotimer() {
        list($start_date, $end_date, $pwe_date_start_available, $pwe_date_end_available, $pwe_shortcodes_available) = $this->get_trade_fair_dates();

        $lang = strtolower(PWE_LANG);
        $current_time = strtotime("now");

        $date = (empty($start_date) || (!empty($end_date) && (strtotime($end_date . " +20 hours")) < $current_time))
            ? ""
            : $start_date;

        // Check if the result is in YYYY/MM/DD format (10 characters)
        if (is_string($date) && preg_match('/^\d{4}[\/-]\d{2}[\/-]\d{2}$/', $date)) {
            $date .= " 10:00"; // Add hour 10:00
        }

        $option_name = "trade_fair_datetotimer";

        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_shortcodes_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="<?php echo $option_name; ?>"
                    id="<?php echo $option_name; ?>"
                    placeholder="<?php echo $pwe_shortcodes_available ? $date : get_option($option_name) ?>"
                    value="<?php echo !$pwe_shortcodes_available ? get_option($option_name) : "" ?>"
                />
                <p><?php echo $pwe_date_start_available ? "Dane pobrane z CAP DB" : "2025/10/14 10:00 (Y:M:D H:M)"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_enddata() {
        list($start_date, $end_date, $pwe_date_start_available, $pwe_date_end_available, $pwe_shortcodes_available) = $this->get_trade_fair_dates();

        $lang = strtolower(PWE_LANG);
        $current_time = strtotime("now");

        $date = (empty($start_date) || (!empty($end_date) && (strtotime($end_date . " +20 hours")) < $current_time))
            ? ""
            : $end_date;

        // Check if the result is in YYYY/MM/DD format (10 characters)
        if (is_string($date) && preg_match('/^\d{4}[\/-]\d{2}[\/-]\d{2}$/', $date)) {
            $date .= " 17:00"; // Add hour 17:00
        }

        $option_name = "trade_fair_enddata";

        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_shortcodes_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="<?php echo $option_name; ?>"
                    id="<?php echo $option_name; ?>"
                    placeholder="<?php echo $pwe_shortcodes_available ? $date : get_option($option_name) ?>"
                    value="<?php echo !$pwe_shortcodes_available ? get_option($option_name) : "" ?>"
                />
                <p><?php echo $pwe_date_end_available ? "Dane pobrane z CAP DB" : "2025/10/16 10:00 (Y:M:D H:M)"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_date_custom_format() {
        list($start_date, $end_date, $pwe_date_start_available, $pwe_date_end_available, $pwe_shortcodes_available) = $this->get_trade_fair_dates();

        $lang = strtolower(PWE_LANG);
        $new_date_comming_soon = "Nowa data wkrótce / New date comming soon";

        $current_time = strtotime("now");

        $custom_date = (empty($start_date) || (!empty($end_date) && (strtotime($end_date . " +20 hours")) < $current_time))
            ? $new_date_comming_soon
            : PWE_Functions::transform_dates($start_date, $end_date, false);

        $option_name = "trade_fair_date_custom_format";
        $placeholder = "14-16|10|2025 (D-D|M|Y)";

        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_shortcodes_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="<?php echo $option_name; ?>"
                    id="<?php echo $option_name; ?>"
                    placeholder="<?php echo $pwe_shortcodes_available ? $custom_date : get_option($option_name) ?>"
                    value="<?php echo !$pwe_shortcodes_available ? get_option($option_name) : "" ?>"
                />
                <p><?php echo ($pwe_date_start_available && $pwe_date_end_available) ? "Dane pobrane z CAP DB" : $placeholder; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_date_multilang() {
        ?>
            <div class="form-field">
                <input
                    style="pointer-events: none; opacity: 0.5;"
                    type="text"
                    name="trade_fair_name"
                    id="trade_fair_name"
                    value="<?php echo do_shortcode('[trade_fair_date_multilang]') ?>"
                />
                <p>"Dane pobrane z shortcodu [trade_fair_date_multilang]"</p>
            </div>
        <?php
    }

    private function get_trade_fair_days() {
        $dates = do_shortcode('[trade_fair_date_multilang]');
        $dates = trim($dates);

        if (preg_match('/(\d+)\s*-\s*(\d+)\s+([^\s]+)\s+(\d{4})/', $dates, $matches)) {
            $start_day = (int)$matches[1];
            $end_day   = (int)$matches[2];
            $month     = $matches[3];
            $year      = $matches[4];

            $days = [];

            for ($i = $start_day; $i <= $end_day; $i++) {
                $days[] = $i . ' ' . $month;
            }

            return $days;
        }

        return [];
    }

    public function display_trade_fair_first_day() {
        $day = $this->get_trade_fair_day(0);

        $value = !empty($day)
            ? $day
            : get_option('trade_fair_first_day');

        ?>
        <div class="form-field">
            <input
                style="pointer-events: none; opacity: 0.5;"
                type="text"
                name="trade_fair_first_day"
                id="trade_fair_first_day"
                value="<?php echo esc_attr($value); ?>"
            />
            <p>Automatycznie pobierany pierwszy dzień</p>
        </div>
        <?php
    }

    public function display_trade_fair_second_day() {
        $day = $this->get_trade_fair_day(1);

        $value = !empty($day)
            ? $day
            : get_option('trade_fair_second_day');

        ?>
        <div class="form-field">
            <input
                style="pointer-events: none; opacity: 0.5;"
                type="text"
                name="trade_fair_second_day"
                id="trade_fair_second_day"
                value="<?php echo esc_attr($value); ?>"
            />
            <p>Automatycznie pobierany drugi dzień</p>
        </div>
        <?php
    }

    public function display_trade_fair_third_day() {
        
        $day = $this->get_trade_fair_day(2);

        $value = !empty($day)
            ? $day
            : get_option('trade_fair_third_day');

        ?>
        <div class="form-field">
            <input
                style="pointer-events: none; opacity: 0.5;"
                type="text"
                name="trade_fair_third_day"
                id="trade_fair_third_day"
                value="<?php echo esc_attr($value); ?>"
            />
            <p>Automatycznie pobierany trzeci dzień</p>
        </div>
        <?php
    }

    public function display_trade_fair_catalog() {
        $pwe_catalog = shortcode_exists("pwe_catalog") ? do_shortcode('[pwe_catalog]') : "";
        $pwe_catalog_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_catalog) && $pwe_catalog !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_catalog_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_catalog"
                    id="trade_fair_catalog"
                    value="<?php echo $pwe_catalog_available ? $pwe_catalog : get_option('trade_fair_catalog'); ?>"
                />
                <p><?php echo $pwe_catalog_available ? "Dane pobrane z CAP DB" : "np. 69"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_catalog_id() {
        $pwe_catalog_id = shortcode_exists("pwe_catalog_id") ? do_shortcode('[pwe_catalog_id]') : "";
        $pwe_catalog_id_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_catalog_id) && $pwe_catalog_id !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_catalog_id_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_catalog_id"
                    id="trade_fair_catalog_id"
                    value="<?php echo $pwe_catalog_id_available ? $pwe_catalog_id : get_option('trade_fair_catalog_id'); ?>"
                />
                <p><?php echo $pwe_catalog_id_available ? "Dane pobrane z CAP DB" : "np. 69"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_catalog_archive() {
        $pwe_catalog_archive = shortcode_exists("pwe_catalog_archive") ? do_shortcode('[pwe_catalog_archive]') : "";
        $pwe_catalog_archive_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_catalog_archive) && $pwe_catalog_archive !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_catalog_archive_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_catalog_archive"
                    id="trade_fair_catalog_archive"
                    value="<?php echo $pwe_catalog_archive_available ? $pwe_catalog_archive : get_option('trade_fair_catalog_archive'); ?>"
                />
                <p><?php echo $pwe_catalog_archive_available ? "Dane pobrane z CAP DB" : "np. 2025-1447; 2024-1578..."; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_catalog_id_archive() {
        $pwe_catalog_id_archive = shortcode_exists("pwe_catalog_id_archive") ? do_shortcode('[pwe_catalog_id_archive]') : "";
        $pwe_catalog_id_archive_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_catalog_id_archive) && $pwe_catalog_id_archive !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_catalog_id_archive_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_catalog_id_archive"
                    id="trade_fair_catalog_id_archive"
                    value="<?php echo $pwe_catalog_id_archive_available ? $pwe_catalog_id_archive : get_option('trade_fair_catalog_id_archive'); ?>"
                />
                <p><?php echo $pwe_catalog_id_archive_available ? "Dane pobrane z CAP DB" : "np. 2025-47,48;2024-78,79..."; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_catalog_year() {
        $pwe_date_start = shortcode_exists("pwe_date_start") ? do_shortcode('[pwe_date_start]') : "";
        $pwe_date_start_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_date_start));
        $result = $pwe_date_start_available ? date('Y', strtotime($pwe_date_start)) : get_option('trade_fair_catalog_year');
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_date_start_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_catalog_year"
                    id="trade_fair_catalog_year"
                    value="<?php echo $result ?>"
                />
                <p><?php echo $pwe_date_start_available ? "Dane pobrane z CAP DB" : "2026"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_conference() {
        $pwe_conference_name = shortcode_exists("pwe_conference_name") ? do_shortcode('[pwe_conference_name]') : "";
        $pwe_conference_name_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_conference_name) && $pwe_conference_name !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_conference_name_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_conference"
                    id="trade_fair_conference"
                    value="<?php echo $pwe_conference_name_available ? $pwe_conference_name : get_option('trade_fair_conference'); ?>"
                />
                <p><?php echo $pwe_conference_name_available ? "Dane pobrane z CAP DB" : "np. Congress of shit"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_conference_title() {
        $pwe_conference_title_pl = shortcode_exists("pwe_conference_title_pl") ? do_shortcode('[pwe_conference_title_pl]') : "";
        $pwe_conference_title_pl_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_conference_title_pl) && $pwe_conference_title_pl !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_conference_title_pl_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_conference_title"
                    id="trade_fair_conference_title"
                    value="<?php echo $pwe_conference_title_pl_available ? $pwe_conference_title_pl : get_option('trade_fair_conference_title'); ?>"
                />
                <p><?php echo $pwe_conference_title_pl_available ? "Dane pobrane z CAP DB" : "np. Congress of shit"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_conference_title_eng() {
        $pwe_conference_title_en = shortcode_exists("pwe_conference_title_en") ? do_shortcode('[pwe_conference_title_en]') : "";
        $pwe_conference_title_en_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_conference_title_en) && $pwe_conference_title_en !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_conference_title_en_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_conference_title_eng"
                    id="trade_fair_conference_title_eng"
                    value="<?php echo $pwe_conference_title_en_available ? $pwe_conference_title_en : get_option('trade_fair_conference_title_eng'); ?>"
                />
                <p><?php echo $pwe_conference_title_en_available ? "Dane pobrane z CAP DB" : "np. Congress of shit"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_1stbuildday() {
        $pwe_date_start = shortcode_exists("pwe_date_start") ? do_shortcode('[pwe_date_start]') : "";
        $pwe_date_start_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_date_start));
        $result = $pwe_date_start_available ? $pwe_date_start : get_option('trade_fair_datetotimer');
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_date_start_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_1stbuildday"
                    id="trade_fair_1stbuildday"
                    value="<?php echo $pwe_date_start_available ? (date('d.m.Y', strtotime($result . ' -2 day')) . ' 8:00-18:00') : get_option('trade_fair_1stbuildday') ?>"
                />
                <p><?php echo $pwe_date_start_available ? "Dane pobrane z CAP DB" : 'wartość domyślna -> ' . date('d.m.Y', strtotime($result . ' -2 day')) . ' 8:00-18:00' ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_2ndbuildday() {
        $pwe_date_start = shortcode_exists("pwe_date_start") ? do_shortcode('[pwe_date_start]') : "";
        $pwe_date_start_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_date_start));
        $result = $pwe_date_start_available ? (date('d.m.Y', strtotime($pwe_date_start . ' -1 day')) . ' 8:00-20:00') : get_option('trade_fair_2ndbuildday');
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_date_start_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_2ndbuildday"
                    id="trade_fair_2ndbuildday"
                    value="<?php echo $result ?>"
                    />
                <p><?php echo $pwe_date_start_available ? "Dane pobrane z CAP DB" : 'wartość domyślna -> ' . date('d.m.Y', strtotime($result . ' -1 day')) . ' 8:00-18:00' ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_1stdismantlday() {
        $pwe_date_end = shortcode_exists("pwe_date_end") ? do_shortcode('[pwe_date_end]') : "";
        $pwe_date_end_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_date_end));
        $result = $pwe_date_end_available ? $pwe_date_end : get_option('trade_fair_enddata');
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_date_end_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_1stdismantlday"
                    id="trade_fair_1stdismantlday"
                    value="<?php echo $pwe_date_end_available ? date('d.m.Y', strtotime($result)) . ' 17:00-24:00' : get_option('trade_fair_1nddismantlday'); ?>"
                />
                <p><?php echo $pwe_date_end_available ? "Dane pobrane z CAP DB" : 'wartość domyślna -> ' . date('d.m.Y', strtotime($result)) . ' 17:00-24:00' ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_2nddismantlday() {
        $pwe_date_end = shortcode_exists("pwe_date_end") ? do_shortcode('[pwe_date_end]') : "";
        $pwe_date_end_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_date_end));
        $result = $pwe_date_end_available ? $pwe_date_end : get_option('trade_fair_enddata');
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_date_end_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_2nddismantlday"
                    id="trade_fair_2nddismantlday"
                    value="<?php echo $pwe_date_end_available ? date('d.m.Y', strtotime($result . ' +1 day')) . ' 8:00-12:00' : get_option('trade_fair_2nddismantlday'); ?>"
                />
                <p><?php echo $pwe_date_end_available ? "Dane pobrane z CAP DB" : 'wartość domyślna -> ' . date('d.m.Y', strtotime($result . ' +1 day')) . ' 8:00-12:00' ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_actualyear() {
        ?>
            <div class="form-field">
                <input type="text" name="trade_fair_actualyear" id="trade_fair_actualyear" value="<?php echo date('Y') ?>" disabled/>
                <p>"Automatycznie pobierany aktulny rok"</p>
            </div>
        <?php
    }

    public function display_trade_fair_branzowy_field($lang = "pl") {
        $pwe_shortcodes_available = empty(get_option("pwe_general_options", [])["pwe_dp_shortcodes_unactive"]);
        $new_date_comming_soon = ($lang === "pl") ? "Nowa data wkrótce" : "New date comming soon";

        list($start_date, $end_date, $pwe_date_start_available, $pwe_date_end_available) = $this->get_trade_fair_dates();

        $current_time = strtotime("now");
        $new_date_comming_soon = ($lang === "pl") ? "Nowa data wkrótce" : "New date comming soon";

        $option_name = ($lang === "pl") ? "trade_fair_branzowy" : "trade_fair_branzowy_eng";
        $placeholder = ($lang === "pl") ? "np. 15 grudnia 2020" : "e.g. December 15, 2020";

        // no dates → immediate message
        if (empty($start_date)) {
            $industry_day = $new_date_comming_soon;
        } else if (!empty($end_date) && (strtotime($end_date . " +20 hours")) < $current_time) {
            $industry_day = $new_date_comming_soon;
        } else {
            if ($lang === "pl") {
                setlocale(LC_TIME, "pl_PL.UTF-8");
                $industry_day = strftime("%e %B %Y", strtotime($start_date));
            } else {
                $industry_day = date("F j, Y", strtotime($start_date)); // US format
            }
        }

        ?>
        <div class="form-field">
            <input
                <?php echo $pwe_shortcodes_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                type="text"
                name="<?php echo $option_name; ?>"
                id="<?php echo $option_name; ?>"
                placeholder="<?php echo $pwe_shortcodes_available ? $industry_day : "" ?>"
                value="<?php echo $pwe_shortcodes_available ? $industry_day : get_option($option_name) ?>"
            />
            <p>
                <?php
                echo $pwe_shortcodes_available ? "Dane pobrane z CAP DB" : $placeholder;
                ?>
            </p>
        </div>
        <?php
    }

    public function display_trade_fair_branzowy() {
        $this->display_trade_fair_branzowy_field("pl");
    }

    public function display_trade_fair_branzowy_eng() {
        $this->display_trade_fair_branzowy_field("en");
    }

    public function display_trade_fair_hall() {
        $pwe_hall = shortcode_exists("pwe_hall") ? do_shortcode('[pwe_hall]') : "";
        $pwe_hall_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_hall) && $pwe_hall !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_hall_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_hall"
                    id="trade_fair_hall"
                    value="<?php echo $pwe_hall_available ? $pwe_hall : get_option('trade_fair_hall'); ?>"
                />
                <p><?php echo $pwe_hall_available ? "Dane pobrane z CAP DB" : "np -> B albo B1"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_hall_entrance() {
        $pwe_hall_entrance = shortcode_exists("pwe_hall_entrance") ? do_shortcode('[pwe_hall_entrance]') : "";
        $pwe_hall_entrance_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_hall_entrance) && $pwe_hall_entrance !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_hall_entrance_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_hall_entrance"
                    id="trade_fair_hall_entrance"
                    value="<?php echo $pwe_hall_entrance_available ? $pwe_hall_entrance : get_option('trade_fair_hall_entrance'); ?>"
                />
                <p><?php echo $pwe_hall_entrance_available ? "Dane pobrane z CAP DB" : "np -> B16"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_edition() {
        $pwe_edition = shortcode_exists("pwe_edition") ? do_shortcode('[pwe_edition]') : "";
        $pwe_edition_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_edition) && $pwe_edition !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_edition_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_edition"
                    id="trade_fair_edition"
                    value="<?php echo $pwe_edition_available ? $pwe_edition : get_option('trade_fair_edition'); ?>"
                />
                <p><?php echo $pwe_edition_available ? "Dane pobrane z CAP DB" : "np -> 2"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_accent() {
        $pwe_color_accent = shortcode_exists("pwe_color_accent") ? do_shortcode('[pwe_color_accent]') : "";
        $pwe_color_accent_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_color_accent) && $pwe_color_accent !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_color_accent_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_accent"
                    id="trade_fair_accent"
                    value="<?php echo $pwe_color_accent_available ? $pwe_color_accent : get_option('trade_fair_accent'); ?>"
                />
                <p><?php echo $pwe_color_accent_available ? "Dane pobrane z CAP DB" : "np -> #84gj64"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_main2() {
        $pwe_color_main2 = shortcode_exists("pwe_color_main2") ? do_shortcode('[pwe_color_main2]') : "";
        $pwe_color_main2_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_color_main2) && $pwe_color_main2 !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_color_main2_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_main2"
                    id="trade_fair_main2"
                    value="<?php echo $pwe_color_main2_available ? $pwe_color_main2 : get_option('trade_fair_main2'); ?>" />
                <p><?php echo $pwe_color_main2_available ? "Dane pobrane z CAP DB" : "np -> #84gj64"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_badge() {
        $pwe_badge = shortcode_exists("pwe_badge") ? do_shortcode('[pwe_badge]') : "";
        $pwe_badge_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_badge) && $pwe_badge !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo !empty(get_option('trade_fair_badge')) ? "" : ($pwe_badge_available ? "style='pointer-events: none; opacity: 0.5;'" : ""); ?>
                    type="text"
                    name="trade_fair_badge"
                    id="trade_fair_badge"
                    value="<?php echo (!empty(get_option('trade_fair_badge')) ? get_option('trade_fair_badge') : ($pwe_badge_available ? $pwe_badge : '')); ?>"
                />
                <p><?php echo !empty(get_option('trade_fair_badge')) ? "Początek nazwy badge -> ..._gosc_a6 " : ($pwe_badge_available ? "Dane pobrane z CAP DB" : ""); ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_feed_prefix() {
        $badge = get_option('trade_fair_badge');
        $badge = preg_replace('/[^a-zA-Z0-9]/u', '', $badge);
        $feed_prefix = mb_strtoupper(mb_substr($badge, 0, 4));

        ?>
            <div class="form-field">
                <input
                    type="text"
                    name="trade_fair_feed_prefix"
                    id="trade_fair_feed_prefix"
                    value="<?php echo !empty(get_option('trade_fair_feed_prefix')) ? get_option('trade_fair_feed_prefix') : $feed_prefix; ?>"
                />
                <p><?php echo "np. MRGL"; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_domainadress() {
        ?>
            <div class="form-field">
                <input type="text" name="trade_fair_domainadress" id="trade_fair_domainadress" value="<?php echo str_replace('https://', '', home_url()); ?>" disabled/>
                <p>"Automatycznie pobierany adres strony"</p>
            </div>
        <?php
    }

    public function display_trade_fair_facebook() {
        $pwe_facebook = shortcode_exists("pwe_facebook") ? do_shortcode('[pwe_facebook]') : "";
        $pwe_facebook_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_facebook) && $pwe_facebook !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_facebook_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_facebook"
                    id="trade_fair_facebook"
                    value="<?php echo $pwe_facebook_available ? $pwe_facebook : get_option('trade_fair_facebook'); ?>"
                />
                <p><?php echo $pwe_facebook_available ? "Dane pobrane z CAP DB" : "https://facebook/..."; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_instagram() {
        $pwe_instagram = shortcode_exists("pwe_instagram") ? do_shortcode('[pwe_instagram]') : "";
        $pwe_instagram_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_instagram) && $pwe_instagram !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_instagram_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_instagram"
                    id="trade_fair_instagram"
                    value="<?php echo $pwe_instagram_available ? $pwe_instagram : get_option('trade_fair_instagram'); ?>"
                />
                <p><?php echo $pwe_instagram_available ? "Dane pobrane z CAP DB" : "https://instagram/..."; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_linkedin() {
        $pwe_linkedin = shortcode_exists("pwe_linkedin") ? do_shortcode('[pwe_linkedin]') : "";
        $pwe_linkedin_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_linkedin) && $pwe_linkedin !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_linkedin_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_linkedin"
                    id="trade_fair_linkedin"
                    value="<?php echo $pwe_linkedin_available ? $pwe_linkedin : get_option('trade_fair_linkedin'); ?>"
                />
                <p><?php echo $pwe_linkedin_available ? "Dane pobrane z CAP DB" : "https://linkedin/..."; ?></p>
            </div>
        <?php
    }

    public function display_trade_fair_youtube() {
        $pwe_youtube = shortcode_exists("pwe_youtube") ? do_shortcode('[pwe_youtube]') : "";
        $pwe_youtube_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_youtube) && $pwe_youtube !== "");
        ?>
            <div class="form-field">
                <input
                    <?php echo $pwe_youtube_available ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_youtube"
                    id="trade_fair_youtube"
                    value="<?php echo $pwe_youtube_available ? $pwe_youtube : get_option('trade_fair_youtube'); ?>"
                />
                <p><?php echo $pwe_youtube_available ? "Dane pobrane z CAP DB" : "https://youtube/..."; ?></p>
            </div>
        <?php
    }


    private function get_group_contact_default_value($groups_slug, $field = 'email') {
        $pwe_groups_data = PWE_Functions::get_database_groups_data();
        $pwe_groups_contacts_data = PWE_Functions::get_database_groups_contacts_data();

        $current_domain = $_SERVER['HTTP_HOST'] ?? '';

        if (empty($pwe_groups_data) || empty($pwe_groups_contacts_data) || empty($current_domain)) {
            return '';
        }

        foreach ($pwe_groups_data as $group) {
            if ($current_domain != $group->fair_domain) {
                continue;
            }

            foreach ($pwe_groups_contacts_data as $group_contact) {
                if ($group->fair_group != $group_contact->groups_name || $group_contact->groups_slug != $groups_slug) {
                    continue;
                }

                $contact_data = json_decode($group_contact->groups_data);

                if (empty($contact_data) || !is_object($contact_data)) {
                    return '';
                }

                $field_aliases = [
                    'name' => ['name'],
                    'phone' => ['tel'],
                    'email' => ['email'],
                ];

                $aliases = $field_aliases[$field] ?? [$field];

                foreach ($aliases as $alias) {
                    if (isset($contact_data->{$alias}) && trim((string) $contact_data->{$alias}) !== '') {
                        return trim((string) $contact_data->{$alias});
                    }
                }

                return '';
            }
        }

        return '';
    }

    private function show_contact_field_with_default($option_name, $groups_slug, $field = 'email') {
        $option_value = trim((string) get_option($option_name, ''));

        if ($option_value !== '') {
            return $option_value;
        }

        return trim((string) $this->get_group_contact_default_value($groups_slug, $field));
    }

    private function display_contact_field_with_default($option_name, $default_value = '') {
        ?>
            <div class="form-field full-tab-code-system">
                <input
                    type="text"
                    name="<?php echo esc_attr($option_name); ?>"
                    id="<?php echo esc_attr($option_name); ?>"
                    value="<?php echo esc_attr(get_option($option_name)); ?>"
                />
                <p>"wartość domyślna -> <?php echo esc_html($default_value); ?>"</p>
            </div>
        <?php
    }

    public function display_trade_fair_rejestracja() {
        ?>
            <div class="form-field full-tab-code-system">
                <input type="text" name="trade_fair_rejestracja" id="trade_fair_rejestracja" value="<?php echo get_option('trade_fair_rejestracja'); ?>"/>
                <p>"wartość domyślna -> rejestracja@<?php echo $_SERVER['HTTP_HOST']; ?>"</p>
            </div>
        <?php
    }

    public function display_trade_fair_contact() {
        $this->display_contact_field_with_default('trade_fair_contact', $this->get_group_contact_default_value('biuro-ob', 'email'));
    }


    public function display_trade_fair_contact_service_name() {
        $this->display_contact_field_with_default('trade_fair_contact_service_name', $this->get_group_contact_default_value('biuro-ob', 'name'));
    }

    public function display_trade_fair_contact_service_phone() {
        $this->display_contact_field_with_default('trade_fair_contact_service_phone', $this->get_group_contact_default_value('biuro-ob', 'phone'));
    }

    public function display_trade_fair_contact_service_email() {
        $this->display_contact_field_with_default('trade_fair_contact_service_email', $this->get_group_contact_default_value('biuro-ob', 'email'));
    }

    public function display_trade_fair_contact_media_phone() {
        $this->display_contact_field_with_default('trade_fair_contact_media_phone', $this->get_group_contact_default_value('ob-marketing-media', 'phone'));
    }

    public function display_trade_fair_contact_media_name() {
        $this->display_contact_field_with_default('trade_fair_contact_media_name', $this->get_group_contact_default_value('ob-marketing-media', 'name'));
    }

    public function display_trade_fair_contact_media_person_name() {
        $this->display_contact_field_with_default('trade_fair_contact_media_person_name', $this->get_group_contact_default_value('osoba-kontakt', 'name'));
    }

    public function display_trade_fair_contact_media_person_phone() {
        $this->display_contact_field_with_default('trade_fair_contact_media_person_phone', $this->get_group_contact_default_value('osoba-kontakt', 'phone'));
    }

    public function display_trade_fair_contact_media_person_email() {
        $this->display_contact_field_with_default('trade_fair_contact_media_person_email', $this->get_group_contact_default_value('osoba-kontakt', 'email'));
    }

    public function display_trade_fair_contact_media_person_name_2() {
        $this->display_contact_field_with_default('trade_fair_contact_media_person_name_2', $this->get_group_contact_default_value('osoba-kontakt-2', 'name'));
    }

    public function display_trade_fair_contact_media_person_phone_2() {
        $this->display_contact_field_with_default('trade_fair_contact_media_person_phone_2', $this->get_group_contact_default_value('osoba-kontakt-2', 'phone'));
    }

    public function display_trade_fair_contact_media_person_email_2() {
        $this->display_contact_field_with_default('trade_fair_contact_media_person_email_2', $this->get_group_contact_default_value('osoba-kontakt-2', 'email'));
    }

    public function display_trade_fair_contact_media_person_name_3() {
        $this->display_contact_field_with_default('trade_fair_contact_media_person_name_3', $this->get_group_contact_default_value('osoba-kontakt-3', 'name'));
    }

    public function display_trade_fair_contact_media_person_phone_3() {
        $this->display_contact_field_with_default('trade_fair_contact_media_person_phone_3', $this->get_group_contact_default_value('osoba-kontakt-3', 'phone'));
    }

    public function display_trade_fair_contact_media_person_email_3() {
        $this->display_contact_field_with_default('trade_fair_contact_media_person_email_3', $this->get_group_contact_default_value('osoba-kontakt-3', 'email'));
    }

    public function display_trade_fair_contact_tech() {
        $this->display_contact_field_with_default('trade_fair_contact_tech', $this->get_group_contact_default_value('ob-tech-wyst', 'email'));
    }

    public function display_trade_fair_contact_media() {
        $this->display_contact_field_with_default('trade_fair_contact_media', $this->get_group_contact_default_value('ob-marketing-media', 'email'));
    }

    public function display_trade_fair_lidy() {
        $this->display_contact_field_with_default('trade_fair_lidy', $this->get_group_contact_default_value('lidy', 'email'));
    }

    public function display_trade_fair_contact_email_vip() {
        $this->display_contact_field_with_default('trade_fair_contact_email_vip', $this->get_group_contact_default_value('obsluga-vip', 'email'));
    }

    public function display_trade_fair_contact_phone_vip() {
        $this->display_contact_field_with_default('trade_fair_contact_phone_vip', $this->get_group_contact_default_value('obsluga-vip', 'phone'));
    }

    public function display_trade_fair_contact_medal_ceremony_email() {
        $this->display_contact_field_with_default('trade_fair_contact_medal_ceremony_email', $this->get_group_contact_default_value('obsluga-ceremonia-medalowa', 'email'));
    }

    public function days_difference() {
        $trade_fair_date = do_shortcode('[trade_fair_date_custom_format]');

        if (preg_match('/(\d{2})-(\d{2})\|(\d{2})\|(\d{4})/', $trade_fair_date, $matches)) {
            // $matches[1] = starting day
            // $matches[2] = end day
            // $matches[3] = month
            // $matches[4] = year
            $start_date = DateTime::createFromFormat('d-m-Y', $matches[1] . '-' . $matches[3] . '-' . $matches[4]);
            $end_date = DateTime::createFromFormat('d-m-Y', $matches[2] . '-' . $matches[3] . '-' . $matches[4]);

            // Calculate the difference in days
            $interval = $start_date->diff($end_date);
            $days_difference = $interval->days + 1;
        } else {
            $days_difference = 3;
        }

        return $days_difference;
    }

    public function display_trade_fair_registration_benefits_pl() {
        if (empty(get_option('trade_fair_registration_benefits_pl'))) {
            $html_code = '
            <ul>
                <li><strong>wejścia na targi po rejestracji przez '. $this->days_difference() .' dni</strong></li>
                <li><strong>możliwość udziału w konferencjach</strong> lub warsztatach na zasadzie “wolnego słuchacza”</li>
                <li>darmowy parking</li>
            </ul>';
        } else {
            $html_code = get_option('trade_fair_registration_benefits_pl');
        }
        ?>
            <div class="form-field">
                <textarea id="trade_fair_registration_benefits_pl" name="trade_fair_registration_benefits_pl" rows="5" cols="100"><?php echo $html_code; ?></textarea>
            </div>
        <?php
    }

    public function display_trade_fair_registration_benefits_en() {
        if (empty(get_option('trade_fair_registration_benefits_en'))) {
            $html_code = '
            <ul>
                <li><strong>access to the trade fair for all '. $this->days_difference() .' days upon registration</strong></li>
                <li><strong>the chance to join conferences</strong> or workshops as a listener</li>
                <li>free parking</li>
            </ul>';
        } else {
            $html_code = get_option('trade_fair_registration_benefits_en');
        }
        ?>
            <div class="form-field">
                <textarea id="trade_fair_registration_benefits_en" name="trade_fair_registration_benefits_en" rows="5" cols="100"><?php echo $html_code; ?></textarea>
            </div>
        <?php
    }

    public function display_trade_fair_ticket_benefits_pl() {
        if (empty(get_option('trade_fair_ticket_benefits_pl'))) {
            $html_code = '
            <ul>
                <li><strong>fast track</strong> - szybkie wejście na targi dedykowaną bramką przez '. $this->days_difference() .' dni</li>
                <li><strong>imienny pakiet</strong> - targowy przesyłany kurierem przed wydarzeniem</li>
                <li><strong>welcome pack</strong> - przygotowany specjalnie przez wystawców</li>
                <li>obsługa concierge</li>
                <li>możliwość udziału w konferencjach i&nbsp; warsztatach</li>
                <li>darmowy parking</li>
            </ul>';
        } else {
            $html_code = get_option('trade_fair_ticket_benefits_pl');
        }
        ?>
            <div class="form-field">
                <textarea id="trade_fair_ticket_benefits_pl" name="trade_fair_ticket_benefits_pl" rows="5" cols="100"><?php echo $html_code; ?></textarea>
            </div>
        <?php
    }

    public function display_trade_fair_ticket_benefits_en() {
        if (empty(get_option('trade_fair_ticket_benefits_en'))) {
            $html_code = '
            <ul>
                <li><strong>fast track access</strong> – skip the line and enter the trade fair through a dedicated priority gate for all '. $this->days_difference() .' days</li>
                <li><strong>Personalized trade fair package</strong> - delivered by courier to your address before the event</li>
                <li><strong>welcome pack</strong> - a special set of materials and gifts prepared by exhibitors</li>
                <li>Concierge service</li>
                <li>Access to conferences and workshops</li>
                <li>Free parking</li>
            </ul>';
        } else {
            $html_code = get_option('trade_fair_ticket_benefits_en');
        }
        ?>
            <div class="form-field">
                <textarea id="trade_fair_ticket_benefits_en" name="trade_fair_ticket_benefits_en" rows="5" cols="100"><?php echo $html_code; ?></textarea>
            </div>
        <?php
    }

    public function display_trade_fair_group() {
        $pwe_groups_data = PWE_Functions::get_database_groups_data();

        foreach ($pwe_groups_data as $group) {
            if ($_SERVER['HTTP_HOST'] == $group->fair_domain) {
                $current_group = $group->fair_group;
            }
        }
        ?>
            <div class="form-field">
                <input
                    <?php echo !empty($current_group) ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>
                    type="text"
                    name="trade_fair_group"
                    id="trade_fair_group"
                    value="<?php echo !empty($current_group) ? $current_group : get_option('trade_fair_group'); ?>"
                />
                <p><?php echo !empty($current_group) ? "Dane pobrane z CAP DB" : "np -> gr2"; ?></p>
            </div>
        <?php
    }





    // DISPLAYING THE SHORTCODES <----------------------------------------------------------------------<

    public function show_trade_fair_name() {
        $pwe_name_pl = shortcode_exists("pwe_name_pl") ? do_shortcode('[pwe_name_pl]') : "";
        $pwe_name_pl_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_name_pl) && $pwe_name_pl !== "");
        $result = $pwe_name_pl_available ? $pwe_name_pl : get_option('trade_fair_name');

        $result = html_entity_decode($result, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $result;
    }

    public function show_trade_fair_name_eng() {
        $pwe_name_pl = shortcode_exists("pwe_name_pl") ? do_shortcode('[pwe_name_pl]') : "";
        $pwe_name_en = shortcode_exists("pwe_name_en") ? do_shortcode('[pwe_name_en]') : "";
        $pwe_name_en = !empty($pwe_name_en) ? $pwe_name_en : $pwe_name_pl;
        $pwe_name_en_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_name_en) && $pwe_name_en !== "");
        $result = $pwe_name_en_available ? $pwe_name_en : get_option('trade_fair_name_eng');

        $result = html_entity_decode($result, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $result;
    }

    public function show_trade_fair_desc() {
        $pwe_desc_pl = shortcode_exists("pwe_desc_pl") ? do_shortcode('[pwe_desc_pl]') : "";
        $pwe_desc_pl_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_desc_pl) && $pwe_desc_pl !== "");
        $result = $pwe_desc_pl_available ? $pwe_desc_pl : get_option('trade_fair_desc');
        return $result;
    }

    public function show_trade_fair_desc_eng() {
        $pwe_desc_en = shortcode_exists("pwe_desc_en") ? do_shortcode('[pwe_desc_en]') : "";
        $pwe_desc_en_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_desc_en) && $pwe_desc_en !== "");
        $result = $pwe_desc_en_available ? $pwe_desc_en : get_option('trade_fair_desc_eng');
        return $result;
    }

    public function show_trade_fair_desc_short() {
        $pwe_short_desc_pl = shortcode_exists("pwe_short_desc_pl") ? do_shortcode('[pwe_short_desc_pl]') : "";
        $pwe_short_desc_pl_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_short_desc_pl) && $pwe_short_desc_pl !== "");
        $result = $pwe_short_desc_pl_available ? $pwe_short_desc_pl : get_option('trade_fair_desc_short');

        if (empty($result)) {
            return get_option('trade_fair_desc');
        }
        return $result;
    }

    public function show_trade_fair_desc_short_eng() {
        $pwe_short_desc_en = shortcode_exists("pwe_short_desc_en") ? do_shortcode('[pwe_short_desc_en]') : "";
        $pwe_short_desc_en_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_short_desc_en) && $pwe_short_desc_en !== "");
        $result = $pwe_short_desc_en_available ? $pwe_short_desc_en : get_option('trade_fair_desc_short_eng');
        if (empty($result)) {
            return get_option('trade_fair_desc_eng');
        }
        return $result;
    }

    public function show_trade_fair_datetotimer() {
        list($start_date, $end_date, $pwe_date_start_available, $pwe_date_end_available, $pwe_shortcodes_available) = $this->get_trade_fair_dates();

        $lang = strtolower(PWE_LANG);
        $current_time = strtotime("now");
        $new_date_comming_soon = ($lang === "pl") ? "Nowa data wkrótce" : "New date comming soon";

        $date = (empty($start_date) || (!empty($end_date) && (strtotime($end_date . " +20 hours")) < $current_time))
            ? ($pwe_shortcodes_available ? "" : get_option('trade_fair_datetotimer'))
            : $start_date;

        // Check if the result is in YYYY/MM/DD format (10 characters)
        if (is_string($date) && preg_match('/^\d{4}[\/-]\d{2}[\/-]\d{2}$/', $date)) {
            $date .= " 10:00"; // Add hour 10:00
        }

        return $date;
    }

    public function show_trade_fair_enddata() {
        list($start_date, $end_date, $pwe_date_start_available, $pwe_date_end_available, $pwe_shortcodes_available) = $this->get_trade_fair_dates();

        $lang = strtolower(PWE_LANG);
        $current_time = strtotime("now");
        $new_date_comming_soon = ($lang === "pl") ? "Nowa data wkrótce" : "New date comming soon";

        $date = (empty($start_date) || (!empty($end_date) && (strtotime($end_date . " +20 hours")) < $current_time))
            ? ($pwe_shortcodes_available ? "" : get_option('trade_fair_enddata'))
            : $end_date;

        // Check if the result is in YYYY/MM/DD format (10 characters)
        if (is_string($date) && preg_match('/^\d{4}[\/-]\d{2}[\/-]\d{2}$/', $date)) {
            $date .= " 17:00"; // Add hour 10:00
        }

        return $date;
    }

    public function show_trade_fair_date_custom_format() {
        list($start_date, $end_date, $pwe_date_start_available, $pwe_date_end_available, $pwe_shortcodes_available) = $this->get_trade_fair_dates();

        $lang = strtolower(PWE_LANG);
        $current_time = strtotime("now");
        $new_date_comming_soon = ($lang === "pl") ? "Nowa data wkrótce" : "New date comming soon";

        $date = (empty($start_date) || (!empty($end_date) && (strtotime($end_date . " +20 hours")) < $current_time))
            ? ($pwe_shortcodes_available ? $new_date_comming_soon : get_option('trade_fair_date_custom_format'))
            :  PWE_Functions::transform_dates($start_date, $end_date, false);

        return $date;
    }

    public function show_trade_fair_date() {
        list($start_date, $end_date, $pwe_date_start_available, $pwe_date_end_available, $pwe_shortcodes_available) = $this->get_trade_fair_dates();

        $lang = strtolower(PWE_LANG);
        $current_time = strtotime("now");
        $new_date_comming_soon = ($lang === "pl") ? "Nowa data wkrótce" : "New date comming soon";

        $date = (empty($start_date) || (!empty($end_date) && (strtotime($end_date . " +20 hours")) < $current_time))
            ? ($pwe_shortcodes_available ? $new_date_comming_soon : get_option('trade_fair_date'))
            :  $this->format_trade_fair_date($start_date, $end_date, "pl");

        return $date;
    }

    public function show_trade_fair_date_eng() {
        list($start_date, $end_date, $pwe_date_start_available, $pwe_date_end_available, $pwe_shortcodes_available) = $this->get_trade_fair_dates();

        $lang = strtolower(PWE_LANG);
        $current_time = strtotime("now");
        $new_date_comming_soon = ($lang === "pl") ? "Nowa data wkrótce" : "New date comming soon";

        $date = (empty($start_date) || (!empty($end_date) && (strtotime($end_date . " +20 hours")) < $current_time))
            ? ($pwe_shortcodes_available ? $new_date_comming_soon : get_option('trade_fair_date_eng'))
            :  $this->format_trade_fair_date($start_date, $end_date, "en");

        return $date;
    }

    public function show_trade_fair_date_multilang($atts = []) {

        $atts = shortcode_atts([
            'lang' => '',
        ], $atts, 'trade_fair_date_multilang');

        list(
            $start_date,
            $end_date,
            $pwe_date_start_available,
            $pwe_date_end_available,
            $pwe_shortcodes_available
        ) = $this->get_trade_fair_dates();

        $requested_lang = trim((string) $atts['lang']);

        /*
        * {{lang}} can only be resolved in the context of a Gravity Forms notification.
        *
        * Outside of Gravity Forms, we treat this as automatic.
        */
        if ($requested_lang === '{{lang}}') {
            $requested_lang = '';
        }

        if ($requested_lang !== '') {
            // Hardcoded language, e.g. lang="cs"
            $lang = $requested_lang;
        } elseif (
            class_exists('PWE_Functions') &&
            is_callable(['PWE_Functions', 'lang'])
        ) {
            // Automatic language detection
            $lang = PWE_Functions::lang();
        } elseif (defined('PWE_LANG') && PWE_LANG) {
            $lang = PWE_LANG;
        } else {
            $lang = determine_locale();
        }

        // cs_CZ, cs-CZ, CS -> cs
        $lang = strtolower(trim((string) $lang));
        $lang = str_replace('_', '-', $lang);
        $lang = explode('-', $lang)[0];
        $lang = sanitize_key($lang);

        $supported_languages = [
            'pl',
            'en',
            'it',
            'cs',
            'de',
            'lv',
            'lt',
            'sk',
            'uk',
            'et',
            'ro',
            'ru',
            'hu',
            'es',
            'fr',
        ];

        if (!in_array($lang, $supported_languages, true)) {
            $lang = 'en';
        }

        $coming_soon_translations = [
            'pl' => 'Nowa data wkrótce',
            'en' => 'New date coming soon',
            'it' => 'Nuova data in arrivo',
            'cs' => 'Nový termín již brzy',
            'de' => 'Neuer Termin folgt in Kürze',
            'lv' => 'Jauns datums drīzumā',
            'lt' => 'Nauja data netrukus',
            'sk' => 'Nový termín už čoskoro',
            'uk' => 'Нова дата незабаром',
            'et' => 'Uus kuupäev varsti',
            'ro' => 'Noua dată în curând',
            'ru' => 'Новая дата скоро',
            'hu' => 'Hamarosan új dátum érkezik',
            'es' => 'Nueva fecha próximamente',
            'fr' => 'Nouvelle date prochainement',
        ];

        $current_time = current_time('timestamp');

        $event_has_ended =
            !empty($end_date) &&
            strtotime($end_date . ' +20 hours') < $current_time;

        if (empty($start_date) || $event_has_ended) {
            return $pwe_shortcodes_available
                ? $coming_soon_translations[$lang]
                : (string) get_option(
                    'trade_fair_date_' . $lang,
                    $coming_soon_translations[$lang]
                );
        }

        return $this->format_trade_fair_date(
            $start_date,
            $end_date,
            $lang
        );
    }

    private function get_trade_fair_day(int $offset = 0): string {
        list(
            $start_date,
            $end_date,
            $pwe_date_start_available,
            $pwe_date_end_available,
            $pwe_shortcodes_available
        ) = $this->get_trade_fair_dates();

        if (empty($start_date)) {
            return '';
        }

        try {
            $date = DateTime::createFromFormat('Y/m/d', $start_date);

            if (!$date) {
                return '';
            }

            if ($offset > 0) {
                $date->modify('+' . $offset . ' day');
            }
        } catch (Exception $e) {
            return '';
        }

        // Aktualny język strony
        if (
            class_exists('PWE_Functions') &&
            is_callable(['PWE_Functions', 'lang'])
        ) {
            $lang = PWE_Functions::lang();
        } elseif (defined('PWE_LANG') && PWE_LANG) {
            $lang = PWE_LANG;
        } else {
            $lang = determine_locale();
        }

        // np. en_US -> en
        $lang = strtolower(trim((string) $lang));
        $lang = str_replace('_', '-', $lang);
        $lang = explode('-', $lang)[0];
        $lang = sanitize_key($lang);

        $single_date = $date->format('Y/m/d');

        return $this->format_trade_fair_date(
            $single_date,
            $single_date,
            $lang
        );
    }

    public function show_trade_fair_first_day() {
        $day = $this->get_trade_fair_day(0);

        return !empty($day)
            ? $day
            : get_option('trade_fair_first_day');
    }

    public function show_trade_fair_second_day() {
        $day = $this->get_trade_fair_day(1);

        return !empty($day)
            ? $day
            : get_option('trade_fair_second_day');
    }

    public function show_trade_fair_third_day() {
        $day = $this->get_trade_fair_day(2);

        return !empty($day)
            ? $day
            : get_option('trade_fair_third_day');
    }

    public function show_trade_fair_catalog() {
        $pwe_catalog = shortcode_exists("pwe_catalog") ? do_shortcode('[pwe_catalog]') : "";
        $pwe_catalog_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_catalog) && $pwe_catalog !== "");
        $result = $pwe_catalog_available ? $pwe_catalog : get_option('trade_fair_catalog');
        return $result;
    }

    public function show_trade_fair_catalog_id() {
        $pwe_catalog_id = shortcode_exists("pwe_catalog_id") ? do_shortcode('[pwe_catalog_id]') : "";
        $pwe_catalog_id_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_catalog_id) && $pwe_catalog_id !== "");
        $result = $pwe_catalog_id_available ? $pwe_catalog_id : get_option('trade_fair_catalog_id');
        return $result;
    }

    public function show_trade_fair_catalog_archive() {
        $pwe_catalog_archive = shortcode_exists("pwe_catalog_archive") ? do_shortcode('[pwe_catalog_archive]') : "";
        $pwe_catalog_archive_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_catalog_archive) && $pwe_catalog_archive !== "");
        $result = $pwe_catalog_archive_available ? $pwe_catalog_archive : get_option('trade_fair_catalog_archive');
        return $result;
    }

    public function show_trade_fair_catalog_id_archive() {
        $pwe_catalog_id_archive = shortcode_exists("pwe_catalog_id_archive") ? do_shortcode('[pwe_catalog_id_archive]') : "";
        $pwe_catalog_id_archive_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_catalog_id_archive) && $pwe_catalog_id_archive !== "");
        $result = $pwe_catalog_id_archive_available ? $pwe_catalog_id_archive : get_option('trade_fair_catalog_id_archive');
        return $result;
    }

    public function show_trade_fair_catalog_year() {
        $pwe_date_start = shortcode_exists("pwe_date_start") ? do_shortcode('[pwe_date_start]') : "";
        $pwe_date_start_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_date_start));
        $result = $pwe_date_start_available ? date('Y', strtotime($pwe_date_start)) : get_option('trade_fair_catalog_year');
        return $result;
    }

    public function show_trade_fair_conference() {
        $pwe_conference_name = shortcode_exists("pwe_conference_name") ? do_shortcode('[pwe_conference_name]') : "";
        $pwe_conference_name_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_conference_name) && $pwe_conference_name !== "");
        $result = $pwe_conference_name_available ? $pwe_conference_name : get_option('trade_fair_conference');

        $result = html_entity_decode($result, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $result;
    }

    public function show_trade_fair_conference_title() {
        $pwe_conference_title_pl = shortcode_exists("pwe_conference_title_pl") ? do_shortcode('[pwe_conference_title_pl]') : "";
        $pwe_conference_title_pl_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_conference_title_pl) && $pwe_conference_title_pl !== "");
        $result = $pwe_conference_title_pl_available ? $pwe_conference_title_pl : get_option('trade_fair_conference_title');

        $result = html_entity_decode($result, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $result;
    }

    public function show_trade_fair_conference_title_eng() {
        $pwe_conference_title_en = shortcode_exists("pwe_conference_title_en") ? do_shortcode('[pwe_conference_title_en]') : "";
        $pwe_conference_title_en_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_conference_title_en) && $pwe_conference_title_en !== "");
        $result = $pwe_conference_title_en_available ? $pwe_conference_title_en : get_option('trade_fair_conference_title_eng');

        $result = html_entity_decode($result, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $result;
    }

    public function show_trade_fair_1stbuildday() {
        $pwe_date_start = shortcode_exists("pwe_date_start") ? do_shortcode('[pwe_date_start]') : "";
        $pwe_date_start_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_date_start));
        $result = $pwe_date_start_available ? (date('d.m.Y', strtotime($pwe_date_start . ' -2 day')) . ' 8:00-18:00') : get_option('trade_fair_1stbuildday');

        return $result;
    }

    public function show_trade_fair_2ndbuildday() {
        $pwe_date_start = shortcode_exists("pwe_date_start") ? do_shortcode('[pwe_date_start]') : "";
        $pwe_date_start_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_date_start));
        $result = $pwe_date_start_available ? (date('d.m.Y', strtotime($pwe_date_start . ' -1 day')) . ' 8:00-20:00') : get_option('trade_fair_2ndbuildday');

        return $result;
    }

    public function show_trade_fair_1stdismantlday() {
        $pwe_date_end = shortcode_exists("pwe_date_end") ? do_shortcode('[pwe_date_end]') : "";
        $pwe_date_end_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_date_end));
        // if (!empty(get_option('trade_fair_1nddismantlday'))) {
        // 	$result = get_option('trade_fair_1nddismantlday');
        // } else {
            $result = date('d.m.Y', strtotime($pwe_date_end_available ? $pwe_date_end : get_option('trade_fair_enddata'))) . ' 17:00-24:00';
        // }
        return $result;
    }

    public function show_trade_fair_2nddismantlday() {
        $pwe_date_end = shortcode_exists("pwe_date_end") ? do_shortcode('[pwe_date_end]') : "";
        $pwe_date_end_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_date_end));
        // if (!empty(get_option('trade_fair_2nddismantlday'))) {
        // 	$result = get_option('trade_fair_2nddismantlday');
        // } else {
            $result = date('d.m.Y', strtotime(($pwe_date_end_available ? $pwe_date_end : get_option('trade_fair_enddata')) . ' +1 day')) . ' 8:00-12:00';
        // }
        return $result;
    }

    public function show_trade_fair_hall() {
        $pwe_hall = shortcode_exists("pwe_hall") ? do_shortcode('[pwe_hall]') : "";
        $pwe_hall_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_hall) && $pwe_hall !== "");
        $result = $pwe_hall_available ? $pwe_hall : get_option('trade_fair_hall');

        return $result;
    }

    public function show_trade_fair_hall_entrance() {
        $pwe_hall_entrance = shortcode_exists("pwe_hall_entrance") ? do_shortcode('[pwe_hall_entrance]') : "";
        $pwe_hall_entrance_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_hall_entrance) && $pwe_hall_entrance !== "");
        $result = $pwe_hall_entrance_available ? $pwe_hall_entrance : get_option('trade_fair_hall_entrance');

        return $result;
    }

    public function show_trade_fair_edition($entry = null, $fields = null) {
        $result = '';

        $pwe_edition = shortcode_exists("pwe_edition") ? do_shortcode('[pwe_edition]') : "";
        $pwe_edition_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_edition) && $pwe_edition !== "");

        $trade_fair_edition = $pwe_edition_available ? $pwe_edition : get_option('trade_fair_edition');

        // Sprawdzenie wartości i ustawienie wyniku
        if ($trade_fair_edition === '1') {
            $result = get_locale() === "pl_PL" ? 'Premierowa' : 'Premier';
        } else {
            $result = $trade_fair_edition . '.';
        }

        return $result;
    }

    public function show_trade_fair_accent() {
        $pwe_color_accent = shortcode_exists("pwe_color_accent") ? do_shortcode('[pwe_color_accent]') : "";
        $pwe_color_accent_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_color_accent) && $pwe_color_accent !== "");
        $result = $pwe_color_accent_available ? $pwe_color_accent : get_option('trade_fair_accent');
        return $result;
    }

    public function show_trade_fair_main2() {
        $pwe_color_main2 = shortcode_exists("pwe_color_main2") ? do_shortcode('[pwe_color_main2]') : "";
        $pwe_color_main2_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_color_main2) && $pwe_color_main2 !== "");
        $result = $pwe_color_main2_available ? $pwe_color_main2 : get_option('trade_fair_main2');
        return $result;
    }

    public function trade_fair_branzowy_result($lang = "pl") {
        list($start_date, $end_date, $pwe_date_start_available, $pwe_date_end_available, $pwe_shortcodes_available) = $this->get_trade_fair_dates();

        $new_date_comming_soon = ($lang === "pl") ? "Nowa data wkrótce" : "New date comming soon";
        $current_time = strtotime("now");

        // no dates → immediate message
        if (empty($start_date)) {
            return $new_date_comming_soon;
        }

        // end date expired → message
        if (!empty($end_date) && (strtotime($end_date . " +20 hours")) < $current_time) {
            return $new_date_comming_soon;
        }

        // correct date → we format only the first day
        if ($lang === "pl") {
            setlocale(LC_TIME, "pl_PL.UTF-8");
            $industry_day = strftime("%e %B %Y", strtotime($start_date));
        } else {
            $industry_day = date("F j, Y", strtotime($start_date)); // US format
        }

        return $pwe_shortcodes_available ? $industry_day : (($lang === "pl") ? get_option('trade_fair_branzowy') : get_option('trade_fair_branzowy_eng'));
    }

    public function show_trade_fair_branzowy() {
        $result = $this->trade_fair_branzowy_result("pl");

        if (empty($result)) {
            return get_option('trade_fair_date');
        }
        return $result;
    }

    public function show_trade_fair_branzowy_eng() {
        $result = $this->trade_fair_branzowy_result("en");

        if (empty($result)) {
            return get_option('trade_fair_date_eng');
        }
        return $result;
    }

    public function show_trade_fair_badge() {
        $pwe_badge = shortcode_exists("pwe_badge") ? do_shortcode('[pwe_badge]') : "";
        $pwe_badge_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_badge) && $pwe_badge !== "");
        $result = !empty(get_option('trade_fair_badge')) ? get_option('trade_fair_badge') : ($pwe_badge_available ? $pwe_badge : "");
        return $result;
    }

    public function show_trade_fair_feed_prefix() {
        $badge = get_option('trade_fair_badge');
        $badge = preg_replace('/[^a-zA-Z0-9]/u', '', $badge);
        $feed_prefix = mb_strtoupper(mb_substr($badge, 0, 4));

        $result = !empty(get_option('trade_fair_feed_prefix')) ? get_option('trade_fair_feed_prefix') : $feed_prefix;
        return $result;
    }

    public function show_trade_fair_facebook() {
        $pwe_facebook = shortcode_exists("pwe_facebook") ? do_shortcode('[pwe_facebook]') : "";
        $pwe_facebook_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_facebook) && $pwe_facebook !== "");
        $result = $pwe_facebook_available ? $pwe_facebook : get_option('trade_fair_facebook');
        if (empty($result)) {
            return "https://warsawexpo.eu";
        }
        return $result;
    }

    public function show_trade_fair_instagram() {
        $pwe_instagram = shortcode_exists("pwe_instagram") ? do_shortcode('[pwe_instagram]') : "";
        $pwe_instagram_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_instagram) && $pwe_instagram !== "");
        $result = $pwe_instagram_available ? $pwe_instagram : get_option('trade_fair_instagram');
        if (empty($result)) {
            return "https://warsawexpo.eu";
        }
        return $result;
    }

    public function show_trade_fair_linkedin() {
        $pwe_linkedin = shortcode_exists("pwe_linkedin") ? do_shortcode('[pwe_linkedin]') : "";
        $pwe_linkedin_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_linkedin) && $pwe_linkedin !== "");
        $result = $pwe_linkedin_available ? $pwe_linkedin : get_option('trade_fair_linkedin');
        if (empty($result)) {
            return "https://warsawexpo.eu";
        }
        return $result;
    }

    public function show_trade_fair_youtube() {
        $pwe_youtube = shortcode_exists("pwe_youtube") ? do_shortcode('[pwe_youtube]') : "";
        $pwe_youtube_available = (empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']) && !empty($pwe_youtube) && $pwe_youtube !== "");
        $result = $pwe_youtube_available ? $pwe_youtube : get_option('trade_fair_youtube');
        if (empty($result)) {
            return "https://warsawexpo.eu";
        }
        return $result;
    }

    public function show_trade_fair_domainadress() {
        $result = $_SERVER['HTTP_HOST'];
        if(empty($result)){
            return str_replace('https://', '', home_url());
        }
        return $result;
    }

    function get_lang_domain($atts = []) {

        $atts = shortcode_atts([
            'lang' => '',
        ], $atts, 'pwe_lang_domain');

        $requested_lang = trim((string) $atts['lang']);

        // {{lang}} can only be resolved in the context of a Gravity Forms notification.
        // Outside of Gravity Forms, we treat this as automatic.
        if ($requested_lang === '{{lang}}') {
            $requested_lang = '';
        }

        if ($requested_lang !== '') {
            $lang = $requested_lang;
        } elseif (
            class_exists('PWE_Functions') &&
            is_callable(['PWE_Functions', 'lang'])
        ) {
            $lang = PWE_Functions::lang();
        } elseif (defined('PWE_LANG') && PWE_LANG) {
            $lang = PWE_LANG;
        } else {
            $lang = determine_locale();
        }

        // cs_CZ, cs-CZ, CS -> cs
        $lang = strtolower(trim((string) $lang));
        $lang = str_replace('_', '-', $lang);
        $lang = explode('-', $lang)[0];
        $lang = sanitize_key($lang);

        $host = $_SERVER['HTTP_HOST'] ?? '';

        if (empty($host)) {
            $host = wp_parse_url(home_url(), PHP_URL_HOST);
        }

        if ($lang === 'pl' || $lang === '') {
            return $host;
        }

        return $host . '/' . $lang;
    }

    public function show_trade_fair_actualyear() {
        $result = date('Y');
        return $result;
    }

    public function show_trade_fair_rejestracja() {
        $result = get_option('trade_fair_rejestracja');

        if (empty($result)) {
            return 'rejestracja@' . ($_SERVER['HTTP_HOST'] ?? str_replace('https://', '', home_url()));
        }

        return $result;
    }

    public function show_trade_fair_contact() {
        return $this->show_contact_field_with_default('trade_fair_contact', 'biuro-ob', 'email');
    }

    public function show_trade_fair_contact_service_name() {
        return $this->show_contact_field_with_default('trade_fair_contact_service_name', 'biuro-ob', 'name');
    }

    public function show_trade_fair_contact_service_phone() {
        return $this->show_contact_field_with_default('trade_fair_contact_service_phone', 'biuro-ob', 'phone');
    }

    public function show_trade_fair_contact_service_email() {
        return $this->show_contact_field_with_default('trade_fair_contact_service_email', 'biuro-ob', 'email');
    }

    public function show_trade_fair_contact_media_phone() {
        return $this->show_contact_field_with_default('trade_fair_contact_media_phone', 'ob-marketing-media', 'phone');
    }

    public function show_trade_fair_contact_media_name() {
        return $this->show_contact_field_with_default('trade_fair_contact_media_name', 'ob-marketing-media', 'name');
    }

    public function show_trade_fair_contact_media_person_name() {
        return $this->show_contact_field_with_default('trade_fair_contact_media_person_name', 'osoba-kontakt', 'name');
    }

    public function show_trade_fair_contact_media_person_phone() {
        return $this->show_contact_field_with_default('trade_fair_contact_media_person_phone', 'osoba-kontakt', 'phone');
    }

    public function show_trade_fair_contact_media_person_email() {
        return $this->show_contact_field_with_default('trade_fair_contact_media_person_email', 'osoba-kontakt', 'email');
    }

    public function show_trade_fair_contact_tech() {
        return $this->show_contact_field_with_default('trade_fair_contact_tech', 'ob-tech-wyst', 'email');
    }

    public function show_trade_fair_contact_media() {
        return $this->show_contact_field_with_default('trade_fair_contact_media', 'ob-marketing-media', 'email');
    }

    public function show_trade_fair_lidy() {
        return $this->show_contact_field_with_default('trade_fair_lidy', 'lidy', 'email');
    }

    public function show_trade_fair_contact_email_vip() {
        return $this->show_contact_field_with_default('trade_fair_contact_email_vip', 'obsluga-vip', 'email');
    }

    public function show_trade_fair_contact_phone_vip() {
        return $this->show_contact_field_with_default('trade_fair_contact_phone_vip', 'obsluga-vip', 'phone');
    }

    public function show_trade_fair_contact_medal_ceremony_email() {
        return $this->show_contact_field_with_default('trade_fair_contact_medal_ceremony_email', 'obsluga-ceremonia-medalowa', 'email');
    }

    public function show_trade_fair_group() {
        $pwe_groups_data = PWE_Functions::get_database_groups_data();

        foreach ($pwe_groups_data as $group) {
            if ($_SERVER['HTTP_HOST'] == $group->fair_domain) {
                $current_group = $group->fair_group;
            }
        }

        return $current_group;
    }

    public function show_trade_fair_registration_benefits_pl() {
        if (empty(get_option('trade_fair_registration_benefits_pl'))) {
            $result = '
            <ul>
                <li><strong>wejścia na targi po rejestracji przez '. $this->days_difference() .' dni</strong></li>
                <li><strong>możliwość udziału w konferencjach</strong> lub warsztatach na zasadzie “wolnego słuchacza”</li>
                <li>darmowy parking</li>
            </ul>';
        } else {
            $result = get_option('trade_fair_registration_benefits_pl');
        }
        return $result;
    }

    public function show_trade_fair_registration_benefits_en() {
        if (empty(get_option('trade_fair_registration_benefits_en'))) {
            $result = '
            <ul>
                <li><strong>access to the trade fair for all '. $this->days_difference() .' days upon registration</strong></li>
                <li><strong>the chance to join conferences</strong> or workshops as a listener</li>
                <li>free parking</li>
            </ul>';
        } else {
            $result = get_option('trade_fair_registration_benefits_en');
        }
        return $result;
    }

    public function show_trade_fair_ticket_benefits_pl() {
        if (empty(get_option('trade_fair_ticket_benefits_pl'))) {
            $result = '
            <ul>
                <li><strong>fast track</strong> - szybkie wejście na targi dedykowaną bramką przez '. $this->days_difference() .' dni</li>
                <li><strong>imienny pakiet</strong> - targowy przesyłany kurierem przed wydarzeniem</li>
                <li><strong>welcome pack</strong> - przygotowany specjalnie przez wystawców</li>
                <li>obsługa concierge</li>
                <li>możliwość udziału w konferencjach i&nbsp; warsztatach</li>
                <li>darmowy parking</li>
            </ul>';
        } else {
            $result = get_option('trade_fair_ticket_benefits_pl');
        }
        return $result;
    }

    public function show_trade_fair_ticket_benefits_en() {
        if (empty(get_option('trade_fair_ticket_benefits_en'))) {
            $result = '
            <ul>
                <li><strong>fast track access</strong> – skip the line and enter the trade fair through a dedicated priority gate for all '. $this->days_difference() .' days</li>
                <li><strong>Personalized trade fair package</strong> - delivered by courier to your address before the event</li>
                <li><strong>welcome pack</strong> - a special set of materials and gifts prepared by exhibitors</li>
                <li>Concierge service</li>
                <li>Access to conferences and workshops</li>
                <li>Free parking</li>
            </ul>';
        } else {
            $result = get_option('trade_fair_ticket_benefits_en');
        }
        return $result;
    }

    public function show_trade_fair_exhibitor_generator_icons() {
        $fair_group = trim((string) do_shortcode('[trade_fair_group]'));

        $locale = determine_locale();

        $language = substr($locale, 0, 2) === 'en' ? 'en' : 'pl';

        $labels = [
            'pl' => [
                'fast_track'  => 'Fast<br>Track',
                'vip_room'    => 'VIP<br>Room',
                'concierge'   => 'Opieka<br>concierge`a',
                'konferencje' => 'Udział w<br>konferencjach',
                'parking'     => 'Darmowy<br>parking',
                'attractions' => 'Udział w<br>atrakcjach',
            ],
            'en' => [
                'fast_track'  => 'Fast<br>Track',
                'vip_room'    => 'VIP<br>Room',
                'concierge'   => 'Concierge<br>service',
                'konferencje' => 'Conference<br>attendance',
                'parking'     => 'Free<br>parking',
                'attractions' => 'Participation<br>in attractions',
            ],
        ];

        $icons_url = plugins_url(
            'pwe-media/media/generator-gosci-wystawcow-auto-switch/icons/'
        );

        $icons = [
            'fast_track'  => 'fast-track-icon.png',
            'vip_room'    => 'vip-room-icon.png',
            'concierge'   => 'concierge-icon.png',
            'konferencje' => 'conferences-icon.png',
            'parking'     => 'parking-icon.png',
            'attractions' => 'attractions-icon.png',
        ];

        $group_icons = [
            'gr1' => [
                'concierge',
                'konferencje',
                'parking',
            ],
            'gr2' => [
                'fast_track',
                'vip_room',
                'concierge',
                'konferencje',
                'parking',
            ],
            'gr3' => [
                'vip_room',
                'concierge',
                'konferencje',
                'parking',
            ],
            'b2c' => [
                'vip_room',
                'fast_track',
                'concierge',
                'konferencje',
                'parking',
            ],

            'b2c-new' => [
                'vip_room',
                'fast_track',
                'concierge',
                'konferencje',
                'parking',
            ],
        ];

        $selected_icons = $group_icons[$fair_group] ?? $group_icons['gr1'];

        // Keep only icons that have both an image and a translated label
        $selected_icons = array_values(array_filter(
            $selected_icons,
            static function ($icon_key) use ($icons, $labels, $language) {
                return isset(
                    $icons[$icon_key],
                    $labels[$language][$icon_key]
                );
            }
        ));

        $icons_count = count($selected_icons);

        if ($icons_count === 0) {
            return '';
        }

        $cell_width = floor(100 / $icons_count);

        $output = '
            <style>
                @media only screen and (max-width: 550px) {
                    .pwe-generator-icons-row {
                        display: block !important;
                        width: 100% !important;
                        text-align: center !important;
                    }

                    .pwe-generator-icon-cell {
                        display: inline-block !important;
                        width: 30% !important;
                        max-width: 30% !important;
                        box-sizing: border-box !important;
                        vertical-align: top !important;
                    }
                }

                @media only screen and (max-width: 360px) {
                    .pwe-generator-icon-cell {
                        width: 30% !important;
                        max-width: 30% !important;
                    }
                }
            </style>

            <table
                class="pwe-generator-icons-table"
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
                role="presentation"
                style="
                    width:100%;
                    border:0;
                    border-color:transparent;
                    border-collapse:collapse;
                "
            >
                <tr
                    class="pwe-generator-icons-row"
                    align="center"
                    style="
                        border:0;
                        border-color:transparent;
                        text-align:center;
                    "
                >
        ';

        foreach ($selected_icons as $icon_key) {
            $image_url = trailingslashit($icons_url) . $icons[$icon_key];
            $label = $labels[$language][$icon_key];

            $output .= sprintf(
                '
                <td
                    class="pwe-generator-icon-cell"
                    width="%1$d%%"
                    valign="top"
                    align="center"
                    style="
                        width:%1$d%%;
                        padding:18px 4px 0;
                        border:0;
                        border-color:transparent;
                        text-align:center;
                        vertical-align:top;
                    "
                >
                    <img
                        src="%2$s"
                        alt=""
                        width="50"
                        height="50"
                        style="
                            display:block;
                            width:50px;
                            height:50px;
                            object-fit:contain;
                            margin:0 auto 8px;
                            border:0;
                        "
                    >

                    <p style="
                        padding:10px 0 0;
                        margin:0;
                        font-size:12px;
                        line-height:1.2;
                        font-weight:500;
                        text-align:center;
                        color:#886843;
                    ">
                        %3$s
                    </p>
                </td>
                ',
                $cell_width,
                esc_url($image_url),
                wp_kses($label, [
                    'br' => [],
                ])
            );
        }

        $output .= '
                </tr>
            </table>
        ';

        return $output;
    }

    public function show_trade_fair_exhibitor_generator_text() {
        $fair_group = trim((string) do_shortcode('[trade_fair_group]'));

        $locale = determine_locale();

        $language = substr($locale, 0, 2) === 'en' ? 'en' : 'pl';

        $texts = [
            'pl' => [
                'gr1' => 'Pobierz swój identyfikator VIP GOLD i skorzystaj z bezpłatnego wejścia na teren targów, szybkiego wejścia Fast Track, udziału we wszystkich konferencjach branżowych oraz opieki concierge’a.',

                'gr2' => 'Pobierz swój identyfikator VIP GOLD i skorzystaj z bezpłatnego wejścia na teren targów, udziału we wszystkich konferencjach branżowych, wejścia do specjalnie przygotowanej strefy VIP ROOM oraz opieki concierge’a.',

                'gr3' => 'Pobierz swój identyfikator VIP GOLD i skorzystaj z bezpłatnego wejścia na teren targów, welcome packu, udziału we wszystkich konferencjach branżowych, wejścia do specjalnie przygotowanej strefy VIP ROOM oraz opieki concierge’a.',

                'b2c' => 'Pobierz swój identyfikator VIP GOLD i skorzystaj z bezpłatnego wejścia na teren targów, udziału we wszystkich konferencjach branżowych oraz atrakcjach, szybkiego wejścia Fast Track, wejścia do specjalnie przygotowanej strefy VIP ROOM oraz opieki concierge’a. Zaproszenie umożliwia bezpłatny udział pierwszego dnia targów <strong>([trade_fair_first_day])</strong>.',

                'b2c-new' => 'Pobierz swój identyfikator VIP GOLD i skorzystaj z bezpłatnego wejścia na teren targów, udziału we wszystkich konferencjach branżowych oraz atrakcjach, szybkiego wejścia Fast Track, wejścia do specjalnie przygotowanej strefy VIP ROOM oraz opieki concierge’a. Zaproszenie umożliwia bezpłatny udział pierwszego dnia targów <strong>([trade_fair_first_day])</strong>.',
            ],

            'en' => [
                'gr1' => 'Download your VIP GOLD badge and enjoy free access to the trade fair area, Fast Track entry, participation in all industry conferences and concierge service.',

                'gr2' => 'Download your VIP GOLD badge and enjoy free access to the trade fair area, participation in all industry conferences, access to the specially prepared VIP ROOM zone and concierge service.',

                'gr3' => 'Download your VIP GOLD badge and enjoy free access to the trade fair area, welcome pack, participation in all industry conferences, access to the specially prepared VIP ROOM zone and concierge service.',

                'b2c' => 'Download your VIP GOLD ID and benefit from free entry to the trade fair, participation in all industry conferences and attractions, Fast Track entry, entry to the specially prepared VIP ROOM zone, and concierge care. The invitation allows free participation on the first day of the trade fair <strong>([trade_fair_first_day])</strong>.',

                'b2c-new' => 'Download your VIP GOLD ID and benefit from free entry to the trade fair, participation in all industry conferences and attractions, Fast Track entry, entry to the specially prepared VIP ROOM zone, and concierge care. The invitation allows free participation on the first day of the trade fair <strong>([trade_fair_first_day])</strong>.',


            ],
        ];

        return $texts[$language][$fair_group] ?? $texts[$language]['gr1'];
    }

    public function show_trade_fair_exhibitor_generator_header_url() {
        $lang = PWE_Functions::lang();

        $lang = ($lang === 'pl') ? 'pl' : 'en';

        $cap_files = PWE_Functions::get_database_fairs_data_files();

        $available = [];

        foreach ($cap_files as $item) {
            if (
                $item->category_slug === 'exhibitor-generator-header' &&
                $item->is_active == '1'
            ) {
                $available[$item->language] = $item;
            }
        }

        $priority = ($lang === 'pl')
            ? ['pl', 'all']
            : ['en', 'pl', 'all'];

        foreach ($priority as $language) {
            if (isset($available[$language])) {
                return 'https://cap.warsawexpo.eu' . $available[$language]->file_path;
            }
        }

       // fallback 1 - local file
        $file_path = ABSPATH . 'doc/vip.jpg';

        if (file_exists($file_path)) {
            return 'https://' . $_SERVER['HTTP_HOST'] . '/doc/vip.jpg';
        }

        // fallback 2 - plugin
        return content_url('plugins/pwe-media/media/vip-pwe.jpg');
    }

    public function show_trade_fair_exhibitor_generator_badge_url() {
        $cap_files = PWE_Functions::get_database_fairs_data_files();

        $available = null;

        foreach ($cap_files as $item) {
            if (
                $item->category_slug === 'exhibitor-generator-badge' &&
                $item->is_active == '1'
            ) {
                $available = $item;
                break;
            }
        }

        if ($available !== null && !empty($available->file_path)) {
            return 'https://cap.warsawexpo.eu' . $available->file_path;
        }

        // fallback
        return '/wp-content/plugins/pwe-media/media/generator-gosci-wystawcow-auto-switch/badgevip.webp';
    }

    // FOR YOAST SEO START <----------------------------------------------------------------------<

    public function sc_pwe_trade_fair_full_desc() {
        $domain = $_SERVER['HTTP_HOST'];
        $shortcodes_active = empty(get_option('pwe_general_options', [])['pwe_dp_shortcodes_unactive']);
        $lang = strtolower(PWE_LANG);

        if (!function_exists('get_translated_field')) {
            function get_translated_field($fair, $field_base_name) {
                // Get the language in the format e.g. "de", "pl"
                $lang = strtolower(PWE_LANG); // "de"

                // Check if a specific translation exists (e.g. fair_name_{lang})
                $field_with_lang = "{$field_base_name}_{$lang}";

                if (!empty($fair[$field_with_lang])) {
                    return $fair[$field_with_lang];
                }

                // Fallback to English
                $fallback = "{$field_base_name}_en";
                return $fair[$fallback] ?? '';
            }
        }

        if (!function_exists('get_pwe_shortcode')) {
            function get_pwe_shortcode($shortcode, $domain) {
                return shortcode_exists($shortcode) ? do_shortcode('[' . $shortcode . ' domain="' . $domain . '"]') : "";
            }
        }

        if (!function_exists('check_available_pwe_shortcode')) {
            function check_available_pwe_shortcode($shortcodes_active, $shortcode) {
                return $shortcodes_active && !empty($shortcode);
            }
        }

        $translates = PWE_Functions::get_database_translations_data($domain);

        $shortcode_full_desc = get_pwe_shortcode("pwe_full_desc_$lang", $domain);
        $shortcode_full_desc_available = check_available_pwe_shortcode($shortcodes_active, $shortcode_full_desc);
        $fair_full_desc = $shortcode_full_desc_available ? get_translated_field($translates[0], 'fair_full_desc') : '';

        $description = '';
        if (!empty($fair_full_desc)) {
            $description = strstr($fair_full_desc, '<br>', true);
            if ($description === false) {
                $description = $fair_full_desc;
            }
        }

        return $description;
    }

    public function sc_pwe_text_news() {
        if (PWE_LANG == "pl") {
            return 'Bądź na bieżąco z wydarzeniami i nowościami związanymi z '. do_shortcode('[trade_fair_name]') .' '. do_shortcode('[trade_fair_catalog_year]') .'.';
        } else {
            return 'Stay up to date with events and news related to '. do_shortcode('[trade_fair_name_eng]') .' '. do_shortcode('[trade_fair_catalog_year]') .'.';
        }
    }

    public function sc_pwe_text_for_visitors() {
        if (PWE_LANG == "pl") {
            return 'Sprawdź, dlaczego warto odwiedzić '. do_shortcode('[trade_fair_name]') .' '. do_shortcode('[trade_fair_catalog_year]') .' – znajdziesz tu najnowsze trendy, innowacje i inspirujące rozwiązania.';
        } else {
            return 'Check out why you should visit '. do_shortcode('[trade_fair_name_eng]') .' '. do_shortcode('[trade_fair_catalog_year]') .' – discover the latest trends, innovations, and inspiring solutions.';
        }
    }

    public function sc_pwe_text_for_exhibitors() {
        if (PWE_LANG == "pl") {
            return 'Zdobądź nowych klientów i pokaż swoją markę na '. do_shortcode('[trade_fair_name]') .' '. do_shortcode('[trade_fair_catalog_year]') .'.';
        } else {
            return 'Gain new customers and showcase your brand at '. do_shortcode('[trade_fair_name_eng]') .' '. do_shortcode('[trade_fair_catalog_year]') .'.';
        }
    }

    public function sc_pwe_text_add_calendar() {
        if (PWE_LANG == "pl") {
            return 'Nie przegap '. do_shortcode('[trade_fair_name]') .' '. do_shortcode('[trade_fair_catalog_year]').'! Dodaj wydarzenie do swojego kalendarza.';
        } else {
            return 'Don\'t miss '. do_shortcode('[trade_fair_name_eng]') .' '. do_shortcode('[trade_fair_catalog_year]').'! Add the event to your calendar.';
        }
    }

    public function sc_pwe_text_gallery() {
        if (PWE_LANG == "pl") {
            return 'Zobacz galerię '. do_shortcode('[trade_fair_name]') .' – sprawdź jak wyglądają targi z perspektywy obiektywu.';
        } else {
            return 'See the gallery of '. do_shortcode('[trade_fair_name_eng]') .' – check out the fair through the lens of the camera.';
        }
    }

    public function sc_pwe_text_org_info() {
        if (PWE_LANG == "pl") {
            return 'Wszystkie niezbędne informacje organizacyjne dla wystawców '. do_shortcode('[trade_fair_name]') .' '. do_shortcode('[trade_fair_catalog_year]') .'.';
        } else {
            return 'All necessary organizational information for exhibitors at '. do_shortcode('[trade_fair_name_eng]') .' '. do_shortcode('[trade_fair_catalog_year]') .'.';
        }
    }

    public function sc_pwe_text_exh_catalog() {
        if (PWE_LANG == "pl") {
            return 'Poznaj firmy i marki obecne na '. do_shortcode('[trade_fair_name]') .' '. do_shortcode('[trade_fair_catalog_year]') .'.';
        } else {
            return 'Get to know the companies and brands present at '. do_shortcode('[trade_fair_name_eng]') .' '. do_shortcode('[trade_fair_catalog_year]') .'.';
        }
    }

    public function sc_pwe_text_events() {
        if (PWE_LANG == "pl") {
            return 'Sprawdź wydarzenia towarzyszące '. do_shortcode('[trade_fair_name]') .' '. do_shortcode('[trade_fair_catalog_year]') .' – konferencje, prelekcje, spotkania.';
        } else {
            return 'Check out the events accompanying '. do_shortcode('[trade_fair_name_eng]') .' '. do_shortcode('[trade_fair_catalog_year]') .' – conferences, lectures, meetings.';
        }
    }

    public function sc_pwe_text_contact() {
        if (PWE_LANG == "pl") {
            return 'Skontaktuj się z organizatorami '. do_shortcode('[trade_fair_name]') .' i uzyskaj potrzebne informacje o wydarzeniu.';
        } else {
            return 'Contact the organizers of '. do_shortcode('[trade_fair_name_eng]') .' to get the information you need about the event.';
        }
    }

    public function sc_pwe_text_fair_plan() {
        if (PWE_LANG == "pl") {
            return 'Zobacz plan stoisk i atrakcji '. do_shortcode('[trade_fair_name]') .' '. do_shortcode('[trade_fair_catalog_year]') .'.';
        } else {
            return 'See the booth and attraction plan for '. do_shortcode('[trade_fair_name_eng]') .' '. do_shortcode('[trade_fair_catalog_year]') .'.';
        }
    }

    public function sc_pwe_text_registration() {
        if (PWE_LANG == "pl") {
            return 'Zarejestruj się na '. do_shortcode('[trade_fair_name]') .' '. do_shortcode('[trade_fair_catalog_year]') .' i odbierz swój bilet na targi.';
        } else {
            return 'Register for '. do_shortcode('[trade_fair_name_eng]') .' '. do_shortcode('[trade_fair_catalog_year]') .' and get your ticket to the fair.';
        }
    }

    public function sc_pwe_text_promote_yourself() {
        if (PWE_LANG == "pl") {
            return 'Zwiększ rozpoznawalność swojej marki – wypromuj się na '. do_shortcode('[trade_fair_name]') .' '. do_shortcode('[trade_fair_catalog_year]') .'.';
        } else {
            return 'Increase your brand visibility – promote yourself at '. do_shortcode('[trade_fair_name_eng]') .' '. do_shortcode('[trade_fair_catalog_year]') .'.';
        }
    }

    public function sc_pwe_text_become_an_exhibitor() {
        if (PWE_LANG == "pl") {
            return 'Dołącz do grona wystawców '. do_shortcode('[trade_fair_name]') .' '. do_shortcode('[trade_fair_catalog_year]') .' i zaprezentuj swoją ofertę.';
        } else {
            return 'Join the exhibitors at '. do_shortcode('[trade_fair_name_eng]') .' '. do_shortcode('[trade_fair_catalog_year]') .' and present your offer.';
        }
    }

    public function sc_pwe_text_store() {
        if (PWE_LANG == "pl") {
            return 'Zamów bilety lub pakiety promocyjne związane z '. do_shortcode('[trade_fair_name]') .' '. do_shortcode('[trade_fair_catalog_year]') .' w naszym sklepie online.';
        } else {
            return 'Order tickets or promotional packages related to '. do_shortcode('[trade_fair_name_eng]') .' '. do_shortcode('[trade_fair_catalog_year]') .' in our online store.';
        }
    }

    public function wpseo_register_extra_replacements() {
        $shortcode_map = $this->get_yoast_shortcodes_map();
        $keys = array_map(function($key) {
            return '%%' . $key . '%%';
        }, array_keys($shortcode_map));
        return $keys;
    }

    public function wpseo_replacements($replacements) {
        $shortcode_map = $this->get_yoast_shortcodes_map();

        foreach ($shortcode_map as $yoast_key => $callback) {
            $value = '';

            // Wywołaj metodę klasy
            if (is_callable([$this, $callback])) {
                $value = call_user_func([$this, $callback]);
            }

            $replacements['%%' . $yoast_key . '%%'] = $value;
        }

        return $replacements;
    }

    // FOR YOAST SEO END <----------------------------------------------------------------------<

    public function replace_multilang_date_in_notification(
        $notification,
        $form,
        $entry
    ) {
        $notification_name = isset($notification['name'])
            ? (string) $notification['name']
            : '';

        $notification_lang =
            $this->get_language_from_notification_name(
                $notification_name
            );

        $fields = [
            'subject',
            'message',
            'fromName',
            'from',
            'replyTo',
            'to',
            'cc',
            'bcc',
        ];

        foreach ($fields as $field) {
            if (
                !isset($notification[$field]) ||
                !is_string($notification[$field])
            ) {
                continue;
            }

            // Replace {pwe_mailing_header_platyna_url} and [pwe_mailing_header_platyna_url] with the actual URL
            $notification[$field] = str_replace(
                [
                    '{pwe_mailing_header_platyna_url}',
                    '[pwe_mailing_header_platyna_url]',
                ],
                $this->show_pwe_mailing_header_platyna_url($notification_lang),
                $notification[$field]
            );

            $notification[$field] = preg_replace_callback(
                '/[\{\[]trade_fair_date_multilang(?:\s+lang=["\']([^"\']+)["\'])?[\}\]]/i',
                function ($matches) use ($notification_lang) {

                    $requested_lang = isset($matches[1])
                        ? trim((string) $matches[1])
                        : '';

                    /*
                    * Three modes:
                    *
                    * no lang:
                    * automatic detection by shortcode
                    *
                    * lang="cs":
                    * fixed language
                    *
                    * lang="{{lang}}":
                    * language from notification name
                    */
                    if ($requested_lang === '{{lang}}') {
                        $requested_lang = $notification_lang;
                    }

                    return $this->show_trade_fair_date_multilang([
                        'lang' => $requested_lang,
                    ]);
                },
                $notification[$field]
            );
        }

        return $notification;
    }

    private function get_language_from_notification_name(
        $notification_name
    ) {
        $supported_languages = [
            'pl',
            'en',
            'it',
            'cs',
            'de',
            'lv',
            'lt',
            'sk',
            'uk',
            'et',
            'ro',
            'ru',
            'hu',
            'es',
            'fr',
        ];

        $notification_name = trim(
            strtolower((string) $notification_name)
        );

        /*
        * Examples:
        *
        * Registration - CS
        * Admin Notification - UK
        * Resend - LT
        */
        if (
            preg_match(
                '/-\s*([a-z]{2})\s*$/i',
                $notification_name,
                $matches
            )
        ) {
            $lang = strtolower($matches[1]);

            if (in_array($lang, $supported_languages, true)) {
                return $lang;
            }
        }

        if (
            class_exists('PWE_Functions') &&
            is_callable(['PWE_Functions', 'lang'])
        ) {
            $lang = PWE_Functions::lang();
        } elseif (defined('PWE_LANG') && PWE_LANG) {
            $lang = PWE_LANG;
        } else {
            $lang = determine_locale();
        }

        $lang = strtolower(trim((string) $lang));
        $lang = str_replace('_', '-', $lang);
        $lang = explode('-', $lang)[0];
        $lang = sanitize_key($lang);

        return in_array($lang, $supported_languages, true)
            ? $lang
            : 'en';
    }

    public function replace_gf_merge_tags($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
        $map = $this->get_gf_shortcodes_map();

        foreach ($map as $tag => $function_name) {
            if ($tag === 'trade_fair_date_multilang') {
                continue;
            }

            if (!is_callable([$this, $function_name])) {
                continue;
            }

            $value = call_user_func([$this, $function_name]);

            $text = str_replace(
                ['{' . $tag . '}', '[' . $tag . ']'],
                $value,
                $text
            );
        }

        foreach ($this->get_gf_url_shortcodes_map() as $shortcode_tag) {

            $value = $this->show_multilang_url(
                [],
                null,
                $shortcode_tag
            );

            $text = str_replace(
                '{' . $shortcode_tag . '}',
                $value,
                $text
            );
        }

        return $text;
    }


    /**
     * Reads and decodes a single JSON file.
     *
     * @param string $json_file Full path to the file.
     *
     * @return array|null Parsed data array or null on failure.
     */
    private function read_urls_json_file($json_file) {

        if (
            empty($json_file) ||
            !is_string($json_file) ||
            !is_file($json_file) ||
            !is_readable($json_file)
        ) {
            return null;
        }

        $json = file_get_contents($json_file);

        if ($json === false || trim($json) === '') {
            return null;
        }

        $data = json_decode($json, true);

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !is_array($data)
        ) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(
                    'PWE Shortcodes: invalid JSON file: '
                    . $json_file
                    . ' | '
                    . json_last_error_msg()
                );
            }

            return null;
        }

        return $data;
    }

    /**
     * Retrieves URL data.
     *
     * Priorytet:
     * 1. /wp-content/plugins/pwe-multilang/website-translation.json
     * 2. /pwe-system/data/website-translation.json jako fallback
     *
     * @return array
     */
    private function get_urls_data() {

        if ($this->urls_data !== null) {
            return $this->urls_data;
        }

        foreach (PWE_System_Functions::get_website_translation_files() as $json_file) {
            $data = $this->read_urls_json_file($json_file);

            if (is_array($data)) {
                $this->urls_data = $data;

                return $this->urls_data;
            }
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(
                'PWE Shortcodes: unable to load any '
                . 'website-translation.json file.'
            );
        }

        $this->urls_data = [];

        return $this->urls_data;
    }

    /**
     * Returns the current language for URL shortcodes.
     *
     * Priority:
     * 1. The lang attribute passed to the shortcode.
     * 2. PWE_Functions::lang().
     * 3. The PWE_LANG constant.
     * 4. The current WordPress locale.
     * 5. English.
     *
     * @param string $requested_lang Language passed to the shortcode.
     *
     * @return string
     */
    private function get_url_shortcode_language($requested_lang = '') {

        if (!empty($requested_lang)) {
            $lang = $requested_lang;
        } elseif (
            class_exists('PWE_Functions') &&
            is_callable(['PWE_Functions', 'lang'])
        ) {
            $lang = PWE_Functions::lang();
        } elseif (defined('PWE_LANG') && PWE_LANG) {
            $lang = PWE_LANG;
        } else {
            $lang = determine_locale();
        }

        $lang = strtolower(trim((string) $lang));

        /*
        * Convert locale codes such as cs_CZ or en-US
        * to simple language codes such as cs or en.
        */
        $lang = str_replace('_', '-', $lang);
        $lang = explode('-', $lang)[0];

        $lang = sanitize_key($lang);

        return $lang !== '' ? $lang : 'en';
    }

    /**
     * Retrieves language-specific data for a given URL entry.
     *
     * Fallback order:
     * 1. Current language.
     * 2. English.
     * 3. Polish.
     * 4. First available language.
     *
     * @param array  $url_entry Data for a single URL entry.
     * @param string $lang      Language code.
     *
     * @return array|null
     */
    private function get_url_language_data(array $url_entry, $lang) {

        if (
            isset($url_entry[$lang]) &&
            is_array($url_entry[$lang])
        ) {
            return $url_entry[$lang];
        }

        if (
            isset($url_entry['en']) &&
            is_array($url_entry['en'])
        ) {
            return $url_entry['en'];
        }

        if (
            isset($url_entry['pl']) &&
            is_array($url_entry['pl'])
        ) {
            return $url_entry['pl'];
        }

        foreach ($url_entry as $language_data) {
            if (is_array($language_data)) {
                return $language_data;
            }
        }

        return null;
    }

    /**
     * Handles all dynamic URL shortcodes.
     *
     * Examples:
     *
     * [url_home]
     * [url_dla_odwiedzajacych]
     * [url_dla_odwiedzajacych lang="de"]
     * [url_dla_odwiedzajacych absolute="1"]
     *
     * @param array       $atts          Shortcode attributes.
     * @param string|null $content       Shortcode content.
     * @param string      $shortcode_tag Name of the executed shortcode.
     *
     * @return string
     */
    public function show_multilang_url(
        $atts = [],
        $content = null,
        $shortcode_tag = ''
    ) {

        $atts = shortcode_atts(
            [
                'lang'     => '',
                'absolute' => '0',
            ],
            $atts,
            $shortcode_tag
        );

        /*
        * Extract the URL key from the shortcode name.
        *
        * Example:
        * url_dla_odwiedzajacych becomes dla_odwiedzajacych.
        */
        $url_key = preg_replace(
            '/^url_/',
            '',
            (string) $shortcode_tag
        );

        $url_key = sanitize_key($url_key);

        if ($url_key === '') {
            return '';
        }

        $urls_data = $this->get_urls_data();

        if (
            !isset($urls_data[$url_key]) ||
            !is_array($urls_data[$url_key])
        ) {
            return '';
        }

        $lang = $this->get_url_shortcode_language(
            $atts['lang']
        );

        $language_data = $this->get_url_language_data(
            $urls_data[$url_key],
            $lang
        );

        if (
            !is_array($language_data) ||
            !isset($language_data['url'])
        ) {
            return '';
        }

        $url = trim((string) $language_data['url']);

        if ($url === '') {
            return '';
        }

        /*
        * Add the language prefix to relative URLs.
        *
        * Examples:
        * /                     becomes /uk/
        * /dlya-vidviduvachiv/  becomes /uk/dlya-vidviduvachiv/
        * /fur-besucher/         becomes /de/fur-besucher/
        *
        * Polish URLs do not receive the /pl/ prefix.
        */
        if (!preg_match('#^https?://#i', $url)) {

            $url_path = '/' . ltrim($url, '/');

            /*
            * Do not add a language prefix for Polish.
            */
            if ($lang !== 'pl') {

                $language_prefix = '/' . $lang . '/';

                /*
                * Prevent duplicate language prefixes.
                */
                if (
                    $url_path !== '/' . $lang &&
                    strpos($url_path, $language_prefix) !== 0
                ) {
                    if ($url_path === '/') {
                        $url_path = $language_prefix;
                    } else {
                        $url_path = $language_prefix . ltrim(
                            $url_path,
                            '/'
                        );
                    }
                }
            }

            $url = $url_path;
        }

        /*
        * Optionally convert the relative path into an absolute URL.
        */
        $absolute = filter_var(
            $atts['absolute'],
            FILTER_VALIDATE_BOOLEAN
        );

        if (
            $absolute &&
            !preg_match('#^https?://#i', $url)
        ) {
            $url = home_url($url);
        }

        return esc_url($url);
    }

    /**
     * Registers dynamic URL shortcodes based on JSON keys.
     */
    private function register_url_shortcodes() {

        $urls_data = $this->get_urls_data();

        if (empty($urls_data)) {
            return;
        }

        foreach (array_keys($urls_data) as $url_key) {

            $url_key = sanitize_key($url_key);

            if ($url_key === '') {
                continue;
            }

            $shortcode_tag = 'url_' . $url_key;

            if (shortcode_exists($shortcode_tag)) {
                remove_shortcode($shortcode_tag);
            }

            add_shortcode(
                $shortcode_tag,
                [$this, 'show_multilang_url']
            );
        }
    }

    private function get_gf_url_shortcodes_map() {

        $urls_data = $this->get_urls_data();
        $shortcodes = [];

        foreach (array_keys($urls_data) as $url_key) {
            $url_key = sanitize_key($url_key);

            if ($url_key === '') {
                continue;
            }

            $shortcodes[] = 'url_' . $url_key;
        }

        return $shortcodes;
    }

    /**
     * Returns the absolute mailing header URL.
     *
     * Polish pages use:
     * /doc/header.jpg
     *
     * Other language versions use:
     * /doc/header_en.jpg
     *
     * If the English header does not exist or an error occurs,
     * the Polish header is returned as a fallback.
     *
     * @return string
     */
    public function show_pwe_mailing_header_url() {
        $fallback_url = get_site_url(null, '/doc/header.jpg');

        try {
            $lang = 'pl';

            // Use the main project language helper when available
            if (
                class_exists('PWE_Functions') &&
                is_callable(['PWE_Functions', 'lang'])
            ) {
                $lang = PWE_Functions::lang();
            } elseif (defined('PWE_LANG') && PWE_LANG) {
                $lang = PWE_LANG;
            } elseif (function_exists('determine_locale')) {
                $lang = determine_locale();
            }

            // Normalize values such as pl_PL, en_US or de-DE
            $lang = strtolower(trim((string) $lang));
            $lang = str_replace('_', '-', $lang);
            $lang = explode('-', $lang)[0];

            // Always use the Polish header on Polish pages
            if ($lang === 'pl') {
                return $fallback_url;
            }

            // Return the fallback URL if the WordPress path is unavailable
            if (!defined('ABSPATH')) {
                return $fallback_url;
            }

            $english_header_path = trailingslashit(ABSPATH) . 'doc/header_en.jpg';

            // Use the English header only when the file exists
            if (is_file($english_header_path)) {
                return get_site_url(null, '/doc/header_en.jpg');
            }

            return $fallback_url;
        } catch (Throwable $e) {
            return $fallback_url;
        }
    }

    /**
     * Returns the absolute platinum mailing header URL.
     *
     * Polish pages use:
     * /doc/mailing-platyna.jpg
     *
     * Other language versions use:
     * /doc/mailing-platyna-en.jpg
     *
     * Fallback for Polish pages:
     * /doc/header.jpg
     *
     * Fallback for non-Polish pages:
     * /doc/header_en.jpg
     *
     * If the English fallback does not exist,
     * /doc/header.jpg is returned.
     *
     * @param string $requested_lang Optional language code passed from Gravity Forms.
     *
     * @return string
     */
    public function show_pwe_mailing_header_platyna_url($requested_lang = '') {
        $default_header_url = get_site_url(null, '/doc/header.jpg');

        try {
            if (!defined('ABSPATH')) {
                return $default_header_url;
            }

            $lang = 'pl';

            // Use the explicitly requested language when available
            if (!empty($requested_lang)) {
                $lang = $requested_lang;
            }
            // Use the main project language helper when available
            elseif (
                class_exists('PWE_Functions') &&
                is_callable(['PWE_Functions', 'lang'])
            ) {
                $lang = PWE_Functions::lang();
            } elseif (defined('PWE_LANG') && PWE_LANG) {
                $lang = PWE_LANG;
            } elseif (function_exists('determine_locale')) {
                $lang = determine_locale();
            }

            // Normalize values such as pl_PL, en_US or de-DE
            $lang = strtolower(trim((string) $lang));
            $lang = str_replace('_', '-', $lang);
            $lang = explode('-', $lang)[0];

            $document_directory = trailingslashit(ABSPATH) . 'doc/';

            // Polish language version
            if ($lang === 'pl') {
                $platinum_header_path =
                    $document_directory . 'mailing-platyna.jpg';

                if (is_file($platinum_header_path)) {
                    return get_site_url(
                        null,
                        '/doc/mailing-platyna.jpg'
                    );
                }

                return $default_header_url;
            }

            // Non-Polish language version
            $platinum_english_header_path =
                $document_directory . 'mailing-platyna-en.jpg';

            if (is_file($platinum_english_header_path)) {
                return get_site_url(
                    null,
                    '/doc/mailing-platyna-en.jpg'
                );
            }

            // Use the English standard header as the first fallback
            $english_header_path =
                $document_directory . 'header_en.jpg';

            if (is_file($english_header_path)) {
                return get_site_url(
                    null,
                    '/doc/header_en.jpg'
                );
            }

            // Use the Polish standard header as the final fallback
            return $default_header_url;
        } catch (Throwable $e) {
            return $default_header_url;
        }
    }

}

PWE_Shortcodes::init();