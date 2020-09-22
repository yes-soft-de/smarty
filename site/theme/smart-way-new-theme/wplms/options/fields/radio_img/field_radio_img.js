/*
 *
 * VIBE_Options_radio_img function
 * Changes the radio select option, and changes class on images
 *
 */
function vibe_radio_img_select(relid, labelclass){
	jQuery(this).prev('input[type="radio"]').prop('checked');

	jQuery('.vibe-radio-img-'+labelclass).removeClass('vibe-radio-img-selected');	
	
	jQuery('label[for="'+relid+'"]').addClass('vibe-radio-img-selected');
}//function