<?php
/*
Plugin Name: Vibe Custom Types
Plugin URI: http://www.vibethemes.com/
Description: This plugin creates Custom Post Types and Custom Meta boxes for WPLMS theme.
Version: 3.8
Author: Mr.Vibe
Author URI: http://www.vibethemes.com/
Text Domain: vibe-customtypes
Domain Path: /languages/
*/
if ( !defined( 'ABSPATH' ) ) exit;
/*  Copyright 2013 VibeThemes  (email: vibethemes@gmail.com) */

if( !defined('VIBE_PLUGIN_URL')){
    define('VIBE_PLUGIN_URL',plugins_url());
}

/*====== BEGIN INCLUDING FILES ======*/

include_once('custom-post-types.php');
include_once('includes/errorhandle.php');
include_once('includes/featured.php');
include_once('includes/statistics.php');
include_once('includes/musettings.php');
include_once('includes/course_settings.php');
include_once('includes/course_menu.php');
include_once('includes/profile_menu.php');
include_once('includes/member_types.php');
include_once('includes/group_types.php');
include_once('includes/loggedin_menu.php');
include_once('includes/permalinks.php');
include_once('includes/caching.php');
include_once('includes/tips.php');
include_once('metaboxes/meta_box.php');
include_once('metaboxes/library/vibe-editor.php');

include_once('custom_meta_boxes.php');
include_once('includes/api/class-api-wp.php');
/*====== INSTALLATION HOOKs ======*/        

include_once('metaboxes/library/vc-mapper/vc-mapper.php');

register_activation_hook(__FILE__,'register_lms');
register_activation_hook(__FILE__,'register_popups', 11);
register_activation_hook(__FILE__,'register_testimonials', 12);
register_activation_hook(__FILE__,'flush_rewrite_rules', 20);

if(!function_exists('animation_effects')){
    function animation_effects(){
        $animate=array(
                        ''=>'none',
                        'animate cssanim flash'=> 'Flash',
                        'animate zoom' => 'Zoom',
                        'animate scale' => 'Scale',
                        'animate slide' => 'Slide (Height)', 
                        'animate expand' => 'Expand (Width)',
                        'animate cssanim shake'=> 'Shake',
                        'animate cssanim bounce'=> 'Bounce',
                        'animate cssanim tada'=> 'Tada',
                        'animate cssanim swing'=> 'Swing',
                        'animate cssanim wobble'=> 'Flash',
                        'animate cssanim wiggle'=> 'Flash',
                        'animate cssanim pulse'=> 'Flash',
                        'animate cssanim flip'=> 'Flash',
                        'animate cssanim flipInX'=> 'Flip Left',
                        'animate cssanim flipInY'=> 'Flip Top',
                        'animate cssanim fadeIn'=> 'Fade',
                        'animate cssanim fadeInUp'=> 'Fade Up',
                        'animate cssanim fadeInDown'=> 'Fade Down',
                        'animate cssanim fadeInLeft'=> 'Fade Left',
                        'animate cssanim fadeInRight'=> 'Fade Right',
                        'animate cssanim fadeInUptBig'=> 'Fade Big Up',
                        'animate cssanim fadeInDownBig'=> 'Fade Big Down',
                        'animate cssanim fadeInLeftBig'=> 'Fade Big Left',
                        'animate cssanim fadeInRightBig'=> 'Fade Big Right',
                        'animate cssanim bounceInUp'=> 'Bounce Up',
                        'animate cssanim bounceInDown'=> 'Bounce Down',
                        'animate cssanim bounceInLeft'=> 'Bounce Left',
                        'animate cssanim bounceInRight'=> 'Bounce Right',
                        'animate cssanim rotateIn'=> 'Rotate',
                        'animate cssanim rotateInUpLeft'=> 'Rotate Up Left',
                        'animate cssanim rotateInUpRight'=> 'Rotate Up Right',
                        'animate cssanim rotateInDownLeft'=> 'Rotate Down Left',
                        'animate cssanim rotateInDownRight'=> 'Rotate Down Right',
                        'animate cssanim speedIn'=> 'Speed In',
                        'animate cssanim rollIn'=> 'Roll In',
                        'animate ltr'=> 'Left To Right',
                        'animate rtl' => 'Right to Left', 
                        'animate btt' => 'Bottom to Top',
                        'animate ttb'=>'Top to Bottom',
                        'animate smallspin'=> 'Small Spin',
                        'animate spin'=> 'Infinite Spin'
                        );
    return $animate;
    }
}

add_action( 'init', 'vibe_custom_types_update' );
function vibe_custom_types_update() {

    /* Load Plugin Updater */
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'autoupdate/class-plugin-update.php' );

    /* Updater Config */
    $config = array(
        'base'      => plugin_basename( __FILE__ ), //required
        'dashboard' => true,
        'repo_uri'  => 'http://www.vibethemes.com/',  //required
        'repo_slug' => 'vibe-customtypes',  //required
    );

    /* Load Updater Class */
    new Vibe_Custom_Types_Auto_Update( $config );
}

add_action('plugins_loaded','vibe_customtypes_translations');
function vibe_customtypes_translations(){
    $locale = apply_filters("plugin_locale", get_locale(), 'vibe-customtypes');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'vibe-customtypes', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'vibe-customtypes', $mofile_global );
    } else {
        load_textdomain( 'vibe-customtypes', $mofile_local );
    }  
}

