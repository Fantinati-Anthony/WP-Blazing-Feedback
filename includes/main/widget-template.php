<?php
/**
 * Template du widget de feedback - Orchestrateur
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Variables globales utilisées par les sous-templates
$button_position = WPVFH_Database::get_setting( 'wpvfh_button_position', 'bottom-right' );
$panel_position = WPVFH_Database::get_setting( 'wpvfh_panel_position', 'right' );

// Chemin vers les templates
$templates_dir = __DIR__ . '/templates/';
?>
<div id="wpvfh-container" class="wpvfh-container" data-position="<?php echo esc_attr( $button_position ); ?>" data-panel-position="<?php echo esc_attr( $panel_position ); ?>" role="complementary" aria-label="<?php esc_attr_e( 'Feedback visuel', 'blazing-feedback' ); ?>">
	<!-- Overlay pour la sidebar -->
	<div id="wpvfh-sidebar-overlay" class="wpvfh-sidebar-overlay"></div>

	<!-- Bouton principal Feedback -->
	<?php include $templates_dir . 'widget-template-button.php'; ?>

	<!-- Sidebar de feedback -->
	<div id="wpvfh-panel" class="wpvfh-panel" data-panel-position="<?php echo esc_attr( $panel_position ); ?>" style="position: fixed;" hidden aria-hidden="true">
		<?php
		// Header et onglets
		include $templates_dir . 'widget-template-panel-header.php';
		?>

		<div class="wpvfh-panel-body">
			<?php
			// Onglet: Nouveau feedback
			include $templates_dir . 'widget-template-tab-new.php';

			// Onglet: Liste des feedbacks
			include $templates_dir . 'widget-template-tab-list.php';

			// Onglet: Pages
			include $templates_dir . 'widget-template-tab-pages.php';

			// Onglet: Métadonnées
			include $templates_dir . 'widget-template-tab-metadata.php';

			// Onglet: Détails
			include $templates_dir . 'widget-template-tab-details.php';
			?>
		</div>

		<?php
		// Footer
		include $templates_dir . 'widget-template-footer.php';
		?>
	</div>

	<?php
	// Modals et overlays
	include $templates_dir . 'widget-template-modals.php';
	?>
</div>
<?php
