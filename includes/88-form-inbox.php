<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke ONE — Moduł Skrzynki Formularzy Bricks (Form Inbox)
 */

define('EVK_INBOX_OPTION',      'evk_forminbox');
define('EVK_INBOX_READ_OPTION', 'evk_forminbox_read');

// =========================================================================
// USTAWIENIA
// =========================================================================

function evk_inbox_defaults(): array {
    return [
        'enabled'       => 0,
        'menu_label'    => 'Wiadomości',
        'menu_icon'     => 'dashicons-email-alt',
        'menu_position' => 25,
        'per_page'      => 25,
        'field_labels'  => [],   // ['form-field-abc' => 'Imię']
        'hidden_fields' => [],   // ['form-field-xyz']
        'email_field'   => '',   // klucz pola z e-mailem (auto-detect jeśli puste)
    ];
}

function evk_inbox_get_settings(): array {
    return wp_parse_args(get_option(EVK_INBOX_OPTION, []), evk_inbox_defaults());
}

function evk_inbox_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'bricks_form_submissions';
}

function evk_inbox_table_exists(): bool {
    global $wpdb;
    return (bool) $wpdb->get_var("SHOW TABLES LIKE '" . evk_inbox_table() . "'");
}

// =========================================================================
// REJESTRACJA USTAWIEŃ
// =========================================================================

add_action('admin_init', function () {
    register_setting(EVK_INBOX_OPTION . '_group', EVK_INBOX_OPTION, [
        'sanitize_callback' => 'evk_inbox_sanitize_settings',
    ]);
});

function evk_inbox_sanitize_settings($input): array {
    $d = evk_inbox_defaults();
    $c = [];
    $c['enabled']       = !empty($input['enabled']) ? 1 : 0;
    $c['menu_label']    = sanitize_text_field($input['menu_label']    ?? $d['menu_label'])    ?: $d['menu_label'];
    $c['menu_icon']     = sanitize_text_field($input['menu_icon']     ?? $d['menu_icon'])     ?: $d['menu_icon'];
    $c['menu_position'] = max(1, min(100, intval($input['menu_position'] ?? 25)));
    $c['per_page']      = max(5, min(100, intval($input['per_page']   ?? 25)));
    $c['email_field']   = sanitize_key($input['email_field'] ?? '');

    $labels = [];
    if (!empty($input['field_labels']) && is_array($input['field_labels'])) {
        foreach ($input['field_labels'] as $k => $v) {
            $k = sanitize_key($k); $v = sanitize_text_field($v);
            if ($k && $v) $labels[$k] = $v;
        }
    }
    $c['field_labels'] = $labels;

    $hidden = [];
    if (!empty($input['hidden_fields']) && is_array($input['hidden_fields'])) {
        foreach ($input['hidden_fields'] as $k) {
            $k = sanitize_key($k);
            if ($k) $hidden[] = $k;
        }
    }
    $c['hidden_fields'] = $hidden;
    return $c;
}

// =========================================================================
// REJESTRACJA MENU
// =========================================================================

add_action('admin_menu', function () {
    $s = evk_inbox_get_settings();
    if (empty($s['enabled'])) return;

    add_menu_page(
        $s['menu_label'],
        $s['menu_label'],
        'manage_options',
        'evk-form-inbox',
        'evk_inbox_render_page',
        $s['menu_icon'],
        (int) $s['menu_position']
    );
});

add_action('admin_enqueue_scripts', function (string $hook) {
    if (strpos($hook, 'evk-form-inbox') === false) return;
    // Admin.js/css dla evo-toggle i evo-status-card na stronie ustawień
    // (nie jest potrzebny na stronie inbox — ma własny CSS/JS)
});

function evk_inbox_render_page(): void {
    if (!current_user_can('manage_options')) return;
    require EVOKE_ONE_DIR . 'includes/admin/forminbox-page.php';
}

// =========================================================================
// HELPERY
// =========================================================================

function evk_inbox_get_read(): array {
    $r = get_option(EVK_INBOX_READ_OPTION, []);
    return is_array($r) ? array_map('intval', $r) : [];
}

