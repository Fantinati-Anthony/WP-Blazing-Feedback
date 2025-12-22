<?php
/**
 * Trait pour la gestion des assets admin (styles CSS)
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait de gestion des assets admin
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Assets {

	/**
	 * Charger les styles admin
	 *
	 * @since 1.0.0
	 * @param string $hook Page actuelle
	 * @return void
	 */
	public static function enqueue_admin_styles( $hook ) {
		$screen = get_current_screen();

		// Vérifier si on est sur une page du plugin
		$is_plugin_page = (
			strpos( $hook, 'wpvfh' ) !== false ||
			( $screen && $screen->post_type === 'visual_feedback' )
		);

		if ( ! $is_plugin_page ) {
			return;
		}

		// Charger le CSS unifié pour toutes les pages admin du plugin
		wp_enqueue_style(
			'wpvfh-admin-settings',
			WPVFH_PLUGIN_URL . 'assets/css/admin-settings.css',
			array(),
			WPVFH_VERSION
		);

		// Charger admin-options.css aussi pour les pages métadatas
		if ( strpos( $hook, 'wpvfh-options' ) !== false ) {
			wp_enqueue_style(
				'wpvfh-admin-options',
				WPVFH_PLUGIN_URL . 'assets/css/admin-options.css',
				array( 'wpvfh-admin-settings' ),
				WPVFH_VERSION
			);
		}

		// Charger la bibliothèque de médias sur la page des paramètres
		if ( strpos( $hook, 'wpvfh-settings' ) !== false ) {
			wp_enqueue_media();
		}
	}
}
