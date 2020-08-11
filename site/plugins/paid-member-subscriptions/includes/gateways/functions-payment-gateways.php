<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Function that returns an array with all payment gateways
 *
 * @param $only_slugs - returns an array with the payment gateways slugs
 *
 * @return array
 *
 */
function pms_get_payment_gateways( $only_slugs = false ) {

    $payment_gateways = apply_filters( 'pms_payment_gateways', array(

        'manual'          => array(
            'display_name_user'  => __( 'Manual/Offline', 'paid-member-subscriptions' ),
            'display_name_admin' => __( 'Manual/Offline', 'paid-member-subscriptions' ),
            'class_name'         => 'PMS_Payment_Gateway_Manual'
        ),

        'paypal_standard' => array(
            'display_name_user'  => __( 'PayPal', 'paid-member-subscriptions' ),
            'display_name_admin' => __( 'PayPal Standard', 'paid-member-subscriptions' ),
            'class_name'         => 'PMS_Payment_Gateway_PayPal_Standard'
        )

    ));


    if( $only_slugs )
        $payment_gateways = array_keys( $payment_gateways );

    return $payment_gateways;

}


/**
 * The purpose of this function is to add filters for the display name of the payment gateways. The display names appear in the admin panel
 * and also on the front-end when selecting a Payment Method.
 *
 * This filters are added simply to avoid hooking into the main filter ( "pms_payment_gateways" ) for changing the names that get displayed
 *
 * @param array $payment_gateways
 *
 * @return array
 *
 */
function _pms_get_payment_gateways_hooks( $payment_gateways = array() ) {

    if( empty( $payment_gateways ) )
        return $payment_gateways;

    foreach( $payment_gateways as $slug => $payment_gateway ) {

        if( !isset( $payment_gateway['display_name_user'] ) || !isset( $payment_gateway['display_name_user'] ) )
            continue;

        $payment_gateways[$slug]['display_name_user']  = apply_filters( 'pms_payment_gateway_' . $slug . '_display_name_user', $payment_gateway['display_name_user'] );
        $payment_gateways[$slug]['display_name_admin'] = apply_filters( 'pms_payment_gateway_' . $slug . '_display_name_admin', $payment_gateway['display_name_admin'] );
    }

    return $payment_gateways;

}
add_filter( 'pms_payment_gateways', '_pms_get_payment_gateways_hooks', 100 );


/**
 * Returns the payment gateway object
 *
 * @param string $gateway_slug
 * @param array $payment_data
 *
 * @return object
 *
 */
function pms_get_payment_gateway( $gateway_slug = '', $payment_data = array() ) {

    if( empty( $gateway_slug ) )
        return null;

    $payment_gateways = pms_get_payment_gateways();

    if( !isset( $payment_gateways[$gateway_slug] ) || !isset( $payment_gateways[$gateway_slug]['class_name'] ) )
        return null;

    $class = apply_filters( 'pms_get_payment_gateway_class_name', $payment_gateways[$gateway_slug]['class_name'], $gateway_slug, $payment_data );

    return new $class( $payment_data );

}


/**
 * Verifies if a feature is supported by any of the given payment gateways
 *
 * @param array $payment_gateways
 * @param string $feature
 *
 * @return bool
 *
 */
function pms_payment_gateways_support( $payment_gateways = array(), $feature = '' ) {

    if( empty( $feature ) )
        return false;

    if( empty( $payment_gateways ) )
        return false;

    foreach( $payment_gateways as $gateway_slug ) {

        $payment_gateway = pms_get_payment_gateway( $gateway_slug );

        if( is_null( $payment_gateway ) )
            continue;

        if( $payment_gateway->supports( $feature ) )
            return true;

    }

    return false;

}


/**
 * Returns the active pay gates selected by the admin in the Payments tab in PMS Settings
 *
 * @return array
 *
 */
function pms_get_active_payment_gateways() {

    $settings = get_option( 'pms_payments_settings' );

    if( empty( $settings['active_pay_gates'] ) )
        return array();
    else
        return $settings['active_pay_gates'];

}


/**
 * Direct the data flow to the payment gateway
 *
 * @param string $payment_gateway_slug
 * @param array $payment_data
 *
 * @return void
 *
 */
