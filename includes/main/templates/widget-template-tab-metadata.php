<?php
/**
 * Template de l'onglet Métadonnées
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Récupérer tous les groupes de métadonnées
$metadata_groups = array();

// Groupes standards
$statuses_settings = WPVFH_Options_Manager::get_group_settings( 'statuses' );
if ( $statuses_settings['enabled'] && $statuses_settings['show_in_sidebar'] && WPVFH_Options_Manager::user_can_access_group( 'statuses' ) ) {
	$metadata_groups['statuses'] = array(
		'slug' => 'statuses',
		'name' => __( 'Statuts', 'blazing-feedback' ),
		'icon' => '📊',
		'items' => WPVFH_Options_Manager::get_statuses(),
	);
}

$types_settings = WPVFH_Options_Manager::get_group_settings( 'types' );
if ( $types_settings['enabled'] && $types_settings['show_in_sidebar'] && WPVFH_Options_Manager::user_can_access_group( 'types' ) ) {
	$metadata_groups['types'] = array(
		'slug' => 'types',
		'name' => __( 'Types', 'blazing-feedback' ),
		'icon' => '🏷️',
		'items' => WPVFH_Options_Manager::get_types(),
	);
}

$priorities_settings = WPVFH_Options_Manager::get_group_settings( 'priorities' );
if ( $priorities_settings['enabled'] && $priorities_settings['show_in_sidebar'] && WPVFH_Options_Manager::user_can_access_group( 'priorities' ) ) {
	$metadata_groups['priorities'] = array(
		'slug' => 'priorities',
		'name' => __( 'Priorités', 'blazing-feedback' ),
		'icon' => '⚡',
		'items' => WPVFH_Options_Manager::get_priorities(),
	);
}

$tags_settings = WPVFH_Options_Manager::get_group_settings( 'tags' );
if ( $tags_settings['enabled'] && $tags_settings['show_in_sidebar'] && WPVFH_Options_Manager::user_can_access_group( 'tags' ) ) {
	$metadata_groups['tags'] = array(
		'slug' => 'tags',
		'name' => __( 'Tags', 'blazing-feedback' ),
		'icon' => '🔖',
		'items' => WPVFH_Options_Manager::get_predefined_tags(),
	);
}

// Groupes personnalisés
$custom_groups = WPVFH_Options_Manager::get_custom_groups();
foreach ( $custom_groups as $slug => $group ) {
	$group_settings = WPVFH_Options_Manager::get_group_settings( $slug );
	if ( $group_settings['enabled'] && $group_settings['show_in_sidebar'] && WPVFH_Options_Manager::user_can_access_group( $slug ) ) {
		$metadata_groups[ $slug ] = array(
			'slug' => $slug,
			'name' => $group['name'],
			'icon' => '📋',
			'items' => WPVFH_Options_Manager::get_custom_group_items( $slug ),
		);
	}
}
?>
<!-- Onglet: Métadatas -->
<div id="wpvfh-tab-metadata" class="wpvfh-tab-content">
	<!-- Sous-onglets pour les groupes de métadonnées -->
	<div class="wpvfh-subtabs" id="wpvfh-metadata-subtabs">
		<?php
		$first = true;
		foreach ( $metadata_groups as $group_slug => $group ) :
		?>
		<button type="button" class="wpvfh-subtab <?php echo $first ? 'active' : ''; ?>" data-subtab="<?php echo esc_attr( $group_slug ); ?>">
			<span class="wpvfh-subtab-icon"><?php echo esc_html( $group['icon'] ); ?></span>
			<span class="wpvfh-subtab-text"><?php echo esc_html( $group['name'] ); ?></span>
		</button>
		<?php
		$first = false;
		endforeach;
		?>
	</div>

	<!-- Contenu des sous-onglets -->
	<div class="wpvfh-metadata-content">
		<?php
		$first = true;
		foreach ( $metadata_groups as $group_slug => $group ) :
			$items = $group['items'];
		?>
		<div id="wpvfh-metadata-<?php echo esc_attr( $group_slug ); ?>" class="wpvfh-metadata-subtab-content <?php echo $first ? 'active' : ''; ?>" data-group="<?php echo esc_attr( $group_slug ); ?>">
			<!-- Titre du groupe de métadonnées -->
			<h3 class="wpvfh-metadata-group-title">
				<span class="wpvfh-metadata-group-icon"><?php echo esc_html( $group['icon'] ); ?></span>
				<?php echo esc_html( $group['name'] ); ?>
			</h3>
			<!-- Zones de dépôt sticky -->
			<div class="wpvfh-metadata-dropzones" data-group="<?php echo esc_attr( $group_slug ); ?>">
				<?php foreach ( $items as $item ) : ?>
					<?php if ( ! empty( $item['enabled'] ) ) : ?>
					<div class="wpvfh-dropzone wpvfh-dropzone-metadata" data-group="<?php echo esc_attr( $group_slug ); ?>" data-value="<?php echo esc_attr( $item['id'] ); ?>" style="--dropzone-color: <?php echo esc_attr( $item['color'] ?? '#6c757d' ); ?>;">
						<span class="wpvfh-dropzone-label"><?php echo esc_html( ( $item['emoji'] ?? '' ) . ' ' . $item['label'] ); ?></span>
					</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<!-- Listes par valeur -->
			<div class="wpvfh-metadata-sections" data-group="<?php echo esc_attr( $group_slug ); ?>">
				<!-- Section "Non assigné" -->
				<div class="wpvfh-metadata-section" data-group="<?php echo esc_attr( $group_slug ); ?>" data-value="none">
					<h4 class="wpvfh-metadata-title">
						⚪ <?php printf( esc_html__( 'Sans %s', 'blazing-feedback' ), esc_html( strtolower( $group['name'] ) ) ); ?>
					</h4>
					<div class="wpvfh-metadata-list" id="wpvfh-metadata-<?php echo esc_attr( $group_slug ); ?>-none-list"></div>
				</div>

				<?php foreach ( $items as $item ) : ?>
					<?php if ( ! empty( $item['enabled'] ) ) : ?>
					<div class="wpvfh-metadata-section" data-group="<?php echo esc_attr( $group_slug ); ?>" data-value="<?php echo esc_attr( $item['id'] ); ?>">
						<h4 class="wpvfh-metadata-title" style="--section-color: <?php echo esc_attr( $item['color'] ?? '#6c757d' ); ?>;">
							<?php echo esc_html( ( $item['emoji'] ?? '' ) . ' ' . $item['label'] ); ?>
						</h4>
						<div class="wpvfh-metadata-list" id="wpvfh-metadata-<?php echo esc_attr( $group_slug ); ?>-<?php echo esc_attr( $item['id'] ); ?>-list"></div>
					</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		$first = false;
		endforeach;
		?>
	</div>
</div><!-- /wpvfh-tab-metadata -->
