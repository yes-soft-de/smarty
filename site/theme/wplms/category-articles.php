<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	get_header(); ?>

<div class="article-page">

  <div class="article-header">
    <div class="container">
      <h1><?php echo single_cat_title(); ?></h1>
      <p><?php echo category_description(); ?></p>
    </div>
  </div>

  <div class="container">
    <?php if ( have_posts() ): while ( have_posts() ): the_post(); ?>
      <div class="sub-article">
        <div class="row">
          <div class="col-sm-4 col-xs-12">
            <a href="<?php echo get_the_permalink(); ?>" class="zoom-img">
              <img src="<?php echo (has_post_thumbnail()) ? wp_get_attachment_url( get_post_thumbnail_id( get_the_ID() ) ) : get_template_directory_uri() . '/assets/images/inner-peace-meditation.jpg'; ?>" class="img-responsive">
            </a>
          </div>
          <div class="col-sm-8 col-xs-12">
            <h2>
              <a href="<?php echo get_the_permalink(); ?>">
                <?php echo get_the_title(); ?>
              </a>
            </h2>
            <span class="date"><?php the_time('F j, Y'); ?></span>
            <div class="content">
              <?php echo get_the_excerpt(); ?>
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
            <a href="<?php echo get_the_permalink(); ?>" class="read-more">Learn More</a>
          </div>
        </div><!--.row-->
      </div><!--.sub-article-->
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-xs-12 col-sm-8 col-md-6 mx-auto">
        <div class="alert alert-light text-center">There Is Not Any Posts To Show Yet.</div>
      </div>
    <?php endif; ?>
    <?php pagination(); ?>

<!--    <div class="sub-article">-->
<!--      <div class="row">-->
<!--        <div class="col-sm-4 col-xs-12">-->
<!--          <a href="#" class="zoom-img">-->
<!--            <img src="images/inner-peace-meditation.jpg" class="img-responsive">-->
<!--          </a>-->
<!--        </div>-->
<!--        <div class="col-sm-8 col-xs-12">-->
<!--          <h2>-->
<!--            <a href="#">-->
<!--              Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua-->
<!--            </a>-->
<!--          </h2>-->
<!--          <span class="date">31-3-2020</span>-->
<!--          <div class="content">-->
<!--            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua-->
<!--            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua-->
<!--            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua-->
<!---->
<!--            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua-->
<!--            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua-->
<!--            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua-->
<!--          </div>-->
<!--          <div class="share-area">-->
<!--            <span>Share to</span>-->
<!--            <ul>-->
<!--              <li class="facebook">-->
<!--                <a href="#">-->
<!--                  <i class="fa fa-facebook" aria-hidden="true"></i>-->
<!--                </a>-->
<!--              </li>-->
<!--              <li class="twitter">-->
<!--                <a href="#">-->
<!--                  <i class="fa fa-twitter" aria-hidden="true"></i>-->
<!--                </a>-->
<!--              </li>-->
<!--              <li class="whatsapp">-->
<!--                <a href="#">-->
<!--                  <i class="fa fa-whatsapp" aria-hidden="true"></i>-->
<!--                </a>-->
<!--              </li>-->
<!--            </ul>-->
<!--          </div>-->
<!--          <a href="#" class="read-more">Learn More</a>-->
<!--        </div>-->
<!--      </div>-->
<!--    </div>-->

  </div><!--.container-->

</div><!--article-page-->

<?php get_footer(); ?>
