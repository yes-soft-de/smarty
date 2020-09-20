<?php 

if ( !defined( 'ABSPATH' ) ) exit;

$redirect_course_cat_directory = vibe_get_option('redirect_course_cat_directory');
if(!empty($redirect_course_cat_directory)){
	locate_template( array( 'course/index.php' ), true );	
	exit;	
}


// get_header( vibe_get_header() );
get_header(); 

$request_uri = explode('/', $_SERVER['REQUEST_URI']);
if ( in_array('courses', $request_uri, false) ): ?>

	<section id="title">
		<?php do_action('wplms_before_title'); ?>
		<div class="container-courses-header-image">
			<div class="container">
				<h1 class="page-title"><?php single_cat_title(); ?></h1>
				<h5><?php echo do_shortcode( category_description() ); ?></h5>
			</div>
		</div>
	</section>

<section id="content">
	<div id="buddypress" style="margin-top: 0;">
    <div class="custom-main">
		<div class="padder">
		<?php do_action( 'bp_before_directory_course' ); ?>	
		<div class="row" style="margin: 0">
			<?php 
				$args =	get_terms( 'course-cat', array( 'hide_empty' => 0, 'parent' => 71 ));
				// $args =	get_terms( 'course-cat', array( 'hide_empty' => 0 ));
				if ($args): ?>
				<div class="col-sm-2 col-md-3 llms-loop-categories-filter" style="padding-top: 2rem; padding-bottom: 2rem;">
					<h4>Filter by categories</h4>
					<div style="margin-top: 2rem; padding-left: 2rem; padding-right: 2rem;">
						<h5>All</h5>
						<i class="fa fa-check fa-1x" aria-hidden="true"></i>
					</div>
					<?php
					echo '<ul class="list-unstyled">';
						foreach ($args as $arg):
							echo '<li class="nav-item" style="margin-top: 1rem; padding-left: 2rem; padding-right: 2rem;">
								<a class="nav-link" href="' . get_term_link( $arg->slug, 'course-cat' ).'" aria-controls="now" aria-selected="true">'.$arg->name.'</a>
								</li>';
						endforeach;
					echo '</ul>';
					?>
				</div>
			<?php endif; ?>
			<div class="col-md-9 col-sm-8 custom-llms-loop-list">			
				<div class="content">
					<a href="" class="sort-courses py-4">
						<i class="fa fa-exchange" aria-hidden="true"></i>
						Sort from oldest to newest
					</a>
					<?php
					$style = vibe_get_option('default_course_block_style');
					if(Empty($style)){$style = apply_filters('wplms_instructor_courses_style','course2');}
					
						if ( have_posts() ) : while ( have_posts() ) : the_post();

						echo '<div class="col-md-4 col-sm-6 clear3">'.thumbnail_generator($post,$style,'3','0',true,true).'</div>';
					
						endwhile;
						pagination();
						endif;
					?>
				</div>
			</div>
		</div>	
		<?php do_action( 'bp_after_directory_course' ); ?>

		</div><!-- .padder -->
	
	<?php do_action( 'bp_after_directory_course_page' ); ?>
</div><!-- #content -->
</div>
</section>

