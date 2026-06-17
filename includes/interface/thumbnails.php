<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke One — Interface: Miniatury w tabelach wpisów
 * Dodaje kolumnę z miniaturą (wyróżnionym obrazem) do tabel wpisów w panelu admina.
 */

add_action('admin_init', function () {
    register_setting('evoke_one_interface', 'evk_interface', [
        'sanitize_callback' => 'evk_interface_sanitize',
        'default'           => [],
    ]);
});

function evk_interface_sanitize($input): array {
    $input = is_array($input) ? $input : [];
    return [
        'post_thumbnails_enabled'  => !empty($input['post_thumbnails_enabled']) ? 1 : 0,
        'post_thumbnails_position' => in_array(
            $input['post_thumbnails_position'] ?? 'after_cb',
            ['after_cb', 'before_title', 'after_title', 'after_list'], true
        ) ? $input['post_thumbnails_position'] : 'after_cb',
        'post_thumbnails_size' => max(20, min(300, absint($input['post_thumbnails_size'] ?? 48))),
    ];
}

function evk_interface_get(): array {
    return wp_parse_args((array) get_option('evk_interface', []), [
        'post_thumbnails_enabled'  => 0,
        'post_thumbnails_position' => 'after_cb',
        'post_thumbnails_size'     => 48,
    ]);
}

add_action('admin_init', function () {
    $s = evk_interface_get();
    if (empty($s['post_thumbnails_enabled'])) return;

    $pos  = $s['post_thumbnails_position'];
    $size = max(20, min(300, (int)($s['post_thumbnails_size'] ?? 48)));

    foreach (get_post_types(['public' => true], 'names') as $pt) {
        add_filter("manage_{$pt}_posts_columns", function (array $cols) use ($pos): array {
            $thumb = ['evk_thumbnail' => 'Miniatura'];
            if ($pos === 'before_title') {
                $new = [];
                foreach ($cols as $k => $v) {
                    if ($k === 'title') $new += $thumb;
                    $new[$k] = $v;
                }
                return $new;
            }
            if ($pos === 'after_title') {
                $new = [];
                foreach ($cols as $k => $v) {
                    $new[$k] = $v;
                    if ($k === 'title') $new += $thumb;
                }
                return $new;
            }
            if ($pos === 'after_list') {
                return $cols + $thumb;
            }
            // after_cb — domyślne
            $new = [];
            foreach ($cols as $k => $v) {
                $new[$k] = $v;
                if ($k === 'cb') $new += $thumb;
            }
            return $new;
        });

        add_action("manage_{$pt}_posts_custom_column", function (string $col, int $pid) use ($size) {
            if ($col !== 'evk_thumbnail') return;
            $thumb_id = get_post_thumbnail_id($pid);
            if ($thumb_id) {
                echo wp_get_attachment_image($thumb_id, [$size, $size], false, [
                    'style' => "display:block;width:{$size}px;height:{$size}px;object-fit:cover;border-radius:4px;",
                ]);
            } else {
                echo '<span style="display:block;width:' . $size . 'px;height:' . $size . 'px;background:#f0f0f1;border-radius:4px;"></span>';
            }
        }, 10, 2);
    }

    $col_w = $size + 16;
    add_action('admin_head', function () use ($col_w) {
        echo "<style>.column-evk_thumbnail{width:{$col_w}px !important;}</style>";
    });
});
