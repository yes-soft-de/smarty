<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'VIBE_BP_API_Rest_Members_Controller' ) ) {
	
	class Vibe_BP_API_Rest_Members_Controller extends WP_REST_Controller{
		
		public static $instance;
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new Vibe_BP_API_Rest_Members_Controller();
	        return self::$instance;
	    }
	    public function __construct( ) {
			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->register_routes();
		}

		public function register_routes() {
			register_rest_route( $this->namespace, '/members', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'get_members' ),
				),
			));
			register_rest_route( $this->namespace, '/member/(?P<user_id>\d+)?', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_member' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'args'                     	=>  array(
						'user_id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/member_card/(?P<user_id>\d+)', array(
				array(
					'methods'             =>  'GET',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'get_member_card' ),
				),
			));
			register_rest_route( $this->namespace, '/member_values', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'get_member_card_values' ),
				),
			));
			
			register_rest_route( $this->namespace, '/member/avatars/', array(
				array(
					'methods'             => 'GET',
					'callback'            =>  array( $this, 'get_member_avatars' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/friends/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_get_friends' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/friends/addfriendship/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_friends_add_friend' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/friends/removefriendship/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_friends_remove_friend' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/friends/action/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_friends_action_friendship' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));



			register_rest_route( $this->namespace, '/check/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'checkfuction' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));
			register_rest_route( $this->namespace, '/friends/requests/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'vibe_friends_get_friendId_request_ids_for_user' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));


			register_rest_route( $this->namespace, '/followers', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'get_followers' ),
				),
			));
			register_rest_route( $this->namespace, '/follower_ids', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'get_follower_ids' ),
				),
			));
			register_rest_route( $this->namespace, '/following', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'get_following' ),
				),
			));
			register_rest_route( $this->namespace, '/followers/action', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'follower_action' ),
				),
			));

		}


		/*
	    PERMISSIONS
	     */
	    function get_members_permissions($request){
	    	
	    	$body = json_decode($request->get_body(),true);
	       	
	        if (empty($body) || empty($body['token'])){
	           	$client_id = $request->get_param('client_id');

	           	if($client_id == vibebp_get_setting('client_id')){
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

    	function get_members($request){
    		$args = json_decode($request->get_body(),true);	
    		$args=apply_filters( 'vibe_bp_api_members_get_members_args', $args, $request);

    		/*'type'                => 'active',     // Active, newest, alphabetical, random or popular.
			'user_id'             => false,        // Pass a user_id to limit to only friend connections for this user.
			'exclude'             => false,        // Users to exclude from results.
			'search_terms'        => false,        // Limit to users that match these search terms.
			'meta_key'            => false,        // Limit to users who have this piece of usermeta.
			'meta_value'          => false,        // With meta_key, limit to users where usermeta matches this value.
			'member_type'         => '',
			'member_type__in'     => '',
			'member_type__not_in' => '',
			'include'             => false,        // Pass comma separated list of user_ids to limit to only these users.
			'per_page'            => 20,           // The number of results to return per page.
			'page'                => 1,            // The page to return if limiting per page.
			'populate_xtras'     => true,         // Fetch the last active, where the user is a friend, total friend count, latest update.
			'count_total'         => 'count_query' // What kind of total user count to do, if any. 'count_query', 'sql_calc_found_rows', or false.
			*/
			if(!empty($args['filters'])){
				$xprofile_query = array(
					'relation'=>'AND'
				);
				foreach($args['filters'] as $filter){
					switch($filter['type']){
						
						default:
							$xprofile_query[] = array(
								'field'   => $filter['field_id'],
								'value'   => $filter['values'],
								'compare' => 'IN',
							);
						break;
					}
				}
				
				$args['xprofile_query'] = $xprofile_query;
				unset($args['filters']);
			}
			

			$run =  bp_core_get_users($args);	

			if( count($run['users']) ){
				foreach($run['users'] as $key => $user){
					$run['users'][$key]->avatar = bp_core_fetch_avatar(array(
                            'item_id' 	=> $user->ID,
                            'object'  	=> 'user',
                            'type'		=>'thumb',
                            'html'    	=> false
                        ));
					$run['users'][$key]->url = bp_core_get_user_domain($run['users'][$key]->id);
					if(isset($user->last_update)){
						$run['users'][$key]->last_update = maybe_unserialize($user->last_update);	
					}
					
					if(!empty($args['show_map'])){
						$run['users'][$key]->location = array('lat'=>get_user_meta($user->ID,'lat',true),'lng'=>get_user_meta($user->ID,'lng',true));
					}
				}
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Members Exist','Members Exist','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('No members found !','Members Not Exist','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_members_get_members', $data, $request ,$args );
			return new WP_REST_Response( $data, 200 ); 
    	}


    	function get_member($request){
    		$user_id = (int)$request->get_param('user_id');	 // get param data 'id'
    		$run =  bp_core_get_core_userdata($user_id);	
			if( $run  ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Member Exist','Member Exist','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Member Not Exist','Member Not Exist','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_members_get_member', $data, $request ,$args );
			return new WP_REST_Response( $data, 200 ); 		
    	}

    	function vibe_bp_api_get_member_by_id($id){
    		$data = get_userdata($id);
    		$data=apply_filters( 'vibe_bp_api_get_member_by_id', $data);
    		return 	$data;
    	}


    	function vibe_bp_api_get_friends($request){

    		
    		$args = json_decode($request->get_body(),true);

    		$run = bp_core_get_users( array(
				'type'         => $args['sort'],
				'per_page'     => 15,
				'page'         => $args['page'],
				'user_id'      => $this->user->id,
				'search_terms' => $args['search'],
			) );

    		if( $run['total'] ){

    			foreach($run['users'] as $key=>$user){
    				$run['users'][$key]->latest_update = maybe_unserialize($user->latest_update);
    				$run['users'][$key]->avatar = bp_core_fetch_avatar(array(
                        'item_id' 	=> $user->ID,
                        'object'  	=> 'user',
                        'type'		=>'thumb',
                        'html'    	=> false
                    ));
    			}

    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('User has Friends','User has Friends','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('User has no Friends','User has no Friends','vibebp')
	    		);
    	    }

    		$data=apply_filters( 'vibe_bp_api_get_friends', $data ,$request);

			return new WP_REST_Response( $data, 200 );  
    	}

    	// for sending frienship request get true if send else false
    	function vibe_bp_api_friends_add_friend($request){
 
    		$args = json_decode($request->get_body(),true);
    		$friends= $args['friends'];	 /* get param data 'friend_userid' */
    		$run = true;
    		if(!empty($friends)){

    			foreach($friends as $friend){
    				if($run){
    					$run=friends_add_friend($this->user->id,$friend,false);    /* return bool true|false */
    				}
    				
    			}
    		}
    		
    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Request Send','Request Send','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Request Not Send','Request Not Send','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_friends_add_friend', $data ,$request);
			return new WP_REST_Response( $data, 200 );  	

    	}

    	function vibe_bp_api_friends_remove_friend($request){

    		$args = json_decode($request->get_body(),true);
    		$initiator_userid = $this->user->id;	 /* get param data 'initiator_userid' */
    		$friend_userid= (int)$args['friend_userid'];	 /* get param data 'friend_userid' */

    		$run=friends_remove_friend($initiator_userid,$friend_userid);  /* return bool 



    		true|false */
    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Friend  removed','Friend  removed','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Friend not removed','Friend not removed','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_friends_add_friend', $data ,$request);
			return new WP_REST_Response( $data, 200 );  	
    	}

    	function vibe_get_friendship_ids_for_user($id){   		
    		return BP_Friends_Friendship::get_friendship_ids_for_user($id);
    	}



/*
*	This is used to Accept a Friendship ID
*/
    	function vibe_bp_api_friends_action_friendship($request){

    		$args = json_decode($request->get_body(),true);

    		$bp = buddypress();
    		global $wpdb;

    		$friendship_id = (int)$args['friendship_id'];
    		$action = $args['action'];
    		if($action == 'accept'){

    			
				$run = $wpdb->query( $wpdb->prepare( "UPDATE {$bp->friends->table_name} SET is_confirmed = 1, date_created = %s WHERE id = %d AND friend_user_id = %d", bp_core_current_time(), $friendship_id, $this->user->id ) );
				if($run){
					$friendship = new BP_Friends_Friendship( $friendship_id, true, false );
					friends_update_friend_totals( $friendship->initiator_user_id, $friendship->friend_user_id );
					do_action( 'friends_friendship_accepted', $friendship->id, $friendship->initiator_user_id, $friendship->friend_user_id, $friendship );
				}
    			
    		}else if($action == 'reject'){

				$run =  $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->friends->table_name} WHERE id = %d AND friend_user_id = %d", $friendship_id, $this->user->id ) );

				if($run){
					$friendship = new BP_Friends_Friendship( $friendship_id, true, false );
					do_action_ref_array( 'friends_friendship_rejected', array( $friendship_id, &$friendship ) );
				}
    		}else if($action == 'cancel'){
    			$run = $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->friends->table_name} WHERE id = %d AND initiator_user_id = %d", $friendship_id, $this->user->id ) );
    			if($run){
    				$friendship = new BP_Friends_Friendship( $friendship_id, true, false );
    				do_action_ref_array( 'friends_friendship_whithdrawn', array( $friendship_id, &$friendship ) );
					do_action_ref_array( 'friends_friendship_withdrawn',  array( $friendship_id, &$friendship ) );
    			}
    		}
			
    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Friend Request action complete','Friend Request action','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Friend Request action can not be completed','Friend Request Not Accepted','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_friends_accept_friendship', $data ,$request);
			return new WP_REST_Response( $data, 200 );  
    	}


    	// fetch friendship id for user
    	function vibe_friends_get_friendship_ids_for_user($user_id,$page=1,$requested=1,$sort=DESC,$is_confirmed=0){
	    	global $wpdb;
			$bp = buddypress();
			if($requested){
				$friendship_ids = $wpdb->get_results( $wpdb->prepare( "SELECT id, initiator_user_id,friend_user_id  FROM {$bp->friends->table_name} WHERE initiator_user_id = %d AND (is_confirmed=%d)  ORDER BY date_created $sort LIMIT %d,20",  $user_id ,$is_confirmed,($page-1)*20 ) );
			}else{
				$friendship_ids = $wpdb->get_results( $wpdb->prepare( "SELECT id, initiator_user_id,friend_user_id  FROM {$bp->friends->table_name} WHERE friend_user_id = %d AND (is_confirmed=%d)  ORDER BY date_created $sort LIMIT %d,20",  $user_id ,$is_confirmed,($page-1)*20 ) );
			}
			
			return $friendship_ids;
    	
    	}


		// friend id and friendship id who  is request to this user;
    	function vibe_friends_get_friendId_request_ids_for_user($request){

    		$args = json_decode($request->get_body(),true);

    	    $initiator_friendship_ids=$this->vibe_friends_get_friendship_ids_for_user($this->user->id,$args['page'],$args['requester'],$args['sort']);
    	    $user_details = array();
    	    if(!empty($initiator_friendship_ids)){
	    		foreach ($initiator_friendship_ids as $initiator_friendship_id) {

	    			$uid = (int)$initiator_friendship_id->initiator_user_id;
	    			if(!empty($args['requester'])){
	    				$uid = (int)$initiator_friendship_id->friend_user_id;
	    			}
	    			$user = bp_core_get_core_userdata($uid);
	    			$user->avatar = bp_core_fetch_avatar(array(
	                            'item_id' 	=> $user->ID,
	                            'object'  	=> 'user',
	                            'type'		=>'thumb',
	                            'html'    	=> false
	                        ));
	    			 $user_details[]=array(
	    			 	'friendship_id'=>(int)$initiator_friendship_id->id,
	    			 	'user'=>$user
	    			 );
	    		}
	    	}
    		$data=apply_filters( 'vibe_friends_get_friendId_request_ids_for_user', array('status'=>1,'data'=>$user_details),$request );
			return new WP_REST_Response( $data, 200 );   

    	}
    			

    	function get_followers($request){

    		$body = json_decode($request->get_body(),true);
    		$data = array(
    			'status'=>1,
    			'followers'=>array()
    		);

    		global $wpdb;
    		$results = $wpdb->get_results($wpdb->prepare("
    			SELECT user_id 
    			FROM {$wpdb->usermeta}
    			WHERE meta_key ='vibebp_follow' 
    			AND meta_value = %d",
    			$this->user->id));

    		if(!empty($results)){
    			foreach($results as $result){
    				$user=bp_core_get_core_userdata($result->user_id);
    				$user->avatar = bp_core_fetch_avatar(array(
                        'item_id' 	=> $result->user_id,
                        'object'  	=> 'user',
                        'type'		=>'thumb',
                        'html'    	=> false
                    ));
                    
                    $followers = get_user_meta($this->user->id,'vibebp_follow',false);
                    if(in_array($result->user_id,$followers)){
                    	$user->is_following = true;
                    }

                    $data['followers'][]=$user;
    			}
    		}
    		return new WP_REST_Response( $data, 200 );   
    	}

    	function get_follower_ids($request){
    		global $wpdb;
    		$results = $wpdb->get_results($wpdb->prepare("
    			SELECT user_id 
    			FROM {$wpdb->usermeta}
    			WHERE meta_key ='vibebp_follow' 
    			AND meta_value = %d",
    			$this->user->id),ARRAY_A);
    		$users=array();
    		if(!empty($results)){
    			foreach($results as $result){
    				$users[]=$result['user_id'];
    			}
    		}
    		return new WP_REST_Response( array('status'=>1,'followers'=>$users), 200 ); 
    	}

    	function get_following($request){

    		$body = json_decode($request->get_body(),true);
    		$data = array(
    			'status'=>1,
    			'following'=>array()
    		);

    		$results = get_user_meta($this->user->id,'vibebp_follow',false);
    		if(!empty($results)){
    			foreach($results as $result){
    				$user=bp_core_get_core_userdata($result);
    				$user->avatar = bp_core_fetch_avatar(array(
                        'item_id' 	=> $result,
                        'object'  	=> 'user',
                        'type'		=>'thumb',
                        'html'    	=> false
                    ));
                    $user->is_following = true;
                    $data['following'][]=$user;
    			}
    		}
    		return new WP_REST_Response( $data, 200 );   
    	}


    	function follower_action($request){
    		$body = json_decode($request->get_body());
    		
    		if($body->action === 'follow'){
    			$followers = get_user_meta($this->user->id,'vibebp_follow',false);
    			if(!empty($body->followers)){

    				$messages = $rtm = array();
    				foreach($body->followers as $user){
    					if(empty($followers) || !in_array($user->id,$followers)){
							add_user_meta($this->user->id,'vibebp_follow',$user->id);	
							$messages[] = sprintf(__('Now following %s','wplms'),$user->name);
							$rtm[]=array('user_id'=>$user->id,'message'=>sprintf(__('%s is now following you','wplms'),$this->user->name));   
		    			}
    				}
    				
    				return new WP_REST_Response(array('status'=>1,'message'=>$messages,'rtm'=>$rtm),200);

    			}else if(empty($followers) || !in_array($body->user->ID,$followers)){
					add_user_meta($this->user->id,'vibebp_follow',$body->user->ID);	
					return new WP_REST_Response( array('status'=>1,'message'=>sprintf(__('Now following %s','wplms'),$body->user->displayname),'rtm'=>array('user_id'=>$body->user->ID,'message'=>sprintf(__('%s is now following you','wplms'),$this->user->displayname))), 200 );   
    			}
    		}
    		if($body->action === 'unfollow'){
    			
    			$followers = get_user_meta($this->user->id,'vibebp_follow',false);
    			if(!empty($followers) && in_array($body->user->ID,$followers)){
    				delete_user_meta($this->user->id,'vibebp_follow',$body->user->ID);
    				return new WP_REST_Response( array('status'=>1,'message'=>sprintf(__('Unfollowed %s','wplms'),$body->user->displayname),'rtm'=>array('user_id'=>$body->user->ID,'message'=>sprintf(__('%s is unfollowed you','wplms'),$this->user->displayname))), 200 );   	
    			}
    		}
    		return new WP_REST_Response( array('status'=>0,'message'=>__('Unable to perform task','wplms')),200);
    	}

    	function get_member_card($request){

    		$user_id = $request->get_param('user_id');

    		$layouts = new WP_Query(apply_filters('wplms_member_card',array(
				'post_type'=>'member-card',
				'posts_per_page'=>1
			)));
			$init = VibeBP_Init::init();
			$init->user_id = $user_id;
    		ob_start();
			if($layouts->have_posts()){
				while($layouts->have_posts()){
					$layouts->the_post();
					the_content();
				}
			}
			return ob_get_clean();
    	}

    	function get_member_card_values($request){
    		$body = json_decode($request->get_body(),true);
    		$data=[];
    		if(is_array($body['fields'])){
    			foreach($body['fields'] as $field){
    				if(is_numeric($field['id'])){
    					$d = xprofile_get_field_data( $field['id'], $body['user_id']);
    					if(is_array($d)){
    						$data[$field['id']] = $d;	
    					}else{
    						$json = json_decode($d);
	    					if(json_last_error() === 0){
	    						$data[$field['id']] = $json;	
	    					}else{
	    						$data[$field['id']] = $d;	
	    					}	
    					}
    					
    					
    				}else{
    					if($field['id'] === 'profile_pic'){
    						$data[$field['id']] = bp_core_fetch_avatar(array(
			                        'item_id' 	=> $body['user_id'],
			                        'object'  	=> 'user',
			                        'type'		=>'full',
			                        'html'    	=> false
			                    ));
    					}
    					if($field['id'] === 'friend_count'){
    						$data[$field['id']] = friends_get_total_friend_count($body['user_id']);
    					}
    					if($field['id'] === 'group_count'){
    						$data[$field['id']] = bp_get_total_group_count_for_user($body['user_id']);
    					}
    					if($field['id'] === 'follower_count'){
    						global $wpdb;
    						$data[$field['id']] = $wpdb->get_var("SELECT count(user_id) FROM {$wpdb->usermeta} WHERE meta_key = 'vibebp_follow' AND meta_value = ".$body['user_id']);
    					}
    					if($field['id'] === 'following_count'){
    						$count = get_user_meta($body['user_id'],'vibebp_follow',false);
    						$data[$field['id']] = count($count);
    					}
    					

    				}
    			}
    		}
    		if(empty($data)){
    			return new WP_REST_Response( array('status'=>0,'message'=>__('No card data','vibebp')), 200 );   
    		}else{
    			return new WP_REST_Response( array('status'=>1,'data'=>$data), 200 );   
    		}
    	}
	}
}


Vibe_BP_API_Rest_Members_Controller::init();