<?php

namespace JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc;

use PXLBSAdminify\Inc\Admin\AdminSettings;

// no direct access allowed
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPAdminify
 *
 * @package Activity Logs
 *
 * @author Jewel Theme <support@jeweltheme.com>
 */

class Api {

	const CLEANUP_HOOK = 'adminify_activity_logs_daily_cleanup';

	public function __construct() {
		// Run cleanup on daily cron only — not on every insert.
		add_action( self::CLEANUP_HOOK, [ $this, 'old_data_remove' ] );
		if ( ! wp_next_scheduled( self::CLEANUP_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CLEANUP_HOOK );
		}
	}

	public function old_data_remove() {
		global $wpdb;

		// Lifespan can come from Adminify settings or the plugin's own option.
		$logs_lifespan = 0;
		$adminifyAdminSettingsClass = 'PXLBSAdminify\Inc\Admin\AdminSettings';
		if ( class_exists( $adminifyAdminSettingsClass ) ) {
			$logs_lifespan = (int) AdminSettings::get_instance()->get( 'activity_logs_history_data' );
		}
		if ( empty( $logs_lifespan ) ) {
			$logs_lifespan = (int) get_option( '_adminify_activity_logs_days', 30 );
		}
		if ( empty( $logs_lifespan ) ) {
			return;
		}

		$cutoff = strtotime( '-' . $logs_lifespan . ' days', current_time( 'timestamp' ) );

		$wpdb->query(
			$wpdb->prepare(
				'DELETE FROM `' . $wpdb->adminify_activity_logs . '` WHERE `log_time` < %d',
				$cutoff
			)
		);
	}

	public function erase_all_items() {
		global $wpdb;

		$wpdb->query( 'TRUNCATE `' . $wpdb->adminify_activity_logs . '`' );
	}

	protected function get_ip_address() {
		// Priority order for checking IP headers
		// Ordered from most specific/reliable to least reliable
		$server_ip_keys = [
			'HTTP_CF_CONNECTING_IP',     // CloudFlare
			'HTTP_TRUE_CLIENT_IP',        // CloudFlare Enterprise header
			'HTTP_X_REAL_IP',            // Nginx proxy/real IP
			'HTTP_CLIENT_IP',            // Proxy
			'HTTP_X_FORWARDED_FOR',      // Load balancers, proxies
			'HTTP_X_FORWARDED',          // Proxies
			'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster environments
			'HTTP_FORWARDED_FOR',        // Forwarded header
			'HTTP_FORWARDED',            // Forwarded header (standard)
			'REMOTE_ADDR',               // Standard server variable
		];

		// Allow customization of IP detection headers via filter
		$server_ip_keys = apply_filters( 'adminify_activity_logs_ip_headers', $server_ip_keys );

		$valid_ips = [];

		foreach ( $server_ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) ) {
				$ip_list = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );

				// Handle comma-separated IPs (common with X-Forwarded-For)
				// Take the first IP which is typically the original client
				if ( strpos( $ip_list, ',' ) !== false ) {
					$ips = array_map( 'trim', explode( ',', $ip_list ) );
					// Get the first valid public IP from the list
					foreach ( $ips as $single_ip ) {
						if ( $this->is_valid_ip( $single_ip ) ) {
							$valid_ips[] = $single_ip;
						}
					}
				} else {
					$ip = trim( $ip_list );
					if ( $this->is_valid_ip( $ip ) ) {
						$valid_ips[] = $ip;
					}
				}

				// Return the first valid IP we find
				if ( ! empty( $valid_ips ) ) {
					$final_ip = $valid_ips[0];

					// Convert IPv6 localhost to IPv4
					if ( $final_ip === '::1' ) {
						return '127.0.0.1';
					}

					// Handle IPv4-mapped IPv6 addresses (::ffff:192.168.1.1)
					if ( strpos( $final_ip, '::ffff:' ) === 0 ) {
						$final_ip = substr( $final_ip, 7 );
					}

					// Allow final IP to be filtered for custom implementations
					return apply_filters( 'adminify_activity_logs_detected_ip', $final_ip, $server_ip_keys );
				}
			}
		}

		// Fallback to localhost if no valid IP is found
		return '127.0.0.1';
	}

	/**
	 * Validate an IP address and check if it's not a private/reserved IP
	 *
	 * @param string $ip IP address to validate
	 * @return bool True if valid IP
	 */
	private function is_valid_ip( $ip ) {
		// Basic IP validation
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return false;
		}

		// For production sites, we typically want to skip private/reserved IPs
		// except for REMOTE_ADDR which might be the only available option
		// Allow all IPs for now, but you can uncomment the checks below
		// if you want to filter out private/reserved ranges

		/*
		// Check if it's not a private or reserved IP
		// This would filter out: 10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16, etc.
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false ) {
			// Allow localhost IPs
			if ( $ip !== '127.0.0.1' && $ip !== '::1' ) {
				return false;
			}
		}
		*/

		return true;
	}

	public function insert( $args ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			[
				'action'         => '',
				'object_type'    => '',
				'object_subtype' => '',
				'object_name'    => '',
				'object_id'      => '',
				'log_ip'         => $this->get_ip_address(),
				'log_time'       => current_time( 'timestamp' ),
			]
		);

		$user = get_user_by( 'id', get_current_user_id() );
		if ( $user ) {
			$args['user_caps'] = strtolower( key( $user->caps ) );
			if ( empty( $args['user_id'] ) ) {
				$args['user_id'] = $user->ID;
			}
		} else {
			$args['user_caps'] = 'guest';
			if ( empty( $args['user_id'] ) ) {
				$args['user_id'] = 0;
			}
		}

		// TODO: Find better way to Multisite compatibility.
		// Fallback for multisite with bbPress
		if ( empty( $args['user_caps'] ) || 'bbp_participant' === $args['user_caps'] ) {
			$args['user_caps'] = 'administrator';
		}

		// Make sure for non duplicate.
		$check_duplicate = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT `log_id` FROM `' . $wpdb->adminify_activity_logs . '`
					WHERE `user_caps` = %s
						AND `action` = %s
						AND `object_type` = %s
						AND `object_subtype` = %s
						AND `object_name` = %s
						AND `user_id` = %s
						AND `log_ip` = %s
						AND `log_time` = %s
				;',
				$args['user_caps'],
				$args['action'],
				$args['object_type'],
				$args['object_subtype'],
				$args['object_name'],
				$args['user_id'],
				$args['log_ip'],
				$args['log_time']
			)
		);

		if ( $check_duplicate ) {
			return;
		}

		$wpdb->insert(
			$wpdb->adminify_activity_logs,
			[
				'action'         => $args['action'],
				'object_type'    => $args['object_type'],
				'object_subtype' => $args['object_subtype'],
				'object_name'    => $args['object_name'],
				'object_id'      => $args['object_id'],
				'user_id'        => $args['user_id'],
				'user_caps'      => $args['user_caps'],
				'log_ip'         => $args['log_ip'],
				'log_time'       => $args['log_time'],
			],
			[ '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d' ]
		);

		do_action( 'adminify_activity_logs', $args );
	}

	public function delete( $id ) {
		global $wpdb;
		return $wpdb->delete( $wpdb->adminify_activity_logs, [ 'log_id' => $id ], [ '%d' ] );
	}
}
