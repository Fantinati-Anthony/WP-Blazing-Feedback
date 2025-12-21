<?php
/**
 * Template de l'onglet Liste des feedbacks
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- Onglet: Liste des feedbacks -->
<div id="wpvfh-tab-list" class="wpvfh-tab-content active">
	<!-- Filtres par état -->
	<div class="wpvfh-legend" id="wpvfh-filters">
		<button type="button" class="wpvfh-filter-btn active" data-status="all">
			<?php esc_html_e( 'Tous', 'blazing-feedback' ); ?>
			<span class="wpvfh-filter-count" id="wpvfh-filter-all-count"><span>0</span></span>
		</button>
		<?php foreach ( WPVFH_Options_Manager::get_statuses() as $status ) : ?>
		<button type="button" class="wpvfh-filter-btn" data-status="<?php echo esc_attr( $status['id'] ); ?>">
			<?php echo esc_html( $status['label'] ); ?>
			<span class="wpvfh-filter-count" id="wpvfh-filter-<?php echo esc_attr( $status['id'] ); ?>-count"><span>0</span></span>
		</button>
		<?php endforeach; ?>
	</div>

	<div id="wpvfh-pins-list" class="wpvfh-pins-list">
		<!-- Les pins seront chargés dynamiquement -->
	</div>
	<div id="wpvfh-empty-state" class="wpvfh-empty-state">
		<div class="wpvfh-empty-icon" aria-hidden="true">📭</div>
		<p class="wpvfh-empty-text"><?php esc_html_e( 'Aucun feedback pour cette page, cliquez sur Nouveau en bas de cette barre latérale', 'blazing-feedback' ); ?></p>
	</div>
</div><!-- /wpvfh-tab-list -->
