<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<h3><?php _e( 'Next Step', 'paid-member-subscriptions' ); ?></h3>

<div class="pms-setup-next">
    <h4><?php _e( 'Create Subscription Plans', 'paid-member-subscriptions' ); ?></h4>

    <div class="pms-setup-line-wrap">
        <p><?php _e( 'Configure your Subscription Plans and start selling them to your users.', 'paid-member-subscriptions' ); ?></p>

        <a href="<?php echo admin_url( 'edit.php?post_type=pms-subscription' ); ?>" target="_blank" class="button primary button-primary button-hero">Create Subscription Plans</a>
    </div>
</div>

<h3><?php _e( 'Additional Information', 'paid-member-subscriptions' ); ?></h3>

<div class="pms-setup-next">
    <h4><?php _e( 'Content Restriction', 'paid-member-subscriptions' ); ?></h4>

    <div class="pms-setup-line-wrap">
        <p><?php _e( 'Learn about the different ways in which you can restrict your premium content.', 'paid-member-subscriptions' ); ?></p>

        <a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/content-restriction/?utm_source=wpbackend&utm_medium=pms-setup-wizard&utm_campaign=PMSFree" target="_blank" class="button secondary button-secondary button-hero">
            <?php _e( 'Content Restriction', 'paid-member-subscriptions' ); ?>
        </a>
    </div>
</div>

<div class="pms-setup-next">
    <h4><?php _e( 'WooCommerce', 'paid-member-subscriptions' ); ?></h4>

    <div class="pms-setup-line-wrap">
        <p><?php _e( 'Paid Member Subscriptions integrates with WooCommerce allowing admins to restrict product viewing & purchasing, and also offer special product prices to members.', 'paid-member-subscriptions' ); ?></p>

        <a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/integration-with-other-plugins/woocommerce/?utm_source=wpbackend&utm_medium=pms-setup-wizard&utm_campaign=PMSFree" target="_blank" class="button secondary button-secondary button-hero">
            <?php _e( 'Read More', 'paid-member-subscriptions' ); ?>
        </a>
    </div>
</div>

<div class="pms-setup-links">
    <div class="pms-setup-line-wrap">
        <a href="<?php echo admin_url(); ?>" class="button secondary button-secondary button-hero"><?php _e( 'Visit Dashboard', 'paid-member-subscriptions' ); ?></a>
        <a href="<?php echo admin_url( 'admin.php?page=pms-settings-page&tab=general' ); ?>" class="button secondary button-secondary button-hero"><?php _e( 'Settings', 'paid-member-subscriptions' ); ?></a>
        <a href="<?php echo admin_url( 'edit.php?s=%5Bpms-&post_status=all&post_type=page&action=-1&m=0&paged=1&action2=-1' ); ?>" class="button secondary button-secondary button-hero"><?php _e( 'View Pages', 'paid-member-subscriptions' ); ?></a>
    </div>
</div>

<p class="pms-setup-text">
    To learn more about Paid Member Subscriptions, visit the <a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/?utm_source=wpbackend&utm_medium=pms-setup-wizard&utm_campaign=PMSFree" target="_blank">documentation</a>.
</p>
