<?php
if (!defined('ABSPATH')) exit;

/**
 * EVOKE Tłumaczenia - Bricks language conditions
 */

// ====================================================================
// 1b. BRICKS CONDITIONS - DYNAMICZNE OPCJE JĘZYKÓW
// ====================================================================

/**
 * Rejestracja grupy warunków
 */
add_filter('bricks/conditions/groups', function ($groups) {
    $groups[] = [
        'name'  => 'custom_lang',
        'label' => 'Tłumaczenia',
    ];
    return $groups;
});

/**
 * Rejestracja opcji z dynamicznymi językami
 */
add_filter('bricks/conditions/options', function ($options) {
    // Pobierz języki z ustawień
    $langs = tl_get_languages();

    // Zbuduj opcje - zawsze zaczynamy od PL
    $lang_options = [
        'pl' => 'Polski (PL)',
    ];

    foreach ($langs as $code => $lang) {
        $lang_options[$code] = $lang['name'] . ' (' . strtoupper($code) . ')';
    }

    $options[] = [
        'key'   => 'current_language',
        'label' => 'Aktualny język',
        'group' => 'custom_lang',
        'compare' => [
            'type' => 'select',
            'options' => [
                '==' => 'jest',
                '!=' => 'nie jest',
            ],
        ],
        'value' => [
            'type' => 'select',
            'options' => $lang_options,
        ],
    ];

    return $options;
});

/**
 * Logika sprawdzania warunku
 */
add_filter('bricks/conditions/result', function ($result, $key, $condition) {
    if ($key !== 'current_language') {
        return $result;
    }

    // Sprawdź parametr URL dla buildera, potem globalną zmienną
    $allowed = tl_get_active_lang_codes();

    if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed, true)) {
        $current_lang = sanitize_text_field($_GET['lang']);
    } elseif (isset($_GET['lang']) && $_GET['lang'] === 'pl') {
        $current_lang = 'pl';
    } else {
        $current_lang = get_current_lang();
    }

    $compare = $condition['compare'] ?? '==';
    $value   = $condition['value'] ?? '';

    if ($compare === '==') {
        return $current_lang === $value;
    }
    if ($compare === '!=') {
        return $current_lang !== $value;
    }

    return $result;
}, 10, 3);

