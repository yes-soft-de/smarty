<?php

 if ( ! defined( 'ABSPATH' ) ) exit;
class VibeErrors {
	function __construct() {
		$this->localizionName = '';
		$this->errors = new WP_Error();
		$this->initialize_errors();
	}
	/* get_error - Returns an error message based on the passed code
	Parameters - $code (the error code as a string)
	Returns an error message */
	function get_error($code = '') {
	global $vibe_options;
	if(isset($vibe_options['disable_errors']) && $vibe_options['disable_errors']){return '';}
	    $errorMessage ='<div class="alert alert-block"><span class="error"></span><button type="button" class="close" data-dismiss="alert">×</button>';
		$errorMessage .= $this->errors->get_error_message($code);
		if ($errorMessage == null) {
			return __("Unknown error.", 'vibe-customtypes');
		}
		$errorMessage .= '  ..<a href="http://vibethemes.com/documentation/wplms/" target="_blank">more..</a></div>';
		return $errorMessage;
	}
	/* Initializes all the error messages */
	function initialize_errors() {
	    $this->errors->add('initialize', __('Please save the changes again.', 'vibe-customtypes'));
	    $this->errors->add('unknown', __('Some Uknown issue appeared, please contact us.', 'vibe-customtypes'));
	    $this->errors->add('unsaved_editor', __('Please save the Page Builder changes !', 'vibe-customtypes'));
	    $this->errors->add('slider_not_found', __('Requested Slider was not found, Please check slider post id !', 'vibe-customtypes'));
	    $this->errors->add('no_posts', __('No Posts Id\'s found in given Custom Post Type.', 'vibe-customtypes'));
	    $this->errors->add('author_not_found', __('Author Information missing.', 'vibe-customtypes'));
        $this->errors->add('access_denied', __('You do not have permission to do that.','vibe-customtypes'));
        $this->errors->add('error_tweets', __('Unable to get tweets from Twitter !','vibe-customtypes'));
        $this->errors->add('no_tweets', __('No public tweets found on Twitter !','vibe-customtypes'));
        $this->errors->add('term_taxonomy_mismatch', __('Term : Taxonomy Mismatch: Selected Term does not exist in Taxonomy !','vibe-customtypes'));
        $this->errors->add('term_postype_mismatch', __('Post Type : Taxonomy Mismatch: Selected Taxonomy does not exist in Post Type !','vibe-customtypes'));
        $this->errors->add('no_featured', __('Featured Component does not Exist !','vibe-customtypes'));
        $this->errors->add('incorrect_audio', __('Incorrect or Incompatible Audio Format !','vibe-customtypes'));
        $this->errors->add('incorrect_video', __('Incorrect or Incompatible Video Embed Code !','vibe-customtypes'));
        $this->errors->add('incorrect_modal', __('Modal ID Incorrect, please recheck !','vibe-customtypes'));
        $this->errors->add('incorrect_testimonial', __('Testimonial ID Incorrect, please recheck !','vibe-customtypes'));
        $this->errors->add('incorrect_post', __('Post ID Incorrect, please recheck !','vibe-customtypes'));
	} //end function initialize_errors
}
/*
$error = new VibeErrors();$error->get_error('unsaved_editor');
 if(!isset($atts) || !isset($atts['post_type'])){
   return $error->get_error('unsaved_editor');
 }
*/

 if(!function_exists('calculate_duration_time')){
  function calculate_duration_time($seconds) {
  	switch($seconds){
  		case 1: $return = __('Seconds','vibe-customtypes');break;
  		case 60: $return = __('Minutes','vibe-customtypes');break;
  		case 3600: $return = __('Hours','vibe-customtypes');break;
  		case 86400: $return = __('Days','vibe-customtypes');break;
  		case 604800: $return = __('Weeks','vibe-customtypes');break;
  		case 2592000: $return = __('Months','vibe-customtypes');break;
  		case 31104000: $return = __('Years','vibe-customtypes');break;
  		default:
  		$return = apply_filters('vibe_calculation_duration_default',$return,$seconds);
  		break;
  	}
  	return $return;
	} 
}
?>