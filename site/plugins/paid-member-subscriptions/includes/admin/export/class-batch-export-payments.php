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
class PMS_Batch_Export_Payments extends PMS_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.7.6
	 */
	public $export_type = 'payments';

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

            'payment_id'                   => 'payment_id',
            'payment_user_id'              => 'payment_user_id',
            'payment_subscription_plan_id' => 'payment_subscription_plan_id', // this is how it's in the database. The PMS_Payment object actually uses subscription_id but it's actually the subscription PLAN id.
            'payment_status'               => 'payment_status',
            'payment_date'                 => 'payment_date',
            'payment_amount'               => 'payment_amount',
            'payment_payment_gateway'      => 'payment_payment_gateway',
            // 'pms_payments_currency'             => 'pms_payments_currency', // this is not used so we're not exporting it.
            'payment_type'                 => 'payment_type',
            'payment_transaction_id'       => 'payment_transaction_id',
            'payment_profile_id'           => 'payment_profile_id',
            //'payment_logs'                 => 'payment_logs',
            'payment_ip_address'           => 'payment_ip_address',
            'payment_discount_code'        => 'payment_discount_code',

        );
        $meta_keys = $this->get_all_pms_meta_keys( "pms_paymentmeta" );

        foreach ( $meta_keys as $meta_key ){
            $cols['paymentmeta_' . $meta_key] = 'paymentmeta_' . $meta_key;
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
         * Set payment arguments
         *
         */
        $args['number'] = 10;
        $args['offset'] = ( $this->step - 1 ) * 10;
        $args['status'] = ( isset( $_REQUEST['pms-filter-payment-status'] ) ? sanitize_text_field( $_REQUEST['pms-filter-payment-status'] ) : '' );
        $start_date = ( isset( $_REQUEST['pms-filter-start-date'] ) ? sanitize_text_field( $_REQUEST['pms-filter-start-date'] ) : '' );
        $end_date = ( isset( $_REQUEST['pms-filter-end-date'] ) ? sanitize_text_field( $_REQUEST['pms-filter-end-date'] ) : '' );
        $end_date = date('Y-m-d',strtotime($end_date . "+1 days"));
        $args['date'] = array($start_date, $end_date);

        // Get the members
        $payments = pms_get_payments( $args );

        // Get the total number of members
        $this->total = pms_get_payments_count( $args );

		if( $payments )  {
            $meta_keys = $this->get_all_pms_meta_keys( "pms_paymentmeta" );

			foreach ( $payments as $payment ) {

                $user_data = array();

                $user = get_userdata( $payment->user_id );

                $user_data['user_username'] = $user->user_login;
                $user_data['user_email'] = $user->user_email;
                $user_data['user_firstname'] = $user->user_firstname;
                $user_data['user_lastname'] = $user->user_lastname;

                $subscription_plan = pms_get_subscription_plan( $payment->subscription_id );
                $pms_subscription_name = array();
                $pms_subscription_name["subscription_name"] = $subscription_plan->name;

                $pms_payments = array(
                    'payment_id'                       => $payment->id,
                    'payment_user_id'                  => $payment->user_id,
                    'payment_subscription_plan_id'     => $payment->subscription_id,
                    'payment_status'                   => $payment->status,
                    'payment_date'			           => pms_sanitize_date( $payment->date ),
                    'payment_amount'                   => $payment->amount,
                    'payment_payment_gateway'          => $payment->payment_gateway,
                    'payment_type'                     => $payment->type,
                    'payment_transaction_id'           => $payment->transaction_id,
                    'payment_profile_id'               => $payment->transaction_id,
                    //'payment_logs'                     => $payment->logs,
                    'payment_ip_address'               => $payment->ip_address,
                    'payment_discount_code'            => $payment->discount_code,
                );

                $paymentmeta = array();
                foreach ( $meta_keys as $meta_key ){
                    $paymentmeta['paymentmeta_' . $meta_key] = pms_get_payment_meta( $payment->id, $meta_key, true );
                }

                $data[] = array_merge( $user_data, $pms_subscription_name, $pms_payments, $paymentmeta );

			} // end foreach payments

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
