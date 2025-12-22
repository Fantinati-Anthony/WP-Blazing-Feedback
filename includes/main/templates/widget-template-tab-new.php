<?php
/**
 * Template de l'onglet Nouveau feedback
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Récupérer les options
$feedback_types    = WPVFH_Options_Manager::get_types();
$priorities        = WPVFH_Options_Manager::get_priorities();
$predefined_tags   = WPVFH_Options_Manager::get_predefined_tags();
$custom_groups     = WPVFH_Options_Manager::get_custom_groups();
$types_settings    = WPVFH_Options_Manager::get_group_settings( 'types' );
$priority_settings = WPVFH_Options_Manager::get_group_settings( 'priorities' );
$tags_settings     = WPVFH_Options_Manager::get_group_settings( 'tags' );
?>
<!-- Onglet: Nouveau feedback -->
<div id="wpvfh-tab-new" class="wpvfh-tab-content">
	<form id="wpvfh-form" class="wpvfh-form">
	<!-- Zone de texte principale -->
	<div class="wpvfh-form-group">
		<textarea
			id="wpvfh-comment"
			name="comment"
			class="wpvfh-textarea"
			rows="3"
			required
			placeholder="<?php esc_attr_e( 'Décrivez votre feedback...', 'blazing-feedback' ); ?>"
		></textarea>
	</div>

	<!-- Barre d'outils média -->
	<div class="wpvfh-media-toolbar">
		<div class="wpvfh-tool-dropdown">
			<button type="button" class="wpvfh-tool-btn wpvfh-tool-screenshot" data-tool="screenshot" title="<?php esc_attr_e( 'Capture d\'écran', 'blazing-feedback' ); ?>">
				<span class="wpvfh-tool-emoji">📸</span>
				<span><?php esc_html_e( 'Capture', 'blazing-feedback' ); ?></span>
				<span class="wpvfh-dropdown-arrow">▾</span>
			</button>
			<div class="wpvfh-dropdown-menu" id="wpvfh-screenshot-menu">
				<button type="button" class="wpvfh-dropdown-item" data-action="screenshot-viewport">
					<span class="wpvfh-dropdown-icon">🖼️</span>
					<span class="wpvfh-dropdown-text"><?php esc_html_e( 'Capture visible', 'blazing-feedback' ); ?></span>
				</button>
				<button type="button" class="wpvfh-dropdown-item" data-action="screenshot-fullpage">
					<span class="wpvfh-dropdown-icon">📜</span>
					<span class="wpvfh-dropdown-text"><?php esc_html_e( 'Page entière', 'blazing-feedback' ); ?></span>
				</button>
			</div>
		</div>
		<button type="button" class="wpvfh-tool-btn wpvfh-tool-voice" data-tool="voice" title="<?php esc_attr_e( 'Message vocal', 'blazing-feedback' ); ?>">
			<span class="wpvfh-tool-emoji">🎤</span>
			<span><?php esc_html_e( 'Audio', 'blazing-feedback' ); ?></span>
		</button>
		<button type="button" class="wpvfh-tool-btn wpvfh-tool-video" data-tool="video" title="<?php esc_attr_e( 'Enregistrer l\'écran', 'blazing-feedback' ); ?>">
			<span class="wpvfh-tool-emoji">🎬</span>
			<span><?php esc_html_e( 'Vidéo', 'blazing-feedback' ); ?></span>
		</button>
		<button type="button" class="wpvfh-tool-btn wpvfh-tool-target" data-tool="target" title="<?php esc_attr_e( 'Cibler un élément', 'blazing-feedback' ); ?>">
			<span class="wpvfh-tool-emoji">🎯</span>
			<span><?php esc_html_e( 'Cibler', 'blazing-feedback' ); ?></span>
		</button>
		<button type="button" class="wpvfh-tool-btn wpvfh-tool-files" data-tool="files" title="<?php esc_attr_e( 'Ajouter des fichiers', 'blazing-feedback' ); ?>">
			<span class="wpvfh-tool-emoji">📎</span>
			<span><?php esc_html_e( 'Fichiers', 'blazing-feedback' ); ?></span>
		</button>
		<button type="button" class="wpvfh-tool-btn wpvfh-tool-links" data-tool="links" title="<?php esc_attr_e( 'Ajouter des liens', 'blazing-feedback' ); ?>">
			<span class="wpvfh-tool-emoji">🔗</span>
			<span><?php esc_html_e( 'Lien', 'blazing-feedback' ); ?></span>
		</button>
	</div>

	<!-- Indicateur élément ciblé -->
	<div id="wpvfh-selected-element" class="wpvfh-selected-element" hidden>
		<span class="wpvfh-selected-icon">✓</span>
		<span class="wpvfh-selected-text"><?php esc_html_e( 'Élément sélectionné', 'blazing-feedback' ); ?></span>
		<button type="button" class="wpvfh-clear-selection" title="<?php esc_attr_e( 'Retirer la sélection', 'blazing-feedback' ); ?>">&times;</button>
	</div>

	<!-- Section enregistrement vocal -->
	<div id="wpvfh-voice-section" class="wpvfh-media-section" hidden>
		<div class="wpvfh-recorder-controls">
			<button type="button" id="wpvfh-voice-record" class="wpvfh-record-btn">
				<span class="wpvfh-record-icon"></span>
				<span class="wpvfh-record-text"><?php esc_html_e( 'Enregistrer', 'blazing-feedback' ); ?></span>
			</button>
			<div class="wpvfh-recorder-status">
				<span class="wpvfh-recorder-time">0:00</span>
				<span class="wpvfh-recorder-max">/ 2:00</span>
			</div>
		</div>
		<div id="wpvfh-voice-preview" class="wpvfh-audio-preview" hidden>
			<audio controls></audio>
			<button type="button" class="wpvfh-remove-media">&times;</button>
		</div>
		<div id="wpvfh-transcript-preview" class="wpvfh-transcript-preview" hidden>
			<label><?php esc_html_e( 'Transcription:', 'blazing-feedback' ); ?></label>
			<p class="wpvfh-transcript-text"></p>
		</div>
	</div>

	<!-- Section enregistrement vidéo -->
	<div id="wpvfh-video-section" class="wpvfh-media-section" hidden>
		<div class="wpvfh-recorder-controls">
			<button type="button" id="wpvfh-video-record" class="wpvfh-record-btn">
				<span class="wpvfh-record-icon"></span>
				<span class="wpvfh-record-text"><?php esc_html_e( 'Enregistrer l\'écran', 'blazing-feedback' ); ?></span>
			</button>
			<div class="wpvfh-recorder-status">
				<span class="wpvfh-recorder-time">0:00</span>
				<span class="wpvfh-recorder-max">/ 5:00</span>
			</div>
		</div>
		<div id="wpvfh-video-preview" class="wpvfh-video-preview" hidden>
			<video controls></video>
			<button type="button" class="wpvfh-remove-media">&times;</button>
		</div>
	</div>

	<!-- Aperçu capture d'écran -->
	<div id="wpvfh-screenshot-preview" class="wpvfh-screenshot-preview" hidden>
		<img src="" alt="<?php esc_attr_e( 'Aperçu de la capture', 'blazing-feedback' ); ?>">
		<button type="button" class="wpvfh-remove-media">&times;</button>
	</div>

	<!-- Section pièces jointes -->
	<div id="wpvfh-attachments-section" class="wpvfh-attachments-section wpvfh-media-section" hidden>
		<div class="wpvfh-section-header">
			<span class="wpvfh-section-title">📎 <?php esc_html_e( 'Pièces jointes', 'blazing-feedback' ); ?></span>
		</div>
		<div class="wpvfh-attachments-input">
			<input type="file" id="wpvfh-attachments" name="attachments" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt" hidden>
			<button type="button" id="wpvfh-add-attachment-btn" class="wpvfh-add-attachment-btn">
				<span>➕</span>
				<?php esc_html_e( 'Ajouter des fichiers', 'blazing-feedback' ); ?>
			</button>
		</div>
		<div id="wpvfh-attachments-preview" class="wpvfh-attachments-preview"></div>
		<p class="wpvfh-attachments-hint"><?php esc_html_e( 'Images, PDF, documents (max 5 fichiers, 10 Mo chacun)', 'blazing-feedback' ); ?></p>
	</div>

	<!-- Section liens enrichis -->
	<div id="wpvfh-links-section" class="wpvfh-links-section wpvfh-media-section" hidden>
		<div class="wpvfh-section-header">
			<span class="wpvfh-section-title">🔗 <?php esc_html_e( 'Liens', 'blazing-feedback' ); ?></span>
		</div>
		<div id="wpvfh-links-list" class="wpvfh-links-list"></div>
		<div class="wpvfh-add-link-form">
			<input type="url" id="wpvfh-link-url" class="wpvfh-link-input" placeholder="<?php esc_attr_e( 'https://exemple.com', 'blazing-feedback' ); ?>">
			<button type="button" id="wpvfh-add-link-btn" class="wpvfh-add-link-btn">
				<span>➕</span>
				<?php esc_html_e( 'Ajouter', 'blazing-feedback' ); ?>
			</button>
		</div>
		<input type="hidden" id="wpvfh-links-data" name="links_data" value="">
	</div>

	<!-- Champs déroulants (Type, Priorité, Tags, Groupes personnalisés) -->
	<div class="wpvfh-form-dropdowns">
		<!-- Type de feedback -->
		<?php if ( $types_settings['enabled'] && WPVFH_Options_Manager::user_can_access_group( 'types' ) ) : ?>
		<div class="wpvfh-dropdown-group">
			<label for="wpvfh-feedback-type">
				<span class="wpvfh-dropdown-icon">🏷️</span>
				<?php esc_html_e( 'Type', 'blazing-feedback' ); ?>
				<?php if ( $types_settings['required'] ) : ?>
					<span class="wpvfh-required-badge">*</span>
				<?php endif; ?>
			</label>
			<select id="wpvfh-feedback-type" name="feedback_type" class="wpvfh-dropdown" <?php echo $types_settings['required'] ? 'required' : ''; ?>>
				<option value=""><?php esc_html_e( '-- Sélectionner --', 'blazing-feedback' ); ?></option>
				<?php foreach ( $feedback_types as $type ) : ?>
					<?php if ( ! empty( $type['enabled'] ) ) : ?>
					<option value="<?php echo esc_attr( $type['id'] ); ?>" data-color="<?php echo esc_attr( $type['color'] ); ?>">
						<?php echo esc_html( $type['emoji'] . ' ' . $type['label'] ); ?>
					</option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		</div>
		<?php endif; ?>

		<!-- Niveau de priorité -->
		<?php if ( $priority_settings['enabled'] && WPVFH_Options_Manager::user_can_access_group( 'priorities' ) ) : ?>
		<div class="wpvfh-dropdown-group">
			<label for="wpvfh-feedback-priority">
				<span class="wpvfh-dropdown-icon">⚡</span>
				<?php esc_html_e( 'Priorité', 'blazing-feedback' ); ?>
				<?php if ( $priority_settings['required'] ) : ?>
					<span class="wpvfh-required-badge">*</span>
				<?php endif; ?>
			</label>
			<select id="wpvfh-feedback-priority" name="feedback_priority" class="wpvfh-dropdown" <?php echo $priority_settings['required'] ? 'required' : ''; ?>>
				<?php foreach ( $priorities as $index => $priority ) : ?>
					<?php if ( ! empty( $priority['enabled'] ) ) : ?>
					<option value="<?php echo esc_attr( $priority['id'] ); ?>" data-color="<?php echo esc_attr( $priority['color'] ); ?>" <?php selected( $index, 0 ); ?>>
						<?php echo esc_html( $priority['emoji'] . ' ' . $priority['label'] ); ?>
					</option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		</div>
		<?php endif; ?>

		<!-- Tags -->
		<?php if ( $tags_settings['enabled'] && WPVFH_Options_Manager::user_can_access_group( 'tags' ) ) : ?>
		<div class="wpvfh-dropdown-group wpvfh-tags-group">
			<label for="wpvfh-feedback-tags-input">
				<span class="wpvfh-dropdown-icon">🔖</span>
				<?php esc_html_e( 'Tags', 'blazing-feedback' ); ?>
				<?php if ( $tags_settings['required'] ) : ?>
					<span class="wpvfh-required-badge">*</span>
				<?php endif; ?>
			</label>
			<div class="wpvfh-tags-container" id="wpvfh-feedback-tags-container">
				<?php if ( ! empty( $predefined_tags ) ) : ?>
					<div class="wpvfh-predefined-tags" id="wpvfh-predefined-tags">
						<?php foreach ( $predefined_tags as $tag ) : ?>
							<?php if ( ! empty( $tag['enabled'] ) ) : ?>
							<button type="button" class="wpvfh-predefined-tag-btn" data-tag="<?php echo esc_attr( $tag['label'] ); ?>" data-color="<?php echo esc_attr( $tag['color'] ); ?>">
								<?php echo esc_html( $tag['label'] ); ?>
							</button>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<input type="text" id="wpvfh-feedback-tags-input" class="wpvfh-tags-input-inline" placeholder="<?php esc_attr_e( 'Ajouter...', 'blazing-feedback' ); ?>" <?php echo $tags_settings['required'] ? 'data-required="true"' : ''; ?>>
			</div>
			<input type="hidden" id="wpvfh-feedback-tags" name="feedback_tags" <?php echo $tags_settings['required'] ? 'required' : ''; ?>>
		</div>
		<?php endif; ?>

		<!-- Groupes personnalisés -->
		<?php foreach ( $custom_groups as $slug => $group ) :
			$group_settings = WPVFH_Options_Manager::get_group_settings( $slug );
			if ( ! $group_settings['enabled'] || ! WPVFH_Options_Manager::user_can_access_group( $slug ) ) {
				continue;
			}
			$group_items = WPVFH_Options_Manager::get_custom_group_items( $slug );
			if ( empty( $group_items ) ) {
				continue;
			}
		?>
		<div class="wpvfh-dropdown-group wpvfh-custom-group" data-group="<?php echo esc_attr( $slug ); ?>">
			<label for="wpvfh-custom-<?php echo esc_attr( $slug ); ?>">
				<span class="wpvfh-dropdown-icon">📋</span>
				<?php echo esc_html( $group['name'] ); ?>
				<?php if ( $group_settings['required'] ) : ?>
					<span class="wpvfh-required-badge">*</span>
				<?php endif; ?>
			</label>
			<select id="wpvfh-custom-<?php echo esc_attr( $slug ); ?>" name="custom_<?php echo esc_attr( $slug ); ?>" class="wpvfh-dropdown wpvfh-custom-dropdown" <?php echo $group_settings['required'] ? 'required' : ''; ?>>
				<option value=""><?php esc_html_e( '-- Sélectionner --', 'blazing-feedback' ); ?></option>
				<?php foreach ( $group_items as $item ) : ?>
					<?php if ( ! empty( $item['enabled'] ) ) : ?>
					<option value="<?php echo esc_attr( $item['id'] ); ?>" data-color="<?php echo esc_attr( $item['color'] ); ?>">
						<?php echo esc_html( $item['emoji'] . ' ' . $item['label'] ); ?>
					</option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		</div>
		<?php endforeach; ?>
	</div>

	<!-- Info pin -->
	<div class="wpvfh-form-group wpvfh-pin-info" hidden>
		<p class="wpvfh-help-text">
			<span class="wpvfh-pin-icon" aria-hidden="true">📍</span>
			<?php esc_html_e( 'Position du marqueur enregistrée', 'blazing-feedback' ); ?>
		</p>
	</div>

	<!-- Champs cachés -->
	<input type="hidden" id="wpvfh-position-x" name="position_x" value="">
	<input type="hidden" id="wpvfh-position-y" name="position_y" value="">
	<input type="hidden" id="wpvfh-screenshot-data" name="screenshot_data" value="">
	<input type="hidden" id="wpvfh-audio-data" name="audio_data" value="">
	<input type="hidden" id="wpvfh-video-data" name="video_data" value="">
	<input type="hidden" id="wpvfh-transcript" name="transcript" value="">

	<!-- Actions -->
	<div class="wpvfh-form-actions">
		<button type="button" class="wpvfh-btn wpvfh-btn-secondary wpvfh-cancel-btn">
			<span class="wpvfh-btn-emoji">✕</span>
			<?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?>
		</button>
		<button type="submit" class="wpvfh-btn wpvfh-btn-primary wpvfh-submit-btn">
			<span class="wpvfh-btn-emoji">📨</span>
			<?php esc_html_e( 'Envoyer', 'blazing-feedback' ); ?>
		</button>
	</div>
</form>
</div><!-- /wpvfh-tab-new -->
