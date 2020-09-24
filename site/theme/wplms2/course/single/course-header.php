<?php
/**
 * The template for displaying Course Header
 *
 * Override this template by copying it to yourtheme/course/single/header.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.1
 */
if ( !defined( 'ABSPATH' ) ) exit;
do_action( 'bp_before_course_header' );

?>

	<div id="item-header-avatar">
			<?php bp_course_avatar(); ?>
	</div><!-- #item-header-avatar -->


<div id="item-header-content">
	<span class="highlight"><?php bp_course_type(); ?></span>
	<h3><?php bp_course_name(); ?></h3>
	
	<?php do_action( 'bp_before_course_header_meta' ); ?>

	<div id="item-meta">
		<?php bp_course_meta(); ?>
		<?php do_action( 'bp_course_header_actions' ); ?>

		<?php do_action( 'bp_course_header_meta' ); ?>

	</div>
</div><!-- #item-header-content -->
<?php
$enable_instructor = apply_filters('wplms_display_instructor',true,get_the_ID());
if($enable_instructor){
?>
<div id="item-admins">

<h3><?php _e( 'Instructors', 'vibe' ); ?></h3>
	<?php
	bp_course_instructor();

	do_action( 'bp_after_course_menu_instructors' );
	?>
</div><!-- #item-actions -->
<?php
}
do_action( 'bp_after_course_header' );
?>