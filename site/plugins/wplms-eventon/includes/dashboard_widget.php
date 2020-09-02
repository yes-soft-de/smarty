<?php

add_action( 'widgets_init', 'wplms_dash_cal_widget' );

function wplms_dash_cal_widget() {
    register_widget('wplms_dash_cal');
}

class wplms_dash_cal extends WP_Widget {

    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'wplms_dash_cal', 'description' => __('Calendar for Dashboard', 'wplms-eventon') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_dash_cal' );
    parent::__construct( 'wplms_dash_cal', __(' DASHBOARD : Calendar Widget', 'wplms-eventon'), $widget_ops, $control_ops );
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $width =  $instance['width'];

    $user_id = get_current_user_id();
    $courses = bp_course_get_user_courses($user_id);

    $courses = implode(',',$courses);

    $content =  '[add_eventon_dv cal_id="2" today="no" show_et_ft_img="no" ft_event_priority="no" wplms_course="'.$courses.'"]';
    
    echo '<div class="'.$width.'">
            <div class="dash-widget">'.$before_widget;

    // Display the widget title 
    if ( $title )
      	echo $before_title . $title . $after_title;
    		
        echo do_shortcode($content);

        echo $after_widget.'
        </div>
        </div>';
                
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
                        'title'  => __('My Course Events','wplms-eventon'),
                        'content' => '',
                        'width' => 'col-md-6 col-sm-12'
                    );
  		  $instance = wp_parse_args( (array) $instance, $defaults );
        $title  = esc_attr($instance['title']);
        $content = esc_attr($instance['content']);
        $width = esc_attr($instance['width']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-eventon'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Select Width','wplms-eventon'); ?></label> 
          <select id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>">
          	<option value="col-md-3 col-sm-6" <?php selected('col-md-3 col-sm-6',$width); ?>><?php _e('One Fourth','wplms-eventon'); ?></option>
          	<option value="col-md-4 col-sm-6" <?php selected('col-md-4 col-sm-6',$width); ?>><?php _e('One Third','wplms-eventon'); ?></option>
          	<option value="col-md-6 col-sm-12" <?php selected('col-md-6 col-sm-12',$width); ?>><?php _e('One Half','wplms-eventon'); ?></option>
            <option value="col-md-8 col-sm-12" <?php selected('col-md-8 col-sm-12',$width); ?>><?php _e('Two Third','wplms-eventon'); ?></option>
             <option value="col-md-8 col-sm-12" <?php selected('col-md-9 col-sm-12',$width); ?>><?php _e('Three Fourth','wplms-eventon'); ?></option>
          	<option value="col-md-12" <?php selected('col-md-12',$width); ?>><?php _e('Full','wplms-eventon'); ?></option>
          </select>
        </p>
        <?php 
    }
} 

?>