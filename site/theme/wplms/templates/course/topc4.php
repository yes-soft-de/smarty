<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$header = vibe_get_customizer('header_style');
if($header == 'transparent' || $header == 'generic'){
    echo '<section id="title">';
     do_action('wplms_before_title'); 
    echo '<div class="container">
    	<div class="pagetitle">'; ?>
    		<?php vibe_breadcrumbs(); ?>
	<h1><a href="<?php bp_course_permalink(); ?>" title="<?php bp_course_name(); ?>"><?php bp_course_name(); ?></a></h1>
	<div id="item-meta">
		<?php bp_course_meta(); ?>
		<?php do_action( 'bp_course_header_actions' ); ?>

		<?php do_action( 'bp_course_header_meta' ); ?>
	</div>
    	<?php
		echo '</div></div></section>';
}
?>

<section id="content">
	<div id="buddypress">
		<div class="<?php echo vibe_get_container(); ?>">
			<div class="row">
				<div class="col-md-9">
					<div id="item-header" role="complementary">
						<?php 
						if($header == 'transparent' || $header == 'generic'){ 

							do_action( 'bp_before_course_header' );
							?>
							<div id="item-header-avatar">
									<?php bp_course_avatar(); ?>
							</div><!-- #item-header-avatar -->
							<?php
							do_action( 'bp_after_course_header' );
						}else{
							locate_template( array( 'course/single/course-header3.php' ), true ); 
						}?>
					</div><!-- #item-header -->
					<div id="item-nav">
						<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
							<ul>
								<?php bp_get_options_nav(); ?>
								<?php

								if(function_exists('bp_course_nav_menu'))
									bp_course_nav_menu();
								
								
								?>
								<?php do_action( 'bp_course_options_nav' ); ?>
							</ul>
							<?php do_action( 'bp_before_course_home_content' ); ?>
						</div>
					</div>
