<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<section id="title">
	<?php do_action('wplms_before_title'); ?>
	<div class="course_header">
		<div class="<?php echo vibe_get_container(); ?>">
			<div id="item-header" role="complementary">
				<?php locate_template( array( 'course/single/course-header4.php' ), true ); ?>
			</div><!-- #item-header -->
		</div>
	</div>
</section>
<section>
	<div id="item-nav">
		<div class="<?php echo vibe_get_container(); ?>">
			<div class="row">
				<div class="col-md-12">
					<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
						<ul>
							<?php bp_get_options_nav(); ?>
							<?php

							if(function_exists('bp_course_nav_menu'))
								bp_course_nav_menu();
							
							
							?>
							<?php do_action( 'bp_course_options_nav' ); ?>
						</ul>
					</div>
				</div>
			</div>
		</div><!-- #item-nav -->
	</div>
</section>
<section id="content">
	<div id="buddypress">
	    <div class="<?php echo vibe_get_container(); ?>">
	    	<?php do_action( 'bp_before_course_home_content' ); ?>
	        <div class="row">
				<div class="col-md-9">	