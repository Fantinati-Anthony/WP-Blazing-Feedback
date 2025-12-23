<?php
/**
 * Administration des Réglages Unifiés
 *
 * Fusionne les réglages de Blazing Minds et Blazing Feedback
 *
 * @package Blazing_Minds
 * @since 1.0.0
 * @since 2.1.0 Fusion des réglages Blazing Minds + Blazing Feedback
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Admin_Settings
 *
 * Configuration centralisée de tous les paramètres des plugins Blazing
 *
 * @since 1.0.0
 */
class BZMI_Admin_Settings {

	/**
	 * Enregistrer les réglages
	 *
	 * @return void
	 */
	public static function register_settings() {
		// Section Générale
		add_settings_section(
			'bzmi_general_section',
			__( 'Paramètres généraux', 'blazing-minds' ),
			array( __CLASS__, 'render_general_section' ),
			'blazing-minds-settings'
		);

		// Section IA
		add_settings_section(
			'bzmi_ai_section',
			__( 'Configuration IA', 'blazing-minds' ),
			array( __CLASS__, 'render_ai_section' ),
			'blazing-minds-settings-ai'
		);

		// Section ICAVAL
		add_settings_section(
			'bzmi_icaval_section',
			__( 'Workflow ICAVAL', 'blazing-minds' ),
			array( __CLASS__, 'render_icaval_section' ),
			'blazing-minds-settings-icaval'
		);

		// Section Intégrations
		add_settings_section(
			'bzmi_integrations_section',
			__( 'Intégrations', 'blazing-minds' ),
			array( __CLASS__, 'render_integrations_section' ),
			'blazing-minds-settings-integrations'
		);
	}

