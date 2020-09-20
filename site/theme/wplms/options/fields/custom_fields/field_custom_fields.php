<?php
class VIBE_Options_custom_fields extends VIBE_Options{	
	
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
                
		echo '<ul id="'.$this->field['id'].'-ul" class="sortable_vibe_custom_fields"><li style="margin-bottom: 20px;margin-left: -15px;"><span style="width:60px;font-size:11px;font-weight:100;display:block;float:left">Show in Feature Area</span> | <strong style="padding:0 20px;">Field Label</strong> | <strong style="padding:0 20px;">Field Type</strong> | <strong style="padding:0 20px;">Display Class</strong> </li>';
		if(isset($this->value) && is_array($this->value)){
			foreach($this->value['field_type'] as $k=>$value){ 
				if(isset($value) && $value != ''){
					echo '<li class="clearfix"><a href="#" class="sort_order">|||</a>
                                                <input type="checkbox" id="'.$this->args['opt_name'].'['.$this->field['id'].'][feature]" name="'.$this->args['opt_name'].'['.$this->field['id'].'][feature]['.$k.']" value="1" '.(isset($this->value['feature'][$k]) && esc_attr($this->value['feature'][$k])?'CHECKED':'').' class="'.$class.'" style="max-width:25px;" />         
                                                <input type="text" id="'.$this->args['opt_name'].'['.$this->field['id'].'][label]" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label]['.$k.']" value="'.esc_attr($this->value['label'][$k]).'" class="'.$class.'" style="width:150px;" /> 
                                                <select id="'.$this->args['opt_name'].'['.$this->field['id'].'][field_type]" name="'.$this->args['opt_name'].'['.$this->field['id'].'][field_type]['.$k.']">
                                                    <option value="showhide" '.(($value =='showhide')?'selected="selected"':'').'>Show/Hide</option>
                                                    <option value="price" '.(($value =='price')?'selected="selected"':'').'>Price</option>    
                                                    <option value="available" '.(($value =='available')?'selected="selected"':'').'>Available</option>    
                                                    <option value="featured" '.(($value =='featured')?'selected="selected"':'').'>Featured</option>        
                                                    <option value="number" '.(($value =='number')?'selected="selected"':'').'>Number</option>        
                                                    <option value="text" '.(($value =='text')?'selected="selected"':'').'>Simple Text Field</option>
                                                    <option value="textarea" '.(($value =='textarea')?'selected="selected"':'').'>Text Area</option>
                                                    <option value="checkbox" '.(($value =='checkbox')?'selected="selected"':'').'>Yes/No</option>
                                                    <option value="select" '.(($value =='select')?'selected="selected"':'').'>Select</option>    
                                                    <option value="multiselect" '.(($value =='multiselect')?'selected="selected"':'').'>Multi Select</option>
                                                    <option value="date" '.(($value =='date')?'selected="selected"':'').'>Date</option>
                                                   <option value="agents" '.(($value =='agents')?'selected="selected"':'').'>Agents</option>     
                                                    <option value="gmap" '.(($value =='gmap')?'selected="selected"':'').'>Google Map</option>
                                                </select>
                                                <input type="text" id="'.$this->args['opt_name'].'['.$this->field['id'].'][class]" name="'.$this->args['opt_name'].'['.$this->field['id'].'][class]['.$k.']" value="'.esc_attr($this->value['class'][$k]).'" class="'.$class.'" style="width:150px;" /> 
                                                <a href="javascript:void(0);" class="vibe-opts-multi-social-remove">'.__('Remove', 'vibe').'</a>
                                             </li>';
					
				}//if
				
			}//foreach
		}
		echo '<li style="display:none;clear:both;">
                    <input type="checkbox" id="'.$this->field['id'].'[feature]" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][feature][]" class="'.$class.'" style="max-width:25px;"  />         
                    <input type="text" id="'.$this->field['id'].'[label]" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label][]" class="'.$class.'" style="width:150px;" />     
                                                <select id="'.$this->field['id'].'[field_type]" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][field_type][]">
                                                    <option value="showhide">Show/Hide</option>
                                                    <option value="price">Price</option>
                                                    <option value="available">Available</option>
                                                    <option value="featured">Featured</option>
                                                    <option value="number">Number</option>
                                                    <option value="text">Simple Text Field</option>
                                                    <option value="textarea">Text Area</option>
                                                    <option value="checkbox">Yes/No</option>
                                                    <option value="select">Select</option>    
                                                    <option value="multiselect">Multi Select</option>
                                                    <option value="date">Date</option>
                                                    <option value="agents">Agents</option>
                                                    <option value="gmap">Google Map</option>
                                                </select>
                        <input type="text" id="'.$this->field['id'].'[class]" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][class][]" class="'.$class.'" style="width:150px;" />                             
                                                <a href="javascript:void(0);" class="vibe-opts-custom-fields-remove">'.__('Remove', 'vibe').'</a>
                     </li>';
		
		echo '</ul>';
		
		echo '<a href="javascript:void(0);" class="vibe-btn green vibe-opts-custom-fields-add " rel-id="'.$this->field['id'].'-ul" style="text-align:center;">'.__('Add New List Field', 'vibe').'</a><br/>';
		
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
			'vibe-opts-field-custom-fields-js', 
			VIBE_OPTIONS_URL.'fields/custom_fields/field_custom_fields.js', 
			array('jquery'),
			time(),
			true
		);
		
	}//function
	
}//class
?>