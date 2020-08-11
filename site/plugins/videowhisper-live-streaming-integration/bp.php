<?php
//BuddyPress Integration

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class liveStreamingGroup extends BP_Group_Extension {

	var $visibility = 'public'; // 'public' will show your extension to non-group members, 'private' means you have to be a member of the group to view your extension.

	var $enable_create_step = true; // If your extension does not need a creation step, set this to false
	var $enable_nav_item = true; // If your extension does not need a navigation item, set this to false
	var $enable_edit_item = true; // If your extension does not need an edit screen, set this to false


	public function __construct()
	{
		$this->name = 'Live Streaming';
		$this->slug = 'live-streaming';

		$this->create_step_position = 21;
		$this->nav_item_position = 31;
	}


	public function liveStreamingGroup() { //constructor
		self::__construct();

	}


	function create_screen($group_id = NULL) {
		if ( !bp_is_group_creation_step( $this->slug ) )
			return false;
?>

		<p><?php _e('To stream live video on this group just go to Admin > Live Streaming.','ppv-live-webcams'); ?></p>

		<?php
		wp_nonce_field( 'groups_create_save_' . $this->slug );
	}


	function create_screen_save($group_id = NULL) {
		global $bp;

		check_admin_referer( 'groups_create_save_' . $this->slug );

		/* Save any details submitted here */
		groups_update_groupmeta( $bp->groups->new_group_id, 'my_meta_name', 'value' );
	}


	function edit_screen($group_id = NULL) {
		if ( !bp_is_group_admin_screen( $this->slug ) )
			return false;
?>
				<h2><?php echo attribute_escape( $this->name ) ?></h2>
	<?php
		global $bp;
		$root_url = get_bloginfo( "url" ) . "/";



		if (class_exists('VWliveStreaming'))
		{
			VWliveStreaming::cleanSessions(1);
			VWliveStreaming::enqueueUI();
		}

		global $wpdb;
		$table_name = $wpdb->prefix . "vw_sessions";
		$wpdb->flush();

		$channelName = $bp->groups->current_group->slug;
		$options = get_option('VWliveStreamingOptions');


		//broadcasting?
		$sql = "SELECT * FROM $table_name where session='" . $channelName. "' and status='1'";
		$session = $wpdb->get_row($sql);
		if ($session)
		{
?>
		<p>A live broadcast session is already in progress for this group. A new session can be started 30s after previous completes. Click <a href="<?php echo $root_url."groups/". $channelName . "/". $this->slug ."/"; ?> ">here</a> to watch current session.</p>
		<?php
		}
		else
		{

			//find post
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE `post_title` = '$channelName' AND `post_type`='".$options['custom_post']."' LIMIT 0,1" );

			$current_user = wp_get_current_user();
			
			$post = array(
				'post_content'   => $bp->groups->current_group->description,
				'post_name'      => $channelName,
				'post_title'     => $channelName,
				'post_author'    => $current_user->ID,
				'post_type'      => $options['custom_post'],
				'post_status'    => 'publish',
			);

			if (!$postID)
			{
				echo __( 'First broadcast: creating group channel.', 'ppv-live-webcams') . ' <b>'. $channelName . '</b>';

				//var_dump($bp->groups->current_group);
				$postID = wp_insert_post($post);

			} else
			{
				$post['ID'] = $postID;
				wp_update_post($post);
			}

			echo '<a class="ui button primary" href="' . add_query_arg(array('broadcast'=>''), get_permalink($postID)) . '">' . __('Broadcast', 'live-streaming') . ' ' . $channelName . '</a>';


			//echo do_shortcode('[videowhisper_broadcast channel="' .$channelName. '"]');
		}
	}


	function edit_screen_save($group_id = NULL) {
		global $bp;

		if ( !isset( $_POST['save'] ) )
			return false;

		check_admin_referer( 'groups_edit_save_' . $this->slug );

		/* Insert your edit screen save code here */

		/* To post an error/success message to the screen, use the following */
		if ( !$success )
			bp_core_add_message( __( 'There was an error saving, please try again', 'buddypress' ), 'error' );
		else
			bp_core_add_message( __( 'Settings saved successfully', 'buddypress' ) );

		bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
	}


	function display($group_id = NULL) {
		/* Use this function to display the actual content of your group extension when the nav item is selected */
		global $bp;
		$root_url = get_bloginfo( "url" ) . "/";

		echo do_shortcode('[videowhisper_watch channel="' .$bp->groups->current_group->slug. '"]');
	}


	function widget_display() { ?>
		<div class="info-group">
			<h4><?php echo attribute_escape( $this->name ) ?></h4>
			<p>
				Group Live Streaming allows broadcasting a live video on the group.
			</p>
		</div>
		test
		<?php
	}


}


bp_register_group_extension( 'liveStreamingGroup' );
