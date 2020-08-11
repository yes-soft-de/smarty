<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Function that return the IP address of the user. Checks for IPs (in order) in: 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'
 *
 * @return string
 *
 */
function pms_get_user_ip_address() {

    $ip_address = '';

    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
        if (array_key_exists($key, $_SERVER) === true) {
            foreach ( array_map('trim', explode( ',', $_SERVER[$key]) ) as $ip ) {
                if ( filter_var($ip, FILTER_VALIDATE_IP) !== false ) {
                    return $ip;
                }
            }
        }
    }

    return $ip_address;

}


/**
 * Detect if the current user has concurrent sessions ( multiple logins at the same time )
 *
 * @return bool
 */
function pms_user_has_concurrent_sessions(){

    return ( is_user_logged_in() && count( wp_get_all_sessions() ) > 1 );

}


/**
 * Get the user's current session
 *
 * @return array
 */
function pms_get_current_session(){

    $sessions = WP_Session_Tokens::get_instance( get_current_user_id() );

    return $sessions->get( wp_get_session_token() );

}


/**
 * Allow only one session per user (disable concurrent logins)
 *
 * A newer session will have priority over an old one.
 * If the current user's session has been taken over by a newer session, we will destroy their session automatically and they will have to login again.
 * This will make it annoying for members to share their login credentials.
 *
 */
function pms_disable_concurrent_logins(){

    if ( !pms_user_has_concurrent_sessions() )

        return;

    $user_id = pms_get_current_user_id();

    $newest_session = max( wp_list_pluck( wp_get_all_sessions(), 'login') );

    $session = pms_get_current_session();

    if ( $session['login'] === $newest_session ) {

        // remove other sessions and keep this one
        wp_destroy_other_sessions();

        /**
         * Fires after a user's non-current sessions are destroyed
         *
         * @param int $user_id ID of the affected user
         */
        do_action( 'pms_destroy_other_sessions', $user_id );

    }

}

$pms_settings = get_option( 'pms_general_settings' );

if ( isset( $pms_settings['prevent_account_sharing'] ) && !empty( $pms_settings['prevent_account_sharing'] ) )
    add_action( 'init', 'pms_disable_concurrent_logins' );


/**
 * Redirect users from default WordPress login, register and lost password forms to the corresponding front-end pages created with our plugin.
 * This is done only if these pages are set under Settings -> General -> Membership Pages
 *
 */
function pms_redirect_default_wp_pages(){

    global $pagenow;

    if( $pagenow === 'wp-login.php' && isset( $_GET['pms_force_wp_login'] ) )
        return;

    $settings = get_option( 'pms_general_settings' );

    $login_page = ( isset( $settings['login_page'] ) && $settings['login_page'] != -1) ? get_permalink($settings['login_page']) : false;

    $register_page = ( isset( $settings['register_page'] ) && $settings['register_page'] != -1) ? get_permalink($settings['register_page']) : false;

    $lost_password_page =  ( isset( $settings['lost_password_page'] ) && $settings['lost_password_page'] != -1) ? get_permalink($settings['lost_password_page']) : false;

    if( ($pagenow == "wp-login.php") && !isset($_GET['action']) && $login_page ) {

        wp_redirect($login_page);
        exit;
    }

    else if ( ($pagenow == "wp-login.php") && ( isset( $_GET['action'] ) && ( $_GET['action'] == 'register' ) ) && $register_page ) {
        wp_redirect($register_page);
        exit;
    }

    else if ( ($pagenow == "wp-login.php") && ( isset( $_GET['action'] ) && ( $_GET['action'] == 'lostpassword' ) ) && $lost_password_page ) {
        wp_redirect($lost_password_page);
        exit;
    }

}

// make sure "Redirect Default WordPress Pages" option is checked
$pms_settings = get_option( 'pms_general_settings' );

if ( isset( $pms_settings['redirect_default_wp'] ) && !empty( $pms_settings['redirect_default_wp'] ) )
    add_action( 'init', 'pms_redirect_default_wp_pages' );


