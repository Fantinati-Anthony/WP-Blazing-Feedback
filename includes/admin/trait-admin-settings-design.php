<?php
/**
 * Trait pour l'onglet de personnalisation (Design)
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait de gestion de l'onglet Design et des couleurs
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Settings_Design {

    public static function render_tab_design() {
        // Récupération des options
        $theme_mode = get_option( 'wpvfh_theme_mode', 'system' );
        $light_icon_type = get_option( 'wpvfh_light_icon_type', 'emoji' );
        $light_icon_emoji = get_option( 'wpvfh_light_icon_emoji', '💬' );
        $light_icon_url = get_option( 'wpvfh_light_icon_url', '' );
        $dark_icon_type = get_option( 'wpvfh_dark_icon_type', 'emoji' );
        $dark_icon_emoji = get_option( 'wpvfh_dark_icon_emoji', '💬' );
        $dark_icon_url = get_option( 'wpvfh_dark_icon_url', '' );
        $button_color = get_option( 'wpvfh_button_color', '#FE5100' );
        $button_color_hover = get_option( 'wpvfh_button_color_hover', '#E04800' );
        $badge_bg_color = get_option( 'wpvfh_badge_bg_color', '#263e4b' );
        $badge_text_color = get_option( 'wpvfh_badge_text_color', '#ffffff' );
        $button_border_width = get_option( 'wpvfh_button_border_width', 0 );
        $button_border_color = get_option( 'wpvfh_button_border_color', '#ffffff' );
        $button_shadow_color = get_option( 'wpvfh_button_shadow_color', '#000000' );
        $button_shadow_blur = get_option( 'wpvfh_button_shadow_blur', 12 );
        $button_shadow_opacity = get_option( 'wpvfh_button_shadow_opacity', 15 );
        $button_style = get_option( 'wpvfh_button_style', 'detached' );
        $button_position = get_option( 'wpvfh_button_position', 'bottom-right' );
        $border_radius = get_option( 'wpvfh_button_border_radius', 50 );
        $border_radius_unit = get_option( 'wpvfh_button_border_radius_unit', 'percent' );
        $button_margin = get_option( 'wpvfh_button_margin', 20 );
        $button_size = get_option( 'wpvfh_button_size', 56 );

        // URLs des icônes/logos par défaut
        $default_light_icon = WPVFH_PLUGIN_URL . 'assets/logo/light-mode-feedback.png';
        $default_dark_icon = WPVFH_PLUGIN_URL . 'assets/logo/dark-mode-feedback.png';

        // Logos du panneau
        $panel_logo_light_url = get_option( 'wpvfh_panel_logo_light_url', '' );
        $panel_logo_dark_url = get_option( 'wpvfh_panel_logo_dark_url', '' );

        // Positions d'angle = quart de cercle, positions centrales = demi-cercle
        $corner_positions = array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' );
        $is_corner = in_array( $button_position, $corner_positions, true );
        ?>

        <!-- Mode du thème (pleine largeur) -->
        <div class="wpvfh-settings-section" style="margin-bottom: 30px;">
            <h2><?php esc_html_e( 'Mode d\'affichage', 'blazing-feedback' ); ?></h2>
            <p class="description"><?php esc_html_e( 'Choisissez le mode d\'affichage du widget. Le mode Système s\'adapte automatiquement aux préférences de l\'utilisateur.', 'blazing-feedback' ); ?></p>

            <div class="wpvfh-theme-mode-selector" style="display: flex; gap: 15px; margin: 20px 0;">
                <label class="wpvfh-mode-option <?php echo 'system' === $theme_mode ? 'active' : ''; ?>" style="flex: 1; padding: 15px; border: 2px solid <?php echo 'system' === $theme_mode ? '#FE5100' : '#ddd'; ?>; border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.2s;">
                    <input type="radio" name="wpvfh_theme_mode" value="system" <?php checked( $theme_mode, 'system' ); ?> style="display: none;">
                    <span style="font-size: 24px; display: block; margin-bottom: 5px;">🔄</span>
                    <strong><?php esc_html_e( 'Système', 'blazing-feedback' ); ?></strong>
                    <small style="display: block; color: #666; margin-top: 5px;"><?php esc_html_e( 'Auto dark/light', 'blazing-feedback' ); ?></small>
                </label>
                <label class="wpvfh-mode-option <?php echo 'light' === $theme_mode ? 'active' : ''; ?>" style="flex: 1; padding: 15px; border: 2px solid <?php echo 'light' === $theme_mode ? '#FE5100' : '#ddd'; ?>; border-radius: 8px; cursor: pointer; text-align: center; background: #fff; transition: all 0.2s;">
                    <input type="radio" name="wpvfh_theme_mode" value="light" <?php checked( $theme_mode, 'light' ); ?> style="display: none;">
                    <span style="font-size: 24px; display: block; margin-bottom: 5px;">☀️</span>
                    <strong><?php esc_html_e( 'Clair', 'blazing-feedback' ); ?></strong>
                    <small style="display: block; color: #666; margin-top: 5px;"><?php esc_html_e( 'Mode clair', 'blazing-feedback' ); ?></small>
                </label>
                <label class="wpvfh-mode-option <?php echo 'dark' === $theme_mode ? 'active' : ''; ?>" style="flex: 1; padding: 15px; border: 2px solid <?php echo 'dark' === $theme_mode ? '#FE5100' : '#ddd'; ?>; border-radius: 8px; cursor: pointer; text-align: center; background: #263e4b; color: #fff; transition: all 0.2s;">
                    <input type="radio" name="wpvfh_theme_mode" value="dark" <?php checked( $theme_mode, 'dark' ); ?> style="display: none;">
                    <span style="font-size: 24px; display: block; margin-bottom: 5px;">🌙</span>
                    <strong><?php esc_html_e( 'Sombre', 'blazing-feedback' ); ?></strong>
                    <small style="display: block; color: #b0bcc4; margin-top: 5px;"><?php esc_html_e( 'Mode sombre', 'blazing-feedback' ); ?></small>
                </label>
            </div>

            <!-- Icône ou logo selon le mode -->
            <div class="wpvfh-icon-settings" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                <h4 style="margin-top: 0;"><?php esc_html_e( 'Icône ou logo selon le mode', 'blazing-feedback' ); ?></h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Mode clair -->
                    <div style="padding: 15px; background: #fff; border-radius: 6px; border: 1px solid #e0e4e8;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                            <span style="font-size: 18px;">☀️</span>
                            <strong><?php esc_html_e( 'Mode clair', 'blazing-feedback' ); ?></strong>
                            <small style="color: #666;"><?php esc_html_e( '(fond #ffffff)', 'blazing-feedback' ); ?></small>
                        </div>

                        <!-- Icône du bouton -->
                        <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e0e4e8;">
                            <label style="display: block; font-weight: 500; margin-bottom: 8px;"><?php esc_html_e( 'Icône du bouton', 'blazing-feedback' ); ?></label>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <div id="wpvfh-light-preview-box" style="width: 44px; height: 44px; background: #FE5100; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff;">
                                    <?php if ( 'emoji' === $light_icon_type ) : ?>
                                        <span id="wpvfh-light-preview-content"><?php echo esc_html( $light_icon_emoji ); ?></span>
                                    <?php else : ?>
                                        <img src="<?php echo esc_url( $light_icon_url ? $light_icon_url : $default_light_icon ); ?>" alt="" style="max-width: 26px; max-height: 26px; filter: brightness(0) invert(1);" id="wpvfh-light-preview-content">
                                    <?php endif; ?>
                                </div>
                                <div style="margin-bottom: 0;">
                                    <label style="display: inline-flex; align-items: center; gap: 5px; margin-right: 10px; cursor: pointer;">
                                        <input type="radio" name="wpvfh_light_icon_type" value="emoji" <?php checked( $light_icon_type, 'emoji' ); ?>>
                                        <?php esc_html_e( 'Emoji', 'blazing-feedback' ); ?>
                                    </label>
                                    <label style="display: inline-flex; align-items: center; gap: 5px; cursor: pointer;">
                                        <input type="radio" name="wpvfh_light_icon_type" value="image" <?php checked( $light_icon_type, 'image' ); ?>>
                                        <?php esc_html_e( 'Image', 'blazing-feedback' ); ?>
                                    </label>
                                </div>
                            </div>
                            <div id="wpvfh-light-emoji-input" style="<?php echo 'image' === $light_icon_type ? 'display: none;' : ''; ?>">
                                <input type="text" name="wpvfh_light_icon_emoji" id="wpvfh_light_icon_emoji" value="<?php echo esc_attr( $light_icon_emoji ); ?>" style="width: 60px; font-size: 20px; text-align: center;" maxlength="4">
                            </div>
                            <div id="wpvfh-light-image-input" style="<?php echo 'emoji' === $light_icon_type ? 'display: none;' : ''; ?> display: flex; gap: 8px;">
                                <input type="text" name="wpvfh_light_icon_url" id="wpvfh_light_icon_url" value="<?php echo esc_attr( $light_icon_url ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'URL ou vide pour défaut', 'blazing-feedback' ); ?>" style="flex: 1;">
                                <button type="button" class="button wpvfh-select-icon" data-target="wpvfh_light_icon_url" data-mode="light"><?php esc_html_e( 'Bibliothèque', 'blazing-feedback' ); ?></button>
                            </div>
                        </div>

                        <!-- Logo du panneau -->
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 8px;"><?php esc_html_e( 'Logo du panneau', 'blazing-feedback' ); ?></label>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <div style="height: 36px; padding: 5px 10px; background: #f0f0f1; border-radius: 4px; display: flex; align-items: center;">
                                    <img src="<?php echo esc_url( $panel_logo_light_url ? $panel_logo_light_url : $default_light_icon ); ?>" alt="" style="max-height: 26px;" id="wpvfh-panel-logo-light-preview">
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <input type="text" name="wpvfh_panel_logo_light_url" id="wpvfh_panel_logo_light_url" value="<?php echo esc_attr( $panel_logo_light_url ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'URL ou vide pour light-mode-feedback.png', 'blazing-feedback' ); ?>" style="flex: 1;">
                                <button type="button" class="button wpvfh-select-panel-logo" data-target="wpvfh_panel_logo_light_url" data-preview="wpvfh-panel-logo-light-preview"><?php esc_html_e( 'Bibliothèque', 'blazing-feedback' ); ?></button>
                            </div>
                        </div>
                    </div>

                    <!-- Mode sombre -->
                    <div style="padding: 15px; background: #263e4b; border-radius: 6px; color: #fff;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                            <span style="font-size: 18px;">🌙</span>
                            <strong><?php esc_html_e( 'Mode sombre', 'blazing-feedback' ); ?></strong>
                            <small style="color: #b0bcc4;"><?php esc_html_e( '(fond #263E4B)', 'blazing-feedback' ); ?></small>
                        </div>

                        <!-- Icône du bouton -->
                        <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #3d5564;">
                            <label style="display: block; font-weight: 500; margin-bottom: 8px;"><?php esc_html_e( 'Icône du bouton', 'blazing-feedback' ); ?></label>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <div id="wpvfh-dark-preview-box" style="width: 44px; height: 44px; background: #FE5100; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff;">
                                    <?php if ( 'emoji' === $dark_icon_type ) : ?>
                                        <span id="wpvfh-dark-preview-content"><?php echo esc_html( $dark_icon_emoji ); ?></span>
                                    <?php else : ?>
                                        <img src="<?php echo esc_url( $dark_icon_url ? $dark_icon_url : $default_dark_icon ); ?>" alt="" style="max-width: 26px; max-height: 26px; filter: brightness(0) invert(1);" id="wpvfh-dark-preview-content">
                                    <?php endif; ?>
                                </div>
                                <div style="margin-bottom: 0;">
                                    <label style="display: inline-flex; align-items: center; gap: 5px; margin-right: 10px; cursor: pointer; color: #fff;">
                                        <input type="radio" name="wpvfh_dark_icon_type" value="emoji" <?php checked( $dark_icon_type, 'emoji' ); ?>>
                                        <?php esc_html_e( 'Emoji', 'blazing-feedback' ); ?>
                                    </label>
                                    <label style="display: inline-flex; align-items: center; gap: 5px; cursor: pointer; color: #fff;">
                                        <input type="radio" name="wpvfh_dark_icon_type" value="image" <?php checked( $dark_icon_type, 'image' ); ?>>
                                        <?php esc_html_e( 'Image', 'blazing-feedback' ); ?>
                                    </label>
                                </div>
                            </div>
                            <div id="wpvfh-dark-emoji-input" style="<?php echo 'image' === $dark_icon_type ? 'display: none;' : ''; ?>">
                                <input type="text" name="wpvfh_dark_icon_emoji" id="wpvfh_dark_icon_emoji" value="<?php echo esc_attr( $dark_icon_emoji ); ?>" style="width: 60px; font-size: 20px; text-align: center;" maxlength="4">
                            </div>
                            <div id="wpvfh-dark-image-input" style="<?php echo 'emoji' === $dark_icon_type ? 'display: none;' : ''; ?> display: flex; gap: 8px;">
                                <input type="text" name="wpvfh_dark_icon_url" id="wpvfh_dark_icon_url" value="<?php echo esc_attr( $dark_icon_url ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'URL ou vide pour défaut', 'blazing-feedback' ); ?>" style="flex: 1; background: #334a5a; border-color: #3d5564; color: #fff;">
                                <button type="button" class="button wpvfh-select-icon" data-target="wpvfh_dark_icon_url" data-mode="dark"><?php esc_html_e( 'Bibliothèque', 'blazing-feedback' ); ?></button>
                            </div>
                        </div>

                        <!-- Logo du panneau -->
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 8px;"><?php esc_html_e( 'Logo du panneau', 'blazing-feedback' ); ?></label>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <div style="height: 36px; padding: 5px 10px; background: #334a5a; border-radius: 4px; display: flex; align-items: center;">
                                    <img src="<?php echo esc_url( $panel_logo_dark_url ? $panel_logo_dark_url : $default_dark_icon ); ?>" alt="" style="max-height: 26px;" id="wpvfh-panel-logo-dark-preview">
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <input type="text" name="wpvfh_panel_logo_dark_url" id="wpvfh_panel_logo_dark_url" value="<?php echo esc_attr( $panel_logo_dark_url ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'URL ou vide pour dark-mode-feedback.png', 'blazing-feedback' ); ?>" style="flex: 1; background: #334a5a; border-color: #3d5564; color: #fff;">
                                <button type="button" class="button wpvfh-select-panel-logo" data-target="wpvfh_panel_logo_dark_url" data-preview="wpvfh-panel-logo-dark-preview"><?php esc_html_e( 'Bibliothèque', 'blazing-feedback' ); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Container avec prévisualisation à droite -->
        <div class="wpvfh-preview-container">
            <div class="wpvfh-preview-settings">
                <!-- Position -->
                <div class="wpvfh-settings-section">
                    <h2><?php esc_html_e( 'Position', 'blazing-feedback' ); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Position du bouton', 'blazing-feedback' ); ?></th>
                            <td>
                                <?php self::render_position_field(); ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Position du volet', 'blazing-feedback' ); ?></th>
                            <td>
                                <?php self::render_panel_position_field(); ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Bouton flottant -->
                <div class="wpvfh-settings-section">
                    <h2><?php esc_html_e( 'Bouton flottant', 'blazing-feedback' ); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Couleur du bouton', 'blazing-feedback' ); ?></th>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="color" name="wpvfh_button_color" id="wpvfh_button_color" value="<?php echo esc_attr( $button_color ); ?>">
                                    <input type="text" value="<?php echo esc_attr( $button_color ); ?>" class="wpvfh-color-hex-input" data-color-input="wpvfh_button_color" style="width: 80px; font-family: monospace;" maxlength="7">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Couleur du bouton au survol', 'blazing-feedback' ); ?></th>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="color" name="wpvfh_button_color_hover" id="wpvfh_button_color_hover" value="<?php echo esc_attr( $button_color_hover ); ?>">
                                    <input type="text" value="<?php echo esc_attr( $button_color_hover ); ?>" class="wpvfh-color-hex-input" data-color-input="wpvfh_button_color_hover" style="width: 80px; font-family: monospace;" maxlength="7">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Taille du bouton', 'blazing-feedback' ); ?></th>
                            <td>
                                <input type="range" name="wpvfh_button_size" id="wpvfh_button_size" min="40" max="80" value="<?php echo esc_attr( $button_size ); ?>" style="width: 150px; vertical-align: middle;">
                                <span id="wpvfh_button_size_value" style="margin-left: 10px; font-weight: 500;"><?php echo esc_html( $button_size ); ?>px</span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Style du bouton', 'blazing-feedback' ); ?></th>
                            <td>
                                <fieldset>
                                    <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; cursor: pointer;">
                                        <input type="radio" name="wpvfh_button_style" value="detached" <?php checked( $button_style, 'detached' ); ?>>
                                        <span style="display: flex; align-items: center; gap: 8px;">
                                            <span style="width: 32px; height: 32px; background: #FE5100; border-radius: 50%; display: inline-block;"></span>
                                            <span>
                                                <strong><?php esc_html_e( 'Séparé', 'blazing-feedback' ); ?></strong><br>
                                                <small style="color: #666;"><?php esc_html_e( 'Bouton flottant avec marge', 'blazing-feedback' ); ?></small>
                                            </span>
                                        </span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <input type="radio" name="wpvfh_button_style" value="attached" <?php checked( $button_style, 'attached' ); ?>>
                                        <span style="display: flex; align-items: center; gap: 8px;">
                                            <span id="wpvfh-attached-style-icon" style="width: 32px; height: 32px; background: #FE5100; border-radius: <?php echo $is_corner ? '0 0 0 16px' : '16px 0 0 16px'; ?>; display: inline-block;"></span>
                                            <span>
                                                <strong><?php esc_html_e( 'Collé', 'blazing-feedback' ); ?></strong><br>
                                                <small style="color: #666;" id="wpvfh-attached-style-desc">
                                                    <?php echo $is_corner ? esc_html__( 'Quart de cercle (position d\'angle)', 'blazing-feedback' ) : esc_html__( 'Demi-cercle (position centrale)', 'blazing-feedback' ); ?>
                                                </small>
                                            </span>
                                        </span>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                    </table>

                    <!-- Options pour bouton séparé -->
                    <div id="wpvfh-detached-options" style="<?php echo 'attached' === $button_style ? 'display: none;' : ''; ?> margin-left: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px; margin-top: 10px;">
                        <h4 style="margin-top: 0;"><?php esc_html_e( 'Options du bouton séparé', 'blazing-feedback' ); ?></h4>
                        <table class="form-table" style="margin: 0;">
                            <tr>
                                <th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Border radius', 'blazing-feedback' ); ?></th>
                                <td style="padding: 10px 0;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <input type="number" name="wpvfh_button_border_radius" id="wpvfh_button_border_radius" value="<?php echo esc_attr( $border_radius ); ?>" min="0" max="100" style="width: 70px;">
                                        <select name="wpvfh_button_border_radius_unit" id="wpvfh_button_border_radius_unit">
                                            <option value="percent" <?php selected( $border_radius_unit, 'percent' ); ?>>%</option>
                                            <option value="px" <?php selected( $border_radius_unit, 'px' ); ?>>px</option>
                                        </select>
                                        <input type="range" id="wpvfh_border_radius_slider" min="0" max="50" value="<?php echo esc_attr( $border_radius ); ?>" style="width: 100px;">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Marge', 'blazing-feedback' ); ?></th>
                                <td style="padding: 10px 0;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <input type="number" name="wpvfh_button_margin" id="wpvfh_button_margin" value="<?php echo esc_attr( $button_margin ); ?>" min="0" max="50" style="width: 70px;">
                                        <span>px</span>
                                        <input type="range" id="wpvfh_margin_slider" min="0" max="50" value="<?php echo esc_attr( $button_margin ); ?>" style="width: 100px;">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Bordure et ombre -->
                    <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
                        <h4 style="margin-top: 0;"><?php esc_html_e( 'Bordure et ombre', 'blazing-feedback' ); ?></h4>
                        <table class="form-table" style="margin: 0;">
                            <tr>
                                <th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Bordure', 'blazing-feedback' ); ?></th>
                                <td style="padding: 10px 0;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <input type="number" name="wpvfh_button_border_width" id="wpvfh_button_border_width" value="<?php echo esc_attr( $button_border_width ); ?>" min="0" max="10" style="width: 60px;">
                                        <span>px</span>
                                        <input type="color" name="wpvfh_button_border_color" id="wpvfh_button_border_color" value="<?php echo esc_attr( $button_border_color ); ?>">
                                        <input type="text" value="<?php echo esc_attr( $button_border_color ); ?>" class="wpvfh-color-hex-input" data-color-input="wpvfh_button_border_color" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Ombre', 'blazing-feedback' ); ?></th>
                                <td style="padding: 10px 0;">
                                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                        <label style="display: flex; align-items: center; gap: 5px;">
                                            <?php esc_html_e( 'Flou:', 'blazing-feedback' ); ?>
                                            <input type="number" name="wpvfh_button_shadow_blur" id="wpvfh_button_shadow_blur" value="<?php echo esc_attr( $button_shadow_blur ); ?>" min="0" max="50" style="width: 60px;">
                                            <span>px</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 5px;">
                                            <?php esc_html_e( 'Opacité:', 'blazing-feedback' ); ?>
                                            <input type="range" name="wpvfh_button_shadow_opacity" id="wpvfh_button_shadow_opacity" value="<?php echo esc_attr( $button_shadow_opacity ); ?>" min="0" max="100" style="width: 80px;">
                                            <span id="wpvfh_shadow_opacity_value"><?php echo esc_html( $button_shadow_opacity ); ?>%</span>
                                        </label>
                                        <input type="color" name="wpvfh_button_shadow_color" id="wpvfh_button_shadow_color" value="<?php echo esc_attr( $button_shadow_color ); ?>">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Couleurs du badge -->
                    <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
                        <h4 style="margin-top: 0;"><?php esc_html_e( 'Badge compteur', 'blazing-feedback' ); ?></h4>
                        <table class="form-table" style="margin: 0;">
                            <tr>
                                <th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Couleur de fond', 'blazing-feedback' ); ?></th>
                                <td style="padding: 10px 0;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <input type="color" name="wpvfh_badge_bg_color" id="wpvfh_badge_bg_color" value="<?php echo esc_attr( $badge_bg_color ); ?>">
                                        <input type="text" value="<?php echo esc_attr( $badge_bg_color ); ?>" class="wpvfh-color-hex-input" data-color-input="wpvfh_badge_bg_color" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Couleur du nombre', 'blazing-feedback' ); ?></th>
                                <td style="padding: 10px 0;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <input type="color" name="wpvfh_badge_text_color" id="wpvfh_badge_text_color" value="<?php echo esc_attr( $badge_text_color ); ?>">
                                        <input type="text" value="<?php echo esc_attr( $badge_text_color ); ?>" class="wpvfh-color-hex-input" data-color-input="wpvfh_badge_text_color" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Couleurs du thème -->
                <div class="wpvfh-settings-section">
                    <h2><?php esc_html_e( 'Couleurs du thème', 'blazing-feedback' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'Personnalisez les couleurs du widget de feedback selon le mode sélectionné.', 'blazing-feedback' ); ?></p>

                    <!-- Couleurs communes -->
                    <h4 style="margin: 15px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd;"><?php esc_html_e( 'Couleurs communes', 'blazing-feedback' ); ?></h4>
                    <div class="wpvfh-color-grid">
                        <?php
                        $common_colors = array(
                            'wpvfh_color_primary'       => __( 'Principale', 'blazing-feedback' ),
                            'wpvfh_color_primary_hover' => __( 'Principale (survol)', 'blazing-feedback' ),
                            'wpvfh_color_success'       => __( 'Succès', 'blazing-feedback' ),
                            'wpvfh_color_warning'       => __( 'Avertissement', 'blazing-feedback' ),
                            'wpvfh_color_danger'        => __( 'Danger', 'blazing-feedback' ),
                        );
                        $common_defaults = array(
                            'wpvfh_color_primary'       => '#FE5100',
                            'wpvfh_color_primary_hover' => '#E04800',
                            'wpvfh_color_success'       => '#28a745',
                            'wpvfh_color_warning'       => '#ffc107',
                            'wpvfh_color_danger'        => '#dc3545',
                        );
                        foreach ( $common_colors as $option_name => $label ) :
                            $value = get_option( $option_name, $common_defaults[ $option_name ] );
                        ?>
                        <div class="wpvfh-color-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #f9f9f9; border-radius: 4px;">
                            <input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
                            <input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
                            <span style="flex: 1; font-size: 13px;"><?php echo esc_html( $label ); ?></span>
                            <button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $common_defaults[ $option_name ] ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>">
                                <span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Couleurs Mode Clair -->
                    <h4 style="margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">☀️</span>
                        <?php esc_html_e( 'Mode Clair', 'blazing-feedback' ); ?>
                        <small style="color: #666; font-weight: normal;">(fond #ffffff)</small>
                    </h4>
                    <div class="wpvfh-color-grid">
                        <?php
                        $light_colors = array(
                            'wpvfh_color_bg'            => __( 'Fond principal', 'blazing-feedback' ),
                            'wpvfh_color_bg_light'      => __( 'Fond secondaire', 'blazing-feedback' ),
                            'wpvfh_color_text'          => __( 'Texte', 'blazing-feedback' ),
                            'wpvfh_color_text_light'    => __( 'Texte secondaire', 'blazing-feedback' ),
                            'wpvfh_color_secondary'     => __( 'Secondaire', 'blazing-feedback' ),
                            'wpvfh_color_border'        => __( 'Bordures', 'blazing-feedback' ),
                        );
                        $light_defaults = array(
                            'wpvfh_color_bg'            => '#ffffff',
                            'wpvfh_color_bg_light'      => '#f8f9fa',
                            'wpvfh_color_text'          => '#263e4b',
                            'wpvfh_color_text_light'    => '#5a7282',
                            'wpvfh_color_secondary'     => '#263e4b',
                            'wpvfh_color_border'        => '#e0e4e8',
                        );
                        foreach ( $light_colors as $option_name => $label ) :
                            $value = get_option( $option_name, $light_defaults[ $option_name ] );
                        ?>
                        <div class="wpvfh-color-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #f9f9f9; border-radius: 4px;">
                            <input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
                            <input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
                            <span style="flex: 1; font-size: 13px;"><?php echo esc_html( $label ); ?></span>
                            <button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $light_defaults[ $option_name ] ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>">
                                <span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Couleurs Mode Sombre -->
                    <h4 style="margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">🌙</span>
                        <?php esc_html_e( 'Mode Sombre', 'blazing-feedback' ); ?>
                        <small style="color: #666; font-weight: normal;">(fond #263E4B)</small>
                    </h4>
                    <div class="wpvfh-color-grid" style="background: #263e4b; padding: 10px; border-radius: 6px;">
                        <?php
                        $dark_colors = array(
                            'wpvfh_color_bg_dark'            => __( 'Fond principal', 'blazing-feedback' ),
                            'wpvfh_color_bg_light_dark'      => __( 'Fond secondaire', 'blazing-feedback' ),
                            'wpvfh_color_text_dark'          => __( 'Texte', 'blazing-feedback' ),
                            'wpvfh_color_text_light_dark'    => __( 'Texte secondaire', 'blazing-feedback' ),
                            'wpvfh_color_secondary_dark'     => __( 'Secondaire', 'blazing-feedback' ),
                            'wpvfh_color_border_dark'        => __( 'Bordures', 'blazing-feedback' ),
                        );
                        $dark_defaults = array(
                            'wpvfh_color_bg_dark'            => '#263e4b',
                            'wpvfh_color_bg_light_dark'      => '#334a5a',
                            'wpvfh_color_text_dark'          => '#ffffff',
                            'wpvfh_color_text_light_dark'    => '#b0bcc4',
                            'wpvfh_color_secondary_dark'     => '#4a6572',
                            'wpvfh_color_border_dark'        => '#3d5564',
                        );
                        foreach ( $dark_colors as $option_name => $label ) :
                            $value = get_option( $option_name, $dark_defaults[ $option_name ] );
                        ?>
                        <div class="wpvfh-color-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #334a5a; border-radius: 4px;">
                            <input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
                            <input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 70px; font-family: monospace; font-size: 12px; background: #263e4b; color: #fff; border-color: #3d5564;" maxlength="7">
                            <span style="flex: 1; font-size: 13px; color: #fff;"><?php echo esc_html( $label ); ?></span>
                            <button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $dark_defaults[ $option_name ] ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>" style="background: #4a6572; border-color: #5a7282; color: #fff;">
                                <span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Couleurs Footer Mode Clair -->
                    <h4 style="margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">📋</span>
                        <?php esc_html_e( 'Footer - Mode Clair', 'blazing-feedback' ); ?>
                        <small style="color: #666; font-weight: normal;">(fond blanc)</small>
                    </h4>
                    <div class="wpvfh-color-grid">
                        <?php
                        $footer_light_colors = array(
                            'wpvfh_color_footer_bg'                  => __( 'Fond du footer', 'blazing-feedback' ),
                            'wpvfh_color_footer_border'              => __( 'Bordure du footer', 'blazing-feedback' ),
                            'wpvfh_color_footer_btn_add_bg'          => __( 'Bouton Nouveau - Fond', 'blazing-feedback' ),
                            'wpvfh_color_footer_btn_add_text'        => __( 'Bouton Nouveau - Texte', 'blazing-feedback' ),
                            'wpvfh_color_footer_btn_add_hover'       => __( 'Bouton Nouveau - Survol', 'blazing-feedback' ),
                            'wpvfh_color_footer_btn_visibility_bg'   => __( 'Bouton Pins - Fond', 'blazing-feedback' ),
                            'wpvfh_color_footer_btn_visibility_text' => __( 'Bouton Pins - Texte', 'blazing-feedback' ),
                            'wpvfh_color_footer_btn_visibility_hover'=> __( 'Bouton Pins - Survol', 'blazing-feedback' ),
                        );
                        $footer_light_defaults = array(
                            'wpvfh_color_footer_bg'                  => '#f8f9fa',
                            'wpvfh_color_footer_border'              => '#e9ecef',
                            'wpvfh_color_footer_btn_add_bg'          => '#27ae60',
                            'wpvfh_color_footer_btn_add_text'        => '#ffffff',
                            'wpvfh_color_footer_btn_add_hover'       => '#219a52',
                            'wpvfh_color_footer_btn_visibility_bg'   => '#3498db',
                            'wpvfh_color_footer_btn_visibility_text' => '#ffffff',
                            'wpvfh_color_footer_btn_visibility_hover'=> '#2980b9',
                        );
                        foreach ( $footer_light_colors as $option_name => $label ) :
                            $value = get_option( $option_name, $footer_light_defaults[ $option_name ] );
                        ?>
                        <div class="wpvfh-color-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #f5f5f5; border-radius: 4px;">
                            <input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
                            <input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
                            <span style="flex: 1; font-size: 13px;"><?php echo esc_html( $label ); ?></span>
                            <button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $footer_light_defaults[ $option_name ] ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>">
                                <span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Couleurs Footer Mode Sombre -->
                    <h4 style="margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">📋</span>
                        <?php esc_html_e( 'Footer - Mode Sombre', 'blazing-feedback' ); ?>
                        <small style="color: #666; font-weight: normal;">(fond #263E4B)</small>
                    </h4>
                    <div class="wpvfh-color-grid" style="background: #263e4b; padding: 10px; border-radius: 6px;">
                        <?php
                        $footer_dark_colors = array(
                            'wpvfh_color_footer_bg_dark'                  => __( 'Fond du footer', 'blazing-feedback' ),
                            'wpvfh_color_footer_border_dark'              => __( 'Bordure du footer', 'blazing-feedback' ),
                            'wpvfh_color_footer_btn_add_bg_dark'          => __( 'Bouton Nouveau - Fond', 'blazing-feedback' ),
                            'wpvfh_color_footer_btn_add_text_dark'        => __( 'Bouton Nouveau - Texte', 'blazing-feedback' ),
                            'wpvfh_color_footer_btn_add_hover_dark'       => __( 'Bouton Nouveau - Survol', 'blazing-feedback' ),
                            'wpvfh_color_footer_btn_visibility_bg_dark'   => __( 'Bouton Pins - Fond', 'blazing-feedback' ),
                            'wpvfh_color_footer_btn_visibility_text_dark' => __( 'Bouton Pins - Texte', 'blazing-feedback' ),
                            'wpvfh_color_footer_btn_visibility_hover_dark'=> __( 'Bouton Pins - Survol', 'blazing-feedback' ),
                        );
                        $footer_dark_defaults = array(
                            'wpvfh_color_footer_bg_dark'                  => '#1a2e38',
                            'wpvfh_color_footer_border_dark'              => '#3d5564',
                            'wpvfh_color_footer_btn_add_bg_dark'          => '#27ae60',
                            'wpvfh_color_footer_btn_add_text_dark'        => '#ffffff',
                            'wpvfh_color_footer_btn_add_hover_dark'       => '#219a52',
                            'wpvfh_color_footer_btn_visibility_bg_dark'   => '#3498db',
                            'wpvfh_color_footer_btn_visibility_text_dark' => '#ffffff',
                            'wpvfh_color_footer_btn_visibility_hover_dark'=> '#2980b9',
                        );
                        foreach ( $footer_dark_colors as $option_name => $label ) :
                            $value = get_option( $option_name, $footer_dark_defaults[ $option_name ] );
                        ?>
                        <div class="wpvfh-color-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #334a5a; border-radius: 4px;">
                            <input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
                            <input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 70px; font-family: monospace; font-size: 12px; background: #263e4b; color: #fff; border-color: #3d5564;" maxlength="7">
                            <span style="flex: 1; font-size: 13px; color: #fff;"><?php echo esc_html( $label ); ?></span>
                            <button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $footer_dark_defaults[ $option_name ] ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>" style="background: #4a6572; border-color: #5a7282; color: #fff;">
                                <span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Prévisualisation -->
            <div class="wpvfh-preview-widget">
                <h3 style="margin-top: 0;"><?php esc_html_e( 'Prévisualisation', 'blazing-feedback' ); ?></h3>
                <div class="wpvfh-preview-box" id="wpvfh-preview-box">
                    <!-- Bouton de feedback preview -->
                    <div id="wpvfh-preview-button-wrapper">
                        <div id="wpvfh-preview-button" class="wpvfh-preview-btn">
                            <span id="wpvfh-preview-icon">
                                <?php if ( 'emoji' === $light_icon_type ) : ?>
                                    <span id="wpvfh-preview-icon-emoji"><?php echo esc_html( $light_icon_emoji ); ?></span>
                                <?php else : ?>
                                    <img src="<?php echo esc_url( $light_icon_url ? $light_icon_url : $default_light_icon ); ?>" alt="" id="wpvfh-preview-icon-img">
                                <?php endif; ?>
                            </span>
                        </div>
                        <!-- Compteur preview -->
                        <div id="wpvfh-preview-badge">3</div>
                    </div>
                </div>
                <p class="description" style="margin-top: 10px; text-align: center;">
                    <?php esc_html_e( 'Cliquez sur le bouton pour voir l\'effet', 'blazing-feedback' ); ?>
                </p>
            </div>
        </div>

        <style>
            .wpvfh-preview-box {
                background: #f0f0f1;
                border-radius: 8px;
                min-height: 350px;
                position: relative;
                overflow: hidden;
            }
            #wpvfh-preview-button-wrapper {
                position: absolute;
            }
            .wpvfh-preview-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s ease;
                background: <?php echo esc_attr( $button_color ); ?>;
            }
            .wpvfh-preview-btn:hover {
                filter: brightness(0.9);
            }
            .wpvfh-preview-btn.active {
                transform: rotate(45deg);
            }
            .wpvfh-preview-btn #wpvfh-preview-icon {
                font-size: 24px;
                color: #fff;
                transition: transform 0.2s ease;
                line-height: 1;
            }
            .wpvfh-preview-btn.active #wpvfh-preview-icon {
                transform: rotate(-45deg);
            }
            .wpvfh-preview-btn #wpvfh-preview-icon img {
                width: 28px;
                height: 28px;
                object-fit: contain;
                filter: brightness(0) invert(1);
            }
            #wpvfh-preview-badge {
                position: absolute;
                background: <?php echo esc_attr( $badge_bg_color ); ?>;
                color: <?php echo esc_attr( $badge_text_color ); ?>;
                font-size: 11px;
                font-weight: bold;
                padding: 2px 6px;
                border-radius: 10px;
                min-width: 18px;
                text-align: center;
                transition: all 0.2s ease;
            }
            .wpvfh-mode-option:hover {
                border-color: #FE5100 !important;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            var previewActive = false;
            var cornerPositions = ['bottom-right', 'bottom-left', 'top-right', 'top-left'];
            var defaultLightIcon = '<?php echo esc_js( $default_light_icon ); ?>';
            var defaultDarkIcon = '<?php echo esc_js( $default_dark_icon ); ?>';

            // Déterminer la forme selon la position
            function getShapeFromPosition() {
                var position = $('#wpvfh_button_position').val();
                return cornerPositions.indexOf(position) !== -1 ? 'quarter' : 'half';
            }

            // Mise à jour de l'icône et description du style "Collé"
            function updateAttachedStyleInfo() {
                var shape = getShapeFromPosition();
                var $icon = $('#wpvfh-attached-style-icon');
                var $desc = $('#wpvfh-attached-style-desc');

                if (shape === 'quarter') {
                    $icon.css({'border-radius': '0 0 0 16px', 'width': '32px'});
                    $desc.text('<?php echo esc_js( __( 'Quart de cercle (position d\'angle)', 'blazing-feedback' ) ); ?>');
                } else {
                    $icon.css({'border-radius': '16px 0 0 16px', 'width': '16px'});
                    $desc.text('<?php echo esc_js( __( 'Demi-cercle (position centrale)', 'blazing-feedback' ) ); ?>');
                }
            }

            // Convertir hex en rgba
            function hexToRgba(hex, opacity) {
                var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
                if (result) {
                    var r = parseInt(result[1], 16);
                    var g = parseInt(result[2], 16);
                    var b = parseInt(result[3], 16);
                    return 'rgba(' + r + ',' + g + ',' + b + ',' + (opacity / 100) + ')';
                }
                return 'rgba(0,0,0,' + (opacity / 100) + ')';
            }

            // Fonction de mise à jour du bouton preview
            function updateButtonPreview() {
                var style = $('input[name="wpvfh_button_style"]:checked').val();
                var position = $('#wpvfh_button_position').val() || 'bottom-right';
                var size = parseInt($('#wpvfh_button_size').val()) || 56;
                var color = $('#wpvfh_button_color').val() || '#FE5100';
                var borderWidth = parseInt($('#wpvfh_button_border_width').val()) || 0;
                var borderColor = $('#wpvfh_button_border_color').val() || '#ffffff';
                var shadowBlur = parseInt($('#wpvfh_button_shadow_blur').val()) || 12;
                var shadowOpacity = parseInt($('#wpvfh_button_shadow_opacity').val()) || 15;
                var shadowColor = $('#wpvfh_button_shadow_color').val() || '#000000';
                var badgeBgColor = $('#wpvfh_badge_bg_color').val() || '#263e4b';
                var badgeTextColor = $('#wpvfh_badge_text_color').val() || '#ffffff';

                var $btn = $('#wpvfh-preview-button');
                var $wrapper = $('#wpvfh-preview-button-wrapper');
                var $badge = $('#wpvfh-preview-badge');

                // Construire le box-shadow
                var boxShadow = '0 0 ' + shadowBlur + 'px ' + hexToRgba(shadowColor, shadowOpacity);

                // Construire la bordure
                var border = borderWidth > 0 ? borderWidth + 'px solid ' + borderColor : 'none';

                // Réinitialiser complètement les styles
                $wrapper.attr('style', 'position: absolute;');
                $btn.attr('style', '');

                // Mettre à jour les couleurs du badge
                $badge.css({
                    'background': badgeBgColor,
                    'color': badgeTextColor
                });

                if (style === 'attached') {
                    var shape = getShapeFromPosition();
                    var btnCss = {
                        'background': color,
                        'box-shadow': boxShadow,
                        'border': border,
                        'display': 'flex',
                        'align-items': 'center',
                        'justify-content': 'center',
                        'cursor': 'pointer',
                        'transition': 'all 0.2s ease'
                    };
                    var wrapperCss = {};
                    var badgeCss = { 'bottom': 'auto', 'top': 'auto', 'left': 'auto', 'right': 'auto', 'transform': 'none' };

                    if (shape === 'quarter') {
                        btnCss.width = size + 'px';
                        btnCss.height = size + 'px';

                        switch(position) {
                            case 'bottom-right':
                                btnCss['border-radius'] = size + 'px 0 0 0';
                                wrapperCss = { 'bottom': '0', 'right': '0' };
                                badgeCss = { 'top': '-8px', 'bottom': 'auto', 'left': '50%', 'right': 'auto', 'transform': 'translateX(-50%)' };
                                break;
                            case 'bottom-left':
                                btnCss['border-radius'] = '0 ' + size + 'px 0 0';
                                wrapperCss = { 'bottom': '0', 'left': '0' };
                                badgeCss = { 'top': '-8px', 'bottom': 'auto', 'left': '50%', 'right': 'auto', 'transform': 'translateX(-50%)' };
                                break;
                            case 'top-right':
                                btnCss['border-radius'] = '0 0 0 ' + size + 'px';
                                wrapperCss = { 'top': '0', 'right': '0' };
                                badgeCss = { 'top': 'auto', 'bottom': '-8px', 'left': '50%', 'right': 'auto', 'transform': 'translateX(-50%)' };
                                break;
                            case 'top-left':
                                btnCss['border-radius'] = '0 0 ' + size + 'px 0';
                                wrapperCss = { 'top': '0', 'left': '0' };
                                badgeCss = { 'top': 'auto', 'bottom': '-8px', 'left': '50%', 'right': 'auto', 'transform': 'translateX(-50%)' };
                                break;
                        }
                    } else {
                        var halfSize = size / 2;

                        switch(position) {
                            case 'bottom-center':
                                btnCss.width = size + 'px';
                                btnCss.height = halfSize + 'px';
                                btnCss['border-radius'] = size + 'px ' + size + 'px 0 0';
                                wrapperCss = { 'bottom': '0', 'left': '50%', 'transform': 'translateX(-50%)' };
                                badgeCss = { 'top': '-8px', 'bottom': 'auto', 'left': '50%', 'right': 'auto', 'transform': 'translateX(-50%)' };
                                break;
                            case 'top-center':
                                btnCss.width = size + 'px';
                                btnCss.height = halfSize + 'px';
                                btnCss['border-radius'] = '0 0 ' + size + 'px ' + size + 'px';
                                wrapperCss = { 'top': '0', 'left': '50%', 'transform': 'translateX(-50%)' };
                                badgeCss = { 'top': 'auto', 'bottom': '-8px', 'left': '50%', 'right': 'auto', 'transform': 'translateX(-50%)' };
                                break;
                            case 'middle-left':
                                btnCss.width = halfSize + 'px';
                                btnCss.height = size + 'px';
                                btnCss['border-radius'] = '0 ' + size + 'px ' + size + 'px 0';
                                wrapperCss = { 'top': '50%', 'left': '0', 'transform': 'translateY(-50%)' };
                                badgeCss = { 'top': '-8px', 'bottom': 'auto', 'left': 'auto', 'right': '-8px', 'transform': 'none' };
                                break;
                            case 'middle-right':
                                btnCss.width = halfSize + 'px';
                                btnCss.height = size + 'px';
                                btnCss['border-radius'] = size + 'px 0 0 ' + size + 'px';
                                wrapperCss = { 'top': '50%', 'right': '0', 'transform': 'translateY(-50%)' };
                                badgeCss = { 'top': '-8px', 'bottom': 'auto', 'left': '-8px', 'right': 'auto', 'transform': 'none' };
                                break;
                        }
                    }

                    $btn.css(btnCss);
                    $wrapper.css(wrapperCss);
                    $badge.attr('style', '').css(badgeCss);
                } else {
                    var radius = $('#wpvfh_button_border_radius').val() || 50;
                    var unit = $('#wpvfh_button_border_radius_unit').val() || 'percent';
                    var margin = parseInt($('#wpvfh_button_margin').val()) || 20;
                    var radiusValue = radius + (unit === 'percent' ? '%' : 'px');

                    $btn.css({
                        'width': size + 'px',
                        'height': size + 'px',
                        'border-radius': radiusValue,
                        'box-shadow': boxShadow,
                        'border': border,
                        'background': color,
                        'display': 'flex',
                        'align-items': 'center',
                        'justify-content': 'center',
                        'cursor': 'pointer',
                        'transition': 'all 0.2s ease'
                    });

                    var wrapperCss = {};
                    var badgeCss = { 'top': '-5px', 'right': '-5px', 'left': 'auto', 'bottom': 'auto', 'transform': 'none' };

                    switch(position) {
                        case 'bottom-right':
                            wrapperCss = { 'bottom': margin + 'px', 'right': margin + 'px' };
                            break;
                        case 'bottom-left':
                            wrapperCss = { 'bottom': margin + 'px', 'left': margin + 'px' };
                            break;
                        case 'top-right':
                            wrapperCss = { 'top': margin + 'px', 'right': margin + 'px' };
                            break;
                        case 'top-left':
                            wrapperCss = { 'top': margin + 'px', 'left': margin + 'px' };
                            break;
                        case 'bottom-center':
                            wrapperCss = { 'bottom': margin + 'px', 'left': '50%', 'transform': 'translateX(-50%)' };
                            break;
                        case 'top-center':
                            wrapperCss = { 'top': margin + 'px', 'left': '50%', 'transform': 'translateX(-50%)' };
                            break;
                        case 'middle-left':
                            wrapperCss = { 'top': '50%', 'left': margin + 'px', 'transform': 'translateY(-50%)' };
                            break;
                        case 'middle-right':
                            wrapperCss = { 'top': '50%', 'right': margin + 'px', 'transform': 'translateY(-50%)' };
                            break;
                    }

                    $wrapper.css(wrapperCss);
                    $badge.attr('style', '').css(badgeCss);
                }
            }

            // Toggle du bouton preview
            $('#wpvfh-preview-button').on('click', function() {
                previewActive = !previewActive;
                $(this).toggleClass('active', previewActive);
            });

            // Sélecteur de mode thème
            $('input[name="wpvfh_theme_mode"]').on('change', function() {
                $('.wpvfh-mode-option').css('border-color', '#ddd');
                $(this).closest('.wpvfh-mode-option').css('border-color', '#FE5100');
                updateMainIconPreview();
            });

            // Changement de position (input caché mis à jour par le sélecteur de grille)
            $('#wpvfh_button_position').on('change', function() {
                updateAttachedStyleInfo();
                updateButtonPreview();
            });

            // Style du bouton (collé/séparé)
            $('input[name="wpvfh_button_style"]').on('change', function() {
                var style = $(this).val();
                if (style === 'attached') {
                    $('#wpvfh-detached-options').slideUp();
                } else {
                    $('#wpvfh-detached-options').slideDown();
                }
                updateButtonPreview();
            });

            // Taille du bouton
            $('#wpvfh_button_size').on('input', function() {
                $('#wpvfh_button_size_value').text($(this).val() + 'px');
                updateButtonPreview();
            });

            // Bordure et ombre
            $('#wpvfh_button_border_width, #wpvfh_button_border_color, #wpvfh_button_shadow_blur, #wpvfh_button_shadow_color').on('input change', function() {
                updateButtonPreview();
            });

            // Opacité de l'ombre
            $('#wpvfh_button_shadow_opacity').on('input', function() {
                $('#wpvfh_shadow_opacity_value').text($(this).val() + '%');
                updateButtonPreview();
            });

            // Couleurs du badge
            $('#wpvfh_badge_bg_color, #wpvfh_badge_text_color').on('input change', function() {
                updateButtonPreview();
            });

            // Border radius
            $('#wpvfh_button_border_radius, #wpvfh_border_radius_slider').on('input', function() {
                var val = $(this).val();
                $('#wpvfh_button_border_radius').val(val);
                $('#wpvfh_border_radius_slider').val(val);
                updateButtonPreview();
            });
            $('#wpvfh_button_border_radius_unit').on('change', function() {
                updateButtonPreview();
            });

            // Margin
            $('#wpvfh_button_margin, #wpvfh_margin_slider').on('input', function() {
                var val = $(this).val();
                $('#wpvfh_button_margin').val(val);
                $('#wpvfh_margin_slider').val(val);
                updateButtonPreview();
            });

            // Couleur du bouton
            $('input[name="wpvfh_button_color"]').on('input change', function() {
                updateButtonPreview();
            });

            // Synchroniser les inputs couleur et texte
            $('input[type="color"]').on('input change', function() {
                var optionName = $(this).attr('name');
                var hexInput = $('[data-color-input="' + optionName + '"]');
                hexInput.val($(this).val());
                updatePreview();
            });

            // Synchroniser le texte vers l'input couleur
            $('.wpvfh-color-hex-input').on('input change', function() {
                var optionName = $(this).data('color-input');
                var colorInput = $('#' + optionName);
                var value = $(this).val();
                if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                    colorInput.val(value);
                    updatePreview();
                }
            });

            // Réinitialiser la couleur par défaut
            $('.wpvfh-reset-color').on('click', function() {
                var optionName = $(this).data('option');
                var defaultValue = $(this).data('default');
                var colorInput = $('#' + optionName);
                var hexInput = $('[data-color-input="' + optionName + '"]');
                colorInput.val(defaultValue);
                hexInput.val(defaultValue);
                updatePreview();
                updateButtonPreview();
            });

            // Toggle emoji/image pour mode clair
            $('input[name="wpvfh_light_icon_type"]').on('change', function() {
                var type = $(this).val();
                if (type === 'emoji') {
                    $('#wpvfh-light-emoji-input').show();
                    $('#wpvfh-light-image-input').hide();
                } else {
                    $('#wpvfh-light-emoji-input').hide();
                    $('#wpvfh-light-image-input').show();
                }
                updateLightIconPreview();
                updateMainIconPreview();
            });

            // Toggle emoji/image pour mode sombre
            $('input[name="wpvfh_dark_icon_type"]').on('change', function() {
                var type = $(this).val();
                if (type === 'emoji') {
                    $('#wpvfh-dark-emoji-input').show();
                    $('#wpvfh-dark-image-input').hide();
                } else {
                    $('#wpvfh-dark-emoji-input').hide();
                    $('#wpvfh-dark-image-input').show();
                }
                updateDarkIconPreview();
                updateMainIconPreview();
            });

            // Mise à jour aperçu icône mode clair
            function updateLightIconPreview() {
                var type = $('input[name="wpvfh_light_icon_type"]:checked').val() || 'emoji';
                var $previewBox = $('#wpvfh-light-preview-box');

                if (type === 'emoji') {
                    var emoji = $('#wpvfh_light_icon_emoji').val() || '💬';
                    $previewBox.html('<span id="wpvfh-light-preview-content">' + emoji + '</span>');
                } else {
                    var url = $('#wpvfh_light_icon_url').val() || defaultLightIcon;
                    $previewBox.html('<img src="' + url + '" style="max-width: 26px; max-height: 26px; filter: brightness(0) invert(1);" id="wpvfh-light-preview-content">');
                }
            }

            // Mise à jour aperçu icône mode sombre
            function updateDarkIconPreview() {
                var type = $('input[name="wpvfh_dark_icon_type"]:checked').val() || 'emoji';
                var $previewBox = $('#wpvfh-dark-preview-box');

                if (type === 'emoji') {
                    var emoji = $('#wpvfh_dark_icon_emoji').val() || '💬';
                    $previewBox.html('<span id="wpvfh-dark-preview-content">' + emoji + '</span>');
                } else {
                    var url = $('#wpvfh_dark_icon_url').val() || defaultDarkIcon;
                    $previewBox.html('<img src="' + url + '" style="max-width: 26px; max-height: 26px; filter: brightness(0) invert(1);" id="wpvfh-dark-preview-content">');
                }
            }

            // Mise à jour icône principale (preview du bouton)
            function updateMainIconPreview() {
                var themeMode = $('input[name="wpvfh_theme_mode"]:checked').val() || 'system';
                var $iconContainer = $('#wpvfh-preview-icon');

                // Déterminer quel mode utiliser pour l'aperçu
                var useLight = (themeMode === 'light' || themeMode === 'system');

                if (useLight) {
                    var type = $('input[name="wpvfh_light_icon_type"]:checked').val() || 'emoji';
                    if (type === 'emoji') {
                        var emoji = $('#wpvfh_light_icon_emoji').val() || '💬';
                        $iconContainer.html('<span id="wpvfh-preview-icon-emoji">' + emoji + '</span>');
                    } else {
                        var url = $('#wpvfh_light_icon_url').val() || defaultLightIcon;
                        $iconContainer.html('<img src="' + url + '" id="wpvfh-preview-icon-img">');
                    }
                } else {
                    var type = $('input[name="wpvfh_dark_icon_type"]:checked').val() || 'emoji';
                    if (type === 'emoji') {
                        var emoji = $('#wpvfh_dark_icon_emoji').val() || '💬';
                        $iconContainer.html('<span id="wpvfh-preview-icon-emoji">' + emoji + '</span>');
                    } else {
                        var url = $('#wpvfh_dark_icon_url').val() || defaultDarkIcon;
                        $iconContainer.html('<img src="' + url + '" id="wpvfh-preview-icon-img">');
                    }
                }
            }

            // Changement d'emoji
            $('#wpvfh_light_icon_emoji').on('input', function() {
                updateLightIconPreview();
                updateMainIconPreview();
            });
            $('#wpvfh_dark_icon_emoji').on('input', function() {
                updateDarkIconPreview();
                updateMainIconPreview();
            });

            // Changement d'URL d'image
            $('#wpvfh_light_icon_url').on('input change', function() {
                updateLightIconPreview();
                updateMainIconPreview();
            });
            $('#wpvfh_dark_icon_url').on('input change', function() {
                updateDarkIconPreview();
                updateMainIconPreview();
            });

            // Sélection d'icône via la bibliothèque de médias
            $('.wpvfh-select-icon').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var targetId = $button.data('target');
                var mode = $button.data('mode');

                var frame = wp.media({
                    title: '<?php echo esc_js( __( 'Sélectionner une icône', 'blazing-feedback' ) ); ?>',
                    button: { text: '<?php echo esc_js( __( 'Utiliser cette image', 'blazing-feedback' ) ); ?>' },
                    multiple: false,
                    library: { type: 'image' }
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#' + targetId).val(attachment.url).trigger('change');
                    if (mode === 'light') {
                        updateLightIconPreview();
                    } else {
                        updateDarkIconPreview();
                    }
                    updateMainIconPreview();
                });

                frame.open();
            });

            // Sélection de logo panneau via la bibliothèque de médias
            $('.wpvfh-select-panel-logo').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var targetId = $button.data('target');
                var previewId = $button.data('preview');

                var frame = wp.media({
                    title: '<?php echo esc_js( __( 'Sélectionner un logo pour le panneau', 'blazing-feedback' ) ); ?>',
                    button: { text: '<?php echo esc_js( __( 'Utiliser ce logo', 'blazing-feedback' ) ); ?>' },
                    multiple: false,
                    library: { type: 'image' }
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#' + targetId).val(attachment.url);
                    $('#' + previewId).attr('src', attachment.url);
                });

                frame.open();
            });

            // Mise à jour preview logo panneau lors du changement d'URL
            $('#wpvfh_panel_logo_light_url').on('input change', function() {
                var url = $(this).val() || defaultLightIcon;
                $('#wpvfh-panel-logo-light-preview').attr('src', url);
            });

            $('#wpvfh_panel_logo_dark_url').on('input change', function() {
                var url = $(this).val() || defaultDarkIcon;
                $('#wpvfh-panel-logo-dark-preview').attr('src', url);
            });

            function updatePreview() {
                var bgLight = $('#wpvfh_color_bg_light').val();
                $('#wpvfh-preview-box').css('background', bgLight);
                updateButtonPreview();
            }

            // Initialisation
            updateAttachedStyleInfo();
            updateButtonPreview();
            updateLightIconPreview();
            updateDarkIconPreview();
            updateMainIconPreview();
        });
        </script>
        <?php
    }

    /**
     * Obtenir les couleurs du thème personnalisées
     *
     * @since 1.8.0
     * @return array
     */
    public static function get_theme_colors() {
        return array(
            'primary'       => get_option( 'wpvfh_color_primary', '#e74c3c' ),
            'primary_hover' => get_option( 'wpvfh_color_primary_hover', '#c0392b' ),
            'secondary'     => get_option( 'wpvfh_color_secondary', '#3498db' ),
            'success'       => get_option( 'wpvfh_color_success', '#27ae60' ),
            'warning'       => get_option( 'wpvfh_color_warning', '#f39c12' ),
            'danger'        => get_option( 'wpvfh_color_danger', '#e74c3c' ),
            'text'          => get_option( 'wpvfh_color_text', '#333333' ),
            'text_light'    => get_option( 'wpvfh_color_text_light', '#666666' ),
            'bg'            => get_option( 'wpvfh_color_bg', '#ffffff' ),
            'bg_light'      => get_option( 'wpvfh_color_bg_light', '#f5f5f5' ),
            'border'        => get_option( 'wpvfh_color_border', '#dddddd' ),
        );
    }

    /**
     * Générer le CSS inline pour les couleurs personnalisées
     *
     * @since 1.8.0
     * @return string
     */
    public static function get_custom_colors_css() {
        $colors = self::get_theme_colors();

        // Vérifier si des couleurs ont été personnalisées
        $defaults = array(
            'primary'       => '#e74c3c',
            'primary_hover' => '#c0392b',
            'secondary'     => '#3498db',
            'success'       => '#27ae60',
            'warning'       => '#f39c12',
            'danger'        => '#e74c3c',
            'text'          => '#333333',
            'text_light'    => '#666666',
            'bg'            => '#ffffff',
            'bg_light'      => '#f5f5f5',
            'border'        => '#dddddd',
        );

        $has_custom = false;
        foreach ( $colors as $key => $value ) {
            if ( strtolower( $value ) !== strtolower( $defaults[ $key ] ) ) {
                $has_custom = true;
                break;
            }
        }

        if ( ! $has_custom ) {
            return '';
        }

        $css = ':root {';
        $css .= '--wpvfh-primary: ' . esc_attr( $colors['primary'] ) . ';';
        $css .= '--wpvfh-primary-hover: ' . esc_attr( $colors['primary_hover'] ) . ';';
        $css .= '--wpvfh-secondary: ' . esc_attr( $colors['secondary'] ) . ';';
        $css .= '--wpvfh-success: ' . esc_attr( $colors['success'] ) . ';';
        $css .= '--wpvfh-warning: ' . esc_attr( $colors['warning'] ) . ';';
        $css .= '--wpvfh-danger: ' . esc_attr( $colors['danger'] ) . ';';
        $css .= '--wpvfh-text: ' . esc_attr( $colors['text'] ) . ';';
        $css .= '--wpvfh-text-light: ' . esc_attr( $colors['text_light'] ) . ';';
        $css .= '--wpvfh-bg: ' . esc_attr( $colors['bg'] ) . ';';
        $css .= '--wpvfh-bg-light: ' . esc_attr( $colors['bg_light'] ) . ';';
        $css .= '--wpvfh-border: ' . esc_attr( $colors['border'] ) . ';';
        $css .= '}';

        return $css;
    }
}
