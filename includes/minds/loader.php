<?php
/**
 * Blazing Minds - Chargeur de modules CPPICAVAL
 *
 * Intègre les fonctionnalités de gestion de projet dans Blazing Feedback
 *
 * @package Blazing_Feedback
 * @subpackage Minds
 * @since 1.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Définir les constantes Minds
if ( ! defined( 'BZMI_VERSION' ) ) {
	define( 'BZMI_VERSION', '1.0.0' );
}
if ( ! defined( 'BZMI_DB_VERSION' ) ) {
	define( 'BZMI_DB_VERSION', '1.0.0' );
}
if ( ! defined( 'BZMI_PLUGIN_DIR' ) ) {
	define( 'BZMI_PLUGIN_DIR', WPVFH_PLUGIN_DIR );
}
if ( ! defined( 'BZMI_PLUGIN_URL' ) ) {
	define( 'BZMI_PLUGIN_URL', WPVFH_PLUGIN_URL );
}

/**
 * Charger les modules Blazing Minds
 *
 * @since 1.11.0
 * @return void
 */
function wpvfh_load_minds_modules() {
	$minds_dir = WPVFH_PLUGIN_DIR . 'includes/minds/';

	// Database
	require_once $minds_dir . 'database/class-database.php';
	require_once $minds_dir . 'database/class-migrations.php';

	// Models
	require_once $minds_dir . 'models/class-model-base.php';
	require_once $minds_dir . 'models/class-client.php';
	require_once $minds_dir . 'models/class-portfolio.php';
	require_once $minds_dir . 'models/class-project.php';
	require_once $minds_dir . 'models/class-information.php';
	require_once $minds_dir . 'models/class-clarification.php';
	require_once $minds_dir . 'models/class-action.php';
	require_once $minds_dir . 'models/class-value.php';
	require_once $minds_dir . 'models/class-apprenticeship.php';

	// API
	require_once $minds_dir . 'api/class-rest-api.php';
	require_once $minds_dir . 'api/class-ai-config.php';

	// Admin (seulement si admin)
	if ( is_admin() ) {
		require_once $minds_dir . 'admin/class-admin.php';
		require_once $minds_dir . 'admin/class-admin-clients.php';
		require_once $minds_dir . 'admin/class-admin-portfolios.php';
		require_once $minds_dir . 'admin/class-admin-projects.php';
		require_once $minds_dir . 'admin/class-admin-icaval.php';
		require_once $minds_dir . 'admin/class-admin-settings.php';
	}

	// Charger le module Foundations
	$foundations_loader = WPVFH_PLUGIN_DIR . 'includes/foundations/loader.php';
	if ( file_exists( $foundations_loader ) ) {
		require_once $foundations_loader;
	}
}

/**
 * Initialiser Blazing Minds
 *
 * @since 1.11.0
 * @return void
 */
function wpvfh_init_minds() {
	// Charger les modules
	wpvfh_load_minds_modules();

	// Vérifier la version de la base de données
	$current_version = BZMI_Database::get_setting( 'db_version', '0.0.0' );
	if ( version_compare( $current_version, BZMI_DB_VERSION, '<' ) ) {
		BZMI_Migrations::run();
	}

	// Note: Le module Foundations s'initialise automatiquement via ses propres hooks plugins_loaded
}

/**
 * Enregistrer les hooks Minds
 *
 * @since 1.11.0
 * @return void
 */
function wpvfh_register_minds_hooks() {
	// REST API
	add_action( 'rest_api_init', array( 'BZMI_REST_API', 'register_routes' ) );

	// Admin
	if ( is_admin() ) {
		add_action( 'admin_menu', array( 'BZMI_Admin', 'register_menu' ), 20 );
		add_action( 'admin_enqueue_scripts', 'wpvfh_enqueue_minds_admin_assets' );
		add_action( 'admin_init', array( 'BZMI_Admin_Settings', 'register_settings' ) );
	}

	// Hook pour créer une Information depuis un feedback
	add_action( 'wpvfh_feedback_saved', 'wpvfh_create_information_from_feedback', 10, 2 );
}

/**
 * Charger les assets admin Minds
 *
 * @since 1.11.0
 * @param string $hook Page admin actuelle.
 * @return void
 */
function wpvfh_enqueue_minds_admin_assets( $hook ) {
	// Seulement sur les pages Blazing Minds
	if ( strpos( $hook, 'blazing-minds' ) === false ) {
		return;
	}

	wp_enqueue_style(
		'bzmi-admin',
		WPVFH_PLUGIN_URL . 'assets/css/minds-admin.css',
		array(),
		WPVFH_VERSION
	);

	wp_enqueue_script(
		'bzmi-admin',
		WPVFH_PLUGIN_URL . 'assets/js/minds-admin.js',
		array( 'jquery', 'wp-api-fetch' ),
		WPVFH_VERSION,
		true
	);

	wp_localize_script( 'bzmi-admin', 'bzmiData', array(
		'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		'restUrl'   => rest_url( 'blazing-minds/v1/' ),
		'nonce'     => wp_create_nonce( 'bzmi_nonce' ),
		'restNonce' => wp_create_nonce( 'wp_rest' ),
		'strings'   => array(
			'confirm_delete' => __( 'Êtes-vous sûr de vouloir supprimer cet élément ?', 'blazing-feedback' ),
			'loading'        => __( 'Chargement...', 'blazing-feedback' ),
			'error'          => __( 'Une erreur est survenue.', 'blazing-feedback' ),
			'saved'          => __( 'Enregistré !', 'blazing-feedback' ),
		),
	) );
}

