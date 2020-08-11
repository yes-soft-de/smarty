<?php

/**
 * Returns the HTML of the reCaptcha field
 *
 * @param string $form_location
 *
 * @return string
 *
 */
function pms_recaptcha_get_field_output( $form_location = 'register' ) {

    global $pms_print_scripts_recaptcha;

    $pms_print_scripts_recaptcha = true;

    // Get PMS's settings
    $settings = pms_recaptcha_get_settings();

    // Exit if this form does not have reCaptcha
    if( empty( $settings['display_form'] ) || !in_array( $form_location, $settings['display_form'] ) )
        return '';

    $field_errors = pms_errors()->get_error_messages( 'recaptcha-' . $form_location );

    $output = '<div id="pms-recaptcha-' . $form_location . '-wrapper" class="pms-field">';
        $output .= '<div id="pms-recaptcha-' . $form_location . '" class="pms-recaptcha g-recaptcha" data-sitekey="' . ( !empty( $settings['site_key'] ) ? $settings['site_key'] : '' ) . '" data-theme="' . apply_filters( 'pms_recaptcha_theme', 'light' ) . '" data-size="' . apply_filters( 'pms_recaptcha_size', 'normal' ) . '"></div>';

        if( false === strpos( $form_location, 'wp_' ) )
            $output .= pms_display_field_errors( $field_errors, true );

    $output .= '</div>';

    return $output;

}


/**
 * Outputs the reCaptcha frame at the bottom of the Register form
 *
 * @param array $shortcode_atts
 *
 */
function pms_recaptcha_field_register_form_bottom( $shortcode_atts ) {

    echo pms_recaptcha_get_field_output( 'register' );

}
add_action( 'pms_register_form_bottom', 'pms_recaptcha_field_register_form_bottom', 100 );


/**
 * Outputs the reCaptcha frame just beneath the password field of the Login form
 *
 * @param string $string
 * @param array $args
 *
 */
function pms_recaptcha_field_login_form_middle( $string, $args ) {

    if( $args['form_id'] != 'pms_login' )
        return $string;

    $string .= pms_recaptcha_get_field_output( 'login' );

    return $string;
}
add_filter( 'login_form_middle', 'pms_recaptcha_field_login_form_middle', 9, 2 );


/*
 * Outputs the reCaptcha frame at the bottom of the Recover Password form
 *
 */
function pms_recaptcha_field_recover_password_form_bottom() {

    echo pms_recaptcha_get_field_output( 'recover_password' );

}
add_action( 'pms_recover_password_form_bottom', 'pms_recaptcha_field_recover_password_form_bottom', 100 );


/**
 * Outputs the reCaptcha frame at the bottom of the WordPress default register form
 *
 */
function pms_recaptcha_field_default_wp_register_form() {

    echo '<div style="margin-left: -15px; margin-bottom: 15px;">';
        echo pms_recaptcha_get_field_output( 'default_wp_register' );
    echo '</div>';

}
add_action( 'register_form', 'pms_recaptcha_field_default_wp_register_form' );


/**
 * Outputs the reCaptcha frame at the bottom of the WordPress default login form
 *
 */
function pms_recaptcha_field_default_wp_login_form() {

    echo '<div style="margin-left: -15px; margin-bottom: 15px;">';
        echo pms_recaptcha_get_field_output( 'default_wp_login' );
    echo '</div>';

}
add_action( 'login_form', 'pms_recaptcha_field_default_wp_login_form' );


/**
 * Outputs the reCaptcha frame at the bottom of the WordPress default register form
 *
 */
function pms_recaptcha_field_default_wp_recover_password_form() {

    echo '<div style="margin-left: -15px; margin-bottom: 15px;">';
        echo pms_recaptcha_get_field_output( 'default_wp_recover_password' );
    echo '</div>';

}
add_action( 'lostpassword_form', 'pms_recaptcha_field_default_wp_recover_password_form' );

/**
 * Retrieve settings
 */
function pms_recaptcha_get_settings(){

    $settings = get_option( 'pms_misc_settings', array() );

    return isset( $settings['recaptcha'] ) ? $settings['recaptcha'] : array();

}
