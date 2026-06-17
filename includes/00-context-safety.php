<?php
if (!defined('ABSPATH')) exit;

/**
 * EVOKE Tłumaczenia - context and safety helpers
 */

// ====================================================================
// 0. CONTEXT / SAFETY
// ====================================================================
function tl_is_bricks_editor(): bool {
    return isset($_GET['bricks']) && !isset($_GET['bricks_preview']) && !isset($_GET['preview']);
}
function tl_is_bricks_preview(): bool {
    return isset($_GET['bricks_preview']) ||
        (isset($_GET['bricks']) && isset($_GET['preview'])) ||
        (defined('BRICKS_IS_BUILDER_IFRAME') && BRICKS_IS_BUILDER_IFRAME);
}
function tl_is_wp_admin(): bool { return is_admin(); }
