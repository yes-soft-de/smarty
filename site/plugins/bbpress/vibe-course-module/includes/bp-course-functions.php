<?php

/**
 * The -functions.php file is a good place to store miscellaneous functions needed by your plugin.
 *
 * @package BuddyPress_Course_Component
 * @since 1.6
 */

/**
 * bp_course_load_template_filter()
 *
 * You can define a custom load template filter for your component. This will allow
 * you to store and load template files from your plugin directory.
 *
 * This will also allow users to override these templates in their active theme and
 * replace the ones that are stored in the plugin directory.
 *
 * If you're not interested in using template files, then you don't need this function.
 *
 * This will become clearer in the function bp_course_screen_one() when you want to load
 * a template file.
 */

 if ( ! defined( 'ABSPATH' ) ) exit;
 
function bp_course_load_template_filter( $found_template, $templates ) {
	global $bp;

	/**
	 * Only filter the template location when we're on the course component pages.
	 */
	if ( $bp->current_component != $bp->course->slug )
		return $found_template;

	foreach ( (array) $templates as $template ) {
		if ( file_exists( get_stylesheet_directory(). '/' . $template ) )
			$filtered_templates[] = get_stylesheet_directory() . '/' . $template;
    elseif ( file_exists( get_template_directory() . '/' . $template ) )
            $filtered_templates[] = get_template_directory() . '/' . $template;
		else
			$filtered_templates[] = dirname( __FILE__ ) . '/templates/' . $template;
	}

	$found_template = $filtered_templates[0];

	return apply_filters( 'bp_course_load_template_filter', $found_template );
}
add_filter( 'bp_located_template', 'bp_course_load_template_filter', 10, 2 );

function all_course_page_title(){
    echo '<h1>'.__('Course Directory','vibe').'</h1>
          <h5>'.__('All Courses by all instructors','vibe').'</h5>';
}

function bp_user_can_create_course() { 
  // Bail early if super admin 
  if ( is_super_admin() ) 
          return true; 

  if ( current_user_can('edit_posts') ) 
          return true;     

  // Get group creation option, default to 0 (allowed) 
  $restricted = (int) get_site_option( 'bp_restrict_course_creation', 0 ); 

  // Allow by default 
  $can_create = true; 

  // Are regular users restricted? 
  if ( $restricted ) 
          $can_create = false; 
	
	return apply_filters( 'bp_user_can_create_course', $can_create ); 
} 
/**
 * bp_course_nav_menu()
 * Navigation menu for BuddyPress course
 */

