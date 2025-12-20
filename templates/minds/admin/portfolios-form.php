<?php
/**
 * Template: Formulaire portefeuille
 *
 * @package Blazing_Minds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_edit = ! empty( $portfolio->id );
$title = $is_edit ? __( 'Modifier le portefeuille', 'blazing-feedback' ) : __( 'Nouveau portefeuille', 'blazing-feedback' );
?>
<div class="wrap">
	<h1><?php echo esc_html( $title ); ?></h1>

	<form method="post" class="bzmi-form">
		<?php wp_nonce_field( 'save_portfolio' ); ?>
		<input type="hidden" name="action" value="save">
		<?php if ( $is_edit ) : ?>
			<input type="hidden" name="id" value="<?php echo intval( $portfolio->id ); ?>">
		<?php endif; ?>

		<table class="form-table">
			<tr>
				<th><label for="name"><?php esc_html_e( 'Nom', 'blazing-feedback' ); ?> *</label></th>
				<td>
					<input type="text" name="name" id="name" class="regular-text" required
						   value="<?php echo esc_attr( $portfolio->name ?? '' ); ?>">
				</td>
			</tr>
			<tr>
				<th><label for="client_id"><?php esc_html_e( 'Client', 'blazing-feedback' ); ?></label></th>
				<td>
					<select name="client_id" id="client_id">
						<option value=""><?php esc_html_e( '-- Sans client --', 'blazing-feedback' ); ?></option>
						<?php foreach ( $clients as $client ) : ?>
							<option value="<?php echo intval( $client->id ); ?>" <?php selected( $portfolio->client_id ?? '', $client->id ); ?>>
								<?php echo esc_html( $client->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="description"><?php esc_html_e( 'Description', 'blazing-feedback' ); ?></label></th>
				<td>
					<textarea name="description" id="description" rows="4" class="large-text"><?php echo esc_textarea( $portfolio->description ?? '' ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th><label for="status"><?php esc_html_e( 'Statut', 'blazing-feedback' ); ?></label></th>
				<td>
					<select name="status" id="status">
						<option value="active" <?php selected( $portfolio->status ?? 'active', 'active' ); ?>><?php esc_html_e( 'Actif', 'blazing-feedback' ); ?></option>
						<option value="inactive" <?php selected( $portfolio->status ?? '', 'inactive' ); ?>><?php esc_html_e( 'Inactif', 'blazing-feedback' ); ?></option>
						<option value="archived" <?php selected( $portfolio->status ?? '', 'archived' ); ?>><?php esc_html_e( 'Archivé', 'blazing-feedback' ); ?></option>
					</select>
				</td>
			</tr>
		</table>

		<p class="submit">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Enregistrer', 'blazing-feedback' ); ?></button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-portfolios' ) ); ?>" class="button"><?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?></a>
		</p>
	</form>
</div>
