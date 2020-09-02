<?php
/**
 * Action functions for Course Module
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Vibe Course Module
 * @version     2.0
 */

 if ( ! defined( 'ABSPATH' ) ) exit;



defined('Vibe_BP_API_GROUPS_TYPE') or define('Vibe_BP_API_GROUPS_TYPE', (function_exists('buddypress')?(!empty(buddypress()->groups)?buddypress()->groups->id:''):''));
defined('Vibe_BP_API_MEMBERS_TYPE') or define('Vibe_BP_API_MEMBERS_TYPE', 'members');
defined('Vibe_BP_API_MESSAGES_TYPE') or define('Vibe_BP_API_MESSAGES_TYPE', 'messages');
defined('Vibe_BP_API_ACTIVITY_TYPE') or define('Vibe_BP_API_ACTIVITY_TYPE', 'activity');
defined('Vibe_BP_API_NOTIFICATIONS_TYPE') or define('Vibe_BP_API_NOTIFICATIONS_TYPE', 'notifications');
defined('Vibe_BP_API_SETTINGS_TYPE') or define('Vibe_BP_API_SETTINGS_TYPE', 'settings');
defined('Vibe_BP_API_XPROFILE_TYPE') or define('Vibe_BP_API_XPROFILE_TYPE', 'xprofile');


if ( ! class_exists( 'Vibe_BP_API' ) ) {

	class Vibe_BP_API {
		public static $instance;
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new Vibe_BP_API();
	        return self::$instance;
	    }
	    public function __construct( ) {
	    	$this->include_files();
		}
		public function include_files(){
			require_once dirname( __FILE__ ) . '/class-api-controller.php';
			require_once dirname( __FILE__ ) . '/class.members.php';
			require_once dirname( __FILE__ ) . '/class.groups.php';
			require_once dirname( __FILE__ ) . '/class-api-members-controller.php';
			require_once dirname( __FILE__ ) . '/class-api-groups-controller.php';
			require_once dirname( __FILE__ ) . '/class-api-messages-controller.php';
			require_once dirname( __FILE__ ) . '/class-api-activity-controller.php';
			require_once dirname( __FILE__ ) . '/class-api-notifications-controller.php';
			require_once dirname( __FILE__ ) . '/class-api-settings-controller.php';
			require_once dirname( __FILE__ ) . '/class-api-xprofile-controller.php';
			
			if(function_exists('WC')){
				require_once dirname( __FILE__ ) . '/class-api-woocommerce-controller.php';	
			}
			require_once dirname( __FILE__ ) . '/class.init.php';

		}
	}
}

/*
*	Include file when Api hit makes
*/	
add_action('rest_api_init',function(){ 
	Vibe_BP_API::init();
});

