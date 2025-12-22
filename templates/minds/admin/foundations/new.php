<?php
/**
 * Template: Nouvelle Fondation
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 *
 * @var array $clients_without_foundation Clients sans fondation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap bzmi-foundations-wrap">
	<div class="bzmi-page-header">
		<div class="bzmi-page-header__back">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-foundations' ) ); ?>" class="bzmi-back-link">
				<span class="dashicons dashicons-arrow-left-alt"></span>
				<?php esc_html_e( 'Retour aux fondations', 'blazing-feedback' ); ?>
			</a>
		</div>
		<div class="bzmi-page-header__content">
			<h1 class="bzmi-page-title">
				<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'Nouvelle fondation', 'blazing-feedback' ); ?>
			</h1>
			<p class="bzmi-page-description">
				<?php esc_html_e( 'Créez une fondation de marque pour un client. Chaque client ne peut avoir qu\'une seule fondation.', 'blazing-feedback' ); ?>
			</p>
		</div>
	</div>

	<?php if ( empty( $clients_without_foundation ) ) : ?>
		<div class="bzmi-empty-state">
			<div class="bzmi-empty-state__icon">
				<span class="dashicons dashicons-warning"></span>
			</div>
			<h2><?php esc_html_e( 'Aucun client disponible', 'blazing-feedback' ); ?></h2>
			<p><?php esc_html_e( 'Tous vos clients ont déjà une fondation, ou vous n\'avez pas encore créé de client.', 'blazing-feedback' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds&action=new_client' ) ); ?>" class="button button-primary button-hero">
				<?php esc_html_e( 'Créer un client', 'blazing-feedback' ); ?>
			</a>
		</div>
	<?php else : ?>
		<div class="bzmi-card bzmi-card--form">
			<form id="bzmi-new-foundation-form" class="bzmi-form">
				<?php wp_nonce_field( 'bzmi_nonce', 'bzmi_nonce' ); ?>

				<div class="bzmi-form-section">
					<h2 class="bzmi-form-section__title">
						<?php esc_html_e( 'Sélectionner un client', 'blazing-feedback' ); ?>
					</h2>

					<div class="bzmi-client-select-grid">
						<?php foreach ( $clients_without_foundation as $client ) :
							$mode = $client->company_mode ?? 'existing';
						?>
							<label class="bzmi-client-option">
								<input type="radio" name="client_id" value="<?php echo esc_attr( $client->id ); ?>" required>
								<div class="bzmi-client-option__content">
									<div class="bzmi-client-option__avatar">
										<?php echo esc_html( strtoupper( substr( $client->name, 0, 2 ) ) ); ?>
									</div>
									<div class="bzmi-client-option__info">
										<span class="bzmi-client-option__name"><?php echo esc_html( $client->name ); ?></span>
										<?php if ( $client->company ) : ?>
											<span class="bzmi-client-option__company"><?php echo esc_html( $client->company ); ?></span>
										<?php endif; ?>
									</div>
									<span class="bzmi-badge bzmi-badge--<?php echo esc_attr( $mode ); ?> bzmi-badge--small">
										<?php echo 'creation' === $mode ? esc_html__( 'Création', 'blazing-feedback' ) : esc_html__( 'Existante', 'blazing-feedback' ); ?>
									</span>
								</div>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="bzmi-form-section bzmi-form-section--info">
					<div class="bzmi-info-box">
						<span class="dashicons dashicons-info"></span>
						<div class="bzmi-info-box__content">
							<h4><?php esc_html_e( 'Qu\'est-ce qu\'une fondation ?', 'blazing-feedback' ); ?></h4>
							<p><?php esc_html_e( 'Une fondation de marque est un ensemble structuré d\'informations stratégiques qui définit l\'identité, l\'offre, l\'expérience client et le cadre d\'exécution d\'une entreprise.', 'blazing-feedback' ); ?></p>
							<ul>
								<li><strong><?php esc_html_e( 'Identité', 'blazing-feedback' ); ?></strong> - <?php esc_html_e( 'ADN, vision, ton, visuels', 'blazing-feedback' ); ?></li>
								<li><strong><?php esc_html_e( 'Offre & Marché', 'blazing-feedback' ); ?></strong> - <?php esc_html_e( 'Produits, proposition de valeur, concurrence', 'blazing-feedback' ); ?></li>
								<li><strong><?php esc_html_e( 'Expérience', 'blazing-feedback' ); ?></strong> - <?php esc_html_e( 'Parcours clients, canaux, messages', 'blazing-feedback' ); ?></li>
								<li><strong><?php esc_html_e( 'Exécution', 'blazing-feedback' ); ?></strong> - <?php esc_html_e( 'Périmètre, planning, budget, RGPD', 'blazing-feedback' ); ?></li>
							</ul>
						</div>
					</div>
				</div>

				<div class="bzmi-form-actions">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-foundations' ) ); ?>" class="button">
						<?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?>
					</a>
					<button type="submit" class="button button-primary button-hero">
						<span class="dashicons dashicons-plus-alt"></span>
						<?php esc_html_e( 'Créer la fondation', 'blazing-feedback' ); ?>
					</button>
				</div>
			</form>
		</div>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('#bzmi-new-foundation-form').on('submit', function(e) {
		e.preventDefault();

		var $form = $(this);
		var $submit = $form.find('[type="submit"]');
		var clientId = $form.find('[name="client_id"]:checked').val();

		if (!clientId) {
			alert('<?php echo esc_js( __( 'Veuillez sélectionner un client.', 'blazing-feedback' ) ); ?>');
			return;
		}

		$submit.prop('disabled', true).addClass('is-loading');

		wp.apiFetch({
			path: '/blazing-minds/v1/foundations',
			method: 'POST',
			data: { client_id: parseInt(clientId) }
		}).then(function(response) {
			window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=bzmi-foundations&action=edit&id=' ) ); ?>' + response.id;
		}).catch(function(error) {
			alert(error.message || '<?php echo esc_js( __( 'Une erreur est survenue.', 'blazing-feedback' ) ); ?>');
			$submit.prop('disabled', false).removeClass('is-loading');
		});
	});
});
</script>
