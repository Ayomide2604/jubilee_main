<?php
namespace JLTAdminify\Modules\ActivityLogs\Inc\Classes;

use JLTAdminify\Modules\ActivityLogs\Libs\Recommended;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\JLTAdminify\Modules\ActivityLogs\Inc\Classes\Recommended_Plugins' ) ) {
	/**
	 * Recommended Plugins class
	 *
	 * Registers a "Recommended" submenu under the active parent. When the
	 * Adminify plugin is active, the submenu attaches to the Adminify
	 * settings menu; otherwise it attaches to the standalone Activity Logs
	 * top-level menu.
	 *
	 * Jewel Theme <support@jeweltheme.com>
	 */
	class Recommended_Plugins extends Recommended {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$parent_slug = 'adminify-activity-logs';

			parent::__construct(
				$parent_slug,
				'pixarlabs',
				'',
				70
			);
		}
	}
}
