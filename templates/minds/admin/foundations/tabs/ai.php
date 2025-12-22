<?php
/**
 * Template: Onglet IA & Insights
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 *
 * @var BZMI_Foundation $foundation La fondation
 * @var array           $ai_logs    Logs IA récents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ai_enabled = bzmi_ai()->is_enabled();
?>
<div class="bzmi-tab-content bzmi-tab-ai">
	<?php if ( ! $ai_enabled ) : ?>
		<div class="bzmi-notice bzmi-notice--warning">
			<span class="dashicons dashicons-warning"></span>
			<div>
				<strong><?php esc_html_e( 'IA non configurée', 'blazing-feedback' ); ?></strong>
				<p><?php esc_html_e( 'Configurez votre clé API dans les paramètres pour activer les fonctionnalités IA.', 'blazing-feedback' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-settings' ) ); ?>" class="button">
					<?php esc_html_e( 'Configurer', 'blazing-feedback' ); ?>
				</a>
			</div>
		</div>
	<?php endif; ?>

	<!-- Actions IA -->
	<div class="bzmi-ai-actions-panel">
		<h2>
			<span class="dashicons dashicons-superhero"></span>
			<?php esc_html_e( 'Actions IA disponibles', 'blazing-feedback' ); ?>
		</h2>

		<div class="bzmi-ai-actions-grid">
			<div class="bzmi-ai-action-card">
				<div class="bzmi-ai-action-card__icon bzmi-ai-action-card__icon--audit">
					<span class="dashicons dashicons-search"></span>
				</div>
				<div class="bzmi-ai-action-card__content">
					<h3><?php esc_html_e( 'Audit complet', 'blazing-feedback' ); ?></h3>
					<p><?php esc_html_e( 'Analyse la cohérence globale de votre fondation et identifie les points d\'amélioration.', 'blazing-feedback' ); ?></p>
				</div>
				<button type="button" class="button button-primary bzmi-btn-ai-audit" <?php disabled( ! $ai_enabled ); ?>>
					<?php esc_html_e( 'Lancer l\'audit', 'blazing-feedback' ); ?>
				</button>
			</div>

			<div class="bzmi-ai-action-card">
				<div class="bzmi-ai-action-card__icon bzmi-ai-action-card__icon--identity">
					<span class="dashicons dashicons-id"></span>
				</div>
				<div class="bzmi-ai-action-card__content">
					<h3><?php esc_html_e( 'Enrichir l\'identité', 'blazing-feedback' ); ?></h3>
					<p><?php esc_html_e( 'Génère des suggestions pour votre ADN de marque, vision et ton.', 'blazing-feedback' ); ?></p>
				</div>
				<button type="button" class="button bzmi-btn-ai-enrich" data-socle="identity" <?php disabled( ! $ai_enabled ); ?>>
					<?php esc_html_e( 'Générer', 'blazing-feedback' ); ?>
				</button>
			</div>

			<div class="bzmi-ai-action-card">
				<div class="bzmi-ai-action-card__icon bzmi-ai-action-card__icon--offer">
					<span class="dashicons dashicons-cart"></span>
				</div>
				<div class="bzmi-ai-action-card__content">
					<h3><?php esc_html_e( 'Analyse concurrentielle', 'blazing-feedback' ); ?></h3>
					<p><?php esc_html_e( 'Analyse vos concurrents et suggère un positionnement différenciant.', 'blazing-feedback' ); ?></p>
				</div>
				<button type="button" class="button bzmi-btn-ai-enrich" data-socle="offer" data-target="competitor_analysis" <?php disabled( ! $ai_enabled ); ?>>
					<?php esc_html_e( 'Analyser', 'blazing-feedback' ); ?>
				</button>
			</div>

			<div class="bzmi-ai-action-card">
				<div class="bzmi-ai-action-card__icon bzmi-ai-action-card__icon--experience">
					<span class="dashicons dashicons-admin-users"></span>
				</div>
				<div class="bzmi-ai-action-card__content">
					<h3><?php esc_html_e( 'Optimiser l\'expérience', 'blazing-feedback' ); ?></h3>
					<p><?php esc_html_e( 'Identifie les points de friction et opportunités dans vos parcours.', 'blazing-feedback' ); ?></p>
				</div>
				<button type="button" class="button bzmi-btn-ai-enrich" data-socle="experience" data-target="journey_optimization" <?php disabled( ! $ai_enabled ); ?>>
					<?php esc_html_e( 'Optimiser', 'blazing-feedback' ); ?>
				</button>
			</div>
		</div>
	</div>

	<!-- Résultats du dernier audit -->
	<div class="bzmi-ai-results-panel" id="bzmi-ai-results" style="display: none;">
		<div class="bzmi-ai-results-panel__header">
			<h2>
				<span class="dashicons dashicons-chart-bar"></span>
				<?php esc_html_e( 'Résultats de l\'analyse', 'blazing-feedback' ); ?>
			</h2>
			<button type="button" class="button bzmi-btn-close-results">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="bzmi-ai-results-panel__content">
			<!-- Contenu injecté par JS -->
		</div>
	</div>

	<!-- Historique -->
	<div class="bzmi-ai-history-panel">
		<h2>
			<span class="dashicons dashicons-backup"></span>
			<?php esc_html_e( 'Historique des enrichissements IA', 'blazing-feedback' ); ?>
		</h2>

		<?php if ( empty( $ai_logs ) ) : ?>
			<p class="bzmi-empty-text"><?php esc_html_e( 'Aucun enrichissement IA effectué pour cette fondation.', 'blazing-feedback' ); ?></p>
		<?php else : ?>
			<table class="bzmi-table bzmi-ai-logs-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'blazing-feedback' ); ?></th>
						<th><?php esc_html_e( 'Socle', 'blazing-feedback' ); ?></th>
						<th><?php esc_html_e( 'Action', 'blazing-feedback' ); ?></th>
						<th><?php esc_html_e( 'Confiance', 'blazing-feedback' ); ?></th>
						<th><?php esc_html_e( 'Appliqué', 'blazing-feedback' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'blazing-feedback' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $ai_logs as $log ) :
						$socle_labels = array(
							'identity'   => __( 'Identité', 'blazing-feedback' ),
							'offer'      => __( 'Offre', 'blazing-feedback' ),
							'experience' => __( 'Expérience', 'blazing-feedback' ),
							'execution'  => __( 'Exécution', 'blazing-feedback' ),
							'all'        => __( 'Global', 'blazing-feedback' ),
						);
						$action_labels = array(
							'enrich'  => __( 'Enrichissement', 'blazing-feedback' ),
							'suggest' => __( 'Suggestion', 'blazing-feedback' ),
							'audit'   => __( 'Audit', 'blazing-feedback' ),
						);
					?>
						<tr data-log-id="<?php echo esc_attr( $log['id'] ); ?>">
							<td>
								<span class="bzmi-log-date"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log['created_at'] ) ) ); ?></span>
							</td>
							<td>
								<span class="bzmi-badge bzmi-badge--<?php echo esc_attr( $log['socle'] ); ?>">
									<?php echo esc_html( $socle_labels[ $log['socle'] ] ?? $log['socle'] ); ?>
								</span>
							</td>
							<td>
								<?php echo esc_html( $action_labels[ $log['action'] ] ?? $log['action'] ); ?>
								<?php if ( $log['target_type'] ) : ?>
									<small>(<?php echo esc_html( $log['target_type'] ); ?>)</small>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $log['confidence_score'] ) : ?>
									<div class="bzmi-confidence-badge" style="--confidence: <?php echo esc_attr( $log['confidence_score'] * 100 ); ?>%">
										<?php echo esc_html( round( $log['confidence_score'] * 100 ) ); ?>%
									</div>
								<?php else : ?>
									-
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $log['applied'] ) : ?>
									<span class="bzmi-status-icon bzmi-status-icon--success">
										<span class="dashicons dashicons-yes-alt"></span>
									</span>
								<?php else : ?>
									<span class="bzmi-status-icon bzmi-status-icon--pending">
										<span class="dashicons dashicons-marker"></span>
									</span>
								<?php endif; ?>
							</td>
							<td>
								<button type="button" class="button button-small bzmi-btn-view-log" data-log-id="<?php echo esc_attr( $log['id'] ); ?>">
									<span class="dashicons dashicons-visibility"></span>
								</button>
								<?php if ( ! $log['applied'] && $log['output'] ) : ?>
									<button type="button" class="button button-small bzmi-btn-apply-log" data-log-id="<?php echo esc_attr( $log['id'] ); ?>">
										<span class="dashicons dashicons-yes"></span>
									</button>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>
