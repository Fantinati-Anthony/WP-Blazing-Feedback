<?php
/**
 * REST API pour les Fondations
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_REST_Foundations
 *
 * Gère les endpoints REST API pour les Fondations
 *
 * @since 2.0.0
 */
class BZMI_REST_Foundations {

	/**
	 * Namespace de l'API
	 *
	 * @var string
	 */
	const NAMESPACE = 'blazing-minds/v1';

	/**
	 * Enregistrer les routes
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function register_routes() {
		// Fondations
		register_rest_route( self::NAMESPACE, '/foundations', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_foundations' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_foundation' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
		) );

		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_foundation' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_foundation' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_foundation' ),
				'permission_callback' => array( __CLASS__, 'check_delete_permission' ),
			),
		) );

		// Contexte IA complet
		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)/context', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'get_foundation_context' ),
			'permission_callback' => array( __CLASS__, 'check_read_permission' ),
		) );

		// Identité
		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)/identity', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_identity' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
		) );

		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)/identity/(?P<section>[a-z_]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_identity_section' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_identity_section' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
		) );

		// Personas
		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)/personas', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_personas' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_persona' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
		) );

		register_rest_route( self::NAMESPACE, '/personas/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_persona' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_persona' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_persona' ),
				'permission_callback' => array( __CLASS__, 'check_delete_permission' ),
			),
		) );

		// Offres
		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)/offers', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_offers' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_offer' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
		) );

		register_rest_route( self::NAMESPACE, '/offers/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_offer' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_offer' ),
				'permission_callback' => array( __CLASS__, 'check_delete_permission' ),
			),
		) );

		// Concurrents
		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)/competitors', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_competitors' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_competitor' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
		) );

		register_rest_route( self::NAMESPACE, '/competitors/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_competitor' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_competitor' ),
				'permission_callback' => array( __CLASS__, 'check_delete_permission' ),
			),
		) );

		// Parcours
		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)/journeys', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_journeys' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_journey' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
		) );

		register_rest_route( self::NAMESPACE, '/journeys/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_journey' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_journey' ),
				'permission_callback' => array( __CLASS__, 'check_delete_permission' ),
			),
		) );

		// Canaux
		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)/channels', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_channels' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_channel' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
		) );

		register_rest_route( self::NAMESPACE, '/channels/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_channel' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_channel' ),
				'permission_callback' => array( __CLASS__, 'check_delete_permission' ),
			),
		) );

		// Exécution
		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)/execution', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'get_execution' ),
			'permission_callback' => array( __CLASS__, 'check_read_permission' ),
		) );

		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)/execution/(?P<section>[a-z_]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_execution_section' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_execution_section' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
		) );

		// IA
		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)/ai/enrich', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'ai_enrich' ),
			'permission_callback' => array( __CLASS__, 'check_ai_permission' ),
		) );

		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)/ai/suggest', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'ai_suggest' ),
			'permission_callback' => array( __CLASS__, 'check_ai_permission' ),
		) );

		register_rest_route( self::NAMESPACE, '/foundations/(?P<id>\d+)/ai/audit', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'ai_audit' ),
			'permission_callback' => array( __CLASS__, 'check_ai_permission' ),
		) );
	}

	// ========================
	// PERMISSIONS
	// ========================

	/**
	 * Vérifier la permission de lecture
	 *
	 * @return bool
	 */
	public static function check_read_permission() {
		return current_user_can( 'bzmi_view_foundations' ) || current_user_can( 'manage_options' );
	}

	/**
	 * Vérifier la permission d'écriture
	 *
	 * @return bool
	 */
	public static function check_write_permission() {
		return current_user_can( 'bzmi_edit_foundations' ) || current_user_can( 'manage_options' );
	}

	/**
	 * Vérifier la permission de suppression
	 *
	 * @return bool
	 */
	public static function check_delete_permission() {
		return current_user_can( 'bzmi_delete_foundations' ) || current_user_can( 'manage_options' );
	}

	/**
	 * Vérifier la permission IA
	 *
	 * @return bool
	 */
	public static function check_ai_permission() {
		return current_user_can( 'bzmi_use_foundations_ai' ) || current_user_can( 'manage_options' );
	}

	// ========================
	// FONDATIONS
	// ========================

