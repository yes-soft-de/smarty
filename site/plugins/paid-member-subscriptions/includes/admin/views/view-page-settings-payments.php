<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * HTML Output for the Payments tab
 */
?>

<div id="payments-general">

    <h3><?php _e( 'General', 'paid-member-subscriptions' ); ?></h3>

    <div class="pms-form-field-wrapper pms-test-mode">
        <label class="pms-form-field-label"><?php _e( 'Test Mode', 'paid-member-subscriptions' ) ?></label>

        <p class="description">
            <input type="checkbox" id="payment-test-mode" name="pms_payments_settings[test_mode]" value="1" <?php echo ( isset( $this->options['test_mode'] ) ? 'checked' : '' ); ?> />

            <?php printf( __( 'By checking this option you will be able to use Paid Member Subscriptions only with test accounts from your payment processors. <a href="%s">More Details</a>', 'paid-member-subscriptions' ), 'https://www.cozmoslabs.com/docs/paid-member-subscriptions/settings/payments/#Test_Mode' ); ?>
        </p>
    </div>

    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="payment-currency"><?php _e( 'Currency', 'paid-member-subscriptions' ) ?></label>

        <select id="payment-currency" class="pms-chosen" name="pms_payments_settings[currency]">
            <?php
            foreach( pms_get_currencies() as $currency_code => $currency )
                echo '<option value="' . esc_attr( $currency_code ) . '"' . ( isset( $this->options['currency'] ) ? selected( $this->options['currency'], $currency_code, false ) : '') . '>' . esc_html( $currency ) . ' (' . esc_html( $currency_code ) . ')</option>';
            ?>
        </select>

        <p class="description"><?php _e( 'Select your currency. Please note that some payment gateways can have currency restrictions.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="payment-currency-position"><?php _e( 'Currency Position', 'paid-member-subscriptions' ) ?></label>

        <select id="payment-currency-position" name="pms_payments_settings[currency_position]">
            <option value="before" <?php ( isset( $this->options['currency_position'] ) ? selected( $this->options['currency_position'], 'before', true ) : ''); ?>><?php _e( 'Before', 'paid-member-subscriptions' ); ?></option>
            <option value="after" <?php ( isset( $this->options['currency_position'] ) ? selected( $this->options['currency_position'], 'after', true ) : ''); ?>><?php _e( 'After', 'paid-member-subscriptions' ); ?></option>
        </select>

        <p class="description"><?php _e( 'Select whether the currency symbol should appear before the price or after the price.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <?php
    $payment_gateways = pms_get_payment_gateways();

    if( count( $payment_gateways ) > 1 ) {

        // Checkboxes to select active Payment Gateways
        echo '<div class="pms-form-field-wrapper pms-form-field-active-payment-gateways">';
            echo '<label class="pms-form-field-label">' . __( 'Active Payment Gateways', 'paid-member-subscriptions' ) . '</label>';

            foreach( $payment_gateways as $payment_gateway_slug => $payment_gateways_details ) {
                echo '<label>';
                echo '<input type="checkbox" name="pms_payments_settings[active_pay_gates][]" value="' . esc_attr( $payment_gateway_slug ) . '" ' . ( !empty( $this->options['active_pay_gates'] ) && in_array( $payment_gateway_slug, $this->options['active_pay_gates'] ) ? 'checked="checked"' : '' ) . '/>';
                echo esc_html( $payment_gateways_details['display_name_admin'] );
                echo '</label><br>';
            }
        echo '</div>';

        do_action( $this->menu_slug . '_payment_general_after_gateway_checkboxes', $this->options );

        $default_gateway = '';

        if ( empty( $this->options['default_payment_gateway'] ) && !empty( $this->options['active_pay_gates'][0] ) )
            $default_gateway = $this->options['active_pay_gates'][0];
        else
            $default_gateway = $this->options['default_payment_gateway'];

        // Select the default active Payment Gateway
        echo '<div class="pms-form-field-wrapper">';

            echo '<label class="pms-form-field-label" for="default-payment-gateway">' . __( 'Default Payment Gateway', 'paid-member-subscriptions' ) . '</label>';

            echo '<select id="default-payment-gateway" name="pms_payments_settings[default_payment_gateway]">';
                foreach( $payment_gateways as $payment_gateway_slug => $payment_gateways_details ) {

                    echo '<option value="' . esc_attr( $payment_gateway_slug ) . '" ' . selected( $default_gateway, $payment_gateway_slug, false ) . '>' . esc_html( $payment_gateways_details['display_name_admin'] ) . '</option>';
                }
            echo '</select>';

        echo '</div>';

        // Select renewal type if payment gateways support this
        if( pms_payment_gateways_support( pms_get_payment_gateways( true ), 'recurring_payments' ) ) {

            echo '<div class="pms-form-field-wrapper">';
                echo '<label class="pms-form-field-label" for="payment-recurring">' . __( 'Renewal', 'paid-member-subscriptions' ) . '</label>';

                echo '<select id="payment-recurring" name="pms_payments_settings[recurring]" class="widefat">';

                    echo '<option value="1" ' . ( isset( $this->options['recurring'] ) && ( $this->options['recurring'] == 1 ) ? 'selected' : '' ) . '>' . __( 'Customer opts in for automatic renewal', 'paid-member-subscriptions' ) . '</option>';
                    echo '<option value="2" ' . ( isset( $this->options['recurring'] ) && ( $this->options['recurring'] == 2 ) ? 'selected' : '' ) . '>' . __( 'Always renew automatically', 'paid-member-subscriptions' ) . '</option>';
                    echo '<option value="3" ' . ( isset( $this->options['recurring'] ) && ( $this->options['recurring'] == 3 ) ? 'selected' : '' ) . '>' . __( 'Never renew automatically', 'paid-member-subscriptions' ) . '</option>';

                echo '</select>';

                echo '<p class="description">' . __( 'Select renewal type. You can either allow the customer to opt in or force automatic renewal.', 'paid-member-subscriptions' ) . '</p>';

            echo '</div>';

        }

    }

    ?>

    <?php do_action( $this->menu_slug . '_payment_general_after_content', $this->options ); ?>

</div>


<div id="pms-settings-payment-gateways">

    <h3><?php _e( 'Payment Gateways', 'paid-member-subscriptions' ); ?></h3>

    <?php do_action( $this->menu_slug . '_payment_gateways_content', $this->options ); ?>

    <?php do_action( $this->menu_slug . '_payment_gateways_after_content', $this->options ); ?>

</div>
