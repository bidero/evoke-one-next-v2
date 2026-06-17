<?php
if (!defined('ABSPATH')) exit;

/**
 * EVOKE Tłumaczenia - language detection, URL prefixes and translated slugs
 */

// ====================================================================
// 1. LANGUAGE SYSTEM - REWRITE RULES & DETECTION
// ====================================================================

/**
 * Pobiera listę aktywnych kodów językowych (bez PL)
 */
function tl_get_active_lang_codes(): array {
    return array_keys(tl_get_languages());
}

/**
 * Pobiera wszystkie języki z ustawień
 */
function tl_get_languages(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $saved = get_option('tl_languages', []);
    if (empty($saved)) {
        $saved = [
            ['code' => 'en', 'name' => 'Angielski', 'html' => 'en-US'],
            ['code' => 'de', 'name' => 'Niemiecki', 'html' => 'de-DE'],
        ];
    }
    $result = [];
    foreach ((array) $saved as $lang) {
        $code = trim($lang['code'] ?? '');
        if ($code && $code !== 'pl') {
            $result[$code] = [
                'name' => $lang['name'] ?? $code,
                'html' => $lang['html'] ?? $code,
                'flag' => absint($lang['flag'] ?? 0),
            ];
        }
    }
    $cache = $result;
    return $cache;
}

/**
 * Generuje regex dla rewrite rules
 */
function tl_get_lang_regex(): string {
    $codes = tl_get_active_lang_codes();
    if (empty($codes)) return '';
    return '(' . implode('|', array_map('preg_quote', $codes)) . ')';
}

/**
 * Rejestracja rewrite rules dla prefiksów językowych
 */
add_action('init', function () {
    $lang_regex = tl_get_lang_regex();
    if (empty($lang_regex)) return;

    // Reguła dla strony głównej z prefiksem językowym: /en/, /de/
    add_rewrite_rule(
        '^' . $lang_regex . '/?$',
        'index.php?lang_prefix=$matches[1]',
        'top'
    );

    // Reguła dla wszystkich podstron z prefiksem językowym: /en/cokolwiek
    add_rewrite_rule(
        '^' . $lang_regex . '/(.+?)/?$',
        'index.php?lang_prefix=$matches[1]&pagename=$matches[2]',
        'top'
    );

    // Dodatkowa reguła dla custom post types i innych
    add_rewrite_rule(
        '^' . $lang_regex . '/(.+?)$',
        'index.php?lang_prefix=$matches[1]&name=$matches[2]',
        'top'
    );
}, 5);

/**
 * Rejestracja query var
 */
add_filter('query_vars', function ($vars) {
    $vars[] = 'lang_prefix';
    return $vars;
});

/**
 * Flush rewrite rules przy aktywacji/zmianie ustawień
 */
function tl_flush_rewrite_rules(): void {
    flush_rewrite_rules();
}
register_activation_hook(EVOKE_TL_FILE, 'tl_flush_rewrite_rules');
add_action('update_option_tl_languages', 'tl_flush_rewrite_rules');
add_action('update_option_tl_url_slugs', 'tl_flush_rewrite_rules');

/**
 * Wykrywanie języka z URL prefix, ?lang= lub cookie
 */
