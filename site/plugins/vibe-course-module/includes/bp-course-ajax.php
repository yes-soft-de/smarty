<?php
/**
 * Ajax functions for Course Module
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Vibe Course Module
 * @version     2.0
 */

 if ( ! defined( 'ABSPATH' ) ) exit;

class BP_Course_Ajax{

    public static $instance;
    var $schedule;
    public static function init(){

      if ( is_null( self::$instance ) )
          self::$instance = new BP_Course_Ajax();

      return self::$instance;
    }

    private function __construct(){

      // Unit functions       
      add_action('wp_ajax_complete_unit',array($this,'wplms_complete_unit'));
      add_action('wp_ajax_reset_question_answer', array($this,'reset_question_answer'));
      add_action('wp_ajax_load_unit_comments',array($this,'wplms_load_unit_comments'));
      add_action('wp_ajax_add_unit_comment',array($this,'wplms_add_unit_comment'));
      add_action('wp_ajax_unit_traverse', array($this,'unit_traverse'));
      add_action('wp_ajax_nopriv_unit_traverse', array($this,'unit_traverse' ));
      add_action('wp_ajax_instructor_uncomplete_unit',array($this,'instructor_uncomplete_unit'));
      add_action('wp_ajax_instructor_complete_unit',array($this,'instructor_complete_unit'));

      //Quiz Functions
      add_action('wp_ajax_retake_inquiz',array($this,'wplms_retake_inquiz'));
      add_action('wp_ajax_in_start_quiz', array($this,'incourse_start_quiz'));
      add_action('wp_ajax_continue_quiz', array($this,'continue_quiz')); // Only for LoggedIn Users
      add_action('wp_ajax_quiz_question', array($this,'quiz_question')); // Only for LoggedIn Users
      add_action('wp_ajax_in_submit_quiz',array($this,'incourse_submit_quiz'));
      add_action('wp_ajax_submit_quiz', array($this,'submit_quiz'));
      add_action('wp_ajax_begin_quiz', array($this,'begin_quiz'));
      add_action('wp_ajax_check_unanswered_questions',array($this,'bp_course_check_unanswered_questions'));
      add_action('wp_ajax_save_question_marked_answer',array($this,'save_question_marked_answer'));

      // Evaluation functions
      add_action('wp_ajax_check_user_quiz_results',array($this,'wplms_check_user_quiz_results'));
      add_action( 'wp_ajax_remove_user_course', array($this,'remove_user_course')); // RESETS QUIZ FOR USER
      add_action( 'wp_ajax_reset_course_user', array($this,'reset_course_user')); // RESETS COURSE FOR USER
      add_action( 'wp_ajax_reset_quiz', array($this,'reset_quiz')); // RESETS QUIZ FOR USER
      add_action( 'wp_ajax_give_marks', array($this,'give_marks')); // RESETS QUIZ FOR USER
      add_action( 'wp_ajax_complete_course_marks', array($this,'complete_course_marks')); // COURSE MARKS FOR USER
      add_action( 'wp_ajax_save_quiz_marks', array($this,'save_quiz_marks')); // RESETS QUIZ FOR USER
      add_action( 'wp_ajax_evaluate_course', array($this,'wplms_evaluate_course' )); // RESETS QUIZ FOR USER
      add_action( 'wp_ajax_evaluate_quiz', array($this,'evaluate_quiz' )); // EVALAUTES QUIZ FOR USER : MANUAL EVALUATION

      //Bulk actions  
      add_Action('wp_ajax_search_course_members',array($this,'search_course_members'));
      add_Action('wp_ajax_nopriv_search_course_members',array($this,'search_course_members'));

      add_action( 'wp_ajax_send_bulk_message', array($this,'send_bulk_message' ));
      add_action('wp_ajax_select_users',array($this,'select_users'));
      add_action( 'wp_ajax_add_bulk_students', array($this,'add_bulk_students' ));
      add_action( 'wp_ajax_assign_badge_certificates', array($this,'assign_badge_certificates' ));
      add_action( 'wp_ajax_extend_course_subscription', array($this,'wplms_extend_course_subscription' ));
      add_action( 'wp_ajax_change_course_status', array($this,'wplms_change_course_status' ));
      
      //Course and Quiz Stats
      add_action( 'wp_ajax_calculate_stats_course', array($this,'calculate_stats_course')); 
      add_action( 'wp_ajax_course_stats_user', array($this,'course_stats_user' )); 
      add_action('wp_ajax_download_stats',array($this,'wplms_course_download_stats'));
      add_action('wp_ajax_download_mod_stats',array($this,'wplms_download_mod_stats'));
      add_action('wp_ajax_load_stats',array($this,'wplms_load_cpt_stats'));
      add_action('wp_ajax_nopriv_load_stats',array($this,'wplms_load_cpt_stats'));

      add_action('wp_ajax_cpt_stats_graph',array($this,'wplms_cpt_stats_graph'));
      add_action('wp_ajax_nopriv_cpt_stats_graph',array($this,'wplms_cpt_stats_graph'));
      add_action('wp_ajax_load_more_stats',array($this,'wplms_load_more_cpt_stats'));
      add_action('wp_ajax_nopriv_load_more_cpt_stats',array($this,'wplms_load_more_cpt_stats'));
      
      
      // Misc
      add_action('wp_ajax_messages_star',array($this,'bp_course_messages_star'));
      add_action('wp_ajax_fetch_quiz_submissions',array($this,'fetch_quiz_submissions'));
      add_action('wp_ajax_fetch_course_submissions',array($this,'fetch_course_submissions'));
      add_action('wp_ajax_nopriv_get_unit_content',array($this,'get_unit_content'));
      add_action('wp_ajax_get_unit_content',array($this,'get_unit_content'));
      add_action('wp_ajax_apply_for_course',array($this,'apply_for_course'));
      add_action('wp_ajax_manage_user_application',array($this,'manage_user_application'));
      // Results pagination
      add_action('wp_ajax_get_results_page',array($this,'get_results_page'));
      
      // Check Question answer
      add_action('wp_ajax_check_question_answer',array($this,'check_question_answer'));
    }

    function get_results_page(){
      $user_id = get_current_user_id();
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_user_logged_in()){
         _e('Security check Failed. Contact Administrator.','vibe');
         die();
      }
      $type = $_POST['type'];
      $paged = $_POST['page'];
      $per_page = 5;
      if(function_exists('vibe_get_option')){
        $per_page = vibe_get_option('loop_number');  
      }
      $the_quiz=new WP_QUERY(array(
        'post_type' => $type,
        'posts_per_page'=>$per_page,
        'paged' => $paged,
        'meta_query'=>array(
          array(
            'key' => $user_id,
            'compare' => 'EXISTS'
            ),
          ),
        ));

