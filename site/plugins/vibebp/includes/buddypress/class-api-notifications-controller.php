<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'VIBE_BP_API_Rest_Notifications_Controller' ) ) {
	
	class VIBE_BP_API_Rest_Notifications_Controller extends WP_REST_Controller{
		
		public static $instance;
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new VIBE_BP_API_Rest_Notifications_Controller();
	        return self::$instance;
	    }
	    public function __construct( ) {
			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->type= Vibe_BP_API_NOTIFICATIONS_TYPE;
			$this->register_routes();
		}

		public function register_routes() {
			
			register_rest_route( $this->namespace, '/' .$this->type. '/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_notifications' ),
					'permission_callback' => array( $this, 'get_notifications_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/notification-id/(?P<notification_id>\d+)?', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_get_notification_by_id'),
					'permission_callback' => array( $this, 'get_notifications_permissions' ),
					'args'                     	=>  array(
						'notification_id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/read-unread-notification/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_get_read_unread_notification'),
					'permission_callback' => array( $this, 'get_notifications_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/unread-notification/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_get_unread_notification'),
					'permission_callback' => array( $this, 'get_notifications_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/markall-read-unread/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_markall_read_unread'),
					'permission_callback' => array( $this, 'get_notifications_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/deleteall/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_deleteall'),
					'permission_callback' => array( $this, 'get_notifications_permissions' ),
				),
			));

			
			register_rest_route( $this->namespace, '/'.$this->type .'/mark-read-unread/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_mark_read_unread'),
					'permission_callback' => array( $this, 'get_notifications_permissions' ),
				),
			));


			register_rest_route( $this->namespace, '/'.$this->type .'/delete-notification/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_delete_notification'),
					'permission_callback' => array( $this, 'get_notifications_permissions' ),
				),
			));

		}


		/*
	    PERMISSIONS
	     */
	    function get_notifications_permissions($request){

	    	$body = json_decode($request->get_body(),true);
	       	
	        if (empty($body['token'])){
           		return false;
	        }else{
	        	$token = $body['token'];
	        }
	        /** Get the Secret Key */
	        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
	        if (!$secret_key) {
	          	return false;
	        }
	        /** Try to decode the token */ /** Else return exception*/
	        try {
	            $user_data = JWT::decode($token, $secret_key, array('HS256'));
	            /*
		        avatar: "//www.gravatar.com/avatar/73745bceffd75a7e5a1203d9f0e9fe44?s=150&#038;r=g&#038;d=mm"
				caps: ["subscriber"]
				displayname: "test"
				email: "q@q.com"
				id: "2"
				profile_link: "http://localhost/appointments/members/test"
				slug: "test"
				username: "test"*/
		        $this->user = $user_data->data->user;
		        /** Let the user modify the data before send it back */
	        	return true;

	        }catch (Exception $e) {
	            /** Something is wrong trying to decode the token, send back the error */
	            return false;
	        }
	    	

	    	return false;
	    }

	    function get_notifications($request){
	    	
	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);

	    	$notification_args = array();
	    	$notification_args['per_page'] = 10;
	    	$notification_args['page'] = $args['page'];
	    	$notification_args['user_id'] = $this->user->id;
	    	if($args['filter'] === 'unread'){
	    		$notification_args['is_new'] = 1;
	    	}
	    	if($args['filter'] === 'read'){
	    		$notification_args['is_new'] = 0;
	    	}
	    	if(!empty($args['search'])){
	    		$notification_args['search_terms'] = $args['search'];
	    	}
	    	$run = BP_Notifications_Notification::get_current_notifications_for_user( $notification_args );
    		
    		if(!empty($run['notifications'])){
    			foreach($run['notifications'] as $key=>$notification_item){

    				$component_name = $notification_item->component_name;
					if ( 'xprofile' == $notification_item->component_name ) {
						$component_name = 'profile';
					}
					$bp = buddypress();
					if ( isset( $bp->{$component_name}->notification_callback ) && is_callable( $bp->{$component_name}->notification_callback ) ) {
						// Retrieve the content of the notification using the callback.
						$content = call_user_func( $bp->{$component_name}->notification_callback, $notification_item->component_action, $notification_item->item_id, $notification_item->secondary_item_id, 0, 'array', $notification_item->id );
    				
						$run['notifications'][$key]->content = $content;
					}

    			}
    		}
    		
    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('notifications Found','notifications Found','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('notifications Not Found','notifications Not Found','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_get_notifications', $data , $request ,$args);

    		return new WP_REST_Response( $data, 200 );  
	    }

	   	function vibe_bp_api_get_notification_by_id($request){
	   		$notification_id = $request->get_param('notification_id');

	   		$run = bp_notifications_get_notification((int)$notification_id);
    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('notification Found','notification Found','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('notification Not Found','notificationsNot Found','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_get_notification_by_id', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 );  
	    }


	    function vibe_bp_api_markall_read_unread($request){
	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);
	    	$user_id = $this->user->id;

	    	$is_new = $args['is_new'];
	    	if($is_new){
	    		//unread
	    		$message='unread';

	    	}else{
	    		//read
	    		$message='read';
	    		
	    	}
	    	// return $args;
			// $is_new            Mark as read (0) or unread (1) buddypress error on comment
	    	$run = BP_Notifications_Notification::mark_all_for_user($user_id, $is_new);
    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Mark as all '.$message,'Mark as all '.$message,'vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Mark as all not '.$message,'Mark as all not '.$message,'vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_markall_read_unread', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 );  
	    }

	    function vibe_bp_api_delete_notification($request){

	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);
	    	$id = $args['id'];  // notification id

	    	if(is_numeric($id)){
	    		$run = BP_Notifications_Notification::delete( array( 'id' => $id ) ) ;
	    	}

	    	if(is_Array($id)){
	    		foreach($id as $i){
	    			$run = BP_Notifications_Notification::delete( array( 'id' => $i ) ) ;
	    		}
	    	}
	    	
    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Notification deleted','Notification deleted','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Notification Not deleted','Notification Not deleted','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_delete_notification', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 );  
	    }

	    function vibe_bp_api_deleteall($request){

	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);
	    	$ids = $args['ids'];  // notification id

	    	if(is_Array($ids)){
	    		foreach($ids as $i){
	    			$run = BP_Notifications_Notification::delete( array( 'id' => $i ) ) ;
	    		}
	    	}
	    	
    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Notifications deleted','Notification deleted','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Notification Not deleted','Notification Not deleted','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_deleteall_notification', $data , $request ,$args);

    		return new WP_REST_Response( $data, 200 );  
	    }

	    function vibe_bp_api_mark_read_unread($request){

	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);
	    	$id = $args['id'];
	    	$is_new = $args['is_new'];
	    	if($is_new){
	    		$message='unread';
	    	}else{
	    		$message='read';
	    	}

	    	if(is_numeric($id)){
	    		$run = BP_Notifications_Notification::update(
					array( 'is_new' => $is_new ),
					array( 'id'     => $id     )
				);
	    	}

	    	if(is_Array($id)){
	    		foreach($id as $i){
	    			$run = BP_Notifications_Notification::update(
						array( 'is_new' => $is_new ),
						array( 'id'     => $i     )
					);
	    		}
	    	}

	    	
    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' =>_x('Marked !','Mark as '.$message,'vibebp'),
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Unable to mark','','vibebp'),
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_mark_read_unread', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 );  
	    }



	}
}

VIBE_BP_API_Rest_Notifications_Controller::init();