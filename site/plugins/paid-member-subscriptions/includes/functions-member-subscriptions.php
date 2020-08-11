<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns an array with member subscriptions based on the given arguments
 *
 * @param array $args
 *
 * @return array
 *
 */
function pms_get_member_subscriptions( $args = array() ) {

	global $wpdb;

	$defaults = array(
        'order'                       => 'DESC',
        'orderby'                     => 'id',
        'number'                      => 1000,
        'offset'                      => '',
        'status'                      => '',
        'user_id'                     => '',
        'subscription_plan_id'        => '',
        'start_date'                  => '',
        'start_date_after'            => '',
        'start_date_before'           => '',
        'expiration_date'             => '',
        'expiration_date_after'       => '',
        'expiration_date_before'      => '',
        'billing_next_payment'        => '',
        'billing_next_payment_after'  => '',
        'billing_next_payment_before' => '',
		'include_abandoned'           => false,
    );

    /**
     * Filter the query args
     *
     * @param array $query_args - the args for which the query will be made
     * @param array $args       - the args passed as parameter
     * @param array $defaults   - the default args for the query
     *
     */
	$args = apply_filters( 'pms_get_member_subscriptions_args', wp_parse_args( $args, $defaults ), $args, $defaults );


	// Start query string
    $query_string = "SELECT * ";

    $query_from   = "FROM {$wpdb->prefix}pms_member_subscriptions ";
    $query_where  = "WHERE 1=%d ";


    // Filter by user id
    if( !empty( $args['user_id'] ) ) {

        $user_id      = absint( $args['user_id'] );
        $query_where .= " AND user_id LIKE '{$user_id}'";

    }

    // Filter by status
    if( !empty( $args['status'] ) ) {

        $status       = sanitize_text_field( $args['status'] );
        $query_where .= " AND status LIKE '{$status}'";

    }

	// Exclude Abandoned subscriptions unless requested
	if( isset( $args['include_abandoned'] ) && $args['include_abandoned'] === false )
		$query_where .= " AND status NOT LIKE 'abandoned'";

    // Filter by start date
    if( ! empty( $args['start_date'] ) ) {

        $query_where .= " AND start_date LIKE '%%{$args['start_date']}%%'";

    }

    // Filter by start date after
    if( ! empty( $args['start_date_after'] ) ) {

        $query_where .= " AND start_date > '{$args['start_date_after']}'";

    }

    // Filter by start date before
    if( ! empty( $args['start_date_before'] ) ) {

        $query_where .= " AND start_date < '{$args['start_date_before']}'";

    }

    // Filter by expiration date
    if( ! empty( $args['expiration_date'] ) ) {

        $query_where .= " AND expiration_date LIKE '%%{$args['expiration_date']}%%'";

    }

    // Filter by expiration date after
    if( ! empty( $args['expiration_date_after'] ) ) {

        $query_where .= " AND expiration_date > '{$args['expiration_date_after']}'";

    }

    // Filter by expiration date before
    if( ! empty( $args['expiration_date_before'] ) ) {

        $query_where .= " AND expiration_date < '{$args['expiration_date_before']}'";

    }

    // Filter by billing next payment date
    if( ! empty( $args['billing_next_payment'] ) ) {

        $query_where .= " AND billing_next_payment LIKE '%%{$args['billing_next_payment']}%%'";

    }

    // Filter by billing next date payment after
    if( ! empty( $args['billing_next_payment_after'] ) ) {

        $query_where .= " AND billing_next_payment > '{$args['billing_next_payment_after']}'";

    }

    // Filter by billing next payment date before
    if( ! empty( $args['billing_next_payment_before'] ) ) {

        $query_where .= " AND billing_next_payment < '{$args['billing_next_payment_before']}'";

    }

    // Filter by subscription plan id
    if( ! empty( $args['subscription_plan_id'] ) ) {

        $query_where .= " AND subscription_plan_id = '{$args['subscription_plan_id']}'";

    }

    // Query order by
    $query_order_by = '';

    if ( ! empty($args['orderby']) ) {

		// On the edit_member page, make sure abandoned subs are last
		if( isset( $_GET['page'], $_GET['subpage'] ) && $_GET['page'] == 'pms-members-page' && $_GET['subpage'] == 'edit_member' )
			$query_order_by = " ORDER BY status = 'abandoned', status ";
		else
			$query_order_by = " ORDER BY " . trim( $args['orderby'] ) . ' ';

    }

    // Query order
    $query_order = $args['order'] . ' ';

    // Query limit
    $query_limit = '';

    if( ! empty( $args['number'] ) ) {

        $query_limit = 'LIMIT ' . (int)trim( $args['number'] ) . ' ';

    }

    // Query offset
    $query_offset = '';

    if( ! empty( $args['offset'] ) ) {

        $query_offset = 'OFFSET ' . (int)trim( $args['offset'] ) . ' ';

    }


    $query_string .= $query_from . $query_where . $query_order_by . $query_order . $query_limit . $query_offset;

	$data_array = $wpdb->get_results( $wpdb->prepare( $query_string, 1 ), ARRAY_A );

	$subscriptions = array();

	foreach( $data_array as $key => $data ) {

		$subscriptions[$key] = new PMS_Member_Subscription( $data );

 	}

 	/**
     * Filter member subscriptions just before returning them
     *
     * @param array $subscriptions - the array of returned member subscriptions from the db
     * @param array $args     	   - the arguments used to query the member subscriptions from the db
     *
     */
    $subscriptions = apply_filters( 'pms_get_member_subscriptions', $subscriptions, $args );

	return $subscriptions;

}


