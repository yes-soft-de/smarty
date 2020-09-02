<?php
class VIBE_Options_slider extends VIBE_Options{	
	
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
		
		$class = (isset($this->field['class']))?$this->field['class']:'';
		
                if(!isset($this->value) || $this->value == 0){
                    $this->value=$this->field['std'];
                }
		echo '<div class="wrapper">';
		echo '<div class="jqslider" rel-default="'.$this->value.'" rel-min="'.(isset($this->field['min'])?$this->field['min']:0).'" rel-max="'.(isset($this->field['max'])?$this->field['max']:100).'"></div>';
		echo '<input type="text" id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" value="'.$this->value.'" class="'.$class.' slider_value"/> ';
		
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description">'.$this->field['desc'].'</span>':'';
		
		echo '</div>';
		
	}//function
	
	
	/**
	 * Enqueue Function.
	 *
	 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
	 *
	 * @since VIBE_Options 1.0
	*/
	function enqueue(){
		wp_enqueue_script('jquery-ui-slider');
                wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script(
			'vibe-opts-field-slider-js', 
			VIBE_OPTIONS_URL.'fields/slider/field_slider.js'
		);
		
	}//function
	
}//class
?>