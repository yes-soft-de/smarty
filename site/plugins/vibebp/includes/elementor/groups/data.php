<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Group_Data extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{



    public function get_name() {
		return 'group_data';
	}

	public function get_title() {
		return __( 'Group Data', 'vibebp' );
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
					'group_status' =>__('Group Status','vibebp'),
					'last_active' =>__('Last Active','vibebp'),
					'create_date' =>__('Creation Date','vibebp'),
					'last_status_update' =>__('Last Status update','vibebp'),
					'moderator_count' =>__('Moderator Count','vibebp'),
					'admin_count' =>__('Administrator Count','vibebp'),
					'member_count' =>__('Member Count','vibebp'),
				);

		
		$this->add_control(
			'data',
			[
				'label' => __( 'Group Data', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => apply_filters('vibebp_elementor_group_data',$profile_data)
			]
		);

	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$init = VibeBP_Init::init();

		if(empty($group_id)){
			
			if(!empty($init->group_id)){
				$group_id = $init->group_id;
			}
		}

		if(empty($group_id) && empty($_GET['action'])){
			
			if(empty($init->group_id)){
				global $wpdb,$bp;
				$group_id = $wpdb->get_var("SELECT id FROM {$bp->groups->table_name} LIMIT 0,1");
				$init->group_id = $group_id;
			}else{
				$group_id = $init->group_id;
			}
		}

		if($init->group->id == $group_id){
			$group = $init->group;
		}else if(empty($init->group)){
			$init->group = groups_get_group($group_id);
			$group = $init->group;
		}

        echo '<div class="group_data_field">';
        
        echo $this->get_group_data($settings['data'],$group_id);
        echo '</div>';
	}

	function get_group_data($type,$group_id){

		
		switch($type){
			case 'last_active':

				$time = groups_get_groupmeta( $group_id, 'last_activity',true);
				
				echo bp_core_time_since($time);
			break;
			case 'create_date':
				echo bp_get_group_date_created();
			break; 
			case 'creator_name':
				echo bp_get_group_creator_username();
			break;
			case 'last_status_update':
			
				$activity = bp_get_user_last_activity($user_id);
				echo $activity->content;
			break; 
			case 'admin_count':
				echo count( groups_get_group_admins( $group_id ) );
			break; 
			case 'moderator_count':
				echo count( groups_get_group_mods( $group_id ) );
			break; 
			case 'member_count':
				echo bp_get_group_total_members();
			break;
			case 'group_status':
				echo bp_get_group_status( $group_id );
			break;
			default:
				do_action('vibebp_get_group_data',$type,$group_id);
			break;
		}
	}
}