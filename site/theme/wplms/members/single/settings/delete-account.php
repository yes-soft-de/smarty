<?php

/**
 * BuddyPress Delete Account
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
	<h3><?php _e( 'Delete Account', 'vibe' ); ?></h3>

	<div id="message" class="info">

		<?php if ( bp_is_my_profile() ) : ?>

			<p><?php _e( 'Deleting your account will delete all of the content you have created. It will be completely irrecoverable.', 'vibe' ); ?></p>
			
		<?php else : ?>

			<p><?php _e( 'Deleting this account will delete all of the content it has created. It will be completely irrecoverable.', 'vibe' ); ?></p>

		<?php endif; ?>

	</div>

	<form action="<?php echo bp_displayed_user_domain() . bp_get_settings_slug() . '/delete-account'; ?>" name="account-delete-form" id="account-delete-form" class="standard-form" method="post">

		<?php do_action( 'bp_members_delete_account_before_submit' ); ?>

		<label>
			<input type="checkbox" name="delete-account-understand" id="delete-account-understand" value="1" onclick="if(this.checked) { document.getElementById('delete-account-button').disabled = ''; } else { document.getElementById('delete-account-button').disabled = 'disabled'; }" />
			 <?php _e( 'I understand the consequences.', 'vibe' ); ?>
		</label>

		<div class="submit">
			<input type="submit" disabled="disabled" value="<?php _e( 'Delete Account', 'vibe' ); ?>" id="delete-account-button" name="delete-account-button" />
		</div>

		<?php do_action( 'bp_members_delete_account_after_submit' ); ?>

		<?php wp_nonce_field( 'delete-account' ); ?>

	</form>

	<?php do_action( 'bp_after_member_body' ); ?>

</div><!-- #item-body -->

<?php do_action( 'bp_after_member_settings_template' ); ?>

<?php

vibe_include_template("profile/bottom.php");  

get_footer( vibe_get_footer() );  