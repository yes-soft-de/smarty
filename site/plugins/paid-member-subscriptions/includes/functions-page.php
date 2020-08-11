<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the ID or URL of a page associated with PMS actions.
 *
 * @param  string $page Can be: login, account, register and lost-password
 * @param  bool   $url  Return URL or ID
 * @return mixed        ID or URL of the page.
 */
function pms_get_page( $page, $url = false ) {
    $settings = get_option( 'pms_general_settings' );

    //replace "-" with "_" and append "_page" at the end to match the keys from our settings
    $page = str_replace( '-', '_', $page ) . '_page';

    if ( !isset( $settings[$page] ) || $settings[$page] == '-1' )
        return false;
    else {
        if ( $url )
            return get_permalink( $settings[$page] );
        else
            return $settings[$page];
    }
}

/**
 * Given a subscription plan ID, generate an URL to retry the payment
 *
 * @since  1.8.7
 * @param  int     $plan_id  Subscription Plan ID
 * @return string            Retry Payment URL
 */
function pms_get_retry_url( $plan_id = '' ) {

    // check if account page is set
    if( !( $account_page = pms_get_page( 'account', true ) ) )
        return false;

    // validate member
    $member = pms_get_member( get_current_user_id() );

    if( !$member->is_member() )
        return false;

    // if a plan id is not provided, we default to the first subscription of the user
    if( empty( $plan_id ) ) {
        $subscriptions = $member->get_subscriptions_ids();
        $plan_id       = $subscriptions[0];
    }

    // check if user is member of the supplied plan
    if( !in_array( $plan_id, $member->get_subscriptions_ids() ) )
        return false;

    $member_subscription = $member->get_subscription( (int)$plan_id );

    // only pending payments can be retried
    if( $member_subscription['status'] != 'pending' )
        return false;

    // don't show if subscription plan is free
    $subscription_plan = pms_get_subscription_plan( (int)$plan_id );

    if( !( $subscription_plan->price > 0 ) )
        return false;

    $url = wp_nonce_url( add_query_arg( array( 'pms-action' => 'retry_payment_subscription', 'subscription_plan' => $plan_id ), $account_page ), 'pms_member_nonce', 'pmstkn' );

    return apply_filters( 'pms_get_retry_url', $url, $plan_id );

}

/**
 * Given a subscription plan ID, generate an URL to abandon the subscription
 *
 * @since  1.8.7
 * @param  int     $plan_id  Subscription Plan ID
 * @return string            Abandon Subscription URL
 */
function pms_get_abandon_url( $plan_id = '' ) {

    if( !( $account_page = pms_get_page( 'account', true ) ) )
        return false;

    $member = pms_get_member( get_current_user_id() );

    if( !$member->is_member() )
        return false;

    if( empty( $plan_id ) ) {
        $subscriptions = $member->get_subscriptions_ids();
        $plan_id       = $subscriptions[0];
    }

    if( !in_array( $plan_id, $member->get_subscriptions_ids() ) )
        return false;

    $member_subscription = $member->get_subscription( (int)$plan_id );

    $url = wp_nonce_url( add_query_arg( array( 'pms-action' => 'abandon_subscription', 'subscription_id' => $member_subscription['id'] ), $account_page ), 'pms_member_nonce', 'pmstkn' );

    return apply_filters( 'pms_get_abandon_url', $url, $plan_id );

}

/**
 * Given a subscription plan ID, generate an URL to cancel the subscription
 *
 * @since  1.8.7
 * @param  int     $plan_id  Subscription Plan ID
 * @return string            Cancel Subscription URL
 */
function pms_get_cancel_url( $plan_id = '' ) {

    if( !( $account_page = pms_get_page( 'account', true ) ) )
        return false;

    $member = pms_get_member( get_current_user_id() );

    if( !$member->is_member() )
        return false;

    if( empty( $plan_id ) ) {
        $subscriptions = $member->get_subscriptions_ids();
        $plan_id       = $subscriptions[0];
    }

    if( !in_array( $plan_id, $member->get_subscriptions_ids() ) )
        return false;

    // get subscription array from the member object
    $member_subscription = $member->get_subscription( (int)$plan_id );

    // get Member Subscription object
    $subscription = pms_get_member_subscription( $member_subscription['id'] );

    // only active subscriptions can be canceled
    if( $subscription->status != 'active' )
        return false;

    // return if subscription is recurring but HTTPS is not detected
    if( $subscription->is_auto_renewing() && !pms_is_https() )
        return false;

    $url = wp_nonce_url( add_query_arg( array( 'pms-action' => 'cancel_subscription', 'subscription_id' => $subscription->id ), $account_page ), 'pms_member_nonce', 'pmstkn' );

    return apply_filters( 'pms_get_cancel_url', $url, $plan_id );

}

/**
 * Given a subscription plan ID, generate an URL to renew the subscription
 *
 * @since  1.8.7
 * @param  int     $plan_id  Subscription Plan ID
 * @return string            Renew Subscription URL
 */
