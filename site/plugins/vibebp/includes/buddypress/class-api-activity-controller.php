<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'VIBE_BP_API_Rest_Activity_Controller' ) ) {
	
	class VIBE_BP_API_Rest_Activity_Controller extends WP_REST_Controller{
		
		public static $instance;
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new VIBE_BP_API_Rest_Activity_Controller();
	        return self::$instance;
	    }
	    public function __construct( ) {
			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->type= Vibe_BP_API_ACTIVITY_TYPE;
			$this->register_routes();
		}

		public function register_routes() {
			register_rest_route( $this->namespace, '/' .$this->type, array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_activity' ),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/add', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'post_activity' ),
					'permission_callback' => array( $this, 'get_activity_post_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/remove', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'remove_activity' ),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/add-favorite', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_add_favorite'),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/remove-favorite', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_remove_favorite'),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/add-like', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_add_like'),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/remove-like', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_remove_like'),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/get-favorite', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_get_favorite'),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			

		}


		/*
	    PERMISSIONS
	     */
	    function get_activity_permissions($request){


	    	$body = json_decode($request->get_body(),true);
	       	
	        if (empty($body['token'])){
	           	$client_id = $request->get_param('client_id');
	           	if($client_d == vibebp_get_setting('client_id')){
	           		return true;
	           	}
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

	    function get_activity_post_permissions($request){
	    	$body = json_decode(stripslashes($_POST['body']),true);
	    	if (empty($body['token'])){
	           	$client_id = $request->get_param('client_id');
	           	if($client_d == vibebp_get_setting('client_id')){
	           		return true;
	           	}
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

	    function get_activity($request){

	    	if(empty($this->user->id)){
	    		return new WP_REST_Response( array('status'=>0,'message'=>'Security error'), 200 );
	    	}

	    	$args = json_decode($request->get_body(),true);

	    	$activity_args = array();
	    	$activity_args['search_terms'] = $args['search'];

			if($args['filter'] === 'just-me'){
				$activity_args['scope'] = 'just-me'; 
				$activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
			}

			if($args['filter'] === 'activity-mentions'){
				$activity_args['search_terms'] = '@' . bp_activity_get_user_mentionname( $this->user->id );
			}

			if($args['filter'] === 'activity-favs'){
				$activity_args['scope'] = 'favorites'; 
				$activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
			}

			if($args['filter'] === 'activity-likes'){
				$activity_args['scope'] = 'likes'; 
				$activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
			}

			if($args['filter'] === 'activity-following'){
				$activity_args['scope'] = 'following'; 
				$activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
			}

			if($args['filter'] === 'activity-friends'){
				$activity_args['scope'] = 'friends'; 
				$activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
			}

			if($args['filter'] === 'activity-groups'){
				$activity_args['scope'] = 'groups'; 
				$activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
			}

			if($args['filter'] === 'groups'){
				$activity_args['filter'] = array('primary_id'=>$args['item_id'],'object'=>'groups');
			}

			if(!empty($args['sorter']) && $args['sorter'] > -1){
				$activity_args['filter']['action'] = $args['sorter'];
			}

			
			if(!empty($args['page'])){
				$activity_args['page'] = $args['page'];
			}
			$activity_args['per_page'] = 20;

			$activity_args['display_comments'] = 'stream';

			$activity_args = apply_filters('vibebp_api_get_activity',$activity_args,$args,$this->user->id);

			
	    	$run = bp_activity_get($activity_args);
	    	$activity_ids = wp_list_pluck( $run['activities'], 'id');
	    	
	    	if(!empty($run['activities'])){
	    		foreach($run['activities'] as $key=>$activity){
	    			$all_meta = bp_activity_get_meta( $activity->id,'',false );
	    			$run['activities'][$key]->meta=$all_meta;
	    		}
	    	}
    		if( $run){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Activities Found','Activities Found','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Activities not Found','Activities not Found','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_get_activity', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 ); 

	    }

	    function post_activity($request){


	    	$body = json_decode(stripslashes($_POST['body']),true);
	    	
	    	if(empty($this->user->id) || empty($body['args'])){
	    		return new WP_REST_Response( array('status'=>0,'message'=>'Security error'), 200 );
	    	}

	    	
	    	$args = $body['args'];
	    	//{content:'',component:'',component_id:'',meta:[]}
	    	if($args['component'] == 'activity_comment'){
	    		$activity_args = array(
	    			'content'			=>$args['content'],
	    			'component'         => $args['component'],
	    			'user_id'			=>$this->user->id,
	    			'activity_id'		=>$args['parent_id'],
	    			'parent_id'			=>$args['component_id'],
	    		);

	    		$activity_id = bp_activity_new_comment($activity_args); 

	    	}else{
	    		if($args['component_id'] == 'group'){
	    			$args['component_id']='groups';
	    		}
	    		$activity_args = array(
		    		'content'			=>$args['content'],
	    			'user_id'			=>$this->user->id,
	    			'item_id'			=>$args['component_id'],
					'component'         =>$args['component'],
					'type'              => 'activity_update',
					'parent_id'			=>$args['parent_id']
		    	);

		    	$activity_id = bp_activity_add($activity_args);
	    	}

	    	if(is_numeric($activity_id)){
    			
	    		
    			if(!empty($_FILES) && !empty($args['meta'])){
    				if ( ! function_exists( 'wp_handle_upload' ) ) {
					    require_once( ABSPATH . 'wp-admin/includes/file.php' );
					}
					 
					$uploadedfiles = $_FILES['files'];
					
					$upload_overrides = array(
					    'test_form' => false
					);
					foreach($args['meta'] as $key=>$meta){
						if($key == 'image'){

						}
						$uploadedfiles = $_FILES['files_'.$key];
						
						$movefile = wp_handle_upload( $uploadedfiles, $upload_overrides );
						
						if ( $movefile && ! isset( $movefile['error'] ) ) {
							$args['meta'][$key]['value'] = $movefile['url'];
							do_action('vibebp_upload_attachment',$movefile['url'],$this->user->id);
						}
					}
    			}

    			if(!empty($args['meta'])){
    				foreach($args['meta'] as $meta){
    					//process upload and get a meta value
    					bp_activity_add_meta( $activity_id, $meta['key'], $meta['value'], false );
    				}
    			}
    		}else{
    			return new WP_REST_Response( array('status'=>0,'message'=>__('Activity not saved !','vibebp')), 200 );
    		}

    		if($args['component'] == 'activity_comment'){
    			$activity = array(
    				'id'=>$activity_id,
					'avatar'=> $this->user->avatar,
					'component'=>'activity',
					'content'=> $args['content'],
					'item_id'=> $args['parent_id'],
					'secondary_item_id'=> $args['component_id'],
					'type'=> 'activity_comment',
					'display_name'=>  $this->user->displayname,
					'user_id'=>$this->user->id
    			);
    			$all_meta = bp_activity_get_meta( $activity_id,'',false );
    			$activity['meta']=$all_meta;
    			$activity['action'] = bp_activity_generate_action_string($activity);
    			if(!$activity['action']){
    				$activity['action'] = sprintf(__('%s posted new comment','vibebp'),$this->user->displayname);
    			}
    			 
    		}else{
    			$act_obj = bp_activity_get(array('in'=>array($activity_id)));
				$activity = (Array)$act_obj['activities'][0];
				$all_meta = bp_activity_get_meta( $activity_id,'',false );
    			$activity['meta']=$all_meta;
    		}
    		
    		
    		$activity['action']=strip_tags($activity['action']);
    		return new WP_REST_Response( array('status'=>1,'activity'=>$activity), 200 );
			
	    }

	    function remove_activity($request){

	    	if(empty($this->user->id)){
	    		return new WP_REST_Response( array('status'=>0,'message'=>'Security error'), 200 );
	    	}

	    	$args = json_decode($request->get_body(),true);
	    	if(!empty($args['parent_id'])){
	    		bp_activity_delete_comment( $args['parent_id'], $args['activity_id'] );
	    	}else{
	    		bp_activity_delete(array('id'=>$args['activity_id']) );
	    	}
			return new WP_REST_Response( array('status'=>1,'message'=>__('Activity removed','vibebp')), 200 );
	    }

	    function vibe_bp_api_add_favorite($request){

	    	if(empty($this->user->id)){
	    		return new WP_REST_Response( array('status'=>0,'message'=>'Security error'), 200 );
	    	}

	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);
	    	$activity_id = $args['activity_id'];
	    	$user_id  = $args['user_id'];

	    	$run = bp_activity_add_user_favorite( $activity_id, $user_id  );
    		if( $run){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Activity added as Favorite','Activity added as Favorite','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Activity not added as Favorite','Activity not added as Favorite','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_add_favorite', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 ); 
	    }

	    function vibe_bp_api_remove_favorite($request){

	    	if(empty($this->user->id)){
	    		return new WP_REST_Response( array('status'=>0,'message'=>'Security error'), 200 );
	    	}

	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);
	    	$activity_id = $args['activity_id'];
	    	$user_id  = $args['user_id'];

	    	$run = bp_activity_remove_user_favorite( $activity_id, $user_id  );
    		if( $run){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Activity removed from favorite','Activity removed as Favorite','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Activity not removed as Favorite','Activity not removed as Favorite','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_remove_favorite', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 ); 
	    }

	    function vibe_bp_api_add_like($request){


	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);
	    	$activity_id = $args['activity_id'];
	    	$user_id  = $this->user->id;

	    	$run = vibebp_activity_add_user_like( $activity_id, $user_id  );
    		if( $run){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Activity liked','Activity like','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Activity unliked','Activity unliked','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_add_favorite', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 ); 
	    }

		function vibe_bp_api_remove_like($request){


	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);
	    	$activity_id = $args['activity_id'];
	    	$user_id  = $this->user->id;

	    	$run = vibebp_activity_remove_user_like( $activity_id, $user_id  );
    		if( $run){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Activity like removed','activity like removed','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Activity like removed','activity like removed','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_remove_favorite', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 ); 
	    }


	}
}

VIBE_BP_API_Rest_Activity_Controller::init();