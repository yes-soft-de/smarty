<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Main function to return a products
 *
 * @param $id_or_post   - post ID or post of the subscription plan
 *
 * @return PMS_Subscription_Plan
 *
 */
function pms_get_subscription_plan( $id_or_post ) {
    return apply_filters( 'pms_get_subscription_plan', new PMS_Subscription_Plan( $id_or_post ), $id_or_post );
}


/**
 * Returns all subscription plans into an array of objects
 *
 * @param $only_active   - true to return only active subscription plans, false to return all
 *
 * @return array
 *
 */
function pms_get_subscription_plans( $only_active = true, $include = array() ) {

    $subscription_plans = array();
    $subscription_plan_post_ids = array();

    if( empty( $include ) ) {

        $subscription_plan_posts = get_posts( array('post_type' => 'pms-subscription', 'numberposts' => -1, 'post_status' => 'any' ) );

        $page_hierarchy_posts = get_page_hierarchy( $subscription_plan_posts );

        foreach( $page_hierarchy_posts as $post_id => $post_name ) {
            $subscription_plan_post_ids[] = $post_id;
        }

    } else {

        $subscription_plan_posts = get_posts( array('post_type' => 'pms-subscription', 'numberposts' => -1, 'include' => $include, 'orderby' => 'post__in', 'post_status' => 'any' ) );
        $subscription_plan_post_ids = $subscription_plan_posts;

    }

    // Return if we don't have any plans by now
    if( empty( $subscription_plan_post_ids ) )
        return $subscription_plans;


    foreach( $subscription_plan_post_ids as $subscription_plan_post_id ) {
        $subscription_plan = pms_get_subscription_plan( $subscription_plan_post_id );

        if( $only_active && !$subscription_plan->is_active() )
            continue;

        $subscription_plans[] = $subscription_plan;
    }

    return apply_filters( 'pms_get_subscription_plans', $subscription_plans, $only_active );

}


function pms_get_subscription_plan_groups_parent_ids() {

    $parent_ids = array();

    $subscription_plan_posts = get_posts( array( 'post_type' => 'pms-subscription', 'numberposts' => -1, 'post_parent' => 0, 'post_status' => 'any' ) );

    if( !empty( $subscription_plan_posts ) ) {
        foreach( $subscription_plan_posts as $subscription_plan_post ) {
            $parent_ids[] = $subscription_plan_post->ID;
        }
    }

    return $parent_ids;

}


function pms_get_subscription_plans_group_parent_id( $subscription_plan_id ) {

    $ancestors_ids = get_post_ancestors( $subscription_plan_id );

    if( !empty( $ancestors_ids ) )
        $top_parent_id = $ancestors_ids[ count( $ancestors_ids ) - 1 ];
    else
        $top_parent_id = $subscription_plan_id;

    return $top_parent_id;

}


function pms_get_subscription_plans_group( $subscription_plan_id, $only_active = true, $ascending = false ) {

    $top_parent_id = pms_get_subscription_plans_group_parent_id( $subscription_plan_id );

    // Add top most parent
    $subscription_plan_posts[] = get_post( $top_parent_id );

    // Add all the children in the group
    while( ( $subscription_plan_downgrade = get_posts( array('post_type' => 'pms-subscription', 'numberposts' => -1, 'post_parent' => $top_parent_id, 'order' => 'DESC', 'orderby' => 'parent', 'post_status' => 'any' ) ) ) != null ) {

        $top_parent_id = $subscription_plan_downgrade[0]->ID;
        $subscription_plan_posts[] = $subscription_plan_downgrade[0];

    }

    $subscription_plans = array();

    if( !empty( $subscription_plan_posts ) ) {
        foreach( $subscription_plan_posts as $subscription_plan_post ) {
            $subscription_plan = pms_get_subscription_plan( $subscription_plan_post );

            if( $only_active && !$subscription_plan->is_active() )
                continue;

            $subscription_plans[] = $subscription_plan;
        }
    }

    if( $ascending == true )
        $subscription_plans = array_reverse( $subscription_plans );

    return $subscription_plans;

}


/**
 * Returns an array of PMS_Subscription_Plan objects that are possible upgrades for the given
 * subscription_plan_id
 *
 * @param int  $subscription_plan_id - the id of the subscription plan for which we want to receive the possible upgrades
 * @param bool $only_active          - whether to return only active subscription plans or no
 *
 */
