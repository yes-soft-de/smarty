jQuery(document).ready(function(){
	
	jQuery('.vibe-opts-custom-post-remove').live('click', function(){
		//jQuery(this).prev('input[type="text"]').val('');
		jQuery(this).parent().fadeOut('slow', function(){jQuery(this).remove();});
	});
	
	jQuery('.vibe-opts-custom-post-add').click(function(){
                jQuery('#intro').remove();
		var new_input = jQuery('#new_custom_post').clone();
		jQuery('#'+jQuery(this).attr('rel-id')).append(new_input);
		jQuery(new_input).removeAttr('style');
		
                
		jQuery(new_input).find(' input').each(function(){
                   jQuery(this).attr('name' , jQuery(this).attr('rel-name'));
                });
                
		jQuery(new_input).find(' textarea').each(function(){
                   jQuery(this).attr('name' , jQuery(this).attr('rel-name'));
                });
                
                 jQuery('.vibe-drop h4').click(function(){
                    jQuery(this).parent().find('ul').slideToggle('slow'); 
                    });
        
                jQuery('.vibe-new h2').click(function(){
                    jQuery(this).parent().find('.vibe-custom').fadeToggle('slow'); 
                    });
	});
        
        
        jQuery('.vibe-drop h4').click(function(){
           jQuery(this).parent().find('ul').slideToggle('slow'); 
        });
        
        jQuery('.vibe-new h2').click(function(){
           jQuery(this).parent().find('.vibe-custom').fadeToggle('slow'); 
        });
        
        
	
});