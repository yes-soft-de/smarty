<?php
/**
 * Functions that handle product content restriction on front-end
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Function that displays "Purchase options" for restricted products in Woocommerce individual product page. This way you can select which members (subscription plans) are allowed to purchase a specific product.
 *
 * @param int $post_id
 */
function pms_woo_content_restrict_add_product_purchase_options( $post_id ){

    if ($post_id) {

        $post_type = get_post_type($post_id);

        if ( !empty($post_type) && ($post_type == 'product') ) {

            // this is a WooCommerce product, so we need to display the Content Restrictions -> product purchase options
            include_once 'views/view-content-restriction-product-purchase-options.php';

        }
    }
}
add_action('pms_view_meta_box_content_restrict_display_options', 'pms_woo_content_restrict_add_product_purchase_options');


/**
 * Function that displays the editor for customizing the "purchase restricted" message for individual WooCommerce products.
 *
 * @param int $post_id
 */
function pms_woo_content_restrict_add_purchasing_restricted_message( $post_id ){

    if ($post_id) {

        $post_type = get_post_type($post_id);

        if ( !empty($post_type) && ($post_type == 'product') ) {

            // this is a WooCommerce product, so we will display the editor for customizing the purchasing restricted message.
            echo '<p><strong>' . __( 'Messages for restricted product purchase', 'paid-member-subscriptions' ) . '</strong></p>';
            wp_editor( wp_kses_post( get_post_meta( $post_id, 'pms-content-restrict-message-purchasing_restricted', true ) ), 'messages_purchasing_restricted', array( 'textarea_name' => 'pms-content-restrict-message-purchasing_restricted', 'editor_height' => 200 ) );

        }
    }

}
add_action( 'pms_view_meta_box_content_restrict_restriction_messages_bottom', 'pms_woo_content_restrict_add_purchasing_restricted_message');


/**
 * Function that adds a default "purchasing restricted" message for WooCommerce products under PMS Settings -> Content Restriction messages
 *
 * @param array $options PMS settings options
 */
function pms_woo_settings_page_add_default_purchasing_restricted_message( $options ) {

    echo '<h4 class="pms-subsection-title">' . __( 'WooCommerce Restriction Messages', 'paid-member-subscriptions' ) . '</h4>';

    echo '<div class="pms-form-field-wrapper">

            <label class="pms-form-field-label">' . __( 'Messages for restricted product purchase', 'paid-member-subscriptions' ) . '</label>';
    wp_editor( pms_get_restriction_content_message( 'purchasing_restricted' ), 'messages_purchasing_restricted', array( 'textarea_name' => 'pms_content_restriction_settings[purchasing_restricted]', 'editor_height' => 250 ) );

    echo '</div>';

}
add_action('pms-settings-page_tab_content_restriction_restrict_messages_bottom', 'pms_woo_settings_page_add_default_purchasing_restricted_message');


/**
 * Function that sets the default "purchasing restricted" message for WooCommerce products used under PMS Settings Page -> Content Restriction messages
 *
 * @param string $message The message to return
 * @param string $type The type of message
 * @param array $settings The PMS settings array
 * @return string
 */
function pms_woo_set_default_purchasing_restricted_message($message, $type, $settings) {

    if ($type == 'purchasing_restricted') {

        $message = isset($settings['purchasing_restricted']) ? $settings['purchasing_restricted'] : __('You need the proper subscription plan to be able to purchase this product.', 'paid-member-subscriptions');
    }

    return wp_kses_post($message);
}
add_filter('pms_get_restriction_content_message_default', 'pms_woo_set_default_purchasing_restricted_message', 10, 3);


/**
 * Function that saves the "Purchasing Options" and "purchase restricted" messages set in the Content Restriction metabox for individual WooCommerce products
 *
 * @param int $post_id The post ID
 */
