<?php
/**
 * The template for displaying Course events
 *
 * Override this template by copying it to yourtheme/course/single/events.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.1
 */
if ( !defined( 'ABSPATH' ) ) exit;

global $post;
if(class_exists('WPLMS_Events_Interface')){
?>

<div class="course_title">
	<h1><?php the_title(); echo ' ';_e('Events','vibe')  ?></h1>
</div>
<?php
	    $events_interface = new WPLMS_Events_Interface;
		$events_interface->wplms_event_calendar(get_the_ID());
		
 }
?>