function pms_to_gateway( $payment_gateway_slug, $payment_data ) {

    if( has_action( 'pms_process_payment_' . $payment_gateway_slug ) ) {

        $settings = get_option( 'pms_payments_settings' );

        do_action( 'pms_process_payment_' . $payment_gateway_slug, $payment_data, $settings );

    } else {

        $payment_gateway = pms_get_payment_gateway( $payment_gateway_slug, $payment_data );
        $payment_gateway->process_sign_up();

    }

}


/*
 * Processes the webhooks for all active payment gateways
 *
 * @return void
 *
 */
function pms_payment_gateways_webhook_catcher() {

    $gateways = pms_get_payment_gateways();

    foreach( $gateways as $gateway_slug => $gateway_details ) {
        $gateway = pms_get_payment_gateway( $gateway_slug );

        if( $gateway !== null )
            $gateway->process_webhooks();
    }

}
add_action( 'init', 'pms_payment_gateways_webhook_catcher', 1 );


/*
 * If a payment process confirmation is provided in the request call the
 * payment gateway in question and process the confirmation
 *
 */
function pms_payment_gateways_process_confirmation() {

    if( !isset( $_REQUEST['pmstkn'] ) )
        return;

    if( empty( $_REQUEST['pms-gateway'] ) )
        return;

    $gateway_slug = base64_decode( $_REQUEST['pms-gateway'] );

    /**
     * Skip verifying nonce if automatically login is set to "Yes"
     * After wp_signon we cannot refresh before generating the nonce, so it's always generated for a non-logged in user.
     * For PayPal Express, when returning to the site to confirm the payment (with auto-login on), the user is logged in so the nonce will be different.
     *
     */

    if ( !pms_is_autologin_active() && !wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_payment_process_confirmation' ) )
        return;

    if ( ( $gateway_slug != 'paypal_express' ) && !wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_payment_process_confirmation' ) )
        return;

    $active_payment_gateways = pms_get_active_payment_gateways();

    if( in_array( $gateway_slug, $active_payment_gateways ) ) {

        $gateway = pms_get_payment_gateway( $gateway_slug );
        $gateway->process_confirmation();

    }

}
add_action( 'init', 'pms_payment_gateways_process_confirmation', 1 );


/**
 * Function that outputs the payment gateway options
 *
 * @param array $pms_settings     - the saved settings
 *
 * @return string
 *
 */
function pms_get_output_payment_gateways( $pms_settings = array() ) {

    if( empty($pms_settings) )
        $pms_settings = get_option( 'pms_payments_settings' );

    $output = '';

    // Output gateways only when we have active subscription plans
    $active_subscriptions = pms_get_subscription_plans();
    if ( empty($active_subscriptions) ) {
        return $output;
    }


    $active_gateways = ( ! empty( $pms_settings['active_pay_gates'] ) && is_array( $pms_settings['active_pay_gates'] ) ? $pms_settings['active_pay_gates'] : array() );

    // Filter active payment gateways
    // Remove gateways that are not registered, but exist in the Settings
    if( ! empty( $active_gateways ) && is_array( $active_gateways ) ) {

        $payment_gateways = pms_get_payment_gateways( true );

        foreach( $active_gateways as $key => $active_gateway ) {

            if( ! in_array( $active_gateway, $payment_gateways ) )
                unset( $active_gateways[$key] );

        }

        $active_gateways = array_values( $active_gateways );

    }

    /**
     * Generate supports data attributes for each payment gateway
     *
     */
    $gateway_supports_arr = array();

    if( ! empty( $active_gateways ) ) {
        foreach( $active_gateways as $gateway_slug ) {

            $gateway_supports_arr[$gateway_slug] = array();

            $gateway_obj = pms_get_payment_gateway( $gateway_slug );

            if( is_null( $gateway_obj ) )
                continue;

            if( $gateway_obj->supports( 'subscription_sign_up_fee' ) )
                $gateway_supports_arr[$gateway_slug]['sign_up_fee'] = 1;

            if( $gateway_obj->supports( 'subscription_free_trial' ) )
                $gateway_supports_arr[$gateway_slug]['trial'] = 1;

            if( $gateway_obj->supports( 'recurring_payments' ) )
                $gateway_supports_arr[$gateway_slug]['recurring'] = 1;

        }
    }

    // Transform gateway supports for each gateway from array to printable string
    foreach( $gateway_supports_arr as $gateway_slug => $gateway_supports ) {

        $gateway_input_data = '';
        foreach( $gateway_supports as $key => $val ) {
            $gateway_input_data .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $val ) . '" ';
        }

        $gateway_supports_arr[$gateway_slug] = $gateway_input_data;

    }


    /**
     * Generate output string for the payment gateways
     *
     */

    // Show payment gateway errors
    $output .= pms_display_field_errors( pms_errors()->get_error_messages( 'payment_gateway' ), true );

    if( empty( $active_gateways ) ) {

        // Output payment gateways not available message
        $output .= '<div id="pms-active-gateways-not-available">';
            $output .= '<span>' . __( 'No payment methods are available to complete the checkout process.', 'paid-member-subscriptions' ) . '</span>';
        $output .= '</div>';

    // If there's only one payment gateway saved
    } else if( count( $active_gateways ) == 1 ) {

        $paygate_key = ( $active_gateways[0] != 'paypal_standard' ? $active_gateways[0] : 'paypal_standard' );

        $output .= apply_filters( 'pms_output_payment_gateway_input_hidden', '<div id="pms-paygates-wrapper"><input type="hidden" class="pms_pay_gate" name="pay_gate" value="' . esc_attr( $paygate_key ) . '" ' . $gateway_supports_arr[$paygate_key] . ' /></div>', $active_gateways[0] );

    } else {

        $payment_gateways = pms_get_payment_gateways();

        // Set default payment gateway
        $default_gateway  = ( !empty( $pms_settings['default_payment_gateway'] ) ? ( in_array( $pms_settings['default_payment_gateway'], $active_gateways ) ? $pms_settings['default_payment_gateway'] : $active_gateways[0] ) : 'paypal_standard' );
        $default_gateway  = ( !empty( $_POST['pay_gate'] ) ? esc_attr( $_POST['pay_gate'] ) : $default_gateway );

        // Output content for the payment gateways
        $output .= '<div id="pms-paygates-wrapper">';

            $output .= apply_filters( 'pms_get_output_payment_gateways_before', '<h3>' . __( 'Select a Payment Method', 'paid-member-subscriptions' ) . '</h3>', $pms_settings );

            $output .= '<div id="pms-paygates-inner">';

                if( !empty( $active_gateways ) ) {
                    foreach( $active_gateways as $paygate_key ) {

                        // Check to see if the gateway exists
                        if( empty( $payment_gateways[$paygate_key] ) )
                            continue;

                        $output .= '<label>';
                            $output .= apply_filters( 'pms_output_payment_gateway_input_radio', '<input type="radio" class="pms_pay_gate" name="pay_gate" value="' . esc_attr( $paygate_key ) . '" ' . checked( $default_gateway, $paygate_key, false ) . $gateway_supports_arr[$paygate_key] . ' />', $paygate_key );
                            $output .= '<span class="pms-paygate-name">' . $payment_gateways[$paygate_key]['display_name_user'] . '</span>';
                        $output .= '</label>';

                    }
                }

            $output .= '</div>';

            // Output payment gateways not available message
            $output .= '<div id="pms-gateways-not-available">';
                $output .= '<span>' . __( 'No payment methods are available for the selected subscription plan.', 'paid-member-subscriptions' ) . '</span>';
            $output .= '</div>';

        $output .= '</div>';

    }

    //backwards compatibility with older add-on versions
    if ( defined( 'PMS_STRIPE_VERSION' ) && version_compare( PMS_STRIPE_VERSION, '1.2.5' ) == -1 )
        $pms_settings = get_option( 'pms_settings' );

    return apply_filters( 'pms_get_output_payment_gateways', $output, $pms_settings );

}

/**
 * Function that outputs the payment gateway options after the subscription plans
 * radio buttons
 *
 * @return string
 *
 */
function pms_output_subscription_plans_payment_gateways( $output, $include, $exclude_id_group, $member, $pms_settings ) {

    if( is_object( $member ) )
        return $output;

    $output .= pms_get_output_payment_gateways( $pms_settings );

    return $output;

}
add_filter( 'pms_output_subscription_plans', 'pms_output_subscription_plans_payment_gateways', 10, 5);
