<?php
/**
 * Envato Theme Setup Wizard Class
 *
 * Takes new users through some basic steps to setup their ThemeForest theme.

 *
 * Based off the WooThemes installer.
 *
 *
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Envato_Theme_Setup_Wizard' ) ) {
	/**
	 * Envato_Theme_Setup_Wizard class
	 */
	class Envato_Theme_Setup_Wizard {

		/**
		 * The class version number.
		 *
		 * @since 1.1.1
		 * @access private
		 *
		 * @var string
		 */
		protected $version = '3.4';

		/** @var string Current theme name, used as namespace in actions. */
		protected $theme_name = 'wplms';

		/** @var string Theme author username, used in check for oauth. */
		protected $envato_username = 'vibethemes';

		protected $oauth_script = '';

		/** @var string Current Step */
		protected $step = '';

		/** @var array Steps for the setup wizard */
		protected $steps = array();

		/**
		 * Relative plugin path
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $plugin_path = '';

		/**
		 * Relative plugin url for this plugin folder, used when enquing scripts
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $plugin_url = '';

		/**
		 * The slug name to refer to this menu
		 *
		 * @since 1.1.1
		 *
		 * @var string
		 */
		protected $page_slug;

		/**
		 * TGMPA instance storage
		 *
		 * @var object
		 */
		protected $tgmpa_instance;

		/**
		 * TGMPA Menu slug
		 *
		 * @var string
		 */
		public $tgmpa_menu_slug = 'tgmpa-install-plugins';

		/**
		 * TGMPA Menu url
		 *
		 * @var string
		 */
		public $tgmpa_url = 'themes.php?page=tgmpa-install-plugins';

		/**
		 * The slug name for the parent menu
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $page_parent;

		/**
		 * Complete URL to Setup Wizard
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $page_url;

		/**
		 * @since 1.1.8
		 *
		 */
		public $site_styles = array();
		/**
		 * @since 1.1.8
		 *
		 */
		public $debug = 0;
		/**
		 * @since 1.1.8
		 *
		 */
		public $features = array();

		/**
		 * Holds the current instance of the theme manager
		 *
		 * @since 1.1.3
		 * @var Envato_Theme_Setup_Wizard
		 */
		private static $instance = null;

		/**
		 * @since 1.1.3
		 *
		 * @return Envato_Theme_Setup_Wizard
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * A dummy constructor to prevent this class from being loaded more than once.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.1
		 * @access private
		 */
		public function __construct() {
			$this->init_globals();
			$this->init_actions();

			//Ajax Calls
			add_action('wp_ajax_wplms_exported_content_plupload',array($this,'wplms_exported_content_plupload'));
			add_action('wp_ajax_insert_export_content_final',array($this,'insert_export_content_final'));
		}

		/**
		 * Get the default style. Can be overriden by theme init scripts.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.7
		 * @access public
		 */
		public function get_default_theme_style() {
			return 'demo1';
		}

		/**
		 * Get the default style. Can be overriden by theme init scripts.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.9
		 * @access public
		 */
		public function get_header_logo_width() {
			return '80px';
		}

		/**
		 * Get the default style. Can be overriden by theme init scripts.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.9
		 * @access public
		 */
		public function get_logo_image() {
			$image_url = '';
			return apply_filters( 'envato_setup_logo_image', $image_url );
		}

		/**
		 * Setup the class globals.
		 *
		 * @since 1.1.1
		 * @access public
		 */
		public function init_globals() {

			$current_theme         = wp_get_theme();
			$this->theme_name      = 'wplms';//strtolower( preg_replace( '#[^a-zA-Z]#', '', $current_theme->get( 'Name' ) ) );
			$this->envato_username = apply_filters( $this->theme_name . '_theme_setup_wizard_username', 'vibethemes' );
			$this->oauth_script    = apply_filters( $this->theme_name . '_theme_setup_wizard_oauth_script', 'https://wplms.io/api/server-script.php' );
			$this->page_slug       = apply_filters( $this->theme_name . '_theme_setup_wizard_page_slug', $this->theme_name . '-setup' );
			$this->parent_slug     = apply_filters( $this->theme_name . '_theme_setup_wizard_parent_slug', '' );

			$this->features = array(
								
	                    		'courses'=>array(
	                    						'label'=>__('[ RECOMMENDED ] LMS'),
	                    						'default'=>1,
	                    						'icon'=>'images/courses.png',
	                    						'description'=> __('eLearning and Course management. In built student management, instructor management, certificates, badges, quizzing platform, question bank, commissions, dashboards and much more.  Create an eLearning site with unlimited possibilities.','vibe'),
	                    						'verify'=>array('vibe-course-module/loader.php','vibe-customtypes/vibe-customtypes.php','vibe-shortcodes/vibe-shortcodes.php','wplms-front-end/wplms-front-end.php','wplms-assignments/wplms-assignments.php',
	                    							'wplms-dashboard/wplms-dashboard.php','buddypress/bp-loader.php')
                    						),
	                    		'woocommerce'=>array(
	                    						'label'=>__('[ RECOMMENDED ] eCommerce'),
	                    						'icon'=>'images/woocommerce.png',
	                    						'default'=>1,
	                    						'description'=> __('Create and sell courses online. Allow instructors to set price for their courses, earn commissions and much more.','vibe'),
	                    						'verify'=>array('woocommerce/woocommerce.php')
                    						),
	                    		'slider'=>array(
	                    						'label'=>__('[ RECOMMENDED ] Slider'),
	                    						'icon'=>'images/Layout-Design.png',
	                    						'default'=>1,
	                    						'description'=> __('Create unlimited slideshows in your site. Includes LayerSlider and Revolution slider top selling premium slider plugins. Recommended for sample data import.','vibe'),
	                    						'verify'=>array('layerslider/layerslider.php','revslider/revslider.php')
                    						),
	                    		'vc'=>array(
	                    						'label'=>__('Visual Composer'),
	                    						'icon'=>'images/directory.png',
	                    						'description'=> __('The top selling premium page builder for WordPress.','vibe'),
	                    						'verify'=>array('js_composer/js_composer.php','wplms_vc_templates/wplms_vc_templates.php')
                    						),
	                    		'events'=>array(
	                    						'label'=>__('Events'),
	                    						'icon'=>'images/calendar.png',
	                    						'description'=> __('Site and Course Events with EventON. Physical events with Google maps. Instructors can create events for their courses.','vibe'),
	                    						'verify'=>array('eventON/eventon.php','wplms-eventon/wplms-eventon.php')
                    						),
	                    		'drive'=>array(
	                    						'label'=>__('Drive','vibe'),
	                    						'icon'=>'images/drive.png',
	                    						'description'=> __('Enable users to upload and share material among themselves. Instructor can create a course drive and course users can download uploads.','vibe'),
	                    						'verify'=>array('buddydrive/buddydrive.php'),
                    						),
	                    		'forums'=>array(
	                    						'label'=>__('Discussion forums'),
	                    						'icon'=>'images/disscusion.png',
	                    						'description'=> __('Discussion Forums with BBPress. Allow students to interact with each other by using forums. Enable Course specific forums for course users only.','vibe'),
	                    						'verify'=>array('bbpress/bbpress.php'),
                    						),
	                    		'points'=>array(
	                    						'label'=>__('Gamification & Points'),
	                    						'icon'=>'images/single-course.png',
	                    						'description'=> __('Points system for the site. Allow users to purchase courses using Points. Award points on various action points in the system.','vibe'),
	                    						'verify'=>array('mycred/mycred.php','wplms-mycred-addon/wplms-mycred-addon.php'),
                    						),
	                    		'memberships'=>array(
	                    						'label'=>__('Memberships'),
	                    						'icon'=>'images/memberships.png',
	                    						'description'=> __('Sell courses via memberships, create and connect memberships with courses.','vibe'),
	                    						'verify'=>array('paid-memberships-pro/paid-memberships-pro.php')
                    						),
	                    		'badgeos'=>array(
	                    						'label'=>__('Custom Badges'),
	                    						'icon'=>'images/badges.png',
	                    						'description'=> __('Award custom badges to users on various action points. Uses BadgeOS from mozilla open badges.','vibe'),
	                    						'verify'=>array('badgeos/badgeos.php','WPLMS-BadgeOS/badgeos-wplms.php','badgeos-community-add-on/badgeos-community.php'),
	                    						
                    						),
	                    		'multiinstructor'=>array(
	                    						'label'=>__('Multiple Instructors per course'),
	                    						'icon'=>'images/multiinstructor.png',
	                    						'description'=> __('Enable multiple instructors per course. Add more than 1 isntructor per course. ','vibe'),
	                    						'verify'=>array('co-authors-plus/co-authors-plus.php','WPLMS-Coauthors-Plus/wplms-coauthor-plus.php')
                    						),
	                    		'bigbluebutton'=>array(
	                    						'label'=>__('Video Conferencing'),
	                    						'icon'=>'images/single-instructor.png',
	                    						'description'=> __('Enable Video conferencing with BigBlueButton. ','vibe'),
	                    						'verify'=>array('bigbluebutton/bigbluebutton-plugin.php','wplms-bbb/wplms-bbb.php')
                    						),
	                    		'h5p'=>array(
	                    						'label'=>__('H5P Interactive Learning'),
	                    						'icon'=>'images/messaging.png',
	                    						'description'=> 'Enable Video conferencing with H5P. ',
	                    						'verify'=>array('h5p/h5p.php','wplms-h5p/wplms-h5p.php')
                    						),
	                    		
	                    	);

			// create an images/styleX/ folder for each style here.
			$this->site_styles = array(
               'demo1' => array('label'=>'Demo 1','link'=>'https://wplms.io/demos/demo1/'),
               'demo2' => array('label'=>'Demo 2','link'=>'https://wplms.io/demos/demo2/'),
               'demo3' => array('label'=>'Demo 3','link'=>'https://wplms.io/demos/demo3/'),
               'demo4' => array('label'=>'Demo 4','link'=>'https://wplms.io/demos/demo4/'),
               'demo5' => array('label'=>'Demo 5','link'=>'https://wplms.io/demos/demo5/'),
               'demo6' => array('label'=>'Demo 6','link'=>'https://wplms.io/demos/demo6/'),
               'demo7' => array('label'=>'Demo 7','link'=>'https://wplms.io/demos/demo7/'),
               'demo8' => array('label'=>'Demo 8','link'=>'https://wplms.io/demos/demo8/'),
               'demo9' => array('label'=>'Demo 9','link'=>'https://wplms.io/demos/demo9/'),
               'demo10' => array('label'=>'Demo 10','link'=>'https://wplms.io/demos/demo10/'),
               'default' => array('label'=>'Default','link'=>'https://wplms.io/demos/default/'),
               'points_system' => array('label'=>'Points System','link'=>'https://wplms.io/demos/points_system/'),
               'oneinstructor' => array('label'=>'One Instructor','link'=>'https://wplms.io/demos/oneinstructor/'),
               'onecourse' => array('label'=>'One Course','link'=>'https://wplms.io/demos/onecourse/'),
               'childone' => array('label'=>'Child One','link'=>'https://wplms.io/demos/childone/'),
           );

			//If we have parent slug - set correct url
			if ( $this->parent_slug !== '' ) {
				$this->page_url = 'admin.php?page=' . $this->page_slug;
			} else {
				$this->page_url = 'themes.php?page=' . $this->page_slug;
			}
			$this->page_url = apply_filters( $this->theme_name . '_theme_setup_wizard_page_url', $this->page_url );

			//set relative plugin path url
			$this->plugin_path = trailingslashit( $this->cleanFilePath( dirname( __FILE__ ) ) );
			$relative_url      = str_replace( $this->cleanFilePath( get_template_directory() ), '', $this->plugin_path );
			$this->plugin_url  = trailingslashit( get_template_directory_uri() . $relative_url );
		}

		/**
		 * Setup the hooks, actions and filters.
		 *
		 * @uses add_action() To add actions.
		 * @uses add_filter() To add filters.
		 *
		 * @since 1.1.1
		 * @access public
		 */
		public function init_actions() {

			if ( apply_filters( $this->theme_name . '_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {
				add_action( 'after_switch_theme', array( $this, 'switch_theme' ) );

				if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
					add_action( 'init', array( $this, 'get_tgmpa_instanse' ), 30 );
					add_action( 'init', array( $this, 'set_tgmpa_url' ), 40 );
				}

				add_action( 'admin_menu', array( $this, 'admin_menus' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'admin_init', array( $this, 'admin_redirects' ), 30 );
				add_action( 'admin_init', array( $this, 'init_wizard_steps' ), 30 );
				add_action( 'admin_init', array( $this, 'setup_wizard' ), 30 );
				add_filter( 'tgmpa_load', array( $this, 'tgmpa_load' ), 10, 1 );
				add_action( 'wp_ajax_envato_setup_plugins', array( $this, 'ajax_plugins' ) );
				add_action( 'wp_ajax_envato_setup_content', array( $this, 'ajax_content' ) );
				add_filter('wplms_required_plugins',array($this,'setup_wizard_plugins'));

				add_filter('wplms_import_post_type_content',array($this,'check_post_type'),10,2);
				add_filter('wplms_import_post_type_content_disable',array($this,'check_post_type'),10,2);

				add_action('wp_ajax_clear_imported_posts',array($this,'clear_imported_posts'));
			}
			if ( function_exists( 'envato_market' ) ) {
				add_action( 'admin_init', array( $this, 'envato_market_admin_init' ), 20 );
				add_filter( 'http_request_args', array( $this, 'envato_market_http_request_args' ), 10, 2 );
			}
			add_action('widgets_init',array($this,'wplms_register_sidebars'));
			add_action( 'upgrader_post_install', array( $this, 'upgrader_post_install' ), 10, 2 );
		}


		function clear_imported_posts(){
			
			if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_clear_imported_posts') || !current_user_can('manage_options')){
	         	_e('Security check Failed. Contact Administrator.','vibe');
	         	die();
	      	}
	      	delete_transient( 'importpostids');
	      	delete_transient( 'importtermids');
	      	die();
		}

		function wplms_register_sidebars(){

			$style = vibe_get_site_style();//get_option('wplms_site_style');
			if(empty($style)){$style = $this->get_default_theme_style();}
			switch($style){
				case 'demo1':
				case 'demo5':
					register_sidebar( array(
			          	'name' => 'MegaMenu',
			          	'id' => 'MegaMenu',
			          	'before_widget' => '<div class="widget"><div class="inside">',
			              'after_widget' => '</div></div>',
			              'before_title' => '<h4 class="widgettitle"><span>',
			              'after_title' => '</span></h4>',
			            'description'   => __('This is the MegaMenu sidebar','vibe')
			        ));
			      	register_sidebar( array(
			    	  	'name' => 'MegaMenu2',
			          	'id' => 'MegaMenu2',
			          	'before_widget' => '<div class="widget"><div class="inside">',
			            'after_widget' => '</div></div>',
			            'before_title' => '<h4 class="widgettitle"><span>',
			            'after_title' => '</span></h4>',
			          	'description'   => __('This is the MegaMenu2 sidebar','vibe')
			      	));
				break;
				case 'demo2':
					register_sidebar( array(
			          	'name' => 'MegaMenu',
			          	'id' => 'MegaMenu',
			          	'before_widget' => '<div class="widget"><div class="inside">',
			              'after_widget' => '</div></div>',
			              'before_title' => '<h4 class="widgettitle"><span>',
			              'after_title' => '</span></h4>',
			            'description'   => __('This is the MegaMenu sidebar','vibe')
			        ));
				break;
				case 'demo3':
				case 'demo6':
					register_sidebar( array(
			          	'name' => 'MegaMenu',
			          	'id' => 'MegaMenu',
			          	'before_widget' => '<div class="widget"><div class="inside">',
			              'after_widget' => '</div></div>',
			              'before_title' => '<h4 class="widgettitle"><span>',
			              'after_title' => '</span></h4>',
			            'description'   => __('This is the MegaMenu sidebar','vibe')
			        ));
				break;
				case 'demo4':
				break;
			}
		}
		function check_post_type($check,$post_type){
			
			if(empty($this->check_wplms_plugins)){
				$this->check_wplms_plugins = get_option('wplms_plugins');	

			}
			
			if(!in_array('bbpress/bbpress.php',$this->check_wplms_plugins)){
				if(in_array($post_type,array('forum','topic','reply'))){
					$check = 0;
				}
			}

			if(!in_array('eventON/eventon.php',$this->check_wplms_plugins)){
				if(in_array($post_type,array('ajde_events'))){
					$check = 0;
				}
			}

			if(!in_array('woocommerce/woocommerce.php',$this->check_wplms_plugins)){
				if(in_array($post_type,array('product'))){
					$check = 0;
				}
			}

			if(!in_array('buddydrive/buddydrive.php',$this->check_wplms_plugins)){
				if(in_array($post_type,array('buddydrive-file'))){
					$check = 0;
				}
			}

			if(!in_array('badgeos/badgeos.php',$this->check_wplms_plugins)){
				if(in_array($post_type,array('achievement-type','badgeos-log-entry'))){
					$check = 0;
				}
			}

			return $check;	
		}
		function setup_wizard_plugins($plugins){

			// SETUP WIZARD PLUGINS
			$wplms_plugins = get_option( 'wplms_plugins');
			if(isset($wplms_plugins) && is_array($wplms_plugins)){
				$plugins[] = array(
	            'name'                  => 'MyCred',
	            'slug'                  => 'mycred', 
	            'file'					=> 'mycred/mycred.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'WPLMS MyCred Addon',
	            'slug'                  => 'wplms-mycred-addon', 
	            'file'					=> 'wplms-mycred-addon/wplms-mycred-addon.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'CoAuthors plus',
	            'slug'                  => 'co-authors-plus', 
	            'file'					=> 'co-authors-plus/co-authors-plus.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'WPLMS CoAuthors plus',
	            'slug'                  => 'wplms-coauthors-plus', 
	            'file'					=> 'WPLMS-Coauthors-Plus/wplms-coauthor-plus.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'BadgeOS',
	            'slug'                  => 'badgeos', 
	            'file'					=> 'badgeos/badgeos.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'WPLMS BadgeOS',
	            'slug'                  => 'wplms-badgeos', 
	            'file'					=> 'WPLMS-BadgeOS/badgeos-wplms.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'BadgeOS Community Addon',
	            'slug'                  => 'badgeos-community-add-on', 
	            'file'					=> 'badgeos-community-add-on/badgeos-community.php'
	        	);
	        	
	        	$plugins[] = array(
        		'name'                  => 'PMPRO', // The plugin name
            	'slug'                  => 'paid-memberships-pro',
            	'file'					=> 'paid-memberships-pro/paid-memberships-pro.php',
        		);

	        	$plugins[] = array(
	            'name'                  => 'BigBlueButton',
	            'slug'                  => 'bigbluebutton', 
	            'file'					=> 'bigbluebutton/bigbluebutton-plugin.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'WPLMS BBB',
	            'slug'                  => 'bbb-wplms', 
	            'file'					=> 'wplms-bbb/wplms-bbb.php',
	        	);

	        	$plugins[] = array(
	            'name'                  => 'H5P',
	            'slug'                  => 'h5p', 
	            'file'					=> 'h5p/h5p.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'WPLMS H5P',
	            'slug'                  => 'wplms-h5p-plugin', 
	            'file'					=> 'wplms-h5p/wplms-h5p.php',
	        	);
				foreach($plugins as $k=>$plugin){
					if(empty($plugin['required']) && isset($plugin['file']) && !in_array($plugin['file'],$wplms_plugins)){
						unset($plugins[$k]);
					}
				}
			}

			return $plugins;
		}
		/**
		 * After a theme update we clear the setup_complete option. This prompts the user to visit the update page again.
		 *
		 * @since 1.1.8
		 * @access public
		 */
		public function upgrader_post_install( $return, $theme ) {
			if ( is_wp_error( $return ) ) {
				return $return;
			}
			if ( $theme != get_stylesheet() ) {
				return $return;
			}
			update_option( 'envato_setup_complete', false );

			return $return;
		}

		/**
		 * We determine if the user already has theme content installed. This can happen if swapping from a previous theme or updated the current theme. We change the UI a bit when updating / swapping to a new theme.
		 *
		 * @since 1.1.8
		 * @access public
		 */
		public function is_possible_upgrade() {
			return false;
		}

		public function enqueue_scripts() {
		}

		public function tgmpa_load( $status ) {
			return is_admin() || current_user_can( 'install_themes' );
		}

		public function switch_theme() {
			set_transient( '_' . $this->theme_name . '_activation_redirect', 1 );
		}

		public function admin_redirects() {
			ob_start();
			if ( ! get_transient( '_' . $this->theme_name . '_activation_redirect' ) || get_option( 'envato_setup_complete', false ) ) {
				return;
			}
			delete_transient( '_' . $this->theme_name . '_activation_redirect' );
			wp_safe_redirect( admin_url( $this->page_url ) );
			exit;
		}

		/**
		 * Get configured TGMPA instance
		 *
		 * @access public
		 * @since 1.1.2
		 */
		public function get_tgmpa_instanse() {
			$this->tgmpa_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
		}

		/**
		 * Update $tgmpa_menu_slug and $tgmpa_parent_slug from TGMPA instance
		 *
		 * @access public
		 * @since 1.1.2
		 */
		public function set_tgmpa_url() {

			$this->tgmpa_menu_slug = ( property_exists( $this->tgmpa_instance, 'menu' ) ) ? $this->tgmpa_instance->menu : $this->tgmpa_menu_slug;
			$this->tgmpa_menu_slug = apply_filters( $this->theme_name . '_theme_setup_wizard_tgmpa_menu_slug', $this->tgmpa_menu_slug );

			$tgmpa_parent_slug = ( property_exists( $this->tgmpa_instance, 'parent_slug' ) && $this->tgmpa_instance->parent_slug !== 'themes.php' ) ? 'admin.php' : 'themes.php';

			$this->tgmpa_url = apply_filters( $this->theme_name . '_theme_setup_wizard_tgmpa_url', $tgmpa_parent_slug . '?page=' . $this->tgmpa_menu_slug );

		}

		/**
		 * Add admin menus/screens.
		 */
		public function admin_menus() {

			if ( $this->is_submenu_page() ) {
				//prevent Theme Check warning about "themes should use add_theme_page for adding admin pages"
				$add_subpage_function = 'add_submenu' . '_page';
				$add_subpage_function( $this->parent_slug, esc_html__( 'Setup Wizard' ), esc_html__( 'Setup Wizard' ), 'manage_options', $this->page_slug, array(
					$this,
					'setup_wizard',
				) );
			} else {
				add_theme_page( esc_html__( 'Setup Wizard' ), esc_html__( 'Setup Wizard' ), 'manage_options', $this->page_slug, array(
					$this,
					'setup_wizard',
				) );
			}

			add_theme_page( esc_html__( 'Export Wizard' ), esc_html__( 'Export Wizard' ), 'manage_options', $this->page_slug.'&export', array(
					$this,
					'export_wizard',
				) );

		}

		/**
		 * Setup steps.
		 *
		 * @since 1.1.1
		 * @access public
		 * @return array
		 */
		public function init_wizard_steps() {

			$this->steps = array(
				'introduction' => array(
					'name'    => esc_html__( 'Introduction' ),
					'view'    => array( $this, 'envato_setup_introduction' ),
					'handler' => array( $this, 'envato_setup_introduction_save' ),
				),
			);
			
			$this->steps['start']         = array(
				'name'    => esc_html__( 'Start' ),
				'view'    => array( $this, 'envato_start_setup' ),
				'handler' => array( $this, 'envato_start_setup_save' ),
			);

			if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
				$this->steps['default_plugins'] = array(
					'name'    => esc_html__( 'Plugins' ),
					'view'    => array( $this, 'envato_setup_default_plugins' ),
					'handler' => '',
				);
			}

			$this->steps['pagesetup']         = array(
				'name'    => esc_html__( 'Pages' ),
				'view'    => array( $this, 'envato_page_setup' ),
				'handler' => array( $this, 'envato_page_setup_save' ),
			);
			
			if( count($this->site_styles) > 1 ) {
				$this->steps['style'] = array(
					'name'    => esc_html__( 'Style' ),
					'view'    => array( $this, 'envato_setup_demo_style' ),
					'handler' => array( $this, 'envato_setup_demo_style_save' ),
				);
			}
			$this->steps['default_content'] = array(
				'name'    => esc_html__( 'Content' ),
				'view'    => array( $this, 'envato_setup_default_content' ),
				'handler' => '',
			);
			$this->steps['design']          = array(
				'name'    => esc_html__( 'Design' ),
				'view'    => array( $this, 'envato_setup_design' ),
				'handler' => array( $this, 'envato_setup_design_save' ),
			);
			$this->steps['next_steps']      = array(
				'name'    => esc_html__( 'Ready!' ),
				'view'    => array( $this, 'envato_setup_ready' ),
				'handler' => '',
			);

			$this->steps = apply_filters( $this->theme_name . '_theme_setup_wizard_steps', $this->steps );

		}

		function envato_start_setup(){
			
			?><h1><?php esc_html_e( 'Select features for this site' ); ?></h1>
            <form method="post">
                <p><?php echo esc_html_e( 'Select the features you need for your site. The features here are pre-configured, so the next steps would be based on the selection you make here. Ofcourse these features can be added/removed later on from theme settings as well.' ); ?></p>
                <hr>
                <div id="purpose_description"></div>
                <div class="theme-features">
                    <ul>
	                    <?php

	                    foreach ( $this->features as $feature => $data ) {
	                    	$class='';$flag=0;
	                    	
	                    	if(isset($data['verify'])){
	                    		$flag = 1;
	                    		foreach($data['verify'] as $plugin){

	                    			if(!is_plugin_active($plugin)){
	                    				$flag=0;break;
	                    			}
	                    		}
	                    	}
	                    	if(isset($data['default'])){$flag = 1;}
	                    	if(empty($flag)){$class='';}else{$class='selected';}

		                    ?>
                            <li class="<?php echo $class; ?>">
                                <a href="#" data-style="<?php echo esc_attr( $feature ); ?>" ><img
                                            src="<?php echo esc_url(get_template_directory_uri() .'/setup/installer/'.$data['icon']);?>">
                                    <h4><?php echo $data['label']; ?></h4>
                                    <p><?php echo $data['description']; ?></p>
                                    <?php
                                    if(isset($data['verify'])){
                                    	foreach($data['verify'] as $plugin){
                                    		echo '<input type="hidden" '.(empty($flag)?'':'name="plugins[]"').' value="'.$plugin.'" />';
                                    	}
                                    }
                                    ?>
                                </a>
                            </li>
	                    <?php } ?>
                    </ul>
                </div>

                <hr><p><em>Have a suggestion for us. Share it with us <a href="http://vibethemes.com/documentation/wplms/forums/forum/general/feature-request/" target="_blank">here</a>  !</em></p>

                <p class="envato-setup-actions step">
                    <input type="submit" class="button-primary button button-large button-next"
                           value="<?php _e( 'Continue', 'vibe' ); ?>" name="save_step"/>
                    <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
                       class="button button-large button-next"><?php esc_html_e( 'Skip this step' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
                </p>
            </form>
            <?php
		}

		function envato_start_setup_save(){
			check_admin_referer( 'envato-setup' );
			if ( ! empty( $_REQUEST['save_step'] )){
			
				$deactivate_plugins = array();
				if(isset($_POST['plugins'])){
					foreach($this->features as $key=>$feature){
						if($key !='course' && isset($feature['verify'])){
							foreach($feature['verify'] as $plugin){
								if(is_plugin_active($plugin) && !in_array($plugin,$_POST['plugins'])){
									deactivate_plugins($plugin);
								}
							}					
						}
					}	
				}
				update_option( 'wplms_plugins', $_POST['plugins'] );
			}
			wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
			exit;
		}
		/**
		 * Show the setup wizard
		 */
		public function setup_wizard() {
			if ( empty( $_GET['page'] ) || $this->page_slug !== $_GET['page'] ) {
				return;
			}
			ob_end_clean();

			$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

			wp_register_script( 'jquery-blockui', $this->plugin_url . 'js/jquery.blockUI.js', array( 'jquery' ), '2.70', true );
			wp_enqueue_script( 'envato-color', $this->plugin_url . 'js/jscolor.js',array(), $this->version );
			wp_register_script( 'envato-setup', $this->plugin_url . 'js/envato-setup.js', array(
				'jquery',
				'jquery-blockui',
			), $this->version );
			wp_localize_script( 'envato-setup', 'envato_setup_params', array(
				'tgm_plugin_nonce' => array(
					'update'  => wp_create_nonce( 'tgmpa-update' ),
					'install' => wp_create_nonce( 'tgmpa-install' ),
				),
				'tgm_bulk_url'     => admin_url( $this->tgmpa_url ),
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'wpnonce'          => wp_create_nonce( 'envato_setup_nonce' ),
				'verify_text'      => esc_html__( '...verifying' ),
			) );

			//wp_enqueue_style( 'envato_wizard_admin_styles', $this->plugin_url . '/css/admin.css', array(), $this->version );
			wp_enqueue_style( 'envato-setup', $this->plugin_url . 'css/envato-setup.css', array(
				'wp-admin',
				'dashicons',
				'install',
			), $this->version );

			//enqueue style for admin notices
			wp_enqueue_style( 'wp-admin');

			wp_enqueue_media();
			wp_enqueue_script( 'media');
			ob_start();
			$this->setup_wizard_header();
			$this->setup_wizard_steps();
			$show_content = true;
			echo '<div class="envato-setup-content">';
			if ( ! empty( $_REQUEST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
				$show_content = call_user_func( $this->steps[ $this->step ]['handler'] );
			}
			if ( $show_content ) {
				$this->setup_wizard_content();
			}
			echo '</div>';
			$this->setup_wizard_footer();
			exit;
		}

		public function get_step_link( $step ) {
			return add_query_arg( 'step', $step, admin_url( 'admin.php?page=' . $this->page_slug ) );
		}

		public function get_next_step_link() {
			$keys = array_keys( $this->steps );

			return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ], remove_query_arg( 'translation_updated' ) );
		}

		public function envato_page_setup(){
			?><h1><?php esc_html_e( 'Set required pages' ); ?></h1>
			<p><?php echo esc_html_e( 'Automatically configure and set required pages for WPLMS. There are important pages required for LMS to work properly. We recommend everyone using the LMS to setup these pages.' ); ?></p>
                <hr>
                <table class="wplms-setup-pages" cellspacing="0">
					<thead>
						<tr>
							<th class="page-name">Page Name</th>
							<th class="page-description">Description</th>
						</tr>
					</thead>
					<tbody>
						<?php if(function_exists('vibe_get_option')){$page_id = vibe_get_option('take_course_page');}?>
						<tr <?php echo (empty($page_id)?'':'class="done"');?>>
							<td class="page-name">Pursue Course</td>
							<td>The course pursue page is a protected page where all the course activity and learning occurs.</td>
						</tr>
						<?php if(function_exists('vibe_get_option')){$page_id = vibe_get_option('create_course');}  ?>
						<tr <?php echo (empty($page_id)?'':'class="done"');?>>
							<td class="page-name">Create Course</td>
							<td>The create course page is the front end course creation platform for Instructors.</td>
						</tr>
						<?php if(function_exists('get_option')){$page_ids = get_option('bp-pages');} ?>
						<tr <?php echo (empty($page_ids['course'])?'':'class="done"');?>>
							<td class="page-name">Directory Pages</td>
							<td>
								The Directory pages for Members, Courses, Activity will be created to browse various items in site. 					</td>
						</tr>
						<?php if(function_exists('get_option') && empty($page_ids)){$page_ids = get_option('bp-pages');}?>
						<tr <?php echo (empty($page_ids['register'])?'':'class="done"');?>>
							<td class="page-name">Registration</td>
							<td>
								Set a default registration form for users to register on your site. You can disable it from settings. 						</td>
						</tr>
					</tbody>
				</table>
				<br><p><em>You can deactivate registration and directories features from settings provided in the theme. In case you have a suggestion for us. Share it with us <a href="" target="_blank">here</a>  !</em></p>
				<form method="post">
                <p class="envato-setup-actions step">
                    <input type="submit" class="button-primary button button-large button-next"
                           value="<?php _e( 'Continue', 'vibe' ); ?>" name="save_step"/>
                    <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
                       class="button button-large button-next"><?php esc_html_e( 'Skip this step' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
                </p>
                </form>
			<?php                

		}

		public function envato_page_setup_save($go=null){
			if(empty($go)){
				check_admin_referer( 'envato-setup' );
			}

			if ( ! empty( $_REQUEST['save_step'] ) || !empty($go)){
				
				$user_id = get_current_user_id();
				$pages = array(
					array(
			            'post_title'     => 'Course Status',
			            'post_type'      => 'page',
			            'post_name'      => 'course-status',
			            'comment_status' => 'closed',
			            'ping_status'    => 'closed',
			            'post_content'   => '<strong>Course Instructions</strong>\n\nPlease enter Course instructions in the Course Status page. To edit this content, simply follow below steps :\n<ol>\n\t<li>Login to WP Admin panel</li>\n\t<li>Locate and click Pages section</li>\n\t<li>Search for page Course Status</li>\n\t<li>Edit the page and change this content</li>\n\t<li>Update the page.</li>\n</ol>',
			            'post_status'    => 'publish',
			            'post_author'    => $user_id,
			            'menu_order'     => 0,
			            'page_template'  => 'start.php'
			        ),
			        array(
			            'post_title'     => 'Edit Course',
			            'post_type'      => 'page',
			            'post_name'      => 'edit-course',
			            'comment_status' => 'closed',
			            'ping_status'    => 'closed',
			            'post_content'   => '',
			            'post_status'    => 'publish',
			            'post_author'    => $user_id,
			            'menu_order'     => 0,
			            'page_template'  => 'create_content.php'
			        ),
			        array(
			            'post_title'     => 'All Courses',
			            'post_type'      => 'page',
			            'post_name'      => 'all-courses',
			            'comment_status' => 'closed',
			            'ping_status'    => 'closed',
			            'post_content'   => '',
			            'post_status'    => 'publish',
			            'post_author'    => $user_id,
			            'menu_order'     => 0,
			        ),
			        array(
			            'post_title'     => 'Certificate',
			            'post_type'      => 'page',
			            'post_name'      => 'default-certificate',
			            'comment_status' => 'closed',
			            'ping_status'    => 'closed',
			            'post_content'   => '',
			            'post_status'    => 'publish',
			            'post_author'    => $user_id,
			            'menu_order'     => 0,
			        ),
			        array(
			            'post_title'     => 'Register',
			            'post_type'      => 'page',
			            'post_name'      => 'register',
			            'comment_status' => 'closed',
			            'ping_status'    => 'closed',
			            'post_content'   => '',
			            'post_status'    => 'publish',
			            'post_author'    => $user_id,
			            'menu_order'     => 0,
			        ),
			        array(
			            'post_title'     => 'Activate',
			            'post_type'      => 'page',
			            'post_name'      => 'activate',
			            'comment_status' => 'closed',
			            'ping_status'    => 'closed',
			            'post_content'   => '',
			            'post_status'    => 'publish',
			            'post_author'    => $user_id,
			            'menu_order'     => 0,
			        ), 
			        array(
			            'post_title'     => 'Notes and Discussion',
			            'post_type'      => 'page',
			            'post_name'      => 'notes-discussion',
			            'comment_status' => 'closed',
			            'ping_status'    => 'closed',
			            'post_content'   => '',
			            'post_status'    => 'publish',
			            'post_author'    => $user_id,
			            'menu_order'     => 0,
			        ), 
				);
				foreach($pages as $key => $page){
					if($page['post_name'] == 'course-status'){
						$take_course_page = vibe_get_option('take_course_page');
						if(empty($take_course_page)){
							$course_status = get_page_by_title( 'Course Status' );
							if(empty($course_status)){
								$page_id = wp_insert_post($page);		
							}else{
								$page_id = $course_status->ID;
							}	
							vibe_update_option('take_course_page',$page_id);
						}
					}	

					if($page['post_name'] == 'edit-course'){
						$create_course = vibe_get_option('create_course');
						if(empty($create_course)){
							$edit_course = get_page_by_title( 'Edit Course' );
							if(empty($edit_course)){
								$page_id = wp_insert_post($page);		
							}else{
								$page_id = $edit_course->ID;
							}
							vibe_update_option('create_course',$page_id);
						}
					}
					if($page['post_name'] == 'all-courses'){
						$bp_pages = get_option('bp-pages');
						if(empty($bp_pages['course'])){
							$page_id = wp_insert_post($page);	
							$bp_pages['course'] = $page_id;
							update_option('bp-pages',$bp_pages);
						}
					}

					if($page['post_name'] == 'certificate'){
						$page_id = wp_insert_post($page);	
						vibe_update_option('certificate_page',$page_id);
						update_post_meta($page_id,'_wp_page_template','certificate.php');
					}

					if($page['post_name'] == 'notes-discussion'){
						$page_id = wp_insert_post($page);	
						vibe_update_option('unit_comments',$page_id);
						update_post_meta($page_id,'_wp_page_template','notes_discussion.php');
					}

					if($page['post_name'] == 'register'){
						if(empty($bp_pages)){$bp_pages = get_option('bp-pages');}
						if(empty($bp_pages['register'])){
							$page_id = wp_insert_post($page);	
							$bp_pages['register'] = $page_id;
							update_option('bp-pages',$bp_pages);
							update_option('users_can_register',1);
						}
					}
					if($page['post_name'] == 'activate'){
						if(empty($bp_pages)){$bp_pages = get_option('bp-pages');}
						if(empty($bp_pages['activate'])){
							$page_id = wp_insert_post($page);	
							$bp_pages['activate'] = $page_id;
							update_option('bp-pages',$bp_pages);
						}
					}
				}
			}
			if(empty($go)){
				wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
				exit;
			}
		}
		/**
		 * Setup Wizard Header
		 */
	public function setup_wizard_header() {
		?>
		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<?php
			// avoid theme check issues.
			echo '<title>' . esc_html__( 'Theme &rsaquo; Setup Wizard' ) . '</title>'; ?>
			<?php wp_print_scripts( 'envato-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="envato-setup wp-core-ui">
		<h1 id="wc-logo">
			<a href="http://themeforest.net/user/vibethemes/portfolio" target="_blank"><?php
				$image_url = $this->get_logo_image();
				if ( $image_url ) {
					$image = '<img class="site-logo" src="%s" alt="%s" style="width:%s; height:auto" />';
					printf(
						$image,
						$image_url,
						get_bloginfo( 'name' ),
						$this->get_header_logo_width()
					);
				} else { ?>
						<img src="<?php echo esc_url( $this->plugin_url . 'images/logo.png' ); ?>" alt="Envato install wizard" /><?php
				} ?></a>
		</h1>
		<?php
		}

		/**
		 * Setup Wizard Footer
		 */
		public function setup_wizard_footer() {
		?>
		<?php if ( 'next_steps' === $this->step ) : ?>
			<a class="wc-return-to-dashboard"
			   href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to the WordPress Dashboard' ); ?></a>
		<?php endif; ?>
		</body>
		<?php
		@do_action( 'admin_footer' ); // this was spitting out some errors in some admin templates. quick @ fix until I have time to find out what's causing errors.
		do_action( 'admin_print_footer_scripts' );
		?>
		</html>
		<?php
	}

		/**
		 * Output the steps
		 */
		public function setup_wizard_steps() {
			$ouput_steps = $this->steps;
			array_shift( $ouput_steps );
			?>
			<ol class="envato-setup-steps">
				<?php foreach ( $ouput_steps as $step_key => $step ) : ?>
					<li class="<?php
					$show_link = false;
					if ( $step_key === $this->step ) {
						echo 'active';
					} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
						echo 'done';
						$show_link = true;
					}
					?>"><?php
						if ( $show_link ) {
							?>
							<a href="<?php echo esc_url( $this->get_step_link( $step_key ) ); ?>"><?php echo esc_html( $step['name'] ); ?></a>
							<?php
						} else {
							echo esc_html( $step['name'] );
						}
						?></li>
				<?php endforeach; ?>
			</ol>
			<?php
		}

		/**
		 * Output the content for the current step
		 */
		public function setup_wizard_content() {
			isset( $this->steps[ $this->step ] ) ? call_user_func( $this->steps[ $this->step ]['view'] ) : false;
		}

		/**
		 * Introduction step
		 */
		public function envato_setup_introduction() {


			$setup_options = '<div class="setup_wizard_options_overlay">
					<div class="setup_wizard_options">
						<div class="container">
							<div class="row">
								<div class="col-md-4 col-md-offset-4 col-sm-6">
									<a class="button-primary button" id="install_setup_wizard">Install using Setup Wizard</a>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4 col-md-offset-4 col-sm-6">
									<a class="button" id="install_one_click_installer">';
								
									
								$setup_options .='Install using One Click installer';
								if(function_exists('ini_get')){
										$maxtime = ini_get('max_execution_time');
										if($maxtime < 200){
											$setup_options .= '<span style="color:red;display:block;font-size:80%;">( * Increase Max execution time to 200 )</span> ';
										}
									}
								$setup_options.='</a></div>
							</div>
						</div>
					</div>
				</div>
				<style>
				.setup_wizard_options_overlay{
				    position: fixed;top: 0;z-index:9;
				    background: rgba(0,0,0,0.6);left: 0;
				    width: 100%;height: 100%;
				    display: flex;justify-content: center;align-content: center;
				}.setup_wizard_options{align-self: center;}
				.setup_wizard_options .row+.row{margin-top:30px;}
				.setup_wizard_options .button{
				    width: 100%;text-align: center;height: auto;padding: 30px;font-size: 16px;}
				</style><script>
				jQuery(document).ready(function($){
					$("#install_setup_wizard").on("click",function(){
						$(".setup_wizard_options_overlay").hide(200).remove();
					});
					$("#install_one_click_installer").on("click",function(){
						window.location.href = "'.admin_url('index.php?page=wplms-install').'";
					});
				});
				</script>';

			if ( isset( $_REQUEST['debug'] ) ) {
				echo '<pre>';
				// debug inserting a particular post so we can see what's going on
				$post_type = 'nav_menu_item';
				$post_id   = 239; // debug this particular import post id.
				$all_data  = $this->_get_json( 'default.json' );
				if ( ! $post_type || ! isset( $all_data[ $post_type ] ) ) {
					echo "Post type $post_type not found.";
				} else {
					echo "Looking for post id $post_id \n";
					foreach ( $all_data[ $post_type ] as $post_data ) {

						if ( $post_data['post_id'] == $post_id ) {
							$this->_process_post_data( $post_type, $post_data, 0, true );
						}
					}
				}
				$this->_handle_delayed_posts();
				
				echo '</pre>';
			} else if ( isset( $_REQUEST['export'] ) ) {

				@include('envato-setup-export.php');

			} else if ( $this->is_possible_upgrade() ) {

				echo $setup_options;
				?>
				<h1><?php printf( esc_html__( 'Welcome to the setup wizard for %s.' ), 'WPLMS' ); ?></h1>
				<p><?php esc_html_e( 'It looks like you may have recently upgraded to this theme. Great! This setup wizard will help ensure all the default settings are correct. It will also show some information about your new website and support options.' ); ?></p>
				<p class="envato-setup-actions step">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button-primary button button-large button-next"><?php esc_html_e( 'Let\'s Go!' ); ?></a>
					<a href="<?php echo esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '' ) ); ?>"
					   class="button button-large"><?php esc_html_e( 'Not right now' ); ?></a>
				</p>
				<?php
			} else if ( get_option( 'envato_setup_complete', false )) {
				echo $setup_options;
				?>
				<h1><?php printf( esc_html__( 'Welcome to the setup wizard for %s Theme.' ), 'WPLMS'); ?></h1>
				<p><?php esc_html_e( 'It looks like you have already run the setup wizard. Below are some options: ' ); ?></p>
				<ul>
					<li>
						<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
						   class="button-primary button button-next button-large"><?php esc_html_e( 'Run Setup Wizard Again' ); ?></a>
					</li>
					<li>
						<form method="post">
							<input type="hidden" name="reset-font-defaults" value="yes">
							<!--input type="submit" class="button-primary button button-large button-next"
							       value="<?php //_e( 'Reset font style and colors', 'vibe' ); ?>" name="save_step"/ -->
							<?php wp_nonce_field( 'envato-setup' ); ?>
						</form>
					</li>
				</ul>
				<p class="envato-setup-actions step">
					<a href="<?php echo esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '' ) ); ?>"
					   class="button button-large"><?php esc_html_e( 'Cancel' ); ?></a>
				</p>
				<?php
			} else {

				echo $setup_options;
				?>

				<h1 style="font-size:3rem;text-align:center;margin:20px 0;"><?php echo esc_html__('WPLMS Setup Wizard'); ?></h1>
				<p><?php 
					printf( '<h4>'.esc_html__( 'Welcome to the setup wizard for WPLMS Theme. You\'re using %s theme.').'</h4><hr>',wp_get_theme());
					printf( esc_html__( 'Thank you for choosing the %s theme from ThemeForest. This quick setup wizard will help you configure your new website. This wizard will install the required WordPress plugins, default content, logo and tell you a little about Help &amp; Support options. It should only take 5 minutes.' ), 'WPLMS' ); ?></p>
				<p><?php esc_html_e( 'No time right now? If you don\'t want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!' ); ?></p>
				
				<h2><?php echo esc_html__( 'Configuration Check'); ?></h2>
				<ul class="config">
				<?php
				$memory = $this->wplms_let_to_num( WP_MEMORY_LIMIT );
				$class='no';
				 if ( $memory >= 134217728 ) {$class='yes'; }
				?>
				<li class="<?php echo $class; ?>"><label><?php echo esc_html__( 'PHP Memory allocation'); ?></label>
				<?php
				if ( $memory < 134217728 ) {
					echo '<mark class="error">' . sprintf( __( '%s - We recommend setting memory to at least 128MB. See: <a href="%s">Increasing memory allocated to PHP</a>', 'vibe' ), size_format( $memory ), 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP' ) . '</mark>';
				} else {
					echo '<mark class="yes">' . size_format( $memory ) . '</mark>';
				}
				?>
				</li>
				<?php 
				$class='no';
				$x = wp_max_upload_size();
				 if ( $memory >= 33554432 ) {$class='yes'; }
				 ?>
				<li class="<?php echo $class; ?>"><label><?php _e( 'WP Max Upload Size', 'vibe' ); ?></label>
				<?php echo size_format( $x ); ?></li>
				<?php if ( function_exists( 'ini_get' ) ) : ?>
						<?php $class='no'; $x = $this->wplms_let_to_num( ini_get('post_max_size') ) ; if($x >= 33554432){$class = 'yes';} ?>
						<li class="<?php echo $class; ?>"><label><?php _e('PHP Post Max Size', 'vibe' ); ?></label>
						<?php echo size_format($x); ?></li>
						<?php
						$class='no'; $x = ini_get('max_execution_time') ; if($x >= 200){$class = 'yes';}
						?>					
						<li class="<?php echo $class; ?>"><label><?php _e('PHP Time Limit', 'vibe' ); ?></label>
						<?php echo $x.' s '; if($x < 200){printf( '<mark> - We recommend increasing this value to 200. See <a href="%s">Increasing PHP Time limit</a></mark>','https://premium.wpmudev.org/blog/increase-memory-limit/');} ?></li>
						<?php $class='yes';?>
						<li class="<?php echo $class; ?>"><label><?php _e( 'PHP Max Input Vars', 'vibe' ); ?></label>
							<?php echo ini_get('max_input_vars'); ?>
						</li>
				<?php endif; ?>
				<?php
					$wp_content = WP_CONTENT_DIR;
					
					$files_to_check = array(
										'' => '0755',
										'themes/wplms/plugins' => '0755',
										'themes/wplms/assets' => '0755',);
					
					$root = WP_CONTENT_DIR;
					
					echo '</ul><h2>'._x('File Permissions Check','installer label','vibe').'</h2><ul class="config">';
					foreach($files_to_check as $k => $v){
						
						$path = $root.'/'.$k;

						$stat = @stat($path);
						$suggested = $v;
						$actual = substr(sprintf('%o', $stat['mode']), -4);
						echo '<li class="'.((version_compare($actual, $suggested) < 0 ) ? 'no' : 'yes').'"><label>'.$k.'</label>
						'.$actual.''.((version_compare($actual, $suggested) < 0 ) ? '- '._x('[Recommended]','recommended label','vibe').'<mark> '.$suggested.'</mark>' : '').'
						</li>';
					}
					?>
				</ul>
				<p class="envato-setup-actions step">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button-primary button button-large button-next"><?php esc_html_e( 'Let\'s Go!' ); ?></a>
					<a href="<?php echo esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '' ) ); ?>"
					   class="button button-large"><?php esc_html_e( 'Not right now' ); ?></a>
				</p>
				<?php
			}
		}

		function wplms_let_to_num( $size ) {
			$l   = substr( $size, -1 );
			$ret = substr( $size, 0, -1 );
			switch ( strtoupper( $l ) ) {
				case 'P':
					$ret *= 1024;
				case 'T':
					$ret *= 1024;
				case 'G':
					$ret *= 1024;
				case 'M':
					$ret *= 1024;
				case 'K':
					$ret *= 1024;
			}
			return $ret;
		}

		public function filter_options( $options ) {
			return $options;
		}

		/**
		 *
		 * Handles save button from welcome page. This is to perform tasks when the setup wizard has already been run. E.g. reset defaults
		 *
		 * @since 1.2.5
		 */
		public function envato_setup_introduction_save() {

			check_admin_referer( 'envato-setup' );

			if ( ! empty( $_POST['reset-font-defaults'] ) && $_POST['reset-font-defaults'] == 'yes' ) {


				$file_name = get_template_directory() . '/style.custom.css';
				if ( file_exists( $file_name ) ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					WP_Filesystem();
					global $wp_filesystem;
					$wp_filesystem->put_contents( $file_name, '' );
				}
				?>
				<p>
					<strong><?php esc_html_e( 'Options have been reset. Please go to Appearance > Customize in the WordPress backend.' ); ?></strong>
				</p>
				<?php
				return true;
			}

			return false;
		}

		private function _get_plugins() {
			$instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
			$plugins  = array(
				'all'      => array(), // Meaning: all plugins which still have open actions.
				'install'  => array(),
				'update'   => array(),
				'activate' => array(),
			);

			foreach ( $instance->plugins as $slug => $plugin ) {
				if ( $instance->is_plugin_active( $slug ) && false === $instance->does_plugin_have_update( $slug ) ) {
					// No need to display plugins if they are installed, up-to-date and active.
					continue;
				} else {
					$plugins['all'][ $slug ] = $plugin;

					if ( ! $instance->is_plugin_installed( $slug ) ) {
						$plugins['install'][ $slug ] = $plugin;
					} else {
						if ( false !== $instance->does_plugin_have_update( $slug ) ) {
							$plugins['update'][ $slug ] = $plugin;
						}

						if ( $instance->can_plugin_activate( $slug ) ) {
							$plugins['activate'][ $slug ] = $plugin;
						}
					}
				}
			}

			return $plugins;
		}

		/**
		 * Page setup
		 */
		public function envato_setup_default_plugins() {

			tgmpa_load_bulk_installer();
			// install plugins with TGM.
			if ( ! class_exists( 'TGM_Plugin_Activation' ) || ! isset( $GLOBALS['tgmpa'] ) ) {
				die( 'Failed to find TGM' );
			}
			$url     = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'envato-setup' );
			$plugins = $this->_get_plugins();

			// copied from TGM

			$method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
			$fields = array_keys( $_POST ); // Extra fields to pass to WP_Filesystem.

			if ( false === ( $creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields ) ) ) {
				return true; // Stop the normal page form from displaying, credential request form will be shown.
			}

			// Now we have some credentials, setup WP_Filesystem.
			if ( ! WP_Filesystem( $creds ) ) {
				// Our credentials were no good, ask the user for them again.
				request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );

				return true;
			}

			/* If we arrive here, we have the filesystem */

			?>
			<h1><?php esc_html_e( 'Default Plugins' ); ?></h1>
			<form method="post">

				<?php
				$plugins = $this->_get_plugins();
				if ( count( $plugins['all'] ) ) {
					?>
					<p><?php esc_html_e( 'Your website needs a few essential plugins. The following plugins will be installed or updated:' ); ?></p>
					<ul class="envato-wizard-plugins">
						<?php foreach ( $plugins['all'] as $slug => $plugin ) { ?>
							<li data-slug="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $plugin['name'] ); ?>
								<span>
    								<?php
								    $keys = array();
								    if ( isset( $plugins['install'][ $slug ] ) ) {
									    $keys[] = 'Installation';
								    }
								    if ( isset( $plugins['update'][ $slug ] ) ) {
									    $keys[] = 'Update';
								    }
								    if ( isset( $plugins['activate'][ $slug ] ) ) {
									    $keys[] = 'Activation';
								    }
								    echo implode( ' and ', $keys ) . ' required';
								    ?>
    							</span>
								<div class="spinner"></div>
							</li>
						<?php } ?>
					</ul>
					<?php
				} else {
					echo '<p><strong>' . esc_html_e( 'Good news! All plugins are already installed and up to date. Please continue.' ) . '</strong></p>';
				} ?>

				<p><?php esc_html_e( 'You can add and remove plugins later on from within WordPress.' ); ?></p>

				<p class="envato-setup-actions step">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button-primary button button-large button-next"
					   data-callback="install_plugins"><?php esc_html_e( 'Continue' ); ?></a>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button button-large button-next"><?php esc_html_e( 'Skip this step' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</p>
			</form>
			<?php
		}

		public function ajax_plugins() {
			if ( ! check_ajax_referer( 'envato_setup_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) ) {
				wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No Slug Found' ) ) );
			}
			$json = array();
			// send back some json we use to hit up TGM
			$plugins = $this->_get_plugins();
			// what are we doing with this plugin?
			foreach ( $plugins['activate'] as $slug => $plugin ) {
				if ( $_POST['slug'] == $slug ) {
					$json = array(
						'url'           => admin_url( $this->tgmpa_url ),
						'plugin'        => array( $slug ),
						'tgmpa-page'    => $this->tgmpa_menu_slug,
						'plugin_status' => 'all',
						'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
						'action'        => 'tgmpa-bulk-activate',
						'action2'       => - 1,
						'message'       => esc_html__( 'Activating Plugin' ),
					);
					break;
				}
			}
			foreach ( $plugins['update'] as $slug => $plugin ) {
				if ( $_POST['slug'] == $slug ) {
					$json = array(
						'url'           => admin_url( $this->tgmpa_url ),
						'plugin'        => array( $slug ),
						'tgmpa-page'    => $this->tgmpa_menu_slug,
						'plugin_status' => 'all',
						'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
						'action'        => 'tgmpa-bulk-update',
						'action2'       => - 1,
						'message'       => esc_html__( 'Updating Plugin' ),
					);
					break;
				}
			}
			foreach ( $plugins['install'] as $slug => $plugin ) {
				if ( $_POST['slug'] == $slug ) {
					$json = array(
						'url'           => admin_url( $this->tgmpa_url ),
						'plugin'        => array( $slug ),
						'tgmpa-page'    => $this->tgmpa_menu_slug,
						'plugin_status' => 'all',
						'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
						'action'        => 'tgmpa-bulk-install',
						'action2'       => - 1,
						'message'       => esc_html__( 'Installing Plugin' ),
					);
					break;
				}
			}

			if ( $json ) {
				$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next plugin
				wp_send_json( $json );
			} else {
				wp_send_json( array( 'done' => 1, 'message' => esc_html__( 'Success' ) ) );
			}
			exit;

		}

		private function _content_default_get() {

			$content = array();

			// find out what content is in our default json file.
			$available_content = $this->_get_json( 'default.json' );
			foreach ( $available_content as $post_type => $post_data ) {
				if ( count( $post_data ) ) {
					$first           = current( $post_data );
					$post_type_title = ! empty( $first['type_title'] ) ? $first['type_title'] : ucwords( $post_type ) . 's';
					if ( $post_type_title == 'Navigation Menu Items' ) {
						$post_type_title = 'Navigation';
					}

					$check = apply_filters('wplms_import_post_type_content',1,$post_type);
					
					$content[ $post_type ] = array(
						'title'            => $post_type_title,
						'description'      => sprintf( esc_html__( 'This will create default %s as seen in the demo.' ), $post_type_title ),
						'pending'          => esc_html__( 'Pending.' ),
						'installing'       => esc_html__( 'Installing.' ),
						'success'          => esc_html__( 'Success.' ),
						'install_callback' => array( $this, '_content_install_type' ),
						'checked'          => $this->is_possible_upgrade()?0:$check,
						'disabled'		   => !$check,
						// dont check if already have content installed.
					);
				}
			}

			$content['widgets'] = array(
				'title'            => esc_html__( 'Widgets' ),
				'description'      => esc_html__( 'Insert default sidebar widgets as seen in the demo.' ),
				'pending'          => esc_html__( 'Pending.' ),
				'installing'       => esc_html__( 'Installing Default Widgets.' ),
				'success'          => esc_html__( 'Success.' ),
				'install_callback' => array( $this, '_content_install_widgets' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);
			

			$content['options_panel'] = array(
				'title'            => esc_html__( 'Vibe Options Panel' ),
				'description'      => esc_html__( 'Configure options panel.' ),
				'pending'          => esc_html__( 'Pending.' ),
				'installing'       => esc_html__( 'Installing options panel settings.' ),
				'success'          => esc_html__( 'Success.' ),
				'install_callback' => array( $this, '_content_options_settings' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);
			$content['customizer'] = array(
				'title'            => esc_html__( 'Customizer' ),
				'description'      => esc_html__( 'Configure customizer settings.' ),
				'pending'          => esc_html__( 'Pending.' ),
				'installing'       => esc_html__( 'Installing customiser settings.' ),
				'success'          => esc_html__( 'Success.' ),
				'install_callback' => array( $this, '_content_customizer_settings' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);
			$content['users'] = array(
				'title'            => esc_html__( 'Users' ),
				'description'      => esc_html__( 'Configure sample users & profile fields.' ),
				'pending'          => esc_html__( 'Pending.' ),
				'installing'       => esc_html__( 'Installing user settings.' ),
				'success'          => esc_html__( 'Success.' ),
				'install_callback' => array( $this, '_content_setup_users' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);
			
			$content['slider'] = array(
				'title'            => esc_html__( 'Slider' ),
				'description'      => esc_html__( 'Import sliders used in the demo' ),
				'pending'          => esc_html__( 'Pending.' ),
				'installing'       => esc_html__( 'Installing Slider.' ),
				'success'          => esc_html__( 'Success.' ),
				'install_callback' => array( $this, '_content_install_slider' ),
				'checked'          => 0,
				// dont check if already have content installed.
			);

			$content['settings'] = array(
				'title'            => esc_html__( 'Settings' ),
				'description'      => esc_html__( 'Configure default settings (menus locations, widget connections, set home page, link course units, quiz questions etc).' ),
				'pending'          => esc_html__( 'Pending.' ),
				'installing'       => esc_html__( 'Installing Default Settings.' ),
				'success'          => esc_html__( 'Success.' ),
				'install_callback' => array( $this, '_content_install_settings' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);
			$content = apply_filters( $this->theme_name . '_theme_setup_wizard_content', $content );

			return $content;

		}

		/**
		 * Page setup
		 */
		public function envato_setup_default_content() {
			?>
			<h1><?php esc_html_e( 'Default Content' ); ?></h1>
			<form method="post">
				<?php if ( $this->is_possible_upgrade() ) { ?>
					<p><?php esc_html_e( 'It looks like you already have content installed on this website. If you would like to install the default demo content as well you can select it below. Otherwise just choose the upgrade option to ensure everything is up to date.' ); ?></p>
				<?php } else { ?>
					<p><?php printf( esc_html__( 'It\'s time to insert some default content for your new WordPress website. Choose what you would like inserted below and click Continue. It is recommended to leave everything selected. Once inserted, this content can be managed from the WordPress admin dashboard. %s Re-installing content from another demo, %s clear cache %s before content re-import.' ),'<hr>','<a class="clear_imported_posts" data-security="'.wp_create_nonce('wplms_clear_imported_posts').'">','</a>' ); ?></p>
				<?php } ?>
				<table class="envato-setup-pages" cellspacing="0">
					<thead>
					<tr>
						<td class="check"></td>
						<th class="item"><?php esc_html_e( 'Item' ); ?></th>
						<th class="description"><?php esc_html_e( 'Description' ); ?></th>
						<th class="status"><?php esc_html_e( 'Status' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $this->_content_default_get() as $slug => $default ) { ?>
						<tr class="envato_default_content" data-content="<?php echo esc_attr( $slug ); ?>">
							<td>
								<input type="checkbox" name="default_content[<?php echo esc_attr( $slug ); ?>]"
								       class="envato_default_content"
								       id="default_content_<?php echo esc_attr( $slug ); ?>"
								       value="1" <?php echo ( ! isset( $default['checked'] ) || $default['checked'] ) ? ' checked' : ''; ?> <?php echo (  isset( $default['disabled'] ) && $default['disabled'] ) ? ' disabled' : ''; ?>>
							</td>
							<td><label
									for="default_content_<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $default['title'] ); ?></label>
							</td>
							<td class="description"><?php echo esc_html( $default['description'] ); ?></td>
							<td class="status"><span><?php echo esc_html( $default['pending'] ); ?></span>
								<div class="spinner"></div>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>

				<p class="envato-setup-actions step">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button-primary button button-large button-next"
					   data-callback="install_content"><?php esc_html_e( 'Continue' ); ?></a>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button button-large button-next"><?php esc_html_e( 'Skip this step' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</p>
			</form>
			<?php
		}

		public function ajax_content() {
			$content = $this->_content_default_get();
			if ( ! check_ajax_referer( 'envato_setup_nonce', 'wpnonce' ) || empty( $_POST['content'] ) && isset( $content[ $_POST['content'] ] ) ) {
				wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No content Found' ) ) );
			}

			$json         = false;
			$this_content = $content[ $_POST['content'] ];

			if ( isset( $_POST['proceed'] ) ) {
				// install the content!

				$this->log( ' -!! STARTING SECTION for ' . $_POST['content'] );

				// init delayed posts from transient.
				$this->delay_posts = get_transient( 'delayed_posts' );
				if ( ! is_array( $this->delay_posts ) ) {
					$this->delay_posts = array();
				}

				if ( ! empty( $this_content['install_callback'] ) ) {
					if ( $result = call_user_func( $this_content['install_callback'] ) ) {

						$this->log( ' -- FINISH. Writing ' . count( $this->delay_posts, COUNT_RECURSIVE ) . ' delayed posts to transient ' );
						set_transient( 'delayed_posts', $this->delay_posts, 60 * 60 * 24 );

						if ( is_array( $result ) && isset( $result['retry'] ) ) {
							// we split the stuff up again.
							$json = array(
								'url'         => admin_url( 'admin-ajax.php' ),
								'action'      => 'envato_setup_content',
								'proceed'     => 'true',
								'retry'       => time(),
								'retry_count' => $result['retry_count'],
								'content'     => $_POST['content'],
								'_wpnonce'    => wp_create_nonce( 'envato_setup_nonce' ),
								'message'     => $this_content['installing'],
								'logs'        => $this->logs,
								'errors'      => $this->errors,
							);
						} else {
							$json = array(
								'done'    => 1,
								'message' => $this_content['success'],
								'debug'   => $result,
								'logs'    => $this->logs,
								'errors'  => $this->errors,
							);
						}
					}
				}
			} else {

				$json = array(
					'url'      => admin_url( 'admin-ajax.php' ),
					'action'   => 'envato_setup_content',
					'proceed'  => 'true',
					'content'  => $_POST['content'],
					'_wpnonce' => wp_create_nonce( 'envato_setup_nonce' ),
					'message'  => $this_content['installing'],
					'logs'     => $this->logs,
					'errors'   => $this->errors,
				);
			}

			if ( $json ) {
				$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next plugin
				wp_send_json( $json );

			} else {
				wp_send_json( array(
					'error'   => 1,
					'message' => esc_html__( 'Error' ),
					'logs'    => $this->logs,
					'errors'  => $this->errors,
				) );
			}

			exit;

		}


		private function _imported_term_id( $original_term_id, $new_term_id = false ) {
			$terms = get_transient( 'importtermids' );
			if ( ! is_array( $terms ) ) {
				$terms = array();
			}
			if ( $new_term_id ) {
				if ( ! isset( $terms[ $original_term_id ] ) ) {
					$this->log( 'Insert old TERM ID ' . $original_term_id . ' as new TERM ID: ' . $new_term_id );
				} else if ( $terms[ $original_term_id ] != $new_term_id ) {
					$this->error( 'Replacement OLD TERM ID ' . $original_term_id . ' overwritten by new TERM ID: ' . $new_term_id );
				}
				$terms[ $original_term_id ] = $new_term_id;
				set_transient( 'importtermids', $terms, 60 * 60 * 24 );
			} else if ( $original_term_id && isset( $terms[ $original_term_id ] ) ) {
				return $terms[ $original_term_id ];
			}

			return false;
		}


		public function vc_post( $post_id = false ) {

			$vc_post_ids = get_transient( 'import_vc_posts' );
			if ( ! is_array( $vc_post_ids ) ) {
				$vc_post_ids = array();
			}
			if ( $post_id ) {
				$vc_post_ids[ $post_id ] = $post_id;
				set_transient( 'import_vc_posts', $vc_post_ids, 60 * 60 * 24 );
			} else {

				$this->log( 'Processing vc pages 2: ' );

				return;
				if ( class_exists( 'Vc_Manager' ) && class_exists( 'Vc_Post_Admin' ) ) {
					$this->log( $vc_post_ids );
					$vc_manager = Vc_Manager::getInstance();
					$vc_base    = $vc_manager->vc();
					$post_admin = new Vc_Post_Admin();
					foreach ( $vc_post_ids as $vc_post_id ) {
						$this->log( 'Save ' . $vc_post_id );
						$vc_base->buildShortcodesCustomCss( $vc_post_id );
						$post_admin->save( $vc_post_id );
						$post_admin->setSettings( $vc_post_id );
						//twice? bug?
						$vc_base->buildShortcodesCustomCss( $vc_post_id );
						$post_admin->save( $vc_post_id );
						$post_admin->setSettings( $vc_post_id );
					}
				}
			}

		}

		public function elementor_post( $post_id = false ) {

			// regenrate the CSS for this Elementor post
			if( class_exists( 'Elementor\Post_CSS_File' ) ) {
                $post_css = new Elementor\Post_CSS_File($post_id);
				$post_css->update();
			}

		}

		private function _imported_post_id( $original_id = false, $new_id = false ) {
			if ( is_array( $original_id ) || is_object( $original_id ) ) {
				return false;
			}
			$post_ids = get_transient( 'importpostids' );
			if ( ! is_array( $post_ids ) ) {
				$post_ids = array();
			}
			if ( $new_id ) {
				if ( ! isset( $post_ids[ $original_id ] ) ) {
					$this->log( 'Insert old ID ' . $original_id . ' as new ID: ' . $new_id );
				} else if ( $post_ids[ $original_id ] != $new_id ) {
					$this->error( 'Replacement OLD ID ' . $original_id . ' overwritten by new ID: ' . $new_id );
				}
				$post_ids[ $original_id ] = $new_id;
				set_transient( 'importpostids', $post_ids, 60 * 60 * 24 );
			} else if ( $original_id && isset( $post_ids[ $original_id ] ) ) {
				return $post_ids[ $original_id ];
			} else if ( $original_id === false ) {
				return $post_ids;
			}

			return false;
		}

		private function _post_orphans( $original_id = false, $missing_parent_id = false ) {
			$post_ids = get_transient( 'postorphans' );
			if ( ! is_array( $post_ids ) ) {
				$post_ids = array();
			}
			if ( $missing_parent_id ) {
				$post_ids[ $original_id ] = $missing_parent_id;
				set_transient( 'postorphans', $post_ids, 60 * 60 * 24 );
			} else if ( $original_id && isset( $post_ids[ $original_id ] ) ) {
				return $post_ids[ $original_id ];
			} else if ( $original_id === false ) {
				return $post_ids;
			}

			return false;
		}

		private function _cleanup_imported_ids() {
			// loop over all attachments and assign the correct post ids to those attachments.

		}

		private $delay_posts = array();

		private function _delay_post_process( $post_type, $post_data ) {
			if ( ! isset( $this->delay_posts[ $post_type ] ) ) {
				$this->delay_posts[ $post_type ] = array();
			}
			$this->delay_posts[ $post_type ][ $post_data['post_id'] ] = $post_data;

		}

		// return the difference in length between two strings
		public function cmpr_strlen( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}

		private function _process_post_data( $post_type, $post_data, $delayed = 0, $debug = false ) {

			$this->log( " Processing $post_type " . $post_data['post_id'] );

			$original_post_data = $post_data;

			if ( $debug ) {
				echo "HERE\n";
			}
			if ( ! post_type_exists( $post_type ) ) {
				return false;
			}
			if ( ! $debug && $this->_imported_post_id( $post_data['post_id'] ) ) {
				return true; // already done :)
			}

			if ( empty( $post_data['post_title'] ) && empty( $post_data['post_name'] ) ) {
				// this is menu items
				$post_data['post_name'] = $post_data['post_id'];
			}

			$post_data['post_type'] = $post_type;

			$post_parent = (int) $post_data['post_parent'];
			if ( $post_parent ) {
				// if we already know the parent, map it to the new local ID
				if ( $this->_imported_post_id( $post_parent ) ) {
					$post_data['post_parent'] = $this->_imported_post_id( $post_parent );
					// otherwise record the parent for later
				} else {
					$this->_post_orphans( intval( $post_data['post_id'] ), $post_parent );
					$post_data['post_parent'] = 0;
				}
			}

			// check if already exists
			if ( ! $debug ) {
				if ( empty( $post_data['post_title'] ) && ! empty( $post_data['post_name'] ) ) {
					global $wpdb;
					$sql     = "
					SELECT ID, post_name, post_parent, post_type
					FROM $wpdb->posts
					WHERE post_name = %s
					AND post_type = %s
				";
					$pages   = $wpdb->get_results( $wpdb->prepare( $sql, array(
						$post_data['post_name'],
						$post_type,
					) ), OBJECT_K );
					$foundid = 0;
					foreach ( (array) $pages as $page ) {
						if ( $page->post_name == $post_data['post_name'] && empty( $page->post_title ) ) {
							$foundid = $page->ID;
						}
					}
					if ( $foundid ) {
						$this->_imported_post_id( $post_data['post_id'], $foundid );

						return true;
					}
				}
				// dont use post_exists because it will dupe up on media with same name but different slug
				if ( ! empty( $post_data['post_title'] ) && ! empty( $post_data['post_name'] ) ) {
					global $wpdb;
					$sql     = "
					SELECT ID, post_name, post_parent, post_type
					FROM $wpdb->posts
					WHERE post_name = %s
					AND post_title = %s
					AND post_type = %s
					";
					$pages   = $wpdb->get_results( $wpdb->prepare( $sql, array(
						$post_data['post_name'],
						$post_data['post_title'],
						$post_type,
					) ), OBJECT_K );
					$foundid = 0;
					foreach ( (array) $pages as $page ) {
						if ( $page->post_name == $post_data['post_name'] ) {
							$foundid = $page->ID;
						}
					}
					if ( $foundid ) {
						$this->_imported_post_id( $post_data['post_id'], $foundid );

						return true;
					}
				}
			}

			switch ( $post_type ) {
				case 'attachment':
					// import media via url
					if ( ! empty( $post_data['guid'] ) ) {

						// check if this has already been imported.
						$old_guid = $post_data['guid'];
						if ( $this->_imported_post_id( $old_guid ) ) {
							return true; // alrady done;
						}
						// ignore post parent, we haven't imported those yet.
						// $file_data = wp_remote_get($post_data['guid']);
						$remote_url = $post_data['guid'];

						$post_data['upload_date'] = date( 'Y/m', strtotime( $post_data['post_date_gmt'] ) );
						if ( isset( $post_data['meta'] ) ) {
							foreach ( $post_data['meta'] as $key => $meta ) {
								if ( $key == '_wp_attached_file' ) {
									foreach ( (array) $meta as $meta_val ) {
										if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta_val, $matches ) ) {
											$post_data['upload_date'] = $matches[0];
										}
									}
								}
							}
						}

						$upload = $this->_fetch_remote_file( $remote_url, $post_data );

						if ( ! is_array( $upload ) || is_wp_error( $upload ) ) {
							// todo: error
							return false;
						}

						if ( $info = wp_check_filetype( $upload['file'] ) ) {
							$post['post_mime_type'] = $info['type'];
						} else {
							return false;
						}

						$post_data['guid'] = $upload['url'];

						// as per wp-admin/includes/upload.php
						$post_id = wp_insert_attachment( $post_data, $upload['file'] );
						if($post_id) {

							if ( ! empty( $post_data['meta'] ) ) {
								foreach ( $post_data['meta'] as $meta_key => $meta_val ) {
									if($meta_key != '_wp_attached_file' && !empty($meta_val)) {
										update_post_meta( $post_id, $meta_key, $meta_val );
									}
								}
							}

							wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

							// remap resized image URLs, works by stripping the extension and remapping the URL stub.
							if ( preg_match( '!^image/!', $info['type'] ) ) {
								$parts = pathinfo( $remote_url );
								$name  = basename( $parts['basename'], ".{$parts['extension']}" ); // PATHINFO_FILENAME in PHP 5.2

								$parts_new = pathinfo( $upload['url'] );
								$name_new  = basename( $parts_new['basename'], ".{$parts_new['extension']}" );

								$this->_imported_post_id( $parts['dirname'] . '/' . $name, $parts_new['dirname'] . '/' . $name_new );
							}
							$this->_imported_post_id( $post_data['post_id'], $post_id );
							//$this->_imported_post_id( $old_guid, $post_id );
						}

					}
					break;	
				default:
					// work out if we have to delay this post insertion

					$replace_meta_vals = array(
						/*'_vc_post_settings'                                => array(
							'posts'      => array( 'item' ),
							'taxonomies' => array( 'taxonomies' ),
						),
						'_menu_item_object_id|_menu_item_menu_item_parent' => array(
							'post' => true,
						),*/
					);

					if ( ! empty( $post_data['meta'] ) && is_array( $post_data['meta'] ) ) {

						// replace any elementor post data:

						// fix for double json encoded stuff:
						foreach ( $post_data['meta'] as $meta_key => $meta_val ) {
							if ( is_string( $meta_val ) && strlen( $meta_val ) && $meta_val[0] == '[' ) {
								$test_json = @json_decode( $meta_val, true );
								if ( is_array( $test_json ) ) {
									$post_data['meta'][ $meta_key ] = $test_json;
								}
							}
						}

						array_walk_recursive( $post_data['meta'], array( $this, '_elementor_id_import' ) );

						// replace menu data:
						// work out what we're replacing. a tax, page, term etc..

						if(!empty($post_data['meta']['_menu_item_menu_item_parent'])) {
							$this->log[]='finding id for ...'.$post_data['meta']['_menu_item_menu_item_parent']. '##';
							$new_parent_id = $this->_imported_post_id( $post_data['meta']['_menu_item_menu_item_parent'] );
							if(!$new_parent_id) {
								if ( $delayed ) {
									// already delayed, unable to find this meta value, skip inserting it
									$this->error( 'Unable to find replacement. Continue anyway.... content will most likely break..' );
								} else {
									$this->error( 'Unable to find replacement. Delaying.... ' );
									$this->_delay_post_process( $post_type, $original_post_data );
									return false;
								}
							}
							$post_data['meta']['_menu_item_menu_item_parent'] = $new_parent_id;
						}
						if(isset($post_data['meta'][ '_menu_item_type' ])){

							switch($post_data['meta'][ '_menu_item_type' ]){
								case 'post_type':
									if(!empty($post_data['meta']['_menu_item_object_id'])) {
										$new_parent_id = $this->_imported_post_id( $post_data['meta']['_menu_item_object_id'] );

										$this->log(' #3 FOUND id '.$post_data['meta']['_menu_item_object_id'].' - '.$new_parent_id);

										if(!$new_parent_id) {
											if ( $delayed ) {
												// already delayed, unable to find this meta value, skip inserting it
												$this->error( 'Unable to find replacement. Continue anyway.... content will most likely break..' );
											} else {
												$this->error( 'Unable to find replacement. Delaying.... ' );
												$this->_delay_post_process( $post_type, $original_post_data );
												return false;
											}
										}
										$post_data['meta']['_menu_item_object_id'] = $new_parent_id;
									}
									break;
								case 'taxonomy':
									if(!empty($post_data['meta']['_menu_item_object_id'])) {
										$new_parent_id = $this->_imported_term_id( $post_data['meta']['_menu_item_object_id'] );
										if(!$new_parent_id) {
											if ( $delayed ) {
												// already delayed, unable to find this meta value, skip inserting it
												$this->error( 'Unable to find replacement. Continue anyway.... content will most likely break..' );
											} else {
												$this->error( 'Unable to find replacement. Delaying.... ' );
												$this->_delay_post_process( $post_type, $original_post_data );
												return false;
											}
										}
										$post_data['meta']['_menu_item_object_id'] = $new_parent_id;
									}
									break;
							}
						}

						// please ignore this horrible loop below:
						// it was an attempt to automate different visual composer meta key replacements
						// but I'm not using visual composer any more, so ignoring it.
						foreach ( $replace_meta_vals as $meta_key_to_replace => $meta_values_to_replace ) {

							$meta_keys_to_replace   = explode( '|', $meta_key_to_replace );
							$success                = false;
							$trying_to_find_replace = false;
							foreach ( $meta_keys_to_replace as $meta_key ) {

								if ( ! empty( $post_data['meta'][ $meta_key ] ) ) {

									$meta_val = $post_data['meta'][ $meta_key ];

									if ( $debug ) {
										echo "Meta key: $meta_key \n";
										print_r( $meta_val );
									}

									// if we're replacing a single post/tax value.
									if ( isset( $meta_values_to_replace['post'] ) && $meta_values_to_replace['post'] && (int) $meta_val > 0 ) {
										$trying_to_find_replace = true;
										$new_meta_val           = $this->_imported_post_id( $meta_val );
										if ( $new_meta_val ) {
											$post_data['meta'][ $meta_key ] = $new_meta_val;
											$success                        = true;
										} else {
											$success = false;
											break;
										}
									}
									if ( isset( $meta_values_to_replace['taxonomy'] ) && $meta_values_to_replace['taxonomy'] && (int) $meta_val > 0 ) {
										$trying_to_find_replace = true;
										$new_meta_val           = $this->_imported_term_id( $meta_val );
										if ( $new_meta_val ) {
											$post_data['meta'][ $meta_key ] = $new_meta_val;
											$success                        = true;
										} else {
											$success = false;
											break;
										}
									}
									if ( is_array( $meta_val ) && isset( $meta_values_to_replace['posts'] ) ) {

										foreach ( $meta_values_to_replace['posts'] as $post_array_key ) {

											$this->log( 'Trying to find/replace "' . $post_array_key . '"" in the ' . $meta_key . ' sub array:' );
											//$this->log(var_export($meta_val,true));

											$this_success = false;
											array_walk_recursive( $meta_val, function ( &$item, $key ) use ( &$trying_to_find_replace, $post_array_key, &$success, &$this_success, $post_type, $original_post_data, $meta_key, $delayed ) {
												if ( $key == $post_array_key && (int) $item > 0 ) {
													$trying_to_find_replace = true;
													$new_insert_id          = $this->_imported_post_id( $item );
													if ( $new_insert_id ) {
														$success      = true;
														$this_success = true;
														$this->log( 'Found' . $meta_key . ' -> ' . $post_array_key . ' replacement POST ID insert for ' . $item . ' ( as ' . $new_insert_id . ' ) ' );
														$item = $new_insert_id;
													} else {
														$this->error( 'Unable to find ' . $meta_key . ' -> ' . $post_array_key . ' POST ID insert for ' . $item . ' ' );
													}
												}
											} );
											if ( $this_success ) {
												$post_data['meta'][ $meta_key ] = $meta_val;
											}
										}
										foreach ( $meta_values_to_replace['taxonomies'] as $post_array_key ) {

											$this->log( 'Trying to find/replace "' . $post_array_key . '"" TAXONOMY in the ' . $meta_key . ' sub array:' );
											//$this->log(var_export($meta_val,true));

											$this_success = false;
											array_walk_recursive( $meta_val, function ( &$item, $key ) use ( &$trying_to_find_replace, $post_array_key, &$success, &$this_success, $post_type, $original_post_data, $meta_key, $delayed ) {
												if ( $key == $post_array_key && (int) $item > 0 ) {
													$trying_to_find_replace = true;
													$new_insert_id          = $this->_imported_term_id( $item );
													if ( $new_insert_id ) {
														$success      = true;
														$this_success = true;
														$this->log( 'Found' . $meta_key . ' -> ' . $post_array_key . ' replacement TAX ID insert for ' . $item . ' ( as ' . $new_insert_id . ' ) ' );
														$item = $new_insert_id;
													} else {
														$this->error( 'Unable to find ' . $meta_key . ' -> ' . $post_array_key . ' TAX ID insert for ' . $item . ' ' );
													}
												}
											} );

											if ( $this_success ) {
												$post_data['meta'][ $meta_key ] = $meta_val;
											}
										}
									}

									if ( $success ) {
										if ( $debug ) {
											echo "Meta key AFTER REPLACE: $meta_key \n";
											print_r( $post_data['meta'] );
										}
									}
								}
							}
							if ( $trying_to_find_replace ) {
								$this->log( 'Trying to find/replace postmeta "' . $meta_key_to_replace . '" ' );
								if ( ! $success ) {
									// failed to find a replacement.
									if ( $delayed ) {
										// already delayed, unable to find this meta value, skip inserting it
										$this->error( 'Unable to find replacement. Continue anyway.... content will most likely break..' );
									} else {
										$this->error( 'Unable to find replacement. Delaying.... ' );
										$this->_delay_post_process( $post_type, $original_post_data );

										return false;
									}
								} else {
									$this->log( 'SUCCESSSS ' );
								}
							}
						}
					}

					$post_data['post_content'] = $this->_parse_gallery_shortcode_content($post_data['post_content']);

					// we have to fix up all the visual composer inserted image ids
					$replace_post_id_keys = array(
						'parallax_image',
						'image',
						'item', // vc grid
						'post_id',
					);
					foreach ( $replace_post_id_keys as $replace_key ) {
						if ( preg_match_all( '# ' . $replace_key . '="(\d+)"#', $post_data['post_content'], $matches ) ) {
							foreach ( $matches[0] as $match_id => $string ) {
								$new_id = $this->_imported_post_id( $matches[1][ $match_id ] );
								if ( $new_id ) {
									$post_data['post_content'] = str_replace( $string, ' ' . $replace_key . '="' . $new_id . '"', $post_data['post_content'] );
								} else {
									$this->error( 'Unable to find POST replacement for ' . $replace_key . '="' . $matches[1][ $match_id ] . '" in content.' );
									if ( $delayed ) {
										// already delayed, unable to find this meta value, insert it anyway.

									} else {

										$this->error( 'Adding ' . $post_data['post_id'] . ' to delay listing.' );
										//                                      echo "Delaying post id ".$post_data['post_id']."... \n\n";
										$this->_delay_post_process( $post_type, $original_post_data );

										return false;
									}
								}
							}
						}
					}
					$replace_tax_id_keys = array(
						'taxonomies',
					);
					foreach ( $replace_tax_id_keys as $replace_key ) {
						if ( preg_match_all( '# ' . $replace_key . '="(\d+)"#', $post_data['post_content'], $matches ) ) {
							foreach ( $matches[0] as $match_id => $string ) {
								$new_id = $this->_imported_term_id( $matches[1][ $match_id ] );
								if ( $new_id ) {
									$post_data['post_content'] = str_replace( $string, ' ' . $replace_key . '="' . $new_id . '"', $post_data['post_content'] );
								} else {
									$this->error( 'Unable to find TAXONOMY replacement for ' . $replace_key . '="' . $matches[1][ $match_id ] . '" in content.' );
									if ( $delayed ) {
										// already delayed, unable to find this meta value, insert it anyway.
									} else {
										//                                      echo "Delaying post id ".$post_data['post_id']."... \n\n";
										$this->_delay_post_process( $post_type, $original_post_data );

										return false;
									}
								}
							}
						}
					}

					$post_id = wp_insert_post( $post_data, true );

					if ( ! is_wp_error( $post_id ) ) {
						$this->_imported_post_id( $post_data['post_id'], $post_id );
						// add/update post meta
						if ( ! empty( $post_data['meta'] ) ) {
							foreach ( $post_data['meta'] as $meta_key => $meta_val ) {

								// if the post has a featured image, take note of this in case of remap
								if ( '_thumbnail_id' == $meta_key ) {
									/// find this inserted id and use that instead.
									$inserted_id = $this->_imported_post_id( intval( $meta_val ) );
									if ( $inserted_id ) {
										$meta_val = $inserted_id;
									}
								}

								if(!is_numeric($meta_key)){
									update_post_meta( $post_id, $meta_key, $meta_val );
								}

							}
						}
						if ( ! empty( $post_data['terms'] ) ) {
							$terms_to_set = array();
							foreach ( $post_data['terms'] as $term_slug => $terms ) {
								foreach ( $terms as $term ) {
									$taxonomy = $term['taxonomy'];
									if ( taxonomy_exists( $taxonomy ) ) {
										$term_exists = term_exists( $term['slug'], $taxonomy );
										$term_id     = is_array( $term_exists ) ? $term_exists['term_id'] : $term_exists;
										if ( ! $term_id ) {
											if ( ! empty( $term['parent'] ) ) {
												// see if we have imported this yet?
												$term['parent'] = $this->_imported_term_id( $term['parent'] );
											}
											$t = wp_insert_term( $term['name'], $taxonomy, $term );
											if ( ! is_wp_error( $t ) ) {
												$term_id = $t['term_id'];
											} else {
												// todo - error
												continue;
											}
										}
										$this->_imported_term_id( $term['term_id'], $term_id );
										// add the term meta.
										if($term_id && !empty($term['meta']) && is_array($term['meta'])){
											foreach($term['meta'] as $meta_key => $meta_val){
											    // we have to replace certain meta_key/meta_val
                                                // e.g. thumbnail id from woocommerce product categories.
                                                switch($meta_key){
                                                    case 'thumbnail_id':
                                                        if( $new_meta_val = $this->_imported_post_id($meta_val) ){
                                                            // use this new id.
                                                            $meta_val = $new_meta_val;
                                                        }
                                                        break;
                                                    case 'course_cat_thumbnail_id':
                                                    	 if( $new_meta_val = $this->_imported_post_id($meta_val) ){
                                                            // use this new id.
                                                            $meta_val = $new_meta_val;
                                                        }
                                                    break;
                                                }
												update_term_meta( $term_id, $meta_key, $meta_val );
											}
										}
										$terms_to_set[ $taxonomy ][] = intval( $term_id );
									}
								}
							}
							foreach ( $terms_to_set as $tax => $ids ) {
								wp_set_post_terms( $post_id, $ids, $tax );
							}
						}

						// procses visual composer just to be sure.
						if ( strpos( $post_data['post_content'], '[vc_' ) !== false ) {
							$this->vc_post( $post_id );
						}
						if ( !empty($post_data['meta']['_elementor_data']) || !!empty($post_data['meta']['_elementor_css']) ) {
							$this->elementor_post( $post_id );
						}
					}

					break;
			}

			return true;
		}

		private function _parse_gallery_shortcode_content($content){
			// we have to format the post content. rewriting images and gallery stuff
			$replace      = $this->_imported_post_id();
			$urls_replace = array();
			foreach ( $replace as $key => $val ) {
				if ( $key && $val && ! is_numeric( $key ) && ! is_numeric( $val ) ) {
					$urls_replace[ $key ] = $val;
				}
			}
			if ( $urls_replace ) {
				uksort( $urls_replace, array( &$this, 'cmpr_strlen' ) );
				foreach ( $urls_replace as $from_url => $to_url ) {
					$content = str_replace( $from_url, $to_url, $content );
				}
			}
			if ( preg_match_all( '#\[gallery[^\]]*\]#', $content, $matches ) ) {
				foreach ( $matches[0] as $match_id => $string ) {
					if ( preg_match( '#ids="([^"]+)"#', $string, $ids_matches ) ) {
						$ids = explode( ',', $ids_matches[1] );
						foreach ( $ids as $key => $val ) {
							$new_id = $val ? $this->_imported_post_id( $val ) : false;
							if ( ! $new_id ) {
								unset( $ids[ $key ] );
							} else {
								$ids[ $key ] = $new_id;
							}
						}
						$new_ids                   = implode( ',', $ids );
						$content = str_replace( $ids_matches[0], 'ids="' . $new_ids . '"', $content );
					}
				}
			}
			return $content;
		}

		private function _elementor_id_import( &$item, $key ) {
			if ( $key == 'id' && ! empty( $item ) && is_numeric( $item ) ) {
				// check if this has been imported before
				$new_meta_val = $this->_imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( $key == 'page' && ! empty( $item ) ) {

				if ( false !== strpos( $item, 'p.' ) ) {
					$new_id = str_replace('p.', '', $item);
					// check if this has been imported before
					$new_meta_val = $this->_imported_post_id( $new_id );
					if ( $new_meta_val ) {
						$item = 'p.' . $new_meta_val;
					}
				}else if(is_numeric($item)){
					// check if this has been imported before
					$new_meta_val = $this->_imported_post_id( $item );
					if ( $new_meta_val ) {
						$item = $new_meta_val;
					}
				}
			}
			if ( $key == 'post_id' && ! empty( $item ) && is_numeric( $item ) ) {
				// check if this has been imported before
				$new_meta_val = $this->_imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( $key == 'url' && ! empty( $item ) && strstr( $item, 'ocalhost' ) ) {
				// check if this has been imported before
				$new_meta_val = $this->_imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( ($key == 'shortcode' || $key == 'editor') && ! empty( $item ) ) {
				// we have to fix the [contact-form-7 id=133] shortcode issue.
				$item = $this->_parse_gallery_shortcode_content($item);

			}
		}

		public function _content_install_type($type=null,$index=null) {
			$post_type = ! empty( $_POST['content'] ) ? $_POST['content'] : false;
			if(!empty($type)){
				$post_type= $type;
			}
			$all_data  = $this->_get_json( 'default.json' );
			if ( ! $post_type || ! isset( $all_data[ $post_type ] ) ) {
				return false;
			}
			$limit = 10 + ( isset( $_REQUEST['retry_count'] ) ? (int) $_REQUEST['retry_count'] : 0 );
			if(!isset($_REQUEST['retry_count']) && !empty($index)){
				$limit = 5 + ( isset( $index) ? (int) $index : 0 );
			}
			$x  = 0;
			
			$this->logs[]='#1 - Inside the Nav menu item - '.$post_type;
			if($post_type == 'nav_menu_item'){
				$style = get_option('wplms_site_style');
				if(empty($style)){$style = $this->get_default_theme_style();}
				
				if($style == 'demo1'){
					$x = $this->_imported_post_id(2218);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 2218, $course_directory->ID );	

						$activity_directory = get_page_by_title( 'Activity' );
						$this->_imported_post_id( 2216, $activity_directory->ID );	

						$member_directory = get_page_by_title( 'Members' );
						$this->_imported_post_id( 2237, $member_directory->ID );

						$this->logs[]='#2 - Sample post ids Imported - demo1';
					}
				}else if($style == 'demo2'){
					$x = $this->_imported_post_id(2108);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 2108, $course_directory->ID );	

						$activity_directory = get_page_by_title( 'Activity' );
						$this->_imported_post_id( 2121, $activity_directory->ID );	

						$member_directory = get_page_by_title( 'Members' );
						$this->_imported_post_id( 2122, $member_directory->ID );

						$this->logs[]='#2 - Sample post ids Imported - demo2';
					}
				}else if($style == 'demo3'){
					$x = $this->_imported_post_id(2140);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 2140, $course_directory->ID );	

						$activity_directory = get_page_by_title( 'Activity' );
						$this->_imported_post_id( 2158, $activity_directory->ID );	

						$member_directory = get_page_by_title( 'Members' );
						$this->_imported_post_id( 2159, $member_directory->ID );

						$this->logs[]='#2 - Sample post ids Imported - demo2';
					}
				}else if( $style == 'demo4'){
					$x = $this->_imported_post_id(2172);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 2172, $course_directory->ID );	

						$activity_directory = get_page_by_title( 'Activity' );
						$this->_imported_post_id( 2186, $activity_directory->ID );	

						$member_directory = get_page_by_title( 'Members' );
						$this->_imported_post_id( 2187, $member_directory->ID );
					}
				}else if($style == 'demo6'){
					$x = $this->_imported_post_id(2140);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 2140, $course_directory->ID );

					}
				}else if($style == 'demo7'){
					$x = $this->_imported_post_id(2140);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 2140, $course_directory->ID );

					}
				}else if($style == 'demo8'){
					$x = $this->_imported_post_id(25);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 25, $course_directory->ID );

					}
				}else if($style == 'default'){
					$x = $this->_imported_post_id(1994);
					if(empty($x)){
						$course_directory = get_page_by_title( 'All Courses' );
						$this->_imported_post_id( 1994, $course_directory->ID );

					}
				}
			}

			foreach ( $all_data[ $post_type ] as $post_data ) {

				$this->_process_post_data( $post_type, $post_data );

				if ( $x ++ > $limit ) {
					return array( 'retry' => 1, 'retry_count' => $limit );
				}
			}

			$this->_handle_delayed_posts();
			$this->_handle_post_orphans();

			return true;

		}

		private function _handle_post_orphans() {
			$orphans = $this->_post_orphans();
			foreach ( $orphans as $original_post_id => $original_post_parent_id ) {
				if ( $original_post_parent_id ) {
					if ( $this->_imported_post_id( $original_post_id ) && $this->_imported_post_id( $original_post_parent_id ) ) {
						$post_data                = array();
						$post_data['ID']          = $this->_imported_post_id( $original_post_id );
						$post_data['post_parent'] = $this->_imported_post_id( $original_post_parent_id );
						wp_update_post( $post_data );
						$this->_post_orphans( $original_post_id, 0 ); // ignore future
					}
				}
			}
		}

		private function _handle_delayed_posts( $last_delay = false ) {

			$this->log( ' ---- Processing ' . count( $this->delay_posts, COUNT_RECURSIVE ) . ' delayed posts' );
			for ( $x = 1; $x < 4; $x ++ ) {
				foreach ( $this->delay_posts as $delayed_post_type => $delayed_post_datas ) {
					foreach ( $delayed_post_datas as $delayed_post_id => $delayed_post_data ) {
						if ( $this->_imported_post_id( $delayed_post_data['post_id'] ) ) {
							$this->log( $x . ' - Successfully processed ' . $delayed_post_type . ' ID ' . $delayed_post_data['post_id'] . ' previously.' );
							unset( $this->delay_posts[ $delayed_post_type ][ $delayed_post_id ] );
							$this->log( ' ( ' . count( $this->delay_posts, COUNT_RECURSIVE ) . ' delayed posts remain ) ' );
						} else if ( $this->_process_post_data( $delayed_post_type, $delayed_post_data, $last_delay ) ) {
							$this->log( $x . ' - Successfully found delayed replacement for ' . $delayed_post_type . ' ID ' . $delayed_post_data['post_id'] . '.' );
							// successfully inserted! don't try again.
							unset( $this->delay_posts[ $delayed_post_type ][ $delayed_post_id ] );
							$this->log( ' ( ' . count( $this->delay_posts, COUNT_RECURSIVE ) . ' delayed posts remain ) ' );
						}
					}
				}
			}
		}

		private function _fetch_remote_file( $url, $post ) {
			// extract the file name and extension from the url
			$file_name  = basename( $url );
			$upload     = false;

			if ( ! $upload || $upload['error'] ) {
				// get placeholder file in the upload dir with a unique, sanitized filename
				$upload = wp_upload_bits( $file_name, 0, '', $post['upload_date'] );
				if ( $upload['error'] ) {
					return new WP_Error( 'upload_dir_error', $upload['error'] );
				}

				$max_size = (int) apply_filters( 'import_attachment_size_limit', 0 );

				if ( empty( $this->debug ) ) {

					//Change to Uploaded file path if uploaded
					$path = get_option('wplms_export_import_content_path');
					if( !empty($path) ){
						$vibe_url = site_url().'/wp-content/uploads/upload_demos/'.basename($path).'/images/'.$file_name;
					}else{
						if(strpos($url, 'http://local.wordpress.dev') !== false || strpos($url, 'htt://themes.vibethemes.com') !== false){
							$vibe_url = 'https://wplms.io/demos/demodata/content/images/'.$file_name;
						}else{
							$vibe_url = $url;
						}
					}
				}

				$response = wp_remote_get( $vibe_url ,array('timeout' => 60));
				if ( is_array( $response ) && ! empty( $response['body'] ) && $response['response']['code'] == '200' ) {
					//
				}else{
					$local_file = trailingslashit( get_template_directory() ) . 'assets/images/title_bg.png';
					
					if ( is_file( $local_file ) && filesize( $local_file ) > 0 ) {
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
						WP_Filesystem();
						global $wp_filesystem;
						$file_data = $wp_filesystem->get_contents( $local_file );
						$upload    = wp_upload_bits( $file_name, 0, $file_data, $post['upload_date'] );
						if ( $upload['error'] ) {
							return new WP_Error( 'upload_dir_error', $upload['error'] );
						}
					}
				}

				if ( is_array( $response ) && ! empty( $response['body'] ) && $response['response']['code'] == '200' ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					$headers = $response['headers'];
					WP_Filesystem();
					global $wp_filesystem;
					$wp_filesystem->put_contents( $upload['file'], $response['body'] );
					//
				} else {
					// required to download file failed.
					@unlink( $upload['file'] );

					return new WP_Error( 'import_file_error', esc_html__( 'Remote server did not respond' ) );
				}

				$filesize = filesize( $upload['file'] );

				if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
					@unlink( $upload['file'] );

					return new WP_Error( 'import_file_error', esc_html__( 'Remote file is incorrect size' ) );
				}

				if ( 0 == $filesize ) {
					@unlink( $upload['file'] );

					return new WP_Error( 'import_file_error', esc_html__( 'Zero size file downloaded' ) );
				}

				if ( ! empty( $max_size ) && $filesize > $max_size ) {
					@unlink( $upload['file'] );

					return new WP_Error( 'import_file_error', sprintf( esc_html__( 'Remote file is too large, limit is %s' ), size_format( $max_size ) ) );
				}
			}

			// keep track of the old and new urls so we can substitute them later
			$this->_imported_post_id( $url, $upload['url'] );
			$this->_imported_post_id( $post['guid'], $upload['url'] );
			// keep track of the destination if the remote url is redirected somewhere else
			if ( isset( $headers['x-final-location'] ) && $headers['x-final-location'] != $url ) {
				$this->_imported_post_id( $headers['x-final-location'], $upload['url'] );
			}

			return $upload;
		}

		public function _content_install_widgets() {
			// todo: pump these out into the 'content/' folder along with the XML so it's a little nicer to play with
			$import_widget_positions = $this->_get_json( 'widget_positions.json' );
			$import_widget_options   = $this->_get_json( 'widget_options.json' );

			// importing.
			$widget_positions = get_option( 'sidebars_widgets' );
			if ( ! is_array( $widget_positions ) ) {
				$widget_positions = array();
			}

			foreach ( $import_widget_options as $widget_name => $widget_options ) {
				// replace certain elements with updated imported entries.
				foreach ( $widget_options as $widget_option_id => $widget_option ) {

					// replace TERM ids in widget settings.
					foreach ( array( 'nav_menu' ) as $key_to_replace ) {
						if ( ! empty( $widget_option[ $key_to_replace ] ) ) {
							// check if this one has been imported yet.
							$new_id = $this->_imported_term_id( $widget_option[ $key_to_replace ] );
							if ( ! $new_id ) {
								// do we really clear this out? nah. well. maybe.. hmm.
							} else {
								$widget_options[ $widget_option_id ][ $key_to_replace ] = $new_id;
							}
						}
					}
					// replace POST ids in widget settings.
					foreach ( array( 'image_id', 'post_id' ) as $key_to_replace ) {
						if ( ! empty( $widget_option[ $key_to_replace ] ) ) {
							// check if this one has been imported yet.
							$new_id = $this->_imported_post_id( $widget_option[ $key_to_replace ] );
							if ( ! $new_id ) {
								// do we really clear this out? nah. well. maybe.. hmm.
							} else {
								$widget_options[ $widget_option_id ][ $key_to_replace ] = $new_id;
							}
						}
					}
				}
				$existing_options = get_option( 'widget_' . $widget_name, array() );
				if ( ! is_array( $existing_options ) ) {
					$existing_options = array();
				}
				$new_options = $existing_options + $widget_options;
				update_option( 'widget_' . $widget_name, $new_options );
			}
			update_option( 'sidebars_widgets', array_merge( $widget_positions, $import_widget_positions ) );

			return true;

		}

		public function _content_options_settings(){

			$this->logs[] = 'inside options panel';
			$custom_options = $this->_get_json( 'options.json' );

			foreach ( $custom_options as $option => $value ) {
				if($option == 'wplms' ){
					$ops = get_option($option);
					foreach($value as $key => $val){
						$ops[$key] = $val;
					}

					update_option( $option, $ops );

					break;
				}		
			}

			return true;
		}

		public function _content_customizer_settings(){

			$this->logs[] = 'inside customizer settings';
			$custom_options = $this->_get_json( 'options.json' );

			foreach ( $custom_options as $option => $value ) {
				
				if($option == 'vibe_customizer'){
					$ops = get_option('vibe_customizer');
					foreach($value as $key => $val){
						$ops[$key] = $val;
					}
					update_option( $option, $ops );
					break;
				}
			}

			return true;

		}

		public function _content_install_settings() {

			$this->_handle_delayed_posts( true ); // final wrap up of delayed posts.
			$this->vc_post(); // final wrap of vc posts.
			$this->logs[] = 'inside settings';
			$custom_options = $this->_get_json( 'options.json' );

			// we also want to update the widget area manager options.
			foreach ( $custom_options as $option => $value ) {
				// we have to update widget page numbers with imported page numbers.
				if (
					preg_match( '#(wam__position_)(\d+)_#', $option, $matches ) ||
					preg_match( '#(wam__area_)(\d+)_#', $option, $matches )
				) {
					$new_page_id = $this->_imported_post_id( $matches[2] );
					if ( $new_page_id ) {
						// we have a new page id for this one. import the new setting value.
						$option = str_replace( $matches[1] . $matches[2] . '_', $matches[1] . $new_page_id . '_', $option );
					}
				}

				if($option != 'wplms' && $option != 'vibe_customizer'){
					update_option( $option, $value );	
				}
			}

			$menu_ids = $this->_get_json( 'menu.json' );
			$save     = array();
			$style = get_option('wplms_site_style');
			if(empty($style)){$style = $this->get_default_theme_style();}

			foreach ( $menu_ids as $menu_id => $term_id ) { 

				$new_term_id = $this->_imported_term_id( $term_id );
				if ( $new_term_id  && in_array($style,array('demo1','demo3','demo4','demo5','demo6','demo7','demo8'))) {
					$save[ $menu_id ] = $new_term_id;
				}else{
					$save[ $menu_id ] = $term_id;
				}
			}
			if ( $save ) {
				set_theme_mod( 'nav_menu_locations', array_map( 'absint', $save ) );
			}

			// set the blog page and the home page.
			$shoppage = get_page_by_title( 'Shop' );
			if ( $shoppage ) {
				update_option( 'woocommerce_shop_page_id', $shoppage->ID );
			}
			$shoppage = get_page_by_title( 'Cart' );
			if ( $shoppage ) {
				update_option( 'woocommerce_cart_page_id', $shoppage->ID );
			}
			$shoppage = get_page_by_title( 'Checkout' );
			if ( $shoppage ) {
				update_option( 'woocommerce_checkout_page_id', $shoppage->ID );
			}
			$shoppage = get_page_by_title( 'My Account' );
			if ( $shoppage ) {
				update_option( 'woocommerce_myaccount_page_id', $shoppage->ID );
			}
			
			$homepage = get_page_by_title( 'Home' );
			if ( $homepage ) { 
				update_option( 'page_on_front', $homepage->ID );
				update_option( 'show_on_front', 'page' );
				update_post_meta($homepage->ID,'_wp_page_template','notitle.php');
				update_post_meta($homepage->ID,'_add_content','no');
			}

			$blogpage = get_page_by_title( 'Blog' );
			if ( $blogpage ) {
				update_option( 'page_for_posts', $blogpage->ID );
				update_option( 'show_on_front', 'page' );
			}

			global $wp_rewrite;
		    $wp_rewrite->set_permalink_structure( '/%postname%/' );
		    $wp_rewrite->flush_rules();
		    $wp_rewrite->init();
			$post_ids = get_transient( 'importpostids' );
			
			if(!empty($post_ids)){
				
				$meta_keys = array('vibe_product','vibe_quiz_course','vibe_courses','vibe_course_curriculum','vibe_quiz_questions','vibe_forum','vibe_pre_course','vibe_assignment','vibe_assignment_course','_menu_item_object_id');

				foreach($post_ids as $i=>$d){
					if(is_numeric($i) && is_numeric($d)){
						$ids[$i] = $d;
					}
				}
				$ids = implode(',',$ids);
				foreach($meta_keys as $meta_key){
					global $wpdb;

					$results = $wpdb->get_results($wpdb->prepare("SELECT post_id,meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id IN ($ids)",$meta_key));

					if(!empty($results)){
						foreach($results as $result){
							
							if(is_numeric($result->meta_value)){
								if(isset($post_ids[$result->meta_value])){
									update_post_meta($result->post_id,$meta_key,$post_ids[$result->meta_value]);
								}
							}else{
								if(is_string($result->meta_value)){
									$result->meta_value = unserialize($result->meta_value);	
								}
							 	if(is_array($result->meta_value)){
									$changed = 0;
									
									foreach($result->meta_value as $k=>$v){
										
										if(is_numeric($v) && isset($post_ids[$v])){
											$changed = 1;
											$result->meta_value[$k] = $post_ids[$v];
										}else{
											if(is_string($v)){
												$v = @unserialize($v);
											}
										} 
										
										if(is_array($v) && $k == 'ques'){ //Quiz questions use case
											foreach($v as $i => $q){
												if(is_numeric($q) && isset($post_ids[$q])){
													$changed = 1;
													$result->meta_value[$k][$i] = $post_ids[$q];
												}
											}
										}
									}
									if($changed){update_post_meta($result->post_id,$meta_key,$result->meta_value);}
								}
							}
						}
					}
				}
			}

			update_option('default_role','student');			

			global $wp_rewrite;
			$wp_rewrite->set_permalink_structure( '/%postname%/' );
			update_option( 'rewrite_rules', false );
			$wp_rewrite->flush_rules( true );

			return true;
		}

		function _content_install_slider(){

			$style = get_option('wplms_site_style');

			if(empty($style)){$style = $this->get_default_theme_style();}

			$slider_array = array();
			$ls_slider_array = array();

			//$url = 'https://s3.amazonaws.com/wplmsdownloads/demodata/'.$style;
			$url = 'https://wplms.io/demos/demodata/content/'.$style;

			if(in_array($style,array('demo1'))){
				$slider_array = array($url."/classicslider1.zip");
			}
			if(in_array($style,array('demo2'))){
				$slider_array = array($url."/search-form-hero2.zip",$url."/news-hero4.zip",$url."/about1.zip");
			}
			if(in_array($style,array('demo3'))){
				$slider_array = array($url."/highlight-showcase4.zip");
			}

			if(in_array($style,array('demo4'))){
				$slider_array = array($url."/homeslider.zip",$url."/categories.zip");
			}

			if(in_array($style,array('demo5'))){
				$slider_array = array($url."/demo5.zip");
			}

			if(in_array($style,array('demo6'))){
				$slider_array = array($url."/homeslider.zip");
			}

			if(in_array($style,array('demo7'))){
				$slider_array = array($url."/demo7.zip");
			}

			if(in_array($style,array('demo8'))){
				$slider_array = array($url."/demo8.zip");
			}
 
			if(in_array($style,array('demo9'))){
				$slider_array = array($url."/demo9.zip",$url."/demo9_parallax.zip");
			}
			
			if(in_array($style,array('default'))){
				$ls_slider_array = array($url."/lsslider.zip");
			}

			if(in_array($style,array('demo10'))){
				$ls_slider_array = array($url."/demo10-1.zip",$url."/demo10-2.zip",$url."/demo10-3.zip");
			}
	        
	        if(!empty($ls_slider_array)){
	        	include LS_ROOT_PATH.'/classes/class.ls.importutil.php';
	        	if(class_exists('LS_ImportUtil')){
	        		foreach($ls_slider_array as $url) {
		        		$filepath = $this->_download_slider($url);
						$import = new LS_ImportUtil($filepath);
					}
	        	}
	        }


			if(class_exists('RevSlider') && !empty($slider_array)){
				$slider = new RevSlider();
				foreach($slider_array as $url){
					$filepath = $this->_download_slider($url);
					$slider->importSliderFromPost(true,true,$filepath);  
				}	

			}


			return true;
		}

		function _download_slider($url){

			$file_name = basename( $url );
			$upload_dir = wp_upload_dir();
			$full_path = $upload_dir['path'].'/'.$file_name;
			if(file_exists($full_path)){
				@unlink($full_path);
			}

			$upload = wp_upload_bits( $file_name, 0, '');

			if ( $upload['error'] ) { // File already imported
				@unlink( $upload['file'] );

				$upload = wp_upload_bits( $file_name, 0, '');

				if ( $upload['error'] ) {
					return $upload['file'];
				}
				//new WP_Error( 'upload_dir_error', $upload['error'] );
			}

			// we check if this file is uploaded locally in the source folder.
			$response = wp_remote_get( $url ,array('timeout' => 200));


			WP_Filesystem();
			global $wp_filesystem;
			$wp_filesystem->put_contents( $upload['file'], $response['body'] );
				
			if ( is_array( $response ) && ! empty( $response['body'] ) && $response['response']['code'] == '200' ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				$headers = $response['headers'];
				WP_Filesystem();
				global $wp_filesystem;
				$wp_filesystem->put_contents( $upload['file'], $response['body'] );
			} else {
				// required to download file failed.
				@unlink( $upload['file'] );

				return new WP_Error( 'import_file_error', esc_html__( 'Remote server did not respond' ) );
			}

			return $upload['file'];
		}

		public function _get_json( $file ) {

			//Change to Uploaded file path if uploaded
			$path = get_option('wplms_export_import_content_path');

			if( !empty($path) ){
				$style = basename($path);
				$theme_style = $path.'/';
			}else{
				$style = get_option('wplms_site_style');
				if(empty($style)){$style = $this->get_default_theme_style();}
				$theme_style = __DIR__ . '/content/' . basename($style) .'/';
			}

            if($file == 'options.json'){
                WP_Filesystem();
                global $wp_filesystem;
                $file_name = $theme_style . basename( $file );   
                $file = $wp_filesystem->get_contents( $file_name );
                if(empty($file)){
                    $file = file_get_contents($file_name);
                }  
                return json_decode($file, true );
            }

            if ( is_file( $theme_style . basename( $file ) ) ) {
                WP_Filesystem();
                global $wp_filesystem;
                $file_name = $theme_style . basename( $file );
                if ( file_exists( $file_name ) ) {
                     $file = $wp_filesystem->get_contents( $file_name );
                     if(empty($file)){
                         $file = file_get_contents($file_name);
                     }
                    return json_decode($file, true );
                }
            }
            // backwards compat:
            if ( is_file( __DIR__ . '/content/' . basename( $file ) ) ) {
                WP_Filesystem();
                global $wp_filesystem;
                $file_name = __DIR__ . '/content/' . basename( $file );

                if ( file_exists( $file_name ) ) {
                    return json_decode( $wp_filesystem->get_contents( $file_name ), true );
                }
            }

            return array();
        }
        
		private function _get_sql( $file ) {
			if ( is_file( __DIR__ . '/content/' . basename( $file ) ) ) {
				WP_Filesystem();
				global $wp_filesystem;
				$file_name = __DIR__ . '/content/' . basename( $file );
				if ( file_exists( $file_name ) ) {
					return $wp_filesystem->get_contents( $file_name );
				}
			}

			return false;
		}

		public function _content_setup_users(){

			//Save BuddyPress settings 
			if(function_exists('xprofile_insert_field_group')){
				$field_group=array(
					'name' => 'Instructor',
					'description' => 'Instructor only field group'
				);
				$social_field_group=array(
					'name' => 'Social Profiles',
					'description' => 'Links to social profiles'
				);
				$social_field_group_id=xprofile_insert_field_group($social_field_group);
				$group_id=xprofile_insert_field_group($field_group);
				$fields = array(
					array(
						'field_group_id'=>1,
						'type'=>'textbox',
						'name'=>'Location',
						'description'=>'Student Location'
					),
					array(
						'field_group_id'=>1,
						'type'=>'textarea',
						'name'=>'Bio',
						'description'=>'About Student'
					),
					array(
						'field_group_id'=>$group_id,
						'type'=>'textbox',
						'name'=>'Speciality',
						'description'=>'Instructor Speciality'
					),
					array(
						'field_group_id'=>$social_field_group_id,
						'type'=>'url',
						'name'=>'Facebook',
						'description'=>'Facebook profile link'
					),
					array(
						'field_group_id'=>$social_field_group_id,
						'type'=>'url',
						'name'=>'Twitter',
						'description'=>'Twitter profile link'
					),
				);

				foreach($fields as $field){
					xprofile_insert_field($field);	
				}
			}

			foreach($fields as $field){
				xprofile_insert_field($field);	
			}
			
			$users = array(
				array(
						'username'=>'wplms_lynda',
						'password'=>'lynda',
						'email'=>'vibethemes@gmail.com',
						'role'=>'instructor',
						'fields'=>array(
							'Location'=>'New York',
							'Speciality'=>'Design',
							'Bio'=>'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters English.',
							'Facebook'=>'#',
							'Twitter'=>'#',
						),
					),
				array(
						'username'=>'wplms_parker',
						'password'=>'parker',
						'email'=>'support@vibethemes.com',
						'role'=>'instructor',
						'fields'=>array(
							'Location'=>'New York',
							'Speciality'=>'Design',
							'Bio'=>'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters English.',
							'Facebook'=>'#',
							'Twitter'=>'#',
						),
					),
				array(
						'username'=>'wplms_simon',
						'password'=>'simon',
						'email'=>'sample@sample.com',
						'role'=>'instructor',
						'fields'=>array(
							'Location'=>'New York',
							'Speciality'=>'Literature',
							'Bio'=>'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters English.',
							'Facebook'=>'#',
							'Twitter'=>'#',
						),
					),
				array(
						'username'=>'wplms_leon',
						'password'=>'leon',
						'email'=>'sample@example.com',
						'role'=>'instructor',
						'fields'=>array(
							'Location'=>'New York',
							'Speciality'=>'MAths',
							'Bio'=>'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters English.',
							'Facebook'=>'#',
							'Twitter'=>'#',
						),
					),
				);

			foreach($users as $user){
				$user_id = wp_insert_user(array('user_login'=>$user['username'],'user_pass'=>$user['password'],'user_email'=>$user['email'],'role'=>$user['role']));
				if(!is_wp_error($user_id) && function_exists('xprofile_set_field_data')){
					foreach($user['fields'] as $field=>$value){
						xprofile_set_field_data($field,$user_id,$value);
					}
				}
			}
		
			return true;
		}

		public $logs = array();

		public function log( $message ) {
			$this->logs[] = $message;
		}

		public $errors = array();

		public function error( $message ) {
			$this->logs[] = 'ERROR!!!! ' . $message;
		}

		public function envato_setup_demo_style() {

			?>
            <h1><?php esc_html_e( 'Site Style' ); ?></h1>
            <form method="post">
                <p><?php esc_html_e( 'Please choose your site style below.' ); ?></p>

                <div class="theme-presets">
                    <ul>
	                    <?php

	                    $current_style = get_option('wplms_site_style');
	                    
						if(empty($current_style)){$current_style = $this->get_default_theme_style();}
	                    foreach ( $this->site_styles as $style_name => $style_data ) {
		                    ?>
                            <li<?php echo $style_name == $current_style ? ' class="current" ' : ''; ?>>
                                <a href="#" class="sitestyle" data-style="<?php echo esc_attr( $style_name ); ?>"><img
                                            src="<?php echo esc_url(get_template_directory_uri() .'/setup/installer/images/'.$style_name.'.jpg');?>"></a><a href="<?php echo $style_data['link']; ?>" target="_blank" class="link"></a>
                            </li>
	                    <?php } ?>
                    </ul>
                </div>

                <input type="hidden" name="demo_style" id="demo_style" value="<?php echo $current_style; ?>">

                <hr>
                <div class="custom_upload_block">
                <h3 class="hide_next">* OR Import your exported code from another WPLMS site. (<a href="http://vibethemes.com/documentation/wplms/knowledge-base/wplms-site-import-and-export/" title="wplms site exporter" target="_blank">?</a>)</h3>
                <div class="hide">
	                <?php wp_enqueue_script('plupload'); ?>

	                <div  class="plupload_error_notices notice notice-error is-dismissible"></div>
	                <div id="plupload-upload-ui" class="hide-if-no-js">
	                    <div id="drag-drop-area">
	                        <div class="drag-drop-inside">
	                            <p class="drag-drop-info"><?php _e('Drop files here','vibe'); ?></p>
	                            <p><?php _ex('or', 'Uploader: Drop files here - or - Select Files','vibe'); ?></p>
	                            <p class="drag-drop-buttons"><input id="plupload-browse-button" type="button" value="<?php _e('Select Files','vibe'); ?>" class="button" /></p>
	                        </div>
	                    </div>
	                </div>

	                <div class="pl_wplms_progress">
	                	<div class="warning_plupload" style="display:none;padding:15px;padding-bottom:1px;margin:10px 0;background:#d8d8d8;">
		                    <h3><?php echo __("Please do not close the window until process is completed","vibe") ?></h3>
		                </div>
	                </div>
                </div>

                <?php
                    if ( function_exists( 'ini_get' ) )
                        $post_size = ini_get('post_max_size') ;
                    $post_size = preg_replace('/[^0-9\.]/', '', $post_size);
                    $post_size = intval($post_size);
                    if($post_size != 1){
                        $post_size = $post_size-1;
                    }

                 $plupload_init = array(
                    'runtimes'            => 'html5,silverlight,flash,html4',
                    'chunk_size'          =>  (($post_size*1024) - 100).'kb',
                    'max_retries'         => 3,
                    'browse_button'       => 'plupload-browse-button',
                    'container'           => 'plupload-upload-ui',
                    'drop_element'        => 'drag-drop-area',
                    'multiple_queues'     => false,
                    'multi_selection'     => false,
                    'filters'             => array( array( 'extensions' => implode( ',', array('zip') ) ) ),
                    'url'                 => admin_url('admin-ajax.php'),
                    'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
                    'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
                    'multipart'           => true,
                    'urlstream_upload'    => true,

                    // additional post data to send to our ajax hook
                    'multipart_params'    => array(
                      '_ajax_nonce' => wp_create_nonce('wplms_exported_content_plupload'),
                      'action'      => 'wplms_exported_content_plupload'
                    ),
                  );

                $plupload_init = apply_filters('plupload_init', $plupload_init);
                
                ?>
				<script>
					jQuery(document).ready(function($){
						var temp = <?php echo json_encode($plupload_init,JSON_UNESCAPED_SLASHES); ?>;
						// create the uploader and pass the config from above
						var uploader = new plupload.Uploader(temp);
						// checks if browser supports drag and drop upload, makes some css adjustments if necessary
						uploader.bind('Init', function(up){
							var uploaddiv = $('#plupload-upload-ui');
							uploaddiv.css({'display':'block','margin-bottom':'10px'});
							if(up.features.dragdrop){
                				uploaddiv.addClass('drag-drop');
                				$('#drag-drop-area')
                					.bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
                					.bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); })
                					.css('height', 'auto');
                			}else{
                				uploaddiv.removeClass('drag-drop');
                				$('#drag-drop-area').unbind('.wp-uploader');
                			}
                		});

                		uploader.init();

                		// a file was added in the queue
                        uploader.bind('FilesAdded', function(up, files){
                            
                            var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
                            plupload.each(files, function(file){
                                if (file.size > max && up.runtime != 'html5'){
                                    console.log('call "upload_to_amazon" not sent');
                                }else{
                                     $('.pl_wplms_progress').addClass('visible');
                                    var clone = $('.pl_wplms_progress').append('<div class="'+file.id+'">'+file.name+'<i></i><strong><span></span></strong></div>');
                                    $('.pl_wplms_progress').append(clone);
                                    $('.warning_plupload').show(300);
                                }
                               
                            });

                            up.refresh();
                            up.start();
                        });

                		uploader.bind('Error', function(up, args){
                			console.log(up);
                			$('.plupload_error_notices').show();
                			$('.plupload_error_notices').html('<div class="message text-danger danger tada animate load">'+args.message+' for '+args.file.name+'</div>');
                			setTimeout(function(){
                				$('.plupload_error_notices').hide();
                			}, 5000);
                			up.refresh();
                			up.start();
                		});
                		uploader.bind('UploadProgress', function(up, file){
                			if(file.percent < 100 && file.percent >= 1){
                				$('.pl_wplms_progress div.'+file.id+' strong span').css('width', (file.percent)+'%');
                				$('.pl_wplms_progress div.'+file.id+' i').html( (file.percent)+'%');
                			}
                			up.refresh();
                			up.start();
                		});
                		// a file was uploaded
                		uploader.bind('FileUploaded', function(up, file, response) {

                            //$('.stop_s3_plupload_upload').addClass('disabled');
                             $.ajax({
                              type: "POST",
                              url: 'admin-ajax.php',
                              data: { action: 'insert_export_content_final', 
                                      security: '<?php echo wp_create_nonce("wplms_export_content_final"); ?>',
                                      name:file.name,
                                      type:file.type,
                                      size:file.origSize,
                                    },
                              cache: false,
                              success: function (html) {
                                if(html){
                                    if(html == '0'){
                                        $('.pl_wplms_progress div.'+file.id+' strong span').css('width', '0%');
                                        $('.pl_wplms_progress div.'+file.id+' strong').html("<i class='error'><?php echo __("File couldn't be unzipped properly","vibe"); ?><i>");
                                        setTimeout(function(){
                                            $('.pl_wplms_progress div.'+file.id).fadeOut(600);
                                            $('.pl_wplms_progress div.'+file.id).remove();
                                        }, 2500);
                                        $('.warning_plupload').hide(300);
                                        return false;
                                    }else{

                                        $('.pl_wplms_progress div.'+file.id+' strong span').css('width', '100%');
                                        $('.pl_wplms_progress div.'+file.id+' i').html('100%');

                                            setTimeout(function(){
                                              $('.pl_wplms_progress div.'+file.id+' strong').fadeOut(500);
                                            }, 1200);

                                            $('.pl_wplms_progress div.'+file.id).html(html);
                                            $('.pl_wplms_progress div.success.message').css({'border-left':'4px solid #a0da13','background-color':'#f4fbea','margin-bottom':'10px'});
                                            setTimeout(function(){
                                                if($('.pl_wplms_progress strong').length < 1){
                                                    $('.warning_plupload').hide(300);
                                                }
											}, 1750);
                                    }
                                }

                              }
                            });
                        });

                	});
                </script>
                <?php
                ?>

                <p class="envato-setup-actions step">
                    <input type="submit" class="button-primary button button-large button-next"
                           value="<?php _e( 'Continue','vibe' ); ?>" name="save_step"/>
                    <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
                       class="button button-large button-next"><?php esc_html_e( 'Skip this step' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
                </p>
                <p><em>Please Note: Advanced changes to website graphics/colors may require extensive PhotoShop and Web
                        Development knowledge. We recommend hiring an expert from <a
                                href="http://studio.envato.com/"
                                target="_blank">Envato Studio</a> to assist with any advanced website changes.</em></p>
            </form>
			<?php
		}

		/**
		 * Save logo & design options
		 */
		public function envato_setup_demo_style_save($demo=null) {
			if(empty($demo)){
				check_admin_referer( 'envato-setup' );
			}

			$demo_style = isset( $_POST['demo_style'] ) ? $_POST['demo_style'] : false;
			if(!empty($demo)){
				$demo_style=$demo;
			}
			if ( $demo_style ) {
				update_option( 'wplms_site_style', $demo_style );
			}
			if(empty($demo)){
				wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
				exit;
			}
			
		}

		/**
		 * Logo & Design
		 */
		public function envato_setup_design() {
			/*Delete option for uploaded content to avoid conflicts when setup wizard runs again*/
			delete_option( 'wplms_export_import_content_path' );

			?>
			<h1><?php esc_html_e( 'Design and Layouts' ); ?></h1>
			<form method="post">
				<p><?php printf( esc_html__( 'Please add your logo below. For best results, the logo should be a transparent PNG ( 466 by 277 pixels). The logo can be changed at any time from the Appearance > Customize area in your dashboard. Try %sEnvato Studio%s if you need a new logo designed.' ), '<a href="http://studiotracking.envato.com/aff_c?offer_id=4&aff_id=1564&source=DemoInstall" target="_blank">', '</a>' ); ?></p>

				<table>
					<tr>
						<td>
							LOGO
						</td>
						<td>
							<div id="current-logo">
								<?php
								$image_url = vibe_get_option('logo');
								if(empty($image_url)){
									$image_url = VIBE_URL.'/assets/images/logo.png';
								}
								
								if ( $image_url ) {
									$image = '<img class="site-logo" src="%s" alt="%s" style="width:%s; height:auto" />';
									printf(
										$image,
										$image_url,
										get_bloginfo( 'name' ),
										$this->get_header_logo_width()
									);
								} ?>
							</div>
							<input type="hidden" name="logo_url" id="logo_url" value="<?php echo $image_url; ?>">
						</td>
						<td>
							<a href="#" class="button button-upload" data-title="Upload a logo" data-text="select a logo" data-target="#current-logo img" data-save="#logo_url"><?php esc_html_e( 'Upload New Logo' ); ?></a>
						</td>
					</tr>
					<tr>
						<td>
							Theme Skin
						</td>
						<?php
							$theme_skin = vibe_get_customizer('theme_skin');
						?>
						<td>
							<select name="theme_skin">
								<option value="" <?php echo (empty($theme_skin)?'selected':''); ?>>Default</option>
								<option value="minimal"  <?php echo (($theme_skin == 'minimal')?'selected':''); ?>>Minimal</option>
								<option value="elegant" <?php echo (($theme_skin == 'elegant')?'selected':''); ?>>Elegant</option>
								<option value="modern" <?php echo (($theme_skin == 'modern')?'selected':''); ?>>Modern</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							Primary color
							<?php 
							$primary_bg=vibe_get_customizer('primary_bg');
							if(Empty($primary_bg)){$primary_bg= '#009dd8';}
							?>
						</td>
						<td>
							<input id="primary_bg" class="jscolor {hash:true}" name="primary_bg" type="text" value="<?php echo $primary_bg; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Primary text color
							<?php 
							$primary_color=vibe_get_customizer('primary_color');
							if(Empty($primary_color)){$primary_color= '#ffffff';}
							?>
						</td>
						<td>
							<input id="primary_color" class="jscolor {hash:true}" name="primary_color" type="text" value="#ffffff" />
						</td>
					</tr>
				</table>
				<br>
				<hr>
				<p><em>Please Note: Advanced changes to website graphics/colors may require extensive PhotoShop and Web
						Development knowledge. We recommend hiring an expert from <a
							href=""
							target="_blank">Envato Studio</a> to assist with any advanced website changes.</em></p>
				</div>

				<p class="envato-setup-actions step">
					<input type="submit" class="button-primary button button-large button-next"
					       value="<?php _e( 'Continue','vibe' ); ?>" name="save_step"/>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button button-large button-next"><?php esc_html_e( 'Skip this step' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</p>
			</form>
			<?php
		}

		/**
		 * Save logo & design options
		 */
		public function envato_setup_design_save($theme=null) {
			if(empty($theme)){
				check_admin_referer( 'envato-setup' );
			}

			$logo_url = $_POST['logo_url'];
			vibe_update_option('logo',$logo_url);

			$theme_skin =  isset( $_POST['theme_skin'] ) ? $_POST['theme_skin'] : false;
			if(!empty($theme)){
				$theme_skin = $theme;
			}
			if ( $theme_skin ) {
				vibe_update_customizer('theme_skin',$theme_skin);
				if(function_exists('wplms_get_theme_color_config')){
					$new_option = wplms_get_theme_color_config($theme_skin);
					$option = get_option('vibe_customizer');
					if(!empty($new_option)){
				        foreach($new_option as $k=>$v){
				            $option[$k] = $v;
				        }
				    }
				    update_option('vibe_customizer',$option);
				}
			}
			$primary_bg = isset( $_POST['primary_bg'] ) ? $_POST['primary_bg'] : false;
			if ( $primary_bg ) {
				vibe_update_customizer('primary_bg',$primary_bg);
			}

			$primary_color = isset( $_POST['primary_color'] ) ? $_POST['primary_color'] : false;
			if ( $primary_color ) {
				vibe_update_customizer('primary_color',$primary_color);
			}
			if(empty($theme)){
				wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
				exit;
			}
		}
		
		/**
		 * Final step
		 */
		public function envato_setup_ready() {

			update_option( 'envato_setup_complete', time() );
			?>
			<a href="https://twitter.com/share" class="twitter-share-button"
			   data-url="http://themeforest.net/user/vibethemes/portfolio?ref=vibethemes"
			   data-text="<?php echo esc_attr( 'I just installed the ' . wp_get_theme() . ' #WordPress theme from #ThemeForest' ); ?>"
			   data-via="EnvatoMarket" data-size="large">Tweet</a>
			<script>!function (d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (!d.getElementById(id)) {
						js = d.createElement(s);
						js.id = id;
						js.src = "//platform.twitter.com/widgets.js";
						fjs.parentNode.insertBefore(js, fjs);
					}
				}(document, "script", "twitter-wjs");</script>

			<h1><?php esc_html_e( 'Your Website is Ready!' ); ?></h1>

			<p>Congratulations! The theme has been activated and your website is ready. Login to your WordPress
				dashboard to make changes and modify any of the default content to suit your needs.</p>
			<p>Please come back and <a href="http://themeforest.net/downloads" target="_blank">leave a 5-star rating</a>
				if you are happy with this theme. <br/>Follow <a href="https://twitter.com/vibethemes" target="_blank">@vibethemes</a>
				on Twitter to see updates. Thanks! </p>
			<?php flush_rewrite_rules(); ?>
			<div class="envato-setup-next-steps">
				<div class="envato-setup-next-steps-first">
					<h2><?php esc_html_e( 'Next Steps' ); ?></h2>
					<ul>
						<li class="setup-product"><a class="button button-primary button-large"
						                             href="https://twitter.com/vibethemes"
						                             target="_blank"><?php esc_html_e( 'Follow @vibethemes on Twitter' ); ?></a>
						</li>
						<li class="setup-product"><a class="button button-next button-large"
						                             href="<?php echo esc_url( home_url() ); ?>"><?php esc_html_e( 'View your new website!' ); ?></a>
						</li>
					</ul>
				</div>
				<div class="envato-setup-next-steps-last">
					<h2><?php esc_html_e( 'More Resources' ); ?></h2>
					<ul>
						<li class="documentation"><a href="http://vibethemes.com/envato/documentation/"
						                             target="_blank"><?php esc_html_e( 'Read the Theme Documentation' ); ?></a>
						</li>
						<li class="howto"><a href="https://wordpress.org/support/"
						                     target="_blank"><?php esc_html_e( 'Learn how to use WordPress' ); ?></a>
						</li>
						<li class="rating"><a href="http://themeforest.net/downloads"
						                      target="_blank"><?php esc_html_e( 'Leave an Item Rating' ); ?></a></li>
						<li class="support"><a href="http://vibethemes.com/documentation/wplms/"
						                       target="_blank"><?php esc_html_e( 'Get Help and Support' ); ?></a></li>
					</ul>
				</div>
			</div>
			<?php
		}

		public function envato_market_admin_init() {

			if ( ! function_exists( 'envato_market' ) ) {
				return;
			}

			global $wp_settings_sections;
			if ( ! isset( $wp_settings_sections[ envato_market()->get_slug() ] ) ) {
				// means we're running the admin_init hook before envato market gets to setup settings area.
				// good - this means our oauth prompt will appear first in the list of settings blocks
				register_setting( envato_market()->get_slug(), envato_market()->get_option_name() );
			}

			// pull our custom options across to envato.
			$option         = get_option( 'envato_setup_wizard', array() );
			$envato_options = envato_market()->get_options();
			$envato_options = $this->_array_merge_recursive_distinct( $envato_options, $option );
			update_option( envato_market()->get_option_name(), $envato_options );

			//add_thickbox();

			if ( ! empty( $_POST['oauth_session'] ) && ! empty( $_POST['bounce_nonce'] ) && wp_verify_nonce( $_POST['bounce_nonce'], 'envato_oauth_bounce_' . $this->envato_username ) ) {
				// request the token from our bounce url.
				$my_theme    = wp_get_theme();
				$oauth_nonce = get_option( 'envato_oauth_' . $this->envato_username );
				if ( ! $oauth_nonce ) {
					// this is our 'private key' that is used to request a token from our api bounce server.
					// only hosts with this key are allowed to request a token and a refresh token
					// the first time this key is used, it is set and locked on the server.
					$oauth_nonce = wp_create_nonce( 'envato_oauth_nonce_' . $this->envato_username );
					update_option( 'envato_oauth_' . $this->envato_username, $oauth_nonce );
				}
				$response = wp_remote_post( $this->oauth_script, array(
						'method'      => 'POST',
						'timeout'     => 15,
						'redirection' => 1,
						'httpversion' => '1.0',
						'blocking'    => true,
						'headers'     => array(),
						'body'        => array(
							'oauth_session' => $_POST['oauth_session'],
							'oauth_nonce'   => $oauth_nonce,
							'get_token'     => 'yes',
							'url'           => home_url(),
							'theme'         => $my_theme->get( 'Name' ),
							'version'       => $my_theme->get( 'Version' ),
						),
						'cookies'     => array(),
					)
				);
				if ( is_wp_error( $response ) ) {
					$error_message = $response->get_error_message();
					$class         = 'error';
					echo "<div class=\"$class\"><p>" . sprintf( esc_html__( 'Something went wrong while trying to retrieve oauth token: %s' ), $error_message ) . '</p></div>';
				} else {
					$token  = @json_decode( wp_remote_retrieve_body( $response ), true );
					$result = false;
					if ( is_array( $token ) && ! empty( $token['access_token'] ) ) {
						$token['oauth_session'] = $_POST['oauth_session'];
						$result                 = $this->_manage_oauth_token( $token );
					}
					if ( $result !== true ) {
						echo 'Failed to get oAuth token. Please go back and try again';
						exit;
					}
				}
			}

			add_settings_section(
				envato_market()->get_option_name() . '_' . $this->envato_username . '_oauth_login',
				sprintf( esc_html__( 'Login for %s updates' ), $this->envato_username ),
				array( $this, 'render_oauth_login_description_callback' ),
				envato_market()->get_slug()
			);
			// Items setting.
			add_settings_field(
				$this->envato_username . 'oauth_keys',
				esc_html__( 'oAuth Login' ),
				array( $this, 'render_oauth_login_fields_callback' ),
				envato_market()->get_slug(),
				envato_market()->get_option_name() . '_' . $this->envato_username . '_oauth_login'
			);
		}

		private static $_current_manage_token = false;

		private function _manage_oauth_token( $token ) {
			if ( is_array( $token ) && ! empty( $token['access_token'] ) ) {
				if ( self::$_current_manage_token == $token['access_token'] ) {
					return false; // stop loops when refresh auth fails.
				}
				self::$_current_manage_token = $token['access_token'];
				// yes! we have an access token. store this in our options so we can get a list of items using it.
				$option = get_option( 'envato_setup_wizard', array() );
				if ( ! is_array( $option ) ) {
					$option = array();
				}
				if ( empty( $option['items'] ) ) {
					$option['items'] = array();
				}
				// check if token is expired.
				if ( empty( $token['expires'] ) ) {
					$token['expires'] = time() + 3600;
				}
				if ( $token['expires'] < time() + 120 && ! empty( $token['oauth_session'] ) ) {
					// time to renew this token!
					$my_theme    = wp_get_theme();
					$oauth_nonce = get_option( 'envato_oauth_' . $this->envato_username );
					$response    = wp_remote_post( $this->oauth_script, array(
							'method'      => 'POST',
							'timeout'     => 10,
							'redirection' => 1,
							'httpversion' => '1.0',
							'blocking'    => true,
							'headers'     => array(),
							'body'        => array(
								'oauth_session' => $token['oauth_session'],
								'oauth_nonce'   => $oauth_nonce,
								'refresh_token' => 'yes',
								'url'           => home_url(),
								'theme'         => $my_theme->get( 'Name' ),
								'version'       => $my_theme->get( 'Version' ),
							),
							'cookies'     => array(),
						)
					);
					if ( is_wp_error( $response ) ) {
						$error_message = $response->get_error_message();
						echo "Something went wrong while trying to retrieve oauth token: $error_message";
					} else {
						$new_token = @json_decode( wp_remote_retrieve_body( $response ), true );
						$result    = false;
						if ( is_array( $new_token ) && ! empty( $new_token['new_token'] ) ) {
							$token['access_token'] = $new_token['new_token'];
							$token['expires']      = time() + 3600;
						}
					}
				}
				// use this token to get a list of purchased items
				// add this to our items array.
				$response                    = envato_market()->api()->request( 'https://api.envato.com/v3/market/buyer/purchases', array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $token['access_token'],
					),
				) );
				self::$_current_manage_token = false;
				if ( is_array( $response ) && is_array( $response['purchases'] ) ) {
					// up to here, add to items array
					foreach ( $response['purchases'] as $purchase ) {
						// check if this item already exists in the items array.
						$exists = false;
						foreach ( $option['items'] as $id => $item ) {
							if ( ! empty( $item['id'] ) && $item['id'] == $purchase['item']['id'] ) {
								$exists = true;
								// update token.
								$option['items'][ $id ]['token']      = $token['access_token'];
								$option['items'][ $id ]['token_data'] = $token;
								$option['items'][ $id ]['oauth']      = $this->envato_username;
								if ( ! empty( $purchase['code'] ) ) {
									$option['items'][ $id ]['purchase_code'] = $purchase['code'];
								}
							}
						}
						if ( ! $exists ) {
							$option['items'][] = array(
								'id'            => '' . $purchase['item']['id'],
								// item id needs to be a string for market download to work correctly.
								'name'          => $purchase['item']['name'],
								'token'         => $token['access_token'],
								'token_data'    => $token,
								'oauth'         => $this->envato_username,
								'type'          => ! empty( $purchase['item']['wordpress_theme_metadata'] ) ? 'theme' : 'plugin',
								'purchase_code' => ! empty( $purchase['code'] ) ? $purchase['code'] : '',
							);
						}
					}
				} else {
					return false;
				}
				if ( ! isset( $option['oauth'] ) ) {
					$option['oauth'] = array();
				}
				// store our 1 hour long token here. we can refresh this token when it comes time to use it again (i.e. during an update)
				$option['oauth'][ $this->envato_username ] = $token;
				update_option( 'envato_setup_wizard', $option );

				$envato_options = envato_market()->get_options();
				$envato_options = $this->_array_merge_recursive_distinct( $envato_options, $option );
				update_option( envato_market()->get_option_name(), $envato_options );
				envato_market()->items()->set_themes( true );
				envato_market()->items()->set_plugins( true );

				return true;
			} else {
				return false;
			}
		}

		/**
		 * @param $array1
		 * @param $array2
		 *
		 * @return mixed
		 *
		 *
		 * @since    1.1.4
		 */
		private function _array_merge_recursive_distinct( $array1, $array2 ) {
			$merged = $array1;
			foreach ( $array2 as $key => &$value ) {
				if ( is_array( $value ) && isset( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
					$merged [ $key ] = $this->_array_merge_recursive_distinct( $merged [ $key ], $value );
				} else {
					$merged [ $key ] = $value;
				}
			}

			return $merged;
		}

		/**
		 * @param $args
		 * @param $url
		 *
		 * @return mixed
		 *
		 * Filter the WordPress HTTP call args.
		 * We do this to find any queries that are using an expired token from an oAuth bounce login.
		 * Since these oAuth tokens only last 1 hour we have to hit up our server again for a refresh of that token before using it on the Envato API.
		 * Hacky, but only way to do it.
		 */
		public function envato_market_http_request_args( $args, $url ) {
			if ( strpos( $url, 'api.envato.com' ) && function_exists( 'envato_market' ) ) {
				// we have an API request.
				// check if it's using an expired token.
				if ( ! empty( $args['headers']['Authorization'] ) ) {
					$token = str_replace( 'Bearer ', '', $args['headers']['Authorization'] );
					if ( $token ) {
						// check our options for a list of active oauth tokens and see if one matches, for this envato username.
						$option = envato_market()->get_options();
						if ( $option && ! empty( $option['oauth'][ $this->envato_username ] ) && $option['oauth'][ $this->envato_username ]['access_token'] == $token && $option['oauth'][ $this->envato_username ]['expires'] < time() + 120 ) {
							// we've found an expired token for this oauth user!
							// time to hit up our bounce server for a refresh of this token and update associated data.
							$this->_manage_oauth_token( $option['oauth'][ $this->envato_username ] );
							$updated_option = envato_market()->get_options();
							if ( $updated_option && ! empty( $updated_option['oauth'][ $this->envato_username ]['access_token'] ) ) {
								// hopefully this means we have an updated access token to deal with.
								$args['headers']['Authorization'] = 'Bearer ' . $updated_option['oauth'][ $this->envato_username ]['access_token'];
							}
						}
					}
				}
			}

			return $args;
		}

		public function render_oauth_login_description_callback() {
			echo 'If you have purchased items from ' . esc_html( $this->envato_username ) . ' on ThemeForest or CodeCanyon please login here for quick and easy updates.';

		}

		public function render_oauth_login_fields_callback() {
			$option = envato_market()->get_options();
			?>
			<div class="oauth-login" data-username="<?php echo esc_attr( $this->envato_username ); ?>">
				<a href="<?php echo esc_url( $this->get_oauth_login_url( admin_url( 'admin.php?page=' . envato_market()->get_slug() . '#settings' ) ) ); ?>"
				   class="oauth-login-button button button-primary">Login with Envato to activate updates</a>
			</div>
			<?php
		}

		/// a better filter would be on the post-option get filter for the items array.
		// we can update the token there.

		public function get_oauth_login_url( $return ) {
			return $this->oauth_script . '?bounce_nonce=' . wp_create_nonce( 'envato_oauth_bounce_' . $this->envato_username ) . '&wp_return=' . urlencode( $return );
		}

		/**
		 * Helper function
		 * Take a path and return it clean
		 *
		 * @param string $path
		 *
		 * @since    1.1.2
		 */
		public static function cleanFilePath( $path ) {
			$path = str_replace( '', '', str_replace( array( '\\', '\\\\', '//' ), '/', $path ) );
			if ( $path[ strlen( $path ) - 1 ] === '/' ) {
				$path = rtrim( $path, '/' );
			}

			return $path;
		}

		public function is_submenu_page() {
			return ( $this->parent_slug == '' ) ? false : true;
		}

		function wplms_exported_content_plupload(){

			check_ajax_referer('wplms_exported_content_plupload');
			if( !is_user_logged_in() )
				die('user not logged in');

			if( empty($_FILES) || $_FILES['file']['error'] )
				die('{"OK": 0, "info": "Failed to move uploaded file."}');

			$chunk 	  = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
			$chunks   = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
			$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];

			$upload_dir_base = wp_upload_dir();
			$folderPath = $upload_dir_base['basedir'].'/upload_demos/';
			if ( function_exists('is_dir') && !is_dir( $folderPath ) ) {
				if( function_exists('mkdir') ){
					mkdir($folderPath, 0755, true) || chmod($folderPath, 0755);
				}
			}
			$filePath = $folderPath."/$fileName";

			if($chunk == 0)
				$perm = "wb";
			else
				$perm = "ab";

			$out = @fopen("{$filePath}.part",$perm );
			if($out){
				// Read binary input stream and append it to temp file
				$in = @fopen($_FILES['file']['tmp_name'], "rb");
				if($in){
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				}else{
					die('{"OK": 0, "info": "Failed to open input stream."}');
				}
				@fclose($in);
				@fclose($out);
				@unlink($_FILES['file']['tmp_name']);
			}else{
				die('{"OK": 0, "info": "Failed to open output stream."}');
			}

			// Check if file has been uploaded
			if( !$chunks || $chunk == $chunks - 1 ){
				// Strip the temp .part suffix off
				rename("{$filePath}.part", $filePath);
			}

			die('{"OK": 1, "info": "Upload successful."}');
			exit;
		}

		function insert_export_content_final(){
			if( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_export_content_final') || !is_user_logged_in() ){
				wp_die( __('Security check failed contact administrator','vibe') );
				die();
			}

			$filename 		 = $_POST['name'];
			$upload_dir_base = wp_upload_dir();
			$folderPath 	 = $upload_dir_base['basedir'].'/upload_demos';
			$filePath = $folderPath.'/'.$filename;

			$zip = new ZipArchive;
			$response = $zip->open( $filePath );
			if( $response ){
				$zip->extractTo($folderPath);
				$zip->close();

				//Update option for importing content from uploads folder
				$temp_folder_path = $folderPath.'/'.basename($filePath,'.zip');
				update_option( 'wplms_export_import_content_path', $temp_folder_path );

				//Delete file after uploading
				unlink($filePath);
				echo '<div class="success message">'.__('File uploaded and unzipped successfully','vibe').'<div>';
			}else{
				echo '0';
			}

			die();
		}
	}

}// if !class_exists

/**
 * Loads the main instance of Envato_Theme_Setup_Wizard to have
 * ability extend class functionality
 *
 * @since 1.1.1
 * @return object Envato_Theme_Setup_Wizard
 */
add_action( 'after_setup_theme', 'envato_theme_setup_wizard', 10 );
if ( ! function_exists( 'envato_theme_setup_wizard' ) ) :
	function envato_theme_setup_wizard() {
		Envato_Theme_Setup_Wizard::get_instance();
	}
endif;

add_filter('wplms_theme_setup_wizard_username', 'wplms_set_theme_setup_wizard_username', 10);
if( ! function_exists('wplms_set_theme_setup_wizard_username') ){
    function wplms_set_theme_setup_wizard_username($username){
        return 'vibethemes';
    }
}

add_filter('envato_setup_logo_image','wplms_envato_setup_logo_image');
function wplms_envato_setup_logo_image($old_image_url){
	return get_template_directory_uri().'/assets/images/wplms.png';
}
