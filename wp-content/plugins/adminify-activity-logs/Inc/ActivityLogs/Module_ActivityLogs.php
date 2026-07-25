<?php
namespace JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs;

use JLTAdminify\Modules\ActivityLogs\Libs\Helper;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc\DB_Table;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc\Api;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc\Hooks;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc\Adminify_Activity_Log_List_Table;

// no direct access allowed
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WPAdminify
 *
 * @package Activity Logs
 *
 * @author Jewel Theme <support@jeweltheme.com>
 */

class Module_ActivityLogs
{
	public $api;
	public $hooks;

	// Adminify List Table
	protected $_list_table = null;
	protected $_screens    = [];

	public static $instance;

	public static function get_instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function get_log_table()
	{
		global $wpdb;
		return $wpdb->prefix . 'adminify_activity_logs';
	}

	public function __construct()
	{
		global $wpdb;

		// Required across admin, AJAX, and cron contexts.
		$wpdb->adminify_activity_logs = self::get_log_table();
		new DB_Table();
		$this->api = new Api();

		if (is_admin()) {
			 // Admin Menu
				if (Helper::is_plugin_active('adminify/adminify.php') || Helper::is_plugin_active('adminify-pro/adminify.php')) {
						add_action('admin_menu', [$this, 'adminify_activity_logs_adminify_submenu'], 14);
				} else {
						add_action('admin_menu', [$this, 'adminify_activity_logs_submenu'], 19);
				}

			add_action('admin_enqueue_scripts', [$this, 'jltwp_adminify_enqueue_scripts']);

			$this->hooks = Hooks::get_instance();
		}

		add_action('admin_init', [$this, 'init_actions']);

		add_action('wp_ajax_adminify_activitylogs_store_days', [$this, 'adminify_activitylogs_store_days']);
	}



	public function adminify_activitylogs_store_days()
	{
		// Verify nonce first
		if (!isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'adminify_activitylogs_recommended_nonce')) {
		    wp_send_json_error(['message' => esc_html__('Invalid security token.', 'adminify-activity-logs')]);
		}

		// Check user capabilities
		if (!current_user_can('manage_options')) {
		    wp_send_json_error(['message' => esc_html__('You do not have permission to perform this action.', 'adminify-activity-logs')]);
		}

		// Sanitize and validate activity days array
		$activity_days = isset($_POST['activity_days']) ? (array) $_POST['activity_days'] : [];
		$activity_days = array_map(function($day) {
		    $day = absint($day);
		    return ($day >= 1 && $day <= 365) ? $day : 30; // Default to 30 if invalid
		}, $activity_days);

		// Remove any duplicate values and reindex array
		$activity_days = array_values(array_unique($activity_days));

		if (!current_user_can('manage_options')) {
			return;
		}

		check_ajax_referer('adminify_activitylogs_recommended_nonce', 'security');
		if( class_exists('PXLBSAdminify\Inc\Utils')){
			$activity_days = \PXLBSAdminify\Inc\Utils::clean_ajax_input(wp_unslash($_POST['activity_days']));
		}


