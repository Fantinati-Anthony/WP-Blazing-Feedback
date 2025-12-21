<?php
/**
 * Template du header du panneau et onglets
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpvfh-panel-header">
	<?php
	$logo_mode = WPVFH_Database::get_setting( 'wpvfh_logo_mode', 'none' );
	$logo_url  = '';
	if ( $logo_mode === 'light' ) {
		$logo_url = WPVFH_PLUGIN_URL . 'assets/logo/light-mode-feedback.png';
	} elseif ( $logo_mode === 'dark' ) {
		$logo_url = WPVFH_PLUGIN_URL . 'assets/logo/dark-mode-feedback.png';
	} elseif ( $logo_mode === 'custom' ) {
		$logo_url = WPVFH_Database::get_setting( 'wpvfh_logo_custom_url', '' );
	}
	// Fallback to default light logo if no logo URL
	if ( empty( $logo_url ) ) {
		$logo_url = WPVFH_PLUGIN_URL . 'assets/logo/light-mode-feedback.png';
	}
	?>
	<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Blazing Feedback', 'blazing-feedback' ); ?>" class="wpvfh-panel-logo">
	<div class="wpvfh-header-actions">
		<button type="button" id="wpvfh-visibility-btn" class="wpvfh-header-btn wpvfh-visibility-btn" title="<?php esc_attr_e( 'Afficher/masquer les points', 'blazing-feedback' ); ?>" data-visible="true">
			<span class="wpvfh-icon-visible" aria-hidden="true">👁️</span>
			<span class="wpvfh-icon-hidden" aria-hidden="true" hidden>🙈</span>
		</button>
		<button type="button" class="wpvfh-search-btn" id="wpvfh-search-btn" aria-label="<?php esc_attr_e( 'Rechercher', 'blazing-feedback' ); ?>" title="<?php esc_attr_e( 'Rechercher un feedback', 'blazing-feedback' ); ?>">
			<span aria-hidden="true">🔍</span>
		</button>
		<button type="button" class="wpvfh-close-btn" aria-label="<?php esc_attr_e( 'Fermer', 'blazing-feedback' ); ?>">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
</div>

<!-- Onglets -->
<div class="wpvfh-tabs">
	<button type="button" class="wpvfh-tab wpvfh-tab-add" data-tab="new" id="wpvfh-add-btn" title="<?php esc_attr_e( 'Nouveau feedback', 'blazing-feedback' ); ?>">
		<span class="wpvfh-tab-icon wpvfh-icon-plus" aria-hidden="true">+</span>
	</button>
	<button type="button" class="wpvfh-tab active" data-tab="list">
		<span class="wpvfh-tab-icon" aria-hidden="true">📋</span>
		<?php esc_html_e( 'Liste', 'blazing-feedback' ); ?>
		<span class="wpvfh-tab-count" id="wpvfh-pins-count"></span>
	</button>
	<button type="button" class="wpvfh-tab" data-tab="pages">
		<span class="wpvfh-tab-icon" aria-hidden="true">📄</span>
		<?php esc_html_e( 'Pages', 'blazing-feedback' ); ?>
	</button>
	<button type="button" class="wpvfh-tab" data-tab="metadata">
		<span class="wpvfh-tab-icon" aria-hidden="true">🏷️</span>
		<?php esc_html_e( 'Métadatas', 'blazing-feedback' ); ?>
	</button>
	<button type="button" class="wpvfh-tab" data-tab="details" id="wpvfh-tab-details-btn" hidden>
		<span class="wpvfh-tab-icon" aria-hidden="true">👁️</span>
		<?php esc_html_e( 'Détails', 'blazing-feedback' ); ?>
	</button>
</div>
