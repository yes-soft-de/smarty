<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Emails Class contains the necessary functions for sending emails to users
 *
 */
Class PMS_Emails {

    /**
     * Initializer for the class where we hook in the actions to send emails
     *
     */
    static function init() {

        add_action( 'pms_register_form_after_create_user', array( 'PMS_Emails', 'send_registration_email' ) );

        add_action( 'pms_member_subscription_insert', array( 'PMS_Emails', 'send_emails' ), 10, 2 );
        add_action( 'pms_member_subscription_update', array( 'PMS_Emails', 'send_emails' ), 10, 3 );

        add_filter( 'pms_email_content_user',  array( 'PMS_Emails', 'maybe_add_html_tags' ), 20 );
        add_filter( 'pms_email_content_admin', array( 'PMS_Emails', 'maybe_add_html_tags' ), 20 );

    }


    /**
     * Sends emails to users / admins when a subscription is added or updated
     *
     * @param int   $subscription_id        - the ID of the subscription being added/updated
     * @param array $subscription_data      - the data array added to the subscription
     * @param array $old_subscription_data  - the array of values representing the subscription before the update
     *
     */
    static function send_emails( $subscription_id = 0, $subscription_data = array(), $old_subscription_data = array() ) {

        if( empty( $subscription_id ) )
            return;

        if( empty( $subscription_data['status'] ) )
            return;

        // Send emails only if the status or the subscription plan changes
        if( ! empty( $old_subscription_data['status'] ) && ( $old_subscription_data['status'] == $subscription_data['status'] ) ) {
            if ( empty($subscription_data['subscription_plan_id']) || ( !empty($subscription_data['subscription_plan_id']) && $old_subscription_data['subscription_plan_id'] == $subscription_data['subscription_plan_id']))
                return;
        }

        $subscription = pms_get_member_subscription( (int)$subscription_id );

        if( is_null( $subscription ) )
            return;

        // Set the current action
        switch ( $subscription->status ) {
            case 'active':
                $action = 'activate';
                break;

            case 'abandoned':
            case 'canceled':
                $action = 'cancel';
                break;

            case 'expired':
                $action = 'expired';
                break;

            default:
                $action = '';
                break;
        }

        // Action must be set
        if( empty( $action ) )
            return;

        $settings  = get_option( 'pms_emails_settings', array() );

        // Check that the status is a supported email action and that the email is active
        if( ! in_array( $action, PMS_Emails::get_email_actions() ) )
            return;

        /**
         * Send the email to the user
         *
         */
        if ( isset( $settings[ $action . '_is_enabled' ] ) )
            PMS_Emails::pms_mail( 'user', $action, $subscription->user_id, $subscription->subscription_plan_id, $subscription->start_date, $subscription->expiration_date );

        /**
         * Send the email to the admins
         *
         */

        if( empty( $settings['admin_emails_on'] ) || !isset( $settings[ $action . '_admin_is_enabled' ] ) )
            return;

        PMS_Emails::pms_mail( 'admin', $action, $subscription->user_id, $subscription->subscription_plan_id, $subscription->start_date, $subscription->expiration_date );

    }


    /**
     * Sends the user registration mail
     *
     * @param array $user_data
     *
     */
    static function send_registration_email( $user_data = array() ) {

        $settings        = get_option( 'pms_emails_settings', array() );
        $subscription_id = ( isset( $user_data['subscriptions'][0] ) ? $user_data['subscriptions'][0] : 0 );

        if ( isset( $settings[ 'register_is_enabled' ] ) )
            PMS_Emails::pms_mail( 'user', 'register', $user_data['user_id'], $subscription_id );

        if ( isset( $settings[ 'register_admin_is_enabled' ] ) )
            PMS_Emails::pms_mail( 'admin', 'register', $user_data['user_id'], $subscription_id );

    }


    /**
     * Function that calls wp_mail after we decide what to send
     *
     * @param string $send_to              - the recepient of the email, possible values: user, admin
     * @param string $action               - the action for which the email is sent
     * @param int    $user_id
     * @param int    $subscription_plan_id
     * @param string $start_date
     * @param string $expiration_date
     *
     */
    static function pms_mail( $send_to = '', $action = '', $user_id = 0, $subscription_plan_id = 0, $start_date = '', $expiration_date = '' ) {

        if( empty( $send_to ) )
            return false;

        if( empty( $action ) )
            return false;

        if( apply_filters( 'pms_mail_stop_emails', false ) )
            return false;

        $settings  = get_option( 'pms_emails_settings', array() );
        $user_info = get_userdata( $user_id );

        if( empty( $user_info ) )
            return false;

        /**
         * Set the email address which will receive the email
         *
         */
        if( $send_to == 'user' ) {

            $email_to = $user_info->user_email;

        }

        if( $send_to == 'admin' ) {

            $admin_emails = ( ! empty( $settings['admin_emails'] ) ? $settings['admin_emails'] : '' );
            $admin_emails = array_map( 'trim', explode( ',', $admin_emails ) );

            // Make sure emails are valid
            foreach( $admin_emails as $key => $email_address ) {

                if( ! is_email( $email_address ) )
                    unset( $admin_emails[$key] );

            }

            if ( !empty( $admin_emails ))
                $email_to = $admin_emails;
            else
                $email_to = get_option( 'admin_email' );

        }


        /**
         * Set the subject and message content of the email
         *
         */
        $email_default_subjects = PMS_Emails::get_default_email_subjects( $send_to );
        $email_default_content  = PMS_Emails::get_default_email_content( $send_to );

        // Email settings for the user are saved in the db without a sufix
        $settings_sufix = ( $send_to == 'admin' ? '_admin' : '' );

        // Set email subject
        if( ! empty( $settings[$action . '_sub_subject' . $settings_sufix] ) )
            $email_subject = $settings[$action . '_sub_subject' . $settings_sufix];
        else
            $email_subject = $email_default_subjects[$action];

        // Set email message
        if( ! empty( $settings[$action . '_sub' . $settings_sufix] ) )
            $email_content = $settings[$action . '_sub' . $settings_sufix];
        else
            $email_content = $email_default_content[$action];

        // Process the merge tags for both subject and message content
        $email_subject = PMS_Merge_Tags::pms_process_merge_tags( $email_subject, $user_info, $subscription_plan_id, $start_date, $expiration_date, $action );
        $email_content = PMS_Merge_Tags::pms_process_merge_tags( $email_content, $user_info, $subscription_plan_id, $start_date, $expiration_date, $action );

        $email_content = wpautop( $email_content );
        $email_content = do_shortcode( $email_content );

        /**
         * Filter the subject and the content before sending the mail
         *
         * Deprecated when the $send_to was added
         *
         * @deprecated 1.5.6
         *
         */
        $email_subject = apply_filters( 'pms_email_subject', $email_subject, $action, $user_info, $subscription_plan_id, $start_date, $expiration_date );
        $email_content = apply_filters( 'pms_email_content', $email_content, $action, $user_info, $subscription_plan_id, $start_date, $expiration_date );

        /**
         * Filter the subject and the content before sending the mail
         *
         */
        $email_subject = apply_filters( 'pms_email_subject_' . $send_to, $email_subject, $action, $user_info, $subscription_plan_id, $start_date, $expiration_date );
        $email_content = apply_filters( 'pms_email_content_' . $send_to, $email_content, $action, $user_info, $subscription_plan_id, $start_date, $expiration_date );

        // Add filter to enable html encoding
        add_filter( 'wp_mail_content_type', array( 'PMS_Emails', 'pms_email_content_type' ) );

        // Temporary change the from name and from email
        add_filter( 'wp_mail_from_name', array( 'PMS_Emails', 'pms_email_website_name' ), 20, 1 );
        add_filter( 'wp_mail_from', array( 'PMS_Emails', 'pms_email_website_email' ), 20, 1 );

        // Send email
        $mail_sent = wp_mail( $email_to, $email_subject, $email_content );

        // Reset html encoding
        remove_filter( 'wp_mail_content_type', array( 'PMS_Emails', 'pms_email_content_type' ) );

        // Reset the from name and email
        remove_filter( 'wp_mail_from_name', array( 'PMS_Emails', 'pms_email_website_name' ), 20 );
        remove_filter( 'wp_mail_from', array( 'PMS_Emails', 'pms_email_website_email' ), 20 );

        return $mail_sent;

    }


    /**
     * Function that returns the possible email actions
     *
     * @return array
     *
     */
    static function get_email_actions() {

        $email_actions = array( 'register', 'activate', 'cancel', 'expired' );

        return apply_filters( 'pms_email_actions', $email_actions );

    }


    /**
     * Function that returns the general email option defaults
     *
     * @return mixed
     *
     */
    static function get_email_general_options() {

        $email_options = array(
            'email-from-name'  => get_bloginfo('name'),
            'email-from-email' => get_bloginfo('admin_email'),
        );

        return apply_filters( 'pms_email_general_options_defaults', $email_options );
    }

    /**
     * The headers fot the emails in the settings page
     *
     * @return array
     *
     */
    static function get_email_headings() {

        $email_headings = array(
            'register' => __( 'Register Email', 'paid-member-subscriptions' ),
            'activate' => __( 'Activate Subscription Email', 'paid-member-subscriptions' ),
            'cancel'   => __( 'Cancel and Abandon Subscription Email', 'paid-member-subscriptions' ),
            'expired'  => __( 'Expired Subscription Email', 'paid-member-subscriptions' )
        );

        return apply_filters( 'pms_email_headings', $email_headings );

    }


    /**
     * The function that returns the default email subjects
     *
     * @param string $send_to
     *
     * @return array
     *
     */
    static function get_default_email_subjects( $send_to = '' ) {

        // Emails sent to the user
        if( empty( $send_to ) || $send_to == 'user' ) {

            $email_subjects = array(
                'register' => __( 'You have a new account', 'paid-member-subscriptions' ),
                'activate' => __( 'Your Subscription is now active', 'paid-member-subscriptions' ),
                'cancel'   => __( 'Your Subscription has been canceled', 'paid-member-subscriptions' ),
                'expired'  => __( 'Your Subscription has expired', 'paid-member-subscriptions' )
            );

        }

        // Emails sent to the admin
        if( $send_to == 'admin' ) {

            $email_subjects = array(
                'register' => __( 'A New User has registered to your website', 'paid-member-subscriptions' ),
                'activate' => __( 'A Member Subscription is now active', 'paid-member-subscriptions' ),
                'cancel'   => __( 'A Member Subscription has been canceled', 'paid-member-subscriptions' ),
                'expired'  => __( 'A Member Subscription has expired', 'paid-member-subscriptions' )
            );

        }

        return apply_filters( 'pms_default_email_subjects', $email_subjects, $send_to );

    }


    /**
     * The function that returns the default email contents
     *
     * @param string $send_to
     *
     * @return array
     *
     */
    static function get_default_email_content( $send_to = '' ) {

        // Emails sent to the user
        if( empty( $send_to ) || $send_to == 'user' ) {

            $email_content = array(
                'register' => __( 'Congratulations {{display_name}}! You have successfully created an account!', 'paid-member-subscriptions' ),
                'activate' => __( 'Congratulations {{display_name}}! The "{{subscription_name}}" plan has been successfully activated.', 'paid-member-subscriptions' ),
                'cancel'   => __( 'Hello {{display_name}}, The "{{subscription_name}}" plan has been canceled.', 'paid-member-subscriptions' ),
                'expired'  => __( 'Hello {{display_name}},The "{{subscription_name}}" plan has expired.', 'paid-member-subscriptions' )
            );

        }

        // Emails sent to the admin
        if( $send_to == 'admin' ) {

            $email_content = array(
                'register' => __( '{{display_name}} has just created an account!', 'paid-member-subscriptions' ),
                'activate' => __( 'The "{{subscription_name}}" plan has been successfully activated for user {{display_name}}.', 'paid-member-subscriptions' ),
                'cancel'   => __( 'The "{{subscription_name}}" plan has been canceled for user {{display_name}}.', 'paid-member-subscriptions' ),
                'expired'  => __( 'The "{{subscription_name}}" plan has expired for user {{display_name}}.', 'paid-member-subscriptions' )
            );

        }

        return apply_filters( 'pms_default_email_content', $email_content, $send_to );

    }

    /**
     * Filters the From name
     *
     * @param string $site_name
     *
     * @return string
     *
     */
    static function pms_email_website_name( $site_name = '' ) {

        $pms_settings = get_option( 'pms_emails_settings' );

        if ( !empty( $pms_settings['email-from-name'] ) ) {

            $site_name = $pms_settings['email-from-name'];

        } else {

            $site_name = get_bloginfo('name');

        }

        return $site_name;
    }


    /**
     * Filters the From email address
     *
     * @param string $site_name
     *
     * @return string
     *
     */
    static function pms_email_website_email( $sender_email = '' ) {

        $pms_settings = get_option( 'pms_emails_settings' );

        if ( ! empty( $pms_settings['email-from-email'] ) ) {

            if( is_email( $pms_settings['email-from-email'] ) )
                $sender_email = $pms_settings['email-from-email'];

        } else {

            $sender_email = get_bloginfo( 'admin_email' );

        }

        return $sender_email;
    }


    /**
     * Callback to be applied to change the content type of the sent emails
     *
     * @return string
     *
     */
    static function pms_email_content_type() {

        return 'text/html';

    }

    /**
     * Add HTML tags around message content
     */
    static function maybe_add_html_tags( $content ){

        if( $content !== wp_strip_all_tags( $content ) ){
            if( strpos( html_entity_decode( $content ), '<html' ) === false && strpos( html_entity_decode( $content ), '<body' ) === false )
                $content = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>'. $content . '</body></html>';
        }

        return $content;

    }

}

PMS_Emails::init();
