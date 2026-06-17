<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke ONE Newsletter — Listy i subskrybenci
 * CRUD operacje na evk_nl_lists i evk_nl_subscribers.
 */

// =========================================================================
// LISTY
// =========================================================================

function evk_nl_get_lists(): array {
    global $wpdb;
    $t = evk_nl_table('lists');
    return $wpdb->get_results("SELECT * FROM $t ORDER BY name ASC", ARRAY_A) ?: [];
}

function evk_nl_get_list(int $id): ?array {
    global $wpdb;
    $t = evk_nl_table('lists');
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE id=%d", $id), ARRAY_A);
    return $row ?: null;
}

function evk_nl_create_list(string $name, array $fields_config = []): int|false {
    global $wpdb;
    $wpdb->insert(evk_nl_table('lists'), [
        'name'          => sanitize_text_field($name),
        'fields_config' => wp_json_encode($fields_config),
        'status'        => 1,
    ]);
    return $wpdb->insert_id ?: false;
}

function evk_nl_update_list(int $id, array $data): bool {
    global $wpdb;
    $allowed = ['name', 'fields_config', 'status'];
    $clean   = [];
    if (isset($data['name']))          $clean['name']          = sanitize_text_field($data['name']);
    if (isset($data['fields_config'])) $clean['fields_config'] = is_string($data['fields_config']) ? $data['fields_config'] : wp_json_encode($data['fields_config']);
    if (isset($data['status']))        $clean['status']        = (int) $data['status'];
    if (empty($clean)) return false;
    return (bool) $wpdb->update(evk_nl_table('lists'), $clean, ['id' => $id]);
}

function evk_nl_delete_list(int $id): bool {
    global $wpdb;
    // Usuń najpierw subskrybentów
    $wpdb->delete(evk_nl_table('subscribers'), ['list_id' => $id]);
    return (bool) $wpdb->delete(evk_nl_table('lists'), ['id' => $id]);
}

function evk_nl_list_count(int $list_id, bool $active_only = true): int {
    global $wpdb;
    $t = evk_nl_table('subscribers');
    if ($active_only) {
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $t WHERE list_id=%d AND status=1", $list_id
        ));
    }
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $t WHERE list_id=%d", $list_id
    ));
}

// =========================================================================
// SUBSKRYBENCI
// =========================================================================

function evk_nl_get_subscribers(int $list_id, array $args = []): array {
    global $wpdb;
    $t       = evk_nl_table('subscribers');
    $limit   = (int) ($args['limit'] ?? 50);
    $offset  = (int) ($args['offset'] ?? 0);
    $search  = $args['search'] ?? '';
    $status  = $args['status'] ?? null;

    $where = $wpdb->prepare("WHERE list_id=%d", $list_id);
    if ($status !== null) {
        $where .= $wpdb->prepare(" AND status=%d", (int) $status);
    }
    if ($search !== '') {
        $like   = '%' . $wpdb->esc_like($search) . '%';
        $where .= $wpdb->prepare(" AND email LIKE %s", $like);
    }

    return $wpdb->get_results(
        "SELECT * FROM $t $where ORDER BY subscribed_at DESC LIMIT $limit OFFSET $offset",
        ARRAY_A
    ) ?: [];
}

function evk_nl_count_subscribers(int $list_id, array $args = []): int {
    global $wpdb;
    $t      = evk_nl_table('subscribers');
    $search = $args['search'] ?? '';
    $status = $args['status'] ?? null;

    $where = $wpdb->prepare("WHERE list_id=%d", $list_id);
    if ($status !== null) $where .= $wpdb->prepare(" AND status=%d", (int) $status);
    if ($search !== '') {
        $like   = '%' . $wpdb->esc_like($search) . '%';
        $where .= $wpdb->prepare(" AND email LIKE %s", $like);
    }

    return (int) $wpdb->get_var("SELECT COUNT(*) FROM $t $where");
}

function evk_nl_get_subscriber(int $id): ?array {
    global $wpdb;
    $t   = evk_nl_table('subscribers');
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE id=%d", $id), ARRAY_A);
    return $row ?: null;
}

function evk_nl_get_subscriber_by_token(string $token): ?array {
    global $wpdb;
    $t   = evk_nl_table('subscribers');
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE token=%s", $token), ARRAY_A);
    return $row ?: null;
}

