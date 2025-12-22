<?php
/**
 * Modèle Project
 *
 * P = PROJET dans CPPICAVAL
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Project
 *
 * @since 1.0.0
 */
class BZMI_Project extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'projects';

	/**
	 * Colonnes fillables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'portfolio_id',
		'foundation_id',
		'name',
		'description',
		'start_date',
		'end_date',
		'budget',
		'color',
		'priority',
		'progress',
		'metadata',
		'status',
		'created_by',
	);

	/**
	 * Statuts disponibles
	 *
	 * @return array
	 */
	public static function get_statuses() {
		return array(
			'pending'     => __( 'En attente', 'blazing-minds' ),
			'active'      => __( 'Actif', 'blazing-minds' ),
			'in_progress' => __( 'En cours', 'blazing-minds' ),
			'on_hold'     => __( 'En pause', 'blazing-minds' ),
			'completed'   => __( 'Terminé', 'blazing-minds' ),
			'cancelled'   => __( 'Annulé', 'blazing-minds' ),
		);
	}

	/**
	 * Priorités disponibles
	 *
	 * @return array
	 */
	public static function get_priorities() {
		return array(
			0 => __( 'Basse', 'blazing-minds' ),
			1 => __( 'Normale', 'blazing-minds' ),
			2 => __( 'Haute', 'blazing-minds' ),
			3 => __( 'Urgente', 'blazing-minds' ),
		);
	}

	/**
	 * Obtenir le portefeuille
	 *
	 * @return BZMI_Portfolio|null
	 */
	public function portfolio() {
		return BZMI_Portfolio::find( $this->portfolio_id );
	}

	/**
	 * Obtenir le client (via le portefeuille)
	 *
	 * @return BZMI_Client|null
	 */
	public function client() {
		$portfolio = $this->portfolio();
		return $portfolio ? $portfolio->client() : null;
	}

	/**
	 * Obtenir les informations du projet
	 *
	 * @return array
	 */
	public function informations() {
		return BZMI_Information::where( array( 'project_id' => $this->id ) );
	}

	/**
	 * Obtenir le nombre d'informations
	 *
	 * @return int
	 */
	public function informations_count() {
		return BZMI_Information::count( array( 'project_id' => $this->id ) );
	}

	/**
	 * Obtenir les utilisateurs assignés
	 *
	 * @return array
	 */
	public function users() {
		global $wpdb;

		$table = BZMI_Database::get_table_name( 'project_users' );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, role, permissions FROM {$table} WHERE project_id = %d",
				$this->id
			),
			ARRAY_A
		);

		$users = array();
		foreach ( $rows as $row ) {
			$user = get_user_by( 'id', $row['user_id'] );
			if ( $user ) {
				$users[] = array(
					'user'        => $user,
					'role'        => $row['role'],
					'permissions' => maybe_unserialize( $row['permissions'] ),
				);
			}
		}

		return $users;
	}

	/**
	 * Ajouter un utilisateur au projet
	 *
	 * @param int    $user_id     ID de l'utilisateur.
	 * @param string $role        Rôle.
	 * @param array  $permissions Permissions.
	 * @return bool
	 */
	public function add_user( $user_id, $role = 'member', $permissions = array() ) {
		global $wpdb;

		$table = BZMI_Database::get_table_name( 'project_users' );

		// Vérifier si déjà assigné
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE project_id = %d AND user_id = %d",
				$this->id,
				$user_id
			)
		);

		if ( $exists ) {
			// Mettre à jour
			return false !== $wpdb->update(
				$table,
				array(
					'role'        => $role,
					'permissions' => maybe_serialize( $permissions ),
				),
				array(
					'project_id' => $this->id,
					'user_id'    => $user_id,
				)
			);
		}

		// Insérer
		return false !== $wpdb->insert(
			$table,
			array(
				'project_id'  => $this->id,
				'user_id'     => $user_id,
				'role'        => $role,
				'permissions' => maybe_serialize( $permissions ),
				'added_at'    => current_time( 'mysql' ),
				'added_by'    => get_current_user_id(),
			)
		);
	}

	/**
	 * Retirer un utilisateur du projet
	 *
	 * @param int $user_id ID de l'utilisateur.
	 * @return bool
	 */
	public function remove_user( $user_id ) {
		global $wpdb;

		$table = BZMI_Database::get_table_name( 'project_users' );

		return false !== $wpdb->delete(
			$table,
			array(
				'project_id' => $this->id,
				'user_id'    => $user_id,
			)
		);
	}

	/**
	 * Vérifier si un utilisateur est assigné
	 *
	 * @param int $user_id ID de l'utilisateur.
	 * @return bool
	 */
	public function has_user( $user_id ) {
		global $wpdb;

		$table = BZMI_Database::get_table_name( 'project_users' );

		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE project_id = %d AND user_id = %d",
				$this->id,
				$user_id
			)
		);
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
	 * Valider les données
	 *
	 * @return array Erreurs de validation.
	 */
	public function validate() {
		$errors = array();

		if ( empty( $this->name ) ) {
			$errors['name'] = __( 'Le nom est requis.', 'blazing-minds' );
		}

		if ( empty( $this->portfolio_id ) ) {
			$errors['portfolio_id'] = __( 'Le portefeuille est requis.', 'blazing-minds' );
		} elseif ( ! BZMI_Portfolio::find( $this->portfolio_id ) ) {
			$errors['portfolio_id'] = __( 'Le portefeuille sélectionné n\'existe pas.', 'blazing-minds' );
		}

		if ( ! empty( $this->start_date ) && ! empty( $this->end_date ) ) {
			if ( strtotime( $this->end_date ) < strtotime( $this->start_date ) ) {
				$errors['end_date'] = __( 'La date de fin doit être après la date de début.', 'blazing-minds' );
			}
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
	 * Calculer la progression automatiquement
	 *
	 * @return int
	 */
	public function calculate_progress() {
		$informations = $this->informations();

		if ( empty( $informations ) ) {
			return 0;
		}

		$stages = array(
			'information'   => 0,
			'clarification' => 20,
			'action'        => 40,
			'value'         => 60,
			'apprenticeship' => 80,
			'completed'     => 100,
		);

		$total_progress = 0;
		foreach ( $informations as $info ) {
			$stage = $info->icaval_stage;
			$total_progress += isset( $stages[ $stage ] ) ? $stages[ $stage ] : 0;
		}

		return (int) round( $total_progress / count( $informations ) );
	}

	/**
	 * Mettre à jour la progression
	 *
	 * @return bool
	 */
	public function update_progress() {
		$this->progress = $this->calculate_progress();
		return $this->save();
	}

	/**
	 * Obtenir les statistiques ICAVAL du projet
	 *
	 * @return array
	 */
	public function get_icaval_stats() {
		$informations = $this->informations();

		$stats = array(
			'information'    => 0,
			'clarification'  => 0,
			'action'         => 0,
			'value'          => 0,
			'apprenticeship' => 0,
			'completed'      => 0,
		);

		foreach ( $informations as $info ) {
			if ( isset( $stats[ $info->icaval_stage ] ) ) {
				$stats[ $info->icaval_stage ]++;
			}
		}

		return $stats;
	}

	/**
	 * Obtenir les projets par portefeuille
	 *
	 * @param int $portfolio_id ID du portefeuille.
	 * @return array
	 */
	public static function by_portfolio( $portfolio_id ) {
		return static::where( array( 'portfolio_id' => $portfolio_id ) );
	}

	/**
	 * Obtenir les projets actifs
	 *
	 * @return array
	 */
	public static function active() {
		return static::where( array( 'status' => array( 'active', 'in_progress' ) ) );
	}

	/**
	 * Obtenir la fondation associée au projet
	 *
	 * @since 2.0.0
	 * @return BZMI_Foundation|null
	 */
	public function foundation() {
		if ( ! class_exists( 'BZMI_Foundation' ) ) {
			return null;
		}

		// D'abord vérifier si une fondation est directement liée
		if ( $this->foundation_id ) {
			return BZMI_Foundation::find( $this->foundation_id );
		}

		// Sinon, chercher via le client
		$client = $this->client();
		if ( $client ) {
			return $client->foundation();
		}

		return null;
	}

	/**
	 * Vérifier si le projet a une fondation
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function has_foundation() {
		return null !== $this->foundation();
	}

	/**
	 * Obtenir le contexte IA pour ce projet
	 *
	 * Combine les informations de la fondation avec les données du projet
	 * pour fournir un contexte riche à l'IA.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_ai_context() {
		$context = array(
			'project' => array(
				'id'          => $this->id,
				'name'        => $this->name,
				'description' => $this->description,
				'status'      => $this->status,
				'priority'    => $this->priority,
				'progress'    => $this->progress,
				'budget'      => $this->budget,
				'start_date'  => $this->start_date,
				'end_date'    => $this->end_date,
			),
		);

		// Ajouter le contexte client
		$client = $this->client();
		if ( $client ) {
			$context['client'] = array(
				'id'           => $client->id,
				'name'         => $client->name,
				'company'      => $client->company,
				'company_mode' => $client->company_mode,
			);
		}

		// Ajouter le contexte de la fondation
		$foundation = $this->foundation();
		if ( $foundation ) {
			$context['foundation'] = $foundation->get_ai_context();
		}

		// Ajouter les statistiques ICAVAL
		$context['icaval'] = $this->get_icaval_stats();

		return $context;
	}

	/**
	 * Lier une fondation au projet
	 *
	 * @since 2.0.0
	 * @param int $foundation_id ID de la fondation.
	 * @return bool
	 */
	public function link_foundation( $foundation_id ) {
		$this->foundation_id = $foundation_id;
		return $this->save();
	}

	/**
	 * Délier la fondation du projet
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function unlink_foundation() {
		$this->foundation_id = null;
		return $this->save();
	}
}