/**
 * Returns a member subscription object from the database by the given id
 * or null if no subscription is found
 *
 * @param int $member_subscription_id
 *
 * @return mixed
 *
 */
function pms_get_member_subscription( $member_subscription_id = 0 ) {

    global $wpdb;

    $result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}pms_member_subscriptions WHERE id = {$member_subscription_id}", ARRAY_A );

    if( ! is_null( $result ) )
        $result = new PMS_Member_Subscription( $result );

    return $result;

}


/**
 * Function that returns all available member subscription statuses
 *
 * @return array
 *
 */
function pms_get_member_subscription_statuses() {

    $statuses = array(
        'active'    => __( 'Active', 'paid-member-subscriptions' ),
        'canceled'  => __( 'Canceled', 'paid-member-subscriptions' ),
        'expired'   => __( 'Expired', 'paid-member-subscriptions' ),
        'pending'   => __( 'Pending', 'paid-member-subscriptions' ),
		'abandoned' => __( 'Abandoned', 'paid-member-subscriptions' ),
    );

    /**
     * Filter to add/remove member subscription statuses
     *
     * @param array $statuses
     *
     */
    $statuses = apply_filters( 'pms_member_subscription_statuses', $statuses );

    return $statuses;

}


/**
 * Returns the metadata for a given member subscription
 *
 * @param int    $member_subscription_id
 * @param string $meta_key
 * @param bool   $single
 *
 * @return mixed - single metadata value | array of values
 *
 */
function pms_get_member_subscription_meta( $member_subscription_id = 0, $meta_key = '', $single = false ) {

    return get_metadata( 'member_subscription', $member_subscription_id, $meta_key, $single );

}


/**
 * Adds the metadata for a member subscription
 *
 * @param int    $member_subscription_id
 * @param string $meta_key
 * @param string $meta_value
 * @param bool   $unique
 *
 * @return mixed - int | false
 *
 */
function pms_add_member_subscription_meta( $member_subscription_id = 0, $meta_key = '', $meta_value = '', $unique = false ) {

    return add_metadata( 'member_subscription', $member_subscription_id, $meta_key, $meta_value, $unique );

}


/**
 * Updates the metadata for a member subscription
 *
 * @param int    $member_subscription_id
 * @param string $meta_key
 * @param string $meta_value
 * @param string $prev_value
 *
 * @return bool
 *
 */
function pms_update_member_subscription_meta( $member_subscription_id = 0, $meta_key = '', $meta_value = '', $prev_value = '' ) {

    return update_metadata( 'member_subscription', $member_subscription_id, $meta_key, $meta_value, $prev_value );

}


/**
 * Deletes the metadata for a member subscription
 *
 * @param int    $member_subscription_id
 * @param string $meta_key
 * @param string $meta_value
 * @param string $delete_all - If true, delete matching metadata entries for all member subscriptions, ignoring
 *                             the specified member_subscription_id. Otherwise, only delete matching metadata
 *                             entries for the specified member_subscription_id.
 *
 */
function pms_delete_member_subscription_meta( $member_subscription_id = 0, $meta_key = '', $meta_value = '', $delete_all = false ) {

    return delete_metadata( 'member_subscription', $member_subscription_id, $meta_key, $meta_value, $delete_all );

}

/**
 * Adds log data to a given subscription
 *
 * @param int    $member_subscription_id
 * @param string $type
 * @param array  $data
 */
