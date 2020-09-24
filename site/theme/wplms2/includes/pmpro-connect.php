<?php
/**
 * Initialization functions for WPLMS & PMPRO
 *
 * @author      VibeThemes
 * @category    PMPRO
 * @package     PMPRO CONNET
 * @version     2.1
 */


if ( ! defined( 'ABSPATH' ) ) exit;

/*==== PMPRO CONNECT ====*/

Class WPLMS_PMPRO_Connect{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_PMPRO_Connect();

        return self::$instance;
    }

    private function __construct(){
      $this->user_courses='';
      add_action('wplms_the_course_button',array($this,'wplms_pmp_pro_connect'),10,2);
      
      //add_action( 'personal_options_update',array($this, 'pmpro_membership_level_profile_fields_update', ),1);
      //add_action( 'edit_user_profile_update', array($this,'pmpro_membership_level_profile_fields_update' ),1);

      add_action('pmpro_before_change_membership_level',array($this,'record_previous_subscriptions'),10,4);
      add_action('pmpro_after_change_membership_level',array($this,'wplms_pmprostop_previous_courses'),10,3);
      add_filter('wplms_private_course_button',array($this,'wplms_check_pmpro_button'));
      add_filter('wplms_private_course_button_label',array($this,'wplms_check_pmpro_course_button'));
    }

    function wplms_pmp_pro_connect($course_id,$user_id){


        $membership_ids=vibe_sanitize(get_post_meta($course_id,'vibe_pmpro_membership',false));
         $flag = apply_filters('wplms_pmpro_connect_allow_course_subscription',1,$course_id,$user_id,$membership_ids);

        if(pmpro_hasMembershipLevel($membership_ids,$user_id) && isset($membership_ids) && count($membership_ids) >= 1 && $flag){
          
            $coursetaken=get_user_meta($user_id,$course_id,true);
            if(!isset($coursetaken) || $coursetaken ==''){

                $duration=get_post_meta($course_id,'vibe_duration',true);
                $course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400,$course_id);
                $new_duration = time()+$course_duration_parameter*$duration;
                $new_duration = apply_filters('wplms_pmpro_course_check',$new_duration);
                if(function_exists('bp_course_add_user_to_course')){
                  bp_course_add_user_to_course($user_id,$course_id,$new_duration);
                }

            }
         }
      
    }

    /*
    * RECORD PREVIOUS COURSES CONNECTED TO THE OLD MEMBERSHIP
    */

    function pmpro_membership_level_profile_fields_update(){

      global $wpdb, $current_user, $user_ID;
      wp_get_current_user();
      
      if(!empty($_REQUEST['user_id'])) 
        $user_ID = $_REQUEST['user_id'];

      $membership_level_capability = apply_filters("pmpro_edit_member_capability", "manage_options");
      if(!current_user_can($membership_level_capability))
        return false;
      $level=pmpro_getMembershipLevelsForUser($user_ID);
      //level change
        if(!empty($level)){
          $this->wplms_track_course_membership($level, $user_ID);
        }

    }
    function wplms_track_course_membership($levels, $user_id){
      
        global $wpdb;
        if($levels){
          foreach($levels as $old_level) {
            $membership_courses=$wpdb->get_results($wpdb->prepare("SELECT post_id as course_id  FROM {$wpdb->postmeta} WHERE meta_key ='%s' AND meta_value LIKE '%s'",'vibe_pmpro_membership','%"'.$old_level->ID.'"%'));

            if(!empty($membership_courses)){
              foreach($membership_courses as $membership_course){
                $courses[]=$membership_course->course_id;
              }
            }
          }
        }
        if(is_array($courses))
          $courses= array_unique( $courses);
        
        $this->user_courses = $courses;
      
    } 
 
    /*
    * REMOVE COURSES WHICH ARE NOT IN THE NEW MEMBERSHIP
    */
   
    function record_previous_subscriptions($level_id, $user_id,$levels,$cancel_level){
      //$levels = pmpro_getMembershipLevelsForUser($user_id);
      $this->wplms_track_course_membership($levels, $user_id);
    }

    function wplms_pmprostop_previous_courses($level_id, $user_id,$cancel_level){
      global $pmpro_pages, $wpdb;
      $courses = array();
      
      if(!empty($level_id)){
        
        $membership_courses=$wpdb->get_results($wpdb->prepare("SELECT post_id as course_id  FROM {$wpdb->postmeta} WHERE meta_key ='%s' AND meta_value LIKE '%s'",'vibe_pmpro_membership','%"'.$level_id.'"%'));
        if(!empty($membership_courses)){
          foreach($membership_courses as $membership_course){
            $courses[]=$membership_course->course_id;
          }
        }

        if(!empty($courses) && !empty($this->user_courses)){
          foreach($courses as $course_id){
            if(!in_array($course_id,$this->user_courses)){
              bp_course_add_user_to_course($user_id,$course_id);
            }
          }
        }
      }

      if(!empty($this->user_courses)){
        foreach($this->user_courses as $k => $course_id){
          if(!in_array($course_id,$courses)){
           bp_course_remove_user_from_course($user_id,$course_id);
          }
        }
      }

      return;
    }



    function wplms_check_pmpro_button($link){
        $course_id = get_the_ID();
        if ( in_array( 'paid-memberships-pro/paid-memberships-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {
          $membership_ids=vibe_sanitize(get_post_meta($course_id,'vibe_pmpro_membership',false));

          if(isset($membership_ids) && is_array($membership_ids) && count($membership_ids)){
             $pmpro_levels_page_id = get_option('pmpro_levels_page_id');
             $link = get_permalink($pmpro_levels_page_id);
          }
        }
        return $link;
    }
    function wplms_check_pmpro_course_button($label){
      $course_id = get_the_ID();
      if ( in_array( 'paid-memberships-pro/paid-memberships-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {
        $membership_ids=vibe_sanitize(get_post_meta($course_id,'vibe_pmpro_membership',false));
        if(isset($membership_ids) && is_array($membership_ids) && count($membership_ids)){
          $label = apply_filters('wplms_take_this_course_button_label',__('TAKE THIS COURSE','vibe'),$course_id);
        }
      }
      return $label;
    }

}

WPLMS_PMPRO_Connect::init();


/*===== PMPRO END ====*/