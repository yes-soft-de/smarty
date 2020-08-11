<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//Move existing serial from individual options into the new general option
function pms_get_addon_serial_option_names() {
    return array(
        'pay-what-you-want_serial_number',
        'fixed-period-membership_serial_number',
        'invoices_serial_number',
        'email-reminders_serial_number',
        'content-dripping_serial_number',
        'paid-member-subscriptions-bbpress_serial_number',
        'paypal-pro-paypal-express_serial_number',
        'recurring-payments-for-paypal-standard_serial_number',
        'stripe_serial_number',
        'global-content-restriction_serial_number',
        'multiple-subscriptions-per-user_serial_number',
        'navigation-menu-filtering_serial_number',
        'discount-codes_serial_number'
    );
}

add_action( 'init', 'pms_move_serial_to_general_option_if_available' );
function pms_move_serial_to_general_option_if_available() {
    if ( get_option( 'pms_already_moved_serial' ) == 'yes' || pms_get_serial_number() !== false ) return;

    foreach ( pms_get_addon_serial_option_names() as $option_name ) {
        $option = get_option( $option_name );

        if ( !empty( $option ) && get_option( 'pms_add_on_' . str_replace( '_serial_number', '_serial_status', $option_name ) ) == 'found' ) {
            update_option( 'pms_serial_number', $option );
            update_option( 'pms_already_moved_serial', 'yes' );

            break;
        }
    }
}

function pms_addon_serial_handler( $value, $option ) {
    return pms_get_serial_number() === false ? $value : pms_get_serial_number();
}

foreach( pms_get_addon_serial_option_names() as $pms_addon_option_name ) {
    add_filter( 'pre_option_' . $pms_addon_option_name, 'pms_addon_serial_handler', 20, 2);
}

//Migrate the old single option for settings to the new structure
add_action( 'init', 'pms_migrate_old_settings_to_new' );
function pms_migrate_old_settings_to_new() {
    remove_filter( 'option_pms_settings', 'pms_recreate_old_settings', 99 );

    $old_settings = get_option( 'pms_settings' );

    add_filter( 'option_pms_settings', 'pms_recreate_old_settings', 99, 2);

    if ( get_option( 'pms_already_migrated_options' ) == 'yes' || empty( $old_settings ) ) return;

    $new_settings = array( 'general', 'payments', 'emails', 'woocommerce', 'invoices', 'recaptcha' );

    foreach( $new_settings as $setting ) {
        if ( !empty( $old_settings[$setting] ) )
            update_option( 'pms_' . $setting . '_settings', $old_settings[$setting] );
    }

    //Content Restriction
    $content_restriction_settings = array();

    $keys = array( 'content_restrict_type', 'content_restrict_redirect_url', 'content_restrict_template' );

    foreach( $keys as $key ) {
        if ( !empty( $old_settings[$key] ) )
            $content_restriction_settings[$key] = $old_settings[$key];
    }

    if ( !empty( $old_settings['messages']['logged_out'] ) )
        $content_restriction_settings['logged_out'] = $old_settings['messages']['logged_out'];

    if ( !empty( $old_settings['messages']['non_members'] ) )
        $content_restriction_settings['non_members'] = $old_settings['messages']['non_members'];

    if ( !empty( $old_settings['messages']['purchasing_restricted'] ) )
        $content_restriction_settings['purchasing_restricted'] = $old_settings['messages']['purchasing_restricted'];

    if ( !empty( $old_settings['general']['restricted_post_preview'] ) )
        $content_restriction_settings['restricted_post_preview'] = $old_settings['general']['restricted_post_preview'];

    update_option( 'pms_content_restriction_settings', $content_restriction_settings );

    update_option( 'pms_already_migrated_options', 'yes' );
}

