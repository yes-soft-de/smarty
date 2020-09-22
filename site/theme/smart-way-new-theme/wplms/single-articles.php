<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<div class="article-details-page">

	<div class="container">

		<img src="<?php echo ( has_post_thumbnail() ) ? wp_get_attachment_url( get_post_thumbnail_id( get_the_ID() ) ) : get_template_directory_uri() . '/assets/images/inner-peace-meditation.jpg'; ?>" class="main-img img-responsive">

		<div class="title-box">
			<h1>
				<?php echo get_the_title(); ?>
			</h1>

			<span class="date"><?php echo get_the_time('F j, Y'); ?></span>

		</div>

		<div class="details-content">
			<?php echo get_the_content(); ?>
		</div>
		<div class="share-area">
			<span>Share to</span>
			<ul>
				<li class="facebook">
					<a href="#">
						<i class="fa fa-facebook" aria-hidden="true"></i>
					</a>
				</li>
				<li class="twitter">
					<a href="#">
						<i class="fa fa-twitter" aria-hidden="true"></i>
					</a>
				</li>
				<li class="whatsapp">
					<a href="#">
						<i class="fa fa-whatsapp" aria-hidden="true"></i>
					</a>
				</li>
			</ul>
		</div>

	</div>

</div>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
