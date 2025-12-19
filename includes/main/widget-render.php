<?php
/**
 * Rendu du widget HTML de feedback
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Afficher le widget de feedback dans le footer
 *
 * @since 1.0.0
 * @return void
 */
function wpvfh_render_feedback_widget() {
	// Vérifier les permissions
	if ( ! wpvfh_can_user_see_feedback_widget() ) {
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
		wpvfh_render_default_widget();
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
 * NOTE: Cette fonction contient plus de 1000 lignes de HTML.
 * Pour voir le code complet, référez-vous au fichier blazing-feedback.php lignes 758-1779
 *
 * @since 1.0.0
 * @return void
 */
function wpvfh_render_default_widget() {
	// Inclure le template depuis le fichier temporaire
	$template_file = WPVFH_PLUGIN_DIR . 'includes/main/widget-template.php';
	if ( file_exists( $template_file ) ) {
		include $template_file;
	}
}
