<?php
/********************************************************************************
 * Activity & Notification Functions
 *
 * These functions handle the recording, deleting and formatting of activity and
 * notifications for the user and for this specific component.
 */
/**
 * bp_course_screen_notification_settings()
 *
 * Adds notification settings for the component, so that a user can turn off email
 * notifications set on specific component actions.
 */

 if ( ! defined( 'ABSPATH' ) ) exit;
class bp_course_notifications{

	/*
	Store all the titles in the calls
	*/
	public $info = array();
	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new bp_course_notifications();
        return self::$instance;
    }

	private function __construct(){

		$this->get();
		$this->run();
		add_action( 'bp_notification_settings', array($this,'bp_course_screen_notification_settings' ));
		add_action( 'bp_notification_settings', array($this,'bp_scheduled_email_notification_settings' ));
	}

	function get(){
		
		if(class_exists('WPLMS_tips')){
	        $wplms_settings = WPLMS_tips::init();
	        $lms_settings = $wplms_settings->lms_settings;
      	}else{
	        $lms_settings = get_option('lms_settings');  
      	}
		
		if(isset($lms_settings) && isset($lms_settings['touch'])){
			if(class_exists('lms_settings')){
				$this->settings = lms_settings::get_touch_points();
			}
		}
	}

	/*
		NECESSARY FUNCTIONS TO AVOID DUPLICATE FUNCTION CALLS
	*/

	function get_the_title($id){
		if(!empty($this->info[$id]['post_title']))
			return $this->info[$id]['post_title'];

		$this->info[$id]['post_title'] = get_the_title($id);
		return $this->info[$id]['post_title'];
	}	

	function get_post_field($label,$id){
		if(!empty($this->info[$id][$label]))
			return $this->info[$id][$label];

		$this->info[$id][$label] = get_post_field($label, $id);
		return $this->info[$id][$label];	
	}

	function get_permalink($id){
		if(!empty($this->info[$id]['permalink']))
			return $this->info[$id]['permalink'];

		$this->info[$id]['permalink'] = get_permalink($id);
		return $this->info[$id]['permalink'];	
	}

	function bp_core_get_userlink($id){
		if(!empty($this->info[$id]['profile']))
			return $this->info[$id]['profile'];

		$this->info[$id]['profile'] = bp_core_get_userlink($id);
		return $this->info[$id]['profile'];
	}

	function bp_core_get_user_displayname($id){
		if(!empty($this->info[$id]['name']))
			return $this->info[$id]['name'];

		$this->info[$id]['name'] = bp_core_get_user_displayname($id);
		return $this->info[$id]['name'];
	}

	function get_instructors($course_id){

		$instructor_ids = apply_filters('wplms_course_instructors',$this->get_post_field('post_author', $course_id),$course_id);	
		if(!is_array($instructor_ids)){
			$instructor_ids = array($instructor_ids);
		}
		return $instructor_ids;
	}
	function instructor_emails($course_id,$key){
		$instructor_ids =apply_filters('wplms_course_instructors',$this->get_post_field('post_author', $course_id),$course_id);	
		if(!is_array($instructor_ids)){
			$instructor_ids = array($instructor_ids);
		}
		$to = array();

		foreach($instructor_ids as $instructor_id){
			$enable = get_user_meta($instructor_id,'instructor_'.$key,true);
			if($enable !== 'no'){
				$user = get_user_by( 'id', $instructor_id);
				$to[] = $user->user_email;
			}
		}
		return $to;
	}

	function get_admins(){
		$admin_ids=array();
		if(empty($this->admin_ids)){
			$this->admin_ids = array();
		 	$user_query = new WP_User_Query( array( 'role' => 'Administrator' ,'fields' => array('ID','user_email')) );
			foreach( $user_query->results as $user){
				$admin_ids[] = array('ID' => $user->ID,'email'=> $user->user_email);
			}
			$this->admin_ids = $admin_ids;
		}

		return $this->admin_ids;
	}
	/*
		EXECUTE TOUCH POINTS 
	*/
	function run(){
		if(empty($this->lms_settings))
			$this->lms_settings = get_option('lms_settings');

		if(isset($this->lms_settings) && isset($this->lms_settings['touch']) && is_array($this->lms_settings['touch'])){

			foreach($this->lms_settings['touch'] as $key => $value){
				$hook = $this->settings[$key]['hook'];

				if(!empty($value['student']['message'])){
					$student_fx = 'student_message_'.$key;
					if(function_exists($student_fx)){
						add_action($hook,$student_fx,10,$this->settings[$key]['params']);
					}else{
						add_action($hook,array($this,$student_fx),10,$this->settings[$key]['params']);	
					}
				}

				if(!empty($value['student']['notification'])){
					$student_fx = 'student_notification_'.$key;
					if(function_exists($student_fx)){
						add_action($hook,$student_fx,9,$this->settings[$key]['params']);
					}else{
						add_action($hook,array($this,$student_fx),9,$this->settings[$key]['params']);	
					}	
				}

				if(!empty($value['student']['email'])){
					$student_fx = 'student_email_'.$key;
					if(function_exists($student_fx)){
						add_action($hook,$student_fx,10,$this->settings[$key]['params']);
					}else{
						add_action($hook,array($this,$student_fx),10,$this->settings[$key]['params']);	
					}
				}

				if(!empty($value['instructor']['message'])){
					$instructor_fx = 'instructor_message_'.$key;
					if(function_exists($instructor_fx)){
						add_action($hook,$instructor_fx,15,$this->settings[$key]['params']);
					}else{
						add_action($hook,array($this,$instructor_fx),15,$this->settings[$key]['params']);	
					}
				}

				if(!empty($value['instructor']['notification'])){
					$instructor_fx = 'instructor_notification_'.$key;
					if(function_exists($instructor_fx)){
						add_action($hook,$instructor_fx,15,$this->settings[$key]['params']);
					}else{
						add_action($hook,array($this,$instructor_fx),15,$this->settings[$key]['params']);	
					}
				}

				if(!empty($value['instructor']['email'])){
					$instructor_fx = 'instructor_email_'.$key;
					if(function_exists($instructor_fx)){
						add_action($hook,$instructor_fx,15,$this->settings[$key]['params']);
					}else{
						add_action($hook,array($this,$instructor_fx),15,$this->settings[$key]['params']);	
					}
				}

				if(!empty($value['admin']['message'])){
					$admin_fx = 'admin_message_'.$key;
					if(function_exists($admin_fx)){
						add_action($hook,$admin_fx,25,$this->settings[$key]['params']);
					}else{
						add_action($hook,array($this,$admin_fx),25,$this->settings[$key]['params']);	
					}
				}

				if(!empty($value['admin']['notification'])){
					$admin_fx = 'admin_notification_'.$key;
					if(function_exists($admin_fx)){
						add_action($hook,$admin_fx,25,$this->settings[$key]['params']);
					}else{
						add_action($hook,array($this,$admin_fx),25,$this->settings[$key]['params']);	
					}
				}

				if(!empty($value['admin']['email'])){
					$admin_fx = 'admin_email_'.$key;
					if(function_exists($admin_fx)){
						add_action($hook,$admin_fx,25,$this->settings[$key]['params']);
					}else{
						add_action($hook,array($this,$admin_fx),25,$this->settings[$key]['params']);	
					}
				}

			}
		}
	}

	function bp_course_screen_notification_settings() {
	global $current_user;

	if(class_exists('WPLMS_tips')){
        $wplms_settings = WPLMS_tips::init();
        $lms_settings = $wplms_settings->lms_settings;
  	}else{
        $lms_settings = get_option('lms_settings');  
  	}

	if(isset($lms_settings) && isset($lms_settings['touch'])){
		?>
		<hr />
		<table class="notification-settings" id="bp-course-notification-settings">
			<thead>
			<tr>
				<th class="icon"></th>
				<th class="title"><?php _e( 'Course', 'vibe' ) ?></th>
				<th class="yes"><?php _e( 'Yes', 'vibe' ) ?></th>
				<th class="no"><?php _e( 'No', 'vibe' )?></th>
			</tr>
			</thead>
			<tbody>
			<?php

				foreach($lms_settings['touch'] as $key => $value){
					if(isset($value['student']) && isset($value['student']['email']) && $value['student']['email']){
					?>
					<tr>
						<td></td>
						<td><?php echo translate($this->settings[$key]['label'],'vibe'); ?></td>
						<td class="yes"><input type="radio" name="notifications[<?php echo 'student_'.$key; ?>]" id="notifications[<?php echo 'student_'.$key; ?>]_yes" value="yes" <?php if ( !get_user_meta( $current_user->id,'student_'.$key, true ) || 'yes' == get_user_meta( $current_user->id, 'student_'.$key, true ) ) { ?>checked="checked" <?php } ?>/><label for="notifications[<?php echo 'student_'.$key; ?>]_yes" class="bp-screen-reader-text"><?php _e( 'Yes, send email', 'vibe' ) ?></label></td>
						<td class="no"><input type="radio" id="notifications[<?php echo 'student_'.$key; ?>]_no" name="notifications[<?php echo 'student_'.$key; ?>]" value="no" <?php if ( 'no' == get_user_meta( $current_user->id,'student_'.$key, true ) ) { ?>checked="checked" <?php } ?>/><label for="notifications[<?php echo 'student_'.$key; ?>]_no"><?php _e( 'No, do not send email', 'vibe' ) ?></label></td>
					</tr>
					<?php
					}
				}
			?>
			</tbody>
			<?php do_action( 'bp_course_notification_settings' ); ?>
		</table>
		<?php
			if(current_user_can('edit_posts')){
		?>
		<hr />
		<table class="notification-settings" id="bp-course-instructor-notification-settings">
			<thead>
			<tr>
				<th class="icon"></th>
				<th class="title"><?php _e( 'Instructor', 'vibe' ) ?></th>
				<th class="yes"><?php _e( 'Yes', 'vibe' ) ?></th>
				<th class="no"><?php _e( 'No', 'vibe' )?></th>
			</tr>
			</thead>
			<tbody>
			<?php

				foreach($lms_settings['touch'] as $key => $value){
					if(isset($value['instructor']) && isset($value['instructor']['email']) && $value['instructor']['email']){
					?>
					<tr>
						<td></td>
						<td><?php echo translate($this->settings[$key]['label'],'vibe'); ?></td>
						<td class="yes"><input type="radio" id="notifications[<?php echo 'instructor_'.$key; ?>]_yes" name="notifications[<?php echo 'instructor_'.$key; ?>]" value="yes" <?php if ( !get_user_meta( $current_user->id,'instructor_'.$key, true ) || 'yes' == get_user_meta( $current_user->id, 'instructor_'.$key, true ) ) { ?>checked="checked" <?php } ?>/><label for="notifications[<?php echo 'instructor_'.$key; ?>]_yes"><?php _e( 'Yes, send email', 'vibe' ) ?></label></td>
						<td class="no"><input type="radio" id="notifications[<?php echo 'instructor_'.$key; ?>]_no" name="notifications[<?php echo 'instructor_'.$key; ?>]" value="no" <?php if ( 'no' == get_user_meta( $current_user->id, 'instructor_'.$key, true ) ) { ?>checked="checked" <?php } ?>/><label for="notifications[<?php echo 'instructor_'.$key; ?>]_no"><?php _e( 'No, do not send email', 'vibe' ) ?></label></td>
					</tr>
					<?php
					}
				}
			?>
			</tbody>
			<?php do_action( 'bp_course_instructor_notification_settings' ); ?>
		</table>
	<?php
			}
		}
	}

	function bp_scheduled_email_notification_settings(){

		if(class_exists('WPLMS_tips')){
	        $wplms_settings = WPLMS_tips::init();
	        $settings = $wplms_settings->lms_settings;
      	}else{
	        $settings = get_option('lms_settings');  
      	}
		if(!empty($settings['schedule'])){
			$schedule = $settings['schedule'];
		}
		
		$labels = array(
			'drip'=>_x('Schedule drip feed email','Drip feed email label in profile email settings','vibe'),
			'expiry'=>_x('Schedule course expiry email','Course expiry email label in profile email settings','vibe'),
			'inactive'=>_x('Schedule user inactivity email','User inactivity email label in profile email settings','vibe'),
			);

		if(!isset($schedule) || !is_array($schedule)){
	    	return;
	    }

	    if($schedule['drip'] == 'no' && $schedule['expire'] == 'no' & $schedule['inactive'] == 'no'){
	    	return;
	    }

	    ?>
		<table class="notification-settings" id="bp-schedule-notification-settings">
			<thead>
			<tr>
				<th class="icon"></th>
				<th class="title"><?php _e( 'Schedule Emails', 'vibe' ) ?></th>
				<th class="yes"><?php _e( 'Yes', 'vibe' ) ?></th>
				<th class="no"><?php _e( 'No', 'vibe' )?></th>
			</tr>
			</thead>
			<tbody>
			<?php
				foreach($schedule as $key => $value){
					if(isset($key) && isset($value) && $value == 'yes'){
					?>
					<tr>
						<td></td>

						<td><?php if($key == 'drip'){echo $labels['drip'];}elseif($key == 'expire'){echo $labels['expiry'];}elseif($key == 'inactive'){echo $labels['inactive'];} ?></td>

						<td class="yes"><input type="radio" id="notifications[<?php echo $key; ?>]_yes" name="notifications[<?php echo $key; ?>]" value="yes" <?php if ( !get_user_meta( $current_user->id,$key, true ) || 'yes' == get_user_meta( $current_user->id, $key, true ) ) { ?>checked="checked" <?php } ?>/><label for="notifications[<?php echo $key; ?>]_yes"><?php _e( 'Yes, send email', 'vibe' ) ?></label></td>
						
						<td class="no"><input type="radio" id="notifications[<?php echo $key; ?>]_no" name="notifications[<?php echo $key; ?>]" value="no" <?php if ( 'no' == get_user_meta( $current_user->id, $key, true ) ) { ?>checked="checked" <?php } ?>/><label for="notifications[<?php echo $key; ?>]_no"><?php _e( 'No, do not send email', 'vibe' ) ?></label></td>
					</tr>
					<?php
					}
				}
			?>
			</tbody>
		</table>
	    <?php

	}
	
	function student_message_course_announcement($course_id,$student_type,$email,$announcement){
		global $wpdb;
		$sender_id=$this->get_post_field('post_author', $course_id);
		$subject = sprintf(__('Announcement for course %s','vibe'),$this->get_the_title($course_id));
		switch($student_type){
			case 1: // All course students = Any course status
				$users=$wpdb->get_results($wpdb->prepare("
	              	SELECT  user_id
	                FROM {$wpdb->usermeta} 
	                WHERE  meta_key   = %s
	            ",'course_status'.$course_id),ARRAY_A);
			break;
			case 2: // Students pursuing = Course status = 1,2
				$users=$wpdb->get_results($wpdb->prepare("
	              	SELECT  user_id
	                FROM {$wpdb->usermeta} 
	                WHERE  meta_key   = %s
	                AND meta_value IN (%d,%d)
	            ",'course_status'.$course_id,1,2),ARRAY_A);
			break;
			case 3: // Students finished = Course status = 3,4
				$users=$wpdb->get_results($wpdb->prepare("
	              	SELECT  user_id
	                FROM {$wpdb->usermeta} 
	                WHERE  meta_key   = %s
	                AND meta_value IN (%d,%d)
	            ",'course_status'.$course_id,3,4),ARRAY_A);
			break;
		}
		$user_ids = array();
		foreach($users as $user_id){
			$user_ids[]=$user_id['user_id'];
		}
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message') && count($user_ids))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => $subject, 'content' => $announcement,   'recipients' => $user_ids ) );
	}
	function student_notification_course_announcement($course_id,$student_type,$email,$announcement){
		global $wpdb;
		switch($student_type){
			case 1: // All course students = Any course status
				$users=$wpdb->get_results($wpdb->prepare("
	              	SELECT  user_id
	                FROM {$wpdb->usermeta} 
	                WHERE  meta_key   = %s
	            ",'course_status'.$course_id),ARRAY_A);
			break;
			case 2: // Students pursuing = Course status = 1,2
				$users=$wpdb->get_results($wpdb->prepare("
	              	SELECT  user_id
	                FROM {$wpdb->usermeta} 
	                WHERE  meta_key   = %s
	                AND meta_value IN (%d,%d)
	            ",'course_status'.$course_id,1,2),ARRAY_A);
			break;
			case 3: // Students finished = Course status = 3,4
				$users=$wpdb->get_results($wpdb->prepare("
	              	SELECT  user_id
	                FROM {$wpdb->usermeta} 
	                WHERE  meta_key   = %s
	                AND meta_value IN (%d,%d)
	            ",'course_status'.$course_id,3,4),ARRAY_A);
			break;
		}

		foreach($users as $user_id){
			bp_course_add_notification(array(
				'user_id' => $user_id['user_id'],
				'item_id' => $course_id,
				'component_action'  => 'course_announcement'
			));
		}
	}
	function student_email_course_announcement($course_id,$student_type,$email,$announcement){
		global $wpdb;
		switch($student_type){
			case 1: // All course students = Any course status
				$users=$wpdb->get_results($wpdb->prepare("
	              	SELECT  user_id
	                FROM {$wpdb->usermeta} 
	                WHERE  meta_key   = %s
	            ",'course_status'.$course_id),ARRAY_A);
			break;
			case 2: // Students pursuing = Course status = 1,2
				$users=$wpdb->get_results($wpdb->prepare("
	              	SELECT  user_id
	                FROM {$wpdb->usermeta} 
	                WHERE  meta_key   = %s
	                AND meta_value IN (%d,%d)
	            ",'course_status'.$course_id,1,2),ARRAY_A);
			break;
			case 3: // Students finished = Course status = 3,4
				$users=$wpdb->get_results($wpdb->prepare("
	              	SELECT  user_id
	                FROM {$wpdb->usermeta} 
	                WHERE  meta_key   = %s
	                AND meta_value IN (%d,%d)
	            ",'course_status'.$course_id,3,4),ARRAY_A);
			break;
		}
		$to = array();
		foreach($users as $user_id){
			$user = get_user_by('id',$user_id['user_id']);
			$enable = get_user_meta($user_id['user_id'],'student_course_announcement',true);
			if(isset($user->user_email) && $enable !== 'no'){
				//$to[] = $user->user_email;
				$to = $user->user_email;
				$subject = sprintf(__('Announcement for Course %s','vibe'),$this->get_the_title($course_id));		
				bp_course_wp_mail($to,$subject,$announcement,array('action'=>'student_course_announcement','item_id'=>$course_id,'user_id'=>$user_id['user_id'],'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.announcement'=>$announcement)));
			}
		}

		
	}
	function instructor_message_course_announcement($course_id,$student_type,$email,$announcement){
		$reciever_ids =$this->get_instructors($course_id);
		$sender_id = get_current_user_id();
		$subject = sprintf(__('Announcement for course %s','vibe'),$this->get_the_title($course_id));
		if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => $subject, 'content' => $announcement,   'recipients' => $reciever_ids ) );
	}
	function instructor_notification_course_announcement($course_id,$student_type,$email,$announcement){
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){

			bp_course_add_notification( array(
					'user_id'          => $instructor_id,
					'item_id'          => $course_id,
					'component_name'   => 'course',
					'component_action' => 'instructor_course_announcement'
				) );
		}
	}
	function instructor_email_course_announcement($course_id,$student_type,$email,$announcement){
		$to = $this->instructor_emails($course_id,'course_announcement');
		$subject = sprintf(__('Announcement for Course %s','vibe'),$this->get_the_title($course_id));
		bp_course_wp_mail($to,$subject,$announcement,array('action'=>'instructor_course_announcement','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.announcement'=>$announcement)));
	}

	function student_message_course_news($news_id,$post){
		$course_id = get_post_meta($news_id,'vibe_news_course',true);
		if(!isset($course_id) || !is_numeric($course_id))
			return;
		global $wpdb;
		$users=$wpdb->get_results($wpdb->prepare("
	              	SELECT  user_id
	                FROM {$wpdb->usermeta} 
	                WHERE  meta_key   = %s
	                AND meta_value IN (%d,%d)
	            ",'course_status'.$course_id,1,2),ARRAY_A);

		$content = $post->post_title.' <a href="'.$this->get_permalink($news_id).'" class="link">'.__('Read more','vibe').'</a>';
		if(bp_is_active('messages') && function_exists('bp_course_messages_new_message') && count($user_ids))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => sprintf(__('News for Course %s','vibe'),$this->get_the_title($course_id)), 'content' => $content,   'recipients' => $user_ids ) );
	}

	function student_notification_course_news($news_id,$post){
		$course_id = get_post_meta($news_id,'vibe_news_course',true);
		if(!isset($course_id) || !is_numeric($course_id))
			return;
		global $wpdb;
		$users=$wpdb->get_results($wpdb->prepare("
	              	SELECT  user_id
	                FROM {$wpdb->usermeta} 
	                WHERE  meta_key   = %s
	                AND meta_value IN (%d,%d)
	            ",'course_status'.$course_id,1,2),ARRAY_A);

		foreach($users as $user_id){
			bp_course_add_notification(array(
				'user_id' => $user_id['user_id'],
				'item_id' => $course_id,
				'secondary_item_id'=> $news_id,
				'component_name'=>'course',
				'component_action'  => 'course_news'
			));
		}
	}

	function student_email_course_news($news_id,$post){
		$course_id = get_post_meta($news_id,'vibe_news_course',true);
		if(!isset($course_id) || !is_numeric($course_id))
			return;
		global $wpdb;
		$users=$wpdb->get_results($wpdb->prepare("
	              	SELECT  user_id
	                FROM {$wpdb->usermeta} 
	                WHERE  meta_key   = %s
	                AND meta_value IN (%d,%d)
	            ",'course_status'.$course_id,1,2),ARRAY_A);

		$subject = sprintf(__('News for Course %s','vibe'),$this->get_the_title($course_id));
		$message = '<h3>'.$post->post_title.'</h3> <hr> '.$post->post_content.' <a href="'.$this->get_permalink($news_id).'" class="link">'.__('Read more','vibe').'</a>';

		foreach($users as $user_id){
			$user = get_user_by('id',$user_id['user_id']);
			$enable = get_user_meta($user_id['user_id'],'student_course_news',true);
			if(isset($user->user_email) && $enable !== 'no')
				$to = $user->user_email;

			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_course_news','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.news'=>$message)));
		}
	}

	function instructor_message_course_news($news_id,$post){
		$course_id = get_post_meta($news_id,'vibe_news_course',true);
		if(!isset($course_id) || !is_numeric($course_id))
			return;

		$reciever_ids =$this->get_instructors($course_id);
		$sender_id = get_current_user_id();
		$subject = sprintf(__('News for course %s','vibe'),$this->get_the_title($course_id));
		$content = $post->post_title.' <a href="'.$this->get_permalink($news_id).'" class="link">'.__('Read more','vibe').'</a>';
		if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => $subject, 'content' => $content,   'recipients' => $reciever_ids ) );
	}

	function instructor_notification_course_news($news_id,$post){
		$course_id = get_post_meta($news_id,'vibe_news_course',true);
		if(!isset($course_id) || !is_numeric($course_id))
			return;

		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){

			bp_course_add_notification( array(
					'user_id'          => $instructor_id,
					'item_id'          => $course_id,
					'secondary_item_id'=> $news_id,
					'component_name'   => 'course',
					'component_action' => 'instructor_course_news'
				) );
		}
	}

	function instructor_email_course_news($news_id,$post){
		$course_id = get_post_meta($news_id,'vibe_news_course',true);
		if(!isset($course_id) || !is_numeric($course_id))
			return;

		$to = $this->instructor_emails($course_id,'course_news');
		$subject = sprintf(__('News for Course %s','vibe'),$this->get_the_title($course_id));
		$message = '<h3>'.$post->post_title.'</h3> <hr> '.$post->post_content.' <a href="'.$this->get_permalink($news_id).'" class="link">'.__('Read more','vibe').'</a>';

		bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_news','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.news'=>$message)));
	}

	/* ====== WHEN STUDENT SUBSCRIBES A COURSE ====== */
	//student functions
	function student_message_course_subscribed($course_id,$user_id,$group_id = null){
		if(!is_user_logged_in())
			return;

		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('You\'re subscribed for course : %s','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Subscribed for Course','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}

	function student_notification_course_subscribed($course_id,$user_id,$group_id = null){

		bp_course_add_notification(array(
				'user_id' => $user_id,
				'item_id' => $course_id,
				'secondary_item_id' => (($group_id)?$group_id:''),
				'component_action'  => 'subscribe_course'
			));
	}

	function student_email_course_subscribed($course_id,$user_id,$group_id = null){

		$enable = get_user_meta($user_id,'student_course_subscribed',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			
			$subject = __('Subscribed for Course','vibe');
			$message = sprintf(__('You\'re subscribed for course : %s','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');

			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_course_subscribed','item_id'=>$course_id,'secondary_item_id'=>(($group_id)?$group_id:''),'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>')));
		}

	}

	//Instructor functions
	function instructor_message_course_subscribed($course_id,$user_id,$group_id = null){

		$instructor_ids=apply_filters('wplms_course_instructors',$this->get_post_field('post_author', $course_id),$course_id);
		if(!is_array($instructor_ids))
			$instructor_ids = array($instructor_ids);

		$message = sprintf(__('Student %s subscribed for course : %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => __('Subscribed for Course','vibe'), 'content' => $message,   'recipients' => $instructor_ids ) );
	}

	function instructor_notification_course_subscribed($course_id,$user_id,$group_id = null){
		$instructor_ids=apply_filters('wplms_course_instructors',$this->get_post_field('post_author', $course_id),$course_id);
		if(!is_array($instructor_ids))
			$instructor_ids = array($instructor_ids);

		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification(array(
				'user_id' => $instructor_id,
				'item_id' => $course_id,
				'secondary_item_id' => $user_id,
				'component_action'  => 'instructor_subscribe_course'
			));
		}
	}

	function instructor_email_course_subscribed($course_id,$user_id,$group_id = null){

		$to = $this->instructor_emails($course_id,'course_subscribed');

		$subject = sprintf(__('Student subscribed for course %s','vibe'),$this->get_the_title($course_id)); 
		$message = sprintf(__('Student %s subscribed for course : %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to)){
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_subscribed','item_id'=>$course_id,'secondary_item_id'=>(($group_id)?$group_id:''),'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));
		}
	}

	/* ====== WHEN STUDENT ADDED BY INSTRUCTOR VIA COURSE - BULK ACTIONS */

	//Student Functions
	function student_message_course_added($check_action,$course_id,$members){ 
		if(!is_user_logged_in() || $check_action != 'added_students')
			return;

		$sender_id=$this->get_post_field('post_author', $course_id);

		$message = sprintf(__('You\'re added to course : %s','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Added to Course','vibe'), 'content' => $message,   'recipients' => $members ) );
	}

	function student_notification_course_added($check_action,$course_id,$members){ 
		if(!is_user_logged_in() || $check_action != 'added_students')
			return;
		
		foreach($members as $user_id){
				bp_course_add_notification( array(
					'user_id'          => $user_id,
					'item_id'          => $course_id,
					'component_name'   => 'course',
					'component_action' => 'bulk_action'
				) );
		}
	}

	function student_email_course_added($check_action,$course_id,$members){ 
		if(!is_user_logged_in() || $check_action != 'added_students')
			return;

		$to = array();
		foreach($members as $user_id){
			$enable = get_user_meta($user_id,'student_course_added',true);
			if($enable !== 'no'){
				$user = get_user_by( 'id', $user_id);
				$to[] = $user->user_email;
			}
		}
		$subject = sprintf(__('Added to course %s','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('You\'ve been added to course : %s','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_course_added','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>')));
	}

	function instructor_message_course_added($check_action,$course_id,$members){ 
		if(!is_user_logged_in() || $check_action != 'added_students')
			return;

		$instructor_ids = apply_filters('wplms_course_instructors',$this->get_post_field('post_author', $course_id),$course_id);
		if(!is_array($instructor_ids)){
			$instructor_ids = array($instructor_ids);
		}

		foreach($members as $user_id){
			$message = sprintf(__('Student %s added to course : %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
		      	bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => __('Student added to Course','vibe'), 'content' => $message,   'recipients' => $instructor_ids ) );
		}
	}

	//Instructor Functions
	function instructor_notification_course_added($check_action,$course_id,$members){ 
		if(!is_user_logged_in() || $check_action != 'added_students')
			return;
		
		$instructor_ids =apply_filters('wplms_course_instructors',$this->get_post_field('post_author', $course_id),$course_id);	
		if(!is_array($instructor_ids)){
			$instructor_ids = array($instructor_ids);
		}
		foreach($members as $student_id){
				foreach($instructor_ids as $instructor_id){
					bp_course_add_notification( array(
						'user_id'          => $instructor_id,
						'item_id'          => $course_id,
						'secondary_item_id'=> $student_id,
						'component_name'   => 'course',
						'component_action' => 'instructor_bulk_action'
					) );
				}
		}
	}

	function instructor_email_course_added($check_action,$course_id,$members){ 
		if(!is_user_logged_in() || $check_action != 'added_students')
			return;

		$to = $this->instructor_emails($course_id,'course_added');

		$student_info = '';
		foreach($members as $student_id){
			$student =get_user_by('id',$student_id);
			$student_info .= $student->display_name.' ( '.$student->user_email.' ) ';
		}
		// Keep Note !!
		
		$subject = sprintf(__('Students added to course %s','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('%d students added to course : %s , %s','vibe'),count($members),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>',$student_info);
		if(count($to))
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_added','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$student_info,'student.name'=>$student->display_name)));
			
	}
	/* ====== WHEN STUDENT STARTS A COURSE ====== */

	function student_message_course_start($course_id,$user_id){
		
		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('You started the course : %s','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Started a Course','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}

	function student_notification_course_start($course_id,$user_id){
		
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $course_id,
			'component_name'   => 'course',
			'component_action' => 'course_start'
		));
	}

	function student_email_course_start($course_id,$user_id){
		
		$enable = get_user_meta($user_id,'student_course_start',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You started course %s','vibe'),$this->get_the_title($course_id));
			$message = sprintf(__('You\'ve started the course : %s','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_course_start','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>')));
		}
		
	}

	function instructor_message_course_start($course_id,$user_id){
		
		$instructor_ids=apply_filters('wplms_course_instructors',$this->get_post_field('post_author', $course_id),$course_id);
		if(!is_array($instructor_ids))
			$instructor_ids = array($instructor_ids);

		$message = sprintf(__('Student %s started the course : %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Student started the course %s','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $instructor_ids ) );
	}

	function instructor_notification_course_start($course_id,$user_id){
		
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){

			bp_course_add_notification( array(
					'user_id'          => $instructor_id,
					'item_id'          => $course_id,
					'secondary_item_id'=> $user_id,
					'component_name'   => 'course',
					'component_action' => 'instructor_course_start'
				) );
		}
	}

	function instructor_email_course_start($course_id,$user_id){
		
		$to = $this->instructor_emails($course_id,'course_start');

		$subject = sprintf(__('Student started course %s','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('Student %s started the course : %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))			
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_start','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));
	}

	/* ====== WHEN STUDENT SUBMITS A COURSE ====== */
	function student_message_course_submit($course_id,$user_id){

		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('You\'ve submitted the course : %s','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Course submitted','vibe'), 'content' => $message,   'recipients' => $user_id ) );

	}

	function student_notification_course_submit($course_id,$user_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $course_id,
			'component_name'   => 'course',
			'component_action' => 'course_submit'
		));
	}
	
	function student_email_course_submit($course_id,$user_id){
		
		$enable = get_user_meta($user_id,'student_course_submit',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('Course %s submitted','vibe'),$this->get_the_title($course_id));
			$message = sprintf(__('You\'ve submitted the course : %s','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_course_submit','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>')));
		}

	}
	
	function instructor_message_course_submit($course_id,$user_id){

		$instructor_ids = $this->get_instructors($course_id);

		$message = sprintf(__('Student %s submitted the course : %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Student submitted the course %s','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $instructor_ids) );

	}

	function instructor_notification_course_submit($course_id,$user_id){
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $course_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_course_submit'
			));
		}
	}
	
	function instructor_email_course_submit($course_id,$user_id){
		$to = $this->instructor_emails($course_id,'course_submit');

		$subject = sprintf(__('Student submitted course %s','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('Student %s submitted the course : %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))			
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_submit','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));
	}

	/* ====== WHEN STUDENT RESETS A COURSE ====== */
	function student_message_course_reset($course_id,$user_id){

		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('The Course %s was reset by Course instructor ','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Course reset','vibe'), 'content' => $message,   'recipients' => $user_id ) );

	}
	function student_notification_course_reset($course_id,$user_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $course_id,
			'component_name'   => 'course',
			'component_action' => 'course_reset'
		));
	}
	function student_email_course_reset($course_id,$user_id){
		$enable = get_user_meta($user_id,'student_course_reset',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('Course %s reset','vibe'),$this->get_the_title($course_id));
			$message = sprintf(__('%s Course was reset by Instructor','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_course_reset','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>')));
		}
	}
	function instructor_message_course_reset($course_id,$user_id){
		$instructor_ids = $this->get_instructors($course_id);
		$message = sprintf(__('%s course reset for student : %s','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>',$this->bp_core_get_userlink($user_id));
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Course %s reset for student','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $instructor_ids) );

	}
	function instructor_notification_course_reset($course_id,$user_id){
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $course_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_course_reset'
			));
		}
	}
	function instructor_email_course_reset($course_id,$user_id){
		$to = $this->instructor_emails($course_id,'course_reset');
		$subject = sprintf(__('Course %s reset for Student','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('Course %s was reset for student %s ','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>',$this->bp_core_get_userlink($user_id));
		if(count($to))			
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_reset','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));
	}
	/* ====== WHEN STUDENT RETAKES A COURSE ====== */
	function student_message_course_retake($course_id,$user_id){
		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('You retook the Course %s ','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Course retake','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}
	function student_notification_course_retake($course_id,$user_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $course_id,
			'component_name'   => 'course',
			'component_action' => 'course_retake'
		));
	}
	function student_email_course_retake($course_id,$user_id){
		$enable = get_user_meta($user_id,'student_course_retake',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You retook the course Course %s','vibe'),$this->get_the_title($course_id));
			$message = sprintf(__('You\'ve retaken the Course %s','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_course_retake','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>')));
		}
	}
	function instructor_message_course_retake($course_id,$user_id){
		$instructor_ids = $this->get_instructors($course_id);
		$message = sprintf(__('Student %s retook the course %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Course %s reset for student','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $instructor_ids) );

	}
	function instructor_notification_course_retake($course_id,$user_id){
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $course_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_course_retake'
			));
		}
	}
	function instructor_email_course_retake($course_id,$user_id){
		$to = $this->instructor_emails($course_id,'course_retake');
		$subject = sprintf(__('Course %s retaken by the Student','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('Course %s was retaken by the student %s ','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>',$this->bp_core_get_userlink($user_id));
		if(count($to))			
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_retake','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));
	}
	/* ====== WHEN STUDENT COURSE IS EVALUATED ====== */

	function student_message_course_evaluation($course_id,$marks,$user_id){

		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('You\'ve obtained %s  in Course : %s','vibe'),apply_filters('wplms_course_marks',$marks.'/100',$course_id),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Course results available','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}
	function student_notification_course_evaluation($course_id,$marks,$user_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $course_id,
			'component_name'   => 'course',
			'component_action' => 'course_evaluated'
		));
	}
	function student_email_course_evaluation($course_id,$marks,$user_id){
		$enable = get_user_meta($user_id,'student_course_evaluation',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('Course %s results available','vibe'),$this->get_the_title($course_id));
			$message = sprintf(__('You\'ve obtained %s  in Course : %s','vibe'),apply_filters('wplms_course_marks',$marks.'/100',$course_id),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_course_evaluation','item_id'=>$course_id,'marks'=>$marks,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','course.marks'=>$marks)));
		}
	}
	function instructor_message_course_evaluation($course_id,$marks,$user_id){
		$instructor_ids = $this->get_instructors($course_id);
		$message = sprintf(__('Student %s got %s in course %s','vibe'),$this->bp_core_get_userlink($user_id),apply_filters('wplms_course_marks',$marks.'/100',$course_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Course %s evaluated for student','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_course_evaluation($course_id,$marks,$user_id){
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $course_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_course_evaluation'
			));
		}
	}
	function instructor_email_course_evaluation($course_id,$marks,$user_id){
		$to = $this->instructor_emails($course_id,'course_retake');
		$subject = sprintf(__('Course %s evaluated for Student','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('Student %s got %s in Course %s','vibe'),$this->bp_core_get_userlink($user_id),apply_filters('wplms_course_marks',$marks.'/100',$course_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))			
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_evaluation','item_id'=>$course_id,'marks'=>$marks,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id),'course.marks'=>$marks)));
	}
	/* ====== WHEN STUDENT OBTAINS A BADGE IN A COURSE ====== */
	function student_message_course_badge($course_id,$badges,$user_id,$badge_filter){
		if(!$badge_filter)
			return;
		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('Congratulations ! You\'ve obtained a badge in Course : %s','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('You got a new Badge !','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}
	function student_notification_course_badge($course_id,$badges,$user_id,$badge_filter){
		if(!$badge_filter)
			return;
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $course_id,
			'component_name'   => 'course',
			'component_action' => 'course_badge'
		));
	}
	function student_email_course_badge($course_id,$badges,$user_id,$badge_filter){
		if(!$badge_filter)
			return;
		$enable = get_user_meta($user_id,'student_course_badge',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You got a Badge in Course %s','vibe'),$this->get_the_title($course_id));
			$message = sprintf(__('You\'ve obtained a Badge in Course : %s','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_course_badge','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>')));
		}
	}
	function instructor_message_course_badge($course_id,$badges,$user_id,$badge_filter){
		if(!$badge_filter)
			return;
		$instructor_ids = $this->get_instructors($course_id);
		$message = sprintf(__('Student %s got a Badge in course %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Student got a Badge in Course %s','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_course_badge($course_id,$badges,$user_id,$badge_filter){
		if(!$badge_filter)
			return;
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $course_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_course_badge'
			));
		}
	}
	function instructor_email_course_badge($course_id,$badges,$user_id,$badge_filter){
		if(!$badge_filter)
			return;
		$to = $this->instructor_emails($course_id,'course_badge');
		$subject = sprintf(__('Student got a Badge in Course %s','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('Student %s got a Badge in Course %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))			
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_badge','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));
	}
	/* ====== WHEN STUDENT OBTAINS A CERTIFICATE IN A COURSE ====== */
	function student_message_course_certificate($course_id,$pass,$user_id,$passing_filter){
		if(!$passing_filter)
			return;
		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('Congratulations ! You\'ve obtained a Certificate in Course : %s','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('You got a new Certificate !','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}
	function student_notification_course_certificate($course_id,$pass,$user_id,$passing_filter){
		if(!$passing_filter)
			return;
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $course_id,
			'component_name'   => 'course',
			'component_action' => 'course_certificate'
		));
	}
	function student_email_course_certificate($course_id,$pass,$user_id,$passing_filter){
		if(!$passing_filter)
			return;
		$enable = get_user_meta($user_id,'student_course_certificate',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You got a Certificate in Course %s','vibe'),$this->get_the_title($course_id));
			$message = sprintf(__('You\'ve obtained a certificate in Course : %s . %s View Certificate %s','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','<a href="'.bp_get_course_certificate('user_id='.$user_id.'&course_id='.$course_id).'">','</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_course_certificate','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>')));
		}
	}
	function instructor_message_course_certificate($course_id,$pass,$user_id,$passing_filter){
		if(!$passing_filter)
			return;
		$instructor_ids = $this->get_instructors($course_id);
		$message = sprintf(__('Student %s got a Certificate in course %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Student got a Certificate in Course %s','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_course_certificate($course_id,$pass,$user_id,$passing_filter){
		if(!$passing_filter)
			return;
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $course_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_course_certificate'
			));
		}
	}
	function instructor_email_course_certificate($course_id,$pass,$user_id,$passing_filter){
		if(!$passing_filter)
			return;
		$to = $this->instructor_emails($course_id,'course_certificate');
		$subject = sprintf(__('Student got a Certificate in Course %s','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('Student %s got a Certificate in Course %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))			
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_certificate','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));
	}
	/* ====== WHEN STUDENT SUBMITS A REVIEW FOR COURSE ====== */
	function student_message_course_review($course_id,$rating,$title){
		$user_id = get_current_user_id();
		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('You\'ve submitted a review of Course : %s','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('You submitted a course review','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}
	function student_notification_course_review($course_id,$rating,$title){
		$user_id = get_current_user_id();
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $course_id,
			'component_name'   => 'course',
			'component_action' => 'course_review'
		));
	}
	function student_email_course_review($course_id,$rating,$title){
		$user_id = get_current_user_id();
		$enable = get_user_meta($user_id,'student_course_review',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You submitted a review for Course %s','vibe'),$this->get_the_title($course_id));
			$message = sprintf(__('You submitted a review Course : %s','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_course_review','item_id'=>$course_id,'user_id'=>$user_id,'rating'=>$rating,'title'=>$title,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','course.review'=>$title,'course.rating'=>$rating)));
		}
	}
	function instructor_message_course_review($course_id,$rating,$title){
		$user_id = get_current_user_id();
		$instructor_ids = $this->get_instructors($course_id);
		$message = sprintf(__('Student %s submitted a review for the course %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Student submitted a review for the course %s','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_course_review($course_id,$rating,$title){
		$user_id = get_current_user_id();
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $course_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_course_review'
			));
		}
	}
	function instructor_email_course_review($course_id,$rating,$title){
		$user_id = get_current_user_id();
		$to = $this->instructor_emails($course_id,'course_review');
		$subject = sprintf(__('Student submitted a review for Course %s','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('Student %s submitted a review for the Course %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))			
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_review','item_id'=>$course_id,'user_id'=>$user_id,'rating'=>$rating,'title'=>$title,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id),'course.review'=>$title,'course.rating'=>$rating)));

	}
	/* ====== WHEN STUDENT UNSUBSCRIBES FROM COURSE ====== */
	function student_message_course_unsubscribe($course_id,$user_id,$group_id){
		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('You\'re unsubscribed from Course : %s','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('You unsubscribed from course','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}
	function student_notification_course_unsubscribe($course_id,$user_id,$group_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $course_id,
			'component_name'   => 'course',
			'component_action' => 'course_unsubscribe'
		));
	}
	function student_email_course_unsubscribe($course_id,$user_id,$group_id){
		$enable = get_user_meta($user_id,'student_course_unsubscribe',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You\'re unsubscribed from course %s','vibe'),$this->get_the_title($course_id));
			$message = sprintf(__('You\'re unsubscribed from the Course %s','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_course_unsubscribe','item_id'=>$course_id,'user_id'=>$user_id,'secondary_item_id'=>$group_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>')));
		}
	}
	function instructor_message_course_unsubscribe($course_id,$user_id,$group_id){
		$instructor_ids = $this->get_instructors($course_id);
		$message = sprintf(__('Student %s unsubscribed course %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Student unsubscribed from Course %s','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_course_unsubscribe($course_id,$user_id,$group_id){
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $course_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_course_unsubscribe'
			));
		}
	}
	function instructor_email_course_unsubscribe($course_id,$user_id,$group_id){
		$to = $this->instructor_emails($course_id,'course_unsubscribe ');
		$subject = sprintf(__('Student unsubscribed from Course %s','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('Student %s unsubscribed from Course %s','vibe'),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))			
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_unsubscribe','item_id'=>$course_id,'user_id'=>$user_id,'secondary_item_id'=>$group_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));
	}
	/* ====== WHEN STUDENT USES A COURSE CODE ====== */
	function student_message_course_codes($code,$course_id,$user_id){
		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('You applied a code for Course : %s','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('You applied a course code','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}
	function student_notification_course_codes($code,$course_id,$user_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $course_id,
			'component_name'   => 'course',
			'component_action' => 'course_codes'
		));
	}
	function student_email_course_codes($code,$course_id,$user_id){
		$enable = get_user_meta($user_id,'student_course_unsubscribe',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You applied course code in course %s','vibe'),$this->get_the_title($course_id));
			$message = sprintf(__('You\'ve applied Course Code in Course %s','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_course_codes','item_id'=>$course_id,'user_id'=>$user_id,'code'=>$code,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','course.code'=>$code)));
		}
	}
	function instructor_message_course_codes($code,$course_id,$user_id){

		$instructor_ids = $this->get_instructors($course_id);
		$message = sprintf(__('Student %s used course code %s for course %s','vibe'),$this->bp_core_get_userlink($user_id),$code,'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Student applied Course code for Course %s','vibe'),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>'), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_course_codes($code,$course_id,$user_id){
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $course_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_course_codes'
			));
		}
	}
	function instructor_email_course_codes($code,$course_id,$user_id){
		$to = $this->instructor_emails($course_id,'course_codes ');
		$subject = sprintf(__('Student applied code for Course %s','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('Student %s applied code %s for Course %s','vibe'),$this->bp_core_get_userlink($user_id),$code,'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))			
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_codes','item_id'=>$course_id,'user_id'=>$user_id,'code'=>$code,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id),'course.code'=>$code)));
	}
	/* ====== WHEN STUDENT COMPLETES A UNIT ====== */
	function student_message_unit_complete($unit_id,$course_progress,$course_id){
		$user_id = get_current_user_id();
		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('You\'ve completed the unit %s in Course : %s','vibe'),$this->get_the_title($unit_id),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('You completed a unit','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}
	function student_notification_unit_complete($unit_id,$course_progress,$course_id){
		$user_id = get_current_user_id();
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $unit_id,
			'secondary_item_id'=> $course_id,
			'component_name'   => 'course',
			'component_action' => 'unit_complete'
		));
	}
	function student_email_unit_complete($unit_id,$course_progress,$course_id){
		$user_id = get_current_user_id();
		$enable = get_user_meta($user_id,'student_unit_complete',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You completed unit','vibe'),$this->get_the_title($unit_id));
			$message = sprintf(__('You completed a unit %s in Course %s','vibe'),$this->get_the_title($unit_id),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_unit_complete','item_id'=>$course_id,'user_id'=>$user_id,'secondary_item_id'=>$unit_id,'progress'=>$course_progress,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','unit.name'=>$this->get_the_title($unit_id))));
		}
	}
	function instructor_message_unit_complete($unit_id,$course_progress,$course_id){
		$user_id = get_current_user_id();
		$instructor_ids = $this->get_instructors($course_id);
		$message = sprintf(__('Student %s completed the unit %s in course %s','vibe'),$this->bp_core_get_userlink($user_id),$this->get_the_title($unit_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Student completed a unit in Course %s','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_unit_complete($unit_id,$course_progress,$course_id){
		$user_id = get_current_user_id();
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $unit_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_unit_complete'
			));
		}
	}
	function instructor_email_unit_complete($unit_id,$course_progress,$course_id){
		$user_id = get_current_user_id();
		$to = $this->instructor_emails($course_id,'unit_complete');
		$subject = sprintf(__('Student completed unit in Course %s','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('Student %s completed unit %s in Course %s','vibe'),$this->bp_core_get_userlink($user_id),$this->get_the_title($unit_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))			
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_unit_complete','item_id'=>$unit_id,'user_id'=>$user_id,'secondary_item_id'=>$course_id,'progress'=>$course_progress,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id),'unit.name'=>$this->get_the_title($unit_id))));
	}
	/* ==== WHEN Instructor Completes a unit for Student ==== */
	
	function student_message_unit_instructor_complete($unit_id,$user_id,$course_id){
		$sender_id=get_current_user_id();
		$message = sprintf(__('Instructor %s marked the unit %s complete in Course : %s','vibe'),$this->bp_core_get_userlink($sender_id),$this->get_the_title($unit_id),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => sprintf(__('Instructor marked a unit complete in Course %s','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $user_id ) );
	}

	function student_notification_unit_instructor_complete($unit_id,$user_id,$course_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $unit_id,
			'secondary_item_id'=> $course_id,
			'component_name'   => 'course',
			'component_action' => 'unit_instructor_complete'
		));
	}
	function student_email_unit_instructor_complete($unit_id,$user_id,$course_id){
		$instructor_id = get_current_user_id();
		$enable = get_user_meta($user_id,'unit_instructor_complete',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('Instructor marked unit %s complete ','vibe'),$this->get_the_title($unit_id));
			$message = sprintf(__('Unit %s was marked complete by Instructor %s in Course %s','vibe'),$this->get_the_title($unit_id),$this->bp_core_get_userlink($instructor_id),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_unit_instructor_complete','item_id'=>$course_id,'user_id'=>$user_id,'instructor_id'=>$instructor_id,'secondary_item_id'=>$unit_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','course.instructorlink'=>$this->bp_code_get_userlink($instructor_id),'unit.name'=>$this->get_the_title($unit_id))));
		}
	}
	function instructor_message_unit_instructor_complete($unit_id,$user_id,$course_id){
		$i_id = get_current_user_id();
		$instructor_ids = $this->get_instructors($course_id);
		$message = sprintf(__('Instructor %s completed unit %s for Student %s in course %s','vibe'),$this->bp_core_get_userlink($i_id),$this->get_the_title($unit_id),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Student completed a unit in Course %s','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_unit_instructor_complete($unit_id,$user_id,$course_id){
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $unit_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_unit_instructor_complete'
			));
		}
	}
	function instructor_email_unit_instructor_complete($unit_id,$user_id,$course_id){
		$i_id = get_current_user_id();
		$to = $this->instructor_emails($course_id,'unit_complete');
		$subject = sprintf(__('Instructor marked unit %s comple for Student in Course %s','vibe'),$this->get_the_title($unit_id),$this->get_the_title($course_id));
		$message = sprintf(__('Instructor %s completed the unit %s for Student %s in Course %s','vibe'),$this->bp_core_get_userlink($i_id),$this->get_the_title($unit_id),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))			
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_unit_instructor_complete','item_id'=>$course_id,'user_id'=>$user_id,'secondary_item_id'=>$unit_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','course.instructorlink'=>$this->bp_core_get_userlink($i_id),'student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id),'unit.name'=>$this->get_the_title($unit_id))));
	}
	/* ==== WHEN Instructor UnCompletes a unit for Student ==== */

	function student_message_unit_instructor_uncomplete($unit_id,$user_id,$course_id){
		$sender_id=get_current_user_id();
		$message = sprintf(__('Instructor %s marked the unit %s incomplete in Course : %s','vibe'),$this->bp_core_get_userlink($sender_id),$this->get_the_title($unit_id),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => sprintf(__('Instructor marked a unit uncomplete in Course %s','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $user_id ) );
	}

	function student_notification_unit_instructor_uncomplete($unit_id,$user_id,$course_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $unit_id,
			'secondary_item_id'=> $course_id,
			'component_name'   => 'course',
			'component_action' => 'unit_instructor_uncomplete'
		));
	}
	function student_email_unit_instructor_uncomplete($unit_id,$user_id,$course_id){
		$instructor_id = get_current_user_id();
		$enable = get_user_meta($user_id,'unit_instructor_uncomplete',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('Instructor marked unit %s uncompleted ','vibe'),$this->get_the_title($unit_id));
			$message = sprintf(__('Unit %s was marked uncomplete by Instructor %s in Course %s','vibe'),$this->get_the_title($unit_id),$this->bp_core_get_userlink($instructor_id),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_unit_instructor_uncomplete','item_id'=>$unit_id,'user_id'=>$user_id,'secondary_item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','course.instructorlink'=>$this->bp_code_get_userlink($instructor_id),'unit.name'=>$this->get_the_title($unit_id))));
		}
	}
	function instructor_message_unit_instructor_uncomplete($unit_id,$user_id,$course_id){
		$i_id = get_current_user_id();
		$instructor_ids = $this->get_instructors($course_id);
		$message = sprintf(__('Instructor %s uncompleted unit %s for Student %s in course %s','vibe'),$this->bp_core_get_userlink($i_id),$this->get_the_title($unit_id),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Student uncompleted a unit in Course %s','vibe'),$this->get_the_title($course_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_unit_instructor_uncomplete($unit_id,$user_id,$course_id){
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $unit_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_unit_instructor_uncomplete'
			));
		}
	}
	function instructor_email_unit_instructor_uncomplete($unit_id,$user_id,$course_id){
		$i_id = get_current_user_id();
		$to = $this->instructor_emails($course_id,'unit_uncomplete');
		$subject = sprintf(__('Instructor marked unit %s comple for Student in Course %s','vibe'),$this->get_the_title($unit_id),$this->get_the_title($course_id));
		$message = sprintf(__('Instructor %s uncompleted the unit %s for Student %s in Course %s','vibe'),$this->bp_core_get_userlink($i_id),$this->get_the_title($unit_id),$this->bp_core_get_userlink($user_id),'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))			
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_unit_instructor_uncomplete','item_id'=>$unit_id,'user_id'=>$user_id,'secondary_item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','course.instructorlink'=>$this->bp_core_get_userlink($i_id),'student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id),'unit.name'=>$this->get_the_title($unit_id))));
	}

	/* ====== WHEN STUDENT ADDS A UNIT COMMENT ====== */
	function student_message_unit_comment($unit_id,$user_id,$comment_id,$args){
		
		$sender_id=$this->get_post_field('post_author', $unit_id);
		$message = sprintf(__('You\'ve submitted a comment on unit : %s','vibe'),' <a href="'.$this->get_permalink($unit_id).'">'.$this->get_the_title($unit_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('You added a comment in Unit','vibe'), 'content' => $message,   'recipients' => $user_id ) );

	  	if(!empty($args['comment_parent']) && $args['comment_approved']){
			if(empty($this->comment_reply_user_id)){
				global $wpdb;
				$this->comment_reply_user_id = $wpdb->get_var("SELECT user_id FROM {$wpdb->comments} WHERE comment_id = ".$args['comment_parent']."");	
			}
			
			if($this->comment_reply_user_id != $user_id){
				
				$message = sprintf(__('%s replied on your comment in unit "%s" : %s ','vibe'),$this->bp_core_get_userlink($user_id),' <a href="'.$this->get_permalink($unit_id).'">'.$this->get_the_title($unit_id).'</a>',$args['comment_content']);
			    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
			      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => __('Reply on your comment','vibe'), 'content' => $message,   'recipients' => $this->comment_reply_user_id ) );
			}
		}
	}
	function student_notification_unit_comment($unit_id,$user_id,$comment_id,$args){
		
		$course_id = bp_course_get_unit_course_id($unit_id);
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $course_id,
			'secondary_item_id'=> $unit_id,
			'component_name'   => 'course',
			'component_action' => 'unit_comment'
		));

		if(!empty($args['comment_parent']) && $args['comment_approved']){
			if(empty($this->comment_reply_user_id)){
				global $wpdb;
				$this->comment_reply_user_id = $wpdb->get_var("SELECT user_id FROM {$wpdb->comments} WHERE comment_id = ".$args['comment_parent']."");	
			}
			if($this->comment_reply_user_id != $user_id){
				bp_course_add_notification( array(
					'user_id'          => $this->comment_reply_user_id,
					'item_id'          => $unit_id,
					'secondary_item_id'=> $user_id,
					'component_name'   => 'course',
					'component_action' => 'unit_comment_reply'
				));
			}
		}
	}
	function student_email_unit_comment($unit_id,$user_id,$comment_id,$args){
		$enable = get_user_meta($user_id,'student_unit_comment',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You added a comment on Unit %s','vibe'),$this->get_the_title($unit_id));
			$message = sprintf(__('You added a comment on Unit %s','vibe'),$this->get_the_title($unit_id));
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_unit_comment','item_id'=>$unit_id,'user_id'=>$user_id,'comment_id'=>$comment_id,'tokens'=>array('unit.name'=>$this->get_the_title($unit_id),'comment.comment_content'=>$args['comment_content'])));

			if(!empty($args['comment_parent']) && $args['comment_approved']){
				if(empty($this->comment_reply_user_id)){
					global $wpdb;
					$this->comment_reply_user_id = $wpdb->get_var("SELECT user_id FROM {$wpdb->comments} WHERE comment_id = ".$args['comment_parent']."");	
				}
				
				if($this->comment_reply_user_id != $user_id){
					$origninal_user = get_user_by( 'id', $this->comment_reply_user_id);
					$original_to = $origninal_user->user_email;
					$subject = sprintf(__('You received a reply on your comment in Unit %s','vibe'),$this->get_the_title($unit_id));
					$message = sprintf(__('Reply from %s on Unit %s : %s','vibe'),$this->bp_core_get_userlink($user_id),$this->get_the_title($unit_id),$args['comment_content']);
					bp_course_wp_mail($original_to,$subject,$message,array('action'=>'student_unit_comment_reply','item_id'=>$unit_id,'user_id'=>$user_id,'comment_id'=>$comment_id,'tokens'=>array('unit.name'=>$this->get_the_title($unit_id),'comment.userlink'=>$this->bp_core_get_userlink($user_id),'comment.name'=>$this->bp_core_get_user_displayname($user_id),'comment.comment_content'=>$args['comment_content'])));
				}
			}	
		}
	}
	function instructor_message_unit_comment($unit_id,$user_id,$comment_id,$args){
		$user_id = get_current_user_id();
		$instructor_ids = $this->get_instructors($unit_id);
		$message = sprintf(__('Student %s submitted a comment on unit %s ','vibe'),$this->bp_core_get_userlink($user_id),$this->get_the_title($unit_id));
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Student submitted a comment on Unit','vibe'),$this->get_the_title($unit_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_unit_comment($unit_id,$user_id,$comment_id,$args){
		$instructor_ids = $this->get_instructors($unit_id); 
		$course_id = bp_course_get_unit_course_id($unit_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $course_id,
				'secondary_item_id'=> $unit_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_unit_comment'
			));
		}
	}
	function instructor_email_unit_comment($unit_id,$user_id,$comment_id,$args){
		$user_id = get_current_user_id();
		$enable = get_user_meta($user_id,'instructor_unit_comment',true);
		if($enable !== 'no'){
			$sender_id = $this->get_post_field('post_author', $unit_id);
			$user = get_user_by( 'id', $sender_id);
			$to = $user->user_email;
			$subject = sprintf(__('Student added comment in unit %s','vibe'),$this->get_the_title($unit_id));
			$message = sprintf(__('Student %s added a comment in unit %s ','vibe'),$this->bp_core_get_userlink($user_id),$this->get_the_title($unit_id));

			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_unit_comment','item_id'=>$unit_id,'user_id'=>$user_id,'comment_id'=>$comment_id,'tokens'=>array('student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id),'unit.name'=>$this->get_the_title($unit_id),'comment.comment_content'=>$args['comment_content'])));
		}
	}

	/* ====== WHEN STUDENT STARTS A QUIZ ====== */
	function student_message_start_quiz($quiz_id,$user_id){
		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('You\'ve started the quiz : %s','vibe'),' <a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('You started a Quiz','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}
	function student_notification_start_quiz($quiz_id,$user_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $quiz_id,
			'component_name'   => 'course',
			'component_action' => 'start_quiz'
		));
	}
	function student_email_start_quiz($quiz_id,$user_id){
		$enable = get_user_meta($user_id,'student_start_quiz',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You started quiz %s','vibe'),$this->get_the_title($quiz_id));
			$message = sprintf(__('You started a quiz %s','vibe'),' <a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_start_quiz','item_id'=>$quiz_id,'user_id'=>$user_id,'tokens'=>array('quiz.name'=>$this->get_the_title($quiz_id),'quiz.titlelink'=>'<a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>')));	
		}
	}
	function instructor_message_start_quiz($quiz_id,$user_id){
		$instructor_ids = $this->get_instructors($quiz_id);
		$message = sprintf(__('Student %s started the quiz %s ','vibe'),$this->bp_core_get_userlink($user_id),$this->get_the_title($quiz_id));
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Student started the quiz %s','vibe'),$this->get_the_title($quiz_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_start_quiz($quiz_id,$user_id){
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $quiz_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_start_quiz'
			));
		}
	}
	function instructor_email_start_quiz($quiz_id,$user_id){
		$enable = get_user_meta($user_id,'instructor_start_quiz',true);
		if($enable !== 'no'){
			$sender_id=$this->get_post_field('post_author', $quiz_id);
			$user = get_user_by( 'id', $sender_id);
			$to = $user->user_email;
			$subject = sprintf(__('Student started quiz %s','vibe'),$this->get_the_title($quiz_id));
			$message = sprintf(__('Student %s started the quiz %s ','vibe'),$this->bp_core_get_userlink($user_id),$this->get_the_title($quiz_id));

			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_start_quiz','item_id'=>$quiz_id,'user_id'=>$user_id,'tokens'=>array('quiz.name'=>$this->get_the_title($quiz_id),'quiz.titlelink'=>'<a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));	
		}
	}
	/* ====== WHEN STUDENT QUIZ IS EVALUATED ====== */

	function student_message_quiz_evaluation($quiz_id,$marks,$user_id,$max){	
		$sender_id=$this->get_post_field('post_author', $quiz_id);
		$permalinks = bp_course_get_nav_permalinks();
		$message = sprintf(__('You obtained %d out of %s in Quiz : ','vibe'),$marks,$max,'<a href="'.trailingslashit( bp_core_get_user_domain( $user_id )) . BP_COURSE_SLUG. '/'.BP_COURSE_RESULTS_SLUG.'/?action='.$quiz_id .'">'.$this->get_the_title($quiz_id).'</a>');
	    if(bp_is_active('messages'))
	    bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Quiz results available','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	   
	}
	function student_notification_quiz_evaluation($quiz_id,$marks,$user_id,$max){	
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $quiz_id,
			'component_name'   => 'course',
			'component_action' => 'quiz_evaluation'
		));
	}
	function student_email_quiz_evaluation($quiz_id,$marks,$user_id,$max){	
		$enable = get_user_meta($user_id,'student_start_quiz',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('Results available for quiz %s','vibe'),$this->get_the_title($quiz_id));
			$message = sprintf(__('You obtained %d out of %s in Quiz : ','vibe'),$marks,$max,'<a href="'.trailingslashit( bp_core_get_user_domain( $user_id )) . BP_COURSE_SLUG. '/'.BP_COURSE_RESULTS_SLUG.'/?action='.$quiz_id .'">'.$this->get_the_title($quiz_id).'</a>');
				bp_course_wp_mail($to,$subject,$message,array('action'=>'student_quiz_evaluation','item_id'=>$quiz_id,'user_id'=>$user_id,'marks'=>$marks,'maximum'=>$max,'tokens'=>array('quiz.name'=>$this->get_the_title($quiz_id),'quiz.titlelink'=>'<a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>','quiz.max'=>$max,'quiz.marks'=>$marks)));	
		}
	}
	function instructor_message_quiz_evaluation($quiz_id,$marks,$user_id,$max){	
		$instructor_ids = $this->get_instructors($quiz_id);
		$message = sprintf(__('Student %s got %s from %s in quiz %s ','vibe'),$this->bp_core_get_userlink($user_id),$marks,$max,$this->get_the_title($quiz_id));
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Quiz %s evaluated for student','vibe'),$this->get_the_title($quiz_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_quiz_evaluation($quiz_id,$marks,$user_id,$max){	
		$instructor_id =$this->get_post_field('post_author', $quiz_id);
		
		bp_course_add_notification( array(
			'user_id'          => $instructor_id,
			'item_id'          => $quiz_id,
			'secondary_item_id'=> $user_id,
			'component_name'   => 'course',
			'component_action' => 'instructor_quiz_evaluation'
		));
		
	}
	function instructor_email_quiz_evaluation($quiz_id,$marks,$user_id,$max){	
		$enable = get_user_meta($user_id,'instructor_quiz_evaluation',true);
		if($enable !== 'no'){
			$sender_id = $this->get_post_field('post_author', $quiz_id);
			$user = get_user_by( 'id', $sender_id);
			$to = $user->user_email;
			$subject = sprintf(__('Quiz %s evaluated for Student','vibe'),$this->get_the_title($quiz_id));
			$message = sprintf(__('Student %s got %s from %s in quiz %s ','vibe'),$this->bp_core_get_userlink($user_id),$marks,$max,$this->get_the_title($quiz_id));

			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_quiz_evaluation','item_id'=>$quiz_id,'user_id'=>$user_id,'marks'=>$marks,'maximum'=>$max,'tokens'=>array('quiz.name'=>$this->get_the_title($quiz_id),'quiz.titlelink'=>'<a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>','quiz.max'=>$max,'quiz.marks'=>$marks,'student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));	
		}
	}
	/* ====== WHEN STUDENT SUBMITS A QUIZ ====== */
	function student_message_quiz_submit($quiz_id,$user_id){	

		if(function_exists('bp_course_messages_new_message')){
	          
	        $sender_id=$this->get_post_field('post_author', $quiz_id);
			$message = sprintf(__('You submitted the Quiz : %s','vibe'),$this->get_the_title($quiz_id));
		    if(bp_is_active('messages'))
		    	bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => sprintf(__('You submitted quiz %s ','vibe'),$this->get_the_title($quiz_id)), 'content' => $message,   'recipients' => $user_id ) );
			   
		}
	}
	function student_notification_quiz_submit($quiz_id,$user_id){	
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $quiz_id,
			'component_name'   => 'course',
			'component_action' => 'quiz_submit'
		));
	}
	function student_email_quiz_submit($quiz_id,$user_id){	
		$enable = get_user_meta($user_id,'student_quiz_submit',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You submitted quiz %s','vibe'),$this->get_the_title($quiz_id));
			$message = sprintf(__('You submitted quiz %s','vibe'),' <a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_quiz_submit','item_id'=>$quiz_id,'user_id'=>$user_id,'tokens'=>array('quiz.name'=>$this->get_the_title($quiz_id),'quiz.titlelink'=>'<a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>')));	
		}
	}
	function instructor_message_quiz_submit($quiz_id,$user_id){	
		$instructor_ids = $this->get_instructors($quiz_id);
		$course_id = get_post_meta($quiz_id,'vibe_course',true);
		$evaluation = get_post_meta($quiz_id,'vibe_quiz_auto_evaluate',true);
		if(isset($course_id) && is_numeric($course_id) && vibe_validate($evaluation)){
		$quiz_link ='<a href="'.$this->get_permalink($course_id).'?action=admin&submissions">'.$this->get_the_title($quiz_id).'</a>';
		}else{
		$quiz_link =$this->get_the_title($quiz_id);
		}
		$message = sprintf(__('Quiz %s submitted by student %s','vibe'),$quiz_link,$this->bp_core_get_userlink($user_id));

		bp_course_messages_new_message( array('sender_id' =>  $user_id, 'subject' => sprintf(__('Quiz %s submitted by Student','vibe'),$this->get_the_title($quiz_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_quiz_submit($quiz_id,$user_id){	
		$instructor_ids = $this->get_instructors($quiz_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $quiz_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_quiz_submit'
			));
		}
	}	
	function instructor_email_quiz_submit($quiz_id,$user_id){
		$to = $this->instructor_emails($quiz_id,'quiz_submit');
		$subject = sprintf(__('Student submitted quiz %s','vibe'),$this->get_the_title($quiz_id));
		$message = sprintf(__('Student %s submitted quiz %s','vibe'),$this->bp_core_get_userlink($user_id),' <a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>');
		if(count($to))
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_quiz_submit','item_id'=>$quiz_id,'user_id'=>$user_id,'tokens'=>array('quiz.name'=>$this->get_the_title($quiz_id),'quiz.titlelink'=>'<a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));	
	}
	/* ====== WHEN STUDENT RETAKES A QUIZ ====== */
	function student_message_quiz_retake($quiz_id,$user_id){
		$sender_id=$this->get_post_field('post_author', $quiz_id);
		$message = sprintf(__('You retook the Quiz : %s','vibe'),' <a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('You retook a Quiz','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}
	function student_notification_quiz_retake($quiz_id,$user_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $quiz_id,
			'component_name'   => 'course',
			'component_action' => 'quiz_retake'
		));
	}
	function student_email_quiz_retake($quiz_id,$user_id){
		$enable = get_user_meta($user_id,'student_start_quiz',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You retook quiz %s','vibe'),$this->get_the_title($quiz_id));
			$message = sprintf(__('You retook quiz %s','vibe'),' <a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_quiz_retake','item_id'=>$quiz_id,'user_id'=>$user_id,'tokens'=>array('quiz.name'=>$this->get_the_title($quiz_id),'quiz.titlelink'=>'<a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>')));	
		}
	}
	function instructor_message_quiz_retake($quiz_id,$user_id){
		$instructor_ids = $this->get_instructors($quiz_id);
		$message = sprintf(__('Student %s retook the Quiz : %s','vibe'),$this->bp_core_get_userlink($user_id),' <a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => sprintf(__('Student retook the Quiz %s','vibe'),$this->get_the_title($quiz_id)), 'content' => $message,   'recipients' => $instructor_ids ) );
	}
	function instructor_notification_quiz_retake($quiz_id,$user_id){
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $quiz_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_quiz_retake'
			));
		}
	}
	function instructor_email_quiz_retake($quiz_id,$user_id){
		$to = $this->instructor_emails($quiz_id,'quiz_retake');
		$subject = sprintf(__('Student retook the quiz %s','vibe'),$this->get_the_title($quiz_id));
		$message = sprintf(__('Student %s retook the quiz %s','vibe'),$this->bp_core_get_userlink($user_id),' <a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>');
		if(count($to))
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_quiz_retake','item_id'=>$quiz_id,'user_id'=>$user_id,'tokens'=>array('quiz.name'=>$this->get_the_title($quiz_id),'quiz.titlelink'=>'<a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));	
	}
	/* ====== WHEN STUDENT QUIZ  IS RESET ====== */
	function student_message_quiz_reset($quiz_id,$user_id){
		$sender_id=$this->get_post_field('post_author', $quiz_id);
		$message = sprintf(__('Quiz %s was reset by Instructor','vibe'),' <a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Quiz reset by Instructor','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}
	function student_notification_quiz_reset($quiz_id,$user_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $quiz_id,
			'component_name'   => 'course',
			'component_action' => 'quiz_reset'
		));
	}
	function student_email_quiz_reset($quiz_id,$user_id){
		$enable = get_user_meta($user_id,'student_start_quiz',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('Quiz %s reset','vibe'),$this->get_the_title($quiz_id));
			$message = sprintf(__('Quiz %s was reset by Instructor','vibe'),' <a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_quiz_reset','item_id'=>$quiz_id,'user_id'=>$user_id,'tokens'=>array('quiz.name'=>$this->get_the_title($quiz_id),'quiz.titlelink'=>'<a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>')));	
		}
	}
	function instructor_message_quiz_reset($quiz_id,$user_id){
		$instructor_ids = $this->get_instructors($quiz_id);
		$message = sprintf(__('Quiz %s reset for Student %s','vibe'),' <a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>',$this->bp_core_get_userlink($user_id));
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => sprintf(__('Quiz %s reset for Student','vibe'),$this->get_the_title($quiz_id)), 'content' => $message,   'recipients' => $instructor_ids ) );
	}
	function instructor_notification_quiz_reset($quiz_id,$user_id){
		$instructor_ids = $this->get_instructors($quiz_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $quiz_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_quiz_reset'
			));
		}

	}
	
	function instructor_email_quiz_reset($quiz_id,$user_id){
		$to = $this->instructor_emails($quiz_id,'quiz_reset');
		$subject = sprintf(__('Quiz %s reset for Student','vibe'),$this->get_the_title($quiz_id));
		$message = sprintf(__('Quiz %s was reset for Student %s','vibe'),' <a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>',$this->bp_core_get_userlink($user_id));
		if(count($to))
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_quiz_reset','item_id'=>$quiz_id,'user_id'=>$user_id,'tokens'=>array('quiz.name'=>$this->get_the_title($quiz_id),'quiz.titlelink'=>'<a href="'.$this->get_permalink($quiz_id).'">'.$this->get_the_title($quiz_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));	
	}

	/* ====== Assignments Start ====== */
	/* ====== WHEN STUDENT STARTS AN ASSIGNMENT ====== */
	function student_message_start_assignment($assignment_id,$user_id){
		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('You\'ve started the assignemtn : %s','vibe'),' <a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => sprintf(__('You started assignment %s','vibe'),$this->get_the_title($assignment_id)), 'content' => $message,   'recipients' => $user_id ) );
	}
	function student_notification_start_assignment($assignment_id,$user_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $assignment_id,
			'component_name'   => 'course',
			'component_action' => 'start_assignment'
		));
	}
	function student_email_start_assignment($assignment_id,$user_id){
		$enable = get_user_meta($user_id,'student_start_assignment',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You started assignment %s','vibe'),$this->get_the_title($assignment_id));
			$message = sprintf(__('You started the assignment %s','vibe'),' <a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_start_assignment','item_id'=>$assignment_id,'user_id'=>$user_id,'tokens'=>array('assignment.name'=>$this->get_the_title($assignment_id),'assignment.titlelink'=>'<a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>')));	
		}
	}
	function instructor_message_start_assignment($assignment_id,$user_id){
		$instructor_ids = $this->get_instructors($assignment_id);
		$message = sprintf(__('Student %s started the assignment %s ','vibe'),$this->bp_core_get_userlink($user_id),$this->get_the_title($assignment_id));
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Student started an assignment %s ','vibe'),$this->get_the_title($assignment_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_start_assignment($assignment_id,$user_id){
		$instructor_ids = $this->get_instructors($assignment_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $assignment_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_start_assignment'
			));
		}
	}
	function instructor_email_start_assignment($assignment_id,$user_id){
		$enable = get_user_meta($user_id,'instructor_start_assignment',true);
		if($enable !== 'no'){
			$sender_id = $this->get_post_field('post_author', $assignment_id);
			$user = get_user_by( 'id', $sender_id);
			$to = $user->user_email;
			$subject = sprintf(__('Student started assignment %s','vibe'),$this->get_the_title($assignment_id));
			$message = sprintf(__('Student %s started the assignment %s ','vibe'),$this->bp_core_get_userlink($user_id),$this->get_the_title($assignment_id));

			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_start_assignment','item_id'=>$assignment_id,'user_id'=>$user_id,'tokens'=>array('assignment.name'=>$this->get_the_title($assignment_id),'assignment.titlelink'=>'<a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));	
		}
	}
	/* ====== WHEN STUDENT QUIZ IS EVALUATED ====== */

	function student_message_assignment_evaluation($assignment_id,$marks,$user_id,$max){	
		$sender_id=$this->get_post_field('post_author', $assignment_id);

		$message = sprintf(__('You obtained %d out of %s in Assignment : %s ','vibe'),$marks,$max,'<a href="'.trailingslashit( bp_core_get_user_domain( $user_id )) . BP_COURSE_SLUG. '/'.BP_COURSE_RESULTS_SLUG.'/?action='.$assignment_id .'">'.$this->get_the_title($assignment_id).'</a>');
	    if(bp_is_active('messages'))
	    bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Assignment results available','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	   
	}
	function student_notification_assignment_evaluation($assignment_id,$marks,$user_id,$max){	
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $assignment_id,
			'component_name'   => 'course',
			'component_action' => 'assignment_evaluation'
		));
	}
	function student_email_assignment_evaluation($assignment_id,$marks,$user_id,$max){	
		$enable = get_user_meta($user_id,'student_assignment_evaluation',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('Results available for assignment %s','vibe'),$this->get_the_title($assignment_id));
			$message = sprintf(__('You obtained %d out of %s in Assignment : %s','vibe'),$marks,$max,'<a href="'.trailingslashit( bp_core_get_user_domain( $user_id )) . BP_COURSE_SLUG. '/'.BP_COURSE_RESULTS_SLUG.'/?action='.$assignment_id .'">'.$this->get_the_title($assignment_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_assignment_evaluation','item_id'=>$assignment_id,'user_id'=>$user_id,'marks'=>$marks,'maximum'=>$max,'tokens'=>array('assignment.name'=>$this->get_the_title($assignment_id),'assignment.titlelink'=>'<a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>','assignment.max'=>$max,'assignment.marks'=>$marks)));	
		}
	}
	function instructor_message_assignment_evaluation($assignment_id,$marks,$user_id,$max){	
		$instructor_ids = $this->get_instructors($assignment_id);
		$message = sprintf(__('Student %s got %s from %s in assignment %s ','vibe'),$this->bp_core_get_userlink($user_id),$marks,$max,$this->get_the_title($assignment_id));
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Assignment %s evaluated for student','vibe'),$this->get_the_title($assignment_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_assignment_evaluation($assignment_id,$marks,$user_id,$max){	
		$instructor_id =$this->get_post_field('post_author', $assignment_id);
		bp_course_add_notification( array(
			'user_id'          => $instructor_id,
			'item_id'          => $assignment_id,
			'secondary_item_id'=> $user_id,
			'component_name'   => 'course',
			'component_action' => 'instructor_assignment_evaluation'
		));
		
	}
	function instructor_email_assignment_evaluation($assignment_id,$marks,$user_id,$max){	
		$enable = get_user_meta($user_id,'instructor_assignment_evaluation',true);
		if($enable !== 'no'){
		$sender_id = $this->get_post_field('post_author', $assignment_id);
		$user = get_user_by( 'id', $sender_id);
		$to = $user->user_email;
		$subject = sprintf(__('Assignment %s evaluated for Student','vibe'),$this->get_the_title($assignment_id));
		$message = sprintf(__('Student %s got %s from %s in assignment %s ','vibe'),$this->bp_core_get_userlink($user_id),$marks,$max,$this->get_the_title($assignment_id));

		bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_assignment_evaluation','item_id'=>$assignment_id,'user_id'=>$user_id,'marks'=>$marks,'maximum'=>$max,'tokens'=>array('assignment.name'=>$this->get_the_title($assignment_id),'assignment.titlelink'=>'<a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id),'assignment.max'=>$max,'assignment.marks'=>$marks)));	
		}
	}
	/* ====== WHEN STUDENT SUBMITS ASSIGNMENT ====== */

	function student_message_assignment_submit($assignment_id,$user_id){	

		if(function_exists('bp_course_messages_new_message')){
	          
	        $sender_id=$this->get_post_field('post_author', $assignment_id);
			$message = sprintf(__('You submitted the assignment : %s','vibe'),$this->get_the_title($assignment_id));
		    if(bp_is_active('messages'))
		    	bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => sprintf(__('You submitted the assignment %s ','vibe'),$this->get_the_title($assignment_id)), 'content' => $message,   'recipients' => $user_id ) );
			   
		}
	}
	function student_notification_assignment_submit($assignment_id,$user_id){	
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $assignment_id,
			'component_name'   => 'course',
			'component_action' => 'assignment_submit'
		));
	}
	function student_email_assignment_submit($assignment_id,$user_id){	
		$enable = get_user_meta($user_id,'student_assignment_submit',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You submitted assignment %s','vibe'),$this->get_the_title($assignment_id));
			$message = sprintf(__('You submitted assignment %s','vibe'),' <a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_assignment_submit','item_id'=>$assignment_id,'user_id'=>$user_id,'tokens'=>array('assignment.name'=>$this->get_the_title($assignment_id),'assignment.titlelink'=>'<a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>')));	
		}
	}
	function instructor_message_assignment_submit($assignment_id,$user_id){	
		$instructor_ids = $this->get_instructors($assignment_id);
		$course_id = get_post_meta($assignment_id,'vibe_assignment_course',true);
		if(isset($course_id) && is_numeric($course_id)){
			$quiz_link ='<a href="'.$this->get_permalink($course_id).'?action=admin&submissions">'.$this->get_the_title($assignment_id).'</a>';
		}else{
			$assignment_link =$this->get_the_title($assignment_id);
		}
		$message = sprintf(__('Assignment %s submitted by student %s','vibe'),$assignment_link,$this->bp_core_get_userlink($user_id));

		bp_course_messages_new_message( array('sender_id' =>  $user_id, 'subject' => sprintf(__('Assignment %s submitted by Student','vibe'),$this->get_the_title($assignment_id)), 'content' => $message,   'recipients' => $instructor_ids) );
	}
	function instructor_notification_assignment_submit($assignment_id,$user_id){	
		$instructor_ids = $this->get_instructors($assignment_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $assignment_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_assignment_submit'
			));
		}
	}	
	function instructor_email_assignment_submit($assignment_id,$user_id){
		$to = $this->instructor_emails($assignment_id,'assignment_submit');
		$subject = sprintf(__('Student submitted assignment %s','vibe'),$this->get_the_title($assignment_id));
		$message = sprintf(__('Student %s submitted assignment %s','vibe'),$this->bp_core_get_userlink($user_id),' <a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>');
		if(count($to))
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_assignment_submit','item_id'=>$assignment_id,'user_id'=>$user_id,'tokens'=>array('assignment.name'=>$this->get_the_title($assignment_id),'assignment.titlelink'=>'<a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));	
	}

	/* ====== WHEN STUDENT ASSIGNMENT  IS RESET ====== */
	function student_message_assignment_reset($assignment_id,$user_id){
		$sender_id=$this->get_post_field('post_author', $assignment_id);
		$message = sprintf(__('Assignment %s was reset by Instructor','vibe'),' <a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	    	bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Assignment reset by Instructor','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}
	function student_notification_assignment_reset($assignment_id,$user_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $assignment_id,
			'component_name'   => 'course',
			'component_action' => 'assignment_reset'
		));
	}
	function student_email_assignment_reset($assignment_id,$user_id){
		$enable = get_user_meta($user_id,'student_assignment_reset',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('Assignment %s reset','vibe'),$this->get_the_title($assignment_id));
			$message = sprintf(__('Assignment %s was reset by Instructor','vibe'),' <a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_assignment_reset','item_id'=>$assignment_id,'user_id'=>$user_id,'tokens'=>array('assignment.name'=>$this->get_the_title($assignment_id),'assignment.titlelink'=>'<a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>')));	
		}
	}
	function instructor_message_assignment_reset($assignment_id,$user_id){
		$instructor_ids = $this->get_instructors($assignment_id);
		$message = sprintf(__('Assignment %s reset for Student %s','vibe'),' <a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>',$this->bp_core_get_userlink($user_id));
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
			bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => sprintf(__('Assignment %s reset for Student','vibe'),$this->get_the_title($quiz_id)), 'content' => $message,   'recipients' => $instructor_ids ) );
	}
	function instructor_notification_assignment_reset($assignment_id,$user_id){
		$instructor_ids = $this->get_instructors($assignment_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $assignment_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_assignment_reset'
			));
		}

	}
	
	function instructor_email_assignment_reset($quiz_id,$user_id){
		$to = $this->instructor_emails($assignment_id,'assignment_reset');
		$subject = sprintf(__('Assignment %s reset for Student','vibe'),$this->get_the_title($assignment_id));
		$message = sprintf(__('Assignment %s was reset for Student %s','vibe'),' <a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>',$this->bp_core_get_userlink($user_id));
		if(count($to))
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_assignment_reset','item_id'=>$assignment_id,'user_id'=>$user_id,'tokens'=>array('assignment.name'=>$this->get_the_title($assignment_id),'assignment.titlelink'=>'<a href="'.$this->get_permalink($assignment_id).'">'.$this->get_the_title($assignment_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));	
	}

	/* == Course Application functions === */
	function student_message_user_course_application($course_id,$user_id){
		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('You applied to course %s ','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
			bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('You applied for Course','vibe'), 'content' => $message,   'recipients' => $user_id ) );
	}

	function student_notification_user_course_application($course_id,$user_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $course_id,
			'component_name'   => 'course',
			'component_action' => 'user_course_application'
		));
	}

	function student_email_user_course_application($course_id,$user_id){
		$enable = get_user_meta($user_id,'user_course_application',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('You applied to course %s ','vibe'),$this->get_the_title($course_id));
			$message = sprintf(__('You have applied to Course %s ','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_user_course_application','item_id'=>$course_id,'user_id'=>$user_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>')));	
		}
	}

	function instructor_message_user_course_application($course_id,$user_id){
		$sender_id= $user_id;
		$receiver_id = $this->get_post_field('post_author', $course_id);
		$message = sprintf(__('Student %s applied to course %s ','vibe'),$this->bp_core_get_userlink($user_id),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
			bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Student applied for Course','vibe'), 'content' => $message,   'recipients' => $receiver_id ) );
	}

	function instructor_notification_user_course_application($course_id,$user_id){
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $course_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_user_course_application'
			));
		}
	}

	function instructor_email_user_course_application($course_id,$user_id){
		$to = $this->instructor_emails($course_id,'user_course_application');
		$subject = sprintf(__('Application for course %s was submitted by Student','vibe'),$this->get_the_title($course_id));
		$message = sprintf(__('Student %s submitted an application for course %s','vibe'),$this->bp_core_get_userlink($user_id),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_user_course_application','item_id'=>$course_id,'user_id'=>$user_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id))));	
	}

	/* == */
	function student_message_manage_user_application($action,$course_id,$user_id){

		$action_label = _x('Approved','course application status in email','vibe');
		if($action != 'approve'){
			$action_label = _x('Rejected','course application status in email','vibe');	
		}

		$sender_id=$this->get_post_field('post_author', $course_id);
		$message = sprintf(__('Application %s for course %s ','vibe'),$action_label,' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
	      bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => sprintf(__('Application %s by Instructor','vibe'),$action_label), 'content' => $message,   'recipients' => $user_id ) );
	}

	function student_notification_manage_user_application($action,$course_id,$user_id){
		bp_course_add_notification( array(
			'user_id'          => $user_id,
			'item_id'          => $course_id,
			'component_name'   => 'course',
			'component_action' => 'manage_user_application_'.$action
		));
	}

	function student_email_manage_user_application($action,$course_id,$user_id){
		$enable = get_user_meta($user_id,'manage_user_application',true);
		if($enable !== 'no'){

			$action_label = _x('Approved','course application status in email','vibe');
			if($action != 'approve'){
				$action_label = _x('Rejected','course application status in email','vibe');	
			}

			$user = get_user_by( 'id', $user_id);
			$to = $user->user_email;
			$subject = sprintf(__('Application %s to course %s ','vibe'),$action_label,$this->get_the_title($course_id));

			$message = sprintf(__('Your application was %s for Course %s ','vibe'),$action_label,' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
			bp_course_wp_mail($to,$subject,$message,array('action'=>'student_manage_user_application','item_id'=>$course_id,'user_id'=>$user_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','course.application_status'=>$action_label)));	
		}
	}

	function instructor_message_manage_user_application($action,$course_id,$user_id){
		$sender_id= $user_id;
		$receiver_id = $this->get_post_field('post_author', $course_id);

		$action_label = _x('Approved','course application status in email','vibe');
		if($action != 'approve'){
			$action_label = _x('Rejected','course application status in email','vibe');	
		}

		$message = sprintf(__('Application %s for course %s ','vibe'),$action_label,' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
			bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => sprintf(__('Application %s by Instructor','vibe'),$action_label), 'content' => $message,   'recipients' => $receiver_id ) );
	}

	function instructor_notification_manage_user_application($action,$course_id,$user_id){
		$instructor_ids = $this->get_instructors($course_id);
		foreach($instructor_ids as $instructor_id){
			bp_course_add_notification( array(
				'user_id'          => $instructor_id,
				'item_id'          => $course_id,
				'secondary_item_id'=> $user_id,
				'component_name'   => 'course',
				'component_action' => 'instructor_manage_user_application_'.$action
			));
		}
	}

	function instructor_email_manage_user_application($action,$course_id,$user_id){

		$action_label = _x('Approved','course application status in email','vibe');
		if($action != 'approve'){
			$action_label = _x('Rejected','course application status in email','vibe');	
		}
		
		$to = $this->instructor_emails($course_id,'manage_user_application');
		$subject = sprintf(__('Student application for course %s was %s','vibe'),$this->get_the_title($course_id),$action_label);
		$message = sprintf(__('Application by Student %s was %s for course %s','vibe'),$this->bp_core_get_userlink($user_id),$action_label,' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		if(count($to))
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_manage_user_application','item_id'=>$course_id,'user_id'=>$user_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','student.userlink'=>$this->bp_core_get_userlink($user_id),'student.name'=>$this->bp_core_get_user_displayname($user_id),'course.application_status'=>$action_label)));	
	}

	/* ==== Course Published or Sent for Approval ===== */

	function instructor_message_course_go_live($course_id,$the_post){
		
		$sender_id= $the_post['post_author'];
		$status = '';
		if($the_post['post_status'] == 'publish'){
			$message = sprintf(__('You published the course %s ','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		}else{
			$message = sprintf(__('Course %s sent for approval from admin','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		}
		
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
			bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Course status updated','vibe'), 'content' => $message,   'recipients' => $sender_id ) );

	}

	function instructor_notification_course_go_live($course_id,$the_post){
		$instructor_id = $the_post['post_author'];
		bp_course_add_notification( array(
			'user_id'          => $instructor_id,
			'item_id'          => $course_id,
			'component_name'   => 'course',
			'component_action' => 'course_go_live_'.$the_post['post_status']
		));
	}

	function instructor_email_course_go_live($course_id,$the_post){
		$user = get_user_by( 'id', $the_post['post_author']);
		$to = $user->user_email;
		if($the_post['post_status'] == 'publish'){
			$subject = sprintf(__('Course %s published','vibe'),$the_post['post_title']);
			$message = sprintf(__('You published the course %s ','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		}else{
			$subject = sprintf(__('Course %s sent for Admin approval','vibe'),$the_post['post_title']);
			$message = sprintf(__('Course %s sent for approval from admin','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		}
		if(count($to))
			bp_course_wp_mail($to,$subject,$message,array('action'=>'instructor_course_go_live','item_id'=>$course_id,'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','course.publish_status'=>(($the_post['post_status'] == 'publish')?_x('published','course status when instructor sends for approval','vibe'):_x('drafted','course status when instructor sends for approval','vibe')))));	
	}

	function admin_message_course_go_live($course_id,$the_post){

		if(empty($this->admins))
			$this->admins = $this->get_admins();

		$admins = $this->admins;
		$admin_ids = array();
		foreach($admins as $admin) {
			$admin_ids[] = $admin['ID'];
		}
		$sender_id= $the_post['post_author'];
		$status = '';
		if($the_post['post_status'] == 'publish'){
			$message = sprintf(__('Instructor published the course %s ','vibe'),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		}else{
			$message = sprintf(__('Instructor %s sent the Course %s for approval','vibe'),$this->bp_core_get_userlink($sender_id),' <a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>');
		}
		
	    if(bp_is_active('messages') && function_exists('bp_course_messages_new_message'))
			bp_course_messages_new_message( array('sender_id' => $sender_id, 'subject' => __('Instructor changed the Course status','vibe'), 'content' => $message,   'recipients' => $admin_ids ) );
	}

	function admin_notification_course_go_live($course_id,$the_post){
		
		if(empty($this->admins))
			$this->admins = $this->get_admins();

		$admins = $this->admins;

		$admin_ids = array();
		foreach($admins as $admin) {
			$admin_ids[] = $admin['ID'];
		}

		foreach($admin_ids as $admin_id){
			bp_course_add_notification( array(
				'user_id'          => $admin_id,
				'item_id'          => $course_id,
				'secondary_item_id'=> $the_post['post_author'],
				'component_name'   => 'course',
				'component_action' => 'admin_course_go_live_'.$the_post['post_status']
			));
		}
	}

	function admin_email_course_go_live($course_id,$the_post){
		
		if(empty($this->admins))
			$this->admins = $this->get_admins();

		$admins = $this->admins;

		$admin_ids = array();
		foreach($admins as $admin) {
			$to[] = $admin['email'];
		}

		$subject = sprintf(__('Instructor changed status for Course %s to %s','vibe'),$this->get_the_title($course_id),$the_post['post_status']);
		$message = sprintf(__('Instructor %s changed status for Course %s to %s','vibe'),$this->bp_core_get_userlink($the_post['post_author']),$this->get_the_title($course_id),$the_post['post_status']);
		if(count($to))
			bp_course_wp_mail($to,$subject,$message,array('action'=>'admin_course_go_live','item_id'=>$course_id,'user_id'=>$the_post['post_author'],'tokens'=>array('course.name'=>$this->get_the_title($course_id),'course.titlelink'=>'<a href="'.$this->get_permalink($course_id).'">'.$this->get_the_title($course_id).'</a>','course.instructorlink'=>$this->bp_core_get_userlink($the_post['post_author']),'course.publish_status'=>(($the_post['post_status'] == 'publish')?_x('Approved','course status when instructor sends for approval','vibe'):_x('Under review','course status when instructor sends for approval','vibe')))));	
	}
}

bp_course_notifications::init();	

//* === CALL BACK NOTIFICATION DESCRIPTIONS *===/

function bp_course_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

	$notifications = bp_course_notifications::init();

	switch ( $action ) {
		case 'course_announcement':
			return sprintf(__('Announcement for Course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_course_announcement':
			return sprintf(__('Announcement for Course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'bulk_action':
			return sprintf(__('Added to Course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_bulk_action':
			return sprintf(__('Student %s added to Course %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'bulk_action':
			return sprintf(__('Added to Course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_bulk_action':
			return sprintf(__('Student %s added to Course %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'subscribe_course':
			return sprintf(__('You\'ve subscribed to Course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_subscribe_course':
			return sprintf(__('Student %s subscribed to Course %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'course_start':
			return sprintf(__('You started the course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_course_start':
			return sprintf(__('Student %s started course %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'course_submit':
			return sprintf(__('You submitted the course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_course_submit':
			return sprintf(__('Student %s submitted course %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'course_reset':
			return sprintf(__('Student %s submitted course %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_course_reset':
			return sprintf(__('Course %s was reset for Student %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>',$notifications->bp_core_get_userlink($secondary_item_id));
		break;
		case 'course_retake':
			return sprintf(__('Student %s retook the course course %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_course_retake':
			return sprintf(__('Course %s was retaken by Student %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>',$notifications->bp_core_get_userlink($secondary_item_id));
		break;
		case 'course_evaluated':
			return sprintf(__('Course %s evaluated for Student %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>',$notifications->bp_core_get_userlink($secondary_item_id));
		break;
		case 'instructor_course_evaluated':
			return sprintf(__('Course %s evaluated for Student %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>',$notifications->bp_core_get_userlink($secondary_item_id));
		break;
		case 'course_badge':
			return sprintf(__('Congratulations ! You got a badge in Course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_course_badge':
			return sprintf(__('Student %s got a Badge in Course %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'course_certificate':
			return sprintf(__('Congratulations ! You got a certificate in Course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_course_certificate':
			return sprintf(__('Student %s got a Certificate in Course %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'course_review':
			return sprintf(__('You submitted a review for Course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_course_review':
			return sprintf(__('Student %s submitted a review for Course %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'course_unsubscribe':
			return sprintf(__('You\'re unsubscribed from Course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_course_unsubscribe':
			return sprintf(__('Student %s is unsubscribed from Course %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'unit_complete':
			return sprintf(__('You completed the unit %s in course %s','vibe'),$notifications->get_the_title($item_id),'<a href="'.$notifications->get_permalink($secondary_item_id).'">'.$notifications->get_the_title($secondary_item_id).'</a>');
		break;
		case 'instructor_unit_complete':
			return sprintf(__('Student %s completed the unit %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'unit_instructor_complete':
			return sprintf(__('Instructor marked Unit %s complete for you.','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_unit_instructor_complete':
			return sprintf(__('Instructor marked Unit %s complete for Student %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>',$notifications->bp_core_get_userlink($secondary_item_id));
		break;
		case 'unit_instructor_uncomplete':
			return sprintf(__('Instructor marked Unit %s incomplete for you.','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_unit_instructor_uncomplete':
			return sprintf(__('Instructor marked Unit %s incomplete for Student %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>',$notifications->bp_core_get_userlink($secondary_item_id));
		break;
		case 'unit_comment':
			return sprintf(__('You added a comment in unit %s in course %s','vibe'),$notifications->get_the_title($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_unit_comment':
			return sprintf(__('Student %s added a comment in unit %s in course %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($secondary_item_id).'">'.$notifications->get_the_title($secondary_item_id).'</a>','<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'unit_comment_reply':
			$course_id = bp_course_get_unit_course_id($item_id);
			return sprintf(__('%s replied on your comment in unit "%s" in Course : %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),$notifications->get_the_title($item_id),'<a href="'.$notifications->get_permalink($course_id).'">'.$notifications->get_the_title($course_id).'</a>');
		break;
		case 'start_quiz':
			return sprintf(__('You started the quiz %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_start_quiz':
			return sprintf(__('Student %s started the quiz %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'quiz_submit':
			return sprintf(__('You submitted the quiz %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_quiz_submit':
			return sprintf(__('Student %s submitted the quiz %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'quiz_evaluation':
			return sprintf(__('Quiz %s results available','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_quiz_evaluation':
			return sprintf(__('Quiz %s results available for student %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>',$notifications->bp_core_get_userlink($secondary_item_id));
		break;
		case 'quiz_reset':
			return sprintf(__('Quiz %s reset by Instructor','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_quiz_reset':
			return sprintf(__('Quiz %s reset for student %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>',$notifications->bp_core_get_userlink($secondary_item_id));
		break;
		case 'quiz_retake':
			return sprintf(__('You retook the quiz %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_quiz_retake':
			return sprintf(__('Student %s retook the Quiz %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'start_assignment':
			return sprintf(__('You started the assignment %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_start_assignment':
			return sprintf(__('Student %s started the assignment %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'assignment_submit':
			return sprintf(__('You submitted the assignment %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_assignment_submit':
			return sprintf(__('Student %s submitted the assignment %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'assignment_evaluation':
			return sprintf(__('Assignment %s results available','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_assignment_evaluation':
			return sprintf(__('Assignment %s results available for student %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>',$notifications->bp_core_get_userlink($secondary_item_id));
		break;
		case 'assignment_reset':
			return sprintf(__('Assignment %s reset by Instructor','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_assignment_reset':
			return sprintf(__('Assignment %s reset for student %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>',$notifications->bp_core_get_userlink($secondary_item_id));
		break;
		case 'course_news':
            return sprintf(__('Latest News : %s for Course : %s','vibe'),'<a href="'.$notifications->get_permalink($secondary_item_id).'">'.$notifications->get_the_title($secondary_item_id).'</a>','<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_course_news' : 
			return sprintf(__('News : %s was published for your course : %s','vibe'),$notifications->get_the_title($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'user_course_application':
			return sprintf(__('You applied for course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_user_course_application':
			return sprintf(__('Student %s applied for course : %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'manage_user_application_approve':
			return sprintf(__('Application approved for course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_manage_user_application_approve':
			return sprintf(__('Student %s application approved for course : %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'manage_user_application_reject':
			return sprintf(__('Application rejected for course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'instructor_manage_user_application_reject':
			return sprintf(__('Student %s application rejected for course : %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'course_go_live_publish':
			return sprintf(__('You published the course %s','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>',$notifications->bp_core_get_userlink($user_id));
		break;
		case 'course_go_live_pending':
			return sprintf(__('You sent the course %s for approval','vibe'),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>',$notifications->bp_core_get_userlink($user_id));
		break;
		case 'admin_course_go_live_publish':
			return sprintf(__('Instructor %s published the course %s','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
		case 'admin_course_go_live_pending':
			return sprintf(__('Instructor %s sent the course %s for approval','vibe'),$notifications->bp_core_get_userlink($secondary_item_id),'<a href="'.$notifications->get_permalink($item_id).'">'.$notifications->get_the_title($item_id).'</a>');
		break;
	}

	return apply_filters( 'bp_course_format_notifications',false, $action, $item_id, $secondary_item_id, $total_items );
}

?>