<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Field extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{


    public function get_name() {
		return 'member_field';
	}

	public function get_title() {
		return __( 'Member Profile Field', 'vibebp' );
	}

	public function get_icon() {
		return 'vicon vicon-direction-alt';
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
		

		if(!empty($groups)){
			foreach($groups as $group){
				if(!empty($group->fields)){
					foreach ( $group->fields as $field ) {
						//$field = xprofile_get_field( $field->id );
						$fields[$field->id]=$field->name;
					}
				}
			}
		}

		
		$this->add_control(
			'field_id',
			[
				'label' => __( 'Select Field', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'name',
				'options' => $fields,
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
					'{{WRAPPER}} .vibebp_profile_field' => 'font-size: {{SIZE}}px',
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
					'{{WRAPPER}} .vibebp_profile_field' => 'color: {{VALUE}}',
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
					'stacked' =>__('Stacked','vibebp'),
					'spaced'=>__('Spaced','vibebp'),
					'nolabel'=>__('No Label','vibebp'),
					'icon'=>__('Icons','vibebp'),
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

		
		
		$field = xprofile_get_field( $settings['field_id'] );

		$value = xprofile_get_field_data( $settings['field_id'], $user_id);
		if(is_string($value)){
			$json = json_decode($value,true);
			if (json_last_error() === 0) {
			   $value = $json;
			}
		}

		if($field->type == 'social'){
			$newvalue = '';
			$newvalue .='<div class="social_icons">';
			if(!empty($value)){
				foreach($value as $social_icon){
					$newvalue .='<a href="'.$social_icon['url'].'" target="_blank" style="color:'.$settings['text_color'].';font-size:'.$settings['font_size']['size'].'px"><span class="'.$social_icon['icon'].'"></span></a>';
				}
			}
			
			$newvalue .='</div>';
			$value = $newvalue;
		}

		if(is_array($value)){
			$newvalue = '';
			foreach($value as $key=>$val){
				if(is_array($val)){
					foreach($val as $k=>$v){
						$newvalue .='<div class="'.$k.'">'.$v.'</div>';	
					}
				}else{
					$newvalue .='<div class="'.$key.'">'.$val.'</div>';	
				}
			}
			$value = $newvalue;
		}

		?>
		<div class="vibebp_profile_field field_<?php echo $settings['field_id'].' '.$settings['style']; ?> " <?php  echo 'style="color:'.$settings['text_color'].';font-size:'.$settings['font_size']['size'].'px"'; ?>>
			<?php
				if($settings['style'] == 'icon'){
					?><label class="<?php echo $field->name ?>"></label><?php
				}else if($settings['style'] != 'nolabel' ){
					?><label><?php echo $field->name ?></label><?php
				}
			?>
			<div class="<?php echo sanitize_title($field->name).' field_type_'.$field->type;?> "><?php echo $value; ?></div>
		</div>
		<?php
	}

}