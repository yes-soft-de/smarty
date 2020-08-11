<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * This file has the sole purpose to help solve compatibility issues with other plugins/themes
 *
 */

/****************************************************
 * Theme Name: Eduma
 * Plugin URI: https://themeforest.net/item/education-wordpress-theme-education-wp/14058034
 ****************************************************/


 /**
  * Do not autologin if the registration happened on a PMS form
  *
  */
 if( function_exists( 'thim_register_extra_fields' ) ) {
     remove_action( 'user_register', 'thim_register_extra_fields', 1000 );
     add_action( 'user_register', 'pms_thim_register_extra_fields', 1001 );

     function pms_thim_register_extra_fields( $user_id ) {

         //if PMS form, skip
         if ( isset( $_POST['pmstkn'] ) )
             return;

         $user_data = array();
         $user_data['ID'] = $user_id;
         if (!empty($_POST['password'])) {
             $user_data['user_pass'] = $_POST['password'];
             add_filter('send_password_change_email', '__return_false');
         }
         $new_user_id = wp_update_user($user_data);

         // Login after registered
         if (!is_admin()) {
             wp_set_current_user($user_id);
             wp_set_auth_cookie($user_id);
             wp_new_user_notification($user_id, null, 'both');

             if (isset($_POST['level']) && $_POST['level'] && isset($_POST['token']) && $_POST['token'] && isset($_POST['gateway']) && $_POST['gateway']) {
                 return;
             }

             if (isset($_REQUEST['level']) && $_REQUEST['level'] && isset($_REQUEST['review']) && $_REQUEST['review'] && isset($_REQUEST['token']) && $_REQUEST['token'] && isset($_REQUEST['PayerID']) && $_REQUEST['PayerID']) {
                 return;
             }

             if ((isset($_POST['billing_email']) && !empty($_POST['billing_email'])) || (isset($_POST['bconfirmemail']) && !empty($_POST['bconfirmemail']))) {
                 return;
             } else {
                 if (!empty($_REQUEST['redirect_to'])) {
                     wp_redirect($_REQUEST['redirect_to']);
                 } else {
                     $theme_options_data = get_theme_mods();
                     if (!empty($_REQUEST['option']) && $_REQUEST['option'] == 'moopenid') {
                         if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                             $http = "https://";
                         } else {
                             $http = "http://";
                         }
                         $redirect_url = urldecode(html_entity_decode(esc_url($http . $_SERVER["HTTP_HOST"] . str_replace('?option=moopenid', '', $_SERVER['REQUEST_URI']))));
                         if (html_entity_decode(esc_url(remove_query_arg('ss_message', $redirect_url))) == wp_login_url() || strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false || strpos($_SERVER['REQUEST_URI'], 'wp-admin') !== false) {
                             $redirect_url = site_url() . '/';
                         }

                         wp_redirect($redirect_url);

                         return;
                     }

                     if (!empty($theme_options_data['thim_register_redirect'])) {
                         wp_redirect($theme_options_data['thim_register_redirect']);
                     } else {
                         wp_redirect(home_url());
                     }
                 }
                 exit();
             }
         }
     }
 }

 add_action( 'after_setup_theme', 'pms_plugin_compatibility_notices' );
 function pms_plugin_compatibility_notices() {

    /**
    * Add a notice if the Avada theme is used
    */
    if ( defined( 'AVADA_VERSION' ) ) {

        $message = sprintf( __( 'If you have issues with the styling of the submit button on <strong>Paid Member Subscriptions</strong> forms, <a href="%s" target="_blank">click here</a> for more info.', 'paid-member-subscriptions' ), 'https://www.cozmoslabs.com/docs/paid-member-subscriptions/developer-knowledge-base/style-submit-buttons-when-using-avada-theme/' );
        $message .= sprintf( __( ' %1$sDismiss%2$s', 'paid-member-subscriptions'), "<a href='" . esc_url( add_query_arg('pms_avada_styling_compatibility_dismiss_notification', '0') ) . "'>", "</a>" );
        
        new PMS_Add_General_Notices(
            'pms_avada_styling_compatibility',
            $message,
            'notice-warning' );
    }
 }
