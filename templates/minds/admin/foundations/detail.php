<?php
/**
 * Template: Détail d'une Fondation
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 *
 * @var BZMI_Foundation $foundation La fondation
 * @var BZMI_Client     $client     Le client
 * @var string          $tab        Onglet actif
 * @var array           $data       Données de l'onglet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tabs = BZMI_Admin_Foundations::get_tabs();
$mode = $client ? $client->company_mode : 'existing';
?>
<div class="wrap bzmi-foundations-wrap bzmi-foundation-detail">
	<!-- Header -->
	<div class="bzmi-page-header bzmi-page-header--detail">
		<div class="bzmi-page-header__back">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-foundations' ) ); ?>" class="bzmi-back-link">
				<span class="dashicons dashicons-arrow-left-alt"></span>
				<?php esc_html_e( 'Retour aux fondations', 'blazing-feedback' ); ?>
			</a>
		</div>
		<div class="bzmi-page-header__content">
			<div class="bzmi-page-header__title-row">
				<h1 class="bzmi-page-title">
					<span class="dashicons dashicons-building"></span>
					<?php echo esc_html( $client ? $client->name : __( 'Fondation', 'blazing-feedback' ) ); ?>
				</h1>
				<span class="bzmi-badge bzmi-badge--<?php echo esc_attr( $mode ); ?> bzmi-badge--large">
					<?php echo 'creation' === $mode ? esc_html__( 'Mode Création', 'blazing-feedback' ) : esc_html__( 'Mode Existante', 'blazing-feedback' ); ?>
				</span>
				<span class="bzmi-badge bzmi-badge--status-<?php echo esc_attr( $foundation->status ); ?>">
					<?php echo esc_html( BZMI_Foundation::STATUSES[ $foundation->status ] ?? $foundation->status ); ?>
				</span>
			</div>
			<div class="bzmi-page-header__meta">
				<span class="bzmi-meta-item">
					<span class="dashicons dashicons-chart-pie"></span>
					<?php
					/* translators: %d: completion score */
					printf( esc_html__( 'Complétion : %d%%', 'blazing-feedback' ), esc_html( $foundation->completion_score ) );
					?>
				</span>
			</div>
		</div>
		<div class="bzmi-page-header__actions">
			<button type="button" class="button bzmi-btn-ai" data-action="audit">
				<span class="dashicons dashicons-superhero"></span>
				<?php esc_html_e( 'Audit IA', 'blazing-feedback' ); ?>
			</button>
			<button type="button" class="button button-primary bzmi-btn-save-all">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Enregistrer', 'blazing-feedback' ); ?>
			</button>
		</div>
	</div>

	<!-- Score Cards -->
	<div class="bzmi-score-cards">
		<?php foreach ( $tabs as $tab_key => $tab_info ) :
			if ( 'ai' === $tab_key ) continue;
			$score_key = $tab_key . '_score';
			$score = $foundation->$score_key ?? 0;
			$is_active = $tab === $tab_key;
		?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-foundations&action=edit&id=' . $foundation->id . '&tab=' . $tab_key ) ); ?>"
			   class="bzmi-score-card <?php echo $is_active ? 'bzmi-score-card--active' : ''; ?>">
				<div class="bzmi-score-card__icon">
					<span class="dashicons <?php echo esc_attr( $tab_info['icon'] ); ?>"></span>
				</div>
				<div class="bzmi-score-card__content">
					<span class="bzmi-score-card__label"><?php echo esc_html( $tab_info['label'] ); ?></span>
					<div class="bzmi-score-card__bar">
						<div class="bzmi-score-card__fill" style="width: <?php echo esc_attr( $score ); ?>%"></div>
					</div>
					<span class="bzmi-score-card__value"><?php echo esc_html( $score ); ?>%</span>
				</div>
			</a>
		<?php endforeach; ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-foundations&action=edit&id=' . $foundation->id . '&tab=ai' ) ); ?>"
		   class="bzmi-score-card bzmi-score-card--ai <?php echo 'ai' === $tab ? 'bzmi-score-card--active' : ''; ?>">
			<div class="bzmi-score-card__icon">
				<span class="dashicons dashicons-superhero"></span>
			</div>
			<div class="bzmi-score-card__content">
				<span class="bzmi-score-card__label"><?php esc_html_e( 'IA & Insights', 'blazing-feedback' ); ?></span>
			</div>
		</a>
	</div>

	<!-- Content -->
	<div class="bzmi-foundation-content" data-foundation-id="<?php echo esc_attr( $foundation->id ); ?>" data-tab="<?php echo esc_attr( $tab ); ?>">
		<?php
		// Charger le template de l'onglet
		$template_file = WPVFH_PLUGIN_DIR . 'templates/minds/admin/foundations/tabs/' . $tab . '.php';
		if ( file_exists( $template_file ) ) {
			include $template_file;
		} else {
			// Template générique
			?>
			<div class="bzmi-tab-content">
				<div class="bzmi-notice bzmi-notice--info">
					<?php esc_html_e( 'Contenu de cet onglet en cours de développement.', 'blazing-feedback' ); ?>
				</div>
			</div>
			<?php
		}
		?>
	</div>
</div>

<!-- Modal pour les formulaires -->
<div id="bzmi-modal" class="bzmi-modal" style="display: none;">
	<div class="bzmi-modal__backdrop"></div>
	<div class="bzmi-modal__container">
		<div class="bzmi-modal__header">
			<h2 class="bzmi-modal__title"></h2>
			<button type="button" class="bzmi-modal__close">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="bzmi-modal__body"></div>
		<div class="bzmi-modal__footer">
			<button type="button" class="button bzmi-modal__cancel"><?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?></button>
			<button type="button" class="button button-primary bzmi-modal__save"><?php esc_html_e( 'Enregistrer', 'blazing-feedback' ); ?></button>
		</div>
	</div>
</div>

<!-- Sidebar IA -->
<div id="bzmi-ai-sidebar" class="bzmi-ai-sidebar" style="display: none;">
	<div class="bzmi-ai-sidebar__header">
		<h3>
			<span class="dashicons dashicons-superhero"></span>
			<?php esc_html_e( 'Suggestion IA', 'blazing-feedback' ); ?>
		</h3>
		<button type="button" class="bzmi-ai-sidebar__close">
			<span class="dashicons dashicons-no-alt"></span>
		</button>
	</div>
	<div class="bzmi-ai-sidebar__content"></div>
	<div class="bzmi-ai-sidebar__actions">
		<button type="button" class="button bzmi-ai-sidebar__dismiss"><?php esc_html_e( 'Ignorer', 'blazing-feedback' ); ?></button>
		<button type="button" class="button button-primary bzmi-ai-sidebar__apply"><?php esc_html_e( 'Appliquer', 'blazing-feedback' ); ?></button>
	</div>
</div>
