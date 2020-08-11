<?php
/**
 * Plugin Name: Paid Member Subscriptions
 * Plugin URI: http://www.cozmoslabs.com/
 * Description: Accept payments, create subscription plans and restrict content on your membership website.
 * Version: 2.1.5
 * Author: Cozmoslabs
 * Author URI: http://www.cozmoslabs.com/
 * Text Domain: paid-member-subscriptions
 * License: GPL2
 * WC requires at least: 3.0.0
 * WC tested up to: 4.2
 *
 * == Copyright ==
 * Copyright 2015 Cozmoslabs (www.cozmoslabs.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class Paid_Member_Subscriptions {

    public $prefix;

    public function __construct() {

        define( 'PMS_VERSION', '2.1.5' );
        define( 'PMS_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
        define( 'PMS_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
        define( 'PMS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

        // The prefix of the plugin
        $this->prefix = 'pms_';

        // Install needed components on plugin activation
        register_activation_hook( __FILE__, array( $this, 'install' ) );

        register_deactivation_hook(__FILE__, array($this, 'uninstall') );

        add_action( 'plugins_loaded', array( $this, 'register_custom_meta_tables' ) );

        // Load plugin text domain
        add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );

        // Check if this is a newer version
        add_action( 'plugins_loaded', array( $this, 'update_check' ) );

        // Include dependencies
        $this->include_dependencies();

        // Initialize the components
        $this->init();

    }


    /*
     * Method that gets executed on plugin activation
     *
     */
    public function install( $network_activate = false ) {

        // Handle multi-site installation
        if( function_exists( 'is_multisite' ) && is_multisite() && $network_activate ) {

            global $wpdb;

            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

            foreach( $blog_ids as $blog_id ) {

                switch_to_blog( $blog_id );

                // Create needed tables
                $this->create_tables();

                // Add default settings
                $this->add_default_settings();

                restore_current_blog();

            }

        // Handle single site installation
        } else {

            // Create needed tables
            $this->create_tables();

            // Add default settings
            $this->add_default_settings();

        }


        // Add a cron job to be executed daily
        $this->cron_job();
    }

    /*
     * Method that gets executed on plugin deactivation
     *
     */
    public function uninstall() {

        // Clear cron job
        $this->clear_cron_job();

    }


    /*
     * Loads plugin text domain
     *
     */
    public function load_text_domain() {

        $current_theme = wp_get_theme();

        if( !empty( $current_theme->stylesheet ) && file_exists( get_theme_root() . '/' . $current_theme->stylesheet . '/local_pms_lang' ) )
            load_plugin_textdomain( 'paid-member-subscriptions', false, plugin_basename( dirname( __FILE__ ) ) . '/../../themes/' . $current_theme->stylesheet . '/local_pms_lang' );
        else
            load_plugin_textdomain( 'paid-member-subscriptions', false, plugin_basename( dirname( __FILE__ ) ) . '/translations' );

    }


    /*
     * Method that checks if the current version differs from the one saved in the db
     *
     */
    public function update_check() {

        $db_version = get_option( 'pms_version', '' );

        if( PMS_VERSION != $db_version ) {

            $this->create_tables();

            do_action('pms_update_check');

            update_option( 'pms_version', PMS_VERSION );
        }

    }


    /*
     * Function that schedules a hook to be executed daily (cron job)
     *
     */
    public function cron_job() {

        // Process payments for custom member subscriptions
        if( !wp_next_scheduled( 'pms_cron_process_member_subscriptions_payments' ) )
            wp_schedule_event( time(), 'daily', 'pms_cron_process_member_subscriptions_payments' );

        // Schedule event for checking subscription status
        if( !wp_next_scheduled( 'pms_check_subscription_status' ) )
            wp_schedule_event( time(), 'daily', 'pms_check_subscription_status' );

        // Schedule event for setting old payments to failed
        if( !wp_next_scheduled( 'pms_cron_process_pending_payments' ) )
            wp_schedule_event( time(), 'daily', 'pms_cron_process_pending_payments' );

        //Schedule event for deleting expired activation keys used for password reset
        if( !wp_next_scheduled( 'pms_remove_activation_key' ) )
            wp_schedule_event( time(), 'daily', 'pms_remove_activation_key' );


    }

    /*
     * Function that cleans the scheduler on plugin deactivation:
     *
     */
    public function clear_cron_job() {

        wp_clear_scheduled_hook( 'pms_cron_process_member_subscriptions_payments' );

        wp_clear_scheduled_hook( 'pms_check_subscription_status' );

        wp_clear_scheduled_hook( 'pms_cron_process_pending_payments' );

        wp_clear_scheduled_hook( 'pms_remove_activation_key' );

    }


    /*
     * Add the default settings if they do not exist
     *
     */
    public function add_default_settings() {

        $already_installed = get_option( 'pms_already_installed' );

        //Run Setup Wizard ?
        if( !$already_installed && !pms_get_paypal_email() && !pms_is_addon_active() )
            set_transient( 'pms_run_setup_wizard', 'true', 120 );

        //General
        $settings = get_option( 'pms_general_settings', array() );

        if( !isset( $settings['use_pms_css'] ) && !$already_installed ) {
            $settings['use_pms_css'] = 1;
        }

        update_option( 'pms_general_settings', $settings );

        //Payments
        $settings = get_option( 'pms_payments_settings', array() );

        if( !isset( $settings['currency'] ) )
            $settings['currency'] = 'USD';

        if( !isset( $settings['active_pay_gates'] ) )
            $settings['active_pay_gates'][] = 'paypal_standard';

        if( !isset( $settings['default_payment_gateway'] ) )
            $settings['default_payment_gateway'] = 'paypal_standard';

        update_option( 'pms_payments_settings', $settings );

        // Messages
        $settings = get_option( 'pms_content_restriction_settings', array() );

        if( !isset( $settings['logged_out'] ) )
            $settings['logged_out'] = __( 'You must be logged in to view this content.', 'paid-member-subscriptions' );

        if( !isset( $settings['non_members'] ) )
            $settings['non_members'] = __( 'This content is restricted for your membership level.', 'paid-member-subscriptions' );

        if( !isset( $settings['content_restrict_type'] ) )
            $settings['content_restrict_type'] = 'message';

        update_option( 'pms_content_restriction_settings', $settings );

        // E-mails
        $mail_general_options = PMS_Emails::get_email_general_options();

        $settings = get_option( 'pms_emails_settings', array() );

        if( !empty( $mail_general_options ) ) {
            foreach( $mail_general_options as $option_slug => $mail_general_option ) {

                if( !isset( $settings[$option_slug] ) )
                    $settings[$option_slug] = $mail_general_option;

            }
        }

        $mail_subjects = PMS_Emails::get_default_email_subjects();

        if( !empty( $mail_subjects ) ) {
            foreach( $mail_subjects as $mail_slug => $subject ) {

                if( !isset( $settings[$mail_slug. '_sub_subject'] ) )
                    $settings[$mail_slug. '_sub_subject'] = $subject;

                if ( !isset( $settings[$mail_slug . '_is_enabled'] ) && !$already_installed ) {
                    $settings[$mail_slug . '_is_enabled'] = 'yes';
                }

                if ( !isset( $settings[$mail_slug . '_admin_is_enabled'] ) && !$already_installed ) {
                    $settings[$mail_slug . '_admin_is_enabled'] = 'yes';
                }

            }
        }

        $mail_contents = PMS_Emails::get_default_email_content();

        if( !empty( $mail_contents ) ) {
            foreach( $mail_contents as $mail_slug => $content ) {

                if( !isset( $settings[$mail_slug. '_sub'] ) )
                    $settings[$mail_slug. '_sub'] = $content;

            }
        }

        update_option( 'pms_emails_settings', $settings );

        if ( !$already_installed )
            update_option( 'pms_already_installed', 'yes', false );
    }


    /*
     * Function to include the files needed
     *
     */
    public function include_dependencies() {

        /*
         * Notices
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-notices.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-notices.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-plugin-notifications.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-plugin-notifications.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-plugin-notifications.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-plugin-notifications.php';

        /*
         * Core files
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-form-handler.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-form-handler.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-form-extra-fields.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-form-extra-fields.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-utils.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-utils.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-user.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-user.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-core.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-core.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-success.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-success.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-page.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-page.php';

        /*
         * Custom post types and meta boxes base classes
         */

        // Include the class file for the custom post types
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-custom-post-types.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-custom-post-types.php';

        // Include class file for the meta boxes
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-meta-boxes.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-meta-boxes.php';


        /*
         * Admin Submenu Page Class
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-submenu-page.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-submenu-page.php';

        /*
         * Shortcodes files
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-shortcodes.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-shortcodes.php';

        /*
         * Email files
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-emails.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-emails.php';

        /*
         * Merge Tags
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-merge-tags.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-merge-tags.php';

        /*
         * User roles functions
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-user-roles.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-user-roles.php';

        /*
         * Basic Information
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-basic-info.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-basic-info.php';

        /*
         * Billing Details
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-billing-details.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-billing-details.php';


        /*
         * Subscription Plans
         */

        // Subscription plan object class
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-subscription-plan.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-subscription-plan.php';

        // Subscription plan functions
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-subscription-plan.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-subscription-plan.php';

        // Subscription plans cpt
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-subscription-plans.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-subscription-plans.php';

        // Meta box for subscription cpt
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/meta-boxes/class-meta-box-subscription-plan-details.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/meta-boxes/class-meta-box-subscription-plan-details.php';

        /*
         * Members
         */

        // Member Subscriptions functions
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-member-subscriptions.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-member-subscriptions.php';

        // Member Subscription class
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-member-subscription.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-member-subscription.php';

        // Member object class
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-member.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-member.php';

        // Member functions
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-member.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-member.php';

        // Members admin page list table class
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-members-list-table.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-members-list-table.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-member-subscription-list-table.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-member-subscription-list-table.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-member-payments-list-table.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-member-payments-list-table.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-members-add-new-bulk-list-table.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-members-add-new-bulk-list-table.php';

        // Members admin page
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-members.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-members.php';


        /*
         * Payments
         */

        // Recent payments WP Dashboard meta-box
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/meta-boxes/class-meta-box-admin-dashboard-payments.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/meta-boxes/class-meta-box-admin-dashboard-payments.php';

        // Payment object class
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-payment.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-payment.php';

        // Payment functions
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-payment.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-payment.php';

        // Payment admin list table class
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-payments-list-table.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-payments-list-table.php';

        // Payment admin list table class
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-payments-log-list-table.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-payments-log-list-table.php';


        // Payments admin page
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-payments.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-payments.php';

        /*
         * Reports
         */

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-reports.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-reports.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-export.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-export.php';

        /*
         * WooCommerce Compatibility
         */

        if ( ! function_exists( 'is_plugin_active_for_network' ) )
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

        // First check if WooCommerce is active
        if ( ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) || (is_plugin_active_for_network('woocommerce/woocommerce.php')) ) {

            /**
             * Filter for disabling/enabling PMS - WooCommerce integration
             *
             */
            $enable_woo_integration = apply_filters( 'pms_enable_woocommerce_integration', true );

            if ( file_exists( PMS_PLUGIN_DIR_PATH . 'extend/woocommerce/woocommerce-integration.php' ) && $enable_woo_integration )
                include_once PMS_PLUGIN_DIR_PATH . 'extend/woocommerce/woocommerce-integration.php';

        }

        /*
         * Settings
         */

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-settings.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-settings.php';

        /*
         * Add-ons
         */

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-addons.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-addons.php';

        /*
         * Add-ons update
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-update-checker.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-update-checker.php';

        /*
         * Payment gateways
         */

        // Gateway base class and extends
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/gateways/class-payment-gateway.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/gateways/class-payment-gateway.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/gateways/manual/class-payment-gateway-manual.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/gateways/manual/class-payment-gateway-manual.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/gateways/paypal_standard/class-payment-gateway-paypal-standard.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/gateways/paypal_standard/class-payment-gateway-paypal-standard.php';

        // Gateway functions
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/gateways/functions-payment-gateways.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/gateways/functions-payment-gateways.php';


        // PayPal listener
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/gateways/paypal/ipnlistener.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/gateways/paypal/ipnlistener.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/gateways/paypal/class-ipn-verify.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/gateways/paypal/class-ipn-verify.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/gateways/paypal_standard/functions-paypal-standard.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/gateways/paypal_standard/functions-paypal-standard.php';


        /*
         * Content restriction
         */

        // Content restriction helper functions
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-content-restriction.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-content-restriction.php';

        // Content filtering functions
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-content-filtering.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-content-filtering.php';

        // Meta box with content restriction options on pages
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/meta-boxes/class-meta-box-single-content-restriction.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/meta-boxes/class-meta-box-single-content-restriction.php';

        /*
         * Functions that log payment or subscriptions data
         */
         if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-logger.php' ) )
             include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-logger.php';

        /*
         * Deprecated
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/deprecated-functions.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/deprecated-functions.php';

        /*
         * Functions & Hooks for backwards compatibility
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/functions-backwards-compatibility.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-backwards-compatibility.php';

        /*
         * Uninstall plugin
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-uninstall.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-uninstall.php';

        /*
         * bbPress
         */
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'extend/bbpress/functions.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'extend/bbpress/functions.php';

        /*
         * Profile Builder Compatibility
         */

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'extend/profile-builder/admin/manage-fields.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'extend/profile-builder/admin/manage-fields.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'extend/profile-builder/functions.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'extend/profile-builder/functions.php';

        if (file_exists(PMS_PLUGIN_DIR_PATH . 'extend/profile-builder/front-end/subscription-plans-field.php'))
            include_once PMS_PLUGIN_DIR_PATH . 'extend/profile-builder/front-end/subscription-plans-field.php';

        if( file_exists( PMS_PLUGIN_DIR_PATH . 'extend/profile-builder/functions-email-confirmation.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'extend/profile-builder/functions-email-confirmation.php';

        if (file_exists(PMS_PLUGIN_DIR_PATH . 'extend/profile-builder/functions-pb-redirect.php'))
            include_once PMS_PLUGIN_DIR_PATH . 'extend/profile-builder/functions-pb-redirect.php';

        /*
         * Other compatibilities
         */
         if (file_exists(PMS_PLUGIN_DIR_PATH . 'includes/functions-plugin-compatibilities.php') )
             include_once PMS_PLUGIN_DIR_PATH . 'includes/functions-plugin-compatibilities.php';


        /*
         * Elementor
         */
        if ( did_action( 'elementor/loaded' ) ) {

            if( file_exists( PMS_PLUGIN_DIR_PATH . 'extend/elementor/class-elementor.php' ) )
                include_once PMS_PLUGIN_DIR_PATH . 'extend/elementor/class-elementor.php';

        }

        /*
         * SiteOrigin Widgets
         */
        if ( ( in_array( 'siteorigin-panels/siteorigin-panels.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) || ( is_plugin_active_for_network( 'siteorigin-panels/siteorigin-panels.php' ) ) ) {

            if ( file_exists( PMS_PLUGIN_DIR_PATH . 'extend/siteorigin/functions.php' ) )
                include_once PMS_PLUGIN_DIR_PATH . 'extend/siteorigin/functions.php';

        }

        /*
         * BeaverBuilder Widgets
         */
        if ( defined( 'FL_BUILDER_VERSION' ) ) {

            if ( file_exists( PMS_PLUGIN_DIR_PATH . 'extend/beaver-builder/class-fl-builder.php' ) )
                include_once PMS_PLUGIN_DIR_PATH . 'extend/beaver-builder/class-fl-builder.php';

        }

        /*
         * WPBakery Widgets
         */
        if ( defined( 'WPB_VC_VERSION' ) ) {

            if ( file_exists( PMS_PLUGIN_DIR_PATH . 'extend/wpbakery/class-wpbakery.php' ) )
                include_once PMS_PLUGIN_DIR_PATH . 'extend/wpbakery/class-wpbakery.php';

        }

        /*
         * MailChimp for WP
         */
        if ( file_exists( PMS_PLUGIN_DIR_PATH . 'extend/mailchimp-for-wp/functions.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'extend/mailchimp-for-wp/functions.php';

        /**
         * Load modules
         */
        if ( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/modules/modules.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/modules/modules.php';

        /**
         * Load usage tracker
         */
        if ( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-plugin-usage-tracker.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/class-plugin-usage-tracker.php';


        /**
         * Hook to include needed files
         *
         */
        do_action( 'pms_include_files' );

    }

    /*
     * Dependencies for the init hook
     */
    public function init_dependencies() {

        // Setup Wizard
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-setup-wizard.php' ) )
            include_once PMS_PLUGIN_DIR_PATH . 'includes/admin/class-admin-setup-wizard.php';

    }

    /*
     * Create or update the database tables needed for the plugin to work
     * as needed
     *
     */
    public function create_tables() {

        global $wpdb;

        // If pms_member_subscriptions already exists, but does not have the 'id' column, add it before the other columns
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$this->prefix}member_subscriptions';" ) ) {
            if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$wpdb->prefix}{$this->prefix}member_subscriptions` LIKE 'id';" ) ) {
                $wpdb->query( "ALTER TABLE {$wpdb->prefix}{$this->prefix}member_subscriptions ADD `id` bigint(20) NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;" );
            }
        }

        // Add / Update the tables as needed
        $charset_collate = $wpdb->get_charset_collate();

        $sql_query = "CREATE TABLE {$wpdb->prefix}{$this->prefix}member_subscriptions (
          id bigint(20) AUTO_INCREMENT NOT NULL PRIMARY KEY,
          user_id bigint(20) NOT NULL,
          subscription_plan_id bigint(20) NOT NULL,
          start_date datetime DEFAULT NULL,
          expiration_date datetime DEFAULT NULL,
          status varchar(32) NOT NULL,
          payment_profile_id varchar(32) NOT NULL,
          payment_gateway varchar(32) NOT NULL,
          billing_amount float(10) NOT NULL,
          billing_duration int(10) NOT NULL,
          billing_duration_unit varchar(32) NOT NULL,
          billing_cycles int(10) NOT NULL,
          billing_next_payment datetime DEFAULT NULL,
          billing_last_payment datetime DEFAULT NULL,
          trial_end datetime DEFAULT NULL,
          KEY user_id (user_id),
          KEY subscription_plan_id (subscription_plan_id)
        ) {$charset_collate};
        CREATE TABLE {$wpdb->prefix}{$this->prefix}member_subscriptionmeta (
          meta_id bigint(20) AUTO_INCREMENT NOT NULL,
          member_subscription_id bigint(20) NOT NULL DEFAULT '0',
          meta_key varchar(191),
          meta_value longtext,
          PRIMARY KEY  (meta_id),
          KEY member_subscription_id (member_subscription_id),
          KEY meta_key (meta_key)
        ) {$charset_collate};
        CREATE TABLE {$wpdb->prefix}{$this->prefix}payments (
          id bigint(20) NOT NULL AUTO_INCREMENT,
          user_id bigint(20) NOT NULL,
          subscription_plan_id bigint(20) NOT NULL,
          status varchar(32) NOT NULL,
          date datetime DEFAULT NULL,
          amount float(10) NOT NULL,
          payment_gateway varchar(32) NOT NULL,
          currency varchar(32) NOT NULL,
          type varchar(64) NOT NULL,
          transaction_id varchar(32) NOT NULL,
          profile_id varchar(32) NOT NULL,
          logs longtext NOT NULL,
          ip_address varchar(64) NOT NULL,
          discount_code varchar(64) NOT NULL,
          UNIQUE KEY id (id),
          KEY user_id (user_id)
        ) {$charset_collate};
        CREATE TABLE {$wpdb->prefix}{$this->prefix}paymentmeta (
          meta_id bigint(20) AUTO_INCREMENT NOT NULL,
          payment_id bigint(20) NOT NULL DEFAULT '0',
          meta_key varchar(191),
          meta_value longtext,
          PRIMARY KEY  (meta_id),
          KEY payment_id (payment_id),
          KEY meta_key (meta_key)
        ) {$charset_collate};";

        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

        dbDelta( $sql_query );

    }


    /**
     * Registers custom meta tables with WP's $wpdb object
     *
     */
    public function register_custom_meta_tables() {

        global $wpdb;

        $wpdb->member_subscriptionmeta = $wpdb->prefix . $this->prefix . 'member_subscriptionmeta';
        $wpdb->paymentmeta = $wpdb->prefix . $this->prefix . 'paymentmeta';

    }

    /*
     * Initialize the plugin
     *
     */
    public function init() {

        // Set the main menu page
        add_action( 'admin_menu', array( $this, 'add_menu_page' ), 1 );

        add_action( 'admin_menu', array( $this, 'remove_submenu_page' ) );

        // Enqueue scripts on the front end side
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_end_scripts' ) );

        // Enqueue scripts on the admin side
        if( is_admin() )
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        // Initialize shortcodes
        add_action( 'init', array( 'PMS_Shortcodes', 'init' ) );
        add_action( 'init', array( $this, 'init_dependencies' ) );

        //Show row meta on the plugin screen (used to add links like Documentation, Support etc.).
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

        // Hook to be executed on a specific interval, by the cron job (wp_schedule_event); used to check if a subscription has expired
        add_action('pms_check_subscription_status','pms_member_check_expired_subscriptions');

        // Hook to be executed on a daily interval, by the cron job (wp_schedule_event); used to remove the user activation key from the db (make it expire) every 24 hours
        add_action('pms_remove_activation_key','pms_remove_expired_activation_key');

        // Add new actions besides the activate/deactivate ones from the Plugins page
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_plugin_action_links' ) );

    }


    /*
     * Add the main menu page of the plugin
     *
     */
    public function add_menu_page() {

        add_menu_page( __( 'Paid Member Subscriptions', 'paid-member-subscriptions' ), __( 'Paid Member Subscriptions', 'paid-member-subscriptions' ), 'manage_options', 'paid-member-subscriptions', null, plugin_dir_url( __FILE__ ).'/assets/images/pms-menu-icon.png', '71.1' );

    }


    /*
     * Remove the main menu page of the plugin, as we are using only sub-menu pages
     *
     */
    public function remove_submenu_page() {

        remove_submenu_page( 'paid-member-subscriptions', 'paid-member-subscriptions' );

    }


    public function add_plugin_action_links( $links ) {

        if ( current_user_can( 'manage_options' ) ) {
            $links[] = '<span class="delete"><a href="' . wp_nonce_url( add_query_arg( array( 'page' => 'pms-uninstall-page' ) , admin_url( 'admin.php' ) ), 'pms_uninstall_page_nonce' ) . '">' . __( 'Uninstall', 'paid-member-subscriptions' ) . '</a></span>';

            $settings_url = sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'pms-settings-page', false ), esc_html( __( 'Settings', 'paid-member-subscriptions' ) ) );

            $docs_url = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( 'https://www.cozmoslabs.com/docs/paid-member-subscriptions/' ), esc_html( __( 'Docs', 'paid-member-subscriptions' ) ) );
        }

        array_unshift( $links, $settings_url, $docs_url );

        return $links;

    }


    /*
     * Enqueue scripts for the back-end (dashboard) part of the website
     *
     * @return void
     *
     */
    public function enqueue_admin_scripts() {

        wp_enqueue_style( 'pms-style-back-end', PMS_PLUGIN_DIR_URL . 'assets/css/style-back-end.css', array(), PMS_VERSION );

    }


    /*
     * Enqueue scripts for the front-end part of the website
     *
     * @return void
     *
     */
    public function enqueue_front_end_scripts() {

        $pms_settings = get_option( 'pms_general_settings' );

        if( !empty( $pms_settings['use_pms_css'] ) && $pms_settings['use_pms_css'] == 1 )
            wp_enqueue_style( 'pms-style-front-end', PMS_PLUGIN_DIR_URL . 'assets/css/style-front-end.css', array(), PMS_VERSION );

        wp_register_script( 'pms-front-end', PMS_PLUGIN_DIR_URL . 'assets/js/front-end.js', array( 'jquery' ), PMS_VERSION );
        wp_enqueue_script( 'pms-front-end' );

        /* Add GDPR Delete Button functionality*/
        $delete_url = add_query_arg( array(
            'pms_user'   => get_current_user_id(),
            'pms_action' => 'pms_delete_user',
            'pms_nonce'  => wp_create_nonce( 'pms-user-own-account-deletion'),
        ), home_url());

        wp_localize_script( 'pms-front-end', 'pmsGdpr', array(
            'delete_url'        => $delete_url,
            'delete_text'       => sprintf(__('Type %s to confirm deleting your account and all data associated with it:', 'paid-member-subscriptions'), 'DELETE' ),
            'delete_error_text' => sprintf(__('You did not type %s. Try again!', 'paid-member-subscriptions'), 'DELETE' ),
        ));

        wp_localize_script( 'pms-front-end', 'PMS_States', pms_get_billing_states() );

        // Add chosen in the front-end if Billing Details are showing
        if( defined( 'PMS_TAX_VERSION' ) || defined( 'PMS_INV_VERSION' ) ) {
            $account_page  = pms_get_page( 'account' );
            $register_page = pms_get_page( 'register' );

            if( ( !empty( $account_page ) && $account_page == get_the_ID() ) || ( !empty( $register_page ) && $register_page == get_the_ID() ) ){
                wp_enqueue_script( 'pms-chosen', PMS_PLUGIN_DIR_URL . 'assets/libs/chosen/chosen.jquery.min.js', array( 'jquery' ), PMS_VERSION );
                wp_enqueue_style( 'pms-chosen', PMS_PLUGIN_DIR_URL . 'assets/libs/chosen/chosen.css', array(), PMS_VERSION );

                wp_localize_script( 'pms-front-end', 'PMS_ChosenStrings', array(
                    'placeholder_text' => __( 'Select an option', 'paid-member-subscriptions' ),
                    'no_results_text'  => __( 'No results match', 'paid-member-subscriptions' )
                ) );
            }
        }

    }

    /**
     * Show row meta on the plugin screen. (Used to add links like Documentation, Support etc.)
     *
     * @param	mixed $links Plugin Row Meta
     * @param	mixed $file  Plugin Base file
     * @return	array
     *
     */
    public static function plugin_row_meta( $links, $file ) {
        if ( $file == PMS_PLUGIN_BASENAME ) {

            $row_meta = array(
                'get_support'    => '<a href="' . esc_url( apply_filters( 'pms_docs_url', 'https://www.cozmoslabs.com/support/open-ticket/' ) ) . '" title="' . esc_attr( __( 'Get Support', 'paid-member-subscriptions' ) ) . '" target="_blank">' . __( 'Get Support', 'paid-member-subscriptions' ) . '</a>',
            );

            return array_merge( $links, $row_meta );
        }

        return (array) $links;
    }

}

// Let's get the party started
new Paid_Member_Subscriptions;
