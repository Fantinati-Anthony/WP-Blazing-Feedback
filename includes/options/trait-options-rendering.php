<?php
/**
 * Trait pour le rendu des pages d'options
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait pour le rendu des pages d'options
 *
 * @since 1.7.0
 */
trait WPVFH_Options_Rendering_Trait {

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
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=bzmi-metadata&tab=' . $tab_id ) ); ?>"
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
    private static function render_priorities_tab() {
        $priorities = self::get_priorities();
        self::render_items_table( 'priorities', $priorities );
    }

    /**
     * Rendu de l'onglet Tags
     *
     * @since 1.1.0
     * @return void
     */
    private static function render_tags_tab() {
        $tags = self::get_predefined_tags();
        self::render_items_table( 'tags', $tags );
    }

    /**
     * Rendu du tableau d'éléments
     *
     * @since 1.1.0
     * @param string      $type       Type d'option (types, priorities, tags, statuses, ou slug personnalisé)
     * @param array       $items      Éléments à afficher
     * @param string|null $group_name Nom du groupe (pour groupes personnalisés)
     * @return void
     */
    private static function render_items_table( $type, $items, $group_name = null ) {
        $reset_url = wp_nonce_url(
            admin_url( 'admin.php?page=bzmi-metadata&tab=' . $type . '&action=reset' ),
            'wpvfh_reset_options'
        );

        // Obtenir les paramètres du groupe
        $group_settings = self::get_group_settings( $type );
        $is_custom_group = ! self::is_default_group( $type );

        // Préparer les labels d'accès pour affichage
        $access_labels = array();
        foreach ( $group_settings['allowed_roles'] as $role ) {
            $role_name = wp_roles()->get_names()[ $role ] ?? $role;
            $access_labels[] = array(
                'type'  => 'role',
                'id'    => $role,
                'label' => $role_name,
            );
        }
        foreach ( $group_settings['allowed_users'] as $user_id ) {
            $user = get_user_by( 'id', $user_id );
            if ( $user ) {
                $access_labels[] = array(
                    'type'  => 'user',
                    'id'    => $user_id,
                    'label' => $user->display_name,
                );
            }
        }
        ?>
        <!-- Paramètres du groupe -->
        <div class="wpvfh-group-settings-panel" data-group="<?php echo esc_attr( $type ); ?>">
            <div class="wpvfh-group-settings-header">
                <div class="wpvfh-group-title-section">
                    <?php if ( $is_custom_group && $group_name ) : ?>
                        <h3 class="wpvfh-group-title">
                            <span class="wpvfh-group-name-display"><?php echo esc_html( $group_name ); ?></span>
                            <input type="text" class="wpvfh-group-name-input" value="<?php echo esc_attr( $group_name ); ?>" style="display: none;">
                            <button type="button" class="wpvfh-rename-group-btn" title="<?php esc_attr_e( 'Renommer', 'blazing-feedback' ); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                        </h3>
                    <?php else : ?>
                        <h3 class="wpvfh-group-title"><?php echo esc_html( self::get_all_tabs()[ $type ] ?? $type ); ?></h3>
                    <?php endif; ?>
                    <p class="description">
                        <?php
                        switch ( $type ) {
                            case 'statuses':
                                esc_html_e( 'Définissez les statuts des feedbacks. Glissez-déposez pour réorganiser.', 'blazing-feedback' );
                                break;
                            case 'types':
                                esc_html_e( 'Définissez les types de feedback disponibles. Glissez-déposez pour réorganiser.', 'blazing-feedback' );
                                break;
                            case 'priorities':
                                esc_html_e( 'Définissez les niveaux de priorité disponibles. Glissez-déposez pour réorganiser.', 'blazing-feedback' );
                                break;
                            case 'tags':
                                esc_html_e( 'Définissez les tags prédéfinis. Les utilisateurs peuvent aussi créer leurs propres tags.', 'blazing-feedback' );
                                break;
                            default:
                                if ( $group_name ) {
                                    esc_html_e( 'Gérez les métadatas de ce groupe. Glissez-déposez pour réorganiser.', 'blazing-feedback' );
                                }
                                break;
                        }
                        ?>
                    </p>
                </div>
                <div class="wpvfh-group-settings-toggle">
                    <label class="wpvfh-toggle">
                        <input type="checkbox" class="wpvfh-group-enabled" <?php checked( $group_settings['enabled'] ); ?>>
                        <span class="wpvfh-toggle-slider"></span>
                    </label>
                    <span class="wpvfh-toggle-label"><?php esc_html_e( 'Activé', 'blazing-feedback' ); ?></span>
                    <span class="wpvfh-toggle-separator">|</span>
                    <label class="wpvfh-toggle">
                        <input type="checkbox" class="wpvfh-group-required" <?php checked( $group_settings['required'] ); ?>>
                        <span class="wpvfh-toggle-slider"></span>
                    </label>
                    <span class="wpvfh-toggle-label"><?php esc_html_e( 'Obligatoire', 'blazing-feedback' ); ?></span>
                    <span class="wpvfh-toggle-separator">|</span>
                    <label class="wpvfh-toggle">
                        <input type="checkbox" class="wpvfh-group-show-in-sidebar" <?php checked( $group_settings['show_in_sidebar'] ); ?>>
                        <span class="wpvfh-toggle-slider"></span>
                    </label>
                    <span class="wpvfh-toggle-label"><?php esc_html_e( 'Sidebar', 'blazing-feedback' ); ?></span>
                    <button type="button" class="button wpvfh-group-settings-btn" title="<?php esc_attr_e( 'Paramètres du groupe', 'blazing-feedback' ); ?>">
                        <span class="dashicons dashicons-admin-generic"></span>
                    </button>
                </div>
            </div>
            <div class="wpvfh-group-settings-body" style="display: none;">
                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group">
                        <label><?php esc_html_e( 'Accès autorisé (vide = tous)', 'blazing-feedback' ); ?></label>
                        <div class="wpvfh-access-control">
                            <div class="wpvfh-access-search-wrapper">
                                <input type="text" class="wpvfh-access-search wpvfh-group-access-search" placeholder="<?php esc_attr_e( 'Rechercher un rôle ou utilisateur...', 'blazing-feedback' ); ?>">
                                <div class="wpvfh-access-dropdown" style="display: none;"></div>
                            </div>
                            <div class="wpvfh-access-tags wpvfh-group-access-tags">
                                <?php foreach ( $access_labels as $access ) : ?>
                                    <span class="wpvfh-access-tag" data-type="<?php echo esc_attr( $access['type'] ); ?>" data-id="<?php echo esc_attr( $access['id'] ); ?>">
                                        <?php echo esc_html( $access['label'] ); ?>
                                        <button type="button" class="wpvfh-access-tag-remove">&times;</button>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" class="wpvfh-group-allowed-roles" value="<?php echo esc_attr( implode( ',', $group_settings['allowed_roles'] ) ); ?>">
                            <input type="hidden" class="wpvfh-group-allowed-users" value="<?php echo esc_attr( implode( ',', $group_settings['allowed_users'] ) ); ?>">
                        </div>
                        <p class="description"><?php esc_html_e( 'Si vide, tous les utilisateurs peuvent voir ce groupe.', 'blazing-feedback' ); ?></p>
                    </div>
                </div>
                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group">
                        <label><?php esc_html_e( 'Prompt IA pour ce groupe (optionnel)', 'blazing-feedback' ); ?></label>
                        <textarea class="wpvfh-group-ai-prompt large-text" rows="3" placeholder="<?php esc_attr_e( 'Instructions pour l\'IA pour toutes les métadatas de ce groupe...', 'blazing-feedback' ); ?>"><?php echo esc_textarea( $group_settings['ai_prompt'] ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Ce prompt sera utilisé par l\'IA pour traiter les feedbacks utilisant ce groupe de métadatas.', 'blazing-feedback' ); ?></p>
                    </div>
                </div>
                <div class="wpvfh-form-actions">
                    <button type="button" class="button button-primary wpvfh-save-group-settings-btn">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e( 'Enregistrer les paramètres du groupe', 'blazing-feedback' ); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="wpvfh-options-header">
            <p class="description">
                <?php esc_html_e( 'Éléments de ce groupe :', 'blazing-feedback' ); ?>
            </p>
            <div class="wpvfh-options-actions">
                <button type="button" class="button button-primary wpvfh-add-item-btn" data-type="<?php echo esc_attr( $type ); ?>">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php esc_html_e( 'Ajouter', 'blazing-feedback' ); ?>
                </button>
                <a href="<?php echo esc_url( $reset_url ); ?>" class="button"
                   onclick="return confirm('<?php esc_attr_e( 'Réinitialiser aux valeurs par défaut ?', 'blazing-feedback' ); ?>');">
                    <span class="dashicons dashicons-image-rotate"></span>
                    <?php esc_html_e( 'Réinitialiser', 'blazing-feedback' ); ?>
                </a>
            </div>
        </div>

        <div class="wpvfh-items-list" data-type="<?php echo esc_attr( $type ); ?>">
            <?php foreach ( $items as $item ) : ?>
                <?php self::render_item_card( $type, $item ); ?>
            <?php endforeach; ?>
        </div>

        <!-- Template pour nouvel élément -->
        <script type="text/template" id="wpvfh-item-template-<?php echo esc_attr( $type ); ?>">
            <?php
            self::render_item_card( $type, array(
                'id'            => '',
                'label'         => '',
                'emoji'         => '📌',
                'color'         => '#666666',
                'display_mode'  => 'emoji',
                'enabled'       => true,
                'ai_prompt'     => '',
                'allowed_roles' => array(),
                'allowed_users' => array(),
            ), true );
            ?>
        </script>
        <?php
    }

