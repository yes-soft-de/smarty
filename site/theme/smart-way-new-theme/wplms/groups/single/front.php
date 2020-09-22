<?php
if ( !defined( 'ABSPATH' ) ) exit;
do_action('bp_before_group_description');
?>
<h3 class="heading"><?php _e('Description','vibe'); ?></h3>
<?php bp_group_description(); ?>
<?php

do_action('bp_after_group_description');
?>
<?php 

if ( bp_group_is_visible() ) { ?>

	<div class="group_admins">
		<h3 class="heading"><?php _e('Administrators','vibe'); ?></h3>

		<?php bp_group_list_admins(); ?>
	</div>
	<?php do_action( 'bp_after_group_menu_admins' );

	if ( bp_group_has_moderators() ) { ?>
		<div class="group_mods">
			<?php
				do_action( 'bp_before_group_menu_mods' ); ?>

				<h3 class="heading"><?php _e('Moderators','vibe'); ?></h3>

				<?php bp_group_list_mods(); ?>
		</div>
		<?php do_action( 'bp_after_group_menu_mods' );
		}
	} 

do_action('bp_after_group_front');