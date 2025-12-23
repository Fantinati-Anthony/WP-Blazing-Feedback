<?php
/**
 * Template: Client Form
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = admin_url( 'admin.php?page=blazing-minds-clients' );
?>
<div class="wrap">
	<h1>
		<?php echo $is_new ? esc_html__( 'Nouveau client', 'blazing-minds' ) : esc_html__( 'Modifier le client', 'blazing-minds' ); ?>
	</h1>

	<form method="post" action="<?php echo esc_url( $base_url ); ?>" class="bzmi-form">
		<?php wp_nonce_field( 'bzmi_save_client' ); ?>
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="client_id" value="<?php echo esc_attr( $client->id ); ?>">

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="name"><?php esc_html_e( 'Nom', 'blazing-minds' ); ?> <span class="required">*</span></label>
				</th>
				<td>
					<input type="text" id="name" name="name" value="<?php echo esc_attr( $client->name ); ?>" class="regular-text" required>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="email"><?php esc_html_e( 'Email', 'blazing-minds' ); ?></label>
				</th>
				<td>
					<input type="email" id="email" name="email" value="<?php echo esc_attr( $client->email ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="phone"><?php esc_html_e( 'Téléphone', 'blazing-minds' ); ?></label>
				</th>
				<td>
					<input type="text" id="phone" name="phone" value="<?php echo esc_attr( $client->phone ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="company"><?php esc_html_e( 'Entreprise', 'blazing-minds' ); ?></label>
				</th>
				<td>
					<input type="text" id="company" name="company" value="<?php echo esc_attr( $client->company ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="company_mode"><?php esc_html_e( 'Mode entreprise', 'blazing-minds' ); ?></label>
				</th>
				<td>
					<select id="company_mode" name="company_mode">
						<?php foreach ( BZMI_Client::COMPANY_MODES as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $client->company_mode ?: 'existing', $key ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php esc_html_e( 'Création = nouvelle entreprise à créer, Existante = entreprise déjà existante', 'blazing-minds' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="website"><?php esc_html_e( 'Site web', 'blazing-minds' ); ?></label>
				</th>
				<td>
					<input type="url" id="website" name="website" value="<?php echo esc_attr( $client->website ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="address"><?php esc_html_e( 'Adresse', 'blazing-minds' ); ?></label>
				</th>
				<td>
					<textarea id="address" name="address" rows="3" class="large-text"><?php echo esc_textarea( $client->address ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="status"><?php esc_html_e( 'Statut', 'blazing-minds' ); ?></label>
				</th>
				<td>
					<select id="status" name="status">
						<?php foreach ( $statuses as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $client->status ?: 'active', $key ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="notes"><?php esc_html_e( 'Notes', 'blazing-minds' ); ?></label>
				</th>
				<td>
					<textarea id="notes" name="notes" rows="5" class="large-text"><?php echo esc_textarea( $client->notes ); ?></textarea>
				</td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" class="button button-primary" value="<?php echo $is_new ? esc_attr__( 'Créer le client', 'blazing-minds' ) : esc_attr__( 'Mettre à jour', 'blazing-minds' ); ?>">
			<a href="<?php echo esc_url( $base_url ); ?>" class="button"><?php esc_html_e( 'Annuler', 'blazing-minds' ); ?></a>
		</p>
	</form>
</div>