function pms_get_subscription_plan_upgrades( $subscription_plan_id, $only_active = true ) {

    $current_post   = get_post( $subscription_plan_id );
    $parent_post_id = $current_post->post_parent;

    $subscription_plan_posts = array();

    while( $post_ancestor = get_post( $parent_post_id ) ) {

        $parent_post_id = $post_ancestor->post_parent;
        $subscription_plan_posts[] = $post_ancestor;

        if( empty( $post_ancestor->post_parent ) )
            break;

    }

    $subscription_plans = array();

    if( !empty( $subscription_plan_posts ) ) {
        foreach( $subscription_plan_posts as $subscription_plan_post ) {

            $subscription_plan = pms_get_subscription_plan( $subscription_plan_post );

            if( $only_active && !$subscription_plan->is_active() )
                continue;

            $subscription_plans[] = $subscription_plan;

        }
    }

    /**
     * Filter the subscription plans available for upgrade just before returning them
     *
     * @param array $subscription_plans
     * @param int   $subscription_plan_id
     * @param bool  $only_active
     *
     */
    return apply_filters( 'pms_get_subscription_plan_upgrades', $subscription_plans, $subscription_plan_id, $only_active );

}


/**
 * Returns the user role attached to the subscription plan with the provided id
 *
 * @param int $subscription_plan_id
 *
 * @return string
 *
 */
function pms_get_subscription_plan_user_role( $subscription_plan_id = 0 ) {

    if( empty( $subscription_plan_id ) )
        return '';

    $user_role = get_post_meta( $subscription_plan_id, 'pms_subscription_plan_user_role', true );

    return $user_role;

}


/**
 * Function that outputs the subscription plans
 *
 * Warning: Should not be used by other plugin developers as it is subject to change
 *
 * @param array $include            - return only these subscription plans
 * @param array $exclude_id_group   - exclude the groups that have these ids
 * @param mixed $member             - bool false to display input fields, object PMS_Member to display member information
 * @param int $default_checked      - default subscription plan to be selected
 *
 * @return string
 *
 */