function pms_add_member_subscription_log( $member_subscription_id, $type, $data = array() ){

	if( empty( $type ) )
		return false;

	$subscription_logs = pms_get_member_subscription_meta( $member_subscription_id, 'logs', true );

	if( empty( $subscription_logs ) )
		$subscription_logs = array();

	$subscription_logs[] = array(
		'date'       => date( 'Y-m-d H:i:s' ),
		'type'       => $type,
		'data'       => !empty( $data ) ? $data : ''
	);

	$update_result = pms_update_member_subscription_meta( $member_subscription_id, 'logs', $subscription_logs );

	if( $update_result !== false )
		$update_result = true;

	// Save the abandon date as a subscription meta
	if( $type == 'subscription_abandoned' )
		pms_add_member_subscription_meta( $member_subscription_id, 'abandon_date', date( 'Y-m-d H:i:s' ) );

	return $update_result;

}

/**
 * Cancels all member subscriptions for a user when the user is deleted
 *
 * @param int $user_id
 *
 */
function pms_member_delete_user_subscription_cancel( $user_id = 0 ) {

    if( empty( $user_id ) )
        return;

    $member_subscriptions = pms_get_member_subscriptions( array( 'user_id' => (int)$user_id ) );

    if( empty( $member_subscriptions ) )
        return;

    foreach( $member_subscriptions as $member_subscription ) {

        if( $member_subscription->status == 'active' ) {

            $member_subscription->update( array( 'status' => 'canceled' ) );

			pms_add_member_subscription_log( $member_subscription->id, 'subscription_canceled_user_deletion', array( 'who' => get_current_user_id() ) );

        }

    }

}
add_action( 'delete_user', 'pms_member_delete_user_subscription_cancel' );


/**
 * Function triggered by the cron job that checks for any expired subscriptions.
 *
 * Note 1: This function has been refactored due to slow performance. It would take all members and then
 *         for each one of the subscription it would check to see if it was expired and if so, set the status
 *         to expired.
 * Note 2: The function now gets all active subscriptions without using the PMS_Member class and checks to see
 *         if they have passed their expiration time and if so, sets the status to expire. Due to the fact that
 *         the PMS_Member class is not used, the "pms_member_update_subscription" had to be added here also to
 *         deal with further actions set on the hook
 *
 * @return void
 *
 */
function pms_member_check_expired_subscriptions() {

    global $wpdb;

    $subscriptions = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}pms_member_subscriptions WHERE ( status = 'active' OR status = 'canceled' ) AND expiration_date > '0000-00-00 00:00:00' AND expiration_date < DATE_SUB( NOW(), INTERVAL 12 HOUR )", ARRAY_A );

    if( empty( $subscriptions ) )
        return;

    foreach( $subscriptions as $subscription ) {

        $update_result = $wpdb->update( $wpdb->prefix . 'pms_member_subscriptions', array( 'status' => 'expired' ), array( 'user_id' => $subscription['user_id'], 'subscription_plan_id' => $subscription['subscription_plan_id'] ) );

		pms_add_member_subscription_log( $subscription['id'], 'subscription_expired' );

        // Can return 0 if no data was changed
        if( $update_result !== false )
            $update_result = true;

        if( $update_result ) {

            /**
             * Fires right after the Member Subscription db entry was updated
             *
             * This action is the same as the one in the "update" method in PMS_Member_Subscription class
             *
             * @param int   $id            - the id of the subscription that has been updated
             * @param array $data          - the array of values to be updated for the subscription
             * @param array $old_data      - the array of values representing the subscription before the update
             *
             */
            do_action( 'pms_member_subscription_update', $subscription['id'], array( 'status' => 'expired' ), $subscription );


            /**
             * Action to do something after a subscription update.
             *
             * This action is the same as the one in the "update" method in PMS_Member_Subscription class
             *
             * @deprecated 1.6.1           - Scheduled for official removal in version 2.0.0
             *
             * @param int   $id            - the id of the subscription that has been updated
             * @param array $data          - the array of values to be updated for the subscription
             * @param array $old_data      - the array of values representing the subscription before the update
             *
             */
            do_action( 'pms_member_subscription_updated', $subscription['id'], array( 'status' => 'expired' ), $subscription );

        }

        /**
         * Deprecated action to do something after a subscription update.
         *
         * @deprecated
         *
         * This action is the same as the one in the "update_subscription" method in PMS_Member class
         *
         */
        do_action( 'pms_member_update_subscription', $update_result, $subscription['user_id'], $subscription['subscription_plan_id'], $subscription['start_date'], $subscription['expiration_date'], 'expired' );

    }

}
