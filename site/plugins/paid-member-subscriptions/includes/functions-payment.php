<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Wrapper function to return a payment object
 *
 * @param int $payment_id
 *
 */
function pms_get_payment( $payment_id = 0 ) {

    return new PMS_Payment( $payment_id );

}


/**
 * Return payments filterable by an array of arguments
 *
 * @param array $args
 *
 * @return array
 *
 */
function pms_get_payments( $args = array() ) {

    global $wpdb;

    $defaults = array(
        'order'                => 'DESC',
        'orderby'              => 'id',
        'offset'               => '',
        'status'               => '',
        'type'                 => '',
        'user_id'              => '',
        'subscription_plan_id' => '',
        'profile_id'           => '',
        'transaction_id'       => '',
        'date'                 => '',
        'search'               => ''
    );

    $args = apply_filters( 'pms_get_payments_args', wp_parse_args( $args, $defaults ), $args, $defaults );

    // If Limit is empty, add a default one
    if ( empty( $args['number'] ) )
        $args['number'] = 10;

    // Start query string
    $query_string       = "SELECT pms_payments.* ";

    // Query string sections
    $query_from         = "FROM {$wpdb->prefix}pms_payments pms_payments ";
    $query_inner_join   = "INNER JOIN {$wpdb->users} users ON pms_payments.user_id = users.id ";
    $query_inner_join   = $query_inner_join . "INNER JOIN {$wpdb->posts} posts ON pms_payments.subscription_plan_id = posts.id ";
    $query_where        = "WHERE 1=%d ";

    // Add search query
    if( !empty($args['search']) ) {
        $search_term    = sanitize_text_field( $args['search'] );
        $query_where    = $query_where . " AND " . " ( pms_payments.transaction_id LIKE '%s' OR users.user_nicename LIKE '%%%s%%' OR posts.post_title LIKE '%%%s%%' ) ". " ";
    }

    // Filter by status
    if( !empty( $args['status'] ) ) {
        $status         = sanitize_text_field( $args['status'] );
        $query_where    = $query_where . " AND " . " pms_payments.status LIKE '{$status}'";
    }

    /*
     * Filter by date
     * Can be filtered by - a single date that will return payments from that date
     *                    - an array with two dates that will return payments between the two dates
     */
    if( !empty( $args['date'] ) ) {

        if( is_array( $args['date'] ) && !empty( $args['date'][0] ) && !empty( $args['date'][1] ) ) {

            $args['date'][0] = sanitize_text_field( $args['date'][0] );
            $args['date'][1] = sanitize_text_field( $args['date'][1] );

            $query_where = $query_where . " AND " . " ( pms_payments.date BETWEEN '{$args['date'][0]}' AND '{$args['date'][1]}' )";

        } elseif( is_string( $args['date'] ) ) {

            $args['date'] = sanitize_text_field( $args['date'] );

            $query_where = $query_where . " AND " . " pms_payments.date LIKE '%%{$args['date']}%%'";

        }

    }

    // Filter by type
    if( !empty( $args['type'] ) ) {
        $type = sanitize_text_field( $args['type'] );
        $query_where    = $query_where . " AND " . " pms_payments.type LIKE '{$type}'";
    }

    // Filter by profile_id
    if( !empty( $args['profile_id'] ) ) {
        $profile_id = sanitize_text_field( $args['profile_id'] );
        $query_where    = $query_where . " AND " . " pms_payments.profile_id LIKE '{$profile_id}'";
    }

    // Filter by user_id
    if( !empty( $args['user_id'] ) ) {
        $user_id = (int)trim( $args['user_id'] );
        $query_where    = $query_where . " AND " . " pms_payments.user_id = {$user_id}";
    }

    // Filter by subscription_plan_id
    if( !empty( $args['subscription_plan_id'] ) ) {
        $subscription_plan_id = (int)trim( $args['subscription_plan_id'] );
        $query_where          = $query_where . " AND " . " pms_payments.subscription_plan_id = {$subscription_plan_id}";
    }

    // Filter by transaction_id
    if( !empty( $args['transaction_id'] ) ) {
        $transaction_id = sanitize_text_field( $args['transaction_id'] );
        $query_where    = $query_where . " AND " . " pms_payments.transaction_id = '{$transaction_id}'";
    }

    $query_order_by = '';
    if ( !empty($args['orderby']) )
        $query_order_by = " ORDER BY pms_payments." . sanitize_text_field( $args['orderby'] ) . ' ';

    $query_order = strtoupper( sanitize_text_field( $args['order'] ) ) . ' ';

    $query_limit        = '';
    if( $args['number'] && $args['number'] != '-1' )
        $query_limit    = 'LIMIT ' . (int)trim( $args['number'] ) . ' ';

    $query_offset       = '';
    if( $args['offset'] )
        $query_offset   = 'OFFSET ' . (int)trim( $args['offset'] ) . ' ';

    // Concatenate query string
    $query_string .= $query_from . $query_inner_join . $query_where . $query_order_by . $query_order . $query_limit . $query_offset;


    // Return results
    if (!empty($search_term))
        $data_array = $wpdb->get_results( $wpdb->prepare( $query_string, 1, $wpdb->esc_like( $search_term ) , $wpdb->esc_like( $search_term ), $wpdb->esc_like( $search_term ) ), ARRAY_A );
    else
        $data_array = $wpdb->get_results( $wpdb->prepare( $query_string, 1 ), ARRAY_A );

    $payments = array();

    if( !empty( $data_array ) ) {
        foreach( $data_array as $key => $data ) {

            // Inconsistency fix between the db table row name and
            // the PMS_Payment property
            if( !empty( $data['subscription_plan_id'] ) )
                $data['subscription_id'] = $data['subscription_plan_id'];

            $payment = new PMS_Payment();
            $payment->set_instance( $data );

            $payments[] = $payment;
        }
    }

    /**
     * Filter payments just before returning them
     *
     * @param array $payments - the array of returned payments from the db
     * @param array $args     - the arguments used to query the payments from the db
     *
     */
    $payments = apply_filters( 'pms_get_payments', $payments, $args );

    return $payments;

}


