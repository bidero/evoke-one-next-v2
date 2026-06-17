<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke ONE — Role Manager (logika)
 */

define('EVK_ROLE_RESTRICTIONS_OPTION', 'evk_role_restrictions');

// =========================================================================
// INIT — admin zawsze ma evk_access_translations
// =========================================================================

// Dynamicznie nadaj evk_access_* administratorowi — działa od razu
// bez potrzeby zapisywania do bazy i przeładowania sesji
add_filter('user_has_cap', function (array $caps, array $cap_check, array $args) {
    if (empty($cap_check)) return $caps;
    $cap = $cap_check[0];
    if (!in_array($cap, ['evk_access_translations', 'evk_access_newsletter'], true)) return $caps;
    if (!empty($caps['manage_options'])) {
        $caps[$cap] = true;
    }
    return $caps;
}, 1, 3);

add_action('init', function () {
    // Nadaj evk_access_translations administratorowi (zawsze ma dostęp)
    $admin_role = get_role('administrator');
    if ($admin_role && !$admin_role->has_cap('evk_access_translations')) {
        $admin_role->add_cap('evk_access_translations', true);
    }
    if ($admin_role && !$admin_role->has_cap('evk_access_newsletter')) {
        $admin_role->add_cap('evk_access_newsletter', true);
    }
    // Nadaj manage_evk_roles administratorowi
    if ($admin_role && !$admin_role->has_cap('manage_evk_roles')) {
        $admin_role->add_cap('manage_evk_roles', true);
    }
});

// AJAX tłumaczeń — nadaj manage_options dla ról z evk_access_translations
// (AJAX handlery w 31-admin-page.php sprawdzają manage_options)
add_action('admin_init', function () {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    if (!$action) return;
    if (strpos($action, 'tl_') !== 0 && strpos($action, 'evoke_tl') !== 0) return;

    $user = wp_get_current_user();
    if (!$user->ID || $user->has_cap('manage_options')) return;
    if (!$user->has_cap('evk_access_translations')) return;

    $user->add_cap('manage_options', true);
    add_action('shutdown', function () use ($user) {
        $user->remove_cap('manage_options');
    });
}, 1);

// =========================================================================
// HELPERY
// =========================================================================

function evk_role_get_all_caps(): array {
    global $wp_roles;
    if (!isset($wp_roles)) $wp_roles = new WP_Roles();
    $caps = [];
    foreach ($wp_roles->role_objects as $role) {
        if (is_array($role->capabilities)) {
            foreach (array_keys($role->capabilities) as $cap) {
                if (strpos($cap, 'evk_') === 0) continue;
                if (strpos($cap, 'manage_evk') === 0) continue;
                $caps[] = $cap;
            }
        }
    }
    $caps = array_unique($caps);
    sort($caps);
    return $caps;
}

function evk_role_is_core(string $slug): bool {
    return in_array($slug, ['administrator', 'editor', 'author', 'contributor', 'subscriber'], true);
}

function evk_role_get_restrictions(): array {
    return (array) get_option(EVK_ROLE_RESTRICTIONS_OPTION, []);
}

function evk_role_set_restrictions(string $role_id, array $page_ids): void {
    $all = evk_role_get_restrictions();
    if (empty($page_ids)) {
        unset($all[$role_id]);
    } else {
        $all[$role_id] = array_map('absint', $page_ids);
    }
    update_option(EVK_ROLE_RESTRICTIONS_OPTION, $all);
}

// =========================================================================
// OBSŁUGA FORMULARZY
// =========================================================================

add_action('admin_init', function () {
    if (!current_user_can('manage_evk_roles')) return;
    if (!isset($_POST['evk_role_action'])) return;
    $action = sanitize_key($_POST['evk_role_action']);

    if ($action === 'edit_role') {
        if (!wp_verify_nonce($_POST['evk_role_nonce'] ?? '', 'evk_edit_role')) return;
        $role_id = sanitize_key($_POST['role_id'] ?? '');
        if ($role_id === 'administrator' || !isset(get_editable_roles()[$role_id])) return;
        $role = get_role($role_id);
        if (!$role) return;

        // Standardowe capabilities (bez evk_*)
        $all_caps = evk_role_get_all_caps();
        $new_caps = array_keys(array_filter((array)($_POST['capabilities'] ?? []), fn($v) => $v === '1'));
        foreach ($all_caps as $cap) {
            if (in_array($cap, $new_caps, true)) $role->add_cap($cap, true);
            else $role->remove_cap($cap);
        }

        // Dostęp do Evoke ONE — Tłumaczenia
        if (!empty($_POST['evk_tl_access'])) {
            $role->add_cap('evk_access_translations', true);
        } else {
            $role->remove_cap('evk_access_translations');
        }

        // Dostęp do Evoke ONE — Newsletter
        if (!empty($_POST['evk_nl_access'])) {
            $role->add_cap('evk_access_newsletter', true);
        } else {
            $role->remove_cap('evk_access_newsletter');
        }

        // Ograniczenia stron
        $pages = array_map('absint', (array)($_POST['page_restrictions'] ?? []));
        evk_role_set_restrictions($role_id, $pages);
        add_settings_error('evk_role_manager', 'saved', 'Rola zaktualizowana.', 'updated');
    }

    if ($action === 'add_role') {
        if (!wp_verify_nonce($_POST['evk_role_nonce'] ?? '', 'evk_add_role')) return;
        $name    = sanitize_text_field($_POST['role_name'] ?? '');
        $slug    = sanitize_key($_POST['role_slug'] ?? '');
        $copy_of = sanitize_key($_POST['copy_from'] ?? '');
        if (empty($name) || empty($slug)) return;
        $caps = ($copy_of && ($src = get_role($copy_of))) ? $src->capabilities : [];
        add_role($slug, $name, $caps);
        add_settings_error('evk_role_manager', 'added', 'Rola dodana.', 'updated');
    }

    if ($action === 'delete_role') {
        if (!wp_verify_nonce($_POST['evk_role_nonce'] ?? '', 'evk_delete_role')) return;
        $role_id = sanitize_key($_POST['role_id'] ?? '');
        if (!evk_role_is_core($role_id)) remove_role($role_id);
        add_settings_error('evk_role_manager', 'deleted', 'Rola usunięta.', 'updated');
    }
}, 10);

// =========================================================================
// OGRANICZENIE EDYCJI STRON
// =========================================================================

add_filter('user_has_cap', function (array $allcaps, array $caps, array $args) {
    if (!in_array('edit_page', $caps, true) && !in_array('edit_post', $caps, true)) return $allcaps;
    $post_id = $args[2] ?? 0;
    if (!$post_id) return $allcaps;
    $user = wp_get_current_user();
    if (!$user->ID || $user->has_cap('administrator')) return $allcaps;
    $restrictions = evk_role_get_restrictions();
    foreach ($user->roles as $role_id) {
        if (!empty($restrictions[$role_id]) && !in_array((int)$post_id, $restrictions[$role_id], true)) {
            $allcaps['edit_pages'] = false;
            $allcaps['edit_posts'] = false;
        }
    }
    return $allcaps;
}, 10, 3);