add_action('init', function () {
    if (is_admin() && !wp_doing_ajax()) {
        $GLOBALS['lang_code'] = '';
        return;
    }

    $allowed = tl_get_active_lang_codes();
    $lang    = '';

    // 1. Sprawdź prefix w URL (parsowanie REQUEST_URI)
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($request_uri, PHP_URL_PATH);
    $path = trim($path, '/');
    $segments = explode('/', $path);

    if (!empty($segments[0]) && in_array($segments[0], $allowed, true)) {
        $lang = $segments[0];
    }

    // 2. Fallback: sprawdź parametr ?lang= (dla kompatybilności wstecznej i buildera)
    if (!$lang && isset($_GET['lang']) && in_array($_GET['lang'], $allowed, true)) {
        $lang = sanitize_key($_GET['lang']);

        // Przekierowanie ze starego formatu ?lang= na nowy z prefiksem (tylko na froncie)
        if (!tl_is_bricks_editor() && !tl_is_bricks_preview() && !is_admin()) {
            $clean_url = remove_query_arg(['lang', 'clear_lang']);
            $new_url = tl_add_lang_prefix_to_url($clean_url, $lang);
            if ($new_url !== $clean_url) {
                setcookie('site_lang', $lang, time() + (86400 * 30), '/');
                wp_redirect($new_url, 301);
                exit;
            }
        }
    }

    // 3. Obsługa clear_lang - przejście na PL (usunięcie prefiksu)
    if (isset($_GET['clear_lang'])) {
        setcookie('site_lang', '', time() - 3600, '/');
        $clean_url = remove_query_arg(['clear_lang', 'lang']);
        $clean_url = tl_remove_lang_prefix_from_url($clean_url);
        wp_redirect($clean_url, 301);
        exit;
    }

    // 4. Cookie jako ostateczny fallback (bez przekierowania)
    if (!$lang && isset($_COOKIE['site_lang']) && in_array($_COOKIE['site_lang'], $allowed, true)) {
        // Nie ustawiamy języka z cookie automatycznie - prefix URL ma priorytet
        // Cookie służy tylko do zapamiętania preferencji przy pierwszej wizycie
    }

    // 5. Ustaw cookie jeśli język wykryty z URL
    if ($lang) {
        if (!isset($_COOKIE['site_lang']) || $_COOKIE['site_lang'] !== $lang) {
            setcookie('site_lang', $lang, time() + (86400 * 30), '/');
        }
    }

    $GLOBALS['lang_code'] = $lang;
}, 1);

/**
 * Wymuszenie języka w Bricks Builder na podstawie ?lang=
 */
add_action('init', function() {
    if (isset($_GET['bricks']) && $_GET['bricks'] === 'run' && isset($_GET['lang'])) {
        $allowed = tl_get_active_lang_codes();
        if (in_array($_GET['lang'], $allowed, true)) {
            $GLOBALS['lang_code'] = sanitize_text_field($_GET['lang']);
        }
    }
}, 5);

/**
 * Pomocnicze funkcje URL
 */
function tl_add_lang_prefix_to_url(string $url, string $lang): string {
    if ($lang === 'pl' || $lang === '') {
        return tl_remove_lang_prefix_from_url($url);
    }

    $allowed = tl_get_active_lang_codes();
    if (!in_array($lang, $allowed, true)) {
        return $url;
    }

    $parsed = parse_url($url);
    $path = $parsed['path'] ?? '/';

    // Usuń istniejący prefix językowy jeśli jest
    $path = tl_remove_lang_prefix_from_path($path);

    // Dodaj nowy prefix
    $path = '/' . $lang . $path;

    // Złóż URL z powrotem
    $new_url = '';
    if (!empty($parsed['scheme'])) {
        $new_url .= $parsed['scheme'] . '://';
    }
    if (!empty($parsed['host'])) {
        $new_url .= $parsed['host'];
    }
    if (!empty($parsed['port'])) {
        $new_url .= ':' . $parsed['port'];
    }
    $new_url .= $path;
    if (!empty($parsed['query'])) {
        // Usuń parametr lang z query string
        parse_str($parsed['query'], $query_params);
        unset($query_params['lang']);
        unset($query_params['clear_lang']);
        if (!empty($query_params)) {
            $new_url .= '?' . http_build_query($query_params);
        }
    }
    if (!empty($parsed['fragment'])) {
        $new_url .= '#' . $parsed['fragment'];
    }

    return $new_url;
}