function pms_get_renew_url( $plan_id = '' ) {

    if( !( $account_page = pms_get_page( 'account', true ) ) )
        return false;

    $member = pms_get_member( get_current_user_id() );

    if( !$member->is_member() )
        return false;

    if( empty( $plan_id ) ) {
        $subscriptions = $member->get_subscriptions_ids();
        $plan_id       = $subscriptions[0];
    }

    if( !in_array( $plan_id, $member->get_subscriptions_ids() ) )
        return false;

    $member_subscription  = $member->get_subscription( (int)$plan_id );
    $subscription         = pms_get_member_subscription( $member_subscription['id'] );

    // number of days before expiration to show the renewal action
    $renewal_display_time = apply_filters( 'pms_output_subscription_plan_action_renewal_time', 15 );

    // do not show if subscription is recurring
    if( $subscription->is_auto_renewing() )
        return false;

    if( !( strtotime( $subscription->expiration_date ) - time() < $renewal_display_time * DAY_IN_SECONDS ) )
        return false;

    $url = wp_nonce_url( add_query_arg( array( 'pms-action' => 'renew_subscription', 'subscription_id' => $subscription->id, 'subscription_plan' => $plan_id ), $account_page ), 'pms_member_nonce', 'pmstkn' );

    return apply_filters( 'pms_get_renew_url', $url, $plan_id );

}

/**
 * Given a subscription plan ID, generate an URL to upgrade the subscription
 *
 * @since  1.8.7
 * @param  int     $plan_id  Subscription Plan ID
 * @return string            Upgrade Subscription URL
 */
function pms_get_upgrade_url( $upgrade_plan_id = '' ) {

    if( !( $account_page = pms_get_page( 'account', true ) ) )
        return false;

    $user_id = get_current_user_id();

    if( empty( $user_id ) )
        return false;

    $member              = pms_get_member( $user_id );
    $member_subscription = '';

    if( !$member->is_member() )
        return false;

    // If no upgrade path given, grab the first subscription
    if( empty( $upgrade_plan_id ) ) {

        if( !empty( $member->subscriptions ) && isset( $member->subscriptions[0] ) )
            $member_subscription = pms_get_member_subscription( $member->subscriptions[0]['id'] );

    } else {

        // Determine current subscription based on the provided upgrade path
        $member_subscription = pms_get_current_subscription_from_tier( $user_id, (int)$upgrade_plan_id );

    }

    if( empty( $member_subscription ) )
        return false;

    $url = wp_nonce_url( add_query_arg( array( 'pms-action' => 'upgrade_subscription', 'subscription_id' => $member_subscription->id, 'subscription_plan' => $member_subscription->subscription_plan_id ), $account_page ), 'pms_member_nonce', 'pmstkn' );

    return apply_filters( 'pms_get_upgrade_url', $url, $upgrade_plan_id );

}


/**
 * Get the URL of an account page tab, processed based on current permalinks settings.
 *
 * @param  string   $tab       Tab slug.
 * @param  string   $permalink Page permalink.
 * @return string              Processed permalink with the correct tab added.
 */
function pms_account_get_tab_url( $tab, $permalink ) {
    $permalink = remove_query_arg( array( 'pms_gateway_payment_action', 'pms_gateway_payment_id', 'pmsscscd' ), $permalink );

    if ( get_option( 'permalink_structure' ) && pms_get_page( 'account' ) && !apply_filters( 'pms_account_rewrite_tab_urls', false ) ) {
        if ( strstr( $permalink, '?' ) ) {
            $query_string = '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
            $permalink    = current( explode( '?', $permalink ) );
        } else
            $query_string = '';

        $url = trailingslashit( $permalink ) . trailingslashit( $tab );

        $url .= $query_string;
    } else
        $url = add_query_arg( 'tab', $tab, $permalink );

    return apply_filters( 'pms_account_get_tab_url', $url, $tab );

}

/**
 * Add query vars.
 *
 * @param   array  $vars
 * @return  array
 */
function pms_query_vars( $vars ) {
    $vars[] = 'tab';

    return $vars;
}
add_filter( 'query_vars', 'pms_query_vars', 10 );

//Account page needs the rewrite so we have pretty permalinks for tabs
if ( pms_get_page( 'account' ) ) {
    add_action( 'init', 'pms_rewrite_rule', 10 );
    add_action( 'wp_loaded', 'pms_flush_rewrite_rules' );
}

/**
 * Add new rewrite rule.
 *
 * @return void
 */
function pms_rewrite_rule() {

    if ( apply_filters( 'pms_account_rewrite_tab_urls', false ) )
        return;

    $page      = pms_get_page( 'account' );
    $page_slug = get_post_field( 'post_name', $page );

    add_rewrite_rule( '^'.$page_slug.'/([^/]*)/page/?([0-9]{1,})/?$','index.php?page_id='.$page.'&tab=$matches[1]&paged=$matches[2]', 'top' );
    add_rewrite_rule( '(.?.+?)/'.$page_slug.'(/(.*))/page/?([0-9]{1,})/?$','index.php?page_id='.$page.'&tab=$matches[3]&paged=$matches[4]', 'top' );
    add_rewrite_rule( '^'.$page_slug.'/([^/]*)/?','index.php?page_id='.$page.'&tab=$matches[1]', 'top' );
    add_rewrite_rule( '(.?.+?)/'.$page_slug.'(/(.*))?/?$','index.php?page_id='.$page.'&tab=$matches[3]', 'top' );

}

/**
 * Flush rewrite rules if our rule is not set.
 *
 * @return void
 */
function pms_flush_rewrite_rules() {

    if ( apply_filters( 'pms_account_rewrite_tab_urls', false ) )
        return;

	$rules     = get_option( 'rewrite_rules' );
    $page_slug = get_post_field( 'post_name', pms_get_page( 'account' ) );

	if ( !isset( $rules['(.?.+?)/'.$page_slug.'(/(.*))?/?$'] ) || !isset( $rules['^'.$page_slug.'/([^/]*)/?'] ) ) {
		global $wp_rewrite;

		$wp_rewrite->flush_rules();
	}
}
