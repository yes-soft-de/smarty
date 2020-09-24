<?php
class VIBE_Options_thumbnails extends VIBE_Options{	
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since VIBE_Options 1.0
	*/
	function __construct($field = array(), $value ='', $parent = ''){
		
		parent::__construct($parent->sections, $parent->args, $parent->extra_tabs);
		$this->field = $field;
		$this->value = $value;
		
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
		
		
		echo '<select id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" '.$class.'rows="6"  class=" select_thumbnail" style="width:300px;">';
			
			foreach($this->field['options'] as $k => $v){
				
				echo '<option value="'.$k.'" '.selected($this->value, $k, false).'>'.$v.'</option>';
				
			}//foreach

		echo '</select>';
                echo '<div class="html">'.$this->field['html'].'</div>';
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
			'vibe-opts-field-thumbnails-js', 
			VIBE_OPTIONS_URL.'fields/thumbnails/field_thumbnails.js', 
			array('jquery'),
			time(),
			true
		);
		
		
	}//function
	
}//class
?>