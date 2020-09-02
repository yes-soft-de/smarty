<?php


function assignment_comment_handle($comment, $args, $depth){
    $flag=0;
    if(current_user_can('edit_posts')){
        $flag=1;
    }

    if($flag){
    $GLOBALS['comment'] = $comment;
        extract($args, EXTR_SKIP);

        if ( 'div' == $args['style'] ) {
            $tag = 'div';
            $add_below = 'comment';
        } else {
            $tag = 'li';
            $add_below = 'div-comment';
        }
    ?>
        <<?php echo $tag ?> <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">
        <?php if ( 'div' != $args['style'] ) : ?>
        <div id="div-comment-<?php comment_ID() ?>" class="comment-body">
        <?php endif; ?>
        <div class="comment-author vcard">
        <?php if ($args['avatar_size'] != 0) echo get_avatar( $comment, $args['avatar_size'] ); ?>
        <?php printf(__('<span>'.__('Submitted by','wplms-assignments').'</span> <cite class="fn">%s</cite>'), get_comment_author_link()) ?>
        </div>
    <?php if ($comment->comment_approved == '0') : ?>
            <em class="comment-awaiting-moderation"><?php _e('Assignment awaiting moderation.','wplms-assignments') ?></em>
            <br />
    <?php endif; ?>

        <div class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>">
            <?php
                /* translators: 1: date, 2: time */
                printf( __('%1$s at %2$s','wplms-assignments'), get_comment_date(),  get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)','wplms-assignments'),'  ','' );
            ?>
        </div>

        <?php comment_text();
        
        if ( 'div' != $args['style'] ) : ?>
        </div>
        <?php endif; ?>
    <?php
    }
}


if(!function_exists('the_assignment_timer')){
  function the_assignment_timer($hours,$start=NULL){
    global $post;

      $user_id = get_current_user_id();
      $assignmenttaken=get_user_meta($user_id,$post->ID,true);

      if(!isset($hours))
            $hours=intval(get_post_meta($post->ID,'vibe_assignment_duration',true));

      if(!$hours) {$hours=1; echo "Duration not Set"; return;}
      if($hours > 9998){
          return;
      }

      $assignment_duration_parameter = apply_filters('vibe_assignment_duration_parameter',86400,$post->ID);
      $hours = $hours*$assignment_duration_parameter;
      $start=0;

      if(isset($assignmenttaken) && $assignmenttaken!=''){
          if( ($assignmenttaken + $hours*$assignment_duration_parameter) > time()){
            $start=1;
            $remaining=($assignmenttaken + $hours) - time();
          }else{
            $remaining=1;
          }
          
      }else{
          $remaining = $hours; // Converting to seconds
      } 

      $status = get_post_meta($post->ID,$user_id,true);
      if(isset($status) && $status){
        $start=0;
        $remaining=0;
      }

      $remaining = apply_filters('wplms_assignment_remaining_time',$remaining,$post->ID);
      if($remaining > 86400){

        echo '<div class="assignment_timer '.(($start)?'start':'').'" data-time="'.$remaining.'">
          <span class="timer" data-timer="'.$remaining.'"></span>
          <span class="counttime">'.floor($remaining/86400).'</span>
          <span>'.__('Time Remaining','wplms-assignments').'</span>
          <span>'.__('Days','wplms-assignments').'</span>
          </div>';
      }else{

        echo '<div class="assignment_timer '.(($start)?'start':'').'" data-time="'.$remaining.'">
          <span class="timer" data-timer="'.$remaining.'"></span>
          <span class="countdown">'.seconds_to_hoursminutes($remaining).'</span>
          <span>'.__('Time Remaining','wplms-assignments').'</span>
          <span><strong>'.__('Hours','wplms-assignments').'</strong> '.__('Minutes','wplms-assignments').'</span>
          </div>';
      }
  }
}

