<?php

namespace JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Inc;

use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks\Adminify_Logs_Attachments;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks\Adminify_Logs_Comments;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks\Adminify_Logs_Core;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks\Adminify_Logs_Menu;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks\Adminify_Logs_Options;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks\Adminify_Logs_Posts;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks\Adminify_Logs_Plugins;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks\Adminify_Logs_Taxonomy;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks\Adminify_Logs_Theme;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks\Adminify_Logs_Users;
use JLTAdminify\Modules\ActivityLogs\Inc\ActivityLogs\Hooks\Adminify_Logs_Widgets;

// no direct access allowed
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hooks {


	public static $instance;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->init();
	}

	public function init() {
		new Adminify_Logs_Attachments();
		new Adminify_Logs_Comments();
		new Adminify_Logs_Core();
		new Adminify_Logs_Menu();
		new Adminify_Logs_Options();
		new Adminify_Logs_Posts();
		new Adminify_Logs_Plugins();
		new Adminify_Logs_Taxonomy();
		new Adminify_Logs_Theme();
		new Adminify_Logs_Users();
		new Adminify_Logs_Widgets();
	}
}
