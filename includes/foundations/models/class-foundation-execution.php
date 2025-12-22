<?php
/**
 * Modèle Foundation Execution
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Foundation_Execution
 *
 * Représente une section du socle Exécution
 *
 * @since 2.0.0
 */
class BZMI_Foundation_Execution extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'foundation_execution';

	/**
	 * Colonnes modifiables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'foundation_id',
		'section',
		'content',
		'status',
		'validated_at',
		'validated_by',
		'created_by',
	);

	/**
	 * Statuts disponibles
	 *
	 * @var array
	 */
	const STATUSES = array(
		'draft'     => 'Brouillon',
		'validated' => 'Validé',
	);

	/**
	 * Structure du contenu par section
	 *
	 * @var array
	 */
	const CONTENT_STRUCTURE = array(
		'scope' => array(
			'description'     => '',
			'inclusions'      => array(),
			'exclusions'      => array(),
			'assumptions'     => array(),
			'dependencies'    => array(),
		),
		'deliverables' => array(
			'items'           => array(),
			'acceptance_criteria' => array(),
			'formats'         => array(),
		),
		'planning' => array(
			'start_date'      => '',
			'end_date'        => '',
			'milestones'      => array(),
			'phases'          => array(),
			'buffer_days'     => 0,
		),
		'budget' => array(
			'total'           => 0,
			'currency'        => 'EUR',
			'breakdown'       => array(),
			'payment_terms'   => '',
			'contingency'     => 0,
		),
		'constraints' => array(
			'technical'       => array(),
			'organizational'  => array(),
			'time'            => array(),
			'resource'        => array(),
		),
		'legal' => array(
			'gdpr_compliance' => array(),
			'data_handling'   => '',
			'consent_requirements' => array(),
			'retention_policy' => '',
			'third_party_tools' => array(),
			'contractual'     => array(),
		),
	);

	/**
	 * Obtenir la fondation parente
	 *
	 * @return BZMI_Foundation|null
	 */
	public function get_foundation() {
		if ( ! $this->foundation_id ) {
			return null;
		}
		return BZMI_Foundation::find( $this->foundation_id );
	}

	/**
	 * Obtenir le contenu décodé
	 *
	 * @return array
	 */
	public function get_content() {
		$content = $this->content;
		if ( is_string( $content ) ) {
			$content = maybe_unserialize( $content );
		}
		if ( ! is_array( $content ) ) {
			$content = json_decode( $content, true ) ?: array();
		}

		// Fusionner avec la structure par défaut
		$default = isset( self::CONTENT_STRUCTURE[ $this->section ] )
			? self::CONTENT_STRUCTURE[ $this->section ]
			: array();

		return wp_parse_args( $content, $default );
	}

	/**
	 * Définir le contenu
	 *
	 * @param array $content Contenu.
	 * @return void
	 */
	public function set_content( $content ) {
		$this->content = wp_json_encode( $content );
	}

	/**
	 * Valider la section
	 *
	 * @return bool
	 */
	public function validate() {
		$this->status       = 'validated';
		$this->validated_at = current_time( 'mysql' );
		$this->validated_by = get_current_user_id();
		return $this->save();
	}

	/**
	 * Remettre en brouillon
	 *
	 * @return bool
	 */
	public function unvalidate() {
		$this->status       = 'draft';
		$this->validated_at = null;
		$this->validated_by = null;
		return $this->save();
	}

	/**
	 * Vérifier si la section est complète
	 *
	 * @return bool
	 */
	public function is_complete() {
		$content = $this->get_content();
		$structure = isset( self::CONTENT_STRUCTURE[ $this->section ] )
			? self::CONTENT_STRUCTURE[ $this->section ]
			: array();

		$required_fields = $this->get_required_fields();

		foreach ( $required_fields as $key ) {
			if ( ! isset( $content[ $key ] ) || empty( $content[ $key ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Obtenir les champs requis par section
	 *
	 * @return array
	 */
	protected function get_required_fields() {
		$required = array(
			'scope'        => array( 'description', 'inclusions' ),
			'deliverables' => array( 'items' ),
			'planning'     => array( 'start_date', 'end_date' ),
			'budget'       => array( 'total' ),
			'constraints'  => array(),
			'legal'        => array( 'data_handling' ),
		);

		return isset( $required[ $this->section ] ) ? $required[ $this->section ] : array();
	}

	/**
	 * Calculer le score de complétion
	 *
	 * @return int
	 */
	public function get_completion_score() {
		$content = $this->get_content();
		$structure = isset( self::CONTENT_STRUCTURE[ $this->section ] )
			? self::CONTENT_STRUCTURE[ $this->section ]
			: array();

		if ( empty( $structure ) ) {
			return 0;
		}

		$filled = 0;
		$total  = count( $structure );

		foreach ( $structure as $key => $default ) {
			if ( isset( $content[ $key ] ) && ! empty( $content[ $key ] ) ) {
				$filled++;
			}
		}

		return round( ( $filled / $total ) * 100 );
	}

	/**
	 * Obtenir le libellé de la section
	 *
	 * @return string
	 */
	public function get_section_label() {
		$labels = BZMI_Foundation::EXECUTION_SECTIONS;
		return isset( $labels[ $this->section ] ) ? $labels[ $this->section ] : $this->section;
	}

	/**
	 * Obtenir l'icône de la section
	 *
	 * @return string
	 */
	public function get_section_icon() {
		$icons = array(
			'scope'        => 'dashicons-visibility',
			'deliverables' => 'dashicons-media-document',
			'planning'     => 'dashicons-calendar-alt',
			'budget'       => 'dashicons-chart-area',
			'constraints'  => 'dashicons-warning',
			'legal'        => 'dashicons-shield',
		);

		return isset( $icons[ $this->section ] ) ? $icons[ $this->section ] : 'dashicons-admin-generic';
	}

	/**
	 * Calculer le budget total depuis le breakdown
	 *
	 * @return float
	 */
	public function calculate_budget_total() {
		if ( 'budget' !== $this->section ) {
			return 0;
		}

		$content = $this->get_content();
		$breakdown = isset( $content['breakdown'] ) ? $content['breakdown'] : array();

		$total = 0;
		foreach ( $breakdown as $item ) {
			if ( isset( $item['amount'] ) ) {
				$total += (float) $item['amount'];
			}
		}

		return $total;
	}

	/**
	 * Obtenir la durée du projet en jours
	 *
	 * @return int
	 */
	public function get_project_duration_days() {
		if ( 'planning' !== $this->section ) {
			return 0;
		}

		$content = $this->get_content();
		$start   = isset( $content['start_date'] ) ? $content['start_date'] : '';
		$end     = isset( $content['end_date'] ) ? $content['end_date'] : '';

		if ( empty( $start ) || empty( $end ) ) {
			return 0;
		}

		$start_date = new DateTime( $start );
		$end_date   = new DateTime( $end );
		$diff       = $start_date->diff( $end_date );

		return $diff->days;
	}

	/**
	 * Exporter pour l'IA
	 *
	 * @return array
	 */
	public function to_ai_context() {
		return array(
			'section' => $this->section,
			'label'   => $this->get_section_label(),
			'content' => $this->get_content(),
			'status'  => $this->status,
		);
	}
}
