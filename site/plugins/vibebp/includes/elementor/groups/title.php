<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Groups_Title extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{

    public function get_name() {
		return 'group_title';
	}

	public function get_title() {
		return __( 'Group Title', 'vibebp' );
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
			'font_size',
			[
				'label' =>__('Font Size', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 48,
					'max' => $width,
					'step' => 1,
				],
				'default' => [
					'size'=>24,
				]
			]
		);
		$this->add_control(
			'font_family',
			[
				'label' =>__('Font Family', 'vibebp'),
				'type' => \Elementor\Controls_Manager::FONT,
				'default'=> "'Open Sans', sans-serif",
				'selectors' => [
					'{{WRAPPER}} .title' => 'font-family: {{VALUE}}',
				],
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
					'image_perspective' =>__('Perspective','vibebp'),
					'image_shadow' =>__('Shadow','vibebp'),
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

		$init = VibeBP_Init::init();
		if($init->group->id == $group_id){
			$group = $init->group;
		}else if(empty($init->group)){
			$init->group = groups_get_group($group_id);
			$group = $init->group;
		}
		

		$title = bp_get_group_name($group);
		if(empty($title)){
			$title = 'Group Title';
		}
		

        $style ='';
        if(!empty($settings['font_size'])){
        	$style .= 'font_size:'.$settings['font_size']['size'].'px;';
        }

        echo '<h2 class="title '.$settings['style'].'" style="font-family: ' . $settings['font_family'] . ' '.$style.'"><a href="'.bp_get_group_permalink( $group ).'">'.$title.'</a></h2>';
	}

}