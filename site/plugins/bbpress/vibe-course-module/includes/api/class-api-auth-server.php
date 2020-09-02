<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// Include this library


class WPLMS_oAuth_Server extends WP_REST_Controller{

	var $settings;
	var $temp;

	public static $instance;
	
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_oAuth_Server();
        return self::$instance;
    }

	public function __construct(){
			
		
		//
		//add_action('init', array($this,'add_endpoints'));
    	
		
		add_action('init',array($this,'authentication_endpoints'));
		add_filter( 'request', array($this,'filter_request' ));

		add_action('wplms_before_auth',function(){
			$this->namespace = 'wplms/v1';
			$this->endpoints = apply_filters( "wplms_auth_endpoints", array(
				'me' => array(
					'func' => array($this,'fetch_me'), 
					'public' => false 
				),
				'mycourses' => array(
					'func' => array($this,'mycourses'), 
					'public' => false 
				),
				'destroy' => array( 
					'func' => array($this,'destroy'), 
					'public' => false 
				)
			));
		});
		
		add_action( 'password_reset',function($user){$this->reset_access_tokens($user->ID);});
		add_action( 'profile_update',function($user_id){$this->reset_access_tokens($user_id);});
		

		//add_filter( 'determine_current_user', array( $this, 'authenicate_bypass' ), 21 );
		//add_filter( 'template_include', array($this,'template_redirect_intercept'), 100 );

	}

	function authentication_endpoints(){
    	add_rewrite_endpoint('wplmsoauth/(?P<method>[authorize|token|user]+)', EP_PAGES);    
    }

    function filter_request( $vars ){
    	if(isset($vars)){
    		if (isset($vars['attachment']) && in_array($vars['attachment'],array('authorize','token'))){
				$this->load_auth($vars['attachment']);
				exit;
			}
    	}
    	return $vars;
	}


	public function authenicate_bypass( $user_id ) {
		if ( $user_id && $user_id > 0 ) {
			return (int) $user_id;
		}


		require_once( 'OAuth2/Autoloader.php' );
		OAuth2\Autoloader::register();
		$server  = new OAuth2\Server( new OAuth2\Storage\Wordpressdb() );
		$request = OAuth2\Request::createFromGlobals();
		if ( $server->verifyResourceRequest( $request ) ) {
			$token = $server->getAccessTokenData( $request );
			if ( isset( $token['user_id'] ) && $token['user_id'] > 0 ) {
				return (int) $token['user_id'];
			} elseif ( isset( $token['user_id'] ) && $token['user_id'] === 0 ) {

			}
		}
	}



	/**
	* [template_redirect_intercept description]
	*
	* @return [type] [description]
	*/
	function template_redirect_intercept( $template ) {
		global $wp_query;

		if ( $wp_query->get( 'wplmsoauth' ) || $wp_query->get( 'wplmswell-known' ) ) {
			$this->load_auth();
			exit;
		}
		return $template;
	}

	/*
	function authentication_endpoints(){
		
	    register_rest_route( $this->namespace, '/oauth/(?P<method>[authorize|token]+)', array(
			array(
				'methods'             =>  WP_REST_Server::READABLE,
				'callback'            =>  array( $this, 'load_auth' ),
				'args'                =>  array(
					'method' => array(
		                'validate_callback' => function($param, $request, $key) {
		                    return $param;
		                }
		            )	
				),
			),
		));
	}
	*/
	function load_auth($method){

		do_action( 'wplms_before_auth', $method);


		require_once( 'OAuth2/Autoloader.php' );
		OAuth2\Autoloader::register();

		$server  = new OAuth2\Server( new OAuth2\Storage\Wordpressdb() );
		$authrequest = OAuth2\Request::createFromGlobals();

		//print_r($authrequest);
		if ( $server->verifyResourceRequest( $authrequest ) ) {
			$token = $server->getAccessTokenData( $authrequest );
			print_R($token);
			if ( isset( $token['user_id'] ) && $token['user_id'] > 0 ) {
				return (int) $token['user_id'];
			} elseif ( isset( $token['user_id'] ) && $token['user_id'] === 0 ) {

			}
		}


		$storage    = new OAuth2\Storage\Wordpressdb();
		$config     = array(
			'use_crypto_tokens'                 => false,
			'store_encrypted_token_string'      => false,
			'use_openid_connect'                => false,
			'issuer'                            => site_url(), // Must be HTTPS
			'id_lifetime'                       => 3600,
			'access_lifetime'                   => 86400,
			'refresh_token_lifetime'            => 86400,
			'www_realm'                         => 'Service',
			'token_param_name'                  => 'access_token',
			'token_bearer_header_name'          => 'Bearer',
			'enforce_state'                     =>	true,
			'require_exact_redirect_uri'        => false,
			'allow_implicit'                    => false,
			'allow_credentials_in_request_body' => true, // Must be set to true for openID to work in most cases
			'allow_public_clients'              => false,
			'always_issue_new_refresh_token'    => false,
			'redirect_status_code'              => 302
		);

		$server = new OAuth2\Server( $storage, $config );
		
		/*
		|--------------------------------------------------------------------------
		| SUPPORTED GRANT TYPES
		|--------------------------------------------------------------------------
		|
		| Authorization Code will always be on. This may be a bug or a f@#$ up on
		| my end. None the less, these are controlled in the server settings page.
		|
		 */
		$support_grant_types = array();
		$server->addGrantType( new OAuth2\GrantType\AuthorizationCode( $storage ) );

		/*
		|--------------------------------------------------------------------------
		| DEFAULT SCOPES
		|--------------------------------------------------------------------------
		|
		| Supported scopes can be added to the plugin by modifying the wo_scopes. 
		| Until further notice, the default scope is 'basic'. Plans are in place to
		| allow this scope to be adjusted.
		|
		 */
		$default_scope = 'basic';

		$supported_scopes = apply_filters( 'wplms_auth_scopes', array(
			'openid',
			'profile',
			'email',
			'basic'
		) );

		$scope_util = new OAuth2\Scope( array(
			'default_scope'    => $default_scope,
			'supported_scopes' => $supported_scopes,
		) );

		$server->setScopeUtil( $scope_util );
/*
		|--------------------------------------------------------------------------
		| TOKEN CATCH
		|--------------------------------------------------------------------------
		|
		| The following code is ran when a request is made to the server using the
		| Authorization Code (implicit) Grant Type as well as request tokens
		|
		 */
		if ( $method == 'token' ) {
			$server->handleTokenRequest( OAuth2\Request::createFromGlobals() )->send();
			exit;
		}
		
		/*
		|--------------------------------------------------------------------------
		| AUTHORIZATION CODE CATCH
		|--------------------------------------------------------------------------
		|
		| The following code is ran when a request is made to the server using the
		| Authorization Code (not implicit) Grant Type.
		|
		| 1. Check if the user is logged in (redirect if not)
		| 2. Validate the request (client_id, redirect_uri)
		| 3. Create the authorization request using the authentication user's user_id
		|
		*/
		if ( $method == 'authorize' ) {
			
			$request  = OAuth2\Request::createFromGlobals();
			$response = new OAuth2\Response();
			if ( ! $server->validateAuthorizeRequest( $request, $response ) ) {
				$response->send();
				exit;
			}

			if ( ! is_user_logged_in() ) {
				wp_redirect( wp_login_url( $_SERVER['REQUEST_URI'] ) );
				exit;
			}

			

			$server->handleAuthorizeRequest( $request, $response, true, get_current_user_id() );
			
			$response->send();
			

			exit;
		}

		/*
		|--------------------------------------------------------------------------
		| RESOURCE SERVER METHODS
		|--------------------------------------------------------------------------
		|
		| Below this line is part of the developer API. Do not edit directly.
		| Refer to the developer documentation for extending the WordPress OAuth
		| Server plugin core functionality.
		|
		| @todo Document and tighten up error messages. All error messages will soon be
		| controlled through apply_filters so start planning for a filter error list to
		| allow for developers to customize error messages.
		|
		*/
		$ext_methods = $this->endpoints; 

		// Check to see if the method exists in the filter
		if ( is_array($ext_methods) && array_key_exists( $method, $ext_methods ) ) {

			// If the method is is set to public, lets just run the method without
			if ( isset( $ext_methods[ $method ]['public'] ) && $ext_methods[ $method ]['public'] ) {
				call_user_func_array( $ext_methods[ $method ]['func'], $_REQUEST );
				exit;
			}

			$response = new OAuth2\Response();
			if ( ! $server->verifyResourceRequest( OAuth2\Request::createFromGlobals() ) ) {
				$response->setError( 400, 'invalid_request', 'Missing or invalid parameter(s)' );
				$response->send();
				exit;
			}
			$token = $server->getAccessTokenData( OAuth2\Request::createFromGlobals() );
			if ( is_null( $token ) ) {
				$server->getResponse()->send();
				exit;
			}

			do_action( 'wplms_auth_endpoint_user_authenticated', array( $token ) );
			call_user_func_array( $ext_methods[ $method ]['func'], array( $token ) );

			exit;
		}

		/**
		 * Server error response. End of line
		 *
		 * @since 3.1.0
		 */
		$response = new OAuth2\Response();
		$response->setError( 400, 'invalid_request', 'Unknown request' );
		$response->send();

		
		exit;
	}


	function reset_access_tokens($user_id){
		//reset all authorization codes
		////reset all refresh tokens
		//reset all access tokens
	}

	function client_ip(){
		$ipaddress = '';
	    if (getenv('HTTP_CLIENT_IP'))
	        $ipaddress = getenv('HTTP_CLIENT_IP');
	    else if(getenv('HTTP_X_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	    else if(getenv('HTTP_X_FORWARDED'))
	        $ipaddress = getenv('HTTP_X_FORWARDED');
	    else if(getenv('HTTP_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_FORWARDED_FOR');
	    else if(getenv('HTTP_FORWARDED'))
	        $ipaddress = getenv('HTTP_FORWARDED');
	    else if(getenv('REMOTE_ADDR'))
	        $ipaddress = getenv('REMOTE_ADDR');
	    else
	        $ipaddress = 'UNKNOWN';
	 
	    return $ipaddress;
	}
}

WPLMS_oAuth_Server::init();


