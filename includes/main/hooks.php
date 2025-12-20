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

	// Footer admin (widget de feedback si activé)
	add_action( 'admin_footer', 'wpvfh_render_admin_feedback_widget' );
}

/**
 * Afficher le widget de feedback dans le footer admin
 *
 * @since 1.10.0
 * @return void
 */
function wpvfh_render_admin_feedback_widget() {
	// Vérifier si l'option est activée
	if ( ! WPVFH_Database::get_setting( 'wpvfh_enable_admin', false ) ) {
		return;
	}

	// Vérifier les permissions
	if ( ! wpvfh_can_user_see_feedback_widget() ) {
		return;
	}

	// Charger les assets frontend dans l'admin
	wpvfh_enqueue_frontend_assets_for_admin();

	/**
	 * Action avant le rendu du widget de feedback admin
	 *
	 * @since 1.10.0
	 */
	do_action( 'wpvfh_before_admin_widget' );

	// Template du widget
	$template_file = WPVFH_PLUGIN_DIR . 'includes/main/widget-template.php';
	if ( file_exists( $template_file ) ) {
		include $template_file;
	}

	/**
	 * Action après le rendu du widget de feedback admin
	 *
	 * @since 1.10.0
	 */
	do_action( 'wpvfh_after_admin_widget' );
}

/**
 * Charger les assets frontend dans l'admin pour le widget
 *
 * @since 1.10.0
 * @return void
 */
function wpvfh_enqueue_frontend_assets_for_admin() {
	// CSS Frontend
	wp_enqueue_style(
		'wpvfh-frontend',
		WPVFH_PLUGIN_URL . 'assets/css/frontend.css',
		array(),
		WPVFH_VERSION
	);

	// JS Frontend
	wp_enqueue_script(
		'wpvfh-frontend',
		WPVFH_PLUGIN_URL . 'assets/js/frontend.js',
		array( 'jquery' ),
		WPVFH_VERSION,
		true
	);

	// html2canvas pour les captures d'écran
	if ( wpvfh_is_screenshot_enabled() ) {
		wp_enqueue_script(
			'html2canvas',
			WPVFH_PLUGIN_URL . 'assets/js/lib/html2canvas.min.js',
			array(),
			'1.4.1',
			true
		);
	}

	// Données localisées
	wp_localize_script( 'wpvfh-frontend', 'wpvfhData', wpvfh_get_frontend_data() );

	// CSS inline pour les couleurs personnalisées
	$custom_colors_css = wpvfh_get_custom_colors_css();
	if ( $custom_colors_css ) {
		wp_add_inline_style( 'wpvfh-frontend', $custom_colors_css );
	}

	// CSS inline pour le border-radius personnalisé
	$custom_border_radius_css = wpvfh_get_custom_border_radius_css();
	if ( $custom_border_radius_css ) {
		wp_add_inline_style( 'wpvfh-frontend', $custom_border_radius_css );
	}
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
