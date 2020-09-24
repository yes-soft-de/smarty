<?php

/**
 * BuddyPress - Users Plugins
 *
 * This is a fallback file that external plugins can use if the template they
 * need is not installed in the current theme. Use the actions in this template
 * to output everything your plugin needs.
 *
 * @package BuddyPress
 * @subpackage bp-default
 */
if ( !defined( 'ABSPATH' ) ) exit;
get_header( vibe_get_header() ); 

$profile_layout = vibe_get_customizer('profile_layout');

vibe_include_template("profile/top$profile_layout.php");  
?>
<div id="item-body" role="main">

	<?php do_action( 'bp_before_member_body' ); ?>

	<div class="item-list-tabs no-ajax" id="subnav">
		<ul>

			<?php bp_get_options_nav(); ?>

			<?php do_action( 'bp_member_plugin_options_nav' ); ?>

		</ul>
	</div><!-- .item-list-tabs -->
	<?php do_action('wplms_after_single_item_list_tabs'); ?>
	<h3><?php do_action( 'bp_template_title' ); ?></h3>

	<?php do_action( 'bp_template_content' ); ?>

	<?php do_action( 'bp_after_member_body' ); ?>

</div><!-- #item-body -->


<?php do_action( 'bp_after_member_plugin_template' ); ?>
<?php

vibe_include_template("profile/bottom.php");  

get_footer( vibe_get_footer() );  						