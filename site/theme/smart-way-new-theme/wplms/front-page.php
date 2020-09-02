<?php get_header(); ?>

<!-- Start Home Page -->
<?php  echo wp_get_session_token(); ?>
<div class="home-page">
  <!-- live video section -->
 <?php
	 $args = array(
		 'post_type' => 'course',
		 'posts_per_page' => 6,
		 'tax_query' => array(
			 array (
				 'taxonomy' => 'course-cat',
				 'field' => 'slug',
				 'terms' => 'live-video',
			 )
		 ),
	 );
	 $query = new WP_Query( $args );
	 if ( $query->have_posts() ): ?>
    <article class="live-video">
      <div class="container">
        <div class="row live-video-carousel">

          <?php while ( $query->have_posts() ):
            $query->the_post(); ?>
            <div class="col-xs-12">
              <div class="row">
              <div class="col-xs-12 col-md-8">
                <div class="live-video-desc">
                  <div class="row">
                    <div class="col-xs-6 pl-5">
                      <span class="intro text-white">join our community</span>
                    </div>
                    <div class="col-xs-6 text-right pr-4">
                      <span>For 50$</span>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-xs-12 pl-5">
                      <h3 class="h1">
                        <a class="btn btn-secondary" aria-label="star">
                          <i class="fa fa-star-o" aria-hidden="true"></i>
                        </a>
                        Live Video
                      </h3>
                      <p><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a></p>
                      <p><?php echo get_the_time(); ?></p>
                      <p>With <?php echo get_the_author(); ?></p>
                    </div>
                    <div class="clearfix"></div>
                  </div><!--.row-->
                  <div class="w-75 register-button" style="width: 75%;">
