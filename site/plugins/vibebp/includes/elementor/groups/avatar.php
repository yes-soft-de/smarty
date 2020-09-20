<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Groups_Avatar extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{

    public function get_name() {
		return 'group_avatar';
	}

	public function get_title() {
		return __( 'Group Avatar', 'vibebp' );
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


		if(bp_get_current_group_id()){
			$group_id = bp_get_current_group_id();
		}else{
			global $groups_template;
			if(!empty($groups_template->group)){
				$group_id = $groups_template->group->id;
			}
		}

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

		

		$src =  bp_core_fetch_avatar(array(
            'item_id' => $group_id,
            'object'  => 'group',
            'type'=>'full',
            'html'    => false
        ));

        if(empty($src)){
        	$src = plugins_url('../../../assets/images/avatar.jpg',__FILE__);
        }

        $style ='';
        if(!empty($settings['width'])){
        	$style .= 'width:'.$settings['width']['size'].$settings['width']['unit'].';';
        }
        if(!empty($settings['border-radius'])){
        	$style .= 'border-radius:'.$settings['border-radius']['size'].$settings['border-radius']['unit'].';';
        }
        echo '<a href="'.bp_get_group_permalink().'"><img src="'.$src.'" class="'.$settings['style'].'" '.(empty($style)?'':'style="'.$style.'"').' /></a>';
	}

}