      if($the_quiz->have_posts()){
        $bp_actions = BP_Course_Action::init();
        if(class_exists('WPLMS_Assignments')){
          $wplms_assignments = WPLMS_Assignments::init();
        }
        ?><ul class="quiz_results <?php echo $type;?>_results"><?php
        while($the_quiz->have_posts()) : $the_quiz->the_post();
        global $post;
          if(!empty($type) && $type == 'quiz'){
            $bp_actions->get_quiz_item($post,$user_id);
          }elseif(!empty($type) && $type == 'wplms-assignment' && class_exists('WPLMS_Assignments') && !empty($wplms_assignments)){
            //for assignments in assignments plugin
            $wplms_assignments->results_item($post,$user_id);
          }
          
        endwhile;
        ?></ul><?php
        if($the_quiz->max_num_pages>1){?>
            <div class="pagination no-ajax">
            <div class="pag-count">
              <?php echo sprintf(__('Viewing %d out of %d','vibe'),$paged,$the_quiz->max_num_pages) ?>
            </div>
            <div class="pagination-links">
              <?php
                for($i=1;$i<=$the_quiz->max_num_pages;$i++){
                  if(($paged==$i)){
                    ?>
                    <span class="page-numbers current"><?php echo $i;?></span>
                    <?php
                  }else{
                    ?>
                    <a class="page-numbers get_results_pagination" data-type="<?php echo $type; ?>"><?php echo $i;?></a>
                    <?php
                  }
                }
                wp_nonce_field('security','security');
                ?>
            </div>
          </div>
        <?php }
      }
      die();
    }

    function wplms_complete_unit(){
      $unit_id = $_POST['id'];
      $course_id = $_POST['course_id'];
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_user_logged_in()){
         _e('Security check Failed. Contact Administrator.','vibe');
         die();
      }

      
      $user_id = get_current_user_id();

      
      $flag = apply_filters('wplms_allow_complete_unit',1,$user_id,$course_id,$unit_id);
      
      // Check if user has taken the course
      if(bp_course_is_member($course_id,$user_id)){
        
        $stop_progress = apply_filters('bp_course_stop_course_progress',true,$course_id);
      
        if(!empty($stop_progress)){ // Enable Next unit access
          if($flag){
            bp_course_update_user_unit_completion_time($user_id,$unit_id,$course_id,time());
          }
          
          $curriculum=bp_course_get_curriculum_units($course_id);
          $key = array_search($unit_id,$curriculum);

          if($key <=(count($curriculum)-1) ){  // Check if not the last unit
            $key++;
            echo $curriculum[$key];
          }

        }else{
            $curriculum=bp_course_get_curriculum_units($course_id);
            $key = array_search($unit_id,$curriculum);
            $key++;
            if($flag){
              bp_course_update_user_unit_completion_time($user_id,$unit_id,$course_id,time());
            }
        }
        
        $c=(count($curriculum)?count($curriculum):1);
        $course_progress = $key/$c;
        do_action('wplms_unit_complete',$unit_id,$course_progress,$course_id,$user_id );
      }
      die();
    }

    /* == Instructors can mark unit complete/uncomplete from Course Admin area == */

    function instructor_uncomplete_unit(){
        if (!is_numeric($_POST['course_id']) || !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$_POST['course_id'])  || !is_numeric($_POST['user_id']) || !is_numeric($_POST['id']) || !current_user_can('edit_posts')){
            echo '<div id="message" class="info notice"><p>'.__('Security check failed !','vibe').'</p></div>';
            die();
        }

        if(bp_course_is_member($_POST['course_id'],$_POST['user_id'])){
            
            bp_course_reset_unit($_POST['user_id'],$_POST['id'],$_POST['course_id']);

          $curriculum=bp_course_get_curriculum_units($_POST['course_id']);
          
          $per = round((100/count($curriculum)),2);
          echo $per;
          $progress = bp_course_get_user_progress($_POST['user_id'],$_POST['course_id']);
          echo $progress;
          $new_progress = $progress - $per;
          echo $new_progress;
          if($new_progress < 0){
            $new_progress = 0;
          }

          bp_course_update_user_progress($_POST['user_id'],$_POST['course_id'],$new_progress);

          $post_type = bp_course_get_post_type($_POST['id']);
          if($post_type == 'quiz'){
            bp_course_update_user_quiz_status($_POST['user_id'],$_POST['id'],0);
          }

          do_action('wplms_unit_instructor_uncomplete',$_POST['id'],$_POST['user_id'],$_POST['course_id']);
        }
        die();
    }

    function instructor_complete_unit(){

      if (!is_numeric($_POST['course_id']) || !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$_POST['course_id'])  || !is_numeric($_POST['user_id']) || !is_numeric($_POST['id']) || !current_user_can('edit_posts')){

          echo '<div id="message" class="info notice"><p>'.__('Security check failed !','vibe').'</p></div>';
          die();
      }

      if(bp_course_is_member($_POST['course_id'],$_POST['user_id'])){

        $time = apply_filters('wplms_force_complete_unit',time(),$_POST['id'],$_POST['course_id'],$_POST['user_id']);

          update_user_meta($_POST['user_id'],$_POST['id'],$time);
          update_post_meta($_POST['id'],$_POST['user_id'],0);

          $post_type = bp_course_get_post_type($_POST['id']);
          if($post_type == 'unit'){
            bp_course_update_user_unit_completion_time($_POST['user_id'],$_POST['id'],$_POST['course_id'],$time);
          }else if($post_type == 'quiz'){
            bp_course_update_user_quiz_status($_POST['user_id'],$_POST['id'],1);
          }

          if($post_type == 'wplms-assignment'){
            $user = get_userdata($_POST['user_id']);
            $assignment_title = get_the_title($_POST['id']);
            $args = array(
                    'comment_post_ID' => $_POST['id'],
                    'comment_author' => $user->display_name,
                    'comment_author_email' => $user->user_email,
                    'comment_content' => $assignment_title.' - '.$user->display_name,
                    'comment_date' => current_time('mysql'),
                    'comment_approved' => 1,
                    'comment_parent' => 0,
                    'user_id' => $_POST['user_id'],
              );
            wp_insert_comment($args);
          }

        $curriculum = bp_course_get_curriculum_units($_POST['course_id']);
        $per = round((100/count($curriculum)),2);
        $progress = bp_course_get_user_progress($_POST['user_id'],$_POST['course_id']);
        $new_progress = $progress+$per;

        if($new_progress > 100){
          $new_progress = 100;
        }

        bp_course_update_user_progress($_POST['user_id'],$_POST['course_id'],$new_progress);

        do_action('wplms_unit_instructor_complete',$_POST['id'],$_POST['user_id'],$_POST['course_id']);
      }

      die();
    }

    /* == End == */

    function reset_question_answer(){
      global $wpdb;
      $ques_id = $_POST['ques_id'];
      if(isset($ques_id) && $_POST['security'] && wp_verify_nonce($_POST['security'],'security'.$ques_id)){
        $user_id = get_current_user_id();
        $wpdb->query($wpdb->prepare("UPDATE $wpdb->comments SET comment_approved='trash' WHERE comment_post_ID=%d AND user_id=%d",$ques_id,$user_id));
        echo '<p>'.__('Answer Reset','vibe').'</p>';
      }else
        echo '<p>'.__('Unable to Reset','vibe').'</p>';

      die();
    }

    function calculate_stats_course(){
    	$course_id=$_POST['id'];
    	$flag=0;
    	if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') ){
            echo '<p>'.__('Security check failed !','vibe').'</p>';
            die();
        }

        if ( !isset($course_id) || !$course_id){
        	echo '<p>'.__('Incorrect Course selected.','vibe').'</p>';
            die();
        }
        $badge=$pass=$total_qmarks=$gross_qmarks=0;
        $users=array();
    	global $wpdb;

    	$badge_val=get_post_meta($course_id,'vibe_course_badge_percentage',true);
    	$pass_val=get_post_meta($course_id,'vibe_course_passing_percentage',true);

    	$members_course_grade = $wpdb->get_results( $wpdb->prepare("SELECT meta_value,meta_key FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key IN (SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s)",$course_id,'course_status'.$course_id), ARRAY_A);
      
    	if(count($members_course_grade)){
        $cmarks=$i=0;
    		foreach($members_course_grade as $meta){
    			if(is_numeric($meta['meta_key']) && $meta['meta_value'] > 2){
           
    						if($meta['meta_value'] >= $badge_val)
    							$badge++;

    						if($meta['meta_value'] >= $pass_val)
    							$pass++;

    						$users[]=$meta['meta_key'];

                if(isset($meta['meta_value']) && is_numeric($meta['meta_value']) && $meta['meta_value'] > 2 && $meta['meta_value']<101){
                  $cmarks += $meta['meta_value'];
                  $i++;
                }
    					}
    			}  // META KEY is NUMERIC ONLY FOR USERIDS
    	}

    	if($pass)
    		update_post_meta($course_id,'pass',$pass);


    	if($badge)
    		update_post_meta($course_id,'badge',$badge);

    	if($i==0)$i=1;
        $avg = round(($cmarks/$i));

    update_post_meta($course_id,'average',$avg);

    if($flag !=1){
    	$curriculum=bp_course_get_curriculum($course_id);
    		foreach($curriculum as $c){
    			if(is_numeric($c)){

    				if(bp_course_get_post_type($c) == 'quiz'){
              $i=$qmarks=0;

    					foreach($users as $user){
    						$k=get_post_meta($c,$user,true);
                if(is_numeric($k)){
      						$qmarks +=$k;
                  $i++;
      						$gross_qmarks +=$k;
                }
    					}
              if($i==0)$i=1;
    					
              $qavg=round(($qmarks/$i),1);

    					if($qavg)
    						update_post_meta($c,'average',$qavg);
    					else{
    						$flag=1;
    						break;
    					}
    				}
    			}
    	}
    }

    if(function_exists('assignment_comment_handle')){ // Assignment is active
      $assignments_query = $wpdb->get_results( $wpdb->prepare("select post_id from {$wpdb->postmeta} where meta_value = %d AND meta_key = 'vibe_assignment_course'",$course_id), ARRAY_A);
      foreach($assignments_query as $assignment_query){
        $assignments[]=$assignment_query['post_id'];
      }

      if(count($assignments)){ // If any connected assignments
        $assignments_string = implode(',',$assignments);
        $assignments_marks_query = $wpdb->get_results("select post_id,meta_value from {$wpdb->postmeta} where post_id IN ($assignments_string) AND meta_key REGEXP '^[0-9]+$' AND meta_value REGEXP '^[0-9]+$'", ARRAY_A);
        
        foreach($assignments_marks_query as $marks){
          $user_assignments[$marks['post_id']]['total'] += $marks['meta_value'];
          $user_assignments[$marks['post_id']]['number']++;
        }

        foreach($user_assignments as $key=>$user_assignment){
          if(isset($user_assignment['number']) && $user_assignment['number']){
            $avg = $user_assignment['total']/$user_assignment['number'];  
            update_post_meta($key,'average',$avg);
          }
        }
      }
    }
    	if(!$flag){
    		echo '<p>'.__('Statistics successfully calculated. Reloading...','vibe').'</p>';
    	}else{
    		echo '<p>'.__('Unable to calculate Average.','vibe').'</p>';
    	}

    	die();
    }

    function course_stats_user(){
	    $course_id = $_POST['id'];
      $user_id = $_POST['user'];

      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$course_id) ){
          echo '<div class="message">Security check failed !</div>';
          die();
      }
      

      echo '<a class="show_side link right" data-side=".course_stats_user">'.__('SHOW STATS','vibe').'</a><div class="course_stats_user"><a class="hide_parent link right">'.__('HIDE','vibe').'</a>';


      if ( !isset($user_id) || !$user_id){
      	echo '<div id="message" class="info notice"><p>'.__('Incorrect User selected.','vibe').'</p></div>';
          die();
      }


      global $wpdb,$bp;
      $start = $wpdb->get_var($wpdb->prepare("SELECT date_recorded FROM {$bp->activity->table_name} WHERE type ='start_course' AND item_id=%d AND component='course' AND user_id=%d ORDER BY id DESC LIMIT 1", $course_id,$user_id));
    	
    	$being = bp_course_get_user_course_status($user_id,$course_id);

    	if(empty($being)){
    			echo '<p>'.__('This User has not started the course.','vibe').'</p>';
  		}else{

        if($being > 3){
          $marks = get_post_meta($course_id,$user_id,true);
    			echo '<p>'.__('This User has completed the course.','vibe').'</p>';
    			echo '<h4>'.__('Student Score for Course ','vibe').' : <strong>'.$marks.__(' out of 100','vibe').'</strong></h4>';
        }  
          $course_curriculum = bp_course_get_curriculum_units($course_id);
          $complete = $total = count($course_curriculum);
    		
    			$total = 0;
    			$complete = 0;

    			echo '<h6>';
    			_e('Course Started : ','vibe');
          if(!empty($start)){
    			   echo '<span>'.human_time_diff(strtotime($start),time()).'</span>';
          }
          echo '</h6>';
    			$course_curriculum = bp_course_get_curriculum($course_id);

    			$curriculum = '<div class="curriculum_check"><h6>'.__('Curriculum :','vibe').'</h6><ul>';
    			$quiz ='<h5>'.__('Quizes','vibe').'</h5>';
    			foreach($course_curriculum as $c){
    				if(is_numeric($c)){
    					$total++;
              $type = bp_course_get_post_type($c);

              if($type == 'unit'){
                  if(bp_course_get_user_unit_completion_time($user_id,$c,$course_id)){
                      $complete++;
                      $curriculum .= '<li><span data-id="'.$c.'" class="done"></span> '.get_the_title($c).'</li>';
                  }else{
                      $curriculum .= '<li><span data-id="'.$c.'"></span> '.get_the_title($c).'</li>';
                  }
              }

  						if($type == 'quiz'){
  							$marks = get_post_meta($c,$user_id,true);
                if(is_numeric($marks)){
                    $complete++;
  							    $curriculum .= '<li class="check_user_quiz_results" data-quiz="'.$c.'" data-user="'.$user_id.'"><span class="done"></span> '.get_the_title($c).' <strong>'.(($marks)?_x('Marks Obtained : ',' marks obtained in quiz result','vibe').$marks:__('Under Evaluation','vibe')).'</strong></li>';
                    }else{
                        $curriculum .= '<li><span data-id="'.$c.'"></span> '.get_the_title($c).'</li>';    
                    }
                }

    				}else{
    					$curriculum .= '<li><h5>'.$c.'</h5></li>';
    				}
    			}
                
                $curriculum = apply_filters('wplms_course_student_stats',$curriculum,$course_id,$user_id);
    			$curriculum .= '</ul></div>';
    	}

      if(empty($complete)){$complete = 0;}
    	echo '<strong>'.__('Units Completed ','vibe').$complete.__(' out of ','vibe').$total.'</strong>';
    	echo '<div class="complete_course"><input type="text" class="dial" data-max="'.$total.'" value="'.$complete.'"></div>';
    	echo $curriculum;
        echo '</div>';
    	die();
    }

  function wplms_check_user_quiz_results($quiz_id = NULL,$user_id = NULL){

    if(empty($quiz_id))
      $quiz_id = $_REQUEST['quiz'];

    if(empty($user_id))
      $user_id = $_REQUEST['user'];

    if ( !isset($_REQUEST['security']) || !wp_verify_nonce($_REQUEST['security'],'security'.$_POST['course_id']) ){
        echo '<p>'.__('Security check failed !','vibe').'</p>';
        die();
    }
    bp_course_quiz_results($quiz_id,$user_id);
    do_action('wplms_quiz_results_extras');
      
    die();
  }

    function remove_user_course(){
    	  $course_id = $_POST['id'];
        $user_id = $_POST['user'];

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$course_id) ){
            echo '<div class="message">'.__('Security check failed !','vibe').'</div>';
            die();
        }

        if ( !isset($user_id) || !$user_id){
            echo '<p>'.__(' Incorrect User selected.','vibe').'</p>';
            die();
        }

        bp_course_remove_user_from_course($user_id,$course_id);

        $students=get_post_meta($course_id,'vibe_students',true);
        if($students >= 1){
          $students--;
          update_post_meta($course_id,'vibe_students',$students);
        }
  			echo '<p>'.__('User removed from the Course','vibe').'</p>';

        
        do_action('wplms_course_unsubscribe',$course_id,$user_id,$group_id);

    	die();
    }

    function reset_course_user(){
    	
      $course_id = $_POST['id'];
      $user_id = $_POST['user'];

      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$course_id) ){
          echo '<div class="message">'.__('Security check failed !','vibe').'</div>';
          die();
      }

      if ( !isset($user_id) || !is_numeric($user_id) || !$user_id){
          echo '<p>'.__(' Incorrect User selected.','vibe').'</p>';
          die();
      }
        
      //delete_user_meta($user_id,$course_id) // DELETE ONLY IF USER SUBSCRIPTION EXPIRED
      $status = bp_course_get_user_course_status($user_id,$course_id);
      
      if(isset($status) && is_numeric($status)){  // Necessary for continue course
        
        bp_course_update_user_course_status($user_id,$course_id,0); // New function
      
	      $course_curriculum = bp_course_get_curriculum($course_id);
      
        bp_course_update_user_progress($user_id,$course_id,0);
      
        //NEw drip feed use case
        delete_user_meta($user_id,'start_course_'.$course_id);
        do_action('wplms_student_course_reset',$course_id,$user_id);

  			foreach($course_curriculum as $c){
  				if(is_numeric($c)){
  					if(bp_course_get_post_type($c) == 'quiz'){
                          
              bp_course_remove_user_quiz_status($user_id,$c);
              bp_course_reset_quiz_retakes($c,$user_id);

              $questions = bp_course_get_quiz_questions($c,$user_id);
              if(isset($questions) && is_array($questions) && is_Array($questions['ques'])){
                foreach($questions['ques'] as $question){
                  global $wpdb;
                  if(isset($question) && $question !='' && is_numeric($question)){
                    bp_course_reset_question_marked_answer($c,$question,$user_id);
                  }
                }
              }
            }else{
              bp_course_reset_unit($user_id,$c,$course_id);
            }
  				}
  			}
  			
        /*=== Fix in 1.5 : Reset  Badges and CErtificates on Course Reset === */
        $user_badges = vibe_sanitize(get_user_meta($user_id,'badges',false));
        $user_certifications = vibe_sanitize(get_user_meta($user_id,'certificates',false));

        if(isset($user_badges) && is_Array($user_badges) && in_array($course_id,$user_badges)){
            $key=array_search($course_id,$user_badges);
            unset($user_badges[$key]);
            $user_badges = array_values($user_badges);
            update_user_meta($user_id,'badges',$user_badges);
        }

        if(isset($user_certifications) && is_Array($user_certifications) && in_array($course_id,$user_certifications)){
            $key=array_search($course_id,$user_certifications);
            unset($user_certifications[$key]);
            $user_certifications = array_values($user_certifications);

            global $wpdb;
            $certificate_name = 'certificate_'.$course_id.'_'.$user_id;
            $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_name = %s AND post_parent = %d AND post_author = %d",$certificate_name,$course_id,$user_id));
            if(is_numeric($attachment_id)){
              wp_delete_attachment($attachment_id);
            }
            
            update_user_meta($user_id,'certificates',$user_certifications);
        }
              /*==== End Fix ======*/

  			echo '<p>'.__('Course Reset for User','vibe').'</p>';
          
        do_action('wplms_course_reset',$course_id,$user_id);

    	}else{
    		echo '<p>'.__('There was issue in resetting this course for the user. Please contact admin.','vibe').'</p>';
    	}
    	die();
    }

    function reset_quiz(){

      $quiz_id = $_POST['id'];
      $user_id = $_POST['user'];

      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_quiz') ){
          echo '<p>'.__('Security check failed !','vibe').'</p>';
          die();
      }

      if ( !isset($user_id) || !$user_id){
          echo '<p>'.__(' Incorrect User selected.','vibe').'</p>';
          die();
      }

      bp_course_remove_user_quiz_status($user_id,$quiz_id);
      bp_course_reset_quiz_retakes($quiz_id,$user_id);
      $questions = bp_course_get_quiz_questions($quiz_id,$user_id);

      if(!empty($questions) && is_array($questions['ques'])){
          foreach($questions['ques'] as $question){
              bp_course_reset_question_marked_answer($quiz_id,$question,$user_id);
          }    
      }

      delete_user_meta($user_id,'quiz_questions'.$quiz_id);
      echo '<p>'.__('Quiz Reset for Selected User','vibe').'</p>';

      do_action('wplms_quiz_reset',$quiz_id,$user_id);
      die();
    }

    function give_marks(){
      $answer_id=intval($_POST['aid']);
      $value=intval($_POST['aval']);
      
      if(is_numeric($answer_id) && is_numeric($value)){
        update_comment_meta( $answer_id, 'marks',$value);
      }

      die();
    }

    function complete_course_marks(){
        $user_id=intval($_POST['user']);
        $course_id=intval($_POST['course']);
        $marks=intval($_POST['marks']);

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],$course_id) || !is_numeric($user_id) || !is_numeric($course_id) ){
            echo '<p>'.__('Security check failed !','vibe').'</p>';
            die();
        }

        $badge_per = get_post_meta($course_id,'vibe_course_badge_percentage',true);
        $passing_per =get_post_meta($course_id,'vibe_course_passing_percentage',true);

        $badge_filter = 0;
        if(isset($badge_per) && $badge_per && $marks >= $badge_per)
          $badge_filter = 1;

        $badge_filter = apply_filters('wplms_course_student_badge_check',$badge_filter,$course_id,$user_id,$marks,$badge_per);
        
        if($badge_filter){  
            $badges= vibe_sanitize(get_user_meta($user_id,'badges',false));

            if(is_array($badges)){
              if(!in_array($course_id,$badges))
                $badges[]=$course_id;
            }else{
              $badges = array();
              $badges[]=$course_id;
            }
            update_user_meta($user_id,'badges',$badges);
            do_action('wplms_badge_earned',$course_id,$badges,$user_id,$badge_filter);
        }

        $passing_filter = 0;
        if(isset($passing_per) && $passing_per && $marks >= $passing_per)
          $passing_filter = 1;

        $passing_filter = apply_filters('wplms_course_student_certificate_check',$passing_filter,$course_id,$user_id,$marks,$passing_per);
          
        if($passing_filter){
            $pass=vibe_sanitize(get_user_meta($user_id,'certificates',false));
            if(is_array($pass)){
              if(!in_array($course_id,$pass))
                $pass[]=$course_id; 
            }else{
              $pass = array();
              $pass[]=$course_id; 
            }
            update_user_meta($user_id,'certificates',$pass);
            do_action('wplms_certificate_earned',$course_id,$pass,$user_id,$passing_filter);
        }
        update_post_meta( $course_id,$user_id,$marks);    
        $course_end_status = apply_filters('wplms_course_status',4);  
        update_user_meta( $user_id,'course_status'.$course_id,$course_end_status);//EXCEPTION
        echo __('COURSE MARKED COMPLETE','vibe');

        do_action('wplms_evaluate_course',$course_id,$marks,$user_id);
        
        die();
    }

    function save_quiz_marks(){
      $quiz_id=intval($_POST['quiz_id']);
      $user_id=intval($_POST['user_id']);
      $marks=intval($_POST['marks']);

      $questions = bp_course_get_quiz_questions($quiz_id,$user_id);
      $max= array_sum($questions['marks']);
      
      update_post_meta( $quiz_id, $user_id,$marks);
      bp_course_update_user_quiz_status($user_id,$quiz_id,1);

      bp_course_set_quiz_remarks($quiz_id,$user_id,$_POST['remarks']);
      do_action('wplms_evaluate_quiz',$quiz_id,$marks,$user_id,$max);

      die();
    }

    function wplms_evaluate_course(){
        
        $course_id=intval($_POST['id']);
        $user_id=intval($_POST['user']);

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],$course_id) ){
            echo '<p>'.__('Security check failed !','vibe').'</p>';
            die();
        }

        if ( !isset($user_id) || !$user_id || !is_numeric($user_id)){
            echo '<p>'.__(' Incorrect User selected.','vibe').'</p>';
            die();
        }
        echo '<h4>'.sprintf(_x('%s submission by %s','Course submission by User in course - admin - submissions','vibe'),get_the_title($course_id),bp_core_get_user_displayname($user_id)).'</h4>';
        $sum=$max_sum=0;
        $curriculum= bp_course_get_curriculum($course_id);
         echo '<ul class="course_curriculum">';
        foreach($curriculum as $c){
          if(is_numeric($c)){
            if(bp_course_get_post_type($c) == 'quiz'){
                $status = get_user_meta($user_id,$c,true);
                $marks=get_post_meta($c,$user_id,true);
                $sum += intval($marks);

                $qmax = bp_course_get_quiz_questions($c,$user_id);
                
                $max=array_sum($qmax['marks']);
                $max_sum +=$max;
                echo '<li>
                      <strong>'.get_the_title($c).' <span>'.((isset($status) && $status !='')?__('MARKS: ','vibe').$marks.__(' out of ','vibe').$max:__(' PENDING','vibe')).'</span></strong>
                      </li>';
            }else{
                $status = bp_course_get_user_unit_completion_time($user_id,$c,$course_id);
                echo '<li>
                      <strong>'.get_the_title($c).' <span>'.((isset($status) && $status !='')?'<i class="icon-check"></i> '.__('DONE','vibe'):'<i class="icon-alarm-1"></i>'.__(' PENDING','vibe')).'</span></strong>
                      </li>';
            } 
          }else{

          }
        }
        //if existing marks 
        $existing_marks = 0;
        $existing_marks = get_post_meta($course_id,$user_id,true); 

        do_action('wplms_course_manual_evaluation',$course_id,$user_id);
        echo '</ul>';
        echo '<div id="total_marks">'.__('Total','vibe').' <strong><span>'.apply_filters('wplms_course_student_marks',$sum,$course_id,$user_id).'</span> / '.apply_filters('wplms_course_maximum_marks',$max_sum,$course_id,$user_id).'</strong> </div>';
        echo '<div id="course_marks">'.__('Course Percentage (Out of 100)','vibe').' <strong><span><input type="number" name="course_marks" id="course_marks_field" class="form_field" value="'.(!empty($existing_marks)?$existing_marks:'0').'" placegolder="'.__('Course Percentage out of 100','vibe').'" /></span></div>';
        echo '<a href="#" id="course_complete" class="button full" data-course="'.$course_id.'" data-user="'.$user_id.'">'.__('Mark Course Complete','vibe').'</a>';
        
        wp_nonce_field($course_id,'security');
      die();
    }

    function evaluate_quiz(){

        $quiz_id=intval($_POST['id']);
        $user_id=intval($_POST['user']);

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_quiz') ){
           echo '<p>'.__('Security check failed !','vibe').'</p>';
            die();
        }

        if ( !isset($user_id) || !$user_id){
             echo '<p>'.__(' Incorrect User selected.','vibe').'</p>';
            die();
        }

        if(bp_course_get_post_type($quiz_id) != 'quiz'){
          echo '<p>'.__(' Incorrect Quiz Id.','vibe').'</p>';
            die();
        }

      $questions = bp_course_get_quiz_questions($quiz_id,$user_id);

      echo '<h3 class="evaluate_heading">'.get_the_title($quiz_id).' '._x('submission by','Quiz submission "by" user','vibe').' '.bp_core_get_user_displayname($user_id).'</h3>';
      if(count($questions)):

        echo '<ul class="quiz_questions">';
        $sum=$max_sum=0;

        //global $wpdb;  To be Covered at a Later update
        //$questions_ids = implode($questions['ques']);
        //$quiz_results = $wpdb->get_results($wpdb->prepare("SELECT p.ID,c.comment_ID,p.post_content,c.comment_content FROM {$wpdb->posts} as p LEFT JOIN {$wpdb->comments} as c ON p.ID = c.comment_post_ID WHERE c.ID IN ($questions_ids) AND c.user_id = %d LIMIT 0,1"))

        foreach($questions['ques'] as $key=>$question){ 
          if(isset($question) && $question){

          $cid = '';
          $q=get_post($question);

          echo '<li>
              <div class="q">'.apply_filters('the_content',$q->post_content).'</div>';

            $marked_answer = bp_course_get_question_marked_answer($quiz_id,$question,$user_id);
            $marked_answer_id = bp_course_get_question_marked_answer_id($quiz_id,$question,$user_id);

            echo '<strong>';_e('Marked Answer :','vibe');echo '</strong>';
            $correct_answer=get_post_meta($question,'vibe_question_answer',true);
            $type = get_post_meta($question,'vibe_question_type',true);
            
            switch($type){
                  
                case 'select':
                case 'single': 
                    $options = vibe_sanitize(get_post_meta($question,'vibe_question_options',false));
                    
                    echo $options[(intval($marked_answer)-1)]; // Reseting for the array
                    if(isset($correct_answer) && $correct_answer !=''){
                      $ans=$options[(intval($correct_answer)-1)];

                    }
                break;  
                case 'multiple': 
                    $options = vibe_sanitize(get_post_meta($question,'vibe_question_options',false));
                    $ans=explode(',',$marked_answer);

                    foreach($ans as $an){
                      echo $options[intval($an)-1].' ';
                    }

                    $cans = explode(',',$correct_answer);
                    $ans='';
                    foreach($cans as $can){
                      $ans .= $options[intval($can)-1].', ';
                    }
                break;
                case 'match': 
                case 'sort': 
                    $options = vibe_sanitize(get_post_meta($question,'vibe_question_options',false));
                    $ans=explode(',',$marked_answer);

                    foreach($ans as $an){
                      echo $an.'. '.$options[intval($an)-1].' ';
                    }

                    $cans = explode(',',$correct_answer);
                    $ans='';
                    foreach($cans as $can){
                      $ans .= $can.'. '.$options[intval($can)-1].', ';
                    }
                break;
                case 'fillblank':
                case 'smalltext': 
                      echo $marked_answer;
                      $ans = $correct_answer;
                break;
                case 'survey': 
                  $options = vibe_sanitize(get_post_meta($question,'vibe_question_options',false));
                  echo $options[(intval($marked_answer)-1)]; // Reseting for the array
                  $ans = '';
                break;
                default: 
                      echo apply_filters('the_content',$marked_answer);
                      $ans = $correct_answer;
                break;
            }

            $marks = bp_course_get_user_question_marks($quiz_id,$question,$user_id);
            if(isset($correct_answer) && $correct_answer !='' && isset($ans) && $ans != ''){
                echo '<strong>';_e('Correct Answer :','vibe');echo '<span>'.$ans.'</span></strong>';
            }

            if(!empty($marks)){
            
                if(empty($marked_answer_id))
                    echo '<strong>'._x('Unattempted question ! Marks can not be assigned for this question.','Unattempted question warning in Course - Admin - Submissions area','vibe').'</strong>';
                else
                    echo '<span class="marking">'.__('Marks Obtained','vibe').' <input type="text" id="'.$marked_answer_id.'" class="form_field question_marks" value="'.$marks.'" placeholder="'.__('Give marks','vibe').'" />
                    <a href="#" class="give_marks button" data-ans-id="'.$marked_answer_id.'">'.__('Update Marks','vibe').'</a>';

                $sum = $sum+$marks;
            }else{
                if(empty($marked_answer_id)){
                    echo '<strong>'._x('Unattempted question ! Marks can not be assigned for this question.','Unattempted question warning in Course - Admin - Submissions area','vibe').'</strong>';
            }else{
                echo '<span class="marking">'.__('Marks Obtained','vibe').' <input type="text" id="'.$marked_answer_id.'" class="form_field question_marks" value="" placeholder="'.__('Give marks','vibe').'" />
                <a href="#" class="give_marks button" data-ans-id="'.$marked_answer_id.'">'.__('Give Marks','vibe').'</a>';
            }
          }
          $max_sum=$max_sum+intval($questions['marks'][$key]);
          echo '<span> '.__('Total Marks','vibe').' : '.$questions['marks'][$key].'</span>';
          echo '</li>';

          } // IF question check
        } 
        echo '</ul>';
        echo '<div id="total_marks">'.__('Total','vibe').' <strong><span>'.$sum.'</span> / '.$max_sum.'</strong> </div>';
        echo '<div id="quiz_remarks_block">';
        echo '<h3 class="heading"><span>'.__('Instructor remarks','vibe').'</span></h3>';
        $remarks = bp_course_get_quiz_remarks($quiz_id,$user_id);
        wp_enqueue_editor();
        wp_editor($remarks,'quiz_remarks',array('editor_class'=>'post_field'));
        echo   '</div>';
        _WP_Editors::enqueue_scripts();
        _WP_Editors::editor_js();
        print_footer_scripts();
        echo '<script>jQuery(document).ready(function($){
          wp.editor.initialize("quiz_remarks",{ 
    tinymce: { 
      wpautop:true, 
      plugins : "charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview", 
      toolbar1: "formatselect bold italic | bullist numlist | blockquote | alignleft aligncenter alignright | link unlink | wp_more | spellchecker" 
    }, 
    quicktags: true 
  });});</script>';
                
        echo '<a href="#" id="mark_complete" class="button full" data-quiz="'.$quiz_id.'" data-user="'.$user_id.'">'.__('Mark Quiz as Checked','vibe').'</a>';
        endif;

        die();
    }

    /*
      COURSE BULK ACTIONS
    */

    function search_course_members(){

        $course_id=$_POST['course_id'];

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$course_id) ){
            echo '<div class="message">Security check failed !</div>';
            die();
        } 
        $active_status = $_POST['active_status'];
        $course_status = $_POST['course_status'];
        $s = $_POST['s'];
        
        $view = $_POST['view'];
        $user_ids = array();

        $loop_number = vibe_get_option('loop_number');
        if(!isset($loop_number)) $loop_number = 5;

        $page = $_POST['page'];
        if(empty($page)){
            $page = 1;
            $start = 0;
        }else{
            $start = ($page-1)*$loop_number;
        }

        global $wpdb,$bp;
        $query_start = "SELECT SQL_CALC_FOUND_ROWS DISTINCT u.ID as id"; 
        $query_tables = $query_where = $flag = "";
        if(!empty($s)){
            $search_terms = '%' .bp_esc_like( wp_kses_normalize_entities( $s ) ). '%';
            $query_tables ="FROM {$wpdb->users} as u";
            $query_where = "WHERE ( user_login LIKE '$search_terms' OR user_nicename LIKE '$search_terms' OR display_name LIKE '$search_terms' OR user_email LIKE '$search_terms' )";
        }

        if(!empty($active_status)){
            $time = time();
            if(empty($query_where)){
                $query_tables = "FROM {$wpdb->users} as u LEFT JOIN {$wpdb->usermeta} as m ON u.ID= m.user_id";
                if($active_status == 1){
                    $query_where = "WHERE m.meta_key = $course_id AND m.meta_value > $time";
                }else{
                    $query_where = "WHERE m.meta_key = $course_id AND m.meta_value <= $time";
                }
            }else{
                $query_tables .= " LEFT JOIN {$wpdb->usermeta} as m ON u.ID= m.user_id";
                if($active_status == 1){
                    $query_where .= "AND m.meta_key = $course_id AND m.meta_value > $time";  
                }else{
                    $query_where .= " AND m.meta_key = $course_id AND m.meta_value <= $time";
                }
            }
        }

        if(!empty($course_status)){
          if(is_numeric($course_status)){
            if(empty($query_where)){
                $query_tables = "FROM {$wpdb->users} as u LEFT JOIN {$wpdb->usermeta} as m2 ON u.ID= m2.user_id";
                $query_where = "WHERE m2.meta_key = 'course_status$course_id' AND m2.meta_value = $course_status";
            }else{
                $query_tables .= " LEFT JOIN {$wpdb->usermeta} as m2 ON u.ID = m2.user_id";
                $query_where .= " AND m2.meta_key = 'course_status$course_id' AND m2.meta_value = $course_status";
            }
          }else{
              $query_tables = "FROM {$wpdb->users} as u LEFT JOIN {$wpdb->usermeta} as m2 ON u.ID= m2.user_id";
              if(empty($query_where)){
                $query_where =" WHERE m2.meta_key = $course_id";
              }else{
                $query_where .=" AND m2.meta_key = $course_id";
              }
          }
        }

        if(!empty($s) && empty($active_status) && empty($course_status)){
          $query_tables .= " LEFT JOIN {$wpdb->usermeta} as m2 ON u.ID = m2.user_id";
          $query_where .= " AND m2.meta_key = 'course_status$course_id'";
        }
        if(empty($s) && empty($active_status) && empty($course_status)){
            $query_tables ="FROM {$wpdb->users} as u LEFT JOIN {$wpdb->usermeta} as m ON u.ID= m.user_id";
            $query_where = "WHERE m.meta_key = 'course_status$course_id'";
        }

        //Ordering in Course- Members
        if(!empty($course_status) && !is_numeric($course_status)){
          switch($course_status){
            case 'alphabetical':
              $query_where .= " ORDER BY u.display_name ASC";
            break;
            case 'toppers':
              $query_where .= " ORDER BY m2.meta_value DESC";
            break;
          }
        }

        if(empty($query_where) && empty($query_tables)){
            $flag = 1;
        }

        if(empty($flag)){
            $query = $query_start.' '.$query_tables.' '.$query_where.' LIMIT '.$start.','.$loop_number;
            
            $user_ids = $wpdb->get_results($query);
            $max_page = $wpdb->get_var('SELECT FOUND_ROWS();');
            if(empty($max_page)){$max_page = 1;}else{$max_page = ceil($max_page/$loop_number);}
            if(empty($user_ids)){
                $flag = 1;
            }else{

                if($view == 'admin' && current_user_can('edit_posts')){
                  foreach($user_ids as $user_id){
                        bp_course_admin_userview($user_id->id,$course_id);
                    }
                }else{
                  foreach($user_ids as $user_id){
                    bp_course_member_userview($user_id->id,$course_id);
                  }  
                }
                
                echo '<li><div class="pagination"><div><div class="pag-count" id="course-member-count">'.sprintf(__('Viewing page %d of %d ','vibe'),(empty($page)?1:$page ),$max_page).'</div>';

                echo '<div class="pagination-links">';
                $f = 1;
                if($max_page > 1){
                    for($i=1;$i<=$max_page;$i++ ){
                        if($i == $page){
                            echo '<span class="page-numbers current">'.$page.'</span>';
                        }else if(in_array($i,array($page-1,$page+1,1,$max_page))){
                            echo '<a class="page-numbers course_admin_paged">'.$i.'</a>';
                        }else if($f && ( $i > ($page+1) || $i < ($page-1))){
                            echo '<a class="page-numbers">...</a>'; 
                            $f=0;
                        }
                    }
                }

                echo '</div></div></div></li>';
            }
        }
        if($flag){
            echo '<li><div class="message">'._x('No user found !','no user found in live search in course admin','vibe').'</div></li>';
        }
        
        die();
    }  

    function send_bulk_message(){

        $course_id=$_POST['course'];
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$course_id) ){
            echo 'Security check failed !';
            die();
        }
        if(isset($_POST['all'])){
          $members = bp_course_get_students_undertaking($course_id,999);
        }else{
          $members = json_decode(stripslashes($_POST['members']));
        }

        $sender = $_POST['sender'];
        $subject = stripslashes($_POST['subject']);
        if(!isset($subject)){
          _e('Set a Subject for the message','vibe');
          die();  
        }
        $message = stripslashes($_POST['message']);
        if(!isset($message)){
          _e('Set a Subject for the message','vibe');
          die();  
        }
        $sent = 0;
        if(count($members) > 0){
              if(bp_is_active('messages')){
               messages_new_message( array('sender_id' => $sender, 'subject' => $subject, 'content' => $message,   'recipients' => $members ) );
                $sent = count($members);
              }
          echo __('Messages Sent to ','vibe').$sent.__(' members','vibe');
        }else{
          echo __('Please select members','vibe');
        }

        do_action('wplms_bulk_action','bulk_message',$course_id,$members);

        die();
    }

    function select_users(){
      $course_id=$_POST['course'];
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$course_id) ){
          echo 'Security check failed !';
          die();
      }
      
      global $wpdb;
      $term = $_POST['q']['term'];
      $q = "
        SELECT ID, display_name FROM {$wpdb->users} 
        WHERE (
          user_login LIKE '%$term%'
          OR user_nicename LIKE '%$term%'
          OR user_email LIKE '%$term%' 
          OR user_url LIKE '%$term%'
          OR display_name LIKE '%$term%'
          ) AND ID NOT IN ( 
            SELECT user_id 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = 'course_status$course_id'
            )";
      $users = $wpdb->get_results($q);

      $user_list = array();
      // Check for results
      if (!empty($users)) {
          foreach($users as $user){
            $user_list[] = array(
              'id'=>$user->ID,
              'image'=>bp_core_fetch_avatar(array('item_id' => $user->ID, 'type' => 'thumb', 'width' => 32, 'height' => 32, 'html'=>false)),
              'text'=>$user->display_name
            );
          }
          echo json_encode($user_list);
      } else {
          echo json_encode(array('id'=>'','text'=>_x('No Users found !','No users found in Course - admin - add users area','vibe')));
      }
      die();
    }

    function add_bulk_students(){
        
        $course_id=$_POST['course'];
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$course_id) ){
            echo 'Security check failed !';
            die();
        }

        $members = $_POST['members'];
        $member_ids = array();
        if(!is_Array($members)){
          $members = stripslashes($members);
          if(strpos($members,',')){
            $members=explode(',',$members);
          }else{
            $members = array($members);
          }    
        }
        $html = '';
        foreach($members as $member){
          if(is_numeric($member)){
            $user_id = $member;
          }else{
            if(filter_var($member, FILTER_VALIDATE_EMAIL)) {
              $user_id = email_exists($member);
            }else {
              $user_id = bp_core_get_userid_from_nicename($member);
            }  
          }
          
          if(!empty($user_id)){
            $force_flag = apply_filters('wplms_force_flag_bulk_add_students',1,$course_id,$user_id);
            $check = bp_course_add_user_to_course($user_id,$course_id,'',$force_flag);
            if($check){
                $field = vibe_get_option('student_field');
                if(!isset($field) || !$field) $field = 'Location';
                $html .= '<li id="s'.$user_id.'">
                <input type="checkbox" class="member" value="'.$user_id.'">
                '.bp_core_fetch_avatar ( array( 'item_id' => $user_id, 'type' => 'full' ) ).'
                <h6>'.bp_core_get_userlink( $user_id ).'</h6><span>'.(function_exists('xprofile_get_field_data')?xprofile_get_field_data( $field, $user_id ):'').'</span><ul> 
                <li><a class="tip reset_course_user" data-course="'.$course_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('Reset Course for User','vibe').'"><i class="icon-reload"></i></a></li>
                <li><a class="tip course_stats_user" data-course="'.$course_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('See Course stats for User','vibe').'"><i class="icon-bars"></i></a></li>
                <li><a class="tip remove_user_course" data-course="'.$course_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('Remove User from this Course','vibe').'"><i class="icon-x"></i></a></li>
                </ul></li>'; 
                $member_ids[]=$user_id;
            }
          }
        }
        echo $html;
        if(!empty($member_ids)){
            do_action('wplms_bulk_action','added_students',$course_id,$member_ids);
        }
        die();
    }

    function assign_badge_certificates(){

        $course_id=$_POST['course'];
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$course_id) ){
            echo 'Security check failed !';
            die();
        }
        $members = json_decode(stripslashes($_POST['members']));

        $assign_action = $_POST['assign_action'];
        if(!isset($assign_action) && !$assign_action){
          _e('Select Assign Value','vibe');
          die();  
        }

        $assigned=0;
        if(count($members) > 0){
          foreach($members as $mkey=>$member){ 
              if(is_numeric($member) && bp_course_get_post_type($course_id) == 'course'){

                switch($assign_action){
                  case 'add_badge':
                    $badges = vibe_sanitize(get_user_meta($member,'badges',false));
                    if(isset($badges) && is_array($badges)){
                      if(!in_array($course_id,$badges))
                        $badges[]=$course_id;
                    }else{
                      $badges = array($course_id);
                    }
                    update_user_meta($member,'badges',$badges);
                  break;
                  case 'add_certificate':
                    $certificates = vibe_sanitize(get_user_meta($member,'certificates',false));
                    if(isset($certificates) && is_array($certificates)){
                      if(!in_array($course_id,$certificates))
                        $certificates[]=$course_id;
                    }else{
                        $certificates = array($course_id);
                    }
                    update_user_meta($member,'certificates',$certificates);
                  break;
                  case 'remove_badge': 
                    $badges = vibe_sanitize(get_user_meta($member,'badges',false));
                    if(isset($badges) && is_array($badges)){
                      $k=array_search($course_id,$badges);
                      if(isset($k)){
                        unset($badges[$k]);
                      }
                      $badges = array_values($badges);
                      update_user_meta($member,'badges',$badges);
                    }
                  break;
                  case 'remove_certificate':
                    $certificates = vibe_sanitize(get_user_meta($member,'certificates',false));
                    $k=array_search($course_id,$certificates);
                    if(isset($k)){
                      unset($certificates[$k]);
                      global $wpdb;
                      $user_id = $member;
                      $certificate_name = 'certificate_'.$course_id.'_'.$user_id;
                      $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_name = %s AND post_parent = %d AND post_author = %d",$certificate_name,$course_id,$user_id));
                      if(is_numeric($attachment_id)){
                        wp_delete_attachment($attachment_id);
                      }
                    }
                    $certificates = array_values($certificates);
                    update_user_meta($member,'certificates',$certificates);

                  break;
                }
                
                
                $flag=1;
                $assigned++;
              }else{
                $flag=0;
                break;
              }
          }

          if($flag){
            echo __('Action assigned to ','vibe').$assigned.__(' members','vibe');
            
            do_action('wplms_bulk_action',$assign_action,$course_id,$members);

          }else
            echo __('Could not assign action to members','vibe');

        }else{
          echo __('Please select members','vibe');
        }

        die();
    }

    function wplms_extend_course_subscription(){

        $course_id=$_POST['course'];
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$course_id) || bp_course_get_post_type($course_id) != 'course' ){
            echo 'Security check failed !';
            die();
        }
        $members = json_decode(stripslashes($_POST['members']));
        $extend_amount = $_POST['extend_amount'];
        if(!isset($extend_amount) || !$extend_amount){
          echo __('Please enter extension amount','vibe');
          die();
        }

        if(!count($members)){
          echo __('Please select members','vibe');
          die();
        }
        $course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400,$course_id);
        $extend_amount_seconds = $extend_amount*$course_duration_parameter;
        $count=0;$neg=0;
        foreach($members as $member){
          if(is_numeric($member)){

            $expiry = get_user_meta($member,$course_id,true);
            if(isset($expiry) && $expiry){
              if($expiry < time()){
                $expiry = time();
              }
              $expiry = $expiry + $extend_amount_seconds;
              update_user_meta($member,$course_id,$expiry);
              $count++;
            }else{
              $neg++;
            }
          }
        }
        if($neg){
          echo sprintf(__('Subscription extended for %d students, unable to extend for %d students','vibe'),$count,$neg);
        }else{

          echo sprintf(__('Subscription extended for %d students','vibe'),$count);
          do_action('wplms_bulk_action','extend_course_subscription',$course_id,$members);
        }
      die();  
    }

