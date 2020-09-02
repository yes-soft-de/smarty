<?php
/**
 * Installation related functions and actions.
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	Setup Install
 * @version     1.8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'WPLMS_Install' ) ) :

/**
 * WPLMS_Install Class
 */
class WPLMS_Install {

	public $version = WPLMS_VERSION;
	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action('after_switch_theme', array( $this, 'install' ) , 10 , 2);
		// Hooks
		add_action( 'admin_init', array( $this, 'check_version' ), 5 );
		
		add_action( 'in_theme_update_message-'. THEME_SHORT_NAME, array( $this, 'wplms_update_message' ) );
		
		add_action('wplms_before_sample_data_import',array($this,'wplms_install_plugins'),10,1);
		add_action('wplms_after_sample_data_import',array($this,'wplms_setup_settings'),20,1);
		add_action('wplms_after_sample_data_import',array($this,'vibe_import_sample_slider'),20,1);
		add_action('wplms_after_sample_data_import',array($this,'wplms_flush_permalinks'),100);

		add_filter( 'theme_action_links_' . THEME_SHORT_NAME, array( $this, 'theme_action_links' ) );
		add_filter( 'theme_row_meta', array( $this, 'theme_row_meta' ), 10, 3 );

		add_action( 'wp_ajax_import_sample_data',array($this,'import_sample_data'));

