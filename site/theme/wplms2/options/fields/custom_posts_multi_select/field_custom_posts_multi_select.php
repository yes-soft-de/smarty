<?php
class VIBE_Options_custom_posts_multi_select extends VIBE_Options{	
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since VIBE_Options 1.0.1
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
	 * @since VIBE_Options 1.0.1
	*/
	function render(){
		
		$class = (isset($this->field['class']))?'class="'.$this->field['class'].'" chzn-select':'';
		echo '<select id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].'][]" '.$class.'multiple="multiple" class="chzn-select">';

		//if(!isset($this->field['args'])){$this->field['args'] = array();}
		//$args = wp_parse_args($this->field['args'], array('public' => true));
		
		if(!isset($this->value)){ $this->value =array();
		$this->value=$this->field['std'];
                }
                
		$post_types=get_post_types('','objects'); 
		foreach ( $post_types as $post_type ){
		    if( !in_array($post_type->name, array('attachment','revision','nav_menu_item','sliders','modals','shop','shop_order','shop_coupon'))){
			 
                        $selected = (is_array($this->value) && in_array($post_type->name, $this->value))?' selected="selected"':'';
			echo '<option value="'.$post_type->name.'" '.$selected.'>'.$post_type->label.'</option>';}
		}
		
		

		echo '</select>';

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
		
		
	}//function
}//class
?>