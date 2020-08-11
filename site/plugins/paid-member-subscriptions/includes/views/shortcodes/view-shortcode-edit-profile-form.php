<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * HTML output for the edit profile
 *
 */
?>

<form id="pms_edit-profile-form" class="pms-form" method="POST">

    <?php
        $user = get_userdata( pms_get_current_user_id() );

        wp_nonce_field( 'pms_edit_profile_form_nonce', 'pmstkn' );

        pms_display_success_messages( pms_success()->get_messages('edit_profile') );
        pms_display_field_errors( pms_errors()->get_error_messages('edit_profile') );
    ?>

    <ul class="pms-form-fields-wrapper">

        <?php do_action( 'pms_edit_profile_form_before_fields' ); ?>

        <li class="pms-field pms-user-login-field">
            <label for="pms_user_login"><?php echo apply_filters( 'pms_edit_profile_form_label_user_login', __( 'Username *', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_user_login" name="user_login" type="text" value="<?php echo sanitize_text_field( $user->user_login ); ?>" disabled />
        </li>

        <?php $field_errors = pms_errors()->get_error_messages('user_email'); ?>
        <li class="pms-field pms-user-email-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
            <label for="pms_user_email"><?php echo apply_filters( 'pms_register_form_label_user_email', __( 'E-mail *', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_user_email" name="user_email" type="text" value="<?php echo sanitize_email( $user->user_email ); ?>" />

            <?php pms_display_field_errors( $field_errors ); ?>
        </li>

        <?php $field_errors = pms_errors()->get_error_messages('first_name'); ?>
        <li class="pms-field pms-first-name-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
            <label for="pms_first_name"><?php echo apply_filters( 'pms_register_form_label_first_name', __( 'First Name', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_first_name" name="first_name" type="text" value="<?php echo sanitize_text_field( $user->first_name ); ?>" />

            <?php pms_display_field_errors( $field_errors ); ?>
        </li>

        <?php $field_errors = pms_errors()->get_error_messages('last_name'); ?>
        <li class="pms-field pms-last-name-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
            <label for="pms_last_name"><?php echo apply_filters( 'pms_register_form_label_last_name', __( 'Last Name', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_last_name" name="last_name" type="text" value="<?php echo sanitize_text_field( $user->last_name ); ?>" />

            <?php pms_display_field_errors( $field_errors ); ?>
        </li>

        <?php $field_errors = pms_errors()->get_error_messages('pass1'); ?>
        <li class="pms-field pms-pass1-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
            <label for="pms_pass1"><?php echo apply_filters( 'pms_register_form_label_pass1', __( 'Password', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_pass1" name="pass1" type="password" />

            <?php pms_display_field_errors( $field_errors ); ?>
        </li>

        <?php $field_errors = pms_errors()->get_error_messages('pass2'); ?>
        <li class="pms-field pms-pass2-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
            <label for="pms_pass2"><?php echo apply_filters( 'pms_register_form_label_pass2', __( 'Repeat Password', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_pass2" name="pass2" type="password" />

            <?php pms_display_field_errors( $field_errors ); ?>
        </li>


        <?php
        $gdpr_settings = pms_get_gdpr_settings();
        if( !empty( $gdpr_settings ) ){
            if( !empty( $gdpr_settings['gdpr_delete'] ) && $gdpr_settings['gdpr_delete'] === 'enabled' ){
                 ?>
                <li class="pms-field">
                    <label for="pms-delete-account"><?php _e( 'Delete account and data', 'paid-member-subscriptions' ) ?>
                        <input id="pms-delete-account" type="submit" value=<?php _e( "Delete", 'paid-member-subscriptions' ) ?> />
                    </label>
                </li>
                <?php
            }
        }
        ?>


        <?php do_action( 'pms_edit_profile_form_after_fields' ); ?>

        <li>
            <input name="pms_edit_profile" type="submit" value="<?php echo apply_filters( 'pms_edit_profile_form_submit_text', __( 'Edit Profile', 'paid-member-subscriptions' ) ); ?>" />
        </li>
    </ul>

</form>