/**
 * Créer une Information depuis un feedback sauvegardé
 *
 * @since 1.11.0
 * @param int   $feedback_id   ID du feedback.
 * @param array $feedback_data Données du feedback.
 * @return void
 */
function wpvfh_create_information_from_feedback( $feedback_id, $feedback_data ) {
	// Vérifier si la synchronisation est activée
	if ( ! BZMI_Database::get_setting( 'blazing_feedback_sync', true ) ) {
		return;
	}

	if ( ! BZMI_Database::get_setting( 'blazing_feedback_auto_import', true ) ) {
		return;
	}

	// Obtenir le projet par défaut
	$project_id = BZMI_Database::get_setting( 'blazing_feedback_default_project', 0 );
	if ( ! $project_id ) {
		return;
	}

	// Vérifier que le projet existe
	$project = BZMI_Project::find( $project_id );
	if ( ! $project ) {
		return;
	}

	// Créer l'information
	$info = BZMI_Information::create_from_feedback( $feedback_data, $project_id );

	if ( $info ) {
		/**
		 * Action après l'import d'un feedback comme Information
		 *
		 * @since 1.11.0
		 * @param BZMI_Information $info         L'information créée.
		 * @param array            $feedback_data Les données du feedback.
		 */
		do_action( 'wpvfh_feedback_imported_as_information', $info, $feedback_data );

		// Déclencher l'IA si activée
		wpvfh_maybe_generate_ai_clarifications( $info );
	}
}

/**
 * Générer des clarifications IA si activé
 *
 * @since 1.11.0
 * @param BZMI_Information $info L'information.
 * @return void
 */
function wpvfh_maybe_generate_ai_clarifications( $info ) {
	$ai = bzmi_ai();

	if ( ! $ai->is_feature_enabled( 'auto_clarify' ) ) {
		return;
	}

	// Planifier la génération asynchrone
	if ( function_exists( 'wp_schedule_single_event' ) ) {
		wp_schedule_single_event(
			time() + 5,
			'wpvfh_generate_ai_clarifications',
			array( $info->id )
		);
	}
}

// Hook pour génération IA asynchrone
add_action( 'wpvfh_generate_ai_clarifications', function( $information_id ) {
	$info = BZMI_Information::find( $information_id );
	if ( ! $info ) {
		return;
	}

	$ai = bzmi_ai();
	$questions = $ai->generate_clarifications( $info );

	if ( is_wp_error( $questions ) || empty( $questions ) ) {
		return;
	}

	foreach ( $questions as $question ) {
		BZMI_Clarification::create_ai_suggestion( $info->id, $question, 0.85 );
	}

	if ( 'information' === $info->icaval_stage ) {
		$info->icaval_stage = 'clarification';
		$info->save();
	}
} );

/**
 * Créer les rôles et capacités Minds
 *
 * @since 1.11.0
 * @return void
 */
function wpvfh_create_minds_roles() {
	$capabilities = array(
		'bzmi_manage_clients'      => true,
		'bzmi_manage_portfolios'   => true,
		'bzmi_manage_projects'     => true,
		'bzmi_manage_informations' => true,
		'bzmi_manage_icaval'       => true,
		'bzmi_manage_settings'     => true,
		'bzmi_view_reports'        => true,
		'bzmi_use_ai'              => true,
		// Fondations (v2.0.0)
		'bzmi_manage_foundations'  => true,
		'bzmi_edit_foundations'    => true,
		'bzmi_delete_foundations'  => true,
	);

	$admin = get_role( 'administrator' );
	if ( $admin ) {
		foreach ( $capabilities as $cap => $grant ) {
			$admin->add_cap( $cap, $grant );
		}
	}
}

// Initialiser à plugins_loaded
add_action( 'plugins_loaded', 'wpvfh_init_minds', 15 );
add_action( 'plugins_loaded', 'wpvfh_register_minds_hooks', 16 );

// Activation
register_activation_hook( WPVFH_PLUGIN_DIR . 'blazing-feedback.php', function() {
	wpvfh_load_minds_modules();
	BZMI_Migrations::run();
	BZMI_Database::set_default_settings();
	wpvfh_create_minds_roles();

	// Migrations Foundations (v2.0.0)
	if ( class_exists( 'BZMI_Foundations_Migrations' ) ) {
		BZMI_Foundations_Migrations::run();
	}
} );
