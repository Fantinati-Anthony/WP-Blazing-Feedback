<?php
/**
 * Template du bouton flottant
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon_mode = WPVFH_Database::get_setting( 'wpvfh_icon_mode', 'emoji' );
$icon_emoji = WPVFH_Database::get_setting( 'wpvfh_icon_emoji', '💬' );
$icon_image_url = WPVFH_Database::get_setting( 'wpvfh_icon_image_url', '' );
$button_style = WPVFH_Database::get_setting( 'wpvfh_button_style', 'detached' );
// Forme automatique selon la position (angle = quart de cercle, centre = demi-cercle)
$corner_positions = array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' );
$button_shape = in_array( $button_position, $corner_positions, true ) ? 'quarter' : 'half';
$button_size = absint( WPVFH_Database::get_setting( 'wpvfh_button_size', 56 ) );
$button_border_radius = absint( WPVFH_Database::get_setting( 'wpvfh_button_border_radius', 50 ) );
$button_border_radius_unit = WPVFH_Database::get_setting( 'wpvfh_button_border_radius_unit', 'percent' );
$button_margin = absint( WPVFH_Database::get_setting( 'wpvfh_button_margin', 20 ) );
$button_color = WPVFH_Database::get_setting( 'wpvfh_button_color', '#e74c3c' );

// Calculer les styles inline
$btn_styles = array();
$btn_styles[] = 'position: fixed';
$btn_styles[] = 'background-color: ' . esc_attr( $button_color );
$btn_styles[] = '--wpvfh-btn-size: ' . $button_size . 'px';

if ( 'attached' === $button_style ) {
	// Bouton collé au bord
	if ( 'quarter' === $button_shape ) {
		// Quart de cercle pour les angles
		$btn_styles[] = 'width: ' . $button_size . 'px';
		$btn_styles[] = 'height: ' . $button_size . 'px';

		switch ( $button_position ) {
			case 'bottom-right':
				$btn_styles[] = 'border-radius: ' . $button_size . 'px 0 0 0';
				$btn_styles[] = 'bottom: 0';
				$btn_styles[] = 'right: 0';
				break;
			case 'bottom-left':
				$btn_styles[] = 'border-radius: 0 ' . $button_size . 'px 0 0';
				$btn_styles[] = 'bottom: 0';
				$btn_styles[] = 'left: 0';
				break;
			case 'top-right':
				$btn_styles[] = 'border-radius: 0 0 0 ' . $button_size . 'px';
				$btn_styles[] = 'top: 0';
				$btn_styles[] = 'right: 0';
				break;
			case 'top-left':
				$btn_styles[] = 'border-radius: 0 0 ' . $button_size . 'px 0';
				$btn_styles[] = 'top: 0';
				$btn_styles[] = 'left: 0';
				break;
		}
	} else {
		// Demi-cercle pour les positions centrales
		$half_size = $button_size / 2;

		switch ( $button_position ) {
			case 'bottom-center':
				$btn_styles[] = 'width: ' . $button_size . 'px';
				$btn_styles[] = 'height: ' . $half_size . 'px';
				$btn_styles[] = 'border-radius: ' . $button_size . 'px ' . $button_size . 'px 0 0';
				$btn_styles[] = 'bottom: 0';
				$btn_styles[] = 'left: 50%';
				$btn_styles[] = 'transform: translateX(-50%)';
				break;
			case 'top-center':
				$btn_styles[] = 'width: ' . $button_size . 'px';
				$btn_styles[] = 'height: ' . $half_size . 'px';
				$btn_styles[] = 'border-radius: 0 0 ' . $button_size . 'px ' . $button_size . 'px';
				$btn_styles[] = 'top: 0';
				$btn_styles[] = 'left: 50%';
				$btn_styles[] = 'transform: translateX(-50%)';
				break;
			case 'middle-left':
				$btn_styles[] = 'width: ' . $half_size . 'px';
				$btn_styles[] = 'height: ' . $button_size . 'px';
				$btn_styles[] = 'border-radius: 0 ' . $button_size . 'px ' . $button_size . 'px 0';
				$btn_styles[] = 'top: 50%';
				$btn_styles[] = 'left: 0';
				$btn_styles[] = 'transform: translateY(-50%)';
				break;
			case 'middle-right':
				$btn_styles[] = 'width: ' . $half_size . 'px';
				$btn_styles[] = 'height: ' . $button_size . 'px';
				$btn_styles[] = 'border-radius: ' . $button_size . 'px 0 0 ' . $button_size . 'px';
				$btn_styles[] = 'top: 50%';
				$btn_styles[] = 'right: 0';
				$btn_styles[] = 'transform: translateY(-50%)';
				break;
		}
	}
} else {
	// Bouton séparé
	$radius_unit = ( 'percent' === $button_border_radius_unit ) ? '%' : 'px';
	$btn_styles[] = 'width: ' . $button_size . 'px';
	$btn_styles[] = 'height: ' . $button_size . 'px';
	$btn_styles[] = 'border-radius: ' . $button_border_radius . $radius_unit;
	$btn_styles[] = 'box-shadow: 0 4px 12px rgba(0,0,0,0.15)';

	switch ( $button_position ) {
		case 'bottom-right':
			$btn_styles[] = 'bottom: ' . $button_margin . 'px';
			$btn_styles[] = 'right: ' . $button_margin . 'px';
			break;
		case 'bottom-left':
			$btn_styles[] = 'bottom: ' . $button_margin . 'px';
			$btn_styles[] = 'left: ' . $button_margin . 'px';
			break;
		case 'top-right':
			$btn_styles[] = 'top: ' . $button_margin . 'px';
			$btn_styles[] = 'right: ' . $button_margin . 'px';
			break;
		case 'top-left':
			$btn_styles[] = 'top: ' . $button_margin . 'px';
			$btn_styles[] = 'left: ' . $button_margin . 'px';
			break;
		case 'bottom-center':
			$btn_styles[] = 'bottom: ' . $button_margin . 'px';
			$btn_styles[] = 'left: 50%';
			$btn_styles[] = 'transform: translateX(-50%)';
			break;
		case 'top-center':
			$btn_styles[] = 'top: ' . $button_margin . 'px';
			$btn_styles[] = 'left: 50%';
			$btn_styles[] = 'transform: translateX(-50%)';
			break;
		case 'middle-left':
			$btn_styles[] = 'top: 50%';
			$btn_styles[] = 'left: ' . $button_margin . 'px';
			$btn_styles[] = 'transform: translateY(-50%)';
			break;
		case 'middle-right':
			$btn_styles[] = 'top: 50%';
			$btn_styles[] = 'right: ' . $button_margin . 'px';
			$btn_styles[] = 'transform: translateY(-50%)';
			break;
	}
}

$btn_class = 'wpvfh-corner-btn';
$btn_class .= ' wpvfh-btn-' . esc_attr( $button_style );
if ( 'attached' === $button_style ) {
	$btn_class .= ' wpvfh-btn-' . esc_attr( $button_shape );
}
?>
<button
	type="button"
	id="wpvfh-toggle-btn"
	class="<?php echo esc_attr( $btn_class ); ?>"
	data-position="<?php echo esc_attr( $button_position ); ?>"
	data-style="<?php echo esc_attr( $button_style ); ?>"
	aria-expanded="false"
	aria-controls="wpvfh-panel"
	title="<?php esc_attr_e( 'Voir les feedbacks', 'blazing-feedback' ); ?>"
	style="<?php echo esc_attr( implode( '; ', $btn_styles ) ); ?>"
>
	<span class="wpvfh-corner-icon-wrapper">
		<span class="wpvfh-corner-icon" aria-hidden="true">
			<?php if ( $icon_mode === 'image' && ! empty( $icon_image_url ) ) : ?>
				<img src="<?php echo esc_url( $icon_image_url ); ?>" alt="">
			<?php else : ?>
				<?php echo esc_html( $icon_emoji ); ?>
			<?php endif; ?>
		</span>
		<span class="wpvfh-corner-count" id="wpvfh-feedback-count" hidden></span>
	</span>
</button>
