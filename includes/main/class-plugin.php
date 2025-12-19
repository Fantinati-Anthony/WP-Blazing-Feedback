<?php
/**
 * Classe principale du plugin
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
			add_action( 'admin_notices', 'wpvfh_php_version_notice' );
			return;
		}

		// Vérifier la version WordPress
		if ( version_compare( get_bloginfo( 'version' ), WPVFH_MINIMUM_WP_VERSION, '<' ) ) {
			add_action( 'admin_notices', 'wpvfh_wp_version_notice' );
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
			new WPVFH_GitHub_Updater( WPVFH_PLUGIN_DIR . 'blazing-feedback.php' );

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
		// Appel de la fonction d'enregistrement des hooks
		wpvfh_register_hooks();

		/**
		 * Action déclenchée après l'initialisation complète du plugin
		 *
		 * @since 1.0.0
		 */
		do_action( 'wpvfh_loaded' );
	}
}