function tl_remove_lang_prefix_from_url(string $url): string {
    $parsed = parse_url($url);
    $path = $parsed['path'] ?? '/';

    $path = tl_remove_lang_prefix_from_path($path);

    $new_url = '';
    if (!empty($parsed['scheme'])) {
        $new_url .= $parsed['scheme'] . '://';
    }
    if (!empty($parsed['host'])) {
        $new_url .= $parsed['host'];
    }
    if (!empty($parsed['port'])) {
        $new_url .= ':' . $parsed['port'];
    }
    $new_url .= $path ?: '/';
    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $query_params);
        unset($query_params['lang']);
        unset($query_params['clear_lang']);
        if (!empty($query_params)) {
            $new_url .= '?' . http_build_query($query_params);
        }
    }
    if (!empty($parsed['fragment'])) {
        $new_url .= '#' . $parsed['fragment'];
    }

    return $new_url;
}

function tl_remove_lang_prefix_from_path(string $path): string {
    $allowed = tl_get_active_lang_codes();
    $path = '/' . ltrim($path, '/');

    foreach ($allowed as $code) {
        if (preg_match('#^/' . preg_quote($code, '#') . '(/|$)#', $path)) {
            $path = preg_replace('#^/' . preg_quote($code, '#') . '#', '', $path);
            break;
        }
    }

    return $path ?: '/';
}

/**
 * Główna funkcja pobierania aktualnego języka
 */
function get_current_lang(): string {
    return ($GLOBALS['lang_code'] ?? '') ?: 'pl';
}

/**
 * Generuje URL do przełączenia języka (bez tłumaczenia slugów - dla kompatybilności)
 */
function lang_switch_url(string $lang = ''): string {
    if (is_admin()) return '#';

    // Użyj funkcji z tłumaczeniem slugów jako domyślnej
    return lang_switch_url_with_translated_slug($lang);
}

// ====================================================================
// 1a. URL SLUG TRANSLATION SYSTEM
// ====================================================================

/**
 * Pobiera mapę tłumaczeń slugów URL
 */
function tl_get_url_slugs(): array {
    static $cache = null;
    if ($cache !== null) return $cache;

    $cached = get_transient(TL_TRANSIENT_SLUGS);
    if ($cached !== false) {
        $cache = is_array($cached) ? $cached : [];
        return $cache;
    }

    $slugs = get_option('tl_url_slugs', []);
    $cache = is_array($slugs) ? $slugs : [];

    set_transient(TL_TRANSIENT_SLUGS, $cache, TL_CACHE_TTL);
    return $cache;
}

/**
 * Tłumaczy slug z polskiego na inny język
 */
function tl_translate_slug(string $pl_slug, string $lang): string {
    if ($lang === 'pl' || empty($pl_slug)) return $pl_slug;

    $slugs = tl_get_url_slugs();

    foreach ($slugs as $entry) {
        if (($entry['pl'] ?? '') === $pl_slug && !empty($entry[$lang])) {
            return $entry[$lang];
        }
    }

    return $pl_slug;
}

/**
 * Tłumaczy slug z dowolnego języka na polski (do znajdowania strony)
 */
function tl_get_pl_slug(string $translated_slug, string $lang): string {
    if ($lang === 'pl' || empty($translated_slug)) return $translated_slug;

    $slugs = tl_get_url_slugs();

    foreach ($slugs as $entry) {
        if (($entry[$lang] ?? '') === $translated_slug && !empty($entry['pl'])) {
            return $entry['pl'];
        }
    }

    return $translated_slug;
}

/**
 * Tłumaczy całą ścieżkę URL (wiele segmentów)
 */
function tl_translate_url_path(string $path, string $from_lang, string $to_lang): string {
    if ($from_lang === $to_lang || empty($path)) return $path;

    $path = trim($path, '/');
    if ($path === '') return '/';

    $segments = array_filter(explode('/', $path));
    $translated = [];

    foreach ($segments as $segment) {
        if ($from_lang === 'pl') {
            $translated[] = tl_translate_slug($segment, $to_lang);
        } else {
            // Najpierw znajdź polski slug, potem przetłumacz na docelowy
            $pl_slug = tl_get_pl_slug($segment, $from_lang);
            $translated[] = ($to_lang === 'pl') ? $pl_slug : tl_translate_slug($pl_slug, $to_lang);
        }
    }

    return '/' . implode('/', $translated);
}

