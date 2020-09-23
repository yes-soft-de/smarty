<?php
/**
 * The template for displaying instructor courses in course directory
 *
 * Override this template by copying it to yourtheme/course/instructor-courses.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.1
 */
if ( !defined( 'ABSPATH' ) ) exit;
$user_id=get_current_user_id();

if(function_exists('bp_course_get_current_user_id')){
	$user_id=bp_course_get_current_user_id();	
}

$append='&instructor='.$user_id;
?>

<?php do_action( 'bp_before_course_loop' ); ?>
<?php 


if ( bp_course_has_items( bp_ajax_querystring( 'course' ).$append )) : ?>
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
		<?php /*while ( bp_course_has_items() ) : bp_course_the_item(); ?>

			<?php 
			global $post;
			$cache_duration = vibe_get_option('cache_duration'); if(!isset($cache_duration)) $cache_duration=86400;
			if($cache_duration){
				$course_key= 'course_'.$post->ID;
				if(is_user_logged_in()){
					$user_id = get_current_user_id();
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
			

	<?php endwhile;  */?>
	<?php while ( bp_course_has_items() ) : bp_course_the_item(); ?>

		<li>
			<div class="row">
				<div class="col-md-3 col-sm-4">
					<div class="item-avatar instructor-course-avatar">
						<?php bp_course_avatar(); ?>
					</div>
				</div>
				<div class="col-md-9 col-sm-8">

					<div class="item">
						<div class="item-title"><?php bp_course_title(); if(get_post_status() != 'publish'){echo '<i> ( '.get_post_status().' ) </i>';} ?></div>
						<div class="item-meta"><?php bp_course_meta(); ?></div>
						<div class="item-action-buttons">
							<?php bp_course_instructor_controls(); ?>
						</div>
						<?php do_action( 'bp_directory_instructing_course_item' ); ?>

					</div>
				</div>
			</div>
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