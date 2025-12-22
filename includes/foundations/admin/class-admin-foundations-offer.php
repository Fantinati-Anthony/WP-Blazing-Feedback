<?php
/**
 * Admin Foundations Offer - Gestion du socle Offre & Marché
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Admin_Foundations_Offer
 *
 * Gère l'interface d'administration du socle Offre & Marché
 *
 * @since 2.0.0
 */
class BZMI_Admin_Foundations_Offer {

	/**
	 * Gérer les actions AJAX pour le socle Offre
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function handle_ajax() {
		check_ajax_referer( 'bzmi_nonce', 'nonce' );

		if ( ! current_user_can( 'bzmi_edit_foundations' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission refusée.', 'blazing-feedback' ) ) );
		}

		$action = isset( $_POST['offer_action'] ) ? sanitize_text_field( wp_unslash( $_POST['offer_action'] ) ) : '';

		switch ( $action ) {
			// Offres
			case 'create_offer':
				self::ajax_create_offer();
				break;

			case 'update_offer':
				self::ajax_update_offer();
				break;

			case 'delete_offer':
				self::ajax_delete_offer();
				break;

			// Concurrents
			case 'create_competitor':
				self::ajax_create_competitor();
				break;

			case 'update_competitor':
				self::ajax_update_competitor();
				break;

			case 'delete_competitor':
				self::ajax_delete_competitor();
				break;

			default:
				wp_send_json_error( array( 'message' => __( 'Action inconnue.', 'blazing-feedback' ) ) );
		}
	}

	/**
	 * AJAX: Créer une offre
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_create_offer() {
		$foundation_id = isset( $_POST['foundation_id'] ) ? absint( $_POST['foundation_id'] ) : 0;
		$data          = isset( $_POST['offer'] ) ? wp_unslash( $_POST['offer'] ) : array();

		$foundation = BZMI_Foundation::find( $foundation_id );
		if ( ! $foundation ) {
			wp_send_json_error( array( 'message' => __( 'Fondation introuvable.', 'blazing-feedback' ) ) );
		}

		$offer_data = self::sanitize_offer_data( $data );
		$offer_data['foundation_id'] = $foundation_id;

		$offer = BZMI_Foundation_Offer::create( $offer_data );

		if ( $offer ) {
			$foundation->recalculate_scores();

			wp_send_json_success( array(
				'message'  => __( 'Offre créée.', 'blazing-feedback' ),
				'offer_id' => $offer->id,
				'offer'    => $offer->to_array(),
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la création.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Mettre à jour une offre
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_update_offer() {
		$offer_id = isset( $_POST['offer_id'] ) ? absint( $_POST['offer_id'] ) : 0;
		$data     = isset( $_POST['offer'] ) ? wp_unslash( $_POST['offer'] ) : array();

		$offer = BZMI_Foundation_Offer::find( $offer_id );
		if ( ! $offer ) {
			wp_send_json_error( array( 'message' => __( 'Offre introuvable.', 'blazing-feedback' ) ) );
		}

		$offer_data = self::sanitize_offer_data( $data );
		$offer->fill( $offer_data );

		if ( $offer->save() ) {
			$foundation = $offer->get_foundation();
			if ( $foundation ) {
				$foundation->recalculate_scores();
			}

			wp_send_json_success( array(
				'message' => __( 'Offre mise à jour.', 'blazing-feedback' ),
				'offer'   => $offer->to_array(),
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Supprimer une offre
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_delete_offer() {
		$offer_id = isset( $_POST['offer_id'] ) ? absint( $_POST['offer_id'] ) : 0;

		$offer = BZMI_Foundation_Offer::find( $offer_id );
		if ( ! $offer ) {
			wp_send_json_error( array( 'message' => __( 'Offre introuvable.', 'blazing-feedback' ) ) );
		}

		$foundation = $offer->get_foundation();

		if ( $offer->delete() ) {
			if ( $foundation ) {
				$foundation->recalculate_scores();
			}

			wp_send_json_success( array( 'message' => __( 'Offre supprimée.', 'blazing-feedback' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la suppression.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Créer un concurrent
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_create_competitor() {
		$foundation_id = isset( $_POST['foundation_id'] ) ? absint( $_POST['foundation_id'] ) : 0;
		$data          = isset( $_POST['competitor'] ) ? wp_unslash( $_POST['competitor'] ) : array();

		$foundation = BZMI_Foundation::find( $foundation_id );
		if ( ! $foundation ) {
			wp_send_json_error( array( 'message' => __( 'Fondation introuvable.', 'blazing-feedback' ) ) );
		}

		$competitor_data = self::sanitize_competitor_data( $data );
		$competitor_data['foundation_id'] = $foundation_id;

		$competitor = BZMI_Foundation_Competitor::create( $competitor_data );

		if ( $competitor ) {
			$foundation->recalculate_scores();

			wp_send_json_success( array(
				'message'       => __( 'Concurrent créé.', 'blazing-feedback' ),
				'competitor_id' => $competitor->id,
				'competitor'    => $competitor->to_array(),
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la création.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Mettre à jour un concurrent
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_update_competitor() {
		$competitor_id = isset( $_POST['competitor_id'] ) ? absint( $_POST['competitor_id'] ) : 0;
		$data          = isset( $_POST['competitor'] ) ? wp_unslash( $_POST['competitor'] ) : array();

		$competitor = BZMI_Foundation_Competitor::find( $competitor_id );
		if ( ! $competitor ) {
			wp_send_json_error( array( 'message' => __( 'Concurrent introuvable.', 'blazing-feedback' ) ) );
		}

		$competitor_data = self::sanitize_competitor_data( $data );
		$competitor->fill( $competitor_data );

		if ( $competitor->save() ) {
			$foundation = $competitor->get_foundation();
			if ( $foundation ) {
				$foundation->recalculate_scores();
			}

			wp_send_json_success( array(
				'message'    => __( 'Concurrent mis à jour.', 'blazing-feedback' ),
				'competitor' => $competitor->to_array(),
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Supprimer un concurrent
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_delete_competitor() {
		$competitor_id = isset( $_POST['competitor_id'] ) ? absint( $_POST['competitor_id'] ) : 0;

		$competitor = BZMI_Foundation_Competitor::find( $competitor_id );
		if ( ! $competitor ) {
			wp_send_json_error( array( 'message' => __( 'Concurrent introuvable.', 'blazing-feedback' ) ) );
		}

		$foundation = $competitor->get_foundation();

		if ( $competitor->delete() ) {
			if ( $foundation ) {
				$foundation->recalculate_scores();
			}

			wp_send_json_success( array( 'message' => __( 'Concurrent supprimé.', 'blazing-feedback' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la suppression.', 'blazing-feedback' ) ) );
	}

	/**
	 * Sanitize les données d'une offre
	 *
	 * @since 2.0.0
	 * @param array $data Données brutes.
	 * @return array
	 */
	private static function sanitize_offer_data( $data ) {
		if ( is_string( $data ) ) {
			$data = json_decode( $data, true ) ?: array();
		}

		$sanitized = array();

		// Champs texte
		$text_fields = array( 'name', 'type', 'pricing_model', 'price_range', 'status' );
		foreach ( $text_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $data[ $field ] );
			}
		}

		// Champs texte long (HTML autorisé)
		$html_fields = array( 'description', 'value_proposition', 'differentiation' );
		foreach ( $html_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = wp_kses_post( $data[ $field ] );
			}
		}

		// Champs JSON (arrays)
		$array_fields = array( 'target_personas', 'features', 'benefits' );
		foreach ( $array_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				if ( is_string( $data[ $field ] ) ) {
					$data[ $field ] = json_decode( $data[ $field ], true ) ?: array();
				}
				if ( is_array( $data[ $field ] ) ) {
					$sanitized[ $field ] = wp_json_encode( array_map( 'sanitize_text_field', $data[ $field ] ) );
				}
			}
		}

		// Priorité
		if ( isset( $data['priority'] ) ) {
			$sanitized['priority'] = absint( $data['priority'] );
		}

		return $sanitized;
	}

	/**
	 * Sanitize les données d'un concurrent
	 *
	 * @since 2.0.0
	 * @param array $data Données brutes.
	 * @return array
	 */
	private static function sanitize_competitor_data( $data ) {
		if ( is_string( $data ) ) {
			$data = json_decode( $data, true ) ?: array();
		}

		$sanitized = array();

		// Champs texte
		$text_fields = array( 'name', 'type', 'market_position', 'pricing_level' );
		foreach ( $text_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $data[ $field ] );
			}
		}

		// URL
		if ( isset( $data['website'] ) ) {
			$sanitized['website'] = esc_url_raw( $data['website'] );
		}

		// Champs texte long (HTML autorisé)
		$html_fields = array( 'description', 'notes' );
		foreach ( $html_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = wp_kses_post( $data[ $field ] );
			}
		}

		// Champs JSON (arrays)
		$array_fields = array( 'strengths', 'weaknesses' );
		foreach ( $array_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				if ( is_string( $data[ $field ] ) ) {
					$data[ $field ] = json_decode( $data[ $field ], true ) ?: array();
				}
				if ( is_array( $data[ $field ] ) ) {
					$sanitized[ $field ] = wp_json_encode( array_map( 'sanitize_text_field', $data[ $field ] ) );
				}
			}
		}

		// Niveau de menace (1-10)
		if ( isset( $data['threat_level'] ) ) {
			$sanitized['threat_level'] = max( 1, min( 10, absint( $data['threat_level'] ) ) );
		}

		return $sanitized;
	}

	/**
	 * Obtenir les champs du formulaire d'offre
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_offer_fields() {
		return array(
			array(
				'name'        => 'name',
				'label'       => __( 'Nom de l\'offre', 'blazing-feedback' ),
				'type'        => 'text',
				'required'    => true,
				'placeholder' => __( 'Ex: Forfait Premium', 'blazing-feedback' ),
			),
			array(
				'name'    => 'type',
				'label'   => __( 'Type', 'blazing-feedback' ),
				'type'    => 'select',
				'options' => BZMI_Foundation_Offer::TYPES,
			),
			array(
				'name'        => 'description',
				'label'       => __( 'Description', 'blazing-feedback' ),
				'type'        => 'textarea',
				'placeholder' => __( 'Décrivez votre offre...', 'blazing-feedback' ),
			),
			array(
				'name'        => 'value_proposition',
				'label'       => __( 'Proposition de valeur', 'blazing-feedback' ),
				'type'        => 'textarea',
				'placeholder' => __( 'Pourquoi le client devrait choisir cette offre ?', 'blazing-feedback' ),
				'ai_enabled'  => true,
			),
			array(
				'name'        => 'target_personas',
				'label'       => __( 'Personas cibles', 'blazing-feedback' ),
				'type'        => 'persona_select',
				'multiple'    => true,
				'help'        => __( 'Sélectionnez les personas visés par cette offre.', 'blazing-feedback' ),
			),
			array(
				'name'        => 'features',
				'label'       => __( 'Fonctionnalités', 'blazing-feedback' ),
				'type'        => 'tags',
				'placeholder' => __( 'Ajoutez les fonctionnalités...', 'blazing-feedback' ),
			),
			array(
				'name'        => 'benefits',
				'label'       => __( 'Bénéfices', 'blazing-feedback' ),
				'type'        => 'tags',
				'placeholder' => __( 'Ajoutez les bénéfices client...', 'blazing-feedback' ),
				'ai_enabled'  => true,
			),
			array(
				'name'    => 'pricing_model',
				'label'   => __( 'Modèle de tarification', 'blazing-feedback' ),
				'type'    => 'select',
				'options' => BZMI_Foundation_Offer::PRICING_MODELS,
			),
			array(
				'name'        => 'price_range',
				'label'       => __( 'Gamme de prix', 'blazing-feedback' ),
				'type'        => 'text',
				'placeholder' => __( 'Ex: 99€ - 299€/mois', 'blazing-feedback' ),
			),
			array(
				'name'        => 'differentiation',
				'label'       => __( 'Différenciation', 'blazing-feedback' ),
				'type'        => 'textarea',
				'placeholder' => __( 'Qu\'est-ce qui rend cette offre unique ?', 'blazing-feedback' ),
				'ai_enabled'  => true,
			),
			array(
				'name'    => 'status',
				'label'   => __( 'Statut', 'blazing-feedback' ),
				'type'    => 'select',
				'options' => BZMI_Foundation_Offer::STATUSES,
			),
		);
	}

	/**
	 * Obtenir les champs du formulaire de concurrent
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_competitor_fields() {
		return array(
			array(
				'name'        => 'name',
				'label'       => __( 'Nom du concurrent', 'blazing-feedback' ),
				'type'        => 'text',
				'required'    => true,
				'placeholder' => __( 'Ex: Concurrent SA', 'blazing-feedback' ),
			),
			array(
				'name'        => 'website',
				'label'       => __( 'Site web', 'blazing-feedback' ),
				'type'        => 'url',
				'placeholder' => __( 'https://www.concurrent.com', 'blazing-feedback' ),
			),
			array(
				'name'    => 'type',
				'label'   => __( 'Type de concurrent', 'blazing-feedback' ),
				'type'    => 'select',
				'options' => BZMI_Foundation_Competitor::TYPES,
			),
			array(
				'name'        => 'description',
				'label'       => __( 'Description', 'blazing-feedback' ),
				'type'        => 'textarea',
				'placeholder' => __( 'Décrivez ce concurrent...', 'blazing-feedback' ),
			),
			array(
				'name'        => 'strengths',
				'label'       => __( 'Points forts', 'blazing-feedback' ),
				'type'        => 'tags',
				'placeholder' => __( 'Ajoutez les forces...', 'blazing-feedback' ),
				'ai_enabled'  => true,
			),
			array(
				'name'        => 'weaknesses',
				'label'       => __( 'Points faibles', 'blazing-feedback' ),
				'type'        => 'tags',
				'placeholder' => __( 'Ajoutez les faiblesses...', 'blazing-feedback' ),
				'ai_enabled'  => true,
			),
			array(
				'name'    => 'market_position',
				'label'   => __( 'Position sur le marché', 'blazing-feedback' ),
				'type'    => 'select',
				'options' => BZMI_Foundation_Competitor::MARKET_POSITIONS,
			),
			array(
				'name'    => 'pricing_level',
				'label'   => __( 'Niveau de prix', 'blazing-feedback' ),
				'type'    => 'select',
				'options' => BZMI_Foundation_Competitor::PRICING_LEVELS,
			),
			array(
				'name'  => 'threat_level',
				'label' => __( 'Niveau de menace', 'blazing-feedback' ),
				'type'  => 'range',
				'min'   => 1,
				'max'   => 10,
				'help'  => __( '1 = Faible, 10 = Critique', 'blazing-feedback' ),
			),
			array(
				'name'        => 'notes',
				'label'       => __( 'Notes', 'blazing-feedback' ),
				'type'        => 'textarea',
				'placeholder' => __( 'Notes additionnelles...', 'blazing-feedback' ),
			),
		);
	}
}

// Enregistrer les handlers AJAX
add_action( 'wp_ajax_bzmi_offer', array( 'BZMI_Admin_Foundations_Offer', 'handle_ajax' ) );
