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
		<?php if ( bp_is_my_profile() ) bp_get_options_nav(); ?>

		<?php if ( !bp_is_current_action( 'invites' ) ) : ?>

			<li id="groups-order-select" class="last filter">

				<label for="groups-sort-by"><?php _e( 'Order By:', 'vibe' ); ?></label>
				<select id="groups-sort-by">
					<option value="active"><?php _e( 'Last Active', 'vibe' ); ?></option>
					<option value="popular"><?php _e( 'Most Members', 'vibe' ); ?></option>
					<option value="newest"><?php _e( 'Newly Created', 'vibe' ); ?></option>
					<option value="alphabetical"><?php _e( 'Alphabetical', 'vibe' ); ?></option>

					<?php do_action( 'bp_member_group_order_options' ); ?>

				</select>
			</li>

		<?php endif; ?>

	</ul>
</div><!-- .item-list-tabs -->

<?php
do_action('wplms_after_single_item_list_tabs');
if ( bp_is_current_action( 'invites' ) ) :
	locate_template( array( 'members/single/groups/invites.php' ), true );

else :
	do_action( 'bp_before_member_groups_content' ); ?>

	<div class="groups mygroups">

		<?php locate_template( array( 'groups/groups-loop.php' ), true ); ?>

	</div>

	<?php do_action( 'bp_after_member_groups_content' ); ?>

<?php endif; ?>
