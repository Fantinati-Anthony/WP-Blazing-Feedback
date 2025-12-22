<?php
/**
 * Modèle Foundation Offer
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Foundation_Offer
 *
 * Représente une offre/produit/service
 *
 * @since 2.0.0
 */
class BZMI_Foundation_Offer extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'foundation_offers';

	/**
	 * Colonnes modifiables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'foundation_id',
		'name',
		'type',
		'description',
		'value_proposition',
		'target_personas',
		'features',
		'benefits',
		'pricing_model',
		'price_range',
		'differentiation',
		'status',
		'priority',
		'metadata',
		'created_by',
	);

	/**
	 * Types d'offre
	 *
	 * @var array
	 */
	const TYPES = array(
		'product'     => 'Produit',
		'service'     => 'Service',
		'saas'        => 'SaaS',
		'subscription' => 'Abonnement',
		'consulting'  => 'Conseil',
		'training'    => 'Formation',
		'other'       => 'Autre',
	);

	/**
	 * Modèles de tarification
	 *
	 * @var array
	 */
	const PRICING_MODELS = array(
		'one_time'     => 'Paiement unique',
		'subscription' => 'Abonnement',
		'freemium'     => 'Freemium',
		'usage_based'  => 'À l\'usage',
		'tiered'       => 'Paliers',
		'custom'       => 'Sur devis',
		'free'         => 'Gratuit',
	);

	/**
	 * Statuts disponibles
	 *
	 * @var array
	 */
	const STATUSES = array(
		'draft'      => 'Brouillon',
		'active'     => 'Actif',
		'deprecated' => 'Obsolète',
		'planned'    => 'Prévu',
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
	 * Obtenir les personas cibles
	 *
	 * @return array
	 */
	public function get_target_personas() {
		return $this->decode_json_field( 'target_personas' );
	}

	/**
	 * Définir les personas cibles
	 *
	 * @param array $personas IDs des personas.
	 * @return void
	 */
	public function set_target_personas( $personas ) {
		$this->target_personas = wp_json_encode( $personas );
	}

	/**
	 * Obtenir les fonctionnalités
	 *
	 * @return array
	 */
	public function get_features() {
		return $this->decode_json_field( 'features' );
	}

	/**
	 * Définir les fonctionnalités
	 *
	 * @param array $features Fonctionnalités.
	 * @return void
	 */
	public function set_features( $features ) {
		$this->features = wp_json_encode( $features );
	}

	/**
	 * Obtenir les bénéfices
	 *
	 * @return array
	 */
	public function get_benefits() {
		return $this->decode_json_field( 'benefits' );
	}

	/**
	 * Définir les bénéfices
	 *
	 * @param array $benefits Bénéfices.
	 * @return void
	 */
	public function set_benefits( $benefits ) {
		$this->benefits = wp_json_encode( $benefits );
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
	 * Obtenir le libellé du modèle de tarification
	 *
	 * @return string
	 */
	public function get_pricing_model_label() {
		return isset( self::PRICING_MODELS[ $this->pricing_model ] )
			? self::PRICING_MODELS[ $this->pricing_model ]
			: $this->pricing_model;
	}

	/**
	 * Calculer le score de complétion
	 *
	 * @return int
	 */
	public function get_completion_score() {
		$fields = array(
			'name', 'type', 'description', 'value_proposition',
			'features', 'benefits', 'pricing_model', 'differentiation',
		);

		$filled = 0;
		foreach ( $fields as $field ) {
			$value = $this->$field;
			if ( ! empty( $value ) ) {
				if ( is_string( $value ) && in_array( $field, array( 'features', 'benefits' ), true ) ) {
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
	 * Obtenir les personas liés (objets complets)
	 *
	 * @return array
	 */
	public function get_linked_personas() {
		$persona_ids = $this->get_target_personas();
		if ( empty( $persona_ids ) ) {
			return array();
		}

		$personas = array();
		foreach ( $persona_ids as $id ) {
			$persona = BZMI_Foundation_Persona::find( $id );
			if ( $persona ) {
				$personas[] = $persona;
			}
		}

		return $personas;
	}

	/**
	 * Exporter pour l'IA
	 *
	 * @return array
	 */
	public function to_ai_context() {
		return array(
			'name'              => $this->name,
			'type'              => $this->get_type_label(),
			'description'       => $this->description,
			'value_proposition' => $this->value_proposition,
			'features'          => $this->get_features(),
			'benefits'          => $this->get_benefits(),
			'pricing_model'     => $this->get_pricing_model_label(),
			'price_range'       => $this->price_range,
			'differentiation'   => $this->differentiation,
		);
	}
}
