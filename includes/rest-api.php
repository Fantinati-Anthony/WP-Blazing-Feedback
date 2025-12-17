<?php
/**
 * REST API Endpoints pour Blazing Feedback
 *
 * Expose les endpoints pour créer, lire, modifier les feedbacks
 * Namespace: /wp-json/blazing-feedback/v1/
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe de gestion de la REST API
 *
 * @since 1.0.0
 */
class WPVFH_REST_API {

    /**
     * Namespace de l'API
     *
     * @since 1.0.0
     * @var string
     */
    const NAMESPACE = 'blazing-feedback/v1';

    /**
     * Initialiser l'API
     *
     * @since 1.0.0
     * @return void
     */
    public static function init() {
        add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
    }

    /**
     * Enregistrer les routes de l'API
     *
     * @since 1.0.0
     * @return void
     */
    public static function register_routes() {
        // GET /feedbacks - Liste des feedbacks
        register_rest_route( self::NAMESPACE, '/feedbacks', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( __CLASS__, 'get_feedbacks' ),
                'permission_callback' => array( __CLASS__, 'can_read_feedbacks' ),
                'args'                => self::get_collection_params(),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( __CLASS__, 'create_feedback' ),
                'permission_callback' => array( __CLASS__, 'can_create_feedback' ),
                'args'                => self::get_create_params(),
            ),
        ) );

        // GET/PUT/DELETE /feedbacks/{id}
        register_rest_route( self::NAMESPACE, '/feedbacks/(?P<id>\d+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( __CLASS__, 'get_feedback' ),
                'permission_callback' => array( __CLASS__, 'can_read_single_feedback' ),
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'type'              => 'integer',
                        'sanitize_callback' => function( $value ) { return absint( $value ); },
                    ),
                ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( __CLASS__, 'update_feedback' ),
                'permission_callback' => array( __CLASS__, 'can_edit_feedback' ),
                'args'                => self::get_update_params(),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( __CLASS__, 'delete_feedback' ),
                'permission_callback' => array( __CLASS__, 'can_delete_feedback' ),
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'type'              => 'integer',
                        'sanitize_callback' => function( $value ) { return absint( $value ); },
                    ),
                ),
            ),
        ) );

        // POST /feedbacks/{id}/replies - Ajouter une réponse
        register_rest_route( self::NAMESPACE, '/feedbacks/(?P<id>\d+)/replies', array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( __CLASS__, 'add_reply' ),
                'permission_callback' => array( __CLASS__, 'can_reply_to_feedback' ),
                'args'                => array(
                    'id'      => array(
                        'required'          => true,
                        'type'              => 'integer',
                        'sanitize_callback' => function( $value ) { return absint( $value ); },
                    ),
                    'content' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ),
                ),
            ),
        ) );

        // PUT /feedbacks/{id}/status - Changer le statut
        register_rest_route( self::NAMESPACE, '/feedbacks/(?P<id>\d+)/status', array(
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( __CLASS__, 'update_status' ),
                'permission_callback' => array( __CLASS__, 'can_change_status' ),
                'args'                => array(
                    'id'     => array(
                        'required'          => true,
                        'type'              => 'integer',
                        'sanitize_callback' => function( $value ) { return absint( $value ); },
                    ),
                    'status' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'enum'              => array_keys( WPVFH_CPT_Feedback::get_statuses() ),
                        'sanitize_callback' => 'sanitize_key',
                    ),
                ),
            ),
        ) );

        // POST /feedbacks/{id}/screenshot - Upload un screenshot
        register_rest_route( self::NAMESPACE, '/feedbacks/(?P<id>\d+)/screenshot', array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( __CLASS__, 'upload_screenshot' ),
                'permission_callback' => array( __CLASS__, 'can_edit_feedback' ),
                'args'                => array(
                    'id'   => array(
                        'required'          => true,
                        'type'              => 'integer',
                        'sanitize_callback' => function( $value ) { return absint( $value ); },
                    ),
                    'data' => array(
                        'required'    => true,
                        'type'        => 'string',
                        'description' => __( 'Image en base64 (data:image/...)', 'blazing-feedback' ),
                    ),
                ),
            ),
        ) );

        // GET /feedbacks/by-url - Feedbacks par URL
        register_rest_route( self::NAMESPACE, '/feedbacks/by-url', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( __CLASS__, 'get_feedbacks_by_url' ),
                'permission_callback' => array( __CLASS__, 'can_read_feedbacks' ),
                'args'                => array(
                    'url' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'esc_url_raw',
                    ),
                    'include_resolved' => array(
                        'type'    => 'boolean',
                        'default' => false,
                    ),
                ),
            ),
        ) );

        // GET /statuses - Liste des statuts
        register_rest_route( self::NAMESPACE, '/statuses', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( __CLASS__, 'get_statuses' ),
                'permission_callback' => '__return_true',
            ),
        ) );

        // GET /stats - Statistiques (admin)
        register_rest_route( self::NAMESPACE, '/stats', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( __CLASS__, 'get_stats' ),
                'permission_callback' => array( __CLASS__, 'can_manage_feedback' ),
            ),
        ) );

        // GET /pages - Liste des pages avec feedbacks
        register_rest_route( self::NAMESPACE, '/pages', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( __CLASS__, 'get_pages' ),
                'permission_callback' => array( __CLASS__, 'can_read_feedbacks' ),
            ),
        ) );

        // POST /pages/validate - Valider une page
        register_rest_route( self::NAMESPACE, '/pages/validate', array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( __CLASS__, 'validate_page' ),
                'permission_callback' => array( __CLASS__, 'can_validate_page' ),
                'args'                => array(
                    'url' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'esc_url_raw',
                    ),
                ),
            ),
        ) );

        // GET /users - Liste des utilisateurs (pour mentions)
        register_rest_route( self::NAMESPACE, '/users', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( __CLASS__, 'get_users' ),
                'permission_callback' => array( __CLASS__, 'can_read_feedbacks' ),
                'args'                => array(
                    'search' => array(
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            ),
        ) );

        /**
         * Action pour enregistrer des routes supplémentaires
         *
         * @since 1.0.0
         * @param string $namespace Namespace de l'API
         */
        do_action( 'wpvfh_register_rest_routes', self::NAMESPACE );
    }

    /**
     * Paramètres pour la collection de feedbacks
     *
     * @since 1.0.0
     * @return array
     */
    private static function get_collection_params() {
        return array(
            'page'     => array(
                'type'              => 'integer',
                'default'           => 1,
                'minimum'           => 1,
                'sanitize_callback' => function( $value ) { return absint( $value ); },
            ),
            'per_page' => array(
                'type'              => 'integer',
                'default'           => 20,
                'minimum'           => 1,
                'maximum'           => 100,
                'sanitize_callback' => function( $value ) { return absint( $value ); },
            ),
            'status'   => array(
                'type'              => 'string',
                'enum'              => array_merge( array( '' ), array_keys( WPVFH_CPT_Feedback::get_statuses() ) ),
                'sanitize_callback' => 'sanitize_key',
            ),
            'author'   => array(
                'type'              => 'integer',
                'sanitize_callback' => function( $value ) { return absint( $value ); },
            ),
            'search'   => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'orderby'  => array(
                'type'    => 'string',
                'default' => 'date',
                'enum'    => array( 'date', 'title', 'modified', 'author' ),
            ),
            'order'    => array(
                'type'    => 'string',
                'default' => 'DESC',
                'enum'    => array( 'ASC', 'DESC' ),
            ),
        );
    }

    /**
     * Paramètres pour créer un feedback
     *
     * @since 1.0.0
     * @return array
     */
    private static function get_create_params() {
        return array(
            'comment'         => array(
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
                'description'       => __( 'Contenu du feedback', 'blazing-feedback' ),
            ),
            'url'             => array(
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'description'       => __( 'URL de la page concernée', 'blazing-feedback' ),
            ),
            'position_x'      => array(
                'type'              => 'number',
                'sanitize_callback' => function( $value ) { return floatval( $value ); },
                'description'       => __( 'Position X du marqueur (%)', 'blazing-feedback' ),
            ),
            'position_y'      => array(
                'type'              => 'number',
                'sanitize_callback' => function( $value ) { return floatval( $value ); },
                'description'       => __( 'Position Y du marqueur (%)', 'blazing-feedback' ),
            ),
            'screenshot_data' => array(
                'type'              => 'string',
                'description'       => __( 'Screenshot en base64', 'blazing-feedback' ),
                'validate_callback' => function( $value ) {
                    // Accepter null, chaîne vide, ou chaîne valide
                    return $value === null || is_string( $value );
                },
                'sanitize_callback' => function( $value ) {
                    // Retourner tel quel pour les données base64
                    return is_string( $value ) ? $value : '';
                },
            ),
            'screen_width'    => array(
                'type'              => 'integer',
                'sanitize_callback' => function( $value ) { return absint( $value ); },
            ),
            'screen_height'   => array(
                'type'              => 'integer',
                'sanitize_callback' => function( $value ) { return absint( $value ); },
            ),
            'viewport_width'  => array(
                'type'              => 'integer',
                'sanitize_callback' => function( $value ) { return absint( $value ); },
            ),
            'viewport_height' => array(
                'type'              => 'integer',
                'sanitize_callback' => function( $value ) { return absint( $value ); },
            ),
            'browser'         => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'os'              => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'device'          => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'user_agent'      => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'selector'        => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'description'       => __( 'Sélecteur CSS de l\'élément', 'blazing-feedback' ),
            ),
            'element_offset_x' => array(
                'type'              => 'number',
                'sanitize_callback' => function( $value ) { return floatval( $value ); },
                'description'       => __( 'Offset X dans l\'élément (%)', 'blazing-feedback' ),
            ),
            'element_offset_y' => array(
                'type'              => 'number',
                'sanitize_callback' => function( $value ) { return floatval( $value ); },
                'description'       => __( 'Offset Y dans l\'élément (%)', 'blazing-feedback' ),
            ),
            'scroll_x'        => array(
                'type'              => 'integer',
                'sanitize_callback' => function( $value ) { return absint( $value ); },
            ),
            'scroll_y'        => array(
                'type'              => 'integer',
                'sanitize_callback' => function( $value ) { return absint( $value ); },
            ),
        );
    }

    /**
     * Paramètres pour mettre à jour un feedback
     *
     * @since 1.0.0
     * @return array
     */
    private static function get_update_params() {
        $params = self::get_create_params();

        // Rendre tous les paramètres optionnels pour la mise à jour
        foreach ( $params as $key => $param ) {
            $params[ $key ]['required'] = false;
        }

        $params['id'] = array(
            'required'          => true,
            'type'              => 'integer',
            'sanitize_callback' => function( $value ) { return absint( $value ); },
        );

        $params['status'] = array(
            'type'              => 'string',
            'enum'              => array_keys( WPVFH_CPT_Feedback::get_statuses() ),
            'sanitize_callback' => 'sanitize_key',
        );

        return $params;
    }

    // ========================================
    // Permission Callbacks
    // ========================================

    /**
     * Vérifier si l'utilisateur peut lire les feedbacks
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return bool|WP_Error
     */
    public static function can_read_feedbacks( $request ) {
        // Vérifier le nonce
        $nonce_check = WPVFH_Permissions::verify_rest_nonce( $request );
        if ( is_wp_error( $nonce_check ) ) {
            return $nonce_check;
        }

        // Les utilisateurs qui peuvent créer ou modérer peuvent aussi lire
        return current_user_can( 'read_feedback' ) ||
               current_user_can( 'publish_feedbacks' ) ||
               current_user_can( 'moderate_feedback' );
    }

    /**
     * Vérifier si l'utilisateur peut lire un feedback spécifique
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return bool|WP_Error
     */
    public static function can_read_single_feedback( $request ) {
        $nonce_check = WPVFH_Permissions::verify_rest_nonce( $request );
        if ( is_wp_error( $nonce_check ) ) {
            return $nonce_check;
        }

        $feedback_id = $request->get_param( 'id' );
        return WPVFH_Permissions::can_read_feedback( $feedback_id );
    }

    /**
     * Vérifier si l'utilisateur peut créer un feedback
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return bool|WP_Error
     */
    public static function can_create_feedback( $request ) {
        $nonce_check = WPVFH_Permissions::verify_rest_nonce( $request );
        if ( is_wp_error( $nonce_check ) ) {
            return $nonce_check;
        }

        return WPVFH_Permissions::can_create_feedback();
    }

    /**
     * Vérifier si l'utilisateur peut modifier un feedback
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return bool|WP_Error
     */
    public static function can_edit_feedback( $request ) {
        $nonce_check = WPVFH_Permissions::verify_rest_nonce( $request );
        if ( is_wp_error( $nonce_check ) ) {
            return $nonce_check;
        }

        $feedback_id = $request->get_param( 'id' );
        return WPVFH_Permissions::can_edit_feedback( $feedback_id );
    }

    /**
     * Vérifier si l'utilisateur peut supprimer un feedback
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return bool|WP_Error
     */
    public static function can_delete_feedback( $request ) {
        $nonce_check = WPVFH_Permissions::verify_rest_nonce( $request );
        if ( is_wp_error( $nonce_check ) ) {
            return $nonce_check;
        }

        $feedback_id = $request->get_param( 'id' );
        return WPVFH_Permissions::can_delete_feedback( $feedback_id );
    }

    /**
     * Vérifier si l'utilisateur peut répondre à un feedback
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return bool|WP_Error
     */
    public static function can_reply_to_feedback( $request ) {
        $nonce_check = WPVFH_Permissions::verify_rest_nonce( $request );
        if ( is_wp_error( $nonce_check ) ) {
            return $nonce_check;
        }

        $feedback_id = $request->get_param( 'id' );
        return WPVFH_Permissions::can_reply_to_feedback( $feedback_id );
    }

    /**
     * Vérifier si l'utilisateur peut changer le statut
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return bool|WP_Error
     */
    public static function can_change_status( $request ) {
        $nonce_check = WPVFH_Permissions::verify_rest_nonce( $request );
        if ( is_wp_error( $nonce_check ) ) {
            return $nonce_check;
        }

        $feedback_id = $request->get_param( 'id' );
        return WPVFH_Permissions::can_change_status( $feedback_id );
    }

    /**
     * Vérifier si l'utilisateur peut gérer les feedbacks
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return bool|WP_Error
     */
    public static function can_manage_feedback( $request ) {
        $nonce_check = WPVFH_Permissions::verify_rest_nonce( $request );
        if ( is_wp_error( $nonce_check ) ) {
            return $nonce_check;
        }

        return WPVFH_Permissions::can_manage_feedback();
    }

    /**
     * Vérifier si l'utilisateur peut valider une page
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return bool|WP_Error
     */
    public static function can_validate_page( $request ) {
        $nonce_check = WPVFH_Permissions::verify_rest_nonce( $request );
        if ( is_wp_error( $nonce_check ) ) {
            return $nonce_check;
        }

        // Admin peut toujours valider
        if ( WPVFH_Permissions::can_manage_feedback() ) {
            return true;
        }

        // Pour les autres, vérifier si tous les feedbacks sont résolus
        $url = $request->get_param( 'url' );
        return self::check_page_can_be_validated( $url );
    }

    /**
     * Vérifier si une page peut être validée (tous les feedbacks résolus)
     *
     * @since 1.0.0
     * @param string $url URL de la page
     * @return bool
     */
    private static function check_page_can_be_validated( $url ) {
        $parsed = wp_parse_url( $url );
        $path = isset( $parsed['path'] ) ? $parsed['path'] : '/';
        $path = rtrim( $path, '/' );
        if ( empty( $path ) ) {
            $path = '/';
        }

        $args = array(
            'post_type'      => WPVFH_CPT_Feedback::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_wpvfh_url',
                    'value'   => $path,
                    'compare' => 'LIKE',
                ),
            ),
            'fields'         => 'ids',
        );

        $query = new WP_Query( $args );

        if ( $query->found_posts === 0 ) {
            return true; // Pas de feedback = peut valider
        }

        // Vérifier que tous sont resolved ou rejected
        foreach ( $query->posts as $post_id ) {
            $status = get_post_meta( $post_id, '_wpvfh_status', true );
            if ( ! in_array( $status, array( 'resolved', 'rejected' ), true ) ) {
                return false;
            }
        }

        return true;
    }

    // ========================================
    // Route Callbacks
    // ========================================

    /**
     * Obtenir la liste des feedbacks
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response
     */
    public static function get_feedbacks( $request ) {
        $args = array(
            'post_type'      => WPVFH_CPT_Feedback::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => $request->get_param( 'per_page' ),
            'paged'          => $request->get_param( 'page' ),
            'orderby'        => $request->get_param( 'orderby' ),
            'order'          => $request->get_param( 'order' ),
        );

        // Filtre par statut
        if ( $status = $request->get_param( 'status' ) ) {
            $args['meta_query'][] = array(
                'key'   => '_wpvfh_status',
                'value' => $status,
            );
        }

        // Filtre par auteur
        if ( $author = $request->get_param( 'author' ) ) {
            $args['author'] = $author;
        }

        // Recherche
        if ( $search = $request->get_param( 'search' ) ) {
            $args['s'] = $search;
        }

        // Restreindre aux feedbacks de l'utilisateur si pas de capacité read_others_feedback
        if ( ! current_user_can( 'read_others_feedback' ) ) {
            $args['author'] = get_current_user_id();
        }

        /**
         * Filtre les arguments de la requête de feedbacks
         *
         * @since 1.0.0
         * @param array           $args    Arguments de la requête
         * @param WP_REST_Request $request Requête REST
         */
        $args = apply_filters( 'wpvfh_rest_feedbacks_args', $args, $request );

        $query = new WP_Query( $args );
        $feedbacks = array();

        foreach ( $query->posts as $post ) {
            $feedbacks[] = WPVFH_CPT_Feedback::get_feedback_data( $post );
        }

        $response = new WP_REST_Response( $feedbacks );
        $response->header( 'X-WP-Total', $query->found_posts );
        $response->header( 'X-WP-TotalPages', $query->max_num_pages );

        return $response;
    }

    /**
     * Obtenir un feedback spécifique
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response|WP_Error
     */
    public static function get_feedback( $request ) {
        $feedback_id = $request->get_param( 'id' );
        $data = WPVFH_CPT_Feedback::get_feedback_data( $feedback_id );

        if ( ! $data ) {
            return new WP_Error(
                'feedback_not_found',
                __( 'Feedback non trouvé.', 'blazing-feedback' ),
                array( 'status' => 404 )
            );
        }

        return new WP_REST_Response( $data );
    }

    /**
     * Créer un nouveau feedback
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response|WP_Error
     */
    public static function create_feedback( $request ) {
        $data = array(
            'comment'          => $request->get_param( 'comment' ),
            'url'              => $request->get_param( 'url' ),
            'position_x'       => $request->get_param( 'position_x' ),
            'position_y'       => $request->get_param( 'position_y' ),
            'screen_width'     => $request->get_param( 'screen_width' ),
            'screen_height'    => $request->get_param( 'screen_height' ),
            'viewport_width'   => $request->get_param( 'viewport_width' ),
            'viewport_height'  => $request->get_param( 'viewport_height' ),
            'browser'          => $request->get_param( 'browser' ),
            'os'               => $request->get_param( 'os' ),
            'device'           => $request->get_param( 'device' ),
            'user_agent'       => $request->get_param( 'user_agent' ),
            'selector'         => $request->get_param( 'selector' ),
            'element_offset_x' => $request->get_param( 'element_offset_x' ),
            'element_offset_y' => $request->get_param( 'element_offset_y' ),
            'scroll_x'         => $request->get_param( 'scroll_x' ),
            'scroll_y'         => $request->get_param( 'scroll_y' ),
        );

        // Créer le feedback
        $feedback_id = WPVFH_CPT_Feedback::create_feedback( $data );

        if ( is_wp_error( $feedback_id ) ) {
            return $feedback_id;
        }

        // Upload du screenshot si fourni
        $screenshot_data = $request->get_param( 'screenshot_data' );
        if ( $screenshot_data && WPVFH_Permissions::validate_screenshot_data( $screenshot_data ) ) {
            $screenshot_id = self::save_screenshot( $screenshot_data, $feedback_id );
            if ( ! is_wp_error( $screenshot_id ) ) {
                update_post_meta( $feedback_id, '_wpvfh_screenshot_id', $screenshot_id );
            }
        }

        // Récupérer les données complètes du feedback créé
        $feedback_data = WPVFH_CPT_Feedback::get_feedback_data( $feedback_id );

        /**
         * Action après création d'un feedback via l'API
         *
         * @since 1.0.0
         * @param int             $feedback_id ID du feedback
         * @param array           $data        Données du feedback
         * @param WP_REST_Request $request     Requête REST
         */
        do_action( 'wpvfh_rest_feedback_created', $feedback_id, $data, $request );

        return new WP_REST_Response( $feedback_data, 201 );
    }

    /**
     * Mettre à jour un feedback
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response|WP_Error
     */
    public static function update_feedback( $request ) {
        $feedback_id = $request->get_param( 'id' );

        $post = get_post( $feedback_id );
        if ( ! $post || WPVFH_CPT_Feedback::POST_TYPE !== $post->post_type ) {
            return new WP_Error(
                'feedback_not_found',
                __( 'Feedback non trouvé.', 'blazing-feedback' ),
                array( 'status' => 404 )
            );
        }

        $update_data = array();

        // Mise à jour du commentaire
        if ( $comment = $request->get_param( 'comment' ) ) {
            wp_update_post( array(
                'ID'           => $feedback_id,
                'post_title'   => wp_trim_words( $comment, 10, '...' ),
                'post_content' => $comment,
            ) );
        }

        // Mise à jour des métadonnées
        $meta_fields = array(
            'position_x', 'position_y', 'screen_width', 'screen_height',
            'viewport_width', 'viewport_height', 'browser', 'os', 'device',
            'user_agent', 'selector', 'element_offset_x', 'element_offset_y',
            'scroll_x', 'scroll_y',
        );

        foreach ( $meta_fields as $field ) {
            $value = $request->get_param( $field );
            if ( null !== $value ) {
                update_post_meta( $feedback_id, '_wpvfh_' . $field, $value );
            }
        }

        // Mise à jour du statut
        if ( $status = $request->get_param( 'status' ) ) {
            update_post_meta( $feedback_id, '_wpvfh_status', $status );
            wp_set_object_terms( $feedback_id, $status, WPVFH_CPT_Feedback::TAX_STATUS );

            /**
             * Action après changement de statut via l'API
             *
             * @since 1.0.0
             * @param int    $feedback_id ID du feedback
             * @param string $status      Nouveau statut
             */
            do_action( 'wpvfh_rest_status_changed', $feedback_id, $status );
        }

        /**
         * Action après mise à jour d'un feedback via l'API
         *
         * @since 1.0.0
         * @param int             $feedback_id ID du feedback
         * @param WP_REST_Request $request     Requête REST
         */
        do_action( 'wpvfh_rest_feedback_updated', $feedback_id, $request );

        return new WP_REST_Response( WPVFH_CPT_Feedback::get_feedback_data( $feedback_id ) );
    }

    /**
     * Supprimer un feedback
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response|WP_Error
     */
    public static function delete_feedback( $request ) {
        $feedback_id = $request->get_param( 'id' );

        $post = get_post( $feedback_id );
        if ( ! $post || WPVFH_CPT_Feedback::POST_TYPE !== $post->post_type ) {
            return new WP_Error(
                'feedback_not_found',
                __( 'Feedback non trouvé.', 'blazing-feedback' ),
                array( 'status' => 404 )
            );
        }

        // Supprimer le screenshot associé
        $screenshot_id = get_post_meta( $feedback_id, '_wpvfh_screenshot_id', true );
        if ( $screenshot_id ) {
            wp_delete_attachment( $screenshot_id, true );
        }

        // Supprimer le feedback
        $deleted = wp_delete_post( $feedback_id, true );

        if ( ! $deleted ) {
            return new WP_Error(
                'delete_failed',
                __( 'Échec de la suppression.', 'blazing-feedback' ),
                array( 'status' => 500 )
            );
        }

        /**
         * Action après suppression d'un feedback via l'API
         *
         * @since 1.0.0
         * @param int $feedback_id ID du feedback supprimé
         */
        do_action( 'wpvfh_rest_feedback_deleted', $feedback_id );

        return new WP_REST_Response( array(
            'deleted'  => true,
            'id'       => $feedback_id,
            'message'  => __( 'Feedback supprimé avec succès.', 'blazing-feedback' ),
        ) );
    }

    /**
     * Ajouter une réponse à un feedback
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response|WP_Error
     */
    public static function add_reply( $request ) {
        $feedback_id = $request->get_param( 'id' );
        $content = $request->get_param( 'content' );

        $post = get_post( $feedback_id );
        if ( ! $post || WPVFH_CPT_Feedback::POST_TYPE !== $post->post_type ) {
            return new WP_Error(
                'feedback_not_found',
                __( 'Feedback non trouvé.', 'blazing-feedback' ),
                array( 'status' => 404 )
            );
        }

        $current_user = wp_get_current_user();

        $comment_data = array(
            'comment_post_ID'      => $feedback_id,
            'comment_content'      => $content,
            'comment_type'         => 'comment',
            'comment_author'       => $current_user->display_name,
            'comment_author_email' => $current_user->user_email,
            'user_id'              => $current_user->ID,
            'comment_approved'     => 1,
        );

        $comment_id = wp_insert_comment( $comment_data );

        if ( ! $comment_id ) {
            return new WP_Error(
                'reply_failed',
                __( 'Échec de l\'ajout de la réponse.', 'blazing-feedback' ),
                array( 'status' => 500 )
            );
        }

        $comment = get_comment( $comment_id );

        /**
         * Action après ajout d'une réponse via l'API
         *
         * @since 1.0.0
         * @param int             $comment_id  ID du commentaire
         * @param int             $feedback_id ID du feedback
         * @param WP_REST_Request $request     Requête REST
         */
        do_action( 'wpvfh_rest_reply_added', $comment_id, $feedback_id, $request );

        return new WP_REST_Response( array(
            'id'      => $comment_id,
            'content' => $comment->comment_content,
            'date'    => $comment->comment_date,
            'author'  => array(
                'id'     => $comment->user_id,
                'name'   => $comment->comment_author,
                'avatar' => get_avatar_url( $comment->comment_author_email, array( 'size' => 32 ) ),
            ),
        ), 201 );
    }

    /**
     * Mettre à jour le statut d'un feedback
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response|WP_Error
     */
    public static function update_status( $request ) {
        $feedback_id = $request->get_param( 'id' );
        $status = $request->get_param( 'status' );

        $post = get_post( $feedback_id );
        if ( ! $post || WPVFH_CPT_Feedback::POST_TYPE !== $post->post_type ) {
            return new WP_Error(
                'feedback_not_found',
                __( 'Feedback non trouvé.', 'blazing-feedback' ),
                array( 'status' => 404 )
            );
        }

        $old_status = get_post_meta( $feedback_id, '_wpvfh_status', true );

        update_post_meta( $feedback_id, '_wpvfh_status', $status );
        wp_set_object_terms( $feedback_id, $status, WPVFH_CPT_Feedback::TAX_STATUS );

        /**
         * Action après changement de statut via l'API
         *
         * @since 1.0.0
         * @param int    $feedback_id ID du feedback
         * @param string $status      Nouveau statut
         * @param string $old_status  Ancien statut
         */
        do_action( 'wpvfh_rest_status_updated', $feedback_id, $status, $old_status );

        $statuses = WPVFH_CPT_Feedback::get_statuses();

        return new WP_REST_Response( array(
            'id'         => $feedback_id,
            'status'     => $status,
            'old_status' => $old_status,
            'label'      => $statuses[ $status ]['label'] ?? $status,
        ) );
    }

    /**
     * Upload un screenshot pour un feedback
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response|WP_Error
     */
    public static function upload_screenshot( $request ) {
        $feedback_id = $request->get_param( 'id' );
        $data = $request->get_param( 'data' );

        // Valider les données
        if ( ! WPVFH_Permissions::validate_screenshot_data( $data ) ) {
            return new WP_Error(
                'invalid_screenshot',
                __( 'Données de screenshot invalides.', 'blazing-feedback' ),
                array( 'status' => 400 )
            );
        }

        // Sauvegarder le screenshot
        $screenshot_id = self::save_screenshot( $data, $feedback_id );

        if ( is_wp_error( $screenshot_id ) ) {
            return $screenshot_id;
        }

        // Mettre à jour le feedback
        update_post_meta( $feedback_id, '_wpvfh_screenshot_id', $screenshot_id );

        return new WP_REST_Response( array(
            'id'  => $screenshot_id,
            'url' => wp_get_attachment_url( $screenshot_id ),
        ), 201 );
    }

    /**
     * Obtenir les feedbacks par URL
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response
     */
    public static function get_feedbacks_by_url( $request ) {
        $url = $request->get_param( 'url' );
        $include_resolved = $request->get_param( 'include_resolved' );

        // Extraire le path de l'URL pour une recherche plus flexible
        $parsed = wp_parse_url( $url );
        $path = isset( $parsed['path'] ) ? $parsed['path'] : '/';

        // Nettoyer le path (enlever trailing slash sauf pour la racine)
        $path = rtrim( $path, '/' );
        if ( empty( $path ) ) {
            $path = '/';
        }

        $args = array(
            'post_type'      => WPVFH_CPT_Feedback::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_wpvfh_url',
                    'value'   => $path,
                    'compare' => 'LIKE',
                ),
            ),
        );

        // Exclure les feedbacks résolus/rejetés si demandé
        if ( ! $include_resolved ) {
            $args['meta_query'][] = array(
                'key'     => '_wpvfh_status',
                'value'   => array( 'resolved', 'rejected' ),
                'compare' => 'NOT IN',
            );
        }

        // Restreindre aux feedbacks de l'utilisateur si pas de capacité read_others_feedback
        if ( ! current_user_can( 'read_others_feedback' ) ) {
            $args['author'] = get_current_user_id();
        }

        $query = new WP_Query( $args );
        $feedbacks = array();

        foreach ( $query->posts as $post ) {
            $feedbacks[] = WPVFH_CPT_Feedback::get_feedback_data( $post );
        }

        return new WP_REST_Response( $feedbacks );
    }

    /**
     * Obtenir les statuts disponibles
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response
     */
    public static function get_statuses( $request ) {
        return new WP_REST_Response( WPVFH_CPT_Feedback::get_statuses() );
    }

    /**
     * Obtenir les statistiques des feedbacks
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response
     */
    public static function get_stats( $request ) {
        global $wpdb;

        $stats = array(
            'total'       => 0,
            'by_status'   => array(),
            'by_page'     => array(),
            'recent'      => array(),
        );

        // Total
        $stats['total'] = wp_count_posts( WPVFH_CPT_Feedback::POST_TYPE )->publish;

        // Par statut
        $statuses = WPVFH_CPT_Feedback::get_statuses();
        foreach ( array_keys( $statuses ) as $status ) {
            $count = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = '_wpvfh_status'
                AND pm.meta_value = %s
                AND p.post_type = %s
                AND p.post_status = 'publish'",
                $status,
                WPVFH_CPT_Feedback::POST_TYPE
            ) );
            $stats['by_status'][ $status ] = (int) $count;
        }

        // Par page (top 10)
        $pages = $wpdb->get_results( $wpdb->prepare(
            "SELECT pm.meta_value as url, COUNT(*) as count
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_wpvfh_url'
            AND p.post_type = %s
            AND p.post_status = 'publish'
            GROUP BY pm.meta_value
            ORDER BY count DESC
            LIMIT 10",
            WPVFH_CPT_Feedback::POST_TYPE
        ) );

        foreach ( $pages as $page ) {
            $stats['by_page'][] = array(
                'url'   => $page->url,
                'path'  => wp_parse_url( $page->url, PHP_URL_PATH ) ?: '/',
                'count' => (int) $page->count,
            );
        }

        // Feedbacks récents
        $recent = get_posts( array(
            'post_type'      => WPVFH_CPT_Feedback::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );

        foreach ( $recent as $post ) {
            $stats['recent'][] = WPVFH_CPT_Feedback::get_feedback_data( $post );
        }

        /**
         * Filtre les statistiques des feedbacks
         *
         * @since 1.0.0
         * @param array $stats Statistiques
         */
        $stats = apply_filters( 'wpvfh_feedback_stats', $stats );

        return new WP_REST_Response( $stats );
    }

    /**
     * Obtenir la liste des pages avec feedbacks
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response
     */
    public static function get_pages( $request ) {
        global $wpdb;

        // Récupérer toutes les URLs uniques avec leur compte de feedbacks
        $pages_data = $wpdb->get_results( $wpdb->prepare(
            "SELECT pm.meta_value as url, COUNT(*) as count
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_wpvfh_url'
            AND p.post_type = %s
            AND p.post_status = 'publish'
            GROUP BY pm.meta_value
            ORDER BY count DESC",
            WPVFH_CPT_Feedback::POST_TYPE
        ) );

        $pages = array();

        foreach ( $pages_data as $page ) {
            // Vérifier si la page est validée
            $validated = get_option( 'wpvfh_validated_pages', array() );
            $is_validated = in_array( $page->url, $validated, true );

            // Essayer de récupérer le titre de la page WordPress
            $post_id = url_to_postid( $page->url );
            $title = $post_id ? get_the_title( $post_id ) : '';

            $pages[] = array(
                'url'       => $page->url,
                'title'     => $title,
                'count'     => (int) $page->count,
                'validated' => $is_validated,
            );
        }

        return new WP_REST_Response( $pages );
    }

    /**
     * Valider une page
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response|WP_Error
     */
    public static function validate_page( $request ) {
        $url = $request->get_param( 'url' );

        // Normaliser l'URL
        $parsed = wp_parse_url( $url );
        $path = isset( $parsed['path'] ) ? $parsed['path'] : '/';
        $path = rtrim( $path, '/' );
        if ( empty( $path ) ) {
            $path = '/';
        }

        // Ajouter à la liste des pages validées
        $validated = get_option( 'wpvfh_validated_pages', array() );
        if ( ! in_array( $url, $validated, true ) ) {
            $validated[] = $url;
            update_option( 'wpvfh_validated_pages', $validated );
        }

        // Marquer tous les feedbacks de cette page comme résolus (si pas déjà)
        $args = array(
            'post_type'      => WPVFH_CPT_Feedback::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_wpvfh_url',
                    'value'   => $path,
                    'compare' => 'LIKE',
                ),
            ),
            'fields'         => 'ids',
        );

        $query = new WP_Query( $args );

        foreach ( $query->posts as $post_id ) {
            $status = get_post_meta( $post_id, '_wpvfh_status', true );
            // Marquer comme résolu uniquement les new/in_progress
            if ( in_array( $status, array( 'new', 'in_progress' ), true ) ) {
                update_post_meta( $post_id, '_wpvfh_status', 'resolved' );
                wp_set_object_terms( $post_id, 'resolved', WPVFH_CPT_Feedback::TAX_STATUS );
            }
        }

        /**
         * Action après validation d'une page
         *
         * @since 1.0.0
         * @param string $url URL de la page
         */
        do_action( 'wpvfh_page_validated', $url );

        return new WP_REST_Response( array(
            'success' => true,
            'url'     => $url,
            'message' => __( 'Page validée avec succès.', 'blazing-feedback' ),
        ) );
    }

    /**
     * Obtenir la liste des utilisateurs (pour les mentions)
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Requête REST
     * @return WP_REST_Response
     */
    public static function get_users( $request ) {
        $search = $request->get_param( 'search' );

        $args = array(
            'number'  => 20,
            'orderby' => 'display_name',
            'order'   => 'ASC',
            'fields'  => array( 'ID', 'display_name', 'user_login', 'user_email' ),
        );

        if ( $search ) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = array( 'display_name', 'user_login', 'user_email' );
        }

        $users_query = new WP_User_Query( $args );
        $users = array();

        foreach ( $users_query->get_results() as $user ) {
            $users[] = array(
                'id'       => $user->ID,
                'name'     => $user->display_name,
                'username' => $user->user_login,
                'avatar'   => get_avatar_url( $user->user_email, array( 'size' => 32 ) ),
            );
        }

        return new WP_REST_Response( $users );
    }

    /**
     * Sauvegarder un screenshot depuis des données base64
     *
     * @since 1.0.0
     * @param string $data        Données base64
     * @param int    $feedback_id ID du feedback
     * @return int|WP_Error ID de l'attachment ou erreur
     */
    private static function save_screenshot( $data, $feedback_id ) {
        // Extraire le type MIME et les données
        if ( ! preg_match( '/^data:image\/(png|jpeg|jpg|gif|webp);base64,(.+)$/', $data, $matches ) ) {
            return new WP_Error( 'invalid_format', __( 'Format d\'image invalide.', 'blazing-feedback' ) );
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $decoded = base64_decode( $matches[2] );

        if ( false === $decoded ) {
            return new WP_Error( 'decode_failed', __( 'Échec du décodage de l\'image.', 'blazing-feedback' ) );
        }

        // Créer le fichier
        $upload_dir = wp_upload_dir();
        $feedback_dir = $upload_dir['basedir'] . '/visual-feedback';

        if ( ! file_exists( $feedback_dir ) ) {
            wp_mkdir_p( $feedback_dir );
        }

        $filename = 'screenshot-' . $feedback_id . '-' . time() . '.' . $extension;
        $filepath = $feedback_dir . '/' . $filename;

        // Écrire le fichier
        $written = file_put_contents( $filepath, $decoded );

        if ( false === $written ) {
            return new WP_Error( 'write_failed', __( 'Échec de l\'écriture du fichier.', 'blazing-feedback' ) );
        }

        // Créer l'attachment WordPress
        $file_url = $upload_dir['baseurl'] . '/visual-feedback/' . $filename;

        $attachment = array(
            'post_mime_type' => 'image/' . ( $extension === 'jpg' ? 'jpeg' : $extension ),
            'post_title'     => sprintf( __( 'Screenshot - Feedback #%d', 'blazing-feedback' ), $feedback_id ),
            'post_content'   => '',
            'post_status'    => 'inherit',
            'post_parent'    => $feedback_id,
        );

        $attachment_id = wp_insert_attachment( $attachment, $filepath, $feedback_id );

        if ( is_wp_error( $attachment_id ) ) {
            unlink( $filepath );
            return $attachment_id;
        }

        // Générer les métadonnées de l'attachment
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata( $attachment_id, $filepath );
        wp_update_attachment_metadata( $attachment_id, $attach_data );

        return $attachment_id;
    }

    /**
     * Normaliser une URL pour la comparaison
     *
     * @since 1.0.0
     * @param string $url URL à normaliser
     * @return string URL normalisée
     */
    private static function normalize_url( $url ) {
        // Parser l'URL
        $parsed = wp_parse_url( $url );

        if ( ! $parsed ) {
            return $url;
        }

        $scheme = isset( $parsed['scheme'] ) ? $parsed['scheme'] : 'https';
        $host = isset( $parsed['host'] ) ? strtolower( $parsed['host'] ) : '';
        $path = isset( $parsed['path'] ) ? $parsed['path'] : '/';
        $query = isset( $parsed['query'] ) ? '?' . $parsed['query'] : '';

        // Supprimer www.
        $host = preg_replace( '/^www\./', '', $host );

        // Normaliser le path
        $path = rtrim( $path, '/' );
        if ( empty( $path ) ) {
            $path = '/';
        }

        return $scheme . '://' . $host . $path . $query;
    }
}

// Initialiser l'API
WPVFH_REST_API::init();