/*=== Manage Course Status v 1.9.5 =====*/

    function wplms_change_course_status(){

        $course_id=$_POST['course'];
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$course_id) ){
            echo 'Security check failed !';
            die();
        }
        $members = json_decode(stripslashes($_POST['members']));

        $status_action = $_POST['status_action'];
        if(!isset($status_action) && !$status_action){
          _e('Select Course Status','vibe');
          die();  
        }

        $assigned=0;
        if(count($members) > 0){
          foreach($members as $mkey=>$member){ 
              if(is_numeric($member) && bp_course_get_post_type($course_id) == 'course'){

                switch($status_action){
                  case 'start_course':
                    $status=0;
                  break;
                  case 'continue_course':
                    $status=1;
                  break;
                  case 'under_evaluation': 
                    $status=2;
                  break;
                  case 'finish_course':
                    $status=3;
                  break;
                }
                $status = apply_filters('wplms_course_status',$status,$status_action);
                if(is_numeric($status)){
                  bp_course_update_user_course_status($member,$course_id,$status);  
                  if($status == 3 && isset($_POST['data']) && is_numeric($_POST['data'])){
                    update_post_meta($course_id,$member,$_POST['data']);
                  }
                }
                
                $flag=1;
                $assigned++;
              }else{
                $flag=0;
                break;
              }
          }

          if($flag){
            echo __('Course status changed for ','vibe').$assigned.__(' members','vibe');
            do_action('wplms_bulk_action','change_course_status',$course_id,$members);
          }else
            echo __('Could not assign action to members','vibe');

        }else{
          echo __('Please select members','vibe');
        }
        die();
    }