function evk_nl_add_subscriber(int $list_id, string $email, array $fields = []): int|false {
    global $wpdb;
    $email = sanitize_email($email);
    if (!is_email($email)) return false;

    $t = evk_nl_table('subscribers');

    // Sprawdź czy istnieje
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id, status FROM $t WHERE list_id=%d AND email=%s", $list_id, $email
    ), ARRAY_A);

    if ($existing) {
        // Reaktywuj jeśli wypisany
        if ((int) $existing['status'] === 0) {
            $wpdb->update($t, [
                'status'           => 1,
                'unsubscribed_at'  => null,
                'fields_json'      => wp_json_encode($fields),
            ], ['id' => $existing['id']]);
        }
        return (int) $existing['id'];
    }

    $token = wp_generate_password(32, false);
    // Upewnij się unikalność tokenu
    while ($wpdb->get_var($wpdb->prepare("SELECT id FROM $t WHERE token=%s", $token))) {
        $token = wp_generate_password(32, false);
    }

    $wpdb->insert($t, [
        'list_id'    => $list_id,
        'email'      => $email,
        'fields_json'=> wp_json_encode($fields),
        'status'     => 1,
        'token'      => $token,
    ]);
    return $wpdb->insert_id ?: false;
}

function evk_nl_unsubscribe_by_token(string $token): bool {
    global $wpdb;
    $sub = evk_nl_get_subscriber_by_token($token);
    if (!$sub) return false;

    $wpdb->update(evk_nl_table('subscribers'), [
        'status'          => 0,
        'unsubscribed_at' => current_time('mysql'),
    ], ['id' => $sub['id']]);

    return true;
}

function evk_nl_delete_subscriber(int $id): bool {
    global $wpdb;
    return (bool) $wpdb->delete(evk_nl_table('subscribers'), ['id' => $id]);
}

// =========================================================================
// IMPORT
// =========================================================================

/**
 * Importuje listę emaili (tablica) do listy.
 * Zwraca ['added' => N, 'skipped' => N, 'invalid' => N]
 */
function evk_nl_import_emails(int $list_id, array $emails): array {
    $added   = 0;
    $skipped = 0;
    $invalid = 0;

    foreach ($emails as $raw) {
        $email = sanitize_email(trim($raw));
        if (!is_email($email)) {
            $invalid++;
            continue;
        }
        $result = evk_nl_add_subscriber($list_id, $email);
        if ($result) {
            $added++;
        } else {
            $skipped++;
        }
    }

    return compact('added', 'skipped', 'invalid');
}

/**
 * Parsuje CSV: email w pierwszej kolumnie, obsługuje przecinki i nowe linie.
 */
function evk_nl_parse_csv(string $csv_content): array {
    $emails = [];
    $lines  = preg_split('/\r?\n/', trim($csv_content));
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        // Weź pierwszą kolumnę
        $parts = str_getcsv($line);
        $email = trim($parts[0] ?? '');
        if ($email) $emails[] = $email;
    }
    return $emails;
}

/**
 * Parsuje textarea — jeden email per linia.
 */
function evk_nl_parse_textarea(string $text): array {
    $emails = [];
    $lines  = preg_split('/\r?\n/', trim($text));
    foreach ($lines as $line) {
        $email = sanitize_email(trim($line));
        if (is_email($email)) $emails[] = $email;
    }
    return $emails;
}

// =========================================================================
// AKTYWNI SUBSKRYBENCI DLA KAMPANII
// =========================================================================

/**
 * Pobiera wszystkich aktywnych subskrybentów z podanych list (bez duplikatów emaili).
 */
function evk_nl_get_campaign_subscribers(array $list_ids): array {
    if (empty($list_ids)) return [];
    global $wpdb;
    $t            = evk_nl_table('subscribers');
    $ids_escaped  = implode(',', array_map('intval', $list_ids));

    // GROUP BY email żeby uniknąć duplikatów
    return $wpdb->get_results(
        "SELECT MIN(id) as id, email, MIN(token) as token, MIN(fields_json) as fields_json
         FROM $t
         WHERE list_id IN ($ids_escaped) AND status=1
         GROUP BY email",
        ARRAY_A
    ) ?: [];
}
