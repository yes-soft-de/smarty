<?php
/*
    This is the template for the Blog Page
    @package smartway
*/
?>
<?php get_header() ?>
<!-- This is the standard html code that we will find in every standard wordpress theme -->
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
    <div class="container">
      <div class="row">
	      <?php if ( is_active_sidebar('smart-way-sidebar' ) ): ?>
          <div class="col-md-2 col-lg-4 d-none d-md-block">
            <div class="sidebar-scroll">
              <?php get_sidebar(); ?>
            </div><!-- .sidebar-scroll -->
          </div>
        <?php endif; ?>
        <div class="col-12 col-md-10 col-lg-8 mx-auto">
          <?php
            if ( have_posts() ):
              while( have_posts() ):
                the_post();

                get_template_part( 'template-parts/single', get_post_format() );

                // Get The Default Wordpress Posts Pagination
                echo sunset_post_navigation();

                // Check If the Comments Is Open
                if ( comments_open() ):
                    comments_template();
                endif;

              endwhile;
            endif;
          ?>
        </div><!--.col-xs-12-->
      </div><!--.row-->
		</div><!--.container-->
	</main>
</div><!--#primary-->

<?php get_footer() ?>
