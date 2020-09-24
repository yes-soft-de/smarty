jQuery(document).ready(function(){
	jQuery('.vibe-opts-multi-page-remove').live('click', function(){ 
		jQuery(this).parent().fadeOut('slow', function(){jQuery(this).remove();});
	});
	
	jQuery('.vibe-opts-multi-page-add').click(function(){ 	
		var new_input = jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child').clone();
		jQuery('#'+jQuery(this).attr('rel-id')).append(new_input);
		jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child').removeAttr('style');
                
                jQuery(new_input).find('select').each(function(){
                   jQuery(this).attr('name' , jQuery(this).attr('rel-name'));
                });
                
                jQuery(new_input).find('input').each(function(){
                   jQuery(this).attr('name' , jQuery(this).attr('rel-name'));
                });
                
            });
});