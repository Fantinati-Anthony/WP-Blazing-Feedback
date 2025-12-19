<?php
/**
 * Initialisation, menu admin, scripts
 * 
 * Reference file for options-manager.php lines 1-135
 * See main file: includes/options-manager.php
 * 
 * @package Blazing_Feedback
 */

// To view this section, read options-manager.php with:
// offset=1, limit=135

<?php
/**
 * Gestionnaire des métadonnées personnalisables (Types, Priorités, Tags, Statuts)
 *
 * @package WP_Visual_Feedback_Hub
 * @since 1.1.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe de gestion des options
 *
 * @since 1.1.0
 */
class WPVFH_Options_Manager {

    /**
     * Clés des options
     */
    const OPTION_TYPES         = 'wpvfh_feedback_types';
    const OPTION_PRIORITIES    = 'wpvfh_feedback_priorities';
    const OPTION_TAGS          = 'wpvfh_feedback_tags';
    const OPTION_STATUSES      = 'wpvfh_feedback_statuses';
    const OPTION_CUSTOM_GROUPS = 'wpvfh_custom_option_groups';
    const OPTION_GROUP_SETTINGS = 'wpvfh_group_settings';

    /**
     * Groupes par défaut (non supprimables)
     */
    private static $default_groups = array( 'statuses', 'types', 'priorities', 'tags' );

    /**
     * Initialiser le gestionnaire
     *
     * @since 1.1.0
     * @return void
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
        add_action( 'admin_init', array( __CLASS__, 'handle_actions' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
        add_action( 'wp_ajax_wpvfh_save_options_order', array( __CLASS__, 'ajax_save_order' ) );
        add_action( 'wp_ajax_wpvfh_save_option_item', array( __CLASS__, 'ajax_save_item' ) );
        add_action( 'wp_ajax_wpvfh_delete_option_item', array( __CLASS__, 'ajax_delete_item' ) );
        add_action( 'wp_ajax_wpvfh_search_users_roles', array( __CLASS__, 'ajax_search_users_roles' ) );
        add_action( 'wp_ajax_wpvfh_create_custom_group', array( __CLASS__, 'ajax_create_custom_group' ) );
        add_action( 'wp_ajax_wpvfh_delete_custom_group', array( __CLASS__, 'ajax_delete_custom_group' ) );
        add_action( 'wp_ajax_wpvfh_rename_custom_group', array( __CLASS__, 'ajax_rename_custom_group' ) );
        add_action( 'wp_ajax_wpvfh_save_group_settings', array( __CLASS__, 'ajax_save_group_settings' ) );
    }

    /**
     * Ajouter le menu d'administration
     *
     * @since 1.1.0
     * @return void
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'wpvfh-dashboard',
            __( 'Métadatas', 'blazing-feedback' ),
            __( 'Métadatas', 'blazing-feedback' ),
            'manage_feedback',
            'wpvfh-options',
            array( __CLASS__, 'render_options_page' )
        );
    }

    /**
     * Charger les scripts admin
     *
     * @since 1.1.0
     * @param string $hook Page hook
     * @return void
     */
    public static function enqueue_admin_scripts( $hook ) {
        if ( 'feedbacks_page_wpvfh-options' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'jquery-ui-sortable' );

        wp_enqueue_style(
            'wpvfh-options-admin',
            WPVFH_PLUGIN_URL . 'assets/css/admin-options.css',
            array(),
            WPVFH_VERSION
        );

        wp_enqueue_script(
            'wpvfh-options-admin',
            WPVFH_PLUGIN_URL . 'assets/js/admin-options.js',
            array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ),
            WPVFH_VERSION,
            true
        );

        // Obtenir les rôles disponibles
        $roles = wp_roles()->get_names();

        wp_localize_script( 'wpvfh-options-admin', 'wpvfhOptionsAdmin', array(
            'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
            'adminUrl'      => admin_url( 'admin.php' ),
            'nonce'         => wp_create_nonce( 'wpvfh_options_nonce' ),
            'roles'         => $roles,
            'defaultGroups' => self::$default_groups,
            'i18n'          => array(
                'confirmDelete'      => __( 'Êtes-vous sûr de vouloir supprimer cette métadata ?', 'blazing-feedback' ),
                'confirmDeleteGroup' => __( 'Êtes-vous sûr de vouloir supprimer ce groupe et toutes ses métadatas ?', 'blazing-feedback' ),
                'saving'             => __( 'Enregistrement...', 'blazing-feedback' ),
                'saved'              => __( 'Enregistré !', 'blazing-feedback' ),
                'error'              => __( 'Erreur lors de l\'enregistrement', 'blazing-feedback' ),
                'searchPlaceholder'  => __( 'Rechercher un utilisateur ou rôle...', 'blazing-feedback' ),
                'noResults'          => __( 'Aucun résultat', 'blazing-feedback' ),
                'allAllowed'         => __( 'Tous autorisés (vide)', 'blazing-feedback' ),
                'newGroupName'       => __( 'Nom du nouveau groupe', 'blazing-feedback' ),
                'groupCreated'       => __( 'Groupe créé avec succès', 'blazing-feedback' ),
                'groupDeleted'       => __( 'Groupe supprimé', 'blazing-feedback' ),
            ),
        ) );
    }

    /**
     * Créer un élément par défaut avec tous les champs
     *
     * @since 1.2.0
     * @param array $base Données de base
     * @return array
     */