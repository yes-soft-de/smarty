<?php
class VibeappGrid_Control extends \Elementor\Base_Data_Control {

	/**
	 * Get emoji one area control type.
	 *
	 * Retrieve the control type, in this case `emojionearea`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'grid_control';
	}

	/**
	 * Enqueue emoji one area control scripts and styles.
	 *
	 * Used to register and enqueue custom scripts and styles used by the emoji one
	 * area control.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue() {
		// Styles
		wp_enqueue_script('grid_control',VIBE_URL.'/includes/elementor/controls/grid_control/js/grid_control.js',[],VIBEBP_VERSION);
		wp_enqueue_style('grid_control',VIBE_URL.'/includes/elementor/controls/grid_control/css/style.css',[],VIBEBP_VERSION);
		
	}

	/**
	 * Get emoji one area control default settings.
	 *
	 * Retrieve the default settings of the emoji one area control. Used to return
	 * the default settings while initializing the emoji one area control.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		/*return [
			'label_block' => true,
			'rows' => 3,
			'emojionearea_options' => [],
		];*/

		return [];
	}

	/**
	 * Render emoji one area control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function content_template() {
		$control_uid = $this->get_control_uid();
		?>
		<div class="elementor-control-field">
			<label for="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<div class="elementor-control-input-wrapper">
				<div class="grid_field_wrapper">
					<div class="columns_wrapper">
						<div>
							<label for="grid-rows"><?php echo _x('Rows','','vibeapp')?></label>
							<input type="number" name="grid-rows" class="grid-rows" />
						</div>
						<div>
							<label for="grid-rows"><?php echo _x('Columns','','vibeapp')?></label>
							<input type="number" name="grid-columns" class="grid-columns" />
						</div>
					</div>
					<div class="playground"></div>
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