<?php elseif ( in_array('meditations', $request_uri, false) ):?>
	<section id="content" class="custom-main">
	    <div class="llms-loop">
			<div id="spinner-cover" class="cover hide">
				<i class="fa fa-spinner fa-spin text-white fa-4x mx-auto" aria-hidden="true"></i>
			</div>
		<?php
		$args = array(
			'post_type' => 'course',
			'posts_per_page' => -1,
			'tax_query' => array(
				array (
					'taxonomy' => 'course-cat',
					'field' => 'slug',
					'terms' => 'meditations',
				)
			),
		);
		$query = new WP_Query( $args );

		$args = $query->get_posts();
		//		        $args =	get_terms( 'course_cat', array( 'hide_empty' => 0, 'parent' => 14 ));
		$argNumber = count($args);

		if ( $args ): ?>
			<article class="w-100 meditations-topics" style="width: 100%;">
			<h3 class="text-center mb-5">Choose one topic</h3>
			<?php if ( $argNumber <= 4 ): ?>
				<?php
					if ($argNumber == 1):
						$class = 'col-xs-' . ($argNumber+4) . ' ' . 'col-sm-' . ($argNumber+3) . ' ' . 'col-md-' . ($argNumber+2) . ' ' . 'col-lg-' . ($argNumber + 1) . ' ' . 'col-xl-' . ($argNumber) . ' mx-auto';
					elseif ($argNumber == 2):
						$class = 'col-xs-' . ($argNumber+6) . ' ' . 'col-sm-' . ($argNumber+4) . ' ' . 'col-md-' . ($argNumber + 3) . ' ' . 'col-lg-' . ($argNumber + 2) . ' ' . 'col-xl-' . ($argNumber + 1) . ' mx-auto';
					elseif($argNumber == 3):
						$class = 'col-xs-' . ($argNumber+7) . ' ' . 'col-sm-' . ($argNumber+5) . ' ' . 'col-md-' . ($argNumber + 4) . ' ' . 'col-lg-' . ($argNumber + 3) . ' ' . 'col-xl-' . ($argNumber + 2) . ' mx-auto';
					elseif($argNumber == 4):
						$class = 'col-xs-' . ($argNumber+8) . ' ' . 'col-sm-' . ($argNumber+6) . ' ' . 'col-md-' . ($argNumber + 5) . ' ' . 'col-lg-' . ($argNumber + 4) . ' ' . 'col-xl-' . ($argNumber + 3) . ' mx-auto';
					endif;
				?>
			<div class="<?php echo $class; ?>">
				<div class="row">
				<?php foreach ($args as $arg): ?>
					<div class="col-*-*">
						<a id="topic" data-id="<?php echo $arg->ID; ?>" data-url="<?php echo admin_url( 'admin-ajax.php' ); ?>" data-href="<?php echo get_the_permalink( $arg->ID ); ?>" href="#">
							<div class="topic">
								<img class="img-responsive topic-image" src="<?php echo get_the_post_thumbnail_url( $arg->ID ) ? get_the_post_thumbnail_url( $arg->ID ) : get_template_directory_uri() . '/assets/img/inner-peace-meditation.jpg'; ?>" alt="" >
								<h5 class="text-white"><?php echo $arg->post_title; ?></h5>
							</div>
						</a>
					</div>
				<?php endforeach; ?>
				</div>
			</div>
			<?php else: ?>
				<?php
				if ($argNumber == 5):
					$carouselClass = 'meditation-carousel-5';
					elseif ($argNumber == 6):
						$carouselClass = 'meditation-carousel-6';
					elseif($argNumber == 7):
						$carouselClass = 'meditation-carousel-7';
					elseif($argNumber == 8):
						$carouselClass = 'meditation-carousel-8';
					elseif($argNumber == 9):
						$carouselClass = 'meditation-carousel-9';
					elseif($argNumber >= 10):
					$carouselClass = 'meditation-carousel-10';
				endif;
				?>
				<div class="row meditation-carousel <?php echo $carouselClass; ?>">
					<?php foreach ($args as $arg): ?>
					<div class="col-xs-2">
						<a id="topic" data-id="<?php echo $arg->ID; ?>" data-url="<?php echo admin_url( 'admin-ajax.php' ); ?>" data-href="<?php echo get_the_permalink( $arg->ID ); ?>" href="#">
							<div class="topic">
								<img class="img-responsive topic-image" src="<?php echo get_the_post_thumbnail_url( $arg->ID ) ? get_the_post_thumbnail_url( $arg->ID ) : get_template_directory_uri() . '/assets/img/inner-peace-meditation.jpg'; ?>" alt="" >
								<h5 class="text-white"><?php echo $arg->post_title; ?></h5>
							</div>
						</a>
					</div>
					<?php endforeach; ?>
				</div><!--.meditation-carousel-->
			<?php endif; ?>

			</article>
		<?php endif; ?>
		<div class="col-xs-12 mx-auto mt-4 pb-5">
          <div class="container">
				<?php
					$args = array(
						'post_type' => 'course',
						'tax_query' => array(
							array (
								'taxonomy' => 'course-cat',
								'field' => 'term_id',
								'terms' => get_queried_object_id(),
							)
						),
					);
					$query = new WP_Query( $args );
				?>
				<h3>Mindfulness plan ( <?php echo $query->found_posts . ' ' . ($query->found_posts > 1 ? 'Topics' : 'Topic'); ?> )</h3>
				<p class="lead">Online Mindfulness-Based Stress Reduction (MBSR).This online MBSR training course is created by a fully certified MBSR instructor, and is based</p>
				<ul class="custom-llms-loop-list">
					<div class="row">
						<div id="llms-loop-item-meditations" class="col-12 mx-auto">
							<div class="alert text-center" style="background: #30124E">
								<h3 style="color: #fff">Select Your Topic</h3>
							</div>
						</div>
					</div>
				</ul>
			</div>
		</div>
		<div class="clearfix"></div>
		<?php $request_uri = explode('/', $_SERVER['REQUEST_URI']);
			echo '<div class="container">';
			if ( in_array('meditations', $request_uri, false) ): ?>
				<div class="col-xs-12 meditation-bottom-section pt-5">
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Adipisci expedita explicabo perspiciatis sed! Assumenda consequatur esse est eum, incidunt iusto modi molestias nemo neque non obcaecati qui rem repudiandae tempora.</p>
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Adipisci expedita explicabo perspiciatis sed! Assumenda consequatur esse est eum, incidunt iusto modi molestias nemo neque non obcaecati qui rem repudiandae tempora.</p>
				</div>
				<div class="clearfix"></div>
			<!-- </div> -->
		<?php endif; echo '</div>'; ?>
		</div><!-- .col-12 -->
	</section>

<?php else: ?>

	<section id="title">
	<?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
             <div class="col-md-12">
                <div class="pagetitle">
                	<?php vibe_breadcrumbs(); ?> 
                   	<h1><?php single_cat_title(); ?></h1>
                    <h5><?php echo do_shortcode(category_description()); ?></h5>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="content">
	<div id="buddypress">
    <div class="<?php echo vibe_get_container(); ?>">
		<div class="padder">
		<?php do_action( 'bp_before_directory_course' ); ?>	
		<div class="row">
			<div class="col-md-9 col-sm-8">
				<div class="content">
				<?php
				$style = vibe_get_option('default_course_block_style');
				if(Empty($style)){$style = apply_filters('wplms_instructor_courses_style','course2');}
				
					if ( have_posts() ) : while ( have_posts() ) : the_post();

					echo '<div class="col-md-4 col-sm-6 clear3">'.thumbnail_generator($post,$style,'3','0',true,true).'</div>';
				
					endwhile;
					pagination();
					endif;
				?>
				</div>
			</div>	
			<div class="col-md-3 col-sm-3">
				<?php
                   $sidebar = apply_filters('wplms_sidebar','coursesidebar');
                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
                <?php endif; ?>
			</div>
		</div>	
		<?php do_action( 'bp_after_directory_course' ); ?>

		</div><!-- .padder -->
	
	<?php do_action( 'bp_after_directory_course_page' ); ?>
</div><!-- #content-->
</div>
</section>

<?php endif;
get_footer();
// get_footer( vibe_get_footer() );  ?>

