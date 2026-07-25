<?php
namespace JLTAdminify\Modules\ActivityLogs;

use JLTAdminify\Modules\ActivityLogs\Libs\Helper;
use JLTAdminify\Modules\ActivityLogs\Inc\Classes\Recommended_Plugins;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Module_ActivityLogs;

/**
 * Main Class
 *
 * @Adminify Activity Logs
 * Jewel Theme <support@jeweltheme.com>
 * @version     1.0.0
 */

// No, Direct access Sir !!!
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class: ActivityLogs
 */
final class ActivityLogs {

	const VERSION            = ACTIVITYLOGS_VER;
	private static $instance = null;
	public $url;

	/**
	 * what we collect construct method
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public function __construct() {
		$this->includes();
		add_action( 'plugins_loaded', array( $this, 'adminify_activitylogs_plugins_loaded' ), 999 );

		// Body Class.
		add_filter( 'admin_body_class', array( $this, 'adminify_activitylogs_body_class' ) );
	}

	/**
	 * plugins_loaded method
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public function adminify_activitylogs_plugins_loaded() {
		$this->adminify_activitylogs_activate();
	}

	/**
	 * Version Key
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public static function plugin_version_key() {
		return Helper::adminify_activitylogs_slug_cleanup() . '_version';
	}

	/**
	 * Activation Hook
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public static function adminify_activitylogs_activate() {
		$current_adminify_activitylogs_version = get_option( self::plugin_version_key(), null );

		if ( get_option( 'adminify_activitylogs_activation_time' ) === false ) {
			update_option( 'adminify_activitylogs_activation_time', strtotime( 'now' ) );
		}

		if ( is_null( $current_adminify_activitylogs_version ) ) {
			update_option( self::plugin_version_key(), self::VERSION );
		}

		$allowed = get_option( Helper::adminify_activitylogs_slug_cleanup() . '_allow_tracking', 'no' );

		// if it wasn't allowed before, do nothing .
		if ( 'yes' !== $allowed ) {
			return;
		}
		// re-schedule and delete the last sent time so we could force send again .
		$hook_name = Helper::adminify_activitylogs_slug_cleanup() . '_tracker_send_event';
		if ( ! wp_next_scheduled( $hook_name ) ) {
			wp_schedule_event( time(), 'weekly', $hook_name );
		}
	}


	/**
	 * Add Body Class
	 *
	 * @param [type] $classes .
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public function adminify_activitylogs_body_class( $classes ) {
		$classes .= ' adminify-activity-logs ';
		return $classes;
	}

	/**
	 * Include methods
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public function includes() {
		new Module_ActivityLogs();
		new Recommended_Plugins();
	}


	/**
	 * Initialization
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public function adminify_activitylogs_init() {
		$this->adminify_activitylogs_load_textdomain();
	}

	/**
	 * Text Domain
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public function adminify_activitylogs_load_textdomain() {
			add_action('init', function () {
				$domain = 'adminify-activity-logs';
				$locale = apply_filters('adminify_activitylogs_plugin_locale', get_locale(), $domain);

				load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
				load_plugin_textdomain($domain, false, dirname(ACTIVITYLOGS_BASE) . '/languages/');
			});
	}

	/**
	 * Returns the singleton instance of the class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof ActivityLogs ) ) {
			self::$instance = new ActivityLogs();
			self::$instance->adminify_activitylogs_init();
		}

		return self::$instance;
	}
}

ActivityLogs::get_instance();
