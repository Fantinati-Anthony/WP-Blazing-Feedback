<?php
/**
 * Fonctions helper (get_*_by_id, get_*_label)
 * 
 * Reference file for options-manager.php lines 1000-1200
 * See main file: includes/options-manager.php
 * 
 * @package Blazing_Feedback
 */

// To view this section, read options-manager.php with:
// offset=1000, limit=201


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