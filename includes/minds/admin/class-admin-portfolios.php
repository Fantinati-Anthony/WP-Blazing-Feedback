<?php
/**
 * Administration des Portefeuilles
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Admin_Portfolios
 *
 * @since 1.0.0
 */
class BZMI_Admin_Portfolios {

	/**
	 * Afficher la page
	 *
	 * @return void
	 */
	public static function render_page() {
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
		if ( isset( $_POST['action'] ) && 'save' === $_POST['action'] ) {
			self::handle_save();
		}

		$per_page = BZMI_Database::get_setting( 'items_per_page', 20 );
		$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$client_id = isset( $_GET['client_id'] ) ? intval( $_GET['client_id'] ) : 0;
		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		$args = array(
			'orderby' => 'created_at',
			'order'   => 'DESC',
		);

		$where = array();
		if ( $client_id ) {
			$where['client_id'] = $client_id;
		}
		if ( $status ) {
			$where['status'] = $status;
		}
		if ( ! empty( $where ) ) {
			$args['where'] = $where;
		}

		$result = BZMI_Portfolio::paginate( $current_page, $per_page, $args );
		$portfolios = $result['items'];
		$total = $result['total'];

		$clients = BZMI_Client::all( array( 'orderby' => 'name', 'order' => 'ASC' ) );
		$statuses = BZMI_Portfolio::get_statuses();

		include BZMI_PLUGIN_DIR . 'templates/minds/admin/portfolios-list.php';
	}

	/**
	 * Afficher le formulaire
	 *
	 * @return void
	 */
	private static function render_form() {
		$portfolio_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		$portfolio = $portfolio_id ? BZMI_Portfolio::find( $portfolio_id ) : new BZMI_Portfolio();

		if ( $portfolio_id && ! $portfolio ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-portfolios',
				__( 'Portefeuille introuvable.', 'blazing-minds' ),
				'error'
			);
		}

		// Pré-remplir le client si passé en paramètre
		if ( ! $portfolio_id && isset( $_GET['client_id'] ) ) {
			$portfolio->client_id = intval( $_GET['client_id'] );
		}

		$clients = BZMI_Client::all( array( 'orderby' => 'name', 'order' => 'ASC' ) );
		$statuses = BZMI_Portfolio::get_statuses();
		$colors = BZMI_Portfolio::get_colors();
		$is_new = ! $portfolio_id;

		include BZMI_PLUGIN_DIR . 'templates/minds/admin/portfolios-form.php';
	}

	/**
	 * Afficher la vue détaillée
	 *
	 * @return void
	 */
	private static function render_view() {
		$portfolio_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		$portfolio = BZMI_Portfolio::find( $portfolio_id );

		if ( ! $portfolio ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-portfolios',
				__( 'Portefeuille introuvable.', 'blazing-minds' ),
				'error'
			);
		}

		$client = $portfolio->client();
		$projects = $portfolio->projects();
		$stats = $portfolio->get_stats();

		include BZMI_PLUGIN_DIR . 'templates/minds/admin/portfolios-view.php';
	}

	/**
	 * Gérer la sauvegarde
	 *
	 * @return void
	 */
	private static function handle_save() {
		BZMI_Admin::verify_nonce( 'bzmi_save_portfolio' );

		$portfolio_id = isset( $_POST['portfolio_id'] ) ? intval( $_POST['portfolio_id'] ) : 0;
		$portfolio = $portfolio_id ? BZMI_Portfolio::find( $portfolio_id ) : new BZMI_Portfolio();

		if ( $portfolio_id && ! $portfolio ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-portfolios',
				__( 'Portefeuille introuvable.', 'blazing-minds' ),
				'error'
			);
		}

		$portfolio->fill( array(
			'client_id'   => isset( $_POST['client_id'] ) ? intval( $_POST['client_id'] ) : 0,
			'name'        => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
			'color'       => isset( $_POST['color'] ) ? sanitize_hex_color( wp_unslash( $_POST['color'] ) ) : '#3498db',
			'icon'        => isset( $_POST['icon'] ) ? sanitize_text_field( wp_unslash( $_POST['icon'] ) ) : '',
			'priority'    => isset( $_POST['priority'] ) ? intval( $_POST['priority'] ) : 0,
			'status'      => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active',
		) );

		$result = $portfolio->save_validated();

		if ( is_array( $result ) ) {
			$error_messages = implode( ' ', $result );
			BZMI_Admin::redirect_with_message(
				'blazing-minds-portfolios',
				$error_messages,
				'error'
			);
		} else {
			$message = $portfolio_id
				? __( 'Portefeuille mis à jour avec succès.', 'blazing-minds' )
				: __( 'Portefeuille créé avec succès.', 'blazing-minds' );

			BZMI_Admin::redirect_with_message(
				'blazing-minds-portfolios',
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
		$portfolio_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

		BZMI_Admin::verify_nonce( 'bzmi_delete_portfolio_' . $portfolio_id );

		$portfolio = BZMI_Portfolio::find( $portfolio_id );

		if ( ! $portfolio ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-portfolios',
				__( 'Portefeuille introuvable.', 'blazing-minds' ),
				'error'
			);
		}

		if ( $portfolio->projects_count() > 0 ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-portfolios',
				__( 'Impossible de supprimer ce portefeuille car il contient des projets.', 'blazing-minds' ),
				'error'
			);
		}

		$portfolio->delete();

		BZMI_Admin::redirect_with_message(
			'blazing-minds-portfolios',
			__( 'Portefeuille supprimé avec succès.', 'blazing-minds' ),
			'success'
		);
	}
}
