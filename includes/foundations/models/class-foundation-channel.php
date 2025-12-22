<?php
/**
 * Modèle Foundation Channel
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Foundation_Channel
 *
 * Représente un canal de communication
 *
 * @since 2.0.0
 */
class BZMI_Foundation_Channel extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'foundation_channels';

	/**
	 * Colonnes modifiables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'foundation_id',
		'name',
		'type',
		'platform',
		'url',
		'description',
		'objectives',
		'target_personas',
		'key_messages',
		'tone_guidelines',
		'cta_primary',
		'cta_secondary',
		'kpis',
		'priority',
		'status',
		'metadata',
		'created_by',
	);

	/**
	 * Types de canaux
	 *
	 * @var array
	 */
	const TYPES = array(
		'website'     => array(
			'label'     => 'Site web',
			'icon'      => 'dashicons-admin-site',
			'platforms' => array( 'corporate', 'ecommerce', 'landing', 'blog' ),
		),
		'social'      => array(
			'label'     => 'Réseaux sociaux',
			'icon'      => 'dashicons-share',
			'platforms' => array( 'facebook', 'instagram', 'linkedin', 'twitter', 'tiktok', 'youtube', 'pinterest' ),
		),
		'email'       => array(
			'label'     => 'Email',
			'icon'      => 'dashicons-email',
			'platforms' => array( 'newsletter', 'transactional', 'automation', 'campaign' ),
		),
		'advertising' => array(
			'label'     => 'Publicité',
			'icon'      => 'dashicons-megaphone',
			'platforms' => array( 'google_ads', 'facebook_ads', 'linkedin_ads', 'display', 'native' ),
		),
		'content'     => array(
			'label'     => 'Contenu',
			'icon'      => 'dashicons-media-document',
			'platforms' => array( 'blog', 'podcast', 'video', 'webinar', 'ebook', 'whitepaper' ),
		),
		'offline'     => array(
			'label'     => 'Hors ligne',
			'icon'      => 'dashicons-location',
			'platforms' => array( 'print', 'event', 'retail', 'phone', 'direct_mail' ),
		),
		'messaging'   => array(
			'label'     => 'Messagerie',
			'icon'      => 'dashicons-format-chat',
			'platforms' => array( 'chat', 'whatsapp', 'messenger', 'sms' ),
		),
		'other'       => array(
			'label'     => 'Autre',
			'icon'      => 'dashicons-admin-generic',
			'platforms' => array(),
		),
	);

	/**
	 * Statuts disponibles
	 *
	 * @var array
	 */
	const STATUSES = array(
		'active'   => 'Actif',
		'planned'  => 'Prévu',
		'paused'   => 'En pause',
		'archived' => 'Archivé',
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
	public function get_objectives() {
		return $this->decode_json_field( 'objectives' );
	}

	/**
	 * Définir les objectifs
	 *
	 * @param array $objectives Objectifs.
	 * @return void
	 */
	public function set_objectives( $objectives ) {
		$this->objectives = wp_json_encode( $objectives );
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
	 * Obtenir les messages clés
	 *
	 * @return array
	 */
	public function get_key_messages() {
		return $this->decode_json_field( 'key_messages' );
	}

	/**
	 * Définir les messages clés
	 *
	 * @param array $messages Messages.
	 * @return void
	 */
	public function set_key_messages( $messages ) {
		$this->key_messages = wp_json_encode( $messages );
	}

	/**
	 * Obtenir les KPIs
	 *
	 * @return array
	 */
	public function get_kpis() {
		return $this->decode_json_field( 'kpis' );
	}

	/**
	 * Définir les KPIs
	 *
	 * @param array $kpis KPIs.
	 * @return void
	 */
	public function set_kpis( $kpis ) {
		$this->kpis = wp_json_encode( $kpis );
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
		return isset( self::TYPES[ $this->type ]['label'] )
			? self::TYPES[ $this->type ]['label']
			: $this->type;
	}

	/**
	 * Obtenir l'icône du type
	 *
	 * @return string
	 */
	public function get_type_icon() {
		return isset( self::TYPES[ $this->type ]['icon'] )
			? self::TYPES[ $this->type ]['icon']
			: 'dashicons-admin-generic';
	}

	/**
	 * Obtenir les plateformes disponibles pour ce type
	 *
	 * @return array
	 */
	public function get_available_platforms() {
		return isset( self::TYPES[ $this->type ]['platforms'] )
			? self::TYPES[ $this->type ]['platforms']
			: array();
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
	 * Calculer le score de complétion
	 *
	 * @return int
	 */
	public function get_completion_score() {
		$fields = array(
			'name', 'type', 'description', 'objectives',
			'key_messages', 'cta_primary',
		);

		$filled = 0;
		foreach ( $fields as $field ) {
			$value = $this->$field;
			if ( ! empty( $value ) ) {
				if ( is_string( $value ) && in_array( $field, array( 'objectives', 'key_messages' ), true ) ) {
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
	 * Obtenir les parcours utilisant ce canal
	 *
	 * @return array
	 */
	public function get_related_journeys() {
		$journeys = BZMI_Foundation_Journey::where( array( 'foundation_id' => $this->foundation_id ) );
		$related  = array();

		foreach ( $journeys as $journey ) {
			$touchpoints = $journey->get_touchpoints();
			foreach ( $touchpoints as $touchpoint ) {
				if ( isset( $touchpoint['channel_id'] ) && (int) $touchpoint['channel_id'] === (int) $this->id ) {
					$related[] = $journey;
					break;
				}
			}
		}

		return $related;
	}

	/**
	 * Exporter pour l'IA
	 *
	 * @return array
	 */
	public function to_ai_context() {
		return array(
			'name'            => $this->name,
			'type'            => $this->get_type_label(),
			'platform'        => $this->platform,
			'description'     => $this->description,
			'objectives'      => $this->get_objectives(),
			'key_messages'    => $this->get_key_messages(),
			'tone_guidelines' => $this->tone_guidelines,
			'cta_primary'     => $this->cta_primary,
			'cta_secondary'   => $this->cta_secondary,
			'kpis'            => $this->get_kpis(),
		);
	}
}