function pms_woo_save_custom_purchasing_restricted_message( $post_id ) {
    //verify nonce
    if ( empty($_POST['pmstkn']) || !wp_verify_nonce( $_POST['pmstkn'], 'pms_meta_box_single_content_restriction_nonce' ) )
        return;

    delete_post_meta( $post_id, 'pms-purchase-restrict-subscription-plan' );
    if( isset( $_POST['pms-purchase-restrict-subscription-plan'] ) ) {

        foreach ( $_POST['pms-purchase-restrict-subscription-plan'] as $subscription_id )
            add_post_meta( $post_id, 'pms-purchase-restrict-subscription-plan', (int) $subscription_id );

    }


    if( isset( $_POST['pms-purchase-restrict-user-status'] ) && $_POST['pms-purchase-restrict-user-status'] == 'loggedin' )
        update_post_meta( $post_id, 'pms-purchase-restrict-user-status', 'loggedin' );
    else
        delete_post_meta( $post_id, 'pms-purchase-restrict-user-status' );

    // save custom "product purchase restricted" message
    update_post_meta( $post_id, 'pms-content-restrict-message-purchasing_restricted', ( ! empty( $_POST['pms-content-restrict-message-purchasing_restricted'] ) ? wp_kses_post($_POST['pms-content-restrict-message-purchasing_restricted']) : '' ) );

}
add_action( 'pms_save_meta_box_product', 'pms_woo_save_custom_purchasing_restricted_message');


/**
 * Function that restricts product viewing by hijacking WooCommerce product password protection (hide_content restriction mode)
 *
 */
function pms_woo_maybe_password_protect_product(){
    global $post;

    // if the product is to be restricted, and doesn't already have a password,
    // set a password so as to perform the actions we want
    if ( pms_is_post_restricted() && ! post_password_required() ) {

        $post->post_password = uniqid( 'pms_woo_product_restricted_' );

        add_filter( 'the_password_form', 'pms_woo_restrict_product_content' );

    }
}
add_action( 'woocommerce_before_single_product', 'pms_woo_maybe_password_protect_product' );


/**
 * Function that restricts product content
 *
 * @param $output What is returned
 * @return string
 */
function pms_woo_restrict_product_content( $output ){
    global $post, $user_ID;

    if ( strpos( $post->post_password, 'pms_woo_product_restricted_' ) !== false ) {

        // user does not have access, filter the content
        $output = '';

        // check if restricted post preview is set
        $settings       = get_option( 'pms_content_restriction_settings' );
        $preview_option = ( !empty( $settings['restricted_post_preview']['option'] ) ? $settings['restricted_post_preview']['option'] : '' );

        if ( !empty($preview_option) && ($preview_option != 'none') ) {
            // display product title

            ob_start();

            echo '<div class="summary entry-summary">';
            wc_get_template( 'single-product/title.php' );
            echo '</div>';

            $output = ob_get_clean();
        }

        $output .= pms_get_restricted_post_message();

        $post->post_password = null;

    }

    return $output;
}

/**
 * Function that sets the "purchasing_restricted" message type inside "pms_get_restricted_post_message" function
 *
 * @param string $message The message type to return
 *
 */
function pms_woo_set_purchasing_restricted_message_type( $message, $post_id ) {

    if( get_post_type( $post_id ) != 'product' )
        return $message;

    if  ( !pms_is_post_restricted() && !pms_is_product_purchasable() ) {

        // this product can be viewed, but purchase is restricted
        $message = 'purchasing_restricted';
    }

    return $message;
}
add_filter( 'pms_get_restricted_post_message_type', 'pms_woo_set_purchasing_restricted_message_type', 10, 2 );


/**
 * Function that hides the price for view-restricted products
 *
 * @param float $price The product price
 * @param WC_Product $product The product
 * @return string
 */
function pms_woo_hide_restricted_product_price( $price, WC_Product $product ){
    // check if current user can view this product, and if not, remove the price
    if ( pms_is_post_restricted( $product->get_id() ) ) {

        $price = '';
    }

    return $price;
}
add_filter( 'woocommerce_get_price_html', 'pms_woo_hide_restricted_product_price', 9, 2);


/**
 * Function that hides the product image thumbnail for view-restricted products
 *
 */
