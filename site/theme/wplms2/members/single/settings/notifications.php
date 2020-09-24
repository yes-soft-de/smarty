<?php

/**
 * BuddyPress Notification Settings
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

get_header( vibe_get_header() ); 

$profile_layout = vibe_get_customizer('profile_layout');

vibe_include_template("profile/top$profile_layout.php");  
?>
<div id="item-body">

	<?php do_action( 'bp_before_member_body' ); ?>

	<div class="item-list-tabs no-ajax" id="subnav">
		<ul>

			<?php bp_get_options_nav(); ?>

			<?php do_action( 'bp_member_plugin_options_nav' ); ?>

		</ul>
	</div><!-- .item-list-tabs -->
	<?php do_action('wplms_after_single_item_list_tabs'); ?>
	<?php do_action('bp_before_member_settings_template'); ?>
	<h3><?php _e( 'Email Notification', 'vibe' ); ?></h3>

	<?php do_action( 'bp_template_content' ); ?>

	<form action="<?php echo bp_displayed_user_domain() . bp_get_settings_slug() . '/notifications'; ?>" method="post" class="standard-form" id="settings-form">
		<p><?php _e( 'Send an email notice when:', 'vibe' ); ?></p>

		<?php do_action( 'bp_notification_settings' ); ?>

		<?php do_action( 'bp_members_notification_settings_before_submit' ); ?>

		<div class="submit">
			<input type="submit" name="submit" value="<?php _e( 'Save Changes', 'vibe' ); ?>" id="submit" class="auto" />
		</div>

		<?php do_action( 'bp_members_notification_settings_after_submit' ); ?>

		<?php wp_nonce_field('bp_settings_notifications'); ?>

	</form>

	<?php do_action( 'bp_after_member_body' ); ?>

</div><!-- #item-body -->

<?php do_action( 'bp_after_member_settings_template' ); ?>
		
<?php

vibe_include_template("profile/bottom.php");  

get_footer( vibe_get_footer() );  