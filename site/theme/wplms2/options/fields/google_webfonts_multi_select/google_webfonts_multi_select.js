/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function(){
   jQuery('#reset-google-fonts').click(function(){
      jQuery.ajax({
                                    type: "POST",
                                    url: ajaxurl,
                                    data:
                                    {
					action : 'reset_googlewebfonts'
                                    },
                                    error: function( xhr, ajaxOptions, thrownError ){
					console.log('error occurred' +ajaxOptions);
                                    },
                                    success: function( data ){ console.log(data);
                                        jQuery('#reset-google-fonts').after('<span class="success">Google Fonts List Updated.</span>');
                                    } 
   });
 });  
});   