<!--                    <a href="--><?php //echo get_site_url() . '/register' ?><!--">Register now</a>-->
	                  <?php echo do_shortcode( '[lifterlms_access_plan_button id="51"]Register Now[/lifterlms_access_plan_button]') ;?>
                    <span class="d-inline-block text-center"><i class="fa fa-arrow-right"></i></span>
                  </div>
                </div>
              </div>
              <div class="col-md-4 d-none d-md-block align-self-center text-center">
                <img class="live-video-image flashOpacity" src="<?php echo get_template_directory_uri() . '/assets/img/home-slider-circle.png' ?>" alt="home slider circle">
              </div>
              <div class="clearfix"></div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>

    </div>
  </article>
   <?php endif;
	 /* Restore original Post Data */
	 wp_reset_postdata(); ?>
  <!-- live video section -->

  <!-- What We Do -->
  <article class="what-we-do">
    <div class="container">
      <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-6 mb-5">
          <span class="intro mb-3">intro</span>
          <h3 class="mb-4">What we do</h3>
          <p class="content-desc">Empowering individuals to become more confident in their ability to master a particular skill for the future challenges and helping them know their potentials and capabilities for development and innovation to become like a bridge between human resources and institutions.</p>
        </div>
        <div class="d-none d-sm-block col-sm-4 col-md-6 align-self-center text-center mb-5">
          <img class="video-player-icon" src="<?php echo get_template_directory_uri() . '/assets/img/purple-card.png' ?>" alt="">
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 col-md-4">
          <div class="content-icon-item">
            <img class="responsive-image" src="<?php echo get_template_directory_uri() . '/assets/img/react.png'; ?>" alt="react image">
          </div>
          <h5>Develop & Empowering</h5>
          <p class="content-small-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Debitis facilis obcaecati reprehenderit?</p>
        </div>
        <div class="col-xs-12 col-md-4">
          <div class="content-icon-item">
            <img class="responsive-image" src="<?php echo get_template_directory_uri() . '/assets/img/balance.png'; ?>" alt="balance image">
          </div>
          <h5>Give advice</h5>
          <p class="content-small-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Debitis facilis obcaecati reprehenderit?</p>
        </div>
        <div class="col-xs-12 col-md-4">
          <div class="content-icon-item">
            <img class="responsive-image" src="<?php echo get_template_directory_uri() . '/assets/img/brain.png'; ?>" alt="brain image">
          </div>
          <h5>Guid you to the first step</h5>
          <p class="content-small-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Debitis facilis obcaecati reprehenderit?</p>
        </div>
      </div>
    </div>
  </article>
  <!-- What We Do -->

  <!-- Believe Us -->
  <article class="believe-us">
    <div class="container">
      <span class="intro text-center">our record</span>
      <h3 class="text-center mb-5">What we do more than you can<br> imagine, believe us.</h3>
      <div class="row text-center">
        <div class="col-xs-12 col-md-4">
          <span class="number pink">15+</span>
          <span class="event-name">events</span>
        </div>
        <div class="col-xs-12 col-md-4">
          <span class="number blue">33</span>
          <span class="event-name">crazy days</span>
        </div>
        <div class="col-xs-12 col-md-4">
          <span class="number green">12</span>
          <span class="event-name">travels</span>
        </div>
      </div>
    </div>
  </article>
  <!-- Believe Us -->

  <!-- Power of meditation -->
  <?php
    $args = array(
      'post_type' => 'post',
      'posts_per_page' => 6
    );
    $posts = new WP_Query( $args );
    if ($posts->have_posts()): ?>
    <article class="power-meditation">
      <div class="power-top-box"></div>
      <div class="power-bottom-box"></div>

      <div class="parent-content-box parent-content-box-carousel">
        <?php
            while ( $posts->have_posts() ):
	            $posts->the_post(); ?>
              <div class="row">
                <div class="col-xs-9">
                  <div class="content-box">
                    <div class="col-xs-9 content-top">
                      <span class="intro">Blog</span>
                      <h3 class="text-white my-3"><?php echo get_the_title(); ?></h3>
                      <p class="excerpt-content"><?php the_excerpt(); ?></p>
                      <a href="<?php echo get_the_permalink(); ?>" class="learn-more-link mt-4">Learn more</a>
                    </div>
                    <div class="content-image">
                      <img class="responsive-image" src="<?php echo get_the_post_thumbnail_url(); ?>" alt=""/>
                    </div>
                  </div>
                </div>
                <div class="col-xs-3"></div>
              </div><!--.row-->
        <?php endwhile; ?>
      </div><!--.parent-content-box-carousel-->
    </article>
  <?php endif; ?>
  <!-- Power of meditation -->

  <!-- Our Services -->
  <article class="our-services">
    <div class="col-xs-12 col-sm-10 col-md-10 mx-auto">
      <span class="intro text-center">we are amazing</span>
      <h3 class="text-center text-white my-4">Our Services</h3>
      <p class="col-xs-12 col-sm-10 col-md-8 col-lg-5 mx-auto text-center">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Amet culpa deleniti eligendi esse expedita</p>
      <div class="row">
        <div class="col-xs-12 col-sm-7">
          <div class="content-service">
            <div class="row">
              <div class="col-xs-6 mb-4">
                <div class="service-box">
                  <div class="content-icon-item">
                    <img class="responsive-image" src="<?php echo get_template_directory_uri() . '/assets/img/billiard-without-border.png'; ?>" alt="brain image">
                  </div>
                  <h6 class="green">volunteer group</h6>
                  <p class="content-small-desc mb-3">Revive the spirit in volunteer teams by:</p>
                  <ul>
                    <li>Laying the basics of team building</li>
                    <li>Teaching them how to get financial support</li>
                    <li>How to create a clear plan, system for polarization, retention, and continuity</li>
                    <li>How to prepare programs that help the growth and development of the team</li>
                  </ul>
                </div>
              </div>
              <div class="col-xs-6 mb-5">
                <div class="content-icon-item">
                  <img class="responsive-image" src="<?php echo get_template_directory_uri() . '/assets/img/react-without-border.png'; ?>" alt="brain image">
                </div>
                <h6 class="pink">indiviual</h6>
                <p class="content-small-desc mb-3">Individual development based on:</p>
                <ul>
                  <li>Learning about their purpose and passion in life</li>
                  <li>To enable them to become better in life and self-reliant</li>
                  <li>Help them become successful entrepreneurs</li>
                  <li>Making them better in their environment, home, work and society</li>
                </ul>
              </div>
              <div class="clearfix"></div>
              <div class="col-xs-6 mb-5">
                <div class="content-icon-item">
                  <img class="responsive-image" src="<?php echo get_template_directory_uri() . '/assets/img/brain-without-border.png'; ?>" alt="brain image">
                </div>
                <h6 class="pink">colleges and universities</h6>
                <p class="content-small-desc mb-3">Providing programs for students and teachers<br> <b>For Students:</b></p>
                <ul>
                  <li>Helping students to conduct productive activities that bring in additional income to colleges</li>
                  <li>The student’s orientation and interest is more deeply studied</li>
                  <li>Doing activities that help spread and expand the college</li>
                </ul>
                <b>For Teachers:</b>
                <ul>
                  <li>Assisting teachers in the art of dealing with students</li>
                  <li>Enabling teachers to deliver the message further</li>
                  <li>Enjoy work</li>
                </ul>
              </div>
              <div class="col-xs-6 mb-5">
                <div class="content-icon-item">
                  <img class="responsive-image" src="<?php echo get_template_directory_uri() . '/assets/img/chess-without-border.png'; ?>" alt="brain image">
                </div>
                <h6 class="light-orange">institutions</h6>
                <ul>
                  <li>Professional management of human capital</li>
                  <li>Keeping pace with the growth of institutions and the trends of governments</li>
                  <li>Create employee loyalty and love the work environment</li>
                </ul>
              </div>
              <div class="clearfix"></div>
            </div>
          </div>
        </div>
        <div class="col-xs-12 col-sm-4 volunteer-group pt-4 pb-5">
          <img src="<?php echo get_template_directory_uri() . '/assets/img/inner-peace-meditation.jpg'; ?>" class="responsive-image" alt="volunteer group image">
          <div class="volunteer-group-container">
            <h3 class="text-center green my-5">volunteer group</h3>
            <p>
              Revive the spirit in volunteer teams by:
            <ul>
              <li>Laying the basics of team building</li>
              <li>Teaching them how to get financial support</li>
              <li>How to create a clear plan, system for polarization, retention, and continuity</li>
              <li>How to prepare programs that help the growth and development of the team</li>
    			  </ul>
          </div>
        </div>
      </div>
    </div>
  </article>
  <!-- Our Services -->


  <!-- meditation -->
	<?php
		$args = array(
			'post_type' => 'course',
			'posts_per_page' => 2,
			'tax_query' => array(
				array (
					'taxonomy' => 'course-cat',
					'field' => 'slug',
					'terms' => 'meditations',
				)
			),
		);
		$query = new WP_Query( $args );
		if ( $query->have_posts() ): ?>
      <article class="meditation">
        <div class="container">
          <h3 class="text-center mb-4">Let start with quick meditation for beginners</h3>
          <?php while ( $query->have_posts() ):
            $query->the_post();
            //$course = new LLMS_Course( get_the_ID() );
           $course_curriculum = bp_course_get_full_course_curriculum( get_the_ID() ); 
            $x = 0;
           foreach($course_curriculum as $lesson):
			if($lesson['type']=='unit'):
          //      echo '<pre>';
          //    print_r($lesson);
          //  echo '</pre>';
              if ( $x < 3 ):
