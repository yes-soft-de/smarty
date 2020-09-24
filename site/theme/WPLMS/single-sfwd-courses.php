<?php 
	get_header( vibe_get_header() );

	if(have_posts()):
		while(have_posts()):the_post();
?>
<section id="content">
	<div id="buddypress">
	    <div class="container">
	        <div class="row">
	            <div class="col-md-3 col-sm-3">
	            	<div id="item-header" role="complementary">

						
	<div id="item-header-avatar" itemscope="" itemtype="http://data-vocabulary.org/Product">
		<?php
			the_post_thumbnail(get_the_ID(),'full');
		?>	
	</div><!-- #item-header-avatar -->


<div id="item-header-content">
<?php
	echo get_the_term_list( $post->ID, 'category', '<span class="highlight">', ',</span><span class="highlight">', '</span>' );
?>

	<h3><?php the_title(); ?></h3>
	
	
	<div id="item-meta">			
		
	</div>
</div><!-- #item-header-content -->

<div id="item-admins">

<h3><?php _e('INSTRUCTORS','vibe'); ?></h3>
	<div class="item-avatar">
		<?php echo get_avatar(get_the_author_meta('email',$post->post_author)); ?>
	</div>
	<h5 class="course_instructor"><?php echo get_the_author_meta('display_name',$post->post_author); ?></h5></div><!-- #item-actions -->


					</div>

					
			
				<div id="item-nav">
					<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
						<ul>
							<li id="home" class="<?php echo (!isset($_GET['action'])?'selected':''); ?>"><a href="<?php echo get_permalink(); ?>"><?php  _e( 'Home', 'vibe' ); ?></a></li>
							<li id="curriculum" class="<?php echo (($_GET['action']=='curriculum')?'selected':''); ?>"><a href="?action=curriculum"><?php  _e( 'Curriculum', 'vibe' ); ?></a></li>
							<li id="members" class="<?php echo (($_GET['action']=='info')?'selected':''); ?>"><a href="?action=info"><?php  _e( 'Course Info', 'vibe' ); ?></a></li>
							
						</ul>
					</div>
				</div><!-- #item-nav -->
			</div>
			<div class="col-md-6 col-sm-6">	
			<?php do_action( 'template_notices' ); ?>
			<div id="item-body">

				<?php
				if(isset($_GET['action']) && $_GET['action'] == 'curriculum'){
					echo do_shortcode('[ld_lesson_list]');
				}else if(isset($_GET['action']) && $_GET['action'] == 'info'){
					 the_widget('LearnDash_Course_Info_Widget',array(),array());   
				}else
					the_content();
				?>

			</div><!-- #item-body -->

			<?php do_action( 'bp_after_course_home_content' ); ?>

			<?php endwhile; endif; ?>
			</div>
			<div class="col-md-3 col-sm-3">	
				<div class="widget pricing">
					<?php
						echo learndash_payment_buttons(get_the_ID());
					?>
				</div>

			 	<?php
	                $sidebar=getPostMeta($post->ID,'vibe_sidebar');
	                ((isset($sidebar) && $sidebar)?$sidebar:$sidebar='coursesidebar');
	                if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
               	<?php endif; ?>
			</div>
		</div><!-- .padder -->
		
	</div><!-- #container -->
	</div>
</section>	
<?php get_footer( vibe_get_footer() );  ?>
