<?php
/**
 * Trait pour les handlers AJAX du gestionnaire d'options
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait pour les handlers AJAX
 *
 * @since 1.7.0
 */
trait WPVFH_Options_Ajax_Handlers_Trait {

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
            'redirect_url' => admin_url( 'admin.php?page=bzmi-metadata&tab=' . $group['slug'] ),
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
            'redirect_url' => admin_url( 'admin.php?page=bzmi-metadata&tab=statuses' ),
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
}
