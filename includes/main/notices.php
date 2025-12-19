<?php
/**
 * Notices admin (version PHP/WP)
 * 
 * Reference file for blazing-feedback.php lines 1787-1838
 * See main file: blazing-feedback.php
 * 
 * @package Blazing_Feedback
 */

// To view this section, read blazing-feedback.php with:
// offset=1787, limit=52

    public function php_version_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                printf(
                    /* translators: %s: version PHP minimale requise */
                    esc_html__( 'WP Visual Feedback Hub nécessite PHP %s ou supérieur. Veuillez mettre à jour votre version de PHP.', 'blazing-feedback' ),
                    WPVFH_MINIMUM_PHP_VERSION
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Notice pour version WordPress insuffisante
     *
     * @since 1.0.0
     * @return void
     */
    public function wp_version_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                printf(
                    /* translators: %s: version WordPress minimale requise */
                    esc_html__( 'WP Visual Feedback Hub nécessite WordPress %s ou supérieur. Veuillez mettre à jour WordPress.', 'blazing-feedback' ),
                    WPVFH_MINIMUM_WP_VERSION
                );
                ?>
            </p>
        </div>
        <?php
    }
}

/**
 * Initialiser le plugin
 *
 * @since 1.0.0
 * @return WP_Visual_Feedback_Hub
 */
function wpvfh() {
    return WP_Visual_Feedback_Hub::get_instance();
}

// Démarrer le plugin
wpvfh();