function pms_output_subscription_plans( $include = array(), $exclude_id_group = array(), $member = false, $default_checked = '', $form_location = '' ) {

    $output = '';
    $pms_settings = get_option( 'pms_payments_settings' );


    // Get all subscription plans
    if( empty( $include ) )
        $subscription_plans = pms_get_subscription_plans();
    else {
        if( !is_object( $include[0] ) )
            $subscription_plans = pms_get_subscription_plans( true, $include );
        else
            $subscription_plans = $include;
    }


    /*
     * Group subscription plans
     */
    $subscription_plan_groups = array();

    if( !empty( $subscription_plans ) ) {
        foreach( $subscription_plans as $subscription_plan ) {
            $subscription_plan_groups[ $subscription_plan->top_parent ][] = $subscription_plan;
        }
    }


    /*
     * Exclude certain groups like the ones the member is already subscribed to
     */
    if( !empty( $exclude_id_group ) ) {
        foreach( $exclude_id_group as $exclude_id ) {

            if( !empty( $subscription_plans ) ) {
                foreach( $subscription_plans as $subscription_plan ) {

                    if( $subscription_plan->id == $exclude_id ) {
                        if( isset( $subscription_plan_groups[ $subscription_plan->top_parent ] ) )
                            unset( $subscription_plan_groups[ $subscription_plan->top_parent ] );
                    }

                }
            }

        }
    }

    /*
     * Display the information for each plan
     */
    if( !empty( $subscription_plan_groups ) ) {

        if( !$member && count( $subscription_plan_groups ) == 1 && count( $subscription_plan_groups[ key($subscription_plan_groups) ] ) == 1 ) {

            $subscription_plan = $subscription_plan_groups[ key($subscription_plan_groups) ][0];

            // Output subscription plan wrapper
            $subscription_plan_output = '<div class="pms-subscription-plan pms-hidden">';

            // Output subscription plan hidden input and label
            $subscription_plan_output .= '<input type="hidden" name="subscription_plans" ' . pms_get_subscription_plan_input_data_attrs( $subscription_plan ) . ' value="' . esc_attr( $subscription_plan->id ) . '" />';
            $subscription_plan_output .= '<label><span class="pms-subscription-plan-name">' . $subscription_plan->name . '</span>';

                // Output subscription plan price
                $subscription_plan_output .= '<span class="pms-subscription-plan-price">' . pms_get_output_subscription_plan_price( $subscription_plan ) . '</span>';

                if( in_array( $form_location, array( 'register', 'new_subscription', 'retry_payment', 'upgrade_subscription', 'register_email_confirmation' ) ) ) {

                    // Output subscription plan trial
                    $subscription_plan_output .= '<span class="pms-subscription-plan-trial">' . pms_get_output_subscription_plan_trial( $subscription_plan ) . '</span>';

                    // Output subscription plan sign-up
                    $subscription_plan_output .= '<span class="pms-subscription-plan-sign-up-fee">' . pms_get_output_subscription_plan_sign_up_fee( $subscription_plan ) . '</span>';

                }

            $subscription_plan_output .= '</label>';

            // Description
            if( !empty($subscription_plan->description) )
                $subscription_plan_output .= '<div class="pms-subscription-plan-description">' . apply_filters( 'pms_output_subscription_plan_description', htmlspecialchars_decode( esc_html( $subscription_plan->description ) ), $subscription_plan )  . '</div>';

            $subscription_plan_output .= '</div>';

            // Modify the entire subscription plan output if desired
            $output .= apply_filters( 'pms_subscription_plan_output', $subscription_plan_output, $subscription_plan );

        } else {

            $current_group = 1;
            $group_count = count( $subscription_plan_groups );

            $default_checked = ( ! empty( $_REQUEST['subscription_plans'] ) ? (int)$_REQUEST['subscription_plans'] : (int)$default_checked );
            $default_checked = ( ! empty( $default_checked ) ? $default_checked : $subscription_plans[0]->id );
            $default_checked = ( ! empty( $_GET['subscription_plan'] ) ? (int)$_GET['subscription_plan'] : (int)$default_checked );
            $default_checked = ( ! empty( $_GET['upgrade_subscription_plan'] ) ? (int)$_GET['upgrade_subscription_plan'] : (int)$default_checked );

            if( $form_location == 'upgrade_subscription' && isset( $subscription_plan_groups[key( $subscription_plan_groups )][0]->id ))
                $default_checked = $subscription_plan_groups[key( $subscription_plan_groups )][0]->id;

            $default_checked = apply_filters( 'pms_output_subscription_default_checked', $default_checked );

            foreach( $subscription_plan_groups as $top_parent_id => $subscriptions ) {

                /*
                 * Output subscription plan fields for forms
                 */
                if( !$member ) {

                    foreach( $subscriptions as $subscription_plan ) {

                        // Output subscription plan wrapper
                        $subscription_plan_output = '<div class="pms-subscription-plan pms-subscription-plan-'. $subscription_plan->id .'">';

                        // Output subscription plan radio button and label
                        $subscription_plan_output .= '<label>';
                            $subscription_plan_output .= '<input type="radio" name="subscription_plans" ' . pms_get_subscription_plan_input_data_attrs( $subscription_plan ) . ' value="' . esc_attr( $subscription_plan->id ) . '" ' .  checked( $default_checked, $subscription_plan->id, false ) . ' />';
                            $subscription_plan_output .= '<span class="pms-subscription-plan-name">' . apply_filters( 'pms_output_subscription_plan_name', esc_html( $subscription_plan->name ), $subscription_plan ) . '</span>';

                            // Output subscription plan price
                            $subscription_plan_output .= '<span class="pms-subscription-plan-price">' . pms_get_output_subscription_plan_price( $subscription_plan ) . '</span>';

                            if( in_array( $form_location, array( 'register', 'new_subscription', 'retry_payment', 'upgrade_subscription', 'register_email_confirmation' ) ) ) {

                                // Output subscription plan trial
                                $subscription_plan_output .= '<span class="pms-subscription-plan-trial">' . pms_get_output_subscription_plan_trial( $subscription_plan ) . '</span>';

                                // Output subscription plan sign-up
                                $subscription_plan_output .= '<span class="pms-subscription-plan-sign-up-fee">' . pms_get_output_subscription_plan_sign_up_fee( $subscription_plan ) . '</span>';

                            }

                        $subscription_plan_output .= '</label>';

                        // Description
                        if( !empty($subscription_plan->description) )
                            $subscription_plan_output .= '<div class="pms-subscription-plan-description">' . apply_filters( 'pms_output_subscription_plan_description', htmlspecialchars_decode( esc_html( $subscription_plan->description ) ), $subscription_plan )  . '</div>';

                        $subscription_plan_output .= '</div>';

                        // Modify the entire subscription plan output if desired
                        $output .= apply_filters( 'pms_subscription_plan_output', $subscription_plan_output, $subscription_plan );

                    }

                }

                $current_group++;
            }

        }

    }

    // Add error message if no plans have been selected
    if( !$member )
        $output .= pms_display_field_errors( pms_errors()->get_error_messages('subscription_plans'), true );

    return apply_filters( 'pms_output_subscription_plans', $output, $include, $exclude_id_group, $member, $pms_settings, $subscription_plans, $form_location );

}