/**
 * Returns the metadata for a given payment
 *
 * @param int    $payment_id
 * @param string $meta_key
 * @param bool   $single
 *
 * @return mixed - single metadata value | array of values
 *
 */
function pms_get_payment_meta( $payment_id = 0, $meta_key = '', $single = false ) {

    return get_metadata( 'payment', $payment_id, $meta_key, $single );

}


/**
 * Adds the metadata for a payment
 *
 * @param int    $payment_id
 * @param string $meta_key
 * @param string $meta_value
 * @param bool   $unique
 *
 * @return mixed - int | false
 *
 */
function pms_add_payment_meta( $payment_id = 0, $meta_key = '', $meta_value = '', $unique = false ) {

    return add_metadata( 'payment', $payment_id, $meta_key, $meta_value, $unique );

}


/**
 * Updates the metadata for a payment
 *
 * @param int    $payment_id
 * @param string $meta_key
 * @param string $meta_value
 * @param string $prev_value
 *
 * @return bool
 *
 */
function pms_update_payment_meta( $payment_id = 0, $meta_key = '', $meta_value = '', $prev_value = '' ) {

    return update_metadata( 'payment', $payment_id, $meta_key, $meta_value, $prev_value );

}


/**
 * Deletes the metadata for a payment
 *
 * @param int    $payment_id
 * @param string $meta_key
 * @param string $meta_value
 * @param string $delete_all - If true, delete matching metadata entries for all payments, ignoring
 *                             the specified payment_id. Otherwise, only delete matching metadata entries
 *                             for the specified payment_id.
 *
 */
function pms_delete_payment_meta( $payment_id = 0, $meta_key = '', $meta_value = '', $delete_all = false ) {

    return delete_metadata( 'payment', $payment_id, $meta_key, $meta_value, $delete_all );

}


/**
 * Returns the total number of payments from the db
 *
 * @param array $args  - array of arguments to filter the count for
 *
 * @return int
 *
 */
function pms_get_payments_count( $args = array() ) {

    global $wpdb;

    /**
     * Base query string
     */
    $query_string = "SELECT COUNT(pms_payments.id) FROM {$wpdb->prefix}pms_payments pms_payments ";

    /**
     * Inner join
     */
    $query_inner_join = "INNER JOIN {$wpdb->users} users ON pms_payments.user_id = users.id ";
    $query_inner_join .= "INNER JOIN {$wpdb->posts} posts ON pms_payments.subscription_plan_id = posts.id ";


    /**
     * Where clauses
     */
    $query_where  = "WHERE 1=%d ";

    // Filter by search
    if( !empty( $args['search'] ) ) {
        $search = sanitize_text_field( $args['search'] );
        $query_where .= " AND ( pms_payments.transaction_id LIKE '%%{$search}%%' OR users.user_nicename LIKE '%%{$search}%%' OR posts.post_title LIKE '%%{$search}%%' ) ". " ";
    }

    // Filter by status
    if( !empty( $args['status'] ) ) {
        $status = sanitize_text_field( $args['status'] );
        $query_where .= "AND pms_payments.status = '{$status}' ";
    }


    /**
     * Get cached version first
     *
     */
    $key   = md5( 'pms_payments_count_' . serialize( $args ) );
    $count = get_transient( $key );


    /**
     * Make db query if cache is empty and set the cache
     *
     */
    if( false === $count ) {

        $count = $wpdb->get_var( $wpdb->prepare( $query_string . $query_inner_join . $query_where, 1 ) );

        /**
         * The expiration time ( in seconds ) for the cached payments count returned for
         * the given args
         *
         * @param array $args
         *
         */
        $cache_time = apply_filters( 'pms_payments_count_cache_time', 1800, $args );

        set_transient( $key, $count, $cache_time );

    }

    return (int)$count;

}


