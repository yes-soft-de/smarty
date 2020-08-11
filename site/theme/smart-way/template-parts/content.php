<?php
/*
	=============================
		STANDARD POST FORMAT
	=============================
*/
?>
<!-- the_ID: fetch the post id | We need post_class to customize our block post -->
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<!-- THis is the Standard wordpress markup -->
	<header class="entry-header text-center">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		<div class="entry-meta">
			<!-- Instead of writing this meta manually we use this function that we'll create -->
			<?php echo sunset_posted_meta(); ?>
		</div>
	</header>
	<div class="entry-content">
		<?php
            if ( has_post_thumbnail() ):
                // Get THe Thumbnail Url
                $featuredImage = wp_get_attachment_url( get_post_thumbnail_id( get_the_ID() ) );
            ?>
                <a class="standard-featured-link" href="<?php the_permalink(); ?>">
                    <div class="standard-featured background-image" style="background-image: url(<?php echo $featuredImage ?>)">
                        <!--<?php // the_post_thumbnail() ?> : We Can Use Post Thumbnail this way but we need to make our thumbnail as background for the dev -->
                    </div>
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
