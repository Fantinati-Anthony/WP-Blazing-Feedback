<?php
/**
 * Trait pour les fonctions helper du gestionnaire d'options
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait pour les fonctions helper
 *
 * @since 1.7.0
 */
trait WPVFH_Options_Helpers_Trait {

    /**
     * Obtenir tous les onglets (par défaut + personnalisés) triés par sort_order
     *
     * @since 1.3.0
     * @return array
     */
    public static function get_all_tabs() {
        // Tous les onglets avec leurs labels
        $all_tabs = array(
            'statuses'   => __( 'Statuts', 'blazing-feedback' ),
            'types'      => __( 'Types de feedback', 'blazing-feedback' ),
            'priorities' => __( 'Niveaux de priorité', 'blazing-feedback' ),
            'tags'       => __( 'Tags prédéfinis', 'blazing-feedback' ),
        );

        // Ajouter les groupes personnalisés
        $custom_groups = self::get_custom_groups();
        foreach ( $custom_groups as $slug => $group ) {
            $all_tabs[ $slug ] = $group['name'];
        }

        // Récupérer l'ordre de tous les groupes
        $all_settings = WPVFH_Database::get_all_group_settings_ordered();

        // Construire un tableau slug => sort_order
        $order_map = array();
        foreach ( $all_settings as $slug => $settings ) {
            $order_map[ $slug ] = isset( $settings['sort_order'] ) ? $settings['sort_order'] : 99;
        }

        // Trier les onglets par sort_order
        uksort( $all_tabs, function( $a, $b ) use ( $order_map ) {
            $order_a = isset( $order_map[ $a ] ) ? $order_map[ $a ] : 99;
            $order_b = isset( $order_map[ $b ] ) ? $order_map[ $b ] : 99;
            return $order_a - $order_b;
        } );

        return $all_tabs;
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
