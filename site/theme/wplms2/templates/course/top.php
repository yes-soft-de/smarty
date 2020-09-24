<?php

if ( ! defined( 'ABSPATH' ) ) exit;

$header = vibe_get_customizer('header_style');
if($header == 'transparent' || $header == 'generic'){
    echo '<section id="title">';
    do_action('wplms_before_title');
    echo '<div class="container"><div class="pagetitle"><h1>'.get_the_title().'</h1></div></div></section>';
}
?>
<section id="content">
	<div id="buddypress">
	    <div class="<?php echo vibe_get_container(); ?>">
	    	<?php do_action( 'bp_before_course_home_content' ); ?>
	        <div class="row">
	            <div class="col-md-3 col-sm-3">
					
					<div id="item-header" role="complementary">

						<?php locate_template( array( 'course/single/course-header.php' ), true ); ?>

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
					</div>
				</div><!-- #item-nav -->
			</div> 
			<div class="col-md-6 col-sm-6">	