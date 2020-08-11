<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends core PMS_Submenu_Page base class to create and add custom functionality
 * for the settings page in the admin section
 *
 * The settings page will contain several tabs where the user will be able to customize e-mails,
 * user messages and also set up payment gateways
 *
 */
Class PMS_Submenu_Page_Settings extends PMS_Submenu_Page {

    public $active_tab = 'general';

    /*
     * Method that initializes the class
     *
     */
    public function init() {

        // Hook the output method to the parent's class action for output instead of overwriting the
        // output method
        add_action( 'pms_output_content_submenu_page_' . $this->menu_slug, array( $this, 'output' ) );

        if ( isset( $_GET['tab'] ) )
            $this->active_tab = sanitize_text_field( $_GET['tab'] );

        add_action( 'pms_submenu_page_enqueue_admin_scripts_' . $this->menu_slug, array( $this, 'admin_scripts' ) );

    }


    /*
     * Method to output content in the custom page
     *
     */
    public function output() {

        // Set options
        $this->options = get_option( 'pms_' . $this->active_tab . '_settings', array() );

        ?>
        <div class="wrap pms-wrap">
            <h1>
                <?php
                    $tabs = $this->get_tabs();
                    echo $tabs[$this->active_tab];
                ?>

                    <div class="pms-payments-status-wrap pms-payments-status-wrap--<?php echo ( pms_is_payment_test_mode() ? 'test' : 'live' ); ?>">
                        <div class="pms-payments-status pms-payments-status--<?php echo ( pms_is_payment_test_mode() ? 'test' : 'live' ); ?>"></div>

                        <div><?php echo ( pms_is_payment_test_mode() ? __( 'Test payments are enabled', 'paid-member-subscriptions' ) : __( 'Live payments are enabled', 'paid-member-subscriptions' ) ); ?></div>
                    </div>

            </h1>

            <h3 class="nav-tab-wrapper">
                <?php
                    foreach( $this->get_tabs() as $tab_slug => $tab_name )
                        echo '<a href="' . admin_url( add_query_arg( array( 'page' => 'pms-settings-page', 'tab' => $tab_slug ), 'admin.php' ) ) . '" class="nav-tab ' . ( $this->active_tab == $tab_slug ? 'nav-tab-active' : '' ) . '">' . $tab_name . '</a>';
                ?>
            </h3>

            <?php settings_errors(); ?>

            <form method="post" enctype="multipart/form-data" encoding="multipart/form-data" action="options.php">
                <?php
                    settings_fields( 'pms_' . $this->active_tab . '_settings' );

                    ob_start();

                    if ( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/admin/views/view-page-settings-' . $this->active_tab . '.php' ) )
                        include_once 'views/view-page-settings-' . $this->active_tab . '.php';

                    $output = ob_get_clean();

                    echo apply_filters( 'pms_settings_tab_content', $output, $this->active_tab, $this->options );

                    submit_button( __( 'Save Settings', 'paid-member-subscriptions' ) );
                ?>
            </form>

        </div>

        <?php
    }


    /*
     * Callback overwrite for sanitizing settings
     *
     */
    public function sanitize_settings( $options ) {

        // Sanitize all option values
        $options = pms_array_strip_script_tags( $options );

        if ( isset( $_REQUEST['option_page'] ) ) {
            $option_page = sanitize_text_field( $_REQUEST['option_page'] );

            // If no active payment gateways are checked, add paypal_standard as default
            if( $option_page == 'pms_payments_settings' && !isset( $options['active_pay_gates'] ) )
                $options['active_pay_gates'] = array( 'paypal_standard' );

            if ( $option_page == 'pms_general_settings' ) {

                if (isset($options['register_success_page']))
                    $options['register_success_page'] = (int)$options['register_success_page'];

                if (isset($options['login_page']))
                    $options['login_page'] = (int)$options['login_page'];

                if (isset($options['register_page']))
                    $options['register_page'] = (int)$options['register_page'];

                if (isset($options['account_page']))
                    $options['account_page'] = (int)$options['account_page'];

                if (isset($options['lost_password_page']))
                    $options['lost_password_page'] = (int)$options['lost_password_page'];

                if (isset($options['edit_profile_shortcode']))
                    $options['edit_profile_shortcode'] = $options['edit_profile_shortcode'];
            }

            if( $option_page == 'pms_content_restriction_settings' && isset( $options['restricted_post_preview']['trim_content_length'] ) )
                $options['restricted_post_preview']['trim_content_length'] = (int)$options['restricted_post_preview']['trim_content_length'];

            // Sanitize admin emails field
            if( $option_page == 'pms_emails_settings' && ! empty( $options['admin_emails'] ) ) {

                $admin_emails = array_map( 'trim', explode( ',', $options['admin_emails'] ) );

                foreach( $admin_emails as $key => $admin_email ) {

                    if( ! is_email( $admin_email ) )
                        unset( $admin_emails[$key] );

                }

                $options['admin_emails'] = implode( ', ', $admin_emails );

            }

            if ( $option_page == 'pms_payments_settings' ) {
                $old_settings = get_option( 'pms_payments_settings' );

                if ( empty( $options['gateways']['paypal'] ) && !empty( $old_settings['gateways']['paypal'] ) )
                    $options['gateways']['paypal'] = $old_settings['gateways']['paypal'];

                if ( empty( $options['gateways']['stripe'] ) && !empty( $old_settings['gateways']['stripe'] ) )
                    $options['gateways']['stripe'] = $old_settings['gateways']['stripe'];
            }

            if ( $option_page == 'pms_misc_settings' ) {

                if (isset($options['gdpr_checkbox']))
                    $options['gdpr_checkbox'] = sanitize_text_field($options['gdpr_checkbox']);

                if (isset($options['gdpr_checkbox_text']))
                    $options['gdpr_checkbox_text'] = sanitize_text_field($options['gdpr_checkbox_text']);

                if (isset($options['gdpr_delete']))
                    $options['gdpr_delete'] = sanitize_text_field($options['gdpr_delete']);
            }
        }

        /**
         * Filter to sanitize plugin settings
         *
         * @param array $options
         *
         */
        $options = apply_filters( 'pms_sanitize_settings', $options );

        return $options;
    }


    /*
     * Returns the tabs we want for this page
     *
     */
    private function get_tabs() {

        $tabs = array(
            'general'              => __( 'General', 'paid-member-subscriptions' ),
            'payments'             => __( 'Payments', 'paid-member-subscriptions' ),
            'content_restriction'  => __( 'Content Restriction', 'paid-member-subscriptions' ),
            'emails'               => __( 'E-Mails', 'paid-member-subscriptions' ),
            'misc'                 => __( 'Misc', 'paid-member-subscriptions' )
        );

        return apply_filters( $this->menu_slug . '_tabs', $tabs );

    }

    public function register_settings() {

        foreach ( $this->get_tabs() as $slug => $name )
            register_setting( 'pms_' . $slug . '_settings', 'pms_' . $slug . '_settings', array( $this, 'sanitize_settings' ) );

        do_action( 'pms_register_tab_settings' );
    }

    public function admin_scripts() {

        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

        global $wp_scripts;

        // Try to detect if chosen has already been loaded
        $found_chosen = false;

        foreach( $wp_scripts as $wp_script ) {
            if( !empty( $wp_script['src'] ) && strpos($wp_script['src'], 'chosen') !== false )
                $found_chosen = true;
        }

        if( !$found_chosen ) {
            wp_enqueue_script( 'pms-chosen', PMS_PLUGIN_DIR_URL . 'assets/libs/chosen/chosen.jquery.min.js', array( 'jquery' ), PMS_VERSION );
            wp_enqueue_style( 'pms-chosen', PMS_PLUGIN_DIR_URL . 'assets/libs/chosen/chosen.css', array(), PMS_VERSION );
        }

    }


}

$pms_submenu_page_settings = new PMS_Submenu_Page_Settings( 'paid-member-subscriptions', __( 'Settings', 'paid-member-subscriptions' ), __( 'Settings', 'paid-member-subscriptions' ), 'manage_options', 'pms-settings-page', 30, 'pms_settings' );
$pms_submenu_page_settings->init();
