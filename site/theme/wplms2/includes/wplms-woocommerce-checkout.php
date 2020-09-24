<?php
if ( !defined( 'ABSPATH' ) ) exit;
if(!class_exists('WC_Courses_Checkout')){
   class WC_Courses_Checkout {


        /** @var bool true when enable signup option has been changed */
        public $enable_signup_changed;

        /** @var bool true when enable guest checkout option has been changed */
        public $enable_guest_checkout_changed;


        public static $instance;
    
        public static function init(){

            if ( is_null( self::$instance ) )
                self::$instance = new WC_Courses_Checkout();

            return self::$instance;
        }


        /**
         * Constructor
         *
         * @since 1.0.0
         */
        public function __construct() {
            $enable_force_wplms = apply_filters('wplms_force_registration_during_checkout',1);

            if($enable_force_wplms){
                // users must be able to register on checkout
                // note this runs at -1 priority to ensure this is set before any other hooks
                add_action( 'woocommerce_before_checkout_form', array( $this, 'maybe_enable_registration' ), -1 );

                // mark checkout registration fields as required
                add_action( 'woocommerce_checkout_fields', array( $this, 'maybe_require_registration_fields' ) );

                // remove guest checkout param from WC checkout JS
                add_filter( 'woocommerce_get_script_data', array( $this, 'remove_guest_checkout_js_param' ) );

                // force registration during checkout process
                add_action( 'woocommerce_before_checkout_process', array( $this, 'maybe_force_registration_during_checkout' ) );
            }
        }


        /**
         * If shopping cart contains subscriptions, make sure a user can register on the checkout page
         *
         * @param \WC_Checkout $checkout instance
         * @since 1.0.0
         */
        public function maybe_enable_registration( $checkout = null ) {
            if ( $checkout && $this->force_registration() ) {

                // enable signups
                if ( false === $checkout->enable_signup ) {
                    $checkout->enable_signup = true;
                    $this->enable_signup_changed = true;
                }

                // disable guest checkout
                if ( true === $checkout->enable_guest_checkout ) {
                    $checkout->enable_guest_checkout = false;
                    $checkout->must_create_account = true;
                    $this->enable_guest_checkout_changed = true;
                }

                // restore previous settings after checkout has loaded
                if ( $this->enable_signup_changed || $this->enable_guest_checkout_changed ) {
                    add_action( 'woocommerce_after_checkout_form', array( $this, 'restore_registration_settings' ), 9999 );
                }
            }
        }


        /**
         * Restore the original checkout registration settings after checkout has loaded
         *
         * @param \WC_Checkout $checkout instance
         * @since 1.0.0
         */
        public function restore_registration_settings( $checkout = null ) {

            // re-disable signups
            if ( $this->enable_signup_changed ) {
                $checkout->enable_signup = false;
            }

            // re-enable guest checkouts
            if ( $this->enable_guest_checkout_changed ) {
                $checkout->enable_guest_checkout = true;
                $checkout->must_create_account = false;
            }
        }


        /**
         * Mark the account fields as required
         *
         * @since 1.0.0
         */
        public function maybe_require_registration_fields( $fields ) {

            if ( $this->force_registration() ) {

                foreach ( array( 'account_username', 'account_password', 'account_password-2' ) as $field ) {
                    if ( isset( $fields['account'][ $field ] ) ) {
                        $fields['account'][ $field ]['required'] = true;
                    }
                }
            }

            return $fields;
        }


        /**
         * Remove the guest checkout param from WC checkout JS so the registration
         * form isn't hidden
         *
         * @since 1.0.0
         * @param array $params checkout JS params
         * @return array
         */
        public function remove_guest_checkout_js_param( $params ) {

            if ( $this->force_registration() && isset( $params['option_guest_checkout'] ) && 'yes' == $params['option_guest_checkout'] ) {
                $params['option_guest_checkout'] = 'no';
            }

            return $params;
        }


        /**
         * Force registration during the checkout process
         *
         * @since 1.0.0
         */
        public function maybe_force_registration_during_checkout() {

            if ( $this->force_registration() ) {
                $_POST['createaccount'] = 1;
            }
        }


        /**
         * Check if registration should be forced if all of the following are true:
         *
         * 1) user is not logged in
         * 2) an item in the cart contains a product that grants access to a membership
         *
         * @since 1.0.0
         * @return bool
         */
        protected function force_registration() {

            if ( is_user_logged_in() ) {
                return false;
            }

            if(!WC()->cart){
                return false;
            }
            $cart = WC()->cart->get_cart();
            if(WC()->cart && !empty($cart)){
                foreach (WC()->cart->get_cart() as $item ) {
                    $courses  = get_post_meta($item['product_id'],'vibe_courses',true);
                    if (!empty( $courses )) {
                        return true;
                    }
                }
            }else{
                return false;
            }
            return false;
        }

    }
    WC_Courses_Checkout::init(); 
}