function bp_course_nav_menu(){

    $nav = bp_course_get_nav_permalinks();
    $defaults = array(
      '' => array(
                        'id' => 'home',
                        'label'=>__('Home','vibe'),
                        'action' => '',
                        'link'=>bp_get_course_permalink(),
                    ),
      'curriculum' => array(
                        'id'     => 'curriculum',
                        'label'  =>__('Curriculum','vibe'),
                        'can_view' => 1,
                        'action' => (empty($nav['curriculum_slug'])?__('curriculum','vibe'):$nav['curriculum_slug']),
                        'link'   => bp_get_course_permalink(),
                    ),
      'members' => array(
                        'id'    => 'members',
                        'label' =>__('Members','vibe'),
                        'can_view' => 1,
                        'action'=> (empty($nav['members_slug'])?__('members','vibe'):$nav['members_slug']),
                        'link'  =>bp_get_course_permalink(),
                    ),
      );

    if(bp_is_active('activity')){
      $defaults['activity']= array(
                'id'    => 'activity',
                'label' =>__('Activity','vibe'),
                'can_view' => 1,
                'action'=> (empty($nav['activity_slug'])?__('activity','vibe'):$nav['activity_slug']),
                'link'  =>bp_get_course_permalink(),
            );
    }
    global $post;
    if($post->post_type == 'course'){
        if(function_exists('bp_is_active') && bp_is_active('groups')){
          $vgroup=get_post_meta(get_the_ID(),'vibe_group',true);
          if(!empty($vgroup)){
            $group=groups_get_group(array('group_id'=>$vgroup));

            $defaults['group'] = array(
                          'id' => 'group',
                          'label'=>__('Group','vibe'),
                          'action' => 'group',
                          'can_view' => 1,
                          'link'=> bp_get_group_permalink($group),
                          'external'=>true,
                      );
          }
      }
      if ( in_array( 'bbpress/bbpress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'bbpress/bbpress.php'))) {
        $forum=get_post_meta(get_the_ID(),'vibe_forum',true);
        if(!empty($forum) && bp_course_get_post_type($forum) == 'forum'){
          $defaults['forum'] = array(
                        'id' => 'forum',
                        'label'=>__('Forum','vibe'),
                        'action' => 'forum',
                        'can_view' => 1,
                        'link'=> get_permalink($forum),
                        'external'=>true,
                    );
        }
      }
    }
    
    $nav_menu = apply_filters('wplms_course_nav_menu',$defaults);
    
    global $bp;
    $action = bp_current_action(); 
    if(empty($action)){
      (!empty($_GET['action'])?$action=$_GET['action']:$action='');
    }

    /*== ACTION DEFAULT ==*/
    $empty_action_first = 0;

    foreach($nav_menu as $key => $menu_item){
      if($key == $action){$empty_action_first = 1;}
    }
    if(empty($empty_action_first)){$empty_action_first = 1;}else{$empty_action_first = 0;}
    /*=== END HOME SELECTION == */

    if(is_array($nav_menu)){
        
        foreach($nav_menu as $key => $menu_item){
          $menu_item['action'] = str_replace('/','',$menu_item['action']);
          if($key == $action || $empty_action_first){
            $class = 'class="current"';
            $empty_action_first = 0;
          }else{
            $class='';
          }
          
          global $wp_query;

          if(!empty($nav[$menu_item['id'].'_slug'])){
            echo '<li id="'.$menu_item['id'].'" '.$class.'><a href="'.$menu_item['link'].''.((isset($menu_item['action']) && !isset($menu_item['external']))?$menu_item['action']:'').'">'.$menu_item['label'].'</a></li>';
          }else{

            echo '<li id="'.$menu_item['id'].'" '.$class.'><a href="'.$menu_item['link'].''.((!empty($menu_item['action']) && !isset($menu_item['external']))?(strpos($menu_item['link'],'?')?'&':'?').'action='.$menu_item['action']:'').'">'.$menu_item['label'].'</a></li>';
          }
        }
    }

    if(is_super_admin() || is_instructor()){ 
      $admin_slug = (empty($nav['admin_slug'])?_x('admin','course admin slug','vibe'):$nav['admin_slug']);
      $admin_slug = apply_filters('wplms_course_admin_slug',str_replace('/','',$admin_slug));
      ?>
      <li id="admin" class="<?php echo ((!empty($action) && ( $action == 'admin' || $action == 'submission' || $action == 'stats'))?'selected current':''); ?>"><a href="<?php bp_course_permalink(); echo $admin_slug; ?>"><?php  _e( 'Admin', 'vibe' ); ?></a></li>
      <?php
    }
}

/**
 * bp_course_remove_data()
 *
 * It's always wise to clean up after a user is deleted. This stops the database from filling up with
 * redundant information.
 */
function bp_course_remove_data( $user_id ) {
	/* You'll want to run a function here that will delete all information from any component tables
	   for this $user_id */

	/* Remember to remove usermeta for this component for the user being deleted */
	delete_user_meta( $user_id, 'bp_course_some_setting' );

	do_action( 'bp_course_remove_data', $user_id );
}
add_action( 'wpmu_delete_user', 'bp_course_remove_data', 1 );
add_action( 'delete_user', 'bp_course_remove_data', 1 );

function bp_directory_course_search_form() {

	$default_search_value = bp_get_search_default_text( BP_COURSE_SLUG );
	$search_value         = !empty( $_REQUEST['s'] ) ? stripslashes( $_REQUEST['s'] ) : $default_search_value;

	$search_form_html = '<form action="" method="get" id="search-course-form">
		<label><input type="text" name="s" id="course_search" placeholder="'. esc_attr( $search_value ) .'" /></label>
		<input type="submit" id="course_search_submit" name="course_search_submit" value="'. __( 'Search', 'vibe' ) .'" />
	</form>';

	echo apply_filters( 'bp_directory_course_search_form', $search_form_html );

}

if(!function_exists('the_course_button')){
function the_course_button($id=NULL){
  global $post;
  if(isset($id) && $id)
    $course_id=$id;
   else 
    $course_id=get_the_ID();

  // Free Course
   $free_course= get_post_meta($course_id,'vibe_course_free',true);

  if(!is_user_logged_in() && vibe_validate($free_course)){
    echo apply_filters('wplms_course_non_loggedin_user','<a href="'.get_permalink($course_id).'?error=login" class="course_button button full '.((function_exists('vibe_get_option') && vibe_get_option('enable_ajax_registration_login'))?'auto_trigger':'').'">'.apply_filters('wplms_take_this_course_button_label',__('TAKE THIS COURSE','vibe'),$course_id).'</a>',$course_id); 
    return;
  }

    $take_course_page_id=vibe_get_option('take_course_page');

    if(function_exists('icl_object_id'))
      $take_course_page_id = icl_object_id($take_course_page_id, 'page', true);

   $take_course_page=get_permalink($take_course_page_id);
   $user_id = get_current_user_id();

   do_action('wplms_the_course_button',$course_id,$user_id);
   $coursetaken = bp_course_get_user_expiry_time($user_id,$course_id);
   $auto_subscribe = 0; 

   if(vibe_validate($free_course) && is_user_logged_in() && (!isset($coursetaken) || !is_numeric($coursetaken))){ 
      $auto_subscribe = 1;
   }

   $auto_subscribe = apply_filters('wplms_auto_subscribe',$auto_subscribe,$course_id);
   if($auto_subscribe){  
      $t = bp_course_add_user_to_course($user_id,$course_id);

      if($t){
          $new_duration = apply_filters('wplms_free_course_check',$t);
          $coursetaken = $new_duration;
      }      
   }

   if(!empty($coursetaken) && is_user_logged_in()){   // COURSE IS TAKEN & USER IS LOGGED IN

         if($coursetaken > time()){  // COURSE ACTIVE
            $course_user= bp_course_get_user_course_status($user_id,$course_id); // Validates that a user has taken this course

            if((isset($course_user) && is_numeric($course_user)) || (isset($free_course) && $free_course && $free_course !='H' && is_user_logged_in())){ // COURSE PURCHASED SECONDARY VALIDATION
             echo '<form action="'.apply_filters('wplms_take_course_page',$take_course_page,$course_id).'" method="post">';

                    switch($course_user){
                    case 1:
                      echo  apply_filters('wplms_start_course_button','<input type="submit" class="'.((isset($id) && $id )?'':'course_button full ').'button" value="'.__('START COURSE','vibe').'">',$course_id); 
                      wp_nonce_field('start_course'.$user_id,'start_course');
                    break;
                    case 2:  
                      echo  apply_filters('wplms_continue_course_button','<input type="submit" class="'.((isset($id) && $id )?'':'course_button full ').'button" value="'.__('CONTINUE COURSE','vibe').'">',$course_id);
                      wp_nonce_field('continue_course'.$user_id,'continue_course');
                    break;
                    case 3:
                      echo  apply_filters('wplms_evaluation_course_button','<a href="#" class="full button">'.__('COURSE UNDER EVALUATION','vibe').'</a>',$course_id);
                    break;
                    case 4:

                      $finished_course_access = vibe_get_option('finished_course_access');
                      if(isset($finished_course_access) && $finished_course_access){
                        echo apply_filters('finish_course_button_access_html','<input type="submit" class="'.((isset($id) && $id )?'':'course_button full ').'button" value="'.__('FINISHED COURSE','vibe').'">',$user_id,$course_id,$course_user);
                        wp_nonce_field('continue_course'.$user_id,'continue_course');
                      }else{
                        echo apply_filters('finish_course_button_html','<a href="'.apply_filters('wplms_finished_course_link','#',$course_id).'" class="full button">'.__('COURSE FINISHED','vibe').'</a>',$user_id,$course_id,$course_user);
                      }
                    break;
                    default:
                      $course_button_html = '<a class="course_button button">'.__('COURSE ENABLED','vibe').'<span>'.__('CONTACT ADMIN TO ENABLE','vibe').'</span></a>';
                      echo apply_filters('wplms_default_course_button',$course_button_html,$user_id,$course_id,$course_user);
                    break;
                  }  

             echo  '<input type="hidden" name="course_id" value="'.$course_id.'" />';
             
             echo  '</form>';
             do_action('wplms_after_course_button_form',$user_id,$course_id,$course_user); 
            }else{ 
                  $pid=get_post_meta($course_id,'vibe_product',true); // SOME ISSUE IN PROCESS BUT STILL DISPLAYING THIS FOR NO REASON.
                  echo '<a href="'.get_permalink($pid).'" class="'.((isset($id) && $id )?'':'course_button full ').'button">'.__('COURSE ENABLED','vibe').'<span>'.__('CONTACT ADMIN TO ENABLE','vibe').'</span></a>';   
            }
      }else{
            $pid=get_post_meta($course_id,'vibe_product',true);
            $pid=apply_filters('wplms_course_product_id',$pid,$course_id,-1); // $id checks for Single Course page or Course page in the my courses section
            if(is_numeric($pid)){
              $pid=get_permalink($pid);
              $check=vibe_get_option('direct_checkout');
              $check =intval($check);
              if(isset($check) &&  $check){
                $pid .= '?redirect';
              }
            }
            echo apply_filters('wplms_expired_course_button','<a href="'.$pid.'" class="'.((isset($id) && $id )?'':'course_button full ').'button">'.__('Course Expired','vibe').'&nbsp;<span>'.__('Click to renew','vibe').'</span></a>',$course_id);   
      }
    
   }else{
      $pid=get_post_meta($course_id,'vibe_product',true);
      $pid=apply_filters('wplms_course_product_id',$pid,$course_id,0);

      if(is_numeric($pid) && bp_course_get_post_type($pid) == 'product'){
        $pid=get_permalink($pid);
        $check=vibe_get_option('direct_checkout');
        $check =intval($check);
        if(isset($check) &&  $check){
          $pid .= '?redirect';
        }
      }
      
      $extra ='';
      if(isset($pid) && $pid){
        //Check Partial free course setting.
        $partial_free_course = get_post_meta($course_id,'vibe_partial_free_course',true);
        if( vibe_validate($partial_free_course) && is_user_logged_in() ){
          echo apply_filters('wplms_take_course_button_html','<a href="'.get_permalink($course_id).'?subscribe" class="'.((isset($id) && $id )?'':'course_button full ').'button">'.apply_filters('wplms_take_this_course_button_label',__('SUBSCRIBE FOR FREE','vibe'),$course_id).apply_filters('wplms_course_button_extra',$extra,$course_id).'</a>',$course_id);
        }else{
          echo apply_filters('wplms_take_course_button_html','<a href="'.$pid.'" class="'.((isset($id) && $id )?'':'course_button full ').'button">'.apply_filters('wplms_take_this_course_button_label',__('TAKE THIS COURSE','vibe'),$course_id).apply_filters('wplms_course_button_extra',$extra,$course_id).'</a>',$course_id);
        }
      }else{
        echo apply_filters('wplms_private_course_button_html','<a href="'.apply_filters('wplms_private_course_button','#',$course_id).'" class="'.((isset($id) && $id )?'':'course_button full ').'button">'. apply_filters('wplms_private_course_button_label',__('PRIVATE COURSE','vibe'),$course_id).'</a>',$course_id); 
      }
   }
}
}

function the_course_details($args=NULL){
  echo get_the_course_details($args);
}

function get_the_course_details($args=NULL){
  $defaults=array(
    'course_id' =>get_the_ID(),
    );
  $r = wp_parse_args( $args, $defaults );
  extract( $r, EXTR_SKIP );

  $precourse=get_post_meta($course_id,'vibe_pre_course',true);
  $maximum = bp_course_get_max_students($course_id); 
  $badge=get_post_meta($course_id,'vibe_course_badge',true);
  $certificate=get_post_meta($course_id,'vibe_course_certificate',true);

  $level = vibe_get_option('level');
  if(isset($level) && $level)
    $levels=get_the_term_list( $course_id, 'level', '', ', ', '' );

  $location = vibe_get_option('location');
  if(isset($location) && $location)
    $location=get_the_term_list( $course_id, 'location', '', ', ', '' );

  $pre_course_html = '';
  if(!empty($precourse)){
    if(is_numeric($precourse)){
      $pre_course_html = '<a href="'.get_permalink($precourse).'">'.get_the_title($precourse).'</a>';
    }else if(is_array($precourse)){
       foreach($precourse as $k => $pre_course_id){
          $pre_course_html .= (empty($k)?'':' , ').'<a href="'.get_permalink($pre_course_id).'">'.get_the_title($pre_course_id).'</a>';
       }
    }
  }

  //Check Partial free course setting.
  $partial = 0;
  $partial_free_course = get_post_meta($course_id,'vibe_partial_free_course',true);
  if( vibe_validate($partial_free_course) ){
    if( is_user_logged_in() ){
      $partial = 1;
    }else{
      $partial = 0;
    }
  }

  // Display Course Details
  $course_details = array(
    'price' => '<li class="course_price">'.bp_course_get_course_credits(array('id'=>$course_id,'partial'=>$partial)).'</li>',
    'precourse'=>(empty($precourse)?'':'<li class="course_precourse"><i class="icon-clipboard-1"></i> '.__('* REQUIRES','vibe').' '.$pre_course_html.' </li>'),
    'time' => '<li class="course_time"><i class="icon-clock"></i>'.get_the_course_time('course_id='.$course_id).'</li>',
    'location' => ((isset($location) && $location && strlen($location)>5)?'<li class="course_location"><i class="icon-map-pin-5"></i> '.$location.'</li>':''),
    'level' => ((isset($level) && $level && strlen($levels)>5)?'<li class="course_level"><i class="icon-bars"></i> '.$levels.'</li>':''),
    'seats' => ((isset($maximum) && is_numeric($maximum) && $maximum < 9999 )?'<li class="course_seats"><i class="icon-users"></i> '.$maximum.' '.__('SEATS','vibe').'</li>':''),
    'badge' => ((isset($badge) && $badge && $badge !=' ')?apply_filters('wplms_show_badge_popup_in_course_details','<li class="course_badge"><i class="icon-award-stroke"></i> '.__('Course Badge','vibe').'</li>',$course_id):''),

    'certificate'=> (vibe_validate($certificate)?apply_filters('wplms_show_certificate_popup_in_course_details','<li class="course_certificate"><i class="icon-certificate-file"></i>  '.__('Course Certificate','vibe').'</li>',$course_id):''),
    );

  $course_details = apply_filters('wplms_course_details_widget',$course_details,$course_id);

  global $post;
  $return ='<div class="course_details">
              <ul>'; 
  foreach($course_details as $course_detail){
    if(isset($course_detail) && strlen($course_detail) > 5)
      $return .=$course_detail;
  }
  $return .=  '</ul>
            </div>';
   return apply_filters('wplms_course_front_details',$return);
}

if(!function_exists('the_question')){
  function the_question($id=null,$quiz_id=null){
    if(!empty($id)){
      $post = get_post($id);
    }
    global $post;
    $hint = get_post_meta($post->ID,'vibe_question_hint',true);
    $type = get_post_meta(get_the_ID(),'vibe_question_type',true);
    echo '<div id="question" data-ques="'.get_the_ID().'">';
    echo '<div class="question '.$type.'">';
    the_content();
    if(isset($hint) && strlen($hint)>5){
      echo '<a class="show_hint tip" tip="'.__('SHOW HINT','vibe').'"><span></span></a>';
      echo '<div class="hint"><i><span class="left">'.__('HINT','vibe').' : </span>'.do_shortcode(apply_filters('the_content',$hint)).'</i></div>';
    }
    echo '</div>';

    switch($type){
      case 'truefalse': 
        the_options('truefalse');
      break;
      case 'survey':
      case 'single': 
        the_options('single');
      break;  
      case 'multiple': 
        the_options('multiple');
      break;
      case 'match': 
        the_options('match');
      break;
      case 'sort': 
        the_options('sort');
      break;
      case 'smalltext': 
        the_text();
      break;
      case 'largetext': 
        the_textarea();
      break;
      case 'fillblank': 
      case 'select': 
      break;
      default:
        do_action('wplms_generate_question_html');
      break;
    }
    
    do_action('wplms_after_question_options',$type,get_the_ID());
    the_marked_question_answer($quiz_id);
    

    echo '</div><div id="ajaxloader" class="disabled"></div>';
  }
}

if(!function_exists('the_options')){
  function the_options($type){
      global $post,$wpdb;
      $options = vibe_sanitize(get_post_meta(get_the_ID(),'vibe_question_options',false));
      
      if($type == 'truefalse')
        $options = array( 0 => __('FALSE','vibe'),1 =>__('TRUE','vibe'));

    if(isset($options) || $options){  
      $content=array();

      echo '<ul class="question_options '.$type.'">';
      if($type=='single'){
        foreach($options as $key=>$value){

          $k=$key+1;
          echo '<li>
                  <div class="radio">
                    <input type="radio" id="'.$post->post_name.$key.'" name="'.$post->post_name.'" value="'.$k.'" '.(in_array($k,$content)?'checked':'').'/>
                    <label for="'.$post->post_name.$key.'"><span></span> '.do_shortcode($value).'</label>
                  </div>  
                </li>';
        }
      }else if($type == 'sort'){
        foreach($options as $key=>$value){
          echo '<li id="'.($key+1).'" class="sort_option">
                      <label for="'.$post->post_name.$key.'"><span></span> '.do_shortcode($value).'</label>
                  </li>';
        }        
      }else if($type == 'match'){
        foreach($options as $key=>$value){
          echo '<li id="'.($key+1).'" class="match_option">
                      <label for="'.$post->post_name.$key.'"><span></span> '.do_shortcode($value).'</label>
                  </li>';
        }        
      }else if($type == 'truefalse'){
        foreach($options as $key=>$value){

          echo '<li>
                  <div class="radio">
                    <input type="radio" id="'.$post->post_name.$key.'" name="'.$post->post_name.'" value="'.$key.'" '.(in_array($key,$content)?'checked':'').'/>
                    <label for="'.$post->post_name.$key.'"><span></span> '.$value.'</label>
                  </div>  
                </li>';
        }       
      }else{
        foreach($options as $key=>$value){
          $k=$key+1;
          echo '<li>
                  <div class="checkbox">
                    <input type="checkbox" id="'.$post->post_name.$key.'" name="'.$post->post_name.$key.'" value="'.$k.'" '.(in_array($k,$content)?'checked':'').'/>
                    <label for="'.$post->post_name.$key.'">'.do_shortcode($value).'</label>
                  </div>  
                </li>';
        }
      }  
      echo '</ul>';
    }
  }
}

function the_marked_question_answer($quiz_id=null){
  global $post,$wpdb;
  $user_id = get_current_user_id();
  if(empty($quiz_id)){
    $answer = $wpdb->get_var($wpdb->prepare("SELECT comment_content FROM {$wpdb->comments} WHERE comment_post_ID = %d and user_id = %d LIMIT 0,1",$post->ID,$user_id));
  }else{
    $answer = bp_course_get_question_marked_answer($quiz_id,$post->ID,$user_id);
  }
  if(isset($answer) && $answer != ''){
    echo '<input type="hidden" id="question_marked_answer'.$post->ID.'" value="'.$answer->comment_content.'" />';
  }
}

if(!function_exists('the_text')){
  function the_text(){
      global $post;
      echo '<div class="single_text">';
      echo '<input type="text" class="form_field" placeholder="'.__('Type answer','vibe').'" />';
      echo '</div>';
  }
}

if(!function_exists('the_textarea')){
  function the_textarea(){
      echo '<div class="essay_text">';
      echo '<textarea class="form_field" placeholder="'.__('Type answer','vibe').'"></textarea>';
      echo '</div>';
  }
}

if(!function_exists('the_question_tags')){
  function the_question_tags($before,$saperator,$after){
    global $post;
    echo get_the_term_list($post->ID,'question-tag',$before,$saperator,$after);
  }
}

function bp_course_user_time_left($args){
  echo bp_get_course_user_time_left($args);
}

if(!function_exists('bp_get_course_user_time_left')){
  function bp_get_course_user_time_left($args=NULL){
    $defaults=array(
    'course' =>get_the_ID(),
    'user'=> get_current_user_id()
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );
    $course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400,$course);
    $expiry = bp_course_get_user_expiry_time($user,$course);

    $time_left = 0;
    if(!empty($expiry)){
      $time = time();
      $time_left = intval($expiry) - $time;
    }

    if($time_left > 0){
      if($time_left > 863913600){
        return __('Unlimited Time','vibe');
      }
      return round(($time_left/$course_duration_parameter),0).' '.calculate_duration_time($course_duration_parameter);
    }else{
      return __('EXPIRED','vibe');
    }
  }
}

