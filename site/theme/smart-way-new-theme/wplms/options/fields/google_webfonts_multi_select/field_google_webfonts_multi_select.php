<?php
class VIBE_Options_google_webfonts_multi_select extends VIBE_Options{	
	
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
		if(empty($this->field)){$this->field=array();}
		$this->field['fonts'] = array();
		/*
		$fonts = get_transient('vibe-opts-google-webfonts');
		if(!is_array(json_decode($fonts))){
			
			$fonts = wp_remote_retrieve_body(wp_remote_get('https://www.googleapis.com/webfonts/v1/webfonts?key='.$this->args['google_api_key']));
			set_transient('vibe-opts-google-webfonts', $fonts, 60 * 60 * 24);
				
		}
		$this->field['fonts'] = json_decode($fonts);
		*/
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
		
		
                    $r = get_option('google_webfonts');
                    $fonts=  json_decode($r);	
                    
                   
		echo '<select id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].'][]" '.$class.'rows="6" class="chzn-select" multiple="multiple" style="width:300px;" data-placeholder="Type to search...">';
		if(is_array($fonts->items)){
		foreach($fonts->items as $font){
			if(isset($font->variants)){
				foreach($font->variants as $variant){
					if(isset($font->subsets)){
						foreach($font->subsets as $subset){
							$value = $font->family.'-'.$variant.'-'.$subset;
							$selected = (is_array($this->value) && in_array($value, $this->value))?' selected="selected"':'';
	                		echo '<option value="'.$value.'" '.$selected.'>'.$value.'</option>';
						}
					}
				}
			}
			}
		}
		echo '</select>';
                  echo  '<span class="right-description"><i class="icon-refresh"></i><a id="reset-google-fonts" class="reset">Refresh Google Webfont List</a> <small> * Updates the font list with latest available Google fonts. Reload after font refresh.</small></span>';
	
               
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
			'vibe-opts-field-google-webfonts-multi-select-js', 
			VIBE_OPTIONS_URL.'fields/google_webfonts_multi_select/google_webfonts_multi_select.js', 
			array('jquery'),
			time(),
			true
		);
		
	}//function
	
}//class
?>