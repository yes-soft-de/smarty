<?php
if ( !defined( 'ABSPATH' ) ) exit;
do_action( 'bp_before_group_header' );

?>

	<div id="item-header-avatar">
		<a href="<?php bp_group_permalink(); ?>" title="<?php bp_group_name(); ?>">

			<?php bp_group_avatar(); ?>

		</a>
	</div><!-- #item-header-avatar -->


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

<?php
do_action( 'bp_after_group_header' );
?>