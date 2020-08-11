<?php
/**
 * HTML Output for the settings page, WooCommerce Integration tab
 */
?>

<div id="pms-settings-woocommerce" class="pms-tab <?php echo ( $active_tab == 'woocommerce' ? 'tab-active' : '' ); ?>">

    <?php do_action( 'pms-settings-page_tab_woocommerce_before_content', $options ); ?>

    <div id="woocommerce-products">

        <h3><?php _e( 'Products', 'paid-member-subscriptions' ); ?></h3>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="woocommerce-cumulative-discounts"><?php _e( 'Allow cumulative discounts', 'paid-member-subscriptions' ) ?></label>

            <p class="description"><input type="checkbox" id="woocommerce-cumulative-discounts" name="pms_woocommerce_settings[cumulative_discounts]" value="1" <?php echo ( isset( $options['cumulative_discounts'] ) ? checked($options['cumulative_discounts'], '1', false) : '' ); ?> /><?php _e( 'By checking this option we will cumulate all discounts that apply to a specific product. <strong> By default we\'re applying only the highest discount. </strong>', 'paid-member-subscriptions' ); ?></p>
        </div>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="woocommerce-exclude-on-sale"><?php _e( 'Exclude products on sale ', 'paid-member-subscriptions' ) ?></label>

            <p class="description"><input type="checkbox" id="woocommerce-exclude-on-sale" name="pms_woocommerce_settings[exclude_on_sale]" value="1" <?php echo ( isset( $options['exclude_on_sale'] ) ? checked($options['exclude_on_sale'], '1', false) : '' ); ?> /><?php _e( 'Do not apply any member discounts to products that are currently on sale.', 'paid-member-subscriptions' ); ?></p>
        </div>

        <?php do_action( 'pms-settings-page_woocommerce_products_after_content', $options ); ?>

    </div>

    <div id="woocommerce-products">

        <h3><?php _e( 'Product Messages', 'paid-member-subscriptions' ); ?></h3>

        <div class="pms-form-field-wrapper">
            <label class="pms-form-field-label" for="woocommerce-product-discounted-message"><?php _e( 'Product Discounted - Membership Required', 'paid-member-subscriptions' ) ?></label>
            <?php wp_editor( ( isset($options['product_discounted_message']) ? wp_kses_post($options['product_discounted_message']) : __( 'Want a discount? Become a member, sign up for a subscription plan.' ,'paid-member-subscriptions') ), 'woocommerce-product-discounted-message', array( 'textarea_name' => 'pms_woocommerce_settings[product_discounted_message]', 'editor_height' => 150 ) ); ?>
            <p class="description"> <?php _e('Message displayed to non-members if the product has a membership discount. Displays below add to cart buttons. Leave blank to disable.','paid-member-subscriptions') ?></p>
        </div>

        <?php do_action( 'pms-settings-page_woocommerce_product_messages_after_content', $options ); ?>

    </div>

</div>
