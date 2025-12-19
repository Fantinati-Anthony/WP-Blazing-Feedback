<?php
/**
 * Trait pour le rendu de la page de paramètres
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait de gestion de la page de paramètres
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Settings_Page {

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
}
