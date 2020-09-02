jQuery(document).ready(function($){
    
    $('#course-category-select').change(function(event){
      
    	if($(this).val() === 'new'){
    		$('#new_course_category').addClass('animate cssanim fadeIn load');
    	}else{
            $('#new_course_category').removeClass('animate cssanim fadeIn load');
        }
    });

    $('#course-linkage-select').change(function(event){
      
      if($(this).val() === 'add_new'){
        $('#new_course_linkage').addClass('animate cssanim fadeIn load');
      }else{
            $('#new_course_linkage').removeClass('animate cssanim fadeIn load');
            $('#save_course_action').addClass('reload_page');
        }
    });

    $( ".date_box" ).datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: true,
    });
    
$('.remove_text_click').on('click',function(){
    var defaulttext=$(this).text();
    $(this).text('');

});
$('body').delegate('*[data-help-tag]','click',function(event){
  
     var n=parseInt($(this).attr('data-help-tag'));
     n--;
    $('.course-create-help li').removeClass('active');
    $('.course-create-help li:eq('+n+')').addClass('active');
});



$('.color_picker').each(function(){
    var $this = $(this);
    $this.css('background',$this.val());

    $this.iris({
        width: 200,
        hide: true,
        change: function(event, ui) {
            $this.css( 'background', ui.color.toString());
        }
    });
});

$('.color_picker').on('click',function(){
    $(this).parent().find('.iris-picker').toggle(100);
});

$('#course_creation_tabs > ul > li').click(function(event){
    event.preventDefault();
    if($(this).hasClass('active'))
        return;

    if($(this).parent().hasClass('islive')){
        var n = $(this).index();
        $('.islive li.active').removeClass('active');
        if ($(this).attr('class') != undefined){
            var itemclass = $(this).attr('class');
            itemclass = itemclass.replace(' done', '');    
        }
        $(this).addClass('active');
        $('.create_course_content > div').removeClass('active');
        $('.edit_course_content > div').removeClass('active');
        $('.course-create-help > ul').removeClass('active');

        if($('#'+itemclass).length){
            $('#'+itemclass).addClass('active');    
        }else{
            $('.create_course_content > div').eq(n).addClass('active');
            $('.course-create-help > ul').eq(n).addClass('active');
        }
        if($('#'+itemclass+'_help').length){
            $('#'+itemclass+'_help').addClass('active');    
            $('#'+itemclass+'_help li:first-child').addClass('active');    
        }
    }
});    

// Uploading files
var media_uploader;
jQuery('.upload_image_button').on('click', function( event ){
  
    var button = jQuery( this );
    if ( media_uploader ) {
      media_uploader.open();
      return;
    }
    // Create the media uploader.
    media_uploader = wp.media.frames.media_uploader = wp.media({
        title: button.data( 'uploader-title' ),
        // Tell the modal to show only images.
        library: {
            type: 'image',
            query: false
        },
        button: {
            text: button.data( 'uploader-button-text' ),
        },
        multiple: button.data( 'uploader-allow-multiple' )
    });

    // Create a callback when the uploader is called
    media_uploader.on( 'select', function() {
        var selection = media_uploader.state().get('selection'),
            input_name = button.data( 'input-name' );
            selection.map( function( attachment ) {
            attachment = attachment.toJSON();
            console.log(attachment);
            var url_image='';
            if( attachment.sizes){
                if(   attachment.sizes.thumbnail !== undefined  ) url_image=attachment.sizes.thumbnail.url; 
                else if( attachment.sizes.medium !== undefined ) url_image=attachment.sizes.medium.url;
                else url_image=attachment.sizes.full.url;
            }
            
            button.html('<img src="'+url_image+'" class="submission_thumb thumbnail" /><input id="'+input_name+'" class="post_field" data-type="featured_image" data-id="'+input_name+'" name="'+input_name+'" type="hidden" value="'+attachment.id+'" />');
         });

    });
    // Open the uploader
    media_uploader.open();
  });

