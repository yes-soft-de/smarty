<?php
/**
 * The template for displaying Course Header
 *
 * Override this template by copying it to yourtheme/course/single/header.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.0
 */

if ( !defined( 'ABSPATH' ) ) exit;
do_action( 'bp_before_group_header' );

?>
<div id="item-header-content">
	<div class="row">
	<div class="col-md-4">
		<div id="item-header-avatar">
			<?php bp_group_avatar(); ?>
			<style>#item-header-avatar{padding:15px;}#buddypress div#item-header div#item-header-content {padding:15px;mix-blend-mode: unset;}
			</style>
		</div><!-- #item-header-avatar -->
	</div>
	<div class="col-md-8">
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
	</div>
	</div>
</div><!-- #item-header-content -->

<?php
do_action( 'bp_after_group_header' );
 