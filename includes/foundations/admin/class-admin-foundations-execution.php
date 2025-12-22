<?php
/**
 * Admin Foundations Execution - Gestion du socle Exécution
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Admin_Foundations_Execution
 *
 * Gère l'interface d'administration du socle Exécution
 *
 * @since 2.0.0
 */
class BZMI_Admin_Foundations_Execution {

	/**
	 * Gérer les actions AJAX pour le socle Exécution
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function handle_ajax() {
		check_ajax_referer( 'bzmi_nonce', 'nonce' );

		if ( ! current_user_can( 'bzmi_edit_foundations' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission refusée.', 'blazing-feedback' ) ) );
		}

		$action = isset( $_POST['execution_action'] ) ? sanitize_text_field( wp_unslash( $_POST['execution_action'] ) ) : '';

		switch ( $action ) {
			case 'save_section':
				self::ajax_save_section();
				break;

			case 'validate_section':
				self::ajax_validate_section();
				break;

			case 'calculate_budget':
				self::ajax_calculate_budget();
				break;

			case 'calculate_duration':
				self::ajax_calculate_duration();
				break;

			default:
				wp_send_json_error( array( 'message' => __( 'Action inconnue.', 'blazing-feedback' ) ) );
		}
	}

	/**
	 * AJAX: Sauvegarder une section d'exécution
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_save_section() {
		$foundation_id = isset( $_POST['foundation_id'] ) ? absint( $_POST['foundation_id'] ) : 0;
		$section       = isset( $_POST['section'] ) ? sanitize_text_field( wp_unslash( $_POST['section'] ) ) : '';
		$content       = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : array();
		$status        = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'draft';

		$foundation = BZMI_Foundation::find( $foundation_id );
		if ( ! $foundation ) {
			wp_send_json_error( array( 'message' => __( 'Fondation introuvable.', 'blazing-feedback' ) ) );
		}

		// Valider la section
		if ( ! isset( BZMI_Foundation::EXECUTION_SECTIONS[ $section ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Section invalide.', 'blazing-feedback' ) ) );
		}

		// Sanitize le contenu
		$sanitized_content = self::sanitize_section_content( $section, $content );

		// Sauvegarder
		$execution = $foundation->set_execution_section( $section, $sanitized_content, $status );

		if ( $execution ) {
			wp_send_json_success( array(
				'message'          => __( 'Section enregistrée.', 'blazing-feedback' ),
				'execution_id'     => $execution->id,
				'completion_score' => $execution->get_completion_score(),
				'foundation_score' => $foundation->execution_score,
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de l\'enregistrement.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Valider une section d'exécution
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

		$execution = $foundation->get_execution_section( $section );
		if ( ! $execution ) {
			wp_send_json_error( array( 'message' => __( 'Section introuvable.', 'blazing-feedback' ) ) );
		}

		if ( $execution->validate() ) {
			wp_send_json_success( array(
				'message' => __( 'Section validée.', 'blazing-feedback' ),
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Erreur lors de la validation.', 'blazing-feedback' ) ) );
	}

	/**
	 * AJAX: Calculer le budget total
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_calculate_budget() {
		$foundation_id = isset( $_POST['foundation_id'] ) ? absint( $_POST['foundation_id'] ) : 0;

		$foundation = BZMI_Foundation::find( $foundation_id );
		if ( ! $foundation ) {
			wp_send_json_error( array( 'message' => __( 'Fondation introuvable.', 'blazing-feedback' ) ) );
		}

		$execution = $foundation->get_execution_section( 'budget' );
		if ( ! $execution ) {
			wp_send_json_error( array( 'message' => __( 'Section budget introuvable.', 'blazing-feedback' ) ) );
		}

		$total = $execution->calculate_budget_total();
		$content = $execution->get_content();
		$content['total'] = $total;
		$execution->set_content( $content );
		$execution->save();

		wp_send_json_success( array(
			'message' => __( 'Budget calculé.', 'blazing-feedback' ),
			'total'   => $total,
		) );
	}

	/**
	 * AJAX: Calculer la durée du projet
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private static function ajax_calculate_duration() {
		$foundation_id = isset( $_POST['foundation_id'] ) ? absint( $_POST['foundation_id'] ) : 0;

		$foundation = BZMI_Foundation::find( $foundation_id );
		if ( ! $foundation ) {
			wp_send_json_error( array( 'message' => __( 'Fondation introuvable.', 'blazing-feedback' ) ) );
		}

		$execution = $foundation->get_execution_section( 'planning' );
		if ( ! $execution ) {
			wp_send_json_error( array( 'message' => __( 'Section planning introuvable.', 'blazing-feedback' ) ) );
		}

		$days = $execution->get_project_duration_days();

		wp_send_json_success( array(
			'message' => __( 'Durée calculée.', 'blazing-feedback' ),
			'days'    => $days,
			'weeks'   => ceil( $days / 7 ),
			'months'  => round( $days / 30, 1 ),
		) );
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

		$structure = isset( BZMI_Foundation_Execution::CONTENT_STRUCTURE[ $section ] )
			? BZMI_Foundation_Execution::CONTENT_STRUCTURE[ $section ]
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
					$sanitized[ $key ] = self::sanitize_array_recursive( $value );
				} else {
					$sanitized[ $key ] = array();
				}
			} elseif ( is_int( $default ) || is_float( $default ) ) {
				$sanitized[ $key ] = is_numeric( $value ) ? floatval( $value ) : 0;
			} else {
				$sanitized[ $key ] = wp_kses_post( $value );
			}
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
			$clean_key = is_int( $key ) ? $key : sanitize_text_field( $key );

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
	 * Obtenir les champs du formulaire d'une section
	 *
	 * @since 2.0.0
	 * @param string $section Nom de la section.
	 * @return array
	 */
	public static function get_section_fields( $section ) {
		$fields = array(
			'scope' => array(
				array(
					'name'        => 'description',
					'label'       => __( 'Description du périmètre', 'blazing-feedback' ),
					'type'        => 'wysiwyg',
					'placeholder' => __( 'Décrivez le périmètre du projet...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'inclusions',
					'label'       => __( 'Inclusions', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Ce qui est inclus dans le projet...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'exclusions',
					'label'       => __( 'Exclusions', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Ce qui n\'est PAS inclus...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'assumptions',
					'label'       => __( 'Hypothèses', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Hypothèses de travail...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'dependencies',
					'label'       => __( 'Dépendances', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Dépendances externes...', 'blazing-feedback' ),
				),
			),
			'deliverables' => array(
				array(
					'name'   => 'items',
					'label'  => __( 'Livrables', 'blazing-feedback' ),
					'type'   => 'repeater',
					'fields' => array(
						array( 'name' => 'name', 'label' => __( 'Nom', 'blazing-feedback' ), 'type' => 'text' ),
						array( 'name' => 'description', 'label' => __( 'Description', 'blazing-feedback' ), 'type' => 'textarea' ),
						array( 'name' => 'format', 'label' => __( 'Format', 'blazing-feedback' ), 'type' => 'text' ),
						array( 'name' => 'due_date', 'label' => __( 'Date limite', 'blazing-feedback' ), 'type' => 'date' ),
					),
				),
				array(
					'name'        => 'acceptance_criteria',
					'label'       => __( 'Critères d\'acceptation', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Critères pour valider les livrables...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'formats',
					'label'       => __( 'Formats de livraison', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'PDF, Figma, code source...', 'blazing-feedback' ),
				),
			),
			'planning' => array(
				array(
					'name'  => 'start_date',
					'label' => __( 'Date de début', 'blazing-feedback' ),
					'type'  => 'date',
				),
				array(
					'name'  => 'end_date',
					'label' => __( 'Date de fin', 'blazing-feedback' ),
					'type'  => 'date',
				),
				array(
					'name'   => 'milestones',
					'label'  => __( 'Jalons', 'blazing-feedback' ),
					'type'   => 'repeater',
					'fields' => array(
						array( 'name' => 'name', 'label' => __( 'Nom', 'blazing-feedback' ), 'type' => 'text' ),
						array( 'name' => 'date', 'label' => __( 'Date', 'blazing-feedback' ), 'type' => 'date' ),
						array( 'name' => 'description', 'label' => __( 'Description', 'blazing-feedback' ), 'type' => 'textarea' ),
					),
				),
				array(
					'name'   => 'phases',
					'label'  => __( 'Phases', 'blazing-feedback' ),
					'type'   => 'repeater',
					'fields' => array(
						array( 'name' => 'name', 'label' => __( 'Nom', 'blazing-feedback' ), 'type' => 'text' ),
						array( 'name' => 'start', 'label' => __( 'Début', 'blazing-feedback' ), 'type' => 'date' ),
						array( 'name' => 'end', 'label' => __( 'Fin', 'blazing-feedback' ), 'type' => 'date' ),
					),
				),
				array(
					'name'  => 'buffer_days',
					'label' => __( 'Jours de marge', 'blazing-feedback' ),
					'type'  => 'number',
					'min'   => 0,
				),
			),
			'budget' => array(
				array(
					'name'     => 'total',
					'label'    => __( 'Budget total', 'blazing-feedback' ),
					'type'     => 'number',
					'step'     => '0.01',
					'readonly' => true,
					'help'     => __( 'Calculé automatiquement depuis la ventilation.', 'blazing-feedback' ),
				),
				array(
					'name'    => 'currency',
					'label'   => __( 'Devise', 'blazing-feedback' ),
					'type'    => 'select',
					'options' => array(
						'EUR' => 'EUR (€)',
						'USD' => 'USD ($)',
						'GBP' => 'GBP (£)',
						'CHF' => 'CHF',
					),
				),
				array(
					'name'   => 'breakdown',
					'label'  => __( 'Ventilation', 'blazing-feedback' ),
					'type'   => 'repeater',
					'fields' => array(
						array( 'name' => 'category', 'label' => __( 'Catégorie', 'blazing-feedback' ), 'type' => 'text' ),
						array( 'name' => 'description', 'label' => __( 'Description', 'blazing-feedback' ), 'type' => 'text' ),
						array( 'name' => 'amount', 'label' => __( 'Montant', 'blazing-feedback' ), 'type' => 'number', 'step' => '0.01' ),
					),
					'calculate' => true,
				),
				array(
					'name'        => 'payment_terms',
					'label'       => __( 'Conditions de paiement', 'blazing-feedback' ),
					'type'        => 'textarea',
					'placeholder' => __( 'Ex: 30% à la commande, 40% à mi-projet, 30% à la livraison', 'blazing-feedback' ),
				),
				array(
					'name'  => 'contingency',
					'label' => __( 'Provision pour imprévus (%)', 'blazing-feedback' ),
					'type'  => 'number',
					'min'   => 0,
					'max'   => 50,
				),
			),
			'constraints' => array(
				array(
					'name'        => 'technical',
					'label'       => __( 'Contraintes techniques', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Ex: Compatibilité IE11, API existante...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'organizational',
					'label'       => __( 'Contraintes organisationnelles', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Ex: Validation hiérarchique requise...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'time',
					'label'       => __( 'Contraintes de temps', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Ex: Lancement avant le salon...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'resource',
					'label'       => __( 'Contraintes de ressources', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Ex: Équipe limitée à 3 personnes...', 'blazing-feedback' ),
				),
			),
			'legal' => array(
				array(
					'name'        => 'gdpr_compliance',
					'label'       => __( 'Conformité RGPD', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Exigences RGPD applicables...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'data_handling',
					'label'       => __( 'Gestion des données', 'blazing-feedback' ),
					'type'        => 'wysiwyg',
					'placeholder' => __( 'Comment les données seront collectées, stockées, traitées...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'consent_requirements',
					'label'       => __( 'Exigences de consentement', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Types de consentement requis...', 'blazing-feedback' ),
				),
				array(
					'name'        => 'retention_policy',
					'label'       => __( 'Politique de rétention', 'blazing-feedback' ),
					'type'        => 'textarea',
					'placeholder' => __( 'Durée de conservation des données...', 'blazing-feedback' ),
				),
				array(
					'name'   => 'third_party_tools',
					'label'  => __( 'Outils tiers', 'blazing-feedback' ),
					'type'   => 'repeater',
					'fields' => array(
						array( 'name' => 'name', 'label' => __( 'Nom', 'blazing-feedback' ), 'type' => 'text' ),
						array( 'name' => 'purpose', 'label' => __( 'Usage', 'blazing-feedback' ), 'type' => 'text' ),
						array( 'name' => 'compliance', 'label' => __( 'Conformité', 'blazing-feedback' ), 'type' => 'text' ),
					),
				),
				array(
					'name'        => 'contractual',
					'label'       => __( 'Clauses contractuelles', 'blazing-feedback' ),
					'type'        => 'tags',
					'placeholder' => __( 'Clauses spécifiques...', 'blazing-feedback' ),
				),
			),
		);

		return isset( $fields[ $section ] ) ? $fields[ $section ] : array();
	}
}

// Enregistrer les handlers AJAX
add_action( 'wp_ajax_bzmi_execution', array( 'BZMI_Admin_Foundations_Execution', 'handle_ajax' ) );
