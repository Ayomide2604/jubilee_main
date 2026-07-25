<?php

/**
 * Plugin Name: Adminify Activity Logs
 * Plugin URI:  https://wpadminify.com/activity-logs
 * Description: Redirect Custom Login, Logout URLs by User Roles and Capabilities
 * Version:     1.0.9
 * Author:      Pixar Labs
 * Author URI:  https://pixarlabs.com
 * Text Domain: adminify-activity-logs
 * Requires PHP: 7.0
 * License:     GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package adminify-activity-logs
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
$adminify_activitylogs_plugin_data = get_file_data( __FILE__, array(
    'Version'     => 'Version',
    'Plugin Name' => 'Plugin Name',
    'Author'      => 'Author',
    'Description' => 'Description',
    'Plugin URI'  => 'Plugin URI',
), false );
// Define Constants.
if ( !defined( 'ACTIVITYLOGS' ) ) {
    define( 'ACTIVITYLOGS', $adminify_activitylogs_plugin_data['Plugin Name'] );
}
if ( !defined( 'ACTIVITYLOGS_VER' ) ) {
    define( 'ACTIVITYLOGS_VER', $adminify_activitylogs_plugin_data['Version'] );
}
if ( !defined( 'ACTIVITYLOGS_AUTHOR' ) ) {
    define( 'ACTIVITYLOGS_AUTHOR', $adminify_activitylogs_plugin_data['Author'] );
}
if ( !defined( 'ACTIVITYLOGS_DESC' ) ) {
    define( 'ACTIVITYLOGS_DESC', $adminify_activitylogs_plugin_data['Author'] );
}
if ( !defined( 'ACTIVITYLOGS_URI' ) ) {
    define( 'ACTIVITYLOGS_URI', $adminify_activitylogs_plugin_data['Plugin URI'] );
}
if ( !defined( 'ACTIVITYLOGS_DIR' ) ) {
    define( 'ACTIVITYLOGS_DIR', __DIR__ );
}
if ( !defined( 'ACTIVITYLOGS_FILE' ) ) {
    define( 'ACTIVITYLOGS_FILE', __FILE__ );
}
if ( !defined( 'ACTIVITYLOGS_SLUG' ) ) {
    define( 'ACTIVITYLOGS_SLUG', dirname( plugin_basename( __FILE__ ) ) );
}
if ( !defined( 'ACTIVITYLOGS_BASE' ) ) {
    define( 'ACTIVITYLOGS_BASE', plugin_basename( __FILE__ ) );
}
if ( !defined( 'ACTIVITYLOGS_PATH' ) ) {
    define( 'ACTIVITYLOGS_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}
if ( !defined( 'ACTIVITYLOGS_URL' ) ) {
    define( 'ACTIVITYLOGS_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );
}
if ( !defined( 'ACTIVITYLOGS_INC' ) ) {
    define( 'ACTIVITYLOGS_INC', ACTIVITYLOGS_PATH . '/Inc/' );
}
if ( !defined( 'ACTIVITYLOGS_LIBS' ) ) {
    define( 'ACTIVITYLOGS_LIBS', ACTIVITYLOGS_PATH . 'Libs' );
}
if ( !defined( 'ACTIVITYLOGS_ASSETS' ) ) {
    define( 'ACTIVITYLOGS_ASSETS', ACTIVITYLOGS_URL . 'assets/' );
}
if ( !defined( 'ACTIVITYLOGS_IMAGES' ) ) {
    define( 'ACTIVITYLOGS_IMAGES', ACTIVITYLOGS_ASSETS . 'images/' );
}
if ( !function_exists( 'adminify_activitylogs_fs' ) ) {
    // Create a helper function for easy SDK access.
    function adminify_activitylogs_fs() {
        global $adminify_activitylogs_fs;
        if ( !isset( $adminify_activitylogs_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_15240_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_15240_MULTISITE', true );
            }
            // Include Freemius SDK.
            if ( file_exists( dirname( dirname( __FILE__ ) ) . '/adminify/freemius/start.php' ) ) {
                // Try to load SDK from parent plugin folder.
                require_once dirname( dirname( __FILE__ ) ) . '/adminify/freemius/start.php';
            } else {
                if ( file_exists( dirname( dirname( __FILE__ ) ) . '/adminify-pro/freemius/start.php' ) ) {
                    // Try to load SDK from premium parent plugin folder.
                    require_once dirname( dirname( __FILE__ ) ) . '/adminify-pro/freemius/start.php';
                } else {
                    // SDK is auto-loaded through composer
                    $adminify_activitylogs_fs = fs_dynamic_init( array(
                        'id'               => '15240',
                        'slug'             => 'adminify-activity-logs',
                        'type'             => 'plugin',
                        'public_key'       => 'pk_98c0f82a391bfc883ca8a22a57447',
                        'is_premium'       => false,
                        'has_paid_plans'   => false,
                        'is_org_compliant' => true,
                        'parent'           => array(
                            'id'         => '7829',
                            'slug'       => 'adminify',
                            'public_key' => 'pk_a0ea61beae7126eb845f7e58a03e5',
                            'name'       => 'WP Adminify',
                        ),
                        'menu'             => array(
                            'account' => false,
                            'support' => false,
                        ),
                        'is_live'          => true,
                    ) );
                }
            }
            return $adminify_activitylogs_fs;
        }
    }

}
// Defer plugin loading to init for WordPress 6.7+ textdomain compatibility
add_action( 'init', function () {
    require_once ACTIVITYLOGS_DIR . '/vendor/autoload.php';
    include_once ACTIVITYLOGS_DIR . '/loader.php';
    // Activation and Deactivation Hook
    if ( class_exists( '\\JLTAdminify\\Modules\\ActivityLogs\\Inc\\ActivityLogs\\Inc\\DB_Table' ) ) {
        register_activation_hook( ACTIVITYLOGS_FILE, ['\\JLTAdminify\\Modules\\ActivityLogs\\Inc\\ActivityLogs\\Inc\\DB_Table', 'activation_hook'] );
        register_uninstall_hook( ACTIVITYLOGS_FILE, ['\\JLTAdminify\\Modules\\ActivityLogs\\Inc\\ActivityLogs\\Inc\\DB_Table', 'deactivation_hook'] );
    }
}, -1 );