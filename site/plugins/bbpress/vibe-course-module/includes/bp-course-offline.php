<?php
/**
 * OFFLINE Courses for Course Module
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Vibe Course Module
 * @version     2.0
 */

 if ( ! defined( 'ABSPATH' ) ) exit;

class BP_Course_Offline{

    public static $instance;
    var $schedule;

    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new BP_Course_Offline();
        return self::$instance;
    }

    private function __construct(){
    	
    	add_filter('wplms_course_details_widget',array($this,'display_course_progress'));
    	add_filter('wplms_course_progress_display',array($this,'auto_progress'),10,2);
    	//Finished course button
    	add_filter('wplms_start_course_button',array($this,'course_button'),10,2);
    	add_filter('wplms_continue_course_button',array($this,'course_button'),10,2);
    	add_filter('wplms_evaluation_course_button',array($this,'course_button'),10,2);
    	add_filter('wplms_finished_course_link',array($this,'course_button'),10,2);
        add_filter('wplms_expired_course_button',array($this,'course_button'),10,2);

        add_filter('course_friendly_time',array($this,'display_time'),10,3);
    	
    }

    function display_course_progress($course_details){
    	global $post;
    	if($post->post_type == 'course'){

    		$vibe_course_progress = get_post_meta($post->ID,'vibe_course_progress',true);
    		if(vibe_validate($vibe_course_progress)){ 

               if(is_user_logged_in() && bp_course_is_member($post->ID,get_current_user_id())){ 
    				$progress = apply_filters('wplms_course_progress_display','',$post->ID);
    				
    				if(is_numeric($progress)){
    					self::display_progress($progress);
    					return $course_details;
    				}
    				if(is_user_logged_in()){
    					$progress = get_user_meta(get_current_user_id(),'progress'.$post->ID,true);
    					if(!empty($progress)){
    						self::display_progress($progress);
    					}
        			}
                }
    		}
    	}

    	return $course_details;
    }

    function auto_progress($progress,$course_id){
    	$user_id = get_current_user_id();
    	$vibe_course_auto_progress = get_post_meta($course_id,'vibe_course_auto_progress',true);
		if(vibe_validate($vibe_course_auto_progress)){
            $course_duration = bp_course_get_course_duration($course_id,$user_id);

            $activity = bp_activity_get( array(
                    'max'    => 1,
                    'filter' => array( 
                                    'user_id' => $user_id,
                                    'item_id'=>$course_id,
                                    'component'=>'course',
                                    'action'=>'subscribe_course' 
                                )
                ));

            if(!empty($activity)){
                $user_start_datetime = $activity['activities'][0]->date_recorded;
                $user_start_datetime = strtotime($user_start_datetime);
             
            }

			$course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400,$course_id);

			if(bp_course_is_member($course_id,$user_id)){
				$end_time = apply_filters('bp_course_auto_progress_user_start_time',bp_course_get_user_expiry_time($user_id,$course_id));
				
                if(empty($end_time)){
					$end_time = time();
				}
			}else{
				$start_date = get_post_meta($course_id,'vibe_start_date',true);

				if(empty($start_date)){
					$start_date = get_post_field('post_date',$course_id);
					$start_time = strtotime($start_date);
				}else{
					$start_time = strtotime($start_date);
				}
			}

		}else{
			if(is_user_logged_in()){
				$user_id = get_current_user_id();
				if(bp_course_is_member($course_id,$user_id)){ 
					$progress = bp_course_get_user_progress($user_id,$course_id);
					if(!empty($progress))
						return $progress;
                    else
                        return 0;
				}
			}
		}
        if(isset($end_time)){ 
            if($end_time < time()){
                $progress = 100;
            }else{
                $elapsed = (time() - $user_start_datetime )/$course_duration_parameter;

                $total = get_post_meta($course_id,'vibe_duration',true);
                $progress = round(($elapsed/$total),2)*100;
            }
        }else if(isset($start_time)){
			$elapsed = (time() - $start_time)/$course_duration_parameter;
			$total = get_post_meta($course_id,'vibe_duration',true);
			$progress = round(($elapsed/$total),2)*100;
		}
		
		if($progress < 0)
			$progress = 0;
		if($progress > 100)
			$progress = 100;

		return $progress;
    }

    function display_progress($progress = 0){
        $progress = intval($progress);
    	?>
    	<div class="course_front_progressbar">
    		<div class="progress">
        		<div class="bar stretchRight" style="width: 0%;" data-percentage="<?php echo $progress; ?>"></div>
        	</div>
        	<span><?php echo $progress.'%'; ?></span>
       	</div>
        <script>
            jQuery(document).ready(function($){
                $('.course_front_progressbar .bar').css('width', $('.course_front_progressbar .bar').attr('data-percentage')+'%');
            });
        </script>
    	<?php
    }

    function course_button($button,$course_id){
    	$button_access = get_post_meta($course_id,'vibe_course_button',true);
    	if(vibe_validate($button_access)){
    		return '';
    	}
    	return $button;
    }

    function display_time($time,$seconds,$course_id){
        $button_access = get_post_meta($course_id,'vibe_course_button',true);
        if(vibe_validate($button_access)){
            if(empty($seconds)){
                $time = __('COURSE FINISHED','vibe');
            }
        }
        return $time;
    }
}

BP_Course_Offline::init();