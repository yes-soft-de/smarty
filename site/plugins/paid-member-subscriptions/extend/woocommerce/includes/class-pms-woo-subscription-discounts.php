<?php
/**
 * Subscription Discounts class
 *
 * This class handles all purchasing discounts for members with an active subscription plan
 *
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


class PMS_WOO_Subscription_Discounts {


    /** @var bool Whether products on sale are excluded from discounts. */
    private $exclude_on_sale_products = false;

    /** @var bool Whether cumulative membership discounts are allowed. */
    private $allow_cumulative_discounts = false;

    /** @var bool Whether the current user is logged in and is an active member. */
    private $is_active_member = false;



    /**
     * Set up subscription discounts for members
     *
     */
    public function __construct() {

        // init discounts so we don't hook too early
        add_action('init', array($this, 'init'));

    }


    /**
     * Init member discounts.
     *
     * We follow here a pattern common in many price-affecting extensions, due to
     * the need to produce a "price before/after discount" type of HTML output,
     * so shop customers can easily understand the deal they're being offered.
     *
     * To do so we need to juggle WooCommerce prices, we start off by instantiating
     * this class with our discounts active, so we can be sure to always pass those
     * to other extensions if a member is logged in. In Paid Member Subscriptions, we
     * filter sale prices and pass member discounts as apparent sale prices. So WooCommerce
     * core can trigger the HTML output sought by Paid Member Subscriptions, which shows a
     * before/after price change.
     *
     * Extensions and third party code that need to know if PMS price modifiers
     * are being applied or not in these two phases, can use doing_action and hook into
     * 'pms_woo_subscription_discounts_enable_price_adjustments' and
     * 'pms_woo_subscription_discounts_disable_price_adjustments', or call directly the
     * callback methods found in this class, which we use to add and remove
     * price modifiers.
     */
    public function init() {

        $this->is_active_member = pms_is_member( get_current_user_id() );

        // check if on sale products are excluded from membership discounts
        $options = get_option( 'pms_woocommerce_settings' );

        if ( !empty($options) && isset($options['exclude_on_sale']) ) {
            $this->exclude_on_sale_products = true;
        }

        // check if "allow cumulative discounts" is set for members of multiple subscription plans with overlapping discount codes for the same products
        if ( !empty($options) && isset($options['cumulative_discounts']) ) {
            $this->allow_cumulative_discounts = true;
        }

        // "Product Discounted - Membership Required" message, displayed to non-members if the product has a membership discount.
        add_action( 'woocommerce_single_product_summary', array($this, 'product_discounted_membership_required_message'), 31 );

        // Refreshes the mini cart upon member login.
        add_action('wp_login', array($this, 'refresh_cart_upon_member_login'), 10, 2);

        // Clear all transients for variation prices after updating/saving a subscription plan (due to possible discount codes modification) which will result in price changes
        add_action('save_post_pms-subscription', array($this, 'after_subscription_plan_discounts_updated'), 10 ,1);
        // Clear all transients for variation prices after updating PMS WooCommerce settings
        add_action('update_option_pms_settings', array($this, 'after_pms_woocommerce_settings_updated'), 10, 2);

        // Subscription discount class methods are available on both frontend and backend,
        // but the hooks below should run in frontend only for logged in members.
        if (!(is_admin() && !is_ajax())) {

            // Initialize discount actions that will be called in this class methods
            add_action('pms_woo_discounts_enable_price_adjustments', array($this, 'enable_price_adjustments'));
            add_action('pms_woo_discounts_disable_price_adjustments', array($this, 'disable_price_adjustments'));

            if ($this->is_active_member) {

                // Save the subscription plan product discounts for active member in a global variable
                add_action( 'woocommerce_before_single_product', array( $this, 'setup_global_subscription_plan_discounts' ) );
                add_action( 'woocommerce_before_shop_loop_item', array( $this, 'setup_global_subscription_plan_discounts' ) );
                add_action( 'woocommerce_before_calculate_totals', array($this, 'setup_global_subscription_plan_discounts') );
                add_action( 'woocommerce_before_mini_cart_contents', array($this, 'setup_global_subscription_plan_discounts') );

                // Activate discounts for logged in, active members.
                do_action( 'pms_woo_discounts_enable_price_adjustments' );

                // Force calculations in cart.
                add_filter( 'woocommerce_update_cart_action_cart_updated', '__return_true' );

               // Replace "Sale" badge with "Member Discount!" for products which have valid membership discounts
                add_action( 'woocommerce_sale_flash' , array($this, 'get_member_discount_badge' ), 10, 3 );
                // Display "Member Discount" suffix for variation prices.
                add_filter( 'woocommerce_get_price_html', array( $this, 'get_member_price_html' ), 999, 2 );

            }
        }
    }


    /**
     * Save the subscription plan product membership discounts for current active member in a global variable: $pms_woo_member_discounts.
     * Also, save the current member active subscriptions in a global variable: $pms_woo_member_subscriptions.
     *
     */
    public function setup_global_subscription_plan_discounts() {

        // here we store the global subscription plan product discounts for the current (logged in) member
        global $pms_woo_member_discounts, $pms_woo_member_subscriptions;

        // make sure the global variables were not set already on a different hook
        if ( !isset($pms_woo_member_discounts) || !isset($pms_woo_member_subscriptions) ) {

            $member = pms_get_member( get_current_user_id() );

            if ( !empty($member) ) {

                $pms_woo_member_discounts = array();
                $pms_woo_member_subscriptions = array();

                foreach ( $member->subscriptions as $subscription ) {

                    if ( $subscription['status'] == 'active' || $subscription['status'] == 'canceled' ){

                        $pms_woo_member_subscriptions[] = $subscription['subscription_plan_id'];
                        $discounts = get_post_meta( (int)$subscription['subscription_plan_id'], 'pms-woo-subscription-plan-product-discounts', true );

                        if ( !empty($discounts) ) {

                            foreach ($discounts as $key => $discount) {
                                if ( $discount['status'] == 'inactive' ) unset($discounts[$key]);
                            }

                            $pms_woo_member_discounts = array_merge($pms_woo_member_discounts, $discounts);
                        }
                    }
                }

            }

        }

    }

    //@TODO: check this shit
    /**
     * Clear all transients for variation prices after updating PMS WooCommerce settings
     *
     * Fires after updating the PMS WooCommerce settings page, and if there are changes, clears all transients variation prices
     * @param mixed $old_value the old option value
     * @param mixed $value the new option value
     */
    public function after_pms_woocommerce_settings_updated( $old_value, $value ){

        // check if PMS WooCommerce settings have been updated (modified)
        if ( isset($value['woocommerce']) && isset($old_value['woocommerce']) ) {

            if (count($value['woocommerce']) != count($old_value['woocommerce'])) {

                // if so, clear all transients variation prices
                $this->clear_all_variation_prices_transients();

            }
        }
    }

    /**
     * Clear all transients for variation prices after saving a Subscription Plan details
     *
     * If there are any active membership discounts attached, clears all transients variation prices
     * @param int $post_id the subscription plan id
     */
    public function after_subscription_plan_discounts_updated( $post_id ){

        if ( empty($post_id) ){
            // get current post id
            global $post;

            $post_id = $post->ID;
        }

        $discounts = get_post_meta( $post_id, 'pms-woo-subscription-plan-product-discounts', true );

        if ( !empty($discounts) ) {
            // check if subscription plan has active membership discounts
            foreach ($discounts as $key => $discount) {
                if ($discount['status'] == 'inactive') unset($discounts[$key]);
            }
        }

        // If we have active membership discounts for this subscription plan, clear all transients for variation prices
        if ( !empty($discounts) ) {
            $this->clear_all_variation_prices_transients();
        }

    }


    /**
     * Clear all variation prices transients
     *
     * Single transient is used per product for all variation prices, so we must remove all of them
     */
    public function clear_all_variation_prices_transients(){

        global $wpdb;

        $table = $wpdb->options;

        $wpdb->query( $wpdb->prepare( "
          DELETE FROM `$table`
          WHERE option_name LIKE %s", '%\_wc\_var\_prices\_%'
        ) );

    }



    /**
     * * Enable price adjustments for product prices based on membership discounts for logged in members
     *
     */
    public function enable_price_adjustments() {

        // Apply membership discount to product price.
        add_filter( 'woocommerce_product_get_sale_price',              array( $this, 'get_member_price' ), 999, 2 );
        add_filter( 'woocommerce_product_variation_get_sale_price',    array( $this, 'get_member_price' ), 999, 2 );
        add_filter( 'woocommerce_product_get_price',                   array( $this, 'get_member_price' ), 999, 2 );
        add_filter( 'woocommerce_product_variation_get_price',         array( $this, 'get_member_price' ), 999, 2 );

        // Replace regular price with sale (using a filter)
        /** @see \PMS_WOO_Subscription_Discounts::member_prices_display_sale_price() */
        add_filter( 'woocommerce_product_get_regular_price',           array( $this, 'get_member_regular_price' ), 999, 2 );
        add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'get_member_regular_price' ), 999, 2 );


        // Apply membership discount to variation price.
        add_filter( 'woocommerce_variation_prices_sale_price',    array( $this, 'get_member_variation_price' ), 999, 3 );
        add_filter( 'woocommerce_variation_prices_price',         array( $this, 'get_member_variation_price' ), 999, 3 );
        add_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'get_member_variation_regular_price' ), 999, 3 );
        // Clear variation prices cache.
        add_filter( 'woocommerce_get_variation_prices_hash',      array( $this, 'set_user_variation_prices_hash' ), 999, 2 );

    }


    /**
     * Disable price adjustments.
     *
     * Calling this method will **disable** membership adjustments
     * for product prices that have member discounts for logged in members.
     *
     */
    public function disable_price_adjustments() {

        // Restore prices to original amount before membership discount.
        remove_filter( 'woocommerce_product_get_sale_price',              array( $this, 'get_member_price' ), 999 );
        remove_filter( 'woocommerce_product_get_price',                   array( $this, 'get_member_price' ), 999 );
        remove_filter( 'woocommerce_product_variation_get_price',         array( $this, 'get_member_price' ), 999 );
        remove_filter( 'woocommerce_product_variation_get_sale_price',    array( $this, 'get_member_price' ), 999 );
        remove_filter( 'woocommerce_product_get_regular_price',           array( $this, 'get_member_regular_price' ), 999 );
        remove_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'get_member_regular_price' ), 999 );

        remove_filter( 'woocommerce_variation_prices_sale_price',    array( $this, 'get_member_variation_price' ), 999 );
        remove_filter( 'woocommerce_variation_prices_price',         array( $this, 'get_member_variation_price' ), 999 );
        remove_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'get_member_variation_regular_price' ), 999 );
        remove_filter( 'woocommerce_get_variation_prices_hash',      array( $this, 'set_user_variation_prices_hash' ), 999 );
    }


    /**
     * Check if a product is to be excluded from membership discounts.
     *
     * @param int|\WP_Post|\WC_Product $product Product object or id.
     * @return bool
     */
    public function is_product_excluded_from_member_discounts( $product ) {

        if ( is_numeric( $product ) ) {
            $product = wc_get_product( $product );
        } elseif ( $product instanceof WP_Post ) {
            $product = wc_get_product( $product );
        }

        if ( ! $product instanceof WC_Product ) {
            return false;
        }

        // if is variation, use the id of the parent product
        if ($product->is_type( 'variation' )) {
            $product_id = $product->get_parent_id();
        } else {
            $product_id = $product->get_id();
        }

        // exclude if product-level setting is set to exclude this product from all membership discounts
        $exclude_product = 'exclude' === get_post_meta( (int)$product_id, 'pms-woo-product-membership-discounts-behaviour', true);
        // exclude if product is on sale and global PMS setting is enabled to exclude all products on sale from membership discounts
        $exclude_on_sale = $this->exclude_on_sale_products && $this->get_product_unfiltered_sale_status( $product );

        /**
         * Filter product from having discount rules applied.
         *
         * @param bool $exclude Whether the product is excluded from discount rules.
         * @param \WC_Product $product The product object.
         */
         $exclude = (bool) apply_filters( 'pms_woo_exclude_product_from_member_discounts', $exclude_product || $exclude_on_sale, $product );

        return $exclude;
    }


    /**
     * Adjust discounted product price HTML.
     *
     * @param string $html The price HTML maybe after discount.
     * @param \WC_Product|\WC_Product_Variable|\WC_Product_Variation $product The product object for which we may have discounts.
     * @return string The original price HTML if no discount or a new formatted string showing before/after discount.
     */
    public function get_member_price_html( $html, $product ) {
        global $post;

        /**
         * Controls whether or not member prices should use discount format when displayed.
         *
         * @param bool $use_discount_format Defaults to true.
         */
        $use_discount_format = (bool) apply_filters( 'pms_woo_member_prices_use_discount_format', true );

        // Bail out if any of the following conditions applies:
        // - custom code set to not to use discount format
        // - user is not logged in or an active member
        // - product is excluded from discount rules
        // - current user has no discounts for the product
        if (      $use_discount_format
            &&   $this->is_active_member
            && ! $this->is_product_excluded_from_member_discounts( $product )
            &&  $this->get_user_membership_discounts( $product ) ) {

            $html_after_discount  = $html;

            // Add "Member Discount!" suffix for single variation prices.
            if ( $product->is_type( 'variation' ) ) {

                $html_after_discount .= ' ' . $this->get_member_discount_badge( '', $post, $product );

            }

            /**
             * Filter the HTML price after member discounts have been applied.
             *
             * @since 1.7.2
             * @param string $html The price HTML output.
             * @param \WC_Product $product The product the discounted price is meant for.
             * @param string $html_before_discount Original HTML before discounts.
             * @param string $html_after_discount Original HTML after discounts.
             *
             */
            $html = (string) apply_filters( 'pms_woo_get_discounted_price_html', $html_after_discount, $product );
        }

        /**
         * Filter the HTML price after member discounts may have been applied.
         *
         * @since 1.7.1
         * @param string $html The price HTML.
         * @param \WC_Product $product The product the price is meant for.
         */
        return apply_filters( 'pms_woo_get_price_html', $html, $product );
    }



    /**
     * Get the unfiltered sale status.
     *
     * Used to determine if a product was marked on sale before membership price adjustments.
     *
     * @param \WC_Product $product The product object.
     * @return bool
     */
    private function get_product_unfiltered_sale_status( $product ) {

        // Temporarily disable membership price adjustments.
        do_action( 'pms_woo_discounts_disable_price_adjustments' );
        remove_filter( 'woocommerce_get_price_html', array( $this, 'get_member_price_html' ), 999, 2 );

        $on_sale = $product->is_on_sale();

        // Re-enable membership price adjustments.
        do_action( 'pms_woo_discounts_enable_price_adjustments' );
        add_filter( 'woocommerce_get_price_html', array( $this, 'get_member_price_html' ), 999, 2 );

        return $on_sale;
    }


    /**
     * Whether to show sale prices as regular when displaying discounts to members.
     *
     * @return bool
     */
    private function member_prices_display_sale_price() {

        /**
         * Controls whether or not member prices should display sale prices as well.
         *
         * @param bool $display_sale_price Defaults to false.
         */
        return (bool) apply_filters( 'pms_woo_member_prices_display_sale_price', false );
    }


    /**
     * Apply purchasing member discounts to product price.
     *
     * @param string|int|float $price Price to discount (normally a float, maybe a string number).
     * @param \WC_Product $product The product object.
     * @return float Price.
     */
    public function get_member_price( $price, $product ) {

        // Bail out if any of the following is true:
        // - user is not logged in or an active member
        // - product is excluded from member discounts
        // - user has no member discount over the product
        if (     $this->is_active_member
            && ! $this->is_product_excluded_from_member_discounts( $product )
            &&   $this->get_user_membership_discounts( $product ) ) {

            // Account also for variation sale price filter.
            if ( in_array( current_filter(), array( 'woocommerce_product_get_sale_price', 'woocommerce_product_variation_get_sale_price' ), false ) ) {
                $member_price = $product->get_price();
            } else {
                $member_price = $this->get_discounted_price( $price, $product, get_current_user_id() );
            }

            $price = is_numeric( $member_price ) ? $member_price : $price;
        }

        return $price;
    }


    /**
     * Apply purchasing discounts to variation price.
     *
     * @param string|int|float $price Price to discount (normally a float, maybe a string number).
     * @param \WC_Product_Variation $variation The variation object.
     * @param \WC_Product $product The product object.
     * @return float Price.
     */
    public function get_member_variation_price( $price, $variation, $product ) {

        // Bail out if any of the following is true:
        // - user is not logged in or not an active member
        // - product is excluded from member discounts
        // - user has no member discount over the product
        if (     $this->is_active_member
            && ! $this->is_product_excluded_from_member_discounts( $variation )
            &&   $this->get_user_membership_discounts( $variation ) ) {

            if ( 'woocommerce_variation_prices_sale_price' === current_filter() ) {
                $member_price =  apply_filters( 'woocommerce_variation_prices_price', $variation->get_price('edit'), $variation, $product );
            } else {
                $member_price = $this->get_discounted_price( $price, $variation, get_current_user_id() );
            }

            $price = is_numeric( $member_price ) ? $member_price : $price;
        }

        return $price;
    }


    /**
     * Replace regular prices with sale before discounts when calculating price html strings.
     * We basically show sale prices to members as reference instead of regular prices.
     *
     * @param string|int|float $regular_price Regular price used as reference.
     * @param \WC_Product $product The product object.
     * @return float Price.
     */
    public function get_member_regular_price( $regular_price, $product ) {

        // Bail out if any of the following is true:
        // - user is not logged in or not an active member
        // - product is excluded from member discounts
        // - user has no member discount over the product

        if (      $this->is_active_member
            && !$this->is_product_excluded_from_member_discounts( $product )
            &&  $this->get_user_membership_discounts( $product )
            && $this->member_prices_display_sale_price() ) {

            // Temporarily disable membership price adjustments, so we can get sale price without querying the db.
            do_action( 'pms_woo_discounts_disable_price_adjustments' );

            if ( $product->is_on_sale() ) {
                $regular_price = $product->get_sale_price();
            }

            // Re-enable membership price adjustments.
            do_action( 'pms_woo_discounts_enable_price_adjustments' );
        }

        return $regular_price;
    }


    /**
     * Replace regular prices with sale before discounts when calculating price html strings.
     * We basically show sale prices to members as reference instead of regular prices.
     *
     * @param string|int|float $regular_price Regular price used as reference.
     * @param \WC_Product_Variation $variation The variation object.
     * @param \WC_Product $product The product object.
     * @return float Price.
     */
    public function get_member_variation_regular_price( $regular_price, $variation, $product ) {

        // Bail out if any of the following is true:
        // - user is not logged in or not an active member
        // - product is excluded from member discounts
        // - user has no member discount over the product
        if (      $this->is_active_member
            && ! $this->is_product_excluded_from_member_discounts( $product )
            &&   $this->get_user_membership_discounts( $product )
            &&   $this->member_prices_display_sale_price() ) {

            // Temporarily disable membership price adjustments.
            do_action( 'pms_woo_discounts_disable_price_adjustments' );

            $price         = apply_filters( 'woocommerce_variation_prices_price', $variation->get_price( 'edit' ), $variation, $product );
            $sale_price    = apply_filters( 'woocommerce_variation_prices_sale_price', $variation->get_sale_price( 'edit' ), $variation, $product );
            $regular_price = $regular_price !== $sale_price && $price === $sale_price ? $sale_price : $regular_price;

            // Re-enable membership price adjustments.
            do_action( 'pms_woo_discounts_enable_price_adjustments' );
        }

        return $regular_price;
    }


    /**
     * Add the current user ID to the variation prices hash for caching.
     *
     * @param array $data The existing hash data.
     * @param \WC_Product $product The current product variation.
     * @return array $data The hash data with a user ID added if applicable.
     */
    public function set_user_variation_prices_hash( $data, $product ) {

        // Bail out if:
        // - user is not logged in or not an active member
        // - logged in user has no membership discount over the product
        // - product is being explicitly excluded from member discounts
        if (      $this->is_active_member
            && ! $this->is_product_excluded_from_member_discounts( $product )
            &&   $this->get_user_membership_discounts( $product ) ){

            $data[] = get_current_user_id();

            if ( $this->member_prices_display_sale_price() ) {
                $data[] = 'member_prices_display_sale_price';
            }
        }

        return $data;
    }


    /**
     * Get member discount badge.
     *
     * @param \WC_Product $product The product object to output a badge for (passed to filter).
     * @param bool $variation Whether to output a discount badge specific for a product variation (default false).
     * @return string
     */
    public function get_member_discount_badge( $badge, $post, $product ) {

        // Bail out if any of the following conditions applies:
        // - no member user is logged in
        // - product is excluded from membership discount
        // - current user has no discounts for the product
        if ( $this->is_active_member
            && !$this->is_product_excluded_from_member_discounts( $product )
            && $this->get_user_membership_discounts( $product ) ) {

            $label = __('Member discount!', 'paid-member-subscriptions');

            if ( $product->is_type( array( 'variation' ) ) ) {

                $badge = '<span class="pms-woo-variation-member-discount-badge">' . esc_html($label) . '</span>';

                /**
                 * Filter the variation member discount badge.
                 *
                 * @param string $badge The badge HTML.
                 * @param \WC_Product|\WC_Product_Variation $variation The product variation.
                 */
                $badge = apply_filters('pms_woo_variation_member_discount_badge', $badge, $product);
            }
            else {

                $badge = '<span class="onsale pms-woo-member-discount-badge">' . esc_html($label) . '</span>';

               /**
                * Filter the member discount badge.
                *
                * @param string $badge The badge HTML.
                * @param \WP_Post $post The product post object.
                */
                $badge = apply_filters('pms_woo_member_discount_badge', $badge, $product);
            }
        }

        return $badge;
    }


    /**
     * Get product discounted price for member.
     *
     * @param float $base_price Original price.
     * @param int|\WC_Product $product Product ID or product object.
     * @param int|null $member_id Optional, defaults to current user id.
     * @return float|null The discounted price or null if no discount applies.
     */
    public function get_discounted_price( $base_price, $product, $member_id = null ) {

        if ( empty( $member_id ) ) {
            $member_id = get_current_user_id();
        }

        if ( is_numeric( $product ) ) {
            $product = wc_get_product( (int) $product );
        }

        $price          = null;
        $product_id     = null;
        $member_discounts = array();

        // We need a product and a user to get a member discounted price.
        if ( $product instanceof WC_Product && $member_id > 0 ) {

            // if is variation, use the id of the parent product
            if ($product->is_type( 'variation' )) {
                $product_id = $product->get_parent_id();
            } else {
                $product_id = $product->get_id();
            }

            $member_discounts = $this->get_user_membership_discounts($product, $member_id);
        }


        if ( $product_id && !empty($member_discounts) ) {
            // We have membership discounts that need to be applied to the product price

            $price = (float)$base_price;
            $discounted_price = $price;
            $prices = array();

            $discounts_behaviour = get_post_meta((int)$product_id, 'pms-woo-product-membership-discounts-behaviour', true);
            //for products added in Woocommerce before activating PMS and running the save_post hook added in PMS
            if ( empty($discounts_behaviour) )
                $discounts_behaviour = 'default';

            $discount_location = array();
            $discount_type = array('fixed', 'percent');

            switch ($discounts_behaviour) {
                case 'default':  // best price
                    $discount_location = array('subscription_plan', 'product');
                    break;
                case 'ignore':  //apply only discounts set per product, ignore the rest
                    $discount_location = array('product');
            }


            // Apply discounts and store both lowest individual price (after applying just one discount -> $prices) and lowest cumulative price (after applying all discounts -> $discounted_price)
            foreach ($discount_location as $location) {

                foreach ($discount_type as $type) {

                    if (!empty($member_discounts[$location])) {

                        if (!empty($member_discounts[$location][$type])) {

                            foreach ($member_discounts[$location][$type] as $discount_amount) {

                                switch ($type) {
                                    case 'fixed' :
                                        $discounted_price = max($discounted_price - (float)$discount_amount, 0);
                                        $prices[] = max($price - (float)$discount_amount, 0);
                                        break;

                                    case 'percent' :
                                        $discounted_price = max($discounted_price * (100 - (float)$discount_amount) / 100, 0);
                                        $prices[] = max($price * (100 - (float)$discount_amount) / 100, 0);
                                        break;
                                }

                            }
                        }
                    }
                }
            }


            /**
             * Filter whether to allow stacking product discounts for members of multiple plans
             * with overlapping discount rules for the same products
             *
             * @param bool $allow_cumulative_discounts Default false (do not allow).
             * @param int $member_id The user id discounts are calculated for.
             * @param \WC_Product $product The product object being discounted.
             */
            $allow_cumulative_discounts = apply_filters('pms_woo_allow_cumulative_member_discounts', $this->allow_cumulative_discounts, $member_id, $product);

            // Pick the best price based on $allow_cumulative_discounts option value.
            if (true === $allow_cumulative_discounts) {
                $price = $discounted_price;

            } else if (!empty($prices)) {
                $price = min($prices);
            }

            // Sanity check.
            if ($price >= $base_price) {
                $price = null;
            }

        }// end if ( $product_id && !empty($member_discounts) )

        /**
         * Filter discounted membership price of a product.
         *
         * @param null|float $price The discounted price or null if no discount applies.
         * @param float $base_price The original price (not discounted by PMS).
         * @param int $product_id The id of the product (or variation) the price is for.
         * @param int $member_id The id of the logged in member (it's zero for non logged in users).
         * @param \WC_Product $product The product object for the price being discounted.
         */
        return apply_filters( 'pms_woo_get_discounted_price', $price, $base_price, $product_id, $member_id, $product );
    }


    /**
     * Check if the user has any membership discounts for the product
     *
     * @param int|\WC_Product $product Product ID or object.
     * @param null|int $user_id Optional, defaults to current user id.
     * @return array()| $member_discount containing an array of all user discounts for the product
     *         empty array if no membership discounts found for this product
     */
    public function get_user_membership_discounts( $the_product, $the_user = null ) {

        global $pms_woo_member_discounts, $pms_woo_member_subscriptions;

        // initialize the $member_discount array
        $member_discounts = array();

        // Get the product.
        if ( is_numeric( $the_product ) ) {
            $the_product = wc_get_product( (int) $the_product );
        } elseif ( null === $the_product ) {
            global $product;

            if ( $product instanceof WC_Product ) {
                $the_product = $product;
            }
        }

        // bail out if no product
        if ( ! $the_product instanceof WC_Product ) {
            return $member_discounts;
        }


        // get the user id
        if ( null === $the_user ) {
            $member_id = get_current_user_id();
        } elseif ( is_numeric( $the_user ) ) {
            $member_id = (int) $the_user;
        } elseif ( isset( $the_user->ID ) ) {
            $member_id = (int) $the_user->ID;
        } else {
            return $member_discounts;
        }

        // bail out if user is not logged in
        if ( 0 === $member_id ) {
            return $member_discounts;
        }

        // if is variation, use the id of the parent product
        if ($the_product->is_type( 'variation' )) {
            $product_id = $the_product->get_parent_id();
        } else {
            $product_id = $the_product->get_id();
        }

        // get discounts behaviour for this product
        $discounts_behaviour = get_post_meta((int)$product_id, 'pms-woo-product-membership-discounts-behaviour', true);
        //for products added in Woocommerce before activating PMS and running the save_post hook added in PMS
        if ( empty($discounts_behaviour) )
            $discounts_behaviour = 'default';

        // check if there are any global subscription discounts that apply to this product
        if ( !empty($pms_woo_member_discounts) && ($discounts_behaviour == 'default') ) {

            foreach ($pms_woo_member_discounts as $discount) {

                // don't save inactive discounts
                if ( $discount['status'] == 'inactive' )
                    continue;

                if ( ($discount['discount-for'] == 'products') &&
                    ( !isset($discount['name']) || ( isset($discount['name']) && in_array($product_id, $discount['name'])) ) ){

                    $member_discounts['subscription_plan'][$discount['type']][] = $discount['amount'];
                }

                if ( ($discount['discount-for'] == 'product-categories') &&
                    ( ( !isset($discount['name']) && has_term('', 'product_cat', (int)$product_id) ) || ( isset($discount['name']) && has_term( $discount['name'], 'product_cat', (int)$product_id ) ) ) ) {

                    $member_discounts['subscription_plan'][$discount['type']][] = $discount['amount'];
                }

            }
        }

        // check if there are any discounts set per product that apply to logged in member
        $product_discounts = get_post_meta( (int)$product_id, 'pms-woo-product-membership-discounts', true );
        //for products added in Woocommerce before activating PMS and running the save_post hook added in PMS
        if( $product_discounts === '' )
            $product_discounts = array();

        if ( !empty($product_discounts) ){

            foreach ( $product_discounts as $product_discount ){

                if ( $product_discount['status'] == 'inactive' )
                    continue;

                if ( !empty($pms_woo_member_subscriptions) && in_array( $product_discount['subscription-plan'], $pms_woo_member_subscriptions ) )
                    $member_discounts['product'][$product_discount['type']][] = $product_discount['amount'];
            }

        }

        return $member_discounts;
    }


    /**
     * Function that checks if there are any active membership discounts that apply to this product
     *
     * @param null|int|\WC_Product $product Product ID or object.
     * @return bool
     */
    public function product_has_member_discounts( $the_product = null ){

        // Get the product.
        if ( is_numeric( $the_product ) ) {
            $the_product = wc_get_product( (int) $the_product );
        } elseif ( null === $the_product ) {
            global $product;

            if ( $product instanceof WC_Product ) {
                $the_product = $product;
            }
        }

        // bail out if no product
        if ( ! $the_product instanceof WC_Product ) {
            return false;
        }

        // if is variation, use the id of the parent product
        if ($the_product->is_type( 'variation' )) {
            $product_id = $the_product->get_parent_id();
        } else {
            $product_id = $the_product->get_id();
        }


        // first check if there are any discounts set per product
        $product_discounts = get_post_meta( (int)$product_id, 'pms-woo-product-membership-discounts', true );
        //for products added in Woocommerce before activating PMS and running the save_post hook added in PMS
        if( $product_discounts === '' )
            $product_discounts = array();

        if ( !empty($product_discounts) ) {

            foreach ($product_discounts as $product_discount) {

                if ($product_discount['status'] == 'active' && !empty($product_discount['subscription-plan'])) {

                    // product has membership discounts
                    return true;
                }
            }
        }


        // see if product is not excluded from global membership discounts (per subscription plan)
        $discounts_behaviour = get_post_meta( (int)$product_id, 'pms-woo-product-membership-discounts-behaviour', true);
        //for products added in Woocommerce before activating PMS and running the save_post hook added in PMS
        if ( empty($discounts_behaviour) )
            $discounts_behaviour = 'default';

        if ( $discounts_behaviour == 'ignore')
            return false;


        // check if there are global membership discounts set per subscription plan that apply to this product
        $subscription_plans = pms_get_subscription_plans();

        if ( !empty($subscription_plans) ) {

            foreach ( $subscription_plans as $subscription_plan ) {

                $subscription_plan_discounts = get_post_meta( (int)$subscription_plan->id, 'pms-woo-subscription-plan-product-discounts', true );

                if ( !empty($subscription_plan_discounts) ){

                    foreach ( $subscription_plan_discounts as $discount ) {

                        // don't save inactive discounts
                        if ( $discount['status'] == 'active' ) {

                            if (($discount['discount-for'] == 'products') &&
                                (!isset($discount['name']) || (isset($discount['name']) && in_array($product_id, $discount['name'])))) {
                                // product has membership discounts
                                return true;
                            }

                            if (($discount['discount-for'] == 'product-categories') &&
                                ((!isset($discount['name']) && has_term('', 'product_cat', $product_id)) || (isset($discount['name']) && has_term($discount['name'], 'product_cat', $product_id)))) {
                                // product has membership discounts
                                return true;
                            }
                        }
                    }

                }
            }
        }

        return false;
    }


    /**
     * Function that displays the 'Product Discounted - Membership Required' message to non-members if a product has a membership discount.
     * Displays below add to cart buttons.
     *
     **/
    function product_discounted_membership_required_message(){

        global $product;

        $options = get_option( 'pms_woocommerce_settings' );

        if ( !empty($options) && !empty($options['product_discounted_message']) ) {

            // check if product has any membership discounts and user doesn't have any or is a non member
            if ( !$this->is_product_excluded_from_member_discounts($product) && $this->product_has_member_discounts($product) && !$this->get_user_membership_discounts($product) ) {

                /**
                 * Filter for Product Discounted - Membership Required message
                 */
                $message = apply_filters( 'pms_woo_product_discounted_membership_required_message', '<div class="woocommerce"> <div class="woocommerce-info pms-woo-product-discounted-membership-required">'. $options['product_discounted_message'] . '</div> </div>', $options );

                echo wp_kses_post($message);
            }

        }
    }



    /**
     * Refresh cart fragments upon member login.
     *
     * This is useful if a non-logged in member added items to cart,
     * which should have otherwise membership discounts applied.
     *
     *
     * @see \WC_Cart::reset()
     *
     * @param string $user_login User login name.
     * @param \WP_User $user User that just logged in.
     */
    public function refresh_cart_upon_member_login( $user_login, $user ) {

        // small "hack" to trigger a refresh in cart contents
        // that will set any membership discounts to products that apply
        if ( $user_login && pms_is_member( $user->ID ) ) {

            $this->reset_cart_session_data();
        }
    }


    /**
     * Reset cart session data.
     *
     * @see \WC_Cart::reset() private method
     *
     */
    private function reset_cart_session_data() {

        $wc = WC();

        // Some very zealous sanity checks here:
        if ( $wc && isset( $wc->cart->cart_session_data ) ) {

            $session_data = $wc->cart->cart_session_data;

            if ( ! empty( $session_data ) ) {

                foreach ( $session_data as $key => $default ) {

                    if ( isset( $wc->session->$key ) ) {
                        unset( $wc->session->$key );
                    }
                }
            }

            // WooCommerce core filter.
            do_action( 'woocommerce_cart_reset', $wc->cart, true );
        }
    }


} // end class PMS_WOO_Subscription_Discounts

$pms_woo_subscription_discounts = new PMS_WOO_Subscription_Discounts();
