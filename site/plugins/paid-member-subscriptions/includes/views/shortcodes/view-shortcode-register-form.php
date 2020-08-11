<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

    /*
     * HTML output for register form
     *
     * @param $atts     - is available from parent file, in the register_form method of the PMS_Shortcodes class
     */
    $form_name = 'register';

?>

<form id="pms_<?php echo $form_name; ?>-form" class="pms-form" method="POST">

    <?php do_action( 'pms_' . $form_name . '_form_top', $atts ); ?>

    <?php wp_nonce_field( 'pms_' . $form_name . '_form_nonce', 'pmstkn' ); ?>

    <ul class="pms-form-fields-wrapper">

        <?php

        // Start catching the subscription plan fields
        ob_start();

        $field_errors = pms_errors()->get_error_messages('subscription_plans');
        echo '<li class="pms-field pms-field-subscriptions ' . (!empty($field_errors) ? 'pms-field-error' : '') . '">';

        $subscription_plans = pms_get_subscription_plans();

        // Add nonce field when subscription_plans='none' (to allow users to register without becoming members, selecting a subscription plan)
        if( empty( $subscription_plans ) || ( isset( $atts['subscription_plans'][0] ) && ( strtolower($atts['subscription_plans'][0]) == 'none' ) ) )

            wp_nonce_field( 'pms_register_user_no_subscription_nonce','pmstkn2');

        else

            echo pms_output_subscription_plans( $atts['subscription_plans'], array(), false, (isset($atts['selected']) ? trim($atts['selected']) : ''), 'register' );

        echo '</li>';

        // Get the contents and clean
        $subscription_plans_field = ob_get_contents();
        ob_end_clean();

        // Display subscription plans at the bottom
        if( $atts['plans_position'] == 'top' )
            echo $subscription_plans_field;

        ?>


        <?php
            // Start catching the register form fields
            ob_start();
        ?>

        <?php do_action( 'pms_register_form_before_fields', $atts ); ?>

        <?php $field_errors = pms_errors()->get_error_messages('user_login'); ?>
        <li class="pms-field pms-user-login-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
            <label for="pms_user_login"><?php echo apply_filters( 'pms_register_form_label_user_login', __( 'Username *', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_user_login" name="user_login" type="text" value="<?php echo apply_filters( 'pms_' . $form_name . '_form_value_user_login', isset( $_POST['user_login'] ) ? esc_attr( $_POST['user_login'] ) : '' ); ?>" />

            <?php pms_display_field_errors( $field_errors ); ?>
        </li>

        <?php $field_errors = pms_errors()->get_error_messages('user_email'); ?>
        <li class="pms-field pms-user-email-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
            <label for="pms_user_email"><?php echo apply_filters( 'pms_register_form_label_user_email', __( 'E-mail *', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_user_email" name="user_email" type="text" value="<?php echo apply_filters( 'pms_' . $form_name . '_form_value_user_email', isset( $_POST['user_email'] ) ? esc_attr( $_POST['user_email'] ) : '' ); ?>" <?php echo apply_filters( 'pms_register_form_attributes_user_email', '' ); ?>/>

            <?php pms_display_field_errors( $field_errors ); ?>
        </li>

        <?php $field_errors = pms_errors()->get_error_messages('first_name'); ?>
        <li class="pms-field pms-first-name-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
            <label for="pms_first_name"><?php echo apply_filters( 'pms_register_form_label_first_name', __( 'First Name', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_first_name" name="first_name" type="text" value="<?php echo ( isset( $_POST['first_name'] ) ? esc_attr( $_POST['first_name'] ) : '' ); ?>" />

            <?php pms_display_field_errors( $field_errors ); ?>
        </li>

        <?php $field_errors = pms_errors()->get_error_messages('last_name'); ?>
        <li class="pms-field pms-last-name-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
            <label for="pms_last_name"><?php echo apply_filters( 'pms_register_form_label_last_name', __( 'Last Name', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_last_name" name="last_name" type="text" value="<?php echo ( isset( $_POST['last_name'] ) ? esc_attr( $_POST['last_name'] ) : '' ); ?>" />

            <?php pms_display_field_errors( $field_errors ); ?>
        </li>

        <?php $field_errors = pms_errors()->get_error_messages('pass1'); ?>
        <li class="pms-field pms-pass1-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
            <label for="pms_pass1"><?php echo apply_filters( 'pms_register_form_label_pass1', __( 'Password *', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_pass1" name="pass1" type="password" />

            <?php pms_display_field_errors( $field_errors ); ?>
        </li>

        <?php $field_errors = pms_errors()->get_error_messages('pass2'); ?>
        <li class="pms-field pms-pass2-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
            <label for="pms_pass2"><?php echo apply_filters( 'pms_register_form_label_pass2', __( 'Repeat Password *', 'paid-member-subscriptions' ) ); ?></label>
            <input id="pms_pass2" name="pass2" type="password" />

            <?php pms_display_field_errors( $field_errors ); ?>
        </li>

        <?php
        $gdpr_settings = pms_get_gdpr_settings();
        if( !empty( $gdpr_settings ) ){
            if( !empty( $gdpr_settings['gdpr_checkbox'] ) && $gdpr_settings['gdpr_checkbox'] === 'enabled' ){
                $field_errors = pms_errors()->get_error_messages('user_consent'); ?>
                <li class="pms-field pms-gdpr-field <?php echo ( !empty( $field_errors ) ? 'pms-field-error' : '' ); ?>">
                    <label for="pms_user_consent">
                        <input id="pms_user_consent" name="user_consent" type="checkbox" value="1">
                        <?php echo ( isset($gdpr_settings['gdpr_checkbox_text']) ? wp_kses_post( str_replace( '{{privacy_policy}}', get_the_privacy_policy_link(), $gdpr_settings['gdpr_checkbox_text'] ) ) : __( 'I allow the website to collect and store the data I submit through this form. *', 'paid-member-subscriptions' ) ); ?>
                    </label>

                    <?php pms_display_field_errors( $field_errors ); ?>
                </li>
                <?php
            }
        }
        ?>

        <?php do_action( 'pms_register_form_after_fields', $atts ); ?>

        <?php
            // Get form fields and clean the buffer
            $register_form_fields = ob_get_contents();
            ob_end_clean();

            if( $form_name == 'register' )
                echo $register_form_fields;
        ?>

        <?php
            // Display subscription plans at the bottom
            if( $atts['plans_position'] == 'bottom' )
                echo $subscription_plans_field;
        ?>

    </ul>

    <?php do_action( 'pms_' . $form_name . '_form_bottom', $atts ); ?>

    <input name="pms_<?php echo $form_name; ?>" type="submit" value="<?php echo apply_filters( 'pms_' . $form_name . '_form_submit_text', __( 'Register', 'paid-member-subscriptions' ) ); ?>" />

</form>
