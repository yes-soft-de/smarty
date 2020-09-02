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
$course_id = $_POST['course_id'];
$user_id  = get_current_user_id();
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
    <div class="upload_course_content_panel container-fluid">
        <div class="upload_course_content_panel_content row">
            <div class="upload_course_content_header">
                <div class="course_packages_menu_before_title">
                    <p class="burger_menu_wrapper">
                        <strong class="burger_menu">
                            <span></span>
                        </strong>
                    </p>
                <?php 
                    do_action('course_packages_menu_before_title');
                ?>
                </div>
                <div class="course_packages_title">
                    <h1 class="center"><?php echo get_the_title($course_id ) ;?></h1>
                </div>
                <div class="course_packages_menu_after_title">

                    <strong class="packaged_up_down">
                        <span></span>
                    </strong>
                <?php 
                    do_action('course_packages_menu_after_title');
                ?>
                </div>
          </div>
          <div class="upload_course_content">

            <?php 
            $package = get_post_meta($course_id,'vibe_course_package',true);
            echo do_shortcode('[iframe]'.$package['path'].'[/iframe]')   ?>
          </div>
        </div>
    </div>
            
</section>
<div class="course_packages_menu">
    <div class="course_pursue_panel_content">
        <div class="more_course">
            <a href="<?php echo get_permalink($course_id); ?>" class="unit_button full button"><?php _e('BACK TO COURSE','vibe'); ?></a>
            <form action="<?php echo get_permalink($course_id); ?>" method="post">
            <?php
            $finishbit=bp_course_get_user_course_status($user_id,$course_id);
            if(is_numeric($finishbit)){
                if($finishbit < 4){
                    $comment_status = get_post_field('comment_status',$course_id);
                    if($comment_status == 'open'){
                        echo '<input type="submit" name="review_course" class="review_course unit_button full button" value="'. __('REVIEW COURSE ','vibe').'" />';
                    }
                    echo '<input type="submit" name="submit_course" class="review_course unit_button full button" value="'. __('FINISH COURSE ','vibe').'" />';
                }
            }
            ?>  
            <?php wp_nonce_field($course_id,'review'); ?>
            </form>
        </div>
    </div>
</div>
<?php
    /**
    * wplms_after_course_content hook.

    */
    do_action('wplms_after_start_course');

    get_footer( 'blank' );  