    /**
     * Rendu d'une carte d'élément
     *
     * @since 1.2.0
     * @param string $type   Type d'option
     * @param array  $item   Données de l'élément
     * @param bool   $is_new Est un nouvel élément
     * @return void
     */
    private static function render_item_card( $type, $item, $is_new = false ) {
        $item = self::normalize_item( $item );
        $id           = $item['id'];
        $label        = $item['label'];
        $emoji        = $item['emoji'];
        $color        = $item['color'];
        $display_mode = $item['display_mode'];
        $enabled      = $item['enabled'];
        $is_treated   = isset( $item['is_treated'] ) ? $item['is_treated'] : false;
        $ai_prompt    = $item['ai_prompt'];
        $allowed_roles = $item['allowed_roles'];
        $allowed_users = $item['allowed_users'];

        // Obtenir les noms des rôles/utilisateurs pour l'affichage
        $access_labels = array();
        $roles = wp_roles()->get_names();
        foreach ( $allowed_roles as $role ) {
            if ( isset( $roles[ $role ] ) ) {
                $access_labels[] = array( 'type' => 'role', 'id' => $role, 'label' => '👥 ' . $roles[ $role ] );
            }
        }
        foreach ( $allowed_users as $user_id ) {
            $user = get_user_by( 'id', $user_id );
            if ( $user ) {
                $access_labels[] = array( 'type' => 'user', 'id' => $user_id, 'label' => '👤 ' . $user->display_name );
            }
        }
        ?>
        <div class="wpvfh-option-card <?php echo $is_new ? 'wpvfh-new-item' : ''; ?> <?php echo ! $enabled ? 'wpvfh-disabled' : ''; ?>" data-id="<?php echo esc_attr( $id ); ?>">
            <div class="wpvfh-card-header">
                <span class="wpvfh-drag-handle dashicons dashicons-menu"></span>
                <div class="wpvfh-card-preview">
                    <?php if ( $display_mode === 'emoji' ) : ?>
                        <span class="wpvfh-preview-emoji"><?php echo esc_html( $emoji ); ?></span>
                    <?php else : ?>
                        <span class="wpvfh-preview-dot" style="background-color: <?php echo esc_attr( $color ); ?>;"></span>
                    <?php endif; ?>
                    <span class="wpvfh-preview-label"><?php echo esc_html( $label ?: __( 'Nouveau', 'blazing-feedback' ) ); ?></span>
                </div>
                <div class="wpvfh-card-actions">
                    <label class="wpvfh-toggle">
                        <input type="checkbox" class="wpvfh-enabled-toggle" <?php checked( $enabled ); ?>>
                        <span class="wpvfh-toggle-slider"></span>
                    </label>
                    <button type="button" class="wpvfh-expand-btn" title="<?php esc_attr_e( 'Développer', 'blazing-feedback' ); ?>">
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <button type="button" class="button wpvfh-delete-item-btn" title="<?php esc_attr_e( 'Supprimer', 'blazing-feedback' ); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>

            <div class="wpvfh-card-body" style="display: none;">
                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group wpvfh-form-group-half">
                        <label><?php esc_html_e( 'Label', 'blazing-feedback' ); ?></label>
                        <input type="text" class="wpvfh-label-input regular-text" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'Label...', 'blazing-feedback' ); ?>">
                    </div>
                    <div class="wpvfh-form-group wpvfh-form-group-quarter">
                        <label><?php esc_html_e( 'Couleur', 'blazing-feedback' ); ?></label>
                        <input type="text" class="wpvfh-color-input" value="<?php echo esc_attr( $color ); ?>" data-default-color="<?php echo esc_attr( $color ); ?>">
                    </div>
                </div>

                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group">
                        <label><?php esc_html_e( 'Mode d\'affichage', 'blazing-feedback' ); ?></label>
                        <div class="wpvfh-display-mode-selector">
                            <label class="wpvfh-radio-card <?php echo $display_mode === 'emoji' ? 'selected' : ''; ?>">
                                <input type="radio" name="display_mode_<?php echo esc_attr( $id ?: 'new' ); ?>" value="emoji" <?php checked( $display_mode, 'emoji' ); ?>>
                                <span class="wpvfh-radio-content">
                                    <span class="wpvfh-radio-icon"><?php echo esc_html( $emoji ); ?></span>
                                    <span class="wpvfh-radio-label"><?php esc_html_e( 'Emoji', 'blazing-feedback' ); ?></span>
                                </span>
                            </label>
                            <label class="wpvfh-radio-card <?php echo $display_mode === 'color_dot' ? 'selected' : ''; ?>">
                                <input type="radio" name="display_mode_<?php echo esc_attr( $id ?: 'new' ); ?>" value="color_dot" <?php checked( $display_mode, 'color_dot' ); ?>>
                                <span class="wpvfh-radio-content">
                                    <span class="wpvfh-radio-dot" style="background-color: <?php echo esc_attr( $color ); ?>;"></span>
                                    <span class="wpvfh-radio-label"><?php esc_html_e( 'Rond coloré', 'blazing-feedback' ); ?></span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="wpvfh-form-row wpvfh-emoji-row" style="<?php echo $display_mode !== 'emoji' ? 'display: none;' : ''; ?>">
                    <div class="wpvfh-form-group">
                        <label><?php esc_html_e( 'Emoji', 'blazing-feedback' ); ?></label>
                        <div class="wpvfh-emoji-input-wrapper">
                            <input type="text" class="wpvfh-emoji-input" value="<?php echo esc_attr( $emoji ); ?>" maxlength="4" readonly>
                            <button type="button" class="button wpvfh-emoji-picker-btn" title="<?php esc_attr_e( 'Choisir un emoji', 'blazing-feedback' ); ?>">
                                <span class="dashicons dashicons-smiley"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group">
                        <label><?php esc_html_e( 'Accès autorisé (vide = tous)', 'blazing-feedback' ); ?></label>
                        <div class="wpvfh-access-control">
                            <div class="wpvfh-access-search-wrapper">
                                <input type="text" class="wpvfh-access-search" placeholder="<?php esc_attr_e( 'Rechercher un rôle ou utilisateur...', 'blazing-feedback' ); ?>">
                                <div class="wpvfh-access-dropdown" style="display: none;"></div>
                            </div>
                            <div class="wpvfh-access-tags">
                                <?php foreach ( $access_labels as $access ) : ?>
                                    <span class="wpvfh-access-tag" data-type="<?php echo esc_attr( $access['type'] ); ?>" data-id="<?php echo esc_attr( $access['id'] ); ?>">
                                        <?php echo esc_html( $access['label'] ); ?>
                                        <button type="button" class="wpvfh-access-tag-remove">&times;</button>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" class="wpvfh-allowed-roles" value="<?php echo esc_attr( implode( ',', $allowed_roles ) ); ?>">
                            <input type="hidden" class="wpvfh-allowed-users" value="<?php echo esc_attr( implode( ',', $allowed_users ) ); ?>">
                        </div>
                        <p class="description"><?php esc_html_e( 'Si vide, tous les utilisateurs peuvent utiliser cette métadata.', 'blazing-feedback' ); ?></p>
                    </div>
                </div>

                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group">
                        <label><?php esc_html_e( 'Prompt IA (optionnel)', 'blazing-feedback' ); ?></label>
                        <textarea class="wpvfh-ai-prompt large-text" rows="3" placeholder="<?php esc_attr_e( 'Instructions pour l\'IA lors du traitement de ce type de feedback...', 'blazing-feedback' ); ?>"><?php echo esc_textarea( $ai_prompt ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Ce prompt sera utilisé par l\'IA pour traiter les feedbacks de ce type.', 'blazing-feedback' ); ?></p>
                    </div>
                </div>

                <?php if ( 'statuses' === $type ) : ?>
                <div class="wpvfh-form-row">
                    <div class="wpvfh-form-group">
                        <label class="wpvfh-checkbox-label">
                            <input type="checkbox" class="wpvfh-is-treated-toggle" <?php checked( $is_treated ); ?>>
                            <span><?php esc_html_e( 'Considéré comme traité', 'blazing-feedback' ); ?></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Les feedbacks avec ce statut seront considérés comme traités et masqués par défaut dans le widget.', 'blazing-feedback' ); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="wpvfh-form-actions">
                    <button type="button" class="button button-primary wpvfh-save-item-btn">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e( 'Enregistrer', 'blazing-feedback' ); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}
