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

				<?php do_action( 'bp_before_group_body' );

				/**
				 * Does this next bit look familiar? If not, go check out WordPress's
				 * /wp-includes/template-loader.php file.
				 *
				 * @todo A real template hierarchy? Gasp!
				 */
				
				// Group is visible
				if ( bp_group_is_visible() ) :

					// Looking at home location
					if ( bp_is_group_home() ) :
						// Use custom front if one exists
						$custom_front = locate_template( array( 'groups/single/front.php' ) );
						if     ( ! empty( $custom_front   ) ) : load_template( $custom_front, true );

						// Default to activity
						elseif ( bp_is_active( 'activity' ) ) : locate_template( array( 'groups/single/activity.php' ), true );

						// Otherwise show members
						elseif ( bp_is_active( 'members'  ) ) : locate_template( array( 'groups/single/members.php'  ), true );

						endif;

					// Not looking at home
					else :

						// Group Admin
						if     ( bp_is_group_admin_page() ) : locate_template( array( 'groups/single/admin.php'        ), true );

						// Group Activity
						elseif ( bp_is_group_activity()   ) : locate_template( array( 'groups/single/activity.php'     ), true );

						// Group Members
						elseif ( bp_is_group_members()    ) : locate_template( array( 'groups/single/members.php'      ), true );

						// Group Invitations
						elseif ( bp_is_group_invites()    ) : locate_template( array( 'groups/single/send-invites.php' ), true );

						// Old group forums
						elseif ( bp_is_group_forum()      ) : locate_template( array( 'groups/single/forum.php'        ), true );

						// Anything else (plugins mostly)
						else                                : locate_template( array( 'groups/single/plugins.php'      ), true );

						endif;
					endif;

				// Group is not visible 
				elseif ( ! bp_group_is_visible() ) :
					// Membership request
					if ( bp_is_group_membership_request() ) :
						locate_template( array( 'groups/single/request-membership.php' ), true );

					// The group is not visible, show the status message
					else :

						do_action( 'bp_before_group_status_message' ); ?>

						<div id="message" class="info">
							<p><?php bp_group_status_message(); ?></p>
						</div>

						<?php do_action( 'bp_after_group_status_message' );

					endif;
				endif;

				do_action( 'bp_after_group_body' ); ?>
				
			</div><!-- #item-body -->
<?php
endwhile;
endif;
				
vibe_include_template("groups/bottom$group_layout.php","groups/bottom.php");  			
get_footer( vibe_get_footer() );  
