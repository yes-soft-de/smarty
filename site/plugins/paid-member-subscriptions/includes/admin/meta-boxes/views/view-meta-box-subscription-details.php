<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

    /*
     * HTML output for subscription plan details meta-box
     */
?>

<?php do_action( 'pms_view_meta_box_subscription_details_top', $subscription_plan->id ); ?>

<!-- Description -->
<div class="pms-meta-box-field-wrapper">

    <label for="pms-subscription-plan-description" class="pms-meta-box-field-label"><?php _e( 'Description', 'paid-member-subscriptions' ); ?></label>

    <textarea id="pms-subscription-plan-description" name="pms_subscription_plan_description" class="widefat" placeholder="<?php _e( 'Write description', 'paid-member-subscriptions' ); ?>"><?php echo esc_html( $subscription_plan->description ); ?></textarea>
    <p class="description"><?php _e( 'A description for this subscription plan. This will be displayed on the register form.', 'paid-member-subscriptions' ); ?></p>

</div>

<?php do_action( 'pms_view_meta_box_subscription_details_description_bottom', $subscription_plan->id ); ?>

<!-- Duration -->
<div class="pms-meta-box-field-wrapper">

    <label for="pms-subscription-plan-duration" class="pms-meta-box-field-label"><?php _e( 'Duration', 'paid-member-subscriptions' ); ?></label>

    <input type="text" id="pms-subscription-plan-duration" name="pms_subscription_plan_duration" value="<?php echo $subscription_plan->duration; ?>" />

    <select id="pms-subscription-plan-duration-unit" name="pms_subscription_plan_duration_unit">
        <option value="day"   <?php selected( 'day', $subscription_plan->duration_unit, true ); ?>><?php _e( 'Day(s)', 'paid-member-subscriptions' ); ?></option>
        <option value="week"  <?php selected( 'week', $subscription_plan->duration_unit, true ); ?>><?php _e( 'Week(s)', 'paid-member-subscriptions' ); ?></option>
        <option value="month" <?php selected( 'month', $subscription_plan->duration_unit, true ); ?>><?php _e( 'Month(s)', 'paid-member-subscriptions' ); ?></option>
        <option value="year"  <?php selected( 'year', $subscription_plan->duration_unit, true ); ?>><?php _e( 'Year(s)', 'paid-member-subscriptions' ); ?></option>
    </select>
    <p class="description"><?php _e( 'Set the subscription duration. Leave 0 for unlimited.', 'paid-member-subscriptions' ); ?></p>

</div>

<?php do_action( 'pms_view_meta_box_subscription_details_duration_bottom', $subscription_plan->id ); ?>

<!-- Price -->
<div class="pms-meta-box-field-wrapper">

    <label for="pms-subscription-plan-price" class="pms-meta-box-field-label"><?php _e( 'Price', 'paid-member-subscriptions' ); ?></label>

    <input type="text" id="pms-subscription-plan-price" name="pms_subscription_plan_price" class="small" value="<?php echo esc_attr( $subscription_plan->price ); ?>" /> <?php echo pms_get_active_currency(); ?>

    <p class="description"><?php _e( 'Amount you want to charge people who join this plan. Leave 0 if you want this plan to be free.', 'paid-member-subscriptions' ); ?></p>

</div>

<?php do_action( 'pms_view_meta_box_subscription_details_price_bottom', $subscription_plan->id ); ?>

<!-- Sign Up Fee -->
<?php if( pms_payment_gateways_support( pms_get_active_payment_gateways(), 'subscription_sign_up_fee' ) ) : ?>
    <div class="pms-meta-box-field-wrapper">

        <label for="pms-subscription-plan-sign-up-fee" class="pms-meta-box-field-label"><?php _e( 'Sign-up Fee', 'paid-member-subscriptions' ); ?></label>

        <input type="text" id="pms-subscription-plan-sign-up-fee" name="pms_subscription_plan_sign_up_fee" class="small" value="<?php echo $subscription_plan->sign_up_fee; ?>" /> <?php echo pms_get_active_currency(); ?>

        <p class="description"><?php _e( 'Amount you want to charge people upfront when subscribing to this plan.', 'paid-member-subscriptions' ); ?></p>

    </div>

    <?php do_action( 'pms_view_meta_box_subscription_details_sign_up_fee_bottom', $subscription_plan->id ); ?>
