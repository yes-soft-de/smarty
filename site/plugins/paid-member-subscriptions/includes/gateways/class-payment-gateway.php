<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Payment Gateways base class
 *
 */
Class PMS_Payment_Gateway {

    /**
     * Payment id
     *
     * @access public
     * @var int
     */
    public $payment_id;

    /**
     * User id
     *
     * @access public
     * @var int
     */
    public $user_id;

    /**
     * User email
     *
     * @access public
     * @var string
     */
    public $user_email;

    /**
     * Subscription plan
     *
     * @access public
     * @var object
     */
    public $subscription_plan;

    /**
     * Subscription plan price currency
     *
     * @access public
     * @var string
     */
    public $currency;

    /**
     * Subscription plan price
     *
     * @access public
     * @var int
     */
    public $amount;

    /**
     * Sign up amount
     *
     * @access public
     * @var int
     */
    public $sign_up_amount;

    /**
     * Recurring payment
     *
     * @access public
     * @var string
     */
    public $recurring;

    /**
     * Redirect URL
     *
     * @access public
     * @var string
     */
    public $redirect_url;

    /**
     * Form location
     *
     * @access public
     * @var string
     */
    public $form_location;

    /**
     * The user data at checkout
     *
     * @access public
     * @var array
     *
     */
    public $user_data;

    /**
     * The member subscription data at checkout
     *
     * @access public
     * @var array
     *
     */
    public $subscription_data;

    /**
     * If test mode
     *
     * @access public
     * @var bool
     */
    public $test_mode;


    /**
     * A list of supported features
     *
     * @access public
     * @var array
     */
    public $supports = array();


    /**
     * Constructor
     *
     * @param array $data
     *
     */
    public function __construct( $data = array() ) {

        $this->payment_id        = ( isset( $data['payment_id'] ) ? $data['payment_id'] : 0 );
        $this->user_id           = ( isset( $data['user_data']['user_id'] ) ? $data['user_data']['user_id'] : 0 );
        $this->user_email        = ( isset( $data['user_data']['user_email'] ) ? $data['user_data']['user_email'] : '' );
        $this->subscription_plan = ( isset( $data['user_data']['subscription'] ) && is_object( $data['user_data']['subscription'] ) ? $data['user_data']['subscription'] : '' );
        $this->currency          = ( isset( $data['currency'] ) ? $data['currency'] : pms_get_active_currency() );
        $this->amount            = ( isset( $data['amount'] ) ? $data['amount'] : 0 );
        $this->sign_up_amount    = ( isset( $data['sign_up_amount'] ) ? $data['sign_up_amount'] : NULL );
        $this->recurring         = ( isset( $data['recurring'] ) ? $data['recurring'] : 0 );
        $this->redirect_url      = ( isset( $data['redirect_url'] ) ? $data['redirect_url'] : '' );
        $this->form_location     = ( isset( $data['form_location'] ) ? $data['form_location'] : '' );

        $this->test_mode         = pms_is_payment_test_mode();

        $this->user_data         = ( isset( $data['user_data'] ) ? $data['user_data'] : '' );
        $this->subscription_data = ( isset( $data['subscription_data'] ) ? $data['subscription_data'] : '' );

        $this->init();

        /**
         * Gateway initialised
         *
         */
        do_action( 'pms_payment_gateway_initialised', $this, $data );

    }

    public function init() {}

    public function process_sign_up() {}

    public function process_webhooks() {}

    public function fields() {}

    /**
     * Validates gateway specific fields
     *
     * Should be replaced by sub-classes
     *
     */
    public function validate_fields() {}

    /**
     * Validates if gateway specific settings are present (e.g. paypal email address)
     *
     * Should be replaced by sub-classes
     */
    public function validate_credentials() {}

    /**
     * Processes a one-time payment
     *
     * @param int $payment_id       - the payment that needs to be processed
     * @param int $subscription_id  - (optional) the subscription for which the payment is made
     *
     * @return bool
     *
     */
    public function process_payment( $payment_id = 0, $subscription_id = 0 ) {

        return false;

    }


    /**
     * To make automatic future payments for a subscription certain information from the payment gateway
     * should be saved for later access
     *
     * @param int $subscription_id
     *
     * @return bool
     *
     */
    public function register_automatic_billing_info( $subscription_id = 0 ) {

        return false;

    }


    /**
     * Checks to see if a certain feature is supported by the payment gateway
     *
     * @param string $feature
     *
     * @return bool
     *
     */
    public function supports( $feature = '' ) {

        return ( in_array( $feature , $this->supports ) ? true : false );

    }

}
