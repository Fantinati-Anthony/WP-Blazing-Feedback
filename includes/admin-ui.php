<?php
/**
 * Interface d'administration de Blazing Feedback
 *
 * Dashboard, paramètres et pages admin
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe de gestion de l'interface admin
 *
 * @since 1.0.0
 */
class WPVFH_Admin_UI {

    /**
     * Initialiser l'interface admin
     *
     * @since 1.0.0
     * @return void
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_menu_pages' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
        add_action( 'admin_init', array( __CLASS__, 'handle_danger_zone_actions' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_styles' ) );

        // Actions AJAX admin
        add_action( 'wp_ajax_wpvfh_quick_status_update', array( __CLASS__, 'ajax_quick_status_update' ) );
        add_action( 'wp_ajax_wpvfh_dismiss_notice', array( __CLASS__, 'ajax_dismiss_notice' ) );

        // Notices
        add_action( 'admin_notices', array( __CLASS__, 'show_admin_notices' ) );

        // Liens dans la page plugins
        add_filter( 'plugin_action_links_' . WPVFH_PLUGIN_BASENAME, array( __CLASS__, 'add_plugin_links' ) );
    }

    /**
     * Ajouter les pages de menu admin
     *
     * @since 1.0.0
     * @return void
     */
    public static function add_menu_pages() {
        // Page principale - Dashboard
        add_menu_page(
            __( 'Blazing Feedback', 'blazing-feedback' ),
            __( 'Feedbacks', 'blazing-feedback' ),
            'edit_feedbacks',
            'wpvfh-dashboard',
            array( __CLASS__, 'render_dashboard_page' ),
            'dashicons-format-chat',
            30
        );

        // Sous-page - Dashboard (redirection pour remplacer le titre auto)
        add_submenu_page(
            'wpvfh-dashboard',
            __( 'Tableau de bord', 'blazing-feedback' ),
            __( 'Tableau de bord', 'blazing-feedback' ),
            'edit_feedbacks',
            'wpvfh-dashboard',
            array( __CLASS__, 'render_dashboard_page' )
        );

        // Note: "Tous les feedbacks" est ajouté automatiquement par le CPT
        // avec show_in_menu => 'wpvfh-dashboard'

        // Sous-page - Paramètres
        add_submenu_page(
            'wpvfh-dashboard',
            __( 'Paramètres', 'blazing-feedback' ),
            __( 'Paramètres', 'blazing-feedback' ),
            'manage_feedback',
            'wpvfh-settings',
            array( __CLASS__, 'render_settings_page' )
        );
    }