if(!function_exists('the_quiz')){
  function the_quiz($args=NULL){

  $defaults=array(
  'quiz_id' =>get_the_ID(),
  'ques_id'=> ''
  );

  $r = wp_parse_args( $args, $defaults );
  extract( $r, EXTR_SKIP );

    $user_id = get_current_user_id();
    $questions = bp_course_get_quiz_questions($quiz_id,$user_id);

    if(isset($questions['ques']) && is_array($questions['ques']))
      $key=array_search($ques_id,$questions['ques']);

    if($ques_id){
      $the_query = new WP_Query(array(
        'post_type'=>'question',
        'p'=>$ques_id
        ));
      while ( $the_query->have_posts() ) : $the_query->the_post(); 
        the_question('',$quiz_id);
        do_action('wplms_quiz_question',$ques_id,$quiz_id);
        echo '<div class="quiz_bar">';
        if($key == 0){ // FIRST QUESTION
          if($key != (count($questions['ques'])-1)) // First But not the Last
            echo '<a href="#" class="ques_link right quiz_question nextq" data-quiz="'.$quiz_id.'" data-qid="'.$questions['ques'][($key+1)].'">'.__('Next Question','vibe').' &rsaquo;</a>';

        }elseif($key == (count($questions['ques'])-1)){ // LAST QUESTION

          echo '<a href="#" class="ques_link left quiz_question prevq" data-quiz="'.$quiz_id.'" data-qid="'.$questions['ques'][($key-1)].'">&lsaquo; '.__('Previous Question','vibe').'</a>';

        }else{
          echo '<a href="#" class="ques_link left quiz_question prevq" data-quiz="'.$quiz_id.'" data-qid="'.$questions['ques'][($key-1)].'">&lsaquo; '.__('Previous Question','vibe').'</a>';
          echo '<a href="#" class="ques_link right quiz_question nextq" data-quiz="'.$quiz_id.'" data-qid="'.$questions['ques'][($key+1)].'">'.__('Next Question','vibe').' &rsaquo;</a>';
        }

        echo '</div>';
      endwhile;
      wp_reset_postdata();
    }else{
        
        $quiz_taken=get_user_meta($user_id,$quiz_id,true);
        if(isset($quiz_taken) && $quiz_taken && ($quiz_taken < time())){

          $message=get_post_meta($quiz_id,'vibe_quiz_message',true);
          echo '<div class="main_content">';
          echo apply_filters('the_content',$message);
          do_action('wplms_after_quiz_message',$quiz_id,$user_id);
          echo '</div>';
        }else{
          echo '<div class="main_content">';
          the_content();
          echo '</div>';
        }
    }
  }
}

if(!function_exists('the_quiz_timer')){
  function the_quiz_timer($args=NULL){
    global $post;

    $defaults = array( 'start'=>'','quiz_id'=>$post->ID);
    $args = wp_parse_args( (array)$args, $defaults );
    extract($args);

      $user_id = get_current_user_id();
      $quiztaken=get_user_meta($user_id,$quiz_id,true);
      $minutes=intval(get_post_meta($quiz_id,'vibe_duration',true));
      
      if($minutes > 9998)
        return true;
      
      if(isset($quiztaken) && is_numeric($quiztaken) && $quiztaken){ 
          if($quiztaken>time()){
            $minutes=$quiztaken-time();
            $start=1;
          }else{
            $minutes=0;
          }  
      }else{
          if(!$minutes) {$minutes=1; echo __("Duration not Set","vibe");}else $start=0;
          $quiz_duration_parameter = apply_filters('vibe_quiz_duration_parameter',60,$quiz_id);
          $minutes= $minutes*$quiz_duration_parameter;
      } 

      echo '<div class="quiz_timer '.(($start)?'start':'').'" data-time="'.$minutes.'">
      <span class="timer" data-timer="'.$minutes.'"></span>
      <span class="countdown">'.minutes_to_hms($minutes).'</span>
      <span>'.__('Time Remaining','vibe').'</span>'.
      '<span '.(($minutes >= 10800)?'':'style="display:none;"').' class="timer_hours_labels"><strong>'.__('Hour','vibe').'</strong> '.__('Mins','vibe').'</span>'.
      '<span '.(($minutes >= 10800)?'style="display:none;"':'').' class="timer_mins_labels"><strong>'.__('Mins','vibe').'</strong> '.__('Secs','vibe').'</span>'.'
      </div>';
  }
}

function in_quiz_timer($args=NULL){
    $defaults = array('start'=>'','quiz_id'=>$post->ID);
    $args = wp_parse_args( (array)$args, $defaults );
    extract($args);

    $user_id = get_current_user_id();
    $quiztaken=get_user_meta($user_id,$quiz_id,true);
    $minutes=intval(get_post_meta($quiz_id,'vibe_duration',true));
    
    if($minutes > 9998)
      return true;

    if(isset($quiztaken) && is_numeric($quiztaken) && $quiztaken){
        if($quiztaken>time()){
          $minutes=$quiztaken-time();
          $start=1;
        }else{
          $minutes=0;
        }  
    }else{
        if(!$minutes) {$minutes=1; echo __("Duration not Set","vibe");}else $start=0;
        $quiz_duration_parameter = apply_filters('vibe_quiz_duration_parameter',60,$quiz_id);
        $minutes= $minutes*$quiz_duration_parameter;
    } 
    echo '<div class="quiz_meta"><div class="inquiz_timer '.(($start)?'start':'').'" data-time="'.$minutes.'">
    <span class="timer" data-timer="'.$minutes.'"></span>
    <span class="countdown">'.minutes_to_hms($minutes).'</span>
    </div><i>'.__('Progress','vibe').':<span>0</span></i>
    <div class="progress">
     <div class="bar animate stretchRight load" style="width:0%;"></div>
   </div></div>';
}

