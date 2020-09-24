<?php
/**
 * The template for displaying student courses in student profile and course directory
 *
 * Override this template by copying it to yourtheme/course/my-courses.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.1
 */
if ( !defined( 'ABSPATH' ) ) exit;
?>

<?php do_action( 'bp_before_course_loop' ); ?>
<?php 
$user_id=get_current_user_id();
if(function_exists('bp_course_get_current_user_id')){
	$user_id=bp_course_get_current_user_id();	
}

$append='&user='.$user_id;


if ( bp_course_has_items( bp_ajax_querystring( 'course' ).$append) ) : ?>
	
	<div id="pag-top" class="pagination">

		<div class="pag-count" id="course-dir-count-top">

			<?php bp_course_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="course-dir-pag-top">

			<?php bp_course_item_pagination(); ?>

		</div>

	</div>

	<?php do_action( 'bp_before_directory_course_list' ); ?>

	<ul id="course-list" class="item-list" role="main">

	<?php while ( bp_course_has_items() ) : bp_course_the_item(); ?>

		<?php
		if(function_exists('bp_course_item_view')){
					bp_course_item_view();
				}

		?>

	<?php endwhile; ?>

	</ul>

	<?php do_action( 'bp_after_directory_course_list' ); ?>

	<div id="pag-bottom" class="pagination">

		<div class="pag-count" id="course-dir-count-bottom">

			<?php bp_course_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="course-dir-pag-bottom">

			<?php bp_course_item_pagination(); ?>

		</div>

	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'You have not subscribed to any courses.', 'vibe' ); ?></p>
	</div>

<?php endif;  ?>


<?php do_action( 'bp_after_course_loop' ); ?>
