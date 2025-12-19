<?php
/**
 * CRUD métadonnées (get/save types, priorities, tags)
 * 
 * Reference file for options-manager.php lines 341-550
 * See main file: includes/options-manager.php
 * 
 * @package Blazing_Feedback
 */

// To view this section, read options-manager.php with:
// offset=341, limit=210

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