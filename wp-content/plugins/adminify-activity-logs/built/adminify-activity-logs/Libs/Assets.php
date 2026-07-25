<?php
namespace JLTAdminify\Modules\ActivityLogs\Libs;

if ( ! class_exists( 'Assets' ) ) {

	/**
	 * Assets Class
	 *
	 * Jewel Theme <support@jeweltheme.com>
	 * @version     1.0.0
	 */
	class Assets {

		/**
		 * Constructor method
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'adminify_activitylogs_admin_enqueue_scripts' ), 100 );
		}


		/**
		 * Get environment mode
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public function get_mode() {
			return defined( 'WP_DEBUG' ) && WP_DEBUG ? 'development' : 'production';
		}

		/**
		 * Enqueue Scripts
		 *
		 * @method admin_enqueue_scripts()
		 */
		public function adminify_activitylogs_admin_enqueue_scripts() {

			wp_enqueue_style( 'adminify-activity-logs-admin', ACTIVITYLOGS_ASSETS . 'css/adminify-activity-logs-admin.css', array( 'dashicons' ), ACTIVITYLOGS_VER, 'all' );
			
			$screen = get_current_screen();
			if ( ! $screen || false === strpos( $screen->id, 'adminify-activity-logs' ) ) {
				return;
			}

			// CSS Files .

			// JS Files .
			wp_enqueue_script( 'adminify-activity-logs-admin', ACTIVITYLOGS_ASSETS . 'js/adminify-activity-logs-admin.js', array( 'jquery' ), ACTIVITYLOGS_VER, true );
			wp_localize_script(
				'adminify-activity-logs-admin',
				'ACTIVITYLOGSCORE',
				array(
					'admin_ajax'        => admin_url( 'admin-ajax.php' ),
					'recommended_nonce' => wp_create_nonce( 'adminify_activitylogs_recommended_nonce' ),
				)
			);
		}
	}
}
