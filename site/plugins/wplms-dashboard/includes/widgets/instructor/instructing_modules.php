<?php

add_action( 'widgets_init', 'wplms_dash_instructing_modules_widget' );

function wplms_dash_instructing_modules_widget() {
    register_widget('wplms_dash_instructing_modules');
}

class wplms_dash_instructing_modules extends WP_Widget {

    /** constructor -- name this the same as the class above */
    function __construct() {
      $widget_ops = array( 'classname' => 'wplms_dash_instructing_modules', 'description' => __('Instructing Modules  Widget for Dashboard', 'wplms-dashboard') );
      $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_dash_instructing_modules' );
      parent::__construct( 'wplms_dash_instructing_modules', __(' DASHBOARD : Instructing Modules', 'wplms-dashboard'), $widget_ops, $control_ops );
    }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );
      $defaults = array(
        'title' => '',
        'post_types'=>array(),
        'number'=>5,
        'orderby' => 'post_date',
        'order' => 'DESC'
        );

      $post_args = wp_parse_args( $instance, $defaults );
      extract($post_args);

      $user_id = get_current_user_id();

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $title );

    echo '<div class="'.$width.'">
            <div class="dash-widget">'.$before_widget;

    global $wpdb;
    
    // Display the widget title 
    if ( $title )
        echo $before_title . $title . $after_title;
        
        $user_id = get_current_user_id();

        echo '<div id="vibe-tabs-instructing-modules" class="tabs tabbable">
              <ul class="nav nav-tabs clearfix">';

        if(!empty($post_types)){
          foreach($post_types as $post_type){
            if(function_exists('post_type_exists') && post_type_exists($post_type)){
                $posttype_obj=get_post_type_object($post_type);
                echo '<li><a href="#tab-my'. $post_type.'" data-toggle="tab">'.$posttype_obj->labels->name.'</a></li>';
            }
          }
           echo '</ul><div class="tab-content">';
            foreach($post_types as $post_type){
              if(function_exists('post_type_exists') && post_type_exists($post_type)){
                $posttype_obj=get_post_type_object($post_type);
                  echo '<div id="tab-my'.$post_type.'" class="tab-pane">';
                  $args = apply_filters('wplms_dashboard_instrcuting_modules_args',array(
                      'post_type' => $post_type,
                      'post_status' => 'publish',
                      'author' => $user_id,
                      'posts_per_page' => $number,
                      'orderby' =>$orderby,
                      'order' => $order
                       ));
     
                $the_posts= new Wp_Query($args);
                if($the_posts->have_posts()){
                  echo '<ul class="dashboard-my'.$post_type.'">';
                  while($the_posts->have_posts()){
                      $the_posts->the_post();
                      global $post;
                      echo '<li><a href="'.get_permalink($post->ID).'">'.$post->post_title.'</a></li>';
                  }
                  echo '</ul>';  
                }else{
                  echo '<div class="message error">'.sprintf(__('No %s found','wplms-dashboard'),$posttype_obj->labels->name).'</div>';
                }
                wp_reset_postdata();
                echo '</div>';
              }
            }
             echo '</div>';
        }
       echo '</div></div>'.$after_widget.'
        </div>
        </div>';
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
      $instance = $old_instance;
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['post_types'] = $new_instance['post_types'];
      $instance['number'] = $new_instance['number'];
      $instance['orderby'] = $new_instance['orderby'];
      $instance['order'] = $new_instance['order'];
      $instance['width'] = $new_instance['width'];
      
      return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) { 
     $ptypes = get_post_types(array('public'   => true),'objects');
     $posts = array();
     foreach($ptypes as $k => $p){
      $posts[]=$p->name;
     } 
     
        $defaults = array( 
                        'title'  => __('Instructing Modules','wplms-dashboard'),
                        'width' => 'col-md-6 col-sm-12',
                        'post_types' => array('course')
                    );
      $instance = wp_parse_args( (array) $instance, $defaults );
        $title  = esc_attr($instance['title']);
        
        $number= esc_attr($instance['number']);
        $orderby= esc_attr($instance['orderby']);
        $order= esc_attr($instance['order']);
        $width = esc_attr($instance['width']);
        $post_types = $instance['post_types'];
        if(empty($post_types)){
          $post_types = array();
        }

        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('post_types'); ?>"><?php _e('Select Post Types:','wplms-dashboard'); ?></label> 
          <select class="select" id="<?php echo $this->get_field_id('post_types'); ?>" name="<?php echo $this->get_field_name('post_types'); ?>[]" multiple>
          <?php
           
            
            foreach($ptypes as $ptype){

              echo '<option value="'.$ptype->name.'" '.(in_array($ptype->name,$post_types)?'selected':'').'>'.$ptype->label.'</option>';
            }
          ?>
          </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of items','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" />
        </p>
        <p>
        <label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order by','wplms-dashboard'); ?></label> 
          <select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
              <?php
              $orderby_array=apply_filters('wplms_dashboard_instructing_modules_settings',array(
                  'date' => __('Publish Date','wplms-dashboard'),
                  'title' =>__('Alphabetical','wplms-dashboard'),
                  'menu_order' => __('Menu order','wplms-dashboard'),
                  
                ));
              foreach($orderby_array as $key => $value){
                echo '<option value="'.$key.'" '.selected($key,$orderby,false).'>'.$value.'</option>';
              }
              ?>
            </select>
            </p>
             <p>
             <label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order','wplms-dashboard'); ?></label> 
            <select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
              <?php
                echo '<option value="DESC" '.selected("DESC",$order,false).'>DESC</option><option value="ASC" '.selected("ASC",$order,false).'>ASC</option>';
              ?>
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