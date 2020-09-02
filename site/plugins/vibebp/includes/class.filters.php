<?php
/**
 * Filters
 *
 * @class       VibeBP_Init
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VibeBP_Filters{

	
	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Filters();
        return self::$instance;
    }

	private function __construct(){
		add_filter( 'bp_activity_set_likes_scope_args', array($this,'bp_activity_filter_likes_scope'), 10, 2 );
		add_filter( 'bp_activity_set_following_scope_args', array($this,'bp_activity_filter_following_scope'), 10, 2 );

		add_filter('vibebp_api_get_user_from_token',array($this,'decode_token_return_user'),99,2);
		add_filter('vibebp_component_icon',array($this,'set_component_icon'),10,2);
		add_filter('vibebp_enqueue_profile_script',array($this,'enqueue_scripts'),9);
		add_filter('wplms_directory_single_member_view',array($this,'member_view'),10,2);
		add_filter('wplms_directory_single_group_view',array($this,'group_view'),10,2);


		add_filter('vibebp_precache_script',array($this,'remove_redundant_scripts'),10,2);
		add_filter('vibebp_precache_style',array($this,'remove_redundant_styles'),10,2);
		add_filter('vibebp_enable_registration',array($this,'enable_registrations'));

		//Social login brand icons
		add_filter('vibebp_vars',array($this,'social_brand_icons'));
	}

	function social_brand_icons($vars){
		
		if(vibebp_get_setting('use_brand_icons')){
			$vars['settings']['icons']=array(
                'google'=>'<svg width="46px" height="46px" viewBox="0 0 46 46" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns"> <title>btn_google_light_normal_ios</title> <desc>Created with Sketch.</desc> <defs> <filter x="-50%" y="-50%" width="200%" height="200%" filterUnits="objectBoundingBox" id="filter-1"> <feOffset dx="0" dy="1" in="SourceAlpha" result="shadowOffsetOuter1"></feOffset> <feGaussianBlur stdDeviation="0.5" in="shadowOffsetOuter1" result="shadowBlurOuter1"></feGaussianBlur> <feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.168 0" in="shadowBlurOuter1" type="matrix" result="shadowMatrixOuter1"></feColorMatrix> <feOffset dx="0" dy="0" in="SourceAlpha" result="shadowOffsetOuter2"></feOffset> <feGaussianBlur stdDeviation="0.5" in="shadowOffsetOuter2" result="shadowBlurOuter2"></feGaussianBlur> <feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.084 0" in="shadowBlurOuter2" type="matrix" result="shadowMatrixOuter2"></feColorMatrix> <feMerge> <feMergeNode in="shadowMatrixOuter1"></feMergeNode> <feMergeNode in="shadowMatrixOuter2"></feMergeNode> <feMergeNode in="SourceGraphic"></feMergeNode> </feMerge> </filter> <rect id="path-2" x="0" y="0" width="40" height="40" rx="2"></rect> </defs> <g id="Google-Button" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage"> <g id="9-PATCH" sketch:type="MSArtboardGroup" transform="translate(-608.000000, -160.000000)"></g> <g id="btn_google_light_normal" sketch:type="MSArtboardGroup" transform="translate(-1.000000, -1.000000)"> <g id="button" sketch:type="MSLayerGroup" transform="translate(4.000000, 4.000000)" filter="url(#filter-1)"> <g id="button-bg"> <use fill="#FFFFFF" fill-rule="evenodd" sketch:type="MSShapeGroup" xlink:href="#path-2"></use> <use fill="none" xlink:href="#path-2"></use> <use fill="none" xlink:href="#path-2"></use> <use fill="none" xlink:href="#path-2"></use> </g> </g> <g id="logo_googleg_48dp" sketch:type="MSLayerGroup" transform="translate(15.000000, 15.000000)"> <path d="M17.64,9.20454545 C17.64,8.56636364 17.5827273,7.95272727 17.4763636,7.36363636 L9,7.36363636 L9,10.845 L13.8436364,10.845 C13.635,11.97 13.0009091,12.9231818 12.0477273,13.5613636 L12.0477273,15.8195455 L14.9563636,15.8195455 C16.6581818,14.2527273 17.64,11.9454545 17.64,9.20454545 L17.64,9.20454545 Z" id="Shape" fill="#4285F4" sketch:type="MSShapeGroup"></path> <path d="M9,18 C11.43,18 13.4672727,17.1940909 14.9563636,15.8195455 L12.0477273,13.5613636 C11.2418182,14.1013636 10.2109091,14.4204545 9,14.4204545 C6.65590909,14.4204545 4.67181818,12.8372727 3.96409091,10.71 L0.957272727,10.71 L0.957272727,13.0418182 C2.43818182,15.9831818 5.48181818,18 9,18 L9,18 Z" id="Shape" fill="#34A853" sketch:type="MSShapeGroup"></path> <path d="M3.96409091,10.71 C3.78409091,10.17 3.68181818,9.59318182 3.68181818,9 C3.68181818,8.40681818 3.78409091,7.83 3.96409091,7.29 L3.96409091,4.95818182 L0.957272727,4.95818182 C0.347727273,6.17318182 0,7.54772727 0,9 C0,10.4522727 0.347727273,11.8268182 0.957272727,13.0418182 L3.96409091,10.71 L3.96409091,10.71 Z" id="Shape" fill="#FBBC05" sketch:type="MSShapeGroup"></path> <path d="M9,3.57954545 C10.3213636,3.57954545 11.5077273,4.03363636 12.4404545,4.92545455 L15.0218182,2.34409091 C13.4631818,0.891818182 11.4259091,0 9,0 C5.48181818,0 2.43818182,2.01681818 0.957272727,4.95818182 L3.96409091,7.29 C4.67181818,5.16272727 6.65590909,3.57954545 9,3.57954545 L9,3.57954545 Z" id="Shape" fill="#EA4335" sketch:type="MSShapeGroup"></path> <path d="M0,0 L18,0 L18,18 L0,18 L0,0 Z" id="Shape" sketch:type="MSShapeGroup"></path> </g> <g id="handles_square" sketch:type="MSLayerGroup"></g> </g> </g></svg>'
			);
		}

		return $vars;
	}
	

	function enqueue_scripts($return){
		
		if(function_exists('bp_is_user') && bp_is_user()){
			return true;
		}
		return $return;
	}

	function enable_registrations($return){
		$enabled = get_option('users_can_register');
		if(empty($enabled)){
			$return = false;
		}

		if(function_exists('vibe_get_option')){
			$custom_registration_page = vibe_get_option('custom_registration_page');
			if(!empty($custom_registration_page)){
				return get_permalink($custom_registration_page);
			}
		}
		return $return;
	}

	function decode_token_return_user($user,$token){
		/** Get the Secret Key */
		
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;

        if (!$secret_key) {
          	return false; 
        }

        try {
            $user_data = JWT::decode($token, $secret_key, array('HS256'));

	    	return $user_data->data->user;
    	}catch (Exception $e) {
            return false;
        }
	}

	function bp_activity_filter_following_scope( $retval = array(), $filter = array() ) {

		// Determine the user_id.
		if ( ! empty( $filter['user_id'] ) ) {
			$user_id = $filter['user_id'];
		} else {
			$user_id = bp_displayed_user_id()
				? bp_displayed_user_id()
				: bp_loggedin_user_id();
		}

		$following = bp_get_user_meta( $user_id, 'bp_following', true );
		
		//vibebp_activity_get_user_likes( $user_id );
		if ( empty( $following ) ) {
			$following = 0;
		}

		// Should we show all items regardless of sitewide visibility?
		$show_hidden = array();
		if ( ! empty( $user_id ) && ( $user_id !== bp_loggedin_user_id() ) ) {
			$show_hidden = array(
				'column' => 'hide_sitewide',
				'value'  => 0
			);
		}

		
		if(empty($following)){
			$following = array(0);
		}

		$retval = array(
			$show_hidden,
			// Overrides.
			'override' => array(
				'display_comments' => true,
				'filter'           => array( 
					'user_id' => $following,

				),
				'show_hidden'      => true
			),
		);


		return $retval;
	}


	function bp_activity_filter_likes_scope( $retval = array(), $filter = array() ) {

		// Determine the user_id.
		if ( ! empty( $filter['user_id'] ) ) {
			$user_id = $filter['user_id'];
		} else {
			$user_id = bp_displayed_user_id()
				? bp_displayed_user_id()
				: bp_loggedin_user_id();
		}

		// Determine the favorites.
		$favs = bp_get_user_meta( $user_id, 'bp_like_activities', true );
		//vibebp_activity_get_user_likes( $user_id );
		if ( empty( $favs ) ) {
			$favs = array( 0 );
		}

		// Should we show all items regardless of sitewide visibility?
		$show_hidden = array();
		if ( ! empty( $user_id ) && ( $user_id !== bp_loggedin_user_id() ) ) {
			$show_hidden = array(
				'column' => 'hide_sitewide',
				'value'  => 0
			);
		}

		$retval = array(
			'relation' => 'AND',
			array(
				'column'  => 'id',
				'compare' => 'IN',
				'value'   => (array) $favs
			),
			$show_hidden,

			// Overrides.
			'override' => array(
				'display_comments' => true,
				'filter'           => array( 'user_id' => 0 ),
				'show_hidden'      => true
			),
		);

		return $retval;
	}
	
	function set_component_icon($icon,$component_name){

		switch($component_name){
			case 'dashboard':
				$icon ='<svg width="24" height="24" viewBox="0 0 24 24" version="1.1" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path d="M24,15C24,17.184 23.414,19.233 22.39,20.999L20.654,19.996C21.505,18.525 22,16.822 22,15C22,9.477 17.523,5 12,5C6.477,5 2,9.477 2,15C2,16.822 2.495,18.525 3.346,19.996L1.61,20.999C0.586,19.233 0,17.184 0,15C0,8.383 5.383,3 12,3C18.617,3 24,8.383 24,15Z" style="fill-rule:nonzero;"/><path d="M20.021,12.593C19.88,12.166 19.707,11.749 19.505,11.351L17.051,12.457C17.268,12.851 17.441,13.267 17.568,13.699L20.021,12.593ZM7.448,11.69C7.719,11.336 8.028,11.015 8.367,10.733L6.477,8.764C6.149,9.058 5.84,9.379 5.559,9.721L7.448,11.69ZM9.163,10.175C9.542,9.954 9.944,9.779 10.361,9.652L9.327,7.083C8.917,7.225 8.515,7.401 8.129,7.607L9.163,10.175ZM6.404,13.791C6.525,13.356 6.692,12.937 6.902,12.541L4.433,11.475C4.236,11.878 4.069,12.297 3.935,12.725L6.404,13.791ZM15.838,7.591C15.451,7.386 15.048,7.212 14.638,7.072L13.614,9.645C14.031,9.77 14.434,9.944 14.814,10.164L15.838,7.591ZM18.439,9.721C18.157,9.379 17.849,9.058 17.521,8.764L15.631,10.733C15.97,11.015 16.278,11.337 16.549,11.69L18.439,9.721ZM12.648,6.662C12.429,6.644 12.211,6.636 11.999,6.636C11.787,6.636 11.568,6.645 11.349,6.662L11.349,9.446C11.565,9.421 11.783,9.408 11.999,9.408C12.215,9.408 12.433,9.42 12.648,9.446L12.648,6.662ZM12,21C10.706,21 9.657,19.951 9.657,18.657C9.657,17.774 10.146,17.005 10.867,16.606L12,11L13.133,16.605C13.855,17.004 14.343,17.773 14.343,18.656C14.343,19.951 13.294,21 12,21Z" style="fill-opacity:0.79;fill-rule:nonzero;"/></svg>';
			break;
			case 'groups':
				$icon ='<svg width="24" height="24" viewBox="0 0 24 24" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path d="M22.808,15.786C21.518,15.488 20.318,15.227 20.899,14.129C22.668,10.787 21.368,9 19.499,9C18.234,9 17.251,9.817 17.251,11.324C17.251,15.227 19.519,13.094 19.497,18L23.998,18L24,17.537C24,16.591 23.926,16.044 22.808,15.786ZM0.002,18L4.503,18C4.482,13.094 6.749,15.228 6.749,11.324C6.749,9.817 5.766,9 4.501,9C2.632,9 1.332,10.787 3.102,14.129C3.683,15.228 2.483,15.488 1.193,15.786C0.074,16.044 0,16.591 0,17.537L0.002,18Z" style="fill-opacity:0.71;fill-rule:nonzero;"/><path d="M17.997,18L6.002,18L6,17.377C6,16.118 6.1,15.391 7.588,15.047C9.272,14.658 10.932,14.311 10.133,12.838C7.767,8.475 9.459,6 11.999,6C14.49,6 16.225,8.383 13.865,12.839C13.09,14.303 14.691,14.651 16.41,15.048C17.9,15.392 17.999,16.12 17.999,17.381L17.997,18Z" style="fill-rule:nonzero;"/></svg>';
			break;
			case 'drive':
				$icon ='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M19 18.5c0-.276.224-.5.5-.5s.5.224.5.5-.224.5-.5.5-.5-.224-.5-.5zm5-2.5v6h-24v-6l5-14h14l5 14zm-16-6l4 4 4-4h-3v-5h-2v5h-3zm14 7h-20v3h20v-3z"/></svg>';
			break;
			case 'forums':
				$icon ='<svg width="24" height="24" viewBox="0 0 24 24" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path d="M24,20L21,20L21,24L15.667,20L8,20L8,16L10,16L10,18L16.333,18L19,20L19,18L22,18L22,9.999L20,9.999L20,7.999L24,7.999L24,20Z" style="fill-opacity:0.66;"/><path d="M18,14L8.333,14L3,18L3,14L0,14L0,-0.001L18,-0.001L18,14ZM9,9.916L4,9.916L4,11L9,11L9,9.916ZM14,7L4,7L4,8L14,8L14,7ZM14,4L4,4L4,5L14,5L14,4Z"/></svg>';
			break;
			case 'projects':
				$icon ='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M2 9l-1-7h5.694c1.265 1.583 1.327 2 3.306 2h13l-1 5h-4.193l-3.9-3-1.464 1.903 1.428 1.097h-1.971l-3.9-3-2.307 3h-3.693zm-2 2l2 11h20l2-11h-24z"/></svg>';
			break;
			case 'appointments':
				$icon ='<svg width="24" height="24" viewBox="0 0 24 24" version="1.1" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
			    <path d="M20,20L16,20L16,16L20,16L20,20ZM14,10L10,10L10,14L14,14L14,10ZM20,10L16,10L16,14L20,14L20,10ZM8,16L4,16L4,20L8,20L8,16ZM14,16L10,16L10,20L14,20L14,16ZM8,10L4,10L4,14L8,14L8,10Z" style="fill-opacity:0.67;fill-rule:nonzero;"/>
			    <path d="M24,2L24,24L0,24L0,2L3,2L3,3C3,4.103 3.897,5 5,5C6.103,5 7,4.103 7,3L7,2L17,2L17,3C17,4.103 17.897,5 19,5C20.103,5 21,4.103 21,3L21,2L24,2ZM22,8L2,8L2,22L22,22L22,8ZM20,1C20,0.448 19.553,0 19,0C18.447,0 18,0.448 18,1L18,3C18,3.552 18.447,4 19,4C19.553,4 20,3.552 20,3L20,1ZM6,3C6,3.552 5.553,4 5,4C4.447,4 4,3.552 4,3L4,1C4,0.448 4.447,0 5,0C5.553,0 6,0.448 6,1L6,3Z" style="fill-rule:nonzero;"/>
			</svg>';
			break;
			case 'activity':
				$icon ='<svg width="24" height="24" viewBox="0 0 24 24" version="1.1" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><g transform="matrix(1,0,0,1,1,1)"><path d="M10.043,18.153C9.752,16.952 8.781,12.625 8.781,12.625C8.595,12.857 8.31,13 8,13L5,13C4.448,13 4,12.552 4,12C4,11.448 4.448,11 5,11L7.279,11L8.051,8.684C8.205,8.223 8.572,8.001 8.937,8.001C9.339,8.001 9.74,8.27 9.854,8.783C10.071,9.763 10.812,13.187 10.812,13.187C10.812,13.187 11.889,5.958 12.041,4.862C12.12,4.295 12.567,4.003 13.014,4.003C13.431,4.003 13.848,4.258 13.966,4.782L15.215,11.38C15.401,11.145 15.688,11 16,11L19,11C19.552,11 20,11.448 20,12C20,12.552 19.552,13 19,13L16.721,13L15.949,15.316C15.632,16.266 14.469,16.343 14.188,15.218C13.895,14.045 13.094,10.218 13.094,10.218C13.094,10.218 12.132,17.004 11.979,18.103C11.899,18.676 11.455,18.992 11.007,18.992C10.596,18.993 10.182,18.728 10.043,18.153Z" style="fill-opacity:0.3;fill-rule:nonzero;"/></g><path d="M10.043,18.153C9.752,16.952 8.781,12.625 8.781,12.625C8.595,12.857 8.31,13 8,13L5,13C4.448,13 4,12.552 4,12C4,11.448 4.448,11 5,11L7.279,11L8.051,8.684C8.205,8.223 8.572,8.001 8.937,8.001C9.339,8.001 9.74,8.27 9.854,8.783C10.071,9.763 10.812,13.187 10.812,13.187C10.812,13.187 11.889,5.958 12.041,4.862C12.12,4.295 12.567,4.003 13.014,4.003C13.431,4.003 13.848,4.258 13.966,4.782L15.215,11.38C15.401,11.145 15.688,11 16,11L19,11C19.552,11 20,11.448 20,12C20,12.552 19.552,13 19,13L16.721,13L15.949,15.316C15.632,16.266 14.469,16.343 14.188,15.218C13.895,14.045 13.094,10.218 13.094,10.218C13.094,10.218 12.132,17.004 11.979,18.103C11.899,18.676 11.455,18.992 11.007,18.992C10.596,18.993 10.182,18.728 10.043,18.153Z" style="fill-rule:nonzero;"/></svg>';
			break;
			case 'profile':
				$icon ='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm7.753 18.305c-.261-.586-.789-.991-1.871-1.241-2.293-.529-4.428-.993-3.393-2.945 3.145-5.942.833-9.119-2.489-9.119-3.388 0-5.644 3.299-2.489 9.119 1.066 1.964-1.148 2.427-3.393 2.945-1.084.25-1.608.658-1.867 1.246-1.405-1.723-2.251-3.919-2.251-6.31 0-5.514 4.486-10 10-10s10 4.486 10 10c0 2.389-.845 4.583-2.247 6.305z"/></svg>';
			break;
			case 'messages':
				$icon ='<svg width="24" height="24" viewBox="0 0 24 24" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path d="M24,0L17.673,6.527L7.215,13.754L0,12L24,0ZM9,16.668L9,24L12.258,19.569L9,16.668Z" style="fill-rule:nonzero;"/><path d="M24,0L18,22L9.871,14.761L17.673,6.527L24,0Z" style="fill-opacity:0.67;fill-rule:nonzero;"/></svg>';
			break;
			case 'notifications':
				$icon ='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M15.137 3.945c-.644-.374-1.042-1.07-1.041-1.82v-.003c.001-1.172-.938-2.122-2.096-2.122s-2.097.95-2.097 2.122v.003c.001.751-.396 1.446-1.041 1.82-4.667 2.712-1.985 11.715-6.862 13.306v1.749h20v-1.749c-4.877-1.591-2.195-10.594-6.863-13.306zm-3.137-2.945c.552 0 1 .449 1 1 0 .552-.448 1-1 1s-1-.448-1-1c0-.551.448-1 1-1zm3 20c0 1.598-1.392 3-2.971 3s-3.029-1.402-3.029-3h6z"/></svg>';
			break;
			case 'friends':
				$icon ='<svg width="24" height="24" viewBox="0 0 24 24" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><g transform="matrix(0.24,0,0,0.24,0,0)"><g transform="matrix(4.16667,0,0,4.16667,2.46099e-15,0)"><path d="M12.683,10L11.398,6.667L21.028,6.667C21.625,6.667 22.149,7.063 22.312,7.637L23.971,13.493L24,13.703C24,14.016 23.809,14.305 23.505,14.423L23.504,14.424C23.144,14.563 22.737,14.416 22.551,14.077L20.835,10.971C20.835,10.971 20.472,19.801 20.343,22.921C20.317,23.525 19.823,24 19.22,24L19.219,24C18.628,24 18.144,23.541 18.099,22.953C17.997,21.656 17.725,18.553 17.643,17.325C17.609,16.815 17.236,16.515 16.831,16.515C16.468,16.515 16.06,16.815 16.027,17.325C15.944,18.553 15.672,21.656 15.571,22.953C15.525,23.541 15.041,24 14.451,24L14.449,24C13.847,24 13.352,23.525 13.327,22.921C13.197,19.801 12.683,10 12.683,10ZM16.835,0C15.179,0 13.835,1.344 13.835,3C13.835,4.656 15.179,6 16.835,6C18.49,6 19.835,4.656 19.835,3C19.835,1.344 18.49,0 16.835,0Z" style="fill-opacity:0.61;"/></g><g transform="matrix(4.16667,0,0,4.16667,2.46099e-15,0)"><path d="M4.781,24L4.78,24C4.177,24 3.683,23.525 3.657,22.921C3.528,19.801 3.165,10.971 3.165,10.971L1.449,14.077C1.263,14.416 0.856,14.563 0.496,14.424L0.495,14.423C0.191,14.305 0,14.016 0,13.703L0.029,13.493L1.688,7.637C1.851,7.063 2.375,6.667 2.972,6.667L12.43,6.667L11.335,10C11.335,10 10.803,19.801 10.673,22.921C10.648,23.525 10.153,24 9.551,24L9.549,24C8.959,24 8.475,23.541 8.429,22.953C8.328,21.656 8.056,18.553 7.973,17.325C7.94,16.815 7.532,16.515 7.169,16.515C6.764,16.515 6.391,16.815 6.357,17.325C6.275,18.553 6.003,21.656 5.901,22.953C5.856,23.541 5.372,24 4.781,24ZM7.165,0C8.821,0 10.165,1.344 10.165,3C10.165,4.656 8.821,6 7.165,6C5.51,6 4.165,4.656 4.165,3C4.165,1.344 5.51,0 7.165,0Z"/></g></g></svg>';
			break;
			case 'followers':
				$icon ='<svg width="24" height="24" viewBox="0 0 100 100" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path  serif:id=" " d="M81.25,62.5C70.904,62.5 62.5,70.896 62.5,81.25C62.5,91.604 70.904,100 81.25,100C91.596,100 100,91.604 100,81.25C100,70.896 91.596,62.5 81.25,62.5ZM91.667,83.333L83.333,83.333L83.333,91.667L79.167,91.667L79.167,83.333L70.833,83.333L70.833,79.167L79.167,79.167L79.167,70.833L83.333,70.833L83.333,79.167L91.667,79.167L91.667,83.333Z" style="fill-opacity:0.62;fill-rule:nonzero;"/><path serif:id=" " d="M61.75,100L0.021,100L0,94.829C0,84.329 0.829,78.267 13.242,75.4C27.263,72.163 41.108,69.263 34.45,56.992C14.729,20.621 28.825,0 50,0C78.129,0 81.275,31.646 65.167,56.996C59.783,65.458 54.167,72.121 54.167,81.25C54.167,88.529 57.067,95.129 61.75,100Z" style="fill-rule:nonzero;"/></svg>';
			break;
			case 'settings':
				$icon ='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M24 14v-4c-1.619 0-2.906.267-3.705-1.476-.697-1.663.604-2.596 1.604-3.596l-2.829-2.828c-1.033 1.033-1.908 2.307-3.666 1.575-1.674-.686-1.404-2.334-1.404-3.675h-4c0 1.312.278 2.985-1.404 3.675-1.761.733-2.646-.553-3.667-1.574l-2.829 2.828c1.033 1.033 2.308 1.909 1.575 3.667-.348.849-1.176 1.404-2.094 1.404h-1.581v4c1.471 0 2.973-.281 3.704 1.475.698 1.661-.604 2.596-1.604 3.596l2.829 2.829c1-1 1.943-2.282 3.667-1.575 1.673.687 1.404 2.332 1.404 3.675h4c0-1.244-.276-2.967 1.475-3.704 1.645-.692 2.586.595 3.596 1.604l2.828-2.829c-1-1-2.301-1.933-1.604-3.595l.03-.072c.687-1.673 2.332-1.404 3.675-1.404zm-12 2c-2.209 0-4-1.791-4-4s1.791-4 4-4 4 1.791 4 4-1.791 4-4 4z"/></svg>';
			break;
			case 'shop':
				$icon ='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path  d="M16 6v-2c0-2.209-1.791-4-4-4s-4 1.791-4 4v2h-5v18h18v-18h-5zm-7-2c0-1.654 1.346-3 3-3s3 1.346 3 3v2h-6v-2zm10 8h-14v-4h3v1.5c0 .276.224.5.5.5s.5-.224.5-.5v-1.5h6v1.5c0 .276.224.5.5.5s.5-.224.5-.5v-1.5h3v4z"/></svg>';
			break;
			case 'kb':
				$icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M6 22v-16h16v7.543c0 4.107-6 2.457-6 2.457s1.518 6-2.638 6h-7.362zm18-7.614v-10.386h-20v20h10.189c3.163 0 9.811-7.223 9.811-9.614zm-10 1.614h-5v-1h5v1zm5-4h-10v1h10v-1zm0-3h-10v1h10v-1zm2-7h-19v19h-2v-21h21v2z"/></svg>';
			break;
		}

		return $icon;
	}


	function member_view($return,$member_id){
		
		$layouts = new WP_Query(apply_filters('wplms_member_card',array(
			'post_type'=>'member-card',
			'posts_per_page'=>1
		)));

		if($layouts->have_posts()){
			while($layouts->have_posts()){
				$layouts->the_post();
				the_content();
				
			}
			$return=1;
		}

		return $return;
	}

	function group_view($return,$group_id){
		$layouts = new WP_Query(apply_filters('wplms_member_card',array(
			'post_type'=>'group-card',
			'posts_per_page'=>1
		)));

		if($layouts->have_posts()){
			while($layouts->have_posts()){
				$layouts->the_post();
				the_content();
				
			}
			$return=1;
		}

		return $return;
	}


	function remove_redundant_scripts($return,$script_handle){
		
		/* Eventon
		ajde_backender_styles
		colorpicker_styles
		evo_osmap
		evcal_ajax_handle
		evcal_functions
		evo_mouse
		evcal_easing
		evo_mobile
		evo_moment
		evo_handlebars
		evcal_addon
		evcal_troubleshoot
		select2
		evcal_backend_post_timepicker
		taxonomy
		*/

		if(in_array($script_handle,array('bp-confirm','add-to-cart','woocommerce','buddypress-js'))){
			return false;
		}

		return $return;
	}
	function remove_redundant_styles($return,$style_handle){

		/*
			ajde_backender_script
			backender_colorpicker
			eventon_init_gmaps_blank
			eventon_init_gmaps
			eventon_gmaps
			evo_osmap
		*/
		if(in_array($style_handle,array('woocommerce','buddypress'))){
			return false;
		}

		return $return;
	}

}

VibeBP_Filters::init();