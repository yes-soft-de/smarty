<?php
class VIBE_Options_google_webfonts extends VIBE_Options{	
	
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
		
		echo '<select id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" '.$class.'rows="6" class="chzn-select" style="width:300px;">';
                 /*     <option value="Aller">CuFon-Aller 400</option>
                      <option value="Bebas">CuFon-Bebas 400</option>
                      <option value="Cabin">CuFon-Cabon 400</option>
                      <option value="Cicle">CuFon-Cicle Gordita 700</option>
                      <option value="ColaborateLight">CuFon-ColaborateLight 400</option>
                      <option value="Josefin">CuFon-Josefin Sans 300</option>
                      <option value="Luxi_Serif">CuFon-Luxi Serif 400</option>
                      <option value="Museo_Sans">CuFon-Museo Sans 500</option>
                      <option value="Nobile">CuFon-Nobile 400</option>
                      <option value="Oswald">CuFon-Oswald 400</option>
                      <option value="Quicksand_Book">CuFon-Quicksand Book 400</option>
                      <option value="Samba_">CuFon-Samba 400</option>
                      <option value="Sansation_">CuFon-Sansation 400</option>
                      <option value="Yanone_Kaffeesatz">CuFon-Yanone Kaffeesatz 400</option>
                      <option value="cantarell">CuFon-Cantarell 400</option>
                      ';*/
		
                    $r = get_option('google_webfonts');
                    $fonts=  unserialize($r);	
                    
                    //print_r($fonts);
		
		foreach($fonts as $font){
		
   echo '<option value="'.$font.'" '.selected($this->value, $font, false).'>'.$font.'</option>';
			
		}
		echo '</select>';
                echo '<div style="float:right;width:200px;border:5px dotted #EFEFEF;text-align:center;" class="font_preview" data-ref="'.$this->field['id'].'">
                       <h1>'.__('Font Preview','vibe').' </h1>
                        </div>';
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
			'vibe-opts-google-webfont-js', 
			VIBE_OPTIONS_URL.'fields/google_webfonts/google_webfonts.js', 
			array('jquery'),
			time(),
			true
		);
                
		
	}//function
	
}//class
?>