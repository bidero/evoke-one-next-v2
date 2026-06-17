<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke One — Moduł SEO
 */

add_filter('pre_get_document_title', function ($title) {
    if (is_singular()) {
        $pid    = get_queried_object_id();
        $custom = $pid ? get_post_meta($pid, '_evoke_seo_title', true) : '';
        if (!empty($custom)) return $custom;
    }
    return $title;
}, 999);

add_action('wp_ajax_evoke_save_seo_ajax', function () {
    if (!current_user_can('manage_options') || empty($_POST['post_id'])) wp_send_json_error();
    $pid = absint($_POST['post_id']);
    update_post_meta($pid, '_evoke_seo_title',    sanitize_text_field(wp_unslash($_POST['seo_title']    ?? '')));
    update_post_meta($pid, '_evoke_seo_desc',     sanitize_textarea_field(wp_unslash($_POST['seo_desc'] ?? '')));
    update_post_meta($pid, '_evoke_seo_keywords', sanitize_text_field(wp_unslash($_POST['seo_keywords'] ?? '')));
    $robots = json_decode(wp_unslash($_POST['seo_robots'] ?? '[]'), true);
    $valid  = array_values(array_intersect((array)$robots, ['index','noindex','follow','nofollow','noarchive','nosnippet']));
    update_post_meta($pid, '_evoke_seo_robots', $valid);
    wp_send_json_success();
});

add_action('wp_ajax_evoke_save_seo_bulk', function () {
    if (!current_user_can('manage_options')) wp_send_json_error();
    $rows = json_decode(wp_unslash($_POST['rows'] ?? '[]'), true);
    $count = 0;
    foreach ((array)$rows as $row) {
        $pid = absint($row['post_id'] ?? 0);
        if (!$pid) continue;
        update_post_meta($pid, '_evoke_seo_title',    sanitize_text_field($row['seo_title']    ?? ''));
        update_post_meta($pid, '_evoke_seo_desc',     sanitize_textarea_field($row['seo_desc'] ?? ''));
        update_post_meta($pid, '_evoke_seo_keywords', sanitize_text_field($row['seo_keywords'] ?? ''));
        $robots = (array)($row['seo_robots'] ?? []);
        $valid  = array_values(array_intersect($robots, ['index','noindex','follow','nofollow','noarchive','nosnippet']));
        update_post_meta($pid, '_evoke_seo_robots', $valid);
        $count++;
    }
    wp_send_json_success(['saved' => $count]);
});

// 1. Filtr tytułu
add_filter('pre_get_document_title', function ($title) {
    // Wpuszczamy wpisy, zwykłe strony ORAZ stronę bloga (Aktualności)
    if (is_singular() || is_home()) {
        $pid    = get_queried_object_id();
        $custom = $pid ? get_post_meta($pid, '_evoke_seo_title', true) : '';
        if (!empty($custom)) return $custom;
    }
    return $title;
}, 999);

// 2. Akcja dla sekcji <head>
add_action('wp_head', function () {
    // Przerywamy tylko, jeśli to nie jest wpis/strona i nie jest to główny blog
    if (!is_singular() && !is_home()) return;

    $pid = get_queried_object_id();
    if (!$pid) return;

    $title    = get_post_meta($pid, '_evoke_seo_title', true) ?: get_the_title($pid);
    $desc     = get_post_meta($pid, '_evoke_seo_desc', true);
    $keywords = get_post_meta($pid, '_evoke_seo_keywords', true);
    $robots   = (array)(get_post_meta($pid, '_evoke_seo_robots', true) ?: []);

    if ($desc)     echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    if ($keywords) echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
    
    if (!empty($robots)) {
        $valid = array_values(array_intersect($robots, ['index','noindex','follow','nofollow','noarchive','nosnippet']));
        if ($valid) echo '<meta name="robots" content="' . esc_attr(implode(', ', $valid)) . '">' . "\n";
    }

    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:type" content="article">' . "\n";
    echo '<meta property="og:url" content="' . esc_url(get_permalink($pid)) . '">' . "\n";
    if ($desc) echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";

    if (function_exists('evk_og_get_url')) {
        $img = evk_og_get_url($pid);
        if ($img) echo '<meta property="og:image" content="' . esc_url($img) . '">' . "\n";
    }
}, 5);
