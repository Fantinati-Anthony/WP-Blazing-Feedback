<?php
/**
 * Gestionnaire des métadonnées personnalisables (Types, Priorités, Tags, Statuts)
 *
 * @package WP_Visual_Feedback_Hub
 * @since 1.1.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Charger les traits
require_once WPVFH_PLUGIN_DIR . 'includes/options/trait-options-init.php';
require_once WPVFH_PLUGIN_DIR . 'includes/options/trait-options-defaults.php';
require_once WPVFH_PLUGIN_DIR . 'includes/options/trait-options-metadata-crud.php';
require_once WPVFH_PLUGIN_DIR . 'includes/options/trait-options-custom-groups.php';
require_once WPVFH_PLUGIN_DIR . 'includes/options/trait-options-group-settings.php';
require_once WPVFH_PLUGIN_DIR . 'includes/options/trait-options-helpers.php';
require_once WPVFH_PLUGIN_DIR . 'includes/options/trait-options-ajax-handlers.php';
require_once WPVFH_PLUGIN_DIR . 'includes/options/trait-options-rendering.php';

/**
 * Classe de gestion des options
 *
 * @since 1.1.0
 */
class WPVFH_Options_Manager {

    use WPVFH_Options_Init_Trait;
    use WPVFH_Options_Defaults_Trait;
    use WPVFH_Options_Metadata_CRUD_Trait;
    use WPVFH_Options_Custom_Groups_Trait;
    use WPVFH_Options_Group_Settings_Trait;
    use WPVFH_Options_Helpers_Trait;
    use WPVFH_Options_Ajax_Handlers_Trait;
    use WPVFH_Options_Rendering_Trait;

    /**
     * Clés des options
     */
    const OPTION_TYPES         = 'wpvfh_feedback_types';
    const OPTION_PRIORITIES    = 'wpvfh_feedback_priorities';
    const OPTION_TAGS          = 'wpvfh_feedback_tags';
    const OPTION_STATUSES      = 'wpvfh_feedback_statuses';
    const OPTION_CUSTOM_GROUPS = 'wpvfh_custom_option_groups';
    const OPTION_GROUP_SETTINGS = 'wpvfh_group_settings';

    /**
     * Groupes par défaut (non supprimables)
     */
    private static $default_groups = array( 'statuses', 'types', 'priorities', 'tags' );
}