//Recreate old settings option from the new one when requested
add_filter( 'option_pms_settings', 'pms_recreate_old_settings', 99, 2);
function pms_recreate_old_settings( $value, $option ) {
    $settings = array();

    $new_settings_slug = array( 'general', 'payments', 'emails', 'woocommerce', 'invoices', 'recaptcha' );

    foreach( $new_settings_slug as $slug ) {
        $new_setting = get_option( 'pms_' . $slug . '_settings' );

        if ( !empty( $new_setting ) )
            $settings[$slug] = $new_setting;
    }

    $content_restriction_settings = get_option( 'pms_content_restriction_settings' );

    $keys = array( 'content_restrict_type', 'content_restrict_redirect_url', 'content_restrict_template' );

    foreach( $keys as $key ) {
        if ( !empty( $content_restriction_settings[$key] ) )
            $settings[$key] = $content_restriction_settings[$key];
    }

    $keys = array( 'logged_out', 'non_members', 'purchasing_restricted' );

    foreach( $keys as $key ) {
        if ( !empty( $content_restriction_settings[$key] ) )
            $settings['messages'][$key] = $content_restriction_settings[$key];
    }

    if ( !empty( $content_restriction_settings['restricted_post_preview'] ) )
        $settings['general']['restricted_post_preview'] = $content_restriction_settings['restricted_post_preview'];

    return $settings;
}

function pms_add_settings_content_stripe_compatibility( $options ) {
    // Stripe API fields
    $fields = array(
        'test_api_publishable_key' => array(
            'label' => __( 'Test Publishable Key', 'paid-member-subscriptions' )
        ),
        'test_api_secret_key' => array(
            'label' => __( 'Test Secret Key', 'paid-member-subscriptions' )
        ),
        'api_publishable_key' => array(
            'label' => __( 'Live Publishable Key', 'paid-member-subscriptions' )
        ),
        'api_secret_key' => array(
            'label' => __( 'Live Secret Key', 'paid-member-subscriptions' )
        )
    );

    echo '<div class="pms-payment-gateway-wrapper">';

        echo '<h4 class="pms-payment-gateway-title">' . __( 'Stripe', 'paid-member-subscriptions' ) . '</h4>';

        foreach( $fields as $field_slug => $field_options ) {
            echo '<div class="pms-form-field-wrapper">';

            echo '<label class="pms-form-field-label" for="stripe-' . str_replace( '_', '-', $field_slug ) . '">' . $field_options['label'] . '</label>';
            echo '<input id="stripe-' . str_replace( '_', '-', $field_slug ) . '" type="text" name="pms_payments_settings[gateways][stripe][' . $field_slug . ']" value="' . ( isset( $options['gateways']['stripe'][$field_slug] ) ? $options['gateways']['stripe'][$field_slug] : '' ) . '" class="widefat" />';

            if( isset( $field_options['desc'] ) )
                echo '<p class="description">' . $field_options['desc'] . '</p>';

            echo '</div>';
        }

        do_action( 'pms_settings_page_payment_gateway_stripe_extra_fields', $options );

    echo '</div>';
}

function pms_settings_gateway_paypal_express_extra_fields_compatibility( $options ) {

    echo '<div class="pms-form-field-wrapper pms-form-field-wrapper-checkbox">';
        echo '<label class="pms-form-field-label" for="paypal-express-reference-transactions">' . __( 'Reference Transactions', 'paid-member-subscriptions' ) . '</label>';
        echo '<p class="description"><input type="checkbox" id="paypal-express-reference-transactions" name="pms_payments_settings[gateways][paypal][reference_transactions]" value="1" ' . ( isset( $options['gateways']['paypal']['reference_transactions'] ) ? 'checked' : '' ) . '/>' . sprintf( __( 'Check if your PayPal account has Reference Transactions enabled. %1$sLearn how to enable reference transactions.%2$s', 'paid-member-subscriptions' ), '<a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/add-ons/paypal-pro-and-express-checkout/#Reference_Transactions" target="_blank">', '</a>' ) . '</p>';
    echo '</div>';

}

