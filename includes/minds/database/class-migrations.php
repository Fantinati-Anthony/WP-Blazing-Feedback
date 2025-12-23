<?php
/**
 * Migrations de base de données
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Migrations
 *
 * Gère les migrations de la base de données CPPICAVAL
 *
 * @since 1.0.0
 */
class BZMI_Migrations {

	/**
	 * Exécuter toutes les migrations
	 *
	 * @return void
	 */
	public static function run() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// Table des paramètres
		self::create_settings_table( $charset_collate );

		// Tables CPPICAVAL - Structure hiérarchique
		self::create_clients_table( $charset_collate );
		self::create_portfolios_table( $charset_collate );
		self::create_projects_table( $charset_collate );

		// Tables CPPICAVAL - Cycle ICAVAL
		self::create_informations_table( $charset_collate );
		self::create_clarifications_table( $charset_collate );
		self::create_actions_table( $charset_collate );
		self::create_values_table( $charset_collate );
		self::create_apprenticeships_table( $charset_collate );

		// Tables relationnelles
		self::create_project_users_table( $charset_collate );
		self::create_attachments_table( $charset_collate );
		self::create_activity_log_table( $charset_collate );

		// Migrations de mise à jour
		self::migrate_add_company_mode_column();

		// Mettre à jour la version
		BZMI_Database::update_setting( 'db_version', BZMI_DB_VERSION );
	}

	/**
	 * Ajouter la colonne company_mode à la table clients si elle n'existe pas
	 *
	 * @since 2.1.0
	 * @return void
	 */
	private static function migrate_add_company_mode_column() {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'clients' );

		// Vérifier si la colonne existe déjà
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				'company_mode'
			)
		);

		if ( empty( $column_exists ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN company_mode varchar(50) DEFAULT 'existing' AFTER company" );
			$wpdb->query( "ALTER TABLE {$table_name} ADD INDEX company_mode (company_mode)" );
		}
	}

	/**
	 * Créer la table des paramètres
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_settings_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'settings' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			setting_key varchar(191) NOT NULL,
			setting_value longtext,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY setting_key (setting_key)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des clients
	 *
	 * C = CLIENT dans CPPICAVAL
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_clients_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'clients' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			email varchar(255),
			phone varchar(50),
			company varchar(255),
			company_mode varchar(50) DEFAULT 'existing',
			address text,
			website varchar(255),
			notes text,
			metadata longtext,
			status varchar(50) DEFAULT 'active',
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY status (status),
			KEY created_by (created_by),
			KEY name (name(191)),
			KEY company_mode (company_mode)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des portefeuilles
	 *
	 * P = PORTEFEUILLE dans CPPICAVAL
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_portfolios_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'portfolios' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			client_id bigint(20) unsigned NOT NULL,
			name varchar(255) NOT NULL,
			description text,
			color varchar(7) DEFAULT '#3498db',
			icon varchar(50),
			priority int DEFAULT 0,
			metadata longtext,
			status varchar(50) DEFAULT 'active',
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY client_id (client_id),
			KEY status (status),
			KEY priority (priority)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des projets
	 *
	 * P = PROJET dans CPPICAVAL
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_projects_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'projects' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			portfolio_id bigint(20) unsigned NOT NULL,
			name varchar(255) NOT NULL,
			description text,
			start_date date,
			end_date date,
			budget decimal(15,2),
			color varchar(7) DEFAULT '#2ecc71',
			priority int DEFAULT 0,
			progress int DEFAULT 0,
			metadata longtext,
			status varchar(50) DEFAULT 'pending',
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY portfolio_id (portfolio_id),
			KEY status (status),
			KEY priority (priority),
			KEY start_date (start_date),
			KEY end_date (end_date)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des informations
	 *
	 * I = INFORMATION dans ICAVAL
	 * Les feedbacks de Blazing Feedback arrivent ici
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_informations_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'informations' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			project_id bigint(20) unsigned NOT NULL,
			type varchar(50) NOT NULL DEFAULT 'feedback',
			source varchar(100) DEFAULT 'manual',
			source_id varchar(100),
			title varchar(255) NOT NULL,
			content longtext,
			priority varchar(20) DEFAULT 'normal',
			category varchar(100),
			tags text,
			metadata longtext,
			status varchar(50) DEFAULT 'new',
			icaval_stage varchar(20) DEFAULT 'information',
			assigned_to bigint(20) unsigned,
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY project_id (project_id),
			KEY type (type),
			KEY source (source),
			KEY source_id (source_id),
			KEY status (status),
			KEY icaval_stage (icaval_stage),
			KEY priority (priority),
			KEY assigned_to (assigned_to)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des clarifications
	 *
	 * C = CLARIFICATION dans ICAVAL
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_clarifications_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'clarifications' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			information_id bigint(20) unsigned NOT NULL,
			question text NOT NULL,
			answer text,
			ai_suggested tinyint(1) DEFAULT 0,
			ai_confidence decimal(3,2),
			resolved tinyint(1) DEFAULT 0,
			resolved_by bigint(20) unsigned,
			resolved_at datetime,
			metadata longtext,
			status varchar(50) DEFAULT 'pending',
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY information_id (information_id),
			KEY status (status),
			KEY resolved (resolved),
			KEY ai_suggested (ai_suggested)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des actions
	 *
	 * A = ACTION dans ICAVAL
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_actions_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'actions' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			information_id bigint(20) unsigned NOT NULL,
			title varchar(255) NOT NULL,
			description text,
			action_type varchar(50) DEFAULT 'task',
			priority varchar(20) DEFAULT 'normal',
			effort_estimate varchar(50),
			due_date datetime,
			completed_at datetime,
			ai_suggested tinyint(1) DEFAULT 0,
			ai_confidence decimal(3,2),
			metadata longtext,
			status varchar(50) DEFAULT 'pending',
			assigned_to bigint(20) unsigned,
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY information_id (information_id),
			KEY status (status),
			KEY priority (priority),
			KEY due_date (due_date),
			KEY assigned_to (assigned_to),
			KEY action_type (action_type)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des valeurs
	 *
	 * V = VALEUR dans ICAVAL
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_values_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'values' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			action_id bigint(20) unsigned NOT NULL,
			value_type varchar(50) NOT NULL DEFAULT 'business',
			title varchar(255) NOT NULL,
			description text,
			impact_score int DEFAULT 0,
			monetary_value decimal(15,2),
			time_saved int,
			satisfaction_increase int,
			metrics longtext,
			ai_calculated tinyint(1) DEFAULT 0,
			validated tinyint(1) DEFAULT 0,
			validated_by bigint(20) unsigned,
			validated_at datetime,
			metadata longtext,
			status varchar(50) DEFAULT 'estimated',
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY action_id (action_id),
			KEY value_type (value_type),
			KEY status (status),
			KEY validated (validated),
			KEY impact_score (impact_score)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des apprentissages
	 *
	 * AL = APPRENTISSAGE dans ICAVAL
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_apprenticeships_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'apprenticeships' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			source_type varchar(50) NOT NULL,
			source_id bigint(20) unsigned NOT NULL,
			lesson_type varchar(50) DEFAULT 'insight',
			title varchar(255) NOT NULL,
			description text,
			context text,
			recommendations text,
			applies_to text,
			reusable tinyint(1) DEFAULT 1,
			ai_generated tinyint(1) DEFAULT 0,
			confidence_score decimal(3,2),
			usage_count int DEFAULT 0,
			last_used_at datetime,
			tags text,
			metadata longtext,
			status varchar(50) DEFAULT 'active',
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY source_type (source_type),
			KEY source_id (source_id),
			KEY lesson_type (lesson_type),
			KEY status (status),
			KEY reusable (reusable),
			KEY usage_count (usage_count)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table de liaison projets-utilisateurs
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_project_users_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'project_users' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			project_id bigint(20) unsigned NOT NULL,
			user_id bigint(20) unsigned NOT NULL,
			role varchar(50) DEFAULT 'member',
			permissions text,
			added_at datetime DEFAULT CURRENT_TIMESTAMP,
			added_by bigint(20) unsigned,
			PRIMARY KEY (id),
			UNIQUE KEY project_user (project_id, user_id),
			KEY project_id (project_id),
			KEY user_id (user_id),
			KEY role (role)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des pièces jointes
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_attachments_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'attachments' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			parent_type varchar(50) NOT NULL,
			parent_id bigint(20) unsigned NOT NULL,
			file_name varchar(255) NOT NULL,
			file_type varchar(100),
			file_size bigint(20) unsigned,
			file_path text NOT NULL,
			attachment_id bigint(20) unsigned,
			metadata longtext,
			uploaded_by bigint(20) unsigned,
			uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY parent_type (parent_type),
			KEY parent_id (parent_id),
			KEY attachment_id (attachment_id)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table du journal d'activité
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_activity_log_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'activity_log' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			object_type varchar(50) NOT NULL,
			object_id bigint(20) unsigned NOT NULL,
			action varchar(50) NOT NULL,
			description text,
			old_value longtext,
			new_value longtext,
			ip_address varchar(45),
			user_agent text,
			user_id bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY object_type (object_type),
			KEY object_id (object_id),
			KEY action (action),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Supprimer toutes les tables du plugin
	 *
	 * @return void
	 */
	public static function drop_all_tables() {
		global $wpdb;

		$tables = array(
			'activity_log',
			'attachments',
			'project_users',
			'apprenticeships',
			'values',
			'actions',
			'clarifications',
			'informations',
			'projects',
			'portfolios',
			'clients',
			'settings',
		);

		foreach ( $tables as $table ) {
			$table_name = BZMI_Database::get_table_name( $table );
			$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
		}
	}
}