		if (!empty($activity_days)) {
			$data_update = update_option('_adminify_activity_logs_days', $activity_days);
			if (! is_wp_error($data_update)) {
				wp_send_json_success([
					'message'	=> 'ok',
					'data'		=> $data_update
				]);
			} else {
				wp_send_json_error("Couldn't Save");
			}
		}
	}

	/** Submenu */
	public function adminify_activity_logs_submenu()
	{
		$cap = apply_filters('jltwp_adminify_capability', 'manage_options');

		$this->_screens['adminify_log'] = add_menu_page(
			esc_html__('Activity Logs by WP Adminify', 'adminify-activity-logs'),
			esc_html__('Activity Logs', 'adminify-activity-logs'),
			$cap,
			'adminify-activity-logs', // Page slug, will be displayed in URL
			[$this, 'adminify_activity_logs_contents'],
			ACTIVITYLOGS_IMAGES . 'logo.svg',
			30
		);

		// Making sure that we've created Instance
		add_action('load-' . $this->_screens['adminify_log'], [$this, 'jltwp_adminify_get_list_table']);
	}

	/**
	 * Adminify Sub Menu
	 *
	 * @return void
	 */
	public function adminify_activity_logs_adminify_submenu()
	{
		$submenu_position = apply_filters('jltwp_adminify_submenu_position', 5);
		$cap              = apply_filters('jltwp_adminify_capability', 'manage_options');

		// If WP Adminify Plugin Installed then show on Sub Menu
		$this->_screens['adminify_log'] = add_submenu_page(
			'wp-adminify-settings',
			esc_html__('Activity Logs by WP Adminify', 'adminify-activity-logs'),
			esc_html__('Activity Logs', 'adminify-activity-logs'),
			$cap,
			'adminify-activity-logs', // Page slug, will be displayed in URL
			[$this, 'adminify_activity_logs_contents'],
			$submenu_position
		);

		if ($this->_screens['adminify_log']) {
			add_action('load-' . $this->_screens['adminify_log'], [$this, 'jltwp_adminify_get_list_table']);
		}
	}


	public function init_actions()
	{
		if (empty($_GET['page']) || $_GET['page'] !== 'adminify-activity-logs') {
			return;
		}
		if (empty($_GET['action']) || $_GET['action'] == -1) {
			return;
		}
		if (empty($_GET['log_id'])) {
			return;
		}

		$action = sanitize_text_field(wp_unslash($_GET['action']));
		$log_id = (int) $_GET['log_id'];

		$sendback = admin_url('admin.php?page=adminify-activity-logs');

		if ($action == 'delete') {
			check_admin_referer('delete-log_' . $log_id);
			$adminify_activity_logs = self::get_instance();
			$adminify_activity_logs->api->delete($log_id);
		}

		wp_safe_redirect($sendback);
		exit;
	}

	/**
	 * Scripst / Styles
	 */
	public function jltwp_adminify_enqueue_scripts()
	{
		$screen = get_current_screen();

		// Load Scripts/Styles only Activity Logs Page
		if ( ! $screen || false === strpos( $screen->id, 'adminify-activity-logs' ) ) {
			return;
		}

		$this->admin_head_css();
	}

	/**
	 * Admin head CSS
	 *
	 * @return void
	 */
	public function admin_head_css()
	{
		$activity_custom_css  = '';
		$activity_custom_css .= '.adminify-activity-logs .label {
                font-size: inherit !important;
            }

            #record-actions-submit {
                margin-top: 10px;
            }

            .wp-adminify-pt {
                color: #ffffff;
                padding: 1px 4px;
                margin: 0 5px;
                font-size: 1em;
                border-radius: 3px;
                background: #808080;
                font-family: inherit;
            }

            .adminify-activity-logs .manage-column {
                width: auto;
            }

            .adminify-activity-logs .column-description {
                width: 20%;
            }

            #adminmenu #wp-adminify_page_adminify-activity-logs div.wp-menu-image:before {
                content: "\f321";
            }

            @media (max-width: 767px) {
                .adminify-activity-logs .manage-column {
                    width: auto;
                }

                .adminify-activity-logs .column-date,
                .adminify-activity-logs .column-author {
                    display: table-cell;
                    width: auto;
                }

                .adminify-activity-logs .column-ip,
                .adminify-activity-logs .column-description,
                .adminify-activity-logs .column-label {
                    display: none;
                }

                .adminify-activity-logs .column-author .avatar {
                    display: none;
                }
            }';

		$activity_custom_css = preg_replace('#/\*.*?\*/#s', '', $activity_custom_css);
		$activity_custom_css = preg_replace('/\s*([{}|:;,])\s+/', '$1', $activity_custom_css);
		$activity_custom_css = preg_replace('/\s\s+(.*)/', '$1', $activity_custom_css);

		// Sanitize CSS before adding
		$safe_css = wp_strip_all_tags(wp_kses_post($activity_custom_css));
		wp_add_inline_style('wp-adminify-admin', $safe_css);
		wp_add_inline_style('wp-adminify-default-ui', $safe_css);
	}

	/**
	 * Get List Table
	 */
	public function jltwp_adminify_get_list_table()
	{
		if (is_null($this->_list_table)) {
			$screen = isset($this->_screens['adminify_log']) ? $this->_screens['adminify_log'] : get_current_screen();
			$this->_list_table = new Adminify_Activity_Log_List_Table(['screen' => $screen]);
			do_action('adminify_activity_page', $this->_list_table);
		}

		return $this->_list_table;
	}

	public function adminify_activity_logs_contents()
	{
		$this->jltwp_adminify_get_list_table()->prepare_items();
		// Sanitize input early
		$page_value = isset($_REQUEST['page']) ? sanitize_key(wp_unslash($_REQUEST['page'])) : '';
	?>
		<div class="wrap">
			<h1 class="wp-adminify-page-title"><?php echo esc_html__('Activity Log', 'adminify-activity-logs'); ?></h1>

			<form id="activity-filter" method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr($page_value); ?>" />
				<?php $this->jltwp_adminify_get_list_table()->display(); ?>
			</form>
		</div>

<?php
	}

}
