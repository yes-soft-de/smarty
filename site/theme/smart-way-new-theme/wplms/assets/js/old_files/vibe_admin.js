jQuery(document).ready(function(){
   jQuery('.on_off').click(function(e){
       e.preventDefault();
      if(jQuery(this).hasClass('active')){
          jQuery(this).removeClass('active');
          jQuery(this).parent().parent().find('.sidebar').hide(200);
          jQuery(this).parent().parent().find('.select-sidebar select').val('');
      }else{
          jQuery(this).addClass('active');
          jQuery(this).parent().parent().find('.sidebar').show(200);
      }
   });
});   
 
jQuery(document).ready(function($){
  jQuery('.sample_data_install:not(.disabled)').click(function(){ 
            $('.sample_data_install').hide();
            $('#loading').addClass('active');
            $(this).show();
            jQuery(this).addClass('disabled');
            var file =jQuery(this).attr('data-file');
           jQuery.ajax({
                      type: "POST",
                      url: ajaxurl,
                      data:
                      {
                        action : 'import_sample_data',
                        file : file
                        },
                      error: function( xhr, ajaxOptions, thrownError ){
                            jQuery(this).after('<span class="error">Some error occured !<span>');
                            console.log('error occurred' +ajaxOptions);
                            $('#loading').removeClass('active');
                        },
                      success: function( data ){ 
                            $('#loading').removeClass('active');
                            jQuery('.install_buttons').html('<span class="success">'+data+'<span>');
                          }
                   });
        });
});

jQuery(document).ready(function($){
    
    jQuery('.author-social-remove').live('click', function(){ 
      jQuery(this).parent().parent().fadeOut('slow', function(){jQuery(this).remove();});
    });
    $('#add_social_info').click(function(event){
      event.preventDefault();
      var new_input = jQuery('#socialtable tr:last-child').clone();
      jQuery('#socialtable').append(new_input);
      jQuery('#socialtable tr:last-child').removeAttr('style');
      jQuery(new_input).find('select').each(function(){
        jQuery(this).attr('name' , jQuery(this).attr('rel-name'));
      });
    
      jQuery(new_input).find('input').each(function(){
         jQuery(this).attr('name' , jQuery(this).attr('rel-name'));
      });
      return false;
    });
    $('select.chosen,select.chzn-select').each(function(){
      $(this).select2();
    });

});