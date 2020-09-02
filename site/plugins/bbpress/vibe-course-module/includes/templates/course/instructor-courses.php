<?php
/**
 * The template for displaying instructor courses in course directory
 *
 * Override this template by copying it to yourtheme/course/instructor-courses.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     1.8
 */

$loop_number=vibe_get_option('loop_number');
isset($loop_number)?$loop_number:$loop_number=5;
?>

<?php do_action( 'bp_before_course_loop' ); ?>



<?php 
$user_id=get_current_user_id();

if ( bp_course_has_items( bp_ajax_querystring( 'course' ).'&instructor='.$user_id.'&per_page='.$loop_number ) ) : ?>
<?php // global $items_template; var_dump( $items_template ) ?>
	<div id="pag-top" class="pagination no-ajax">

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

		<li>
			<div class="item-avatar instructor-course-avatar">
				<?php bp_course_avatar(); ?>
			</div>
			<div class="item">
				<div class="item-title"><?php bp_course_title() ?></div>
				<div class="item-meta"><?php bp_course_meta() ?></div>
				<div class="item-action-buttons">
					<?php bp_course_instructor_controls() ?>
				</div>
				<?php do_action( 'bp_directory_instructing_course_item' ); ?>

			</div>

			<div class="clear"></div>
		</li>

	<?php endwhile; ?>

	</ul>

	<?php do_action( 'bp_after_directory_course_list' ); ?>

	<div id="pag-bottom" class="pagination no-ajax">

		<div class="pag-count" id="course-dir-count-bottom">

			<?php bp_course_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="course-dir-pag-bottom">

			<?php bp_course_item_pagination(); ?>

		</div>

	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'You have not created any Course.', 'vibe' ); ?></p>
	</div>

<?php endif;  ?>


<?php do_action( 'bp_after_course_loop' ); ?>