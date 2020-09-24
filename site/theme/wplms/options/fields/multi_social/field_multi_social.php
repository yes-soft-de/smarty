<?php
class VIBE_Options_multi_social extends VIBE_Options{	
	
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
        $social_array = array( 'twitter' => 'Twitter',
                        'facebook' => 'Facebook',
                        'github' => 'Github',
                        'pinterest' => 'Pinterest',
                        'youtube' => 'Youtube',
                        'vk' => 'VK',
                        'vimeo' => 'Vimeo',
                        'pinboard' => 'pinboard',
                        'google-plus' => 'Google Plus',
                        'google' => 'Google',
                        'gmail' => 'Gmail',
                        'tumblr' => 'Tumblr',
                        'foursquare' => 'Foursquare',
                        'linkedin' => 'Linkedin',
                        'dribbble' => 'Dribbble',
                        'stumbleupon' => 'Stumbleupon',
                        'digg' => 'Digg',
                        'flickr' => 'flickr',
                        'yahoo' => 'yahoo',
                        'odnoklassniki' => 'Odnoklassniki',
                        'rss' => 'rss',
                        'chrome' => 'chrome',
                        'lastfm' => 'LastFM',
                        'delicious' => 'Delicious',
                        'reddit' => 'Reddit',
                        'blogger' => 'Blogger',
                        'spotify' => 'Spotify',
                        'instagram' => 'Instagram',
                        'skype' => 'Skype',
                        'dropbox' => 'Dropbox',
                        'paypal' => 'Paypal',
                        'soundcloud' => 'SoundCloud',
                        'xing' => 'Xing',
                        'behance' => 'Behance',
                        'bitcoin' => 'Bitcoin',
                        'html5' => 'HTML 5',
                        'wordpress' => 'wordpress',
                        'android' => 'Android',
                        'amazon' => 'Amazon',
                        'googleplay' => 'Google play',
                        'steam' => 'steam',
                        'wikipedia-w' => 'Wikipedia',
                        'openid' => 'Openid',
                        'yelp' => 'Yelp',
                        'scribd' => 'Scribd'
                    );
        if(isset($this->value) && is_array($this->value)){
            foreach($this->value['social'] as $k=>$value){ 
                    echo '<li>
                                                <select id="'.$this->field['id'].'-'.$k.'[social]" name="'.$this->args['opt_name'].'['.$this->field['id'].'][social]['.$k.']">';
                    foreach($social_array as $key=>$label){
                        echo '<option value="'.$key.'" '.(($key == $value)?'selected':'').'>'.$label.'</option>';
                    }
                                                echo '</select>
                                                <input type="text" id="'.$this->field['id'].'-'.$k.'-url" name="'.$this->args['opt_name'].'['.$this->field['id'].'][url]['.$k.']" value="'.esc_attr($this->value['url'][$k]).'" class="'.$class.'" /> 
                                                <a href="javascript:void(0);" class="vibe-opts-multi-social-remove">'.__('Remove', 'vibe').'</a>
                                             </li>';
                    
                }//if
                
            }//foreach
        
        
        echo '<li style="display:none;">
                    <select id="'.$this->field['id'].'[social]" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][social][]">';
        foreach($social_array as $key=>$label){
                        echo '<option value="'.$key.'">'.$label.'</option>';
                    }                                          
        echo '</select>
                                                <input type="text" id="'.$this->field['id'].'[url]" name="" placeholder="'.__('Enter Full URL of the page','vibe').'" value="" class="'.$class.'" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][url][]" /> 
                                                    <a href="javascript:void(0);" class="vibe-opts-multi-social-remove">'.__('Remove', 'vibe').'</a>
                                                </li>';
        
        echo '</ul>';
        
        echo '<a href="javascript:void(0);" class="vibe-btn green vibe-opts-multi-social-add " rel-id="'.$this->field['id'].'-ul">'.__('Add Social Icons', 'vibe').'</a><br/>';
        
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
			'vibe-opts-field-multi-social-js', 
			VIBE_OPTIONS_URL.'fields/multi_social/field_multi_social.js', 
			array('jquery'),
			time(),
			true
		);
		
	}//function
	
}//class
?>