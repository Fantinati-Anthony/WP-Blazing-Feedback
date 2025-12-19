<?php
/**
 * Vérifications des permissions utilisateur
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vérifier si l'utilisateur peut voir le widget de feedback
 *
 * @since 1.0.0
 * @return bool
 */
function wpvfh_can_user_see_feedback_widget() {
	// Les utilisateurs non connectés ne peuvent pas voir le widget par défaut
	if ( ! is_user_logged_in() ) {
		/**
		 * Filtre pour autoriser les utilisateurs non connectés à voir le widget
		 *
		 * @since 1.0.0
		 * @param bool $allow Autoriser ou non (défaut: false)
		 */
		return apply_filters( 'wpvfh_allow_guest_feedback', false );
	}

	// Vérifier les capacités
	return current_user_can( 'publish_feedbacks' ) || current_user_can( 'moderate_feedback' ) || current_user_can( 'manage_feedback' );
}

/**
 * Vérifier si les screenshots sont activés
 *
 * @since 1.0.0
 * @return bool
 */
function wpvfh_is_screenshot_enabled() {
	/**
	 * Filtre pour activer/désactiver les captures d'écran
	 *
	 * @since 1.0.0
	 * @param bool $enabled Activé ou non (défaut: true)
	 */
	return apply_filters( 'wpvfh_screenshot_enabled', get_option( 'wpvfh_screenshot_enabled', true ) );
}
