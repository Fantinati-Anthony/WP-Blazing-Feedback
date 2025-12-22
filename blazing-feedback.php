<?php
/**
 * Plugin Name: Blazing Minds
 * Plugin URI: https://github.com/Fantinati-Anthony/WP-Blazing-Feedback
 * Description: Solution complète de gestion de projets avec widget de feedback et méthodologie CPPICAVAL™ - Du feedback à l'apprentissage. Annotations, captures d'écran, suivi de projet, IA intégrée.
 * Version: 2.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Blazing Minds Team
 * Author URI: https://github.com/Fantinati-Anthony
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: blazing-feedback
 * Domain Path: /languages
 *
 * @package Blazing_Minds
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Constantes du plugin
 */
define( 'WPVFH_VERSION', '2.1.0' );
define( 'WPVFH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPVFH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPVFH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPVFH_MINIMUM_WP_VERSION', '6.0' );
define( 'WPVFH_MINIMUM_PHP_VERSION', '7.4' );

/**
 * Charger les fichiers principaux du plugin (Module Feedback)
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
 * Charger le module Minds (CPPICAVAL)
 */
require_once WPVFH_PLUGIN_DIR . 'includes/minds/loader.php';

/**
 * Fonction helper pour obtenir l'instance du module Feedback
 *
 * @since 1.0.0
 * @return WP_Visual_Feedback_Hub
 */
function wpvfh() {
	return WP_Visual_Feedback_Hub::get_instance();
}

/**
 * Alias pour la compatibilité - Blazing Minds
 *
 * @since 2.0.0
 * @return WP_Visual_Feedback_Hub
 */
function blazing_minds() {
	return wpvfh();
}

// Démarrer le plugin
wpvfh();
