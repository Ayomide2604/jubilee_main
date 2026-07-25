<?php

namespace PXLBSAdminify\Inc\Admin\Frames;

use PXLBSAdminify\Inc\Utils;

// no direct access allowed
if (!defined('ABSPATH')) {
	exit;
}
/**
 * WP Adminify
 * Frames Class
 *
 * @author Jewel Theme <support@jeweltheme.com>
 */

if (!class_exists('Frames')) {
	class Frames
	{
        public function __construct()
        {
            $this->init_hooks();
        }

        private function init_hooks()
        {
            add_filter("language_attributes", [$this, "page_attribute"]);
            add_action('admin_enqueue_scripts', [$this, 'load_scripts']);

            // Reload the page after plugin activation/deactivation
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only check, no state change.
            if ( isset( $_GET['activate'] ) || isset( $_GET['activate-multi'] ) || isset( $_GET['deactivate'] ) || isset( $_GET['deactivate-multi'] ) ) {
                add_action('admin_footer', [$this, 'render_reload_script']);
            }
        }

        public static function render_reload_script() {
            self::custom_plugin_change_reload();
        }

        /**
         * Break the current page out of the Adminify UI iframe.
         *
         * @param string|null $actual_link Optional. URL to send the parent window to.
         *                                 When null, the parent simply reloads.
         * @return void
         */
        public static function custom_plugin_change_reload( $actual_link = null ) {
            if ( null !== $actual_link ) {
                // wp_json_encode(), not esc_js(): esc_js() turns "&" into "&amp;",
                // which a classic <script> does not decode, breaking query args.
                echo '<script>parent.location.replace(' . wp_json_encode(
                    esc_url_raw( $actual_link ),
                    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                ) . ');</script>';

                // The reload() below would abort the replace() navigation above.
                return;
            }

            echo '<script>parent.location.reload();</script>';
        }

        public function load_scripts()
        {
            wp_enqueue_style('frame-adminify--frame', PXLBSADMINIFY_ASSETS . 'admin/css/frame' . Utils::assets_ext('.css'), [], PXLBSADMINIFY_VER);
        }

        public function page_attribute($attr)
        {
            $attrs = [$attr];
            $attrs[] = 'frame-adminify-iframe="true"';
            return implode(' ', $attrs);
        }
    }

}
