<?php
/**
 * Trait pour les onglets de paramètres (petits onglets)
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait de gestion des onglets de paramètres (général, notifications, IA, danger)
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Settings_Tabs_Small {

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
                <tr>
                    <th scope="row"><?php esc_html_e( 'Après ajout d\'un feedback', 'blazing-feedback' ); ?></th>
                    <td>
                        <?php self::render_post_feedback_action_field(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Back-office', 'blazing-feedback' ); ?></th>
                    <td>
                        <?php self::render_enable_admin_field(); ?>
                    </td>
                </tr>
            </table>
        </div>
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
        $ai_enabled = WPVFH_Database::get_setting( 'wpvfh_ai_enabled', false );
        $api_key = WPVFH_Database::get_setting( 'wpvfh_ai_api_key', '' );
        $system_prompt = WPVFH_Database::get_setting( 'wpvfh_ai_system_prompt', '' );
        $analysis_prompt = WPVFH_Database::get_setting( 'wpvfh_ai_analysis_prompt', '' );
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
}
