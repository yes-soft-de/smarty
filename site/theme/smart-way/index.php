<?php get_header() ?>
<!-- This is the standard html code that we will find in every standard wordpress theme -->
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<div class="container sunset-posts-container">
			<?php
				if ( have_posts() ):
					while( have_posts() ):
						the_post();
						get_template_part( 'template-parts/content', get_post_format() );   // ex : template-parts/content, template-parts/content-image ...
					endwhile;
					the_posts_pagination();
				endif;
			?>
		</div>

	</main>
</div><!--#primary-->

<?php get_footer() ?>
