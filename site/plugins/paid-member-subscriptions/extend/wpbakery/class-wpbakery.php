<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class PMS_WPBakery_Widgets {
    function __construct() {

        add_action( 'init', array( $this, 'add_widgets' ) );

        add_shortcode( 'pms-wpb-register', array( $this, 'register' ) );

    }

    public function add_widgets() {

        // [pms-register]
        vc_map( array(
            'name'        => __( 'PMS Register', 'paid-member-subscriptions' ),
            'description' => __( 'Insert the [pms-register] shortcode', 'paid-member-subscriptions' ),
            'base'        => 'pms-wpb-register',
            'icon'        => PMS_PLUGIN_DIR_URL . 'assets/images/pms_logo.png', // or css class name which you can reffer in your css file later. Example: "vc_extend_my_class"
            'category'    => __( 'Paid Member Subscriptions', 'paid-member-subscriptions' ),
            'params'      => array(
                array(
                    'type'        => 'textfield',
                    'holder'      => 'div',
                    'heading'     => __( 'Subscription Plans', 'paid-member-subscriptions' ),
                    'param_name'  => 'subscription_plans',
                    'description' => sprintf( __( 'Comma separated list of subscription plans ids to show. %sRead more%s', 'paid-member-subscriptions' ), '<a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/shortcodes/#Parameters">', '</a>')
                ),
                array(
                    'type'        => 'textfield',
                    'holder'      => 'div',
                    'heading'     => __( 'Selected plan', 'paid-member-subscriptions' ),
                    'param_name'  => 'selected_subscription_plan',
                    'description' => sprintf( __( 'ID of the plan which should be selected by default. %sRead more%s', 'paid-member-subscriptions' ), '<a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/shortcodes/#Parameters">', '</a>')
                ),
                array(
                    'type'       => 'dropdown',
                    'holder'     => 'div',
                    'heading'    => __( 'Plans position', 'paid-member-subscriptions' ),
                    'param_name' => 'plans_position',
                    'value'      => array( 'top', 'bottom' ),
                    'std'        => 'bottom',
                ),
            )
        ) );

        // [pms-account]
        vc_map( array(
            'name'        => __( 'PMS Account', 'paid-member-subscriptions' ),
            'description' => __( 'Add the [pms-account] shortcode', 'paid-member-subscriptions' ),
            'base'        => 'pms-account',
            'icon'        => PMS_PLUGIN_DIR_URL . 'assets/images/pms_logo.png', // or css class name which you can reffer in your css file later. Example: "vc_extend_my_class"
            'category'    => __( 'Paid Member Subscriptions', 'paid-member-subscriptions' ),
            'params'      => array(
                array(
                    'type'       => 'dropdown',
                    'holder'     => 'div',
                    'heading'    => __( 'Show Tabs', 'paid-member-subscriptions' ),
                    'param_name' => 'show_tabs',
                    'value'      => array( 'yes', 'no' ),
                    'std'        => 'yes',
                ),
            )
        ) );

        // [pms-login]
        vc_map( array(
            'name'        => __( 'PMS Login', 'paid-member-subscriptions' ),
            'description' => __( 'Create a login form using the [pms-login] shortcode', 'paid-member-subscriptions' ),
            'base'        => 'pms-login',
            'icon'        => PMS_PLUGIN_DIR_URL . 'assets/images/pms_logo.png', // or css class name which you can reffer in your css file later. Example: "vc_extend_my_class"
            'category'    => __( 'Paid Member Subscriptions', 'paid-member-subscriptions' ),
            'params'      => array(
                array(
                    'type'        => 'textfield',
                    'holder'      => 'div',
                    'heading'     => __( 'After Login redirect URL', 'paid-member-subscriptions' ),
                    'param_name'  => 'after_login_redirect_url',
                    'description' => sprintf( __( 'Enter the URL where users should be redirected after logging in. %sRead more%s', 'paid-member-subscriptions' ), '<a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/shortcodes/#Parameters-2">', '</a>')
                ),
                array(
                    'type'        => 'textfield',
                    'holder'      => 'div',
                    'heading'     => __( 'After Logout redirect URL', 'paid-member-subscriptions' ),
                    'param_name'  => 'after_logout_redirect_url',
                    'description' => sprintf( __( 'Enter the URL where users should be redirected after logging out. %sRead more%s', 'paid-member-subscriptions' ), '<a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/shortcodes/#Parameters-2">', '</a>')
                ),
            )
        ) );

        // [pms-recover-password]
        vc_map( array(
            'name'        => __( 'PMS Recover Password', 'paid-member-subscriptions' ),
            'description' => __( 'Add a password recovery form using [pms-recover-password]', 'paid-member-subscriptions' ),
            'base'        => 'pms-recover-password',
            'icon'        => PMS_PLUGIN_DIR_URL . 'assets/images/pms_logo.png', // or css class name which you can reffer in your css file later. Example: "vc_extend_my_class"
            'category'    => __( 'Paid Member Subscriptions', 'paid-member-subscriptions' ),
            'params'      => array(
                array(
                    'type'        => 'textfield',
                    'holder'      => 'div',
                    'heading'     => __( 'After recovery redirect URL', 'paid-member-subscriptions' ),
                    'param_name'  => 'after_recovery_redirect_url',
                    'description' => sprintf( __( 'Enter the URL where users should be redirected after a sucessful password reset. %sRead more%s', 'paid-member-subscriptions' ), '<a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/shortcodes/#Parameters-6">', '</a>')
                ),
            )
        ) );
    }

    public function register( $atts, $content = null ) {
        $atts = shortcode_atts( array(
            'subscription_plans' => array(),
            'plans_position'     => 'bottom',
            'selected_subscription_plan'           => ''
        ), $atts );

        if ( !empty( $atts['subscription_plans'] ) )
            $plans = 'subscription_plans="'.$atts['subscription_plans'].'"';
        else
            $plans = '';

        return do_shortcode( '[pms-register '.$plans.' selected="'.$atts['selected_subscription_plan'].'" plans_position="'.$atts['plans_position'].'"]' );
    }


}

new PMS_WPBakery_Widgets();
