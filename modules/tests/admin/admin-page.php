<?php
if (!defined('ABSPATH')) { exit; }

final class PWE_System_Tests_Admin
{
    public static function render(): void
    {
        if (!PWE_System_Tests_Runner::allowed()) { wp_die('Brak uprawnień do diagnostyki.'); }
        $definitions = array_values(PWE_System_Tests_Runner::definitions());
        if (!$definitions) {
            echo '<p>Brak skonfigurowanych testów.</p>';
            return;
        }
        foreach ($definitions as &$definition) { unset($definition['callback']); }
        unset($definition);
        $config = ['tests' => $definitions, 'results' => PWE_System_Tests_Runner::results(),
            'nonce' => wp_create_nonce('pwe_system_tests_diagnostic'), 'url' => admin_url('admin-ajax.php')];
        ?>
        <div id="pwe-diagnostics">
            <?php PWE_System_Tests_Icons::sprite(); ?>
            <?php if (!empty($_GET['pwe_status']) && is_string($_GET['pwe_status'])) : ?>
                <p class="diag-tool-feedback" role="status"><?php echo PWE_System_Tests_Icons::icon('info'); ?><span><?php echo esc_html(wp_unslash($_GET['pwe_status'])); ?></span></p>
            <?php endif; ?>
            <section class="diag-toolbar" aria-label="Szybkie akcje">
                <strong><?php echo PWE_System_Tests_Icons::icon('play'); ?> Szybkie akcje</strong>
                <button type="button" class="diag-primary" data-run="all"><?php echo PWE_System_Tests_Icons::icon('play'); ?> Uruchom wszystkie testy</button>
                <button type="button" data-run="critical"><?php echo PWE_System_Tests_Icons::icon('shield'); ?> Testy krytyczne</button>
                <button type="button" data-run="forms"><?php echo PWE_System_Tests_Icons::icon('forms'); ?> Testy formularzy</button>
                <button type="button" data-run="languages"><?php echo PWE_System_Tests_Icons::icon('language'); ?> Testy tłumaczeń</button>
                <button type="button" data-run="integrations"><?php echo PWE_System_Tests_Icons::icon('gf_addon'); ?> Testy integracji</button>
                <button type="button" id="diag-clear"><?php echo PWE_System_Tests_Icons::icon('trash'); ?> Wyczyść wyniki</button>
                <a class="diag-report-link" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=pwe_system_tests_report'), 'pwe_system_tests_report')); ?>" target="_blank" rel="noopener noreferrer" aria-label="Otwórz raport JSON (nowa karta)"><?php echo PWE_System_Tests_Icons::icon('code'); ?> Otwórz raport JSON</a>
            </section>
            <section class="diag-filters" aria-label="Filtry testów">
                <label>Filtry testów<input id="diag-search" type="search" placeholder="Szukaj testu…"></label>
                <label>Kategoria<select id="diag-category"><option value="">Wszystkie kategorie</option></select></label>
                <label>Status<select id="diag-status"><option value="">Wszystkie statusy</option></select></label>
                <label>Komponent<select id="diag-component"><option value="">Wszystkie komponenty</option></select></label>
                <button type="button" id="diag-reset"><?php echo PWE_System_Tests_Icons::icon('sync'); ?> Resetuj filtry</button>
            </section>
            <section class="diag-summary">
                <div id="diag-counts" aria-live="polite"></div>
                <div class="diag-views" role="group" aria-label="Widok wyników">
                    <button type="button" data-view="grid" aria-pressed="true"><?php echo PWE_System_Tests_Icons::icon('grid'); ?> Siatka</button>
                    <button type="button" data-view="list" aria-pressed="false"><?php echo PWE_System_Tests_Icons::icon('list'); ?> Lista</button>
                    <select id="diag-sort" aria-label="Sortowanie"><option value="name">Nazwa A–Z</option><option value="status">Najpierw błędy</option><option value="critical">Najpierw krytyczne</option></select>
                </div>
            </section>
            <p id="diag-progress" role="status" aria-live="polite">Wybierz zestaw testów. Ostatnie wyniki są przechowywane przez 24 godziny.</p>
            <div id="diag-results"></div>
            <?php PWE_System_Tests_Result_Modal::render(); ?>
            <noscript><p>Włącz JavaScript, aby uruchamiać diagnostykę i wyświetlać wyniki.</p></noscript>
        </div>
        <script type="application/json" id="pwe-diagnostics-config"><?php echo wp_json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
        <?php
    }
}
