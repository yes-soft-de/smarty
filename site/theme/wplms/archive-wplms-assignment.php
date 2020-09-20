<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(vibe_get_header());
?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
	<div class="<?php echo vibe_get_container(); ?>">
		<div class="row">
            <div class="col-md-12">
                <div class="pagetitle">
                    <?php vibe_breadcrumbs(); ?>  
                    <h1><?php

                    if(is_month()){
                        single_month_title(' ');
                    }elseif(is_year()){
                        echo get_the_time('Y');
                    }else if(is_tag()){
                         single_tag_title();
                    }else{
                        post_type_archive_title();
                    }
                     ?></h1>
                    <h5><?php echo term_description(); ?></h5>
                </div>
            </div>
        </div>
	</div>
</section>
<section id="content">
	<div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
    		<div class="col-md-12">
    			<div class="content">
                    <div class="row">
    				<?php
                        
                    if ( have_posts() ) : 
                        while ( have_posts() ) : the_post();
                        global $post;
                        $return = '<div class="col-md-4 clear3"><div class="assignmentblock"><h4 class="block_title"><a href="'.get_permalink($post->ID).'" title="'.$post->post_title.'">'.$post->post_title.'</a></h4>';

                        $return .= sprintf(_x('By %s','name of assignment author','vibe'),'<a href="'.bp_core_get_user_domain($post->post_author).'" class="assignment_instructor" 
                            title="'.__('Assignment Instructor','vibe').'">'.bp_core_get_user_displayname($post->post_author ).'</a>');
                        $return .= '<div class="assignment_details">';
                        $return .='<ul>';
                        $course_id = get_post_meta($post->ID,'vibe_assignment_course',true);
                        if(!empty($course_id))
                        $return .='<li><strong>'.__('Course','vibe').'</strong><span><a href="'.get_permalink($course_id).'">'.get_the_title($course_id).'</a></span></li> ';    

                        $total_marks = $my_marks = 0;
                        if(is_user_logged_in()){
                            $user_id = get_current_user_id();
                            $my_marks = get_post_meta($post->ID,$user_id,true);
                        }
                        if(!empty($my_marks)){
                            $return .='<li><strong>'.__('My Marks','vibe').'</strong><span>'.$my_marks.'</span></li> ';    
                        }
                        $total_marks = get_post_meta($post->ID,'vibe_assignment_marks',true);
                        $return .='<li><strong>'.__('Total Marks','vibe').'</strong><span>'.$total_marks.'</span></li>';
                        if(!empty($my_marks)){
                            $return .='<li><a href="'.bp_loggedin_user_domain().BP_COURSE_SLUG.'/'.BP_COURSE_RESULTS_SLUG.'/?actions='.$post->ID.'" class="link">'.__('Check results','vibe').'</a></li>';
                        }

                        if($my_marks == 0){
                            $return .='<li><strong class="label label-success">'.__('UNDER EVALUATION','vibe').'</strong></li> ';
                        }
                        $return .= '</ul></div></div></div>';
                        echo $return;
                        endwhile;
                    else:
                        echo '<div class="message error">'.__('You\'ve not attempted any assignments yet !','vibe').'</div>';                    
                    endif;
                        pagination();
                    ?>
                    </div>
    			</div>
    		</div>
        </div>
	</div>
</section>

<?php
get_footer(vibe_get_footer());
?>