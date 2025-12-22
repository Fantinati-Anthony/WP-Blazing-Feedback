<?php
/**
 * Classe principale d'administration
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Admin
 *
 * Gère l'interface d'administration principale
 *
 * @since 1.0.0
 */
class BZMI_Admin {

	/**
	 * Enregistrer le menu d'administration
	 *
	 * @return void
	 */
	public static function register_menu() {
		global $menu, $submenu;

		// Menu principal - Position 2 = juste après le Dashboard
		add_menu_page(
			__( 'Blazing Minds', 'blazing-minds' ),
			__( 'Blazing Minds', 'blazing-minds' ),
			'bzmi_view_reports',
			'blazing-minds',
			array( __CLASS__, 'render_dashboard' ),
			'dashicons-lightbulb',
			2
		);

		// Tableau de bord (remplace le titre auto-généré)
		add_submenu_page(
			'blazing-minds',
			__( 'Tableau de bord', 'blazing-minds' ),
			__( 'Tableau de bord', 'blazing-minds' ),
			'bzmi_view_reports',
			'blazing-minds',
			array( __CLASS__, 'render_dashboard' )
		);

		// Clients
		add_submenu_page(
			'blazing-minds',
			__( 'Clients', 'blazing-minds' ),
			__( 'Clients', 'blazing-minds' ),
			'bzmi_manage_clients',
			'bzmi-clients',
			array( 'BZMI_Admin_Clients', 'render_page' )
		);

		// Fondations
		add_submenu_page(
			'blazing-minds',
			__( 'Fondations', 'blazing-minds' ),
			__( 'Fondations', 'blazing-minds' ),
			'bzmi_manage_foundations',
			'bzmi-foundations',
			array( 'BZMI_Admin_Foundations', 'render_page' )
		);

		// Portefeuilles
		add_submenu_page(
			'blazing-minds',
			__( 'Portefeuilles', 'blazing-minds' ),
			__( 'Portefeuilles', 'blazing-minds' ),
			'bzmi_manage_portfolios',
			'bzmi-portfolios',
			array( 'BZMI_Admin_Portfolios', 'render_page' )
		);

		// Projets
		add_submenu_page(
			'blazing-minds',
			__( 'Projets', 'blazing-minds' ),
			__( 'Projets', 'blazing-minds' ),
			'bzmi_manage_projects',
			'bzmi-projects',
			array( 'BZMI_Admin_Projects', 'render_page' )
		);

		// Cycle ICAVAL
		add_submenu_page(
			'blazing-minds',
			__( 'Cycle ICAVAL', 'blazing-minds' ),
			__( 'Cycle ICAVAL', 'blazing-minds' ),
			'bzmi_manage_icaval',
			'bzmi-icaval',
			array( 'BZMI_Admin_ICAVAL', 'render_page' )
		);

		// Note: Le CPT feedback s'ajoute automatiquement ici
		// avec show_in_menu => 'blazing-minds'

		// Métadatas
		add_submenu_page(
			'blazing-minds',
			__( 'Métadatas', 'blazing-minds' ),
			__( 'Métadatas', 'blazing-minds' ),
			'manage_feedback',
			'bzmi-metadata',
			array( 'WPVFH_Options_Manager', 'render_options_page' )
		);

		// Réglages
		add_submenu_page(
			'blazing-minds',
			__( 'Réglages', 'blazing-minds' ),
			__( 'Réglages', 'blazing-minds' ),
			'bzmi_manage_settings',
			'bzmi-settings',
			array( 'BZMI_Admin_Settings', 'render_page' )
		);
	}

	/**
	 * Afficher le tableau de bord
	 *
	 * @return void
	 */
	public static function render_dashboard() {
		// Statistiques globales
		$stats = self::get_dashboard_stats();

		// Activités récentes
		$recent_activities = self::get_recent_activities( 10 );

		// Informations en attente d'action
		$pending_informations = BZMI_Information::all( array(
			'where' => array( 'status' => 'new' ),
			'limit' => 5,
			'orderby' => 'created_at',
			'order' => 'DESC',
		) );

		// Actions en retard
		$overdue_actions = BZMI_Action::overdue();

		include BZMI_PLUGIN_DIR . 'templates/minds/admin/dashboard.php';
	}

	/**
	 * Obtenir les statistiques du tableau de bord
	 *
	 * @return array
	 */
	public static function get_dashboard_stats() {
		return array(
			'clients'       => BZMI_Client::count(),
			'portfolios'    => BZMI_Portfolio::count(),
			'projects'      => BZMI_Project::count(),
			'active_projects' => BZMI_Project::count( array( 'status' => 'active' ) ) + BZMI_Project::count( array( 'status' => 'in_progress' ) ),
			'informations'  => BZMI_Information::count(),
			'new_informations' => BZMI_Information::count( array( 'status' => 'new' ) ),
			'actions'       => BZMI_Action::count(),
			'pending_actions' => BZMI_Action::count( array( 'status' => 'pending' ) ),
			'apprenticeships' => BZMI_Apprenticeship::count(),
			'icaval_stages' => self::get_icaval_stage_counts(),
		);
	}

