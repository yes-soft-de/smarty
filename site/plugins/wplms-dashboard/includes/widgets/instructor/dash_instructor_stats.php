<?php

add_action( 'widgets_init', 'wplms_instructor_dash_stats_widget' );

function wplms_instructor_dash_stats_widget() {
    register_widget('wplms_instructor_dash_stats');
}

class wplms_instructor_dash_stats extends WP_Widget {

    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'wplms_instructor_dash_stats', 'description' => __('Simple stats scores for instructors', 'wplms-dashboard') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_instructor_dash_stats' );
    parent::__construct( 'wplms_instructor_dash_stats', __(' DASHBOARD : Instructor Simple Stats', 'wplms-dashboard'), $widget_ops, $control_ops );
  }
        
    function widget( $args, $instance ) {
    extract( $args );

    global $wpdb;
    $user_id=get_current_user_id();
    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $width =  $instance['width'];
    $stats =  $instance['stats'];
    $user_id = get_current_user_id();

    echo '<div class="'.$width.'">
            <div class="dash-widget '.$stats.'">'.$before_widget;
    		
          
        if(isset($stats)){
          switch($stats){
            case 'woo_commission':

              $total_commission = get_user_meta($user_id,'total_commission',true);
              if(function_exists('get_woocommerce_currency_symbol'))
                $symbol= get_woocommerce_currency_symbol();

              if(!isset($symbol))
                $symbol='$';

              if(function_exists('wc_price')){
                $value = str_replace('span','strong',wc_price(round($total_commission,0)));
              }
              //$value = $symbol.round($total_commission,0);
              if(!is_numeric($total_commission))
                $value=__('N.A','wplms-dashboard');

              $item_meta_table =$wpdb->prefix.'woocommerce_order_itemmeta';
              $commision_array=$wpdb->get_results($wpdb->prepare("
              SELECT order_meta.meta_value as value
              FROM $item_meta_table AS order_meta
              WHERE  ( order_meta.meta_key = %s 
              OR order_meta.meta_key = %s ) 
              ORDER BY order_item_id DESC
              LIMIT 0,10
              ",'commission'.$user_id,'_commission'.$user_id),ARRAY_A);
              
              if(isset($commision_array) && is_array($commision_array)){
                  foreach($commision_array as $commision){
                    $commissions[]=$commision['value'];
                  }
              }
              if(is_array($commissions))
                $value_string=implode(',',$commissions);
              else
                $value_string='';

              if($title)
                $label=$title;
              else
                $label=__('Total Commission Earned','wplms-dashboard');
            break;
            case 'courses':
              $query = apply_filters('wplms_dashboard_instructors_course_count',$wpdb->prepare("
              SELECT count(posts.ID) as num
                FROM {$wpdb->posts} AS posts
                WHERE   posts.post_type   = 'course'
                AND   posts.post_author   = %d
            ",$user_id));

              $instructor_courses=$wpdb->get_results($query,ARRAY_A);
              if(isset($instructor_courses[0]['num']) && is_numeric($instructor_courses[0]['num']))
                $value = $instructor_courses[0]['num'];
              else
                $value=0;
              
              if($title)
                $label=$title;
              else
                $label=__('Courses Instructing','wplms-dashboard');

            break;
            case 'quizes':
              $query = apply_filters('wplms_dashboard_instructors_quiz_count',$wpdb->prepare("
              SELECT count(posts.ID) as num
                FROM {$wpdb->posts} AS posts
                WHERE   posts.post_type   = 'quiz'
                AND   posts.post_author   = %d
            ",$user_id));

              $instructor_courses=$wpdb->get_results($query,ARRAY_A);
              if(isset($instructor_courses[0]['num']) && is_numeric($instructor_courses[0]['num']))
                $value = $instructor_courses[0]['num'];
              else
                $value=0;

              if($title)
                $label=$title;
              else
                $label=__('Quizzes Created','wplms-dashboard');
            break;
            case 'units':
              $query = apply_filters('wplms_dashboard_instructors_unit_count',$wpdb->prepare("
              SELECT count(posts.ID) as num
                FROM {$wpdb->posts} AS posts
                WHERE   posts.post_type   = 'unit'
                AND   posts.post_author   = %d
            ",$user_id));

              $instructor_courses=$wpdb->get_results($query,ARRAY_A);
              if(isset($instructor_courses[0]['num']) && is_numeric($instructor_courses[0]['num']))
                $value = $instructor_courses[0]['num'];
              else
                $value=0;

              if($title)
                $label=$title;
              else
                $label=__('Units Created','wplms-dashboard');
            break;
            case 'assignments':
              $query = apply_filters('wplms_dashboard_instructors_assignment_count',$wpdb->prepare("
              SELECT count(posts.ID) as num
                FROM {$wpdb->posts} AS posts
                WHERE   posts.post_type   = 'wplms-assignment'
                AND   posts.post_author   = %d
            ",$user_id));

              $instructor_courses=$wpdb->get_results($query,ARRAY_A);
              if(isset($instructor_courses[0]['num']) && is_numeric($instructor_courses[0]['num']))
                $value = $instructor_courses[0]['num'];
              else
                $value=0;

              if($title)
                $label=$title;
              else
                $label=__('Assignments Created','wplms-dashboard');
            break;
            case 'questions':
              $query = apply_filters('wplms_dashboard_instructors_question_count',$wpdb->prepare("
              SELECT count(posts.ID) as num
                FROM {$wpdb->posts} AS posts
                WHERE   posts.post_type   = 'question'
                AND   posts.post_author   = %d
            ",$user_id));

              $instructor_courses=$wpdb->get_results($query,ARRAY_A);
              if(isset($instructor_courses[0]['num']) && is_numeric($instructor_courses[0]['num']))
                $value = $instructor_courses[0]['num'];
              else
                $value=0;

              if($title)
                $label=$title;
              else
                $label=__('Questions Created','wplms-dashboard');
            break;
            case 'badges':
                $bg=apply_filters('wplms_dashboard_instructors_course_badges', $wpdb->get_results($wpdb->prepare("
                  SELECT SUM(rel.meta_value) as total_badge
                    FROM {$wpdb->posts} AS posts
                    LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
                    WHERE   posts.post_type   = 'course'
                    AND posts.post_author = %d
                  AND   posts.post_status   = 'publish'
                  AND   rel.meta_key   = 'badge'
                ",$user_id)));
                if(isset($bg[0]->total_badge) && is_numeric($bg[0]->total_badge))
                  $value = $bg[0]->total_badge;
                else
                  $value =0;

                if($title)
                $label=$title;
              else
                $label=__('Badges Awarded','wplms-dashboard');
  
            break;
            case 'certificates':
                $ps=apply_filters('wplms_dashboard_instructors_course_certificates', $wpdb->get_results($wpdb->prepare("
                  SELECT SUM(rel.meta_value) as total_pass
                    FROM {$wpdb->posts} AS posts
                    LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
                    WHERE   posts.post_type   = 'course'
                     AND posts.post_author = %d
                  AND   posts.post_status   = 'publish'
                  AND   rel.meta_key   = 'pass'
                ",$user_id)));

                if(isset($ps[0]->total_pass) && is_numeric($ps[0]->total_pass))
                  $value = $ps[0]->total_pass;
                else
                  $value =0;

                if($title)
                $label=$title;
              else
                $label=__('Certificates awarded','wplms-dashboard');
            break;
            case 'students':
               $ps=apply_filters('wplms_dashboard_instructors_course_students', $wpdb->get_results($wpdb->prepare("
                    SELECT SUM(rel.meta_value) as total_students
                    FROM {$wpdb->posts} AS posts
                    LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
                    WHERE   posts.post_type   = 'course'
                    AND posts.post_author = %d
                    AND   posts.post_status   = 'publish'
                    AND   rel.meta_key   = 'vibe_students'
                    ",$user_id)));

                if(isset($ps[0]->total_students) && is_numeric($ps[0]->total_students))
                  $value = $ps[0]->total_students;
                else
                  $value =0;

                if($title)
                $label=$title;
              else
                $label=__('Total Students in Courses','wplms-dashboard');
            break;
            default:
              $value = apply_filters('wplms_instructor_dash_stats_default_value','',$stats);
              $value_string = apply_filters('wplms_instructor_dash_stats_default_value_string','',$stats);
              if($title)
                $label=$title;
            break;
          }
        }

        
        
        echo '<div class="dash-stats">';
        
        if($stats == 'woo_commission')
          echo '<a class="commission_reload"><i class="icon-reload"></i></a>';

        echo '<h3>'.$value.'<span>'.$label.'</span></h3>';
        if(isset($value_string) && $value_string !='')
        echo '<div class="sparkline'.$stats.'">Loading..</div>';
        echo '</div>';
        echo $after_widget.'
        </div>
        </div>';

        if(isset($value_string) && $value_string !='')
        echo "<script>jQuery(document).ready(function($){
        var myvalues = [$value_string];
        $('.sparkline$stats').sparkline(myvalues, {
          type: 'bar',
          height: 50,
          barColor: '#FFF',});
        });
      </script>";
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
	    $instance = $old_instance;
	    $instance['title'] = strip_tags($new_instance['title']);
	    $instance['stats'] = $new_instance['stats'];
	    $instance['width'] = $new_instance['width'];
	    return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                        'title'  => __('Instructor Stats','wplms-dashboard'),
                        'stats' => '',
                        'width' => 'col-md-6 col-sm-12'
                    );
  		  $instance = wp_parse_args( (array) $instance, $defaults );
        $title  = esc_attr($instance['title']);
        $stats = esc_attr($instance['stats']);
        $width = esc_attr($instance['width']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('stats'); ?>"><?php _e('Select Stats','wplms-dashboard'); ?>
          </label> 
          <select id="<?php echo $this->get_field_id( 'stats' ); ?>" name="<?php echo $this->get_field_name( 'stats' ); ?>">
          <?php
          $stats_array=apply_filters('wplms_dashboard_instructor_stats',array(
              'woo_commission' => __('WooCommerce Earnings','wplms-dashboard'),
              'courses' =>__('Number of courses','wplms-dashboard'),
              'quizes' => __('Number of Quizzes','wplms-dashboard'),
              'assignments' => __('Number of assignments','wplms-dashboard'),
              'questions' => __('Number of questions','wplms-dashboard'),
              'units' => __('Number of units','wplms-dashboard'),
              'badges' => __('Number of badges','wplms-dashboard'),
              'certificates' => __('Number of certificates','wplms-dashboard'),
              'students' => __('Number of Students','wplms-dashboard'),
            ));
          foreach($stats_array as $key => $value){
            echo '<option value="'.$key.'" '.selected($key,$stats,false).'>'.$value.'</option>';
          }
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