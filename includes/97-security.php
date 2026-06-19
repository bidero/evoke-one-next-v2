<?php
if (!defined('ABSPATH')) exit;
/**
 * Evoke ONE — Czyszczenie (XML-RPC / RSS). Osobna opcja evk_cleanup,
 * niezależna od systemu evk_security (Limit logowań / REST / Ochrona WP).
 */

function evk_cleanup_opts(): array {
    return array_merge(['disable_xmlrpc' => 0, 'remove_rss' => 0], (array) get_option('evk_cleanup', []));
}

add_filter('xmlrpc_enabled', function ($enabled) {
    return !empty(evk_cleanup_opts()['disable_xmlrpc']) ? false : $enabled;
});

// Twarda blokada xmlrpc.php. Filtr xmlrpc_enabled wyłącza tylko metody POST
// wymagające auth — sam plik nadal odpowiada, a GET i tak zwraca 405 z rdzenia
// WP niezależnie od filtra. 403 = realna blokada pliku, testowalna w obu pozycjach.
// XMLRPC_REQUEST jest definiowane w xmlrpc.php PRZED wp-load.php, więc łapie się tu.
add_action('plugins_loaded', function () {
    if (!defined('XMLRPC_REQUEST') || !XMLRPC_REQUEST) return;
    if (empty(evk_cleanup_opts()['disable_xmlrpc'])) return;
    status_header(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('XML-RPC services are disabled.');
});

add_action('init', function () {
    if (empty(evk_cleanup_opts()['remove_rss'])) return;
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'wlwmanifest_link');
});
