<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Modifies the request form location from the form handler class
 *
 * @param string $location
 * @param array  $request_data
 *
 * @return string
 *
 */
function pms_pb_change_request_form_location( $location, $request_data ) {

    if( ! isset( $request_data['form_name'] ) )
        return $location;

    if( isset( $request_data['action'] ) && $request_data['action'] == 'register' )
        return 'register';
    else
        return $location;

}
add_filter( 'pms_request_form_location', 'pms_pb_change_request_form_location', 10, 2 );


/**
 * Hooks to the Profile Builder subscription plans field to add the extra form fields
 *
 * @param string $output
 *
 * @return string
 *
 */
function pms_pb_add_form_extra_fields( $output = '' ) {

    ob_start();

    // Call the extra form fields adder
    pms_add_form_extra_fields();

    $extra_fields_output = ob_get_contents();
    ob_end_clean();

    return $output . $extra_fields_output;

}
add_filter( 'wppb_register_subscription_plans_field', 'pms_pb_add_form_extra_fields', 50 );


/**
 * Modify the form name for the extra form fields when coming from a PB form
 *
 * @param string $form_name
 * @param string $hook
 *
 * @return string
 *
 */
function pms_pb_form_extra_fields_form_name( $form_name = '', $hook = '' ) {

    if( $hook == 'wppb_register_subscription_plans_field' )
        $form_name = 'register';

    return $form_name;

}
add_filter( 'pms_form_extra_fields_form_name', 'pms_pb_form_extra_fields_form_name', 10, 2 );


/* remove the Subscription Plans auto generated meta tag in userlisting */
add_filter('wppb_userlisting_merge_tags' , 'pms_remove_subscription_plans_from_auto_generated_merge_tags' );
add_filter('wppb_email_customizer_get_fields' , 'pms_remove_subscription_plans_from_auto_generated_merge_tags' );
function pms_remove_subscription_plans_from_auto_generated_merge_tags( $all_fields ){
    if( !empty( $all_fields ) && is_array( $all_fields ) ){
        foreach ($all_fields as $key => $field ) {
            if( $field['field'] == 'Subscription Plans' ){
                $unset_key = $key;
                break;
            }
        }

        if( !empty( $unset_key ) )
            unset( $all_fields[$unset_key] );
    }

    return $all_fields;
}

/* add the tags we need  */
add_filter( 'wppb_userlisting_get_merge_tags', 'pms_add_tags_in_userlisting_and_ec', 10, 2 );
add_filter( 'wppb_email_customizer_get_merge_tags', 'pms_add_tags_in_userlisting_and_ec', 10, 2 );
function pms_add_tags_in_userlisting_and_ec( $merge_tags, $type = '' ){

    if( $type == 'sort' )
        return $merge_tags;

    /* unescaped because they might contain html */
    $merge_tags[] = array( 'name' => 'subscription_name', 'type' => 'subscription_name', 'unescaped' => true, 'label' => __( 'Subscription Name', 'paid-member-subscriptions' ) );
    $merge_tags[] = array( 'name' => 'subscription_status', 'type' => 'subscription_status', 'unescaped' => true, 'label' => __( 'Subscription Status', 'paid-member-subscriptions' ) );
    $merge_tags[] = array( 'name' => 'subscription_start_date', 'type' => 'subscription_start_date', 'unescaped' => true, 'label' => __( 'Subscription Start Date', 'paid-member-subscriptions' ) );
    $merge_tags[] = array( 'name' => 'subscription_expiration_date', 'type' => 'subscription_expiration_date', 'unescaped' => true, 'label' => __( 'Subscription Expiration Date', 'paid-member-subscriptions' ) );

    return $merge_tags;
}

/* add functionality for Subscription Name tag */
add_filter( 'mustache_variable_subscription_name', 'pms_handle_merge_tag_subscription_name', 10, 4 );
function pms_handle_merge_tag_subscription_name( $value, $name, $children, $extra_info ){
    $user_id = ( ! empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : get_query_var( 'username' ) );
    if( !empty( $user_id ) ){
        $member = pms_get_member( $user_id );
        if( !empty( $member->subscriptions ) ){
            if( count( $member->subscriptions ) == 1 ){
                return get_the_title( $member->subscriptions[0]['subscription_plan_id'] );
            }
            else{
                $subscription_names = '';
                foreach( $member->subscriptions as $subscription_plan ){
                    $subscription_names .= '<div>'. get_the_title( $subscription_plan['subscription_plan_id'] ) .'</div>';
                }
                return $subscription_names;
            }
        }
    }
}

/* add functionality for Subscription Status tag */
add_filter( 'mustache_variable_subscription_status', 'pms_handle_merge_tag_subscription_status', 10, 4 );
function pms_handle_merge_tag_subscription_status( $value, $name, $children, $extra_info ){
    $user_id = ( ! empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : get_query_var( 'username' ) );
    if( !empty( $user_id ) ){
        $member = pms_get_member( $user_id );
        if( !empty( $member->subscriptions ) ){
            if( count( $member->subscriptions ) == 1 ){
                return $member->subscriptions[0]['status'];
            }
            else{
                $subscription_status = '';
                foreach( $member->subscriptions as $subscription_plan ){
                    $subscription_status .= '<div>'. $subscription_plan['status'] .'</div>';
                }
                return $subscription_status;
            }
        }
    }
}


/* add functionality for Subscription Start Date tag */
add_filter( 'mustache_variable_subscription_start_date', 'pms_handle_merge_tag_subscription_start_date', 10, 4 );
function pms_handle_merge_tag_subscription_start_date( $value, $name, $children, $extra_info ){
    $user_id = ( ! empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : get_query_var( 'username' ) );
    if( !empty( $user_id ) ){
        $member = pms_get_member( $user_id );
        if( !empty( $member->subscriptions ) ){
            if( count( $member->subscriptions ) == 1 ){
                return apply_filters( 'pms_change_userlisting_expiration_date_format', $member->subscriptions[0]['start_date'] );
            }
            else{
                $subscription_start_date = '';
                foreach( $member->subscriptions as $subscription_plan ){
                    $subscription_start_date .= '<div>'. apply_filters( 'pms_change_userlisting_expiration_date_format', $subscription_plan['start_date'] ) .'</div>';
                }
                return $subscription_start_date;
            }
        }
    }
}


/* add functionality for Subscription Expiration Date tag */
add_filter( 'mustache_variable_subscription_expiration_date', 'pms_handle_merge_tag_subscription_expiration_date', 10, 4 );
function pms_handle_merge_tag_subscription_expiration_date( $value, $name, $children, $extra_info ){
    $user_id = ( ! empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : get_query_var( 'username' ) );
    if( !empty( $user_id ) ){
        $member = pms_get_member( $user_id );
        if( !empty( $member->subscriptions ) ){
            if( count( $member->subscriptions ) == 1 ){
                return apply_filters( 'pms_change_userlisting_expiration_date_format', $member->subscriptions[0]['expiration_date'] );
            }
            else{
                $subscription_expiration_date = '';
                foreach( $member->subscriptions as $subscription_plan ){
                    $subscription_expiration_date .= '<div>'. apply_filters( 'pms_change_userlisting_expiration_date_format', $subscription_plan['expiration_date'] ) .'</div>';
                }
                return $subscription_expiration_date;
            }
        }
    }
}
