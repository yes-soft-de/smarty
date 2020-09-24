<?php

if ( !defined( 'ABSPATH' ) ) exit;
do_action('wplms_before_single_group');

get_header( vibe_get_header() ); 

$group_layout = vibe_get_customizer('group_layout');
if ( bp_has_groups() ) : while ( bp_groups() ) : bp_the_group();

vibe_include_template("groups/top$group_layout.php"); 
?>
					

					<div id="item-body">

						<?php do_action( 'bp_before_group_body' ); ?>

						<?php do_action( 'bp_template_content' ); ?>

						<?php do_action( 'bp_after_group_body' ); ?>
					</div><!-- #item-body -->

					<?php do_action( 'bp_after_group_plugin_template' ); ?>

					<?php endwhile; endif; ?>

<?php
				
vibe_include_template("groups/bottom$group_layout.php","groups/bottom.php");  			
get_footer( vibe_get_footer() );  