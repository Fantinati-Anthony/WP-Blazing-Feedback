<?php
/**
 * Template: Clients List - Card Layout
 *
 * @package Blazing_Minds
 * @since 1.0.0
 * @since 2.1.0 Card-based layout with subsections
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = admin_url( 'admin.php?page=blazing-minds-clients' );
?>
<div class="wrap bzmi-clients-wrap">
	<div class="bzmi-page-header">
		<div class="bzmi-page-header__content">
			<h1 class="bzmi-page-title">
				<span class="dashicons dashicons-groups"></span>
				<?php esc_html_e( 'Clients', 'blazing-minds' ); ?>
			</h1>
			<p class="bzmi-page-description">
				<?php esc_html_e( 'Gérez vos clients et accédez à leurs fondations, portefeuilles et projets.', 'blazing-minds' ); ?>
			</p>
		</div>
		<div class="bzmi-page-header__actions">
			<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base_url ) ); ?>" class="button button-primary">
				<span class="dashicons dashicons-plus-alt2"></span>
				<?php esc_html_e( 'Nouveau client', 'blazing-minds' ); ?>
			</a>
		</div>
	</div>

	<!-- Filtres -->
	<div class="bzmi-filters-bar">
		<form method="get" class="bzmi-filter-form">
			<input type="hidden" name="page" value="blazing-minds-clients">
			<div class="bzmi-filters-row">
				<div class="bzmi-search-field">
					<span class="dashicons dashicons-search"></span>
					<input type="search" name="s" value="<?php echo esc_attr( isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '' ); ?>" placeholder="<?php esc_attr_e( 'Rechercher un client...', 'blazing-minds' ); ?>">
				</div>
				<select name="status" class="bzmi-select">
					<option value=""><?php esc_html_e( 'Tous les statuts', 'blazing-minds' ); ?></option>
					<?php foreach ( $statuses as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '', $key ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="button"><?php esc_html_e( 'Filtrer', 'blazing-minds' ); ?></button>
			</div>
		</form>
		<div class="bzmi-results-count">
			<?php
			printf(
				/* translators: %d: number of clients */
				esc_html( _n( '%d client', '%d clients', $total, 'blazing-minds' ) ),
				esc_html( number_format_i18n( $total ) )
			);
			?>
		</div>
	</div>

	<?php if ( empty( $clients ) ) : ?>
		<div class="bzmi-empty-state">
			<div class="bzmi-empty-state__icon">
				<span class="dashicons dashicons-groups"></span>
			</div>
			<h2><?php esc_html_e( 'Aucun client', 'blazing-minds' ); ?></h2>
			<p><?php esc_html_e( 'Commencez par créer votre premier client pour gérer ses projets et fondations.', 'blazing-minds' ); ?></p>
			<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base_url ) ); ?>" class="button button-primary button-hero">
				<span class="dashicons dashicons-plus-alt2"></span>
				<?php esc_html_e( 'Créer un client', 'blazing-minds' ); ?>
			</a>
		</div>
	<?php else : ?>
		<div class="bzmi-clients-grid">
			<?php foreach ( $clients as $client ) :
				$stats = $client->get_stats();
				$mode = $client->company_mode ?: 'existing';
			?>
				<div class="bzmi-client-card" data-client-id="<?php echo esc_attr( $client->id ); ?>">
					<!-- Header -->
					<div class="bzmi-client-card__header">
						<div class="bzmi-client-card__avatar">
							<?php echo esc_html( strtoupper( substr( $client->name, 0, 2 ) ) ); ?>
						</div>
						<div class="bzmi-client-card__info">
							<h3 class="bzmi-client-card__name">
								<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'view', 'id' => $client->id ), $base_url ) ); ?>">
									<?php echo esc_html( $client->name ); ?>
								</a>
							</h3>
							<?php if ( $client->company ) : ?>
								<span class="bzmi-client-card__company">
									<span class="dashicons dashicons-building"></span>
									<?php echo esc_html( $client->company ); ?>
								</span>
							<?php endif; ?>
						</div>
						<div class="bzmi-client-card__badges">
							<span class="bzmi-badge bzmi-badge--<?php echo esc_attr( $client->status ); ?>">
								<?php echo esc_html( $statuses[ $client->status ] ?? $client->status ); ?>
							</span>
							<span class="bzmi-badge bzmi-badge--<?php echo esc_attr( $mode ); ?> bzmi-badge--small">
								<?php echo esc_html( BZMI_Client::COMPANY_MODES[ $mode ] ?? $mode ); ?>
							</span>
						</div>
					</div>

					<!-- Contact Info -->
					<div class="bzmi-client-card__contact">
						<?php if ( $client->email ) : ?>
							<a href="mailto:<?php echo esc_attr( $client->email ); ?>" class="bzmi-client-card__contact-item">
								<span class="dashicons dashicons-email"></span>
								<?php echo esc_html( $client->email ); ?>
							</a>
						<?php endif; ?>
						<?php if ( $client->phone ) : ?>
							<a href="tel:<?php echo esc_attr( $client->phone ); ?>" class="bzmi-client-card__contact-item">
								<span class="dashicons dashicons-phone"></span>
								<?php echo esc_html( $client->phone ); ?>
							</a>
						<?php endif; ?>
						<?php if ( $client->website ) : ?>
							<a href="<?php echo esc_url( $client->website ); ?>" target="_blank" class="bzmi-client-card__contact-item">
								<span class="dashicons dashicons-admin-site-alt3"></span>
								<?php echo esc_html( wp_parse_url( $client->website, PHP_URL_HOST ) ); ?>
							</a>
						<?php endif; ?>
					</div>

					<!-- Subsections Stats -->
					<div class="bzmi-client-card__sections">
						<!-- Fondations -->
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-foundations&client_id=' . $client->id ) ); ?>" class="bzmi-client-card__section bzmi-client-card__section--identity">
							<div class="bzmi-client-card__section-icon">
								<span class="dashicons dashicons-id"></span>
							</div>
							<div class="bzmi-client-card__section-content">
								<span class="bzmi-client-card__section-count"><?php echo esc_html( $stats['foundations_count'] ); ?></span>
								<span class="bzmi-client-card__section-label"><?php esc_html_e( 'Fondations', 'blazing-minds' ); ?></span>
							</div>
							<?php if ( $stats['production_foundations'] > 0 ) : ?>
								<span class="bzmi-client-card__section-badge bzmi-badge--success">
									<?php echo esc_html( $stats['production_foundations'] ); ?> prod
								</span>
							<?php endif; ?>
						</a>

						<!-- Portefeuilles -->
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-portfolios&client_id=' . $client->id ) ); ?>" class="bzmi-client-card__section bzmi-client-card__section--portfolios">
							<div class="bzmi-client-card__section-icon">
								<span class="dashicons dashicons-portfolio"></span>
							</div>
							<div class="bzmi-client-card__section-content">
								<span class="bzmi-client-card__section-count"><?php echo esc_html( $stats['portfolios_count'] ); ?></span>
								<span class="bzmi-client-card__section-label"><?php esc_html_e( 'Portefeuilles', 'blazing-minds' ); ?></span>
							</div>
						</a>

						<!-- Projets -->
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-projects&client_id=' . $client->id ) ); ?>" class="bzmi-client-card__section bzmi-client-card__section--projects">
							<div class="bzmi-client-card__section-icon">
								<span class="dashicons dashicons-clipboard"></span>
							</div>
							<div class="bzmi-client-card__section-content">
								<span class="bzmi-client-card__section-count"><?php echo esc_html( $stats['projects_count'] ); ?></span>
								<span class="bzmi-client-card__section-label"><?php esc_html_e( 'Projets', 'blazing-minds' ); ?></span>
							</div>
							<?php if ( $stats['active_projects'] > 0 ) : ?>
								<span class="bzmi-client-card__section-badge bzmi-badge--warning">
									<?php echo esc_html( $stats['active_projects'] ); ?> actifs
								</span>
							<?php endif; ?>
						</a>

						<!-- Feedbacks (si disponible) -->
						<?php
						$feedbacks_count = 0;
						if ( post_type_exists( 'feedback' ) ) {
							$feedbacks_count = count( get_posts( array(
								'post_type'      => 'feedback',
								'posts_per_page' => -1,
								'meta_query'     => array(
									array(
										'key'   => '_client_id',
										'value' => $client->id,
									),
								),
								'fields' => 'ids',
							) ) );
						}
						?>
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=feedback&client_id=' . $client->id ) ); ?>" class="bzmi-client-card__section bzmi-client-card__section--feedbacks">
							<div class="bzmi-client-card__section-icon">
								<span class="dashicons dashicons-format-chat"></span>
							</div>
							<div class="bzmi-client-card__section-content">
								<span class="bzmi-client-card__section-count"><?php echo esc_html( $feedbacks_count ); ?></span>
								<span class="bzmi-client-card__section-label"><?php esc_html_e( 'Feedbacks', 'blazing-minds' ); ?></span>
							</div>
						</a>
					</div>

					<!-- Actions -->
					<div class="bzmi-client-card__actions">
						<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'view', 'id' => $client->id ), $base_url ) ); ?>" class="button">
							<span class="dashicons dashicons-visibility"></span>
							<?php esc_html_e( 'Voir', 'blazing-minds' ); ?>
						</a>
						<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'id' => $client->id ), $base_url ) ); ?>" class="button">
							<span class="dashicons dashicons-edit"></span>
							<?php esc_html_e( 'Modifier', 'blazing-minds' ); ?>
						</a>
						<div class="bzmi-client-card__actions-more">
							<button type="button" class="button bzmi-dropdown-toggle">
								<span class="dashicons dashicons-ellipsis"></span>
							</button>
							<div class="bzmi-dropdown-menu">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-foundations&action=new&client_id=' . $client->id ) ); ?>">
									<span class="dashicons dashicons-plus-alt"></span>
									<?php esc_html_e( 'Nouvelle fondation', 'blazing-minds' ); ?>
								</a>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-portfolios&action=new&client_id=' . $client->id ) ); ?>">
									<span class="dashicons dashicons-plus-alt"></span>
									<?php esc_html_e( 'Nouveau portefeuille', 'blazing-minds' ); ?>
								</a>
								<hr>
								<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $client->id ), $base_url ), 'bzmi_delete_client_' . $client->id ) ); ?>" class="bzmi-delete-link">
									<span class="dashicons dashicons-trash"></span>
									<?php esc_html_e( 'Supprimer', 'blazing-minds' ); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>

			<!-- Add New Client Card -->
			<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base_url ) ); ?>" class="bzmi-client-card bzmi-client-card--add">
				<span class="dashicons dashicons-plus-alt2"></span>
				<span><?php esc_html_e( 'Ajouter un client', 'blazing-minds' ); ?></span>
			</a>
		</div>

		<?php echo BZMI_Admin::pagination( $total, $per_page, $current_page, $base_url ); // phpcs:ignore ?>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	// Dropdown toggle
	$('.bzmi-dropdown-toggle').on('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		var $menu = $(this).siblings('.bzmi-dropdown-menu');
		$('.bzmi-dropdown-menu').not($menu).removeClass('is-open');
		$menu.toggleClass('is-open');
	});

	// Close dropdown on outside click
	$(document).on('click', function() {
		$('.bzmi-dropdown-menu').removeClass('is-open');
	});

	// Confirm delete
	$('.bzmi-delete-link').on('click', function(e) {
		if (!confirm('<?php echo esc_js( __( 'Êtes-vous sûr de vouloir supprimer ce client ? Cette action est irréversible.', 'blazing-minds' ) ); ?>')) {
			e.preventDefault();
		}
	});
});
</script>
