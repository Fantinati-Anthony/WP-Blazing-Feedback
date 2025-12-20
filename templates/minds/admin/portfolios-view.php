<?php
/**
 * Template: Vue portefeuille
 *
 * @package Blazing_Minds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$client = $portfolio->client();
?>
<div class="wrap">
	<h1>
		<?php echo esc_html( $portfolio->name ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-portfolios&action=edit&id=' . $portfolio->id ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Modifier', 'blazing-feedback' ); ?>
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
							<em><?php esc_html_e( 'Sans client', 'blazing-feedback' ); ?></em>
						<?php endif; ?>
					</div>
				</div>
				<div class="bzmi-detail-item">
					<label><?php esc_html_e( 'Statut', 'blazing-feedback' ); ?></label>
					<div class="value">
						<span class="bzmi-status <?php echo esc_attr( $portfolio->status ); ?>">
							<?php echo esc_html( ucfirst( $portfolio->status ) ); ?>
						</span>
					</div>
				</div>
				<div class="bzmi-detail-item">
					<label><?php esc_html_e( 'Projets', 'blazing-feedback' ); ?></label>
					<div class="value"><?php echo intval( $portfolio->projects_count() ); ?></div>
				</div>
			</div>
			<?php if ( ! empty( $portfolio->description ) ) : ?>
				<div style="margin-top: 20px;">
					<label><?php esc_html_e( 'Description', 'blazing-feedback' ); ?></label>
					<p><?php echo nl2br( esc_html( $portfolio->description ) ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="bzmi-card">
		<div class="bzmi-card-header">
			<h3><?php esc_html_e( 'Projets', 'blazing-feedback' ); ?></h3>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-projects&action=new&portfolio_id=' . $portfolio->id ) ); ?>" class="button button-small">
				<?php esc_html_e( 'Ajouter un projet', 'blazing-feedback' ); ?>
			</a>
		</div>
		<div class="bzmi-card-body">
			<?php $projects = $portfolio->projects(); ?>
			<?php if ( empty( $projects ) ) : ?>
				<p><em><?php esc_html_e( 'Aucun projet dans ce portefeuille.', 'blazing-feedback' ); ?></em></p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Nom', 'blazing-feedback' ); ?></th>
							<th><?php esc_html_e( 'Statut', 'blazing-feedback' ); ?></th>
							<th><?php esc_html_e( 'Priorité', 'blazing-feedback' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $projects as $project ) : ?>
							<tr>
								<td>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-projects&action=view&id=' . $project->id ) ); ?>">
										<?php echo esc_html( $project->name ); ?>
									</a>
								</td>
								<td>
									<span class="bzmi-status <?php echo esc_attr( $project->status ); ?>">
										<?php echo esc_html( ucfirst( $project->status ) ); ?>
									</span>
								</td>
								<td>
									<span class="bzmi-priority <?php echo esc_attr( $project->priority ); ?>">
										<?php echo esc_html( ucfirst( $project->priority ) ); ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>