<?php else : ?>
    <div class="pms-meta-box-field-wrapper">

        <label for="pms-subscription-plan-sign-up-fee" class="pms-meta-box-field-label"><?php _e( 'Sign-up Fee', 'paid-member-subscriptions' ); ?></label>

        <span class="pms-disabled-input">0</span><?php echo pms_get_active_currency(); ?>

        <p class="description"><?php printf( __( 'This feature is available only with the Manual, %1$sStripe%2$s or %3$sPayPal Express with Reference Transactions%4$s gateways.', 'paid-member-subscriptions' ), '<a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/add-ons/stripe-payment-gateway/" target="_blank">', '</a>', '<a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/add-ons/paypal-pro-and-express-checkout/#Reference_Transactions" target="_blank">', '</a>' ); ?></p>

    </div>
<?php endif; ?>

<!-- Free trial -->
<?php if( pms_payment_gateways_support( pms_get_active_payment_gateways(), 'subscription_free_trial' ) ) : ?>
    <div class="pms-meta-box-field-wrapper">

        <label for="pms-subscription-plan-trial-duration" class="pms-meta-box-field-label"><?php _e( 'Free Trial', 'paid-member-subscriptions' ); ?></label>

        <input type="text" id="pms-subscription-plan-trial-duration" name="pms_subscription_plan_trial_duration" value="<?php echo $subscription_plan->trial_duration; ?>" />

        <select id="pms-subscription-plan-trial-duration-unit" name="pms_subscription_plan_trial_duration_unit">
            <option value="day"   <?php selected( 'day', $subscription_plan->trial_duration_unit, true ); ?>><?php _e( 'Day(s)', 'paid-member-subscriptions' ); ?></option>
            <option value="week"  <?php selected( 'week', $subscription_plan->trial_duration_unit, true ); ?>><?php _e( 'Week(s)', 'paid-member-subscriptions' ); ?></option>
            <option value="month" <?php selected( 'month', $subscription_plan->trial_duration_unit, true ); ?>><?php _e( 'Month(s)', 'paid-member-subscriptions' ); ?></option>
            <option value="year"  <?php selected( 'year', $subscription_plan->trial_duration_unit, true ); ?>><?php _e( 'Year(s)', 'paid-member-subscriptions' ); ?></option>
        </select>
        <p class="description"><?php _e( 'The free trial represents the amount of time before charging the first recurring payment. The sign-up fee applies regardless of the free trial.', 'paid-member-subscriptions' ); ?></p>

    </div>

    <?php do_action( 'pms_view_meta_box_subscription_details_free_trial_bottom', $subscription_plan->id ); ?>
<?php else : ?>
    <div class="pms-meta-box-field-wrapper">

        <label for="pms-subscription-plan-trial-duration" class="pms-meta-box-field-label"><?php _e( 'Free Trial', 'paid-member-subscriptions' ); ?></label>

        <span class="pms-disabled-input">0</span><?php echo pms_get_active_currency(); ?>

        <select id="pms-subscription-plan-trial-duration-unit" disabled>
            <option value="day"   <?php selected( 'day', $subscription_plan->trial_duration_unit, true ); ?>><?php _e( 'Day(s)', 'paid-member-subscriptions' ); ?></option>
            <option value="week"  <?php selected( 'week', $subscription_plan->trial_duration_unit, true ); ?>><?php _e( 'Week(s)', 'paid-member-subscriptions' ); ?></option>
            <option value="month" <?php selected( 'month', $subscription_plan->trial_duration_unit, true ); ?>><?php _e( 'Month(s)', 'paid-member-subscriptions' ); ?></option>
            <option value="year"  <?php selected( 'year', $subscription_plan->trial_duration_unit, true ); ?>><?php _e( 'Year(s)', 'paid-member-subscriptions' ); ?></option>
        </select>
        <p class="description"><?php printf( __( 'This feature is available only with the Manual, %1$sStripe%2$s or %3$sPayPal Express with Reference Transactions%4$s gateways.', 'paid-member-subscriptions' ), '<a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/add-ons/stripe-payment-gateway/" target="_blank">', '</a>', '<a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/add-ons/paypal-pro-and-express-checkout/#Reference_Transactions" target="_blank">', '</a>' ); ?></p>

    </div>

    <?php do_action( 'pms_view_meta_box_subscription_details_free_trial_bottom', $subscription_plan->id ); ?>
<?php endif; ?>