/* GDPR Delete user */
add_action('template_redirect','pms_gdpr_delete_user');
function pms_gdpr_delete_user() {
    $gdpr_settings = pms_get_gdpr_settings();
    if( !empty( $gdpr_settings ) ) {
        if (!empty($gdpr_settings['gdpr_delete']) && $gdpr_settings['gdpr_delete'] === 'enabled') {
            if (isset($_REQUEST['pms_action']) && $_REQUEST['pms_action'] == 'pms_delete_user' && wp_verify_nonce($_REQUEST['pms_nonce'], 'pms-user-own-account-deletion') && isset($_REQUEST['pms_user']) && get_current_user_id() == $_REQUEST['pms_user']) {
                require_once(ABSPATH . 'wp-admin/includes/user.php');
                $user = new WP_User($_REQUEST['pms_user']);

                if (!empty($user->roles)) {
                    foreach ($user->roles as $role) {
                        if ($role != 'administrator') {
                            wp_delete_user($_REQUEST['pms_user']);
                            pms_member_delete_user_subscription_cancel($_REQUEST['pms_user']);
                        }
                    }
                }

                $args = array('pms_user', 'pms_action', 'pms_nonce');
                wp_redirect(remove_query_arg($args));
            }
        }
    }
}

// Save GDPR field
add_action( 'pms_register_form_after_create_user', 'pms_save_gdpr_field' );
function pms_save_gdpr_field( $userdata ){

    if( empty( $userdata['user_id'] ) )
        return;

    if ( isset( $_POST['user_consent'] ) && $_POST['user_consent'] == '1' ) {
        update_user_meta( $userdata['user_id'], 'pms_gdpr_user_consent', 'yes' );
        update_user_meta( $userdata['user_id'], 'pms_gdpr_user_consent_time', time() );
    }

}

//hook into the wp export compatibility
add_filter( 'wp_privacy_personal_data_exporters', 'pms_register_pms_wp_exporter', 10 );
function pms_register_pms_wp_exporter( $exporters ) {
    $exporters['profile-builder'] = array(
        'exporter_friendly_name' => __( 'Paid Member Subscriptions', 'paid-member-subscriptions' ),
        'callback' => 'pms_wp_exporter',
    );
    return $exporters;
}

/* function to add our user meta to wp exporter */
function pms_wp_exporter( $email_address, $page = 1 ) {

    $export_items = array();

    $user = get_user_by( 'email', $email_address );
    if( $user ) {

        //add PMS meta to Export Personal Data
        $all_meta_for_user = get_user_meta( $user->ID );
        if( !empty( $all_meta_for_user ) ) {

            $item_id = "pms-billing-details-{$user->ID}";
            $group_id = 'pms-billing-details';
            $group_label = __('Paid Member Subscriptions Billing Details', 'paid-member-subscriptions');
            $data = array();

            foreach ( $all_meta_for_user as $meta_key => $meta_for_user ) {
                if (strpos( $meta_key, 'pms_billing_') === 0 ) {
                    $user_meta_value = $meta_for_user[0];
                    if( !empty( $user_meta_value ) ){

                        $data[] = array(
                            'name' => $meta_key,
                            'value' => $user_meta_value
                        );
                    }
                }
            }

            $export_items[] = array(
                'group_id' => $group_id,
                'group_label' => $group_label,
                'item_id' => $item_id,
                'data' => $data,
            );

        }

        //add PMS payments to Export Personal Data
        $user_payments = pms_get_payments( array( 'user_id' => $user->ID ) );
        if( !empty( $user_payments ) ){
            $item_id = "pms-payments-{$user->ID}";
            $group_id = 'pms-payments';
            $group_label = __('Paid Member Subscriptions Payments', 'paid-member-subscriptions');
            $data = array();

            foreach ( $user_payments as $user_payment ) {

                $data[] = array(
                    'name' => __( 'Subscription', 'paid-member-subscriptions' ),
                    'value' => get_the_title( $user_payment->subscription_id )
                );

                $data[] = array(
                    'name' => __( 'Status', 'paid-member-subscriptions' ),
                    'value' => $user_payment->status
                );

                $data[] = array(
                    'name' => __( 'Date', 'paid-member-subscriptions' ),
                    'value' => $user_payment->date
                );

                $data[] = array(
                    'name' => __( 'IP Address', 'paid-member-subscriptions' ),
                    'value' => $user_payment->ip_address
                );


            }

            $export_items[] = array(
                'group_id' => $group_id,
                'group_label' => $group_label,
                'item_id' => $item_id,
                'data' => $data,
            );

        }


    }

    return array(
        'data' => $export_items,
        'done' => true,
    );
}

/**
 * Remove admin bard from logged in users if option is selected
 */
$pms_misc_settings = get_option( 'pms_misc_settings', array() );

if( isset( $pms_misc_settings, $pms_misc_settings['hide-admin-bar'] ) && $pms_misc_settings['hide-admin-bar'] == 1 ){
    add_filter( 'show_admin_bar', 'pms_remove_admin_bar' );
}

function pms_remove_admin_bar(){

    if( current_user_can( 'manage_options' ) )
        return true;

    return false;

}