/**
 * Returns the HTML output for the subscription plan price
 *
 * @param PMS_Subscription_Plan $subscription_plan
 *
 * @return string
 *
 */
function pms_get_output_subscription_plan_price( $subscription_plan = null ) {

    if( is_null( $subscription_plan ) )
        return '';

    if( ! is_object( $subscription_plan ) )
        return '';


    // Handle the subscription plan price
    if( $subscription_plan->price == 0 )
        $price_output = '<span class="pms-subscription-plan-price-value">' . __( 'Free', 'paid-member-subscriptions' ) . '</span>';
    else {
        $price_output = pms_format_price( $subscription_plan->price, pms_get_active_currency(), array( 'before_price' => '<span class="pms-subscription-plan-price-value">', 'after_price' => '</span>', 'before_currency' => '<span class="pms-subscription-plan-currency">', 'after_currency' => '</span>' ) );
    }

    $price_output = apply_filters( 'pms_subscription_plan_output_price', '<span class="pms-divider"> - </span>' . $price_output, $subscription_plan );

    // Handle the subscription plan duration
    if( $subscription_plan->duration == 0 )
        $duration_output = apply_filters( 'pms_subscription_plan_output_duration_unlimited', '', $subscription_plan );
    else {
        $duration = '';
        switch ($subscription_plan->duration_unit) {
            case 'day':
                $duration = sprintf( _n( '%s Day', '%s Days', $subscription_plan->duration, 'paid-member-subscriptions' ), $subscription_plan->duration );
                break;
            case 'week':
                $duration = sprintf( _n( '%s Week', '%s Weeks', $subscription_plan->duration, 'paid-member-subscriptions' ), $subscription_plan->duration );
                break;
            case 'month':
                $duration = sprintf( _n( '%s Month', '%s Months', $subscription_plan->duration, 'paid-member-subscriptions' ), $subscription_plan->duration );
                break;
            case 'year':
                $duration = sprintf( _n( '%s Year', '%s Years', $subscription_plan->duration, 'paid-member-subscriptions' ), $subscription_plan->duration );
                break;
        }

        $duration_output = apply_filters('pms_subscription_plan_output_duration_limited', '<span class="pms-divider"> / </span>' . $duration, $subscription_plan);
    }

    $duration_output = apply_filters( 'pms_subscription_plan_output_duration', $duration_output, $subscription_plan );

    // Return output
    return $price_output . $duration_output;

}


/**
 * Returns the HTML output for the subscription plan trial period
 *
 * @param PMS_Subscription_Plan $subscription_plan
 *
 * @return string
 *
 */
function pms_get_output_subscription_plan_trial( $subscription_plan = null ) {

    if( is_null( $subscription_plan ) )
        return '';

    if( ! is_object( $subscription_plan ) )
        return '';

    if( empty( $subscription_plan->trial_duration ) )
        return '';

    // if current user already benefited from the trial on this plan, do not add it again
    if( is_user_logged_in() ){
        $user = get_userdata( get_current_user_id() );

        if( !empty( $user->user_email ) ){

            $used_trial = get_option( 'pms_used_trial_' . $subscription_plan->id, false );

            if( $used_trial !== false && in_array( $user->user_email, $used_trial ) )
                return '';

        }
    }

    if( ! pms_payment_gateways_support( pms_get_active_payment_gateways(), 'subscription_free_trial' ) )
        return '';

    $trial_duration      = $subscription_plan->trial_duration;
    $trial_duration_unit = '';

    switch ( $subscription_plan->trial_duration_unit ) {
        case 'day':
            $trial_duration_unit = __( 'day', 'paid-member-subscriptions' );
            break;
        case 'week':
            $trial_duration_unit = __( 'week', 'paid-member-subscriptions' );
            break;
        case 'month':
            $trial_duration_unit = __( 'month', 'paid-member-subscriptions' );
            break;
        case 'year':
            $trial_duration_unit = __( 'year', 'paid-member-subscriptions' );
            break;
        default:
            $trial_duration_unit = '';
            break;
    }

    // Actual output
    $trial_output = sprintf( __( ' with a %1$s %2$s free trial', 'paid-member-subscriptions' ), $trial_duration, $trial_duration_unit );

    /**
     * Filter the trial output before returning
     *
     * @param string $trial_output
     * @param PMS_Subscription_Plan $subscription_plan
     *
     */
    $trial_output = apply_filters( 'pms_subscription_plan_output_trial', $trial_output, $subscription_plan );

    // Return output
    return $trial_output;

}


