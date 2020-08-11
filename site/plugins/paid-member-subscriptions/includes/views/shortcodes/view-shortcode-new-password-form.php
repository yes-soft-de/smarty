<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HTML output for recover password - enter new password form
 *
 */
?>

<form id="pms_new_password_form" class="pms-form" method="post">

    <?php wp_nonce_field( 'pms_new_password_form_nonce', 'pmstkn' ); ?>

    <?php
    $pms_newpass_notification = '<p>' . __( 'Please enter your new password.', 'paid-member-subscriptions' ) . '</p>';
    echo apply_filters( 'pms_new_password_message', $pms_newpass_notification );
    ?>

    <ul class="pms-form-fields-wrapper">

        <?php do_action( 'pms_new_password_form_before_fields' ); ?>

        <?php $field_errors = pms_errors()->get_error_messages('pms_repeat_password'); ?>

        <li class="pms-field">
            <label for="pms_new_password"><?php echo apply_filters( 'pms_recover_password_form_label_new_password', __( 'Password', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_new_password" name="pms_new_password" class="password" type="password" value="" autocomplete="off" />
        </li>

        <li class="pms-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
            <label for="pms_repeat_password"><?php echo apply_filters( 'pms_recover_password_form_label_repeat_password', __( 'Repeat Password', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_repeat_password" name="pms_repeat_password" class="password" type="password" value="" autocomplete="off" />

            <?php pms_display_field_errors( $field_errors ); ?>
        </li>

        <?php do_action( 'pms_new_password_form_after_fields' ); ?>

    </ul>

    <?php do_action( 'pms_new_password_form_bottom' ); ?>

    <input type="submit" name="submit" value="<?php _e( 'Reset Password', 'paid-member-subscriptions' ); ?>"/>

</form>
