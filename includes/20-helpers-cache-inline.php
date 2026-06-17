<?php
if (!defined('ABSPATH')) exit;

/**
 * EVOKE Tłumaczenia - helpers, caches and inline phrase discovery
 */

// ====================================================================
// 2a. HELPERS
// ====================================================================

function tl_normalize_text_for_match(string $text): string {
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_replace("\xC2\xA0", ' ', $text);
    $text = str_replace('&nbsp;', ' ', $text);
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim((string) $text);
}

function tl_split_paragraphs(string $text): array {
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = trim($text);
    if ($text === '') return [];
    $parts = preg_split('/\n[ \t]*\n+/u', $text);
    $parts = array_map(function ($part) {
        $part = preg_replace('/[ \t]+/u', ' ', $part);
        $part = preg_replace('/\n+/u', ' ', $part);
        return trim((string) $part);
    }, $parts ?: []);
    return array_values(array_filter($parts, fn($p) => $p !== ''));
}

function tl_invalidate_cache(): void {
    delete_transient(TL_TRANSIENT_CONFIG);
    delete_transient(TL_TRANSIENT_INLINE);
    delete_transient(TL_TRANSIENT_SLUGS);
    foreach (tl_get_active_lang_codes() as $code) {
        delete_transient(TL_TRANSIENT_TOKENS . $code);
    }
}

// ====================================================================
// 2b. TRANSLATION CACHE
// ====================================================================
function get_translation_config(): array {
    static $mem_cache = null;
    if ($mem_cache !== null) return $mem_cache;
    $cached = get_transient(TL_TRANSIENT_CONFIG);
    if ($cached !== false) {
        $mem_cache = is_array($cached) ? $cached : ['strings' => [], 'meta' => []];
        return $mem_cache;
    }
    $data   = get_option('tl_translations', ['groups' => []]);
    $codes  = tl_get_active_lang_codes();
    $config = ['strings' => [], 'meta' => []];
    foreach (($data['groups'] ?? []) as $group) {
        foreach (($group['rows'] ?? []) as $row) {
            $pl = trim($row['pl'] ?? '');
            if ($pl === '') continue;
            $entry = [];
            foreach ($codes as $code) { $entry[$code] = trim($row[$code] ?? ''); }
            $config['strings'][$pl] = $entry;
            $pl_parts = tl_split_paragraphs($pl);
            if (count($pl_parts) < 2) continue;
            foreach ($pl_parts as $index => $pl_part) {
                if (isset($config['strings'][$pl_part])) continue;
                $part_entry = [];
                foreach ($codes as $code) {
                    $translated_parts = tl_split_paragraphs($entry[$code] ?? '');
                    $part_entry[$code] = $translated_parts[$index] ?? '';
                }
                $config['strings'][$pl_part] = $part_entry;
                $config['meta'][$pl_part] = ['parent_pl' => $pl, 'part_index' => $index];
            }
        }
    }
    set_transient(TL_TRANSIENT_CONFIG, $config, TL_CACHE_TTL);
    $mem_cache = $config;
    return $mem_cache;
}

function tl_get_token_map(string $lang): array {
    static $mem = [];
    if (isset($mem[$lang])) return $mem[$lang];
    $cached = get_transient(TL_TRANSIENT_TOKENS . $lang);
    if ($cached !== false) { $mem[$lang] = is_array($cached) ? $cached : []; return $mem[$lang]; }
    $strings = get_translation_config()['strings'] ?? [];
    uksort($strings, fn($a, $b) => mb_strlen($b) - mb_strlen($a));
    $map = [];
    foreach ($strings as $search => $translations) {
        if (!empty($translations[$lang])) {
            $map['##TL_' . md5(trim($search)) . '##'] = $translations[$lang];
        }
    }
    set_transient(TL_TRANSIENT_TOKENS . $lang, $map, TL_CACHE_TTL);
    $mem[$lang] = $map;
    return $mem[$lang];
}

add_action('update_option_tl_translations', 'tl_invalidate_cache');
add_action('update_option_tl_languages', 'tl_invalidate_cache');
add_action('update_option_tl_dd_keys', 'tl_invalidate_cache');
add_action('update_option_tl_url_slugs', 'tl_invalidate_cache');

add_filter('bricks/code/echo_functions', function ($functions) {
    return array_merge($functions, ['lang_switch_url', 'get_current_lang']);
});

// ====================================================================
// 2c. INLINE PHRASE DISCOVERY
// ====================================================================
function tl_get_inline_phrases(): array {
    static $mem = null;
    if ($mem !== null) return $mem;
    $cached = get_transient(TL_TRANSIENT_INLINE);
    if ($cached !== false) { $mem = is_array($cached) ? $cached : []; return $mem; }
    $phrases = [];
    $posts = get_posts(['post_type' => ['bricks_template','page','post'], 'posts_per_page' => 200, 'post_status' => 'publish', 'fields' => 'ids']);
    foreach ($posts as $post_id) {
        $content = get_post_meta($post_id, '_bricks_page_content_2', true);
        if (empty($content)) continue;
        $json = is_string($content) ? $content : wp_json_encode($content);
        preg_match_all('/\{tl:([^}]+)\}/i', $json, $matches);
        foreach ($matches[0] as $index => $full_match) {
            $pairs = explode('|', $matches[1][$index]);
            $translations = [];
            foreach ($pairs as $pair) {
                if (strpos($pair, '=') === false) continue;
                [$code, $text] = explode('=', $pair, 2);
                $translations[strtolower(trim($code))] = trim($text);
            }
            if (!empty($translations)) {
                $key = $translations['pl'] ?? reset($translations);
                $phrases[$key] = ['source' => 'inline', 'raw' => $full_match, 'translations' => $translations];
            }
        }
        preg_match_all('/\[tl\s+([^\]]+)\]/i', $json, $shortcode_matches);
        foreach ($shortcode_matches[0] as $index => $full_match) {
            $attrs = $shortcode_matches[1][$index];
            $translations = [];
            preg_match_all('/(\w+)=["\']([^"\']+)["\']/i', $attrs, $attr_matches);
            foreach ($attr_matches[1] as $i => $attr_name) {
                $translations[strtolower($attr_name)] = $attr_matches[2][$i];
            }
            if (!empty($translations)) {
                $key = $translations['pl'] ?? reset($translations);
                $phrases[$key] = ['source' => 'shortcode', 'raw' => $full_match, 'translations' => $translations];
            }
        }
    }
    set_transient(TL_TRANSIENT_INLINE, $phrases, TL_CACHE_TTL);
    $mem = $phrases;
    return $mem;
}
foreach (['bricks_template','page','post'] as $_tl_post_type) {
    add_action('save_post_' . $_tl_post_type, function () { delete_transient(TL_TRANSIENT_INLINE); });
}

