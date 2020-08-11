<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Hijack the content when restrictions are set on a single post
 *
 */
function pms_filter_content( $content, $post = null ) {

    global $user_ID, $pms_show_content;

    if( is_null( $post ) )
        global $post;

    if( empty( $post ) )
        return $content;

    /*
     * Defining this variable:
     *
     * $pms_show_content can have 3 states: null, true and false
     *
     * - if the state is "null" the $content is showed, but it did not go through any actual filtering
     * - if the state is "true" the $content is showed, but it did go through filters that explicitly said the $content should be shown
     * - if the state is "false" the $content is not showed, it is replaced with a restriction message, thus it explicitly says that it was filtered and access is denied to it
     *
     */
    $pms_show_content = null;

    /**
     * Show content for the `manage_options` and `pms_bypass_content_restriction` capabilities
     */
    if( current_user_can( 'manage_options' ) || current_user_can( apply_filters( 'pms_content_restriction_capability', 'pms_bypass_content_restriction' ) ) )
        return $content;


    // Get subscription plans that have access to this post
    $user_status             = get_post_meta( $post->ID, 'pms-content-restrict-user-status', true );
    $post_subscription_plans = get_post_meta( $post->ID, 'pms-content-restrict-subscription-plan' );

    if ( !empty( $post_subscription_plans ) && is_user_logged_in() ) {

        if ( pms_is_member( $user_ID, $post_subscription_plans ) ) {
            $pms_show_content = true;

            return $content;
        } else {
            $pms_show_content = false;

            $message = pms_process_restriction_content_message( 'non_members', $user_ID, $post->ID );

            return do_shortcode( apply_filters( 'pms_restriction_message_non_members', $message, $content, $post, $user_ID ) );
        }

    } else if ( !is_user_logged_in() && (
                ( !empty( $user_status ) && $user_status == 'loggedin' ) || ( !empty( $post_subscription_plans ) )
            ) ) {

        // If user is not logged in and this is required prompt the correct message
        $pms_show_content = false;

        $message = pms_process_restriction_content_message( 'logged_out', $user_ID, $post->ID );

        return do_shortcode( apply_filters( 'pms_restriction_message_logged_out', $message, $content, $post, $user_ID ) );
    }

    return $content;

}
add_filter( 'the_content', 'pms_filter_content', 11 );
add_filter( 'pms_post_restricted_check', 'pms_filter_content', 10, 2 );


/**
 * Checks to see if the attachment image is restricted and returns
 * false instead of the image if it is restricted
 *
 * @param array|false $image
 * @param int         $attachment_id
 *
 * @return array|false
 *
 */
function pms_filter_attachment_image_src( $image, $attachment_id ) {

    if( is_admin() )
        return $image;

    if( pms_is_post_restricted( $attachment_id ) )
        return false;

    return $image;

}
add_filter( 'wp_get_attachment_image_src', 'pms_filter_attachment_image_src', 10, 2 );


/**
 * Checks to see if the attachment is restricted and returns
 * false instead of the metadata if it is restricted
 *
 * @param array|false $data
 * @param int         $attachment_id
 *
 * @return array|false
 *
 */
function pms_filter_attachment_metadata( $data, $attachment_id ) {

    if( is_admin() )
        return $data;

    if( pms_is_post_restricted( $attachment_id ) )
        return false;

    return $data;

}
add_filter( 'wp_get_attachment_metadata', 'pms_filter_attachment_metadata', 10, 2 );


/**
 * Checks to see if the attachment thumb is restricted and returns
 * false instead of the thumb url if it is restricted
 *
 * @param string $url
 * @param int    $attachment_id
 *
 * @return string|false
 *
 */
function pms_filter_attachment_thumb_url( $url, $attachment_id ) {

    if( is_admin() )
        return $url;

    if( pms_is_post_restricted( $attachment_id ) )
        return false;

    return $url;

}
add_filter( 'wp_get_attachment_thumb_url', 'pms_filter_attachment_thumb_url', 10, 2 );


