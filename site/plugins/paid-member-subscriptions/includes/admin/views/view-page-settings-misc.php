<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * HTML Output for the Payments tab
 */

?>

<div id="gdpr-general">

    <h3><?php _e( 'GDPR', 'paid-member-subscriptions' ); ?></h3>

    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="gdpr-checkbox"><?php _e( 'GDPR checkbox on Forms', 'paid-member-subscriptions' ) ?></label>

        <select id="gdpr-checkbox" name="pms_misc_settings[gdpr][gdpr_checkbox]">
            <option value="disabled" <?php ( isset( $this->options['gdpr']['gdpr_checkbox'] ) ? selected( $this->options['gdpr']['gdpr_checkbox'], 'disabled', true ) : ''); ?>><?php _e( 'Disabled', 'paid-member-subscriptions' ); ?></option>
            <option value="enabled" <?php ( isset( $this->options['gdpr']['gdpr_checkbox'] ) ? selected( $this->options['gdpr']['gdpr_checkbox'], 'enabled', true ) : ''); ?>><?php _e( 'Enabled', 'paid-member-subscriptions' ); ?></option>
        </select>

        <p class="description"><?php _e( 'Select whether to show a GDPR checkbox on our forms.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="gdpr-checkbox-text"><?php _e( 'GDPR Checkbox Text', 'paid-member-subscriptions' ) ?></label>
        <input type="text" id="gdpr-checkbox-text" class="widefat" name="pms_misc_settings[gdpr][gdpr_checkbox_text]" value="<?php echo ( isset($this->options['gdpr']['gdpr_checkbox_text']) ? esc_attr( $this->options['gdpr']['gdpr_checkbox_text'] ) : __( 'I allow the website to collect and store the data I submit through this form. *', 'paid-member-subscriptions' ) ); ?>">
        <p class="description"><?php _e( 'Text for the GDPR checkbox. You can use {{privacy_policy}} to generate a link for the Privacy policy page.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="gdpr-delete-button"><?php _e( 'GDPR Delete Button on Forms', 'paid-member-subscriptions' ) ?></label>

        <select id="gdpr-delete-button" name="pms_misc_settings[gdpr][gdpr_delete]">
            <option value="disabled" <?php ( isset( $this->options['gdpr']['gdpr_delete'] ) ? selected( $this->options['gdpr']['gdpr_delete'], 'disabled', true ) : ''); ?>><?php _e( 'Disabled', 'paid-member-subscriptions' ); ?></option>
            <option value="enabled" <?php ( isset( $this->options['gdpr']['gdpr_delete'] ) ? selected( $this->options['gdpr']['gdpr_delete'], 'enabled', true ) : ''); ?>><?php _e( 'Enabled', 'paid-member-subscriptions' ); ?></option>
        </select>

        <p class="description"><?php _e( 'Select whether to show a GDPR Delete button on our forms.', 'paid-member-subscriptions' ); ?></p>
    </div>

    <h3><?php _e( 'Others', 'paid-member-subscriptions' ); ?></h3>

    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="allow-usage-tracking"><?php _e( 'Usage Tracking' , 'paid-member-subscriptions' ) ?></label>

        <p class="description">
            <input type="checkbox" id="allow-usage-tracking" name="pms_misc_settings[allow-usage-tracking]" value="1" <?php echo ( isset( $this->options['allow-usage-tracking'] ) ? 'checked' : '' ); ?> /><?php printf( __( 'Allow Paid Member Subscriptions to anonymously track the plugin\'s usage. Data provided by this tracking helps us improve the plugin.<br> No sensitive data is shared. %sLearn More%s', 'paid-member-subscriptions' ), '<a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/usage-tracking/" target="_blank">', '</a>' ); ?>
        </p>
    </div>

    <div class="pms-form-field-wrapper">
        <label class="pms-form-field-label" for="hide-admin-bar"><?php _e( 'Admin Bar' , 'paid-member-subscriptions' ) ?></label>

        <p class="description">
            <input type="checkbox" id="hide-admin-bar" name="pms_misc_settings[hide-admin-bar]" value="1" <?php echo ( isset( $this->options['hide-admin-bar'] ) ? 'checked' : '' ); ?> /><?php _e( 'Hide admin bar', 'paid-member-subscriptions' ); ?>
        </p>
        <p class="description">
            <?php _e( 'By checking this option, the admin bar will be removed from all logged in users except Administrators.', 'paid-member-subscriptions' ); ?>
        </p>
    </div>

    <?php do_action( $this->menu_slug . '_misc_after_content', $this->options ); ?>

</div>
