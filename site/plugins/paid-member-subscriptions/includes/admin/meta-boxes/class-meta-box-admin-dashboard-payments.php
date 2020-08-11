<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin WP Dashboard recent payments meta box class
 *
 */
Class PMS_Dashboard_Payments_Summary {

    /*
     * Constructor
     *
     */
    public function __construct() {

        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_meta_box' ) );

    }


    /*
     * Adds a new dashboard widget for admins
     *
     */
    public function add_dashboard_meta_box() {

        if( !current_user_can( 'manage_options' ) )
            return;

        wp_add_dashboard_widget( 'pms_payments_summary', __( 'Paid Member Subscriptions Payments Summary', 'paid-member-subscriptions' ), array( $this, 'output_payments_summary' ) );

    }


    /*
     * Callback for the output of the Payments Summary meta-box
     *
     */
    public function output_payments_summary() {

        include 'views/view-meta-box-admin-dashboard-payments.php';

    }

}

// Fire it up!
new PMS_Dashboard_Payments_Summary;
