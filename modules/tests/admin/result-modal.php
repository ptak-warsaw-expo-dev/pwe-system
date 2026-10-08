<?php
if (!defined('ABSPATH')) { exit; }

final class PWE_System_Tests_Result_Modal
{
    public static function render(): void
    {
        ?>
            <dialog id="diag-dialog" aria-labelledby="diag-dialog-title" aria-describedby="diag-detail-description">
                <header class="diag-modal-header">
                    <span id="diag-modal-symbol" class="diag-modal-symbol"><?php echo PWE_System_Tests_Icons::icon('grid'); ?></span>
                    <div class="diag-modal-heading"><h2 id="diag-dialog-title"></h2><p id="diag-detail-description"></p><div id="diag-detail-meta"></div></div>
                    <button type="button" id="diag-close" aria-label="Zamknij szczegóły"><?php echo PWE_System_Tests_Icons::icon('close'); ?></button>
                </header>
                <div class="diag-modal-body">
                    <section id="diag-result-banner" class="diag-result-banner" aria-live="polite">
                        <span id="diag-result-symbol" class="diag-result-symbol"></span>
                        <div class="diag-result-copy"><h3>Wynik</h3><p id="diag-detail-message"></p></div>
                        <div id="diag-metrics" class="diag-metrics"></div>
                    </section>
                    <section id="diag-next-step" class="diag-next-step" hidden><span class="diag-metric-icon"><?php echo PWE_System_Tests_Icons::icon('info'); ?></span><div><h3>Co sprawdzić dalej</h3><ul id="diag-detail-suggestions"></ul></div></section>
                    <section class="diag-diagnosis">
                        <div class="diag-diagnosis-head"><span class="diag-metric-icon"><?php echo PWE_System_Tests_Icons::icon('list'); ?></span><div><h3>Szczegóły diagnozy</h3><p>Zakres sprawdzenia</p></div><label class="diag-modal-search"><?php echo PWE_System_Tests_Icons::icon('search'); ?><input id="diag-row-search" type="search" aria-label="Szukaj w wynikach" placeholder="Szukaj nazwy, pliku lub wyniku…"></label></div>
                        <div class="diag-table-toolbar"><span id="diag-row-count" aria-live="polite"></span><label>Pokaż: <span class="diag-select-wrap"><select id="diag-row-status" aria-label="Status wyników"><option value="">Wszystkie</option><option value="success">OK</option><option value="error">Błędy</option><option value="warning">Ostrzeżenia</option><option value="skipped">Pominięte</option></select><?php echo PWE_System_Tests_Icons::icon('chevron'); ?></span></label></div>
                        <div id="diag-detail-visual"></div>
                    </section>
                    <details id="diag-raw"><summary><?php echo PWE_System_Tests_Icons::icon('code'); ?> Pełne dane wyniku <?php echo PWE_System_Tests_Icons::icon('chevron'); ?></summary><pre id="diag-detail-data"></pre></details>
                </div>
                <footer class="diag-dialog-actions"><span class="diag-duration"><?php echo PWE_System_Tests_Icons::icon('clock'); ?><span id="diag-duration"></span></span><button type="button" id="diag-single" class="diag-primary"><?php echo PWE_System_Tests_Icons::icon('play'); ?> Uruchom test</button></footer>
            </dialog>
        <?php
    }
}
