<?php
/**
 * Paramètres des groupes, items, renommage
 * 
 * Reference file for options-manager.php lines 748-1000
 * See main file: includes/options-manager.php
 * 
 * @package Blazing_Feedback
 */

// To view this section, read options-manager.php with:
// offset=748, limit=253

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
