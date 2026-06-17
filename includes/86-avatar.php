<?php
if (!defined('ABSPATH')) exit;

/**
 * Evoke One — Moduł Avatara Użytkownika
 * Własny obraz użytkownika z biblioteki mediów WP, zastępujący Gravatar.
 */

// =========================================================================
// POLE W PROFILU UŻYTKOWNIKA
// =========================================================================

// Wyświetl pole wyboru avatara w profilu
function evk_avatar_profile_field(WP_User $user): void {
    $attachment_id = (int) get_user_meta($user->ID, 'evk_avatar_id', true);
    $current_url   = $attachment_id ? wp_get_attachment_image_url($attachment_id, [96, 96]) : '';
    ?>
    <tr class="user-evk-avatar-wrap">
        <th><label><?php esc_html_e('Avatar (własny obraz)', 'evoke-one'); ?></label></th>
        <td>
            <div style="display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap;">
                <div id="evk-avatar-preview" style="width:96px;height:96px;border-radius:50%;overflow:hidden;border:2px solid #d1d5db;flex-shrink:0;background:#f3f4f6;display:flex;align-items:center;justify-content:center;">
                    <?php if ($current_url): ?>
                        <img src="<?php echo esc_url($current_url); ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
                    <?php else: ?>
                        <span class="dashicons dashicons-admin-users" style="font-size:40px;width:40px;height:40px;color:#9ca3af;margin:0;line-height:1;"></span>
                    <?php endif; ?>
                </div>
                <div style="padding-top:4px;">
                    <input type="hidden" id="evk-avatar-id" name="evk_avatar_id" value="<?php echo esc_attr($attachment_id ?: ''); ?>">
                    <div style="display:flex;gap:8px;align-items:center;margin-bottom:10px;">
                        <button type="button" id="evk-avatar-select" class="button">
                            <?php echo $attachment_id ? 'Zmień obraz' : 'Wybierz obraz'; ?>
                        </button>
                        <?php if ($attachment_id): ?>
                        <button type="button" id="evk-avatar-remove" class="button button-link-delete" style="height:30px;line-height:28px;">Usuń</button>
                        <?php endif; ?>
                    </div>
                    <p class="description" style="margin:0;">Obraz z biblioteki mediów zastąpi Gravatar w całej witrynie<br>(komentarze, panel admina, autor wpisu). Zalecany rozmiar: min. 96×96 px.</p>
                </div>
            </div>
            <script>
            (function ($) {
                var frame;
                $('#evk-avatar-select').on('click', function (e) {
                    e.preventDefault();
                    if (frame) { frame.open(); return; }
                    frame = wp.media({
                        title: 'Wybierz avatar',
                        button: { text: 'Ustaw jako avatar' },
                        multiple: false,
                        library: { type: 'image' }
                    });
                    frame.on('select', function () {
                        var att = frame.state().get('selection').first().toJSON();
                        $('#evk-avatar-id').val(att.id);
                        var url = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                        $('#evk-avatar-preview').html('<img src="' + url + '" style="width:100%;height:100%;object-fit:cover;" alt="">');
                        $('#evk-avatar-select').text('Zmień obraz');
                        if (!$('#evk-avatar-remove').length) {
                            $('<button type="button" id="evk-avatar-remove" class="button button-link-delete" style="height:30px;line-height:28px;">Usuń</button>')
                                .insertAfter('#evk-avatar-select')
                                .on('click', evkAvatarRemove);
                        }
                    });
                    frame.open();
                });

                function evkAvatarRemove(e) {
                    e.preventDefault();
                    $('#evk-avatar-id').val('');
                    $('#evk-avatar-preview').html('<span class="dashicons dashicons-admin-users" style="font-size:48px;width:48px;height:48px;color:#9ca3af;"></span>');
                    $('#evk-avatar-select').text('Wybierz obraz');
                    $(this).remove();
                }
                $('#evk-avatar-remove').on('click', evkAvatarRemove);
            })(jQuery);
            </script>
        </td>
    </tr>
    <?php
}
add_action('show_user_profile',   'evk_avatar_profile_field');
add_action('edit_user_profile',   'evk_avatar_profile_field');

