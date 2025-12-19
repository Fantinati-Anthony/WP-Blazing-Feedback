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
    private static function create_default_item( $base ) {
        return array_merge( array(
            'id'            => '',
            'label'         => '',
            'emoji'         => '📌',
            'color'         => '#666666',
            'display_mode'  => 'emoji', // 'emoji' ou 'color_dot'
            'enabled'       => true,
            'is_treated'    => false, // Considéré comme traité (pour les statuts)
            'ai_prompt'     => '',
            'allowed_roles' => array(), // vide = tous autorisés
            'allowed_users' => array(), // vide = tous autorisés
        ), $base );
    }

    /**
     * Obtenir les types de feedback par défaut
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_default_types() {
        return array(
            self::create_default_item( array(
                'id'    => 'bug',
                'label' => __( 'Bug', 'blazing-feedback' ),
                'emoji' => '🐛',
                'color' => '#e74c3c',
            ) ),
            self::create_default_item( array(
                'id'    => 'improvement',
                'label' => __( 'Amélioration', 'blazing-feedback' ),
                'emoji' => '💡',
                'color' => '#f39c12',
            ) ),
            self::create_default_item( array(
                'id'    => 'question',
                'label' => __( 'Question', 'blazing-feedback' ),
                'emoji' => '❓',
                'color' => '#3498db',
            ) ),
            self::create_default_item( array(
                'id'    => 'design',
                'label' => __( 'Design', 'blazing-feedback' ),
                'emoji' => '🎨',
                'color' => '#9b59b6',
            ) ),
            self::create_default_item( array(
                'id'    => 'content',
                'label' => __( 'Contenu', 'blazing-feedback' ),
                'emoji' => '📝',
                'color' => '#1abc9c',
            ) ),
            self::create_default_item( array(
                'id'    => 'other',
                'label' => __( 'Autre', 'blazing-feedback' ),
                'emoji' => '📌',
                'color' => '#95a5a6',
            ) ),
        );
    }

    /**
     * Obtenir les priorités par défaut
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_default_priorities() {
        return array(
            self::create_default_item( array(
                'id'    => 'none',
                'label' => __( 'Aucune', 'blazing-feedback' ),
                'emoji' => '⚪',
                'color' => '#bdc3c7',
            ) ),
            self::create_default_item( array(
                'id'    => 'low',
                'label' => __( 'Basse', 'blazing-feedback' ),
                'emoji' => '🟢',
                'color' => '#27ae60',
            ) ),
            self::create_default_item( array(
                'id'    => 'medium',
                'label' => __( 'Moyenne', 'blazing-feedback' ),
                'emoji' => '🟠',
                'color' => '#f39c12',
            ) ),
            self::create_default_item( array(
                'id'    => 'high',
                'label' => __( 'Haute', 'blazing-feedback' ),
                'emoji' => '🔴',
                'color' => '#e74c3c',
            ) ),
        );
    }

    /**
     * Obtenir les tags par défaut
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_default_tags() {
        return array(
            self::create_default_item( array(
                'id'           => 'urgent',
                'label'        => __( 'Urgent', 'blazing-feedback' ),
                'emoji'        => '🚨',
                'color'        => '#e74c3c',
                'display_mode' => 'color_dot',
            ) ),
            self::create_default_item( array(
                'id'           => 'frontend',
                'label'        => __( 'Frontend', 'blazing-feedback' ),
                'emoji'        => '🖥️',
                'color'        => '#3498db',
                'display_mode' => 'color_dot',
            ) ),
            self::create_default_item( array(
                'id'           => 'backend',
                'label'        => __( 'Backend', 'blazing-feedback' ),
                'emoji'        => '⚙️',
                'color'        => '#9b59b6',
                'display_mode' => 'color_dot',
            ) ),
            self::create_default_item( array(
                'id'           => 'mobile',
                'label'        => __( 'Mobile', 'blazing-feedback' ),
                'emoji'        => '📱',
                'color'        => '#1abc9c',
                'display_mode' => 'color_dot',
            ) ),
        );
    }

    /**
     * Obtenir les statuts par défaut
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_default_statuses() {
        return array(
            self::create_default_item( array(
                'id'    => 'new',
                'label' => __( 'Nouveau', 'blazing-feedback' ),
                'emoji' => '🆕',
                'color' => '#3498db',
            ) ),
            self::create_default_item( array(
                'id'    => 'in_progress',
                'label' => __( 'En cours', 'blazing-feedback' ),
                'emoji' => '🔄',
                'color' => '#f39c12',
            ) ),
            self::create_default_item( array(
                'id'         => 'resolved',
                'label'      => __( 'Résolu', 'blazing-feedback' ),
                'emoji'      => '✅',
                'color'      => '#27ae60',
                'is_treated' => true,
            ) ),
            self::create_default_item( array(
                'id'         => 'rejected',
                'label'      => __( 'Rejeté', 'blazing-feedback' ),
                'emoji'      => '❌',
                'color'      => '#e74c3c',
                'is_treated' => true,
            ) ),
        );
    }

    /**
     * Normaliser un élément avec les champs par défaut
     *
     * @since 1.2.0
     * @param array $item Élément à normaliser
     * @return array
     */
    private static function normalize_item( $item ) {
        $defaults = array(
            'id'            => '',
            'label'         => '',
            'emoji'         => '📌',
            'color'         => '#666666',
            'display_mode'  => 'emoji',
            'enabled'       => true,
            'is_treated'    => false,
            'ai_prompt'     => '',
            'allowed_roles' => array(),
            'allowed_users' => array(),
        );
        return array_merge( $defaults, $item );
    }

    /**
     * Convertir un objet de base de données en tableau d'item
     *
     * @since 1.7.0
     * @param object $db_item Objet de la base de données
     * @return array
     */
    private static function db_item_to_array( $db_item ) {
        return array(
            'id'            => $db_item->slug,
            'label'         => $db_item->label,
            'emoji'         => $db_item->emoji ?: '📌',
            'color'         => $db_item->color ?: '#666666',
            'display_mode'  => $db_item->display_mode ?: 'emoji',
            'enabled'       => (bool) $db_item->enabled,
            'is_treated'    => isset( $db_item->is_treated ) ? (bool) $db_item->is_treated : false,
            'ai_prompt'     => $db_item->ai_prompt ?: '',
            'allowed_roles' => is_array( $db_item->allowed_roles ) ? $db_item->allowed_roles : array(),
            'allowed_users' => is_array( $db_item->allowed_users ) ? $db_item->allowed_users : array(),
            'db_id'         => (int) $db_item->id,
            'sort_order'    => (int) $db_item->sort_order,
        );
    }

    /**
     * Convertir un tableau d'item en données pour la base de données
     *
     * @since 1.7.0
     * @param array  $item       Tableau d'item
     * @param string $type_group Groupe de type
     * @param int    $sort_order Ordre de tri
     * @return array
     */
    private static function array_to_db_item( $item, $type_group, $sort_order = 0 ) {
        return array(
            'type_group'    => $type_group,
            'slug'          => isset( $item['id'] ) ? $item['id'] : sanitize_title( $item['label'] ),
            'label'         => isset( $item['label'] ) ? $item['label'] : '',
            'emoji'         => isset( $item['emoji'] ) ? $item['emoji'] : '📌',
            'color'         => isset( $item['color'] ) ? $item['color'] : '#666666',
            'display_mode'  => isset( $item['display_mode'] ) ? $item['display_mode'] : 'emoji',
            'sort_order'    => $sort_order,
            'enabled'       => isset( $item['enabled'] ) ? (int) $item['enabled'] : 1,
            'is_treated'    => isset( $item['is_treated'] ) ? (int) $item['is_treated'] : 0,
            'ai_prompt'     => isset( $item['ai_prompt'] ) ? $item['ai_prompt'] : '',
            'allowed_roles' => isset( $item['allowed_roles'] ) ? $item['allowed_roles'] : array(),
            'allowed_users' => isset( $item['allowed_users'] ) ? $item['allowed_users'] : array(),
        );
    }

    /**
     * Obtenir les types de feedback
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_types() {
        $db_items = WPVFH_Database::get_metadata_by_type( 'types' );

        if ( empty( $db_items ) ) {
            // Insérer les valeurs par défaut dans la base de données
            $defaults = self::get_default_types();
            self::save_types( $defaults );
            return $defaults;
        }

        return array_map( array( __CLASS__, 'db_item_to_array' ), $db_items );
    }

    /**
     * Obtenir les priorités
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_priorities() {
        $db_items = WPVFH_Database::get_metadata_by_type( 'priorities' );

        if ( empty( $db_items ) ) {
            $defaults = self::get_default_priorities();
            self::save_priorities( $defaults );
            return $defaults;
        }

        return array_map( array( __CLASS__, 'db_item_to_array' ), $db_items );
    }

    /**
     * Obtenir les tags prédéfinis
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_predefined_tags() {
        $db_items = WPVFH_Database::get_metadata_by_type( 'tags' );

        if ( empty( $db_items ) ) {
            $defaults = self::get_default_tags();
            self::save_tags( $defaults );
            return $defaults;
        }

        return array_map( array( __CLASS__, 'db_item_to_array' ), $db_items );
    }

    /**
     * Sauvegarder les métadonnées d'un type
     *
     * @since 1.7.0
     * @param string $type_group Groupe de type
     * @param array  $items      Items à sauvegarder
     * @return bool
     */
    private static function save_metadata_items( $type_group, $items ) {
        // Récupérer les items existants pour déterminer les mises à jour/insertions/suppressions
        $existing = WPVFH_Database::get_metadata_by_type( $type_group );
        $existing_by_slug = array();
        foreach ( $existing as $item ) {
            $existing_by_slug[ $item->slug ] = $item;
        }

        $processed_slugs = array();
        $sort_order = 0;

        foreach ( $items as $item ) {
            $slug = isset( $item['id'] ) ? $item['id'] : sanitize_title( $item['label'] );
            $processed_slugs[] = $slug;
            $db_data = self::array_to_db_item( $item, $type_group, $sort_order );

            if ( isset( $existing_by_slug[ $slug ] ) ) {
                // Mise à jour
                WPVFH_Database::update_metadata_item( $existing_by_slug[ $slug ]->id, $db_data );
            } else {
                // Insertion
                WPVFH_Database::insert_metadata_item( $db_data );
            }

            $sort_order++;
        }

        // Supprimer les items qui ne sont plus dans la liste
        foreach ( $existing_by_slug as $slug => $item ) {
            if ( ! in_array( $slug, $processed_slugs, true ) ) {
                WPVFH_Database::delete_metadata_item( $item->id );
            }
        }

        return true;
    }

    /**
     * Sauvegarder les types
     *
     * @since 1.1.0
     * @param array $types Types à sauvegarder
     * @return bool
     */
    public static function save_types( $types ) {
        return self::save_metadata_items( 'types', $types );
    }

    /**
     * Sauvegarder les priorités
     *
     * @since 1.1.0
     * @param array $priorities Priorités à sauvegarder
     * @return bool
     */
    public static function save_priorities( $priorities ) {
        return self::save_metadata_items( 'priorities', $priorities );
    }

    /**
     * Sauvegarder les tags
     *
     * @since 1.1.0
     * @param array $tags Tags à sauvegarder
     * @return bool
     */
    public static function save_tags( $tags ) {
        return self::save_metadata_items( 'tags', $tags );
    }

    /**
     * Obtenir les statuts
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_statuses() {
        $db_items = WPVFH_Database::get_metadata_by_type( 'statuses' );

        if ( empty( $db_items ) ) {
            $defaults = self::get_default_statuses();
            self::save_statuses( $defaults );
            return $defaults;
        }

        return array_map( array( __CLASS__, 'db_item_to_array' ), $db_items );
    }

    /**
     * Sauvegarder les statuts
     *
     * @since 1.1.0
     * @param array $statuses Statuts à sauvegarder
     * @return bool
     */
    public static function save_statuses( $statuses ) {
        return self::save_metadata_items( 'statuses', $statuses );
    }

    /**
     * Obtenir les groupes personnalisés
     *
     * @since 1.3.0
     * @return array
     */
    public static function get_custom_groups() {
        $db_groups = WPVFH_Database::get_custom_groups();

        $groups = array();
        foreach ( $db_groups as $group ) {
            $groups[ $group->slug ] = array(
                'slug'    => $group->slug,
                'name'    => $group->name,
                'created' => strtotime( $group->created_at ),
                'db_id'   => (int) $group->id,
            );
        }

        return $groups;
    }

    /**
     * Sauvegarder les groupes personnalisés
     *
     * @since 1.3.0
     * @param array $groups Groupes à sauvegarder
     * @return bool
     */
    public static function save_custom_groups( $groups ) {
        // Cette méthode est maintenant principalement utilisée pour la compatibilité
        // Les vraies sauvegardes se font via create_custom_group et delete_custom_group
        return true;
    }

    /**
     * Créer un nouveau groupe personnalisé
     *
     * @since 1.3.0
     * @param string $name Nom du groupe
     * @return array|false Le groupe créé ou false si échec
     */
    public static function create_custom_group( $name ) {
        $groups = self::get_custom_groups();

        $slug = sanitize_title( $name );
        $base_slug = $slug;
        $counter = 1;

        // S'assurer que le slug est unique
        while ( isset( $groups[ $slug ] ) || in_array( $slug, self::$default_groups, true ) ) {
            $slug = $base_slug . '_' . $counter;
            $counter++;
        }

        // Calculer l'ordre de tri
        $sort_order = count( $groups );

        // Insérer dans la base de données
        $group_id = WPVFH_Database::insert_custom_group( array(
            'slug'       => $slug,
            'name'       => $name,
            'sort_order' => $sort_order,
        ) );

        if ( ! $group_id ) {
            return false;
        }

        return array(
            'slug'    => $slug,
            'name'    => $name,
            'created' => time(),
            'db_id'   => $group_id,
        );
    }

    /**
     * Supprimer un groupe personnalisé
     *
     * @since 1.3.0
     * @param string $slug Slug du groupe
     * @return bool
     */
    public static function delete_custom_group( $slug ) {
        // Ne pas permettre la suppression des groupes par défaut
        if ( in_array( $slug, self::$default_groups, true ) ) {
            return false;
        }

        return WPVFH_Database::delete_custom_group( $slug );
    }

    /**
     * Obtenir les items d'un groupe personnalisé
     *
     * @since 1.3.0
     * @param string $slug Slug du groupe
     * @return array
     */
    public static function get_custom_group_items( $slug ) {
        $group = WPVFH_Database::get_custom_group( $slug );
        if ( ! $group ) {
            return array();
        }

        $db_items = WPVFH_Database::get_custom_group_items( $group->id );

        return array_map( function( $item ) {
            return array(
                'id'            => $item->slug,
                'label'         => $item->label,
                'emoji'         => $item->emoji ?: '📌',
                'color'         => $item->color ?: '#666666',
                'display_mode'  => $item->display_mode ?: 'emoji',
                'enabled'       => (bool) $item->enabled,
                'ai_prompt'     => $item->ai_prompt ?: '',
                'allowed_roles' => is_array( $item->allowed_roles ) ? $item->allowed_roles : array(),
                'allowed_users' => is_array( $item->allowed_users ) ? $item->allowed_users : array(),
                'db_id'         => (int) $item->id,
                'sort_order'    => (int) $item->sort_order,
            );
        }, $db_items );
    }

    /**
     * Sauvegarder les items d'un groupe personnalisé
     *
     * @since 1.3.0
     * @param string $slug  Slug du groupe
     * @param array  $items Items à sauvegarder
     * @return bool
     */
    public static function save_custom_group_items( $slug, $items ) {
        $group = WPVFH_Database::get_custom_group( $slug );
        if ( ! $group ) {
            return false;
        }

        // Récupérer les items existants
        $existing = WPVFH_Database::get_custom_group_items( $group->id );
        $existing_by_slug = array();
        foreach ( $existing as $item ) {
            $existing_by_slug[ $item->slug ] = $item;
        }

        $processed_slugs = array();
        $sort_order = 0;

        foreach ( $items as $item ) {
            $item_slug = isset( $item['id'] ) ? $item['id'] : sanitize_title( $item['label'] );
            $processed_slugs[] = $item_slug;

            $db_data = array(
                'group_id'      => $group->id,
                'slug'          => $item_slug,
                'label'         => isset( $item['label'] ) ? $item['label'] : '',
                'emoji'         => isset( $item['emoji'] ) ? $item['emoji'] : '📌',
                'color'         => isset( $item['color'] ) ? $item['color'] : '#666666',
                'display_mode'  => isset( $item['display_mode'] ) ? $item['display_mode'] : 'emoji',
                'sort_order'    => $sort_order,
                'enabled'       => isset( $item['enabled'] ) ? (int) $item['enabled'] : 1,
                'ai_prompt'     => isset( $item['ai_prompt'] ) ? $item['ai_prompt'] : '',
                'allowed_roles' => isset( $item['allowed_roles'] ) ? $item['allowed_roles'] : array(),
                'allowed_users' => isset( $item['allowed_users'] ) ? $item['allowed_users'] : array(),
            );

            if ( isset( $existing_by_slug[ $item_slug ] ) ) {
                WPVFH_Database::update_custom_group_item( $existing_by_slug[ $item_slug ]->id, $db_data );
            } else {
                WPVFH_Database::insert_custom_group_item( $db_data );
            }

            $sort_order++;
        }

        // Supprimer les items qui ne sont plus dans la liste
        foreach ( $existing_by_slug as $item_slug => $item ) {
            if ( ! in_array( $item_slug, $processed_slugs, true ) ) {
                WPVFH_Database::delete_custom_group_item( $item->id );
            }
        }

        return true;
    }

    /**
     * Vérifier si un groupe est un groupe par défaut
     *
     * @since 1.3.0
     * @param string $slug Slug du groupe
     * @return bool
     */
    public static function is_default_group( $slug ) {
        return in_array( $slug, self::$default_groups, true );
    }

    /**
     * Obtenir les paramètres d'un groupe
     *
     * @since 1.4.0
     * @param string $slug Slug du groupe
     * @return array
     */
    public static function get_group_settings( $slug ) {
        return WPVFH_Database::get_group_settings( $slug );
    }

    /**
     * Sauvegarder les paramètres d'un groupe
     *
     * @since 1.4.0
     * @param string $slug     Slug du groupe
     * @param array  $settings Paramètres à sauvegarder
     * @return bool
     */
    public static function save_group_settings( $slug, $settings ) {
        return WPVFH_Database::save_group_settings( $slug, $settings );
    }

    /**
     * Renommer un groupe personnalisé
     *
     * @since 1.4.0
     * @param string $slug     Slug du groupe
     * @param string $new_name Nouveau nom
     * @return bool
     */
    public static function rename_custom_group( $slug, $new_name ) {
        if ( self::is_default_group( $slug ) ) {
            return false;
        }

        return WPVFH_Database::update_custom_group( $slug, array( 'name' => $new_name ) );
    }

    /**
     * Vérifier si l'utilisateur a accès à un groupe
     *
     * @since 1.4.0
     * @param string   $slug    Slug du groupe
     * @param int|null $user_id ID utilisateur
     * @return bool
     */
    public static function user_can_access_group( $slug, $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        $settings = self::get_group_settings( $slug );

        // Si désactivé, pas d'accès (sauf admin)
        if ( ! $settings['enabled'] && ! current_user_can( 'manage_feedback' ) ) {
            return false;
        }

        // Si pas de restrictions, tout le monde a accès
        $has_role_restriction = ! empty( $settings['allowed_roles'] );
        $has_user_restriction = ! empty( $settings['allowed_users'] );

        if ( ! $has_role_restriction && ! $has_user_restriction ) {
            return true;
        }

        // Vérifier si l'utilisateur est dans la liste
        if ( $has_user_restriction && in_array( $user_id, $settings['allowed_users'], true ) ) {
            return true;
        }

        // Vérifier si l'utilisateur a un des rôles autorisés
        if ( $has_role_restriction ) {
            $user = get_user_by( 'id', $user_id );
            if ( $user && ! empty( array_intersect( $user->roles, $settings['allowed_roles'] ) ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir tous les onglets (par défaut + personnalisés)
     *
     * @since 1.3.0
     * @return array
     */
    public static function get_all_tabs() {
        $tabs = array(
            'statuses'   => __( 'Statuts', 'blazing-feedback' ),
            'types'      => __( 'Types de feedback', 'blazing-feedback' ),
            'priorities' => __( 'Niveaux de priorité', 'blazing-feedback' ),
            'tags'       => __( 'Tags prédéfinis', 'blazing-feedback' ),
        );

        // Ajouter les groupes personnalisés
        $custom_groups = self::get_custom_groups();
        foreach ( $custom_groups as $slug => $group ) {
            $tabs[ $slug ] = $group['name'];
        }

        return $tabs;
    }

    /**
     * Obtenir un statut par ID
     *
     * @since 1.1.0
     * @param string $id ID du statut
     * @return array|null
     */
    public static function get_status_by_id( $id ) {
        $statuses = self::get_statuses();
        foreach ( $statuses as $status ) {
            if ( $status['id'] === $id ) {
                return $status;
            }
        }
        return null;
    }

    /**
     * Obtenir un type par ID
     *
     * @since 1.1.0
     * @param string $id ID du type
     * @return array|null
     */
    public static function get_type_by_id( $id ) {
        $types = self::get_types();
        foreach ( $types as $type ) {
            if ( $type['id'] === $id ) {
                return $type;
            }
        }
        return null;
    }

    /**
     * Obtenir une priorité par ID
     *
     * @since 1.1.0
     * @param string $id ID de la priorité
     * @return array|null
     */
    public static function get_priority_by_id( $id ) {
        $priorities = self::get_priorities();
        foreach ( $priorities as $priority ) {
            if ( $priority['id'] === $id ) {
                return $priority;
            }
        }
        return null;
    }

    /**
     * Vérifier si un utilisateur a accès à une option
     *
     * @since 1.2.0
     * @param array    $item    L'élément d'option
     * @param int|null $user_id ID utilisateur (null = utilisateur courant)
     * @return bool
     */
    public static function user_can_access_option( $item, $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        // Si pas activé, pas d'accès
        if ( isset( $item['enabled'] ) && ! $item['enabled'] ) {
            return false;
        }

        // Si pas de restrictions, tout le monde a accès
        $has_role_restriction = ! empty( $item['allowed_roles'] );
        $has_user_restriction = ! empty( $item['allowed_users'] );

        if ( ! $has_role_restriction && ! $has_user_restriction ) {
            return true;
        }

        // Vérifier si l'utilisateur est dans la liste
        if ( $has_user_restriction && in_array( $user_id, $item['allowed_users'], true ) ) {
            return true;
        }

        // Vérifier si l'utilisateur a un des rôles autorisés
        if ( $has_role_restriction ) {
            $user = get_user_by( 'id', $user_id );
            if ( $user && ! empty( array_intersect( $user->roles, $item['allowed_roles'] ) ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filtrer les options accessibles par l'utilisateur
     *
     * @since 1.2.0
     * @param array    $items   Liste des éléments
     * @param int|null $user_id ID utilisateur
     * @return array
     */
    public static function filter_accessible_options( $items, $user_id = null ) {
        return array_filter( $items, function( $item ) use ( $user_id ) {
            return self::user_can_access_option( $item, $user_id );
        } );
    }

    /**
     * Gérer les actions admin
     *
     * @since 1.1.0
     * @return void
     */
    public static function handle_actions() {
        if ( ! isset( $_GET['page'] ) || 'wpvfh-options' !== $_GET['page'] ) {
            return;
        }

        // Reset aux valeurs par défaut
        if ( isset( $_GET['action'] ) && 'reset' === $_GET['action'] ) {
            check_admin_referer( 'wpvfh_reset_options' );

            if ( ! current_user_can( 'manage_feedback' ) ) {
                wp_die( esc_html__( 'Permission refusée.', 'blazing-feedback' ) );
            }

            $tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'types';

            switch ( $tab ) {
                case 'types':
                    delete_option( self::OPTION_TYPES );
                    break;
                case 'priorities':
                    delete_option( self::OPTION_PRIORITIES );
                    break;
                case 'tags':
                    delete_option( self::OPTION_TAGS );
                    break;
                case 'statuses':
                    delete_option( self::OPTION_STATUSES );
                    break;
                default:
                    // Groupe personnalisé - vider les items
                    if ( ! self::is_default_group( $tab ) ) {
                        self::save_custom_group_items( $tab, array() );
                    }
                    break;
            }

            wp_safe_redirect( admin_url( 'admin.php?page=wpvfh-options&tab=' . $tab . '&reset=1' ) );
            exit;
        }
    }

    /**
     * AJAX: Rechercher utilisateurs et rôles
     *
     * @since 1.2.0
     * @return void
     */
    public static function ajax_search_users_roles() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
        $results = array();

        // Rechercher les rôles
        $roles = wp_roles()->get_names();
        foreach ( $roles as $role_slug => $role_name ) {
            if ( empty( $search ) || stripos( $role_name, $search ) !== false || stripos( $role_slug, $search ) !== false ) {
                $results[] = array(
                    'type'  => 'role',
                    'id'    => $role_slug,
                    'label' => $role_name,
                    'icon'  => '👥',
                );
            }
        }

        // Rechercher les utilisateurs
        if ( ! empty( $search ) ) {
            $users = get_users( array(
                'search'         => '*' . $search . '*',
                'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
                'number'         => 10,
            ) );

            foreach ( $users as $user ) {
                $results[] = array(
                    'type'  => 'user',
                    'id'    => $user->ID,
                    'label' => $user->display_name . ' (' . $user->user_email . ')',
                    'icon'  => '👤',
                );
            }
        }

        wp_send_json_success( $results );
    }

    /**
     * AJAX: Sauvegarder l'ordre
     *
     * @since 1.1.0
     * @return void
     */
    public static function ajax_save_order() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $type  = isset( $_POST['option_type'] ) ? sanitize_key( $_POST['option_type'] ) : '';
        $order = isset( $_POST['order'] ) ? array_map( 'sanitize_key', $_POST['order'] ) : array();

        if ( empty( $type ) || empty( $order ) ) {
            wp_send_json_error( __( 'Données invalides.', 'blazing-feedback' ) );
        }

        $items = self::get_items_by_type( $type );

        // Réorganiser selon l'ordre
        $sorted = array();
        foreach ( $order as $id ) {
            foreach ( $items as $item ) {
                if ( $item['id'] === $id ) {
                    $sorted[] = $item;
                    break;
                }
            }
        }

        // Sauvegarder
        self::save_items_by_type( $type, $sorted );

        wp_send_json_success();
    }

    /**
     * Obtenir les items par type (helper)
     *
     * @since 1.3.0
     * @param string $type Type d'option
     * @return array
     */
    public static function get_items_by_type( $type ) {
        switch ( $type ) {
            case 'types':
                return self::get_types();
            case 'priorities':
                return self::get_priorities();
            case 'tags':
                return self::get_predefined_tags();
            case 'statuses':
                return self::get_statuses();
            default:
                // Groupe personnalisé
                return self::get_custom_group_items( $type );
        }
    }

    /**
     * Sauvegarder les items par type (helper)
     *
     * @since 1.3.0
     * @param string $type  Type d'option
     * @param array  $items Items à sauvegarder
     * @return bool
     */
    private static function save_items_by_type( $type, $items ) {
        switch ( $type ) {
            case 'types':
                return self::save_types( $items );
            case 'priorities':
                return self::save_priorities( $items );
            case 'tags':
                return self::save_tags( $items );
            case 'statuses':
                return self::save_statuses( $items );
            default:
                // Groupe personnalisé
                return self::save_custom_group_items( $type, $items );
        }
    }

    /**
     * AJAX: Sauvegarder un élément
     *
     * @since 1.1.0
     * @return void
     */
    public static function ajax_save_item() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $option_type   = isset( $_POST['option_type'] ) ? sanitize_key( $_POST['option_type'] ) : '';
        $item_id       = isset( $_POST['item_id'] ) ? sanitize_key( $_POST['item_id'] ) : '';
        $label         = isset( $_POST['label'] ) ? sanitize_text_field( $_POST['label'] ) : '';
        $emoji         = isset( $_POST['emoji'] ) ? wp_kses( $_POST['emoji'], array() ) : '📌';
        $color         = isset( $_POST['color'] ) ? sanitize_hex_color( $_POST['color'] ) : '#666666';
        $display_mode  = isset( $_POST['display_mode'] ) ? sanitize_key( $_POST['display_mode'] ) : 'emoji';
        $enabled       = isset( $_POST['enabled'] ) ? ( $_POST['enabled'] === 'true' || $_POST['enabled'] === '1' ) : true;
        $is_treated    = isset( $_POST['is_treated'] ) ? ( $_POST['is_treated'] === 'true' || $_POST['is_treated'] === '1' ) : false;
        $ai_prompt     = isset( $_POST['ai_prompt'] ) ? sanitize_textarea_field( $_POST['ai_prompt'] ) : '';
        $allowed_roles = isset( $_POST['allowed_roles'] ) ? array_map( 'sanitize_key', (array) $_POST['allowed_roles'] ) : array();
        $allowed_users = isset( $_POST['allowed_users'] ) ? array_map( 'absint', (array) $_POST['allowed_users'] ) : array();
        $is_new        = isset( $_POST['is_new'] ) && $_POST['is_new'] === 'true';

        if ( empty( $option_type ) || empty( $label ) ) {
            wp_send_json_error( __( 'Données invalides.', 'blazing-feedback' ) );
        }

        // Valider display_mode
        if ( ! in_array( $display_mode, array( 'emoji', 'color_dot' ), true ) ) {
            $display_mode = 'emoji';
        }

        // Générer un ID si nouveau
        if ( $is_new || empty( $item_id ) ) {
            $item_id = sanitize_title( $label ) . '_' . time();
        }

        $new_item = array(
            'id'            => $item_id,
            'label'         => $label,
            'emoji'         => $emoji,
            'color'         => $color,
            'display_mode'  => $display_mode,
            'enabled'       => $enabled,
            'is_treated'    => $is_treated,
            'ai_prompt'     => $ai_prompt,
            'allowed_roles' => array_filter( $allowed_roles ),
            'allowed_users' => array_filter( $allowed_users ),
        );

        // Obtenir les items existants
        $items = self::get_items_by_type( $option_type );

        // Mettre à jour ou ajouter
        $found = false;
        foreach ( $items as $key => $item ) {
            if ( $item['id'] === $item_id ) {
                $items[ $key ] = $new_item;
                $found = true;
                break;
            }
        }

        if ( ! $found ) {
            $items[] = $new_item;
        }

        // Sauvegarder
        self::save_items_by_type( $option_type, $items );

        wp_send_json_success( array( 'item' => $new_item ) );
    }

    /**
     * AJAX: Supprimer un élément
     *
     * @since 1.1.0
     * @return void
     */
    public static function ajax_delete_item() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $option_type = isset( $_POST['option_type'] ) ? sanitize_key( $_POST['option_type'] ) : '';
        $item_id     = isset( $_POST['item_id'] ) ? sanitize_key( $_POST['item_id'] ) : '';

        if ( empty( $option_type ) || empty( $item_id ) ) {
            wp_send_json_error( __( 'Données invalides.', 'blazing-feedback' ) );
        }

        // Obtenir les items existants
        $items = self::get_items_by_type( $option_type );

        // Supprimer l'élément
        $items = array_filter( $items, function( $item ) use ( $item_id ) {
            return $item['id'] !== $item_id;
        } );
        $items = array_values( $items ); // Réindexer

        // Sauvegarder
        self::save_items_by_type( $option_type, $items );

        wp_send_json_success();
    }

    /**
     * AJAX: Créer un groupe personnalisé
     *
     * @since 1.3.0
     * @return void
     */
    public static function ajax_create_custom_group() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';

        if ( empty( $name ) ) {
            wp_send_json_error( __( 'Le nom du groupe est requis.', 'blazing-feedback' ) );
        }

        $group = self::create_custom_group( $name );

        if ( ! $group ) {
            wp_send_json_error( __( 'Erreur lors de la création du groupe.', 'blazing-feedback' ) );
        }

        wp_send_json_success( array(
            'group'       => $group,
            'redirect_url' => admin_url( 'admin.php?page=wpvfh-options&tab=' . $group['slug'] ),
        ) );
    }

    /**
     * AJAX: Supprimer un groupe personnalisé
     *
     * @since 1.3.0
     * @return void
     */
    public static function ajax_delete_custom_group() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $slug = isset( $_POST['slug'] ) ? sanitize_key( $_POST['slug'] ) : '';

        if ( empty( $slug ) ) {
            wp_send_json_error( __( 'Slug du groupe requis.', 'blazing-feedback' ) );
        }

        if ( self::is_default_group( $slug ) ) {
            wp_send_json_error( __( 'Les groupes par défaut ne peuvent pas être supprimés.', 'blazing-feedback' ) );
        }

        if ( ! self::delete_custom_group( $slug ) ) {
            wp_send_json_error( __( 'Erreur lors de la suppression du groupe.', 'blazing-feedback' ) );
        }

        wp_send_json_success( array(
            'redirect_url' => admin_url( 'admin.php?page=wpvfh-options&tab=statuses' ),
        ) );
    }

    /**
     * AJAX: Renommer un groupe personnalisé
     *
     * @since 1.4.0
     * @return void
     */
    public static function ajax_rename_custom_group() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $slug = isset( $_POST['slug'] ) ? sanitize_key( $_POST['slug'] ) : '';
        $name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';

        if ( empty( $slug ) || empty( $name ) ) {
            wp_send_json_error( __( 'Slug et nom du groupe requis.', 'blazing-feedback' ) );
        }

        if ( self::is_default_group( $slug ) ) {
            wp_send_json_error( __( 'Les groupes par défaut ne peuvent pas être renommés.', 'blazing-feedback' ) );
        }

        if ( ! self::rename_custom_group( $slug, $name ) ) {
            wp_send_json_error( __( 'Erreur lors du renommage du groupe.', 'blazing-feedback' ) );
        }

        wp_send_json_success( array(
            'name' => $name,
        ) );
    }

    /**
     * AJAX: Sauvegarder les paramètres d'un groupe
     *
     * @since 1.4.0
     * @return void
     */
    public static function ajax_save_group_settings() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $slug = isset( $_POST['slug'] ) ? sanitize_key( $_POST['slug'] ) : '';

        if ( empty( $slug ) ) {
            wp_send_json_error( __( 'Slug du groupe requis.', 'blazing-feedback' ) );
        }

        // Récupérer et nettoyer les paramètres
        $enabled = isset( $_POST['enabled'] ) && $_POST['enabled'] === 'true';
        $required = isset( $_POST['required'] ) && $_POST['required'] === 'true';
        $show_in_sidebar = isset( $_POST['show_in_sidebar'] ) && $_POST['show_in_sidebar'] === 'true';
        $ai_prompt = isset( $_POST['ai_prompt'] ) ? sanitize_textarea_field( $_POST['ai_prompt'] ) : '';

        $allowed_roles = array();
        if ( ! empty( $_POST['allowed_roles'] ) ) {
            $allowed_roles = array_map( 'sanitize_key', explode( ',', $_POST['allowed_roles'] ) );
            $allowed_roles = array_filter( $allowed_roles );
        }

        $allowed_users = array();
        if ( ! empty( $_POST['allowed_users'] ) ) {
            $allowed_users = array_map( 'intval', explode( ',', $_POST['allowed_users'] ) );
            $allowed_users = array_filter( $allowed_users );
        }

        $settings = array(
            'enabled'         => $enabled,
            'required'        => $required,
            'show_in_sidebar' => $show_in_sidebar,
            'allowed_roles'   => $allowed_roles,
            'allowed_users'   => $allowed_users,
            'ai_prompt'       => $ai_prompt,
        );

        if ( ! self::save_group_settings( $slug, $settings ) ) {
            wp_send_json_error( __( 'Erreur lors de la sauvegarde des paramètres.', 'blazing-feedback' ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Paramètres enregistrés.', 'blazing-feedback' ),
        ) );
    }

    /**
     * Rendu de la page d'options
     *
     * @since 1.1.0
     * @return void
     */
    public static function render_options_page() {
        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_die( esc_html__( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'statuses';
        $tabs = self::get_all_tabs();
        $custom_groups = self::get_custom_groups();
        $is_custom_tab = isset( $custom_groups[ $current_tab ] );

        // Message de confirmation
        $message = '';
        if ( isset( $_GET['reset'] ) ) {
            $message = '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Métadatas réinitialisées avec succès.', 'blazing-feedback' ) . '</p></div>';
        }
        if ( isset( $_GET['created'] ) ) {
            $message = '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Groupe créé avec succès.', 'blazing-feedback' ) . '</p></div>';
        }
        ?>
        <div class="wrap wpvfh-options-page">
            <h1><?php esc_html_e( 'Métadatas', 'blazing-feedback' ); ?></h1>

            <?php echo $message; ?>

            <nav class="nav-tab-wrapper wpvfh-nav-tabs">
                <?php foreach ( $tabs as $tab_id => $tab_label ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpvfh-options&tab=' . $tab_id ) ); ?>"
                       class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>"
                       data-tab="<?php echo esc_attr( $tab_id ); ?>"
                       data-deletable="<?php echo ! self::is_default_group( $tab_id ) ? 'true' : 'false'; ?>">
                        <?php echo esc_html( $tab_label ); ?>
                        <?php if ( ! self::is_default_group( $tab_id ) ) : ?>
                            <span class="wpvfh-tab-delete" title="<?php esc_attr_e( 'Supprimer ce groupe', 'blazing-feedback' ); ?>">&times;</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
                <button type="button" class="nav-tab wpvfh-add-group-btn" title="<?php esc_attr_e( 'Ajouter un nouveau groupe', 'blazing-feedback' ); ?>">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php esc_html_e( 'Ajouter', 'blazing-feedback' ); ?>
                </button>
            </nav>

            <div class="wpvfh-options-content">
                <?php
                switch ( $current_tab ) {
                    case 'statuses':
                        self::render_statuses_tab();
                        break;
                    case 'types':
                        self::render_types_tab();
                        break;
                    case 'priorities':
                        self::render_priorities_tab();
                        break;
                    case 'tags':
                        self::render_tags_tab();
                        break;
                    default:
                        // Groupe personnalisé
                        if ( $is_custom_tab ) {
                            self::render_custom_group_tab( $current_tab, $custom_groups[ $current_tab ] );
                        }
                        break;
                }
                ?>
            </div>
        </div>

        <!-- Modal pour créer un nouveau groupe -->
        <div id="wpvfh-new-group-modal" class="wpvfh-modal">
            <div class="wpvfh-modal-content">
                <div class="wpvfh-modal-header">
                    <h2><?php esc_html_e( 'Nouveau groupe de métadatas', 'blazing-feedback' ); ?></h2>
                    <button type="button" class="wpvfh-modal-close">&times;</button>
                </div>
                <div class="wpvfh-modal-body">
                    <p><?php esc_html_e( 'Créez un nouveau groupe de métadatas personnalisé pour vos feedbacks.', 'blazing-feedback' ); ?></p>
                    <div class="wpvfh-form-group">
                        <label for="wpvfh-new-group-name"><?php esc_html_e( 'Nom du groupe', 'blazing-feedback' ); ?></label>
                        <input type="text" id="wpvfh-new-group-name" class="regular-text" placeholder="<?php esc_attr_e( 'Ex: Catégories, Départements, etc.', 'blazing-feedback' ); ?>">
                    </div>
                </div>
                <div class="wpvfh-modal-footer">
                    <button type="button" class="button wpvfh-modal-cancel"><?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?></button>
                    <button type="button" class="button button-primary wpvfh-create-group-btn"><?php esc_html_e( 'Créer le groupe', 'blazing-feedback' ); ?></button>
                </div>
            </div>
        </div>

        <!-- Emoji Picker Popup -->
        <div id="wpvfh-emoji-picker" class="wpvfh-emoji-picker">
            <div class="wpvfh-emoji-picker-header">
                <div class="wpvfh-emoji-tabs">
                    <button type="button" class="wpvfh-emoji-tab active" data-category="smileys" title="<?php esc_attr_e( 'Smileys', 'blazing-feedback' ); ?>">😀</button>
                    <button type="button" class="wpvfh-emoji-tab" data-category="gestures" title="<?php esc_attr_e( 'Gestes', 'blazing-feedback' ); ?>">👍</button>
                    <button type="button" class="wpvfh-emoji-tab" data-category="symbols" title="<?php esc_attr_e( 'Symboles', 'blazing-feedback' ); ?>">❤️</button>
                    <button type="button" class="wpvfh-emoji-tab" data-category="objects" title="<?php esc_attr_e( 'Objets', 'blazing-feedback' ); ?>">📦</button>
                    <button type="button" class="wpvfh-emoji-tab" data-category="nature" title="<?php esc_attr_e( 'Nature', 'blazing-feedback' ); ?>">🌿</button>
                    <button type="button" class="wpvfh-emoji-tab" data-category="flags" title="<?php esc_attr_e( 'Drapeaux', 'blazing-feedback' ); ?>">🚩</button>
                </div>
            </div>
            <div class="wpvfh-emoji-picker-content">
                <div class="wpvfh-emoji-grid" data-category="smileys">
                    <?php
                    $smileys = array( '😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '😮‍💨', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '🥴', '😵', '🤯', '🤠', '🥳', '🥸', '😎', '🤓', '🧐', '😕', '😟', '🙁', '☹️', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '🥱', '😤', '😡', '😠', '🤬', '😈', '👿', '💀', '☠️', '💩', '🤡', '👹', '👺', '👻', '👽', '👾', '🤖' );
                    foreach ( $smileys as $e ) {
                        echo '<span class="wpvfh-emoji-item">' . esc_html( $e ) . '</span>';
                    }
                    ?>
                </div>
                <div class="wpvfh-emoji-grid" data-category="gestures" style="display: none;">
                    <?php
                    $gestures = array( '👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅', '🤳', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠', '🫀', '🫁', '🦷', '🦴', '👀', '👁️', '👅', '👄', '👶', '🧒', '👦', '👧', '🧑', '👱', '👨', '🧔', '👩', '🧓', '👴', '👵', '🙍', '🙎', '🙅', '🙆', '💁', '🙋', '🧏', '🙇', '🤦', '🤷', '👮', '🕵️', '💂', '🥷', '👷', '🤴', '👸', '👳', '👲', '🧕', '🤵', '👰', '🤰', '🤱', '👼', '🎅', '🤶', '🦸', '🦹', '🧙', '🧚', '🧛', '🧜', '🧝', '🧞', '🧟', '💆', '💇', '🚶', '🧍', '🧎', '🏃', '💃', '🕺', '🕴️', '👯', '🧖', '🧗', '🤸', '🏌️', '🏇', '⛷️', '🏂', '🏋️', '🤼', '🤽', '🤾', '🤺', '⛹️', '🏊', '🚣', '🧘', '🛀', '🛌' );
                    foreach ( $gestures as $e ) {
                        echo '<span class="wpvfh-emoji-item">' . esc_html( $e ) . '</span>';
                    }
                    ?>
                </div>
                <div class="wpvfh-emoji-grid" data-category="symbols" style="display: none;">
                    <?php
                    $symbols = array( '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '☮️', '✝️', '☪️', '🕉️', '☸️', '✡️', '🔯', '🕎', '☯️', '☦️', '🛐', '⛎', '♈', '♉', '♊', '♋', '♌', '♍', '♎', '♏', '♐', '♑', '♒', '♓', '🆔', '⚛️', '🉑', '☢️', '☣️', '📴', '📳', '🈶', '🈚', '🈸', '🈺', '🈷️', '✴️', '🆚', '💮', '🉐', '㊙️', '㊗️', '🈴', '🈵', '🈹', '🈲', '🅰️', '🅱️', '🆎', '🆑', '🅾️', '🆘', '❌', '⭕', '🛑', '⛔', '📛', '🚫', '💯', '💢', '♨️', '🚷', '🚯', '🚳', '🚱', '🔞', '📵', '🚭', '❗', '❕', '❓', '❔', '‼️', '⁉️', '🔅', '🔆', '〽️', '⚠️', '🚸', '🔱', '⚜️', '🔰', '♻️', '✅', '🈯', '💹', '❇️', '✳️', '❎', '🌐', '💠', 'Ⓜ️', '🌀', '💤', '🏧', '🚾', '♿', '🅿️', '🛗', '🈳', '🈂️', '🛂', '🛃', '🛄', '🛅', '🚹', '🚺', '🚼', '⚧️', '🚻', '🚮', '🎦', '📶', '🈁', '🔣', 'ℹ️', '🔤', '🔡', '🔠', '🆖', '🆗', '🆙', '🆒', '🆕', '🆓', '0️⃣', '1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟', '🔢', '#️⃣', '*️⃣', '⏏️', '▶️', '⏸️', '⏯️', '⏹️', '⏺️', '⏭️', '⏮️', '⏩', '⏪', '⏫', '⏬', '◀️', '🔼', '🔽', '➡️', '⬅️', '⬆️', '⬇️', '↗️', '↘️', '↙️', '↖️', '↕️', '↔️', '↪️', '↩️', '⤴️', '⤵️', '🔀', '🔁', '🔂', '🔄', '🔃', '🎵', '🎶', '➕', '➖', '➗', '✖️', '🟰', '♾️', '💲', '💱', '™️', '©️', '®️', '〰️', '➰', '➿', '🔚', '🔙', '🔛', '🔝', '🔜', '✔️', '☑️', '🔘', '🔴', '🟠', '🟡', '🟢', '🔵', '🟣', '⚫', '⚪', '🟤', '🔺', '🔻', '🔸', '🔹', '🔶', '🔷', '🔳', '🔲', '▪️', '▫️', '◾', '◽', '◼️', '◻️', '🟥', '🟧', '🟨', '🟩', '🟦', '🟪', '⬛', '⬜', '🟫', '🔈', '🔇', '🔉', '🔊', '🔔', '🔕', '📣', '📢', '💬', '💭', '🗯️', '♠️', '♣️', '♥️', '♦️', '🃏', '🎴', '🀄', '🕐', '🕑', '🕒', '🕓', '🕔', '🕕', '🕖', '🕗', '🕘', '🕙', '🕚', '🕛', '🕜', '🕝', '🕞', '🕟', '🕠', '🕡', '🕢', '🕣', '🕤', '🕥', '🕦', '🕧' );
                    foreach ( $symbols as $e ) {
                        echo '<span class="wpvfh-emoji-item">' . esc_html( $e ) . '</span>';
                    }
                    ?>
                </div>
                <div class="wpvfh-emoji-grid" data-category="objects" style="display: none;">
                    <?php
                    $objects = array( '📌', '📍', '📎', '🖇️', '📏', '📐', '✂️', '🗃️', '🗄️', '🗑️', '🔒', '🔓', '🔏', '🔐', '🔑', '🗝️', '🔨', '🪓', '⛏️', '⚒️', '🛠️', '🗡️', '⚔️', '🔫', '🪃', '🏹', '🛡️', '🪚', '🔧', '🪛', '🔩', '⚙️', '🗜️', '⚖️', '🦯', '🔗', '⛓️', '🪝', '🧰', '🧲', '🪜', '⚗️', '🧪', '🧫', '🧬', '🔬', '🔭', '📡', '💉', '🩸', '💊', '🩹', '🩺', '🚪', '🛗', '🪞', '🪟', '🛏️', '🛋️', '🪑', '🚽', '🪠', '🚿', '🛁', '🪤', '🪒', '🧴', '🧷', '🧹', '🧺', '🧻', '🪣', '🧼', '🪥', '🧽', '🧯', '🛒', '🚬', '⚰️', '🪦', '⚱️', '🗿', '🪧', '🏧', '🎰', '💎', '💍', '👑', '👒', '🎩', '🎓', '🧢', '⛑️', '📿', '💄', '💼', '🎒', '🧳', '👓', '🕶️', '🥽', '🌂', '☂️', '🧵', '🪡', '🧶', '👔', '👕', '👖', '🧣', '🧤', '🧥', '🧦', '👗', '👘', '🥻', '🩱', '🩲', '🩳', '👙', '👚', '👛', '👜', '👝', '🛍️', '🎀', '💰', '💴', '💵', '💶', '💷', '💸', '💳', '🧾', '💹', '📱', '📲', '☎️', '📞', '📟', '📠', '🔋', '🔌', '💻', '🖥️', '🖨️', '⌨️', '🖱️', '🖲️', '💽', '💾', '💿', '📀', '🧮', '🎥', '🎞️', '📽️', '🎬', '📺', '📷', '📸', '📹', '📼', '🔍', '🔎', '🕯️', '💡', '🔦', '🏮', '🪔', '📔', '📕', '📖', '📗', '📘', '📙', '📚', '📓', '📒', '📃', '📜', '📄', '📰', '🗞️', '📑', '🔖', '🏷️', '✉️', '📧', '📨', '📩', '📤', '📥', '📦', '📫', '📪', '📬', '📭', '📮', '🗳️', '✏️', '✒️', '🖋️', '🖊️', '🖌️', '🖍️', '📝', '📁', '📂', '🗂️', '📅', '📆', '🗒️', '🗓️', '📇', '📈', '📉', '📊', '🎁', '🎀', '🎈', '🎉', '🎊', '🎄', '🎃', '🪅', '🪆', '🎋', '🎍', '🎎', '🎏', '🎐', '🎑', '🧧', '🪄', '🎮', '🕹️', '🎲', '🧩', '🧸', '🪀', '🪁', '♟️', '🎯', '🎳', '🎱', '🔮', '🪬', '🧿', '🎼', '🎤', '🎧', '🎷', '🪗', '🎸', '🎹', '🎺', '🎻', '🪕', '🥁', '🪘' );
                    foreach ( $objects as $e ) {
                        echo '<span class="wpvfh-emoji-item">' . esc_html( $e ) . '</span>';
                    }
                    ?>
                </div>
                <div class="wpvfh-emoji-grid" data-category="nature" style="display: none;">
                    <?php
                    $nature = array( '🌿', '🍀', '🌱', '🌲', '🌳', '🌴', '🌵', '🌾', '🌷', '🌸', '🌹', '🌺', '🌻', '🌼', '💐', '🍁', '🍂', '🍃', '🪴', '🪻', '🪷', '🪹', '🪺', '🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐻‍❄️', '🐨', '🐯', '🦁', '🐮', '🐷', '🐽', '🐸', '🐵', '🙈', '🙉', '🙊', '🐒', '🐔', '🐧', '🐦', '🐤', '🐣', '🐥', '🦆', '🦅', '🦉', '🦇', '🐺', '🐗', '🐴', '🦄', '🐝', '🪱', '🐛', '🦋', '🐌', '🐞', '🐜', '🪰', '🪲', '🪳', '🦟', '🦗', '🕷️', '🕸️', '🦂', '🐢', '🐍', '🦎', '🦖', '🦕', '🐙', '🦑', '🦐', '🦞', '🦀', '🐡', '🐠', '🐟', '🐬', '🐳', '🐋', '🦈', '🐊', '🐅', '🐆', '🦓', '🦍', '🦧', '🦣', '🐘', '🦛', '🦏', '🐪', '🐫', '🦒', '🦘', '🦬', '🐃', '🐂', '🐄', '🐎', '🐖', '🐏', '🐑', '🦙', '🐐', '🦌', '🐕', '🐩', '🦮', '🐕‍🦺', '🐈', '🐈‍⬛', '🪶', '🐓', '🦃', '🦤', '🦚', '🦜', '🦢', '🦩', '🕊️', '🐇', '🦝', '🦨', '🦡', '🦫', '🦦', '🦥', '🐁', '🐀', '🐿️', '🦔', '🐾', '🐉', '🐲', '🌵', '🎄', '🌲', '🌳', '🌴', '🪵', '🌱', '🌿', '☘️', '🍀', '🎍', '🪴', '🎋', '🍃', '🍂', '🍁', '🍄', '🐚', '🪸', '🪨', '🌾', '💐', '🌷', '🌹', '🥀', '🪻', '🌺', '🌸', '🌼', '🌻', '🌞', '🌝', '🌛', '🌜', '🌚', '🌕', '🌖', '🌗', '🌘', '🌑', '🌒', '🌓', '🌔', '🌙', '🌎', '🌍', '🌏', '🪐', '💫', '⭐', '🌟', '✨', '⚡', '☄️', '💥', '🔥', '🌪️', '🌈', '☀️', '🌤️', '⛅', '🌥️', '☁️', '🌦️', '🌧️', '⛈️', '🌩️', '🌨️', '❄️', '☃️', '⛄', '🌬️', '💨', '💧', '💦', '☔', '☂️', '🌊', '🌫️' );
                    foreach ( $nature as $e ) {
                        echo '<span class="wpvfh-emoji-item">' . esc_html( $e ) . '</span>';
                    }
                    ?>
                </div>
                <div class="wpvfh-emoji-grid" data-category="flags" style="display: none;">
                    <?php
                    $flags = array( '🚩', '🏁', '🎌', '🏴', '🏳️', '🏳️‍🌈', '🏳️‍⚧️', '🏴‍☠️', '🇦🇫', '🇦🇱', '🇩🇿', '🇦🇸', '🇦🇩', '🇦🇴', '🇦🇮', '🇦🇶', '🇦🇬', '🇦🇷', '🇦🇲', '🇦🇼', '🇦🇺', '🇦🇹', '🇦🇿', '🇧🇸', '🇧🇭', '🇧🇩', '🇧🇧', '🇧🇾', '🇧🇪', '🇧🇿', '🇧🇯', '🇧🇲', '🇧🇹', '🇧🇴', '🇧🇦', '🇧🇼', '🇧🇷', '🇮🇴', '🇻🇬', '🇧🇳', '🇧🇬', '🇧🇫', '🇧🇮', '🇰🇭', '🇨🇲', '🇨🇦', '🇮🇨', '🇨🇻', '🇧🇶', '🇰🇾', '🇨🇫', '🇹🇩', '🇨🇱', '🇨🇳', '🇨🇽', '🇨🇨', '🇨🇴', '🇰🇲', '🇨🇬', '🇨🇩', '🇨🇰', '🇨🇷', '🇨🇮', '🇭🇷', '🇨🇺', '🇨🇼', '🇨🇾', '🇨🇿', '🇩🇰', '🇩🇯', '🇩🇲', '🇩🇴', '🇪🇨', '🇪🇬', '🇸🇻', '🇬🇶', '🇪🇷', '🇪🇪', '🇸🇿', '🇪🇹', '🇪🇺', '🇫🇰', '🇫🇴', '🇫🇯', '🇫🇮', '🇫🇷', '🇬🇫', '🇵🇫', '🇹🇫', '🇬🇦', '🇬🇲', '🇬🇪', '🇩🇪', '🇬🇭', '🇬🇮', '🇬🇷', '🇬🇱', '🇬🇩', '🇬🇵', '🇬🇺', '🇬🇹', '🇬🇬', '🇬🇳', '🇬🇼', '🇬🇾', '🇭🇹', '🇭🇳', '🇭🇰', '🇭🇺', '🇮🇸', '🇮🇳', '🇮🇩', '🇮🇷', '🇮🇶', '🇮🇪', '🇮🇲', '🇮🇱', '🇮🇹', '🇯🇲', '🇯🇵', '🎌', '🇯🇪', '🇯🇴', '🇰🇿', '🇰🇪', '🇰🇮', '🇽🇰', '🇰🇼', '🇰🇬', '🇱🇦', '🇱🇻', '🇱🇧', '🇱🇸', '🇱🇷', '🇱🇾', '🇱🇮', '🇱🇹', '🇱🇺', '🇲🇴', '🇲🇬', '🇲🇼', '🇲🇾', '🇲🇻', '🇲🇱', '🇲🇹', '🇲🇭', '🇲🇶', '🇲🇷', '🇲🇺', '🇾🇹', '🇲🇽', '🇫🇲', '🇲🇩', '🇲🇨', '🇲🇳', '🇲🇪', '🇲🇸', '🇲🇦', '🇲🇿', '🇲🇲', '🇳🇦', '🇳🇷', '🇳🇵', '🇳🇱', '🇳🇨', '🇳🇿', '🇳🇮', '🇳🇪', '🇳🇬', '🇳🇺', '🇳🇫', '🇰🇵', '🇲🇰', '🇲🇵', '🇳🇴', '🇴🇲', '🇵🇰', '🇵🇼', '🇵🇸', '🇵🇦', '🇵🇬', '🇵🇾', '🇵🇪', '🇵🇭', '🇵🇳', '🇵🇱', '🇵🇹', '🇵🇷', '🇶🇦', '🇷🇪', '🇷🇴', '🇷🇺', '🇷🇼', '🇼🇸', '🇸🇲', '🇸🇹', '🇸🇦', '🇸🇳', '🇷🇸', '🇸🇨', '🇸🇱', '🇸🇬', '🇸🇽', '🇸🇰', '🇸🇮', '🇬🇸', '🇸🇧', '🇸🇴', '🇿🇦', '🇰🇷', '🇸🇸', '🇪🇸', '🇱🇰', '🇧🇱', '🇸🇭', '🇰🇳', '🇱🇨', '🇵🇲', '🇻🇨', '🇸🇩', '🇸🇷', '🇸🇪', '🇨🇭', '🇸🇾', '🇹🇼', '🇹🇯', '🇹🇿', '🇹🇭', '🇹🇱', '🇹🇬', '🇹🇰', '🇹🇴', '🇹🇹', '🇹🇳', '🇹🇷', '🇹🇲', '🇹🇨', '🇹🇻', '🇻🇮', '🇺🇬', '🇺🇦', '🇦🇪', '🇬🇧', '🏴󠁧󠁢󠁥󠁮󠁧󠁿', '🏴󠁧󠁢󠁳󠁣󠁴󠁿', '🏴󠁧󠁢󠁷󠁬󠁳󠁿', '🇺🇳', '🇺🇸', '🇺🇾', '🇺🇿', '🇻🇺', '🇻🇦', '🇻🇪', '🇻🇳', '🇼🇫', '🇪🇭', '🇾🇪', '🇿🇲', '🇿🇼' );
                    foreach ( $flags as $e ) {
                        echo '<span class="wpvfh-emoji-item">' . esc_html( $e ) . '</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Rendu d'un onglet de groupe personnalisé
     *
     * @since 1.3.0
     * @param string $slug  Slug du groupe
     * @param array  $group Données du groupe
     * @return void
     */
    private static function render_custom_group_tab( $slug, $group ) {
        $items = self::get_custom_group_items( $slug );
        self::render_items_table( $slug, $items, $group['name'] );
    }

    /**
     * Rendu de l'onglet Statuts
     *
     * @since 1.1.0
     * @return void
     */
    private static function render_statuses_tab() {
        $statuses = self::get_statuses();
        self::render_items_table( 'statuses', $statuses );
    }

    /**
     * Rendu de l'onglet Types
     *
     * @since 1.1.0
     * @return void
     */
    private static function render_types_tab() {
        $types = self::get_types();
        self::render_items_table( 'types', $types );
    }

    /**
     * Rendu de l'onglet Priorités
     *
     * @since 1.1.0
     * @return void
     */
    private static function render_priorities_tab() {
        $priorities = self::get_priorities();
        self::render_items_table( 'priorities', $priorities );
    }

    /**
     * Rendu de l'onglet Tags
     *
     * @since 1.1.0
     * @return void
     */
    private static function render_tags_tab() {
        $tags = self::get_predefined_tags();
        self::render_items_table( 'tags', $tags );
    }

    /**
     * Rendu du tableau d'éléments
     *
     * @since 1.1.0
     * @param string      $type       Type d'option (types, priorities, tags, statuses, ou slug personnalisé)
     * @param array       $items      Éléments à afficher
     * @param string|null $group_name Nom du groupe (pour groupes personnalisés)
     * @return void
     */
    private static function render_items_table( $type, $items, $group_name = null ) {
        $reset_url = wp_nonce_url(
            admin_url( 'admin.php?page=wpvfh-options&tab=' . $type . '&action=reset' ),
            'wpvfh_reset_options'
        );

        // Obtenir les paramètres du groupe
        $group_settings = self::get_group_settings( $type );
        $is_custom_group = ! self::is_default_group( $type );

        // Préparer les labels d'accès pour affichage
        $access_labels = array();
        foreach ( $group_settings['allowed_roles'] as $role ) {
            $role_name = wp_roles()->get_names()[ $role ] ?? $role;
            $access_labels[] = array(
                'type'  => 'role',
                'id'    => $role,
                'label' => $role_name,
            );
        }
        foreach ( $group_settings['allowed_users'] as $user_id ) {
            $user = get_user_by( 'id', $user_id );
            if ( $user ) {
                $access_labels[] = array(
                    'type'  => 'user',
                    'id'    => $user_id,
                    'label' => $user->display_name,
                );
            }
        }
        ?>
        <!-- Paramètres du groupe -->
        <div class="wpvfh-group-settings-panel" data-group="<?php echo esc_attr( $type ); ?>">
            <div class="wpvfh-group-settings-header">
                <div class="wpvfh-group-title-section">
                    <?php if ( $is_custom_group && $group_name ) : ?>
                        <h3 class="wpvfh-group-title">
                            <span class="wpvfh-group-name-display"><?php echo esc_html( $group_name ); ?></span>
                            <input type="text" class="wpvfh-group-name-input" value="<?php echo esc_attr( $group_name ); ?>" style="display: none;">
                            <button type="button" class="wpvfh-rename-group-btn" title="<?php esc_attr_e( 'Renommer', 'blazing-feedback' ); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                        </h3>
                    <?php else : ?>
                        <h3 class="wpvfh-group-title"><?php echo esc_html( self::get_all_tabs()[ $type ] ?? $type ); ?></h3>
                    <?php endif; ?>
                    <p class="description">
                        <?php
                        switch ( $type ) {
                            case 'statuses':
                                esc_html_e( 'Définissez les statuts des feedbacks. Glissez-déposez pour réorganiser.', 'blazing-feedback' );
                                break;
                            case 'types':
                                esc_html_e( 'Définissez les types de feedback disponibles. Glissez-déposez pour réorganiser.', 'blazing-feedback' );
                                break;
                            case 'priorities':
                                esc_html_e( 'Définissez les niveaux de priorité disponibles. Glissez-déposez pour réorganiser.', 'blazing-feedback' );
                                break;
                            case 'tags':
                                esc_html_e( 'Définissez les tags prédéfinis. Les utilisateurs peuvent aussi créer leurs propres tags.', 'blazing-feedback' );
                                break;
                            default:
                                if ( $group_name ) {
                                    esc_html_e( 'Gérez les métadatas de ce groupe. Glissez-déposez pour réorganiser.', 'blazing-feedback' );
                                }
                                break;
                        }
                        ?>
                    </p>
                </div>
                <div class="wpvfh-group-settings-toggle">
                    <label class="wpvfh-toggle">
                        <input type="checkbox" class="wpvfh-group-enabled" <?php checked( $group_settings['enabled'] ); ?>>
                        <span class="wpvfh-toggle-slider"></span>
                    </label>
                    <span class="wpvfh-toggle-label"><?php esc_html_e( 'Activé', 'blazing-feedback' ); ?></span>
                    <span class="wpvfh-toggle-separator">|</span>
                    <label class="wpvfh-toggle">
                        <input type="checkbox" class="wpvfh-group-required" <?php checked( $group_settings['required'] ); ?>>
                        <span class="wpvfh-toggle-slider"></span>
                    </label>
                    <span class="wpvfh-toggle-label"><?php esc_html_e( 'Obligatoire', 'blazing-feedback' ); ?></span>
                    <span class="wpvfh-toggle-separator">|</span>
                    <label class="wpvfh-toggle">
                        <input type="checkbox" class="wpvfh-group-show-in-sidebar" <?php checked( $group_settings['show_in_sidebar'] ); ?>>
                        <span class="wpvfh-toggle-slider"></span>
                    </label>
                    <span class="wpvfh-toggle-label"><?php esc_html_e( 'Sidebar', 'blazing-feedback' ); ?></span>
                    <button type="button" class="button wpvfh-group-settings-btn" title="<?php esc_attr_e( 'Paramètres du groupe', 'blazing-feedback' ); ?>">
                        <span class="dashicons dashicons-admin-generic"></span>
                    </button>
                </div>
            </div>
            <div class="wpvfh-group-settings-body" style="display: none;">
                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group">
                        <label><?php esc_html_e( 'Accès autorisé (vide = tous)', 'blazing-feedback' ); ?></label>
                        <div class="wpvfh-access-control">
                            <div class="wpvfh-access-search-wrapper">
                                <input type="text" class="wpvfh-access-search wpvfh-group-access-search" placeholder="<?php esc_attr_e( 'Rechercher un rôle ou utilisateur...', 'blazing-feedback' ); ?>">
                                <div class="wpvfh-access-dropdown" style="display: none;"></div>
                            </div>
                            <div class="wpvfh-access-tags wpvfh-group-access-tags">
                                <?php foreach ( $access_labels as $access ) : ?>
                                    <span class="wpvfh-access-tag" data-type="<?php echo esc_attr( $access['type'] ); ?>" data-id="<?php echo esc_attr( $access['id'] ); ?>">
                                        <?php echo esc_html( $access['label'] ); ?>
                                        <button type="button" class="wpvfh-access-tag-remove">&times;</button>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" class="wpvfh-group-allowed-roles" value="<?php echo esc_attr( implode( ',', $group_settings['allowed_roles'] ) ); ?>">
                            <input type="hidden" class="wpvfh-group-allowed-users" value="<?php echo esc_attr( implode( ',', $group_settings['allowed_users'] ) ); ?>">
                        </div>
                        <p class="description"><?php esc_html_e( 'Si vide, tous les utilisateurs peuvent voir ce groupe.', 'blazing-feedback' ); ?></p>
                    </div>
                </div>
                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group">
                        <label><?php esc_html_e( 'Prompt IA pour ce groupe (optionnel)', 'blazing-feedback' ); ?></label>
                        <textarea class="wpvfh-group-ai-prompt large-text" rows="3" placeholder="<?php esc_attr_e( 'Instructions pour l\'IA pour toutes les métadatas de ce groupe...', 'blazing-feedback' ); ?>"><?php echo esc_textarea( $group_settings['ai_prompt'] ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Ce prompt sera utilisé par l\'IA pour traiter les feedbacks utilisant ce groupe de métadatas.', 'blazing-feedback' ); ?></p>
                    </div>
                </div>
                <div class="wpvfh-form-actions">
                    <button type="button" class="button button-primary wpvfh-save-group-settings-btn">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e( 'Enregistrer les paramètres du groupe', 'blazing-feedback' ); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="wpvfh-options-header">
            <p class="description">
                <?php esc_html_e( 'Éléments de ce groupe :', 'blazing-feedback' ); ?>
            </p>
            <div class="wpvfh-options-actions">
                <button type="button" class="button button-primary wpvfh-add-item-btn" data-type="<?php echo esc_attr( $type ); ?>">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php esc_html_e( 'Ajouter', 'blazing-feedback' ); ?>
                </button>
                <a href="<?php echo esc_url( $reset_url ); ?>" class="button"
                   onclick="return confirm('<?php esc_attr_e( 'Réinitialiser aux valeurs par défaut ?', 'blazing-feedback' ); ?>');">
                    <span class="dashicons dashicons-image-rotate"></span>
                    <?php esc_html_e( 'Réinitialiser', 'blazing-feedback' ); ?>
                </a>
            </div>
        </div>

        <div class="wpvfh-items-list" data-type="<?php echo esc_attr( $type ); ?>">
            <?php foreach ( $items as $item ) : ?>
                <?php self::render_item_card( $type, $item ); ?>
            <?php endforeach; ?>
        </div>

        <!-- Template pour nouvel élément -->
        <script type="text/template" id="wpvfh-item-template-<?php echo esc_attr( $type ); ?>">
            <?php
            self::render_item_card( $type, array(
                'id'            => '',
                'label'         => '',
                'emoji'         => '📌',
                'color'         => '#666666',
                'display_mode'  => 'emoji',
                'enabled'       => true,
                'ai_prompt'     => '',
                'allowed_roles' => array(),
                'allowed_users' => array(),
            ), true );
            ?>
        </script>
        <?php
    }

    /**
     * Rendu d'une carte d'élément
     *
     * @since 1.2.0
     * @param string $type   Type d'option
     * @param array  $item   Données de l'élément
     * @param bool   $is_new Est un nouvel élément
     * @return void
     */
    private static function render_item_card( $type, $item, $is_new = false ) {
        $item = self::normalize_item( $item );
        $id           = $item['id'];
        $label        = $item['label'];
        $emoji        = $item['emoji'];
        $color        = $item['color'];
        $display_mode = $item['display_mode'];
        $enabled      = $item['enabled'];
        $is_treated   = isset( $item['is_treated'] ) ? $item['is_treated'] : false;
        $ai_prompt    = $item['ai_prompt'];
        $allowed_roles = $item['allowed_roles'];
        $allowed_users = $item['allowed_users'];

        // Obtenir les noms des rôles/utilisateurs pour l'affichage
        $access_labels = array();
        $roles = wp_roles()->get_names();
        foreach ( $allowed_roles as $role ) {
            if ( isset( $roles[ $role ] ) ) {
                $access_labels[] = array( 'type' => 'role', 'id' => $role, 'label' => '👥 ' . $roles[ $role ] );
            }
        }
        foreach ( $allowed_users as $user_id ) {
            $user = get_user_by( 'id', $user_id );
            if ( $user ) {
                $access_labels[] = array( 'type' => 'user', 'id' => $user_id, 'label' => '👤 ' . $user->display_name );
            }
        }
        ?>
        <div class="wpvfh-option-card <?php echo $is_new ? 'wpvfh-new-item' : ''; ?> <?php echo ! $enabled ? 'wpvfh-disabled' : ''; ?>" data-id="<?php echo esc_attr( $id ); ?>">
            <div class="wpvfh-card-header">
                <span class="wpvfh-drag-handle dashicons dashicons-menu"></span>
                <div class="wpvfh-card-preview">
                    <?php if ( $display_mode === 'emoji' ) : ?>
                        <span class="wpvfh-preview-emoji"><?php echo esc_html( $emoji ); ?></span>
                    <?php else : ?>
                        <span class="wpvfh-preview-dot" style="background-color: <?php echo esc_attr( $color ); ?>;"></span>
                    <?php endif; ?>
                    <span class="wpvfh-preview-label"><?php echo esc_html( $label ?: __( 'Nouveau', 'blazing-feedback' ) ); ?></span>
                </div>
                <div class="wpvfh-card-actions">
                    <label class="wpvfh-toggle">
                        <input type="checkbox" class="wpvfh-enabled-toggle" <?php checked( $enabled ); ?>>
                        <span class="wpvfh-toggle-slider"></span>
                    </label>
                    <button type="button" class="wpvfh-expand-btn" title="<?php esc_attr_e( 'Développer', 'blazing-feedback' ); ?>">
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <button type="button" class="button wpvfh-delete-item-btn" title="<?php esc_attr_e( 'Supprimer', 'blazing-feedback' ); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>

            <div class="wpvfh-card-body" style="display: none;">
                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group wpvfh-form-group-half">
                        <label><?php esc_html_e( 'Label', 'blazing-feedback' ); ?></label>
                        <input type="text" class="wpvfh-label-input regular-text" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'Label...', 'blazing-feedback' ); ?>">
                    </div>
                    <div class="wpvfh-form-group wpvfh-form-group-quarter">
                        <label><?php esc_html_e( 'Couleur', 'blazing-feedback' ); ?></label>
                        <input type="text" class="wpvfh-color-input" value="<?php echo esc_attr( $color ); ?>" data-default-color="<?php echo esc_attr( $color ); ?>">
                    </div>
                </div>

                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group">
                        <label><?php esc_html_e( 'Mode d\'affichage', 'blazing-feedback' ); ?></label>
                        <div class="wpvfh-display-mode-selector">
                            <label class="wpvfh-radio-card <?php echo $display_mode === 'emoji' ? 'selected' : ''; ?>">
                                <input type="radio" name="display_mode_<?php echo esc_attr( $id ?: 'new' ); ?>" value="emoji" <?php checked( $display_mode, 'emoji' ); ?>>
                                <span class="wpvfh-radio-content">
                                    <span class="wpvfh-radio-icon"><?php echo esc_html( $emoji ); ?></span>
                                    <span class="wpvfh-radio-label"><?php esc_html_e( 'Emoji', 'blazing-feedback' ); ?></span>
                                </span>
                            </label>
                            <label class="wpvfh-radio-card <?php echo $display_mode === 'color_dot' ? 'selected' : ''; ?>">
                                <input type="radio" name="display_mode_<?php echo esc_attr( $id ?: 'new' ); ?>" value="color_dot" <?php checked( $display_mode, 'color_dot' ); ?>>
                                <span class="wpvfh-radio-content">
                                    <span class="wpvfh-radio-dot" style="background-color: <?php echo esc_attr( $color ); ?>;"></span>
                                    <span class="wpvfh-radio-label"><?php esc_html_e( 'Rond coloré', 'blazing-feedback' ); ?></span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="wpvfh-form-row wpvfh-emoji-row" style="<?php echo $display_mode !== 'emoji' ? 'display: none;' : ''; ?>">
                    <div class="wpvfh-form-group">
                        <label><?php esc_html_e( 'Emoji', 'blazing-feedback' ); ?></label>
                        <div class="wpvfh-emoji-input-wrapper">
                            <input type="text" class="wpvfh-emoji-input" value="<?php echo esc_attr( $emoji ); ?>" maxlength="4" readonly>
                            <button type="button" class="button wpvfh-emoji-picker-btn" title="<?php esc_attr_e( 'Choisir un emoji', 'blazing-feedback' ); ?>">
                                <span class="dashicons dashicons-smiley"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group">
                        <label><?php esc_html_e( 'Accès autorisé (vide = tous)', 'blazing-feedback' ); ?></label>
                        <div class="wpvfh-access-control">
                            <div class="wpvfh-access-search-wrapper">
                                <input type="text" class="wpvfh-access-search" placeholder="<?php esc_attr_e( 'Rechercher un rôle ou utilisateur...', 'blazing-feedback' ); ?>">
                                <div class="wpvfh-access-dropdown" style="display: none;"></div>
                            </div>
                            <div class="wpvfh-access-tags">
                                <?php foreach ( $access_labels as $access ) : ?>
                                    <span class="wpvfh-access-tag" data-type="<?php echo esc_attr( $access['type'] ); ?>" data-id="<?php echo esc_attr( $access['id'] ); ?>">
                                        <?php echo esc_html( $access['label'] ); ?>
                                        <button type="button" class="wpvfh-access-tag-remove">&times;</button>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" class="wpvfh-allowed-roles" value="<?php echo esc_attr( implode( ',', $allowed_roles ) ); ?>">
                            <input type="hidden" class="wpvfh-allowed-users" value="<?php echo esc_attr( implode( ',', $allowed_users ) ); ?>">
                        </div>
                        <p class="description"><?php esc_html_e( 'Si vide, tous les utilisateurs peuvent utiliser cette métadata.', 'blazing-feedback' ); ?></p>
                    </div>
                </div>

                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group">
                        <label><?php esc_html_e( 'Prompt IA (optionnel)', 'blazing-feedback' ); ?></label>
                        <textarea class="wpvfh-ai-prompt large-text" rows="3" placeholder="<?php esc_attr_e( 'Instructions pour l\'IA lors du traitement de ce type de feedback...', 'blazing-feedback' ); ?>"><?php echo esc_textarea( $ai_prompt ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Ce prompt sera utilisé par l\'IA pour traiter les feedbacks de ce type.', 'blazing-feedback' ); ?></p>
                    </div>
                </div>

                <?php if ( 'statuses' === $type ) : ?>
                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group">
                        <label class="wpvfh-checkbox-label">
                            <input type="checkbox" class="wpvfh-is-treated-toggle" <?php checked( $is_treated ); ?>>
                            <span><?php esc_html_e( 'Considéré comme traité', 'blazing-feedback' ); ?></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Les feedbacks avec ce statut seront considérés comme traités et masqués par défaut dans le widget.', 'blazing-feedback' ); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="wpvfh-form-actions">
                    <button type="button" class="button button-primary wpvfh-save-item-btn">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e( 'Enregistrer', 'blazing-feedback' ); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Obtenir toutes les options pour le frontend
     * Filtre par utilisateur et options activées
     *
     * @since 1.1.0
     * @param int|null $user_id ID utilisateur (null = courant)
     * @return array
     */
    public static function get_all_options_for_frontend( $user_id = null ) {
        return array(
            'statuses'   => array_values( self::filter_accessible_options( self::get_statuses(), $user_id ) ),
            'types'      => array_values( self::filter_accessible_options( self::get_types(), $user_id ) ),
            'priorities' => array_values( self::filter_accessible_options( self::get_priorities(), $user_id ) ),
            'tags'       => array_values( self::filter_accessible_options( self::get_predefined_tags(), $user_id ) ),
        );
    }
}
