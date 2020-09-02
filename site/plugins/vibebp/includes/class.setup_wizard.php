<?php
/**
 * Setup Wizard
 *
 * @class       VibeBP_setUp Wizard
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VibeBP_SetupWizard{


    public static $instance;
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_SetupWizard();
        return self::$instance;
    }

    private function __construct(){
    	add_action('rest_api_init',array($this,'rest_api'));
		

		add_action('vibebp_activate_feature_xprofile',array($this,'enable_xprofile'));
		add_action('vibebp_activate_feature_activity',array($this,'enable_activity'));
		add_action('vibebp_activate_feature_messages',array($this,'enable_messages'));
		add_action('vibebp_activate_feature_notifications',array($this,'enable_notifications'));
		add_action('vibebp_activate_feature_friends',array($this,'enable_friends'));
		add_action('vibebp_activate_feature_groups',array($this,'enable_groups'));
		add_action('vibebp_activate_feature_followers',array($this,'enable_followers'));
		add_action('vibebp_activate_feature_likes',array($this,'enable_likes'));
		
		add_action('vibebp_import_layout_profile',array($this,'import_default_xprofile'));
		add_action('vibebp_import_layout_menus',array($this,'import_default_menu'));
		add_action('vibebp_import_layout_group',array($this,'import_group_layout'));
		add_action('vibebp_import_layout_members_directory',array($this,'import_members_directory'));
		add_action('vibebp_import_layout_groups_directory',array($this,'import_groups_directory'));
	}


	function rest_api(){


        register_rest_route( Vibe_BP_API_NAMESPACE, '/setup_wizard/', array(
            array(
                'methods'             => 'POST',
                'callback'            =>  array( $this, 'setup_wizard' ),
                'permission_callback' => array( $this, 'admin_permissions_check' ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/complete_wizard/', array(
            array(
                'methods'             => 'POST',
                'callback'            =>  array( $this, 'complete_wizard' ),
                'permission_callback' => array( $this, 'admin_permissions_check' ),
            ),
        ));
    }


    function admin_permissions_check($request){
    	
        
    	$security = get_transient('vibebp_admin_security');
    	if($request->get_param('security') == $security){
    		return true;
    	}

    	return false;
    }

    function complete_wizard($request){
    	update_option('vibebp_setup_complete',1);
    	ob_start();
    	do_action('vibebp_setup_wizard_complete');
        global $bp;
        if(function_exists('bp_core_install')){
            bp_core_install( $bp->active_components );    
        }
        
    	$debug = ob_get_clean();


        //Flush Permalinks and force set to postname
        global $wp_rewrite; 
        $wp_rewrite->set_permalink_structure('/%postname%/'); 
        update_option( "rewrite_rules", FALSE ); 
        $wp_rewrite->flush_rules( true );

        if(function_exists('elementor_load_plugin_textdomain')){
            Elementor\Plugin::$instance->files_manager->clear_cache();    
        }
        
        //reload_nav
        $body = json_decode($request->get_body(),true);

    	return new WP_REST_Response(array('status'=>1,'debug'=>$debug,'url'=>bp_core_get_user_domain($body['id']).'?reload_nav=1'),200);
    }

    function setup_wizard($request){


    	$body = json_decode($request->get_body(),true);
    	$debug= array();
    	switch($body['step']){

    		case 'features':
	    		if(!empty($body['features'])){
	    			foreach($body['features'] as $feature){
	    				ob_start();
	    				do_action('vibebp_activate_feature_'.$feature['key']);
	    				$debug[$feature['key']] = ob_get_clean();
	    			}
	    		}
    		break;
    		case 'content':
    			foreach($body['content'] as $layout){
    				ob_start();
    				do_action('vibebp_import_layout_'.$layout['key']);
    				$debug[$layout['key']] = ob_get_clean();
    			}
			break;
    	}

    	return new WP_REST_Response(array('status'=>1,'debug'=>$debug),200);
    }


    function enable_xprofile(){
    	if(bp_is_active('xprofile'))
    		return;
    	if(empty($this->bp_active_components)){
    		$this->bp_active_components=get_option('bp-active-components');
    	}
    	$this->bp_active_components['xprofile']=1;
    	update_option('bp-active-components',$this->bp_active_components);
    }
    function enable_activity(){
    	if(bp_is_active('activity'))
    		return;
    	if(empty($this->bp_active_components)){
    		$this->bp_active_components=get_option('bp-active-components');
    	}
    	$this->bp_active_components['activity']=1;
    	update_option('bp-active-components',$this->bp_active_components);
    }
    function enable_messages(){
    	if(bp_is_active('messages'))
    		return;
    	if(empty($this->bp_active_components)){
    		$this->bp_active_components=get_option('bp-active-components');
    	}
    	$this->bp_active_components['messages']=1;
    	update_option('bp-active-components',$this->bp_active_components);
    } 
    function enable_notifications(){
    	if(bp_is_active('notifications'))
    		return;
    	if(empty($this->bp_active_components)){
    		$this->bp_active_components=get_option('bp-active-components');
    	}
    	$this->bp_active_components['notifications']=1;
    	update_option('bp-active-components',$this->bp_active_components);
    } 
    function enable_friends(){
    	if(bp_is_active('friends'))
    		return;
    	if(empty($this->bp_active_components)){
    		$this->bp_active_components=get_option('bp-active-components');
    	}
    	$this->bp_active_components['friends']=1;
    	update_option('bp-active-components',$this->bp_active_components);
    } 
    function enable_groups(){

    	if(bp_is_active('groups'))
    		return;
    	
    	if(empty($this->bp_active_components)){
    		$this->bp_active_components=get_option('bp-active-components');
    	}
    	$this->bp_active_components['groups']=1;

    	update_option('bp-active-components',$this->bp_active_components);
    } 
    function enable_followers(){
    	$check = vibebp_get_setting('followers','bp');
    	if(empty($check) || $check != 'on'){
    		$vibebp = get_option(VIBE_BP_SETTINGS);
    		$vibebp['bp']['followers']='on';
			update_option(VIBE_BP_SETTINGS,$vibebp );
    	}
    } 
    function enable_likes(){
    	$check = vibebp_get_setting('likes','bp');
    	if(empty($check) || $check != 'on'){
    		$vibebp = get_option(VIBE_BP_SETTINGS);
    		$vibebp['bp']['likes']='on';
			update_option(VIBE_BP_SETTINGS,$vibebp );
    	}
    	
    }

    function field_options($options){
    	
    	if(!empty($this->options) && empty($options)){
    		return $this->options;
    	}

    	return $options;
	}
    public function is_profile_group( $name ) {
        global $wpdb,$bp;
        $group_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bp->profile->table_name_groups} WHERE name = %s", $name ) );
        if( $group_id ) {
            return true;
        } else {
            return false;
        }
    }

    function import_default_xprofile(){

    	$path = plugin_dir_path(__FILE__).'../sampledata/xprofile_fields.json';
    	
    	if(file_exists($path)){
    		$content = file_get_contents($path);
    		$content = json_decode($content,true); 


    		foreach($content as $group){

                if(!$this->is_profile_group($group['group'])){
        			if(empty($group['field_group_id'])){
        				$group['field_group_id']=xprofile_insert_field_group(array('name'=>$group['group']));
        			}
        			
        			if(!empty($group['fields']) && !empty($group['field_group_id'])){
        				foreach($group['fields'] as $field){

        					$field['field_group_id']=$group['field_group_id'];
    						
    						print_R($field);

        					if($field['type'] == 'checkboxes' || $field['type'] == 'radio' || $field['type'] == 'selectbox'){
        						$this->options = $field['options'];
        						
        						add_filter('xprofile_field_options_before_save',array($this,'field_options'));
        						
        					}
        					xprofile_insert_field($field);	
        				}
        			}
                }
    		}
    	}
    	
    	$path = plugin_dir_path(__FILE__).'../sampledata/profile_layout.json';
    	
    	if(file_exists($path)){
    		$content = file_get_contents($path);
    		$content = json_decode($content,true);
    		foreach($content as $post_type=>$posts){
    			foreach($posts as $post){
    				$post['post_type'] = $post_type;
    				 $id = wp_insert_post($post);  
    				if($post_type == 'member-profile'){
    					if(function_exists('vibe_get_customizer') && empty(vibe_get_option('create_course'))){
	    					$customizer = get_option('vibe_customizer');
	    					if(!$customizer['profile_layout'] != 'blank'){
	    						$customizer['profile_layout'] = 'blank';
	    						update_option('vibe_customizer',$customizer);	
	    					}
	    				}
    				}				
    			}
    		}
    	}
    }

    private function _imported_term_id( $original_term_id, $new_term_id = false ) {
		$terms = get_transient( 'import_term_ids' );
		if ( ! is_array( $terms ) ) {
			$terms = array();
		}
		if ( $new_term_id ) {
			if ( ! isset( $terms[ $original_term_id ] ) ) {
				print_r( 'Insert old TERM ID ' . $original_term_id . ' as new TERM ID: ' . $new_term_id );
			} else if ( $terms[ $original_term_id ] != $new_term_id ) {
				print_r( 'Replacement OLD TERM ID ' . $original_term_id . ' overwritten by new TERM ID: ' . $new_term_id );
			}
			$terms[ $original_term_id ] = $new_term_id;
			set_transient( 'import_term_ids', $terms, 60 * 60 );
		} else if ( $original_term_id && isset( $terms[ $original_term_id ] ) ) {
			return $terms[ $original_term_id ];
		}

		return false;
	}

    function import_default_menu(){
    	
        $loggedin_menu = 'VibeBP Loggedin Menu';
        

        $check = wp_get_nav_menu_object( $loggedin_menu );
        $pages = bp_nav_menu_get_loggedin_pages();
       
        if(!$check){
            $menu_id = wp_create_nav_menu($loggedin_menu);
           
            wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title' =>  '<span class="vicon vicon-settings"></span>'.html_entity_decode( $pages['settings']->post_title, ENT_QUOTES, get_bloginfo( 'charset' ) ),
                'menu-item-classes' => 'bp-menu bp-settings-nav',
                'menu-item-url' => $pages['settings']->guid, 
                'menu-item-object'     =>'bp_loggedin_nav',
                'menu-item-object_id'  => -1,
                'menu-item-type' => 'bp_nav',
                'menu-item-status' => 'publish')
            );
            
            wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title' =>  '<span class="vicon vicon-bell"></span>'.html_entity_decode( $pages['notifications']->post_title, ENT_QUOTES, get_bloginfo( 'charset' ) ),
                'menu-item-classes' => 'bp-menu bp-notifications-nav',
                'menu-item-url' => $pages['notifications']->guid, 
                'menu-item-object'     =>'bp_loggedin_nav',
                'menu-item-object_id'  => -1,
                'menu-item-type' => 'bp_nav',
                'menu-item-status' => 'publish')
            );
            $locations = get_theme_mod('nav_menu_locations');
            
            $locations['loggedin'] = $menu_id;
            set_theme_mod( 'nav_menu_locations', $locations );
        }


        $profile_menu = 'VibeBP Profile Menu';
        $pcheck = wp_get_nav_menu_object( $profile_menu );
        
        if(!$pcheck){
            $menu_id = wp_create_nav_menu($profile_menu);
            unset($pages['logout']);
            unset($pages['notifications']);
            unset($pages['settings']);
            $i=0;
            print_r('creating profile menu');
            foreach($pages as $key=>$page){
                wp_update_nav_menu_item($menu_id, 0, array(
                    'menu-item-title' =>  html_entity_decode( $pages[$key]->post_title, ENT_QUOTES, get_bloginfo( 'charset' ) ),
                    'menu-item-classes' => 'bp-menu bp-'.$key.'-nav',
                    'menu-item-url' => $pages[$key]->guid, 
                    'menu-item-object'     =>'bp_loggedin_nav',
                    'menu-item-object_id'  => -1,
                    'menu-item-type' => 'bp_nav',
                    'menu-item-status' => 'publish')
                );
               
            }
            $locations = get_theme_mod('nav_menu_locations');
            $locations['profile'] = $menu_id;
            set_theme_mod( 'nav_menu_locations', $locations );
        }
    }  


    function import_group_layout(){
    	$path = plugin_dir_path(__FILE__).'../sampledata/group_layout.json';
    	if(file_exists($path)){
    		$content = file_get_contents($path);
    		$content = json_decode($content,true);
    		foreach($content as $post_type=>$posts){
    			foreach($posts as $post){
    				$post['post_type'] = $post_type;
    				wp_insert_post($post);
    				if($post_type =='group-layout'){

	    				if(class_exists('WPLMS_Init') && function_exists('vibe_get_option') && empty(vibe_get_option('create_course'))){
	    					$customizer = get_option('vibe_customizer');
	    					if(!$customizer['group_layout'] != 'blank'){
	    						$customizer['group_layout'] = 'blank';
	    						update_option('vibe_customizer',$customizer);	
	    					}
	    				}
    				}
    			}
    		}
    	}
    } 
    function import_members_directory(){
    	$path = plugin_dir_path(__FILE__).'../sampledata/member_directory.json';

        global $wpdb;
        
        $check = $wpdb->get_var("SELECT * FROM {$wpdb->posts} WHERE post_name = 'members-directory'");
        
    	if(file_exists($path) && empty($check)){
    		$content = file_get_contents($path);
    		$content = json_decode($content,true);
    		foreach($content as $post_type=>$posts){
    			foreach($posts as $post){
    				$post['post_type'] = $post_type;
    				$id = wp_insert_post($post);
    				$pages = get_option('bp-pages');
    				$pages['members']=$id;
    				update_option('bp-pages',$pages);
    				if(class_exists('WPLMS_Init') && function_exists('vibe_get_option') && empty(vibe_get_option('create_course'))){
    					$customizer = get_option('vibe_customizer');
    					if(!$customizer['directory_layout'] != 'blank'){
    						$customizer['directory_layout'] = 'blank';
    						update_option('vibe_customizer',$customizer);	
    					}
    					
    				}
    				break;
    			}
    		}

    		$path = plugin_dir_path(__FILE__).'../sampledata/member_types.json';
    		if(file_exists($path)){
    			$content = file_get_contents($path);
    			$content = json_decode($content,true);
    			if(!empty($content) && is_array($content)){
    				update_option('vibebp_member_types',$content);
    			}
    		}
    	}
    }  
    function import_groups_directory(){
    	$path = plugin_dir_path(__FILE__).'../sampledata/group_directory.json';
    	if(file_exists($path)){
    		$content = file_get_contents($path);
    		$content = json_decode($content,true);
    		foreach($content as $post_type=>$posts){
    			foreach($posts as $post){
    				$post['post_type'] = $post_type;
    				$id = wp_insert_post($post);
    				$pages = get_option('bp-pages');
    				$pages['groups']=$id;
    				update_option('bp-pages',$pages);
    				if(class_exists('WPLMS_Init') && function_exists('vibe_get_option') && empty(vibe_get_option('create_course'))){
    					$customizer = get_option('vibe_customizer');
    					if(!$customizer['directory_layout'] != 'blank'){
    						$customizer['directory_layout'] = 'blank';
    						update_option('vibe_customizer',$customizer);	
    					}
    				}
    				break;
    			}
    		}

    		$path = plugin_dir_path(__FILE__).'../sampledata/group_types.json';
    		if(file_exists($path)){
    			$content = file_get_contents($path);
    			$content = json_decode($content,true);
    			if(!empty($content) && is_array($content)){
    				update_option('vibebp_group_types',$content);
    			}
    		}

    	}
    }
}

VibeBP_SetupWizard::init();