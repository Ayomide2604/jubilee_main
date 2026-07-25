<?php

namespace PXLBSAdminify\Inc\Classes;

use PXLBSAdminify\Inc\Utils;
use PXLBSAdminify\Inc\Admin\AdminSettings;

// no direct access allowed
if (!defined('ABSPATH')) {
    exit;
}
/**
 * PXLBSAdminify
 * Dark Mode Conflicts with other plugins supports
 *
 * @author Jewel Theme <support@jeweltheme.com>
 */

class DarkModeConflicts
{
    public $options;

    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'darkmode_scripts'), 100);
        add_action( 'enqueue_block_editor_assets', array($this, "gutenberg_block_editor_darkmode_assets"));
        // Block editor canvas (iframe[name="editor-canvas"]) is a frontend style
        // PREVIEW — Adminify is an admin-focus plugin, so dark mode must NOT darken
        // the previewed content. Only the controller chrome (sidebar/panels) goes
        // dark, handled by the parent-admin dark mode + gutenberg_block_editor_darkmode_assets.
        // (Intentionally no iframe injection into the block canvas.)
        // Classic editor (TinyMCE) renders the Visual tab inside its own iframe
        // (#content_ifr). Same iframe-isolation problem as the block canvas: the
        // parent Darkreader run can't reach the iframe DOM, so the editor body
        // stays white in dark mode. Inject the same script into TinyMCE iframes.
        add_action('admin_footer', array($this, 'inject_darkmode_into_classic_editor_iframe'), 999);
        // customize.php controls: Adminify scripts are skipped there by design
        // (Assets::should_skip_adminify_scripts), so enqueue dark-mode only on this
        // dedicated hook to keep the customize panel dark without the rest of Adminify.
        add_action('customize_controls_enqueue_scripts', array($this, 'customize_controls_darkmode_enqueue'));
    }

    public function darkmode_scripts()
    {

        $this->options = (array) AdminSettings::get_instance()->get();
        $this->options = !empty($this->options['light_dark_mode']['admin_ui_mode']) ? $this->options['light_dark_mode']['admin_ui_mode'] : '';

        if ($this->options == 'light') {
            return;
        }


        $dark_mode_style = '.ReactModal__Content,
        .select2-selection,
        .select2-results__options,
        .adminify-dark-mode.toplevel_page_aio-contact-lite,
        .aio-contact-menu,
        .bsr-action-form input.regular-text,
        .select2-dropdown,
        .form-cancel-btn,
        .folders-tabs,
        .folder-tab-menu,
        .folder-tab-menu a,
        .custom-checkbox span,
        .preview-inner-box,
        .preview-inner-box .form-options,
        .media-buttons,
        .popup-form-content,
        .select2-results__option,
        div.tagsinput,
        .settings-tabs-list .nav-tab,
        input[type=number],
        .bulk-action-select .bulk-action-select__control,
        .bulk-action-select__menu,
        .wprf-section-fields,
        .wprf-section-title,
        .wprf-input-radio-option:not(.wprf-option-selected) .wprf-input-label,
        .wprf-control-field .components-button,
        .wprf-select__menu,
        .wprf-select__control,
        .wprf-tab-content,
        .squirrly-seo-settings #wpcontent .bg-light,
        .bootstrap-select,
        .bg-white,
        .wdt-addons-intro {
            background: white !important;
        }

        .el-checkbox.is-checked ~ div,
        .fluentcrm_header {
            background: none!important;
        }

        .folder-user-settings .pro-feature-popup {
            background: rgb(0, 0, 0, 0.4)
        }

        .big-pluspro-btn,
        .dokan-btn,
        .close_dp_help,
        .dup-btn,
        .folder-tab-menu a,
        .pro-feature-popup .pro-feature-content > a,
        .user-upgrade-inline-btn,
        .pro-feature-popup .pro-feature-content .pro-user-title,
        .pro-feature-popup .pro-feature-content .pro-user-desc,
        .folder-access,
        .add-new-folder,
        .media-select option,
        .popup-form-content,
        .nf-button:hover,
        .wp-react-form h1,
        .wp-react-form h2,
        .wp-react-form h3,
        .wp-react-form h4,
        .wprf-input-label,
        .wprf-tab-nav-item:not(:hover),
        .swift-btn,
        .wpcode-button,
        .wdt-constructor-type-selecter-block .card-body h4,
        .wdt-constructor-type-selecter-block .card-body h4 span,
        .yoast-button-upsell,
        .aioseo-button:hover,
        .el-button {
            color: white !important;
        }

        .folders-tabs .dashicons-editor-help,
        .folder-tab-menu a.active,
        .folder-list li a span,
        .select2-selection__rendered,
        .fp-item-name,
        .select2-results__option,
        .form-sample,
        input[type=number],
        .wprf-select__single-value,
        .wprf-code-viewer textarea,
        .wdt-constructor-type-selecter-block .card-body span,
        .mce-menu-item > span  {
            color: gray !important;
        }

        .select2-selection,
        .bsr-action-form input.regular-text,
        #bsr-table-select,
        .custom-checkbox input+span,
        .preview-inner-box,
        div.tagsinput,
        input[type=number],
        .bulk-action-select .bulk-action-select__control,
        .wprf-tab-nav-item,
        .wprf-control-field .components-button,
        .wprf-select__control,
        .wprf-control-field input[type="checkbox"],
        .bootstrap-select {
            border-color: #D0D5DD !important;
        }

        .dokan-admin-header-logo img,
        .dup-header img {
            filter: grayscale(1) brightness(5);
        }

        .jetpack-logo,
        .it-ui-list div[direction="horizontal"] > svg {
            fill: black !important;
        }

        .jp-form-block-fade {
            opacity: 0.5;
        }
        ';

        // Elementor Style issues
        // if (Utils::is_plugin_active('elementor/elementor.php')) {
        //     $dark_mode_style .= '.darkmode{
        //          background: white !important;
        //     }';
        // }

        // Better Links Style issues
        if (Utils::is_plugin_active('betterlinks/betterlinks.php')) {
            $dark_mode_style .= '#betterlinksbody .kpyoXL,
            .btl-react-select__menu,
            #betterlinksbody .jlDjrx {
                background: white !important;
            }
            #betterlinksbody .kpyoXL{
                color: white !important;
            }
            ';
        }

        // Forminator Style issues
        if (Utils::is_plugin_active('forminator/forminator.php')) {
            $dark_mode_style .= '.sui-box,
            .sui-icon-plugin-2,
            .sui-vertical-tab a.current,
            .sui-wrap .fui-multi-answers,
            .sui-wrap .fui-multi-answers .fui-answers>li,
            .sui-dropdown.open ul,
            .sui-notice-content,
            .sui-wrap .sui-box-selectors,
            .sui-select-dropdown,
            .sui-accordion-item,
            .sui-tabs-menu,
            .forminator-addon-card--footer,
            .sui-vertical-tab.current,
            .sui-form-control,
            select#forminator-field-user_role,
            .forminator-save-field-settings {
                background: white !important;
            }

            .sui-upgrade-page,
            .sui-upgrade-page-header,
            .sui-upgrade-page-cta {
                background: none!important;
            }

            .sui-header-title,
            .sui-summary-large,
            .sui-list-label,
            .sui-box-title,
            .sui-wrap h1,
            .sui-wrap h2,
            .sui-wrap h3,
            .sui-button,
            .sui-vertical-tab a.current,
            .sui-table thead>tr>th,
            .sui-trim-text,
            .sui-message-content h2,
            .sui-tab-item.active,
            .sui-vertical-tab.current a,
            select#forminator-field-user_role {
                color: white !important;
            }

            .sui-upsell-list li,
            .sui-table-item-title,
            .sui-vertical-tab a,
            .sui-form-control,
            .sui-settings-label {
                color: gray !important;
            }

            .sui-list li,
            .sui-box-header,
            #sui-cross-sell-footer>div,
            .sui-box-settings-row,
            .sui-dropdown.open ul,
            .sui-box-footer,
            .sui-status-changes,
            .sui-table thead>tr>th,
            .sui-select-dropdown,
            .sui-tab-content,
            .forminator-addon-card--footer,
            .sui-table.fui-table--apps,
            .sui-table tbody>tr>td,
            .sui-tabs-overflow,
            .sui-form-control,
            .sui-button,
            select#forminator-field-user_role {
                border-color: #D0D5DD !important;
            }

            .sui-dropdown.open ul::after,
            .sui-dropdown.open ul::before {
                border-color: #D0D5DD rgba(0, 0, 0, 0)!important;
            }

            .sui-box,
            .sui-accordion-item,
            .sui-tabs-menu {
                box-shadow: none!important;
            }

            .sui-chartjs-message--empty {
                background-blend-mode: exclusion;
            }

            .sui-tag,
            .sui-tabs-menu .sui-tab-item {
                background: #6f6f6f!important;
                color: #fff!important;
            }

            .sui-tab-item.active {
                background: black!important;
            }
            ';
        }

        // Loginpress
        if (Utils::is_plugin_active('loginpress/loginpress.php')) {
            $dark_mode_style .= '.loginpress-help-page pre textarea,
            .loginpress-import-export-page .upload-file,
            .loginpress-extension {
                background: white !important;
            }

            .toplevel_page_loginpress-settings #wpcontent,
            .loginpress_page_loginpress-help #wpcontent,
            .loginpress_page_loginpress-import-export #wpcontent,
            .loginpress-header-wrapper,
            .loginpress-settings {
                background: none!important;
            }

            .loginpress-help-page h2,
            .loginpress-import-export-page h2,
            .loginpress-addons-wrap h2,
            .loginpress-extension h3 span,
            .loginpress-settings h3{
                color: white !important;
            }

            .loginpress-help-page pre textarea,
            .loginpress-import-export-page > div,
            .loginpress-settings .form-table tr th,
            .loginpress-settings .form-table tr td fieldset label {
                color: gray !important
            }

            .loginpress-help-page pre textarea,
            .loginpress-import-export-page .upload-file,
            .loginpress-extension h3,
            .loginpress-extension {
                border-color: #D0D5DD !important;
            }

            .loginpress-header-logo img {
                filter: grayscale(1) brightness(5);
            }

            ';
        }

        // Notificationx Style issues
        if (Utils::is_plugin_active('notificationx/notificationx.php')) {
            $dark_mode_style .= '.nx-analytics-counter,
            #notificationx .notificationx-items .nx-admin-menu>ul li:not(.nx-active) a,
            .nx-admin-sidebar .sidebar-widget,
            .nx-settings-right,
            .nx-admin-block,
            .nx-sidebar,
            .nx-quick-builder-wrapper {
                 background: white !important;
            }

            .nx-button:not(:hover) {
                background: none!important;
            }

            .nx-counter-number,
            .nx-admin-title {
                color: white !important;
            }

            #notificationx .notificationx-items .nx-admin-menu>ul li:not(.nx-active) a,
            #nx-title,
            #notification-template,
            .nx-admin-sidebar-cta a:not(:hover) {
                color: gray !important;
            }

            #notificationx .notificationx-items .nx-admin-menu>ul li:not(.nx-active) a,
            #nx-title,
            .nx-widget-title,
            #notification-template {
                border-color: #D0D5DD !important;
            }

            .nx-admin-sidebar-logo img {
                filter: grayscale(1) brightness(5);
            }

            .nx-admin-header svg g {
                fill: #fff!important;
            }
            ';
        }

        // Wpdatatables Style issues
        if (Utils::is_plugin_active('wpdatatables/wpdatatables.php')) {
            $dark_mode_style .= '.wpdt-c.toplevel_page_wpdatatables-dashboard,
            .wpdt-c .wdt-datatables-admin-wrap .plugin-dashboard,
            .wpdt-c.wpdatatables_page_wpdatatables-administration,
            .card-header.wdt-admin-card-header.ch-alt,
            #wdt-datatables-browse-table,
            .manage-column,
            .wpdt-c.wpdatatables_page_wpdatatables-constructor,
            .wdt-constructor-type-selecter-block .card-header,
            .wpdt-c,
            .chart-wizard-breadcrumb,
            .wpdt-c .chart-name,
            .wpdt-c .render-engine {
                 background: white !important;
            }

            ';
        }



        $dark_mode_style = preg_replace('#/\*.*?\*/#s', '', $dark_mode_style);
        $dark_mode_style = preg_replace('/\s*([{}|:;,])\s+/', '$1', $dark_mode_style);
        $dark_mode_style = preg_replace('/\s\s+(.*)/', '$1', $dark_mode_style);
        wp_add_inline_style('adminify-admin', wp_strip_all_tags($dark_mode_style));
    }

    /**
     * Gutenberg Block Editor Dark Mode CSS
     */
    function gutenberg_block_editor_darkmode_assets () {

        wp_register_style('adminify-gutenberg-dark', false, array(), PXLBSADMINIFY_VER);
        wp_enqueue_style('adminify-gutenberg-dark');

        // $parent_selector = 'body.wp-adminify.adminify-dark-mode';
        $parent_selector = 'body.wp-adminify';

        $dark_mode_style = "$parent_selector .components-modal__content .components-text,
            $parent_selector .components-popover__content .components-text { color: black!important; }
            $parent_selector .block-editor-block-inspector .components-tools-panel { border-top-color: #e0e0e0; }
            $parent_selector .admin-ui-navigable-region .components-panel__header > div > button { color: black; }
            $parent_selector .editor-sidebar__panel .editor-post-card-panel__header svg,
            $parent_selector .commands-command-menu__container .commands-command-menu__header svg { fill: black; }
            /** Typography & Size */
            $parent_selector .components-toggle-group-control {
                background: #1e1e1e!important; border-color: #4a4a4a!important;
            }
            /* Advanced > ADDITIONAL CSS textarea + WIDTH/number/unit inputs render white
               (the dark engine skips them), clashing with the dark inspector. Force dark. */
            $parent_selector .block-editor-block-inspector textarea,
            $parent_selector .block-editor-block-inspector .components-textarea-control__input,
            $parent_selector .block-editor-block-inspector input[type=\"number\"],
            $parent_selector .block-editor-block-inspector input[type=\"text\"],
            $parent_selector .block-editor-block-inspector .components-input-control__input,
            $parent_selector .block-editor-block-inspector .components-unit-control input {
                background: #1e1e1e!important; color: #f0f0f1!important; border-color: #4a4a4a!important;
            }
            /* Unit suffix/select (the \"px\" box) is a separate element the engine leaves
               white — force it dark to match the WIDTH input. */
            $parent_selector .block-editor-block-inspector .components-unit-control__unit-select,
            $parent_selector .block-editor-block-inspector .components-unit-control select,
            $parent_selector .block-editor-block-inspector .components-input-control__suffix,
            $parent_selector .block-editor-block-inspector .components-input-control__suffix * {
                background: #1e1e1e!important; color: #f0f0f1!important; border-color: #4a4a4a!important;
            }
            /* ToolsPanel \"Settings\" three-dot dropdown menu: item labels (e.g. WIDTH) render
               dark on the dark popover and disappear — force them light. The newer block
               editor uses an Ariakit menu with hashed class names (css-*-Item-baseItem), so
               target the ARIA roles too, not just the legacy .components-menu-item__item. */
            $parent_selector .components-dropdown-menu__menu .components-menu-item__item,
            $parent_selector .components-menu-group .components-menu-item__item,
            $parent_selector [role=\"menu\"] .components-menu-item__item,
            $parent_selector [role=\"menu\"] [role=\"menuitem\"],
            $parent_selector [role=\"menu\"] [role=\"menuitemcheckbox\"],
            $parent_selector [role=\"menu\"] [role=\"menuitemradio\"],
            $parent_selector [role=\"menu\"] [role=\"menuitem\"] *,
            $parent_selector [role=\"menu\"] [role=\"menuitemcheckbox\"] *,
            $parent_selector [role=\"menu\"] [role=\"menuitemradio\"] * {
                color: #f0f0f1!important;
            }
            /* Block card description (text under the block name, e.g. \"A single column
               within a columns block\") renders too dark to read — force a light color. */
            $parent_selector .block-editor-block-inspector .block-editor-block-card__description,
            $parent_selector .block-editor-block-card__description {
                color: #c0c0c5!important;
            }
            /* Inspector tab icons (Settings gear / Styles) render dark on the dark panel —
               force a light fill so they stay visible. */
            $parent_selector .block-editor-block-inspector [role=\"tab\"] svg,
            $parent_selector .block-editor-block-inspector-tabs__tablist svg {
                fill: #e0e0e0!important;
            }
        ";
        wp_add_inline_style('adminify-gutenberg-dark', wp_strip_all_tags($dark_mode_style));
    }

    /**
     * Resolve the active light/dark mode for the current user.
     * Per-user `color_mode` meta takes precedence over the global setting.
     * Returns 'light' | 'dark' | 'auto' | ''.
     */
    public function pxlbsadminify_resolve_color_mode()
    {
        $opts        = (array) AdminSettings::get_instance()->get();
        $global_mode = !empty($opts['light_dark_mode']['admin_ui_mode']) ? $opts['light_dark_mode']['admin_ui_mode'] : 'light';
        $user_mode   = get_user_meta(get_current_user_id(), 'color_mode', true);
        return !empty($user_mode) ? $user_mode : $global_mode;
    }

    /**
     * Inject the AdminifyDarkMode script INTO every classic-editor TinyMCE iframe.
     *
     * The TinyMCE Visual tab renders post content inside an iframe (`#content_ifr`,
     * generally `{editor_id}_ifr`) whose body carries the `mce-content-body` class.
     * As with the block-editor canvas, the parent Darkreader run never touches the
     * iframe document, so the editor background stays white in dark mode. The bridge
     * below watches for TinyMCE iframes (created lazily, and re-created when toggling
     * Visual/Text or initializing extra editors), injects the dark-mode JS into each
     * iframe's document and calls `AdminifyDarkMode.enable()` from inside. `__adminifyDM`
     * guards against double-injection per iframe window.
     */
    public function inject_darkmode_into_classic_editor_iframe()
    {
        $mode = $this->pxlbsadminify_resolve_color_mode();
        if ( $mode === 'auto' ) {
            $mode = 'system';
        }
        // 'dark' = always on; 'system' = follow OS prefers-color-scheme (decided client-side).
        if ( $mode !== 'dark' && $mode !== 'system' ) {
            return;
        }
        $dark_js_url = PXLBSADMINIFY_ASSETS . 'admin/js/wp-adminify-dark-mode' . Utils::assets_ext( '.js' );
        // For 'system' mode, only proceed when the OS currently prefers dark; otherwise bail
        // out of the bridge entirely so the iframe stays light (matches the parent admin).
        $apply_guard = ( $mode === 'system' )
            ? 'if(!(window.matchMedia&&window.matchMedia("(prefers-color-scheme: dark)").matches)){return;}'
            : '';
        $bridge      = '(function(){'
            . $apply_guard
            . 'var URL=' . wp_json_encode( $dark_js_url ) . ';'
            . 'function looksLikeMCE(ifr){try{if(/_ifr$/.test(ifr.id||""))return true;var d=ifr.contentDocument;return !!(d&&d.body&&d.body.classList&&d.body.classList.contains("mce-content-body"));}catch(e){return false;}}'
            . 'function activate(w){try{if(w&&w.AdminifyDarkMode){w.AdminifyDarkMode.enable({brightness:120});return true;}}catch(e){}return false;}'
            . 'function inject(ifr){try{var d=ifr.contentDocument,w=ifr.contentWindow;if(!d||!w||!d.body)return;if(w.__adminifyDM){activate(w);return;}w.__adminifyDM=true;if(activate(w))return;var s=d.createElement("script");s.src=URL;s.onload=function(){if(!activate(w)){setTimeout(function(){activate(w);},100);setTimeout(function(){activate(w);},500);}};(d.head||d.documentElement||d.body).appendChild(s);}catch(e){}}'
            . 'var seen=new WeakSet();'
            . 'function watch(ifr){if(seen.has(ifr))return;seen.add(ifr);inject(ifr);ifr.addEventListener("load",function(){try{ifr.contentWindow.__adminifyDM=false;}catch(e){}inject(ifr);});}'
            . 'function scan(){var all=document.querySelectorAll("iframe");for(var i=0;i<all.length;i++){if(looksLikeMCE(all[i]))watch(all[i]);}}'
            . 'if(document.readyState!=="loading"){scan();}else{document.addEventListener("DOMContentLoaded",scan);}'
            . 'new MutationObserver(scan).observe(document.documentElement,{childList:true,subtree:true});'
            . '}());';
        if ( function_exists( 'wp_print_inline_script_tag' ) ) {
            wp_print_inline_script_tag( $bridge );
        } else {
            echo '<script>' . $bridge . '</script>'; // phpcs:ignore WordPress.WP.EnqueuedResources
        }
    }

    /**
     * customize.php controls: Adminify's general scripts are deliberately skipped on
     * customize.php (Assets::should_skip_adminify_scripts), so dark-mode never reaches
     * the controls panel. Enqueue dark-mode standalone on the customize controls hook.
     */
    public function customize_controls_darkmode_enqueue()
    {
        $mode = $this->pxlbsadminify_resolve_color_mode();
        if ($mode === 'auto') {
            $mode = 'system';
        }
        if ($mode !== 'dark' && $mode !== 'system') {
            return;
        }
        wp_enqueue_script(
            'adminify--dark-mode',
            PXLBSADMINIFY_ASSETS . 'admin/js/wp-adminify-dark-mode' . Utils::assets_ext('.js'),
            array(),
            PXLBSADMINIFY_VER,
            false
        );
        wp_add_inline_script('adminify--dark-mode', $this->darkmode_enable_inline($mode));
    }

    /**
     * Build the inline enable() snippet. For 'dark' it always enables; for 'system' it
     * enables only when the OS currently prefers dark (prefers-color-scheme), matching
     * the parent-admin behavior in Assets::header_scripts.
     */
    private function darkmode_enable_inline($mode)
    {
        $enable = ($mode === 'system')
            ? 'if(window.matchMedia&&window.matchMedia("(prefers-color-scheme: dark)").matches){window.AdminifyDarkMode.enable({brightness:120});}'
            : 'window.AdminifyDarkMode.enable({brightness:120});';
        return 'if(window.AdminifyDarkMode){' . $enable . '}'
            . 'addEventListener("load",function(){if(window.AdminifyDarkMode){' . $enable . '}});';
    }
}
