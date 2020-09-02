<?php

add_action( 'widgets_init', 'wplms_instructor_stats' );

function wplms_instructor_stats() {
    register_widget('wplms_instructor_stats');
}

class wplms_instructor_stats extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
  function __construct() {
    $widget_ops = array( 'classname' => 'wplms_instructor_stats', 'description' => __('Instructor Statistics widget', 'wplms-dashboard') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_instructor_stats' );
    parent::__construct( 'wplms_instructor_stats', __(' DASHBOARD : Instructor Stats', 'wplms-dashboard'), $widget_ops, $control_ops );
    add_action('wp_ajax_generate_ranges',array($this,'generate_ranges'));
    add_action('wp_ajax_load_quiz_assignment_list',array($this,'load_quiz_assignment_list'));
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $width =  $instance['width'];
    $range=$instance['range'];
    $max=$instance['max'];
    if(!isset($max))
      $max=10;

    global $wpdb;
    $user_id = get_current_user_id();
    echo '<div class="'.$width.'"><div class="dash-widget instructor-stats">'.$before_widget;
    // Display the widget title 
    if ( $title )
      	$label = $title;
    else 
      $label = __('Courses','wplms-dashboard'); 

        
        $query = apply_filters('wplms_dashboard_courses_instructors',$wpdb->prepare("
              SELECT DISTINCT posts.ID as course_id
                FROM {$wpdb->posts} AS posts
                WHERE   posts.post_type   = 'course'
                AND   posts.post_author   = %d
                ORDER BY posts.post_modified_gmt DESC
                LIMIT 0,%d
            ",$user_id,$max),$user_id,$instance);

        $instructor_courses=$wpdb->get_results($query,ARRAY_A);
        
        if(!count($instructor_courses) || !is_array($instructor_courses))
          echo '<div class="message error"><p>'.__('No courses created by instructor','wplms-dashboard').'</p></div>';
        else{
          echo '<div class="col-md-3"><label id="stats-title" data-range="'.$range.'">'.$label.'</label>';
          echo '<ul class="instructor-stats-courses">';
          $totalpass=0;
          $totalbadge=0;
          $cumulative_data=array();
          foreach($instructor_courses as $k => $value){
              $course_id = $value['course_id'];
              $avg = get_post_meta($course_id,'average',true);

              if(!is_numeric($avg))
                $avg=0;
              
              $ctitle=get_the_title($course_id);
              $cumulative_data[$course_id]=array(
                'title'=>$ctitle,
                'avg'=>$avg
                );

              echo '<li>'.$ctitle.'
              <ul>
                <li data-id="'.$course_id.'" class="list-stats tip" data-title="'.__('Show Stats','wplms-dashboard').'"><i class="icon-bars"></i></li>
                <li  data-id="'.$course_id.'" class="list-recalculate-stats tip" data-title="'.__('Re-calculate Stats','wplms-dashboard').'"><i class="icon-reload"></i></li>
                <li  data-id="'.$course_id.'" class="list-sub tip" data-title="'.__('List Quiz / Assignments','wplms-dashboard').'"><i class="icon-plus-1"></i></li>
              </ul>
              </li>';
          }  
          echo '</ul></div>';
          echo '<div class="col-md-9">
                  <div id="instructor_stats" class="morris"></div>
                </div>';
        
        echo '<script>
                var instructor_data=[';$first=0;
                if(is_array($cumulative_data)){
        foreach($cumulative_data as $data){
          if($first)
            echo ',';
          $first=1;
          echo str_replace('"','\'',json_encode($data,JSON_NUMERIC_CHECK));
        }}
        echo  '];
                </script>';
        echo "<script>
        jQuery(document).ready(function($){
           if(jQuery('#instructor_stats').length){
                Morris.Bar({
                    element: 'instructor_stats',
                    data: instructor_data,
                    xkey: 'title',
                    ykeys: ['avg'],
                    labels: ['".__('Average Score','wplms-dashboard')."'],
                    barColors: ['#23b7e5'],
                    ymin:0,
                    ymax:100,
                    xLabelAngle: 60,
                    lineWidth: 1,
                    resize:true,
                    parseTime: false
                  });
                $('#stats-title').click(function(){
                    $('#instructor_stats').html('');
                    new Morris.Bar({
                    element: 'instructor_stats',
                    data: instructor_data,
                    xkey: 'title',
                    ykeys: ['avg'],
                    labels: ['".__('Average Score','wplms-dashboard')."'],
                    barColors: ['#23b7e5'],
                    ymin:0,
                    ymax:100,
                    lineWidth: 1,
                    resize:true,
                    parseTime: false
                  });
                });
                }
              });
          </script>"; 

        }  
        echo '</div>';
        echo $after_widget.'</div></div>';
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
	    $instance = $old_instance;
	    $instance['title'] = strip_tags($new_instance['title']);
	    $instance['width'] = $new_instance['width'];
      $instance['range'] = $new_instance['range'];
      $instance['max'] = $new_instance['max'];
	    return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                        'title'  => __('Instructor Statistics','wplms-dashboard'),
                        'width' => 'col-md-6 col-sm-12',
                        'course' => 1,
                        'range'=>10,
                        'max' => 10
                    );
  		  $instance = wp_parse_args( (array) $instance, $defaults );
        $title  = esc_attr($instance['title']);
        $range  = esc_attr($instance['range']);
        $max  = esc_attr($instance['max']);
        $width = esc_attr($instance['width']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('range'); ?>"><?php _e('Set Range value (10 for 1/10)','wplms-dashboard'); ?></label> 
          <input class="text" id="<?php echo $this->get_field_id( 'range' ); ?>" name="<?php echo $this->get_field_name( 'range' ); ?>" type="text" value="<?php echo $range; ?>"/>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('max'); ?>"><?php _e('Set Max course limit','wplms-dashboard'); ?></label> 
          <input class="text" id="<?php echo $this->get_field_id( 'max' ); ?>" name="<?php echo $this->get_field_name( 'max' ); ?>" type="text" value="<?php echo $max; ?>"/>
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
    function generate_ranges(){
      $user_id = get_current_user_id();
      global $wpdb;
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') || !current_user_can('edit_posts')){
             echo '<p class="message">'.__('Security error','wplms-dashboard').'</p>';
             die();
        }
      
      $id = intval($_POST['id']);
      $post_type = get_post_type($id);
      $range=intval($_POST['range']);
      $student_marks_array=array();
      $query = $wpdb->get_results($wpdb->prepare("
              SELECT rel.meta_key as student,rel.meta_value as marks
                FROM {$wpdb->posts} AS posts
                LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
                WHERE   posts.post_type   = '%s'
                AND posts.ID = %d
                AND   rel.meta_key REGEXP '^-?[0-9]+$'
                AND   rel.meta_value > 1
                ",$post_type,$id),ARRAY_A);  

            if(is_array($query) && count($query)){
            foreach($query as $k => $value){
              $student_marks_array[$value['student']]=$value['marks'];
            }

            asort($student_marks_array);

            $max=max($student_marks_array);
            $min=min($student_marks_array);

            $range_val = round(($max-$min)/$range);
            $student_range=array();

            $begin = $min;
            $end = $min+$range_val;
            if($range_val >= 1){
              $i=0;
              foreach($student_marks_array as $key=>$value){

                if(isset($student_range[$begin.'-'.$end]['value']))
                  $i=$student_range[$begin.'-'.$end]['value'];
                else
                  $i=0;

                 if($value >= $begin && $value <= $end){
                   $i++;
                   $student_range[$begin.'-'.$end]=array(
                    'range'=>$begin.'-'.$end,
                    'value'=> $i 
                    );
                 }else{
                  $i=0;
                   while($value > $end){
                      $begin = $begin+$range_val; 
                      $end=$end+$range_val;
                      if($end > $max)
                      $end=$max;
                   }
                   $i++;
                   $student_range[$begin.'-'.$end]=array(
                    'range'=>$begin.'-'.$end,
                    'value'=> $i 
                  );
                 }
              }//end for
            }else{
              if(is_array($student_marks_array)){
              foreach($student_marks_array as $key=>$value){
                $student_range[$value]=array(
                  'range' => $value,
                  'value' => 1
                  );
                }
              }
            }

            echo '[';
            $first=0;
            if(is_array($student_range))
            foreach($student_range as $data){
              if($first)
                echo ',';

              $first=1;

              echo json_encode($data,JSON_NUMERIC_CHECK);
            }
            echo  ']';
          }else{
           echo json_encode('<p class="message">'.__('No data available','wplms-dashboard').'</p>');
          }
          die();  
    }
    function load_quiz_assignment_list(){
      $user_id = get_current_user_id();
      global $wpdb;
      $id = intval($_POST['id']);
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') || !current_user_can('edit_posts')){
             echo '<p class="message">'.__('Security error','wplms-dashboard').'</p>';
             die();
        }
      
       $quiz_list = $wpdb->get_results($wpdb->prepare("
                SELECT posts.ID as id,posts.post_title as title
                FROM {$wpdb->posts} AS posts
                LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
                WHERE   posts.post_type   = 'quiz'
                AND   rel.meta_key = 'vibe_quiz_course'
                AND   rel.meta_value = %d
                ",$id),ARRAY_A);

      $assignment_list = $wpdb->get_results($wpdb->prepare("
                SELECT posts.ID as id,posts.post_title as title
                FROM {$wpdb->posts} AS posts
                LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
                WHERE   posts.post_type   = 'wplms-assignment'
                AND   rel.meta_key = 'vibe_assignment_course'
                AND   rel.meta_value = %d
                ",$id),ARRAY_A);  

      if(is_array($quiz_list) || is_array($assignment_list)){
        echo '<ul class="qa_list">';
        if(is_array($quiz_list))
        foreach($quiz_list as $quiz){
         echo '<li><strong class="quiz_label">'.__('Quiz','wplms-dashboard').'</strong> : '.$quiz['title'].'<span data-id="'.$quiz['id'].'" class="list-stats"><i class="icon-bars "></i></span></li>';
        }
        if(is_array($assignment_list))
        foreach($assignment_list as $assignment){
         echo '<li><strong class="assignment_label">'.__('Assignment','wplms-dashboard').'</strong> : '.$assignment['title'].'<span data-id="'.$assignment['id'].'" class="list-stats"><i class="icon-bars "></i></span></li>';
        }
        echo '</ul>';
      }
      die();
    }
} 

?>