function in_quiz_timeline($args=NULL){ 
    $defaults = array('ques_id'=>'','quiz_id'=>$post->ID);
    $args = wp_parse_args( (array)$args, $defaults );
    extract($args);

    $user_id = get_current_user_id();
    $questions = bp_course_get_quiz_questions($quiz_id,$user_id);
    $quess=$questions['ques'];
    $marks=$questions['marks'];
    if(isset($quess) && is_array($quess)){
      echo '<div class="inquiz_timeline">
              <ul>';
      
        foreach($quess as $i => $ques){
          $class='';

          if(!isset($marks[$i]) || !is_numeric($marks[$i])) $marks[$i]=0;
          $user_marked_answer = bp_course_get_question_marked_answer_id($quiz_id,$ques,$user_id);
          if($user_marked_answer){
            $marks = bp_course_get_user_question_marks($quiz_id,$ques,$user_id);
            echo $ques.'='.$marks;
            if($marks){$class = 'correct ';}else{$class = 'incorrect ';}
            $class .="done";
          }

          if(isset($ques) && is_numeric($ques)){
            if(isset($id) && $ques == $id){
              $class="active";
            }
            echo '<li id="ques'.$ques.'" class="'.$class.'"><span></span> <a href="#" data-quiz="'.$quiz_id.'" data-qid="'.$ques.'" class="'.(is_user_logged_in()?'quiz_question':'').'"><span>'.$marks[$i].'</span></a></li>';
          }
        }   
      echo '</ul></div>';  
    }else{
       echo '<span class="message">'.__('Please set questions in quiz or reset quiz !','vibe').'</span>';
    }   
}    


if(!function_exists('the_quiz_timeline')){
  function the_quiz_timeline($args=NULL){
    global $post;

    $defaults = array('ques_id'=>'','quiz_id'=>$post->ID);
    $args = wp_parse_args( (array)$args, $defaults );
    extract($args);
    $user_id = get_current_user_id();
    $questions = bp_course_get_quiz_questions($quiz_id,$user_id);
    $quess=$questions['ques'];
    $marks=$questions['marks'];

    if(isset($quess) && is_array($quess)){
      echo '<div class="quiz_timeline">
             <div class="timeline_wrapper">
              <ul>';

        $check_answer = get_post_meta($quiz_id,'vibe_quiz_check_answer',true);
        foreach($quess as $i => $ques){
          $class='';

          if(!isset($marks[$i]) || !is_numeric($marks[$i])) $marks[$i]=0;
          $user_marked_answer = bp_course_get_question_marked_answer_id($quiz_id,$ques,$user_id);
          if($user_marked_answer){
            if(!empty($check_answer) && $check_answer != 'H'){
              $umarks = bp_course_get_user_question_marks($quiz_id,$ques,$user_id);
              if($umarks){$class = 'correct ';}else{$class = 'incorrect ';}
            }
            $class .="done";
          }

          if(isset($ques) && is_numeric($ques)){
            if(isset($id) && $ques == $id){
              $class="active";
            }
            echo '<li id="ques'.$ques.'" class="'.$class.'"><span></span> <a href="#" data-quiz="'.$quiz_id.'" data-qid="'.$ques.'" class="'.(is_user_logged_in()?'quiz_question':'').'">'.__('QUESTION','vibe').' '.($i+1).'<span>'.$marks[$i].'</span></a></li>';
          }
        }   
      echo '</ul></div></div>';  
    }   
  }
}

if(!function_exists('minutes_to_hms')){
  function minutes_to_hms($sec){
    if($sec >= 10800){
       $hours = floor($sec/3600);
        $mins = floor(($sec%3600)/60);
        if($mins < 10) $mins = '0'.$mins;
        return $hours.':'.$mins;
    }else if($sec > 60){
        $minutes = floor($sec/60);
        $secs = $sec%60;
        if($secs < 10) $secs = '0'.$secs;
        return $minutes.':'.$secs;
    }else{
      $secs = $sec;
      if($secs == 0){
        return  _x( 'ENDED','displayed to user when quiz times out.','vibe' );
      }else{
        return '00:'.$secs;  
      }
    }
  }
}

if(!function_exists('tofriendlytime')){
  function tofriendlytime($seconds,$force = null) {
  $measures = array(
    array('label'=>__('year','vibe'),'multi'=>__('years','vibe'), 
          'value'=>12*30*24*60*60),
    array('label'=>__('month','vibe'),'multi'=>__('months','vibe'), 
          'value'=>30*24*60*60),
    array('label'=>__('week','vibe'),'multi'=>__('weeks','vibe'), 
          'value'=>7*24*60*60),
    array('label'=>__('day','vibe'),'multi'=>__('days','vibe'), 
          'value'=>24*60*60),
    array('label'=>__('hour','vibe'),'multi'=>__('hours','vibe'), 
          'value'=>60*60),
    array('label'=>__('minute','vibe'),'multi'=>__('minutes','vibe'), 
          'value'=>60),
    array('label'=>__('second','vibe'),'multi'=>__('seconds','vibe'), 
          'value'=>1),
    );

    if($seconds <= 0)
      return __('EXPIRED','vibe');
  
    foreach($measures as $key => $measure){
      if(!empty($force)){
          if($measure['value'] <= $force){
            $count = floor($seconds/$force);
            break;
          }
      }else{
          if($measure['value'] < $seconds && empty($force)){
            $count = floor($seconds/$measure['value']);
            break;
          }
      }
    }

    if(empty($force))
      $time_labels = $count.' '.(($count > 1)?$measure['multi']:$measure['label']);
    else
      $time_labels = (($count > 1)?$count:'').' '.(($count > 1)?$measure['multi']:$measure['label']);

    if($measure['value'] > 1){ // Ensure we're not on last element
      $small_measure = $measures[$key+1];  
      $small_count = floor(($seconds%$measure['value'])/$small_measure['value']);
      if($small_count)
        $time_labels .= ', '.$small_count.' '.(($small_count > 1)?$small_measure['multi']:$small_measure['label']);
    }
    
  return $time_labels;
  } 
}

if(!function_exists('the_course_timeline')){
    
    function the_course_timeline($course_id=NULL,$uid=NULL){
       $user_id = get_current_user_id(); 
       $class='';

       if(class_exists('WPLMS_tips')){
        $settings = WPLMS_tips::init();
         if(!empty($settings->settings['curriculum_accordion']))
            $class="accordion"; 
       }

       $return ='<div class="course_timeline '.$class.'">
                    <ul>';
        $course_curriculum= bp_course_get_curriculum($course_id); 

        if(isset($course_curriculum) && is_array($course_curriculum)){
            $first_unit = 1;
            $nextunit_access = apply_filters('bp_course_next_unit_access',true,$course_id);
            $active_flag=0; // For duplicate active check.

            foreach($course_curriculum as $key => $unit_id){
                
                if(is_numeric($unit_id)){
                    if(bp_course_get_post_type($unit_id) == 'unit'){
                        $unittaken = bp_course_check_unit_complete($unit_id,$user_id,$course_id);
                    }else{
                        $unittaken = bp_course_check_quiz_complete($unit_id,$user_id,$course_id);
                    }

                    $class='';$flag=0;

                    if(!empty($uid)){
                        if($uid == $unit_id || $uid == $first_unit){
                            if(empty($active_flag)){
                                $active_flag = 1;
                                $class .=' active';
                            }
                            $flag = 1;
                        }
                    }else{
                        if(!empty($first_unit)){
                            if(empty($active_flag)){
                                $active_flag = 1;
                                $class .=' active';
                            }
                        }
                    }

                    $first_unit=0;

                    if(isset($unittaken) && $unittaken ){
                        $class .=' done';
                        $flag = 1;
                    } 

                    if(empty($nextunit_access)){ 
                        if($flag){
                            $return .= '<li id="unit'.$unit_id.'" class="unit_line '.$class.'"><span></span> <a class="unit" data-unit="'.$unit_id.'">'.get_the_title($unit_id).'</a></li>';
                        }else{
                            $return .= '<li id="unit'.$unit_id.'" class="unit_line '.$class.'"><span></span> <a>'.get_the_title($unit_id).'</a></li>';        
                        }
                    }else{
                        $return .= '<li id="unit'.$unit_id.'" class="unit_line '.$class.'"><span></span> <a class="unit" data-unit="'.$unit_id.'">'.get_the_title($unit_id).'</a></li>';
                    }
                }else{
                    
                    $return .='<li class="section"><h4>'.$unit_id.'</h4></li>';

                }
            } // End For
        }else{
            $return .= '<li><h3>';
            $return .=__('Course Curriculum Not Set.','vibe');
            $return .= '</h3></li>';
        }      

        $return .='</ul></div>';             
        return $return;
    }
}

if(!function_exists('the_unit')){
  function the_unit($id=NULL){
    if(!isset($id))
      return;
    
    do_action('wplms_before_every_unit',$id);
    $post_type = bp_course_get_post_type($id);
    $the_query = new WP_Query( 'post_type='.$post_type.'&p='.$id );
    $user_id = get_current_user_id();

    while ( $the_query->have_posts() ):$the_query->the_post();
    
      $unit_class = 'unit_class';
      $unit_class=apply_filters('wplms_unit_classes',$unit_class,$id);
      echo '<div class="main_unit_content '.$unit_class.'">';
      
      if($post_type == 'quiz'){ 
        $expiry = get_user_meta($user_id,$id,true);
        if(is_numeric($expiry) && $expiry < time()){
          $message = get_post_meta($id,'vibe_quiz_message',true);
          echo apply_filters('the_content',$message);
          do_action('wplms_after_quiz_message',$id,$user_id);
        }else{
          the_content();  
        }
      }else{
         the_content();  
      }
      
      wp_link_pages(apply_filters('wplms_unit_pre_next',array(
        'before'=>'<div class="unit-page-links page-links"><div class="page-link">',
        'link_before' => '<span>',
        'link_after'=>'</span>',
        'after'=> '</div></div>')));

      echo '</div>';
    endwhile;
    wp_reset_postdata();

    $attachments = apply_filters('wplms_unit_attachments',1,$id);
    if($attachments){
      echo bp_course_get_unit_attachments($id);
    }


    if(bp_course_get_post_type($id) == 'unit'){
      do_action('wplms_after_every_unit',$id);
    }
    if($post_type == 'quiz'){
      do_action('wplms_front_end_quiz_controls',$id);
    }
    $forum=get_post_meta($id,'vibe_forum',true);
    if(!empty($forum) && bp_course_get_post_type($forum) == 'forum'){
      echo '<div class="unitforum"><a href="'.get_permalink($forum).'" target="_blank">'.__('Have Questions ? Ask in the Unit Forums','vibe').'</a></div>';
    }
  }
}

