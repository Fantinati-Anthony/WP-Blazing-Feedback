<?php
/**
 * Modèle Foundation Persona
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Foundation_Persona
 *
 * Représente un persona/cible
 *
 * @since 2.0.0
 */
class BZMI_Foundation_Persona extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'foundation_personas';

	/**
	 * Colonnes modifiables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'foundation_id',
		'name',
		'avatar_url',
		'age_range',
		'job_title',
		'description',
		'goals',
		'pain_points',
		'behaviors',
		'preferred_channels',
		'quote',
		'priority',
		'status',
		'metadata',
		'created_by',
	);

	/**
	 * Tranches d'âge prédéfinies
	 *
	 * @var array
	 */
	const AGE_RANGES = array(
		'18-24' => '18-24 ans',
		'25-34' => '25-34 ans',
		'35-44' => '35-44 ans',
		'45-54' => '45-54 ans',
		'55-64' => '55-64 ans',
		'65+'   => '65 ans et plus',
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
	 * Obtenir les objectifs
	 *
	 * @return array
	 */
	public function get_goals() {
		return $this->decode_json_field( 'goals' );
	}

	/**
	 * Définir les objectifs
	 *
	 * @param array $goals Objectifs.
	 * @return void
	 */
	public function set_goals( $goals ) {
		$this->goals = wp_json_encode( $goals );
	}

	/**
	 * Obtenir les points de douleur
	 *
	 * @return array
	 */
	public function get_pain_points() {
		return $this->decode_json_field( 'pain_points' );
	}

	/**
	 * Définir les points de douleur
	 *
	 * @param array $pain_points Points de douleur.
	 * @return void
	 */
	public function set_pain_points( $pain_points ) {
		$this->pain_points = wp_json_encode( $pain_points );
	}

	/**
	 * Obtenir les comportements
	 *
	 * @return array
	 */
	public function get_behaviors() {
		return $this->decode_json_field( 'behaviors' );
	}

	/**
	 * Définir les comportements
	 *
	 * @param array $behaviors Comportements.
	 * @return void
	 */
	public function set_behaviors( $behaviors ) {
		$this->behaviors = wp_json_encode( $behaviors );
	}

	/**
	 * Obtenir les canaux préférés
	 *
	 * @return array
	 */
	public function get_preferred_channels() {
		return $this->decode_json_field( 'preferred_channels' );
	}

	/**
	 * Définir les canaux préférés
	 *
	 * @param array $channels Canaux.
	 * @return void
	 */
	public function set_preferred_channels( $channels ) {
		$this->preferred_channels = wp_json_encode( $channels );
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
	 * Obtenir les parcours associés
	 *
	 * @return array
	 */
	public function get_journeys() {
		return BZMI_Foundation_Journey::where( array( 'persona_id' => $this->id ) );
	}

	/**
	 * Valider le persona
	 *
	 * @return bool
	 */
	public function validate() {
		$this->status = 'validated';
		return $this->save();
	}

	/**
	 * Calculer le score de complétion
	 *
	 * @return int
	 */
	public function get_completion_score() {
		$fields = array(
			'name', 'age_range', 'job_title', 'description',
			'goals', 'pain_points', 'behaviors', 'preferred_channels',
		);

		$filled = 0;
		foreach ( $fields as $field ) {
			$value = $this->$field;
			if ( ! empty( $value ) ) {
				if ( is_string( $value ) && in_array( $field, array( 'goals', 'pain_points', 'behaviors', 'preferred_channels' ), true ) ) {
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
	 * Générer un avatar placeholder
	 *
	 * @return string
	 */
	public function get_avatar_url() {
		if ( ! empty( $this->avatar_url ) ) {
			return $this->avatar_url;
		}

		// Générer un avatar UI avec les initiales
		$initials = '';
		$words = explode( ' ', $this->name );
		foreach ( $words as $word ) {
			$initials .= strtoupper( substr( $word, 0, 1 ) );
		}
		$initials = substr( $initials, 0, 2 );

		return 'data:image/svg+xml,' . rawurlencode( sprintf(
			'<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><rect fill="%s" width="80" height="80"/><text fill="white" font-size="32" font-family="Arial" x="50%%" y="50%%" text-anchor="middle" dy=".35em">%s</text></svg>',
			$this->get_avatar_color(),
			esc_html( $initials )
		) );
	}

	/**
	 * Obtenir une couleur basée sur le nom
	 *
	 * @return string
	 */
	protected function get_avatar_color() {
		$colors = array( '#3498db', '#e74c3c', '#2ecc71', '#9b59b6', '#f39c12', '#1abc9c', '#34495e' );
		$hash = crc32( $this->name ?? 'default' );
		return $colors[ abs( $hash ) % count( $colors ) ];
	}

	/**
	 * Exporter pour l'IA
	 *
	 * @return array
	 */
	public function to_ai_context() {
		return array(
			'name'               => $this->name,
			'age_range'          => $this->age_range,
			'job_title'          => $this->job_title,
			'description'        => $this->description,
			'goals'              => $this->get_goals(),
			'pain_points'        => $this->get_pain_points(),
			'behaviors'          => $this->get_behaviors(),
			'preferred_channels' => $this->get_preferred_channels(),
			'quote'              => $this->quote,
		);
	}
}
