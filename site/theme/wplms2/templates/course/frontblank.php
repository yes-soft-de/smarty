<?php

/**
 * The template for displaying Course font
 *
 * Override this template by copying it to yourtheme/course/single/front.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;
global $post;
$id= get_the_ID();

do_action('wplms_course_before_front_main');

do_action('wplms_before_course_description');


echo apply_filters('the_content',get_the_content());

do_action('wplms_after_course_description');