function evk_inbox_mark_read(array $ids): void {
    $read = evk_inbox_get_read();
    $read = array_values(array_unique(array_merge($read, array_map('intval', $ids))));
    if (count($read) > 10000) $read = array_slice($read, -10000);
    update_option(EVK_INBOX_READ_OPTION, $read, false);
}

function evk_inbox_mark_unread(array $ids): void {
    $read = evk_inbox_get_read();
    $ids  = array_map('intval', $ids);
    $read = array_values(array_diff($read, $ids));
    update_option(EVK_INBOX_READ_OPTION, $read, false);
}

function evk_inbox_format_date(string $dt): string {
    $ts   = strtotime($dt);
    $diff = time() - $ts;
    if ($diff < 60)        return 'Przed chwilą';
    if ($diff < 3600)      return round($diff / 60) . ' min temu';
    if ($diff < 86400)     return round($diff / 3600) . ' godz. temu';
    if ($diff < 86400 * 2) return 'Wczoraj ' . date('H:i', $ts);
    if ($diff < 86400 * 7) return date('j M', $ts);
    return date('j M Y', $ts);
}

function evk_inbox_field_label(string $key, array $s): string {
    if (!empty($s['field_labels'][$key])) return $s['field_labels'][$key];
    $label = preg_replace('/^form-field-/', '', $key);
    return ucwords(str_replace(['-', '_'], ' ', $label));
}

function evk_inbox_get_preview(array $fields): string {
    foreach ($fields as $v) {
        if (is_string($v) && strlen(trim($v)) > 0) {
            return mb_substr(strip_tags($v), 0, 100);
        }
    }
    return '—';
}

function evk_inbox_find_email(array $fields, string $forced_key = ''): string {
    if ($forced_key && isset($fields[$forced_key])) {
        $v = trim((string) $fields[$forced_key]);
        if (is_email($v)) return $v;
    }
    foreach ($fields as $v) {
        if (is_string($v) && is_email(trim($v))) return trim($v);
    }
    return '';
}

function evk_inbox_get_name(array $fields): string {
    $name_hints = ['name', 'imie', 'imię', 'nazwisko', 'fullname', 'full_name', 'first_name'];
    foreach ($fields as $k => $v) {
        if (!is_string($v) || !trim($v)) continue;
        foreach ($name_hints as $h) {
            if (stripos($k, $h) !== false && strlen($v) < 100 && !is_email($v)) {
                return sanitize_text_field($v);
            }
        }
    }
    foreach ($fields as $v) {
        if (is_string($v) && trim($v) && strlen($v) < 80 && !is_email($v)) {
            return sanitize_text_field($v);
        }
    }
    return 'Anonimowy';
}

// =========================================================================
// AJAX: Lista formularzy (z liczbą nieprzeczytanych)
// =========================================================================

