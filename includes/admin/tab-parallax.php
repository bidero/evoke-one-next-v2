<?php
if (!defined('ABSPATH')) exit;
/**
 * Evoke ONE — Tab: parallax
 */
?>
<form method="post" action="options.php">
                <?php settings_fields('evoke_one_parallax'); ?>

                <p class="evo-section-title">Ustawienia efektu Parallax</p>
                <div class="evo-info-box">
                    <span class="dashicons dashicons-info"></span>
                    <div>Użyj <code>{evk_parallax}</code> i <code>{evk_parallax_scale}</code> w atrybutach elementów Bricks Builder lub jako <code>data-parallax</code> / <code>data-skala</code> na dowolnym elemencie HTML.</div>
                </div>

                <div class="evo-field">
                    <label>Intensywność parallax</label>
                    <div class="evo-slider-wrap">
                        <div class="evo-slider-track">
                            <div class="evo-slider-fill" id="fill-parallax"></div>
                            <input type="range" class="evo-range" id="evk_parallax_value" name="evk_parallax_value" min="-1" max="1" step="0.05" value="<?php echo esc_attr($parallax_value); ?>">
                            <div class="evo-slider-thumb" id="thumb-parallax"></div>
                        </div>
                        <span class="evo-slider-value" id="value-parallax"><?php echo esc_html($parallax_value); ?></span>
                    </div>
                    <div class="evo-desc">Zakres: -1 (odwrócony) do 1 (maksymalny). Domyślnie: 0.3</div>
                </div>

                <div class="evo-field">
                    <label>Skalowanie obrazu</label>
                    <div class="evo-slider-wrap">
                        <div class="evo-slider-track">
                            <div class="evo-slider-fill" id="fill-scale"></div>
                            <input type="range" class="evo-range" id="evk_parallax_scale" name="evk_parallax_scale" min="1" max="2" step="0.05" value="<?php echo esc_attr($scale_value); ?>">
                            <div class="evo-slider-thumb" id="thumb-scale"></div>
                        </div>
                        <span class="evo-slider-value" id="value-scale"><?php echo esc_html($scale_value); ?></span>
                    </div>
                    <div class="evo-desc">Zakres: 1 (bez skalowania) do 2 (dwukrotne). Domyślnie: 1.2</div>
                </div>

                <div class="evo-save-bar">
                    <?php submit_button('Zapisz ustawienia parallax', 'primary', 'submit', false); ?>
                </div>
            </form>
