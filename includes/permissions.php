<?php
/**
 * Gestion des permissions du plugin
 *
 * Définit les capacités et vérifie les droits d'accès
 *
 * @package WP_Visual_Feedback_Hub
 * @since 1.0.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe de gestion des permissions
 *
 * @since 1.0.0
 */
class WPVFH_Permissions {

    /**
     * Liste des capacités du plugin
     *
     * @since 1.0.0
     * @var array
     */
    private static $capabilities = array(
        // Capacités de base (Client)
        'create_feedback'        => true,  // Créer un feedback
        'read_feedback'          => true,  // Lire ses propres feedbacks
        'edit_feedback'          => true,  // Modifier ses propres feedbacks
        'delete_feedback'        => true,  // Supprimer ses propres feedbacks

        // Capacités avancées (Member)
        'read_others_feedback'   => false, // Lire les feedbacks des autres
        'edit_others_feedback'   => false, // Modifier les feedbacks des autres
        'moderate_feedback'      => false, // Modérer (changer statut, répondre)

        // Capacités admin (Admin)
        'delete_others_feedback' => false, // Supprimer les feedbacks des autres
        'manage_feedback'        => false, // Gérer les paramètres
        'export_feedback'        => false, // Exporter les feedbacks
    );

    /**
     * Obtenir la liste des capacités
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_capabilities() {
        /**
         * Filtre la liste des capacités du plugin
         *
         * @since 1.0.0
         * @param array $capabilities Liste des capacités
         */
        return apply_filters( 'wpvfh_capabilities', self::$capabilities );
    }

    /**
     * Vérifier si l'utilisateur peut créer un feedback
     *
     * @since 1.0.0
     * @param int|null $user_id ID de l'utilisateur (optionnel, utilise l'utilisateur courant par défaut)
     * @return bool
     */
    public static function can_create_feedback( $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        // Vérifier si l'utilisateur est connecté
        if ( 0 === $user_id ) {
            /**
             * Filtre pour autoriser les feedbacks anonymes
             *
             * @since 1.0.0
             * @param bool $allow Autoriser ou non (défaut: false)
             */
            return apply_filters( 'wpvfh_allow_anonymous_feedback', false );
        }

        return user_can( $user_id, 'publish_feedbacks' );
    }

    /**
     * Vérifier si l'utilisateur peut lire un feedback
     *
     * @since 1.0.0
     * @param int      $feedback_id ID du feedback
     * @param int|null $user_id     ID de l'utilisateur
     * @return bool
     */
    public static function can_read_feedback( $feedback_id, $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        // Récupérer le feedback
        $feedback = get_post( $feedback_id );
        if ( ! $feedback || 'visual_feedback' !== $feedback->post_type ) {
            return false;
        }

        // L'auteur peut toujours lire son propre feedback
        if ( (int) $feedback->post_author === $user_id ) {
            return user_can( $user_id, 'read_feedback' );
        }

        // Sinon, vérifier la capacité de lire les autres (feedbacks privés)
        return user_can( $user_id, 'read_private_feedbacks' );
    }

    /**
     * Vérifier si l'utilisateur peut modifier un feedback
     *
     * @since 1.0.0
     * @param int      $feedback_id ID du feedback
     * @param int|null $user_id     ID de l'utilisateur
     * @return bool
     */
    public static function can_edit_feedback( $feedback_id, $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        $feedback = get_post( $feedback_id );
        if ( ! $feedback || 'visual_feedback' !== $feedback->post_type ) {
            return false;
        }

        // L'auteur peut modifier son propre feedback
        if ( (int) $feedback->post_author === $user_id ) {
            return user_can( $user_id, 'edit_feedback' );
        }

        // Sinon, vérifier la capacité de modifier les autres
        return user_can( $user_id, 'edit_others_feedbacks' );
    }

    /**
     * Vérifier si l'utilisateur peut supprimer un feedback
     *
     * @since 1.0.0
     * @param int      $feedback_id ID du feedback
     * @param int|null $user_id     ID de l'utilisateur
     * @return bool
     */
    public static function can_delete_feedback( $feedback_id, $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        $feedback = get_post( $feedback_id );
        if ( ! $feedback || 'visual_feedback' !== $feedback->post_type ) {
            return false;
        }

        // L'auteur peut supprimer son propre feedback
        if ( (int) $feedback->post_author === $user_id ) {
            return user_can( $user_id, 'delete_feedback' );
        }

        // Sinon, vérifier la capacité de supprimer les autres
        return user_can( $user_id, 'delete_others_feedbacks' );
    }

    /**
     * Vérifier si l'utilisateur peut modérer les feedbacks
     *
     * @since 1.0.0
     * @param int|null $user_id ID de l'utilisateur
     * @return bool
     */
    public static function can_moderate_feedback( $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        return user_can( $user_id, 'moderate_feedback' );
    }

    /**
     * Vérifier si l'utilisateur peut gérer le plugin
     *
     * @since 1.0.0
     * @param int|null $user_id ID de l'utilisateur
     * @return bool
     */
    public static function can_manage_feedback( $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        return user_can( $user_id, 'manage_feedback' );
    }

    /**
     * Vérifier si l'utilisateur peut répondre à un feedback
     *
     * @since 1.0.0
     * @param int      $feedback_id ID du feedback
     * @param int|null $user_id     ID de l'utilisateur
     * @return bool
     */
    public static function can_reply_to_feedback( $feedback_id, $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        // L'auteur peut répondre à son propre feedback
        $feedback = get_post( $feedback_id );
        if ( $feedback && (int) $feedback->post_author === $user_id ) {
            return true;
        }

        // Les modérateurs peuvent répondre
        return self::can_moderate_feedback( $user_id );
    }

    /**
     * Vérifier si l'utilisateur peut changer le statut d'un feedback
     *
     * @since 1.0.0
     * @param int      $feedback_id ID du feedback
     * @param int|null $user_id     ID de l'utilisateur
     * @return bool
     */
    public static function can_change_status( $feedback_id, $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        // Seuls les modérateurs peuvent changer le statut
        return self::can_moderate_feedback( $user_id );
    }

    /**
     * Vérifier si l'utilisateur peut exporter les feedbacks
     *
     * @since 1.0.0
     * @param int|null $user_id ID de l'utilisateur
     * @return bool
     */
    public static function can_export_feedback( $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        return user_can( $user_id, 'export_feedback' );
    }

    /**
     * Vérifier le nonce de sécurité
     *
     * @since 1.0.0
     * @param string $nonce  Valeur du nonce
     * @param string $action Action associée au nonce
     * @return bool
     */
    public static function verify_nonce( $nonce, $action = 'wpvfh_nonce' ) {
        return wp_verify_nonce( $nonce, $action );
    }

    /**
     * Vérifier le nonce REST API
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return bool|WP_Error
     */
    public static function verify_rest_nonce( $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );

        if ( ! $nonce ) {
            return new WP_Error(
                'rest_missing_nonce',
                __( 'Nonce de sécurité manquant.', 'blazing-feedback' ),
                array( 'status' => 401 )
            );
        }

        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new WP_Error(
                'rest_invalid_nonce',
                __( 'Nonce de sécurité invalide.', 'blazing-feedback' ),
                array( 'status' => 403 )
            );
        }

        return true;
    }

    /**
     * Obtenir l'ID utilisateur depuis une requête REST
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return int ID de l'utilisateur
     */
    public static function get_user_from_request( $request ) {
        return get_current_user_id();
    }

    /**
     * Assainir et valider une URL
     *
     * @since 1.0.0
     * @param string $url URL à valider
     * @return string|false URL assainie ou false si invalide
     */
    public static function sanitize_url( $url ) {
        $url = esc_url_raw( $url );

        // Vérifier que l'URL appartient au site
        $site_url = parse_url( home_url() );
        $feedback_url = parse_url( $url );

        if ( ! isset( $feedback_url['host'] ) || $feedback_url['host'] !== $site_url['host'] ) {
            /**
             * Filtre pour autoriser les URLs externes
             *
             * @since 1.0.0
             * @param bool   $allow Autoriser ou non (défaut: false)
             * @param string $url   URL à vérifier
             */
            if ( ! apply_filters( 'wpvfh_allow_external_urls', false, $url ) ) {
                return false;
            }
        }

        return $url;
    }

    /**
     * Assainir les données de feedback
     *
     * @since 1.0.0
     * @param array $data Données à assainir
     * @return array Données assainies
     */
    public static function sanitize_feedback_data( $data ) {
        $sanitized = array();

        // Commentaire
        if ( isset( $data['comment'] ) ) {
            $sanitized['comment'] = sanitize_textarea_field( $data['comment'] );
        }

        // URL
        if ( isset( $data['url'] ) ) {
            $sanitized['url'] = self::sanitize_url( $data['url'] );
        }

        // Position X
        if ( isset( $data['position_x'] ) ) {
            $sanitized['position_x'] = floatval( $data['position_x'] );
        }

        // Position Y
        if ( isset( $data['position_y'] ) ) {
            $sanitized['position_y'] = floatval( $data['position_y'] );
        }

        // Résolution écran
        if ( isset( $data['screen_width'] ) ) {
            $sanitized['screen_width'] = absint( $data['screen_width'] );
        }
        if ( isset( $data['screen_height'] ) ) {
            $sanitized['screen_height'] = absint( $data['screen_height'] );
        }

        // Viewport
        if ( isset( $data['viewport_width'] ) ) {
            $sanitized['viewport_width'] = absint( $data['viewport_width'] );
        }
        if ( isset( $data['viewport_height'] ) ) {
            $sanitized['viewport_height'] = absint( $data['viewport_height'] );
        }

        // Navigateur / OS
        if ( isset( $data['browser'] ) ) {
            $sanitized['browser'] = sanitize_text_field( $data['browser'] );
        }
        if ( isset( $data['os'] ) ) {
            $sanitized['os'] = sanitize_text_field( $data['os'] );
        }
        if ( isset( $data['device'] ) ) {
            $sanitized['device'] = sanitize_text_field( $data['device'] );
        }
        if ( isset( $data['user_agent'] ) ) {
            $sanitized['user_agent'] = sanitize_text_field( $data['user_agent'] );
        }

        // Statut
        if ( isset( $data['status'] ) ) {
            $allowed_statuses = array_keys( WPVFH_CPT_Feedback::get_statuses() );
            $sanitized['status'] = in_array( $data['status'], $allowed_statuses, true )
                ? $data['status']
                : 'new';
        }

        /**
         * Filtre les données de feedback assainies
         *
         * @since 1.0.0
         * @param array $sanitized Données assainies
         * @param array $data      Données originales
         */
        return apply_filters( 'wpvfh_sanitized_feedback_data', $sanitized, $data );
    }

    /**
     * Valider les données de screenshot (base64)
     *
     * @since 1.0.0
     * @param string $data Données base64 de l'image
     * @return bool
     */
    public static function validate_screenshot_data( $data ) {
        // Vérifier le préfixe data URI
        if ( ! preg_match( '/^data:image\/(png|jpeg|jpg|gif|webp);base64,/', $data ) ) {
            return false;
        }

        // Extraire les données base64
        $base64_data = preg_replace( '/^data:image\/\w+;base64,/', '', $data );

        // Vérifier que c'est du base64 valide
        if ( base64_decode( $base64_data, true ) === false ) {
            return false;
        }

        // Vérifier la taille maximale (5 Mo par défaut)
        $max_size = apply_filters( 'wpvfh_max_screenshot_size', 5 * 1024 * 1024 );
        $decoded_size = strlen( base64_decode( $base64_data ) );

        if ( $decoded_size > $max_size ) {
            return false;
        }

        return true;
    }
}
