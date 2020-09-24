<?php

/**
 * BuddyPress Member Settings
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
<h3><?php _e( 'General Settings', 'vibe' ); ?></h3>

<?php do_action( 'bp_template_content' ); ?>

<form action="<?php echo bp_displayed_user_domain() . bp_get_settings_slug() . '/general'; ?>" method="post" class="standard-form" id="settings-form">

	<?php if ( !is_super_admin() ) : ?>

		<label for="pwd"><?php _e( 'Current Password <span>(required to update email or change current password)</span>', 'vibe' ); ?></label>
		<input type="password" name="pwd" id="pwd" size="16" value="" class="settings-input small" /> &nbsp;<a href="<?php echo wp_lostpassword_url(); ?>" title="<?php _e( 'Password Lost and Found', 'vibe' ); ?>"><?php _e( 'Lost your password?', 'vibe' ); ?></a>

	<?php endif; ?>

	<label for="email"><?php _e( 'Account Email', 'vibe' ); ?></label>
	<input type="text" name="email" id="email" value="<?php echo bp_get_displayed_user_email(); ?>" class="settings-input" />

	<label for="pass1"><?php _e( 'Change Password <span>(leave blank for no change)</span>', 'vibe' ); ?></label>
	<input type="password" name="pass1" id="pass1" size="16" value="" class="settings-input small" /> &nbsp;<?php _e( 'New Password', 'vibe' ); ?><br />
	<input type="password" name="pass2" id="pass2" size="16" value="" class="settings-input small" /> &nbsp;<?php _e( 'Repeat New Password', 'vibe' ); ?>

	<?php do_action( 'bp_core_general_settings_before_submit' ); ?>

	<div class="submit">
		<input type="submit" name="submit" value="<?php _e( 'Save Changes', 'vibe' ); ?>" id="submit" class="auto" />
	</div>

	<?php do_action( 'bp_core_general_settings_after_submit' ); ?>

	<?php wp_nonce_field( 'bp_settings_general' ); ?>

</form>

<?php do_action( 'bp_after_member_body' ); ?>

</div><!-- #item-body -->

<?php do_action( 'bp_after_member_settings_template' ); ?>

<?php

vibe_include_template("profile/bottom.php");  

get_footer( vibe_get_footer() );  
