<?php
/**
 * Trait pour la section Border Radius (Design)
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait de gestion de la section Border Radius
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Settings_Design_Border_Radius {

	/**
	 * Valeur par défaut du border-radius
	 *
	 * @since 1.9.0
	 * @return int
	 */
	public static function get_border_radius_default() {
		return 8;
	}

	/**
	 * Rendre la section Border Radius
	 *
	 * @since 1.9.0
	 * @return void
	 */
	public static function render_design_border_radius_section() {
		$default = self::get_border_radius_default();
		$value = get_option( 'wpvfh_border_radius', $default );
		?>
		<div class="wpvfh-settings-section">
			<h2><?php esc_html_e( 'Arrondis des coins (Border Radius)', 'blazing-feedback' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Définissez l\'arrondi global pour tous les éléments de l\'interface (panneau, boutons, cartes, badges, champs, etc.).', 'blazing-feedback' ); ?></p>

			<div class="wpvfh-radius-setting" style="max-width: 500px; margin-top: 20px; padding: 20px; background: #f9f9f9; border: 1px solid #e0e4e8; border-radius: 8px;">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
					<span style="font-size: 24px;">📐</span>
					<div>
						<strong style="display: block; font-size: 15px;"><?php esc_html_e( 'Arrondi global', 'blazing-feedback' ); ?></strong>
						<small style="color: #666;"><?php esc_html_e( 'S\'applique à tous les éléments de la sidebar', 'blazing-feedback' ); ?></small>
					</div>
				</div>

				<div style="display: flex; align-items: center; gap: 16px;">
					<input
						type="range"
						name="wpvfh_border_radius"
						id="wpvfh_border_radius"
						value="<?php echo esc_attr( $value ); ?>"
						min="0"
						max="20"
						step="1"
						class="wpvfh-radius-slider"
						style="flex: 1; cursor: pointer;"
					>
					<div style="display: flex; align-items: center; gap: 4px;">
						<input
							type="number"
							value="<?php echo esc_attr( $value ); ?>"
							class="wpvfh-radius-number"
							id="wpvfh_border_radius_number"
							min="0"
							max="20"
							style="width: 60px; text-align: center; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; font-weight: 600;"
						>
						<span style="color: #666; font-size: 14px; font-weight: 500;">px</span>
					</div>
					<button
						type="button"
						class="button wpvfh-reset-radius"
						data-default="<?php echo esc_attr( $default ); ?>"
						title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>"
					>
						<span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px;"></span>
						<?php esc_html_e( 'Réinitialiser', 'blazing-feedback' ); ?>
					</button>
				</div>

				<!-- Aperçu -->
				<div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #e0e4e8;">
					<span style="font-size: 12px; color: #888; display: block; margin-bottom: 12px;"><?php esc_html_e( 'Aperçu:', 'blazing-feedback' ); ?></span>
					<div style="display: flex; gap: 16px; align-items: center;">
						<!-- Aperçu bouton -->
						<div
							id="wpvfh-preview-button"
							style="
								padding: 10px 20px;
								background: linear-gradient(135deg, var(--wpvfh-primary, #FE5100) 0%, #E04800 100%);
								color: #fff;
								font-weight: 500;
								border-radius: <?php echo esc_attr( $value ); ?>px;
								transition: border-radius 0.2s ease;
							"
						><?php esc_html_e( 'Bouton', 'blazing-feedback' ); ?></div>

						<!-- Aperçu carte -->
						<div
							id="wpvfh-preview-card"
							style="
								padding: 12px 16px;
								background: #fff;
								border: 1px solid #e0e4e8;
								border-radius: <?php echo esc_attr( $value ); ?>px;
								transition: border-radius 0.2s ease;
								font-size: 13px;
							"
						><?php esc_html_e( 'Carte feedback', 'blazing-feedback' ); ?></div>

						<!-- Aperçu badge -->
						<div
							id="wpvfh-preview-badge"
							style="
								padding: 4px 10px;
								background: #e3f2fd;
								color: #1565c0;
								font-size: 11px;
								font-weight: 500;
								border-radius: <?php echo esc_attr( $value ); ?>px;
								transition: border-radius 0.2s ease;
							"
						><?php esc_html_e( 'Badge', 'blazing-feedback' ); ?></div>
					</div>
				</div>
			</div>
		</div>

		<script>
		(function() {
			const slider = document.getElementById('wpvfh_border_radius');
			const numberInput = document.getElementById('wpvfh_border_radius_number');
			const previewButton = document.getElementById('wpvfh-preview-button');
			const previewCard = document.getElementById('wpvfh-preview-card');
			const previewBadge = document.getElementById('wpvfh-preview-badge');
			const resetBtn = document.querySelector('.wpvfh-reset-radius');

			function updatePreview(value) {
				if (previewButton) previewButton.style.borderRadius = value + 'px';
				if (previewCard) previewCard.style.borderRadius = value + 'px';
				if (previewBadge) previewBadge.style.borderRadius = value + 'px';
			}

			if (slider) {
				slider.addEventListener('input', function() {
					if (numberInput) numberInput.value = this.value;
					updatePreview(this.value);
				});
			}

			if (numberInput) {
				numberInput.addEventListener('input', function() {
					let val = parseInt(this.value) || 0;
					if (val > 20) val = 20;
					if (val < 0) val = 0;
					this.value = val;
					if (slider) slider.value = val;
					updatePreview(val);
				});
			}

			if (resetBtn) {
				resetBtn.addEventListener('click', function() {
					const defaultValue = this.getAttribute('data-default');
					if (slider) slider.value = defaultValue;
					if (numberInput) numberInput.value = defaultValue;
					updatePreview(defaultValue);
				});
			}
		})();
		</script>
		<?php
	}

	/**
	 * Générer le CSS inline pour le border-radius personnalisé
	 *
	 * @since 1.9.0
	 * @return string
	 */
	public static function get_custom_border_radius_css() {
		$default = self::get_border_radius_default();
		$value = intval( get_option( 'wpvfh_border_radius', $default ) );

		if ( $value === $default ) {
			return '';
		}

		$css = ':root {';
		$css .= '--wpvfh-radius: ' . $value . 'px;';
		$css .= '}';

		return $css;
	}
}