/**
 * Returns the number of payments a user has made
 *
 * @param int $user_id
 *
 * @return int
 *
 */
function pms_get_member_payments_count( $user_id = 0 ) {

    if( $user_id === 0 )
        return 0;

    global $wpdb;

    $user_id = (int)$user_id;

    $query_string = "SELECT COUNT( DISTINCT id ) FROM {$wpdb->prefix}pms_payments WHERE 1=%d AND user_id LIKE {$user_id}";

    $count = $wpdb->get_var( $wpdb->prepare( $query_string, 1 ) );

    return (int)$count;

}


/**
 * Function that returns all possible payment statuses
 *
 * @return array
 *
 */
function pms_get_payment_statuses() {

    $payment_statuses = array(
        'pending'   => __( 'Pending', 'paid-member-subscriptions' ),
        'completed' => __( 'Completed', 'paid-member-subscriptions' ),
        'failed'    => __( 'Failed', 'paid-member-subscriptions' ),
        'refunded'  => __( 'Refunded', 'paid-member-subscriptions' )
    );

    /**
     * Filter to add/remove payment statuses
     *
     * @param array $payment_statuses
     *
     */
    $payment_statuses = apply_filters( 'pms_payment_statuses', $payment_statuses );

    return $payment_statuses;

}


/**
 * Returns an array with the payment types supported
 *
 * @return array
 *
 */
function pms_get_payment_types() {

    $payment_types = array(
        'manual_payment'                 => __( 'Manual Payment', 'paid-member-subscriptions' ),
        'web_accept_paypal_standard'     => __( 'PayPal Standard - One-Time Payment', 'paid-member-subscriptions' ),
        'subscription_initial_payment'   => __( 'Subscription Initial Payment', 'paid-member-subscriptions' ),
        'subscription_recurring_payment' => __( 'Subscription Recurring Payment', 'paid-member-subscriptions' ),
        'subscription_renewal_payment'   => __( 'Subscription Renewal Payment', 'paid-member-subscriptions' ),
        'subscription_upgrade_payment'   => __( 'Subscription Upgrade Payment', 'paid-member-subscriptions' )
    );

    /**
     * Filter to add/remove payment types
     *
     * @param array $payment_types
     *
     */
    $payment_types = apply_filters( 'pms_payment_types', $payment_types );

    return $payment_types;

}


/**
 * Returns true if the test mode is checked in the payments settings page
 * and false if it is not checked
 *
 * @return bool
 *
 */
function pms_is_payment_test_mode() {

    $pms_settings = get_option( 'pms_payments_settings' );

    if( isset( $pms_settings['test_mode'] ) && $pms_settings['test_mode'] == 1 )
        return true;
    else
        return false;

}


/*
 * Returns the name of the payment type given its slug
 *
 * @param string $payment_type_slug
 *
 * @return string
 *
 */
function pms_get_payment_type_name( $payment_type_slug ) {

    $payment_types = pms_get_payment_types();

    if( isset( $payment_types[$payment_type_slug] ) )
        return $payment_types[$payment_type_slug];
    else
        return '';

}


/**
 * Processes payments for custom member subscriptions
 * Is a callback to the cron job with the same name
 *
 */
