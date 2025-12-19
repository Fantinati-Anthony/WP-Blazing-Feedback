<?php
/**
 * En-tête plugin, constantes, singleton
 * 
 * Reference file for blazing-feedback.php lines 1-130
 * See main file: blazing-feedback.php
 * 
 * @package Blazing_Feedback
 */

// To view this section, read blazing-feedback.php with:
// offset=1, limit=130

<?php
/**
 * Plugin Name: Blazing Feedback
 * Plugin URI: https://github.com/Fantinati-Anthony/WP-Blazing-Feedback
 * Description: Plugin de feedback visuel autonome pour WordPress. Annotations, captures d'écran, gestion de statuts. Alternative open-source à ProjectHuddle, Feedbucket et Marker.io.
 * Version: 1.7.0
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
define( 'WPVFH_VERSION', '1.7.0' );
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
        // Database management (doit être chargé en premier)
        require_once WPVFH_PLUGIN_DIR . 'includes/database.php';

        // Fichiers du core
        require_once WPVFH_PLUGIN_DIR . 'includes/permissions.php';
        require_once WPVFH_PLUGIN_DIR . 'includes/roles.php';
        require_once WPVFH_PLUGIN_DIR . 'includes/options-manager.php';
        require_once WPVFH_PLUGIN_DIR . 'includes/cpt-feedback.php';
        require_once WPVFH_PLUGIN_DIR . 'includes/rest-api.php';

        // Admin uniquement
        if ( is_admin() ) {
            require_once WPVFH_PLUGIN_DIR . 'includes/admin-ui.php';
            require_once WPVFH_PLUGIN_DIR . 'includes/github-updater.php';

            // Initialiser le système de mise à jour GitHub
            new WPVFH_GitHub_Updater( __FILE__ );

            // Initialiser le gestionnaire d'options (admin seulement pour les hooks admin)
            WPVFH_Options_Manager::init();
        }
    }

    /**
     * Initialiser les hooks WordPress
     *
     * @since 1.0.0
     * @return void
     */
    private function init_hooks() {