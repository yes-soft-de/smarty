<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$members_directory = vibe_get_bp_page_id('members');

$layout = new WP_Query(apply_filters('vibebp_members_directory_layout_query',array(
	'post_type'=>'page',
	'p'=>$members_directory
)));

?>
<div id="primary" class="content-area">
	<div class="container">
		<main id="user_<?php echo bp_displayed_user_id(); ?>" <?php vibebp_member_class(); ?>>
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