if(!function_exists('seconds_to_hoursminutes')){
  function seconds_to_hoursminutes($sec){
    if($sec > 3600){
        $hours = floor($sec/3600);
        $mins = floor(($sec%3600)/60);
        if($mins < 10) $mins = '0'.$mins;
        return $hours.':'.$mins;
    }else{
      $mins = $sec;
      if($mins == 0){
        return __('ENDED','wplms-assignments');
      }else{
        return '00:'.floor($mins/60);
      }
    }
  }
}

function assignment_start_button(){ // Check on Start Values
    global $post;

    if(!is_user_logged_in())
        return;

    $user_id=get_current_user_id();
    $flag=1;  
    $connected_course = get_post_meta($post->ID,'vibe_assignment_course',true);
    if(isset($connected_course) && is_numeric($connected_course)){
       $expiry=get_user_meta($user_id,$connected_course,true);
       if(isset($expiry) && is_numeric($expiry)){
            if($expiry < time())
            $flag=0; 
       }else
        $flag=0; 
    }
    if($flag){
        echo '<form method="post">
              <input type="submit" name="start_assignment" class="button primary full" value="'.__('START ASSIGNMENT','wplms-assignments').'" />';
              wp_nonce_field('assignment'.$post->ID,'security');
        echo '</form>'; 
    }    
}

function wplms_assignment_answer_posted() {
  global $wpdb;
  $user_id= get_current_user_id();
  $post_id = get_the_ID();

  $count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM {$wpdb->comments} WHERE comment_post_ID = %d and comment_approved = %d and user_id = %d",$post_id,1,$user_id));
  
  return $count;
}

function assignment_continue_button(){ // Check on Start Values
    global $post;
    if(!is_user_logged_in())
        return;

        echo '<form method="post">
              <input type="submit" name="continue_assignment" class="button primary full" value="'.__('CONTINUE ASSIGNMENT','wplms-assignments').'" />';
              wp_nonce_field('assignment'.$post->ID,'security');
        echo '</form>'; 
}

function assignment_submit_button(){
    global $post;
    if(!is_user_logged_in())
        return;
    
    $user_id = get_current_user_id();
    
    $evaluation= get_post_meta($post->ID,'vibe_assignment_evaluation',true);

    if(isset($evaluation)){

        $course = get_post_meta($post->ID,'vibe_assignment_course',true);        
        $coursestarted = get_user_meta($user_id,$course,true); // Check if Connected course is started
        if(isset($coursestarted) && $coursestarted > time()){ // Course is Still active
          if (wplms_assignment_answer_posted()){ 
            echo '<form method="post">
                  <input type="submit" name="submit_assignment" class="submit_assignment button primary" value="'.__('SUBMIT ASSIGNMENT','wplms-assignments').'" />';
                  wp_nonce_field('assignment'.$post->ID,'security');
            echo '</form>'; 
          }
        }else{
            echo '<a href="'.get_permalink($course).'" class="button primary full">'.__('INACTIVE COURSE ','wplms-assignments').'</a>';
        }
    }else{
       if (wplms_assignment_answer_posted()){
          echo '<form method="post">
              <input type="submit" name="submit_assignment" class="button primary full" value="'.__('SUBMIT ASSIGNMENT','wplms-assignments').'" />';
              wp_nonce_field('assignment'.$post->ID,'security');
          echo '</form>'; 
       }
    }
}

function assignment_results_button(){  
    echo '<a href="'.bp_loggedin_user_domain().BP_COURSE_SLUG.'/'.BP_COURSE_RESULTS_SLUG.'/?action='.get_the_ID().'" class="button primary full">'.__('CHECK RESULTS','wplms-assignments').'</a>';
}

