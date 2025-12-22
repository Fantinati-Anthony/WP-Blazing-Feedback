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
	 * @since 2.1.0 Support des pages bzmi-* (menu unifié)
	 * @param string $hook Page actuelle
	 * @return void
	 */
	public static function enqueue_admin_styles( $hook ) {
		$screen = get_current_screen();

		// Vérifier si on est sur une page du plugin (WPVFH ou BZMI)
		$is_plugin_page = (
			strpos( $hook, 'wpvfh' ) !== false ||
			strpos( $hook, 'bzmi' ) !== false ||
			strpos( $hook, 'blazing-minds' ) !== false ||
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

		// Charger admin-options.css pour les pages métadatas (wpvfh-options ou bzmi-metadata)
		if ( strpos( $hook, 'wpvfh-options' ) !== false || strpos( $hook, 'bzmi-metadata' ) !== false ) {
			wp_enqueue_style(
				'wpvfh-admin-options',
				WPVFH_PLUGIN_URL . 'assets/css/admin-options.css',
				array( 'wpvfh-admin-settings' ),
				WPVFH_VERSION
			);
		}

		// Charger la bibliothèque de médias sur les pages des paramètres
		if ( strpos( $hook, 'wpvfh-settings' ) !== false || strpos( $hook, 'bzmi-settings' ) !== false ) {
			wp_enqueue_media();
		}
	}
}
