<?php
/*
 Plugin Name: EventON - Daily View 
 Plugin URI: http://www.myeventon.com/addons/daily-view/
 Description: Adds the capabilities to create a calendar with horizontally scrollable list of days of the month right below month title and sort bar. Read the guide for more information on how to use this addon.
 Author: Ashan Jay
 Version: 0.31
 Author URI: http://www.ashanjay.com/
 Requires at least: 3.8
 Tested up to: 4.2.2
 */
 
class EventON_daily_view{
	
	public $version='0.31';
	public $eventon_version = '2.3.1';
	public $name = 'DailyView';
		
	public $is_running_dv =false;
	
	public $addon_data = array();
	public $slug, $plugin_slug , $plugin_url , $plugin_path ;
	public $template_url ;	
	private $urls;
	
	public $shortcode_args;
	
	// construct
	public function __construct(){
		
		$this->super_init();

		include_once( 'includes/admin/class-admin_check.php' );
		$this->check = new addon_check($this->addon_data);
		$check = $this->check->initial_check();
		
		if($check){
			$this->addon = new evo_addon($this->addon_data);
			add_action( 'init', array( $this, 'init' ), 0 );	
		}
	}	
	
	// SUPER init
		function super_init(){
			// PLUGIN SLUGS			
			$this->addon_data['plugin_url'] = plugins_url('/'.basename(dirname(__FILE__)),dirname(__FILE__));//path_join(WP_PLUGIN_URL, basename(dirname(__FILE__)));

			$this->addon_data['plugin_slug'] = plugin_basename(__FILE__);
			list ($t1, $t2) = explode('/', $this->addon_data['plugin_slug'] );
	        $this->addon_data['slug'] = $t1;
	        $this->addon_data['plugin_path'] = dirname( __FILE__ );
	        $this->addon_data['evo_version'] = $this->eventon_version;
	        $this->addon_data['version'] = $this->version;
	        $this->addon_data['name'] = $this->name;

	        $this->plugin_url = $this->addon_data['plugin_url'];
	        $this->plugin_slug = $this->addon_data['plugin_slug'];
	        $this->slug = $this->addon_data['slug'];
	        $this->plugin_path = $this->addon_data['plugin_path'];
		}

	// INITIATE please
		function init(){				
			// Activation
			$this->activate();

			// Deactivation
			register_deactivation_hook( __FILE__, array($this,'deactivate'));
						
			// RUN addon updater only in dedicated pages
			if ( is_admin() ){
				$this->addon->updater();
				include_once( 'includes/admin/admin-init.php' );
			}

			include_once( 'includes/class-frontend.php' );
			include_once( 'includes/eventonDV_shortcode.php' );
			
			if ( defined('DOING_AJAX') ){
				include_once( 'includes/eventonDV_ajax.php' );
			}

			$this->shortcodes = new evo_dv_shortcode();
			$this->frontend = new evodv_frontend();
		}		
	
	// ACTIVATION	
		function activate(){
			// add actionUser addon to eventon addons list
			$this->addon->activate();
		}		
	
		// Deactivate addon
		function deactivate(){
			$this->addon->remove_addon();
		}
}

// Initiate this addon within the plugin
$GLOBALS['eventon_dv'] = new EventON_daily_view();

// php tag
function add_eventon_dv($args=''){	
	global $eventon_dv;
	echo $eventon_dv->shortcodes->evoDV_generate_calendar($args);
}
?>