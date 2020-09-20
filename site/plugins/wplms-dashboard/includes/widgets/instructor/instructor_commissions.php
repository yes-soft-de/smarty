<?php

add_action( 'widgets_init', 'wplms_instructor_commission_stats_widget' );

function wplms_instructor_commission_stats_widget() {
    register_widget('wplms_instructor_commission_stats');
}

class wplms_instructor_commission_stats extends WP_Widget {

    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'wplms_instructor_commission_stats', 'description' => __('WooCommerce Commission Stats  for instructors', 'wplms-dashboard') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_instructor_commission_stats' );
    parent::__construct( 'wplms_instructor_commission_stats', __(' DASHBOARD : Instructor Commission Stats', 'wplms-dashboard'), $widget_ops, $control_ops );
    add_action('wp_ajax_generate_commission_data',array($this,'generate_commission_data'));
  }
        
    function widget( $args, $instance ) {
    extract( $args );

    global $wpdb;
    $user_id=get_current_user_id();
    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $width =  $instance['width'];
    $user_id = get_current_user_id();

    echo '<div class="'.$width.'">
            <div class="dash-widget">'.$before_widget;
    if ( $title )
        echo $before_title . $title . $after_title;


    $commision_array =  vibe_sanitize(get_user_meta($user_id,'commission_data',false));
    $commission_recieved = vibe_sanitize(get_user_meta($user_id,'commission_recieved',false));
    $sales_pie = vibe_sanitize(get_user_meta($user_id,'sales_pie',false));
    $total_commission = get_user_meta($user_id,'total_commission',true);

    if(function_exists('get_woocommerce_currency_symbol')){
      $symbol= get_woocommerce_currency_symbol();  
    }
    
    if(function_exists('wc_price')){
      $value = wc_price($total_commission);
    }
      //$value = $symbol.$total_commission;
      if(empty($sales_pie))
     {
        echo '<a class="commission_reload"><i class="icon-reload"></i></a>';
        echo '<div id="instructor_commissions" class="morris" style="padding:10px 20px 20px;text-align:center;"><div class="message"><p>'.__('No data found','wplms-dashboard').'</p></div></div>';
       
     } 
         
    echo '<a class="commission_reload"><i class="icon-reload"></i></a>';
    echo '<div id="instructor_commissions" class="morris"></div>';
    echo '<div class="row">
          <div class="col-md-6">
          <label class="sales_labels">'.__('Course','wplms-dashboard').'<strong>'.__('Earnings','wplms-dashboard').' ('.$symbol.')</strong></label>
          <div class="course_list">';
          $sales_pie_array=array();
        if(isset($sales_pie) && is_array($sales_pie) && count($sales_pie)){
          echo '<ul class="course_sales_list">';
          
          foreach($sales_pie as $cid=>$sales){
            if($cid == 'commission'){
              echo '<li>'.__('Commissions Paid','wplms-dashboard').'<strong>'.$sales.'</strong></li>';
            }
            $ctitle=get_the_title($cid);
            echo '<li>'.$ctitle.'<strong>'.$sales.'</strong></li>';
            $sales_pie_array[]=array(
              'label'=>$ctitle,
              'value' => $sales
              );
          }
          echo '</ul>';
        }
        // else{
        //   echo '<div class="message"><p>'.__('No data found','wplms-dashboard').'</p></div>';
        // }  
    echo '</div></div><div class="col-md-6">
            <div id="commission_breakup" class="morris"></div>
          </div></div>';
    echo '</div>';
    echo $after_widget.'
    </div>';
    if(isset($commision_array) && is_array($commision_array )){
        foreach($commision_array as $key=>$commission){ 
            if(isset($commission_recieved[$key])){ 
              $commision_array[$key]['commission'] = $commission_recieved[$key]['commission'];
            }else{
              $commision_array[$key]['commission'] = 0;
            }
        }
    }
    echo '<script>
            var instructor_commission_data=[';$first=0;
    
    if(isset($commision_array) && is_array($commision_array)) {       

    foreach($commision_array as $data){
      if($first)
        echo ',';
      $first=1;
      echo str_replace('"','\'',json_encode($data,JSON_NUMERIC_CHECK));
    }}
    echo  '];
    var commission_breakup =[';$first=0;
    if(isset($sales_pie_array) && is_Array($sales_pie_array))
    foreach($sales_pie_array as $data){
      if($first)
        echo ',';
      $first=1;
      echo str_replace('"','\'',json_encode($data,JSON_NUMERIC_CHECK));
    }
    echo  '];
    </script>';
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
                        'title'  => __('Instructor Stats','wplms-dashboard'),
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

    function generate_commission_data(){
      global $wpdb;
      $user_id = get_current_user_id();

      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') || !current_user_can('edit_posts')){
           echo __('Security error','wplms-dashboard');
           die();
      }

      if(function_exists('vibe_get_option'))
      $instructor_commission = vibe_get_option('instructor_commission');
    
    if(!isset($instructor_commission) || !$instructor_commission)
      $instructor_commission = 70;

    $commissions = get_option('instructor_commissions');


    $query = apply_filters('wplms_dashboard_courses_instructors',$wpdb->prepare("
    SELECT posts.ID as course_id
      FROM {$wpdb->posts} AS posts
      WHERE   posts.post_type   = 'course'
      AND   posts.post_author  = %d
  ",$user_id));

    $instructor_courses=$wpdb->get_results($query,ARRAY_A);

    $total_commission=0;
    $commision_array=array();
    $course_product_map=array();
    $daily_val = array();
    if(count($instructor_courses)){

      foreach($instructor_courses as $key => $value){
        $course_id=$value['course_id'];

        $pid=get_post_meta($course_id,'vibe_product',true);
        if(is_numeric($pid)){
        if(isset($commissions[$course_id][$user_id])){
          $course_commission[$course_id]=$commissions[$course_id][$user_id];
        }else{
          $course_commission[$value['course_id']] = $instructor_commission;
        }
          $product_ids[]= $pid;
          $course_product_map[$pid]=$course_id;
        }
      }
      if(!is_array($product_ids))
        die();
      
      $product_id_string=implode(',',$product_ids);
      $item_meta_table = $wpdb->prefix.'woocommerce_order_itemmeta';
      $order_items_table= $wpdb->prefix.'woocommerce_order_items';

      // CALCULATED COMMISSIONS

      $product_sales=$wpdb->get_results("
       SELECT order_meta.meta_value as value,order_meta.order_item_id as item_id,MONTH(post_meta.meta_value) as date,order_items.order_id as order_id
       FROM $item_meta_table AS order_meta
       LEFT JOIN $order_items_table as order_items ON order_items.order_item_id = order_meta.order_item_id
       LEFT JOIN {$wpdb->postmeta} as post_meta ON post_meta.post_id = order_items.order_id
        LEFT JOIN {$wpdb->posts} as posts ON posts.ID = order_items.order_id
       WHERE  (order_meta.meta_key = 'commission$user_id' 
       OR order_meta.meta_key = '_commission$user_id')
       AND  post_meta.meta_key = '_completed_date' 
       AND posts.post_status='wc-completed'
       AND post_meta.meta_value!='NULL'
       ",ARRAY_A);

      $sales_pie=array();
      $i=count($product_sales);
      if(is_array($product_sales) && $i){

        foreach($product_sales as $sale){
          $pid=wc_get_order_item_meta( $sale['item_id'],'_product_id',true);
          $ctitle=get_the_title($course_product_map[$pid]);
          $sales_pie[$course_product_map[$pid]] += $sale['value'];
          $val=$sale['value'];
          $order_ids[]=$sale['order_id'];
          $total_commission += $val;
          $daily_val[$sale['date']]+=$val;
        }
      }

      /*
      $oquery = "SELECT order_meta.meta_value as value,order_meta.order_item_id as item_id,MONTH(post_meta.meta_value) as date,order_items.order_id as order_id
        FROM $item_meta_table AS order_meta
        LEFT JOIN $order_items_table as order_items ON order_items.order_item_id = order_meta.order_item_id
        LEFT JOIN {$wpdb->postmeta} as post_meta ON post_meta.post_id = order_items.order_id
        WHERE   order_meta.meta_key = '_line_total'
        AND  post_meta.meta_key = '_completed_date'
        AND   order_meta.order_item_id IN (
          SELECT order_item_id
          FROM $item_meta_table as t
          WHERE t.meta_key = '_product_id'
          AND t.meta_value IN  ($product_id_string)
          )";
      
      $order_id_string='';
      if(is_array($order_ids)){
        $order_id_string=implode(',',$order_ids);
        $oquery .="AND post_meta.post_id NOT IN ($order_id_string)";
      }
      // FOR UNCALCULATED COMMISSIONS
      $product_sales=$wpdb->get_results($oquery,ARRAY_A);

      $i=count($product_sales);
      if(is_array($product_sales) && $i){

        foreach($product_sales as $sale){
         // echo $sale['date'].' => '. $sale['value'].' - '.$sale['order_id'].' | ';
          $pid=wc_get_order_item_meta( $sale['item_id'],'_product_id',true);

          if(isset($course_commission[$course_product_map[$pid]]) && $course_commission[$course_product_map[$pid]])
            $commission_percentage=$course_commission[$course_product_map[$pid]];
          else 
            $commission_percentage = $instructor_commission;
          
          $ctitle=get_the_title($course_product_map[$pid]);//.' - '.$course_product_map[$pid];

          $val=round($sale['value']*($commission_percentage)/100,2);

          $sales_pie[$course_product_map[$pid]] += $val;
          $total_commission += $val;
          $daily_val[$sale['date']]+=$val;
        }
      }*/ 


        if(is_array($daily_val)){

          if(count($daily_val)){
            ksort($daily_val);
            foreach($daily_val as $key => $value){
            $commission_array[$key]=array(
                'date' => date('M', mktime(0, 0, 0, $key, 10)),
                'sales'=>$value);
            }
          }
           
            update_user_meta($user_id,'commission_data',$commission_array);
            update_user_meta($user_id,'sales_pie',$sales_pie);
            update_user_meta($user_id,'total_commission',$total_commission);
        }

        // Commission Paid out calculation
        $flag = 0;
        $commission_recieved = array();
        $commissions_paid = $wpdb->get_results($wpdb->prepare("
          SELECT meta_value,post_id FROM {$wpdb->postmeta} 
          WHERE meta_key = %s
         ",'vibe_instructor_commissions'));

        if(isset($commissions_paid) && is_Array($commissions_paid) && count($commissions_paid)){
          foreach($commissions_paid as $commission){
              $commission->meta_value = unserialize($commission->meta_value);
              if(isset($commission->meta_value[$user_id]) && isset($commission->meta_value[$user_id]['commission'])){
                $flag=1;
                $date = $wpdb->get_var($wpdb->prepare("SELECT MONTH(post_date) FROM {$wpdb->posts} WHERE ID = %d",$commission->post_id));
                $k = date('n', mktime(0, 0, 0, $date, 10));
                $commission_recieved[$k]=array(
                    'date' => date('M', mktime(0, 0, 0, $date, 10)),
                    'commission'=>$commission->meta_value[$user_id]['commission']);
                }
             }
          }

        if($flag || !count($commission_recieved)){
          update_user_meta($user_id,'commission_recieved',$commission_recieved);
        }  
        echo 1;
        die();
      }// End count courses

      _e('No courses found for Instructor','wplms-dashboard');
      die();
    }//End function
} 

?>