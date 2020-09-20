<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// Include this library


class WPLMS_oAuth_Server{

	var $settings;
	var $temp;

	public static $instance;
	
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_oAuth_Server();
        return self::$instance;
    }

	private function __construct(){
		$tips = WPLMS_tips::init();
		if(isset($tips) && isset($tips->lms_settings) && isset($tips->lms_settings['api']) && isset($tips->lms_settings['api']['api'])){
			$this->namespace = 'wplms/v1';
			add_action('rest_api_init',array($this,'authentication_endpoint'));
		}
	}

	function authentication_endpoint(){

	    register_rest_route( $this->namespace, '/auth/', array(
	        'methods' => 'POST',
	        'callback' => array($this,'load_auth'),
	    ) );
	}

	function load_auth($request){

		do_action( 'wplms_before_auth', array( $_REQUEST ) );
		require_once( '/OAuth2/Autoloader.php' );
		OAuth2\Autoloader::register();

		/*
		$server  = new OAuth2\Server( new OAuth2\Storage\Wordpressdb() );
		$authrequest = OAuth2\Request::createFromGlobals();

		if ( $server->verifyResourceRequest( $authrequest ) ) {
			$token = $server->getAccessTokenData( $authrequest );
			if ( isset( $token['user_id'] ) && $token['user_id'] > 0 ) {
				return (int) $token['user_id'];
			} elseif ( isset( $token['user_id'] ) && $token['user_id'] === 0 ) {

			}
		}*/

		//
		

		global $wp_query;
		$method     = $wp_query->get( 'oauth' );
		$well_known = $wp_query->get( 'well-known' );
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
		if ( '1' == $o['auth_code_enabled'] ) {
			$server->addGrantType( new OAuth2\GrantType\AuthorizationCode( $storage ) );
		}

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
			'email'
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
			do_action( 'wplms_auth_before_token_method', array( $_REQUEST ) );
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
			do_action( 'wplms_auth_before_authorize_method', array( $_REQUEST ) );
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
		$ext_methods = apply_filters( "wplms_auth_endpoints", null );

		// Check to see if the method exists in the filter
		if ( array_key_exists( $method, $ext_methods ) ) {

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
}

WPLMS_oAuth_Server::init();


