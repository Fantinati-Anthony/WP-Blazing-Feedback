<?php
/**
 * Admin Foundations - Classe principale
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Admin_Foundations
 *
 * Gère l'interface d'administration des Fondations
 *
 * @since 2.0.0
 */
class BZMI_Admin_Foundations {

	/**
	 * Enregistrer le menu admin
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function register_menu() {
		// Sous-menu Fondations dans Blazing Minds
		add_submenu_page(
			'blazing-minds',
			__( 'Fondations', 'blazing-feedback' ),
			__( 'Fondations', 'blazing-feedback' ),
			'bzmi_view_foundations',
			'bzmi-foundations',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Afficher la page principale
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function render_page() {
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
		$id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$tab    = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'identity';

		switch ( $action ) {
			case 'view':
			case 'edit':
				if ( $id ) {
					self::render_foundation_detail( $id, $tab );
				} else {
					self::render_list();
				}
				break;

			case 'new':
				self::render_new_foundation();
				break;

			default:
				self::render_list();
				break;
		}
	}

	/**
	 * Afficher la liste des fondations
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function render_list() {
		$foundations = BZMI_Foundation::all( array(
			'orderby' => 'updated_at',
			'order'   => 'DESC',
		) );

		include WPVFH_PLUGIN_DIR . 'templates/minds/admin/foundations/list.php';
	}

	/**
	 * Afficher le détail d'une fondation
	 *
	 * @since 2.0.0
	 * @param int    $id  ID de la fondation.
	 * @param string $tab Onglet actif.
	 * @return void
	 */
	public static function render_foundation_detail( $id, $tab ) {
		$foundation = BZMI_Foundation::find( $id );

		if ( ! $foundation ) {
			wp_die( __( 'Fondation introuvable.', 'blazing-feedback' ) );
		}

		$client = $foundation->get_client();

		// Charger les données selon l'onglet
		$data = array(
			'foundation' => $foundation,
			'client'     => $client,
			'tab'        => $tab,
		);

		switch ( $tab ) {
			case 'identity':
				$data['identity_sections'] = array();
				foreach ( BZMI_Foundation::IDENTITY_SECTIONS as $section_key => $section_label ) {
					$data['identity_sections'][ $section_key ] = array(
						'label' => $section_label,
						'data'  => $foundation->get_identity_section( $section_key ),
					);
				}
				$data['personas'] = $foundation->get_personas();
				break;

			case 'offer':
				$data['offers']      = $foundation->get_offers();
				$data['competitors'] = $foundation->get_competitors();
				$data['personas']    = $foundation->get_personas();
				break;

			case 'experience':
				$data['journeys'] = $foundation->get_journeys();
				$data['channels'] = $foundation->get_channels();
				$data['personas'] = $foundation->get_personas();
				break;

			case 'execution':
				$data['execution_sections'] = array();
				foreach ( BZMI_Foundation::EXECUTION_SECTIONS as $section_key => $section_label ) {
					$data['execution_sections'][ $section_key ] = array(
						'label' => $section_label,
						'data'  => $foundation->get_execution_section( $section_key ),
					);
				}
				break;

			case 'ai':
				$data['ai_logs'] = self::get_ai_logs( $id );
				break;
		}

		include WPVFH_PLUGIN_DIR . 'templates/minds/admin/foundations/detail.php';
	}

	/**
	 * Afficher le formulaire de nouvelle fondation
	 *
	 * @since 2.0.0
	 * @since 2.1.0 Support de plusieurs fondations par client
	 * @return void
	 */
	public static function render_new_foundation() {
		// Récupérer tous les clients avec le nombre de fondations
		$all_clients = BZMI_Client::all();
		$clients_with_count = array();

		foreach ( $all_clients as $client ) {
			$foundation_count = BZMI_Foundation::count( array( 'client_id' => $client->id ) );
			$client->foundation_count = $foundation_count;
			$clients_with_count[] = $client;
		}

		include WPVFH_PLUGIN_DIR . 'templates/minds/admin/foundations/new.php';
	}

