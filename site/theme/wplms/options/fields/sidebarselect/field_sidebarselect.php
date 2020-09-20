<?php
class VIBE_Options_sidebarselect extends VIBE_Options{	
	
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
		
		$class = (isset($this->field['class']))?'class="'.$this->field['class'].'" ':'';
		

		echo '<select id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" '.$class.'rows="6"  class="chzn-select" style="width:300px;">';
			
			$sidebars=$GLOBALS['wp_registered_sidebars'];
			echo '<option value="">'.__('Select Sidebar','vibe').'</option>';
			foreach($sidebars as $sidebar){
				if(!in_array($sidebar['id'],array('student_sidebar','instructor_sidebar')))
				echo '<option value="'.$sidebar['id'].'" '.selected($this->value, $sidebar['id'], false).'>'.$sidebar['name'].'</option>';
			}//foreach

		echo '</select>';

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
	}//function
}//class
?>