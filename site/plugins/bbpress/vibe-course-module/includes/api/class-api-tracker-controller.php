<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'BP_Course_Rest_Tracker_Controller' ) ) {
	
	class BP_Course_Rest_Tracker_Controller{

		public $tracker;
		public $usertracker;
		
		public static $instance;
		public static function init(){

	        if ( is_null( self::$instance ) )
	            self::$instance = new BP_Course_Rest_Tracker_Controller();
	        return self::$instance;
	    }

	    function __construct(){

	    	$this->tracker = $this->user_tracker = array();
	    	//Update tracker codes
	    	add_action('create_course-cat',array($this,'reset_allcoursecategories'));
	    	add_action('create_level',array($this,'reset_allcourselevels'));
	    	add_action('create_location',array($this,'reset_allcourselocations'));
	    	add_action('delete_course-cat',array($this,'reset_allcoursecategories'));
	    	add_action('delete_level',array($this,'reset_allcourselevels'));
	    	add_action('delete_location',array($this,'reset_allcourselocations'));
	    	//featured course
	    	add_action('update_featured_course',array($this,'update_featured'));
	    	add_action('wplms_start_course',array($this,'update_popular'));
	    	//allcourses
	    	add_action('wp_insert_post',array($this,'check_post_updates'),10,2);
	    	add_action('save_post',array($this,'check_post_updates'),10,2);
	    	add_action('wplms_front_end_save_course_settings',array($this,'update_course'),10,1);
	    	add_action('wplms_front_end_save_course_pricing',array($this,'update_course'),10,1);
	    	add_action('wplms_course_curriculum_updated',array($this,'update_course'),10,1);
	    	add_action('wplms_course_curriculum_updated',array($this,'update_course_status'),10,1);
	    	//', $post_ID, $post
	    	//allinstructors
	    	
	    	//save_post_{$post->post_type}", $post_ID, $post
	    	//USER specific
	    	add_action('wplms_course_subscribed',array($this,'reload_mycourses'),10,2);

			add_action('wplms_start_course',array($this,'reload_mycourses'),10,2);
			add_action('wplms_submit_course',array($this,'reload_mycourses'),10,2);
			add_action('wplms_evaluate_course',array($this,'reload_mycourses'),10,3); 

			
			add_action('wplms_course_reset',array($this,'reload_mycourses'),10,2);
			add_action('wplms_course_retake',array($this,'reload_mycourses'),10,2);
			
			add_action('wplms_bulk_action',array($this,'reload_mycourses_extend'),10,3);
			
	 		add_action('wplms_badge_earned',array($this,'reload_myprofiletab'),10,3);
	 		add_action('wplms_certificate_earned',array($this,'reload_myprofiletab'),10,3);

	 		add_action('wplms_course_review',array($this,'update_course'),10,1);
	 		
			add_action('wplms_course_unsubscribe',array($this,'reload_mycourses'),10,3);
			add_action('wplms_renew_course',array($this,'reload_mycourses'),10,2);

			add_action('wplms_start_unit',array($this,'reload_statusitem'),10,3);
			
			add_action('wplms_unit_complete',array($this,'reload_statusitem_complete'),10,4);
			add_action('wplms_unit_instructor_complete',array($this,'reload_statusitem'),10,3);
			add_action('wplms_unit_instructor_uncomplete',array($this,'reload_statusitem'),10,3);

			//add_action('wplms_course_unit_comment',array($this,'unit_comment'),10,3);

			add_action('wplms_start_quiz',array($this,'reload_statusitem_quiz'),10,2);
			add_action('wplms_submit_quiz',array($this,'reload_statusitem_quiz'),10,2);
			add_action('wplms_quiz_retake',array($this,'reload_statusitem_quiz'),10,2);
			add_action('wplms_quiz_reset',array($this,'reload_statusitem_quiz'),10,2);
			add_action('wplms_evaluate_quiz',array($this,'evaluate_quiz'),10,4);

			add_action('bp_activity_add',array($this,'reload_activity'));
			add_filter('bp_course_format_notifications',array($this,'reload_notifications'));


			add_action('wp_ajax_api_send_notice_to_user',array($this,'wplms_api_send_notice_to_user'));

			add_action('wp_ajax_api_send_notice_to_all',array($this,'wplms_api_send_notice_to_all'));

			/* WALLET */
			add_filter('wplms_wallet_transaction_status',array($this,'transaction_status'),10,2);
			add_action('wplms_wallet_transaction',array($this,'wplms_api_record_trasaction_count_in_tracker'),10,1);
	    }


	    /*
	    
	    Generic functions
	     */
	    function get_tracker(){

	    	if(empty($this->tracker)){
	    		$this->tracker = get_option('wplms_api_tracker');	
	    	}
	    	return $this->tracker;
	    }
	    function update_tracker(){
	    	if(empty($this->tracker['counter'])){
	    		$this->tracker['counter'] =0;
	    	}
	    	$this->tracker['counter']++;
	    	update_option('wplms_api_tracker',$this->tracker);	
	    }

	    function get_user_tracker($user_id){

	    	if(empty($this->user_tracker)){
	    		$this->user_tracker = get_user_meta($user_id,'wplms_api_tracker',true);	
	    	}

	    	return $this->user_tracker;
	    }
	    
	    function update_user_tracker($user_id){
	    	
	    	if(empty($this->user_tracker)){
	    		$this->user_tracker = array('counter'=>0);
	    	}
	    	if(empty($this->user_tracker['counter'])){
	    		$this->user_tracker['counter'] = 0;
	    	}
	    	$this->user_tracker['counter']=(int)$this->user_tracker['counter']+1;
	    	update_user_meta($user_id,'wplms_api_tracker',$this->user_tracker);
	    }
	    /*
	    TRACKER UPDATE FUNCTIONS
	    */
	    
	    // ALL COURSE CATEGORIES
	    function reset_allcoursecategories(){
	    	
    		$this->get_tracker();
	    	$this->tracker['allcoursecategories'] = time();//save counter state
	    	$this->update_tracker();
	    }
	    function reset_allcourselevels(){
	    	$this->get_tracker();
	    	$this->tracker['allcourselevels'] = time();
	    	$this->update_tracker();
	    }
	    function reset_allcourselocations(){
	    	$this->get_tracker();
	    	$this->tracker['allcourselocations'] = time();
	    	$this->update_tracker();
	    }

	    // All course count
	    function check_post_updates($post_id,$post){
	    	
	    	$this->get_tracker();

	    	if($post->post_type == 'course'){
	    		$allcourses = wp_count_posts('course');
		    	$this->tracker['allcourses'] = intval($allcourses->publish);//save counter state

		    	if(empty($this->tracker['courses'])){
		    		$this->tracker['courses'] = array($post_id=>time());
		    	}else if(!in_array($post_id,$this->tracker['courses'])){
		    		$this->tracker['courses'][$post_id] = time();	
		    	}
	    	}

	    	if(in_array($post->post_type,array('quiz','unit'))){
	    		if(empty($this->tracker['statusitems'])){
		    		$this->tracker['statusitems'] = array($post_id=>time());
		    	}else if(!in_array($post_id,$this->tracker['statusitems'])){
		    		$this->tracker['statusitems'][$post_id]=time();
		    	}
	    	}

	    	if(in_array($post->post_type,array('post'))){
	    		if(empty($this->tracker['posts'])){
		    		
		    		$this->tracker['posts'] = array($post_id => time());

		    	}else if(!in_array($post_id,$this->tracker['posts'])){
		    		$this->tracker['posts'][$post_id]=time();
		    	}
		    	$allposts = wp_count_posts('post');
		    	$this->tracker['blog'] = intval($allposts->publish);
	    	}
	    	$this->update_tracker();
	    }

	    //Track updated course
	    function update_course($course_id){
	    	$this->get_tracker();

	    	$allcourses = wp_count_posts('course');
	    	$this->tracker['allcourses'] = intval($allcourses->publish);

	    	if(empty($this->tracker['courses'])){
	    		$this->tracker['courses']=array($course_id=>time());
	    	}else if(!in_array($course_id,$this->tracker['courses'])){
	    		$this->tracker['courses'][$course_id]=time();	
	    	}
	    	
	    	$this->update_tracker();
	    }

	    function update_course_status($course_id){
	    	$this->get_tracker();
	    	if(empty($this->tracker['course_status'])){
	    		$this->tracker['course_status']=array();
	    	}
	    	if(empty($this->tracker['courses'])){
	    		$this->tracker['courses']=array();
	    	}
	    	$this->tracker['courses'][$course_id]=time();
	    	$this->tracker['course_status'][$course_id]=time();
	    	$this->update_tracker();
	    }
	    //Remove featured
	    function update_featured(){
	    	$this->get_tracker();
	    	$this->tracker['featured']=time();
	    	$this->update_tracker();
	    }
	    //Remove Popular
	    function update_popular(){
	    	$this->get_tracker();
	    	$this->tracker['popular']=time();
	    	$this->update_tracker();
	    }


	    function reload_mycourses($course_id,$user_id){
	    	$this->get_user_tracker($user_id);

	    	if(empty($this->user_tracker)){
	    		$this->user_tracker =array();
	    	}
	    	if(empty($this->user_tracker['profiletabs'])){
	    		$this->user_tracker['profiletabs'] = array('courses'=>time());
	    	}else if(!in_array('courses',$this->user_tracker['profiletabs'])){
	    		$this->user_tracker['profiletabs']['courses']=time();
	    	}
	    	/* course_status also */
	    	if(empty($this->user_tracker['course_status'])){
	    		$this->user_tracker['course_status']=array();
	    	}
	    	if(isset($this->user_tracker['course_status'][$course_id])){
	    		$this->user_tracker['course_status'][$course_id] = time();
	    	}else{
	    		$this->user_tracker['course_status'] = array(
	    			$course_id => time(),
	    			);
	    	}
	    	
	    	$this->update_user_tracker($user_id);
    	}

    	function reload_mycourses_extend($action,$course_id,$members){
    		if($action != 'extend_course_subscription')
    			return;

    		$this->get_tracker();

	    	

	    	if(empty($this->tracker['courses'])){
	    		$this->tracker['courses']=array($course_id=>time());
	    	}else if(!in_array($course_id,$this->tracker['courses'])){
	    		$this->tracker['courses'][$course_id]=time();	
	    	}
	    	
	    	$this->update_tracker();

    		foreach ($members as $key => $user_id) {
    			$this->get_user_tracker($user_id);
    			if(empty($this->user_tracker)){
		    		$this->user_tracker =array();
		    	}
		    	if(empty($this->user_tracker['profiletabs'])){
		    		$this->user_tracker['profiletabs'] = array('courses'=>time());
		    	}else if(!in_array('courses',$this->user_tracker['profiletabs'])){
		    		$this->user_tracker['profiletabs']['courses']=time();
		    	}
		    	$this->update_user_tracker($user_id);
    		}
    	}

		function reload_evaluate_course($course_id,$marks,$user_id){
			$this->get_user_tracker($user_id);
	    	if(empty($this->user_tracker['profiletabs'])){
	    		$this->user_tracker['profiletabs'] = array(array('time'=>time(),'value'=>'courses'));
	    	}else if(!in_array('courses',$this->user_tracker['profiletabs'])){
	    		$this->user_tracker['profiletabs'][]=array('time'=>time(),'value'=>'courses');
	    	}
	    	$this->update_user_tracker($user_id);
		}

		function reload_myprofiletab($course_id,$badges,$user_id){
			$this->get_user_tracker($user_id);
	    	if(empty($this->user_tracker['profiletabs'])){
	    		$this->user_tracker['profiletabs'] = array('dashboard'=>time());
	    	}else if(!in_array('dashboard',$this->user_tracker['profiletabs'])){
	    		$this->user_tracker['profiletabs']['dashboard']=time();
	    	}
	    	$this->update_user_tracker($user_id);
 		}


		function reload_statusitem($unit_id,$course_id,$user_id){
			$this->get_user_tracker($user_id);
	    	if(empty($this->user_tracker['statusitems'])){
	    		$this->user_tracker['statusitems'] = array($unit_id=>time());
	    	}else if(!in_array($unit_id,$this->user_tracker['statusitems'])){
	    		$this->user_tracker['statusitems'][$unit_id]=time();
	    	}
	    	$this->update_user_tracker($user_id);
		}

		function reload_statusitem_complete($unit_id,$course_progress,$course_id,$user_id){
			$this->get_user_tracker($user_id);
	    	if(empty($this->user_tracker['statusitems'])){
	    		$this->user_tracker['statusitems'] = array($unit_id=>time());
	    	}else if(!in_array($unit_id,$this->user_tracker['statusitems'])){
	    		$this->user_tracker['statusitems'][$unit_id]=time();
	    	}
	    	$this->update_user_tracker($user_id);
		}

		function reload_statusitem_quiz($quiz_id,$user_id){
			$this->get_user_tracker($user_id);
	    	if(empty($this->user_tracker['statusitems'])){
	    		$this->user_tracker['statusitems'] = array($quiz_id=>time());
	    	}else if(!in_array($quiz_id,$this->user_tracker['statusitems'])){
	    		$this->user_tracker['statusitems'][$quiz_id]=time();
	    	}
	    	$this->update_user_tracker($user_id);
		}

		function evaluate_quiz($quiz_id,$marks,$user_id,$max){
			$this->get_user_tracker($user_id);
	    	if(empty($this->user_tracker['statusitems'])){
	    		$this->user_tracker['statusitems'] = array($quiz_id=>time());
	    	}else if(!in_array($quiz_id,$this->user_tracker['statusitems'])){
	    		$this->user_tracker['statusitems'][$quiz_id]=time();
	    	}
	    	if(empty($this->user_tracker['saved_results'])){
	    		$this->user_tracker['saved_results'] = array($quiz_id=>time());
	    	}else{
	    		$this->user_tracker['saved_results'][$quiz_id]=time();
	    	}
	    	$this->update_user_tracker($user_id);
		}

		function reload_activity(){
	    	$user_id = get_current_user_id();
	    	$this->get_user_tracker($user_id);
	    	if(empty($this->user_tracker)){
	    		$this->user_tracker = array('profiletabs'=>array());
	    	}
	    	if(empty($this->user_tracker['profiletabs'])){
	    		$this->user_tracker['profiletabs'] = array('activity'=>time());
	    	}else if(!in_array('activity',$this->user_tracker['profiletabs'])){
	    		$this->user_tracker['profiletabs']['activity']=time();
	    	}
	    	$this->update_user_tracker($user_id);
	    }

	    function reload_notifications($x){
			$user_id = get_current_user_id();
	    	$this->get_user_tracker($user_id);
	    	if(empty($this->user_tracker['profiletabs'])){
	    		$this->user_tracker['profiletabs'] = array('notifications'=>time());
	    	}else if(!in_array('notifications',$this->user_tracker['profiletabs'])){
	    		$this->user_tracker['profiletabs']['notifications']=time();
	    	}
	    	$this->update_user_tracker($user_id);  	
			return $x;
	    }


	    /*
	    Send user notice
	     */

	    function wplms_api_send_notice_to_user(){
			
			if(!current_user_can('edit_posts'))
				die();

			$client_id = $_POST['client_id'];
			$user_id = $_POST['user_id'];
			if(is_numeric($user_id)){
				$this->get_user_tracker($user_id);
				
				if(empty($this->user_tracker['updates'])){
		    		 $this->user_tracker['updates'] = array(array('time'=>time(),'content'=>$_POST['message']));
		    		 
		    	}else {
		    		$updates_array = $this->user_tracker['updates'];
		    		$updates_array[] = array('time'=>time(),'content'=>$_POST['message']);

					function cmp( $a, $b ) { 
					  if(  $a['time'] ==  $b['time'] ){ return 0 ; } 
					  return ($a['time'] < $b['time']) ? 1 : -1;
					} 

					usort($updates_array,'cmp');
		    		$this->user_tracker['updates'] = $updates_array;
		    	}

		    	_e('Update sent!','vibe');
		    	$this->update_user_tracker($user_id); 
			}
			
			die();
		}

		function wplms_api_send_notice_to_all(){
			if(!current_user_can('edit_posts'))
				die();
			if(!empty($_POST['message'])){
				$this->get_tracker();
				if(empty($this->tracker['updates'])){
		    		 $this->tracker['updates'] = array(array('time'=>time(),'content'=>$_POST['message']));
		    		 
		    	}else {
		    		$updates_array = $this->tracker['updates'];
		    		$updates_array[] = array('time'=>time(),'content'=>$_POST['message']);

					function cmp( $a, $b ) { 
					  if(  $a['time'] ==  $b['time'] ){ return 0 ; } 
					  return ($a['time'] < $b['time']) ? 1 : -1;
					} 

					usort($updates_array,'cmp');
		    		$this->tracker['updates'] = $updates_array;
		    	}

		    	_e('Update sent!','vibe');
		    	$this->update_tracker(); 
			}
			
			die();
		}

		/*
			API INTERFACE
		*/
		function fetch_tracker($timestamp,$user_id=null){

			if(!empty($user_id)){
				$tracker = $this->get_user_tracker($user_id);
			}else{
				$tracker = $this->get_tracker();	
			}
			
			if(empty($timestamp)){
				return $tracker;
			}

			

			if(!empty($tracker)){
				$newTracker = array();
				foreach($tracker as $key => $item){
					switch($key){
						case 'featured':
						case 'popular':
							if($timestamp < $item){
								$newTracker[$key]=1;
							}
						break;
						case 'courses':
						case 'posts':
						case 'statusitems':
						case 'profiletabs':
							if(is_array($item)){
								$newTracker[$key] = array();
								foreach($item as $id=>$stored_timestamp){
									if($timestamp < $stored_timestamp){
										$newTracker[$key][]=$id;
									}
								}
								if(empty($newTracker[$key])){
									unset($newTracker[$key]);
								}
							}
						break;
						default:
							$newTracker[$key]=$item;
						break;
					}
				}
				return $newTracker;
			}

			return $tracker;
		}

		/*
			WALLET FUNCTIONS
		*/

		function transaction_status($status,$args){
			if(empty($args) || $status != 'success'){
				return $status;
			}
			switch($args['status']){
				case 'debit':
					if(!empty($args['more']) && !empty($args['more']['type'])){
						
						switch($args['more']['type']){

							case 'subscribe_course':
								if(!empty($args['more']['pricing']['extras'])){
									foreach($args['more']['pricing']['extras'] as $val){
										if($val['id'] == 'subscription'){
											bp_course_add_user_to_course($args['userid'],$args['more']['course'],$val['value']);
										}
									}
								}
							break;
						}
					}
				break;
				case 'credit':
				break;
			}


			return $status;
		}

		/*
		WALLET TRANSACTIONS
		*/
		function wplms_api_record_trasaction_count_in_tracker($args){
			$this->get_user_tracker($args['user_id']);
			global $wpdb,$bp;
			$transaction_count = $wpdb->get_var($wpdb->prepare("
						SELECT count(*)
						FROM {$bp->activity->table_name}
						WHERE user_id = %d 
						AND component = %s",$args['user_id'],'wallet'));

			$this->user_tracker['transactions'] = intval($transaction_count);
			$this->update_user_tracker($args['user_id']); 
		}
	}

	BP_Course_Rest_Tracker_Controller::init();
}

