<?php
/**
 * Database management class
 *
 * Handles custom tables creation, migration, and CRUD operations
 *
 * @package Blazing_Feedback
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPVFH_Database
 *
 * Manages custom database tables for the plugin
 */
class WPVFH_Database {

    /**
     * Database version
     */
    const DB_VERSION = '1.3.0';

    /**
     * Option name for database version
     */
    const DB_VERSION_OPTION = 'wpvfh_db_version';

    /**
     * Table names (without WordPress prefix)
     * Final table names will be: {wp_prefix}blazingfeedback_{table}
     */
    const TABLE_FEEDBACKS       = 'blazingfeedback_feedbacks';
    const TABLE_REPLIES         = 'blazingfeedback_replies';
    const TABLE_METADATA_TYPES  = 'blazingfeedback_metadata_types';
    const TABLE_METADATA_ITEMS  = 'blazingfeedback_metadata_items';
    const TABLE_CUSTOM_GROUPS   = 'blazingfeedback_custom_groups';
    const TABLE_GROUP_SETTINGS  = 'blazingfeedback_group_settings';

    /**
     * Get table name with WordPress base prefix
     *
     * Uses base_prefix for multisite compatibility (shared tables across network)
     *
     * @param string $table Table name constant.
     * @return string Full table name with prefix.
     */
    public static function get_table_name( $table ) {
        global $wpdb;
        return $wpdb->base_prefix . $table;
    }

    /**
     * Install database tables
     */
    public static function install() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $current_version = get_option( self::DB_VERSION_OPTION, '0.0.0' );

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Create all tables
        self::create_feedbacks_table( $charset_collate );
        self::create_replies_table( $charset_collate );
        self::create_metadata_types_table( $charset_collate );
        self::create_metadata_items_table( $charset_collate );
        self::create_custom_groups_table( $charset_collate );
        self::create_group_settings_table( $charset_collate );

        // Run migrations if updating from a previous version
        if ( $current_version !== '0.0.0' && version_compare( $current_version, self::DB_VERSION, '<' ) ) {
            self::run_db_migrations( $current_version );
        }

