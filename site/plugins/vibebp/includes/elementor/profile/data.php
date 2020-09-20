<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Profile_Data extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{



    public function get_name() {
		return 'member_profile_data';
	}

	public function get_title() {
		return __( 'Member Profile Data', 'vibebp' );
	}

	public function get_icon() {
		return 'vicon vicon-direction';
	}

	public function get_categories() {
		return [ 'vibebp' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Controls', 'vibebp' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		

		$profile_data = array(
					'member_type' =>__('Member Type','vibebp'),
					'last_active' =>__('Last Active','vibebp'),
					'time_in_site' =>__('Register Time','vibebp'),
					'last_status_update' =>__('Last Status update','vibebp'),
				);

		if(bp_is_active('friends')){
			$profile_data['count_friends'] =__('Friends Count','vibebp');
		}
		if(bp_is_active('groups')){
			$profile_data['count_groups'] = __('Group Count','vibebp');
		}
		if(vibebp_get_setting('bp_followers','bp')){
			$profile_data['count_followers'] = __('Followers Count','vibebp');
			$profile_data['count_following'] = __('Following Count','vibebp');
		}

		$this->add_control(
			'data',
			[
				'label' => __( 'Profile Data', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => apply_filters('vibebp_elementor_profile_data',$profile_data)
			]
		);

		$this->add_control(
			'font_size',
			[
				'label' =>__('Font Size', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 48,
					'max' => 100,
					'step' => 1,
				],
				'default' => [
					'size'=>12,
				],
				'selectors' => [
					'{{WRAPPER}} .profile_data_field' => 'font-size: {{SIZE}}px',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .profile_data_field' => 'color: {{VALUE}}',
				],
			]
		);
		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			$user_id = get_current_user_id();
		}

        echo '<span class="profile_data_field '.(isset($settings['style'])?$settings['style']:'').'" style="'.(isset($settings['text_color'])?'color:'.$settings['text_color']:'').';'.(isset($settings['font_size'])?'font-size:'.$settings['font_size']['size'].'px':'').'">'.$this->get_profile_data($settings['data']).'</span>';
	}

	function get_profile_data($type){

		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			global $members_template;
			if(!empty($members_template->member)){
				$user_id = $members_template->member->id;
			}
		}
		if(empty($user_id)){
			$init = VibeBP_Init::init();
			if(!empty($init->user_id)){
				$user_id = $init->user_id;
			}else{
				$user_id = get_current_user_id();
			}
		}
		
		
		switch($type){
			case 'member_type':
				$mtype =  bp_get_member_type($user_id);
				$member_type_object = bp_get_member_type_object( $mtype );
				if(is_object($member_type_object)){
					return $member_type_object->labels['singular_name'];	
				}else{
					return '';
				}
			break;
			case 'last_active':
				$activity = bp_get_user_last_activity($user_id);

				return bp_core_time_since($activity->date_recorded);
			break;
			case 'time_in_site':
				echo bp_core_time_since(get_userdata($user_id)->user_registered);
			break; 
			case 'last_status_update':
				$activity = bp_get_user_last_activity($user_id);
				return $activity->content;
			break; 
			case 'count_friends':
				if ( bp_is_active( 'friends' ) ) {
					return BP_Friends_Friendship::total_friend_count( $user_id );
				}
			break; 
			case 'count_groups':
				if ( bp_is_active( 'groups' ) ) {
					return BP_Groups_Member::total_group_count( $user_id );
				}
			break;
			case 'count_followers':
				global $wpdb;
				$count = $wpdb->get_var($wpdb->prepare("
    			SELECT count(user_id) 
    			FROM {$wpdb->usermeta}
    			WHERE meta_key ='vibebp_follow' 
    			AND meta_value = %d",
    			$user_id));
				return intval($count);
			break;
			case 'count_following':
				$following = get_user_meta($user_id,'vibebp_follow',false);
				return count($following);
			break;
			default:
				ob_start();
				do_action('vibebp_profile_get_profile_data',$type,$user_id);
				return ob_get_clean();
			break;
		}
	}
}