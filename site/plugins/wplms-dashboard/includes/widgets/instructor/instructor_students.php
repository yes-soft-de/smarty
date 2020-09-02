<?php

add_action( 'widgets_init', 'wplms_instructor_students_widget' );

function wplms_instructor_students_widget() {
    register_widget('wplms_instructor_students_widget');
}

class wplms_instructor_students_widget extends WP_Widget {

    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'wplms_instructor_students_widget', 'description' => __('Instructor Students widget', 'wplms-dashboard') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_instructor_students_widget' );
    parent::__construct( 'wplms_instructor_students_widget', __(' DASHBOARD : Instructor Students', 'wplms-dashboard'), $widget_ops, $control_ops );

    add_action('wp_ajax_instructor_load_more_students',array($this,'instructor_load_more_students'));
    
  }
        
    function widget( $args, $instance ) {

        extract( $args );

        if(!is_user_logged_in() || !current_user_can('edit_posts'))
            return;


        //Our variables from the widget settings.
        $title = apply_filters('widget_title', $instance['title'] );
        $width =  $instance['width'];
        

        echo '<div class="'.$width.'">
                <div class="dash-widget">'.$before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;


       
        $user_id=get_current_user_id();
        

        //get instructor students
        //get instructor courses
        if(class_exists('CoAuthors_Plus')){
            $nickname = get_user_meta($user_id,'nickname',true);
            
               $args = apply_filters('wplms_instructor_courses_args', 
                array( 
                    'post_type'=>'course',
                    'posts_per_page' => -1,
                    'author_name' => $nickname,
                    'fields' => 'ids',
                    ),
                $user_id);
        }else{
            $args = apply_filters('wplms_instructor_courses_args', array( 
                    'post_type'=>'course',
                    'posts_per_page' => -1,
                    'author'=>$user_id,
                    'fields' => 'ids'
                ),$user_id);
        }

        $courses = new WP_Query($args);
        if(!empty($courses->posts)){
            $inst_courses = implode(',',$courses->posts);
            global $bp;
             global $wpdb;

            $limit = 20;
            $query_students = $wpdb->get_results("Select user_id,item_id FROM {$bp->activity->table_name} WHERE item_id IN ($inst_courses) AND type = 'subscribe_course' order by id DESC limit 0,$limit");

            if(!empty($query_students)){
                echo '<div class="instructor_students_widget_students_list">';
                $students=array();

                foreach($query_students as $student){
                    if(empty($students[$student->user_id])){
                        $students[$student->user_id]=array(
                            'name'=>bp_core_get_user_displayname($student->user_id),
                            'courses'=>array(
                                $student->item_id => array('title'=>get_the_title($student->item_id)),
                            ),
                        );
                    }else{
                        if(empty($students[$student->user_id]['courses'][$student->item_id])){
                            $students[$student->user_id]['courses'][$student->item_id]=array('title'=>get_the_title($student->item_id));
                        }
                    }
                }
                if(!empty($students)){
                    foreach($students as $student){
                        echo '<div class="student_item">
                        <div class="student_details"><strong>'.$student['name'].'</strong></div>
                        <div class="course_details">';
                        foreach($student['courses'] as $course_id=>$course){
                            echo '<div class="student_course" data-course='.$course_id.'>'.$course['title'].'</div>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }

                    if(count($query_students) == $limit)
                    echo '<a class="instructor_load_more_students link">'.__('Load more','vibe').'</a>';
                        ?>
                        <script>
                            let page = 0;
                            $('.instructor_load_more_students').on('click',function(){
                                var $this = $(this);
                                if($this.hasClass('loading'))
                                    reutnr;

                                page++;
                                $.ajax({
                                  type: "POST",
                                  url: ajaxurl,
                                  data: { action: 'instructor_load_more_students',
                                          security:'<?php echo wp_create_nonce('instructor_load_more_students'); ?>',
                                          courses: '<?php echo $inst_courses; ?>',
                                          page: page,
                                        },
                                  cache: false,
                                  success: function (html) {
                                    $this.removeClass('loading');
                                    $this.before(html);
                                  }
                                });
                            });
                            
                        </script>
                        <?php
                }else{
                    echo '<div class="message"><p>'._x('No students found.','message in dashbaord widget','vibe').'</p></div>';
                }
                
                echo '</div>';
            }else{

            }
            
        }
        
        echo '</div></div>';
    
    }

    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
      $instance = $old_instance;
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['width'] = $new_instance['width'];
      return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                        'title'  => __('Instructor Students','wplms-dashboard'),
                        'width' => 'col-md-6 col-sm-12'
                    );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $title  = esc_attr($instance['title']);
        $width = esc_attr($instance['width']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
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

    function instructor_load_more_students(){

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'instructor_load_more_students') || !current_user_can('edit_posts')){
             echo '<p class="message">'.__('Security error','wplms-dashboard').'</p>';
             die();
        }

        $page = $_POST['page'];

        $limit = 2;
        global $wpdb,$bp;
        $query_students = $wpdb->get_results("Select user_id,item_id FROM {$bp->activity->table_name} WHERE item_id IN (".esc_attr($_POST['courses']).") AND type = 'subscribe_course' order by id DESC limit ".($page*$limit).",$limit");

       
        if(!empty($query_students)){
            $students=array();

            foreach($query_students as $student){
                if(empty($students[$student->user_id])){
                    $students[$student->user_id]=array(
                        'name'=>bp_core_get_user_displayname($student->user_id),
                        'courses'=>array(
                            $student->item_id => array('title'=>get_the_title($student->item_id)),
                        ),
                    );
                }else{
                    if(empty($students[$student->user_id]['courses'][$student->item_id])){
                        $students[$student->user_id]['courses'][$student->item_id]=array('title'=>get_the_title($student->item_id));
                    }
                }
            }
            if(!empty($students)){
                foreach($students as $student){
                    echo '<div class="student_item">
                    <div class="student_details"><strong>'.$student['name'].'</strong></div>
                    <div class="course_details">';
                    foreach($student['courses'] as $course_id=>$course){
                        echo '<div class="student_course" data-course='.$course_id.'>'.$course['title'].'</div>';
                    }
                    echo '</div>';
                }
            }else{
                echo '<style>.instructor_load_more_students{display:none;}</style>';
            }
        }else{
            echo '<style>.instructor_load_more_students{display:none;}</style>';
        }

        die();
    }
} 

?>