<?php
/**
 * Template: Formulaire projet
 *
 * @package Blazing_Minds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_edit = ! empty( $project->id );
$title = $is_edit ? __( 'Modifier le projet', 'blazing-feedback' ) : __( 'Nouveau projet', 'blazing-feedback' );
?>
<div class="wrap">
	<h1><?php echo esc_html( $title ); ?></h1>

	<form method="post" class="bzmi-form">
		<?php wp_nonce_field( 'save_project' ); ?>
		<input type="hidden" name="action" value="save">
		<?php if ( $is_edit ) : ?>
			<input type="hidden" name="id" value="<?php echo intval( $project->id ); ?>">
		<?php endif; ?>

		<table class="form-table">
			<tr>
				<th><label for="name"><?php esc_html_e( 'Nom', 'blazing-feedback' ); ?> *</label></th>
				<td>
					<input type="text" name="name" id="name" class="regular-text" required
						   value="<?php echo esc_attr( $project->name ?? '' ); ?>">
				</td>
			</tr>
			<tr>
				<th><label for="client_id"><?php esc_html_e( 'Client', 'blazing-feedback' ); ?></label></th>
				<td>
					<select name="client_id" id="client_id">
						<option value=""><?php esc_html_e( '-- Sans client --', 'blazing-feedback' ); ?></option>
						<?php foreach ( $clients as $client ) : ?>
							<option value="<?php echo intval( $client->id ); ?>" <?php selected( $project->client_id ?? '', $client->id ); ?>>
								<?php echo esc_html( $client->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="portfolio_id"><?php esc_html_e( 'Portefeuille', 'blazing-feedback' ); ?></label></th>
				<td>
					<select name="portfolio_id" id="portfolio_id">
						<option value=""><?php esc_html_e( '-- Sans portefeuille --', 'blazing-feedback' ); ?></option>
						<?php foreach ( $portfolios as $portfolio ) : ?>
							<option value="<?php echo intval( $portfolio->id ); ?>" <?php selected( $project->portfolio_id ?? '', $portfolio->id ); ?>>
								<?php echo esc_html( $portfolio->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="description"><?php esc_html_e( 'Description', 'blazing-feedback' ); ?></label></th>
				<td>
					<textarea name="description" id="description" rows="4" class="large-text"><?php echo esc_textarea( $project->description ?? '' ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th><label for="url"><?php esc_html_e( 'URL du projet', 'blazing-feedback' ); ?></label></th>
				<td>
					<input type="url" name="url" id="url" class="regular-text"
						   value="<?php echo esc_url( $project->url ?? '' ); ?>">
				</td>
			</tr>
			<tr>
				<th><label for="status"><?php esc_html_e( 'Statut', 'blazing-feedback' ); ?></label></th>
				<td>
					<select name="status" id="status">
						<option value="new" <?php selected( $project->status ?? 'new', 'new' ); ?>><?php esc_html_e( 'Nouveau', 'blazing-feedback' ); ?></option>
						<option value="in_progress" <?php selected( $project->status ?? '', 'in_progress' ); ?>><?php esc_html_e( 'En cours', 'blazing-feedback' ); ?></option>
						<option value="active" <?php selected( $project->status ?? '', 'active' ); ?>><?php esc_html_e( 'Actif', 'blazing-feedback' ); ?></option>
						<option value="completed" <?php selected( $project->status ?? '', 'completed' ); ?>><?php esc_html_e( 'Terminé', 'blazing-feedback' ); ?></option>
						<option value="archived" <?php selected( $project->status ?? '', 'archived' ); ?>><?php esc_html_e( 'Archivé', 'blazing-feedback' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="priority"><?php esc_html_e( 'Priorité', 'blazing-feedback' ); ?></label></th>
				<td>
					<select name="priority" id="priority">
						<option value="low" <?php selected( $project->priority ?? '', 'low' ); ?>><?php esc_html_e( 'Basse', 'blazing-feedback' ); ?></option>
						<option value="normal" <?php selected( $project->priority ?? 'normal', 'normal' ); ?>><?php esc_html_e( 'Normale', 'blazing-feedback' ); ?></option>
						<option value="high" <?php selected( $project->priority ?? '', 'high' ); ?>><?php esc_html_e( 'Haute', 'blazing-feedback' ); ?></option>
						<option value="critical" <?php selected( $project->priority ?? '', 'critical' ); ?>><?php esc_html_e( 'Critique', 'blazing-feedback' ); ?></option>
					</select>
				</td>
			</tr>
		</table>

		<p class="submit">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Enregistrer', 'blazing-feedback' ); ?></button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-projects' ) ); ?>" class="button"><?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?></a>
		</p>
	</form>
</div>
