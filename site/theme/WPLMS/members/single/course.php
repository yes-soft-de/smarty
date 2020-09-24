<?php

/**
 * BuddyPress - Users Groups
 *
 * @package BuddyPress
 * @subpackage bp-default
 */
if ( !defined( 'ABSPATH' ) ) exit;
?>

<div class="item-list-tabs no-ajax <?php if ( !bp_is_my_profile() ) echo 'notmyprofile'; ?>" id="subnav" role="navigation">
	<ul>
		<?php if ( bp_is_my_profile() ) bp_get_options_nav(); 
		do_action('bp_course_get_options_sub_nav');
		?>
	</ul>
</div><!-- .item-list-tabs -->

<?php
	do_action('wplms_after_single_item_list_tabs');
	do_action( 'bp_before_member_course_content' ); 

	if ( bp_is_current_action( BP_COURSE_RESULTS_SLUG ) ) :
	
	locate_template( array( 'members/single/course/results.php' ), true );
	
	else:
		if ( bp_is_current_action( BP_COURSE_STATS_SLUG ) ) :
	
		locate_template( array( 'members/single/course/stats.php' ), true );
		
		else:
			?>
		<div class="course mycourse">
		<?php
			if( bp_is_current_action('instructor-courses')):
				locate_template( array( 'course/instructor-courses.php' ), true );
			else:
			?>
				<?php locate_template( array( 'course/my-courses.php' ), true ); 
			endif;
		?>
		</div>
		<?php
		endif;
	endif;
	?>
	<?php do_action( 'bp_after_member_course_content' ); ?>


