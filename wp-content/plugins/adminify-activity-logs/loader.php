<?php

namespace JLTAdminify\Modules\ActivityLogs;

use JLTAdminify\Modules\ActivityLogs\Libs\Assets;

class Loader {

    public function __construct() {

        /**
         * Autoload Necessary Files
         */
        include_once ACTIVITYLOGS_DIR . '/vendor/autoload.php';


        new Assets();

        /**
         * Only bootstrap when the main Adminify plugin is loaded. Adminify
         * fires 'pxlbsadminify_loaded' during its own load (before 'init',
         * where this loader runs), so check did_action() first and otherwise
         * defer. When Adminify is not active the hook never fires and this
         * add-on stays inert instead of erroring on missing dependencies.
         */
        if ( did_action( 'pxlbsadminify_loaded' ) ) {
            $this->adminify_activitylogs_plugin_loaded();
        } else {
            // Adminify may load later in this request, so still defer the
            // bootstrap; and if it is not active at all, show a notice telling
            // the user the Adminify plugin is required.
            add_action( 'pxlbsadminify_loaded', [ $this, 'adminify_activitylogs_plugin_loaded' ] );
            add_action( 'admin_notices', [ $this, 'adminify_required_notice' ] );
            add_action( 'network_admin_notices', [ $this, 'adminify_required_notice' ] );
        }
    }

    public function adminify_activitylogs_plugin_loaded() {

        if ( ! class_exists( '\\JLTAdminify\Modules\ActivityLogs\\ActivityLogs' ) ) {
            include_once ACTIVITYLOGS_DIR . '/class-adminify-activity-logs.php';
        }
    }

    /**
     * Notice shown when the required Adminify plugin is not active.
     * Mirrors the "requires WP Adminify" notice used by the other Adminify
     * add-ons (offers Activate when installed, Install otherwise).
     */
    public function adminify_required_notice() {
        // Adminify loaded after all (fired its hook later in the request).
        if ( did_action( 'pxlbsadminify_loaded' ) ) {
            return;
        }

        if ( ( is_multisite() || ! is_network_admin() ) && ! current_user_can( 'install_plugins' ) ) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $adminify_basename = '';
        foreach ( array_keys( get_plugins() ) as $basename ) {
            if ( 0 === strpos( $basename, 'adminify/' ) || 0 === strpos( $basename, 'adminify-pro/' ) ) {
                $adminify_basename = $basename;
                break;
            }
        }

        if ( $adminify_basename ) {
            $activate_url = wp_nonce_url( 'plugins.php?action=activate&plugin=' . $adminify_basename . '&plugin_status=all&paged=1&s', 'activate-plugin_' . $adminify_basename );
            $message      = sprintf( /* translators: %s: add-on name. */ __( '<b>%1$s</b> requires <strong>WP Adminify</strong> plugin to be active. Please activate WP Adminify to continue.', 'adminify-activity-logs' ), 'Adminify Activity Logs' );
            $button       = '<p><a href="' . esc_url( $activate_url ) . '" class="button-primary">' . esc_html__( 'Activate WP Adminify', 'adminify-activity-logs' ) . '</a></p>';
        } else {
            $message = sprintf( /* translators: %s: add-on name. */ __( '<b>%1$s</b> requires <strong>"WP Adminify"</strong> plugin to be installed and activated. Please install WP Adminify to continue.', 'adminify-activity-logs' ), 'Adminify Activity Logs' );
            $button  = '<p><a class="install-now button-primary" data-plugin="' . esc_url( 'https://downloads.wordpress.org/plugin/adminify.zip' ) . '">' . esc_html__( 'Install WP Adminify', 'adminify-activity-logs' ) . '</a></p>';
        }

        $allowed = array(
            'b'      => array(),
            'strong' => array(),
            'p'      => array(),
            'a'      => array( 'href' => array(), 'class' => array(), 'data-plugin' => array() ),
        );
        echo '<div class="notice notice-warning"><p>' . wp_kses( $message, $allowed ) . '</p>' . wp_kses( $button, $allowed ) . '</div>';
    }

}

new Loader();
