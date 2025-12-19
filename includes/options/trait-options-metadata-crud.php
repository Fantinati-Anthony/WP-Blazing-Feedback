<?php
/**
 * Trait pour le CRUD des métadonnées (types, priorités, tags, statuts)
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait pour le CRUD des métadonnées
 *
 * @since 1.7.0
 */
trait WPVFH_Options_Metadata_CRUD_Trait {

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
     * Sauvegarder les statuts
     *
     * @since 1.1.0
     * @param array $statuses Statuts à sauvegarder
     * @return bool
     */
    public static function save_statuses( $statuses ) {
        return self::save_metadata_items( 'statuses', $statuses );
    }
}
