
<section id="content">
	<div id="buddypress">
		<div class="group_header">
			<?php 
			/**
			 * Fires before the display of member home content.
			 *
			 * @since BuddyPress (1.2.0)
			 */
			do_action( 'bp_before_group_home_content' ); ?>

			<div id="item-header" class="<?php 
						$image = bp_attachments_get_group_has_cover_image();echo (empty($image)?'':'cover_image')?>"  role="complementary">

				<?php locate_template( array( 'groups/single/group-header2.php' ), true );?>

			</div><!-- #item-header -->
		</div><!-- #item-header -->
		<div id="item-nav">
			<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
				<div class="<?php echo vibe_get_container(); ?>">
					<div class="col-md-9 col-md-offset-3">
						<ul>

							<?php bp_get_options_nav(); ?>

							<?php do_action( 'bp_group_options_nav' ); ?>

						</ul>
					</div>
				</div>
			</div>
		</div><!-- #item-nav -->
	    <div class="<?php echo vibe_get_container(); ?>">
	        <div class="row">	
				<div class="col-md-12">
				