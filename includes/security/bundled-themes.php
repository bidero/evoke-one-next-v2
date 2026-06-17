<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke One — Security: Wyłącz aktualizację motywów dołączonych do WP (Twenty*)
 * Zapobiega automatycznej instalacji/aktualizacji domyślnych motywów podczas aktualizacji rdzenia.
 */

add_filter('wp_auto_update_send_request_body', function (array $body): array {
    if (!empty(evk_security_get()['disable_bundled_themes'])) {
        $body['update_themes_bundled'] = false;
    }
    return $body;
});

add_filter('upgrader_pre_install', function ($response, array $hook_extra) {
    if (empty(evk_security_get()['disable_bundled_themes'])) return $response;

    if (($hook_extra['type'] ?? '') === 'theme' && ($hook_extra['action'] ?? '') === 'update') {
        $bundled = [
            'twentytwentyfive', 'twentytwentyfour', 'twentytwentythree',
            'twentytwentytwo',  'twentytwentyone',  'twentytwenty',
        ];
        if (in_array($hook_extra['theme'] ?? '', $bundled, true)) {
            return new WP_Error('evk_skip_bundled',
                __('Aktualizacja motywu domyślnego zablokowana przez Evoke One.', 'evoke-one'));
        }
    }
    return $response;
}, 10, 2);
