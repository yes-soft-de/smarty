<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class PMS_Member_Subscription {

	public $id = 0;

	public $user_id = 0;

	public $subscription_plan_id = 0;

	public $start_date;

	public $expiration_date;

	public $status;

	public $payment_profile_id;

	public $payment_gateway;

	public $billing_amount;

	public $billing_duration;

	public $billing_duration_unit;

	public $billing_cycles;

	public $billing_next_payment;

	public $billing_last_payment;

	public $trial_end;


	/**
	 * Construct
	 *
	 * @param array $data - the subscription data
	 *
	 */
	public function __construct( $data = array() ) {

		$this->set_instance( $data );

	}


	/**
	 * Sets the values of the object properties to the provided data
	 *
	 * @param array $data - the subscription data
	 *
	 */
	public function set_instance( $data = array() ) {

		// Grab all properties and populate them
        foreach( get_object_vars( $this ) as $property => $value ) {

            if( isset( $data[$property] ) ) {

            	// Empty dates overwrite
            	if( $data[$property] == '0000-00-00 00:00:00' )
            		$data[$property] = '';

                $this->$property = $data[$property];

            }

        }

	}


	/**
	 * Clears the instance data
	 *
	 */
	public function clear_instance() {

		foreach( get_class_vars( __CLASS__ ) as $property => $value ) {
			$this->$property = $value;
		}


	}


	/**
	 * Inserts a new member subscription into the database
	 *
	 * @param array $data - the array of data for the member subscription
	 *
	 * @return mixed int|false
	 *
	 */
	public function insert( $data = array() ) {

		global $wpdb;

		// Clean the data array
        $data = $this->sanitize_data( $data );

        // Insert member subscription
		$insert_result = $wpdb->insert( $wpdb->prefix . 'pms_member_subscriptions', $data );

		if( $insert_result ) {

            // Populate current object
            $this->id = $wpdb->insert_id;
            $this->set_instance( $data );

            /**
             * Fires right after the Member Subscription db entry was inserted into the db
             *
             * @param int   $id               - the id of the new member subscription
             * @param array $data             - the provided data for the current member subscription
             *
             */
            do_action( 'pms_member_subscription_insert', $this->id, $data );


            /**
		     * Fires right after the Member Subscription db entry was inserted into the db
		     *
		     * @deprecated 1.6.1 					- Scheduled for official removal in version 2.0.0
		     *
		     * @param int   $member_subscription_id - the id of the new member subscription
		     * @param array $data             		- the provided data for the current member subscription
		     *
		     */
		    do_action( 'pms_member_subscription_inserted', $this->id, $data );


            /**
			 * Compatibility hook from class PMS_Member in add_subscription() method
			 *
			 * @deprecated 1.5.0 					- Scheduled for official removal in version 2.0.0
			 *
			 * @param bool   $insert_result			- the result of the database insertion
			 * @param int  	 $user_id				- the id of the user attached to this subscription
			 * @param int  	 $subscription_plan_id  - the id of the subscription plan attached to this subscription
			 * @param string $start_date   			- the start date of the subscription
			 * @param string $expiration_date 		- the expiration date of the subscription
			 * @param string $status 				- the status of the subscription
			 *
			 */
            do_action( 'pms_member_add_subscription', $insert_result, $this->user_id, $this->subscription_plan_id, $this->start_date, $this->expiration_date, $this->status );

            return $this->id;

        }

        return false;

	}


	/**
	 * Updates an existing member subscription with the new provided data
	 *
	 * @param array $data - the new datas to be updated for the member subscription
	 *
	 * @return bool
	 *
	 */
	public function update( $data = array() ) {

		global $wpdb;

		// Clean the data array
		$data = $this->sanitize_data( $data );

		// We don't want the id to be updated
		if( isset( $data['id'] ) )
			unset( $data['id'] );

		// Update the member subscription
		$update_result = $wpdb->update( $wpdb->prefix . 'pms_member_subscriptions', $data, array( 'id' => $this->id ) );

		// Can return 0 if no rows are affected
        if( $update_result !== false )
            $update_result = true;


		if( $update_result ) {

			/**
			 * Fires right after the Member Subscription db entry was updated
			 *
			 * @param int 	$id 		   - the id of the subscription that has been updated
			 * @param array $data 		   - the array of values to be updated for the subscription
			 * @param array $old_data 	   - the array of values representing the subscription before the update
			 *
			 */
			do_action( 'pms_member_subscription_update', $this->id, $data, $this->to_array() );


			/**
			 * Fires right after the Member Subscription db entry was updated
			 *
			 * @deprecated 1.6.1		   - Scheduled for official removal in version 2.0.0
			 *
			 * @param int 	$id 		   - the id of the subscription that has been updated
			 * @param array $data 		   - the array of values to be updated for the subscription
			 * @param array $old_data 	   - the array of values representing the subscription before the update
			 *
			 */
			do_action( 'pms_member_subscription_updated', $this->id, $data, $this->to_array() );


			// Update the current instance with the new data values
			$this->set_instance( $data );

		}

		/**
		 * Compatibility hook from class PMS_Member in update_subscription() method
		 *
		 * @deprecated 1.5.0 					- Scheduled for official removal in version 2.0.0
		 *
		 * @param bool   $update_result			- the result of the database update
		 * @param int  	 $user_id				- the id of the user attached to this subscription
		 * @param int  	 $subscription_plan_id  - the id of the subscription plan attached to this subscription
		 * @param string $start_date   			- the start date of the subscription
		 * @param string $expiration_date 		- the expiration date of the subscription
		 * @param string $status 				- the status of the subscription
		 *
		 */
		do_action( 'pms_member_update_subscription', $update_result, $this->user_id, $this->subscription_plan_id, $this->start_date, $this->expiration_date, $this->status );

		return $update_result;

	}


	/**
	 * Removes the current member subscription from the database
	 *
	 * @return bool
	 *
	 */
	public function remove() {

		global $wpdb;

		$delete_result = $wpdb->delete( $wpdb->prefix . 'pms_member_subscriptions', array( 'id' => $this->id ) );

		// Can return 0 if no rows are affected
        if( $delete_result !== false )
            $delete_result = true;

        if( $delete_result ) {

			/**
			 * Fires right after a member subscription has been deleted, but before metadata is deleted
			 *
			 * @param int   $id   	  - the id of the member subscription that has just been deleted from the db
			 * @param array $old_data - the data the subscription had at the moment of deletion
			 *
			 */
			do_action( 'pms_member_subscription_before_metadata_delete', $this->id, $this->to_array() );

        	/**
        	 * Remove all meta data
        	 *
        	 */
        	$meta_delete_result = $wpdb->delete( $wpdb->prefix . 'pms_member_subscriptionmeta', array( 'member_subscription_id' => $this->id ) );

        	/**
	         * Fires right after a member subscription has been deleted
	         *
	         * @param int   $id   	  - the id of the member subscription that has just been deleted from the db
	         * @param array $old_data - the data the subscription had at the moment of deletion
	         *
	         */
	        do_action( 'pms_member_subscription_delete', $this->id, $this->to_array() );


	        /**
		     * Fires right after a member subscription has been deleted
		     *
		     * @deprecated 1.6.1	  - Scheduled for official removal in version 2.0.0
		     *
		     * @param int   $id   	  - the id of the member subscription that has just been deleted from the db
		     * @param array $old_data - the data the subscription had at the moment of deletion
		     *
		     */
		    do_action( 'pms_member_subscription_deleted', $this->id, $this->to_array() );


	        // Clear the current object instance
	        $this->clear_instance();

        }

        return $delete_result;

	}


	/**
	 * Verifies if the current subscription is auto renewing
	 * What this means is that it either has a subscription equivalent in one of the payment gateways
	 * or that it has a renewal schedule set in the database for it
	 *
	 * @return bool
	 *
	 */
	public function is_auto_renewing() {

		if( $this->status == 'expired' || $this->status == 'canceled' )
			return false;

		if( ! empty( $this->payment_profile_id ) )
			return true;

		if( ( ! empty( $this->billing_duration ) && ! empty( $this->billing_duration_unit ) ) )
			return true;

		return false;

	}


    /**
     * Checks to see if the current subscription is in its trial period or not
     *
     * @return bool
     *
     */
    public function is_trial_period() {

        if( empty( $this->trial_end ) )
            return false;

        if( strtotime( $this->trial_end ) < time() )
            return false;

        return true;

    }


	/**
	 * Eliminate all values from the provided data array that are not a part of the object
	 *
	 * @param array $data
	 *
	 * @return array
	 *
	 */
	private function sanitize_data( $data = array() ) {

		// Strip data of any script tags
		$data = pms_array_strip_script_tags( $data );

		$object_vars = array_keys( get_object_vars( $this ) );

        foreach( $data as $key => $val ) {

            if( !in_array( $key, $object_vars ) )
                unset( $data[$key] );

        }

        return $data;

	}


	/**
	 * Returns the array representation of the current object instance
	 *
	 */
	public function to_array() {

		return get_object_vars( $this );

	}

}
