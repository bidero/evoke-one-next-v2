<?php
if (!defined('ABSPATH')) exit;

/**
 * EVOKE Tłumaczenia - translation tokenizer and render engine
 */

// ====================================================================
// 7. TRANSLATION ENGINE
// ====================================================================
function tl_translate_attr_value(string $value, string $lang, array $strings): string {
    if ($value === '') return $value;

    uksort($strings, fn($a, $b) => mb_strlen($b) - mb_strlen($a));

    foreach ($strings as $search => $translations) {
        if (empty($translations[$lang])) continue;

        if (mb_strtolower(tl_normalize_text_for_match($value)) === mb_strtolower(tl_normalize_text_for_match(trim($search)))) {
            return $translations[$lang];
        }
    }

    return $value;
}

function tl_tokenize_attributes(string $content, string $lang): string {
    if ($lang === 'pl' || $content === '') return $content;

    $strings = get_translation_config()['strings'] ?? [];
    if (empty($strings)) return $content;

    $attrs_always = ['placeholder', 'aria-label', 'aria-placeholder', 'title'];

    return preg_replace_callback('/<([a-zA-Z][a-zA-Z0-9]*)\b([^>]*)>/u', function ($tag_match) use ($lang, $strings, $attrs_always) {
        $tag_name = strtolower($tag_match[1]);
        $attrs_str = $tag_match[2];
        $is_input = in_array($tag_name, ['input', 'textarea', 'select', 'button'], true);

        foreach ($attrs_always as $attr) {
            $attrs_str = preg_replace_callback('/(' . preg_quote($attr, '/') . '\s*=\s*)(["\'])(.*?)\2/iu', function ($match) use ($lang, $strings) {
                $prefix = $match[1];
                $quote  = $match[2];
                $value  = $match[3];
                return $prefix . $quote . esc_attr(tl_translate_attr_value($value, $lang, $strings)) . $quote;
            }, $attrs_str);
        }

        if ($is_input) {
            $attrs_str = preg_replace_callback('/(value\s*=\s*)(["\'])(.*?)\2/iu', function ($match) use ($lang, $strings) {
                $prefix = $match[1];
                $quote  = $match[2];
                $value  = $match[3];
                if (trim($value) === '') return $match[0];
                return $prefix . $quote . esc_attr(tl_translate_attr_value($value, $lang, $strings)) . $quote;
            }, $attrs_str);
        }

        return '<' . $tag_match[1] . $attrs_str . '>';
    }, $content);
}

function tl_tokenize_content(string $content, string $lang): string {
    if ($lang === 'pl' || $content === '') return $content;

    $strings = get_translation_config()['strings'] ?? [];
    if (empty($strings)) return $content;

    uksort($strings, fn($a, $b) => mb_strlen($b) - mb_strlen($a));

    $content = preg_replace_callback('/>([^<]+)</u', function ($matches) use ($strings, $lang) {
        $text = $matches[1];
        $text_normalized = tl_normalize_text_for_match($text);

        if ($text_normalized === '') return $matches[0];

        foreach ($strings as $search => $translations) {
            if (empty($translations[$lang])) continue;

            $search_trimmed = trim($search);
            if (mb_strtolower($text_normalized) !== mb_strtolower(tl_normalize_text_for_match($search_trimmed))) {
                continue;
            }

            return '>##TL_' . md5($search_trimmed) . '##<';
        }

        return $matches[0];
    }, $content);

    return tl_tokenize_attributes($content, $lang);
}

function tl_detokenize_content(string $content, string $lang): string {
    if ($lang === 'pl' || $content === '') return $content;

    $map = tl_get_token_map($lang);
    return empty($map) ? $content : strtr($content, $map);
}

add_filter('bricks/frontend/render_data', function ($content) {
    if (tl_is_bricks_editor() || tl_is_bricks_preview()) {
        return is_string($content) ? tl_replace_tl_tags_in_html($content, 'pl') : $content;
    }

    if (!is_string($content)) return $content;

    $content = preg_replace_callback('/\{tl:([^}]+)\}/i', fn($match) => tl_parse_inline_tag($match[1]), $content);
    return tl_tokenize_content($content, get_current_lang());
}, 1);
