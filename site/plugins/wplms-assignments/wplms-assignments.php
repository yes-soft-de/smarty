<?php
/*
Plugin Name: WPLMS Assignments
Plugin URI: http://www.Vibethemes.com
Description: COURSE Assignments plugin for WPLMS 
Version: 3.8
Author: VibeThemes
Author URI: http://www.vibethemes.com
License: as Per Themeforest GuideLines
Text Domain: wplms-assignments
Domain Path: /languages/
*/
/*
Copyright 2014  VibeThemes  (email : vibethemes@gmail.com)

WPLMS Assignments is a plugin made for WPLMS Theme. This plugin is only meant to work with WPLMS and can only be used with WPLMS.
WPLMS Assignment program is not a free software; you can not redistribute it and/or modify 
Please consult VibeThemes.com or email us at vibethemes@gmail.com for more.
*/

if ( !defined( 'ABSPATH' ) ) exit;
include_once 'includes/assignments_functions.php';
include_once 'includes/assignments.php';
include_once 'includes/assignment_statistics.php';

define ( 'WPLMS_ASSIGNMENTS_CPT', 'wplms-assignment' );
define ( 'WPLMS_ASSIGNMENTS_SLUG', 'assignment' );

function initialize_assignments(){
	register_activation_hook(__FILE__, 'wplms_assignments_activate');
    register_deactivation_hook(__FILE__,'wplms_assignments_deactivate');
    WPLMS_Assignments::init();    
}
if(class_exists('WPLMS_Assignments')){	
    add_action('plugins_loaded','initialize_assignments',100);
}

add_action( 'init', 'wplms_assignments_update' );
function wplms_assignments_update() {

	/* Load Plugin Updater */
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'autoupdate/class-plugin-update.php' );

	/* Updater Config */
	$config = array(
		'base'      => plugin_basename( __FILE__ ), //required
		'dashboard' => true,
		'repo_uri'  => 'http://www.vibethemes.com/',  //required
		'repo_slug' => 'wplms-assignments',  //required
	);

	/* Load Updater Class */
	new WPLMS_Assignments_Auto_Update( $config );
}

add_action('wp_enqueue_scripts','wplms_assignments_enqueue_scripts');

function wplms_assignments_enqueue_scripts(){
	if((function_exists('bp_current_action') && is_singular('course') && bp_current_action()=='submissions') || (isset($_GET['action']) && $_GET['action'] == 'admin' && isset($_GET['submissions']))){
		wp_enqueue_script('plupload');	
	}
	if(is_singular('wplms-assignment') || (isset($_GET['action']) && $_GET['action'] == 'admin' && isset($_GET['submissions'])) || (function_exists('bp_current_action') && bp_current_action() == 'submissions')){

        wp_enqueue_style( 'wplms-assignments-css', plugins_url( 'css/wplms-assignments.css' , __FILE__ ));
        wp_enqueue_script( 'wplms-assignments-js', plugins_url( 'js/wplms-assignments.js' , __FILE__ ));
        $translation_array = array( 
			'assignment_reset' => __( 'This step is irreversible. All Assignment submissions would be reset for this user. Are you sure you want to Reset the Assignment for this User? ','wplms-assignments' ), 
			'assignment_reset_button' => __( 'Confirm, Assignment reset for this User','wplms-assignments' ), 
			'marks_saved' => __( 'Marks Saved','wplms-assignments' ), 
			'assignment_marks_saved' => __( 'Assignment Marks Saved','wplms-assignments' ), 
			'cancel' => __( 'Cancel','wplms-assignments' ),
			'incorrect_file_format'=> __('Incorrect file format ','wplms-assignments'),
			'duplicate_file'=> __('File already selected ','wplms-assignments'),
			'remove_attachment'=>_x('Are you sure you want to remove this attachment ?','Notification when user removes the attachment from assignment','wplms-assignments'),
			);
    	wp_localize_script( 'wplms-assignments-js', 'wplms_assignment_messages', $translation_array );
    }
}

function wplms_assignments_activate(){
	flush_rewrite_rules(false );
}
function wplms_assignments_deactivate(){
	flush_rewrite_rules(false );	
}

add_action( 'plugins_loaded', 'wplms_assignments_language_setup' );
function wplms_assignments_language_setup(){
    $locale = apply_filters("plugin_locale", get_locale(), 'wplms-assignments');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'wplms-assignments', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'wplms-assignments', $mofile_global );
    } else {
        load_textdomain( 'wplms-assignments', $mofile_local );
    }  
}
