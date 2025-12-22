<?php
/**
 * Template: Onglet Identité
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 *
 * @var BZMI_Foundation $foundation       La fondation
 * @var array           $identity_sections Sections d'identité
 * @var array           $personas          Les personas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="bzmi-tab-content bzmi-tab-identity">
	<!-- Sections d'identité -->
	<div class="bzmi-sections-grid">
		<?php foreach ( $identity_sections as $section_key => $section ) :
			$section_data = $section['data'];
			$content = $section_data ? $section_data->get_content() : array();
			$status = $section_data ? $section_data->status : 'empty';
			$score = $section_data ? $section_data->get_completion_score() : 0;

			$status_class = 'empty' === $status ? 'empty' : ( 'validated' === $status ? 'validated' : 'hypothesis' );
			$status_icon = 'empty' === $status ? 'marker' : ( 'validated' === $status ? 'yes-alt' : 'edit' );
		?>
			<div class="bzmi-section-card" data-section="<?php echo esc_attr( $section_key ); ?>">
				<div class="bzmi-section-card__header">
					<h3 class="bzmi-section-card__title">
						<?php echo esc_html( $section['label'] ); ?>
					</h3>
					<div class="bzmi-section-card__status">
						<span class="bzmi-status-badge bzmi-status-badge--<?php echo esc_attr( $status_class ); ?>">
							<span class="dashicons dashicons-<?php echo esc_attr( $status_icon ); ?>"></span>
							<?php
							if ( 'empty' === $status ) {
								esc_html_e( 'À compléter', 'blazing-feedback' );
							} elseif ( 'validated' === $status ) {
								esc_html_e( 'Validé', 'blazing-feedback' );
							} else {
								esc_html_e( 'Hypothèse', 'blazing-feedback' );
							}
							?>
						</span>
					</div>
				</div>

				<div class="bzmi-section-card__progress">
					<div class="bzmi-mini-progress">
						<div class="bzmi-mini-progress__fill" style="width: <?php echo esc_attr( $score ); ?>%"></div>
					</div>
					<span class="bzmi-mini-progress__label"><?php echo esc_html( $score ); ?>%</span>
				</div>

				<div class="bzmi-section-card__preview">
					<?php
					// Aperçu du contenu selon la section
					switch ( $section_key ) {
						case 'brand_dna':
							if ( ! empty( $content['mission'] ) ) {
								echo '<p class="bzmi-preview-text">' . esc_html( wp_trim_words( $content['mission'], 15 ) ) . '</p>';
							}
							if ( ! empty( $content['values'] ) && is_array( $content['values'] ) ) {
								echo '<div class="bzmi-preview-tags">';
								foreach ( array_slice( $content['values'], 0, 3 ) as $value ) {
									echo '<span class="bzmi-tag">' . esc_html( $value ) . '</span>';
								}
								echo '</div>';
							}
							break;

						case 'vision':
							if ( ! empty( $content['vision_statement'] ) ) {
								echo '<p class="bzmi-preview-text">' . esc_html( wp_trim_words( $content['vision_statement'], 15 ) ) . '</p>';
							}
							break;

						case 'tone_voice':
							if ( ! empty( $content['tone_attributes'] ) && is_array( $content['tone_attributes'] ) ) {
								echo '<div class="bzmi-preview-tags">';
								foreach ( array_slice( $content['tone_attributes'], 0, 4 ) as $attr ) {
									echo '<span class="bzmi-tag">' . esc_html( $attr ) . '</span>';
								}
								echo '</div>';
							}
							break;

						case 'colors':
							if ( ! empty( $content['primary_color'] ) ) {
								echo '<div class="bzmi-preview-colors">';
								$colors = array( 'primary_color', 'secondary_color', 'accent_color' );
								foreach ( $colors as $color_key ) {
									if ( ! empty( $content[ $color_key ] ) ) {
										echo '<span class="bzmi-color-dot" style="background-color: ' . esc_attr( $content[ $color_key ] ) . '"></span>';
									}
								}
								echo '</div>';
							}
							break;

						case 'typography':
							if ( ! empty( $content['heading_font'] ) ) {
								echo '<p class="bzmi-preview-font">' . esc_html( $content['heading_font'] );
								if ( ! empty( $content['body_font'] ) ) {
									echo ' / ' . esc_html( $content['body_font'] );
								}
								echo '</p>';
							}
							break;

						default:
							echo '<p class="bzmi-preview-empty">' . esc_html__( 'Cliquez pour compléter', 'blazing-feedback' ) . '</p>';
					}

					if ( empty( $content ) || ( is_array( $content ) && count( array_filter( $content ) ) === 0 ) ) {
						echo '<p class="bzmi-preview-empty">' . esc_html__( 'Cliquez pour compléter', 'blazing-feedback' ) . '</p>';
					}
					?>
				</div>

				<div class="bzmi-section-card__actions">
					<button type="button" class="button bzmi-btn-edit-section" data-section="<?php echo esc_attr( $section_key ); ?>">
						<span class="dashicons dashicons-edit"></span>
						<?php esc_html_e( 'Éditer', 'blazing-feedback' ); ?>
					</button>
					<button type="button" class="button bzmi-btn-ai-section" data-section="<?php echo esc_attr( $section_key ); ?>" title="<?php esc_attr_e( 'Suggestions IA', 'blazing-feedback' ); ?>">
						<span class="dashicons dashicons-superhero"></span>
					</button>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Personas -->
	<div class="bzmi-personas-section">
		<div class="bzmi-section-header">
			<h2 class="bzmi-section-title">
				<span class="dashicons dashicons-groups"></span>
				<?php esc_html_e( 'Personas / Cibles', 'blazing-feedback' ); ?>
			</h2>
			<button type="button" class="button button-primary bzmi-btn-add-persona">
				<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'Ajouter un persona', 'blazing-feedback' ); ?>
			</button>
		</div>

		<?php if ( empty( $personas ) ) : ?>
			<div class="bzmi-empty-inline">
				<span class="dashicons dashicons-admin-users"></span>
				<p><?php esc_html_e( 'Aucun persona défini. Créez vos premiers personas pour mieux cibler votre communication.', 'blazing-feedback' ); ?></p>
				<button type="button" class="button bzmi-btn-add-persona">
					<?php esc_html_e( 'Créer un persona', 'blazing-feedback' ); ?>
				</button>
				<button type="button" class="button bzmi-btn-ai-persona">
					<span class="dashicons dashicons-superhero"></span>
					<?php esc_html_e( 'Générer avec l\'IA', 'blazing-feedback' ); ?>
				</button>
			</div>
		<?php else : ?>
			<div class="bzmi-personas-grid">
				<?php foreach ( $personas as $persona ) :
					$completion = $persona->get_completion_score();
				?>
					<div class="bzmi-persona-card" data-persona-id="<?php echo esc_attr( $persona->id ); ?>">
						<div class="bzmi-persona-card__avatar">
							<img src="<?php echo esc_url( $persona->get_avatar_url() ); ?>" alt="<?php echo esc_attr( $persona->name ); ?>">
						</div>
						<div class="bzmi-persona-card__content">
							<h4 class="bzmi-persona-card__name"><?php echo esc_html( $persona->name ); ?></h4>
							<?php if ( $persona->job_title ) : ?>
								<span class="bzmi-persona-card__job"><?php echo esc_html( $persona->job_title ); ?></span>
							<?php endif; ?>
							<?php if ( $persona->age_range ) : ?>
								<span class="bzmi-persona-card__age"><?php echo esc_html( $persona->age_range ); ?></span>
							<?php endif; ?>

							<?php if ( $persona->quote ) : ?>
								<blockquote class="bzmi-persona-card__quote">
									"<?php echo esc_html( wp_trim_words( $persona->quote, 10 ) ); ?>"
								</blockquote>
							<?php endif; ?>

							<div class="bzmi-persona-card__progress">
								<div class="bzmi-mini-progress">
									<div class="bzmi-mini-progress__fill" style="width: <?php echo esc_attr( $completion ); ?>%"></div>
								</div>
								<span><?php echo esc_html( $completion ); ?>%</span>
							</div>
						</div>
						<div class="bzmi-persona-card__actions">
							<button type="button" class="button button-small bzmi-btn-edit-persona" data-persona-id="<?php echo esc_attr( $persona->id ); ?>">
								<span class="dashicons dashicons-edit"></span>
							</button>
							<button type="button" class="button button-small bzmi-btn-delete-persona" data-persona-id="<?php echo esc_attr( $persona->id ); ?>">
								<span class="dashicons dashicons-trash"></span>
							</button>
						</div>
					</div>
				<?php endforeach; ?>

				<!-- Carte pour ajouter -->
				<div class="bzmi-persona-card bzmi-persona-card--add">
					<button type="button" class="bzmi-btn-add-persona">
						<span class="dashicons dashicons-plus-alt2"></span>
						<span><?php esc_html_e( 'Ajouter', 'blazing-feedback' ); ?></span>
					</button>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
