<?php
class VIBE_Options_text_upload extends VIBE_Options{	
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since VIBE_Options 1.0
	*/
	function __construct($field = array(), $value ='', $parent){
		
		parent::__construct($parent->sections, $parent->args, $parent->extra_tabs);
		$this->field = $field;
		$this->value = $value;
		//$this->render();
		
	}//function
	
	
	
	/**
	 * Field Render Function.
	 *
	 * Takes the vars and outputs the HTML for the field in the settings
	 *
	 * @since VIBE_Options 1.0
	*/
	function render(){
		
		$class = (isset($this->field['class']))?$this->field['class']:'regular-text';
		
		$placeholder = (isset($this->field['placeholder']))?' placeholder="'.esc_attr($this->field['placeholder']).'" ':'';
		
                if(!isset($this->value) || !$this->value && isset($this->field['std'])){
                    $this->value=$this->field['std'];
                }
        
        echo '<div class="field_text_upload_switch">
        		<label class="text_upload_switch">
  				<input type="checkbox" value="1" data-control="'.$this->field['id'].'" '.(filter_var($this->value, FILTER_VALIDATE_URL)?'':'checked').'>
  				<span class="text_upload_slider round"></span>
			</label><br>';        
		echo '<div class="field_text_upload_textarea" id="text_'.$this->field['id'].'" '.(filter_var($this->value, FILTER_VALIDATE_URL)?'style="display:none"':'').'>'._x('Raw Text Replacement for Image','options panel upload box','vibe').'<textarea style="display:block;" id="textarea_'.$this->field['id'].'" 
		'.(filter_var($this->value, FILTER_VALIDATE_URL)?'
		rel-name="'.$this->args['opt_name'].'['.$this->field['id'].']" ':'
		name="'.$this->args['opt_name'].'['.$this->field['id'].']"').' '.$placeholder.' class="'.$class.'">'.esc_attr($this->value).'</textarea></div>';
		
		
		echo '<div class="field_text_upload_img" id="img_'.$this->field['id'].'" '.(filter_var($this->value, FILTER_VALIDATE_URL)?'style="display:block"':'style="display:none"').'>
		'._x('Uploaded Image','options panel upload box','vibe').'
		<input type="hidden" id="input_img_'.$this->field['id'].'" 

		'.(filter_var($this->value, FILTER_VALIDATE_URL)?'
		name="'.$this->args['opt_name'].'['.$this->field['id'].']"':'
		rel-name="'.$this->args['opt_name'].'['.$this->field['id'].']" ').'

		value="'.$this->value.'" class="'.$class.'" />';
		//if($this->value != ''){
			echo '<img class="vibe-opts-screenshot" id="vibe-opts-screenshot-'.$this->field['id'].'" src="'.$this->value.'" />';
		//}
		
		if($this->value == ''){$remove = ' style="display:none;"';$upload = '';}else{$remove = '';$upload = ' style="display:none;"';}

		echo ' <a href="javascript:void(0);" class="vibe-opts-upload button-secondary"'.$upload.' rel-id="'.$this->field['id'].'"  data-title="'.$this->field['title'].'"  data-save="#input_img_'.$this->field['id'].'" data-target="#vibe-opts-screenshot-'.$this->field['id'].'">'.__('Browse', 'vibe').'</a>';
		echo ' <a href="javascript:void(0);" class="vibe-opts-upload-remove"'.$remove.' rel-id="'.$this->field['id'].'">'.__('Remove Upload', 'vibe').'</a>
		</div></div>';
		
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?'<br/><br/><span class="description">'.$this->field['desc'].'</span>':'';
		
	}//function
	
	
	
	/**
	 * Enqueue Function.
	 *
	 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
	 *
	 * @since VIBE_Options 1.0
	*/
	function enqueue(){
		
		wp_enqueue_script(
			'vibe-opts-text-field-upload-js', 
			VIBE_OPTIONS_URL.'fields/text_upload/field_text_upload.js', 
			array('jquery','vibe-opts-field-upload-js'),
			2,
			true
		);
		wp_enqueue_style(
			'vibe-opts-field-text-upload-css', 
			VIBE_OPTIONS_URL.'fields/text_upload/field_text_upload.css', 2);

		wp_enqueue_media();
		
		wp_localize_script('vibe-opts-field-upload-js', 'vibe_upload', array('url' => $this->url.'fields/upload/blank.png'));
		
	}//function
	
}//class
?>