/*=== DOWNLOAD STATS =====*/

    function wplms_course_download_stats(){
      $course_id=$_POST['course'];
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') ){
          echo __('Security check failed !','vibe');
          die();
      } 
      if (!current_user_can('edit_posts') || !is_numeric($course_id)){
          echo __('User does not have capability to download stats !','vibe');
          die();
      }
      
      $fields = json_decode(stripslashes($_POST['fields']));
      $type=stripslashes($_POST['type']);
      if(!isset($type))
        die();

      $users = array();
      $csv = array();$csv_title=array();
      global $wpdb,$bp;

      switch($type){
        case 'all_students':
          $users = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key  = %s ",'course_status'.$course_id),ARRAY_A);
        break;
        case 'expired_students':
          $users = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key  = %s AND meta_value = %d",'course_status'.$course_id,4),ARRAY_A);
        break;
        case 'finished_students':
          $users = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key  = %s AND meta_value = %d",'course_status'.$course_id,4),ARRAY_A);
        break;
        case 'pursuing_students':
          $users = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value < %d",'course_status'.$course_id,4),ARRAY_A);
        break;
        case 'badge_students':
          $users = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s",'badges',"%$course_id%"),ARRAY_A);
        break;
        case 'certificate_students':
          $users = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s",'certificates',"%$course_id%"),ARRAY_A);
        break;
      }
      if(count($users)){ 
        foreach($users as $user){
          $user_id = $user['user_id'];
          $i=0;
          foreach($fields as $k=>$field){
            switch($field){
              case 'stats_student_start_date':
                $title=__('START DATE','vibe');
                if(!in_array($title,$csv_title))
                  $csv_title[$i]=$title;

                $date = $wpdb->get_results($wpdb->prepare("SELECT date_recorded FROM {$bp->activity->table_name} WHERE type=%s AND user_id = %d and item_id = %d",'start_course',$user_id,$course_id));
                if(is_array($date) && is_object($date[0]) && isset($date[0]->date_recorded))
                  $csv[$i][]=$date[0]->date_recorded;
                else
                  $csv[$i][]=__('N.A','vibe');

              break;
              case 'stats_student_completion_date':
                $title=__('COMPLETION DATE','vibe');
                if(!in_array($title,$csv_title))
                  $csv_title[$i]=$title;

                $date = $wpdb->get_results($wpdb->prepare("SELECT date_recorded FROM {$bp->activity->table_name} WHERE type=%s AND user_id = %d and item_id = %d",'submit_course',$user_id,$course_id));
                if(is_array($date) && is_object($date[0]) && isset($date[0]->date_recorded))
                  $csv[$i][]=$date[0]->date_recorded;
                else
                  $csv[$i][]=__('N.A','vibe');
              break;
              case 'stats_student_id':
                $title=__('ID','vibe');
                if(!in_array($title,$csv_title))
                  $csv_title[$i]=$title;

                $csv[$i][] = $user_id;
              break;
              case 'stats_student_name':
                $title=__('NAME','vibe');
                if(!in_array($title,$csv_title))
                  $csv_title[$i]=$title;

                $csv[$i][] = bp_core_get_user_displayname($user_id);
              break;
              case 'stats_student_unit_status':
                $units=bp_course_get_curriculum_units($course_id);
                foreach($units as $unit_id){
                  if(bp_course_get_post_type($unit_id) == 'unit'){

                  $title=get_the_title($unit_id);
                  if(!in_array($title,$csv_title))
                  $csv_title[$i]=$title;
                  $unit_completion_time = bp_course_get_user_unit_completion_time($user_id,$unit_id,$course_id);//get_user_meta($user_id,$unit_id,true);
                  if(isset($unit_completion_time)){
                    $csv[$i][] = date("Y-m-d H:i", $unit_completion_time);
                  }else{
                    $csv[$i][] = 0;
                  }
                  $i++;
                  }
                }
                
              break;
              case 'stats_student_quiz_score':
                $units=bp_course_get_curriculum_units($course_id);

                foreach($units as $unit_id){
                  if(bp_course_get_post_type($unit_id) == 'quiz'){

                    $title=get_the_title($unit_id);
                    if(!in_array($title,$csv_title))
                      $csv_title[$i]=$title;

                    $score = get_post_meta($unit_id,$user_id,true);
                    if(!isset($score) || !$score)
                      $csv[$i][] = __('N.A','vibe');
                    else
                      $csv[$i][] = $score;

                    $i++;
                  }
                }
              break;
              case 'stats_student_badge':
                $title=__('BADGE','vibe');
                  if(!in_array($title,$csv_title))
                  $csv_title[$i]=$title;
                $check = $wpdb->get_results($wpdb->prepare("SELECT COUNT(meta_key) as count FROM {$wpdb->usermeta} WHERE meta_key = %s AND user_id = %d AND meta_value LIKE %s",'badges',$user_id,"%$course_id%"),ARRAY_A);
                if(isset($check) && is_array($check)){
                   if($check[0]['count'])
                    $csv[$i][]= 1;
                    else
                      $csv[$i][]= 0;
                }
              break;
              case 'stats_student_certificate':
                $title=__('CERTIFICATE','vibe');
                if(!in_array($title,$csv_title))
                $csv_title[$i]=$title;
                $check = $wpdb->get_results($wpdb->prepare("SELECT COUNT(meta_key) as count FROM {$wpdb->usermeta} WHERE meta_key = %s AND user_id = %d AND meta_value LIKE %s",'certificates',$user_id,"%$course_id%"),ARRAY_A);
                if(isset($check) && is_array($check)){
                   if($check[0]['count'])
                    $csv[$i][]= 1;
                    else
                      $csv[$i][]= 0;
                }
              break;
              case 'stats_student_marks':
              $title=__('SCORE','vibe');
              if(!in_array($title,$csv_title))
                $csv_title[$i]=$title;

              $score = get_post_meta($course_id,$user_id,true);
              $csv[$i][]=$score;

              break;
              default;
              do_action_ref_array('wplms_course_stats_process', array( &$csv_title, &$csv,&$i,&$course_id,&$user_id,&$field));
              break;
            }
            $i++;
          }
        }
      }  

      if(!count($csv) || !is_array($csv[0])){
        echo '#';
        die();
      }

      $dir = wp_upload_dir();
      $user_id = get_current_user_id();
      $file_name = 'download_'.$course_id.'_'.$user_id.'.csv';
      $filepath = $dir['basedir'] . '/stats/';
      if(!file_exists($filepath))
        mkdir($filepath,0755);

      $file = $filepath.$file_name;
      if(file_exists($file))
      unlink($file);
      
      if (($handle = fopen($file, "w")) !== FALSE) {
        fputcsv($handle,$csv_title);
          
          $rows = count($csv[0]);
          for($i=0;$i<$rows;$i++){
            $arr=array(); 
              foreach ($csv as $key=>$f) {
                $arr[]=$f[$i];
              }
            fputcsv($handle, $arr);  
          }
        }
        fclose($handle);

      $file_url = $dir['baseurl']. '/stats/'.$file_name;
      echo $file_url;
      
      die();
    }

    function record_cache($key,$val){
      $this->cached_values[$key]=$val;
    }
    function fetch_cache($key){
      return $this->cached_values[$key];
    }
    function wplms_download_mod_stats(){

      $id=$_POST['id'];
      $post_type=$_POST['type'];
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') ){
          echo __('Security check failed !','vibe');
          die();
      } 
      if (!current_user_can('edit_posts') || !is_numeric($id)){
          echo __('User does not have capability to download stats !','vibe');
          die();
      }
      
      $fields = json_decode(stripslashes($_POST['fields']));
      $type=stripslashes($_POST['select']);
      if(!isset($type))
        die();

      $users = array();
      $csv = array();$csv_title=array();
      global $wpdb,$bp;
      if(in_array($post_type,array('quiz','wplms-assignment'))){
        switch($type){
          case 'all_students':
            $users = $wpdb->get_results($wpdb->prepare("SELECT meta_key as user_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key REGEXP '^[0-9]+$' AND meta_value REGEXP '^[0-9]+$'",$id),ARRAY_A);
          break;
          case 'finished_students':
            $users = $wpdb->get_results($wpdb->prepare("SELECT meta_key as user_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_value REGEXP '^[0-9]+$'  AND meta_key IN (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %d AND meta_value < %d)",$id,0,$id,time()),ARRAY_A);
          break;
        }
      }

      if($post_type == 'question'){
        $users = $wpdb->get_results($wpdb->prepare("SELECT user_id as user_id FROM {$wpdb->comments} WHERE comment_post_ID = %d AND comment_approved = %d",$id,1),ARRAY_A);
      }
      if(count($users)){
        foreach($users as $user){
          if(is_numeric($user['user_id']) && $user['user_id']){
          $user_id = $user['user_id'];
          $i=0;
          foreach($fields as $k=>$field){
            switch($field){
              case 'stats_student_start_date':
                $title=__('START DATE','vibe');
                if(!in_array($title,$csv_title))
                  $csv_title[$i]=$title;

               if(in_array($post_type,array('quiz','wplms-assignment'))){
                $dtype='start_'.$post_type;

                if($post_type == 'wplms-assignment')
                  $dtype='assignment_started';
                $date = $wpdb->get_results($wpdb->prepare("SELECT date_recorded FROM {$bp->activity->table_name} WHERE type=%s AND user_id = %d AND secondary_item_id = %d ORDER BY id DESC",$dtype,$user_id,$id));
                }else if($post_type == 'question'){
                  $date = $wpdb->get_results($wpdb->prepare("SELECT comment_date as date_recorded FROM {$wpdb->comments} WHERE comment_approved= %d AND user_id = %d and comment_post_ID = %d",1,$user_id,$id));
                }
                if(is_array($date) && is_object($date[0]) && isset($date[0]->date_recorded))
                  $csv[$i][]=$date[0]->date_recorded;
                else
                  $csv[$i][]=__('N.A','vibe');
              break;
              case 'stats_student_finish_date':
                $title=__('COMPLETION DATE','vibe');
                if(!in_array($title,$csv_title))
                  $csv_title[$i]=$title;
                if(in_array($post_type,array('quiz','wplms-assignment'))){
                   $dtype='submit_'.$post_type;
                   if($post_type == 'wplms-assignment')
                  $dtype='assignment_submitted';
                $date = $wpdb->get_results($wpdb->prepare("SELECT date_recorded FROM {$bp->activity->table_name} WHERE type=%s AND user_id = %d AND secondary_item_id = %d ORDER BY id DESC",$dtype,$user_id,$id));
                }else if($post_type == 'question'){
                  $date = $wpdb->get_results($wpdb->prepare("SELECT comment_date as date_recorded FROM {$wpdb->comments} WHERE comment_approved= %d AND user_id = %d and comment_post_ID = %d",1,$user_id,$id));
                }
                if(is_array($date) && is_object($date[0]) && isset($date[0]->date_recorded))
                  $csv[$i][]=$date[0]->date_recorded;
                else
                  $csv[$i][]=__('N.A','vibe');
                
              break;
              case 'stats_student_id':
                $title=__('ID','vibe');
                if(!in_array($title,$csv_title))
                  $csv_title[$i]=$title;

                $csv[$i][] = $user_id;
              break;
              case 'stats_student_name':
                $title=__('NAME','vibe');
                if(!in_array($title,$csv_title))
                  $csv_title[$i]=$title;

                $csv[$i][] = bp_core_get_username($user_id);
              break;
              case 'stats_question_scores':

                  $quiz_dynamic = $this->fetch_cache($id.'_dynamic');
                  if(empty($quiz_dynamic)){
                    $quiz_dynamic = get_post_meta($id,'vibe_quiz_dynamic',true);
                    $this->record_cache($id.'_dynamic',$quiz_dynamic);
                  }

                if(!vibe_validate($quiz_dynamic)){

                    $questions = $this->fetch_cache($id.'_questions');
                    if(empty($questions)){
                      $questions = get_post_meta($id,'vibe_quiz_questions',true);
                      $this->record_cache($id.'_questions',$questions);
                    }

                    $i_bkup = $i;
                    if(is_array($questions) && is_array($questions['ques'])){
                      foreach($questions['ques'] as $m=>$question){

                        $qtitle = $this->fetch_cache($question.'_title');
                        if(empty($qtitle)){
                          $qtitle = get_the_title($question);
                          $this->record_cache($question.'_title',$qtitle);
                        }
                        $title = $qtitle.' ('.$questions['marks'][$m].') ';
                        if(!in_array($title,$csv_title)){
                          $csv_title[$i_bkup]=$title;  
                          $i_bkup++;
                        }
                      }
                      $i_bkup = $i;
                      $all_marks = array();
                      foreach($questions['ques'] as $m=>$question){
                        $marks = $wpdb->get_var($wpdb->prepare("SELECT meta_value as score FROM {$wpdb->commentmeta} WHERE meta_key = %s AND comment_id IN ( SELECT c.comment_ID FROM {$wpdb->comments} as c LEFT JOIN {$wpdb->commentmeta} as m ON c.comment_ID = m.comment_id WHERE c.comment_approved= %d AND c.user_id = %d and c.comment_post_ID = %d AND m.meta_key = 'quiz_id' AND m.meta_value = %d)",'marks',1,$user_id,$question,$id));

                        if(!isset($marks)){$marks = 'N.A';}
                        
                        $csv[$i_bkup][] = $marks;
                        $i_bkup++;
                      }
                    }
                }
                $i = $i_bkup;
              break;
              case 'stats_question_marked_value':
                
                $quiz_dynamic = $this->fetch_cache($id.'_dynamic');
                if(empty($quiz_dynamic)){
                  $quiz_dynamic = get_post_meta($id,'vibe_quiz_dynamic',true);
                  $this->record_cache($id.'_dynamic',$quiz_dynamic);
                }

                if(!vibe_validate($quiz_dynamic)){
                    
                    $questions = $this->fetch_cache($id.'_questions');
                    if(empty($questions)){
                      $questions = get_post_meta($id,'vibe_quiz_questions',true);
                      $this->record_cache($id.'_questions',$questions);
                    }

                    $i_bkup = $i;
                    $question_options = array();
                    if(is_array($questions) && is_array($questions['ques'])){
                      foreach($questions['ques'] as $m=>$question){
                        
                        $title = $this->fetch_cache($question.'_title');
                        if(empty($title)){
                          $title = get_the_title($question);
                          $this->record_cache($question.'_title',$title);
                        }
                        
                        $question_options[$question] = $this->fetch_cache($question.'_options');
                        if(empty($question_options[$question])){
                          $question_options[$question] = get_post_meta($question,'vibe_question_options',true);
                          $this->record_cache($question.'_options',$question_options[$question]);
                        }
                        
                        if(!in_array($title,$csv_title)){
                          $csv_title[$i_bkup]=$title;  
                          $i_bkup++;
                        }
                      }
                      $i_bkup = $i;
                      $all_content = array();
                      foreach($questions['ques'] as $m=>$question){
                        
                        $content = bp_course_get_question_marked_answer($id,$question,$user_id);
                        
                        if(!empty($question_options[$question]) && is_array($question_options[$question])){
                          if(strpos($content, ',') !== false){
                            $marked_answers = explode(',',$content);
                            $content = '';
                            foreach($marked_answers as $answer){
                              if(is_numeric($answer)){
                                $content .= $question_options[$question][($answer-1)].', '; 
                              }
                            }
                          }else{
                            $content = $question_options[$question][($content-1)];  
                          }
                        }
                        if(!isset($content)){$content = 'N.A';}
                        $csv[$i_bkup][] = $content;
                        $i_bkup++;
                      }
                    }
                  $i = $i_bkup;
                }
              break;
              case 'stats_student_marks':
              $title=__('SCORE','vibe');
              if(!in_array($title,$csv_title))
                $csv_title[$i]=$title;
              if(in_array($post_type,array('quiz','wplms-assignment'))){
                $score = get_post_meta($id,$user_id,true);
                $csv[$i][]=$score;
              }else if($post_type == 'question'){
                $marks = $wpdb->get_results($wpdb->prepare("SELECT meta_value as score FROM {$wpdb->commentmeta} WHERE meta_key = %s AND comment_id IN ( SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved= %d AND user_id = %d and comment_post_ID = %d )",'marks',1,$user_id,$id));
                if(isset($marks) && is_array($marks) && is_object($marks[0]) && isset($marks[0]->score))  

                  $csv[$i][]=$marks[0]->score;
                else
                  $csv[$i][]=0;
              }
              break;
              default:
                do_action_ref_array('wplms_mod_stats_process', array( &$csv_title, &$csv,&$i,&$id,&$user_id,&$field,&$post_type));
              break;
            }
            $i++;
            }
          }
        }
      }  

      $dir = wp_upload_dir();
      $user_id = get_current_user_id();
      $file_name = 'download_'.$id.'_'.$user_id.'.csv';
      $filepath = $dir['basedir'] . '/stats/';
      if(!file_exists($filepath))
        mkdir($filepath,0755);

      $file = $filepath.$file_name;
      if(file_exists($file))
      unlink($file);

      if (($handle = fopen($file, "w")) !== FALSE) {
        fputcsv($handle,$csv_title);
        $rows = count($csv[0]);
          for($i=0;$i<$rows;$i++){
            $arr=array();
              foreach ($csv as $key=>$f) {
                array_push($arr,$f[$i]);
              }

            fputcsv($handle, $arr);  
          }
        }
        fclose($handle);

      $file_url = $dir['baseurl']. '/stats/'.$file_name;

      echo $file_url;

      die();
    }
