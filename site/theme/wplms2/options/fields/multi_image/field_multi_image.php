<?php
class VIBE_Options_multi_image extends VIBE_Options{	
	
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
                     
		if(isset($this->value) && is_array($this->value)){
			foreach($this->value as $k => $value){
                            
				if($value != ''){
				
                                        echo '<li><input type="hidden" id="'.$this->field['id'].'-'.$k.'" name="'.$this->args['opt_name'].'['.$this->field['id'].'][]" value="'.esc_attr($value).'" class="'.$class.'" />';
                                        echo '<img class="vibe-opts-screenshot" id="vibe-opts-screenshot-'.$this->field['id'].'-'.$k.'" src="'.$value.'" />';
                                        $remove = '';$upload = ' style="display:none;"';
                                        echo ' <a href="javascript:void(0);" class="vibe-opts-upload button-secondary"'.$upload.' rel-id="'.$this->field['id'].'-'.$k.'">'.__('Browse', 'vibe').'</a>';
                                        echo ' <a href="javascript:void(0);" class="vibe-opts-upload-remove"'.$remove.' rel-id="'.$this->field['id'].'-'.$k.'">'.__('Remove Upload', 'vibe').'</a><a href="javascript:void(0);" class="vibe-opts-multi-image-remove">'.__('Remove', 'vibe').'</a></li>';
		
	
				}
                                }//foreach
                        }else{
                                    echo '<li><input type="hidden" id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].'][]" value="" class="'.$class.'" />';
                                     echo '<img class="vibe-opts-screenshot" id="vibe-opts-screenshot-'.$this->field['id'].'" src="" />';
                                     $remove = ' style="display:none;"';$upload = '';
                                    echo ' <a href="javascript:void(0);" class="vibe-opts-upload button-secondary"'.$upload.' rel-id="'.$this->field['id'].'">'.__('Browse', 'vibe').'</a>';
                                    echo ' <a href="javascript:void(0);" class="vibe-opts-upload-remove"'.$remove.' rel-id="'.$this->field['id'].'">'.__('Remove Upload', 'vibe').'</a><a href="javascript:void(0);" class="vibe-opts-multi-image-remove">'.__('Remove', 'vibe').'</a></li>';
                                }
                               
		        echo '<li style="display:none;"><input type="hidden" id="" name="" value="" class="'.$class.'" />';
                        echo '<img class="vibe-opts-screenshot" id="vibe-opts-screenshot-'.$this->field['id'].'" src="" />';
                        $remove = ' style="display:none;"';$upload = '';
                        echo ' <a href="javascript:void(0);" class="vibe-opts-upload button-secondary"'.$upload.' rel-id="">'.__('Browse', 'vibe').'</a>';
                        echo 
                        ' <a href="javascript:void(0);" class="remove-image vibe-opts-upload-remove"'.$remove.' rel-id="">'.__('Remove Upload', 'vibe').'</a><a href="javascript:void(0);" class="vibe-opts-multi-image-remove">'.__('Remove', 'vibe').'</a></li>';
                
                  
		   
		echo '</ul>';
		
		echo '<a href="javascript:void(0);" class="vibe-opts-multi-image-add" rel-id-name="'.$this->field['id'].'" rel-id="'.$this->field['id'].'-ul" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][]">'.__('Add More', 'vibe').'</a><br/>';
		
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
			'vibe-opts-field-multi-image-js', 
			VIBE_OPTIONS_URL.'fields/multi_image/field_multi_image.js', 
			array('jquery'),
			time(),
			true
		);
		
	}//function
	
}//class
?>