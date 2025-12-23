<?php
/**
 * Administration des Projets
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Admin_Projects
 *
 * @since 1.0.0
 */
class BZMI_Admin_Projects {

	/**
	 * Afficher la page
	 *
	 * @return void
	 */
	public static function render_page() {
		// Traiter la sauvegarde en premier (POST)
		if ( isset( $_POST['action'] ) && 'save' === $_POST['action'] ) {
			self::handle_save();
			return;
		}

		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';

		BZMI_Admin::display_messages();

		switch ( $action ) {
			case 'new':
			case 'edit':
				self::render_form();
				break;
			case 'delete':
				self::handle_delete();
				break;
			case 'view':
				self::render_view();
				break;
			default:
				self::render_list();
		}
	}

	/**
	 * Afficher la liste
	 *
	 * @return void
	 */
	private static function render_list() {
		$per_page = BZMI_Database::get_setting( 'items_per_page', 20 );
		$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$portfolio_id = isset( $_GET['portfolio_id'] ) ? intval( $_GET['portfolio_id'] ) : 0;
		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		$args = array(
			'orderby' => 'created_at',
			'order'   => 'DESC',
		);

		$where = array();
		if ( $portfolio_id ) {
			$where['portfolio_id'] = $portfolio_id;
		}
		if ( $status ) {
			$where['status'] = $status;
		}
		if ( ! empty( $where ) ) {
			$args['where'] = $where;
		}

		$result = BZMI_Project::paginate( $current_page, $per_page, $args );
		$projects = $result['items'];
		$total = $result['total'];

		$portfolios = BZMI_Portfolio::all( array( 'orderby' => 'name', 'order' => 'ASC' ) );
		$statuses = BZMI_Project::get_statuses();

		include BZMI_PLUGIN_DIR . 'templates/minds/admin/projects-list.php';
	}

	/**
	 * Afficher le formulaire
	 *
	 * @return void
	 */
	private static function render_form() {
		$project_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		$project = $project_id ? BZMI_Project::find( $project_id ) : new BZMI_Project();

		if ( $project_id && ! $project ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-projects',
				__( 'Projet introuvable.', 'blazing-minds' ),
				'error'
			);
		}

		if ( ! $project_id && isset( $_GET['portfolio_id'] ) ) {
			$project->portfolio_id = intval( $_GET['portfolio_id'] );
		}

		$clients = BZMI_Client::all( array( 'orderby' => 'name', 'order' => 'ASC' ) );
		$portfolios = BZMI_Portfolio::all( array( 'orderby' => 'name', 'order' => 'ASC' ) );
		$foundations = BZMI_Foundation::all( array( 'orderby' => 'name', 'order' => 'ASC' ) );
		$statuses = BZMI_Project::get_statuses();
		$priorities = BZMI_Project::get_priorities();
		$is_new = ! $project_id;

		include BZMI_PLUGIN_DIR . 'templates/minds/admin/projects-form.php';
	}

	/**
	 * Afficher la vue détaillée avec le cycle ICAVAL
	 *
	 * @return void
	 */
	private static function render_view() {
		$project_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		$project = BZMI_Project::find( $project_id );

		if ( ! $project ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-projects',
				__( 'Projet introuvable.', 'blazing-minds' ),
				'error'
			);
		}

		$portfolio = $project->portfolio();
		$client = $project->client();
		$informations = $project->informations();
		$icaval_stats = $project->get_icaval_stats();
		$users = $project->users();

		include BZMI_PLUGIN_DIR . 'templates/minds/admin/projects-view.php';
	}

	/**
	 * Gérer la sauvegarde
	 *
	 * @return void
	 */
	private static function handle_save() {
		BZMI_Admin::verify_nonce( 'bzmi_save_project' );

		$project_id = isset( $_POST['project_id'] ) ? intval( $_POST['project_id'] ) : 0;
		$project = $project_id ? BZMI_Project::find( $project_id ) : new BZMI_Project();

		if ( $project_id && ! $project ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-projects',
				__( 'Projet introuvable.', 'blazing-minds' ),
				'error'
			);
		}

		$project->fill( array(
			'portfolio_id'   => isset( $_POST['portfolio_id'] ) ? intval( $_POST['portfolio_id'] ) : 0,
			'foundation_id'  => isset( $_POST['foundation_id'] ) ? intval( $_POST['foundation_id'] ) : null,
			'name'           => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'description'    => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
			'start_date'     => isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : null,
			'end_date'       => isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : null,
			'budget'         => isset( $_POST['budget'] ) ? floatval( $_POST['budget'] ) : 0,
			'color'          => isset( $_POST['color'] ) ? sanitize_hex_color( wp_unslash( $_POST['color'] ) ) : '#2ecc71',
			'priority'       => isset( $_POST['priority'] ) ? intval( $_POST['priority'] ) : 0,
			'status'         => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'pending',
		) );

		$result = $project->save_validated();

		if ( is_array( $result ) ) {
			$error_messages = implode( ' ', $result );
			BZMI_Admin::redirect_with_message(
				'blazing-minds-projects',
				$error_messages,
				'error'
			);
		} else {
			$message = $project_id
				? __( 'Projet mis à jour avec succès.', 'blazing-minds' )
				: __( 'Projet créé avec succès.', 'blazing-minds' );

			BZMI_Admin::redirect_with_message(
				'blazing-minds-projects',
				$message,
				'success'
			);
		}
	}

	/**
	 * Gérer la suppression
	 *
	 * @return void
	 */
	private static function handle_delete() {
		$project_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

		BZMI_Admin::verify_nonce( 'bzmi_delete_project_' . $project_id );

		$project = BZMI_Project::find( $project_id );

		if ( ! $project ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-projects',
				__( 'Projet introuvable.', 'blazing-minds' ),
				'error'
			);
		}

		if ( $project->informations_count() > 0 ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-projects',
				__( 'Impossible de supprimer ce projet car il contient des informations.', 'blazing-minds' ),
				'error'
			);
		}

		$project->delete();

		BZMI_Admin::redirect_with_message(
			'blazing-minds-projects',
			__( 'Projet supprimé avec succès.', 'blazing-minds' ),
			'success'
		);
	}
}
