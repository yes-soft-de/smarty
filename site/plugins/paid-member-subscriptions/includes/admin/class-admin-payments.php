<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends core PMS_Submenu_Page base class to create and add custom functionality
 * for the payments section in the admin section
 *
 */
Class PMS_Submenu_Page_Payments extends PMS_Submenu_Page {

    public $list_table;

    /*
     * Method that initializes the class
     *
     */
    public function init() {

        // Enqueue admin scripts
        add_action( 'pms_submenu_page_enqueue_admin_scripts_' . $this->menu_slug, array( $this, 'admin_scripts' ) );

        // Hook the output method to the parent's class action for output instead of overwriting the
        // output method
        add_action( 'pms_output_content_submenu_page_' . $this->menu_slug, array( $this, 'output' ) );

        // Process different actions within the page
        add_action( 'init', array( $this, 'process_data' ) );

        // Add Ajax hooks
        add_action( 'wp_ajax_populate_subscription_price', array( $this, 'ajax_populate_subscription_price' ) );

        if( isset( $_GET['page'] ) && $_GET['page'] == 'pms-payments-page' )
            add_action( 'current_screen', array( $this, 'load_table' ) );

    }

    public function load_table() {
        $this->list_table = new PMS_Payments_List_Table();
    }

    /*
     * Method to enqueue admin scripts
     *
     */
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

    /**
     * Returns a custom message by the provided code
     *
     * @param int $code
     *
     * @return string
     *
     */
    protected function get_message_by_code( $code = 0 ) {

        $messages = array(
            1 => __( 'Payment successfully added. The subscription was also added or updated for the selected user.', 'paid-member-subscriptions' )
        );

        return ( ! empty( $messages[$code] ) ? $messages[$code] : '' );

    }


    /*
     * Method that processes data on payment admin pages
     *
     */
    public function process_data() {

        // Verify correct nonce
        if( !isset( $_REQUEST['_wpnonce'] ) || !wp_verify_nonce( $_REQUEST['_wpnonce'], 'pms_payment_nonce' ) )
            return;

        // Get current actions
        $action = !empty( $_REQUEST['pms-action'] ) ? $_REQUEST['pms-action'] : '';

        if( empty($action) )
            return;


        // Deleting a payment
        if( $action == 'delete_payment' ) {

            // Get payment id
            $payment_id = ( !empty( $_REQUEST['payment_id'] ) ? (int)$_REQUEST['payment_id'] : 0 );

            // Do nothing if there's no payment to work with
            if( $payment_id == 0 )
                return;

            $payment = pms_get_payment( $payment_id );

            if( $payment->remove() )
                $this->add_admin_notice( __( 'Payment successfully deleted.', 'paid-member-subscriptions' ), 'updated' );

        }


        // Saving / editing a payment
        if( $action == 'edit_payment' ) {

            // Get payment id
            $payment_id = ( !empty( $_REQUEST['payment_id'] ) ? (int)$_REQUEST['payment_id'] : 0 );

            // Do nothing if there's no payment to work with
            if( $payment_id == 0 )
                return;

            // Get payment and extract the object/payment vars with their value
            $payment      = pms_get_payment( $payment_id );
            $payment_vars = get_object_vars( $payment );

            // Pass through each payment var and see if the value provided by the admin is different
            foreach( $payment_vars as $payment_var => $payment_var_val ) {

                // Get the value from the form field
                $post_field_value = isset( $_POST['pms-payment-' . str_replace('_', '-', $payment_var) ] ) ? sanitize_text_field( $_POST['pms-payment-' . str_replace('_', '-', $payment_var) ] ) : '';

                // If we're handling the date value take into account the time zone difference
                // In the db we want to have universal time, not local time
                if( $payment_var == 'date' )
                    $post_field_value = date( 'Y-m-d H:i:s', strtotime( $post_field_value ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );

                // If the form value exists and differs from the one saved in the payment
                // replace it, if not simply unset the value from the object vars array
                if( $post_field_value !== '' && $post_field_value != $payment_var_val )
                    $payment_vars[$payment_var] = $post_field_value;
                else
                    unset( $payment_vars[$payment_var] );
            }

            // Subscription_id needs to be subscription_plan_id
            // This is not very consistent and should be modified
            if( !empty( $payment_vars['subscription_id'] ) ) {
                $payment_vars['subscription_plan_id'] = $payment_vars['subscription_id'];
                unset( $payment_vars['subscription_id'] );
            }

            // Update payment
            if( empty( $payment_vars ) )
                $updated = true;
            else
                $updated = $payment->update( $payment_vars );

            if( $updated )
                $this->add_admin_notice( __( 'Payment successfully updated.', 'paid-member-subscriptions' ), 'updated' );

        }


        // Adding a new payment manually
        if ( $action == 'add_payment' ) {

            if (!$this->validate_payment_data()) {
                return;
            }

            /**
             * If there are no errors proceed with inserting the payment in the db and adding the member (or updating his subscription)
             *
             */
            if ( !empty($_POST) ) {

                $form_data = pms_array_sanitize_text_field( $_POST );

                if ( !empty( $form_data['pms-member-username'] ) ) {

                    $member = pms_get_member( $form_data['pms-member-username'] );

                    if ( is_object($member) && !empty($member) ) {

                        $subscription_plan        = pms_get_subscription_plan( $form_data['pms-payment-subscription-id'] );
                        $member_subscription_data = $member->get_subscription( $form_data['pms-payment-subscription-id'] );

                        if ( !empty($subscription_plan) ) {

                           // Set member subscription status based on payment status
                            switch ( $form_data['pms-payment-status'] ) {
                                case 'failed' :
                                case 'pending' :
                                    $member_subscription_status = 'pending';
                                    break;
                                case 'refunded' :
                                    $member_subscription_status = 'canceled';
                                    break;
                                default:
                                    $member_subscription_status = 'active';
                            }

                            if( !empty( $member_subscription_data['id'] ) ){
                                // Subscription exists, extend duration if the payment is completed
                                if( $form_data['pms-payment-status'] == 'completed' ){

                                    $member_subscription = pms_get_member_subscription( $member_subscription_data['id'] );

                                    if( !empty( $member_subscription ) ){
                                        $data = array(
                                            'expiration_date' => date( 'Y-m-d 23:59:59', strtotime( $member_subscription->expiration_date . '+' . $subscription_plan->duration . ' ' . $subscription_plan->duration_unit ) ),
                                            'status'          => $member_subscription_status
                                        );

                                        $member_subscription->update( $data );

                                        pms_add_member_subscription_log( $member_subscription->id, 'admin_subscription_activated_payments' );
                                    }
                                }
                            } else {
                                // User does not have this subscription, so add it
                                $data = array(
                                    'user_id'              => $member->user_id,
                                    'subscription_plan_id' => $subscription_plan->id,
                                    'start_date'           => $form_data['pms-payment-date'],
                                    'expiration_date'      => $subscription_plan->get_expiration_date(),
                                    'status'               => $member_subscription_status
                                );

                                $member_subscription = new PMS_Member_Subscription();
                                $subscription_id     = $member_subscription->insert( $data );

                                pms_add_member_subscription_log( $subscription_id, 'admin_subscription_added_payments' );
                            }

                        }

                    }

                }

                $payment_data = array(
                    'user_id'              => $form_data['pms-member-username'],
                    'subscription_plan_id' => $form_data['pms-payment-subscription-id'],
                    'date'                 => $form_data['pms-payment-date'],
                    'amount'               => $form_data['pms-payment-amount'],
                    'type'                 => $form_data['pms-payment-type'],
                    'currency'             => pms_get_active_currency(),
                    'status'               => $form_data['pms-payment-status'],
                    'transaction_id'       => $form_data['pms-payment-transaction-id'],
                    'payment_gateway'      => 'manual'
                );

                $payment = new PMS_Payment();
                $added = $payment->insert($payment_data);

                if ( $added ) {

                    do_action( 'pms_manually_added_payment_success', $payment );

                    wp_redirect(add_query_arg(array('page' => $this->menu_slug, 'message' => '1', 'updated' => '1'), admin_url('admin.php')));
                    exit;
                }

            } // if ( !empty($_POST) )

        }

        // Used to complete payments in a scenario with the Manual payment gateway
        if ( $action == 'complete_payment' ) {

            // Get payment id
            $payment_id = ( !empty( $_REQUEST['payment_id'] ) ? (int)$_REQUEST['payment_id'] : 0 );

            // Do nothing if there's no payment to work with
            if( $payment_id == 0 )
                return;

            $payment = pms_get_payment( $payment_id );

            if( $payment->update( array( 'status' => 'completed' ) ) )
                $this->add_admin_notice( __( 'Payment successfully completed.', 'paid-member-subscriptions' ), 'updated' );

        }
    }

    /**
     * Method to validate payment data in case it's added manually by the admin
     *
     * return bool
     *
     */
    public function validate_payment_data() {

        $request_data = $_REQUEST;

        //Check to see if the a username was selected (not empty)
        if ( empty($request_data['pms-member-username']) ) {
            $this->add_admin_notice( __( 'Please select a user.', 'paid-member-subscriptions' ), 'error' );
        }
        else {
            $user_id = sanitize_text_field( $request_data['pms-member-username'] );

            // Check to see if the username exists
            $user = get_user_by( 'id', $user_id );

            if( !$user )
                $this->add_admin_notice( __( 'It seems this user does not exist.', 'paid-member-subscriptions' ), 'error' );
        }

        // Make sure a subscription plan was selected
        if ( empty($request_data['pms-payment-subscription-id']) ) {
            $this->add_admin_notice( __( 'Please select a subscription plan.', 'paid-member-subscriptions' ), 'error' );
        }

        // Make sure the payment date is not empty
        if ( empty($request_data['pms-payment-date']) ){
            $this->add_admin_notice( __( 'Please enter a date for the payment.', 'paid-member-subscriptions' ), 'error' );
        }

        // Make sure we can add the selected subscription to this user (it doesn't already have one from the same group)
        $member = pms_get_member( $request_data['pms-member-username'] );

        if ( is_object($member) && !empty($member) ) {

            $subscription_plan = pms_get_subscription_plan($request_data['pms-payment-subscription-id']);

            if ( !empty($member->subscriptions) && !empty($subscription_plan) ) {

                foreach ($member->subscriptions as $member_subscription) {

                    if ( ($member_subscription['subscription_plan_id'] != $subscription_plan->id) &&
                        ( pms_get_subscription_plans_group_parent_id($member_subscription['subscription_plan_id']) == pms_get_subscription_plans_group_parent_id($subscription_plan->id) ) ) {

                        $existing_subscription = new PMS_Subscription_Plan($member_subscription['subscription_plan_id']);

                        $this->add_admin_notice( sprintf(__('This user already has a subscription (%s) from the same group with the one you selected. Select it or remove it to be able to complete this payment.', 'paid-member-subscriptions'), $existing_subscription->name), 'error');
                        break;
                    }

                }

            }
        }

        if( $this->has_admin_notice( 'error' ) )
            return false;
        else
            return true;
    }


    /**
     * Method to output content in the custom page
     *
     */
    public function output() {

        // Check if payments cron is defined and if not, add it
        if ( ! wp_next_scheduled( 'pms_cron_process_member_subscriptions_payments' ) )
            wp_schedule_event( time(), 'daily', 'pms_cron_process_member_subscriptions_payments' );


        // Display the edit payment view
        if( isset( $_GET['pms-action'] ) && ( $_GET['pms-action'] == 'edit_payment' || $_GET['pms-action'] == 'add_payment' ) )
            include_once 'views/view-page-payments-add-new-edit.php';

        // Display all payments table
        else
            include_once 'views/view-page-payments-list-table.php';

    }

    /**
     * Method that returns the price of a subscription plan.
     * We use this when adding a new payment to automatically fill in the price based on the selected subscription plan
     *
     */
    public function ajax_populate_subscription_price() {

        $subscription_plan_id = (int)trim( $_POST['subscription_plan_id'] );

        if( ! empty( $subscription_plan_id ) ) {

            $subscription_plan = pms_get_subscription_plan( $subscription_plan_id );

            echo pms_sanitize_date( $subscription_plan->price );

        } else
            echo '';

        wp_die();

    }


	/**
     * Method that adds Screen Options to Payments page
     *
     */
	public function add_screen_options() {

		$args = array(
			'label' => 'Payments per page',
			'default' => 10,
			'option' => 'pms_payments_per_page'
		);

		add_screen_option( 'per_page', $args );

	}

}

$pms_submenu_page_payments = new PMS_Submenu_Page_Payments( 'paid-member-subscriptions', __( 'Payments', 'paid-member-subscriptions' ), __( 'Payments', 'paid-member-subscriptions' ), 'manage_options', 'pms-payments-page', 20 );
$pms_submenu_page_payments->init();