//                var_dump( llms_is_user_enrolled( get_current_user_id(), $lesson->id ));
//	              if ( ! llms_is_user_enrolled( get_current_user_id(), $lesson->get( 'parent_course' ) ) )
//	              { return; }
                  ?>
                <div class="meditation-video <?php echo $lesson['labels'] != null ? 'freeLesson' : ''; ?> p-4 mb-3">
                  <a href="<?php echo $lesson['link']; ?>">
                    <div class="row">
                      <div class="col-xs-7">
                        <img src="<?php echo get_the_post_thumbnail_url( $lesson['id'] ) ? get_the_post_thumbnail_url( $lesson['id'] ) : get_template_directory_uri() . '/assets/img/inner-peace-meditation.jpg'; ?>" alt="">
                        <div class="d-inline-block meditation-div-title">
                          <span class="meditation-title d-block"><?php echo $lesson['title']; ?></span>
                          <span class="meditation-shadow-title d-block"><?php echo $lesson['title']; ?></span>
                        </div>
                      </div>
                      <div class="col-xs-5 text-right align-self-center">
                        <span class="meditation-play"></span>
                      </div>
                      <div class="clearfix"></div>
                    </div>
                  </a>
                </div><!--.meditation-video-->
          <?php $x++; endif; endif; endforeach; endwhile; ?>

        </div><!--.container-->
        <div class="w-50 full-sessions" style="width: 50%;">
          <a href="<?php echo get_term_link('meditations', 'course-cat') ?>">See full sessions</a>
          <span class="d-inline-block text-center"><i class="fa fa-arrow-right"></i></span>
        </div>
      </article>
		<?php endif;
		/* Restore original Post Data */
		wp_reset_postdata(); ?>
  <!-- meditation -->


  <!-- Our Courses -->
  <?php
      $args = array(
          'post_type' => 'course',
          'posts_per_page' => 6,
          'tax_query' => array(
              array (
                  'taxonomy' => 'course-cat',
                  'field' => 'slug',
                  'terms' => 'courses',
              )
          ),
      );
//      $args =	get_terms( 'course-cat', array( 'hide_empty' => 0 ));
      $query = new WP_Query( $args );
      if ( $query->have_posts() ): ?>
        <article class="our-courses">
          <div class="container">
            <h3 class="text-center mt-4 mb-5">Our Courses</h3>
            <div class="row courses-carousel">

              <?php $x = 0; while ( $query->have_posts() ):
                $query->the_post(); ?>

                <div class="col-xs-4">
                  <div class="courses-box">
                    <div class="thumbnail">
                      <img src="<?php echo get_the_post_thumbnail_url(); ?>" alt="Card image cap" style="width: 100%; max-height: 300px;">
                      <div class="caption">
                        <h3 class="text-center">
                            <a class="nav-link" href="<?php echo get_permalink(); ?>" aria-controls="now" aria-selected="true"><?php echo get_the_title(); ?></a>
                          </h3>
                      </div>
                    </div>
                  </div>

                </div>
                <?php // echo ($x == 2) ? '<div class="clearfix"></div>' : ''; ?>

              <?php $x++; endwhile; ?>
              <!-- <div class="clearfix"></div> -->
            </div><!--.courses-carousel-->    
          </div><!--.container-->
        </article>
      <?php endif;
	  /* Restore original Post Data */
	  wp_reset_postdata(); ?>
  <!-- Our Courses -->


</div>
<!-- End Home Page -->

<?php get_footer(); ?>
