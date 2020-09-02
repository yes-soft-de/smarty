<?php
/**
 * The template for displaying Course activity
 *
 * Override this template by copying it to yourtheme/course/single/activity.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.3
 */
?>
<div class="activity_form">
	<?php

		/**
		 * Fires before the display of the group activity post form.
		 *
		 * @since BuddyPress (1.2.0)
		 */
		do_action( 'bp_course_before_activity_post_form' ); ?>

		<?php if ( is_user_logged_in() && bp_course_is_member() ) : ?>

			<?php bp_get_template_part( 'activity/post-form' ); ?>

		<?php endif; ?>

		<?php

		/**
		 * Fires after the display of the group activity post form.
		 *
		 * @since BuddyPress (1.2.0)
		 */
		do_action( 'bp_course_after_activity_post_form' ); ?>
</div>

<div class="item-list-tabs activity-type-tabs no-ajax" id="subnav" role="navigation">
	<ul>
		<?php

		/**
		 * Fires inside the syndication options list, after the RSS option.
		 *
		 * @since BuddyPress (1.2.0)
		 */
		do_action( 'bp_course_activity_syndication_options' ); ?>
		<?php
		$flag = apply_filters('wplms_course_activity_visibility',1);
		global $bp;

		if($flag){ ?>
		<li id="activity-course_<?php echo $bp->current_item; ?>" class="selected"><a href="#" title=""><?php _e('All Activity','vibe'); ?></a></li>
		<?php } ?>
		<?php
		if(is_user_logged_in() && bp_course_is_member()){ ?>
		<li id="activity-course_personal_<?php echo $bp->current_item; ?>"><a href="#" title=""><?php _e('My Activity','vibe'); ?></a></li>
		<?php } ?>
		<li id="activity-filter-select" class="last">
			<label for="activity-filter-by"><?php _e( 'Show:', 'vibe' ); ?></label>
			<select id="activity-filter-by">
				<option value="-1"><?php _e( '&mdash; Everything &mdash;', 'vibe' ); ?></option>

				<?php bp_activity_show_filters( 'course' ); ?>

				<?php
				do_action( 'bp_course_activity_filter_options' ); ?>
			</select>
		</li>
	</ul>
</div><!-- .item-list-tabs -->

<div class="activity">
<?php do_action( 'bp_before_course_activity_loop' ); ?>

<?php if ( bp_has_activities( bp_ajax_querystring( 'activity' ) ) ) : ?>

	<?php /* Show pagination if JS is not enabled, since the "Load More" link will do nothing */ ?>
	
	<?php if ( empty( $_POST['page'] ) ) : ?>

		<ul id="activity-stream" class="activity-list item-list">

	<?php endif; ?>

	<?php while ( bp_activities() ) : bp_the_activity(); ?>

		<?php locate_template( array( 'activity/entry.php' ), true, false ); ?>

	<?php endwhile; ?>

	<?php if ( bp_activity_has_more_items() ) : ?>

		<li class="load-more">
			<a href="#more"><?php _e( 'Load More', 'vibe' ); ?></a>
		</li>

	<?php endif; ?>

	<?php if ( empty( $_POST['page'] ) ) : ?>

		</ul>

	<?php endif; ?>

<?php else : ?>

	<div id="message" class="info">
		<p><?php _e( 'Sorry, there was no activity found. Please try a different filter.', 'vibe' ); ?></p>
	</div>

<?php endif; ?>

<?php do_action( 'bp_after_activity_loop' ); ?>

<form action="" name="activity-loop-form" id="activity-loop-form" method="post">

	<?php wp_nonce_field( 'activity_filter', '_wpnonce_activity_filter' ); ?>

</form>
</div>