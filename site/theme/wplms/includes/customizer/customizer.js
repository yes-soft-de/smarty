( function() {
    
    var api = wp.customize;

    api('vibe_customizer[theme_skin]',function(value){
        value.bind( function (newval){
            if (confirm('Change of Theme Skin would overwrite most of the colors in Customiser. Are you sure you want to change to a new skin ?') == true) {
                 jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'reset_customizer_colors', 
                            value: newval,
                        },
                    cache: false,
                    success: function (html) {
                        api.previewer.refresh();
                        setTimeout(function(){api.previewer.refresh();},2000);
                    }
                });
            } else {
                jQuery('#customize-control-theme_skin select').val(jQuery.data(this, 'current'));
            }
        });
    });

    jQuery( document ).ready( function( $ ) {
      
        $( 'input.alpha-color-control' ).alphaColorPicker();
    });
})(jQuery);