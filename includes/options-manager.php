<?php
/**
 * Gestionnaire des options personnalisables (Types, Priorités, Tags, Statuts)
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
    const OPTION_TYPES      = 'wpvfh_feedback_types';
    const OPTION_PRIORITIES = 'wpvfh_feedback_priorities';
    const OPTION_TAGS       = 'wpvfh_feedback_tags';
    const OPTION_STATUSES   = 'wpvfh_feedback_statuses';

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
            __( 'Options de feedback', 'blazing-feedback' ),
            __( 'Options', 'blazing-feedback' ),
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
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'wpvfh_options_nonce' ),
            'roles'     => $roles,
            'i18n'      => array(
                'confirmDelete'   => __( 'Êtes-vous sûr de vouloir supprimer cet élément ?', 'blazing-feedback' ),
                'saving'          => __( 'Enregistrement...', 'blazing-feedback' ),
                'saved'           => __( 'Enregistré !', 'blazing-feedback' ),
                'error'           => __( 'Erreur lors de l\'enregistrement', 'blazing-feedback' ),
                'searchPlaceholder' => __( 'Rechercher un utilisateur ou rôle...', 'blazing-feedback' ),
                'noResults'       => __( 'Aucun résultat', 'blazing-feedback' ),
                'allAllowed'      => __( 'Tous autorisés (vide)', 'blazing-feedback' ),
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
                'id'    => 'resolved',
                'label' => __( 'Résolu', 'blazing-feedback' ),
                'emoji' => '✅',
                'color' => '#27ae60',
            ) ),
            self::create_default_item( array(
                'id'    => 'rejected',
                'label' => __( 'Rejeté', 'blazing-feedback' ),
                'emoji' => '❌',
                'color' => '#e74c3c',
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
            'ai_prompt'     => '',
            'allowed_roles' => array(),
            'allowed_users' => array(),
        );
        return array_merge( $defaults, $item );
    }

    /**
     * Obtenir les types de feedback
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_types() {
        $types = get_option( self::OPTION_TYPES );
        if ( false === $types || empty( $types ) ) {
            $types = self::get_default_types();
            update_option( self::OPTION_TYPES, $types );
        }
        // Normaliser chaque élément
        return array_map( array( __CLASS__, 'normalize_item' ), $types );
    }

    /**
     * Obtenir les priorités
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_priorities() {
        $priorities = get_option( self::OPTION_PRIORITIES );
        if ( false === $priorities || empty( $priorities ) ) {
            $priorities = self::get_default_priorities();
            update_option( self::OPTION_PRIORITIES, $priorities );
        }
        return array_map( array( __CLASS__, 'normalize_item' ), $priorities );
    }

    /**
     * Obtenir les tags prédéfinis
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_predefined_tags() {
        $tags = get_option( self::OPTION_TAGS );
        if ( false === $tags ) {
            $tags = self::get_default_tags();
            update_option( self::OPTION_TAGS, $tags );
        }
        return array_map( array( __CLASS__, 'normalize_item' ), $tags );
    }

    /**
     * Sauvegarder les types
     *
     * @since 1.1.0
     * @param array $types Types à sauvegarder
     * @return bool
     */
    public static function save_types( $types ) {
        return update_option( self::OPTION_TYPES, $types );
    }

    /**
     * Sauvegarder les priorités
     *
     * @since 1.1.0
     * @param array $priorities Priorités à sauvegarder
     * @return bool
     */
    public static function save_priorities( $priorities ) {
        return update_option( self::OPTION_PRIORITIES, $priorities );
    }

    /**
     * Sauvegarder les tags
     *
     * @since 1.1.0
     * @param array $tags Tags à sauvegarder
     * @return bool
     */
    public static function save_tags( $tags ) {
        return update_option( self::OPTION_TAGS, $tags );
    }

    /**
     * Obtenir les statuts
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_statuses() {
        $statuses = get_option( self::OPTION_STATUSES );
        if ( false === $statuses || empty( $statuses ) ) {
            $statuses = self::get_default_statuses();
            update_option( self::OPTION_STATUSES, $statuses );
        }
        return array_map( array( __CLASS__, 'normalize_item' ), $statuses );
    }

    /**
     * Sauvegarder les statuts
     *
     * @since 1.1.0
     * @param array $statuses Statuts à sauvegarder
     * @return bool
     */
    public static function save_statuses( $statuses ) {
        return update_option( self::OPTION_STATUSES, $statuses );
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

        $items = array();
        switch ( $type ) {
            case 'types':
                $items = self::get_types();
                break;
            case 'priorities':
                $items = self::get_priorities();
                break;
            case 'tags':
                $items = self::get_predefined_tags();
                break;
            case 'statuses':
                $items = self::get_statuses();
                break;
        }

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
        switch ( $type ) {
            case 'types':
                self::save_types( $sorted );
                break;
            case 'priorities':
                self::save_priorities( $sorted );
                break;
            case 'tags':
                self::save_tags( $sorted );
                break;
            case 'statuses':
                self::save_statuses( $sorted );
                break;
        }

        wp_send_json_success();
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
            'ai_prompt'     => $ai_prompt,
            'allowed_roles' => array_filter( $allowed_roles ),
            'allowed_users' => array_filter( $allowed_users ),
        );

        // Obtenir les items existants
        $items = array();
        switch ( $option_type ) {
            case 'types':
                $items = self::get_types();
                break;
            case 'priorities':
                $items = self::get_priorities();
                break;
            case 'tags':
                $items = self::get_predefined_tags();
                break;
            case 'statuses':
                $items = self::get_statuses();
                break;
        }

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
        switch ( $option_type ) {
            case 'types':
                self::save_types( $items );
                break;
            case 'priorities':
                self::save_priorities( $items );
                break;
            case 'tags':
                self::save_tags( $items );
                break;
            case 'statuses':
                self::save_statuses( $items );
                break;
        }

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
        $items = array();
        switch ( $option_type ) {
            case 'types':
                $items = self::get_types();
                break;
            case 'priorities':
                $items = self::get_priorities();
                break;
            case 'tags':
                $items = self::get_predefined_tags();
                break;
            case 'statuses':
                $items = self::get_statuses();
                break;
        }

        // Supprimer l'élément
        $items = array_filter( $items, function( $item ) use ( $item_id ) {
            return $item['id'] !== $item_id;
        } );
        $items = array_values( $items ); // Réindexer

        // Sauvegarder
        switch ( $option_type ) {
            case 'types':
                self::save_types( $items );
                break;
            case 'priorities':
                self::save_priorities( $items );
                break;
            case 'tags':
                self::save_tags( $items );
                break;
            case 'statuses':
                self::save_statuses( $items );
                break;
        }

        wp_send_json_success();
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
        $tabs = array(
            'statuses'   => __( 'Statuts', 'blazing-feedback' ),
            'types'      => __( 'Types de feedback', 'blazing-feedback' ),
            'priorities' => __( 'Niveaux de priorité', 'blazing-feedback' ),
            'tags'       => __( 'Tags prédéfinis', 'blazing-feedback' ),
        );

        // Message de confirmation
        $message = '';
        if ( isset( $_GET['reset'] ) ) {
            $message = '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Options réinitialisées avec succès.', 'blazing-feedback' ) . '</p></div>';
        }
        ?>
        <div class="wrap wpvfh-options-page">
            <h1><?php esc_html_e( 'Options de feedback', 'blazing-feedback' ); ?></h1>

            <?php echo $message; ?>

            <nav class="nav-tab-wrapper">
                <?php foreach ( $tabs as $tab_id => $tab_label ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpvfh-options&tab=' . $tab_id ) ); ?>"
                       class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html( $tab_label ); ?>
                    </a>
                <?php endforeach; ?>
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
                }
                ?>
            </div>
        </div>
        <?php
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
     * @param string $type  Type d'option (types, priorities, tags, statuses)
     * @param array  $items Éléments à afficher
     * @return void
     */
    private static function render_items_table( $type, $items ) {
        $reset_url = wp_nonce_url(
            admin_url( 'admin.php?page=wpvfh-options&tab=' . $type . '&action=reset' ),
            'wpvfh_reset_options'
        );
        ?>
        <div class="wpvfh-options-header">
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
                }
                ?>
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
                        <input type="text" class="wpvfh-emoji-input" value="<?php echo esc_attr( $emoji ); ?>" maxlength="4" style="width: 60px; text-align: center; font-size: 20px;">
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
                        <p class="description"><?php esc_html_e( 'Si vide, tous les utilisateurs peuvent utiliser cette option.', 'blazing-feedback' ); ?></p>
                    </div>
                </div>

                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group">
                        <label><?php esc_html_e( 'Prompt IA (optionnel)', 'blazing-feedback' ); ?></label>
                        <textarea class="wpvfh-ai-prompt large-text" rows="3" placeholder="<?php esc_attr_e( 'Instructions pour l\'IA lors du traitement de ce type de feedback...', 'blazing-feedback' ); ?>"><?php echo esc_textarea( $ai_prompt ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Ce prompt sera utilisé par l\'IA pour traiter les feedbacks de ce type.', 'blazing-feedback' ); ?></p>
                    </div>
                </div>

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
