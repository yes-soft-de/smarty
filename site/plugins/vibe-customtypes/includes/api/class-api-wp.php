<?php

if(!class_exists('Wplms_Api_Wp')){
	Class Wplms_Api_Wp{

		public static $instance;
		
		public static function init(){

	        if ( is_null( self::$instance ) )
	            self::$instance = new Wplms_Api_Wp();
	        return self::$instance;
	    }

		private function __construct(){
			$this->wplms_api_tracker = '';
			$this->global_updates = '';
			add_action('wp_ajax_remove_update',array($this,'remove_update'));
			add_action('wp_ajax_remove_update_user',array($this,'remove_update_user'));
		}
		
		function get_global_updates(){
			if(empty($this->wplms_api_tracker) && empty($this->global_updates)){
				$this->wplms_api_tracker = get_option('wplms_api_tracker');
				$this->global_updates = $this->wplms_api_tracker['updates'];
			}
		}

		function remove_update(){
			if(empty($_POST['security']) || !isset($_POST['id']) || !wp_verify_nonce($_POST['security'],'update_actions') || !current_user_can('edit_posts') || !is_numeric($_POST['id'])){
				echo _x('Security check failed','','vibe-customtypes');
				die();
			}
				
			if(isset($_POST['id'])){
				$this->get_global_updates();
				if(!empty($this->wplms_api_tracker) && !empty($this->global_updates)){
					if(array_key_exists($_POST['id'], $this->global_updates)){
						unset($this->global_updates[$_POST['id']]);
						$this->wplms_api_tracker['updates'] = $this->global_updates;
						update_option('wplms_api_tracker',$this->wplms_api_tracker);
					}

				}
				echo _x('Removed!','','vibe-customtypes');
			}
			die();
		}

		function remove_update_user(){
			if(empty($_POST['security']) || !isset($_POST['id']) || !wp_verify_nonce($_POST['security'],'update_actions') || !current_user_can('edit_posts') || empty($_POST['user_id']) || !is_numeric($_POST['user_id']) || !is_numeric($_POST['id'])){
				echo _x('Security check failed','','vibe-customtypes');
				die();
			}
				
			if(isset($_POST['id'])){
				$meta = get_user_meta($_POST['user_id'],'wplms_api_tracker',true);
				if(!empty($meta) ){
					$updates = $meta['updates'];
					if(array_key_exists($_POST['id'], $updates)){
						unset($updates[$_POST['id']]);
						$meta['updates'] = $updates;
						print_r($meta);
						update_user_meta($_POST['user_id'],'wplms_api_tracker',$meta);
					}
				}
				
				echo _x('Removed!','','vibe-customtypes');
			}
			die();
		}
	}

	add_action('init',function(){
		Wplms_Api_Wp::init();
	});
}

