<?php
/**
 * Trait pour la section Couleurs du thème (Design)
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait de gestion de la section Couleurs du thème
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Settings_Design_Colors {

	/**
	 * Rendre la section Couleurs du thème
	 *
	 * @since 1.9.0
	 * @return void
	 */
	public static function render_design_colors_section() {
		?>
		<div class="wpvfh-settings-section">
			<h2><?php esc_html_e( 'Couleurs du thème', 'blazing-feedback' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Personnalisez les couleurs du widget de feedback selon le mode sélectionné.', 'blazing-feedback' ); ?></p>

			<?php
			self::render_common_colors();
			self::render_light_mode_colors();
			self::render_dark_mode_colors();
			self::render_pin_item_light_colors();
			self::render_pin_item_dark_colors();
			self::render_footer_light_colors();
			self::render_footer_dark_colors();
			?>
		</div>
		<?php
	}

	/**
	 * Rendre les couleurs communes
	 *
	 * @since 1.9.0
	 * @return void
	 */
	private static function render_common_colors() {
		$common_colors = array(
			'wpvfh_color_primary'       => __( 'Principale', 'blazing-feedback' ),
			'wpvfh_color_primary_hover' => __( 'Principale (survol)', 'blazing-feedback' ),
			'wpvfh_color_success'       => __( 'Succès', 'blazing-feedback' ),
			'wpvfh_color_warning'       => __( 'Avertissement', 'blazing-feedback' ),
			'wpvfh_color_danger'        => __( 'Danger', 'blazing-feedback' ),
		);
		$common_defaults = array(
			'wpvfh_color_primary'       => '#FE5100',
			'wpvfh_color_primary_hover' => '#E04800',
			'wpvfh_color_success'       => '#28a745',
			'wpvfh_color_warning'       => '#ffc107',
			'wpvfh_color_danger'        => '#dc3545',
		);
		?>
		<h4 style="margin: 15px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd;"><?php esc_html_e( 'Couleurs communes', 'blazing-feedback' ); ?></h4>
		<div class="wpvfh-color-grid">
			<?php foreach ( $common_colors as $option_name => $label ) :
				$value = get_option( $option_name, $common_defaults[ $option_name ] );
			?>
			<div class="wpvfh-color-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #f9f9f9; border-radius: 4px;">
				<input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
				<input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
				<span style="flex: 1; font-size: 13px;"><?php echo esc_html( $label ); ?></span>
				<button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $common_defaults[ $option_name ] ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>">
					<span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
				</button>
			</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Rendre les couleurs mode clair
	 *
	 * @since 1.9.0
	 * @return void
	 */
	private static function render_light_mode_colors() {
		$light_colors = array(
			'wpvfh_color_bg'            => __( 'Fond principal', 'blazing-feedback' ),
			'wpvfh_color_bg_light'      => __( 'Fond secondaire', 'blazing-feedback' ),
			'wpvfh_color_text'          => __( 'Texte', 'blazing-feedback' ),
			'wpvfh_color_text_light'    => __( 'Texte secondaire', 'blazing-feedback' ),
			'wpvfh_color_secondary'     => __( 'Secondaire', 'blazing-feedback' ),
			'wpvfh_color_border'        => __( 'Bordures', 'blazing-feedback' ),
		);
		$light_defaults = array(
			'wpvfh_color_bg'            => '#ffffff',
			'wpvfh_color_bg_light'      => '#f8f9fa',
			'wpvfh_color_text'          => '#263e4b',
			'wpvfh_color_text_light'    => '#5a7282',
			'wpvfh_color_secondary'     => '#263e4b',
			'wpvfh_color_border'        => '#e0e4e8',
		);
		?>
		<h4 style="margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 8px;">
			<span style="font-size: 16px;">☀️</span>
			<?php esc_html_e( 'Mode Clair', 'blazing-feedback' ); ?>
			<small style="color: #666; font-weight: normal;">(fond #ffffff)</small>
		</h4>
		<div class="wpvfh-color-grid">
			<?php foreach ( $light_colors as $option_name => $label ) :
				$value = get_option( $option_name, $light_defaults[ $option_name ] );
			?>
			<div class="wpvfh-color-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #f9f9f9; border-radius: 4px;">
				<input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
				<input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
				<span style="flex: 1; font-size: 13px;"><?php echo esc_html( $label ); ?></span>
				<button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $light_defaults[ $option_name ] ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>">
					<span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
				</button>
			</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Rendre les couleurs mode sombre
	 *
	 * @since 1.9.0
	 * @return void
	 */
	private static function render_dark_mode_colors() {
		$dark_colors = array(
			'wpvfh_color_bg_dark'            => __( 'Fond principal', 'blazing-feedback' ),
			'wpvfh_color_bg_light_dark'      => __( 'Fond secondaire', 'blazing-feedback' ),
			'wpvfh_color_text_dark'          => __( 'Texte', 'blazing-feedback' ),
			'wpvfh_color_text_light_dark'    => __( 'Texte secondaire', 'blazing-feedback' ),
			'wpvfh_color_secondary_dark'     => __( 'Secondaire', 'blazing-feedback' ),
			'wpvfh_color_border_dark'        => __( 'Bordures', 'blazing-feedback' ),
		);
		$dark_defaults = array(
			'wpvfh_color_bg_dark'            => '#263e4b',
			'wpvfh_color_bg_light_dark'      => '#334a5a',
			'wpvfh_color_text_dark'          => '#ffffff',
			'wpvfh_color_text_light_dark'    => '#b0bcc4',
			'wpvfh_color_secondary_dark'     => '#4a6572',
			'wpvfh_color_border_dark'        => '#3d5564',
		);
		?>
		<h4 style="margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 8px;">
			<span style="font-size: 16px;">🌙</span>
			<?php esc_html_e( 'Mode Sombre', 'blazing-feedback' ); ?>
			<small style="color: #666; font-weight: normal;">(fond #263E4B)</small>
		</h4>
		<div class="wpvfh-color-grid" style="background: #263e4b; padding: 10px; border-radius: 6px;">
			<?php foreach ( $dark_colors as $option_name => $label ) :
				$value = get_option( $option_name, $dark_defaults[ $option_name ] );
			?>
			<div class="wpvfh-color-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #334a5a; border-radius: 4px;">
				<input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
				<input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 70px; font-family: monospace; font-size: 12px; background: #263e4b; color: #fff; border-color: #3d5564;" maxlength="7">
				<span style="flex: 1; font-size: 13px; color: #fff;"><?php echo esc_html( $label ); ?></span>
				<button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $dark_defaults[ $option_name ] ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>" style="background: #4a6572; border-color: #5a7282; color: #fff;">
					<span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
				</button>
			</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Rendre les couleurs des éléments de liste (pin-item) mode clair
	 *
	 * @since 1.9.0
	 * @return void
	 */
	private static function render_pin_item_light_colors() {
		$pin_item_light_colors = array(
			'wpvfh_color_pin_item_bg'           => __( 'Fond', 'blazing-feedback' ),
			'wpvfh_color_pin_item_bg_hover'     => __( 'Fond (survol)', 'blazing-feedback' ),
			'wpvfh_color_pin_item_bg_selected'  => __( 'Fond (sélectionné)', 'blazing-feedback' ),
			'wpvfh_color_pin_item_border'       => __( 'Bordure', 'blazing-feedback' ),
			'wpvfh_color_pin_item_border_hover' => __( 'Bordure (survol)', 'blazing-feedback' ),
			'wpvfh_color_pin_item_text'         => __( 'Texte', 'blazing-feedback' ),
			'wpvfh_color_pin_item_text_light'   => __( 'Texte secondaire', 'blazing-feedback' ),
		);
		$pin_item_light_defaults = array(
			'wpvfh_color_pin_item_bg'           => '#f8f9fa',
			'wpvfh_color_pin_item_bg_hover'     => '#ffffff',
			'wpvfh_color_pin_item_bg_selected'  => '#fff5f3',
			'wpvfh_color_pin_item_border'       => '#e0e4e8',
			'wpvfh_color_pin_item_border_hover' => '#FE5100',
			'wpvfh_color_pin_item_text'         => '#263e4b',
			'wpvfh_color_pin_item_text_light'   => '#5a7282',
		);
		?>
		<h4 style="margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 8px;">
			<span style="font-size: 16px;">📝</span>
			<?php esc_html_e( 'Éléments de liste - Mode Clair', 'blazing-feedback' ); ?>
			<small style="color: #666; font-weight: normal;">(cartes feedback)</small>
		</h4>
		<div class="wpvfh-color-grid">
			<?php foreach ( $pin_item_light_colors as $option_name => $label ) :
				$value = get_option( $option_name, $pin_item_light_defaults[ $option_name ] );
			?>
			<div class="wpvfh-color-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #f5f5f5; border-radius: 4px;">
				<input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
				<input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
				<span style="flex: 1; font-size: 13px;"><?php echo esc_html( $label ); ?></span>
				<button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $pin_item_light_defaults[ $option_name ] ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>">
					<span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
				</button>
			</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Rendre les couleurs des éléments de liste (pin-item) mode sombre
	 *
	 * @since 1.9.0
	 * @return void
	 */
	private static function render_pin_item_dark_colors() {
		$pin_item_dark_colors = array(
			'wpvfh_color_pin_item_bg_dark'           => __( 'Fond', 'blazing-feedback' ),
			'wpvfh_color_pin_item_bg_hover_dark'     => __( 'Fond (survol)', 'blazing-feedback' ),
			'wpvfh_color_pin_item_bg_selected_dark'  => __( 'Fond (sélectionné)', 'blazing-feedback' ),
			'wpvfh_color_pin_item_border_dark'       => __( 'Bordure', 'blazing-feedback' ),
			'wpvfh_color_pin_item_border_hover_dark' => __( 'Bordure (survol)', 'blazing-feedback' ),
			'wpvfh_color_pin_item_text_dark'         => __( 'Texte', 'blazing-feedback' ),
			'wpvfh_color_pin_item_text_light_dark'   => __( 'Texte secondaire', 'blazing-feedback' ),
		);
		$pin_item_dark_defaults = array(
			'wpvfh_color_pin_item_bg_dark'           => '#334a5a',
			'wpvfh_color_pin_item_bg_hover_dark'     => '#3d5564',
			'wpvfh_color_pin_item_bg_selected_dark'  => '#4a3530',
			'wpvfh_color_pin_item_border_dark'       => '#3d5564',
			'wpvfh_color_pin_item_border_hover_dark' => '#FE5100',
			'wpvfh_color_pin_item_text_dark'         => '#ffffff',
			'wpvfh_color_pin_item_text_light_dark'   => '#b0bcc4',
		);
		?>
		<h4 style="margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 8px;">
			<span style="font-size: 16px;">📝</span>
			<?php esc_html_e( 'Éléments de liste - Mode Sombre', 'blazing-feedback' ); ?>
			<small style="color: #666; font-weight: normal;">(cartes feedback)</small>
		</h4>
		<div class="wpvfh-color-grid" style="background: #263e4b; padding: 10px; border-radius: 6px;">
			<?php foreach ( $pin_item_dark_colors as $option_name => $label ) :
				$value = get_option( $option_name, $pin_item_dark_defaults[ $option_name ] );
			?>
			<div class="wpvfh-color-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #334a5a; border-radius: 4px;">
				<input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
				<input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 70px; font-family: monospace; font-size: 12px; background: #263e4b; color: #fff; border-color: #3d5564;" maxlength="7">
				<span style="flex: 1; font-size: 13px; color: #fff;"><?php echo esc_html( $label ); ?></span>
				<button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $pin_item_dark_defaults[ $option_name ] ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>" style="background: #4a6572; border-color: #5a7282; color: #fff;">
					<span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
				</button>
			</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Rendre les couleurs footer mode clair
	 *
	 * @since 1.9.0
	 * @return void
	 */
	private static function render_footer_light_colors() {
		$footer_light_colors = array(
			'wpvfh_color_footer_bg'                  => __( 'Fond du footer', 'blazing-feedback' ),
			'wpvfh_color_footer_border'              => __( 'Bordure du footer', 'blazing-feedback' ),
			'wpvfh_color_footer_btn_add_bg'          => __( 'Bouton Nouveau - Fond', 'blazing-feedback' ),
			'wpvfh_color_footer_btn_add_text'        => __( 'Bouton Nouveau - Texte', 'blazing-feedback' ),
			'wpvfh_color_footer_btn_add_hover'       => __( 'Bouton Nouveau - Survol', 'blazing-feedback' ),
			'wpvfh_color_footer_btn_visibility_bg'   => __( 'Bouton Pins - Fond', 'blazing-feedback' ),
			'wpvfh_color_footer_btn_visibility_text' => __( 'Bouton Pins - Texte', 'blazing-feedback' ),
			'wpvfh_color_footer_btn_visibility_hover'=> __( 'Bouton Pins - Survol', 'blazing-feedback' ),
		);
		$footer_light_defaults = array(
			'wpvfh_color_footer_bg'                  => '#f8f9fa',
			'wpvfh_color_footer_border'              => '#e9ecef',
			'wpvfh_color_footer_btn_add_bg'          => '#27ae60',
			'wpvfh_color_footer_btn_add_text'        => '#ffffff',
			'wpvfh_color_footer_btn_add_hover'       => '#219a52',
			'wpvfh_color_footer_btn_visibility_bg'   => '#3498db',
			'wpvfh_color_footer_btn_visibility_text' => '#ffffff',
			'wpvfh_color_footer_btn_visibility_hover'=> '#2980b9',
		);
		?>
		<h4 style="margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 8px;">
			<span style="font-size: 16px;">📋</span>
			<?php esc_html_e( 'Footer - Mode Clair', 'blazing-feedback' ); ?>
			<small style="color: #666; font-weight: normal;">(fond blanc)</small>
		</h4>
		<div class="wpvfh-color-grid">
			<?php foreach ( $footer_light_colors as $option_name => $label ) :
				$value = get_option( $option_name, $footer_light_defaults[ $option_name ] );
			?>
			<div class="wpvfh-color-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #f5f5f5; border-radius: 4px;">
				<input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
				<input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
				<span style="flex: 1; font-size: 13px;"><?php echo esc_html( $label ); ?></span>
				<button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $footer_light_defaults[ $option_name ] ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>">
					<span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
				</button>
			</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Rendre les couleurs footer mode sombre
	 *
	 * @since 1.9.0
	 * @return void
	 */
	private static function render_footer_dark_colors() {
		$footer_dark_colors = array(
			'wpvfh_color_footer_bg_dark'                  => __( 'Fond du footer', 'blazing-feedback' ),
			'wpvfh_color_footer_border_dark'              => __( 'Bordure du footer', 'blazing-feedback' ),
			'wpvfh_color_footer_btn_add_bg_dark'          => __( 'Bouton Nouveau - Fond', 'blazing-feedback' ),
			'wpvfh_color_footer_btn_add_text_dark'        => __( 'Bouton Nouveau - Texte', 'blazing-feedback' ),
			'wpvfh_color_footer_btn_add_hover_dark'       => __( 'Bouton Nouveau - Survol', 'blazing-feedback' ),
			'wpvfh_color_footer_btn_visibility_bg_dark'   => __( 'Bouton Pins - Fond', 'blazing-feedback' ),
			'wpvfh_color_footer_btn_visibility_text_dark' => __( 'Bouton Pins - Texte', 'blazing-feedback' ),
			'wpvfh_color_footer_btn_visibility_hover_dark'=> __( 'Bouton Pins - Survol', 'blazing-feedback' ),
		);
		$footer_dark_defaults = array(
			'wpvfh_color_footer_bg_dark'                  => '#1a2e38',
			'wpvfh_color_footer_border_dark'              => '#3d5564',
			'wpvfh_color_footer_btn_add_bg_dark'          => '#27ae60',
			'wpvfh_color_footer_btn_add_text_dark'        => '#ffffff',
			'wpvfh_color_footer_btn_add_hover_dark'       => '#219a52',
			'wpvfh_color_footer_btn_visibility_bg_dark'   => '#3498db',
			'wpvfh_color_footer_btn_visibility_text_dark' => '#ffffff',
			'wpvfh_color_footer_btn_visibility_hover_dark'=> '#2980b9',
		);
		?>
		<h4 style="margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 8px;">
			<span style="font-size: 16px;">📋</span>
			<?php esc_html_e( 'Footer - Mode Sombre', 'blazing-feedback' ); ?>
			<small style="color: #666; font-weight: normal;">(fond #263E4B)</small>
		</h4>
		<div class="wpvfh-color-grid" style="background: #263e4b; padding: 10px; border-radius: 6px;">
			<?php foreach ( $footer_dark_colors as $option_name => $label ) :
				$value = get_option( $option_name, $footer_dark_defaults[ $option_name ] );
			?>
			<div class="wpvfh-color-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #334a5a; border-radius: 4px;">
				<input type="color" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
				<input type="text" value="<?php echo esc_attr( $value ); ?>" class="wpvfh-color-hex-input" data-color-input="<?php echo esc_attr( $option_name ); ?>" style="width: 70px; font-family: monospace; font-size: 12px; background: #263e4b; color: #fff; border-color: #3d5564;" maxlength="7">
				<span style="flex: 1; font-size: 13px; color: #fff;"><?php echo esc_html( $label ); ?></span>
				<button type="button" class="button button-small wpvfh-reset-color" data-option="<?php echo esc_attr( $option_name ); ?>" data-default="<?php echo esc_attr( $footer_dark_defaults[ $option_name ] ); ?>" title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>" style="background: #4a6572; border-color: #5a7282; color: #fff;">
					<span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
				</button>
			</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