add_action('wplms_before_single_assignment','wplms_assignment_start');
function wplms_assignment_start(){
    global $post;
    if(!isset($_POST['security']))
        return;

    if ( !isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'assignment'.$post->ID ) ) {
        wp_die(__('Security check failed !','wplms-assignments'));
       exit;
    }

    $user_id = get_current_user_id();
    $assignment_taken = get_user_meta($user_id,$post->ID,true);
    if(isset($_POST['start_assignment'])){
        
        if(add_user_meta($user_id,$post->ID,time())){
            //Record activity
       
            do_action('wplms_start_assignment',$post->ID,$user_id);
            return;
        }else
            wp_die(__('Assignment can not be re-started','wplms-assignments'));
    }
    if(isset($_POST['continue_assignment'])){ //Added Security measure, if someone renames the hidden field and submits 
        $start_time=get_user_meta($user_id,$post->ID,true);
        $time=get_post_meta($post->ID,'vibe_assignment_duration',true);
        $assignment_duration_parameter = apply_filters('vibe_assignment_duration_parameter',86400,$post->ID);
        $time_limit = intval($start_time)+ intval($time)*$assignment_duration_parameter;
        if($time_limit > time())
            return;
        else{
            wp_die(__('TIME EXPIRED, PLEASE SUBMIT THE ASSIGNMENT','wplms-assignments'));
        }

    }
    if(isset($_POST['submit_assignment'])){
        if(add_post_meta($post->ID,$user_id,0)){

          if(function_exists('messages_new_message')){
            $message = __('Assignment ','wplms-assignments').get_the_title($post->ID).__(' submitted by student ','wplms-assignments').bp_core_get_userlink($user_id);
            messages_new_message( array('sender_id' => $user_id, 'subject' => __('Assignment submitted by Student','wplms-assignments'), 'content' => $message,   'recipients' => $post->post_author ) );
          }
            
            do_action('wplms_submit_assignment',$post->ID,$user_id);

            return;

        }
        return;
    }

}

add_action('wp_ajax_clear_previous_submissions','wplms_clear_previous_submissions');
function wplms_clear_previous_submissions(){
    global $wpdb,$post;
  $user_id = get_current_user_id();

  if(isset($_POST['security']) && wp_verify_nonce($_POST['security'],'user'.$user_id)){
    $id= intval($_POST['id']);
    if(get_post_type($id) != 'wplms-assignment'){
        echo __('Invalid ID','wplms-assignments');
        die();
    }
    $rows=$wpdb->query($wpdb->prepare("UPDATE $wpdb->comments SET comment_approved='trash' WHERE comment_post_ID=%d AND user_id=%d",$id,$user_id));
    echo $rows.__(' previous submissions removed ! Reloading page ...','wplms-assignments');
  }else
    echo __('Unable to remove previous submissions','wplms-assignments');

  die();
}





add_action( 'wp_ajax_wplms_reset_assignment', 'wplms_reset_assignment' ); // RESETS QUIZ FOR USER
function wplms_reset_assignment(){

    $assignment_id = $_POST['id'];
    $user_id = $_POST['user'];

     if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_assignment') ){
        echo '<p>'.__('Security check failed !','wplms-assignments').'</p>';
        die();
    }

    if ( !isset($user_id) || !is_numeric($user_id)){
        echo '<p>'.__(' Incorrect User selected.','wplms-assignments').'</p>';
        die();
    }

    if ( !is_numeric($assignment_id) || get_post_type($assignment_id) != 'wplms-assignment'){
        echo '<p>'.__(' Incorrect Assignment','wplms-assignments').'</p>';
        die();
    }

      delete_user_meta($user_id,$assignment_id);

      delete_post_meta($assignment_id,$user_id); // Optional validates that user can retake the quiz

        global $wpdb; // DUMPING ASSIGNMENT SUBMISSION
        echo $assignment_id;
        $result=$wpdb->query($wpdb->prepare("UPDATE $wpdb->comments SET comment_approved='trash' WHERE comment_post_ID=%d AND user_id=%d",$assignment_id,$user_id));
        if($result)
            echo '<p>'.__('Assignment Reset for Selected User','wplms-assignments').'</p>';
        else
            echo '<p>'.__('Could not find Assignment for User. Contact Admin.','wplms-assignments').'</p>';

    
    do_action('wplms_assignment_reset',$assignment_id,$user_id);
    die();
}

