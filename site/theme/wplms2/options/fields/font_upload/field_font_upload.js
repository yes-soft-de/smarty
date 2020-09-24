jQuery(document).ready(function(){ /*
  jQuery('.font-upload').click(function(){ alert('click');
     jQuery.ajax({
                                    type: "POST",
                                    url: ajaxurl,
                                    data:
                                    {
					action : 'upload_font',
					name : jQuery('#font-name').val(),
                                        eot : jQuery('#font-eot').val(),
                                        ttf : jQuery('#font-ttf').val(),
                                        woff : jQuery('#font-woff').val(),
                                        svg : jQuery('#font-svg').val()
                                    },
                                    error: function( xhr, ajaxOptions, thrownError ){
					console.log('error occurred' +ajaxOptions);
                                    },
                                    success: function( data ){
                                        console.log(data);
                                        var name =jQuery('#font-name').val();
                                        jQuery('#font-name').val('');
                                        jQuery('#font-eot').val('');
                                        jQuery('#font-ttf').val('');
                                        jQuery('#font-woff').val('');
                                        jQuery('#font-svg').val('');

                                        jQuery('#font-uploader-form').before('<li><h2>Custom Font '+name+':  &rsaquo;</h2><a href="javascript:void(0);" class="vibe-opts-custom-font-remove">Remove</a></li>');
                                        }
                                    }); 
  });	*/
});