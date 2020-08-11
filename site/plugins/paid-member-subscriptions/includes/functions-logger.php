<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add a log entry when an admin is manually adding a payment.
 */
add_action( 'pms_payment_insert', 'pms_log_manually_added_payments', 20, 2 );
function pms_log_manually_added_payments( $payment_id, $data ) {
    if ( !is_admin() || ( isset( $data['type'] ) && $data['type'] != 'manual_payment' ) )
        return;

    $payment = pms_get_payment( $payment_id );

    if ( empty( $payment->id ) )
        return;

    $user = get_userdata( get_current_user_id() );

    $payment->log_data( 'payment_added', array( 'user' => $user->user_login ) );
}

/**
 * Add a log entry when the status of a payment changes.
 */
add_action( 'pms_payment_update', 'pms_log_payment_data_changes', 20, 3 );
function pms_log_payment_data_changes( $payment_id, $data, $old_data ) {

    $fields = array( 'status' );

    foreach( $fields as $field ){

        if ( !isset( $data[$field] ) || ( isset( $data[$field] ) && $data[$field] == $old_data[$field] ) )
            continue;

        $payment = pms_get_payment( $payment_id );

        if ( empty( $payment->id ) )
            continue;

        unset( $old_data['logs'] );

        $payment->log_data( $field . '_changed', array( 'user' => get_current_user_id(), 'field' => $field, 'new_data' => $data, 'old_data' => $old_data ) );

    }

}

/**
 * Log subscription changes made by the admin
 */
add_action( 'pms_member_subscription_update', 'pms_log_admin_subscription_changes', 20, 3 );
function pms_log_admin_subscription_changes( $subscription_id, $data, $old_data ){
    if( !isset( $_GET['page'] ) || $_GET['page'] != 'pms-members-page' )
        return;

    $keys = array(
        'subscription_plan_id',
        'start_date',
        'expiration_date',
        'status',
        'trial_end',
        'billing_duration_unit',
        'billing_next_payment',
        'payment_gateway'
    );

    $date_keys = array(
        'start_date',
        'expiration_date',
        'trial_end',
        'billing_next_payment'
    );

    foreach( $keys as $key ){

        if( !isset( $data[$key], $old_data[$key] ) )
            continue;

        if( in_array( $key, $date_keys ) && pms_sanitize_date( $data[$key] ) != pms_sanitize_date( $old_data[$key] ) )
            pms_add_member_subscription_log( $subscription_id, 'admin_subscription_edit', array( 'field' => $key, 'old' => pms_sanitize_date( $old_data[$key] ), 'new' => pms_sanitize_date( $data[$key] ), 'who' => get_current_user_id() ) );
        else if( !in_array( $key, $date_keys ) && $data[$key] != $old_data[$key] )
            pms_add_member_subscription_log( $subscription_id, 'admin_subscription_edit', array( 'field' => $key, 'old' => $old_data[$key], 'new' => $data[$key], 'who' => get_current_user_id() ) );

    }

    if( !empty( $data['billing_duration'] ) && $data['billing_duration'] != $old_data['billing_duration'] )
        pms_add_member_subscription_log( $subscription_id, 'admin_subscription_edit', array( 'field' => 'billing_duration', 'old' => $old_data['billing_duration'], 'new' => $data['billing_duration'], 'who' => get_current_user_id() ) );

}

/**
 * Log payment gateway changes
 */
add_action( 'pms_member_subscription_update', 'pms_log_payment_gateway_changes', 20, 3 );
function pms_log_payment_gateway_changes( $subscription_id, $data, $old_data ){

    if( empty( $subscription_id ) )
        return;

    if( isset( $data['payment_gateway'], $old_data['payment_gateway'] ) && $data['payment_gateway'] != $old_data['payment_gateway'] )
        pms_add_member_subscription_log( $subscription_id, 'changed_payment_gateway', array( 'field' => 'payment_gateway', 'old' => $old_data['payment_gateway'], 'new' => $data['payment_gateway'], 'who' => get_current_user_id() ) );

}
