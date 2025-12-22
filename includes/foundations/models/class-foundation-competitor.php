<?php
/**
 * Modèle Foundation Competitor
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Foundation_Competitor
 *
 * Représente un concurrent
 *
 * @since 2.0.0
 */
class BZMI_Foundation_Competitor extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'foundation_competitors';

	/**
	 * Colonnes modifiables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'foundation_id',
		'name',
		'website',
		'description',
		'type',
		'strengths',
		'weaknesses',
		'market_position',
		'pricing_level',
		'threat_level',
		'notes',
		'metadata',
		'created_by',
	);

	/**
	 * Types de concurrents
	 *
	 * @var array
	 */
	const TYPES = array(
		'direct'     => 'Direct',
		'indirect'   => 'Indirect',
		'substitute' => 'Substitut',
		'potential'  => 'Potentiel',
	);

	/**
	 * Positions de marché
	 *
	 * @var array
	 */
	const MARKET_POSITIONS = array(
		'leader'     => 'Leader',
		'challenger' => 'Challenger',
		'follower'   => 'Suiveur',
		'nicher'     => 'Niche',
		'newcomer'   => 'Nouveau venu',
	);

	/**
	 * Niveaux de prix
	 *
	 * @var array
	 */
	const PRICING_LEVELS = array(
		'premium'    => 'Premium',
		'mid_range'  => 'Milieu de gamme',
		'budget'     => 'Entrée de gamme',
		'free'       => 'Gratuit',
		'mixed'      => 'Mixte',
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
	 * Obtenir les forces
	 *
	 * @return array
	 */
	public function get_strengths() {
		return $this->decode_json_field( 'strengths' );
	}

	/**
	 * Définir les forces
	 *
	 * @param array $strengths Forces.
	 * @return void
	 */
	public function set_strengths( $strengths ) {
		$this->strengths = wp_json_encode( $strengths );
	}

	/**
	 * Obtenir les faiblesses
	 *
	 * @return array
	 */
	public function get_weaknesses() {
		return $this->decode_json_field( 'weaknesses' );
	}

	/**
	 * Définir les faiblesses
	 *
	 * @param array $weaknesses Faiblesses.
	 * @return void
	 */
	public function set_weaknesses( $weaknesses ) {
		$this->weaknesses = wp_json_encode( $weaknesses );
	}

	/**
	 * Décoder un champ JSON
	 *
	 * @param string $field Nom du champ.
	 * @return array
	 */
	protected function decode_json_field( $field ) {
		$value = $this->$field;
		if ( is_string( $value ) ) {
			$value = maybe_unserialize( $value );
		}
		if ( ! is_array( $value ) ) {
			$value = json_decode( $value, true ) ?: array();
		}
		return $value;
	}

	/**
	 * Obtenir le libellé du type
	 *
	 * @return string
	 */
	public function get_type_label() {
		return isset( self::TYPES[ $this->type ] ) ? self::TYPES[ $this->type ] : $this->type;
	}

	/**
	 * Obtenir le libellé de la position
	 *
	 * @return string
	 */
	public function get_market_position_label() {
		return isset( self::MARKET_POSITIONS[ $this->market_position ] )
			? self::MARKET_POSITIONS[ $this->market_position ]
			: $this->market_position;
	}

	/**
	 * Obtenir le libellé du niveau de prix
	 *
	 * @return string
	 */
	public function get_pricing_level_label() {
		return isset( self::PRICING_LEVELS[ $this->pricing_level ] )
			? self::PRICING_LEVELS[ $this->pricing_level ]
			: $this->pricing_level;
	}

	/**
	 * Obtenir la couleur du niveau de menace
	 *
	 * @return string
	 */
	public function get_threat_color() {
		$level = (int) $this->threat_level;
		if ( $level >= 8 ) {
			return '#e74c3c'; // Rouge
		} elseif ( $level >= 5 ) {
			return '#f39c12'; // Orange
		} elseif ( $level >= 3 ) {
			return '#f1c40f'; // Jaune
		}
		return '#2ecc71'; // Vert
	}

	/**
	 * Obtenir le libellé du niveau de menace
	 *
	 * @return string
	 */
	public function get_threat_label() {
		$level = (int) $this->threat_level;
		if ( $level >= 8 ) {
			return 'Critique';
		} elseif ( $level >= 5 ) {
			return 'Élevé';
		} elseif ( $level >= 3 ) {
			return 'Modéré';
		}
		return 'Faible';
	}

	/**
	 * Calculer le score de complétion
	 *
	 * @return int
	 */
	public function get_completion_score() {
		$fields = array(
			'name', 'website', 'description', 'type',
			'strengths', 'weaknesses', 'market_position', 'pricing_level',
		);

		$filled = 0;
		foreach ( $fields as $field ) {
			$value = $this->$field;
			if ( ! empty( $value ) ) {
				if ( is_string( $value ) && in_array( $field, array( 'strengths', 'weaknesses' ), true ) ) {
					$decoded = json_decode( $value, true );
					if ( ! empty( $decoded ) ) {
						$filled++;
					}
				} else {
					$filled++;
				}
			}
		}

		return round( ( $filled / count( $fields ) ) * 100 );
	}

	/**
	 * Exporter pour l'IA
	 *
	 * @return array
	 */
	public function to_ai_context() {
		return array(
			'name'            => $this->name,
			'website'         => $this->website,
			'description'     => $this->description,
			'type'            => $this->get_type_label(),
			'strengths'       => $this->get_strengths(),
			'weaknesses'      => $this->get_weaknesses(),
			'market_position' => $this->get_market_position_label(),
			'pricing_level'   => $this->get_pricing_level_label(),
			'threat_level'    => $this->threat_level,
		);
	}
}
