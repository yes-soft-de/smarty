<?php
$header_style =  vibe_get_customizer('header_style');
if($header_style == 'transparent'){ 
	echo '<section id="title">';
	do_action('wplms_before_title');
	echo '</section>';
}
?>
<section id="content">
	<div id="buddypress">
	    <div class="<?php echo vibe_get_container(); ?>">
	        <div class="row">
	            <div class="col-md-3 col-sm-3">

					<?php do_action( 'bp_before_group_home_content' ); ?>

					<div id="item-header" role="complementary">

						<?php locate_template( array( 'groups/single/group-header.php' ), true ); ?>

					</div><!-- #item-header -->
			
				<div id="item-nav">
					<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
						<ul>

							<?php bp_get_options_nav(); ?>

							<?php do_action( 'bp_group_options_nav' ); ?>
						</ul>
					</div>
				</div><!-- #item-nav -->
			</div>
			<div class="col-md-9 col-sm-9">	