add_action('wp_ajax_evk_inbox_forms', function () {
    check_ajax_referer('evk_inbox_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    if (!evk_inbox_table_exists()) wp_send_json_error('no_table');

    global $wpdb;
    $table = evk_inbox_table();
    $read  = evk_inbox_get_read();

    $rows = $wpdb->get_results("SELECT form_id, COUNT(*) as cnt FROM {$table} GROUP BY form_id ORDER BY cnt DESC");

    $forms = []; $all_cnt = 0; $all_unread = 0;
    foreach ($rows as $r) {
        $ids    = array_map('intval', $wpdb->get_col($wpdb->prepare("SELECT id FROM {$table} WHERE form_id = %s", $r->form_id)));
        $unread = count(array_diff($ids, $read));
        $forms[]     = ['form_id' => $r->form_id, 'count' => (int)$r->cnt, 'unread' => $unread];
        $all_cnt    += (int)$r->cnt;
        $all_unread += $unread;
    }
    wp_send_json_success(['forms' => $forms, 'total' => $all_cnt, 'total_unread' => $all_unread]);
});

// =========================================================================
// AJAX: Lista wiadomości (z paginacją, filtrem i wyszukiwaniem)
// =========================================================================

add_action('wp_ajax_evk_inbox_list', function () {
    check_ajax_referer('evk_inbox_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    if (!evk_inbox_table_exists()) wp_send_json_error('no_table');

    global $wpdb;
    $table    = evk_inbox_table();
    $s        = evk_inbox_get_settings();
    $read     = evk_inbox_get_read();
    $form_id  = sanitize_text_field($_GET['form_id'] ?? '');
    $search   = sanitize_text_field($_GET['search']  ?? '');
    $page     = max(1, intval($_GET['page'] ?? 1));
    $per_page = (int) $s['per_page'];
    $offset   = ($page - 1) * $per_page;

    $where = ['1=1']; $params = [];
    if ($form_id && $form_id !== 'all') { $where[] = 'form_id = %s'; $params[] = $form_id; }
    if ($search) { $where[] = 'form_data LIKE %s'; $params[] = '%' . $wpdb->esc_like($search) . '%'; }
    $wsql = implode(' AND ', $where);

    $total = (int) ($params
        ? $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE {$wsql}", ...$params))
        : $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE {$wsql}"));

    $rows = $params
        ? $wpdb->get_results($wpdb->prepare("SELECT id, form_id, form_data, created_at FROM {$table} WHERE {$wsql} ORDER BY created_at DESC LIMIT %d OFFSET %d", ...array_merge($params, [$per_page, $offset])))
        : $wpdb->get_results($wpdb->prepare("SELECT id, form_id, form_data, created_at FROM {$table} WHERE {$wsql} ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset));

    $items = [];
    foreach ($rows as $row) {
        $fields  = json_decode($row->form_data, true) ?: [];
        $items[] = [
            'id'      => (int) $row->id,
            'form_id' => $row->form_id,
            'name'    => evk_inbox_get_name($fields),
            'email'   => evk_inbox_find_email($fields, $s['email_field']),
            'preview' => evk_inbox_get_preview($fields),
            'date'    => evk_inbox_format_date($row->created_at),
            'is_read' => in_array((int) $row->id, $read, true),
        ];
    }
    wp_send_json_success(['items' => $items, 'total' => $total, 'page' => $page, 'per_page' => $per_page, 'pages' => (int) ceil($total / max(1, $per_page))]);
});

// =========================================================================
// AJAX: Szczegóły pojedynczej wiadomości
// =========================================================================

add_action('wp_ajax_evk_inbox_detail', function () {
    check_ajax_referer('evk_inbox_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    if (!evk_inbox_table_exists()) wp_send_json_error('no_table');

    global $wpdb;
    $id    = intval($_GET['id'] ?? 0);
    $table = evk_inbox_table();
    $s     = evk_inbox_get_settings();
    $row   = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id));
    if (!$row) wp_send_json_error('not_found');

    $fields = json_decode($row->form_data, true) ?: [];
    evk_inbox_mark_read([$id]);

    $display = [];
    foreach ($fields as $k => $v) {
        if (in_array($k, $s['hidden_fields'], true)) continue;
        $display[] = [
            'key'   => $k,
            'label' => evk_inbox_field_label($k, $s),
            'value' => is_array($v) ? implode(', ', $v) : (string) $v,
        ];
    }

    $user_name = '';
    if (!empty($row->user_id) && ($u = get_userdata((int)$row->user_id))) {
        $user_name = $u->display_name . ' (' . $u->user_email . ')';
    }

    wp_send_json_success([
        'id'     => (int) $row->id,
        'form_id'=> $row->form_id,
        'fields' => $display,
        'email'  => evk_inbox_find_email($fields, $s['email_field']),
        'name'   => evk_inbox_get_name($fields),
        'meta'   => [
            'date'     => date_i18n('j F Y, H:i', strtotime($row->created_at)),
            'ip'       => $row->ip       ?? '',
            'browser'  => $row->browser  ?? '',
            'os'       => $row->os       ?? '',
            'referrer' => $row->referrer ?? '',
            'user'     => $user_name,
        ],
    ]);
});

// =========================================================================
// AJAX: Oznacz przeczytane / nieprzeczytane
// =========================================================================

add_action('wp_ajax_evk_inbox_mark', function () {
    check_ajax_referer('evk_inbox_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    $ids   = array_map('intval', (array)($_POST['ids'] ?? []));
    $state = sanitize_key($_POST['state'] ?? 'read');
    if ($state === 'unread') evk_inbox_mark_unread($ids);
    else                     evk_inbox_mark_read($ids);
    wp_send_json_success();
});

// =========================================================================
// AJAX: Usuń wiadomości
// =========================================================================

add_action('wp_ajax_evk_inbox_delete', function () {
    check_ajax_referer('evk_inbox_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    if (!evk_inbox_table_exists()) wp_send_json_error('no_table');

    global $wpdb;
    $ids   = array_map('intval', (array)($_POST['ids'] ?? []));
    $table = evk_inbox_table();
    $count = 0;
    foreach ($ids as $id) { if ($wpdb->delete($table, ['id' => $id], ['%d'])) $count++; }
    evk_inbox_mark_unread($ids); // usuń z listy przeczytanych
    wp_send_json_success(['deleted' => $count]);
});

// =========================================================================
// AJAX: Pobierz klucze pól (dla ustawień)
// =========================================================================

add_action('wp_ajax_evk_inbox_field_keys', function () {
    check_ajax_referer('evk_inbox_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);

    if (!evk_inbox_table_exists()) { wp_send_json_success(['keys' => []]); }

    global $wpdb;
    $s    = evk_inbox_get_settings();
    $rows = $wpdb->get_results("SELECT form_data FROM " . evk_inbox_table() . " ORDER BY id DESC LIMIT 300");
    $keys = [];
    foreach ($rows as $row) {
        $fields = json_decode($row->form_data, true) ?: [];
        foreach (array_keys($fields) as $k) $keys[$k] = true;
    }
    $result = [];
    foreach (array_keys($keys) as $k) {
        $result[] = ['key' => $k, 'label' => evk_inbox_field_label($k, $s), 'hidden' => in_array($k, $s['hidden_fields'], true)];
    }
    wp_send_json_success(['keys' => $result, 'settings' => $s]);
});

// =========================================================================
// EKSPORT CSV (nie-AJAX — direct download)
// =========================================================================

add_action('admin_init', function () {
    if (
        !is_admin()
        || ($_GET['action'] ?? '') !== 'evk_inbox_export'
        || !current_user_can('manage_options')
        || empty($_GET['_wpnonce'])
        || !wp_verify_nonce($_GET['_wpnonce'], 'evk_inbox_export')
    ) return;

    if (!evk_inbox_table_exists()) wp_die('Tabela Bricks nie istnieje.');

    global $wpdb;
    $table   = evk_inbox_table();
    $s       = evk_inbox_get_settings();
    $form_id = sanitize_text_field($_GET['form_id'] ?? '');
    $where   = ($form_id && $form_id !== 'all') ? $wpdb->prepare('WHERE form_id = %s', $form_id) : '';
    $rows    = $wpdb->get_results("SELECT * FROM {$table} {$where} ORDER BY created_at DESC");

    $all_keys = [];
    foreach ($rows as $row) {
        $fields = json_decode($row->form_data, true) ?: [];
        foreach (array_keys($fields) as $k) {
            if (!in_array($k, $s['hidden_fields'], true)) $all_keys[$k] = true;
        }
    }
    $all_keys = array_keys($all_keys);

    $fn = 'submissions-' . sanitize_file_name($form_id ?: 'all') . '-' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $fn . '"');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

    $hdr = ['ID', 'Formularz', 'Data'];
    foreach ($all_keys as $k) $hdr[] = evk_inbox_field_label($k, $s);
    $hdr = array_merge($hdr, ['IP', 'Przeglądarka', 'OS', 'Referer', 'Użytkownik']);
    fputcsv($out, $hdr, ';');

    foreach ($rows as $row) {
        $fields = json_decode($row->form_data, true) ?: [];
        $line   = [$row->id, $row->form_id, $row->created_at];
        foreach ($all_keys as $k) {
            $v = $fields[$k] ?? '';
            $line[] = is_array($v) ? implode(', ', $v) : (string) $v;
        }
        $user_name = '';
        if (!empty($row->user_id) && ($u = get_userdata((int)$row->user_id))) $user_name = $u->display_name;
        $line = array_merge($line, [$row->ip ?? '', $row->browser ?? '', $row->os ?? '', $row->referrer ?? '', $user_name]);
        fputcsv($out, $line, ';');
    }
    fclose($out);
    exit;
});
