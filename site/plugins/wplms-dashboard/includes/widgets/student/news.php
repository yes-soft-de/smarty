<?php

add_action( 'widgets_init', 'wplms_dash_news' );

function wplms_dash_news() {
    register_widget('wplms_dash_news');
}

class wplms_dash_news extends WP_Widget {
 
    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'wplms_dash_news', 'description' => __('News for Members', 'wplms-dashboard') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_dash_news' );
    parent::__construct( 'wplms_dash_news', __(' DASHBOARD : Member News', 'wplms-dashboard'), $widget_ops, $control_ops );
  }

    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $num =  $instance['number'];
    $width =  $instance['width'];
    echo '<div class="'.$width.'"><div class="dash-widget '.(($title)?'':'notitle').'">'.$before_widget;
      if ( $title )
        echo $before_title . $title . $after_title;
			echo '<div class="news_block">';

       
      
      global $wpdb;

      $user_id=get_current_user_id();
      $user_courses=$wpdb->get_results($wpdb->prepare("
        SELECT posts.ID as ID
          FROM {$wpdb->posts} AS posts
          LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
          WHERE   posts.post_type   = 'course'
        AND   posts.post_status   = 'publish'
        AND   rel.meta_key   = %s
      ",$user_id));
      $course_ids=array();
      if(isset($user_courses) && is_array($user_courses)){
          foreach($user_courses as $course){
            $course_ids[]=$course->ID;
          }
      }

      
      if(!isset($course_ids) || !is_array($course_ids))
      $course_ids = array();

			$query_args = apply_filters('wplms-dashboard-news_filter',array(
        'post_type'=> 'news',
        'post_per_page'=> $num,
        'post_status' => 'publish',
        'meta_query'=>array(
          array(
            'meta_key' => 'vibe_news_course',
            'compare' => 'IN',
            'value' => $course_ids,
            'type' => 'numeric'
            ),
          )
        )); 
      $the_query = new WP_Query($query_args);

      //print_r($the_query);

      switch($width){
        case 'col-md-12':
         $size = 'full';
         break;
        case 'col-md-6 col-sm-12':
        $size = 'medium';
        break;
        case 'col-md-8 col-sm-12':
        case 'col-md-9 col-sm-12':
        $size = 'big';
        break;
        default:
        $size = 'small';
        break;
      }
      if(function_exists('vibe_get_option'))
        $instructor_field = vibe_get_option('instructor_field');
      
      if($the_query->have_posts()){
        echo '
          <ul class="dash-news slides">';
        while($the_query->have_posts()){
          $the_query->the_post();
          
          $format=get_post_format(get_the_ID());
          if(!isset($format) || !$format)
            $format = 'standard';

          $post_author = get_the_author_meta('ID');
          $displayname = bp_core_get_user_displayname($post_author);
          $special='';
          echo $field;
          if(bp_is_active('xprofile'))
            $special = bp_get_profile_field_data('field='.$instructor_field.'&user_id='.$post_author);

          echo '<li>';
          switch($format){
            case 'aside':
            echo get_the_post_thumbnail($post->ID,$size);
            echo '<div class="'.$format.'-block">';
            the_content();
            echo '<div class="news_author">'.bp_core_fetch_avatar(array( 'item_id' => $post_author, 'type' => 'thumb')).'
            <h5><a href="'.bp_core_get_user_domain($post_author) .'">'.$displayname.'<span>'.$special.'</span></a></h5>
            <ul>'.get_the_term_list($post->ID,'news-tag','<li>','</li><li>','</li>').'</ul>
            </div>';
            echo '</div>';
            break;
            case 'image':
            echo get_the_post_thumbnail($post->ID,$size);
            echo '<div class="'.$format.'-block"><h4>'.get_the_title().'</h4>';
            the_content();
            echo '<div class="news_author">'.bp_core_fetch_avatar(array( 'item_id' => $post_author, 'type' => 'thumb')).'
            <h5><a href="'.bp_core_get_user_domain($post_author) .'">'.$displayname.'<span>'.$special.'</span></a></h5>
            <ul>'.get_the_term_list($post->ID,'news-tag','<li>','</li><li>','</li>').'</ul>
            </div>';
            echo '</div>';
            break;
            case 'chat':
            echo '<div class="'.$format.'-block">';
            the_content();
            echo '<a href="'.get_comments_link().'" class="chat_comments">';
            comments_number( '0', '1', '%' );
            echo '<i class="icon-bubble"></i></a>';
            echo '<div class="news_author">'.bp_core_fetch_avatar(array( 'item_id' => $post_author, 'type' => 'thumb')).'
            <h5><a href="'.bp_core_get_user_domain($post_author) .'">'.$displayname.'<span>'.$special.'</span></a></h5>
            <ul>'.get_the_term_list($post->ID,'news-tag','<li>','</li><li>','</li>').'</ul>
            </div>';
            echo '</div>';
            break;
            case 'quote':
            case 'status':
            case 'gallery':
            case 'audio':
            case 'video':
            echo '<div class="'.$format.'-block">';
            the_content();
            echo '<div class="news_author">'.bp_core_fetch_avatar(array( 'item_id' => $post_author, 'type' => 'thumb')).'
            <h5><a href="'.bp_core_get_user_domain($post_author) .'">'.$displayname.'<span>'.$special.'</span></a></h5>
            <ul>'.get_the_term_list($post->ID,'news-tag','<li>','</li><li>','</li>').'</ul>
            </div>';
            echo '</div>';
            break;
            default:
            echo '<div class="'.$format.'-block">
                  <h3 class="heading">'.get_the_title().'</h3>';
            echo '<div class="news_thumb">'.get_the_post_thumbnail().'</div>';
                  the_content();
            echo '<div class="news_author">'.bp_core_fetch_avatar(array( 'item_id' => $post_author, 'type' => 'thumb')).'
            <h5><a href="'.bp_core_get_user_domain($post_author) .'">'.$displayname.'<span>'.$special.'</span></a></h5>
            <ul>'.get_the_term_list($post->ID,'news-tag','<li>','</li><li>','</li>').'</ul>
            </div>';      
            echo '</div>';
            break;
          }
          echo '</li>';
        }
        echo '</ul>';
        echo '<a href="'.get_post_type_archive_link('news').'" target="_blank" class="small button '.(( $title )?'withtitle':'').'"><i class="icon-plus-1"></i></a>';

      }else{
        echo '<ul class="dash-news slides">';
        echo '<li><div class="error-block">'.__('No news for you !','wplms-dashboard').'</div></li>';
        echo '</ul>';
      }
      wp_reset_postdata();
      if(current_user_can('edit_posts' ))
      echo '<a href="'.admin_url( 'post-new.php?post_type=news').'" target="_blank" class="small button add_news'.(( $title )?'withtitle':'').'"><i class="icon-file-add"></i></a>';

        echo '</div>'.$after_widget.'</div></div>';
                
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
                        'title'  => __('News','wplms-dashboard'),
                        'number'  => 5,
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
          <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Maximum Number of New blocks in carousel','wplms-dashboard'); ?></label> 
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
