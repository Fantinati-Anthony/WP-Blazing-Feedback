<?php
/**
 * Admin Foundations Experience - Gestion du socle Expérience & Canaux
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Admin_Foundations_Experience
 *
 * Gère l'interface d'administration du socle Expérience & Canaux
 *
 * @since 2.0.0
 */
class BZMI_Admin_Foundations_Experience {

	/**
	 * Gérer les actions AJAX pour le socle Expérience
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function handle_ajax() {
		check_ajax_referer( 'bzmi_nonce', 'nonce' );

		if ( ! current_user_can( 'bzmi_edit_foundations' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission refusée.', 'blazing-feedback' ) ) );
		}

		$action = isset( $_POST['experience_action'] ) ? sanitize_text_field( wp_unslash( $_POST['experience_action'] ) ) : '';

		switch ( $action ) {
			// Parcours
			case 'create_journey':
				self::ajax_create_journey();
				break;

			case 'update_journey':
				self::ajax_update_journey();
				break;

			case 'delete_journey':
				self::ajax_delete_journey();
				break;

			case 'apply_template':
				self::ajax_apply_template();
				break;

			// Canaux
			case 'create_channel':
				self::ajax_create_channel();
				break;

			case 'update_channel':
				self::ajax_update_channel();
				break;

			case 'delete_channel':
				self::ajax_delete_channel();
				break;

			default:
				wp_send_json_error( array( 'message' => __( 'Action inconnue.', 'blazing-feedback' ) ) );
		}
	}

	/**
	 * AJAX: Créer un parcours
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_create_journey() {
		$foundation_id = isset( $_POST['foundation_id'] ) ? absint( $_POST['foundation_id'] ) : 0;
		$data          = isset( $_POST['journey'] ) ? wp_unslash( $_POST['journey'] ) : array();

		$foundation = BZMI_Foundation::find( $foundation_id );
		if ( ! $foundation ) {
			wp_send_json_error( array( 'message' => __( 'Fondation introuvable.', 'blazing-feedback' ) ) );
		}

		$journey_data = self::sanitize_journey_data( $data );
		$journey_data['foundation_id'] = $foundation_id;

		$journey = BZMI_Foundation_Journey::create( $journey_data );

		if ( $journey ) {
			$foundation->recalculate_scores();

			wp_send_json_success( array(
				'message'    => __( 'Parcours créé.', 'blazing-feedback' ),
				'journey_id' => $journey->id,
				'journey'    => $journey->to_array(),
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la création.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Mettre à jour un parcours
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_update_journey() {
		$journey_id = isset( $_POST['journey_id'] ) ? absint( $_POST['journey_id'] ) : 0;
		$data       = isset( $_POST['journey'] ) ? wp_unslash( $_POST['journey'] ) : array();

		$journey = BZMI_Foundation_Journey::find( $journey_id );
		if ( ! $journey ) {
			wp_send_json_error( array( 'message' => __( 'Parcours introuvable.', 'blazing-feedback' ) ) );
		}

		$journey_data = self::sanitize_journey_data( $data );
		$journey->fill( $journey_data );

		if ( $journey->save() ) {
			$foundation = $journey->get_foundation();
			if ( $foundation ) {
				$foundation->recalculate_scores();
			}

			wp_send_json_success( array(
				'message' => __( 'Parcours mis à jour.', 'blazing-feedback' ),
				'journey' => $journey->to_array(),
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Supprimer un parcours
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_delete_journey() {
		$journey_id = isset( $_POST['journey_id'] ) ? absint( $_POST['journey_id'] ) : 0;

		$journey = BZMI_Foundation_Journey::find( $journey_id );
		if ( ! $journey ) {
			wp_send_json_error( array( 'message' => __( 'Parcours introuvable.', 'blazing-feedback' ) ) );
		}

		$foundation = $journey->get_foundation();

		if ( $journey->delete() ) {
			if ( $foundation ) {
				$foundation->recalculate_scores();
			}

			wp_send_json_success( array( 'message' => __( 'Parcours supprimé.', 'blazing-feedback' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la suppression.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Appliquer un template de parcours
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_apply_template() {
		$journey_id = isset( $_POST['journey_id'] ) ? absint( $_POST['journey_id'] ) : 0;
		$template   = isset( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : 'purchase';

		$journey = BZMI_Foundation_Journey::find( $journey_id );
		if ( ! $journey ) {
			wp_send_json_error( array( 'message' => __( 'Parcours introuvable.', 'blazing-feedback' ) ) );
		}

		$template_data = BZMI_Foundation_Journey::get_template( $template );

		if ( ! empty( $template_data['stages'] ) ) {
			$journey->set_stages( $template_data['stages'] );
			$journey->save();

			wp_send_json_success( array(
				'message' => __( 'Template appliqué.', 'blazing-feedback' ),
				'stages'  => $template_data['stages'],
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Template introuvable.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Créer un canal
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_create_channel() {
		$foundation_id = isset( $_POST['foundation_id'] ) ? absint( $_POST['foundation_id'] ) : 0;
		$data          = isset( $_POST['channel'] ) ? wp_unslash( $_POST['channel'] ) : array();

		$foundation = BZMI_Foundation::find( $foundation_id );
		if ( ! $foundation ) {
			wp_send_json_error( array( 'message' => __( 'Fondation introuvable.', 'blazing-feedback' ) ) );
		}

		$channel_data = self::sanitize_channel_data( $data );
		$channel_data['foundation_id'] = $foundation_id;

		$channel = BZMI_Foundation_Channel::create( $channel_data );

		if ( $channel ) {
			$foundation->recalculate_scores();

			wp_send_json_success( array(
				'message'    => __( 'Canal créé.', 'blazing-feedback' ),
				'channel_id' => $channel->id,
				'channel'    => $channel->to_array(),
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la création.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Mettre à jour un canal
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_update_channel() {
		$channel_id = isset( $_POST['channel_id'] ) ? absint( $_POST['channel_id'] ) : 0;
		$data       = isset( $_POST['channel'] ) ? wp_unslash( $_POST['channel'] ) : array();

		$channel = BZMI_Foundation_Channel::find( $channel_id );
		if ( ! $channel ) {
			wp_send_json_error( array( 'message' => __( 'Canal introuvable.', 'blazing-feedback' ) ) );
		}

		$channel_data = self::sanitize_channel_data( $data );
		$channel->fill( $channel_data );

		if ( $channel->save() ) {
			$foundation = $channel->get_foundation();
			if ( $foundation ) {
				$foundation->recalculate_scores();
			}

			wp_send_json_success( array(
				'message' => __( 'Canal mis à jour.', 'blazing-feedback' ),
				'channel' => $channel->to_array(),
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Supprimer un canal
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_delete_channel() {
		$channel_id = isset( $_POST['channel_id'] ) ? absint( $_POST['channel_id'] ) : 0;

		$channel = BZMI_Foundation_Channel::find( $channel_id );
		if ( ! $channel ) {
			wp_send_json_error( array( 'message' => __( 'Canal introuvable.', 'blazing-feedback' ) ) );
		}

		$foundation = $channel->get_foundation();

		if ( $channel->delete() ) {
			if ( $foundation ) {
				$foundation->recalculate_scores();
			}

			wp_send_json_success( array( 'message' => __( 'Canal supprimé.', 'blazing-feedback' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la suppression.', 'blazing-feedback' ) ) );
	}

	/**
	 * Sanitize les données d'un parcours
	 *
	 * @since 2.0.0
	 * @param array $data Données brutes.
	 * @return array
	 */
	private static function sanitize_journey_data( $data ) {
		if ( is_string( $data ) ) {
			$data = json_decode( $data, true ) ?: array();
		}

		$sanitized = array();

		// Champs texte
		$text_fields = array( 'name', 'objective', 'status' );
		foreach ( $text_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $data[ $field ] );
			}
		}

		// Description (HTML autorisé)
		if ( isset( $data['description'] ) ) {
			$sanitized['description'] = wp_kses_post( $data['description'] );
		}

		// Persona ID
		if ( isset( $data['persona_id'] ) ) {
			$sanitized['persona_id'] = absint( $data['persona_id'] );
		}

		// Champs JSON complexes
		$complex_fields = array( 'stages', 'touchpoints', 'emotions', 'pain_points', 'opportunities' );
		foreach ( $complex_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				if ( is_string( $data[ $field ] ) ) {
					$data[ $field ] = json_decode( $data[ $field ], true ) ?: array();
				}
				if ( is_array( $data[ $field ] ) ) {
					$sanitized[ $field ] = wp_json_encode( self::sanitize_array_recursive( $data[ $field ] ) );
				}
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize les données d'un canal
	 *
	 * @since 2.0.0
	 * @param array $data Données brutes.
	 * @return array
	 */
	private static function sanitize_channel_data( $data ) {
		if ( is_string( $data ) ) {
			$data = json_decode( $data, true ) ?: array();
		}

		$sanitized = array();

		// Champs texte
		$text_fields = array( 'name', 'type', 'platform', 'cta_primary', 'cta_secondary', 'status' );
		foreach ( $text_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $data[ $field ] );
			}
		}

		// URL
		if ( isset( $data['url'] ) ) {
			$sanitized['url'] = esc_url_raw( $data['url'] );
		}

		// Champs texte long (HTML autorisé)
		$html_fields = array( 'description', 'tone_guidelines' );
		foreach ( $html_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = wp_kses_post( $data[ $field ] );
			}
		}

		// Champs JSON (arrays)
		$array_fields = array( 'objectives', 'target_personas', 'key_messages', 'kpis' );
		foreach ( $array_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				if ( is_string( $data[ $field ] ) ) {
					$data[ $field ] = json_decode( $data[ $field ], true ) ?: array();
				}
				if ( is_array( $data[ $field ] ) ) {
					$sanitized[ $field ] = wp_json_encode( self::sanitize_array_recursive( $data[ $field ] ) );
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
	 * Sanitize un tableau récursivement
	 *
	 * @since 2.0.0
	 * @param array $array Tableau à sanitizer.
	 * @return array
	 */
	private static function sanitize_array_recursive( $array ) {
		$sanitized = array();

		foreach ( $array as $key => $value ) {
			$clean_key = sanitize_text_field( $key );

			if ( is_array( $value ) ) {
				$sanitized[ $clean_key ] = self::sanitize_array_recursive( $value );
			} elseif ( is_string( $value ) ) {
				$sanitized[ $clean_key ] = wp_kses_post( $value );
			} elseif ( is_numeric( $value ) ) {
				$sanitized[ $clean_key ] = $value;
			} elseif ( is_bool( $value ) ) {
				$sanitized[ $clean_key ] = $value;
			}
		}

		return $sanitized;
	}

	/**
	 * Obtenir les champs du formulaire de parcours
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_journey_fields() {
		return array(
			array(
				'name'        => 'name',
				'label'       => __( 'Nom du parcours', 'blazing-feedback' ),
				'type'        => 'text',
				'required'    => true,
				'placeholder' => __( 'Ex: Parcours d\'achat B2B', 'blazing-feedback' ),
			),
			array(
				'name'    => 'persona_id',
				'label'   => __( 'Persona associé', 'blazing-feedback' ),
				'type'    => 'persona_select',
				'help'    => __( 'Optionnel: associer ce parcours à un persona spécifique.', 'blazing-feedback' ),
			),
			array(
				'name'        => 'objective',
				'label'       => __( 'Objectif du parcours', 'blazing-feedback' ),
				'type'        => 'text',
				'placeholder' => __( 'Ex: Conversion prospect en client', 'blazing-feedback' ),
			),
			array(
				'name'        => 'description',
				'label'       => __( 'Description', 'blazing-feedback' ),
				'type'        => 'textarea',
				'placeholder' => __( 'Décrivez ce parcours...', 'blazing-feedback' ),
			),
			array(
				'name'     => 'stages',
				'label'    => __( 'Étapes', 'blazing-feedback' ),
				'type'     => 'journey_stages',
				'help'     => __( 'Définissez les étapes du parcours.', 'blazing-feedback' ),
				'template' => true,
			),
			array(
				'name'  => 'touchpoints',
				'label' => __( 'Points de contact', 'blazing-feedback' ),
				'type'  => 'journey_touchpoints',
				'help'  => __( 'Associez des canaux à chaque étape.', 'blazing-feedback' ),
			),
			array(
				'name'  => 'emotions',
				'label' => __( 'Émotions', 'blazing-feedback' ),
				'type'  => 'journey_emotions',
				'help'  => __( 'Évaluez l\'état émotionnel à chaque étape.', 'blazing-feedback' ),
			),
			array(
				'name'        => 'pain_points',
				'label'       => __( 'Points de friction', 'blazing-feedback' ),
				'type'        => 'tags',
				'placeholder' => __( 'Identifiez les points de friction...', 'blazing-feedback' ),
				'ai_enabled'  => true,
			),
			array(
				'name'        => 'opportunities',
				'label'       => __( 'Opportunités', 'blazing-feedback' ),
				'type'        => 'tags',
				'placeholder' => __( 'Identifiez les opportunités d\'amélioration...', 'blazing-feedback' ),
				'ai_enabled'  => true,
			),
			array(
				'name'    => 'status',
				'label'   => __( 'Statut', 'blazing-feedback' ),
				'type'    => 'select',
				'options' => BZMI_Foundation_Journey::STATUSES,
			),
		);
	}

	/**
	 * Obtenir les champs du formulaire de canal
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_channel_fields() {
		return array(
			array(
				'name'        => 'name',
				'label'       => __( 'Nom du canal', 'blazing-feedback' ),
				'type'        => 'text',
				'required'    => true,
				'placeholder' => __( 'Ex: Page LinkedIn', 'blazing-feedback' ),
			),
			array(
				'name'    => 'type',
				'label'   => __( 'Type', 'blazing-feedback' ),
				'type'    => 'select',
				'options' => array_combine(
					array_keys( BZMI_Foundation_Channel::TYPES ),
					array_column( BZMI_Foundation_Channel::TYPES, 'label' )
				),
			),
			array(
				'name'        => 'platform',
				'label'       => __( 'Plateforme', 'blazing-feedback' ),
				'type'        => 'text',
				'placeholder' => __( 'Ex: LinkedIn, Instagram...', 'blazing-feedback' ),
				'conditional' => 'type',
			),
			array(
				'name'        => 'url',
				'label'       => __( 'URL', 'blazing-feedback' ),
				'type'        => 'url',
				'placeholder' => __( 'https://...', 'blazing-feedback' ),
			),
			array(
				'name'        => 'description',
				'label'       => __( 'Description', 'blazing-feedback' ),
				'type'        => 'textarea',
				'placeholder' => __( 'Décrivez l\'utilisation de ce canal...', 'blazing-feedback' ),
			),
			array(
				'name'        => 'objectives',
				'label'       => __( 'Objectifs', 'blazing-feedback' ),
				'type'        => 'tags',
				'placeholder' => __( 'Objectifs du canal...', 'blazing-feedback' ),
			),
			array(
				'name'     => 'target_personas',
				'label'    => __( 'Personas cibles', 'blazing-feedback' ),
				'type'     => 'persona_select',
				'multiple' => true,
			),
			array(
				'name'        => 'key_messages',
				'label'       => __( 'Messages clés', 'blazing-feedback' ),
				'type'        => 'tags',
				'placeholder' => __( 'Messages principaux pour ce canal...', 'blazing-feedback' ),
				'ai_enabled'  => true,
			),
			array(
				'name'        => 'tone_guidelines',
				'label'       => __( 'Guidelines de ton', 'blazing-feedback' ),
				'type'        => 'textarea',
				'placeholder' => __( 'Comment adapter le ton sur ce canal...', 'blazing-feedback' ),
			),
			array(
				'name'        => 'cta_primary',
				'label'       => __( 'CTA principal', 'blazing-feedback' ),
				'type'        => 'text',
				'placeholder' => __( 'Ex: Demander une démo', 'blazing-feedback' ),
			),
			array(
				'name'        => 'cta_secondary',
				'label'       => __( 'CTA secondaire', 'blazing-feedback' ),
				'type'        => 'text',
				'placeholder' => __( 'Ex: En savoir plus', 'blazing-feedback' ),
			),
			array(
				'name'  => 'kpis',
				'label' => __( 'KPIs', 'blazing-feedback' ),
				'type'  => 'repeater',
				'fields' => array(
					array( 'name' => 'name', 'label' => __( 'Nom', 'blazing-feedback' ), 'type' => 'text' ),
					array( 'name' => 'target', 'label' => __( 'Objectif', 'blazing-feedback' ), 'type' => 'text' ),
				),
			),
			array(
				'name'    => 'status',
				'label'   => __( 'Statut', 'blazing-feedback' ),
				'type'    => 'select',
				'options' => BZMI_Foundation_Channel::STATUSES,
			),
		);
	}

	/**
	 * Obtenir les templates de parcours disponibles
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_journey_templates() {
		return array(
			'purchase'   => __( 'Parcours d\'achat', 'blazing-feedback' ),
			'onboarding' => __( 'Parcours d\'onboarding', 'blazing-feedback' ),
			'support'    => __( 'Parcours de support', 'blazing-feedback' ),
		);
	}
}

// Enregistrer les handlers AJAX
add_action( 'wp_ajax_bzmi_experience', array( 'BZMI_Admin_Foundations_Experience', 'handle_ajax' ) );
