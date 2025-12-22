<?php
/**
 * Template: Onglet Offre & Marché
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 *
 * @var BZMI_Foundation $foundation  La fondation
 * @var array           $offers      Les offres
 * @var array           $competitors Les concurrents
 * @var array           $personas    Les personas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="bzmi-tab-content bzmi-tab-offer">
	<!-- Offres -->
	<div class="bzmi-offers-section">
		<div class="bzmi-section-header">
			<h2 class="bzmi-section-title">
				<span class="dashicons dashicons-products"></span>
				<?php esc_html_e( 'Offres / Produits / Services', 'blazing-feedback' ); ?>
			</h2>
			<button type="button" class="button button-primary bzmi-btn-add-offer">
				<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'Ajouter une offre', 'blazing-feedback' ); ?>
			</button>
		</div>

		<?php if ( empty( $offers ) ) : ?>
			<div class="bzmi-empty-inline">
				<span class="dashicons dashicons-products"></span>
				<p><?php esc_html_e( 'Aucune offre définie. Documentez vos produits et services.', 'blazing-feedback' ); ?></p>
				<button type="button" class="button bzmi-btn-add-offer">
					<?php esc_html_e( 'Créer une offre', 'blazing-feedback' ); ?>
				</button>
			</div>
		<?php else : ?>
			<div class="bzmi-offers-list">
				<?php foreach ( $offers as $offer ) :
					$type_label = BZMI_Foundation_Offer::TYPES[ $offer->type ] ?? $offer->type;
					$pricing_label = BZMI_Foundation_Offer::PRICING_MODELS[ $offer->pricing_model ] ?? $offer->pricing_model;
				?>
					<div class="bzmi-offer-item" data-offer-id="<?php echo esc_attr( $offer->id ); ?>">
						<div class="bzmi-offer-item__header">
							<div class="bzmi-offer-item__info">
								<h4 class="bzmi-offer-item__name"><?php echo esc_html( $offer->name ); ?></h4>
								<div class="bzmi-offer-item__meta">
									<span class="bzmi-badge bzmi-badge--<?php echo esc_attr( $offer->type ); ?>">
										<?php echo esc_html( $type_label ); ?>
									</span>
									<?php if ( $offer->pricing_model ) : ?>
										<span class="bzmi-meta-tag">
											<span class="dashicons dashicons-tag"></span>
											<?php echo esc_html( $pricing_label ); ?>
										</span>
									<?php endif; ?>
									<?php if ( $offer->price_range ) : ?>
										<span class="bzmi-meta-tag">
											<?php echo esc_html( $offer->price_range ); ?>
										</span>
									<?php endif; ?>
								</div>
							</div>
							<div class="bzmi-offer-item__actions">
								<button type="button" class="button button-small bzmi-btn-edit-offer" data-offer-id="<?php echo esc_attr( $offer->id ); ?>">
									<span class="dashicons dashicons-edit"></span>
								</button>
								<button type="button" class="button button-small bzmi-btn-delete-offer" data-offer-id="<?php echo esc_attr( $offer->id ); ?>">
									<span class="dashicons dashicons-trash"></span>
								</button>
							</div>
						</div>

						<?php if ( $offer->value_proposition ) : ?>
							<div class="bzmi-offer-item__proposition">
								<strong><?php esc_html_e( 'Proposition de valeur :', 'blazing-feedback' ); ?></strong>
								<?php echo esc_html( wp_trim_words( $offer->value_proposition, 30 ) ); ?>
							</div>
						<?php endif; ?>

						<?php
						$features = $offer->get_features();
						$benefits = $offer->get_benefits();
						?>
						<?php if ( ! empty( $features ) || ! empty( $benefits ) ) : ?>
							<div class="bzmi-offer-item__details">
								<?php if ( ! empty( $features ) ) : ?>
									<div class="bzmi-offer-detail">
										<span class="bzmi-offer-detail__label"><?php esc_html_e( 'Fonctionnalités', 'blazing-feedback' ); ?></span>
										<div class="bzmi-tags">
											<?php foreach ( array_slice( $features, 0, 4 ) as $feature ) : ?>
												<span class="bzmi-tag"><?php echo esc_html( $feature ); ?></span>
											<?php endforeach; ?>
											<?php if ( count( $features ) > 4 ) : ?>
												<span class="bzmi-tag bzmi-tag--more">+<?php echo count( $features ) - 4; ?></span>
											<?php endif; ?>
										</div>
									</div>
								<?php endif; ?>

								<?php if ( ! empty( $benefits ) ) : ?>
									<div class="bzmi-offer-detail">
										<span class="bzmi-offer-detail__label"><?php esc_html_e( 'Bénéfices', 'blazing-feedback' ); ?></span>
										<div class="bzmi-tags">
											<?php foreach ( array_slice( $benefits, 0, 4 ) as $benefit ) : ?>
												<span class="bzmi-tag bzmi-tag--green"><?php echo esc_html( $benefit ); ?></span>
											<?php endforeach; ?>
										</div>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<!-- Concurrents -->
	<div class="bzmi-competitors-section">
		<div class="bzmi-section-header">
			<h2 class="bzmi-section-title">
				<span class="dashicons dashicons-chart-line"></span>
				<?php esc_html_e( 'Analyse concurrentielle', 'blazing-feedback' ); ?>
			</h2>
			<div class="bzmi-section-actions">
				<button type="button" class="button bzmi-btn-ai-competitors">
					<span class="dashicons dashicons-superhero"></span>
					<?php esc_html_e( 'Analyse IA', 'blazing-feedback' ); ?>
				</button>
				<button type="button" class="button button-primary bzmi-btn-add-competitor">
					<span class="dashicons dashicons-plus-alt"></span>
					<?php esc_html_e( 'Ajouter', 'blazing-feedback' ); ?>
				</button>
			</div>
		</div>

		<?php if ( empty( $competitors ) ) : ?>
			<div class="bzmi-empty-inline">
				<span class="dashicons dashicons-chart-line"></span>
				<p><?php esc_html_e( 'Aucun concurrent identifié. Analysez votre environnement concurrentiel.', 'blazing-feedback' ); ?></p>
				<button type="button" class="button bzmi-btn-add-competitor">
					<?php esc_html_e( 'Ajouter un concurrent', 'blazing-feedback' ); ?>
				</button>
			</div>
		<?php else : ?>
			<div class="bzmi-competitors-grid">
				<?php foreach ( $competitors as $competitor ) :
					$type_label = BZMI_Foundation_Competitor::TYPES[ $competitor->type ] ?? $competitor->type;
					$position_label = BZMI_Foundation_Competitor::MARKET_POSITIONS[ $competitor->market_position ] ?? '';
					$threat_color = $competitor->get_threat_color();
					$threat_label = $competitor->get_threat_label();
					$strengths = $competitor->get_strengths();
					$weaknesses = $competitor->get_weaknesses();
				?>
					<div class="bzmi-competitor-card" data-competitor-id="<?php echo esc_attr( $competitor->id ); ?>">
						<div class="bzmi-competitor-card__header">
							<div class="bzmi-competitor-card__info">
								<h4 class="bzmi-competitor-card__name"><?php echo esc_html( $competitor->name ); ?></h4>
								<?php if ( $competitor->website ) : ?>
									<a href="<?php echo esc_url( $competitor->website ); ?>" target="_blank" class="bzmi-competitor-card__link">
										<span class="dashicons dashicons-external"></span>
									</a>
								<?php endif; ?>
							</div>
							<span class="bzmi-threat-badge" style="background-color: <?php echo esc_attr( $threat_color ); ?>">
								<?php echo esc_html( $threat_label ); ?>
							</span>
						</div>

						<div class="bzmi-competitor-card__meta">
							<span class="bzmi-badge bzmi-badge--outline"><?php echo esc_html( $type_label ); ?></span>
							<?php if ( $position_label ) : ?>
								<span class="bzmi-meta-tag"><?php echo esc_html( $position_label ); ?></span>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $strengths ) || ! empty( $weaknesses ) ) : ?>
							<div class="bzmi-competitor-card__swot">
								<?php if ( ! empty( $strengths ) ) : ?>
									<div class="bzmi-swot-section bzmi-swot-section--strengths">
										<span class="bzmi-swot-label">
											<span class="dashicons dashicons-yes-alt"></span>
											<?php esc_html_e( 'Forces', 'blazing-feedback' ); ?>
										</span>
										<ul>
											<?php foreach ( array_slice( $strengths, 0, 2 ) as $strength ) : ?>
												<li><?php echo esc_html( $strength ); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php endif; ?>

								<?php if ( ! empty( $weaknesses ) ) : ?>
									<div class="bzmi-swot-section bzmi-swot-section--weaknesses">
										<span class="bzmi-swot-label">
											<span class="dashicons dashicons-warning"></span>
											<?php esc_html_e( 'Faiblesses', 'blazing-feedback' ); ?>
										</span>
										<ul>
											<?php foreach ( array_slice( $weaknesses, 0, 2 ) as $weakness ) : ?>
												<li><?php echo esc_html( $weakness ); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<div class="bzmi-competitor-card__actions">
							<button type="button" class="button button-small bzmi-btn-edit-competitor" data-competitor-id="<?php echo esc_attr( $competitor->id ); ?>">
								<span class="dashicons dashicons-edit"></span>
								<?php esc_html_e( 'Éditer', 'blazing-feedback' ); ?>
							</button>
							<button type="button" class="button button-small bzmi-btn-delete-competitor" data-competitor-id="<?php echo esc_attr( $competitor->id ); ?>">
								<span class="dashicons dashicons-trash"></span>
							</button>
						</div>
					</div>
				<?php endforeach; ?>

				<!-- Carte pour ajouter -->
				<div class="bzmi-competitor-card bzmi-competitor-card--add">
					<button type="button" class="bzmi-btn-add-competitor">
						<span class="dashicons dashicons-plus-alt2"></span>
						<span><?php esc_html_e( 'Ajouter un concurrent', 'blazing-feedback' ); ?></span>
					</button>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
