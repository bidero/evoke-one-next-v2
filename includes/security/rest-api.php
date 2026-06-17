<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke One — Security: Blokowanie REST API
 * Opcja 1: zablokuj cały REST API dla gości (jeden toggle)
 * Opcja 2: zablokuj wybrane endpointy dla gości
 */

// =========================================================================
// SANITIZE — dodane do evk_security_sanitize w settings.php
// =========================================================================

add_filter('evk_security_sanitize_extra', function (array $clean, array $input): array {
    $clean['rest_block_all']       = !empty($input['rest_block_all'])       ? 1 : 0;
    $clean['disabled_rest_endpoints'] = isset($input['disabled_rest_endpoints']) && is_array($input['disabled_rest_endpoints'])
        ? array_map('sanitize_text_field', $input['disabled_rest_endpoints'])
        : [];
    return $clean;
}, 10, 2);

// =========================================================================
// BLOKOWANIE
// =========================================================================

// Używamy rest_pre_dispatch — odpala się dla każdego requestu REST po routingu
// Działa też dla /wp-json (index) i wszystkich endpointów
add_filter('rest_pre_dispatch', function ($result, $server, $request) {
    // Pomiń zalogowanych
    if (is_user_logged_in()) return $result;

    $s = evk_security_get();

    // Opcja 1: zablokuj cały REST API
    if (!empty($s['rest_block_all'])) {
        return new WP_Error(
            'evk_rest_disabled',
            'Access Denied',
            ['status' => 401]
        );
    }

    // Opcja 2: zablokuj wybrane endpointy
    $disabled = $s['disabled_rest_endpoints'] ?? [];
    if (empty($disabled)) return $result;

    $route = $request->get_route();

    foreach ($disabled as $pattern) {
        // Exact match
        if ($route === $pattern) {
            return new WP_Error(
                'evk_rest_forbidden',
                'Access Denied',
                ['status' => 401]
            );
        }
        // Pattern match — endpointy z parametrami np. /wp/v2/posts/(?P<id>\d+)
        if (!empty($pattern) && @preg_match('#^' . $pattern . '$#', $route)) {
            return new WP_Error(
                'evk_rest_forbidden',
                'Access Denied',
                ['status' => 401]
            );
        }
    }

    return $result;
}, 10, 3);

// =========================================================================
// HELPER — pobierz endpointy pogrupowane po namespace (dla UI ustawień)
// =========================================================================

function evk_rest_get_endpoints(): array {
    $server  = rest_get_server();
    $routes  = $server->get_routes();
    $grouped = [];

    foreach ($routes as $route => $route_data) {
        $namespace = 'core';
        if (preg_match('#^/([^/]+)/#', $route, $m)) {
            $namespace = $m[1];
        }

        $methods = [];
        foreach ($route_data as $handler) {
            if (isset($handler['methods'])) {
                $m = is_array($handler['methods'])
                    ? array_keys($handler['methods'])
                    : [$handler['methods']];
                $methods = array_merge($methods, $m);
            }
        }

        $grouped[$namespace][] = [
            'route'   => $route,
            'methods' => array_unique($methods),
        ];
    }

    ksort($grouped);
    return $grouped;
}
