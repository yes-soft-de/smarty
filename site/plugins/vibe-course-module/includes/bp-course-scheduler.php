<?php

/*
*	BP COURSE SCHEDULER
*   OBJECTIVE : SCHEDULER EMAILS OR CUSTOM TASKS
*   USE CASES :
*   SEND EMAIL TO STUDENT BEFORE HER COURSE EXPIRES
*	SEND EMAIL TO STUDENT WHEN HER UNIT IS AVAILABLE in DRIP FEED COURSE
*	SEND EMAIL FOR EVENT ** to be used with Advanced Events plugin
*	SEND A WEEKLY COURSE PROGRESS REPORT TO INSTRUCTOR, PROCESS COURSE REPORTS AND EMAIL ** ADVANCED SCHEDULER
*/

 if ( ! defined( 'ABSPATH' ) ) exit;

class bp_course_scheduler{

    public static $instance;
    var $schedule;

    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new bp_course_scheduler();
        return self::$instance;
    }

    private function __construct(){

		$this->get();
		//Schedule Course expiry email
		add_action('wplms_bulk_action',array($this,'reset_course_expire_schedule'),10,3);
		add_action('wplms_course_subscribed',array($this,'schedule_expire_mail'),10,3);
      	add_action('wplms_send_course_expiry_mail',array($this,'wplms_send_course_expiry_mail'),10,3);

		//Schedule Drip feed email
		add_action('wplms_start_unit',array($this,'schedule_drip_mail'),10,5);
		add_action('wplms_send_drip_mail',array($this,'wplms_send_drip_mail'),10,3);

      	//Schedule User's inactivity email
      	add_action('init',array($this,'wplms_schedule_user_inactivity_mail'));
      	add_action('wplms_user_inactivity_mail',array($this,'wplms_user_inactivity_mail'));

      	//Schedule Course Review email
      	add_action('init',array($this,'wplms_schedule_course_review_mail'));
      	add_action('wplms_course_review_mail',array($this,'wplms_course_review_mail'));
	}
   
	function get(){

		if(class_exists('WPLMS_tips')){
	        $wplms_settings = WPLMS_tips::init();
	        $settings = $wplms_settings->lms_settings;
      	}else{
	        $settings = get_option('lms_settings');  
      	}

		if(!empty($settings['schedule']))
			$this->schedule = $settings['schedule'];
	}

	function reset_course_expire_schedule($action,$course_id,$members){ 
		if($action != 'extend_course_subscription')
			return;

		if(isset($this->schedule) && is_array($this->schedule)){
			if($this->schedule['expire'] === 'yes'){
				$expire_schedule = $this->schedule['expire_schedule'];
				$expire_schedule = apply_filters('wplms_course_expire_schedule_duration',$expire_schedule,$course_id,$members);
				foreach($members as $user_id){
					$group_id = get_post_meta($course_id,'vibe_group',true);
					if(!is_numeric($group_id))
						$group_id ='';

					$args = array($course_id, $user_id,$group_id);
					wp_clear_scheduled_hook('wplms_send_course_expiry_mail',array($course_id, $user_id,$group_id));

					$timestamp = get_user_meta($user_id,$course_id,true);
					$time = $timestamp - $expire_schedule*3600;
					if($time > time() && $time < 2147483648){
					
					if(!wp_next_scheduled('wplms_send_course_expiry_mail',$args))
						wp_schedule_single_event($time,'wplms_send_course_expiry_mail',$args);
					}
				}
			}
		}
	}

	function schedule_expire_mail($course_id, $user_id,$group_id = null){

		if(empty($group_id))
			$group_id = '';
		if(isset($this->schedule) && is_array($this->schedule)){
			if($this->schedule['expire'] === 'yes'){

				$expire_schedule = $this->schedule['expire_schedule'];
				$expire_schedule = apply_filters('wplms_course_expire_schedule_duration',$expire_schedule,$course_id,array( $user_id));
				$timestamp = get_user_meta($user_id,$course_id,true);
				if(!empty($timestamp)){
					$timestamp +=  get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ;
					$time = $timestamp - $expire_schedule*3600;
					if($time > current_time('timestamp') && $time < 2147483648){
						$args = array($course_id, $user_id,$group_id);
						wp_clear_scheduled_hook('wplms_send_course_expiry_mail',array($course_id, $user_id,$group_id));
						if(!wp_next_scheduled('wplms_send_course_expiry_mail',$args))
							wp_schedule_single_event($time,'wplms_send_course_expiry_mail',$args);
					}
				}
			}
		}
	}

	function wplms_send_course_expiry_mail($course_id, $user_id,$group_id = null){

		if(empty($group_id))
			$group_id = '';

	  	if(isset($this->schedule) && is_array($this->schedule)){
	      if($this->schedule['expire'] === 'yes'){

	        $subject = $this->schedule['expire_subject'];
	        $message = $this->schedule['expire_message'];
	        $course_title = get_the_title($course_id);
	        $username = bp_core_get_user_displayname($user_id);

	        $subject = str_replace('{{course}}',$course_title ,$subject);
	        $message = str_replace('{{course}}',$course_title ,$message);
	        $subject = str_replace('{{user}}',$username,$subject);
	        $message = str_replace('{{user}}',$username,$message);
	        $user = get_user_by('id',$user_id);    

        	bp_course_wp_mail($user->user_email,$subject,$message,array('action'=>'wplms_expire_mail','tokens'=>array('course.name'=>$course_title,'course.titlelink'=>'<a href="'.get_permalink($course_id).'">'.$course_title.'</a>')));
	        wp_clear_scheduled_hook('wplms_send_course_expiry_mail',array($course_id,$user_id,$group_id));
	      }
	    }
	}

	function schedule_drip_mail($current_unit_id,$course_id,$user_id,$next_unit_id,$timestamp){

		if(isset($this->schedule) && is_array($this->schedule)){
			if($this->schedule['drip'] === 'yes'){

				$drip_schedule = $this->schedule['drip_schedule'];
				$drip_schedule = $timestamp - $drip_schedule*3600;
				$args = array($next_unit_id,$course_id,$user_id);

				wp_clear_scheduled_hook('wplms_send_drip_mail',array($current_unit_id,$course_id,$user_id));
				if(!wp_next_scheduled('wplms_send_drip_mail',$args))
					wp_schedule_single_event($drip_schedule,'wplms_send_drip_mail',$args);
			}
		}
	}

	function wplms_send_drip_mail($unit_id,$course_id,$user_id){

		    if(isset($this->schedule) && is_array($this->schedule)){
		      if($this->schedule['drip'] === 'yes'){

		        $subject = $this->schedule['drip_subject'];
		        $message = $this->schedule['drip_message'];
		        $unit_title = get_the_title($unit_id);
		        $course_title = get_the_title($course_id);
		        $username = bp_core_get_user_displayname($user_id);

		    	$subject = str_replace('{{unit}}',$unit_title,$subject);
		        $message = str_replace('{{unit}}',$unit_title,$message);
		        $subject = str_replace('{{course}}',$course_title,$subject);
		        $message = str_replace('{{course}}',$course_title,$message);
		        $subject = str_replace('{{user}}',$username,$subject);
		        $message = str_replace('{{user}}',$username,$message);	

		        $user = get_user_by('id',$user_id);       
		        if(bp_course_is_member($course_id,$user_id)){
		        	bp_course_wp_mail($user->user_email,$subject,$message,array('action'=>'wplms_drip_mail','tokens'=> array('unit.name'=>$unit_title,'course.name'=>$course_title,'student.userlink'=>$username,'course.titlelink'=>'<a href="'.get_permalink($course_id).'">'.$course_title.'</a>')));
		        } 

		        wp_clear_scheduled_hook('wplms_send_drip_mail',array($unit_id,$course_id,$user_id));
		    }
	    }
	}

	function wplms_schedule_user_inactivity_mail(){

		if(!isset($this->schedule) || !is_array($this->schedule) || $this->schedule['inactive'] != 'yes'){
	    	return;
	    }

     	$seconds_in_day = 86400;
	    if(!wp_next_scheduled( 'wplms_user_inactivity_mail' )){
    		wp_schedule_single_event(time()+$seconds_in_day,'wplms_user_inactivity_mail');
    	}

	}

	function wplms_user_inactivity_mail(){

		if(!isset($this->schedule) || !is_array($this->schedule) || $this->schedule['inactive'] != 'yes'){
	    	return;
	    }

	    $seconds_in_day = 86400;

	    global $wpdb;
	    $inactivity_schedule = $this->schedule['inactivity_schedule'];
	    $current_time = time();
	    $time = $current_time - ($inactivity_schedule*$seconds_in_day);
	    $date = date('Y-m-d H:i:s', $time);
		$site_url = home_url();

		$daily_mails = $this->schedule['inactivity_days']; //number of days, mail is to be sent daily
		$weekly_mails = $this->schedule['inactivity_weeks']; // number of weeks, mail is to be sent weekly
		$monthly_mails = $this->schedule['inactivity_months'];//number of months, mail is to be sent monthly

		$users = $wpdb->get_results("
			SELECT u.ID as id, u.user_email as email, m.meta_value as last_activity, m3.meta_value as sent_email_count
			FROM {$wpdb->users} as u
			LEFT JOIN {$wpdb->usermeta} as m
			ON m.user_id = u.ID AND m.meta_key = 'last_activity'
			LEFT JOIN {$wpdb->usermeta} as m2
			ON  m2.user_id = u.ID AND m2.meta_key = 'inactive'
			LEFT JOIN {$wpdb->usermeta} as m3
			ON  m3.user_id = u.ID AND m3.meta_key = 'inactivity_emails'
			WHERE m.meta_value <= '$date'
			AND (m2.meta_key IS NULL OR m2.meta_value = 'yes')
			AND (m3.meta_key IS NULL OR m3.meta_value < ".($daily_mails+$weekly_mails+$monthly_mails).")
			");

		if(!empty($users)){
	        $bpargs = array(
	            'tokens' => array(
	            				'user.inactive' => $inactivity_schedule,
	            				'site.name' => $site_url
	            			),
	        );

	        foreach ($users as $user){

				if(empty($user->sent_email_count)){
					$user->sent_email_count = 0;
				}

				$last_active_timestamp = strtotime($user->last_activity);
				$send_email = false;

				if(!empty($daily_mails) && ($user->sent_email_count < $daily_mails)){
					
					$daily_sent_mails = $user->sent_email_count;
					// $time increments daily

					for($daily_mail_count=$daily_mails; $daily_mail_count>=(1+$daily_sent_mails); $daily_mail_count--){
						if( ($daily_mail_count*$seconds_in_day) <= ($time - $last_active_timestamp)){
							$send_email = true;
							break;
						}
					}

				}else if(!empty($daily_mails) && !empty($weekly_mails) && ($user->sent_email_count >= $daily_mails && $user->sent_email_count < ($daily_mails+$weekly_mails))){

					$weekly_sent_mails = $user->sent_email_count-$daily_mails;
					// $time increments daily

					for($weekly_mail_count=$weekly_mails;$weekly_mail_count>=(1+$weekly_sent_mails);$weekly_mail_count--){
						if( ($weekly_mail_count*7*$seconds_in_day) <= ($time - $last_active_timestamp)){
							$send_email = true;
							break;
						}
					}

				}else if(!empty($daily_mails) && !empty($weekly_mails) && !empty($monthly_mails) && ($user->sent_email_count >= ($daily_mails+$weekly_mails) && $user->sent_email_count < ($daily_mails+$weekly_mails+$monthly_mails))){

					$monthly_sent_mails = $user->sent_email_count-$daily_mails-$weekly_mails;
					// $time increments daily

					for($monthly_mail_count=$monthly_mails;$monthly_mail_count>=(1+$monthly_sent_mails);$monthly_mail_count--){
						if( ($monthly_mail_count*30*$seconds_in_day) <= ($time - $last_active_timestamp)){
							$send_email = true;
							break;
						}
					}
				}

				if($send_email){
					bp_send_email( 'wplms_inactive_user',$user->email, $bpargs );
					$user->sent_email_count++;
					update_user_meta($user->id,'inactivity_emails',$user->sent_email_count);
				}
	        }
		}

		wp_clear_scheduled_hook('wplms_user_inactivity_mail');
	}

	function wplms_schedule_course_review_mail(){

		if(!isset($this->schedule) || !is_array($this->schedule) || $this->schedule['review_course'] != 'yes'){
	    	return;
	    }

     	$seconds_in_day = 86400;
	    if(!wp_next_scheduled( 'wplms_course_review_mail' )){
    		wp_schedule_single_event(time()+$seconds_in_day,'wplms_course_review_mail');
    	}

	}

	function wplms_course_review_mail(){

		if(!isset($this->schedule) || !is_array($this->schedule) || $this->schedule['review_course'] != 'yes'){
	    	return;
	    }

	    global $wpdb,$bp;
	    $seconds_in_day = 86400;
	    $review_schedule = $this->schedule['review_course_schedule'];
	    $current_time = time();
	    $time = $current_time - ($review_schedule*$seconds_in_day);
	    $date = date('Y-m-d H:i:s', $time);

	    $users = $wpdb->get_results( $wpdb->prepare("
	    	SELECT a.user_id as user_id, a.item_id as course_id, u.user_email as user_email 
	    	FROM {$bp->activity->table_name} as a
	    	LEFT JOIN {$wpdb->users} as u
	    	ON a.user_id = u.ID
	    	LEFT JOIN {$wpdb->usermeta} as m
	    	ON a.user_id = m.user_id AND m.meta_key = CONCAT('review_course_email',a.item_id)
	    	LEFT JOIN {$bp->activity->table_name} as a2
	    	ON a.user_id = a2.user_id AND a.item_id = a2.item_id AND a2.type = %s
	    	WHERE a.type = %s 
	    	AND a.date_recorded < %s
	    	AND (m.meta_key IS NULL OR m.meta_value != 1)
	    	AND a2.type IS NULL
	    	GROUP BY a.user_id",'review_course','course_evaluated',$date) );

	    if(!empty($users)){
	    	foreach ($users as $user) {
	    		$course_link = '<a href="'.get_permalink($user->course_id).'">'.get_the_title($user->course_id).'</a>';
	    		$course_title = get_the_title($user->course_id);
	    		$bpargs = array(
	            	'tokens' => array('course.link' => $course_link,'course.name' => $course_title),
	        	);
	        	bp_send_email( 'wplms_course_review_email',$user->user_email, $bpargs );
	        	update_user_meta($user->user_id,'review_course_email'.$user->course_id,1);
	    	}
	    }

		wp_clear_scheduled_hook('wplms_course_review_mail');
	}

}//End Of Class

bp_course_scheduler::init();
