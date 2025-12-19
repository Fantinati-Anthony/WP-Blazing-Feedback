<?php
/**
 * Interface d'administration de Blazing Feedback
 *
 * Dashboard, paramètres et pages admin
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Inclure tous les traits
require_once WPVFH_PLUGIN_DIR . 'includes/admin/trait-admin-menu.php';
require_once WPVFH_PLUGIN_DIR . 'includes/admin/trait-admin-settings.php';
require_once WPVFH_PLUGIN_DIR . 'includes/admin/trait-admin-assets.php';
require_once WPVFH_PLUGIN_DIR . 'includes/admin/trait-admin-settings-page.php';
require_once WPVFH_PLUGIN_DIR . 'includes/admin/trait-admin-settings-design.php';
require_once WPVFH_PLUGIN_DIR . 'includes/admin/trait-admin-settings-tabs-small.php';
require_once WPVFH_PLUGIN_DIR . 'includes/admin/trait-admin-settings-fields.php';
require_once WPVFH_PLUGIN_DIR . 'includes/admin/trait-admin-dashboard.php';
require_once WPVFH_PLUGIN_DIR . 'includes/admin/trait-admin-ajax.php';
require_once WPVFH_PLUGIN_DIR . 'includes/admin/trait-admin-notifications.php';

/**
 * Classe de gestion de l'interface admin
 *
 * @since 1.0.0
 */
class WPVFH_Admin_UI {

	// Utiliser tous les traits
	use WPVFH_Admin_Menu;
	use WPVFH_Admin_Settings;
	use WPVFH_Admin_Assets;
	use WPVFH_Admin_Settings_Page;
	use WPVFH_Admin_Settings_Design;
	use WPVFH_Admin_Settings_Tabs_Small;
	use WPVFH_Admin_Settings_Fields;
	use WPVFH_Admin_Dashboard;
	use WPVFH_Admin_Ajax;
	use WPVFH_Admin_Notifications;

	/**
	 * Initialiser l'interface admin
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_pages' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_danger_zone_actions' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_styles' ) );

		// Actions AJAX admin
		add_action( 'wp_ajax_wpvfh_quick_status_update', array( __CLASS__, 'ajax_quick_status_update' ) );
		add_action( 'wp_ajax_wpvfh_dismiss_notice', array( __CLASS__, 'ajax_dismiss_notice' ) );

		// Notices
		add_action( 'admin_notices', array( __CLASS__, 'show_admin_notices' ) );

		// Liens dans la page plugins
		add_filter( 'plugin_action_links_' . WPVFH_PLUGIN_BASENAME, array( __CLASS__, 'add_plugin_links' ) );
	}
}
