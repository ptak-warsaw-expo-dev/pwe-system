<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PWE_System_Replace_Content_Page_Locator
{
    public static function find_by_url(string $url): ?WP_Post
    {
        $path = wp_parse_url($url, PHP_URL_PATH);
        if (!is_string($path)) {
            return null;
        }

        $page_path = trim($path, '/');
        if ($page_path === '') {
            $front_page_id = (int) get_option('page_on_front');
            return $front_page_id > 0 ? self::as_page($front_page_id) : null;
        }

        $page_id = (int) url_to_postid(home_url('/' . $page_path . '/'));
        $page = $page_id > 0 ? self::as_page($page_id) : null;
        if ($page && $page->post_status !== 'trash') {
            return $page;
        }

        $page = get_page_by_path($page_path, OBJECT, 'page');
        return ($page instanceof WP_Post && $page->post_status !== 'trash') ? $page : null;
    }

    private static function as_page(int $post_id): ?WP_Post
    {
        $post = $post_id > 0 ? get_post($post_id) : null;
        return ($post instanceof WP_Post && $post->post_type === 'page') ? $post : null;
    }
}
