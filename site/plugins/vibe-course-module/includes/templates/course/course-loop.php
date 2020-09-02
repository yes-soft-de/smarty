<?php
/**
 * The template for displaying course directory loop.
 *
 * Override this template by copying it to yourtheme/course/course-loop.php
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


if ( bp_course_has_items( bp_ajax_querystring( 'course' ).'&orderby=menu_order&per_page='.$loop_number ) ) : ?>
<?php // global $items_template; var_dump( $items_template ) ?>
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

		<li itemscope itemtype="http://data-vocabulary.org/Review-aggregate">
			<div class="item-avatar" itemprop="photo">
				<?php bp_course_avatar(); ?>

			</div>

			<div class="item">
				<div class="item-title"  itemprop="itemreviewed"><?php bp_course_title(); if(get_post_status() != 'publish'){echo '<i> ( '.get_post_status().' ) </i>';} ?></div>
				<div class="item-meta"><?php bp_course_meta(); ?></div>
				<div class="item-desc"><?php bp_course_desc(); ?></div>
				<div class="item-credits">
					<?php bp_course_credits(); ?>
				</div>
				<div class="item-instructor">
					<?php bp_course_instructor(); ?>
				</div>
				<div class="item-action"><?php bp_course_action() ?></div>
				<?php do_action( 'bp_directory_course_item' ); ?>

			</div>

			<div class="clear"></div>
		</li>

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
