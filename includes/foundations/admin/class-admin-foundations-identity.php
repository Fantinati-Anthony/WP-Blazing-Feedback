<?php
/**
 * Admin Foundations Identity - Gestion du socle Identité
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Admin_Foundations_Identity
 *
 * Gère l'interface d'administration du socle Identité
 *
 * @since 2.0.0
 */
class BZMI_Admin_Foundations_Identity {

	/**
	 * Gérer les actions AJAX pour le socle Identité
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function handle_ajax() {
		check_ajax_referer( 'bzmi_nonce', 'nonce' );

		if ( ! current_user_can( 'bzmi_edit_foundations' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission refusée.', 'blazing-feedback' ) ) );
		}

		$action = isset( $_POST['identity_action'] ) ? sanitize_text_field( wp_unslash( $_POST['identity_action'] ) ) : '';

		switch ( $action ) {
			case 'save_section':
				self::ajax_save_section();
				break;

			case 'validate_section':
				self::ajax_validate_section();
				break;

			case 'create_persona':
				self::ajax_create_persona();
				break;

			case 'update_persona':
				self::ajax_update_persona();
				break;

			case 'delete_persona':
				self::ajax_delete_persona();
				break;

			default:
				wp_send_json_error( array( 'message' => __( 'Action inconnue.', 'blazing-feedback' ) ) );
		}
	}

	/**
	 * AJAX: Sauvegarder une section d'identité
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_save_section() {
		$foundation_id = isset( $_POST['foundation_id'] ) ? absint( $_POST['foundation_id'] ) : 0;
		$section       = isset( $_POST['section'] ) ? sanitize_text_field( wp_unslash( $_POST['section'] ) ) : '';
		$content       = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : array();
		$status        = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'hypothesis';

		$foundation = BZMI_Foundation::find( $foundation_id );
		if ( ! $foundation ) {
			wp_send_json_error( array( 'message' => __( 'Fondation introuvable.', 'blazing-feedback' ) ) );
		}

		// Valider la section
		if ( ! isset( BZMI_Foundation::IDENTITY_SECTIONS[ $section ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Section invalide.', 'blazing-feedback' ) ) );
		}

		// Sanitize le contenu
		$sanitized_content = self::sanitize_section_content( $section, $content );

		// Sauvegarder
		$identity = $foundation->set_identity_section( $section, $sanitized_content, $status );

		if ( $identity ) {
			wp_send_json_success( array(
				'message'          => __( 'Section enregistrée.', 'blazing-feedback' ),
				'identity_id'      => $identity->id,
				'completion_score' => $identity->get_completion_score(),
				'foundation_score' => $foundation->identity_score,
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de l\'enregistrement.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Valider une section d'identité
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_validate_section() {
		$foundation_id = isset( $_POST['foundation_id'] ) ? absint( $_POST['foundation_id'] ) : 0;
		$section       = isset( $_POST['section'] ) ? sanitize_text_field( wp_unslash( $_POST['section'] ) ) : '';

		$foundation = BZMI_Foundation::find( $foundation_id );
		if ( ! $foundation ) {
			wp_send_json_error( array( 'message' => __( 'Fondation introuvable.', 'blazing-feedback' ) ) );
		}

		$identity = $foundation->get_identity_section( $section );
		if ( ! $identity ) {
			wp_send_json_error( array( 'message' => __( 'Section introuvable.', 'blazing-feedback' ) ) );
		}

		if ( $identity->validate() ) {
			wp_send_json_success( array(
				'message' => __( 'Section validée.', 'blazing-feedback' ),
				'version' => $identity->version,
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la validation.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Créer un persona
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_create_persona() {
		$foundation_id = isset( $_POST['foundation_id'] ) ? absint( $_POST['foundation_id'] ) : 0;
		$data          = isset( $_POST['persona'] ) ? wp_unslash( $_POST['persona'] ) : array();

		$foundation = BZMI_Foundation::find( $foundation_id );
		if ( ! $foundation ) {
			wp_send_json_error( array( 'message' => __( 'Fondation introuvable.', 'blazing-feedback' ) ) );
		}

		$persona_data = self::sanitize_persona_data( $data );
		$persona_data['foundation_id'] = $foundation_id;

		$persona = BZMI_Foundation_Persona::create( $persona_data );

		if ( $persona ) {
			$foundation->recalculate_scores();

			wp_send_json_success( array(
				'message'    => __( 'Persona créé.', 'blazing-feedback' ),
				'persona_id' => $persona->id,
				'persona'    => $persona->to_array(),
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la création.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Mettre à jour un persona
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_update_persona() {
		$persona_id = isset( $_POST['persona_id'] ) ? absint( $_POST['persona_id'] ) : 0;
		$data       = isset( $_POST['persona'] ) ? wp_unslash( $_POST['persona'] ) : array();

		$persona = BZMI_Foundation_Persona::find( $persona_id );
		if ( ! $persona ) {
			wp_send_json_error( array( 'message' => __( 'Persona introuvable.', 'blazing-feedback' ) ) );
		}

		$persona_data = self::sanitize_persona_data( $data );
		$persona->fill( $persona_data );

		if ( $persona->save() ) {
			$foundation = $persona->get_foundation();
			if ( $foundation ) {
				$foundation->recalculate_scores();
			}

			wp_send_json_success( array(
				'message' => __( 'Persona mis à jour.', 'blazing-feedback' ),
				'persona' => $persona->to_array(),
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la mise à jour.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Supprimer un persona
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_delete_persona() {
		$persona_id = isset( $_POST['persona_id'] ) ? absint( $_POST['persona_id'] ) : 0;

		$persona = BZMI_Foundation_Persona::find( $persona_id );
		if ( ! $persona ) {
			wp_send_json_error( array( 'message' => __( 'Persona introuvable.', 'blazing-feedback' ) ) );
		}

		$foundation = $persona->get_foundation();

		if ( $persona->delete() ) {
			if ( $foundation ) {
				$foundation->recalculate_scores();
			}

			wp_send_json_success( array( 'message' => __( 'Persona supprimé.', 'blazing-feedback' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la suppression.', 'blazing-feedback' ) ) );
	}

	/**
	 * Sanitize le contenu d'une section
	 *
	 * @since 2.0.0
	 * @param string $section Nom de la section.
	 * @param array  $content Contenu brut.
	 * @return array
	 */
	private static function sanitize_section_content( $section, $content ) {
		if ( is_string( $content ) ) {
			$content = json_decode( $content, true ) ?: array();
		}

		$structure = isset( BZMI_Foundation_Identity::CONTENT_STRUCTURE[ $section ] )
			? BZMI_Foundation_Identity::CONTENT_STRUCTURE[ $section ]
			: array();

		$sanitized = array();

		foreach ( $structure as $key => $default ) {
			if ( ! isset( $content[ $key ] ) ) {
				$sanitized[ $key ] = $default;
				continue;
			}

			$value = $content[ $key ];

			// Sanitize selon le type
			if ( is_array( $default ) ) {
				if ( is_array( $value ) ) {
					$sanitized[ $key ] = array_map( 'sanitize_text_field', $value );
				} else {
					$sanitized[ $key ] = array();
				}
			} elseif ( is_int( $default ) ) {
				$sanitized[ $key ] = absint( $value );
			} else {
				$sanitized[ $key ] = wp_kses_post( $value );
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize les données d'un persona
	 *
	 * @since 2.0.0
	 * @param array $data Données brutes.
	 * @return array
	 */
	private static function sanitize_persona_data( $data ) {
		if ( is_string( $data ) ) {
			$data = json_decode( $data, true ) ?: array();
		}

		$sanitized = array();

		// Champs texte
		$text_fields = array( 'name', 'age_range', 'job_title', 'quote', 'status' );
		foreach ( $text_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $data[ $field ] );
			}
		}

		// Description (HTML autorisé)
		if ( isset( $data['description'] ) ) {
			$sanitized['description'] = wp_kses_post( $data['description'] );
		}

		// URL
		if ( isset( $data['avatar_url'] ) ) {
			$sanitized['avatar_url'] = esc_url_raw( $data['avatar_url'] );
		}

		// Champs JSON (arrays)
		$array_fields = array( 'goals', 'pain_points', 'behaviors', 'preferred_channels' );
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
	 * Obtenir les champs du formulaire d'une section
	 *
	 * @since 2.0.0
	 * @param string $section Nom de la section.
	 * @return array
	 */
	public static function get_section_fields( $section ) {
		$fields = array(
			'brand_dna' => array(
				array(
					'name'        => 'mission',
					'label'       => __( 'Mission', 'blazing-feedback' ),
					'type'        => 'textarea',
					'placeholder' => __( 'Quelle est la raison d\'être de votre entreprise ?', 'blazing-feedback' ),
					'help'        => __( 'La mission définit le "pourquoi" de votre entreprise.', 'blazing-feedback' ),
					'ai_enabled'  => true,
				),
				array(
					'name'        => 'values',
					'label'       => __( 'Valeurs fondamentales', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Ajoutez vos valeurs...', 'blazing-feedback' ),
					'help'        => __( '3 à 5 valeurs qui guident toutes vos décisions.', 'blazing-feedback' ),
					'ai_enabled'  => true,
				),
				array(
					'name'        => 'promise',
					'label'       => __( 'Promesse de marque', 'blazing-feedback' ),
					'type'        => 'text',
					'placeholder' => __( 'Votre promesse en une phrase...', 'blazing-feedback' ),
					'help'        => __( 'L\'engagement que vous prenez envers vos clients.', 'blazing-feedback' ),
					'ai_enabled'  => true,
				),
				array(
					'name'        => 'personality',
					'label'       => __( 'Traits de personnalité', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Ex: Innovant, Accessible, Expert...', 'blazing-feedback' ),
					'help'        => __( 'Comment votre marque serait décrite si elle était une personne.', 'blazing-feedback' ),
				),
				array(
					'name'        => 'story',
					'label'       => __( 'Histoire de la marque', 'blazing-feedback' ),
					'type'        => 'wysiwyg',
					'placeholder' => __( 'Racontez l\'histoire de votre marque...', 'blazing-feedback' ),
					'help'        => __( 'Le récit qui humanise votre marque.', 'blazing-feedback' ),
				),
			),
			'vision' => array(
				array(
					'name'        => 'vision_statement',
					'label'       => __( 'Énoncé de vision', 'blazing-feedback' ),
					'type'        => 'textarea',
					'placeholder' => __( 'Où voulez-vous emmener votre entreprise ?', 'blazing-feedback' ),
					'help'        => __( 'L\'avenir que vous voulez créer.', 'blazing-feedback' ),
					'ai_enabled'  => true,
				),
				array(
					'name'        => 'ambition',
					'label'       => __( 'Ambition', 'blazing-feedback' ),
					'type'        => 'textarea',
					'placeholder' => __( 'Quelle empreinte voulez-vous laisser ?', 'blazing-feedback' ),
				),
				array(
					'name'        => 'goals_3_years',
					'label'       => __( 'Objectifs à 3 ans', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Objectifs mesurables...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'goals_5_years',
					'label'       => __( 'Objectifs à 5 ans', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Objectifs mesurables...', 'blazing-feedback' ),
				),
			),
			'tone_voice' => array(
				array(
					'name'        => 'tone_attributes',
					'label'       => __( 'Attributs du ton', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Ex: Professionnel, Chaleureux, Direct...', 'blazing-feedback' ),
					'ai_enabled'  => true,
				),
				array(
					'name'        => 'voice_style',
					'label'       => __( 'Style de voix', 'blazing-feedback' ),
					'type'        => 'textarea',
					'placeholder' => __( 'Décrivez comment votre marque s\'exprime...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'do_list',
					'label'       => __( 'À faire', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Ce que la marque fait toujours...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'dont_list',
					'label'       => __( 'À éviter', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Ce que la marque ne fait jamais...', 'blazing-feedback' ),
				),
				array(
					'name'  => 'examples',
					'label' => __( 'Exemples de messages', 'blazing-feedback' ),
					'type'  => 'repeater',
					'fields' => array(
						array( 'name' => 'context', 'label' => __( 'Contexte', 'blazing-feedback' ), 'type' => 'text' ),
						array( 'name' => 'message', 'label' => __( 'Message', 'blazing-feedback' ), 'type' => 'textarea' ),
					),
				),
			),
			'visuals' => array(
				array(
					'name'  => 'logo_primary_id',
					'label' => __( 'Logo principal', 'blazing-feedback' ),
					'type'  => 'image',
				),
				array(
					'name'  => 'logo_secondary_id',
					'label' => __( 'Logo secondaire', 'blazing-feedback' ),
					'type'  => 'image',
				),
				array(
					'name'  => 'logo_icon_id',
					'label' => __( 'Icône / Favicon', 'blazing-feedback' ),
					'type'  => 'image',
				),
				array(
					'name'        => 'logo_guidelines',
					'label'       => __( 'Directives d\'utilisation du logo', 'blazing-feedback' ),
					'type'        => 'wysiwyg',
					'placeholder' => __( 'Règles d\'utilisation, zones de protection, etc.', 'blazing-feedback' ),
				),
				array(
					'name'        => 'imagery_style',
					'label'       => __( 'Style d\'imagerie', 'blazing-feedback' ),
					'type'        => 'textarea',
					'placeholder' => __( 'Décrivez le style visuel de vos images...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'iconography_style',
					'label'       => __( 'Style d\'iconographie', 'blazing-feedback' ),
					'type'        => 'textarea',
					'placeholder' => __( 'Style des icônes utilisées...', 'blazing-feedback' ),
				),
			),
			'colors' => array(
				array(
					'name'  => 'primary_color',
					'label' => __( 'Couleur primaire', 'blazing-feedback' ),
					'type'  => 'color',
				),
				array(
					'name'  => 'secondary_color',
					'label' => __( 'Couleur secondaire', 'blazing-feedback' ),
					'type'  => 'color',
				),
				array(
					'name'  => 'accent_color',
					'label' => __( 'Couleur d\'accent', 'blazing-feedback' ),
					'type'  => 'color',
				),
				array(
					'name'  => 'background_color',
					'label' => __( 'Couleur de fond', 'blazing-feedback' ),
					'type'  => 'color',
				),
				array(
					'name'  => 'text_color',
					'label' => __( 'Couleur de texte', 'blazing-feedback' ),
					'type'  => 'color',
				),
				array(
					'name'  => 'palette',
					'label' => __( 'Palette étendue', 'blazing-feedback' ),
					'type'  => 'color_repeater',
				),
				array(
					'name'        => 'usage_guidelines',
					'label'       => __( 'Directives d\'utilisation', 'blazing-feedback' ),
					'type'        => 'wysiwyg',
					'placeholder' => __( 'Quand et comment utiliser chaque couleur...', 'blazing-feedback' ),
				),
			),
			'typography' => array(
				array(
					'name'        => 'heading_font',
					'label'       => __( 'Police des titres', 'blazing-feedback' ),
					'type'        => 'text',
					'placeholder' => __( 'Ex: Montserrat, Open Sans...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'body_font',
					'label'       => __( 'Police du corps de texte', 'blazing-feedback' ),
					'type'        => 'text',
					'placeholder' => __( 'Ex: Roboto, Lato...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'accent_font',
					'label'       => __( 'Police d\'accent', 'blazing-feedback' ),
					'type'        => 'text',
					'placeholder' => __( 'Police pour éléments spéciaux...', 'blazing-feedback' ),
				),
				array(
					'name'  => 'font_sizes',
					'label' => __( 'Échelle typographique', 'blazing-feedback' ),
					'type'  => 'repeater',
					'fields' => array(
						array( 'name' => 'name', 'label' => __( 'Nom', 'blazing-feedback' ), 'type' => 'text' ),
						array( 'name' => 'size', 'label' => __( 'Taille', 'blazing-feedback' ), 'type' => 'text' ),
					),
				),
				array(
					'name'        => 'usage_guidelines',
					'label'       => __( 'Directives d\'utilisation', 'blazing-feedback' ),
					'type'        => 'wysiwyg',
					'placeholder' => __( 'Hiérarchie, utilisation des polices...', 'blazing-feedback' ),
				),
			),
		);

		return isset( $fields[ $section ] ) ? $fields[ $section ] : array();
	}
}

// Enregistrer les handlers AJAX
add_action( 'wp_ajax_bzmi_identity', array( 'BZMI_Admin_Foundations_Identity', 'handle_ajax' ) );
