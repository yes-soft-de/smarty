<?php
/*
Plugin Name: Vibe Course Module
Plugin URI: http://www.VibeThemes.com
Description: This is the Course module for WPLMS WordPress Theme by VibeThemes
Version: 3.8
Requires at least: WP 3.8, BuddyPress 1.9 
Tested up to: 4.8
License: (Themeforest License : http://themeforest.net/licenses)
Author: Mr.Vibe 
Author URI: http://www.VibeThemes.com
Network: false
Text Domain: vibe
Domain Path: /languages/
*/

// Checks if Course Module is Installed
define( 'BP_COURSE_MOD_INSTALLED', 1 );

// Checks the Course Module Version and necessary changes are hooked to this component
define( 'BP_COURSE_MOD_VERSION', '3.8' );

// FILE PATHS of Course Module
define( 'BP_COURSE_MOD_PLUGIN_DIR', dirname( __FILE__ ) );

/* Database Version for Course Module */
define ( 'BP_COURSE_DB_VERSION', '3.8' );

define ( 'BP_COURSE_CPT', 'course' );

if ( ! defined( 'BP_COURSE_SLUG' ) ){
    define ( 'BP_COURSE_SLUG','course' );
}


/* Only load the component if BuddyPress is loaded and initialized. */
function bp_course_init() {
	// Because our loader file uses BP_Component, it requires BP 1.5 or greater.
	if ( version_compare( BP_VERSION, '1.8', '>' ) )
		require( dirname( __FILE__ ) . '/includes/bp-course-loader.php' );
}
add_action( 'bp_include', 'bp_course_init' );

function bp_course_version(){
    return '3.6';
}

/* Setup procedures to be run when the plugin */
function bp_course_activate() {

}
register_activation_hook( __FILE__, 'bp_course_activate' );

/* clean up On deacativation */
function bp_course_deactivate() {
	
}
register_deactivation_hook( __FILE__, 'bp_course_deactivate' );



add_action( 'init', 'vibe_course_module_update' );
function vibe_course_module_update() {

    /* Load Plugin Updater */
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'autoupdate/class-plugin-update.php' );

    /* Updater Config */
    $config = array(
        'base'      => plugin_basename( __FILE__ ), //required
        'dashboard' => true,
        'repo_uri'  => 'http://www.vibethemes.com/',  //required
        'repo_slug' => 'vibe-course-module',  //required
    );

    /* Load Updater Class */
    new Vibe_Course_Module_Auto_Update( $config );
}

add_action('plugins_loaded','vibe_course_module_translations');
function vibe_course_module_translations(){
    $locale = apply_filters("plugin_locale", get_locale(), 'vibe');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'vibe', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'vibe', $mofile_global );
    } else {
        load_textdomain( 'vibe', $mofile_local );
    }   
}

/* BP 3.0 fixes */


add_action('init',function(){
    $bp_version = bp_get_version();
    if(function_exists('bp_current_action') && version_compare($bp_version, "3.0.0") >= 0){
        require_once(plugin_dir_path(__FILE__).'../buddypress/bp-core/deprecated/3.0.php');
    }
});

add_action('template_redirect',function(){
    $bp_version = bp_get_version();
    
    if(function_exists('bp_current_action') && version_compare($bp_version, "3.0.0") >= 0){
        if(bp_current_component() == 'course'){
            if(defined('BP_COURSE_STATS_SLUG') && bp_current_action() == BP_COURSE_STATS_SLUG){
                do_action( 'bp_course_screen_course_stats' );
                bp_core_load_template( apply_filters( 'bp_course_template_course_stats', 'members/single/home' ) );
                exit(); 
            }

            if(defined('BP_COURSE_INSTRUCTOR_SLUG') && bp_current_action() == BP_COURSE_INSTRUCTOR_SLUG){
                do_action( 'bp_course_instructing_courses' );
                bp_core_load_template( apply_filters( 'bp_course_instructor_courses', 'members/single/home' ) );
                exit(); 
            }

            if(defined('BP_COURSE_RESULTS_SLUG') && bp_current_action() == BP_COURSE_RESULTS_SLUG){
                do_action( 'bp_course_screen_my_results' );
                bp_core_load_template( apply_filters( 'bp_course_template_my_results', 'members/single/home' ) );
                exit(); 
            }
        }
    }
},1);

add_action('wp_footer',function(){
    if(is_page()){
        $bp_version = bp_get_version();

        if(function_exists('bp_current_component') && version_compare($bp_version, "3.0.0") >= 0){
            if( bp_current_component() == 'activate'){
                ?>
                <script>
                    jQuery(document).ready(function(){
                        jQuery('#key').val('<?php echo bp_current_action(); ?>');
                    });
                </script>
                <?php
            }
        } 
    }
});