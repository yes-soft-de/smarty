<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Verifies whether the current page communicates through HTTPS
 *
 * @return bool
 *
 */
function pms_is_https() {

    $is_secure = false;

    if ( isset( $_SERVER['HTTPS'] ) && 'on' == strtolower( $_SERVER['HTTPS'] ) ) {

        $is_secure = true;

    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || ! empty( $_SERVER['HTTP_X_FORWARDED_SSL'] ) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on' ) {

        $is_secure = true;

    }

    return $is_secure;

}


/**
 * Function that returns only the date part of a date-time format
 *
 * @param string $date
 *
 * @return string
 *
 */
function pms_sanitize_date( $date ) {

    if( !isset( $date ) )
        return;

    $date_time = explode( ' ', $date );

    return $date_time[0];

}


/**
 * Returns the url of the current page
 *
 * @param bool $strip_query_args - whether to eliminate query arguments from the url or not
 *
 * @return string
 *
 */
function pms_get_current_page_url( $strip_query_args = false ) {
    $home_url = pms_get_absolute_home();

    $home_path       = trim( parse_url( $home_url, PHP_URL_PATH ), '/' );
    $home_path_regex = sprintf( '|^%s|i', preg_quote( $home_path, '|' ) );

    $request_uri = preg_replace( $home_path_regex, '', ltrim( $_SERVER['REQUEST_URI'], '/' ) );
    $page_url    = trim( $home_url, '/') . '/' . ltrim( $request_uri, '/' );

    // Remove query arguments
    if( $strip_query_args ) {
        $page_url_parts = explode( '?', $page_url );

        $page_url = $page_url_parts[0];

        // Keep query args "p" and "page_id" for non-beautified permalinks
        if( isset( $page_url_parts[1] ) ) {
            $page_url_query_args = explode( '&', $page_url_parts[1] );

            if( !empty( $page_url_query_args ) ) {
                foreach( $page_url_query_args as $key => $query_arg ) {

                    if( strpos( $query_arg, 'p=' ) === 0 ) {
                        $query_arg_parts = explode( '=', $query_arg );
                        $query_arg       = $query_arg_parts[0];
                        $query_arg_val   = $query_arg_parts[1];

                        $page_url = add_query_arg( array( $query_arg => $query_arg_val ), $page_url );
                    }

                    if( strpos( $query_arg, 'page_id=' ) === 0 ) {
                        $query_arg_parts = explode( '=', $query_arg );
                        $query_arg       = $query_arg_parts[0];
                        $query_arg_val   = $query_arg_parts[1];

                        $page_url = add_query_arg( array( $query_arg => $query_arg_val ), $page_url );
                    }

                }
            }
        }

    }

    /**
     * Filter the page url just before returning
     *
     * @param string $page_url
     *
     */
    $page_url = apply_filters( 'pms_get_current_page_url', $page_url );

    return $page_url;

}

