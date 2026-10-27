<?php
if (!defined('ABSPATH')) { exit; }

final class PWE_Images_Admin {
    public static function submit(): void {
        if (!current_user_can('manage_options')) { wp_die('Brak uprawnień.', '', ['response' => 403]); }
        check_admin_referer('pwe_images_generate');
        PWE_Images_Generator::generate(true);
        wp_safe_redirect(admin_url('admin.php?page=pwe-images-generator'));
        exit;
    }

    /** Serve only registered template PNGs to authenticated administrators. */
    public static function preview(): void {
        if (!current_user_can('manage_options')) { wp_die('Brak uprawnień.', '', ['response' => 403]); }
        check_admin_referer('pwe_images_preview');
        $id = isset($_GET['template']) && is_string($_GET['template']) ? $_GET['template'] : '';
        $templates = PWE_Image_Templates::all();
        if (!isset($templates[$id])) { wp_die('Nieprawidłowy szablon.', '', ['response' => 400]); }
        $name = $templates[$id]['filename'];
        $path = PWE_Images_Generator::config()['output_dir'] . '/' . $name;
        $file = @fopen($path, 'rb');
        if (!$file) { wp_die('Brak grafiki.', '', ['response' => 404]); }
        nocache_headers();
        header('Content-Type: image/png');
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: inline; filename="' . $name . '"');
        fpassthru($file);
        fclose($file);
        exit;
    }

    public static function page(): void {
        if (!current_user_can('manage_options')) { return; }
        $templates = PWE_Image_Templates::all();
        $c = PWE_Images_Generator::config();
        echo '<div class="wrap pwe-system-wrap pwe-system-module-page pwe-module-images">';
        PWE_System_Admin::render_module_header('PWE SYSTEM / GRAFIKI', 'Images Generator', 'Materiały promocyjne targów. Wybierz podgląd, aby otworzyć grafikę w pełnym rozmiarze.', 'dashicons-format-image');
        echo '<div class="pwe-module-content">';
        if ($error = get_option(PWE_Images_Generator::ERROR)) {
            echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
        }
        echo '<div class="pwe-images-toolbar"><div><span class="pwe-images-caption">Data targów na ostatnio wygenerowanych grafikach</span><strong>' . esc_html(get_option(PWE_Images_Generator::LAST, 'Brak generacji')) . '</strong></div>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="pwe_images_generate">';
        wp_nonce_field('pwe_images_generate');
        submit_button('Regeneruj wszystkie grafiki', 'primary', 'regenerate', false);
        echo '</form></div><div class="pwe-images-grid">';
        foreach ($templates as $id => $template) {
            $width = $template['width']; $height = $template['height'];
            $name = $template['filename'];
            $path = $c['output_dir'] . '/' . $name;
            $available = is_file($path) && is_readable($path);
            echo '<section class="pwe-images-card"><div class="pwe-images-card-heading"><h2>' . esc_html($template['label']) . '</h2><span class="pwe-images-language">' . esc_html(strtoupper($template['language'])) . '</span></div>';
            if ($available) {
                $url = add_query_arg([
                    'action' => 'pwe_images_preview', 'template' => $id,
                    '_wpnonce' => wp_create_nonce('pwe_images_preview'),
                    'v' => (string)filemtime($path),
                ], admin_url('admin-post.php'));
                echo '<a class="pwe-images-preview" href="' . esc_url($url) . '" target="_blank" rel="noopener" aria-label="' . esc_attr($template['label'] . ' — otwórz pełny rozmiar w nowej karcie') . '"><img src="' . esc_url($url) . '" alt="' . esc_attr($template['label']) . '" width="' . (int)$width . '" height="' . (int)$height . '" loading="lazy"></a>';
            } else {
                echo '<div class="pwe-images-preview pwe-images-empty"><span class="dashicons dashicons-format-image" aria-hidden="true"></span><p>Brak zapisanej grafiki</p></div>';
            }
            echo '<div class="pwe-images-card-footer"><span class="pwe-images-filename">' . esc_html($name) . '</span><span>' . esc_html($width . ' × ' . $height . ' px') . '</span></div></section>';
        }
        echo '</div><p class="pwe-images-hint">Regeneracja zastąpi wszystkie pliki aktualnymi grafikami z datą targów.</p></div></div>';
    }
}