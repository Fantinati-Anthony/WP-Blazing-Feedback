<?php
/**
 * Handlers AJAX (save, delete, reorder)
 * 
 * Reference file for options-manager.php lines 1200-1600
 * See main file: includes/options-manager.php
 * 
 * @package Blazing_Feedback
 */

// To view this section, read options-manager.php with:
// offset=1200, limit=401

            }
        }

        if ( ! $found ) {
            $items[] = $new_item;
        }

        // Sauvegarder
        self::save_items_by_type( $option_type, $items );

        wp_send_json_success( array( 'item' => $new_item ) );
    }

    /**
     * AJAX: Supprimer un élément
     *
     * @since 1.1.0
     * @return void
     */
    public static function ajax_delete_item() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $option_type = isset( $_POST['option_type'] ) ? sanitize_key( $_POST['option_type'] ) : '';
        $item_id     = isset( $_POST['item_id'] ) ? sanitize_key( $_POST['item_id'] ) : '';

        if ( empty( $option_type ) || empty( $item_id ) ) {
            wp_send_json_error( __( 'Données invalides.', 'blazing-feedback' ) );
        }

        // Obtenir les items existants
        $items = self::get_items_by_type( $option_type );

        // Supprimer l'élément
        $items = array_filter( $items, function( $item ) use ( $item_id ) {
            return $item['id'] !== $item_id;
        } );
        $items = array_values( $items ); // Réindexer

        // Sauvegarder
        self::save_items_by_type( $option_type, $items );

        wp_send_json_success();
    }

    /**
     * AJAX: Créer un groupe personnalisé
     *
     * @since 1.3.0
     * @return void
     */
    public static function ajax_create_custom_group() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';

        if ( empty( $name ) ) {
            wp_send_json_error( __( 'Le nom du groupe est requis.', 'blazing-feedback' ) );
        }

        $group = self::create_custom_group( $name );

        if ( ! $group ) {
            wp_send_json_error( __( 'Erreur lors de la création du groupe.', 'blazing-feedback' ) );
        }

        wp_send_json_success( array(
            'group'       => $group,
            'redirect_url' => admin_url( 'admin.php?page=wpvfh-options&tab=' . $group['slug'] ),
        ) );
    }

    /**
     * AJAX: Supprimer un groupe personnalisé
     *
     * @since 1.3.0
     * @return void
     */
    public static function ajax_delete_custom_group() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $slug = isset( $_POST['slug'] ) ? sanitize_key( $_POST['slug'] ) : '';

        if ( empty( $slug ) ) {
            wp_send_json_error( __( 'Slug du groupe requis.', 'blazing-feedback' ) );
        }

        if ( self::is_default_group( $slug ) ) {
            wp_send_json_error( __( 'Les groupes par défaut ne peuvent pas être supprimés.', 'blazing-feedback' ) );
        }

        if ( ! self::delete_custom_group( $slug ) ) {
            wp_send_json_error( __( 'Erreur lors de la suppression du groupe.', 'blazing-feedback' ) );
        }

        wp_send_json_success( array(
            'redirect_url' => admin_url( 'admin.php?page=wpvfh-options&tab=statuses' ),
        ) );
    }

    /**
     * AJAX: Renommer un groupe personnalisé
     *
     * @since 1.4.0
     * @return void
     */
    public static function ajax_rename_custom_group() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $slug = isset( $_POST['slug'] ) ? sanitize_key( $_POST['slug'] ) : '';
        $name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';

        if ( empty( $slug ) || empty( $name ) ) {
            wp_send_json_error( __( 'Slug et nom du groupe requis.', 'blazing-feedback' ) );
        }

        if ( self::is_default_group( $slug ) ) {
            wp_send_json_error( __( 'Les groupes par défaut ne peuvent pas être renommés.', 'blazing-feedback' ) );
        }

        if ( ! self::rename_custom_group( $slug, $name ) ) {
            wp_send_json_error( __( 'Erreur lors du renommage du groupe.', 'blazing-feedback' ) );
        }

        wp_send_json_success( array(
            'name' => $name,
        ) );
    }

    /**
     * AJAX: Sauvegarder les paramètres d'un groupe
     *
     * @since 1.4.0
     * @return void
     */
    public static function ajax_save_group_settings() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $slug = isset( $_POST['slug'] ) ? sanitize_key( $_POST['slug'] ) : '';

        if ( empty( $slug ) ) {
            wp_send_json_error( __( 'Slug du groupe requis.', 'blazing-feedback' ) );
        }

        // Récupérer et nettoyer les paramètres
        $enabled = isset( $_POST['enabled'] ) && $_POST['enabled'] === 'true';
        $required = isset( $_POST['required'] ) && $_POST['required'] === 'true';
        $show_in_sidebar = isset( $_POST['show_in_sidebar'] ) && $_POST['show_in_sidebar'] === 'true';
        $ai_prompt = isset( $_POST['ai_prompt'] ) ? sanitize_textarea_field( $_POST['ai_prompt'] ) : '';

        $allowed_roles = array();
        if ( ! empty( $_POST['allowed_roles'] ) ) {
            $allowed_roles = array_map( 'sanitize_key', explode( ',', $_POST['allowed_roles'] ) );
            $allowed_roles = array_filter( $allowed_roles );
        }

        $allowed_users = array();
        if ( ! empty( $_POST['allowed_users'] ) ) {
            $allowed_users = array_map( 'intval', explode( ',', $_POST['allowed_users'] ) );
            $allowed_users = array_filter( $allowed_users );
        }

        $settings = array(
            'enabled'         => $enabled,
            'required'        => $required,
            'show_in_sidebar' => $show_in_sidebar,
            'allowed_roles'   => $allowed_roles,
            'allowed_users'   => $allowed_users,
            'ai_prompt'       => $ai_prompt,
        );

        if ( ! self::save_group_settings( $slug, $settings ) ) {
            wp_send_json_error( __( 'Erreur lors de la sauvegarde des paramètres.', 'blazing-feedback' ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Paramètres enregistrés.', 'blazing-feedback' ),
        ) );
    }

    /**
     * Rendu de la page d'options
     *
     * @since 1.1.0
     * @return void
     */
    public static function render_options_page() {
        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_die( esc_html__( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'statuses';
        $tabs = self::get_all_tabs();
        $custom_groups = self::get_custom_groups();
        $is_custom_tab = isset( $custom_groups[ $current_tab ] );

        // Message de confirmation
        $message = '';
        if ( isset( $_GET['reset'] ) ) {
            $message = '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Métadatas réinitialisées avec succès.', 'blazing-feedback' ) . '</p></div>';
        }
        if ( isset( $_GET['created'] ) ) {
            $message = '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Groupe créé avec succès.', 'blazing-feedback' ) . '</p></div>';
        }
        ?>
        <div class="wrap wpvfh-options-page">
            <h1><?php esc_html_e( 'Métadatas', 'blazing-feedback' ); ?></h1>

            <?php echo $message; ?>

            <nav class="nav-tab-wrapper wpvfh-nav-tabs">
                <?php foreach ( $tabs as $tab_id => $tab_label ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpvfh-options&tab=' . $tab_id ) ); ?>"
                       class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>"
                       data-tab="<?php echo esc_attr( $tab_id ); ?>"
                       data-deletable="<?php echo ! self::is_default_group( $tab_id ) ? 'true' : 'false'; ?>">
                        <?php echo esc_html( $tab_label ); ?>
                        <?php if ( ! self::is_default_group( $tab_id ) ) : ?>
                            <span class="wpvfh-tab-delete" title="<?php esc_attr_e( 'Supprimer ce groupe', 'blazing-feedback' ); ?>">&times;</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
                <button type="button" class="nav-tab wpvfh-add-group-btn" title="<?php esc_attr_e( 'Ajouter un nouveau groupe', 'blazing-feedback' ); ?>">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php esc_html_e( 'Ajouter', 'blazing-feedback' ); ?>
                </button>
            </nav>

            <div class="wpvfh-options-content">
                <?php
                switch ( $current_tab ) {
                    case 'statuses':
                        self::render_statuses_tab();
                        break;
                    case 'types':
                        self::render_types_tab();
                        break;
                    case 'priorities':
                        self::render_priorities_tab();
                        break;
                    case 'tags':
                        self::render_tags_tab();
                        break;
                    default:
                        // Groupe personnalisé
                        if ( $is_custom_tab ) {
                            self::render_custom_group_tab( $current_tab, $custom_groups[ $current_tab ] );
                        }
                        break;
                }
                ?>
            </div>
        </div>

        <!-- Modal pour créer un nouveau groupe -->
        <div id="wpvfh-new-group-modal" class="wpvfh-modal">
            <div class="wpvfh-modal-content">
                <div class="wpvfh-modal-header">
                    <h2><?php esc_html_e( 'Nouveau groupe de métadatas', 'blazing-feedback' ); ?></h2>
                    <button type="button" class="wpvfh-modal-close">&times;</button>
                </div>
                <div class="wpvfh-modal-body">
                    <p><?php esc_html_e( 'Créez un nouveau groupe de métadatas personnalisé pour vos feedbacks.', 'blazing-feedback' ); ?></p>
                    <div class="wpvfh-form-group">
                        <label for="wpvfh-new-group-name"><?php esc_html_e( 'Nom du groupe', 'blazing-feedback' ); ?></label>
                        <input type="text" id="wpvfh-new-group-name" class="regular-text" placeholder="<?php esc_attr_e( 'Ex: Catégories, Départements, etc.', 'blazing-feedback' ); ?>">
                    </div>
                </div>
                <div class="wpvfh-modal-footer">
                    <button type="button" class="button wpvfh-modal-cancel"><?php esc_html_e( 'Annuler', 'blazing-feedback' ); ?></button>
                    <button type="button" class="button button-primary wpvfh-create-group-btn"><?php esc_html_e( 'Créer le groupe', 'blazing-feedback' ); ?></button>
                </div>
            </div>
        </div>

        <!-- Emoji Picker Popup -->
        <div id="wpvfh-emoji-picker" class="wpvfh-emoji-picker">
            <div class="wpvfh-emoji-picker-header">
                <div class="wpvfh-emoji-tabs">
                    <button type="button" class="wpvfh-emoji-tab active" data-category="smileys" title="<?php esc_attr_e( 'Smileys', 'blazing-feedback' ); ?>">😀</button>
                    <button type="button" class="wpvfh-emoji-tab" data-category="gestures" title="<?php esc_attr_e( 'Gestes', 'blazing-feedback' ); ?>">👍</button>
                    <button type="button" class="wpvfh-emoji-tab" data-category="symbols" title="<?php esc_attr_e( 'Symboles', 'blazing-feedback' ); ?>">❤️</button>
                    <button type="button" class="wpvfh-emoji-tab" data-category="objects" title="<?php esc_attr_e( 'Objets', 'blazing-feedback' ); ?>">📦</button>
                    <button type="button" class="wpvfh-emoji-tab" data-category="nature" title="<?php esc_attr_e( 'Nature', 'blazing-feedback' ); ?>">🌿</button>
                    <button type="button" class="wpvfh-emoji-tab" data-category="flags" title="<?php esc_attr_e( 'Drapeaux', 'blazing-feedback' ); ?>">🚩</button>
                </div>
            </div>
            <div class="wpvfh-emoji-picker-content">
                <div class="wpvfh-emoji-grid" data-category="smileys">
                    <?php
                    $smileys = array( '😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '😮‍💨', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '🥴', '😵', '🤯', '🤠', '🥳', '🥸', '😎', '🤓', '🧐', '😕', '😟', '🙁', '☹️', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '🥱', '😤', '😡', '😠', '🤬', '😈', '👿', '💀', '☠️', '💩', '🤡', '👹', '👺', '👻', '👽', '👾', '🤖' );
                    foreach ( $smileys as $e ) {
                        echo '<span class="wpvfh-emoji-item">' . esc_html( $e ) . '</span>';
                    }
                    ?>
                </div>
                <div class="wpvfh-emoji-grid" data-category="gestures" style="display: none;">
                    <?php
                    $gestures = array( '👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅', '🤳', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠', '🫀', '🫁', '🦷', '🦴', '👀', '👁️', '👅', '👄', '👶', '🧒', '👦', '👧', '🧑', '👱', '👨', '🧔', '👩', '🧓', '👴', '👵', '🙍', '🙎', '🙅', '🙆', '💁', '🙋', '🧏', '🙇', '🤦', '🤷', '👮', '🕵️', '💂', '🥷', '👷', '🤴', '👸', '👳', '👲', '🧕', '🤵', '👰', '🤰', '🤱', '👼', '🎅', '🤶', '🦸', '🦹', '🧙', '🧚', '🧛', '🧜', '🧝', '🧞', '🧟', '💆', '💇', '🚶', '🧍', '🧎', '🏃', '💃', '🕺', '🕴️', '👯', '🧖', '🧗', '🤸', '🏌️', '🏇', '⛷️', '🏂', '🏋️', '🤼', '🤽', '🤾', '🤺', '⛹️', '🏊', '🚣', '🧘', '🛀', '🛌' );
                    foreach ( $gestures as $e ) {
                        echo '<span class="wpvfh-emoji-item">' . esc_html( $e ) . '</span>';
                    }
                    ?>
                </div>
                <div class="wpvfh-emoji-grid" data-category="symbols" style="display: none;">
                    <?php
                    $symbols = array( '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '☮️', '✝️', '☪️', '🕉️', '☸️', '✡️', '🔯', '🕎', '☯️', '☦️', '🛐', '⛎', '♈', '♉', '♊', '♋', '♌', '♍', '♎', '♏', '♐', '♑', '♒', '♓', '🆔', '⚛️', '🉑', '☢️', '☣️', '📴', '📳', '🈶', '🈚', '🈸', '🈺', '🈷️', '✴️', '🆚', '💮', '🉐', '㊙️', '㊗️', '🈴', '🈵', '🈹', '🈲', '🅰️', '🅱️', '🆎', '🆑', '🅾️', '🆘', '❌', '⭕', '🛑', '⛔', '📛', '🚫', '💯', '💢', '♨️', '🚷', '🚯', '🚳', '🚱', '🔞', '📵', '🚭', '❗', '❕', '❓', '❔', '‼️', '⁉️', '🔅', '🔆', '〽️', '⚠️', '🚸', '🔱', '⚜️', '🔰', '♻️', '✅', '🈯', '💹', '❇️', '✳️', '❎', '🌐', '💠', 'Ⓜ️', '🌀', '💤', '🏧', '🚾', '♿', '🅿️', '🛗', '🈳', '🈂️', '🛂', '🛃', '🛄', '🛅', '🚹', '🚺', '🚼', '⚧️', '🚻', '🚮', '🎦', '📶', '🈁', '🔣', 'ℹ️', '🔤', '🔡', '🔠', '🆖', '🆗', '🆙', '🆒', '🆕', '🆓', '0️⃣', '1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟', '🔢', '#️⃣', '*️⃣', '⏏️', '▶️', '⏸️', '⏯️', '⏹️', '⏺️', '⏭️', '⏮️', '⏩', '⏪', '⏫', '⏬', '◀️', '🔼', '🔽', '➡️', '⬅️', '⬆️', '⬇️', '↗️', '↘️', '↙️', '↖️', '↕️', '↔️', '↪️', '↩️', '⤴️', '⤵️', '🔀', '🔁', '🔂', '🔄', '🔃', '🎵', '🎶', '➕', '➖', '➗', '✖️', '🟰', '♾️', '💲', '💱', '™️', '©️', '®️', '〰️', '➰', '➿', '🔚', '🔙', '🔛', '🔝', '🔜', '✔️', '☑️', '🔘', '🔴', '🟠', '🟡', '🟢', '🔵', '🟣', '⚫', '⚪', '🟤', '🔺', '🔻', '🔸', '🔹', '🔶', '🔷', '🔳', '🔲', '▪️', '▫️', '◾', '◽', '◼️', '◻️', '🟥', '🟧', '🟨', '🟩', '🟦', '🟪', '⬛', '⬜', '🟫', '🔈', '🔇', '🔉', '🔊', '🔔', '🔕', '📣', '📢', '💬', '💭', '🗯️', '♠️', '♣️', '♥️', '♦️', '🃏', '🎴', '🀄', '🕐', '🕑', '🕒', '🕓', '🕔', '🕕', '🕖', '🕗', '🕘', '🕙', '🕚', '🕛', '🕜', '🕝', '🕞', '🕟', '🕠', '🕡', '🕢', '🕣', '🕤', '🕥', '🕦', '🕧' );
                    foreach ( $symbols as $e ) {
                        echo '<span class="wpvfh-emoji-item">' . esc_html( $e ) . '</span>';
                    }
                    ?>
                </div>
                <div class="wpvfh-emoji-grid" data-category="objects" style="display: none;">
                    <?php
                    $objects = array( '📌', '📍', '📎', '🖇️', '📏', '📐', '✂️', '🗃️', '🗄️', '🗑️', '🔒', '🔓', '🔏', '🔐', '🔑', '🗝️', '🔨', '🪓', '⛏️', '⚒️', '🛠️', '🗡️', '⚔️', '🔫', '🪃', '🏹', '🛡️', '🪚', '🔧', '🪛', '🔩', '⚙️', '🗜️', '⚖️', '🦯', '🔗', '⛓️', '🪝', '🧰', '🧲', '🪜', '⚗️', '🧪', '🧫', '🧬', '🔬', '🔭', '📡', '💉', '🩸', '💊', '🩹', '🩺', '🚪', '🛗', '🪞', '🪟', '🛏️', '🛋️', '🪑', '🚽', '🪠', '🚿', '🛁', '🪤', '🪒', '🧴', '🧷', '🧹', '🧺', '🧻', '🪣', '🧼', '🪥', '🧽', '🧯', '🛒', '🚬', '⚰️', '🪦', '⚱️', '🗿', '🪧', '🏧', '🎰', '💎', '💍', '👑', '👒', '🎩', '🎓', '🧢', '⛑️', '📿', '💄', '💼', '🎒', '🧳', '👓', '🕶️', '🥽', '🌂', '☂️', '🧵', '🪡', '🧶', '👔', '👕', '👖', '🧣', '🧤', '🧥', '🧦', '👗', '👘', '🥻', '🩱', '🩲', '🩳', '👙', '👚', '👛', '👜', '👝', '🛍️', '🎀', '💰', '💴', '💵', '💶', '💷', '💸', '💳', '🧾', '💹', '📱', '📲', '☎️', '📞', '📟', '📠', '🔋', '🔌', '💻', '🖥️', '🖨️', '⌨️', '🖱️', '🖲️', '💽', '💾', '💿', '📀', '🧮', '🎥', '🎞️', '📽️', '🎬', '📺', '📷', '📸', '📹', '📼', '🔍', '🔎', '🕯️', '💡', '🔦', '🏮', '🪔', '📔', '📕', '📖', '📗', '📘', '📙', '📚', '📓', '📒', '📃', '📜', '📄', '📰', '🗞️', '📑', '🔖', '🏷️', '✉️', '📧', '📨', '📩', '📤', '📥', '📦', '📫', '📪', '📬', '📭', '📮', '🗳️', '✏️', '✒️', '🖋️', '🖊️', '🖌️', '🖍️', '📝', '📁', '📂', '🗂️', '📅', '📆', '🗒️', '🗓️', '📇', '📈', '📉', '📊', '🎁', '🎀', '🎈', '🎉', '🎊', '🎄', '🎃', '🪅', '🪆', '🎋', '🎍', '🎎', '🎏', '🎐', '🎑', '🧧', '🪄', '🎮', '🕹️', '🎲', '🧩', '🧸', '🪀', '🪁', '♟️', '🎯', '🎳', '🎱', '🔮', '🪬', '🧿', '🎼', '🎤', '🎧', '🎷', '🪗', '🎸', '🎹', '🎺', '🎻', '🪕', '🥁', '🪘' );
                    foreach ( $objects as $e ) {
                        echo '<span class="wpvfh-emoji-item">' . esc_html( $e ) . '</span>';
                    }
                    ?>
                </div>
                <div class="wpvfh-emoji-grid" data-category="nature" style="display: none;">
                    <?php
                    $nature = array( '🌿', '🍀', '🌱', '🌲', '🌳', '🌴', '🌵', '🌾', '🌷', '🌸', '🌹', '🌺', '🌻', '🌼', '💐', '🍁', '🍂', '🍃', '🪴', '🪻', '🪷', '🪹', '🪺', '🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐻‍❄️', '🐨', '🐯', '🦁', '🐮', '🐷', '🐽', '🐸', '🐵', '🙈', '🙉', '🙊', '🐒', '🐔', '🐧', '🐦', '🐤', '🐣', '🐥', '🦆', '🦅', '🦉', '🦇', '🐺', '🐗', '🐴', '🦄', '🐝', '🪱', '🐛', '🦋', '🐌', '🐞', '🐜', '🪰', '🪲', '🪳', '🦟', '🦗', '🕷️', '🕸️', '🦂', '🐢', '🐍', '🦎', '🦖', '🦕', '🐙', '🦑', '🦐', '🦞', '🦀', '🐡', '🐠', '🐟', '🐬', '🐳', '🐋', '🦈', '🐊', '🐅', '🐆', '🦓', '🦍', '🦧', '🦣', '🐘', '🦛', '🦏', '🐪', '🐫', '🦒', '🦘', '🦬', '🐃', '🐂', '🐄', '🐎', '🐖', '🐏', '🐑', '🦙', '🐐', '🦌', '🐕', '🐩', '🦮', '🐕‍🦺', '🐈', '🐈‍⬛', '🪶', '🐓', '🦃', '🦤', '🦚', '🦜', '🦢', '🦩', '🕊️', '🐇', '🦝', '🦨', '🦡', '🦫', '🦦', '🦥', '🐁', '🐀', '🐿️', '🦔', '🐾', '🐉', '🐲', '🌵', '🎄', '🌲', '🌳', '🌴', '🪵', '🌱', '🌿', '☘️', '🍀', '🎍', '🪴', '🎋', '🍃', '🍂', '🍁', '🍄', '🐚', '🪸', '🪨', '🌾', '💐', '🌷', '🌹', '🥀', '🪻', '🌺', '🌸', '🌼', '🌻', '🌞', '🌝', '🌛', '🌜', '🌚', '🌕', '🌖', '🌗', '🌘', '🌑', '🌒', '🌓', '🌔', '🌙', '🌎', '🌍', '🌏', '🪐', '💫', '⭐', '🌟', '✨', '⚡', '☄️', '💥', '🔥', '🌪️', '🌈', '☀️', '🌤️', '⛅', '🌥️', '☁️', '🌦️', '🌧️', '⛈️', '🌩️', '🌨️', '❄️', '☃️', '⛄', '🌬️', '💨', '💧', '💦', '☔', '☂️', '🌊', '🌫️' );
                    foreach ( $nature as $e ) {
                        echo '<span class="wpvfh-emoji-item">' . esc_html( $e ) . '</span>';
                    }
                    ?>
                </div>
                <div class="wpvfh-emoji-grid" data-category="flags" style="display: none;">
                    <?php
                    $flags = array( '🚩', '🏁', '🎌', '🏴', '🏳️', '🏳️‍🌈', '🏳️‍⚧️', '🏴‍☠️', '🇦🇫', '🇦🇱', '🇩🇿', '🇦🇸', '🇦🇩', '🇦🇴', '🇦🇮', '🇦🇶', '🇦🇬', '🇦🇷', '🇦🇲', '🇦🇼', '🇦🇺', '🇦🇹', '🇦🇿', '🇧🇸', '🇧🇭', '🇧🇩', '🇧🇧', '🇧🇾', '🇧🇪', '🇧🇿', '🇧🇯', '🇧🇲', '🇧🇹', '🇧🇴', '🇧🇦', '🇧🇼', '🇧🇷', '🇮🇴', '🇻🇬', '🇧🇳', '🇧🇬', '🇧🇫', '🇧🇮', '🇰🇭', '🇨🇲', '🇨🇦', '🇮🇨', '🇨🇻', '🇧🇶', '🇰🇾', '🇨🇫', '🇹🇩', '🇨🇱', '🇨🇳', '🇨🇽', '🇨🇨', '🇨🇴', '🇰🇲', '🇨🇬', '🇨🇩', '🇨🇰', '🇨🇷', '🇨🇮', '🇭🇷', '🇨🇺', '🇨🇼', '🇨🇾', '🇨🇿', '🇩🇰', '🇩🇯', '🇩🇲', '🇩🇴', '🇪🇨', '🇪🇬', '🇸🇻', '🇬🇶', '🇪🇷', '🇪🇪', '🇸🇿', '🇪🇹', '🇪🇺', '🇫🇰', '🇫🇴', '🇫🇯', '🇫🇮', '🇫🇷', '🇬🇫', '🇵🇫', '🇹🇫', '🇬🇦', '🇬🇲', '🇬🇪', '🇩🇪', '🇬🇭', '🇬🇮', '🇬🇷', '🇬🇱', '🇬🇩', '🇬🇵', '🇬🇺', '🇬🇹', '🇬🇬', '🇬🇳', '🇬🇼', '🇬🇾', '🇭🇹', '🇭🇳', '🇭🇰', '🇭🇺', '🇮🇸', '🇮🇳', '🇮🇩', '🇮🇷', '🇮🇶', '🇮🇪', '🇮🇲', '🇮🇱', '🇮🇹', '🇯🇲', '🇯🇵', '🎌', '🇯🇪', '🇯🇴', '🇰🇿', '🇰🇪', '🇰🇮', '🇽🇰', '🇰🇼', '🇰🇬', '🇱🇦', '🇱🇻', '🇱🇧', '🇱🇸', '🇱🇷', '🇱🇾', '🇱🇮', '🇱🇹', '🇱🇺', '🇲🇴', '🇲🇬', '🇲🇼', '🇲🇾', '🇲🇻', '🇲🇱', '🇲🇹', '🇲🇭', '🇲🇶', '🇲🇷', '🇲🇺', '🇾🇹', '🇲🇽', '🇫🇲', '🇲🇩', '🇲🇨', '🇲🇳', '🇲🇪', '🇲🇸', '🇲🇦', '🇲🇿', '🇲🇲', '🇳🇦', '🇳🇷', '🇳🇵', '🇳🇱', '🇳🇨', '🇳🇿', '🇳🇮', '🇳🇪', '🇳🇬', '🇳🇺', '🇳🇫', '🇰🇵', '🇲🇰', '🇲🇵', '🇳🇴', '🇴🇲', '🇵🇰', '🇵🇼', '🇵🇸', '🇵🇦', '🇵🇬', '🇵🇾', '🇵🇪', '🇵🇭', '🇵🇳', '🇵🇱', '🇵🇹', '🇵🇷', '🇶🇦', '🇷🇪', '🇷🇴', '🇷🇺', '🇷🇼', '🇼🇸', '🇸🇲', '🇸🇹', '🇸🇦', '🇸🇳', '🇷🇸', '🇸🇨', '🇸🇱', '🇸🇬', '🇸🇽', '🇸🇰', '🇸🇮', '🇬🇸', '🇸🇧', '🇸🇴', '🇿🇦', '🇰🇷', '🇸🇸', '🇪🇸', '🇱🇰', '🇧🇱', '🇸🇭', '🇰🇳', '🇱🇨', '🇵🇲', '🇻🇨', '🇸🇩', '🇸🇷', '🇸🇪', '🇨🇭', '🇸🇾', '🇹🇼', '🇹🇯', '🇹🇿', '🇹🇭', '🇹🇱', '🇹🇬', '🇹🇰', '🇹🇴', '🇹🇹', '🇹🇳', '🇹🇷', '🇹🇲', '🇹🇨', '🇹🇻', '🇻🇮', '🇺🇬', '🇺🇦', '🇦🇪', '🇬🇧', '🏴󠁧󠁢󠁥󠁮󠁧󠁿', '🏴󠁧󠁢󠁳󠁣󠁴󠁿', '🏴󠁧󠁢󠁷󠁬󠁳󠁿', '🇺🇳', '🇺🇸', '🇺🇾', '🇺🇿', '🇻🇺', '🇻🇦', '🇻🇪', '🇻🇳', '🇼🇫', '🇪🇭', '🇾🇪', '🇿🇲', '🇿🇼' );
                    foreach ( $flags as $e ) {
                        echo '<span class="wpvfh-emoji-item">' . esc_html( $e ) . '</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Rendu d'un onglet de groupe personnalisé
     *
     * @since 1.3.0
     * @param string $slug  Slug du groupe
     * @param array  $group Données du groupe
     * @return void
     */
    private static function render_custom_group_tab( $slug, $group ) {
        $items = self::get_custom_group_items( $slug );
        self::render_items_table( $slug, $items, $group['name'] );
    }

    /**
     * Rendu de l'onglet Statuts
     *
     * @since 1.1.0
     * @return void
     */
    private static function render_statuses_tab() {
        $statuses = self::get_statuses();
        self::render_items_table( 'statuses', $statuses );
    }

    /**
     * Rendu de l'onglet Types
     *
     * @since 1.1.0
     * @return void
     */
    private static function render_types_tab() {
        $types = self::get_types();
        self::render_items_table( 'types', $types );
    }

    /**
     * Rendu de l'onglet Priorités
     *
     * @since 1.1.0
     * @return void
     */