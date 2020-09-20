<?php
if ( !defined( 'ABSPATH' ) ) exit;

do_action('wplms_before_single_assignment');
get_header(vibe_get_header());
if ( have_posts() ) : while ( have_posts() ) : the_post();

$user_id = get_current_user_id();
$marks=get_post_meta($post->ID,'vibe_assignment_marks',true);
$course=get_post_meta($post->ID,'vibe_assignment_course',true);

$time=get_post_meta($post->ID,'vibe_assignment_duration',true);
$evaluation=get_post_meta($post->ID,'vibe_assignment_evaluation',true);
$assignment_submission_type=get_post_meta($post->ID,'vibe_assignment_submission_type',true);


if(is_user_logged_in()){
    $assignment_taken = get_user_meta($user_id,$post->ID,true);
    $assignment_finished = get_post_meta($post->ID,$user_id,true);
}
$flag=0;
if(isset($assignment_taken) && $assignment_taken !=''){
    $assignment_duration_parameter = apply_filters('vibe_assignment_duration_parameter',86400);
    $timelimit = $assignment_taken + $time*$assignment_duration_parameter;
    if($timelimit > time())
        $flag=1;
    else
        $flag=3;
}

if(isset($assignment_finished) && is_numeric($assignment_finished) && $assignment_finished>0)
    $flag=2;


if($user_id == $post->post_author || current_user_can('manage_options'))
    $flag=1;
?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
    <div class="container">
        <div class="row">
            <div class="col-md-9 col-sm-8">
                <div class="pagetitle">
                    <span><?php echo '<a href="'.get_permalink($course).'">'.get_the_title($course).'</a>'; ?></span>
                    <h1>
                        <?php the_title(); ?>
                    </h1>
                    <?php the_sub_title(); ?>
                </div>
            </div>
             <div class="col-md-3 col-sm-4">
                <?php
                $course_id = get_post_meta(get_the_ID(),'vibe_assignment_course',true);
                    if(isset($course_id) && is_numeric($course_id)){
                        if(is_user_logged_in()){
                            $user_id = get_current_user_id();
                            if(wplms_user_course_active_check($user_id,$course_id)){
                                $take_id = vibe_get_option('take_course_page');
                                if(function_exists('icl_object_id')){
                                    $take_id = icl_object_id($take_id,'page',ICL_LANGUAGE_CODE);
                                }
                                $take_course_page=get_permalink( $take_id);
                                echo '<form action="'.$take_course_page.'" method="post">';
                                echo '<input type="submit" class="button full create-group-button" value="'.__('Back to Course','vibe').'">';
                                wp_nonce_field('continue_course'.$user_id,'continue_course'); 
                                echo  '<input type="hidden" name="course_id" value="'.$course_id.'" />';
                                echo  '</form>';
                            }else{
                                echo '<a href="'.get_permalink($course_id).'" class="button create-group-button full">'.__('Back to Course','vibe').'</a>';
                            }
                        }else{
                            echo '<a href="'.get_permalink($course_id).'" class="button create-group-button full">'.__('Back to Course','vibe').'</a>';
                        }
                        
                         
                    }else {
                   vibe_breadcrumbs(); 
                }    
                ?>
            </div>
        </div>
    </div>
</section>
<section id="content">
    <div class="container">
        <div class="row">
            <div class="col-md-9 col-sm-8">
                    <div class="content">
                        <div id="assignment" class="main_content">
                            <?php do_action('wplms_assignment_before_content'); 
                            ?>
                            <?php
                            switch($flag){
                                case 0:
                                the_excerpt();
                                assignment_start_button();
                                break;
                                case 1:
                                the_content(); 
                                if(isset($assignment_submission_type)){
                                    switch($assignment_submission_type){
                                        case 'upload': comments_template('/assignment-upload.php',true); 
                                        break;
                                        case 'textarea':  comments_template('/assignment-textarea.php',true); 
                                        break;
                                        
                                    }
                                 }else{
                                    comments_template('/assignment-upload.php',true); 
                                 }
                                break;
                                case 2:
                                the_content(); 
                                assignment_results_button();
                                break;
                                case 3:
                                the_content(); 
                                assignment_results_button();
                                break;
                            }
                            if(current_user_can('manage_options') || ($user_id == $post->post_author)){
                                echo '<h3 class="heading">'.__('All Assignment Submissions','vibe').'</h3>';?>
                                <div class="assignment_submissionlist"> 
                                <?php 
                                  comments_template();
                                ?>  
                                </div> <?php
                            }
                            ?>
                        </div>
                        <?php do_action('wplms_assignment_after_content'); ?> 
                    </div>
                <?php
                
                endwhile;
                endif;

                ?>
            </div>
            <div class="col-md-3 col-sm-4">
                <div class="assignment_details">
                <?php
                    if(isset($marks) && is_numeric($marks)){
                        echo '<div class="assignment_marks">';
                        echo '<h2>'.$marks.'<span>'.__('Maximum Marks','vibe').'</span></h2>';
                        echo '</div>';
                    }
                    if(isset($time) && is_numeric($time)){
                        echo '<div class="assignment_duration">';
                        the_assignment_timer($time);
                        echo '</div>';
                    }
                ?>
                </div>
                <?php       
                    if(isset($_GET['edit']) || isset($wp_query->query_vars['edit']) ){
                        do_action('wplms_front_end_assignment_controls');    
                    }else{
                        
                    $sidebar = apply_filters('wplms_sidebar','coursesidebar',get_the_ID());
                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) {}
                   }
                   ?>
            </div>
        </div>
    </div>
</section>
<?php
    do_action('wplms_after_assignment');
?>
<?php
get_footer(vibe_get_footer());
