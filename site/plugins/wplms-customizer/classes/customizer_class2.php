<?php

if(!class_exists('WPLMS_Customizer_Plugin_Class'))
{   
    class WPLMS_Customizer_Plugin_Class  // We'll use this just to avoid function name conflicts 
    {
            
        public function __construct(){   /*
            // JON ADDITION 1
			add_filter('wplms_course_nav_menu',array($this,'wplms_course_nav_menu')); 
			 
			// JON ADDITION 2
			add_action('wp_enqueue_scripts',array($this,'add_gaq_in_header'));
			
			// JON ADDITION 3
			add_action('wplms_after_every_unit',array($this,'add_gaq_after_unit'),1);  
			
			// JON ADDITION 4
    		remove_filter('course_friendly_time','convert_unlimited_time');
    		add_filter('course_friendly_time',array($this,'convert_unlimited_time'),1,2);  
    
    		// JON ADDITION 5
    		add_filter('wplms_display_course_member',array($this,'wplms_display_course_member'),1,2);
    		add_filter('wplms_display_course_member_avatar',array($this,'wplms_display_course_member_avatar'),1,2);  

        	// JON ADDITION 6
			add_filter('wplms_course_stats_list',array($this,'add_custom_course_stat'));
			add_action('wplms_course_stats_process',array($this,'process_custom_course_stat'),10,6);  
			
			// JON ADDITION 6b
      add_filter('wplms_course_stats_list',array($this,'add_custom_course_stat1'));
      add_action('wplms_course_stats_process',array($this,'process_custom_course_stat1'),10,6);
			
   			// JON ADDITION 7
 			add_filter('wplms_curriculum_time_filter',array($this,'wplms_custom_curriculum_time_filter_remove_hours'),10,2);  

 */
            
        } // END public function __construct
        public function activate(){
        }
        public function deactivate(){
        	// ADD Custom Code which you want to run when the plugin is de-activated	
        }
        
        // ADD custom Code in clas
        
        	// JON ADDITION 1
        	function wplms_course_nav_menu($menu_array){
                   unset($menu_array['members']);
                   unset($menu_array['events']);
                   return $menu_array;
               }
        	// JON ADDITION 2

function add_gaq_in_header(){
  echo '<script type="text/javascript">var _gaq = _gaq || [];
  _gaq.push(["_setAccount", "UA-59135810-1"]);
  _gaq.push(["_trackPageview"]);</script>';
} 
        // JON ADDITION 3
function add_gaq_after_unit($unit_id){
  
  echo '<script type="text/javascript">
  _gaq.push(["_trackPageview", "'.get_permalink($unit_id).'"]);</script>';
} 

               // JON ADDITION 4
               function convert_unlimited_time($time_html,$time){
                  if(intval($time/86400) > 9998) // the original time is in seconds, so we convert it into days and check
                    return 'Unlimited';
               
               return $time_html;
               }  
               // JON ADDITION 5
               function wplms_display_course_member($member_title,$course_id=NULL){
                           $member_title='';
                           return $member_title;
                       }
                       function wplms_display_course_member_avatar($member_avatar,$course_id=NULL){
                           $member_avatar='';
                           return $member_avatar;
                       }  
                       
                        // JON ADDITION 6a
                     function add_custom_course_stat($list){
                                 $list['user_email']= 'User Email';
                                 return $list;
                             }
                     
                     function process_custom_course_stat(&$csv_title, &$csv,&$i,&$course_id,&$user_id,&$field){
                     if($field != 'user_email') // Ensures the field was checked.
                      return;
                        // Check if Student has got a certificate in the course..
                                 $title=__('Email','vibe'); 
                                 if(!in_array($title,$csv_title))
                                 $csv_title[$i]=$title;
                                 $user_info=get_userdata( $user_id ); 
                                 $csv[$i][]= $user_info->user_email;
                     
                     }   
                       // JON ADDITION 6b
function add_custom_course_stat1($list){
         $list['user_field']= 'Institution Code';
         return $list;
        }
          
        function process_custom_course_stat1(&$csv_title, &$csv,&$i,&$course_id,&$user_id,&$field){
          if($field != 'user_field') // Ensures the field was checked.
             return;
          $title=__('Institution Code','vibe');
          if(!in_array($title,$csv_title))
            $csv_title[$i]=$title;
          $ifield = 'Institution Code';  
          if(bp_is_active('xprofile'))
           $field_val= bp_get_profile_field_data(array( 'field' => $ifield,'user_id'=>$user_id) );
                       
          if(isset($field_val) && $field_val)
            $csv[$i][]= $field_val;
          else
            $csv[$i][]= 'N.A';
          
         }                  
                       
                  // JON ADDITION 7
               function wplms_custom_curriculum_time_filter_remove_hours($html,$minutes){
          
            if($minutes < 60){
            $html = '<span><i class="icon-clock"></i>'.$minutes.' '.__('minutes','vibe').'</span>';
            }
   

        return $html;
        }  
    } // END class WPLMS_Customizer_Class
} // END if(!class_exists('WPLMS_Customizer_Class'))

?>