<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class PMS_Setup_Wizard {
    private $step             = '';
    private $steps            = array();
    private $general_settings = array();
    public $kses_args         = array(
        'strong' => array()
    );

    public function __construct(){
        if( apply_filters( 'pms_run_setup_wizard', true ) && current_user_can( 'manage_options' ) ){
            add_action( 'admin_menu', array( $this, 'add_page' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
            add_action( 'admin_init', array( $this, 'setup_wizard' ) );
            add_action( 'admin_init', array( $this, 'redirect_to_setup' ) );
            add_action( 'wp_ajax_pms_create_subscription_pages', array( $this, 'ajax_create_subscription_pages' ) );
        }
    }

    public function add_page(){
        // add_submenu_page( 'pms-setup', 'pms-setup', 'pms-setup', 'manage_options', 'pms-setup', array( __CLASS__, 'setup_wizard' ) );
        add_dashboard_page( '', '', 'manage_options', 'pms-setup', '' );
    }

    public function enqueue_scripts_and_styles(){
        if( isset( $_GET['page'] ) && $_GET['page'] == 'pms-setup' ) {
            wp_enqueue_style( 'pms-setup-wizard', PMS_PLUGIN_DIR_URL . 'assets/css/style-setup-wizard.css', array(), PMS_VERSION );
            wp_enqueue_script( 'pms-wizard-js', PMS_PLUGIN_DIR_URL . 'assets/js/admin/setup-wizard.js', array( 'jquery' ), PMS_VERSION );
        }
    }

    public function setup_wizard(){
        if( empty( $_GET['page'] ) || $_GET['page'] != 'pms-setup' )
            return;

        $this->general_settings = get_option( 'pms_general_settings', array() );

        $default_steps = array(
            'general'  => __( 'Settings', 'paid-member-subscriptions' ),
            'payments' => __( 'Payments', 'paid-member-subscriptions' ),
            'next'     => __( 'Next Steps', 'paid-member-subscriptions' )
        );

        reset( $default_steps );

        $this->steps = apply_filters( 'pms_setup_wizard_steps', $default_steps );
        $this->step  = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : key( $default_steps );

        if( !empty( $_POST['pms_setup_wizard_nonce'] ) )
            $this->save_data();

        include_once 'views/view-page-setup-wizard.php';

        exit;
    }

    private function save_data(){
        check_admin_referer( 'pms-setup-wizard-nonce', 'pms_setup_wizard_nonce' );

        //save data
        if( $this->step === 'general' ){
            $settings = get_option( 'pms_general_settings', array() );

            if( isset( $_POST['pms_automatically_login'] ) )
                $settings['automatically_log_in'] = sanitize_text_field( $_POST['pms_automatically_login'] );
            else
                unset( $settings['automatically_log_in'] );

            if( isset( $_POST['pms_account_sharing'] ) )
                $settings['prevent_account_sharing'] = sanitize_text_field( $_POST['pms_account_sharing'] );
            else
                unset( $settings['prevent_account_sharing'] );

            if( isset( $_POST['pms_redirect_default'] ) )
                $settings['redirect_default_wp'] = sanitize_text_field( $_POST['pms_redirect_default'] );
            else
                unset( $settings['redirect_default_wp'] );

            if( !empty( $settings ) )
                update_option( 'pms_general_settings', $settings );

        } else if( $this->step === 'payments' ){
            $settings = get_option( 'pms_payments_settings', array() );

            if( isset( $_POST['pms_payments_currency'] ) )
                $settings['currency'] = sanitize_text_field( $_POST['pms_payments_currency'] );

            $settings['active_pay_gates'] = array();

            if( isset( $_POST['pms_gateway_offline'] ) ){
                $settings['active_pay_gates'][] = 'manual';
                $settings['default_payment_gateway'] = 'manual';
            }

            if( isset( $_POST['pms_gateway_paypal_standard'] ) ){
                $settings['active_pay_gates'][] = 'paypal_standard';
                $settings['default_payment_gateway'] = 'paypal_standard';
            }

            if( isset( $_POST['pms_gateway_paypal_email_address'] ) ){
                $settings['gateways']['paypal_standard'] = array(
                    'email_address' => sanitize_text_field( $_POST['pms_gateway_paypal_email_address'] )
                );
            }

            if( !empty( $settings ) )
                update_option( 'pms_payments_settings', $settings );
        }

        //redirect to the next step at the end
        wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    private function get_next_step_link( $step = '' ){
        if( !$step )
            $step = $this->step;

        $keys = array_keys( $this->steps );

        if( end( $keys ) === $step )
            return admin_url();

        $step_index = array_search( $step, $keys, true );

        if( $step_index === false )
            return '';

        return add_query_arg( 'step', $keys[$step_index + 1] );
    }

    public function ajax_create_subscription_pages(){

        $pages = array(
            'register' => array(
                'title'   => 'Register',
                'option'  => 'register_page',
                'content' => '[pms-register]',
            ),
            'login' => array(
                'title'   => 'Login',
                'option'  => 'login_page',
                'content' => '[pms-login]',
            ),
            'account' => array(
                'title'   => 'Account',
                'option'  => 'account_page',
                'content' => '[pms-account]',
            ),
            'reset_password' => array(
                'title'   => 'Password Reset',
                'option'  => 'lost_password_page',
                'content' => '[pms-recover-password]',
            ),
        );

        foreach( $pages as $key => $page )
            $this->create_page( $page['option'], $page['title'], $page['content'] );

        update_option( 'pms_general_settings', $this->general_settings );

        die('success');
    }

    private function create_page( $option, $title, $content = '' ){
        if( empty( $this->general_settings ) )
            $this->general_settings = get_option( 'pms_general_settings', array() );

        //try to find an existing page with the shortcode
        if( empty( $this->general_settings[$option] ) || $this->general_settings[$option] == '-1' ) {

            if( !empty( $content ) ){
                global $wpdb;

                $shortcode = str_replace( array( '<!-- wp:shortcode -->', '<!-- /wp:shortcode -->' ), '', $content );
                $existing_page = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", '%' . $shortcode . '%' ) );

                if( !empty( $existing_page ) ) {
                    $this->general_settings[$option] = $existing_page;

                    return $existing_page;
                }
            }

            $page = array(
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_title'   => $title,
                'post_content' => $content
            );

            $page_id = wp_insert_post( $page );
            $this->general_settings[$option] = $page_id;
        }
    }

    public function show_pages_button(){
        if( empty( $this->general_settings ) )
            $this->general_settings = get_option( 'pms_general_settings', array() );

        $pages = array( 'register_page', 'login_page', 'account_page', 'lost_password_page' );

        $show_button = false;

        foreach( $pages as $page ){
            if( empty( $this->general_settings[$page] ) || $this->general_settings[$page] == '-1' )
                $show_button = true;
        }

        return $show_button;
    }

    public function check_value( $slug ){
        if( $slug == 'automatically_log_in' && !get_option( 'pms_already_installed' ) )
            return true;

        if( !empty( $this->general_settings[$slug] ) && $this->general_settings[$slug] == '1' )
            return true;

        return false;
    }

    public function check_gateway( $slug ){
        if( in_array( $slug, pms_get_active_payment_gateways() ) )
            return true;

        return false;
    }

    public function redirect_to_setup(){
        $run_setup = get_transient( 'pms_run_setup_wizard' );

        if( $run_setup == true ){
            delete_transient( 'pms_run_setup_wizard' );
            wp_safe_redirect( admin_url( 'index.php?page=pms-setup' ) );
            die();
        }
    }
}

new PMS_Setup_Wizard();
