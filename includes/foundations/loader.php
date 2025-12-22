<?php
/**
 * Blazing Minds - Chargeur du module Foundations
 *
 * Module de gestion des Fondations de marque
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Définir les constantes Foundations
if ( ! defined( 'BZMI_FOUNDATIONS_VERSION' ) ) {
	define( 'BZMI_FOUNDATIONS_VERSION', '1.0.0' );
}

/**
 * Charger les fichiers du module Foundations
 *
 * @since 2.0.0
 * @return void
 */
function bzmi_load_foundations_modules() {
	$foundations_dir = WPVFH_PLUGIN_DIR . 'includes/foundations/';

	// Database
	require_once $foundations_dir . 'database/class-foundations-migrations.php';

	// Models
	require_once $foundations_dir . 'models/class-foundation.php';
	require_once $foundations_dir . 'models/class-foundation-identity.php';
	require_once $foundations_dir . 'models/class-foundation-persona.php';
	require_once $foundations_dir . 'models/class-foundation-offer.php';
	require_once $foundations_dir . 'models/class-foundation-competitor.php';
	require_once $foundations_dir . 'models/class-foundation-journey.php';
	require_once $foundations_dir . 'models/class-foundation-channel.php';
	require_once $foundations_dir . 'models/class-foundation-execution.php';

	// API
	require_once $foundations_dir . 'api/class-rest-foundations.php';
	require_once $foundations_dir . 'api/class-foundations-ai.php';

	// Admin (seulement si admin)
	if ( is_admin() ) {
		require_once $foundations_dir . 'admin/class-admin-foundations.php';
		require_once $foundations_dir . 'admin/class-admin-foundations-identity.php';
		require_once $foundations_dir . 'admin/class-admin-foundations-offer.php';
		require_once $foundations_dir . 'admin/class-admin-foundations-experience.php';
		require_once $foundations_dir . 'admin/class-admin-foundations-execution.php';
	}
}

/**
 * Initialiser le module Foundations
 *
 * @since 2.0.0
 * @return void
 */
function bzmi_init_foundations() {
	// Charger les modules
	bzmi_load_foundations_modules();

	// Vérifier la version de la base de données
	$current_version = BZMI_Database::get_setting( 'foundations_db_version', '0.0.0' );
	if ( version_compare( $current_version, BZMI_Foundations_Migrations::VERSION, '<' ) ) {
		BZMI_Foundations_Migrations::run();
		BZMI_Foundations_Migrations::add_company_mode_to_clients();
		BZMI_Foundations_Migrations::add_foundation_to_projects();
	}
}

/**
 * Enregistrer les hooks du module Foundations
 *
 * @since 2.0.0
 * @return void
 */
function bzmi_register_foundations_hooks() {
	// REST API
	add_action( 'rest_api_init', array( 'BZMI_REST_Foundations', 'register_routes' ) );

	// Admin
	if ( is_admin() ) {
		add_action( 'admin_menu', array( 'BZMI_Admin_Foundations', 'register_menu' ), 21 );
		add_action( 'admin_enqueue_scripts', 'bzmi_enqueue_foundations_admin_assets' );
	}
}

/**
 * Charger les assets admin du module Foundations
 *
 * @since 2.0.0
 * @param string $hook Page admin actuelle.
 * @return void
 */