	/**
	 * Obtenir le nombre d'informations par étape ICAVAL
	 *
	 * @return array
	 */
	public static function get_icaval_stage_counts() {
		global $wpdb;

		$table = BZMI_Database::get_table_name( 'informations' );

		$results = $wpdb->get_results(
			"SELECT icaval_stage, COUNT(*) as count FROM {$table} GROUP BY icaval_stage",
			ARRAY_A
		);

		$counts = array();
		foreach ( $results as $row ) {
			$counts[ $row['icaval_stage'] ] = (int) $row['count'];
		}

		return $counts;
	}

	/**
	 * Obtenir les activités récentes
	 *
	 * @param int $limit Nombre d'activités.
	 * @return array
	 */
	public static function get_recent_activities( $limit = 10 ) {
		global $wpdb;

		$table = BZMI_Database::get_table_name( 'activity_log' );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);
	}

	/**
	 * Afficher un message d'admin
	 *
	 * @param string $message Message.
	 * @param string $type    Type (success, error, warning, info).
	 * @return void
	 */
	public static function admin_notice( $message, $type = 'success' ) {
		printf(
			'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
			esc_attr( $type ),
			esc_html( $message )
		);
	}

	/**
	 * Vérifier le nonce
	 *
	 * @param string $action Action du nonce.
	 * @param string $name   Nom du champ nonce.
	 * @return bool
	 */
	public static function verify_nonce( $action, $name = '_wpnonce' ) {
		if ( ! isset( $_REQUEST[ $name ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ $name ] ) ), $action ) ) {
			wp_die( esc_html__( 'Erreur de sécurité. Veuillez réessayer.', 'blazing-minds' ) );
		}
		return true;
	}

	/**
	 * Rediriger avec un message
	 *
	 * @param string $page    Page de destination.
	 * @param string $message Message.
	 * @param string $type    Type de message.
	 * @return void
	 */
	public static function redirect_with_message( $page, $message, $type = 'success' ) {
		$url = add_query_arg( array(
			'page'    => $page,
			'message' => urlencode( $message ),
			'type'    => $type,
		), admin_url( 'admin.php' ) );

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Afficher les messages de la query string
	 *
	 * @return void
	 */
	public static function display_messages() {
		if ( isset( $_GET['message'] ) && isset( $_GET['type'] ) ) {
			self::admin_notice(
				sanitize_text_field( wp_unslash( $_GET['message'] ) ),
				sanitize_text_field( wp_unslash( $_GET['type'] ) )
			);
		}
	}

	/**
	 * Générer une pagination
	 *
	 * @param int    $total    Nombre total d'éléments.
	 * @param int    $per_page Éléments par page.
	 * @param int    $current  Page actuelle.
	 * @param string $base_url URL de base.
	 * @return string
	 */
	public static function pagination( $total, $per_page, $current, $base_url ) {
		$total_pages = ceil( $total / $per_page );

		if ( $total_pages <= 1 ) {
			return '';
		}

		$output = '<div class="tablenav"><div class="tablenav-pages">';
		$output .= sprintf(
			'<span class="displaying-num">%s</span>',
			sprintf(
				/* translators: %s: Number of items */
				_n( '%s élément', '%s éléments', $total, 'blazing-minds' ),
				number_format_i18n( $total )
			)
		);

		$output .= '<span class="pagination-links">';

		// Première page
		if ( $current > 1 ) {
			$output .= sprintf(
				'<a class="first-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">«</span></a>',
				esc_url( add_query_arg( 'paged', 1, $base_url ) ),
				esc_html__( 'Première page', 'blazing-minds' )
			);
			$output .= sprintf(
				'<a class="prev-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">‹</span></a>',
				esc_url( add_query_arg( 'paged', $current - 1, $base_url ) ),
				esc_html__( 'Page précédente', 'blazing-minds' )
			);
		} else {
			$output .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>';
			$output .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>';
		}

		$output .= sprintf(
			'<span class="paging-input">%d / <span class="total-pages">%d</span></span>',
			$current,
			$total_pages
		);

		// Dernière page
		if ( $current < $total_pages ) {
			$output .= sprintf(
				'<a class="next-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">›</span></a>',
				esc_url( add_query_arg( 'paged', $current + 1, $base_url ) ),
				esc_html__( 'Page suivante', 'blazing-minds' )
			);
			$output .= sprintf(
				'<a class="last-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">»</span></a>',
				esc_url( add_query_arg( 'paged', $total_pages, $base_url ) ),
				esc_html__( 'Dernière page', 'blazing-minds' )
			);
		} else {
			$output .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>';
			$output .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>';
		}

		$output .= '</span></div></div>';

		return $output;
	}
}