// Załaduj media uploader na stronach profilu
add_action('admin_enqueue_scripts', function (string $hook) {
    if (!in_array($hook, ['profile.php', 'user-edit.php'], true)) return;
    wp_enqueue_media();
});

// =========================================================================
// ZAPIS
// =========================================================================

function evk_avatar_save(int $user_id): void {
    if (!current_user_can('edit_user', $user_id)) return;
    if (!isset($_POST['evk_avatar_id'])) return;

    $attachment_id = absint($_POST['evk_avatar_id']);
    if ($attachment_id) {
        // Weryfikuj że to faktycznie obraz z biblioteki mediów
        if (get_post_type($attachment_id) === 'attachment' && wp_attachment_is_image($attachment_id)) {
            update_user_meta($user_id, 'evk_avatar_id', $attachment_id);
        }
    } else {
        delete_user_meta($user_id, 'evk_avatar_id');
    }
}
add_action('personal_options_update', 'evk_avatar_save');
add_action('edit_user_profile_update', 'evk_avatar_save');

// =========================================================================
// PODMIANA AVATARA WSZĘDZIE W WP
// =========================================================================

add_filter('get_avatar', function (string $avatar, $id_or_email, $size, string $default, string $alt): string {
    $user = evk_avatar_resolve_user($id_or_email);
    if (!$user) return $avatar;

    $attachment_id = (int) get_user_meta($user->ID, 'evk_avatar_id', true);
    if (!$attachment_id) return $avatar;

    $url = wp_get_attachment_image_url($attachment_id, [$size, $size]);
    if (!$url) return $avatar;

    $class = "avatar avatar-{$size} photo evk-avatar";
    return sprintf(
        '<img alt="%s" src="%s" srcset="%s 2x" class="%s" height="%d" width="%d" loading="lazy" decoding="async">',
        esc_attr($alt ?: $user->display_name),
        esc_url($url),
        esc_url(wp_get_attachment_image_url($attachment_id, [$size * 2, $size * 2]) ?: $url),
        esc_attr($class),
        (int) $size,
        (int) $size
    );
}, 10, 5);

add_filter('get_avatar_url', function (string $url, $id_or_email): string {
    $user = evk_avatar_resolve_user($id_or_email);
    if (!$user) return $url;

    $attachment_id = (int) get_user_meta($user->ID, 'evk_avatar_id', true);
    if (!$attachment_id) return $url;

    $custom = wp_get_attachment_image_url($attachment_id, [96, 96]);
    return $custom ?: $url;
}, 10, 2);

// =========================================================================
// HELPER: wyciągnij WP_User z różnych typów wejścia
// =========================================================================

function evk_avatar_resolve_user($id_or_email): ?WP_User {
    if ($id_or_email instanceof WP_User) return $id_or_email;
    if ($id_or_email instanceof WP_Post) {
        $user = get_user_by('id', (int) $id_or_email->post_author);
        return $user ?: null;
    }
    if ($id_or_email instanceof WP_Comment) {
        if (!empty($id_or_email->user_id)) {
            $user = get_user_by('id', (int) $id_or_email->user_id);
            return $user ?: null;
        }
        if (!empty($id_or_email->comment_author_email)) {
            $user = get_user_by('email', $id_or_email->comment_author_email);
            return $user ?: null;
        }
        return null;
    }
    if (is_numeric($id_or_email)) {
        $user = get_user_by('id', (int) $id_or_email);
        return $user ?: null;
    }
    if (is_string($id_or_email) && is_email($id_or_email)) {
        $user = get_user_by('email', $id_or_email);
        return $user ?: null;
    }
    return null;
}