function pms_woo_maybe_remove_product_thumbnail(){
    global $post, $pms_woo_product_thumbnail_restricted;;

    $pms_woo_product_thumbnail_restricted = false;

    // skip if the product thumbnail is not shown anyway
    if ( ! has_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail' ) ) {
        return;
    }

    // if product is view restricted, do not display the product thumbnail
    if ( pms_is_post_restricted($post->ID) ) {

        // indicate that we removed the product thumbnail
        $pms_woo_product_thumbnail_restricted = true;

        // remove the product thumbnail and replace it with the placeholder image
        remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail' );
        add_action( 'woocommerce_before_shop_loop_item_title', 'pms_woo_template_loop_product_thumbnail_placeholder' , 10 );
    }

}
add_action( 'woocommerce_before_shop_loop_item_title', 'pms_woo_maybe_remove_product_thumbnail', 5 );


// return placeholder thumbnail instead of image for view-restricted products
function pms_woo_template_loop_product_thumbnail_placeholder(){
    if ( wc_placeholder_img_src() ) {

        echo wc_placeholder_img( 'shop_catalog' );
    }
}

// restore product thumbnail for the next product in the loop
function pms_woo_restore_product_thumbnail(){
    global $pms_woo_product_thumbnail_restricted;

    if (  $pms_woo_product_thumbnail_restricted
        && ! has_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail' ) ) {

        add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
        remove_action( 'woocommerce_before_shop_loop_item_title', 'pms_woo_template_loop_product_thumbnail_placeholder' );
    }
}
add_action( 'woocommerce_after_shop_loop_item_title', 'pms_woo_restore_product_thumbnail', 5 );


/**
 * Function that restricts product purchasing
 *
 * @param boolean $purchasable Whether the product is purchasable or not
 * @param $product The product
 * @return bool
 */
function pms_woo_product_is_purchasable( $purchasable, $product ){

    // if the product is view-restricted or purchase-restricted it cannot be purchased
    if ( pms_is_post_restricted( $product->get_id() ) || !pms_is_product_purchasable( $product ) ) {

        $purchasable = false;
    }

    // double-check for variations; if parent is not purchasable, then neither should be the variation
    if ( $purchasable && $product->is_type( array( 'variation' ) ) ) {

        $parent = wc_get_product( $product->get_parent_id() );

        $purchasable = $parent->is_purchasable();
    }

    return $purchasable;

}
add_filter( 'woocommerce_is_purchasable', 'pms_woo_product_is_purchasable', 10, 2 );
add_filter( 'woocommerce_variation_is_purchasable', 'pms_woo_product_is_purchasable', 10, 2 );


/**
 * Function that checks if current user can purchase the product
 *
 * @param string|WC_Product $product The product
 * @return bool
 */
function pms_is_product_purchasable( $product = '' ){
    global $user_ID, $post;

    if ( empty($product) )
        $product = wc_get_product( $post->ID );

    if( false == $product )
        return false;

    /**
     * Show "buy now" for the `manage_options` and `pms_bypass_content_restriction` capabilities
     */
    if( current_user_can( 'manage_options' ) || current_user_can( apply_filters( 'pms_content_restriction_capability', 'pms_bypass_content_restriction' ) ) )
        return true;

    // if is variation, use the id of the parent product
    if ( $product->is_type( 'variation' ) )
        $product_id = $product->get_parent_id();
    else
        $product_id = $product->get_id();

    // Get subscription plans that can purchase this product
    $user_status                = get_post_meta( $product_id, 'pms-purchase-restrict-user-status', true );
    $product_subscription_plans = get_post_meta( $product_id, 'pms-purchase-restrict-subscription-plan' );

    if( empty( $user_status ) && empty( $product_subscription_plans ) ) {
        //everyone can purchase
        return true;

    } else if( !empty( $product_subscription_plans ) && is_user_logged_in() ) {

        if ( pms_is_member( $user_ID, $product_subscription_plans ) )
            return true;
        else
            return false;

    } else if ( !is_user_logged_in() && (
                ( !empty( $user_status ) && $user_status == 'loggedin' ) || ( !empty( $product_subscription_plans ) )
            ) ) {

        return false;
    }

    return true;
}
/**
 * Function that shows the product purchasing restricted message
 *
 **/
