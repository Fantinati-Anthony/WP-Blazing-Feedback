<?php
/**
 * Modèle Client
 *
 * C = CLIENT dans CPPICAVAL
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Client
 *
 * @since 1.0.0
 */
class BZMI_Client extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'clients';

	/**
	 * Colonnes fillables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'name',
		'email',
		'phone',
		'company',
		'address',
		'website',
		'notes',
		'metadata',
		'status',
		'company_mode',
		'created_by',
	);

	/**
	 * Modes d'entreprise disponibles
	 *
	 * @since 2.0.0
	 */
	const COMPANY_MODES = array(
		'creation' => 'Création',
		'existing' => 'Existante',
	);

	/**
	 * Statuts disponibles
	 *
	 * @return array
	 */
	public static function get_statuses() {
		return array(
			'active'   => __( 'Actif', 'blazing-minds' ),
			'inactive' => __( 'Inactif', 'blazing-minds' ),
			'prospect' => __( 'Prospect', 'blazing-minds' ),
			'archived' => __( 'Archivé', 'blazing-minds' ),
		);
	}

	/**
	 * Obtenir les portefeuilles du client
	 *
	 * @return array
	 */
	public function portfolios() {
		return BZMI_Portfolio::where( array( 'client_id' => $this->id ) );
	}

	/**
	 * Obtenir le nombre de portefeuilles
	 *
	 * @return int
	 */
	public function portfolios_count() {
		return BZMI_Portfolio::count( array( 'client_id' => $this->id ) );
	}

	/**
	 * Obtenir tous les projets du client (via ses portefeuilles)
	 *
	 * @return array
	 */
	public function projects() {
		$portfolios = $this->portfolios();
		$projects   = array();

		foreach ( $portfolios as $portfolio ) {
			$projects = array_merge( $projects, $portfolio->projects() );
		}

		return $projects;
	}

	/**
	 * Obtenir le nombre total de projets
	 *
	 * @return int
	 */
	public function projects_count() {
		$portfolios = $this->portfolios();
		$count      = 0;

		foreach ( $portfolios as $portfolio ) {
			$count += $portfolio->projects_count();
		}

		return $count;
	}

	/**
	 * Obtenir les métadonnées décodées
	 *
	 * @return array
	 */
	public function get_metadata() {
		$metadata = $this->metadata;
		if ( is_string( $metadata ) ) {
			$metadata = maybe_unserialize( $metadata );
		}
		return is_array( $metadata ) ? $metadata : array();
	}

	/**
	 * Définir une métadonnée
	 *
	 * @param string $key   Clé.
	 * @param mixed  $value Valeur.
	 * @return $this
	 */
	public function set_metadata( $key, $value ) {
		$metadata         = $this->get_metadata();
		$metadata[ $key ] = $value;
		$this->metadata   = $metadata;
		return $this;
	}

	/**
	 * Obtenir le créateur
	 *
	 * @return WP_User|null
	 */
	public function creator() {
		if ( $this->created_by ) {
			return get_user_by( 'id', $this->created_by );
		}
		return null;
	}

	/**
	 * Valider les données
	 *
	 * @return array Erreurs de validation.
	 */
	public function validate() {
		$errors = array();

		if ( empty( $this->name ) ) {
			$errors['name'] = __( 'Le nom est requis.', 'blazing-minds' );
		}

		if ( ! empty( $this->email ) && ! is_email( $this->email ) ) {
			$errors['email'] = __( 'L\'adresse email n\'est pas valide.', 'blazing-minds' );
		}

		if ( ! empty( $this->website ) && ! filter_var( $this->website, FILTER_VALIDATE_URL ) ) {
			$errors['website'] = __( 'L\'URL du site web n\'est pas valide.', 'blazing-minds' );
		}

		return $errors;
	}

	/**
	 * Sauvegarder avec validation
	 *
	 * @return bool|int|array False, ID, ou array d'erreurs.
	 */
	public function save_validated() {
		$errors = $this->validate();

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return $this->save();
	}

	/**
	 * Rechercher des clients
	 *
	 * @param string $search Terme de recherche.
	 * @param array  $args   Arguments supplémentaires.
	 * @return array
	 */
	public static function search( $search, $args = array() ) {
		global $wpdb;

		$table = BZMI_Database::get_table_name( static::$table );
		$like  = '%' . $wpdb->esc_like( $search ) . '%';

		$sql = $wpdb->prepare(
			"SELECT * FROM {$table} WHERE name LIKE %s OR email LIKE %s OR company LIKE %s ORDER BY name ASC",
			$like,
			$like,
			$like
		);

		$rows   = $wpdb->get_results( $sql, ARRAY_A );
		$models = array();

		foreach ( $rows as $row ) {
			$models[] = new static( $row );
		}

		return $models;
	}

	/**
	 * Obtenir les statistiques du client
	 *
	 * @return array
	 */
	public function get_stats() {
		$portfolios = $this->portfolios();
		$projects   = $this->projects();

		$active_projects   = 0;
		$completed_projects = 0;
		$total_budget      = 0;

		foreach ( $projects as $project ) {
			if ( 'completed' === $project->status ) {
				$completed_projects++;
			} elseif ( 'active' === $project->status || 'in_progress' === $project->status ) {
				$active_projects++;
			}
			$total_budget += (float) $project->budget;
		}

		// Statistiques des fondations
		$foundations_count      = $this->foundations_count();
		$active_foundations     = count( $this->active_foundations() );
		$production_foundations = count( $this->foundations( array( 'status' => 'production' ) ) );

		return array(
			'portfolios_count'       => count( $portfolios ),
			'projects_count'         => count( $projects ),
			'active_projects'        => $active_projects,
			'completed_projects'     => $completed_projects,
			'total_budget'           => $total_budget,
			'foundations_count'      => $foundations_count,
			'active_foundations'     => $active_foundations,
			'production_foundations' => $production_foundations,
		);
	}

	/**
	 * Obtenir toutes les fondations du client
	 *
	 * @since 2.0.0
	 * @param array $args Arguments optionnels (status, orderby, order, limit).
	 * @return array Array de BZMI_Foundation
	 */
	public function foundations( $args = array() ) {
		if ( ! class_exists( 'BZMI_Foundation' ) ) {
			return array();
		}
		return BZMI_Foundation::get_by_client( $this->id, $args );
	}

	/**
	 * Obtenir les fondations actives du client
	 *
	 * @since 2.0.0
	 * @return array Array de BZMI_Foundation avec statuts actifs
	 */
	public function active_foundations() {
		if ( ! class_exists( 'BZMI_Foundation' ) ) {
			return array();
		}
		return BZMI_Foundation::get_active_by_client( $this->id );
	}

	/**
	 * Obtenir la fondation en production du client
	 *
	 * @since 2.0.0
	 * @return BZMI_Foundation|null La première fondation en production
	 */
	public function production_foundation() {
		$foundations = $this->foundations( array( 'status' => 'production' ) );
		return ! empty( $foundations ) ? $foundations[0] : null;
	}

	/**
	 * Obtenir une fondation par son ID
	 *
	 * @since 2.0.0
	 * @param int $foundation_id ID de la fondation.
	 * @return BZMI_Foundation|null
	 */
	public function get_foundation( $foundation_id ) {
		if ( ! class_exists( 'BZMI_Foundation' ) ) {
			return null;
		}
		$foundation = BZMI_Foundation::find( $foundation_id );
		if ( $foundation && (int) $foundation->client_id === (int) $this->id ) {
			return $foundation;
		}
		return null;
	}

	/**
	 * Créer une nouvelle fondation pour le client
	 *
	 * @since 2.0.0
	 * @param string $name   Nom de la fondation.
	 * @param string $status Statut initial (default: draft).
	 * @return BZMI_Foundation|null
	 */
	public function create_foundation( $name = '', $status = 'draft' ) {
		if ( ! class_exists( 'BZMI_Foundation' ) ) {
			return null;
		}
		if ( empty( $name ) ) {
			$count = count( $this->foundations() );
			$name  = sprintf( '%s - Fondation %d', $this->name, $count + 1 );
		}
		return BZMI_Foundation::create_for_client( $this->id, $name, $status );
	}

	/**
	 * Vérifier si le client a des fondations
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function has_foundations() {
		return count( $this->foundations() ) > 0;
	}

	/**
	 * Obtenir le nombre de fondations
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function foundations_count() {
		if ( ! class_exists( 'BZMI_Foundation' ) ) {
			return 0;
		}
		return BZMI_Foundation::count( array( 'client_id' => $this->id ) );
	}

	/**
	 * Obtenir le nombre de fondations par statut
	 *
	 * @since 2.0.0
	 * @return array Tableau associatif status => count
	 */
	public function foundations_count_by_status() {
		$counts     = array();
		$foundations = $this->foundations();

		foreach ( $foundations as $foundation ) {
			$status = $foundation->status ?: 'draft';
			if ( ! isset( $counts[ $status ] ) ) {
				$counts[ $status ] = 0;
			}
			$counts[ $status ]++;
		}

		return $counts;
	}

	/**
	 * Obtenir le mode d'entreprise formaté
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_company_mode_label() {
		$mode = $this->company_mode ?: 'existing';
		return self::COMPANY_MODES[ $mode ] ?? $mode;
	}

	/**
	 * Vérifier si le client est en mode création
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_creation_mode() {
		return 'creation' === $this->company_mode;
	}
}
