<?php
/**
 * Trait pour la gestion des paramètres de groupes
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait pour la gestion des paramètres de groupes
 *
 * @since 1.7.0
 */
trait WPVFH_Options_Group_Settings_Trait {

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
}
