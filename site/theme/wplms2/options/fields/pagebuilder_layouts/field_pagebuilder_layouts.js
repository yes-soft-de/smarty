jQuery(document).ready(function(){
	
	jQuery('.vibe-opts-layout-remove').live('click', function(){
                 ConfirmDialog('Are you sure, you want to delete Layout ?',jQuery(this));
                 
	});
        
});

function ConfirmDialog(message,obj){
    var $=jQuery;
    $('<div></div>').appendTo('body')
                    .html('<p class="dialog_message">'+message+'?</p>')
                    .dialog({
                        modal: true, title: 'Delete message', zIndex: 10000, autoOpen: true,
                        width: 'auto', resizable: false,
                        buttons: {
                            Yes: function () {
                                $.ajax({
                                    type: "POST",
                                    url: ajaxurl,
                                    data:
                                    {
					action : 'delete_layout',
					name : obj.attr('rel-layout')
                                    },
                                    error: function( xhr, ajaxOptions, thrownError ){
					console.log('error occurred' +ajaxOptions);
                                    },
                                    success: function( data ){
                                        obj.parent().fadeOut('slow', function(){jQuery(this).remove();});
                                        
                                        }
                                    });
                             $(this).dialog("close");   
                            },
                            No: function () {
                                $(this).dialog("close");
                            }
                        },
                        close: function (event, ui) {
                            $(this).remove();
                        }
                    });
    };