<?php

namespace JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks;

use  JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc\Hooks_Base;

// no direct access allowed
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Adminify_Logs_Plugins extends Hooks_Base {

	public function __construct() {
		parent::__construct();

		add_action( 'activated_plugin', [ $this, 'hooks_activated_plugin' ] );
		add_action( 'deactivated_plugin', [ $this, 'hooks_deactivated_plugin' ] );
		add_filter( 'wp_redirect', [ $this, 'hooks_plugin_modify' ], 10, 2 );

		add_action( 'upgrader_process_complete', [ $this, 'hooks_plugin_install_or_update' ], 10, 2 );
	}



	protected function _add_log_plugin( $action, $plugin_name ) {
		// Get plugin name if is a path
		if ( false !== strpos( $plugin_name, '/' ) ) {
			$plugin_dir  = explode( '/', $plugin_name );
			$plugin_data = array_values( get_plugins( '/' . $plugin_dir[0] ) );
			$plugin_data = array_shift( $plugin_data );
			$plugin_name = $plugin_data['Name'];
		}

		adminify_activity_logs(
			[
				'action'      => $action,
				'object_type' => 'Plugin',
				'object_id'   => 0,
				'object_name' => $plugin_name,
			]
		);
	}

	public function hooks_deactivated_plugin( $plugin_name ) {
		$this->_add_log_plugin( 'deactivated', $plugin_name );
	}

	public function hooks_activated_plugin( $plugin_name ) {
		$this->_add_log_plugin( 'activated', $plugin_name );
	}

	public function hooks_plugin_modify($location, $status) {
        if (false !== strpos($location, 'plugin-editor.php')) {
            // Only log if this is an actual plugin file update
            if (!empty($_POST) && isset($_REQUEST['action']) && 'update' === $_REQUEST['action'] && !empty($_REQUEST['file'])) {
                // Check user capabilities
                if (!current_user_can('edit_plugins')) {
                    wp_die(esc_html__('You do not have sufficient permissions to edit plugins.', 'adminify-activity-logs'));
                }

                // Verify nonce
                if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'edit-plugin_' . sanitize_text_field(wp_unslash($_REQUEST['file'])))) {
                    wp_die(esc_html__('Invalid nonce verification', 'adminify-activity-logs'));
                }

                $aal_args = [
                    'action'         => 'file_updated',
                    'object_type'    => 'Plugin',
                    'object_subtype' => 'plugin_unknown',
                    'object_id'      => 0,
                    'object_name'    => 'file_unknown',
                ];

                // Sanitize file path early
                $file_path = sanitize_text_field(wp_unslash($_REQUEST['file']));

                $aal_args['object_name'] = $file_path;

                // Get plugin name
                $plugin_dir = explode('/', $file_path);
                $plugin_data = array_values(get_plugins('/' . $plugin_dir[0]));
                $plugin_data = array_shift($plugin_data);

                if (!empty($plugin_data['Name'])) {
                    $aal_args['object_subtype'] = $plugin_data['Name'];
                }

                adminify_activity_logs($aal_args);
            }
        }

        // We need to return the instance, to complete the filter
        return $location;
    }

	/**
	 * @param Plugin_Upgrader $upgrader
	 * @param array           $extra
	 */
	public function hooks_plugin_install_or_update( $upgrader, $extra ) {
		if ( ! isset( $extra['type'] ) || 'plugin' !== $extra['type'] ) {
			return;
		}

		if ( 'install' === $extra['action'] ) {
			$path = $upgrader->plugin_info();
			if ( ! $path ) {
				return;
			}

			$data = get_plugin_data( $upgrader->skin->result['local_destination'] . '/' . $path, true, false );

			adminify_activity_logs(
				[
					'action'         => 'installed',
					'object_type'    => 'Plugin',
					'object_name'    => $data['Name'],
					'object_subtype' => $data['Version'],
				]
			);
		}

		if ( 'update' === $extra['action'] ) {
			if ( isset( $extra['bulk'] ) && true == $extra['bulk'] ) {
				$slugs = $extra['plugins'];
			} else {
				if ( ! isset( $upgrader->skin->plugin ) ) {
					return;
				}

				$slugs = [ $upgrader->skin->plugin ];
			}

			foreach ( $slugs as $slug ) {
				$data = get_plugin_data( WP_PLUGIN_DIR . '/' . $slug, true, false );

				adminify_activity_logs(
					[
						'action'         => 'updated',
						'object_type'    => 'Plugin',
						'object_name'    => esc_html( $data['Name'] ),
						'object_subtype' => esc_html( $data['Version'] ),
					]
				);
			}
		}
	}
}
