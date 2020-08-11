<?php
/*
	===============================
		ADMIN ENQUEUE FUNCTIONS
	===============================
*/

// $hook : prebuild variable inside wordpress that detect the name of page that user is in
function sunset_load_admin_scripts( $hook ){
    // By Printing ($hook) we can have the name for every page
//    echo $hook;

    //register css admin section
    wp_register_style( 'raleway-admin', 'https://fonts.googleapis.com/css?family=Raleway:200,300,500' );
    wp_register_style( 'sunset_admin', get_template_directory_uri() . '/css/sunset.admin.css', array(), '1.0.0', 'all' );

    //register js admin section
    wp_register_script( 'sunset-admin-script', get_template_directory_uri() . '/js/sunset.admin.js', array('jquery'), '1.0.0', true );


    // Array For Pages We Want To Check In Admin Section
    $pages_array = array(
        'toplevel_page_talal_sunset',
        'sunset_page_talal_sunset_theme',
        'sunset_page_talal_sunset_theme_contact'
    );

    // Check to prevent load this function out of this array page
    if ( in_array( $hook, $pages_array ) ) {

        wp_enqueue_style( 'raleway-admin' );    // enqueue the admin font file
        wp_enqueue_style( 'sunset_admin' );     // enqueue the new style file

    }

		// Check to prevent load this function out of this 'toplevel_page_talal_sunset' page
    if ( 'toplevel_page_talal_sunset' == $hook ) {
        /** Enqueues all scripts, styles, settings, and templates necessary to use all media JS APIs.
         * will automatically handle the calling and the activation process of all the scripts and all the source code that we need to use the media uploader
         * and without it no matter what we do the media uploader won't work
         */
        wp_enqueue_media();
        // Register new script file for custom admin page
        wp_enqueue_script( 'sunset-admin-script' );

    }

    if ( 'sunset_page_talal_sunset_css' == $hook ){

        wp_enqueue_style( 'raleway-admin' );
        wp_enqueue_style( 'sunset_admin' );

        wp_enqueue_style( 'ace', get_template_directory_uri() . '/css/sunset.ace.css', array(), '1.0.0', 'all' );

        wp_enqueue_script( 'ace', get_template_directory_uri() . '/js/ace/ace.js', array('jquery'), '1.2.1', true );
        wp_enqueue_script( 'sunset-custom-css-script', get_template_directory_uri() . '/js/sunset.custom_css.js', array('jquery'), '1.0.0', true );

    }

}
// specific unique action that will trigger only if the user is inside the administrator panel
add_action( 'admin_enqueue_scripts', 'sunset_load_admin_scripts' );


/*
	====================================
		FRONT-END ENQUEUE FUNCTIONS
	====================================
*/

function sunset_load_frontend_scripts(){
    // Css Styles
    wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css' );
    wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/css/font-awesome.min.css' );
    wp_enqueue_style( 'slick-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css' );
    wp_enqueue_style( 'slick-theme-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css' );
    wp_enqueue_style( 'sunset', get_template_directory_uri() . '/css/sunset.css', array(), '1.0.0', 'all' );
    // Add Google Font
    wp_enqueue_style( 'raleway', 'https://fonts.googleapis.com/css?family=Raleway:200,300,500' );

    // Js Scripts
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery' , get_template_directory_uri() . '/js/jquery.js', false, '1.11.3', true );
    wp_enqueue_script( 'jquery' );
    // add bootstrap to wordpress
    //    wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), '3.3.6', true );
	wp_enqueue_script( 'popper-js', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js', array(), false, true );
		//		wp_enqueue_script( 'bootstrap-js', get_Template_directory_uri() . '/js/bootstrap.min.js', array(), false, true );
	wp_enqueue_script( 'bootstrap-js',  'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js', array(), false, true );
	wp_enqueue_script( 'slick-js',  'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array(), false, true );
	  // add our custom file to wordpress
    wp_enqueue_script( 'sunset', get_template_directory_uri() . '/js/sunset.js', array('jquery'), '1.0.0', true );

}
add_action( 'wp_enqueue_scripts', 'sunset_load_frontend_scripts' );





