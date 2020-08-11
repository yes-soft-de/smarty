<?php
/*
	=============================
		IMAGE POST FORMAT
	=============================
*/
?>
<!-- the_ID: fetch the post id | We need post_class to customize our block post -->
<article id="post-<?php the_ID(); ?>" <?php post_class( 'sunset-format-video' ); ?>>
	<!-- THis is the Standard wordpress markup -->
	<header class="entry-header text-center">
        <div class="embed-responsive embed-responsive-16by9">
            <?php echo sunset_get_embedded_media( array( 'video', 'iframe' ) ); ?>
        </div>
		<?php the_title( '<h1 class="entry-title"><a href="'. esc_url( get_permalink() ) .'" rel="bookmark">', '</a></h1>' ); ?>
		<div class="entry-meta">
			<!-- Instead of writing this meta manually we use this function that we'll create -->
			<?php echo sunset_posted_meta(); ?>
		</div>
	</header>
	<div class="entry-content">
		<?php
      if ( sunset_get_attachment() ): ?>
          <a class="standard-featured-link" href="<?php the_permalink(); ?>">
              <div class="standard-featured background-image" style="background-image: url(<?php echo sunset_get_attachment(); ?>);"></div>
          </a>
		<?php endif; ?>
		<div class="entry-excerpt">
			<?php the_excerpt(); ?>
		</div>
		<div class="button-container text-center">
			<!-- _e(test) : it's like echo, is the safety way to print text and prevent user from edit it-->
			<a href="<?php the_permalink(); ?>" class="btn btn-default"><?php _e( 'Read More' ); ?></a>
		</div>
	</div><!--.entry-content-->
	<footer class="entry-footer">
		<?php echo sunset_posted_footer(); ?>
	</footer>
</article>