/**
 * Pobiera oryginalną (polską) ścieżkę z przetłumaczonej ścieżki URL
 */
function tl_get_original_path_from_translated(string $translated_path, string $lang): string {
    if ($lang === 'pl' || empty($translated_path)) return $translated_path;

    $translated_path = trim($translated_path, '/');
    if ($translated_path === '') return '/';

    $segments = array_values(array_filter(explode('/', $translated_path)));
    $original = [];

    foreach ($segments as $segment) {
        $original[] = tl_get_pl_slug($segment, $lang);
    }

    return '/' . implode('/', $original);
}

/**
 * Modyfikuje request aby znaleźć stronę po przetłumaczonym slugu
 */
add_filter('request', function($query_vars) {
    if (is_admin()) return $query_vars;

    // Pobierz lang — na tym etapie GLOBALS może być jeszcze niepewny,
    // więc parsujemy REQUEST_URI bezpośrednio jako fallback
    $lang = $GLOBALS['lang_code'] ?? '';
    if (empty($lang)) {
        $allowed = tl_get_active_lang_codes();
        $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
        $segments = explode('/', $path);
        if (!empty($segments[0]) && in_array($segments[0], $allowed, true)) {
            $lang = $segments[0];
        }
    }

    if (empty($lang) || $lang === 'pl') return $query_vars;

    // Sprawdź pagename (strony)
    if (!empty($query_vars['pagename'])) {
        $translated_path = $query_vars['pagename'];
        $original_path = tl_get_original_path_from_translated('/' . $translated_path, $lang);
        $original_path = trim($original_path, '/');

        if ($original_path !== $translated_path) {
            $query_vars['pagename'] = $original_path;
        }
    }

    // Sprawdź name (dla postów)
    if (!empty($query_vars['name'])) {
        $pl_name = tl_get_pl_slug($query_vars['name'], $lang);
        if ($pl_name !== $query_vars['name']) {
            $query_vars['name'] = $pl_name;
        }
    }

    return $query_vars;
}, 999); // priorytet 999 — po init, gdy lang_code jest już ustawiony

/**
 * pre_get_posts: dodatkowe zabezpieczenie — tłumaczy pagename/name w WP_Query
 * przed wykonaniem zapytania SQL, gdy filtr 'request' już zadziałał.
 * Obsługuje też przypadek gdy WordPress samodzielnie buduje query z URL
 * (np. dla hierarchicznych stron przez get_page_by_path).
 */
add_action('pre_get_posts', function (WP_Query $query) {
    if (is_admin() || !$query->is_main_query()) return;

    $lang = $GLOBALS['lang_code'] ?? '';
    if (empty($lang) || $lang === 'pl') return;

    // pagename (strony, w tym hierarchiczne)
    $pagename = $query->get('pagename');
    if (!empty($pagename)) {
        $original = trim(tl_get_original_path_from_translated('/' . $pagename, $lang), '/');
        if ($original !== $pagename) {
            $query->set('pagename', $original);
        }
    }

    // name (posty)
    $name = $query->get('name');
    if (!empty($name)) {
        $original_name = tl_get_pl_slug($name, $lang);
        if ($original_name !== $name) {
            $query->set('name', $original_name);
        }
    }
}, 1);

