<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Articles extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{


    public function get_name() {
		return 'kb_articles';
	}

	public function get_title() {
		return __( 'KB Articles', 'vibebp' );
	}

	public function get_icon() {
		return 'vicon vicon-blackboard';
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
			'number',
			[
				'label' =>__('Number of Articles', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 1,
					'max' => 20,
					'step' => 1,
				],
				'default' => [
					'size'=>0,
				]
			]
		);
		
		$this->add_control(
			'grid',
			[
				'label' =>__('Articles Grid', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 1,
					'max' => 6,
					'step' => 1,
				],
				'default' => [
					'size'=>320,
				]
			]
		);

		$this->add_control(
			'article_type',
			[
				'label' => __( 'Article Type', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					'' =>__('Default','vibebp'),
					'image_perspective' =>__('Perspective','vibebp'),
					'image_shadow' =>__('Shadow','vibebp'),
				)
			]
		);

		$this->add_control(
			'post_author',
			[
				'label' => __( 'Article Author', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					'' =>__('Current User','vibebp'),
					'displayed_user' =>__('Displayed User','vibebp'),
					'image_perspective' =>__('Perspective','vibebp'),
					'image_shadow' =>__('Shadow','vibebp'),
				)
			]
		);
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			$user_id = get_current_user_id();
		}

		
		
		$src =  bp_core_fetch_avatar(array(
            'item_id' => $user_id,
            'object'  => 'user',
            'type'=>'full',
            'html'    => false
        ));

        if(empty($src)){

        }

        $style ='';
        if(!empty($settings['width'])){
        	$style .= 'width:'.$settings['width']['size'].$settings['width']['unit'].';';
        }
        if(!empty($settings['border-radius'])){
        	$style .= 'border-radius:'.$settings['border-radius']['size'].$settings['border-radius']['unit'].';';
        }
        echo '<img src="'.$src.'" class="'.$settings['style'].'" '.(empty($style)?'':'style="'.$style.'"').' />';
	}

}