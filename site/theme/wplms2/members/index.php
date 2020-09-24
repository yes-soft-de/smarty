<?php

/**
 * BuddyPress - Members Directory
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

if ( !defined( 'ABSPATH' ) ) exit;

do_action('wplms_before_members_directory');

get_header( vibe_get_header() ); 

$directory_layout = vibe_get_customizer('directory_layout');

vibe_include_template("directory/members/index$directory_layout.php");  

get_footer( vibe_get_footer() );  
