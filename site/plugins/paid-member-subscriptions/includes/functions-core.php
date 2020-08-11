<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

    /**
     * Function that is a wrapper for default WP function get_post_meta,
     * but if provided only the $post_id will return an associative array with values,
     * not an associative array of array
     *
     * @param $post_id      - the ID of the post
     * @param $key          - the post meta key
     * @param $single
     *
     */
    function pms_get_post_meta( $post_id, $key = '', $single = false ) {

        if( empty( $key ) ) {
            $post_meta = get_post_meta( $post_id );

            foreach( $post_meta as $key => $value ) {
                $post_meta[$key] = $value[0];
            }

            return $post_meta;
        }

        return get_post_meta( $post_id, $key, $single );

    }


    /**
     * Function that returns all the users that are not members yet
     *
     * @return array
     *
     */
    function pms_get_users_non_members( $args = array() ) {

        global $wpdb;

        $defaults = array(
            'orderby' => 'ID',
            'offset'  => '',
            'limit'   => ''
        );

        $args = apply_filters( 'pms_get_users_non_members_args', wp_parse_args( $args, $defaults ), $args, $defaults );



        // Start query string
        $query_string = "SELECT {$wpdb->users}.ID, {$wpdb->users}.user_login ";

        // Query string sections
        $query_from   = "FROM {$wpdb->users} ";
        $query_join   = "LEFT JOIN {$wpdb->prefix}pms_member_subscriptions ON {$wpdb->users}.ID = {$wpdb->prefix}pms_member_subscriptions.user_id ";

        $query_where  = "WHERE {$wpdb->prefix}pms_member_subscriptions.user_id is null ";

        $query_limit = '';
        if( !empty( $args['limit'] ) )
            $query_limit = "LIMIT " . $args['limit'] . " ";

        $query_offset = '';
        if( !empty( $args['offset'] ) )
            $query_offset = "OFFSET " . $args['offset'] . " ";


        // Concatenate the sections into the full query string
        $query_string .= $query_from . $query_join . $query_where . $query_limit . $query_offset;

        $results = $wpdb->get_results( $query_string, ARRAY_A );

        $users   = array();
        $blog_id = get_current_blog_id();

        if( !empty( $results ) ) {
            foreach( $results as $result ) {
                if( ( is_multisite() && is_user_member_of_blog( $result['ID'], $blog_id ) ) || !is_multisite() )
                    $users[] = array( 'id' => $result['ID'], 'username' => $result['user_login'] );
            }
        }

        return apply_filters( 'pms_get_users_non_members', $users, $args );

    }


    /**
     * Handles errors in the front end
     *
     */
    function pms_errors() {
        static $wp_errors;

        return ( isset($wp_errors) ? $wp_errors : ( $wp_errors = new WP_Error( null, null, null ) ) );
    }


    /**
     * Handles success messages in front end
     *
     */
    function pms_success() {
        static $pms_success;

        return ( isset($pms_success) ? $pms_success : ( $pms_success = new PMS_Success( null, null ) ) );
    }


    /**
     * Checks to see if there are any success messages somewhere in the
     * URL and add them to the success object
     *
     */
    function pms_check_request_args_success_messages() {

        if( !isset($_REQUEST) )
            return;

        // If there is a success message in the request add it directly
        if( isset( $_REQUEST['pmsscscd'] ) && isset( $_REQUEST['pmsscsmsg'] ) ) {

            $message_code = esc_attr( base64_decode( trim($_REQUEST['pmsscscd']) ) );
            $message      = esc_attr( base64_decode( trim($_REQUEST['pmsscsmsg']) ) );

            pms_success()->add( $message_code, $message );

        // If there is no message, but the code is present check to see for a gateway action present
        // and add messages
        } elseif( isset( $_REQUEST['pmsscscd'] ) && !isset( $_REQUEST['pmsscsmsg'] ) ) {

            $message_code = esc_attr( base64_decode( trim($_REQUEST['pmsscscd']) ) );

            if( !isset( $_REQUEST['pms_gateway_payment_action'] ) )
                return;

            $payment_action = esc_attr( base64_decode( trim( $_REQUEST['pms_gateway_payment_action'] ) ) );

            if( isset( $_REQUEST['pms_gateway_payment_id'] ) ) {

                $payment_id = esc_attr( base64_decode( trim( $_REQUEST['pms_gateway_payment_id'] ) ) );
                $payment    = pms_get_payment( $payment_id );

                // If status of the payment is completed add a success message
                if( $payment->status == 'completed' ) {

                    if( $payment_action == 'upgrade_subscription' )
                        pms_success()->add( $message_code, apply_filters( 'pms_message_gateway_payment_action', __( 'Congratulations, you have successfully upgraded your subscription.', 'paid-member-subscriptions' ), $payment->status, $payment_action, $payment ) );

                    elseif( $payment_action == 'renew_subscription' )
                        pms_success()->add( $message_code, apply_filters( 'pms_message_gateway_payment_action', __( 'Congratulations, you have successfully renewed your subscription.', 'paid-member-subscriptions' ), $payment->status, $payment_action, $payment ) );

                    elseif( $payment_action == 'new_subscription' )
                        pms_success()->add( $message_code, apply_filters( 'pms_message_gateway_payment_action', __( 'Congratulations, you have successfully subscribed to our website.', 'paid-member-subscriptions' ), $payment->status, $payment_action, $payment ) );

                    elseif( $payment_action == 'retry_payment' )
                        pms_success()->add( $message_code, apply_filters( 'pms_message_gateway_payment_action', __( 'Congratulations, you have successfully subscribed to our website.', 'paid-member-subscriptions' ), $payment->status, $payment_action, $payment ) );

                } elseif( $payment->status == 'pending' ) {

                    if( $payment_action == 'upgrade_subscription' )
                        pms_success()->add( $message_code, apply_filters( 'pms_message_gateway_payment_action', __( 'Thank you for your payment. The upgrade may take a while to be processed.', 'paid-member-subscriptions' ), $payment->status, $payment_action, $payment ) );

                    elseif( $payment_action == 'renew_subscription' )
                        pms_success()->add( $message_code, apply_filters( 'pms_message_gateway_payment_action', __( 'Thank you for your payment. The renew may take a while to be processed.', 'paid-member-subscriptions' ), $payment->status, $payment_action, $payment ) );

                    elseif( $payment_action == 'new_subscription' )
                        pms_success()->add( $message_code, apply_filters( 'pms_message_gateway_payment_action', __( 'Thank you for your payment. The subscription may take a while to get activated.', 'paid-member-subscriptions' ), $payment->status, $payment_action, $payment ) );

                    elseif( $payment_action == 'retry_payment' )
                        pms_success()->add( $message_code, apply_filters( 'pms_message_gateway_payment_action', __( 'Thank you for your payment. The subscription may take a while to get activated.', 'paid-member-subscriptions' ), $payment->status, $payment_action, $payment ) );

                }

            }

        }

    }
    add_action( 'init', 'pms_check_request_args_success_messages' );


    /**
     * Displays general errors at the top of the forms
     *
     */
    function pms_display_errors_form_general() {

        $form_error = pms_errors()->get_error_message( 'form_general' );

        if( empty( $form_error ) )
            return;

        echo '<div class="pms-form-errors-wrapper">';
            echo '<p>' . $form_error . '</p>';
        echo '</div>';

    }
    add_action( 'pms_register_form_top', 'pms_display_errors_form_general' );
    add_action( 'pms_new_subscription_form_top', 'pms_display_errors_form_general' );
    add_action( 'pms_upgrade_subscription_form_top', 'pms_display_errors_form_general' );
    add_action( 'pms_renew_subscription_form_bottom', 'pms_display_errors_form_general' );
    add_action( 'pms_retry_payment_form_top', 'pms_display_errors_form_general' );


    /**
     * Adds a hidden span with the text placeholder for the submit buttons while
     * processing the data
     *
     */
    function pms_add_hidden_submit_button_loading_placeholder_text() {

        echo '<span id="pms-submit-button-loading-placeholder-text" style="display: none;">' . apply_filters( 'pms_submit_button_loading_placeholder_text', __( 'Processing. Please wait...', 'paid-member-subscriptions' ) ) . '</span>';

    }
    add_action( 'pms_register_form_bottom', 'pms_add_hidden_submit_button_loading_placeholder_text' );
    add_action( 'pms_new_subscription_form_bottom', 'pms_add_hidden_submit_button_loading_placeholder_text' );
    add_action( 'pms_upgrade_subscription_form_bottom', 'pms_add_hidden_submit_button_loading_placeholder_text' );
    add_action( 'pms_renew_subscription_form_bottom', 'pms_add_hidden_submit_button_loading_placeholder_text' );
    add_action( 'pms_retry_payment_form_bottom', 'pms_add_hidden_submit_button_loading_placeholder_text' );


    /**
     * Function that echoes the errors of a field
     *
     * @param array $field_errors - an array containing the errors
     *
     */
    function pms_display_field_errors( $field_errors = array(), $return = false ) {

        $output = '';

        if( !empty( $field_errors ) ) {
            $output = '<div class="pms_field-errors-wrapper">';

            foreach( $field_errors as $field_error ) {
                $output .= '<p>' . $field_error . '</p>';
            }

            $output .= '</div>';
        }

        if( $return )
            return $output;
        else
            echo $output;

    }


    /**
     * Function that echoes success messages
     *
     * @param array $messages - an array containing the messages
     *
     */
    function pms_display_success_messages( $messages = array(), $return = false ) {
        static $hasRun = false;

        $output = '';

        if ( !$hasRun ) {

            if( !empty( $messages ) ) {
                $output = '<div class="pms_success-messages-wrapper">';

                foreach( $messages as $message ) {
                    $output .= '<p>' . $message . '</p>';
                }

                $output .= '</div>';
            }
        }

        $hasRun = true;

        if( $return )
            return $output;
        else
            echo $output;

    }


    /**
     * Returns an array with the currency codes and their names
     *
     * @return array
     *
     */
    function pms_get_currencies() {

        $currencies = array(
            'USD'   => __( 'US Dollar', 'paid-member-subscriptions' ),
            'EUR'   => __( 'Euro', 'paid-member-subscriptions' ),
            'GBP'   => __( 'Pound Sterling', 'paid-member-subscriptions' ),
            'CAD'   => __( 'Canadian Dollar', 'paid-member-subscriptions' ),
            'AUD'   => __( 'Australian Dollar', 'paid-member-subscriptions' ),
            'BRL'   => __( 'Brazilian Real', 'paid-member-subscriptions' ),
            'CZK'   => __( 'Czech Koruna', 'paid-member-subscriptions' ),
            'DKK'   => __( 'Danish Krone', 'paid-member-subscriptions' ),
            'HKD'   => __( 'Hong Kong Dollar', 'paid-member-subscriptions' ),
            'HUF'   => __( 'Hungarian Forint', 'paid-member-subscriptions' ),
            'ILS'   => __( 'Israeli New Sheqel', 'paid-member-subscriptions' ),
            'JPY'   => __( 'Japanese Yen', 'paid-member-subscriptions' ),
            'MYR'   => __( 'Malaysian Ringgit', 'paid-member-subscriptions' ),
            'MXN'   => __( 'Mexican Peso', 'paid-member-subscriptions' ),
            'NOK'   => __( 'Norwegian Krone', 'paid-member-subscriptions' ),
            'NZD'   => __( 'New Zealand Dollar', 'paid-member-subscriptions' ),
            'PHP'   => __( 'Philippine Peso', 'paid-member-subscriptions' ),
            'PLN'   => __( 'Polish Zloty', 'paid-member-subscriptions' ),
            'RUB'   => __( 'Russian Ruble', 'paid-member-subscriptions' ),
            'SGD'   => __( 'Singapore Dollar', 'paid-member-subscriptions' ),
            'SEK'   => __( 'Swedish Krona', 'paid-member-subscriptions' ),
            'CHF'   => __( 'Swiss Franc', 'paid-member-subscriptions' ),
            'TWD'   => __( 'Taiwan New Dollar', 'paid-member-subscriptions' ),
            'THB'   => __( 'Thai Baht', 'paid-member-subscriptions' ),
            'TRY'   => __( 'Turkish Lira', 'paid-member-subscriptions' )
        );

        return apply_filters( 'pms_currencies', $currencies );

    }


    /**
     * Given a currency code returns a string with the currency symbol as HTML entity
     *
     * @param string $currency_code
     *
     * @return string
     *
     */
    function pms_get_currency_symbol( $currency_code ) {

        $currencies = apply_filters('pms_currency_symbols',
            array(
                'AED' => '&#1583;.&#1573;', // ?
                'AFN' => '&#65;&#102;',
                'ALL' => '&#76;&#101;&#107;',
                'AMD' => '',
                'ANG' => '&#402;',
                'AOA' => '&#75;&#122;', // ?
                'ARS' => '&#36;',
                'AUD' => '&#36;',
                'AWG' => '&#402;',
                'AZN' => '&#1084;&#1072;&#1085;',
                'BAM' => '&#75;&#77;',
                'BBD' => '&#36;',
                'BDT' => '&#2547;', // ?
                'BGN' => '&#1083;&#1074;',
                'BHD' => '.&#1583;.&#1576;', // ?
                'BIF' => '&#70;&#66;&#117;', // ?
                'BMD' => '&#36;',
                'BND' => '&#36;',
                'BOB' => '&#36;&#98;',
                'BRL' => '&#82;&#36;',
                'BSD' => '&#36;',
                'BTN' => '&#78;&#117;&#46;', // ?
                'BWP' => '&#80;',
                'BYR' => '&#112;&#46;',
                'BZD' => '&#66;&#90;&#36;',
                'CAD' => '&#36;',
                'CDF' => '&#70;&#67;',
                'CHF' => '&#67;&#72;&#70;',
                'CLF' => '', // ?
                'CLP' => '&#36;',
                'CNY' => '&#165;',
                'COP' => '&#36;',
                'CRC' => '&#8353;',
                'CUP' => '&#8396;',
                'CVE' => '&#36;', // ?
                'CZK' => '&#75;&#269;',
                'DJF' => '&#70;&#100;&#106;', // ?
                'DKK' => '&#107;&#114;',
                'DOP' => '&#82;&#68;&#36;',
                'DZD' => '&#1583;&#1580;', // ?
                'EGP' => '&#163;',
                'ETB' => '&#66;&#114;',
                'EUR' => '&#8364;',
                'FJD' => '&#36;',
                'FKP' => '&#163;',
                'GBP' => '&#163;',
                'GEL' => '&#4314;', // ?
                'GHS' => '&#162;',
                'GIP' => '&#163;',
                'GMD' => '&#68;', // ?
                'GNF' => '&#70;&#71;', // ?
                'GTQ' => '&#81;',
                'GYD' => '&#36;',
                'HKD' => '&#36;',
                'HNL' => '&#76;',
                'HRK' => '&#107;&#110;',
                'HTG' => '&#71;', // ?
                'HUF' => '&#70;&#116;',
                'IDR' => '&#82;&#112;',
                'ILS' => '&#8362;',
                'INR' => '&#8377;',
                'IQD' => '&#1593;.&#1583;', // ?
                'IRR' => '&#65020;',
                'ISK' => '&#107;&#114;',
                'JEP' => '&#163;',
                'JMD' => '&#74;&#36;',
                'JOD' => '&#74;&#68;', // ?
                'JPY' => '&#165;',
                'KES' => '&#75;&#83;&#104;', // ?
                'KGS' => '&#1083;&#1074;',
                'KHR' => '&#6107;',
                'KMF' => '&#67;&#70;', // ?
                'KPW' => '&#8361;',
                'KRW' => '&#8361;',
                'KWD' => '&#1583;.&#1603;', // ?
                'KYD' => '&#36;',
                'KZT' => '&#1083;&#1074;',
                'LAK' => '&#8365;',
                'LBP' => '&#163;',
                'LKR' => '&#8360;',
                'LRD' => '&#36;',
                'LSL' => '&#76;', // ?
                'LTL' => '&#76;&#116;',
                'LVL' => '&#76;&#115;',
                'LYD' => '&#1604;.&#1583;', // ?
                'MAD' => '&#1583;.&#1605;.', //?
                'MDL' => '&#76;',
                'MGA' => '&#65;&#114;', // ?
                'MKD' => '&#1076;&#1077;&#1085;',
                'MMK' => '&#75;',
                'MNT' => '&#8366;',
                'MOP' => '&#77;&#79;&#80;&#36;', // ?
                'MRO' => '&#85;&#77;', // ?
                'MUR' => '&#8360;', // ?
                'MVR' => '.&#1923;', // ?
                'MWK' => '&#77;&#75;',
                'MXN' => '&#36;',
                'MYR' => '&#82;&#77;',
                'MZN' => '&#77;&#84;',
                'NAD' => '&#36;',
                'NGN' => '&#8358;',
                'NIO' => '&#67;&#36;',
                'NOK' => '&#107;&#114;',
                'NPR' => '&#8360;',
                'NZD' => '&#36;',
                'OMR' => '&#65020;',
                'PAB' => '&#66;&#47;&#46;',
                'PEN' => '&#83;&#47;&#46;',
                'PGK' => '&#75;', // ?
                'PHP' => '&#8369;',
                'PKR' => '&#8360;',
                'PLN' => '&#122;&#322;',
                'PYG' => '&#71;&#115;',
                'QAR' => '&#65020;',
                'RON' => '&#108;&#101;&#105;',
                'RSD' => '&#1044;&#1080;&#1085;&#46;',
                'RUB' => '&#1088;&#1091;&#1073;',
                'RWF' => '&#1585;.&#1587;',
                'SAR' => '&#65020;',
                'SBD' => '&#36;',
                'SCR' => '&#8360;',
                'SDG' => '&#163;', // ?
                'SEK' => '&#107;&#114;',
                'SGD' => '&#36;',
                'SHP' => '&#163;',
                'SLL' => '&#76;&#101;', // ?
                'SOS' => '&#83;',
                'SRD' => '&#36;',
                'STD' => '&#68;&#98;', // ?
                'SVC' => '&#36;',
                'SYP' => '&#163;',
                'SZL' => '&#76;', // ?
                'THB' => '&#3647;',
                'TJS' => '&#84;&#74;&#83;', // ? TJS (guess)
                'TMT' => '&#109;',
                'TND' => '&#1583;.&#1578;',
                'TOP' => '&#84;&#36;',
                'TRY' => '&#8378;', // New Turkey Lira
                'TTD' => '&#36;',
                'TWD' => '&#78;&#84;&#36;',
                'TZS' => '',
                'UAH' => '&#8372;',
                'UGX' => '&#85;&#83;&#104;',
                'USD' => '&#36;',
                'UYU' => '&#36;&#85;',
                'UZS' => '&#1083;&#1074;',
                'VEF' => '&#66;&#115;',
                'VND' => '&#8363;',
                'VUV' => '&#86;&#84;',
                'WST' => '&#87;&#83;&#36;',
                'XAF' => '&#70;&#67;&#70;&#65;',
                'XCD' => '&#36;',
                'XDR' => '',
                'XOF' => '',
                'XPF' => '&#70;',
                'YER' => '&#65020;',
                'ZAR' => '&#82;',
                'ZMK' => '&#90;&#75;', // ?
                'ZWL' => '&#90;&#36;',
            )
        );

        $currency_symbol = ( isset( $currencies[$currency_code] ) ? $currencies[$currency_code] : $currency_code );

        return $currency_symbol;

    }


    /**
     * Function that returns an array with countries using country codes as keys
     *
     * @return array
     *
     */
    function pms_get_countries() {

        $country_array = apply_filters( 'pms_get_countries',
            array(
                ''	 => '',
                'AF' => __( 'Afghanistan', 'paid-member-subscriptions' ),
                'AX' => __( 'Aland Islands', 'paid-member-subscriptions' ),
                'AL' => __( 'Albania', 'paid-member-subscriptions' ),
                'DZ' => __( 'Algeria', 'paid-member-subscriptions' ),
                'AS' => __( 'American Samoa', 'paid-member-subscriptions' ),
                'AD' => __( 'Andorra', 'paid-member-subscriptions' ),
                'AO' => __( 'Angola', 'paid-member-subscriptions' ),
                'AI' => __( 'Anguilla', 'paid-member-subscriptions' ),
                'AQ' => __( 'Antarctica', 'paid-member-subscriptions' ),
                'AG' => __( 'Antigua and Barbuda', 'paid-member-subscriptions' ),
                'AR' => __( 'Argentina', 'paid-member-subscriptions' ),
                'AM' => __( 'Armenia', 'paid-member-subscriptions' ),
                'AW' => __( 'Aruba', 'paid-member-subscriptions' ),
                'AU' => __( 'Australia', 'paid-member-subscriptions' ),
                'AT' => __( 'Austria', 'paid-member-subscriptions' ),
                'AZ' => __( 'Azerbaijan', 'paid-member-subscriptions' ),
                'BS' => __( 'Bahamas', 'paid-member-subscriptions' ),
                'BH' => __( 'Bahrain', 'paid-member-subscriptions' ),
                'BD' => __( 'Bangladesh', 'paid-member-subscriptions' ),
                'BB' => __( 'Barbados', 'paid-member-subscriptions' ),
                'BY' => __( 'Belarus', 'paid-member-subscriptions' ),
                'BE' => __( 'Belgium', 'paid-member-subscriptions' ),
                'BZ' => __( 'Belize', 'paid-member-subscriptions' ),
                'BJ' => __( 'Benin', 'paid-member-subscriptions' ),
                'BM' => __( 'Bermuda', 'paid-member-subscriptions' ),
                'BT' => __( 'Bhutan', 'paid-member-subscriptions' ),
                'BO' => __( 'Bolivia', 'paid-member-subscriptions' ),
                'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'paid-member-subscriptions' ),
                'BA' => __( 'Bosnia and Herzegovina', 'paid-member-subscriptions' ),
                'BW' => __( 'Botswana', 'paid-member-subscriptions' ),
                'BV' => __( 'Bouvet Island', 'paid-member-subscriptions' ),
                'BR' => __( 'Brazil', 'paid-member-subscriptions' ),
                'IO' => __( 'British Indian Ocean Territory', 'paid-member-subscriptions' ),
                'VG' => __( 'British Virgin Islands', 'paid-member-subscriptions' ),
                'BN' => __( 'Brunei', 'paid-member-subscriptions' ),
                'BG' => __( 'Bulgaria', 'paid-member-subscriptions' ),
                'BF' => __( 'Burkina Faso', 'paid-member-subscriptions' ),
                'BI' => __( 'Burundi', 'paid-member-subscriptions' ),
                'KH' => __( 'Cambodia', 'paid-member-subscriptions' ),
                'CM' => __( 'Cameroon', 'paid-member-subscriptions' ),
                'CA' => __( 'Canada', 'paid-member-subscriptions' ),
                'CV' => __( 'Cape Verde', 'paid-member-subscriptions' ),
                'KY' => __( 'Cayman Islands', 'paid-member-subscriptions' ),
                'CF' => __( 'Central African Republic', 'paid-member-subscriptions' ),
                'TD' => __( 'Chad', 'paid-member-subscriptions' ),
                'CL' => __( 'Chile', 'paid-member-subscriptions' ),
                'CN' => __( 'China', 'paid-member-subscriptions' ),
                'CX' => __( 'Christmas Island', 'paid-member-subscriptions' ),
                'CC' => __( 'Cocos Islands', 'paid-member-subscriptions' ),
                'CO' => __( 'Colombia', 'paid-member-subscriptions' ),
                'KM' => __( 'Comoros', 'paid-member-subscriptions' ),
                'CK' => __( 'Cook Islands', 'paid-member-subscriptions' ),
                'CR' => __( 'Costa Rica', 'paid-member-subscriptions' ),
                'HR' => __( 'Croatia', 'paid-member-subscriptions' ),
                'CU' => __( 'Cuba', 'paid-member-subscriptions' ),
                'CW' => __( 'Curacao', 'paid-member-subscriptions' ),
                'CY' => __( 'Cyprus', 'paid-member-subscriptions' ),
                'CZ' => __( 'Czech Republic', 'paid-member-subscriptions' ),
                'CD' => __( 'Democratic Republic of the Congo', 'paid-member-subscriptions' ),
                'DK' => __( 'Denmark', 'paid-member-subscriptions' ),
                'DJ' => __( 'Djibouti', 'paid-member-subscriptions' ),
                'DM' => __( 'Dominica', 'paid-member-subscriptions' ),
                'DO' => __( 'Dominican Republic', 'paid-member-subscriptions' ),
                'TL' => __( 'East Timor', 'paid-member-subscriptions' ),
                'EC' => __( 'Ecuador', 'paid-member-subscriptions' ),
                'EG' => __( 'Egypt', 'paid-member-subscriptions' ),
                'SV' => __( 'El Salvador', 'paid-member-subscriptions' ),
                'GQ' => __( 'Equatorial Guinea', 'paid-member-subscriptions' ),
                'ER' => __( 'Eritrea', 'paid-member-subscriptions' ),
                'EE' => __( 'Estonia', 'paid-member-subscriptions' ),
                'ET' => __( 'Ethiopia', 'paid-member-subscriptions' ),
                'FK' => __( 'Falkland Islands', 'paid-member-subscriptions' ),
                'FO' => __( 'Faroe Islands', 'paid-member-subscriptions' ),
                'FJ' => __( 'Fiji', 'paid-member-subscriptions' ),
                'FI' => __( 'Finland', 'paid-member-subscriptions' ),
                'FR' => __( 'France', 'paid-member-subscriptions' ),
                'GF' => __( 'French Guiana', 'paid-member-subscriptions' ),
                'PF' => __( 'French Polynesia', 'paid-member-subscriptions' ),
                'TF' => __( 'French Southern Territories', 'paid-member-subscriptions' ),
                'GA' => __( 'Gabon', 'paid-member-subscriptions' ),
                'GM' => __( 'Gambia', 'paid-member-subscriptions' ),
                'GE' => __( 'Georgia', 'paid-member-subscriptions' ),
                'DE' => __( 'Germany', 'paid-member-subscriptions' ),
                'GH' => __( 'Ghana', 'paid-member-subscriptions' ),
                'GI' => __( 'Gibraltar', 'paid-member-subscriptions' ),
                'GR' => __( 'Greece', 'paid-member-subscriptions' ),
                'GL' => __( 'Greenland', 'paid-member-subscriptions' ),
                'GD' => __( 'Grenada', 'paid-member-subscriptions' ),
                'GP' => __( 'Guadeloupe', 'paid-member-subscriptions' ),
                'GU' => __( 'Guam', 'paid-member-subscriptions' ),
                'GT' => __( 'Guatemala', 'paid-member-subscriptions' ),
                'GG' => __( 'Guernsey', 'paid-member-subscriptions' ),
                'GN' => __( 'Guinea', 'paid-member-subscriptions' ),
                'GW' => __( 'Guinea-Bissau', 'paid-member-subscriptions' ),
                'GY' => __( 'Guyana', 'paid-member-subscriptions' ),
                'HT' => __( 'Haiti', 'paid-member-subscriptions' ),
                'HM' => __( 'Heard Island and McDonald Islands', 'paid-member-subscriptions' ),
                'HN' => __( 'Honduras', 'paid-member-subscriptions' ),
                'HK' => __( 'Hong Kong', 'paid-member-subscriptions' ),
                'HU' => __( 'Hungary', 'paid-member-subscriptions' ),
                'IS' => __( 'Iceland', 'paid-member-subscriptions' ),
                'IN' => __( 'India', 'paid-member-subscriptions' ),
                'ID' => __( 'Indonesia', 'paid-member-subscriptions' ),
                'IR' => __( 'Iran', 'paid-member-subscriptions' ),
                'IQ' => __( 'Iraq', 'paid-member-subscriptions' ),
                'IE' => __( 'Ireland', 'paid-member-subscriptions' ),
                'IM' => __( 'Isle of Man', 'paid-member-subscriptions' ),
                'IL' => __( 'Israel', 'paid-member-subscriptions' ),
                'IT' => __( 'Italy', 'paid-member-subscriptions' ),
                'CI' => __( 'Ivory Coast', 'paid-member-subscriptions' ),
                'JM' => __( 'Jamaica', 'paid-member-subscriptions' ),
                'JP' => __( 'Japan', 'paid-member-subscriptions' ),
                'JE' => __( 'Jersey', 'paid-member-subscriptions' ),
                'JO' => __( 'Jordan', 'paid-member-subscriptions' ),
                'KZ' => __( 'Kazakhstan', 'paid-member-subscriptions' ),
                'KE' => __( 'Kenya', 'paid-member-subscriptions' ),
                'KI' => __( 'Kiribati', 'paid-member-subscriptions' ),
                'XK' => __( 'Kosovo', 'paid-member-subscriptions' ),
                'KW' => __( 'Kuwait', 'paid-member-subscriptions' ),
                'KG' => __( 'Kyrgyzstan', 'paid-member-subscriptions' ),
                'LA' => __( 'Laos', 'paid-member-subscriptions' ),
                'LV' => __( 'Latvia', 'paid-member-subscriptions' ),
                'LB' => __( 'Lebanon', 'paid-member-subscriptions' ),
                'LS' => __( 'Lesotho', 'paid-member-subscriptions' ),
                'LR' => __( 'Liberia', 'paid-member-subscriptions' ),
                'LY' => __( 'Libya', 'paid-member-subscriptions' ),
                'LI' => __( 'Liechtenstein', 'paid-member-subscriptions' ),
                'LT' => __( 'Lithuania', 'paid-member-subscriptions' ),
                'LU' => __( 'Luxembourg', 'paid-member-subscriptions' ),
                'MO' => __( 'Macao', 'paid-member-subscriptions' ),
                'MK' => __( 'North Macedonia', 'paid-member-subscriptions' ),
                'MG' => __( 'Madagascar', 'paid-member-subscriptions' ),
                'MW' => __( 'Malawi', 'paid-member-subscriptions' ),
                'MY' => __( 'Malaysia', 'paid-member-subscriptions' ),
                'MV' => __( 'Maldives', 'paid-member-subscriptions' ),
                'ML' => __( 'Mali', 'paid-member-subscriptions' ),
                'MT' => __( 'Malta', 'paid-member-subscriptions' ),
                'MH' => __( 'Marshall Islands', 'paid-member-subscriptions' ),
                'MQ' => __( 'Martinique', 'paid-member-subscriptions' ),
                'MR' => __( 'Mauritania', 'paid-member-subscriptions' ),
                'MU' => __( 'Mauritius', 'paid-member-subscriptions' ),
                'YT' => __( 'Mayotte', 'paid-member-subscriptions' ),
                'MX' => __( 'Mexico', 'paid-member-subscriptions' ),
                'FM' => __( 'Micronesia', 'paid-member-subscriptions' ),
                'MD' => __( 'Moldova', 'paid-member-subscriptions' ),
                'MC' => __( 'Monaco', 'paid-member-subscriptions' ),
                'MN' => __( 'Mongolia', 'paid-member-subscriptions' ),
                'ME' => __( 'Montenegro', 'paid-member-subscriptions' ),
                'MS' => __( 'Montserrat', 'paid-member-subscriptions' ),
                'MA' => __( 'Morocco', 'paid-member-subscriptions' ),
                'MZ' => __( 'Mozambique', 'paid-member-subscriptions' ),
                'MM' => __( 'Myanmar', 'paid-member-subscriptions' ),
                'NA' => __( 'Namibia', 'paid-member-subscriptions' ),
                'NR' => __( 'Nauru', 'paid-member-subscriptions' ),
                'NP' => __( 'Nepal', 'paid-member-subscriptions' ),
                'NL' => __( 'Netherlands', 'paid-member-subscriptions' ),
                'NC' => __( 'New Caledonia', 'paid-member-subscriptions' ),
                'NZ' => __( 'New Zealand', 'paid-member-subscriptions' ),
                'NI' => __( 'Nicaragua', 'paid-member-subscriptions' ),
                'NE' => __( 'Niger', 'paid-member-subscriptions' ),
                'NG' => __( 'Nigeria', 'paid-member-subscriptions' ),
                'NU' => __( 'Niue', 'paid-member-subscriptions' ),
                'NF' => __( 'Norfolk Island', 'paid-member-subscriptions' ),
                'KP' => __( 'North Korea', 'paid-member-subscriptions' ),
                'MP' => __( 'Northern Mariana Islands', 'paid-member-subscriptions' ),
                'NO' => __( 'Norway', 'paid-member-subscriptions' ),
                'OM' => __( 'Oman', 'paid-member-subscriptions' ),
                'PK' => __( 'Pakistan', 'paid-member-subscriptions' ),
                'PW' => __( 'Palau', 'paid-member-subscriptions' ),
                'PS' => __( 'Palestinian Territory', 'paid-member-subscriptions' ),
                'PA' => __( 'Panama', 'paid-member-subscriptions' ),
                'PG' => __( 'Papua New Guinea', 'paid-member-subscriptions' ),
                'PY' => __( 'Paraguay', 'paid-member-subscriptions' ),
                'PE' => __( 'Peru', 'paid-member-subscriptions' ),
                'PH' => __( 'Philippines', 'paid-member-subscriptions' ),
                'PN' => __( 'Pitcairn', 'paid-member-subscriptions' ),
                'PL' => __( 'Poland', 'paid-member-subscriptions' ),
                'PT' => __( 'Portugal', 'paid-member-subscriptions' ),
                'PR' => __( 'Puerto Rico', 'paid-member-subscriptions' ),
                'QA' => __( 'Qatar', 'paid-member-subscriptions' ),
                'CG' => __( 'Republic of the Congo', 'paid-member-subscriptions' ),
                'RE' => __( 'Reunion', 'paid-member-subscriptions' ),
                'RO' => __( 'Romania', 'paid-member-subscriptions' ),
                'RU' => __( 'Russia', 'paid-member-subscriptions' ),
                'RW' => __( 'Rwanda', 'paid-member-subscriptions' ),
                'BL' => __( 'Saint Barthelemy', 'paid-member-subscriptions' ),
                'SH' => __( 'Saint Helena', 'paid-member-subscriptions' ),
                'KN' => __( 'Saint Kitts and Nevis', 'paid-member-subscriptions' ),
                'LC' => __( 'Saint Lucia', 'paid-member-subscriptions' ),
                'MF' => __( 'Saint Martin', 'paid-member-subscriptions' ),
                'PM' => __( 'Saint Pierre and Miquelon', 'paid-member-subscriptions' ),
                'VC' => __( 'Saint Vincent and the Grenadines', 'paid-member-subscriptions' ),
                'WS' => __( 'Samoa', 'paid-member-subscriptions' ),
                'SM' => __( 'San Marino', 'paid-member-subscriptions' ),
                'ST' => __( 'Sao Tome and Principe', 'paid-member-subscriptions' ),
                'SA' => __( 'Saudi Arabia', 'paid-member-subscriptions' ),
                'SN' => __( 'Senegal', 'paid-member-subscriptions' ),
                'RS' => __( 'Serbia', 'paid-member-subscriptions' ),
                'SC' => __( 'Seychelles', 'paid-member-subscriptions' ),
                'SL' => __( 'Sierra Leone', 'paid-member-subscriptions' ),
                'SG' => __( 'Singapore', 'paid-member-subscriptions' ),
                'SX' => __( 'Sint Maarten', 'paid-member-subscriptions' ),
                'SK' => __( 'Slovakia', 'paid-member-subscriptions' ),
                'SI' => __( 'Slovenia', 'paid-member-subscriptions' ),
                'SB' => __( 'Solomon Islands', 'paid-member-subscriptions' ),
                'SO' => __( 'Somalia', 'paid-member-subscriptions' ),
                'ZA' => __( 'South Africa', 'paid-member-subscriptions' ),
                'GS' => __( 'South Georgia and the South Sandwich Islands', 'paid-member-subscriptions' ),
                'KR' => __( 'South Korea', 'paid-member-subscriptions' ),
                'SS' => __( 'South Sudan', 'paid-member-subscriptions' ),
                'ES' => __( 'Spain', 'paid-member-subscriptions' ),
                'LK' => __( 'Sri Lanka', 'paid-member-subscriptions' ),
                'SD' => __( 'Sudan', 'paid-member-subscriptions' ),
                'SR' => __( 'Suriname', 'paid-member-subscriptions' ),
                'SJ' => __( 'Svalbard and Jan Mayen', 'paid-member-subscriptions' ),
                'SZ' => __( 'Swaziland', 'paid-member-subscriptions' ),
                'SE' => __( 'Sweden', 'paid-member-subscriptions' ),
                'CH' => __( 'Switzerland', 'paid-member-subscriptions' ),
                'SY' => __( 'Syria', 'paid-member-subscriptions' ),
                'TW' => __( 'Taiwan', 'paid-member-subscriptions' ),
                'TJ' => __( 'Tajikistan', 'paid-member-subscriptions' ),
                'TZ' => __( 'Tanzania', 'paid-member-subscriptions' ),
                'TH' => __( 'Thailand', 'paid-member-subscriptions' ),
                'TG' => __( 'Togo', 'paid-member-subscriptions' ),
                'TK' => __( 'Tokelau', 'paid-member-subscriptions' ),
                'TO' => __( 'Tonga', 'paid-member-subscriptions' ),
                'TT' => __( 'Trinidad and Tobago', 'paid-member-subscriptions' ),
                'TN' => __( 'Tunisia', 'paid-member-subscriptions' ),
                'TR' => __( 'Turkey', 'paid-member-subscriptions' ),
                'TM' => __( 'Turkmenistan', 'paid-member-subscriptions' ),
                'TC' => __( 'Turks and Caicos Islands', 'paid-member-subscriptions' ),
                'TV' => __( 'Tuvalu', 'paid-member-subscriptions' ),
                'VI' => __( 'U.S. Virgin Islands', 'paid-member-subscriptions' ),
                'UG' => __( 'Uganda', 'paid-member-subscriptions' ),
                'UA' => __( 'Ukraine', 'paid-member-subscriptions' ),
                'AE' => __( 'United Arab Emirates', 'paid-member-subscriptions' ),
                'GB' => __( 'United Kingdom', 'paid-member-subscriptions' ),
                'US' => __( 'United States', 'paid-member-subscriptions' ),
                'UM' => __( 'United States Minor Outlying Islands', 'paid-member-subscriptions' ),
                'UY' => __( 'Uruguay', 'paid-member-subscriptions' ),
                'UZ' => __( 'Uzbekistan', 'paid-member-subscriptions' ),
                'VU' => __( 'Vanuatu', 'paid-member-subscriptions' ),
                'VA' => __( 'Vatican', 'paid-member-subscriptions' ),
                'VE' => __( 'Venezuela', 'paid-member-subscriptions' ),
                'VN' => __( 'Vietnam', 'paid-member-subscriptions' ),
                'WF' => __( 'Wallis and Futuna', 'paid-member-subscriptions' ),
                'EH' => __( 'Western Sahara', 'paid-member-subscriptions' ),
                'YE' => __( 'Yemen', 'paid-member-subscriptions' ),
                'ZM' => __( 'Zambia', 'paid-member-subscriptions' ),
                'ZW' => __( 'Zimbabwe', 'paid-member-subscriptions' ),
            )
        );

        return $country_array;
    }



    /**
     * Function that returns the current user id or the current user that is edited in front-end
     * edit profile when an admin is editing
     *
     * @return int
     *
     */
    function pms_get_current_user_id() {
        if( isset( $_GET['edit_user'] ) && !empty( $_GET['edit_user'] ) && current_user_can('edit_users') )
            return (int)$_GET['edit_user'];
        else
            return get_current_user_id();
    }


    /**
     * Get currency saved in the settings page
     *
     * @return string
     *
     */
    function pms_get_active_currency() {

        $settings = get_option( 'pms_payments_settings' );

        return !empty( $settings['currency'] ) ? $settings['currency'] : 'USD';

    }


    /*
     * Wrapper function for WordPress's default paginate links
     *
     */
    function pms_paginate_links( $args = array() ) {

        if( $args['total'] == 1 )
            return '';

        $output = '<p ' . ( !empty( $args['id'] ) ? 'id="' . esc_attr( $args['id'] ) . '"' : '' ) . ' class="pms-pagination">';
            $output .= paginate_links( $args );
        $output .= '</p>';

        return $output;

    }


    /*
     * Modify the logout url to redirect to current page if the user is in the front-end
     * and logs out
     *
     */
    function pms_logout_redirect_url( $logout_url, $redirect ) {

        $current_page = pms_get_current_page_url();

        // Do nothing if there's already a redirect in place
        if( !empty( $redirect ) )
            return $logout_url;

        // Do nothing if we're in an admin page
        if( strpos( $current_page, 'wp-admin' ) !== false )
            return $logout_url;

        $logout_url = add_query_arg( array( 'redirect_to' => urlencode( esc_url( $current_page ) ) ), $logout_url );

        return $logout_url;

    }
    add_filter( 'logout_url', 'pms_logout_redirect_url', 10, 2 );


    /**
     * Function that moves corresponding discount codes for payments (previously saved in "pms_payment_id_discount_code" option - until version 1.1.6) to the PMS Payments table under the "discount_code" column
     *
     */
    function pms_move_previous_discount_codes_in_payments_table(){

        $payment_discount_array = get_option( 'pms_payment_id_discount_code', array() );

        if ( !empty($payment_discount_array) ) {

            foreach ($payment_discount_array as $payment_id => $discount_code) {
                if (class_exists('PMS_Payment')) {
                    $payment = new PMS_Payment($payment_id);
                    $payment->update(array('discount_code' => $discount_code));
                }
            }

            delete_option('pms_payment_id_discount_code');
        }

    }
    add_action( 'pms_update_check' , 'pms_move_previous_discount_codes_in_payments_table');

    /**
     * Returns a formatted string for the price and currency
     *
     * @param int $price
     * @param string $currency
     * @param array $args
     *
     * @return string
     *
     */
    function pms_format_price( $price = 0, $currency = '', $args = array() ) {

        $currency = pms_get_currency_symbol( $currency );

        //format number based on current locale with 2 decimals
        $price = number_format_i18n( $price, 2 );

        //remove any decimal 0s that are irrelevant; will match: x,00, x.00 and also x,10 or x.10
        if( apply_filters( 'pms_format_price_trim_zeroes', true ) )
            $price = preg_replace('/(\.|\,)?0*$/', '', $price);

        //filter clean price that can be altered, no HTML
        $price = apply_filters( 'pms_format_price_before_html', $price, $currency, $args );

        $price    = ( !empty( $args['before_price'] ) && !empty( $args['after_price'] ) ? $args['before_price'] . $price . $args['after_price'] : $price );
        $currency = ( !empty( $args['before_currency'] ) && !empty( $args['after_currency'] ) ? $args['before_currency'] . $currency . $args['after_currency'] : $currency );

        $settings = get_option( 'pms_payments_settings' );

        $output = ( !isset( $settings['currency_position'] ) || ( isset( $settings['currency_position'] ) && $settings['currency_position'] == 'after' ) ? $price . $currency : $currency . $price );

        return apply_filters( 'pms_format_price', $output, $price, $currency, $args );

    }

    /**
     * Returns the Register Success Page from the settings.
     *
     * @since 1.7.8
     * @return string     Either page URL or empty string.
     */
    function pms_get_register_success_url() {

        $settings = get_option( 'pms_general_settings' );

        if ( isset( $settings['register_success_page'] ) && $settings['register_success_page'] != -1 )
            return get_permalink( $settings['register_success_page'] );

        return '';
    }


    /**
     * Returns the Currency Position setting.
     *
     * @since 1.7.8
     * @return string   Currency position.
     */
    function pms_get_currency_position() {

        $settings = get_option( 'pms_payments_settings' );

        if ( isset( $settings['currency_position'] ) )
            return $settings['currency_position'];

        return 'after';
    }

    /**
     * Adds a dismissable admin notice on all WordPress pages and a non-dismissable admin notice on PMS's
     * Settings page requiring SSL to be enabled in order for all functionality to be available
     *
     */
    if( ! pms_is_https() ) {

        $message = __( 'Your website doesn\'t seem to have SSL enabled. Some functionality will not work without a valid SSL certificate. Please enable SSL and ensure your server has a valid SSL certificate.', 'paid-member-subscriptions' );

        if( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'pms-settings-page' ) {

            new PMS_Add_General_Notices( 'pms_force_website_https_on_pms_pages',
                $message,
                'notice-warning');

        } else {

            new PMS_Add_General_Notices( 'pms_force_website_https',
                sprintf( $message . __( ' %1$sDismiss%2$s', 'paid-member-subscriptions'), "<a href='" . esc_url(add_query_arg('pms_force_website_https_dismiss_notification', '0')) . "'>", "</a>"),
                'notice-warning');

        }

    }

    /**
     * Add a notice if the serial number is expired
     *
     */
    $pms_serial_number_status = pms_get_serial_number_status();
     if ( $pms_serial_number_status == 'expired' ) {

         $pms_expired_message = sprintf( __( 'Your <strong>Paid Member Subscriptions</strong> serial number has <strong>expired</strong>. <a class="button-primary" href="%s">Renew now</a>', 'paid-member-subscriptions' ), esc_url( 'https://www.cozmoslabs.com/account/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=PMS&utm_content=add-on-page-expired-serial-number-notification' ) );

         /* if we are on our own plugin pages make the expired license notification non dismissible */
         $pms_notifications_instance = PMS_Plugin_Notifications::get_instance();
         if( !$pms_notifications_instance->is_plugin_page() ) {//add the dismiss button only on other pages in admin
             $pms_expired_message .= sprintf(__(' %1$sDismiss%2$s', 'paid-member-subscriptions'), "<a class='dismiss-right' href='" . esc_url(add_query_arg('pms_expired_licence_dismiss_notification', '0')) . "'>", "</a>");
             $pms_force_show = false;
         }
         else{
             $pms_force_show = true;//sets the forceShow parameter of PMS_Add_General_Notices to true so we don't take into consideration the dismiss user meta
         }

         new PMS_Add_General_Notices( 'pms_expired_licence',
             $pms_expired_message,
             'error',
             '',
             '',
             $pms_force_show );
     }
     elseif ( strpos( $pms_serial_number_status, 'aboutToExpire' ) !== false ){
         $about_to_expire_status = explode('#', $pms_serial_number_status);//format of about to expire status is like aboutToExpire#19 of April 2019
         if( is_array( $about_to_expire_status ) ){
             new PMS_Add_General_Notices( 'pms_about_to_expire_licence',
                 sprintf( __( 'Your <strong>Paid Member Subscriptions</strong> serial number will expire on <strong>%s</strong>.<br/>Please Renew Your Licence to continue receiving access to product downloads, automatic updates and support.  <a class="button-primary" href="%s">Renew now</a>', 'paid-member-subscriptions' ), $about_to_expire_status[1], esc_url( 'https://www.cozmoslabs.com/account/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=PMS&utm_content=add-on-page-expired-serial-number-notification' ) ).
                 sprintf( __( ' %1$sDismiss%2$s', 'paid-member-subscriptions'), "<a class='dismiss-right' href='" . esc_url(add_query_arg('pms_about_to_expire_licence_dismiss_notification', '0')) . "'>", "</a>"),
                 'notice-warning' );
         }
     }


     /**
      * Add a notice if a recurring PayPal gateway is active but API credentials are missing
      *
      */
      add_action( 'plugins_loaded', 'pms_general_notice_plugins_loaded' );
      function pms_general_notice_plugins_loaded() {

          //check if related gateways are active
          $are_active = array_intersect( array( 'paypal_standard', 'paypal_pro', 'paypal_express' ), pms_get_active_payment_gateways() );

          if ( function_exists( 'pms_get_paypal_api_credentials' ) && pms_get_paypal_api_credentials() === false && !empty( $are_active ) ) {

              $message = sprintf( __( 'Your <strong>PayPal API credentials</strong> are missing. In order to make payments you will need to add your API credentials %1$s here %2$s.', 'paid-member-subscriptions' ), '<a href="' . admin_url( 'admin.php?page=pms-settings-page&tab=payments' ) .'">', '</a>' );

              if( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'pms-settings-page' ) {

                  new PMS_Add_General_Notices( 'pms_paypal_api_credentials',
                      '<p>' . $message . '</p>',
                      'notice-warning');

              } else {

                  new PMS_Add_General_Notices( 'pms_paypal_api_credentials',
                      sprintf( '<p>' . $message . __( ' %1$sDismiss%2$s', 'paid-member-subscriptions'), "<a href='" . esc_url( add_query_arg( 'pms_paypal_api_credentials_dismiss_notification', '0' ) ) . "'>", "</a>" ) . '</p>',
                      'notice-warning');

              }

          }
      }
