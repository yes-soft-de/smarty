<?php
/**
 * The template for displaying Course Header
 *
 * Override this template by copying it to yourtheme/course/single/header.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.0
 */
if ( !defined( 'ABSPATH' ) ) exit;
do_action( 'bp_before_course_header' );

?>

<div id="item-header-content">
	<?php vibe_breadcrumbs(); ?>
	<h1><?php bp_course_name(); ?></h1>
	
	<?php do_action( 'bp_before_course_header_meta' ); ?>

	<div id="item-meta">
		<?php bp_course_meta(); ?>
		<?php do_action( 'bp_course_header_actions' ); ?>

		<?php do_action( 'bp_course_header_meta' ); ?>
	</div>
</div><!-- #item-header-content -->
<div id="item-header-avatar">
		<?php bp_course_avatar(); ?>
</div><!-- #item-header-avatar -->

<?php
do_action( 'bp_after_course_header' );
?>