<?php
if (!defined('ABSPATH')) exit;
?>
            <?php
            $og       = evk_og_get_settings();
            $og_layers = $og['layers'] ?? [];
            $og_nonce  = wp_create_nonce('evk_og_regen');
            $all_post_types = get_post_types(['public' => true], 'objects');

            $blend_modes = ['normal','multiply','screen','overlay','darken','lighten',
                            'color-dodge','color-burn','hard-light','soft-light',
                            'difference','exclusion'];
            $layer_types = [
                'rect'     => 'Prostokąt (kolor)',
                'photo'    => 'Zdjęcie wyróżniające',
                'gradient' => 'Gradient',
                'image'    => 'Obraz (biblioteka mediów)',
                'text'     => 'Tekst (tytuł)',
                'qr'       => 'Kod QR',
            ];
            ?>

            <style>
                .evo-og-layer { background:#f8fafc; border:1px solid #d7dde7; border-radius:8px; padding:18px 20px; margin-bottom:14px; }
                .evo-og-layer-header { display:flex; align-items:center; gap:10px; margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid #e5e7eb; cursor:grab; }
                .evo-og-layer-header .drag-handle { color:#94a3b8; font-size:18px; line-height:1; user-select:none; }
                .evo-og-layer-header .layer-toggle { flex-shrink:0; }
                .evo-og-layer-title { font-size:13px; font-weight:600; color:#111827; flex:1; }
                .evo-og-layer-type-badge { font-size:11px; color:#6b7280; background:#e5e7eb; padding:2px 8px; border-radius:10px; }
                .evo-og-layer-fields { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; }
                .evo-og-layer-fields label { font-size:12px; font-weight:600; color:#4b5563; display:block; margin-bottom:3px; }
                .evo-og-layer-fields input[type=text],
                .evo-og-layer-fields input[type=number],
                .evo-og-layer-fields select { width:100%; border:1px solid #d1d5db; border-radius:5px; font-size:13px; padding:5px 8px; }
                .evo-og-layer-fields input[type=color] { width:48px; height:32px; border-radius:5px; border:1px solid #d1d5db; cursor:pointer; padding:2px; }
                .evo-og-color-pair { display:flex; align-items:center; gap:8px; }
                .evo-og-color-pair input[type=text] { font-family:monospace; font-size:12px; max-width:100px; }
                .evo-og-section { background:#fff; border:1px solid #d7dde7; border-radius:8px; padding:18px 20px; margin-bottom:16px; }
                .evo-og-section h3 { font-size:13px; font-weight:700; color:#111827; margin:0 0 14px; text-transform:uppercase; letter-spacing:.5px; font-size:11px; }
                .evo-og-btn-remove { background:none; border:none; color:#ef4444; cursor:pointer; font-size:12px; display:flex; align-items:center; gap:4px; padding:0; }
                .evo-og-btn-remove:hover { color:#b91c1c; text-decoration:underline; }
                .evo-og-regen-result { font-size:13px; color:#047857; margin-top:8px; display:none; }
                .evo-og-full { grid-column: 1/-1; }
                .evo-og-media-row { display:flex; align-items:center; gap:8px; }
                .evo-og-media-row img { max-height:40px; max-width:80px; object-fit:contain; border-radius:4px; border:1px solid #e5e7eb; }
            </style>

            <form method="post" action="options.php" id="evk-og-form">
                <?php settings_fields('evoke_one_og'); ?>

                <!-- USTAWIENIA GLOBALNE -->
                <div class="evo-og-section">
                    <h3>Ustawienia globalne</h3>

                    <?php if (!extension_loaded('imagick')): ?>
                    <div class="evo-info-box" style="border-color:#fca5a5;background:#fef2f2;margin-bottom:16px;">
                        <span class="dashicons dashicons-warning" style="color:#dc2626;"></span>
                        <div>Rozszerzenie <strong>Imagick</strong> nie jest zainstalowane na tym serwerze. Warstwy z plikami <strong>.svg</strong> będą pomijane podczas generowania. Pozostałe formaty (JPG, PNG, WebP, GIF) działają normalnie.</div>
                    </div>
                    <?php else: ?>
                    <div class="evo-info-box" style="border-color:#86efac;background:#f0fdf4;margin-bottom:16px;">
                        <span class="dashicons dashicons-yes-alt" style="color:#16a34a;"></span>
                        <div>Rozszerzenie <strong>Imagick</strong> jest dostępne — obsługa plików <strong>.svg</strong> aktywna.</div>
                    </div>
                    <?php endif; ?>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-bottom:16px;">

                        <div class="evo-field" style="margin-bottom:0;">
                            <label>Szerokość (px)</label>
                            <input type="number" name="evk_og[width]" value="<?php echo esc_attr($og['width']); ?>" min="400" max="2400">
                        </div>
                        <div class="evo-field" style="margin-bottom:0;">
                            <label>Wysokość (px)</label>
                            <input type="number" name="evk_og[height]" value="<?php echo esc_attr($og['height']); ?>" min="200" max="1400">
                        </div>
                        <div class="evo-field" style="margin-bottom:0;">
                            <label>Format</label>
                            <select name="evk_og[format]">
                                <?php foreach (['jpg' => 'JPG', 'png' => 'PNG', 'webp' => 'WebP'] as $val => $lbl): ?>
                                <option value="<?php echo $val; ?>" <?php selected($og['format'], $val); ?>><?php echo $lbl; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="evo-field" style="margin-bottom:0;">
                            <label>Jakość (JPG/WebP)</label>
                            <input type="number" name="evk_og[quality]" value="<?php echo esc_attr($og['quality']); ?>" min="10" max="100">
                        </div>
                    </div>

                    <div class="evo-field">
                        <label>Font (plik .ttf/.otf)</label>
                        <?php
                        $font_id = 0;
                        if (!empty($og['font_url'])) {
                            $font_id = attachment_url_to_postid($og['font_url']);
                        }
                        ?>
                        <div class="evo-og-media-row" id="evk-og-font-preview">
                            <?php if ($font_id): ?>
                                <span style="font-size:12px;color:#374151;"><?php echo esc_html(basename($og['font_url'])); ?></span>
                            <?php else: ?>
                                <span style="font-size:12px;color:#94a3b8;">Nie wybrano</span>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="evk_og[font_url]"  id="evk-og-font-url"  value="<?php echo esc_attr($og['font_url']); ?>">
                        <input type="hidden" name="evk_og[font_path]" id="evk-og-font-path" value="<?php echo esc_attr($og['font_path']); ?>">
                        <button type="button" class="button" style="margin-top:6px;" onclick="evkOgPickMedia('font', null, 'application')">Wybierz plik fontu</button>
                        <div class="evo-desc">Obsługiwane: .ttf, .otf — wgraj przez Bibliotekę mediów.</div>
                    </div>

                    <div class="evo-field" style="margin-bottom:0;">
                        <label>URL fallback (gdy brak miniatury)</label>
                        <input type="text" name="evk_og[fallback_url]" value="<?php echo esc_attr($og['fallback_url']); ?>" placeholder="https://twoja-domena.pl/wp-content/uploads/og-fallback.jpg" style="max-width:100%;">
                    </div>
                </div>

                <!-- POST TYPES -->
                <div class="evo-og-section">
                    <h3>Aktywne typy postów</h3>
                    <div style="display:flex;flex-wrap:wrap;gap:14px;">
                        <?php foreach ($all_post_types as $pt_slug => $pt_obj): ?>
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500;cursor:pointer;">
                            <input type="checkbox" name="evk_og[post_types][]" value="<?php echo esc_attr($pt_slug); ?>"
                                <?php checked(in_array($pt_slug, (array)$og['post_types'], true)); ?>>
                            <?php echo esc_html($pt_obj->labels->singular_name); ?>
                            <span style="color:#94a3b8;font-size:11px;">(<?php echo esc_html($pt_slug); ?>)</span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- WARSTWY -->
                <div class="evo-og-section">
                    <h3>Warstwy <span style="font-weight:400;font-size:11px;color:#6b7280;">(kolejność = kolejność renderowania — przeciągnij aby zmienić)</span></h3>

                    <div id="evk-og-layers-container">
                        <?php foreach ($og_layers as $li => $layer): ?>
                        <?php $type = $layer['type'] ?? 'rect'; ?>
                        <div class="evo-og-layer" data-index="<?php echo $li; ?>">
                            <div class="evo-og-layer-header">
                                <span class="drag-handle dashicons dashicons-menu"></span>
                                <label class="layer-toggle evo-toggle" style="margin:0;">
                                    <input type="checkbox" name="evk_og[layers][<?php echo $li; ?>][enabled]" value="1" <?php checked(!empty($layer['enabled'])); ?>>
                                    <span class="evo-slider"></span>
                                </label>
                                <span class="evo-og-layer-title"><?php echo esc_html($layer['label'] ?? $type); ?></span>
                                <span class="evo-og-layer-type-badge"><?php echo esc_html($layer_types[$type] ?? $type); ?></span>
                                <button type="button" class="evo-og-btn-remove" onclick="this.closest('.evo-og-layer').remove()">
                                    <span class="dashicons dashicons-trash" style="font-size:15px;width:15px;height:15px;"></span>
                                </button>
                            </div>

                            <input type="hidden" name="evk_og[layers][<?php echo $li; ?>][id]"    value="<?php echo esc_attr($layer['id']   ?? uniqid('l')); ?>">
                            <input type="hidden" name="evk_og[layers][<?php echo $li; ?>][type]"  value="<?php echo esc_attr($type); ?>">

                            <div class="evo-og-layer-fields">
                                <!-- Wspólne: label -->
                                <div>
                                    <label>Etykieta</label>
                                    <input type="text" name="evk_og[layers][<?php echo $li; ?>][label]" value="<?php echo esc_attr($layer['label'] ?? ''); ?>">
                                </div>

                                <!-- Pozycja X/Y (nie dla text bo ma y_from_bottom) -->
                                <?php if ($type !== 'text'): ?>
                                <div>
                                    <label>X (px)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][x]" value="<?php echo esc_attr($layer['x'] ?? 0); ?>">
                                </div>
                                <div>
                                    <label>Y (px)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][y]" value="<?php echo esc_attr($layer['y'] ?? 0); ?>">
                                </div>
                                <?php endif; ?>

                                <!-- Rozmiar W/H (nie dla photo jeśli 0) -->
                                <?php if (!in_array($type, ['text', 'qr'], true)): ?>
                                <div>
                                    <label>Szerokość (px, 0=auto)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][width]" value="<?php echo esc_attr($layer['width'] ?? 0); ?>">
                                </div>
                                <div>
                                    <label>Wysokość (px, 0=auto)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][height]" value="<?php echo esc_attr($layer['height'] ?? 0); ?>">
                                </div>
                                <?php endif; ?>

                                <!-- Opacity -->
                                <div>
                                    <label>Krycie (%)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][opacity]" value="<?php echo esc_attr($layer['opacity'] ?? 100); ?>" min="0" max="100">
                                </div>

                                <!-- Blend mode -->
                                <div>
                                    <label>Blend Mode</label>
                                    <select name="evk_og[layers][<?php echo $li; ?>][blend]">
                                        <?php foreach ($blend_modes as $bm): ?>
                                        <option value="<?php echo $bm; ?>" <?php selected($layer['blend'] ?? 'normal', $bm); ?>><?php echo $bm; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <?php if ($type === 'rect'): ?>
                                <div>
                                    <label>Kolor</label>
                                    <div class="evo-og-color-pair">
                                        <input type="color" value="<?php echo esc_attr($layer['color'] ?? '#000000'); ?>"
                                            oninput="this.nextElementSibling.value=this.value">
                                        <input type="text" name="evk_og[layers][<?php echo $li; ?>][color]"
                                            value="<?php echo esc_attr($layer['color'] ?? '#000000'); ?>"
                                            oninput="this.previousElementSibling.value=this.value"
                                            style="max-width:90px;font-family:monospace;">
                                    </div>
                                </div>

                                <?php elseif ($type === 'photo'): ?>
                                <div>
                                    <label>Przesunięcie X zdjęcia (px)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][offset_x]" value="<?php echo esc_attr($layer['offset_x'] ?? 0); ?>">
                                    <div style="font-size:11px;color:#6b7280;margin-top:3px;">Przesuwa kadrowanie w lewo/prawo.</div>
                                </div>

                                <?php elseif ($type === 'gradient'): ?>
                                <div>
                                    <label>Kolor</label>
                                    <div class="evo-og-color-pair">
                                        <input type="color" value="<?php echo esc_attr($layer['color'] ?? '#000000'); ?>"
                                            oninput="this.nextElementSibling.value=this.value">
                                        <input type="text" name="evk_og[layers][<?php echo $li; ?>][color]"
                                            value="<?php echo esc_attr($layer['color'] ?? '#000000'); ?>"
                                            oninput="this.previousElementSibling.value=this.value"
                                            style="max-width:90px;font-family:monospace;">
                                    </div>
                                </div>
                                <div>
                                    <label>Kierunek</label>
                                    <select name="evk_og[layers][<?php echo $li; ?>][direction]">
                                        <?php foreach (['top' => '↑ Górny', 'bottom' => '↓ Dolny', 'left' => '← Lewy', 'right' => '→ Prawy'] as $dv => $dl): ?>
                                        <option value="<?php echo $dv; ?>" <?php selected($layer['direction'] ?? 'bottom', $dv); ?>><?php echo $dl; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label>Alpha start (%)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][alpha_start]" value="<?php echo esc_attr($layer['alpha_start'] ?? 0); ?>" min="0" max="100">
                                </div>
                                <div>
                                    <label>Alpha end (%)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][alpha_end]" value="<?php echo esc_attr($layer['alpha_end'] ?? 100); ?>" min="0" max="100">
                                </div>
                                <div>
                                    <label>Pozycja startu (%)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][pos_pct]" value="<?php echo esc_attr($layer['pos_pct'] ?? 50); ?>" min="0" max="100">
                                    <div style="font-size:11px;color:#6b7280;margin-top:3px;">Gdzie gradient zaczyna się zanikać.</div>
                                </div>

                                <?php elseif ($type === 'image'): ?>
                                <div class="evo-og-full">
                                    <label>Obraz</label>
                                    <?php
                                    $img_id  = absint($layer['image_id'] ?? 0);
                                    $img_url = $img_id ? wp_get_attachment_image_url($img_id, 'thumbnail') : '';
                                    ?>
                                    <div class="evo-og-media-row" id="evk-og-img-preview-<?php echo $li; ?>">
                                        <?php if ($img_url): ?>
                                            <img src="<?php echo esc_url($img_url); ?>">
                                            <span style="font-size:12px;color:#374151;"><?php echo esc_html(get_the_title($img_id)); ?></span>
                                        <?php else: ?>
                                            <span style="font-size:12px;color:#94a3b8;">Nie wybrano</span>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" name="evk_og[layers][<?php echo $li; ?>][image_id]" id="evk-og-img-id-<?php echo $li; ?>" value="<?php echo esc_attr($img_id); ?>">
                                    <button type="button" class="button" style="margin-top:6px;" onclick="evkOgPickMedia('image', <?php echo $li; ?>)">Wybierz obraz</button>
                                </div>

                                <?php elseif ($type === 'text'): ?>
                                <div>
                                    <label>X (od lewej, px)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][x]" value="<?php echo esc_attr($layer['x'] ?? 275); ?>">
                                </div>
                                <div>
                                    <label>Y od dołu (px)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][y_from_bottom]" value="<?php echo esc_attr($layer['y_from_bottom'] ?? 120); ?>">
                                </div>
                                <div>
                                    <label>Maks. szerokość (px)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][max_width]" value="<?php echo esc_attr($layer['max_width'] ?? 900); ?>">
                                </div>
                                <div>
                                    <label>Rozmiar fontu (px)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][font_size]" value="<?php echo esc_attr($layer['font_size'] ?? 80); ?>">
                                </div>
                                <div>
                                    <label>Kolor tekstu</label>
                                    <div class="evo-og-color-pair">
                                        <input type="color" value="<?php echo esc_attr($layer['color'] ?? '#ffffff'); ?>"
                                            oninput="this.nextElementSibling.value=this.value">
                                        <input type="text" name="evk_og[layers][<?php echo $li; ?>][color]"
                                            value="<?php echo esc_attr($layer['color'] ?? '#ffffff'); ?>"
                                            oninput="this.previousElementSibling.value=this.value"
                                            style="max-width:90px;font-family:monospace;">
                                    </div>
                                </div>
                                <div class="evo-og-full" style="border-top:1px solid #e5e7eb;padding-top:10px;margin-top:4px;">
                                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500;margin-bottom:10px;cursor:pointer;">
                                        <input type="checkbox" name="evk_og[layers][<?php echo $li; ?>][shadow_enabled]" value="1" <?php checked(!empty($layer['shadow_enabled'])); ?>>
                                        Cień tekstu
                                    </label>
                                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;">
                                        <div>
                                            <label>Kolor cienia</label>
                                            <div class="evo-og-color-pair">
                                                <input type="color" value="<?php echo esc_attr($layer['shadow_color'] ?? '#000000'); ?>"
                                                    oninput="this.nextElementSibling.value=this.value">
                                                <input type="text" name="evk_og[layers][<?php echo $li; ?>][shadow_color]"
                                                    value="<?php echo esc_attr($layer['shadow_color'] ?? '#000000'); ?>"
                                                    oninput="this.previousElementSibling.value=this.value"
                                                    style="max-width:80px;font-family:monospace;">
                                            </div>
                                        </div>
                                        <div><label>Offset X (px)</label><input type="number" name="evk_og[layers][<?php echo $li; ?>][shadow_offset_x]" value="<?php echo esc_attr($layer['shadow_offset_x'] ?? 3); ?>"></div>
                                        <div><label>Offset Y (px)</label><input type="number" name="evk_og[layers][<?php echo $li; ?>][shadow_offset_y]" value="<?php echo esc_attr($layer['shadow_offset_y'] ?? 5); ?>"></div>
                                        <div><label>Alpha (%)</label><input type="number" name="evk_og[layers][<?php echo $li; ?>][shadow_alpha]" value="<?php echo esc_attr($layer['shadow_alpha'] ?? 50); ?>" min="0" max="100"></div>
                                        <div><label>Blur (px)</label><input type="number" name="evk_og[layers][<?php echo $li; ?>][shadow_blur]" value="<?php echo esc_attr($layer['shadow_blur'] ?? 2); ?>" min="0" max="20"></div>
                                    </div>
                                </div>

                                <?php elseif ($type === 'qr'): ?>
                                <div>
                                    <label>Margin prawy (px)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][x]" value="<?php echo esc_attr($layer['x'] ?? 25); ?>">
                                    <div style="font-size:11px;color:#6b7280;margin-top:3px;">X = odległość od prawej krawędzi.</div>
                                </div>
                                <div>
                                    <label>Y (od góry, px)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][y]" value="<?php echo esc_attr($layer['y'] ?? 426); ?>">
                                </div>
                                <div>
                                    <label>Rozmiar (px)</label>
                                    <input type="number" name="evk_og[layers][<?php echo $li; ?>][size]" value="<?php echo esc_attr($layer['size'] ?? 170); ?>" min="50" max="500">
                                </div>
                                <div>
                                    <label>Kolor kodu (fg)</label>
                                    <div class="evo-og-color-pair">
                                        <input type="color" value="<?php echo esc_attr($layer['fg_color'] ?? '#ffffff'); ?>"
                                            oninput="this.nextElementSibling.value=this.value">
                                        <input type="text" name="evk_og[layers][<?php echo $li; ?>][fg_color]"
                                            value="<?php echo esc_attr($layer['fg_color'] ?? '#ffffff'); ?>"
                                            oninput="this.previousElementSibling.value=this.value"
                                            style="max-width:80px;font-family:monospace;">
                                    </div>
                                </div>
                                <div>
                                    <label>Kolor tła (bg)</label>
                                    <div class="evo-og-color-pair">
                                        <input type="color" value="<?php echo esc_attr($layer['bg_color'] ?? '#000000'); ?>"
                                            oninput="this.nextElementSibling.value=this.value">
                                        <input type="text" name="evk_og[layers][<?php echo $li; ?>][bg_color]"
                                            value="<?php echo esc_attr($layer['bg_color'] ?? '#000000'); ?>"
                                            oninput="this.previousElementSibling.value=this.value"
                                            style="max-width:80px;font-family:monospace;">
                                    </div>
                                </div>
                                <?php endif; ?>

                            </div><!-- .evo-og-layer-fields -->
                        </div><!-- .evo-og-layer -->
                        <?php endforeach; ?>
                    </div><!-- #evk-og-layers-container -->

                    <!-- Dodaj warstwę -->
                    <div style="margin-top:14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <select id="evk-og-new-layer-type" style="min-width:220px;">
                            <?php foreach ($layer_types as $tv => $tl): ?>
                            <option value="<?php echo esc_attr($tv); ?>"><?php echo esc_html($tl); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="button" onclick="evkOgAddLayer()">+ Dodaj warstwę</button>
                    </div>
                </div>

                <!-- REGENERACJA MASOWA -->
                <div class="evo-og-section">
                    <h3>Narzędzia</h3>
                    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                        <button type="button" class="button" id="evk-og-regen-all" onclick="evkOgRegenAll()">
                            <span class="dashicons dashicons-update" style="font-size:16px;width:16px;height:16px;line-height:1;vertical-align:text-bottom;margin-right:4px;"></span>
                            Regeneruj wszystkie obrazy OG
                        </button>
                        <span id="evk-og-regen-result" class="evo-og-regen-result"></span>
                    </div>
                    <div class="evo-desc" style="margin-top:8px;">Przetworzy wszystkie opublikowane posty z przypisanymi miniaturami.</div>
                </div>

                <div class="evo-save-bar">
                    <?php submit_button('Zapisz ustawienia OpenGraph', 'primary', 'submit', false); ?>
                </div>
            </form>

            <script>
            (function ($) {
                /* ── Media picker ───────────────────────────────────────── */
                var evkOgMediaFrames = {};

                window.evkOgPickMedia = function (context, layerIndex, type) {
                    var frameKey = context + (layerIndex !== null ? layerIndex : '');

                    if (evkOgMediaFrames[frameKey]) {
                        evkOgMediaFrames[frameKey].open();
                        return;
                    }

                    var frame = wp.media({
                        title:    context === 'font' ? 'Wybierz plik fontu (.ttf / .otf)' : 'Wybierz obraz',
                        button:   { text: 'Wybierz' },
                        multiple: false,
                        library:  context === 'font' ? {} : { type: 'image' },
                    });

                    frame.on('select', function () {
                        var attachment = frame.state().get('selection').first().toJSON();

                        if (context === 'font') {
                            $('#evk-og-font-url').val(attachment.url);
                            $('#evk-og-font-path').val('');
                            $('#evk-og-font-preview').html('<span style="font-size:12px;color:#374151;">' + attachment.filename + '</span>');
                        } else {
                            $('#evk-og-img-id-' + layerIndex).val(attachment.id);
                            var thumb = attachment.sizes && attachment.sizes.thumbnail
                                        ? attachment.sizes.thumbnail.url : attachment.url;
                            $('#evk-og-img-preview-' + layerIndex).html(
                                '<img src="' + thumb + '" style="max-height:40px;max-width:80px;object-fit:contain;border-radius:4px;border:1px solid #e5e7eb;">' +
                                '<span style="font-size:12px;color:#374151;">' + attachment.filename + '</span>'
                            );
                        }
                    });

                    evkOgMediaFrames[frameKey] = frame;
                    frame.open();
                };

                /* ── Dodaj warstwę ──────────────────────────────────────── */
                var evkOgLayerCount = <?php echo count($og_layers); ?>;

                var evkOgLayerTemplates = {
                    rect: function (i) { return '<div><label>Kolor</label><div class="evo-og-color-pair"><input type="color" value="#000000" oninput="this.nextElementSibling.value=this.value"><input type="text" name="evk_og[layers]['+i+'][color]" value="#000000" oninput="this.previousElementSibling.value=this.value" style="max-width:90px;font-family:monospace;"></div></div>'; },
                    photo: function (i) { return '<div><label>Przesunięcie X (px)</label><input type="number" name="evk_og[layers]['+i+'][offset_x]" value="0"></div>'; },
                    gradient: function (i) { return '<div><label>Kolor</label><div class="evo-og-color-pair"><input type="color" value="#000000" oninput="this.nextElementSibling.value=this.value"><input type="text" name="evk_og[layers]['+i+'][color]" value="#000000" oninput="this.previousElementSibling.value=this.value" style="max-width:90px;font-family:monospace;"></div></div><div><label>Kierunek</label><select name="evk_og[layers]['+i+'][direction]"><option value="top">↑ Górny</option><option value="bottom" selected>↓ Dolny</option><option value="left">← Lewy</option><option value="right">→ Prawy</option></select></div><div><label>Alpha start (%)</label><input type="number" name="evk_og[layers]['+i+'][alpha_start]" value="0" min="0" max="100"></div><div><label>Alpha end (%)</label><input type="number" name="evk_og[layers]['+i+'][alpha_end]" value="100" min="0" max="100"></div><div><label>Pozycja startu (%)</label><input type="number" name="evk_og[layers]['+i+'][pos_pct]" value="50" min="0" max="100"></div>'; },
                    image: function (i) { return '<div class="evo-og-full"><label>Obraz</label><div class="evo-og-media-row" id="evk-og-img-preview-'+i+'"><span style="font-size:12px;color:#94a3b8;">Nie wybrano</span></div><input type="hidden" name="evk_og[layers]['+i+'][image_id]" id="evk-og-img-id-'+i+'" value="0"><button type="button" class="button" style="margin-top:6px;" onclick="evkOgPickMedia(\'image\','+i+')">Wybierz obraz</button></div>'; },
                    text: function (i) { return '<div><label>X od lewej (px)</label><input type="number" name="evk_og[layers]['+i+'][x]" value="275"></div><div><label>Y od dołu (px)</label><input type="number" name="evk_og[layers]['+i+'][y_from_bottom]" value="120"></div><div><label>Maks. szerokość</label><input type="number" name="evk_og[layers]['+i+'][max_width]" value="900"></div><div><label>Rozmiar fontu</label><input type="number" name="evk_og[layers]['+i+'][font_size]" value="80"></div><div><label>Kolor</label><div class="evo-og-color-pair"><input type="color" value="#ffffff" oninput="this.nextElementSibling.value=this.value"><input type="text" name="evk_og[layers]['+i+'][color]" value="#ffffff" oninput="this.previousElementSibling.value=this.value" style="max-width:90px;font-family:monospace;"></div></div>'; },
                    qr: function (i) { return '<div><label>Margin prawy (px)</label><input type="number" name="evk_og[layers]['+i+'][x]" value="25"></div><div><label>Y od góry (px)</label><input type="number" name="evk_og[layers]['+i+'][y]" value="426"></div><div><label>Rozmiar (px)</label><input type="number" name="evk_og[layers]['+i+'][size]" value="170" min="50" max="500"></div><div><label>Kolor kodu (fg)</label><div class="evo-og-color-pair"><input type="color" value="#ffffff" oninput="this.nextElementSibling.value=this.value"><input type="text" name="evk_og[layers]['+i+'][fg_color]" value="#ffffff" style="max-width:80px;font-family:monospace;"></div></div><div><label>Kolor tła (bg)</label><div class="evo-og-color-pair"><input type="color" value="#000000" oninput="this.nextElementSibling.value=this.value"><input type="text" name="evk_og[layers]['+i+'][bg_color]" value="#000000" style="max-width:80px;font-family:monospace;"></div></div>'; },
                };

                var typeLabels = <?php echo wp_json_encode(array_map('esc_html', $layer_types)); ?>;

                window.evkOgAddLayer = function () {
                    var type = document.getElementById('evk-og-new-layer-type').value;
                    var i    = evkOgLayerCount++;
                    var id   = 'layer_' + i;

                    var hasXY     = !['text'].includes(type);
                    var hasWH     = !['text', 'qr'].includes(type);
                    var typeFields = evkOgLayerTemplates[type] ? evkOgLayerTemplates[type](i) : '';

                    var html = '<div class="evo-og-layer" data-index="'+i+'">' +
                        '<div class="evo-og-layer-header">' +
                            '<span class="drag-handle dashicons dashicons-menu"></span>' +
                            '<label class="layer-toggle evo-toggle" style="margin:0;">' +
                                '<input type="checkbox" name="evk_og[layers]['+i+'][enabled]" value="1" checked>' +
                                '<span class="evo-slider"></span>' +
                            '</label>' +
                            '<span class="evo-og-layer-title">' + (typeLabels[type] || type) + '</span>' +
                            '<span class="evo-og-layer-type-badge">' + (typeLabels[type] || type) + '</span>' +
                            '<button type="button" class="evo-og-btn-remove" onclick="this.closest(\'.evo-og-layer\').remove()">' +
                                '<span class="dashicons dashicons-trash" style="font-size:15px;width:15px;height:15px;"></span>' +
                            '</button>' +
                        '</div>' +
                        '<input type="hidden" name="evk_og[layers]['+i+'][id]" value="'+id+'">' +
                        '<input type="hidden" name="evk_og[layers]['+i+'][type]" value="'+type+'">' +
                        '<div class="evo-og-layer-fields">' +
                            '<div><label>Etykieta</label><input type="text" name="evk_og[layers]['+i+'][label]" value="'+( typeLabels[type] || type )+'"></div>' +
                            (hasXY ? '<div><label>X (px)</label><input type="number" name="evk_og[layers]['+i+'][x]" value="0"></div><div><label>Y (px)</label><input type="number" name="evk_og[layers]['+i+'][y]" value="0"></div>' : '') +
                            (hasWH ? '<div><label>Szerokość (px, 0=auto)</label><input type="number" name="evk_og[layers]['+i+'][width]" value="0"></div><div><label>Wysokość (px, 0=auto)</label><input type="number" name="evk_og[layers]['+i+'][height]" value="0"></div>' : '') +
                            '<div><label>Krycie (%)</label><input type="number" name="evk_og[layers]['+i+'][opacity]" value="100" min="0" max="100"></div>' +
                            '<div><label>Blend Mode</label><select name="evk_og[layers]['+i+'][blend]"><option>normal</option><option>multiply</option><option>screen</option><option>overlay</option></select></div>' +
                            typeFields +
                        '</div>' +
                    '</div>';

                    document.getElementById('evk-og-layers-container').insertAdjacentHTML('beforeend', html);
                };

                /* ── Sortowanie warstw (drag & drop) ────────────────────── */
                if (typeof Sortable !== 'undefined') {
                    Sortable.create(document.getElementById('evk-og-layers-container'), {
                        handle: '.drag-handle',
                        animation: 150,
                        onEnd: function () {
                            document.querySelectorAll('#evk-og-layers-container .evo-og-layer').forEach(function (el, idx) {
                                el.querySelectorAll('[name]').forEach(function (inp) {
                                    inp.name = inp.name.replace(/\[layers\]\[\d+\]/, '[layers][' + idx + ']');
                                });
                                el.dataset.index = idx;
                            });
                        }
                    });
                }

                /* ── Regeneracja masowa ─────────────────────────────────── */
                window.evkOgRegenAll = function () {
                    var $btn = $('#evk-og-regen-all');
                    var $res = $('#evk-og-regen-result');
                    $btn.prop('disabled', true).text('Regeneruję…');
                    $res.hide();
                    $.post(ajaxurl, {
                        action: 'evk_og_regenerate_all',
                        nonce:  <?php echo wp_json_encode($og_nonce); ?>,
                    }).done(function (r) {
                        if (r.success) {
                            $res.text('Gotowe! Przetworzono: ' + r.data.count + ' wpisów.').show();
                        } else {
                            $res.text('Błąd: ' + (r.data || 'nieznany')).show();
                        }
                    }).fail(function () {
                        $res.text('Błąd połączenia.').show();
                    }).always(function () {
                        $btn.prop('disabled', false).html(
                            '<span class="dashicons dashicons-update" style="font-size:16px;width:16px;height:16px;line-height:1;vertical-align:text-bottom;margin-right:4px;"></span> Regeneruj wszystkie obrazy OG'
                        );
                    });
                };

            })(jQuery);
            </script>

            <?php // SortableJS i media załadowane w admin_enqueue_scripts ?>
