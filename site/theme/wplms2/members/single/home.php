<?php

/**
 * BuddyPress - Users Home
 *
 * @package BuddyPress
 * @subpackage bp-default
 */
if ( !defined( 'ABSPATH' ) ) exit;

do_action('wplms_before_member_profile');

get_header( vibe_get_header() ); 

$profile_layout = vibe_get_customizer('profile_layout');

vibe_include_template("profile/top$profile_layout.php");  
?>
<div id="item-body">
	<?php do_action( 'template_notices' ); ?>
	
	<?php do_action( 'bp_before_member_body' );


	if ( bp_is_user_activity() || !bp_current_component() ) :
		locate_template( array( 'members/single/activity.php'  ), true );

	 elseif ( bp_is_user_blogs() ) :
		locate_template( array( 'members/single/blogs.php'     ), true );

	elseif ( bp_is_user_friends() ) :
		locate_template( array( 'members/single/friends.php'   ), true );

	elseif ( bp_is_user_groups() ) :
		locate_template( array( 'members/single/groups.php'    ), true );

	elseif ( bp_is_user_messages() ) :
		locate_template( array( 'members/single/messages.php'  ), true );

	elseif ( bp_is_user_notifications() ) :
		locate_template( array( 'members/single/notifications.php'  ), true );

	elseif ( bp_is_user_profile() ) :
		locate_template( array( 'members/single/profile.php'   ), true );

	elseif ( bp_is_user_forums() ) :
		locate_template( array( 'members/single/forums.php'    ), true );

	elseif ( bp_is_user_settings() ) :
		locate_template( array( 'members/single/settings.php'  ), true );

	elseif ( bp_is_user_course() ) :
		locate_template( array( 'members/single/course.php'    ), true );

	// If nothing sticks, load a generic template
	else :
		locate_template( array( 'members/single/plugins.php'   ), true );

	endif;

	do_action( 'bp_after_member_body' ); ?>

</div><!-- #item-body -->

<?php do_action( 'bp_after_member_home_content' ); ?>
<?php
vibe_include_template("profile/bottom.php");  

get_footer( vibe_get_footer() );  

do_action('wplms_after_member_profile');
