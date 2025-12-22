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
 * Note: Le menu principal Feedbacks a été fusionné dans le menu unifié Blazing Minds.
 * Ce trait est conservé pour la rétrocompatibilité et les pages de paramètres.
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Menu {

	/**
	 * Ajouter les pages de menu admin
	 *
	 * Note: L'ancien menu Feedbacks séparé a été supprimé.
	 * Tout est maintenant dans le menu unifié Blazing Minds (voir BZMI_Admin).
	 * Le CPT feedback utilise show_in_menu => 'blazing-minds'.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Menu fusionné dans Blazing Minds
	 * @return void
	 */
	public static function add_menu_pages() {
		// Menu unifié: tout est géré par BZMI_Admin::register_menu()
		// Cette fonction est conservée pour la rétrocompatibilité des hooks
	}
}
