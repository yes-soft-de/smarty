<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Friends extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{


    public function get_name() {
		return 'member_friends';
	}

	public function get_title() {
		return __( 'Member Profile Friends', 'vibebp' );
	}

	public function get_icon() {
		return 'vicon vicon-comments-smiley';
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

		$groups = array();
		$fields = array();
		if(function_exists('bp_xprofile_get_groups')){
			$groups = bp_xprofile_get_groups( array(
				'fetch_fields' => true
			) );
		}
		
		$this->add_control(
			'friends_count',
			[
				'label' => __( 'Show Total friend count', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibebp' ),
						'icon' => 'fa fa-x',
					],
					'1' => [
						'title' => __( 'Yes', 'vibebp' ),
						'icon' => 'fa fa-check',
					],
				],
			]
		);
		
		$this->add_control(
			'show_friends',
			[
				'label' =>__('Show Friends', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 0,
					'max' => 32,
					'step' => 1,
				],
				'default' => [
					'size'=>1,
				]
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __( 'Sort by', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					'' =>__('Active','vibebp'),
					'newest' =>__('Recently Added','vibebp'),
					'alphabetical' =>__('Alphabetical','vibebp'),
					'random'=>__('Random','vibebp'),
					'popular'=>__('Popular','vibebp'),
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
					'pop_names' =>__('Hover Names','vibebp'),
				)
			]
		);

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings_for_display();
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

		
		$run = bp_core_get_users( array(
				'type'         => $settings['order'],
				'per_page'     => $settings['show_friends']['size'],
				'user_id'      => $user_id,
			) );

		if( $run['total'] ){

			foreach($run['users'] as $key=>$user){
				$run['users'][$key]->latest_update = maybe_unserialize($user->latest_update);
				$run['users'][$key]->avatar = bp_core_fetch_avatar(array(
                    'item_id' 	=> $user->ID,
                    'object'  	=> 'user',
                    'type'		=>'thumb',
                    'html'    	=> false
                ));
			}
		}
		?>
		<div class="vibebp_user_friends <?php echo $settings['style'];?>">
			<?php 
			if( $run['total'] ){
				foreach($run['users'] as $key=>$user){
					echo '<div class="vibebp_user_friend">';
					echo '<img src="'.$user->avatar.'" />';
					if($settings['style'] == 'names' || $settings['style'] == 'pop_names'){
						echo '<span>'.$user->display_name.'</span>';
					}
					echo '</div>';
				}
				if($settings['friends_count'] && ($run['total'] - count($run['users']))){
					echo '<span>'.($run['total'] - count($run['users'])).' '.__('more','vibebp').'</span>';	
				}
				
			}
			?>
		</div>
		<?php
	}

}