	/**
	 * Afficher la page des réglages unifiée
	 *
	 * @return void
	 */
	public static function render_page() {
		// Sauvegarder si formulaire soumis
		if ( isset( $_POST['bzmi_save_settings'] ) ) {
			self::save_settings();
		}

		// Traiter les actions de la zone de danger
		self::handle_danger_zone_actions();

		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';

		// Afficher les messages
		BZMI_Admin::display_messages();
		self::display_feedback_messages();

		?>
		<div class="wrap wpvfh-settings-wrap">
			<h1><?php esc_html_e( 'Réglages Blazing', 'blazing-minds' ); ?></h1>

			<nav class="nav-tab-wrapper wpvfh-nav-tabs">
				<!-- Onglets Blazing Minds -->
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-settings&tab=general' ) ); ?>"
				   class="nav-tab <?php echo 'general' === $tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-admin-generic"></span>
					<?php esc_html_e( 'Général', 'blazing-minds' ); ?>
				</a>

				<!-- Onglets Blazing Feedback -->
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-settings&tab=feedback' ) ); ?>"
				   class="nav-tab <?php echo 'feedback' === $tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-format-chat"></span>
					<?php esc_html_e( 'Feedback', 'blazing-minds' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-settings&tab=design' ) ); ?>"
				   class="nav-tab <?php echo 'design' === $tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-admin-appearance"></span>
					<?php esc_html_e( 'Personnalisation', 'blazing-minds' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-settings&tab=notifications' ) ); ?>"
				   class="nav-tab <?php echo 'notifications' === $tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-email"></span>
					<?php esc_html_e( 'Notifications', 'blazing-minds' ); ?>
				</a>

				<!-- IA Unifiée -->
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-settings&tab=ai' ) ); ?>"
				   class="nav-tab <?php echo 'ai' === $tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-welcome-learn-more"></span>
					<?php esc_html_e( 'IA', 'blazing-minds' ); ?>
				</a>

				<!-- Onglets Blazing Minds -->
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-settings&tab=icaval' ) ); ?>"
				   class="nav-tab <?php echo 'icaval' === $tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-networking"></span>
					<?php esc_html_e( 'ICAVAL', 'blazing-minds' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-settings&tab=integrations' ) ); ?>"
				   class="nav-tab <?php echo 'integrations' === $tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-admin-plugins"></span>
					<?php esc_html_e( 'Intégrations', 'blazing-minds' ); ?>
				</a>

				<!-- Zone de danger -->
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-settings&tab=danger' ) ); ?>"
				   class="nav-tab <?php echo 'danger' === $tab ? 'nav-tab-active' : ''; ?>" style="color: #dc3545;">
					<span class="dashicons dashicons-warning"></span>
					<?php esc_html_e( 'Danger', 'blazing-minds' ); ?>
				</a>
			</nav>

			<?php if ( 'danger' !== $tab ) : ?>
			<form method="post" action="">
				<?php wp_nonce_field( 'bzmi_save_settings', 'bzmi_settings_nonce' ); ?>
				<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>">

				<?php
				switch ( $tab ) {
					case 'feedback':
						self::render_feedback_settings();
						break;
					case 'design':
						self::render_design_settings();
						break;
					case 'notifications':
						self::render_notifications_settings();
						break;
					case 'ai':
						self::render_ai_settings();
						break;
					case 'icaval':
						self::render_icaval_settings();
						break;
					case 'integrations':
						self::render_integrations_settings();
						break;
					default:
						self::render_general_settings();
				}
				?>

				<p class="submit">
					<input type="submit" name="bzmi_save_settings" class="button-primary"
						   value="<?php esc_attr_e( 'Enregistrer les modifications', 'blazing-minds' ); ?>">
				</p>
			</form>
			<?php else : ?>
				<?php self::render_danger_zone(); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Afficher les messages Blazing Feedback
	 *
	 * @return void
	 */
	private static function display_feedback_messages() {
		$message = isset( $_GET['message'] ) ? sanitize_key( $_GET['message'] ) : '';
		if ( $message ) {
			$messages = array(
				'feedbacks_truncated' => __( 'Tous les feedbacks ont été supprimés.', 'blazing-minds' ),
				'all_truncated'       => __( 'Toutes les tables ont été vidées.', 'blazing-minds' ),
				'tables_dropped'      => __( 'Toutes les tables ont été supprimées.', 'blazing-minds' ),
				'tables_recreated'    => __( 'Les tables ont été recréées avec succès.', 'blazing-minds' ),
			);
			if ( isset( $messages[ $message ] ) ) {
				printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $messages[ $message ] ) );
			}
		}
	}

	/**
	 * Afficher les réglages généraux (Blazing Minds)
	 *
	 * @return void
	 */
	private static function render_general_settings() {
		$items_per_page = BZMI_Database::get_setting( 'items_per_page', 20 );
		$date_format = BZMI_Database::get_setting( 'date_format', 'Y-m-d H:i' );
		$enable_notifications = BZMI_Database::get_setting( 'enable_notifications', true );
		$notification_email = BZMI_Database::get_setting( 'notification_email', get_option( 'admin_email' ) );
		$default_status = BZMI_Database::get_setting( 'default_status', 'pending' );
		?>
		<div class="wpvfh-settings-section">
			<h2><?php esc_html_e( 'Paramètres généraux Blazing Minds', 'blazing-minds' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="items_per_page"><?php esc_html_e( 'Éléments par page', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<input type="number" id="items_per_page" name="items_per_page"
							   value="<?php echo esc_attr( $items_per_page ); ?>" min="5" max="100" class="small-text">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="date_format"><?php esc_html_e( 'Format de date', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<select id="date_format" name="date_format">
							<option value="Y-m-d H:i" <?php selected( $date_format, 'Y-m-d H:i' ); ?>>2024-01-15 14:30</option>
							<option value="d/m/Y H:i" <?php selected( $date_format, 'd/m/Y H:i' ); ?>>15/01/2024 14:30</option>
							<option value="F j, Y g:i a" <?php selected( $date_format, 'F j, Y g:i a' ); ?>>January 15, 2024 2:30 pm</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="default_status"><?php esc_html_e( 'Statut par défaut', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<select id="default_status" name="default_status">
							<option value="pending" <?php selected( $default_status, 'pending' ); ?>><?php esc_html_e( 'En attente', 'blazing-minds' ); ?></option>
							<option value="active" <?php selected( $default_status, 'active' ); ?>><?php esc_html_e( 'Actif', 'blazing-minds' ); ?></option>
							<option value="new" <?php selected( $default_status, 'new' ); ?>><?php esc_html_e( 'Nouveau', 'blazing-minds' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Notifications', 'blazing-minds' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="enable_notifications" value="1"
								   <?php checked( $enable_notifications, true ); ?>>
							<?php esc_html_e( 'Activer les notifications par email', 'blazing-minds' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="notification_email"><?php esc_html_e( 'Email de notification', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<input type="email" id="notification_email" name="notification_email"
							   value="<?php echo esc_attr( $notification_email ); ?>" class="regular-text">
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Afficher les réglages Feedback (Blazing Feedback général)
	 *
	 * @return void
	 */
	private static function render_feedback_settings() {
		// Appeler le rendu du trait WPVFH si disponible
		if ( class_exists( 'WPVFH_Admin_UI' ) && method_exists( 'WPVFH_Admin_UI', 'render_tab_general' ) ) {
			WPVFH_Admin_UI::render_tab_general();
		} else {
			echo '<p>' . esc_html__( 'Les réglages Blazing Feedback ne sont pas disponibles.', 'blazing-minds' ) . '</p>';
		}
	}

	/**
	 * Afficher les réglages Design (Personnalisation)
	 *
	 * @return void
	 */
	private static function render_design_settings() {
		// Appeler le rendu du trait WPVFH si disponible
		if ( class_exists( 'WPVFH_Admin_UI' ) && method_exists( 'WPVFH_Admin_UI', 'render_tab_design' ) ) {
			WPVFH_Admin_UI::render_tab_design();
		} else {
			echo '<p>' . esc_html__( 'Les réglages de personnalisation ne sont pas disponibles.', 'blazing-minds' ) . '</p>';
		}
	}

	/**
	 * Afficher les réglages Notifications
	 *
	 * @return void
	 */
	private static function render_notifications_settings() {
		// Appeler le rendu du trait WPVFH si disponible
		if ( class_exists( 'WPVFH_Admin_UI' ) && method_exists( 'WPVFH_Admin_UI', 'render_tab_notifications' ) ) {
			WPVFH_Admin_UI::render_tab_notifications();
		} else {
			echo '<p>' . esc_html__( 'Les réglages de notifications ne sont pas disponibles.', 'blazing-minds' ) . '</p>';
		}
	}

	/**
	 * Afficher les réglages IA (fusionnés Blazing Minds + Feedback)
	 *
	 * @return void
	 */
	private static function render_ai_settings() {
		// Configuration IA centralisée (Blazing Minds)
		$ai_enabled = BZMI_Database::get_setting( 'ai_enabled', false );
		$ai_provider = BZMI_Database::get_setting( 'ai_provider', 'openai' );
		$ai_api_key = BZMI_Database::get_setting( 'ai_api_key', '' );
		$ai_model = BZMI_Database::get_setting( 'ai_model', 'gpt-4' );
		$ai_max_tokens = BZMI_Database::get_setting( 'ai_max_tokens', 2000 );
		$ai_temperature = BZMI_Database::get_setting( 'ai_temperature', 0.7 );
		$ai_features = BZMI_Database::get_setting( 'ai_features', array() );

		// Configuration IA Feedback spécifique
		$feedback_ai_enabled = WPVFH_Database::get_setting( 'wpvfh_ai_enabled', false );
		$feedback_system_prompt = WPVFH_Database::get_setting( 'wpvfh_ai_system_prompt', '' );
		$feedback_analysis_prompt = WPVFH_Database::get_setting( 'wpvfh_ai_analysis_prompt', '' );

		$providers = array(
			'openai'    => 'OpenAI',
			'anthropic' => 'Anthropic (Claude)',
			'google'    => 'Google (Gemini)',
			'azure'     => 'Azure OpenAI',
			'local'     => 'LLM Local (Ollama)',
		);

		$models = array(
			'openai' => array(
				'gpt-4'            => 'GPT-4',
				'gpt-4-turbo'      => 'GPT-4 Turbo',
				'gpt-4o'           => 'GPT-4o',
				'gpt-3.5-turbo'    => 'GPT-3.5 Turbo',
			),
			'anthropic' => array(
				'claude-3-opus'    => 'Claude 3 Opus',
				'claude-3-sonnet'  => 'Claude 3 Sonnet',
				'claude-3-haiku'   => 'Claude 3 Haiku',
			),
			'google' => array(
				'gemini-pro'       => 'Gemini Pro',
				'gemini-ultra'     => 'Gemini Ultra',
			),
		);
		?>
		<div class="bzmi-ai-settings">
			<div class="bzmi-notice bzmi-notice-info" style="background: #e7f3ff; border-left: 4px solid #2196F3; padding: 12px; margin-bottom: 20px;">
				<p>
					<strong><?php esc_html_e( 'Configuration IA centralisée', 'blazing-minds' ); ?></strong><br>
					<?php esc_html_e( 'Cette configuration IA est partagée entre Blazing Minds et Blazing Feedback.', 'blazing-minds' ); ?>
				</p>
			</div>

			<h2><?php esc_html_e( 'Configuration générale IA', 'blazing-minds' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Activer l\'IA', 'blazing-minds' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="ai_enabled" value="1"
								   <?php checked( $ai_enabled, true ); ?>>
							<?php esc_html_e( 'Activer les fonctionnalités d\'intelligence artificielle', 'blazing-minds' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'L\'IA peut suggérer des clarifications, des actions, et détecter des patterns.', 'blazing-minds' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ai_provider"><?php esc_html_e( 'Fournisseur IA', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<select id="ai_provider" name="ai_provider">
							<?php foreach ( $providers as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $ai_provider, $key ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ai_api_key"><?php esc_html_e( 'Clé API', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<input type="password" id="ai_api_key" name="ai_api_key"
							   value="<?php echo esc_attr( $ai_api_key ); ?>" class="regular-text">
						<p class="description">
							<?php esc_html_e( 'Votre clé API sera stockée de manière sécurisée.', 'blazing-minds' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ai_model"><?php esc_html_e( 'Modèle', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<select id="ai_model" name="ai_model">
							<?php foreach ( $models as $provider => $provider_models ) : ?>
								<optgroup label="<?php echo esc_attr( $providers[ $provider ] ); ?>">
									<?php foreach ( $provider_models as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $ai_model, $key ); ?>>
											<?php echo esc_html( $label ); ?>
										</option>
									<?php endforeach; ?>
								</optgroup>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ai_max_tokens"><?php esc_html_e( 'Tokens maximum', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<input type="number" id="ai_max_tokens" name="ai_max_tokens"
							   value="<?php echo esc_attr( $ai_max_tokens ); ?>" min="100" max="8000" class="small-text">
						<p class="description">
							<?php esc_html_e( 'Nombre maximum de tokens par requête (affecte le coût).', 'blazing-minds' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ai_temperature"><?php esc_html_e( 'Température', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<input type="range" id="ai_temperature" name="ai_temperature"
							   value="<?php echo esc_attr( $ai_temperature ); ?>" min="0" max="1" step="0.1">
						<span id="ai_temperature_value"><?php echo esc_html( $ai_temperature ); ?></span>
						<p class="description">
							<?php esc_html_e( '0 = déterministe, 1 = créatif. Recommandé: 0.7', 'blazing-minds' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Fonctionnalités IA - Blazing Minds', 'blazing-minds' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Auto-clarification', 'blazing-minds' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="ai_features[auto_clarify]" value="1"
								   <?php checked( ! empty( $ai_features['auto_clarify'] ), true ); ?>>
							<?php esc_html_e( 'Suggérer automatiquement des questions de clarification', 'blazing-minds' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Suggestion d\'actions', 'blazing-minds' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="ai_features[suggest_actions]" value="1"
								   <?php checked( ! empty( $ai_features['suggest_actions'] ), true ); ?>>
							<?php esc_html_e( 'Proposer des actions basées sur les clarifications', 'blazing-minds' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Détection de patterns', 'blazing-minds' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="ai_features[detect_patterns]" value="1"
								   <?php checked( ! empty( $ai_features['detect_patterns'] ), true ); ?>>
							<?php esc_html_e( 'Identifier les patterns et tendances dans les informations', 'blazing-minds' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Génération de rapports', 'blazing-minds' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="ai_features[generate_reports]" value="1"
								   <?php checked( ! empty( $ai_features['generate_reports'] ), true ); ?>>
							<?php esc_html_e( 'Générer des rapports de synthèse automatiquement', 'blazing-minds' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Fonctionnalités IA - Blazing Feedback', 'blazing-minds' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Analyse des feedbacks', 'blazing-minds' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="wpvfh_ai_enabled" value="1"
								   <?php checked( $feedback_ai_enabled, true ); ?>>
							<?php esc_html_e( 'Activer l\'analyse IA des feedbacks', 'blazing-minds' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Permet d\'utiliser l\'IA pour analyser et catégoriser les feedbacks.', 'blazing-minds' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpvfh_ai_system_prompt"><?php esc_html_e( 'Prompt système', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<textarea name="wpvfh_ai_system_prompt" id="wpvfh_ai_system_prompt" rows="4" class="large-text"
								  placeholder="<?php esc_attr_e( 'Vous êtes un assistant qui aide à analyser les retours utilisateurs...', 'blazing-minds' ); ?>"><?php echo esc_textarea( $feedback_system_prompt ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Le prompt système définit le comportement général de l\'IA pour l\'analyse des feedbacks.', 'blazing-minds' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpvfh_ai_analysis_prompt"><?php esc_html_e( 'Prompt d\'analyse', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<textarea name="wpvfh_ai_analysis_prompt" id="wpvfh_ai_analysis_prompt" rows="4" class="large-text"
								  placeholder="<?php esc_attr_e( 'Analysez ce feedback et suggérez une catégorie, une priorité et une réponse type...', 'blazing-minds' ); ?>"><?php echo esc_textarea( $feedback_analysis_prompt ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Le prompt utilisé pour analyser chaque feedback.', 'blazing-minds' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#ai_temperature').on('input', function() {
				$('#ai_temperature_value').text($(this).val());
			});
		});
		</script>
		<?php
	}

	/**
	 * Afficher les réglages ICAVAL
	 *
	 * @return void
	 */
	private static function render_icaval_settings() {
		$auto_advance = BZMI_Database::get_setting( 'icaval_auto_advance', false );
		$require_validation = BZMI_Database::get_setting( 'icaval_require_validation', true );
		$notify_stakeholders = BZMI_Database::get_setting( 'icaval_notify_stakeholders', true );
		?>
		<div class="bzmi-icaval-settings">
			<div class="bzmi-workflow-diagram" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 20px; background: #f8f9fa; border-radius: 8px; margin-bottom: 20px;">
				<div class="bzmi-stage" style="background: #3498db; color: white; padding: 15px; border-radius: 8px; text-align: center; font-weight: bold;">I<br><small><?php esc_html_e( 'Information', 'blazing-minds' ); ?></small></div>
				<div class="bzmi-arrow" style="font-size: 24px; color: #666;">&rarr;</div>
				<div class="bzmi-stage" style="background: #9b59b6; color: white; padding: 15px; border-radius: 8px; text-align: center; font-weight: bold;">C<br><small><?php esc_html_e( 'Clarification', 'blazing-minds' ); ?></small></div>
				<div class="bzmi-arrow" style="font-size: 24px; color: #666;">&rarr;</div>
				<div class="bzmi-stage" style="background: #e67e22; color: white; padding: 15px; border-radius: 8px; text-align: center; font-weight: bold;">A<br><small><?php esc_html_e( 'Action', 'blazing-minds' ); ?></small></div>
				<div class="bzmi-arrow" style="font-size: 24px; color: #666;">&rarr;</div>
				<div class="bzmi-stage" style="background: #27ae60; color: white; padding: 15px; border-radius: 8px; text-align: center; font-weight: bold;">V<br><small><?php esc_html_e( 'Valeur', 'blazing-minds' ); ?></small></div>
				<div class="bzmi-arrow" style="font-size: 24px; color: #666;">&rarr;</div>
				<div class="bzmi-stage" style="background: #2c3e50; color: white; padding: 15px; border-radius: 8px; text-align: center; font-weight: bold;">AL<br><small><?php esc_html_e( 'Apprentissage', 'blazing-minds' ); ?></small></div>
			</div>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Avancement automatique', 'blazing-minds' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="icaval_auto_advance" value="1"
								   <?php checked( $auto_advance, true ); ?>>
							<?php esc_html_e( 'Avancer automatiquement à l\'étape suivante quand les conditions sont remplies', 'blazing-minds' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Ex: passer de Clarification à Action quand toutes les clarifications sont résolues.', 'blazing-minds' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Validation requise', 'blazing-minds' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="icaval_require_validation" value="1"
								   <?php checked( $require_validation, true ); ?>>
							<?php esc_html_e( 'Exiger une validation manuelle avant chaque changement d\'étape', 'blazing-minds' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Notifications', 'blazing-minds' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="icaval_notify_stakeholders" value="1"
								   <?php checked( $notify_stakeholders, true ); ?>>
							<?php esc_html_e( 'Notifier les parties prenantes lors des changements d\'étape', 'blazing-minds' ); ?>
						</label>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Afficher les réglages d'intégrations
	 *
	 * @return void
	 */
	private static function render_integrations_settings() {
		$bf_sync = BZMI_Database::get_setting( 'blazing_feedback_sync', true );
		$bf_auto_import = BZMI_Database::get_setting( 'blazing_feedback_auto_import', true );
		$bf_default_project = BZMI_Database::get_setting( 'blazing_feedback_default_project', 0 );

		// Vérifier si Blazing Feedback est installé
		$bf_installed = class_exists( 'Blazing_Feedback' ) || defined( 'WPVFH_VERSION' );

		$projects = BZMI_Project::all( array( 'orderby' => 'name', 'order' => 'ASC' ) );
		?>
		<div class="bzmi-integrations-settings">
			<h2><?php esc_html_e( 'Blazing Feedback', 'blazing-minds' ); ?></h2>

			<?php if ( ! $bf_installed ) : ?>
				<div class="notice notice-warning inline" style="margin: 0 0 15px 0;">
					<p>
						<?php esc_html_e( 'Blazing Feedback n\'est pas installé. Installez-le pour synchroniser automatiquement les feedbacks.', 'blazing-minds' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Synchronisation', 'blazing-minds' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="blazing_feedback_sync" value="1"
								   <?php checked( $bf_sync, true ); ?> <?php disabled( ! $bf_installed ); ?>>
							<?php esc_html_e( 'Activer la synchronisation avec Blazing Feedback', 'blazing-minds' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Import automatique', 'blazing-minds' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="blazing_feedback_auto_import" value="1"
								   <?php checked( $bf_auto_import, true ); ?> <?php disabled( ! $bf_installed ); ?>>
							<?php esc_html_e( 'Importer automatiquement les nouveaux feedbacks comme Informations', 'blazing-minds' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="blazing_feedback_default_project"><?php esc_html_e( 'Projet par défaut', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<select id="blazing_feedback_default_project" name="blazing_feedback_default_project"
								<?php disabled( ! $bf_installed ); ?>>
							<option value="0"><?php esc_html_e( '-- Sélectionner un projet --', 'blazing-minds' ); ?></option>
							<?php foreach ( $projects as $project ) : ?>
								<option value="<?php echo esc_attr( $project->id ); ?>"
										<?php selected( $bf_default_project, $project->id ); ?>>
									<?php echo esc_html( $project->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php esc_html_e( 'Projet où les feedbacks seront importés par défaut.', 'blazing-minds' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Autres sources d\'informations', 'blazing-minds' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'D\'autres intégrations (Extension Chrome, Application mobile, Webhooks API) seront disponibles dans les prochaines versions.', 'blazing-minds' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Afficher la zone de danger
	 *
	 * @return void
	 */
	private static function render_danger_zone() {
		// Appeler le rendu du trait WPVFH si disponible
		if ( class_exists( 'WPVFH_Admin_UI' ) && method_exists( 'WPVFH_Admin_UI', 'render_tab_danger' ) ) {
			WPVFH_Admin_UI::render_tab_danger();
		} else {
			?>
			<div class="wpvfh-danger-zone" style="padding: 20px; background: #fff; border: 2px solid #dc3545; border-radius: 4px;">
				<h2 style="color: #dc3545; margin-top: 0;">
					<span class="dashicons dashicons-warning" style="color: #dc3545;"></span>
					<?php esc_html_e( 'Zone de danger', 'blazing-minds' ); ?>
				</h2>
				<p style="color: #666;"><?php esc_html_e( 'Les fonctionnalités de la zone de danger ne sont pas disponibles.', 'blazing-minds' ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Gérer les actions de la zone de danger
	 *
	 * @return void
	 */
	private static function handle_danger_zone_actions() {
		if ( ! isset( $_GET['action'] ) || ! isset( $_GET['tab'] ) || 'danger' !== $_GET['tab'] ) {
			return;
		}

		// Déléguer à WPVFH_Admin_UI si disponible
		if ( class_exists( 'WPVFH_Admin_UI' ) && method_exists( 'WPVFH_Admin_UI', 'handle_danger_zone_actions' ) ) {
			// Les actions sont gérées par le trait
			return;
		}
	}

	/**
	 * Callbacks de section
	 */
	public static function render_general_section() {
		echo '<p>' . esc_html__( 'Configurez les paramètres généraux du plugin.', 'blazing-minds' ) . '</p>';
	}

	public static function render_ai_section() {
		echo '<p>' . esc_html__( 'Configurez l\'intelligence artificielle centralisée.', 'blazing-minds' ) . '</p>';
	}

	public static function render_icaval_section() {
		echo '<p>' . esc_html__( 'Configurez le comportement du workflow ICAVAL.', 'blazing-minds' ) . '</p>';
	}

	public static function render_integrations_section() {
		echo '<p>' . esc_html__( 'Configurez les intégrations avec d\'autres plugins et services.', 'blazing-minds' ) . '</p>';
	}

	/**
	 * Sauvegarder les réglages
	 *
	 * @return void
	 */
	private static function save_settings() {
		if ( ! isset( $_POST['bzmi_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bzmi_settings_nonce'] ) ), 'bzmi_save_settings' ) ) {
			return;
		}

		$tab = isset( $_POST['tab'] ) ? sanitize_text_field( wp_unslash( $_POST['tab'] ) ) : 'general';

		switch ( $tab ) {
			case 'feedback':
				self::save_feedback_settings();
				break;
			case 'design':
				self::save_design_settings();
				break;
			case 'notifications':
				self::save_notifications_settings();
				break;
			case 'ai':
				self::save_ai_settings();
				break;
			case 'icaval':
				self::save_icaval_settings();
				break;
			case 'integrations':
				self::save_integrations_settings();
				break;
			default:
				self::save_general_settings();
		}

		BZMI_Admin::redirect_with_message(
			'bzmi-settings&tab=' . $tab,
			__( 'Réglages enregistrés avec succès.', 'blazing-minds' ),
			'success'
		);
	}

	/**
	 * Sauvegarder les réglages généraux
	 *
	 * @return void
	 */
	private static function save_general_settings() {
		BZMI_Database::update_setting( 'items_per_page', isset( $_POST['items_per_page'] ) ? intval( $_POST['items_per_page'] ) : 20 );
		BZMI_Database::update_setting( 'date_format', isset( $_POST['date_format'] ) ? sanitize_text_field( wp_unslash( $_POST['date_format'] ) ) : 'Y-m-d H:i' );
		BZMI_Database::update_setting( 'default_status', isset( $_POST['default_status'] ) ? sanitize_text_field( wp_unslash( $_POST['default_status'] ) ) : 'pending' );
		BZMI_Database::update_setting( 'enable_notifications', isset( $_POST['enable_notifications'] ) );
		BZMI_Database::update_setting( 'notification_email', isset( $_POST['notification_email'] ) ? sanitize_email( wp_unslash( $_POST['notification_email'] ) ) : '' );
	}

	/**
	 * Sauvegarder les réglages Feedback
	 *
	 * @return void
	 */
	private static function save_feedback_settings() {
		// Sauvegarder via les options WordPress (utilisé par WPVFH)
		if ( isset( $_POST['wpvfh_screenshot_enabled'] ) ) {
			WPVFH_Database::update_setting( 'wpvfh_screenshot_enabled', true );
		} else {
			WPVFH_Database::update_setting( 'wpvfh_screenshot_enabled', false );
		}

		if ( isset( $_POST['wpvfh_guest_enabled'] ) ) {
			WPVFH_Database::update_setting( 'wpvfh_guest_enabled', true );
		} else {
			WPVFH_Database::update_setting( 'wpvfh_guest_enabled', false );
		}

		if ( isset( $_POST['wpvfh_enable_admin'] ) ) {
			WPVFH_Database::update_setting( 'wpvfh_enable_admin', true );
		} else {
			WPVFH_Database::update_setting( 'wpvfh_enable_admin', false );
		}

		if ( isset( $_POST['wpvfh_active_pages'] ) ) {
			WPVFH_Database::update_setting( 'wpvfh_active_pages', sanitize_text_field( wp_unslash( $_POST['wpvfh_active_pages'] ) ) );
		}

		if ( isset( $_POST['wpvfh_post_feedback_action'] ) ) {
			WPVFH_Database::update_setting( 'wpvfh_post_feedback_action', sanitize_text_field( wp_unslash( $_POST['wpvfh_post_feedback_action'] ) ) );
		}
	}

	/**
	 * Sauvegarder les réglages Design
	 *
	 * @return void
	 */
	private static function save_design_settings() {
		$design_settings = array(
			'wpvfh_theme_mode',
			'wpvfh_button_position',
			'wpvfh_button_color',
			'wpvfh_button_color_hover',
			'wpvfh_button_size',
			'wpvfh_button_style',
			'wpvfh_button_border_radius',
			'wpvfh_button_border_radius_unit',
			'wpvfh_button_margin',
			'wpvfh_button_border_width',
			'wpvfh_button_border_color',
			'wpvfh_button_shadow_blur',
			'wpvfh_button_shadow_opacity',
			'wpvfh_button_shadow_color',
			'wpvfh_light_icon_type',
			'wpvfh_light_icon_emoji',
			'wpvfh_light_icon_url',
			'wpvfh_dark_icon_type',
			'wpvfh_dark_icon_emoji',
			'wpvfh_dark_icon_url',
			'wpvfh_panel_logo_light_url',
			'wpvfh_panel_logo_dark_url',
			'wpvfh_badge_bg_color',
			'wpvfh_badge_text_color',
			'wpvfh_color_primary',
			'wpvfh_color_primary_hover',
			'wpvfh_color_secondary',
			'wpvfh_color_success',
			'wpvfh_color_warning',
			'wpvfh_color_danger',
			'wpvfh_color_text',
			'wpvfh_color_text_light',
			'wpvfh_color_bg',
			'wpvfh_color_bg_light',
			'wpvfh_color_border',
		);

		foreach ( $design_settings as $setting ) {
			if ( isset( $_POST[ $setting ] ) ) {
				WPVFH_Database::update_setting( $setting, sanitize_text_field( wp_unslash( $_POST[ $setting ] ) ) );
			}
		}
	}

	/**
	 * Sauvegarder les réglages Notifications
	 *
	 * @return void
	 */
	private static function save_notifications_settings() {
		if ( isset( $_POST['wpvfh_email_notifications'] ) ) {
			WPVFH_Database::update_setting( 'wpvfh_email_notifications', true );
		} else {
			WPVFH_Database::update_setting( 'wpvfh_email_notifications', false );
		}

		if ( isset( $_POST['wpvfh_notification_email'] ) ) {
			WPVFH_Database::update_setting( 'wpvfh_notification_email', sanitize_email( wp_unslash( $_POST['wpvfh_notification_email'] ) ) );
		}
	}

	/**
	 * Sauvegarder les réglages IA
	 *
	 * @return void
	 */
	private static function save_ai_settings() {
		// Réglages Blazing Minds
		BZMI_Database::update_setting( 'ai_enabled', isset( $_POST['ai_enabled'] ) );
		BZMI_Database::update_setting( 'ai_provider', isset( $_POST['ai_provider'] ) ? sanitize_text_field( wp_unslash( $_POST['ai_provider'] ) ) : 'openai' );

		// Ne sauvegarder la clé API que si elle n'est pas vide
		if ( isset( $_POST['ai_api_key'] ) && ! empty( $_POST['ai_api_key'] ) ) {
			BZMI_Database::update_setting( 'ai_api_key', sanitize_text_field( wp_unslash( $_POST['ai_api_key'] ) ) );
		}

		BZMI_Database::update_setting( 'ai_model', isset( $_POST['ai_model'] ) ? sanitize_text_field( wp_unslash( $_POST['ai_model'] ) ) : 'gpt-4' );
		BZMI_Database::update_setting( 'ai_max_tokens', isset( $_POST['ai_max_tokens'] ) ? intval( $_POST['ai_max_tokens'] ) : 2000 );
		BZMI_Database::update_setting( 'ai_temperature', isset( $_POST['ai_temperature'] ) ? floatval( $_POST['ai_temperature'] ) : 0.7 );

		$ai_features = isset( $_POST['ai_features'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ai_features'] ) ) : array();
		BZMI_Database::update_setting( 'ai_features', $ai_features );

		// Réglages Blazing Feedback IA
		WPVFH_Database::update_setting( 'wpvfh_ai_enabled', isset( $_POST['wpvfh_ai_enabled'] ) );

		if ( isset( $_POST['wpvfh_ai_system_prompt'] ) ) {
			WPVFH_Database::update_setting( 'wpvfh_ai_system_prompt', sanitize_textarea_field( wp_unslash( $_POST['wpvfh_ai_system_prompt'] ) ) );
		}

		if ( isset( $_POST['wpvfh_ai_analysis_prompt'] ) ) {
			WPVFH_Database::update_setting( 'wpvfh_ai_analysis_prompt', sanitize_textarea_field( wp_unslash( $_POST['wpvfh_ai_analysis_prompt'] ) ) );
		}
	}

	/**
	 * Sauvegarder les réglages ICAVAL
	 *
	 * @return void
	 */
	private static function save_icaval_settings() {
		BZMI_Database::update_setting( 'icaval_auto_advance', isset( $_POST['icaval_auto_advance'] ) );
		BZMI_Database::update_setting( 'icaval_require_validation', isset( $_POST['icaval_require_validation'] ) );
		BZMI_Database::update_setting( 'icaval_notify_stakeholders', isset( $_POST['icaval_notify_stakeholders'] ) );
	}

	/**
	 * Sauvegarder les réglages d'intégrations
	 *
	 * @return void
	 */
	private static function save_integrations_settings() {
		BZMI_Database::update_setting( 'blazing_feedback_sync', isset( $_POST['blazing_feedback_sync'] ) );
		BZMI_Database::update_setting( 'blazing_feedback_auto_import', isset( $_POST['blazing_feedback_auto_import'] ) );
		BZMI_Database::update_setting( 'blazing_feedback_default_project', isset( $_POST['blazing_feedback_default_project'] ) ? intval( $_POST['blazing_feedback_default_project'] ) : 0 );
	}
}
