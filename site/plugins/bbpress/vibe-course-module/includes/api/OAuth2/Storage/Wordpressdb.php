<?php
namespace OAuth2\Storage;

use OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;
use OAuth2\OpenID\Storage\UserClaimsInterface;

/**
 *  DATA MODEL
 *
 *  1. POST - Client ID & Secret with post type oauth_client
 *  2. Post Meta = Meta_key - auth_code, meta_value = array(authcode, userid,redirect_uri,expires,scope,id_token)
 *  3. POST META = meta_key = access_token , meta_value = array(token,user_id)
 */
class Wordpressdb
	implements AuthorizationCodeInterface, AccessTokenInterface, ClientCredentialsInterface, UserCredentialsInterface,
	           RefreshTokenInterface, JwtBearerInterface, ScopeInterface, PublicKeyInterface, UserClaimsInterface,
	           OpenIDAuthorizationCodeInterface {
	protected $db;
	protected $config;

	/**
	 * [__construct description]
	 *
	 * @param array $config Configuration for the WPDB Storage Object
	 */
	public function __construct( $config = array() ) {
		global $wpdb;
		$this->db = $wpdb;
		$this->apps = get_option('wplms_apps');
	}

	/**
	 * [checkClientCredentials description]
	 *
	 * @param  [type] $client_id     [description]
	 * @param  [type] $client_secret [description]
	 *
	 * @return [type]                [description]
	 */
	public function checkClientCredentials( $client_id, $client_secret = null ) {
		

		if(empty($this->apps)){
			return false;
		}

		foreach($this->apps as $app){
			if($app['app_id'] == $client_id){
				if(isset($client_secret)){
					if($client_secret == $app['app_secret']){

						return true;
					}else{

						return false;
					}
				}else{
					return true;
				}
			}	
		}
		
		return false;
	}

	/**
	 * [isPublicClient description]
	 *
	 * @param  [type]  $client_id [description]
	 *
	 * @return boolean            [description]
	 */
	public function isPublicClient( $client_id ) {
		
		if(empty($this->apps))
			return false;

		foreach($this->apps as $app){
			if($app['app_id'] == $client_id){
					return true;
			}	
		}
		return false;
	}

	/**
	 * [getClientDetails description]
	 *
	 * @param  [type] $client_id [description]
	 *
	 * @return [type]            [description]
	 */
	public function getClientDetails( $client_id ) {

		if(empty($this->apps))
			return false;

		foreach($this->apps as $app){
			if($app['app_id'] == $client_id){
				return $app;
			}	
		}
		return false;
	}

	/**
	 * [setClientDetails description]
	 *
	 * @param [type] $client_id     [description]
	 * @param [type] $client_secret [description]
	 * @param [type] $redirect_uri  [description]
	 * @param [type] $grant_types   [description]
	 * @param [type] $scope         [description]
	 * @param [type] $user_id       [description]
	 *
	 * @return false|int
	 */
	public function setClientDetails(
		$client_id, $client_secret = null, $redirect_uri = null, $grant_types = null, $scope = null, $user_id = null
	) {
		//Use Less function not used !
		if(empty($this->apps))
			return false;

		foreach($this->apps as $k => $app){
			if($app['app_id'] == $client_id){
				$this->apps[$k]['app_secret'] = $client_secret;
				$this->apps[$k]['redirect_uri'] = $redirect_uri;
				$this->apps[$k]['grant_types'] = $grant_types;
				$this->apps[$k]['scope'] = $scope;
				$this->apps[$k]['user_id'] = $user_id;
			}	
		}
	}

	/**
	 * [checkRestrictedGrantType description]
	 *
	 * @param  [type] $client_id  [description]
	 * @param  [type] $grant_type [description]
	 *
	 * @return [type]             [description]
	 */
	public function checkRestrictedGrantType( $client_id, $grant_type ) {
		$details = $this->getClientDetails( $client_id );
		if ( isset( $details['grant_types'] ) ) {
			$grant_types = $details['grant_types'];
			return in_array( $grant_type, (array) $grant_types );
		}

		return true;
	}

	/**
	 * [getAccessToken description]
	 *
	 * @param  [type] $access_token [description]
	 *
	 * @return [type]               [description]
	 */
	public function getAccessToken( $access_token ) {

		$token = $this->db->get_var( $this->db->prepare("SELECT meta_value FROM {$this->db->usermeta} WHERE meta_key = %s",$access_token));
		if ( null != $token ) {
			$token['expires'] = strtotime( $token['expires'] );
		}

		return $token;
	}

	/**
	 * [setAccessToken description]
	 *
	 * @param [type] $access_token [description]
	 * @param [type] $client_id    [description]
	 * @param [type] $user_id      [description]
	 * @param [type] $expires      [description]
	 * @param [type] $scope        [description]
	 */
	public function setAccessToken( $access_token, $client_id, $user_id, $expires, $scope = null ) {

		do_action( 'wplms_auth_set_access_token', array(
			'access_token' => $access_token,
			'client_id'    => $client_id,
			'user_id'      => $user_id
		) );

		$expires = date( 'Y-m-d H:i:s', $expires );
		
		$tokens = get_user_meta($user_id,'access_tokens',true);
		if(empty($tokens)){$tokens = array();}else if(in_array($access_token,$tokens)){$k = array_search($access_token, $tokens);unset($tokens[$k]);delete_user_meta($user_id,$access_token);
		}
		
		$tokens[] = $access_token;
		update_user_meta($user_id,'access_tokens',$tokens);

		$token = array(
			'access_token'=> $token,
			'client_id' => $client_id,
			'user_id'	=>	$user_id,
			'expires'	=> $expires,
			'scope'		=> $scope,
			);
		
		update_user_meta($user_id,$access_token,$token);

		return true;
	}

	public function unsetAccessToken($access_token){
		$user_id = $this->db->get_var("SELECT user_id FROM {$this->db->usermeta} WHERE meta_key = '".$access_token."'");

		$access_tokens = get_user_meta($user_id,'access_tokens',true);
		if(in_array($access_token,$access_tokens)){
			$k = array_search($access_token,$access_tokens);
			unset($access_tokens[$k]);
			update_user_meta($user_id,'access_tokens',$access_tokens);
		}
		return delete_user_meta($user_id,$access_token);
	}
	/**
	 * [getAuthorizationCode description]
	 *
	 * @param  [type] $code [description]
	 * @param  bool to return id_token key or not. Now that is the question!
	 *
	 * @return [type]       [description]
	 */
	public function getAuthorizationCode( $code ) {

		$user_id = $this->db->get_var("SELECT user_id FROM {$this->db->usermeta} WHERE meta_key = '".$code."'");
	
		$codes = get_user_meta($user_id,'auth_codes',true);
		if(is_array($codes) && in_array($code,$codes)){
			$auth_code =  get_user_meta($user_id,$code,true);
		
			if ( !empty($auth_code['expires'] ) ){
				$auth_code['expires'] = strtotime( $auth_code['expires'] )+3600;
			}else{
				$auth_code['expires'] = time();
			}	
		}else{
			return;	
		}
		

		/**
		 * This seems to be an issue and not return correctly. For now, lets return the queried object
		 *
		 * @todo This is messy and we need to look up PDO::FEATCH_BOTH
		 */
		return $auth_code;
	}

	/**
	 * [setAuthorizationCode description]
	 *
	 * @param [type] $code         [description]
	 * @param [type] $client_id    [description]
	 * @param [type] $user_id      [description]
	 * @param [type] $redirect_uri [description]
	 * @param [type] $expires      [description]
	 * @param [type] $scope        [description]
	 * @param [type] $id_token     [description]
	 */
	public function setAuthorizationCode(
		$code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null
	) {

		$auth_code = array(
			'authorization_code' =>$code ,
			'client_id' => $client_id,
			'user_id' => $user_id,
			'redirect_uri' => $redirect_uri,
			'expires' => date( 'Y-m-d H:i:s', $expires ),
			'scope' => $scope,
			'id_token' => $id_token
		);

		update_user_meta($user_id,$code,$auth_code);

		$codes = get_user_meta($user_id,'auth_codes',true);
		if(empty($codes)){
			$codes = array();
		}
		if(!in_array($code,$codes)){
			array_push($codes,$code);
		}
		update_user_meta($user_id,'auth_codes',$codes);

		return ;
	}



	/**
	 * [expireAuthorizationCode description]
	 *
	 * @param  [type] $code [description]
	 *
	 * @return [type]       [description]
	 */
	public function expireAuthorizationCode( $code ) {

		$user_id = $this->db->get_var("SELECT user_id FROM {$this->db->usermeta} WHERE meta_key = '".$code."'");
		$codes = get_user_meta($user_id,'auth_codes',true);
		if(is_array($codes) && in_array($code,$codes)){
			$k = array_search($code,$codes);
			unset($codes[$k]);
			update_user_meta($user_id,'auth_codes',$codes);
		}
		return delete_user_meta($user_id,$code);
	}

	/**
	 * [checkUserCredentials description]
	 *
	 * @param  [type] $username [description]
	 * @param  [type] $password [description]
	 *
	 * @return [type]           [description]
	 */
	public function checkUserCredentials( $username, $password ) {
		if ( $user = $this->getUser( $username ) ) {
			$login_check = $this->checkPassword( $user, $password );

			// @since 3.1.94 the parameter $user is being passed
			if ( ! $login_check ) {
				do_action( 'wplms_auth_failed_login', $user );
			}

			return $login_check;
		}
		do_action( 'wplms_auth_user_not_found' );

		return false;
	}

	/**
	 * [getUserDetails description]
	 *
	 * @param  [type] $username [description]
	 *
	 * @return [type]           [description]
	 */
	public function getUserDetails( $username ) {
		return $this->getUser( $username );
	}

	/**
	 * [getUserClaims description]
	 *
	 * @param  [type] $user_id [description]
	 * @param  [type] $claims  [description]
	 *
	 * @return [type]          [description]
	 *
	 * @since  3.0.5-alpha Claims are handled manually since it just makes more sense this way
	 */
	public function getUserClaims( $user_id, $claims ) {

		// Grab the user information for the ID
		$userInfo = get_userdata( $user_id );

		// Split up the claims
		$claims = explode( ' ', trim( $claims ) );

		// User claims array
		$userClaims = array();

		// If the scope "email" is found
		if ( in_array( 'email', $claims ) ) {
			$userClaims += array(
				'email'          => $userInfo->user_email,
				'email_verified' => ''
			);
		}

		// If the scope "profile" is found
		if ( in_array( 'profile', $claims ) ) {
			$userClaims += array(
				'name'               => $userInfo->display_name,
				'family_name'        => '',
				'given_name'         => '',
				'middle_name'        => '',
				'nickname'           => '',
				'preferred_username' => $userInfo->display_name,
				'profile'            => '',
				'picture'            => 'http://www.gravatar.com/avatar/'
				                        . md5( strtolower( trim( $userInfo->user_email ) ) ) . '?s=40',
				'website'            => $userInfo->user_url,
				'gender'             => '',
				'birthdate'          => '',
				'zoneinfo'           => get_option( 'timezone_string' ),
				'updated_at'         => $userInfo->user_registered,
			);
		}

		// If the scope "address" is found
		if ( in_array( 'address', $claims ) ) {
			$userClaims += array(
				'formatted'      => '',
				'street_address' => '',
				'locality'       => '',
				'region'         => '',
				'postal_code'    => '',
				'country'        => '',
			);
		}

		// If the scope "phone" is found
		if ( in_array( 'phone', $claims ) ) {
			$userClaims += array(
				'phone_number'          => '',
				'phone_number_verified' => '',
			);
		}

		return $userClaims;
	}

	/**
	 * [getUserClaim description]
	 *
	 * @param  [type] $claim       [description]
	 * @param  [type] $userDetails [description]
	 *
	 * @return [type]              [description]
	 *
	 * @todo   Check
	 */
	protected function getUserClaim( $claim, $userDetails ) {
		$userClaims        = array();
		$claimValuesString = constant( sprintf( 'self::%s_CLAIM_VALUES', strtoupper( $claim ) ) );
		$claimValues       = explode( ' ', $claimValuesString );

		foreach ( $claimValues as $value ) {
			$userClaims[ $value ] = isset( $userDetails[ $value ] ) ? $userDetails[ $value ] : null;
		}

		return $userClaims;
	}

	/**
	 * [getRefreshToken description]
	 *
	 * @param  [type] $refresh_token [description]
	 *
	 * @return string
	 */
	public function getRefreshToken( $refresh_token ) {

		$refresh_tokens = get_user_meta($user_id,'refresh_tokens',true);
		if(isset($refresh_tokens) && !in_array($refresh_token,$refresh_tokens)){
			return;
		}

		$token = get_user_meta($user_id,$refresh_token,true);

		if ( isset($token)) {
			$token['expires'] = strtotime( $stmt['expires'] );
		}

		return $token;
	}

	/**
	 * [setRefreshToken description]
	 *
	 * @param [type] $refresh_token [description]
	 * @param [type] $client_id     [description]
	 * @param [type] $user_id       [description]
	 * @param [type] $expires       [description]
	 * @param [type] $scope         [description]
	 *
	 * @return false|int
	 */
	public function setRefreshToken( $refresh_token, $client_id, $user_id, $expires, $scope = null ) {
		$expires = date( 'Y-m-d H:i:s', $expires );

		$token = array( 
			'refresh_token' => $refresh_token, 
			'client_id' => $client_id, 
			'user_id' => $user_id, 
			'expires' => $expires, 
			'scope' => $scope ) ;
		
		$refresh_tokens = get_user_meta($user_id,'refresh_tokens',true);
		if(is_array($refresh_tokens) && !in_array($refresh_token,$refresh_tokens)){
			$refresh_tokens[] = $refresh_token;
			update_user_meta($user_id,'refresh_tokens',$refresh_tokens);
		}
		return update_user_meta($user_id,$refresh_token,$token);
	}

	/**
	 * [unsetRefreshToken description]
	 *
	 * @param  [type] $refresh_token [description]
	 *
	 * @return false|int
	 */
	public function unsetRefreshToken( $refresh_token ) {

		$user_id = $this->db->get_var("SELECT user_id FROM {$this->db->usermeta} WHERE meta_key = '".$refresh_token."'");

		$refresh_tokens = get_user_meta($user_id,'refresh_tokens',true);
		if(in_array($refresh_token,$refresh_tokens)){
			$k = array_search($refresh_token,$refresh_tokens);
			unset($refresh_tokens[$k]);
			update_user_meta($user_id,'refresh_tokens',$refresh_tokens);
		}
		return delete_user_meta($user_id,$refresh_token);
	}

	/**
	 * Check the user login credentials
	 *
	 * @param  [type] $user     [description]
	 * @param  [type] $password [description]
	 *
	 * @return [type]           [description]
	 *
	 *
	 */
	protected function checkPassword( $user, $password ) {
		$login_check = wp_check_password( $password, $user['user_pass'], $user['ID'] );
		if ( ! $login_check ) {
			do_action( 'wp_login_failed', $user['user_login'] );
		}

		return $login_check;
	}

	/**
	 * Retrieve a user ID from the database
	 *
	 * @param  [type] $username [description]
	 *
	 * @return [type]           [description]
	 */
	public function getUser( $username ) {
		$field = ( false === filter_var( $username, FILTER_VALIDATE_EMAIL ) ) ? 'login' : 'email';
		$user  = get_user_by( $field, $username );
		if ( false === $user ) {
			return false;
		}
		$userInfo = (array) $user->data;

		return array_merge( array(
			'user_id' => $userInfo['ID']
		), $userInfo );
	}

	/**
	 * Check to see is a scope exists in the database
	 *
	 * @param  [type] $scope [description]
	 *
	 * @return [type]        [description]
	 */
	public function scopeExists( $scope ) {
		$scope   = explode( ' ', $scope );
		$scopes = get_option('wplms_auth_scopes');
		if(is_array($scope)){
			foreach($scope as $s){
				if(!in_array($s,$scopes)){
					return false;
				}
			}
			return true;
		}else if(in_array($scope,$scopes)){
			return true;
		}
		return false;
	}

	/**
	 * Get the default scope from the database
	 *
	 * @param  [type] $client_id [description]
	 *
	 * @return [type]            [description]
	 */
	public function getDefaultScope( $client_id = null ) {
		return 'basic';
	}

	

	/**
	 * [getClientScope description]
	 *
	 * @param  [type] $client_id [description]
	 *
	 * @return [type]            [description]
	 */
	public function getClientScope( $client_id ) {
		if ( ! $clientDetails = $this->getClientDetails( $client_id ) ) {
			return false;
		}

		if ( isset( $clientDetails['scope'] ) ) {
			return $clientDetails['scope'];
		}

		return null;
	}

	/**
	 * 
	 *
	 *
	 *
	 *
	 *    FUNCTIONS NOT IN USE
	 *    FUNCTIONS NOT IN USE
	 *    FUNCTIONS NOT IN USE
	 *    FUNCTIONS NOT IN USE
	 *
	 * 
	/**
	 * [getClientKey description]
	 *
	 * @param  [type] $client_id [description]
	 * @param  [type] $subject   [description]
	 *
	 * @return [type]            [description]
	 */
	public function getClientKey( $client_id, $subject ) {
		$stmt
			= $this->db->prepare( "SELECT public_key from {$this->db->prefix}oauth_jwt where client_id=%s AND subject=%s",
			array( $client_id, $subject ) );

		return $this->db->get_col( $stmt );
	}
	/**
	 * [getJti description]
	 *
	 * @param  [type] $client_id [description]
	 * @param  [type] $subject   [description]
	 * @param  [type] $audience  [description]
	 * @param  [type] $expires   [description]
	 * @param  [type] $jti       [description]
	 *
	 * @return [type]            [description]
	 *
	 * @todo   Check for Removal
	 */
	public function getJti( $client_id, $subject, $audience, $expires, $jti ) {
		$stmt = $this->db->prepare( $sql
			= sprintf( 'SELECT * FROM %s WHERE issuer=:client_id AND subject=:subject AND audience=:audience AND expires=:expires AND jti=:jti',
			$this->config['jti_table'] ) );

		$stmt->execute( compact( 'client_id', 'subject', 'audience', 'expires', 'jti' ) );

		if ( $result = $stmt->fetch() ) {
			return array(
				'issuer'   => $result['issuer'],
				'subject'  => $result['subject'],
				'audience' => $result['audience'],
				'expires'  => $result['expires'],
				'jti'      => $result['jti'],
			);
		}

		return null;
	}

	/**
	 * [setJti description]
	 *
	 * @param [type] $client_id [description]
	 * @param [type] $subject   [description]
	 * @param [type] $audience  [description]
	 * @param [type] $expires   [description]
	 * @param [type] $jti       [description]
	 *
	 * @todo  Check for removal
	 */
	public function setJti( $client_id, $subject, $audience, $expires, $jti ) {
		$stmt
			= $this->db->prepare( sprintf( 'INSERT INTO %s (issuer, subject, audience, expires, jti) VALUES (:client_id, :subject, :audience, :expires, :jti)',
			$this->config['jti_table'] ) );

		return $stmt->execute( compact( 'client_id', 'subject', 'audience', 'expires', 'jti' ) );
	}

	/**
	 * [getPublicKey description]
	 *
	 * @param  [type] $client_id [description]
	 *
	 * @return [type]            [description]
	 */
	public function getPublicKey( $client_id = null ) {
		$stmt
			  = $this->db->prepare( "SELECT public_key FROM {$this->db->prefix}oauth_public_keys WHERE client_id=%s OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC",
			array( $client_id ) );
		$stmt = $this->db->get_row( $stmt, ARRAY_A );

		if ( null != $stmt ) {
			return $result['public_key'];
		}
	}

	/**
	 * [getPrivateKey description]
	 *
	 * @param  [type] $client_id [description]
	 *
	 * @return [type]            [description]
	 */
	public function getPrivateKey( $client_id = null ) {
		$stmt
			  = $this->db->prepare( "SELECT private_key FROM {$this->db->prefix}oauth_public_keys WHERE client_id=%s OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC",
			array( $client_id ) );
		$stmt = $this->db->get_row( $stmt, ARRAY_A );

		if ( null != $stmt ) {
			return $stmt['private_key'];
		}
	}

	/**
	 * [getEncryptionAlgorithm description]
	 *
	 * @param  [type] $client_id [description]
	 *
	 * @return [type]            [description]
	 */
	public function getEncryptionAlgorithm( $client_id = null ) {
		$stmt
			  = $this->db->prepare( "SELECT encryption_algorithm FROM {$this->db->prefix}oauth_public_keys WHERE client_id=%s OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC",
			array( $client_id ) );
		$stmt = $this->db->get_row( $stmt, ARRAY_A );

		if ( null != $stmt ) {
			return $stmt['encryption_algorithm'];
		}
	}
}
