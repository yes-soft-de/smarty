<?php
/**
 * Template Name: Start Course Page
 */

// COURSE STATUS : 
// 0 : NOT STARTED 
// 1: STARTED 
// 2 : SUBMITTED
// > 2 : EVALUATED

// VERSION 1.8.4 NEW COURSE STATUSES
// 1 : START COURSE
// 2 : CONTINUE COURSE
// 3 : FINISH COURSE : COURSE UNDER EVALUATION
// 4 : COURSE EVALUATED

if ( !defined( 'ABSPATH' ) ) exit;

$course_status_template = apply_filters('wplms_course_status_template',vibe_get_option('course_status_template'),$_POST);

if(empty($course_status_template)){$course_status_template = 'default';}

vibe_include_template("course/start/$course_status_template.php");  



