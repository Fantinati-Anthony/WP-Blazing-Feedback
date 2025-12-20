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

	// Gestionnaire de sauvegarde des paramètres (table personnalisée uniquement)
	add_action( 'admin_init', 'wpvfh_handle_settings_save' );
}

/**
 * Gérer la sauvegarde des paramètres directement dans la table personnalisée
 *
 * @since 2.0.0
 * @return void
 */
function wpvfh_handle_settings_save() {
	// Vérifier si c'est une soumission de formulaire de paramètres
	if ( ! isset( $_POST['option_page'] ) || $_POST['option_page'] !== 'wpvfh_general_settings' ) {
		return;
	}

	// Vérifier le nonce
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wpvfh_general_settings-options' ) ) {
		return;
	}

	// Vérifier les permissions
	if ( ! current_user_can( 'manage_feedback' ) ) {
		return;
	}

	// Liste des paramètres à sauvegarder avec leurs types
	$settings = array(
		// Général
		'wpvfh_screenshot_enabled'    => 'boolean',
		'wpvfh_guest_feedback'        => 'boolean',
		'wpvfh_button_position'       => 'string',
		'wpvfh_panel_position'        => 'string',
		'wpvfh_enabled_pages'         => 'string',
		'wpvfh_post_feedback_action'  => 'string',
		'wpvfh_enable_admin'          => 'boolean',
		// Notifications
		'wpvfh_email_notifications'   => 'boolean',
		'wpvfh_notification_email'    => 'email',
		// IA
		'wpvfh_ai_enabled'            => 'boolean',
		'wpvfh_ai_api_key'            => 'string',
		'wpvfh_ai_system_prompt'      => 'textarea',
		'wpvfh_ai_analysis_prompt'    => 'textarea',
		// Graphisme - Bouton
		'wpvfh_button_color'          => 'color',
		'wpvfh_button_color_hover'    => 'color',
		'wpvfh_button_style'          => 'string',
		'wpvfh_button_attached_shape' => 'string',
		'wpvfh_button_border_radius'  => 'integer',
		'wpvfh_button_border_radius_unit' => 'string',
		'wpvfh_button_margin'         => 'integer',
		'wpvfh_button_size'           => 'integer',
		'wpvfh_theme_mode'            => 'string',
		// Icônes
		'wpvfh_light_icon_type'       => 'string',
		'wpvfh_light_icon_emoji'      => 'string',
		'wpvfh_light_icon_url'        => 'url',
		'wpvfh_dark_icon_type'        => 'string',
		'wpvfh_dark_icon_emoji'       => 'string',
		'wpvfh_dark_icon_url'         => 'url',
		// Badge
		'wpvfh_badge_bg_color'        => 'color',
		'wpvfh_badge_text_color'      => 'color',
		// Bordure
		'wpvfh_button_border_width'   => 'integer',
		'wpvfh_button_border_color'   => 'color',
		// Ombre
		'wpvfh_button_shadow_color'   => 'color',
		'wpvfh_button_shadow_blur'    => 'integer',
		'wpvfh_button_shadow_opacity' => 'integer',
		// Logo
		'wpvfh_panel_logo_light_url'  => 'url',
		'wpvfh_panel_logo_dark_url'   => 'url',
		// Border radius global
		'wpvfh_border_radius'         => 'integer',
	);

	// Sauvegarder chaque paramètre dans la table personnalisée
	foreach ( $settings as $option_name => $type ) {
		$value = wpvfh_sanitize_setting( $option_name, $type );
		WPVFH_Database::update_setting( $option_name, $value );
	}

	// Sauvegarder les couleurs personnalisées (commençant par wpvfh_color_)
	foreach ( $_POST as $key => $value ) {
		if ( strpos( $key, 'wpvfh_color_' ) === 0 ) {
			$sanitized_value = sanitize_hex_color( $value );
			WPVFH_Database::update_setting( $key, $sanitized_value );
		}
	}

	// Rediriger avec un message de succès
	wp_safe_redirect( add_query_arg( 'settings-updated', 'true', wp_get_referer() ) );
	exit;
}

/**
 * Nettoyer une valeur de paramètre selon son type
 *
 * @since 2.0.0
 * @param string $option_name Nom de l'option.
 * @param string $type        Type de l'option.
 * @return mixed Valeur nettoyée.
 */
function wpvfh_sanitize_setting( $option_name, $type ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$raw_value = isset( $_POST[ $option_name ] ) ? wp_unslash( $_POST[ $option_name ] ) : '';

	switch ( $type ) {
		case 'boolean':
			return ! empty( $raw_value );

		case 'integer':
			return absint( $raw_value );

		case 'email':
			return sanitize_email( $raw_value );

		case 'url':
			return esc_url_raw( $raw_value );

		case 'color':
			return sanitize_hex_color( $raw_value );

		case 'textarea':
			return sanitize_textarea_field( $raw_value );

		case 'string':
		default:
			return sanitize_text_field( $raw_value );
	}
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
