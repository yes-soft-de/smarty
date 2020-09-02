jQuery(document).ready(function($) {
	$('body').delegate('input[type="submit"].remove_schedule_submit','click',function(e){
        if(confirm(confirm_message_cron )){

        }else{
            e.stopPropagation();
            e.preventDefault();
        }
        
    });
    function recalculate_index(repeatable){
        repeatable.find('.count').each(function(){
            var i= jQuery(this).parent().index();
            jQuery(this).html(i);
        });
    }
	// A hackish way to change the Button text to be more UX friendly
	jQuery('#media-items').bind('DOMNodeInserted',function(){
		jQuery('input[value="Insert into Post"]').each(function(){
				jQuery(this).attr('value','Use This Image');
		});
	});
	
    function vibe_customtypes_upload_media($this) {
        'use strict';

        // Default Logo
        var file_frame, attachment;

        if ( undefined !== file_frame ) {
            file_frame.open();
            return;
        }
        
        console.log($this.data('title'));
        file_frame = wp.media.frames.file_frame = wp.media({
            title: $this.data('title'),
            multiple: false  
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();
            console.log(attachment);
            jQuery($this.data('target')).attr('src',attachment.url);
            jQuery($this.data('save')).val(attachment.id);
        });
        file_frame.open();
    }

    // the upload image button, saves the id and outputs a preview of the image
    jQuery('.meta_box_upload_image_button').click(function(event) { 
        event.preventDefault();
        vibe_customtypes_upload_media($(this));
    });
	
	// the remove image link, removes the image id from the hidden field and replaces the image preview
	jQuery('.meta_box_clear_image_button').click(function() {
		var defaultImage = jQuery(this).parent().siblings('.meta_box_default_image').text();
		jQuery(this).parent().siblings('.meta_box_upload_image').val('');
		jQuery(this).parent().siblings('.meta_box_preview_image').attr('src', defaultImage);
		return false;
	});


	// repeatable fields
	jQuery('.meta_box_repeatable_add').on('click', function(event) {
        event.preventDefault();
		// clone
        var repeatable = jQuery(this).siblings('.meta_box_repeatable');
		var row = repeatable.find('li.hide');
        var lastrow = repeatable.find('li:last-child');
		var clone = row.clone();
        clone.removeClass('hide');

		clone.find('input').val('');
        clone.find('select').val('');
		
		// increment name and id
		
        var inputname=clone.find('input').attr('rel-name');
        clone.find('input').attr('name',inputname);

        var inputname=clone.find('input[type="number"]').attr('rel-name');
        clone.find('input').attr('name',inputname);
       
        var select = clone.find('select');
        var selectname=select.attr('rel-name');
        select.attr('name',selectname);
        select.addClass('selectcpt');
        lastrow.after(clone);
        recalculate_index(repeatable);

            var cpt = select.attr('data-cpt');
            var placeholder = $(this).attr('data-placeholder');
            select.select2({
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
                                        security: $('#vibe_security').val(),
                                        cpt: cpt,
                                        id:select.attr('id'),
                                        post_id:select.attr('data-id'),
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
        
		//
		return false;
	});
	
    jQuery('.meta_box_question_tags_add').on('click', function(event) {
        event.preventDefault();
        // clone
        var repeatable = jQuery(this).parent().find('.meta_box_repeatable');
        var row = jQuery(this).parent().find('.meta_box_repeatable > li.hide');
        var lastrow = jQuery(this).parent().find('.meta_box_repeatable > li:last-child');
        var clone = row.clone();
        clone.removeClass('hide');

        clone.find('input').val('');
        clone.find('select').val('');
        
        // increment name and id
        
        var inputname=clone.find('input.count').attr('rel-name');
        clone.find('input.count').attr('name',inputname);

        var inputname=clone.find('input.marks').attr('rel-name');
        clone.find('input.marks').attr('name',inputname);

        var cinputname=clone.find('input.count').attr('rel-name');
        var minputname=clone.find('input.marks').attr('rel-name');

        var n = jQuery(this).parent().find('.meta_box_repeatable > li').length-1;
        cinputname+='['+n+']';
        minputname+='['+n+']';
        clone.find('input.count').attr('name',cinputname);
        clone.find('input.marks').attr('name',minputname);
       
        var select = clone.find('select');
        var selectname=select.attr('rel-name');
        selectname+='['+n+'][]';
        select.attr('name',selectname);
        lastrow.after(clone);
        select.select2({'allowClear':true});
        return false;
    });

    jQuery('body').delegate('#vibe_quiz_tags-repeatable input[type="number"]', 'change', function(){
        var count = 0;var total = parseInt(0);
        console.log(total);
        jQuery('#vibe_quiz_tags-repeatable input[type="number"].count').each(function(){
            if(!$(this).parent().hasClass('hide')){
                var ival=jQuery(this).val();
                if(ival == 'NAN' || ival ==''){
                    ival=parseInt(0);
                }
                count = parseInt(count) + parseInt(ival);
            }
        });
        jQuery('#total_question_number').text(count);
        jQuery('#vibe_quiz_tags-repeatable input[type="number"].marks').each(function(){
            if(!$(this).parent().hasClass('hide')){
                var ival=jQuery(this).val();
                if(ival == 'NAN' || ival ==''){
                    ival=parseInt(0);
                }else{
                    var c = $(this).parent().find('input.count').val();
                    if(c == 'NAN' || c ==''){c=parseInt(0);}
                    ival = c*ival;
                }
                total = parseInt(total) + parseInt(ival);
            }
        });
        jQuery('#total_question_marks').text(total);
    });
    
    jQuery('body').delegate('#vibe_quiz_questions-repeatable input[type="number"]', 'change', function(){
        var total = parseInt(0);
        console.log(total);
        jQuery('#vibe_quiz_questions-repeatable input[type="number"]').each(function(){
            if(!$(this).parent().hasClass('hide')){
                var ival=jQuery(this).val();
                if(ival == 'NAN' || ival ==''){
                    ival=parseInt(0);
                }
                total = parseInt(total) + parseInt(ival);
            }
        });
        jQuery('#total_quiz_marks').text(total);
    });

    jQuery('.meta_box_add_section').on('click', function(event) {
        event.preventDefault();
        var row = jQuery(this).siblings('.meta_box_repeatable').find('li.section.hide');
        var clone = row.clone();
        clone.removeClass('hide');
        clone.find('input').val('');
        var name=clone.find('input').attr('rel-name');
        clone.find('input').attr('name',name);
        row.after(clone);
    });

    jQuery('.meta_box_add_posttype1').on('click', function(event) {
        event.preventDefault();
        var row = jQuery(this).siblings('.meta_box_repeatable').find('li.posttype1.hide');
        var clone = row.clone();
        clone.removeClass('hide');
        var select = clone.find('select');
        select.val('');

        var name=select.attr('rel-name');
        select.attr('name',name);
        select.addClass('selectcpt');
        row.after(clone);
       
            var cpt = select.attr('data-cpt');
            var placeholder = $(this).attr('data-placeholder');
            select.select2({
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
                                        security: $('#vibe_security').val(),
                                        cpt: cpt,
                                        id:select.attr('id'),
                                        post_id:select.attr('data-id'),
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

    jQuery('.meta_box_add_posttype2').on('click', function(event) {
        event.preventDefault();
        var row = jQuery(this).siblings('.meta_box_repeatable').find('li.posttype2.hide');
        var clone = row.clone();
        clone.removeClass('hide');
        var select = clone.find('select');
        select.val('');

        var name=select.attr('rel-name');
        select.attr('name',name);
        select.addClass('selectcpt');
        row.after(clone);
       
            var cpt = select.attr('data-cpt');
            var placeholder = $(this).attr('data-placeholder');
            select.select2({
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
                                        security: $('#vibe_security').val(),
                                        cpt: cpt,
                                        id:select.attr('id'),
                                        post_id:select.attr('data-id'),
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

	jQuery('.meta_box_repeatable_remove').live('click', function(){
		
        var repeatable=jQuery(this).closest('.meta_box_repeatable');
        jQuery(this).closest('li').remove();
        recalculate_index(repeatable);
		return false;
	});
        
	jQuery('.meta_box_repeatable').sortable({
		opacity: 0.6,
		revert: true,
		cursor: 'move',
		handle: '.handle',
        update: function( event, ui ) {
            recalculate_index(jQuery(this));
        }
	});
        
        
        // repeatable fields
	jQuery('.meta_box_sliders_add').live('click', function() {
		// clone
		var row = jQuery(this).siblings('.meta_box_sliders').find('li:last');
		var clone = row.clone( true, true );
		clone.find('input[type="hidden"]').val('');
                clone.find('input[type="text"]').val('');
                clone.find('textarea').val('');
                var default_src=jQuery('.meta_box_default_image').html();
                if(!default_src){
                    default_src=clone.find('img').attr('rel-default');
                }
                clone.find('img').attr('src',default_src);
		row.after(clone);
		// increment name and id
		clone.find('input.meta_box_upload_image')
			.attr('name', function(index, name) {
				return name.replace(/(\d+)/, function(fullMatch, n) {
					return Number(n) + 1;
				});
			}).attr('id', function(index, name) {
				return name.replace(/(\d+)/, function(fullMatch, n) {
					return Number(n) + 1;
				});
			});
                clone.find('.slide_caption input[type="text"]')
			.attr('name', function(index, name) {
				return name.replace(/(\d+)/, function(fullMatch, n) {
					return Number(n) + 1;
				});
                             });
                clone.find('.slide_caption select')
			.attr('name', function(index, name) {
				return name.replace(/(\d+)/, function(fullMatch, n) {
					return Number(n) + 1;
				});
                             });             
                clone.find('.slide_caption textarea')
			.attr('name', function(index, name) {
				return name.replace(/(\d+)/, function(fullMatch, n) {
					return Number(n) + 1;
				});                
			});
                 clone.find('.step_fields input[type="text"]')
			.attr('name', function(index, name) {
				return name.replace(/(\d+)/, function(fullMatch, n) {
					return Number(n) + 1;
				});
                             });        
                         
		//
		return false;
	});
	
        	
    jQuery('.meta_box_clear_slider_image_button').click(function() {
		var defaultImage = jQuery(this).parent().siblings('.meta_box_preview_image').attr('rel-default');
		jQuery(this).parent().siblings('.meta_box_upload_image').val('');
		jQuery(this).parent().siblings('.meta_box_preview_image').attr('src', defaultImage);
		return false;
	});
        
	jQuery('.meta_box_sliders_remove').live('click', function(){ 
            if(jQuery(this).closest('ul').children().length > 1){
                jQuery(this).closest('li').remove();
            }else{ 
                var answer=confirm('Deleting first slide would diable featured sliders for this post. Are you sure you want to delete this slide?');
                if(answer)
                {jQuery(this).closest('li').remove();}
            }
	   return false;
	});
		
	jQuery('.meta_box_sliders').sortable({
		opacity: 0.6,
		revert: true,
		cursor: 'move',
		handle: '.handle'
	});
     
        
        jQuery('.radio-image-wrapper').click(function(){
            jQuery(this).find('input[type="radio"]').attr("checked","checked");
            
            jQuery(this).parent().find('.select').removeClass("selected");
            jQuery(this).find('.select').addClass("selected");
        });
        
        
       
        var parenttr = jQuery('#vibe_select_featured').parent().parent();
         
                parenttr.siblings().eq(0).show('fast');
                parenttr.siblings().eq(1).hide('fast');
                parenttr.siblings().eq(2).hide('fast');
                parenttr.siblings().eq(3).hide('fast');
                parenttr.siblings().eq(4).hide('fast');
        
        var selectvalue = jQuery('#vibe_select_featured').val();
            if(selectvalue == 'disable'){
                parenttr.siblings().eq(0).hide('fast');
                parenttr.siblings().eq(1).hide('fast');
                parenttr.siblings().eq(2).hide('fast');
                 parenttr.siblings().eq(3).hide('fast');
                  parenttr.siblings().eq(4).hide('fast');
            }  
            if(selectvalue == 'video'){
                parenttr.siblings().eq(0).hide('fast');
                parenttr.siblings().eq(1).show('fast');
                parenttr.siblings().eq(2).hide('fast');
                parenttr.siblings().eq(3).hide('fast');
                parenttr.siblings().eq(4).hide('fast');
            }
             if(selectvalue == 'iframevideo'){
                parenttr.siblings().eq(0).hide('fast');
                parenttr.siblings().eq(2).show('fast');
                parenttr.siblings().eq(1).hide('fast');
                parenttr.siblings().eq(3).hide('fast');
                parenttr.siblings().eq(4).hide('fast');
            }
            if(selectvalue == 'audio'){
                parenttr.siblings().eq(0).hide('fast');
                parenttr.siblings().eq(3).show('fast');
                parenttr.siblings().eq(1).hide('fast');
                parenttr.siblings().eq(2).hide('fast');
                parenttr.siblings().eq(4).hide('fast');
            }
            if(selectvalue == 'gallery'){
                parenttr.siblings().eq(0).show('fast');
                parenttr.siblings().eq(1).hide('fast');
                parenttr.siblings().eq(2).hide('fast');
                 parenttr.siblings().eq(3).hide('fast');
                 parenttr.siblings().eq(4).hide('fast');
            }
             if(selectvalue == 'other'){
                parenttr.siblings().eq(0).hide('fast');
                parenttr.siblings().eq(1).hide('fast');
                parenttr.siblings().eq(2).hide('fast');
                 parenttr.siblings().eq(4).show('fast');
                 parenttr.siblings().eq(3).hide('fast');
            }
            
        jQuery('#vibe_select_featured').change(function(){
            var selectvalue = jQuery('#vibe_select_featured').val();
            if(selectvalue == 'disable'){
                parenttr.siblings().eq(0).hide('fast');
                parenttr.siblings().eq(1).hide('fast');
                parenttr.siblings().eq(2).hide('fast');
                 parenttr.siblings().eq(3).hide('fast');
                  parenttr.siblings().eq(4).hide('fast');
            }  
            if(selectvalue == 'video'){
                parenttr.siblings().eq(0).hide('fast');
                parenttr.siblings().eq(1).show('fast');
                parenttr.siblings().eq(2).hide('fast');
                parenttr.siblings().eq(3).hide('fast');
                parenttr.siblings().eq(4).hide('fast');
            }
             if(selectvalue == 'iframevideo'){
                parenttr.siblings().eq(0).hide('fast');
                parenttr.siblings().eq(2).show('fast');
                parenttr.siblings().eq(1).hide('fast');
                parenttr.siblings().eq(3).hide('fast');
                parenttr.siblings().eq(4).hide('fast');
            }
            if(selectvalue == 'audio'){
                parenttr.siblings().eq(0).hide('fast');
                parenttr.siblings().eq(3).show('fast');
                parenttr.siblings().eq(1).hide('fast');
                parenttr.siblings().eq(2).hide('fast');
                parenttr.siblings().eq(4).hide('fast');
            }
            if(selectvalue == 'gallery'){
                parenttr.siblings().eq(0).show('fast');
                parenttr.siblings().eq(1).hide('fast');
                parenttr.siblings().eq(2).hide('fast');
                 parenttr.siblings().eq(3).hide('fast');
                 parenttr.siblings().eq(4).hide('fast');
            }
             if(selectvalue == 'other'){
                parenttr.siblings().eq(0).hide('fast');
                parenttr.siblings().eq(1).hide('fast');
                parenttr.siblings().eq(2).hide('fast');
                 parenttr.siblings().eq(4).show('fast');
                 parenttr.siblings().eq(3).hide('fast');
            }
        });
        
        jQuery('.plus_more').click(function(){ 
           jQuery(this).next().next().slideToggle('fast');
        });
        jQuery('.step_more').click(function(){ 
           jQuery(this).next().slideToggle('fast');
        });
        
        jQuery('.more_settings').click(function(){ console.log('clcok');
           jQuery(this).parent().next().fadeToggle('fast');
        });
	
    // SELECT 2 Migration
    $('.selectcpt').each(function(){
        var $this = $(this);
        var cpt = $(this).attr('data-cpt');
        var placeholder = $(this).attr('data-placeholder');
        $(this).select2({
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
                                    security: $('#vibe_security').val(),
                                    cpt: cpt,
                                    id:$this.attr('id'),
                                    post_id:$this.attr('data-id'),
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
        }).on('select2:open',function(){
          if($('.select2-container .select2-dropdown').hasClass('select2-dropdown--below')){
            var topmargin = 35;
            $('.select2-container:not(.select2)').css('top', '+='+ topmargin +'px');
              //$('.select2-container:not(.select2) .select2-dropdown--below').css('margin-top','45px');
          }
        });;
    });
    
    $('.selectgroup').each(function(){
        var $this = $(this);
        var placeholder = $(this).attr('data-placeholder');
        $(this).select2({
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
                        return  {   action: 'get_groups', 
                                    security: $('#vibe_security').val(),
                                    id:$this.attr('id'),
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
        }).on('select2:open',function(){
          if($('.select2-container .select2-dropdown').hasClass('select2-dropdown--below')){
            var topmargin = 35;
            $('.select2-container:not(.select2)').css('top', '+='+ topmargin +'px');
              //$('.select2-container:not(.select2) .select2-dropdown--below').css('margin-top','45px');
          }
        });
    });

                            
    // SELECT 2 END
}); 


    jQuery(document).ready(function($){
             var builder_enable=jQuery('.builder_enable').find('#builder_enable');
             
                if(builder_enable.is(':checked')){ 
                    jQuery('.builder_enable').addClass('_enable');
                }
                
                 jQuery('.builder_enable').click(function(){ 
                     
                    var checkbox = jQuery(this).find('input');
                    
                        if(jQuery(this).hasClass('_enable')){
                            
                            jQuery(this).removeClass('_enable');
                            checkbox.removeAttr('checked');
                        }else{
                            checkbox.attr('checked','checked');   
                            jQuery(this).addClass('_enable');
                        }
                 });
                 
                 
             jQuery('.checkbox_val').each(function(){
                     if(jQuery(this).is(':checked')){ 
                    jQuery(this).parent().find('.checkbox_button').addClass('enable');
                    }
                 });
             
                
                
                 jQuery('.checkbox_button').click(function(){ 
                     
                    var checkbox = jQuery(this).parent().find('input');
                    
                        if(jQuery(this).hasClass('enable')){
                            jQuery(this).removeClass('enable');
                            checkbox.removeAttr('checked');
                        }else{
                            checkbox.attr('checked','checked');   
                            jQuery(this).addClass('enable');
                        }
                 });
                 
                 jQuery('.select_val').each(function(){
                     if(jQuery(this).val() == 'S'){ 
                    jQuery(this).parent().find('.select_button').addClass('enable');
                    }else{
                        jQuery(this).parent().find('.select_button').removeClass('enable');
                    }
                 });
             
                
                
                 jQuery('.select_button').click(function(){ 
                     
                    var select = jQuery(this).parent().find('select.select_val');
                        if(jQuery(this).hasClass('enable')){
                            jQuery(this).removeClass('enable');
                            select.val('H');
                        }else{
                            select.val('S');
                            jQuery(this).addClass('enable');
                        }
                 });
                 jQuery('.color').iris({palettes: ['#125', '#459', '#78b', '#ab0', '#de3', '#f0f']});
                 jQuery('.color').click(function(){
                    jQuery(this).iris('toggle');
                 });
                 jQuery('.select2-select').each(function(){
                    jQuery(this).select2({allow_clear: true});
                 }); 
                 jQuery( ".date-picker-field" ).datepicker({
                    dateFormat: "yy-mm-dd",
                    numberOfMonths: 1,
                    showButtonPanel: true,
                });
                 jQuery( ".timepicker" ).each(function(){
                 jQuery(this).timePicker({
                      show24Hours: false,
                      separator:':',
                      step: 15
                  });
                });
    });

jQuery(document).ready(function($){
    $('#wplms_email_template').each(function(){
        var html = $(this).val();
        $('.wplms_email_template iframe').contents().find('html').html(html);
    });
    jQuery('.colorpicker').iris({
        palettes: ['#125', '#459', '#78b', '#ab0', '#de3', '#f0f'],
        change: function(event, ui){
            var ref = $(event.target).attr('data-ref').split(',');
            for ( var i = 0, l = ref.length; i < l; i++ ) { 
                var css = $(event.target).attr('data-css');
                if(css === 'color'){ 
                    var element = $('.wplms_email_template iframe').contents().find(ref[i]);

                    if(element.children().length>0){
                        element.find('*').each(function(i) { 
                            if( $(this).text().length > 5){
                                $(this).css(css,$(event.target).val());
                                $(this).css('border-color',$(event.target).val());
                            }
                        });
                    }else{
                        $('.wplms_email_template iframe').contents().find(ref[i]).css(css,$(event.target).val());
                    }
                    
                }else{
                    $('.wplms_email_template iframe').contents().find(ref[i]).css(css,$(event.target).val());
                    $('.wplms_email_template iframe').contents().find(ref[i]).attr('bgcolor',$(event.target).val());
                }
            }
            var html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />'+$('.wplms_email_template iframe').contents().find('html').html()+'</html>';
            $('#wplms_email_template').val(html);
        }
    });
    jQuery('.colorpicker').click(function(){
        jQuery(this).iris('toggle');
    });
    $('#show_generated').click(function(){
        $('#wplms_email_template').slideToggle(200);
    });
    $('#restore_default').click(function(){
        var r = confirm("Are you sure you want to restore to default ? This will remove all your changes in the Template, after restore press apply changes to save.");
        if (r == true) {
            $.ajax({
                 type: "POST",
                  url: ajaxurl,
                  data: { action: 'lms_restore_email_template', 
                          security: $('#security').val(),
                        },
                  cache: false,
                  success: function (html) {
                    $('.wplms_email_template iframe').contents().find('html').html(html);
                    $('#wplms_email_template').val(html);
                  }
            });
        } 
    });
    $('#apply_settings').click(function(){
        var defaultxt = $(this).text();
        var $this = $(this);
        var r = confirm("Are you sure you want to save the template ?");
        if (r == true) {
            $.ajax({
                 type: "POST",
                  url: ajaxurl,
                  data: { action: 'lms_save_email_template', 
                          security: $('#security').val(),
                          template:$('#wplms_email_template').val()
                        },
                  cache: false,
                  success: function (html) {
                     $this.text(html);
                     setTimeout(function(){
                        $this.text(defaultxt);
                     },2000);
                  }
            });
        }
    });
    $('.activate_license_toggle').click(function(){
        $(this).parent().find('.activate_license').toggle(200);
    });
    //Import BuddyPress Emails
    jQuery('#import_wplms_emails_buddypress').on('click',function(event){
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'lms_import_wplms_emails', 
                    security: $('#_wpnonce').val(),
                },
            cache: false,
            success: function (html) {
                $this.text(html);
                setTimeout(function(){
                $this.text(defaultxt);
                },2000);
            }
        });
    });

    $('.timeout').each(function(){
        var $this = $(this);
        setTimeout(function(){$this.remove();},3000);
    });
});
