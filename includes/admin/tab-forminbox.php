<?php
if (!defined('ABSPATH')) exit;
/**
 * Evoke ONE — Tab: Skrzynka wiadomości (ustawienia modułu)
 */

$fi     = evk_inbox_get_settings();
$nonce  = wp_create_nonce('evk_inbox_nonce');
$has_tbl = evk_inbox_table_exists();
$inbox_url = admin_url('admin.php?page=evk-form-inbox');
?>

<!-- STATUS CARD -->
<div class="evo-status-card">
    <div class="evo-status-icon <?php echo !empty($fi['enabled']) ? 'on' : 'off'; ?>">
        <span class="dashicons dashicons-email-alt" style="font-size:24px;width:24px;height:24px;line-height:1;"></span>
    </div>
    <div class="evo-status-text">
        <h3>Skrzynka wiadomości: <?php echo !empty($fi['enabled']) ? 'WŁĄCZONA' : 'WYŁĄCZONA'; ?></h3>
        <p>Odczytuj zgłoszenia formularzy Bricks jak e-maile — bezpośrednio w panelu WordPress.</p>
    </div>
    <div class="evo-status-actions">
        <span class="evo-toggle-label"><?php echo !empty($fi['enabled']) ? 'Włączona' : 'Wyłączona'; ?></span>
        <label class="evo-toggle">
            <input type="checkbox"
                   data-option="evk_forminbox"
                   data-field="enabled"
                   value="1"
                   <?php checked(!empty($fi['enabled'])); ?>>
            <span class="evo-slider"></span>
        </label>
    </div>
</div>

<?php if (!$has_tbl): ?>
<div class="evo-info-box" style="border-color:#fde68a;background:#fffbeb;margin-top:16px;">
    <span class="dashicons dashicons-warning" style="color:#d97706;"></span>
    <div>
        Tabela zgłoszeń Bricks nie istnieje. Przejdź do <strong>Bricks → Ustawienia → Ogólne</strong> i włącz
        <em>„Zapisuj zgłoszenia formularzy"</em>, a następnie w każdym formularzu dodaj akcję <em>„Save Submission"</em>.
    </div>
</div>
<?php elseif (!empty($fi['enabled'])): ?>
<div class="evo-info-box" style="border-color:#86efac;background:#f0fdf4;margin-top:16px;">
    <span class="dashicons dashicons-yes-alt" style="color:#16a34a;"></span>
    <div>
        Moduł aktywny.
        <a href="<?php echo esc_url($inbox_url); ?>" class="button button-secondary" style="margin-left:10px;">
            <span class="dashicons dashicons-email-alt" style="font-size:14px;vertical-align:middle;margin-right:4px;line-height:1.6;"></span>
            Otwórz skrzynkę
        </a>
    </div>
</div>
<?php endif; ?>

