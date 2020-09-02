<?php


do_action('wplms_before_quiz');
get_header(vibe_get_header());
$user_id = get_current_user_id();
do_action('wplms_before_quiz_begining',get_the_ID());
$quiztaken=get_user_meta($user_id,get_the_ID(),true);
if ( have_posts() ) : while ( have_posts() ) : the_post();

?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-9 col-sm-9">
                <div class="pagetitle">
                    <h1><?php the_title(); ?></h1>
                    <?php the_sub_title(); ?>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="quiz_next">
        <?php
            if(is_user_logged_in()){
                if(isset($quiztaken) && $quiztaken){
                    if($quiztaken > time()){
                        echo apply_filters('wplms_continue_quiz_button','<a class="button create-group-button full begin_quiz continue" data-quiz="'.get_the_ID().'"> '.__('Continue Quiz','vibe').'</a>',get_the_ID());
                            wp_nonce_field('start_quiz','start_quiz');
                    }else{ 

                        $quiz_unfinished_check=get_post_meta(get_the_ID(),$user_id,true);
                        if(!isset($quiz_unfinished_check) || $quiz_unfinished_check ==''){
                            add_post_meta(get_the_ID(),$user_id,0);
                        }
                        
                        $quiz_course = get_post_meta(get_the_ID(),'vibe_quiz_course',true);

                        if(isset($quiz_course) && is_numeric($quiz_course) && $quiz_course && wplms_user_course_active_check($user_id,$quiz_course)){
                            echo '<a href="'.bp_loggedin_user_domain().BP_COURSE_SLUG.'/'.BP_COURSE_RESULTS_SLUG.'/?action='.get_the_ID().'" class="button full"> '.__('Check Quiz Results','vibe').'</a>';
                            $take_course_page=get_permalink(vibe_get_option('take_course_page'));
                            echo '<form action="'.$take_course_page.'" method="post">';
                                echo '<input type="submit" class="button full" value="'.__('Back to Course','vibe').'">';
                                wp_nonce_field('continue_course'.$user_id,'continue_course'); 
                                echo  '<input type="hidden" name="course_id" value="'.$quiz_course.'" />';
                            echo  '</form>'; 
                            //echo '<a href="'.get_permalink($quiz_course).'" class="button full"> '.__('Back to Course','vibe').'</a>';
                        }else{
                            echo '<a href="'.bp_loggedin_user_domain().'course/course-results/?action='.get_the_ID().'" class="button create-group-button full"> '.__('Check Quiz Results','vibe').'</a>';
                        }
                    }
                }else{
                    echo apply_filters('wplms_start_quiz_button','<a class="button create-group-button full begin_quiz" data-quiz="'.get_the_ID().'"> '.__('Start Quiz','vibe').'</a>',get_the_ID());
                     wp_nonce_field('start_quiz','start_quiz');
                }
            }else{
                echo '<a class="button create-group-button full"> '.__('Take a Course to Start the Quiz','vibe').'</a>';
                     
            }
        ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="content">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-9 col-md-push-3 ">
                <div class="content">
                    <?php
                        the_quiz();
                        do_action('wplms_front_end_quiz_controls');
                    ?>
                </div>
            </div>
            <div class="col-md-3 quiz-sidebar col-md-pull-9">
                <div class="quiz_details">
                 <?php
                    the_quiz_timer();
                    the_quiz_timeline();
                ?>
                </div>
                <?php
                do_action('wplms_front_end_quiz_meta_controls');
                ?>
            </div>
             <?php
                endwhile;
                endif;
                ?>
        </div>
    </div>
</section>
<?php
get_footer( vibe_get_footer() ); 
?>