if(!function_exists('bp_course_get_unit_attachments')){

  function bp_course_get_unit_attachments($id=NULL){
      
        if(!is_numeric($id)){
          global $post;
          $id=$post->ID;
          if($post->post_type != 'unit')
            return;

        }else{
            if(bp_course_get_post_type($id) != 'unit')
                return;
        }

        $return='';
        $attachments = get_post_meta($id,'vibe_unit_attachments',true);
        
        if(!empty($attachments)){

            $return ='<div class="unitattachments"><h4>'.__('Attachments','vibe').'<span><i class="icon-download-3"></i>'.count($attachments).'</span></h4><ul id="attachments">';
          
            foreach($attachments as $attachment_id){
                $type=get_post_mime_type($attachment_id);
                $type = bp_course_get_attachment_type($type);
                $return .='<li><i class="'.$type.'"></i>'.wp_get_attachment_link($attachment_id).'</li>';
            }
         
            $return .= '</ul></div>';
            return $return;  
        }

      return;

      //Fallback removed more than 2 yeara now

      //IF Attachments are not set
      $attachments = get_children( 'post_type=attachment&output=ARRAY_N&orderby=menu_order&order=ASC&post_parent='.$id);
       if($attachments && count($attachments)){
            $att= '';

            $count=0;
          foreach( $attachments as $attachmentsID => $attachmentsPost ){
          $type=get_post_mime_type($attachmentsID);

          if($type != 'image/jpeg' && $type != 'image/png' && $type != 'image/gif'){
                $type = bp_course_get_attachment_type($type);
                $count++;
              $att .='<li><i class="'.$type.'"></i>'.wp_get_attachment_link($attachmentsID).'</li>';
            }
          }

        if($count){
          $return ='<div class="unitattachments"><h4>'.__('Attachments','vibe').'<span><i class="icon-download-3"></i>'.$count.'</span></h4><ul id="attachments">';
          $return .= $att;
          $return .= '</ul></div>';
        }
      }
      return $return;
    }
}

if(!function_exists('bp_course_get_attachment_type')){
  function bp_course_get_attachment_type($type){

        if($type == 'application/zip')
            $type='fa fa-file-archive-o';
        else if($type == 'video/mpeg' || $type== 'video/mp4' || $type== 'video/quicktime')
            $type='fa fa-file-video-o';
        else if($type == 'text/csv' || $type== 'text/plain' || $type== 'text/xml')
            $type='fa fa-excel-o';
        else if($type == 'audio/mp3' || $type== 'audio/ogg' || $type== 'audio/wmv')
            $type='fa fa-audio-o';
        else if($type == 'application/pdf')
            $type='fa fa-file-pdf-o';
        else if($type == 'image/jpeg' || $type == 'image/jpg' || $type == 'image/png' && $type == 'image/gif')
            $type='fa fa-picture-o';
        else    
            $type='fa fa-file-text-o';

        return $type;
    }
}

if(!function_exists('the_unit_tags')){
  function the_unit_tags($id){
    $list = get_the_term_list($id,'module-tag','<ul class="tags"><li>','</li><li>','</li></ul>');
    if(strlen($list)>32){
      echo $list;
    }
  }
}

if(!function_exists('the_unit_instructor')){
  function the_unit_instructor($id){
    global $post,$bp;

    $enable_instructor = apply_filters('wplms_display_instructor',true,$post->ID);
    if( !$enable_instructor ){
      return;
    }

    if(isset($id)){
      $author_id = get_post_field( 'post_author', $id );
    }else{
      $author_id = get_the_author_meta('ID');
    }
   
    echo '<div class="instructor">
            <a href="'.bp_core_get_user_domain($author_id).'" title="'.bp_core_get_user_displayname( $author_id) .'"> '.get_avatar($author_id).' <span><strong>'.__('Instructor','vibe').'</strong><br />'.bp_core_get_user_displayname( $author_id) .'</span></a>
          </div>';
       
  }
}

function wplms_user_course_check($user_id = null,$course_id = null){

  if(!isset($user_id) || !$user_id )
    $user_id = get_current_user_id();
  if(!isset($course_id) || !$course_id || !is_numeric($course_id)){
    global $post;
    if($post->post_type == 'course'){
      $course_id = $post->ID;
    }
  }

  $check = get_user_meta($user_id,$course_id,true);
  if(isset($check) && $check && is_numeric($check))
    return true;

  return false;
}



function wplms_user_course_active_check($user_id = null,$course_id = null){

  if(!isset($user_id) || !$user_id)
    $user_id = get_current_user_id(); 

  if(!is_numeric($course_id)){
    global $post;
    if($post->post_type == 'course'){
      $course_id = $post->ID;
    }
  }

  $check = get_user_meta($user_id,$course_id,true);
  if(isset($check) && $check > time()){
    $course_check = bp_course_get_user_course_status($user_id,$course_id);
    if(isset($course_check) && $course_check < 4 ) // Check status of the Course 0 : Start, 1: Continue, 2: Finished and under evaluation, >2: Evaluated
      return true;
  }  
  return false;
}

function the_course_time($args){
  echo '<strong>'.__('Time Remaining','vibe').' : <span>'.get_the_course_time($args).'</span></strong>';
}

function get_the_course_time($args){
  $defaults=array(
    'course_id' =>get_the_ID(),
    'user_id'=> get_current_user_id()
    );
  $r = wp_parse_args( $args, $defaults );

    extract( $r, EXTR_SKIP );

    $start_date = get_post_meta($course_id,'vibe_start_date',true);
 
    if(!empty($start_date) && strtotime($start_date) >= time()){
        $seconds = bp_course_get_course_duration($course_id);
    }else{
      $seconds=get_user_meta($user_id,$course_id,true);

      if(!isset($seconds) || !$seconds){
        $seconds = bp_course_get_course_duration($course_id);
      }else{
        $seconds = $seconds - time();
      }
    }

    if($seconds<0)
      $seconds = 0;
    $time = tofriendlytime($seconds);
    return apply_filters('course_friendly_time',$time,$seconds,$course_id);      
}

/*
    BADGE FUNCTIONS
 */
function bp_get_course_badge($id=NULL){
  if(!isset($id))
      $id=get_the_ID();

  $badge=get_post_meta($id,'vibe_course_badge',true);
  return $badge;
}

// USER BADGES
function bp_course_get_user_badges($user_id = null){
  if(empty($user_id))
      $user_id = get_current_user_id();

  $badges = apply_filters('bp_course_get_user_badges','',$user_id);
  if(empty($badges))
      $badges=get_user_meta($user_id,'badges',true); 

  return $badges;
}

function bp_course_get_badge($badge_id,$title_id){
    if(empty($badge_id))
        return;
  
    $badge=wp_get_attachment_info($badge_id); 
    $badge_url=wp_get_attachment_image_src($badge_id,'full');
    return '<a class="tip ajax-badge" data-course="'.get_the_title($title_id).'" title="'.get_post_meta($title_id,'vibe_course_badge_title',true).'"><img src="'.$badge_url[0].'" title="'.$badge['title'].'"/></a>';
}

/*
END  BADGE FUNCTIONS
 */
/*
 CERTIFICATE FUNCTIONS
 */

function bp_course_get_user_certificates($user_id = null){
    if(empty($user_id))
        $user_id = get_current_user_id();

    $certificates = apply_filters('bp_course_get_user_certificates','',$user_id);
    if(empty($certificates))
        $certificates=get_user_meta($user_id,'certificates',true); 

    return $certificates;
}

function bp_get_course_certificate($args){
  $defaults=array(
    'course_id' =>get_the_ID(),
    'user_id'=> get_current_user_id()
    );

  $r = wp_parse_args( $args, $defaults );
  extract( $r, EXTR_SKIP );

    $url = apply_filters('bp_get_course_certificate_url',0,$course_id,$user_id);
    if(empty($url)){
        $certificate_template_id=get_post_meta($course_id,'vibe_certificate_template',true);
        if(!empty($certificate_template_id) && is_numeric($certificate_template_id)){
            $pid = $certificate_template_id;
        }else{
            if(function_exists('vibe_get_option')){
                $pid=vibe_get_option('certificate_page');
            }
        }
        $url = get_permalink($pid).'?c='.$course_id.'&u='.$user_id;
    }
    return $url;
}

/*
    END  CERTIFICATE FUNCTIONS
*/

function bp_get_total_instructor_count(){
  $args =  array(
    'role' => 'Instructor',
    'count_total' => true
    );
  $users = new WP_User_Query($args);
  return count($users->results);
  
}

