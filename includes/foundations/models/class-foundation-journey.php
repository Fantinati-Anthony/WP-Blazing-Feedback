<?php
/**
 * Modèle Foundation Journey
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Foundation_Journey
 *
 * Représente un parcours utilisateur
 *
 * @since 2.0.0
 */
class BZMI_Foundation_Journey extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'foundation_journeys';

	/**
	 * Colonnes modifiables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'foundation_id',
		'name',
		'persona_id',
		'description',
		'objective',
		'stages',
		'touchpoints',
		'emotions',
		'pain_points',
		'opportunities',
		'status',
		'metadata',
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
		'optimized' => 'Optimisé',
	);

	/**
	 * Étapes de parcours prédéfinies
	 *
	 * @var array
	 */
	const DEFAULT_STAGES = array(
		'awareness'     => 'Découverte',
		'consideration' => 'Considération',
		'decision'      => 'Décision',
		'purchase'      => 'Achat',
		'retention'     => 'Fidélisation',
		'advocacy'      => 'Recommandation',
	);

	/**
	 * Émotions possibles
	 *
	 * @var array
	 */
	const EMOTIONS = array(
		'delighted'   => array( 'label' => 'Ravi', 'value' => 5, 'emoji' => '😊' ),
		'satisfied'   => array( 'label' => 'Satisfait', 'value' => 4, 'emoji' => '🙂' ),
		'neutral'     => array( 'label' => 'Neutre', 'value' => 3, 'emoji' => '😐' ),
		'frustrated'  => array( 'label' => 'Frustré', 'value' => 2, 'emoji' => '😕' ),
		'angry'       => array( 'label' => 'En colère', 'value' => 1, 'emoji' => '😠' ),
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
	 * Obtenir le persona associé
	 *
	 * @return BZMI_Foundation_Persona|null
	 */
	public function get_persona() {
		if ( ! $this->persona_id ) {
			return null;
		}
		return BZMI_Foundation_Persona::find( $this->persona_id );
	}

	/**
	 * Obtenir les étapes
	 *
	 * @return array
	 */
	public function get_stages() {
		return $this->decode_json_field( 'stages' );
	}

	/**
	 * Définir les étapes
	 *
	 * @param array $stages Étapes.
	 * @return void
	 */
	public function set_stages( $stages ) {
		$this->stages = wp_json_encode( $stages );
	}

	/**
	 * Obtenir les points de contact
	 *
	 * @return array
	 */
	public function get_touchpoints() {
		return $this->decode_json_field( 'touchpoints' );
	}

	/**
	 * Définir les points de contact
	 *
	 * @param array $touchpoints Points de contact.
	 * @return void
	 */
	public function set_touchpoints( $touchpoints ) {
		$this->touchpoints = wp_json_encode( $touchpoints );
	}

	/**
	 * Obtenir les émotions par étape
	 *
	 * @return array
	 */
	public function get_emotions() {
		return $this->decode_json_field( 'emotions' );
	}

	/**
	 * Définir les émotions
	 *
	 * @param array $emotions Émotions par étape.
	 * @return void
	 */
	public function set_emotions( $emotions ) {
		$this->emotions = wp_json_encode( $emotions );
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
	 * Obtenir les opportunités
	 *
	 * @return array
	 */
	public function get_opportunities() {
		return $this->decode_json_field( 'opportunities' );
	}

	/**
	 * Définir les opportunités
	 *
	 * @param array $opportunities Opportunités.
	 * @return void
	 */
	public function set_opportunities( $opportunities ) {
		$this->opportunities = wp_json_encode( $opportunities );
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
	 * Ajouter une étape
	 *
	 * @param array $stage Données de l'étape.
	 * @return void
	 */
	public function add_stage( $stage ) {
		$stages   = $this->get_stages();
		$stages[] = wp_parse_args( $stage, array(
			'id'          => uniqid( 'stage_' ),
			'name'        => '',
			'description' => '',
			'duration'    => '',
			'channels'    => array(),
			'actions'     => array(),
		) );
		$this->set_stages( $stages );
	}

	/**
	 * Calculer le score émotionnel moyen
	 *
	 * @return float
	 */
	public function get_average_emotion_score() {
		$emotions = $this->get_emotions();
		if ( empty( $emotions ) ) {
			return 0;
		}

		$total = 0;
		$count = 0;

		foreach ( $emotions as $stage => $emotion ) {
			if ( isset( self::EMOTIONS[ $emotion ] ) ) {
				$total += self::EMOTIONS[ $emotion ]['value'];
				$count++;
			}
		}

		return $count > 0 ? round( $total / $count, 1 ) : 0;
	}

	/**
	 * Calculer le score de complétion
	 *
	 * @return int
	 */
	public function get_completion_score() {
		$fields = array( 'name', 'objective', 'stages', 'touchpoints' );

		$filled = 0;
		foreach ( $fields as $field ) {
			$value = $this->$field;
			if ( ! empty( $value ) ) {
				if ( is_string( $value ) && in_array( $field, array( 'stages', 'touchpoints' ), true ) ) {
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
	 * Générer un template de parcours
	 *
	 * @param string $type Type de parcours (purchase, support, onboarding).
	 * @return array
	 */
	public static function get_template( $type = 'purchase' ) {
		$templates = array(
			'purchase' => array(
				'stages' => array(
					array(
						'id'          => 'awareness',
						'name'        => 'Découverte',
						'description' => 'L\'utilisateur prend conscience de son besoin ou découvre la marque',
						'channels'    => array( 'social', 'search', 'advertising' ),
					),
					array(
						'id'          => 'consideration',
						'name'        => 'Considération',
						'description' => 'L\'utilisateur compare les options et évalue la marque',
						'channels'    => array( 'website', 'reviews', 'comparison' ),
					),
					array(
						'id'          => 'decision',
						'name'        => 'Décision',
						'description' => 'L\'utilisateur choisit et s\'engage',
						'channels'    => array( 'website', 'sales', 'demo' ),
					),
					array(
						'id'          => 'purchase',
						'name'        => 'Achat',
						'description' => 'Transaction et confirmation',
						'channels'    => array( 'checkout', 'email' ),
					),
					array(
						'id'          => 'retention',
						'name'        => 'Fidélisation',
						'description' => 'Engagement post-achat et satisfaction',
						'channels'    => array( 'email', 'support', 'community' ),
					),
				),
			),
			'onboarding' => array(
				'stages' => array(
					array( 'id' => 'signup', 'name' => 'Inscription', 'description' => 'Création de compte' ),
					array( 'id' => 'welcome', 'name' => 'Accueil', 'description' => 'Premier contact et orientation' ),
					array( 'id' => 'setup', 'name' => 'Configuration', 'description' => 'Paramétrage initial' ),
					array( 'id' => 'first_success', 'name' => 'Premier succès', 'description' => 'Première action réussie' ),
					array( 'id' => 'habit', 'name' => 'Habitude', 'description' => 'Usage régulier établi' ),
				),
			),
			'support' => array(
				'stages' => array(
					array( 'id' => 'issue', 'name' => 'Problème identifié', 'description' => 'L\'utilisateur rencontre un problème' ),
					array( 'id' => 'search', 'name' => 'Recherche solution', 'description' => 'Tentative de résolution autonome' ),
					array( 'id' => 'contact', 'name' => 'Contact support', 'description' => 'Demande d\'aide' ),
					array( 'id' => 'resolution', 'name' => 'Résolution', 'description' => 'Problème résolu' ),
					array( 'id' => 'feedback', 'name' => 'Retour', 'description' => 'Évaluation de l\'expérience' ),
				),
			),
		);

		return isset( $templates[ $type ] ) ? $templates[ $type ] : $templates['purchase'];
	}

	/**
	 * Exporter pour l'IA
	 *
	 * @return array
	 */
	public function to_ai_context() {
		$persona = $this->get_persona();

		return array(
			'name'          => $this->name,
			'persona'       => $persona ? $persona->to_ai_context() : null,
			'objective'     => $this->objective,
			'stages'        => $this->get_stages(),
			'touchpoints'   => $this->get_touchpoints(),
			'emotions'      => $this->get_emotions(),
			'pain_points'   => $this->get_pain_points(),
			'opportunities' => $this->get_opportunities(),
		);
	}
}