function pms_woo_single_product_purchasing_restricted_message(){
    global $pms_show_content;

    if ( !pms_is_product_purchasable() ) {

        // product purchasing is restricted
        $pms_show_content = false;
        $message = pms_get_restricted_post_message();

        echo wp_kses_post( $message );
    }
}
add_action( 'woocommerce_single_product_summary', 'pms_woo_single_product_purchasing_restricted_message', 30 );


/**
 * Add divs to restriction message which will be used for styling, separating the message from the content in case we have set a post/product preview
 *
 * @param $message The message to return
 * @return string
 *
 */
function pms_woo_add_divs_to_restriction_message( $message ) {

    global $post;

    if( 'product' === get_post_type( $post ) )
        $message = '<div class="woocommerce">' . '<div class="woocommerce-info pms-woo-restriction-message pms-woo-restricted-product-purchasing-message">' . $message . '</div>' . '</div>';

    return $message;

}
add_filter('pms_restriction_message_wpautop', 'pms_woo_add_divs_to_restriction_message');

// Apply wpautop() to "purchasing restricted" messages as well
add_filter( 'pms_restriction_message_purchasing_restricted', 'pms_restriction_message_wpautop', 30, 1 );


if( function_exists( 'wc_get_page_id' ) ) :
    /**
     * Function that redirects the user if the Shop page (set in WooCommerce Settings -> Products -> Display) is restricted and a redirect is set
     *
     */
    function pms_woo_restricted_shop_redirect() {

        // check if it's the WooCommerce shop page
        if ( !is_post_type_archive('product')  )
            return;

        // get the ID of the shop page
        $post_id = wc_get_page_id('shop');

        // make sure we have the page id
        if ( $post_id != -1 ) {

            $redirect_url = '';
            $post_restriction_type = get_post_meta($post_id, 'pms-content-restrict-type', true);
            $settings = get_option( 'pms_content_restriction_settings', array() );
            $general_restriction_type = (!empty($settings['content_restrict_type']) ? $settings['content_restrict_type'] : 'message');

            if ($post_restriction_type !== 'redirect' && $general_restriction_type !== 'redirect')
                return;

            if ( !in_array($post_restriction_type, array('default', 'redirect')) )
                return;

            if ( !pms_is_post_restricted( $post_id ) )
                return;

            /**
             * Get the redirect URL from the post meta if enabled
             *
             */
            if ($post_restriction_type === 'redirect') {

                $post_redirect_url_enabled = get_post_meta($post_id, 'pms-content-restrict-custom-redirect-url-enabled', true);
                $post_redirect_url = get_post_meta($post_id, 'pms-content-restrict-custom-redirect-url', true);

                $redirect_url = (!empty($post_redirect_url_enabled) && !empty($post_redirect_url) ? $post_redirect_url : '');

            }


            /**
             * If the post doesn't have a custom redirect URL set, get the default from the Settings page
             *
             */
            if (empty($redirect_url)) {

                $redirect_url = (!empty($settings['content_restrict_redirect_url']) ? $settings['content_restrict_redirect_url'] : '');

            }

            if (empty($redirect_url))
                return;

            /**
             * To avoid a redirect loop we break in case the redirect URL is the same as
             * the current page URl
             *
             */
            $current_url = pms_get_current_page_url();

            if ($current_url == $redirect_url)
                return;

            /**
             * Redirect
             *
             */
            wp_redirect( pms_add_missing_http( $redirect_url ) );
            exit;
        }

    }
    add_action( 'wp', 'pms_woo_restricted_shop_redirect' );


    /**
     * Restrict the Shop page
     *
     * @param $template The shop page template to return
     * @return string
     */
    function pms_woo_restrict_shop_page( $template ){

        // check if we're on the Shop page (set under WooCommerce Settings -> Products -> Display)
        if ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id( 'shop' )) ) {

            // get the ID of the shop page
            $post_id = wc_get_page_id('shop');

            if ( ($post_id != -1) && pms_is_post_restricted($post_id) ) {

                $shop_page = get_post( $post_id );

                setup_postdata( $shop_page );

                $template = PMS_PLUGIN_DIR_PATH . 'extend/woocommerce/templates/archive-product.php';

                wp_reset_postdata();
            }

        }

        return $template;
    }
    add_filter( 'template_include', 'pms_woo_restrict_shop_page', 30);
endif;
