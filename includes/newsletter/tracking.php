<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke ONE Newsletter — Tracking & Unsubscribe
 *
 * Używa parse_request dla pełnej izolacji od motywów i Bricks Buildera.
 *
 * URL-e (ładne po flush rewrite):
 * /nl/open/{token}/
 * /nl/click/{token}/?url={encoded_url}
 * /nl/unsub/{token}/
 *
 * Fallback (zawsze działa bez flush):
 * /?evk_nl=open&evk_nl_token={token}
 * /?evk_nl=click&evk_nl_token={token}&url={encoded_url}
 * /?evk_nl=unsub&evk_nl_token={token}
 */

// =========================================================================
// REWRITE RULES
// =========================================================================

add_action('init', function (): void {
    add_rewrite_rule(
        '^nl/open/([a-zA-Z0-9]+)/?$',
        'index.php?evk_nl=open&evk_nl_token=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^nl/click/([a-zA-Z0-9]+)/?$',
        'index.php?evk_nl=click&evk_nl_token=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^nl/unsub/([a-zA-Z0-9]+)/?$',
        'index.php?evk_nl=unsub&evk_nl_token=$matches[1]',
        'top'
    );
    // View in browser: /nl/view/{campaign_id}/ lub /nl/view/{campaign_id}/{token}/
    add_rewrite_rule(
        '^nl/view/([0-9]+)/([a-zA-Z0-9]+)/?$',
        'index.php?evk_nl=view&evk_nl_campaign=$matches[1]&evk_nl_token=$matches[2]',
        'top'
    );
    add_rewrite_rule(
        '^nl/view/([0-9]+)/?$',
        'index.php?evk_nl=view&evk_nl_campaign=$matches[1]',
        'top'
    );
}, 1);

add_filter('query_vars', function (array $vars): array {
    $vars[] = 'evk_nl';
    $vars[] = 'evk_nl_token';
    $vars[] = 'evk_nl_campaign';
    return $vars;
});

// =========================================================================
// DISPATCHER — parse_request omija motyw i błędy 404
// =========================================================================

add_action('parse_request', 'evk_nl_dispatcher', 1);

function evk_nl_dispatcher(\WP $wp): void {
    $action = !empty($wp->query_vars['evk_nl'])
        ? sanitize_key($wp->query_vars['evk_nl'])
        : sanitize_key($_GET['evk_nl'] ?? '');
    if (empty($action)) return;

    $token = !empty($wp->query_vars['evk_nl_token'])
        ? sanitize_text_field($wp->query_vars['evk_nl_token'])
        : sanitize_text_field($_GET['evk_nl_token'] ?? '');
    if (empty($token)) return;

    switch ($action) {
        case 'open':  evk_nl_handle_open($token);  break;
        case 'click': evk_nl_handle_click($token); break;
        case 'unsub': evk_nl_handle_unsub($token); break;
        case 'view':
            $campaign_id = (int) (!empty($wp->query_vars['evk_nl_campaign'])
                ? $wp->query_vars['evk_nl_campaign']
                : ($_GET['evk_nl_campaign'] ?? 0));
            evk_nl_handle_view($campaign_id, $token);
            break;
    }
}

// =========================================================================
// OPEN TRACKING — pixel GIF
// =========================================================================

