<?php

if ( !defined( 'ABSPATH' ) ) exit;

add_action( 'bp_setup_nav', 'vibe_bp_hide_tabs', 15 );
function vibe_bp_hide_tabs(){

   $flag=1;
   $hide=vibe_get_option('activity_tab');
   if(isset($hide)){
      $flag=0;
      switch($hide){
         case 1:
            if(is_user_logged_in()) $flag=1;
         break;
         case 2:
            if(current_user_can('edit_posts')) $flag=1;
         break;
         case 3:
            if(current_user_can('manage_options')) $flag=1;
         break;
         case 0: $flag=1;
         break;
      }
   }
   if(!$flag && bp_is_active( 'groups' ) && !bp_is_my_profile()){
      bp_core_remove_nav_item('activity');   
   }

   $flag=1;
   $hide=vibe_get_option('groups_tab');
   if(isset($hide)){
      $flag=0;
      switch($hide){
         case 1:
            if(is_user_logged_in()) $flag=1;
         break;
         case 2:
            if(current_user_can('edit_posts')) $flag=1;
         break;
         case 3:
            if(current_user_can('manage_options')) $flag=1;
         break;
         case 0: $flag=1;
         break;
      }
   }
   if(!$flag && bp_is_active( 'groups' ) && !bp_is_my_profile()){
      bp_core_remove_nav_item('groups');   
   }
   
   $flag=1;
   $hide=vibe_get_option('forums_tab');
   if(isset($hide)){
      $flag=0;
      switch($hide){
         case 1:
            if(is_user_logged_in()) $flag=1;
         break;
         case 2:
            if(current_user_can('edit_posts')) $flag=1;
         break;
         case 3:
            if(current_user_can('manage_options')) $flag=1;
         break;
         case 0: $flag=1;
         break;
      }
   }
   if(!$flag && bp_is_active( 'groups' ) && !bp_is_my_profile()){
      bp_core_remove_nav_item('forums');   
   }
}

add_action('bp_before_profile_content','wplms_show_instructor_courses',11);
function wplms_show_instructor_courses(){
  global $bp;
  $user_id=bp_displayed_user_id();
  if(function_exists('bp_current_action') && bp_current_action() !== 'public')
    return;

  if(!user_can($user_id,'edit_posts')){
    return;
  }

  $block_style = vibe_get_option('default_course_block_style');
  $n = vibe_get_option('loop_number');
  if($n<4){$n=4;}
  if ( function_exists('get_coauthors')) {
    $user = get_user_by('ID',$user_id);
    $args = array(
      'post_type' => 'course',
      'posts_per_page' => $n,
      'post_status' => 'publish',
      'author_name' => $user->user_nicename,
    );
  }else{
    $args = array(
    'post_type'=>'course',
    'post_status'=>'publish',
    'posts_per_page' => $n,
    'author'=> $user_id,
    );  
  }
  
  $args = apply_filters('wplms_show_instructor_courses',$args);
  
  $query = new WP_Query($args);
  if($query->have_posts()){
   
      $instructing_courses=apply_filters('wplms_instructing_courses_endpoint','instructing-courses');
      
      echo '<div class="instructor_courses">
      <h3 class="heading"><span>'._x('Courses by Instructor','courses by instructor title in profile','vibe').'</span><a href="'.get_author_posts_url($user_id).$instructing_courses.'/" class="tip" title="'.sprintf(_x('Check all Courses created by %s','link help text in instructor profile','vibe'),bp_core_get_user_displayname($user_id)).'"><i class="icon-plus-1"></i></a></h3>
      </div>';
   

    echo '<div class="vibe_carousel flexslider" data-controlnav="1" data-block-width="240" data-block-max="4" data-block-min="1"><ul class="slides">';
    $block_style = vibe_get_option('default_course_block_style');
    if(empty($block_style)){$block_style = 'course';}
    while($query->have_posts()){
        $query->the_post();
        global $post;
        echo '<li>';
        echo thumbnail_generator($post,$block_style,4,0);
        echo '</li>';
    }
    wp_reset_postdata();
    echo '</ul></div><br><hr>';
  }
  
}
add_action('bp_before_profile_content','wplms_show_profile_snapshot');

