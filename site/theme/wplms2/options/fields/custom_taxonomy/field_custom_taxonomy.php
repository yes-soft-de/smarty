<?php
class VIBE_Options_custom_taxonomy extends VIBE_Options{	
	
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
                
		echo '<ul id="'.$this->field['id'].'-ul" class="sortable_vibe_custom_taxonomy"><li style="margin-bottom: 20px;margin-left: -15px;"><span style="width:60px;font-size:11px;font-weight:100;display:block;float:left">Include in Search</span> | <strong style="padding:0 20px;">Taxonomy Label</strong> | <strong style="padding:0 20px;">Taxonomy Slug</strong> | <strong style="padding:0 20px;">Display Class</strong> </li>';
		if(isset($this->value) && is_array($this->value)){
			foreach($this->value['label'] as $k=>$value){ 
				if(isset($value) && $value != ''){
					echo '<li class="clearfix"><a href="#" class="sort_order">|||</a>
                                                <input type="checkbox" id="'.$this->args['opt_name'].'['.$this->field['id'].'][search]" name="'.$this->args['opt_name'].'['.$this->field['id'].'][search]['.$k.']" value="1" '.(isset($this->value['search'][$k]) && esc_attr($this->value['search'][$k])?'CHECKED':'').' class="'.$class.'" style="max-width:25px;" />         
                                                <input type="text" id="'.$this->args['opt_name'].'['.$this->field['id'].'][label]" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label]['.$k.']" value="'.esc_attr($this->value['label'][$k]).'" class="'.$class.'" style="width:150px;" /> 
                                                <input type="text" id="'.$this->args['opt_name'].'['.$this->field['id'].'][slug]" name="'.$this->args['opt_name'].'['.$this->field['id'].'][slug]['.$k.']" value="'.esc_attr($this->value['slug'][$k]).'" class="'.$class.'" style="width:150px;" /> 
                                                <input type="text" id="'.$this->args['opt_name'].'['.$this->field['id'].'][class]" name="'.$this->args['opt_name'].'['.$this->field['id'].'][class]['.$k.']" value="'.esc_attr($this->value['class'][$k]).'" class="'.$class.'" style="width:150px;" /> 
                                                <a href="javascript:void(0);" class="vibe-opts-multi-social-remove">'.__('Remove', 'vibe').'</a>
                                             </li>';
					
				}//if
				
			}//foreach
		}
		echo '<li style="display:none;clear:both;">
                    <input type="checkbox" id="'.$this->field['id'].'[feature]" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][search][]" class="'.$class.'" style="max-width:25px;"  />         
                    <input type="text" id="'.$this->field['id'].'[label]" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label][]" class="'.$class.'" style="width:150px;" />     
                    <input type="text" id="'.$this->field['id'].'[slug]" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][slug][]" class="'.$class.'" style="width:150px;" />     
                    <input type="text" id="'.$this->field['id'].'[class]" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][class][]" class="'.$class.'" style="width:150px;" />                             
                    <a href="javascript:void(0);" class="vibe-opts-custom-taxonomy-remove">'.__('Remove', 'vibe').'</a>
                     </li>';
		
		echo '</ul>';
		
		echo '<a href="javascript:void(0);" class="vibe-btn green vibe-opts-custom-taxonomy-add " rel-id="'.$this->field['id'].'-ul" style="text-align:center;">'.__('Add New Listing Taxonomy', 'vibe').'</a><br/>';
		
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
			'vibe-opts-field-custom-taxonomy-js', 
			VIBE_OPTIONS_URL.'fields/custom_taxonomy/field_custom_taxonomy.js', 
			array('jquery'),
			time(),
			true
		);
		
	}//function
	
}//class
?>