<?php
/**
 * Admin Menu 
 *
 * @class       vibebp_Menu
 * @author      VibeThemes
 * @category    Admin
 * @package     vibebp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VibeBP_Menu{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Menu();
        return self::$instance;
    }

	private function __construct(){

		add_action( 'admin_menu', array($this,'register_menu_page'),11 );
		add_action('admin_init',array($this,'setup_wizard'));
	}

	function register_menu_page(){


		add_menu_page( _x('Vibe BP','title','vibebp'), 
			_x('Vibe BP','menu title','vibebp'), 'manage_options', 
			'vibebp', array($this,'dashboard'),'dashicons-buddicons-groups',100 );
		$vibebp_settings = VibeBP_Settings::init();
	    add_submenu_page( 'vibebp', __('Settings','vibebp'), __('Settings','vibebp'),  'manage_options', 'vibebp_settings', array($vibebp_settings ,'vibebp_settings'));
	    add_submenu_page( 'vibebp', __('Add Ons','vibebp'), __('Add Ons','vibebp'),  'manage_options', 'vibebp_addons', array($this,'addons'));
	}

	function dashboard(){
		echo 'This is Awesome Dashboard';
	}


	function get_addons(){
		$this->addons = apply_filters('vibebp_addons',array(
			'vibe-calendar' =>array(
				'label'=> __('Calendar','vibebp'),
				'sub'=> __('Events & Calendar.','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'',
				'link' => '',
				'extra'=>array('Calendar','Events', 'Location map'),
				'activated'=> (is_plugin_active('vibe-calendar/loader.php')?true:false),
				'price'=>0,
				'included'=>1,
				'tag'=>array('label'=>'INCLUDED','class'=>'included'),
			),
			'vibedrive' =>array(
				'label'=> __('Drive','vibebp'),
				'sub'=> __('Drive for Members.','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'',
				'link' => '',
				'extra'=>array('Space per Member','Upload attachments','Share Docs'),
				'activated'=> (is_plugin_active('vibe-calendar/loader.php')?true:false),
				'price'=>0,
				'included'=>1,
				'tag'=>array('label'=>'INCLUDED','class'=>'included'),
			),
			'vibe-kb' =>array(
				'label'=> __('Knowledge Base','vibebp'),
				'sub'=> __('Knowledge Base & Articles, Editor and sharing','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'',
				'link' => '',
				'extra'=>array('Articles','Sharing','Roles'),
				'activated'=> (is_plugin_active('vibe-kb/loader.php')?true:false),
				'price'=>0,
				'included'=>1,
				'tag'=>array('label'=>'INCLUDED','class'=>'included'),
				'class'=>'featured'
			),
			'vibe-helpdesk' =>array(
				'label'=> __('HelpDesk','vibebp'),
				'sub'=> __('Convert BBPress into a Ticketing Solution.','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'',
				'link' => '',
				'extra'=>array('Tickets','Agents', 'SLA'),
				'activated'=> (is_plugin_active('vibe-helpdesk/loader.php')?true:false),
				'price'=>0,
				'included'=>1,
				'tag'=>array('label'=>'COMING SOON','class'=>'coming_soon'),
			),
			'vibe-projects' =>array(
				'label'=> __('Projects','vibebp'),
				'sub'=> __('Project Management Solution.','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'',
				'link' => '',
				'extra'=>array('Projects','KanBan Boards/Grid/Calendar', 'Cards'),
				'activated'=> (is_plugin_active('vibe-projects/loader.php')?true:false),
				'price'=>0,
				'included'=>1,
				'tag'=>array('label'=>'COMING SOON','class'=>'coming_soon'),
			),
			'wpappointify' =>array(
				'label'=> __('Appointments','vibebp'),
				'sub'=> __('Appointments Calendar.','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'',
				'link' => '',
				'extra'=>array('Booking','Video Conferencing', 'Payments'),
				'activated'=> (is_plugin_active('wpappointify/loader.php')?true:false),
				'price'=>0,
				'included'=>1,
				'tag'=>array('label'=>'COMING SOON','class'=>'coming_soon'),
			),
		));
	}
	function addons(){
		$this->get_addons();
		
		?>
		<div class="vibebp_addons">
		<?php
		foreach($this->addons as $key=>$addon){ 
			if(!empty($addon) && !empty($addon['label'])){

				$class = apply_filters('vibebp_addon_class','',$addon);

				?>
					<div class="vibebp_addon_block">
						<div class="inside <?php echo $class.' '.(($addon['activated'])?'active':''); ?>">
							<span class="<?php echo $addon['tag']['class']; ?>"><?php echo $addon['tag']['label']; ?></span>
							<h3 class=""><?php echo $addon['label']; ?><span><?php echo $addon['sub']; ?></span></h3>
							<?php 
							if(!empty($addon['extra'])){
								if(is_array($addon['extra'])){
									echo '<ul>';
									foreach($addon['extra'] as $ex){
										echo '<li>'.$ex.'</li>';
									}
									echo '</ul>';
								}else{
									echo $addon['extra'];
								}
							}
							if(!empty($addon['license_key']) && $addon['activated']){
								$val = get_option($addon['license_key']);
								?>
								<div class="activate_license">
	                                <form action="<?php  echo admin_url( 'admin.php?page=lms-settings&tab=live'); ?>" method="post">
	                                    <input type="text" id="<?php echo $addon['license_key']; ?>" name="license_key" class="vibe_license_key" value="<?php echo $val ?>" placeholder="<?php _e('Enter License Key','vibe-customtypes'); ?>" />
	                                    <?php 
	                                    if(!empty($val) && strpos($class,'invalid') === false){    ?>
	                                    <input type="submit" class="button primary" name="<?php echo $addon['license_key']; ?>" value="Deactivate" />
	                                    <?php
	                                    }else{
	                                        ?>
	                                    <input type="submit" class="button primary" name="<?php echo $addon['license_key']; ?>" value="Activate" />
	                                    <?php
	                                    }
	                                    wp_nonce_field( $key, $key);
	                                    ?>
	                                </form>
	                            </div>
								<a target="_blank" class="button button-primary activate_license_toggle"><?php _e('License Key','vibe-customtypes'); ?></a>
								<?php
							}

							if(empty($addon['included'])){
							?>
							<a href="<?php echo $addon['link']; ?>" target="_blank" class="button"><?php _e('Learn more','vibe-customtypes'); ?></a>
							<?php
							}
							?>
						</div>
					</div>
			<?php
				}
			}
			?>
			</div>
			<div class="clear">	</div>
			<?php

	}


	function setup_wizard(){
		$option = get_option('vibebp_version');
		if(empty($option)){
			add_action('admin_notices',array($this,'init_setupwizard'));
		}
		//VIBEBP_VERSION
	}

	function init_setupwizard(){

		if(vibebp_is_setup_complete())
			return;

		wp_enqueue_script('vibebp_setup',plugins_url('../assets/js/vibebp_setup.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION,true);
		wp_enqueue_style('vibebp_setup',plugins_url('../assets/css/backend.css',__FILE__),array(),VIBEBP_VERSION);
		$color = '#666666';

		$blog_id = '';
        if(function_exists('get_current_blog_id')){
            $blog_id = get_current_blog_id();
        }
        $security = get_transient('vibebp_admin_security');
        if(empty($security)){
        	$security = wp_generate_password(12,false,false);
        	set_transient('vibebp_admin_security',$security,60*60);
        }

        
		 wp_localize_script('vibebp_setup','vibebp_setup',apply_filterS('vibebp_setup_wizard',array(
		 		'security'=> $security,
		 		'api'=>Array(
		 			'url'=> get_rest_url($blog_id,Vibe_BP_API_NAMESPACE),
		 			'admin_id'=>get_current_user_id()
		 		),
		 		'installation'=>array(
		 			'title'=>__('VibeBP Framework','vibebp'),
		 			'description'=>__('Welcome to the react social network for your site. Get started by installing the required and recommended plugins.','vibebp'),
		 			'plugins'=>array(
		 				array(
		 					'plugin'=>'buddypress',
		 					'label' => __('BuddyPress','vibebp'),
		 					'required'=> 1,
		 					'status'=>function_exists('buddypress')?2:(file_exists(plugin_dir_path(__FILE__).'../../buddypress/loader.php')?1:0)
		 				),
		 				array(
		 					'plugin'=>'elementor',
		 					'label' => __('Elementor','vibebp'),
		 					'required'=> 1,
		 					'status'=>function_exists('elementor_load_plugin_textdomain')?2:(file_exists(plugin_dir_path(__FILE__).'../../elementor/elementor.php')?1:0)
		 				),
		 				array(
		 					'plugin'=>'bbpress',
		 					'label' => __('BBPress','vibebp'),
		 					'required'=> 0,
		 					'status'=>class_exists('bbpress')?2:(file_exists(plugin_dir_path(__FILE__).'../../bbpress/bbpress.php')?1:0)
		 				),
		 				array(
		 					'plugin'=>'wplms',
		 					'label' => __('WPLMS Learning Management System','vibebp'),
		 					'required'=> 0,
		 					'status'=>function_exists('wplms_plugin_load_translations')?2:(file_exists(plugin_dir_path(__FILE__).'../../wplms_plugin/loader.php')?1:0)
		 				),
		 			),
		 		
			 		'steps'=>array(
			 			array(
			 				'key'=>'features',
			 				'label'=>_x('Features','installation step','vibebp'),
			 				'description'=>_x('Select features you want to enable in your site. This feature set comes from the plugins you have selected in the previous step of required and recommended plugin.
			 					<span>To restart the setup wizard, just click outside the setup wizard box and restart it.</span> ','installation step','vibebp'),
			 				'features'=>array(
			 					array(
			 						'type'=>'core',
			 						'key' => 'xprofile',
			 						'icon'=> '<svg width="100%" height="100%" viewBox="0 0 100 100" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path d="M82.304,76.271C81.217,73.829 79.017,72.142 74.508,71.1C64.954,68.896 56.058,66.963 60.371,58.829C73.475,34.071 63.842,20.833 50,20.833C35.883,20.833 26.483,34.579 39.629,58.829C44.071,67.013 34.846,68.942 25.492,71.1C20.975,72.142 18.792,73.842 17.713,76.292C10.734,97.682 86.948,100.491 82.304,76.271Z" style="fill-opacity:0.69;fill-rule:nonzero;"/><path d="M50,0C22.388,0 0,22.388 0,50C0,77.613 22.388,100 50,100C77.613,100 100,77.613 100,50C100,22.388 77.613,0 50,0ZM91.667,50C92.967,105.886 9.586,104.221 8.333,50C7.802,27.031 27.025,8.333 50,8.333C72.975,8.333 91.132,27.031 91.667,50Z" style="fill-rule:nonzero;"/></svg>',
			 						'label'=>__('Profile Fields','vibebp'),
			 						'required'=>1,
			 						'is_active'=>bp_is_active('xprofile')
			 					),
			 					array(
			 						'type'=>'core',
			 						'key' => 'activity',
			 						'label'=>__('Activity','vibebp'),
			 						'required'=>1,
			 						'icon'=>'<svg width="100" height="100" viewBox="0 0 24 24" version="1.1" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><g transform="matrix(1,0,0,1,1,1)"><path d="M10.043,18.153C9.752,16.952 8.781,12.625 8.781,12.625C8.595,12.857 8.31,13 8,13L5,13C4.448,13 4,12.552 4,12C4,11.448 4.448,11 5,11L7.279,11L8.051,8.684C8.205,8.223 8.572,8.001 8.937,8.001C9.339,8.001 9.74,8.27 9.854,8.783C10.071,9.763 10.812,13.187 10.812,13.187C10.812,13.187 11.889,5.958 12.041,4.862C12.12,4.295 12.567,4.003 13.014,4.003C13.431,4.003 13.848,4.258 13.966,4.782L15.215,11.38C15.401,11.145 15.688,11 16,11L19,11C19.552,11 20,11.448 20,12C20,12.552 19.552,13 19,13L16.721,13L15.949,15.316C15.632,16.266 14.469,16.343 14.188,15.218C13.895,14.045 13.094,10.218 13.094,10.218C13.094,10.218 12.132,17.004 11.979,18.103C11.899,18.676 11.455,18.992 11.007,18.992C10.596,18.993 10.182,18.728 10.043,18.153Z" style="fill-opacity:0.3;fill-rule:nonzero;"/></g><path d="M10.043,18.153C9.752,16.952 8.781,12.625 8.781,12.625C8.595,12.857 8.31,13 8,13L5,13C4.448,13 4,12.552 4,12C4,11.448 4.448,11 5,11L7.279,11L8.051,8.684C8.205,8.223 8.572,8.001 8.937,8.001C9.339,8.001 9.74,8.27 9.854,8.783C10.071,9.763 10.812,13.187 10.812,13.187C10.812,13.187 11.889,5.958 12.041,4.862C12.12,4.295 12.567,4.003 13.014,4.003C13.431,4.003 13.848,4.258 13.966,4.782L15.215,11.38C15.401,11.145 15.688,11 16,11L19,11C19.552,11 20,11.448 20,12C20,12.552 19.552,13 19,13L16.721,13L15.949,15.316C15.632,16.266 14.469,16.343 14.188,15.218C13.895,14.045 13.094,10.218 13.094,10.218C13.094,10.218 12.132,17.004 11.979,18.103C11.899,18.676 11.455,18.992 11.007,18.992C10.596,18.993 10.182,18.728 10.043,18.153Z" style="fill-rule:nonzero;"/></svg>',
			 						'is_active'=>bp_is_active('activity')
			 					),
			 					array(
			 						'type'=>'core',
			 						'key' => 'messages',
			 						'label'=>__('Messaging','vibebp'),
			 						'recommended'=>1,
			 						'icon'=>'<svg width="100" height="100" viewBox="0 0 24 24" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path d="M24,0L17.673,6.527L7.215,13.754L0,12L24,0ZM9,16.668L9,24L12.258,19.569L9,16.668Z" style="fill-rule:nonzero;"/><path d="M24,0L18,22L9.871,14.761L17.673,6.527L24,0Z" style="fill-opacity:0.67;fill-rule:nonzero;"/></svg>',
			 						'is_active'=>bp_is_active('messages')
			 					),
			 					array(
			 						'type'=>'core',
			 						'key' => 'notifications',
			 						'label'=>__('Notifications','vibebp'),
			 						'recommended'=>1,
			 						'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24"><path  d="M15.137 3.945c-.644-.374-1.042-1.07-1.041-1.82v-.003c.001-1.172-.938-2.122-2.096-2.122s-2.097.95-2.097 2.122v.003c.001.751-.396 1.446-1.041 1.82-4.667 2.712-1.985 11.715-6.862 13.306v1.749h20v-1.749c-4.877-1.591-2.195-10.594-6.863-13.306zm-3.137-2.945c.552 0 1 .449 1 1 0 .552-.448 1-1 1s-1-.448-1-1c0-.551.448-1 1-1zm3 20c0 1.598-1.392 3-2.971 3s-3.029-1.402-3.029-3h6z"/></svg>',
			 						'is_active'=>bp_is_active('notifications')
			 					),
			 					array(
			 						'type'=>'core',
			 						'key' => 'friends',
			 						'label'=>__('Friends','vibebp'),
			 						'recommended'=>1,
			 						'icon'=>'<svg width="100%" height="100%" viewBox="0 0 100 100" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><g transform="matrix(4.16667,0,0,4.16667,2.46099e-15,0)"><path d="M12.683,10L11.398,6.667L21.028,6.667C21.625,6.667 22.149,7.063 22.312,7.637L23.971,13.493L24,13.703C24,14.016 23.809,14.305 23.505,14.423L23.504,14.424C23.144,14.563 22.737,14.416 22.551,14.077L20.835,10.971C20.835,10.971 20.472,19.801 20.343,22.921C20.317,23.525 19.823,24 19.22,24L19.219,24C18.628,24 18.144,23.541 18.099,22.953C17.997,21.656 17.725,18.553 17.643,17.325C17.609,16.815 17.236,16.515 16.831,16.515C16.468,16.515 16.06,16.815 16.027,17.325C15.944,18.553 15.672,21.656 15.571,22.953C15.525,23.541 15.041,24 14.451,24L14.449,24C13.847,24 13.352,23.525 13.327,22.921C13.197,19.801 12.683,10 12.683,10ZM16.835,0C15.179,0 13.835,1.344 13.835,3C13.835,4.656 15.179,6 16.835,6C18.49,6 19.835,4.656 19.835,3C19.835,1.344 18.49,0 16.835,0Z" style="fill-opacity:0.61;"/></g><g transform="matrix(4.16667,0,0,4.16667,2.46099e-15,0)"><path d="M4.781,24L4.78,24C4.177,24 3.683,23.525 3.657,22.921C3.528,19.801 3.165,10.971 3.165,10.971L1.449,14.077C1.263,14.416 0.856,14.563 0.496,14.424L0.495,14.423C0.191,14.305 0,14.016 0,13.703L0.029,13.493L1.688,7.637C1.851,7.063 2.375,6.667 2.972,6.667L12.43,6.667L11.335,10C11.335,10 10.803,19.801 10.673,22.921C10.648,23.525 10.153,24 9.551,24L9.549,24C8.959,24 8.475,23.541 8.429,22.953C8.328,21.656 8.056,18.553 7.973,17.325C7.94,16.815 7.532,16.515 7.169,16.515C6.764,16.515 6.391,16.815 6.357,17.325C6.275,18.553 6.003,21.656 5.901,22.953C5.856,23.541 5.372,24 4.781,24ZM7.165,0C8.821,0 10.165,1.344 10.165,3C10.165,4.656 8.821,6 7.165,6C5.51,6 4.165,4.656 4.165,3C4.165,1.344 5.51,0 7.165,0Z"/></g></svg>',
			 						'is_active'=>bp_is_active('friends')
			 					),
			 					array(
			 						'type'=>'core',
			 						'key' => 'groups',
			 						'label'=>__('Groups','vibebp'),
			 						'recommended'=>1,
			 						'icon'=>'<svg width="100" height="100" viewBox="0 0 24 24" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path d="M22.808,15.786C21.518,15.488 20.318,15.227 20.899,14.129C22.668,10.787 21.368,9 19.499,9C18.234,9 17.251,9.817 17.251,11.324C17.251,15.227 19.519,13.094 19.497,18L23.998,18L24,17.537C24,16.591 23.926,16.044 22.808,15.786ZM0.002,18L4.503,18C4.482,13.094 6.749,15.228 6.749,11.324C6.749,9.817 5.766,9 4.501,9C2.632,9 1.332,10.787 3.102,14.129C3.683,15.228 2.483,15.488 1.193,15.786C0.074,16.044 0,16.591 0,17.537L0.002,18Z" style="fill-opacity:0.71;fill-rule:nonzero;"/><path d="M17.997,18L6.002,18L6,17.377C6,16.118 6.1,15.391 7.588,15.047C9.272,14.658 10.932,14.311 10.133,12.838C7.767,8.475 9.459,6 11.999,6C14.49,6 16.225,8.383 13.865,12.839C13.09,14.303 14.691,14.651 16.41,15.048C17.9,15.392 17.999,16.12 17.999,17.381L17.997,18Z" style="fill-rule:nonzero;"/></svg>',
			 						'is_active'=>bp_is_active('groups')
			 					),
			 					array(
			 						'type'=>'vibebp',
			 						'key' => 'followers',
			 						'label'=>__('Followers','vibebp'),
			 						'recommended'=>1,
			 						'icon'=>'<svg width="100%" height="100%" viewBox="0 0 100 100" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path  serif:id=" " d="M81.25,62.5C70.904,62.5 62.5,70.896 62.5,81.25C62.5,91.604 70.904,100 81.25,100C91.596,100 100,91.604 100,81.25C100,70.896 91.596,62.5 81.25,62.5ZM91.667,83.333L83.333,83.333L83.333,91.667L79.167,91.667L79.167,83.333L70.833,83.333L70.833,79.167L79.167,79.167L79.167,70.833L83.333,70.833L83.333,79.167L91.667,79.167L91.667,83.333Z" style="fill-opacity:0.62;fill-rule:nonzero;"/><path serif:id=" " d="M61.75,100L0.021,100L0,94.829C0,84.329 0.829,78.267 13.242,75.4C27.263,72.163 41.108,69.263 34.45,56.992C14.729,20.621 28.825,0 50,0C78.129,0 81.275,31.646 65.167,56.996C59.783,65.458 54.167,72.121 54.167,81.25C54.167,88.529 57.067,95.129 61.75,100Z" style="fill-rule:nonzero;"/></svg>',
			 						'is_active'=>(vibebp_get_setting('followers','bp') == 'on')?true:false
			 					),
			 					array(
			 						'type'=>'vibebp',
			 						'key' => 'likes',
			 						'label'=>__('Likes','vibebp'),
			 						'recommended'=>1,
			 						'icon'=>'<svg width="100%" height="100%" viewBox="0 0 100 100"  style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path d="M94.229,56.617C90.658,56.138 91.821,53.558 94.358,52.775C96.529,52.108 100,50.692 100,46.483C100,43.683 97.917,39.975 90.537,40.275C85.421,40.483 75.262,39.45 69.792,36.588C73.567,21.354 72.658,0 62.758,0C56.117,0 54.858,7.529 52.862,14.454C47.775,32.129 39.062,39.525 29.167,43.142L29.167,88.467C47.425,91.388 55.604,100 72.937,100C86.262,100 93.154,92.771 93.154,88.892C93.154,87.496 92.021,86.504 89.154,86.283C85.775,86.025 86.096,82.9 89.283,82.313C94.567,81.337 96.892,78.504 96.892,75.883C96.892,73.679 95.242,71.625 92.317,70.963C88.829,70.175 89.55,67.808 92.446,67.579C97.167,67.204 99.479,64.396 99.479,61.704C99.479,59.35 97.712,57.083 94.229,56.617Z" style="fill-opacity:0.73;fill-rule:nonzero;"/><rect x="0" y="41.667" width="20.833" height="50" style="fill-rule:nonzero;"/></svg>',
			 						'is_active'=>(vibebp_get_setting('likes','bp') == 'on')?true:false
			 					),
			 				)
			 			),
						array(
							'key'=>'content',
			 				'label'=>_x('Content','installation step','vibebp'),
			 				'description'=>_x('Configure default content, layouts and menus for profiles','installation step','vibebp'),
			 				'layouts'=>array(
			 					array(
			 						'key'=>'profile',
			 						'type'=>'checkbox',
			 						'label'=>_x('Install default profile layout & xprofile fields','installation','vibebp'),
			 					),
			 					array(
			 						'key'=>'menus',
			 						'type'=>'checkbox',
			 						'label'=>_x('Set default Profile & Loggedin menu','installation','vibebp'),
			 					),
			 					array(
			 						'key'=>'group',
			 						'type'=>'checkbox',
			 						'label'=>_x('Install default Group Layout','installation','vibebp'),
			 					),
			 					array(
			 						'key'=>'members_directory',
			 						'type'=>'checkbox',
			 						'label'=>_x('Member Directory & Member Types','installation','vibebp'),
			 					),
			 					array(
			 						'key'=>'groups_directory',
			 						'type'=>'checkbox',
			 						'label'=>_x('Group Directory & Group Types','installation','vibebp'),
			 					),
			 				)
						),
						array(
							'key'=>'access',
			 				'label'=>_x('Accessibility','installation step','vibebp'),
			 				'description'=>_x('Accessibility of various modules. If enabled, accessibility to world and search engines. If disabled then only accessible to you and members of your site.','installation step','vibebp'),
			 				'access'=>array(
			 					array(
			 						'key'=>'public_profile',
			 						'type'=>'checkbox',
			 						'label'=>_x('Disable Public User Profiles','installation','vibebp'),
			 					),
			 					array(
			 						'key'=>'public_member_directory',
			 						'type'=>'checkbox',
			 						'label'=>_x('Disable Public Member Directories','vibebp'),
			 					),
			 					array(
			 						'key'=>'public_group_directory',
			 						'type'=>'checkbox',
			 						'label'=>_x('Disbale Public Groups & Group Directories','vibebp'),
			 					),
			 					array(
			 						'key'=>'public_activity',
			 						'type'=>'checkbox',
			 						'label'=>_x('Disbale Public Activities (recommended disable)','installation','vibebp'),
			 					),
			 				)
						),
			 		),
		 		),
                'translations'=>array(
                	
                    'configure_vibebp'=>__('Configure VibeBP, the Modern social network framework built on BuddyPress.', 'vibebp'),
                    'setup_wizard'=>__('Setup Wizard', 'vibebp'),
                    'required'=>__('Required','vibebp'),
                    'recommended'=>__('Recommended','vibebp'),
                    'installed'=>__('Plugin Active','vibebp'),
                    'activate_plugin'=>__('Activate','vibebp'),
                    'install_plugin'=>__('Download','vibebp'),
                    'begin_setup'=>__('Begin Setup','vibebp'),
                    'next_step'=>__('Next Step','vibebp'),

                )
            ))
	 	);
		?>
		<div id="vibebp_setup_wizard"></div>
		<?php
	}
}

VibeBP_Menu::init();