/**
 * Returns the HTML output for the subscription plan sign-up fee
 *
 * @param PMS_Subscription_Plan $subscription_plan
 *
 * @return string
 *
 */
function pms_get_output_subscription_plan_sign_up_fee( $subscription_plan = null ) {

    if( is_null( $subscription_plan ) )
        return '';

    if( ! is_object( $subscription_plan ) )
        return '';

    if( empty( $subscription_plan->sign_up_fee ) )
        return '';

    if( ! pms_payment_gateways_support( pms_get_active_payment_gateways(), 'subscription_sign_up_fee' ) )
        return '';

    $sign_up_output = sprintf( __( ' and a %1$s sign-up fee', 'paid-member-subscriptions' ), pms_format_price( $subscription_plan->sign_up_fee, pms_get_active_currency() ), $subscription_plan );

    /**
     * Filter the sign-up output before returning
     *
     * @param string $trial_output
     * @param PMS_Subscription_Plan $subscription_plan
     *
     */
    $sign_up_output = apply_filters( 'pms_subscription_plan_output_sign_up_fee', $sign_up_output, $subscription_plan );

    return $sign_up_output;

}


/**
 * Function that outputs the automatic renewal option in the front-end for the user/customer to see
 *
 */
if( ! function_exists( 'pms_renewal_option_field' ) ) {

    function pms_renewal_option_field( $output, $include, $exclude_id_group, $member, $pms_settings ) {

        if( $member )
            return $output;

        if( ! pms_payment_gateways_support( pms_get_active_payment_gateways(), 'recurring_payments' ) )
            return $output;

        // Get all subscription plans
        if( empty( $include ) )
            $subscription_plans = pms_get_subscription_plans();
        else {
            if( !is_object( $include[0] ) )
                $subscription_plans = pms_get_subscription_plans( true, $include );
            else
                $subscription_plans = $include;
        }

        // Calculate the amount for all subscription plans
        $amount = 0;
        foreach( $subscription_plans as $subscription_plan ) {
            $amount += $subscription_plan->price;
        }

        if( $amount == 0 )
            return $output;

        // Check to see if any subscription plan has the auto-renew set to "customer opts in"
        $subscription_plan_renew = false;
        foreach( $subscription_plans as $subscription_plan ) {
            if( $subscription_plan->recurring == 1 ) {
                $subscription_plan_renew = true;
                break;
            }
        }


        if( ( isset( $pms_settings['recurring'] ) && $pms_settings['recurring'] == 1 ) || $subscription_plan_renew ) {

            $output .= '<div class="pms-subscription-plan-auto-renew">';
            $output .= '<label><input name="pms_recurring" type="checkbox" value="1" ' . ( isset( $_REQUEST['pms_recurring'] ) ? 'checked="checked"' : '' ) . ' />' . apply_filters( 'pms_auto_renew_label', __( 'Automatically renew subscription', 'paid-member-subscriptions' ) ) . '</label>';
            $output .= '</div>';

        }

        if( ! empty( $pms_settings['recurring'] ) )
            $output .= '<input type="hidden" name="pms_default_recurring" value="' . esc_attr( $pms_settings['recurring'] ) . '" />';

        return $output;

    }
    add_filter( 'pms_output_subscription_plans', 'pms_renewal_option_field', 5, 5 );

}


/**
 * Returns a string with all extra HTML data attributes for a given subscription plan
 *
 * @param PMS_Subscription_Plan $subscription_plan
 *
 * @return string
 *
 */
