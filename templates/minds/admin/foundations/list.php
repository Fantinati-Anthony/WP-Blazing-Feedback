<?php
/**
 * Template: Liste des Fondations
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 *
 * @var array $foundations Liste des fondations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stats = BZMI_Admin_Foundations::get_stats();
?>
<div class="wrap bzmi-foundations-wrap">
	<div class="bzmi-page-header">
		<div class="bzmi-page-header__content">
			<h1 class="bzmi-page-title">
				<span class="dashicons dashicons-flag"></span>
				<?php esc_html_e( 'Fondations de marque', 'blazing-feedback' ); ?>
			</h1>
			<p class="bzmi-page-description">
				<?php esc_html_e( 'Gérez les fondations stratégiques de vos clients : identité, offre, expérience et exécution.', 'blazing-feedback' ); ?>
			</p>
		</div>
		<div class="bzmi-page-header__actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-foundations&action=new' ) ); ?>" class="button button-primary">
				<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'Nouvelle fondation', 'blazing-feedback' ); ?>
			</a>
		</div>
	</div>

	<!-- Stats Cards -->
	<div class="bzmi-stats-grid">
		<div class="bzmi-stat-card">
			<div class="bzmi-stat-card__icon bzmi-stat-card__icon--blue">
				<span class="dashicons dashicons-flag"></span>
			</div>
			<div class="bzmi-stat-card__content">
				<span class="bzmi-stat-card__value"><?php echo esc_html( $stats['total'] ); ?></span>
				<span class="bzmi-stat-card__label"><?php esc_html_e( 'Fondations', 'blazing-feedback' ); ?></span>
			</div>
		</div>
		<div class="bzmi-stat-card">
			<div class="bzmi-stat-card__icon bzmi-stat-card__icon--orange">
				<span class="dashicons dashicons-edit"></span>
			</div>
			<div class="bzmi-stat-card__content">
				<span class="bzmi-stat-card__value"><?php echo esc_html( $stats['draft'] ); ?></span>
				<span class="bzmi-stat-card__label"><?php esc_html_e( 'Brouillons', 'blazing-feedback' ); ?></span>
			</div>
		</div>
		<div class="bzmi-stat-card">
			<div class="bzmi-stat-card__icon bzmi-stat-card__icon--green">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<div class="bzmi-stat-card__content">
				<span class="bzmi-stat-card__value"><?php echo esc_html( $stats['active'] ); ?></span>
				<span class="bzmi-stat-card__label"><?php esc_html_e( 'Actives', 'blazing-feedback' ); ?></span>
			</div>
		</div>
		<div class="bzmi-stat-card">
			<div class="bzmi-stat-card__icon bzmi-stat-card__icon--purple">
				<span class="dashicons dashicons-chart-bar"></span>
			</div>
			<div class="bzmi-stat-card__content">
				<span class="bzmi-stat-card__value"><?php echo esc_html( $stats['avg_score'] ); ?>%</span>
				<span class="bzmi-stat-card__label"><?php esc_html_e( 'Score moyen', 'blazing-feedback' ); ?></span>
			</div>
		</div>
	</div>

	<!-- Liste des fondations -->
	<?php if ( empty( $foundations ) ) : ?>
		<div class="bzmi-empty-state">
			<div class="bzmi-empty-state__icon">
				<span class="dashicons dashicons-flag"></span>
			</div>
			<h2><?php esc_html_e( 'Aucune fondation', 'blazing-feedback' ); ?></h2>
			<p><?php esc_html_e( 'Créez votre première fondation de marque pour un client.', 'blazing-feedback' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-foundations&action=new' ) ); ?>" class="button button-primary button-hero">
				<?php esc_html_e( 'Créer une fondation', 'blazing-feedback' ); ?>
			</a>
		</div>
	<?php else : ?>
		<div class="bzmi-foundations-grid">
			<?php foreach ( $foundations as $foundation ) :
				$client = $foundation->get_client();
				$mode = $client ? $client->company_mode : 'existing';
			?>
				<div class="bzmi-foundation-card" data-id="<?php echo esc_attr( $foundation->id ); ?>">
					<div class="bzmi-foundation-card__header">
						<div class="bzmi-foundation-card__client">
							<span class="dashicons dashicons-building"></span>
							<span class="bzmi-foundation-card__client-name">
								<?php echo esc_html( $client ? $client->name : __( 'Client inconnu', 'blazing-feedback' ) ); ?>
							</span>
						</div>
						<span class="bzmi-badge bzmi-badge--<?php echo esc_attr( $mode ); ?>">
							<?php echo 'creation' === $mode ? esc_html__( 'Création', 'blazing-feedback' ) : esc_html__( 'Existante', 'blazing-feedback' ); ?>
						</span>
					</div>

					<div class="bzmi-foundation-card__progress">
						<div class="bzmi-progress-ring" data-value="<?php echo esc_attr( $foundation->completion_score ); ?>">
							<svg viewBox="0 0 36 36">
								<path class="bzmi-progress-ring__bg"
									d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
								<path class="bzmi-progress-ring__fill"
									stroke-dasharray="<?php echo esc_attr( $foundation->completion_score ); ?>, 100"
									d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
							</svg>
							<span class="bzmi-progress-ring__value"><?php echo esc_html( $foundation->completion_score ); ?>%</span>
						</div>
					</div>

					<div class="bzmi-foundation-card__scores">
						<div class="bzmi-score-item" title="<?php esc_attr_e( 'Identité', 'blazing-feedback' ); ?>">
							<span class="bzmi-score-item__icon dashicons dashicons-id"></span>
							<div class="bzmi-score-item__bar">
								<div class="bzmi-score-item__fill" style="width: <?php echo esc_attr( $foundation->identity_score ); ?>%"></div>
							</div>
							<span class="bzmi-score-item__value"><?php echo esc_html( $foundation->identity_score ); ?>%</span>
						</div>
						<div class="bzmi-score-item" title="<?php esc_attr_e( 'Offre', 'blazing-feedback' ); ?>">
							<span class="bzmi-score-item__icon dashicons dashicons-cart"></span>
							<div class="bzmi-score-item__bar">
								<div class="bzmi-score-item__fill" style="width: <?php echo esc_attr( $foundation->offer_score ); ?>%"></div>
							</div>
							<span class="bzmi-score-item__value"><?php echo esc_html( $foundation->offer_score ); ?>%</span>
						</div>
						<div class="bzmi-score-item" title="<?php esc_attr_e( 'Expérience', 'blazing-feedback' ); ?>">
							<span class="bzmi-score-item__icon dashicons dashicons-admin-users"></span>
							<div class="bzmi-score-item__bar">
								<div class="bzmi-score-item__fill" style="width: <?php echo esc_attr( $foundation->experience_score ); ?>%"></div>
							</div>
							<span class="bzmi-score-item__value"><?php echo esc_html( $foundation->experience_score ); ?>%</span>
						</div>
						<div class="bzmi-score-item" title="<?php esc_attr_e( 'Exécution', 'blazing-feedback' ); ?>">
							<span class="bzmi-score-item__icon dashicons dashicons-clipboard"></span>
							<div class="bzmi-score-item__bar">
								<div class="bzmi-score-item__fill" style="width: <?php echo esc_attr( $foundation->execution_score ); ?>%"></div>
							</div>
							<span class="bzmi-score-item__value"><?php echo esc_html( $foundation->execution_score ); ?>%</span>
						</div>
					</div>

					<div class="bzmi-foundation-card__footer">
						<span class="bzmi-foundation-card__date">
							<?php
							/* translators: %s: date */
							printf( esc_html__( 'Mis à jour %s', 'blazing-feedback' ), esc_html( human_time_diff( strtotime( $foundation->updated_at ) ) . ' ' . __( 'ago', 'blazing-feedback' ) ) );
							?>
						</span>
						<div class="bzmi-foundation-card__actions">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-foundations&action=edit&id=' . $foundation->id ) ); ?>"
							   class="button button-small">
								<span class="dashicons dashicons-edit"></span>
								<?php esc_html_e( 'Éditer', 'blazing-feedback' ); ?>
							</a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
