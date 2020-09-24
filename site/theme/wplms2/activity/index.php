<?php

/**
 * Template Name: BuddyPress - Activity Directory
 *
 * @package BuddyPress
 * @subpackage Theme
 */
if ( !defined( 'ABSPATH' ) ) exit;

do_action('wplms_before_activity_directory');

get_header( vibe_get_header() ); 

$directory_layout = vibe_get_customizer('directory_layout');

vibe_include_template("directory/activity/index$directory_layout.php");  

get_footer( vibe_get_footer() );  

