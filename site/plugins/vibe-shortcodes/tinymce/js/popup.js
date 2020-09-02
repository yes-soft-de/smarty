
// start the popup specefic scripts
// safe to use $
jQuery(document).ready(function($) {
    var vibes = {
    	loadVals: function()
    	{
                var shortcode = $('#_vibe_shortcode').text(),
    			uShortcode = shortcode;
    		
    		// fill in the gaps eg {{param}}
    		$('.vibe-input').each(function() {
    			var input = $(this),
    				id = input.attr('id'),
    				id = id.replace('vibe_', ''),		// gets rid of the vibe_ prefix
    				re = new RegExp("{{"+id+"}}","g");
    				
    			uShortcode = uShortcode.replace(re, input.val());
    		});
    		
    		// adds the filled-in shortcode as hidden input
    		$('#_vibe_ushortcode').remove();
    		$('#vibe-sc-form-table').prepend('<div id="_vibe_ushortcode" class="hidden">' + uShortcode + '</div>');
    	},
    	cLoadVals: function()
    	{
    		var shortcode = $('#_vibe_cshortcode').text(),
    			pShortcode = '';
    			shortcodes = '';
    		
    		// fill in the gaps eg {{param}}
    		$('.child-clone-row').each(function() {
    			var row = $(this),
    				rShortcode = shortcode;
    			
    			$('.vibe-cinput', this).each(function() {
    				var input = $(this),
    					id = input.attr('id'),
    					id = id.replace('vibe_', '')		// gets rid of the vibe_ prefix
    					re = new RegExp("{{"+id+"}}","g");

    				rShortcode = rShortcode.replace(re, input.val());
    			});
    	
    			shortcodes = shortcodes + rShortcode + "\n";
    		});
    		
    		// adds the filled-in shortcode as hidden input
    		$('#_vibe_cshortcodes').remove();
    		$('.child-clone-rows').prepend('<div id="_vibe_cshortcodes" class="hidden">' + shortcodes + '</div>');
    		
    		// add to parent shortcode
    		this.loadVals();
    		pShortcode = $('#_vibe_ushortcode').text().replace('{{child_shortcode}}', shortcodes);
    		
    		// add updated parent shortcode
    		$('#_vibe_ushortcode').remove();
    		$('#vibe-sc-form-table').prepend('<div id="_vibe_ushortcode" class="hidden">' + pShortcode + '</div>');
    	},
    	children: function()
    	{
    		// assign the cloning plugin
    		$('.child-clone-rows').appendo({
    			subSelect: '> div.child-clone-row:last-child',
    			allowDelete: false,
    			focusFirst: false
    		});
    		
    		// remove button
    		$('.child-clone-row-remove').live('click', function() {
    			var	btn = $(this),
    				row = btn.parent();
    			
    			if( $('.child-clone-row').size() > 1 )
    			{
    				row.remove();
    			}
    			else
    			{
    				alert('You need a minimum of one row');
    			}
    			
    			return false;
    		});
    		
    		// assign jUI sortable
    		$( ".child-clone-rows" ).sortable({
				placeholder: "sortable-placeholder",
				items: '.child-clone-row'
				
			});
                        
                       
    	},
    	resizeTB: function()
    	{
			var	ajaxCont = $('#TB_ajaxContent'),
				tbWindow = $('#TB_window'),
				vibePopup = $('#vibe-popup');
                tbWindow.css({
                height: vibePopup.outerHeight(),
                width: vibePopup.outerWidth(),
                marginLeft: -(vibePopup.outerWidth()/2)
                    });
                                  
            

			ajaxCont.css({
				paddingTop: 0,
				paddingLeft: 0,
				paddingRight: 0,
				height: (tbWindow.outerHeight()-0),
				overflow: 'auto', // IMPORTANT
				width: vibePopup.outerWidth()
			});
			
			$('#vibe-popup').addClass('no_preview');
    	},
    	load: function()
    	{  
            $('body').trigger('live');
    		var	vibes = this,
    			popup = $('#vibe-popup'),
    			form = $('#vibe-sc-form', popup),
    			shortcode = $('#_vibe_shortcode', form).text(),
    			popupType = $('#_vibe_popup', form).text(),
    			uShortcode = '';
    		
                //Call special fx
                $(".popup-colorpicker").each(function(){
                    $(this).iris({
                        change: function( event, ui ) {
                            var hexcolor =ui.color.toString();
                            $(this).attr('value',hexcolor);
                            $(this).trigger('change');
                        }
                    });
                });
    		// resize TB
    		vibes.resizeTB();
    		$(window).resize(function() {vibes.resizeTB()});
    		
    		// initialise
    		vibes.loadVals();
    		vibes.children();
    		vibes.cLoadVals();
    		
    		// update on children value change
    		$('.vibe-cinput', form).live('change', function() {
    			vibes.cLoadVals();
    		});
    		
    		// update on value change
    		$('.vibe-input', form).change(function() {
    			vibes.loadVals();
    		});
    		
    		// when insert is clicked
            $('.vibe-insert', form).click(function(event) {event.preventDefault();
                if(parent.tinyMCE)
                {   
                    parent.tinyMCE.execCommand('mceInsertContent', false, $('#_vibe_ushortcode', form).html());
                    tb_remove();
                }
            });
    	}
	}
    
    // run
    $('#vibe-popup').livequery( function() {vibes.load();} );
    
    $('.the-icons i').live('click', function() {
        var inputvalue=$(this).parent().parent().parent().find('.capture-input');
                        inputvalue.val($(this).attr('class'));
                        $(this).parent().parent().find('.clicked').removeClass('clicked');
                        $(this).addClass('clicked');
                        inputvalue.trigger("change");
    		});     
                
                
    $('.popup-colorpicker').live('click', function() { 
        var iris =$(this).parent().find('.iris-picker');
        if(iris.hasClass('show')){
            iris.hide();
            iris.removeClass('show');
        }else{
            iris.show(); 
            iris.addClass('show');
        }
       
    });
    

    $('body').on('live',function(){
        $('.popup-slider').each(function(){
        var slide_val = $(this).next();
        var $this = $(this);
        var std=parseInt(slide_val.attr('data-std'));
        var min=parseInt(slide_val.attr('data-min'));
        var max=parseInt(slide_val.attr('data-max'));
        slide_val.slider({
                    range: "min",
                    value: std,
                    min: min,
                    max: max,
                    slide: function( event, ui ) { 
                        var val=ui.value;
                        $this.attr('value', val +'px');
                        $this.trigger("change");
                    }
                });
           
              
            $(this).val( slide_val.slider( "value" ) +'px');
            $(this).trigger('change');
        });    
    });
    
    $('body').on('live',function(){
        $('.vibe-form-select-hide').each(function(){
            $('#vibe-sc-form-live-preview').show();
            var hide_val = parseInt($(this).attr('rel-hide'));
            var parent = $(this).parent().parent().parent();
            var nextall= parent.nextAll("tbody").slice(0, hide_val);
            
            if($(this).val() == 'other'){
                nextall.show();
            }else{
                nextall.hide();
            }
            $(this).change(function(){ 
                if($(this).val() == 'other'){
                    nextall.show();
                    $('#vibe-popup').css('height','120%');
                }else{
                    nextall.hide();
                    
                }
            });
        });
    });
    
    $('body').on('live',function(){
        $('#vibe_options, #vibe_upload_options').each(function(){
            $(this).parent().parent().hide();
        });
    });

    $('#vibe_type.vibe-cinput').live('change', function() {
        var crow=$(this).parent().parent().next();
       if($(this).val() == 'select'){
           crow.show();
           crow.find('#vibe_options').show(100);
       }else{
           crow.hide(100);
       }
       if($(this).val() == 'upload'){
            crow.next().show();
            crow.next().find('#vibe_upload_options').show(100);
        }else{
            crow.next().hide(100);
        }
    });
});

