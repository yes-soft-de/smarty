jQuery(document).ready(function(){
	jQuery('.vibe-opts-custom-taxonomy-remove').live('click', function(){ 
		jQuery(this).parent().fadeOut('slow', function(){jQuery(this).remove();});
	});
	
	jQuery('.vibe-opts-custom-taxonomy-add').click(function(){ 	
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
       jQuery('.sortable_vibe_custom_taxonomy').sortable({
                placeholder: 'ui-state-highlight',
                handle : '.sort_order',
                stop: function(e, ui) {
                    jQuery(this).find('li').each(function(){
                        var i =  jQuery(this).index()-1;
                        var sid = jQuery(this).find('select').attr('id');
                        jQuery(this).find('select').attr('name',sid+'['+i+']');
                        jQuery(this).find('input').each(function(){
                            var id = jQuery(this).attr('id');
                            jQuery(this).attr('name',id+'['+i+']');
                        });
                    });
                }});
       jQuery('.sortable_vibe_custom_taxonomy').disableSelection();
	
});