<?php
/**
 * Class used for creating product discounts per subscription plan
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PMS_Meta_Box' ) )
    return;

Class PMS_Meta_Box_Subscription_Plan_Product_Discounts extends PMS_Meta_Box {

    public function init(){

        add_action( 'pms_output_content_meta_box_' . $this->post_type . '_' . $this->id, array( $this, 'output' ) );

        add_action( 'pms_save_meta_box_' . $this->post_type, array( $this, 'save_data' ) );

    }

    /**
     * UI for adding membership discounts per subscription plan and displaying existing ones
     *
     * @param WP_Post $post The post object
     */
    public function output( $post ){

        // Get the saved product discounts
        $product_discounts = get_post_meta( $post->ID, 'pms-woo-subscription-plan-product-discounts', true );
        $product_discounts = !empty( $product_discounts ) ? $product_discounts : array();

        // Add a nonce field
        echo wp_nonce_field( 'pms_woo_subscription_plan_product_discounts', 'pmstkn_dc' );

        // Add some global js variables
        $products = get_posts( array( 'post_type' => array('product', /*'product_variation'*/), 'numberposts' => -1 ) );
        $product_categories = get_terms( array( 'taxonomy' => 'product_cat' ) );

        echo '<script type="text/javascript">';
        echo 'var pmsWooProducts = {';
        foreach( $products as $product )
            echo '\'' . (int)$product->ID . '\'' . ':' . '\'' . esc_js($product->post_title) . '\'' . ',';
        echo '}';
        echo '</script>';

        echo '<script type="text/javascript">';
        echo 'var pmsWooProductCategories = {';
        foreach ( $product_categories as $category)
            echo '\'' . (int)$category->term_id . '\'' . ':' . '\'' . esc_js($category->name) . '\'' . ',';
        echo '}';
        echo '</script>';

        // Product Discounts table
        echo '<table id="pms-woo-subscription-product-discounts">';

        // Table header
        echo '<thead>';
        echo '<tr>';
        echo '<td><h4><label>' . __( 'Discount for', 'paid-member-subscriptions' ) . '</label></h4></td>';
        echo '<td><h4><label>' . __( 'Name', 'paid-member-subscriptions' ) . '</label></h4></td>';
        echo '<td><h4><label>' . __( 'Type', 'paid-member-subscriptions' ) . '</label></h4></td>';
        echo '<td><h4><label>' . __( 'Amount', 'paid-member-subscriptions' ) . '</label></h4></td>';
        echo '<td><h4><label>' . __( 'Status', 'paid-member-subscriptions' ) . '</label></h4></td>';
        echo '<td></td>';
        echo '</tr>';
        echo '<thead>';

        // Table body
        echo '<tbody>';

        if ( empty($product_discounts) ) {
            echo '<tr class="pms-woo-no-discounts-message">';
            echo '<td colspan="6">' . __( 'There are no discounts yet. Click below to add one.', 'paid-member-subscriptions' )  . '</td>';
            echo '</tr>';
        }
        else {

            // There are saved discounts, so we need to display them
            foreach ($product_discounts as $key => $discount) {
                echo '<tr class="pms-woo-subscription-product-discount">';

                echo '<td>';
                echo '<select name="pms-woo-subscription-product-discounts[' . esc_attr($key) . '][discount-for]" class="widefat pms-select-discount-for">';
                echo '<option value="products" ' . selected(sanitize_text_field($discount['discount-for']), 'products', false) . '>' . __('Products', 'paid-member-subscriptions') . '</option>';
                echo '<option value="product-categories" ' . selected(sanitize_text_field($discount['discount-for']), 'product-categories', false) . '>' . __('Product Categories', 'paid-member-subscriptions') . '</option>';
                echo '</select>';
                echo '</td>';

                echo '<td>';
                echo '<select name="pms-woo-subscription-product-discounts[' . esc_attr($key) . '][name][]" multiple data-placeholder='. __("Select... or leave blank to apply to all", "paid-member-subscriptions") . ' class="widefat pms-chosen pms-select-name">';

                $values = ( $discount['discount-for'] == 'products' ? $products : $product_categories );

                if( !empty( $values ) ) {
                    foreach( $values as $value_object ) {

                        $value = ($discount['discount-for'] == 'products' ? $value_object->ID : $value_object->term_id);
                        $name = ($discount['discount-for'] == 'products' ? $value_object->post_title : $value_object->name);

                        $values_array = array();
                        if (!empty($discount['name']))
                            $values_array = $discount['name'];

                        echo '<option value="' . esc_attr($value) . '" ' . (in_array($value, $values_array) ? 'selected' : '') . '>' . esc_html($name) . '</option>';
                    }
                }
                echo '</select>';
                echo '</td>';

                echo '<td>';
                echo '<select name="pms-woo-subscription-product-discounts[' . esc_attr($key) . '][type]" class="widefat pms-select-discount-type">';
                echo '<option value="percent" ' . selected(sanitize_text_field($discount['type']), 'percent', false) . '>' . __('Percent', 'paid-member-subscriptions') .' (%)' .'</option>';
                echo '<option value="fixed" ' . selected(sanitize_text_field($discount['type']), 'fixed', false) . '>' . __('Fixed', 'paid-member-subscriptions') . ' (' . get_woocommerce_currency_symbol() . ')'. '</option>';
                echo '</select>';
                echo '</td>';

                echo '<td>';
                $discount_value = !empty($discount['amount']) ? $discount['amount']: '';
                echo '<input type="text" name="pms-woo-subscription-product-discounts[' . esc_attr($key) . '][amount]" value="'. esc_attr($discount_value) .'" class="widefat pms-input-discount-amount">';
                echo '</td>';

                echo '<td>';
                echo '<select name="pms-woo-subscription-product-discounts[' . esc_attr($key) . '][status]" class="widefat pms-select-discount-status">';
                echo '<option value="active" ' . selected(sanitize_text_field($discount['status']), 'active', false) . '>' . __('Active', 'paid-member-subscriptions') . '</option>';
                echo '<option value="inactive" ' . selected(sanitize_text_field($discount['status']), 'inactive', false) . '>' . __('Inactive', 'paid-member-subscriptions') . '</option>';
                echo '</select>';
                echo '</td>';

                echo '<td><a href="#" class="pms-woo-subscription-remove-product-discount" title="'. __('Remove this discount', 'paid-member-subscriptions') .'"><span class="dashicons dashicons-no"></span></a></td>';
                echo '</tr>';
            }
        }

        echo '</tbody>';

        echo '</table>';

        // Add New Discount button
        echo '<a href="#" id="pms-woo-subscription-add-product-discount" class="button button-primary">' . __( 'Add New Discount', 'paid-member-subscriptions' ) . '</a>';

    }

    /**
     * Save membership discounts added per subscription plan
     *
     * @param int $post_id The post ID
     */
    public function save_data( $post_id ){


        // check nonce
        if ( (!isset($_POST['pmstkn_dc']) ) || ( !wp_verify_nonce($_POST['pmstkn_dc'], 'pms_woo_subscription_plan_product_discounts') ) )
            return;

        $product_discounts = !empty($_POST['pms-woo-subscription-product-discounts']) ? $_POST['pms-woo-subscription-product-discounts'] : array();

        // Filter empty discounts, that have no subscription plan selected or no amount set
        foreach ($product_discounts as $key => $discount) {
            if ( empty($discount['amount']) )
                unset($product_discounts[$key]);
        }

        $product_discounts = array_values($product_discounts);

        // save the data in the db
        if (is_array($product_discounts))
            update_post_meta($post_id, 'pms-woo-subscription-plan-product-discounts', $product_discounts);

    }


}

$pms_meta_box_subscription_plan_product_discounts = new PMS_Meta_Box_Subscription_Plan_Product_Discounts( 'pms_woo_subscription_plan_product_discounts', __( 'Product Discounts', 'paid-member-subscriptions' ), 'pms-subscription', 'normal' );
$pms_meta_box_subscription_plan_product_discounts->init();