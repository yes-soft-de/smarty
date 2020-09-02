<?php

add_action( 'widgets_init', 'wplms_dash_mymodules_widget' );

function wplms_dash_mymodules_widget() {
    register_widget('wplms_dash_mymodules');
}

class wplms_dash_mymodules extends WP_Widget {

    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'wplms_dash_mymodules', 'description' => __('My Courses, Units, Quizzes, Assignments, Finished Courses widget for Dashboard', 'wplms-dashboard') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_dash_mymodules' );
    parent::__construct( 'wplms_dash_mymodules', __(' DASHBOARD : My Modules Widget', 'wplms-dashboard'), $widget_ops, $control_ops );
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $width =  $instance['width'];
    $course = esc_attr($instance['course']);
    $quiz = esc_attr($instance['quiz']);
    $unit = esc_attr($instance['unit']);
    $assignment = esc_attr($instance['assignment']);
    $download = esc_attr($instance['download']);
    $finished = esc_attr($instance['finished']);
    $number = esc_attr($instance['number']);

    echo '<div class="'.$width.'">
            <div class="dash-widget">'.$before_widget;
    echo '<div id="vibe-tabs-student-activity" class="tabs tabbable">
              <ul class="nav nav-tabs clearfix">';

    if(isset($course) && $course){
        echo '<li><a href="#tab-mycourses" data-toggle="tab">'.__('Courses','wplms-dashboard').'</a></li>';
    }
    if(isset($quiz) && $quiz){
        echo '<li><a href="#tab-myquizzes" data-toggle="tab">'.__('Quizzes','wplms-dashboard').'</a></li>';
    }
    if(isset($assignment) && $assignment){
        echo '<li><a href="#tab-myassignments" data-toggle="tab">'.__('Assignments','wplms-dashboard').'</a></li>';
    }
    if(isset($unit) && $unit){
        echo '<li><a href="#tab-myunits" data-toggle="tab">'.__('Units','wplms-dashboard').'</a></li>';
    }
    if(isset($download) && $download){
        echo '<li><a href="#tab-mydownloads" data-toggle="tab">'.__('Downloads','wplms-dashboard').'</a></li>';
    }
    if(isset($finished) && $finished){
        echo '<li><a href="#tab-finished" data-toggle="tab">'.__('finished','wplms-dashboard').'</a></li>';
    }
    echo '</ul><div class="tab-content">';


    // Display the widget title 
    
