<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

    /**
     * Because validation and redirects in PB happen much later than in PMS we need to make some of the data available for later
     * and we're going to create a global variable that will store information
     *
     * @param object $gateway_object
     *
     */
    function pms_pb_set_gateway_details( $gateway_object, $gateway_data = array() ) {

        // Do this only when PB is found
        if( ! defined( 'PROFILE_BUILDER_VERSION' ) )
            return;

        if( empty( $gateway_data ) )
            return;

        global $pms_gateway_data;
        $pms_gateway_data = array();

        // Add the payment id
        $pms_gateway_data['payment_id']           = $gateway_object->payment_id;
        $pms_gateway_data['user_id']              = $gateway_object->user_id;
        $pms_gateway_data['subscription_plan_id'] = $gateway_object->subscription_plan->id;

        // Add the payment gateway slug
        $gateways = pms_get_payment_gateways();

        foreach( $gateways as $gateway_slug => $gateway ) {
            if( $gateway['class_name'] == get_class( $gateway_object ) )
                $pms_gateway_data['payment_gateway_slug'] = $gateway_slug;
        }

    }
    add_action( 'pms_payment_gateway_initialised', 'pms_pb_set_gateway_details', 10, 2 );


    /*
     * Handle the redirect to PayPal from the saved transients
     * Also, we change the default
     *
     */
    function pms_pb_payment_redirect_link() {

        if( !isset( $_GET['pmstkn'] ) || !wp_verify_nonce( $_GET['pmstkn'], 'pms_payment_redirect_link') )
            return;

        if (empty($_GET['pms_payment_id']))
            return;

        $payment_id = (int)$_GET['pms_payment_id'];

        /**
         * Automatically logs the user in before redirecting him to the PayPal checkout page
         * Works based on the Automatically Login form setting
         *
         * @since 2.0.5
         */
        if( isset( $_GET['pms_autologin_before_redirect'] ) && $_GET['pms_autologin_before_redirect'] == 'true' ){
            $payment = pms_get_payment( $payment_id );

            if( !empty( $payment->user_id ) )
                wp_set_auth_cookie( $payment->user_id );
        }

        $redirect_to    = get_transient( 'pms_pb_pp_redirect_' . $payment_id );
        $redirect_back  = get_transient( 'pms_pb_pp_redirect_back_' . $payment_id );

        $redirect_to_base  = explode( '?', $redirect_to );
        $redirect_to_parts = explode( '&', $redirect_to_base[1] );

        $redirect_to_base = $redirect_to_base[0];
        $redirect_to_args = '';

        $current_part = 1;
        foreach( $redirect_to_parts as $redirect_to_part ) {

            if( strpos( $redirect_to_part, 'return' ) === 0 && !empty( $redirect_back ) ) {
                $redirect_to_part = 'return=' . $redirect_back;
            }

            $redirect_to_args .=  $redirect_to_part . ( $current_part != count( $redirect_to_parts ) ? '&' : '' );

            $current_part++;
        }

        $redirect_to_base .= '?' . $redirect_to_args;

        header( 'Location:' . $redirect_to_base );
        exit;

    }
    add_action('init', 'pms_pb_payment_redirect_link');


    /*
     * Because redirects happen later and are handled with JS we will save the PayPal link in a transient
     * for security reasons. In the end we will refresh the current page and handle the redirect to PayPal on init
     * with the value we save in this transient
     *
     */
    function pms_pb_before_paypal_redirect( $paypal_link, $gateway_object, $settings ) {

        if( !isset( $gateway_object->payment_id ) )
            return;

        set_transient( 'pms_pb_pp_redirect_' . $gateway_object->payment_id, $paypal_link, DAY_IN_SECONDS );

    }
    add_action( 'pms_before_paypal_redirect', 'pms_pb_before_paypal_redirect', 99, 3 );


    /**
     * Change PB's ( until PB version 2.5.5 ) default success message with a custom one when a payment has been made
     *
     * This function is compatible with Profile Builder until version 2.5.5. In version 2.5.6 of Profile Builder
     * a refactoring for the redirects has been made and some hooks have been removed / modified, one of them being
     * the "wppb_register_redirect" filter, making this callback incompatible with newer versions of PB
     *
     */
    function pms_pb_register_redirect_plugins_loaded() {

        if( ! function_exists( 'wppb_build_redirect' ) ) {

            function pms_pb_register_redirect_link( $redirect_link ) {

                global $pms_gateway_data;

                if( !isset( $pms_gateway_data['payment_id'] ) || ( isset( $pms_gateway_data['payment_gateway_slug'] ) && $pms_gateway_data['payment_gateway_slug'] != 'paypal_standard' ) )
                    return $redirect_link;

                // Scrap the redirect URL from the whole redirect message
                $link = pms_pb_scrap_register_redirect_link( $redirect_link );

                if ( empty( $redirect_link ) || !empty($link) ) {

                    // save in transient
                    set_transient('pms_pb_pp_redirect_back_' . $pms_gateway_data['payment_id'], $link, DAY_IN_SECONDS );

                    $redirect_link = sprintf(
                        '<p class="redirect_message">%1$s <meta http-equiv="Refresh" content="5;url=%2$s" /></p>',
                        __( 'You will soon be redirected to complete the payment.', 'paid-member-subscriptions' ),
                        wp_nonce_url( add_query_arg( array( 'pms_payment_id' => $pms_gateway_data['payment_id'] ), pms_get_current_page_url() ), 'pms_payment_redirect_link', 'pmstkn' )
                    );

                    return $redirect_link;
                }

                return $redirect_link;

            }
            add_filter( 'wppb_register_redirect', 'pms_pb_register_redirect_link', 100 );

        }


        /**
         * Change PB's ( PB version 2.5.6 and higher ) default success message with a custom one when a payment has been made
         *
         */
        if( function_exists( 'wppb_build_redirect' ) ) {

            /**
             * Change the redirect link
             *
             */
            function pms_pb_register_redirect_link( $redirect_link ) {

                global $pms_gateway_data;

                if( !isset( $pms_gateway_data['payment_id'] ) || ( isset( $pms_gateway_data['payment_gateway_slug'] ) && $pms_gateway_data['payment_gateway_slug'] != 'paypal_standard' ) )
                    return $redirect_link;

                // Save the redirect link in a transient
                set_transient('pms_pb_pp_redirect_back_' . $pms_gateway_data['payment_id'], $redirect_link, DAY_IN_SECONDS );

                return wp_nonce_url( add_query_arg( array( 'pms_payment_id' => $pms_gateway_data['payment_id'] ), pms_get_current_page_url() ), 'pms_payment_redirect_link', 'pmstkn' );

            }
            add_filter( 'wppb_register_redirect', 'pms_pb_register_redirect_link', 100 );

            /**
             * Remove PB's default redirect message, but keep the refresh meta element
             *
             */
            function pms_pb_remove_redirect_message( $message, $redirect_url, $redirect_delay, $redirect_url_href, $redirect_type, $form_args ) {

                global $pms_gateway_data;

                if( !isset( $pms_gateway_data['payment_id'] ) || ( isset( $pms_gateway_data['payment_gateway_slug'] ) && $pms_gateway_data['payment_gateway_slug'] != 'paypal_standard' ) )
                    return $message;

                //we are doing a <meta> tag redirect below
                //if this number is not set in front of the URL under the content attribute certain browsers do not redirect
                if ( empty( $redirect_delay) || !is_numeric( $redirect_delay ) )
                    $redirect_delay = 0;

                /**
                 * Add a parameter to the redirect URL if autologin is enabled for this form
                 *
                 * @since 2.0.5
                 */
                if( isset( $form_args['login_after_register'] ) && $form_args['login_after_register'] == 'Yes' )
                    $redirect_url = add_query_arg( 'pms_autologin_before_redirect', 'true', $redirect_url );

                $message = '<meta http-equiv="Refresh" content="'. $redirect_delay .';url='. $redirect_url .'" />';

                $message .= '<p>' . __( 'You are being redirected to PayPal to complete the payment...', 'paid-member-subscriptions' ) . '<br>';
                $message .= sprintf( __( '%sClick here%s to go now.', 'paid-member-subscriptions' ), '<a href="'.$redirect_url.'">', '</a>' ) . '</p>';

                return $message;

            }
            add_filter( 'wppb_redirect_message_before_returning', 'pms_pb_remove_redirect_message', 10, 6 );

        }

    }
    add_action( 'plugins_loaded', 'pms_pb_register_redirect_plugins_loaded', 11 );

    /*
     * When redirecting after successful registration, PB inserts a redirection message instead of the register form
     * We need to scrap this message and return only the URL. This is used in cases where this URL does not exist and
     * we need to redirect the user to the Register Success message from PMS
     *
     */
    function pms_pb_scrap_register_redirect_link( $redirect_link ) {

        $link = '';
        $redirect_link_parts = explode("'", $redirect_link);

        if ( strpos($redirect_link, '<script>') !== false ) { // happens when login after register is true

            $link = $redirect_link_parts[1];

        } else {

            foreach ($redirect_link_parts as $part) {

                if ( strpos( $part, 'http') !== false ) {

                    $parts = explode( '"', $part );

                    foreach( $parts as $small_part ) {
                        if( strpos( $small_part, 'http' ) === 0 ) {
                            $link = $small_part;
                            break 2;
                        }
                    }
                }
            }

        }

        return $link;

    }
