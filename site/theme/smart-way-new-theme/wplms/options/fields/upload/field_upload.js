jQuery(document).ready(function($){

	
	function vibe_upload_media($this) {
        'use strict';

        // Default Logo
        var file_frame, attachment;

        if ( undefined !== file_frame ) {
            file_frame.open();
            return;
        }
        
        console.log($this.data('title'));
        file_frame = wp.media.frames.file_frame = wp.media({
            title: $this.data('title'),
            multiple: false  
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();
            console.log(attachment);
            jQuery($this.data('target')).attr('src',attachment.url).show(200);
            jQuery($this.data('save')).val(attachment.url);
            //jQuery($this.data('save')).val(attachment.id);
            // Do something with attachment.id and/or attachment.url here
        });
        // Now display the actual file_frame
        file_frame.open();

    }

	$('.vibe-opts-upload').on( 'click', function(e) {
        e.preventDefault();
        vibe_upload_media($(this));
    });
	/*
	 *
	 * VIBE_Options_upload function
	 * Adds media upload functionality to the page
	 *
	 */
	 
	 var header_clicked = false;
	 
	jQuery("img[src='']").attr("src", vibe_upload.url);
	
	/*jQuery('.vibe-opts-upload').live("click", function() {
		header_clicked = true;
		formfield = jQuery(this).attr('rel-id');
		preview = jQuery(this).prev('img');
                tb_show('', 'media-upload.php?type=image&amp;post_id=0&amp;TB_iframe=true');
		return false;
	});*/
	
	
	// Store original function
	window.original_send_to_editor = window.send_to_editor;
	
	
	window.send_to_editor = function(html) {
		if (header_clicked) {
			imgurl = jQuery('img',html).attr('src');
                        
			jQuery('#' + formfield).val(imgurl);
			jQuery('#' + formfield).next().fadeIn('slow');
			jQuery('#' + formfield).next().next().fadeOut('slow');
			jQuery('#' + formfield).next().next().next().fadeIn('slow');
			jQuery(preview).attr('src' , imgurl);
			tb_remove();
			header_clicked = false;
		} else {
			window.original_send_to_editor(html);
                        
		}
	}
	
	jQuery('.vibe-opts-upload-remove').click(function(){
		$relid = jQuery(this).attr('rel-id');
		jQuery('#'+$relid).val('');
		jQuery(this).prev().fadeIn('slow');
		jQuery(this).prev().prev().fadeOut('slow', function(){jQuery(this).attr("src", vibe_upload.url);});
		jQuery(this).fadeOut('slow');
                header_clicked = false;
	});
});