/**
 * Checks to see if the attachment is restricted and returns
 * an empty string instead of the attachment url if it is restricted
 *
 * @param string $url
 * @param int    $attachment_id
 *
 * @return string
 *
 */
function pms_filter_attachment_url( $url, $attachment_id ) {

    if( is_admin() )
        return $url;

    if( pms_is_post_restricted( $attachment_id ) )
        return '';

    return $url;

}
add_filter( 'wp_get_attachment_url', 'pms_filter_attachment_url', 10, 2 );
add_filter( 'attachment_link', 'pms_filter_attachment_url', 10, 2 );


/**
 * Formats the error messages to display accordingly to the WYSIWYG editor
 *
 * @param string $message
 *
 * @return string
 *
 */
function pms_restriction_message_wpautop( $message = '' ) {

    if( !empty( $message ) )
        $message = wpautop( $message );

    return apply_filters('pms_restriction_message_wpautop' ,$message);

}
add_filter( 'pms_restriction_message_non_members', 'pms_restriction_message_wpautop', 30, 1 );
add_filter( 'pms_restriction_message_logged_out', 'pms_restriction_message_wpautop', 30, 1 );


/**
 * Adds a preview of the restricted post before the default restriction messages
 *
 * @param string $message
 * @param string $content
 * @param WP_Post $post
 * @param int $user_ID
 *
 * @return string
 *
 */
function pms_add_restricted_post_preview( $message, $content, $post, $user_ID ) {

    $preview        = '';
    $settings       = get_option( 'pms_content_restriction_settings' );
    $preview_option = ( !empty( $settings['restricted_post_preview']['option'] ) ? $settings['restricted_post_preview']['option'] : '' );

    if( empty( $preview_option ) || $preview_option == 'none' )
        return $message;

    $post_content = $content;

    /**
     * Trim the content
     *
     */
    if( $preview_option == 'trim-content' ) {

        $length = ( !empty( $settings['restricted_post_preview']['trim_content_length'] ) ? (int)$settings['restricted_post_preview']['trim_content_length'] : 0 );

        if( $length !== 0 ) {

            // Do shortcodes on the content
            $post_content = do_shortcode( $post_content );

            // Trim the preview
            $preview = wp_trim_words( $post_content, $length, apply_filters( 'pms_restricted_post_preview_more', __( '&hellip;' ) ) );

        }

    }

    /**
     * More tag
     *
     */
    if( $preview_option == 'more-tag' ) {
        /* if we don't have a more tag restrict the whole post */
        if ( preg_match('/<!--more(.*?)?-->/', $post->post_content) ) {
            $content_parts = get_extended($post->post_content);
            $preview = $content_parts['main'];
        }
    }

    // Return the preview
    return wpautop( $preview ) . $message;

}
add_filter( 'pms_restriction_message_non_members', 'pms_add_restricted_post_preview', 30, 4 );
add_filter( 'pms_restriction_message_logged_out', 'pms_add_restricted_post_preview', 30, 4 );


/**
 * Filters the content to display an error message if by some chance a payment error
 * has occured and the payment didn't go through
 *
 * @param string $content
 *
 * @return string
 *
 */
function pms_payment_error_message( $content = '' ) {

    if( empty( $_GET['pms_payment_error'] ) || $_GET['pms_payment_error'] != '1' )
        return $content;

    if( ! isset( $_GET['pms_is_register'] ) )
        return $content;

    $payment_id = ( !empty( $_GET['pms_payment_id'] ) ? $_GET['pms_payment_id'] : 0 );

    $error = __( 'Something went wrong while trying to process the payment.', 'paid-member-subscriptions' ) . ' ' . pms_payment_error_message_retry( $_GET['pms_is_register'], $payment_id );

    return apply_filters( 'pms_payment_error_message', '<div class="pms-payment-error"><p>' . $error . '</p></div>', $_GET['pms_is_register'], $payment_id );
}
add_filter( 'pms_account_shortcode_content', 'pms_payment_error_message', 999 );
add_filter( 'pms_member_account_not_logged_in', 'pms_payment_error_message', 999 );
add_filter( 'pms_register_shortcode_content', 'pms_payment_error_message', 999 );
add_filter( 'wppb_register_form_content', 'pms_payment_error_message', 999 );

