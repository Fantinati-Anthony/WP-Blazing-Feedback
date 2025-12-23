<?php
/**
 * Migrations de base de données pour les Fondations
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Foundations_Migrations
 *
 * Gère les migrations de la base de données pour les Fondations de marque
 *
 * @since 2.0.0
 */
class BZMI_Foundations_Migrations {

	/**
	 * Version actuelle des tables Foundations
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Exécuter toutes les migrations
	 *
	 * @return void
	 */
	public static function run() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// Table principale des fondations
		self::create_foundations_table( $charset_collate );

		// Socle Identité
		self::create_identity_table( $charset_collate );
		self::create_personas_table( $charset_collate );

		// Socle Offre & Marché
		self::create_offers_table( $charset_collate );
		self::create_competitors_table( $charset_collate );

		// Socle Expérience
		self::create_journeys_table( $charset_collate );
		self::create_channels_table( $charset_collate );

		// Socle Exécution
		self::create_execution_table( $charset_collate );

		// Table des logs IA
		self::create_ai_logs_table( $charset_collate );

		// Mettre à jour la version
		BZMI_Database::update_setting( 'foundations_db_version', self::VERSION );
	}

	/**
	 * Créer la table principale des fondations
	 *
	 * Plusieurs fondations peuvent être liées à un même client
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_foundations_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'foundations' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			client_id bigint(20) unsigned NOT NULL,
			name varchar(255) NOT NULL,
			status varchar(50) DEFAULT 'draft',
			completion_score int DEFAULT 0,
			identity_score int DEFAULT 0,
			offer_score int DEFAULT 0,
			experience_score int DEFAULT 0,
			execution_score int DEFAULT 0,
			metadata longtext,
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY client_id (client_id),
			KEY status (status),
			KEY completion_score (completion_score)
		) {$charset_collate};";

		dbDelta( $sql );

		// Supprimer l'ancienne contrainte UNIQUE si elle existe (migration depuis v2.0)
		self::drop_unique_client_constraint();
	}

	/**
	 * Supprimer la contrainte UNIQUE sur client_id pour permettre plusieurs fondations par client
	 *
	 * @since 2.1.0
	 * @return void
	 */
	private static function drop_unique_client_constraint() {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'foundations' );

		// Vérifier si la contrainte UNIQUE existe
		$indexes = $wpdb->get_results( "SHOW INDEX FROM {$table_name} WHERE Key_name = 'client_id' AND Non_unique = 0" );

		if ( ! empty( $indexes ) ) {
			// Supprimer l'index UNIQUE
			$wpdb->query( "ALTER TABLE {$table_name} DROP INDEX client_id" );
			// Recréer en tant qu'index simple
			$wpdb->query( "ALTER TABLE {$table_name} ADD INDEX client_id (client_id)" );
		}
	}

	/**
	 * Créer la table du socle Identité
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_identity_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'foundation_identity' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			foundation_id bigint(20) unsigned NOT NULL,
			section varchar(50) NOT NULL,
			content longtext,
			status varchar(50) DEFAULT 'hypothesis',
			version int DEFAULT 1,
			ai_suggestions longtext,
			validated_at datetime,
			validated_by bigint(20) unsigned,
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY foundation_section (foundation_id, section),
			KEY foundation_id (foundation_id),
			KEY section (section),
			KEY status (status)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des personas
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_personas_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'foundation_personas' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			foundation_id bigint(20) unsigned NOT NULL,
			name varchar(255) NOT NULL,
			avatar_url varchar(500),
			age_range varchar(50),
			job_title varchar(255),
			description text,
			goals longtext,
			pain_points longtext,
			behaviors longtext,
			preferred_channels longtext,
			quote text,
			priority int DEFAULT 0,
			status varchar(50) DEFAULT 'draft',
			metadata longtext,
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY foundation_id (foundation_id),
			KEY status (status),
			KEY priority (priority)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des offres
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_offers_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'foundation_offers' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			foundation_id bigint(20) unsigned NOT NULL,
			name varchar(255) NOT NULL,
			type varchar(50) DEFAULT 'product',
			description text,
			value_proposition text,
			target_personas longtext,
			features longtext,
			benefits longtext,
			pricing_model varchar(100),
			price_range varchar(100),
			differentiation text,
			status varchar(50) DEFAULT 'active',
			priority int DEFAULT 0,
			metadata longtext,
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY foundation_id (foundation_id),
			KEY type (type),
			KEY status (status),
			KEY priority (priority)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des concurrents
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_competitors_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'foundation_competitors' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			foundation_id bigint(20) unsigned NOT NULL,
			name varchar(255) NOT NULL,
			website varchar(500),
			description text,
			type varchar(50) DEFAULT 'direct',
			strengths longtext,
			weaknesses longtext,
			market_position varchar(100),
			pricing_level varchar(50),
			threat_level int DEFAULT 5,
			notes text,
			metadata longtext,
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY foundation_id (foundation_id),
			KEY type (type),
			KEY threat_level (threat_level)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des parcours utilisateurs
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_journeys_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'foundation_journeys' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			foundation_id bigint(20) unsigned NOT NULL,
			name varchar(255) NOT NULL,
			persona_id bigint(20) unsigned,
			description text,
			objective varchar(500),
			stages longtext,
			touchpoints longtext,
			emotions longtext,
			pain_points longtext,
			opportunities longtext,
			status varchar(50) DEFAULT 'draft',
			metadata longtext,
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY foundation_id (foundation_id),
			KEY persona_id (persona_id),
			KEY status (status)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des canaux
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_channels_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'foundation_channels' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			foundation_id bigint(20) unsigned NOT NULL,
			name varchar(255) NOT NULL,
			type varchar(50) NOT NULL,
			platform varchar(100),
			url varchar(500),
			description text,
			objectives longtext,
			target_personas longtext,
			key_messages longtext,
			tone_guidelines text,
			cta_primary varchar(255),
			cta_secondary varchar(255),
			kpis longtext,
			priority int DEFAULT 0,
			status varchar(50) DEFAULT 'active',
			metadata longtext,
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY foundation_id (foundation_id),
			KEY type (type),
			KEY status (status),
			KEY priority (priority)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table du socle Exécution
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_execution_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'foundation_execution' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			foundation_id bigint(20) unsigned NOT NULL,
			section varchar(50) NOT NULL,
			content longtext,
			status varchar(50) DEFAULT 'draft',
			validated_at datetime,
			validated_by bigint(20) unsigned,
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY foundation_section (foundation_id, section),
			KEY foundation_id (foundation_id),
			KEY section (section),
			KEY status (status)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Créer la table des logs IA pour les fondations
	 *
	 * @param string $charset_collate Charset de la base.
	 * @return void
	 */
	private static function create_ai_logs_table( $charset_collate ) {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'foundation_ai_logs' );

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			foundation_id bigint(20) unsigned NOT NULL,
			socle varchar(50) NOT NULL,
			action varchar(50) NOT NULL,
			target_type varchar(50),
			target_id bigint(20) unsigned,
			input longtext,
			output longtext,
			confidence_score decimal(3,2),
			tokens_used int,
			applied tinyint(1) DEFAULT 0,
			applied_at datetime,
			applied_by bigint(20) unsigned,
			error_message text,
			created_by bigint(20) unsigned,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY foundation_id (foundation_id),
			KEY socle (socle),
			KEY action (action),
			KEY applied (applied),
			KEY created_at (created_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Ajouter la colonne company_mode à la table clients
	 *
	 * @return void
	 */
	public static function add_company_mode_to_clients() {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'clients' );

		// Vérifier si la colonne existe déjà
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'company_mode'",
				DB_NAME,
				$table_name
			)
		);

		if ( empty( $column_exists ) ) {
			$wpdb->query(
				"ALTER TABLE {$table_name} ADD COLUMN company_mode varchar(50) DEFAULT 'existing' AFTER status"
			);
		}
	}

	/**
	 * Ajouter la colonne foundation_id à la table projects
	 *
	 * @return void
	 */
	public static function add_foundation_to_projects() {
		global $wpdb;

		$table_name = BZMI_Database::get_table_name( 'projects' );

		// Vérifier si la colonne existe déjà
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'foundation_id'",
				DB_NAME,
				$table_name
			)
		);

		if ( empty( $column_exists ) ) {
			$wpdb->query(
				"ALTER TABLE {$table_name} ADD COLUMN foundation_id bigint(20) unsigned DEFAULT NULL AFTER portfolio_id"
			);
			$wpdb->query(
				"ALTER TABLE {$table_name} ADD KEY foundation_id (foundation_id)"
			);
		}
	}

	/**
	 * Supprimer toutes les tables des fondations
	 *
	 * @return void
	 */
	public static function drop_all_tables() {
		global $wpdb;

		$tables = array(
			'foundation_ai_logs',
			'foundation_execution',
			'foundation_channels',
			'foundation_journeys',
			'foundation_competitors',
			'foundation_offers',
			'foundation_personas',
			'foundation_identity',
			'foundations',
		);

		foreach ( $tables as $table ) {
			$table_name = BZMI_Database::get_table_name( $table );
			$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
		}
	}
}
