<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke ONE — Admin helpers
 * Funkcje pomocnicze używane przez wszystkie zakładki.
 */

/**
 * Renderuje nawigację podzakładek — spójny styl w całym panelu.
 */
function evoke_one_render_subtabs(array $subs, string $active, string $base_url): void {
    echo '<div class="evk-subtabs">';
    foreach ($subs as $key => $s) {
        $is_active = ($active === $key);
        printf(
            '<a href="%s" class="evk-subtab%s">',
            esc_url(add_query_arg('sub', $key, $base_url)),
            $is_active ? ' is-active' : ''
        );
        printf(
            '<span class="dashicons %s"></span>%s</a>',
            esc_attr($s['icon']),
            esc_html($s['label'])
        );
    }
    echo '</div>';
}
