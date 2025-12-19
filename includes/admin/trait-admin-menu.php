<?php
/**
 * Trait pour la gestion des menus admin
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait de gestion des menus admin
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Menu {

	/**
	 * Ajouter les pages de menu admin
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_menu_pages() {
		// Page principale - Dashboard
		add_menu_page(
			__( 'Blazing Feedback', 'blazing-feedback' ),
			__( 'Feedbacks', 'blazing-feedback' ),
			'edit_feedbacks',
			'wpvfh-dashboard',
			array( __CLASS__, 'render_dashboard_page' ),
			'dashicons-format-chat',
			30
		);

		// Sous-page - Dashboard (redirection pour remplacer le titre auto)
		add_submenu_page(
			'wpvfh-dashboard',
			__( 'Tableau de bord', 'blazing-feedback' ),
			__( 'Tableau de bord', 'blazing-feedback' ),
			'edit_feedbacks',
			'wpvfh-dashboard',
			array( __CLASS__, 'render_dashboard_page' )
		);

		// Note: "Tous les feedbacks" est ajouté automatiquement par le CPT
		// avec show_in_menu => 'wpvfh-dashboard'

		// Sous-page - Paramètres
		add_submenu_page(
			'wpvfh-dashboard',
			__( 'Paramètres', 'blazing-feedback' ),
			__( 'Paramètres', 'blazing-feedback' ),
			'manage_feedback',
			'wpvfh-settings',
			array( __CLASS__, 'render_settings_page' )
		);
	}
}
