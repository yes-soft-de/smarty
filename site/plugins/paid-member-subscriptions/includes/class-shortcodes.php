<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcodes base class
 *
 */
Class PMS_Shortcodes {


    public static function init() {

        $shortcodes = array(
            'pms-register'         => __CLASS__ . '::register_form',
            'pms-subscriptions'    => __CLASS__ . '::subscriptions_form',
            'pms-account'          => __CLASS__ . '::member_account',
            'pms-edit-profile'     => __CLASS__ . '::edit_profile_form',
            'pms-login'            => __CLASS__ . '::login_form',
            'pms-logout'           => __CLASS__ . '::logout_form',
            'pms-recover-password' => __CLASS__ . '::recover_password_form',
            'pms-restrict'         => __CLASS__ . '::restrict_content',
            'pms-payment-history'  => __CLASS__ . '::payment_history',
            'pms-action'           => __CLASS__ . '::action_link',
        );

        foreach( $shortcodes as $shortcode_tag => $shortcode_func ) {
            add_shortcode( $shortcode_tag, $shortcode_func );
        }

        // Extra filters needed in the shortcodes
        add_filter( 'login_form_bottom', array( __CLASS__, 'login_form_bottom' ), 10, 2 );
    }


    /*
     * Register form shortcode
     *
     * @param array $attr       - there are the attributes the back-end user can pass to filter the subscription plans on the
     *                          register page. Usable attributes are as follow:
     *
     * - "subscription_plans"   - a list of subscription plan ids separated by comma that the back-end user wants to display in the front-end.
     *                          - "none" for allowing users to register without selecting any of the active subscription plans
     *                          - if this attribute is not set, all active subscription plans will be returned
     * - "plans_position"       - can have the values "bottom" or "top". Where to display the subscription plans in relation to the register
     *                          fields needed
     * - "selected"             - the id of the subscription plan that should be selected by default when rendering the form
     *
     */
    public static function register_form( $atts ) {

        $atts = shortcode_atts( array(
            'subscription_plans' => array(),
            'plans_position'     => 'bottom',
            'selected'           => ''
        ), $atts, 'pms-register' );

        /*
         * Sanitize attributes
         */
        if( ! empty( $atts['subscription_plans'] ) )
            $atts['subscription_plans'] = apply_filters( 'pms_register_form_subscription_plans', array_map( 'trim', explode(',', $atts['subscription_plans'] ) ) );

        if ( ( ! empty($atts['subscription_plans']) ) && ( strtolower( $atts['subscription_plans'][0] ) == 'none' ) )
            pms_errors()->remove('subscription_plans');

        /*
         * Detect if all went well on a registration and display a message to the user
         */
        if( isset( $_POST['pms_register'] ) ) {

            if( count( pms_errors()->get_error_codes() ) == 0 )
                return apply_filters( 'pms_register_success_message', '<div class="pms_success-messages-wrapper"><p>' . __( 'Congratulations, your account has been successfully created.', 'paid-member-subscriptions' ) . '</p></div>' );
            // If something went wrong while registering the user, show error
            else if( pms_errors()->get_error_code() == 'user_registration' )
                return apply_filters( 'pms_register_failed_message', '<div class="pms_field-errors-wrapper"><p>'. pms_errors()->get_error_message( 'user_registration' ). '</p></div>' );

        }


        /*
         * Show a single plan based on an URL parameters
         */
        if ( !empty( $_GET['subscription_plan'] ) && isset( $_GET['single_plan'] ) && $_GET['single_plan'] == 'yes' ) {

            $plan = pms_get_subscription_plan( (int)sanitize_text_field( $_GET['subscription_plan'] ) );

            if ( $plan->is_valid() && $plan->is_active() )
                $atts['subscription_plans'] = array( $plan->id );
        }

        /*
         * Display the register form
         */
        $users_can_register = apply_filters( 'pms_users_can_register', true );

        // Start catching the contents of the register form
        ob_start();

        // Display any success message that exists
        if( pms_success()->get_message( 'subscription_plans' ) ) {

            pms_display_success_messages(pms_success()->get_messages('subscription_plans'));

        }

        if( is_user_logged_in() ) {

            $plans = '';

            if ( !empty( $atts['subscription_plans'] ) && $atts['subscription_plans'][0] != 'none' )
                $plans = 'subscription_plans="'.implode( ',', $atts['subscription_plans'] ).'"';

            echo apply_filters( 'pms_register_form_already_a_user_message', do_shortcode( '[pms-subscriptions '. $plans .' selected="'.$atts['selected'].'"]' ), $atts );

        } else {

            if( !$users_can_register ) {
                echo '<p>' . __( 'Only an administrator can add new users.', 'paid-member-subscriptions' ) . '</p>';
            } else {

                if( !pms_success()->get_message( 'subscription_plans' ) )
                    include 'views/shortcodes/view-shortcode-register-form.php';

            }

        }

        // Get the contents and clean the buffer
        $output = ob_get_contents();
        ob_end_clean();

        return apply_filters( 'pms_register_shortcode_content', $output, $atts );

    }


    /*
     * Shortcode to output subscription plans form and allow users to subscribe to new subscriptions
     *
     */
    public static function subscriptions_form( $atts ) {

        $atts = shortcode_atts( array(
            'subscription_plans' => array(),
            'exclude'            => array(),
            'selected'           => ''
        ), $atts );

        /*
         * Sanitize attributes
         */
        if( ! empty( $atts['subscription_plans'] ) )
            $atts['subscription_plans'] = apply_filters( 'pms_subscription_form_subscription_plans', array_map( 'trim', explode(',', $atts['subscription_plans'] ) ) );

        // Start catching the contents of the subscriptions form
        ob_start();

        if( is_user_logged_in() ) {

            $member = pms_get_member( pms_get_current_user_id() );

            // Exclude subscription
            if( $member->get_subscriptions_count() > 0 ) {
                foreach( $member->subscriptions as $member_subscription )
                    array_push( $atts['exclude'], $member_subscription['subscription_plan_id'] );
            }

            if( $member->is_member() ) {

                echo apply_filters( 'pms_subscriptions_form_already_a_member', do_shortcode( '[pms-account show_tabs="no"]' ), $atts, $member );

            } else {

                include 'views/shortcodes/view-shortcode-new-subscription-form.php';

            }


        } else {

            echo apply_filters( 'pms_subscriptions_form_not_logged_in_message', __( 'Only registered users can see this information.', 'paid-member-subscriptions' ) );

        }

        // Get the contents and clean the buffer
        $output = ob_get_contents();
        ob_end_clean();

        return $output;

    }

    /*
     * Member account shortcode
     *
     * @param array $args      - there are the attributes the back-end user can pass to the account shortcode
     *                          Usable attributes are as follow:
     *
     */
    public static function member_account( $args ) {

        $args = shortcode_atts( array(
            'show_tabs'           => 'yes',
            'logout_redirect_url' => ''
        ), $args );

        // Get atts and set them
        if( !empty( $args['show_tabs'] ) && $args['show_tabs'] == 'no' )
            $args['show_tabs'] = false;
        else
            $args['show_tabs'] = true;

        // Get current user id
        $user_id = pms_get_current_user_id();

        // If no user is logged in display the login form
        if( $user_id === 0 )
            return apply_filters( 'pms_member_account_not_logged_in', do_shortcode( '[pms-login]' ), $args );

        ob_start();

        // Get member
        $member = pms_get_member( $user_id );
        $output = '';

        // Add subscription errors
        $output .= pms_display_success_messages( pms_success()->get_messages(), true );

        if ( $args['show_tabs'] === true ) {
            // Output tabs
            $tabs = apply_filters( 'pms_member_account_tabs', array(
              'subscriptions' => __( 'Subscriptions', 'paid-member-subscriptions' ),
              'profile'       => __( 'Edit Profile', 'paid-member-subscriptions' ),
              'payments'      => __( 'Payments', 'paid-member-subscriptions' ),
            ), $args );

            if ( apply_filters( 'pms_member_account_logout_tab', true ) )
                $tabs['logout'] = __( 'Logout', 'paid-member-subscriptions' );

            $active_tab = get_query_var( 'tab' );

            if ( empty( $active_tab ) || !isset( $tabs[$active_tab] ) )
                $active_tab = 'subscriptions';

            $account_page = pms_get_page( 'account', true );

            if( empty( $account_page ) )
                $account_page = pms_get_current_page_url();

            ?>
            <nav class="pms-account-navigation">
              <ul>
                  <?php foreach( $tabs as $slug => $name ) : ?>
                      <li class="pms-account-navigation-link pms-account-navigation-link--<?php echo $slug; ?>">
                          <a class="<?php echo ( $active_tab == $slug ? 'pms-account-navigation-link--active' : '' ); ?>" href="<?php echo esc_url( ( $slug != 'logout' ? pms_account_get_tab_url( $slug, $account_page ) : wp_logout_url( apply_filters( 'pms_member_account_logout_url', $args['logout_redirect_url'] ) ) ) ); ?>"><?php echo $name; ?></a>
                      </li>
                  <?php endforeach; ?>
              </ul>
            </nav>
        <?php
        } else {
            $active_tab = 'subscriptions';
        }

        /**
         * Action that fires just before the content of the tab
         *
         * @param string  $active_tab
         * @param array   $member
         */
        do_action( 'pms_member_account_before_' . $active_tab . '_tab', $active_tab, $member );

        if ( $active_tab == 'subscriptions' ) {

            if( !$member->is_member() ) {
                $message = '<p>' . esc_html__( 'You do not have any subscriptions attached to your account.', 'paid-member-subscriptions' ) . '</p>';

                $register_page = esc_url( pms_get_page( 'register', true ) );

                if ( !empty( $register_page ) )
                    $message .= sprintf( '<p>' . esc_html__( 'To purchase a subscription, you can %sclick here%s.', 'paid-member-subscriptions' ) . '</p>', '<a href="'.$register_page.'">', '</a>' );

                echo apply_filters( 'pms_member_account_not_member', $message, $member );
            } else {
                include 'views/shortcodes/view-shortcode-account-subscription-details.php';
            }

        } else if ( $active_tab == 'profile' ) {

            if( defined('PROFILE_BUILDER') ){
                $pms_general_settings = get_option( 'pms_general_settings', array() );

                if( isset( $pms_general_settings['edit_profile_shortcode'] ) && $pms_general_settings['edit_profile_shortcode'] != '-1' ){

                    if( $pms_general_settings['edit_profile_shortcode'] == 'wppb-default-edit-profile' )
                        echo do_shortcode( '[wppb-edit-profile]' );
                    else
                        echo do_shortcode( '[wppb-edit-profile form_name="' . Wordpress_Creation_Kit_PB::wck_generate_slug($pms_general_settings['edit_profile_shortcode']) . '"]');

                } else
                    echo do_shortcode( PMS_Shortcodes::edit_profile_form() );


            } else {
                echo do_shortcode( PMS_Shortcodes::edit_profile_form() );
            }

        } else if ( $active_tab == 'payments' ) {

            $args['number_per_page'] = apply_filters( 'pms_member_account_payments_per_page', 10 );

            include 'views/shortcodes/view-shortcode-payment-history.php';

        }

        /**
         * Action that fires after the content of the tab
         *
         * @param string  $active_tab
         * @param array   $member
         */
        do_action( 'pms_member_account_after_' . $active_tab . '_tab', $active_tab, $member );

        // Get the contents and clean the buffer
        $output .= ob_get_clean();

        return apply_filters( 'pms_account_shortcode_content', $output, $active_tab );

    }

    /*
     * Member edit profile form
     *
     */
    public static function edit_profile_form() {

        // Get current user id
        $user_id = pms_get_current_user_id();

        // If no user is found display a message and return
        if( $user_id === 0 ) {
            return apply_filters( 'pms_member_edit_profile_form_not_logged_in', '<p>' . __( 'You must be logged in to view this information.', 'paid-member-subscriptions' ) . '</p>' );
        }

        // Start catching the contents of the register form
        ob_start();

        include 'views/shortcodes/view-shortcode-edit-profile-form.php';

        // Get the contents and clean the buffer
        $output = ob_get_contents();
        ob_end_clean();

        return $output;

    }


    /**
     * Front-end login form
     *
     * @param array $atts       - these are the attributes the back-end user can set. Usable attributes are as follow:
     *
     *  - "redirect_url"         - a url where the logged in user should be redirected to. If this value is not set the user will be redirected to
     *                          the current page
     * - "lostpassword_url"     - if lostpassword_url argument is set, give the lost password error link that value and place a "Lost your password?" link below the login form
     *
     * - "register_url"         - place a "Register" link below the login form
     *
     * - "logout_redirect_url"  - a url where the user should be redirected to after logging out
     *
     */
    public static function login_form( $atts ) {

        $atts = shortcode_atts( array(
            'redirect_url'        => '',
            'register_url'        => '',
            'lostpassword_url'    => '',
            'logout_redirect_url' => pms_get_current_page_url()
        ), $atts );

        $output = '';

        if( !is_user_logged_in() ) {

            // Set up arguments for
            $args = array( 'echo' => false, 'form_id' => 'pms_login' );
            ( ! empty($atts['redirect_url']) ? $args['redirect'] = pms_add_missing_http( $atts['redirect_url'] ) : '' );

            $register_url = '';
            if( !empty( $atts['register_url'] ) )
                $register_url = pms_add_missing_http( $atts['register_url'] );
            else if( pms_get_page( 'register', true ) )
                $register_url = pms_get_page( 'register', true );

            $lostpassword_url = '';
            if( !empty( $atts['lostpassword_url'] ) )
                $lostpassword_url = pms_add_missing_http( $atts['lostpassword_url'] );
            else if( pms_get_page( 'lost-password', true ) )
                $lostpassword_url = pms_get_page( 'lost-password', true );


            $args['register']     = $register_url;
            $args['lostpassword'] = $lostpassword_url;

            // Get login error
            $login_error = ( isset( $_GET['login_error'] ) ? wp_kses_post( urldecode( base64_decode( $_GET['login_error'] ) ) ) : '' );

            if( !empty($login_error) ) {
                if ( !empty($args['lostpassword']) )  // replace the lost password error link with the "lostpassword_url" value from the shortcode
                    $login_error = str_replace( site_url('/wp-login.php?action=lostpassword'), esc_url( $lostpassword_url ), $login_error );

                $output .= '<p class="pms-login-error">' . $login_error . '</p>';
            }

            $output .= PMS_Shortcodes::pms_wp_login_form( apply_filters( 'pms_login_form_args', $args ) );

        } else {

            $user = get_userdata( get_current_user_id() );

            $redirect_url = apply_filters( 'pms_login_form_logout_redirect_url', $atts['logout_redirect_url'] );
            $logout_url   = '<a href="' . wp_logout_url( $redirect_url ) . '" title="' . __( 'Log out of this account', 'paid-member-subscriptions' ) . '">' . __( 'Log out', 'paid-member-subscriptions' ) . '</a>';

            $output .= apply_filters( 'pms_login_form_logged_in_message', '<p class="pms-alert">' . sprintf( __( 'You are currently logged in as %s.', 'paid-member-subscriptions' ), $user->display_name ) . ' ' . $logout_url . '</p>', $user->ID, $user->display_name );

        }

        return $output;

    }

    public static function pms_wp_login_form( $args = array() ) {
        $defaults = array(
            'echo'           => true,
            // Default 'redirect' value takes the user back to the request URI.
            'redirect'       => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            'form_id'        => 'pms-loginform',
            'label_username' => __( 'Username or Email Address', 'paid-member-subscriptions' ),
            'label_password' => __( 'Password', 'paid-member-subscriptions' ),
            'label_remember' => __( 'Remember Me', 'paid-member-subscriptions' ),
            'label_log_in'   => __( 'Log In', 'paid-member-subscriptions' ),
            'id_username'    => 'user_login',
            'id_password'    => 'user_pass',
            'id_remember'    => 'rememberme',
            'id_submit'      => 'wp-submit',
            'remember'       => true,
            'value_username' => '',
            // Set 'value_remember' to true to default the "Remember me" checkbox to checked.
            'value_remember' => false,
        );

        /**
         * Filters the default login form output arguments.
         */
        $args = wp_parse_args( $args, apply_filters( 'login_form_defaults', $defaults ) );

        /**
         * Filters content to display at the top of the login form.
         */
        $login_form_top = apply_filters( 'login_form_top', '', $args );

        /**
         * Filters content to display in the middle of the login form.
         */
        $login_form_middle = apply_filters( 'login_form_middle', '', $args );

        /**
         * Filters content to display at the bottom of the login form.
         */
        $login_form_bottom = apply_filters( 'login_form_bottom', '', $args );

        ob_start();
        ?>
            <form name="<?php echo $args['form_id']; ?>" id="<?php echo $args['form_id']; ?>" action="" method="post">
                <?php echo $login_form_top; ?>

                <p class="login-username">
                    <label for="<?php echo esc_attr( $args['id_username'] ); ?>"><?php echo esc_html( $args['label_username'] ) ?></label>
                    <input type="text" name="log" id="<?php echo esc_attr( $args['id_username'] ); ?>" class="input" value="<?php echo esc_attr( $args['value_username'] ); ?>" size="20" />
                </p>

                <p class="login-password">
                    <label for="<?php echo esc_attr( $args['id_password'] ); ?>"><?php echo esc_html( $args['label_password'] ); ?></label>
                    <input type="password" name="pwd" id="<?php echo esc_attr( $args['id_password'] ); ?>" class="input" value="" size="20" />
                </p>

                <?php echo $login_form_middle; ?>

                <?php if ( $args['remember'] ) : ?>
                    <p class="login-remember">
                        <label>
                            <input name="rememberme" type="checkbox" id="<?php echo esc_attr( $args['id_remember'] ); ?>" value="forever" <?php echo ( $args['value_remember'] ? ' checked="checked"' : '' ) ?> /> <?php echo esc_html( $args['label_remember'] ); ?>
                        </label>
                    </p>
                <?php endif; ?>

                <p class="login-submit">
                    <input type="submit" name="wp-submit" id="<?php echo esc_attr( $args['id_submit'] ); ?>" class="button button-primary" value="<?php echo esc_attr( $args['label_log_in'] ); ?>" />
                    <input type="hidden" name="redirect_to" value="<?php echo esc_url( $args['redirect'] ); ?>" />
                </p>

                <?php echo $login_form_bottom; ?>
            </form>
        <?php

        $form = ob_get_contents();
        ob_end_clean();

        if ( $args['echo'] )
            echo $form;
        else
            return $form;
    }

    /*
     * Add extra fields at the bottom of the login form
     *
     */
    public static function login_form_bottom( $string, $args ) {

        if( !isset( $args['form_id'] ) || $args['form_id'] != 'pms_login' )
            return $string;

        $string .= '<input type="hidden" name="pms_login" value="1" />';
        $string .= '<input type="hidden" name="pms_redirect" value="' . pms_get_current_page_url() . '" />';

        // Add "Register" and "Lost your password" links below the form is shortcode arguments exist
        $i = 0;
        if ( !empty($args['register']) ) {
            $string .= '<a href="' . esc_url($args['register']) . '">' . apply_filters('pms_login_register_text', __('Register', 'paid-member-subscriptions')) . '</a>';
            $i++;
        }
        if ( !empty($args['lostpassword']) ) {
            if ($i != 0) $string .= ' | ';
            $string .= '<a href="' . esc_url($args['lostpassword']) . '">' . apply_filters('pms_login_lostpass_text', __('Lost your password?', 'paid-member-subscriptions')) . '</a>';
        }

        return $string;
    }


    /**
     * Front-end logout link
     *
     * @param array $atts - these are the attributes the back-end user can set. Usable attributes are as follow:
     *
     *  - "text"                 - the text to be displayed to the loggedin user
     *  - "link_text"            - logout link custom text
     *  - "redirect_url"         - a url where the logged in user should be redirected to. If this value is not set the user will be redirected to
     *                          the current page
     */
    public static function logout_form( $atts ) {

        if( !is_user_logged_in() )
            return;

        $atts = shortcode_atts( array(
            'text'         => sprintf( __( 'You are currently logged in as %s.', 'paid-member-subscriptions' ), '{{meta_user_name}}' ),
            'link_text'    => __( 'Log out.', 'paid-member-subscriptions' ),
            'redirect_url' => pms_get_current_page_url()
        ), $atts );

        // Get current user data so that we can change the meta-tags
        $current_user     = get_userdata( get_current_user_id() );

        // Change the meta-tags into the correct user data
        $meta_tags        = apply_filters( 'pms_front_end_logout_meta_tags', array( '{{meta_user_name}}', '{{meta_first_name}}', '{{meta_last_name}}', '{{meta_display_name}}' ) );
        $meta_tags_values = apply_filters( 'pms_front_end_logout_meta_tags_values', array( $current_user->user_login, $current_user->first_name, $current_user->last_name, $current_user->display_name ) );

        $text             = esc_attr( apply_filters( 'pms_front_end_logout_text', str_replace( $meta_tags, $meta_tags_values, $atts['text'] ), $current_user ) );

        // Compose the logout link
        $redirect_url     = apply_filters( 'pms_logout_redirect_url', $atts['redirect_url'] );
        $logout_link      = '<a class="pms-logout-url" href="' . wp_logout_url( $redirect_url ) . '">' . esc_attr( $atts['link_text'] ) . '</a>';

        return '<p class="pms-front-end-logout">' . '<span>' . $text . '</span>' . ' ' . $logout_link . '</p>';

    }


    /**
     * Recover Password shortcode
     * @param $atts shortcode attributes
     *
     * - "redirect_url"         - a url where the user should be redirected to after successful password reset. If this value is not set the user will no redirect.
     *
     */
    public static function recover_password_form( $atts ){
        $atts = shortcode_atts( array(
            'redirect_url' => ''
        ), $atts );

        // If entered username or email is valid, display a message to the user and email confirmation link
        if( isset( $_POST['pms_username_email'] ) && count( pms_errors()->get_error_codes() ) == 0 ) {
            return apply_filters( 'pms_recover_password_confirmation_link_message', '<p>' . __( 'Please check your email for the confirmation link.', 'paid-member-subscriptions' ) . '</p>' );
        }

        ob_start();

        // Do not display the recover password form if user is logged in, display already logged in message
        if ( is_user_logged_in() ) {

            $member = pms_get_member( get_current_user_id() );
            echo( apply_filters ('pms_recover_password_form_logged_in_message', '<p>' .  __( 'You are already logged in.', 'paid-member-subscriptions' ) . '</p>', $atts, $member) );

        } else {

            if ( !empty($_GET['loginName']) && !empty($_GET['key']) ) {
                // The user clicked the email confirmation link
                if ( !empty($_POST['pms_new_password']) && !empty($_POST['pms_repeat_password']) && ( count( pms_errors()->get_error_codes() ) == 0 )) {

                    // The new password form was submitted with no errors
                    echo(apply_filters('pms_recover_password_form_password_changed_message', '<p>' . __('Your password was successfully changed!', 'paid-member-subscriptions') . '</p>'));

                    if ( ! empty($atts['redirect_url']) ) {// "redirect_url" shortcode parameter is set
                        $redirect_url = pms_add_missing_http( $atts['redirect_url'] );

                        $redirect_message = apply_filters( 'pms_recover_pass_redirect_message', __('You will soon be redirected automatically.', 'paid-member-subscriptions') );
                        echo '<p class="pms_redirect_message">'. $redirect_message . '</p>' . '<meta http-equiv="Refresh" content="3;url=' . $redirect_url . '" />';
                    }

                }

                else {
                    global $wpdb;

                    $key = sanitize_text_field( $_GET['key'] );
                    $username = sanitize_user( $_GET['loginName'] );

                    $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $username ) );

                    if ( !empty( $user ) && ( $user->user_activation_key == $_GET['key'] ) )
                        // Display the new password form
                        include 'views/shortcodes/view-shortcode-new-password-form.php';
                    else
                        // Confirmation link has expired or activation key invalid
                        echo( apply_filters ('pms_recover_password_form_invalid_key_message', '<p>' .  __( 'The confirmation link has expired. Invalid key.', 'paid-member-subscriptions' ) . '</p>') );
                }

            } else

                // display the standard recover password form
                include 'views/shortcodes/view-shortcode-recover-password-form.php';

        }

        $output = ob_get_contents();
        ob_end_clean();

        return $output;

    }


    /**
     * Restrict content shortcode
     * @param $atts shortcode attributes
     *   - subscription_plans: list of subscription plans separated by comma. if it is not defined then we only check if user is logged in
     *   - messsage: the message that will be displayed instead of the content
     */
    public static function restrict_content( $atts, $content = null ) {

         $args = shortcode_atts( array(
             'subscription_plans'  => array(),
             'display_to'          => '',
             'message'             => '',
             'group_owner_user_id' => '',
         ), $atts );

        // Message to replace the content of checks do not match
        if( ! empty( $args['message'] ) )
            $message = '<p>' . $args['message'] . '</p>';

        else {
            $type    = ( is_user_logged_in() ? 'non_members' : 'logged_out' );
            $message = pms_get_restricted_post_message();
        }

        /**
         * Filter the message
         *
         * @param string $message   - the current message, whether it is the default one from the settings or
         *                            the one set in the shortcode attributes
         * @param array  $args      - the shortcode attributes
         *
         */
        $message = apply_filters( 'pms_restrict_content_message', $message, $args );

        /**
         * Filter the content
         *
         * @param string $content   - content that is being filtered
         * @param array  $args      - the shortcode attributes
         *
         */
        $content = apply_filters( 'pms_restrict_content_output', $content, $args );


        if( is_user_logged_in() ) {

            // Show for administrators
            if( current_user_can( 'manage_options' ) )
                return do_shortcode( $content );

            if( $args['display_to'] == 'not_logged_in' )
                return $message;

            if( ! empty( $args['subscription_plans'] ) ) {

                $subscription_plans = array_map( 'trim', explode( ',', $args['subscription_plans'] ) );

                if( $args['display_to'] == 'not_subscribed' ) {

                    // when negating we need to take into consideration the MSPU add-on
                    if( defined( 'PMS_MSU_VERSION' ) ){

                        $user_id      = pms_get_current_user_id();
                        $member       = pms_get_member( $user_id );
                        $show_content = false;

                        foreach( $subscription_plans as $subscription_plan ){

                            // If user is not a member of the target plan and doesn't have another subscription from the same tier
                            if( !pms_is_member_of_plan( $subscription_plans ) && !pms_get_current_subscription_from_tier( $user_id, $subscription_plan ) ) {

                                if( $member->get_subscriptions_count() >= 0 && $member->get_subscriptions_count() < pms_get_subscription_plan_groups_count() )
                                    $show_content = true;
                                else
                                    $show_contnet = false;

                            } else
                                $show_content = false;

                        }

                        if( $show_content === true )
                            return do_shortcode( $content );
                        else
                            return $message;

                    // else make sure the user is not subscribed to any other plan before showing the content
                    } else {

                        if( !pms_is_member() && !pms_is_member_of_plan( $subscription_plans ) )
                            return do_shortcode( $content );
                        else
                            return $message;

                    }

                } else {

                    if( pms_is_member_of_plan( $subscription_plans ) )
                        return do_shortcode( $content );
                    else
                        return $message;

                }


            } else if( $args['display_to'] == 'not_subscribed' ){

                if( !pms_is_member() )
                    return do_shortcode( $content );
                else
                    return $message;

            } else if( $args['display_to'] == 'group_owner' && function_exists( 'pms_gm_is_group_owner' ) ){

                $user_id = !empty( $args['group_owner_user_id'] ) ? $args['group_owner_user_id'] : get_current_user_id();

                $member = pms_get_member( $user_id );

                $group_owner = false;

                if( !empty( $member->subscriptions ) ){
                    foreach( $member->subscriptions as $subscription ){
                        if( pms_gm_is_group_owner( $subscription['id'] ) ){
                            $group_owner = true;
                            break;
                        }
                    }
                }

                if( $group_owner === true )
                    return do_shortcode( $content );
                else
                    return $message;

            } else
                return do_shortcode( $content );

        } else {

            if( $args['display_to'] == 'not_logged_in' )
                return do_shortcode( $content );

            else
                return $message;

        }

    }


    /*
     * Displays the user's payments in a table
     *
     */
    public static function payment_history( $atts, $content = null ) {

        $args = shortcode_atts( array(
            'number_per_page' => 10
        ), $atts );

        $user_id = pms_get_current_user_id();

        if( $user_id == 0 )
            return '';

        // Start output buffering
        ob_start();

        include 'views/shortcodes/view-shortcode-payment-history.php';

        $output = ob_get_contents();
        ob_end_clean();

        return $output;

    }

    public static function action_link( $atts, $content = null ) {

        if( empty( $content ) )
            return;

        if( !pms_get_page( 'account' ) || !is_user_logged_in() )
            return;

        if( strpos( $content, '{{' ) === false || strpos( $content, '{{/' ) === false )
            return;

        $atts = shortcode_atts( array(
            'plan_id' => '',
        ), $atts );

        $actions = array( 'retry', 'renew', 'upgrade', 'cancel', 'abandon' );
        $current_action = '';

        foreach( $actions as $action ) {
            if( strpos( $content, '{{' . $action . '}}' ) !== false && strpos( $content, '{{/' . $action . '}}' ) !== false ) {
                $current_action = $action;
                break;
            }
        }

        if( empty( $current_action ) )
            return;

        $url_function = 'pms_get_' . $current_action . '_url';

        if( !function_exists( $url_function ) )
            return;

        $url = $url_function( $atts['plan_id'] );

        if( !$url )
            return;

        //if there's no link title, aka the shortcode is written like this: [pms-action]{{upgrade}}{{/upgrade}}[/pms-action]
        //return the unformatted url
        $link_title = str_replace( array( '{{'.$current_action.'}}', '{{/'.$current_action.'}}' ), '', $content );

        if( $link_title == '' ){

            if( !empty( $atts['plan_id'] ) )
                return add_query_arg( 'upgrade_subscription_plan', $atts['plan_id'], $url );
            else
                return $url;

        }

        $content = str_replace( '{{'.$current_action.'}}', '<a href="'. $url .'">', $content );
        $content = str_replace( '{{/'.$current_action.'}}', '</a>', $content );

        return apply_filters( 'pms_action_link', $content, $url, $current_action );

    }

}
