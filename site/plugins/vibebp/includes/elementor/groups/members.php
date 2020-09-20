<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Groups_Members extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{

    public function get_name() {
		return 'group_members';
	}

	public function get_title() {
		return __( 'Group Members', 'vibebp' );
	}

	public function get_icon() {
		return 'dashicons dashicons-businessman';
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

		
		
		$this->add_control(
			'style',
			[
				'label' => __( 'Group Role', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''=>__('All','vibebp'),
					'members' =>__('Members [Excluding Admin & Mods]','vibebp'),
					'mods' =>__('Moderators','vibebp'),
					'admin' =>__('Administrators','vibebp'),
				)
			]
		);

		$this->add_control(
			'style',
			[
				'label' => __( 'Style', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					'' =>__('Default','vibebp'),
					'names' =>__('Names','vibebp'),
					'pop_names' =>__('Names Popup','vibebp'),
				)
			]
		);
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		if(bp_get_current_group_id()){
			$group_id = bp_get_current_group_id();
		}else{
			global $groups_template;
			if(!empty($groups_template->group)){
				$group_id = $groups_template->group->id;
			}
		}
		if(empty($group_id)){
			$init = VibeBP_Init::init();
			if(!empty($init->group_id)){
				$group_id = $init->group_id;
			}
		}

		if(empty($group_id) && empty($_GET['action'])){
			$init = VibeBP_Init::init();
			if(empty($init->group_id)){
				global $wpdb,$bp;
				$group_id = $wpdb->get_var("SELECT id FROM {$bp->groups->table_name} LIMIT 0,1");
				$init->group_id = $group_id;
			}else{
				$group_id = $init->group_id;
			}
		}

		
		/*
		'group_id'            => bp_get_current_group_id(),
		'per_page'            => false,
		'page'                => false,
		'exclude_admins_mods' => true,
		'exclude_banned'      => true,
		'exclude'             => false,
		'group_role'          => array(),
		'search_terms'        => false,
		'type'                => 'last_joined',
		*/

		$members = groups_get_group_members(array(
			'group_id'=>$group_id,
			'per_page'=>$args['per_page'],
			'page'	  => $args['page'],
			'group_role'=> $args['role'],
		));
		
        echo '<div class="vibebp_group_members '.$style.'"> ';
        if(!empty($members)){
        	foreach($members as $member){
        		echo '<div class="vibebp_member">';
        		echo '<a href="'.bp_core_get_user_domain($member->id).'"><img src="'.bp_core_fetch_avatar(array('item_id'=>$member->id,'object'  => 'user','type'=>'full','html'    => false)).'" ></a>';
        		echo '<span>'.$member->display_name.'</span>';
        		echo '</div>';
        	}
        }
        echo ' </div>';
	}

}