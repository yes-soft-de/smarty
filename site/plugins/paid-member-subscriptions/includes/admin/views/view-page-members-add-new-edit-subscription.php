<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * View for adding / editting member subscriptions
 *
 */

$subpage   = sanitize_text_field( $_GET['subpage'] );
$member_id = ( ! empty( $_GET['member_id'] ) ? (int)$_GET['member_id'] : 0 );

$first_subscription = false;

if( empty( $member_id ) && $subpage == 'add_subscription' )
	$first_subscription = true;

if( ! empty( $_POST ) ) {

	$form_data = pms_array_sanitize_text_field( $_POST );

	// Set the subscription id if it exists
	$form_data['id'] = ( ! empty( $_GET['subscription_id'] ) ? (int)$_GET['subscription_id'] : 0 );

	if( isset( $_GET['subscription_id'] ) )
		$member_subscription = pms_get_member_subscription( (int)$_GET['subscription_id'] );

} else {

	if( $subpage == 'edit_subscription' ) {

		$member_subscription = pms_get_member_subscription( (int)$_GET['subscription_id'] );

		if( is_null( $member_subscription ) )
			return;

		$form_data = $member_subscription->to_array();

	} else

		// Set some defaults for add_new subscription
		$form_data = array(
			'start_date' => date( 'Y-m-d' ),
			'status'	 => 'active'
		);

}
?>

