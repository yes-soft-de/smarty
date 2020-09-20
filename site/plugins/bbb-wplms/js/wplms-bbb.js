jQuery(document).ready(function($){
	//hiding some options by default
	if(!$('.mrcourses_div').hasClass('show')){
		$('.mrcourses_div').hide();
	}
	if(!$('.mrusers_div').hasClass('show')){
		 $('.mrusers_div').hide();
	}
	if(!$('.mrgroups_div').hasClass('show')){
		 $('.mrgroups_div').hide();
	}
   	if(!$('tr.reminder_duration').hasClass('show')){
		 $('tr.reminder_duration').hide();
	}

    
	$('.selectusers_bbb').each(function(){
    	var $this = $(this);
	    $this.select2({
	        minimumInputLength: 4,
	        placeholder: $(this).attr('data-placeholder'),
	        closeOnSelect: true,
	        language: {
	          inputTooShort: function() {
	            return bbb_meetings_strings.more_chars;
	          }
	        },
	        ajax: {
	            url: bbb_meetings_strings.ajax_url,
	            type: "POST",
	            dataType: 'json',
	            delay: 250,
	            data: function(term){ 
	                    return  {   action: 'select_users_bbb', 
	                                security: bbb_meetings_strings.security,
	                                q: term,
	                            }
	            },
	            processResults: function (data) {
	                return {
	                    results: data
	                };
	            },       
	            cache:true  
	        },
	        templateResult: function(data){
	            return '<img width="32" src="'+data.image+'">'+data.text;
	        },
	        templateSelection: function(data){
	            return '<img width="32" src="'+data.image+'">'+data.text;
	        },
	        escapeMarkup: function (m) {
	            return m;
	        }
	    });
  	});
	//courses select2 
	$('.mrcourses').each(function(){
        if(jQuery(this).hasClass('select2-hidden-accessible')){
            return;
        }
        var $this = jQuery(this);
        var cpt = $this.attr('data-cpt');
        var placeholder = $this.attr('data-placeholder');
        $this.select2({
            minimumInputLength: 4,
            placeholder: placeholder,
            closeOnSelect: true,
            allowClear: true,
            ajax: {
                url: ajaxurl,
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function(term){ 
                        return  {   action: 'get_admin_select_cpt', 
                                    security: bbb_meetings_strings.vibe_security,
                                    cpt: cpt,
                                    q: term,
                                }
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },       
                cache:true  
            },
        });
    });


	$('.mrgroups').select2({
        minimumInputLength: 4,
        placeholder: $(this).attr('data-placeholder'),
        closeOnSelect: true,
        allowClear: true,
        language: {
          inputTooShort: function() {
            return bbb_meetings_strings.more_chars;
          }
        },
        ajax: {
            url: ajaxurl,
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function(term){ 
                    return  {   action: 'get_front_groups_bbb', 
                                security: bbb_meetings_strings.security,
                                q: term,
                            }
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },       
            cache:true  
        },
    });

	$('select#mrestriction').change(function(){
		var $this = jQuery(this);
      	var choice = $this.val();
      	if(choice=='selected_users'){
      		$('.mrcourses_div').hide();
      		$('.mrgroups_div').hide();
        	$('.mrusers_div').show();
      	}else if(choice=='course_students'){
      		$('.mrcourses_div').show();
      		$('.mrusers_div').hide();
      		$('.mrgroups_div').hide();
      	}else if(choice=='group'){
      		$('.mrgroups_div').show();
      		$('.mrusers_div').hide();
      		$('.mrcourses_div').hide();
      	}else{
      		$('.mrgroups_div').hide();
        	$('.mrcourses_div').hide();
    		$('.mrusers_div').hide();
      	}
	});
	$('input[name="mdr"]').change(function(){
		var $this = $(this);
		var enabled = $this.is(':checked');
		if(enabled)
			$('tr.reminder_duration').show();
		else
			$('tr.reminder_duration').hide();

	});
	$('input[name="SubmitCreate"]').on('click',function(event){
		//ajax call send json
		event.preventDefault();
		var $this = $(this);
		var defaultxt = $(this).attr('value');
		var meeting_id ='';
		var action = 'create_new_meeting';
		var data = [];
		var security = bbb_meetings_strings.security;
		var temp_text = bbb_meetings_strings.creating;
		var parent = $(this).closest('#wplms_bbb_create_form');
		var go_ahead =  1;
		if(parent.find('.wplm_bbb_meeting_id').length > 0){
			var meeting_id = parent.find('.wplm_bbb_meeting_id').val();
			action = 'edit_meeting';
			security = parent.find('#wplms_bbb_edit_meeting_security').val();
			temp_text = bbb_meetings_strings.editing;
		}
		parent.find('.bbb_create_m_field').each(function(){
	        
	        var field = $(this);
	        
	        if(field.attr('type') == 'checkbox'){
	        	if($(field).prop('checked') === true){
	        		var val = field.val();
	        	}else{
	        		var val = '';
	        	}
	        }else{
	        	var val = field.val();
	        }

	        if((field.hasClass('required') && (field.val() == null ||  field.val() == ''))){
	        	field.css('border','1px solid red');
	        	alert(bbb_meetings_strings.required_warning);
	        	go_ahead = 0;
	        	return false;

	        	event.stopPropagation();
	        }else{
	        	field.css('border','none');
	        }

	    	if(val != 'undefined' && val != null && field.length>0){
	    		data.push({"field":field.attr('name'),"value":val});
	    	}
	    });
	   

       
		if(go_ahead){
			$this.attr('value',temp_text);
			$.ajax({
	            type: "POST",
	            url: ajaxurl,
	            data: { action: action, 
	                    security: security,
	                    meeting_id:meeting_id,
	                    data:JSON.stringify(data),
	                  },
	            cache: false,
	            success: function (html) {
	            	parent.next('.bbb_create_meeting_message').html(html);
	            	parent.next('.bbb_create_meeting_message').show();
	            	setTimeout(function(){
	            		parent.next('.bbb_create_meeting_message').hide();
	            	},5000);
	                $this.attr('value',defaultxt);

	            }
	        });
		}
	   
	});
	//embed shortcode to wp editor 
    $( 'body' ).delegate( '.insert_meeting', "click", function(event) {
      if(!$(this).hasClass('disabled')){
        event.preventDefault();
        var win = window.dialogArguments || opener || parent || top; 
        win.send_to_editor("[wplms_bbb token='"+$(this).attr('data-id')+"'][/wplms_bbb]");
        parent.window.tb_remove();
      }
     
    });
    $( 'body' ).delegate( '.delete_meeting', "click", function(event) {
      if(!$(this).hasClass('disabled')){
      		var $this = $(this);
        	event.preventDefault();
	        if(confirm(bbb_meetings_strings.sure)){
				var defaultxt = $this.text();
				$this.text(bbb_meetings_strings.deleting);   
				$.ajax({
		            type: "POST",
		            url: ajaxurl,
		            data: { action: 'delete_wplms_bb_meeting', 
		                    security: bbb_meetings_strings.security,
		                    meeting_id:$this.attr('data-id'),
		                  },
		            cache: false,
		            success: function (html) {
		                $this.attr('value',defaultxt);
		                $this.parent().parent().remove();
		            }
	        	});
			}else{
				return false;
			}
      }
    });
});     