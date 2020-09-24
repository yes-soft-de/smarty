<?php
if ( !defined( 'ABSPATH' ) ) exit;
do_action( 'bp_before_group_header' );

?>
<div class="<?php echo vibe_get_container(); ?>">
	<div class="row">
		<div class="col-md-3">
			<div id="item-header-avatar">
				<a href="<?php bp_group_permalink(); ?>" title="<?php bp_group_name(); ?>">

					<?php bp_group_avatar(); ?>

				</a>
			</div><!-- #item-header-avatar -->
		</div>
		<div class="col-md-6">
			<div id="item-header-content">
				<span class="highlight"><?php bp_group_type(); ?></span>
				<h3><a href="<?php bp_group_permalink(); ?>" title="<?php bp_group_name(); ?>"><?php bp_group_name(); ?></a></h3>
				 <span class="activity"><?php printf( __( 'active %s', 'vibe' ), bp_get_group_last_active() ); ?></span>

				<?php do_action( 'bp_before_group_header_meta' ); ?>

				<div id="item-meta">

					<div id="item-buttons">

						<?php do_action( 'bp_group_header_actions' ); ?>

					</div><!-- #item-buttons -->

					<?php do_action( 'bp_group_header_meta' ); ?>

				</div>
			</div><!-- #item-header-content -->
		</div>
		<div class="col-md-3">
			<div id="item-actions">

				<?php if ( bp_group_is_visible() ) : ?>

					<h3><?php _e( 'Instructors', 'vibe' ); ?></h3>

					<?php bp_group_list_admins();

					do_action( 'bp_after_group_menu_admins' );

					if ( bp_group_has_moderators() ) :
						do_action( 'bp_before_group_menu_mods' ); ?>

						<h3><?php _e( 'Moderators' , 'vibe' ); ?></h3>

						<?php bp_group_list_mods();

						do_action( 'bp_after_group_menu_mods' );

					endif;

				endif; ?>
			</div><!-- #item-actions -->
		</div>
	</div>
</div>
<?php
do_action( 'bp_after_group_header' );

