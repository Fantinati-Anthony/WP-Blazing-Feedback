<?php
/**
 * Trait pour l'enregistrement des paramètres admin
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait de gestion de l'enregistrement des paramètres
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Settings {

    /**
     * Enregistrer les paramètres
     *
     * @since 1.0.0
     * @return void
     */
    public static function register_settings() {
        // ========================================
        // Onglet Général
        // ========================================
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_screenshot_enabled',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => true,
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_guest_feedback',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => false,
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_position',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'bottom-right',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_panel_position',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'right',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_enabled_pages',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
                'default'           => '*',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_post_feedback_action',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'close',
            )
        );

        // ========================================
        // Onglet Graphisme
        // ========================================
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_color',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_hex_color',
                'default'           => '#FE5100',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_color_hover',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_hex_color',
                'default'           => '#E04800',
            )
        );

        // Style du bouton (collé ou séparé)
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_style',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'detached',
            )
        );

        // Forme pour bouton collé
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_attached_shape',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'quarter',
            )
        );

        // Border radius pour bouton séparé
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_border_radius',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 50,
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_border_radius_unit',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'percent',
            )
        );

        // Margin pour bouton séparé
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_margin',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 20,
            )
        );

        // Taille du bouton
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_size',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 56,
            )
        );

        // Mode du thème (système/clair/sombre)
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_theme_mode',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'system',
            )
        );

        // Type d'icône pour mode clair (emoji ou image)
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_light_icon_type',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'emoji',
            )
        );

        // Emoji pour mode clair
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_light_icon_emoji',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '💬',
            )
        );

        // Icône mode clair (par défaut ou personnalisée)
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_light_icon_url',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'default'           => '',
            )
        );

        // Type d'icône pour mode sombre (emoji ou image)
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_dark_icon_type',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'emoji',
            )
        );

        // Emoji pour mode sombre
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_dark_icon_emoji',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '💬',
            )
        );

        // Icône mode sombre (par défaut ou personnalisée)
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_dark_icon_url',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'default'           => '',
            )
        );

        // Couleurs du badge
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_badge_bg_color',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_hex_color',
                'default'           => '#263e4b',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_badge_text_color',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_hex_color',
                'default'           => '#ffffff',
            )
        );

        // Bordure du bouton
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_border_width',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 0,
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_border_color',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_hex_color',
                'default'           => '#ffffff',
            )
        );

        // Ombre du bouton
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_shadow_color',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_hex_color',
                'default'           => '#000000',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_shadow_blur',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 12,
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_shadow_opacity',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 15,
            )
        );

        // Logo du panneau mode clair
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_panel_logo_light_url',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'default'           => '',
            )
        );

        // Logo du panneau mode sombre
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_panel_logo_dark_url',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'default'           => '',
            )
        );

        // Couleurs du thème (palette: #FE5100, #263e4b, #ffffff)
        $theme_colors = array(
            // Couleurs communes
            'wpvfh_color_primary'           => '#FE5100',
            'wpvfh_color_primary_hover'     => '#E04800',
            'wpvfh_color_success'           => '#28a745',
            'wpvfh_color_warning'           => '#ffc107',
            'wpvfh_color_danger'            => '#dc3545',
            // Couleurs mode clair
            'wpvfh_color_secondary'         => '#263e4b',
            'wpvfh_color_text'              => '#263e4b',
            'wpvfh_color_text_light'        => '#5a7282',
            'wpvfh_color_bg'                => '#ffffff',
            'wpvfh_color_bg_light'          => '#f8f9fa',
            'wpvfh_color_border'            => '#e0e4e8',
            // Couleurs mode sombre
            'wpvfh_color_bg_dark'           => '#263e4b',
            'wpvfh_color_bg_light_dark'     => '#334a5a',
            'wpvfh_color_text_dark'         => '#ffffff',
            'wpvfh_color_text_light_dark'   => '#b0bcc4',
            'wpvfh_color_secondary_dark'    => '#4a6572',
            'wpvfh_color_border_dark'       => '#3d5564',
            // Couleurs footer mode clair
            'wpvfh_color_footer_bg'                  => '#f8f9fa',
            'wpvfh_color_footer_border'              => '#e9ecef',
            'wpvfh_color_footer_btn_add_bg'          => '#27ae60',
            'wpvfh_color_footer_btn_add_text'        => '#ffffff',
            'wpvfh_color_footer_btn_add_hover'       => '#219a52',
            'wpvfh_color_footer_btn_visibility_bg'   => '#3498db',
            'wpvfh_color_footer_btn_visibility_text' => '#ffffff',
            'wpvfh_color_footer_btn_visibility_hover'=> '#2980b9',
            // Couleurs footer mode sombre
            'wpvfh_color_footer_bg_dark'                  => '#1a2e38',
            'wpvfh_color_footer_border_dark'              => '#3d5564',
            'wpvfh_color_footer_btn_add_bg_dark'          => '#27ae60',
            'wpvfh_color_footer_btn_add_text_dark'        => '#ffffff',
            'wpvfh_color_footer_btn_add_hover_dark'       => '#219a52',
            'wpvfh_color_footer_btn_visibility_bg_dark'   => '#3498db',
            'wpvfh_color_footer_btn_visibility_text_dark' => '#ffffff',
            'wpvfh_color_footer_btn_visibility_hover_dark'=> '#2980b9',
        );

        foreach ( $theme_colors as $option_name => $default ) {
            register_setting(
                'wpvfh_general_settings',
                $option_name,
                array(
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_hex_color',
                    'default'           => $default,
                )
            );
        }

        // ========================================
        // Onglet Notifications
        // ========================================
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_email_notifications',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => true,
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_notification_email',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_email',
                'default'           => get_option( 'admin_email' ),
            )
        );

        // ========================================
        // Onglet IA
        // ========================================
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_ai_enabled',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => false,
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_ai_api_key',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_ai_system_prompt',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
                'default'           => '',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_ai_analysis_prompt',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
                'default'           => '',
            )
        );
    }

    /**
     * Gérer les actions de la zone de danger
     *
     * @since 1.7.0
     * @return void
     */
    public static function handle_danger_zone_actions() {
        // Vérifier si on est sur la bonne page et qu'il y a une action
        if ( ! isset( $_GET['page'] ) || 'wpvfh-settings' !== $_GET['page'] || ! isset( $_GET['action'] ) ) {
            return;
        }

        $action = sanitize_key( $_GET['action'] );
        $redirect_url = admin_url( 'admin.php?page=wpvfh-settings' );

        // Vérifier les permissions
        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_die( esc_html__( 'Vous n\'avez pas les permissions nécessaires.', 'blazing-feedback' ) );
        }

        switch ( $action ) {
            case 'truncate_feedbacks':
                // Vider les feedbacks et réponses
                if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'wpvfh_truncate_feedbacks' ) ) {
                    wp_die( esc_html__( 'Nonce invalide.', 'blazing-feedback' ) );
                }

                // Supprimer aussi les screenshots
                $upload_dir = wp_upload_dir();
                $feedback_dir = $upload_dir['basedir'] . '/visual-feedback';
                if ( file_exists( $feedback_dir ) ) {
                    self::delete_directory_contents( $feedback_dir );
                }

                WPVFH_Database::truncate_feedback_tables();
                $redirect_url = add_query_arg( 'message', 'feedbacks_truncated', $redirect_url );
                break;

            case 'truncate_all':
                // Vider toutes les tables
                if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'wpvfh_truncate_all' ) ) {
                    wp_die( esc_html__( 'Nonce invalide.', 'blazing-feedback' ) );
                }

                // Supprimer les screenshots
                $upload_dir = wp_upload_dir();
                $feedback_dir = $upload_dir['basedir'] . '/visual-feedback';
                if ( file_exists( $feedback_dir ) ) {
                    self::delete_directory_contents( $feedback_dir );
                }

                WPVFH_Database::truncate_all_tables();
                $redirect_url = add_query_arg( 'message', 'all_truncated', $redirect_url );
                break;

            case 'drop_tables':
                // Supprimer les tables
                if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'wpvfh_drop_tables' ) ) {
                    wp_die( esc_html__( 'Nonce invalide.', 'blazing-feedback' ) );
                }

                // Supprimer les screenshots
                $upload_dir = wp_upload_dir();
                $feedback_dir = $upload_dir['basedir'] . '/visual-feedback';
                if ( file_exists( $feedback_dir ) ) {
                    self::delete_directory_contents( $feedback_dir );
                }

                WPVFH_Database::uninstall();
                $redirect_url = add_query_arg( 'message', 'tables_dropped', $redirect_url );
                break;

            case 'recreate_tables':
                // Recréer les tables
                if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'wpvfh_recreate_tables' ) ) {
                    wp_die( esc_html__( 'Nonce invalide.', 'blazing-feedback' ) );
                }

                WPVFH_Database::install();
                $redirect_url = add_query_arg( 'message', 'tables_recreated', $redirect_url );
                break;

            default:
                return;
        }

        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Supprimer le contenu d'un répertoire (mais pas le répertoire lui-même)
     *
     * @since 1.7.0
     * @param string $dir Chemin du répertoire
     * @return void
     */
    private static function delete_directory_contents( $dir ) {
        if ( ! is_dir( $dir ) ) {
            return;
        }

        $files = array_diff( scandir( $dir ), array( '.', '..' ) );
        foreach ( $files as $file ) {
            $path = $dir . '/' . $file;
            if ( is_dir( $path ) ) {
                self::delete_directory_contents( $path );
                rmdir( $path );
            } else {
                unlink( $path );
            }
        }
    }
}
