<?php

add_action( 'widgets_init', 'wplms_dash_calendar' );

function wplms_dash_calendar() {
    register_widget('wplms_dash_calendar');
}

class wplms_dash_calendar extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'wplms_dash_calendar', 'description' => __('Recent activity from Student', 'wplms-dashboard') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_dash_calendar' );
    parent::__construct( 'wplms_dash_calendar', __(' DASHBOARD : Calendar', 'wplms-dashboard'), $widget_ops, $control_ops );
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $num =  $instance['number'];
    $friends =  $instance['friends'];

    $width =  $instance['width'];
    echo '<div class="'.$width.'"><div class="dash-widget activity">'.$before_widget;

    // Display the widget title 
    if ( $title )
      	echo $before_title . $title . $after_title;

        if(isset($friends) && $friends){
	      	$friends = friends_get_friend_user_ids( bp_loggedin_user_id() );
			    $friends[] = bp_loggedin_user_id();
			    $friends_and_me = implode( ',', (array) $friends );
		}else
			$friends_and_me =  bp_loggedin_user_id();

		global $wpdb,$bp;

		$activities=apply_filters('wplms_dashboard_activity', $wpdb->get_results("
			SELECT *
		    FROM {$bp->activity->table_name} AS activity
		    WHERE 	activity.user_id IN ($friends_and_me)
		    AND     (activity.action != '' OR activity.action IS NOT NULL)
		    ORDER BY activity.date_recorded DESC
		    LIMIT 0,$num
		"));

		if(isset($activities) && is_array($activities)){

			echo '<div class="student_activity"><a class="small button '.(( $title )?'withtitle':'').'">'.__('VIEW ALL','wplms-dashboard').'</a>
					<ul class="dash-activities">';
			foreach($activities as $activity){
				if(isset($activity->action) && $activity->action != ''){
				$time=tofriendlytime(time()-strtotime($activity->date_recorded));
				echo '<li class="'.$activity->component.' '.$activity->type.'">
						<div class="dash-activity">
							<span class="dash-activity-time">'.$time.' '.__('AGO','wplms-dashboard').'</span>
								<strong>'.$activity->action.'</strong>
								<p>'.$activity->content.'</p>
						</div>
					  </li>';
				}			  
			}	
			echo '</ul>
			</div></div>';
		}
		

        echo $after_widget.'</div>';
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
	    $instance = $old_instance;
	    $instance['title'] = strip_tags($new_instance['title']);
	    $instance['number'] = $new_instance['number'];
	    $instance['friends'] = $new_instance['friends'];
	    $instance['width'] = $new_instance['width'];
	    return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                        'title'  => __('Student Activity','wplms-dashboard'),
                        'number'  => 5,
                        'friends' => 1,
                        'width' => 'col-md-6 col-sm-12'
                    );
  		  $instance = wp_parse_args( (array) $instance, $defaults );
        $title  = esc_attr($instance['title']);
        $number = esc_attr($instance['number']);
        $friends = esc_attr($instance['friends']);
        $width = esc_attr($instance['width']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of activities in one screen','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('friends'); ?>"><?php _e('Show Me & My Friends Activity','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'friends' ); ?>" name="<?php echo $this->get_field_name( 'friends' ); ?>" type="checkbox" value="1"  <?php checked($friends,1,true) ?>/>
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