function pms_settings_gateway_paypal_extra_fields_compatiblity( $options ) {
    // PayPal API fields
    $fields = array(
        'api_username' => array(
            'label' => __( 'API Username', 'paid-member-subscriptions' ),
            'desc'  => __( 'API Username for Live site', 'paid-member-subscriptions' )
        ),
        'api_password' => array(
            'label' => __( 'API Password', 'paid-member-subscriptions' ),
            'desc'  => __( 'API Password for Live site', 'paid-member-subscriptions' )
        ),
        'api_signature' => array(
            'label' => __( 'API Signature', 'paid-member-subscriptions' ),
            'desc'  => __( 'API Signature for Live site', 'paid-member-subscriptions' )
        ),
        'test_api_username' => array(
            'label' => __( 'Test API Username', 'paid-member-subscriptions' ),
            'desc'  => __( 'API Username for Test/Sandbox site', 'paid-member-subscriptions' )
        ),
        'test_api_password' => array(
            'label' => __( 'Test API Password', 'paid-member-subscriptions' ),
            'desc'  => __( 'API Password for Test/Sandbox site', 'paid-member-subscriptions' )
        ),
        'test_api_signature' => array(
            'label' => __( 'Test API Signature', 'paid-member-subscriptions' ),
            'desc'  => __( 'API Signature for Test/Sandbox site', 'paid-member-subscriptions' )
        )
    );

    foreach( $fields as $field_slug => $field_details ) {
        echo '<div class="pms-form-field-wrapper">';

        echo '<label class="pms-form-field-label" for="paypal-' . str_replace('_', '-', $field_slug) . '">' . $field_details['label'] . '</label>';
        echo '<input id="paypal-' . str_replace('_', '-', $field_slug) . '" type="text" name="pms_payments_settings[gateways][paypal][' . $field_slug . ']" value="' . ( isset($options['gateways']['paypal'][$field_slug]) ? $options['gateways']['paypal'][$field_slug] : '' ) . '" class="widefat" />';

        echo '<p class="description">' . $field_details['desc'] . '</p>';

        echo '</div>';
    }
}

//Ensures compatibility between the latest PMS core version and older version of add-ons.
add_action( 'admin_init', 'pms_handle_settings_created_by_outdated_addons' );
function pms_handle_settings_created_by_outdated_addons() {

    if ( defined( 'PMS_STRIPE_VERSION' ) && version_compare( PMS_STRIPE_VERSION, '1.2.6' ) == -1 ) {
        remove_action( 'pms-settings-page_payment_gateways_content', 'pms_add_settings_content_stripe' );

        add_action( 'pms-settings-page_payment_gateways_content', 'pms_add_settings_content_stripe_compatibility' );
    }

    if ( defined( 'PMSPP_VERSION' ) && version_compare( PMSPP_VERSION, '1.2.6' ) == -1 ) {
        remove_action( 'pms_settings_page_payment_gateway_paypal_extra_fields', 'pms_settings_gateway_paypal_extra_fields' );
        remove_action( 'pms_settings_page_payment_gateway_paypal_extra_fields', 'pms_settings_gateway_paypal_express_extra_fields' );

        add_action( 'pms_settings_page_payment_gateway_paypal_extra_fields', 'pms_settings_gateway_paypal_express_extra_fields_compatibility' );
        add_action( 'pms_settings_page_payment_gateway_paypal_extra_fields', 'pms_settings_gateway_paypal_extra_fields_compatiblity' );
    }

    if ( defined( 'PMS_PPSRP_PLUGIN_DIR_PATH' ) && !defined( 'PMS_PPSRP_VERSION' ) ) {
        remove_action( 'pms_settings_page_payment_gateway_paypal_extra_fields', 'pms_settings_gateway_paypal_extra_fields' );
        add_action( 'pms_settings_page_payment_gateway_paypal_extra_fields', 'pms_settings_gateway_paypal_extra_fields_compatiblity' );
    }

}

