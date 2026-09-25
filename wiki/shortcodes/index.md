---
plugin: PWE System
version: 1.0.2
source: uploaded archive
source_commit: null
language: pl
---
# Shortcody

Deterministycznie udokumentowane tagi: **155**. `shortcodes.json` zachowuje format kompatybilny z bieżącym indexerem. Wzorce tworzone w runtime znajdują się w `inventory/shortcode-families.json`.

| Shortcode | Typ | Callback / dispatcher | Źródło |
|---|---|---|---|
| [`[pwe_about_desc_en]`](pwe_about_desc_en.md) | `dynamic-map` | `handle_fair_shortcode($atts, "about_desc_en")` | `modules/shortcodes/backend-shortcodes.php:198` |
| [`[pwe_about_desc_pl]`](pwe_about_desc_pl.md) | `dynamic-map` | `handle_fair_shortcode($atts, "about_desc_pl")` | `modules/shortcodes/backend-shortcodes.php:197` |
| [`[pwe_about_title_en]`](pwe_about_title_en.md) | `dynamic-map` | `handle_fair_shortcode($atts, "about_title_en")` | `modules/shortcodes/backend-shortcodes.php:196` |
| [`[pwe_about_title_pl]`](pwe_about_title_pl.md) | `dynamic-map` | `handle_fair_shortcode($atts, "about_title_pl")` | `modules/shortcodes/backend-shortcodes.php:195` |
| [`[pwe_area]`](pwe_area.md) | `dynamic-map` | `handle_fair_shortcode($atts, "fair_area_current")` | `modules/shortcodes/backend-shortcodes.php:167` |
| [`[pwe_area_prev]`](pwe_area_prev.md) | `dynamic-map` | `handle_fair_shortcode($atts, "fair_area_previous")` | `modules/shortcodes/backend-shortcodes.php:173` |
| [`[pwe_badge]`](pwe_badge.md) | `dynamic-map` | `handle_fair_shortcode($atts, "badge")` | `modules/shortcodes/backend-shortcodes.php:179` |
| [`[pwe_catalog]`](pwe_catalog.md) | `dynamic-map` | `handle_fair_shortcode($atts, "catalog")` | `modules/shortcodes/backend-shortcodes.php:184` |
| [`[pwe_catalog_id]`](pwe_catalog_id.md) | `dynamic-map` | `handle_fair_shortcode($atts, "catalog_id")` | `modules/shortcodes/backend-shortcodes.php:185` |
| [`[pwe_category_en]`](pwe_category_en.md) | `dynamic-map` | `handle_fair_shortcode($atts, "category_en")` | `modules/shortcodes/backend-shortcodes.php:187` |
| [`[pwe_category_pl]`](pwe_category_pl.md) | `dynamic-map` | `handle_fair_shortcode($atts, "category_pl")` | `modules/shortcodes/backend-shortcodes.php:186` |
| [`[pwe_color_accent]`](pwe_color_accent.md) | `dynamic-map` | `handle_fair_shortcode($atts, "color_accent")` | `modules/shortcodes/backend-shortcodes.php:177` |
| [`[pwe_color_main2]`](pwe_color_main2.md) | `dynamic-map` | `handle_fair_shortcode($atts, "color_main2")` | `modules/shortcodes/backend-shortcodes.php:178` |
| [`[pwe_conference_desc_en]`](pwe_conference_desc_en.md) | `dynamic-map` | `handle_fair_shortcode($atts, "conference_desc_en")` | `modules/shortcodes/backend-shortcodes.php:194` |
| [`[pwe_conference_desc_pl]`](pwe_conference_desc_pl.md) | `dynamic-map` | `handle_fair_shortcode($atts, "conference_desc_pl")` | `modules/shortcodes/backend-shortcodes.php:193` |
| [`[pwe_conference_name]`](pwe_conference_name.md) | `dynamic-map` | `handle_fair_shortcode($atts, "conference_name")` | `modules/shortcodes/backend-shortcodes.php:190` |
| [`[pwe_conference_title_en]`](pwe_conference_title_en.md) | `dynamic-map` | `handle_fair_shortcode($atts, "conference_title_en")` | `modules/shortcodes/backend-shortcodes.php:192` |
| [`[pwe_conference_title_pl]`](pwe_conference_title_pl.md) | `dynamic-map` | `handle_fair_shortcode($atts, "conference_title_pl")` | `modules/shortcodes/backend-shortcodes.php:191` |
| [`[pwe_countries]`](pwe_countries.md) | `dynamic-map` | `handle_fair_shortcode($atts, "fair_countries_current")` | `modules/shortcodes/backend-shortcodes.php:166` |
| [`[pwe_countries_prev]`](pwe_countries_prev.md) | `dynamic-map` | `handle_fair_shortcode($atts, "fair_countries_previous")` | `modules/shortcodes/backend-shortcodes.php:172` |
| [`[pwe_date_end]`](pwe_date_end.md) | `dynamic-map` | `handle_fair_shortcode($atts, "date_end")` | `modules/shortcodes/backend-shortcodes.php:160` |
| [`[pwe_date_end_hour]`](pwe_date_end_hour.md) | `dynamic-map` | `handle_fair_shortcode($atts, "date_end_hour")` | `modules/shortcodes/backend-shortcodes.php:161` |
| [`[pwe_date_start]`](pwe_date_start.md) | `dynamic-map` | `handle_fair_shortcode($atts, "date_start")` | `modules/shortcodes/backend-shortcodes.php:158` |
| [`[pwe_date_start_hour]`](pwe_date_start_hour.md) | `dynamic-map` | `handle_fair_shortcode($atts, "date_start_hour")` | `modules/shortcodes/backend-shortcodes.php:159` |
| [`[pwe_desc_en]`](pwe_desc_en.md) | `dynamic-map` | `handle_fair_shortcode($atts, "desc_en")` | `modules/shortcodes/backend-shortcodes.php:152` |
| [`[pwe_desc_pl]`](pwe_desc_pl.md) | `dynamic-map` | `handle_fair_shortcode($atts, "desc_pl")` | `modules/shortcodes/backend-shortcodes.php:151` |
| [`[pwe_edition]`](pwe_edition.md) | `dynamic-map` | `handle_fair_shortcode($atts, "edition")` | `modules/shortcodes/backend-shortcodes.php:162` |
| [`[pwe_exhibitors]`](pwe_exhibitors.md) | `dynamic-map` | `handle_fair_shortcode($atts, "fair_exhibitors_current")` | `modules/shortcodes/backend-shortcodes.php:165` |
| [`[pwe_exhibitors_prev]`](pwe_exhibitors_prev.md) | `dynamic-map` | `handle_fair_shortcode($atts, "fair_exhibitors_previous")` | `modules/shortcodes/backend-shortcodes.php:171` |
| [`[pwe_facebook]`](pwe_facebook.md) | `dynamic-map` | `handle_fair_shortcode($atts, "facebook")` | `modules/shortcodes/backend-shortcodes.php:180` |
| [`[pwe_fair_id]`](pwe_fair_id.md) | `dynamic-map` | `handle_fair_shortcode($atts, "id")` | `modules/shortcodes/backend-shortcodes.php:153` |
| [`[pwe_full_desc_en]`](pwe_full_desc_en.md) | `dynamic-map` | `handle_fair_shortcode($atts, "full_desc_en")` | `modules/shortcodes/backend-shortcodes.php:157` |
| [`[pwe_full_desc_pl]`](pwe_full_desc_pl.md) | `dynamic-map` | `handle_fair_shortcode($atts, "full_desc_pl")` | `modules/shortcodes/backend-shortcodes.php:156` |
| [`[pwe_group]`](pwe_group.md) | `dynamic-map` | `handle_fair_shortcode($atts, "group")` | `modules/shortcodes/backend-shortcodes.php:189` |
| [`[pwe_hall]`](pwe_hall.md) | `dynamic-map` | `handle_fair_shortcode($atts, "hall")` | `modules/shortcodes/backend-shortcodes.php:175` |
| [`[pwe_hall_entrance]`](pwe_hall_entrance.md) | `dynamic-map` | `handle_fair_shortcode($atts, "hall_entrance")` | `modules/shortcodes/backend-shortcodes.php:176` |
| [`[pwe_industry]`](pwe_industry.md) | `dynamic-map` | `handle_fair_shortcode($atts, "industry")` | `modules/shortcodes/backend-shortcodes.php:188` |
| [`[pwe_instagram]`](pwe_instagram.md) | `dynamic-map` | `handle_fair_shortcode($atts, "instagram")` | `modules/shortcodes/backend-shortcodes.php:181` |
| [`[pwe_lang_domain]`](pwe_lang_domain.md) | `class-map` | `PWE_Shortcodes::get_lang_domain` | `modules/shortcodes/class-shortcodes.php:89` |
| [`[pwe_linkedin]`](pwe_linkedin.md) | `dynamic-map` | `handle_fair_shortcode($atts, "linkedin")` | `modules/shortcodes/backend-shortcodes.php:182` |
| [`[pwe_mailing_header_platyna_url]`](pwe_mailing_header_platyna_url.md) | `class-map` | `PWE_Shortcodes::show_pwe_mailing_header_platyna_url` | `modules/shortcodes/class-shortcodes.php:145` |
| [`[pwe_mailing_header_url]`](pwe_mailing_header_url.md) | `class-map` | `PWE_Shortcodes::show_pwe_mailing_header_url` | `modules/shortcodes/class-shortcodes.php:144` |
| [`[pwe_name_en]`](pwe_name_en.md) | `dynamic-map` | `handle_fair_shortcode($atts, "name_en")` | `modules/shortcodes/backend-shortcodes.php:150` |
| [`[pwe_name_pl]`](pwe_name_pl.md) | `dynamic-map` | `handle_fair_shortcode($atts, "name_pl")` | `modules/shortcodes/backend-shortcodes.php:149` |
| [`[pwe_short_desc_en]`](pwe_short_desc_en.md) | `dynamic-map` | `handle_fair_shortcode($atts, "short_desc_en")` | `modules/shortcodes/backend-shortcodes.php:155` |
| [`[pwe_short_desc_pl]`](pwe_short_desc_pl.md) | `dynamic-map` | `handle_fair_shortcode($atts, "short_desc_pl")` | `modules/shortcodes/backend-shortcodes.php:154` |
| [`[pwe_statistics_year_curr]`](pwe_statistics_year_curr.md) | `dynamic-map` | `handle_fair_shortcode($atts, "fair_year_current")` | `modules/shortcodes/backend-shortcodes.php:168` |
| [`[pwe_statistics_year_prev]`](pwe_statistics_year_prev.md) | `dynamic-map` | `handle_fair_shortcode($atts, "fair_year_previous")` | `modules/shortcodes/backend-shortcodes.php:174` |
| [`[pwe_visitors]`](pwe_visitors.md) | `dynamic-map` | `handle_fair_shortcode($atts, "fair_visitors_current")` | `modules/shortcodes/backend-shortcodes.php:163` |
| [`[pwe_visitors_foreign]`](pwe_visitors_foreign.md) | `dynamic-map` | `handle_fair_shortcode($atts, "fair_foreign_current")` | `modules/shortcodes/backend-shortcodes.php:164` |
| [`[pwe_visitors_foreign_prev]`](pwe_visitors_foreign_prev.md) | `dynamic-map` | `handle_fair_shortcode($atts, "fair_foreign_previous")` | `modules/shortcodes/backend-shortcodes.php:170` |
| [`[pwe_visitors_prev]`](pwe_visitors_prev.md) | `dynamic-map` | `handle_fair_shortcode($atts, "fair_visitors_previous")` | `modules/shortcodes/backend-shortcodes.php:169` |
| [`[pwe_youtube]`](pwe_youtube.md) | `dynamic-map` | `handle_fair_shortcode($atts, "youtube")` | `modules/shortcodes/backend-shortcodes.php:183` |
| [`[sc_pwe_text_add_calendar]`](sc_pwe_text_add_calendar.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_add_calendar` | `modules/shortcodes/class-shortcodes.php:131` |
| [`[sc_pwe_text_become_an_exhibitor]`](sc_pwe_text_become_an_exhibitor.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_become_an_exhibitor` | `modules/shortcodes/class-shortcodes.php:140` |
| [`[sc_pwe_text_contact]`](sc_pwe_text_contact.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_contact` | `modules/shortcodes/class-shortcodes.php:136` |
| [`[sc_pwe_text_events]`](sc_pwe_text_events.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_events` | `modules/shortcodes/class-shortcodes.php:135` |
| [`[sc_pwe_text_exh_catalog]`](sc_pwe_text_exh_catalog.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_exh_catalog` | `modules/shortcodes/class-shortcodes.php:134` |
| [`[sc_pwe_text_fair_plan]`](sc_pwe_text_fair_plan.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_fair_plan` | `modules/shortcodes/class-shortcodes.php:137` |
| [`[sc_pwe_text_for_exhibitors]`](sc_pwe_text_for_exhibitors.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_for_exhibitors` | `modules/shortcodes/class-shortcodes.php:130` |
| [`[sc_pwe_text_for_visitors]`](sc_pwe_text_for_visitors.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_for_visitors` | `modules/shortcodes/class-shortcodes.php:129` |
| [`[sc_pwe_text_gallery]`](sc_pwe_text_gallery.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_gallery` | `modules/shortcodes/class-shortcodes.php:132` |
| [`[sc_pwe_text_news]`](sc_pwe_text_news.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_news` | `modules/shortcodes/class-shortcodes.php:128` |
| [`[sc_pwe_text_org_info]`](sc_pwe_text_org_info.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_org_info` | `modules/shortcodes/class-shortcodes.php:133` |
| [`[sc_pwe_text_promote_yourself]`](sc_pwe_text_promote_yourself.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_promote_yourself` | `modules/shortcodes/class-shortcodes.php:139` |
| [`[sc_pwe_text_registration]`](sc_pwe_text_registration.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_registration` | `modules/shortcodes/class-shortcodes.php:138` |
| [`[sc_pwe_text_store]`](sc_pwe_text_store.md) | `class-map` | `PWE_Shortcodes::sc_pwe_text_store` | `modules/shortcodes/class-shortcodes.php:141` |
| [`[sc_pwe_trade_fair_conference_title]`](sc_pwe_trade_fair_conference_title.md) | `class-map` | `show_trade_fair_conference_title (pl) / show_trade_fair_conference_title_eng (inne)` | `modules/shortcodes/class-shortcodes.php:127` |
| [`[trade_fair_1stbuildday]`](trade_fair_1stbuildday.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_1stbuildday` | `modules/shortcodes/class-shortcodes.php:67` |
| [`[trade_fair_1stdismantlday]`](trade_fair_1stdismantlday.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_1stdismantlday` | `modules/shortcodes/class-shortcodes.php:69` |
| [`[trade_fair_2ndbuildday]`](trade_fair_2ndbuildday.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_2ndbuildday` | `modules/shortcodes/class-shortcodes.php:68` |
| [`[trade_fair_2nddismantlday]`](trade_fair_2nddismantlday.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_2nddismantlday` | `modules/shortcodes/class-shortcodes.php:70` |
| [`[trade_fair_accent]`](trade_fair_accent.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_accent` | `modules/shortcodes/class-shortcodes.php:77` |
| [`[trade_fair_actualyear]`](trade_fair_actualyear.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_actualyear` | `modules/shortcodes/class-shortcodes.php:91` |
| [`[trade_fair_badge]`](trade_fair_badge.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_badge` | `modules/shortcodes/class-shortcodes.php:81` |
| [`[trade_fair_branzowy]`](trade_fair_branzowy.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_branzowy` | `modules/shortcodes/class-shortcodes.php:79` |
| [`[trade_fair_branzowy_eng]`](trade_fair_branzowy_eng.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_branzowy_eng` | `modules/shortcodes/class-shortcodes.php:80` |
| [`[trade_fair_catalog]`](trade_fair_catalog.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_catalog` | `modules/shortcodes/class-shortcodes.php:54` |
| [`[trade_fair_catalog_archive]`](trade_fair_catalog_archive.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_catalog_archive` | `modules/shortcodes/class-shortcodes.php:56` |
| [`[trade_fair_catalog_id]`](trade_fair_catalog_id.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_catalog_id` | `modules/shortcodes/class-shortcodes.php:55` |
| [`[trade_fair_catalog_id_archive]`](trade_fair_catalog_id_archive.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_catalog_id_archive` | `modules/shortcodes/class-shortcodes.php:57` |
| [`[trade_fair_catalog_year]`](trade_fair_catalog_year.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_catalog_year` | `modules/shortcodes/class-shortcodes.php:58` |
| [`[trade_fair_conferance]`](trade_fair_conferance.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_conference_title` | `modules/shortcodes/class-shortcodes.php:64` |
| [`[trade_fair_conferance_eng]`](trade_fair_conferance_eng.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_conference_title_eng` | `modules/shortcodes/class-shortcodes.php:65` |
| [`[trade_fair_conference]`](trade_fair_conference.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_conference` | `modules/shortcodes/class-shortcodes.php:60` |
| [`[trade_fair_conference_title]`](trade_fair_conference_title.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_conference_title` | `modules/shortcodes/class-shortcodes.php:61` |
| [`[trade_fair_conference_title_eng]`](trade_fair_conference_title_eng.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_conference_title_eng` | `modules/shortcodes/class-shortcodes.php:62` |
| [`[trade_fair_contact]`](trade_fair_contact.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact` | `modules/shortcodes/class-shortcodes.php:93` |
| [`[trade_fair_contact_email_vip]`](trade_fair_contact_email_vip.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_email_vip` | `modules/shortcodes/class-shortcodes.php:110` |
| [`[trade_fair_contact_medal_ceremony_email]`](trade_fair_contact_medal_ceremony_email.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_medal_ceremony_email` | `modules/shortcodes/class-shortcodes.php:113` |
| [`[trade_fair_contact_media]`](trade_fair_contact_media.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_media` | `modules/shortcodes/class-shortcodes.php:98` |
| [`[trade_fair_contact_media_name]`](trade_fair_contact_media_name.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_media_name` | `modules/shortcodes/class-shortcodes.php:100` |
| [`[trade_fair_contact_media_person_email]`](trade_fair_contact_media_person_email.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_media_person_email` | `modules/shortcodes/class-shortcodes.php:103` |
| [`[trade_fair_contact_media_person_email_2]`](trade_fair_contact_media_person_email_2.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_media_person_email_2` | `modules/shortcodes/class-shortcodes.php:106` |
| [`[trade_fair_contact_media_person_email_3]`](trade_fair_contact_media_person_email_3.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_media_person_email_3` | `modules/shortcodes/class-shortcodes.php:109` |
| [`[trade_fair_contact_media_person_name]`](trade_fair_contact_media_person_name.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_media_person_name` | `modules/shortcodes/class-shortcodes.php:101` |
| [`[trade_fair_contact_media_person_name_2]`](trade_fair_contact_media_person_name_2.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_media_person_name_2` | `modules/shortcodes/class-shortcodes.php:104` |
| [`[trade_fair_contact_media_person_name_3]`](trade_fair_contact_media_person_name_3.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_media_person_name_3` | `modules/shortcodes/class-shortcodes.php:107` |
| [`[trade_fair_contact_media_person_phone]`](trade_fair_contact_media_person_phone.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_media_person_phone` | `modules/shortcodes/class-shortcodes.php:102` |
| [`[trade_fair_contact_media_person_phone_2]`](trade_fair_contact_media_person_phone_2.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_media_person_phone_2` | `modules/shortcodes/class-shortcodes.php:105` |
| [`[trade_fair_contact_media_person_phone_3]`](trade_fair_contact_media_person_phone_3.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_media_person_phone_3` | `modules/shortcodes/class-shortcodes.php:108` |
| [`[trade_fair_contact_media_phone]`](trade_fair_contact_media_phone.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_media_phone` | `modules/shortcodes/class-shortcodes.php:99` |
| [`[trade_fair_contact_phone_vip]`](trade_fair_contact_phone_vip.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_phone_vip` | `modules/shortcodes/class-shortcodes.php:111` |
| [`[trade_fair_contact_service_email]`](trade_fair_contact_service_email.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_service_email` | `modules/shortcodes/class-shortcodes.php:96` |
| [`[trade_fair_contact_service_name]`](trade_fair_contact_service_name.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_service_name` | `modules/shortcodes/class-shortcodes.php:94` |
| [`[trade_fair_contact_service_phone]`](trade_fair_contact_service_phone.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_service_phone` | `modules/shortcodes/class-shortcodes.php:95` |
| [`[trade_fair_contact_tech]`](trade_fair_contact_tech.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_contact_tech` | `modules/shortcodes/class-shortcodes.php:97` |
| [`[trade_fair_date]`](trade_fair_date.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_date` | `modules/shortcodes/class-shortcodes.php:47` |
| [`[trade_fair_date_custom_format]`](trade_fair_date_custom_format.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_date_custom_format` | `modules/shortcodes/class-shortcodes.php:46` |
| [`[trade_fair_date_eng]`](trade_fair_date_eng.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_date_eng` | `modules/shortcodes/class-shortcodes.php:48` |
| [`[trade_fair_date_multilang]`](trade_fair_date_multilang.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_date_multilang` | `modules/shortcodes/class-shortcodes.php:49` |
| [`[trade_fair_datetotimer]`](trade_fair_datetotimer.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_datetotimer` | `modules/shortcodes/class-shortcodes.php:44` |
| [`[trade_fair_desc]`](trade_fair_desc.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_desc` | `modules/shortcodes/class-shortcodes.php:39` |
| [`[trade_fair_desc_eng]`](trade_fair_desc_eng.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_desc_eng` | `modules/shortcodes/class-shortcodes.php:40` |
| [`[trade_fair_desc_short]`](trade_fair_desc_short.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_desc_short` | `modules/shortcodes/class-shortcodes.php:41` |
| [`[trade_fair_desc_short_eng]`](trade_fair_desc_short_eng.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_desc_short_eng` | `modules/shortcodes/class-shortcodes.php:42` |
| [`[trade_fair_domainadress]`](trade_fair_domainadress.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_domainadress` | `modules/shortcodes/class-shortcodes.php:90` |
| [`[trade_fair_edition]`](trade_fair_edition.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_edition` | `modules/shortcodes/class-shortcodes.php:76` |
| [`[trade_fair_enddata]`](trade_fair_enddata.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_enddata` | `modules/shortcodes/class-shortcodes.php:45` |
| [`[trade_fair_exhibitor_generator_badge_url]`](trade_fair_exhibitor_generator_badge_url.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_exhibitor_generator_badge_url` | `modules/shortcodes/class-shortcodes.php:123` |
| [`[trade_fair_exhibitor_generator_header_url]`](trade_fair_exhibitor_generator_header_url.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_exhibitor_generator_header_url` | `modules/shortcodes/class-shortcodes.php:122` |
| [`[trade_fair_exhibitor_generator_icons]`](trade_fair_exhibitor_generator_icons.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_exhibitor_generator_icons` | `modules/shortcodes/class-shortcodes.php:120` |
| [`[trade_fair_exhibitor_generator_text]`](trade_fair_exhibitor_generator_text.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_exhibitor_generator_text` | `modules/shortcodes/class-shortcodes.php:121` |
| [`[trade_fair_facebook]`](trade_fair_facebook.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_facebook` | `modules/shortcodes/class-shortcodes.php:84` |
| [`[trade_fair_feed_prefix]`](trade_fair_feed_prefix.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_feed_prefix` | `modules/shortcodes/class-shortcodes.php:82` |
| [`[trade_fair_first_day]`](trade_fair_first_day.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_first_day` | `modules/shortcodes/class-shortcodes.php:50` |
| [`[trade_fair_full_desc]`](trade_fair_full_desc.md) | `class-map` | `PWE_Shortcodes::sc_pwe_trade_fair_full_desc` | `modules/shortcodes/class-shortcodes.php:126` |
| [`[trade_fair_group]`](trade_fair_group.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_group` | `modules/shortcodes/class-shortcodes.php:75` |
| [`[trade_fair_hall]`](trade_fair_hall.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_hall` | `modules/shortcodes/class-shortcodes.php:72` |
| [`[trade_fair_hall_entrance]`](trade_fair_hall_entrance.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_hall_entrance` | `modules/shortcodes/class-shortcodes.php:73` |
| [`[trade_fair_instagram]`](trade_fair_instagram.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_instagram` | `modules/shortcodes/class-shortcodes.php:85` |
| [`[trade_fair_lidy]`](trade_fair_lidy.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_lidy` | `modules/shortcodes/class-shortcodes.php:112` |
| [`[trade_fair_linkedin]`](trade_fair_linkedin.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_linkedin` | `modules/shortcodes/class-shortcodes.php:86` |
| [`[trade_fair_main2]`](trade_fair_main2.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_main2` | `modules/shortcodes/class-shortcodes.php:78` |
| [`[trade_fair_name]`](trade_fair_name.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_name` | `modules/shortcodes/class-shortcodes.php:37` |
| [`[trade_fair_name_eng]`](trade_fair_name_eng.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_name_eng` | `modules/shortcodes/class-shortcodes.php:38` |
| [`[trade_fair_registration_benefits_en]`](trade_fair_registration_benefits_en.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_registration_benefits_en` | `modules/shortcodes/class-shortcodes.php:116` |
| [`[trade_fair_registration_benefits_pl]`](trade_fair_registration_benefits_pl.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_registration_benefits_pl` | `modules/shortcodes/class-shortcodes.php:115` |
| [`[trade_fair_rejestracja]`](trade_fair_rejestracja.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_rejestracja` | `modules/shortcodes/class-shortcodes.php:92` |
| [`[trade_fair_second_day]`](trade_fair_second_day.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_second_day` | `modules/shortcodes/class-shortcodes.php:51` |
| [`[trade_fair_third_day]`](trade_fair_third_day.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_third_day` | `modules/shortcodes/class-shortcodes.php:52` |
| [`[trade_fair_ticket_benefits_en]`](trade_fair_ticket_benefits_en.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_ticket_benefits_en` | `modules/shortcodes/class-shortcodes.php:118` |
| [`[trade_fair_ticket_benefits_pl]`](trade_fair_ticket_benefits_pl.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_ticket_benefits_pl` | `modules/shortcodes/class-shortcodes.php:117` |
| [`[trade_fair_youtube]`](trade_fair_youtube.md) | `class-map` | `PWE_Shortcodes::show_trade_fair_youtube` | `modules/shortcodes/class-shortcodes.php:87` |
| [`[url_dla_odwiedzajacych]`](url_dla_odwiedzajacych.md) | `dynamic-url` | `PWE_Shortcodes::show_multilang_url` | `modules/shortcodes/class-shortcodes.php:4148` |
| [`[url_dla_wystawcow]`](url_dla_wystawcow.md) | `dynamic-url` | `PWE_Shortcodes::show_multilang_url` | `modules/shortcodes/class-shortcodes.php:4148` |
| [`[url_home]`](url_home.md) | `dynamic-url` | `PWE_Shortcodes::show_multilang_url` | `modules/shortcodes/class-shortcodes.php:4148` |
| [`[url_informacje_organizacyjne_dla_wystawcow]`](url_informacje_organizacyjne_dla_wystawcow.md) | `dynamic-url` | `PWE_Shortcodes::show_multilang_url` | `modules/shortcodes/class-shortcodes.php:4148` |
| [`[url_katalog_wystawcow]`](url_katalog_wystawcow.md) | `dynamic-url` | `PWE_Shortcodes::show_multilang_url` | `modules/shortcodes/class-shortcodes.php:4148` |
| [`[url_kontakt]`](url_kontakt.md) | `dynamic-url` | `PWE_Shortcodes::show_multilang_url` | `modules/shortcodes/class-shortcodes.php:4148` |
| [`[url_krok2]`](url_krok2.md) | `dynamic-url` | `PWE_Shortcodes::show_multilang_url` | `modules/shortcodes/class-shortcodes.php:4148` |
| [`[url_potwierdzenie_rejestracji_wystawcy]`](url_potwierdzenie_rejestracji_wystawcy.md) | `dynamic-url` | `PWE_Shortcodes::show_multilang_url` | `modules/shortcodes/class-shortcodes.php:4148` |
| [`[url_rejestracja]`](url_rejestracja.md) | `dynamic-url` | `PWE_Shortcodes::show_multilang_url` | `modules/shortcodes/class-shortcodes.php:4148` |
| [`[url_wypromuj_sie]`](url_wypromuj_sie.md) | `dynamic-url` | `PWE_Shortcodes::show_multilang_url` | `modules/shortcodes/class-shortcodes.php:4148` |
| [`[url_zostan_wystawca]`](url_zostan_wystawca.md) | `dynamic-url` | `PWE_Shortcodes::show_multilang_url` | `modules/shortcodes/class-shortcodes.php:4148` |
