<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * HTML Output for the Email Settings tab
 */
?>

<!-- Available Email Merge Tags -->
<?php $available_merge_tags = PMS_Merge_Tags::get_merge_tags(); ?>

<div id="pms-settings-emails">
    <div id="pms-available-tags">

        <h3><?php _e( 'Available Tags', 'paid-member-subscriptions' ); ?></h3>

        <?php foreach( $available_merge_tags as $available_merge_tag ):?>
            <input readonly spellcheck="false" type="text" class="pms-tag input" value="{{<?php echo $available_merge_tag; ?>}}">
        <?php endforeach; ?>

    </div>

    <?php $active_sub_tab = ( ! empty( $_GET['nav_sub_tab'] ) ? sanitize_text_field( $_GET['nav_sub_tab'] ) : 'user_emails' ); ?>

    <!-- Sub-tab navigation -->
    <ul class="pms-nav-sub-tab-wrapper subsubsub">
        <li><a data-sub-tab-slug="user_emails"  href="<?php echo admin_url( add_query_arg( array( 'page' => 'pms-settings-page', 'tab' => 'emails', 'nav_sub_tab' => 'user_emails' ), 'admin.php' ) ); ?>" class="nav-sub-tab <?php echo ( $active_sub_tab == 'user_emails' ? 'current' : '' ) ?>"><?php _e( 'Member Emails', 'paid-member-subscriptions' ); ?></a> | </li>
        <li><a data-sub-tab-slug="admin_emails" href="<?php echo admin_url( add_query_arg( array( 'page' => 'pms-settings-page', 'tab' => 'emails', 'nav_sub_tab' => 'admin_emails' ), 'admin.php' ) ); ?>" class="nav-sub-tab <?php echo ( $active_sub_tab == 'admin_emails' ? 'current' : '' ) ?>"><?php _e( 'Administrator Emails', 'paid-member-subscriptions' ); ?></a></li>
    </ul>

    <!-- Divider -->
    <hr style="margin-top: 9px;" />

    <!-- User Emails Sub Tab -->
    <div data-sub-tab-slug="user_emails" class="pms-sub-tab pms-sub-tab-user <?php echo ( $active_sub_tab == 'user_emails' ? 'tab-active' : '' ); ?>">

        <?php do_action( $this->menu_slug . '_tab_emails_before_user_tab', $this->options ); ?>

        <!-- General Email Options -->
        <?php $email_general_options = PMS_Emails::get_email_general_options(); ?>

        <h3><?php _e( 'General Email Options', 'paid-member-subscriptions' ); ?></h3>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="email-from-name"><?php _e( 'From Name', 'paid-member-subscriptions' ) ?></label>
            <input type="text" id="email-from-name" class="widefat" name="pms_emails_settings[email-from-name]" value="<?php echo ( isset($this->options['email-from-name']) ? esc_attr( $this->options['email-from-name'] ) : esc_attr( $email_general_options['email-from-name'] ) ) ?>">
        </div>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="email-from-email"><?php _e( 'From Email', 'paid-member-subscriptions' ) ?></label>
            <input type="text" id="email-from-email" class="widefat" name="pms_emails_settings[email-from-email]" value="<?php echo ( isset($this->options['email-from-email']) ? esc_attr( $this->options['email-from-email'] ) : esc_attr( $email_general_options['email-from-email'] ) ) ?>">
        </div>


        <?php $email_actions  = PMS_Emails::get_email_actions(); ?>
        <?php $email_headings = PMS_Emails::get_email_headings(); ?>
        <?php $email_subjects = PMS_Emails::get_default_email_subjects( 'user' ); ?>
        <?php $email_content  = PMS_Emails::get_default_email_content( 'user' ); ?>

        <!-- Register Email -->
        <div class="pms-heading-wrap">
            <h3><?php echo $email_headings['register']; ?></h3>

            <label for="register-is-enabled">
                <input type="checkbox" id="register-is-enabled" name="pms_emails_settings[register_is_enabled]" value="yes" <?php echo ( isset( $this->options['register_is_enabled'] ) ? 'checked' : '' ); ?> />

                <?php _e( 'Enable email', 'paid-member-subscriptions' ); ?>
            </label>
        </div>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="email-register-subject"><?php _e( 'Subject', 'paid-member-subscriptions' ) ?></label>
            <input type="text" id="email-register-subject" class="widefat" name="pms_emails_settings[register_sub_subject]" value="<?php echo ( isset($this->options['register_sub_subject']) ? esc_attr( $this->options['register_sub_subject'] ) : esc_attr( $email_subjects['register'] ) ) ?>">
        </div>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="emails_register_sub"><?php _e( 'Content', 'paid-member-subscriptions' ) ?></label>
            <?php wp_editor( ( isset($this->options['register_sub']) ? $this->options['register_sub'] : $email_content['register'] ), 'emails_register_sub', array( 'textarea_name' => 'pms_emails_settings[register_sub]', 'editor_height' => 250 ) ); ?>
        </div>

        <!-- Other Emails -->
        <?php if( ( $key = array_search( 'register', $email_actions)) !== false) unset( $email_actions[$key] ); ?>

        <?php foreach( $email_actions as $action ): ?>

            <div class="pms-heading-wrap">
                <h3><?php echo $email_headings[$action]; ?></h3>

                <label for="<?php echo $action; ?>-is-enabled">
                    <input type="checkbox" id="<?php echo $action; ?>-is-enabled" name="pms_emails_settings[<?php echo $action; ?>_is_enabled]" value="yes" <?php echo ( isset( $this->options[$action . '_is_enabled'] ) ? 'checked' : '' ); ?> />

                    <?php _e( 'Enable email', 'paid-member-subscriptions' ); ?>
                </label>
            </div>

            <div class="pms-form-field-wrapper">
                <label class="pms-form-field-label" for="email-<?php echo $action ?>-sub-subject"><?php _e( 'Subject', 'paid-member-subscriptions' ) ?></label>
                <input type="text" id="email-<?php echo $action ?>-sub-subject" class="widefat" name="pms_emails_settings[<?php echo $action ?>_sub_subject]" value="<?php echo ( isset($this->options[$action.'_sub_subject']) ? esc_attr( $this->options[$action.'_sub_subject'] ) : esc_attr( $email_subjects[$action] ) ) ?>">
            </div>

            <div class="pms-form-field-wrapper">
                <label class="pms-form-field-label" for="emails-<?php echo $action ?>-sub"><?php _e( 'Content', 'paid-member-subscriptions' ) ?></label>
                <?php wp_editor( ( isset($this->options[$action.'_sub']) ? $this->options[$action.'_sub'] : $email_content[$action] ), 'emails-'. $action .'-sub', array( 'textarea_name' => 'pms_emails_settings['.$action.'_sub]', 'editor_height' => 250 ) ); ?>
            </div>

        <?php endforeach; ?>

        <?php do_action( $this->menu_slug . '_tab_emails_after_user_tab', $this->options ); ?>

    </div>


    <!-- Admin Emails Sub Tab -->
    <div data-sub-tab-slug="admin_emails" class="pms-sub-tab pms-sub-tab-admin <?php echo ( $active_sub_tab == 'admin_emails' ? 'tab-active' : '' ); ?>">

        <?php do_action( $this->menu_slug . '_tab_emails_before_admin_tab', $this->options ); ?>

        <!-- General Email Options -->
        <?php $email_general_options = PMS_Emails::get_email_general_options(); ?>

        <h3><?php _e( 'Enable Administrator Emails', 'paid-member-subscriptions' ); ?></h3>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="emails-admin-on"><?php _e( 'Send Administrator Emails', 'paid-member-subscriptions' ) ?></label>
            <p class="description"><input type="checkbox" id="emails-admin-on" name="pms_emails_settings[admin_emails_on]" value="1" <?php echo ( isset( $this->options['admin_emails_on'] ) ? 'checked' : '' ); ?> /><?php _e( 'By checking this option administrator emails are enabled.', 'paid-member-subscriptions' ); ?></p>
        </div>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="emails-admin"><?php _e( 'Administrator Emails', 'paid-member-subscriptions' ); ?></label>
            <input type="text" id="emails-admin" class="widefat" name="pms_emails_settings[admin_emails]" value="<?php echo ( isset($this->options['admin_emails']) ? esc_attr( $this->options['admin_emails'] ) : '' ); ?>">
            <p class="description"><?php _e( 'Add a list of email addresses, separated by comma, that you wish to receive emails for member subscription status changes.', 'paid-member-subscriptions' ); ?></p>
        </div>

        <?php $email_actions  = PMS_Emails::get_email_actions(); ?>
        <?php $email_headings = PMS_Emails::get_email_headings(); ?>
        <?php $email_subjects = PMS_Emails::get_default_email_subjects( 'admin' ); ?>
        <?php $email_content  = PMS_Emails::get_default_email_content( 'admin' ); ?>

        <!-- Register Email -->
        <div class="pms-heading-wrap">
            <h3><?php echo $email_headings['register']; ?></h3>

            <label for="register-admin-is-enabled">
                <input type="checkbox" id="register-admin-is-enabled" name="pms_emails_settings[register_admin_is_enabled]" value="yes" <?php echo ( isset( $this->options['register_admin_is_enabled'] ) ? 'checked' : '' ); ?> />

                <?php _e( 'Enable email', 'paid-member-subscriptions' ); ?>
            </label>
        </div>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="email-register-subject"><?php _e( 'Subject', 'paid-member-subscriptions' ) ?></label>
            <input type="text" id="email-register-subject" class="widefat" name="pms_emails_settings[register_sub_subject_admin]" value="<?php echo ( isset($this->options['register_sub_subject_admin']) ? esc_attr( $this->options['register_sub_subject_admin'] ) : esc_attr( $email_subjects['register'] ) ) ?>">
        </div>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="emails_register_sub"><?php _e( 'Content', 'paid-member-subscriptions' ) ?></label>
            <?php wp_editor( ( isset( $this->options['register_sub_admin'] ) ? $this->options['register_sub_admin'] : $email_content['register'] ), 'emails_register_sub_admin', array( 'textarea_name' => 'pms_emails_settings[register_sub_admin]', 'editor_height' => 250 ) ); ?>
        </div>

        <!-- Other Emails -->
        <?php if( ( $key = array_search( 'register', $email_actions)) !== false) unset( $email_actions[$key] ); ?>

        <?php foreach( $email_actions as $action ): ?>

            <div class="pms-heading-wrap">
                <h3><?php echo $email_headings[$action]; ?></h3>

                <label for="<?php echo $action; ?>-admin-is-enabled">
                    <input type="checkbox" id="<?php echo $action; ?>-admin-is-enabled" name="pms_emails_settings[<?php echo $action; ?>_admin_is_enabled]" value="yes" <?php echo ( isset( $this->options[$action . '_admin_is_enabled'] ) ? 'checked' : '' ); ?> />

                    <?php _e( 'Enable email', 'paid-member-subscriptions' ); ?>
                </label>
            </div>

            <div class="pms-form-field-wrapper">
                <label class="pms-form-field-label" for="email-<?php echo $action ?>-sub-subject-admin"><?php _e( 'Subject', 'paid-member-subscriptions' ) ?></label>
                <input type="text" id="email-<?php echo $action ?>-sub-subject-admin" class="widefat" name="pms_emails_settings[<?php echo $action ?>_sub_subject_admin]" value="<?php echo ( isset($this->options[$action.'_sub_subject_admin']) ? esc_attr( $this->options[$action.'_sub_subject_admin'] ) : esc_attr( $email_subjects[$action] ) ); ?>">
            </div>

            <div class="pms-form-field-wrapper">
                <label class="pms-form-field-label" for="emails-<?php echo $action ?>-sub-admin"><?php _e( 'Content', 'paid-member-subscriptions' ) ?></label>
                <?php wp_editor( ( isset($this->options[$action.'_sub_admin']) ? $this->options[$action.'_sub_admin'] : $email_content[$action] ), 'emails-'. $action .'-sub-admin', array( 'textarea_name' => 'pms_emails_settings['.$action.'_sub_admin]', 'editor_height' => 250 ) ); ?>
            </div>

        <?php endforeach; ?>

        <?php do_action( $this->menu_slug . '_tab_emails_after_admin_tab', $this->options ); ?>

    </div>

    <?php do_action( $this->menu_slug . '_tab_emails_after_content', $this->options ); ?>
</div>
