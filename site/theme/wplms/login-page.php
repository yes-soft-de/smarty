<?php
/**
 * Template Name: Login Page (Site Lock)
 */
global $bp;

if(is_user_logged_in()){
    wp_redirect(bp_loggedin_user_domain());
    exit();
}

get_header(vibe_get_header());

if ( have_posts() ) : while ( have_posts() ) : the_post();

$id = vibe_get_bp_page_id('register');
if(empty($id)){
    $id = get_the_ID();
}
$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($id), 'full' );
?>
<style>header,footer,#headertop,#footerbottom{display:none;}</style>
<div class="row">
    <div class="col-md-6 col-sm-6 hidden-xs">
        <div class="login_featured_wrap" style="background-image:url(<?php echo $thumb['0']; ?>);">
            <div class="login_page_featured">
                <h1><?php echo get_the_title($id); ?></h1>
                <?php the_sub_title($id); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-6">
        <div class="login_page_content">
            <?php the_content(); ?>

             <ul class="nav nav-tabs">
                <li class="active"><a href="#login_page_login" data-toggle="tab"><?php echo _x('Login','Login page template login','vibe')?></a></li>
                <?php if(get_option( 'users_can_register' )){ ?>
                <li><a href="#login_page_register" id="login_page_register_tab" data-toggle="tab"><?php echo _x('Register','Login page template - Register','vibe')?></a></li>
                <?php } ?>
                <?php do_action('wplms_login_page_tabs'); ?>
            </ul>

          <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="login_page_login">
                    <form name="login-form" id="vbp-login-form" class="standard-form" action="<?php echo apply_filters('wplms_login_widget_action',site_url( 'wp-login.php', 'login_post' )); ?>" method="post">
                        <div class="list-group list-group-sm">
                            <div class="list-group-item">
                              <label><?php _e('USERNAME','vibe'); ?></label>  
                              <input type="text" name="log" placeholder="<?php _e('Enter Username','vibe'); ?>" class="form-control no-border" required="" tabindex="0" aria-required="true" aria-invalid="true">
                            </div>
                            <div class="list-group-item">
                               <label><?php _e('PASSWORD','vibe'); ?></label>  
                               <input type="password" name="pwd" placeholder="<?php _e('Enter Password','vibe'); ?>" class="form-control no-border" required="" tabindex="0" aria-required="true" aria-invalid="true">
                            </div>
                      </div>
                      <div>
                      <a id="forgot_password_trigger" class="pull-right" href="<?php echo wp_lostpassword_url(); ?>"><?php _e('Forgot Password?','vibe'); ?></a>
                      </div>
                      <div class="checkbox" style="display:inline-block;margin-top:0;">
                        <input type="checkbox" id="rememberme" name="rememberme" value="forever">
                        <label for="rememberme"><?php _e('Remember me','vibe'); ?></label>
                      </div>

                        <input type="submit" name="user-submit" id="sidebar-wp-submit" value="<?php _e( 'Log In','vibe' ); ?>" tabindex="100" />
                        <input type="hidden" name="user-cookie" value="1" />
                        <?php do_action( 'login_form' ); //BruteProtect FIX ?>
                        <div class="line line-dashed"></div>
                        <?php do_action( 'bp_after_sidebar_login_form' ); ?>
                    </form>
                </div>

                <?php if(get_option( 'users_can_register' )){ ?>
                <div role="tabpanel" class="tab-pane" id="login_page_register">

                    <?php //REGISTRATION page ?>
                    <?php

                /**
                 * Fires at the top of the BuddyPress member registration page template.
                 *
                 * @since 1.1.0
                 */
                do_action( 'bp_before_register_page' ); ?>

                <div class="page" id="register-page">

                    <form action="" name="signup_form" id="signup_form" class="standard-form" method="post" enctype="multipart/form-data">

                    <?php if ( 'registration-disabled' == bp_get_current_signup_step() ) : ?>
                        <?php

                        /** This action is documented in bp-templates/bp-legacy/buddypress/activity/index.php */
                        do_action( 'template_notices' ); ?>
                        <?php

                        /**
                         * Fires before the display of the registration disabled message.
                         *
                         * @since 1.5.0
                         */
                        do_action( 'bp_before_registration_disabled' ); ?>

                            <p><?php _e( 'User registration is currently not allowed.', 'vibe' ); ?></p>

                        <?php

                        /**
                         * Fires after the display of the registration disabled message.
                         *
                         * @since 1.5.0
                         */
                        do_action( 'bp_after_registration_disabled' ); ?>
                    <?php endif; // registration-disabled signup step ?>

                    <?php if ( 'request-details' == bp_get_current_signup_step() ) : ?>

                        <?php

                        /** This action is documented in bp-templates/bp-legacy/buddypress/activity/index.php */
                        do_action( 'template_notices' ); ?>

                        <p><?php _e( 'Registering for this site is easy. Just fill in the fields below, and we\'ll get a new account set up for you in no time.', 'vibe' ); ?></p>

                        <?php

                        /**
                         * Fires before the display of member registration account details fields.
                         *
                         * @since 1.1.0
                         */
                        do_action( 'bp_before_account_details_fields' ); ?>

                        <div class="register-section" id="basic-details-section">

                            <?php /***** Basic Account Details ******/ ?>

                            <h4><?php _e( 'Account Details', 'vibe' ); ?></h4>
                            <div<?php bp_field_css_class( 'editfield' ); ?>>
                            <label for="signup_username"><?php _e( 'Username', 'vibe' ); ?> <?php _e( '(required)', 'vibe' ); ?></label>
                            <?php

                            /**
                             * Fires and displays any member registration username errors.
                             *
                             * @since 1.1.0
                             */
                            do_action( 'bp_signup_username_errors' ); ?>
                            <input type="text" name="signup_username" id="signup_username" value="<?php bp_signup_username_value(); ?>" <?php bp_form_field_attributes( 'username' ); ?>/>
                            </div>
                            <div<?php bp_field_css_class( 'editfield' ); ?>>
                            <label for="signup_email"><?php _e( 'Email Address', 'vibe' ); ?> <?php _e( '(required)', 'vibe' ); ?></label>
                            <?php

                            /**
                             * Fires and displays any member registration email errors.
                             *
                             * @since 1.1.0
                             */
                            do_action( 'bp_signup_email_errors' ); ?>
                            <input type="email" name="signup_email" id="signup_email" value="<?php bp_signup_email_value(); ?>" <?php bp_form_field_attributes( 'email' ); ?>/>
                            </div>
                            <div<?php bp_field_css_class( 'editfield' ); ?>>
                            <label for="signup_password"><?php _e( 'Choose a Password', 'vibe' ); ?> <?php _e( '(required)', 'vibe' ); ?></label>
                            <?php

                            /**
                             * Fires and displays any member registration password errors.
                             *
                             * @since 1.1.0
                             */
                            do_action( 'bp_signup_password_errors' ); ?>
                            <input type="password" name="signup_password" id="signup_password" value="" class="password-entry form_field" <?php bp_form_field_attributes( 'password' ); ?>/>
                            <div id="pass-strength-result"></div>
                            </div>
                            <div<?php bp_field_css_class( 'editfield' ); ?>>
                            <label for="signup_password_confirm"><?php _e( 'Confirm Password', 'vibe' ); ?> <?php _e( '(required)', 'vibe' ); ?></label>
                            <?php

                            /**
                             * Fires and displays any member registration password confirmation errors.
                             *
                             * @since 1.1.0
                             */
                            do_action( 'bp_signup_password_confirm_errors' ); ?>
                            <input type="password" name="signup_password_confirm" id="signup_password_confirm" value="" class="password-entry-confirm" <?php bp_form_field_attributes( 'password' ); ?>/>
                            </div>
                            <?php

                            /**
                             * Fires and displays any extra member registration details fields.
                             *
                             * @since 1.9.0
                             */
                            do_action( 'bp_account_details_fields' ); ?>

                        </div><!-- #basic-details-section -->

                        <?php

                        /**
                         * Fires after the display of member registration account details fields.
                         *
                         * @since 1.1.0
                         */
                        do_action( 'bp_after_account_details_fields' ); ?>

                        <?php /***** Extra Profile Details ******/ ?>

                        <?php if ( bp_is_active( 'xprofile' ) ) : ?>

                            <?php

                            /**
                             * Fires before the display of member registration xprofile fields.
                             *
                             * @since 1.2.4
                             */
                            do_action( 'bp_before_signup_profile_fields' ); ?>

                            <div class="register-section" id="profile-details-section">

                                <h4><?php _e( 'Profile Details', 'vibe' ); ?></h4>

                                <?php /* Use the profile field loop to render input fields for the 'base' profile field group */ ?>
                                <?php if ( bp_is_active( 'xprofile' ) ) : if ( bp_has_profile( array( 'profile_group_id' => 1, 'fetch_field_data' => false ) ) ) : while ( bp_profile_groups() ) : bp_the_profile_group(); ?>

                                <?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>

                                    <div<?php bp_field_css_class( 'editfield' ); ?>>

                                        <?php
                                        $field_type = bp_xprofile_create_field_type( bp_get_the_profile_field_type() );
                                        $field_type->edit_field_html();

                                        /**
                                         * Fires before the display of the visibility options for xprofile fields.
                                         *
                                         * @since 1.7.0
                                         */
                                        do_action( 'bp_custom_profile_edit_fields_pre_visibility' );

                                        if ( bp_current_user_can( 'bp_xprofile_change_field_visibility' ) ) : ?>
                                            <p class="field-visibility-settings-toggle" id="field-visibility-settings-toggle-<?php bp_the_profile_field_id() ?>">
                                                <?php
                                                printf(
                                                    __( 'This field can be seen by: %s', 'vibe' ),
                                                    '<span class="current-visibility-level">' . bp_get_the_profile_field_visibility_level_label() . '</span>'
                                                );
                                                ?>
                                                <a href="#" class="visibility-toggle-link"><?php _ex( 'Change', 'Change profile field visibility level', 'vibe' ); ?></a>
                                            </p>

                                            <div class="field-visibility-settings" id="field-visibility-settings-<?php bp_the_profile_field_id() ?>">
                                                <fieldset>
                                                    <legend><?php _e( 'Who can see this field?', 'vibe' ) ?></legend>

                                                    <?php bp_profile_visibility_radio_buttons() ?>

                                                </fieldset>
                                                <a class="field-visibility-settings-close" href="#"><?php _e( 'Close', 'vibe' ) ?></a>

                                            </div>
                                        <?php else : ?>
                                            <p class="field-visibility-settings-notoggle" id="field-visibility-settings-toggle-<?php bp_the_profile_field_id() ?>">
                                                <?php
                                                printf(
                                                    __( 'This field can be seen by: %s', 'vibe' ),
                                                    '<span class="current-visibility-level">' . bp_get_the_profile_field_visibility_level_label() . '</span>'
                                                );
                                                ?>
                                            </p>
                                        <?php endif ?>

                                        <?php

                                        /**
                                         * Fires after the display of the visibility options for xprofile fields.
                                         *
                                         * @since 1.1.0
                                         */
                                        do_action( 'bp_custom_profile_edit_fields' ); ?>

                                        <?php
                                         //now buddypress already show descption below the field since 2.9 
                                        if(function_exists('version_compare') && !empty($bp->version) && !version_compare($bp->version, '2.9.0')){

                                        }else{
                                            ?>
                                            <p class="description"><?php bp_the_profile_field_description(); ?></p> 
                                            <?php
                                        }
                                        
                                        ?>

                                    </div>

                                <?php endwhile; ?>

                                <input type="hidden" name="signup_profile_field_ids" id="signup_profile_field_ids" value="<?php bp_the_profile_field_ids(); ?>" />

                                <?php endwhile; endif; endif; ?>

                                <?php

                                /**
                                 * Fires and displays any extra member registration xprofile fields.
                                 *
                                 * @since 1.9.0
                                 */
                                do_action( 'bp_signup_profile_fields' ); ?>

                            </div><!-- #profile-details-section -->

                            <?php

                            /**
                             * Fires after the display of member registration xprofile fields.
                             *
                             * @since 1.1.0
                             */
                            do_action( 'bp_after_signup_profile_fields' ); ?>

                        <?php endif; ?>

                        <?php if ( bp_get_blog_signup_allowed() ) : ?>

                            <?php

                            /**
                             * Fires before the display of member registration blog details fields.
                             *
                             * @since 1.1.0
                             */
                            do_action( 'bp_before_blog_details_fields' ); ?>

                            <?php /***** Blog Creation Details ******/ ?>

                            <div class="register-section" id="blog-details-section">

                                <h4><?php _e( 'Blog Details', 'vibe' ); ?></h4>

                                <p><label for="signup_with_blog"><input type="checkbox" name="signup_with_blog" id="signup_with_blog" value="1"<?php if ( (int) bp_get_signup_with_blog_value() ) : ?> checked="checked"<?php endif; ?> /> <?php _e( 'Yes, I\'d like to create a new site', 'vibe' ); ?></label></p>

                                <div id="blog-details"<?php if ( (int) bp_get_signup_with_blog_value() ) : ?>class="show"<?php endif; ?>>

                                    <label for="signup_blog_url"><?php _e( 'Blog URL', 'vibe' ); ?> <?php _e( '(required)', 'vibe' ); ?></label>
                                    <?php

                                    /**
                                     * Fires and displays any member registration blog URL errors.
                                     *
                                     * @since 1.1.0
                                     */
                                    do_action( 'bp_signup_blog_url_errors' ); ?>

                                    <?php if ( is_subdomain_install() ) : ?>
                                        http:// <input type="text" name="signup_blog_url" id="signup_blog_url" value="<?php bp_signup_blog_url_value(); ?>" /> .<?php bp_signup_subdomain_base(); ?>
                                    <?php else : ?>
                                        <?php echo home_url( '/' ); ?> <input type="text" name="signup_blog_url" id="signup_blog_url" value="<?php bp_signup_blog_url_value(); ?>" />
                                    <?php endif; ?>

                                    <label for="signup_blog_title"><?php _e( 'Site Title', 'vibe' ); ?> <?php _e( '(required)', 'vibe' ); ?></label>
                                    <?php

                                    /**
                                     * Fires and displays any member registration blog title errors.
                                     *
                                     * @since 1.1.0
                                     */
                                    do_action( 'bp_signup_blog_title_errors' ); ?>
                                    <input type="text" name="signup_blog_title" id="signup_blog_title" value="<?php bp_signup_blog_title_value(); ?>" />

                                    <span class="label"><?php _e( 'I would like my site to appear in search engines, and in public listings around this network.', 'vibe' ); ?></span>
                                    <?php

                                    /**
                                     * Fires and displays any member registration blog privacy errors.
                                     *
                                     * @since 1.1.0
                                     */
                                    do_action( 'bp_signup_blog_privacy_errors' ); ?>

                                    <label for="signup_blog_privacy_public"><input type="radio" name="signup_blog_privacy" id="signup_blog_privacy_public" value="public"<?php if ( 'public' == bp_get_signup_blog_privacy_value() || !bp_get_signup_blog_privacy_value() ) : ?> checked="checked"<?php endif; ?> /> <?php _e( 'Yes', 'vibe' ); ?></label>
                                    <label for="signup_blog_privacy_private"><input type="radio" name="signup_blog_privacy" id="signup_blog_privacy_private" value="private"<?php if ( 'private' == bp_get_signup_blog_privacy_value() ) : ?> checked="checked"<?php endif; ?> /> <?php _e( 'No', 'vibe' ); ?></label>

                                    <?php

                                    /**
                                     * Fires and displays any extra member registration blog details fields.
                                     *
                                     * @since 1.9.0
                                     */
                                    do_action( 'bp_blog_details_fields' ); ?>

                                </div>

                            </div><!-- #blog-details-section -->

                            <?php

                            /**
                             * Fires after the display of member registration blog details fields.
                             *
                             * @since 1.1.0
                             */
                            do_action( 'bp_after_blog_details_fields' ); ?>

                        <?php endif; ?>

                        <?php

                        /**
                         * Fires before the display of the registration submit buttons.
                         *
                         * @since 1.1.0
                         */
                        do_action( 'bp_before_registration_submit_buttons' ); ?>

                        <div class="submit">
                            <input type="submit" name="signup_submit" id="signup_submit" value="<?php _ex( 'Complete Sign Up','sign up button on site lock', 'vibe' ); ?>" />
                        </div>

                        <?php

                        /**
                         * Fires after the display of the registration submit buttons.
                         *
                         * @since 1.1.0
                         */
                        do_action( 'bp_after_registration_submit_buttons' ); ?>

                        <?php wp_nonce_field( 'bp_new_signup' ); ?>

                    <?php endif; // request-details signup step ?>

                    <?php if ( 'completed-confirmation' == bp_get_current_signup_step() ) : ?>

                        <?php

                        /** This action is documented in bp-templates/bp-legacy/buddypress/activity/index.php */
                        do_action( 'template_notices' ); ?>
                        <?php

                        /**
                         * Fires before the display of the registration confirmed messages.
                         *
                         * @since 1.5.0
                         */
                        do_action( 'bp_before_registration_confirmed' ); ?>

                        <?php if ( bp_registration_needs_activation() ) : ?>
                            <h3 class="heading"><span><?php _e('Activate your account','vibe'); ?></span></h3>
                            <p><?php _e( 'You have successfully created your account! To begin using this site you will need to activate your account via the email we have just sent to your address.', 'vibe' ); ?></p>
                        <?php else : ?>
                            <h3 class="heading"><span><?php _e('Account creatd, login to your account','vibe'); ?></span></h3>
                            <p><?php _e( 'You have successfully created your account! Please log in using the username and password you have just created.', 'vibe' ); ?></p>
                        <?php endif; ?>

                        <?php

                        /**
                         * Fires after the display of the registration confirmed messages.
                         *
                         * @since 1.5.0
                         */
                        do_action( 'bp_after_registration_confirmed' ); ?>

                    <?php endif; // completed-confirmation signup step ?>

                    <?php

                    /**
                     * Fires and displays any custom signup steps.
                     *
                     * @since 1.1.0
                     */
                    do_action( 'bp_custom_signup_steps' ); ?>

                    </form>

                </div>

                <?php

                /**
                 * Fires at the bottom of the BuddyPress member registration page template.
                 *
                 * @since 1.1.0
                 */
                do_action( 'bp_after_register_page' ); ?>
                    <?php //REGISTRATION page ?>
                </div>
                <?php } ?>
                <?php do_action('wplms_login_page_tab_content'); ?>
            </div>
        </div>
    </div>    
</div>
<?php
endwhile;
endif; 
if(!empty($_POST['signup_submit'])){ 
?>
<script>
jQuery(window).load(function(){
   jQuery('#login_page_register_tab').tab('show');
});
</script>
<?php
}
get_footer( vibe_get_footer() );