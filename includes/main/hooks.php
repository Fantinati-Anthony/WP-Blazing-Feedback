<?php
/**
 * Initialisation hooks WordPress
 * 
 * Reference file for blazing-feedback.php lines 130-220
 * See main file: blazing-feedback.php
 * 
 * @package Blazing_Feedback
 */

// To view this section, read blazing-feedback.php with:
// offset=130, limit=91

    private function init_hooks() {
        // Activation / Désactivation
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Initialisation
        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Footer du site (widget de feedback)
        add_action( 'wp_footer', array( $this, 'render_feedback_widget' ) );

        /**
         * Action déclenchée après l'initialisation complète du plugin
         *
         * @since 1.0.0
         */
        do_action( 'wpvfh_loaded' );
    }

    /**
     * Activation du plugin
     *
     * @since 1.0.0
     * @return void
     */
    public function activate() {
        // Créer les rôles personnalisés
        WPVFH_Roles::create_roles();

        // Installer les tables SQL personnalisées
        WPVFH_Database::install();

        // Migration des données si nécessaire (depuis posts/postmeta vers tables custom)
        if ( WPVFH_Database::needs_migration() ) {
            WPVFH_Database::run_migration();
        }

        // Enregistrer le CPT pour flush les rewrite rules (gardé pour rétrocompatibilité)
        WPVFH_CPT_Feedback::register_post_type();
        WPVFH_CPT_Feedback::register_taxonomies();

        // Flush des règles de réécriture
        flush_rewrite_rules();

        // Créer le dossier uploads pour les screenshots
        $this->create_upload_directory();

        // Sauvegarder la version pour les mises à jour futures
        update_option( 'wpvfh_version', WPVFH_VERSION );

        /**
         * Action déclenchée après l'activation du plugin
         *
         * @since 1.0.0
         */
        do_action( 'wpvfh_activated' );
    }

    /**
     * Désactivation du plugin
     *
     * @since 1.0.0
     * @return void
     */
    public function deactivate() {
        // Flush des règles de réécriture
        flush_rewrite_rules();

        /**
         * Action déclenchée après la désactivation du plugin
         *
         * @since 1.0.0
         */
        do_action( 'wpvfh_deactivated' );
    }

    /**
     * Générer le CSS inline pour les couleurs personnalisées
     *
     * @since 1.8.0
     * @return string
     */
    public function get_custom_colors_css() {
        $colors = array(
            'primary'       => get_option( 'wpvfh_color_primary', '#e74c3c' ),
            'primary_hover' => get_option( 'wpvfh_color_primary_hover', '#c0392b' ),
            'secondary'     => get_option( 'wpvfh_color_secondary', '#3498db' ),
            'success'       => get_option( 'wpvfh_color_success', '#27ae60' ),
            'warning'       => get_option( 'wpvfh_color_warning', '#f39c12' ),