<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/*
 * @version       1.0.0
 * @package       ActivityLogs
 * @license       Copyright ActivityLogs
 */

if ( ! function_exists( 'adminify_activitylogs_option' ) ) {
	/**
	 * Get setting database option
	 *
	 * @param string $section default section name adminify_activitylogs_general .
	 * @param string $key .
	 * @param string $default .
	 *
	 * @return string
	 */
	function adminify_activitylogs_option( $section = 'adminify_activitylogs_general', $key = '', $default = '' ) {
		$settings = get_option( $section );

		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}
}


if ( ! function_exists( 'adminify_activitylogs_exclude_pages' ) ) {
	/**
	 * Get exclude pages setting option data
	 *
	 * @return string|array
	 *
	 * @version 1.0.0
	 */
	function adminify_activitylogs_exclude_pages() {
		return adminify_activitylogs_option( 'adminify_activitylogs_triggers', 'exclude_pages', array() );
	}
}


if ( ! function_exists( 'adminify_activitylogs_exclude_pages_except' ) ) {
	/**
	 * Get exclude pages except setting option data
	 *
	 * @return string|array
	 *
	 * @version 1.0.0
	 */
	function adminify_activitylogs_exclude_pages_except() {
		return adminify_activitylogs_option( 'adminify_activitylogs_triggers', 'exclude_pages_except', array() );
	}
}


function adminify_activity_logs($args = [])
{
	$adminify_activity_logs = \JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Module_ActivityLogs::get_instance();
	$adminify_activity_logs->api->insert($args);
}
