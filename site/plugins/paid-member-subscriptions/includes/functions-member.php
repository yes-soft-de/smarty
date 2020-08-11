<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Wrapper function to return a member object
 *
 * @param $user_id  - The id of the user we wish to return
 *
 * @return PMS_Member
 *
 */
function pms_get_member( $user_id ) {

    return new PMS_Member( $user_id );

}

/**
 * Check if the currently logged in user or a specific user is an active member (or an active member of a certain subscription)
 *
 * @param  int     $user_id            The id of the user we want to check. Defaults to the currently logged in user
 * @param  array   $subscription_plans Array of subscription plans to check against. If more then one is specified, the function will return true if the user is an active member in any one of them (OR)
 * @return boolean
 */
function pms_is_member( $user_id = '', $subscription_plans = array() ) {
    if ( $user_id == '' )
        $user_id = get_current_user_id();

    if ( $user_id == 0 ) return false;

    $member_subscriptions = pms_get_member_subscriptions( array( 'user_id' => $user_id ) );

    foreach( $member_subscriptions as $member_subscription ) {

        if ( !empty($subscription_plans) ) {
            if ( !in_array( $member_subscription->subscription_plan_id, $subscription_plans ) )
                continue;
        }

        $time_expire = ( ! empty( $member_subscription->expiration_date ) && time() > strtotime( $member_subscription->expiration_date ) ? true : false );

        if( ( $member_subscription->status == 'active' || $member_subscription->status == 'canceled' ) && ! $time_expire )
            return true;

    }

    return false;
}

/**
 * Wrapper for pms_is_member().
 * Usage: pms_is_member_of_plan( 123 ) or pms_is_member_of_plan( array( 123, 124 ) )
 * The user id can also be specified as the second parameter.
 *
 * @param  int     $subscription_plans A single subscription plan id or an array of them.
 * @return boolean
 */
function pms_is_member_of_plan( $subscription_plans, $user_id = '' ) {
    if ( !is_array( $subscription_plans ) && is_numeric( $subscription_plans ) )
        return pms_is_member( $user_id, array( $subscription_plans ) );

    if ( is_array( $subscription_plans ) )
        return pms_is_member( $user_id, $subscription_plans );

    return false;
}

/**
 * Queries the database for user ids that also match the member_subscriptions table
 * and returns an array with member objects
 *
 * @param array $args   - arguments to modify the query and return different results
 * @param bool  $count
 *
 * @param mixed array | int - array with member objects or the count of the members if $count is true
 *
 */
function pms_get_members( $args = array(), $count = false ) {

    global $wpdb;

    $defaults = array(
        'order'                      => 'DESC',
        'orderby'                    => 'ID',
        'offset'                     => '',
        'subscription_plan_id'       => '',
        'member_subscription_status' => '',
        'search'                     => '',
        'group_owner'                => '',
    );

    $args = apply_filters( 'pms_get_members_args', wp_parse_args( $args, $defaults ), $args, $defaults );

    // If Limit is empty, add a default one
    if ( empty( $args['number'] ) )
        $args['number'] = 10;

    // Start query string
    if( ! $count )
        $query_string   = "SELECT DISTINCT users.ID ";
    else
        $query_string   = "SELECT COUNT( DISTINCT users.ID ) ";

    // Query string sections
    $query_from         = "FROM {$wpdb->users} users ";
    $query_inner_join   = "INNER JOIN {$wpdb->prefix}pms_member_subscriptions member_subscriptions ON users.ID = member_subscriptions.user_id ";
    $query_inner_join  .= "INNER JOIN {$wpdb->usermeta} usermeta ON users.ID = usermeta.user_id ";

    if( !empty( $args['group_owner'] ) )
        $query_inner_join .= "INNER JOIN {$wpdb->prefix}pms_member_subscriptionmeta member_subscriptions_meta ON member_subscriptions.id = member_subscriptions_meta.member_subscription_id ";

    $query_where        = "WHERE 1=%d ";

    if( ! empty( $args['member_subscription_status'] ) )
        $query_where    = $query_where . " AND member_subscriptions.status = '" . sanitize_text_field( $args['member_subscription_status'] ) . "' ";

    if( ! empty( $args['subscription_plan_id'] ) )
        $query_where    = $query_where . " AND member_subscriptions.subscription_plan_id = " . (int)$args['subscription_plan_id'] . " ";

    if( ! empty( $args['group_owner' ] ) ){
        $query_where    = $query_where . " AND ( member_subscriptions_meta.meta_key = 'pms_group_subscription_owner' AND member_subscriptions_meta.meta_value = '" . sanitize_text_field( $args['group_owner'] ) . "'
        OR member_subscriptions_meta.meta_key = 'pms_group_name' AND member_subscriptions_meta.meta_value = '".sanitize_text_field( pms_get_member_subscription_meta( $args['group_owner'], 'pms_group_name', true ) )."') ";
    }

    // Add search query
    if( ! empty( $args['search'] ) ) {
        $search_term    = sanitize_text_field( $args['search'] );
        $query_where    = $query_where . " AND  " . "  (users.user_email LIKE '%%%s%%' OR users.user_nicename LIKE '%%%s%%' OR usermeta.meta_value LIKE '%%%s%%')  ". " ";
    }

    $query_oder_by      = "ORDER BY users." . sanitize_text_field( $args['orderby'] ) . ' ';

    $query_order        = strtoupper( sanitize_text_field( $args['order'] ) ) . ' ';

    $query_limit        = '';
    if( $args['number'] )
        $query_limit    = 'LIMIT ' . (int)trim( $args['number'] ) . ' ';

    $query_offset       = '';
    if( $args['offset'] )
        $query_offset   = 'OFFSET ' . (int)trim( $args['offset'] ) . ' ';

    // Concatenate query string
    if( ! $count )
        $query_string .= $query_from . $query_inner_join . $query_where . $query_oder_by . $query_order . $query_limit . $query_offset;
    else
        $query_string .= $query_from . $query_inner_join . $query_where . $query_oder_by . $query_order;

    // Return results
    if( ! $count ) {

        if ( ! empty( $search_term ) )
            $results = $wpdb->get_results( $wpdb->prepare( $query_string, 1, $wpdb->esc_like( $search_term ), $wpdb->esc_like( $search_term ), $wpdb->esc_like( $search_term ) ), ARRAY_A );
        else
            $results = $wpdb->get_results( $wpdb->prepare( $query_string, 1 ), ARRAY_A );

    } else {

        if ( ! empty( $search_term ) )
            $results = (int)$wpdb->get_var( $wpdb->prepare( $query_string, 1, $wpdb->esc_like( $search_term ), $wpdb->esc_like( $search_term ), $wpdb->esc_like( $search_term ) ) );
        else
            $results = (int)$wpdb->get_var( $wpdb->prepare( $query_string, 1 ) );

    }

    // Get members for each ID passed
    if( ! $count ) {

        $members = array();

        if ( ! empty( $results ) ) {
            foreach ($results as $user_data) {
                $member = new PMS_Member( $user_data['ID'] );

                $members[] = $member;
            }
        }

    // Members are represented by their count
    } else {

        $members = $results;

    }

    return apply_filters( 'pms_get_members', $members, $args, $count );

}


