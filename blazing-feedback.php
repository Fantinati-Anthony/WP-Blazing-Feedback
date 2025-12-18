<?php
/**
 * Plugin Name: Blazing Feedback
 * Plugin URI: https://github.com/Fantinati-Anthony/Blazing-Feedback-WP
 * Description: Plugin de feedback visuel autonome pour WordPress. Annotations, captures d'écran, gestion de statuts. Alternative open-source à ProjectHuddle, Feedbucket et Marker.io.
 * Version: 1.6.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Blazing Feedback Team
 * Author URI: https://github.com/Fantinati-Anthony
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: blazing-feedback
 * Domain Path: /languages
 *
 * @package Blazing_Feedback
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Constantes du plugin
 */
define( 'WPVFH_VERSION', '1.6.0' );
define( 'WPVFH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPVFH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPVFH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPVFH_MINIMUM_WP_VERSION', '6.0' );
define( 'WPVFH_MINIMUM_PHP_VERSION', '7.4' );

/**
 * Classe principale du plugin
 *
 * Utilise le pattern Singleton pour garantir une seule instance
 *
 * @since 1.0.0
 */
final class WP_Visual_Feedback_Hub {

    /**
     * Instance unique du plugin
     *
     * @var WP_Visual_Feedback_Hub|null
     */
    private static $instance = null;

    /**
     * Obtenir l'instance unique du plugin
     *
     * @since 1.0.0
     * @return WP_Visual_Feedback_Hub
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructeur privé - initialise le plugin
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->check_requirements();
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Vérifier les prérequis système
     *
     * @since 1.0.0
     * @return void
     */
    private function check_requirements() {
        // Vérifier la version PHP
        if ( version_compare( PHP_VERSION, WPVFH_MINIMUM_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
            return;
        }

        // Vérifier la version WordPress
        if ( version_compare( get_bloginfo( 'version' ), WPVFH_MINIMUM_WP_VERSION, '<' ) ) {
            add_action( 'admin_notices', array( $this, 'wp_version_notice' ) );
            return;
        }
    }

    /**
     * Charger les dépendances du plugin
     *
     * @since 1.0.0
     * @return void
     */
    private function load_dependencies() {
        // Fichiers du core
        require_once WPVFH_PLUGIN_DIR . 'includes/permissions.php';
        require_once WPVFH_PLUGIN_DIR . 'includes/roles.php';
        require_once WPVFH_PLUGIN_DIR . 'includes/cpt-feedback.php';
        require_once WPVFH_PLUGIN_DIR . 'includes/rest-api.php';

        // Admin uniquement
        if ( is_admin() ) {
            require_once WPVFH_PLUGIN_DIR . 'includes/admin-ui.php';
            require_once WPVFH_PLUGIN_DIR . 'includes/github-updater.php';

            // Initialiser le système de mise à jour GitHub
            new WPVFH_GitHub_Updater( __FILE__ );
        }
    }

    /**
     * Initialiser les hooks WordPress
     *
     * @since 1.0.0
     * @return void
     */
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

        // Enregistrer le CPT pour flush les rewrite rules
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
         * @since 1.0.0
         * @param array $data Données localisées
         */
        return apply_filters( 'wpvfh_frontend_data', array(
            'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
            'restUrl'        => rest_url( 'blazing-feedback/v1/' ),
            'restNonce'      => wp_create_nonce( 'wp_rest' ),
            'nonce'          => wp_create_nonce( 'wpvfh_nonce' ),
            'currentUrl'     => esc_url( home_url( add_query_arg( array() ) ) ),
            'userId'         => $current_user->ID,
            'userName'       => $current_user->display_name,
            'userEmail'      => $current_user->user_email,
            'isLoggedIn'     => is_user_logged_in(),
            'canCreate'      => current_user_can( 'publish_feedbacks' ),
            'canModerate'    => current_user_can( 'moderate_feedback' ),
            'canManage'      => current_user_can( 'manage_feedback' ),
            'pluginUrl'      => WPVFH_PLUGIN_URL,
            'screenshotEnabled' => $this->is_screenshot_enabled(),
            'statuses'       => WPVFH_CPT_Feedback::get_statuses(),
            'i18n'           => array(
                'feedbackButton'    => __( 'Donner un feedback', 'blazing-feedback' ),
                'closeButton'       => __( 'Fermer', 'blazing-feedback' ),
                'submitButton'      => __( 'Envoyer', 'blazing-feedback' ),
                'cancelButton'      => __( 'Annuler', 'blazing-feedback' ),
                'placeholder'       => __( 'Décrivez votre feedback...', 'blazing-feedback' ),
                'successMessage'    => __( 'Feedback envoyé avec succès !', 'blazing-feedback' ),
                'errorMessage'      => __( 'Erreur lors de l\'envoi du feedback.', 'blazing-feedback' ),
                'loadingMessage'    => __( 'Chargement...', 'blazing-feedback' ),
                'screenshotLabel'   => __( 'Capturer l\'écran', 'blazing-feedback' ),
                'clickToPin'        => __( 'Cliquez pour placer un marqueur', 'blazing-feedback' ),
                'modeEnabled'       => __( 'Mode feedback activé', 'blazing-feedback' ),
                'modeDisabled'      => __( 'Mode feedback désactivé', 'blazing-feedback' ),
                'replyPlaceholder'  => __( 'Votre réponse...', 'blazing-feedback' ),
                'statusNew'         => __( 'Nouveau', 'blazing-feedback' ),
                'statusInProgress'  => __( 'En cours', 'blazing-feedback' ),
                'statusResolved'    => __( 'Résolu', 'blazing-feedback' ),
                'statusRejected'    => __( 'Rejeté', 'blazing-feedback' ),
            ),
        ) );
    }

