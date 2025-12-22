<?php
/**
 * Template: Onglet Exécution
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 *
 * @var BZMI_Foundation $foundation         La fondation
 * @var array           $execution_sections Sections d'exécution
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section_icons = array(
	'scope'        => 'visibility',
	'deliverables' => 'media-document',
	'planning'     => 'calendar-alt',
	'budget'       => 'chart-area',
	'constraints'  => 'warning',
	'legal'        => 'shield',
);
?>
<div class="bzmi-tab-content bzmi-tab-execution">
	<div class="bzmi-execution-grid">
		<?php foreach ( $execution_sections as $section_key => $section ) :
			$section_data = $section['data'];
			$content = $section_data ? $section_data->get_content() : array();
			$status = $section_data ? $section_data->status : 'empty';
			$score = $section_data ? $section_data->get_completion_score() : 0;

			$status_class = 'empty' === $status ? 'empty' : ( 'validated' === $status ? 'validated' : 'draft' );
			$icon = $section_icons[ $section_key ] ?? 'admin-generic';
		?>
			<div class="bzmi-execution-card" data-section="<?php echo esc_attr( $section_key ); ?>">
				<div class="bzmi-execution-card__header">
					<div class="bzmi-execution-card__icon">
						<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
					</div>
					<div class="bzmi-execution-card__title-wrap">
						<h3 class="bzmi-execution-card__title"><?php echo esc_html( $section['label'] ); ?></h3>
						<span class="bzmi-status-badge bzmi-status-badge--<?php echo esc_attr( $status_class ); ?>">
							<?php
							if ( 'empty' === $status ) {
								esc_html_e( 'À compléter', 'blazing-feedback' );
							} elseif ( 'validated' === $status ) {
								esc_html_e( 'Validé', 'blazing-feedback' );
							} else {
								esc_html_e( 'Brouillon', 'blazing-feedback' );
							}
							?>
						</span>
					</div>
					<div class="bzmi-execution-card__progress">
						<span><?php echo esc_html( $score ); ?>%</span>
					</div>
				</div>

				<div class="bzmi-execution-card__body">
					<?php
					// Aperçu du contenu selon la section
					switch ( $section_key ) {
						case 'scope':
							if ( ! empty( $content['description'] ) ) {
								echo '<p class="bzmi-preview-text">' . esc_html( wp_trim_words( wp_strip_all_tags( $content['description'] ), 20 ) ) . '</p>';
							}
							$counts = array();
							if ( ! empty( $content['inclusions'] ) ) {
								$counts[] = count( $content['inclusions'] ) . ' ' . __( 'inclusions', 'blazing-feedback' );
							}
							if ( ! empty( $content['exclusions'] ) ) {
								$counts[] = count( $content['exclusions'] ) . ' ' . __( 'exclusions', 'blazing-feedback' );
							}
							if ( ! empty( $counts ) ) {
								echo '<div class="bzmi-preview-stats">' . esc_html( implode( ' | ', $counts ) ) . '</div>';
							}
							break;

						case 'deliverables':
							if ( ! empty( $content['items'] ) && is_array( $content['items'] ) ) {
								echo '<ul class="bzmi-preview-list">';
								foreach ( array_slice( $content['items'], 0, 3 ) as $item ) {
									$name = isset( $item['name'] ) ? $item['name'] : '';
									if ( $name ) {
										echo '<li>' . esc_html( $name ) . '</li>';
									}
								}
								if ( count( $content['items'] ) > 3 ) {
									echo '<li class="bzmi-preview-more">+' . ( count( $content['items'] ) - 3 ) . ' ' . esc_html__( 'autres', 'blazing-feedback' ) . '</li>';
								}
								echo '</ul>';
							}
							break;

						case 'planning':
							if ( ! empty( $content['start_date'] ) && ! empty( $content['end_date'] ) ) {
								echo '<div class="bzmi-preview-dates">';
								echo '<span class="bzmi-date"><span class="dashicons dashicons-calendar"></span> ' . esc_html( $content['start_date'] ) . '</span>';
								echo '<span class="bzmi-date-separator">→</span>';
								echo '<span class="bzmi-date">' . esc_html( $content['end_date'] ) . '</span>';
								echo '</div>';

								// Calculer la durée
								try {
									$start = new DateTime( $content['start_date'] );
									$end = new DateTime( $content['end_date'] );
									$diff = $start->diff( $end );
									echo '<div class="bzmi-preview-duration">' . esc_html( $diff->days ) . ' ' . esc_html__( 'jours', 'blazing-feedback' ) . '</div>';
								} catch ( Exception $e ) {
									// Ignorer
								}
							}
							if ( ! empty( $content['milestones'] ) ) {
								echo '<div class="bzmi-preview-stats">' . count( $content['milestones'] ) . ' ' . esc_html__( 'jalons', 'blazing-feedback' ) . '</div>';
							}
							break;

						case 'budget':
							if ( ! empty( $content['total'] ) ) {
								$currency = $content['currency'] ?? 'EUR';
								$symbol = array( 'EUR' => '€', 'USD' => '$', 'GBP' => '£', 'CHF' => 'CHF' );
								echo '<div class="bzmi-preview-budget">';
								echo '<span class="bzmi-budget-amount">' . esc_html( number_format( $content['total'], 0, ',', ' ' ) ) . ' ' . esc_html( $symbol[ $currency ] ?? $currency ) . '</span>';
								echo '</div>';
							}
							if ( ! empty( $content['breakdown'] ) ) {
								echo '<div class="bzmi-preview-stats">' . count( $content['breakdown'] ) . ' ' . esc_html__( 'postes', 'blazing-feedback' ) . '</div>';
							}
							break;

						case 'constraints':
							$constraint_counts = array();
							foreach ( array( 'technical', 'organizational', 'time', 'resource' ) as $type ) {
								if ( ! empty( $content[ $type ] ) ) {
									$constraint_counts[] = count( $content[ $type ] );
								}
							}
							if ( ! empty( $constraint_counts ) ) {
								echo '<div class="bzmi-preview-stats">' . array_sum( $constraint_counts ) . ' ' . esc_html__( 'contraintes identifiées', 'blazing-feedback' ) . '</div>';
							}
							break;

						case 'legal':
							$legal_items = array();
							if ( ! empty( $content['gdpr_compliance'] ) ) {
								$legal_items[] = __( 'RGPD', 'blazing-feedback' );
							}
							if ( ! empty( $content['data_handling'] ) ) {
								$legal_items[] = __( 'Données', 'blazing-feedback' );
							}
							if ( ! empty( $content['third_party_tools'] ) ) {
								$legal_items[] = count( $content['third_party_tools'] ) . ' ' . __( 'outils tiers', 'blazing-feedback' );
							}
							if ( ! empty( $legal_items ) ) {
								echo '<div class="bzmi-preview-tags">';
								foreach ( $legal_items as $item ) {
									echo '<span class="bzmi-tag">' . esc_html( $item ) . '</span>';
								}
								echo '</div>';
							}
							break;
					}

					if ( empty( $content ) || ( is_array( $content ) && count( array_filter( $content ) ) === 0 ) ) {
						echo '<p class="bzmi-preview-empty">' . esc_html__( 'Cliquez pour compléter cette section', 'blazing-feedback' ) . '</p>';
					}
					?>
				</div>

				<div class="bzmi-execution-card__footer">
					<button type="button" class="button bzmi-btn-edit-execution" data-section="<?php echo esc_attr( $section_key ); ?>">
						<span class="dashicons dashicons-edit"></span>
						<?php esc_html_e( 'Compléter', 'blazing-feedback' ); ?>
					</button>
					<?php if ( 'validated' !== $status && ! empty( $content ) && count( array_filter( $content ) ) > 0 ) : ?>
						<button type="button" class="button bzmi-btn-validate-execution" data-section="<?php echo esc_attr( $section_key ); ?>">
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Valider', 'blazing-feedback' ); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Actions IA -->
	<div class="bzmi-execution-ai-actions">
		<h3><?php esc_html_e( 'Assistance IA', 'blazing-feedback' ); ?></h3>
		<div class="bzmi-ai-action-buttons">
			<button type="button" class="button bzmi-btn-ai-execution" data-target="risk_assessment">
				<span class="dashicons dashicons-superhero"></span>
				<?php esc_html_e( 'Analyse des risques', 'blazing-feedback' ); ?>
			</button>
			<button type="button" class="button bzmi-btn-ai-execution" data-target="effort_estimation">
				<span class="dashicons dashicons-superhero"></span>
				<?php esc_html_e( 'Estimation d\'effort', 'blazing-feedback' ); ?>
			</button>
			<button type="button" class="button bzmi-btn-ai-execution" data-target="gdpr_checklist">
				<span class="dashicons dashicons-superhero"></span>
				<?php esc_html_e( 'Checklist RGPD', 'blazing-feedback' ); ?>
			</button>
		</div>
	</div>
</div>
