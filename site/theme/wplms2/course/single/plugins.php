<?php 
/**
 * The template for displaying CUSTOM PLUGINS
 *
 * Override this template by copying it to yourtheme/course/single/home.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.1
 */ 
if ( !defined( 'ABSPATH' ) ) exit;
do_action('wplms_before_single_course_plugin');

get_header( vibe_get_header() ); 

$course_layout = vibe_get_customizer('course_layout');
if ( bp_course_has_items() ) : while ( bp_course_has_items() ) : bp_course_the_item();
vibe_include_template("course/top$course_layout.php");  

?>
<?php do_action( 'template_notices' ); ?>
<div id="item-body">
	<?php do_action( 'bp_course_before_plugin_body' ); ?>

	<div class="item-list-tabs no-ajax" id="subnav">
		<ul>

			<?php bp_get_options_nav(); ?>

			<?php do_action( 'bp_course_plugin_options_nav' ); ?>

		</ul>
	</div><!-- .item-list-tabs -->
	
	<?php do_action( 'bp_course_plugin_template_content' ); ?>

	<?php do_action( 'bp_course_after_plugin_body' ); ?>

</div><!-- #item-body -->

<?php do_action( 'bp_after_course_plugin_content' ); 

vibe_include_template("course/bottom$course_layout.php");  

endwhile; endif; 
?>
<?php get_footer( vibe_get_footer() );  

			