<div class="wrap">

    <h1>
        <?php
        	if( $subpage == 'edit_subscription' )
        		_e( 'Edit Member Subscription', 'paid-member-subscriptions' );
        	else
        		_e( 'Add Member Subscription', 'paid-member-subscriptions' );
        ?>
    </h1>

    <form id="pms-form-<?php echo ( $subpage == 'add_subscription' ? 'add' : 'edit' ); ?>-member-subscription" class="pms-form" method="POST">

		<div id="poststuff">
			<div id="pms-dashboard-widgets-wrap">
				<div id="post-body" class="metabox-holder columns-2">

					<!-- Right Sidebar -->
			    	<div id="postbox-container-1" class="postbox-container">

			    		<!-- Update Subscription Meta-box -->
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<div id="pms-subscription-update" class="postbox">

								<!-- Meta-box Title -->
								<h3 class="hndle">
									<span>
										<?php
								        	if( $subpage == 'edit_subscription' )
								        		_e( 'Update Subscription', 'paid-member-subscriptions' );
								        	else
								        		_e( 'Add Subscription', 'paid-member-subscriptions' );
								        ?>
									</span>
								</h3>

								<!-- Actions -->
								<div class="submitbox">

									<div id="major-publishing-actions">

										<!-- Delete subscription -->
										<?php if( $subpage == 'edit_subscription' ): ?>
											<div id="delete-action">
												<a href="<?php echo wp_nonce_url( add_query_arg( array( 'subscription_id' => $form_data['id'] ), admin_url( 'admin.php?page=pms-members-page' ) ), 'pms_delete_subscription_nonce' ) ?>" class="submitdelete deletion"><?php _e( 'Delete Subscription', 'paid-member-subscriptions' ); ?></a>
											</div>
										<?php endif; ?>

										<!-- Update Subscription -->
										<input type="submit" class="button button-primary right" value="<?php ( $subpage == 'edit_subscription' ? esc_attr_e( 'Save Subscription', 'paid-member-subscriptions' ) : esc_attr_e( 'Add Subscription', 'paid-member-subscriptions' ) ); ?>"/>

										<div class="clear"></div>
									</div>

								</div><!-- End of Actions -->

							</div>
						</div>

					</div><!-- End of Right Sidebar -->

					<!-- Main Content -->
					<div id="postbox-container-2" class="postbox-container">

						<!-- Member/User Data -->
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<div id="pms-member-details" class="postbox">

								<h3 class="hndle">
									<span><?php _e( 'Member', 'paid-member-subscriptions' ); ?></span>
								</h3>

								<div class="inside">

									<?php if( $subpage == 'add_subscription' && $first_subscription ): ?>

										<?php
										$users = count_users();

										if( $users['total_users'] < apply_filters( 'pms_add_new_member_select_user_limit', '8000' ) ) : ?>
								            <select id="pms-member-username" name="pms-member-username" class="widefat pms-chosen">
								                <option value=""><?php _e( 'Select...', 'paid-member-subscriptions' ); ?></option>
								                <?php
								                    $users = pms_get_users_non_members();

								                    foreach( $users as $user ) {
								                        echo '<option ' . ( ! empty( $form_data['user_id'] ) ? selected( $form_data['user_id'], $user['id'], false ) : '' ) . ' value="' . esc_attr( $user['id'] ) . '">' . esc_html( $user['username'] ) . '</option>';
								                    }
								                ?>
								            </select>

											<p class="description"><?php printf( __( 'Select the username you wish to associate a subscription plan with. You can create a new user <a href="%s">here</a>.', 'paid-member-subscriptions' ), admin_url('user-new.php') ); ?></p>
										<?php else : ?>
											<label for="pms-member-username-input"><?php _e( 'Username', 'paid-member-subscriptions' ) ?></label>
											<input type="text" id="pms-member-username-input" name="pms-member-username" value="<?php echo !empty( $form_data['pms-member-username'] ) ? $form_data['pms-member-username'] : ''; ?>" />

											<p class="description"><?php printf( __( 'Enter the username you wish to associate a subscription plan with. You can create a new user <a href="%s">here</a>.', 'paid-member-subscriptions' ), admin_url('user-new.php') ); ?></p>
										<?php endif; ?>

							            <input type="hidden" id="pms-member-user-id" name="user_id" class="widefat" value="<?php echo ( ! empty( $form_data['user_id'] ) ? esc_attr( $form_data['user_id'] ) : 0 ); ?>" />

									<?php elseif( ( $subpage == 'add_subscription' && ! $first_subscription ) || $subpage == 'edit_subscription' ): ?>

										<?php
											if( empty( $member_id ) )
												$user_id = (int)$form_data['user_id'];
											else
												$user_id = $member_id;

											$user = get_user_by( 'id', $user_id );
										?>

										<input type="hidden" name="user_id" value="<?php echo esc_attr( $user_id ); ?>" />
										<a href="<?php echo add_query_arg( array( 'page' => 'pms-members-page', 'subpage' => 'edit_member', 'member_id' => (int)$user_id ), admin_url( 'admin.php' ) ); ?>" style="float: right; margin-top: 9px;"><?php echo __( 'View all subscriptions', 'paid-member-subscriptions' ); ?></a>
										<strong><?php echo $user->display_name; ?></strong><br />
										<?php echo $user->user_email; ?>

									<?php endif; ?>

								</div>
							</div>
						</div>


						<!-- Member Subscription Details Meta-box -->
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<div id="pms-member-subscription-details" class="postbox">

								<h3 class="hndle">
									<span><?php echo __( 'Subscription Details', 'paid-member-subscriptions' ); ?></span>
								</h3>
								<div class="inside">

									<!-- Subscription Plan -->
									<div class="pms-meta-box-field-wrapper">

									    <label for="pms-subscription-plan-id" class="pms-meta-box-field-label"><?php echo __( 'Subscription Plan', 'paid-member-subscriptions' ); ?> <span>*</span></label>

									    <select id="pms-subscription-plan-id" name="subscription_plan_id" class="pms-subscription-field" required>

									    <?php
									    	if( $subpage == 'add_subscription' )
									    		echo '<option value="0">' . __( 'Choose...', 'paid-member-subscriptions' ) . '</option>';
									    ?>

								        <?php

								        	/**
								        	 * Grab all subscription plans if it's the member's first subscription
								        	 *
								        	 */
								        	if( $subpage == 'add_subscription' && $first_subscription )
								        		$subscription_plans = pms_get_subscription_plans();

								        	/**
								        	 * If the member already has subscriptions, grab all subscription plans, but exclude the ones
								        	 * from the member's existing subscription plan groups
								        	 *
								        	 */
								        	elseif( $subpage == 'add_subscription' && ! $first_subscription ) {

								        		$existing_member_subscriptions = pms_get_member_subscriptions( array( 'user_id' => $member_id ) );
									    		$subscription_plans 		   = pms_get_subscription_plans();

									    		foreach( $existing_member_subscriptions as $existing_member_subscription ) {

									    			$subscription_plans_group = pms_get_subscription_plans_group( $existing_member_subscription->subscription_plan_id );
									    			$subscription_plans 	  = array_udiff( $subscription_plans, $subscription_plans_group, '_pms_compare_subscription_plans' );

									    		}

									    	/**
									    	 * If we edit a subcription grab only the subscriptions plans group of the current subscription plan
									    	 *
									    	 */
								        	} else
								        		$subscription_plans = pms_get_subscription_plans_group( $form_data['subscription_plan_id'], false );

								        	foreach( $subscription_plans as $subscription_plan ) {
									            echo '<option value="' . esc_attr( $subscription_plan->id ) . '"' . selected( $subscription_plan->id, ( ! empty( $form_data['subscription_plan_id'] ) ? (int)$form_data['subscription_plan_id'] : 0 ), false ) . '>' . $subscription_plan->name . '</option>';
									        }
								        ?>

								        </select>

								        <div class="spinner" style="float: none; margin-top: 0;"><!-- --></div>

									</div>

									<!-- Start Date -->
									<div class="pms-meta-box-field-wrapper">

									    <label for="pms-subscription-start-date" class="pms-meta-box-field-label"><?php echo __( 'Start Date', 'paid-member-subscriptions' ); ?> <span>*</span></label>

									    <input id="pms-subscription-start-date" type="text" name="start_date" class="datepicker pms-subscription-field" value="<?php echo ( ! empty( $form_data['start_date'] ) ? esc_attr( pms_sanitize_date( $form_data['start_date'] ) ) : '' ); ?>" required />

									</div>

									<!-- Expiration Date -->
									<div class="pms-meta-box-field-wrapper">

									    <label for="pms-subscription-expiration-date" class="pms-meta-box-field-label"><?php echo __( 'Expiration Date', 'paid-member-subscriptions' ); ?></label>

									    <input id="pms-subscription-expiration-date" type="text" name="expiration_date" class="datepicker pms-subscription-field" value="<?php echo ( ! empty( $form_data['expiration_date'] ) ? esc_attr( pms_sanitize_date( $form_data['expiration_date'] ) ) : '' ); ?>" />

									</div>

									<!-- Status -->
									<div class="pms-meta-box-field-wrapper">

									    <label for="pms-subscription-status" class="pms-meta-box-field-label"><?php echo __( 'Status', 'paid-member-subscriptions' ); ?> <span>*</span></label>

								        <select id="pms-subscription-status" name="status" class="pms-subscription-field" required>

								        <?php
								        	foreach( pms_get_member_subscription_statuses() as $member_status_slug => $member_status_name ) {
									            echo '<option value="' . esc_attr( $member_status_slug ) . '"' . selected( $member_status_slug, $form_data['status'], false ) . '>' . $member_status_name . '</option>';
									        }
								        ?>

								        </select>

									</div>

									<!-- Trial End -->
									<?php if( pms_payment_gateways_support( pms_get_payment_gateways( true ), 'subscription_free_trial' ) ): ?>
									<div class="pms-meta-box-field-wrapper">

									    <label for="pms-subscription-trial-end" class="pms-meta-box-field-label"><?php echo __( 'Trial End', 'paid-member-subscriptions' ); ?></label>

									    <input id="pms-subscription-trial-end" type="text" name="trial_end" class="datepicker pms-subscription-field" value="<?php echo ( ! empty( $form_data['trial_end'] ) ? esc_attr( pms_sanitize_date( $form_data['trial_end'] ) ) : '' ); ?>" />

									</div>
									<?php endif; ?>

								</div>

							</div>
						</div>

						<!-- Logs -->
						<?php if( $subpage == 'edit_subscription' && isset( $member_subscription ) ) :

							$subscription_logs = pms_get_member_subscription_meta( $member_subscription->id, 'logs', true );

							if( !empty( $subscription_logs ) ) :
							?>
								<div id="normal-sortables" class="meta-box-sortables ui-sortable">
									<div id="pms-member-subscription-logs" class="postbox">
										<h3 class="hndle">
											<span><?php echo __( 'Subscription Logs', 'paid-member-subscriptions' ); ?></span>
										</h3>

										<div class="inside pms-logs-holder">
											<?php foreach( array_reverse( $subscription_logs ) as $log ) echo $this->get_logs_row( $log ); ?>
										</div>
										<div class="inside">
											<input type="text" name="pms_admin_log" value="" placeholder="Add entry manually..." />
											<input type="hidden" name="pms_subscription_id" value="<?php echo $member_subscription->id ?>" />
											<input type="submit" value="Add Log" class="button" id="pms_add_log_entry" />
										</div>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>

						<!-- Member Subscription Billing Schedule Meta-box -->
						<?php if( isset( $member_subscription ) && pms_payment_gateways_support( array( $member_subscription->payment_gateway ), 'recurring_payments' ) && pms_payment_gateways_support( array( $member_subscription->payment_gateway ), 'change_subscription_payment_method_admin' ) ): ?>

						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<div id="pms-member-subscriptions-billing-schedule" class="postbox">

								<h3 class="hndle">
									<span><?php _e( 'Subscription Billing Schedule', 'paid-member-subscriptions' ); ?></span>
								</h3>

								<div class="inside">

									<?php if( ! empty( $form_data['payment_profile_id'] ) ): ?>

										<div class="pms-meta-box-field-wrapper">
											<label class="pms-meta-box-field-label"><?php _e( 'Payment Gateway Subscription ID', 'paid-member-subscriptions' ); ?></label>

											<span class="readonly medium"><strong><?php echo $form_data['payment_profile_id']; ?></strong></span>

											<p class="description"><?php _e( 'The subscription payment schedule is handled by the payment gateway.', 'paid-member-subscriptions' ); ?></p>

											<?php do_action( 'pms_meta_box_field_payment_profile_id', $form_data ); ?>

										</div>

									<?php else: ?>

										<!-- Recurring Duration and Duration Unit -->
										<div class="pms-meta-box-field-wrapper">

										    <label for="pms-subscription-billing-duration" class="pms-meta-box-field-label"><?php _e( 'Recurring Once Every', 'paid-member-subscriptions' ); ?></label>

										    <input type="text" id="pms-subscription-billing-duration" name="billing_duration" value="<?php echo ( ! empty( $form_data['billing_duration'] ) ? $form_data['billing_duration'] : '' ); ?>" />

										    <select id="pms-subscription-plan-duration-unit" name="billing_duration_unit">
										    	<option value=""><?php _e( 'Choose...', 'paid-member-subscriptions' ); ?></option>
										        <option value="day"   <?php selected( 'day', ( ! empty( $form_data['billing_duration_unit'] ) ? $form_data['billing_duration_unit'] : '' ), true ); ?>><?php _e( 'Day(s)', 'paid-member-subscriptions' ); ?></option>
										        <option value="week"  <?php selected( 'week', ( ! empty( $form_data['billing_duration_unit'] ) ? $form_data['billing_duration_unit'] : '' ), true ); ?>><?php _e( 'Week(s)', 'paid-member-subscriptions' ); ?></option>
										        <option value="month" <?php selected( 'month', ( ! empty( $form_data['billing_duration_unit'] ) ? $form_data['billing_duration_unit'] : '' ), true ); ?>><?php _e( 'Month(s)', 'paid-member-subscriptions' ); ?></option>
										        <option value="year"  <?php selected( 'year', ( ! empty( $form_data['billing_duration_unit'] ) ? $form_data['billing_duration_unit'] : '' ), true ); ?>><?php _e( 'Year(s)', 'paid-member-subscriptions' ); ?></option>
										    </select>

										</div>

										<!-- Billing Next Payment -->
										<div class="pms-meta-box-field-wrapper">

										    <label for="pms-subscription-billing-next-payment" class="pms-meta-box-field-label"><?php _e( 'Next Payment', 'paid-member-subscriptions' ); ?></label>

										    <input id="pms-subscription-billing-next-payment" type="text" name="billing_next_payment" class="datepicker pms-subscription-field" value="<?php echo ( ! empty( $form_data['billing_next_payment'] ) ? esc_attr( pms_sanitize_date( $form_data['billing_next_payment'] ) ) : '' ); ?>" />

										</div>

										<!-- Payment Gateway -->
										<?php $payment_gateways = pms_get_payment_gateways(); ?>
										<div class="pms-meta-box-field-wrapper">
										    <label class="pms-meta-box-field-label"><?php _e( 'Payment Gateway', 'paid-member-subscriptions' ); ?></label>

											<span class="readonly medium"><strong><?php echo !empty( $payment_gateways[$form_data['payment_gateway']]['display_name_admin'] ) ? $payment_gateways[$form_data['payment_gateway']]['display_name_admin'] : $form_data['payment_gateway']; ?></strong></span>
											<input type="hidden" name="payment_gateway" value="<?php echo !empty( $form_data['payment_gateway'] ) ? $form_data['payment_gateway'] : ''; ?>" />
										</div>

										<!-- Payment gateway extra custom fields -->
										<?php
											echo '<div id="pms-meta-box-fields-wrapper-payment-gateways">';

											foreach( $payment_gateways as $payment_gateway_slug => $payment_gateway_details ) {

												echo '<div data-payment-gateway="' . $payment_gateway_slug . '">';

													/**
													 * Action to add extra payment gateway fields
													 *
													 * @param int    $subscription_id
													 * @param string $payment_gateway_slug
													 * @param array  $payment_gateway_details
													 *
													 */
													do_action( 'pms_view_add_new_edit_subscription_payment_gateway_extra', ( ! empty( $form_data['id'] ) ? $form_data['id'] : 0 ), $payment_gateway_slug, $payment_gateway_details );

												echo '</div>';

											}

											echo '</div>';
										?>

									<?php endif; ?>

								</div>

							</div>
						</div>

						<?php endif; ?>

					</div><!-- End Main Content -->

				</div>
			</div>
		</div>

        <?php wp_nonce_field( 'pms_' . $subpage . '_nonce' ); ?>

    </form>

</div>