        // Update database version
        update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
    }

    /**
     * Create feedbacks table
     *
     * @param string $charset_collate Charset collate string.
     */
    private static function create_feedbacks_table( $charset_collate ) {
        $table_name = self::get_table_name( self::TABLE_FEEDBACKS );

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned DEFAULT NULL,
            guest_name varchar(255) DEFAULT NULL,
            guest_email varchar(255) DEFAULT NULL,
            comment text NOT NULL,
            url varchar(2083) NOT NULL,
            page_path varchar(500) NOT NULL,
            position_x decimal(10,6) NOT NULL,
            position_y decimal(10,6) NOT NULL,
            selector varchar(500) DEFAULT NULL,
            element_offset_x decimal(10,6) DEFAULT NULL,
            element_offset_y decimal(10,6) DEFAULT NULL,
            scroll_x int(11) DEFAULT 0,
            scroll_y int(11) DEFAULT 0,
            screenshot_id bigint(20) unsigned DEFAULT NULL,
            screen_width int(11) DEFAULT NULL,
            screen_height int(11) DEFAULT NULL,
            viewport_width int(11) DEFAULT NULL,
            viewport_height int(11) DEFAULT NULL,
            device_pixel_ratio varchar(20) DEFAULT NULL,
            color_depth varchar(20) DEFAULT NULL,
            orientation varchar(50) DEFAULT NULL,
            browser varchar(100) DEFAULT NULL,
            browser_version varchar(50) DEFAULT NULL,
            os varchar(100) DEFAULT NULL,
            os_version varchar(50) DEFAULT NULL,
            device varchar(100) DEFAULT NULL,
            platform varchar(100) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            language varchar(50) DEFAULT NULL,
            languages varchar(255) DEFAULT NULL,
            timezone varchar(100) DEFAULT NULL,
            timezone_offset varchar(20) DEFAULT NULL,
            local_time varchar(50) DEFAULT NULL,
            cookies_enabled tinyint(1) DEFAULT 1,
            online tinyint(1) DEFAULT 1,
            touch_support tinyint(1) DEFAULT 0,
            max_touch_points int(11) DEFAULT 0,
            device_memory varchar(20) DEFAULT NULL,
            hardware_concurrency varchar(20) DEFAULT NULL,
            connection_type varchar(50) DEFAULT NULL,
            referrer varchar(2083) DEFAULT NULL,
            status varchar(50) NOT NULL DEFAULT 'new',
            priority varchar(50) NOT NULL DEFAULT 'none',
            feedback_type varchar(50) NOT NULL DEFAULT 'bug',
            tags text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY priority (priority),
            KEY feedback_type (feedback_type),
            KEY page_path (page_path(191)),
            KEY created_at (created_at),
            KEY url (url(191))
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Create replies table
     *
     * @param string $charset_collate Charset collate string.
     */
    private static function create_replies_table( $charset_collate ) {
        $table_name = self::get_table_name( self::TABLE_REPLIES );

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            feedback_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            author_name varchar(255) DEFAULT NULL,
            author_email varchar(255) DEFAULT NULL,
            content text NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY feedback_id (feedback_id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Create metadata types table
     *
     * @param string $charset_collate Charset collate string.
     */
    private static function create_metadata_types_table( $charset_collate ) {
        $table_name = self::get_table_name( self::TABLE_METADATA_TYPES );

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            type_group varchar(50) NOT NULL,
            slug varchar(100) NOT NULL,
            label varchar(255) NOT NULL,
            emoji varchar(50) DEFAULT NULL,
            color varchar(20) DEFAULT NULL,
            display_mode varchar(50) DEFAULT 'emoji',
            sort_order int(11) NOT NULL DEFAULT 0,
            enabled tinyint(1) NOT NULL DEFAULT 1,
            is_treated tinyint(1) NOT NULL DEFAULT 0,
            ai_prompt text DEFAULT NULL,
            allowed_roles text DEFAULT NULL,
            allowed_users text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY type_group_slug (type_group, slug),
            KEY type_group (type_group),
            KEY enabled (enabled),
            KEY sort_order (sort_order)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Create metadata items table (for custom group items)
     *
     * @param string $charset_collate Charset collate string.
     */
    private static function create_metadata_items_table( $charset_collate ) {
        $table_name = self::get_table_name( self::TABLE_METADATA_ITEMS );

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            group_id bigint(20) unsigned NOT NULL,
            slug varchar(100) NOT NULL,
            label varchar(255) NOT NULL,
            emoji varchar(50) DEFAULT NULL,
            color varchar(20) DEFAULT NULL,
            display_mode varchar(50) DEFAULT 'emoji',
            sort_order int(11) NOT NULL DEFAULT 0,
            enabled tinyint(1) NOT NULL DEFAULT 1,
            ai_prompt text DEFAULT NULL,
            allowed_roles text DEFAULT NULL,
            allowed_users text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY group_id (group_id),
            KEY slug (slug),
            KEY enabled (enabled),
            KEY sort_order (sort_order)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Create custom groups table
     *
     * @param string $charset_collate Charset collate string.
     */
    private static function create_custom_groups_table( $charset_collate ) {
        $table_name = self::get_table_name( self::TABLE_CUSTOM_GROUPS );

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            slug varchar(100) NOT NULL,
            name varchar(255) NOT NULL,
            sort_order int(11) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY sort_order (sort_order)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Create group settings table
     *
     * @param string $charset_collate Charset collate string.
     */
    private static function create_group_settings_table( $charset_collate ) {
        $table_name = self::get_table_name( self::TABLE_GROUP_SETTINGS );

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            group_slug varchar(100) NOT NULL,
            enabled tinyint(1) NOT NULL DEFAULT 1,
            required tinyint(1) NOT NULL DEFAULT 0,
            show_in_sidebar tinyint(1) NOT NULL DEFAULT 1,
            hide_empty_sections tinyint(1) NOT NULL DEFAULT 0,
            sort_order int(11) NOT NULL DEFAULT 0,
            allowed_roles text DEFAULT NULL,
            allowed_users text DEFAULT NULL,
            ai_prompt text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY group_slug (group_slug),
            KEY sort_order (sort_order)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Check if database needs update
     *
     * @return bool True if update needed.
     */
    public static function needs_update() {
        $current_version = get_option( self::DB_VERSION_OPTION, '0.0.0' );
        return version_compare( $current_version, self::DB_VERSION, '<' );
    }

    /**
     * Run database migrations
     *
     * @param string $from_version Version to migrate from.
     */
    public static function run_db_migrations( $from_version ) {
        // Migration 1.1.0: Add is_treated column to metadata_types and set default values
        if ( version_compare( $from_version, '1.1.0', '<' ) ) {
            self::migrate_110_is_treated();
        }

        // Migration 1.2.0: Add hide_empty_sections column to group_settings
        if ( version_compare( $from_version, '1.2.0', '<' ) ) {
            self::migrate_120_hide_empty_sections();
        }

        // Migration 1.3.0: Add sort_order column to group_settings
        if ( version_compare( $from_version, '1.3.0', '<' ) ) {
            self::migrate_130_sort_order();
        }
    }

    /**
     * Migration 1.3.0: Add sort_order column to group_settings
     *
     * @since 1.3.0
     */
    private static function migrate_130_sort_order() {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_GROUP_SETTINGS );

        // Check if table exists
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )
        );

        if ( ! $table_exists ) {
            return;
        }

        // Check if column exists, if not add it
        $column_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'sort_order'",
                DB_NAME,
                $table_name
            )
        );

        if ( ! $column_exists ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query( "ALTER TABLE $table_name ADD COLUMN sort_order int(11) NOT NULL DEFAULT 0 AFTER hide_empty_sections" );

            // Set default order for standard groups
            $default_order = array(
                'statuses'   => 0,
                'types'      => 1,
                'priorities' => 2,
                'tags'       => 3,
            );

            foreach ( $default_order as $slug => $order ) {
                $wpdb->update(
                    $table_name,
                    array( 'sort_order' => $order ),
                    array( 'group_slug' => $slug ),
                    array( '%d' ),
                    array( '%s' )
                );
            }
        }
    }

    /**
     * Migration 1.2.0: Add hide_empty_sections column to group_settings
     *
     * @since 1.2.0
     */
    private static function migrate_120_hide_empty_sections() {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_GROUP_SETTINGS );

        // Check if table exists
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )
        );

        if ( ! $table_exists ) {
            return;
        }

        // Check if column exists, if not add it
        $column_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'hide_empty_sections'",
                DB_NAME,
                $table_name
            )
        );

        if ( ! $column_exists ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query( "ALTER TABLE $table_name ADD COLUMN hide_empty_sections tinyint(1) NOT NULL DEFAULT 0 AFTER show_in_sidebar" );
        }
    }

    /**
     * Migration 1.1.0: Add is_treated field to statuses
     *
     * Sets is_treated = 1 for resolved and rejected statuses
     */
    private static function migrate_110_is_treated() {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_METADATA_TYPES );

        // Check if column exists, if not add it
        $column_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = %s AND COLUMN_NAME = 'is_treated'",
                $table_name
            )
        );

        if ( ! $column_exists ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query( "ALTER TABLE $table_name ADD COLUMN is_treated tinyint(1) NOT NULL DEFAULT 0 AFTER enabled" );
        }

        // Set is_treated = 1 for resolved and rejected statuses
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $table_name SET is_treated = 1 WHERE type_group = %s AND slug IN ('resolved', 'rejected')",
                'statuses'
            )
        );
    }

    /**
     * Drop all custom tables
     */
    public static function uninstall() {
        global $wpdb;

        $tables = array(
            self::TABLE_FEEDBACKS,
            self::TABLE_REPLIES,
            self::TABLE_METADATA_TYPES,
            self::TABLE_METADATA_ITEMS,
            self::TABLE_CUSTOM_GROUPS,
            self::TABLE_GROUP_SETTINGS,
        );

        foreach ( $tables as $table ) {
            $table_name = self::get_table_name( $table );
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
        }

        delete_option( self::DB_VERSION_OPTION );
    }

    /**
     * Truncate all tables (empty data but keep structure)
     *
     * @return bool True on success.
     */
    public static function truncate_all_tables() {
        global $wpdb;

        $tables = array(
            self::TABLE_FEEDBACKS,
            self::TABLE_REPLIES,
            self::TABLE_METADATA_TYPES,
            self::TABLE_METADATA_ITEMS,
            self::TABLE_CUSTOM_GROUPS,
            self::TABLE_GROUP_SETTINGS,
        );

        foreach ( $tables as $table ) {
            $table_name = self::get_table_name( $table );
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query( "TRUNCATE TABLE $table_name" );
        }

        return true;
    }

    /**
     * Truncate only feedback-related tables (feedbacks and replies)
     *
     * @return bool True on success.
     */
    public static function truncate_feedback_tables() {
        global $wpdb;

        $tables = array(
            self::TABLE_FEEDBACKS,
            self::TABLE_REPLIES,
        );

        foreach ( $tables as $table ) {
            $table_name = self::get_table_name( $table );
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query( "TRUNCATE TABLE $table_name" );
        }

        return true;
    }

    /**
     * Get table statistics
     *
     * @return array Array of table stats.
     */
    public static function get_table_stats() {
        global $wpdb;

        $stats = array();

        $tables = array(
            'feedbacks'      => self::TABLE_FEEDBACKS,
            'replies'        => self::TABLE_REPLIES,
            'metadata_types' => self::TABLE_METADATA_TYPES,
            'metadata_items' => self::TABLE_METADATA_ITEMS,
            'custom_groups'  => self::TABLE_CUSTOM_GROUPS,
            'group_settings' => self::TABLE_GROUP_SETTINGS,
        );

        foreach ( $tables as $key => $table ) {
            $table_name = self::get_table_name( $table );
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
            $stats[ $key ] = array(
                'table' => $table_name,
                'count' => (int) $count,
            );
        }

        return $stats;
    }

    /**
     * Check if tables exist
     *
     * @return bool True if all tables exist.
     */
    public static function tables_exist() {
        global $wpdb;

        $tables = array(
            self::TABLE_FEEDBACKS,
            self::TABLE_REPLIES,
            self::TABLE_METADATA_TYPES,
            self::TABLE_METADATA_ITEMS,
            self::TABLE_CUSTOM_GROUPS,
            self::TABLE_GROUP_SETTINGS,
        );

        foreach ( $tables as $table ) {
            $table_name = self::get_table_name( $table );
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SHOW TABLES LIKE %s",
                    $table_name
                )
            );
            if ( ! $exists ) {
                return false;
            }
        }

        return true;
    }

    // =========================================================================
    // FEEDBACK CRUD OPERATIONS
    // =========================================================================

    /**
     * Insert a new feedback
     *
     * @param array $data Feedback data.
     * @return int|false Inserted ID or false on failure.
     */
    public static function insert_feedback( $data ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_FEEDBACKS );

        $defaults = array(
            'user_id'             => null,
            'guest_name'          => null,
            'guest_email'         => null,
            'comment'             => '',
            'url'                 => '',
            'page_path'           => '',
            'position_x'          => 0,
            'position_y'          => 0,
            'selector'            => null,
            'element_offset_x'    => null,
            'element_offset_y'    => null,
            'scroll_x'            => 0,
            'scroll_y'            => 0,
            'screenshot_id'       => null,
            'screen_width'        => null,
            'screen_height'       => null,
            'viewport_width'      => null,
            'viewport_height'     => null,
            'device_pixel_ratio'  => null,
            'color_depth'         => null,
            'orientation'         => null,
            'browser'             => null,
            'browser_version'     => null,
            'os'                  => null,
            'os_version'          => null,
            'device'              => null,
            'platform'            => null,
            'user_agent'          => null,
            'language'            => null,
            'languages'           => null,
            'timezone'            => null,
            'timezone_offset'     => null,
            'local_time'          => null,
            'cookies_enabled'     => 1,
            'online'              => 1,
            'touch_support'       => 0,
            'max_touch_points'    => 0,
            'device_memory'       => null,
            'hardware_concurrency' => null,
            'connection_type'     => null,
            'referrer'            => null,
            'status'              => 'new',
            'priority'            => 'none',
            'feedback_type'       => 'bug',
            'tags'                => null,
        );

        $data = wp_parse_args( $data, $defaults );

        $result = $wpdb->insert( $table_name, $data );

        if ( false === $result ) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Get a feedback by ID
     *
     * @param int $id Feedback ID.
     * @return object|null Feedback object or null.
     */
    public static function get_feedback( $id ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_FEEDBACKS );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ) );
    }

    /**
     * Get feedbacks with filters
     *
     * @param array $args Query arguments.
     * @return array Array of feedback objects.
     */
    public static function get_feedbacks( $args = array() ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_FEEDBACKS );

        $defaults = array(
            'status'        => null,
            'priority'      => null,
            'feedback_type' => null,
            'user_id'       => null,
            'url'           => null,
            'page_path'     => null,
            'search'        => null,
            'orderby'       => 'created_at',
            'order'         => 'DESC',
            'limit'         => 20,
            'offset'        => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $where = array( '1=1' );
        $values = array();

        if ( ! empty( $args['status'] ) ) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if ( ! empty( $args['priority'] ) ) {
            $where[] = 'priority = %s';
            $values[] = $args['priority'];
        }

        if ( ! empty( $args['feedback_type'] ) ) {
            $where[] = 'feedback_type = %s';
            $values[] = $args['feedback_type'];
        }

        if ( ! empty( $args['user_id'] ) ) {
            $where[] = 'user_id = %d';
            $values[] = $args['user_id'];
        }

        if ( ! empty( $args['url'] ) ) {
            $where[] = 'url = %s';
            $values[] = $args['url'];
        }

        if ( ! empty( $args['page_path'] ) ) {
            $where[] = 'page_path = %s';
            $values[] = $args['page_path'];
        }

        if ( ! empty( $args['search'] ) ) {
            $where[] = '(comment LIKE %s OR guest_name LIKE %s OR guest_email LIKE %s)';
            $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
        }

        $where_clause = implode( ' AND ', $where );

        // Sanitize orderby
        $allowed_orderby = array( 'id', 'created_at', 'updated_at', 'status', 'priority', 'feedback_type' );
        $orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
        $order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        $limit = absint( $args['limit'] );
        $offset = absint( $args['offset'] );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = "SELECT * FROM $table_name WHERE $where_clause ORDER BY $orderby $order LIMIT $limit OFFSET $offset";

        if ( ! empty( $values ) ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $sql = $wpdb->prepare( $sql, $values );
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $wpdb->get_results( $sql );
    }

    /**
     * Get feedbacks by URL
     *
     * @param string $url The URL to filter by.
     * @return array Array of feedback objects.
     */
    public static function get_feedbacks_by_url( $url ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_FEEDBACKS );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE url = %s ORDER BY created_at DESC",
                $url
            )
        );
    }

    /**
     * Get feedbacks by page path (more flexible matching)
     *
     * @param string $page_path The page path to filter by (without query string).
     * @param array  $args      Optional arguments (include_resolved, user_id).
     * @return array Array of feedback objects.
     */
    public static function get_feedbacks_by_page_path( $page_path, $args = array() ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_FEEDBACKS );

        $where = array( '1=1' );
        $values = array();

        // Correspondance par page_path (avec ou sans trailing slash)
        $path_clean = rtrim( $page_path, '/' );
        if ( empty( $path_clean ) ) {
            $path_clean = '/';
        }

        // Chercher le path exact ou avec trailing slash
        if ( '/' === $path_clean ) {
            $where[] = 'page_path = %s';
            $values[] = '/';
        } else {
            $where[] = '(page_path = %s OR page_path = %s)';
            $values[] = $path_clean;
            $values[] = $path_clean . '/';
        }

        // Exclure les feedbacks résolus/rejetés si demandé
        if ( empty( $args['include_resolved'] ) ) {
            $where[] = "status NOT IN ('resolved', 'rejected')";
        }

        // Filtrer par utilisateur si spécifié
        if ( ! empty( $args['user_id'] ) ) {
            $where[] = 'user_id = %d';
            $values[] = absint( $args['user_id'] );
        }

        $where_clause = implode( ' AND ', $where );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = "SELECT * FROM $table_name WHERE $where_clause ORDER BY created_at DESC";

        if ( ! empty( $values ) ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $sql = $wpdb->prepare( $sql, $values );
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $wpdb->get_results( $sql );
    }

    /**
     * Count feedbacks with filters
     *
     * @param array $args Query arguments.
     * @return int Count of feedbacks.
     */
    public static function count_feedbacks( $args = array() ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_FEEDBACKS );

        $where = array( '1=1' );
        $values = array();

        if ( ! empty( $args['status'] ) ) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if ( ! empty( $args['priority'] ) ) {
            $where[] = 'priority = %s';
            $values[] = $args['priority'];
        }

        if ( ! empty( $args['feedback_type'] ) ) {
            $where[] = 'feedback_type = %s';
            $values[] = $args['feedback_type'];
        }

        $where_clause = implode( ' AND ', $where );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = "SELECT COUNT(*) FROM $table_name WHERE $where_clause";

        if ( ! empty( $values ) ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $sql = $wpdb->prepare( $sql, $values );
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return (int) $wpdb->get_var( $sql );
    }

    /**
     * Update a feedback
     *
     * @param int   $id   Feedback ID.
     * @param array $data Data to update.
     * @return bool True on success, false on failure.
     */
    public static function update_feedback( $id, $data ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_FEEDBACKS );

        $result = $wpdb->update(
            $table_name,
            $data,
            array( 'id' => $id )
        );

        return false !== $result;
    }

    /**
     * Delete a feedback
     *
     * @param int $id Feedback ID.
     * @return bool True on success, false on failure.
     */
    public static function delete_feedback( $id ) {
        global $wpdb;

        // First delete associated replies
        self::delete_replies_by_feedback( $id );

        // Delete the feedback
        $table_name = self::get_table_name( self::TABLE_FEEDBACKS );

        $result = $wpdb->delete(
            $table_name,
            array( 'id' => $id ),
            array( '%d' )
        );

        return false !== $result;
    }

    // =========================================================================
    // REPLY CRUD OPERATIONS
    // =========================================================================

    /**
     * Insert a new reply
     *
     * @param array $data Reply data.
     * @return int|false Inserted ID or false on failure.
     */
    public static function insert_reply( $data ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_REPLIES );

        $defaults = array(
            'feedback_id'  => 0,
            'user_id'      => null,
            'author_name'  => null,
            'author_email' => null,
            'content'      => '',
        );

        $data = wp_parse_args( $data, $defaults );

        $result = $wpdb->insert( $table_name, $data );

        if ( false === $result ) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Get replies for a feedback
     *
     * @param int $feedback_id Feedback ID.
     * @return array Array of reply objects.
     */
    public static function get_replies( $feedback_id ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_REPLIES );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE feedback_id = %d ORDER BY created_at ASC",
                $feedback_id
            )
        );
    }

    /**
     * Delete replies by feedback ID
     *
     * @param int $feedback_id Feedback ID.
     * @return bool True on success.
     */
    public static function delete_replies_by_feedback( $feedback_id ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_REPLIES );

        $wpdb->delete(
            $table_name,
            array( 'feedback_id' => $feedback_id ),
            array( '%d' )
        );

        return true;
    }

    // =========================================================================
    // METADATA TYPES CRUD OPERATIONS
    // =========================================================================

    /**
     * Get metadata items by type group
     *
     * @param string $type_group The type group (types, priorities, statuses, tags).
     * @return array Array of metadata items.
     */
    public static function get_metadata_by_type( $type_group ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_METADATA_TYPES );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE type_group = %s ORDER BY sort_order ASC, id ASC",
                $type_group
            )
        );

        // Decode JSON fields
        foreach ( $results as &$item ) {
            $item->allowed_roles = ! empty( $item->allowed_roles ) ? json_decode( $item->allowed_roles, true ) : array();
            $item->allowed_users = ! empty( $item->allowed_users ) ? json_decode( $item->allowed_users, true ) : array();
        }

        return $results;
    }

    /**
     * Get a single metadata item
     *
     * @param string $type_group The type group.
     * @param string $slug       The item slug.
     * @return object|null Metadata item or null.
     */
    public static function get_metadata_item( $type_group, $slug ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_METADATA_TYPES );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $item = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE type_group = %s AND slug = %s",
                $type_group,
                $slug
            )
        );

        if ( $item ) {
            $item->allowed_roles = ! empty( $item->allowed_roles ) ? json_decode( $item->allowed_roles, true ) : array();
            $item->allowed_users = ! empty( $item->allowed_users ) ? json_decode( $item->allowed_users, true ) : array();
        }

        return $item;
    }

    /**
     * Insert a metadata item
     *
     * @param array $data Metadata item data.
     * @return int|false Inserted ID or false.
     */
    public static function insert_metadata_item( $data ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_METADATA_TYPES );

        // Encode arrays to JSON
        if ( isset( $data['allowed_roles'] ) && is_array( $data['allowed_roles'] ) ) {
            $data['allowed_roles'] = wp_json_encode( $data['allowed_roles'] );
        }
        if ( isset( $data['allowed_users'] ) && is_array( $data['allowed_users'] ) ) {
            $data['allowed_users'] = wp_json_encode( $data['allowed_users'] );
        }

        $result = $wpdb->insert( $table_name, $data );

        if ( false === $result ) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update a metadata item
     *
     * @param int   $id   Item ID.
     * @param array $data Data to update.
     * @return bool True on success.
     */
    public static function update_metadata_item( $id, $data ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_METADATA_TYPES );

        // Encode arrays to JSON
        if ( isset( $data['allowed_roles'] ) && is_array( $data['allowed_roles'] ) ) {
            $data['allowed_roles'] = wp_json_encode( $data['allowed_roles'] );
        }
        if ( isset( $data['allowed_users'] ) && is_array( $data['allowed_users'] ) ) {
            $data['allowed_users'] = wp_json_encode( $data['allowed_users'] );
        }

        $result = $wpdb->update(
            $table_name,
            $data,
            array( 'id' => $id )
        );

        return false !== $result;
    }

    /**
     * Delete a metadata item
     *
     * @param int $id Item ID.
     * @return bool True on success.
     */
    public static function delete_metadata_item( $id ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_METADATA_TYPES );

        $result = $wpdb->delete(
            $table_name,
            array( 'id' => $id ),
            array( '%d' )
        );

        return false !== $result;
    }

    /**
     * Update metadata items order
     *
     * @param array $order Array of id => sort_order.
     * @return bool True on success.
     */
    public static function update_metadata_order( $order ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_METADATA_TYPES );

        foreach ( $order as $id => $sort_order ) {
            $wpdb->update(
                $table_name,
                array( 'sort_order' => $sort_order ),
                array( 'id' => $id ),
                array( '%d' ),
                array( '%d' )
            );
        }

        return true;
    }

    // =========================================================================
    // CUSTOM GROUPS CRUD OPERATIONS
    // =========================================================================

    /**
     * Get all custom groups
     *
     * @return array Array of custom groups.
     */
    public static function get_custom_groups() {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_CUSTOM_GROUPS );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_results( "SELECT * FROM $table_name ORDER BY sort_order ASC, id ASC" );
    }

    /**
     * Get a custom group by slug
     *
     * @param string $slug Group slug.
     * @return object|null Group object or null.
     */
    public static function get_custom_group( $slug ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_CUSTOM_GROUPS );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE slug = %s",
                $slug
            )
        );
    }

    /**
     * Insert a custom group
     *
     * @param array $data Group data.
     * @return int|false Inserted ID or false.
     */
    public static function insert_custom_group( $data ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_CUSTOM_GROUPS );

        $result = $wpdb->insert( $table_name, $data );

        if ( false === $result ) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update a custom group
     *
     * @param string $slug Group slug.
     * @param array  $data Data to update.
     * @return bool True on success.
     */
    public static function update_custom_group( $slug, $data ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_CUSTOM_GROUPS );

        $result = $wpdb->update(
            $table_name,
            $data,
            array( 'slug' => $slug )
        );

        return false !== $result;
    }

    /**
     * Delete a custom group
     *
     * @param string $slug Group slug.
     * @return bool True on success.
     */
    public static function delete_custom_group( $slug ) {
        global $wpdb;

        // Get group ID first
        $group = self::get_custom_group( $slug );
        if ( ! $group ) {
            return false;
        }

        // Delete group items
        self::delete_custom_group_items( $group->id );

        // Delete group settings
        self::delete_group_settings( $slug );

        // Delete the group
        $table_name = self::get_table_name( self::TABLE_CUSTOM_GROUPS );

        $result = $wpdb->delete(
            $table_name,
            array( 'slug' => $slug ),
            array( '%s' )
        );

        return false !== $result;
    }

    // =========================================================================
    // CUSTOM GROUP ITEMS CRUD OPERATIONS
    // =========================================================================

    /**
     * Get items for a custom group
     *
     * @param int $group_id Group ID.
     * @return array Array of items.
     */
    public static function get_custom_group_items( $group_id ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_METADATA_ITEMS );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE group_id = %d ORDER BY sort_order ASC, id ASC",
                $group_id
            )
        );

        // Decode JSON fields
        foreach ( $results as &$item ) {
            $item->allowed_roles = ! empty( $item->allowed_roles ) ? json_decode( $item->allowed_roles, true ) : array();
            $item->allowed_users = ! empty( $item->allowed_users ) ? json_decode( $item->allowed_users, true ) : array();
        }

        return $results;
    }

    /**
     * Insert a custom group item
     *
     * @param array $data Item data.
     * @return int|false Inserted ID or false.
     */
    public static function insert_custom_group_item( $data ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_METADATA_ITEMS );

        // Encode arrays to JSON
        if ( isset( $data['allowed_roles'] ) && is_array( $data['allowed_roles'] ) ) {
            $data['allowed_roles'] = wp_json_encode( $data['allowed_roles'] );
        }
        if ( isset( $data['allowed_users'] ) && is_array( $data['allowed_users'] ) ) {
            $data['allowed_users'] = wp_json_encode( $data['allowed_users'] );
        }

        $result = $wpdb->insert( $table_name, $data );

        if ( false === $result ) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update a custom group item
     *
     * @param int   $id   Item ID.
     * @param array $data Data to update.
     * @return bool True on success.
     */
    public static function update_custom_group_item( $id, $data ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_METADATA_ITEMS );

        // Encode arrays to JSON
        if ( isset( $data['allowed_roles'] ) && is_array( $data['allowed_roles'] ) ) {
            $data['allowed_roles'] = wp_json_encode( $data['allowed_roles'] );
        }
        if ( isset( $data['allowed_users'] ) && is_array( $data['allowed_users'] ) ) {
            $data['allowed_users'] = wp_json_encode( $data['allowed_users'] );
        }

        $result = $wpdb->update(
            $table_name,
            $data,
            array( 'id' => $id )
        );

        return false !== $result;
    }

    /**
     * Delete a custom group item
     *
     * @param int $id Item ID.
     * @return bool True on success.
     */
    public static function delete_custom_group_item( $id ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_METADATA_ITEMS );

        $result = $wpdb->delete(
            $table_name,
            array( 'id' => $id ),
            array( '%d' )
        );

        return false !== $result;
    }

    /**
     * Delete all items for a custom group
     *
     * @param int $group_id Group ID.
     * @return bool True on success.
     */
    public static function delete_custom_group_items( $group_id ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_METADATA_ITEMS );

        $wpdb->delete(
            $table_name,
            array( 'group_id' => $group_id ),
            array( '%d' )
        );

        return true;
    }

    // =========================================================================
    // GROUP SETTINGS CRUD OPERATIONS
    // =========================================================================

    /**
     * Get settings for a group
     *
     * @param string $group_slug Group slug.
     * @return array Settings array with defaults.
     */
    public static function get_group_settings( $group_slug ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_GROUP_SETTINGS );

        $defaults = array(
            'enabled'            => true,
            'required'           => false,
            'show_in_sidebar'    => true,
            'hide_empty_sections'=> false,
            'sort_order'         => 99,
            'allowed_roles'      => array(),
            'allowed_users'      => array(),
            'ai_prompt'          => '',
        );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE group_slug = %s",
                $group_slug
            )
        );

        if ( ! $row ) {
            return $defaults;
        }

        return array(
            'enabled'             => (bool) $row->enabled,
            'required'            => (bool) $row->required,
            'show_in_sidebar'     => isset( $row->show_in_sidebar ) ? (bool) $row->show_in_sidebar : true,
            'hide_empty_sections' => isset( $row->hide_empty_sections ) ? (bool) $row->hide_empty_sections : false,
            'sort_order'          => isset( $row->sort_order ) ? (int) $row->sort_order : 99,
            'allowed_roles'       => ! empty( $row->allowed_roles ) ? json_decode( $row->allowed_roles, true ) : array(),
            'allowed_users'       => ! empty( $row->allowed_users ) ? json_decode( $row->allowed_users, true ) : array(),
            'ai_prompt'           => $row->ai_prompt ?: '',
        );
    }

    /**
     * Save settings for a group
     *
     * @param string $group_slug Group slug.
     * @param array  $settings   Settings to save.
     * @return bool True on success.
     */
    public static function save_group_settings( $group_slug, $settings ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_GROUP_SETTINGS );

        $data = array(
            'group_slug'          => $group_slug,
            'enabled'             => isset( $settings['enabled'] ) ? (int) $settings['enabled'] : 1,
            'required'            => isset( $settings['required'] ) ? (int) $settings['required'] : 0,
            'show_in_sidebar'     => isset( $settings['show_in_sidebar'] ) ? (int) $settings['show_in_sidebar'] : 1,
            'hide_empty_sections' => isset( $settings['hide_empty_sections'] ) ? (int) $settings['hide_empty_sections'] : 0,
            'allowed_roles'       => isset( $settings['allowed_roles'] ) ? wp_json_encode( $settings['allowed_roles'] ) : '[]',
            'allowed_users'       => isset( $settings['allowed_users'] ) ? wp_json_encode( $settings['allowed_users'] ) : '[]',
            'ai_prompt'           => isset( $settings['ai_prompt'] ) ? $settings['ai_prompt'] : '',
        );

        // Check if exists
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table_name WHERE group_slug = %s",
                $group_slug
            )
        );

        if ( $exists ) {
            unset( $data['group_slug'] );
            $result = $wpdb->update(
                $table_name,
                $data,
                array( 'group_slug' => $group_slug )
            );
        } else {
            $result = $wpdb->insert( $table_name, $data );
        }

        return false !== $result;
    }

    /**
     * Delete settings for a group
     *
     * @param string $group_slug Group slug.
     * @return bool True on success.
     */
    public static function delete_group_settings( $group_slug ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_GROUP_SETTINGS );

        $wpdb->delete(
            $table_name,
            array( 'group_slug' => $group_slug ),
            array( '%s' )
        );

        return true;
    }

    /**
     * Save the order of multiple groups
     *
     * @since 1.3.0
     * @param array $order Array of group_slug => sort_order.
     * @return bool True on success.
     */
    public static function save_groups_order( $order ) {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_GROUP_SETTINGS );

        foreach ( $order as $group_slug => $sort_order ) {
            // Check if settings exist for this group
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $table_name WHERE group_slug = %s",
                    $group_slug
                )
            );

            if ( $exists ) {
                // Update existing
                $wpdb->update(
                    $table_name,
                    array( 'sort_order' => (int) $sort_order ),
                    array( 'group_slug' => $group_slug ),
                    array( '%d' ),
                    array( '%s' )
                );
            } else {
                // Insert new with just sort_order
                $wpdb->insert(
                    $table_name,
                    array(
                        'group_slug' => $group_slug,
                        'sort_order' => (int) $sort_order,
                    ),
                    array( '%s', '%d' )
                );
            }
        }

        return true;
    }

    /**
     * Get all group settings ordered by sort_order
     *
     * @since 1.3.0
     * @return array Array of group_slug => settings.
     */
    public static function get_all_group_settings_ordered() {
        global $wpdb;

        $table_name = self::get_table_name( self::TABLE_GROUP_SETTINGS );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY sort_order ASC, id ASC" );

        $result = array();
        foreach ( $rows as $row ) {
            $result[ $row->group_slug ] = array(
                'enabled'             => (bool) $row->enabled,
                'required'            => (bool) $row->required,
                'show_in_sidebar'     => isset( $row->show_in_sidebar ) ? (bool) $row->show_in_sidebar : true,
                'hide_empty_sections' => isset( $row->hide_empty_sections ) ? (bool) $row->hide_empty_sections : false,
                'sort_order'          => isset( $row->sort_order ) ? (int) $row->sort_order : 99,
                'allowed_roles'       => ! empty( $row->allowed_roles ) ? json_decode( $row->allowed_roles, true ) : array(),
                'allowed_users'       => ! empty( $row->allowed_users ) ? json_decode( $row->allowed_users, true ) : array(),
                'ai_prompt'           => $row->ai_prompt ?: '',
            );
        }

        return $result;
    }

    // =========================================================================
    // MIGRATION FROM POSTS/POSTMETA
    // =========================================================================

    /**
     * Migrate existing data from posts/postmeta to custom tables
     *
     * @return array Migration results.
     */
    public static function migrate_from_posts() {
        global $wpdb;

        $results = array(
            'feedbacks_migrated' => 0,
            'replies_migrated'   => 0,
            'errors'             => array(),
        );

        // Get all existing feedback posts
        $posts = get_posts( array(
            'post_type'      => 'visual_feedback',
            'posts_per_page' => -1,
            'post_status'    => 'any',
        ) );

        foreach ( $posts as $post ) {
            // Get all post meta
            $meta = get_post_meta( $post->ID );

            // Prepare feedback data
            $feedback_data = array(
                'user_id'             => $post->post_author ?: null,
                'comment'             => $post->post_content,
                'url'                 => isset( $meta['_wpvfh_url'][0] ) ? $meta['_wpvfh_url'][0] : '',
                'page_path'           => isset( $meta['_wpvfh_url'][0] ) ? wp_parse_url( $meta['_wpvfh_url'][0], PHP_URL_PATH ) ?: '/' : '/',
                'position_x'          => isset( $meta['_wpvfh_position_x'][0] ) ? floatval( $meta['_wpvfh_position_x'][0] ) : 0,
                'position_y'          => isset( $meta['_wpvfh_position_y'][0] ) ? floatval( $meta['_wpvfh_position_y'][0] ) : 0,
                'selector'            => isset( $meta['_wpvfh_selector'][0] ) ? $meta['_wpvfh_selector'][0] : null,
                'element_offset_x'    => isset( $meta['_wpvfh_element_offset_x'][0] ) ? floatval( $meta['_wpvfh_element_offset_x'][0] ) : null,
                'element_offset_y'    => isset( $meta['_wpvfh_element_offset_y'][0] ) ? floatval( $meta['_wpvfh_element_offset_y'][0] ) : null,
                'scroll_x'            => isset( $meta['_wpvfh_scroll_x'][0] ) ? intval( $meta['_wpvfh_scroll_x'][0] ) : 0,
                'scroll_y'            => isset( $meta['_wpvfh_scroll_y'][0] ) ? intval( $meta['_wpvfh_scroll_y'][0] ) : 0,
                'screenshot_id'       => isset( $meta['_wpvfh_screenshot_id'][0] ) ? intval( $meta['_wpvfh_screenshot_id'][0] ) : null,
                'screen_width'        => isset( $meta['_wpvfh_screen_width'][0] ) ? intval( $meta['_wpvfh_screen_width'][0] ) : null,
                'screen_height'       => isset( $meta['_wpvfh_screen_height'][0] ) ? intval( $meta['_wpvfh_screen_height'][0] ) : null,
                'viewport_width'      => isset( $meta['_wpvfh_viewport_width'][0] ) ? intval( $meta['_wpvfh_viewport_width'][0] ) : null,
                'viewport_height'     => isset( $meta['_wpvfh_viewport_height'][0] ) ? intval( $meta['_wpvfh_viewport_height'][0] ) : null,
                'device_pixel_ratio'  => isset( $meta['_wpvfh_device_pixel_ratio'][0] ) ? $meta['_wpvfh_device_pixel_ratio'][0] : null,
                'color_depth'         => isset( $meta['_wpvfh_color_depth'][0] ) ? $meta['_wpvfh_color_depth'][0] : null,
                'orientation'         => isset( $meta['_wpvfh_orientation'][0] ) ? $meta['_wpvfh_orientation'][0] : null,
                'browser'             => isset( $meta['_wpvfh_browser'][0] ) ? $meta['_wpvfh_browser'][0] : null,
                'browser_version'     => isset( $meta['_wpvfh_browser_version'][0] ) ? $meta['_wpvfh_browser_version'][0] : null,
                'os'                  => isset( $meta['_wpvfh_os'][0] ) ? $meta['_wpvfh_os'][0] : null,
                'os_version'          => isset( $meta['_wpvfh_os_version'][0] ) ? $meta['_wpvfh_os_version'][0] : null,
                'device'              => isset( $meta['_wpvfh_device'][0] ) ? $meta['_wpvfh_device'][0] : null,
                'platform'            => isset( $meta['_wpvfh_platform'][0] ) ? $meta['_wpvfh_platform'][0] : null,
                'user_agent'          => isset( $meta['_wpvfh_user_agent'][0] ) ? $meta['_wpvfh_user_agent'][0] : null,
                'language'            => isset( $meta['_wpvfh_language'][0] ) ? $meta['_wpvfh_language'][0] : null,
                'languages'           => isset( $meta['_wpvfh_languages'][0] ) ? $meta['_wpvfh_languages'][0] : null,
                'timezone'            => isset( $meta['_wpvfh_timezone'][0] ) ? $meta['_wpvfh_timezone'][0] : null,
                'timezone_offset'     => isset( $meta['_wpvfh_timezone_offset'][0] ) ? $meta['_wpvfh_timezone_offset'][0] : null,
                'local_time'          => isset( $meta['_wpvfh_local_time'][0] ) ? $meta['_wpvfh_local_time'][0] : null,
                'cookies_enabled'     => isset( $meta['_wpvfh_cookies_enabled'][0] ) ? intval( $meta['_wpvfh_cookies_enabled'][0] ) : 1,
                'online'              => isset( $meta['_wpvfh_online'][0] ) ? intval( $meta['_wpvfh_online'][0] ) : 1,
                'touch_support'       => isset( $meta['_wpvfh_touch_support'][0] ) ? intval( $meta['_wpvfh_touch_support'][0] ) : 0,
                'max_touch_points'    => isset( $meta['_wpvfh_max_touch_points'][0] ) ? intval( $meta['_wpvfh_max_touch_points'][0] ) : 0,
                'device_memory'       => isset( $meta['_wpvfh_device_memory'][0] ) ? $meta['_wpvfh_device_memory'][0] : null,
                'hardware_concurrency' => isset( $meta['_wpvfh_hardware_concurrency'][0] ) ? $meta['_wpvfh_hardware_concurrency'][0] : null,
                'connection_type'     => isset( $meta['_wpvfh_connection_type'][0] ) ? $meta['_wpvfh_connection_type'][0] : null,
                'referrer'            => isset( $meta['_wpvfh_referrer'][0] ) ? $meta['_wpvfh_referrer'][0] : null,
                'status'              => isset( $meta['_wpvfh_status'][0] ) ? $meta['_wpvfh_status'][0] : 'new',
                'priority'            => isset( $meta['_wpvfh_priority'][0] ) ? $meta['_wpvfh_priority'][0] : 'none',
                'feedback_type'       => isset( $meta['_wpvfh_feedback_type'][0] ) ? $meta['_wpvfh_feedback_type'][0] : 'bug',
                'tags'                => isset( $meta['_wpvfh_tags'][0] ) ? $meta['_wpvfh_tags'][0] : null,
                'created_at'          => $post->post_date,
                'updated_at'          => $post->post_modified,
            );

            // Insert feedback
            $feedback_id = self::insert_feedback( $feedback_data );

            if ( $feedback_id ) {
                $results['feedbacks_migrated']++;

                // Migrate comments/replies
                $comments = get_comments( array(
                    'post_id' => $post->ID,
                    'orderby' => 'comment_date',
                    'order'   => 'ASC',
                ) );

                foreach ( $comments as $comment ) {
                    $reply_data = array(
                        'feedback_id'  => $feedback_id,
                        'user_id'      => $comment->user_id ?: null,
                        'author_name'  => $comment->comment_author,
                        'author_email' => $comment->comment_author_email,
                        'content'      => $comment->comment_content,
                        'created_at'   => $comment->comment_date,
                    );

                    $reply_id = self::insert_reply( $reply_data );
                    if ( $reply_id ) {
                        $results['replies_migrated']++;
                    }
                }
            } else {
                $results['errors'][] = "Failed to migrate feedback post ID: {$post->ID}";
            }
        }

        return $results;
    }

    /**
     * Migrate options to custom tables
     *
     * @return array Migration results.
     */
    public static function migrate_options() {
        $results = array(
            'types_migrated'    => 0,
            'groups_migrated'   => 0,
            'settings_migrated' => 0,
            'errors'            => array(),
        );

        // Migrate feedback types
        $types = get_option( 'wpvfh_feedback_types', array() );
        $sort_order = 0;
        foreach ( $types as $type ) {
            $data = array(
                'type_group'    => 'types',
                'slug'          => isset( $type['id'] ) ? $type['id'] : sanitize_title( $type['label'] ),
                'label'         => isset( $type['label'] ) ? $type['label'] : '',
                'emoji'         => isset( $type['emoji'] ) ? $type['emoji'] : null,
                'color'         => isset( $type['color'] ) ? $type['color'] : null,
                'display_mode'  => isset( $type['display_mode'] ) ? $type['display_mode'] : 'emoji',
                'sort_order'    => $sort_order++,
                'enabled'       => isset( $type['enabled'] ) ? (int) $type['enabled'] : 1,
                'ai_prompt'     => isset( $type['ai_prompt'] ) ? $type['ai_prompt'] : null,
                'allowed_roles' => isset( $type['allowed_roles'] ) ? $type['allowed_roles'] : array(),
                'allowed_users' => isset( $type['allowed_users'] ) ? $type['allowed_users'] : array(),
            );
            if ( self::insert_metadata_item( $data ) ) {
                $results['types_migrated']++;
            }
        }

        // Migrate priorities
        $priorities = get_option( 'wpvfh_feedback_priorities', array() );
        $sort_order = 0;
        foreach ( $priorities as $priority ) {
            $data = array(
                'type_group'    => 'priorities',
                'slug'          => isset( $priority['id'] ) ? $priority['id'] : sanitize_title( $priority['label'] ),
                'label'         => isset( $priority['label'] ) ? $priority['label'] : '',
                'emoji'         => isset( $priority['emoji'] ) ? $priority['emoji'] : null,
                'color'         => isset( $priority['color'] ) ? $priority['color'] : null,
                'display_mode'  => isset( $priority['display_mode'] ) ? $priority['display_mode'] : 'emoji',
                'sort_order'    => $sort_order++,
                'enabled'       => isset( $priority['enabled'] ) ? (int) $priority['enabled'] : 1,
                'ai_prompt'     => isset( $priority['ai_prompt'] ) ? $priority['ai_prompt'] : null,
                'allowed_roles' => isset( $priority['allowed_roles'] ) ? $priority['allowed_roles'] : array(),
                'allowed_users' => isset( $priority['allowed_users'] ) ? $priority['allowed_users'] : array(),
            );
            if ( self::insert_metadata_item( $data ) ) {
                $results['types_migrated']++;
            }
        }

        // Migrate statuses
        $statuses = get_option( 'wpvfh_feedback_statuses', array() );
        $sort_order = 0;
        foreach ( $statuses as $status ) {
            $data = array(
                'type_group'    => 'statuses',
                'slug'          => isset( $status['id'] ) ? $status['id'] : sanitize_title( $status['label'] ),
                'label'         => isset( $status['label'] ) ? $status['label'] : '',
                'emoji'         => isset( $status['emoji'] ) ? $status['emoji'] : null,
                'color'         => isset( $status['color'] ) ? $status['color'] : null,
                'display_mode'  => isset( $status['display_mode'] ) ? $status['display_mode'] : 'emoji',
                'sort_order'    => $sort_order++,
                'enabled'       => isset( $status['enabled'] ) ? (int) $status['enabled'] : 1,
                'ai_prompt'     => isset( $status['ai_prompt'] ) ? $status['ai_prompt'] : null,
                'allowed_roles' => isset( $status['allowed_roles'] ) ? $status['allowed_roles'] : array(),
                'allowed_users' => isset( $status['allowed_users'] ) ? $status['allowed_users'] : array(),
            );
            if ( self::insert_metadata_item( $data ) ) {
                $results['types_migrated']++;
            }
        }

        // Migrate tags
        $tags = get_option( 'wpvfh_feedback_tags', array() );
        $sort_order = 0;
        foreach ( $tags as $tag ) {
            $data = array(
                'type_group'    => 'tags',
                'slug'          => isset( $tag['id'] ) ? $tag['id'] : sanitize_title( $tag['label'] ),
                'label'         => isset( $tag['label'] ) ? $tag['label'] : '',
                'emoji'         => isset( $tag['emoji'] ) ? $tag['emoji'] : null,
                'color'         => isset( $tag['color'] ) ? $tag['color'] : null,
                'display_mode'  => isset( $tag['display_mode'] ) ? $tag['display_mode'] : 'emoji',
                'sort_order'    => $sort_order++,
                'enabled'       => isset( $tag['enabled'] ) ? (int) $tag['enabled'] : 1,
                'ai_prompt'     => isset( $tag['ai_prompt'] ) ? $tag['ai_prompt'] : null,
                'allowed_roles' => isset( $tag['allowed_roles'] ) ? $tag['allowed_roles'] : array(),
                'allowed_users' => isset( $tag['allowed_users'] ) ? $tag['allowed_users'] : array(),
            );
            if ( self::insert_metadata_item( $data ) ) {
                $results['types_migrated']++;
            }
        }

        // Migrate custom groups
        $custom_groups = get_option( 'wpvfh_custom_option_groups', array() );
        $group_sort_order = 0;
        foreach ( $custom_groups as $group ) {
            $group_slug = isset( $group['slug'] ) ? $group['slug'] : sanitize_title( $group['name'] );

            $group_data = array(
                'slug'       => $group_slug,
                'name'       => isset( $group['name'] ) ? $group['name'] : '',
                'sort_order' => $group_sort_order++,
            );

            $group_id = self::insert_custom_group( $group_data );

            if ( $group_id ) {
                $results['groups_migrated']++;

                // Migrate group items
                $items = get_option( 'wpvfh_custom_group_' . $group_slug, array() );
                $item_sort_order = 0;
                foreach ( $items as $item ) {
                    $item_data = array(
                        'group_id'      => $group_id,
                        'slug'          => isset( $item['id'] ) ? $item['id'] : sanitize_title( $item['label'] ),
                        'label'         => isset( $item['label'] ) ? $item['label'] : '',
                        'emoji'         => isset( $item['emoji'] ) ? $item['emoji'] : null,
                        'color'         => isset( $item['color'] ) ? $item['color'] : null,
                        'display_mode'  => isset( $item['display_mode'] ) ? $item['display_mode'] : 'emoji',
                        'sort_order'    => $item_sort_order++,
                        'enabled'       => isset( $item['enabled'] ) ? (int) $item['enabled'] : 1,
                        'ai_prompt'     => isset( $item['ai_prompt'] ) ? $item['ai_prompt'] : null,
                        'allowed_roles' => isset( $item['allowed_roles'] ) ? $item['allowed_roles'] : array(),
                        'allowed_users' => isset( $item['allowed_users'] ) ? $item['allowed_users'] : array(),
                    );
                    self::insert_custom_group_item( $item_data );
                }
            }
        }

        // Migrate group settings
        $group_settings = get_option( 'wpvfh_group_settings', array() );
        foreach ( $group_settings as $slug => $settings ) {
            if ( self::save_group_settings( $slug, $settings ) ) {
                $results['settings_migrated']++;
            }
        }

        return $results;
    }

    /**
     * Check if migration is needed
     *
     * @return bool True if old data exists and new tables are empty.
     */
    public static function needs_migration() {
        global $wpdb;

        // Check if there are old posts
        $old_posts_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'visual_feedback'"
        );

        if ( $old_posts_count > 0 ) {
            // Check if new table is empty
            $table_name = self::get_table_name( self::TABLE_FEEDBACKS );
            $table_exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SHOW TABLES LIKE %s",
                    $table_name
                )
            );

            if ( $table_exists ) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $new_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
                return $new_count == 0;
            }
        }

        // Check for old options
        $old_types = get_option( 'wpvfh_feedback_types', null );
        if ( $old_types !== null ) {
            $table_name = self::get_table_name( self::TABLE_METADATA_TYPES );
            $table_exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SHOW TABLES LIKE %s",
                    $table_name
                )
            );

            if ( $table_exists ) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $new_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
                return $new_count == 0;
            }
        }

        return false;
    }

    /**
     * Run full migration
     *
     * @return array Combined migration results.
     */
    public static function run_migration() {
        $results = array(
            'posts'   => self::migrate_from_posts(),
            'options' => self::migrate_options(),
        );

        // Mark migration as complete
        update_option( 'wpvfh_migration_complete', true );

        return $results;
    }
}
