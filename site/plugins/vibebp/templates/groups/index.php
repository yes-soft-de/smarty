<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$groups_directory = vibe_get_bp_page_id('groups');

$layout = new WP_Query(apply_filters('vibebp_groups_directory_layout_query',array(
	'post_type'=>'page',
	'p'=>$groups_directory
)));
?>
<div id="primary" class="content-area">
	<div class="container">
		<main>
		<?php
		if ( $layout->have_posts() ) :
			
			/* Start the Loop */
			while ( $layout->have_posts() ) :
				$layout->the_post();
				
				the_content();
				break;
			endwhile;
		endif;
		?>

		</main><!-- #main -->
	</div>
</div><!-- #primary -->