// Filtrujemy wynik WP_Query gdy dostaje 0 wyników dla przetłumaczonego pagename
add_filter('the_posts', function (array $posts, WP_Query $query) {
    if (is_admin() || !$query->is_main_query()) return $posts;
    if (!empty($posts)) return $posts; // są wyniki — nie ruszamy

    $lang = $GLOBALS['lang_code'] ?? '';
    if (empty($lang) || $lang === 'pl') return $posts;

    // Sprawdź oryginalne REQUEST_URI — pobierz path bez prefiksu językowego
    $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = tl_remove_lang_prefix_from_path(parse_url($request_uri, PHP_URL_PATH) ?? '/');
    $path = trim($path, '/');
    if ($path === '') return $posts;

    // Przetłumacz każdy segment na PL
    $segments = array_values(array_filter(explode('/', $path)));
    $pl_segments = array_map(fn($s) => tl_get_pl_slug($s, $lang), $segments);
    $pl_path = implode('/', $pl_segments);

    if ($pl_path === $path) return $posts; // brak tłumaczenia — nie ruszamy

    // Spróbuj znaleźć stronę po polskiej ścieżce
    $found = get_page_by_path($pl_path, OBJECT, 'page');
    if ($found) {
        $query->is_404  = false;
        $query->is_page = true;
        $query->set('pagename', $pl_path);
        return [$found];
    }

    // Spróbuj jako post
    $found_post = get_page_by_path($pl_path, OBJECT, 'post');
    if ($found_post) {
        $query->is_404      = false;
        $query->is_singular = true;
        $query->set('name', $pl_segments[count($pl_segments) - 1]);
        return [$found_post];
    }

    return $posts;
}, 10, 2);

/**
 * Redirect 302: jeśli odwiedzono /en/kontakt zamiast /en/contact, przekieruj na przetłumaczony slug.
 * Strona ładuje się normalnie (ten sam WordPress post), tylko URL w pasku jest przetłumaczony.
 */
add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax()) return;
    if (tl_is_bricks_editor() || tl_is_bricks_preview()) return;

    $lang = $GLOBALS['lang_code'] ?? '';
    if (empty($lang) || $lang === 'pl') return;

    // Pobierz aktualną ścieżkę bez prefiksu językowego
    $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
    $parsed      = parse_url($request_uri);
    $path        = $parsed['path'] ?? '/';
    $path_clean  = tl_remove_lang_prefix_from_path($path);
    $path_clean  = trim($path_clean, '/');

    if ($path_clean === '') return; // strona główna — brak slugów do tłumaczenia

    // Przetłumacz każdy segment z PL na docelowy język
    $segments    = array_values(array_filter(explode('/', $path_clean)));
    $translated  = [];
    $has_change  = false;

    foreach ($segments as $segment) {
        $tr = tl_translate_slug($segment, $lang);
        $translated[] = $tr;
        if ($tr !== $segment) {
            $has_change = true;
        }
    }

    if (!$has_change) return; // URL już jest przetłumaczony lub brak tłumaczenia — nie rób nic

    // Zbuduj docelowy URL z przetłumaczonymi slugami
    $new_path = '/' . $lang . '/' . implode('/', $translated) . '/';
    $base     = untrailingslashit(get_option('home'));
    $new_url  = $base . $new_path;

    if (!empty($parsed['query'])) {
        $new_url .= '?' . $parsed['query'];
    }
    if (!empty($parsed['fragment'])) {
        $new_url .= '#' . $parsed['fragment'];
    }

    wp_redirect($new_url, 302);
    exit;
});

/**
 * Modyfikuje generowane linki aby zawierały przetłumaczone slugi
 */
add_filter('page_link', function($link, $post_id, $sample) {
    if (tl_is_bricks_editor() || is_admin()) return $link;

    $lang = $GLOBALS['lang_code'] ?? '';
    if (empty($lang) || $lang === 'pl') return $link;

    $post = get_post($post_id);
    if (!$post) return $link;

    // Pobierz oryginalny slug i przetłumacz
    $ancestors = get_post_ancestors($post_id);
    $path_parts = [];

    // Dodaj ancestorów (od najstarszego)
    foreach (array_reverse($ancestors) as $ancestor_id) {
        $ancestor = get_post($ancestor_id);
        if ($ancestor) {
            $path_parts[] = tl_translate_slug($ancestor->post_name, $lang);
        }
    }

    // Dodaj aktualną stronę
    $path_parts[] = tl_translate_slug($post->post_name, $lang);

    $translated_path = implode('/', $path_parts);

    // Podmień w URL
    $parsed = parse_url($link);
    $new_path = '/' . $lang . '/' . $translated_path . '/';

    $new_url = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');
    if (!empty($parsed['port'])) $new_url .= ':' . $parsed['port'];
    $new_url .= $new_path;
    if (!empty($parsed['query'])) $new_url .= '?' . $parsed['query'];
    if (!empty($parsed['fragment'])) $new_url .= '#' . $parsed['fragment'];

    return $new_url;
}, 20, 3);

