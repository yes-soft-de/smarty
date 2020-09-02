<?php

defined( 'ABSPATH' ) or die();


if ( ! defined( 'BP_COURSE_API_NAMESPACE ' ) )
	define( 'BP_COURSE_API_NAMESPACE', 'wplms/v1' );

if ( ! class_exists( 'BP_Course_API' ) ) {

	/**
	 * WPLMS Course API class.
	 *
	 * @since 3.0.0
	 */
	class BP_Course_API {

		/**
		 * Initialize the Course API.
		 * 
		 * @since 3.0.0
		 */
		public static function initialize() {
	
			// Do nothing if the BP COURSE REST API is not enabled
			if ( false == bp_course_get_setting( 'api', 'api','bool' ) || false == self::is_wp_api_active() ) {
				return;
			}
			self::includes();
			self::hooks();
		}

		/**
		 * Includes the required plugin files.
		 * 
		 * @since 3.0.0
		 * @access private
		 */	
		private static function includes() {

			//$tips = WPLMS_tips::init();
			//if(isset($tips) && isset($tips->lms_settings) && isset($tips->lms_settings['api']) && isset($tips->lms_settings['api']['api'])){
		
			require_once dirname( __FILE__ ) . '/api/class-api-auth-server.php';
			require_once dirname( __FILE__ ) . '/api/class-api-controller.php';

			require_once dirname( __FILE__ ) . '/api/class-api-user-controller.php';
			require_once dirname( __FILE__ ) . '/api/class-api-course-controller.php';

			/**
			 * Fires when all BP COURSE API files are loaded.
			 *
			 * @since 3.0.0
			 */
			do_action( 'bp_course_api_loaded' );
		}

		/**
		 * Adds the required action hooks.
		 * 
		 * @since 3.0.0
		 * @access private
		 */
		private static function hooks() {
			add_action( 'rest_api_init', array( __CLASS__, 'create_rest_routes' ), 10 );
		}

		/**
		 * Creates the BP COURSE API endpoints.
		 * 
		 * @since 3.0.0
		 * @access private
		 */
		public static function create_rest_routes() {

			$types = array(
				'user',
				'course',
			);

			/**
			 * Filter the list of resource types.
			 * 
			 * @since 3.0.0
			 */
			$types = apply_filters( 'bp_course_api_types', $types );
			
			if ( is_array( $types ) && count( $types ) > 0 ) {
				foreach( $types as $type ) {
					$type = ucfirst( $type );
					$class_name = "BP_Course_Rest_{$type}_Controller";
					if ( class_exists( $class_name ) ) {
						$controller = new $class_name( $type );

						$controller->register_routes();
					}
				}
			}
			/**
			 * Fires after BP COURSE REST API routes are created.
			 *
			 * @since 3.0.0
			 */
			do_action( 'bp_course_api_init' );
		}

		/**
		 * Returns true if the WP API is active.
		 * 
		 * @since 3.0.0
		 * 
		 * @return bool
		 */
		public static function is_wp_api_active() {
			return class_exists( 'WP_REST_Controller' );
		}

		/**
		 * Displays an admin notice if the WP API is not available.
		 * 
		 * @since 3.0.0
		 */
		public static function missing_wp_api_notice() {
			if ( false != bp_course_get_setting( 'api', 'api', 'bool' ) && false == self::is_wp_api_active() ) {
				// REST API IS NOT ACTIVE
				add_action('admin_notices',function(){
					echo '<div class="error"><p>'.sprintf(__( 'REST API not active ! Please update WordPress %d or greater version.', 'vibe' ),'4.7').'</p></div>';
				});
			}
		}
		
	}

	add_action( 'init', array( 'BP_Course_API', 'initialize' ) );
	add_action( 'admin_notices', array( 'BP_Course_API', 'missing_wp_api_notice' ) );
}

/*
IN CASE PHP CORE FUNCTION IS NOT DEFINED //Happened in TEST CASE 1
 */

function vibe_getallheaders() 
{ 
   	$headers = array (); 
   	if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && !isset($_SERVER['HTTP_AUTHORIZATION'])) {
		$_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
	}
   	foreach ($_SERVER as $name => $value) 
   	{ 
       if (substr($name, 0, 5) == 'HTTP_') 
       { 
           $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
       } 
   	} 
   return $headers; 
} 
