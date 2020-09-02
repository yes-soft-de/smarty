<?php

add_action( 'widgets_init', 'wplms_line_break_widget' );

function wplms_line_break_widget() {
    register_widget('wplms_line_break');
}

class wplms_line_break extends WP_Widget {

    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'wplms_line_break', 'description' => __('Line Widget for Dashboard', 'wplms-dashboard') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_line_break' );
    parent::__construct( 'wplms_line_break', __(' DASHBOARD : Line Break', 'wplms-dashboard'), $widget_ops, $control_ops );
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $width =  $instance['width'];
    $content =  $instance['content'];

    echo '<hr class="clear">';
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
	    $instance = $old_instance;
	    return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array();
        ?>
        <p>
          <label><?php _e('Line Break : New Line','wplms-dashboard'); ?></label> 
        </p>
        <?php 
    }
} 

?>