<!-- Renewal option -->
<?php if( pms_payment_gateways_support( pms_get_active_payment_gateways(), 'recurring_payments' ) ) : ?>

    <div class="pms-meta-box-field-wrapper">
        <label for="pms-subscription-plan-recurring" class="pms-meta-box-field-label"><?php _e( 'Renewal', 'paid-member-subscriptions' ); ?></label>

        <select id="pms-subscription-plan-recurring" name="pms_subscription_plan_recurring">

            <option value="0" <?php echo ( empty( $subscription_plan->recurring ) ? 'selected' : '' ); ?> ><?php _e( 'Settings default', 'paid-member-subscriptions' ); ?></option>
            <option value="1" <?php echo ( isset( $subscription_plan->recurring ) && ( $subscription_plan->recurring == 1 ) ? 'selected' : '' ); ?> ><?php _e( 'Customer opts in for automatic renewal', 'paid-member-subscriptions' ); ?></option>
            <option value="2" <?php echo ( isset( $subscription_plan->recurring ) && ( $subscription_plan->recurring == 2 ) ? 'selected' : '' ); ?> ><?php _e( 'Always renew automatically', 'paid-member-subscriptions' ); ?></option>
            <option value="3" <?php echo ( isset( $subscription_plan->recurring ) && ( $subscription_plan->recurring == 3 ) ? 'selected' : '' ); ?> ><?php _e( 'Never renew automatically', 'paid-member-subscriptions' ); ?></option>

        </select>

        <p class="description"><?php _e( 'Select renewal type. You can either allow the customer to opt in, force automatic renewal or force no renewal.', 'paid-member-subscriptions' ); ?></p>

    </div>

    <?php do_action( 'pms_view_meta_box_subscription_details_renewal_bottom', $subscription_plan->id ); ?>

<?php endif; ?>

<!-- Status -->
<div class="pms-meta-box-field-wrapper">

    <label for="pms-subscription-plan-status" class="pms-meta-box-field-label"><?php _e( 'Status', 'paid-member-subscriptions' ); ?></label>

    <select id="pms-subscription-plan-status" name="pms_subscription_plan_status">
        <option value="active" <?php selected( 'active', $subscription_plan->status, true  ); ?>><?php _e( 'Active', 'paid-member-subscriptions' ); ?></option>
        <option value="inactive" <?php selected( 'inactive', $subscription_plan->status, true  ); ?>><?php _e( 'Inactive', 'paid-member-subscriptions' ); ?></option>
    </select>
    <p class="description"><?php _e( 'Only active subscription plans will be displayed to the user.', 'paid-member-subscriptions' ); ?></p>

</div>

<?php do_action( 'pms_view_meta_box_subscription_details_status_bottom', $subscription_plan->id ); ?>

<!-- User Role -->
<div class="pms-meta-box-field-wrapper">

    <label for="pms-subscription-plan-user-role" class="pms-meta-box-field-label"><?php _e( 'User role', 'paid-member-subscriptions' ); ?></label>

    <select id="pms-subscription-plan-user-role" name="pms_subscription_plan_user_role">

        <?php
            if( !pms_user_role_exists( 'pms_subscription_plan_' . $subscription_plan->id ) )
                echo '<option value="create-new">' . __( '... Create new user role from this Subscription Plan', 'paid-member-subscriptions' ) . '</option>';
            else
                echo '<option value="pms_subscription_plan_' . $subscription_plan->id . '" ' . selected( 'pms_subscription_plan_' . $subscription_plan->id, $subscription_plan->user_role, false) . '>' . pms_get_user_role_name( 'pms_subscription_plan_' . $subscription_plan->id ) . '</option>';
        ?>

        <?php foreach( pms_get_user_role_names() as $role_slug => $role_name ): ?>
            <option value="<?php echo esc_attr( $role_slug ); ?>" <?php selected( $role_slug, $subscription_plan->user_role, true); ?> ><?php echo esc_html( $role_name ); ?></option>
        <?php endforeach; ?>

    </select>
    <p class="description"><?php _e( 'Select which user role to associate with this subscription plan.', 'paid-member-subscriptions' ); ?></p>

</div>

<?php do_action( 'pms_view_meta_box_subscription_details_user_role_bottom', $subscription_plan->id ); ?>

<?php do_action( 'pms_view_meta_box_subscription_details_bottom', $subscription_plan->id ); ?>
