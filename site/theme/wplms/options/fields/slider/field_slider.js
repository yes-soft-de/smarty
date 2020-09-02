jQuery.noConflict();
jQuery(document).ready(function(){
	
        var jqslider=jQuery( ".jqslider" );
         
	jqslider.each(function(){
            var defaultval=parseInt(jQuery(this).attr('rel-default'));
            var minval=parseInt(jQuery(this).attr('rel-min'));
            var maxval=parseInt(jQuery(this).attr('rel-max'));
           var jqinput=jQuery(this).next();
           jQuery(this).slider({
            range: "min",
            value: defaultval,
            min: minval,
            max: maxval,
            slide: function( event, ui ) {
                jqinput.val(ui.value );
            }
        });
        jqinput.val(jQuery(this).slider( "value" ) );
        });
});