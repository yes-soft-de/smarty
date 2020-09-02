<?php
class VibeAppPost_Control extends \Elementor\Base_Data_Control {

	
	public function get_type() {
		return 'post_control';
	}

	public function enqueue() {
		// Styles
		wp_enqueue_script('post_control',VIBE_URL.'/includes/elementor/controls/post_control/js/post_control.js',[],VIBEAPP_VERSION);
		wp_enqueue_style('post_control',VIBE_URL.'/includes/elementor/controls/post_control/css/style.css',[],VIBEAPP_VERSION);
		
	}

	
	protected function get_default_settings() {
		/*return [
			'label_block' => true,
			'rows' => 3,
			'emojionearea_options' => [],
		];*/

		return [];
	}


	public function content_template() {
		$control_uid = $this->get_control_uid();

		$v_post_types = array();
	    $post_types=get_post_types(array('public'=>true),'objects'); 

	    foreach ( $post_types as $post_type ){
	        if( !in_array($post_type->name, array('attachment','revision','nav_menu_item')))
	           $v_post_types[$post_type->name]=$post_type->label;
	    }
		?>
		<div class="elementor-control-field">
			<label for="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<div class="elementor-control-input-wrapper">
				<div class="post_field_wrapper">
					<div>
						<label for="grid-rows"><?php echo _x('Select Post Type','','vibeapp')?></label>
						<select class="vibeapp_select_post_type">
							<option><?php _e('Select Post Type','vibeapp'); ?></option>
							<?php
							foreach($v_post_types as $post_type => $label){
								echo '<option value="'.$post_type.'">'.$label.'</option>';
							}
							?>
						</select> 
					</div>
					
					<input id="<?php echo esc_attr( $control_uid ); ?>" 
					class="elementor-control-tag-area grid_value" rows="{{ data.rows }}" data-setting="{{ data.name }}"
					type="hidden"  value=""/>
					<button class="button reset_grid"><?php echo _x('Reset','','vibeapp')?></button>
				</div>

				
			</div>
		</div>
		<script>jQuery('body').trigger('vibeapp_grid_selected');</script>
		<# if ( data.description ) { #>
		<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}

}