    /**
     * Enregistrer les paramètres
     *
     * @since 1.0.0
     * @return void
     */
    public static function register_settings() {
        // ========================================
        // Onglet Général
        // ========================================
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_screenshot_enabled',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => true,
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_guest_feedback',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => false,
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_position',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'bottom-right',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_panel_position',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'right',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_enabled_pages',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
                'default'           => '*',
            )
        );

        // ========================================
        // Onglet Graphisme
        // ========================================
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_color',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_hex_color',
                'default'           => '#e74c3c',
            )
        );

        // Style du bouton (collé ou séparé)
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_style',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'detached',
            )
        );

        // Forme pour bouton collé
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_attached_shape',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'quarter',
            )
        );

        // Border radius pour bouton séparé
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_border_radius',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 50,
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_border_radius_unit',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'percent',
            )
        );

        // Margin pour bouton séparé
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_margin',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 20,
            )
        );

        // Taille du bouton
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_button_size',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 56,
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_icon_mode',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'emoji',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_icon_emoji',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '💬',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_icon_image_url',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'default'           => '',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_logo_mode',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'default'           => 'none',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_logo_custom_url',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'default'           => '',
            )
        );

        // Couleurs du thème
        $theme_colors = array(
            'wpvfh_color_primary'       => '#e74c3c',
            'wpvfh_color_primary_hover' => '#c0392b',
            'wpvfh_color_secondary'     => '#3498db',
            'wpvfh_color_success'       => '#27ae60',
            'wpvfh_color_warning'       => '#f39c12',
            'wpvfh_color_danger'        => '#e74c3c',
            'wpvfh_color_text'          => '#333333',
            'wpvfh_color_text_light'    => '#666666',
            'wpvfh_color_bg'            => '#ffffff',
            'wpvfh_color_bg_light'      => '#f5f5f5',
            'wpvfh_color_border'        => '#dddddd',
        );

        foreach ( $theme_colors as $option_name => $default ) {
            register_setting(
                'wpvfh_general_settings',
                $option_name,
                array(
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_hex_color',
                    'default'           => $default,
                )
            );
        }

        // ========================================
        // Onglet Notifications
        // ========================================
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_email_notifications',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => true,
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_notification_email',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_email',
                'default'           => get_option( 'admin_email' ),
            )
        );

        // ========================================
        // Onglet IA
        // ========================================
        register_setting(
            'wpvfh_general_settings',
            'wpvfh_ai_enabled',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => false,
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_ai_api_key',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_ai_system_prompt',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
                'default'           => '',
            )
        );

        register_setting(
            'wpvfh_general_settings',
            'wpvfh_ai_analysis_prompt',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
                'default'           => '',
            )
        );
    }

    /**
     * Gérer les actions de la zone de danger
     *
     * @since 1.7.0
     * @return void
     */
    public static function handle_danger_zone_actions() {
        // Vérifier si on est sur la bonne page et qu'il y a une action
        if ( ! isset( $_GET['page'] ) || 'wpvfh-settings' !== $_GET['page'] || ! isset( $_GET['action'] ) ) {
            return;
        }

        $action = sanitize_key( $_GET['action'] );
        $redirect_url = admin_url( 'admin.php?page=wpvfh-settings' );

        // Vérifier les permissions
        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_die( esc_html__( 'Vous n\'avez pas les permissions nécessaires.', 'blazing-feedback' ) );
        }

        switch ( $action ) {
            case 'truncate_feedbacks':
                // Vider les feedbacks et réponses
                if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'wpvfh_truncate_feedbacks' ) ) {
                    wp_die( esc_html__( 'Nonce invalide.', 'blazing-feedback' ) );
                }

                // Supprimer aussi les screenshots
                $upload_dir = wp_upload_dir();
                $feedback_dir = $upload_dir['basedir'] . '/visual-feedback';
                if ( file_exists( $feedback_dir ) ) {
                    self::delete_directory_contents( $feedback_dir );
                }

                WPVFH_Database::truncate_feedback_tables();
                $redirect_url = add_query_arg( 'message', 'feedbacks_truncated', $redirect_url );
                break;

            case 'truncate_all':
                // Vider toutes les tables
                if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'wpvfh_truncate_all' ) ) {
                    wp_die( esc_html__( 'Nonce invalide.', 'blazing-feedback' ) );
                }

                // Supprimer les screenshots
                $upload_dir = wp_upload_dir();
                $feedback_dir = $upload_dir['basedir'] . '/visual-feedback';
                if ( file_exists( $feedback_dir ) ) {
                    self::delete_directory_contents( $feedback_dir );
                }

                WPVFH_Database::truncate_all_tables();
                $redirect_url = add_query_arg( 'message', 'all_truncated', $redirect_url );
                break;

            case 'drop_tables':
                // Supprimer les tables
                if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'wpvfh_drop_tables' ) ) {
                    wp_die( esc_html__( 'Nonce invalide.', 'blazing-feedback' ) );
                }

                // Supprimer les screenshots
                $upload_dir = wp_upload_dir();
                $feedback_dir = $upload_dir['basedir'] . '/visual-feedback';
                if ( file_exists( $feedback_dir ) ) {
                    self::delete_directory_contents( $feedback_dir );
                }

                WPVFH_Database::uninstall();
                $redirect_url = add_query_arg( 'message', 'tables_dropped', $redirect_url );
                break;

            case 'recreate_tables':
                // Recréer les tables
                if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'wpvfh_recreate_tables' ) ) {
                    wp_die( esc_html__( 'Nonce invalide.', 'blazing-feedback' ) );
                }

                WPVFH_Database::install();
                $redirect_url = add_query_arg( 'message', 'tables_recreated', $redirect_url );
                break;

            default:
                return;
        }

        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Supprimer le contenu d'un répertoire (mais pas le répertoire lui-même)
     *
     * @since 1.7.0
     * @param string $dir Chemin du répertoire
     * @return void
     */
    private static function delete_directory_contents( $dir ) {
        if ( ! is_dir( $dir ) ) {
            return;
        }

        $files = array_diff( scandir( $dir ), array( '.', '..' ) );
        foreach ( $files as $file ) {
            $path = $dir . '/' . $file;
            if ( is_dir( $path ) ) {
                self::delete_directory_contents( $path );
                rmdir( $path );
            } else {
                unlink( $path );
            }
        }
    }

    /**
     * Charger les styles admin
     *
     * @since 1.0.0
     * @param string $hook Page actuelle
     * @return void
     */
    public static function enqueue_admin_styles( $hook ) {
        // Charger sur toutes les pages du plugin
        if ( strpos( $hook, 'wpvfh' ) !== false || get_current_screen()->post_type === 'visual_feedback' ) {
            wp_add_inline_style( 'wp-admin', self::get_admin_inline_styles() );

            // Charger la bibliothèque de médias sur la page des paramètres
            if ( strpos( $hook, 'wpvfh-settings' ) !== false ) {
                wp_enqueue_media();
            }
        }
    }

    /**
     * Styles CSS inline pour l'admin
     *
     * @since 1.0.0
     * @return string
     */
    private static function get_admin_inline_styles() {
        return '
            .wpvfh-dashboard-wrap {
                max-width: 1200px;
            }
            .wpvfh-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            .wpvfh-stat-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 20px;
                text-align: center;
            }
            .wpvfh-stat-card h3 {
                margin: 0 0 10px;
                color: #1d2327;
            }
            .wpvfh-stat-number {
                font-size: 36px;
                font-weight: 600;
                color: #2271b1;
                line-height: 1;
            }
            .wpvfh-stat-label {
                color: #50575e;
                margin-top: 5px;
            }
            .wpvfh-status-new { color: #3498db; }
            .wpvfh-status-in_progress { color: #f39c12; }
            .wpvfh-status-resolved { color: #27ae60; }
            .wpvfh-status-rejected { color: #e74c3c; }
            .wpvfh-recent-feedbacks {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                margin: 20px 0;
            }
            .wpvfh-recent-feedbacks h3 {
                padding: 15px 20px;
                margin: 0;
                border-bottom: 1px solid #ccd0d4;
            }
            .wpvfh-feedback-list {
                padding: 0;
                margin: 0;
                list-style: none;
            }
            .wpvfh-feedback-item {
                padding: 15px 20px;
                border-bottom: 1px solid #f0f0f1;
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .wpvfh-feedback-item:last-child {
                border-bottom: none;
            }
            .wpvfh-feedback-avatar {
                flex-shrink: 0;
            }
            .wpvfh-feedback-avatar img {
                border-radius: 50%;
            }
            .wpvfh-feedback-content {
                flex: 1;
                min-width: 0;
            }
            .wpvfh-feedback-title {
                font-weight: 500;
                margin: 0 0 5px;
            }
            .wpvfh-feedback-meta {
                color: #50575e;
                font-size: 13px;
            }
            .wpvfh-feedback-status {
                flex-shrink: 0;
            }
            .wpvfh-status-badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: 500;
            }
            .wpvfh-badge-new { background: #e3f2fd; color: #1565c0; }
            .wpvfh-badge-in_progress { background: #fff3e0; color: #ef6c00; }
            .wpvfh-badge-resolved { background: #e8f5e9; color: #2e7d32; }
            .wpvfh-badge-rejected { background: #ffebee; color: #c62828; }
            .wpvfh-quick-actions {
                display: flex;
                gap: 10px;
                margin-top: 20px;
            }
        ';
    }

    /**
     * Rendu de la page dashboard
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_dashboard_page() {
        // Récupérer les statistiques
        $stats = self::get_dashboard_stats();
        ?>
        <div class="wrap wpvfh-dashboard-wrap">
            <h1><?php esc_html_e( 'Blazing Feedback - Tableau de bord', 'blazing-feedback' ); ?></h1>

            <!-- Statistiques -->
            <div class="wpvfh-stats-grid">
                <div class="wpvfh-stat-card">
                    <div class="wpvfh-stat-number"><?php echo esc_html( $stats['total'] ); ?></div>
                    <div class="wpvfh-stat-label"><?php esc_html_e( 'Total des feedbacks', 'blazing-feedback' ); ?></div>
                </div>
                <div class="wpvfh-stat-card">
                    <div class="wpvfh-stat-number wpvfh-status-new"><?php echo esc_html( $stats['new'] ); ?></div>
                    <div class="wpvfh-stat-label"><?php esc_html_e( 'Nouveaux', 'blazing-feedback' ); ?></div>
                </div>
                <div class="wpvfh-stat-card">
                    <div class="wpvfh-stat-number wpvfh-status-in_progress"><?php echo esc_html( $stats['in_progress'] ); ?></div>
                    <div class="wpvfh-stat-label"><?php esc_html_e( 'En cours', 'blazing-feedback' ); ?></div>
                </div>
                <div class="wpvfh-stat-card">
                    <div class="wpvfh-stat-number wpvfh-status-resolved"><?php echo esc_html( $stats['resolved'] ); ?></div>
                    <div class="wpvfh-stat-label"><?php esc_html_e( 'Résolus', 'blazing-feedback' ); ?></div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="wpvfh-quick-actions">
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=visual_feedback' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Voir tous les feedbacks', 'blazing-feedback' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpvfh-settings' ) ); ?>" class="button">
                    <?php esc_html_e( 'Paramètres', 'blazing-feedback' ); ?>
                </a>
                <?php if ( current_user_can( 'export_feedback' ) ) : ?>
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wpvfh-dashboard&action=export' ), 'wpvfh_export' ) ); ?>" class="button">
                    <?php esc_html_e( 'Exporter', 'blazing-feedback' ); ?>
                </a>
                <?php endif; ?>
            </div>

            <!-- Feedbacks récents -->
            <div class="wpvfh-recent-feedbacks">
                <h3><?php esc_html_e( 'Feedbacks récents', 'blazing-feedback' ); ?></h3>
                <?php
                $recent = self::get_recent_feedbacks( 10 );
                if ( $recent ) :
                ?>
                <ul class="wpvfh-feedback-list">
                    <?php foreach ( $recent as $feedback ) : ?>
                    <li class="wpvfh-feedback-item">
                        <div class="wpvfh-feedback-avatar">
                            <?php echo get_avatar( $feedback->post_author, 40 ); ?>
                        </div>
                        <div class="wpvfh-feedback-content">
                            <p class="wpvfh-feedback-title">
                                <a href="<?php echo esc_url( get_edit_post_link( $feedback->ID ) ); ?>">
                                    <?php echo esc_html( $feedback->post_title ); ?>
                                </a>
                            </p>
                            <p class="wpvfh-feedback-meta">
                                <?php
                                $author = get_userdata( $feedback->post_author );
                                $url = get_post_meta( $feedback->ID, '_wpvfh_url', true );
                                printf(
                                    /* translators: %1$s: author name, %2$s: date, %3$s: page path */
                                    esc_html__( 'Par %1$s le %2$s sur %3$s', 'blazing-feedback' ),
                                    '<strong>' . esc_html( $author ? $author->display_name : __( 'Anonyme', 'blazing-feedback' ) ) . '</strong>',
                                    esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $feedback->post_date ) ) ),
                                    '<code>' . esc_html( wp_parse_url( $url, PHP_URL_PATH ) ?: '/' ) . '</code>'
                                );
                                ?>
                            </p>
                        </div>
                        <div class="wpvfh-feedback-status">
                            <?php
                            $status = get_post_meta( $feedback->ID, '_wpvfh_status', true ) ?: 'new';
                            $status_data = WPVFH_Options_Manager::get_status_by_id( $status );
                            if ( ! $status_data ) {
                                $status_data = WPVFH_Options_Manager::get_status_by_id( 'new' );
                            }
                            ?>
                            <span class="wpvfh-status-badge wpvfh-badge-<?php echo esc_attr( $status ); ?>">
                                <?php echo esc_html( ( $status_data['emoji'] ?? '' ) . ' ' . ( $status_data['label'] ?? $status ) ); ?>
                            </span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else : ?>
                <p style="padding: 20px; text-align: center; color: #50575e;">
                    <?php esc_html_e( 'Aucun feedback pour le moment.', 'blazing-feedback' ); ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Pages les plus commentées -->
            <div class="wpvfh-recent-feedbacks">
                <h3><?php esc_html_e( 'Pages les plus commentées', 'blazing-feedback' ); ?></h3>
                <?php
                $top_pages = self::get_top_pages( 5 );
                if ( $top_pages ) :
                ?>
                <ul class="wpvfh-feedback-list">
                    <?php foreach ( $top_pages as $page ) : ?>
                    <li class="wpvfh-feedback-item">
                        <div class="wpvfh-feedback-content">
                            <p class="wpvfh-feedback-title">
                                <a href="<?php echo esc_url( $page->url ); ?>" target="_blank">
                                    <?php echo esc_html( $page->path ); ?>
                                </a>
                            </p>
                        </div>
                        <div class="wpvfh-feedback-status">
                            <span class="wpvfh-status-badge">
                                <?php
                                printf(
                                    /* translators: %d: number of feedbacks */
                                    esc_html( _n( '%d feedback', '%d feedbacks', $page->count, 'blazing-feedback' ) ),
                                    $page->count
                                );
                                ?>
                            </span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else : ?>
                <p style="padding: 20px; text-align: center; color: #50575e;">
                    <?php esc_html_e( 'Aucune donnée disponible.', 'blazing-feedback' ); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Rendu de la page paramètres
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_settings_page() {
        // Vérifier les permissions
        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_die( esc_html__( 'Vous n\'avez pas les permissions nécessaires.', 'blazing-feedback' ) );
        }

        // Onglet actif
        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

        // Afficher les messages de succès
        $message = isset( $_GET['message'] ) ? sanitize_key( $_GET['message'] ) : '';
        ?>
        <div class="wrap wpvfh-settings-wrap">
            <h1><?php esc_html_e( 'Blazing Feedback - Paramètres', 'blazing-feedback' ); ?></h1>

            <?php if ( $message ) :
                $messages = array(
                    'feedbacks_truncated' => __( 'Tous les feedbacks ont été supprimés.', 'blazing-feedback' ),
                    'all_truncated'       => __( 'Toutes les tables ont été vidées.', 'blazing-feedback' ),
                    'tables_dropped'      => __( 'Toutes les tables ont été supprimées.', 'blazing-feedback' ),
                    'tables_recreated'    => __( 'Les tables ont été recréées avec succès.', 'blazing-feedback' ),
                );
                if ( isset( $messages[ $message ] ) ) :
            ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html( $messages[ $message ] ); ?></p></div>
            <?php endif; endif; ?>

            <!-- Navigation par onglets -->
            <nav class="nav-tab-wrapper wpvfh-nav-tabs">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpvfh-settings&tab=general' ) ); ?>"
                   class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php esc_html_e( 'Général', 'blazing-feedback' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpvfh-settings&tab=design' ) ); ?>"
                   class="nav-tab <?php echo 'design' === $active_tab ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-appearance"></span>
                    <?php esc_html_e( 'Personnalisation', 'blazing-feedback' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpvfh-settings&tab=notifications' ) ); ?>"
                   class="nav-tab <?php echo 'notifications' === $active_tab ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-email"></span>
                    <?php esc_html_e( 'Notifications', 'blazing-feedback' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpvfh-settings&tab=ai' ) ); ?>"
                   class="nav-tab <?php echo 'ai' === $active_tab ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-welcome-learn-more"></span>
                    <?php esc_html_e( 'IA', 'blazing-feedback' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpvfh-settings&tab=danger' ) ); ?>"
                   class="nav-tab <?php echo 'danger' === $active_tab ? 'nav-tab-active' : ''; ?>" style="color: #dc3545;">
                    <span class="dashicons dashicons-warning"></span>
                    <?php esc_html_e( 'Zone de danger', 'blazing-feedback' ); ?>
                </a>
            </nav>

            <form method="post" action="options.php" class="wpvfh-settings-form">
                <?php settings_fields( 'wpvfh_general_settings' ); ?>

                <!-- Onglet Général -->
                <div class="wpvfh-tab-content <?php echo 'general' === $active_tab ? 'active' : ''; ?>" id="tab-general">
                    <?php self::render_tab_general(); ?>
                </div>

                <!-- Onglet Graphisme -->
                <div class="wpvfh-tab-content <?php echo 'design' === $active_tab ? 'active' : ''; ?>" id="tab-design">
                    <?php self::render_tab_design(); ?>
                </div>

                <!-- Onglet Notifications -->
                <div class="wpvfh-tab-content <?php echo 'notifications' === $active_tab ? 'active' : ''; ?>" id="tab-notifications">
                    <?php self::render_tab_notifications(); ?>
                </div>

                <!-- Onglet IA -->
                <div class="wpvfh-tab-content <?php echo 'ai' === $active_tab ? 'active' : ''; ?>" id="tab-ai">
                    <?php self::render_tab_ai(); ?>
                </div>

                <?php if ( 'danger' !== $active_tab ) : ?>
                    <?php submit_button(); ?>
                <?php endif; ?>
            </form>

            <!-- Onglet Zone de danger (hors formulaire) -->
            <div class="wpvfh-tab-content <?php echo 'danger' === $active_tab ? 'active' : ''; ?>" id="tab-danger">
                <?php self::render_tab_danger(); ?>
            </div>
        </div>

        <style>
            .wpvfh-settings-wrap { max-width: 1200px; }
            .wpvfh-nav-tabs { margin-bottom: 20px; }
            .wpvfh-nav-tabs .nav-tab { display: inline-flex; align-items: center; gap: 5px; }
            .wpvfh-nav-tabs .dashicons { font-size: 16px; width: 16px; height: 16px; }
            .wpvfh-tab-content { display: none; background: #fff; padding: 20px; border: 1px solid #c3c4c7; border-top: none; }
            .wpvfh-tab-content.active { display: block; }
            .wpvfh-settings-section { margin-bottom: 30px; }
            .wpvfh-settings-section h2 { font-size: 1.3em; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 15px; }
            .wpvfh-settings-row { display: flex; margin-bottom: 15px; align-items: flex-start; }
            .wpvfh-settings-row label { flex: 0 0 200px; font-weight: 500; padding-top: 5px; }
            .wpvfh-settings-row .wpvfh-field { flex: 1; }
            .wpvfh-settings-row .description { color: #666; font-size: 13px; margin-top: 5px; }
            .wpvfh-preview-container { display: flex; gap: 30px; margin-top: 20px; }
            .wpvfh-preview-settings { flex: 1; }
            .wpvfh-preview-widget { flex: 0 0 400px; position: sticky; top: 50px; }
            .wpvfh-preview-box { background: #f0f0f1; border-radius: 8px; padding: 20px; min-height: 300px; position: relative; }
            .wpvfh-color-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
            @media (max-width: 1200px) {
                .wpvfh-preview-container { flex-direction: column; }
                .wpvfh-preview-widget { flex: none; position: static; }
            }
        </style>
        <?php
    }

    /**
     * Rendu de l'onglet Général
     *
     * @since 1.8.0
     * @return void
     */
    public static function render_tab_general() {
        ?>
        <div class="wpvfh-settings-section">
            <h2><?php esc_html_e( 'Paramètres généraux', 'blazing-feedback' ); ?></h2>
            <p class="description"><?php esc_html_e( 'Configurez le comportement général du widget de feedback.', 'blazing-feedback' ); ?></p>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Capture d\'écran', 'blazing-feedback' ); ?></th>
                    <td>
                        <?php self::render_screenshot_field(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Feedback anonyme', 'blazing-feedback' ); ?></th>
                    <td>
                        <?php self::render_guest_field(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Pages actives', 'blazing-feedback' ); ?></th>
                    <td>
                        <?php self::render_pages_field(); ?>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Rendu de l'onglet Personnalisation
     *
     * @since 1.8.0
     * @return void
     */
    public static function render_tab_design() {
        $button_color = get_option( 'wpvfh_button_color', '#e74c3c' );
        $button_color_hover = get_option( 'wpvfh_color_primary_hover', '#c0392b' );
        $icon_mode = get_option( 'wpvfh_icon_mode', 'emoji' );
        $icon_emoji = get_option( 'wpvfh_icon_emoji', '💬' );
        $icon_image = get_option( 'wpvfh_icon_image_url', '' );
        $button_style = get_option( 'wpvfh_button_style', 'detached' );
        $button_position = get_option( 'wpvfh_button_position', 'bottom-right' );
        $border_radius = get_option( 'wpvfh_button_border_radius', 50 );
        $border_radius_unit = get_option( 'wpvfh_button_border_radius_unit', 'percent' );
        $button_margin = get_option( 'wpvfh_button_margin', 20 );
        $button_size = get_option( 'wpvfh_button_size', 56 );

        // Positions d'angle = quart de cercle, positions centrales = demi-cercle
        $corner_positions = array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' );
        $is_corner = in_array( $button_position, $corner_positions, true );
        ?>
        <div class="wpvfh-preview-container">
            <div class="wpvfh-preview-settings">
                <!-- Position -->
                <div class="wpvfh-settings-section">
                    <h2><?php esc_html_e( 'Position', 'blazing-feedback' ); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Position du bouton', 'blazing-feedback' ); ?></th>
                            <td>
                                <?php self::render_position_field(); ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Position du volet', 'blazing-feedback' ); ?></th>
                            <td>
                                <?php self::render_panel_position_field(); ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Bouton flottant -->
                <div class="wpvfh-settings-section">
                    <h2><?php esc_html_e( 'Bouton flottant', 'blazing-feedback' ); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Couleur du bouton', 'blazing-feedback' ); ?></th>
                            <td>
                                <?php self::render_color_field(); ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Taille du bouton', 'blazing-feedback' ); ?></th>
                            <td>
                                <input type="range" name="wpvfh_button_size" id="wpvfh_button_size" min="40" max="80" value="<?php echo esc_attr( $button_size ); ?>" style="width: 150px; vertical-align: middle;">
                                <span id="wpvfh_button_size_value" style="margin-left: 10px; font-weight: 500;"><?php echo esc_html( $button_size ); ?>px</span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Style du bouton', 'blazing-feedback' ); ?></th>
                            <td>
                                <fieldset>
                                    <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; cursor: pointer;">
                                        <input type="radio" name="wpvfh_button_style" value="detached" <?php checked( $button_style, 'detached' ); ?>>
                                        <span style="display: flex; align-items: center; gap: 8px;">
                                            <span style="width: 32px; height: 32px; background: #e74c3c; border-radius: 50%; display: inline-block;"></span>
                                            <span>
                                                <strong><?php esc_html_e( 'Séparé', 'blazing-feedback' ); ?></strong><br>
                                                <small style="color: #666;"><?php esc_html_e( 'Bouton flottant avec marge', 'blazing-feedback' ); ?></small>
                                            </span>
                                        </span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <input type="radio" name="wpvfh_button_style" value="attached" <?php checked( $button_style, 'attached' ); ?>>
                                        <span style="display: flex; align-items: center; gap: 8px;">
                                            <span id="wpvfh-attached-style-icon" style="width: 32px; height: 32px; background: #e74c3c; border-radius: <?php echo $is_corner ? '0 0 0 16px' : '16px 0 0 16px'; ?>; display: inline-block;"></span>
                                            <span>
                                                <strong><?php esc_html_e( 'Collé', 'blazing-feedback' ); ?></strong><br>
                                                <small style="color: #666;" id="wpvfh-attached-style-desc">
                                                    <?php echo $is_corner ? esc_html__( 'Quart de cercle (position d\'angle)', 'blazing-feedback' ) : esc_html__( 'Demi-cercle (position centrale)', 'blazing-feedback' ); ?>
                                                </small>
                                            </span>
                                        </span>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                    </table>

                    <!-- Options pour bouton séparé -->
                    <div id="wpvfh-detached-options" style="<?php echo 'attached' === $button_style ? 'display: none;' : ''; ?> margin-left: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px; margin-top: 10px;">
                        <h4 style="margin-top: 0;"><?php esc_html_e( 'Options du bouton séparé', 'blazing-feedback' ); ?></h4>
                        <table class="form-table" style="margin: 0;">
                            <tr>
                                <th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Border radius', 'blazing-feedback' ); ?></th>
                                <td style="padding: 10px 0;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <input type="number" name="wpvfh_button_border_radius" id="wpvfh_button_border_radius" value="<?php echo esc_attr( $border_radius ); ?>" min="0" max="100" style="width: 70px;">
                                        <select name="wpvfh_button_border_radius_unit" id="wpvfh_button_border_radius_unit">
                                            <option value="percent" <?php selected( $border_radius_unit, 'percent' ); ?>>%</option>
                                            <option value="px" <?php selected( $border_radius_unit, 'px' ); ?>>px</option>
                                        </select>
                                        <input type="range" id="wpvfh_border_radius_slider" min="0" max="50" value="<?php echo esc_attr( $border_radius ); ?>" style="width: 100px;">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Marge', 'blazing-feedback' ); ?></th>
                                <td style="padding: 10px 0;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <input type="number" name="wpvfh_button_margin" id="wpvfh_button_margin" value="<?php echo esc_attr( $button_margin ); ?>" min="0" max="50" style="width: 70px;">
                                        <span>px</span>
                                        <input type="range" id="wpvfh_margin_slider" min="0" max="50" value="<?php echo esc_attr( $button_margin ); ?>" style="width: 100px;">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <table class="form-table" style="margin-top: 20px;">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Icône du bouton', 'blazing-feedback' ); ?></th>
                            <td>
                                <?php self::render_icon_mode_field(); ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Logo du panneau -->
                <div class="wpvfh-settings-section">
                    <h2><?php esc_html_e( 'Logo du panneau', 'blazing-feedback' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'Personnalisez le logo affiché dans l\'entête du panneau.', 'blazing-feedback' ); ?></p>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Mode du logo', 'blazing-feedback' ); ?></th>
                            <td>
                                <?php self::render_logo_mode_field(); ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Couleurs du thème -->
                <div class="wpvfh-settings-section">
                    <h2><?php esc_html_e( 'Couleurs du thème', 'blazing-feedback' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'Personnalisez les couleurs du widget de feedback.', 'blazing-feedback' ); ?></p>

                    <div class="wpvfh-color-grid">
                        <?php
                        $colors = array(
                            'wpvfh_color_primary'       => __( 'Principale', 'blazing-feedback' ),
                            'wpvfh_color_primary_hover' => __( 'Principale (survol)', 'blazing-feedback' ),
                            'wpvfh_color_secondary'     => __( 'Secondaire', 'blazing-feedback' ),
                            'wpvfh_color_success'       => __( 'Succès', 'blazing-feedback' ),
                            'wpvfh_color_warning'       => __( 'Avertissement', 'blazing-feedback' ),
                            'wpvfh_color_danger'        => __( 'Danger', 'blazing-feedback' ),
                            'wpvfh_color_text'          => __( 'Texte', 'blazing-feedback' ),
                            'wpvfh_color_text_light'    => __( 'Texte secondaire', 'blazing-feedback' ),
                            'wpvfh_color_bg'            => __( 'Fond', 'blazing-feedback' ),
                            'wpvfh_color_bg_light'      => __( 'Fond secondaire', 'blazing-feedback' ),
                            'wpvfh_color_border'        => __( 'Bordures', 'blazing-feedback' ),
                        );
                        $defaults = array(
                            'wpvfh_color_primary'       => '#e74c3c',
                            'wpvfh_color_primary_hover' => '#c0392b',
                            'wpvfh_color_secondary'     => '#3498db',
                            'wpvfh_color_success'       => '#27ae60',
                            'wpvfh_color_warning'       => '#f39c12',
                            'wpvfh_color_danger'        => '#e74c3c',
                            'wpvfh_color_text'          => '#333333',
                            'wpvfh_color_text_light'    => '#666666',
                            'wpvfh_color_bg'            => '#ffffff',
                            'wpvfh_color_bg_light'      => '#f5f5f5',
                            'wpvfh_color_border'        => '#dddddd',
                        );
                        foreach ( $colors as $option_name => $label ) :
                            $value = get_option( $option_name, $defaults[ $option_name ] );
                        ?>
                        <div class="wpvfh-color-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #f9f9f9; border-radius: 4px;">
                            <input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
                            <input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
                            <span style="flex: 1; font-size: 13px;"><?php echo esc_html( $label ); ?></span>
                            <button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $defaults[ $option_name ] ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>">
                                <span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Prévisualisation -->
            <div class="wpvfh-preview-widget">
                <h3 style="margin-top: 0;"><?php esc_html_e( 'Prévisualisation', 'blazing-feedback' ); ?></h3>
                <div class="wpvfh-preview-box" id="wpvfh-preview-box">
                    <!-- Bouton de feedback preview -->
                    <div id="wpvfh-preview-button-wrapper" style="position: absolute; bottom: 0; right: 0;">
                        <div id="wpvfh-preview-button" class="wpvfh-preview-btn">
                            <span id="wpvfh-preview-icon">
                                <?php if ( 'emoji' === $icon_mode ) : ?>
                                    <?php echo esc_html( $icon_emoji ); ?>
                                <?php elseif ( 'image' === $icon_mode && $icon_image ) : ?>
                                    <img src="<?php echo esc_url( $icon_image ); ?>" alt="">
                                <?php else : ?>
                                    💬
                                <?php endif; ?>
                            </span>
                        </div>
                        <!-- Compteur preview -->
                        <div id="wpvfh-preview-badge">3</div>
                    </div>
                </div>
                <p class="description" style="margin-top: 10px; text-align: center;">
                    <?php esc_html_e( 'Cliquez sur le bouton pour voir l\'effet', 'blazing-feedback' ); ?>
                </p>
            </div>
        </div>

        <style>
            .wpvfh-preview-box {
                background: #f0f0f1;
                border-radius: 8px;
                min-height: 350px;
                position: relative;
                overflow: hidden;
            }
            .wpvfh-preview-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s ease;
                background: <?php echo esc_attr( $button_color ); ?>;
            }
            .wpvfh-preview-btn:hover {
                transform: scale(1.1);
                background: <?php echo esc_attr( $button_color_hover ); ?> !important;
            }
            .wpvfh-preview-btn.active {
                transform: scale(1.1) rotate(45deg);
            }
            .wpvfh-preview-btn #wpvfh-preview-icon {
                font-size: 24px;
                color: #fff;
                transition: transform 0.2s ease;
                line-height: 1;
            }
            .wpvfh-preview-btn.active #wpvfh-preview-icon {
                transform: rotate(-45deg);
            }
            .wpvfh-preview-btn #wpvfh-preview-icon img {
                width: 28px;
                height: 28px;
                object-fit: contain;
                filter: brightness(0) invert(1);
            }
            #wpvfh-preview-badge {
                position: absolute;
                background: #dc3545;
                color: #fff;
                font-size: 11px;
                font-weight: bold;
                padding: 2px 6px;
                border-radius: 10px;
                min-width: 18px;
                text-align: center;
                transition: all 0.2s ease;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            var previewActive = false;
            var cornerPositions = ['bottom-right', 'bottom-left', 'top-right', 'top-left'];

            // Déterminer la forme selon la position
            function getShapeFromPosition() {
                var position = $('select[name="wpvfh_button_position"]').val();
                return cornerPositions.indexOf(position) !== -1 ? 'quarter' : 'half';
            }

            // Mise à jour de l'icône et description du style "Collé"
            function updateAttachedStyleInfo() {
                var shape = getShapeFromPosition();
                var $icon = $('#wpvfh-attached-style-icon');
                var $desc = $('#wpvfh-attached-style-desc');

                if (shape === 'quarter') {
                    $icon.css('border-radius', '0 0 0 16px');
                    $desc.text('<?php echo esc_js( __( 'Quart de cercle (position d\'angle)', 'blazing-feedback' ) ); ?>');
                } else {
                    $icon.css({'border-radius': '16px 0 0 16px', 'width': '16px'});
                    $desc.text('<?php echo esc_js( __( 'Demi-cercle (position centrale)', 'blazing-feedback' ) ); ?>');
                }
            }

            // Fonction de mise à jour du bouton preview
            function updateButtonPreview() {
                var style = $('input[name="wpvfh_button_style"]:checked').val();
                var size = parseInt($('#wpvfh_button_size').val()) || 56;
                var color = $('input[name="wpvfh_button_color"]').val();
                var $btn = $('#wpvfh-preview-button');
                var $wrapper = $('#wpvfh-preview-button-wrapper');
                var $badge = $('#wpvfh-preview-badge');

                if (style === 'attached') {
                    var shape = getShapeFromPosition();
                    if (shape === 'quarter') {
                        $btn.css({
                            'width': size + 'px',
                            'height': size + 'px',
                            'border-radius': '100% 0 0 0',
                            'background': color,
                            'box-shadow': '-2px -2px 12px rgba(0,0,0,0.2)'
                        });
                    } else {
                        $btn.css({
                            'width': (size / 2) + 'px',
                            'height': size + 'px',
                            'border-radius': size + 'px 0 0 ' + size + 'px',
                            'background': color,
                            'box-shadow': '-2px 0 12px rgba(0,0,0,0.2)'
                        });
                    }
                    $wrapper.css({
                        'bottom': '0',
                        'right': '0'
                    });
                    $badge.css({
                        'top': '-8px',
                        'right': shape === 'quarter' ? (size - 20) + 'px' : (size / 2 - 15) + 'px'
                    });
                } else {
                    var radius = $('#wpvfh_button_border_radius').val();
                    var unit = $('#wpvfh_button_border_radius_unit').val();
                    var margin = $('#wpvfh_button_margin').val();
                    var radiusValue = radius + (unit === 'percent' ? '%' : 'px');

                    $btn.css({
                        'width': size + 'px',
                        'height': size + 'px',
                        'border-radius': radiusValue,
                        'box-shadow': '0 4px 12px rgba(0,0,0,0.15)',
                        'background': color
                    });
                    $wrapper.css({
                        'bottom': margin + 'px',
                        'right': margin + 'px'
                    });
                    $badge.css({
                        'top': '-5px',
                        'right': '-5px'
                    });
                }
            }

            // Toggle du bouton preview
            $('#wpvfh-preview-button').on('click', function() {
                previewActive = !previewActive;
                $(this).toggleClass('active', previewActive);
            });

            // Changement de position
            $('select[name="wpvfh_button_position"]').on('change', function() {
                updateAttachedStyleInfo();
                updateButtonPreview();
            });

            // Style du bouton (collé/séparé)
            $('input[name="wpvfh_button_style"]').on('change', function() {
                var style = $(this).val();
                if (style === 'attached') {
                    $('#wpvfh-detached-options').slideUp();
                } else {
                    $('#wpvfh-detached-options').slideDown();
                }
                updateButtonPreview();
            });

            // Taille du bouton
            $('#wpvfh_button_size').on('input', function() {
                $('#wpvfh_button_size_value').text($(this).val() + 'px');
                updateButtonPreview();
            });

            // Border radius
            $('#wpvfh_button_border_radius, #wpvfh_border_radius_slider').on('input', function() {
                var val = $(this).val();
                $('#wpvfh_button_border_radius').val(val);
                $('#wpvfh_border_radius_slider').val(val);
                updateButtonPreview();
            });
            $('#wpvfh_button_border_radius_unit').on('change', function() {
                updateButtonPreview();
            });

            // Margin
            $('#wpvfh_button_margin, #wpvfh_margin_slider').on('input', function() {
                var val = $(this).val();
                $('#wpvfh_button_margin').val(val);
                $('#wpvfh_margin_slider').val(val);
                updateButtonPreview();
            });

            // Couleur du bouton
            $('input[name="wpvfh_button_color"]').on('input change', function() {
                updateButtonPreview();
            });

            // Synchroniser les inputs couleur et texte
            $('input[type="color"]').on('input change', function() {
                var optionName = $(this).attr('name');
                var hexInput = $('[data-color-input="' + optionName + '"]');
                hexInput.val($(this).val());
                updatePreview();
            });

            // Synchroniser le texte vers l'input couleur
            $('.wpvfh-color-hex-input').on('input change', function() {
                var optionName = $(this).data('color-input');
                var colorInput = $('#' + optionName);
                var value = $(this).val();
                if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                    colorInput.val(value);
                    updatePreview();
                }
            });

            // Réinitialiser la couleur par défaut
            $('.wpvfh-reset-color').on('click', function() {
                var optionName = $(this).data('option');
                var defaultValue = $(this).data('default');
                var colorInput = $('#' + optionName);
                var hexInput = $('[data-color-input="' + optionName + '"]');
                colorInput.val(defaultValue);
                hexInput.val(defaultValue);
                updatePreview();
                updateButtonPreview();
            });

            // Mise à jour icône
            $('input[name="wpvfh_icon_mode"]').on('change', function() {
                updateIconPreview();
            });
            $('input[name="wpvfh_icon_emoji"]').on('input', function() {
                if ($('input[name="wpvfh_icon_mode"]:checked').val() === 'emoji') {
                    $('#wpvfh-preview-icon').html($(this).val() || '💬');
                }
            });

            function updateIconPreview() {
                var mode = $('input[name="wpvfh_icon_mode"]:checked').val();
                if (mode === 'emoji') {
                    var emoji = $('input[name="wpvfh_icon_emoji"]').val() || '💬';
                    $('#wpvfh-preview-icon').html(emoji);
                } else if (mode === 'image') {
                    var imgUrl = $('input[name="wpvfh_icon_image_url"]').val();
                    if (imgUrl) {
                        $('#wpvfh-preview-icon').html('<img src="' + imgUrl + '">');
                    }
                }
            }

            function updatePreview() {
                var bgLight = $('#wpvfh_color_bg_light').val();
                $('#wpvfh-preview-box').css('background', bgLight);
                updateButtonPreview();
            }

            // Initialisation
            updateButtonPreview();
        });
        </script>
        <?php
    }

    /**
     * Rendu de l'onglet Notifications
     *
     * @since 1.8.0
     * @return void
     */
    public static function render_tab_notifications() {
        ?>
        <div class="wpvfh-settings-section">
            <h2><?php esc_html_e( 'Notifications par email', 'blazing-feedback' ); ?></h2>
            <p class="description"><?php esc_html_e( 'Configurez les notifications par email pour les nouveaux feedbacks.', 'blazing-feedback' ); ?></p>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Activer les notifications', 'blazing-feedback' ); ?></th>
                    <td>
                        <?php self::render_email_notifications_field(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Email de notification', 'blazing-feedback' ); ?></th>
                    <td>
                        <?php self::render_notification_email_field(); ?>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Rendu de l'onglet IA
     *
     * @since 1.8.0
     * @return void
     */
    public static function render_tab_ai() {
        $ai_enabled = get_option( 'wpvfh_ai_enabled', false );
        $api_key = get_option( 'wpvfh_ai_api_key', '' );
        $system_prompt = get_option( 'wpvfh_ai_system_prompt', '' );
        $analysis_prompt = get_option( 'wpvfh_ai_analysis_prompt', '' );
        ?>
        <div class="wpvfh-settings-section">
            <h2><?php esc_html_e( 'Intelligence Artificielle', 'blazing-feedback' ); ?></h2>
            <p class="description"><?php esc_html_e( 'Activez l\'IA pour analyser automatiquement les feedbacks et générer des suggestions.', 'blazing-feedback' ); ?></p>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Activer l\'IA', 'blazing-feedback' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wpvfh_ai_enabled" id="wpvfh_ai_enabled" value="1" <?php checked( $ai_enabled, true ); ?>>
                            <?php esc_html_e( 'Activer les fonctionnalités d\'intelligence artificielle', 'blazing-feedback' ); ?>
                        </label>
                        <p class="description"><?php esc_html_e( 'Permet d\'utiliser l\'IA pour analyser et catégoriser les feedbacks.', 'blazing-feedback' ); ?></p>
                    </td>
                </tr>
            </table>

            <div id="wpvfh-ai-settings" style="<?php echo ! $ai_enabled ? 'display: none;' : ''; ?>">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Clé API', 'blazing-feedback' ); ?></th>
                        <td>
                            <input type="password" name="wpvfh_ai_api_key" id="wpvfh_ai_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" autocomplete="off">
                            <button type="button" class="button button-small" id="wpvfh-toggle-api-key">
                                <span class="dashicons dashicons-visibility" style="vertical-align: middle;"></span>
                            </button>
                            <p class="description"><?php esc_html_e( 'Votre clé API pour le service d\'IA (OpenAI, Anthropic, etc.)', 'blazing-feedback' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Prompt système', 'blazing-feedback' ); ?></th>
                        <td>
                            <textarea name="wpvfh_ai_system_prompt" id="wpvfh_ai_system_prompt" rows="5" class="large-text" placeholder="<?php esc_attr_e( 'Vous êtes un assistant qui aide à analyser les retours utilisateurs...', 'blazing-feedback' ); ?>"><?php echo esc_textarea( $system_prompt ); ?></textarea>
                            <p class="description"><?php esc_html_e( 'Le prompt système définit le comportement général de l\'IA.', 'blazing-feedback' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Prompt d\'analyse', 'blazing-feedback' ); ?></th>
                        <td>
                            <textarea name="wpvfh_ai_analysis_prompt" id="wpvfh_ai_analysis_prompt" rows="5" class="large-text" placeholder="<?php esc_attr_e( 'Analysez ce feedback et suggérez une catégorie, une priorité et une réponse type...', 'blazing-feedback' ); ?>"><?php echo esc_textarea( $analysis_prompt ); ?></textarea>
                            <p class="description"><?php esc_html_e( 'Le prompt utilisé pour analyser chaque feedback.', 'blazing-feedback' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Toggle AI settings visibility
            $('#wpvfh_ai_enabled').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#wpvfh-ai-settings').slideDown();
                } else {
                    $('#wpvfh-ai-settings').slideUp();
                }
            });

            // Toggle API key visibility
            $('#wpvfh-toggle-api-key').on('click', function() {
                var input = $('#wpvfh_ai_api_key');
                var icon = $(this).find('.dashicons');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Rendu de l'onglet Zone de danger
     *
     * @since 1.8.0
     * @return void
     */
    public static function render_tab_danger() {
        $tables_exist = WPVFH_Database::tables_exist();
        $table_stats = $tables_exist ? WPVFH_Database::get_table_stats() : array();
        ?>
        <div class="wpvfh-danger-zone" style="padding: 20px; background: #fff; border: 2px solid #dc3545; border-radius: 4px;">
            <h2 style="color: #dc3545; margin-top: 0;">
                <span class="dashicons dashicons-warning" style="color: #dc3545;"></span>
                <?php esc_html_e( 'Zone de danger', 'blazing-feedback' ); ?>
            </h2>
            <p style="color: #666;"><?php esc_html_e( 'Ces actions sont irréversibles. Utilisez-les avec précaution.', 'blazing-feedback' ); ?></p>

            <?php if ( $tables_exist ) : ?>
                <!-- Stats des tables -->
                <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <h4 style="margin-top: 0;"><?php esc_html_e( 'État des tables', 'blazing-feedback' ); ?></h4>
                    <table class="widefat" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Table', 'blazing-feedback' ); ?></th>
                                <th style="text-align: right;"><?php esc_html_e( 'Entrées', 'blazing-feedback' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $table_stats as $key => $stat ) : ?>
                                <tr>
                                    <td><code><?php echo esc_html( $stat['table'] ); ?></code></td>
                                    <td style="text-align: right;"><?php echo esc_html( number_format_i18n( $stat['count'] ) ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Actions -->
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wpvfh-settings&tab=danger&action=truncate_feedbacks' ), 'wpvfh_truncate_feedbacks' ) ); ?>"
                       class="button"
                       style="border-color: #f0ad4e; color: #856404;"
                       onclick="return confirm('<?php esc_attr_e( 'Êtes-vous sûr de vouloir supprimer TOUS les feedbacks et réponses ? Cette action est irréversible.', 'blazing-feedback' ); ?>');">
                        <span class="dashicons dashicons-trash" style="vertical-align: middle;"></span>
                        <?php esc_html_e( 'Vider les feedbacks', 'blazing-feedback' ); ?>
                    </a>

                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wpvfh-settings&tab=danger&action=truncate_all' ), 'wpvfh_truncate_all' ) ); ?>"
                       class="button"
                       style="border-color: #dc3545; color: #dc3545;"
                       onclick="return confirm('<?php esc_attr_e( 'Êtes-vous sûr de vouloir vider TOUTES les tables (feedbacks, métadonnées, groupes, paramètres) ? Cette action est irréversible.', 'blazing-feedback' ); ?>');">
                        <span class="dashicons dashicons-database-remove" style="vertical-align: middle;"></span>
                        <?php esc_html_e( 'Vider toutes les tables', 'blazing-feedback' ); ?>
                    </a>

                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wpvfh-settings&tab=danger&action=drop_tables' ), 'wpvfh_drop_tables' ) ); ?>"
                       class="button button-link-delete"
                       style="background: #dc3545; border-color: #dc3545; color: #fff;"
                       onclick="return confirm('<?php esc_attr_e( 'ATTENTION : Êtes-vous sûr de vouloir SUPPRIMER toutes les tables de la base de données ? Vous devrez réactiver le plugin pour les recréer.', 'blazing-feedback' ); ?>');">
                        <span class="dashicons dashicons-database-remove" style="vertical-align: middle;"></span>
                        <?php esc_html_e( 'Supprimer les tables', 'blazing-feedback' ); ?>
                    </a>
                </div>

            <?php else : ?>
                <!-- Tables n'existent pas -->
                <div class="notice notice-warning inline" style="margin: 0 0 15px 0;">
                    <p>
                        <strong><?php esc_html_e( 'Les tables de base de données n\'existent pas.', 'blazing-feedback' ); ?></strong><br>
                        <?php esc_html_e( 'Cliquez sur le bouton ci-dessous pour les créer.', 'blazing-feedback' ); ?>
                    </p>
                </div>
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wpvfh-settings&tab=danger&action=recreate_tables' ), 'wpvfh_recreate_tables' ) ); ?>"
                   class="button button-primary">
                    <span class="dashicons dashicons-database-add" style="vertical-align: middle;"></span>
                    <?php esc_html_e( 'Créer les tables', 'blazing-feedback' ); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Rendu de la section générale (legacy - unused)
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_general_section() {
        // Legacy function - kept for backwards compatibility
    }

    /**
     * Rendu de la section notifications (legacy - unused)
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_notification_section() {
        // Legacy function - kept for backwards compatibility
    }

    /**
     * Rendu de la section logo (legacy - unused)
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_logo_section() {
        // Legacy function - kept for backwards compatibility
    }

    /**
     * Rendu de la section icône du bouton (legacy - unused)
     *
     * @since 1.7.0
     * @return void
     */
    public static function render_icon_section() {
        // Legacy function - kept for backwards compatibility
    }

    /**
     * Champ Mode de l'icône
     *
     * @since 1.7.0
     * @return void
     */
    public static function render_icon_mode_field() {
        $mode = get_option( 'wpvfh_icon_mode', 'emoji' );
        $emoji = get_option( 'wpvfh_icon_emoji', '💬' );
        $image_url = get_option( 'wpvfh_icon_image_url', '' );
        ?>
        <fieldset>
            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                <input type="radio" name="wpvfh_icon_mode" value="emoji" <?php checked( $mode, 'emoji' ); ?>>
                <?php esc_html_e( 'Emoji personnalisé', 'blazing-feedback' ); ?>
            </label>

            <div id="wpvfh-emoji-wrapper" style="margin-left: 24px; margin-bottom: 20px; <?php echo $mode !== 'emoji' ? 'display: none;' : ''; ?>">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" name="wpvfh_icon_emoji" id="wpvfh_icon_emoji"
                           value="<?php echo esc_attr( $emoji ); ?>"
                           style="width: 60px; text-align: center; font-size: 24px;"
                           maxlength="4"
                           placeholder="💬">
                    <span class="description"><?php esc_html_e( 'Entrez un emoji', 'blazing-feedback' ); ?></span>
                </div>
                <div style="margin-top: 10px;">
                    <span class="description"><?php esc_html_e( 'Suggestions :', 'blazing-feedback' ); ?></span>
                    <div style="display: flex; gap: 8px; margin-top: 5px; flex-wrap: wrap;">
                        <?php
                        $suggestions = array( '💬', '💭', '✨', '📝', '🔔', '💡', '❓', '🎯', '📌', '🗣️', '👋', '🚀' );
                        foreach ( $suggestions as $suggestion ) :
                        ?>
                        <button type="button" class="button wpvfh-emoji-suggestion" data-emoji="<?php echo esc_attr( $suggestion ); ?>" style="font-size: 18px; padding: 2px 8px;">
                            <?php echo esc_html( $suggestion ); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                <input type="radio" name="wpvfh_icon_mode" value="image" <?php checked( $mode, 'image' ); ?>>
                <?php esc_html_e( 'Image personnalisée', 'blazing-feedback' ); ?>
            </label>

            <div id="wpvfh-image-wrapper" style="margin-left: 24px; <?php echo $mode !== 'image' ? 'display: none;' : ''; ?>">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="wpvfh_icon_image_url" id="wpvfh_icon_image_url"
                           value="<?php echo esc_attr( $image_url ); ?>"
                           class="regular-text"
                           placeholder="<?php esc_attr_e( 'URL de l\'image ou sélectionner depuis la bibliothèque', 'blazing-feedback' ); ?>">
                    <button type="button" class="button" id="wpvfh-select-icon-btn">
                        <?php esc_html_e( 'Bibliothèque', 'blazing-feedback' ); ?>
                    </button>
                </div>
                <?php if ( $image_url ) : ?>
                <div style="margin-top: 10px;">
                    <img src="<?php echo esc_url( $image_url ); ?>" alt="Preview" style="max-height: 40px; background: #f0f0f0; padding: 5px; border-radius: 4px;">
                </div>
                <?php endif; ?>
                <p class="description" style="margin-top: 8px;">
                    <?php esc_html_e( 'Recommandé : image carrée, 64x64px minimum, fond transparent (PNG ou SVG).', 'blazing-feedback' ); ?>
                </p>
            </div>
        </fieldset>

        <script>
        jQuery(document).ready(function($) {
            // Toggle icon input sections
            $('input[name="wpvfh_icon_mode"]').on('change', function() {
                const mode = $(this).val();
                if (mode === 'emoji') {
                    $('#wpvfh-emoji-wrapper').slideDown();
                    $('#wpvfh-image-wrapper').slideUp();
                } else {
                    $('#wpvfh-emoji-wrapper').slideUp();
                    $('#wpvfh-image-wrapper').slideDown();
                }
            });

            // Emoji suggestions
            $('.wpvfh-emoji-suggestion').on('click', function() {
                const emoji = $(this).data('emoji');
                $('#wpvfh_icon_emoji').val(emoji);
            });

            // Media library for icon
            $('#wpvfh-select-icon-btn').on('click', function(e) {
                e.preventDefault();
                var frame = wp.media({
                    title: '<?php echo esc_js( __( 'Sélectionner une icône', 'blazing-feedback' ) ); ?>',
                    button: { text: '<?php echo esc_js( __( 'Utiliser cette image', 'blazing-feedback' ) ); ?>' },
                    multiple: false,
                    library: { type: 'image' }
                });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#wpvfh_icon_image_url').val(attachment.url);
                });
                frame.open();
            });
        });
        </script>
        <?php
    }

    /**
     * Champ Mode du logo
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_logo_mode_field() {
        $mode = get_option( 'wpvfh_logo_mode', 'none' );
        $custom_url = get_option( 'wpvfh_logo_custom_url', '' );
        $light_logo = WPVFH_PLUGIN_URL . 'assets/logo/light-mode-feedback.png';
        $dark_logo = WPVFH_PLUGIN_URL . 'assets/logo/dark-mode-feedback.png';
        ?>
        <fieldset>
            <label style="display: block; margin-bottom: 10px;">
                <input type="radio" name="wpvfh_logo_mode" value="none" <?php checked( $mode, 'none' ); ?>>
                <?php esc_html_e( 'Aucun (affiche le titre "Feedbacks")', 'blazing-feedback' ); ?>
            </label>
            <label style="display: block; margin-bottom: 10px;">
                <input type="radio" name="wpvfh_logo_mode" value="light" <?php checked( $mode, 'light' ); ?>>
                <?php esc_html_e( 'Mode clair (light-mode-feedback.png)', 'blazing-feedback' ); ?>
            </label>
            <label style="display: block; margin-bottom: 10px;">
                <input type="radio" name="wpvfh_logo_mode" value="dark" <?php checked( $mode, 'dark' ); ?>>
                <?php esc_html_e( 'Mode sombre (dark-mode-feedback.png)', 'blazing-feedback' ); ?>
            </label>
            <label style="display: block; margin-bottom: 10px;">
                <input type="radio" name="wpvfh_logo_mode" value="custom" <?php checked( $mode, 'custom' ); ?>>
                <?php esc_html_e( 'Personnalisé (URL ou bibliothèque)', 'blazing-feedback' ); ?>
            </label>
        </fieldset>
        <p class="description" style="margin-top: 10px;">
            <?php
            printf(
                esc_html__( 'Pour les modes clair/sombre, placez vos logos dans : %s', 'blazing-feedback' ),
                '<code>assets/logo/</code>'
            );
            ?>
        </p>

        <div id="wpvfh-custom-logo-wrapper" style="margin-top: 15px; <?php echo $mode !== 'custom' ? 'display: none;' : ''; ?>">
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" name="wpvfh_logo_custom_url" id="wpvfh_logo_custom_url"
                       value="<?php echo esc_attr( $custom_url ); ?>"
                       class="regular-text"
                       placeholder="<?php esc_attr_e( 'URL du logo ou sélectionner depuis la bibliothèque', 'blazing-feedback' ); ?>">
                <button type="button" class="button" id="wpvfh-select-logo-btn">
                    <?php esc_html_e( 'Bibliothèque', 'blazing-feedback' ); ?>
                </button>
            </div>
            <?php if ( $custom_url ) : ?>
            <div style="margin-top: 10px;">
                <img src="<?php echo esc_url( $custom_url ); ?>" alt="Preview" style="max-height: 50px; background: #f0f0f0; padding: 5px; border-radius: 4px;">
            </div>
            <?php endif; ?>
            <p class="description">
                <?php esc_html_e( 'Entrez une URL ou sélectionnez une image depuis la bibliothèque de médias.', 'blazing-feedback' ); ?>
            </p>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Toggle custom logo input
            $('input[name="wpvfh_logo_mode"]').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#wpvfh-custom-logo-wrapper').slideDown();
                } else {
                    $('#wpvfh-custom-logo-wrapper').slideUp();
                }
            });

            // Media library
            $('#wpvfh-select-logo-btn').on('click', function(e) {
                e.preventDefault();
                var frame = wp.media({
                    title: '<?php echo esc_js( __( 'Sélectionner un logo', 'blazing-feedback' ) ); ?>',
                    button: { text: '<?php echo esc_js( __( 'Utiliser ce logo', 'blazing-feedback' ) ); ?>' },
                    multiple: false,
                    library: { type: 'image' }
                });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#wpvfh_logo_custom_url').val(attachment.url);
                });
                frame.open();
            });
        });
        </script>
        <?php
    }

    /**
     * Champ Screenshot
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_screenshot_field() {
        $value = get_option( 'wpvfh_screenshot_enabled', true );
        ?>
        <label>
            <input type="checkbox" name="wpvfh_screenshot_enabled" value="1" <?php checked( $value, true ); ?>>
            <?php esc_html_e( 'Activer la capture d\'écran automatique', 'blazing-feedback' ); ?>
        </label>
        <p class="description">
            <?php esc_html_e( 'Utilise html2canvas pour capturer la page lors de la création d\'un feedback.', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Champ Feedback anonyme
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_guest_field() {
        $value = get_option( 'wpvfh_guest_feedback', false );
        ?>
        <label>
            <input type="checkbox" name="wpvfh_guest_feedback" value="1" <?php checked( $value, true ); ?>>
            <?php esc_html_e( 'Autoriser les feedbacks des visiteurs non connectés', 'blazing-feedback' ); ?>
        </label>
        <p class="description">
            <?php esc_html_e( 'Attention : cela peut générer du spam. Utilisez avec précaution.', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Champ Position du bouton
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_position_field() {
        $value = get_option( 'wpvfh_button_position', 'bottom-right' );
        $positions = array(
            'top-left'      => __( 'Haut gauche', 'blazing-feedback' ),
            'top-center'    => __( 'Haut centre', 'blazing-feedback' ),
            'top-right'     => __( 'Haut droite', 'blazing-feedback' ),
            'middle-left'   => __( 'Milieu gauche', 'blazing-feedback' ),
            'middle-right'  => __( 'Milieu droite', 'blazing-feedback' ),
            'bottom-left'   => __( 'Bas gauche', 'blazing-feedback' ),
            'bottom-center' => __( 'Bas centre', 'blazing-feedback' ),
            'bottom-right'  => __( 'Bas droite', 'blazing-feedback' ),
        );
        ?>
        <input type="hidden" name="wpvfh_button_position" id="wpvfh_button_position" value="<?php echo esc_attr( $value ); ?>">

        <div class="wpvfh-position-selector">
            <div class="wpvfh-position-grid">
                <!-- Ligne du haut -->
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'top-left' ? 'active' : ''; ?>" data-position="top-left" title="<?php echo esc_attr( $positions['top-left'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'top-center' ? 'active' : ''; ?>" data-position="top-center" title="<?php echo esc_attr( $positions['top-center'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'top-right' ? 'active' : ''; ?>" data-position="top-right" title="<?php echo esc_attr( $positions['top-right'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>

                <!-- Ligne du milieu -->
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'middle-left' ? 'active' : ''; ?>" data-position="middle-left" title="<?php echo esc_attr( $positions['middle-left'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>
                <div class="wpvfh-position-preview">
                    <span>📄</span>
                </div>
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'middle-right' ? 'active' : ''; ?>" data-position="middle-right" title="<?php echo esc_attr( $positions['middle-right'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>

                <!-- Ligne du bas -->
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'bottom-left' ? 'active' : ''; ?>" data-position="bottom-left" title="<?php echo esc_attr( $positions['bottom-left'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'bottom-center' ? 'active' : ''; ?>" data-position="bottom-center" title="<?php echo esc_attr( $positions['bottom-center'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>
                <button type="button" class="wpvfh-position-btn <?php echo $value === 'bottom-right' ? 'active' : ''; ?>" data-position="bottom-right" title="<?php echo esc_attr( $positions['bottom-right'] ); ?>">
                    <span class="wpvfh-position-dot"></span>
                </button>
            </div>
            <p class="description" style="margin-top: 10px;">
                <?php esc_html_e( 'Cliquez pour choisir la position du bouton.', 'blazing-feedback' ); ?>
            </p>
        </div>

        <style>
            .wpvfh-position-selector {
                max-width: 200px;
            }
            .wpvfh-position-grid {
                display: grid;
                grid-template-columns: 40px 1fr 40px;
                grid-template-rows: 40px 60px 40px;
                gap: 4px;
                background: #f0f0f1;
                border: 2px solid #c3c4c7;
                border-radius: 8px;
                padding: 4px;
            }
            .wpvfh-position-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fff;
                border: 2px solid #dcdcde;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s ease;
                padding: 0;
            }
            .wpvfh-position-btn:hover {
                border-color: #2271b1;
                background: #f0f7fc;
            }
            .wpvfh-position-btn.active {
                border-color: #2271b1;
                background: #2271b1;
            }
            .wpvfh-position-btn.active .wpvfh-position-dot {
                background: #fff;
            }
            .wpvfh-position-dot {
                width: 12px;
                height: 12px;
                background: #c3c4c7;
                border-radius: 50%;
                transition: all 0.2s ease;
            }
            .wpvfh-position-btn:hover .wpvfh-position-dot {
                background: #2271b1;
            }
            .wpvfh-position-preview {
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fff;
                border: 1px dashed #c3c4c7;
                border-radius: 4px;
                font-size: 24px;
                color: #787c82;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const buttons = document.querySelectorAll('.wpvfh-position-btn');
                const input = document.getElementById('wpvfh_button_position');

                buttons.forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        buttons.forEach(function(b) { b.classList.remove('active'); });
                        this.classList.add('active');
                        input.value = this.dataset.position;
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * Champ Position du volet
     *
     * @since 1.7.0
     * @return void
     */
    public static function render_panel_position_field() {
        $value = get_option( 'wpvfh_panel_position', 'right' );
        ?>
        <fieldset>
            <label style="display: inline-flex; align-items: center; gap: 5px; margin-right: 20px;">
                <input type="radio" name="wpvfh_panel_position" value="left" <?php checked( $value, 'left' ); ?>>
                <span style="display: inline-flex; align-items: center; gap: 5px;">
                    <svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="opacity: 0.7;">
                        <rect x="0.5" y="0.5" width="19" height="15" rx="1.5" stroke="currentColor"/>
                        <rect x="1" y="1" width="6" height="14" fill="currentColor" opacity="0.3"/>
                    </svg>
                    <?php esc_html_e( 'Gauche', 'blazing-feedback' ); ?>
                </span>
            </label>
            <label style="display: inline-flex; align-items: center; gap: 5px;">
                <input type="radio" name="wpvfh_panel_position" value="right" <?php checked( $value, 'right' ); ?>>
                <span style="display: inline-flex; align-items: center; gap: 5px;">
                    <svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="opacity: 0.7;">
                        <rect x="0.5" y="0.5" width="19" height="15" rx="1.5" stroke="currentColor"/>
                        <rect x="13" y="1" width="6" height="14" fill="currentColor" opacity="0.3"/>
                    </svg>
                    <?php esc_html_e( 'Droite', 'blazing-feedback' ); ?>
                </span>
            </label>
        </fieldset>
        <p class="description">
            <?php esc_html_e( 'Position du volet latéral de feedback.', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Champ Couleur du bouton
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_color_field() {
        $value = get_option( 'wpvfh_button_color', '#e74c3c' );
        ?>
        <input type="color" name="wpvfh_button_color" value="<?php echo esc_attr( $value ); ?>">
        <p class="description">
            <?php esc_html_e( 'Couleur du bouton de feedback.', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Champ Pages actives
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_pages_field() {
        $value = get_option( 'wpvfh_enabled_pages', '*' );
        ?>
        <textarea name="wpvfh_enabled_pages" rows="4" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
        <p class="description">
            <?php esc_html_e( 'Entrez * pour toutes les pages, ou listez les URLs (une par ligne). Ex: /contact, /produits/*', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Champ Notifications email
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_email_notifications_field() {
        $value = get_option( 'wpvfh_email_notifications', true );
        ?>
        <label>
            <input type="checkbox" name="wpvfh_email_notifications" value="1" <?php checked( $value, true ); ?>>
            <?php esc_html_e( 'Envoyer un email lors d\'un nouveau feedback', 'blazing-feedback' ); ?>
        </label>
        <?php
    }

    /**
     * Champ Email de notification
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_notification_email_field() {
        $value = get_option( 'wpvfh_notification_email', get_option( 'admin_email' ) );
        ?>
        <input type="email" name="wpvfh_notification_email" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
        <p class="description">
            <?php esc_html_e( 'Adresse email pour recevoir les notifications.', 'blazing-feedback' ); ?>
        </p>
        <?php
    }

    /**
     * Rendu de la section couleurs
     *
     * @since 1.8.0
     * @return void
     */
    public static function render_colors_section() {
        echo '<p>' . esc_html__( 'Personnalisez les couleurs du widget de feedback.', 'blazing-feedback' ) . '</p>';
    }

    /**
     * Champ couleur du thème générique
     *
     * @since 1.8.0
     * @param array $args Arguments du champ
     * @return void
     */
    public static function render_theme_color_field( $args ) {
        $option_name = $args['option_name'];
        $default = $args['default'];
        $value = get_option( $option_name, $default );
        ?>
        <div style="display: flex; align-items: center; gap: 10px;">
            <input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
            <input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 80px; font-family: monospace;" maxlength="7">
            <button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $default ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>">
                <span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px;"></span>
            </button>
        </div>
        <?php
    }

    /**
     * Obtenir les couleurs du thème personnalisées
     *
     * @since 1.8.0
     * @return array
     */
    public static function get_theme_colors() {
        return array(
            'primary'       => get_option( 'wpvfh_color_primary', '#e74c3c' ),
            'primary_hover' => get_option( 'wpvfh_color_primary_hover', '#c0392b' ),
            'secondary'     => get_option( 'wpvfh_color_secondary', '#3498db' ),
            'success'       => get_option( 'wpvfh_color_success', '#27ae60' ),
            'warning'       => get_option( 'wpvfh_color_warning', '#f39c12' ),
            'danger'        => get_option( 'wpvfh_color_danger', '#e74c3c' ),
            'text'          => get_option( 'wpvfh_color_text', '#333333' ),
            'text_light'    => get_option( 'wpvfh_color_text_light', '#666666' ),
            'bg'            => get_option( 'wpvfh_color_bg', '#ffffff' ),
            'bg_light'      => get_option( 'wpvfh_color_bg_light', '#f5f5f5' ),
            'border'        => get_option( 'wpvfh_color_border', '#dddddd' ),
        );
    }

    /**
     * Générer le CSS inline pour les couleurs personnalisées
     *
     * @since 1.8.0
     * @return string
     */
    public static function get_custom_colors_css() {
        $colors = self::get_theme_colors();

        // Vérifier si des couleurs ont été personnalisées
        $defaults = array(
            'primary'       => '#e74c3c',
            'primary_hover' => '#c0392b',
            'secondary'     => '#3498db',
            'success'       => '#27ae60',
            'warning'       => '#f39c12',
            'danger'        => '#e74c3c',
            'text'          => '#333333',
            'text_light'    => '#666666',
            'bg'            => '#ffffff',
            'bg_light'      => '#f5f5f5',
            'border'        => '#dddddd',
        );

        $has_custom = false;
        foreach ( $colors as $key => $value ) {
            if ( strtolower( $value ) !== strtolower( $defaults[ $key ] ) ) {
                $has_custom = true;
                break;
            }
        }

        if ( ! $has_custom ) {
            return '';
        }

        $css = ':root {';
        $css .= '--wpvfh-primary: ' . esc_attr( $colors['primary'] ) . ';';
        $css .= '--wpvfh-primary-hover: ' . esc_attr( $colors['primary_hover'] ) . ';';
        $css .= '--wpvfh-secondary: ' . esc_attr( $colors['secondary'] ) . ';';
        $css .= '--wpvfh-success: ' . esc_attr( $colors['success'] ) . ';';
        $css .= '--wpvfh-warning: ' . esc_attr( $colors['warning'] ) . ';';
        $css .= '--wpvfh-danger: ' . esc_attr( $colors['danger'] ) . ';';
        $css .= '--wpvfh-text: ' . esc_attr( $colors['text'] ) . ';';
        $css .= '--wpvfh-text-light: ' . esc_attr( $colors['text_light'] ) . ';';
        $css .= '--wpvfh-bg: ' . esc_attr( $colors['bg'] ) . ';';
        $css .= '--wpvfh-bg-light: ' . esc_attr( $colors['bg_light'] ) . ';';
        $css .= '--wpvfh-border: ' . esc_attr( $colors['border'] ) . ';';
        $css .= '}';

        return $css;
    }

    /**
     * Obtenir les statistiques du dashboard
     *
     * @since 1.0.0
     * @return array
     */
    private static function get_dashboard_stats() {
        global $wpdb;

        $stats = array(
            'total'       => 0,
            'new'         => 0,
            'in_progress' => 0,
            'resolved'    => 0,
            'rejected'    => 0,
        );

        $stats['total'] = wp_count_posts( 'visual_feedback' )->publish;

        $statuses = array( 'new', 'in_progress', 'resolved', 'rejected' );
        foreach ( $statuses as $status ) {
            $count = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = '_wpvfh_status'
                AND pm.meta_value = %s
                AND p.post_type = 'visual_feedback'
                AND p.post_status = 'publish'",
                $status
            ) );
            $stats[ $status ] = (int) $count;
        }

        return $stats;
    }

    /**
     * Obtenir les feedbacks récents
     *
     * @since 1.0.0
     * @param int $limit Nombre de feedbacks
     * @return array
     */
    private static function get_recent_feedbacks( $limit = 10 ) {
        return get_posts( array(
            'post_type'      => 'visual_feedback',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );
    }

    /**
     * Obtenir les pages les plus commentées
     *
     * @since 1.0.0
     * @param int $limit Nombre de pages
     * @return array
     */
    private static function get_top_pages( $limit = 5 ) {
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT pm.meta_value as url, COUNT(*) as count
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_wpvfh_url'
            AND p.post_type = 'visual_feedback'
            AND p.post_status = 'publish'
            GROUP BY pm.meta_value
            ORDER BY count DESC
            LIMIT %d",
            $limit
        ) );

        foreach ( $results as &$result ) {
            $result->path = wp_parse_url( $result->url, PHP_URL_PATH ) ?: '/';
        }

        return $results;
    }

    /**
     * Mise à jour rapide du statut (AJAX)
     *
     * @since 1.0.0
     * @return void
     */
    public static function ajax_quick_status_update() {
        check_ajax_referer( 'wpvfh_nonce', 'nonce' );

        if ( ! current_user_can( 'moderate_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $feedback_id = isset( $_POST['feedback_id'] ) ? absint( $_POST['feedback_id'] ) : 0;
        $status = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '';

        if ( ! $feedback_id || ! $status ) {
            wp_send_json_error( __( 'Données invalides.', 'blazing-feedback' ) );
        }

        update_post_meta( $feedback_id, '_wpvfh_status', $status );
        wp_set_object_terms( $feedback_id, $status, 'feedback_status' );

        $status_data = WPVFH_Options_Manager::get_status_by_id( $status );
        wp_send_json_success( array(
            'status' => $status,
            'label'  => $status_data ? $status_data['label'] : $status,
        ) );
    }

    /**
     * Masquer une notice (AJAX)
     *
     * @since 1.0.0
     * @return void
     */
    public static function ajax_dismiss_notice() {
        check_ajax_referer( 'wpvfh_nonce', 'nonce' );

        $notice_id = isset( $_POST['notice_id'] ) ? sanitize_key( $_POST['notice_id'] ) : '';

        if ( $notice_id ) {
            update_user_meta( get_current_user_id(), 'wpvfh_dismissed_' . $notice_id, true );
        }

        wp_send_json_success();
    }

    /**
     * Afficher les notices admin
     *
     * @since 1.0.0
     * @return void
     */
    public static function show_admin_notices() {
        // Notice de bienvenue (première activation) - ne s'affiche qu'une seule fois
        $welcome_notice = get_option( 'wpvfh_welcome_notice_dismissed' );
        if ( false === $welcome_notice && current_user_can( 'manage_feedback' ) ) {
            ?>
            <div class="notice notice-info is-dismissible wpvfh-welcome-notice" data-notice="wpvfh-welcome">
                <p>
                    <strong><?php esc_html_e( 'Blazing Feedback est activé !', 'blazing-feedback' ); ?></strong>
                    <?php esc_html_e( 'Le widget de feedback est maintenant disponible sur votre site.', 'blazing-feedback' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpvfh-settings' ) ); ?>">
                        <?php esc_html_e( 'Configurer', 'blazing-feedback' ); ?>
                    </a>
                </p>
            </div>
            <script>
            jQuery(document).ready(function($) {
                $('.wpvfh-welcome-notice').on('click', '.notice-dismiss', function() {
                    $.post(ajaxurl, {
                        action: 'wpvfh_dismiss_notice',
                        notice_id: 'welcome',
                        nonce: '<?php echo esc_js( wp_create_nonce( 'wpvfh_nonce' ) ); ?>'
                    });
                });
            });
            </script>
            <?php
            // Marquer comme vu dès l'affichage
            update_option( 'wpvfh_welcome_notice_dismissed', '1' );
        }
    }

    /**
     * Ajouter des liens sur la page plugins
     *
     * @since 1.0.0
     * @param array $links Liens existants
     * @return array
     */
    public static function add_plugin_links( $links ) {
        $plugin_links = array(
            '<a href="' . admin_url( 'admin.php?page=wpvfh-settings' ) . '">' . __( 'Paramètres', 'blazing-feedback' ) . '</a>',
            '<a href="' . admin_url( 'admin.php?page=wpvfh-dashboard' ) . '">' . __( 'Dashboard', 'blazing-feedback' ) . '</a>',
        );

        return array_merge( $plugin_links, $links );
    }
}

// Initialiser l'interface admin
WPVFH_Admin_UI::init();

/**
 * Envoyer une notification email pour un nouveau feedback
 *
 * @since 1.0.0
 * @param int   $feedback_id ID du feedback
 * @param array $data        Données du feedback
 * @return void
 */
function wpvfh_send_new_feedback_notification( $feedback_id, $data ) {
    // Vérifier si les notifications sont activées
    if ( ! get_option( 'wpvfh_email_notifications', true ) ) {
        return;
    }

    $to = get_option( 'wpvfh_notification_email', get_option( 'admin_email' ) );
    if ( ! is_email( $to ) ) {
        return;
    }

    $post = get_post( $feedback_id );
    $author = get_userdata( $post->post_author );
    $url = get_post_meta( $feedback_id, '_wpvfh_url', true );

    $subject = sprintf(
        /* translators: %s: site name */
        __( '[%s] Nouveau feedback reçu', 'blazing-feedback' ),
        get_bloginfo( 'name' )
    );

    $message = sprintf(
        /* translators: %1$s: author name, %2$s: page URL, %3$s: comment, %4$s: admin link */
        __( "Un nouveau feedback a été envoyé.\n\nAuteur : %1\$s\nPage : %2\$s\n\nCommentaire :\n%3\$s\n\nVoir le feedback : %4\$s", 'blazing-feedback' ),
        $author ? $author->display_name : __( 'Anonyme', 'blazing-feedback' ),
        $url,
        $post->post_content,
        admin_url( 'post.php?post=' . $feedback_id . '&action=edit' )
    );

    /**
     * Filtre le sujet de l'email de notification
     *
     * @since 1.0.0
     * @param string $subject     Sujet
     * @param int    $feedback_id ID du feedback
     */
    $subject = apply_filters( 'wpvfh_notification_subject', $subject, $feedback_id );

    /**
     * Filtre le contenu de l'email de notification
     *
     * @since 1.0.0
     * @param string $message     Message
     * @param int    $feedback_id ID du feedback
     */
    $message = apply_filters( 'wpvfh_notification_message', $message, $feedback_id );

    wp_mail( $to, $subject, $message );
}
add_action( 'wpvfh_feedback_created', 'wpvfh_send_new_feedback_notification', 10, 2 );
