<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Function that adds the HTML for PayPal Standard in the payments tab from the Settings page
 *
 * @param array $options    - The saved option settings
 *
 */
function pms_add_settings_content_paypal_standard( $options ) {
    ?>

    <div class="pms-payment-gateway-wrapper">
        <h4 class="pms-payment-gateway-title"><?php echo apply_filters( 'pms_settings_page_payment_gateway_paypal_title', __( 'Paypal Standard', 'paid-member-subscriptions' ) ); ?></h4>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="paypal-standard-email"><?php _e( 'PayPal E-mail Address', 'paid-member-subscriptions' ); ?></label>
            <input id="paypal-standard-email" type="text" name="pms_payments_settings[gateways][paypal_standard][email_address]" value="<?php echo isset( $options['gateways']['paypal_standard']['email_address' ]) ? $options['gateways']['paypal_standard']['email_address'] : ''; ?>" class="widefat" />

            <input type="hidden" name="pms_payments_settings[gateways][paypal_standard][name]" value="PayPal" />

            <p class="description"><?php _e( 'Enter your PayPal e-mail address', 'paid-member-subscriptions' ); ?></p>
        </div>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="paypal-standard-email"><?php _e( 'Test PayPal E-mail Address', 'paid-member-subscriptions' ); ?></label>
            <input id="paypal-standard-email" type="text" name="pms_payments_settings[gateways][paypal_standard][test_email_address]" value="<?php echo isset( $options['gateways']['paypal_standard']['test_email_address' ]) ? $options['gateways']['paypal_standard']['test_email_address'] : ''; ?>" class="widefat" />

            <p class="description"><?php _e( 'PayPal E-mail address to use for test transactions', 'paid-member-subscriptions' ); ?></p>
        </div>

        <?php do_action( 'pms_settings_page_payment_gateway_paypal_extra_fields', $options ); ?>

        <!-- IPN Message -->
        <p class="pms-ipn-notice">
            <?php printf( __( 'In order for <strong>PayPal payments to work correctly</strong>, you need to setup the IPN Url in your PayPal account. %sMore info%s', 'paid-member-subscriptions' ), '<a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/member-payments/#IPN_for_PayPal_gateways">', '</a>' ); ?>
        </p>

    </div>

    <?php
}
add_action( 'pms-settings-page_payment_gateways_content', 'pms_add_settings_content_paypal_standard' );

/*
 * Display a warning to administrators if the PayPal Email is not entered in settings
 *
 */
function pms_paypal_email_address_admin_warning() {

    if( !current_user_can( 'manage_options' ) )
        return;

    $are_active = array_intersect( array( 'paypal_standard', 'paypal_express', 'paypal_pro' ), pms_get_active_payment_gateways() );

    if( !empty( $are_active ) && pms_get_paypal_email() === false ) {

        echo '<div class="pms-warning-message-wrapper">';
            echo '<p>' . sprintf( __( 'Your <strong>PayPal Email Address</strong> is missing. In order to make payments you will need to add the Email Address of your PayPal account %1$s here %2$s.', 'paid-member-subscriptions' ), '<a href="' . admin_url( 'admin.php?page=pms-settings-page&tab=payments' ) .'" target="_blank">', '</a>' ) . '</p>';
            echo '<p><em>' . __( 'This message is visible only by Administrators.', 'paid-member-subscriptions' ) . '</em></p>';
        echo '</div>';

    }

}
add_action( 'pms_register_form_top', 'pms_paypal_email_address_admin_warning' );
add_action( 'pms_new_subscription_form_top', 'pms_paypal_email_address_admin_warning' );
add_action( 'pms_upgrade_subscription_form_top', 'pms_paypal_email_address_admin_warning' );
add_action( 'pms_renew_subscription_form_top', 'pms_paypal_email_address_admin_warning' );
add_action( 'pms_retry_payment_form_top', 'pms_paypal_email_address_admin_warning' );

function pms_wppb_paypal_email_address_admin_warning() {

    if( !current_user_can( 'manage_options' ) )
        return;

    $fields = get_option( 'wppb_manage_fields' );

    if ( empty( $fields ) )
        return;

    $are_active = array_intersect( array( 'paypal_standard', 'paypal_express', 'paypal_pro' ), pms_get_active_payment_gateways() );

    foreach( $fields as $field ) {
        if ( $field['field'] == 'Subscription Plans' && !empty( $are_active ) && pms_get_paypal_email() === false ) {
            echo '<div class="pms-warning-message-wrapper">';
                echo '<p>' . sprintf( __( 'Your <strong>PayPal Email Address</strong> is missing. In order to make payments you will need to add the Email Address of your PayPal account %1$s here %2$s.', 'paid-member-subscriptions' ), '<a href="' . admin_url( 'admin.php?page=pms-settings-page&tab=payments' ) .'" target="_blank">', '</a>' ) . '</p>';
                echo '<p><em>' . __( 'This message is visible only by Administrators.', 'paid-member-subscriptions' ) . '</em></p>';
            echo '</div>';

            break;
        }
    }

}
add_action( 'wppb_before_register_fields', 'pms_wppb_paypal_email_address_admin_warning' );

/**
 * Returns the PayPal Email Address
 *
 * @since 1.8.5
 */
function pms_get_paypal_email() {
    $settings = get_option( 'pms_payments_settings' );

    $slug = 'email_address';

    if( isset( $settings['test_mode'] ) && $settings['test_mode'] == '1' )
        $slug = 'test_email_address';

    if ( !empty( $settings['gateways']['paypal_standard'][$slug] ) )
        return $settings['gateways']['paypal_standard'][$slug];

    return false;
}

/**
 * Add custom log messages for the PayPal Standard gateway
 *
 */
function pms_paypal_payment_logs_system_error_messages( $message, $log ) {

    if ( empty( $log['type'] ) )
        return $message;

    $kses_args = array(
        'strong' => array()
    );

    switch( $log['type'] ) {
        case 'paypal_to_checkout':
            $message = __( 'User sent to <strong>PayPal Checkout</strong> to continue the payment process.', 'paid-member-subscriptions' );
            break;
        case 'paypal_ipn_waiting':
            $message = __( 'Waiting to receive Instant Payment Notification (IPN) from <strong>PayPal</strong>.', 'paid-member-subscriptions' );
            break;
        case 'paypal_ipn_received':
            $message = __( 'Instant Payment Notification (IPN) received from PayPal.', 'paid-member-subscriptions' );
            break;
        case 'paypal_ipn_not_received':
            $message = __( 'Instant Payment Notification (IPN) not received from PayPal.', 'paid-member-subscriptions' );
            break;
    }

    return apply_filters( 'pms_paypal_payment_logs_system_error_messages', wp_kses( $message, $kses_args ), $log );

}
add_filter( 'pms_payment_logs_system_error_messages', 'pms_paypal_payment_logs_system_error_messages', 10, 2 );
