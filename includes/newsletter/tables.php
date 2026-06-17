<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke ONE Newsletter — Tabele bazy danych
 * Rejestracja i tworzenie 6 custom tables przez dbDelta.
 */

// =========================================================================
// TWORZENIE TABEL
// =========================================================================

function evk_nl_create_tables(): void {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset = $wpdb->get_charset_collate();

    $tables = [];

    $tables[] = "CREATE TABLE {$wpdb->prefix}evk_nl_lists (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        fields_config longtext DEFAULT NULL,
        status tinyint(1) NOT NULL DEFAULT 1,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}evk_nl_subscribers (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        list_id bigint(20) UNSIGNED NOT NULL,
        email varchar(255) NOT NULL,
        fields_json longtext DEFAULT NULL,
        status tinyint(1) NOT NULL DEFAULT 1,
        token varchar(64) NOT NULL DEFAULT '',
        subscribed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        unsubscribed_at datetime DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY token (token),
        KEY list_id (list_id),
        KEY email (email)
    ) $charset;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}evk_nl_templates (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        subject varchar(500) NOT NULL,
        body_html longtext NOT NULL,
        attachments_json longtext DEFAULT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}evk_nl_campaigns (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        template_id bigint(20) UNSIGNED NOT NULL,
        lists_json longtext NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'draft',
        scheduled_at datetime DEFAULT NULL,
        batch_size int(11) NOT NULL DEFAULT 50,
        batch_interval int(11) NOT NULL DEFAULT 5,
        tracking_enabled tinyint(1) NOT NULL DEFAULT 1,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status)
    ) $charset;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}evk_nl_queue (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        campaign_id bigint(20) UNSIGNED NOT NULL,
        subscriber_id bigint(20) UNSIGNED NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        attempts tinyint(3) NOT NULL DEFAULT 0,
        sent_at datetime DEFAULT NULL,
        opened_at datetime DEFAULT NULL,
        error_message text DEFAULT NULL,
        PRIMARY KEY (id),
        KEY campaign_status (campaign_id, status)
    ) $charset;";

    $tables[] = "CREATE TABLE {$wpdb->prefix}evk_nl_logs (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        campaign_id bigint(20) UNSIGNED NOT NULL,
        event varchar(50) NOT NULL,
        subscriber_id bigint(20) UNSIGNED DEFAULT NULL,
        data_json text DEFAULT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY campaign_event (campaign_id, event)
    ) $charset;";

    foreach ($tables as $sql) {
        dbDelta($sql);
    }

    update_option('evk_nl_db_version', '1.0.0');
}

// =========================================================================
// SPRAWDZENIE WERSJI — przy każdym ładowaniu modułu
// =========================================================================

function evk_nl_maybe_create_tables(): void {
    $installed = get_option('evk_nl_db_version', '');
    if ($installed !== '1.0.0') {
        evk_nl_create_tables();
    }
}

add_action('plugins_loaded', 'evk_nl_maybe_create_tables');

// =========================================================================
// POMOCNICZE — nazwy tabel
// =========================================================================

function evk_nl_table(string $name): string {
    global $wpdb;
    return $wpdb->prefix . 'evk_nl_' . $name;
}
