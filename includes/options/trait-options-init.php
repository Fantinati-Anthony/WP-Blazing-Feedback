<?php
/**
 * Trait pour l'initialisation, menu et scripts du gestionnaire d'options
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait pour l'initialisation du gestionnaire d'options
 *
 * @since 1.7.0
 */
trait WPVFH_Options_Init_Trait {

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
     * @since 2.1.0 Support du menu unifié bzmi-metadata
     * @param string $hook Page hook
     * @return void
     */
    public static function enqueue_admin_scripts( $hook ) {
        // Supporter les deux hooks : ancien (wpvfh-options) et nouveau (bzmi-metadata)
        $valid_hooks = array(
            'feedbacks_page_wpvfh-options',
            'toplevel_page_blazing-minds_page_bzmi-metadata',
            'blazing-minds_page_bzmi-metadata',
        );

        // Vérifier si le hook contient bzmi-metadata (plus flexible)
        $is_metadata_page = in_array( $hook, $valid_hooks, true ) || strpos( $hook, 'bzmi-metadata' ) !== false;

        if ( ! $is_metadata_page ) {
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
            'pageSlug'      => 'bzmi-metadata',
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
}
