<?php
/**
 * Enregistrement des hooks WordPress
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistrer tous les hooks WordPress du plugin
 *
 * @since 1.7.0
 * @return void
 */
function wpvfh_register_hooks() {
	// Activation / Désactivation
	register_activation_hook( WPVFH_PLUGIN_DIR . 'blazing-feedback.php', 'wpvfh_activate' );
	register_deactivation_hook( WPVFH_PLUGIN_DIR . 'blazing-feedback.php', 'wpvfh_deactivate' );

	// Initialisation
	add_action( 'init', 'wpvfh_load_textdomain' );
	add_action( 'wp_enqueue_scripts', 'wpvfh_enqueue_frontend_assets' );
	add_action( 'admin_enqueue_scripts', 'wpvfh_enqueue_admin_assets' );

	// Footer du site (widget de feedback)
	add_action( 'wp_footer', 'wpvfh_render_feedback_widget' );
}

/**
 * Charger les fichiers de traduction
 *
 * @since 1.0.0
 * @return void
 */
function wpvfh_load_textdomain() {
	load_plugin_textdomain(
		'blazing-feedback',
		false,
		dirname( WPVFH_PLUGIN_BASENAME ) . '/languages'
	);
}
