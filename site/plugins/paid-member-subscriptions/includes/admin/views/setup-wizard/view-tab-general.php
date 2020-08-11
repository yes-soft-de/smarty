<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<h3><?php _e( 'Membership Pages', 'paid-member-subscriptions' ); ?></h3>
<div class="pms-setup-pages">
    <h4><?php _e( 'Do you want to create the Membership Pages automatically ?', 'paid-member-subscriptions' ); ?></h4>

    <div class="pms-setup-pages__line">
        <?php if( $this->show_pages_button() ) : ?>
            <button class="button primary button-primary button-hero" id="pms_create_subscription_pages"><?php _e( 'Yes, create the pages for me', 'paid-member-subscriptions' ); ?></button>
        <?php else : ?>
            <a href="<?php echo admin_url( 'edit.php?s=%5Bpms-&post_status=all&post_type=page&action=-1&m=0&paged=1&action2=-1' ); ?>" target="_blank" class="button secondary button-secondary button-hero"><?php _e( 'View Pages', 'paid-member-subscriptions' ); ?></a>
        <?php endif; ?>

        <div class="pms-setup-pages__success pms-setup-hidden"><?php _e( 'Membership Pages created successfully !', 'paid-member-subscriptions' ); ?></div>
    </div>

    <p class="description"><?php _e( 'This will create the pages for Registration, Login, Account and Reset Password automatically.', 'paid-member-subscriptions' ); ?></p>
</div>

<h3 style="margin-bottom:0;"><?php _e( 'Settings', 'paid-member-subscriptions' ); ?></h3>

<form class="pms-setup-form" method="post">
    <div class="pms-setup-line-wrap">
        <h4><?php _e( 'Would you like to automatically log users in after registration ?', 'paid-member-subscriptions' ); ?></h4>

        <div class="pms-setup-toggle">
            <input type="checkbox" name="pms_automatically_login" id="pms_automatically_login" value="1" <?php echo $this->check_value( 'automatically_log_in' ) ? 'checked' : '' ?> /><label for="pms_automatically_login">Toggle</label>
        </div>
    </div>

    <div class="pms-setup-line-wrap">
        <h4><?php _e( 'Prevent users from being logged in with the same account from multiple places at the same time ?', 'paid-member-subscriptions' ); ?></h4>

        <div class="pms-setup-toggle">
            <input type="checkbox" name="pms_account_sharing" id="pms_account_sharing" value="1" <?php echo $this->check_value( 'prevent_account_sharing' ) ? 'checked' : '' ?>  /><label for="pms_account_sharing">Toggle</label>
        </div>
    </div>
    <p class="description"><?php _e( 'If the current user\'s session has been taken over by a newer session, we will log him out and he will have to login again. This will make it inconvenient for members to share their login credentials.', 'paid-member-subscriptions' ); ?></p>

    <div class="pms-setup-line-wrap">
        <h4><?php _e( 'Would you like to redirect the default WordPress pages for register, login and password reset ?', 'paid-member-subscriptions' ); ?></h4>

        <div class="pms-setup-toggle">
            <input type="checkbox" name="pms_redirect_default" id="pms_redirect_default" value="1" <?php echo $this->show_pages_button() ? 'disabled' : ''; ?> <?php echo $this->check_value( 'redirect_default_wp' ) ? 'checked' : '' ?> /><label for="pms_redirect_default" <?php echo $this->show_pages_button() ? 'style="opacity:0.4"' : ''; ?>>Toggle</label>
        </div>
    </div>
    <p class="description"><?php _e( 'The pages will be redirected to their front-end counterparts created automatically above. Can be activated later from settings.', 'paid-member-subscriptions' ); ?></p>

    <div class="pms-setup-form-button">
        <input type="submit" class="button primary button-primary button-hero" value="<?php _e( 'Continue', 'paid-member-subscriptions' ); ?>" />
    </div>

    <?php wp_nonce_field( 'pms-setup-wizard-nonce', 'pms_setup_wizard_nonce' ); ?>
</form>
