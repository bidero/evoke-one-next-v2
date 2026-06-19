<?php
if (!defined('ABSPATH')) exit;
/**
 * Evoke ONE — Bezpieczeństwo / czyszczenie (port pomysłów SNN jako evk)
 */

function evk_security_opts(): array {
    return array_merge(['disable_xmlrpc' => 0, 'remove_rss' => 0], (array) get_option('evk_security', []));
}

// Wyłącz XML-RPC
add_filter('xmlrpc_enabled', function ($enabled) {
    return !empty(evk_security_opts()['disable_xmlrpc']) ? false : $enabled;
});

// Usuń linki feedów RSS z <head>
add_action('init', function () {
    if (empty(evk_security_opts()['remove_rss'])) return;
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'wlwmanifest_link');
});
