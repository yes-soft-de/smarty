<?php
/**
 * Class used for creating membership discounts for individual WooCommerce products
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'PMS_Meta_Box' ) )
    return;

Class PMS_Meta_Box_Product_Membership_Discounts extends PMS_Meta_Box {

    public function init(){

        add_action( 'pms_output_content_meta_box_' . $this->post_type . '_' . $this->id, array( $this, 'output' ) );

        add_action( 'pms_save_meta_box_' . $this->post_type, array( $this, 'save_data' ) );

    }

    /**
     * UI for adding new membership discounts per product and displaying existing ones
     *
     * @param WP_Post $post The post object
     */
    public function output( $post ){

            // Get the saved membership discounts behaviour
            $membership_discounts_behaviour = get_post_meta( $post->ID, 'pms-woo-product-membership-discounts-behaviour', true );
            $membership_discounts_behaviour = !empty( $membership_discounts_behaviour ) ? sanitize_text_field($membership_discounts_behaviour) : 'default';

            // Get the saved membership discounts
            $membership_discounts = get_post_meta( $post->ID, 'pms-woo-product-membership-discounts', true );
            $membership_discounts = !empty( $membership_discounts ) ? $membership_discounts : array();

            // Get active subscription plans
            $subscription_plans = pms_get_subscription_plans();

            // Set discount type
            $delay_units = array( 'fixed' => __( 'Fixed amount', 'paid-member-subscriptions' ), 'percent' => __( 'Percent', 'paid-member-subscriptions' ) );

            // Set discount status
            $status = array( 'active' => __( 'Active', 'paid-member-subscriptions' ), 'inactive' => __( 'Inactive', 'paid-member-subscriptions' ) );

            // Add a nonce field
            echo wp_nonce_field( 'pms_woo_product_membership_discounts', 'pmstkn_dc' );

            // Add some global js variables
            echo '<script type="text/javascript">';
            echo 'var pmsSubscriptionPlans = {';
            foreach( $subscription_plans as $subscription )
                echo '\'' . (int)$subscription->id . '\'' . ':' . '\'' . esc_js($subscription->name) . '\'' . ',';
            echo '}';
            echo '</script>';


            // Product Discounts behaviour
            echo '<div class="pms-meta-box-field-wrapper">';
            echo '<label for="pms_woo_product_membership_discounts_behaviour" class="pms-meta-box-field-label">' . __("Discounts behaviour", "paid-member-subscriptions") . '</label>';
            echo '<select id="pms_woo_product_membership_discounts_behaviour" name="pms-woo-product-membership-discounts-behaviour">';
            echo '<option value="default" ' . selected($membership_discounts_behaviour, 'default', false) . '>' . __("Best price" , "paid-member-subscriptions"). '</option>';
            echo '<option value="ignore" '  . selected($membership_discounts_behaviour, 'ignore', false) . '>'  . __("Apply only discounts set below for this product") . '</option>';
            echo '<option value="exclude" ' . selected($membership_discounts_behaviour, 'exclude', false) . '>' . __("Exclude this product from all membership discounts", "paid-member-subscriptions") . '</option>';
            echo '</select>';

            echo '<p class="description default_discount"'. ( ($membership_discounts_behaviour == 'default') ? '' : 'style="display:none"' ) .'>' . __('This will calculate the best price for this product, based on all existing member discounts (set both per subscription plan and per product) ', 'paid-member-subscriptions'). '</p>';
            echo '<p class="description ignore_discount"' . ( ($membership_discounts_behaviour == 'ignore')  ? '' : 'style="display:none"' ) .'>' . __('This will ignore the global discounts set per subscription plan that apply to this product', 'paid-member-subscriptions') . '</p>';
            echo '<p class="description exclude_discount"'. ( ($membership_discounts_behaviour == 'exclude') ? '' : 'style="display:none"' ) .'>' . __('This will exclude this product from any membership discounts that may apply now or in the future', 'paid-member-subscriptions') . '</p>';
            echo '</div>';


            // Discount codes table
            echo '<table id="pms-woo-product-membership-discounts">';

            // Table header
            echo '<thead>';
            echo '<tr>';
            echo '<td><h4><label>' . __( 'Subscription plan', 'paid-member-subscriptions' ) . '</label></h4></td>';
            echo '<td><h4><label>' . __( 'Type', 'paid-member-subscriptions' ) . '</label></h4></td>';
            echo '<td><h4><label>' . __( 'Amount', 'paid-member-subscriptions' ) . '</label></h4></td>';
            echo '<td><h4><label>' . __( 'Status', 'paid-member-subscriptions' ) . '</label></h4></td>';
            echo '<td></td>';
            echo '</tr>';
            echo '<thead>';

            // Table body
            echo '<tbody>';

            if ( empty($membership_discounts) ) {
                echo '<tr class="pms-woo-no-discounts-message">';
                echo '<td colspan="6">' . __( 'There are no discounts yet. Click below to add one.' )  . '</td>';
                echo '</tr>';
            }
            else {
                // There are saved discounts, so we need to display them
                foreach ($membership_discounts as $key => $discount) {
                    echo '<tr class="pms-woo-product-membership-discount">';

                    echo '<td>';
                    echo '<select name="pms-woo-product-membership-discounts[' . $key . '][subscription-plan]" class="widefat pms-select-subscription-plan">';
                    echo '<option value="0">' . __('Choose...', 'paid-member-subscriptions') . '</option>';
                    foreach ($subscription_plans as $subscription)
                        echo '<option value="' . esc_attr($subscription->id) . '" ' . selected(sanitize_text_field($discount['subscription-plan']), (int)$subscription->id, false) . '>' . sanitize_text_field($subscription->name) . '</option>';
                    echo '</select>';
                    echo '</td>';

                    echo '<td>';
                    echo '<select name="pms-woo-product-membership-discounts[' . $key . '][type]" class="widefat pms-select-discount-type">';
                    echo '<option value="percent" ' . selected(sanitize_text_field($discount['type']), 'percent', false) . '>' . __('Percent', 'paid-member-subscriptions') .' (%)' .'</option>';
                    echo '<option value="fixed" ' . selected(sanitize_text_field($discount['type']), 'fixed', false) . '>' . __('Fixed', 'paid-member-subscriptions') . ' (' . get_woocommerce_currency_symbol() . ')'. '</option>';
                    echo '</select>';
                    echo '</td>';

                    echo '<td>';
                    $discount_value = !empty($discount['amount']) ? $discount['amount']: '';
                    echo '<input type="text" name="pms-woo-product-membership-discounts[' . $key . '][amount]" value="'. esc_attr($discount_value) .'" class="widefat pms-input-discount-amount">';
                    echo '</td>';

                    echo '<td>';
                    echo '<select name="pms-woo-product-membership-discounts[' . $key . '][status]" class="widefat pms-select-discount-status">';
                    echo '<option value="active" ' . selected(sanitize_text_field($discount['status']), 'active', false) . '>' . __('Active', 'paid-member-subscriptions') . '</option>';
                    echo '<option value="inactive" ' . selected(sanitize_text_field($discount['status']), 'inactive', false) . '>' . __('Inactive', 'paid-member-subscriptions') . '</option>';
                    echo '</select>';
                    echo '</td>';

                    echo '<td><a href="#" class="pms-woo-product-remove-membership-discount" title="'. __('Remove this discount', 'paid-member-subscriptions') . '"><span class="dashicons dashicons-no"></span></a></td>';
                    echo '</tr>';
                }
            }

            echo '</tbody>';

            echo '</table>';

            // Add New Discount button
            echo '<a href="#" id="pms-woo-product-add-membership-discount" class="button button-primary">' . __( 'Add New Discount', 'paid-member-subscriptions' ) . '</a>';

    }

    /**
     * Save membership discounts added per product
     *
     * @param int $post_id The post ID
     */

    public function save_data( $post_id ){

        // check nonce
        if ( (!isset($_POST['pmstkn_dc']) ) || ( !wp_verify_nonce($_POST['pmstkn_dc'], 'pms_woo_product_membership_discounts') ) )
            return;

        $product_membership_discounts_behaviour = !empty($_POST['pms-woo-product-membership-discounts-behaviour']) ? sanitize_text_field($_POST['pms-woo-product-membership-discounts-behaviour']) : 'default';
        $product_membership_discounts = !empty($_POST['pms-woo-product-membership-discounts']) ? $_POST['pms-woo-product-membership-discounts'] : array();

        // Filter empty discounts, that have no subscription plan selected or no amount set
        foreach ($product_membership_discounts as $key => $discount) {
            if (empty($discount['subscription-plan']) || empty($discount['amount']))
               unset($product_membership_discounts[$key]);
        }

        $product_membership_discounts = array_values($product_membership_discounts);

        // save the data in the db
        update_post_meta($post_id, 'pms-woo-product-membership-discounts-behaviour', $product_membership_discounts_behaviour);

        if ( is_array($product_membership_discounts) )
            update_post_meta($post_id, 'pms-woo-product-membership-discounts', $product_membership_discounts);


    }

}


$pms_meta_box_product_membership_discounts = new PMS_Meta_Box_Product_Membership_Discounts( 'pms_woo_product_membership_discounts', __( 'Membership Discounts', 'paid-member-subscriptions' ), 'product', 'normal' );
$pms_meta_box_product_membership_discounts->init();