add_action( 'wp_ajax_give_assignment_marks', 'wplms_give_assignment_marks' ); // RESETS QUIZ FOR USER
function wplms_give_assignment_marks(){
$answer_id=$_POST['id'];
$value=$_POST['aval'];
$remarks=$_POST['message'];
if(is_numeric($answer_id) && is_numeric($value)){
  update_comment_meta( $answer_id, 'marks',$value);
  $comment=get_comment($answer_id);
  if(is_object($comment))
      update_post_meta($comment->comment_post_ID,$comment->user_id,$value);

      $assignment_duration=get_post_meta($comment->comment_post_ID,'vibe_assignment_duration',true);
      $assignment_duration_parameter = apply_filters('vibe_assignment_duration_parameter',86400,$comment->comment_post_ID);
      $time = time() - ($assignment_duration*$assignment_duration_parameter);
      
      update_user_meta($comment->user_id,$comment->comment_post_ID,$time);
      
      $max_marks = get_post_meta($comment->comment_post_ID,'vibe_assignment_marks',true);
      $marks = $value;
      $message = sprintf(_x('You\'ve obtained %s our of %s in Assignment : %s Check Results %s. %s Additional Remarks from Instructor %s %s %s','wplms-assignments'),$value,$max_marks,'<a href="'.get_permalink($comment->comment_post_ID).'">'.get_the_title($comment->comment_post_ID).'</a>
      <a href="'.bp_core_get_user_domain( $comment->user_id ).'course/course-results/?action='.$comment->comment_post_ID.'">','</a>',
      '<h3>','</h3>','<br />',$remarks);
      $message_id='';
      if(function_exists('messages_new_message')){
        $message_id=messages_new_message( array('sender_id' => get_current_user_id(), 'subject' => __('Assignment results available','wplms-assignments'), 'content' => $message,   'recipients' => $comment->user_id ) );
      }
      do_action('wplms_evaluate_assignment',$comment->comment_post_ID,$marks,$comment->user_id,$max_marks,$message_id);
}
die();
}


add_action('wplms_event_after_content','wplms_unit_assignments',1,2);
add_action('wplms_after_every_unit','wplms_unit_assignments',1,2);
function wplms_unit_assignments($unit_id){
  $assignment_ids = get_post_meta($unit_id,'vibe_assignment',false);
  if(is_Array($assignment_ids) && is_array($assignment_ids[0]))
    $assignment_ids = vibe_sanitize($assignment_ids);

  
  if(isset($assignment_ids) && is_array($assignment_ids))
  foreach($assignment_ids as $assignment_id){
    if(is_numeric($assignment_id)){
      $assignment_duration_parameter = apply_filters('vibe_assignment_duration_parameter',86400,$assignment_id);
      $marks = get_post_meta($assignment_id,'vibe_assignment_marks',true);
      $duration = get_post_meta($assignment_id,'vibe_assignment_duration',true);
      if($duration >= 9999){
        $duration=__('Unlimited','wplms-assignments');
      }else{
        if(function_exists('tofriendlytime')){
          $duration=tofriendlytime($duration*$assignment_duration_parameter);
        }else{
            $duration=$duration.__(' days','wplms-assignments');
        }
      }
       echo '<h3 class="assignment_heading">'.__('ASSIGNMENT : ','wplms-assignments').'
       <a href="'.get_permalink($assignment_id).'" target="_blank" style="float: none;">'.get_the_title($assignment_id).'<i class="icon-in-alt"></i></a>
       <strong style="float:right "><span>'.__('MARKS : ','wplms-assignments').$marks.'</span>&nbsp;&nbsp;<span>'.__('DURATION : ','wplms-assignments').$duration.'</span></strong>
       </h3>';
    }
  }
}


add_action('wplms_course_manual_evaluation','wplms_assignments_manual_evaluation',1,2);

