<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Member class stores and handles user data that is specific only for members
 *
 */
Class PMS_Member {

    /**
     * User ID
     *
     * @access public
     * @var int
     */
    public $user_id;

    /**
     * User name
     *
     * @access public
     * @var string
     */
    public $username;

    /**
     * User email
     *
     * @access public
     * @var string
     */
    public $email;

    /**
     * Member subscriptions data
     *
     * @access public
     * @var array
     */
    public $subscriptions;


    /**
     * Constructor
     *
     */
    public function __construct( $user_id = 0 ) {

        add_action( 'pms_member_before_remove_subscription', array( $this, 'before_remove_subscription_status_cancel' ), 10, 2 );

        $this->init( $user_id );

    }


    /**
     * Initialize method where we set the member data
     *
     * @param $user_id  - id of the user
     *
     */
    public function init( $user_id ) {

        $user_data = get_userdata( $user_id );

        $this->user_id = $user_id;

        if( !$user_data )
            return null;

        // Set member data
        $this->username = $user_data->user_login;
        $this->email = $user_data->user_email;

        // Set subscriptions
        $this->subscriptions = $this->get_subscriptions();

    }


    /**
     * Method that returns the properties as an associative array
     *
     * @return array
     *
     */
    public function to_array() {

        return get_object_vars( $this );

    }


    /**
     * Method that returns an array with the class properties
     *
     * @return array
     *
     */
    public static function get_properties() {

        $properties = array();

        $class_vars = get_class_vars( __CLASS__ );

        foreach( $class_vars as $class_var => $class_var_value ) {

            $properties[] = $class_var;

        }

        return $properties;

    }


    /**
     * Return from the database the subscriptions associated with a user
     *
     * @return array
     *
     */
    public function get_subscriptions() {
        global $wpdb;

        $subscriptions = $wpdb->get_results( "SELECT id, subscription_plan_id, start_date, expiration_date, status, payment_profile_id FROM {$wpdb->prefix}pms_member_subscriptions WHERE user_id = $this->user_id AND status != 'abandoned' ", ARRAY_A );

        return $subscriptions;

    }


    /**
     * Return an array that contains data about a member subscription
     *
     * @param $subscription_plan_id
     *
     * @return array
     *
     */
    public function get_subscription( $subscription_plan_id ) {

        if( false !== ( $key = array_search( $subscription_plan_id, $this->get_subscriptions_ids() ) ) )
            return $this->subscriptions[$key];
        else
            return array();

    }


    /**
     * Returns the number of subscriptions that a member has
     *
     * @return int
     *
     */
    public function get_subscriptions_count() {

        return count( $this->subscriptions );

    }


    /**
     * Returns an array with the ids of the subscription plans associated with the member
     *
     * @return array
     *
     */
    public function get_subscriptions_ids() {

        $subscription_ids = array();

        foreach( $this->subscriptions as $subscription )
            $subscription_ids[] = $subscription['subscription_plan_id'];

        return $subscription_ids;

    }


    /**
     * Before removing a subscription from a member, if it is active we're
     * going to first change its status to canceled
     *
     */
    public function before_remove_subscription_status_cancel( $user_id, $subscription_plan_id ) {

        $member_subscription = $this->get_subscription( $subscription_plan_id );

        if( $member_subscription['status'] == 'active' ) {

            $this->update_subscription( $subscription_plan_id, $member_subscription['start_date'], $member_subscription['expiration_date'], 'canceled' );

        }

    }


    /**
     * Removes from the database a member's subscription
     *
     * @deprecated 1.5.0                    Will be removed in the future
     *
     * @param $subscription_plan_id
     *
     * @return bool
     *
     */
    public function remove_subscription( $subscription_plan_id ) {

        global $wpdb;

        do_action( 'pms_member_before_remove_subscription', $this->user_id, $subscription_plan_id );

        $delete_result = $wpdb->delete( $wpdb->prefix . 'pms_member_subscriptions', array( 'user_id' => $this->user_id, 'subscription_plan_id' => $subscription_plan_id ) );

        do_action( 'pms_member_remove_subscription', $delete_result, $this->user_id, $subscription_plan_id );

        return $delete_result;

    }


    /**
     * Updates in the database the member's subscription data
     *
     * @deprecated 1.5.0                    Will be removed in the future
     *
     * @param $subscription_plan_id
     * @param $start_date
     * @param $expiration_date
     * @param $status
     *
     * @return bool
     *
     */
    public function update_subscription( $subscription_plan_id, $start_date, $expiration_date, $status = 'pending' ) {

        global $wpdb;

        // Automatically update status to correct state ('expired' or 'active') in case $start_date or $expiration_date have been modified
        if ( ( $status != 'canceled') && ($status != 'pending') ) {
            if ( ( $status == 'active' ) && ( ( strtotime($start_date) > strtotime($expiration_date) ) || ( time() > strtotime($expiration_date) ) ) ) $status = 'expired';
            if ( ( $status == 'expired') &&  ( time() < strtotime($expiration_date) ) ) $status = 'active';
        }

        $update_result = $wpdb->update( $wpdb->prefix . 'pms_member_subscriptions', array( 'start_date' => $start_date, 'expiration_date' => $expiration_date, 'status' => $status ), array( 'user_id' => $this->user_id, 'subscription_plan_id' => $subscription_plan_id ) );

        // Can return 0 if no data was changed
        if( $update_result !== false )
            $update_result = true;

        do_action( 'pms_member_update_subscription', $update_result, $this->user_id, $subscription_plan_id, $start_date, $expiration_date, $status );

        return $update_result;

    }


    /**
     * Updates in the database the subscription plan id for a member's subscription
     *
     * @deprecated 1.5.0                    Will be removed in the future
     *
     * @param $old_subscription_plan_id
     * @param $new_subscription_plan_id
     *
     * @return bool
     *
     */
    public function replace_subscription( $old_subscription_plan_id, $new_subscription_plan_id ) {

        global $wpdb;

        $update_result = $wpdb->update( $wpdb->prefix . 'pms_member_subscriptions', array( 'subscription_plan_id' => $new_subscription_plan_id ), array( 'user_id' => $this->user_id, 'subscription_plan_id' => $old_subscription_plan_id ) );

        // Can return 0 if no data was changed
        if( $update_result !== false )
            $update_result = true;

        do_action( 'pms_member_replace_subscription', $update_result, $this->user_id, $new_subscription_plan_id, $old_subscription_plan_id );

        return $update_result;

    }


    /**
     * Add a new subscription in the database for this member
     *
     * @deprecated 1.5.0                    Will be removed in the future
     *
     * @param $subscription_plan_id
     * @param $start_date
     * @param $expiration_date
     * @param $status
     *
     * @return bool
     *
     */
    public function add_subscription( $subscription_plan_id, $start_date, $expiration_date, $status ) {

        global $wpdb;

        // If the start date is set after the expiration date, change status to 'expired'
        if ( ( strtotime($start_date) > strtotime($expiration_date) ) && ( $status == 'active' ) ) $status = 'expired';

        $insert_result = $wpdb->insert( $wpdb->prefix . 'pms_member_subscriptions', array( 'user_id' => $this->user_id, 'subscription_plan_id' => $subscription_plan_id, 'start_date' => $start_date, 'expiration_date' => $expiration_date, 'status' => $status ) );

        do_action( 'pms_member_add_subscription', $insert_result, $this->user_id, $subscription_plan_id, $start_date, $expiration_date, $status );

        return $insert_result;

    }


    /**
     * Method that checks if the user has a subscription plan id associated
     *
     * @return bool     - true if finds a subscription plan id, false if it doesn't
     *
     */
    public function is_member() {

        if( empty( $this->subscriptions ) )
            return false;
        else
            return true;

    }
}
