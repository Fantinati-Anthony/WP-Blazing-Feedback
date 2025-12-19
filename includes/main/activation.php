<?php
/**
 * Gestion de l'activation et désactivation du plugin
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activation du plugin
 *
 * @since 1.0.0
 * @return void
 */
function wpvfh_activate() {
	// Créer les rôles personnalisés
	WPVFH_Roles::create_roles();

	// Installer les tables SQL personnalisées
	WPVFH_Database::install();

	// Migration des données si nécessaire (depuis posts/postmeta vers tables custom)
	if ( WPVFH_Database::needs_migration() ) {
		WPVFH_Database::run_migration();
	}

	// Enregistrer le CPT pour flush les rewrite rules (gardé pour rétrocompatibilité)
	WPVFH_CPT_Feedback::register_post_type();
	WPVFH_CPT_Feedback::register_taxonomies();

	// Flush des règles de réécriture
	flush_rewrite_rules();

	// Créer le dossier uploads pour les screenshots
	wpvfh_create_upload_directory();

	// Sauvegarder la version pour les mises à jour futures
	update_option( 'wpvfh_version', WPVFH_VERSION );

	/**
	 * Action déclenchée après l'activation du plugin
	 *
	 * @since 1.0.0
	 */
	do_action( 'wpvfh_activated' );
}

/**
 * Désactivation du plugin
 *
 * @since 1.0.0
 * @return void
 */
function wpvfh_deactivate() {
	// Flush des règles de réécriture
	flush_rewrite_rules();

	/**
	 * Action déclenchée après la désactivation du plugin
	 *
	 * @since 1.0.0
	 */
	do_action( 'wpvfh_deactivated' );
}

/**
 * Créer le dossier d'upload pour les screenshots
 *
 * @since 1.0.0
 * @return void
 */
function wpvfh_create_upload_directory() {
	$upload_dir = wp_upload_dir();
	$feedback_dir = $upload_dir['basedir'] . '/visual-feedback';

	if ( ! file_exists( $feedback_dir ) ) {
		wp_mkdir_p( $feedback_dir );

		// Créer un fichier index.php pour la sécurité
		$index_file = $feedback_dir . '/index.php';
		if ( ! file_exists( $index_file ) ) {
			file_put_contents( $index_file, '<?php // Silence is golden.' );
		}

		// Créer un .htaccess pour protéger le dossier
		$htaccess_file = $feedback_dir . '/.htaccess';
		if ( ! file_exists( $htaccess_file ) ) {
			$htaccess_content = "Options -Indexes\n<FilesMatch '\.(php|php\.)$'>\nOrder Allow,Deny\nDeny from all\n</FilesMatch>";
			file_put_contents( $htaccess_file, $htaccess_content );
		}
	}
}