    /**
     * Vérifier si l'utilisateur peut voir le widget de feedback
     *
     * @since 1.0.0
     * @return bool
     */
    private function can_user_see_feedback_widget() {
        // Les utilisateurs non connectés ne peuvent pas voir le widget par défaut
        if ( ! is_user_logged_in() ) {
            /**
             * Filtre pour autoriser les utilisateurs non connectés à voir le widget
             *
             * @since 1.0.0
             * @param bool $allow Autoriser ou non (défaut: false)
             */
            return apply_filters( 'wpvfh_allow_guest_feedback', false );
        }

        // Vérifier les capacités
        return current_user_can( 'publish_feedbacks' ) || current_user_can( 'moderate_feedback' ) || current_user_can( 'manage_feedback' );
    }

    /**
     * Vérifier si les screenshots sont activés
     *
     * @since 1.0.0
     * @return bool
     */
    private function is_screenshot_enabled() {
        /**
         * Filtre pour activer/désactiver les captures d'écran
         *
         * @since 1.0.0
         * @param bool $enabled Activé ou non (défaut: true)
         */
        return apply_filters( 'wpvfh_screenshot_enabled', get_option( 'wpvfh_screenshot_enabled', true ) );
    }

    /**
     * Afficher le widget de feedback dans le footer
     *
     * @since 1.0.0
     * @return void
     */
    public function render_feedback_widget() {
        // Vérifier les permissions
        if ( ! $this->can_user_see_feedback_widget() ) {
            return;
        }

        // Ne pas afficher dans l'admin
        if ( is_admin() ) {
            return;
        }

        /**
         * Action avant le rendu du widget de feedback
         *
         * @since 1.0.0
         */
        do_action( 'wpvfh_before_widget' );

        // Template du widget
        $template = WPVFH_PLUGIN_DIR . 'templates/feedback-widget.php';

        /**
         * Filtre le chemin du template du widget
         *
         * @since 1.0.0
         * @param string $template Chemin du template
         */
        $template = apply_filters( 'wpvfh_widget_template', $template );

        if ( file_exists( $template ) ) {
            include $template;
        } else {
            // Template par défaut inline
            $this->render_default_widget();
        }

        /**
         * Action après le rendu du widget de feedback
         *
         * @since 1.0.0
         */
        do_action( 'wpvfh_after_widget' );
    }

