<?php

add_action( 'widgets_init', 'wplms_student_stats' );

function wplms_student_stats() {
    register_widget('wplms_student_stats');
}

class wplms_student_stats extends WP_Widget {
  
 
    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'wplms_student_stats', 'description' => __('Student Statistics widget', 'wplms-dashboard') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_student_stats' );
    parent::__construct( 'wplms_student_stats', __(' DASHBOARD : Student Stats', 'wplms-dashboard'), $widget_ops, $control_ops );
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $course_graphs =  $instance['course'];
    $quiz_graphs =  $instance['quiz'];
    $assignment_graphs =  $instance['assignments'];
    $width =  $instance['width'];
    $course_chart=$instance['course_chart'];
    $quiz_chart=$instance['quiz_chart'];
    $assignment_chart=$instance['assignment_chart'];


    echo '<div class="'.$width.'"><div class="dash-widget">'.$before_widget;

    $r = rand(1,999);
    // Display the widget title 
    if ( $title )
        echo $before_title . $title . $after_title;
        
        echo '<div id="vibe-tabs-student-graphs" class="tabs tabbable">
              <ul class="nav nav-tabs clearfix">';

        if(isset($course_graphs) && $course_graphs){
          echo '<li><a href="#tab-courses" class="course_data" data-toggle="tab">'.__('Courses','wplms-dashboard').'</a></li>';
        }
        if(isset($quiz_graphs) && $quiz_graphs){
          echo '<li><a href="#tab-quizes" class="quiz_data" data-toggle="tab">'.__('Quiz','wplms-dashboard').'</a></li>';
        }    
        if(isset($assignment_graphs) && $assignment_graphs){
          echo '<li><a href="#tab-assignments" class="assignment_data" data-toggle="tab">'.__('Assignments','wplms-dashboard').'</a></li>';
        }    
        echo '</ul><div class="tab-content">';
        if(isset($course_graphs) && $course_graphs){

        echo '<div id="tab-courses" class="tab-pane">
                <div id="student_courses'.$r.'" class="morris"></div>';
        global $wpdb;
        $user_id = get_current_user_id();
        $marks=$wpdb->get_results(sprintf("
              SELECT rel.post_id as id,rel.meta_value as val
                FROM {$wpdb->posts} AS posts
                LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
                WHERE   posts.post_type   = 'course'
                AND   posts.post_status   = 'publish'
                AND   rel.meta_key   = %d
                AND   rel.meta_value > 2
            ",$user_id));
        if(is_array($marks)){
          foreach($marks as $k=>$mark){
            $course[] = $mark->id;
            $user_courses[$mark->id]=array('id'=>$k,'label'=>($k+1).' '.get_the_title($mark->id), 'marks'=>$mark->val);
          }
          if(is_array($course)){
          $user_course=implode(',',$course);
          $average_marks=$wpdb->get_results("
            SELECT c.post_id as id,c.meta_value as average
            FROM {$wpdb->postmeta} AS c
            WHERE c.post_id IN ($user_course)
            AND c.meta_key   = 'average'
              ");

            foreach($average_marks as $average_mark){
              if(isset($average_mark->average))
                $user_courses[$average_mark->id]['average']=$average_mark->average;
              else
                $user_courses[$average_mark->id]['average']=0;
            }
          }
        } // End marks array

        if(empty($user_courses)){
          echo '<p class="message">No Data Available</p>';
        }
        
        echo '<script>
                var student_data'.$r.'=[';
                $first=0;
                if(is_array($user_courses) && isset($user_courses) && !empty($user_courses)){
                    foreach($user_courses as $k=>$user_course){
                    if($first)
                      echo ',';
                    $first=1;
                    echo str_replace('"','\'',json_encode($user_course,JSON_NUMERIC_CHECK));
                  }
                }
        echo  '];
                </script>
              </div>';
        }

        if(isset($quiz_graphs) && $quiz_graphs){
        echo '<div id="tab-quizes" class="tab-pane">
                <div id="student_quizes'.$r.'" class="morris"></div>';

        global $wpdb;
        $user_id = get_current_user_id();
        $marks=$wpdb->get_results(sprintf("
              SELECT rel.post_id as id,rel.meta_value as val
                FROM {$wpdb->posts} AS posts
                LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
                WHERE   posts.post_type   = 'quiz'
                AND   posts.post_status   = 'publish'
                AND   rel.meta_key   = %d
                AND   rel.meta_value >= 0
            ",$user_id));
        if(is_array($marks)){
          foreach($marks as $k=>$mark){
            $quiz[] = $mark->id;
            $user_quizes[$mark->id]=array('id'=>$k,'label'=>($k+1).' '.get_the_title($mark->id), 'marks'=>$mark->val);
          }
          if(is_array($quiz)){
            $user_quiz=implode(',',$quiz);
            $average_marks=$wpdb->get_results("
              SELECT c.post_id as id,c.meta_value as average
              FROM {$wpdb->postmeta} AS c
              WHERE c.post_id IN ($user_quiz)
              AND c.meta_key   = 'average'
                ");
            foreach($average_marks as $average_mark){
                if(isset($average_mark->average))
                  $user_quizes[$average_mark->id]['average']=$average_mark->average;
                else
                  $user_quizes[$average_mark->id]['average']=0;
            }
          }
        }

          if(empty($user_quizes)){
            echo '<p class="message">No Data Available</p>';
          }


        echo '<script>
                var quiz_data'.$r.'=[';
                $first=0;
                if(isset($user_quizes) && is_array($user_quizes) && !empty($user_quizes)){
                  foreach($user_quizes as $k=>$user_quiz){
                    if($first)
                      echo ',';
                    $first=1;
                    echo str_replace('"','\'',json_encode($user_quiz,JSON_NUMERIC_CHECK));
                  }
                }
        echo  '];
                </script>
              </div>';
        }


        if(isset($assignment_graphs) && $assignment_graphs){
          echo '<div id="tab-assignments" class="tab-pane">
                  <div id="student_assignments'.$r.'" class="morris"></div>
                </div>';
            global $wpdb;
          $user_id = get_current_user_id();
          $marks=$wpdb->get_results(sprintf("
                SELECT rel.post_id as id,rel.meta_value as val
                  FROM {$wpdb->posts} AS posts
                  LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
                  WHERE   posts.post_type   = 'wplms-assignment'
                  AND   posts.post_status   = 'publish'
                  AND   rel.meta_key   = %d
                  AND   rel.meta_value >= 0
              ",$user_id));
          if(!empty($marks ) && is_array($marks)){
            foreach($marks as $k=>$mark){
              $assignment[] = $mark->id;
              $user_assignments[$mark->id]=array('id'=>$k,'label'=>($k+1).' '.get_the_title($mark->id), 'marks'=>$mark->val);
            }
            if(is_array($assignment)){
              $user_assignment=implode(',',$assignment);
              $average_marks=$wpdb->get_results("
                SELECT c.post_id as id,c.meta_value as average
                FROM {$wpdb->postmeta} AS c
                WHERE c.post_id IN ($user_assignment)
                AND c.meta_key   = 'average'
                  ");
              if(is_array($average_marks))
              foreach($average_marks as $average_mark){
                  if(isset($average_mark->average))
                    $user_assignments[$average_mark->id]['average']=$average_mark->average;
                  else
                    $user_assignments[$average_mark->id]['average']=0;
              }
            }
          }

          if(empty($user_assignments)){
            echo '<p class="message">No Data Available</p>';
          }
          
          if(is_array($user_assignments) && isset($user_assignments) && !empty($user_assignments)){
          echo '<script>
                  var assignment_data'.$r.'=[';
                  $first=0;
                  
                  foreach($user_assignments as $k=>$user_assignment){
                    if($first)
                      echo ',';
                    $first=1;
                    echo str_replace('"','\'',json_encode($user_assignment,JSON_NUMERIC_CHECK));
                  }
          echo  '];
                  </script>';
          }
          echo '</div>';
      }     
        //
       echo "<script>
        jQuery(document).ready(function($){
           if(jQuery('#student_courses$r').length){
              $(document).on('shown.bs.tab', '.nav-tabs a.course_data', function (e) { 
                if($(this).hasClass('course_data')){
                 Morris.$course_chart({
                    element: student_courses$r,
                    data: student_data$r,
                    xkey: 'label',
                    ykeys: ['marks', 'average'],
                    labels: ['".__('MY MARKS','wplms-dashboard')."', '".__('AVERAGE','wplms-dashboard')."'],
                    lineColors: ['#23b7e5','#bbb'],
                    ymin:0,
                    lineWidth: 2,
                    resize:true,
                    parseTime: false
                  });
                  $(this).removeClass('course_data');
                }
              });
            }
            if(jQuery('#student_quizes$r').length){
              $(document).on('shown.bs.tab', '.nav-tabs a.quiz_data', function (e) {
                if($(this).hasClass('quiz_data')){
                 Morris.$quiz_chart({
                    element: student_quizes$r,
                    data: quiz_data$r,
                    xkey: 'label',
                    ykeys: ['marks', 'average'],
                    labels: ['".__('MY MARKS','wplms-dashboard')."', '".__('AVERAGE','wplms-dashboard')."'],
                    lineColors: ['#27c24c','#bbb'],
                    ymin:0,
                    lineWidth: 2,
                    resize:true,
                    parseTime: false
                  });
                  $(this).removeClass('quiz_data');
                }
              });
            }
            if(jQuery('#student_assignments$r').length){
              $(document).on('shown.bs.tab', '.nav-tabs a.assignment_data', function (e) { 
                if($(this).hasClass('assignment_data')){
                 Morris.$assignment_chart({
                    element: student_assignments$r,
                    data: assignment_data$r,
                    xkey: 'label',
                    ykeys: ['marks', 'average'],
                    labels: ['".__('MY MARKS','wplms-dashboard')."', '".__('AVERAGE','wplms-dashboard')."'],
                    lineColors: ['#27c24c','#bbb'],
                    ymin:0,
                    lineWidth: 2,
                    resize:true,
                    parseTime: false
                  });
                  $(this).removeClass('assignment_data');
                }
              });
            }
          });
          </script>"; 
        echo '</div>';
        echo $after_widget.'</div></div>';
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
      $instance = $old_instance;
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['width'] = $new_instance['width'];
      $instance['course'] = $new_instance['course'];
      $instance['quiz'] = $new_instance['quiz'];
      $instance['assignments'] = $new_instance['assignments'];
      $instance['course_chart'] =$new_instance['course_chart'];
      $instance['quiz_chart'] =$new_instance['quiz_chart'];
      $instance['assignment_chart'] =$new_instance['assignment_chart'];
      return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                        'title'  => __('Student Statistics','wplms-dashboard'),
                        'width' => 'col-md-6 col-sm-12',
                        'chart'=>'Line'
                    );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $title  = esc_attr($instance['title']);
        $quiz  = esc_attr($instance['quiz']);
        $quiz_chart  = esc_attr($instance['quiz_chart']);
        $assignments  = esc_attr($instance['assignments']);
        $assignment_chart  = esc_attr($instance['assignment_chart']);
        $course  = esc_attr($instance['course']);
        $course_chart  = esc_attr($instance['course_chart']);
        $width = esc_attr($instance['width']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('course'); ?>"><?php _e('Show Course statistics','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'course' ); ?>" name="<?php echo $this->get_field_name( 'course' ); ?>" type="checkbox" value="1"  <?php checked($course,1,true) ?>/>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('course_chart'); ?>"><?php _e('Course Chart Style','wplms-dashboard'); ?></label> 
          <select id="<?php echo $this->get_field_id( 'course_chart' ); ?>" name="<?php echo $this->get_field_name( 'course_chart' ); ?>">
            <option value="Line" <?php selected($course_chart,'Line') ?>><?php _e('LINE CHART','wplms-dashboard'); ?></option>
            <option value="Area" <?php selected($course_chart,'Area') ?>><?php _e('AREA CHART','wplms-dashboard'); ?></option>
            <option value="Bar" <?php selected($course_chart,'Bar') ?>><?php _e('BAR CHART','wplms-dashboard'); ?></option>
          </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('quiz'); ?>"><?php _e('Show Quiz statistics','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'quiz' ); ?>" name="<?php echo $this->get_field_name( 'quiz' ); ?>" type="checkbox" value="1"  <?php checked($quiz,1,true) ?>/>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('quiz_chart'); ?>"><?php _e('Quiz Chart Style','wplms-dashboard'); ?></label> 
          <select id="<?php echo $this->get_field_id( 'quiz_chart' ); ?>" name="<?php echo $this->get_field_name( 'quiz_chart' ); ?>">
            <option value="Line" <?php selected($quiz_chart,'Line') ?>><?php _e('LINE CHART','wplms-dashboard'); ?></option>
            <option value="Area" <?php selected($quiz_chart,'Area') ?>><?php _e('AREA CHART','wplms-dashboard'); ?></option>
            <option value="Bar" <?php selected($quiz_chart,'Bar') ?>><?php _e('BAR CHART','wplms-dashboard'); ?></option>
          </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('assignments'); ?>"><?php _e('Show Assignments statistics','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'assignments' ); ?>" name="<?php echo $this->get_field_name( 'assignments' ); ?>" type="checkbox" value="1"  <?php checked($assignments,1,true) ?>/>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('assignment_chart'); ?>"><?php _e('Quiz Chart Style','wplms-dashboard'); ?></label> 
          <select id="<?php echo $this->get_field_id( 'assignment_chart' ); ?>" name="<?php echo $this->get_field_name( 'assignment_chart' ); ?>">
            <option value="Line" <?php selected($assignment_chart,'Line') ?>><?php _e('LINE CHART','wplms-dashboard'); ?></option>
            <option value="Area" <?php selected($assignment_chart,'Area') ?>><?php _e('AREA CHART','wplms-dashboard'); ?></option>
            <option value="Bar" <?php selected($assignment_chart,'Bar') ?>><?php _e('BAR CHART','wplms-dashboard'); ?></option>
          </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Select Width','wplms-dashboard'); ?></label> 
          <select id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>">
            <option value="col-md-3 col-sm-6" <?php selected('col-md-3 col-sm-6',$width); ?>><?php _e('One Fourth','wplms-dashboard'); ?></option>
            <option value="col-md-4 col-sm-6" <?php selected('col-md-4 col-sm-6',$width); ?>><?php _e('One Third','wplms-dashboard'); ?></option>
            <option value="col-md-6 col-sm-12" <?php selected('col-md-6 col-sm-12',$width); ?>><?php _e('One Half','wplms-dashboard'); ?></option>
            <option value="col-md-8 col-sm-12" <?php selected('col-md-8 col-sm-12',$width); ?>><?php _e('Two Third','wplms-dashboard'); ?></option>
             <option value="col-md-8 col-sm-12" <?php selected('col-md-9 col-sm-12',$width); ?>><?php _e('Three Fourth','wplms-dashboard'); ?></option>
            <option value="col-md-12" <?php selected('col-md-12',$width); ?>><?php _e('Full','wplms-dashboard'); ?></option>
          </select>
        </p>
        <?php 
    }
} 

?>