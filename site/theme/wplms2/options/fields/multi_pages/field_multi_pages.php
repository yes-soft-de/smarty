<?php
class VIBE_Options_multi_pages extends VIBE_Options{	
	
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
		
		$class = (isset($this->field['class']))?$this->field['class']:'regular-text';
		echo '<ul id="'.$this->field['id'].'-ul">';
		//
        $args=array(
            'post_type' => $this->field['custompost'],
            'numberposts' => -1
            );
        $customtypes=get_posts($args);
		if(isset($this->value) && is_array($this->value)){
			foreach($this->value['cp'] as $k=>$value){ 
				if($value != ''){
					echo '<li>
                            <select id="'.$this->field['id'].'-'.$k.'[cp]" name="'.$this->args['opt_name'].'['.$this->field['id'].'][cp]['.$k.']">';
                            foreach($customtypes as $customtype){
                                echo '<option value="'.$customtype->ID.'" '.selected(esc_attr($this->value['cp'][$k]),$customtype->ID).'>'.$customtype->post_title.'</option>';
                            }
                            echo '</select>
                            <input type="text" id="'.$this->field['id'].'-'.$k.'-title" name="'.$this->args['opt_name'].'['.$this->field['id'].'][title]['.$k.']" value="'.esc_attr($this->value['title'][$k]).'" class="'.$class.'" /> 
                            <a href="javascript:void(0);" class="vibe-opts-multi-page-remove">'.__('Remove', 'vibe').'</a>
                         </li>';
				}//if
			}//foreach
		}else{
				
		}//if
		
		echo '<li style="display:none;">
                <select id="'.$this->field['id'].'[cp]" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][cp][]">
                ';
                    foreach($customtypes as $customtype){
                        echo '<option value="'.$customtype->ID.'">'.$customtype->post_title.'</option>';
                    }
               echo '                              
                </select>
                <input type="text" id="'.$this->field['id'].'[title]" name="" value="" class="'.$class.'" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][title][]" /> 
                    <a href="javascript:void(0);" class="vibe-opts-multi-page-remove">'.__('Remove', 'vibe').'</a>
                </li>';
		echo '</ul>';
		
		echo '<a href="javascript:void(0);" class="vibe-btn green vibe-opts-multi-page-add " rel-id="'.$this->field['id'].'-ul">'.__('Add Custom Posts', 'vibe').'</a><br/>';
		
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
			'vibe-opts-field-multi-page-js', 
			VIBE_OPTIONS_URL.'fields/multi_pages/field_multi_pages.js', 
			array('jquery'),
			time(),
			true
		);
		
	}//function
	
}//class
?>