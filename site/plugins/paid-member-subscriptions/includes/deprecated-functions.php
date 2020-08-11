<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Function that returns all possible member statuses
 *
 * Scheduled for official removal in version 2.0.0
 *
 * @deprecated 1.5.0
 * @see 	   pms_get_member_subscription_statuses()
 *
 * @return array
 *
 */
function pms_get_member_statuses() {

	_pms_deprecated_function( __FUNCTION__, '1.5.0', 'pms_get_member_subscription_statuses()' );

	return apply_filters( 'pms_member_statuses', array(
        'active'    => __( 'Active', 'paid-member-subscriptions' ),
        'canceled'  => __( 'Canceled', 'paid-member-subscriptions' ),
        'expired'   => __( 'Expired', 'paid-member-subscriptions' ),
        'pending'   => __( 'Pending', 'paid-member-subscriptions' )
    ));

}


/**
 * Removes a user role and adds a new one when the a member gets a subscription replaced with another one
 *
 * Scheduled for official removal in version 2.0.0
 *
 * Note: This function should be replaced by another function, that does not depend on the PMS_Member object,
 *       but rather on the PMS_Member_Subscription object
 *
 * @deprecated since 1.5.2
 *
 */
function pms_member_replace_user_role( $update_result, $user_id, $new_subscription_plan_id, $old_subscription_plan_id ) {

    if( !$update_result )
        return;

    // Remove the member's user role corresponding to the old subscription plan
    pms_member_remove_user_role( $update_result, $user_id, $old_subscription_plan_id );

    // Add new role based on the new subscription plan the user has
    $member       = pms_get_member( $user_id );
    $subscription = $member->get_subscription( $new_subscription_plan_id );

    if( $subscription['status'] == 'active' )
        pms_add_user_role( $user_id, pms_get_user_roles_by_plan_ids( $new_subscription_plan_id ) );

}
add_action( 'pms_member_replace_subscription', 'pms_member_replace_user_role', 10, 4 );


/**
 * Add user role to a member when the member gets subscribed to a new subscription plan
 *
 * Scheduled for official removal in version 2.0.0
 *
 * @deprecated since 1.5.2
 *
 */
function pms_member_add_user_role( $db_action_result, $user_id, $subscription_plan_id, $start_date, $expiration_date, $status ) {

    if( ! $db_action_result )
        return;

    if( $status != 'active' )
        return;

    pms_add_user_role( $user_id, get_post_meta( $subscription_plan_id, 'pms_subscription_plan_user_role', true ) );

}
add_action( 'pms_member_add_subscription', 'pms_member_add_user_role', 10, 6 );
add_action( 'pms_member_update_subscription', 'pms_member_add_user_role', 10, 6 );


/**
 * Remove user role when a members subscription gets removed
 *
 * Scheduled for official removal in version 2.0.0
 *
 * @deprecated since 1.5.2
 *
 */
function pms_member_remove_user_role( $delete_result, $user_id, $subscription_plan_id ) {

    if( !$delete_result )
        return;

    $member = pms_get_member($user_id);

    $subscription_plan_user_role = pms_get_user_roles_by_plan_ids( $subscription_plan_id );

    if( in_array( $subscription_plan_user_role, pms_get_user_roles_by_plan_ids( $member->get_subscriptions_ids() ) ) )
        return;

    pms_remove_user_role( $user_id, $subscription_plan_user_role );

}
add_action( 'pms_member_remove_subscription', 'pms_member_remove_user_role', 10, 3 );