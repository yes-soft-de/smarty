<?php

if ( ! defined( 'ABSPATH' ) ) exit;

include_once 'settings/group_types.php';
include_once 'settings/member_types.php';
include_once 'settings/field_types.php';
include_once 'settings/emails.php';

class VibeBP_Settings{


	public static $instance;
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Settings();
        return self::$instance;
    }

	private function __construct(){
		
		add_action( 'add_meta_boxes', array($this,'member_profile_card'));
		add_action( 'save_post_member-profile', array($this,'save_member_profile_card' ),10,1);
		add_action( 'save_post_member-card', array($this,'save_member_profile_card' ),10,1);
		add_action('bp_members_admin_user_metaboxes',array($this,'user_metabox'),10,2);
		add_action( 'bp_members_admin_load', array( $this, 'process_member_profile_update' ) );
		add_action( 'add_meta_boxes', array($this,'group_layout_card'));
		add_action('bp_groups_admin_meta_boxes',array($this,'set_group_layout'));
		add_action( 'save_post_group-layout', array($this,'save_group_layout_card' ),10,1);
		add_action( 'save_post_group-card', array($this,'save_group_layout_card' ),10,1);
		add_action( 'bp_group_admin_edit_after',array($this, 'bp_groups_process_group_layout_update' ));
		add_action('wp_ajax_save_member_card',array($this,'save_member_card'));
		add_action('wp_ajax_regenerate_service_worker',array($this,'regenerate_service_worker'));
	}

	public function vibebp_settings() {
	    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
		$this->vibebp_settings_tabs($tab); 
		$this->get_vibebp_settings($tab);
		do_action('vibebp_settings_page_loaded');
	}

	function vibebp_settings_tabs($tab){
		$tabs = apply_filters('vibebp_settings_tabs',array( 
	    		'general' => __('General','vibebp'), 
	    		'bp' => __('BuddyPress','vibebp'), 
	    		//'cards' => __('Cards','vibebp'), 
    		));

		if(vibebp_get_setting('service_workers')){
			$tabs['service_worker'] = __('Service Worker','vibebp');
		}
		$tabs['app'] = __('App Builder','vibebp');

	 	$current = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
	    echo '<div id="icon-themes" class="icon32"><br></div>';
	    echo '<h2 class="nav-tab-wrapper">';
	    foreach( $tabs as $tab => $name ){
	        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
	        echo "<a class='nav-tab$class' href='?page=".VIBE_BP_SETTINGS."&tab=$tab'>$name</a>";

	    }
	    echo '</h2>';
	}


	function get_vibebp_settings($tab){
		if(isset($_POST['save'])){
			echo $this->vibebp_save_settings($tab);
		}		
		switch($tab){
			case 'cards':
				$this->vibebp_cards();
			break;
			case 'bp':
				$this->vibebp_buddypress();
			break; 
			case 'app':
				$this->show_app_form();
			break;
			case 'service_worker':
				$this->service_workers();
			break;
			default:
				$function_name = apply_filters('vibebp_settings_tab',$tab);
				if(!empty($tab) && function_exists($function_name) && $tab != 'general'){
					$function_name();
				}else{
					$this->vibebp_general_settings();
				}
				
			break;
		}
		do_action('get_vibebp_settings',$tab);
	}


	function vibebp_cards(){

		echo '<h3>'.__('Card Builder','vibebp').'</h3>';

		$template_array = apply_filters('vibebp_card_builder_tabs',array(
			'members'=> __('Members','vibebp'),
		));
		if(bp_is_active('groups')){
			$template_array['groups'] = __('Groups','vibebp');
		}
		

		echo '<ul class="subsubsub">';

		$cards = get_option('cards');
		if(empty($cards)){
			$cards = array();
		}
		$post_types = get_post_types();
		if(!empty($cards)){
			foreach($cards as $card){
				$template_array[$card] = $post_types[$card];
			}
		}


		$template_array['add']=__('Add Card','vibebp');

		foreach($template_array as $k=>$value){
			if(empty($_GET['sub']) && empty($current)){
				$current = $k;
			}else if(!empty($_GET['sub']) && empty($current)){
				$current = $_GET['sub'];
			}
			echo '<li><a href="?page='.VIBE_BP_SETTINGS.'&tab=cards&sub='.$k.'" '.(($k == $current)?'class="current"':'').'>'.$value.'</a>  &#124; </li>';
		}
		echo '</ul><div class="clear"><hr/>';
		//
		if(!isset($_GET['sub'])){$_GET['sub']='members';}

		
		switch($_GET['sub']){
			case 'groups':
				include_once('settings/group_card.php');
			break;
			case 'add':
				if(!empty($_POST)){
					if ( isset($_POST['add_card'])  && wp_verify_nonce($_POST['add_card'],'add_card') ){
				        $cards[]=$_POST['card'];
				        update_option('cards',$cards);
			      	}
				}
				?>
				<div style="display:inline-block;padding:1.5rem;background:#fff;border:1px solid rgba(0,0,0,0.1)">
					<form method="post">
					<select name="card">
						<option><?php _e('Select Post Type','vibebp'); ?></option>
						<?php
						
						foreach($post_types as $post=>$label){
							if(!in_array($post,$cards)){
								echo '<option value="'.$post.'">'.$label.'</option>';
							}
						}
						?>
					</select>
					<?php wp_nonce_field('add_card','add_card'); ?>
					<input type="submit" name="add_Card" value="<?php _e('Add Card','vibebp') ?>" /></a>
					</form>
				</div>
				<?php
			break;
			default:
				if(in_array($_GET['sub'],array_keys($post_types))){
					include_once('settings/post_card.php');
				}else{
					include_once('settings/member_card.php');	
				}
				
			break;
		}
		
		wp_nonce_field('security','security');
		wp_enqueue_style('vibebp-icons-css',plugins_url('../assets/vicons.css',__FILE__),array(),VIBEBP_VERSION);

	}


	function vibebp_buddypress(){

		echo '<h3>'.__('BuddyPress General Settings','vibebp').'<a href="'.bp_core_get_user_domain( get_current_user_id() ).'?reload_nav=1" class="button-primary" target="_blank">Refresh BuddyPress Navigation</a></h3> ';

		$template_array = apply_filters('vibebp_buddypress_general_settings_tabs',array(
			'general'=> __('General Settings','vibebp'),
			'members'=> __('Members','vibebp'),
		));
		
		if(bp_is_active('groups')){
			$template_array['groups'] = __('Groups','vibebp');
		}

		echo '<ul class="subsubsub">';

		foreach($template_array as $k=>$value){
			if(empty($_GET['sub']) && empty($current)){
				$current = $k;
			}else if(!empty($_GET['sub']) && empty($current)){
				$current = $_GET['sub'];
			}
			echo '<li><a href="?page='.VIBE_BP_SETTINGS.'&tab=bp&sub='.$k.'" '.(($k == $current)?'class="current"':'').'>'.$value.'</a>  &#124; </li>';
		}
		echo '</ul><div class="clear"><hr/>';
		//
		if(!isset($_GET['sub'])){$_GET['sub']='';}

		switch($_GET['sub']){
			case 'groups':
				$init=VibeBP_Group_Type_Settings::init();
				$init->show();
			break;
			case 'members':
				$init=VibeBP_Member_Type_Settings::init();
				$init->show();
			break;
			default:

				$review_options = array(
					'all'=>__('All','vibebp')
				);
				global $wp_roles;
				$roles = array_keys($wp_roles->roles);
				foreach($roles as $role){
					$review_options[$role]=$wp_roles->roles[$role]['name'];
				}

				$settings = apply_filters('vibebp_bp_settings',array(
					array(
						'label'=>__('Additional Components','vibebp' ),
						'type'=> 'heading',
					),
					array(
						'label' => __('Followers','vibebp'),
						'name' => 'bp_followers',
						'type' => 'checkbox',
						'value' => 1,
						'desc' => '',
					),
					array(
						'label' => __('Likes','vibebp'),
						'name' => 'bp_likes',
						'type' => 'checkbox',
						'value' => 1,
						'desc' => '',
					),
					array(
						'label'=>__('Accessibility Settings','vibebp' ),
						'type'=> 'heading',
					),
					array(
						'label' => __('Disable Public Profile','vibebp'),
						'name' => 'public_profile',
						'type' => 'checkbox',
						'value' => 1,
						'desc' => _x('Profiles are accessible on internet by anyone. Switching this on disables public access for profiles.','settings','vibebp'),
					),
					array(
						'label' => __('Disable Public Member Directory','vibebp'),
						'name' => 'public_member_directory',
						'type' => 'checkbox',
						'value' => 1,
						'desc' => _x('Disable member directory acess for public. You can add member directory in profile menu.','settings','vibebp'),
					),
					array(
						'label' => __('Disable Groups & Group Directory','vibebp'),
						'name' => 'public_group_directory',
						'type' => 'checkbox',
						'value' => 1,
						'desc' => _x('Disable group directory access for public. You can add group directory in profile menu.','settings','vibebp'),
					),
					array(
						'label' => __('Disable Public Activities [recommended]','vibebp'),
						'name' => 'public_activity',
						'type' => 'checkbox',
						'value' => 1,
						'desc' => _x('Disable activities for public.','settings','vibebp'),
					),
					array(
						'label'=>__('BuddyPress Settings','vibebp' ),
						'type'=> 'heading',
					),
					array(
						'label' => __('BuddyPress Avatar Full Width (px)','vibebp'),
						'name' => 'bp_avatar_full_width',
						'type' => 'number',
						'desc' => '',
						'default'=>300
					),
					array(
						'label' => __('BuddyPress Avatar Full Height (px)','vibebp'),
						'name' => 'bp_avatar_full_height',
						'type' => 'number',
						'desc' => '',
						'default'=>300
					),
					array(
						'label' => __('BuddyPress Avatar Thumbnail Width (px)','vibebp'),
						'name' => 'bp_avatar_thumb_width',
						'type' => 'number',
						'desc' => '',
						'default'=>150
					),
					array(
						'label' => __('BuddyPress Avatar Thumbnail Height (px)','vibebp'),
						'name' => 'bp_avatar_thumb_height',
						'type' => 'number',
						'desc' => '',
						'default'=>150
					),
				));
				$this->vibebp_settings_generate_form('bp',$settings);
			break;
		}
	}

	function vibebp_general_settings(){
		echo '<h3>'.__('General Settings','vibebp').'</h3>';

		$template_array = apply_filters('vibebp_general_settings_tabs',array(
			'general'=> __('General Settings','vibebp')
		));
		echo '<ul class="subsubsub">';

		foreach($template_array as $k=>$value){
			if(empty($_GET['sub']) && empty($current)){
				$current = $k;
			}else if(!empty($_GET['sub']) && empty($current)){
				$current = $_GET['sub'];
			}
			echo '<li><a href="?page='.VIBE_BP_SETTINGS.'&tab=general&sub='.$k.'" '.(($k == $current)?'class="current"':'').'>'.$value.'</a>  &#124; </li>';
		}
		echo '</ul><div class="clear"><hr/>';
		if(!isset($_GET['sub'])){$_GET['sub']='';}
		switch($_GET['sub']){
			case 'layouts':
				$this->vibebp_layouts();
			break;
			default:
			$settings = apply_filters('vibebp_general_settings',array(
					array(
						'label'=>__('Basic Settings','vibebp' ),
						'type'=> 'heading',
					),
					array(
						'label' => __('Client id','vibebp'),
						'name' => 'client_id',
						'type' => 'text',
						'desc' => __('Client id for all api hits ','vibebp'),
						'default'=>wp_generate_password(16,false),
					),
					array(
						'label' => __('Synchronise WP with VibeBP Login','vibebp'),
						'name' => 'sync_login',
						'type' => 'checkbox',
						'desc' => __('When user logs in WordPress he is also logged in VibeBP','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Token Duration','vibebp'),
						'name' => 'token_duration',
						'type' => 'select',
						'options'=>array(
							604800=>_x('1 Week','setting','vibebp'),
							1800=>_x('30 Minutes','setting','vibebp'),
							3600=>_x('1 Hour','setting','vibebp'),
							21600=>_x('6 Hours','setting','vibebp'),
							43200=>_x('12 Hours','setting','vibebp'),
							86400=>_x('24 Hours','setting','vibebp'),
							''=>_x('Never expires','setting','vibebp'),
						),
						'desc' => __(' User remains logged in without the need for re-login.','vibebp'),
						'default'=>''
					),
					array(
						'label'=>__('Login Settings','vibebp' ),
						'type'=> 'heading',
					),
					array(
						'label' => __('Global Login','vibebp'),
						'name' => 'global_login',
						'type' => 'checkbox',
						'desc' => __('Are you adding login in Menu/Header or on specific page. Global login scripts loaded on entire site.','vibebp'),
						'default'=>'on'
					),
					array(
						'label' => __('Login Heading','vibebp'),
						'name' => 'login_heading',
						'type' => 'textarea',
						'desc' => __('Login screen heading ','vibebp'),
						'default'=>_x('Welcome back','login','vibebp'),
					),
					array(
						'label' => __('Login message','vibebp'),
						'name' => 'login_message',
						'type' => 'textarea',
						'desc' => __('Login message below heading','vibebp'),
						'default'=>_x('Sign in to experience the next generation of WPLMS 4.0.','login','vibebp'),
					),
					array(
						'label' => __('Login Screen Terms','vibebp'),
						'name' => 'login_terms',
						'type' => 'textarea',
						'desc' => __('Terms and Conditions text in login screen ','vibebp'),
						'default'=>'To make VibeThemes work, we log user data and share it with service providers. Click “Sign in” above to accept VibeThemes’s Terms of Service & Privacy Policy.',
					),
					array(
						'label' => __('SignIn Title','vibebp'),
						'name' => 'signin_email_heading',
						'type' => 'text',
						'desc' => __('Title shown on login popup screen ','vibebp'),
						'default'=>'Sign in with email',
					),
					array(
						'label' => __('Sign In Description','vibebp'),
						'name' => 'signin_email_description',
						'type' => 'textarea',
						'desc' => __('Text shown below login title in popup screen ','vibebp'),
						'default'=>'To login enter the email address associated with your account, and the password.',
					),
					array(
						'label'=>__('Forgot Password Settings','vibebp' ),
						'type'=> 'heading',
					),
					array(
						'label' => __('Forgot Password Description','vibebp'),
						'name' => 'forgot_password',
						'type' => 'text',
						'desc' => __('Text shown below Forgot Password title in popup screen ','vibebp'),
						'default'=>'Enter the email address associated with your account, and we’ll send a magic link to your inbox.',
					),
					array(
						'label'=>__('Create Account Settings','vibebp' ),
						'type'=> 'heading',
					),
					array(
						'label' => __('Create Account Title','vibebp'),
						'name' => 'register_account_heading',
						'type' => 'text',
						'desc' => __('Title shown on login popup screen ','vibebp'),
						'default'=>'Join VibeThemes',
					),
					array(
						'label' => __('Create Account Description','vibebp'),
						'name' => 'register_account_description',
						'type' => 'textarea',
						'desc' => __('Text shown below login title in popup screen ','vibebp'),
						'default'=>'Login to connect and check your account, personalize your dashboard, and follow people and chat with them.',
					),
					array(
						'label'=>__('Firebase Project','vibebp' ),
						'type'=> 'heading',
					),
					array(
						'label' => __('Enter Firebase Config','vibebp'),
						'name' => 'firebase_config',
						'type' => 'textarea',
						'desc' => __('Firebase Config for web app. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Disable Live chat','vibebp'),
						'name' => 'disable_firebase_live_chat',
						'type' => 'checkbox',
						'desc' => __('Disable Live chat, Notes and realtime notifications.','vibebp'),
						'default'=>'on'
					),
					array(
						'label' => __('User Brand Icons','vibebp'),
						'name' => 'use_brand_icons',
						'type' => 'checkbox',
						'desc' => __('Use brand icons in site.','vibebp'),
						'default'=>'on'
					),
					array(
						'label' => __('Google Login','vibebp'),
						'name' => 'firebase_google_auth',
						'type' => 'checkbox',
						'desc' => __('Google login via firebase,  make sure Google is enabled as Login method in Firebase auth signin. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Facebook Login','vibebp'),
						'name' => 'firebase_facebook_auth',
						'type' => 'checkbox',
						'desc' => __('Facebook login via firebase,  make sure Facebook is enabled as Login method in Firebase auth signin. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Twitter Login','vibebp'),
						'name' => 'firebase_twitter_auth',
						'type' => 'checkbox',
						'desc' => __('Twitter login via firebase,  make sure Twitter is enabled as Login method in Firebase auth signin. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Github Login','vibebp'),
						'name' => 'firebase_github_auth',
						'type' => 'checkbox',
						'desc' => __('Github login via firebase,  make sure Github is enabled as Login method in Firebase auth signin. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Apple Login','vibebp'),
						'name' => 'firebase_apple_auth',
						'type' => 'checkbox',
						'desc' => __('Apple ID login via firebase,  make sure Apple is enabled as Login method in Firebase auth signin. ','vibebp'),
						'default'=>''
					),
					array(
						'label'=>__('Google Maps','vibebp' ),
						'type'=> 'heading',
					),
					array(
						'label' => __('Google Maps API Key','vibebp'),
						'name' => 'google_maps_api_key',
						'type' => 'text',
						'desc' => __('Get your maps api key ','vibebp'),
						'default'=>''
					),
					array(
						'label'=>__('Service Worker','vibebp' ),
						'type'=> 'heading',
					),
					array(
						'label' => __('Setup Service Workers','vibebp'),
						'name' => 'service_workers',
						'type' => 'checkbox',
						'desc' => __('Setup service workers for offline loading, pre-loading and push notifications. ','vibebp'),
						'default'=>''
					),
				));

			$this->vibebp_settings_generate_form('general',$settings);
			break;
		}
	}	

	function get_layouts(){
		return apply_filters('vibebp_layouts',
			array(
				'members' => array(
						'index' => array(
							'label'=>_x('Members Directory', '', 'vibebp'),
							'value'=>'members_index',
						)
				),
				'activity' => array(
						'index' => array(
							'label'=>_x('Activity Directory', '', 'vibebp'),
							'value'=>'activity_index',
						)
				),
				'xprofile' => array(
						'public' => array(
							'label'=>_x('Public Profile', '', 'vibebp'),
							'value'=>'public_profile',
						),
						'private' => array(
							'label'=>_x('Private Profile', '', 'vibebp'),
							'value'=>'private_profile',
						)
				),
				'groups' => array(
						'index' => array(
							'label'=>_x('Groups Directory', '', 'vibebp'),
							'value'=>'groups_index',
						)
				),
			)
		);
	}

	/*
		Layout Connections
	*/
	function get_layout_options($parent,$key){
		
		$args = array(
		  'numberposts' => 999,
		  'post_type'   => 'layouts'
		);
		$options_html = '';
		$layouts = get_posts( $args );
		$option = get_option('vibebp_layout_connections');
		
		if ( !empty($layouts )) {
			foreach ($layouts  as $key => $l) {
				$selected = '';
		    	if(!empty($option) && !empty($option[$parent]) && !empty($option[$parent][$key])){
		    		$selected = 'selected="selected"';
		    	}
		    	
		        $options_html .= '<option value="'.$l->ID.'" '.$selected.'>' . $l->post_title . '</option>';
			}
	    	
		}
		wp_reset_postdata();
		return $options_html;
	}

	function vibebp_layouts(){
		$layouts = $this->get_layouts();

		foreach ($layouts as $key => $layout) {
			echo '<h3>'.ucfirst($key).'</h3>';
			echo '<ul>';

			foreach ($layout as $k => $l) {
				echo '<li><label>'.$l['label'].'</label>
				<select  name="'.$key.'['.$k.']'.'['.$l['value'].']'.'">
				'.$this->get_layout_options($key,$k).'
				</select>
				</li>';
			}
			echo '</ul>';
		}
	}


	function vibebp_settings_generate_form($tab,$settings){

		if(empty($settings))
			return; 

		
		echo '<form method="post">';
		wp_nonce_field('vibebp_settings','_wpnonce');
		echo '<table class="form-table">
				<tbody>';

		$vibebp_settings=get_option(VIBE_BP_SETTINGS);

		foreach($settings as $setting ){
			echo '<tr valign="top" '.(empty($setting['class'])?'':'class="'.$setting['class'].'"').'>';
			switch($setting['type']){
				case 'heading':
					echo '<th scope="row" class="titledesc" colspan="2"><h3>'.$setting['label'].'</h3></th>';
				break;
				case 'link':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><a href="'.$setting['value'].'" class="button">'.$setting['button_label'].'</a>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'select':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><select name="'.$setting['name'].'">';
					foreach($setting['options'] as $key=>$option){
						echo '<option value="'.$key.'" '.(isset($vibebp_settings[$tab][$setting['name']])?selected($key,$vibebp_settings[$tab][$setting['name']]):'').'>'.$option.'</option>';
					}
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'multiselect':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><select name="'.$setting['name'].'[]" multiple>';
					foreach($setting['options'] as $key=>$option){
						echo '<option value="'.$key.'" '.(isset($vibebp_settings[$tab][$setting['name']])?(in_array($key,$vibebp_settings[$tab][$setting['name']])?'selected="selected"':''):'').'>'.$option.'</option>';
					}
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'registration_forms':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><select name="'.$setting['name'].'"><option>'.__('Select registration form','vibebp').'</option>';

					$forms = get_option('vibebp_registration_forms');
					if(!empty($forms)){
						foreach($forms as $key=>$option){
						echo '<option value="'.$key.'" '.(isset($vibebp_settings[$tab][$setting['name']])?selected($key,$vibebp_settings[$tab][$setting['name']]):'').'>'.$key.'</option>';
						}
					}
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'checkbox':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><input type="checkbox" name="'.$setting['name'].'" '.(isset($vibebp_settings[$tab][$setting['name']])?'CHECKED':'').' />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'number':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><input type="number" name="'.$setting['name'].'" value="'.(isset($vibebp_settings[$tab][$setting['name']])?$vibebp_settings[$tab][$setting['name']]:(isset($setting['default'])?$setting['default']:'')).'" />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'cptselect':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp">';
					echo '<select name="'.$setting['name'].'"><option value="">'.__('Select','vibebp').' '.$setting['cpt'].'</option>';
					global $wpdb;
					$cpts = '';
					if($setting['cpt']){
						$cpts = $wpdb->get_results("
							SELECT ID,post_title 
							FROM {$wpdb->posts} 
							WHERE post_type = '".$setting['cpt']."' 
							AND post_status='publish' 
							ORDER BY post_title DESC LIMIT 0,999");	
					}
					if(is_array($cpts)){
						foreach($cpts as $cpt){
							echo '<option value="'.$cpt->ID.'" '.((isset($vibebp_settings[$tab][$setting['name']]) && $vibebp_settings[$tab][$setting['name']] == $cpt->ID)?'selected="selected"':'').'>'.$cpt->post_title.'</option>';
						}
					}
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'title':
					echo '<th scope="row" class="titledesc"><h3>'.$setting['label'].'</h3></th>';
					echo '<td class="forminp"><hr /></td>';
				break;
				case 'taxonomy':
					if(empty($this->taxonomy[$setting['taxonomy']])){
						$this->taxonomy[$setting['taxonomy']]=get_terms( array(
						    'taxonomy' => $setting['taxonomy'],
						    'hide_empty' => false,
						) );
					}
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>'.$tab.' = '.$setting['name'].' -> ';
					

					echo '<td class="forminp forminp-color"><select name="'.$setting['name'].'" >';
					if(!empty($this->taxonomy[$setting['taxonomy']])){
						foreach($this->taxonomy[$setting['taxonomy']] as $term){
							echo '<option value="'.$term->slug.'" '.(($vibebp_settings[$tab][$setting['name']] == $term->slug)?'selected':'').'>'.$term->name.'</option>';
						}
					}
					echo '</select><span>'.$setting['desc'].'</span></td>';
				break;
				case 'color':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp forminp-color"><input type="text" name="'.$setting['name'].'" class="colorpicker" value="'.(isset($vibebp_settings[$tab][$setting['name']])?$vibebp_settings[$tab][$setting['name']]:'').'" />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'upload':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					$url =0;

					if(!empty($vibebp_settings[$tab][$setting['name']])){
						$url = wp_get_attachment_image_src($vibebp_settings[$tab][$setting['name']],'full');
					}
					
					echo '<td class="forminp forminp-upload">'.($url?'<img src="'.$url[0].'" class="upload_image_button" button_label="'.$setting['button_label'].'" input-name="'.$setting['name'].'" /><input type="hidden" name="'.$setting['name'].'" value='.$vibebp_settings[$tab][$setting['name']].' /><span class="dashicons dashicons-no remove_uploaded"></span>':'').'<a class="button upload_image_button" input-name="'.$setting['name'].'" uploader-title="'.$setting['button_title'].'" style="'.($url?'display:none;':'').'">'.$setting['button_label'].'</a>';
					echo '<span>'.$setting['desc'].'</span></td>';					
				break;
				case 'hidden':
					echo '<td><input type="hidden" name="'.$setting['name'].'" value="1"/></td>';
				break;
				case 'bp_setup_nav':
					$nav = bp_get_nav_menu_items();
					update_option('bp_setup_nav',bp_get_nav_menu_items());
				break;
				case 'repeatable':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><a class="add_new_repeatable button-primary" data-name="'.$setting['name'].'[]" data-placeholder="'.$setting['placeholder'].'">'.__('Add New','vibebp').'</a><ul>';
					if(!empty($vibebp_settings[$tab][$setting['name']])){
						foreach($vibebp_settings[$tab][$setting['name']] as $k=>$item){
							echo '<li><strong>'.$item.'</strong><span class="dashicons dashicons-no-alt remove_item"></span></li>';	
						}
						
					}
					echo '</ul><span>'.$setting['desc'].'</span></td>';
					add_action('admin_footer',array($this,'repeatable_script'));
					
				break;
				case 'textarea':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><textarea name="'.$setting['name'].'">'.(isset($vibebp_settings[$tab][$setting['name']])?$vibebp_settings[$tab][$setting['name']]:(isset($setting['default'])?$setting['default']:'')).'</textarea>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				default:
					$setting['value']=$vibebp_settings[$tab][$setting['name']];
					$html = apply_filters('vibebp_settings_type',0,$setting);
					if(empty($html)){
						echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
						echo '<td class="forminp"><input type="text" name="'.$setting['name'].'" value="'.(isset($vibebp_settings[$tab][$setting['name']])?$vibebp_settings[$tab][$setting['name']]:(isset($setting['default'])?$setting['default']:'')).'" />';
						echo '<span>'.$setting['desc'].'</span></td>';	
					}
					
				break;
			}
			
			echo '</tr>';
		}
		echo '</tbody>
		</table>';

		wp_enqueue_media();
		?>
		<script>
			jQuery(document).ready(function($){
				$('.remove_uploaded').on('click',function(){
					$(this).parent().find('img').remove();
					$(this).parent().find('input').remove();
					$(this).parent().find('.upload_image_button').show();
					$(this).remove();
				});
				
				var media_uploader=[];
				jQuery('.upload_image_button').on('click', function( event ){
				  
				    var button = jQuery( this );
				    var input_name = button.attr( 'input-name' );

				    if ( media_uploader[input_name]) {
				      media_uploader[input_name].open();
				      return;
				    }
				    // Create the media uploader.
				    media_uploader[input_name] = wp.media.frames.media_uploader = wp.media({
				        title: button.attr( 'uploader-title' ),
				        // Tell the modal to show only images.
				        library: {
				            type: 'image',
				            query: false
				        },
				        button: {
				            text: button.attr( 'button_label' ),
				        },
				        multiple: false
				    });

				    // Create a callback when the uploader is called
				    media_uploader[input_name].on( 'select', function() {
			        	var selection = media_uploader[input_name].state().get('selection');
			            
			            selection.map( function( attachment ) {
				            attachment = attachment.toJSON();

				            var url_image='';
				            if( attachment.sizes){
				                if(   attachment.sizes.thumbnail !== undefined  ) url_image=attachment.sizes.thumbnail.url; 
				                else if( attachment.sizes.medium !== undefined ) url_image=attachment.sizes.medium.url;
				                else url_image=attachment.sizes.full.url;
				            }
				            
					        if(button.prop('tagName') == 'IMG'){
					        	button.attr('src',url_image);
					        	button.parent().find('input[name="'+input_name+'"]').val(attachment.id);
					        }else{
					        	button.html('<img src="'+url_image+'" class="submission_thumb thumbnail" /><input id="'+input_name+'" class="post_field" data-type="featured_image" data-id="'+input_name+'" name="'+input_name+'" type="hidden" value="'+attachment.id+'" />');	
					        }
				            
			         	});

				    });
				    // Open the uploader
				    media_uploader[input_name].open();
				  });
			});
			</script>
		<?php
		if(!empty($settings))
			echo '<input type="hidden" name="tab" value="'.$tab.'" /><input type="submit" name="save" value="'.__('Save Settings','vibebp').'" class="button button-primary" /></form>';
	}

	function vibebp_save_settings($tab){
		if ( !empty($_POST) && check_admin_referer('vibebp_settings','_wpnonce') ){
			$vibebp_settings=array();

			$vibebp_settings = get_option(VIBE_BP_SETTINGS);	
			
			unset($_POST['_wpnonce']);
			unset($_POST['_wp_http_referer']);
			unset($_POST['save']);
			if(empty($tab)){
				$tab = apply_filters('vibebp_save_tab','general',$_POST);
			}

			

			switch($tab){
				case 'bp':
					$vibebp_settings['bp'] = $_POST;
				break;
				default:
				if(!empty($_POST['firebase_config'])){
					$firebase_config = $_POST['firebase_config'];
					if(!is_serialized($firebase_config)){
						$firebase_config = str_replace('{','{"',str_replace(',',',"',str_replace(': ','":',$firebase_config)));
						$firebase_config = stripslashes(preg_replace('/\s\s+/', '', str_replace(' ','',$firebase_config)));
						$_POST['firebase_config']=serialize(json_decode($firebase_config,true));
					}else{
						$_POST['firebase_config']=stripslashes($firebase_config);
					}
				}
				if(is_array($_POST)){
					foreach($_POST as $k=>$v){
						if(vibe_isJson(stripslashes($v))){
							$_POST[$k]=json_decode(stripslashes($v));
						}
					}
				}

				if(!empty($_GET['sub'])){
					$vibebp_settings[$tab][$_GET['sub']] = apply_filters('vibebp_save_settings',$_POST,$tab);  
				}else{
					$vibebp_settings[$tab] = apply_filters('vibebp_save_settings',$_POST,$tab);  					
				}

				break;
			}

			//print_r($vibebp_settings);
			update_option(VIBE_BP_SETTINGS,$vibebp_settings);

			echo '<div class="updated"><p>'.__('Settings Saved','vibebp').'</p></div>';
		}else{
			echo '<div class="error"><p>'.__('Unable to Save settings','vibebp').'</p></div>';
		}
	}
	
	function save_member_card(){

		if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') ){
	         die();
      	}
		if(!current_user_can('manage_options') || empty($_POST['card'])){
			die();
		}

		update_option('member_card',$_POST['card']);

		die();
	}


	function show_app_form(){
		
		if(!empty($_POST)){
			
			if(wp_verify_nonce($_POST['security'],'security')){
				echo '<div id="message" class="fade updated"><p>'.__('App request VibeThemes','vibebp').'</p></div>';
			}
		}
		?>
		<h2><?php _e('Build your Mobile App','vibebp');?></h2>
		<form method="post">
			<ul id="app_form">
				<li><strong><?php _e('App Icon','vibebp'); ?></strong><span><input type="file" name="app_icon" /></span></li>
				<li><strong><?php _e('App Splash Image','vibebp'); ?></strong><span><input type="file" name="app_splash" /></span>
				</li>
			</ul>
			<?php wp_nonce_field('security','security'); ?>
			<input type="submit" class="button-primary" value="<?php _e('Submit','vibebp'); ?>" />
		</form>
		<style>#app_form li{display:flex;}#app_form li strong{min-width:200px;}#app_form li span{flex:1}</style>
		<?php

	}

	function service_workers(){
		
		$template_array = apply_filters('vibebp_service_worker_settings_tabs',array(
			'general'=> __('General Settings','vibebp'),
			'background_sync'=> __('Background Sync','vibebp'),
			'push_notification'=> __('Push Notifications','vibebp'),
		));
		echo '<h3>'.__('Service Workers','vibebp').(file_exists($_SERVER['DOCUMENT_ROOT'].'/firebase-messaging-sw.js')?'<a class="button-primary generate_service_worker">'.__('Regenerate Service Worker','vibebp').'</a>':'<a class="button-primary generate_service_worker">'.__('Generate Service Worker','vibebp').'</a>').'</h3>';


		echo '<ul class="subsubsub">';
		foreach($template_array as $k=>$value){
			if(empty($_GET['sub']) && empty($current)){
				$current = $k;
			}else if(!empty($_GET['sub']) && empty($current)){
				$current = $_GET['sub'];
			}
			echo '<li><a href="?page='.VIBE_BP_SETTINGS.'&tab=service&sub='.$k.'" '.(($k == $current)?'class="current"':'').'>'.$value.'</a>  &#124; </li>';
		}
		echo '</ul><div class="clear"><hr/>';

		if(empty($_GET['sub'])){$_GET['sub']='general';}

		if(empty($this->pages)){
			$query = new WP_Query(array(
				'post_type'=>'page',
				'posts_per_page'=>-1
			));
			while($query->have_posts()){
				$query->the_post();
				$this->pages[get_the_ID()]=get_the_title();
			}
		}
		switch($_GET['sub']){
			case 'push_notification':
				if(!class_exists('WPLMS_Push_Notifications_Init')){
					?>
					<div id="message" class="warning fade">
						<p><?php _e('Push Notification addon required !','vibebp'); ?></p>
					</div>
					<?php
				}
			break;
			case 'background_sync':
				?>
					<div id="message" class="warning fade">
						<p><?php _e('Coming up.','vibebp'); ?></p>
					</div>
					<?php
			break;
			default:

			$service_worker_settings = apply_filters('vibebp_service_workers_general_settings',array(
					array(
						'label' => __('Version','vibebp'),
						'name' => 'version',
						'type' => 'text',
						'default' => '0.0001',
						'desc' => __('Service Worker Version. Updates service workers, clears out cached scripts and other API data.','vibebp'),
					),
					array(
						'label' => __('App Name','vibebp'),
						'name' => 'app_name',
						'type' => 'text',
						'default' => get_bloginfo('name'),
						'desc' => __('App name when users download on desktop','vibebp'),
					),
					array(
						'label' => __('App Shortname','vibebp'),
						'name' => 'app_short_name',
						'type' => 'text',
						'default' => get_bloginfo('name'),
						'desc' => __('App name when users download on desktop','vibebp'),
					),
					array(
						'label' => __('App description','vibebp'),
						'name' => 'app_description',
						'type' => 'textarea',
						'desc' => __('App description when users download on desktop','vibebp'),
						'default'=>get_bloginfo('description'),
					),
					array(
						'label' => __('App Icon','vibebp'),
						'name' => 'app_icon',
						'type' => 'upload',
						'desc' => __('Recommended Size 512x512','vibebp'),
						'button_label'=>__('Set App Icon','vibebp'),
						'button_title'=>__('Select App Icon Image','vibebp').'<a href="https://developer.apple.com/design/human-interface-guidelines/ios/icons-and-images/app-icon/"><span class="dashicons dashicons-editor-help"></span></a>',
					),
					array(
						'label' => __('Default Image','vibebp'),
						'name' => 'default_image',
						'type' => 'upload',
						'button_label'=>__('Set default image','vibebp'),
						'button_title'=>__('Select Fallback Image','vibebp'),
						'desc' => __('Fallback image when image not available in offline mode.','vibebp'),
					),
					array(
						'label' => __('App Screenshot','vibebp'),
						'name' => 'app_screenshot',
						'type' => 'upload',
						'button_label'=>__('Set Splash Screen','vibebp'),
						'button_title'=>__('Select Splash Screen Image','vibebp'),
						'desc' => __('Recommended Size 2732x2732','vibebp').'<a href="https://developer.apple.com/design/human-interface-guidelines/ios/visual-design/launch-screen/"><span class="dashicons dashicons-editor-help"></span></a>',
					),
					array(
						'label' => __('Offline Page URL [Required]','vibebp'),
						'name' => 'offline_page',
						'type' => 'select',
						'options'=>$this->pages,
						'desc' => __('App home, this is cached. Set to User profile or custom page with VibeBp profile shortcode to enable app in offline mode','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Pre-Cache Resources','vibebp'),
						'name' => 'pre-cached',
						'type' => 'repeatable',
						'placeholder'=>__('Enter Script/Style URL','vibebp'),
						'desc' => __('Additional scripts which need to be precached. All VibeBP & Addon scripts are cached by default.','vibebp'),
						'default'=>''
					),
				)
	);
			$this->vibebp_settings_generate_form('service_worker',$service_worker_settings);
			break;
		}
		
	}

	function regenerate_service_worker(){

		if(wp_verify_nonce($_POST['security'],'security')){
			$actions = VibeBP_Actions::init();
			$actions->generate_manifest(1);
			$actions->install_sw(1);
			echo json_encode(array('status'=>1,'message'=>__('Successfully regenerated','vibebp')));
		}
		die();
	}

	function repeatable_script(){
		?>
		<script>
			jQuery(document).ready(function($){
				$('.add_new_repeatable').on('click',function(){
					$(this).parent().find('ul').append('<li><input type="text" name="'+$(this).attr('data-name')+'" placeholder="'+$(this).attr('data-placeholder')+'" /><span class="dashicons dashicons-no-alt remove_item"></span></li>');
					$('.remove_item').on('click',function(){
						$(this).parent().remove();
					});
				});
				$('.remove_item').on('click',function(){
					$(this).parent().remove();
				});

				$('.generate_service_worker').on('click',function(){
					let $this = $(this);
					let text = $this.text();
					$this.text('...');
					$(this).addClass('disabled');
					$.ajax({
			          	type: "POST",
			          	url: ajaxurl,
			          	dataType:'json',
			          	data: { action: 'regenerate_service_worker',
			                  security:'<?php echo wp_create_nonce('security','security'); ?>',
			                },
			          	cache: false,
			          	success: function (json) {
			            	if(json.status){
			            		$this.text(json.message);
			            		setTimeout(function(){
			            			$this.text(text);
			            			$this.removeClass('disabled');
			            		},4000);
			            	}
			          	}
			        });
				})
			});
		</script><style>.forminp img{max-width:320px;}</style>
		<?php
	}

	function member_profile_card() {
	    add_meta_box( 'member_type_selector', __( 'Apply on Member Type', 'vibebp' ), array($this,'member_type_selector'), 'member-profile','side' );
	    add_meta_box( 'member_type_selector', __( 'Apply on Member Type', 'vibebp' ), array($this,'member_type_selector'), 'member-card' ,'side');

	    //Add meta box selection in User extended profile
		
	}

	function user_metabox($true,$user_id){
		$screen_id = get_current_screen()->id;
		add_meta_box( 'member_profile_selector', __( 'Select Member Profile Layout', 'vibebp' ), array($this,'member_profile_selector'), $screen_id,'side' );
	}
	function member_profile_selector($user = null){

		// Bail if no user ID.
		if ( empty( $user->ID ) ) {
			return;
		}
		
		$profile_layout = get_user_meta($user->ID,'member_profile',true);
		?>
		<label for="bp-members-profile-member-type" class="screen-reader-text"><?php
			/* translators: accessibility text */
			esc_html_e( 'Select Member Profile Layout', 'vibebp' );
		?></label>
		<select name="member_profile">
			<option value=""><?php _ex('Select Member Profile','wplms'); ?></option>
			<?php
			$query = new WP_Query(array(
				'post_type'=>'member-profile',
				'posts_per_page'=>-1
			));
			if($query->have_posts()){
				while($query->have_posts()){
					$query->the_post();
					echo '<option value="'.get_the_ID().'" '.($profile_layout == get_the_ID()?'selected':'').'>'.get_the_title().'</option>';
				}
			}
			?>
		</select>
		<?php
		wp_nonce_field( 'bp-member-profile-change-' . $user->ID, 'bp-member-profile-nonce' );
	}

	function process_member_profile_update(){

		if ( ! isset( $_POST['bp-member-profile-nonce'] ) || ! isset( $_POST['member_profile'] ) ) {
			return;
		}
		
		$user_id = (int) get_current_user_id();

		// We'll need a user ID when not on self profile.
		if ( ! empty( $_GET['user_id'] ) ) {
			$user_id = (int) $_GET['user_id'];
		}
		

		check_admin_referer( 'bp-member-profile-change-' . $user_id, 'bp-member-profile-nonce' );

		// Permission check.
		if ( ! bp_current_user_can( 'bp_moderate' ) && $user_id != bp_loggedin_user_id() ) {
			return;
		}

		
		// Member type string must either reference a valid member type, or be empty.
		$member_profile = stripslashes( $_POST['member_profile']);
		update_user_meta($user_id,'member_profile',$member_profile);
	}

	function group_layout_card(){
		add_meta_box( 'group_type_selector', __( 'Apply on Group Type', 'vibebp' ), array($this,'group_type_selector'), 'group-layout','side' );
	    add_meta_box( 'group_type_selector', __( 'Apply on Group Type', 'vibebp' ), array($this,'group_type_selector'), 'group-card','side' );
	}
	function member_type_selector(){
		$types = bp_get_member_types(array(),'objects');
		global $post;
		$selected_type = get_post_meta($post->ID,'member_type',true);
		?>
		<select name="member_type">
			<option value=""><?php _ex('Select Member Type','wplms'); ?></option>
			<?php
				if(!empty($types)){
					
					foreach($types as $type => $labels){
						echo '<option value="'.$type.'" '.($selected_type == $type?'selected':'').'>'.$labels->labels['name'].'</option>';	
					}
					
				}
			?>
		</select>
		<?php
		
	}

	function group_type_selector(){

		if(!function_exists('bp_groups_get_group_types'))
			return;
		$types = bp_groups_get_group_types(array(),'objects');
		global $post;
		$selected_type = get_post_meta($post->ID,'group_type',true);
		?>
		<select name="group_type">
			<option value=""><?php _ex('Select Group Type','wplms'); ?></option>
			<?php
				if(!empty($types)){
					
					foreach($types as $type => $labels){
						echo '<option value="'.$type.'" '.($selected_type == $type?'selected':'').'>'.$labels->labels['name'].'</option>';	
					}
					
				}
			?>
		</select>
		<?php
	}

	function save_member_profile_card($post_id){
		if(!empty($_POST['member_type']) && current_user_can('manage_options')){
			update_post_meta($post_id,'member_type',sanitize_title($_POST['member_type']));
		}
	}

	function save_group_layout_card(){
		if(!empty($_POST['group_type']) && current_user_can('manage_options')){
			update_post_meta($post_id,'group_type',sanitize_title($_POST['group_type']));
		}
	}

	function set_group_layout(){
		add_meta_box( 'bp_group_layout_settings', _x( 'Group Layout', 'group admin edit screen', 'wplms' ), array($this,'show_group_layouts'), get_current_screen()->id, 'side', 'core' );
	}
	function show_group_layouts($item){
		// Bail if no user ID.
		if ( empty( $item->id ) ) {
			return;
		}
		
		$group_layout = groups_get_groupmeta($item->id,'group_layout',true);
		?>
		<label for="bp-group-layouts" class="screen-reader-text"><?php
			/* translators: accessibility text */
			esc_html_e( 'Select Group Layout', 'vibebp' );
		?></label>
		<select name="group_layout">
			<option><?php _ex('Select Group Layout','wplms'); ?></option>
			<?php
			$query = new WP_Query(array(
				'post_type'=>'group-layout',
				'posts_per_page'=>-1
			));
			if($query->have_posts()){
				while($query->have_posts()){
					$query->the_post();
					echo '<option value="'.get_the_ID().'" '.($group_layout == get_the_ID()?'selected':'').'>'.get_the_title().'</option>';
				}
			}
			?>
		</select>
		<?php
		wp_nonce_field( 'bp-group-layout-change-' . $item->id, 'bp-group-layout-nonce' );
	}

	function bp_groups_process_group_layout_update( $group_id ) {
		if ( ! isset( $_POST['bp-group-layout-nonce'] ) ) {
			return;
		}

		check_admin_referer( 'bp-group-layout-change-' . $group_id, 'bp-group-layout-nonce' );

		// Permission check.
		if ( ! bp_current_user_can( 'bp_moderate' ) ) {
			return;
		}

		$group_layout = ! empty( $_POST['group_layout'] ) ? wp_unslash( $_POST['group_layout'] ) : array();

		groups_update_groupmeta($group_id,'group_layout',$group_layout);
	}

}



VibeBP_Settings::init();

if(!function_exists('vibe_isJson')){
	function vibe_isJson($string) {
	 	json_decode($string);
	 	return (json_last_error() == JSON_ERROR_NONE);
	}
}