    /**
     * Rendu du widget par défaut
     *
     * @since 1.0.0
     * @return void
     */
    private function render_default_widget() {
        ?>
        <div id="wpvfh-container" class="wpvfh-container" role="complementary" aria-label="<?php esc_attr_e( 'Feedback visuel', 'blazing-feedback' ); ?>">
            <!-- Overlay pour la sidebar -->
            <div id="wpvfh-sidebar-overlay" class="wpvfh-sidebar-overlay"></div>

            <!-- Groupe de boutons flottants -->
            <div class="wpvfh-floating-buttons">
                <!-- Bouton principal Feedback (ouvre la liste) -->
                <button
                    type="button"
                    id="wpvfh-toggle-btn"
                    class="wpvfh-toggle-btn wpvfh-btn-main"
                    aria-expanded="false"
                    aria-controls="wpvfh-panel"
                    title="<?php esc_attr_e( 'Voir les feedbacks', 'blazing-feedback' ); ?>"
                >
                    <span class="wpvfh-btn-icon" aria-hidden="true">💬</span>
                    <span class="wpvfh-btn-text"><?php esc_html_e( 'Feedback', 'blazing-feedback' ); ?></span>
                    <span class="wpvfh-feedback-count" id="wpvfh-feedback-count" hidden></span>
                </button>

                <!-- Bouton ajouter (nouveau feedback) -->
                <button
                    type="button"
                    id="wpvfh-add-btn"
                    class="wpvfh-toggle-btn wpvfh-btn-add"
                    title="<?php esc_attr_e( 'Ajouter un feedback', 'blazing-feedback' ); ?>"
                >
                    <span class="wpvfh-btn-icon" aria-hidden="true">➕</span>
                </button>

                <!-- Bouton afficher/masquer les points -->
                <button
                    type="button"
                    id="wpvfh-visibility-btn"
                    class="wpvfh-toggle-btn wpvfh-btn-visibility"
                    title="<?php esc_attr_e( 'Afficher/masquer les points', 'blazing-feedback' ); ?>"
                    data-visible="true"
                >
                    <span class="wpvfh-btn-icon wpvfh-icon-visible" aria-hidden="true">👁️</span>
                    <span class="wpvfh-btn-icon wpvfh-icon-hidden" aria-hidden="true" hidden>🙈</span>
                </button>
            </div>

            <!-- Sidebar de feedback -->
            <div id="wpvfh-panel" class="wpvfh-panel" hidden aria-hidden="true">
                <div class="wpvfh-panel-header">
                    <?php
                    $logo_mode = get_option( 'wpvfh_logo_mode', 'none' );
                    $logo_url = '';
                    if ( $logo_mode === 'light' ) {
                        $logo_url = WPVFH_PLUGIN_URL . 'assets/logo/light-mode-feedback.png';
                    } elseif ( $logo_mode === 'dark' ) {
                        $logo_url = WPVFH_PLUGIN_URL . 'assets/logo/dark-mode-feedback.png';
                    } elseif ( $logo_mode === 'custom' ) {
                        $logo_url = get_option( 'wpvfh_logo_custom_url', '' );
                    }
                    if ( $logo_mode !== 'none' && $logo_url ) : ?>
                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Blazing Feedback', 'blazing-feedback' ); ?>" class="wpvfh-panel-logo">
                    <?php else : ?>
                    <h3 class="wpvfh-panel-title"><?php esc_html_e( 'Feedbacks', 'blazing-feedback' ); ?></h3>
                    <?php endif; ?>
                    <div class="wpvfh-header-actions">
                        <button type="button" class="wpvfh-search-btn" id="wpvfh-search-btn" aria-label="<?php esc_attr_e( 'Rechercher', 'blazing-feedback' ); ?>" title="<?php esc_attr_e( 'Rechercher un feedback', 'blazing-feedback' ); ?>">
                            <span aria-hidden="true">🔍</span>
                        </button>
                        <button type="button" class="wpvfh-close-btn" aria-label="<?php esc_attr_e( 'Fermer', 'blazing-feedback' ); ?>">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>

                <!-- Onglets -->
                <div class="wpvfh-tabs">
                    <button type="button" class="wpvfh-tab" data-tab="new" id="wpvfh-tab-new-btn" hidden>
                        <span class="wpvfh-tab-icon" aria-hidden="true">➕</span>
                        <?php esc_html_e( 'Nouveau', 'blazing-feedback' ); ?>
                    </button>
                    <button type="button" class="wpvfh-tab active" data-tab="list">
                        <span class="wpvfh-tab-icon" aria-hidden="true">📋</span>
                        <?php esc_html_e( 'Liste', 'blazing-feedback' ); ?>
                        <span class="wpvfh-tab-count" id="wpvfh-pins-count"></span>
                    </button>
                    <button type="button" class="wpvfh-tab" data-tab="pages">
                        <span class="wpvfh-tab-icon" aria-hidden="true">📄</span>
                        <?php esc_html_e( 'Pages', 'blazing-feedback' ); ?>
                    </button>
                    <button type="button" class="wpvfh-tab" data-tab="priority">
                        <span class="wpvfh-tab-icon" aria-hidden="true">⚡</span>
                        <?php esc_html_e( 'Priorité', 'blazing-feedback' ); ?>
                    </button>
                    <button type="button" class="wpvfh-tab" data-tab="details" id="wpvfh-tab-details-btn" hidden>
                        <span class="wpvfh-tab-icon" aria-hidden="true">👁️</span>
                        <?php esc_html_e( 'Détails', 'blazing-feedback' ); ?>
                    </button>
                </div>

                <div class="wpvfh-panel-body">
                    <!-- Onglet: Nouveau feedback -->
                    <div id="wpvfh-tab-new" class="wpvfh-tab-content">
                        <form id="wpvfh-form" class="wpvfh-form">
                        <!-- Zone de texte principale -->
                        <div class="wpvfh-form-group">
                            <textarea
                                id="wpvfh-comment"
                                name="comment"
                                class="wpvfh-textarea"
                                rows="3"
                                required
                                placeholder="<?php esc_attr_e( 'Décrivez votre feedback...', 'blazing-feedback' ); ?>"
                            ></textarea>
                        </div>

                        <!-- Section ciblage d'élément (optionnel) -->
                        <div class="wpvfh-target-section">
                            <button type="button" id="wpvfh-select-element-btn" class="wpvfh-select-element-btn">
                                <span class="wpvfh-btn-emoji">🎯</span>
                                <span><?php esc_html_e( 'Cibler un élément', 'blazing-feedback' ); ?></span>
                                <span class="wpvfh-optional-badge"><?php esc_html_e( 'optionnel', 'blazing-feedback' ); ?></span>
                            </button>
                            <div id="wpvfh-selected-element" class="wpvfh-selected-element" hidden>
                                <span class="wpvfh-selected-icon">✓</span>
                                <span class="wpvfh-selected-text"><?php esc_html_e( 'Élément sélectionné', 'blazing-feedback' ); ?></span>
                                <button type="button" class="wpvfh-clear-selection" title="<?php esc_attr_e( 'Retirer la sélection', 'blazing-feedback' ); ?>">&times;</button>
                            </div>
                        </div>

                        <!-- Barre d'outils média -->
                        <div class="wpvfh-media-toolbar">
                            <button type="button" class="wpvfh-tool-btn wpvfh-tool-screenshot" data-tool="screenshot" title="<?php esc_attr_e( 'Capture d\'écran', 'blazing-feedback' ); ?>">
                                <span class="wpvfh-tool-emoji">📸</span>
                                <span><?php esc_html_e( 'Capture', 'blazing-feedback' ); ?></span>
                            </button>
                            <button type="button" class="wpvfh-tool-btn wpvfh-tool-voice" data-tool="voice" title="<?php esc_attr_e( 'Message vocal', 'blazing-feedback' ); ?>">
                                <span class="wpvfh-tool-emoji">🎤</span>
                                <span><?php esc_html_e( 'Audio', 'blazing-feedback' ); ?></span>
                            </button>
                            <button type="button" class="wpvfh-tool-btn wpvfh-tool-video" data-tool="video" title="<?php esc_attr_e( 'Enregistrer l\'écran', 'blazing-feedback' ); ?>">
                                <span class="wpvfh-tool-emoji">🎬</span>
                                <span><?php esc_html_e( 'Vidéo', 'blazing-feedback' ); ?></span>
                            </button>
                        </div>

                        <!-- Section enregistrement vocal -->
                        <div id="wpvfh-voice-section" class="wpvfh-media-section" hidden>
                            <div class="wpvfh-recorder-controls">
                                <button type="button" id="wpvfh-voice-record" class="wpvfh-record-btn">
                                    <span class="wpvfh-record-icon"></span>
                                    <span class="wpvfh-record-text"><?php esc_html_e( 'Enregistrer', 'blazing-feedback' ); ?></span>
                                </button>
                                <div class="wpvfh-recorder-status">
                                    <span class="wpvfh-recorder-time">0:00</span>
                                    <span class="wpvfh-recorder-max">/ 2:00</span>
                                </div>
                            </div>
                            <div id="wpvfh-voice-preview" class="wpvfh-audio-preview" hidden>
                                <audio controls></audio>
                                <button type="button" class="wpvfh-remove-media">&times;</button>
                            </div>
                            <div id="wpvfh-transcript-preview" class="wpvfh-transcript-preview" hidden>
                                <label><?php esc_html_e( 'Transcription:', 'blazing-feedback' ); ?></label>
                                <p class="wpvfh-transcript-text"></p>
                            </div>
                        </div>

                        <!-- Section enregistrement vidéo -->
                        <div id="wpvfh-video-section" class="wpvfh-media-section" hidden>
                            <div class="wpvfh-recorder-controls">
                                <button type="button" id="wpvfh-video-record" class="wpvfh-record-btn">
                                    <span class="wpvfh-record-icon"></span>
                                    <span class="wpvfh-record-text"><?php esc_html_e( 'Enregistrer l\'écran', 'blazing-feedback' ); ?></span>
                                </button>
                                <div class="wpvfh-recorder-status">
                                    <span class="wpvfh-recorder-time">0:00</span>
                                    <span class="wpvfh-recorder-max">/ 5:00</span>
                                </div>
                            </div>
                            <div id="wpvfh-video-preview" class="wpvfh-video-preview" hidden>
                                <video controls></video>
                                <button type="button" class="wpvfh-remove-media">&times;</button>
                            </div>
                        </div>

                        <!-- Aperçu capture d'écran -->
                        <div id="wpvfh-screenshot-preview" class="wpvfh-screenshot-preview" hidden>
                            <img src="" alt="<?php esc_attr_e( 'Aperçu de la capture', 'blazing-feedback' ); ?>">
                            <button type="button" class="wpvfh-remove-media">&times;</button>
                        </div>

                        <!-- Section pièces jointes -->
                        <div class="wpvfh-attachments-section">
                            <label class="wpvfh-attachments-label">
                                <span class="wpvfh-label-icon">📎</span>
                                <?php esc_html_e( 'Pièces jointes', 'blazing-feedback' ); ?>
                                <span class="wpvfh-optional-badge"><?php esc_html_e( 'optionnel', 'blazing-feedback' ); ?></span>
                            </label>
                            <div class="wpvfh-attachments-input">
                                <input type="file" id="wpvfh-attachments" name="attachments" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt" hidden>
                                <button type="button" id="wpvfh-add-attachment-btn" class="wpvfh-add-attachment-btn">
                                    <span>➕</span>
                                    <?php esc_html_e( 'Ajouter des fichiers', 'blazing-feedback' ); ?>
                                </button>
                            </div>
                            <div id="wpvfh-attachments-preview" class="wpvfh-attachments-preview"></div>
                            <p class="wpvfh-attachments-hint"><?php esc_html_e( 'Images, PDF, documents (max 5 fichiers, 10 Mo chacun)', 'blazing-feedback' ); ?></p>
                        </div>

                        <!-- Info pin -->
                        <div class="wpvfh-form-group wpvfh-pin-info" hidden>
                            <p class="wpvfh-help-text">
                                <span class="wpvfh-pin-icon" aria-hidden="true">📍</span>
                                <?php esc_html_e( 'Position du marqueur enregistrée', 'blazing-feedback' ); ?>
                            </p>
                        </div>

                        <!-- Champs cachés -->
                        <input type="hidden" id="wpvfh-position-x" name="position_x" value="">
                        <input type="hidden" id="wpvfh-position-y" name="position_y" value="">
                        <input type="hidden" id="wpvfh-screenshot-data" name="screenshot_data" value="">
                        <input type="hidden" id="wpvfh-audio-data" name="audio_data" value="">
                        <input type="hidden" id="wpvfh-video-data" name="video_data" value="">
                        <input type="hidden" id="wpvfh-transcript" name="transcript" value="">

                        <!-- Actions -->
                        <div class="wpvfh-form-actions">
                            <button type="button" class="wpvfh-btn wpvfh-btn-secondary wpvfh-cancel-btn">
                                <span class="wpvfh-btn-emoji">✕</span>
                                <?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?>
                            </button>
                            <button type="submit" class="wpvfh-btn wpvfh-btn-primary wpvfh-submit-btn">
                                <span class="wpvfh-btn-emoji">📨</span>
                                <?php esc_html_e( 'Envoyer', 'blazing-feedback' ); ?>
                            </button>
                        </div>
                    </form>
                    </div><!-- /wpvfh-tab-new -->

                    <!-- Onglet: Liste des feedbacks -->
                    <div id="wpvfh-tab-list" class="wpvfh-tab-content active">
                        <!-- Filtres par état -->
                        <div class="wpvfh-legend" id="wpvfh-filters">
                            <button type="button" class="wpvfh-filter-btn active" data-status="all">
                                <?php esc_html_e( 'Tous', 'blazing-feedback' ); ?>
                                <span class="wpvfh-filter-count" id="wpvfh-filter-all-count">0</span>
                            </button>
                            <button type="button" class="wpvfh-filter-btn" data-status="new">
                                <?php esc_html_e( 'Nouveau', 'blazing-feedback' ); ?>
                                <span class="wpvfh-filter-count" id="wpvfh-filter-new-count">0</span>
                            </button>
                            <button type="button" class="wpvfh-filter-btn" data-status="in_progress">
                                <?php esc_html_e( 'En cours', 'blazing-feedback' ); ?>
                                <span class="wpvfh-filter-count" id="wpvfh-filter-progress-count">0</span>
                            </button>
                            <button type="button" class="wpvfh-filter-btn" data-status="resolved">
                                <?php esc_html_e( 'Résolu', 'blazing-feedback' ); ?>
                                <span class="wpvfh-filter-count" id="wpvfh-filter-resolved-count">0</span>
                            </button>
                            <button type="button" class="wpvfh-filter-btn" data-status="rejected">
                                <?php esc_html_e( 'Rejeté', 'blazing-feedback' ); ?>
                                <span class="wpvfh-filter-count" id="wpvfh-filter-rejected-count">0</span>
                            </button>
                        </div>

                        <div id="wpvfh-pins-list" class="wpvfh-pins-list">
                            <!-- Les pins seront chargés dynamiquement -->
                        </div>
                        <div id="wpvfh-empty-state" class="wpvfh-empty-state">
                            <div class="wpvfh-empty-icon" aria-hidden="true">📭</div>
                            <p class="wpvfh-empty-text"><?php esc_html_e( 'Aucun feedback pour cette page', 'blazing-feedback' ); ?></p>
                            <button type="button" class="wpvfh-btn wpvfh-btn-primary wpvfh-add-feedback-btn" style="margin-top: 16px;">
                                <span class="wpvfh-btn-emoji">➕</span>
                                <?php esc_html_e( 'Ajouter un feedback', 'blazing-feedback' ); ?>
                            </button>
                        </div>
                        <!-- Section validation de page -->
                        <div id="wpvfh-page-validation" class="wpvfh-page-validation" hidden>
                            <div class="wpvfh-validation-status" id="wpvfh-validation-status">
                                <span class="wpvfh-validation-icon">⏳</span>
                                <span class="wpvfh-validation-text"><?php esc_html_e( 'Points en attente de résolution', 'blazing-feedback' ); ?></span>
                            </div>
                            <button type="button" id="wpvfh-validate-page-btn" class="wpvfh-btn wpvfh-btn-validate" disabled>
                                <span class="wpvfh-btn-emoji">✅</span>
                                <?php esc_html_e( 'Valider cette page', 'blazing-feedback' ); ?>
                            </button>
                            <p class="wpvfh-validation-hint" id="wpvfh-validation-hint">
                                <?php esc_html_e( 'Tous les points doivent être résolus ou rejetés avant validation.', 'blazing-feedback' ); ?>
                            </p>
                        </div>

                    </div><!-- /wpvfh-tab-list -->

                    <!-- Onglet: Pages -->
                    <div id="wpvfh-tab-pages" class="wpvfh-tab-content">
                        <div class="wpvfh-pages-header">
                            <h4><?php esc_html_e( 'Toutes les pages avec feedbacks', 'blazing-feedback' ); ?></h4>
                        </div>
                        <div id="wpvfh-pages-list" class="wpvfh-pages-list">
                            <!-- Les pages seront chargées dynamiquement -->
                        </div>
                        <div id="wpvfh-pages-empty" class="wpvfh-empty-state" hidden>
                            <div class="wpvfh-empty-icon" aria-hidden="true">📄</div>
                            <p class="wpvfh-empty-text"><?php esc_html_e( 'Aucune page avec des feedbacks', 'blazing-feedback' ); ?></p>
                        </div>
                        <div id="wpvfh-pages-loading" class="wpvfh-loading-state">
                            <span class="wpvfh-spinner"></span>
                            <span><?php esc_html_e( 'Chargement des pages...', 'blazing-feedback' ); ?></span>
                        </div>
                    </div><!-- /wpvfh-tab-pages -->

                    <!-- Onglet: Priorité -->
                    <div id="wpvfh-tab-priority" class="wpvfh-tab-content">
                        <!-- Zones de dépôt sticky -->
                        <div class="wpvfh-priority-dropzones">
                            <div class="wpvfh-dropzone wpvfh-dropzone-high" data-priority="high">
                                <span class="wpvfh-dropzone-label">🔴 <?php esc_html_e( 'Haute', 'blazing-feedback' ); ?></span>
                            </div>
                            <div class="wpvfh-dropzone wpvfh-dropzone-medium" data-priority="medium">
                                <span class="wpvfh-dropzone-label">🟠 <?php esc_html_e( 'Moyenne', 'blazing-feedback' ); ?></span>
                            </div>
                            <div class="wpvfh-dropzone wpvfh-dropzone-low" data-priority="low">
                                <span class="wpvfh-dropzone-label">🟢 <?php esc_html_e( 'Basse', 'blazing-feedback' ); ?></span>
                            </div>
                        </div>

                        <!-- Listes par priorité -->
                        <div class="wpvfh-priority-sections">
                            <div class="wpvfh-priority-section" data-priority="high">
                                <h4 class="wpvfh-priority-title wpvfh-priority-high">🔴 <?php esc_html_e( 'Priorité haute', 'blazing-feedback' ); ?></h4>
                                <div class="wpvfh-priority-list" id="wpvfh-priority-high-list"></div>
                            </div>
                            <div class="wpvfh-priority-section" data-priority="medium">
                                <h4 class="wpvfh-priority-title wpvfh-priority-medium">🟠 <?php esc_html_e( 'Priorité moyenne', 'blazing-feedback' ); ?></h4>
                                <div class="wpvfh-priority-list" id="wpvfh-priority-medium-list"></div>
                            </div>
                            <div class="wpvfh-priority-section" data-priority="low">
                                <h4 class="wpvfh-priority-title wpvfh-priority-low">🟢 <?php esc_html_e( 'Priorité basse', 'blazing-feedback' ); ?></h4>
                                <div class="wpvfh-priority-list" id="wpvfh-priority-low-list"></div>
                            </div>
                            <div class="wpvfh-priority-section" data-priority="none">
                                <h4 class="wpvfh-priority-title wpvfh-priority-none">⚪ <?php esc_html_e( 'Aucun niveau de priorité', 'blazing-feedback' ); ?></h4>
                                <div class="wpvfh-priority-list" id="wpvfh-priority-none-list"></div>
                            </div>
                        </div>
                    </div><!-- /wpvfh-tab-priority -->

                    <!-- Onglet: Détails d'un feedback -->
                    <div id="wpvfh-tab-details" class="wpvfh-tab-content">
                        <div class="wpvfh-details-header-bar">
                            <button type="button" class="wpvfh-back-btn" id="wpvfh-back-to-list">
                                <span aria-hidden="true">←</span>
                                <?php esc_html_e( 'Retour', 'blazing-feedback' ); ?>
                            </button>
                            <span class="wpvfh-feedback-id" id="wpvfh-detail-id"></span>
                        </div>

                        <div class="wpvfh-detail-content" id="wpvfh-detail-content">
                            <!-- Statut -->
                            <div class="wpvfh-detail-status" id="wpvfh-detail-status"></div>

                            <!-- Auteur et date -->
                            <div class="wpvfh-detail-meta">
                                <div class="wpvfh-detail-author" id="wpvfh-detail-author"></div>
                                <div class="wpvfh-detail-date" id="wpvfh-detail-date"></div>
                            </div>

                            <!-- Commentaire -->
                            <div class="wpvfh-detail-comment" id="wpvfh-detail-comment"></div>

                            <!-- Pièces jointes -->
                            <div class="wpvfh-detail-attachments" id="wpvfh-detail-attachments" hidden>
                                <h4><?php esc_html_e( 'Pièces jointes', 'blazing-feedback' ); ?></h4>
                                <div class="wpvfh-attachments-list" id="wpvfh-attachments-list"></div>
                            </div>

                            <!-- Screenshot -->
                            <div class="wpvfh-detail-screenshot" id="wpvfh-detail-screenshot" hidden>
                                <img src="" alt="<?php esc_attr_e( 'Screenshot', 'blazing-feedback' ); ?>">
                            </div>

                            <!-- Réponses -->
                            <div class="wpvfh-detail-replies" id="wpvfh-detail-replies" hidden>
                                <h4><?php esc_html_e( 'Réponses', 'blazing-feedback' ); ?></h4>
                                <div class="wpvfh-replies-list" id="wpvfh-replies-list"></div>
                            </div>

                            <!-- Inviter des utilisateurs -->
                            <div class="wpvfh-invite-section" id="wpvfh-invite-section">
                                <h4><?php esc_html_e( 'Participants', 'blazing-feedback' ); ?></h4>
                                <div class="wpvfh-participants-list" id="wpvfh-participants-list">
                                    <!-- Liste des participants -->
                                </div>
                                <div class="wpvfh-invite-input-wrapper">
                                    <input type="text" id="wpvfh-invite-input" class="wpvfh-invite-input" placeholder="<?php esc_attr_e( 'Rechercher un utilisateur...', 'blazing-feedback' ); ?>">
                                    <button type="button" id="wpvfh-invite-btn" class="wpvfh-btn wpvfh-btn-small">
                                        <span>➕</span>
                                        <?php esc_html_e( 'Inviter', 'blazing-feedback' ); ?>
                                    </button>
                                </div>
                                <div id="wpvfh-user-suggestions" class="wpvfh-user-suggestions" hidden></div>
                            </div>

                            <!-- Actions modérateur -->
                            <div class="wpvfh-detail-actions" id="wpvfh-detail-actions" hidden>
                                <div class="wpvfh-status-change">
                                    <label for="wpvfh-status-select"><?php esc_html_e( 'Statut:', 'blazing-feedback' ); ?></label>
                                    <select id="wpvfh-status-select" class="wpvfh-status-select">
                                        <option value="new"><?php esc_html_e( 'Nouveau', 'blazing-feedback' ); ?></option>
                                        <option value="in_progress"><?php esc_html_e( 'En cours', 'blazing-feedback' ); ?></option>
                                        <option value="resolved"><?php esc_html_e( 'Résolu', 'blazing-feedback' ); ?></option>
                                        <option value="rejected"><?php esc_html_e( 'Rejeté', 'blazing-feedback' ); ?></option>
                                    </select>
                                </div>

                                <div class="wpvfh-reply-section">
                                    <label for="wpvfh-reply-input"><?php esc_html_e( 'Ajouter une réponse:', 'blazing-feedback' ); ?></label>
                                    <textarea id="wpvfh-reply-input" class="wpvfh-textarea" rows="2" placeholder="<?php esc_attr_e( 'Votre réponse...', 'blazing-feedback' ); ?>"></textarea>
                                    <button type="button" class="wpvfh-btn wpvfh-btn-primary" id="wpvfh-send-reply">
                                        <span class="wpvfh-btn-emoji">📨</span>
                                        <?php esc_html_e( 'Envoyer', 'blazing-feedback' ); ?>
                                    </button>
                                </div>

                                <!-- Bouton supprimer (visible pour créateur/admin) -->
                                <div class="wpvfh-delete-section" id="wpvfh-delete-section" hidden>
                                    <hr class="wpvfh-separator">
                                    <button type="button" class="wpvfh-btn wpvfh-btn-danger" id="wpvfh-delete-feedback-btn">
                                        <span class="wpvfh-btn-emoji">🗑️</span>
                                        <?php esc_html_e( 'Supprimer ce feedback', 'blazing-feedback' ); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div><!-- /wpvfh-tab-details -->
                </div>
            </div>

            <!-- Conteneur pour les pins existants -->
            <div id="wpvfh-pins-container" class="wpvfh-pins-container" aria-live="polite"></div>

            <!-- Overlay mode annotation -->
            <div id="wpvfh-annotation-overlay" class="wpvfh-annotation-overlay" hidden>
                <div class="wpvfh-annotation-hint">
                    <span class="wpvfh-hint-icon" aria-hidden="true">👆</span>
                    <span class="wpvfh-hint-text"><?php esc_html_e( 'Cliquez pour placer un marqueur', 'blazing-feedback' ); ?></span>
                    <button type="button" class="wpvfh-hint-close"><?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?></button>
                </div>
            </div>

            <!-- Messages de notification -->
            <div id="wpvfh-notifications" class="wpvfh-notifications" aria-live="assertive"></div>

            <!-- Dropdown suggestions mentions @ -->
            <div id="wpvfh-mention-dropdown" class="wpvfh-mention-dropdown" hidden>
                <div class="wpvfh-mention-list" id="wpvfh-mention-list">
                    <!-- Utilisateurs suggérés chargés dynamiquement -->
                </div>
            </div>

            <!-- Modal confirmation suppression -->
            <div id="wpvfh-confirm-modal" class="wpvfh-modal" hidden>
                <div class="wpvfh-modal-overlay"></div>
                <div class="wpvfh-modal-content">
                    <h3 class="wpvfh-modal-title"><?php esc_html_e( 'Confirmer la suppression', 'blazing-feedback' ); ?></h3>
                    <p class="wpvfh-modal-text"><?php esc_html_e( 'Êtes-vous sûr de vouloir supprimer ce feedback ? Cette action est irréversible.', 'blazing-feedback' ); ?></p>
                    <div class="wpvfh-modal-actions">
                        <button type="button" class="wpvfh-btn wpvfh-btn-secondary" id="wpvfh-cancel-delete">
                            <?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?>
                        </button>
                        <button type="button" class="wpvfh-btn wpvfh-btn-danger" id="wpvfh-confirm-delete">
                            <?php esc_html_e( 'Supprimer', 'blazing-feedback' ); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal validation page -->
            <div id="wpvfh-validate-modal" class="wpvfh-modal" hidden>
                <div class="wpvfh-modal-overlay"></div>
                <div class="wpvfh-modal-content">
                    <div class="wpvfh-modal-icon">✅</div>
                    <h3 class="wpvfh-modal-title"><?php esc_html_e( 'Valider cette page', 'blazing-feedback' ); ?></h3>
                    <p class="wpvfh-modal-text"><?php esc_html_e( 'En validant cette page, vous confirmez que tous les feedbacks ont été traités. Cette page sera marquée comme terminée.', 'blazing-feedback' ); ?></p>
                    <div class="wpvfh-modal-actions">
                        <button type="button" class="wpvfh-btn wpvfh-btn-secondary" id="wpvfh-cancel-validate">
                            <?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?>
                        </button>
                        <button type="button" class="wpvfh-btn wpvfh-btn-success" id="wpvfh-confirm-validate">
                            <?php esc_html_e( 'Valider', 'blazing-feedback' ); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal de recherche -->
            <div id="wpvfh-search-modal" class="wpvfh-modal wpvfh-search-modal" hidden>
                <div class="wpvfh-modal-overlay"></div>
                <div class="wpvfh-modal-content wpvfh-search-content">
                    <div class="wpvfh-search-header">
                        <h3 class="wpvfh-modal-title">🔍 <?php esc_html_e( 'Rechercher un feedback', 'blazing-feedback' ); ?></h3>
                        <button type="button" class="wpvfh-search-close" id="wpvfh-search-close">&times;</button>
                    </div>
                    <form id="wpvfh-search-form" class="wpvfh-search-form">
                        <!-- Recherche par ID -->
                        <div class="wpvfh-search-group">
                            <label for="wpvfh-search-id"><?php esc_html_e( 'Numéro du feedback', 'blazing-feedback' ); ?></label>
                            <input type="number" id="wpvfh-search-id" placeholder="<?php esc_attr_e( 'Ex: 123', 'blazing-feedback' ); ?>" min="1">
                        </div>

                        <!-- Recherche par texte -->
                        <div class="wpvfh-search-group">
                            <label for="wpvfh-search-text"><?php esc_html_e( 'Contenu du commentaire', 'blazing-feedback' ); ?></label>
                            <input type="text" id="wpvfh-search-text" placeholder="<?php esc_attr_e( 'Rechercher dans le texte...', 'blazing-feedback' ); ?>">
                        </div>

                        <!-- Filtres sur une ligne -->
                        <div class="wpvfh-search-filters">
                            <!-- Statut -->
                            <div class="wpvfh-search-group wpvfh-search-filter">
                                <label for="wpvfh-search-status"><?php esc_html_e( 'Statut', 'blazing-feedback' ); ?></label>
                                <select id="wpvfh-search-status">
                                    <option value=""><?php esc_html_e( 'Tous', 'blazing-feedback' ); ?></option>
                                    <option value="new"><?php esc_html_e( 'Nouveau', 'blazing-feedback' ); ?></option>
                                    <option value="in_progress"><?php esc_html_e( 'En cours', 'blazing-feedback' ); ?></option>
                                    <option value="resolved"><?php esc_html_e( 'Résolu', 'blazing-feedback' ); ?></option>
                                    <option value="rejected"><?php esc_html_e( 'Rejeté', 'blazing-feedback' ); ?></option>
                                </select>
                            </div>

                            <!-- Priorité -->
                            <div class="wpvfh-search-group wpvfh-search-filter">
                                <label for="wpvfh-search-priority"><?php esc_html_e( 'Priorité', 'blazing-feedback' ); ?></label>
                                <select id="wpvfh-search-priority">
                                    <option value=""><?php esc_html_e( 'Toutes', 'blazing-feedback' ); ?></option>
                                    <option value="high"><?php esc_html_e( 'Haute', 'blazing-feedback' ); ?></option>
                                    <option value="medium"><?php esc_html_e( 'Moyenne', 'blazing-feedback' ); ?></option>
                                    <option value="low"><?php esc_html_e( 'Basse', 'blazing-feedback' ); ?></option>
                                    <option value="none"><?php esc_html_e( 'Aucune', 'blazing-feedback' ); ?></option>
                                </select>
                            </div>

                            <!-- Auteur -->
                            <div class="wpvfh-search-group wpvfh-search-filter">
                                <label for="wpvfh-search-author"><?php esc_html_e( 'Auteur', 'blazing-feedback' ); ?></label>
                                <input type="text" id="wpvfh-search-author" placeholder="<?php esc_attr_e( 'Nom...', 'blazing-feedback' ); ?>">
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="wpvfh-search-dates">
                            <div class="wpvfh-search-group wpvfh-search-filter">
                                <label for="wpvfh-search-date-from"><?php esc_html_e( 'Du', 'blazing-feedback' ); ?></label>
                                <input type="date" id="wpvfh-search-date-from">
                            </div>
                            <div class="wpvfh-search-group wpvfh-search-filter">
                                <label for="wpvfh-search-date-to"><?php esc_html_e( 'Au', 'blazing-feedback' ); ?></label>
                                <input type="date" id="wpvfh-search-date-to">
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="wpvfh-search-actions">
                            <button type="button" class="wpvfh-btn wpvfh-btn-secondary" id="wpvfh-search-reset">
                                <?php esc_html_e( 'Réinitialiser', 'blazing-feedback' ); ?>
                            </button>
                            <button type="submit" class="wpvfh-btn wpvfh-btn-primary">
                                <?php esc_html_e( 'Rechercher', 'blazing-feedback' ); ?>
                            </button>
                        </div>
                    </form>

                    <!-- Résultats -->
                    <div id="wpvfh-search-results" class="wpvfh-search-results" hidden>
                        <div class="wpvfh-search-results-header">
                            <span id="wpvfh-search-results-count"></span>
                        </div>
                        <div id="wpvfh-search-results-list" class="wpvfh-search-results-list"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Notice pour version PHP insuffisante
     *
     * @since 1.0.0
     * @return void
     */
    public function php_version_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                printf(
                    /* translators: %s: version PHP minimale requise */
                    esc_html__( 'WP Visual Feedback Hub nécessite PHP %s ou supérieur. Veuillez mettre à jour votre version de PHP.', 'blazing-feedback' ),
                    WPVFH_MINIMUM_PHP_VERSION
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Notice pour version WordPress insuffisante
     *
     * @since 1.0.0
     * @return void
     */
    public function wp_version_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                printf(
                    /* translators: %s: version WordPress minimale requise */
                    esc_html__( 'WP Visual Feedback Hub nécessite WordPress %s ou supérieur. Veuillez mettre à jour WordPress.', 'blazing-feedback' ),
                    WPVFH_MINIMUM_WP_VERSION
                );
                ?>
            </p>
        </div>
        <?php
    }
}

/**
 * Initialiser le plugin
 *
 * @since 1.0.0
 * @return WP_Visual_Feedback_Hub
 */
function wpvfh() {
    return WP_Visual_Feedback_Hub::get_instance();
}

// Démarrer le plugin
wpvfh();