add_filter('post_link', function($link, $post, $leavename) {
    if (tl_is_bricks_editor() || is_admin()) return $link;

    $lang = $GLOBALS['lang_code'] ?? '';
    if (empty($lang) || $lang === 'pl') return $link;

    $translated_slug = tl_translate_slug($post->post_name, $lang);

    if ($translated_slug !== $post->post_name) {
        $link = str_replace('/' . $post->post_name . '/', '/' . $translated_slug . '/', $link);
    }

    return $link;
}, 20, 3);

/**
 * Generuje URL przełącznika języka z przetłumaczonym slugiem
 * TO JEST GŁÓWNA FUNKCJA DLA PRZEŁĄCZNIKA JĘZYKÓW
 */
function lang_switch_url_with_translated_slug(string $target_lang = ''): string {
    if (is_admin()) return '#';

    $current_lang = get_current_lang();
    $allowed = tl_get_active_lang_codes();

    // Pobierz aktualną ścieżkę (bez prefiksu językowego)
    $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
    $parsed = parse_url($request_uri);
    $path = $parsed['path'] ?? '/';

    // Usuń prefix językowy z aktualnej ścieżki
    $path = tl_remove_lang_prefix_from_path($path);
    $path = trim($path, '/');

    // Jeśli jesteśmy na stronie z przetłumaczonym slugiem, musimy najpierw
    // znaleźć oryginalną (polską) ścieżkę
    $original_path = '/';
    if ($path !== '') {
        if ($current_lang !== 'pl') {
            // Przetłumacz z bieżącego języka na polski
            $original_path = tl_get_original_path_from_translated('/' . $path, $current_lang);
        } else {
            $original_path = '/' . $path;
        }
    }

    // Teraz przetłumacz oryginalną ścieżkę na docelowy język
    $translated_path = '/';
    if ($original_path !== '/') {
        if ($target_lang === 'pl' || $target_lang === '' || !in_array($target_lang, $allowed, true)) {
            // Dla polskiego - użyj oryginalnej ścieżki
            $translated_path = $original_path;
        } else {
            // Dla innych języków - przetłumacz z polskiego
            $translated_path = tl_translate_url_path($original_path, 'pl', $target_lang);
        }
    }

    // Złóż nowy URL — użyj RAW home URL bez filtrów, żeby uniknąć
    // podwójnego prefiksu gdy home_url jest filtrowane przez tl_add_lang_prefix_to_url
    $new_url = untrailingslashit(get_option('home'));

    if ($target_lang === 'pl' || $target_lang === '' || !in_array($target_lang, $allowed, true)) {
        // Polski - bez prefiksu
        $new_url .= rtrim($translated_path, '/');
    } else {
        // Inny język - z prefiksem
        $new_url .= '/' . $target_lang . rtrim($translated_path, '/');
    }

    // Dodaj trailing slash
    if (substr($new_url, -1) !== '/') {
        $new_url .= '/';
    }

    // Query string (bez lang i clear_lang)
    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $query_params);
        unset($query_params['lang'], $query_params['clear_lang']);
        if (!empty($query_params)) {
            $new_url .= '?' . http_build_query($query_params);
        }
    }

    return $new_url;
}

// Dodaj do dozwolonych funkcji Bricks
add_filter('bricks/code/echo_functions', function ($functions) {
    $functions[] = 'lang_switch_url_with_translated_slug';
    return $functions;
});
