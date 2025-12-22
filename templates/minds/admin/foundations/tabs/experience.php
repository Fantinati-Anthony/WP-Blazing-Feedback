<?php
/**
 * Template: Onglet Expérience & Canaux
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 *
 * @var BZMI_Foundation $foundation La fondation
 * @var array           $journeys   Les parcours
 * @var array           $channels   Les canaux
 * @var array           $personas   Les personas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="bzmi-tab-content bzmi-tab-experience">
	<!-- Parcours utilisateurs -->
	<div class="bzmi-journeys-section">
		<div class="bzmi-section-header">
			<h2 class="bzmi-section-title">
				<span class="dashicons dashicons-randomize"></span>
				<?php esc_html_e( 'Parcours utilisateurs', 'blazing-feedback' ); ?>
			</h2>
			<button type="button" class="button button-primary bzmi-btn-add-journey">
				<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'Nouveau parcours', 'blazing-feedback' ); ?>
			</button>
		</div>

		<?php if ( empty( $journeys ) ) : ?>
			<div class="bzmi-empty-inline">
				<span class="dashicons dashicons-randomize"></span>
				<p><?php esc_html_e( 'Aucun parcours défini. Cartographiez l\'expérience de vos utilisateurs.', 'blazing-feedback' ); ?></p>
				<div class="bzmi-empty-actions">
					<button type="button" class="button bzmi-btn-add-journey">
						<?php esc_html_e( 'Créer un parcours', 'blazing-feedback' ); ?>
					</button>
					<div class="bzmi-template-dropdown">
						<button type="button" class="button bzmi-btn-template-toggle">
							<span class="dashicons dashicons-layout"></span>
							<?php esc_html_e( 'Utiliser un template', 'blazing-feedback' ); ?>
						</button>
						<ul class="bzmi-template-list" style="display: none;">
							<li><button type="button" data-template="purchase"><?php esc_html_e( 'Parcours d\'achat', 'blazing-feedback' ); ?></button></li>
							<li><button type="button" data-template="onboarding"><?php esc_html_e( 'Parcours d\'onboarding', 'blazing-feedback' ); ?></button></li>
							<li><button type="button" data-template="support"><?php esc_html_e( 'Parcours de support', 'blazing-feedback' ); ?></button></li>
						</ul>
					</div>
				</div>
			</div>
		<?php else : ?>
			<div class="bzmi-journeys-list">
				<?php foreach ( $journeys as $journey ) :
					$persona = $journey->get_persona();
					$stages = $journey->get_stages();
					$emotions = $journey->get_emotions();
					$avg_emotion = $journey->get_average_emotion_score();
				?>
					<div class="bzmi-journey-card" data-journey-id="<?php echo esc_attr( $journey->id ); ?>">
						<div class="bzmi-journey-card__header">
							<div class="bzmi-journey-card__info">
								<h4 class="bzmi-journey-card__name"><?php echo esc_html( $journey->name ); ?></h4>
								<?php if ( $persona ) : ?>
									<span class="bzmi-journey-card__persona">
										<span class="dashicons dashicons-admin-users"></span>
										<?php echo esc_html( $persona->name ); ?>
									</span>
								<?php endif; ?>
							</div>
							<div class="bzmi-journey-card__actions">
								<button type="button" class="button button-small bzmi-btn-edit-journey" data-journey-id="<?php echo esc_attr( $journey->id ); ?>">
									<span class="dashicons dashicons-edit"></span>
								</button>
								<button type="button" class="button button-small bzmi-btn-delete-journey" data-journey-id="<?php echo esc_attr( $journey->id ); ?>">
									<span class="dashicons dashicons-trash"></span>
								</button>
							</div>
						</div>

						<?php if ( $journey->objective ) : ?>
							<p class="bzmi-journey-card__objective"><?php echo esc_html( $journey->objective ); ?></p>
						<?php endif; ?>

						<?php if ( ! empty( $stages ) ) : ?>
							<div class="bzmi-journey-stages">
								<?php foreach ( $stages as $index => $stage ) :
									$stage_name = isset( $stage['name'] ) ? $stage['name'] : ( isset( $stage['id'] ) ? $stage['id'] : '' );
									$stage_id = isset( $stage['id'] ) ? $stage['id'] : $index;
									$emotion = isset( $emotions[ $stage_id ] ) ? $emotions[ $stage_id ] : 'neutral';
									$emotion_data = BZMI_Foundation_Journey::EMOTIONS[ $emotion ] ?? BZMI_Foundation_Journey::EMOTIONS['neutral'];
								?>
									<div class="bzmi-stage-item">
										<div class="bzmi-stage-item__connector"></div>
										<div class="bzmi-stage-item__content">
											<span class="bzmi-stage-item__emoji"><?php echo esc_html( $emotion_data['emoji'] ); ?></span>
											<span class="bzmi-stage-item__name"><?php echo esc_html( $stage_name ); ?></span>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<?php if ( $avg_emotion > 0 ) : ?>
							<div class="bzmi-journey-card__emotion">
								<span class="bzmi-emotion-label"><?php esc_html_e( 'Score émotionnel moyen', 'blazing-feedback' ); ?></span>
								<div class="bzmi-emotion-bar">
									<div class="bzmi-emotion-bar__fill" style="width: <?php echo esc_attr( ( $avg_emotion / 5 ) * 100 ); ?>%"></div>
								</div>
								<span class="bzmi-emotion-value"><?php echo esc_html( $avg_emotion ); ?>/5</span>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<!-- Canaux -->
	<div class="bzmi-channels-section">
		<div class="bzmi-section-header">
			<h2 class="bzmi-section-title">
				<span class="dashicons dashicons-share"></span>
				<?php esc_html_e( 'Canaux de communication', 'blazing-feedback' ); ?>
			</h2>
			<button type="button" class="button button-primary bzmi-btn-add-channel">
				<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'Ajouter un canal', 'blazing-feedback' ); ?>
			</button>
		</div>

		<?php if ( empty( $channels ) ) : ?>
			<div class="bzmi-empty-inline">
				<span class="dashicons dashicons-share"></span>
				<p><?php esc_html_e( 'Aucun canal défini. Identifiez vos points de contact avec vos clients.', 'blazing-feedback' ); ?></p>
				<button type="button" class="button bzmi-btn-add-channel">
					<?php esc_html_e( 'Ajouter un canal', 'blazing-feedback' ); ?>
				</button>
			</div>
		<?php else : ?>
			<div class="bzmi-channels-grid">
				<?php
				// Grouper par type
				$channels_by_type = array();
				foreach ( $channels as $channel ) {
					$type = $channel->type ?: 'other';
					if ( ! isset( $channels_by_type[ $type ] ) ) {
						$channels_by_type[ $type ] = array();
					}
					$channels_by_type[ $type ][] = $channel;
				}
				?>

				<?php foreach ( $channels_by_type as $type => $type_channels ) :
					$type_info = BZMI_Foundation_Channel::TYPES[ $type ] ?? array( 'label' => $type, 'icon' => 'dashicons-admin-generic' );
				?>
					<div class="bzmi-channel-group">
						<div class="bzmi-channel-group__header">
							<span class="dashicons <?php echo esc_attr( $type_info['icon'] ); ?>"></span>
							<h4><?php echo esc_html( $type_info['label'] ); ?></h4>
							<span class="bzmi-channel-group__count"><?php echo count( $type_channels ); ?></span>
						</div>
						<div class="bzmi-channel-group__items">
							<?php foreach ( $type_channels as $channel ) : ?>
								<div class="bzmi-channel-item" data-channel-id="<?php echo esc_attr( $channel->id ); ?>">
									<div class="bzmi-channel-item__info">
										<span class="bzmi-channel-item__name"><?php echo esc_html( $channel->name ); ?></span>
										<?php if ( $channel->platform ) : ?>
											<span class="bzmi-channel-item__platform"><?php echo esc_html( $channel->platform ); ?></span>
										<?php endif; ?>
									</div>
									<?php if ( $channel->cta_primary ) : ?>
										<span class="bzmi-channel-item__cta"><?php echo esc_html( $channel->cta_primary ); ?></span>
									<?php endif; ?>
									<div class="bzmi-channel-item__actions">
										<button type="button" class="bzmi-btn-edit-channel" data-channel-id="<?php echo esc_attr( $channel->id ); ?>">
											<span class="dashicons dashicons-edit"></span>
										</button>
										<button type="button" class="bzmi-btn-delete-channel" data-channel-id="<?php echo esc_attr( $channel->id ); ?>">
											<span class="dashicons dashicons-trash"></span>
										</button>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>

				<!-- Zone pour ajouter -->
				<div class="bzmi-channel-group bzmi-channel-group--add">
					<button type="button" class="bzmi-btn-add-channel">
						<span class="dashicons dashicons-plus-alt2"></span>
						<span><?php esc_html_e( 'Ajouter un canal', 'blazing-feedback' ); ?></span>
					</button>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
