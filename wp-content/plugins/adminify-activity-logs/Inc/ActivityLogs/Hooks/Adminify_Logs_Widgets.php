<?php

namespace JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks;

use  JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc\Hooks_Base;

// no direct access allowed
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Adminify_Logs_Widgets extends Hooks_Base {

	public function __construct() {
		parent::__construct();
		add_filter( 'widget_update_callback', [ $this, 'hooks_widget_update_callback' ], 9999, 4 );
		add_filter( 'sidebar_admin_setup', [ $this, 'hooks_widget_delete' ] ); // Widget delete.
	}

	public function hooks_widget_update_callback( $instance, $new_instance, $old_instance, \WP_Widget $widget ) {
		$adminify_widget_args = [
			'action'         => 'updated',
			'object_type'    => 'Widget',
			'object_subtype' => 'sidebar_unknown',
			'object_id'      => 0,
			'object_name'    => $widget->id_base,
		];

		if ( empty( $_REQUEST['sidebar'] ) ) {
			return $instance;
		}

		adminify_activity_logs( $adminify_widget_args );

		// We are need return the instance, for complete the filter.
		return $instance;
	}

	public function hooks_widget_delete() {
        // Only process widget delete operations
        if ('post' === strtolower(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD']))) && !empty($_REQUEST['widget-id'])) {
            if (isset($_REQUEST['delete_widget']) && 1 === (int)$_REQUEST['delete_widget']) {
                // Check user capabilities
                if (!current_user_can('edit_theme_options')) {
                    wp_die(esc_html__('You do not have sufficient permissions to manage widgets.', 'adminify-activity-logs'));
                }

                // Verify nonce
                if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'save-delete-widget')) {
                    wp_die(esc_html__('Invalid nonce verification', 'adminify-activity-logs'));
                }

                // Sanitize inputs early
                $sidebar = isset($_REQUEST['sidebar']) ? sanitize_text_field(wp_unslash($_REQUEST['sidebar'])) : '';
                $id_base = isset($_REQUEST['id_base']) ? sanitize_text_field(wp_unslash($_REQUEST['id_base'])) : '';

                adminify_activity_logs([
                    'action'         => 'deleted',
                    'object_type'    => 'Widget',
                    'object_subtype' => strtolower($sidebar),
                    'object_id'      => 0,
                    'object_name'    => $id_base,
                ]);
            }
        }
    }
}
