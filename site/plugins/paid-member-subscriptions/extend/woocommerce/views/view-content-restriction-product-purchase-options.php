<?php
/*
 * HTML output for content restriction meta-box regarding product purchase options
 */
?>
<h4><?php echo __( 'Purchase Options', 'paid-member-subscriptions' ); ?></h4>

<!-- Who Can Purchase? options -->
<div class="pms-meta-box-field-wrapper">
    <label class="pms-meta-box-field-label"><?php _e( 'Who can purchase?', 'paid-member-subscriptions' ); ?></label>

    <?php
    $user_status          = get_post_meta( $post_id, 'pms-purchase-restrict-user-status', true );
    $subscription_plans   = pms_get_subscription_plans();
    $selected_subscription_plans = get_post_meta( $post_id, 'pms-purchase-restrict-subscription-plan' );
    ?>

    <label class="pms-meta-box-checkbox-label" for="pms-purchase-restrict-user-status">
        <input type="checkbox" value="loggedin" <?php if( ! empty( $user_status ) ) checked($user_status, 'loggedin' ); ?> name="pms-purchase-restrict-user-status" id="pms-purchase-restrict-user-status">
        <?php echo __( 'Logged In Users', 'paid-member-subscriptions' ); ?>
    </label>

    <?php if( !empty( $subscription_plans ) ): foreach( $subscription_plans as $subscription_plan ): ?>

        <label class="pms-meta-box-checkbox-label" for="pms-purchase-restrict-subscription-plan-<?php echo $subscription_plan->id ?>">
            <input type="checkbox" value="<?php echo $subscription_plan->id; ?>" <?php if( in_array( $subscription_plan->id, $selected_subscription_plans ) ) echo 'checked="checked"'; ?> name="pms-purchase-restrict-subscription-plan[]" id="pms-purchase-restrict-subscription-plan-<?php echo $subscription_plan->id ?>">
            <?php echo esc_html($subscription_plan->name); ?>
        </label>

    <?php endforeach; ?>

        <p class="description" style="margin-top: 10px;">
            <?php echo __( 'Select who can purchase this product.', 'paid-member-subscriptions' ); ?>
        </p>

    <?php endif; ?>

</div>
