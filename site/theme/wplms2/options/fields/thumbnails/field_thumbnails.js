jQuery(document).ready(function(){
    
jQuery('.select_thumbnail').each(function(){
     var value = jQuery(this).val();
     var html=jQuery(this).parent().find('.html');
     var block = html.find('.block');
    block.addClass(value);
    
    if(value == 'direction-hover-1'){
        html.find('.block').each( function() { jQuery(this).hoverdir(); } );
    }
    
    if(value == 'direction-hover-2'){
        html.find('.block').each( function() { jQuery(this).hoverdir({
              hoverDelay : 75  
        }); } );
    }
    if(value == 'direction-hover-3'){
        html.find('.block').each( function() { jQuery(this).hoverdir({
              hoverDelay : 50,
	      inverse : true
        }); } );
    }
});    

jQuery('.select_thumbnail').change(function(){
    
   var value = jQuery(this).val();
   
   
   var html=jQuery(this).parent().find('.html');
   var block = html.find('.block');
   block.removeClass();
   block.addClass('block');
    block.addClass(value);
    
    if(value == 'direction-hover-1'){
        html.find('.block').each( function() { jQuery(this).hoverdir(); } );
    }
    
    if(value == 'direction-hover-2'){
        html.find('.block').each( function() { jQuery(this).hoverdir({
              hoverDelay : 75  
        }); } );
    }
    if(value == 'direction-hover-3'){
        html.find('.block').each( function() { jQuery(this).hoverdir({
              hoverDelay : 50,
	      inverse : true
        }); } );
    }
                            
});

    
});