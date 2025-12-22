<?php
/**
 * Custom Post Type pour les feedbacks visuels
 *
 * Enregistre le CPT visual_feedback et les taxonomies associées
 *
 * @package WP_Visual_Feedback_Hub
 * @since 1.0.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe de gestion du Custom Post Type Feedback
 *
 * @since 1.0.0
 */
class WPVFH_CPT_Feedback {

    /**
     * Nom du post type
     *
     * @since 1.0.0
     * @var string
     */
    const POST_TYPE = 'visual_feedback';

    /**
     * Taxonomie de statut
     *
     * @since 1.0.0
     * @var string
     */
    const TAX_STATUS = 'feedback_status';

    /**
     * Taxonomie de page
     *
     * @since 1.0.0
     * @var string
     */
    const TAX_PAGE = 'feedback_page';


    /**
     * Meta keys pour les feedbacks
     *
     * @since 1.0.0
     * @var array
     */
    private static $meta_keys = array(
        '_wpvfh_position_x',
        '_wpvfh_position_y',
        '_wpvfh_url',
        '_wpvfh_screenshot_id',
        '_wpvfh_screen_width',
        '_wpvfh_screen_height',
        '_wpvfh_viewport_width',
        '_wpvfh_viewport_height',
        '_wpvfh_browser',
        '_wpvfh_os',
        '_wpvfh_device',
        '_wpvfh_user_agent',
        '_wpvfh_status',
        '_wpvfh_selector',
        '_wpvfh_element_offset_x',
        '_wpvfh_element_offset_y',
        '_wpvfh_scroll_x',
        '_wpvfh_scroll_y',
    );

    /**
     * Initialiser le CPT
     *
     * @since 1.0.0
     * @return void
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_post_type' ) );
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
        add_action( 'init', array( __CLASS__, 'register_meta_fields' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_meta_box' ) );

        // Colonnes admin
        add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'add_admin_columns' ) );
        add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'render_admin_columns' ), 10, 2 );
        add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( __CLASS__, 'sortable_columns' ) );

        // Filtres admin
        add_action( 'restrict_manage_posts', array( __CLASS__, 'add_admin_filters' ) );
        add_filter( 'parse_query', array( __CLASS__, 'filter_admin_query' ) );
    }

    /**
     * Enregistrer le Custom Post Type
     *
     * @since 1.0.0
     * @return void
     */
    public static function register_post_type() {
        $labels = array(
            'name'                  => _x( 'Feedbacks', 'Post type general name', 'blazing-feedback' ),
            'singular_name'         => _x( 'Feedback', 'Post type singular name', 'blazing-feedback' ),
            'menu_name'             => _x( 'Feedbacks', 'Admin Menu text', 'blazing-feedback' ),
            'name_admin_bar'        => _x( 'Feedback', 'Add New on Toolbar', 'blazing-feedback' ),
            'add_new'               => __( 'Ajouter', 'blazing-feedback' ),
            'add_new_item'          => __( 'Ajouter un feedback', 'blazing-feedback' ),
            'new_item'              => __( 'Nouveau feedback', 'blazing-feedback' ),
            'edit_item'             => __( 'Modifier le feedback', 'blazing-feedback' ),
            'view_item'             => __( 'Voir le feedback', 'blazing-feedback' ),
            'all_items'             => __( 'Tous les feedbacks', 'blazing-feedback' ),
            'search_items'          => __( 'Rechercher un feedback', 'blazing-feedback' ),
            'parent_item_colon'     => __( 'Feedback parent :', 'blazing-feedback' ),
            'not_found'             => __( 'Aucun feedback trouvé.', 'blazing-feedback' ),
            'not_found_in_trash'    => __( 'Aucun feedback dans la corbeille.', 'blazing-feedback' ),
            'featured_image'        => __( 'Screenshot', 'blazing-feedback' ),
            'set_featured_image'    => __( 'Définir le screenshot', 'blazing-feedback' ),
            'remove_featured_image' => __( 'Retirer le screenshot', 'blazing-feedback' ),
            'use_featured_image'    => __( 'Utiliser comme screenshot', 'blazing-feedback' ),
            'archives'              => __( 'Archives des feedbacks', 'blazing-feedback' ),
            'filter_items_list'     => __( 'Filtrer les feedbacks', 'blazing-feedback' ),
            'items_list_navigation' => __( 'Navigation des feedbacks', 'blazing-feedback' ),
            'items_list'            => __( 'Liste des feedbacks', 'blazing-feedback' ),
        );

        $args = array(
            'labels'              => $labels,
            'description'         => __( 'Feedbacks visuels du site', 'blazing-feedback' ),
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => 'blazing-minds',
            'query_var'           => false,
            'rewrite'             => false,
            'capability_type'     => array( 'feedback', 'feedbacks' ),
            'map_meta_cap'        => true,
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => null,
            'menu_icon'           => 'dashicons-format-chat',
            'supports'            => array( 'title', 'editor', 'author', 'comments' ),
            'show_in_rest'        => true,
            'rest_base'           => 'feedbacks',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
        );

        /**
         * Filtre les arguments du CPT
         *
         * @since 1.0.0
         * @param array $args Arguments du CPT
         */
        $args = apply_filters( 'wpvfh_cpt_args', $args );

        register_post_type( self::POST_TYPE, $args );
    }

