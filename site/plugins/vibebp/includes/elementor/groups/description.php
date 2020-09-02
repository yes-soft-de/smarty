<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Groups_Description extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{


    public function get_name() {
		return 'group_description';
	}

	public function get_title() {
		return __( 'Group Description', 'vibebp' );
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
			'font_Size',
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
					'{{WRAPPER}} .group_description' => 'font-size: {{SIZE}}px',
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
					'{{WRAPPER}} .group_description' => 'color: {{VALUE}}',
				],
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

		

		$description = bp_get_group_description($group);
		if(empty($description)){
			$description = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.";
		}
		

        echo '<div class="group_description"> '.$description.' </div>';
	}

}