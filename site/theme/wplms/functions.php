<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once get_template_directory() . '/_inc/wp-bootstrap-navwalker.php';

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
