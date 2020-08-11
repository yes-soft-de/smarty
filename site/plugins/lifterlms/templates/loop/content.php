<?php
/**
 * The Template for displaying all single courses.
 *
 * @package     LifterLMS/Templates
 *
 * @since       1.0.0
 * @version     3.14.0
 */

defined( 'ABSPATH' ) || exit;

?>
<?php $request_uri = explode('/', $_SERVER['REQUEST_URI']);
	if ( in_array('courses', $request_uri, false) ): ?>
  <div class="col-6 mx-auto mb-4">
    <li <?php post_class( 'llms-loop-item mx-auto' ); ?>>
      <div class="llms-loop-item-content">
		  <?php
			  /**
			   * Hook: lifterlms_before_loop_item
			   *
			   * @hooked lifterlms_loop_featured_video - 8
			   * @hooked lifterlms_loop_link_start - 10
			   */
			  do_action( 'lifterlms_before_loop_item' );
		  ?>

		  <?php
			  /**
			   * Hook: lifterlms_before_loop_item_title
			   *
			   * @hooked lifterlms_template_loop_thumbnail - 10
			   * @hooked lifterlms_template_loop_progress - 15
			   */
			  do_action( 'lifterlms_before_loop_item_title' );
		  ?>

        <h4 class="llms-loop-title"><?php the_title(); ?></h4>

        <footer class="llms-loop-item-footer">
			<?php
				/**
				 * Hook: lifterlms_after_loop_item_title
				 *
				 * @hooked lifterlms_template_loop_author - 10
				 * @hooked lifterlms_template_loop_length - 15
				 * @hooked lifterlms_template_loop_difficulty - 20
				 *
				 * On Student Dashboard & "Mine" Courses Shortcode
				 * @hooked lifterlms_template_loop_enroll_status - 25
				 * @hooked lifterlms_template_loop_enroll_date - 30
				 */
				do_action( 'lifterlms_after_loop_item_title' );
			?>
        </footer>

		  <?php
			  /**
			   * Hook: lifterlms_after_loop_item
			   *
			   * @hooked lifterlms_loop_link_end - 5
			   */
			  do_action( 'lifterlms_after_loop_item' );
		  ?>

      </div><!-- .llms-loop-item-content -->
    </li><!-- .llms-loop-item -->
  </div>

<?php //elseif ( in_array('meditations', $request_uri, false) ): ?>

    <!--  <div id="llms-loop-item-meditations" class="col-12 mx-auto" data-class="--><?php //post_class( 'llms-loop-item mx-auto meditations p-4 my-3' ); ?><!--">-->
<!--    -->
<!--    <li --><?php //post_class( 'llms-loop-item mx-auto meditations p-4 my-3' ); ?>
<!--      <div class="llms-loop-item-content m-0">-->
<!--        <div class="row">-->
<!--          <div class="col-7">-->
<!--	          --><?php
//		          /**
//		           * Hook: lifterlms_before_loop_item
//		           *
//		           * @hooked lifterlms_loop_featured_video - 8
//		           * @hooked lifterlms_loop_link_start - 10
//		           */
//		          do_action( 'lifterlms_before_loop_item' );
//	          ?>
<!---->
<!--	          --><?php
//		          /**
//		           * Hook: lifterlms_before_loop_item_title
//		           *
//		           * @hooked lifterlms_template_loop_thumbnail - 10
//		           * @hooked lifterlms_template_loop_progress - 15
//		           */
//		          do_action( 'lifterlms_before_loop_item_title' );
//	          ?>
<!---->
<!--            <div class="d-inline-block meditation-div-title">-->
<!--              <span class="meditation-title d-block">--><?php //echo get_the_title(); ?><!--</span>-->
<!--              <span class="meditation-shadow-title d-block">--><?php //echo get_the_title(); ?><!--</span>-->
<!--            </div>-->
<!---->
<!--            <footer class="llms-loop-item-footer d-none">-->
<!--	            --><?php
//		            /**
//		             * Hook: lifterlms_after_loop_item_title
//		             *
//		             * @hooked lifterlms_template_loop_author - 10
//		             * @hooked lifterlms_template_loop_length - 15
//		             * @hooked lifterlms_template_loop_difficulty - 20
//		             *
//		             * On Student Dashboard & "Mine" Courses Shortcode
//		             * @hooked lifterlms_template_loop_enroll_status - 25
//		             * @hooked lifterlms_template_loop_enroll_date - 30
//		             */
//		            do_action( 'lifterlms_after_loop_item_title' );
//	            ?>
<!--            </footer>-->
<!---->
<!--	          --><?php
//		          /**
//		           * Hook: lifterlms_after_loop_item
//		           *
//		           * @hooked lifterlms_loop_link_end - 5
//		           */
//		          do_action( 'lifterlms_after_loop_item' );
//	          ?>
<!--          </div>-->
<!--          <div class="col-5 text-right align-self-center">-->
<!--            <span class="meditation-play"></span>-->
<!--          </div>-->
<!--        </div>-->
<!---->
<!---->
<!--      </div>.llms-loop-item-content-->
<!--    </li>.llms-loop-item-->
<!---->
<!--  </div>-->

<?php else: ?>
    <div class="col-6 mx-auto mb-4">
      <li <?php post_class( 'llms-loop-item mx-auto' ); ?>>
        <div class="llms-loop-item-content">
			<?php
				/**
				 * Hook: lifterlms_before_loop_item
				 *
				 * @hooked lifterlms_loop_featured_video - 8
				 * @hooked lifterlms_loop_link_start - 10
				 */
				do_action( 'lifterlms_before_loop_item' );
			?>

			<?php
				/**
				 * Hook: lifterlms_before_loop_item_title
				 *
				 * @hooked lifterlms_template_loop_thumbnail - 10
				 * @hooked lifterlms_template_loop_progress - 15
				 */
				do_action( 'lifterlms_before_loop_item_title' );
			?>

          <h4 class="llms-loop-title"><?php  the_title(); ?>  <?php echo $plan->get( 'id' ); ?></h4>

          <footer class="llms-loop-item-footer">
			  <?php
				  /**
				   * Hook: lifterlms_after_loop_item_title
				   *
				   * @hooked lifterlms_template_loop_author - 10
				   * @hooked lifterlms_template_loop_length - 15
				   * @hooked lifterlms_template_loop_difficulty - 20
				   *
				   * On Student Dashboard & "Mine" Courses Shortcode
				   * @hooked lifterlms_template_loop_enroll_status - 25
				   * @hooked lifterlms_template_loop_enroll_date - 30
				   */
				  do_action( 'lifterlms_after_loop_item_title' );
			  ?>
          </footer>

			<?php
				/**
				 * Hook: lifterlms_after_loop_item
				 *
				 * @hooked lifterlms_loop_link_end - 5
				 */
				do_action( 'lifterlms_after_loop_item' );
			?>

        </div><!-- .llms-loop-item-content -->
      </li><!-- .llms-loop-item -->
    </div>
<?php endif; ?>
