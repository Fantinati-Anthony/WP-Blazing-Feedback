<?php
/**
 * Administration du cycle ICAVAL
 *
 * Gestion complète du cycle : Information → Clarification → Action → Valeur → Apprentissage
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Admin_ICAVAL
 *
 * @since 1.0.0
 */
class BZMI_Admin_ICAVAL {

	/**
	 * Afficher la page
	 *
	 * @return void
	 */
	public static function render_page() {
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'informations';
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';

		BZMI_Admin::display_messages();

		// Traiter les actions POST
		if ( isset( $_POST['action'] ) ) {
			self::handle_post_action( $tab );
		}

		// Afficher les onglets
		self::render_tabs( $tab );

		switch ( $action ) {
			case 'new':
			case 'edit':
				self::render_form( $tab );
				break;
			case 'view':
				self::render_view( $tab );
				break;
			case 'delete':
				self::handle_delete( $tab );
				break;
			default:
				self::render_list( $tab );
		}
	}

	/**
	 * Afficher les onglets
	 *
	 * @param string $current Onglet actuel.
	 * @return void
	 */
	private static function render_tabs( $current ) {
		$tabs = array(
			'informations'   => array(
				'label' => __( 'Informations', 'blazing-minds' ),
				'icon'  => 'dashicons-info',
				'count' => BZMI_Information::count(),
			),
			'clarifications' => array(
				'label' => __( 'Clarifications', 'blazing-minds' ),
				'icon'  => 'dashicons-search',
				'count' => BZMI_Clarification::count(),
			),
			'actions'        => array(
				'label' => __( 'Actions', 'blazing-minds' ),
				'icon'  => 'dashicons-hammer',
				'count' => BZMI_Action::count(),
			),
			'values'         => array(
				'label' => __( 'Valeurs', 'blazing-minds' ),
				'icon'  => 'dashicons-chart-area',
				'count' => BZMI_Value::count(),
			),
			'apprenticeships' => array(
				'label' => __( 'Apprentissages', 'blazing-minds' ),
				'icon'  => 'dashicons-welcome-learn-more',
				'count' => BZMI_Apprenticeship::count(),
			),
		);

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Cycle ICAVAL', 'blazing-minds' ); ?></h1>

			<nav class="nav-tab-wrapper bzmi-icaval-tabs">
				<?php foreach ( $tabs as $slug => $tab ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-icaval&tab=' . $slug ) ); ?>"
					   class="nav-tab <?php echo $current === $slug ? 'nav-tab-active' : ''; ?>">
						<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
						<?php echo esc_html( $tab['label'] ); ?>
						<span class="bzmi-count"><?php echo esc_html( $tab['count'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		</div>
		<?php
	}

	/**
	 * Afficher la liste selon l'onglet
	 *
	 * @param string $tab Onglet.
	 * @return void
	 */
	private static function render_list( $tab ) {
		$per_page = BZMI_Database::get_setting( 'items_per_page', 20 );
		$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$project_id = isset( $_GET['project_id'] ) ? intval( $_GET['project_id'] ) : 0;

		$args = array(
			'orderby' => 'created_at',
			'order'   => 'DESC',
		);

		if ( $project_id && 'informations' === $tab ) {
			$args['where'] = array( 'project_id' => $project_id );
		}

		switch ( $tab ) {
			case 'informations':
				$result = BZMI_Information::paginate( $current_page, $per_page, $args );
				$items = $result['items'];
				$total = $result['total'];
				$projects = BZMI_Project::all( array( 'orderby' => 'name', 'order' => 'ASC' ) );
				include BZMI_PLUGIN_DIR . 'templates/minds/admin/icaval/informations-list.php';
				break;

			case 'clarifications':
				$result = BZMI_Clarification::paginate( $current_page, $per_page, $args );
				$items = $result['items'];
				$total = $result['total'];
				include BZMI_PLUGIN_DIR . 'templates/minds/admin/icaval/clarifications-list.php';
				break;

			case 'actions':
				$result = BZMI_Action::paginate( $current_page, $per_page, $args );
				$items = $result['items'];
				$total = $result['total'];
				include BZMI_PLUGIN_DIR . 'templates/minds/admin/icaval/actions-list.php';
				break;

			case 'values':
				$result = BZMI_Value::paginate( $current_page, $per_page, $args );
				$items = $result['items'];
				$total = $result['total'];
				include BZMI_PLUGIN_DIR . 'templates/minds/admin/icaval/values-list.php';
				break;

			case 'apprenticeships':
				$result = BZMI_Apprenticeship::paginate( $current_page, $per_page, $args );
				$items = $result['items'];
				$total = $result['total'];
				include BZMI_PLUGIN_DIR . 'templates/minds/admin/icaval/apprenticeships-list.php';
				break;
		}
	}

	/**
	 * Afficher le formulaire selon l'onglet
	 *
	 * @param string $tab Onglet.
	 * @return void
	 */
	private static function render_form( $tab ) {
		$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		$is_new = ! $id;

		switch ( $tab ) {
			case 'informations':
				$item = $id ? BZMI_Information::find( $id ) : new BZMI_Information();
				$projects = BZMI_Project::all( array( 'orderby' => 'name', 'order' => 'ASC' ) );
				$types = BZMI_Information::get_types();
				$priorities = BZMI_Information::get_priorities();
				$statuses = BZMI_Information::get_statuses();
				include BZMI_PLUGIN_DIR . 'templates/minds/admin/icaval/informations-form.php';
				break;

			case 'clarifications':
				$item = $id ? BZMI_Clarification::find( $id ) : new BZMI_Clarification();
				$information_id = isset( $_GET['information_id'] ) ? intval( $_GET['information_id'] ) : ( $item ? $item->information_id : 0 );
				$informations = BZMI_Information::all( array( 'orderby' => 'created_at', 'order' => 'DESC' ) );
				$statuses = BZMI_Clarification::get_statuses();
				include BZMI_PLUGIN_DIR . 'templates/minds/admin/icaval/clarifications-form.php';
				break;

			case 'actions':
				$item = $id ? BZMI_Action::find( $id ) : new BZMI_Action();
				$information_id = isset( $_GET['information_id'] ) ? intval( $_GET['information_id'] ) : ( $item ? $item->information_id : 0 );
				$informations = BZMI_Information::all( array( 'orderby' => 'created_at', 'order' => 'DESC' ) );
				$action_types = BZMI_Action::get_action_types();
				$priorities = BZMI_Action::get_priorities();
				$efforts = BZMI_Action::get_effort_estimates();
				$statuses = BZMI_Action::get_statuses();
				include BZMI_PLUGIN_DIR . 'templates/minds/admin/icaval/actions-form.php';
				break;

			case 'values':
				$item = $id ? BZMI_Value::find( $id ) : new BZMI_Value();
				$action_id = isset( $_GET['action_id'] ) ? intval( $_GET['action_id'] ) : ( $item ? $item->action_id : 0 );
				$actions = BZMI_Action::all( array( 'orderby' => 'created_at', 'order' => 'DESC' ) );
				$value_types = BZMI_Value::get_value_types();
				$statuses = BZMI_Value::get_statuses();
				include BZMI_PLUGIN_DIR . 'templates/minds/admin/icaval/values-form.php';
				break;

			case 'apprenticeships':
				$item = $id ? BZMI_Apprenticeship::find( $id ) : new BZMI_Apprenticeship();
				$source_types = BZMI_Apprenticeship::get_source_types();
				$lesson_types = BZMI_Apprenticeship::get_lesson_types();
				$statuses = BZMI_Apprenticeship::get_statuses();
				include BZMI_PLUGIN_DIR . 'templates/minds/admin/icaval/apprenticeships-form.php';
				break;
		}
	}

	/**
	 * Afficher la vue détaillée
	 *
	 * @param string $tab Onglet.
	 * @return void
	 */
	private static function render_view( $tab ) {
		$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

		switch ( $tab ) {
			case 'informations':
				$item = BZMI_Information::find( $id );
				if ( $item ) {
					$clarifications = $item->clarifications();
					$actions = $item->actions();
					include BZMI_PLUGIN_DIR . 'templates/minds/admin/icaval/informations-view.php';
				}
				break;

			case 'actions':
				$item = BZMI_Action::find( $id );
				if ( $item ) {
					$values = $item->values();
					include BZMI_PLUGIN_DIR . 'templates/minds/admin/icaval/actions-view.php';
				}
				break;

			default:
				// Rediriger vers la liste pour les autres onglets
				wp_safe_redirect( admin_url( 'admin.php?page=blazing-minds-icaval&tab=' . $tab ) );
				exit;
		}
	}

	/**
	 * Gérer les actions POST
	 *
	 * @param string $tab Onglet.
	 * @return void
	 */
	private static function handle_post_action( $tab ) {
		$post_action = sanitize_text_field( wp_unslash( $_POST['action'] ) );

		if ( 'save' !== $post_action ) {
			return;
		}

		switch ( $tab ) {
			case 'informations':
				self::save_information();
				break;
			case 'clarifications':
				self::save_clarification();
				break;
			case 'actions':
				self::save_action();
				break;
			case 'values':
				self::save_value();
				break;
			case 'apprenticeships':
				self::save_apprenticeship();
				break;
		}
	}

	/**
	 * Sauvegarder une information
	 *
	 * @return void
	 */
	private static function save_information() {
		BZMI_Admin::verify_nonce( 'bzmi_save_information' );

		$id = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;
		$item = $id ? BZMI_Information::find( $id ) : new BZMI_Information();

		$item->fill( array(
			'project_id'   => isset( $_POST['project_id'] ) ? intval( $_POST['project_id'] ) : 0,
			'type'         => isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'manual',
			'title'        => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
			'content'      => isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '',
			'priority'     => isset( $_POST['priority'] ) ? sanitize_text_field( wp_unslash( $_POST['priority'] ) ) : 'normal',
			'category'     => isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '',
			'tags'         => isset( $_POST['tags'] ) ? sanitize_text_field( wp_unslash( $_POST['tags'] ) ) : '',
			'status'       => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'new',
			'icaval_stage' => isset( $_POST['icaval_stage'] ) ? sanitize_text_field( wp_unslash( $_POST['icaval_stage'] ) ) : 'information',
			'assigned_to'  => isset( $_POST['assigned_to'] ) ? intval( $_POST['assigned_to'] ) : null,
		) );

		$result = $item->save_validated();

		if ( is_array( $result ) ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-icaval&tab=informations',
				implode( ' ', $result ),
				'error'
			);
		} else {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-icaval&tab=informations',
				$id ? __( 'Information mise à jour.', 'blazing-minds' ) : __( 'Information créée.', 'blazing-minds' ),
				'success'
			);
		}
	}

	/**
	 * Sauvegarder une clarification
	 *
	 * @return void
	 */
	private static function save_clarification() {
		BZMI_Admin::verify_nonce( 'bzmi_save_clarification' );

		$id = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;
		$item = $id ? BZMI_Clarification::find( $id ) : new BZMI_Clarification();

		$item->fill( array(
			'information_id' => isset( $_POST['information_id'] ) ? intval( $_POST['information_id'] ) : 0,
			'question'       => isset( $_POST['question'] ) ? sanitize_textarea_field( wp_unslash( $_POST['question'] ) ) : '',
			'answer'         => isset( $_POST['answer'] ) ? sanitize_textarea_field( wp_unslash( $_POST['answer'] ) ) : '',
			'status'         => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'pending',
		) );

		// Si une réponse est fournie, marquer comme résolu
		if ( ! empty( $item->answer ) && ! $item->resolved ) {
			$item->resolve( $item->answer );
		} else {
			$item->save_validated();
		}

		BZMI_Admin::redirect_with_message(
			'blazing-minds-icaval&tab=clarifications',
			$id ? __( 'Clarification mise à jour.', 'blazing-minds' ) : __( 'Clarification créée.', 'blazing-minds' ),
			'success'
		);
	}

	/**
	 * Sauvegarder une action
	 *
	 * @return void
	 */
	private static function save_action() {
		BZMI_Admin::verify_nonce( 'bzmi_save_action' );

		$id = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;
		$item = $id ? BZMI_Action::find( $id ) : new BZMI_Action();

		$item->fill( array(
			'information_id'  => isset( $_POST['information_id'] ) ? intval( $_POST['information_id'] ) : 0,
			'title'           => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
			'description'     => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
			'action_type'     => isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : 'task',
			'priority'        => isset( $_POST['priority'] ) ? sanitize_text_field( wp_unslash( $_POST['priority'] ) ) : 'normal',
			'effort_estimate' => isset( $_POST['effort_estimate'] ) ? sanitize_text_field( wp_unslash( $_POST['effort_estimate'] ) ) : '',
			'due_date'        => isset( $_POST['due_date'] ) ? sanitize_text_field( wp_unslash( $_POST['due_date'] ) ) : null,
			'status'          => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'pending',
			'assigned_to'     => isset( $_POST['assigned_to'] ) ? intval( $_POST['assigned_to'] ) : null,
		) );

		$result = $item->save_validated();

		if ( is_array( $result ) ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-icaval&tab=actions',
				implode( ' ', $result ),
				'error'
			);
		} else {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-icaval&tab=actions',
				$id ? __( 'Action mise à jour.', 'blazing-minds' ) : __( 'Action créée.', 'blazing-minds' ),
				'success'
			);
		}
	}

	/**
	 * Sauvegarder une valeur
	 *
	 * @return void
	 */
	private static function save_value() {
		BZMI_Admin::verify_nonce( 'bzmi_save_value' );

		$id = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;
		$item = $id ? BZMI_Value::find( $id ) : new BZMI_Value();

		$item->fill( array(
			'action_id'             => isset( $_POST['action_id'] ) ? intval( $_POST['action_id'] ) : 0,
			'value_type'            => isset( $_POST['value_type'] ) ? sanitize_text_field( wp_unslash( $_POST['value_type'] ) ) : 'business',
			'title'                 => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
			'description'           => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
			'monetary_value'        => isset( $_POST['monetary_value'] ) ? floatval( $_POST['monetary_value'] ) : 0,
			'time_saved'            => isset( $_POST['time_saved'] ) ? intval( $_POST['time_saved'] ) : 0,
			'satisfaction_increase' => isset( $_POST['satisfaction_increase'] ) ? intval( $_POST['satisfaction_increase'] ) : 0,
			'status'                => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'estimated',
		) );

		$result = $item->save_validated();

		if ( is_array( $result ) ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-icaval&tab=values',
				implode( ' ', $result ),
				'error'
			);
		} else {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-icaval&tab=values',
				$id ? __( 'Valeur mise à jour.', 'blazing-minds' ) : __( 'Valeur créée.', 'blazing-minds' ),
				'success'
			);
		}
	}

	/**
	 * Sauvegarder un apprentissage
	 *
	 * @return void
	 */
	private static function save_apprenticeship() {
		BZMI_Admin::verify_nonce( 'bzmi_save_apprenticeship' );

		$id = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;
		$item = $id ? BZMI_Apprenticeship::find( $id ) : new BZMI_Apprenticeship();

		$item->fill( array(
			'source_type'     => isset( $_POST['source_type'] ) ? sanitize_text_field( wp_unslash( $_POST['source_type'] ) ) : 'information',
			'source_id'       => isset( $_POST['source_id'] ) ? intval( $_POST['source_id'] ) : 0,
			'lesson_type'     => isset( $_POST['lesson_type'] ) ? sanitize_text_field( wp_unslash( $_POST['lesson_type'] ) ) : 'insight',
			'title'           => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
			'description'     => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
			'context'         => isset( $_POST['context'] ) ? sanitize_textarea_field( wp_unslash( $_POST['context'] ) ) : '',
			'recommendations' => isset( $_POST['recommendations'] ) ? sanitize_textarea_field( wp_unslash( $_POST['recommendations'] ) ) : '',
			'tags'            => isset( $_POST['tags'] ) ? sanitize_text_field( wp_unslash( $_POST['tags'] ) ) : '',
			'reusable'        => isset( $_POST['reusable'] ) ? 1 : 0,
			'status'          => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active',
		) );

		$result = $item->save_validated();

		if ( is_array( $result ) ) {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-icaval&tab=apprenticeships',
				implode( ' ', $result ),
				'error'
			);
		} else {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-icaval&tab=apprenticeships',
				$id ? __( 'Apprentissage mis à jour.', 'blazing-minds' ) : __( 'Apprentissage créé.', 'blazing-minds' ),
				'success'
			);
		}
	}

	/**
	 * Gérer la suppression
	 *
	 * @param string $tab Onglet.
	 * @return void
	 */
	private static function handle_delete( $tab ) {
		$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

		BZMI_Admin::verify_nonce( 'bzmi_delete_' . $tab . '_' . $id );

		$model_map = array(
			'informations'    => 'BZMI_Information',
			'clarifications'  => 'BZMI_Clarification',
			'actions'         => 'BZMI_Action',
			'values'          => 'BZMI_Value',
			'apprenticeships' => 'BZMI_Apprenticeship',
		);

		if ( ! isset( $model_map[ $tab ] ) ) {
			return;
		}

		$class = $model_map[ $tab ];
		$item = $class::find( $id );

		if ( $item ) {
			$item->delete();
			BZMI_Admin::redirect_with_message(
				'blazing-minds-icaval&tab=' . $tab,
				__( 'Élément supprimé avec succès.', 'blazing-minds' ),
				'success'
			);
		} else {
			BZMI_Admin::redirect_with_message(
				'blazing-minds-icaval&tab=' . $tab,
				__( 'Élément introuvable.', 'blazing-minds' ),
				'error'
			);
		}
	}
}
