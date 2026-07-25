<?php

namespace JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks;

use  JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc\Hooks_Base;

// no direct access allowed
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Adminify_Logs_Core extends Hooks_Base {

	public function __construct() {
		parent::__construct();
		add_action( '_core_updated_successfully', [ $this, 'core_updated_successfully' ] );
	}

	public function core_updated_successfully( $wp_version ) {
		global $pagenow;

		// Auto updated
		if ( 'update-core.php' !== $pagenow ) {
			$object_name = esc_html__( 'WordPress Auto Updated', 'adminify-activity-logs' );
		} else {
			$object_name = esc_html__( 'WordPress Updated', 'adminify-activity-logs' );
		}

		adminify_activity_logs(
			[
				'action'      => 'updated',
				'object_type' => 'Core',
				'object_id'   => 0,
				'object_name' => $object_name,
			]
		);
	}
}