function bp_course_quiz_auto_submit($quiz_id,$user_id){

    $quiz_auto_evaluate=get_post_meta($quiz_id,'vibe_quiz_auto_evaluate',true);

    if(vibe_validate($quiz_auto_evaluate)){ // Auto Evaluate for Quiz Enabled, Quiz auto evaluate, autoevaluate
        $total_marks=0;
        $questions = bp_course_get_quiz_questions($quiz_id,$user_id);
        $max_marks = array_sum($questions['marks']);
        
        if(count($questions)){
            $sum=$max_sum=0;
            foreach($questions['ques'] as $key=>$question){ // Grab all the Questions
                $marks = 0;
                if(isset($question) && $question){

                    $type = get_post_meta($question,'vibe_question_type',true); 
                    $auto_evaluate_question_types=apply_filters('wplms_auto_evaluate_question_types',array('truefalse','single','multiple','sort','match','fillblank','select','smalltext'));                             

                    if($type == 'survey'){
                        $correct_answer=get_post_meta($question,'vibe_question_answer',true);
                        $options = get_post_meta($question,'vibe_question_options',true);
                        if(empty($options)){$options = array();}
                        $user_marked_answer = bp_course_get_question_marked_answer($quiz_id,$question,$user_id);
                        $option_key = array_search($user_marked_answer,$options);
                        $option_key++;

                        //option count
                        $marks= $option_key*apply_filters('wplms_correct_quiz_answer',$questions['marks'][$key],$quiz_id,$user_marked_answer,$question);
                        $total_marks = $total_marks+$marks;
                        bp_course_save_user_answer_marks($quiz_id,$question,$user_id,$marks);

                    } else if(isset($type) && in_array($type,$auto_evaluate_question_types) ){
                        
                        $correct_answer=get_post_meta($question,'vibe_question_answer',true);
                        $user_marked_answer = bp_course_get_question_marked_answer($quiz_id,$question,$user_id); 

                        $check = bp_course_evaluate_question(array('question_id'=>$question,'type'=>$type,'correct_answer'=>$correct_answer,'marked_answer'=>$user_marked_answer),$user_marked_answer);

                        if(is_float($check)){
                          $marks = round($check*$questions['marks'][$key],0);
                          $check = 1;
                        }else{
                          $marks = $check*$questions['marks'][$key];
                        }

                        if($check){
                          $marks= apply_filters('wplms_correct_quiz_answer',$marks,$quiz_id,$user_marked_answer,$question);
                          
                          $total_marks = $total_marks+$marks;
                        }else{                               // Use cases for No exact match for answer
                            $marks = apply_filters('wplms_incorrect_quiz_answer',0,$quiz_id,$user_marked_answer,$question);
                            $total_marks = $total_marks+$marks;
                        }
                        
                        bp_course_save_user_answer_marks($quiz_id,$question,$user_id,$marks);
                    } // Auto evaluate  check ends
                }// End isset question
            }// End foreach

            update_post_meta( $quiz_id, $user_id,$total_marks);
            bp_course_update_user_quiz_status($user_id,$quiz_id,1);
            do_action('wplms_evaluate_quiz',$quiz_id,$total_marks,$user_id,$max_marks);
        }//End IF set question
    }
}

//Evaluate individual question function
function bp_course_evaluate_question($args,$marked_answer){
    $correct = 1; $incorrect = 0; 
    //Args must include question_id
    extract($args);
    if(empty($question_id))
        return $incorrect;

    if(empty($type))
        $type = get_post_meta($question_id,'vibe_question_type',true); 

    if(!isset($correct_answer))
        $correct_answer = get_post_meta($question_id,'vibe_question_answer',true);

    if(!isset($marked_answer) || !isset($correct_answer) || !isset($type))
        return $incorrect;

    
    $partial_marking = apply_filters('bp_course_evaluate_question_partial_marking',0);
    switch($type){
        case 'multiple':
            $marked_answers = explode(',',$marked_answer);
            if(!is_array($marks_answers)){ // Force Array Form
                $marks_answers=array($marks_answers);
            } 
                
            $correct_answers = explode(',',$correct_answer);
            if(!is_array($correct_answers)){ // Force Array Form
                $correct_answers=array($correct_answers);
            }
            foreach($marked_answers as $k=>$v){
              if($v==''){unset($marked_answers[$k]);}
            }
            sort($marked_answers);
            sort($correct_answers);

            if($partial_marking){
              $marked_answers_by_user = 0;

              foreach($marked_answers as $k=>$v){
                if(in_array($v,$correct_answers)){
                  $marked_answers_by_user++;
                }else{
                  $marked_answers_by_user--;
                }
              }
              if( $marked_answers_by_user < 0 ){
                $marked_answers_by_user = 0;
              }

              return ($marked_answers_by_user/count($correct_answers));
              
            }else{
              if(array_diff($marked_answers,$correct_answers) == array_diff($correct_answers,$marked_answers)){
                  return $correct;
              }
            }
            
        break;
        case 'smalltext':
        case 'fillblank':
        case 'select':
            //IF multiple Fill blanks
           
            $marked_answer = rtrim($marked_answer,'|');
            if(strpos($correct_answer, '|') !== false){
                $correct_answers = explode('|',$correct_answer);
                if(!empty($marked_answer)){
                    if(strpos($marked_answer, '|') !== false){
                      $marked_answers = explode('|',$marked_answer);
                    }else{
                      $marked_answers = array($marked_answer);
                    }
                    
                    $marked_answers_by_user = count($correct_answers);
                    foreach($correct_answers as $ci=>$c_answer){

                        if(!isset($marked_answers[$ci]) && !$partial_marking){
                          return $incorrect;
                        }

                        if($partial_marking && !isset($marked_answers[$ci])){
                          $marked_answers[$ci] = '';
                        }

                        $k = apply_filters('wplms_text_correct_answer',strtolower($c_answer),$c_answer);
                        
                        if(strpos($k, ',') !== false){
                            $c_arr= explode(',',$k);
                            if(!empty($c_arr)){
                              foreach($c_arr as $x=>$v){
                                $c_arr[$x]=trim($v,' ');
                              }
                            }
                        }else{
                          $k = trim($k,' ');
                          $c_arr = array($k);  
                        }
                        
                        $marked_answers[$ci] = trim($marked_answers[$ci],' ');

                        $marked_answers[$ci] = apply_filters('wplms_text_correct_answer',strtolower($marked_answers[$ci]),$marked_answers[$ci]);
                        if(!in_array($marked_answers[$ci],$c_arr)){ // Match sequentially
                          if($partial_marking){
                            $marked_answers_by_user--;
                          }else{
                            return $incorrect;
                          }
                        }
                    }
                    if($partial_marking){
                      return ($marked_answers_by_user/count($correct_answers));
                    }
                    return $correct;
                }
            }else{
                if(strpos($correct_answer, ',') !== false){
                    $correct_answers_array = explode(',',$correct_answer);
                    foreach($correct_answers_array as $c_answer){
                        $c_answer = apply_filters('wplms_text_correct_answer',strtolower($c_answer),$c_answer);
                        $marked_answer = apply_filters('wplms_text_correct_answer',strtolower($marked_answer),$marked_answer);
                            if( $c_answer == $marked_answer){
                                return $correct;
                            
                        }
                    }
                }else{
                    $correct_answer = apply_filters('wplms_text_correct_answer',strtolower($correct_answer),$correct_answer);
                    $marked_answer = apply_filters('wplms_text_correct_answer',strtolower($marked_answer),$marked_answer);
                    if($correct_answer == $marked_answer){
                        return $correct;
                    }
                }
            }
        break;
        case 'sort':
        case 'match':
            $marked_answers = explode(',',$marked_answer);
            if(!is_array($marks_answers)){ // Force Array Form
                $marks_answers=array($marks_answers);
            } 
                
            $correct_answers = explode(',',$correct_answer);
            if(!is_array($correct_answers)){ // Force Array Form
                $correct_answers=array($correct_answers);
            }
            foreach($marked_answers as $k=>$v){
              if($v==''){unset($marked_answers[$k]);}
            }

            if($partial_marking){
              $marked_answers_by_user = 0;

              foreach($marked_answers as $k=>$v){
                if($v == $correct_answers[$k]){
                  $marked_answers_by_user++;
                }
              }
              if( $marked_answers_by_user < 0 ){
                $marked_answers_by_user = 0;
              }

              return ($marked_answers_by_user/count($correct_answers));
              
            }else{
              if($marked_answer == $correct_answer){
                  return $correct;
              }
            }
        break;
        default:
            $correct_answer = apply_filters('bp_course_evaluate_question',$correct_answer,$args,$marked_answer);
            if($marked_answer == $correct_answer){
                return $correct;
            }
        break;
    }

    return $incorrect;
}
// End function

function bp_course_validate_certificate($args){
  $defaults=array(
    'course_id' =>get_the_ID(),
    'user_id'=> get_current_user_id()
    );
  $r = wp_parse_args( $args, $defaults );
  extract( $r, EXTR_SKIP );
  
  $meta = vibe_sanitize(get_user_meta($user_id,'certificates',false));

  if(isset($meta)){ 
    if((in_array($course_id,$meta) && is_array($meta)) || (!is_array($meta) && $course_id==$meta)){
      return;
    }else{
      wp_die(__('Certificate not valid for user','vibe'));
    }
  }else{
    wp_die(__('Certificate not valid for user','vibe'));
  }
}

function bp_course_add_user_to_course($user_id,$course_id,$duration = NULL,$force = NULL,$args=null){
  
    if(empty($args)){$args=array();}
    
    $seats = bp_course_get_max_students($course_id,$user_id); 
    $students = bp_course_count_students_pursuing($course_id);

    if(!empty($seats) && $seats < 9999 && empty($force)){
      if($seats < $students){ 
         return false;
      }
    }
    $total_duration = 0;
    if(empty($duration)){
      $total_duration = bp_course_get_course_duration($course_id,$user_id);
    }else{
      $total_duration = $duration;
    }
   
    $time=0;
    $existing = get_user_meta($user_id,$course_id,true);

    if(empty($existing)){
        $start_date = bp_course_get_start_date($course_id,$user_id); 
        if(isset($start_date) && $start_date){
          $time=strtotime($start_date);
        }
    }else{
        $time = $existing;
    }

    if($time<time())
      $time=time();

    if(empty($total_duration)){
      $total_duration=0;
    }

    $t=$time+$total_duration;

    update_post_meta($course_id,$user_id,0);
    
    if(empty($existing)){
      update_user_meta($user_id,'course_status'.$course_id,1);
      $accuracy = vibe_get_option('sync_student_count');
      if(empty($accuracy) || $accuracy == '0'){ 
        $students = get_post_meta($course_id,'vibe_students',true);
      }
      $students++;
      update_post_meta($course_id,'vibe_students',$students);
    }else{
      update_user_meta($user_id,'course_status'.$course_id,2);
    }

    update_user_meta($user_id,$course_id,$t);

    $group_id=get_post_meta($course_id,'vibe_group',true);
    if(!empty($group_id) && function_exists('groups_join_group')){
      groups_join_group($group_id, $user_id );  
    }else{
      $group_id = '';
    }

    $auto_forum_subscribe = apply_filters('bp_course_add_user_to_course_enable_forum_subscription',1);
    if($auto_forum_subscribe){
      $forum_id = get_post_meta($course_id,'vibe_forum',true);
      if(!empty($forum_id) && function_exists('bbp_add_user_forum_subscription')){
        bbp_add_user_forum_subscription( $user_id, $forum_id);
      }
    }

    do_action('wplms_course_subscribed',$course_id,$user_id,$group_id,$args);

    return $t;
}

