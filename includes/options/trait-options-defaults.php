<?php
/**
 * Trait pour les valeurs par défaut du gestionnaire d'options
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait pour les valeurs par défaut
 *
 * @since 1.7.0
 */
trait WPVFH_Options_Defaults_Trait {

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
}