add_action( 'plugins_loaded', 'pms_add_on_related_notices' );
function pms_add_on_related_notices() {
    /**
     * Add a notice if the Invoices add-on is out of date
     */
     if ( defined( 'PMS_INV_VERSION' ) && version_compare( PMS_INV_VERSION, '1.0.5', '<' ) ) {

         $message = __( 'Your <strong>Invoices</strong> add-on version is not 100% compatible with this version of <strong>Paid Member Subscriptions</strong>.<br>', 'paid-member-subscriptions' );

         //serial exists and is expired
         if ( pms_get_serial_number() && pms_get_serial_number_status() == 'expired' )
             $message .= sprintf( __( 'Your licence is currently <strong>expired</strong>, you need to <a href="%s">renew</a> your licence to get access to the latest updates. After renewal, go to the <a href="%s">plugins</a> page to <strong>update the add-on to the latest version</strong>.', 'paid-member-subscriptions' ), esc_url( 'https://www.cozmoslabs.com/account/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=PMS&utm_content=add-on-page-invoices-outdated-notification' ), admin_url( 'plugins.php?s=paid member subscriptions&plugin_status=all' ) ) ;
         else
              $message .= sprintf( __( 'Please go to the <a href="%s">plugins</a> page to <strong>update the add-on to the latest version</strong>.', 'paid-member-subscriptions' ), admin_url( 'plugins.php?s=paid member subscriptions&plugin_status=all' ) ) ;

         new PMS_Add_General_Notices( 'pms_inv_add-on_incompatibility',
             $message,
             'error' );
     }

     /**
      * Add a notice if the reCAPTCHA add-on is out of date
      */
      if ( defined( 'PMS_RECAPTCHA_VERSION' ) && version_compare( PMS_RECAPTCHA_VERSION, '1.0.4', '<' ) ) {

          $message = __( 'Your <strong>reCAPTCHA</strong> add-on version is not 100% compatible with this version of <strong>Paid Member Subscriptions</strong>.<br>', 'paid-member-subscriptions' );
          $message .= sprintf( __( '<a href="%s" target="_blank">Click here</a> to download the latest version of the add-on. You can read install instructions on our <a href="%s" target="_blank">documentation</a>.', 'paid-member-subscriptions' ), esc_url( 'http://www.cozmoslabs.com/wp-content/plugins/download-monitor/download.php?id=69' ), esc_url( 'https://www.cozmoslabs.com/docs/paid-member-subscriptions/add-ons/recaptcha/#Install_the_reCaptcha_Add-on' ) ) ;

          new PMS_Add_General_Notices( 'pms_recaptcha_add-on_incompatibility',
              $message,
              'error' );
      }
}

//Backwards compatiblity for old function name
function pms_get_retry_payment_url( $plan_id ) {
    return pms_get_retry_url( $plan_id );
}

//Do not autoload failed api attempts option
add_action( 'init', 'pms_check_failed_api_attempts' );
function pms_check_failed_api_attempts() {
    $cleared = get_option( 'pms_already_checked_attempts' );

    if( empty( $cleared ) ) {
        //remove autoloading
        $failed_attempts = get_option( 'pms_api_failed_attempts' );

        if( empty( $failed_attempts ) )
            $failed_attempts = array( 1 => array( 2 => array( 'retries' => array() ) ) );

        update_option( 'pms_api_failed_attempts', '', 'no' );
        update_option( 'pms_api_failed_attempts', $failed_attempts );

        global $wpdb;

        $query = $wpdb->get_results( "SELECT option_name, length(option_value) FROM {$wpdb->prefix}options WHERE option_name = 'pms_api_failed_attempts' AND autoload = 'no'" );

        $key = 'length(option_value)';
        if( isset( $query[0] ) && $query[0]->option_name == 'pms_api_failed_attempts' && isset( $query[0]->$key ) ) {

            if( (int)$query[0]->$key > 1000000 )
                update_option( 'pms_api_failed_attempts', '' );

        }

        update_option( 'pms_already_checked_attempts', 'yes' );
    }

}