    global $wpdb,$bp;

    
    if(isset($course) && $course){
      echo '<div id="tab-mycourses" class="tab-pane">';
        $user_id = get_current_user_id();
        $courses= $wpdb->get_results($wpdb->prepare("
              SELECT ID as courseID,post_title
              FROM {$wpdb->posts} AS posts
              LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
              WHERE   posts.post_type   = 'course'
              AND   posts.post_status   = 'publish'
              AND   rel.meta_key   = %d
              LIMIT 0,%d",$user_id,$number));
        if(count($courses)){
          echo '<ul class="dashboard-mycourses">';
          foreach($courses as $course){
              echo '<li><a href="'.get_permalink($course->courseID).'">'.$course->post_title.'</a></li>';
          }
          echo '</ul>';  
        }else{
          echo '<div class="message error">'.__('No Courses found','wplms-dashboard').'</div>';
        }
      echo '</div>';
    }
    if(isset($quiz) && $quiz){
      echo '<div id="tab-myquizzes" class="tab-pane">';
        $user_id = get_current_user_id();
        $quizzes= $wpdb->get_results($wpdb->prepare("
              SELECT ID,post_title
              FROM {$wpdb->posts} AS posts
              LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
              WHERE   posts.post_type   = 'quiz'
              AND   posts.post_status   = 'publish'
              AND   rel.meta_key   = %d
              LIMIT 0,%d",$user_id,$number));
        if(count($quizzes)){
          echo '<ul class="dashboard-myquizzes">';
          foreach($quizzes as $quiz){
              echo '<li><a href="'.get_permalink($quiz->ID).'">'.$quiz->post_title.'</a></li>';
          }
          echo '</ul>';  
        }else{
          echo '<div class="message error">'.__('No quizzes found','wplms-dashboard').'</div>';
        }
      echo '</div>';
    }
    if(isset($assignment) && $assignment){
       echo '<div id="tab-myassignments" class="tab-pane">';
        $user_id = get_current_user_id();
        $assignments= $wpdb->get_results($wpdb->prepare("
              SELECT ID,post_title
              FROM {$wpdb->posts} AS posts
              LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
              WHERE   posts.post_type   = 'wplms-assignment'
              AND   posts.post_status   = 'publish'
              AND   rel.meta_key   = %d
              LIMIT 0,%d",$user_id,$number));
        if(count($assignments)){
          echo '<ul class="dashboard-myassignments">';
          foreach($assignments as $assignment){
              echo '<li><a href="'.get_permalink($assignment->ID).'">'.$assignment->post_title.'</a></li>';
          }
          echo '</ul>';  
        }else{
          echo '<div class="message error">'.__('No assignments found','wplms-dashboard').'</div>';
        }
      echo '</div>';
    }
    if(isset($unit) && $unit){
      echo '<div id="tab-myunits" class="tab-pane">';
        $user_id = get_current_user_id();

        $units= $wpdb->get_results($wpdb->prepare("
              SELECT ID,post_title
              FROM {$wpdb->posts} AS posts
              LEFT JOIN {$wpdb->usermeta} AS rel ON posts.ID = rel.meta_key
              WHERE   posts.post_type   = 'unit'
              AND   posts.post_status   = 'publish'
              AND   rel.user_id   = %d
              LIMIT 0,%d",$user_id,$number));

        if(count($units)){
          echo '<ul class="dashboard-myunits">';
          foreach($units as $unit){
              echo '<li><a href="'.get_permalink($unit->ID).'">'.$unit->post_title.'</a></li>';
          }
          echo '</ul>';  
        }else{
          echo '<div class="message error">'.__('No units found','wplms-dashboard').'</div>';
        }
      echo '</div>';
    }
    if(isset($download) && $download){
      echo '<div id="tab-mydownloads" class="tab-pane">';
        $user_id = get_current_user_id();
        $downloads = $wpdb->get_results($wpdb->prepare("
              SELECT ID,post_title
              FROM {$wpdb->posts} AS wpposts
              WHERE   wpposts.post_type   = 'attachment'
              AND wpposts.post_parent IN (
              SELECT ID
              FROM {$wpdb->posts} AS posts
              LEFT JOIN {$wpdb->usermeta} AS rel ON posts.ID = rel.meta_key
              WHERE   posts.post_type   = 'unit'
              AND   posts.post_status   = 'publish'
              AND   rel.user_id   = %d
              )
              LIMIT 0,%d",$user_id,$number));
        if(count($downloads)){
          echo '<ul class="dashboard-mycourses">';
          foreach($downloads as $download){
              echo '<li><a href="'.wp_get_attachment_url($download->ID).'" target="_blank">'.$download->post_title.'</a></li>';
          }
          echo '</ul>';  
        }else{
          echo '<div class="message error">'.__('No downloads found','wplms-dashboard').'</div>';
        }
      echo '</div>';
    }
    if(isset($finished) && $finished){ 
      echo '<div id="tab-finished" class="tab-pane">';
      $user_id = get_current_user_id();
        $finished= $wpdb->get_results($wpdb->prepare("
              SELECT ID as courseID,post_title
              FROM {$wpdb->posts} AS posts
              LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
              WHERE   posts.post_type   = 'course'
              AND   posts.post_status   = 'publish'
              AND   rel.meta_key   = %d
              AND   rel.meta_value > 2
              LIMIT 0,%d",$user_id,$number));

       if(count($finished)){
          echo '<ul class="dashboard-finished">';
          foreach($finished as $finish){
              echo '<li><a href="'.get_permalink($finish->courseID).'">'.$finish->post_title.'</a></li>';
          }
          echo '</ul>';  
        }else{
          echo '<div class="message error">'.__('No Finished Courses found','wplms-dashboard').'</div>';
        }
      echo '</div>';
    }

        echo '</div>
        </div>'.$after_widget.'
        </div>
        </div>';
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
	    $instance = $old_instance;
	    $instance['title'] = strip_tags($new_instance['title']);
	    $instance['course'] = $new_instance['course'];
      $instance['quiz'] = $new_instance['quiz'];
      $instance['assignment'] = $new_instance['assignment'];
      $instance['unit'] = $new_instance['unit'];
      $instance['download'] = $new_instance['download'];
      $instance['finished'] = $new_instance['finished'];
	    $instance['number'] = $new_instance['number'];
      $instance['width'] = $new_instance['width'];
	    return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                        'title'  => __('Contact Instructors','wplms-dashboard'),
                        'width' => 'col-md-6 col-sm-12'
                    );
  		  $instance = wp_parse_args( (array) $instance, $defaults );
        $title  = esc_attr($instance['title']);
        $course = esc_attr($instance['course']);
        $quiz = esc_attr($instance['quiz']);
        $unit = esc_attr($instance['unit']);
        $assignment = esc_attr($instance['assignment']);
        $download= esc_attr($instance['download']);
        $number= esc_attr($instance['number']);
        $width = esc_attr($instance['width']);
        $finished = esc_attr($instance['finished']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('course'); ?>"><?php _e('Show My Courses','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'course' ); ?>" name="<?php echo $this->get_field_name( 'course' ); ?>" type="checkbox" value="1"  <?php checked($course,1,true) ?>/>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('quiz'); ?>"><?php _e('Show My Quizzes','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'quiz' ); ?>" name="<?php echo $this->get_field_name( 'quiz' ); ?>" type="checkbox" value="1"  <?php checked($quiz,1,true) ?>/>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('assignment'); ?>"><?php _e('Show My Assignments','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'assignment' ); ?>" name="<?php echo $this->get_field_name( 'assignment' ); ?>" type="checkbox" value="1"  <?php checked($assignment,1,true) ?>/>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('unit'); ?>"><?php _e('Show My Units','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'unit' ); ?>" name="<?php echo $this->get_field_name( 'unit' ); ?>" type="checkbox" value="1"  <?php checked($unit,1,true) ?>/>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('download'); ?>"><?php _e('Show My Downloads','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'download' ); ?>" name="<?php echo $this->get_field_name( 'download' ); ?>" type="checkbox" value="1"  <?php checked($download,1,true) ?>/>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('finished'); ?>"><?php _e('Finished Courses','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'finished' ); ?>" name="<?php echo $this->get_field_name( 'finished' ); ?>" type="checkbox" value="1"  <?php checked($finished,1,true) ?>/>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of items','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" />
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

