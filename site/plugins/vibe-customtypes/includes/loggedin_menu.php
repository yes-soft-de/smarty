<?php

 if ( ! defined( 'ABSPATH' ) ) exit;

 class WPLMS_Loggedin_Menu{

 	protected $option = 'course_settings';
 	public $course_details_labels= array();
	public static $instance;
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_Loggedin_Menu();
        return self::$instance;
    }

    public function __construct(){
    	add_filter('wplms_lms_commission_tabs',array($this,'add_loggedin_menu_settings'));
    	add_filter('lms_general_settings',array($this,'generate_loggedin_menu_form'),99);
    	add_filter('wplms_logged_in_top_menu',array($this,'apply_loggedin_menu'),1);
    }

    /*
    MAin Function to Apply logged in menu
    */

   	function apply_loggedin_menu($menu){

   		if(is_admin()){
   			return $menu;
   		}

    	/*
    	CHECK IF MENU IS SAVED
    	*/
    	$loggedin_menu = $this->get();
    	$defaults = $this->get(1);
    	$k1 = array_keys($loggedin_menu);
    	$k2 = array_keys($defaults);

    	if($k1 != $k2){
    		//IF menu is saved then use the saved values only.
    		remove_all_filters('wplms_logged_in_top_menu');
    	}

    	if(isset($loggedin_menu) && $loggedin_menu!= ''){
    		if(!count($loggedin_menu)){
    			return array();
    		}else{
    			$loggedin_menu = $this->check_active_components($loggedin_menu);
    		}

			foreach($loggedin_menu as $component  => $item){

				if( isset($item['link']) && strpos($item['link'], '{{siteurl}}') !== false){
					$loggedin_menu[$component]['link']=preg_replace("/{{siteurl}}/",home_url().'/', $item['link']);
				}
				if(isset($item['link']) && strpos($item['link'],'{{userprofile}}') !== false){
					$loggedin_menu[$component]['link']=preg_replace("/{{userprofile}}/",bp_loggedin_user_domain(), $item['link']);
				}

				if(isset($item['label'])){
					if(function_exists('icl_t')){
                        $loggedin_menu[$component]['label'] = icl_t('wplms', 'Loggedin Menu - '.$component,$item['label']);
					}
					if(strpos($item['label'],'{{notification_count}}') !== false){
						if(function_exists('vbp_current_user_notification_count')){
							$n=vbp_current_user_notification_count();
						}else{
							$n='0';
						}
						$loggedin_menu[$component]['label']=preg_replace("/{{notification_count}}/",'<span>'.$n.'</span>', $loggedin_menu[$component]['label']);
					}
					if(strpos($item['label'],'{{inbox_count}}') !== false){
						if(function_exists('messages_get_unread_count')){
							$loggedin_menu[$component]['label']=preg_replace("/{{inbox_count}}/",(messages_get_unread_count()?' <span>' . messages_get_unread_count() . '</span>':''), $loggedin_menu[$component]['label']);
						}else{
							$loggedin_menu[$component]['label']=preg_replace("/{{inbox_count}}/",('0'), $loggedin_menu[$component]['label']);
						}
					}
				}

				if(isset($item['privacy']) && !$this->check_privacy($item['privacy'])){
					//Varifier function missing which is why we have to add this
					unset($loggedin_menu[$component]);
				}
			}
		}
    	return $loggedin_menu;
	}

	function check_active_components($loggedin_menu){
		if(function_exists('bp_is_active') && !bp_is_active('notifications')){
			unset($loggedin_menu['notifications']);
		}
		if(function_exists('bp_is_active') && !bp_is_active('groups')){
			unset($loggedin_menu['groups']);
		}
		if(function_exists('bp_is_active') && !bp_is_active('messages')){
			unset($loggedin_menu['inbox']);
		}
		if(function_exists('bp_is_active') && !bp_is_active('settings')){
			unset($loggedin_menu['settings']);
		}
		if(!class_exists('Wplms_Wishlist_Component')){
			unset($loggedin_menu['wishlist']);
		}
		if (!function_exists('WC')) {
			unset($loggedin_menu['orders']);
		}
		if (!function_exists('pmpro_getMembershipLevelsForUser')) {
			unset($loggedin_menu['membership']);
		}

		return $loggedin_menu;
	}

   	/*
    	ADD TAB IN LMS _ SETTINGS
    */

    function add_loggedin_menu_settings($tabs){
    	if(!isset($_GET['tab']) || $_GET['tab'] == 'general'){
	    	$tabs['loggedin_menu'] = _x('Logged in Menu','configure loggedin menu in LMS - Settings','vibe-customtypes');
 		}
 		return $tabs;
    }

    /*
    	GENERATE TAB SETTINGS IN LMS _ SETTINGS, Note the return array, to avoid LMS - Settings from regeneration
    */

    function generate_loggedin_menu_form($settings){
    	if(!isset($_GET['sub']) || $_GET['sub'] != 'loggedin_menu')
    		return $settings;

    	$loggedin_menu_settings = apply_filters('profile_menu_dropdown_settings_settings',array(
    			array(
					'label'=> sprintf(__('Profile Menu Dropdown %s tutorial %s','vibe-customtypes' ),'<span><a href="https://vibethemes.com/documentation/wplms/knowledge-base/customise-profile-menu-dropdown-wplms-2-6/" target="_blank">','</a>'),

					'type'=> 'heading',
				),
				array(
					'label'=>__('Manage Profile Menu Dropdown','vibe-customtypes' ),
					'type'=> 'menu_dropdown',
				),
    		));
    	$this->put();
    	$this->generate_form($loggedin_menu_settings);
    	//Do not return to stop general form output
    	return array();
    }

    /*
    	PROCESS TAB SETTINGS IMPORTANT
    */

    function generate_form($generate_form_settings){
		echo '<form method="post"><table class="form-table">
				<tbody>';	

		foreach($generate_form_settings as $setting ){
			echo '<tr valign="top" '.(empty($setting['class'])?'':'class="'.$setting['class'].'"').'>';
			switch($setting['type']){
				case 'heading':
					echo '<th scope="row" class="titledesc" colspan="2"><h3>'.$setting['label'].'</h3></th>';
				break;
				case 'menu_dropdown':
					echo '<td>';
					$loggedin_menu = $this->get();
					$default_loggedin_menu = $this->get(1);
					$more_menu_items =  array_diff_key($default_loggedin_menu,$loggedin_menu);
					echo '<a id="add_item" class="button-primary">'._x('Add New Item','abutton label','vibe-customtypes').'</a>';
					echo '<ul class="course_details_list">';

					foreach($loggedin_menu as $component => $item){
						?>
						<li class="detail_list"><span class="dashicons dashicons-menu"></span> 
							<label>
								<span class="logged_inmenu_item"><?php echo $item['label']; ?></span>
								<span class="">[<?php echo $item['privacy']; ?> ] </span>
								<i class="<?php echo $item['icon']; ?> logged_inmenu_item_icon"></i></label>
							<input type="hidden" name="loggedin_menu[component][]" value="<?php echo $component; ?>">
							<input type="hidden" name="loggedin_menu[label][]" value="<?php echo $item['label']; ?>">
							<input type="hidden" name="loggedin_menu[icon][]" value="<?php echo $item['icon']; ?>">
							<input type="hidden" name="loggedin_menu[link][]" value="<?php echo $item['link']; ?>">
							<input type="hidden" name="loggedin_menu[privacy][]" value="<?php echo (isset($item['privacy'])?$item['privacy']:'all'); ?>">
							<span class="dashicons dashicons-no"></span></li>
						</li>
						<?php
					}
					?>
					<li class="hidden_detail_list"><span class="dashicons dashicons-menu"></span>
						<select class="select_component">
							<option><?php _e('Select component','vibe-customtypes'); ?></option>
							<?php
								if(!empty($more_menu_items)){
									foreach($more_menu_items as $component => $item){
										echo '<option value="'.$component.'">'.$item['label'].'</option>';
									}
								}
							?>
							<option value="custom"><?php _e('Custom link','vibe-customtypes'); ?></option>
						</select>
						<div class="add_new_item">
							<ul>
							<li><label><?php _e('Component','vibe-customtypes'); ?></label><input type="hidden" rel-name="loggedin_menu[component][]" placeholder="<?php _ex('Add reference name (without spaces or special characters)','special chars','vibe-customtypes'); ?>"></li>

							<li><label><?php _e('Label','vibe-customtypes'); ?></label><input type="text" rel-name="loggedin_menu[label][]" class="label" value=""></li>

							<li><label><?php _e('Icon','vibe-customtypes'); ?></label><input type="text" rel-name="loggedin_menu[icon][]"  class="icon" value=""></li>

							<li><label><?php _e('Link','vibe-customtypes'); ?></label><input type="text" rel-name="loggedin_menu[link][]"  class="link" value=""></li>

							<li><label><?php _e('Privacy','vibe-customtypes'); ?></label><select rel-name="loggedin_menu[privacy][]" class="privacy" >

							<?php 
								$privacy_options = $this->get_privacy_options();
								if(!empty($privacy_options)){
									foreach($privacy_options as $p=>$o){
										echo '<option value="'.$p.'">'.$o.'</option>';
									}
								}
							?>
							</select></li>
							</ul>
						</div>
						<span class="dashicons dashicons-no"></span></li>
					</li>
					<?php
					echo '</ul>';
					wp_nonce_field('loggedin_menu_security','loggedin_menu_security');   
					echo '</tr><tr><td><input type="submit" name="save_loggedin_menu" class="button-primary" value="'._x('Save Settings','save settings label','vibe-customtypes').'"/></td><tr>';
				break;
			}
		}
		?>
		<style>span.dashicons.dashicons-editor-help {margin-left:10px } .detail_list{border:1px solid #eee; padding:8px 15px;background:#fff;max-width:80%;min-width:240px;position:relative;}.detail_list .dashicons-no{float:right;color:red;position:absolute; top:10px;right:10px;} .button-primary.save{background: #E8442F; text-shadow: none; box-shadow: none; border: none;}.hidden_detail_list,.add_new_item{display:none;}.add_new_item li{display:block;clear:both;}.add_new_item li label{width:200px;float:left;}.add_new_item input{min-width:320px;}span.logged_inmenu_item {display:inline-block; width:210px}.detail_list label i{margin-left:30px;margin-right:30px;}
		</style>
		<script>
			var loggedin_menu = <?php $def = $this->get(1) ; echo json_encode($def); ?>;
			jQuery(document).ready(function($){
				$(".course_details_list").sortable({
					"handle":".dashicons-menu",
					 axis: "y"
				});
				$(".dashicons-no").on("click",function(){
					$(this).parent().remove();
					$("input[name='save_loggedin_menu']").addClass("save");
					var value = $(this).parent().find('input[name="loggedin_menu[component][]"]').val();
					if(value.length){
						$('.hidden_detail_list .select_component').append('<option value="'+value+'">'+loggedin_menu[value]['label']+'</option>');	
					}
					return false;
				});
				$('#add_item').on('click',function(){
					var cloned = $('.hidden_detail_list').clone();
					cloned = cloned.removeClass('hidden_detail_list').addClass('detail_list');
					$('.course_details_list').append(cloned);
					$('.course_details_list .select_component').on('change',function(){
						var $this = $(this);
						$this.parent().find('.add_new_item').show(200);
						$this.parent().find('input[rel-name],select[rel-name]').each(function(){
							var rel = $(this).attr('rel-name');
							$(this).attr('name',rel);
						});
						var value = $(this).val();
						if(value == 'custom'){
							$this.parent().find('input[type="hidden"]').attr('type','text');
						}else{
							$this.parent().find('input[type="hidden"]').val(value);
							$this.parent().find('.label').val(loggedin_menu[value]['label']);
							$this.parent().find('.icon').val(loggedin_menu[value]['icon']);
							$this.parent().find('.link').val(loggedin_menu[value]['link']);
							$this.parent().find('.privacy').val(loggedin_menu[value]['privacy']);
						}
					});
					//recall standard functions
					$(".course_details_list").sortable({
						"handle":".dashicons-menu",
						 axis: "y"
					});
					$(".dashicons-no").on("click",function(){
						$(this).parent().remove();
						$("input[name='save_loggedin_menu']").addClass("save");
						return false;
					});
				});
			});
		</script>
		<?php
		echo '</tbody></table></form>';
	}

	//Function to get privacy Options Starts here
    function get_privacy_options(){
    	$privacy_options = apply_filters('wplms_profile_menu_dropdown_options',array(
						'all'=>_x('All Users','privacy option for profile drop down menu','vibe-customtypes'),
						'students'=>_x('Students','privacy option for profile drop down menu','vibe-customtypes'),
						'instructors'=>_x('Instructors','privacy option for profile drop down menu','vibe-customtypes'),
						'admins'=>_x('Administrators','privacy option for profile drop down menu','vibe-customtypes')
						));
    	return $privacy_options;
    }

    /*
    	GET LOGGED IN MENU 
    */

    function get($default=null){
    	if(empty($default)){
    		$option = get_option('logged_in_profile_menu');	
    		if(isset($option) &&  $option !=''){
    			$loggedin_menu = $option;
    		}
    	}
    	if(!isset($loggedin_menu)){
    		$loggedin_menu = array();
    		if ( !defined( 'WPLMS_DASHBOARD_SLUG' ) )
				define ( 'WPLMS_DASHBOARD_SLUG', 'dashboard' );

			$loggedin_menu['dashboard'] = array(
		              'icon' => 'icon-meter',
		              'label' => __('Dashboard','vibe-customtypes'),
		              'link' => '{{userprofile}}'.WPLMS_DASHBOARD_SLUG
          	);
    		$loggedin_menu['courses'] = array(
			              'icon' => 'icon-book-open-1',
			              'label' => __('Courses','vibe-customtypes'),
			              'link' => '{{userprofile}}'.BP_COURSE_SLUG
			              );
			$loggedin_menu['stats'] = array(
			              'icon' => 'icon-analytics-chart-graph',
			              'label' => __('Stats','vibe-customtypes'),
			              'link' => '{{userprofile}}'.BP_COURSE_SLUG.'/'.BP_COURSE_STATS_SLUG
			              );
			if ( bp_is_active( 'messages' ) ){
			  	$loggedin_menu['messages']=array(
			              'icon' => 'icon-letter-mail-1',
			              'label' => __('Inbox{{inbox_count}}','vibe-customtypes'),
			              'link' => '{{userprofile}}'.BP_MESSAGES_SLUG
			              );
			}
			if ( bp_is_active( 'notifications' ) ){
				  $loggedin_menu['notifications']=array(
				              'icon' => 'icon-exclamation',
				              'label' => __('Notifications{{notification_count}}','vibe-customtypes'),
				              'link' => '{{userprofile}}'.BP_NOTIFICATIONS_SLUG
				              );
			}
			if ( bp_is_active( 'groups' ) ){
			  	$loggedin_menu['groups']=array(
			              'icon' => 'icon-myspace-alt',
			              'label' => __('Groups','vibe-customtypes'),
			              'link' => '{{userprofile}}'.BP_GROUPS_SLUG 
			              );
			}
			$loggedin_menu['settings']=array(
			              'icon' => 'icon-settings',
			              'label' => __('Settings','vibe-customtypes'),
			              'link' => '{{userprofile}}'.BP_SETTINGS_SLUG
          	);
			if(!doing_filter('wplms_logged_in_top_menu'))
          		$loggedin_menu = apply_filters('wplms_logged_in_top_menu',$loggedin_menu);
    	}

    	foreach($loggedin_menu as $k => $menu_item){
    		if(!isset($menu_item['callback'])){
    			$loggedin_menu[$k]['callback'] = false;
    		}
    		if(!isset($menu_item['privacy'])){
    			$loggedin_menu[$k]['privacy'] = 'all';
    		}
    		$link = bp_loggedin_user_domain();
    		$loggedin_menu[$k]['link'] = str_replace($link, '{{userprofile}}', $loggedin_menu[$k]['link']);
    	}
    	$loggedin_menu = $this->check_active_components($loggedin_menu);

    	return $loggedin_menu;
    }

    /*
    	SAVED LOGGED IN MENU by processing format
    */

    function put(){
    	if(isset($_POST['save_loggedin_menu'])){
	    	if ( !isset($_POST['loggedin_menu_security']) || !wp_verify_nonce($_POST['loggedin_menu_security'],'loggedin_menu_security') || !current_user_can('manage_options')){
	         	_e('Security check Failed. Contact Administrator.','vibe-customtypes');
	         	die();

	      	}
	      	if(empty($_POST['loggedin_menu']) || empty($_POST['loggedin_menu']['component']))
	      		return;

	      	$new_loggedin_menu = array();
	      	foreach($_POST['loggedin_menu']['component'] as $k => $component){
	      		$new_loggedin_menu[$component] = array();
	      		if(isset($_POST['loggedin_menu']['label'][$k])){
	      			$new_loggedin_menu[$component]['label'] = $_POST['loggedin_menu']['label'][$k];
	      			if (function_exists ( 'icl_register_string' )){
						icl_register_string('wplms', 'Loggedin Menu - '.$component, $_POST['loggedin_menu']['label'][$k]);
					}
	      		}
	      		if(isset($_POST['loggedin_menu']['icon'][$k])){
	      			$new_loggedin_menu[$component]['icon'] = $_POST['loggedin_menu']['icon'][$k];
	      		}
	      		if(isset($_POST['loggedin_menu']['link'][$k])){
	      			$new_loggedin_menu[$component]['link'] = $_POST['loggedin_menu']['link'][$k];
	      		}
	      		if(isset($_POST['loggedin_menu']['privacy'][$k])){
	      			$new_loggedin_menu[$component]['privacy'] = $_POST['loggedin_menu']['privacy'][$k];
	      		}
	      	}
			update_option('logged_in_profile_menu',$new_loggedin_menu);
	      	?>
	      	<div class="message updated">
	      		<p><?php _ex('Settings saved','saved settings message','vibe-customtypes'); ?></p>
	      	</div>
	      	<?php
      	}
    }

    /* PROCESS PRIVACY HANDLE */

    function check_privacy($privacy){
    	$privacy_options = $this->get_privacy_options();
    	$return = false;
    	if(function_exists('bp_course_version')){
    		$version = bp_course_version();	
    	}else{
    		return true;
    	}

    	switch($privacy){
    		case 'all':
    			return true;
    		break;
    		case 'students':
    			if( is_user_logged_in() && !current_user_can('edit_posts'))
    				return true;
    		break;
    		case 'instructors':
    			if(is_user_logged_in() && current_user_can('edit_posts'))
    				return true;
    		break;
    		case 'admins':
    			if(is_user_logged_in() && current_user_can('manage_options'))
    				return true;
    		break;
    		default:
    			$return = apply_filters('wplms_course_details_check_privacy',false,$privacy);
    		break;
    	}

    	return $return;
    }
}

WPLMS_Loggedin_Menu::init();
