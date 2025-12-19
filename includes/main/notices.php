<?php
/**
 * Notices admin (version PHP/WordPress)
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notice pour version PHP insuffisante
 *
 * @since 1.0.0
 * @return void
 */
function wpvfh_php_version_notice() {
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
function wpvfh_wp_version_notice() {
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
