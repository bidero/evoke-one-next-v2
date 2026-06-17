<?php
if (!defined('ABSPATH')) exit;


/**
 * Evoke One — Moduł Snippetów PHP
 * Oparty na SNN Custom Codes, zaadaptowany do systemu Evoke One.
 *
 * Kod snippetów przechowywany w CPT evk_code_snippet (private posts)
 * dzięki czemu WP automatycznie obsługuje rewizje.
 * Wykonywanie przez eval() z pełną obsługą błędów i auto-wyłączaniem przy fatalnych.
 */

// Stała awaryjna — dodaj do wp-config.php żeby wyłączyć snippety bez dostępu do admina
// define('EVK_CODE_DISABLE', true);

define('EVK_SNIPPETS_LOG_OPTION',       'evk_snippets_error_log');
define('EVK_SNIPPETS_MAX_LOG',          150);
define('EVK_SNIPPETS_FATAL_TRANSIENT',  'evk_snippets_fatal_notice');
define('EVK_SNIPPETS_ENABLED_OPTION',   'evk_snippets_enabled');
define('EVK_SNIPPETS_ADVANCED_ENABLED', 'evk_snippets_advanced_enabled');
define('EVK_SNIPPETS_ADVANCED_CONTENT', 'evk_snippets_advanced_content');

// Definicje zakładek snippetów
function evk_snippets_defs(): array {
    return [
        'frontend' => [
            'title'    => 'Frontend &lt;head&gt;',
            'slug'     => 'evk-snippet-frontend-head',
            'field_id' => 'evk_frontend_code',
            'desc'     => 'PHP lub HTML wykonywany w <code>&lt;head&gt;</code> na froncie. Można używać tagów <code>&lt;?php ?&gt;</code>.',
        ],
        'footer' => [
            'title'    => 'Frontend footer',
            'slug'     => 'evk-snippet-footer',
            'field_id' => 'evk_footer_code',
            'desc'     => 'PHP lub HTML wykonywany przed <code>&lt;/body&gt;</code> na froncie.',
        ],
        'admin' => [
            'title'    => 'Admin &lt;head&gt;',
            'slug'     => 'evk-snippet-admin-head',
            'field_id' => 'evk_admin_code',
            'desc'     => 'PHP lub HTML wykonywany w <code>&lt;head&gt;</code> panelu admina.',
        ],
        'functions' => [
            'title'    => 'PHP (functions.php)',
            'slug'     => 'evk-snippet-functions-php',
            'field_id' => 'evk_functions_code',
            'desc'     => 'PHP wykonywany natychmiast przy ładowaniu — jak <code>functions.php</code>. Błędy tutaj mogą zepsuć stronę.',
        ],
    ];
}


// =========================================================================
// REJESTRACJA USTAWIEŃ
// =========================================================================

add_action('admin_init', function () {
    register_setting('evk_snippets_settings', EVK_SNIPPETS_ENABLED_OPTION,   ['sanitize_callback' => 'absint']);
    register_setting('evk_snippets_settings', EVK_SNIPPETS_ADVANCED_ENABLED, ['sanitize_callback' => 'absint']);
});

// =========================================================================
