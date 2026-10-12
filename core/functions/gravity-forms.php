<?php

/**
 * Gravity Forms lookup, form lists and SMTP incident monitoring.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose gravity Forms lookup, form lists and SMTP incident monitoring in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_Gravity_Forms {
    /**
     * Resolved Gravity Forms IDs indexed by normalized form title.
     */
    private static array $gf_form_resolver_cache = [];

    /**
     * Find the latest active Gravity Forms form with an exact normalized title.
     *
     * Ignore leading parenthetical prefixes, such as "(2027) (PL)".
     * Require the remaining title to match exactly, without additional suffixes.
     * Prefer the highest prefix year, then the highest form ID.
     *
     * @param string $base_title Base title without leading prefixes.
     * @return int|null Latest matching form ID, or null when no form matches.
     */
    public static function get_gf_form_id(string $base_title): ?int {
        global $wpdb;

        $base_title = trim($base_title);

        if ($base_title === '') {
            return null;
        }

        $normalized_base_title = mb_strtolower($base_title, 'UTF-8');
        $cache_key = $normalized_base_title;

        if (array_key_exists($cache_key, self::$gf_form_resolver_cache)) {
            return self::$gf_form_resolver_cache[$cache_key];
        }

        $transient_key = 'pwe_gf_form_resolver_' . md5($cache_key);
        $cached = get_transient($transient_key);

        if ($cached !== false) {
            self::$gf_form_resolver_cache[$cache_key] = $cached ? (int) $cached : null;

            return self::$gf_form_resolver_cache[$cache_key];
        }

        $table = $wpdb->prefix . 'gf_form';

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT id, title
                FROM {$table}
                WHERE is_active = 1
                AND is_trash = 0
                AND title LIKE %s
                ORDER BY id DESC
                ",
                '%' . $wpdb->esc_like($base_title) . '%'
            ),
            ARRAY_A
        );

        if ($wpdb->last_error || empty($rows)) {
            set_transient($transient_key, 0, 10 * MINUTE_IN_SECONDS);
            self::$gf_form_resolver_cache[$cache_key] = null;

            return null;
        }

        $resolved_id = null;
        $resolved_year = 0;

        foreach ($rows as $row) {
            $title = trim((string) $row['title']);

            // Gets all the parentheses at the beginning.
            //
            // Examples: "(2027) Registration" => prefixes: "(2027)" "(2027) (PL) Registration" =>
            // prefixes: "(2027) (PL)".
            preg_match('/^(?<prefixes>(?:\s*\([^)]*\))+)?\s*(?<title>.*)$/u', $title, $matches);

            $title_without_prefixes = trim($matches['title'] ?? $title);

            // After removing the initial brackets, the name must be identical. This will prevent
            // "Registration (FB)" and "Guest Registration..." from matching.
            if (mb_strtolower($title_without_prefixes, 'UTF-8') !== $normalized_base_title) {
                continue;
            }

            $year = 0;
            $prefixes = $matches['prefixes'] ?? '';

            // We look for the year only in parentheses located at the beginning.
            if (
                $prefixes !== ''
                && preg_match_all('/\((?:[^)]*?\b)?((?:19|20)\d{2})\b[^)]*\)/u', $prefixes, $year_matches)
                && !empty($year_matches[1])
            ) {
                $year = max(array_map('intval', $year_matches[1]));
            }

            $row_id = (int) $row['id'];

            // Prefer the highest prefix year, then the highest form ID.
            if (
                $resolved_id === null
                || $year > $resolved_year
                || ($year === $resolved_year && $row_id > $resolved_id)
            ) {
                $resolved_id = $row_id;
                $resolved_year = $year;
            }
        }

        set_transient(
            $transient_key,
            $resolved_id ?: 0,
            10 * MINUTE_IN_SECONDS
        );

        self::$gf_form_resolver_cache[$cache_key] = $resolved_id;

        return $resolved_id;
    }

    /**
     * List Gravity Forms titles and IDs for the admin area.
     */
    public function findFormsGF($mode = '') {
        $pwe_forms_array = array();
        if (is_admin()) {
            if (method_exists('GFAPI', 'get_forms')) {
                $pwe_forms = GFAPI::get_forms();
                if ($mode == 'id') {
                    foreach ($pwe_forms as $form) {
                        $pwe_forms_array[$form['title']] = $form['id'];
                    }
                } else {
                    foreach ($pwe_forms as $form) {
                        $pwe_forms_array[$form['id']] = $form['title'];
                    }
                }
            }
        }
        return $pwe_forms_array;
    }

    /**
     * Find a Gravity Forms form ID by its exact title.
     */
    public static function findFormsID($form_name) {
        $pwe_form_id = '';
        if (method_exists('GFAPI', 'get_forms')) {
            $pwe_forms = GFAPI::get_forms();
            foreach ($pwe_forms as $form) {
                if ($form['title'] === $form_name) {
                    $pwe_form_id = $form['id'];
                    break;
                }
            }
        }
        return $pwe_form_id;
    }

    /**
     * Track Gravity Forms SMTP failures and send failure or recovery alerts.
     */
    public static function gravity_forms_smtp_monitor(
        $is_success = null,
        $to = '',
        $subject = '',
        $message = '',
        $headers = [],
        $attachments = [],
        $message_format = '',
        $from = '',
        $from_name = '',
        $bcc = '',
        $reply_to = '',
        $entry = false
    ) {
        $alert_recipients = [
            'jakub.chola@warsawexpo.eu',
            's.skrypnychenko@warsawexpo.eu',
            'jakub.koscielak@warsawexpo.eu',
            'jakub.goral@warsawexpo.eu',
        ];

        $smtp_host       = 'dedyk180.cyber-folks.pl';
        $smtp_port       = 465;
        $smtp_encryption = 'ssl';
        $smtp_username   = 'smtp@mr.glasstec.pl';
        $smtp_password   = defined('PWE_API_KEY_2') ? PWE_API_KEY_2 : '';

        $max_alerts_per_hour = 100;

        // Handle a successful send and close an active SMTP incident.
        if ($is_success === true) {
            $incident_active = get_transient('pwe_gf_smtp_incident_active');

            if ($incident_active) {
                $failure_count = (int) get_transient('pwe_gf_smtp_failure_count');
                $recovery_sent = get_transient('pwe_gf_smtp_recovery_sent');

                if (!$recovery_sent) {
                    $entry_id = (is_array($entry) && !empty($entry['id'])) ? $entry['id'] : '';
                    $form_id  = (is_array($entry) && !empty($entry['form_id'])) ? $entry['form_id'] : '';
                    $time     = current_time('Y-m-d H:i:s');

                    $recovery_subject = '✅ Gravity Forms SMTP RECOVERY - ' . $smtp_host;

                    $recovery_body  = "Gravity Forms SMTP ponownie działa.\n\n";
                    $recovery_body .= "STATUS: RECOVERY\nData: {$time}\n\n";
                    $recovery_body .= "Serwer backup SMTP:\n{$smtp_host}:{$smtp_port}\nEncryption: {$smtp_encryption}\n\n";
                    $recovery_body .= "Kolejny poprawnie wysłany email:\nDo: {$to}\nTemat: {$subject}\nForm ID: {$form_id}\nEntry ID: {$entry_id}\n\n";
                    $recovery_body .= "Liczba wykrytych błędów przed recovery: {$failure_count}\n\n";
                    $recovery_body .= "Gravity Forms ponownie poprawnie przetworzył wysyłkę wiadomości.";

                    $phpmailer_instance = null;
                    $phpmailer_config   = static function ($phpmailer) use (
                        &$phpmailer_instance,
                        $smtp_host,
                        $smtp_port,
                        $smtp_username,
                        $smtp_password,
                        $smtp_encryption
                    ) {
                        $phpmailer_instance = $phpmailer;
                        $phpmailer->isSMTP();

                        $phpmailer->Host     = $smtp_host;
                        $phpmailer->Port     = $smtp_port;
                        $phpmailer->SMTPAuth = true;
                        $phpmailer->Username = $smtp_username;
                        $phpmailer->Password = $smtp_password;

                        if ($smtp_encryption === 'ssl') {
                            $phpmailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                        } elseif ($smtp_encryption === 'tls') {
                            $phpmailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        }

                        $phpmailer->From     = $smtp_username;
                        $phpmailer->FromName = 'WordPress SMTP Monitor';
                        $phpmailer->Timeout  = 15;
                    };

                    add_action('phpmailer_init', $phpmailer_config, 999);
                    $result = wp_mail($alert_recipients, $recovery_subject, $recovery_body);
                    remove_action('phpmailer_init', $phpmailer_config, 999);

                    if ($result) {
                        set_transient('pwe_gf_smtp_recovery_sent', 1, DAY_IN_SECONDS);
                    }
                }

                delete_transient('pwe_gf_smtp_incident_active');
                delete_transient('pwe_gf_smtp_failure_count');
                delete_transient('pwe_gf_smtp_recovery_sent');

                return;
            }

            return;
        }

        // Handle a failed send and report the SMTP incident.
        if ($is_success !== false) {
            return;
        }

        $entry_id = (is_array($entry) && !empty($entry['id'])) ? $entry['id'] : '';
        $form_id  = (is_array($entry) && !empty($entry['form_id'])) ? $entry['form_id'] : '';

        $form_title = '';
        if ($form_id && class_exists('GFAPI')) {
            $form = \GFAPI::get_form($form_id);
            if (is_array($form) && !empty($form['title'])) {
                $form_title = $form['title'];
            }
        }

        $time = current_time('Y-m-d H:i:s');

        $failure_count = (int) get_transient('pwe_gf_smtp_failure_count');
        $failure_count++;

        set_transient('pwe_gf_smtp_failure_count', $failure_count, DAY_IN_SECONDS);
        set_transient('pwe_gf_smtp_incident_active', 1, DAY_IN_SECONDS);

        $alert_count = (int) get_transient('pwe_gf_smtp_alert_count');
        if ($alert_count >= $max_alerts_per_hour) {
            return;
        }

        $alert_subject = '🚨 Gravity Forms SMTP FAILURE - ' . $smtp_host;

        $alert_body  = "Gravity Forms wykrył problem z wysyłką emaila.\n\n";
        $alert_body .= "STATUS: SMTP FAILURE\nData: {$time}\n\n";
        $alert_body .= "FORMULARZ:\nNazwa: {$form_title}\nForm ID: {$form_id}\nEntry ID: {$entry_id}\n\n";
        $alert_body .= "EMAIL:\nDo: {$to}\nTemat: {$subject}\n\n";
        $alert_body .= "LICZNIKI:\nBłąd nr: {$failure_count}\nAlert nr w tej godzinie: " . ($alert_count + 1) . " / {$max_alerts_per_hour}\n\n";
        $alert_body .= "BACKUP SMTP:\nHost: {$smtp_host}\nPort: {$smtp_port}\nEncryption: {$smtp_encryption}\nUsername: {$smtp_username}\n\n";
        $alert_body .= "Gravity Forms zwrócił is_success = FALSE.\nMonitor wysłał alert przez backup SMTP.";

        if (empty($smtp_password)) {
            return;
        }

        $phpmailer_instance = null;
        $phpmailer_config   = static function ($phpmailer) use (
            &$phpmailer_instance,
            $smtp_host,
            $smtp_port,
            $smtp_username,
            $smtp_password,
            $smtp_encryption
        ) {
            $phpmailer_instance = $phpmailer;
            $phpmailer->isSMTP();

            $phpmailer->Host     = $smtp_host;
            $phpmailer->Port     = $smtp_port;
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $smtp_username;
            $phpmailer->Password = $smtp_password;

            if ($smtp_encryption === 'ssl') {
                $phpmailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtp_encryption === 'tls') {
                $phpmailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }

            $phpmailer->From     = $smtp_username;
            $phpmailer->FromName = 'WordPress SMTP Monitor';
            $phpmailer->Timeout  = 15;
        };

        add_action('phpmailer_init', $phpmailer_config, 999);
        $result = wp_mail($alert_recipients, $alert_subject, $alert_body);
        remove_action('phpmailer_init', $phpmailer_config, 999);

        if ($result) {
            $alert_count++;
            set_transient('pwe_gf_smtp_alert_count', $alert_count, HOUR_IN_SECONDS);
        }
    }
}