function bp_course_remove_user_from_course($user_id,$course_id){
    
    delete_post_meta($course_id,$user_id);
    delete_user_meta($user_id,$course_id);
    delete_user_meta($user_id,'course_status'.$course_id);

    if(function_exists('groups_leave_group')){
      $group_id=get_post_meta($course_id,'vibe_group',true);
      if(!empty($group_id)){
        groups_leave_group($group_id, $user_id );  
      }
    }
    if(function_exists('bbp_remove_user_forum_subscription')){
      $forum_id = get_post_meta($course_id,'vibe_forum',true);
      if(!empty($forum_id) && function_exists('bbp_remove_user_forum_subscription')){
        bbp_remove_user_forum_subscription( $user_id, $forum_id);
      }    
    }
    if ( class_exists( 'WooCommerce' ) ) {
      $product_id = get_post_meta($course_id,'vibe_product',true);
    }
    do_action('wplms_course_unsubscribed',$course_id,$user_id);

}

function bp_course_instructor_controls(){
  global $bp,$wpdb;
  $user_id=$bp->loggedin_user->id;
  $course_id = get_the_ID();

  $curriculum=bp_course_get_curriculum($course_id);
  $course_quizes=array();
  if(!empty($curriculum)){
      foreach($curriculum as $c){
        if(is_numeric($c)){
          if(bp_course_get_post_type($c) == 'quiz'){
              $course_quizes[]=$c;
            }
        }
    }
  }else{

  }

  echo '<ul class="instructor_action_buttons">';

  $course_query = $wpdb->get_results($wpdb->prepare("SELECT COUNT(meta_key) as num FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_value = %d",$course_id,2));
  $num=0;
  if(isset($course_query) && $course_query !='')
    $num=$course_query[0]->num;
  else
    $num=0;

  $admin_slug = '/?action=admin';
  $extend_submissions = '/?action=admin&submissions';
  $extend_stats = '/?action=admin&stats';
  $extend_activity = '/?action=activity';

  if(class_exists('Vibe_CustomTypes_Permalinks')){
    $permalinks = Vibe_CustomTypes_Permalinks::init();
    $tips = WPLMS_tips::init();
    
    if(!empty($permalinks) && empty($tips->settings['revert_permalinks'])){
      $admin_slug = $permalinks->permalinks['admin_slug'];
      $extend_submissions = $permalinks->permalinks['admin_slug'].$permalinks->permalinks['submissions_slug'];
      $extend_stats = $permalinks->permalinks['admin_slug'].$permalinks->permalinks['stats_slug'];
      $extend_activity = $permalinks->permalinks['activity_slug'];
    }
  }
  echo '<li><a href="'.get_permalink($course_id).$extend_submissions.'" class="action_icon tip" title="'.__('Evaluate course submissions','vibe').'"><i class="icon-task"></i><span>'.$num.'</span></a></li>';  

  if(isset($course_quizes) && !empty($course_quizes) && is_array($course_quizes) && count($course_quizes)){
    if(is_array($course_quizes))
      $course_quizes = join(',',$course_quizes);  
      
      $num = $wpdb->get_var($wpdb->prepare("SELECT COUNT(meta_key) FROM {$wpdb->postmeta} WHERE post_id IN ({$course_quizes}) AND meta_key REGEXP '^[0-9]+$' AND meta_value = %d",0));

    
    if(!is_numeric($num))
      $num=0;

    echo '<li><a href="'.get_permalink($course_id).$extend_submissions.'" class="action_icon tip"  title="'.__('Evaluate Quiz submissions','vibe').'"><i class="icon-check-clipboard-1"></i><span>'.$num.'</span></a></li>';

  } 

  $n=get_post_meta($course_id,'vibe_students',true);
  if(isset($n) && $n !=''){$num=$n;}else{$num=0;}
  echo '<li><a href="'.get_permalink($course_id).$admin_slug.'" class="action_icon tip"  title="'.__('Manage Students','vibe').'"><i class="icon-users"></i><span>'.$num.'</span></a></li>';
  echo '<li><a href="'.get_permalink($course_id).$extend_stats.'" class="action_icon tip"  title="'.__('See Stats','vibe').'"><i class="icon-analytics-chart-graph"></i></a></li>';
  echo '<li><a href="'.get_permalink($course_id).$extend_activity.'" class="action_icon tip"  title="'.__('See all Activity','vibe').'"><i class="icon-atom"></i></a></li>';
  echo '</ul>';
}


function bp_wplms_get_theme_color(){
  $option = get_option('vibe_customizer');
  if(isset($option) && is_Array($option)){
    if(isset($option['primary_bg']))
     return $option['primary_bg'];
  }
  return '#78c8c9';
}

function bp_wplms_get_theme_single_dark_color(){
  $option = get_option('vibe_customizer');
  if(isset($option) && is_Array($option)){
    if(isset($option['single_dark_color']))
     return $option['single_dark_color'];
  }
  return '#232b2d';
}

if(!function_exists('calculate_duration_time')){
  function calculate_duration_time($seconds) {
    switch($seconds){
      case 1: $return = __('Seconds','vibe');break;
      case 60: $return = __('Minutes','vibe');break;
      case 3600: $return = __('Hours','vibe');break;
      case 86400: $return = __('Days','vibe');break;
      case 604800: $return = __('Weeks','vibe');break;
      case 2592000: $return = __('Months','vibe');break;
      case 31104000: $return = __('Years','vibe');break;
      default:
      $return = apply_filters('vibe_calculation_duration_default',$return,$seconds);
      break;
    }
    return $return;
  } 
}

if(!function_exists('pmpro_wplms_renew_course')){
 add_filter('wplms_course_product_id','pmpro_wplms_renew_course',10,2);
 function pmpro_wplms_renew_course($pid,$course_id){
   if(!is_numeric($pid)){
     if ( in_array( 'paid-memberships-pro/paid-memberships-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php'))) {
        $membership_ids = get_post_meta($course_id,'vibe_pmpro_membership',true);
        if(!empty($membership_ids)){
          $pmpro_levels_page_id = get_option('pmpro_levels_page_id');
          $pid = get_permalink($pmpro_levels_page_id);
        }
     } 
   }
   return $pid;
 }
}

// Submission functions
function bp_course_get_course_submission_count($course_id){
  global $wpdb;
  $count = $wpdb->get_var("SELECT count(*) FROM {$wpdb->usermeta} WHERE meta_key = 'course_status$course_id' AND meta_value LIKE '3'");
  return (empty($count)?0:$count);
} 

function bp_course_get_quiz_submission_count($course_id){
  global $wpdb,$bp;
  $quizes = bp_course_get_curriculum_quizes($course_id);
  if(!empty($quizes)){
    $quiz_ids = implode(',',$quizes);
    $count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM {$wpdb->postmeta} as p LEFT JOIN {$wpdb->usermeta} as u ON p.meta_key = u.user_id WHERE p.meta_value LIKE '0' AND u.meta_key = p.post_id AND u.meta_value < %d AND p.post_id IN ($quiz_ids)",time()));
  }
  return (empty($count)?0:$count);
}

function bp_course_member_userview($student,$course_id = null){

  if(empty($course_id)){
    $course_id = get_the_ID();
  }

  if (function_exists('bp_get_profile_field_data')) {
    $bp_name = bp_core_get_userlink( $student );
    $sfield=vibe_get_option('student_field');
    if(!isset($sfield) || $sfield =='')
      $sfield = 'Location';

    $bp_location ='';
    if(bp_is_active('xprofile'))
    $bp_location = bp_get_profile_field_data('field='.$sfield.'&user_id='.$student);
    
    if ($bp_name) {
      echo '<li>';
      echo get_avatar($student);
      echo '<h6>'. $bp_name . '</h6>';
      if ($bp_location) {
        echo '<span>'. $bp_location . '</span>';
      }

      echo '<div class="action">';
      $check_meta = vibe_get_option('members_activity');
      if(bp_is_active('friends') && $check_meta){
        if(function_exists('bp_add_friend_button')){
          bp_add_friend_button( $student );
        }
      }
      echo '</div></li>';
    }  
  }
}
/*
USER VIEW in COURSE ADMIN AREA
 */
function bp_course_admin_userview($student,$course_id = null){
    if (function_exists('bp_core_get_userlink')) {
        $bp_name = bp_core_get_userlink( $student );

        $bp_location='';
        if(empty($course_id)){
          $course_id = get_the_ID();
        }
        if(function_exists('vibe_get_option'))
            $ifield = vibe_get_option('student_field');

        if(!isset($field) || $field =='')$field='Location';

        if(bp_is_active('xprofile'))
            $bp_location = bp_get_profile_field_data('field='.$field.'&user_id='.$student);
        
        if ($bp_name) {
            echo '<li id="s'.$student.'"><input type="checkbox" class="member" value="'.$student.'"/>';
            echo get_avatar($student);
            echo '<h6>'. $bp_name . '</h6><span>';
            if ($bp_location) {
                echo $bp_location;
            }
            
            if(function_exists('bp_course_user_time_left')){
                echo ' ( '; bp_course_user_time_left(array('course'=>$course_id,'user'=>$student));
                echo ' ) ';
            }

            echo '</span>';
            do_action('wplms_user_course_admin_member',$student,$course_id);
            // PENDING AJAX SUBMISSIONS
            echo '<ul> 
                    <li><a class="tip reset_course_user" data-course="'.$course_id.'" data-user="'.$student.'" title="'.__('Reset Course for User','vibe').'"><i class="icon-reload"></i></a></li>
                    <li><a class="tip course_stats_user" data-course="'.$course_id.'" data-user="'.$student.'" title="'.__('See Course stats for User','vibe').'"><i class="icon-bars"></i></a></li>';
            if(class_exists('WPLMS_tips')){
                $tips = WPLMS_tips::init();
                if(!empty($permalinks) && empty($tips->settings['revert_permalinks'])){
                    $permalinks = Vibe_CustomTypes_Permalinks::init();      
                    if(empty($permalinks) || empty($permalinks->permalinks) || empty($permalinks->permalinks['activity_slug'])){
                        $activity_slug = '/activity';
                        $activity_slug = str_replace('/','',$activity_slug).'?';
                    }else{
                        $activity_slug = $permalinks->permalinks['activity_slug'];
                        $activity_slug = str_replace('/','',$activity_slug).'?';
                    }   
                }else{
                    $activity_slug = '?action='.BP_ACTIVITY_SLUG.'&';
                }
            }                           
            
            echo '<li><a href="'.get_permalink($course_id).$activity_slug.'student='.$student.'" class="tip" title="'.__('See User Activity in Course','vibe').'"><i class="icon-atom"></i></a></li>
                    <li><a class="tip remove_user_course" data-course="'.$course_id.'" data-user="'.$student.'" title="'.__('Remove User from this Course','vibe').'"><i class="icon-x"></i></a></li>
                    '.do_action('wplms_course_admin_members_functions',$student,$course_id).'
                  </ul>';
            echo '</li>';
        }
    }
}

