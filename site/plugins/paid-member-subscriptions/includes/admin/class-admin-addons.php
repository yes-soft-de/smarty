<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends core PMS_Submenu_Page base class to create and add custom functionality
 * for the add-ons page in the admin section
 *
 * The Add-ons page will contain a listing of all the available add-ons for PMS,
 * allowing the user to purchase, install or activate a certain add-on.
 *
 */
Class PMS_Submenu_Page_Addons extends PMS_Submenu_Page {

    /*
     * Method that initializes the class
     *
     * */
    public function init() {

        // Hook the output method to the parent's class action for output instead of overwriting the
        // output method
        add_action( 'pms_output_content_submenu_page_' . $this->menu_slug, array( $this, 'output' ) );

        add_action( 'wp_ajax_pms_add_on_activate', array( $this, 'add_on_activate' ) );
        add_action( 'wp_ajax_pms_add_on_deactivate', array( $this, 'add_on_deactivate' ) );

        add_action( 'wp_ajax_pms_add_on_save_serial', array( $this, 'add_on_save_serial' ) );

    }

    /*
     * Method to output the content in the Add-ons page
     *
     * */
    public function output(){

        include_once 'views/view-page-addons.php';

    }

    /*
    * Function that returns the array of add-ons from cozmoslabs.com if it finds the file
    * If something goes wrong it returns false
    *
    * @since v.2.1.0
    */
    static function add_ons_get_remote_content() {
        $slug = 'pms_add_ons_remote_content';

        if ( false === ( $pms_add_ons = get_transient( $slug ) ) ) {
            global $wp_version;

            $args = array(
                'user-agent'  => 'PMS/WordPress/' . $wp_version . '; ' . pms_get_current_page_url(),
            );

            $response = wp_remote_get( 'https://www.cozmoslabs.com/wp-content/plugins/cozmoslabs-products-add-ons/paid-member-subscriptions-add-ons.json', $args );

            if( is_wp_error( $response ) ) {
                return false;
            } else {
                $json_file_contents = $response['body'];
                $pms_add_ons        = json_decode( $json_file_contents, true );
            }

            if( !is_object( $pms_add_ons ) && !is_array( $pms_add_ons ) )
                return false;

            set_transient( $slug, $pms_add_ons, 60 * MINUTE_IN_SECONDS );
        }

        return $pms_add_ons;
    }


    /**
     * Function that is triggered through Ajax to activate an add-on
     *
     */
    function add_on_activate(){

        check_ajax_referer( 'pms-activate-addon', 'nonce' );

        if( current_user_can( 'manage_options' ) ){

            // Setup variables from POST
            $pms_add_on_to_activate = sanitize_text_field( $_POST['pms_add_on_to_activate'] );
            $response               = (int)$_POST['pms_add_on_index'];

            if( !empty( $pms_add_on_to_activate ) && !is_plugin_active( $pms_add_on_to_activate )) {
                activate_plugin( $pms_add_on_to_activate );
            }

            if( !empty( $response ) || $response == 0 )
                echo $response;
        }

        wp_die();
    }

    /**
     * Function that is triggered through Ajax to deactivate an add-on
     *
     */
    function add_on_deactivate() {

        check_ajax_referer( 'pms-activate-addon', 'nonce' );

        if( current_user_can( 'manage_options' ) ) {

            // Setup variables from POST
            $pms_add_on_to_deactivate = sanitize_text_field( $_POST['pms_add_on_to_deactivate'] );
            $response                 = (int)$_POST['pms_add_on_index'];

            if( !empty( $pms_add_on_to_deactivate ))
                deactivate_plugins( $pms_add_on_to_deactivate );

            if( !empty( $response ) || $response == 0 )
                echo $response;
        }

        wp_die();

    }

    public function sanitize_settings( $serial_number ) {

        PMS_Submenu_Page_Addons::add_on_check_serial_number( trim( $serial_number ), 'pms', true );

        return $serial_number;
    }

    //the function to check the validity of the serial number and save a variable in the DB; purely visual
    static function add_on_check_serial_number( $serial, $add_on_slug, $resetCron = false ){

        $remote_url = 'http://updatemetadata.cozmoslabs.com/checkserial/?serialNumberSent='.$serial;

        $remote_response = wp_remote_get( $remote_url );

        $response = PMS_Submenu_Page_Addons::add_on_update_serial_status( $remote_response, $add_on_slug );

        if( $resetCron === true )
            PMS_Submenu_Page_Addons::add_on_clear_cron_hooks();

        return $response;
    }

    static function add_on_clear_cron_hooks() {

        $add_ons = PMS_Submenu_Page_Addons::add_ons_get_remote_content();

        if( is_array( $add_ons ) && !empty( $add_ons[0] ) ){
            foreach( $add_ons as $add_on )
                wp_clear_scheduled_hook( 'check_plugin_updates-' . $add_on['slug'] );
        }

    }

    /* function to update the serial number status */
    static function add_on_update_serial_status( $response, $add_on_slug ) {
        if ( $add_on_slug != 'pms' ) {
            $serial_status = 'pms_add_on_'.$add_on_slug.'_serial_status';
            $serial_number = 'pms_add_on_'. $add_on_slug .'_serial_number';
        } else {
            $serial_status = 'pms_serial_number_status';
            $serial_number = 'pms_serial_number';
        }

        if ( is_wp_error($response) ) {

            update_option( $serial_status, 'serverDown' ); //server down

            return 'serverDown';
        } else {
            $response_body = trim($response['body']);

            if (($response_body != 'notFound') && ($response_body != 'found') && ($response_body != 'expired') && (strpos( $response['body'], 'aboutToExpire' ) === false)) {

                update_option( $serial_status, 'serverDown' ); //unknown response parameter
                //update_option( $serial_number, '' ); //reset the entered serial, since the user will need to try again later

                return 'serverDown';
            } else {

                update_option( $serial_status, $response_body ); //either found, notFound, expired or aboutToExpire

                return $response_body;
            }
        }
    }

    static function add_ons_output_serial_number_status_message() {
        $status = pms_get_serial_number_status();

        if ( empty( $status ) || !pms_get_serial_number() )
            return printf( __( 'Need a licence ? <a href="%s">Click here</a> to purchase one.', 'paid-member-subscriptions'), esc_url( 'https://www.cozmoslabs.com/wordpress-paid-member-subscriptions/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=PMS&utm_content=add-on-page-no-serial-number-message' ) );
        else if ( $status == 'found' )
            return _e( 'Your serial number has been successfully validated.', 'paid-member-subscriptions' );
        else if ( $status == 'expired' )
            return printf( __( 'Your serial number has expired. <a href="%s">Click here</a> to renew.', 'paid-member-subscriptions'), esc_url( 'https://www.cozmoslabs.com/account/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=PMS&utm_content=add-on-page-expired-serial-number-message' ) );
        else if ( strpos( $status, 'aboutToExpire' ) !== false ) {
            $parts = explode( '#', $status );

            if ( !empty( $parts[1] ) )
                return printf( __( 'Your licence is valid but will expire on %s. <a href="%s">Click here</a> to renew.', 'paid-member-subscriptions'), $parts[1], esc_url( 'https://www.cozmoslabs.com/account/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=PMS&utm_content=add-on-page-expired-serial-number-message' ) );
            else
                return printf( __( 'Your licence is valid but it will expire soon. <a href="%s">Click here</a> to renew.', 'paid-member-subscriptions'), esc_url( 'https://www.cozmoslabs.com/account/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=PMS&utm_content=add-on-page-expired-serial-number-message' ) );
        }
        else if ( $status == 'notFound' )
            return printf( __( 'The serial number you entered is invalid. Need a licence ? <a href="%s">Click here</a> to purchase one.', 'paid-member-subscriptions'), esc_url( 'https://www.cozmoslabs.com/wordpress-paid-member-subscriptions/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=PMS&utm_content=add-on-page-no-serial-number-message' ) );
        else if ( $status == 'serverDown' )
            return _e( 'Couldn\'t contact our server. Please try again later.', 'paid-member-subscriptions' );
    }

    static function add_ons_output_styling_class( $status ) {
        if ( !pms_get_serial_number() ) {}
        else if ( !empty( $status ) && ( $status == 'found' || strpos( $status, 'aboutToExpire' ) !== false ) )
            echo 'pms-found';
        else
            echo 'pms-error';
    }
}

$pms_submenu_page_addons = new PMS_Submenu_Page_Addons( 'paid-member-subscriptions', __( 'Add-ons', 'paid-member-subscriptions' ), __( 'Add-ons', 'paid-member-subscriptions' ), 'manage_options', 'pms-addons-page', 30, 'pms_serial_number' );
$pms_submenu_page_addons->init();
