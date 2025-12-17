<?php
/**
 * Plugin Name: Blazing Feedback
 * Plugin URI: https://github.com/Fantinati-Anthony/Blazing-Feedback-WP
 * Description: Plugin de feedback visuel autonome pour WordPress. Annotations, captures d'écran, gestion de statuts. Alternative open-source à ProjectHuddle, Feedbucket et Marker.io.
 * Version: 1.4.1
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
define( 'WPVFH_VERSION', '1.4.1' );
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
            <!-- Bouton flottant -->
            <button
                type="button"
                id="wpvfh-toggle-btn"
                class="wpvfh-toggle-btn"
                aria-expanded="false"
                aria-controls="wpvfh-panel"
                title="<?php esc_attr_e( 'Donner un feedback', 'blazing-feedback' ); ?>"
            >
                <span class="wpvfh-btn-icon" aria-hidden="true">💬</span>
                <span class="wpvfh-btn-text"><?php esc_html_e( 'Feedback', 'blazing-feedback' ); ?></span>
            </button>

            <!-- Panneau de feedback -->
            <div id="wpvfh-panel" class="wpvfh-panel" hidden aria-hidden="true">
                <div class="wpvfh-panel-header">
                    <h3 class="wpvfh-panel-title"><?php esc_html_e( 'Nouveau feedback', 'blazing-feedback' ); ?></h3>
                    <button type="button" class="wpvfh-close-btn" aria-label="<?php esc_attr_e( 'Fermer', 'blazing-feedback' ); ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="wpvfh-panel-body">
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

                        <!-- Barre d'outils média -->
                        <div class="wpvfh-media-toolbar">
                            <button type="button" class="wpvfh-tool-btn wpvfh-tool-screenshot" data-tool="screenshot" title="<?php esc_attr_e( 'Capture d\'écran', 'blazing-feedback' ); ?>">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <span><?php esc_html_e( 'Capture', 'blazing-feedback' ); ?></span>
                            </button>
                            <button type="button" class="wpvfh-tool-btn wpvfh-tool-voice" data-tool="voice" title="<?php esc_attr_e( 'Message vocal', 'blazing-feedback' ); ?>">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                    <line x1="12" y1="19" x2="12" y2="23"></line>
                                    <line x1="8" y1="23" x2="16" y2="23"></line>
                                </svg>
                                <span><?php esc_html_e( 'Audio', 'blazing-feedback' ); ?></span>
                            </button>
                            <button type="button" class="wpvfh-tool-btn wpvfh-tool-video" data-tool="video" title="<?php esc_attr_e( 'Enregistrer l\'écran', 'blazing-feedback' ); ?>">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="23 7 16 12 23 17 23 7"></polygon>
                                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                                </svg>
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
                                <?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?>
                            </button>
                            <button type="submit" class="wpvfh-btn wpvfh-btn-primary wpvfh-submit-btn">
                                <?php esc_html_e( 'Envoyer', 'blazing-feedback' ); ?>
                            </button>
                        </div>
                    </form>
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
