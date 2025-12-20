<?php
/**
 * Template: Liste des projets
 *
 * @package Blazing_Minds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Projets', 'blazing-feedback' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-projects&action=new' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Ajouter', 'blazing-feedback' ); ?>
	</a>
	<hr class="wp-header-end">

	<?php BZMI_Admin::display_messages(); ?>

	<?php if ( empty( $projects ) ) : ?>
		<div class="bzmi-empty-state">
			<span class="dashicons dashicons-clipboard"></span>
			<h3><?php esc_html_e( 'Aucun projet', 'blazing-feedback' ); ?></h3>
			<p><?php esc_html_e( 'Commencez par créer votre premier projet.', 'blazing-feedback' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-projects&action=new' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Créer un projet', 'blazing-feedback' ); ?>
			</a>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped bzmi-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Nom', 'blazing-feedback' ); ?></th>
					<th><?php esc_html_e( 'Client', 'blazing-feedback' ); ?></th>
					<th><?php esc_html_e( 'Portefeuille', 'blazing-feedback' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'blazing-feedback' ); ?></th>
					<th><?php esc_html_e( 'Priorité', 'blazing-feedback' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'blazing-feedback' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $projects as $project ) : ?>
					<?php
					$portfolio = $project->portfolio();
					$client = $project->client();
					?>
					<tr>
						<td>
							<strong>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-projects&action=view&id=' . $project->id ) ); ?>">
									<?php echo esc_html( $project->name ); ?>
								</a>
							</strong>
						</td>
						<td>
							<?php if ( $client ) : ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-clients&action=view&id=' . $client->id ) ); ?>">
									<?php echo esc_html( $client->name ); ?>
								</a>
							<?php else : ?>
								<em>-</em>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $portfolio ) : ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-portfolios&action=view&id=' . $portfolio->id ) ); ?>">
									<?php echo esc_html( $portfolio->name ); ?>
								</a>
							<?php else : ?>
								<em>-</em>
							<?php endif; ?>
						</td>
						<td>
							<span class="bzmi-status <?php echo esc_attr( $project->status ); ?>">
								<?php echo esc_html( ucfirst( str_replace( '_', ' ', $project->status ) ) ); ?>
							</span>
						</td>
						<td>
							<span class="bzmi-priority <?php echo esc_attr( $project->priority ); ?>">
								<?php echo esc_html( ucfirst( $project->priority ) ); ?>
							</span>
						</td>
						<td class="actions">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-icaval&project_id=' . $project->id ) ); ?>">
								<?php esc_html_e( 'ICAVAL', 'blazing-feedback' ); ?>
							</a> |
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-projects&action=edit&id=' . $project->id ) ); ?>">
								<?php esc_html_e( 'Modifier', 'blazing-feedback' ); ?>
							</a> |
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=blazing-minds-projects&action=delete&id=' . $project->id ), 'delete_project_' . $project->id ) ); ?>"
							   class="delete"
							   onclick="return confirm('<?php esc_attr_e( 'Supprimer ce projet ?', 'blazing-feedback' ); ?>');">
								<?php esc_html_e( 'Supprimer', 'blazing-feedback' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
