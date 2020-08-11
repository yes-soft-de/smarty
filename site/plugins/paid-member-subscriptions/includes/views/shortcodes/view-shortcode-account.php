<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HTML output for the member's subscriptions
 *
 */
?>

<div class="pms-account-subscriptions-header pms-subscription-plans-header">
	<span class="pms-subscription-plan-name"><?php echo apply_filters( 'pms_subscription_plans_header_plan_name', __( 'Subscription' , 'paid-member-subscriptions' ) ); ?></span>
	<span class="pms-subscription-status"><?php echo apply_filters( 'pms_subscriptions_header_status', __( 'Status', 'paid-member-subscriptions' ) ); ?></span>
	<span class="pms-subscription-plan-expiration"><?php echo apply_filters( 'pms_subscription_plans_header_plan_expiration', __( 'Expires', 'paid-member-subscriptions' ) ); ?></span>
</div>

<?php 
	$subscription_statuses = pms_get_member_subscription_statuses();
?>
<?php foreach( $member_subscriptions as $key => $member_subscription ): ?>

	<?php $subscription_plan = pms_get_subscription_plan( $member_subscription->subscription_plan_id ); ?>

	<div class="pms-account-subscription pms-subscription-plan pms-subscription-plan-has-actions <?php echo ( $key == ( count( $member_subscriptions ) - 1 ) ? ' pms-last' : ''); ?>">

		<!-- Subscription plan name -->
		<span class="pms-subscription-plan-name">
			<?php echo $subscription_plan->name; ?>
		</span>

		<!-- Subscription status -->
		<span class="pms-subscription-status">
			<?php echo ( ! empty( $subscription_statuses[$member_subscription->status] ) ? $subscription_statuses[$member_subscription->status] : '' ); ?>
            <?php echo ( $member_subscription->is_trial_period() ? ' (' . __( 'Trial', 'paid-member-subscriptions' ) . ')' : '' ); ?>
		</span>

		<!-- Subscription expiration date -->
		<?php

			// If subscription is recurring display just a simple message
			if( $member_subscription->is_auto_renewing() || empty( $member_subscription->expiration_date ) ) 
				$expiration_date_output = __( 'Unlimited', 'paid-member-subscriptions' );

			// If subscription is not recurring show the expiration date
			else {

				$date_format 		    = apply_filters( 'pms_output_member_subscription_date_format', get_option('date_format') );
                $expiration_timestamp   = strtotime( pms_sanitize_date( $member_subscription->expiration_date ) );

				$expiration_date_output = ucfirst( date_i18n( $date_format, $expiration_timestamp ) );

			}

			// If the member's subscription is expired, place and "expired on:" label before the expiration date
			if( $member_subscription->status == 'expired' )
				$expiration_date_output = __( 'Expired on: ', 'paid-member-subscriptions' ) . esc_html( $expiration_date_output );

		?>
		<span class="pms-subscription-plan-expiration">
			<?php echo apply_filters( 'pms_output_subscription_plan_expiration_date', $expiration_date_output, $subscription_plan, $member_subscription->to_array(), $member->user_id ); ?>
		</span>

		<!-- Subscription details action -->
		<span class="pms-subscription-details">
			<a href="<?php echo add_query_arg( array( 'subscription_id' => $member_subscription->id ), pms_get_current_page_url( true ) ); ?>"><?php echo __( 'View Details', 'paid-member-subscriptions' ); ?></a>

			<?php 
			
				/**
				 * Add other action for the current subscription
				 *
				 * @param PMS_Member_Subscription $member_subscription
				 *
				 */
				do_action( 'pms_account_member_subscription_details', $member_subscription );

			?>
		</span>

	</div>

<?php endforeach; ?>