function pms_cron_process_member_subscriptions_payments() {

    $args = array(
        'status'                      => 'active',
        'billing_next_payment_after'  => date( 'Y-m-d H:i:s', time() - 1 * MONTH_IN_SECONDS ),
        'billing_next_payment_before' => date( 'Y-m-d H:i:s' )
    );

    $subscriptions = pms_get_member_subscriptions( $args );

    foreach( $subscriptions as $subscription ) {

        if( empty( $subscription->payment_gateway ) )
            continue;

        if( empty( $subscription->user_id ) )
            continue;
        else if( get_userdata( $subscription->user_id ) === false )
            continue;

        $payment_gateway = pms_get_payment_gateway( $subscription->payment_gateway );

        if( ! method_exists( $payment_gateway, 'process_payment' ) )
            continue;

        // Payment data
        $payment_data = apply_filters( 'pms_cron_process_member_subscriptions_payment_data' ,
            array(
                'user_id'              => $subscription->user_id,
                'subscription_plan_id' => $subscription->subscription_plan_id,
                'date'                 => date( 'Y-m-d H:i:s' ),
                'amount'               => $subscription->billing_amount,
                'payment_gateway'      => $subscription->payment_gateway,
                'currency'             => pms_get_active_currency(),
                'status'               => 'pending',
                'type'                 => 'subscription_recurring_payment'
            ),
            $subscription
        );

        $payment = new PMS_Payment();
        $payment->insert( $payment_data );

        $payment->log_data( 'new_payment', array( 'user' => 0 ) );

        // Process payment
        $response = $payment_gateway->process_payment( $payment->id, $subscription->id );

        if( $response ) {

            $subscription_data = array(
                'status'               => 'active',
                'billing_last_payment' => date( 'Y-m-d H:i:s' )
            );

            // Set the next billing date
            if( ! empty( $subscription->billing_duration ) ) {

                $next_payment = date( 'Y-m-d H:i:s', strtotime( "+" . $subscription->billing_duration . " " . $subscription->billing_duration_unit, strtotime( $subscription->billing_next_payment ) ) );

                $subscription_data['billing_next_payment'] = $next_payment;

            } else {

                //here I think we should treat the non auto recurring with free trial cases
                $subscription_data['billing_next_payment'] = null;

                // For Unlimited plans we need to set the expiration date; we get here after a free trial has ended
                $plan = pms_get_subscription_plan( $subscription->subscription_plan_id );

                if( isset( $plan->id, $plan->duration ) && $plan->duration == 0 )
                    $subscription_data['expiration_date'] = '';

            }

            pms_add_member_subscription_log( $subscription->id, 'subscription_renewed_automatically' );

        } else {

            $subscription_data = array();

            if( !isset( $payment_gateway->payment_gateway ) || $payment_gateway->payment_gateway != 'manual' ) {
                $subscription_data = array(
                    'status'                => 'expired',
                    'billing_duration'      => '',
                    'billing_duration_unit' => '',
                    'billing_next_payment'  => NULL,
                );

                pms_add_member_subscription_log( $subscription->id, 'subscription_renewal_failed' );
            }

        }

        if( !empty( $subscription_data ) ) {
            $subscription_data = apply_filters( 'pms_cron_process_member_subscriptions_subscription_data', $subscription_data, $response, $payment );

            $subscription->update( $subscription_data );
        }

        do_action( 'pms_cron_after_processing_member_subscription', $subscription, $payment );

    }

}
add_action( 'pms_cron_process_member_subscriptions_payments', 'pms_cron_process_member_subscriptions_payments' );


/**
 * Sets pending payments that are waiting for an IPN to failed if too much time has passed
 */
function pms_cron_process_pending_payments() {

    global $wpdb;

    $gateways = array( 'paypal_standard', 'paypal_express', 'stripe_intents' );

    $payments = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}pms_payments WHERE `status` = 'pending' AND `date` > '0000-00-00 00:00:00' AND `date` < DATE_SUB( NOW(), INTERVAL 2 DAY ) AND `payment_gateway` IN ('". implode( '\',\'', $gateways ) ."') ORDER BY id DESC", ARRAY_A );

    if ( empty( $payments ) )
        return;

    foreach( $payments as $payment ) {

        $update_result = $wpdb->update( $wpdb->prefix . 'pms_payments', array( 'status' => 'failed' ), array( 'user_id' => $payment['user_id'], 'id' => $payment['id'] ) );

        if( $update_result !== false )
            $update_result = true;

        if ( $update_result ) {

            $payment_obj = pms_get_payment( $payment['id'] );

            if( $payment_obj->payment_gateway == 'stripe_intents' )
                $payment_obj->log_data( 'stripe_authentication_link_not_clicked' );
            else
                $payment_obj->log_data( 'paypal_ipn_not_received' );

            /**
             * Fires right after the Payment db entry was updated
             *
             * @param int   $id            - the id of the payment that was updated
             * @param array $data          - the provided data to be changed for the payment
             * @param array $old_data      - the array of values representing the payment before the update
             *
             */
            do_action( 'pms_payment_update', $payment['id'], array( 'status' => 'failed' ), $payment );

            /**
             * Fires right after the Payment db entry was updated
             *
             * @deprecated 1.6.2           - Scheduled for official removal in version 2.0.0
             *
             *
             * @param array $data          - the provided data to be changed for the payment
             * @param array $old_data      - the array of values representing the payment before the update
             * @param int   $payment_id    - the id of the payment that was updated
             *
             */
            do_action( 'pms_payment_updated', array( 'status' => 'failed' ), $payment, $payment['id'] );

        }

    }
}
add_action( 'pms_cron_process_pending_payments', 'pms_cron_process_pending_payments' );
