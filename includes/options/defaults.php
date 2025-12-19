<?php
/**
 * Types, priorités, tags, statuts par défaut
 * 
 * Reference file for options-manager.php lines 136-340
 * See main file: includes/options-manager.php
 * 
 * @package Blazing_Feedback
 */

// To view this section, read options-manager.php with:
// offset=136, limit=205

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