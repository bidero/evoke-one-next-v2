<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke ONE Newsletter — Ustawienia (edytowalne teksty + wyglad shortcode).
 * Teksty obsluguja placeholder {email}.
 */

function evk_nl_text_defaults(): array {
    return [
        // Komunikaty formularza (AJAX)
        'form_success'        => 'Dziękujemy za zapis!',
        'form_pending'        => 'Sprawdź skrzynkę i potwierdź zapis klikając w link w wiadomości.',
        'form_already'        => 'Ten adres jest już zapisany.',
        // Strona potwierdzenia zapisu (double opt-in)
        'confirm_ok_title'    => 'Zapis potwierdzony',
        'confirm_ok_msg'      => 'Adres {email} został potwierdzony. Dziękujemy!',
        'confirm_bad_title'   => 'Nieprawidłowy link',
        'confirm_bad_msg'     => 'Link jest nieprawidłowy lub wygasł.',
        // Strona pytania o wypis (GET)
        'unsub_confirm_title' => 'Wypisać z newslettera?',
        'unsub_confirm_msg'   => 'Czy na pewno chcesz wypisać {email} z naszego newslettera?',
        'unsub_confirm_btn'   => 'Tak, wypisz mnie',
        // Strona wyniku wypisu
        'unsub_ok_title'      => 'Wypisano z newslettera',
        'unsub_ok_msg'        => 'Adres {email} został wypisany z naszego newslettera.',
        'unsub_bad_title'     => 'Nieprawidłowy link',
        'unsub_bad_msg'       => 'Link jest nieprawidłowy lub już wygasł.',
    ];
}

/**
 * Pobiera tekst (z ustawien lub domyslny) i podstawia {placeholdery}.
 */
function evk_nl_text(string $key, array $repl = []): string {
    $d = evk_nl_text_defaults();
    $o = get_option('evk_newsletter', []);
    $v = (isset($o[$key]) && trim((string) $o[$key]) !== '') ? (string) $o[$key] : ($d[$key] ?? '');
    foreach ($repl as $k => $val) {
        $v = str_replace('{' . $k . '}', $val, $v);
    }
    return $v;
}

/**
 * Ustawienia wygladu formularza shortcode.
 */
function evk_nl_appearance(): array {
    $o = get_option('evk_newsletter', []);
    return [
        'default_styles' => array_key_exists('form_default_styles', $o) ? !empty($o['form_default_styles']) : true,
        'wrap'    => $o['form_wrap_class']    ?? '',
        'input'   => $o['form_input_class']   ?? '',
        'button'  => $o['form_button_class']  ?? '',
        'consent' => $o['form_consent_class'] ?? '',
    ];
}

function evk_nl_sanitize_classes($s): string {
    $out = [];
    foreach (preg_split('/\s+/', (string) $s) as $c) {
        $c = sanitize_html_class($c);
        if ($c !== '') $out[] = $c;
    }
    return implode(' ', $out);
}

// =========================================================================
// ZAPIS USTAWIEN (admin_init — wzorzec snippets/forminbox)
// =========================================================================

add_action('admin_init', 'evk_nl_handle_settings_save');

function evk_nl_handle_settings_save(): void {
    if (($_POST['evk_nl_action'] ?? '') !== 'save_settings') return;
    if (!current_user_can('manage_options') && !current_user_can('evk_access_newsletter')) return;
    if (!isset($_POST['evk_nl_settings_nonce']) || !wp_verify_nonce($_POST['evk_nl_settings_nonce'], 'evk_nl_settings')) return;

    $o = get_option('evk_newsletter', []);
    if (!is_array($o)) $o = [];

    // Wyglad
    $o['form_default_styles'] = !empty($_POST['form_default_styles']) ? 1 : 0;
    foreach (['form_wrap_class', 'form_input_class', 'form_button_class', 'form_consent_class'] as $k) {
        $o[$k] = evk_nl_sanitize_classes($_POST[$k] ?? '');
    }

    // Teksty proste
    $plain = ['form_success', 'form_pending', 'form_already',
              'confirm_ok_title', 'confirm_bad_title',
              'unsub_confirm_title', 'unsub_confirm_btn', 'unsub_ok_title', 'unsub_bad_title'];
    foreach ($plain as $k) {
        if (isset($_POST[$k])) $o[$k] = sanitize_text_field(wp_unslash($_POST[$k]));
    }

    // Teksty z dozwolonym prostym HTML
    $allowed = ['strong' => [], 'em' => [], 'br' => [], 'a' => ['href' => [], 'target' => [], 'rel' => []]];
    $rich = ['confirm_ok_msg', 'confirm_bad_msg', 'unsub_confirm_msg', 'unsub_ok_msg', 'unsub_bad_msg'];
    foreach ($rich as $k) {
        if (isset($_POST[$k])) $o[$k] = wp_kses(wp_unslash($_POST[$k]), $allowed);
    }

    update_option('evk_newsletter', $o);
    wp_safe_redirect(add_query_arg(
        ['page' => 'evoke-one', 'tab' => 'newsletter', 'subtab' => 'settings', 'nl_saved' => '1'],
        admin_url('options-general.php')
    ));
    exit;
}