var media_uploader1;
jQuery('.upload_badge_button').on('click', function( event ){
    var button = jQuery( this );
    if ( media_uploader1 ) {
      media_uploader1.open();
      return;
    }
    // Create the media uploader.
    media_uploader1 = wp.media.frames.media_uploader = wp.media({
        title: button.data( 'uploader-title' ),
        // Tell the modal to show only images.
        library: {
            type: 'image',
            query: false
        },
        button: {
            text: button.data( 'uploader-button-text' ),
        },
        multiple: button.data( 'uploader-allow-multiple' )
    });

    // Create a callback when the uploader is called
    media_uploader1.on( 'select', function() {
        var selection = media_uploader1.state().get('selection'),
            input_name = button.data( 'input-name' );
            selection.map( function( attachment ) {
            attachment = attachment.toJSON();
            var url_image='';
            if( attachment.sizes){
                if(   attachment.sizes.thumbnail !== undefined  ) url_image=attachment.sizes.thumbnail.url; 
                else if( attachment.sizes.medium !== undefined ) url_image=attachment.sizes.medium.url;
                else url_image=attachment.sizes.full.url;
            }
            button.html('<img src="'+url_image+'" class="submission_thumb thumbnail" /><input id="'+input_name+'" name="'+input_name+'" type="hidden" value="'+attachment.id+'" />');
         });

    });
    // Open the uploader
    media_uploader1.open();
  });
  $('#course-title,#new_course_category').click(function(){
    var defaulttext = $(this).attr('data-default');
    var $cthis = $(this);
    if($cthis.html() == defaulttext){
        $cthis.html('');
         $('html').one('click',function() {
            if($cthis.html().length < 1){
                $cthis.html(defaulttext);
            }
          });
          event.stopPropagation();
    }
  });
  $('#create_course_action').click(function(event){
        
        
        var coursetitle=$('#course-title').text();
        var coursecat=$('#course-category-select').val();
        var newcoursecat=$('#new_course_category').text();
        var featuredimage=$('#course-image').val();
        var course_desc=$('#course_short_description').text();
        var courselinkage = '';
        var newcourselinkage = '';

        if(coursetitle == '' || /ENTER A COURSE TITLE/i.test(coursetitle)){
            alert(wplms_front_end_messages.course_title);
            return;
        }
        if($('#course-linkage-select').length){
          courselinkage = $('#course-linkage-select').val();
        }

        if($('#new_course_linkage').length){
          newcourselinkage = $('#new_course_linkage').text();
        }

        var $this = $(this);
        var defaulttxt = $this.html();
        $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
        $this.addClass('disabled');
        $.confirm({
          text: wplms_front_end_messages.create_course_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'create_course', 
                            security: $('#security').val(),
                            title: coursetitle,
                            category: coursecat,
                            newcategory: newcoursecat,
                            thumbnail: featuredimage,
                            description : course_desc,
                            courselinkage:courselinkage,
                            newcourselinkage:newcourselinkage
                          },
                    cache: false,
                    success: function (html) {
                        $this.find('i').remove();
                        if($.isNumeric(html)){
                            var active=$('#course_creation_tabs li.active');
                            active.removeClass('active');
                            $('#create_course').removeClass('active');
                            $('#create_course_help').removeClass('active');
                            active.addClass('done');
                            $('#course_creation_tabs li.done').next().addClass('active');    
                            $('#course_settings').addClass('active');
                            $('#course_settings_help').addClass('active');
                            $('#security').after('<input type="hidden" id="course_id" value="'+html+'" />');
                            $('#course_creation_tabs ul').addClass('islive');
                        }else{
                            $this.html(html);
                            setTimeout(function(){$this.html(defaulttxt);}, 2000);
                        }
                        $this.removeClass('disabled');
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
              $this.removeClass('disabled');
          },
          confirmButton: wplms_front_end_messages.create_course_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
      });
  });

  $('#save_course_action').click(function(event){
        
        var ID=$('#course_id').val();
        var coursetitle=$('#course-title').text();
        var coursecat=$('#course-category-select').val();
        var newcoursecat=$('#new_course_category').text();
        var featuredimage=$('#course-image').val();
        var course_desc=$('#course_short_description').text();
        var status=$('#vibe_course_status:checked').val();

        var courselinkage = '';
        var newcourselinkage = '';

        if($('#course-linkage-select').length){
          courselinkage = $('#course-linkage-select').val();
        }

        if($('#new_course_linkage').length){
          newcourselinkage = $('#new_course_linkage').text();
        }

        var $this = $(this);
        var defaulttxt = $this.html();
        $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
        $this.addClass('disabled');
        $.confirm({
          text: wplms_front_end_messages.save_course_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'save_course', 
                            security: $('#security').val(),
                            ID: ID,
                            status:status,
                            title: coursetitle,
                            category: coursecat,
                            newcategory: newcoursecat,
                            thumbnail: featuredimage,
                            description : course_desc,
                            courselinkage:courselinkage,
                            newcourselinkage:newcourselinkage
                          },
                    cache: false,
                    success: function (html) {
                        $this.find('i').remove();
                        $this.removeClass('disabled');
                        if($.isNumeric(html)){
                            if($this.hasClass('reload_page')){
                                location.reload();
                            }else{
                                var active=$('#course_creation_tabs li.active');
                                active.removeClass('active');
                                $('#create_course').removeClass('active');
                                $('#create_course_help').removeClass('active');
                                active.addClass('done');
                                $('#course_creation_tabs li.done').next().addClass('active');    
                                $('#course_settings').addClass('active');
                                $('#course_settings_help').addClass('active');
                            }
                        }else{
                            $this.html(html);
                            setTimeout(function(){$this.html(defaulttxt);}, 5000);
                        }
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
          },
          confirmButton: wplms_front_end_messages.save_course_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
      });
  });

    jQuery('#create_content').delegate('.switch-label','click',function(event){
      
        var parent=$(this).parent();
        var hidden=$(this).parent().parent().parent().next();
        var checkvalue=parent.find('.switch-input:checked').val();
        
        if(checkvalue == 'H'){ // jQuery records the previous known value
            hidden.fadeIn(200);
        }else{
            hidden.fadeOut(200);
        }
    });
    jQuery('body').delegate('.switch-subscription','click',function(event){
      
        var parent=$(this).parent();
        var hidden=$('.product_duration');
        var checkvalue=parent.find('.switch-input:checked').val();    
        if(checkvalue == 'S'){ // jQuery records the previous known value
            hidden.fadeIn(200);
        }else{
            hidden.fadeOut(200);
        }
    });

  $('body').delegate('#save_course_settings','click',function(event){
    

    var course_id=$('#course_id').val();
    var vibe_course_auto_eval=$('.vibe_course_auto_eval:checked').val();
    var vibe_pre_course = $('#vibe_pre_course').val();
    var vibe_course_drip = $('.vibe_course_drip:checked').val();
    var vibe_course_drip_duration=$('#vibe_course_drip_duration').val();
    var vibe_certificate = $('.vibe_course_certificate:checked').val();
    var vibe_course_passing_percentage = $('#vibe_course_passing_percentage').val();
    var vibe_certificate_template = $('#vibe_certificate_template').val();
    var vibe_course_badge_percentage = $('#vibe_course_badge_percentage').val();
    var vibe_badge = $('.vibe_badge:checked').val(); // Checks if bade is active or not
    var vibe_course_badge = $('#vibe_course_badge').val();
    var vibe_course_badge_title = $('#vibe_course_badge_title').val();
    var vibe_max_students = $('#vibe_max_students').val();
    var vibe_start_date = $('#vibe_start_date').val();
    var vibe_course_retakes = $('#vibe_course_retakes').val();
    var vibe_group = $('#vibe_group').val();
    var vibe_forum = $('#vibe_forum').val();
    var vibe_duration=$('#vibe_duration').val();
    var vibe_course_instructions = $('#vibe_course_instructions').html();
    var vibe_course_message = $('#vibe_course_message').html();
    var level = 0;
    if($('#course-level-select').length){
        level = $('#course-level-select').val();
    }
    var $this = $(this);
    var defaulttxt = $this.html();

    $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
    $this.addClass('disabled');
    $.confirm({
      text: wplms_front_end_messages.save_course_confirm,
      confirm: function() {
         $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'save_course_settings', 
                        security: $('#security').val(),
                        course_id: course_id,
                        vibe_course_auto_eval: vibe_course_auto_eval,
                        vibe_pre_course: vibe_pre_course,
                        vibe_course_drip : vibe_course_drip,
                        vibe_course_drip_duration : vibe_course_drip_duration,
                        vibe_duration:vibe_duration,
                        vibe_certificate : vibe_certificate,
                        vibe_course_passing_percentage : vibe_course_passing_percentage,
                        vibe_certificate_template : vibe_certificate_template,
                        vibe_badge : vibe_badge,
                        vibe_course_badge_title:vibe_course_badge_title,
                        vibe_course_badge_percentage : vibe_course_badge_percentage,
                        vibe_course_badge : vibe_course_badge,
                        vibe_max_students:vibe_max_students,
                        vibe_start_date:vibe_start_date,
                        vibe_course_retakes:vibe_course_retakes,
                        vibe_group : vibe_group,
                        vibe_forum : vibe_forum,
                        vibe_course_instructions : vibe_course_instructions,
                        vibe_course_message:vibe_course_message,
                        level:level
                      },
                cache: false,
                success: function (html) {
                    $this.find('i').remove();
                    $this.removeClass('disabled');
                    if($.isNumeric(html)){
                        var active=$('#course_creation_tabs li.active');
                        active.removeClass('active');
                        $('#course_settings').removeClass('active');
                        $('#course_settings_help').removeClass('active');
                        active.addClass('done');
                        $('#course_creation_tabs li.done').next().addClass('active');    
                        $('#course_curriculum').addClass('active');
                        $('#course_curriculum_help').addClass('active');
                    }else{
                        console.log(html);
                        $this.html(html);
                        setTimeout(function(){$this.html(defaulttxt);}, 2000);
                    }
                }
        });
      },
      cancel: function() {
          $this.find('i').remove();
      },
      confirmButton: wplms_front_end_messages.save_course_confirm_button,
      cancelButton: vibe_course_module_strings.cancel
      });
    });

    $('ul.curriculum').sortable({
          revert: true,
          cursor: 'move',
          refreshPositions: true, 
          opacity: 0.6,
          scroll:true,
          containment: 'parent',
          placeholder: 'placeholder',
          tolerance: 'pointer',
    });//.disableSelection();

    

    $('body').delegate('.curriculum select.chosencpt','change',function(event){
        var href;
        if($(this).val() == 'add_new'){
            $(this).parent().find('.new_unit_actions').fadeIn(200);
            $(this).parent().find('.new_quiz_actions').fadeIn(200);
            $(this).parent().find('.unit_actions').fadeOut(200);
            $(this).parent().find('.quiz_actions').fadeOut(200);

            $('#save_course_curriculum').addClass('disabled');
            $('.new_unit_title,.new_quiz_title').focus();
        }else{
            $(this).parent().find('.new_unit_actions').fadeOut(200);
            $(this).parent().find('.new_quiz_actions').fadeOut(200);
            $(this).parent().find('.unit_actions').fadeIn(200);
            $(this).parent().find('.quiz_actions').fadeIn(200);

            href= $(this).find('option:selected').attr('data-link')+'edit';
            if($(this).parent().hasClass('new_unit')){
                $(this).parent().find('.edit_unit').attr('href',href);
            }else{
                $(this).parent().find('.edit_quiz').attr('href',href);
            }

            $('#save_course_curriculum').removeClass('disabled');
        }
    });

    $('body').delegate('.new_unit_actions .publish','click',function(event){
        
        var course_id=$('#course_id').val();
        var $this = $(this);
        var title = $this.closest('.new_unit_actions').find('.new_unit_title').val();
   
        $this.prepend('<i class="icon-sun-stroke animated spin"></i>');

        $.confirm({
          text: wplms_front_end_messages.create_unit_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'create_unit', 
                            security: $('#security').val(),
                            course_id: course_id,
                            unit_title: title
                          },
                    cache: false,
                    success: function (html) {
                        $this.closest('.new_unit').html(html);
                        $('#save_course_curriculum').removeClass('disabled');
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
              $('#save_course_curriculum').removeClass('disabled');
          },
          confirmButton: wplms_front_end_messages.create_unit_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });
    });

    

    $('body').delegate('.new_quiz_actions .publish','click',function(event){
        

        var course_id=$('#course_id').val();
        var $this = $(this);
        var title = $this.closest('.new_quiz_actions').find('.new_quiz_title').val();

        $this.prepend('<i class="icon-sun-stroke animated spin"></i>');

        $.confirm({
          text: wplms_front_end_messages.create_quiz_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'create_quiz', 
                            security: $('#security').val(),
                            course_id: course_id,
                            quiz_title: title
                          },
                    cache: false,
                    success: function (html) {
                        $this.closest('.new_quiz').html(html);
                        $('#save_course_curriculum').removeClass('disabled');
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
              $('#save_course_curriculum').removeClass('disabled');
          },
          confirmButton: wplms_front_end_messages.create_quiz_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });
    });

    $('body').delegate('.new_q .publish','click',function(event){
        
        var $this = $(this);
        var title = $this.closest('.new_q').find('.question_title').val();
        var quiz_id = $('.save_quiz_settings').attr('data-quiz');
        $this.prepend('<i class="icon-sun-stroke animated spin"></i>');

        $.confirm({
          text: wplms_front_end_messages.create_question_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'create_question', 
                            security: $('#security').val(),
                            title: title,
                            quiz_id:quiz_id
                          },
                    cache: false,
                    success: function (html) {
                        $this.closest('.new_question').html(html);
                        $this.closest('.new_question').removeClass('new_question');
                        $('.save_quiz_settings').removeClass('disabled');
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
          },
          confirmButton: wplms_front_end_messages.create_question_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });
    });

    $('body').delegate('.curriculum .dropdown-menu .delete','click',function(event){
        event.preventDefault();
        var $this = $(this);
        var course_id=$('#course_id').val();
        var li = $(this).parent().parent().parent().parent();
        var id = li.find('h3.title').attr('data-id');
        console.log(id);
        $.confirm({
          text: wplms_front_end_messages.delete_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'delete_curriculum', 
                            security: $('#security').val(),
                            course_id: course_id,
                            id: id
                          },
                    cache: false,
                    success: function (html) {
                        $this.find('i').remove();
                        if($.isNumeric(html)){
                            li.remove();
                        }else{
                            alert(html);
                        }
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
          },
          confirmButton: wplms_front_end_messages.delete_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });
    });
    $('body').delegate('.dropdown-menu .remove','click',function(event){
        var li = $(this).parent().parent().parent().parent();
        li.remove();
        $('#save_course_curriculum').removeClass('disabled');
    });
    $('body').delegate('.dropdown-menu .remove_new','click',function(event){
        var li = $(this).parent().parent().parent().parent().parent().parent();
        li.remove();
        $('#save_course_curriculum').removeClass('disabled');
    });
    $('body').delegate('#save_course_curriculum','click',function(event){
        
        var course_id=$('#course_id').val();
        var $this = $(this);
        var defaulttxt = $this.html();
        var curriculum = [];

        $('ul.curriculum li').each(function() {
            $(this).find('h3').each(function(){
                if($(this).hasClass('title')){
                    var data = { 
                                   id: $(this).attr('data-id')
                               };
                }else{
                    var data = { 
                                   id: $(this).text()
                               };
                }
                curriculum.push(data);
            });
            $(this).find('select').each(function(){
                var data = { 
                               id: $(this).val()
                           };
                curriculum.push(data);           
            });   
            $(this).find('input.section').each(function(){
                var data = { 
                               id: $(this).val()
                           };
                curriculum.push(data);           
            }); 
        });

        $this.prepend('<i class="icon-sun-stroke animated spin"></i>');

        $.confirm({
          text: wplms_front_end_messages.save_course_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'save_curriculum', 
                            security: $('#security').val(),
                            course_id: course_id,
                            curriculum: JSON.stringify(curriculum)
                          },
                    cache: false,
                    success: function (html) {
                        $this.find('i').remove();
                        if($.isNumeric(html)){
                            var active=$('#course_creation_tabs li.active');
                            active.removeClass('active');
                            $('#course_curriculum').removeClass('active');
                            $('#course_curriculum_help').removeClass('active');
                            active.addClass('done');
                            $('#course_creation_tabs li.done').next().addClass('active');    
                            $('#course_pricing').addClass('active');
                            $('#course_pricing_help').addClass('active');
                        }else{
                            $this.html(html);
                            setTimeout(function(){$this.html(defaulttxt);}, 2000);
                        }
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
          },
          confirmButton: wplms_front_end_messages.save_course_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });

    });
    $('body').delegate('#vibe_product ','change',function(event){
        if($(this).val() == 'add_new'){
            $('.new_product').fadeIn(200);
        }else{
            $('.new_product').fadeOut(200);
        }
    });
    $('body').delegate('.vibe_course_free ','click',function(event){
        var val =$('.vibe_course_free:checked').val();
        if(val == 'S'){
            $('#course_pricing > ul > li.course_product').fadeOut(200);
            $('#course_pricing > ul > li.course_membership').fadeOut(200);
            $('#course_pricing > ul > li.new_product').fadeOut(200);
        }else{
            $('#course_pricing > ul > li.course_product').fadeIn(200);
            $('#course_pricing > ul > li.course_membership').fadeIn(200);
        }
    });
    $('body').delegate('#save_pricing ','click',function(event){
        
        var course_id=$('#course_id').val();

        var course_pricing={};

        course_pricing['vibe_course_free'] = $('.vibe_course_free:checked').val();
        if($('#vibe_product').length){
            course_pricing['vibe_product']=$('#vibe_product').val();    
            course_pricing['vibe_subscription']=$('.vibe_subscription:checked').val();
            course_pricing['vibe_product_price']=$('#product_price').val();
            course_pricing['vibe_duration']=$('#product_duration').val();
        }
        if($('#vibe_pmpro_membership').length)
            course_pricing['vibe_pmpro_membership']=$('#vibe_pmpro_membership').val();

        if($('#vibe_mycred_points').length){
            course_pricing['vibe_mycred_points']=$('#vibe_mycred_points').val();
            course_pricing['vibe_mycred_subscription']=$('.vibe_mycred_subscription:checked').val();
            course_pricing['vibe_mycred_duration']=$('#vibe_mycred_duration').val();
        }

        if($('.vibe_coming_soon').length){
            course_pricing['vibe_coming_soon']=$('.vibe_coming_soon:checked').val();
        }

        var extras = [];
        $('.vibe_extras').each(function() {
            extras.push({ 
                           element:$(this).attr('id'),
                           value: $(this).val()
                       });
        });

        var $this = $(this);
        var defaulttxt = $this.html();
        $.confirm({
          text: wplms_front_end_messages.save_course_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'save_pricing', 
                            security: $('#security').val(),
                            course_id: course_id,
                            pricing:JSON.stringify(course_pricing),
                            extras:JSON.stringify(extras)
                          },
                    cache: false,
                    success: function (html) {
                        $this.find('i').remove();
                        console.log(html);
                        if($.isNumeric(html)){
                            var active=$('#course_creation_tabs li.active');
                            active.removeClass('active');
                            $('#course_pricing').removeClass('active');
                            $('#course_pricing_help').removeClass('active');
                            active.addClass('done');
                            $('#course_creation_tabs li.done').next().addClass('active');    
                            $('#course_live').addClass('active');
                            $('#course_live_help').addClass('active');
                        }else{
                            $this.html(html);
                            setTimeout(function(){$this.html(defaulttxt);}, 2000);
                        }
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
          },
          confirmButton: wplms_front_end_messages.save_course_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });
    });

    $('body').delegate('#publish_course','click',function(event){
        

        var course_id=$('#course_id').val();
        var $this = $(this);
        var defaulttxt = $this.html();
        $this.addClass('disable');
        $.confirm({
          text: wplms_front_end_messages.save_course_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'publish_course', 
                            security: $('#security').val(),
                            course_id: course_id
                          },
                    cache: false,
                    success: function (html) {
                        $this.find('i').remove();
                        $this.after(html);
                        $this.fadeOut(200);
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
          },
          confirmButton: wplms_front_end_messages.save_course_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });
    });
    
    $('body').delegate('#offline_course','click',function(event){
        

        var course_id=$('#course_id').val();
        var $this = $(this);
        var defaulttxt = $this.html();
        $this.addClass('disable');
        $.confirm({
          text: wplms_front_end_messages.course_offline,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'offline_course', 
                            security: $('#security').val(),
                            course_id: course_id
                          },
                    cache: false,
                    success: function (html) {
                        $this.find('i').remove();
                        $this.after(html);
                        $this.fadeOut(200);
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
          },
          confirmButton: wplms_front_end_messages.save_course_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });
    });
    
    $('body').delegate('#delete_course','click',function(event){
        

        var course_id=$('#course_id').val();
        var $this = $(this);
        var defaulttxt = $this.html();
        $this.addClass('disable');

        var fields = [];
        $('.delete_field:checked').each(function() {
            fields.push({ 
                           post_type:$(this).attr('data-posttype'),
                           post_meta:$(this).attr('data-meta')
                       });
        });

        $.confirm({
          text: wplms_front_end_messages.delete_course_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'delete_course', 
                            security: $('#security').val(),
                            course_id: course_id,
                            fields: JSON.stringify(fields)
                          },
                    cache: false,
                    success: function (html) {
                        $this.find('i').remove();
                        $this.after(html);
                        $this.fadeOut(200);
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
          },
          confirmButton: wplms_front_end_messages.delete_button,
          cancelButton: vibe_course_module_strings.cancel
          });
    });

  $('body').delegate('#save_unit_settings','click',function(event){
      
        var $this = $(this);
        var unit_id=$this.attr('data-id');
        var course_id=$this.attr('data-course');
        var defaulttxt = $this.html();
        var vibe_type = $('#vibe_type').val();
        var vibe_free = $('.vibe_free:checked').val();
        var vibe_duration = $('#vibe_duration').val();

        var assignment_ids = [];
        if($('#assignments_list').length){
            $('#assignments_list li').each(function(){
                var attr = $(this).attr('data-id');
                if (typeof attr !== typeof undefined && attr !== false) {
                    assignment_ids.push({id:attr});
                }
            });
         vibe_assignment = $('#vibe_assignment').val();
        }

        var extras = [];
        $('.vibe_extras').each(function() {
            extras.push({ 
                           element:$(this).attr('id'),
                           value: $(this).val()
                       });
        });

       var vibe_forum = '';
       if($('#vibe_forum').length)
        vibe_forum= $('#vibe_forum').val();

        $this.addClass('disabled');
        $.confirm({
          text: wplms_front_end_messages.save_unit_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'save_unit_settings', 
                            security: $('#security').val(),
                            course_id: course_id,
                            unit_id: unit_id,
                            vibe_type:vibe_type,
                            vibe_free:vibe_free,
                            vibe_duration:vibe_duration,
                            vibe_assignment:JSON.stringify(assignment_ids),
                            vibe_forum:vibe_forum,
                            extras:JSON.stringify(extras)
                          },
                    cache: false,
                    success: function (html) {
                        $this.find('i').remove();
                        $this.removeClass('disabled');
                        $this.html(html);
                        setTimeout(function(){$this.html(defaulttxt);}, 2000);
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
          },
          confirmButton: wplms_front_end_messages.save_unit_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });
  });
  
  $('#questions').sortable({
          revert: true,
          cursor: 'move',
          refreshPositions: true, 
          opacity: 0.6,
          scroll:true,
          containment: 'parent',
          placeholder: 'placeholder',
          tolerance: 'pointer',
        });//.disableSelection();

  $('body').delegate('#add_question','click',function(event){
      
      var clone=$('#hidden > li').clone();
      $('#questions').append(clone);
        $('#questions').sortable({
          revert: true,
          cursor: 'move',
          refreshPositions: true, 
          opacity: 0.6,
          scroll:true,
          containment: 'parent',
          placeholder: 'placeholder',
          tolerance: 'pointer',
        });//.disableSelection();
       //clone.find('select.chosen').chosen();
       $('.save_quiz_settings').addClass('disabled');
  });

  $('body').delegate('.question','change',function(){
      var value = $(this).val();
      if(value === 'add_new'){
        $(this).parent().find('.new_q').fadeIn(300);
      }else{
        $('.save_quiz_settings').removeClass('disabled');
      }

  });
  $('body').delegate('.save_quiz_settings','click',function(event){
        
        var $this = $(this);
        $this.addClass('disabled');
        var quiz_id=$this.attr('data-quiz');
        var defaulttxt = $this.html();
        var vibe_subtitle = $('#vibe_subtitle').html();
        var vibe_quiz_course = $('#vibe_quiz_course').val();
        var vibe_duration = $('#vibe_duration').val();
        var vibe_quiz_auto_evaluate = $('.vibe_quiz_auto_evaluate:checked').val();
        var vibe_quiz_dynamic = $('.vibe_quiz_dynamic:checked').val();
        var vibe_quiz_tags = $('#vibe_quiz_tags').val();
        var vibe_quiz_number_questions = $('#vibe_quiz_number_questions').val();
        var vibe_quiz_marks_per_question = $('#vibe_quiz_marks_per_question').val();
        var vibe_quiz_retakes=$('#vibe_quiz_retakes').val();
        var vibe_quiz_random = $('.vibe_quiz_randome:checked').val();
        var vibe_quiz_message = $('#vibe_quiz_message').html();
        var extras = [];
        $('.vibe_extras').each(function() {
            extras.push({ 
                           element:$(this).attr('id'),
                           value: $(this).val()
                       });
        });
        var questions = [];
        var qid,qmarks;
        $('#questions > li').each(function() {
              qid=$(this).find('.question').val();
              qmarks=$(this).find('.question_marks').val();
              var data = { 
                           ques: qid,
                           marks: qmarks
                       };
          questions.push(data);
        });
        
        $this.prepend('<i class="icon-sun-stroke animated spin"></i>');

        $.confirm({
          text: wplms_front_end_messages.save_quiz_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'save_quiz_settings', 
                            security: $('#security').val(),
                            quiz_id: quiz_id,
                            vibe_subtitle:vibe_subtitle,
                            vibe_quiz_course:vibe_quiz_course,
                            vibe_duration:vibe_duration,
                            vibe_quiz_auto_evaluate:vibe_quiz_auto_evaluate, 
                            vibe_quiz_dynamic:vibe_quiz_dynamic,
                            vibe_quiz_tags:vibe_quiz_tags,
                            vibe_quiz_number_questions:vibe_quiz_number_questions,
                            vibe_quiz_marks_per_question:vibe_quiz_marks_per_question,
                            vibe_quiz_retakes:vibe_quiz_retakes, 
                            vibe_quiz_random:vibe_quiz_random, 
                            vibe_quiz_message:vibe_quiz_message,   
                            extras:    JSON.stringify(extras),      
                            questions: JSON.stringify(questions)
                          },
                    cache: false,
                    success: function (html) {
                        $this.find('i').remove();
                        $this.html(html);
                        setTimeout(function(){$this.html(defaulttxt);$this.removeClass('disabled');location.reload();}, 2000);
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
              $this.removeClass('disabled');
          },
          confirmButton: wplms_front_end_messages.save_quiz_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });

    });
    
    $('body').delegate('#questions .dropdown-menu .delete','click',function(event){
        
        var $this = $(this);
        var id = $(this).parent().parent().parent().parent().find('.question').val();
        var li = $(this).parent().parent().parent().parent();
        $.confirm({
          text: wplms_front_end_messages.delete_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'delete_question', 
                            security: $('#security').val(),
                            id: id
                          },
                    cache: false,
                    success: function (html) {
                        $this.find('i').remove();
                        if($.isNumeric(html)){
                            li.remove();
                        }else{
                            alert(html);
                        }
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
          },
          confirmButton: wplms_front_end_messages.delete_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });
    });

  $('#vibe_question_type').change(function(event){
    
      var value = $(this).val();
      if(value === 'smalltext' || value === 'largetext'){
        $('li.optionli').fadeOut(200);
      }else{
        $('li.optionli').fadeIn(200);
        var $this=$('.vibe_question_options');
        $this.removeClass();
        $this.addClass('vibe_question_options');
        $this.addClass(value);
      }
  });

    $('.vibe_question_options').sortable({
      revert: true,
      cursor: 'move',
      refreshPositions: true, 
      opacity: 0.6,
      scroll:true,
      containment: 'parent',
      placeholder: 'placeholder',
      tolerance: 'pointer',
      update: function(event, ui) {
        $('.vibe_question_options').trigger('update');
      }
    });//.disableSelection();

    $('#add_option').click(function(event){
        
        var clone = $('.hidden > li').clone();
        console.log(clone);
        $('.vibe_question_options').append(clone);
        $('.vibe_question_options').trigger('update');

    });

    $('.vibe_question_options').on('update',function(){
      var index=0;
        $(this).find('li').each(function(){
            index= $(this).index();
            $(this).find('span').text((index+1));
        });
    });
    $('body').delegate('.vibe_question_options li > span','click',function(event){
        var parent = $(this).parent();
        var index = parent.index();

        if($('.vibe_question_options').hasClass('single')){
          $('.vibe_question_options li').removeClass('selected');
          parent.addClass('selected');
          $('#vibe_question_answer').trigger('update');
        }
        if($('.vibe_question_options').hasClass('multiple')){
            if(parent.hasClass('selected')){
              parent.removeClass('selected');
            }else{
              parent.addClass('selected');
              $('#vibe_question_answer').trigger('update');
            }
        }
        if($('.vibe_question_options').hasClass('sort')){

        }


    });
    $('#vibe_question_answer').on('update',function(){
        var value='';
        value = $('.vibe_question_options > li.selected').map(function() { 
              return ($(this).index()+1); 
          }).get().join(',');
        $('#vibe_question_answer').attr('value',value);
    });
    $('body').delegate('.vibe_quiz_dynamic','click',function(){
        var value = $('.vibe_quiz_dynamic:checked').val();
          if(value === 'S'){
            $('.dynamic').fadeIn(200);
            $('#quiz_question_controls').fadeOut(200);
          }else{
            $('.dynamic').fadeOut(200);
            $('#quiz_question_controls').fadeIn(200);
          }
    });
    $('#save_question_settings').click(function(event){
        
        var $this = $(this);
        var defaulttxt = $this.html();
        var id = $('#question_id').val();
        var vibe_question_type = $('#vibe_question_type').val();
        var vibe_question_answer = $('#vibe_question_answer').val();
        var vibe_question_hint = $('#vibe_question_hint').val();
        var vibe_question_explaination = $('#vibe_question_explaination').html();

         var vibe_question_options = [];

         $this.addClass('disabled');
        $('.vibe_question_options > li').each(function() {
              var option = $(this).find('.option').val();
              var data = { 
                           option: option
                       };
          vibe_question_options.push(data);
        });
        $.confirm({
          text: wplms_front_end_messages.save_settings,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'save_question', 
                            security: $('#security').val(),
                            id: id,
                            vibe_question_type:vibe_question_type,
                            vibe_question_options:JSON.stringify(vibe_question_options),
                            vibe_question_answer:vibe_question_answer,
                            vibe_question_hint:vibe_question_hint,
                            vibe_question_explaination:vibe_question_explaination
                          },
                    cache: false,
                    success: function (html) {
                        $this.find('i').remove();
                        $this.removeClass('disabled');
                        $this.html(html);
                        setTimeout(function(){$this.html(defaulttxt);}, 2000);
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
              $this.removeClass('disabled');
          },
          confirmButton: wplms_front_end_messages.save_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });
    });
    
    $('body').delegate('.rem','click',function(event){
        
        $(this).parent().remove();
        $('#save_course_curriculum').removeClass('disabled');
        $('.save_quiz_settings').removeClass('disabled');
        $('#save_unit_settings').removeClass('disabled');
    });

    $('body').delegate('#vibe_assignment','change',function(){
        var value = $(this).val();
        var href;
        if(value === 'add_new'){
          $('#save_unit_settings').addClass('disabled');
          $('#assignment_link').addClass('hide');
        }else{
          href= $('#vibe_assignment > option:selected').attr('data-link')+'?edit';
          $('#assignment_link').attr('href',href);
          $('#assignment_link').removeClass('hide');
        }

  });

    $('.add_new_assignment').click(function(event){
        $(this).parent().next().show(200);
    });

   $('body').delegate('.dropdown-menu .new_remove','click',function(event){
      
      var li = $(this).parent().parent().parent().parent();
      li.fadeOut(200);
      $('#save_unit_settings').removeClass('disabled');
  });
  $('.add_existing_assignment').click(function(){
        var cloned = $('#assignments_list li.hide').clone();
        $(cloned).removeClass('hide');
        //cloned.find('select').chosen();
        $('#assignments_list').append(cloned);
        $('#assignments_list select').on('change',function(){ 
            $(this).parent().parent().attr('data-id',$(this).val());
        });
  });
  $('body').delegate('.new_assignment_actions .publish','click',function(event){
        
        var unit_id=$('#save_unit_settings').attr('data-id');
        var $this = $(this);
        var title = $this.closest('.new_assignment_actions').find('.new_assignment_title').val();
   
        $this.prepend('<i class="icon-sun-stroke animated spin"></i>');

        $.confirm({
          text: wplms_front_end_messages.create_assignment_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'create_assignment', 
                            security: $('#security').val(),
                            unit_id: unit_id,
                            title: title,
                            dataType: 'json'
                          },
                    cache: false,
                    success: function (response) {
                        response = JSON.parse(response);
                        var cloned = $('#assignments_list li.hide').clone();
                        $(cloned).removeClass('hide');
                        $(cloned).attr('data-id',response.id);
                        $(cloned).find('strong').html('<i class="icon-text-document"></i>'+response.title);
                        $(cloned).find('.edit_unit').attr('href',response.link+'/edit');
                        $(cloned).find('.preview_unit').attr('href',response.link);
                        console.log(cloned);
                        $('#assignments_list').append(cloned);
                        $('#save_unit_settings').removeClass('disabled');
                        $('#assignment_link').removeClass('hide');
                        $('.new_assignment_actions > li:last-child').fadeOut(200);
                    }
            });
          },
          cancel: function() {
              $this.find('i').remove();
              $('#save_course_curriculum').removeClass('disabled');
          },
          confirmButton: wplms_front_end_messages.create_assignment_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });
    });
  $('.vibe_assignment_evaluation').click(function(){
      var value = $('.vibe_assignment_evaluation:checked').val();
      $('#assignment_course').removeClass('hide');
      if(value === 'S'){
        $('#assignment_course').fadeIn(200);
      }else{
        $('#assignment_course').fadeOut(200);
      }

  });

  $('#vibe_assignment_submission_type').change(function(){
      var value = $(this).val();
      if(value === 'textarea'){
        $('#attachment_type').fadeOut(200);
      }else{
        $('#attachment_type').fadeIn(200);
      }
  });

  $('#save_assignment_settings').click(function(event){
        
        var $this = $(this);
        var defaulttxt = $this.html();
        var assignment_id = $('#assignment_id').val();
        var vibe_subtitle = $('#vibe_subtitle').text();
        var vibe_assignment_marks = $('#vibe_assignment_marks').val();
        var vibe_assignment_duration = $('#vibe_assignment_duration').val();
        var vibe_assignment_evaluation= $('.vibe_assignment_evaluation:checked').val();
        var vibe_assignment_course= $('#vibe_assignment_course').val();
        var vibe_assignment_submission_type= $('#vibe_assignment_submission_type').val();
        var vibe_attachment_size = $('#vibe_attachment_size').val();
        var vibe_attachment_type= [];
        $('#vibe_attachment_type option:selected').each(function(i,selected){
            vibe_attachment_type[i] = $(selected).val();
        });

        $this.addClass('disabled');
        $.confirm({
          text: wplms_front_end_messages.save_settings,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'save_assignment_settings', 
                            security: $('#assignment_security').val(),
                            assignment_id: assignment_id,
                            vibe_subtitle:vibe_subtitle,
                            vibe_assignment_marks:vibe_assignment_marks,
                            vibe_assignment_duration:vibe_assignment_duration,
                            vibe_assignment_evaluation:vibe_assignment_evaluation,
                            vibe_assignment_course:vibe_assignment_course,
                            vibe_assignment_submission_type:vibe_assignment_submission_type,
                            vibe_attachment_type: JSON.stringify(vibe_attachment_type),
                            vibe_attachment_size:vibe_attachment_size
                          },
                    cache: false,
                    success: function (html) {
                        $this.find('i').remove();
                        $this.html(html);
                        setTimeout(function(){$this.html(defaulttxt);}, 2000);
                        $this.removeClass('disabled');
                    }
            });
          },
          cancel: function() {
              $this.removeClass('disabled');
          },
          confirmButton: wplms_front_end_messages.save_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });
    });
});