<!-- USTAWIENIA -->
<form method="post" action="options.php" style="margin-top:24px;">
    <?php settings_fields(EVK_INBOX_OPTION . '_group'); ?>

    <p class="evo-section-title">Konfiguracja menu</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:24px;">
        <div class="evo-field" style="margin:0;">
            <label>Nazwa menu</label>
            <input type="text" name="evk_forminbox[menu_label]"
                   value="<?php echo esc_attr($fi['menu_label']); ?>"
                   placeholder="Wiadomości">
            <div class="evo-desc">Tekst wyświetlany w lewym menu WP.</div>
        </div>
        <div class="evo-field" style="margin:0;">
            <label>Ikona (Dashicons)</label>
            <input type="text" name="evk_forminbox[menu_icon]"
                   value="<?php echo esc_attr($fi['menu_icon']); ?>"
                   placeholder="dashicons-email-alt">
            <div class="evo-desc"><a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">Lista Dashicons ↗</a></div>
        </div>
        <div class="evo-field" style="margin:0;">
            <label>Pozycja w menu</label>
            <input type="number" name="evk_forminbox[menu_position]"
                   value="<?php echo esc_attr($fi['menu_position']); ?>"
                   min="1" max="100" style="max-width:80px;">
            <div class="evo-desc">Liczba — im mniejsza, tym wyżej. Domyślnie: 25.</div>
        </div>
        <div class="evo-field" style="margin:0;">
            <label>Wiadomości na stronę</label>
            <input type="number" name="evk_forminbox[per_page]"
                   value="<?php echo esc_attr($fi['per_page']); ?>"
                   min="5" max="100" style="max-width:80px;">
        </div>
    </div>

    <hr class="evo-divider">
    <p class="evo-section-title">Konfiguracja pól</p>
    <div class="evo-info-box" style="margin-bottom:16px;">
        <span class="dashicons dashicons-info"></span>
        <div>
            Przypisz etykiety do kluczy pól Bricks (np. <code>form-field-abc</code> → <em>Imię i nazwisko</em>).
            Pola bez etykiety są wyświetlane z automatycznie wygenerowaną nazwą.
            Pola oznaczone jako <em>ukryte</em> nie pojawiają się w podglądzie ani eksporcie.
        </div>
    </div>

    <div class="evo-field" style="margin-bottom:16px;">
        <label>Klucz pola e-mail (auto-detect jeśli puste)</label>
        <input type="text" name="evk_forminbox[email_field]"
               value="<?php echo esc_attr($fi['email_field']); ?>"
               placeholder="np. form-field-abc123" style="max-width:260px;">
        <div class="evo-desc">Używany do przycisku „Odpowiedz". Jeśli puste — auto-wykrywanie po formacie e-mail.</div>
    </div>

    <div id="evk-fields-config" data-nonce="<?php echo esc_attr($nonce); ?>">
        <?php if ($has_tbl): ?>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <button type="button" id="evk-load-fields" class="button">
                <span class="dashicons dashicons-update" style="font-size:14px;vertical-align:middle;line-height:1.6;"></span>
                Załaduj klucze pól z bazy
            </button>
            <span id="evk-fields-status" style="font-size:12px;color:#6b7280;"></span>
        </div>
        <div id="evk-fields-table"></div>
        <?php else: ?>
        <div class="evo-info-box" style="border-color:#e2e8f0;">
            <span class="dashicons dashicons-info"></span>
            <div>Konfiguracja pól dostępna po włączeniu zapisywania zgłoszeń w Bricks.</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Zachowaj istniejące etykiety jako hidden inputs (nadpisywane przez JS) -->
    <?php foreach ($fi['field_labels'] as $fk => $fl): ?>
    <input type="hidden" name="evk_forminbox[field_labels][<?php echo esc_attr($fk); ?>]" value="<?php echo esc_attr($fl); ?>" class="evk-saved-label" data-key="<?php echo esc_attr($fk); ?>">
    <?php endforeach; ?>
    <?php foreach ($fi['hidden_fields'] as $fk): ?>
    <input type="hidden" name="evk_forminbox[hidden_fields][]" value="<?php echo esc_attr($fk); ?>" class="evk-saved-hidden" data-key="<?php echo esc_attr($fk); ?>">
    <?php endforeach; ?>

    <div class="evo-save-bar" style="margin-top:24px;">
        <?php submit_button('Zapisz ustawienia', 'primary', 'submit', false); ?>
    </div>
</form>

<script>
(function($) {
    var NONCE   = <?php echo json_encode($nonce); ?>;
    var SAVED_LABELS  = <?php echo json_encode($fi['field_labels']); ?>;
    var SAVED_HIDDEN  = <?php echo json_encode($fi['hidden_fields']); ?>;

    document.getElementById('evk-load-fields') && document.getElementById('evk-load-fields').addEventListener('click', function() {
        $('#evk-fields-status').text('Ładowanie…');
        $.get(window.ajaxurl, { action: 'evk_inbox_field_keys', nonce: NONCE }, function(r) {
            if (!r.success) { $('#evk-fields-status').text('Błąd'); return; }
            var keys = r.data.keys;
            if (!keys.length) { $('#evk-fields-status').text('Brak kluczy pól.'); return; }
            $('#evk-fields-status').text(keys.length + ' kluczy wykrytych.');

            // Merge saved labels
            keys.forEach(function(k) {
                if (SAVED_LABELS[k.key]) k.label = SAVED_LABELS[k.key];
                if (SAVED_HIDDEN.includes(k.key)) k.hidden = true;
            });

            var html = '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
            html += '<thead><tr style="border-bottom:2px solid #e2e8f0;">';
            html += '<th style="text-align:left;padding:8px 10px;color:#6b7280;font-weight:600;width:200px;">Klucz pola</th>';
            html += '<th style="text-align:left;padding:8px 10px;color:#6b7280;font-weight:600;">Etykieta (wyświetlana)</th>';
            html += '<th style="text-align:center;padding:8px 10px;color:#6b7280;font-weight:600;width:80px;">Ukryj</th>';
            html += '</tr></thead><tbody>';

            keys.forEach(function(k) {
                var hiddenChecked = k.hidden ? 'checked' : '';
                html += `<tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 10px;font-family:monospace;font-size:12px;color:#475569;">${k.key}</td>
                    <td style="padding:6px 10px;">
                        <input type="text" name="evk_forminbox[field_labels][${k.key}]" value="${k.label.replace(/"/g,'&quot;')}"
                               placeholder="Automatyczna" style="width:100%;border:1px solid #d1d5db;border-radius:5px;padding:5px 8px;font-size:13px;">
                    </td>
                    <td style="padding:6px 10px;text-align:center;">
                        <input type="checkbox" name="evk_forminbox[hidden_fields][]" value="${k.key}" ${hiddenChecked}>
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';

            // Remove previously injected saved inputs (JS will replace via form fields)
            $('.evk-saved-label, .evk-saved-hidden').remove();

            $('#evk-fields-table').html(html);
        });
    });
})(jQuery);
</script>