/**
 * Generate the retry payment part of the payment error message
 *
 * @since 1.8.5
 * @param  int     $is_register  If we're on the register form or another one
 * @param  int     $payment_id   ID of the payment associated with the error
 * @return string                Formatted message according to location and settings
 */
function pms_payment_error_message_retry( $is_register, $payment_id = 0 ) {

    if ( $is_register == '1' ) {

        if ( !is_user_logged_in() ) {

            $message = __( 'Your account has been created, so please %slog in%s and retry the payment.', 'paid-member-subscriptions' );

            if ( $account_page = esc_url( pms_get_page( 'account', true ) ) )
                $message = sprintf( $message, '<a href="'. $account_page .'">', '</a>' );
            else
                $message = sprintf( $message, '', '' );
        //user was logged in automatically
        } else {
            if ( $payment_id != 0 && pms_get_page( 'account' ) != false ) {

                $payment = pms_get_payment( $payment_id );

                $message = sprintf( __( 'You were logged in, please %sclick here%s to try again.', 'paid-member-subscriptions' ), '<a href="'. pms_get_retry_url( $payment->subscription_id ) .'">', '</a>' );

            } else
                $message = __( 'You were logged in, please try again.', 'paid-member-subscriptions' );
        }
    //user is logged in
    } else {

        $payment = pms_get_payment( $payment_id );
        $retry_url = pms_get_retry_url( $payment->subscription_id );

        if ( $payment_id != 0 && pms_get_page( 'account' ) != false && !empty( $retry_url ) )
            $message = sprintf( __( 'Please %sclick here%s to try again.', 'paid-member-subscriptions' ), '<a href="'. $retry_url .'">', '</a>' );
        else
            $message = __( 'Please try again.', 'paid-member-subscriptions' );

    }

    return $message;
}


/*
 * Hijack the content when a member wants to upgrade to a higher subscription plan
 *
 */