function wplms_assignments_manual_evaluation($course_id,$user_id){
  global $wpdb;
  if(!is_numeric($course_id) || !is_numeric($user_id))
    return;

  $members_assignment_marks = $wpdb->get_results( $wpdb->prepare("SELECT meta_value as marks,post_id as assignment_id FROM {$wpdb->postmeta} where meta_key=%d AND post_id IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='vibe_assignment_course' AND meta_value = %d)",$user_id,$course_id), ARRAY_A);
  if(isset($members_assignment_marks) && is_Array($members_assignment_marks) && count($members_assignment_marks))
  foreach($members_assignment_marks as $members_assignment_mark){
       $maximum_marks=get_post_meta($members_assignment_mark['assignment_id'],'vibe_assignment_marks',true);
       echo '<li>
          <strong>'.get_the_title($members_assignment_mark['assignment_id']).' <span>'.((isset($members_assignment_mark['marks']) && $members_assignment_mark['marks'] )?'<i class="icon-check"></i> '.$members_assignment_mark['marks'].__(' out of ','wplms-assignments').$maximum_marks :'<i class="icon-alarm-1"></i>'.__(' PENDING','wplms-assignments')).'</span></strong>
        </li>';
  }
 
}

add_filter('wplms_course_student_marks','wplms_assignment_student_marks',1,3);
add_filter('wplms_course_maximum_marks','wplms_assignment_maximum_marks',1,3);

function wplms_assignment_student_marks($student_marks,$course_id,$user_id){
  global $wpdb;
    $members_assignment_marks = $wpdb->get_results( $wpdb->prepare("SELECT meta_value as marks,post_id as assignment_id FROM {$wpdb->postmeta} where meta_key=%d AND post_id IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='vibe_assignment_course' AND meta_value = %d)",$user_id,$course_id), ARRAY_A);
    if(isset($members_assignment_marks) && is_array($members_assignment_marks) && count($members_assignment_marks))
    foreach($members_assignment_marks as $members_assignment_mark){
        $include_in_course = get_post_meta($members_assignment_mark['assignment_id'],'vibe_assignment_evaluation',true);
        if(vibe_validate($include_in_course)){
          $student_marks += intval($members_assignment_mark['marks']);
        }
    }
    return $student_marks;
}

function wplms_assignment_maximum_marks($maximum_marks,$course_id,$user_id){
  global $wpdb;
  $members_assignments = $wpdb->get_results($wpdb->prepare("SELECT post_id as assignment_id FROM {$wpdb->postmeta} WHERE meta_key='vibe_assignment_course' AND meta_value = %d",$course_id), ARRAY_A);

  if(isset($members_assignments) && is_array($members_assignments) && count($members_assignments)){
    foreach($members_assignments as $members_assignment){
        $include_in_course = get_post_meta($members_assignment['assignment_id'],'vibe_assignment_evaluation',true);
        if(vibe_validate($include_in_course)){
           $newmaximum_marks=get_post_meta($members_assignment['assignment_id'],'vibe_assignment_marks',true);
           if(is_numeric($newmaximum_marks)){
              $maximum_marks += $newmaximum_marks;
           }
        }
    }
  }
  return $maximum_marks;
}

add_filter('wplms_course_stats_list','wplms_assignments_stats');
function wplms_assignments_stats($list){
  $list['stats_student_assignment_marks']= __('Assignment scores','wplms-assignments');
  return $list;
}

add_action('wplms_course_stats_process','wplms_assignment_student_scores',10,6);
function wplms_assignment_student_scores(&$csv_title, &$csv,&$i,&$course_id,&$user_id,&$field){
  if($field != 'stats_student_assignment_marks')
    return;

    
    $assignments = wplms_course_get_course_assignments($course_id);
    if(is_array($assignments) && count($assignments)){
       foreach($assignments as $assignment){
          $title=get_the_title($assignment->post_id);
          if(!in_array($title,$csv_title))
            $csv_title[$i]=$title; 
          
          $marks=get_post_meta($assignment->post_id,$user_id,true);
          if(!isset($marks) || !$marks)
            $marks = 'N.A';

          $csv[$i][] = $marks;
          $i++;
       }
    }
}

// Also used to check if assignments is active
function wplms_course_get_course_assignments($course_id){
  $assignments = WPLMS_Assignments::init();
  return $assignments->get_course_assignments($course_id);
}