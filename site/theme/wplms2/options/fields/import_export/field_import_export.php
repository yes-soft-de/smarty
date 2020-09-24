<?php
class VIBE_Options_import_export extends VIBE_Options{	
	
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
		
		$class = (isset($this->field['class']))?$this->field['class']:'large-text';
                $export_string = get_option($this->field['id']);
                if(!is_string($export_string)){
                    $export_string = serialize($export_string);
                }
		$export_code=base64_encode($export_string);
		$placeholder = (isset($this->field['placeholder']))?' placeholder="'.esc_attr($this->field['placeholder']).'" ':'';
		echo '<p><strong>'.__('Export Code','vibe').'</strong>'.__('(Copy export code and paste it in import area of other WordPress isntallation)','vibe').'</p><textarea '.$placeholder.'class="export_code '.$class.'" rows="6" >'.$export_code.'</textarea>';
		echo '<p><strong>'.__('Import Code','vibe').'</strong></p>
                      <textarea id="'.$this->field['id'].'" '.$placeholder.' class="import_code '.$class.'" rows="6" ></textarea>
                      <a href="javascript:void(0);" class="import_data button button-primary" rel-id="'.$this->field['id'].'">Import</a>    ';
                
		
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?'<br/><span class="description">'.$this->field['desc'].'</span>':'';
		
	}//function
        
        /**
	 * Enqueue Function.
	 *
	 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
	 *
	 * @since VIBE_Options 1.0.5
	*/
	function enqueue(){
		
		wp_enqueue_script(
			'vibe-opts-field-import-export-js', 
			VIBE_OPTIONS_URL.'fields/import_export/field_import_export.js', 
			array('jquery'),
			time(),
			true
		);
		
	}//function
	
}//class
?>