/* == Display and download stats for Quiz/Question/Assignment == */
    function cpt_stats_query_results(){

      $type = $_POST['cpttype'];
      $id = $_POST['id'];
      global $wpdb;
      $count = apply_filters('wplms_starts_leader_board',10);
      switch($type){
        case 'quiz':
        case 'wplms-assignment':

          $maximum = $wpdb->get_results($wpdb->prepare("SELECT max(CAST(meta_value AS DECIMAL(10,0))) as max FROM {$wpdb->postmeta} WHERE meta_key REGEXP '^[0-9]+$' AND post_id = %d",$id));
          $minimum = $wpdb->get_results($wpdb->prepare("SELECT min(CAST(meta_value AS DECIMAL(10,0))) as min FROM {$wpdb->postmeta} WHERE meta_key REGEXP '^[0-9]+$' AND post_id = %d",$id));
          $avg = $wpdb->get_results($wpdb->prepare("SELECT avg(CAST(meta_value AS DECIMAL(10))) as avg FROM {$wpdb->postmeta} WHERE meta_key REGEXP '^[0-9]+$' AND post_id = %d",$id));

          $starting_point = 0;
          if(isset($_POST['starting_point']) && is_numeric($_POST['starting_point'])){
            $starting_point = $_POST['starting_point'];
          }

          $q = $wpdb->prepare("SELECT meta_key as user_id, meta_value as marks FROM {$wpdb->postmeta} WHERE meta_key REGEXP '^[0-9]+$' AND post_id = %d ORDER BY CAST(meta_value AS DECIMAL(10,2)) DESC LIMIT %d,%d",$id,$starting_point,$count);

          $results = $wpdb->get_results($q);
        break;

        case 'question':
          $results = $wpdb->get_results($wpdb->prepare("
            SELECT n.user_id as user_id,m.meta_value as marks
            FROM {$wpdb->comments} AS n
            LEFT JOIN {$wpdb->commentmeta} AS m ON n.comment_ID = m.comment_id
            WHERE  n.comment_post_ID = %d
            AND  n.comment_approved   = %d
            ORDER BY m.meta_value DESC
            LIMIT 0,%d
             ",$id,1,$count));
        break;
      }
      return $results;
    }
    function wplms_load_cpt_stats(){
      $type = $_POST['cpttype'];
      $id = $_POST['id'];
      $check = vibe_get_option('stats_visibility');
      $flag = 0;

      if(isset($check)){
        switch($check){
          case '1':
          if(!is_user_logged_in())
            $flag = 1;
          break;
          case '2':
             if(!current_user_can('edit_posts'))
              $flag = 1;
          break;
          case '3':
          $user_id = get_current_user_id();
            $instructors = apply_filters('wplms_course_instructors',get_post_field('post_author',$id,'raw'),$id);
            if((!is_array($instructors) || !in_array($user_id,$instructors)) && !current_user_can( 'manage_options' ))
              $flag = 1;
          break;
        }
      }
      
      if($flag){
        echo '<div class="message stats_content"><p>'.__('User not allowed to access stats','vibe').'</p></div>';
        die();
      }

      $results = $this->cpt_stats_query_results();
      $count = apply_filters('wplms_starts_leader_board',10);

      if(!is_array($results)){
        echo '<div class="message stats_content"><p>'.__('No data available','vibe').'</p></div>';
        die();
      }

      foreach($results as $result){
        if(is_numeric($result->marks) && is_numeric($result->user_id) && $result->user_id)
        $user_marks[$result->user_id] = $result->marks;
      }

      echo '<div class="stats_content">
            <h2>'.__('Stats','vibe').'</h2><hr />';
      if(is_array($user_marks)){

        $cnt=count($user_marks);if(!$cnt)$cnt=1;
        if(empty($avg)){
          $average = round(array_sum($user_marks)/$cnt,2);
        }else{
          $average = round($avg[0]->avg,2);
        }
        if(empty($maximum)){
          $max = max($user_marks);
        }else{
          $max = $maximum[0]->max;
        }
        if(empty($minimum)){
          $min = min($user_marks);
        }else{
          $min = $minimum[0]->min;
        }
        
        echo '
            <div class="cpit_chat_graph"><canvas id="stats_chart"></canvas></div>
            <h4>'.__('Average','vibe').'<span class="right">'.$average.'</span></h4>
            <h4>'.__('Max','vibe').'<span class="right">'.$max.'</span></h4>
            <h4>'.__('Min','vibe').'<span class="right">'.$min.'</span></h4>';
      }else{
        echo '<div class="message">'.__('N.A','vibe').'</div>';
      }

      echo '<h3 class="heading"><span>'.__('Leaderboard','vibe').'</span></h3>';

      //arsort($user_marks);     
      if(is_array($user_marks)){
        echo '<ol class="marks">';
        
        foreach($user_marks as $userid=>$marks){
          if($count){
            echo '<li>'.bp_core_get_user_displayname($userid).'<span class="right">'.$marks.'</span></li>';
          }else{
            break;
          }
        }
        echo '</ol>';
        echo '<a id="load_more_cpt_user_results" data-starting_point="'.($starting_point+$count).'" class="link" style="text-align: center;width: 100%;display: inline-block;">'.__('Load more','vibe').'</a>';
      }
      if(is_user_logged_in()){
        $user_id = get_current_user_id();
        $instructors = apply_filters('wplms_course_instructors',get_post_field('post_author',$id,'raw'),$id);
        if((is_array($instructors) && in_array($user_id,$instructors)) || current_user_can( 'manage_options' )){
          echo '<h3 class="heading" id="download_stats_options">'.__('Download Stats','vibe').'<i class="icon-download-3 right"></i></h3>';

          $stats_array=apply_filters('wplms_download_mod_stats_fields',array(
            'stats_student_start_date'=>__('Start Date/Time','vibe'),
            'stats_student_finish_date'=>__('End Date/Time','vibe'),
            'stats_student_id'=>__('ID','vibe'),
            'stats_student_name'=>__('Student Name','vibe'),
            'stats_student_marks'=>__('Score','vibe'),
            ),$type);
          if($type == 'quiz'){
            $stats_array['stats_question_scores'] = __('Question scores (* for static quizzes only)','vibe');
            $stats_array['stats_question_marked_value'] = __('Question Answers (* for static quizzes only, useful for surveys)','vibe');
            
          }

          echo '<div class="select_download_options">
          <ul>';
          foreach($stats_array as $key=>$stat){
            echo '<li><input type="checkbox" id="'.$key.'" class="field" value="1" /><label for="'.$key.'">'.$stat.'</label></li>';
          }
          echo '</ul>';
          echo '<br class="clear" /><select id="stats_students">
          <option value="all_students">'.__('All students','vibe').'</option>
          <option value="finished_students">'.__('Students who finished','vibe').'</option>
          </select>';
          wp_nonce_field('security','stats_security');
          echo '<a class="button full" id="download_mod_stats" data-type="'.$type.'" data-id="'.$id.'"><i class="icon-download-3"></i> '.__('Process Stats','vibe').'</a>
          </div>';
        } 
      }
      echo '</div>';
      ?><script>
        var cptchatjs='<?php echo plugins_url('js/Chart.min.js',__FILE__); ?>';
        </script>       
      <?php
      die();
    }


    function wplms_cpt_stats_graph(){
      $type = $_POST['cpttype'];
      $id = $_POST['id'];
      global $wpdb,$bp;
      switch($type){
        case 'quiz':
        case 'wplms-assignment':



          $q = apply_filters('wplms_cpt_stats_graph_query',$wpdb->prepare("
            SELECT
             SUM(CASE WHEN m.meta_value <= 20 THEN 1 ELSE 0 END) as 'less_25',
             SUM(CASE WHEN m.meta_value > 25 and m.meta_value <=50 THEN 1 ELSE 0 END) as '25_50',
             SUM(CASE WHEN m.meta_value > 50 and m.meta_value <=75 THEN 1 ELSE 0 END) as '50_75',
             SUM(CASE WHEN m.meta_value > 75 and m.meta_value <=100 THEN 1 ELSE 0 END) as '75_100'
            FROM {$bp->activity->table_name_meta} as m
            INNER JOIN {$bp->activity->table_name} as a 
            ON a.id = m.activity_id
            WHERE m.meta_key = 'percentage'
            AND a.secondary_item_id = %d ",
            $id),$type,$id);

          $results = $wpdb->get_results($q,ARRAY_A);
         
          echo json_encode(array(
            'labels'=>array(__('Less than 25','vibe'),
              __('More than 25 Less than 50','vibe'),__('More than 50 Less than 75','vibe'),
              __('More than 75 Less than 100','vibe')
            ),
            'data'=>array_values($results[0])
          ));
          die();
        break;

        case 'question':
          $results = $wpdb->get_results($wpdb->prepare("
            SELECT n.user_id as user_id,m.meta_value as marks
            FROM {$wpdb->comments} AS n
            LEFT JOIN {$wpdb->commentmeta} AS m ON n.comment_ID = m.comment_id
            WHERE  n.comment_post_ID = %d
            AND  n.comment_approved   = %d
            ORDER BY m.meta_value DESC
            LIMIT 0,%d
             ",$id,1,$count));
        break;
      }

      die();
    }

    function wplms_load_more_cpt_stats(){

      $results = $this->cpt_stats_query_results();
      $count = apply_filters('wplms_starts_leader_board',10);
      if(!is_array($results)){
        die();
      }

      foreach($results as $result){
        if(is_numeric($result->marks) && is_numeric($result->user_id) && $result->user_id)
        $user_marks[$result->user_id] = $result->marks;
      }

      
      foreach($user_marks as $userid=>$marks){
          if($count){
            echo '<li>'.bp_core_get_user_displayname($userid).'<span class="right">'.$marks.'</span></li>';
          }else{
            break;
          }
      }
      die();
    }
    function unit_traverse(){

      $unit_id= $_POST['id'];
      $course_id = $_POST['course_id'];
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security')){
         _e('Security check Failed. Contact Administrator.','vibe');
         die();
      }

      //verify unit in course
      $units = bp_course_get_curriculum_units($course_id);
      if(!in_array($unit_id,$units)){
        _e('Unit not in Course','vibe');
        die();
      }

      // Check For partially free course
      $check_partial_free_course = apply_filters('wplms_check_partial_free_course_access',1,$unit_id);
      if( !$check_partial_free_course )
        die();

      // Check if user has taken the course
      $user_id = get_current_user_id();
      $coursetaken=bp_course_get_user_expiry_time($user_id,$course_id);

      if(!isset($_COOKIE['course'])) {
        if($coursetaken>time()){
          setcookie('course',$course_id,$expire,'/');
          $_COOKIE['course'] = $course_id;
        }else{
          $pid=get_post_meta($course_id,'vibe_product',true);
          $pid=apply_filters('wplms_course_product_id',$pid,$course_id,-1); // $id checks for Single Course page or Course page in the my courses section
          if(is_numeric($pid))
            $pid=get_permalink($pid);

          echo '<div class="message"><p>'.__('Course Expired.','vibe').'<a href="'.$pid.'" class="link alignright">'.__('Click to renew','vibe').'</a></p></div>';
          die();
        }
      }
      
      if(isset($coursetaken) && $coursetaken){

        if(!bp_course_check_unit_complete($unit_id,$user_id,$course_id)){ // IF unit not completed by user
            // Drip Feed Check    
            $drip_enable= apply_filters('wplms_course_drip_switch',get_post_meta($course_id,'vibe_course_drip',true),$course_id);

            if(vibe_validate($drip_enable)){

                $drip_duration = get_post_meta($course_id,'vibe_course_drip_duration',true);
                $drip_duration_parameter = apply_filters('vibe_drip_duration_parameter',86400,$course_id);

                $unitkey = array_search($unit_id,$units);

                for($i=($unitkey-1);$i>=0;$i--){
                    if(bp_course_get_post_type($units[$i]) == 'unit' ){
                      $pre_unit_key = $i;
                      break;
                    }
                }

                $total_drip_duration = apply_filters('vibe_total_drip_duration',($drip_duration*$drip_duration_parameter),$course_id,$unit_id,$units[$pre_unit_key]);

                if($unitkey == 0){ // Start of Course
                    $pre_unit_time = bp_course_get_drip_access_time($units[$unitkey],$user_id,$course_id);
                    if(!isset($pre_unit_time) || $pre_unit_time ==''){
                        if(is_numeric($units[1])){
                          bp_course_update_drip_access_time($units[$unitkey],$user_id,time(),$course_id); 
                          //Parmas : Next Unit, Next timestamp, course_id, userid
                          do_action('wplms_start_unit',$units[$unitkey],$course_id,$user_id,$units[1],(time()+$total_drip_duration));
                        }
                    }

                }else{
                        //Continuation of Course
                        $pre_unit_time=bp_course_get_drip_access_time($units[$pre_unit_key],$user_id,$course_id);
                        if(!empty($pre_unit_time)){
                            
                              $value = $pre_unit_time + $total_drip_duration;
                              
                              $value = apply_filters('wplms_drip_value',$value,$units[$pre_unit_key],$course_id,$units[$unitkey],$units);

                              //print_r(date('l jS \of F Y h:i:s A',$value).' > '.date('l jS \of F Y h:i:s A',time()));
                             if($value > time()){
                                  $element = apply_filters('wplms_drip_feed_element_in_message',__('Unit','vibe'),$course_id);
                                  echo '<div class="message"><p>'.sprintf(__('%s will be available in %s','vibe'),$element,tofriendlytime($value-time())).'</p></div>';
                                  echo do_shortcode('[countdown_timer seconds="'.($value-time()).'" size="3"]');
                                  die();
                                }else{
                                    $pre_unit_time=bp_course_get_user_unit_completion_time($user_id,$units[$unitkey],$course_id);
                                    if(!isset($pre_unit_time) || $pre_unit_time ==''){
                                      bp_course_update_unit_user_access_time($units[$unitkey],$user_id,time(),$course_id);

                                      //Parmas : Next Unit, Next timestamp, course_id, userid
                                      do_action('wplms_start_unit',$units[$unitkey],$course_id,$user_id,$units[$unitkey+1],(time()+$total_drip_duration));
                                    }
                                } 

                          }else{
                                if(isset($pre_unit_key)){
                                  $completed = bp_course_get_user_unit_completion_time($user_id,$units[$pre_unit_key],$course_id);

                                  if(!empty($completed)){
                                      bp_course_update_unit_user_access_time($units[$pre_unit_key],$user_id,time(),$course_id);
                                      $pre_unit_time = time();
                                      $value = $pre_unit_time + $total_drip_duration;
                                      $value = apply_filters('wplms_drip_value',$value,$units[$pre_unit_key],$course_id,$units[$unitkey],$units);
                                      $element = apply_filters('wplms_drip_feed_element_in_message',__('Unit','vibe'),$course_id);
                                      echo '<div class="message"><p>'.sprintf(__('%s will be available in %s','vibe'),$element,tofriendlytime($value-time())).'</p></div>';
                                      echo do_shortcode('[countdown_timer seconds="'.($value-time()).'" size="3"]');
                                      die();
                                    }else{
                                      $element = apply_filters('wplms_drip_feed_element_in_message',__('Unit','vibe'),$course_id);
                                      echo '<div class="message"><p>'.sprintf(__('%s can not be accessed.','vibe'),$element).'</p></div>';
                                    }
                              }else{
                                $element = apply_filters('wplms_drip_feed_element_in_message',__('Unit','vibe'),$course_id);
                                echo '<div class="message"><p>'.sprintf(__('%s can not be accessed.','vibe'),$element).'</p></div>';

                              }
                              
                              die();
                        }    //Empty pre-unit time
                    }
                }  // Drip enabled
            }
          // END Drip Feed Check  
          
          echo '<div id="unit" class="'.bp_course_get_post_type($unit_id).'_title" data-unit="'.$unit_id.'">';
            do_action('wplms_unit_header',$unit_id,$course_id);

            $duration = get_post_meta($unit_id,'vibe_duration',true);
            $unit_duration_parameter = apply_filters('vibe_unit_duration_parameter',60,$unit_id);

            if($duration){
              do_action('wplms_course_unit_meta',$unit_id);
              echo '<span><i class="icon-clock"></i> '.(($duration<9999)?tofriendlytime(($unit_duration_parameter*$duration)):__('Unlimited','vibe')).'</span>';
            }

          echo '<br /><h1>'.get_the_title($unit_id).'</h1>';
              the_sub_title($unit_id);
          echo '<div class="clear"></div>';
          echo '</div>';
            
                  the_unit($unit_id); 
          
                  $unit_class='unit_button';
                  $hide_unit=0;
                  $nextunit_access = apply_filters('bp_course_next_unit_access',true,$course_id);

                  $k=array_search($unit_id,$units);

                  $done_flag=bp_course_get_user_unit_completion_time($user_id,$unit_id,$course_id);

                  $next=$k+1;
                  $prev=$k-1;
                  $max=count($units)-1;

                  echo  '<div class="unit_prevnext"><div class="col-md-3">';
                  if($prev >=0){

                    if(bp_course_get_post_type($units[$prev]) == 'quiz'){
                      echo '<a href="#" data-unit="'.$units[$prev].'" class="unit '.$unit_class.'"><span>'.__('Previous Quiz','vibe').'</span></a>';
                    }else    
                      echo '<a href="#" id="prev_unit" data-unit="'.$units[$prev].'" class="unit unit_button"><span>'.__('Previous Unit','vibe').'</span></a>';
                  }
                  echo '</div>';
                  $quiz_passing_flag = true;
                  echo  '<div class="col-md-6">';
                  if(bp_course_get_post_type($units[($k)]) == 'quiz'){
                    
                    //QUIZ STATUS
                    $quiz_status = get_user_meta($user_id,$units[($k)],true);
                    //
                    if(is_numeric($quiz_status)){
                       $quiz_passing_flag = apply_filters('wplms_next_unit_access',true,$units[($k)]);
                      if($quiz_status < time()){
                        echo '<a href="'.bp_loggedin_user_domain().BP_COURSE_SLUG.'/'.BP_COURSE_RESULTS_SLUG.'/?action='.$units[($k)].'" class="quiz_results_popup"><span>'.__('Check Results','vibe').'</span></a>';
                      }else{
                          $quiz_class = apply_filters('wplms_in_course_quiz','');
                          echo '<a href="'.get_permalink($units[($k)]).'" class=" unit_button '.$quiz_class.' continue">'.__('Continue Quiz','vibe').'</a>';
                      }
                    }else{
                        $quiz_class = apply_filters('wplms_in_course_quiz','');
                        echo apply_filters('wplms_start_quiz_button','<a href="'.get_permalink($units[($k)]).'" class=" unit_button '.$quiz_class.'"><span>'.__('Start Quiz','vibe').'</span></a>',$units[($k)]);
                    }
                  }else  
                      echo ((isset($done_flag) && $done_flag)?'': apply_filters('wplms_unit_mark_complete','<a href="#" id="mark-complete" data-unit="'.$units[($k)].'" class="unit_button"><span>'.__('Mark this Unit Complete','vibe').'</span></a>',$unit_id,$course_id));

                  echo '</div>';

                  echo  '<div class="col-md-3">';

                  if($next <= $max){
                      if(empty($nextunit_access)){
                          $hide_unit=1;
                      
                      if(isset($done_flag) && $done_flag){
                            $unit_class .=' ';
                            $hide_unit=0;
                      }else{
                            $unit_class .=' hide';
                            $hide_unit=1;
                      }
                    }

                    if(bp_course_get_post_type($units[$next]) == 'quiz'){
                        if($quiz_passing_flag)
                            echo '<a href="#" id="next_quiz" '.(($hide_unit)?'':'data-unit="'.$units[$next].'"').' class="unit '.$unit_class.'"><span>'.__('Proceed to Quiz','vibe').'</span></a>';
                    }else {
                        if($quiz_passing_flag){
                            echo '<a href="#" id="next_unit" '.(($hide_unit)?'':'data-unit="'.$units[$next].'"').' class="unit '.$unit_class.'"><span>'.__('Next Unit','vibe').'</span></a>';
                        }
                    }  
                    
                  }
                  echo '</div></div>';
            }
            die();
    }

/* ===== In Course Quiz ===== */

    function incourse_submit_quiz(){
      $quiz_id= $_POST['quiz_id'];
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($quiz_id)){
         _e('Security check Failed. Contact Administrator.','vibe');
         die();
      }

      $user_id = get_current_user_id();
      $access = get_user_meta($user_id,$quiz_id,true);
      if(!isset($access) || !is_numeric($access)){
        _e('Invalid submission time.','vibe');
         die();
      }
      
      update_user_meta($user_id,$quiz_id,time());
      update_post_meta($quiz_id,$user_id,0);
      bp_course_update_user_quiz_status($user_id,$quiz_id,0);

      

      bp_course_quiz_auto_submit($quiz_id,$user_id);
      do_action('wplms_submit_quiz',$quiz_id,$user_id,$answers);
      $get_message = trim(get_post_meta($quiz_id,'vibe_quiz_message',true));
      ob_start();
      do_action('wplms_after_quiz_message',$quiz_id,$user_id);
      $after_quiz = ob_get_clean();
      
      if(!empty($after_quiz)){
        $get_message .= $after_quiz; 
      }
      
      $course_id = get_post_meta($quiz_id,'vibe_quiz_course',true);        
      $stop_progress = apply_filters('bp_course_stop_course_progress',true,$course_id);

      $flag = apply_filters('wplms_next_unit_access',true,$quiz_id);
      if(is_numeric($course_id) && $stop_progress && $flag){
        $curriculum=bp_course_get_curriculum_units($course_id);
        $key = array_search($quiz_id,$curriculum);
        if($key <=(count($curriculum)-1) ){  // Check if not the last unit
          $key++;
          echo $curriculum[$key].'##';
        }
      }else{
          echo '##';
        }
      
      echo ' ';
      echo apply_filters('the_content',$get_message);
      die();
    }

// Single Quiz

/*==================================================================
  SINGLE QUIZ REVAMP
*/
    function submit_quiz(){
      
        $quiz_id= $_POST['quiz_id'];
        $user_id = get_current_user_id();
        
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'start_quiz') ){
            _e('security check failed.','vibe');
            die();
        }

        $access = get_user_meta($user_id,$quiz_id,true);

        if(!isset($access) || !is_numeric($access)){
            _e('Invalid submission time.','vibe');
            die();
        }
 
      update_user_meta($user_id,$quiz_id,time());
      update_post_meta($quiz_id,$user_id,0);
      bp_course_update_user_quiz_status($user_id,$quiz_id,0);

      

      $course_id = get_post_meta($quiz_id,'vibe_quiz_course',true);

      if(!empty($course_id)){ // Course progressbar fix for single quiz
        
        $curriculum = bp_course_get_curriculum_units($course_id);
        $per = round((100/count($curriculum)),2);

        $progress = bp_course_get_user_progress($user_id,$course_id);
        if(empty($progress))
          $progress = 0;

        $new_progress = $progress+$per;
       
        if($new_progress > 100){
          $new_progress = 100;
        }
        bp_course_update_user_progress($user_id,$course_id,$new_progress);
      }

      bp_course_quiz_auto_submit($quiz_id,$user_id);
      do_action('wplms_submit_quiz',$quiz_id,$user_id,$answers);
      die();
    }

    function begin_quiz(){
        $id= $_POST['id'];
        if ( isset($_POST['start_quiz']) && wp_verify_nonce($_POST['start_quiz'],'start_quiz') ){

          $user_id = get_current_user_id();

          $quiz_access_flag=apply_filters('bp_course_api_check_quiz_lock',true,$id,$user_id,'site');
          if(!$quiz_access_flag){
            _ex('Quiz already in progress in App, contact site administrator to proceed','quiz lock flag check for App and Site','vibe');
            die();
          }


          $quiztaken=get_user_meta($user_id,$id,true);
          

           if(!isset($quiztaken) || !$quiztaken){
              
              $quiz_duration_parameter = apply_filters('vibe_quiz_duration_parameter',60,$id);
              $quiz_duration = get_post_meta($id,'vibe_duration',true) * $quiz_duration_parameter; // Quiz duration in seconds
              $expire=time()+$quiz_duration;
              add_user_meta($user_id,$id,$expire);
              
              $quiz_questions = bp_course_get_quiz_questions($id,$user_id);
              
              the_quiz('quiz_id='.$id.'&ques_id='.$quiz_questions['ques'][0]);

              echo '<script>var all_questions_json = '.json_encode($quiz_questions['ques']).'</script>';

              do_action('wplms_start_quiz',$id,$user_id);
           }else{

            if($quiztaken > time()){
              
              $quiz_questions = bp_course_get_quiz_questions($id,$user_id);
              
              the_quiz('quiz_id='.$id.'&ques_id='.$quiz_questions['ques'][0]);
              echo '<script>var all_questions_json = '.json_encode($quiz_questions['ques']).'</script>';
            }else{
              echo '<div class="message error"><h3>'.__('Quiz Timed Out .','vibe').'</h3>'; 
              echo '<p>'.__('If you want to attempt again, Contact Instructor to reset the quiz.','vibe').'</p></div>
              <script>jQuery(document).ready(function(){jQuery(".quiz_timer").trigger("end");});</script>';
            }
            
           }

       }else{
          echo '<h3>'.__('Quiz Already Attempted.','vibe').'</h3>'; 
          echo '<p>'.__('Security Check Failed. Contact Site Admin.','vibe').'</p>'; 
       }
       die();
    }  

    function save_question_marked_answer(){
        if ( !isset($_POST['security']) || (!wp_verify_nonce($_POST['security'],'start_quiz') && !wp_verify_nonce($_POST['security'],'security'))){
                _e('Security check Failed. Contact Administrator.','vibe');
                die();
        }
        $user_id = get_current_user_id();
        $quiz_id = $_POST['quiz_id'];
        $question_id = $_POST['question_id'];
        if(!is_numeric($quiz_id) || !is_numeric($question_id)){
            _e('Security check Failed. Contact Administrator.','vibe');
            die();
        }
        

        if(empty($this->check_quiz_access[$quiz_id])){
            $start = get_user_meta($user_id,$quiz_id,true);
            $minutes=intval(get_post_meta($quiz_id,'vibe_duration',true));
            $quiz_duration_parameter = apply_filters('vibe_quiz_duration_parameter',60,$quiz_id); 
            if( ($start+$minutes*$quiz_duration_parameter) > time())   {
                $this->check_quiz_access[$quiz_id] = true;
            }else{
                 $this->check_quiz_access[$quiz_id] = false;
            }
        }
        //print_r($question_id.' -> '.$this->check_quiz_access[$quiz_id]);
        //if(!empty($this->check_quiz_access[$quiz_id])){
            bp_course_save_question_quiz_answer($quiz_id,$question_id,$user_id,$answer);
        //}

        die();
    }
