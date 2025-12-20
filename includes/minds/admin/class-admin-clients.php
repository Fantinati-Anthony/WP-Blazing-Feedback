<?php
/**
 * Administration des Clients
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Admin_Clients
 *
 * @since 1.0.0
 */
class BZMI_Admin_Clients {

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
	 * Afficher la liste des clients
	 *
	 * @return void
	 */
	private static function render_list() {
		// Traiter les actions de formulaire
		if ( isset( $_POST['action'] ) && 'save' === $_POST['action'] ) {
			self::handle_save();
		}

		// Pagination
		$per_page = BZMI_Database::get_setting( 'items_per_page', 20 );
		$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;

		// Recherche
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

		// Filtres
		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		// Récupérer les clients
		$args = array(
			'orderby' => 'created_at',
			'order'   => 'DESC',
		);

		if ( ! empty( $search ) ) {
			$clients = BZMI_Client::search( $search );
			$total = count( $clients );
			$clients = array_slice( $clients, ( $current_page - 1 ) * $per_page, $per_page );
		} else {
			if ( ! empty( $status ) ) {
				$args['where'] = array( 'status' => $status );
			}
			$result = BZMI_Client::paginate( $current_page, $per_page, $args );
			$clients = $result['items'];
			$total = $result['total'];
		}

		$statuses = BZMI_Client::get_statuses();

		include BZMI_PLUGIN_DIR . 'templates/minds/admin/clients-list.php';
	}

	/**
	 * Afficher le formulaire d'édition
	 *
	 * @return void
	 */
	private static function render_form() {
		$client_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		$client = $client_id ? BZMI_Client::find( $client_id ) : new BZMI_Client();

		if ( $client_id && ! $client ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-clients',
				__( 'Client introuvable.', 'blazing-minds' ),
				'error'
			);
		}

		$statuses = BZMI_Client::get_statuses();
		$is_new = ! $client_id;

		include BZMI_PLUGIN_DIR . 'templates/minds/admin/clients-form.php';
	}

	/**
	 * Afficher la vue détaillée
	 *
	 * @return void
	 */
	private static function render_view() {
		$client_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		$client = BZMI_Client::find( $client_id );

		if ( ! $client ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-clients',
				__( 'Client introuvable.', 'blazing-minds' ),
				'error'
			);
		}

		$portfolios = $client->portfolios();
		$stats = $client->get_stats();

		include BZMI_PLUGIN_DIR . 'templates/minds/admin/clients-view.php';
	}

	/**
	 * Gérer la sauvegarde
	 *
	 * @return void
	 */
	private static function handle_save() {
		BZMI_Admin::verify_nonce( 'bzmi_save_client' );

		$client_id = isset( $_POST['client_id'] ) ? intval( $_POST['client_id'] ) : 0;
		$client = $client_id ? BZMI_Client::find( $client_id ) : new BZMI_Client();

		if ( $client_id && ! $client ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-clients',
				__( 'Client introuvable.', 'blazing-minds' ),
				'error'
			);
		}

		$client->fill( array(
			'name'    => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'email'   => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'phone'   => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'company' => isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '',
			'address' => isset( $_POST['address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['address'] ) ) : '',
			'website' => isset( $_POST['website'] ) ? esc_url_raw( wp_unslash( $_POST['website'] ) ) : '',
			'notes'   => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
			'status'  => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active',
		) );

		$result = $client->save_validated();

		if ( is_array( $result ) ) {
			// Erreurs de validation
			$error_messages = implode( ' ', $result );
			BZMI_Admin::redirect_with_message(
				'blazing-minds-clients',
				$error_messages,
				'error'
			);
		} else {
			$message = $client_id
				? __( 'Client mis à jour avec succès.', 'blazing-minds' )
				: __( 'Client créé avec succès.', 'blazing-minds' );

			BZMI_Admin::redirect_with_message(
				'blazing-minds-clients',
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
		$client_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

		BZMI_Admin::verify_nonce( 'bzmi_delete_client_' . $client_id );

		$client = BZMI_Client::find( $client_id );

		if ( ! $client ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-clients',
				__( 'Client introuvable.', 'blazing-minds' ),
				'error'
			);
		}

		// Vérifier s'il y a des portefeuilles
		if ( $client->portfolios_count() > 0 ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-clients',
				__( 'Impossible de supprimer ce client car il contient des portefeuilles.', 'blazing-minds' ),
				'error'
			);
		}

		$client->delete();

		BZMI_Admin::redirect_with_message(
			'blazing-minds-clients',
			__( 'Client supprimé avec succès.', 'blazing-minds' ),
			'success'
		);
	}
}
