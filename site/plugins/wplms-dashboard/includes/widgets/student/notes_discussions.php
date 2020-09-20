<?php

add_action( 'widgets_init', 'wplms_notes_discussion_widget' );

function wplms_notes_discussion_widget() {
    register_widget('wplms_notes_discussion');
}

class wplms_notes_discussion extends WP_Widget {

    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'wplms_notes_discussion', 'description' => __('Notes & Discussion Widget for Dashboard', 'wplms-dashboard') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_notes_discussion' );
    parent::__construct( 'wplms_notes_discussion', __(' DASHBOARD : Notes & Discussion', 'wplms-dashboard'), $widget_ops, $control_ops );
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $width =  $instance['width'];
    $number = $instance['number'];
    echo '<div class="'.$width.'">
            <div class="dash-widget">'.$before_widget;

    // Display the widget title 
    if ( $title )
        echo $before_title . $title . $after_title;
        
        $unit_comments = vibe_get_option('unit_comments');
        if(isset($unit_comments) && is_numeric($unit_comments)){
            $link = get_permalink($unit_comments);
        }else
            $link = '#';

        echo '<div id="vibe-tabs-notes_discussion" class="tabs tabbable">   
             <a href="'.$link.'" class="view_all_notes">'.__('SEE ALL','wplms-dashboard').'</a>
              <ul class="nav nav-tabs clearfix">
                <li><a href="#tab-notes" data-toggle="tab">'.__('My Notes','wplms-dashboard').'</a></li>
                <li><a href="#tab-discussion" data-toggle="tab">'.__('My Discussions','wplms-dashboard').'</a></li>
            </ul><div class="tab-content">';
            echo '<div id="tab-notes" class="tab-pane">';
            $user_id =get_current_user_id();
            $args = apply_filters('wplms_notes_dicussion_dashboard_args',array(
                'number'              => $number,
                'post_status'         => 'publish',
                'post_type'           => 'unit',
                'status'              => 'approve',
                'type'                => 'note',
                'user_id'             => $user_id
            ));
            echo '<div id="notes_query">'.json_encode($args).'</div>
                    <div id="notes_discussions">';
                    $comments_query = new WP_Comment_Query;
                    $comments = $comments_query->query( $args );
                    // Comment Loop
                    $vibe_notes_discussions= new vibe_notes_discussions();
                    $vibe_notes_discussions->comments_loop($comments);
            echo '</div></div>';
            echo '<div id="tab-discussion" class="tab-pane">';
            $args = apply_filters('wplms_notes_dicussion_dashboard_args',array(
                'number'              => $number,
                'post_status'         => 'publish',
                'post_type'           => 'unit',
                'status'              => 'approve',
                'type'        => 'public',
                'user_id'             => $user_id
            ));
            echo '<div id="notes_query">'.json_encode($args).'</div>
                    <div id="notes_discussions">';
                    $comments_query = new WP_Comment_Query;
                    $comments = $comments_query->query( $args );
                    // Comment Loop
                    $vibe_notes_discussions= new vibe_notes_discussions();
                    $vibe_notes_discussions->comments_loop($comments);
            echo '</div></div>';

            echo '</div></div>';
        echo $after_widget.'
        </div>
        </div>';
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['number'] = $new_instance['number'];
        $instance['width'] = $new_instance['width'];
        return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                        'title'  => __('Notes& Discussion','wplms-dashboard'),
                        'content' => '',
                        'width' => 'col-md-6 col-sm-12'
                    );
          $instance = wp_parse_args( (array) $instance, $defaults );
        $title  = esc_attr($instance['title']);
        $number = esc_attr($instance['number']);
        $width = esc_attr($instance['width']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
         <p>
          <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of Notes/Dicussions','wplms-dashboard'); ?></label> 
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

?>