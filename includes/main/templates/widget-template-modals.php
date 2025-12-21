<?php
/**
 * Template des modals et overlays
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- Conteneur pour les pins existants -->
<div id="wpvfh-pins-container" class="wpvfh-pins-container" aria-live="polite"></div>

<!-- Overlay mode annotation -->
<div id="wpvfh-annotation-overlay" class="wpvfh-annotation-overlay" hidden>
	<div class="wpvfh-annotation-hint">
		<span class="wpvfh-hint-icon" aria-hidden="true">👆</span>
		<span class="wpvfh-hint-text"><?php esc_html_e( 'Cliquez pour placer un marqueur', 'blazing-feedback' ); ?></span>
		<button type="button" class="wpvfh-hint-close"><?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?></button>
	</div>
</div>

<!-- Messages de notification -->
<div id="wpvfh-notifications" class="wpvfh-notifications" aria-live="assertive"></div>

<!-- Dropdown suggestions mentions @ -->
<div id="wpvfh-mention-dropdown" class="wpvfh-mention-dropdown" hidden>
	<div class="wpvfh-mention-list" id="wpvfh-mention-list">
		<!-- Utilisateurs suggérés chargés dynamiquement -->
	</div>
</div>

<!-- Modal confirmation suppression -->
<div id="wpvfh-confirm-modal" class="wpvfh-modal" hidden>
	<div class="wpvfh-modal-overlay"></div>
	<div class="wpvfh-modal-content">
		<div class="wpvfh-modal-body">
			<h3 class="wpvfh-modal-title"><?php esc_html_e( 'Confirmer la suppression', 'blazing-feedback' ); ?></h3>
			<p class="wpvfh-modal-text"><?php esc_html_e( 'Êtes-vous sûr de vouloir supprimer ce feedback ? Cette action est irréversible.', 'blazing-feedback' ); ?></p>
			<div class="wpvfh-modal-actions">
				<button type="button" class="wpvfh-btn wpvfh-btn-secondary" id="wpvfh-cancel-delete">
					<?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?>
				</button>
				<button type="button" class="wpvfh-btn wpvfh-btn-danger" id="wpvfh-confirm-delete">
					<?php esc_html_e( 'Supprimer', 'blazing-feedback' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal validation page -->
<div id="wpvfh-validate-modal" class="wpvfh-modal" hidden>
	<div class="wpvfh-modal-overlay"></div>
	<div class="wpvfh-modal-content">
		<div class="wpvfh-modal-body">
			<div class="wpvfh-modal-icon">✅</div>
			<h3 class="wpvfh-modal-title"><?php esc_html_e( 'Valider cette page', 'blazing-feedback' ); ?></h3>
			<p class="wpvfh-modal-text"><?php esc_html_e( 'En validant cette page, vous confirmez que tous les feedbacks ont été traités. Cette page sera marquée comme terminée.', 'blazing-feedback' ); ?></p>
			<div class="wpvfh-modal-actions">
				<button type="button" class="wpvfh-btn wpvfh-btn-secondary" id="wpvfh-cancel-validate">
					<?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?>
				</button>
				<button type="button" class="wpvfh-btn wpvfh-btn-success" id="wpvfh-confirm-validate">
					<?php esc_html_e( 'Valider', 'blazing-feedback' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal de recherche -->
<div id="wpvfh-search-modal" class="wpvfh-modal wpvfh-search-modal" hidden>
	<div class="wpvfh-modal-overlay"></div>
	<div class="wpvfh-modal-content wpvfh-search-content">
		<div class="wpvfh-search-header">
			<h3 class="wpvfh-modal-title">🔍 <?php esc_html_e( 'Rechercher un feedback', 'blazing-feedback' ); ?></h3>
			<button type="button" class="wpvfh-search-close" id="wpvfh-search-close">&times;</button>
		</div>
		<form id="wpvfh-search-form" class="wpvfh-search-form">
			<!-- Recherche par ID -->
			<div class="wpvfh-search-group">
				<label for="wpvfh-search-id"><?php esc_html_e( 'Numéro du feedback', 'blazing-feedback' ); ?></label>
				<input type="number" id="wpvfh-search-id" placeholder="<?php esc_attr_e( 'Ex: 123', 'blazing-feedback' ); ?>" min="1">
			</div>

			<!-- Recherche par texte -->
			<div class="wpvfh-search-group">
				<label for="wpvfh-search-text"><?php esc_html_e( 'Contenu du commentaire', 'blazing-feedback' ); ?></label>
				<input type="text" id="wpvfh-search-text" placeholder="<?php esc_attr_e( 'Rechercher dans le texte...', 'blazing-feedback' ); ?>">
			</div>

			<!-- Filtres sur une ligne -->
			<div class="wpvfh-search-filters">
				<!-- Statut -->
				<div class="wpvfh-search-group wpvfh-search-filter">
					<label for="wpvfh-search-status"><?php esc_html_e( 'Statut', 'blazing-feedback' ); ?></label>
					<select id="wpvfh-search-status">
						<option value=""><?php esc_html_e( 'Tous', 'blazing-feedback' ); ?></option>
						<option value="new"><?php esc_html_e( 'Nouveau', 'blazing-feedback' ); ?></option>
						<option value="in_progress"><?php esc_html_e( 'En cours', 'blazing-feedback' ); ?></option>
						<option value="resolved"><?php esc_html_e( 'Résolu', 'blazing-feedback' ); ?></option>
						<option value="rejected"><?php esc_html_e( 'Rejeté', 'blazing-feedback' ); ?></option>
					</select>
				</div>

				<!-- Priorité -->
				<div class="wpvfh-search-group wpvfh-search-filter">
					<label for="wpvfh-search-priority"><?php esc_html_e( 'Priorité', 'blazing-feedback' ); ?></label>
					<select id="wpvfh-search-priority">
						<option value=""><?php esc_html_e( 'Toutes', 'blazing-feedback' ); ?></option>
						<option value="high"><?php esc_html_e( 'Haute', 'blazing-feedback' ); ?></option>
						<option value="medium"><?php esc_html_e( 'Moyenne', 'blazing-feedback' ); ?></option>
						<option value="low"><?php esc_html_e( 'Basse', 'blazing-feedback' ); ?></option>
						<option value="none"><?php esc_html_e( 'Aucune', 'blazing-feedback' ); ?></option>
					</select>
				</div>

				<!-- Auteur -->
				<div class="wpvfh-search-group wpvfh-search-filter">
					<label for="wpvfh-search-author"><?php esc_html_e( 'Auteur', 'blazing-feedback' ); ?></label>
					<input type="text" id="wpvfh-search-author" placeholder="<?php esc_attr_e( 'Nom...', 'blazing-feedback' ); ?>">
				</div>
			</div>

			<!-- Date -->
			<div class="wpvfh-search-dates">
				<div class="wpvfh-search-group wpvfh-search-filter">
					<label for="wpvfh-search-date-from"><?php esc_html_e( 'Du', 'blazing-feedback' ); ?></label>
					<input type="date" id="wpvfh-search-date-from">
				</div>
				<div class="wpvfh-search-group wpvfh-search-filter">
					<label for="wpvfh-search-date-to"><?php esc_html_e( 'Au', 'blazing-feedback' ); ?></label>
					<input type="date" id="wpvfh-search-date-to">
				</div>
			</div>

			<!-- Actions -->
			<div class="wpvfh-search-actions">
				<button type="button" class="wpvfh-btn wpvfh-btn-secondary" id="wpvfh-search-reset">
					<?php esc_html_e( 'Réinitialiser', 'blazing-feedback' ); ?>
				</button>
				<button type="submit" class="wpvfh-btn wpvfh-btn-primary">
					<?php esc_html_e( 'Rechercher', 'blazing-feedback' ); ?>
				</button>
			</div>
		</form>

		<!-- Résultats -->
		<div id="wpvfh-search-results" class="wpvfh-search-results" hidden>
			<div class="wpvfh-search-results-header">
				<span id="wpvfh-search-results-count"></span>
			</div>
			<div id="wpvfh-search-results-list" class="wpvfh-search-results-list"></div>
		</div>
	</div>
</div>

<!-- Modal éditeur de capture d'écran -->
<div id="wpvfh-screenshot-editor" class="wpvfh-modal wpvfh-screenshot-editor-modal" hidden>
	<div class="wpvfh-modal-overlay"></div>
	<div class="wpvfh-modal-content wpvfh-editor-content">
		<div class="wpvfh-editor-header">
			<h3 class="wpvfh-modal-title">✏️ <?php esc_html_e( 'Annoter la capture', 'blazing-feedback' ); ?></h3>
			<button type="button" class="wpvfh-editor-close" id="wpvfh-editor-close">&times;</button>
		</div>
		<div class="wpvfh-editor-toolbar">
			<div class="wpvfh-editor-tools">
				<button type="button" class="wpvfh-editor-tool active" data-tool="pen" title="<?php esc_attr_e( 'Crayon', 'blazing-feedback' ); ?>">
					<span>✏️</span>
				</button>
				<button type="button" class="wpvfh-editor-tool" data-tool="line" title="<?php esc_attr_e( 'Ligne', 'blazing-feedback' ); ?>">
					<span>📏</span>
				</button>
				<button type="button" class="wpvfh-editor-tool" data-tool="arrow" title="<?php esc_attr_e( 'Flèche', 'blazing-feedback' ); ?>">
					<span>➡️</span>
				</button>
				<button type="button" class="wpvfh-editor-tool" data-tool="rect" title="<?php esc_attr_e( 'Rectangle', 'blazing-feedback' ); ?>">
					<span>⬜</span>
				</button>
				<button type="button" class="wpvfh-editor-tool" data-tool="circle" title="<?php esc_attr_e( 'Cercle', 'blazing-feedback' ); ?>">
					<span>⭕</span>
				</button>
				<button type="button" class="wpvfh-editor-tool" data-tool="text" title="<?php esc_attr_e( 'Texte', 'blazing-feedback' ); ?>">
					<span>T</span>
				</button>
			</div>
			<div class="wpvfh-editor-options">
				<input type="color" id="wpvfh-editor-color" value="#e74c3c" title="<?php esc_attr_e( 'Couleur', 'blazing-feedback' ); ?>">
				<select id="wpvfh-editor-size" title="<?php esc_attr_e( 'Épaisseur', 'blazing-feedback' ); ?>">
					<option value="2">Fine</option>
					<option value="4" selected>Normal</option>
					<option value="8">Épaisse</option>
				</select>
				<button type="button" class="wpvfh-editor-undo" id="wpvfh-editor-undo" title="<?php esc_attr_e( 'Annuler', 'blazing-feedback' ); ?>">
					<span>↩️</span>
				</button>
				<button type="button" class="wpvfh-editor-clear" id="wpvfh-editor-clear" title="<?php esc_attr_e( 'Effacer tout', 'blazing-feedback' ); ?>">
					<span>🗑️</span>
				</button>
			</div>
		</div>
		<div class="wpvfh-editor-canvas-container">
			<canvas id="wpvfh-editor-canvas"></canvas>
		</div>
		<div class="wpvfh-editor-actions">
			<button type="button" class="wpvfh-btn wpvfh-btn-secondary" id="wpvfh-editor-cancel">
				<?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?>
			</button>
			<button type="button" class="wpvfh-btn wpvfh-btn-primary" id="wpvfh-editor-save">
				<?php esc_html_e( 'Enregistrer', 'blazing-feedback' ); ?>
			</button>
		</div>
	</div>
</div>
