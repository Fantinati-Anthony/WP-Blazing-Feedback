<?php
/**
 * Template de l'onglet Pages
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- Onglet: Pages -->
<div id="wpvfh-tab-pages" class="wpvfh-tab-content">
	<!-- Stats des pages -->
	<div class="wpvfh-legend" id="wpvfh-pages-stats">
		<div class="wpvfh-pages-stat-item">
			<span class="wpvfh-stat-icon">📄</span>
			<span class="wpvfh-stat-label"><?php esc_html_e( 'Pages', 'blazing-feedback' ); ?></span>
			<span class="wpvfh-stat-count" id="wpvfh-pages-total-count">0</span>
		</div>
		<div class="wpvfh-pages-stat-item">
			<span class="wpvfh-stat-icon">💬</span>
			<span class="wpvfh-stat-label"><?php esc_html_e( 'Feedbacks', 'blazing-feedback' ); ?></span>
			<span class="wpvfh-stat-count" id="wpvfh-pages-feedbacks-count">0</span>
		</div>
		<div class="wpvfh-pages-stat-item wpvfh-stat-validated">
			<span class="wpvfh-stat-icon">✅</span>
			<span class="wpvfh-stat-label"><?php esc_html_e( 'Validées', 'blazing-feedback' ); ?></span>
			<span class="wpvfh-stat-count" id="wpvfh-pages-validated-count">0</span>
		</div>
		<div class="wpvfh-pages-stat-item wpvfh-stat-pending">
			<span class="wpvfh-stat-icon">⏳</span>
			<span class="wpvfh-stat-label"><?php esc_html_e( 'En cours', 'blazing-feedback' ); ?></span>
			<span class="wpvfh-stat-count" id="wpvfh-pages-pending-count">0</span>
		</div>
	</div>
	<div id="wpvfh-pages-list" class="wpvfh-pages-list">
		<!-- Les pages seront chargées dynamiquement -->
	</div>
	<div id="wpvfh-pages-empty" class="wpvfh-empty-state" hidden>
		<div class="wpvfh-empty-icon" aria-hidden="true">📄</div>
		<p class="wpvfh-empty-text"><?php esc_html_e( 'Aucune page avec des feedbacks', 'blazing-feedback' ); ?></p>
	</div>
	<div id="wpvfh-pages-loading" class="wpvfh-loading-state">
		<span class="wpvfh-spinner"></span>
		<span><?php esc_html_e( 'Chargement des pages...', 'blazing-feedback' ); ?></span>
	</div>
</div><!-- /wpvfh-tab-pages -->
