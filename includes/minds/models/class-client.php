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

		return array(
			'portfolios_count'    => count( $portfolios ),
			'projects_count'      => count( $projects ),
			'active_projects'     => $active_projects,
			'completed_projects'  => $completed_projects,
			'total_budget'        => $total_budget,
		);
	}

	/**
	 * Obtenir la fondation du client
	 *
	 * @since 2.0.0
	 * @return BZMI_Foundation|null
	 */
	public function foundation() {
		if ( ! class_exists( 'BZMI_Foundation' ) ) {
			return null;
		}
		return BZMI_Foundation::find_by_client( $this->id );
	}

	/**
	 * Vérifier si le client a une fondation
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function has_foundation() {
		return null !== $this->foundation();
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
