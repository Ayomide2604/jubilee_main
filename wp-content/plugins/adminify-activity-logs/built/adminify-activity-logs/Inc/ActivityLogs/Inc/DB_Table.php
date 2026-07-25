<?php

namespace JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc;

// no direct access allowed
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DB_Table {

	public function __construct() {
		// This class is instantiated during the 'init' hook (the add-on defers
		// its loading to 'init' for WP 6.7 textdomain compatibility), by which
		// point 'plugins_loaded' has already fired. Hooking the migration there
		// would never run, so run it now if plugins_loaded has passed, otherwise
		// defer as usual.
		if ( did_action( 'plugins_loaded' ) ) {
			$this->init();
		} else {
			add_action( 'plugins_loaded', [ $this, 'init' ] );
		}
	}

	// Activation/Deactivation Hook
	public function init() {
		add_action( 'wp_initialize_site', [ 'JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc\DB_Table', 'adminify_new_mu_site_installer' ], 30 );
		add_filter( 'wpmu_drop_tables', [ 'JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc\DB_Table', 'adminify_mu_drop_tables' ], 30 );

		global $wpdb;

		// Self-healing schema migration: create/upgrade the table when the
		// installed schema is older than the current one OR when the table is
		// missing (e.g. it was dropped, or the schema option survived a DB
		// import without the table). dbDelta() is idempotent.
		$installed_schema = get_option( 'adminify_activity_logs_schema', '0' );
		$table_name       = $wpdb->prefix . 'adminify_activity_logs';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-time schema existence check; not cacheable.
		$table_exists = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) );

		if ( ! $table_exists || version_compare( $installed_schema, self::SCHEMA_VERSION, '<' ) ) {
			self::adminify_logs_create_table();
			update_option( 'adminify_activity_logs_schema', self::SCHEMA_VERSION );
		}
	}

	const SCHEMA_VERSION = '1.1.0';

	public static function adminify_mu_drop_tables( $tables ) {
		global $wpdb;

		$tables['adminify_activity_logs'] = $wpdb->prefix . 'adminify_activity_logs';
		$tables['adminify_page_speed']    = $wpdb->prefix . 'adminify_page_speed';

		return $tables;
	}

	public static function adminify_new_mu_site_installer( $site ) {
		global $wpdb;

		if ( is_plugin_active_for_network( PXLBSADMINIFY_BASE ) ) {
			$old_blog_id = $wpdb->blogid;
			switch_to_blog( $site->blog_id );
			self::adminify_logs_create_table();
			switch_to_blog( $old_blog_id );
		}
	}

	public static function activation_hook( $network_wide ) {
		global $wpdb;

		if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
			$old_blog_id = $wpdb->blogid;

			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::adminify_logs_create_table();
			}

			switch_to_blog( $old_blog_id );
		} else {
			self::adminify_logs_create_table();
		}
	}

	public static function deactivation_hook( $network_deactivating ) {
		global $wpdb;

		if ( function_exists( 'is_multisite' ) && is_multisite() && $network_deactivating ) {
			$old_blog_id = $wpdb->blogid;

			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs};" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::delete_table();
			}

			switch_to_blog( $old_blog_id );
		} else {
			self::delete_table();
		}
	}

	protected static function delete_table() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}adminify_activity_logs`;" );

		$admin_role = get_role( 'administrator' );
		if ( $admin_role && $admin_role->has_cap( 'view_all_adminify_activity_logs' ) ) {
			$admin_role->remove_cap( 'view_all_adminify_activity_logs' );
		}

		delete_option( 'adminify_activity_logs_version' );
		delete_option( 'adminify_activity_logs_schema' );

		// Clear scheduled cleanup.
		$ts = wp_next_scheduled( \JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc\Api::CLEANUP_HOOK );
		if ( $ts ) {
			wp_unschedule_event( $ts, \JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc\Api::CLEANUP_HOOK );
		}
	}


	protected static function adminify_logs_create_table() {
		global $wpdb;

		$sql_query = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}adminify_activity_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `user_caps` varchar(70) NOT NULL DEFAULT 'guest',
            `action` varchar(255) NOT NULL,
            `object_type` varchar(255) NOT NULL,
            `object_subtype` varchar(255) NOT NULL DEFAULT '',
            `object_name` varchar(255) NOT NULL,
            `object_id` int(11) NOT NULL DEFAULT '0',
            `user_id` int(11) NOT NULL DEFAULT '0',
            `log_ip` varchar(55) NOT NULL DEFAULT '127.0.0.1',
            `log_time` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`log_id`),
            KEY `log_time` (`log_time`),
            KEY `user_id` (`user_id`),
            KEY `object_type` (`object_type`(32)),
            KEY `action_idx` (`action`(32)),
            KEY `user_caps` (`user_caps`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_query );

		$admin_role = get_role( 'administrator' );
		if ( $admin_role instanceof \WP_Role && ! $admin_role->has_cap( 'view_all_adminify_activity_logs' ) ) {
			$admin_role->add_cap( 'view_all_adminify_activity_logs' );
		}

		update_option( 'adminify_activity_logs_version', ACTIVITYLOGS_VER );
	}
}
