<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke ONE Newsletter — Główna zakładka (loader subtabów)
 */

$nl_opts   = get_option('evk_newsletter', []);
$nl_active = !empty($nl_opts['enabled']);
$subtab    = sanitize_key($_GET['subtab'] ?? 'lists');
$base      = admin_url('options-general.php?page=evoke-one&tab=newsletter');

$subtabs = [
    'lists'     => ['label' => 'Listy', 'icon' => 'dashicons-groups'],
    'templates' => ['label' => 'Szablony', 'icon' => 'dashicons-email-alt'],
    'campaigns' => ['label' => 'Kampanie', 'icon' => 'dashicons-megaphone'],
    'reports'   => ['label' => 'Raporty', 'icon' => 'dashicons-chart-bar'],
    'settings'  => ['label' => 'Ustawienia', 'icon' => 'dashicons-admin-generic'],
];

if (!array_key_exists($subtab, $subtabs)) $subtab = 'lists';

// Ostrzeżenie SMTP
$smtp_ok = evk_nl_smtp_is_configured();
?>

<div class="evk-nl-wrap">

    <?php if (!$smtp_ok): ?>
    <div class="notice notice-warning inline" style="margin:0 0 16px;">
        <p>
            <span class="dashicons dashicons-warning" style="color:#f59e0b;"></span>
            <strong>Newsletter:</strong> SMTP nie jest skonfigurowany — wysyłka maili nie będzie działać.
            <a href="<?php echo esc_url(admin_url('options-general.php?page=evoke-one&tab=narzedzia&subtab=smtp')); ?>">
                Przejdź do konfiguracji SMTP →
            </a>
        </p>
    </div>
    <?php endif; ?>

    <!-- Status card — spójny z innymi modułami Evoke ONE -->
    <div class="evo-status-card">
        <div class="evo-status-icon <?php echo $nl_active ? 'on' : 'off'; ?>">
            <span class="dashicons dashicons-email-alt" style="font-size:24px;width:24px;height:24px;line-height:1;"></span>
        </div>
        <div class="evo-status-text">
            <h3>Newsletter: <?php echo $nl_active ? 'WŁĄCZONY' : 'WYŁĄCZONY'; ?></h3>
            <p>Listy subskrybentów, szablony i kampanie email.</p>
        </div>
        <div class="evo-status-actions">
            <label class="evo-toggle">
                <input type="checkbox" id="evk-nl-toggle" data-option="evk_newsletter" data-field="enabled" value="1" <?php checked($nl_active); ?>>
                <span class="evo-slider"></span>
            </label>
        </div>
    </div>

    <?php if (!$nl_active): ?>
    <div style="padding:40px;text-align:center;background:#f8fafc;border-radius:10px;border:1px dashed #cbd5e1;">
        <span class="dashicons dashicons-email-alt" style="font-size:48px;width:48px;height:48px;color:#94a3b8;"></span>
        <p style="color:#64748b;margin:12px 0 0;">Włącz moduł Newsletter powyżej, aby zarządzać kampaniami email.</p>
    </div>
    <?php else: ?>

    <!-- Subtabs -->
    <div class="evk-subtabs" style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #e2e8f0;padding-bottom:0;">
        <?php foreach ($subtabs as $key => $st): ?>
        <a href="<?php echo esc_url(add_query_arg('subtab', $key, $base)); ?>"
           class="evk-subtab <?php echo $subtab === $key ? 'active' : ''; ?>"
           style="display:flex;align-items:center;gap:6px;padding:10px 18px;text-decoration:none;color:<?php echo $subtab === $key ? '#2563eb' : '#64748b'; ?>;border-bottom:2px solid <?php echo $subtab === $key ? '#2563eb' : 'transparent'; ?>;margin-bottom:-2px;font-weight:<?php echo $subtab === $key ? '600' : '400'; ?>;transition:all .15s;">
            <span class="dashicons <?php echo esc_attr($st['icon']); ?>" style="font-size:16px;width:16px;height:16px;"></span>
            <?php echo esc_html($st['label']); ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Subtab content -->
    <?php
    $subtab_file = EVOKE_ONE_DIR . 'includes/admin/newsletter/tab-' . $subtab . '.php';
    if (file_exists($subtab_file)) {
        require $subtab_file;
    } else {
        echo '<p style="color:#dc2626;">Błąd: plik zakładki nie istnieje (' . esc_html($subtab_file) . ').</p>';
    }
    ?>

    <?php endif; // module active ?>

</div>

<?php
