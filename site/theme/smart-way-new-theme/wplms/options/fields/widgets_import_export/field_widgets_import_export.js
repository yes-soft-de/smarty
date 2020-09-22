jQuery(document).ready(function($){
	
	jQuery('.import_data').live('click', function(){jQuery(this).addClass('disabled');
                 Confirmimport('Importing this field would overwrite any existing data.<br />Are you sure, you want to continue',jQuery(this));
                 
	});
        
});

function Confirmimport(message,obj){
var $= jQuery;
    jQuery('<div></div>').appendTo('body')
                    .html('<p class="dialog_message">'+message+'?</p>')
                    .dialog({
                        modal: true, title: 'Import Code', zIndex: 10000, autoOpen: true,
                        width: 'auto', resizable: false,
                        buttons: {
                            Yes: function () {
                                $.ajax({
                                    type: "POST",
                                    url: ajaxurl,
                                    data:
                                    {
					action : 'import_data',
					name : obj.attr('rel-id'),
                                        code : obj.parent().find('.import_code').val()
                                    },
                                    error: function( xhr, ajaxOptions, thrownError ){
					console.log('error occurred' +ajaxOptions);
                                    },
                                    success: function( data ){
                                        //console.log(data);
                                        obj.after('<span class="success"> Imported sucessfully !<span>');
                                        setTimeout(function(){obj.parent().find('.success').fadeOut(300);},3000);
                                        jQuery('.import_data').removeClass('disabled');
                                        }
                                    });
                             $(this).dialog("close");   
                            },
                            No: function () {
                                $(this).dialog("close");
                                jQuery('.import_data').removeClass('disabled');
                            }
                        },
                        close: function (event, ui) {
                            $(this).remove();
                        }
                    });
    };