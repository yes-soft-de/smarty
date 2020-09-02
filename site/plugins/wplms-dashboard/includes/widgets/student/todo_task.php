<?php

add_action( 'widgets_init', 'wplms_dash_tasks_widget' );

function wplms_dash_tasks_widget() {
    register_widget('wplms_dash_tasks');
}

class wplms_dash_tasks extends WP_Widget {

    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'wplms_dash_tasks', 'description' => __('To Do list Widget', 'wplms-dashboard') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_dash_tasks' );
    parent::__construct( 'wplms_dash_tasks', __(' DASHBOARD : To Do Tasks', 'wplms-dashboard'), $widget_ops, $control_ops );

    add_action('wp_ajax_save_tasks',array($this,'save_tasks'));
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $width =  $instance['width'];
    $date =  $instance['date'];
    $priority =  $instance['priority'];

    echo '<div class="'.$width.'">
            <div class="dash-widget">'.$before_widget;

    // Display the widget title 
    if ( $title )
      	echo $before_title . $title . $after_title;
    		
        echo '<div class="dash-task-list"><ul class="task_list">';
        $user_id = get_current_user_id();
        $tasks = get_user_meta($user_id,'tasks',true);
        

        if(isset($tasks) && is_array($tasks)){
           foreach($tasks as $task){
              echo '<li><a class="task-status '.$task->status.'"></a><p>'.$task->text.'</p><span>'.$task->date.'</span></li>';
           }
        }
        echo '</ul>
              <ul>
                <li class="add_new"><a><i class="icon-plus-1"></i></a><input type="text" class="add_new_task" placeholder="'.__('ADD NEW TASK','wplms-dashboard').'" /> <span>'.date( 'M-d').'</span></li>
              </ul>
              <ul class="select-task-status">
                <li><a class="remove"></a></li>
                <li><a class="normal"></a></li>
                <li><a class="low"></a></li>
                <li><a class="high"></a></li>
                <li><a class="done"></a></li>
              </ul>
        </div>';
        echo '<a class="small button save_tasks">'.__('SAVE','wplms-dashboard').'</a>';
        echo $after_widget.'
        </div>
        </div>';
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
	    $instance = $old_instance;
	    $instance['title'] = strip_tags($new_instance['title']);
	    $instance['date'] = $new_instance['date'];
      $instance['priority'] = $new_instance['priority'];
	    $instance['width'] = $new_instance['width'];
	    return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                        'title'  => __('To do List','wplms-dashboard'),
                        'date' => 1,
                        'priority' => 1,
                        'width' => 'col-md-6 col-sm-12'
                    );
  		  $instance = wp_parse_args( (array) $instance, $defaults );
        $title  = esc_attr($instance['title']);
        $date = esc_attr($instance['date']);
        $priority = esc_attr($instance['priority']);
        $width = esc_attr($instance['width']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('date'); ?>"><?php _e('Show task date','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'date' ); ?>" name="<?php echo $this->get_field_name( 'date' ); ?>" type="checkbox" value="1"  <?php checked($date,1,true) ?>/>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('priority'); ?>"><?php _e('Enable task priority','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'priority' ); ?>" name="<?php echo $this->get_field_name( 'priority' ); ?>" type="checkbox" value="1"  <?php checked($priority,1,true) ?>/>
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

    function save_tasks(){
      $user_id = get_current_user_id();
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security')){
             _e('Security issue.','wplms-dashboard');
             die();
        }

      $tasks = json_decode(stripslashes($_POST['tasks']));

      if(update_user_meta($user_id,'tasks',$tasks)){
        return 1;
      }else{
        return __('Unable to Save','wplms-dashboard');
      }
      die();
    }
} 

?>