/* RECORD COMMISSIONS */
if(!function_exists('bp_course_record_instructor_commission')){
    function bp_course_record_instructor_commission($instructor_id,$commission,$course_id,$meta){
        $commission = apply_filters('bp_course_record_instructor_commission',$commission,$instructor_id,$course_id,$meta);
        $commission_html = $secondary_item_id= '';
        switch($meta['origin']){
            case 'woocommerce':
                if(function_exists('wc_update_order_item_meta')){
                    wc_update_order_item_meta( $meta['item_id'], '_commission'.$instructor_id,$commission);
                }else{
                    woocommerce_update_order_item_meta( $meta['item_id'], '_commission'.$instructor_id,$commission);
                }

                $commission_html = '';
                if(function_exists('wc_price')){
                    $price = round($commission,0);
                    $commission_html = wc_price($price);
                }
                $secondary_item_id = $meta['item_id'];
            break;
            default:
                do_action('bp_course_record_instructor_commission',$instructor_id,$commission,$meta);
            break;
        }
        if(!empty($instructor_id) && !empty($commission_html) && !empty($course_id)){
           $activity_id = bp_course_record_activity(apply_filters('bp_course_record_instructor_commission_activity',array(
                'user_id' => $instructor_id,
                'action' => _x('You earned commission','Instructor earned commission activity','vibe'),
                'content' => sprintf(_x('%s commission earned for course %s','Instructor earned commission activity','vibe'),$commission_html,get_the_title($course_id)),
                'component' => 'course',
                'type' => 'course_commission',
                'item_id' => $course_id,
                'secondary_item_id' => $secondary_item_id,
                'hide_sitewide' => true,
            )));
           if(!empty($activity_id)){
                bp_course_record_activity_meta($activity_id,'_commission'.$instructor_id,$commission);
           }
        }
 
    } 
}


function bp_course_get_setting($setting,$from,$type=null){
    // Check Vibe options panel or LMS Setting
    if(class_exists('wplms_miscellaneous_settings')){
      
        $misc = wplms_miscellaneous_settings::init();
        
        if(!empty($misc) && !empty($misc->settings)){
            
            if(!isset($misc->settings[$from]))
              return false;
            
            $settings = $misc->settings[$from];
            if(isset($settings[$setting])){
                if(isset($type)){
                    switch($type){
                        case 'bool':
                            return true;
                        break;
                        default:
                          if(isset($settings[$setting])){
                            return $settings[$setting];
                          }                          
                        break;
                    }
                }else{
                    return $settings[$setting];    
                }
            }else{
                return false;
            }  
        }
    }
    return false;
}

/*
 FUNCTIONS FOR API
*/
function bp_course_get_course_announcements_for_user($user_id){
    if(function_exists('wplms_dashboards_get_user_annoncements')){
      return wplms_dashboards_get_user_annoncements($user_id);
    }
  return array();
}

function bp_course_get_total_quiz_count_for_user($user_id){
    global $wpdb;
    $count = $wpdb->get_var($wpdb->prepare("
      SELECT count(*) 
      FROM {$wpdb->posts} as p
      LEFT JOIN {$wpdb->postmeta} as m
      ON p.ID = m.post_id
      WHERE p.post_type = %s
      AND p.post_status = %s
      AND m.meta_key = %d
      ",'quiz','publish',$user_id));

    return $count;
}

function bp_course_api_get_user_badges($user_id){
    $badges = array();
    $course_badges = bp_course_get_user_badges($user_id);
    if(!empty($course_badges)){
        foreach($course_badges as $course_id){
            $badge_id = bp_get_course_badge($course_id);
            $badge=wp_get_attachment_info($badge_id); 
            $badge_url=wp_get_attachment_image_src($badge_id,'full');
            if(is_array($badge_url)){$badge_url=$badge_url[0];}
            $badges[] = array('key'=>$course_id,'type'=>'badge','label'=>get_post_meta($course_id,'vibe_course_badge_title',true),'value'=>$badge_url);
        }
    }
    return $badges;
}

function bp_course_api_get_user_certificates($user_id){
    
    $certificates = array();
    $course_certificates = bp_course_get_user_certificates($user_id);
    if(!empty($course_certificates)){
        foreach($course_certificates as $course_id){
            $url = bp_get_course_certificate(Array('course_id'=>$course_id,'user_id'=>$user_id));
            $certificates[] = array(
                'key' => $course_id,
                'type' => 'certificate',
                'label' =>  get_the_title($course_id),
                'value' => $url
                );
        }
    }
    return $certificates;
}

/*
* QUIZ RETAKES REVAMP
* 
*/

/**
 * Initiates Student Quiz retakes
 *
 * Takes quiz id and Student id/user id in Arguments array
 * and processes quiz retakes. Also runs hook for recording activity and other touch points
 *
 * @since 3.3
 *
 * @param array
 */

function bp_course_student_quiz_retake($args){
    $defaults = array(
      'quiz_id' => get_the_ID(),
      'user_id' => get_current_user_id()
      );
    $params = wp_parse_args( $args, $defaults );
    extract( $params, EXTR_SKIP );
    
    if ( !isset($user_id) || !$user_id){
        wp_die(__(' Incorrect User selected.','vibe'),__('Security Error','vibe'),array('back_link' => true));
    }

    bp_course_remove_user_quiz_status($user_id,$quiz_id);
    bp_course_remove_quiz_questions($quiz_id,$user_id);
    $retake_count = bp_course_fetch_user_quiz_retake_count($quiz_id,$user_id);
    bp_course_update_user_quiz_retake_count($quiz_id,$user_id,($retake_count+1));
    
    $course_id = get_post_meta($quiz_id,'vibe_quiz_course',true);
    if(!empty($course_id)){ // Course progressbar fix for single quiz
      
      $curriculum = bp_course_get_curriculum_units($course_id);
      $per = round((100/count($curriculum)),2);
      $progress = get_user_meta($user_id,'progress'.$course_id,true);
      if(empty($progress))
        $progress = 0;

      $new_progress = $progress - $per;
      if($new_progress < 0){
        $new_progress = 0;
      }
      update_user_meta($user_id,'progress'.$course_id,$new_progress);
    }
    do_action('wplms_quiz_retake',$quiz_id,$user_id);
}

/**
 * Initiates Student Quiz retakes
 *
 * Takes quiz id in Arguments array
 * and processes quiz retakes. Also runs hook for recording activity and other touch points
 *
 *
 * @param array
 */
if(!function_exists('student_quiz_retake')){
  function student_quiz_retake($args=NULL){
      $defaults = array(
        'quiz_id' => get_the_ID(),
        'user_id' => get_current_user_id()
        );
      $params = wp_parse_args( $args, $defaults );
      extract( $params, EXTR_SKIP );
      
      if ( !isset($user_id) || !$user_id){
          wp_die(__(' Incorrect User selected.','vibe'),__('Security Error','vibe'),array('back_link' => true));
      }

      bp_course_remove_user_quiz_status($user_id,$quiz_id);
      bp_course_remove_quiz_questions($quiz_id,$user_id);
      
      $course_id = get_post_meta($quiz_id,'vibe_quiz_course',true);
      if(!empty($course_id)){ // Course progressbar fix for single quiz
        
        $curriculum = bp_course_get_curriculum_units($course_id);
        $per = round((100/count($curriculum)),2);
        $progress = get_user_meta($user_id,'progress'.$course_id,true);
        if(empty($progress))
          $progress = 0;
 
        $new_progress = $progress - $per;
        if($new_progress < 0){
          $new_progress = 0;
        }
        update_user_meta($user_id,'progress'.$course_id,$new_progress);
      }
      $retake_count = bp_course_fetch_user_quiz_retake_count($quiz_id,$user_id);
      $retake_count = intval($retake_count);
      bp_course_update_user_quiz_retake_count($quiz_id,$user_id,($retake_count+1));
      do_action('wplms_quiz_retake',$quiz_id,$user_id);
  }
}

//Number of times the user has retaken the quiz
function bp_course_fetch_user_quiz_retake_count($quiz_id,$user_id){

  $retake_count = get_user_meta($user_id,'quiz_retakes_'.$quiz_id,true);
  
  $quiz_course_id = get_post_meta($quiz_id,'vibe_quiz_course',true);
  if(!empty($quiz_course_id)){
     $status = bp_course_get_user_course_status($user_id,$quiz_course_id);
     if($status > 2)
      return 0;
  }
  
    if(empty($retake_count) && $retake_count != 0 ){
      $retake_count = $wpdb->get_var($wpdb->prepare( "
                SELECT count(activity.content) FROM {$bp->activity->table_name} AS activity
                WHERE   activity.component  = 'course'
                AND   activity.type   = 'retake_quiz'
                AND   user_id = %d
                AND   ( item_id = %d OR secondary_item_id = %d )
                ORDER BY date_recorded DESC
              " ,$user_id,$quiz_id,$quiz_id));
      
      $retake_count = intval($retake_count);
      bp_course_update_user_quiz_retake_count($quiz_id,$user_id,$retake_count);
    }
  
  return apply_filters('bp_course_fetch_user_quiz_retake_count',$retake_count,$quiz_id,$user_id);
}

//Number of times the user has retaken the quiz
function bp_course_update_user_quiz_retake_count($quiz_id,$user_id,$value){
  $flag = apply_filters('bp_allow_update_user_quiz_retake_count',1,$quiz_id,$user_id,$value);
  if($flag){
    update_user_meta($user_id,'quiz_retakes_'.$quiz_id,$value);
  }
}

//Number of times the user has retaken the quiz
function bp_course_reset_quiz_retakes($quiz_id,$user_id){
  update_user_meta($user_id,'quiz_retakes_'.$quiz_id,0);
}
