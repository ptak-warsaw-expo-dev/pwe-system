<?php
/**
 * Independent template-news API. POST JSON, X-API-Key: PWE_API_KEY_2.
 * Also accepts ?key= for compatibility. Does not include news/index.php.
 *
 * Example (use action=validate to check without writing):
 * {"action":"upsert","template":"summary","slug":"news-123",
 *  "data_pl":{"title":"Polski tytuł","description":"<p>Opis</p>"},
 *  "data_en":{"title":"English title","description":"<p>Text</p>"}}
 *
 * Only data_{code} fields matching active WPML languages are used.
 * Other languages are skipped and listed in ignored_languages.
 * No matching language returns 422; WPML is required for upsert/validate.
 * Missing languages remain unchanged. Each supplied data_* replaces its data.
 * Top-level slug is the stable group identifier, independent of the title.
 * Optional data_*.slug sets a translation URL; otherwise preserve its existing
 * slug, or use the group slug (default language) / group slug + language code.
 * New posts default to publish; omitted status preserves existing status.
 * featured_image_url can be supplied per language or at the top level;
 * omission preserves the thumbnail, empty string removes it.
 *
 * Permanent delete of all managed translations:
 * {"action":"delete","template":"summary","slug":"news-123"}
 * Optional languages limits deletion to that list, including inactive languages.
 * Writes across languages are not atomic: failures return completed results.
 * Storage: _pwe_post_data = ['template' => ..., 'data' => ...].
 */
require_once __DIR__ . '/../../../../../wp-load.php';

final class PWE_Template_News_API {
    private const OWNER = 'pwe-template-news-v1';
    private const TEMPLATES = ['preview', 'summary', 'medal-ceremony'];
    private const TEMPLATE_CATEGORIES = [
        'summary' => ['podsumowania', 'Podsumowania'],
        'preview' => ['zapowiedzi', 'Zapowiedzi'],
        'medal-ceremony' => ['ceremonie-medalowe', 'Ceremonie Medalowe'],
    ];
    private const MAX_BYTES = 2097152;

    private static function fail($message, $status = 422) {
        throw new RuntimeException($message, $status);
    }