function evk_nl_handle_open(string $token): void {
    $sub = evk_nl_get_subscriber_by_token($token);

    if ($sub) {
        global $wpdb;
        $q = evk_nl_table('queue');

        $updated = $wpdb->query($wpdb->prepare(
            "UPDATE $q
             SET status = 'opened', opened_at = NOW()
             WHERE subscriber_id = %d
               AND status IN ('sent', 'pending')
               AND opened_at IS NULL",
            $sub['id']
        ));

        $queue_row = $wpdb->get_row($wpdb->prepare(
            "SELECT campaign_id FROM $q
             WHERE subscriber_id = %d
             ORDER BY id DESC LIMIT 1",
            $sub['id']
        ), ARRAY_A);

        if ($queue_row) {
            evk_nl_log(
                (int) $queue_row['campaign_id'],
                'open',
                (int) $sub['id'],
                ['first' => (bool) $updated]
            );
        }
    }

    if (ob_get_level()) ob_end_clean();

    header('Content-Type: image/gif');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-Length: 43');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}

// =========================================================================
// CLICK TRACKING — redirect
// =========================================================================

function evk_nl_handle_click(string $token): void {
    $target_url = esc_url_raw(wp_unslash($_GET['url'] ?? ''));
    $sub        = evk_nl_get_subscriber_by_token($token);

    if ($sub) {
        global $wpdb;
        $q = evk_nl_table('queue');

        $queue_row = $wpdb->get_row($wpdb->prepare(
            "SELECT campaign_id FROM $q
             WHERE subscriber_id = %d
             ORDER BY id DESC LIMIT 1",
            $sub['id']
        ), ARRAY_A);

        if ($queue_row) {
            evk_nl_log(
                (int) $queue_row['campaign_id'],
                'click',
                (int) $sub['id'],
                ['url' => $target_url]
            );
            $wpdb->query($wpdb->prepare(
                "UPDATE $q
                 SET status = 'clicked'
                 WHERE subscriber_id = %d
                   AND campaign_id = %d
                   AND status IN ('sent', 'opened')",
                $sub['id'],
                $queue_row['campaign_id']
            ));
        }
    }

    $redirect = ($target_url && wp_http_validate_url($target_url)) ? $target_url : home_url();
    wp_redirect($redirect, 302);
    exit;
}

// =========================================================================
// UNSUBSCRIBE — strona HTML
// =========================================================================

function evk_nl_handle_unsub(string $token): void {
    $sub     = evk_nl_get_subscriber_by_token($token);
    $success = false;

    if ($sub) {
        if ((int) $sub['status'] === 1) {
            $success = evk_nl_unsubscribe_by_token($token);
            if ($success) {
                global $wpdb;
                $q = evk_nl_table('queue');
                $queue_row = $wpdb->get_row($wpdb->prepare(
                    "SELECT campaign_id FROM $q
                     WHERE subscriber_id = %d
                     ORDER BY id DESC LIMIT 1",
                    $sub['id']
                ), ARRAY_A);
                if ($queue_row) {
                    evk_nl_log((int) $queue_row['campaign_id'], 'unsubscribe', (int) $sub['id']);
                }
            }
        } else {
            $success = true; // Już wypisany
        }
    }

    $email = $sub ? esc_html($sub['email']) : '';

    while (ob_get_level()) ob_end_clean();

    http_response_code(200);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    echo evk_nl_unsubscribe_html($success, $email);
    exit;
}

function evk_nl_unsubscribe_html(bool $success, string $email): string {
    $icon  = $success ? '✅' : '❌';
    $title = $success ? 'Wypisano z newslettera' : 'Nieprawidłowy link';

    if ($success && $email) {
        $msg = 'Adres <strong>' . esc_html($email) . '</strong> został wypisany z naszego newslettera.';
    } elseif ($success) {
        $msg = 'Zostałeś wypisany z naszego newslettera.';
    } else {
        $msg = 'Link jest nieprawidłowy lub już wygasł.';
    }

    $home      = esc_url(home_url());
    $site_name = esc_html(get_bloginfo('name'));

    return <<<HTML
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$title} — {$site_name}</title>
<style>
*,*::before,*::after{box-sizing:border-box;}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f1f5f9;}
.card{background:#fff;border-radius:16px;padding:52px 44px;max-width:500px;width:90%;text-align:center;box-shadow:0 8px 40px rgba(0,0,0,.10);}
.icon{font-size:56px;line-height:1;margin-bottom:20px;}
h1{font-size:22px;font-weight:700;color:#1e293b;margin:0 0 12px;}
p{color:#64748b;font-size:15px;line-height:1.7;margin:0 0 28px;}
strong{color:#1e293b;}
.btn{display:inline-block;background:#2563eb;color:#fff;padding:13px 32px;border-radius:10px;text-decoration:none;font-weight:600;font-size:15px;transition:background .15s;}
.btn:hover{background:#1d4ed8;}
.footer{margin-top:28px;font-size:12px;color:#94a3b8;}
</style>
</head>
<body>
<div class="card">
    <div class="icon">{$icon}</div>
    <h1>{$title}</h1>
    <p>{$msg}</p>
    <a href="{$home}" class="btn">Wróć na stronę główną</a>
    <div class="footer">{$site_name}</div>
</div>
</body>
</html>
HTML;
}

// =========================================================================
// VIEW IN BROWSER
// =========================================================================

function evk_nl_handle_view(int $campaign_id, string $token = ''): void {
    if (!$campaign_id) {
        wp_die('Nieprawidłowy link.', 404);
    }

    $campaign = evk_nl_get_campaign($campaign_id);
    if (!$campaign) {
        wp_die('Kampania nie istnieje.', 404);
    }

    $template = evk_nl_get_template((int) $campaign['template_id']);
    if (!$template) {
        wp_die('Brak szablonu kampanii.', 404);
    }

    // Pobierz dane subskrybenta (jeśli token podany)
    $subscriber = $token ? evk_nl_get_subscriber_by_token($token) : null;

    // Merge tagi
    $unsub_url = $subscriber ? evk_nl_unsubscribe_url($subscriber['token']) : '#';
    $fields    = $subscriber ? (json_decode($subscriber['fields_json'] ?? '{}', true) ?: []) : [];

    $merge = array_merge([
        '{email}'            => $subscriber ? $subscriber['email'] : 'twoj@email.com',
        '{unsubscribe_url}'  => $unsub_url,
        '{site_name}'        => get_bloginfo('name'),
        '{site_url}'         => preg_replace('#^https?://#', '', home_url()),
        '{site_url_full}'     => home_url(),
        '{unsubscribe_url_plain}' => preg_replace('#^https?://#', '', $unsub_url),
        '{view_in_browser}'  => '', // Wyczyść w podglądzie
    ], evk_nl_fields_to_merge_tags($fields));

    $subject = evk_nl_replace_merge_tags($template['subject'], $merge);
    $body    = evk_nl_replace_merge_tags($template['body_html'], $merge);

    // Tracking — nie przepisuj linków w podglądzie przeglądarkowym
    // (kliknięcie w podglądzie nie powinno liczyć się jako tracking)

    $site_name = esc_html(get_bloginfo('name'));
    $subject_e = esc_html($subject);

    while (ob_get_level()) ob_end_clean();
    http_response_code(200);
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Robots-Tag: noindex, nofollow');

    echo <<<HTML
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>{$subject_e} — {$site_name}</title>
<style>
body{margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;}
.evk-nl-browser-bar{background:#1e293b;color:#94a3b8;font-size:11px;padding:8px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;}
.evk-nl-browser-bar strong{color:#e2e8f0;}
.evk-nl-content{max-width:680px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);}
</style>
</head>
<body>
<div class="evk-nl-browser-bar">
    <span><strong>{$subject_e}</strong> — {$site_name}</span>
    <span>Wiadomość email wyświetlona w przeglądarce</span>
</div>
<div class="evk-nl-content">
    {$body}
</div>
</body>
</html>
HTML;
    exit;
}

// =========================================================================
// URL HELPERS
// =========================================================================

function evk_nl_view_url(int $campaign_id, string $token = ''): string {
    $rules = get_option('rewrite_rules', []);
    $has_rewrite = !empty($rules) && isset($rules['^nl/view/([0-9]+)/([a-zA-Z0-9]+)/?$']);

    if ($has_rewrite) {
        $base = home_url('/nl/view/' . $campaign_id . '/');
        return $token ? $base . $token . '/' : $base;
    }
    $args = ['evk_nl' => 'view', 'evk_nl_campaign' => $campaign_id];
    if ($token) $args['evk_nl_token'] = $token;
    return add_query_arg($args, home_url('/'));
}

function evk_nl_open_url(string $token): string {
    $rules = get_option('rewrite_rules', []);
    if (!empty($rules) && isset($rules['^nl/open/([a-zA-Z0-9]+)/?$'])) {
        return home_url('/nl/open/' . $token . '/');
    }
    return add_query_arg(['evk_nl' => 'open', 'evk_nl_token' => $token], home_url('/'));
}

function evk_nl_click_url(string $token, string $target): string {
    $rules = get_option('rewrite_rules', []);
    if (!empty($rules) && isset($rules['^nl/click/([a-zA-Z0-9]+)/?$'])) {
        return home_url('/nl/click/' . $token . '/') . '?url=' . rawurlencode($target);
    }
    return add_query_arg(['evk_nl' => 'click', 'evk_nl_token' => $token, 'url' => $target], home_url('/'));
}

function evk_nl_unsubscribe_url(string $token): string {
    $rules = get_option('rewrite_rules', []);
    if (!empty($rules) && isset($rules['^nl/unsub/([a-zA-Z0-9]+)/?$'])) {
        return home_url('/nl/unsub/' . $token . '/');
    }
    return add_query_arg(['evk_nl' => 'unsub', 'evk_nl_token' => $token], home_url('/'));
}

// =========================================================================
// FLUSH REWRITE
// =========================================================================

// Wersja reguł rewrite — zmień gdy dodajesz nowe reguły
define('EVK_NL_REWRITE_VERSION', '1.2');

function evk_nl_flush_rewrite(): void {
    add_rewrite_rule('^nl/open/([a-zA-Z0-9]+)/?$',  'index.php?evk_nl=open&evk_nl_token=$matches[1]',  'top');
    add_rewrite_rule('^nl/click/([a-zA-Z0-9]+)/?$', 'index.php?evk_nl=click&evk_nl_token=$matches[1]', 'top');
    add_rewrite_rule('^nl/unsub/([a-zA-Z0-9]+)/?$', 'index.php?evk_nl=unsub&evk_nl_token=$matches[1]', 'top');
    add_rewrite_rule('^nl/view/([0-9]+)/([a-zA-Z0-9]+)/?$', 'index.php?evk_nl=view&evk_nl_campaign=$matches[1]&evk_nl_token=$matches[2]', 'top');
    add_rewrite_rule('^nl/view/([0-9]+)/?$', 'index.php?evk_nl=view&evk_nl_campaign=$matches[1]', 'top');
    flush_rewrite_rules();
    update_option('evk_nl_rewrite_version', EVK_NL_REWRITE_VERSION);
}

// Auto-flush gdy wersja reguł jest nieaktualna
add_action('init', function (): void {
    if (get_option('evk_nl_rewrite_version') !== EVK_NL_REWRITE_VERSION) {
        evk_nl_flush_rewrite();
    }
}, 99);