	/**
	 * GET /foundations
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response
	 */
	public static function get_foundations( $request ) {
		$args = array(
			'orderby' => $request->get_param( 'orderby' ) ?: 'updated_at',
			'order'   => $request->get_param( 'order' ) ?: 'DESC',
		);

		if ( $request->get_param( 'client_id' ) ) {
			$args['where'] = array( 'client_id' => absint( $request->get_param( 'client_id' ) ) );
		}

		$foundations = BZMI_Foundation::all( $args );

		$data = array_map( function( $foundation ) {
			$client = $foundation->get_client();
			$arr = $foundation->to_array();
			$arr['client_name'] = $client ? $client->name : '';
			$arr['company_mode'] = $client ? $client->company_mode : 'existing';
			return $arr;
		}, $foundations );

		return rest_ensure_response( $data );
	}

	/**
	 * GET /foundations/{id}
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_foundation( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );

		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		$client = $foundation->get_client();
		$data = $foundation->to_array();
		$data['client'] = $client ? $client->to_array() : null;

		return rest_ensure_response( $data );
	}

	/**
	 * POST /foundations
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function create_foundation( $request ) {
		$client_id = absint( $request->get_param( 'client_id' ) );

		if ( ! $client_id ) {
			return new WP_Error( 'missing_client', __( 'Client requis.', 'blazing-feedback' ), array( 'status' => 400 ) );
		}

		$existing = BZMI_Foundation::first_where( array( 'client_id' => $client_id ) );
		if ( $existing ) {
			return new WP_Error( 'already_exists', __( 'Ce client a déjà une fondation.', 'blazing-feedback' ), array( 'status' => 409 ) );
		}

		$foundation = BZMI_Foundation::get_or_create_for_client( $client_id );

		if ( $foundation ) {
			return rest_ensure_response( array(
				'id'      => $foundation->id,
				'message' => __( 'Fondation créée.', 'blazing-feedback' ),
			) );
		}

		return new WP_Error( 'creation_failed', __( 'Erreur lors de la création.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	/**
	 * PUT /foundations/{id}
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function update_foundation( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );

		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		$params = $request->get_json_params();
		$allowed = array( 'name', 'status' );

		foreach ( $allowed as $field ) {
			if ( isset( $params[ $field ] ) ) {
				$foundation->$field = sanitize_text_field( $params[ $field ] );
			}
		}

		if ( $foundation->save() ) {
			return rest_ensure_response( $foundation->to_array() );
		}

		return new WP_Error( 'update_failed', __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	/**
	 * DELETE /foundations/{id}
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function delete_foundation( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );

		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		if ( $foundation->delete() ) {
			return rest_ensure_response( array( 'deleted' => true ) );
		}

		return new WP_Error( 'delete_failed', __( 'Erreur lors de la suppression.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	/**
	 * GET /foundations/{id}/context
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_foundation_context( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );

		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $foundation->get_ai_context() );
	}

	// ========================
	// IDENTITÉ
	// ========================

	/**
	 * GET /foundations/{id}/identity
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_identity( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );

		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		$sections = array();
		foreach ( BZMI_Foundation::IDENTITY_SECTIONS as $key => $label ) {
			$section = $foundation->get_identity_section( $key );
			$sections[ $key ] = array(
				'label'   => $label,
				'data'    => $section ? $section->to_array() : null,
				'content' => $section ? $section->get_content() : array(),
				'status'  => $section ? $section->status : 'empty',
				'score'   => $section ? $section->get_completion_score() : 0,
			);
		}

		return rest_ensure_response( array(
			'sections' => $sections,
			'personas' => array_map( function( $p ) { return $p->to_array(); }, $foundation->get_personas() ),
			'score'    => $foundation->identity_score,
		) );
	}

	/**
	 * GET /foundations/{id}/identity/{section}
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_identity_section( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		$section_key = $request->get_param( 'section' );

		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		if ( ! isset( BZMI_Foundation::IDENTITY_SECTIONS[ $section_key ] ) ) {
			return new WP_Error( 'invalid_section', __( 'Section invalide.', 'blazing-feedback' ), array( 'status' => 400 ) );
		}

		$section = $foundation->get_identity_section( $section_key );

		return rest_ensure_response( array(
			'section' => $section_key,
			'label'   => BZMI_Foundation::IDENTITY_SECTIONS[ $section_key ],
			'data'    => $section ? $section->to_array() : null,
			'content' => $section ? $section->get_content() : BZMI_Foundation_Identity::CONTENT_STRUCTURE[ $section_key ] ?? array(),
		) );
	}

	/**
	 * PUT /foundations/{id}/identity/{section}
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function update_identity_section( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		$section_key = $request->get_param( 'section' );

		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		if ( ! isset( BZMI_Foundation::IDENTITY_SECTIONS[ $section_key ] ) ) {
			return new WP_Error( 'invalid_section', __( 'Section invalide.', 'blazing-feedback' ), array( 'status' => 400 ) );
		}

		$params = $request->get_json_params();
		$content = isset( $params['content'] ) ? $params['content'] : array();
		$status = isset( $params['status'] ) ? sanitize_text_field( $params['status'] ) : 'hypothesis';

		$section = $foundation->set_identity_section( $section_key, $content, $status );

		if ( $section ) {
			return rest_ensure_response( array(
				'section' => $section->to_array(),
				'score'   => $foundation->identity_score,
			) );
		}

		return new WP_Error( 'update_failed', __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	// ========================
	// PERSONAS
	// ========================

	/**
	 * GET /foundations/{id}/personas
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_personas( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );

		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		$personas = $foundation->get_personas();
		$data = array_map( function( $p ) {
			$arr = $p->to_array();
			$arr['avatar_url'] = $p->get_avatar_url();
			$arr['completion_score'] = $p->get_completion_score();
			return $arr;
		}, $personas );

		return rest_ensure_response( $data );
	}

	/**
	 * POST /foundations/{id}/personas
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function create_persona( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );

		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		$params = $request->get_json_params();
		$params['foundation_id'] = $foundation->id;

		$persona = BZMI_Foundation_Persona::create( $params );

		if ( $persona ) {
			$foundation->recalculate_scores();
			return rest_ensure_response( $persona->to_array() );
		}

		return new WP_Error( 'creation_failed', __( 'Erreur lors de la création.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	/**
	 * GET /personas/{id}
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_persona( $request ) {
		$persona = BZMI_Foundation_Persona::find( $request->get_param( 'id' ) );

		if ( ! $persona ) {
			return new WP_Error( 'not_found', __( 'Persona introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		$data = $persona->to_array();
		$data['avatar_url'] = $persona->get_avatar_url();
		$data['completion_score'] = $persona->get_completion_score();

		return rest_ensure_response( $data );
	}

	/**
	 * PUT /personas/{id}
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function update_persona( $request ) {
		$persona = BZMI_Foundation_Persona::find( $request->get_param( 'id' ) );

		if ( ! $persona ) {
			return new WP_Error( 'not_found', __( 'Persona introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		$params = $request->get_json_params();
		$persona->fill( $params );

		if ( $persona->save() ) {
			$foundation = $persona->get_foundation();
			if ( $foundation ) {
				$foundation->recalculate_scores();
			}
			return rest_ensure_response( $persona->to_array() );
		}

		return new WP_Error( 'update_failed', __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	/**
	 * DELETE /personas/{id}
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function delete_persona( $request ) {
		$persona = BZMI_Foundation_Persona::find( $request->get_param( 'id' ) );

		if ( ! $persona ) {
			return new WP_Error( 'not_found', __( 'Persona introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		$foundation = $persona->get_foundation();

		if ( $persona->delete() ) {
			if ( $foundation ) {
				$foundation->recalculate_scores();
			}
			return rest_ensure_response( array( 'deleted' => true ) );
		}

		return new WP_Error( 'delete_failed', __( 'Erreur lors de la suppression.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	// ========================
	// OFFRES (raccourcis, même pattern)
	// ========================

	public static function get_offers( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		return rest_ensure_response( array_map( function( $o ) { return $o->to_array(); }, $foundation->get_offers() ) );
	}

	public static function create_offer( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		$params = $request->get_json_params();
		$params['foundation_id'] = $foundation->id;
		$offer = BZMI_Foundation_Offer::create( $params );
		if ( $offer ) {
			$foundation->recalculate_scores();
			return rest_ensure_response( $offer->to_array() );
		}
		return new WP_Error( 'creation_failed', __( 'Erreur lors de la création.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	public static function update_offer( $request ) {
		$offer = BZMI_Foundation_Offer::find( $request->get_param( 'id' ) );
		if ( ! $offer ) {
			return new WP_Error( 'not_found', __( 'Offre introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		$offer->fill( $request->get_json_params() );
		if ( $offer->save() ) {
			$foundation = $offer->get_foundation();
			if ( $foundation ) $foundation->recalculate_scores();
			return rest_ensure_response( $offer->to_array() );
		}
		return new WP_Error( 'update_failed', __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	public static function delete_offer( $request ) {
		$offer = BZMI_Foundation_Offer::find( $request->get_param( 'id' ) );
		if ( ! $offer ) {
			return new WP_Error( 'not_found', __( 'Offre introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		$foundation = $offer->get_foundation();
		if ( $offer->delete() ) {
			if ( $foundation ) $foundation->recalculate_scores();
			return rest_ensure_response( array( 'deleted' => true ) );
		}
		return new WP_Error( 'delete_failed', __( 'Erreur lors de la suppression.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	// ========================
	// CONCURRENTS
	// ========================

	public static function get_competitors( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		return rest_ensure_response( array_map( function( $c ) { return $c->to_array(); }, $foundation->get_competitors() ) );
	}

	public static function create_competitor( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		$params = $request->get_json_params();
		$params['foundation_id'] = $foundation->id;
		$competitor = BZMI_Foundation_Competitor::create( $params );
		if ( $competitor ) {
			$foundation->recalculate_scores();
			return rest_ensure_response( $competitor->to_array() );
		}
		return new WP_Error( 'creation_failed', __( 'Erreur lors de la création.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	public static function update_competitor( $request ) {
		$competitor = BZMI_Foundation_Competitor::find( $request->get_param( 'id' ) );
		if ( ! $competitor ) {
			return new WP_Error( 'not_found', __( 'Concurrent introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		$competitor->fill( $request->get_json_params() );
		if ( $competitor->save() ) {
			$foundation = $competitor->get_foundation();
			if ( $foundation ) $foundation->recalculate_scores();
			return rest_ensure_response( $competitor->to_array() );
		}
		return new WP_Error( 'update_failed', __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	public static function delete_competitor( $request ) {
		$competitor = BZMI_Foundation_Competitor::find( $request->get_param( 'id' ) );
		if ( ! $competitor ) {
			return new WP_Error( 'not_found', __( 'Concurrent introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		$foundation = $competitor->get_foundation();
		if ( $competitor->delete() ) {
			if ( $foundation ) $foundation->recalculate_scores();
			return rest_ensure_response( array( 'deleted' => true ) );
		}
		return new WP_Error( 'delete_failed', __( 'Erreur lors de la suppression.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	// ========================
	// PARCOURS & CANAUX (même pattern)
	// ========================

	public static function get_journeys( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		return rest_ensure_response( array_map( function( $j ) { return $j->to_array(); }, $foundation->get_journeys() ) );
	}

	public static function create_journey( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		$params = $request->get_json_params();
		$params['foundation_id'] = $foundation->id;
		$journey = BZMI_Foundation_Journey::create( $params );
		if ( $journey ) {
			$foundation->recalculate_scores();
			return rest_ensure_response( $journey->to_array() );
		}
		return new WP_Error( 'creation_failed', __( 'Erreur lors de la création.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	public static function update_journey( $request ) {
		$journey = BZMI_Foundation_Journey::find( $request->get_param( 'id' ) );
		if ( ! $journey ) {
			return new WP_Error( 'not_found', __( 'Parcours introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		$journey->fill( $request->get_json_params() );
		if ( $journey->save() ) {
			$foundation = $journey->get_foundation();
			if ( $foundation ) $foundation->recalculate_scores();
			return rest_ensure_response( $journey->to_array() );
		}
		return new WP_Error( 'update_failed', __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	public static function delete_journey( $request ) {
		$journey = BZMI_Foundation_Journey::find( $request->get_param( 'id' ) );
		if ( ! $journey ) {
			return new WP_Error( 'not_found', __( 'Parcours introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		$foundation = $journey->get_foundation();
		if ( $journey->delete() ) {
			if ( $foundation ) $foundation->recalculate_scores();
			return rest_ensure_response( array( 'deleted' => true ) );
		}
		return new WP_Error( 'delete_failed', __( 'Erreur lors de la suppression.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	public static function get_channels( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		return rest_ensure_response( array_map( function( $c ) { return $c->to_array(); }, $foundation->get_channels() ) );
	}

	public static function create_channel( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		$params = $request->get_json_params();
		$params['foundation_id'] = $foundation->id;
		$channel = BZMI_Foundation_Channel::create( $params );
		if ( $channel ) {
			$foundation->recalculate_scores();
			return rest_ensure_response( $channel->to_array() );
		}
		return new WP_Error( 'creation_failed', __( 'Erreur lors de la création.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	public static function update_channel( $request ) {
		$channel = BZMI_Foundation_Channel::find( $request->get_param( 'id' ) );
		if ( ! $channel ) {
			return new WP_Error( 'not_found', __( 'Canal introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		$channel->fill( $request->get_json_params() );
		if ( $channel->save() ) {
			$foundation = $channel->get_foundation();
			if ( $foundation ) $foundation->recalculate_scores();
			return rest_ensure_response( $channel->to_array() );
		}
		return new WP_Error( 'update_failed', __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	public static function delete_channel( $request ) {
		$channel = BZMI_Foundation_Channel::find( $request->get_param( 'id' ) );
		if ( ! $channel ) {
			return new WP_Error( 'not_found', __( 'Canal introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}
		$foundation = $channel->get_foundation();
		if ( $channel->delete() ) {
			if ( $foundation ) $foundation->recalculate_scores();
			return rest_ensure_response( array( 'deleted' => true ) );
		}
		return new WP_Error( 'delete_failed', __( 'Erreur lors de la suppression.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	// ========================
	// EXÉCUTION
	// ========================

	public static function get_execution( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		$sections = array();
		foreach ( BZMI_Foundation::EXECUTION_SECTIONS as $key => $label ) {
			$section = $foundation->get_execution_section( $key );
			$sections[ $key ] = array(
				'label'   => $label,
				'data'    => $section ? $section->to_array() : null,
				'content' => $section ? $section->get_content() : array(),
				'status'  => $section ? $section->status : 'empty',
				'score'   => $section ? $section->get_completion_score() : 0,
			);
		}

		return rest_ensure_response( array(
			'sections' => $sections,
			'score'    => $foundation->execution_score,
		) );
	}

	public static function get_execution_section( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		$section_key = $request->get_param( 'section' );

		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		if ( ! isset( BZMI_Foundation::EXECUTION_SECTIONS[ $section_key ] ) ) {
			return new WP_Error( 'invalid_section', __( 'Section invalide.', 'blazing-feedback' ), array( 'status' => 400 ) );
		}

		$section = $foundation->get_execution_section( $section_key );

		return rest_ensure_response( array(
			'section' => $section_key,
			'label'   => BZMI_Foundation::EXECUTION_SECTIONS[ $section_key ],
			'data'    => $section ? $section->to_array() : null,
			'content' => $section ? $section->get_content() : BZMI_Foundation_Execution::CONTENT_STRUCTURE[ $section_key ] ?? array(),
		) );
	}

	public static function update_execution_section( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		$section_key = $request->get_param( 'section' );

		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		if ( ! isset( BZMI_Foundation::EXECUTION_SECTIONS[ $section_key ] ) ) {
			return new WP_Error( 'invalid_section', __( 'Section invalide.', 'blazing-feedback' ), array( 'status' => 400 ) );
		}

		$params = $request->get_json_params();
		$content = isset( $params['content'] ) ? $params['content'] : array();
		$status = isset( $params['status'] ) ? sanitize_text_field( $params['status'] ) : 'draft';

		$section = $foundation->set_execution_section( $section_key, $content, $status );

		if ( $section ) {
			return rest_ensure_response( array(
				'section' => $section->to_array(),
				'score'   => $foundation->execution_score,
			) );
		}

		return new WP_Error( 'update_failed', __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ), array( 'status' => 500 ) );
	}

	// ========================
	// IA
	// ========================

	public static function ai_enrich( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		$params = $request->get_json_params();
		$socle = isset( $params['socle'] ) ? sanitize_text_field( $params['socle'] ) : '';
		$target = isset( $params['target'] ) ? sanitize_text_field( $params['target'] ) : '';

		$ai = new BZMI_Foundations_AI();
		$result = $ai->enrich( $foundation, $socle, $target );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	public static function ai_suggest( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		$params = $request->get_json_params();
		$socle = isset( $params['socle'] ) ? sanitize_text_field( $params['socle'] ) : '';
		$field = isset( $params['field'] ) ? sanitize_text_field( $params['field'] ) : '';
		$context = isset( $params['context'] ) ? $params['context'] : array();

		$ai = new BZMI_Foundations_AI();
		$result = $ai->suggest( $foundation, $socle, $field, $context );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	public static function ai_audit( $request ) {
		$foundation = BZMI_Foundation::find( $request->get_param( 'id' ) );
		if ( ! $foundation ) {
			return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ), array( 'status' => 404 ) );
		}

		$ai = new BZMI_Foundations_AI();
		$result = $ai->audit( $foundation );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}
}
