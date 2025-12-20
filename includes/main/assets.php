<?php
/**
 * Gestion des assets (scripts et styles)
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Charger les assets frontend
 *
 * @since 1.0.0
 * @return void
 */
function wpvfh_enqueue_frontend_assets() {
	// Vérifier si l'utilisateur peut voir le widget de feedback
	if ( ! wpvfh_can_user_see_feedback_widget() ) {
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

	// Modules du widget (dans l'ordre de dépendance)
	$widget_modules = array(
		'tools',
		'notifications',
		'core',
		'api',
		'labels',
		'tags',
		'filters',
		'screenshot',
		'media',
		'attachments',
		'mentions',
		'validation',
		'form',
		'list',
		'pages',
		'details',
		'panel',
		'search',
		'events',
		'participants',
	);

	$previous_handle = 'wpvfh-screen-recorder';
	foreach ( $widget_modules as $module ) {
		$handle = 'wpvfh-module-' . $module;
		wp_enqueue_script(
			$handle,
			WPVFH_PLUGIN_URL . 'assets/js/modules/' . $module . '.js',
			array( $previous_handle ),
			WPVFH_VERSION,
			true
		);
		$previous_handle = $handle;
	}

	// Widget principal (orchestrateur)
	wp_enqueue_script(
		'wpvfh-widget',
		WPVFH_PLUGIN_URL . 'assets/js/feedback-widget.js',
		array( $previous_handle, 'wpvfh-annotation', 'wpvfh-voice-recorder', 'wp-i18n' ),
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

	// Couleurs personnalisées
	$custom_colors_css = wpvfh_get_custom_colors_css();
	if ( ! empty( $custom_colors_css ) ) {
		wp_add_inline_style( 'wpvfh-feedback', $custom_colors_css );
	}

	// Border-radius personnalisés
	$custom_radius_css = wpvfh_get_custom_border_radius_css();
	if ( ! empty( $custom_radius_css ) ) {
		wp_add_inline_style( 'wpvfh-feedback', $custom_radius_css );
	}

	// Passer les données au JavaScript
	wp_localize_script( 'wpvfh-widget', 'wpvfhData', wpvfh_get_frontend_data() );

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
function wpvfh_enqueue_admin_assets( $hook ) {
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

	// Couleurs personnalisées
	$custom_colors_css = wpvfh_get_custom_colors_css();
	if ( ! empty( $custom_colors_css ) ) {
		wp_add_inline_style( 'wpvfh-admin', $custom_colors_css );
	}

	// Border-radius personnalisés
	$custom_radius_css = wpvfh_get_custom_border_radius_css();
	if ( ! empty( $custom_radius_css ) ) {
		wp_add_inline_style( 'wpvfh-admin', $custom_radius_css );
	}

	// Modules du widget (dans l'ordre de dépendance)
	$widget_modules = array(
		'tools',
		'notifications',
		'core',
		'api',
		'labels',
		'tags',
		'filters',
		'screenshot',
		'media',
		'attachments',
		'mentions',
		'validation',
		'form',
		'list',
		'pages',
		'details',
		'panel',
		'search',
		'events',
		'participants',
	);

	$previous_handle = 'jquery';
	foreach ( $widget_modules as $module ) {
		$handle = 'wpvfh-admin-module-' . $module;
		wp_enqueue_script(
			$handle,
			WPVFH_PLUGIN_URL . 'assets/js/modules/' . $module . '.js',
			array( $previous_handle ),
			WPVFH_VERSION,
			true
		);
		$previous_handle = $handle;
	}

	wp_enqueue_script(
		'wpvfh-admin',
		WPVFH_PLUGIN_URL . 'assets/js/feedback-widget.js',
		array( $previous_handle, 'wp-i18n' ),
		WPVFH_VERSION,
		true
	);

	wp_localize_script( 'wpvfh-admin', 'wpvfhData', wpvfh_get_frontend_data() );
}

/**
 * Générer le CSS inline pour les couleurs personnalisées
 *
 * @since 1.8.0
 * @return string
 */
function wpvfh_get_custom_colors_css() {
	$colors = array(
		'primary'       => get_option( 'wpvfh_color_primary', '#e74c3c' ),
		'primary_hover' => get_option( 'wpvfh_color_primary_hover', '#c0392b' ),
		'secondary'     => get_option( 'wpvfh_color_secondary', '#3498db' ),
		'success'       => get_option( 'wpvfh_color_success', '#27ae60' ),
		'warning'       => get_option( 'wpvfh_color_warning', '#f39c12' ),
		'danger'        => get_option( 'wpvfh_color_danger', '#e74c3c' ),
		'text'          => get_option( 'wpvfh_color_text', '#333333' ),
		'text_light'    => get_option( 'wpvfh_color_text_light', '#666666' ),
		'bg'            => get_option( 'wpvfh_color_bg', '#ffffff' ),
		'bg_light'      => get_option( 'wpvfh_color_bg_light', '#f5f5f5' ),
		'border'        => get_option( 'wpvfh_color_border', '#dddddd' ),
	);

	$defaults = array(
		'primary'       => '#e74c3c',
		'primary_hover' => '#c0392b',
		'secondary'     => '#3498db',
		'success'       => '#27ae60',
		'warning'       => '#f39c12',
		'danger'        => '#e74c3c',
		'text'          => '#333333',
		'text_light'    => '#666666',
		'bg'            => '#ffffff',
		'bg_light'      => '#f5f5f5',
		'border'        => '#dddddd',
	);

	$has_custom = false;
	foreach ( $colors as $key => $value ) {
		if ( strtolower( $value ) !== strtolower( $defaults[ $key ] ) ) {
			$has_custom = true;
			break;
		}
	}

	if ( ! $has_custom ) {
		return '';
	}

	$css = ':root {';
	$css .= '--wpvfh-primary: ' . esc_attr( $colors['primary'] ) . ';';
	$css .= '--wpvfh-primary-hover: ' . esc_attr( $colors['primary_hover'] ) . ';';
	$css .= '--wpvfh-secondary: ' . esc_attr( $colors['secondary'] ) . ';';
	$css .= '--wpvfh-success: ' . esc_attr( $colors['success'] ) . ';';
	$css .= '--wpvfh-warning: ' . esc_attr( $colors['warning'] ) . ';';
	$css .= '--wpvfh-danger: ' . esc_attr( $colors['danger'] ) . ';';
	$css .= '--wpvfh-text: ' . esc_attr( $colors['text'] ) . ';';
	$css .= '--wpvfh-text-light: ' . esc_attr( $colors['text_light'] ) . ';';
	$css .= '--wpvfh-bg: ' . esc_attr( $colors['bg'] ) . ';';
	$css .= '--wpvfh-bg-light: ' . esc_attr( $colors['bg_light'] ) . ';';
	$css .= '--wpvfh-border: ' . esc_attr( $colors['border'] ) . ';';
	$css .= '}';

	return $css;
}

/**
 * Générer le CSS inline pour le border-radius personnalisé
 *
 * @since 1.9.0
 * @return string
 */
function wpvfh_get_custom_border_radius_css() {
	$default = 8;
	$value = intval( get_option( 'wpvfh_border_radius', $default ) );

	if ( $value === $default ) {
		return '';
	}

	$css = ':root {';
	$css .= '--wpvfh-radius: ' . $value . 'px;';
	$css .= '}';

	return $css;
}
