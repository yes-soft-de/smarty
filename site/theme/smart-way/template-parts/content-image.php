<?php
/*
	=============================
		IMAGE POST FORMAT
	=============================
*/
?>
<!-- the_ID: fetch the post id | We specify new class for our post format page to avoid any duplicated that could happened in the future with new wordpress version -->
<article id="post-<?php the_ID(); ?>" <?php post_class( 'sunset-format-image' ); ?>>
	<!-- THis is the Standard wordpress markup -->
	<header class="entry-header text-center background-image" style="background-image: url(<?php echo sunset_get_attachment(); ?>);">
		<?php the_title( '<h1 class="entry-title"><a href="'. esc_url( get_permalink() ) .'" rel="bookmark">', '</a></h1>'); ?>
		<div class="entry-meta">
			<!-- Instead of writing this meta manually we use this function that we'll create -->
			<?php echo sunset_posted_meta(); ?>
		</div>
		<div class="entry-excerpt image-caption">
			<?php the_excerpt(); ?>
		</div>
	</header>
	<footer class="entry-footer">
		<?php echo sunset_posted_footer(); ?>
	</footer>
</article>
