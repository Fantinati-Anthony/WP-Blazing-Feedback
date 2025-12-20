<?php
/**
 * Trait pour les champs de paramètres admin
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait de gestion des champs de paramètres
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Settings_Fields {

    /**
     * Rendu de la section générale (legacy - unused)
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_general_section() {
        // Legacy function - kept for backwards compatibility
    }

    /**
     * Rendu de la section notifications (legacy - unused)
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_notification_section() {
        // Legacy function - kept for backwards compatibility
    }

    /**
     * Rendu de la section logo (legacy - unused)
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_logo_section() {
        // Legacy function - kept for backwards compatibility
    }

    /**
     * Rendu de la section icône du bouton (legacy - unused)
     *
     * @since 1.7.0
     * @return void
     */
    public static function render_icon_section() {
        // Legacy function - kept for backwards compatibility
    }

    /**
     * Champ Mode de l'icône
     *
     * @since 1.7.0
     * @return void
     */
    public static function render_icon_mode_field() {
        $mode = WPVFH_Database::get_setting( 'wpvfh_icon_mode', 'emoji' );
        $emoji = WPVFH_Database::get_setting( 'wpvfh_icon_emoji', '💬' );
        $image_url = WPVFH_Database::get_setting( 'wpvfh_icon_image_url', '' );
        ?>
        <fieldset>
            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                <input type="radio" name="wpvfh_icon_mode" value="emoji" <?php checked( $mode, 'emoji' ); ?>>
                <?php esc_html_e( 'Emoji personnalisé', 'blazing-feedback' ); ?>
            </label>

            <div id="wpvfh-emoji-wrapper" style="margin-left: 24px; margin-bottom: 20px; <?php echo $mode !== 'emoji' ? 'display: none;' : ''; ?>">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" name="wpvfh_icon_emoji" id="wpvfh_icon_emoji"
                           value="<?php echo esc_attr( $emoji ); ?>"
                           style="width: 60px; text-align: center; font-size: 24px;"
                           maxlength="4"
                           placeholder="💬">
                    <span class="description"><?php esc_html_e( 'Entrez un emoji', 'blazing-feedback' ); ?></span>
                </div>
                <div style="margin-top: 10px;">
                    <span class="description"><?php esc_html_e( 'Suggestions :', 'blazing-feedback' ); ?></span>
                    <div style="display: flex; gap: 8px; margin-top: 5px; flex-wrap: wrap;">
                        <?php
                        $suggestions = array( '💬', '💭', '✨', '📝', '🔔', '💡', '❓', '🎯', '📌', '🗣️', '👋', '🚀' );
                        foreach ( $suggestions as $suggestion ) :
                        ?>
                        <button type="button" class="button wpvfh-emoji-suggestion" data-emoji="<?php echo esc_attr( $suggestion ); ?>" style="font-size: 18px; padding: 2px 8px;">
                            <?php echo esc_html( $suggestion ); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                <input type="radio" name="wpvfh_icon_mode" value="image" <?php checked( $mode, 'image' ); ?>>
                <?php esc_html_e( 'Image personnalisée', 'blazing-feedback' ); ?>
            </label>

            <div id="wpvfh-image-wrapper" style="margin-left: 24px; <?php echo $mode !== 'image' ? 'display: none;' : ''; ?>">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="wpvfh_icon_image_url" id="wpvfh_icon_image_url"
                           value="<?php echo esc_attr( $image_url ); ?>"
                           class="regular-text"
                           placeholder="<?php esc_attr_e( 'URL de l\'image ou sélectionner depuis la bibliothèque', 'blazing-feedback' ); ?>">
                    <button type="button" class="button" id="wpvfh-select-icon-btn">
                        <?php esc_html_e( 'Bibliothèque', 'blazing-feedback' ); ?>
                    </button>
                </div>
                <?php if ( $image_url ) : ?>
                <div style="margin-top: 10px;">
                    <img src="<?php echo esc_url( $image_url ); ?>" alt="Preview" style="max-height: 40px; background: #f0f0f0; padding: 5px; border-radius: 4px;">
                </div>
                <?php endif; ?>
                <p class="description" style="margin-top: 8px;">
                    <?php esc_html_e( 'Recommandé : image carrée, 64x64px minimum, fond transparent (PNG ou SVG).', 'blazing-feedback' ); ?>
                </p>
            </div>
        </fieldset>

        <script>
        jQuery(document).ready(function($) {
            // Toggle icon input sections
            $('input[name="wpvfh_icon_mode"]').on('change', function() {
                const mode = $(this).val();
                if (mode === 'emoji') {
                    $('#wpvfh-emoji-wrapper').slideDown();
                    $('#wpvfh-image-wrapper').slideUp();
                } else {
                    $('#wpvfh-emoji-wrapper').slideUp();
                    $('#wpvfh-image-wrapper').slideDown();
                }
            });

            // Emoji suggestions
            $('.wpvfh-emoji-suggestion').on('click', function() {
                const emoji = $(this).data('emoji');
                $('#wpvfh_icon_emoji').val(emoji);
            });

            // Media library for icon
            $('#wpvfh-select-icon-btn').on('click', function(e) {
                e.preventDefault();
                var frame = wp.media({
                    title: '<?php echo esc_js( __( 'Sélectionner une icône', 'blazing-feedback' ) ); ?>',
                    button: { text: '<?php echo esc_js( __( 'Utiliser cette image', 'blazing-feedback' ) ); ?>' },
                    multiple: false,
                    library: { type: 'image' }
                });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#wpvfh_icon_image_url').val(attachment.url);
                });
                frame.open();
            });
        });
        </script>
        <?php
    }

    /**
     * Champ Screenshot
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_screenshot_field() {
        $value = WPVFH_Database::get_setting( 'wpvfh_screenshot_enabled', true );
        ?>
        <label>
            <input type="checkbox" name="wpvfh_screenshot_enabled" value="1" <?php checked( $value, true ); ?>>
            <?php esc_html_e( 'Activer la capture d\'écran automatique', 'blazing-feedback' ); ?>
        </label>
        <p class="description">
            <?php esc_html_e( 'Utilise html2canvas pour capturer la page lors de la création d\'un feedback.', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Champ Feedback anonyme
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_guest_field() {
        $value = WPVFH_Database::get_setting( 'wpvfh_guest_feedback', false );
        ?>
        <label>
            <input type="checkbox" name="wpvfh_guest_feedback" value="1" <?php checked( $value, true ); ?>>
            <?php esc_html_e( 'Autoriser les feedbacks des visiteurs non connectés', 'blazing-feedback' ); ?>
        </label>
        <p class="description">
            <?php esc_html_e( 'Attention : cela peut générer du spam. Utilisez avec précaution.', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Champ Activer sur le back-office
     *
     * @since 1.10.0
     * @return void
     */
    public static function render_enable_admin_field() {
        $value = WPVFH_Database::get_setting( 'wpvfh_enable_admin', false );
        ?>
        <label>
            <input type="checkbox" name="wpvfh_enable_admin" value="1" <?php checked( $value, true ); ?>>
            <?php esc_html_e( 'Activer le widget de feedback dans l\'administration WordPress', 'blazing-feedback' ); ?>
        </label>
        <p class="description">
            <?php esc_html_e( 'Permet d\'utiliser le widget de feedback directement dans le back-office WordPress.', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Champ Action après ajout d'un feedback
     *
     * @since 1.9.0
     * @return void
     */
    public static function render_post_feedback_action_field() {
        $value = WPVFH_Database::get_setting( 'wpvfh_post_feedback_action', 'close' );
        ?>
        <fieldset>
            <label style="display: block; margin-bottom: 8px;">
                <input type="radio" name="wpvfh_post_feedback_action" value="close" <?php checked( $value, 'close' ); ?>>
                <?php esc_html_e( 'Fermer le volet latéral', 'blazing-feedback' ); ?>
            </label>
            <label style="display: block;">
                <input type="radio" name="wpvfh_post_feedback_action" value="list" <?php checked( $value, 'list' ); ?>>
                <?php esc_html_e( 'Ouvrir la liste des feedbacks', 'blazing-feedback' ); ?>
            </label>
        </fieldset>
        <p class="description">
            <?php esc_html_e( 'Choisissez ce qui se passe après l\'envoi d\'un feedback.', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Champ Position du bouton
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_position_field() {
        $value = WPVFH_Database::get_setting( 'wpvfh_button_position', 'bottom-right' );
        $positions = array(
            'top-left'      => __( 'Haut gauche', 'blazing-feedback' ),
            'top-center'    => __( 'Haut centre', 'blazing-feedback' ),
            'top-right'     => __( 'Haut droite', 'blazing-feedback' ),
            'middle-left'   => __( 'Milieu gauche', 'blazing-feedback' ),
            'middle-right'  => __( 'Milieu droite', 'blazing-feedback' ),
            'bottom-left'   => __( 'Bas gauche', 'blazing-feedback' ),
            'bottom-center' => __( 'Bas centre', 'blazing-feedback' ),
            'bottom-right'  => __( 'Bas droite', 'blazing-feedback' ),
        );
        ?>
        <input type="hidden" name="wpvfh_button_position" id="wpvfh_button_position" value="<?php echo esc_attr( $value ); ?>">

        <div class="wpvfh-position-selector">
            <div class="wpvfh-position-grid">
                <!-- Ligne du haut -->
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'top-left' ? 'active' : ''; ?>" data-position="top-left" title="<?php echo esc_attr( $positions['top-left'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'top-center' ? 'active' : ''; ?>" data-position="top-center" title="<?php echo esc_attr( $positions['top-center'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'top-right' ? 'active' : ''; ?>" data-position="top-right" title="<?php echo esc_attr( $positions['top-right'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>

                <!-- Ligne du milieu -->
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'middle-left' ? 'active' : ''; ?>" data-position="middle-left" title="<?php echo esc_attr( $positions['middle-left'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>
                <div class="wpvfh-position-preview">
                    <span>📄</span>
                </div>
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'middle-right' ? 'active' : ''; ?>" data-position="middle-right" title="<?php echo esc_attr( $positions['middle-right'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>

                <!-- Ligne du bas -->
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'bottom-left' ? 'active' : ''; ?>" data-position="bottom-left" title="<?php echo esc_attr( $positions['bottom-left'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'bottom-center' ? 'active' : ''; ?>" data-position="bottom-center" title="<?php echo esc_attr( $positions['bottom-center'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'bottom-right' ? 'active' : ''; ?>" data-position="bottom-right" title="<?php echo esc_attr( $positions['bottom-right'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>
            </div>
            <p class="description" style="margin-top: 10px;">
                <?php esc_html_e( 'Cliquez pour choisir la position du bouton.', 'blazing-feedback' ); ?>
            </p>
        </div>

        <style>
            .wpvfh-position-selector {
                max-width: 200px;
            }
            .wpvfh-position-grid {
                display: grid;
                grid-template-columns: 40px 1fr 40px;
                grid-template-rows: 40px 60px 40px;
                gap: 4px;
                background: #f0f0f1;
                border: 2px solid #c3c4c7;
                border-radius: 8px;
                padding: 4px;
            }
            .wpvfh-position-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fff;
                border: 2px solid #dcdcde;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s ease;
                padding: 0;
            }
            .wpvfh-position-btn:hover {
                border-color: #2271b1;
                background: #f0f7fc;
            }
            .wpvfh-position-btn.active {
                border-color: #2271b1;
                background: #2271b1;
            }
            .wpvfh-position-btn.active .wpvfh-position-dot {
                background: #fff;
            }
            .wpvfh-position-dot {
                width: 12px;
                height: 12px;
                background: #c3c4c7;
                border-radius: 50%;
                transition: all 0.2s ease;
            }
            .wpvfh-position-btn:hover .wpvfh-position-dot {
                background: #2271b1;
            }
            .wpvfh-position-preview {
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fff;
                border: 1px dashed #c3c4c7;
                border-radius: 4px;
                font-size: 24px;
                color: #787c82;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const buttons = document.querySelectorAll('.wpvfh-position-btn');
                const input = document.getElementById('wpvfh_button_position');

                buttons.forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        buttons.forEach(function(b) { b.classList.remove('active'); });
                        this.classList.add('active');
                        input.value = this.dataset.position;
                        // Déclencher un événement pour mettre à jour la prévisualisation
                        jQuery(input).trigger('change');
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * Champ Position du volet
     *
     * @since 1.7.0
     * @return void
     */
    public static function render_panel_position_field() {
        $value = WPVFH_Database::get_setting( 'wpvfh_panel_position', 'right' );
        ?>
        <fieldset>
            <label style="display: inline-flex; align-items: center; gap: 5px; margin-right: 20px;">
                <input type="radio" name="wpvfh_panel_position" value="left" <?php checked( $value, 'left' ); ?>>
                <span style="display: inline-flex; align-items: center; gap: 5px;">
                    <svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="opacity: 0.7;">
                        <rect x="0.5" y="0.5" width="19" height="15" rx="1.5" stroke="currentColor"/>
                        <rect x="1" y="1" width="6" height="14" fill="currentColor" opacity="0.3"/>
                    </svg>
                    <?php esc_html_e( 'Gauche', 'blazing-feedback' ); ?>
                </span>
            </label>
            <label style="display: inline-flex; align-items: center; gap: 5px;">
                <input type="radio" name="wpvfh_panel_position" value="right" <?php checked( $value, 'right' ); ?>>
                <span style="display: inline-flex; align-items: center; gap: 5px;">
                    <svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="opacity: 0.7;">
                        <rect x="0.5" y="0.5" width="19" height="15" rx="1.5" stroke="currentColor"/>
                        <rect x="13" y="1" width="6" height="14" fill="currentColor" opacity="0.3"/>
                    </svg>
                    <?php esc_html_e( 'Droite', 'blazing-feedback' ); ?>
                </span>
            </label>
        </fieldset>
        <p class="description">
            <?php esc_html_e( 'Position du volet latéral de feedback.', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Champ Couleur du bouton
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_color_field() {
        $value = WPVFH_Database::get_setting( 'wpvfh_button_color', '#e74c3c' );
        ?>
        <input type="color" name="wpvfh_button_color" value="<?php echo esc_attr( $value ); ?>">
        <p class="description">
            <?php esc_html_e( 'Couleur du bouton de feedback.', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Champ Pages actives
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_pages_field() {
        $value = WPVFH_Database::get_setting( 'wpvfh_enabled_pages', '*' );
        ?>
        <textarea name="wpvfh_enabled_pages" rows="4" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
        <p class="description">
            <?php esc_html_e( 'Entrez * pour toutes les pages, ou listez les URLs (une par ligne). Ex: /contact, /produits/*', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Champ Notifications email
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_email_notifications_field() {
        $value = WPVFH_Database::get_setting( 'wpvfh_email_notifications', true );
        ?>
        <label>
            <input type="checkbox" name="wpvfh_email_notifications" value="1" <?php checked( $value, true ); ?>>
            <?php esc_html_e( 'Envoyer un email lors d\'un nouveau feedback', 'blazing-feedback' ); ?>
        </label>
        <?php
    }

    /**
     * Champ Email de notification
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_notification_email_field() {
        $value = WPVFH_Database::get_setting( 'wpvfh_notification_email', get_option( 'admin_email' ) );
        ?>
        <input type="email" name="wpvfh_notification_email" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
        <p class="description">
            <?php esc_html_e( 'Adresse email pour recevoir les notifications.', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Rendu de la section couleurs
     *
     * @since 1.8.0
     * @return void
     */
    public static function render_colors_section() {
        echo '<p>' . esc_html__( 'Personnalisez les couleurs du widget de feedback.', 'blazing-feedback' ) . '</p>';
    }

    /**
     * Champ couleur du thème générique
     *
     * @since 1.8.0
     * @param array $args Arguments du champ
     * @return void
     */
    public static function render_theme_color_field( $args ) {
        $option_name = $args['option_name'];
        $default = $args['default'];
        $value = get_option( $option_name, $default );
        ?>
        <div style="display: flex; align-items: center; gap: 10px;">
            <input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
            <input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 80px; font-family: monospace;" maxlength="7">
            <button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $default ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>">
                <span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px;"></span>
            </button>
        </div>
        <?php
    }
}
