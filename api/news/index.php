<?php

require_once __DIR__ . '/../../../../../wp-load.php';

header('Content-Type: application/json; charset=utf-8');

function pwe_system_api_news_json_response($status_code, $data)
{
    http_response_code($status_code);
    echo json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

function pwe_system_api_news_assign_category($post_id, $language = 'pl')
{
    if (empty($post_id)) {
        return false;
    }

    $specific_slug = ($language === 'en') ? 'news-en' : 'news-pl';
    $category_id   = false;

    $category = get_term_by('slug', $specific_slug, 'category');

    if (!$category || is_wp_error($category)) {
        $category = get_term_by('slug', 'news', 'category');
    }

    if (!$category || is_wp_error($category)) {
        $new_term = wp_insert_term('News', 'category', [
            'slug' => 'news',
        ]);

        if (!is_wp_error($new_term)) {
            $category_id = (int) $new_term['term_id'];
        } else {
            return false;
        }
    } else {
        $category_id = (int) $category->term_id;
    }

    if (function_exists('apply_filters') && $language === 'en') {
        $translated_category_id = apply_filters(
            'wpml_object_id',
            $category_id,
            'category',
            false,
            'en'
        );

        if ($translated_category_id) {
            $category_id = (int) $translated_category_id;
        }
    }

    wp_set_post_categories($post_id, [$category_id], false);

    return $category_id;
}

function pwe_system_api_news_prepare_uncode_raw_html($html)
{
    $html = (string) ($html ?? '');

    $html = preg_replace('/<!DOCTYPE[^>]*>/i', '', $html);
    $html = preg_replace('/<html\b[^>]*>/i', '', $html);
    $html = preg_replace('/<\/html>/i', '', $html);
    $html = preg_replace('/<head\b[^>]*>/i', '', $html);
    $html = preg_replace('/<\/head>/i', '', $html);
    $html = preg_replace('/<body\b[^>]*>/i', '', $html);
    $html = preg_replace('/<\/body>/i', '', $html);
    $html = trim($html);

    $encoded_html = base64_encode(rawurlencode($html));

    return '[vc_row][vc_column][vc_raw_html]' . $encoded_html . '[/vc_raw_html][/vc_column][/vc_row]';
}

function pwe_system_api_news_set_featured_image_from_url($image_url, $post_id, $title)
{
    if (empty($image_url) || empty($post_id)) {
        return null;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $current_thumbnail_id = get_post_thumbnail_id($post_id);

    if ($current_thumbnail_id) {
        $current_source_url = get_post_meta($current_thumbnail_id, '_pwe_source_image_url', true);
        if (!empty($current_source_url) && $current_source_url === $image_url) {
            return (int) $current_thumbnail_id;
        }
    }

    $attachment_id = media_sideload_image($image_url, $post_id, $title, 'id');

    if (is_wp_error($attachment_id)) {
        return ['error' => $attachment_id->get_error_message()];
    }

    set_post_thumbnail($post_id, $attachment_id);
    update_post_meta($attachment_id, '_pwe_source_image_url', $image_url);

    return (int) $attachment_id;
}

// Autoryzacja
$provided_key = $_GET['key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? '';

if (!defined('PWE_API_KEY_2') || empty($provided_key) || !hash_equals((string) PWE_API_KEY_2, (string) $provided_key)) {
    pwe_system_api_news_json_response(401, [
        'status'  => 'error',
        'message' => 'Brak dostępu: nieprawidłowy klucz API',
    ]);
}

$raw_input = file_get_contents('php://input');
$json_data = json_decode($raw_input, true);

if (!is_array($json_data)) {
    pwe_system_api_news_json_response(422, [
        'status'  => 'error',
        'message' => 'Nieprawidłowy JSON',
    ]);
}

$action = $json_data['action'] ?? 'upsert';
$post   = $json_data['post'] ?? null;

if (!in_array($action, ['upsert', 'delete'], true)) {
    pwe_system_api_news_json_response(422, [
        'status'  => 'error',
        'message' => 'Nieprawidłowa akcja. Dozwolone: upsert, delete',
    ]);
}

// Weryfikacja slug
if ($action === 'delete') {
    $slug = sanitize_title($json_data['slug'] ?? '');
    if (empty($slug)) {
        pwe_system_api_news_json_response(422, [
            'status'  => 'error',
            'message' => 'Brak slug do usunięcia',
        ]);
    }
} else {
    if (!is_array($post) || empty($post['slug'])) {
        pwe_system_api_news_json_response(422, [
            'status'  => 'error',
            'message' => 'Brak danych posta albo slug',
        ]);
    }

    $slug = sanitize_title($post['slug']);
    if (empty($slug)) {
        pwe_system_api_news_json_response(422, [
            'status'  => 'error',
            'message' => 'Nieprawidłowy slug',
        ]);
    }
}

// Operacje w WordPressie
$wp_result = [];

if ($action === 'delete') {
    $existing_post = get_page_by_path($slug, OBJECT, 'post');

    if (!$existing_post) {
        $posts = get_posts([
            'post_type'      => 'post',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'meta_query'     => [
                [
                    'key'   => '_pwe_sync_slug',
                    'value' => $slug,
                ]
            ]
        ]);
        $existing_post = !empty($posts) ? $posts[0] : null;
    }

    if ($existing_post) {
        $post_id_pl = (int) $existing_post->ID;
        $post_id_en = apply_filters('wpml_object_id', $post_id_pl, 'post', false, 'en');
        $post_id_en = $post_id_en ? (int) $post_id_en : 0;

        if ($post_id_en > 0 && $post_id_en !== $post_id_pl) {
            wp_delete_post($post_id_en, true);
        }

        wp_delete_post($post_id_pl, true);

        $wp_result = [
            'deleted_pl' => $post_id_pl,
            'deleted_en' => $post_id_en,
        ];
    } else {
        $wp_result = [
            'message' => 'Wpis nie istniał w WordPressie',
        ];
    }
} else {
    $title_pl    = sanitize_text_field($post['title_pl'] ?? '');
    $title_en    = sanitize_text_field($post['title_en'] ?? $title_pl);
    $raw_html_pl = (string) ($post['html_pl'] ?? '');
    $raw_html_en = (string) ($post['html_en'] ?? $raw_html_pl);

    $html_pl = pwe_system_api_news_prepare_uncode_raw_html($raw_html_pl);
    $html_en = pwe_system_api_news_prepare_uncode_raw_html($raw_html_en);

    $image_pl_url = esc_url_raw($post['image_pl_url'] ?? '');
    $image_en_url = esc_url_raw($post['image_en_url'] ?? $image_pl_url);
    $menu_order   = (int) ($post['order'] ?? 0);

    $existing_post = get_page_by_path($slug, OBJECT, 'post');

    if (!$existing_post) {
        $posts = get_posts([
            'post_type'      => 'post',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'meta_query'     => [
                [
                    'key'   => '_pwe_sync_slug',
                    'value' => $slug,
                ]
            ]
        ]);
        $existing_post = !empty($posts) ? $posts[0] : null;
    }

    $post_id_pl = $existing_post ? (int) $existing_post->ID : 0;

    $post_data_pl = [
        'ID'           => $post_id_pl,
        'post_title'   => $title_pl,
        'post_name'    => $slug,
        'post_content' => $html_pl,
        'post_status'  => 'publish',
        'post_type'    => 'post',
        'menu_order'   => $menu_order,
    ];

    $result_pl = ($post_id_pl > 0) ? wp_update_post($post_data_pl, true) : wp_insert_post($post_data_pl, true);

    if (is_wp_error($result_pl)) {
        pwe_system_api_news_json_response(500, [
            'status'  => 'error',
            'message' => 'Błąd zapisu polskiego wpisu',
            'error'   => $result_pl->get_error_message(),
        ]);
    }

    $post_id_pl = (int) $result_pl;
    pwe_system_api_news_assign_category($post_id_pl, 'pl');

    global $wpdb;
    $wpdb->update(
        $wpdb->posts,
        ['post_content' => $html_pl],
        ['ID' => $post_id_pl],
        ['%s'],
        ['%d']
    );

    clean_post_cache($post_id_pl);
    update_post_meta($post_id_pl, '_pwe_sync_slug', $slug);

    $element_type = 'post_post';
    $trid = apply_filters('wpml_element_trid', null, $post_id_pl, $element_type);

    do_action('wpml_set_element_language_details', [
        'element_id'           => $post_id_pl,
        'element_type'         => $element_type,
        'trid'                 => $trid,
        'language_code'        => 'pl',
        'source_language_code' => null,
    ]);

    $featured_pl = pwe_system_api_news_set_featured_image_from_url($image_pl_url, $post_id_pl, $title_pl);

    $post_id_en = apply_filters('wpml_object_id', $post_id_pl, 'post', false, 'en');
    $post_id_en = $post_id_en ? (int) $post_id_en : 0;

    if ($post_id_en === $post_id_pl) {
        $post_id_en = 0;
    }

    $post_data_en = [
        'ID'           => $post_id_en,
        'post_title'   => $title_en,
        'post_name'    => $slug . '-en',
        'post_content' => $html_en,
        'post_status'  => 'publish',
        'post_type'    => 'post',
        'menu_order'   => $menu_order,
    ];

    $result_en = ($post_id_en > 0) ? wp_update_post($post_data_en, true) : wp_insert_post($post_data_en, true);

    if (is_wp_error($result_en)) {
        pwe_system_api_news_json_response(500, [
            'status'  => 'error',
            'message' => 'Błąd zapisu angielskiego wpisu',
            'error'   => $result_en->get_error_message(),
        ]);
    }

    $post_id_en = (int) $result_en;
    pwe_system_api_news_assign_category($post_id_en, 'en');

    $wpdb->update(
        $wpdb->posts,
        ['post_content' => $html_en],
        ['ID' => $post_id_en],
        ['%s'],
        ['%d']
    );

    clean_post_cache($post_id_en);
    update_post_meta($post_id_en, '_pwe_sync_slug', $slug);

    $trid_pl = apply_filters('wpml_element_trid', null, $post_id_pl, $element_type);

    do_action('wpml_set_element_language_details', [
        'element_id'           => $post_id_en,
        'element_type'         => $element_type,
        'trid'                 => $trid_pl,
        'language_code'        => 'en',
        'source_language_code' => 'pl',
    ]);

    $featured_en = pwe_system_api_news_set_featured_image_from_url($image_en_url, $post_id_en, $title_en);

    $wp_result = [
        'post_id_pl'             => $post_id_pl,
        'post_id_en'             => $post_id_en,
        'url_pl'                 => get_permalink($post_id_pl),
        'url_en'                 => get_permalink($post_id_en),
        'featured_image_pl'      => $featured_pl,
        'featured_image_en'      => $featured_en,
        'raw_html_pl_length'     => strlen($raw_html_pl),
        'raw_html_en_length'     => strlen($raw_html_en),
        'post_content_pl_length' => strlen($html_pl),
        'post_content_en_length' => strlen($html_en),
    ];
}

pwe_system_api_news_json_response(200, [
    'status'    => 'success',
    'message'   => $action === 'delete'
        ? 'Wpis usunięty z WordPressa'
        : 'Wpis zapisany w WordPressie',
    'action'    => $action,
    'slug'      => $slug,
    'wordpress' => $wp_result,
]);