<?php
class VIBE_Options_font_upload extends VIBE_Options{	
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since VIBE_Options 1.0
	*/
	function __construct($field = array(), $value ='', $parent = ''){
		
		parent::__construct($parent->sections, $parent->args, $parent->extra_tabs);
		$this->field = $field;
		$this->value = $value;
		
	}//function
	
	
	
	/**
	 * Field Render Function.
	 *
	 * Takes the vars and outputs the HTML for the field in the settings
	 *
	 * @since VIBE_Options 1.0
	*/
	function render(){
		
		
		$class = (isset($this->field['class']))?$this->field['class']:'regular-text';
		echo '<ul id="'.$this->field['id'].'-ul">';
		
		if(isset($this->value) && is_array($this->value)){
			foreach($this->value as $k => $value){
				if($value != ''){
                                                echo '<li>
                                                        <h2>Custom Font : '.esc_attr($value).' &rsaquo;</h2><a href="javascript:void(0);" class="vibe-opts-custom-font-remove">'.__('Remove', 'vibe').'</a>';
                                                echo '</li>';
                                    }
                                }
                        }       
                
                                        echo '<li id="font-uploader-form"><h2>Add New Custom Font</h2>';
                                        echo '<ul class="vibe-custom-fonts">
                                                        <li><label>Font Name</label> 
                                                        <input type="text" id="font-name" name="'.$this->args['opt_name'].'['.$this->field['id'].'][]" value="" class="'.$class.'" /></li>
                                                   
                                                        <li><label>Upload .eot</label>
                                                            <input type="file" name="eot" id="font-eot" /></p>
                                                            <input type="hidden" value="upload"/>
                                                        </li>
                                                        <li><label>Upload .ttf</label>
                                                            <input type="file" name="ttf" id="font-ttf" /></p>
                                                            <input type="hidden" value="upload"/>
                                                        </li>
                                                        <li><label>Upload .woff</label>
                                                           <input type="file" name="woff" id="font-woff" /></p>
                                                            <input type="hidden" value="upload"/>
                                                         </li>      
                                                         <li><label>Upload .svg</label>
                                                            <input type="file" name="svg" id="font-svg" /></p>
                                                            <input type="hidden" value="upload"/>
                                                        </li>     
                                                    </ul>';
                                      echo '</li>';
                 echo '</ul>';
                 echo '<a class="button button-primary font-upload">'.__('Upload Font', 'vibe').'</a><br/>';                      
                echo (isset($this->field['desc']) && !empty($this->field['desc']))?'<br/><br/><span class="description">'.$this->field['desc'].'</span>':'';
                
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
			'vibe-opts-field-font-upload-js', 
			VIBE_OPTIONS_URL.'fields/font_upload/field_font_upload.js', 
			array('jquery','jquery-ui-core', 'thickbox', 'media-upload'),
			time(),
			true
		);
		
		wp_enqueue_style('thickbox');// thanks to https://github.com/rzepak
		
		wp_localize_script('vibe-opts-field-font-upload-js', 'vibe_upload', array('url' => $this->url.'fields/upload/blank.png'));
		
	}//function
	
}//class
?>