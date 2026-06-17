<?php
if (!defined('ABSPATH')) exit;


// =========================================================================
// WALIDACJA SKŁADNI PHP
// =========================================================================

function evk_snippet_validate_syntax(string $code) {
    if (empty(trim($code))) return true;

    $old = error_reporting(0);
    @token_get_all("<?php\n" . $code);
    error_reporting($old);

    $err = error_get_last();
    if ($err && in_array($err['type'], [E_PARSE, E_COMPILE_ERROR], true)) {
        @error_clear_last();
        $line = 0;
        if (preg_match('/on line (\d+)/', $err['message'], $m)) {
            $line = max(0, (int)$m[1] - 1);
        }
        return ['message' => $err['message'], 'line' => $line];
    }
    return true;
}

// =========================================================================
// LOGOWANIE BŁĘDÓW
// =========================================================================

function evk_snippet_log_error(string $type, string $message, string $slug, int $line = 0, string $code_ctx = ''): void {
    $logs = get_option(EVK_SNIPPETS_LOG_OPTION, []);
    if (!is_array($logs)) $logs = [];

    array_unshift($logs, [
        'timestamp' => current_time('mysql'),
        'type'      => sanitize_text_field($type),
        'message'   => wp_strip_all_tags($message),
        'slug'      => sanitize_text_field($slug),
        'line'      => absint($line),
        'context'   => $code_ctx ? substr($code_ctx, 0, 2000) : '',
    ]);

    if (count($logs) > EVK_SNIPPETS_MAX_LOG) {
        $logs = array_slice($logs, 0, EVK_SNIPPETS_MAX_LOG);
    }
    update_option(EVK_SNIPPETS_LOG_OPTION, $logs);
}

function evk_snippet_code_context(string $code, int $line, int $ctx = 3): string {
    $lines = explode("\n", $code);
    $start = max(0, $line - $ctx - 1);
    $end   = min(count($lines), $line + $ctx);
    $out   = [];
    for ($i = $start; $i < $end; $i++) {
        $marker = ($i === $line - 1) ? ' >>> ' : '     ';
        $out[]  = sprintf('%s%4d: %s', $marker, $i + 1, $lines[$i]);
    }
    return implode("\n", $out);
}

// =========================================================================
// WYKONYWANIE SNIPPETÓW
// =========================================================================

function evk_snippet_execute(string $code, string $slug): string {
    if (empty(trim($code))) return '';

    $validation = evk_snippet_validate_syntax($code);
    if (is_array($validation)) {
        update_option(EVK_SNIPPETS_ENABLED_OPTION, 0);
        evk_snippet_log_error('PHP Syntax Error', $validation['message'], $slug, $validation['line'],
            evk_snippet_code_context($code, $validation['line']));
        set_transient(EVK_SNIPPETS_FATAL_TRANSIENT, [
            'message' => $validation['message'],
            'slug'    => $slug,
            'line'    => $validation['line'],
            'type'    => 'Syntax Error',
        ], DAY_IN_SECONDS);
        return '';
    }

    $error_occurred = false;
    set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$error_occurred, $slug, $code) {
        if (!WP_DEBUG && in_array($errno, [E_DEPRECATED, E_USER_DEPRECATED, E_STRICT], true)) return true;
        $error_occurred = true;
        $types = [E_WARNING => 'PHP Warning', E_USER_WARNING => 'PHP Warning',
                  E_NOTICE  => 'PHP Notice',  E_USER_NOTICE  => 'PHP Notice',
                  E_DEPRECATED => 'PHP Deprecated', E_USER_DEPRECATED => 'PHP Deprecated'];
        $type = $types[$errno] ?? 'PHP Error';
        evk_snippet_log_error($type, $errstr, $slug, $errline,
            evk_snippet_code_context($code, $errline));
        return true;
    });

    ob_start();
    try {
        @eval('?>' . $code);
    } catch (ParseError $e) {
        $error_occurred = true;
        evk_snippet_log_error('PHP Parse Error', $e->getMessage(), $slug, $e->getLine(),
            evk_snippet_code_context($code, $e->getLine()));
        update_option(EVK_SNIPPETS_ENABLED_OPTION, 0);
        set_transient(EVK_SNIPPETS_FATAL_TRANSIENT, [
            'message' => $e->getMessage(), 'slug' => $slug,
            'line' => $e->getLine(), 'type' => 'Parse Error',
        ], DAY_IN_SECONDS);
    } catch (Throwable $e) {
        $error_occurred = true;
        evk_snippet_log_error(get_class($e), $e->getMessage(), $slug, $e->getLine(),
            evk_snippet_code_context($code, $e->getLine()));
        if ($e instanceof Error) {
            update_option(EVK_SNIPPETS_ENABLED_OPTION, 0);
            set_transient(EVK_SNIPPETS_FATAL_TRANSIENT, [
                'message' => $e->getMessage(), 'slug' => $slug,
                'line' => $e->getLine(), 'type' => get_class($e),
            ], DAY_IN_SECONDS);
        }
    }

    $output = ob_get_clean();
    restore_error_handler();
    return $error_occurred ? '' : $output;
}
