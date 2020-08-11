 <?php
/*
	=============================
		AUDIO POST FORMAT
	=============================
*/
?>
<!-- the_ID: fetch the post id | We specify new class for our post format page to avoid any duplicated that could happened in the future with new wordpress version -->
<article id="post-<?php the_ID(); ?>" <?php post_class( 'sunset-format-audio' ); ?>>
	<!-- THis is the Standard wordpress markup -->
	<header class="entry-header" style="background-image: url(<?php echo sunset_get_attachment(); ?>);">
		<?php the_title( '<h1 class="entry-title"><a href="'. esc_url( get_permalink() ) .'" rel="bookmark">', '</a></h1>'); ?>
		<div class="entry-meta">
			<!-- Instead of writing this meta manually we use this function that we'll create -->
			<?php echo sunset_posted_meta(); ?>
		</div>
	</header>
    <div class="entry-content">
		<?php echo sunset_get_embedded_media( array( 'audio', 'iframe' ) ) ?>
    </div> <!--.entry-content-->
	<footer class="entry-footer">
		<?php echo sunset_posted_footer(); ?>
	</footer>
</article>
