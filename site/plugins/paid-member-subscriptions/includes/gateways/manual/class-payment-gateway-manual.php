<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class PMS_Payment_Gateway_Manual extends PMS_Payment_Gateway {

    /*
     * The payment gateway slug
     *
     * @access private
     * @var string
     *
     */
    private $payment_gateway = 'manual';


    /**
     * The features supported by the payment gateway
     *
     * @access public
     * @var array
     *
     */
    public $supports;


    public function init() {

        $this->supports = array(
            'subscription_sign_up_fee',
            'subscription_free_trial'
        );

        // Add custom user messages for this gateway
        add_filter( 'pms_message_gateway_payment_action', array( $this, 'success_messages' ), 10, 4 );

        // Automatically activate the member's subscription when completing the payment
        add_action( 'pms_payment_update', array( $this, 'activate_member_subscription' ), 10, 3 );

        // Remove the Retry payment action for this gateway
        add_action( 'pms_output_subscription_plan_pending_retry_payment', array( $this, 'remove_retry_payment' ), 10, 3 );

        add_action( 'pms_output_subscription_plan_action_renewal', array( $this, 'remove_renewal_action' ), 10, 4 );

        // Change payment type in case of payment generated after a Free Trial
        add_filter( 'pms_cron_process_member_subscriptions_payment_data', array( $this, 'change_free_trial_payment_type' ), 20, 2 );

        // Add a new action if the payment is manual so it can be completed from the table
        add_filter( 'pms_payments_list_table_entry_actions', array( $this, 'add_mark_as_completed_action' ), 20, 2 );

    }


    /*
     * Process payment
     *
     */
    public function process_sign_up() {

        // Activate subscription if plan has a free trial
        if( !empty( $this->subscription_data['trial_end'] ) ){

            $subscription = pms_get_current_subscription_from_tier( $this->user_id, $this->subscription_data['subscription_plan_id'] );

            $subscription->update( array( 'status' => 'active', 'billing_next_payment' => $this->subscription_data['trial_end'], 'billing_amount' => $this->subscription_plan->price ) );

            pms_add_member_subscription_log( $subscription->id, 'subscription_trial_started', array( 'until' => $this->subscription_data['trial_end'] ) );

            pms_add_member_subscription_log( $subscription->id, 'subscription_activated' );

        }

        // Get payment
        $payment = pms_get_payment( $this->payment_id );

        do_action( 'pms_manual_gateway_signup_processed', $this->subscription_data, $this->payment_id );

        // Success Redirect
        if( isset( $_POST['pmstkn'] ) ) {
            $redirect_url = add_query_arg(array('pms_gateway_payment_id' => base64_encode($this->payment_id), 'pmsscscd' => base64_encode('subscription_plans')), $this->redirect_url);
            wp_redirect($redirect_url);
            exit;
        }

    }

    // Adds a pending payment for the user when there's a free trial
    public function process_payment( $payment_id = 0, $subscription_id = 0 ) {
        $subscription = pms_get_member_subscription( $subscription_id );

        $data = array(
            'status'               => 'pending',
            'billing_next_payment' => '',
            'trial_end'            => ''
        );

        $subscription->update( $data );

        pms_add_member_subscription_log( $subscription->id, 'subscription_trial_end' );

        return false;
    }


    /*
     * Change the default success message for the different payment actions
     *
     * @param string $message
     * @param string $payment_status
     * @param string $payment_action
     * @param obj $payment
     *
     * @return string
     *
     */
    public function success_messages( $message, $payment_status, $payment_action, $payment ) {

        if( $payment->payment_gateway !== $this->payment_gateway )
            return $message;

        // We're interested in changing only the success messages for paid subscriptions
        // which will all have the "pending" status
        if( $payment_status != 'pending' )
            return $message;

        switch( $payment_action ) {

            case 'upgrade_subscription':
                $message = __( 'Thank you for upgrading. The changes will take effect after the payment is received.', 'paid-member-subscriptions' );
                break;

            case 'renew_subscription':
                $message = __( 'Thank you for renewing. The changes will take effect after the payment is received.', 'paid-member-subscriptions' );
                break;

            case 'new_subscription':
                $message = __( 'Thank you for subscribing. The subscription will be activated after the payment is received.', 'paid-member-subscriptions' );
                break;

            case 'retry_payment':
                $message = __( 'The subscription will be activated after the payment is received.', 'paid-member-subscriptions' );
                break;

            default:
                break;

        }

        return $message;

    }


    /**
     * Activates the member's account when the payment is marked as complete.
     * If the subscription is already active, add the extra time to the subscription expiration date
     *
     * @param int   $payment_id
     * @param array $data         - an array with modifications made when saving the payment in the back-end
     * @param array $old_data     - the array of values representing the payment before the update
     *
     * @return void
     *
     */
    public function activate_member_subscription( $payment_id, $data, $old_data ) {

        if( empty( $data['status'] ) || $data['status'] != 'completed' )
            return;

        $payment = pms_get_payment( $payment_id );

        if( $payment->payment_gateway !== $this->payment_gateway )
            return;

        if( $payment->type == 'subscription_upgrade_payment' ) {

            $old_subscription = pms_get_current_subscription_from_tier( $payment->user_id, $payment->subscription_id );

            if( !empty( $old_subscription ) && !empty( $old_subscription->id ) ) {

                $old_plan_id = $old_subscription->subscription_plan_id;

                $subscription_plan = pms_get_subscription_plan( $payment->subscription_id );

                $subscription_data = array(
                    'user_id'              => $payment->user_id,
                    'subscription_plan_id' => $subscription_plan->id,
                    'start_date'           => date('Y-m-d H:i:s'),
                    'expiration_date'      => $subscription_plan->get_expiration_date(),
                    'status'               => 'active',
                    'payment_gateway'      => $this->payment_gateway,
                );

                $old_subscription->update( $subscription_data );

                pms_add_member_subscription_log( $old_subscription->id, 'subscription_upgrade_success', array( 'old_plan' => $old_plan_id, 'new_plan' => $subscription_plan->id ) );

            }

        } else {

            $member_subscriptions = pms_get_member_subscriptions( array( 'user_id' => $payment->user_id, 'subscription_plan_id' => $payment->subscription_id ) );

            if( empty( $member_subscriptions ) )
                return;

            $member_subscription = $member_subscriptions[0];

            if( ! empty( $member_subscription ) ) {

                $subscription_plan = pms_get_subscription_plan( $payment->subscription_id );

                if ( $member_subscription->status == 'active' )
                    $member_subscription->update( array( 'expiration_date' => date( 'Y-m-d H:i:s', strtotime( pms_sanitize_date($member_subscription->expiration_date) . '+' . $subscription_plan->duration . ' ' . $subscription_plan->duration_unit ) ) ) );
                else if ( $member_subscription->status == 'expired' )
                    $member_subscription->update( array( 'status' => 'active', 'expiration_date' => date( 'Y-m-d H:i:s', strtotime( date( 'Y-m-d H:i:s' ) . '+' . $subscription_plan->duration . ' ' . $subscription_plan->duration_unit ) ) ) );
                else if ( $member_subscription->status == 'canceled' ) {
                    if ( strtotime( $member_subscription->expiration_date ) > strtotime( 'now' ) )
                        $timestamp = strtotime( pms_sanitize_date($member_subscription->expiration_date) . '+' . $subscription_plan->duration . ' ' . $subscription_plan->duration_unit );
                    else
                        $timestamp = strtotime( date( 'Y-m-d H:i:s' ) . '+' . $subscription_plan->duration . ' ' . $subscription_plan->duration_unit );

                    $member_subscription->update( array( 'status' => 'active', 'expiration_date' => date( 'Y-m-d H:i:s', $timestamp ) ) );
                }
                else
                    $member_subscription->update( array( 'status' => 'active' ) );

                pms_add_member_subscription_log( $member_subscription->id, 'admin_subscription_activated_payments' );

            }

        }

    }

    public function remove_retry_payment( $output, $subscription_plan, $subscription ) {
        if ( !empty( $subscription['payment_gateway'] ) && $subscription['payment_gateway'] == 'manual' )
            return;

        return $output;
    }

    /**
     * For manual gateway payments, do not let users request a Renewal more than once.
     *
     * @param  string    $output
     * @param  object    $subscription_plan
     * @param  array     $subscription
     * @param  int       $user_id
     * @return string|void
     */
    public function remove_renewal_action( $output, $subscription_plan, $subscription, $user_id ) {

        $payments = pms_get_payments( array( 'user_id' => $user_id ) );

        foreach( $payments as $payment ) {
            if ( $payment->payment_gateway == 'manual' && $payment->status == 'pending' && $payment->type == 'subscription_renewal_payment' )
                return;
        }

        return $output;

    }

    public function change_free_trial_payment_type( $payment_data, $subscription ){

        if( $subscription->payment_gateway == 'manual' )
            $payment_data['type'] = 'subscription_initial_payment';

        return $payment_data;
    }

    public function add_mark_as_completed_action( $actions, $item ){

        if( empty( $item['id'] ) )
            return $actions;

        $payment = pms_get_payment( $item['id'] );

        if( empty( $payment->id ) )
            return $actions;

        if( $payment->payment_gateway != 'manual' || $payment->status != 'pending' )
            return $actions;

        $delete_action = $actions['delete'];
        unset( $actions['delete'] );

        $actions['complete_payment'] = '<a href="' . wp_nonce_url( add_query_arg( array( 'pms-action' => 'complete_payment', 'payment_id' => $item['id'] ) ), 'pms_payment_nonce' ) . '">' . __( 'Complete Payment', 'paid-member-subscriptions' ) . '</a>';

        $actions['delete'] = $delete_action;

        return $actions;
    }

}
