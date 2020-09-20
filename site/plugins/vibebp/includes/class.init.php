<?php
/**
 * Initialise plugin
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



class VibeBP_Init{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Init();
        return self::$instance;
    }

	private function __construct(){

		add_action('init',array($this,'record_bp_setup_nav'));
		
		add_filter('vibebp_vars',array($this,'add_login_api'),99);
		add_action( 'widgets_init', array($this,'dashboard_sidebar'));

		add_filter('bp_core_avatar_full_width',array($this,'avatar_full_width'));
		add_filter('bp_core_avatar_full_height',array($this,'avatar_full_height'));
		add_filter('bp_core_avatar_thumb_width',array($this,'avatar_thumb_width'));
		add_filter('bp_core_avatar_thumb_height',array($this,'avatar_thumb_height'));

		add_filter('vibebp_elementor_filters',array($this,'bp_profile'));
	}

	function record_bp_setup_nav(){

		if(is_user_logged_in() && bp_is_my_profile() && current_user_can('manage_options')){
			$bp_rest_api_nav = get_transient('bp_rest_api_nav');
			$reload_nav = get_option('vibebp_reload_nav');
			if(empty($bp_rest_api_nav) || !empty($reload_nav) || !empty($_GET['reload_nav'])){

				$nav =[];

			    global $bp;
			    $bpnav = new ReflectionObject($bp->members->nav);
			    $property = $bpnav->getProperty('nav');
			    $property->setAccessible(true);
			    
		    	foreach($property->getValue($bp->members->nav) as $members_nav){
		    	
			        foreach($members_nav as $key=>$obj){

			        	if(!is_callable($obj)){
			        		$item = (Array)($obj);
				        	$item['class']=array('menu-child');
				        	if(!empty($item['parent_slug'])){
				        		$item['parent'] = $item['parent_slug'];
				        	}
				        	$item['name'] = apply_filters('vibebp_force_apply_translations',translate($item['name'],'vibebp'),$item);
				        	
				            $nav[]=$item;
			        	}
		        			
			        	
			        }
			    }

			    set_transient('bp_rest_api_nav',$nav,0);
			    update_option('vibebp_reload_nav',false);
			}
		}
	}

	function dashboard_sidebar() {

	    register_sidebar( array(
	        'name' => __( 'VibeBP Member Dashboard', 'vibebp' ),
	        'id' => 'vibebp-dashboard',
	        'description' => __( 'Widgets appear in Dashboard', 'vibebp' ),
	        'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>',
	    ) );

	}
	
	function avatar_full_width($width){
		$this->get_settings();
		if(!empty($this->settings['bp']['bp_avatar_full_width'])){
			return $this->settings['bp']['bp_avatar_full_width'];
		}

		return 300;
	}
	function avatar_full_height($height){
		$this->get_settings();
		if(!empty($this->settings['bp']['bp_avatar_full_height'])){
			return $this->settings['bp']['bp_avatar_full_height'];
		}

		return 300;
	}
	function avatar_thumb_width($width){
		$this->get_settings();
		if(!empty($this->settings['bp']['bp_avatar_thumb_width'])){
			return $this->settings['bp']['bp_avatar_thumb_width'];
		}

		return 150;
	}
	function avatar_thumb_height($height){
		$this->get_settings();
		if(!empty($this->settings['bp']['bp_avatar_thumb_height'])){
			return $this->settings['bp']['bp_avatar_thumb_height'];
		}

		return 150;
	}

	function get_settings(){
		if(empty($this->settings)){
			$this->settings = get_option(VIBE_BP_SETTINGS);
		}

		return $this->settings;
	}
	function add_login_api($vars){

		if(empty($vars['api'])){
			$vars['api']=[];
		}
		
		$vars['api']['api_security']=vibebp_get_api_security();
		$vars['api']['generate_token']= get_rest_url('',VIBEBP_NAMESPACE.'/'. VIBEBP_TOKEN .'/generate-token/');
		$vars['api']['validate_token']=get_rest_url('',VIBEBP_NAMESPACE. '/'. VIBEBP_TOKEN .'/validate-token/');;

		return $vars;
	}

	function get_security(){


		if(empty($this->security)){
			$this->security = get_transient('vibebp_api_security');
			if(empty($this->security)){
				$this->security = wp_generate_password(8,false,false);
				set_transient('vibebp_api_security',$this->security,24*HOUR_IN_SECONDS);
			}
		}
		return $this->security;
	}
	
	function install(){
		
		add_rewrite_rule('vibebpsw.js','/wp-admin/admin-ajax.php?action=vibebp-sw', 'top');
		flush_rewrite_rules();
	}


	function get_profile_link(){
		$this->get_settings();

		$slug = '';
		if(empty($this->settings['general']) || empty($this->settings['general']['profile']) ){
			$slug = 'profile';
		}else{
			$slug = $this->settings['general']['profile'];
		}
		return home_url().'/'.$slug;
	}

	function bp_page_id($page){
        if(empty($this->bp_pages)){
            $this->bp_pages = get_option('bp-pages');
        }
        if(function_exists('icl_object_id')){
            $this->bp_pages[$page] = icl_object_id($this->bp_pages[$page], 'page', true);
        }

        if(isset($this->bp_pages[$page])){
        	return $this->bp_pages[$page];
        }else{
        	return home_url().'/'.$page;
        }
        
    }

    function get_setting($field,$type = 'general',$sub=null){
    	$this->get_settings();
    	
    	if(!empty($sub)){
    		if(!empty($this->settings[$type][$sub][$field])){
    			return $this->settings[$type][$sub][$field];
    		}
    	}else if(!empty( $field)){
    		return  isset($this->settings[$type][$field])?$this->settings[$type][$field]:'';
    	}else if(!empty($type)){
    		return $this->settings[$type];
    	}
    	return false;
    }

    function bp_profile($args){

    	if(bp_displayed_user_id()){
    		$args['author']=bp_displayed_user_id();
    	}

    	return $args;
    }

}
VibeBP_Init::init();

function vibebp_get_api_security(){
	$init = VibeBP_Init::init();;
	return $init->get_security();
}

function vibebp_get_profile_link($user_nicename){
	if(is_numeric($user_nicename)){
		$user = get_userdata($user_nicename);
		$user_nicename = $user->user_nicename;
	}
	return esc_url(get_permalink(vibe_get_bp_page_id('members')).$user_nicename);
}

if(!function_exists('vibe_get_bp_page_id')){
    function vibe_get_bp_page_id($page){
        $init = VibeBP_Init::init();  
        return $init->bp_page_id($page);
    }
}

function vibebp_get_setting($field,$type='general',$sub=null){
	$init = VibeBP_Init::init();

	return $init->get_setting($field,$type,$sub);
}

function vibebp_member_class(){
	
}	

function vibebp_api_get_user_from_token($token){
	
	/** Get the Secret Key */
    $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
    if (!$secret_key) {
      	return false;
    }
    try {
        $user_data = JWT::decode($token, $secret_key, array('HS256'));

    	return $user_data->data->user;

    }catch (Exception $e) {
        /** Something is wrong trying to decode the token, send back the error */
        return false;
    }

}


