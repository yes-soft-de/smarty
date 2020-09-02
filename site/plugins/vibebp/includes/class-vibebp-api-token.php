<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'VibeBP_Token' ) ) {

	class VibeBP_Token extends WP_REST_Controller{
		/**
	     * The namespace to add to the api calls.
	     *
	     * @var string The namespace to add to the api call
	     */

		var $settings;
		var $temp;

		public static $instance;
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new VibeBP_Token();
	        return self::$instance;
	    }

		public function __construct(){
			$this->namespace = VIBEBP_NAMESPACE;
			$this->type = VIBEBP_TOKEN;
			$this->register_routes(); 	// Register Routes
		}	

		

		public function register_routes(){
			register_rest_route( $this->namespace, '/'. $this->type .'/generate-token/', array(
				'methods'                   =>   'POST',
				'callback'                  =>  array( $this, 'generate_token' ),
				'permission_callback' => array( $this, 'get_user_permissions_check' ),
			) );

			register_rest_route( $this->namespace, '/'. $this->type .'/validate-token/', array(
				'methods'                   =>   'POST',
				'callback'                  =>  array( $this, 'validate_token' ),
				'permission_callback' => array( $this, 'get_user_permissions_check' ),
			) );

		}

		public function get_user_permissions_check($request){
			
			$security = $request->get_param('security');
			
			if($security == vibebp_get_api_security()){
				return true;	
			}
			$security = $request->get_param('client_id');
			if($security == vibebp_get_setting('client_id')){
				return true;	
			}
			return false;
		}

		function generate_token($request){

			$secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false; 
			//Tougher Security
			$secret_key = apply_filters('vibebp_tougher_security',$secret_key);

			$post = json_decode($request->get_body(),true);

	        $username = $post['email'];
	        $password = $post['password'];
	        /** First thing, check the secret key if not exist return a error*/
	        if (!$secret_key) {
	          	return new WP_REST_Response(array(
	          		'status'=>0,
                	'code'=>'vibebp_jwt_security_missing',
	              	'message'=>_x('Secret key missing','JWT authentication error','vibebp'),
	              )
            	);
	        }
	        
        	/** Try to authenticate the user with the passed credentials*/
        	$user = wp_authenticate($username, $password);	

	        /** If the authentication fails return a error*/
	        if (is_wp_error($user) || !$user) {
	          	return new WP_REST_Response(array(
	          		'status'=>0,
	          		'code'=>'vibebp_jwt_invalid_cred',
	          		'message'=>_x('Email or Password not valid','WP authentication error','vibebp'),
	          		)
            	);
	        }
	        /** Valid credentials, the user exists create the according Token */
	        $issuedAt = time();
	        $notBefore = apply_filters( VIBEBP.'_token_expire_not_before', $issuedAt, $issuedAt);

	        $duration = vibebp_get_setting('token_duration');
	        if(empty($duration)){
	        	$duration = DAY_IN_SECONDS * 7;
	        }
	        $expire = apply_filters( VIBEBP.'_token_expire', $issuedAt  + $duration, $issuedAt);


	        $token = array(
	            'iss' => get_bloginfo('url'),
	            'iat' => $issuedAt,
	            'nbf' => $notBefore,
	            'exp' => $expire,
	            'data' => apply_filters('vibebp_jwt_token_data',array(
	                'user' => array(
	                    'id' => $user->data->ID,
	                    'username'=>$user->data->user_login,
	                    'slug'=>$user->data->user_nicename,
	                    'email'=>$user->data->user_email,
	                    'avatar'=> (function_exists('bp_core_fetch_avatar')?bp_core_fetch_avatar(array(
                                        'item_id' => $user->data->ID,
                                        'object'  => 'user',
                                        'type'=>'full',
                                        'html'    => false
                                    )):get_avatar($user->data->user_email,240)),
	                    'displayname'=>$user->data->display_name,
	                    'roles'=> $user->roles,
	                    'caps'=> $user->allcaps,
	                    'profile_link'=>vibebp_get_profile_link($user->data->user_nicename)
		                ),
		            )
	            ),
	        );
	        bp_update_user_last_activity($user->data->ID);
	        
	        /** Let the user modify the token data before the sign. */
	        $token = JWT::encode(apply_filters(VIBEBP.'jwt_auth_token_before_sign', $token, $user), $secret_key);
	        /** The token is signed, now create the object with no sensible user data to the client*/
	        $data = array(
	        	'status' => 1,
	            'token' => $token,
	            'message'=>_x('Token generated','Token generated','vibebp')
	        );
	        /** Let the user modify the data before send it back */
	        return new WP_REST_Response(apply_filters(VIBEBP.'jwt_auth_token_before_dispatch', $data, $user));
		}

		function validate_token($request){
			/*
	         * Looking for the HTTP_AUTHORIZATION header, if not present just
	         * return the user.
	         */
	        $token = $request->get_body();
	       
	        if (!$token) {
	        	$data = array(
		        	'status' => 0,
		            'data' => 'vibebp_jwt_auth_token_missing',
		            'message'=>_x('Authorization token missing','Authorization Token Missing','vibebp')
		        );
		        return $data;
	        }
	        /** Get the Secret Key */
	        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
	        //Tougher Security
			$secret_key = apply_filters('vibebp_tougher_security',$secret_key);
	        if (!$secret_key) {
	            $data = array(
		        	'status' => 0,
		            'data' => 'vibebp_jwt_secret_key_missing',
		            'message'=>_x('Secret key missing','Secret key missing','vibebp')
		        );
		        return $data;
	        }
	        /** Try to decode the token */ /** Else return exception*/
	        try {
	            $expanded_token = JWT::decode($token, $secret_key, array('HS256'));
	            $expanded_token = apply_filters('vibebp_validate_token',$expanded_token,$token);
	            if($expanded_token){
		            $data = array(
			        	'status' => 1, 
			            'data' => $expanded_token,
			            'message'=>_x('Valid Token','Valid Token','vibebp')
			        );
			        return apply_filters(VIBEBP.'jwt_auth_token_validate_before_dispatch', $data);
		        }else{
		        	$data = array(
		        	'status' => 0,
		            'data' => 'jwt_auth_invalid_token',
			        );
			        return $data;
		        }
		        

	        }catch (Exception $e) {
	            $data = array(
		        	'status' => 0,
		            'data' => 'jwt_auth_invalid_token',
		            'message'=>$e->getMessage()
		        );
		        return $data;
	        }
	        
		}
		
	}

}



VibeBP_Token::init();


