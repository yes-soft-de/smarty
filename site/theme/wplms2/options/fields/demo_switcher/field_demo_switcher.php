<?php
class VIBE_Options_demo_switcher extends VIBE_Options{	
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since VIBE_Options 1.0
	*/
	function __construct($field = array(), $value = '', $parent = ''){
		
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
		
		echo '<div id="demo_switcher_wrapper_margin"></div><div id="demo_switcher_wrapper"><fieldset>';
			
			foreach($this->field['options'] as $k => $v){

				$selected = (checked($this->value, $k, false) != '')?' vibe-radio-img-selected':'';

				echo '<label class="vibe-radio-img'.$selected.' vibe-radio-img-'.$this->field['id'].'" for="'.$this->field['id'].'_'.array_search($k,array_keys($this->field['options'])).'">';

				echo '<input type="radio" id="'.$this->field['id'].'_'.array_search($k,array_keys($this->field['options'])).'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" '.$class.' value="'.$k.'" '.checked($this->value, $k, false).'/>';
				echo '<img src="'.$v['img'].'" alt="'.$v['title'].'" onclick="jQuery:vibe_demo_switcher_select(\''.$this->field['id'].'_'.array_search($k,array_keys($this->field['options'])).'\', \''.$this->field['id'].'\');" />';
				echo '<br/><span>'.$v['title'].'</span>';
				echo '<small class="demo_switcher_overlay">
				<a class="button button-primary import_demo import_demo_home" data-demo="'.$k.'">'._x('Import Home page','','vibe').'</a>
				<a class="button button-primary import_demo import_demo_layout" data-demo="'.$k.'">'._x('Import Customizer','','vibe').'</a>
				</small>';
				echo '</label>';
				
			}//foreach

		wp_nonce_field('switch_demo_layouts','switch_demo_layouts');	
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?'<br/><span class="description">'.$this->field['desc'].'</span>':'';

		
		echo '</fieldset>';
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
		
		wp_enqueue_script(
			'vibe-demo-switcher-js', 
			VIBE_OPTIONS_URL.'fields/demo_switcher/field_demo_switcher.js', 
			array('jquery'),
			time(),
			true
		);
		wp_enqueue_style(
			'vibe-demo-switcher-css', 
			VIBE_OPTIONS_URL.'fields/demo_switcher/field_demo_switcher.css'
		);
	}//function
	
}//class
?>