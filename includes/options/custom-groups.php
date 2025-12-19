<?php
/**
 * Groupes personnalisés, création, suppression
 * 
 * Reference file for options-manager.php lines 550-800
 * See main file: includes/options-manager.php
 * 
 * @package Blazing_Feedback
 */

// To view this section, read options-manager.php with:
// offset=550, limit=251

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