    /**
     * Enregistrer les taxonomies
     *
     * @since 1.0.0
     * @return void
     */
    public static function register_taxonomies() {
        // Taxonomie de statut
        $status_labels = array(
            'name'              => _x( 'Statuts', 'taxonomy general name', 'blazing-feedback' ),
            'singular_name'     => _x( 'Statut', 'taxonomy singular name', 'blazing-feedback' ),
            'search_items'      => __( 'Rechercher un statut', 'blazing-feedback' ),
            'all_items'         => __( 'Tous les statuts', 'blazing-feedback' ),
            'edit_item'         => __( 'Modifier le statut', 'blazing-feedback' ),
            'update_item'       => __( 'Mettre à jour le statut', 'blazing-feedback' ),
            'add_new_item'      => __( 'Ajouter un statut', 'blazing-feedback' ),
            'new_item_name'     => __( 'Nouveau statut', 'blazing-feedback' ),
            'menu_name'         => __( 'Statuts', 'blazing-feedback' ),
        );

        $status_args = array(
            'hierarchical'      => false,
            'labels'            => $status_labels,
            'show_ui'           => false,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'query_var'         => false,
            'rewrite'           => false,
        );

        register_taxonomy( self::TAX_STATUS, self::POST_TYPE, $status_args );

        // Créer les termes de statut par défaut
        self::create_default_status_terms();

        // Taxonomie de page
        $page_labels = array(
            'name'              => _x( 'Pages', 'taxonomy general name', 'blazing-feedback' ),
            'singular_name'     => _x( 'Page', 'taxonomy singular name', 'blazing-feedback' ),
            'search_items'      => __( 'Rechercher une page', 'blazing-feedback' ),
            'all_items'         => __( 'Toutes les pages', 'blazing-feedback' ),
            'edit_item'         => __( 'Modifier la page', 'blazing-feedback' ),
            'update_item'       => __( 'Mettre à jour la page', 'blazing-feedback' ),
            'add_new_item'      => __( 'Ajouter une page', 'blazing-feedback' ),
            'new_item_name'     => __( 'Nouvelle page', 'blazing-feedback' ),
            'menu_name'         => __( 'Pages', 'blazing-feedback' ),
        );

        $page_args = array(
            'hierarchical'      => false,
            'labels'            => $page_labels,
            'show_ui'           => false,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'query_var'         => false,
            'rewrite'           => false,
        );

        register_taxonomy( self::TAX_PAGE, self::POST_TYPE, $page_args );
    }

    /**
     * Créer les termes de statut par défaut
     *
     * @since 1.0.0
     * @return void
     */
    private static function create_default_status_terms() {
        $statuses = WPVFH_Options_Manager::get_statuses();
        foreach ( $statuses as $status ) {
            if ( ! term_exists( $status['id'], self::TAX_STATUS ) ) {
                wp_insert_term(
                    $status['label'],
                    self::TAX_STATUS,
                    array( 'slug' => $status['id'] )
                );
            }
        }
    }

    /**
     * Enregistrer les meta fields pour la REST API
     *
     * @since 1.0.0
     * @return void
     */
    public static function register_meta_fields() {
        $meta_args = array(
            'show_in_rest'      => true,
            'single'            => true,
            'auth_callback'     => function() {
                // Use primitive capability (plural) to avoid map_meta_cap warning
                return current_user_can( 'edit_feedbacks' );
            },
        );

        // Position
        register_post_meta( self::POST_TYPE, '_wpvfh_position_x', array_merge( $meta_args, array(
            'type'              => 'number',
            'sanitize_callback' => function( $value ) { return floatval( $value ); },
        ) ) );

        register_post_meta( self::POST_TYPE, '_wpvfh_position_y', array_merge( $meta_args, array(
            'type'              => 'number',
            'sanitize_callback' => function( $value ) { return floatval( $value ); },
        ) ) );

        // URL
        register_post_meta( self::POST_TYPE, '_wpvfh_url', array_merge( $meta_args, array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
        ) ) );

