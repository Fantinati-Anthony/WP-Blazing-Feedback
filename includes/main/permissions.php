<?php
/**
 * Vérifications permissions utilisateur
 * 
 * Reference file for blazing-feedback.php lines 590-710
 * See main file: blazing-feedback.php
 * 
 * @package Blazing_Feedback
 */

// To view this section, read blazing-feedback.php with:
// offset=590, limit=121

    private function can_user_see_feedback_widget() {
        // Les utilisateurs non connectés ne peuvent pas voir le widget par défaut
        if ( ! is_user_logged_in() ) {
            /**
             * Filtre pour autoriser les utilisateurs non connectés à voir le widget
             *
             * @since 1.0.0
             * @param bool $allow Autoriser ou non (défaut: false)
             */
            return apply_filters( 'wpvfh_allow_guest_feedback', false );
        }

        // Vérifier les capacités
        return current_user_can( 'publish_feedbacks' ) || current_user_can( 'moderate_feedback' ) || current_user_can( 'manage_feedback' );
    }

    /**
     * Vérifier si les screenshots sont activés
     *
     * @since 1.0.0
     * @return bool
     */
    private function is_screenshot_enabled() {
        /**
         * Filtre pour activer/désactiver les captures d'écran
         *
         * @since 1.0.0
         * @param bool $enabled Activé ou non (défaut: true)
         */
        return apply_filters( 'wpvfh_screenshot_enabled', get_option( 'wpvfh_screenshot_enabled', true ) );
    }

    /**
     * Obtenir tous les groupes de métadonnées pour le frontend
     *
     * Retourne les groupes standards et personnalisés avec leurs items et paramètres
     *
     * @since 1.7.0
     * @return array
     */
    private function get_metadata_groups_for_frontend() {
        $groups = array();

        // Groupes standards
        $standard_groups = array( 'statuses', 'types', 'priorities', 'tags' );

        foreach ( $standard_groups as $slug ) {
            $settings = WPVFH_Options_Manager::get_group_settings( $slug );

            // Vérifier l'accès de l'utilisateur
            if ( ! WPVFH_Options_Manager::user_can_access_group( $slug ) ) {
                continue;
            }

            $groups[ $slug ] = array(
                'slug'     => $slug,
                'name'     => $this->get_group_label( $slug ),
                'type'     => 'standard',
                'settings' => array(
                    'enabled'         => $settings['enabled'],
                    'required'        => $settings['required'],
                    'show_in_sidebar' => $settings['show_in_sidebar'],
                ),
                'items'    => WPVFH_Options_Manager::get_items_by_type( $slug ),
            );
        }

        // Groupes personnalisés
        $custom_groups = WPVFH_Options_Manager::get_custom_groups();

        foreach ( $custom_groups as $slug => $group ) {
            $settings = WPVFH_Options_Manager::get_group_settings( $slug );

            // Vérifier l'accès de l'utilisateur
            if ( ! WPVFH_Options_Manager::user_can_access_group( $slug ) ) {
                continue;
            }

            $groups[ $slug ] = array(
                'slug'     => $slug,
                'name'     => $group['name'],
                'type'     => 'custom',
                'settings' => array(
                    'enabled'         => $settings['enabled'],
                    'required'        => $settings['required'],
                    'show_in_sidebar' => $settings['show_in_sidebar'],
                ),
                'items'    => WPVFH_Options_Manager::get_custom_group_items( $slug ),
            );
        }

        return $groups;
    }

    /**
     * Obtenir le label traduit d'un groupe standard
     *
     * @since 1.7.0
     * @param string $slug Slug du groupe
     * @return string
     */
    private function get_group_label( $slug ) {
        $labels = array(
            'statuses'   => __( 'Statuts', 'blazing-feedback' ),
            'types'      => __( 'Types', 'blazing-feedback' ),
            'priorities' => __( 'Priorités', 'blazing-feedback' ),
            'tags'       => __( 'Tags', 'blazing-feedback' ),
        );

        return isset( $labels[ $slug ] ) ? $labels[ $slug ] : $slug;
    }

    /**
     * Afficher le widget de feedback dans le footer
     *
     * @since 1.0.0
     * @return void
     */
    public function render_feedback_widget() {
        // Vérifier les permissions
        if ( ! $this->can_user_see_feedback_widget() ) {