function pms_get_absolute_home(){
    global $wpdb;

    $url = ( ! is_multisite() && defined( 'WP_HOME' )
            ? WP_HOME
            : ( is_multisite() && ! is_main_site()
                ? ( preg_match( '/^(https)/', get_option( 'home' ) ) === 1 ? 'https://'
                    : 'http://' ) . $wpdb->get_var( "	SELECT CONCAT(b.domain, b.path)
								FROM {$wpdb->blogs} b
								WHERE blog_id = {$wpdb->blogid}
								LIMIT 1" )

                : $wpdb->get_var( "	SELECT option_value
								FROM {$wpdb->options}
								WHERE option_name = 'home'
								LIMIT 1" ) )
        );

    if ( !empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' )
        $url = str_replace( 'http://', 'https://', $url );
    else
        $url = str_replace( 'https://', 'http://', $url );

    return $url;
}

/**
 * Checks if there is a need to add the http:// prefix to a link and adds it. Returns the correct link.
 *
 * @param string $link
 *
 * @return string
 *
 */
function pms_add_missing_http( $link = '' ) {

    $http = '';

    if ( preg_match( '#^(?:[a-z\d]+(?:-+[a-z\d]+)*\.)+[a-z]+(?::\d+)?(?:/|$)#i', $link ) ) { //if missing http(s)

        $http = 'http';

        if ( isset( $_SERVER['HTTPS'] ) && 'on' == strtolower( $_SERVER['HTTPS'] ) )
            $http .= "s";

        $http .= "://";
    }

    return $http . $link;

}

/**
 * Sanitizes the values of an array recursivelly
 *
 * @param array $array
 *
 * @return array
 *
 */
function pms_array_sanitize_text_field( $array = array() ) {

    if( empty( $array ) || ! is_array( $array ) )
        return array();

    foreach( $array as $key => $value ) {

        if( is_array( $value ) )
            $array[$key] = pms_array_sanitize_text_field( $value );

        else
            $array[$key] = sanitize_text_field( $value );

    }

    return $array;

}


/**
 * Removes the script tags from the values of an array recursivelly
 *
 * @param array $array
 *
 * @return array
 *
 */
function pms_array_strip_script_tags( $array = array() ) {

    if( empty( $array ) || ! is_array( $array ) )
        return array();

    foreach( $array as $key => $value ) {

        if( is_array( $value ) )
            $array[$key] = pms_array_strip_script_tags( $value );

        else
            $array[$key] = preg_replace( '@<(script)[^>]*?>.*?</\\1>@si', '', $value );

    }

    return $array;

}


/**
 * Callback for the "wp_kses_allowed_html" filter to add iframes to the allowed tags
 *
 * @param array  $tags
 * @param strint $context
 *
 * @return array
 *
 */
function pms_wp_kses_allowed_html_iframe( $tags = array(), $context = '' ) {

    if ( 'post' === $context ) {

        $tags['iframe'] = array(
            'src'             => true,
            'height'          => true,
            'width'           => true,
            'frameborder'     => true,
            'allowfullscreen' => true,
        );

    }

    return $tags;

}


/**
 * Copy of WordPress's default _deprecated_function() function, which is marked as private
 *
 */
function _pms_deprecated_function( $function, $version, $replacement = null ) {

    /**
     * Filters whether to trigger an error for deprecated functions.
     *
     * @param bool $trigger Whether to trigger the error for deprecated functions. Default true.
     *
     */
    if ( WP_DEBUG && apply_filters( 'pms_deprecated_function_trigger_error', true ) ) {
        if ( function_exists( '__' ) ) {
            if ( ! is_null( $replacement ) ) {
                /* translators: 1: PHP function name, 2: version number, 3: alternative function name */
                trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.'), $function, $version, $replacement ) );
            } else {
                /* translators: 1: PHP function name, 2: version number */
                trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.'), $function, $version ) );
            }
        } else {
            if ( ! is_null( $replacement ) ) {
                trigger_error( sprintf( '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', $function, $version, $replacement ) );
            } else {
                trigger_error( sprintf( '%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', $function, $version ) );
            }
        }
    }
}

/**
 * Checks the status of the automatically login option
 *
 * @since 1.7.8
 * @return boolean True if auto login activated or false if not
 */
function pms_is_autologin_active() {
    $settings = get_option( 'pms_general_settings' );

    if ( !empty( $settings['automatically_log_in'] ) && $settings['automatically_log_in'] == '1' )
        return true;

    return false;
}

/**
 * Retrieves the serial number if available
 *
 * @since 1.7.8
 * @return string|bool
 */
function pms_get_serial_number() {
    return get_option( 'pms_serial_number' ) === false ? false : get_option( 'pms_serial_number' );
}

/**
 * Retrieves the status of the serial number. If not available, it will try to generate it.
 *
 * @since 1.7.8
 * @return string Serial number status
 */
function pms_get_serial_number_status() {

    if ( class_exists( 'PMS_Submenu_Page_Addons' ) && !get_option( 'pms_serial_number_status') && $serial = pms_get_serial_number() )
        PMS_Submenu_Page_Addons::add_on_check_serial_number( $serial, 'pms', true );

    return get_option( 'pms_serial_number_status' );

}

/**
 * Retrives the current Paid Member Subscriptions version
 *
 * @since 1.7.8
 * @return string  Either free, hobbyist or pro
 */
function pms_get_product_version() {

    if ( !( $serial = pms_get_serial_number() ) ) return 'free';

    $serial = explode( '-', $serial );

    if ( empty( $serial[0] ) ) return 'free';

    if ( $serial[0] == 'CLPMSB' || $serial[0] == 'CLPMSL' )
        return 'pro';
    else if ( $serial[0] == 'CLPMSH' )
        return 'hobbyist';

    return 'free';
}

/**
 * Verifies if any paid add-on is active on the current website
 *
 * @return boolean
 */
function pms_is_addon_active(){
    foreach( PMS_Submenu_Page_Addons::add_ons_get_remote_content() as $add_on ){
        if( $add_on['type'] != 'paid' )
            continue;

        $path = 'pms-add-on-' . $add_on['slug'] . '/index.php';

        if( is_plugin_active( $path ) )
            return true;
    }

    return false;
}

/*
 * To be used in admin screens
 */
function pms_get_current_post_type() {
    global $post, $typenow, $current_screen, $pagenow;

    if ( $post && $post->post_type )
        return $post->post_type;
    elseif ( $typenow )
        return $typenow;
    elseif ( $current_screen && $current_screen->post_type )
        return $current_screen->post_type;
    elseif ( isset( $_GET['post_type'] ) )
        return sanitize_key( $_GET['post_type'] );
    elseif ( isset( $_GET['post'] ) )
        return get_post_type( $_GET['post'] );
    elseif( is_admin() && $pagenow == 'post-new.php' )
        return 'post';

    return null;
}

function pms_get_billing_states(){

    $pms_states = array();

    $files = @glob( PMS_PLUGIN_DIR_PATH . 'i18n/states/[A-Z][A-Z].php', GLOB_NOSORT );

    foreach( $files as $file )
        include( $file );

    return apply_filters( 'pms_get_billing_states', $pms_states );

}

/**
 * Retrieve GDPR settings
 * If current settings are empty, it retrieves settings from the older option
 *
 * @since 2.0.5
 * @return array
 */
function pms_get_gdpr_settings(){

    $settings = get_option( 'pms_misc_settings', array() );

    if( empty( $settings['gdpr'] ) ){
        $old_gdpr_settings = get_option( 'pms_gdpr_settings', array() );

        if( !empty( $old_gdpr_settings ) ){
            $settings['gdpr'] = $old_gdpr_settings;

            update_option( 'pms_misc_settings', $settings );
        }

    }

    return isset( $settings['gdpr'] ) ? $settings['gdpr'] : array();

}
