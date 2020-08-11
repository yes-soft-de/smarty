<?php
/**
 * Members Export Class
 *
 * This class handles test export in batches
 *
 * @package     Paid Member Subscriptions
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Cristian Antohe. Initial code extracted from Easy Digital Downloads by Pippin Williamson.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.7.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * PMS_Batch_Export_Members Class
 *
 * @since 1.7.6
 */
class PMS_Batch_Export_Members extends PMS_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.7.6
	 */
	public $export_type = 'members';

    /**
     * The number of records we process
     * @var int
     * @since 1.7.6
     */
    public $total = 0;
	/**
	 * Set the CSV columns
	 *
	 * @since 1.7.6
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
	    $cols = array(
            'user_username'     => 'user_username',
            'user_email'        => 'user_email',
            'user_firstname'    => 'user_firstname',
            'user_lastname'     => 'user_lastname',

            'subscription_name'     => 'subscription_name',

            'subscription_id'                       => 'subscription_id',
            'subscription_user_id'                  => 'subscription_user_id',
            'subscription_start_date'               => 'subscription_start_date',
            'subscription_expiration_date'          => 'subscription_expiration_date',
            'subscription_status'			        => 'subscription_status',
            'subscription_auto_renewal'             => 'subscription_auto_renewal',
            'subscription_payment_profile_id'       => 'subscription_payment_profile_id',
            'subscription_payment_gateway'          => 'subscription_payment_gateway',
            'subscription_billing_amount'           => 'subscription_billing_amount',
            'subscription_billing_duration'         => 'subscription_billing_duration',
            'subscription_billing_duration_unit'    => 'subscription_billing_duration_unit',
            'subscription_billing_cycles'           => 'subscription_billing_cycles',
            'subscription_billing_next_payment'     => 'subscription_billing_next_payment',
            'subscription_billing_last_payment'     => 'subscription_billing_last_payment',
            'subscription_trial_end'                => 'subscription_trial_end',
        );

        $meta_keys = $this->get_all_pms_meta_keys( "pms_member_subscriptionmeta" );
        foreach ( $meta_keys as $meta_key ){
            $cols['subscriptionmeta_' . $meta_key] = 'subscriptionmeta_' . $meta_key;
        }

        $usermeta_titles = ( isset( $_REQUEST['pms-filter-user-meta-title'] ) ? $_REQUEST['pms-filter-user-meta-title'] : [] );
        $usermeta_keys = ( isset( $_REQUEST['pms-filter-user-meta'] ) ? $_REQUEST['pms-filter-user-meta'] : [] );

        update_user_meta(get_current_user_id(), 'pms_export_meta', array_combine( $usermeta_keys, $usermeta_titles ));

        foreach ( $usermeta_keys as $i => $umeta_key ){
            $umeta_key = sanitize_text_field($umeta_key);
            if(isset($usermeta_titles[$i]) && !empty($usermeta_titles[$i])){
                $cols['usermeta_' . $umeta_key] = sanitize_text_field($usermeta_titles[$i]);
            } else {
                $cols['usermeta_' . $umeta_key] = 'usermeta_' . $umeta_key;
            }
        }

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 1.7.6
	 * @global object $wpdb Used to query the database using the WordPress Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;
        $data = array();

        /**
         * Set member arguments
         *
         */
        $member_status = ( isset( $_REQUEST['pms-filter-member-status'] ) ? sanitize_text_field( $_REQUEST['pms-filter-member-status'] ) : '' );
        $usermeta_keys = ( isset( $_REQUEST['pms-filter-user-meta'] ) ? $_REQUEST['pms-filter-user-meta'] : [] );

        $args['number'] = 10;
        $args['offset'] = ( $this->step - 1 ) * 10;
        $args['member_subscription_status'] = $member_status;
        $args['subscription_plan_id'] = (int)$_REQUEST['pms-filter-subscription-plan'];

        // Get the members
        $members = pms_get_members( $args );

        // Get the total number of members
        $this->total = pms_get_members( $args, true );

		if( $members )  {
            $meta_keys = $this->get_all_pms_meta_keys( "pms_member_subscriptionmeta" );

			foreach ( $members as $member ) {

                $user_data = array();
                $user = get_userdata( $member->user_id );

                $user_data['user_username'] = $user->user_login;
                $user_data['user_email'] = $user->user_email;
                $user_data['user_firstname'] = $user->user_firstname;
                $user_data['user_lastname'] = $user->user_lastname;

                $member_subscriptions = pms_get_member_subscriptions(  array( 'user_id' => $member->user_id ) ) ;

                foreach($member_subscriptions as $subscription){

                    if ( $subscription->subscription_plan_id != $args['subscription_plan_id'] && $args['subscription_plan_id'] != '0' ){
                        continue;
                    }

                    $subscription_plan = pms_get_subscription_plan( $subscription->subscription_plan_id );
                    $pms_subscription_name = array();
                    $pms_subscription_name["subscription_name"] = $subscription_plan->name;

                    $member_subscriptions = array(
                        'subscription_id'                       => $subscription->id,
                        'subscription_user_id'                  => $subscription->user_id,
                        'subscription_start_date'               => pms_sanitize_date( $subscription->start_date ),
                        'subscription_expiration_date'          => pms_sanitize_date( $subscription->expiration_date ),
                        'subscription_status'			        => $subscription->status,
                        'subscription_auto_renewal'             => $subscription->is_auto_renewing(),
                        'subscription_payment_profile_id'       => $subscription->payment_profile_id,
                        'subscription_payment_gateway'          => $subscription->payment_gateway,
                        'subscription_billing_amount'           => $subscription->billing_amount,
                        'subscription_billing_duration'         => $subscription->billing_duration,
                        'subscription_billing_duration_unit'    => $subscription->billing_duration_unit,
                        'subscription_billing_cycles'           => $subscription->billing_cycles,
                        'subscription_billing_next_payment'     => $subscription->billing_next_payment,
                        'subscription_billing_last_payment'     => $subscription->billing_last_payment,
                        'subscription_trial_end'                => $subscription->trial_end,
                    );

                    $member_subscriptionmeta = array();
                    foreach ( $meta_keys as $meta_key ){
                        $member_subscriptionmeta['subscriptionmeta_' . $meta_key] = pms_get_member_subscription_meta( $subscription->id, $meta_key, true );
                    }

                    foreach ( $usermeta_keys as $umeta_key ){
                        $umeta_key = sanitize_text_field($umeta_key);
                        $member_subscriptionmeta['usermeta_' . $umeta_key] = get_user_meta( $member->user_id, $umeta_key, true );
                    }

                    $data[] = array_merge( $user_data, $pms_subscription_name, $member_subscriptions, $member_subscriptionmeta );
                } // end foreach subscriptions
			} // end foreach members

			return $data;
		}

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 1.7.6
	 * @return int
	 */
	public function get_percentage_complete() {

		$status = $this->status;
		$args   = array(
			'start-date' => date( 'n/d/Y', strtotime( $this->start ) ),
			'end-date'   => date( 'n/d/Y', strtotime( $this->end ) ),
		);

		$percentage = 100;

		if( $this->total > 0 ) {
			$percentage = ( ( 10 * $this->step ) / $this->total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the payments export
	 *
	 * @since 1.7.6
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->start  = isset( $request['start'] )  ? sanitize_text_field( $request['start'] )  : '';
		$this->end    = isset( $request['end']  )   ? sanitize_text_field( $request['end']  )   : '';
		$this->status = isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : 'complete';
	}
}