/**
 * Function that saves the user last login time in usermeta table;
 * We use this to send email reminders after a certain time has passed since last login
 *
 * @param string $login
 *
 */
function pms_save_user_last_login( $login = '' ) {

    if( empty( $login ) )
        return;

    $user = get_user_by( 'login', $login );
    $now  = date('Y-m-d H:i:s');

    update_user_meta( $user->ID, 'last_login', $now );

}
add_action( 'wp_login', 'pms_save_user_last_login', 9 );


/*
 * Adds the value of the payment_profile_id received from the payment gateway in the database to a
 * users subscription information
 *
 */
if( !function_exists('pms_member_add_payment_profile_id') ) {

    function pms_member_add_payment_profile_id( $user_id = 0, $subscription_plan_id = 0, $payment_profile_id = '' ) {

        if( empty($user_id) || empty($subscription_plan_id) || empty($payment_profile_id) )
            return false;

        global $wpdb;

        $result = $wpdb->update( $wpdb->prefix . 'pms_member_subscriptions', array( 'payment_profile_id' => $payment_profile_id ), array( 'user_id' => $user_id, 'subscription_plan_id' => $subscription_plan_id ) );

        if( $result === false )
            return false;
        else
            return true;
    }

}


/**
 * Returns the value of the payment_profile_id of a member subscription if it exists
 *
 * @param int $user_id
 * @param int $subscription_plan_id
 *
 * @return mixed string | null
 *
 */
if( !function_exists('pms_member_get_payment_profile_id') ) {
    function pms_member_get_payment_profile_id( $user_id = 0, $subscription_plan_id = 0 ) {

        if( empty($user_id) || empty($subscription_plan_id) )
            return NULL;

        global $wpdb;

        $result = $wpdb->get_var( "SELECT payment_profile_id FROM {$wpdb->prefix}pms_member_subscriptions WHERE user_id = {$user_id} AND subscription_plan_id = {$subscription_plan_id}" );

        // In case we do not find it, it could be located in the api failed canceling
        // errors
        if( is_null($result) ) {

            $api_failed_attempts = get_option( 'pms_api_failed_attempts', array() );

            if( isset( $api_failed_attempts[$user_id][$subscription_plan_id]['payment_profile_id'] ) )
                $result = $api_failed_attempts[$user_id][$subscription_plan_id]['payment_profile_id'];

        }

        return $result;

    }
}


/**
 * Function that retrieves the unique user key from the database. If we don't have one we generate one and add it to the database
 *
 * @param string $requested_user_login the user login
 *
 */
function pms_retrieve_activation_key( $requested_user_login ){
    global $wpdb;

    $key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $requested_user_login ) );

    if ( empty( $key ) ) {

        // Generate something random for a key...
        $key = wp_generate_password( 20, false );
        do_action('pms_retrieve_password_key', $requested_user_login, $key);

        // Now insert the new md5 key into the db
        $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $requested_user_login));
    }

    return $key;
}


/**
 * Function triggered by the cron job that removes the user activation key (used for password reset) from the db, (make it expire) every 20 hours (72000 seconds).
 *
 */
function pms_remove_expired_activation_key(){
    $activation_keys = get_option( 'pms_recover_password_activation_keys', array());

    if ( !empty( $activation_keys ) ) { //option exists

        foreach ($activation_keys as $id => $activation_key) {

            if ( ( $activation_key['time'] + 72000 ) < time() ) {
                update_user_meta($id, 'user_activation_key', '' ); // remove expired activation key from db
                unset($activation_keys[$id]);
                update_option('pms_recover_password_activation_keys', $activation_keys); // delete activation key from option
            }

        }

    }
}
