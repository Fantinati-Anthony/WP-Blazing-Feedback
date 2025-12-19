<?php
/**
 * Enqueue scripts/styles frontend et admin
 * 
 * Reference file for blazing-feedback.php lines 220-460
 * See main file: blazing-feedback.php
 * 
 * @package Blazing_Feedback
 */

// To view this section, read blazing-feedback.php with:
// offset=220, limit=241

            'warning'       => get_option( 'wpvfh_color_warning', '#f39c12' ),
            'danger'        => get_option( 'wpvfh_color_danger', '#e74c3c' ),
            'text'          => get_option( 'wpvfh_color_text', '#333333' ),
            'text_light'    => get_option( 'wpvfh_color_text_light', '#666666' ),
            'bg'            => get_option( 'wpvfh_color_bg', '#ffffff' ),
            'bg_light'      => get_option( 'wpvfh_color_bg_light', '#f5f5f5' ),
            'border'        => get_option( 'wpvfh_color_border', '#dddddd' ),
        );

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

    /**
     * Créer le dossier d'upload pour les screenshots
     *
     * @since 1.0.0
     * @return void
     */
    private function create_upload_directory() {
        $upload_dir = wp_upload_dir();
        $feedback_dir = $upload_dir['basedir'] . '/visual-feedback';

        if ( ! file_exists( $feedback_dir ) ) {
            wp_mkdir_p( $feedback_dir );

            // Créer un fichier index.php pour la sécurité
            $index_file = $feedback_dir . '/index.php';
            if ( ! file_exists( $index_file ) ) {
                file_put_contents( $index_file, '<?php // Silence is golden.' );
            }

            // Créer un .htaccess pour protéger le dossier
            $htaccess_file = $feedback_dir . '/.htaccess';
            if ( ! file_exists( $htaccess_file ) ) {
                $htaccess_content = "Options -Indexes\n<FilesMatch '\.(php|php\.)$'>\nOrder Allow,Deny\nDeny from all\n</FilesMatch>";
                file_put_contents( $htaccess_file, $htaccess_content );
            }
        }
    }

    /**
     * Charger les fichiers de traduction
     *
     * @since 1.0.0
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'blazing-feedback',
            false,
            dirname( WPVFH_PLUGIN_BASENAME ) . '/languages'
        );
    }

    /**
     * Charger les assets frontend
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_frontend_assets() {
        // Vérifier si l'utilisateur peut voir le widget de feedback
        if ( ! $this->can_user_see_feedback_widget() ) {
            return;
        }

        // html2canvas pour les captures d'écran
        wp_enqueue_script(
            'html2canvas',
            WPVFH_PLUGIN_URL . 'assets/vendor/html2canvas.min.js',
            array(),
            '1.4.1',
            true
        );

        // Screenshot handler
        wp_enqueue_script(
            'wpvfh-screenshot',
            WPVFH_PLUGIN_URL . 'assets/js/screenshot.js',
            array( 'html2canvas' ),
            WPVFH_VERSION,
            true
        );

        // Annotation system
        wp_enqueue_script(
            'wpvfh-annotation',
            WPVFH_PLUGIN_URL . 'assets/js/annotation.js',
            array( 'wpvfh-screenshot' ),
            WPVFH_VERSION,
            true
        );

        // Voice recorder
        wp_enqueue_script(
            'wpvfh-voice-recorder',
            WPVFH_PLUGIN_URL . 'assets/js/voice-recorder.js',
            array(),
            WPVFH_VERSION,
            true
        );

        // Screen recorder
        wp_enqueue_script(
            'wpvfh-screen-recorder',
            WPVFH_PLUGIN_URL . 'assets/js/screen-recorder.js',
            array(),
            WPVFH_VERSION,
            true
        );

        // Widget principal
        wp_enqueue_script(
            'wpvfh-widget',
            WPVFH_PLUGIN_URL . 'assets/js/feedback-widget.js',
            array( 'wpvfh-annotation', 'wpvfh-voice-recorder', 'wpvfh-screen-recorder', 'wp-i18n' ),
            WPVFH_VERSION,
            true
        );

        // Styles
        wp_enqueue_style(
            'wpvfh-feedback',
            WPVFH_PLUGIN_URL . 'assets/css/feedback.css',
            array(),
            WPVFH_VERSION
        );

        // Couleurs personnalisées
        $custom_colors_css = $this->get_custom_colors_css();
        if ( ! empty( $custom_colors_css ) ) {
            wp_add_inline_style( 'wpvfh-feedback', $custom_colors_css );
        }

        // Passer les données au JavaScript
        wp_localize_script( 'wpvfh-widget', 'wpvfhData', $this->get_frontend_data() );

        // Traductions JavaScript
        wp_set_script_translations( 'wpvfh-widget', 'blazing-feedback' );
    }

    /**
     * Charger les assets admin
     *
     * @since 1.0.0
     * @param string $hook Page actuelle de l'admin.
     * @return void
     */
    public function enqueue_admin_assets( $hook ) {
        // Charger uniquement sur nos pages admin
        $allowed_pages = array(
            'toplevel_page_wpvfh-dashboard',
            'feedback_page_wpvfh-settings',
            'edit.php',
            'post.php',
        );

        // Vérifier si on est sur une page de feedback
        $screen = get_current_screen();
        $is_feedback_page = $screen && ( 'visual_feedback' === $screen->post_type || in_array( $hook, $allowed_pages, true ) );

        if ( ! $is_feedback_page ) {
            return;
        }

        wp_enqueue_style(
            'wpvfh-admin',
            WPVFH_PLUGIN_URL . 'assets/css/feedback.css',
            array(),
            WPVFH_VERSION
        );

        // Couleurs personnalisées
        $custom_colors_css = $this->get_custom_colors_css();
        if ( ! empty( $custom_colors_css ) ) {
            wp_add_inline_style( 'wpvfh-admin', $custom_colors_css );
        }

        wp_enqueue_script(
            'wpvfh-admin',
            WPVFH_PLUGIN_URL . 'assets/js/feedback-widget.js',
            array( 'jquery', 'wp-i18n' ),
            WPVFH_VERSION,
            true
        );

        wp_localize_script( 'wpvfh-admin', 'wpvfhData', $this->get_frontend_data() );
    }

    /**
     * Obtenir les données pour le frontend JavaScript
     *
     * @since 1.0.0
     * @return array Données localisées
     */
    private function get_frontend_data() {
        $current_user = wp_get_current_user();

        /**
         * Filtre les données passées au JavaScript frontend
         *