		add_action('admin_menu',array($this,'vibe_remove_default_import'),1);
		add_action('layerslider_installed',array($this,'vibe_layerslider_remove_setup_fonts'));	
	}

	/**
	 * check_version function.
	 *
	 * @access public
	 * @return void
	 */
	public function check_version() {
		$wplms_version=get_option( 'wplms_version' );
		if (empty($wplms_version) || $wplms_version != $this->version ) {
			$this->install();
			do_action( 'wplms_updated' );
		}
	}


	/**
	 * Install WPLMS
	 */
	public function install() {
		// Queue upgrades
		$current_version    = get_option( 'wplms_version', null );
		// Update version
		if($current_version != $this->version || !isset($current_version)){
			update_option( 'wplms_version', $this->version );
			flush_rewrite_rules();
			set_transient( '_wplms_activation_redirect', 1, HOUR_IN_SECONDS );
		}
	}


	/**
	 * Show Theme changes. Code adapted from W3 Total Cache.
	 *
	 * @return void
	 */
	function wplms_update_message( $args ) {
		$transient_name = 'wplms_upgrade_notice_' . $args['Version'];

		if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {

			$response = wp_remote_get( 'https://s3.amazonaws.com/WPLMS/readme.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {

				// Output Upgrade Notice
				$matches        = null;
				$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( WC_VERSION ) . '\s*=|$)~Uis';
				$upgrade_notice = '';

				if ( preg_match( $regexp, $response['body'], $matches ) ) {
					$version        = trim( $matches[1] );
					$notices        = (array) preg_split('~[\r\n]+~', trim( $matches[2] ) );

					if ( version_compare( WC_VERSION, $version, '<' ) ) {

						$upgrade_notice .= '<div class="wplms_plugin_upgrade_notice">';

						foreach ( $notices as $index => $line ) {
							$upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line ) );
						}

						$upgrade_notice .= '</div> ';
					}
				}

				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		echo wp_kses_post( $upgrade_notice );
	}
	/**
	 * Show action links on the plugin screen.
	 *
	 * @access	public
	 * @param	mixed $links Plugin Action links
	 * @return	array
	 */
	public function theme_action_links( $links ) {
		$action_links = array(
			'settings'	=>	'<a href="' . admin_url( 'admin.php?page=wplms_options' ) . '" title="' . esc_attr( __( 'View WPLMS Options panel', 'vibe' ) ) . '">' . __( 'Options panel', 'vibe' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @access	public
	 * @param	mixed $links Plugin Row Meta
	 * @param	mixed $file  Plugin Base file
	 * @return	array
	 */
	public function theme_row_meta( $links, $file,$theme ) {
		if ( $theme ==  THEME_SHORT_NAME) {
			$row_meta = array(
				'docs'		=>	'<a href="' . esc_url( apply_filters( 'wplms_docs_url', 'http://vibethemes.com/envato/wplms/documentation/' ) ) . '" title="' . esc_attr( __( 'View WPLMS Documentation', 'vibe' ) ) . '">' . __( 'Docs', 'vibe' ) . '</a>',
				'support'	=>	'<a href="' . esc_url( apply_filters( 'wplms_support_url', 'http://vibethemes.com/forums/forum/wordpress-html-css/wordpress-themes/wplms/' ) ) . '" title="' . esc_attr( __( 'Visit Premium Customer Support Forum', 'vibe' ) ) . '">' . __( 'Support Forum', 'vibe' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	function import_sample_data(){
		$file = stripslashes($_POST['file']);
		
	    include 'vibe_importer/vibeimport.php';
	    vibe_import($file);
	    die();
	}

	function wplms_install_plugins($file){
		if(!is_plugin_active('vibe-customtypes/vibe-customtypes.php') || !is_plugin_active('buddypress/bp-loader.php')){
			_e('Please activate all the required plugins','vibe');
			die();
		}

		//Before installation
		$single_catalog_image_sizes = array(
				'width' => 262,
				'height'=> 999,
				'crop'=>0
				);
		update_option('shop_catalog_image_sizes',$single_catalog_image_sizes);
		$single_product_image_sizes = array(
			'width' => 460,
			'height'=> 999,
			'crop'=>0
			);
		update_option('shop_single_image_size',$single_product_image_sizes);
	}

	function wplms_setup_settings($file){
		global $wpdb;
		//flush_rewrite_rules();
		//Set important settings
		update_option('permalink_structure','/%postname%/');
		update_option('membership_active','yes');
		update_option('require_name_email','');
		update_option('comment_moderation','');
		update_option('comment_whitelist','');
		update_option('posts_per_page',6);
		update_option('comments_per_page',5);
		update_option('users_can_register',1);
		update_option('default_role','student');
		$bp_active_components = apply_filters('wplms_setup_bp_components',array(
			'xprofile' => 1,
			'settings' => 1,
			'friends' => 1,
			'messages' => 1,
			'activity' => 1,
			'notifications' => 1,
			'members' => 1 
			));
		update_option('bp-active-components',$bp_active_components);
		

		$options_pages = array(
			'take_course_page'=>'course-status',
			'create_course' => 'edit-course',
			'certificate_page' => 'default-certificate-template'
			);

		$bp_pages=apply_filters('wplms_setup_bp_pages',array(
			'activity' => 'activity',
			'members' => 'members',
			'course' => 'all-courses',
			'register' => 'register',
			'activate' => 'activate'
			));

		$options_panel = array(
			'last_tab' => 10,
			'header_fix' => 1,
			'course_search' => 0,
			'loop_number' => 5,
       		'take_course_page' => 268 ,
       		'create_course' => 2087 ,
	       	'instructor_add_students' => 1 ,
	       	'instructor_assign_badges' => 1 ,
	       	'instructor_extend_subscription' => 1 ,
       	   	'certificate_page' => 1063,
        	'course_duration_display_parameter' => 86400,
        	'google_fonts' => Array ( '0' => 'Roboto', '1' => 'Raleway' ),
           	'top_footer_columns' => 'col-md-3 col-sm-6',
            'bottom_footer_columns' => 'col-md-3 col-sm-6',
		);
		foreach($options_pages as $key=>$page){
			$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", "{$page}" ) );	

			if(isset($page_id) && is_numeric($page_id)){
				$options_panel[$key]=$page_id;
			}else{
				unset($options_panel[$key]);
			}
		}
		$options_panel = apply_filters('wplms_setup_options_panel',$options_panel);
		update_option(THEME_SHORT_NAME,$options_panel);
		foreach($bp_pages as $key=>$page){
			$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", "{$page}" ) );	
			if(isset($page_id) && is_numeric($page_id)){
				$bp_pages[$key] = $page_id;
			}else{
				unset($bp_pages[$key]);
			}
		}
		update_option('bp-pages',$bp_pages);

		$permalinks = array(
			'course_base' => '/course',
			'quiz_base'=>'/quiz',
			'unit_base'=>'/unit',
			'curriculum_slug'=>'/curriculum',
			'members_slug'=>'/members',
			'activity_slug'=>'/activity',
			'admin_slug'=>'/admin',
			'submissions_slug' => '/submissions',
			'stats_slug' => '/stats'
		);
		
		update_option('vibe_course_permalinks',$permalinks);
		/*==================================================*/
		/* WIDGETS AND SIDEBARS
		/*==================================================*/
		
		if($file == 'sampledata'){
			$sidebars_file = apply_filters('wplms_setup_sidebars_file',VIBE_PATH.'/setup/data/sidebars.txt');

			if(file_exists($sidebars_file)){
				$myfile = fopen($sidebars_file , "r") or die("Unable to open file!".$sidebars_file );
				while(!feof($myfile)) {
					$string = fgets($myfile);
				}
				fclose($myfile);
		        $code = base64_decode(trim($string)); 
		        if(is_string($code)){
		            $code = unserialize($code);
		            if(is_array($code)){
		            	update_option('sidebars_widgets',$code);
		            }
		        }
			}
			//=================
			$widgets_file = apply_filters('wplms_setup_widgets_file',VIBE_PATH.'/setup/data/widgets.txt');
			if(file_exists($widgets_file)){
				$myfile = fopen($widgets_file , "r") or die("Unable to open file!".$widgets_file );
				while(!feof($myfile)) {
					$string = fgets($myfile);
				}
				fclose($myfile);
		        $code = base64_decode(trim($string)); 
		        if(is_string($code)){
		            $code = unserialize($code);
		            if(is_array($code)){
		            	foreach($code as $key=>$option){
		            		update_option($key,$option);
		            	}
		            }
		        }
			}
			// Setup Homepage
			$page = 'home';
			$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", "{$page}" ) );	
			if(isset($page_id) && is_numeric($page_id)){
				update_option('show_on_front','page');
				update_option('page_on_front',$page_id);
			}
			// Setup Menus
			$wplms_menus = array(
				'top-menu'=>1,
				'main-menu'=>1,
				'mobile-menu'=>1,
				'footer-menu'=>1,
			);
			// End HomePage setup
			//Set Menus to Locations
			$vibe_menus  = wp_get_nav_menus();
			if(!empty($vibe_menus) && !empty($wplms_menus)){ // Check if menus are imported
				//Grab Menu values
				foreach($wplms_menus as $key=>$menu_item){
					$term_id = $wpdb->get_var( $wpdb->prepare( "SELECT term_id FROM {$wpdb->terms} WHERE slug = %s LIMIT 1;", "{$key}" ) );	
					if(isset($term_id) && is_numeric($term_id)){
						$wplms_menus[$key]=$term_id;
					}else{
						unset($wplms_menus[$key]);
					}
				}
				//update the theme
				set_theme_mod( 'nav_menu_locations', $wplms_menus);
			}
			//End Menu setup
			
			// Set WooCommerce Pages
			$pages=array(
				'cart'=>'woocommerce_cart_page_id',
				'checkout'=>'woocommerce_checkout_page_id',
				'myaccount' => 'woocommerce_myaccount_page_id',
				'shop' => 'woocommerce_shop_page_id'
				);
			foreach($pages as $page => $option_name){
				$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", "{$page}" ) );	
				if(isset($page_id) && is_numeric($page_id)){
					update_option($option_name,$page_id);
				}	
			}
			
			//Set WooCommerce options

			delete_option( '_wc_needs_pages' );
			delete_option( '_wc_needs_update' );
			delete_transient( '_wc_activation_redirect' );
			// End WooCommerce setup
			
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
			
			// Import Sample Slider
			$this->vibe_import_sample_slider();
		}
	}

	function vibe_remove_default_import(){
		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'import_sample') { 	
			remove_action(	'admin_init' , 'layerslider_import_sample_slider');
			add_action(		'admin_init' , array($this,'vibe_import_sample_slider'));
		}
	}
	function vibe_import_sample_slider() {
		$sample_file = apply_filters('wplms_setup_layerslider_file',VIBE_PATH.'/setup/data/sample_sliders.txt');
		
		if(!file_exists($sample_file))
			return;

		$sample_slider = json_decode(base64_decode(file_get_contents($sample_file)), true);
		foreach($sample_slider as $sliderkey => $slider) {
			foreach($sample_slider[$sliderkey]['layers'] as $layerkey => $layer) {
				if(!empty($sample_slider[$sliderkey]['layers'][$layerkey]['properties']['background'])) {
					$sample_slider[$sliderkey]['layers'][$layerkey]['properties']['background'] = VIBE_URL.'/setup/data/uploads/'.basename($layer['properties']['background']);
				}
				if(!empty($sample_slider[$sliderkey]['layers'][$layerkey]['properties']['thumbnail'])) {
					$sample_slider[$sliderkey]['layers'][$layerkey]['properties']['thumbnail'] = VIBE_URL.'/setup/data/uploads/'.basename($layer['properties']['thumbnail']);
				}
				if(isset($layer['sublayers']) && !empty($layer['sublayers'])) {
					foreach($layer['sublayers'] as $sublayerkey => $sublayer) {
						if($sublayer['type'] == 'img') {
							$sample_slider[$sliderkey]['layers'][$layerkey]['sublayers'][$sublayerkey]['image'] = VIBE_URL.'/setup/data/uploads/'.basename($sublayer['image']);
						}
					}
				}
			}
		}
	 
		global $wpdb;
		$table_name = $wpdb->prefix . "layerslider";
		foreach($sample_slider as $key => $val) {
			$wpdb->query(
				$wpdb->prepare("INSERT INTO $table_name
									(name, data, date_c, date_m)
								VALUES (%s, %s, %d, %d)",
								$val['properties']['title'],
								json_encode($val),
								time(),
								time()
				)
			);
		}
	}
	function vibe_find_layersliders($names_only = false){
	    global $wpdb;
	    // Table name
	    $table_name = $wpdb->prefix . "layerslider";
	 
	    // Get sliders
	    $sliders = $wpdb->get_results( "SELECT * FROM $table_name WHERE flag_hidden = '0' AND flag_deleted = '0' ORDER BY date_c ASC LIMIT 100" );
	 	
	 	if(empty($sliders)) return;
	 	
	 	if($names_only)
	 	{
	 		$new = array();
	 		foreach($sliders as $key => $item) 
		    {
		    	if(empty($item->name)) $item->name = __("(Unnamed Slider)","vibe");
		       $new[$item->name] = $item->id;
		    }
		    
		    return $new;
	 	}
	 	
	 	return $sliders;
	}
	function vibe_layerslider_remove_setup_fonts(){
		update_option('ls-google-fonts', array());
	}
	function wplms_flush_permalinks(){
		update_option('medium_size_w',460);
	}
}

endif;

new WPLMS_Install();

include_once 'welcome.php';