function pms_member_upgrade_subscription( $content ) {

    // Do nothing if we cannot validate the nonce
    if( !isset( $_REQUEST['pmstkn'] ) || !( wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_member_nonce' ) || wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_upgrade_subscription' ) ) )
        return $content;

    $user_id = pms_get_current_user_id();
    $member  = pms_get_member( $user_id );

    // Do nothing if the user is not a member
    if( !$member->is_member() )
        return $content;

    if( !isset( $_REQUEST['pms-action'] ) || ($_REQUEST['pms-action'] != 'upgrade_subscription') || !isset( $_REQUEST['subscription_plan'] ) )
        return $content;

    if( !in_array( trim( $_REQUEST['subscription_plan'] ), $member->get_subscriptions_ids() ) )
        return $content;

    // If we don't have any upgrades available, return the content
    $subscription_plan_upgrades = pms_get_subscription_plan_upgrades( (int)trim( $_REQUEST['subscription_plan'] ) );

    if( empty( $subscription_plan_upgrades ) )
        return $content;

    // If URL parameter is set, only show that plan
    if( isset( $_GET['upgrade_subscription_plan'] ) ){
        $plan = pms_get_subscription_plan( (int)$_GET['upgrade_subscription_plan'] );

        if( !empty( $plan->id ) )
            $subscription_plan_upgrades = array( $plan );
    }

    $subscription_plan = pms_get_subscription_plan( (int)trim( $_REQUEST['subscription_plan'] ) );

    // Output form
    $output = '<form id="pms-upgrade-subscription-form" action="" method="POST" class="pms-form">';

        // Do actions at the top of the form
        ob_start();

        do_action('pms_upgrade_subscription_form_top');

        $output .= ob_get_contents();
        ob_end_clean();

        // Output tagline
        if( count( $subscription_plan_upgrades ) == 1 )
            $output .= apply_filters( 'pms_upgrade_subscription_before_form', '<p>' . sprintf( __( 'Upgrade %1$s to %2$s', 'paid-member-subscriptions' ), '<strong>' . $subscription_plan->name . '</strong>', '<strong>' . $subscription_plan_upgrades[0]->name . '</strong>' ) . '</p>', $subscription_plan, $member );
        else
            $output .= apply_filters( 'pms_upgrade_subscription_before_form', '<p>' . sprintf( __( 'Upgrade %s to:', 'paid-member-subscriptions' ), '<strong>' . $subscription_plan->name . '</strong>' ) . '</p>', $subscription_plan, $member );

        // Output subscription plans
        $output .= pms_output_subscription_plans( $subscription_plan_upgrades, array(), false, '', 'upgrade_subscription' );

        // Used to output the Billing Information and Credit Card form
        ob_start();

        do_action( 'pms_upgrade_subscription_form_bottom' );

        $output .= ob_get_contents();

        ob_end_clean();

        // Output nonce field
        $output .= wp_nonce_field( 'pms_upgrade_subscription', 'pmstkn' );

        // Output submit button
        $output .= '<input type="submit" name="pms_upgrade_subscription" value="' . apply_filters( 'pms_upgrade_subscription_button_value', __( 'Upgrade Subscription', 'paid-member-subscriptions' ) ) . '" />';
        $output .= '<input type="submit" name="pms_redirect_back" value="' . apply_filters( 'pms_upgrade_subscription_go_back_button_value', __( 'Go back', 'paid-member-subscriptions' ) ) . '" />';

    $output .= '</form>';

    return apply_filters( 'pms_the_content_member_upgrade_subscription', $output, $content, $user_id, $subscription_plan );

}
add_filter( 'pms_account_shortcode_content', 'pms_member_upgrade_subscription', 11 );


/*
 * Hijack the content when a member wants to renew a subscription plan
 *
 */
