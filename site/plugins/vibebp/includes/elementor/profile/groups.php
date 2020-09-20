<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Groups extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{

    public function get_name() {
		return 'member_groups';
	}

	public function get_title() {
		return __( 'Member Profile Groups', 'vibebp' );
	}

	public function get_icon() {
		return 'vicon vicon-flag-alt-2';
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
			'groups_count',
			[
				'label' => __( 'Show total groups count', 'vibebp' ),
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
			'show_groups',
			[
				'label' =>__('Show Groups', 'vibebp'),
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
					'names' =>__('Name','vibebp'),
					'pop_names' =>__('Pop Names','vibebp'),
					'card' =>__('Card','vibebp'),
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
		
		$args = array(
			'type'		=>$settings['order'],
			'user_id'	=>$user_id,
			'per_page'	=>$settings['show_groups']['size']
		);
		$run = groups_get_groups($args);
    		
		if( count($run['groups']) ) {

			foreach($run['groups'] as $k=>$group){
				$run['groups'][$k]->avatar = bp_core_fetch_avatar(array(
                        'item_id' => $run['groups'][$k]->id,
                        'object'  => 'group',
                        'type'=>'thumb',
                        'html'    => false
                    ));
			}
		}
		?>
		<div class="vibebp_user_groups <?php echo $settings['style'];?>">
			<?php 
			if( $run['total'] ){
				foreach($run['groups'] as $key=>$group){
					echo '<div class="vibebp_user_group">';
					echo '<img src="'.$group->avatar.'" />';
					if($settings['style'] == 'names' || $settings['style'] == 'pop_names'){
						echo '<span>'.$group->name.'</span>';
					}
					echo '</div>';
				}
				if($settings['groups_count'] && ($run['total'] - count($run['groups']))){
					echo '<span>'.($run['total'] - count($run['groups'])).' '.__('more','vibebp').'</span>';	
				}
				
			}
			?>
		</div>
		<?php
	}

}