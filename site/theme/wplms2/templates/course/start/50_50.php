<?php


// COURSE STATUS : 
// 0 : NOT STARTED 
// 1: STARTED 
// 2 : SUBMITTED
// > 2 : EVALUATED

// VERSION 1.8.4 NEW COURSE STATUSES
// 1 : START COURSE
// 2 : CONTINUE COURSE
// 3 : FINISH COURSE : COURSE UNDER EVALUATION
// 4 : COURSE EVALUATED

if ( !defined( 'ABSPATH' ) ) exit;

/**
* wplms_before_start_course hook.
*
* @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
* @hooked woocommerce_breadcrumb - 20
*/
do_action('wplms_before_start_course');

get_header('blank');

/**
* wplms_before_course_main_content hook.
*
* @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
* @hooked woocommerce_breadcrumb - 20
*/
do_action('wplms_before_course_main_content');

?>
<section id="content">
    <div class="container-fluid">
        <div class="row">
            <div class="course_content_panel">
                <div class="course_content_panel_content">
                    <?php

                        if ( have_posts() ) : while ( have_posts() ) : the_post();
                        /**
                        * wplms_unit_content hook.
                        *
                        * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
                        * @hooked woocommerce_breadcrumb - 20
                        */
                        do_action('wplms_unit_content');
                        endwhile;
                        endif;
                        
                        /**
                        * wplms_unit_controls hook.
                        *
                        * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
                        * @hooked woocommerce_breadcrumb - 20
                        */
                        do_action('wplms_unit_controls');
                    ?>
                </div>
            </div>
            <div id="hideshow_course_pursue_panel"><span></span></div>
            <div class="course_pursue_panel">
                <div class="course_pursue_panel_content">
                <?php 
                    /**
                    * wplms_course_action_points hook.
                    *
                    * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
                    * @hooked woocommerce_breadcrumb - 20
                    */
                    do_action('wplms_course_action_points');
                ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
    /**
    * wplms_after_course_content hook.
    *
    * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
    * @hooked woocommerce_breadcrumb - 20
    */
    do_action('wplms_after_start_course');

    get_footer( 'blank' );  