	/**
	 * Obtenir les logs IA d'une fondation
	 *
	 * @since 2.0.0
	 * @param int $foundation_id ID de la fondation.
	 * @return array
	 */
	public static function get_ai_logs( $foundation_id ) {
		global $wpdb;

		$table = BZMI_Database::get_table_name( 'foundation_ai_logs' );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE foundation_id = %d ORDER BY created_at DESC LIMIT 50",
				$foundation_id
			),
			ARRAY_A
		);
	}

	/**
	 * Obtenir les statistiques des fondations
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_stats() {
		return array(
			'total'       => BZMI_Foundation::count(),
			'draft'       => BZMI_Foundation::count( array( 'status' => 'draft' ) ),
			'active'      => BZMI_Foundation::count( array( 'status' => 'active' ) ),
			'avg_score'   => self::get_average_completion_score(),
		);
	}

	/**
	 * Calculer le score de complétion moyen
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public static function get_average_completion_score() {
		global $wpdb;

		$table = BZMI_Database::get_table_name( 'foundations' );

		$avg = $wpdb->get_var( "SELECT AVG(completion_score) FROM {$table}" );

		return (int) round( $avg );
	}

	/**
	 * Obtenir les onglets disponibles
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_tabs() {
		return array(
			'identity'   => array(
				'label' => __( 'Identité', 'blazing-feedback' ),
				'icon'  => 'dashicons-id',
			),
			'offer'      => array(
				'label' => __( 'Offre & Marché', 'blazing-feedback' ),
				'icon'  => 'dashicons-cart',
			),
			'experience' => array(
				'label' => __( 'Expérience', 'blazing-feedback' ),
				'icon'  => 'dashicons-admin-users',
			),
			'execution'  => array(
				'label' => __( 'Exécution', 'blazing-feedback' ),
				'icon'  => 'dashicons-clipboard',
			),
			'ai'         => array(
				'label' => __( 'IA & Insights', 'blazing-feedback' ),
				'icon'  => 'dashicons-superhero',
			),
		);
	}

	/**
	 * Gérer les actions AJAX
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function handle_ajax() {
		check_ajax_referer( 'bzmi_nonce', 'nonce' );

		if ( ! current_user_can( 'bzmi_manage_foundations' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission refusée.', 'blazing-feedback' ) ) );
		}

		$action = isset( $_POST['foundation_action'] ) ? sanitize_text_field( wp_unslash( $_POST['foundation_action'] ) ) : '';

		switch ( $action ) {
			case 'create':
				self::ajax_create();
				break;

			case 'update':
				self::ajax_update();
				break;

			case 'delete':
				self::ajax_delete();
				break;

			case 'recalculate_scores':
				self::ajax_recalculate_scores();
				break;

			default:
				wp_send_json_error( array( 'message' => __( 'Action inconnue.', 'blazing-feedback' ) ) );
		}
	}

	/**
	 * AJAX: Créer une fondation
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_create() {
		$client_id = isset( $_POST['client_id'] ) ? absint( $_POST['client_id'] ) : 0;

		if ( ! $client_id ) {
			wp_send_json_error( array( 'message' => __( 'Client requis.', 'blazing-feedback' ) ) );
		}

		// Vérifier qu'une fondation n'existe pas déjà
		$existing = BZMI_Foundation::first_where( array( 'client_id' => $client_id ) );
		if ( $existing ) {
			wp_send_json_error( array( 'message' => __( 'Ce client a déjà une fondation.', 'blazing-feedback' ) ) );
		}

		$foundation = BZMI_Foundation::get_or_create_for_client( $client_id );

		if ( $foundation ) {
			wp_send_json_success( array(
				'id'      => $foundation->id,
				'message' => __( 'Fondation créée avec succès.', 'blazing-feedback' ),
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la création.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Mettre à jour une fondation
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_update() {
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		$foundation = BZMI_Foundation::find( $id );
		if ( ! $foundation ) {
			wp_send_json_error( array( 'message' => __( 'Fondation introuvable.', 'blazing-feedback' ) ) );
		}

		$fields = array( 'name', 'status' );
		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$foundation->$field = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
			}
		}

		if ( $foundation->save() ) {
			wp_send_json_success( array( 'message' => __( 'Fondation mise à jour.', 'blazing-feedback' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Supprimer une fondation
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_delete() {
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		$foundation = BZMI_Foundation::find( $id );
		if ( ! $foundation ) {
			wp_send_json_error( array( 'message' => __( 'Fondation introuvable.', 'blazing-feedback' ) ) );
		}

		if ( $foundation->delete() ) {
			wp_send_json_success( array( 'message' => __( 'Fondation supprimée.', 'blazing-feedback' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la suppression.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Recalculer les scores
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_recalculate_scores() {
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		$foundation = BZMI_Foundation::find( $id );
		if ( ! $foundation ) {
			wp_send_json_error( array( 'message' => __( 'Fondation introuvable.', 'blazing-feedback' ) ) );
		}

		$foundation->recalculate_scores();

		wp_send_json_success( array(
			'message'          => __( 'Scores recalculés.', 'blazing-feedback' ),
			'completion_score' => $foundation->completion_score,
			'identity_score'   => $foundation->identity_score,
			'offer_score'      => $foundation->offer_score,
			'experience_score' => $foundation->experience_score,
			'execution_score'  => $foundation->execution_score,
		) );
	}
}

// Enregistrer les handlers AJAX
add_action( 'wp_ajax_bzmi_foundation', array( 'BZMI_Admin_Foundations', 'handle_ajax' ) );