function pms_get_subscription_plan_input_data_attrs( $subscription_plan = null ) {

    if( is_null( $subscription_plan ) )
        return '';

    if( ! is_object( $subscription_plan ) )
        return '';

    // The extra data attributes array for this Subscription Plan
    $subscription_plan_input_data_arr = array(
        'price'       => $subscription_plan->price,
        'duration'    => $subscription_plan->duration
    );

    // Sign Up Fee extra attribute
    if( pms_payment_gateways_support( pms_get_active_payment_gateways(), 'subscription_sign_up_fee' ) ) {
        $subscription_plan_input_data_arr['sign_up_fee'] = $subscription_plan->sign_up_fee;
    }

    // Trial extra attribute
    if( pms_payment_gateways_support( pms_get_active_payment_gateways(), 'subscription_free_trial' ) ) {
        $subscription_plan_input_data_arr['trial'] = ( ! empty( $subscription_plan->trial_duration ) ? '1' : '0' );
    }

    // Recurring extra attribute
    if( pms_payment_gateways_support( pms_get_active_payment_gateways(), 'recurring_payments' ) ) {
        $subscription_plan_input_data_arr['recurring'] = $subscription_plan->recurring;
    }


    /**
     * Filter extra input data attributes before concatenating them as a string
     *
     * @param array $subscription_plan_input_data_arr
     * @param int   $subscription_plan->id
     *
     */
    $subscription_plan_input_data_arr = apply_filters( 'pms_get_subscription_plan_input_data_attrs', $subscription_plan_input_data_arr, $subscription_plan->id );


    // Concatenate the data attributes into a string
    $subscription_plan_input_data = '';

    foreach( $subscription_plan_input_data_arr as $key => $val ) {
        $subscription_plan_input_data .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $val ) . '" ';
    }

    return $subscription_plan_input_data;

}


function _pms_compare_subscription_plans( $object_a, $object_b ) {
    return $object_a->id - $object_b->id;
}

/**
 * Returns an array of all subscription plans that are active. Format: ID -> name
 *
 * @return array
 */
function pms_get_subscription_plans_list() {
    $plans = array();

    $plan_ids = get_posts( array( 'post_type' => 'pms-subscription', 'meta_key' => 'pms_subscription_plan_status', 'meta_value' => 'active', 'numberposts' => -1, 'post_status' => 'any', 'fields' => 'ids' ) );

    if( empty( $plan_ids ) )
        return $plans;

    foreach( $plan_ids as $plan_id )
        $plans[$plan_id] = get_the_title( $plan_id );

    return $plans;
}

/**
 * Shows a message to admins in the front-end forms if there are no subscription plans defined
 */
function pms_no_subscription_plans_notification() {

    if( !current_user_can( 'manage_options' ) )
        return;

    $plans = pms_get_subscription_plans_list();

    if( empty( $plans ) ){

        echo '<div class="pms-warning-message-wrapper">';
            echo '<p>' . wp_kses( __( 'You need to create Subscription Plans before you can use this form. Go to your <strong>Dashboard -> Paid Member Subscriptions -> Subscriptions Plans</strong> to do this.', 'paid-member-subscriptions' ), array( 'strong' => array() ) ) . '</p>';
            echo '<p><em>' . esc_html__( 'This message is visible only by Administrators.', 'paid-member-subscriptions' ) . '</em></p>';
        echo '</div>';

    }

}
add_action( 'pms_new_subscription_form_top', 'pms_no_subscription_plans_notification' );

/**
 * Returns given users subscription that is part of the same tier as the given subscription plan id
 *
 * @since 1.9.5
 *
 * @return PMS_Member_Subscription|bool
 *
 */
function pms_get_current_subscription_from_tier( $user_id, $plan_id ) {

    if( empty( $user_id ) || empty( $plan_id ) )
        return false;

    $subscription_plans_tier = pms_get_subscription_plans_group( $plan_id );
    $possible_values         = array();

    foreach( $subscription_plans_tier as $plan )
        $possible_values[] = $plan->id;

    $subscriptions = pms_get_member_subscriptions( array( 'user_id' => $user_id ) );

    if( empty( $subscriptions ) )
        return false;

    foreach( $subscriptions as $subscription ){
        if( in_array( $subscription->subscription_plan_id, $possible_values ) )
            return $subscription;
    }

    return false;

}
