<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<form method="post">
    <h3><?php _e( 'Currency', 'paid-member-subscriptions' ); ?></h3>

    <h4><?php _e( 'What currency do you want to accept payments in ?', 'paid-member-subscriptions' ); ?></h4>
    <select id="payment-currency" name="pms_payments_currency">
        <?php
        foreach( pms_get_currencies() as $currency_code => $currency )
            echo '<option value="' . esc_attr( $currency_code ) . '"' . selected( pms_get_active_currency(), $currency_code, false ) . '>' . esc_html( $currency ) . '</option>';
        ?>
    </select>

    <h3><?php _e( 'Payment Gateways', 'paid-member-subscriptions' ); ?></h3>

    <div class="pms-setup-pages">

        <div class="pms-setup-gateway">
            <div class="pms-setup-gateway__logo">
                <img src="<?php echo PMS_PLUGIN_DIR_URL . '/assets/images/pms-paypal-logo.png'; ?>" />
            </div>
            <div class="pms-setup-gateway__description"><?php _e( 'Safe and secure payments handled by PayPal using the customers account.', 'paid-member-subscriptions' ); ?></div>
            <div class="pms-setup-toggle">
                <input type="checkbox" name="pms_gateway_paypal_standard" id="pms_gateway_paypal_standard" value="1" <?php echo $this->check_gateway( 'paypal_standard' ) ? 'checked' : '' ?>/><label for="pms_gateway_paypal_standard">Toggle</label>
            </div>
        </div>

        <div class="pms-setup-gateway pms-setup-gateway-extra">
            <div class="pms-setup-gateway__logo">

            </div>

            <div class="pms-setup-gateway__description pms-setup-gateway__description-extra">
                <div class="pms-setup-gateway__description">
                    <label class="pms-setup-label" for="pms_gateway_paypal_email_address"><?php _e( 'PayPal Email Address', 'paid-member-subscriptions' ); ?></label>
                    <input type="email" name="pms_gateway_paypal_email_address" id="pms_gateway_paypal_email_address" value="<?php echo pms_get_paypal_email(); ?>" />
                </div>
                <div>
                    <?php echo wp_kses( __( 'For payments to work correctly, you will also need to <strong>setup the IPN URL in your PayPal account</strong>.', 'paid-member-subscriptions' ), $this->kses_args ); ?>
                    <a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/member-payments/#IPN_for_PayPal_gateways" target="_blank">
                        <?php _e( 'Learn More', 'paid-member-subscriptions' ); ?>
                    </a>.
                </div>
            </div>

            <div class="pms-setup-toggle"></div>
        </div>

        <div class="pms-setup-gateway">
            <div class="pms-setup-gateway__logo">
                <?php _e( 'Offline Payments', 'paid-member-subscriptions' ); ?>
            </div>
            <div class="pms-setup-gateway__description">
                <?php _e( 'Manually collect payments from your customers through Checks, Direct Bank Transfers or in person cash.', 'paid-member-subscriptions' ); ?>
            </div>
            <div class="pms-setup-toggle">
                <input type="checkbox" name="pms_gateway_offline" id="pms_gateway_offline" value="1" <?php echo $this->check_gateway( 'manual' ) ? 'checked' : '' ?>/><label for="pms_gateway_offline">Toggle</label>
            </div>
        </div>

        <div class="pms-setup-gateway pms-setup-fade">
            <div class="pms-setup-gateway__logo">
                <img src="<?php echo PMS_PLUGIN_DIR_URL . '/assets/images/pms-stripe.png'; ?>" />
            </div>
            <div class="pms-setup-gateway__description">
                <?php _e( 'Collect direct credit or debit card payments on your website.', 'paid-member-subscriptions' ); ?>
            </div>
            <div class="pms-setup-toggle">
                <input type="checkbox" name="pms_gateway_stripe" id="pms_gateway_stripe" disabled /><label for="pms_gateway_stripe">Toggle</label>
            </div>
        </div>

        <div class="pms-setup-gateway pms-setup-fade">
            <div class="pms-setup-gateway__logo">
                <img src="<?php echo PMS_PLUGIN_DIR_URL . '/assets/images/pms-paypal-pro-express-logo.png'; ?>" />
            </div>
            <div class="pms-setup-gateway__description"><?php _e( 'PayPal Express Checkout payments using credit cards or customer accounts handled by PayPal.', 'paid-member-subscriptions' ); ?></div>
            <div class="pms-setup-toggle">
                <input type="checkbox" name="pms_gateway_paypal_pro_express" id="pms_gateway_paypal_pro_express" disabled /><label for="pms_gateway_paypal_pro_express">Toggle</label>
            </div>
        </div>

        <div class="pms-setup-gateway">
            <div class="pms-setup-gateway__upsell">
                <?php echo wp_kses( __( 'Additional <strong>Payment Gateways</strong> and <strong>Recurring Subscriptions</strong> are available with a Pro licence of Paid Member Subscriptions.', 'paid-member-subscriptions' ), $this->kses_args ); ?>
                <a href="https://www.cozmoslabs.com/wordpress-paid-member-subscriptions/?utm_source=wpbackend&utm_medium=pms-setup-wizard&utm_campaign=PMSFree" target="_blank">
                    <?php _e( 'Learn More', 'paid-member-subscriptions' ); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="pms-setup-form-button">
        <input type="submit" class="button primary button-primary button-hero" value="<?php _e( 'Continue', 'paid-member-subscriptions' ); ?>" />
    </div>

    <?php wp_nonce_field( 'pms-setup-wizard-nonce', 'pms_setup_wizard_nonce' ); ?>
</form>
