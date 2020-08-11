<?php

/**
 * Validates the field with Google
 *
 * @param string $form_location
 *
 * @return void
 *
 */
function pms_recaptcha_field_validate( $form_location = 'register' ) {

    $settings = pms_recaptcha_get_settings();

    // Exit if this form does not have reCaptcha
    if( empty( $settings['display_form'] ) || ! in_array( $form_location, $settings['display_form'] ) )
        return true;


    $post_data = $_POST;

    // Verify that the user has completed the reCaptcha
    if( !isset( $post_data['g-recaptcha-response'] ) || empty( $post_data['g-recaptcha-response'] ) ) {
        pms_errors()->add( 'recaptcha-' . $form_location, __( 'Please complete the reCaptcha.', 'paid-member-subscriptions' ) );
        return false;
    }

    // Connect to Google to check if the response is valid
    $response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify',
        array(
            'timeout' => 15,
            'body' => array(
                'secret'    => ( !empty( $settings['secret_key'] ) ? $settings['secret_key'] : '' ),
                'response'  => $post_data['g-recaptcha-response'],
                'remoteip'  => pms_get_user_ip_address()
            )
        )
    );


    $has_error = false;

    if( wp_remote_retrieve_response_code( $response ) === 200 ) {

        $body = json_decode( wp_remote_retrieve_body( $response ), ARRAY_A );

        if( empty( $body['success'] ) && $body['success'] != true )
            $has_error = true;

    } else
        $has_error = true;


    // Add errors if something went wrong
    if( $has_error )
        pms_errors()->add( 'recaptcha-' . $form_location, __( 'Could not validate the reCaptcha. Please complete it again.', 'paid-member-subscriptions' ) );

    return ! $has_error;

}


/*
 * Validates the reCaptcha on the different form fields form
 *
 */
function pms_recaptcha_field_validate_forms() {

    switch( current_filter() ) {

        case "pms_register_form_validation";
            $form_location = 'register';
            break;

        case "pms_recover_password_form_validation";
            $form_location = 'recover_password';
            break;

        default:
            return;

    }

    pms_recaptcha_field_validate( $form_location );

}
add_action( 'pms_register_form_validation', 'pms_recaptcha_field_validate_forms' );
add_action( 'pms_recover_password_form_validation', 'pms_recaptcha_field_validate_forms' );


/**
 * Validates the reCaptcha on login forms
 * Handles validations for both the default WP and PMS custom login forms
 *
 * @param WP_User|WP_Error $user
 *
 */
function pms_recaptcha_field_validate_form_login( $user ) {

    if( is_wp_error( $user ) )
        return $user;

    if( isset( $_POST['wp-submit'] ) && !isset( $_POST['wppb-login'] ) )
        $login_form_location = 'default_wp_login';

    if( isset( $_POST['pms_login'] ) && $_POST['pms_login'] == 1 )
        $login_form_location = 'login';

    if( isset( $login_form_location ) ) {

        $validated = pms_recaptcha_field_validate( $login_form_location );

        if( ! $validated ) {

            $user = new WP_Error( 'pms-recaptcha-' . $login_form_location, '<strong>' . __('ERROR', 'paid-member-subscriptions') . '</strong>: ' . pms_errors()->get_error_message( 'recaptcha-' . $login_form_location ) );

        }

    }

    return $user;

}
add_filter( 'authenticate', 'pms_recaptcha_field_validate_form_login', 25 );


/**
 * Validates the reCaptcha on the default WP register form
 *
 * @param WP_Error $errors
 *
 */
function pms_recaptcha_field_validate_default_wp_register( $errors ) {

    if( empty( $_POST['wp-submit'] ) )
        return $errors;

    $validated = pms_recaptcha_field_validate( 'default_wp_register' );

    if( ! $validated ) {

        $errors->add( 'recaptcha-default_wp_register', '<strong>' . __('ERROR', 'paid-member-subscriptions') . '</strong>: ' . pms_errors()->get_error_message( 'recaptcha-default_wp_register' ) );

    }

    return $errors;

}
add_filter( 'registration_errors', 'pms_recaptcha_field_validate_default_wp_register' );


/**
 * Validates the reCaptcha on the default WP lost password form
 *
 */
function pms_recaptcha_field_validate_default_wp_recover_password_form(){

    if( empty( $_REQUEST['user_login'] ) )
        return;

    $validated = pms_recaptcha_field_validate( 'default_wp_recover_password' );

    if( ! $validated ) {

        wp_die( pms_errors()->get_error_message( 'recaptcha-default_wp_recover_password' ) . '<br />' . __( "Click the BACK button on your browser, and try again.", 'paid-member-subscriptions' ) ) ;

    }

}
add_action('lostpassword_post','pms_recaptcha_field_validate_default_wp_recover_password_form');
