<?php
if (!defined('ABSPATH')) {
    exit;
}

final class PWE_System_Doc_Manager {
    private const NONCE_ACTION = 'pwe_system_doc_manager';
    private const LOG_TABLE_SUFFIX = 'pwe_system_log';

    public static function init(): void {
        add_action('admin_menu', [self::class, 'register_menu'], 6);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);

        $actions = [
            'list'    => 'ajax_list',
            'mkdir'   => 'ajax_mkdir',
            'rename'  => 'ajax_rename',
            'delete'  => 'ajax_delete',
            'move'    => 'ajax_move',
            'upload'  => 'ajax_upload',
            'replace' => 'ajax_replace',
            'unzip'   => 'ajax_unzip',
        ];

        foreach ($actions as $action => $callback) {
            add_action('wp_ajax_pwe_system_doc_' . $action, [self::class, $callback]);
        }
    }

    public static function register_menu(): void {
        add_submenu_page(
            'pwe-system',
            'DOC Manager',
            'DOC Manager',
            self::capability(),
            'pwe-system-doc',
            [self::class, 'render_page']
        );
    }

    public static function enqueue_assets(string $hook): void {
        if (strpos($hook, 'pwe-system-doc') === false) {
            return;
        }

        $css = PWE_SYSTEM_PATH . 'modules/doc-manager/assets/admin.css';
        $js  = PWE_SYSTEM_PATH . 'modules/doc-manager/assets/admin.js';

        wp_enqueue_style(
            'pwe-system-doc',
            PWE_SYSTEM_URL . 'modules/doc-manager/assets/admin.css',
            ['pwe-system-admin'],
            is_file($css) ? (string) filemtime($css) : PWE_SYSTEM_VERSION
        );

        wp_enqueue_script(
            'pwe-system-doc',
            PWE_SYSTEM_URL . 'modules/doc-manager/assets/admin.js',
            [],
            is_file($js) ? (string) filemtime($js) : PWE_SYSTEM_VERSION,
            true
        );

        wp_localize_script('pwe-system-doc', 'PWESystemDoc', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE_ACTION),
            'baseUrl' => trailingslashit(site_url('/doc')),
            'maxUpload' => wp_max_upload_size(),
            'maxUploadLabel' => size_format(wp_max_upload_size()),
        ]);
    }

    public static function capability(): string {
        return (string) apply_filters('pwe_system_doc_capability', 'pwe_manage_doc');
    }

    public static function ensure_doc_directory(): void {
        $base = self::base_dir();
        if (!is_dir($base)) {
            wp_mkdir_p($base);
        }
    }

    public static function install_log_table(): void {
        global $wpdb;
        $table = $wpdb->prefix . self::LOG_TABLE_SUFFIX;
        $charset = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta("CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            user_email VARCHAR(190) NOT NULL DEFAULT '',
            action VARCHAR(40) NOT NULL,
            source_path TEXT NULL,
            target_path TEXT NULL,
            details TEXT NULL,
            PRIMARY KEY (id),
            KEY created_at (created_at),
            KEY action (action)
        ) {$charset};");
    }

    public static function render_page(): void {
        if (!current_user_can(self::capability())) {
            wp_die(esc_html__('Brak uprawnień do DOC Managera.', 'pwe-system'));
        }

        self::ensure_doc_directory();
        ?>
        <div class="wrap pwe-system-wrap pwe-doc-app" id="pwe-doc-app">
            <div class="pwe-doc-head">
                <div class="pwe-doc-title-row">
                    <img src="<?php echo esc_url(PWE_SYSTEM_URL . 'assets/images/logo-pwe.webp'); ?>" alt="PWE" class="pwe-doc-logo">
                    <div>
                        <div class="pwe-system-kicker">PWE SYSTEM / DOC</div>
                        <h1>DOC Manager</h1>
                        <p>Pełne zarządzanie katalogiem <code>/doc</code>. Przeciągaj foldery, wrzucaj całe drzewa katalogów, zmieniaj nazwy i podmieniaj pliki.</p>
                    </div>
                </div>
                <div class="pwe-doc-actions">
                    <button type="button" class="button" data-action="new-folder"><span class="dashicons dashicons-plus-alt2"></span> Nowy folder</button>
                    <button type="button" class="button" data-action="pick-folder"><span class="dashicons dashicons-category"></span> Wgraj katalog</button>
                    <button type="button" class="button button-primary" data-action="pick-files"><span class="dashicons dashicons-upload"></span> Dodaj / podmień pliki</button>
                    <input type="file" id="pwe-doc-files" multiple hidden>
                    <input type="file" id="pwe-doc-folder" webkitdirectory directory multiple hidden>
                </div>
            </div>

            <div class="pwe-doc-toolbar">
                <div id="pwe-doc-breadcrumbs" class="pwe-doc-breadcrumbs"></div>
                <div class="pwe-doc-toolbar-right">
                    <input type="search" id="pwe-doc-filter" placeholder="Filtruj w tym folderze…">
                    <button type="button" class="button" data-action="refresh"><span class="dashicons dashicons-update"></span></button>
                </div>
            </div>

            <div class="pwe-doc-dropzone" id="pwe-doc-dropzone">
                <span class="dashicons dashicons-move"></span>
                <strong>Upuść tutaj pliki albo cały katalog</strong>
                <span>Struktura folderów zostanie zachowana. Pliki o tej samej nazwie zostaną podmienione.</span>
            </div>

            <div class="pwe-doc-progress" id="pwe-doc-progress" hidden>
                <div class="pwe-doc-progress-bar"><span></span></div>
                <div class="pwe-doc-progress-label"></div>
            </div>

            <div class="pwe-doc-table-wrap">
                <table class="widefat fixed striped pwe-doc-table">
                    <thead>
                        <tr>
                            <th class="column-name">Nazwa</th>
                            <th>Typ</th>
                            <th>Rozmiar</th>
                            <th class="column-dimensions">Wymiary</th>
                            <th>Modyfikacja</th>
                            <th class="column-actions">Akcje</th>
                        </tr>
                    </thead>
                    <tbody id="pwe-doc-list">
                        <tr><td colspan="6" class="pwe-doc-loading">Wczytywanie…</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="pwe-doc-hint">
                <span class="dashicons dashicons-info-outline"></span>
                <span>Przenoszenie: złap plik lub folder za nazwę i upuść go na wybranym folderze. Dla bezpieczeństwa DOC Manager blokuje pliki wykonywalne, np. PHP i skrypty serwerowe.</span>
            </div>

            <div class="pwe-doc-toast" id="pwe-doc-toast" aria-live="polite"></div>

            <dialog id="pwe-doc-dialog" class="pwe-doc-dialog">
                <form method="dialog">
                    <div class="pwe-doc-dialog-head">
                        <h2 id="pwe-doc-dialog-title">Operacja</h2>
                        <button value="cancel" class="pwe-doc-dialog-close" aria-label="Zamknij">×</button>
                    </div>
                    <div id="pwe-doc-dialog-body"></div>
                    <div class="pwe-doc-dialog-actions">
                        <button value="cancel" class="button">Anuluj</button>
                        <button type="button" class="button button-primary" id="pwe-doc-dialog-confirm">Zapisz</button>
                    </div>
                </form>
            </dialog>
        </div>
        <?php
    }

    public static function ajax_list(): void {
        self::guard();
        $dir = self::clean_rel(self::post('dir'));
        $path = self::resolve_existing($dir, true);

        if (!$path || !is_dir($path)) {
            self::error('Katalog nie istnieje.', 404);
        }

        $items = [];
        $iterator = new DirectoryIterator($path);
        foreach ($iterator as $entry) {
            if ($entry->isDot()) {
                continue;
            }

            if ($entry->isLink()) {
                continue;
            }

            $name = $entry->getFilename();
            if ($name === '.htaccess' || $name === '.user.ini') {
                continue;
            }

            $rel = ltrim($dir . '/' . $name, '/');
            $is_dir = $entry->isDir() && !$entry->isLink();
            $extension = $is_dir ? '' : strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
            $dimensions = $is_dir ? null : self::image_dimensions($entry->getPathname(), $extension);
            $items[] = [
                'name' => $name,
                'path' => $rel,
                'type' => $is_dir ? 'folder' : 'file',
                'extension' => $extension,
                'size' => $is_dir ? null : $entry->getSize(),
                'sizeLabel' => $is_dir ? '—' : size_format($entry->getSize(), 1),
                'modified' => wp_date('Y-m-d H:i', $entry->getMTime()),
                'url' => $is_dir ? null : self::file_url($rel),
                'width' => $dimensions['width'] ?? null,
                'height' => $dimensions['height'] ?? null,
                'isImage' => $dimensions !== null,
                'dimensionsLabel' => $dimensions ? ($dimensions['width'] . ' × ' . $dimensions['height'] . ' px') : '—',
            ];
        }

        usort($items, static function (array $a, array $b): int {
            if ($a['type'] === 'folder' && $b['type'] !== 'folder') return -1;
            if ($a['type'] !== 'folder' && $b['type'] === 'folder') return 1;
            return strnatcasecmp($a['name'], $b['name']);
        });

        wp_send_json_success([
            'dir' => $dir,
            'items' => $items,
            'breadcrumbs' => self::breadcrumbs($dir),
        ]);
    }

    public static function ajax_mkdir(): void {
        self::guard();
        $dir = self::clean_rel(self::post('dir'));
        $name = self::clean_name(self::post('name'));
        if ($name === '') self::error('Podaj nazwę folderu.');

        $parent = self::resolve_existing($dir, true);
        if (!$parent || !is_dir($parent)) self::error('Katalog nadrzędny nie istnieje.', 404);

        $target = $parent . DIRECTORY_SEPARATOR . $name;
        if (file_exists($target)) self::error('Plik lub folder o tej nazwie już istnieje.', 409);
        if (!wp_mkdir_p($target)) self::error('Nie udało się utworzyć folderu.', 500);

        self::log('mkdir', null, self::join_rel($dir, $name));
        wp_send_json_success(['message' => 'Folder utworzony.']);
    }

    public static function ajax_rename(): void {
        self::guard();
        $path_rel = self::clean_rel(self::post('path'));
        $new_name = self::clean_name(self::post('name'));
        if ($path_rel === '' || $new_name === '') self::error('Brak nazwy lub ścieżki.');

        $source = self::resolve_existing($path_rel, false);
        if (!$source) self::error('Element nie istnieje.', 404);
        if (is_file($source) && !self::is_allowed_filename($new_name)) {
            self::error('Ten typ pliku jest zablokowany ze względów bezpieczeństwa.', 422);
        }

        $parent_rel = self::parent_rel($path_rel);
        $parent = self::resolve_existing($parent_rel, true);
        $target = $parent . DIRECTORY_SEPARATOR . $new_name;
        $target_rel = self::join_rel($parent_rel, $new_name);

        if (file_exists($target)) self::error('Element o tej nazwie już istnieje.', 409);
        if (!@rename($source, $target)) self::error('Nie udało się zmienić nazwy.', 500);

        self::log('rename', $path_rel, $target_rel);
        wp_send_json_success(['message' => 'Nazwa została zmieniona.', 'path' => $target_rel]);
    }

    public static function ajax_delete(): void {
        self::guard();
        $path_rel = self::clean_rel(self::post('path'));
        if ($path_rel === '') self::error('Nie można usunąć katalogu /doc.');

        $path = self::resolve_existing($path_rel, false);
        if (!$path) self::error('Element nie istnieje.', 404);

        if (!self::delete_tree($path)) self::error('Nie udało się usunąć elementu.', 500);
        self::log('delete', $path_rel, null);
        wp_send_json_success(['message' => 'Element został usunięty.']);
    }

    public static function ajax_move(): void {
        self::guard();
        $source_rel = self::clean_rel(self::post('source'));
        $target_dir_rel = self::clean_rel(self::post('targetDir'));
        if ($source_rel === '') self::error('Nie można przenieść katalogu /doc.');

        $source = self::resolve_existing($source_rel, false);
        $target_dir = self::resolve_existing($target_dir_rel, true);
        if (!$source || !$target_dir || !is_dir($target_dir)) self::error('Nieprawidłowa ścieżka.', 404);

        $name = basename($source);
        $target = $target_dir . DIRECTORY_SEPARATOR . $name;
        $target_rel = self::join_rel($target_dir_rel, $name);

        if (self::path_is_inside($target_dir, $source, true)) {
            self::error('Nie można przenieść folderu do jego własnego podfolderu.', 409);
        }
        if (file_exists($target)) self::error('W folderze docelowym istnieje już element o tej nazwie.', 409);
        if (!@rename($source, $target)) self::error('Nie udało się przenieść elementu.', 500);

        self::log('move', $source_rel, $target_rel);
        wp_send_json_success(['message' => 'Element został przeniesiony.', 'path' => $target_rel]);
    }

    public static function ajax_upload(): void {
        self::guard();
        self::handle_upload(false);
    }

    public static function ajax_replace(): void {
        self::guard();
        self::handle_upload(true);
    }

    public static function ajax_unzip(): void {
        self::guard();

        if (!class_exists('ZipArchive')) {
            self::error('Na serwerze nie jest dostępne rozszerzenie PHP ZipArchive.', 500);
        }

        $zip_rel = self::clean_rel(self::post('path'));
        $target_dir_rel = self::clean_rel(self::post('targetDir'));
        $overwrite = self::post('overwrite') === '1';

        if ($zip_rel === '' || strtolower((string) pathinfo($zip_rel, PATHINFO_EXTENSION)) !== 'zip') {
            self::error('Wybierz prawidłowy plik ZIP.', 422);
        }

        $zip_path = self::resolve_existing($zip_rel, false);
        if (!$zip_path || !is_file($zip_path)) {
            self::error('Plik ZIP nie istnieje.', 404);
        }

        $target_dir = self::ensure_directory_path($target_dir_rel);
        if (!$target_dir) {
            self::error('Nie udało się utworzyć katalogu docelowego.', 500);
        }

        $zip = new ZipArchive();
        $opened = $zip->open($zip_path);
        if ($opened !== true) {
            self::error('Nie udało się otworzyć archiwum ZIP. Kod: ' . (string) $opened, 422);
        }

        $max_entries = max(1, (int) apply_filters('pwe_system_doc_zip_max_entries', 5000));
        $max_uncompressed = max(1, (int) apply_filters('pwe_system_doc_zip_max_uncompressed_bytes', 2147483648));

        if ($zip->numFiles > $max_entries) {
            $zip->close();
            self::error('Archiwum zawiera zbyt wiele elementów.', 422);
        }

        $plan = [];
        $total_uncompressed = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (!is_array($stat) || !isset($stat['name'])) {
                $zip->close();
                self::error('Nie udało się odczytać zawartości archiwum.', 422);
            }

            $entry_name = self::clean_zip_entry((string) $stat['name']);
            if ($entry_name === '') {
                continue;
            }

            if (self::zip_entry_is_symlink($zip, $i)) {
                $zip->close();
                self::error('Archiwum zawiera dowiązanie symboliczne, którego nie można rozpakować.', 422);
            }

            $is_dir = substr($entry_name, -1) === '/';
            if (!$is_dir && !self::is_allowed_filename(basename($entry_name))) {
                $zip->close();
                self::error('Archiwum zawiera zablokowany typ pliku: ' . basename($entry_name), 422);
            }

            $size = max(0, (int) ($stat['size'] ?? 0));
            $total_uncompressed += $size;
            if ($total_uncompressed > $max_uncompressed) {
                $zip->close();
                self::error('Rozpakowana zawartość archiwum przekracza dozwolony limit.', 422);
            }

            $target_rel = self::join_rel($target_dir_rel, $entry_name);
            $target_abs = self::base_real() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $target_rel);
            if (!self::path_is_inside($target_abs, self::base_real(), false)) {
                $zip->close();
                self::error('Archiwum zawiera nieprawidłową ścieżkę.', 422);
            }
            if (rtrim(str_replace('\\', '/', $target_abs), '/') === rtrim(str_replace('\\', '/', $zip_path), '/')) {
                $zip->close();
                self::error('Archiwum zawiera plik, który nadpisałby samo archiwum ZIP.', 422);
            }

            $plan[] = [
                'index' => $i,
                'zip_name' => (string) $stat['name'],
                'entry' => $entry_name,
                'target_rel' => $target_rel,
                'target_abs' => $target_abs,
                'is_dir' => $is_dir,
            ];
        }

        $extracted = 0;
        $skipped = 0;
        $created_dirs = 0;

        foreach ($plan as $item) {
            if ($item['is_dir']) {
                if (!is_dir($item['target_abs']) && !wp_mkdir_p($item['target_abs'])) {
                    $zip->close();
                    self::error('Nie udało się utworzyć katalogu: ' . $item['entry'], 500);
                }
                $created_dirs++;
                continue;
            }

            $parent = dirname($item['target_abs']);
            if (!is_dir($parent) && !wp_mkdir_p($parent)) {
                $zip->close();
                self::error('Nie udało się utworzyć katalogu dla: ' . $item['entry'], 500);
            }

            if (file_exists($item['target_abs']) && !$overwrite) {
                $skipped++;
                continue;
            }
            if (is_dir($item['target_abs'])) {
                $zip->close();
                self::error('Nie można nadpisać katalogu plikiem: ' . $item['entry'], 409);
            }

            $stream = $zip->getStream($item['zip_name']);
            if (!is_resource($stream)) {
                $zip->close();
                self::error('Nie udało się odczytać pliku z archiwum: ' . $item['entry'], 500);
            }

            $out = @fopen($item['target_abs'], 'wb');
            if (!is_resource($out)) {
                fclose($stream);
                $zip->close();
                self::error('Nie udało się zapisać pliku: ' . $item['entry'], 500);
            }

            $copied = stream_copy_to_stream($stream, $out);
            fclose($stream);
            fclose($out);

            if ($copied === false) {
                @unlink($item['target_abs']);
                $zip->close();
                self::error('Nie udało się rozpakować pliku: ' . $item['entry'], 500);
            }

            @chmod($item['target_abs'], 0644);
            $extracted++;
        }

        $zip->close();

        self::log('unzip', $zip_rel, $target_dir_rel, [
            'files_extracted' => $extracted,
            'files_skipped' => $skipped,
            'directories' => $created_dirs,
            'overwrite' => $overwrite,
        ]);

        $message = 'Rozpakowano ' . $extracted . ' plików.';
        if ($skipped > 0) {
            $message .= ' Pominięto istniejące: ' . $skipped . '.';
        }

        wp_send_json_success([
            'message' => $message,
            'extracted' => $extracted,
            'skipped' => $skipped,
            'targetDir' => $target_dir_rel,
        ]);
    }

    private static function handle_upload(bool $force_replace): void {
        if (empty($_FILES['file']) || !isset($_FILES['file']['tmp_name'])) {
            self::error('Brak pliku.');
        }

        $dir = self::clean_rel(self::post('dir'));
        $relative = self::clean_rel(self::post('relativePath'));
        $replace_path = self::clean_rel(self::post('replacePath'));
        $file = $_FILES['file'];

        if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            self::error(self::upload_error((int) $file['error']), 422);
        }

        $tmp = (string) $file['tmp_name'];
        if ($tmp === '' || !is_uploaded_file($tmp)) self::error('Nieprawidłowy plik tymczasowy.', 422);

        if ($force_replace && $replace_path !== '') {
            $existing = self::resolve_existing($replace_path, false);
            if (!$existing || !is_file($existing)) self::error('Plik do podmiany nie istnieje.', 404);
            $target_rel = $replace_path;
        } else {
            $relative = $relative !== '' ? $relative : self::clean_name((string) ($file['name'] ?? ''));
            if ($relative === '') self::error('Nieprawidłowa nazwa pliku.');
            $target_rel = self::join_rel($dir, $relative);
        }

        $target_name = basename($target_rel);
        if (!self::is_allowed_filename($target_name)) {
            self::error('Ten typ pliku jest zablokowany ze względów bezpieczeństwa.', 422);
        }

        $parent_rel = self::parent_rel($target_rel);
        $parent = self::ensure_directory_path($parent_rel);
        if (!$parent) self::error('Nie udało się utworzyć katalogu docelowego.', 500);

        $target = $parent . DIRECTORY_SEPARATOR . basename($target_rel);
        $replaced = is_file($target);

        if (is_dir($target)) self::error('W tym miejscu istnieje folder o takiej nazwie.', 409);
        if (!@move_uploaded_file($tmp, $target)) self::error('Nie udało się zapisać pliku.', 500);
        @chmod($target, 0644);

        self::log($replaced ? 'replace' : 'upload', null, $target_rel, [
            'size' => (int) ($file['size'] ?? 0),
            'original_name' => sanitize_file_name((string) ($file['name'] ?? '')),
        ]);

        wp_send_json_success([
            'message' => $replaced ? 'Plik został podmieniony.' : 'Plik został dodany.',
            'path' => $target_rel,
            'replaced' => $replaced,
        ]);
    }

    private static function guard(): void {
        if (!current_user_can(self::capability())) {
            self::error('Brak uprawnień.', 403);
        }
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        self::ensure_doc_directory();
    }

    private static function base_dir(): string {
        return trailingslashit(ABSPATH) . 'doc';
    }

    private static function base_real(): string {
        self::ensure_doc_directory();
        $real = realpath(self::base_dir());
        return $real ? rtrim($real, '/\\') : rtrim(self::base_dir(), '/\\');
    }

    private static function clean_rel(string $path): string {
        $path = wp_unslash($path);
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);
        $path = trim((string) $path, '/');
        if ($path === '') return '';

        $parts = [];
        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.') continue;
            if ($part === '..' || strpos($part, "\0") !== false || preg_match('/[\x00-\x1F\x7F]/u', $part)) {
                self::error('Nieprawidłowa ścieżka.', 400);
            }
            $parts[] = $part;
        }
        return implode('/', $parts);
    }

    private static function clean_name(string $name): string {
        $name = trim(wp_unslash($name));
        if ($name === '' || $name === '.' || $name === '..') return '';
        if (strpos($name, "\0") !== false || strpos($name, '/') !== false || strpos($name, '\\') !== false || preg_match('/[\x00-\x1F\x7F]/u', $name)) {
            return '';
        }
        return $name;
    }

    private static function resolve_existing(string $rel, bool $must_be_dir): ?string {
        $base = self::base_real();
        $candidate = $rel === '' ? $base : $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        $real = realpath($candidate);
        if ($real === false || !self::path_is_inside($real, $base, true)) return null;
        if ($must_be_dir && !is_dir($real)) return null;
        return $real;
    }

    private static function ensure_directory_path(string $rel): ?string {
        $base = self::base_real();
        if ($rel === '') return $base;
        $target = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        if (!is_dir($target) && !wp_mkdir_p($target)) return null;
        $real = realpath($target);
        if ($real === false || !self::path_is_inside($real, $base, true)) return null;
        return $real;
    }

    private static function path_is_inside(string $path, string $parent, bool $allow_same = false): bool {
        $path = rtrim(str_replace('\\', '/', $path), '/');
        $parent = rtrim(str_replace('\\', '/', $parent), '/');
        if ($allow_same && $path === $parent) return true;
        return strpos($path . '/', $parent . '/') === 0 && $path !== $parent;
    }

    private static function parent_rel(string $rel): string {
        $parent = str_replace('\\', '/', dirname($rel));
        return $parent === '.' ? '' : trim($parent, '/');
    }

    private static function join_rel(string $a, string $b): string {
        return trim(trim($a, '/') . '/' . trim($b, '/'), '/');
    }

    private static function is_allowed_filename(string $name): bool {
        $lower = strtolower($name);
        if ($lower === '.htaccess' || $lower === '.user.ini' || $lower === 'web.config') return false;

        $ext = strtolower((string) pathinfo($lower, PATHINFO_EXTENSION));
        $blocked = [
            'php','php3','php4','php5','php7','php8','phtml','pht','phar',
            'cgi','pl','py','rb','sh','bash','zsh','fish','fcgi','shtml','asp','aspx','jsp'
        ];
        $blocked = (array) apply_filters('pwe_system_doc_blocked_extensions', $blocked);
        return $ext === '' || !in_array($ext, $blocked, true);
    }

    private static function image_dimensions(string $path, string $extension): ?array {
        if (!is_file($path)) {
            return null;
        }

        $extension = strtolower($extension);

        if ($extension !== 'svg') {
            $size = function_exists('wp_getimagesize') ? @wp_getimagesize($path) : @getimagesize($path);
            if (is_array($size) && isset($size[0], $size[1]) && (int) $size[0] > 0 && (int) $size[1] > 0) {
                return ['width' => (int) $size[0], 'height' => (int) $size[1]];
            }
            return null;
        }

        $svg = @file_get_contents($path, false, null, 0, 131072);
        if (!is_string($svg) || $svg === '') {
            return null;
        }

        $width = self::svg_dimension($svg, 'width');
        $height = self::svg_dimension($svg, 'height');
        if ($width && $height) {
            return ['width' => $width, 'height' => $height];
        }

        if (preg_match('/\bviewBox\s*=\s*["\']\s*[-+]?\d*\.?\d+(?:[eE][-+]?\d+)?[\s,]+[-+]?\d*\.?\d+(?:[eE][-+]?\d+)?[\s,]+([-+]?\d*\.?\d+(?:[eE][-+]?\d+)?)[\s,]+([-+]?\d*\.?\d+(?:[eE][-+]?\d+)?)["\']/i', $svg, $match)) {
            $view_width = (int) round((float) $match[1]);
            $view_height = (int) round((float) $match[2]);
            if ($view_width > 0 && $view_height > 0) {
                return ['width' => $view_width, 'height' => $view_height];
            }
        }

        return null;
    }

    private static function svg_dimension(string $svg, string $attribute): ?int {
        if (!preg_match('/\b' . preg_quote($attribute, '/') . '\s*=\s*["\']\s*([0-9]+(?:\.[0-9]+)?)\s*(?:px)?\s*["\']/i', $svg, $match)) {
            return null;
        }
        $value = (int) round((float) $match[1]);
        return $value > 0 ? $value : null;
    }

    private static function clean_zip_entry(string $name): string {
        $name = str_replace('\\', '/', $name);
        $name = preg_replace('#/+#', '/', $name);
        $name = ltrim((string) $name, '/');

        if ($name === '' || strpos($name, "\0") !== false || preg_match('/[\x00-\x1F\x7F]/u', $name)) {
            return '';
        }
        if (preg_match('/^[a-zA-Z]:\//', $name)) {
            self::error('Archiwum zawiera niedozwoloną ścieżkę absolutną.', 422);
        }

        $is_dir = substr($name, -1) === '/';
        $parts = [];
        foreach (explode('/', trim($name, '/')) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                self::error('Archiwum zawiera próbę wyjścia poza katalog docelowy.', 422);
            }
            $parts[] = $part;
        }

        $clean = implode('/', $parts);
        return $is_dir && $clean !== '' ? $clean . '/' : $clean;
    }

    private static function zip_entry_is_symlink(ZipArchive $zip, int $index): bool {
        if (!method_exists($zip, 'getExternalAttributesIndex')) {
            return false;
        }

        $opsys = 0;
        $attr = 0;
        if (!$zip->getExternalAttributesIndex($index, $opsys, $attr)) {
            return false;
        }

        // UNIX file type is stored in the upper 16 bits. 0120000 = symlink.
        $mode = ($attr >> 16) & 0170000;
        return $mode === 0120000;
    }

    private static function delete_tree(string $path): bool {
        if (is_link($path) || is_file($path)) return @unlink($path);
        if (!is_dir($path)) return false;

        $items = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
        foreach ($items as $item) {
            if (!self::delete_tree($item->getPathname())) return false;
        }
        return @rmdir($path);
    }

    private static function breadcrumbs(string $dir): array {
        $out = [['label' => 'doc', 'dir' => '']];
        if ($dir === '') return $out;
        $acc = [];
        foreach (explode('/', $dir) as $segment) {
            $acc[] = $segment;
            $out[] = ['label' => $segment, 'dir' => implode('/', $acc)];
        }
        return $out;
    }

    private static function file_url(string $rel): string {
        $parts = array_map('rawurlencode', explode('/', $rel));
        return trailingslashit(site_url('/doc')) . implode('/', $parts);
    }

    private static function post(string $key): string {
        return isset($_POST[$key]) && is_scalar($_POST[$key]) ? (string) $_POST[$key] : '';
    }

    private static function error(string $message, int $status = 400): void {
        wp_send_json_error(['message' => $message], $status);
    }

    private static function upload_error(int $code): string {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'Plik jest większy niż limit serwera.';
            case UPLOAD_ERR_PARTIAL:
                return 'Plik został przesłany tylko częściowo.';
            case UPLOAD_ERR_NO_FILE:
                return 'Nie wybrano pliku.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Brak katalogu tymczasowego na serwerze.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Serwer nie może zapisać pliku.';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload został zatrzymany przez rozszerzenie PHP.';
            default:
                return 'Błąd uploadu: ' . $code;
        }
    }

    private static function log(string $action, ?string $source, ?string $target, array $details = []): void {
        global $wpdb;
        $table = $wpdb->prefix . self::LOG_TABLE_SUFFIX;
        $user = wp_get_current_user();

        // Lazy install also covers upgrades from a version without the table.
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
            self::install_log_table();
        }

        $wpdb->insert($table, [
            'created_at' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'user_email' => (string) $user->user_email,
            'action' => $action,
            'source_path' => $source,
            'target_path' => $target,
            'details' => $details ? wp_json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ], ['%s','%d','%s','%s','%s','%s','%s']);
    }
}