function bzmi_enqueue_foundations_admin_assets( $hook ) {
	// Seulement sur les pages Blazing Minds avec fondations
	if ( strpos( $hook, 'blazing-minds' ) === false && strpos( $hook, 'foundations' ) === false ) {
		return;
	}

	// Vérifier la page actuelle
	$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
	if ( strpos( $page, 'foundations' ) === false && strpos( $page, 'bzmi-' ) === false ) {
		return;
	}

	// CSS Foundations
	wp_enqueue_style(
		'bzmi-foundations',
		WPVFH_PLUGIN_URL . 'assets/css/foundations-admin.css',
		array( 'bzmi-admin' ),
		WPVFH_VERSION
	);

	// JS Foundations
	wp_enqueue_script(
		'bzmi-foundations',
		WPVFH_PLUGIN_URL . 'assets/js/foundations-admin.js',
		array( 'jquery', 'wp-api-fetch', 'bzmi-admin' ),
		WPVFH_VERSION,
		true
	);

	// Color picker
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	// Media library
	wp_enqueue_media();

	wp_localize_script( 'bzmi-foundations', 'bzmiFoundationsData', array(
		'restUrl'   => rest_url( 'blazing-minds/v1/foundations/' ),
		'apiBase'   => rest_url( 'blazing-minds/v1/foundations' ),
		'adminUrl'  => admin_url(),
		'nonce'     => wp_create_nonce( 'wp_rest' ),
		'strings'   => array(
			'confirm_delete'     => __( 'Êtes-vous sûr de vouloir supprimer cet élément ?', 'blazing-feedback' ),
			'loading'            => __( 'Chargement...', 'blazing-feedback' ),
			'saving'             => __( 'Enregistrement...', 'blazing-feedback' ),
			'saved'              => __( 'Enregistré !', 'blazing-feedback' ),
			'error'              => __( 'Une erreur est survenue.', 'blazing-feedback' ),
			'select_image'       => __( 'Sélectionner une image', 'blazing-feedback' ),
			'use_image'          => __( 'Utiliser cette image', 'blazing-feedback' ),
			'ai_generating'      => __( 'Génération IA en cours...', 'blazing-feedback' ),
			'ai_suggestion'      => __( 'Suggestion IA', 'blazing-feedback' ),
			'apply_suggestion'   => __( 'Appliquer', 'blazing-feedback' ),
			'dismiss_suggestion' => __( 'Ignorer', 'blazing-feedback' ),
		),
		'identity_sections'  => BZMI_Foundation::IDENTITY_SECTIONS,
		'execution_sections' => BZMI_Foundation::EXECUTION_SECTIONS,
		'channel_types'      => BZMI_Foundation_Channel::TYPES,
		'offer_types'        => BZMI_Foundation_Offer::TYPES,
		'pricing_models'     => BZMI_Foundation_Offer::PRICING_MODELS,
	) );
}

/**
 * Ajouter les capacités Foundations aux rôles
 *
 * @since 2.0.0
 * @return void
 */
function bzmi_create_foundations_capabilities() {
	$capabilities = array(
		'bzmi_manage_foundations' => true,
		'bzmi_edit_foundations'   => true,
		'bzmi_view_foundations'   => true,
		'bzmi_delete_foundations' => true,
		'bzmi_use_foundations_ai' => true,
	);

	$admin = get_role( 'administrator' );
	if ( $admin ) {
		foreach ( $capabilities as $cap => $grant ) {
			$admin->add_cap( $cap, $grant );
		}
	}
}

/**
 * Obtenir la fondation d'un client
 *
 * @since 2.0.0
 * @param int $client_id ID du client.
 * @return BZMI_Foundation|null
 */
function bzmi_get_client_foundation( $client_id ) {
	return BZMI_Foundation::first_where( array( 'client_id' => $client_id ) );
}

/**
 * Obtenir la fondation d'un projet
 *
 * @since 2.0.0
 * @param int $project_id ID du projet.
 * @return BZMI_Foundation|null
 */
function bzmi_get_project_foundation( $project_id ) {
	$project = BZMI_Project::find( $project_id );
	if ( ! $project || ! $project->foundation_id ) {
		return null;
	}
	return BZMI_Foundation::find( $project->foundation_id );
}

/**
 * Obtenir le contexte IA complet pour un projet
 *
 * @since 2.0.0
 * @param int $project_id ID du projet.
 * @return array|null
 */
function bzmi_get_project_ai_context( $project_id ) {
	$foundation = bzmi_get_project_foundation( $project_id );
	if ( ! $foundation ) {
		return null;
	}
	return $foundation->get_ai_context();
}

// Initialiser aux hooks appropriés
add_action( 'plugins_loaded', 'bzmi_init_foundations', 17 );
add_action( 'plugins_loaded', 'bzmi_register_foundations_hooks', 18 );

// Activation
add_action( 'wpvfh_plugin_activated', function() {
	bzmi_load_foundations_modules();
	BZMI_Foundations_Migrations::run();
	BZMI_Foundations_Migrations::add_company_mode_to_clients();
	BZMI_Foundations_Migrations::add_foundation_to_projects();
	bzmi_create_foundations_capabilities();
} );
