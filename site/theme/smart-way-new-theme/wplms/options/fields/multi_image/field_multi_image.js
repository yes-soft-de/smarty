jQuery(document).ready(function(){
	
	jQuery('.vibe-opts-multi-image-remove').live('click', function(){
		jQuery(this).prev('input[type="text"]').val('');
		jQuery(this).parent().fadeOut('slow', function(){jQuery(this).remove();});
	});
	
	jQuery('.vibe-opts-multi-image-add').click(function(){
		var new_input = jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child').clone();
		jQuery('#'+jQuery(this).attr('rel-id')).append(new_input);
                
                
                
                var lastitem=jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child');
		
                lastitem.removeAttr('style');
                
                var newid=jQuery(this).attr('rel-id-name')+jQuery('#'+jQuery(this).attr('rel-id')).length;
                
                //lastitem.attr('id',newid);
                
		jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child input[type="hidden"]').val('');
                
                jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child input[type="hidden"]').attr('id',newid);
                
                jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child a.vibe-opts-upload').attr('rel-id',newid);
                jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child a.remove-image').attr('rel-id',newid);
                
                
                jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child img.vibe-opts-screenshot').attr('src','');
                //jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child a.vibe-opts-upload-remove').hide();
                jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child a.vibe-opts-upload').show();
                var l= ('#'+jQuery(this).attr('rel-id')).length;
		jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child input[type="hidden"]').attr('name' , jQuery(this).attr('rel-name'));
	});
	
});