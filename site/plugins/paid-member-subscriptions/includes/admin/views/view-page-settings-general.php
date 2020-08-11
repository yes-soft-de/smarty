<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * HTML Output for the General Settings tab
 */
?>

<div class="pms-form-fields">
    <!-- Load CSS -->
    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="use-pms-css"><?php _e( 'Load CSS' , 'paid-member-subscriptions' ) ?></label>

        <p class="description"><input type="checkbox" id="use-pms-css" name="pms_general_settings[use_pms_css]" value="1" <?php echo ( isset( $this->options['use_pms_css'] ) ? 'checked' : '' ); ?> /><?php _e( 'Use Paid Member Subscriptions\'s own CSS in the front-end.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <!-- Automatically Log In -->
    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="automatically-log-in"><?php _e( 'Automatically Log In', 'paid-member-subscriptions' ) ?></label>

        <select id="automatically-log-in" name="pms_general_settings[automatically_log_in]" >
            <option value="0" <?php ( isset( $this->options['automatically_log_in'] ) ? selected( $this->options['automatically_log_in'], '0', true ) : ''); ?> ><?php _e( 'No', 'paid-member-subscriptions' ) ?></option>
            <option value="1" <?php ( isset( $this->options['automatically_log_in'] ) ? selected( $this->options['automatically_log_in'], '1', true ) : ''); ?> ><?php _e( 'Yes', 'paid-member-subscriptions' ) ?></option>
        </select>

        <p class="description"><?php _e( 'Select "Yes" to automatically log in new members after successful registration.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <!-- Prevent Account Sharing -->
    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="prevent-account-sharing"><?php _e( 'Prevent Account Sharing' , 'paid-member-subscriptions' ) ?></label>

        <p class="description"><input type="checkbox" id="prevent-account-sharing" name="pms_general_settings[prevent_account_sharing]" value="1" <?php echo ( isset( $this->options['prevent_account_sharing'] ) ? 'checked' : '' ); ?> /><?php _e( 'Prevent users from being logged in with the same account from multiple places at the same time. ', 'paid-member-subscriptions' ); ?></p>
        <p class="description"><?php _e( 'If the current user\'s session has been taken over by a newer session, we will log him out and he will have to login again. This will make it inconvenient for members to share their login credentials.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <!-- Redirect Default WordPress Pages -->
    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="redirect-default-wp"><?php _e( 'Redirect Default WordPress Pages' , 'paid-member-subscriptions' ) ?></label>

        <p class="description"><input type="checkbox" id="redirect-default-wp" name="pms_general_settings[redirect_default_wp]" value="1" <?php echo ( isset( $this->options['redirect_default_wp'] ) ? 'checked' : '' ); ?> /><?php _e( 'Redirect users from the default WordPress login ( wp-login.php ), register and lost password forms to the front-end ones created with Paid Member Subscriptions.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <h4 class="pms-subsection-title"><?php _e( 'Membership Pages', 'paid-member-subscriptions' ); ?></h4>
    <p class="description"><?php _e( 'These pages need to be set so that Paid Member Subscriptions knows where to send users.', 'paid-member-subscriptions' ); ?></p>

    <!-- Register Success Page -->
    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="register-success-page"><?php _e( 'Register Success Page', 'paid-member-subscriptions' ) ?></label>

        <select id="register-success-page" name="pms_general_settings[register_success_page]" class="widefat">
            <option value="-1"><?php _e( 'Choose...', 'paid-member-subscriptions' ) ?></option>

            <?php
            foreach( get_pages() as $page )
                echo '<option value="' . esc_attr( $page->ID ) . '"' . ( isset( $this->options['register_success_page'] ) ? selected( $this->options['register_success_page'], $page->ID, false ) : '') . '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
            ?>
        </select>

        <?php if ( isset( $this->options['register_success_page'] ) && $this->options['register_success_page'] != -1 ) : ?>
            <a class="button" href="<?php echo get_permalink( $this->options['register_success_page'] ); ?>" target="_blank"><?php _e( 'View', 'paid-member-subscriptions' ); ?></a>
            <a class="button" href="<?php echo admin_url( 'post.php?post='. $this->options['register_success_page'] .'&action=edit' ); ?>" target="_blank"><?php _e( 'Edit', 'paid-member-subscriptions' ); ?></a>
        <?php endif; ?>

        <p class="description"><?php _e( 'Select the page where you wish to redirect your newly registered members.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <!-- Login Page -->
    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="login-page"><?php _e( 'Login Page', 'paid-member-subscriptions' ) ?></label>

        <select id="login-page" name="pms_general_settings[login_page]" class="widefat">
            <option value="-1"><?php _e( 'Choose...', 'paid-member-subscriptions' ) ?></option>

            <?php
            foreach( get_pages() as $page )
                echo '<option value="' . esc_attr( $page->ID ) . '"' . ( isset( $this->options['login_page'] ) ? selected( $this->options['login_page'], $page->ID, false ) : '') . '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
            ?>
        </select>

        <?php if ( isset( $this->options['login_page'] ) && $this->options['login_page'] != -1 ) : ?>
            <a class="button" href="<?php echo get_permalink( $this->options['login_page'] ); ?>" target="_blank"><?php _e( 'View', 'paid-member-subscriptions' ); ?></a>
            <a class="button" href="<?php echo admin_url( 'post.php?post='. $this->options['login_page'] .'&action=edit' ); ?>" target="_blank"><?php _e( 'Edit', 'paid-member-subscriptions' ); ?></a>
        <?php endif; ?>

        <p class="description"><?php _e( 'Select the page containing the <strong>[pms-login]</strong> shortcode.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <!-- Register Page -->
    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="register-page"><?php _e( 'Register Page', 'paid-member-subscriptions' ) ?></label>

        <select id="register-page" name="pms_general_settings[register_page]" class="widefat">
            <option value="-1"><?php _e( 'Choose...', 'paid-member-subscriptions' ) ?></option>

            <?php
            foreach( get_pages() as $page )
                echo '<option value="' . esc_attr( $page->ID ) . '"' . ( isset( $this->options['register_page'] ) ? selected( $this->options['register_page'], $page->ID, false ) : '') . '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
            ?>
        </select>

        <?php if ( isset( $this->options['register_page'] ) && $this->options['register_page'] != -1  ) : ?>
            <a class="button" href="<?php echo get_permalink( $this->options['register_page'] ); ?>" target="_blank"><?php _e( 'View', 'paid-member-subscriptions' ); ?></a>
            <a class="button" href="<?php echo admin_url( 'post.php?post='. $this->options['register_page'] .'&action=edit' ); ?>" target="_blank"><?php _e( 'Edit', 'paid-member-subscriptions' ); ?></a>
        <?php endif; ?>

        <p class="description"><?php _e( 'Select the page containing the <strong>[pms-register]</strong> shortcode.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <!-- Account -->
    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="account-page"><?php _e( 'Account Page', 'paid-member-subscriptions' ) ?></label>

        <select id="account-page" name="pms_general_settings[account_page]" class="widefat">
            <option value="-1"><?php _e( 'Choose...', 'paid-member-subscriptions' ) ?></option>

            <?php
            foreach( get_pages() as $page )
                echo '<option value="' . esc_attr( $page->ID ) . '"' . ( isset( $this->options['account_page'] ) ? selected( $this->options['account_page'], $page->ID, false ) : '') . '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
            ?>
        </select>

        <?php if ( isset( $this->options['account_page'] ) && $this->options['account_page'] != -1 ) : ?>
            <a class="button" href="<?php echo get_permalink( $this->options['account_page'] ); ?>" target="_blank"><?php _e( 'View', 'paid-member-subscriptions' ); ?></a>
            <a class="button" href="<?php echo admin_url( 'post.php?post='. $this->options['account_page'] .'&action=edit' ); ?>" target="_blank"><?php _e( 'Edit', 'paid-member-subscriptions' ); ?></a>
        <?php endif; ?>

        <p class="description"><?php _e( 'Select the page containing the <strong>[pms-account]</strong> shortcode.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <!-- Lost Password -->
    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="lost-password-page"><?php _e( 'Lost Password Page', 'paid-member-subscriptions' ) ?></label>

        <select id="lost-password-page" name="pms_general_settings[lost_password_page]" class="widefat">
            <option value="-1"><?php _e( 'Choose...', 'paid-member-subscriptions' ) ?></option>

            <?php
            foreach( get_pages() as $page )
                echo '<option value="' . esc_attr( $page->ID ) . '"' . ( isset( $this->options['lost_password_page'] ) ? selected( $this->options['lost_password_page'], $page->ID, false ) : '') . '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
            ?>
        </select>

        <?php if ( isset( $this->options['lost_password_page'] ) && $this->options['lost_password_page'] != -1 ) : ?>
            <a class="button" href="<?php echo get_permalink( $this->options['lost_password_page'] ); ?>" target="_blank"><?php _e( 'View', 'paid-member-subscriptions' ); ?></a>
            <a class="button" href="<?php echo admin_url( 'post.php?post='. $this->options['lost_password_page'] .'&action=edit' ); ?>" target="_blank"><?php _e( 'Edit', 'paid-member-subscriptions' ); ?></a>
        <?php endif; ?>

        <p class="description"><?php _e( 'Select the page containing the <strong>[pms-recover-password]</strong> shortcode.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <!-- Edit Profile -->
    <?php
        // make sure PB is active.
        if ( defined('PROFILE_BUILDER') ) :
    ?>
    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="edit-profile-shortcode"><?php _e( 'Edit Profile Form', 'paid-member-subscriptions' ) ?></label>

        <select id="edit-profile-shortcode" name="pms_general_settings[edit_profile_shortcode]" class="widefat">
            <option value="-1"><?php _e( 'Default Paid Member Subscriptions', 'paid-member-subscriptions' ) ?></option>
            <option value="wppb-default-edit-profile" <?php if ( isset($this->options['edit_profile_shortcode']) && $this->options['edit_profile_shortcode'] == 'wppb-default-edit-profile' ) echo 'selected'; ?>><?php _e( 'Default Profile Builder', 'paid-member-subscriptions' ); ?></option>
            <?php
            $args = array(
                'post_type' => 'wppb-epf-cpt',
                'post_status' => 'publish',
                'numberposts' => -1,
                'orderby' => 'date',
                'order' => 'DESC'
            );
            $edit_profile_forms = get_posts( $args );

            foreach ( $edit_profile_forms as $key => $value ){
                echo '<option value="'.$value->post_title.'"';
                if ( isset($this->options['edit_profile_shortcode']) && $this->options['edit_profile_shortcode'] == $value->post_title )
                    echo ' selected';

                echo '>' . $value->post_title . '</option>';
            }
            ?>

        </select>

        <p class="description"><?php _e( '<b>Profile Builder</b> is enabled. <b>You can replace the edit profile in the [pms-account] page</b> with the Profile Builder alternative.', 'paid-member-subscriptions' ); ?></p>
    </div>

        <?php endif;?>

</div>
