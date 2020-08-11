<?php
	/*
			This is the template for the Page
			@package sunsettheme
	*/
?>
<?php get_header() ?>
<!-- This is the standard html code that we will find in every standard wordpress theme -->
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<div class="container">
			<?php
				if ( have_posts() ):
//					echo '<div class="page-limit" data-page="/' . sunset_check_paged() . '">';
					while( have_posts() ):
						the_post();

						get_template_part( 'template-parts/content', 'page' );   // ex : template-parts/content-page.php
					endwhile;
//					echo '</div>';
				endif;
			?>
		</div>

	</main>
</div><!--#primary-->

<?php get_footer() ?>
