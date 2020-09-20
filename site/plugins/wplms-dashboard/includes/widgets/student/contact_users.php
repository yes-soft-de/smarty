<?php

add_action( 'widgets_init', 'wplms_dash_contact_users_widget' );

function wplms_dash_contact_users_widget() {
    register_widget('wplms_dash_contact_users');
}

class wplms_dash_contact_users extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'wplms_dash_contact_users', 'description' => __('Contact form Widget', 'wplms-dashboard') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_dash_contact_users' );
    parent::__construct( 'wplms_dash_contact_users', __(' DASHBOARD : Contact Form', 'wplms-dashboard'), $widget_ops, $control_ops );
    
    add_action('wp_ajax_get_friends',array($this,'get_friends'));
    add_action('wp_ajax_get_instructors',array($this,'get_instructors'));
    add_action('wp_ajax_get_admins',array($this,'get_admins'));
    add_action('wp_ajax_get_course_students',array($this,'get_course_students'));
    add_action('wp_ajax_dash_contact_message',array($this,'dash_contact_message'));
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    
    $users =  $instance['users'];
    $width =  $instance['width'];

    $user_id = get_current_user_id();
      
              
    wp_enqueue_style( 'wplms-magic-suggest-css', plugins_url( '../../../css/magicsuggest-min.css' , __FILE__ ));
    wp_enqueue_script( 'wplms-magic-suggest-js', plugins_url( '../../../js/magicsuggest-min.js' , __FILE__ ));

    echo '<div class="'.$width.'">
            <div class="dash-widget">'.$before_widget;

    // Display the widget title 
    if ( $title )
      	echo $before_title . $title . $after_title;
    		global $wpdb,$bp;

        echo '<div class="dash-content-form">';
        
        if(isset($users) && $users){
          echo '<select class="usergroup-dropdown chosen" data-placeholder="'.__('Select User group','wplms-dashboard').'">';
          echo '<option value="">'.__('Select a user group','wplms-dashboard').'</option>';
          if(bp_is_active('friends')){
            echo '<option value="get_friends">'.__('Friends','wplms-dashboard').'</option>';
          }
          if(class_exists('WPLMS_tips')){
            $tips = WPLMS_tips::init();
            if(empty($tips->settings['disable_instructor_display']))
              echo '<option value="get_instructors">'.__('Instructor','wplms-dashboard').'</option>';
          }
          echo '<option value="get_admins">'.__('Administrator','wplms-dashboard').'</option>';
          if(current_user_can('edit_posts'))
            echo '<option value="get_course_students">'.__('Course Students','wplms-dashboard').'</option>';

          echo '</select>';
        }
        echo '<input type="text" name="to" class="input-text to usergroup-filter" placeholder="'.__('Type name to auto-complete','wplms-dashboard').'" />';
        echo '<input type="text" name="subject" class="input-text subject" placeholder="'.__('Enter Subject','wplms-dashboard').'" />';
        echo '<textarea name="message" class="form_message" placeholder="'.__('Enter Message','wplms-dashboard').'"></textarea>';
        echo '<a id="dash_contact_form_submit" class="button">'.__('Send Message','wplms-dashboard').'</a>
        </div>';
        echo $after_widget.'
        </div>
        </div>';
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
	    $instance = $old_instance;
	    $instance['title'] = strip_tags($new_instance['title']);
	    $instance['users'] = $new_instance['users'];
	    $instance['width'] = $new_instance['width'];
	    return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                        'title'  => __('Contact Instructors','wplms-dashboard'),
                        'users' => 1,
                        'width' => 'col-md-6 col-sm-12'
                    );
  		  $instance = wp_parse_args( (array) $instance, $defaults );
        $title  = esc_attr($instance['title']);
        $users = esc_attr($instance['users']);
        $width = esc_attr($instance['width']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('users'); ?>"><?php _e('Show User select dropdown','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'users' ); ?>" name="<?php echo $this->get_field_name( 'users' ); ?>" type="checkbox" value="1"  <?php checked($users,1,true) ?>/>
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

    function get_friends(){
        $user_id = get_current_user_id();
        if(function_exists('friends_get_friend_user_ids')){
        $friends = friends_get_friend_user_ids( $user_id );
        foreach($friends as $key=>$friend){
          $friends[$key] = array(
            'id' => $friend,
            'pic' => bp_core_fetch_avatar ( array( 'item_id' => $friend, 'type' => 'thumb' ) ),
            'name' => bp_core_get_user_displayname($friend),
            );
        }
        echo json_encode($friends);
        }
        die();
    }

    function get_instructors(){
      $user_query = new WP_User_Query( array( 'role' => 'Instructor' ) );
      $instructors =array();
      if ( isset($user_query) && !empty( $user_query->results ) ) {
          foreach ( $user_query->results as $user ) {
              $instructors[]=array(
                'id' => $user->ID,
                'pic' => bp_core_fetch_avatar( array( 'item_id' => $user->ID,'type'=>'thumb')),
                'name' => bp_core_get_user_displayname($user->ID),
                );
          }
          echo json_encode($instructors);
      }
      die();
    }
    function get_admins(){
      $user_query = new WP_User_Query( array( 'role' => 'administrator' ) );
      $admins =array();
      if ( isset($user_query) && !empty( $user_query->results ) ) {
          foreach ( $user_query->results as $user ) {
              $admins[]=array(
                'id' => $user->ID,
                'pic' => bp_core_fetch_avatar( array( 'item_id' => $user->ID,'type'=>'thumb')),
                'name' => bp_core_get_user_displayname($user->ID),
                );
          }
          echo json_encode($admins);
      }
      die();
    }
    function get_course_students(){
      global $wpdb;
      $user_id=get_current_user_id();
      $query = apply_filters('wplms_dashboard_courses_instructors',$wpdb->prepare("
              SELECT posts.ID as course_id
                FROM {$wpdb->posts} AS posts
                WHERE   posts.post_type   = 'course'
                AND   posts.post_author   = %d
            ",$user_id));

        $instructor_courses=$wpdb->get_results($query,ARRAY_A);
        $course_ids=array();
        if(isset($instructor_courses) && count($instructor_courses)){
          foreach($instructor_courses as $key => $value){
              $course_ids[]=$value['course_id'];
            }
        }
      $course_ids_string = implode(',',$course_ids);

      $course_students = $wpdb->get_results("
        SELECT user_id
          FROM {$wpdb->usermeta} as rel
          WHERE  rel.meta_key  IN ($course_ids_string)
          AND   rel.meta_value >= 0
      ",ARRAY_A);

      $unique=array();
      if ( isset($course_students) && is_array( $course_students) ) {
          foreach ( $course_students as $user ) {
            if(!in_array($user['user_id'],$unique)){
              $mycourse_students[]=array(
                'id' => $user['user_id'],
                'pic' => bp_core_fetch_avatar( array( 'item_id' => $user['user_id'],'type'=>'thumb')),
                'name' => bp_core_get_user_displayname($user['user_id']),
                );
              $unique[]=$user['user_id'];
            }
          }
          echo json_encode($mycourse_students);
      }
      die();
    }
    
    function dash_contact_message(){
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security')){
             _e('Security error','wplms-dashboard');
             die();
      }

      $members = json_decode(stripslashes($_POST['to']));
      $subject=$_POST['subject'];
      $message = $_POST['message'];

      if ( !$members || !$subject || !$message){
           echo _e('Please enter to/subject/message','wplms-dashboard');
             die();
      }
      $sender_id = get_current_user_id();
      $sent=0;
      if(bp_is_active('messages'))
        foreach($members as $member){
        if( messages_new_message( array('sender_id' => $sender_id, 'subject' => $subject, 'content' => $message,   'recipients' => $member ) ) ){
        $sent++;
       }}
       echo sprintf(__('Message sent to %s members','wplms-dashboard'),$sent);
       die();
    }
} 

?>