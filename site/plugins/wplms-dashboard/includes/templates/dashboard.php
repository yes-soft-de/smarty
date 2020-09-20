<?php

/**
 * WPLMS- DASHBOARD TEMPLATE
 */

?>
<?php get_header( 'buddypress' ); ?>

<section id="content">
	<div id="buddypress">
	    <div class="container">
	        <div class="row">
	            <div class="col-md-3 col-sm-4">

					<?php do_action( 'bp_before_member_plugin_template' ); ?>
					 <div class="pagetitle">
	                	<div id="item-header">
							<?php locate_template( array( 'members/single/member-header.php' ), true ); ?>
						</div><!-- #item-header -->
					</div>	
					<div id="item-nav">
						<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
							<ul>

								<?php bp_get_displayed_user_nav(); ?>

								<?php do_action( 'bp_member_options_nav' ); ?>

							</ul>
						</div>
					</div><!-- #item-nav -->
				</div>	
				<div class="col-md-9 col-sm-8">
					<div class="padder">
						<div class="wplms-dashboard row">
							<?php do_action( 'bp_before_dashboard_body' ); ?>
							<?php
								if(current_user_can('edit_posts')){
									$sidebar = apply_filters('wplms_instructor_sidebar','instructor_sidebar');
				                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : endif; 
								}else{
				                    $sidebar = apply_filters('wplms_student_sidebar','student_sidebar');
				                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : endif; 
								}
							?>
							<?php do_action( 'bp_after_dashboard_body' ); ?>
						</div>	<!-- .wplms-dashboard -->
					</div><!-- .padder -->

					<?php do_action( 'bp_after_member_dashboard_template' ); ?>

					</div>
				</div><!-- #content -->
			</div>
		</div>
</section>	
</div> <!-- Extra Global div in header -->									
<?php get_footer( 'buddypress' ); ?>
