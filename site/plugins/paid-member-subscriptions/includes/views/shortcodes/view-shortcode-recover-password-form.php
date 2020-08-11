<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HTML output for recover password form
 *
 */
?>

<form id="pms_recover_password_form" class="pms-form" method="post">

    <?php wp_nonce_field( 'pms_recover_password_form_nonce', 'pmstkn' ); ?>

    <?php
        $pms_recover_notification = '<p>' . __( 'Please enter your username or email address.', 'paid-member-subscriptions' );
        $pms_recover_notification .= '<br/>'.__( 'You will receive a link to create a new password via email.', 'paid-member-subscriptions' ).'</p>';

        echo apply_filters( 'pms_recover_password_message', $pms_recover_notification );
        ?>

    <ul class="pms-form-fields-wrapper">

        <?php do_action( 'pms_recover_password_form_before_fields' ); ?>

        <?php $field_errors = pms_errors()->get_error_messages('pms_username_email'); ?>
        <li class="pms-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
            <label for="pms_username_email"><?php echo apply_filters( 'pms_recover_password_form_label_username_email', __( 'Username or Email', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_username_email" name="pms_username_email" type="text" value="<?php echo ( isset( $_POST['pms_username_email'] ) ? esc_attr( $_POST['pms_username_email'] ) : '' ); ?>" />

            <?php pms_display_field_errors( $field_errors ); ?>
        </li>

        <?php do_action( 'pms_recover_password_form_after_fields' ); ?>

    </ul>

    <?php do_action( 'pms_recover_password_form_bottom' ); ?>

    <input type="submit" name="submit" value="<?php _e( 'Reset Password', 'paid-member-subscriptions' ); ?>"/>

</form>



