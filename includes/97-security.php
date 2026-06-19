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

add_action('init', function () {
    if (empty(evk_cleanup_opts()['remove_rss'])) return;
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'wlwmanifest_link');
});
