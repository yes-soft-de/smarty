<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



$layout = new WP_Query(apply_filters('vibebp_public_profile_layout_query',array(
	'post_type'=>'group-layout',
	'post_name'=>bp_get_group_type(bp_get_group_id()),
	'posts_per_page'=>1,
)));
if ( !$layout->have_posts() ){

	$layout = new WP_Query(array(
		'post_type'=>'group-layout',
		'orderby'=>'date',
		'order'=>'ASC',
		'posts_per_page'=>1,
	));
}

if ( !$layout->have_posts() ){
	wp_die(__('Create a new group layout.','vibebp'));
}
get_header();
?>
<div id="vibebp_group">
<div id="primary" class="content-area">
	<div class="container">
		<main id="group_<?php echo bp_get_group_id(); ?>">
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
</div>
<?php
get_footer();