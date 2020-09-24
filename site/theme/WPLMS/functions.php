<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once get_template_directory() . '/_inc/wp-bootstrap-navwalker.php';
require get_template_directory() . '/_inc/custom-post-type.php';

// Essentials
include_once 'includes/config.php';
include_once 'includes/init.php';

// Register & Functions
include_once 'includes/register.php';
include_once 'includes/actions.php';
include_once 'includes/filters.php';
include_once 'includes/func.php';
include_once 'includes/ratings.php'; 
// Customizer
include_once 'includes/customizer/customizer.php';
include_once 'includes/customizer/css.php';
include_once 'includes/vibe-menu.php';
include_once 'includes/notes-discussions.php';
include_once 'includes/wplms-woocommerce-checkout.php';

if ( function_exists('bp_get_signup_allowed')) {
    include_once 'includes/bp-custom.php';
}

include_once '_inc/ajax.php';
include_once 'includes/buddydrive.php';
//Widgets
include_once('includes/widgets/custom_widgets.php');
if ( function_exists('bp_get_signup_allowed')) {
 include_once('includes/widgets/custom_bp_widgets.php');
}
if (function_exists('pmpro_hasMembershipLevel')) {
    include_once('includes/pmpro-connect.php');
}
include_once('includes/widgets/advanced_woocommerce_widgets.php');
include_once('includes/widgets/twitter.php');
include_once('includes/widgets/flickr.php');

//Misc
include_once 'includes/extras.php';
include_once 'includes/tincan.php';
include_once 'setup/wplms-install.php';

include_once 'setup/installer/envato_setup.php';

// Options Panel
get_template_part('vibe','options');


/*
	====================================
		FRONT-END ENQUEUE FUNCTIONS
	====================================
*/

function sunset_load_frontend_scripts() {

    // Css Styles
    wp_enqueue_style( 'slick-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css' );
    wp_enqueue_style( 'slick-theme-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css' );
    wp_enqueue_style( 'custom-css', get_template_directory_uri() . '/assets/css/custom.css', array(), '1.0.0', 'all' );
    
      // add our custom file to wordpress
    wp_enqueue_script( 'slick-js',  'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array(), false, true );
    wp_enqueue_script( 'custom-script-js', get_template_directory_uri() . '/assets/js/custom-script.js', array('jquery'), '1.0.0', true );

}
add_action( 'wp_enqueue_scripts', 'sunset_load_frontend_scripts' );




/**
 ** Function To Register New Sidebar
*/
function smart_way_side_bar() {
    register_sidebar(array(
        'name'          => 'Newsletter Sidebar',      // Your Optional Name Sidebar
        'id'            => 'newsletter-sidebar',      // ID should be LOWERCASE  ! ! !
        'description'   => 'Newsletter Sidebar Appear In FrontPage Only', // any description from your mine
        'class'         => 'newsletter-sidebar',
        'before_widget' => '<div class="widget-content">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>'
    ));
}
// Add Our Action
add_action('widgets_init', 'smart_way_side_bar');


function smarty_get_product_price( $slug ) {
	$args = array(
		'post_type' => 'product',
		'posts_per_page' => -1
	);
	$products = new WP_Query( $args );
	if ( $products->have_posts() ):
		while ( $products->have_posts() ) : $products->the_post();

			$product = wc_get_product( get_the_ID() );
		    if ( $product->get_slug() == $slug ) {
		        return $product->get_price();
		    }
		endwhile;
	endif;
	wp_reset_postdata();
}

add_filter( 'woocommerce_add_to_cart_validation', 'bbloomer_only_one_in_cart', 99, 2 );
function bbloomer_only_one_in_cart( $passed, $added_product_id ) {
		wc_empty_cart();
		return $passed;
}

//	add_filter('woocommerce_get_price','reigel_woocommerce_get_price',20,2);
//	function reigel_woocommerce_get_price($price,$post){
//		if ($post->post->post_type === 'smarty-consulting')
//			$price = get_post_meta($post->id, "price", true);
//		return $price;
//	}


//	function lease_cpt_register() {
//
//		class CPT_lease_product extends WC_Product_Simple {
//			public function __construct( $product ) {
//				$this->product_type = 'smarty-consulting';
//				parent::__construct( $product );
//			}
//		}
//	}
//	add_action( 'init', 'lease_cpt_register' );
//
//
//	function add_cpt_lease_product( $types ){
//		$types[ 'smarty-consulting' ] = __( 'Smarty Consulting' );
//		return $types;
//	}
//	add_filter( 'product_type_selector', 'add_cpt_lease_product' );
//
//	function cpt_woo_tab( $tabs) {
//		$tabs['lease'] = array(
//			'label'         => __( 'Consultation', 'woocommerce' ),
//			'target'        => 'cpt_woo_opt',
//		);
//		return $tabs;
//	}
//	add_filter( 'woocommerce_product_data_tabs', 'cpt_woo_tab' );
//
//	function lease_cpt_woo_opt() {
//		global $post;
//		?><!--<div id='cpt_woo_opt' class='panel woocommerce_options_panel'>--><?php
//		?><!--<div class='options_group'>--><?php
//		woocommerce_wp_text_input( array(
//			'id'                                            => 'woo_cpt_input_txt',
//			'type'                                       => 'text',
//			'label'                                      => __( 'Insert your Value', 'woocommerce' ),
//		) );
//		woocommerce_wp_checkbox( array(
//			'id'                            => 'woo_cpt_opt',
//			'label'      => __( 'Allow Option', 'woocommerce' ),
//		) );
//		?><!--</div>-->
<!--		</div>--><?php
//	}
//	add_action( 'woocommerce_product_data_panels', 'lease_cpt_woo_opt' );
//
//
//	function save_lease_cpt_field( $post_id ) {
//		$lease_opportunity = isset( $_POST['lease_woo_cpt_opt'] ) ? 'yes' : 'no';
//		update_post_meta( $post_id, 'lease_woo_cpt_opt', $lease_opportunity );
//		if ( isset( $_POST['woo_cpt_input_txt'] ) ) :
//			update_post_meta( $post_id, 'woo_cpt_input_txt', sanitize_text_field( $_POST['woo_cpt_input_txt'] ) );
//		endif;
//	}
//	add_action( 'woocommerce_process_product_meta_simple_rental', 'save_lease_cpt_field'  );
//	add_action( 'woocommerce_process_product_meta_variable_rental', 'save_lease_cpt_field'  );
