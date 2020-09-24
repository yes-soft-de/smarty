function vibe_demo_switcher_select(relid, labelclass){
	jQuery(this).prev('input[type="radio"]').prop('checked');

	jQuery('.vibe-radio-img-'+labelclass).removeClass('vibe-radio-img-selected');	
	
	jQuery('label[for="'+relid+'"]').addClass('vibe-radio-img-selected');
}//function

jQuery(document).ready(function($){
    var h =  $('#demo_switcher_wrapper').outerHeight(true);
    if(h > 0){
       $('#demo_switcher_wrapper_margin').height(h); 
    }
    
    $(window).load(function(){
        var li_id = '#'+$('#demo_switcher_wrapper').closest('.vibe-opts-group-tab').attr('id')+'_li';
        $('body').find(li_id).on('click',function(){
            setTimeout(function(){
                var h =  $('#demo_switcher_wrapper').outerHeight(true);
                $('#demo_switcher_wrapper_margin').height(h);
            },500);
        });
    });
	
	$('.import_demo_home').on('click',function(){
		var $this =  $(this);
		demo = $this.data('demo');
        
        if($this.hasClass('disable_button'))
            return false;

        $this.addClass('disable_button');

        setTimeout(function(){
            $this.addClass('progress80');
        },500);

		$.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'switch_demo_homes', 
                    security: $('#switch_demo_layouts').val(),
                    demo:demo
                  },
            cache: false,
            success: function (html) {
                $this.addClass('progress100');
                setTimeout(function(){
                    $this.html('<i class="dashicons dashicons-yes"></i>');
                },500);
            }
        });
	});
	$('.import_demo_layout').on('click',function(){
		var $this =  $(this);

        if($this.hasClass('disable_button'))
            return false;

        $this.addClass('disable_button');

		demo = $this.data('demo');

        setTimeout(function(){
            $this.addClass('progress80');
        },500);
		

		$.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'switch_demo_layout', 
                    security: $('#switch_demo_layouts').val(),
                    demo:demo
                  },
            cache: false,
            success: function (html) {
                $this.addClass('progress100');
                setTimeout(function(){
                    $this.html('<i class="dashicons dashicons-yes"></i>');
                },500);

            }
        });
	});
	
});