function wplms_show_profile_snapshot(){
   global $bp;

  $user_id=bp_displayed_user_id();
  
  if(function_exists('bp_current_action') && bp_current_action() !== 'public')
    return;
  
    if(function_exists('bp_course_get_user_badges')){
        $bids=bp_course_get_user_badges($user_id);
    }else{
        $bids=vibe_sanitize(get_user_meta($user_id,'badges',false));  
    }
  
  if(isset($bids) && is_Array($bids) && count($bids)){
      $badges_html = array();
      
        foreach($bids as $bid){
            $b='';
            if(function_exists('bp_get_course_badge')){
                $b=bp_get_course_badge($bid);
                if(!empty($b)){
                    if(is_numeric($b)){
                        if(function_exists('bp_course_get_badge')){
                            $badges_html[]= bp_course_get_badge($b,$bid);
                        }else{
                            $badge=wp_get_attachment_info($b); 
                            $badge_url=wp_get_attachment_image_src($b,'full');
                            $badges_html[]='<a class="tip ajax-badge" data-course="'.get_the_title($bid).'" title="'.get_post_meta($bid,'vibe_course_badge_title',true).'"><img src="'.$badge_url[0].'" title="'.$badge['title'].'"/></a>';
                        }
                    }
                }
            }
        }
        
        if(!empty($badges_html)){
            echo '<div class="badges"><h6>'.__('Badges','vibe').'</h6>';
            echo '<ul>';
            foreach($badges_html as $b_html){
                echo '<li>';
                echo $b_html;
                echo '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
   }   

  
   $certis=vibe_sanitize(get_user_meta($user_id,'certificates',false));

     if(isset($certis) && is_Array($certis) && count($certis)){
          echo '<div class="certifications"><h6>'.__('Certifications','vibe').'</h6><ul class="slides">';
          if(isset($certis) && is_Array($certis)) 
           foreach($certis as $certi){
              if(function_exists('bp_get_course_certificate')){

                if(!empty($certi) && get_post_type($certi) == 'course'){

                  $url = bp_get_course_certificate('user_id='.$user_id.'&course_id='.$certi);
                  $class = '';
                  $class = apply_filters('bp_course_certificate_class','',array('course_id'=>$certi,'user_id'=>$user_id));
                  if(isset($_GET['regenerate_certificate']) || (strpos($url, 'jpeg') === false) ) {
                    $class .=' regenerate_certificate';
                  }
    
                  echo '<li><a href="'.$url.'" class="ajax-certificate '.$class.'" data-user="'.$user_id.'" data-course="'.$certi.'" data-security="'.wp_create_nonce($user_id).'"><i class="icon-certificate-file"></i><span>'.get_the_title($certi).'</span></a></li>';
                }
              }
           }
         echo '</ul></div>';  
      }
   
}

add_action('bp_group_options_nav','vibe_course_group_link',1,1);

function vibe_course_group_link(){
   global $bp,$wpdb;
   $course_query= $wpdb->get_results( $wpdb->prepare("SELECT post_id from {$wpdb->postmeta} WHERE meta_key='vibe_group' AND meta_value='%d'",$bp->groups->current_group->id));
   if(is_array($course_query) && isset($course_query[0]->post_id))
      echo '<li id="course-li"><a id="admin" href="'.get_permalink($course_query[0]->post_id).'" title="'.get_the_title($course_query[0]->post_id).'">'.__('Course ','vibe').'</a></li>';
}

function bp_dtheme_setup() {
   if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
      // Group buttons
      if ( bp_is_active( 'groups' ) ) {
         $groups_check = vibe_get_option('enable_groups_join_button');

         if(isset($groups_check) && $groups_check)
          add_action( 'bp_directory_groups_actions', 'bp_group_join_button' );
      }

   }
}

add_action( 'after_setup_theme', 'bp_dtheme_setup' );


/* === Course Specific ===== */

class WPLMS_BP_Cover_Image{

   function __construct(){
      add_action( 'bp_after_setup_theme',array($this,'cover_image'));
      add_action('wp_enqueue_scripts',array($this,'display_cover_image'));
   }

   function cover_image() {
      if(function_exists('bp_set_theme_compat_feature')){
       bp_set_theme_compat_feature( 'legacy', array(
          'name'     => 'cover_image',
          'settings' => array(
             'components'   => array( 'xprofile','groups' ),
             'width'        => 1300,
             'height'       => 500,
             'callback'     => array($this,'record_cover_image'), // See line 845
             'theme_handle' => 'bp-default-main',
          ),
       ) );
     }
   }
   function record_cover_image( $params = array() ) {
     if ( empty( $params ) ) {
        return;
     }

     // avatar height - padding - 1/2 avatar height
     $avatar_offset = $params['height'] - 5 - round( (int) bp_core_avatar_full_height() / 2 );

     // header content offset + spacing
     $top_offset  = bp_core_avatar_full_height() - 10;
     $left_offset = bp_core_avatar_full_width() + 20;

     $this->cover_image = $params['cover_image'];
   }

   function display_cover_image(){
      global $bp;

      if(bp_is_user() || bp_is_group()){
         if(!empty($this->cover_image)){
            echo '<style>
              #item-header{background:url("'.$this->cover_image.'") !important;}
              #buddypress div#item-header.light div#item-header-content,
              #buddypress div#item-header.light #latest-update h6,
              #buddypress div#item-header.light div#item-header-content a,
              #buddypress div#item-header.light div#item-header-content h3 a
              {
                 color:#FFF !important;
              }
              #buddypress div#item-header.dark div#item-header-content,
              #buddypress div#item-header.dark #latest-update h6,
              #buddypress div#item-header.dark div#item-header-content a,
              #buddypress div#item-header.dark div#item-header-content h3 a
              {
                 color:#222 !important;
              }
            </style>';
         }
      }
   }
}

new WPLMS_BP_Cover_Image;
