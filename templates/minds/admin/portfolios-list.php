<?php
/**
 * Template: Liste des portefeuilles
 *
 * @package Blazing_Minds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Portefeuilles', 'blazing-feedback' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-portfolios&action=new' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Ajouter', 'blazing-feedback' ); ?>
	</a>
	<hr class="wp-header-end">

	<?php BZMI_Admin::display_messages(); ?>

	<?php if ( empty( $portfolios ) ) : ?>
		<div class="bzmi-empty-state">
			<span class="dashicons dashicons-portfolio"></span>
			<h3><?php esc_html_e( 'Aucun portefeuille', 'blazing-feedback' ); ?></h3>
			<p><?php esc_html_e( 'Commencez par créer votre premier portefeuille de projets.', 'blazing-feedback' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-portfolios&action=new' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Créer un portefeuille', 'blazing-feedback' ); ?>
			</a>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped bzmi-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Nom', 'blazing-feedback' ); ?></th>
					<th><?php esc_html_e( 'Client', 'blazing-feedback' ); ?></th>
					<th><?php esc_html_e( 'Projets', 'blazing-feedback' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'blazing-feedback' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'blazing-feedback' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $portfolios as $portfolio ) : ?>
					<?php $client = $portfolio->client(); ?>
					<tr>
						<td>
							<strong>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-portfolios&action=view&id=' . $portfolio->id ) ); ?>">
									<?php echo esc_html( $portfolio->name ); ?>
								</a>
							</strong>
						</td>
						<td>
							<?php if ( $client ) : ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-clients&action=view&id=' . $client->id ) ); ?>">
									<?php echo esc_html( $client->name ); ?>
								</a>
							<?php else : ?>
								<em><?php esc_html_e( 'Sans client', 'blazing-feedback' ); ?></em>
							<?php endif; ?>
						</td>
						<td><?php echo intval( $portfolio->projects_count() ); ?></td>
						<td>
							<span class="bzmi-status <?php echo esc_attr( $portfolio->status ); ?>">
								<?php echo esc_html( ucfirst( $portfolio->status ) ); ?>
							</span>
						</td>
						<td class="actions">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-portfolios&action=edit&id=' . $portfolio->id ) ); ?>">
								<?php esc_html_e( 'Modifier', 'blazing-feedback' ); ?>
							</a>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=blazing-minds-portfolios&action=delete&id=' . $portfolio->id ), 'delete_portfolio_' . $portfolio->id ) ); ?>"
							   class="delete"
							   onclick="return confirm('<?php esc_attr_e( 'Supprimer ce portefeuille ?', 'blazing-feedback' ); ?>');">
								<?php esc_html_e( 'Supprimer', 'blazing-feedback' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