function vibebp_get_pwa_scripts($force=null){
	$vibe_scripts = array(
			    	'vibecal','vibeforms','tabulator','helpdesk','vibekb','vibe-projects
			','vibedrive','vibedrive_group','wplms-course-component-js','localforage','vibebplogin',
			'wplms_dash_text','wplms_course_progress','wplms_dash_activity','wplms_dash_text','contact_users',
			'wplms_todo_task','wplms_dashboard_student_stats','wplms_dashboard_simple_stats','wplms_dashboard_notes_discussions','wplms_dashboard_mymodules','wplms_dashboard_instructor_simple_stats', 'wplms_dashboard_news','wplms_dashboard_instructor_stats',
			'wplms_dashboard_instructor_commissions','wplms_dashboard_instructor_announcements','wplms_dash_instructing_modules','wplms_instructor_students_widget','firebase','firebase-auth','firebase-database','firebase-messaging','vibebp_live','flatpickr','colorpickr','plyr','tus','vibebpprofile','vibe_editor'
	    ); 
	global $wp_scripts;
	if(vibebp_get_setting('service_workers')){
		$links = vibebp_get_setting('pre-cached','service_worker');	
		if(!empty($links)){
			
			
			foreach($wp_scripts->registered as $key=>$script){
				$script = (Array)$script;
				if(!empty($script['src']) && in_array($script['src'],$links)){
					$vibe_scripts[]=$key;
				}
			}
		}
	}
	
	$vibe_scripts = apply_filters('vibebp_sw_preache_scripts',$vibe_scripts);
	$allscripts=array();
	
	if($force && !empty($vibe_scripts)){
		foreach($wp_scripts->registered as $key=>$script){
			$script = (Array)$script;
			
			if(!empty($script['src']) && in_Array($key,$vibe_scripts)){
				$allscripts[]=$script['src'];
			}
		}
		$allscripts[]= includes_url('js/dist/element.min.js');
		$allscripts[]= includes_url('js/dist/data.min.js');
		$allscripts[]= includes_url('js/dist/redux-routine.min.js');
		$allscripts[]= includes_url('js/dist/hooks.min.js');
		$allscripts[]= includes_url('js/dist/vendor/react-dom.min.js');
		$allscripts[]= includes_url('js/dist/vendor/react.min.js');
		$allscripts[]= includes_url('js/dist/vendor/lodash.min.js');
		$allscripts[]= includes_url('js/dist/vendor/wp-polyfill.min.js');
		$allscripts[]= includes_url('js/dist/escape-html.min.js');
		$allscripts[]= includes_url('js/dist/compose.min.js');
		$allscripts[]= includes_url('/js/dist/deprecated.min.js');
		$allscripts[]= includes_url('js/dist/priority-queue.min.js');
		update_option('vibe_sw_scripts',$allscripts); 
	}

	//set transient
	return $vibe_scripts;
	    
}


function vibebp_get_pwa_styles($force=null){
	$vibe_styles = array(
	    	'bp-member-block','bp-group-block','vibebp-frontend','vibecal','vibeforms_profile_css','tabulator','helpdesk','vibekb','vicons','vibe-projects','vibedrive_profile_css','wplms-cc', 'wplms_dashboard_css', 'vibebp_main', 'vibebp_profile_libs', 'plyr', 'vibe_editor');
	
	global $wp_styles;
	if(vibebp_get_setting('service_workers')){
		$links = vibebp_get_setting('pre-cached','service_worker');	
		if(!empty($links) && is_Array($links)){
			
			foreach($wp_styles->registered as $key=>$style){
				$style = (Array)$style;

				if(!empty($style['src']) && in_array($style['src'],$links)){
					$vibe_styles[]=$key;
				}
			}
		}
	}
	$vibe_styles = apply_filters('vibebp_sw_precache_styles',$vibe_styles);
	$allstyles = array();
	if($force && !empty($vibe_styles)){
		foreach($wp_styles->registered as $key=>$style){
			$style = (Array)$style;
			if(!empty($style['src']) && in_Array($key,$vibe_styles)){
				$allstyles[]=$style['src'];
			}
		}
		
		update_option('vibe_sw_styles',$allstyles);
	}

	return $vibe_styles;
}