function pms_member_renew_subscription( $content ) {

    // Verify nonce
    if( !isset( $_REQUEST['pmstkn'] ) || !( wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_member_nonce' ) || wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_renew_subscription' ) ) )
        return $content;

    $user_id = pms_get_current_user_id();
    $member  = pms_get_member( $user_id );

    // Do nothing if the user is not a member
    if( !$member->is_member() )
        return $content;

    if( !isset( $_REQUEST['pms-action'] ) || ($_REQUEST['pms-action'] != 'renew_subscription') || !isset( $_REQUEST['subscription_plan'] ) )
        return $content;

    if( !in_array( trim( $_REQUEST['subscription_plan'] ), $member->get_subscriptions_ids() ) )
        return $content;

    // Get subscription plan and member subscription
    $subscription_plan       = pms_get_subscription_plan( (int)trim( $_REQUEST['subscription_plan'] ) );
    $member_subscription     = $member->get_subscription( $subscription_plan->id );

    // If member subscription is not in renewal period,
    // return the default content
    $renewal_display_time = apply_filters( 'pms_output_subscription_plan_action_renewal_time', 15 );
    if( $member_subscription['status'] != 'canceled' && strtotime( $member_subscription['expiration_date'] ) - time() > $renewal_display_time * 86400 )
        return $content;


    if( $subscription_plan->duration !== 0 ) {

        if( time() > strtotime( $member_subscription['expiration_date'] ) )
            $renew_expiration_date = date_i18n( get_option('date_format'), strtotime( '+' . $subscription_plan->duration . ' ' . $subscription_plan->duration_unit, time() ) );
        else
            $renew_expiration_date = date_i18n( get_option('date_format'), strtotime( pms_sanitize_date($member_subscription['expiration_date']) . '+' . $subscription_plan->duration . ' ' . $subscription_plan->duration_unit ) );

    } else {
        $renew_expiration_date = $subscription_plan->get_expiration_date(true);
        if( !empty( $renew_expiration_date ) )
            $renew_expiration_date = date_i18n( get_option('date_format'), $renew_expiration_date);
        else
            $renew_expiration_date = __( "Unlimited", 'paid-member-subscriptions' );
    }

    // Output form
    $output = '<form id="pms-renew-subscription-form" action="" method="POST" class="pms-form">';

        // Do Actions at the top of the form
        ob_start();

        do_action('pms_renew_subscription_form_top');

        $output .= ob_get_contents();
        ob_end_clean();


        // Output tagline
        $output .= apply_filters( 'pms_renew_subscription_before_form', '<p>' . sprintf( __( 'Renew %s subscription. The subscription will be active until %s', 'paid-member-subscriptions' ), '<strong>' . $subscription_plan->name . '</strong>', '<strong>' . $renew_expiration_date .'</strong>' ) . '</p>', $subscription_plan, $member );

        // Output subscription plans
        $output .= pms_output_subscription_plans( array($subscription_plan) );

        // Used to output the Billing Information and Credit Card form
        ob_start();

        do_action('pms_renew_subscription_form_bottom');

        $output .= ob_get_contents();
        ob_end_clean();

        // Output nonce field
        $output .= wp_nonce_field( 'pms_renew_subscription', 'pmstkn' );

        // Output submit button
        $output .= '<input type="submit" name="pms_renew_subscription" value="' . apply_filters( 'pms_renew_subscription_button_value', __( 'Renew Subscription', 'paid-member-subscriptions' ) ) . '" />';
        $output .= '<input type="submit" name="pms_redirect_back" value="' . apply_filters( 'pms_renew_subscription_go_back_button_value', __( 'Go back', 'paid-member-subscriptions' ) ) . '" />';

    $output .= '</form>';

    return apply_filters( 'pms_the_content_member_renew_subscription', $output, $content, $user_id, $subscription_plan );

}
add_filter( 'pms_account_shortcode_content', 'pms_member_renew_subscription', 11 );



/*
 * Hijack the content when a member wants to cancel a subscription
 *
 */
