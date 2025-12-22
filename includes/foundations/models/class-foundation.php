<?php
/**
 * Modèle Foundation
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Foundation
 *
 * Représente une fondation de marque liée à un client
 *
 * @since 2.0.0
 */
class BZMI_Foundation extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'foundations';

	/**
	 * Colonnes modifiables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'client_id',
		'name',
		'status',
		'completion_score',
		'identity_score',
		'offer_score',
		'experience_score',
		'execution_score',
		'metadata',
		'created_by',
	);

	/**
	 * Statuts disponibles
	 *
	 * @var array
	 */
	const STATUSES = array(
		'draft'       => 'Brouillon',
		'analysis'    => 'Analyse',
		'in_progress' => 'En cours',
		'review'      => 'En révision',
		'production'  => 'Production',
		'on_hold'     => 'En pause',
		'abandoned'   => 'Abandonné',
		'archived'    => 'Archivé',
	);

	/**
	 * Couleurs des statuts
	 *
	 * @var array
	 */
	const STATUS_COLORS = array(
		'draft'       => '#9ca3af',
		'analysis'    => '#3b82f6',
		'in_progress' => '#f59e0b',
		'review'      => '#8b5cf6',
		'production'  => '#10b981',
		'on_hold'     => '#eab308',
		'abandoned'   => '#ef4444',
		'archived'    => '#6b7280',
	);

	/**
	 * Sections du socle Identité
	 *
	 * @var array
	 */
	const IDENTITY_SECTIONS = array(
		'brand_dna'   => 'ADN de marque',
		'vision'      => 'Vision',
		'tone_voice'  => 'Ton & Voix',
		'visuals'     => 'Identité visuelle',
		'colors'      => 'Couleurs',
		'typography'  => 'Typographies',
	);

	/**
	 * Sections du socle Exécution
	 *
	 * @var array
	 */
	const EXECUTION_SECTIONS = array(
		'scope'        => 'Périmètre',
		'deliverables' => 'Livrables',
		'planning'     => 'Planning',
		'budget'       => 'Budget',
		'constraints'  => 'Contraintes techniques',
		'legal'        => 'RGPD & Légal',
	);

	/**
	 * Obtenir le client associé
	 *
	 * @return BZMI_Client|null
	 */
	public function get_client() {
		if ( ! $this->client_id ) {
			return null;
		}
		return BZMI_Client::find( $this->client_id );
	}

	/**
	 * Trouver une fondation par client (retourne la première)
	 *
	 * @deprecated Utiliser get_by_client() pour obtenir toutes les fondations
	 * @param int $client_id ID du client.
	 * @return BZMI_Foundation|null
	 */
	public static function find_by_client( $client_id ) {
		return static::first_where( array( 'client_id' => $client_id ) );
	}

	/**
	 * Obtenir toutes les fondations d'un client
	 *
	 * @param int   $client_id ID du client.
	 * @param array $args      Arguments supplémentaires (status, orderby, order).
	 * @return array
	 */
	public static function get_by_client( $client_id, $args = array() ) {
		$defaults = array(
			'where'   => array( 'client_id' => $client_id ),
			'orderby' => 'created_at',
			'order'   => 'DESC',
		);

		// Filtrer par statut si spécifié
		if ( ! empty( $args['status'] ) ) {
			$defaults['where']['status'] = $args['status'];
		}

		return static::all( array_merge( $defaults, $args ) );
	}

	/**
	 * Obtenir les fondations actives d'un client (en production ou en cours)
	 *
	 * @param int $client_id ID du client.
	 * @return array
	 */
	public static function get_active_by_client( $client_id ) {
		global $wpdb;

		$table = BZMI_Database::get_table_name( static::$table );
		$active_statuses = array( 'in_progress', 'review', 'production' );
		$placeholders = implode( ',', array_fill( 0, count( $active_statuses ), '%s' ) );

		$sql = $wpdb->prepare(
			"SELECT * FROM {$table} WHERE client_id = %d AND status IN ({$placeholders}) ORDER BY created_at DESC",
			array_merge( array( $client_id ), $active_statuses )
		);

		$rows = $wpdb->get_results( $sql, ARRAY_A );
		$models = array();

		foreach ( $rows as $row ) {
			$models[] = new static( $row );
		}

		return $models;
	}

	/**
	 * Créer une nouvelle fondation pour un client
	 *
	 * @param int    $client_id ID du client.
	 * @param string $name      Nom de la fondation.
	 * @param string $status    Statut initial.
	 * @return BZMI_Foundation
	 */
	public static function create_for_client( $client_id, $name = '', $status = 'draft' ) {
		$client = BZMI_Client::find( $client_id );

		if ( empty( $name ) ) {
			$count = static::count( array( 'client_id' => $client_id ) );
			$name = $client ? $client->name . ' - Fondation ' . ( $count + 1 ) : 'Nouvelle Fondation';
		}

		return static::create( array(
			'client_id'  => $client_id,
			'name'       => $name,
			'status'     => $status,
			'created_by' => get_current_user_id(),
		) );
	}

	/**
	 * Obtenir le libellé du statut
	 *
	 * @return string
	 */
	public function get_status_label() {
		return self::STATUSES[ $this->status ] ?? $this->status;
	}

	/**
	 * Obtenir la couleur du statut
	 *
	 * @return string
	 */
	public function get_status_color() {
		return self::STATUS_COLORS[ $this->status ] ?? '#9ca3af';
	}

	/**
	 * Vérifier si la fondation est active (utilisable)
	 *
	 * @return bool
	 */
	public function is_active() {
		return in_array( $this->status, array( 'in_progress', 'review', 'production' ), true );
	}

	/**
	 * Vérifier si la fondation est en production
	 *
	 * @return bool
	 */
	public function is_in_production() {
		return 'production' === $this->status;
	}

	/**
	 * Vérifier si la fondation est modifiable
	 *
	 * @return bool
	 */
	public function is_editable() {
		return ! in_array( $this->status, array( 'abandoned', 'archived' ), true );
	}

	/**
	 * Changer le statut de la fondation
	 *
	 * @param string $new_status Nouveau statut.
	 * @return bool
	 */
	public function change_status( $new_status ) {
		if ( ! isset( self::STATUSES[ $new_status ] ) ) {
			return false;
		}

		$this->status = $new_status;
		return $this->save();
	}

	/**
	 * Obtenir toutes les données d'identité
	 *
	 * @return array
	 */
	public function get_identity_data() {
		return BZMI_Foundation_Identity::where( array( 'foundation_id' => $this->id ) );
	}

	/**
	 * Obtenir une section d'identité
	 *
	 * @param string $section Nom de la section.
	 * @return BZMI_Foundation_Identity|null
	 */
	public function get_identity_section( $section ) {
		return BZMI_Foundation_Identity::first_where( array(
			'foundation_id' => $this->id,
			'section'       => $section,
		) );
	}

	/**
	 * Définir une section d'identité
	 *
	 * @param string $section Nom de la section.
	 * @param array  $content Contenu.
	 * @param string $status  Statut (hypothesis|validated).
	 * @return BZMI_Foundation_Identity
	 */
	public function set_identity_section( $section, $content, $status = 'hypothesis' ) {
		$identity = $this->get_identity_section( $section );

		if ( $identity ) {
			$identity->content = $content;
			$identity->status  = $status;
			if ( 'validated' === $status ) {
				$identity->version = ( $identity->version ?? 0 ) + 1;
			}
			$identity->save();
		} else {
			$identity = BZMI_Foundation_Identity::create( array(
				'foundation_id' => $this->id,
				'section'       => $section,
				'content'       => $content,
				'status'        => $status,
				'version'       => 1,
			) );
		}

		$this->recalculate_scores();
		return $identity;
	}

	/**
	 * Obtenir tous les personas
	 *
	 * @return array
	 */
	public function get_personas() {
		return BZMI_Foundation_Persona::all( array(
			'where'   => array( 'foundation_id' => $this->id ),
			'orderby' => 'priority',
			'order'   => 'ASC',
		) );
	}

	/**
	 * Obtenir toutes les offres
	 *
	 * @return array
	 */
	public function get_offers() {
		return BZMI_Foundation_Offer::all( array(
			'where'   => array( 'foundation_id' => $this->id ),
			'orderby' => 'priority',
			'order'   => 'ASC',
		) );
	}

	/**
	 * Obtenir tous les concurrents
	 *
	 * @return array
	 */
	public function get_competitors() {
		return BZMI_Foundation_Competitor::all( array(
			'where'   => array( 'foundation_id' => $this->id ),
			'orderby' => 'threat_level',
			'order'   => 'DESC',
		) );
	}

	/**
	 * Obtenir tous les parcours
	 *
	 * @return array
	 */
	public function get_journeys() {
		return BZMI_Foundation_Journey::where( array( 'foundation_id' => $this->id ) );
	}

	/**
	 * Obtenir tous les canaux
	 *
	 * @return array
	 */
	public function get_channels() {
		return BZMI_Foundation_Channel::all( array(
			'where'   => array( 'foundation_id' => $this->id ),
			'orderby' => 'priority',
			'order'   => 'ASC',
		) );
	}

	/**
	 * Obtenir toutes les données d'exécution
	 *
	 * @return array
	 */
	public function get_execution_data() {
		return BZMI_Foundation_Execution::where( array( 'foundation_id' => $this->id ) );
	}

	/**
	 * Obtenir une section d'exécution
	 *
	 * @param string $section Nom de la section.
	 * @return BZMI_Foundation_Execution|null
	 */
	public function get_execution_section( $section ) {
		return BZMI_Foundation_Execution::first_where( array(
			'foundation_id' => $this->id,
			'section'       => $section,
		) );
	}

	/**
	 * Définir une section d'exécution
	 *
	 * @param string $section Nom de la section.
	 * @param array  $content Contenu.
	 * @param string $status  Statut.
	 * @return BZMI_Foundation_Execution
	 */
	public function set_execution_section( $section, $content, $status = 'draft' ) {
		$execution = $this->get_execution_section( $section );

		if ( $execution ) {
			$execution->content = $content;
			$execution->status  = $status;
			$execution->save();
		} else {
			$execution = BZMI_Foundation_Execution::create( array(
				'foundation_id' => $this->id,
				'section'       => $section,
				'content'       => $content,
				'status'        => $status,
			) );
		}

		$this->recalculate_scores();
		return $execution;
	}

	/**
	 * Recalculer les scores de complétion
	 *
	 * @return void
	 */
	public function recalculate_scores() {
		// Score Identité
		$identity_count    = count( self::IDENTITY_SECTIONS );
		$identity_filled   = BZMI_Foundation_Identity::count( array( 'foundation_id' => $this->id ) );
		$personas_count    = BZMI_Foundation_Persona::count( array( 'foundation_id' => $this->id ) );
		$identity_score    = min( 100, round( ( ( $identity_filled / $identity_count ) * 70 ) + ( min( $personas_count, 3 ) / 3 * 30 ) ) );

		// Score Offre
		$offers_count      = BZMI_Foundation_Offer::count( array( 'foundation_id' => $this->id ) );
		$competitors_count = BZMI_Foundation_Competitor::count( array( 'foundation_id' => $this->id ) );
		$offer_score       = min( 100, ( min( $offers_count, 5 ) / 5 * 60 ) + ( min( $competitors_count, 3 ) / 3 * 40 ) );

		// Score Expérience
		$journeys_count  = BZMI_Foundation_Journey::count( array( 'foundation_id' => $this->id ) );
		$channels_count  = BZMI_Foundation_Channel::count( array( 'foundation_id' => $this->id ) );
		$experience_score = min( 100, ( min( $journeys_count, 3 ) / 3 * 50 ) + ( min( $channels_count, 5 ) / 5 * 50 ) );

		// Score Exécution
		$execution_count   = count( self::EXECUTION_SECTIONS );
		$execution_filled  = BZMI_Foundation_Execution::count( array( 'foundation_id' => $this->id ) );
		$execution_score   = round( ( $execution_filled / $execution_count ) * 100 );

		// Score global
		$completion_score = round( ( $identity_score + $offer_score + $experience_score + $execution_score ) / 4 );

		// Mise à jour
		$this->identity_score   = (int) $identity_score;
		$this->offer_score      = (int) $offer_score;
		$this->experience_score = (int) $experience_score;
		$this->execution_score  = (int) $execution_score;
		$this->completion_score = (int) $completion_score;
		$this->save();
	}

	/**
	 * Obtenir le contexte complet pour l'IA
	 *
	 * @return array
	 */
	public function get_ai_context() {
		$client = $this->get_client();

		return array(
			'foundation'  => $this->to_array(),
			'client'      => $client ? $client->to_array() : null,
			'company_mode' => $client ? $client->company_mode : 'existing',
			'identity'    => array(
				'sections' => array_map( function( $item ) {
					return $item->to_array();
				}, $this->get_identity_data() ),
				'personas' => array_map( function( $item ) {
					return $item->to_array();
				}, $this->get_personas() ),
			),
			'offer'       => array(
				'offers'      => array_map( function( $item ) {
					return $item->to_array();
				}, $this->get_offers() ),
				'competitors' => array_map( function( $item ) {
					return $item->to_array();
				}, $this->get_competitors() ),
			),
			'experience'  => array(
				'journeys' => array_map( function( $item ) {
					return $item->to_array();
				}, $this->get_journeys() ),
				'channels' => array_map( function( $item ) {
					return $item->to_array();
				}, $this->get_channels() ),
			),
			'execution'   => array_map( function( $item ) {
				return $item->to_array();
			}, $this->get_execution_data() ),
		);
	}

	/**
	 * Exporter la fondation complète
	 *
	 * @return array
	 */
	public function export() {
		return $this->get_ai_context();
	}

	/**
	 * Obtenir les projets utilisant cette fondation
	 *
	 * @return array
	 */
	public function get_linked_projects() {
		return BZMI_Project::where( array( 'foundation_id' => $this->id ) );
	}

	/**
	 * Supprimer la fondation et toutes ses données
	 *
	 * @return bool
	 */
	public function delete() {
		// Supprimer les données liées
		$tables = array(
			'foundation_identity',
			'foundation_personas',
			'foundation_offers',
			'foundation_competitors',
			'foundation_journeys',
			'foundation_channels',
			'foundation_execution',
			'foundation_ai_logs',
		);

		foreach ( $tables as $table ) {
			BZMI_Database::delete( $table, array( 'foundation_id' => $this->id ) );
		}

		return parent::delete();
	}
}