// After begin quiz, get unmarked quesitons

    function bp_course_check_unanswered_questions(){
        $questions = json_decode(stripslashes($_POST['questions']));
        $user_id = get_current_user_id();
        $answers = array();
        $quiz_id = intval($_POST['id']);
        foreach($questions as $question){
            global $wpdb; $question->id = intval($question->id);
            $val = bp_course_get_question_marked_answer($quiz_id,$question->id,$user_id);
            if(!empty($val))
              $answers[]=array('question_id'=>$question->id,'value'=>$val);
        }
        if(count($answers)){
           print_r(json_encode($answers));
        }
        die();
    }

      function quiz_question(){
          
          $quiz_id= $_POST['quiz_id'];
          $ques_id= $_POST['ques_id'];

          if ( isset($_POST['start_quiz']) && wp_verify_nonce($_POST['start_quiz'],'start_quiz') ){ // Same NONCE just for validation

            $user_id = get_current_user_id();
            $quiztaken = get_user_meta($user_id,$quiz_id,true);

             if(isset($quiztaken) && $quiztaken){
                if($quiztaken > time()){
                    the_quiz('quiz_id='.$quiz_id.'&ques_id='.$ques_id);  
                }else{
                  echo '<div class="message error"><h3>'.__('Quiz Timed Out .','vibe').'</h3>'; 
            echo '<p>'.__('If you want to attempt again, Contact Instructor to reset the quiz.','vibe').'</p></div>';
                }
                
             }else{
                echo '<div class="message info"><h3>'.__('Start Quiz to begin quiz.','vibe').'</h3>'; 
                echo '<p>'.__('Click "Start Quiz" button to start the Quiz.','vibe').'</p></div>';
             }

         }else{
                    echo '<div class="message error"><h3>'.__('Quiz not active.','vibe').'</h3>'; 
                    echo '<p>'.__('Contact your instructor or site admin.','vibe').'</p></div>';
         }
         die();
      }  

      function continue_quiz(){
          $user_id = get_current_user_id();
          $quiztaken=get_user_meta($user_id,get_the_ID(),true);
          
          if ( isset($_POST['start_quiz']) && wp_verify_nonce($_POST['start_quiz'],'start_quiz') ){ // Same NONCE just for validation
           if(isset($quiztaken) && $quiztaken && $quiztaken > time()){
              $questions = bp_course_get_quiz_questions($quiz_id,$user_id);
              the_quiz('quiz_id='.get_the_ID().'&ques_id='.$questions['ques'][0]);  
              
           }else{
             echo '<div class="message error"><h3>'.__('Quiz Timed Out .','vibe').'</h3>'; 
            echo '<p>'.__('If you want to attempt again, Contact Instructor to reset the quiz.','vibe').'</p></div>';
           }
         }else{
              echo '<div class="message error"><h3>'.__('Quiz Already Attempted .','vibe').'</h3>'; 
              echo '<p>'.__('If you want to attempt again, Contact Instructor to reset the quiz.','vibe').'</p></div>'; 
         }
          die();
      }  

    function incourse_start_quiz(){
      $quiz_id= $_POST['quiz_id'];
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($quiz_id)){
         _e('Security check Failed. Contact Administrator.','vibe');
         die();
      }

      $user_id = get_current_user_id();
      

      $quiz_access_flag=apply_filters('bp_course_api_check_quiz_lock',true,$quiz_id,$user_id,'site');
      if(!$quiz_access_flag){
        _ex('Quiz already in progress, contact site, please retry after sometime.','quiz lock flag check for App and Site','vibe');
        die();
      }

      do_action('wplms_before_quiz_begining',$quiz_id);
      
      $get_questions = bp_course_get_quiz_questions($quiz_id,$user_id);

      if(!is_array($get_questions) || !is_array($get_questions['ques']) || !is_array($get_questions['marks'])){
        _e('Questions not set.','vibe');
        die();
      }

      $questions=$get_questions['ques'];
      $marks=$get_questions['marks'];
      $posts_per_page = apply_filters('wplms_incourse_quiz_per_page',10);
      $page = $_POST['page'];

      if(!isset($page) || !is_numeric($page) || !$page){
        $page = 1;
       // Add user to quiz : Quiz attempted by user
        update_post_meta($quiz_id,$user_id,0);
        $quiz_duration_parameter = apply_filters('vibe_quiz_duration_parameter',60,$quiz_id);
        $quiz_duration = get_post_meta($quiz_id,'vibe_duration',true) * $quiz_duration_parameter; // Quiz duration in seconds
        $expire=time()+$quiz_duration;
        update_user_meta($user_id,$quiz_id,$expire);
        do_action('wplms_start_quiz',$quiz_id,$user_id);
        // Start Quiz Notifications
      }

      $args = apply_filters('wplms_in_course_quiz_args',array('post__in' => $questions,'post_type'=>'question','posts_per_page'=>$posts_per_page,'paged'=>$page,'orderby'=>'post__in'));

      $the_query = new WP_Query($args);
      $quiz_questions = array();

      if ( $the_query->have_posts() ) {
        echo '<script>var all_quiz_questions_local =  all_progress_json = all_questions_json = '.json_encode($questions).'</script>';
        while ( $the_query->have_posts() ) {
          $the_query->the_post();
          global $post;
          $loaded_questions[]=get_the_ID();
          $key = array_search(get_the_ID(),$questions);
          $hint = get_post_meta(get_the_ID(),'vibe_question_hint',true);
          $type = get_post_meta(get_the_ID(),'vibe_question_type',true);

          echo '<div class="in_question " data-ques="'.$post->ID.'">';
          echo '<i class="marks">'.(isset($marks[$key])?'<i class="icon-check-5"></i>'.$marks[$key]:'').'</i>';
          echo '<div class="question '.$type.'">';
          the_content();
          if(isset($hint) && strlen($hint)>5){
            echo '<a class="show_hint tip" tip="'.__('SHOW HINT','vibe').'"><span></span></a>';
            echo '<div class="hint"><i>'.__('HINT','vibe').' : '.apply_filters('the_content',$hint).'</i></div>';
          }
          echo '</div>';

          $content = bp_course_get_question_marked_answer($quiz_id,$post->ID,$user_id);
          if(!empty($content))
            $content =  array($content);
          else
            $content = array();
          
          switch($type){
            case 'truefalse': 
            case 'single': 
            case 'survey': 
            case 'multiple': 
            case 'sort':
            case 'match':
               $options = vibe_sanitize(get_post_meta(get_the_ID(),'vibe_question_options',false));

              if($type == 'truefalse')
                $options = array( 0 => __('FALSE','vibe'),1 =>__('TRUE','vibe'));

              if(isset($options) || $options){

            
                echo '<ul class="question_options '.$type.'">';
                  if($type=='single' || $type=='survey'){
                    foreach($options as $key=>$value){

                      echo '<li>
                              <div class="radio">
                                <input type="radio" id="'.$post->post_name.$key.'" class="ques'.$post->ID.'" name="'.$post->ID.'" value="'.($key+1).'" '.(in_array(($key+1),$content)?'checked':'').'/>
                                <label for="'.$post->post_name.$key.'">'.do_shortcode($value).'</label>
                              </div>  
                            </li>';
                    }
                  }else if($type == 'sort'){
                    foreach($options as $key=>$value){
                      echo '<li id="'.($key+1).'" class="ques'.$post->ID.' sort_option">
                                  <label for="'.$post->post_name.$key.'"><span></span> '.do_shortcode($value).'</label>
                              </li>';
                    }        
                  }else if($type == 'match'){
                    foreach($options as $key=>$value){
                      echo '<li id="'.($key+1).'" class="ques'.$post->ID.' match_option">
                                  <label for="'.$post->post_name.$key.'"><span></span> '.do_shortcode($value).'</label>
                              </li>';
                    }        
                  }else if($type == 'truefalse'){
                    foreach($options as $key=>$value){

                      echo '<li>
                              <div class="radio">
                                <input type="radio" id="'.$post->post_name.$key.'" class="ques'.$post->ID.'" name="'.$post->ID.'" value="'.$key.'" '.(in_array($key,$content)?'checked':'').'/>
                                <label for="'.$post->post_name.$key.'">'.$value.'</label>
                              </div>  
                            </li>';
                    }       
                  }else{
                    foreach($options as $key=>$value){
                      echo '<li>
                              <div class="checkbox">
                                <input type="checkbox" class="ques'.$post->ID.'" id="'.$post->post_name.$key.'" name="'.$post->ID.$key.'" value="'.($key+1).'" '.(in_array(($key+1),$content)?'checked':'').'/>
                                <label for="'.$post->post_name.$key.'">'.do_shortcode($value).'</label>
                              </div>  
                            </li>';
                    }
                  }  
                echo '</ul>';
              }
            break; // End Options
            case 'fillblank': 
            break;
            case 'select':  
            break;
            case 'smalltext': 
              echo '<input type="text" name="'.$k.'" class="ques'.$k.' form_field" value="'.($content?$content:'').'" placeholder="'.__('Type Answer','vibe').'" />';
            break;
            case 'largetext': 
              echo '<textarea name="'.$k.'" class="ques'.$k.' form_field" placeholder="'.__('Type Answer','vibe').'">'.($content?$content:'').'</textarea>';
            break;
            default:
              do_action('wplms_in_course_quiz_question_fields',$post->ID,$options);
            break;

          }
          do_action('wplms_quiz_question',$post->ID,$quiz_id);
          echo '</div>';
        } 
         $count = count($questions);
          if($posts_per_page < $count){
              echo '<div class="pagination"><label>'.__('PAGES','vibe').'</label>
              <ul>';
              $max =  $count/$posts_per_page;
              if(($count%$posts_per_page)){
                $max++;
              }
              for($i=1;$i<=$max;$i++){
                 if($page == $i){
                  echo '<li><span>'.$i.'</span></li>';
                 }else{
                  echo '<li><a class="quiz_page">'.$i.'</a><li>';
                 }
              }
              echo '</ul>
              </div>';
          } 
        echo '<script>var questions_json = '.json_encode($loaded_questions).'</script>';
      }
      wp_reset_postdata();  
      die();
    }

    function wplms_retake_inquiz(){
       $quiz_id= $_POST['quiz_id'];
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($quiz_id)){
           _e('Security check Failed. Contact Administrator.','vibe');
           die();
        }
        if(function_exists('bp_course_student_quiz_retake')){
          $user_id = get_current_user_id();
          bp_course_student_quiz_retake(array('quiz_id'=>$quiz_id,'user_id'=>$user_id));
        }else{
          student_quiz_retake(array('quiz_id'=>$quiz_id));  
        }
        
        die();
    }

    // Notes & Discussion Unit comments :

    function wplms_add_unit_comment(){
      $unit_id= $_POST['unit_id'];
      $content = $_POST['text'];
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($unit_id)){
         _e('Security check Failed. Contact Administrator.','vibe');
         die();
      }
      if(strlen($content) < 2){
        echo '<div class="message">'.__('Unable to add message','vibe').'</div>';
        die();
      }

      $time = current_time('mysql');
      $user_id = get_current_user_id();
      $args = array(
        'comment_post_ID' => $unit_id,
        'user_id' => $user_id,
        'comment_type' => 'public',
        'comment_content' => $content,
        'comment_date' => $time,
        'comment_approved' => 1,
        );
      if(isset($_POST['parent']) && is_numeric($_POST['parent'])){
        $args['comment_parent']=$_POST['parent'];
      } 
      $comment_id = wp_new_comment($args);
      $args['comment_id']=$comment_id;
      global $wpdb;
      $approved_status = $wpdb->get_var("SELECT comment_approved FROM {$wpdb->comments} WHERE comment_ID = $comment_id");
      if(is_numeric($comment_id)){

          echo '<li class="public byuser zoom load" id="comment-'.$comment_id.'">
                <div id="div-comment-'.$comment_id.'" class="unit-comment-body '.(empty($approved_status)?'unapproved':'approved').'">
                  <div class="unit-comment-author vcard">
                    '.bp_core_fetch_avatar( array(
                    'item_id' => $user_id,
                    'type' => 'thumb',
                    )).'
                  </div>
                  <div class="unit-comment-text">  
                    <cite class="fn">'.bp_core_get_userlink($user_id).'</cite>  
                    <div class="comment-meta commentmetadata">
                      <a href="'.get_permalink($unit_id).'">'.__('JUST NOW','vibe').'</a>'.(empty($approved_status)?' &nbsp; <a>( '._x('Sent for Approval.','when unit comment is not approved','vibe').' ) </a>':'').'  
                    </div>
                    <p>'.do_shortcode($content).'</p>
                  </div>  
                </div>
              </li>';   
          
          do_action('wplms_course_unit_comment',$unit_id,$user_id,$comment_id,$args);     
      }else{
        echo '<div class="message">'.__('Unable to add message','vibe').'</div>';
      }
      die();
    }

    function wplms_load_unit_comments(){
      $unit_id= $_POST['unit_id'];
      $page= $_POST['page'];
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($unit_id) || !is_numeric($page)){
         _e('Security check Failed. Contact Administrator.','vibe');
         die();
      }    
      $per_page = get_option('comments_per_page');
      if(!is_numeric($per_page))
        $per_page = 5;
      
      $offset = $per_page*$page;

      //Gather comments for a specific page/post 
      $comments = get_comments(array(
          'post_id' => $unit_id,
          'status' => 'approve',
          'offset' => $offset,
          //'number' => $per_page,
      ));

      //Display the list of comments
      wp_list_comments(array(
          'page' => ($page+1),
          'per_page' => $per_page, //Allow comment pagination
          'avatar_size' => 120,
          'callback' => 'wplms_unit_comment' //Show the latest comments at the top of the list
      ), $comments);        

      die();
    }

    // BuddyPress Star Fix 

    function bp_course_messages_star(){
      if(function_exists('bp_messages_star_set_action') && is_numeric($_POST['message_id']) && in_array($_POST['star_status'],array('star','unstar'))){
        echo bp_messages_star_set_action(array(
            'action'     => $_POST['star_status'],
            'message_id' => $_POST['message_id'],
            'bulk'       => false
        ));
      }
      die();
    }

    function fetch_quiz_submissions(){
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'quiz_submissions') || !is_numeric($_POST['quiz_id'])){
         _e('Security check Failed. Contact Administrator.','vibe');
         die();
      }

      global $wpdb;
      $quiz_id = $_POST['quiz_id'];
      $status = $_POST['status'];
      switch($status){
        case 0:
          $members = $wpdb->get_results( $wpdb->prepare("SELECT DISTINCT u.user_id as meta_key FROM {$wpdb->postmeta} as p LEFT JOIN {$wpdb->usermeta} as u ON p.meta_key = u.user_id WHERE p.meta_value LIKE '0' AND u.meta_key = p.post_id AND u.meta_value < %d AND p.post_id = %d",time(),$quiz_id), ARRAY_A); // Internal Query
        break;
        case 1:
          $members = $wpdb->get_results( $wpdb->prepare("SELECT DISTINCT u.user_id as meta_key FROM {$wpdb->postmeta} as p LEFT JOIN {$wpdb->usermeta} as u ON p.meta_key = u.user_id WHERE p.meta_value NOT LIKE '0' AND u.meta_key = p.post_id AND u.meta_value < %d AND p.post_id = %d",time(),$quiz_id), ARRAY_A); // Internal Query
        break;
      }

      echo '<ul class="quiz_students">';
      if(!empty($members)){
        $student_field=vibe_get_option('student_field');
        foreach($members as $member ){

            if(is_numeric($member['meta_key'])){
                $member_id=$member['meta_key'];
                $bp_name = bp_core_get_userlink( $member_id );
                if(!empty($bp_name)){
                    if(isset($student_field))
                        $profile_data = 'field='.$student_field.'&user_id='.$member_id;
                    else
                        $profile_data='user_id='.$member_id;
                  
                  $bp_location ='';
                  if(bp_is_active('xprofile'))
                      $bp_location = bp_get_profile_field_data($profile_data);

              echo '<li id="qs'.$member_id.'">';
              echo get_avatar($member_id);
              echo '<h6>'. $bp_name . '</h6>';
              echo '<span>';
              if ($bp_location) {
                echo $bp_location ;
              }
              
              do_action('wplms_quiz_submission_meta',$member_id,$quiz_id);
              echo '</span>';
              echo '<ul> 
                  <li><a class="tip reset_quiz_user" data-quiz="'.$quiz_id.'" data-user="'.$member_id.'" title="'.__('Reset Quiz for User','vibe').'"><i class="icon-reload"></i></a></li>
                  <li><a class="tip evaluate_quiz_user" data-quiz="'.$quiz_id.'" data-user="'.$member_id.'" title="'.__('Evaluate Quiz for User','vibe').'"><i class="icon-check-clipboard-1"></i></a></li>
                  </ul>';
              echo '</li>';
            }
          } 
        }
      }else{
        echo '<div class="message">'._x('No submissions found','Quiz submission not found','vibe').'</div>';
      }
      echo '</ul>';
      wp_nonce_field('vibe_quiz','qsecurity');
      die();
    }

    function fetch_course_submissions(){

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'pending_course_submissions') || !is_numeric($_POST['course_id'])){
         _e('Security check Failed. Contact Administrator.','vibe');
         die();
        }

        $course_id = $_POST['course_id'];

        global $wpdb;
        $student_field=vibe_get_option('student_field');

        if($_POST['status'] == 4){
          $members_submit_course = $wpdb->get_results( $wpdb->prepare("SELECT user_id as meta_key FROM {$wpdb->usermeta} where meta_key = %s AND meta_value = %d",'course_status'.$course_id,4), ARRAY_A); // Internal Query
        }else{
          $members_submit_course = $wpdb->get_results( $wpdb->prepare("SELECT user_id as meta_key FROM {$wpdb->usermeta} where meta_key = %s AND meta_value = %d",'course_status'.$course_id,3), ARRAY_A); // Internal Query
        }

        if(!empty($members_submit_course)){
          echo '<ul class="course_students">';
          foreach($members_submit_course as $submit_course ){

            if(is_numeric($submit_course['meta_key'])){
            $member_id=$submit_course['meta_key'];

            $bp_name = bp_core_get_userlink( $member_id );
              if(isset($student_field))
              $profile_data = 'field='.$student_field.'&user_id='.$member_id;
            else
              $profile_data='user_id='.$member_id;

            $bp_location ='';
            if(bp_is_active('xprofile'))
              $bp_location = bp_get_profile_field_data($profile_data);

            echo '<li id="s'.$member_id.'">';
              echo get_avatar($member_id);
              echo '<h6>'. $bp_name . '</h6>';
              echo '<span>';
              if ($bp_location) {
                echo $bp_location ;
              }
              do_action('wplms_course_submission_meta',$member_id,$course_id);
              echo '</span>';
              echo '<ul> 
                  <li><a class="tip evaluate_course_user" data-course="'.$course_id.'" data-user="'.$member_id.'" title="'.__('Evaluate Course for User','vibe').'"><i class="icon-check-clipboard-1"></i></a></li>
                  </ul>';
              echo '</li>';
            }
          }
          echo '</ul>';
          wp_nonce_field($course_id,'security');
          ?>
          <script>
            jQuery(document).ready(function($){
              $('#course').trigger('loaded');
            });
          </script>
          <?php
        }else{
          echo '<div class="message">'._x('No course submissions !','No course submissions found in Course - Admin - submissions seciton','vibe').'</div>';
        }
        die();
    }

    function get_unit_content(){
        $course_id = $_POST['course_id'];
        $unit_id = $_POST['unit_id'];
        ?>
         <div id="unit_load<?php echo $unit_id?>">
         <?php
        if(!is_numeric($unit_id) || !is_numeric($course_id)){
          _e('Invalid Course or Unit ID');
          echo '</div>';
          die();
        }
        
        $curriculum = bp_course_get_curriculum_units($course_id);
        if(!in_Array($unit_id,$curriculum)){
          _e('Unit not found in Course');
          echo '</div>';
          die();
        }
        $course_unit_content = get_post_meta($course_id,'vibe_course_unit_content',true);
        if(vibe_validate($course_unit_content)){

            $post = get_post($unit_id);
            if($post->post_type == 'unit'){
              ?>
                <div class="content">
                  <h2><?php echo $post->post_title; ?></h2>
                  <h5><?php echo get_post_meta($post->ID,'vibe_subtitle',true); ?></h5>
                    <?php
                      echo apply_filters('the_content',$post->post_content);
                    ?>
                </div>
              <?php
            }
        }
        ?>
        </div>
        <?php
       die();
    }

    /* == Apply For Course === */
    function apply_for_course(){
      $course_id = $_POST['course_id'];
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$course_id) || !is_numeric($course_id)){
         _e('Security check Failed. Contact Administrator.','vibe');
         die();
      }

      $user_id = get_current_user_id();
      update_user_meta($user_id,'apply_course'.$course_id,$course_id);
      echo _x('Applied for Course','Apply for Course confirmation message.','vibe');
      do_action('wplms_user_course_application',$course_id,$user_id);
      die();
    }

    function manage_user_application(){ 
        $user_id = $_POST['user_id'];
        $course_id = $_POST['course_id'];
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'.$course_id.$user_id) || !is_numeric($course_id) || !is_numeric($user_id)){
           _e('Security check Failed. Contact Administrator.','vibe');
           die();
        }
        $action = $_POST['act'];
        switch($action){
          case 'approve':
            bp_course_add_user_to_course($user_id,$course_id);
          break;
          default:
          break;
        }
        delete_user_meta($user_id,'apply_course'.$course_id,$course_id);
        do_action('wplms_manage_user_application',$action,$course_id,$user_id);
        die();
    }

    function check_question_answer(){
        $user_id = get_current_user_id();
        $quiz_id = $_POST['quiz_id'];
        $question_id = $_POST['question_id'];
        $security = $user_id.$quiz_id.$question_id;
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],$security)){
            echo json_encode(array('message'=>__('Security check Failed. Contact Administrator.','vibe')));
            die();
        }

        $marks = bp_course_get_user_question_marks($quiz_id,$question_id,$user_id);
        if(is_numeric($marks)){
          echo json_encode(array('message'=>__('Question answer already marked','vibe')));
          die();
        }

        $questions = bp_course_get_quiz_questions($quiz_id,$user_id);
        
        if(isset($questions) && isset($questions['ques'])){
            if(!in_array($question_id,$questions['ques'])){
                echo json_encode(array('message'=>__('Question not included in quiz.','vibe')));
                die();       
            }
        }else{
            echo json_encode(array('message'=>__('Unable to fetch quiz questions.','vibe')));
            die();       
        }

        $answer = rtrim($_POST['answer'],',');
        $type = get_post_meta($question_id,'vibe_question_type',true); 
        $correct_answer = get_post_meta($question_id,'vibe_question_answer',true);

        $correct = bp_course_evaluate_question(array('question_id'=>$question_id,'type'=>$type,'correct_answer'=>$correct_answer),$answer);
 
        $return = array();
        if(empty($correct)){
            $marks = apply_filters('wplms_incorrect_quiz_answer',0,$quiz_id,$answer,$question_id);
            $return['status']='incorrect';
        }else{
          $k = array_search($question_id, $questions['ques']);
          $return['status']='correct';

          if(!is_int($correct)){
            //Partial marking
            $questions['marks'][$k] = round($correct*$questions['marks'][$k],0);
          }
          
          $marks= apply_filters('wplms_correct_quiz_answer',$questions['marks'][$k],$quiz_id,$answer,$question_id);
        }

        $return['marks']=$marks;

        if(isset($correct_answer)){
          if(in_Array($type,array('single','select'))){
            $options = get_post_meta($question_id,'vibe_question_options',true);
            if(is_Array($options)){
              if($type == 'select' && strpos($correct_answer,'|') !== false){
                $corr_ans = explode('|',$correct_answer);
                if(!empty($corr_ans)){
                  $return['correct'] = '';
                  foreach($corr_ans as $ca){
                    if($return['correct']){$return['correct'] .=' '.__('and','vibe').' ';}
                    $return['correct'] .=$options[$ca-1];
                  }
                }
              }else{
                $return['correct']=$options[$correct_answer-1] ;
              }
            }
          }else if(in_array($type,array('truefalse'))){
            if($correct_answer){$return['correct']=__('True','vibe');}else{$return['correct']=__('False','vibe');}
          }elseif(in_Array($type,array('multiple','sort','match'))){
            $options = get_post_meta($question_id,'vibe_question_options',true);
            if(is_Array($options)){
              if(strpos($correct_answer,',') !== false){
                $corr_ans = explode(',',$correct_answer);
                if(!empty($corr_ans)){
                  $return['correct'] = '';
                  foreach($corr_ans as $ca){
                    if($return['correct']){$return['correct'] .=' '.__('and','vibe').' ';}
                    $return['correct'] .= do_shortcode($options[$ca-1]);
                  }
                }
              }else{
                $return['correct']=do_shortcode($options[$correct_answer-1]) ;
              }
            }
          }else{
            $return['correct']=do_shortcode($correct_answer);
          }
        }
        $explaination = get_post_meta($question_id,'vibe_question_explaination',true);
        if(isset($explaination) && strlen($explaination) > 5){
            $return['explanation'] = apply_filters('the_content',$explaination);
        }

        bp_course_save_question_quiz_answer($quiz_id,$question_id,$user_id,$answer);
        bp_course_save_user_answer_marks($quiz_id,$question_id,$user_id,$marks);

        echo json_encode($return);
        die();
    }
}

BP_Course_Ajax::init();