        // Screenshot ID
        register_post_meta( self::POST_TYPE, '_wpvfh_screenshot_id', array_merge( $meta_args, array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ) ) );

        // Dimensions écran
        register_post_meta( self::POST_TYPE, '_wpvfh_screen_width', array_merge( $meta_args, array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ) ) );

        register_post_meta( self::POST_TYPE, '_wpvfh_screen_height', array_merge( $meta_args, array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ) ) );

        // Viewport
        register_post_meta( self::POST_TYPE, '_wpvfh_viewport_width', array_merge( $meta_args, array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ) ) );

        register_post_meta( self::POST_TYPE, '_wpvfh_viewport_height', array_merge( $meta_args, array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ) ) );

        // Info navigateur
        register_post_meta( self::POST_TYPE, '_wpvfh_browser', array_merge( $meta_args, array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ) ) );

        register_post_meta( self::POST_TYPE, '_wpvfh_os', array_merge( $meta_args, array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ) ) );

        register_post_meta( self::POST_TYPE, '_wpvfh_device', array_merge( $meta_args, array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ) ) );

        register_post_meta( self::POST_TYPE, '_wpvfh_user_agent', array_merge( $meta_args, array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ) ) );

        // Statut (meta en plus de la taxonomie pour faciliter les requêtes)
        register_post_meta( self::POST_TYPE, '_wpvfh_status', array_merge( $meta_args, array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_key',
            'default'           => 'new',
        ) ) );

        // Sélecteur CSS (pour DOM anchoring / repositionnement intelligent)
        register_post_meta( self::POST_TYPE, '_wpvfh_selector', array_merge( $meta_args, array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ) ) );

        // DOM Anchoring - Offset relatif à l'élément ancre (en pourcentage)
        register_post_meta( self::POST_TYPE, '_wpvfh_element_offset_x', array_merge( $meta_args, array(
            'type'              => 'number',
            'sanitize_callback' => function( $value ) { return floatval( $value ); },
        ) ) );

        register_post_meta( self::POST_TYPE, '_wpvfh_element_offset_y', array_merge( $meta_args, array(
            'type'              => 'number',
            'sanitize_callback' => function( $value ) { return floatval( $value ); },
        ) ) );

        // Position de scroll
        register_post_meta( self::POST_TYPE, '_wpvfh_scroll_x', array_merge( $meta_args, array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ) ) );

        register_post_meta( self::POST_TYPE, '_wpvfh_scroll_y', array_merge( $meta_args, array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ) ) );
    }

    /**
     * Ajouter les meta boxes
     *
     * @since 1.0.0
     * @return void
     */
    public static function add_meta_boxes() {
        // Meta box des informations de feedback
        add_meta_box(
            'wpvfh_feedback_info',
            __( 'Informations du feedback', 'blazing-feedback' ),
            array( __CLASS__, 'render_info_meta_box' ),
            self::POST_TYPE,
            'side',
            'high'
        );

        // Meta box du screenshot
        add_meta_box(
            'wpvfh_feedback_screenshot',
            __( 'Screenshot', 'blazing-feedback' ),
            array( __CLASS__, 'render_screenshot_meta_box' ),
            self::POST_TYPE,
            'normal',
            'high'
        );

        // Meta box des métadonnées techniques
        add_meta_box(
            'wpvfh_feedback_metadata',
            __( 'Métadonnées techniques', 'blazing-feedback' ),
            array( __CLASS__, 'render_metadata_meta_box' ),
            self::POST_TYPE,
            'side',
            'default'
        );
    }

    /**
     * Rendu de la meta box des informations
     *
     * @since 1.0.0
     * @param WP_Post $post Post actuel
     * @return void
     */
    public static function render_info_meta_box( $post ) {
        wp_nonce_field( 'wpvfh_save_feedback', 'wpvfh_feedback_nonce' );

        $current_status = get_post_meta( $post->ID, '_wpvfh_status', true ) ?: 'new';
        $url = get_post_meta( $post->ID, '_wpvfh_url', true );
        $statuses = WPVFH_Options_Manager::get_statuses();
        ?>
        <p>
            <label for="wpvfh_status"><strong><?php esc_html_e( 'Statut', 'blazing-feedback' ); ?></strong></label>
            <select id="wpvfh_status" name="wpvfh_status" class="widefat">
                <?php foreach ( $statuses as $status ) : ?>
                    <option value="<?php echo esc_attr( $status['id'] ); ?>" <?php selected( $current_status, $status['id'] ); ?>>
                        <?php echo esc_html( $status['emoji'] . ' ' . $status['label'] ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <?php if ( $url ) : ?>
        <p>
            <label><strong><?php esc_html_e( 'Page concernée', 'blazing-feedback' ); ?></strong></label><br>
            <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html( wp_parse_url( $url, PHP_URL_PATH ) ?: '/' ); ?>
            </a>
        </p>
        <?php endif;
    }

    /**
     * Rendu de la meta box du screenshot
     *
     * @since 1.0.0
     * @param WP_Post $post Post actuel
     * @return void
     */
    public static function render_screenshot_meta_box( $post ) {
        $screenshot_id = get_post_meta( $post->ID, '_wpvfh_screenshot_id', true );
        $position_x = get_post_meta( $post->ID, '_wpvfh_position_x', true );
        $position_y = get_post_meta( $post->ID, '_wpvfh_position_y', true );

        if ( $screenshot_id ) {
            $screenshot_url = wp_get_attachment_url( $screenshot_id );
            ?>
            <div class="wpvfh-screenshot-container" style="position: relative; max-width: 100%; overflow: auto;">
                <img src="<?php echo esc_url( $screenshot_url ); ?>" alt="<?php esc_attr_e( 'Screenshot', 'blazing-feedback' ); ?>" style="max-width: 100%;">
                <?php if ( $position_x && $position_y ) : ?>
                    <div style="
                        position: absolute;
                        left: <?php echo esc_attr( $position_x ); ?>%;
                        top: <?php echo esc_attr( $position_y ); ?>%;
                        width: 24px;
                        height: 24px;
                        background: #e74c3c;
                        border-radius: 50%;
                        border: 3px solid white;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                        transform: translate(-50%, -50%);
                    "></div>
                <?php endif; ?>
            </div>
            <p class="description">
                <?php esc_html_e( 'Position du marqueur :', 'blazing-feedback' ); ?>
                X: <?php echo esc_html( round( $position_x, 2 ) ); ?>%,
                Y: <?php echo esc_html( round( $position_y, 2 ) ); ?>%
            </p>
            <?php
        } else {
            ?>
            <p class="description"><?php esc_html_e( 'Aucun screenshot associé à ce feedback.', 'blazing-feedback' ); ?></p>
            <?php
        }
    }

    /**
     * Rendu de la meta box des métadonnées
     *
     * @since 1.0.0
     * @param WP_Post $post Post actuel
     * @return void
     */
    public static function render_metadata_meta_box( $post ) {
        $metadata = array(
            'browser'         => get_post_meta( $post->ID, '_wpvfh_browser', true ),
            'os'              => get_post_meta( $post->ID, '_wpvfh_os', true ),
            'device'          => get_post_meta( $post->ID, '_wpvfh_device', true ),
            'screen_width'    => get_post_meta( $post->ID, '_wpvfh_screen_width', true ),
            'screen_height'   => get_post_meta( $post->ID, '_wpvfh_screen_height', true ),
            'viewport_width'  => get_post_meta( $post->ID, '_wpvfh_viewport_width', true ),
            'viewport_height' => get_post_meta( $post->ID, '_wpvfh_viewport_height', true ),
        );
        ?>
        <ul style="margin: 0;">
            <?php if ( $metadata['browser'] ) : ?>
                <li><strong><?php esc_html_e( 'Navigateur:', 'blazing-feedback' ); ?></strong> <?php echo esc_html( $metadata['browser'] ); ?></li>
            <?php endif; ?>
            <?php if ( $metadata['os'] ) : ?>
                <li><strong><?php esc_html_e( 'OS:', 'blazing-feedback' ); ?></strong> <?php echo esc_html( $metadata['os'] ); ?></li>
            <?php endif; ?>
            <?php if ( $metadata['device'] ) : ?>
                <li><strong><?php esc_html_e( 'Appareil:', 'blazing-feedback' ); ?></strong> <?php echo esc_html( $metadata['device'] ); ?></li>
            <?php endif; ?>
            <?php if ( $metadata['screen_width'] && $metadata['screen_height'] ) : ?>
                <li><strong><?php esc_html_e( 'Écran:', 'blazing-feedback' ); ?></strong> <?php echo esc_html( $metadata['screen_width'] . 'x' . $metadata['screen_height'] ); ?></li>
            <?php endif; ?>
            <?php if ( $metadata['viewport_width'] && $metadata['viewport_height'] ) : ?>
                <li><strong><?php esc_html_e( 'Viewport:', 'blazing-feedback' ); ?></strong> <?php echo esc_html( $metadata['viewport_width'] . 'x' . $metadata['viewport_height'] ); ?></li>
            <?php endif; ?>
        </ul>
        <?php
    }

    /**
     * Sauvegarder les données de la meta box
     *
     * @since 1.0.0
     * @param int $post_id ID du post
     * @return void
     */
    public static function save_meta_box( $post_id ) {
        // Vérifier le nonce
        if ( ! isset( $_POST['wpvfh_feedback_nonce'] ) || ! wp_verify_nonce( $_POST['wpvfh_feedback_nonce'], 'wpvfh_save_feedback' ) ) {
            return;
        }

        // Vérifier l'autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Vérifier les permissions
        if ( ! current_user_can( 'edit_feedback', $post_id ) ) {
            return;
        }

        // Sauvegarder le statut
        if ( isset( $_POST['wpvfh_status'] ) ) {
            $status = sanitize_key( $_POST['wpvfh_status'] );
            $status_data = WPVFH_Options_Manager::get_status_by_id( $status );
            if ( $status_data ) {
                update_post_meta( $post_id, '_wpvfh_status', $status );

                // Mettre à jour la taxonomie également
                wp_set_object_terms( $post_id, $status, self::TAX_STATUS );

                /**
                 * Action déclenchée après changement de statut
                 *
                 * @since 1.0.0
                 * @param int    $post_id ID du feedback
                 * @param string $status  Nouveau statut
                 */
                do_action( 'wpvfh_status_changed', $post_id, $status );
            }
        }
    }

    /**
     * Obtenir les statuts disponibles
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_statuses() {
        $statuses = WPVFH_Options_Manager::get_statuses();
        /**
         * Filtre les statuts de feedback
         *
         * @since 1.0.0
         * @param array $statuses Liste des statuts
         */
        return apply_filters( 'wpvfh_feedback_statuses', $statuses );
    }

    /**
     * Ajouter les colonnes admin
     *
     * @since 1.0.0
     * @param array $columns Colonnes existantes
     * @return array
     */
    public static function add_admin_columns( $columns ) {
        $new_columns = array();

        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;

            if ( 'title' === $key ) {
                $new_columns['feedback_status'] = __( 'Statut', 'blazing-feedback' );
                $new_columns['feedback_page'] = __( 'Page', 'blazing-feedback' );
                $new_columns['feedback_screenshot'] = __( 'Screenshot', 'blazing-feedback' );
            }
        }

        return $new_columns;
    }

    /**
     * Rendu des colonnes admin
     *
     * @since 1.0.0
     * @param string $column  Nom de la colonne
     * @param int    $post_id ID du post
     * @return void
     */
    public static function render_admin_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'feedback_status':
                $status = get_post_meta( $post_id, '_wpvfh_status', true ) ?: 'new';
                $status_data = WPVFH_Options_Manager::get_status_by_id( $status );
                if ( ! $status_data ) {
                    $status_data = WPVFH_Options_Manager::get_status_by_id( 'new' );
                }
                if ( $status_data ) {
                    printf(
                        '<span class="wpvfh-status wpvfh-status-%s" style="color: %s;">%s %s</span>',
                        esc_attr( $status ),
                        esc_attr( $status_data['color'] ),
                        esc_html( $status_data['emoji'] ),
                        esc_html( $status_data['label'] )
                    );
                }
                break;

            case 'feedback_page':
                $url = get_post_meta( $post_id, '_wpvfh_url', true );
                if ( $url ) {
                    printf(
                        '<a href="%s" target="_blank" rel="noopener">%s</a>',
                        esc_url( $url ),
                        esc_html( wp_parse_url( $url, PHP_URL_PATH ) ?: '/' )
                    );
                }
                break;

            case 'feedback_screenshot':
                $screenshot_id = get_post_meta( $post_id, '_wpvfh_screenshot_id', true );
                if ( $screenshot_id ) {
                    $screenshot_url = wp_get_attachment_thumb_url( $screenshot_id );
                    printf(
                        '<img src="%s" alt="Screenshot" style="max-width: 80px; max-height: 60px;">',
                        esc_url( $screenshot_url )
                    );
                } else {
                    echo '—';
                }
                break;
        }
    }

    /**
     * Définir les colonnes triables
     *
     * @since 1.0.0
     * @param array $columns Colonnes triables
     * @return array
     */
    public static function sortable_columns( $columns ) {
        $columns['feedback_status'] = 'feedback_status';
        return $columns;
    }

    /**
     * Ajouter les filtres admin
     *
     * @since 1.0.0
     * @param string $post_type Type de post
     * @return void
     */
    public static function add_admin_filters( $post_type ) {
        if ( self::POST_TYPE !== $post_type ) {
            return;
        }

        // Filtre par statut
        $current_status = isset( $_GET['feedback_status_filter'] ) ? sanitize_key( $_GET['feedback_status_filter'] ) : '';
        $statuses = WPVFH_Options_Manager::get_statuses();
        ?>
        <select name="feedback_status_filter">
            <option value=""><?php esc_html_e( 'Tous les statuts', 'blazing-feedback' ); ?></option>
            <?php foreach ( $statuses as $status ) : ?>
                <option value="<?php echo esc_attr( $status['id'] ); ?>" <?php selected( $current_status, $status['id'] ); ?>>
                    <?php echo esc_html( $status['label'] ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Filtrer la requête admin
     *
     * @since 1.0.0
     * @param WP_Query $query Requête
     * @return void
     */
    public static function filter_admin_query( $query ) {
        global $pagenow;

        if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() ) {
            return;
        }

        if ( self::POST_TYPE !== $query->get( 'post_type' ) ) {
            return;
        }

        // Filtre par statut
        if ( ! empty( $_GET['feedback_status_filter'] ) ) {
            $status = sanitize_key( $_GET['feedback_status_filter'] );
            $query->set( 'meta_query', array(
                array(
                    'key'   => '_wpvfh_status',
                    'value' => $status,
                ),
            ) );
        }
    }

    /**
     * Créer un nouveau feedback
     *
     * @since 1.0.0
     * @param array $data Données du feedback
     * @return int|WP_Error ID du feedback créé ou erreur
     */
    public static function create_feedback( $data ) {
        // Vérifier les permissions
        if ( ! WPVFH_Permissions::can_create_feedback() ) {
            return new WP_Error( 'permission_denied', __( 'Vous n\'avez pas la permission de créer un feedback.', 'blazing-feedback' ) );
        }

        // Assainir les données
        $data = WPVFH_Permissions::sanitize_feedback_data( $data );

        // Valider le commentaire
        if ( empty( $data['comment'] ) ) {
            return new WP_Error( 'empty_comment', __( 'Le commentaire est obligatoire.', 'blazing-feedback' ) );
        }

        // Préparer les données pour la table SQL
        $url = isset( $data['url'] ) ? $data['url'] : '';
        $page_path = $url ? ( wp_parse_url( $url, PHP_URL_PATH ) ?: '/' ) : '/';
        // Normaliser le path (enlever trailing slash sauf pour la racine)
        $page_path = rtrim( $page_path, '/' );
        if ( empty( $page_path ) ) {
            $page_path = '/';
        }

        $feedback_data = array(
            'user_id'             => get_current_user_id() ?: null,
            'comment'             => $data['comment'],
            'url'                 => $url,
            'page_path'           => $page_path,
            'position_x'          => isset( $data['position_x'] ) ? floatval( $data['position_x'] ) : 0,
            'position_y'          => isset( $data['position_y'] ) ? floatval( $data['position_y'] ) : 0,
            'selector'            => isset( $data['selector'] ) ? $data['selector'] : null,
            'element_offset_x'    => isset( $data['element_offset_x'] ) ? floatval( $data['element_offset_x'] ) : null,
            'element_offset_y'    => isset( $data['element_offset_y'] ) ? floatval( $data['element_offset_y'] ) : null,
            'scroll_x'            => isset( $data['scroll_x'] ) ? absint( $data['scroll_x'] ) : 0,
            'scroll_y'            => isset( $data['scroll_y'] ) ? absint( $data['scroll_y'] ) : 0,
            'screenshot_id'       => isset( $data['screenshot_id'] ) ? absint( $data['screenshot_id'] ) : null,
            'screen_width'        => isset( $data['screen_width'] ) ? absint( $data['screen_width'] ) : null,
            'screen_height'       => isset( $data['screen_height'] ) ? absint( $data['screen_height'] ) : null,
            'viewport_width'      => isset( $data['viewport_width'] ) ? absint( $data['viewport_width'] ) : null,
            'viewport_height'     => isset( $data['viewport_height'] ) ? absint( $data['viewport_height'] ) : null,
            'device_pixel_ratio'  => isset( $data['device_pixel_ratio'] ) ? $data['device_pixel_ratio'] : null,
            'color_depth'         => isset( $data['color_depth'] ) ? $data['color_depth'] : null,
            'orientation'         => isset( $data['orientation'] ) ? $data['orientation'] : null,
            'browser'             => isset( $data['browser'] ) ? $data['browser'] : null,
            'browser_version'     => isset( $data['browser_version'] ) ? $data['browser_version'] : null,
            'os'                  => isset( $data['os'] ) ? $data['os'] : null,
            'os_version'          => isset( $data['os_version'] ) ? $data['os_version'] : null,
            'device'              => isset( $data['device'] ) ? $data['device'] : null,
            'platform'            => isset( $data['platform'] ) ? $data['platform'] : null,
            'user_agent'          => isset( $data['user_agent'] ) ? $data['user_agent'] : null,
            'language'            => isset( $data['language'] ) ? $data['language'] : null,
            'languages'           => isset( $data['languages'] ) ? $data['languages'] : null,
            'timezone'            => isset( $data['timezone'] ) ? $data['timezone'] : null,
            'timezone_offset'     => isset( $data['timezone_offset'] ) ? $data['timezone_offset'] : null,
            'local_time'          => isset( $data['local_time'] ) ? $data['local_time'] : null,
            'cookies_enabled'     => isset( $data['cookies_enabled'] ) ? (int) $data['cookies_enabled'] : 1,
            'online'              => isset( $data['online'] ) ? (int) $data['online'] : 1,
            'touch_support'       => isset( $data['touch_support'] ) ? (int) $data['touch_support'] : 0,
            'max_touch_points'    => isset( $data['max_touch_points'] ) ? absint( $data['max_touch_points'] ) : 0,
            'device_memory'       => isset( $data['device_memory'] ) ? $data['device_memory'] : null,
            'hardware_concurrency' => isset( $data['hardware_concurrency'] ) ? $data['hardware_concurrency'] : null,
            'connection_type'     => isset( $data['connection_type'] ) ? $data['connection_type'] : null,
            'referrer'            => isset( $data['referrer'] ) ? $data['referrer'] : null,
            'status'              => isset( $data['status'] ) ? sanitize_key( $data['status'] ) : 'new',
            'priority'            => isset( $data['priority'] ) ? sanitize_key( $data['priority'] ) : 'none',
            'feedback_type'       => isset( $data['feedback_type'] ) ? sanitize_key( $data['feedback_type'] ) : 'bug',
            'tags'                => isset( $data['tags'] ) ? sanitize_text_field( $data['tags'] ) : null,
        );

        // Insérer dans la table SQL
        $feedback_id = WPVFH_Database::insert_feedback( $feedback_data );

        if ( ! $feedback_id ) {
            return new WP_Error( 'db_error', __( 'Erreur lors de la création du feedback.', 'blazing-feedback' ) );
        }

        /**
         * Action déclenchée après la création d'un feedback
         *
         * @since 1.0.0
         * @param int   $feedback_id ID du feedback
         * @param array $data        Données du feedback
         */
        do_action( 'wpvfh_feedback_created', $feedback_id, $data );

        return $feedback_id;
    }

    /**
     * Obtenir les feedbacks d'une page
     *
     * @since 1.0.0
     * @param string $url     URL de la page
     * @param array  $args    Arguments supplémentaires
     * @return array
     */
    public static function get_feedbacks_by_url( $url, $args = array() ) {
        $feedbacks = WPVFH_Database::get_feedbacks_by_url( $url );

        /**
         * Filtre les feedbacks récupérés par URL
         *
         * @since 1.0.0
         * @param array  $feedbacks Feedbacks trouvés
         * @param string $url       URL de la page
         */
        return apply_filters( 'wpvfh_feedbacks_by_url', $feedbacks, $url );
    }

    /**
     * Obtenir un feedback formaté pour l'API
     *
     * @since 1.0.0
     * @param int|object $feedback Feedback ID ou objet de la base de données
     * @return array|false
     */
    public static function get_feedback_data( $feedback ) {
        // Si c'est un ID, récupérer depuis la base de données
        if ( is_numeric( $feedback ) ) {
            $feedback = WPVFH_Database::get_feedback( $feedback );
        }

        if ( ! $feedback ) {
            return false;
        }

        // Récupérer l'auteur
        $author = null;
        $author_name = __( 'Anonyme', 'blazing-feedback' );
        $author_id = 0;

        if ( ! empty( $feedback->user_id ) ) {
            $author = get_userdata( $feedback->user_id );
            if ( $author ) {
                $author_name = $author->display_name;
                $author_id = $author->ID;
            }
        } elseif ( ! empty( $feedback->guest_name ) ) {
            $author_name = $feedback->guest_name;
        }

        // Screenshot URL
        $screenshot_url = '';
        if ( ! empty( $feedback->screenshot_id ) ) {
            $screenshot_url = wp_get_attachment_url( $feedback->screenshot_id );
        }

        $data = array(
            'id'              => (int) $feedback->id,
            'comment'         => $feedback->comment,
            'title'           => wp_trim_words( $feedback->comment, 10, '...' ),
            'date'            => $feedback->created_at,
            'date_gmt'        => get_gmt_from_date( $feedback->created_at ),
            'modified'        => $feedback->updated_at,
            'author'          => array(
                'id'           => $author_id,
                'name'         => $author_name,
                'avatar'       => get_avatar_url( $author_id ?: $feedback->guest_email, array( 'size' => 48 ) ),
            ),
            'url'             => $feedback->url,
            'page_path'       => $feedback->page_path,
            'position_x'      => (float) $feedback->position_x,
            'position_y'      => (float) $feedback->position_y,
            'status'          => $feedback->status ?: 'new',
            'priority'        => $feedback->priority ?: 'none',
            'feedback_type'   => $feedback->feedback_type ?: '',
            'tags'            => $feedback->tags ?: '',
            'screenshot_id'   => (int) $feedback->screenshot_id,
            'screenshot_url'  => $screenshot_url,
            'screen_width'    => (int) $feedback->screen_width,
            'screen_height'   => (int) $feedback->screen_height,
            'viewport_width'  => (int) $feedback->viewport_width,
            'viewport_height' => (int) $feedback->viewport_height,
            'browser'         => $feedback->browser,
            'os'              => $feedback->os,
            'device'          => $feedback->device,
            'selector'        => $feedback->selector,
            'element_offset_x' => (float) $feedback->element_offset_x,
            'element_offset_y' => (float) $feedback->element_offset_y,
            'scroll_x'        => (int) $feedback->scroll_x,
            'scroll_y'        => (int) $feedback->scroll_y,
            'replies'         => self::get_feedback_replies( $feedback->id ),
            // Informations système complètes
            'system_info'     => array(
                'device_pixel_ratio'    => $feedback->device_pixel_ratio,
                'color_depth'           => $feedback->color_depth,
                'orientation'           => $feedback->orientation,
                'browser_version'       => $feedback->browser_version,
                'os_version'            => $feedback->os_version,
                'platform'              => $feedback->platform,
                'language'              => $feedback->language,
                'languages'             => $feedback->languages,
                'timezone'              => $feedback->timezone,
                'timezone_offset'       => $feedback->timezone_offset,
                'local_time'            => $feedback->local_time,
                'cookies_enabled'       => (bool) $feedback->cookies_enabled,
                'online'                => (bool) $feedback->online,
                'touch_support'         => (bool) $feedback->touch_support,
                'max_touch_points'      => (int) $feedback->max_touch_points,
                'device_memory'         => $feedback->device_memory,
                'hardware_concurrency'  => $feedback->hardware_concurrency,
                'connection_type'       => $feedback->connection_type,
                'referrer'              => $feedback->referrer,
                'user_agent'            => $feedback->user_agent,
            ),
        );

        /**
         * Filtre les données du feedback pour l'API
         *
         * @since 1.0.0
         * @param array  $data     Données du feedback
         * @param object $feedback Feedback de la base de données
         */
        return apply_filters( 'wpvfh_feedback_data', $data, $feedback );
    }

    /**
     * Obtenir les réponses d'un feedback
     *
     * @since 1.0.0
     * @param int $feedback_id ID du feedback
     * @return array
     */
    public static function get_feedback_replies( $feedback_id ) {
        $db_replies = WPVFH_Database::get_replies( $feedback_id );

        $replies = array();
        foreach ( $db_replies as $reply ) {
            $author_name = $reply->author_name;
            $author_email = $reply->author_email;

            if ( ! empty( $reply->user_id ) ) {
                $user = get_userdata( $reply->user_id );
                if ( $user ) {
                    $author_name = $user->display_name;
                    $author_email = $user->user_email;
                }
            }

            $replies[] = array(
                'id'        => (int) $reply->id,
                'content'   => $reply->content,
                'date'      => $reply->created_at,
                'author'    => array(
                    'id'     => (int) $reply->user_id,
                    'name'   => $author_name,
                    'email'  => $author_email,
                    'avatar' => get_avatar_url( $author_email ?: $reply->user_id, array( 'size' => 32 ) ),
                ),
            );
        }

        return $replies;
    }

    /**
     * Ajouter une réponse à un feedback
     *
     * @since 1.7.0
     * @param int    $feedback_id ID du feedback
     * @param string $content     Contenu de la réponse
     * @param int    $user_id     ID de l'utilisateur (optionnel)
     * @return int|false ID de la réponse ou false
     */
    public static function add_reply( $feedback_id, $content, $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata( $user_id );

        return WPVFH_Database::insert_reply( array(
            'feedback_id'  => $feedback_id,
            'user_id'      => $user_id ?: null,
            'author_name'  => $user ? $user->display_name : __( 'Anonyme', 'blazing-feedback' ),
            'author_email' => $user ? $user->user_email : '',
            'content'      => $content,
        ) );
    }

    /**
     * Mettre à jour un feedback
     *
     * @since 1.7.0
     * @param int   $feedback_id ID du feedback
     * @param array $data        Données à mettre à jour
     * @return bool
     */
    public static function update_feedback( $feedback_id, $data ) {
        return WPVFH_Database::update_feedback( $feedback_id, $data );
    }

    /**
     * Supprimer un feedback
     *
     * @since 1.7.0
     * @param int $feedback_id ID du feedback
     * @return bool
     */
    public static function delete_feedback( $feedback_id ) {
        // Récupérer le screenshot pour le supprimer aussi
        $feedback = WPVFH_Database::get_feedback( $feedback_id );
        if ( $feedback && ! empty( $feedback->screenshot_id ) ) {
            wp_delete_attachment( $feedback->screenshot_id, true );
        }

        return WPVFH_Database::delete_feedback( $feedback_id );
    }
}

// Initialiser le CPT
WPVFH_CPT_Feedback::init();
