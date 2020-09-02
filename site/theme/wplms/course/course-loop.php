<?php
/**
 * The template for displaying course directory loop.
 *
 * Override this template by copying it to yourtheme/course/course-loop.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.1
 */
if ( !defined( 'ABSPATH' ) ) exit;
?>
<?php do_action( 'bp_before_course_loop' ); ?>
<?php 
if ( bp_course_has_items( bp_ajax_querystring( 'course' ) ) ) : ?>
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
			global $post;
			$cache_duration = vibe_get_option('cache_duration'); if(!isset($cache_duration)) $cache_duration=86400;
			if($cache_duration){
				$course_key= 'course_'.$post->ID;
				if(is_user_logged_in()){
					$user_id = bp_displayed_user_id();

					$user_meta = get_user_meta($user_id,$post->ID,true);
					if(isset($user_meta)){
						$course_key= 'course_'.$user_id.'_'.get_the_ID();
					}
				}
				$result = wp_cache_get($course_key,'course_loop');
			}else{$result=false;}

			if ( false === $result) {
				ob_start();
				if(function_exists('bp_course_item_view')){
					bp_course_item_view();
				}
				$result = ob_get_clean();
			}
			if($cache_duration)
			wp_cache_set( $course_key,$result,'course_loop',$cache_duration);

			echo $result;
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
