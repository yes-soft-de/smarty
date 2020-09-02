<?php /* Template Name: Articles */ ?>

<?php get_header(); ?>
    
<div class = "main-content">
		<?php $catquery = new WP_Query( 'cat=195&posts_per_page=5' ); ?>
			<?php
	                $x=1;
	                 
	                ?>
<div class = "post">
  <?php while($catquery->have_posts()) : $catquery->the_post(); ?>
  <img src = "<?php echo get_template_directory_uri() .'/assets/images/assets/image/yoga2.jpg'?>" alt = "" class="post-image">
  <div class="post-preview">
  <h2><a href = "<?php the_permalink() ?>" > The title of the blog we need to last full big art dkd</a></h2>

	  <i class = "far calendar"> 31-8-2020</i> 
	  </div>
  <p class = "preview-text">
 <span><?php echo $x.'-' ?></span>
							<a href="<?php the_permalink() ?>" >
							<?php the_title(); ?>
							</a>
  </p>
  <i class = "share icon">share to</i>
  <i>  <img src = "<?php echo get_template_directory_uri() .'/assets/images/twitter.png'?>" alt = "" class="tw icon"></i>
  &nbsp;
  <i>  <img src = "<?php echo get_template_directory_uri() .'/assets/images/facebook.png'?>" alt = "" class="fa icon"></i>

  &nbsp;
  <i>  <img src = "<?php echo get_template_directory_uri() .'/assets/images/instagram.png'?>" alt = "" class="in icon"></i>

  <i href = "" class = "btn learn-more"> learn More </i>
  </div>
</div>
<?php endwhile; ?>
<?php get_footer(); ?>
