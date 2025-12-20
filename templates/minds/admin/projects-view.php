<?php
/**
 * Template: Vue projet
 *
 * @package Blazing_Minds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$client = $project->client();
$portfolio = $project->portfolio();
?>
<div class="wrap">
	<h1>
		<?php echo esc_html( $project->name ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-projects&action=edit&id=' . $project->id ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Modifier', 'blazing-feedback' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-icaval&project_id=' . $project->id ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Cycle ICAVAL', 'blazing-feedback' ); ?>
		</a>
	</h1>

	<div class="bzmi-card">
		<div class="bzmi-card-header">
			<h3><?php esc_html_e( 'Informations', 'blazing-feedback' ); ?></h3>
		</div>
		<div class="bzmi-card-body">
			<div class="bzmi-details-grid">
				<div class="bzmi-detail-item">
					<label><?php esc_html_e( 'Client', 'blazing-feedback' ); ?></label>
					<div class="value">
						<?php if ( $client ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-clients&action=view&id=' . $client->id ) ); ?>">
								<?php echo esc_html( $client->name ); ?>
							</a>
						<?php else : ?>
							<em>-</em>
						<?php endif; ?>
					</div>
				</div>
				<div class="bzmi-detail-item">
					<label><?php esc_html_e( 'Portefeuille', 'blazing-feedback' ); ?></label>
					<div class="value">
						<?php if ( $portfolio ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-portfolios&action=view&id=' . $portfolio->id ) ); ?>">
								<?php echo esc_html( $portfolio->name ); ?>
							</a>
						<?php else : ?>
							<em>-</em>
						<?php endif; ?>
					</div>
				</div>
				<div class="bzmi-detail-item">
					<label><?php esc_html_e( 'Statut', 'blazing-feedback' ); ?></label>
					<div class="value">
						<span class="bzmi-status <?php echo esc_attr( $project->status ); ?>">
							<?php echo esc_html( ucfirst( str_replace( '_', ' ', $project->status ) ) ); ?>
						</span>
					</div>
				</div>
				<div class="bzmi-detail-item">
					<label><?php esc_html_e( 'Priorité', 'blazing-feedback' ); ?></label>
					<div class="value">
						<span class="bzmi-priority <?php echo esc_attr( $project->priority ); ?>">
							<?php echo esc_html( ucfirst( $project->priority ) ); ?>
						</span>
					</div>
				</div>
				<?php if ( ! empty( $project->url ) ) : ?>
				<div class="bzmi-detail-item">
					<label><?php esc_html_e( 'URL', 'blazing-feedback' ); ?></label>
					<div class="value">
						<a href="<?php echo esc_url( $project->url ); ?>" target="_blank"><?php echo esc_html( $project->url ); ?></a>
					</div>
				</div>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $project->description ) ) : ?>
				<div style="margin-top: 20px;">
					<label><?php esc_html_e( 'Description', 'blazing-feedback' ); ?></label>
					<p><?php echo nl2br( esc_html( $project->description ) ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- ICAVAL Progress -->
	<div class="bzmi-card">
		<div class="bzmi-card-header">
			<h3><?php esc_html_e( 'Progression ICAVAL', 'blazing-feedback' ); ?></h3>
		</div>
		<div class="bzmi-card-body">
			<div class="bzmi-workflow-diagram">
				<div class="bzmi-stage">
					<span>I</span>
					<small><?php esc_html_e( 'Information', 'blazing-feedback' ); ?></small>
				</div>
				<span class="bzmi-arrow">→</span>
				<div class="bzmi-stage">
					<span>C</span>
					<small><?php esc_html_e( 'Clarification', 'blazing-feedback' ); ?></small>
				</div>
				<span class="bzmi-arrow">→</span>
				<div class="bzmi-stage">
					<span>A</span>
					<small><?php esc_html_e( 'Action', 'blazing-feedback' ); ?></small>
				</div>
				<span class="bzmi-arrow">→</span>
				<div class="bzmi-stage">
					<span>V</span>
					<small><?php esc_html_e( 'Valeur', 'blazing-feedback' ); ?></small>
				</div>
				<span class="bzmi-arrow">→</span>
				<div class="bzmi-stage">
					<span>AL</span>
					<small><?php esc_html_e( 'Apprentissage', 'blazing-feedback' ); ?></small>
				</div>
			</div>
			<p style="text-align: center; margin-top: 20px;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-icaval&project_id=' . $project->id ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Accéder au cycle ICAVAL', 'blazing-feedback' ); ?>
				</a>
			</p>
		</div>
	</div>
</div>