function pms_member_cancel_subscription( $content ) {

    // Verify nonce
    if( ! isset( $_REQUEST['pmstkn'] ) || ! wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_member_nonce' ) )
        return $content;

    if( ! isset( $_REQUEST['pms-action'] ) || ( $_REQUEST['pms-action'] != 'cancel_subscription' ) || ! isset( $_GET['subscription_id'] ) )
        return $content;

    // Get member and the member's subscription
    $member              = pms_get_member( get_current_user_id() );
    $member_subscription = pms_get_member_subscription( (int)$_GET['subscription_id'] );

    if( is_null( $member_subscription ) )
        return $content;

    if( $member_subscription->status != 'active' )
        return $content;

    if( ! in_array( $member_subscription->subscription_plan_id, $member->get_subscriptions_ids() ) )
        return $content;

    // Get subscription plan
    $subscription_plan = pms_get_subscription_plan( (int)$member_subscription->subscription_plan_id );

    // Output form
    $output = '<form id="pms-cancel-subscription-form" action="" method="POST" class="pms-form">';

        $output .= apply_filters( 'pms_cancel_subscription_confirmation_message', '<p>' . sprintf( __( 'Are you sure you want to cancel your %s subscription? No further payments will be made for this subscription and it will expire.', 'paid-member-subscriptions' ) . '</p>', '<strong>' . $subscription_plan->name . '</strong>' ), $subscription_plan );

        // Hidden subscription id field
        $output .= '<input type="hidden" name="subscription_id" value="' . esc_attr( $member_subscription->id ) . '" />';

        // Output nonce field
        $output .= wp_nonce_field( 'pms_cancel_subscription', 'pmstkn' );

        // Output submit button
        $output .= '<input type="submit" name="pms_confirm_cancel_subscription" value="' . apply_filters( 'pms_cancel_subscription_button_value', __( 'Confirm', 'paid-member-subscriptions' ) ) . '" />';
        $output .= '<input type="submit" name="pms_redirect_back" value="' . apply_filters( 'pms_cancel_subscription_go_back_button_value', __( 'Go back', 'paid-member-subscriptions' ) ) . '" />';


    $output .= '</form>';

    return $output;

}
add_filter( 'pms_account_shortcode_content', 'pms_member_cancel_subscription', 11 );


/*
 * Hijack the content when a member wants to abandon a subscription
 *
 */
function pms_member_abandon_subscription( $content ) {

    // Verify nonce
    if( ! isset( $_REQUEST['pmstkn'] ) || ! wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_member_nonce' ) )
        return $content;

    if( ! isset( $_GET['pms-action'] ) || ( $_GET['pms-action'] != 'abandon_subscription' ) || ! isset( $_GET['subscription_id'] ) )
        return $content;

    // Get member and the member's subscription
    $member              = pms_get_member( get_current_user_id() );
    $member_subscription = pms_get_member_subscription( (int)$_GET['subscription_id'] );

    if( is_null( $member_subscription ) )
        return $content;

    if( ! in_array( $member_subscription->subscription_plan_id, $member->get_subscriptions_ids() ) )
        return $content;

    // Get subscription plan
    $subscription_plan = pms_get_subscription_plan( (int)$member_subscription->subscription_plan_id );

    // Output form
    $output = '<form id="pms-abandon-subscription-form" action="" method="POST" class="pms-form">';

    $output .= apply_filters( 'pms_abandon_subscription_confirmation_message', '<p>' . sprintf( __( 'Are you sure you want to abandon your %s subscription? This subscription will be removed completely from your account.', 'paid-member-subscriptions' ) . '</p>', '<strong>' . $subscription_plan->name . '</strong>' ), $subscription_plan );

    // Hidden subscription id field
    $output .= '<input type="hidden" name="subscription_id" value="' . esc_attr( $member_subscription->id ) . '" />';

    // Output nonce field
    $output .= wp_nonce_field( 'pms_abandon_subscription', 'pmstkn' );

    // Output submit button
    $output .= '<input type="submit" name="pms_confirm_abandon_subscription" value="' . apply_filters( 'pms_abandon_subscription_button_value', __( 'Abandon Subscription', 'paid-member-subscriptions' ) ) . '" />';
    $output .= '<input type="submit" name="pms_redirect_back" value="' . apply_filters( 'pms_abandon_subscription_go_back_button_value', __( 'Go back', 'paid-member-subscriptions' ) ) . '" />';

    $output .= '</form>';

    return $output;

}
add_filter( 'pms_account_shortcode_content', 'pms_member_abandon_subscription', 11 );


/*
 * Hijack the content when a member wants to retry a payment for a pending subscription
 *
 */
function pms_member_retry_payment_subscription( $content ) {

    // Verify nonce
    if( !isset( $_REQUEST['pmstkn'] ) || !( wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_member_nonce' ) || wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_retry_payment_subscription' ) ) )
        return $content;

    $user_id = pms_get_current_user_id();
    $member  = pms_get_member( $user_id );

    // Do nothing if the user is not a member
    if( !$member->is_member() )
        return $content;

    if( !isset( $_REQUEST['pms-action'] ) || ($_REQUEST['pms-action'] != 'retry_payment_subscription') || !isset( $_REQUEST['subscription_plan'] ) )
        return $content;

    if( !in_array( trim( $_REQUEST['subscription_plan'] ), $member->get_subscriptions_ids() ) )
        return $content;

    // Return if the subscription is not pending
    $member_subscription = $member->get_subscription( (int)trim( $_REQUEST['subscription_plan'] ) );

    if( $member_subscription['status'] != 'pending' )
        return $content;


    // Output form
    $output = '<form id="pms-retry-payment-subscription-form" action="" method="POST" class="pms-form">';

    // Do Actions at the top of the form
    ob_start();

    do_action('pms_retry_payment_form_top');

    $output .= ob_get_contents();
    ob_end_clean();


    // Get subscription plan
    $subscription_plan = pms_get_subscription_plan( (int)trim( $_REQUEST['subscription_plan'] ) );

    $output .= apply_filters( 'pms_retry_payment_subscription_confirmation_message', '<p>' . sprintf( __( 'Your %s subscription is still pending. Do you wish to retry the payment?', 'paid-member-subscriptions' ) . '</p>', '<strong>' . $subscription_plan->name . '</strong>' ), $subscription_plan );

    // Output subscription plans
    $output .= pms_output_subscription_plans( array($subscription_plan), array(), false, '', 'retry_payment' );

    // Used to output the Billing Information and Credit Card form
    ob_start();

    do_action('pms_retry_payment_form_bottom');

    $output .= ob_get_contents();
    ob_end_clean();


    // Output nonce field
    $output .= wp_nonce_field( 'pms_retry_payment_subscription', 'pmstkn' );

    // Output submit button
    $output .= '<input type="submit" name="pms_confirm_retry_payment_subscription" value="' . apply_filters( 'pms_retry_payment_subscription_button_value', __( 'Retry payment', 'paid-member-subscriptions' ) ) . '" />';
    $output .= '<input type="submit" name="pms_redirect_back" value="' . apply_filters( 'pms_retry_payment_subscription_go_back_button_value', __( 'Go back', 'paid-member-subscriptions' ) ) . '" />';

    $output .= '</form>';

    return $output;

}
add_filter( 'pms_account_shortcode_content', 'pms_member_retry_payment_subscription', 11 );

/**
 * Make sure we do not execute shortcodes in our restrict messages if they are generated on the wp_head hook by SEO plugins like YOAST
 */
function pms_remove_shortcodes_from_messages_in_wp_head( $message ) {

    global $wp_current_filter;
    if( !empty( $wp_current_filter ) && is_array( $wp_current_filter ) ){
        foreach( $wp_current_filter as $filter ){
            if( $filter == 'wp_head' ) {
                $message = strip_shortcodes($message);
            }
        }
    }

    return $message;
}
add_filter( 'pms_restriction_message_logged_out', 'pms_remove_shortcodes_from_messages_in_wp_head', 12 );
add_filter( 'pms_restriction_message_non_members', 'pms_remove_shortcodes_from_messages_in_wp_head', 12 );

function pms_comments_restrict_replying( $open, $post_id ) {
    // Show for administrators
    if( current_user_can( 'manage_options' ) && is_admin() )
        return $open;

    if( pms_is_post_restricted( $post_id ) )
        return false;

    return $open;
}

function pms_comments_change_callback_function( $args ) {
    global $post;

    if ( empty( $post->ID ) ) return $args;

    if( pms_is_post_restricted( $post->ID ) )
        $args['callback'] = 'pms_comments_restrict_view';

    return $args;
}

function pms_comments_restrict_view( $comment, $args, $depth ) {
    static $message_shown = false;

    if ( !$message_shown ) {

        if ( is_user_logged_in() )
            printf( '<p>%s</p>', apply_filters( 'pms_comments_restriction_message_non_members', __( 'Comments are restricted for your membership level.', 'paid-member-subscriptions' ) ) );
        else
            printf( '<p>%s</p>', apply_filters( 'pms_comments_restriction_message_logged_out', __( 'You must be logged in to view the comments.', 'paid-member-subscriptions' ) ) );

        $message_shown = true;
    }
}

$pms_cr_settings = get_option( 'pms_content_restriction_settings' );

if ( isset( $pms_cr_settings['comments_restriction']['option'] ) && $pms_cr_settings['comments_restriction']['option'] != 'off' ) {
    add_filter( 'comments_open', 'pms_comments_restrict_replying', 20, 2 );

    if ( $pms_cr_settings['comments_restriction']['option'] == 'restrict-everything' )
        add_filter( 'wp_list_comments_args', 'pms_comments_change_callback_function', 999 );
}
