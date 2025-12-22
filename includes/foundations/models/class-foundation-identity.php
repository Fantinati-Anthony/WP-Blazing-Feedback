<?php
/**
 * Modèle Foundation Identity
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Foundation_Identity
 *
 * Représente une section du socle Identité
 *
 * @since 2.0.0
 */
class BZMI_Foundation_Identity extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'foundation_identity';

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
		'version',
		'ai_suggestions',
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
		'hypothesis' => 'Hypothèse',
		'validated'  => 'Validé',
	);

	/**
	 * Structure du contenu par section
	 *
	 * @var array
	 */
	const CONTENT_STRUCTURE = array(
		'brand_dna' => array(
			'mission'         => '',
			'values'          => array(),
			'promise'         => '',
			'personality'     => array(),
			'story'           => '',
		),
		'vision' => array(
			'vision_statement' => '',
			'ambition'         => '',
			'goals_3_years'    => array(),
			'goals_5_years'    => array(),
		),
		'tone_voice' => array(
			'tone_attributes'  => array(),
			'voice_style'      => '',
			'do_list'          => array(),
			'dont_list'        => array(),
			'examples'         => array(),
		),
		'visuals' => array(
			'logo_primary_id'    => 0,
			'logo_secondary_id'  => 0,
			'logo_icon_id'       => 0,
			'logo_guidelines'    => '',
			'imagery_style'      => '',
			'iconography_style'  => '',
		),
		'colors' => array(
			'primary_color'      => '',
			'secondary_color'    => '',
			'accent_color'       => '',
			'background_color'   => '',
			'text_color'         => '',
			'palette'            => array(),
			'usage_guidelines'   => '',
		),
		'typography' => array(
			'heading_font'       => '',
			'body_font'          => '',
			'accent_font'        => '',
			'font_sizes'         => array(),
			'usage_guidelines'   => '',
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
	 * Obtenir les suggestions IA
	 *
	 * @return array
	 */
	public function get_ai_suggestions() {
		$suggestions = $this->ai_suggestions;
		if ( is_string( $suggestions ) ) {
			$suggestions = maybe_unserialize( $suggestions );
		}
		if ( ! is_array( $suggestions ) ) {
			$suggestions = json_decode( $suggestions, true ) ?: array();
		}
		return $suggestions;
	}

	/**
	 * Ajouter une suggestion IA
	 *
	 * @param string $field      Champ concerné.
	 * @param string $suggestion Suggestion.
	 * @param float  $confidence Score de confiance.
	 * @return void
	 */
	public function add_ai_suggestion( $field, $suggestion, $confidence = 0.8 ) {
		$suggestions = $this->get_ai_suggestions();
		$suggestions[] = array(
			'field'       => $field,
			'suggestion'  => $suggestion,
			'confidence'  => $confidence,
			'created_at'  => current_time( 'mysql' ),
			'applied'     => false,
		);
		$this->ai_suggestions = wp_json_encode( $suggestions );
		$this->save();
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
		$this->version      = ( $this->version ?? 0 ) + 1;
		return $this->save();
	}

	/**
	 * Remettre en hypothèse
	 *
	 * @return bool
	 */
	public function unvalidate() {
		$this->status       = 'hypothesis';
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

		foreach ( $structure as $key => $default ) {
			if ( ! isset( $content[ $key ] ) || empty( $content[ $key ] ) ) {
				return false;
			}
		}

		return true;
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
		$labels = BZMI_Foundation::IDENTITY_SECTIONS;
		return isset( $labels[ $this->section ] ) ? $labels[ $this->section ] : $this->section;
	}
}
