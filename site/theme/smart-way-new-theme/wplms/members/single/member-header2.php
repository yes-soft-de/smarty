<?php

/**
 * BuddyPress - Users Header
 *
 * @package BuddyPress
 * @subpackage bp-default
 */
if ( !defined( 'ABSPATH' ) ) exit;
?>

<?php do_action( 'bp_before_member_header' ); ?>
<div class="<?php echo vibe_get_container(); ?>">
	<div class="row">
		<div class="col-md-3">
			<div id="item-header-avatar">
				<a href="<?php bp_displayed_user_link(); ?>">
					<?php bp_displayed_user_avatar( 'type=full' ); ?>
				</a>
			</div><!-- #item-header-avatar -->
		</div>
		<div class="col-md-9">
			<div id="item-header-content">
				<h3>
					<a href="<?php bp_displayed_user_link(); ?>"><?php bp_displayed_user_fullname(); ?></a>
				</h3>
				<div class="location">
				<?php
					$user_id=bp_displayed_user_id();
					$field = vibe_get_option('student_field');

					if(!isset($field) || $field =='')
						$field = 'Location';

					
					if(bp_is_active('xprofile'))
					echo bp_get_profile_field_data( array('user_id'=>$user_id,'field'=>$field ));

				?>
				</div>
				<?php 
				$members_activity=vibe_get_option('members_activity');
				if(isset($members_activity) && $members_activity){
			   //Hiding Activity
				if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) : ?>
					<span class="user-nicename">@<?php bp_displayed_user_mentionname(); ?></span>
				<?php endif; 

				?>

				<span class="activity"><?php bp_last_activity( bp_displayed_user_id() ); ?></span>
			   

				<?php  
				

				do_action( 'bp_before_member_header_meta' ); 

				 // Hiding MEta Info

				?>

				<div id="item-meta">

					<?php if ( bp_is_active( 'activity' ) ) : ?>

						<div id="latest-update">

							<h6><?php bp_activity_latest_update( bp_displayed_user_id() ); ?></h6>

						</div>

					<?php endif;  
					?>

					<div id="item-buttons">
						<?php do_action( 'bp_member_header_actions' ); ?>
					</div><!-- #item-buttons -->

					<?php
					/***
					 * If you'd like to show specific profile fields here use:
					 * bp_member_profile_data( 'field=About Me' ); -- Pass the name of the field
					 */
					 do_action( 'bp_profile_header_meta' );

					 ?>
				</div><!-- #item-meta -->
				<?php
			    }
			?>
			<?php
	
			 do_action( 'bp_profile_after_member_header_meta' );

			 ?>
			</div>
		</div>
	</div>
</div><!-- #item-header-content -->

<?php do_action( 'bp_after_member_header' ); ?>

