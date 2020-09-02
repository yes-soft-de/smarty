<?php
class VIBE_Options_pagebuilder_layouts extends VIBE_Options{	
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since VIBE_Options 1.0.5
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
	 * @since VIBE_Options 1.0.5
	*/
	function render(){
		$sample_layouts =array();
		$class = (isset($this->field['class']))?$this->field['class']:'regular-text';
		
		echo '<ul id="'.$this->field['id'].'-ul">';
		$raw_layouts = get_option('vibe_builder_sample_layouts');
                if(isset($raw_layouts)){
                    if(is_string($raw_layouts))
                         $sample_layouts = unserialize($raw_layouts);
                     else {
                         $sample_layouts = $raw_layouts;
                     }
                for($i=0;$i<count($sample_layouts);$i++){
                    echo '<li>
                              '.$sample_layouts[$i]['name'].'
                           <a href="javascript:void(0);" class="vibe-opts-layout-remove" rel-layout="'.$sample_layouts[$i]['name'].'">'.__('Remove', 'vibe').'</a></li>';
				
                    }
                }
		echo '</ul>';
		
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description">'.$this->field['desc'].'</span>':'';
		
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
			'vibe-opts-field-pagebuilder-layouts-js', 
			VIBE_OPTIONS_URL.'fields/pagebuilder_layouts/field_pagebuilder_layouts.js', 
			array('jquery'),
			time(),
			true
		);
		
	}//function
	
}//class
?>