jQuery(document).ready(function($){
    
    $('.wplms-taxonomy select').change(function(event){
        var new_tax = $(this).parent().parent().find('.wplms-new-taxonomy');
        if($(this).val() === 'new'){
            new_tax.addClass('animate cssanim fadeIn load');
        }else{
            new_tax.removeClass('animate cssanim fadeIn load');
        }
    });


    $('.select2').each(function(){
        if($(this).hasClass('select2-hidden-accessible'))
            return;
        
        if(!$(this).hasClass('selectcpt')){
            if($(this).is('[multiple]')){ 
                $(this).select2();
            }else{
                $(this).select2({allowClear: true});    
            }
        }
            
    });

    $('.chosen').select2({allowClear: true});
    
    $('.vibe_vibe_group h3>span,.vibe_vibe_forum h3>span').click(function(){
        $(this).parent().next().toggle(200);
    });
    $('.toggle_vibe_post_content').click(function(){
        $('.vibe_post_content').toggle(200);
    });
    $('.vibe_vibe_group .more').click(function(){
        $('.select_group_form,.new_group_form').hide();
        $(this).next().toggle(200);
    });
    $('.vibe_vibe_forum .more').click(function(){
        $('.select_forum_form,.new_forum_form').hide();
        $(this).next().toggle(200);
    });
    
    $('.vibe_vibe_product h3>span').click(function(){
        var pclass = $(this).attr('class');
        $('#edit_product,#change_product').hide(100);
        $('#'+pclass).toggle(200);
    });

    $('.vibe_vibe_product .more').click(function(){
        $('.select_product_form,.new_product_form').hide();
        $(this).next().toggle(200);
    });

    $('.clear_input').on('click',function(){
        var val = $(this).attr('data-id');
        if($('#'+val).length){
            $('#'+val).val('');
            $(this).next().html($(this).find('.hide').html());
            $('.course_components').trigger('active');
            $('.course_pricing').trigger('reactive');
        }
    });
    $('.course_components').on('active',function(){ 
        $('.vibe_vibe_group h3>span,.vibe_vibe_forum h3>span').unbind('click');
        $('.vibe_vibe_group h3>span,.vibe_vibe_forum h3>span').click(function(){
            $(this).parent().next().toggle(200);
        });

        $('.vibe_vibe_group .more').unbind('click');
        $('.vibe_vibe_group .more').click(function(){
            $('.select_group_form,.new_group_form').hide();
            $(this).next().toggle(200);
        });
        $('.vibe_vibe_forum .more').unbind('click');
        $('.vibe_vibe_forum .more').click(function(){
            $('.select_forum_form,.new_forum_form').hide();
            $(this).next().toggle(200);
        });
        $('.clear_input').on('click',function(){
            var val = $(this).attr('data-id');
            if($('#'+val).length){
                $('#'+val).val('');
                $(this).next().html($(this).find('.hide').html());
                $('.course_components').trigger('active');
            }
        });
    });
    $('.course_pricing').on('active',function(){ 
        $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'get_product', 
                        security: $('#security').val(),
                        course_id:$('#course_id').val(),
                      },
                cache: false,
                success: function (html) {
                    $('#edit_product').html(html);
                }
        });
        $('.vibe_vibe_product h3>span').unbind('click');
        $('.vibe_vibe_product h3>span').click(function(){
            var pclass = $(this).attr('class');
            $('#edit_product,#change_product').hide(100);
            $('#'+pclass).toggle(200);
        });
        $('.vibe_vibe_product .more').unbind('click');
        $('.vibe_vibe_product .more').click(function(){
            $('.select_product_form,.new_product_form').hide();
            $(this).next().toggle(200);
        });
        $('.clear_input').on('click',function(){
            var val = $(this).attr('data-id');
            if($('#'+val).length){
                $('#'+val).val('');
                $(this).next().html($(this).find('.hide').html());
                $('.course_pricing').trigger('reactive');
            }
        });
    });

    $('.course_pricing').on('reactive',function(){
         $('.vibe_vibe_product h3>span').unbind('click');
        $('.vibe_vibe_product h3>span').click(function(){ 
            var pclass = $(this).attr('class');
            $('#edit_product,#change_product').hide(100);
            $('#'+pclass).toggle(200);
        });
        $('.vibe_vibe_product .more').unbind('click');
        $('.vibe_vibe_product .more').click(function(){
            $('.select_product_form,.new_product_form').hide();
            $(this).next().toggle(200);
        });
    });
    $('#course_creation_tabs').on('increment',function(){
        var active = $(this).find('li.active');
        active.removeClass('active');
        active.removeClass('done');
        var id = active.attr('class');
        active.addClass('done');
        $('#'+id).removeClass('active');
        
        var nextid = active.next().attr('class');
        $('#'+nextid).addClass('active');
        $('#'+nextid).trigger('active');
        active.next().addClass('active');
        if(active.next().hasClass('hide_cc_element')){
             $('#course_creation_tabs').trigger('increment');
             return false;
        }
        $('body,html').animate({
            scrollTop: 0
          }, 1200);
        
        $('#'+nextid).find( '.wp-editor-area' ).each(function() {
            var id = jQuery( this ).attr( 'id' ),
                sel = '#wp-' + id + '-wrap',
                container = jQuery( sel ),
                editor = tinyMCE.get( id );
            if ( editor && container.hasClass( 'tmce-active' ) ) {
                editor.save();
            }
        });
    });

    $('select[data-type="duration"]').on('change',function(){
        var did = $(this).attr('data-id');
        var val = $(this).find('option:selected').text();
        $('span[data-connect="'+did+'"]').text(val);
    });

    $('input[data-id="post_title"]').on('keyup',function(){
        $('#create_course_button').removeClass('disabled');
        $('input[data-id="post_title"]').removeClass('error');
    });
    /* === Create Course Ajax === */
    $('#create_course_button').on('click',function(){

        var $this = $(this);
        var defaulttxt = $this.html();

        var $title = $('input[data-id="post_title"]').val();
        if($title.length < 1){
            $this.addClass('disabled');
            $('input[data-id="post_title"]').addClass('error');
        }
        if($this.hasClass('disabled') || $this.attr('id') == 'save_course_button')
            return;

        $this.addClass('disabled');

        $('body').trigger('modal_open');

        tinyMCE.triggerSave();

        $.confirm({
          text: wplms_front_end_messages.create_course_confirm,
          confirm: function() {
            var settings = [];

            $('#create_course').each(function() {
                $(this).find('.post_field').each(function(){
                    if($(this).is(':checkbox')){
                        $(this).is(':checked').each(function(){
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                        });
                    }
                    if($(this).is(':radio')){
                        var radio_class = $(this).attr('class');
                        $('.'+radio_class+':checked').each(function(){
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                        });
                    }
                    if($(this).is('select')){
                        var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                    }
                    if($(this).is('input')){
                        var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                    }
                    if($(this).is('textarea')){
                       var data = {id:$(this).attr('id'),type: $(this).attr('data-type'),value: $(this).val()};   
                    }
                    settings.push(data);
                });
            });
  
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'new_create_course', 
                            security: $('#security').val(),
                            settings:JSON.stringify(settings)    
                          },
                    cache: false,
                    success: function (html) {
                        if($.isNumeric(html)){
                            $('#course_id').val(html);
                            $('#create_course_button').attr('id','save_course_button').removeClass('disabled');
                            $('#course_creation_tabs>ul').addClass('islive');
                            $('#course_creation_tabs').trigger('increment');
                        }else{
                            $this.html(html);
                            setTimeout(function(){$this.html(defaulttxt);}, 5000);
                            $this.removeClass('disabled');
                        }
                    }
            });
           
          },
          cancel: function() {
              $this.removeClass('disabled');
          },
          confirmButton: wplms_front_end_messages.create_course_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
      });

    });
    /* === Save Course Ajax ===*/
    $(document).on('click','#save_course_button',function(){

        var $this = $(this);
        var defaulttxt = $this.html();
        $this.addClass('disabled');
        $('body').trigger('modal_open');
        tinyMCE.triggerSave();

        $.confirm({
          text: wplms_front_end_messages.save_course_confirm,
          confirm: function() {
            var settings = [];

            $('#create_course').each(function() {
                $(this).find('.post_field').each(function(){
                    if($(this).is(':checkbox')){
                        $(this).is(':checked').each(function(){
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                        });
                    }
                    if($(this).is(':radio')){
                        var radio_class = $(this).attr('class');
                        $('.'+radio_class+':checked').each(function(){
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                        });
                    }
                    if($(this).is('select')){
                        var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                    }
                    if($(this).is('input')){
                        var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                    }
                    if($(this).is('textarea')){
                        if($(this).hasClass('wp-editor-area')){
                            var data = {id:$(this).attr('id'),type: $(this).attr('name'),value: $(this).val()};  
                        }else{
                            var data = {id:$(this).attr('id'),type: $(this).attr('data-type'),value: $(this).val()};        
                        }
                    }
                    settings.push(data);
                });
            });
  
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'new_save_course', 
                            security: $('#security').val(),
                            course_id:$('#course_id').val(),
                            settings:JSON.stringify(settings)    
                          },
                    cache: false,
                    success: function (html) {
                   
                        $this.removeClass('disabled');
                        if($.isNumeric(html)){
                            $('#course_creation_tabs').trigger('increment');
                        }else{
                            $this.html(html);
                            setTimeout(function(){$this.html(defaulttxt);}, 5000);
                        }
                    }
            });
          },
          cancel: function() {
            console.log('checks');
              $this.removeClass('disabled');
          },
          confirmButton: wplms_front_end_messages.save_course_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
      });

    });
    
    /* === Save Course Settings Ajax ====*/
    $('#save_course_settings_button').on('click',function(){

        var $this = $(this);
        var defaulttxt = $this.html();
        $this.addClass('disabled');
        $('body').trigger('modal_open');
        var settings = [];

        $('#course_settings').find('.post_field').each(function() {
                
                if($(this).is(':checkbox:checked')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is(':radio:checked')){ 
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('select')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('input[type="text"]')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('input[type="hidden"]')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('input[type="number"]')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('textarea')){
                    if($(this).hasClass('wp-editor-area')){
                        tinyMCE.triggerSave();
                        var id = $(this).attr('id'); 
                        var data = {id:$(this).attr('id'),type: $(this).attr('name'),value: $(this).val()};      
                    }else{
                        var data = {id:$(this).attr('data-id'),type: $(this).attr('name'),value: $(this).val()};   
                    }  
                }
                settings.push(data);
        });

        $.confirm({
          text: wplms_front_end_messages.save_course_confirm,
          confirm: function() {
           
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'new_save_course_settings', 
                            security: $('#security').val(),
                            course_id:$('#course_id').val(),
                            settings:JSON.stringify(settings)    
                          },
                    cache: false,
                    success: function (html) {
                   
                        $this.removeClass('disabled');
                        if($.isNumeric(html)){
                            $('#course_creation_tabs').trigger('increment');
                        }else{
                            $this.html(html);
                            setTimeout(function(){$this.html(defaulttxt);}, 5000);
                        }
                    }
            });
          },
          cancel: function() {
              $this.removeClass('disabled');
          },
          post:true,
          confirmButton: wplms_front_end_messages.save_course_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
      });

    });
    /* === Save Course Components Ajax ====*/
    $('#save_course_components_button').on('click',function(){

        var $this = $(this);
        var defaulttxt = $this.html();
        $this.addClass('disabled');
        $('body').trigger('modal_open');
        var components = [];

        $('#course_components').find('.post_field').each(function() {
                
                if($(this).is(':checkbox:checked')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is(':radio:checked')){ 
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('select')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('input[type="text"]')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('input[type="number"]')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('input[type="hidden"]')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('textarea')){
                    if($(this).hasClass('wp-editor-area')){
                        tinyMCE.triggerSave();
                        var id = $(this).attr('id'); 
                        var data = {id:$(this).attr('id'),type: $(this).attr('name'),value: $(this).val()};      
                    }else{
                        var data = {id:$(this).attr('data-id'),type: $(this).attr('name'),value: $(this).val()};   
                    }  
                }
                components.push(data);
        });

        $.confirm({
          text: wplms_front_end_messages.save_course_confirm,
          confirm: function() {
           
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'new_save_course_components', 
                            security: $('#security').val(),
                            course_id:$('#course_id').val(),
                            settings:JSON.stringify(components)    
                          },
                    cache: false,
                    success: function (html) {
                   
                        $this.removeClass('disabled');
                        if($.isNumeric(html)){
                            $('#course_creation_tabs').trigger('increment');
                        }else{
                            $this.html(html);
                            setTimeout(function(){$this.html(defaulttxt);}, 5000);
                        }
                    }
            });
          },
          cancel: function() {
              $this.removeClass('disabled');
          },
          confirmButton: wplms_front_end_messages.save_course_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
      });

    });

    /*===== Curriculum ====*/
    $('.data_links .edit').on('click',function(){
        var $this = $(this);

        console.log(jQuery.active);
        if($this.hasClass('disabled'))
            return;
        
        $this.addClass('disabled');
        var defaulttxt = $this.html();
        $this.closest('div.active').css('opacity','0.6');
        $this.closest('div.active').append('<div id="ajaxloader"></div>');
        $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'get_element', 
                        security: $('#security').val(),
                        course_id:$('#course_id').val(),
                        element_id: $this.parent().parent().parent().find('.title').attr('data-id'),
                      },
                cache: false,
                success: function (html) {
                    var parent;
                    $('#ajaxloader').remove();
                    $this.removeClass('disabled');
                    $this.closest('div.active').css('opacity','1');
                    
                    if($('#course_curriculum').hasClass('active')){
                        parent = $('#course_curriculum');
                    }else if($('#events').hasClass('active')){
                         parent = $('#events');
                    }
                    $('.date_box').datepicker();

                   
                    parent.append(html);


                    var height = parent.find('.element_overlay').outerHeight()+60;

                    parent.css('height',height+'px');
                    parent.css('overflow-y','scroll');
                    parent.trigger('active');
                    $('.element_overlay .tip').tooltip();
                    $('.element_overlay .close-pop').click(function(){
                        $(this).parent().remove();
                    });
                    $('.add_cpt .more').click(function(event){
                        $('.select_existing_cpt,.new_cpt').hide();
                        $(this).next().toggle(200);
                    });
                    $('.accordion_trigger').on('click',function(){
                        $(this).parent().toggleClass('open');
                        $('.vibe_vibe_quiz_tags .select2').select2({allowClear: true});
                    });
                }
        });
    });

    $('.data_links .preview').on('click',function(){
        var $this = $(this);
        var defaulttxt = $this.html();
        $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'preview_element', 
                        security: $('#security').val(),
                        course_id:$('#course_id').val(),
                        element_id: $this.parent().parent().parent().find('.title').attr('data-id'),
                      },
                cache: false,
                success: function (html) {
                    var parent;
                    if($('#course_curriculum').hasClass('active')){
                        parent = $('#course_curriculum');
                    }else if($('#events').hasClass('active')){
                         parent = $('#events');
                    }
                    parent.append(html);

                    var height = parent.find('.element_overlay').outerHeight()+60;

                    parent.css('height',height+'px');
                    parent.css('overflow-y','scroll');
                    parent.trigger('active');


                    $('.element_overlay .close-pop').click(function(){
                        $(this).parent().remove();
                    });
                    $('.accordion_trigger').on('click',function(){
                        $(this).parent().toggleClass('open');
                    });
                    
                }
        });
    });

    $('.data_links .remove').on('click',function(){
        $(this).closest('.data_links').closest('li').remove();
    });

    $('.data_links .delete').on('click',function(){
        var $this = $(this);

        if($this.hasClass('disabled'))
            return;

        $this.addClass('disabled');
        $('body').trigger('modal_open');
        var post_id = $(this).closest('.data_links').parent().find('.title').attr('data-id');
        $.confirm({
              text: wplms_front_end_messages.delete_confirm,
              confirm: function() {
               
                 $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: { action: 'delete_element', 
                                security: $('#security').val(),
                                id:post_id,  
                              },
                        cache: false,
                        success: function (html) {
                            $this.removeClass('disabled');
                            if($.isNumeric(html)){
                                $this.closest('.data_links').parent('li').remove();
                            }
                        }
                });
              },
              cancel: function() {
                  $this.removeClass('disabled');
              },
              confirmButton: wplms_front_end_messages.delete_confirm_button,
              cancelButton: vibe_course_module_strings.cancel
          });
    });
    /* ===== END ==== */

    /* === Save Course Components Ajax ====*/
        $('#save_pricing_button').on('click',function(){

            var $this = $(this);
            var defaulttxt = $this.html();
            $this.addClass('disabled');
            $('body').trigger('modal_open');
            var pricing = [];

            $('#course_pricing').find('.post_field').each(function() {
                    if(!$(this).closest('.select_product_form').length && !$(this).closest('.new_product_form').length){

                        if($(this).is(':checkbox:checked')){
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                        }
                        if($(this).is(':radio:checked')){ 
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                        }
                        if($(this).is('select')){
                            if($(this).is("select[multiple]")){
                                var values = {};

                                $(this).find('option:selected').each(function(i,selected){
                                    values[i] = $(selected).val();
                                });
                                var data = {id:$(this).attr('data-id'),value: values};
                            }else{
                                var data = {id:$(this).attr('data-id'),value: $(this).val()};
                            }
                        }
                        if($(this).is('input[type="text"]')){
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                        }
                        if($(this).is('input[type="number"]')){
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                        }
                        if($(this).is('input[type="hidden"]')){
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                        }
                        if($(this).is('textarea')){
                            if($(this).hasClass('wp-editor-area')){
                                tinyMCE.triggerSave();
                                var id = $(this).attr('id'); 
                                var data = {id:$(this).attr('id'),type: $(this).attr('name'),value: $(this).val()};      
                            }else{
                                var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};   
                            }
                        }
                        pricing.push(data);
                    }
            });

            $.confirm({
              text: wplms_front_end_messages.save_course_confirm,
              confirm: function() {
               
                 $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: { action: 'new_save_pricing', 
                                security: $('#security').val(),
                                course_id:$('#course_id').val(),
                                settings:JSON.stringify(pricing)    
                              },
                        cache: false,
                        success: function (html) {
                       
                            $this.removeClass('disabled');
                            if($.isNumeric(html)){
                                $('#course_creation_tabs').trigger('increment');
                            }else{
                                $this.html(html);
                                setTimeout(function(){$this.html(defaulttxt);}, 5000);
                            }
                        }
                });
              },
              cancel: function() {
                  $this.removeClass('disabled');
              },
              confirmButton: wplms_front_end_messages.save_course_confirm_button,
              cancelButton: vibe_course_module_strings.cancel
          });

        });

    $('.selectgroup').select2({
        minimumInputLength: 4,
        placeholder: $(this).attr('data-placeholder'),
        closeOnSelect: true,
        allowClear: true,
        language: {
          inputTooShort: function() {
            return vibe_course_module_strings.enter_more_characters;
          }
        },
        ajax: {
            url: ajaxurl,
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function(term){ 
                    return  {   action: 'get_front_groups', 
                                security: $('#security').val(),
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
    $('.selectforum').select2({
        minimumInputLength: 4,
        placeholder: $(this).attr('data-placeholder'),
        closeOnSelect: true,
        allowClear: true,
        language: {
          inputTooShort: function() {
            return vibe_course_module_strings.enter_more_characters;
          }
        },
        ajax: {
            url: ajaxurl,
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function(term){ 
                    return  {   action: 'get_forums', 
                                security: $('#security').val(),
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
    $('.use_selected').on('click',function(){
        var val = $(this).parent().find('select').val();
        var label = $(this).parent().find('select option').text();
        var parent = $(this).parent().parent().parent().parent();
        parent.find('input[type="hidden"]').val(val);
        var type = $(this).parent().attr('class');
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'get_permalink', 
                    security: $('#security').val(),
                    type:type,
                    id: val,
                  },
            cache: false,
            success: function (html) {
                parent.find('h3').html('');
                parent.find('h3').html(html);
                $('.course_components').trigger('active');
            }
        });        
        parent.find('h3>span').trigger('click');
    })

    $('.selectcpt.select2').each(function(){
        var cpt = $(this).attr('data-cpt');
        var post_status = $(this).attr('data-status');
        var placeholder = $(this).attr('data-placeholder');
        $(this).select2({
            minimumInputLength: 4,
            placeholder: placeholder,
            closeOnSelect: true,
            allowClear: true,
            language: {
              inputTooShort: function() {
                return vibe_course_module_strings.enter_more_characters;
              }
            },
            ajax: {
                url: ajaxurl,
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function(term){ 
                        return  {   action: 'get_select_cpt', 
                                    security: $('#security').val(),
                                    cpt: cpt,
                                    stats: post_status,
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
    $('.use_selected_product').on('click',function(){
        var $this = $(this);
        if($this.hasClass('disabled'))
            return;

        $this.addClass('disabled');
        $('body').trigger('modal_open');
        var product_id = $(this).parent().find('select').val();
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'set_product', 
                    security: $('#security').val(),
                    course_id:$('#course_id').val(),
                    product_id: product_id,
                  },
            cache: false,
            success: function (html) {
                $this.removeClass('disabled');
                $('#course_pricing .vibe_vibe_product>h3').html(html);
                $('#change_product,#edit_product').hide();
                $('.course_pricing').trigger('active');
            }
        });
    });
    $('#create_new_product').on('click',function(e){
        var $this = $(this);
        
        if($this.hasClass('disabled'))
            return;

        $this.addClass('disabled');
        $('body').trigger('modal_open');
        var parent = $(this).parent();
        var defaulttxt = $(this).text();
        var settings = [];
        var course_id = $('#course_id').val();
        $('.new_product_form').find('.post_field').each(function() {
                
                if($(this).is(':checkbox:checked')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is(':radio:checked')){ 
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('select')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('input[type="text"]')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('input[type="number"]')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('input[type="hidden"]')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('textarea')){
                   var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};   
                }
                //localStorage.setItem('product_'+$(this).attr('data-id')+course_id,$(this).val());
                settings.push(data);
        });


        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'create_new_product', 
                    security: $('#security').val(),
                    course_id:course_id,
                    settings: JSON.stringify(settings),
                  },
            cache: false,
            success: function (html) {
                $this.removeClass('disabled');
                $('#course_pricing .vibe_vibe_product>h3').html(html);
                $('#change_product,#edit_product').hide();
                $('.course_pricing').trigger('active');
            }
        });
    });
    
    $(document).on('click','#edit_course_product',function(e){
        var $this = $(this);
        
        if($this.hasClass('disabled'))
            return;

        $this.addClass('disabled');
        $('body').trigger('modal_open');
        var parent = $(this).parent();
        var defaulttxt = $(this).text();
        var settings = [];

        var course_id = $('#course_id').val();
        $('#edit_product').find('.post_field').each(function() {
                
                if($(this).is(':checkbox:checked')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is(':radio:checked')){ 
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('select')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('input[type="text"]')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('input[type="number"]')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('input[type="hidden"]')){
                    var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                }
                if($(this).is('textarea')){
                    if($(this).hasClass('wp-editor-area')){
                        var data = {id:$(this).attr('id'),type: $(this).attr('name'),value: $(this).val()};   
                    }else{
                        var data = {id:$(this).attr('data-id'),type: $(this).attr('name'),value: $(this).val()};   
                    } 
                }
                //localStorage.setItem('product_'+$(this).attr('data-id')+course_id,$(this).val());
                settings.push(data);
        });
        

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'create_new_product', 
                    security: $('#security').val(),
                    course_id:course_id,
                    settings: JSON.stringify(settings),
                  },
            cache: false,
            success: function (html) {
                $this.removeClass('disabled');
                $('#course_pricing .vibe_vibe_product>h3').html(html);
                $('#change_product,#edit_product').hide();
                $('.course_pricing').trigger('active');
            }
        });
    });

    $('#create_new_group').on('click',function(){
        var $this = $(this);
        var defaulttxt = $this.text();
        if($this.hasClass('disabled'))
            return;

        $this.addClass('disabled');
        $('body').trigger('modal_open');
        $.confirm({
          text: wplms_front_end_messages.create_group_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'create_group', 
                            security: $('#security').val(),
                            course_id: $('#course_id').val(),
                            title: $('#vibe_group_name').val(),
                            privacy:$('#vibe_group_privacy').val(),
                            description : $('#vibe_group_description').val(),
                          },
                    cache: false,
                    success: function (html) {
                        $this.removeClass('disabled');
                        if($.isNumeric(html)){
                            $('#vibe_group').val(html);
                            var span = $('.vibe_vibe_group>.field_wrapper>h3>span').html();
                            var nhtml = html;
                            $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: { action: 'get_permalink', 
                                        security: $('#security').val(),
                                        type:'group',
                                        id: nhtml,
                                      },
                                cache: false,
                                success: function (html) {
                                    $('.vibe_vibe_group>.field_wrapper>h3').html(html);
                                    $('.course_components').trigger('active');
                                }
                            });
                            
                            $('.vibe_vibe_group>.field_wrapper>h3>span').trigger('click');
                        }else{
                            $this.html(html);
                            setTimeout(function(){$this.html(defaulttxt);}, 2000);
                        }
                    }
            });
          },
          cancel: function() {
              $this.removeClass('disabled');
          },
          confirmButton: wplms_front_end_messages.create_group_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
      });
    });
    $('#create_new_forum').on('click',function(){
        var $this = $(this);
        var defaulttxt = $this.text();
        if($this.hasClass('disabled'))
            return;

        $this.addClass('disabled');
        $('body').trigger('modal_open');
        $.confirm({
          text: wplms_front_end_messages.create_forum_confirm,
          confirm: function() {
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'create_forum', 
                        security: $('#security').val(),
                        course_id: $('#course_id').val(),
                        title: $('#vibe_forum_name').val(),
                        privacy:$('#vibe_forum_privacy').val(),
                        description : $('#vibe_forum_description').val(),
                      },
                cache: false,
                success: function (html) {
                    
                    $this.removeClass('disabled');
                    if($.isNumeric(html)){
                        $('#vibe_forum').val(html);
                        var span = $('.vibe_vibe_forum>.field_wrapper>h3>span').html();
                        var nhtml = html;
                        $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: { action: 'get_permalink', 
                                        security: $('#security').val(),
                                        type:'forum',
                                        id: nhtml,
                                      },
                                cache: false,
                                success: function (html) {
                                    $('.vibe_vibe_forum>.field_wrapper>h3').html(html);
                                    $('.course_components').trigger('active');
                                }
                            });
                        $('.vibe_vibe_forum>.field_wrapper>h3>span').trigger('click');
                    }else{
                        $this.html(html);
                        setTimeout(function(){$this.html(defaulttxt);}, 2000);
                    }
                }
            });
          },
          cancel: function() {
              $this.find('i').remove();
              $this.removeClass('disabled');
          },
          confirmButton: wplms_front_end_messages.create_group_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
      });
    });
    
    $('#save_course_curriculum_button').on('click',function(){
        var course_id=$('#course_id').val();
        var $this = $(this);
        var defaulttxt = $this.html();
        var curriculum = [];
        if($(this).hasClass('disabled'))
            return;

        $('ul.curriculum > li').each(function() {

            if($(this).hasClass('new_section')){

                if($(this).find('input.section').length){
                    var val = $(this).find('input.section').val();
                }else{
                    var val = $(this).find('strong').text();
                }
                
            }else{
               var val =  $(this).find('strong.title').attr('data-id');
            }
            if(typeof val != 'undefined'){
                var data = { id: val };  
                curriculum.push(data);                 
            } 
        });

        $this.addClass('disabled');
        $('body').trigger('modal_open');
        $.confirm({
          text: wplms_front_end_messages.save_course_confirm,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'save_course_curriculum', 
                            security: $('#security').val(),
                            course_id: course_id,
                            curriculum: JSON.stringify(curriculum),
                          },
                    cache: false,
                    success: function (html) {
                        $this.removeClass('disabled');
                        if($.isNumeric(html)){
                            $('#course_creation_tabs').trigger('increment');
                        }else{
                            $this.html(html);
                            setTimeout(function(){$this.html(defaulttxt);}, 2000);
                        }
                    }
            });
          },
          cancel: function(){
              $this.removeClass('disabled');
          },
          confirmButton: wplms_front_end_messages.save_course_confirm_button,
          cancelButton: vibe_course_module_strings.cancel
          });
    });

    $('#course_curriculum').on('active',function(){

        $('.trigger_new_product').unbind('click');
        $('.trigger_new_product').on('click',function(){
            $('.new_product').toggle(200);
        });
        $('#save_course_curriculum_button').removeClass('disabled');

        $('.select_existing').unbind('click');
        $('.select_existing').on('click',function(){
            $(this).parent().find('.existing').toggle(200);
        }); 

        $('.select_new').unbind('click');
        $('.select_new').on('click',function(){
            $(this).parent().find('.new_actions').toggle(200);
        });
        $('.tip').tooltip();
        $('.add_cpt .more').unbind('click');
        $('.add_cpt .more').click(function(event){
            $('.select_existing_cpt,.new_cpt').hide();
            $(this).next().toggle(200);
        });
        $('.wplms-taxonomy select').change(function(event){
            var new_tax = $(this).parent().parent().find('.wplms-new-taxonomy');
            if($(this).val() === 'new'){
                new_tax.addClass('animate cssanim fadeIn load');
            }else{
                new_tax.removeClass('animate cssanim fadeIn load');
            }
        });
        
        //$('.chosen').chosen();
        
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

        $('.add_dynamic_question_tag').unbind('click');
        $('.add_dynamic_question_tag').on('click',function(){
            var $this = $(this);
            var length = $this.parent().find('ul.post_field > li').length;
            if(typeof length == 'undefined')
                length = 0;
            
            $this.parent().find('.hidden_block select').attr('data-id', $this.parent().find('.hidden_block select').attr('data-id')+'['+length+'][]');
            $this.parent().find('.hidden_block input').attr('data-id', $this.parent().find('.hidden_block input').attr('data-id')+'['+length+']');

            var newblock = $this.parent().find('.hidden_block').html();
            
            $this.parent().find('ul.post_field').append('<li>'+newblock+'</li>');
            $this.parent().find('ul.post_field').find('select').select2();

            $('#vibe_quiz_tags input').on('change',function(){
                var total_count = parseInt(0);var total_marks = parseInt(0);
                $('#vibe_quiz_tags input.count').each(function(){
                    var val = parseInt($(this).val());
                    if(val == 'NAN' || val ==''){val=parseInt(0);}
                    total_count += val;
                });
                $('#vibe_quiz_tags input.marks').each(function(i,item){
                    var val = parseInt($(this).val());
                     var c = $(this).parent().find('input.count').val();
                    if(c == 'NAN' || c ==''){c=parseInt(0);}
                    if(val == 'NAN' || val ==''){val=parseInt(0);}
                    total_marks += val*c;
                });

                $('#total_question_count').text(total_count);
                $('#total_question_marks').text(total_marks);
            });
            $('.remove_tag').on('click',function(){$(this).parent().remove();});
        });

        $('#vibe_quiz_tags input').on('change',function(){
            var total_count = parseInt(0);var total_marks = parseInt(0);
            $('#vibe_quiz_tags input.count').each(function(){
                var val = parseInt($(this).val());
                if(val == 'NAN' || val ==''){val=parseInt(0);}
                total_count += val;
            });
            $('#vibe_quiz_tags input.marks').each(function(i,item){
                var val = parseInt($(this).val());
                 var c = $(this).parent().find('input.count').val();
                if(c == 'NAN' || c ==''){c=parseInt(0);}
                if(val == 'NAN' || val ==''){val=parseInt(0);}
                total_marks += val*c;
            });

            $('#total_question_count').text(total_count);
            $('#total_question_marks').text(total_marks);
        });

        $('.remove_tag').on('click',function(){$(this).parent().remove();});

        $('.selectcpt.select2').each(function(){

            if($(this).hasClass('select2-hidden-accessible'))
                return;
            
            var cpt = $(this).attr('data-cpt');
            var placeholder = $(this).attr('data-placeholder');
            var post_status = $(this).attr('data-status');
            $(this).select2({
                minimumInputLength: 4,
                placeholder: placeholder,
                closeOnSelect: true,
                allowClear: true,
                language: {
                  inputTooShort: function() {
                    return vibe_course_module_strings.enter_more_characters;
                  }
                },
                ajax: {
                    url: ajaxurl,
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(term){ 
                            return  {   action: 'get_select_cpt', 
                                        security: $('#security').val(),
                                        cpt: cpt,
                                        status:post_status,
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
        
         /* ===== Save Unit/Quiz ==== */
        $('#save_element_button').unbind('click');
        $('#save_element_button').on('click',function(event){
            var $this = $(this);
                var defaulttxt = $this.html();
                if($this.hasClass('disabled'))
                    return;

                $this.addClass('disabled');

                var settings = [];
                var main;
                if($this.parent().hasClass('question_edit_settings_content')){
                    main = '.question_edit_settings_content';
                }else if($this.parent().hasClass('wplms-assignment_edit_settings_content')){
                    main = '.wplms-assignment_edit_settings_content';
                }else{
                    main = '.element_overlay';
                }
                
                $(main).find('.post_field').each(function() {
                        

                        if($(this).is(':radio:checked')){ 
                            var data = {id:$(this).attr('name'),value: $(this).val()};
                        }
                        if($(this).is('select')){
                            if($(this).is("select[multiple]")){
                                var values = {};

                                $(this).find('option:selected').each(function(i,selected){
                                    values[i] = $(selected).val();
                                });
                                var data = {id:$(this).attr('data-id'),value: values};
                            }else{
                                var data = {id:$(this).attr('data-id'),value: $(this).val()};
                            }
                        }
                        if($(this).hasClass('repeatable')){
                            if($(this).is('#vibe_quiz_tags')){
                                var tags ={};
                                var numbers ={};
                                var marks ={};
                                $('#vibe_quiz_tags > li').each(function(i,selected){
                                    var t ={};
                                    $(this).find('input[type="hidden"]').each(function(j,s){
                                        t[j]=$(this).val();
                                    });

                                    if($(this).find('select').length){
                                        t=$(this).find('select').val();    
                                    }

                                    numbers[i] = $(this).find('input[type="text"].count').val();
                                    marks[i] = $(this).find('input[type="text"].marks').val();
                                    tags[i] = t;
                                });
                                var data = {id:$(this).attr('data-id'),value: {'tags':tags,'numbers':numbers,'marks':marks}};    
                                
                            }else{
                                var values = {};
                                $(this).find('li').each(function(i,selected){
                                    values[i] = $(this).find('input').val();
                                });
                                var data = {id:$(this).attr('data-id'),value: values};    
                            }
                        }

                        if($(this).hasClass('list-group-questions')){
                            var values = {};
                            var marks = {};
                            var val = {};
                            $(this).find('.question_block').each(function(i,selected){
                                values[i] = $(this).find('.question_id').val();
                                marks[i] = $(this).find('.question_marks').val();
                            });
                            val ={ques:values,marks:marks};
                            var data = {id:$(this).attr('data-id'),value: val};
                        }

                        if($(this).hasClass('list-group-assignments')){
                            var values = {};
                            $(this).find('.assignment_block').each(function(i,selected){
                                values[i] = $(this).find('.assignment_id').val();
                            });
                            var data = {id:$(this).attr('data-id'),value: values};
                        }

                        if($(this).is('input[type="text"]')){
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                        }
                        if($(this).is('input[type="number"]')){
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('name'),value: $(this).val()};
                        }
                        if($(this).is('input[type="hidden"]')){
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
                        }
                        if($(this).is('textarea')){
                            if($(this).hasClass('wp-editor-area')){
                                tinyMCE.triggerSave();
                                var data = {id:$(this).attr('id'),type: $(this).attr('name'),value: $(this).val()};    
                            }else{
                                var data = {id:$(this).attr('data-id'),type: $(this).attr('name'),value: $(this).val()};   
                            }
                        }
                        settings.push(data);
                });

                $.confirm({
                  text: wplms_front_end_messages.save_confirm,
                  confirm: function() {
                   
                     $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: { action: 'save_element', 
                                    security: $('#security').val(),
                                    id:$this.attr('data-id'),
                                    course_id:$('#course_id').val(),
                                    settings:JSON.stringify(settings)    
                                  },
                            cache: false,
                            success: function (html) {
                                $this.removeClass('disabled');
                                $this.html(html);
                                setTimeout(function(){$this.html(defaulttxt);}, 5000);
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
        
        /* === Questions List in Quizes === */
        $('.edit_sub').unbind('click');
        $('.edit_sub').on('click',function(event){
            event.preventDefault();
            var $this = $(this);
            var parent = $this.parent().parent().parent();
            

            if(parent.hasClass('loaded')){
                parent.toggle('collapse');
            }
            if($this.hasClass('disabled'))
                return;

            $('.question_edit_settings_content').remove();
            $this.addClass('disabled');
            parent.css('opacity','0.6');
            parent.append('<div id="ajaxloader"></div>');

            $('#save_element_button').addClass('disabled');
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'get_sub_element', 
                        security: $('#security').val(),
                        id:parent.find('input[type="hidden"]').val(),
                      },
                cache: false,
                success: function (html) {
                    $this.removeClass('disabled');
                    parent.hasClass('loaded');
                    parent.css('opacity','1');
                    parent.find('#ajaxloader').remove();
                    parent.append(html);
                    $('#course_curriculum').trigger('active');
                    $('select.chosen[multiple]').select2();
                }
            });
        });
        $('.preview_sub').unbind('click');
        $('.preview_sub').on('click',function(event){
            event.preventDefault();
            var $this = $(this);
            var parent = $this.parent().parent().parent();
            if(parent.hasClass('loaded')){
                parent.toggle('collapse');
            }
            if($this.hasClass('disabled'))
                return;

            $this.addClass('disabled');
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'preview_sub_element', 
                        security: $('#security').val(),
                        id:parent.find('input[type="hidden"]').val(),
                      },
                cache: false,
                success: function (html) {
                    $this.removeClass('disabled');
                    parent.hasClass('loaded');
                    parent.append(html);
                    $('#course_curriculum').trigger('question_loaded');
                }
            });
        });

        $('.remove_sub').unbind('click');
        $('.remove_sub').on('click',function(){
            $(this).parent().parent().parent().remove();
        });
        $('.delete_sub').unbind('click');
        $('.delete_sub').on('click',function(){
            var $this = $(this);

            if($this.hasClass('disabled'))
                return;

            $this.addClass('disabled');
            var post_id = $(this).closest('.data_links').parent().find('.title').attr('data-id');
            $.confirm({
                  text: wplms_front_end_messages.delete_confirm,
                  confirm: function() {
                   
                     $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: { action: 'delete_element', 
                                    security: $('#security').val(),
                                    id:post_id,  
                                  },
                            cache: false,
                            success: function (html) {
                                $this.removeClass('disabled');
                                if($.isNumeric(html)){
                                    $this.closest('.data_links').parent().remove();
                                }
                            }
                    });
                  },
                  cancel: function() {
                      $this.removeClass('disabled');
                  },
                  confirmButton: wplms_front_end_messages.delete_confirm_button,
                  cancelButton: vibe_course_module_strings.cancel
              });
        }); 
        $('#close_element_button').unbind('click');
        $('#close_element_button').click(function(){
            $(this).parent().hide(200).remove();
            $('#save_element_button').removeClass('disabled');
        });

        $('ul.repeatable').sortable({
            revert: true,
            cursor: 'move',
            refreshPositions: true, 
            opacity: 0.6,
            scroll:true,
            containment: 'parent',
            placeholder: 'placeholder',
            tolerance: 'pointer',
            update: function(event, ui) {
                $(this).trigger('update');
            }
        });//.disableSelection();
        $('ul.repeatable').on('update',function(){
            if(!$(this).is('#vibe_quiz_tags')){
                var index=0;
                $(this).find('li').each(function(){
                    index= $(this).index();
                    $(this).find('span').text((index+1));
                });
            }
        });

        $('.add_repeatable_count_option').on('click',function(){
            var clone = $('ul.hidden >li').clone();
            var count = $(this).next().find('li').length;
            clone = '<li><span>'+(count+1)+'</span>'+clone.html()+'</li>';
            $(this).next().append(clone);
            $('#course_curriculum').trigger('question_loaded');
        });

        $('.list-group-questions').sortable({
            item: '.question_block',
            handle: '.dashicons-sort',
            revert: true,
            cursor: 'move',
            refreshPositions: true, 
            opacity: 0.6,
            scroll:true,
            containment: 'parent',
            placeholder: 'placeholder',
            tolerance: 'pointer',
        });//.disableSelection();

        $('.use_selected_question').unbind('click');
        $('.use_selected_question').on('click',function(){
            var id = $(this).parent().find('.selectcpt option:selected').val();
            var name = $(this).parent().find('.selectcpt option:selected').text();
            var clone = $('.list-group-questions .hidden_block').clone();
          
            clone.find('.title').text(name).attr('data-id',id);
            clone.find('.question_id').val(id);
            clone.find('.question_marks').val('0');
            clone.removeClass('hide').removeClass('hidden_block').addClass('question_block');
            clone.insertBefore('.list-group-questions .hidden_block');
            $('.select_existing_cpt,.new_cpt').hide();
            $('#course_curriculum').trigger('active');
        });
        $(document).on('click','#create_new_question',function(e){
            var $this = $(this);
            
            if($this.hasClass('disabled'))
                return;

            $this.addClass('disabled');
            var parent = $(this).parent();
            var defaulttxt = $(this).text();
            var title = parent.find('#vibe_question_title').val(); 
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'create_new_question', 
                        security: $('#security').val(),
                        title: title,
                        question_tag:$('#question-tag-select').val(),
                        new_question_tag:$('#new_question_tag').val(),
                        template: parent.find('#vibe_question_template').val()
                      },
                cache: false,
                success: function (html) {
                    $this.removeClass('disabled');
                    if($.isNumeric(html)){
                        var clone = $('.list-group-item.hidden_block').clone();
                        clone.find('.title').text(title).attr('data-id',html);
                        clone.find('.question_id').val(html);
                        clone.find('.question_marks').val('0');
                        clone.removeClass('hide').removeClass('hidden_block').addClass('question_block');
                        clone.insertBefore('.list-group-questions .hidden_block');
                        parent.find('#vibe_question_title').val('');
                        $('#new_question_tag').val('');
                        $('.select_existing_cpt,.new_cpt').hide();
                        $('#course_curriculum').trigger('active');
                    }else{
                        $this.html(html);
                        setTimeout(function(){$this.html(defaulttxt);}, 5000);
                    }
                }
            });
        });
        /*==== End question List ===*/
        /* === Assignment List in Units === */
        $('.list-group-assignments').sortable({
            item: '.assignment_block',
            handle: '.dashicons-sort',
            revert: true,
            cursor: 'move',
            refreshPositions: true, 
            opacity: 0.6,
            scroll:true,
            containment: 'parent',
            placeholder: 'placeholder',
            tolerance: 'pointer',
        });//.disableSelection();
        $('.use_selected_assignment').unbind('click');
        $('.use_selected_assignment').on('click',function(){
            var id = $(this).parent().find('.selectcpt option:selected').val();
            var name = $(this).parent().find('.selectcpt option:selected').text();
            var clone = $('.hidden_block').clone();
            clone.find('.title').text(name).attr('data-id',id);
            clone.find('.assignment_id').val(id);
            clone.removeClass('hide').removeClass('hidden_block').addClass('assignment_block');
            clone.insertBefore('.list-group-assignments .hidden_block');
            $('.remove_sub').on('click',function(){
                $(this).parent().parent().parent().remove();
            });
            $('#course_curriculum').trigger('active');
        });

        $(document).on('click','#create_new_assignment',function(e){
            var $this = $(this);
            
            if($this.hasClass('disabled'))
                return;

            $this.addClass('disabled');
            var parent = $(this).parent();
            var defaulttxt = $(this).text();
            var title = parent.find('#vibe_assignment_title').val();
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'create_new_assignment', 
                        security: $('#security').val(),
                        cpt:'wplms-assignment',
                        title: title,
                      },
                cache: false,
                success: function (html) {
                    $this.removeClass('disabled');
                    if($.isNumeric(html)){
                        var clone = $('.hidden_block').clone();
                        clone.find('.title').text(title).attr('data-id',html);
                        clone.find('.assignment_id').val(html);
                        clone.removeClass('hide').removeClass('hidden_block').addClass('assignment_block');
                        clone.insertBefore('.list-group-assignments .hidden_block');
                        parent.find('#vibe_assignment_title').val('');
                        $('.remove_sub').on('click',function(){
                            $(this).parent().parent().parent().remove();
                        });
                        $('#course_curriculum').trigger('active');
                    }else{
                        $this.html(html);
                        setTimeout(function(){$this.html(defaulttxt);}, 5000);
                    }
                }
            });
        });
        $('.data_links .edit').unbind('click');
        $('.data_links .edit').on('click',function(){
            var $this = $(this);
            var defaulttxt = $this.html();


            console.log(jQuery.active);
            if($this.hasClass('disabled'))
                return;
            
            $this.addClass('disabled');
            $this.closest('div.active').css('opacity','0.6');
            $this.closest('div.active').append('<div id="ajaxloader"></div>');

            $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'get_element', 
                            security: $('#security').val(),
                            course_id:$('#course_id').val(),
                            element_id: $this.parent().parent().parent().find('.title').attr('data-id'),
                          },
                    cache: false,
                    success: function (html) {

                        $('#ajaxloader').remove();
                        $this.removeClass('disabled');
                        $this.closest('div.active').css('opacity','1');

                        var parent;
                        if($('#course_curriculum').hasClass('active')){
                            parent = $('#course_curriculum');
                        }else if($('#events').hasClass('active')){
                             parent = $('#events');
                        }


                        parent.append(html);

                        var height = parent.find('.element_overlay').outerHeight()+60;

                        parent.css('height',height+'px');
                        parent.css('overflow-y','scroll');
                        parent.trigger('active');

                        $('.element_overlay .close-pop').click(function(){
                            $(this).parent().remove();
                        });
                        $('.add_cpt .more').click(function(event){
                            $('.select_existing_cpt,.new_cpt').hide();
                            $(this).next().toggle(200);
                        });
                        $('.accordion_trigger').on('click',function(){
                            $(this).parent().toggleClass('open');
                            $('.vibe_vibe_quiz_tags .select2').select2({allowClear: true});
                        });

                    }
            });
        });
        $('.data_links .preview').unbind('click');
        $('.data_links .preview').on('click',function(){
            var $this = $(this);
            var defaulttxt = $this.html();
            $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'preview_element', 
                            security: $('#security').val(),
                            course_id:$('#course_id').val(),
                            element_id: $this.parent().parent().parent().find('.title').attr('data-id'),
                          },
                    cache: false,
                    success: function (html) {
                        var parent;
                        if($('#course_curriculum').hasClass('active')){
                            parent = $('#course_curriculum');
                        }else if($('#events').hasClass('active')){
                             parent = $('#events');
                        }
                        parent.append(html);

                        var height = parent.find('.element_overlay').outerHeight()+60;

                        parent.css('height',height+'px');
                        parent.css('overflow-y','scroll');
                        parent.trigger('active');

                        $('.element_overlay .close-pop').click(function(){
                            $(this).parent().remove();
                        });
                        $('.accordion_trigger').on('click',function(){
                            $(this).parent().toggleClass('open');
                        });
                        
                    }
            });
        });
        $('.data_links .remove').unbind('click');
        $('.data_links .remove').on('click',function(){
            $(this).closest('.data_links').closest('li').remove();
        });
        $('.data_links .delete').unbind('click');
        $('.data_links .delete').on('click',function(){
            var $this = $(this);

            if($this.hasClass('disabled'))
                return;

            $this.addClass('disabled');
            var post_id = $(this).closest('.data_links').parent().find('.title').attr('data-id');
            $.confirm({
                  text: wplms_front_end_messages.delete_confirm,
                  confirm: function() {
                   
                     $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: { action: 'delete_element', 
                                    security: $('#security').val(),
                                    id:post_id,  
                                  },
                            cache: false,
                            success: function (html) {
                                $this.removeClass('disabled');
                                if($.isNumeric(html)){
                                    $this.closest('.data_links').parent('li').remove();
                                }
                            }
                    });
                  },
                  cancel: function() {
                      $this.removeClass('disabled');
                  },
                  confirmButton: wplms_front_end_messages.delete_confirm_button,
                  cancelButton: vibe_course_module_strings.cancel
              });
        });
    });
    /*==== End Assignment List ===*/
    $('#course_curriculum').on('question_loaded',function(){
        $('#close_element_button').click(function(){
            $(this).parent().hide(200).remove();
            $('#save_element_button').addClass('disabled');
        });

    });
    $('body').delegate('#add_course_section','click',function(event){
        var clone = $('#hidden_base .new_section').clone();
        $('ul.curriculum').append(clone);
        $('#course_curriculum').trigger('add_section');
    });

    $('#add_course_unit').on('click',function(event){
        
        var clone = $('#hidden_base .new_unit').clone().attr('id','id'+Math.floor(Math.random()*100));;
        $('#save_course_curriculum_button').addClass('disabled');
        clone.find('.select_existing_cpt select').addClass('selectcurriculumcpt');
        clone.find('input[name="name"]').attr('name','name'+Math.floor(Math.random()*100)).val("").attr("id","uid"+Math.floor(Math.random()*100));
        $('ul.curriculum').append(clone);
        $('#course_curriculum').trigger('add_section');
        return false;
    });

    $('#add_course_quiz').on('click',function(event){

        var clone = $('#hidden_base .new_quiz').clone().attr('id','id'+Math.floor(Math.random()*100));;;
        clone.find('.select_existing_cpt select').addClass('selectcurriculumcpt');
        clone.find('input[name="name"]').attr('name','name'+Math.floor(Math.random()*100));
        $('ul.curriculum').append(clone);
        $('#course_curriculum').trigger('add_section');
        return false;
    });

    $('#course_curriculum').on('add_section',function(){

        $('.add_cpt .more').click(function(event){
            $('.select_existing_cpt,.new_cpt').hide();
            $(this).next().toggle(200);
        });
        $('.selectcurriculumcpt').each(function(){
            if($(this).hasClass('select2-hidden-accessible'))
                return;

            var cpt = $(this).attr('data-cpt');
            var placeholder = $(this).attr('data-placeholder');
            $(this).select2({
                minimumInputLength: 4,
                placeholder: placeholder,
                allowClear: true,
                closeOnSelect: true,
                language: {
                  inputTooShort: function() {
                    return vibe_course_module_strings.enter_more_characters;
                  }
                },
                ajax: {
                    url: ajaxurl,
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(term){ 
                            return  {   action: 'get_select_cpt', 
                                        security: $('#security').val(),
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
        $('.use_selected_curriculum').on('click',function(){
            var $this = $(this);
            var clone = $('#hidden_base').prev().clone();
            var id = $(this).parent().find('.selectcurriculumcpt').val();
            var title = $(this).parent().find('.selectcurriculumcpt option:selected').text();
            $(clone).find('.title > span').text(title);
            $(clone).find('.title').attr('data-id',id);
            $(clone).find('input[name="name"]').attr('name','name'+Math.floor(Math.random()*100));
            var html = clone.html();
            $('.vibe_vibe_course_curriculum ul.curriculum').append(html);
            $this.closest('.new_unit').remove();
            $this.closest('.new_quiz').remove();
            $('#course_curriculum').trigger('active');
        });

        $('.create_new_curriculum').on('click',function(){
            var $this = $(this);
            
            if($this.hasClass('disabled')){
                return;
            }
            var defaulttxt = $this.text();
            var parent = $(this).parent();
            var title = parent.find('.vibe_curriculum_title').val();
            
            $this.addClass('disabled');
            $('body').trigger('modal_open');
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'create_new_curriculum', 
                        security: $('#security').val(),
                        title: title,
                        cpt:$this.parent().find('.vibe_cpt').val()
                      },
                cache: false,
                success: function (html) {
                    $this.removeClass('disabled');
                    if($.isNumeric(html)){
                        var clone = $('#hidden_base').prev().clone();
                        $(clone).find('.title > span').text(title);
                        $(clone).find('.title').attr('data-id',html);
                        var html =clone.html();
                        $('.vibe_vibe_course_curriculum ul.curriculum').append(html);
                        $this.closest('.new_unit').remove();
                        $this.closest('.new_quiz').remove();
                        $('#course_curriculum').trigger('active');
                        $('#close_element_button').click(function(){
                            $(this).parent().hide(200).remove();
                        });
                    }else{
                        $this.html(html);
                        setTimeout(function(){$this.html(defaulttxt);}, 5000);
                    }
                }
            });

        });
        

    });
    /*$('body').on('modal_open',function(){
        $("body").click(function(e) {
            if ($(e.target).closest(".confirmation-modal").length) {
               $('.button.disabled').removeClass('disabled');
            }
        });
    });*/

    var saved_templates_dom='';
    var hide_applied = '';
    if(typeof $('#course_id') != 'undefined' && $('#course_id').val() != 'undefined' && $('#course_id').val() !=null && $('#course_id').val() != '' ){
        hide_applied = 'applied';
    }else{
        hide_applied = '';
    }
    saved_templates_dom += '<div id="create_course_templates_wrapper_make_height"></div><div id="create_course_templates_wrapper" class="'+hide_applied+'">\
        <a id="create_course_templates_wrapper_close"><i class="fa fa-times"></i></a>\
        <div class="container-fluid"><div class="row">\
            <div class="col-md-6">\
            <a id="create_fresh_course" class="button course_template_button">'+wplms_front_end_messages.create_your_own+'</a>\
            </div>\
            <div class="col-md-6">\
            <a id="upload_course" class="button course_template_button">'+wplms_front_end_messages.upload_package+'</a>\
            </div>\
        </div><div class="row">';
    if(typeof saved_course_templates != 'undefined' && Object.keys(saved_course_templates).length > 0){
        saved_templates_dom += '<h3 class="heading center"><span>'+wplms_front_end_messages.saved_c_templates+'</span></h3>';
        $.each(saved_course_templates,function(key,template){
            saved_templates_dom += '<div class="col-md-3 col-sm-6 course_template_item"><div class="course_template" data-id="'+key+'"><h3>'+template.name+'</h3><p>'+template.desc+'</p></div><span class="delete_course_template"></span></div>'; 
        }); 
        
    }
    
    saved_templates_dom += '</div></div></div>';
    function set_template_for_course(course_id,template_id){
        $.ajax({
            type: "POST",
            url: ajaxurl,
            async:true,
            data: { action: 'set_template_for_course', 
                    security: $('#security').val(),
                    course_id: course_id,
                    template_id:template_id,
                  },
            cache: false,
            
        }); 
    }
   
    function apply_settings(template_settings_data , noval){
        noval = noval || 0;
        $.each(template_settings_data,function(k,section){
            if(section.visibility == 0){
                $('body').find('#'+section.section).addClass('hide_cc_element');
                $('body').find('.'+section.section).addClass('hide_cc_element');
            }else{
                $('body').find('#'+section.section).removeClass('hide_cc_element');
                $('body').find('.'+section.section).removeClass('hide_cc_element');
            }
            $.each(section.fields,function(id,field){
                if(field.visibility == 0){
                    /*$('body').find('[data-id="'+field.id+'"]').addClass('hide_cc_element');*/
                    $('body').find('.vibe_'+field.id).addClass('hide_cc_element');
                }else{
                    $('body').find('.vibe_'+field.id).removeClass('hide_cc_element');
                }
                if(field.value != null && typeof field.value != 'undefined' && noval==0){
                     switch(field.type){
                        case 'text':
                        case 'number':
                        case 'duration':
                        case 'date':
                        
                         $('.edit_course_content.content').find('[data-id="'+field.id+'"]').val(field.value);
                         $('.edit_course_content.content').find('[data-id="'+field.id+'"]').trigger('change');
                        break;
                        case 'switch':
                        case 'yesno':
                        case 'conditionalswitch':
                        case 'reverseconditionalswitch':
                            if(field.value == 'H'){
                                $('.edit_course_content.content').find('#'+field.id+'H').prop('checked','checked');
                                $('.edit_course_content.content').find('#'+field.id+'H').trigger('click');
                            }else{
                                $('.edit_course_content.content').find('#'+field.id+'S').prop('checked','checked');
                                $('.edit_course_content.content').find('#'+field.id+'S').trigger('click');

                            }
                        break;
                        case 'date':
                            $('.edit_course_content.content').find('[data-id="'+field.id+'"]').val(field.value);
                        break;
                        case 'editor':
                     
                            $('.edit_course_content.content').find('#'+field.id).val(field.value);
                            if($('.edit_course_content.content').find('#'+field.id).hasClass('wp-editor-area') && $('.edit_course_content.content').find('#'+field.id).attr('aria-hidden') == 'true')
                            tinymce.editors[field.id].setContent(field.value);
                        break;
                        case 'select':
                        case 'selectmulticpt':
                        case 'selectcpt':
                        case 'multiselect':
                            if($('.edit_course_content.content').find('[data-id="'+field.id+'"]').hasClass('select2-hidden-accessible') && ($('.edit_course_content.content').find('[data-id="'+field.id+'"]').hasClass('select2') || $('.edit_course_content.content').find('[data-id="'+field.id+'"]').hasClass('chosen'))){
                            
                               /* $('.edit_course_content.content').find('[data-id="'+field.id+'"]').select2('data',field.value);*/

                                var s2_options = $('.edit_course_content.content').find('[data-id="'+field.id+'"]:not(.cc_field)').data('select2').options.options;

                                $('.edit_course_content.content').find('[data-id="'+field.id+'"]:not(.cc_field)').select2('destroy');

                                if(field.value.constructor === Array){
                                    $.each(field.value,function(k,v){
                                         $('.edit_course_content.content').find('[data-id="'+field.id+'"]:not(.cc_field)').html(
                                            '<option value="' + v.id + '" selected="selected">' + v.text + '</option>'
                                          );
                                    });
                                }else{
                                   $('.edit_course_content.content').find('[data-id="'+field.id+'"]:not(.cc_field)').append(
                                    '<option value="' + data.id + '" selected="selected">' + data.text + '</option>'
                                  ); 
                                }
                                

                                $('.edit_course_content.content').find('[data-id="'+field.id+'"]:not(.cc_field)').select2(s2_options );


                                
                            }else{
                                $('.edit_course_content.content').find('[data-id="'+field.id+'"]').val(field.value);
                            }
                        break;
                        default:
                        break;
                    }
                }
                   
            });
        });
    }
    var course_curriculum_html = $('body').find('#course_creation_tabs li.course_curriculum').html();
    if(typeof course_existing_template_id !='undefined' && course_existing_template_id != null){
        if(course_existing_template_id == 'upload_course'){
            if(typeof upload_course_json != 'undefined' && upload_course_json != null){
                apply_settings(upload_course_json,1);
                $('body').find('#course_curriculum article ul.course_curriculum').addClass('hide_cc_element');
                $('body').find('#course_curriculum article .upload_course_wrapper').removeClass('hide_cc_element');
                if($('body').find('#course_curriculum article .upload_course_wrapper').length <= 0 && typeof plupload_wrapper != 'undefined'){

                    $('body').find('#course_curriculum article').append(plupload_wrapper);
                    
                    var course_curriculum_html_uplod = '<i class="fa fa-upload"></i><a href="#course_curriculum">'+wplms_front_end_messages.upload_package+'<span></span></a>';
                    $('body').find('#course_creation_tabs li.course_curriculum').html(course_curriculum_html_uplod);
                    $('body').trigger('upload_course_trigger');
                }
            }
        }else{
            if(typeof saved_course_templates != 'undefined' && saved_course_templates != null){
                var current_template_data='';
                $.each(saved_course_templates,function(k,v){
                    if(k == course_existing_template_id){
                       current_template_data = v;
                    }
                });
                apply_settings(current_template_data.template_data,1);
            }
        }
    }
    $('body').find('a.view_package').attr('href',$('body').find('.existing_packages_select').val());
    $('body').delegate('.existing_packages_select','change',function(){
            $('body').find('a.view_package').attr('href',$('body').find('.existing_packages_select').val());
    });

    $('body').delegate('.use_package','click',function(){
        var $this = $(this);
        if($this.hasClass('disabled')){
            return false;
        }
        $this.addClass('disabled');
        $.confirm({
            text: wplms_front_end_messages.set_course_package_confirm,
            confirm: function() {
                 $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'set_course_package', 
                            security: $('body').find('#course_package').val(),
                            course_id:$('body').find('#course_id').val(),
                            package_path:$('body').find('.existing_packages_select').val(),
                            package_name:$('body').find('.existing_packages_select option:selected').text(),
                          },
                    cache: false,
                    success: function (html) {
                       $this.removeClass('disabled');
                       var course_package_wrapper = '<div class="existing_course_package"><strong>'+$('body').find('.existing_packages_select option:selected').text()+'</strong>\
                        <a title="'+wplms_front_end_messages.remove_this_package+'" href="javascript:void(0)" class="button small remove_course_package right" data-package-name="'+$('body').find('.existing_packages_select option:selected').text()+'" data-package-path="'+$('body').find('.existing_packages_select').val()+'"><i class="fa fa-remove"></i></a>\
                        <a title="'+wplms_front_end_messages.view_this_package+'" href="'+$('body').find('.existing_packages_select').val()+'" target="_blank" class="button small right"><i class="fa fa-eye"></i></a>\
                        </div>';
                        $('body').find('.existing_course_package_wrapper').html(course_package_wrapper);
                    }
                });
            },
            cancel: function() {
                $this.removeClass('disabled');
                
            },
            confirmButton: wplms_front_end_messages.yes,
            cancelButton: vibe_course_module_strings.cancel
        });
        
    });
    
    $('body').delegate('.remove_package','click',function(){
        var $this = $(this);
        
        if($this.hasClass('disabled')){
            return false;
        }
        $this.addClass('disabled');
        $.confirm({
            text: wplms_front_end_messages.delete_course_package_confirm,
            confirm: function() {
                var option_value = $('body').find('.existing_packages_select').val();
                 $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'delete_course_package', 
                            security: $('body').find('#course_package').val(),
                            course_id:$('body').find('#course_id').val(),
                            package_path:option_value,
                            package_name:$('body').find('.existing_packages_select option:selected').text(),
                          },
                    cache: false,
                    success: function (html) {
                        $('.existing_packages_select option[value="'+option_value+'"]').each(function() {
                            $(this).remove();
                        });
                       $this.removeClass('disabled');
                    }
                });
            },
            cancel: function() {
                $this.removeClass('disabled');
            },
            confirmButton: wplms_front_end_messages.yes,
            cancelButton: vibe_course_module_strings.cancel
        });
        
    });
    $('body').delegate('.remove_course_package','click',function(){
        var $this = $(this);
        if($this.hasClass('disabled')){
            return false;
        }
        $this.addClass('disabled');
        
        $.confirm({
            text: wplms_front_end_messages.remove_course_package_confirm,
            confirm: function() {
                 $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'remove_course_package', 
                            security: $('body').find('#course_package').val(),
                            course_id:$('body').find('#course_id').val(),
                            package_path:$this.data('package-path'),
                            package_name:$this.data('package-name'),
                          },
                    cache: false,
                    success: function (html) {
                        $this.parent().remove();
                        $this.removeClass('disabled');
                    }
                });
            },
            cancel: function() {
                $this.removeClass('disabled');
            },
            confirmButton: wplms_front_end_messages.yes,
            cancelButton: vibe_course_module_strings.cancel
        });
        
    });
    $('body').delegate('.existing_packages_select','change',function(){
            $('body').find('a.view_package').attr('href',$('body').find('.existing_packages_select').val());
    });
    $.when($('#create_course_wrapper').append(saved_templates_dom)).then(function(){
        setTimeout(function(){
            var templates_wrapper_height = $('#create_course_templates_wrapper .container').outerHeight(true);
            $('#create_course_templates_wrapper_make_height').height(templates_wrapper_height);
        },500);
        if(typeof course_existing_template_id !='undefined' && course_existing_template_id != null){
            $('[data-id="'+course_existing_template_id+'"]').addClass('active'); 
        }
        $('body').delegate('.delete_course_template','click',function(){
            var $this = $(this);
            if($this.hasClass('disabled')){
                return false;
            }
            $this.addClass('disabled');
            $.confirm({
                text: wplms_front_end_messages.delete_this_template,
                confirm: function() {
                     $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: { action: 'delete_course_template',
                                security: $('#security').val(),
                                template_id:$this.parent().find('.course_template').data('id'),
                              },
                        cache: false,
                        success: function (html) {
                           $this.removeClass('disabled');
                           $this.closest('.course_template_item').fadeOut(200).remove();
                        }
                    });
                },
                cancel: function() {
                    $this.removeClass('disabled');
                },
                confirmButton: wplms_front_end_messages.yes,
                cancelButton: vibe_course_module_strings.cancel
            });
        });

        $('#create_fresh_course').on('click',function(){
            if(typeof course_curriculum_html != 'undefined' && course_curriculum_html != ''){
                $('body').find('#course_creation_tabs li.course_curriculum').html(course_curriculum_html);
            }
            if(typeof $('#course_id') != 'undefined' && $('#course_id').val() != 'undefined' && $('#course_id').val() !=null && $('#course_id').val() != '' ){
                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    async:true,
                    data: { action: 'remove_template_for_course', 
                            security: $('#security').val(),
                            course_id: $('#course_id').val(),
                          },
                    cache: false,
                    
                });

            }
            $.each($('body').find('.course_template'),function(){
                var ct = $(this);
                ct.removeClass('active');
            });
            $('body').find('#course_curriculum article .upload_course_wrapper').addClass('hide_cc_element');
            $('body').find('#course_curriculum article ul.course_curriculum').removeClass('hide_cc_element');
            $('#create_course_templates_wrapper').addClass('applied');

        });
        
        $('#upload_course').on('click',function(){

            $('#create_course_templates_wrapper').addClass('applied');
            if(typeof upload_course_json != 'undefined' && upload_course_json!= null)
                apply_settings(upload_course_json);
            $('body').find('#course_curriculum article ul.course_curriculum').addClass('hide_cc_element');
            $('body').find('#course_curriculum article .upload_course_wrapper').removeClass('hide_cc_element');
            var course_curriculum_html_uplod = '<i class="fa fa-upload"></i><a href="#course_curriculum">'+wplms_front_end_messages.upload_package+'<span></span></a>';
            $('body').find('#course_creation_tabs li.course_curriculum').html(course_curriculum_html_uplod);
            if($('body').find('#course_curriculum article .upload_course_wrapper').length <= 0 && typeof plupload_wrapper != 'undefined'){

                $('body').find('#course_curriculum article').append(plupload_wrapper);
                
                $('body').find('.existing_packages_select').select2();
                $('body').trigger('upload_course_trigger');
            }
            $('#create_course ul.create_course').append('<input type="hidden" data-id="vibe_course_template" data-type="meta" class="post_field course_template_id" value="upload_course">');
            if(typeof $('#course_id') != 'undefined' && $('#course_id').val() != null && $('#course_id').val() != 'undefined' && $('#course_id').val() != ''){
                set_template_for_course($('#course_id').val(),'upload_course');
            }
            $.each($('body').find('.course_template'),function(){
                var ct = $(this);
                ct.removeClass('active');
            });
            
        });
        $('.course_template').click(function(){
            var $this = $(this);
            $.confirm({
                text: wplms_front_end_messages.apply_template_confirm,
                confirm: function() {
                    $.each($('body').find('.course_template'),function(){
                        var ct = $(this);
                        ct.removeClass('active');
                    });
                    $this.addClass('active');
                    if(typeof course_curriculum_html != 'undefined' && course_curriculum_html != ''){
                        $('body').find('#course_creation_tabs li.course_curriculum').html(course_curriculum_html);
                    }
                    $('body').find('#course_curriculum article .upload_course_wrapper').addClass('hide_cc_element');
                    $('#create_course_templates_wrapper').addClass('applied');
                    template_id = $this.data('id');
                    var current_data = '';
                    if(typeof saved_course_templates != 'undefined' && saved_course_templates != null){
                        $.each(saved_course_templates,function(k,v){
                            if(k == template_id){
                               current_data = v;
                            }
                        }); 
                    }
                    
                    $('#create_course ul.create_course').append('<input type="hidden" data-id="vibe_course_template" data-type="meta" class="post_field course_template_id" value="'+template_id+'">');
                    if(typeof $('#course_id') != 'undefined' && $('#course_id').val() != null && $('#course_id').val() != 'undefined' && $('#course_id').val() != ''){
                        set_template_for_course($('#course_id').val(),template_id);
                    }
                    apply_settings(current_data.template_data);
                    if(typeof course_creation_template != 'undefined' && course_creation_template != null){
                        $.each(course_creation_template,function(k,v){
                           if($('.'+k).hasClass('active')){
                            $('.'+k).removeClass('active');
                            $('#'+k).removeClass('active');
                           }
                        });
                    }
                    $('div#course_creation_tabs li.create_course').addClass('active');
                    $('.edit_course_content #create_course').addClass('active');
                },
                cancel: function() {
                    $this.removeClass('disabled');
                },
                confirmButton: wplms_front_end_messages.yes,
                cancelButton: vibe_course_module_strings.cancel
            });
        });
    });
    
    $('a#create_course_templates_popop_button').on('click',function(){
        $('#create_course_templates_wrapper').toggleClass('applied');
    });
    $('#create_course_templates_wrapper_close').on('click',function(){
        $('#create_course_templates_wrapper').toggleClass('applied');
    });

    function control(field){

        return '<strong>'+field.label+'</strong><div class="checkbox"><input type="checkbox" name="save_cc_'+field.id+'" data-id="'+field.id+'" data-type="'+field.type+'" class="cc_field" value="1" id="save_cc_'+field.id+'"><label for="save_cc_'+field.id+'"></label></div>';
    } 
    
    $('#save_course_creation_template').on('click',function(){
        var template='';
        template = '<div id="save_cc_template_wrap"><span class="close_save_cc_template"><i class="fa fa-times fa-2"></i></span><h3 class="heading center"><span>'+wplms_front_end_messages.save_c_template+'</span></h3><p class="notice_to_save_cours_template">'+wplms_front_end_messages.c_template_name_message+'</p><ul >';
        $.each(course_creation_template,function(section_id,section){

            if(section_id != 'create_course' && section_id != 'course_live'){
                template += '<li class="section_li save_cc_template_'+section_id+'">';
                template +='<h3 class="heading section_title_e_d section_hide"><small></small><span>'+section.title;
                template += '<div class="checkbox"><input type="checkbox" class="save_cc_section_name" name="save_cc_'+section_id+'" data-id="'+section_id+'" value="1" id="save_cc_'+section_id+'"><label for="save_cc_'+section_id+'"></label></div></span></h3><ul class="save_cc_fields section_settings_hide">';
                $.each(section.fields,function(key,field){
                    if(field.type != 'button' && field.type != 'heading'){
                        template +='<li>'+control(field)+'</li>'
                    }
                });
                template += '</ul></li>';
            }
            
        });
        template +='</ul><div class="save_course_template_form"><input type="text" placeholder="'+wplms_front_end_messages.c_template_name+'" class="save_cc_template_name_field">\
        <textarea type="text" placeholder="'+wplms_front_end_messages.c_template_desc+'" class="save_cc_template_desc_field"></textarea></div>\
        <a class="button full save_cc_template">'+wplms_front_end_messages.save_c_template_button+'</a></div>';
        $('#course_live').append(template);
        $('[name^="save_cc"]').prop('checked','checked');
        $('#course_live').trigger('save_cc_dom_loaded');
    });
    $('body').delegate('.close_save_cc_template','click',function(){
        var close = $(this);
        close.parent().remove();
    });
    $('#course_live').on('save_cc_dom_loaded',function(){
        $('.save_cc_template').click(function(event){
            event.preventDefault();
            if($(this).hasClass('disabled_c_t'))
                return false;
            
            var settings = [];
            //section loop
            var sections = [];
            $('#save_cc_template_wrap').find('.section_li').each(function() {
                var $this = $(this);
                var section_fields = [];
                //fields loop
                $this.find('.cc_field').each(function(){

                    if(typeof $(this).val() != 'undefined' && $(this).is(':checked')){
                        var enabled = 1;
                    }else{
                        var enabled =0;
                    }
                    var temp_attr = $('.edit_course_content.content').find('#'+$(this).attr('data-id')+':not(.cc_field)').attr('data-uploader-button-text');
                    var field_selector = $('.edit_course_content.content').find('[data-id="'+$(this).attr('data-id')+'"]:not(.cc_field)');
                    if(field_selector.is('input') && !field_selector.is(':checkbox') && !field_selector.is(':radio')){
                        var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $('.edit_course_content.content').find('[data-id="'+$(this).attr('data-id')+'"]').val(),visibility:enabled};
                    }
                    else if(field_selector.is(':checkbox')){
                        if(field_selector.is(':checked')){
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: field_selector.val(),visibility:enabled};
                        }
                    }
                    
                    else if(field_selector.is('select')){
                        if(field_selector.hasClass('select2-hidden-accessible') && (field_selector.hasClass('select2') || field_selector.hasClass('chosen') ) ){
                             var data = {id:$(this).attr('data-id'),type: $(this).data('type'),value:field_selector.select2('data'),visibility:enabled};
                            }else{
                                var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: field_selector.val(),visibility:enabled};
                            }
                       
                    }
                    
                    else if(field_selector.is(':radio')){
                        var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value:$('.edit_course_content.content').find('[data-id="'+$(this).attr('data-id')+'"]:checked:not(.cc_field)').val(),visibility:enabled};
                    }
                    
                    else if($('.edit_course_content.content').find('#'+$(this).attr('data-id')).is('textarea')){
                        if($('.edit_course_content.content').find('#'+$(this).attr('data-id')).hasClass('wp-editor-area') && $('.edit_course_content.content').find('#'+$(this).attr('data-id')).attr('aria-hidden') == 'true'){
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: tinymce.editors[$(this).attr('data-id')].getContent(),visibility:enabled};  
                        }else{
                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $('.edit_course_content.content').find('#'+$(this).attr('data-id')).val(),visibility:enabled};        
                        }
                    }
                    else if(typeof temp_attr !== typeof undefined && temp_attr !== false){
                        var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $('.edit_course_content.content').find('#'+$(this).attr('data-id')).val(),visibility:enabled};
                    }else{
                        var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: field_selector.val(),visibility:enabled};
                    }   
                    section_fields.push(data);
                });
                //section json:
                if(typeof $this.find('.save_cc_section_name').val() != 'undefined' && $this.find('.save_cc_section_name').is(':checked')){
                    var sec_enabled = 1;
                }else{
                    var sec_enabled =0;
                }
                sections={section: $(this).find('.save_cc_section_name').attr('data-id'),'visibility': sec_enabled,'fields':section_fields};
                settings.push(sections);

            });
            console.log(settings);
            template_name = $('input.save_cc_template_name_field').val();
            template_desc = $('textarea.save_cc_template_desc_field').val();
            if(typeof template_name == 'undefined' || template_name == '' || template_name == null){
                alert(wplms_front_end_messages.warning_template_name);
                return false;
            }
            var old_text = $(this).text();
            $(this).text(wplms_front_end_messages.saving);
            $(this).addClass('disabled_c_t');

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'save_course_template', 
                        security: $('body').find('#security').val(),
                        template_name:template_name,
                        template_desc:template_desc,
                        template_data:JSON.stringify(settings),
                      },
                cache: false,
                success: function (html) {
                    $('.save_cc_template').text(html);
                    setTimeout(function(){
                        $('.save_cc_template').text(old_text);
                        $('.save_cc_template').removeClass('disabled_c_t');
                    },5000);
                }
            });

        });
        $('h3.heading.section_title_e_d small').on('click',function(){
            var $this = $(this);
            $this.closest('.section_li').find('.save_cc_fields').toggleClass('section_settings_hide');
            $this.toggleClass('section_hide');
        });
    });
});