    private static function response($status, $body) {
        nocache_headers();
        status_header($status);
        header('Content-Type: application/json; charset=utf-8');
        echo wp_json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private static function string($value, $path, $required = false) {
        if (!is_string($value)) self::fail($path . ': expected string');
        $value = trim($value);
        if ($required && $value === '') self::fail($path . ': required');
        return $value;
    }

    private static function object($value, $path) {
        if (!is_array($value) || ($value !== [] && array_is_list($value))) {
            self::fail($path . ': expected object');
        }
        return $value;
    }

    /** Sanitize nested data without flattening repeatable lists or null values. */
    private static function clean_data($value, $path = 'data', $depth = 0) {
        if ($depth > 12) self::fail($path . ': nesting limit exceeded');
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $child) {
                if (is_string($key) && !preg_match('/^[a-zA-Z0-9_-]+$/D', $key)) {
                    self::fail($path . ': invalid field name');
                }
                $result[$key] = self::clean_data($child, $path . '.' . $key, $depth + 1);
            }
            return $result;
        }
        if (is_string($value)) return wp_kses_post($value);
        if ($value === null || is_bool($value) || is_int($value)) return $value;
        if (is_float($value) && is_finite($value)) return $value;
        self::fail($path . ': unsupported value');
    }

    private static function validate_data($data, $template) {
        $data = self::object($data, 'data');
        $title = sanitize_text_field(self::string($data['title'] ?? null, 'data.title', true));
        if ($title === '') self::fail('data.title: empty after sanitization');
        if (isset($data['type']) && $data['type'] !== $template) self::fail('data.type must match template');
        $objects = ['event', 'program', 'closing', 'statistics', 'image', 'ceremony'];
        $lists = ['exhibitions', 'partners', 'highlights', 'sections', 'registration_links', 'gallery', 'categories', 'distinctions', 'awards'];
        foreach ($objects as $field) {
            if (isset($data[$field])) self::object($data[$field], 'data.' . $field);
        }
        foreach ($lists as $field) {
            if (!isset($data[$field])) continue;
            if (!is_array($data[$field]) || !array_is_list($data[$field])) self::fail('data.' . $field . ': expected list');
            if ($field !== 'gallery') {
                foreach ($data[$field] as $row) self::object($row, 'data.' . $field . '[]');
            }
        }
        foreach (['current_edition', 'previous_edition'] as $edition) {
            if (!isset($data['statistics'][$edition])) continue;
            $stats = self::object($data['statistics'][$edition], 'statistics.' . $edition);
            foreach (['visitors', 'exhibitors', 'area', 'countries'] as $metric) {
                if (!isset($stats[$metric])) continue;
                if ((!is_int($stats[$metric]) && !is_float($stats[$metric])) || $stats[$metric] < 0) {
                    self::fail('statistics.' . $edition . '.' . $metric . ': expected nonnegative number or null');
                }
            }
        }
        $data = self::clean_data($data);
        $data['type'] = $template;
        $data['title'] = $title;
        return $data;
    }

    private static function find($news_slug, $language) {
        $posts = get_posts([
            'post_type' => 'post', 'post_status' => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
            'posts_per_page' => 2, 'suppress_filters' => true,
            'meta_query' => [
                ['key' => '_pwe_news_owner', 'value' => self::OWNER],
                ['key' => '_pwe_news_slug', 'value' => $news_slug],
                ['key' => '_pwe_news_language', 'value' => $language],
            ],
        ]);
        if (count($posts) > 1) self::fail('Duplicate managed posts for ' . $language, 409);
        return $posts[0] ?? null;
    }

    private static function category($language, $slug, $name) {
        $term = get_term_by('slug', $slug . '-' . $language, 'category');
        if (!$term || is_wp_error($term)) $term = get_term_by('slug', $slug, 'category');
        if (!$term || is_wp_error($term)) {
            $created = wp_insert_term($name, 'category', ['slug' => $slug]);
            if (is_wp_error($created)) {
                $existing = $created->get_error_data('term_exists');
                if (!$existing) return $created;
                $id = (int) $existing;
            } else $id = (int) $created['term_id'];
        } else $id = (int) $term->term_id;
        $translated = apply_filters('wpml_object_id', $id, 'category', false, $language);
        if ($translated) $id = (int) $translated;
        return $id;
    }

    private static function meta($id, $key, $value) {
        update_post_meta($id, $key, wp_slash($value));
        if (get_post_meta($id, $key, true) != $value) self::fail('Metadata write failed: ' . $key, 500);
    }

    private static function thumbnail($id, $source, $title) {
        if ($source === '') { delete_post_thumbnail($id); return null; }
        $current = get_post_thumbnail_id($id);
        if ($current && get_post_meta($current, '_pwe_source_image_url', true) === $source) return (int) $current;
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        $attachment = media_sideload_image($source, $id, $title, 'id');
        if (is_wp_error($attachment)) return $attachment;
        if (!set_post_thumbnail($id, $attachment)) return new WP_Error('thumbnail', 'Cannot set featured image');
        update_post_meta($attachment, '_pwe_source_image_url', wp_slash($source));
        return (int) $attachment;
    }

    public static function run() {
        $results = [];
        $ignored_languages = [];
        $lock = null;
        $current_id = null;
        $current_language = null;
        $status_code = 200;
        global $wpdb;
        try {
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
                header('Allow: POST');
                self::fail('Use POST', 405);
            }
            $key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['key'] ?? '';
            if (!defined('PWE_API_KEY_2') || (string) PWE_API_KEY_2 === '' || !is_string($key) || $key === '' || !hash_equals((string) PWE_API_KEY_2, $key)) {
                self::fail('Invalid API key', 401);
            }
            if (strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0])) !== 'application/json') {
                self::fail('Use Content-Type: application/json', 415);
            }
            $raw = file_get_contents('php://input', false, null, 0, self::MAX_BYTES + 1);
            if ($raw === false) self::fail('Cannot read request', 400);
            if (strlen($raw) > self::MAX_BYTES) self::fail('Request exceeds 2 MiB', 413);
            $request = json_decode($raw, true, 32);
            if (json_last_error() !== JSON_ERROR_NONE) self::fail('Invalid JSON');
            $request = self::object($request, 'request');
            $action = $request['action'] ?? 'upsert';
            if (!in_array($action, ['upsert', 'validate', 'delete'], true)) self::fail('Invalid action');
            if (($request['schema_version'] ?? 1) !== 1) self::fail('Unsupported schema_version');
            // Use a stable identifier; changing the title must not create a new post.
            $source_slug = $request['slug'] ?? null;
            $news_slug = sanitize_title(self::string($source_slug, 'slug', true));
            if ($news_slug === '') self::fail('Empty sanitized slug');
            $template = $request['template'] ?? null;
            $wpml = defined('ICL_SITEPRESS_VERSION') && has_action('wpml_set_element_language_details');
            $translations = [];
            if ($action === 'delete') {
                if (!in_array($template, self::TEMPLATES, true)) self::fail('Unsupported template');
                $languages = $request['languages'] ?? [];
                if (array_key_exists('languages', $request) && (!is_array($languages) || !array_is_list($languages) || !$languages)) self::fail('languages: nonempty list required');
            } else {
                if (!in_array($template, self::TEMPLATES, true)) self::fail('Unsupported template');
                if (isset($request['status']) && !in_array($request['status'], ['draft', 'publish'], true)) self::fail('status: draft or publish');
                if (isset($request['order']) && !is_int($request['order'])) self::fail('order: expected integer');
                if (!$wpml) self::fail('Active WPML is required to resolve data_* languages', 409);
                $active = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);
                if (!is_array($active) || !$active) self::fail('Cannot read active WPML languages', 409);
                $default_language = apply_filters('wpml_default_language', null);
                $ignored_languages = [];
                foreach ($request as $field => $data) {
                    if (!is_string($field) || !str_starts_with($field, 'data_')) continue;
                    $language = substr($field, 5);
                    if (!isset($active[$language])) {
                        $ignored_languages[] = $language;
                        continue;
                    }
                    $data = self::object($data, $field);
                    $entry = ['data' => $data];
                    if (array_key_exists('slug', $data)) $entry['slug'] = $data['slug'];
                    if (array_key_exists('featured_image_url', $data)) {
                        $entry['featured_image_url'] = $data['featured_image_url'];
                    } elseif (array_key_exists('featured_image_url', $request)) {
                        $entry['featured_image_url'] = $request['featured_image_url'];
                    }
                    $translations[$language] = $entry;
                }
                if (!$translations) self::fail('No data_* fields matching active WPML languages');
                $languages = array_keys($translations);
                // Create the site's default language first when supplied.
                if (in_array($default_language, $languages, true)) {
                    $languages = array_merge([$default_language], array_values(array_diff($languages, [$default_language])));
                }
            }
            foreach ($languages as $language) {
                if (!is_string($language) || !preg_match('/^[a-zA-Z][a-zA-Z0-9_-]{0,31}$/D', $language)) {
                    self::fail('Invalid language code');
                }
            }
            $languages = array_values(array_unique($languages));
            // Connection-scoped lock prevents duplicate creations on concurrent requests.
            $lock_name = 'pwe_news_' . substr(hash('sha256', $wpdb->prefix . ':' . $news_slug), 0, 48);
            if ((string) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock_name)) !== '1') {
                self::fail('Synchronization already running or lock unavailable; retry later', 409);
            }
            $lock = $lock_name;
            if ($action === 'delete' && !array_key_exists('languages', $request)) {
                $managed = get_posts([
                    'post_type' => 'post',
                    'post_status' => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
                    'posts_per_page' => -1,
                    'suppress_filters' => true,
                    'meta_query' => [
                        ['key' => '_pwe_news_owner', 'value' => self::OWNER],
                        ['key' => '_pwe_news_slug', 'value' => $news_slug],
                        ['key' => '_pwe_post_template', 'value' => $template],
                    ],
                ]);
                foreach ($managed as $managed_post) {
                    $language = get_post_meta($managed_post->ID, '_pwe_news_language', true);
                    if (!is_string($language) || $language === '') self::fail('Managed post is missing its language', 409);
                    $languages[] = $language;
                }
                $languages = array_values(array_unique($languages));
            }
            $prepared = [];
            foreach ($languages as $language) {
                $existing = self::find($news_slug, $language);
                if ($action === 'delete') {
                    if ($existing && get_post_meta($existing->ID, '_pwe_post_template', true) !== $template) {
                        self::fail('Template does not match the managed post', 409);
                    }
                    $prepared[$language] = ['existing' => $existing];
                    continue;
                }
                if ($existing && $existing->post_status === 'trash') self::fail('Restore the trashed post before updating: ' . $language, 409);
                $entry = $translations[$language];
                $entry_slug = $entry['slug'] ?? ($existing->post_name ?? ($language === $default_language ? $news_slug : $news_slug . '-' . $language));
                $slug = sanitize_title(self::string($entry_slug, 'data_' . $language . '.slug', true));
                if ($slug === '') self::fail('Empty sanitized slug');
                $collision = get_page_by_path($slug, OBJECT, 'post');
                if ($collision && (!$existing || (int) $collision->ID !== (int) $existing->ID)) self::fail('Slug belongs to another post: ' . $slug, 409);
                foreach ($prepared as $other) if ($other['slug'] === $slug) self::fail('Translation slugs must be different');
                $data = self::validate_data($entry['data'] ?? null, $template);
                if (array_key_exists('featured_image_url', $entry)) {
                    $source = self::string($entry['featured_image_url'], 'featured_image_url');
                    if ($source !== '' && (!preg_match('~^https?://~i', $source) || !wp_http_validate_url($source))) {
                        self::fail('Invalid or nonpublic featured_image_url');
                    }
                    $entry['featured_image_url'] = $source;
                }
                $prepared[$language] = ['existing' => $existing, 'slug' => $slug, 'data' => $data, 'entry' => $entry];
            }
            if ($action === 'validate') {
                $body = ['status' => 'success', 'action' => 'validate', 'slug' => $news_slug, 'template' => $template, 'languages' => $languages];
            } else {
                foreach ($prepared as $language => $item) {
                    $current_language = $language;
                    $current_id = $item['existing'] ? (int) $item['existing']->ID : null;
                    if ($action === 'delete') {
                        if (!$current_id) { $results[$language] = ['action' => 'not_found']; continue; }
                        if (get_post($current_id) && !wp_delete_post($current_id, true)) self::fail('Failed to delete post', 500);
                        $results[$language] = ['post_id' => $current_id, 'action' => 'deleted'];
                        continue;
                    }
                    $post = [
                        'post_type' => 'post', 'post_title' => $item['data']['title'],
                        'post_name' => $item['slug'], 'post_content' => '[pwe-elements-auto-switch-post-template]',
                        'post_status' => $request['status'] ?? ($item['existing']->post_status ?? 'publish'),
                        'menu_order' => $request['order'] ?? (int) ($item['existing']->menu_order ?? 0),
                        'meta_input' => [
                            '_pwe_news_owner' => self::OWNER, '_pwe_news_slug' => $news_slug,
                            '_pwe_news_language' => $language, '_pwe_news_schema_version' => 1,
                            '_pwe_post_template' => $template,
                            '_pwe_post_data' => ['template' => $template, 'data' => $item['data']],
                        ],
                    ];
                    if ($current_id) $post['ID'] = $current_id;
                    $saved = $current_id ? wp_update_post(wp_slash($post), true) : wp_insert_post(wp_slash($post), true);
                    if (is_wp_error($saved) || !$saved) self::fail('Post write failed: ' . (is_wp_error($saved) ? $saved->get_error_message() : 'unknown error'), 500);
                    $current_id = (int) $saved;
                    foreach ($post['meta_input'] as $key => $value) self::meta($current_id, $key, $value);
                    $warnings = [];
                    $category_ids = [];
                    foreach ([['news', 'News'], self::TEMPLATE_CATEGORIES[$template]] as [$category_slug, $category_name]) {
                        $category = self::category($language, $category_slug, $category_name);
                        if (is_wp_error($category)) $warnings[] = $category->get_error_message();
                        else $category_ids[] = $category;
                    }
                    if ($category_ids) {
                        $existing_categories = wp_get_post_categories($current_id);
                        if (is_wp_error($existing_categories)) {
                            $warnings[] = $existing_categories->get_error_message();
                        } else {
                            // WordPress adds its default category before API categories are assigned.
                            $default_category = (int) get_option('default_category');
                            $translated_default = (int) apply_filters('wpml_object_id', $default_category, 'category', false, $language);
                            $category_ids = array_values(array_unique(array_merge(
                                array_diff($existing_categories, [$default_category, $translated_default]),
                                $category_ids
                            )));
                            $category = wp_set_post_categories($current_id, $category_ids);
                            if (is_wp_error($category)) $warnings[] = $category->get_error_message();
                        }
                    }
                    if ($wpml) {
                        // Include translations omitted from this request, regardless of language.
                        $peers = get_posts([
                            'post_type' => 'post',
                            'post_status' => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
                            'post__not_in' => [$current_id],
                            'posts_per_page' => 1,
                            'orderby' => 'ID',
                            'order' => 'ASC',
                            'suppress_filters' => true,
                            'meta_query' => [
                                ['key' => '_pwe_news_owner', 'value' => self::OWNER],
                                ['key' => '_pwe_news_slug', 'value' => $news_slug],
                                ['key' => '_pwe_news_language', 'value' => $language, 'compare' => '!='],
                            ],
                        ]);
                        $peer = $peers[0] ?? null;
                        $trid = apply_filters('wpml_element_trid', null, $peer ? $peer->ID : $current_id, 'post_post');
                        $details = $peer ? apply_filters('wpml_element_language_details', null, ['element_id' => $peer->ID, 'element_type' => 'post_post']) : null;
                        $source = $peer ? (($details->source_language_code ?? null) ?: ($details->language_code ?? null)) : null;
                        if ($source === $language) $source = null;
                        do_action('wpml_set_element_language_details', [
                            'element_id' => $current_id, 'element_type' => 'post_post',
                            'trid' => $trid, 'language_code' => $language, 'source_language_code' => $source,
                        ]);
                    }
                    if (array_key_exists('featured_image_url', $item['entry'])) {
                        $thumbnail = self::thumbnail($current_id, $item['entry']['featured_image_url'], $item['data']['title']);
                        if (is_wp_error($thumbnail)) $warnings[] = $thumbnail->get_error_message();
                    }
                    clean_post_cache($current_id);
                    $results[$language] = [
                        'post_id' => $current_id, 'action' => $item['existing'] ? 'updated' : 'created',
                        'slug' => get_post_field('post_name', $current_id), 'url' => get_permalink($current_id),
                        'status' => get_post_status($current_id), 'warnings' => $warnings,
                    ];
                }
                $body = ['status' => 'success', 'action' => $action, 'slug' => $news_slug, 'wordpress' => $results];
            }
        } catch (Throwable $error) {
            $code = (int) $error->getCode();
            $status_code = ($error instanceof RuntimeException && $code >= 400 && $code <= 599) ? $code : 500;
            $body = ['status' => 'error', 'message' => $error instanceof RuntimeException ? $error->getMessage() : 'Unexpected synchronization error',
                'wordpress' => $results, 'failed_language' => $current_language, 'post_id' => $current_id];
            if (!($error instanceof RuntimeException)) error_log('PWE template news: ' . $error->getMessage());
        } finally {
            if ($lock !== null) $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
        }
        $body['ignored_languages'] = $ignored_languages;
        self::response($status_code, $body);
    }
}

PWE_Template_News_API::run();
