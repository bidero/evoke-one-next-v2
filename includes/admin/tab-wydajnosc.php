<?php
if (!defined('ABSPATH')) exit;
/**
 * Evoke ONE — Zakładka: Wydajność
 */

$sub      = sanitize_key($_GET['sub'] ?? 'maintenance');
$base_url = add_query_arg('tab', 'wydajnosc', admin_url('options-general.php?page=evoke-one'));

$subs = [
    'maintenance' => ['label' => 'Konserwacja',  'icon' => 'dashicons-admin-tools'],
    'parallax'    => ['label' => 'Parallax',      'icon' => 'dashicons-image-flip-vertical'],
    'darkmode'    => ['label' => 'Dark Mode',     'icon' => 'dashicons-lightbulb'],
    'cursor'      => ['label' => 'Kursor',        'icon' => 'dashicons-arrow-up-alt'],
    'lenis'       => ['label' => 'Smooth Scroll', 'icon' => 'dashicons-sort'],
    'a11y'        => ['label' => 'Dostępność',    'icon' => 'dashicons-universal-access'],
    'elementy'    => ['label' => 'Elementy Bricks', 'icon' => 'dashicons-screenoptions'],
];

if (!array_key_exists($sub, $subs)) $sub = 'maintenance';

evoke_one_render_subtabs($subs, $sub, $base_url);

$pages            = get_pages(['post_status' => 'publish', 'sort_column' => 'post_title']);
$selected_page_id = (int) get_option('maintenance_page_id', 0);
$status           = (int) get_option('maintenance_mode', 0);
$bypass_pass      = get_option('maintenance_bypass_password', '');
$bypass_hours     = get_option('maintenance_bypass_hours', 1);
$excluded_paths   = get_option('maintenance_excluded_paths', "/login\n/logmein");
$selected_page_title = '—';
if ($selected_page_id) {
    $p = get_post($selected_page_id);
    if ($p) $selected_page_title = $p->post_title;
}
$parallax_value = evk_get_parallax_value();
$scale_value    = evk_get_parallax_scale();

$sub_file = EVOKE_ONE_DIR . 'includes/admin/tab-' . $sub . '.php';
if (file_exists($sub_file)) require $sub_file;
