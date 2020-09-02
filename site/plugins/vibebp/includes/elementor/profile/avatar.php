<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Avatar extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{


    public function get_name() {
		return 'member_avatar';
	}

	public function get_title() {
		return __( 'Member Profile Avatar', 'vibebp' );
	}

	public function get_icon() {
		return 'vicon vicon-image';
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

		$init = VibeBP_Init::init();
		$init->get_settings();
		if(!empty($init->settings['bp']['bp_avatar_full_width'])){
			$width= $init->settings['bp']['bp_avatar_full_width'];
		}else{
			$width = 320;
		}

		$this->add_control(
			'width',
			[
				'label' =>__('Image Width', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 48,
					'max' => $width,
					'step' => 1,
				],
				'default' => [
					'size'=>320,
				]
			]
		);
		$this->add_control(
			'border-radius',
			[
				'label' =>__('Border Radius', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 48,
					'max' => round($width/2,0),
					'step' => 1,
				],
				'default' => [
					'size'=>0,
				]
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
        echo '<a href="'.bp_core_get_user_domain( $user_id ).'"><img src="'.$src.'" class="'.$settings['style'].'" '.(empty($style)?'':'style="'.$style.'"').' /></a>';
	}

}