<?php

/**
 * FILE: field_sidebars.php 
 * Author: Mr.Vibe 
 * Credits: www.VibeThemes.com
 * Project: WPLMS
 */

class VIBE_Options_sidebars extends VIBE_Options{	
	
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
                $post_types = get_post_types('','names'); 
                echo '<ul>';
                $sidebars=$GLOBALS['wp_registered_sidebars'];
                foreach($sidebars as $sidebar => $value){
                        echo '<li>';
                        echo '<label class="sidebar">'.$sidebar.'</label>';
                        echo '<select id="'.$this->field['id'].'-'.$sidebar.'" name="'.$this->args['opt_name'].'['.$this->field['id'].']['.$sidebar.'][]" '.$class.'multiple="multiple" class="chzn-select">';
                            if(!isset($this->value)){ $this->value =array();
                                $this->value=$this->field['std'];
                                }
                
                            foreach ( $post_types as $post_type ) { 
                                if(!in_array($post_type,array('attachment','revision','nav_menu_item') )){
                                     $selected = (is_array($this->value) && is_array($this->value[$sidebar]) && in_array($post_type, $this->value[$sidebar]))?' selected="selected"':'';
                                    echo '<option value="'.$post_type.'" '.$selected.'>'.$post_type.'</option>';
                                    }
                            }       
		

                        echo '</select>';
                        echo '</li>';
                }
                echo '</ul>';
                echo (isset($this->field['desc']) && !empty($this->field['desc']))?'<br/><span class="description">'.$this->field['desc'].'</span>':'';
		
	}//function
}//class
?>