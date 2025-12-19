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
 * Charger les fichiers principaux du plugin
 */
require_once WPVFH_PLUGIN_DIR . 'includes/main/activation.php';
require_once WPVFH_PLUGIN_DIR . 'includes/main/hooks.php';
require_once WPVFH_PLUGIN_DIR . 'includes/main/notices.php';
require_once WPVFH_PLUGIN_DIR . 'includes/main/permissions.php';
require_once WPVFH_PLUGIN_DIR . 'includes/main/assets.php';
require_once WPVFH_PLUGIN_DIR . 'includes/main/frontend-data.php';
require_once WPVFH_PLUGIN_DIR . 'includes/main/widget-render.php';
require_once WPVFH_PLUGIN_DIR . 'includes/main/class-plugin.php';

/**
 * Fonction helper pour obtenir l'instance du plugin
 *
 * @since 1.0.0
 * @return WP_Visual_Feedback_Hub
 */
function wpvfh() {
	return WP_Visual_Feedback_Hub::get_instance();
}

// Démarrer le plugin
wpvfh();
