<?php
/**
 * LifterLMS Loop Start Wrapper
 *
 * @package LifterLMS/Templates
 *
 * @since   1.0.0
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="llms-loop">
	<div class="row mx-auto">
		<?php
      $request_uri = explode('/', $_SERVER['REQUEST_URI']);
      if ( in_array('courses', $request_uri, false) ):
        $args =	get_terms( 'course_cat', array( 'hide_empty' => 0, 'parent' => 13 ));
        if ($args): ?>
          <div class="col-sm-2 col-md-3 llms-loop-categories-filter p-4 d-none d-sm-block">
            <h4>
              Filter by categories
            </h4>
            <div class="px-3 mt-3">
              <h5>
                <?php
  // 							$term = get_term_by( 'course_cat', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
  // 							$parent = get_term_by( 'id', $term->parent, get_query_var( 'taxonomy' ) );
  // 							if($parent):
  // 								echo $parent->name;
  // 							endif;
                ?>
                All
              </h5>
              <i class="fa fa-check fa-1x" aria-hidden="true"></i>
            </div>
            <?php
              echo '<ul class="list-unstyled">';
              foreach ($args as $arg):
                echo '<li class="nav-item">
                    <a class="nav-link" href="' . get_term_link( $arg->slug, 'course_cat' ).'" aria-controls="now" aria-selected="true">'.$arg->name.'</a>
                    </li>';
              endforeach;
              echo '</ul>';
            ?>
          </div>
        <?php endif; ?>
        <div class="col-12 col-sm-10 col-md-9 mx-auto">
        <ul class="custom-llms-loop-list llms-loop-list<?php echo llms_get_loop_list_classes(); ?>">
        <a href="" class="sort-courses py-4">
          <i class="fa fa-exchange" aria-hidden="true"></i>
          Sort from oldest to newest
        </a>
        <div class="row">
      <?php elseif ( in_array('meditations', $request_uri, false) ):?>
	        <?php
	          $args = array(
		          'post_type' => 'course',
		          'posts_per_page' => -1,
		          'tax_query' => array(
			          array (
				          'taxonomy' => 'course_cat',
				          'field' => 'slug',
				          'terms' => 'meditations',
			          )
		          ),
	          );
	          $query = new WP_Query( $args );
//	          echo '<pre>';
//	            print_r($query);
//	          echo '</pre>';
//           die();
           $args = $query->get_posts();
//		        $args =	get_terms( 'course_cat', array( 'hide_empty' => 0, 'parent' => 14 ));
		        $argNumber = count($args);

		        if ( $args ): ?>
            <article class="w-100 meditations-topics">
              <h3 class="text-center mb-5">Choose one topic</h3>
              <?php if ( $argNumber <= 4 ): ?>
                  <?php
                      if ($argNumber == 1):
                          $class = 'col-' . ($argNumber+4) . ' ' . 'col-sm-' . ($argNumber+3) . ' ' . 'col-md-' . ($argNumber+2) . ' ' . 'col-lg-' . ($argNumber + 1) . ' ' . 'col-xl-' . ($argNumber) . ' mx-auto';
                      elseif ($argNumber == 2):
  	                      $class = 'col-' . ($argNumber+6) . ' ' . 'col-sm-' . ($argNumber+4) . ' ' . 'col-md-' . ($argNumber + 3) . ' ' . 'col-lg-' . ($argNumber + 2) . ' ' . 'col-xl-' . ($argNumber + 1) . ' mx-auto';
                      elseif($argNumber == 3):
	                      $class = 'col-' . ($argNumber+7) . ' ' . 'col-sm-' . ($argNumber+5) . ' ' . 'col-md-' . ($argNumber + 4) . ' ' . 'col-lg-' . ($argNumber + 3) . ' ' . 'col-xl-' . ($argNumber + 2) . ' mx-auto';
                      elseif($argNumber == 4):
	                      $class = 'col-' . ($argNumber+8) . ' ' . 'col-sm-' . ($argNumber+6) . ' ' . 'col-md-' . ($argNumber + 5) . ' ' . 'col-lg-' . ($argNumber + 4) . ' ' . 'col-xl-' . ($argNumber + 3) . ' mx-auto';
                      endif;
                  ?>
                <div class="<?php echo $class; ?>">
                  <div class="row">
                    <?php foreach ($args as $arg): ?>
                      <div class="col">
                        <a id="topic" data-id="<?php echo $arg->ID; ?>" data-url="<?php echo admin_url( 'admin-ajax.php' ); ?>" data-href="<?php echo get_the_permalink( $arg->ID ); ?>" href="#">
                          <div class="topic">
                            <img class="img-fluid topic-image" src="<?php echo get_the_post_thumbnail_url( $arg->ID ) ? get_the_post_thumbnail_url( $arg->ID ) : get_template_directory_uri() . '/img/inner-peace-meditation.jpg'; ?>" alt="" >
                            <h5><?php echo $arg->post_title; ?></h5>
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
                    <div class="col-2">
                      <a id="topic" data-id="<?php echo $arg->ID; ?>" data-url="<?php echo admin_url( 'admin-ajax.php' ); ?>" data-href="<?php echo get_the_permalink( $arg->ID ); ?>" href="#">
                        <div class="topic">
                          <img class="img-fluid" src="<?php echo get_template_directory_uri() . '/img/inner-peace-meditation.jpg'; ?>" alt="" >
                          <h5><?php echo $arg->post_title; ?></h5>
                        </div>
                      </a>
                    </div>
                  <?php endforeach; ?>
                </div><!--.meditation-carousel-->
              <?php endif; ?>

            </article>
        <?php endif; ?>
        <div class="col-12 mx-auto mt-4">
          <div class="container">
            <?php
	            $args = array(
		            'post_type' => 'course',
		            'tax_query' => array(
			            array (
				            'taxonomy' => 'course_cat',
				            'field' => 'term_id',
				            'terms' => get_queried_object_id(),
			            )
		            ),
	            );
	            $query = new WP_Query( $args );
            ?>
            <h3>Mindfulness plan ( <?php echo $query->found_posts . ' ' . ($query->found_posts > 1 ? 'Topics' : 'Topic'); ?> )</h3>
            <p class="lead">Lorem ipsum dolor sit amet, consectetur adipisicing elit. At autem et, impedit maxime minus nesciunt soluta? Accusantium ad alias aperiam aspernatur debitis doloribus, enim error libero optio voluptatum? Error, repudiandae!</p>
            <ul class="custom-llms-loop-list llms-loop-list<?php echo llms_get_loop_list_classes(); ?>">
            <div class="row">
      <?php else: ?>
          <div class="col-12 col-sm-10 col-md-9 mx-auto">
            <ul class="custom-llms-loop-list llms-loop-list<?php echo llms_get_loop_list_classes(); ?>">
              <div class="row">
      <?php endif; ?>
