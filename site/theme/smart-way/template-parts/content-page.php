<?php
	/*
		=============================
			PAGE FORMAT
		=============================
	*/
?>
<!-- the_ID: fetch the post id | We need post_class to customize our block post -->
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<!-- THis is the Standard wordpress markup -->
	<header class="entry-header text-center">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header>

	<div class="entry-content clearfix">
		<?php the_content() ?>
	</div><!--.entry-content-->

</article>
