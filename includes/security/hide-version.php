<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke One — Security: Ukryj wersję WordPress
 * Usuwa numer wersji z HTML, RSS, nagłówków HTTP i query stringów assetów.
 */

add_action('after_setup_theme', function () {
    if (empty(evk_security_get()['hide_wp_version'])) return;

    remove_action('wp_head', 'wp_generator');
    add_filter('the_generator',         '__return_empty_string');
    add_filter('update_right_now_text', '__return_empty_string');

    add_filter('wp_headers', function (array $headers): array {
        unset($headers['X-Powered-By']);
        return $headers;
    });

    add_filter('style_loader_src',  'evk_remove_ver_from_url', 9999);
    add_filter('script_loader_src', 'evk_remove_ver_from_url', 9999);
});

function evk_remove_ver_from_url(string $src): string {
    if (strpos($src, 'ver=') === false) return $src;
    return remove_query_arg('ver', $src);
}
