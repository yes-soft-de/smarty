<?php
/*
Plugin Name: WPLMS Dashboard
Plugin URI: http://www.Vibethemes.com
Description: Student/Instructor Dashboard for WPLMS theme
Version: 3.8
Author: VibeThemes
Author URI: http://www.vibethemes.com
License: as Per Themeforest GuideLines
*/
/*
Copyright 2014  VibeThemes  (email : vibethemes@gmail.com)

WPLMS Dashboard is a plugin made for WPLMS Theme. This plugin is only meant to work with WPLMS and can only be used with WPLMS. WPLMS Dashboard program is not a free software; you can not redistribute it and/or modify 
Please consult VibeThemes.com or email us at vibethemes@gmail.com for more.
*/

if ( !defined( 'ABSPATH' ) ) exit;

if( !defined('WPLMS_DASHBOARD_URL')){
    define('WPLMS_DASHBOARD_URL',plugins_url().'/wplms-dashboard');
}

if( !defined('WPLMS_DASHBOARD_VERSION')){
    define('WPLMS_DASHBOARD_VERSION','3.8');
}

include_once 'includes/functions.php';
include_once 'includes/dashboard.php';

include_once 'includes/widgets/student/activity_widget.php';
include_once 'includes/widgets/student/course_progress.php';
include_once 'includes/widgets/student/contact_users.php';
include_once 'includes/widgets/student/text_widget.php';
include_once 'includes/widgets/student/todo_task.php';
include_once 'includes/widgets/student/student_stats.php';
include_once 'includes/widgets/student/dash_stats.php';
include_once 'includes/widgets/student/notes_discussions.php';
include_once 'includes/widgets/student/my_modules.php';
include_once 'includes/widgets/student/news.php';
include_once 'includes/widgets/instructor/break.php';
include_once 'includes/widgets/instructor/dash_instructor_stats.php';
include_once 'includes/widgets/instructor/instructor_stats.php';
include_once 'includes/widgets/instructor/instructor_commissions.php';
include_once 'includes/widgets/instructor/announcements.php';
include_once 'includes/widgets/instructor/instructing_modules.php';
include_once 'includes/widgets/instructor/instructor_students.php';

add_action( 'init', 'wplms_dashboard_update' );
function wplms_dashboard_update() {
	/* Load Plugin Updater */
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'autoupdate/class-plugin-update.php' );
	/* Updater Config */
	$config = array(
		'base'      => plugin_basename( __FILE__ ), //required
		'dashboard' => true,
		'repo_uri'  => 'http://www.vibethemes.com/',  //required
		'repo_slug' => 'wplms-dashboard',  //required
	);
	/* Load Updater Class */
	new WPLMS_Dashboard_Auto_Update( $config );
}

add_action( 'plugins_loaded', 'wplms_dashboard_language_setup' );
function wplms_dashboard_language_setup(){
    $locale = apply_filters("plugin_locale", get_locale(), 'wplms-dashboard');
    
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'wplms-dashboard', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'wplms-dashboard', $mofile_global );
    } else {
        load_textdomain( 'wplms-dashboard', $mofile_local );
    }   
}
?>