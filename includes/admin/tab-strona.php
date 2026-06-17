<?php
if (!defined('ABSPATH')) exit;
/**
 * Evoke ONE — Zakładka: Strona
 * tab-seo.php zawiera własny system podzakładek (Meta SEO / Sitemap / Schema / OpenGraph)
 * — ładujemy go bezpośrednio bez dodatkowej nawigacji.
 */

$sitemap_settings = tl_get_sitemap_settings();
$sitemap_posts    = get_posts([
    'post_type'      => ['page', 'post'],
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
]);
$excluded_ids = array_map('absint', (array)($sitemap_settings['excluded_ids'] ?? []));
$base         = admin_url('options-general.php?page=evoke-one');

require EVOKE_ONE_DIR . 'includes/admin/tab-seo.php';
