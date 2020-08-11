<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

    /*
     * HTML output for new subscription form
     *
     * @param $atts     - is available from parent file, in the register_form method of the PMS_Shortcodes class
     */
    $form_name = 'new_subscription';
?>

<form id="pms_<?php echo $form_name; ?>-form" class="pms-form" method="POST">

    <?php do_action( 'pms_' . $form_name . '_form_top', $atts ); ?>

    <?php

        wp_nonce_field( 'pms_' . $form_name . '_form_nonce', 'pmstkn' );
        pms_display_success_messages( pms_success()->get_messages('subscription_plans') );

    ?>

    <ul class="pms-form-fields-wrapper">

        <?php

            $field_errors = pms_errors()->get_error_messages( 'subscription_plans' );
            echo '<li class="pms-field pms-field-subscriptions ' . ( !empty( $field_errors ) ? 'pms-field-error' : '' ) . '">';
                echo pms_output_subscription_plans( $atts['subscription_plans'], $atts['exclude'], false, (isset($atts['selected']) ? trim($atts['selected']) : '' ), 'new_subscription' );
            echo '</li>';

        ?>

    </ul>

    <?php do_action( 'pms_' . $form_name . '_form_bottom', $atts ); ?>

    <input name="pms_<?php echo $form_name; ?>" type="submit" value="<?php echo apply_filters( 'pms_' . $form_name . '_form_submit_text', __( 'Subscribe', 'paid-member-subscriptions' ) ); ?>" />

</form>