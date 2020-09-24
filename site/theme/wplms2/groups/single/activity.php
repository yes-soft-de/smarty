<?php

/**
 * BuddyPress - Users Home
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

if ( !defined( 'ABSPATH' ) ) exit;
do_action('wplms_before_single_group');

get_header( vibe_get_header() ); 

$group_layout = vibe_get_customizer('group_layout');
if ( bp_has_groups() ) : while ( bp_groups() ) : bp_the_group();

vibe_include_template("groups/top$group_layout.php");  
?>

			<?php do_action( 'template_notices' ); ?>
			<div id="item-body">

				<?php do_action( 'bp_before_group_body' ); ?>
				<?php do_action('wplms_before_single_group_item_list_tabs'); ?>
				<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
					<ul>
						<li class="feed"><a href="<?php bp_group_activity_feed_link(); ?>" title="<?php _e( 'RSS Feed', 'vibe' ); ?>"><?php _e( 'RSS', 'vibe' ); ?></a></li>

						<?php do_action( 'bp_group_activity_syndication_options' ); ?>

						<li id="activity-filter-select" class="last">
							<label for="activity-filter-by"><?php _e( 'Show:', 'vibe' ); ?></label> 
							<select id="activity-filter-by">
								<option value="-1"><?php _e( 'Everything', 'vibe' ); ?></option>
								<option value="activity_update"><?php _e( 'Updates', 'vibe' ); ?></option>

								<?php if ( bp_is_active( 'forums' ) ) : ?>
									<option value="new_forum_topic"><?php _e( 'Forum Topics', 'vibe' ); ?></option>
									<option value="new_forum_post"><?php _e( 'Forum Replies', 'vibe' ); ?></option>
								<?php endif; ?>

								<option value="joined_group"><?php _e( 'Group Memberships', 'vibe' ); ?></option>

								<?php do_action( 'bp_group_activity_filter_options' ); ?>
							</select>
						</li>
					</ul>
				</div><!-- .item-list-tabs -->
				<?php do_action('wplms_after_single_group_item_list_tabs'); ?>
				<?php 
				do_action('wplms_after_single_item_list_tabs');
				do_action( 'bp_before_group_activity_post_form' ); ?>

				<?php if ( is_user_logged_in() && bp_group_is_member() ) : ?>
					<?php locate_template( array( 'activity/post-form.php'), true ); ?>
				<?php endif; ?>

				<?php do_action( 'bp_after_group_activity_post_form' ); ?>
				<?php do_action( 'bp_before_group_activity_content' ); ?>

				<div class="activity single-group" role="main">
					<?php locate_template( array( 'activity/activity-loop.php' ), true ); ?>
				</div><!-- .activity.single-group -->

				<?php do_action( 'bp_after_group_activity_content' ); ?>
				<?php do_action( 'bp_after_group_body' ); ?>
		</div>		
<?php

endwhile;
endif;	
				
vibe_include_template("groups/bottom$group_layout.php","groups/bottom.php");  			
get_footer( vibe_get_footer() );  
