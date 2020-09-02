;(function($) {
$.fn.timer = function( useroptions ){ 
    var $this = $(this), opt,newVal, count = 0; 

    opt = $.extend( { 
        // Config 
        'timer' : 300, // 300 second default
        'width' : 24 ,
        'height' : 24 ,
        'fgColor' : "#ED7A53" ,
        'bgColor' : "#232323" 
        }, useroptions 
    ); 
    $this.knob({ 
        'min':0, 
        'max': opt.timer, 
        'readOnly': true, 
        'width': opt.width, 
        'height': opt.height, 
        'fgColor': opt.fgColor, 
        'bgColor': opt.bgColor,                 
        'displayInput' : false, 
        'dynamicDraw': false, 
        'ticks': 0, 
        'thickness': 0.1 
    }); 
    setInterval(function(){ 
        newVal = ++count; 
        $this.val(newVal).trigger('change'); 
    }, 1000); 
};



$.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null){
       return null;
    }
    else{
       return results[1] || 0;
    }
}


function bp_course_generate_cookie(){
  var category_filter=[];
     $('.bp-course-category-filter:checked').each(function(){
        var category={'type':'course-cat','value':$(this).val()};
        category_filter.push(category);
     });

     $('.course_cat_nav li.current-cat,input.current-course-cat').each(function(){
        if(typeof $(this).attr('data-slug') != 'undefined'){
          var tax = 'course-cat';
          if($(this).attr('data-cat') != 'undefined'){
            tax = $(this).attr('data-cat');
          }
          var category={'type':tax,'value':$(this).attr('data-slug')};
          category_filter.push(category);
        }
     });

     $('.bp-course-date-filter').each(function(){
        if($(this).val().length){
          var date={'type':$(this).attr('data-type'),'value':$(this).val()};    
        }
        category_filter.push(date);
     });
     $('.bp-course-free-filter:checked').each(function(){
      var free={'type':'free','value':$(this).val()};
        category_filter.push(free);
     });
     $('.bp-course-offline-filter:checked').each(function(){
      var offline={'type':'offline','value':$(this).val()};
        category_filter.push(offline);
     });
     $('.bp-course-level-filter:checked').each(function(){
      var level={'type':'level','value':$(this).val()};
        category_filter.push(level);
     });
     $('.bp-course-location-filter:checked').each(function(){
      var location={'type':'location','value':$(this).val()};
        category_filter.push(location);
     });
     $('.bp-course-instructor-filter:checked').each(function(){
      var level={'type':'instructor','value':$(this).val()};
        category_filter.push(level);
     });
     $.cookie('bp-course-extras', JSON.stringify(category_filter), { expires: 1 ,path: '/'});
}

jQuery(document).ready(function($){

    if($('body').hasClass('directory') && $('body').hasClass('course')){
      $.cookie('bp-course-scope', 'all', {path: '/'});
      if($('body').hasClass('archive')){
        bp_course_generate_cookie();
        bp_filter_request( 'course', '', '', 'div.course','', 1,jq.cookie('bp-course-extras') );
      }else{
        bp_filter_request( 'course', '', '', 'div.course','', 1,'{}');  
      }
      
    }

    if($('body').hasClass('bp-user my-account course')){
      if($('body').hasClass('instructor-courses')){ 
        $.cookie('bp-course-scope', 'instructor', {path: '/'});
        $.cookie('bp-course-extras', '', {path: '/'});
        bp_filter_request( 'course', '', 'instructor', 'div.course','', 1,'{}');
      }else{
        $.cookie('bp-course-scope', 'personal', {path: '/'});
        $.cookie('bp-course-extras', '', {path: '/'});
        bp_filter_request( 'course', '', 'personal', 'div.course','', 1,'{}');
      }
    }
});

//Front End News Creation
jQuery(document).ready(function($){
    var default_save_text = $('#save_news_front_end').text();
    var news = $('body').find('#create_news');
    $('.create_news_front_end,.cancel_news_front_end').on('click',function(){
        if(news.hasClass('show')){
          news.removeClass('show');
          news.addClass('hide');
          news.find('.news_title').val('');
          news.find('.news_sub_title').val('');
          news.find('.news_format').val('post-format-0');
          tinyMCE.get('news_content').setContent('',{format:'raw'});
          $('body').find('#save_news_front_end').text(default_save_text);
          $('body').find('#save_news_front_end').attr('data-id','');
          $('body').find('.edit_news_front_end.active').parent().css('opacity','1');
          $('body').find('.edit_news_front_end.active').removeClass('active');
          return;
        }
        news.removeClass('hide');
        news.addClass('show');
        
        if(news) {
            $('html,body').animate({
              scrollTop: $(news).offset().top -150
            }, 500);
        }
    });
});

//Edit News from Front End
jQuery(document).ready(function($){
    var news = $('body').find('#create_news');
    $('.edit_news_front_end').on('click',function(){
        if(news.hasClass('show')){
          return;
        }
        if($(this).hasClass('active')){
          $(this).removeClass('active');
          return;
        }
        
        $(this).addClass('active');
        $(this).parent().css('opacity','0.5');
        var course_id = $(this).attr('data-id');
        var news_id = $(this).attr('data-news');

        //Ajax Call to fetch news data
        $.ajax({
          type: "POST",
          url: ajaxurl,
          dataType: 'json',
          data: { action: 'edit_news_front_end',
                  security:$('#news_security').val(),
                  id: course_id,
                  news: news_id,
                },
          cache: false,
          success: function (json) {
            news.find('.news_title').val(json.title);
            news.find('.news_sub_title').val(json.subtitle);
            news.find('.news_format').val(json.format);
            tinyMCE.get('news_content').setContent(json.content,{format:'raw'});
            $('body').find('.create_news_front_end').trigger('click');
            $('body').find('#save_news_front_end').text(json.text);
            $('body').find('#save_news_front_end').attr('data-id',news_id);
          }
        });
    });
});

//Save News From Front End
jQuery(document).ready(function($){
    $('#save_news_front_end').on('click',function(){
        $this = $('body').find('#create_news');
        var course_id = $this.attr('data-id');
        var news_title = $this.find('.news_title').val();
        var news_sub_title = $this.find('.news_sub_title').val();
        var news_format = $this.find('.news_format').val();
        var news_content =  tinyMCE.get('news_content').getContent({format:'raw'});
        var news_id = $(this).attr('data-id');

        //Ajax call for saving news
        $.ajax({
          type: "POST",
          url: ajaxurl,
          data: { action: 'save_news_front_end',
                  security:$('#news_security').val(),
                  id: course_id,
                  news_title: news_title,
                  news_sub_title: news_sub_title,
                  news_format: news_format,
                  news_content: news_content,
                  news: news_id,
                },
          cache: false,
          success: function () {
            location.reload(true);
          }
        });
    });
});

//Delete News from Front End
jQuery(document).ready(function($){
    var news = $('body').find('#create_news');
    $('.delete_news_front_end').on('click',function(){
        
        var course_id = $(this).attr('data-id');
        var news_id = $(this).attr('data-news');
        var text = $(this).attr('data-text');
        $.confirm({
          text: text,
          confirm: function() {
            //Ajax Call to delete news
            $.ajax({
              type: "POST",
              url: ajaxurl,
              dataType: 'json',
              data: { action: 'delete_news_front_end',
                      security:$('#news_security').val(),
                      id: course_id,
                      news: news_id,
                    },
              cache: false,
              success: function (json) {
                location.reload(true);
              }
            });
          },
          cancel: function() {
          },
          confirmButton: vibe_course_module_strings.confirm,
          cancelButton: vibe_course_module_strings.cancel
        });
    });
});

// Necessary functions
function runnecessaryfunctions(){
    if ($.isFunction($.fn.fitVids)) {
      jQuery('.fitvids').fitVids();
    }
    if (typeof tooltip !== 'undefined') {
      jQuery('.tip').tooltip();
    }
    
    jQuery('.nav-tabs li:first a').tab('show');
    jQuery('.nav-tabs li a').click(function(event){
        event.preventDefault();
        $(this).tab('show');
    });
    $( "#prev_results a" ).unbind( "click" );
    $('#prev_results a').on('click',function(event){
          event.preventDefault();
          $(this).toggleClass('show');
          $('.prev_quiz_results').toggleClass('show');
    });
    $( ".print_results" ).unbind( "click" );
    $('.print_results').on('click',function(event){
        event.preventDefault();
        $('.quiz_result').print();
    });

    $('.quiz_retake_form.start_quiz').on('click',function(e){
        e.preventDefault();
        var qid=$('#unit.quiz_title').attr('data-unit');
        $.ajax({
          type: "POST",
          url: ajaxurl,
          data: { action: 'retake_inquiz', 
                  security: $('#hash').val(),
                  quiz_id:qid,
                },
          cache: false,
          success: function (html) {
             $('a.unit[data-unit="'+qid+'"]').trigger('click');
             
             $('#unit'+qid).removeClass('done');
             $('#all_questions_json').each(function(){
                  var question_ids = $.parseJSON($(this).val());
                  $.each(question_ids,function(i,question_id){
                  localStorage.removeItem(question_id);
                  localStorage.removeItem('question_result_'+question_id);
                });
             });
             $('body').find('.course_progressbar').removeClass('increment_complete');
             $('body').find('.course_progressbar').trigger('decrement');
          }
        });
    });

    $('.wp-playlist').each(function(){
        return new WPPlaylistView({ el: this });
    });
    $('audio,video').each( function() { if($(this).parents('.flowplayer').length) return; if($(this).closest('.wp-playlist').length){return;}$(this).mediaelementplayer()   });
    jQuery('.gallery').magnificPopup({
            delegate: 'a',
            type: 'image',
            tLoading: 'Loading image #%curr%...',
            mainClass: 'mfp-img-mobile',
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                preload: [0,1] // Will preload 0 - before current, and 1 after the current image
            },
            image: {
                tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
                titleSrc: function(item) {
                return item.el.attr('title');
            }
        }
    });
    $('.open_popup_link').magnificPopup({
      type:'inline',
      midClick: true 
    });
    $('.ajax-popup-link').magnificPopup({
        type: 'ajax',
        alignTop: true,
        fixedContentPos: true,
        fixedBgPos: true,
        overflowY: 'auto',
        closeBtnInside: true,
        preloader: false,
        midClick: true,
        removalDelay: 300,
        mainClass: 'my-mfp-zoom-in'
    });
    $('.quiz_results_popup').magnificPopup({
        type: 'ajax',
        alignTop: true,
        ajax: {
          settings: {cache:false},
        },
        callbacks: {
                 parseAjax: function( mfpResponse ) {
                  mfpResponse.data = $(mfpResponse.data).find('.user_quiz_result');
                },
                ajaxContentAdded: function(){
                  
                  $('#prev_results a').on('click',function(event){
                        event.preventDefault();
                        $(this).toggleClass('show');
                        $('.prev_quiz_results').toggleClass('show');
                  });
                  $('.print_results').click(function(event){
                      event.preventDefault();
                      $('.quiz_result').print();
                  });
                  $('.quiz_retake_form.start_quiz').on('click',function(e){
                      e.preventDefault();
                      var qid=$('#unit.quiz_title').attr('data-unit');
                      $.ajax({
                          type: "POST",
                          url: ajaxurl,
                          data: { action: 'retake_inquiz', 
                                  security: $('#hash').val(),
                                  quiz_id:qid,
                                },
                          cache: false,
                          success: function (html) {
                             $('a.unit[data-unit="'+qid+'"]').trigger('click');
                             $.magnificPopup.close();
                             $('#unit'+qid).removeClass('done');
                             $('#all_questions_json').each(function(){
                                  var question_ids = $.parseJSON($(this).val());
                                  $.each(question_ids,function(i,question_id){
                                  localStorage.removeItem(question_id);
                                  localStorage.removeItem('question_result_'+question_id);
                                });
                             });
                             $('body').find('.course_progressbar').removeClass('increment_complete');
                             $('body').find('.course_progressbar').trigger('decrement');
                          }
                        });
                      
                  });
                }
              }
    });

    $(".live-edit").liveEdit({
          afterSaveAll: function(params) {
            return false;
          }
      });
    if ( typeof vc_js == 'function' ) { 
        window.vc_js();
      }

}


//Cookie evaluation
jQuery(document).ready( function($){

  $('.open_popup_link').magnificPopup({
    type:'inline',
    midClick: true 
  });
  $('.item-list').each(function(){
    var cookie_name = 'bp-'+$('.item-list').attr('id');
    var cookieValue = $.cookie(cookie_name);
    if ((cookieValue !== null) && cookieValue == 'grid') {      
      $('.item-list').addClass('grid');
      $('#list_view').removeClass('active');
      $('#grid_view').addClass('active');
    }
  });
  
   $('.curriculum_unit_popup').on('click',function(event){
      event.preventDefault();
      var $this = $(this);
      
      if(!$('#unit_load'+$this.attr('data-id')).length){

        $.ajax({
          type: "POST",
          url: ajaxurl,
          data: { action: 'get_unit_content', 
                  course_id: $this.attr('data-course'),
                  unit_id: $this.attr('data-id'),
                },
          cache: false,
          success: function (html) {
            $('body').append(html);
            runnecessaryfunctions();
            $('body').find('#unit_load'+$this.attr('data-id')).addClass('unit_content');
            $('body').trigger('unit_load'+$this.attr('data-id'));      
          }
        });

      }else{
        $('body').trigger('unit_load'+$this.attr('data-id'));
      } 

      $('body').on('unit_load'+$this.attr('data-id'),function(){
          $.magnificPopup.open({
              items: {
                  src: '#unit_load'+$this.attr('data-id')
              },
              type: 'inline',
              callbacks:{
                open: function(){
                  $('.unit_content').trigger('unit_traverse');
                  $('body').trigger('unit_loaded');
                }
              }
          });
      });
   });
  $('.shop_table.order_details dl.variation').each(function(){ 
    $("[class^=variation-commission]").hide();
  });
  $('.unit_content').on('unit_traverse',function(){
    $('.accordion').each(function(){
      if($(this).hasClass('load_first')){
        $(this).find('.accordion-group:first-child .accordion-toggle').trigger('click');
      }
    });
  });
  
  $('.datepicker').each(function(){
    $(this).datepicker({dateFormat: 'yy-mm-dd'});
  });

function bp_course_extras_cookies(){
  $('.bp-course-category-filter,.bp-course-free-filter,.bp-course-level-filter,.bp-course-location-filter,.bp-course-instructor-filter,.bp-course-date-filter,.bp-course-offline-filter').on('change',function(){
     bp_course_generate_cookie();
  });
}

$('.course_cat_nav li').each(function(){
    if($(this).hasClass('current-cat')){
        if(typeof $(this).attr('data-slug') != 'undefined' && $(this).attr('data-slug').length){
            bp_course_generate_cookie();
        }
    }
});
 
jQuery(document).ready(function($){
 
  $('.course_pursue_panel').each(function(){
    var course_pursue_panel = $(this);
    var wheight = $(window).height();
    course_pursue_panel.css('height',wheight+'px');
    var viewportWidth = $(window).width();
    if (viewportWidth < 768) {
      $("body").addClass("course_pursue_panel_hide");
    }else{
      $("body").removeClass("course_pursue_panel_hide");
    }  
  });
  $('#hideshow_course_pursue_panel').on('click',function(){
    $('body').toggleClass('course_pursue_panel_hide');
  });

  //close timeline on load mobile
  $(window).load(function(){
    var viewportWidth = $(window).width();
    if (viewportWidth < 768) {
      $('.unit_content').on('unit_traverse',function(){
        $("body").addClass("course_pursue_panel_hide");
      });
    }
  });

  $(window).on("resize", function() {
      var viewportWidth = $(window).width();
      if (viewportWidth < 768) {
        $("body").addClass("course_pursue_panel_hide");
        //close timeline on mobile on resize
        $('.unit_content').on('unit_traverse',function(){
          $("body").addClass("course_pursue_panel_hide");
        });
      }else{
        $("body").removeClass("course_pursue_panel_hide");
      }
  });
});

function bp_course_category_filter_cookie(){

    var category_filter_cookie =  $.cookie("bp-course-extras");

    if (typeof category_filter_cookie !== "undefined" && (category_filter_cookie !== null) ) { 
        var category_filter = JSON.parse(category_filter_cookie);
        if(typeof category_filter != 'object'){
          return;
        }
        $('#active_filters').remove();
        if($('#active_filters').length){
          $('#active_filters').fadeIn(200);
        }else{
          $('#course-dir-list').before('<ul id="active_filters"><li>'+vibe_course_module_strings.active_filters+'</li></ul>');
        }
        //Detect and activate specific filters
        jQuery.each(category_filter, function(index, item) {
            if(item !== null){
              
                if($('input[data-type="'+item['type']+'"]').attr('type') == 'text'){
                 $('input[data-type="'+item['type']+'"]').val(item['value']);
                  var id = $('input[data-type="'+item['type']+'"]').attr('data-type');
                  var text = $('input[data-type="'+item['type']+'"]').attr('placeholder')+' : '+item['value'];
                }else{
                  $('input[value="'+item['value']+'"]').prop('checked', true);
                  var id = $('input[value="'+item['value']+'"]').attr('id');
                  var text = $('label[for="'+id+'"]').text();
                }
              
                if(!$('#active_filters span[data-id="'+id+'"]').length && text.length){
                  $('#active_filters').append('<li><span data-id="'+id+'">'+text+'</span></li>');
                }

            }
        });
        // Delete a specific filter
        $('#active_filters li span').on('click',function(){
           var id = $(this).attr('data-id');
           $(this).parent().fadeOut(200,function(){
            $(this).remove();
            $('#loader_spinner').remove();
            if($('#active_filters li').length < 3)
              $('#active_filters').fadeOut(200);
            else    
              $('#active_filters').fadeIn(200);
          });
           if($('#'+id).length){
              if($('#'+id).attr('type') == 'checkbox'){
                $('#'+id).prop('checked',false);     
              }
              if($('#'+id).attr('type') == 'radio'){
                $('#'+id).prop('checked',false);     
              }
              if($('#'+id).attr('type') == 'text'){
                $('#'+id).val('');
              }
           }

           
           /*===== */ 
           
           var category_filter=[];
           $('.bp-course-category-filter:checked').each(function(){
              var category={'type':'course-cat','value':$(this).val()};
              category_filter.push(category);
           });
           $('.bp-course-free-filter:checked').each(function(){
            var free={'type':'free','value':$(this).val()};
              category_filter.push(free);
           });
           $('.bp-course-offline-filter:checked').each(function(){
            var offline={'type':'offline','value':$(this).val()};
              category_filter.push(offline);
           });
           $('.bp-course-level-filter:checked').each(function(){
            var level={'type':'level','value':$(this).val()};
              category_filter.push(level);
           });
           $('.bp-course-location-filter:checked').each(function(){
            var location={'type':'location','value':$(this).val()};
              category_filter.push(location);
           });
           $('.bp-course-instructor-filter:checked').each(function(){
            var level={'type':'instructor','value':$(this).val()};
              category_filter.push(level);
           });
           $.cookie('bp-course-extras', JSON.stringify(category_filter), { expires: 1 ,path: '/'});

           $('.course_filters').trigger('course_filter');
           /* ==== */
        });

        if(!$('#active_filters .all-filter-clear').length)
            $('#active_filters').append('<li class="all-filter-clear">'+vibe_course_module_strings.clear_filters+'</li>');

        // Clear all Filters link
        $('#active_filters li.all-filter-clear').click(function(){
            $('#loader_spinner').remove();
            $('#active_filters li').each(function(){
              var span = $(this).find('span');
               var id = span.attr('data-id');
               span.parent().fadeOut(200,function(){
                  $(this).remove(); });
               if($('#'+id).attr('type') == 'text'){
                 $('#'+id).val('');
               }else{
                  $('#'+id).prop('checked',false);
               }
              $('#active_filters').fadeOut(200,function(){
                $(this).remove();
              });   
              $.removeCookie('bp-course-extras', { path: '/' });
              $('.course_filters').trigger('course_filter');
              //$('#submit_filters').trigger('click');
            });
        });
        // End Clear All
           // Hide is no filter active
        if($('#active_filters li').length < 3){
          $('#active_filters').fadeOut(200);
        }else{
          $('#active_filters').fadeIn(200);
        }    
    }
}

bp_course_extras_cookies();
bp_course_category_filter_cookie();


  if($('.course_filters').hasClass('auto_click')){
    $('.course_filters input').on('change',function(event){ 
      var jq = jQuery;

      $('#loader_spinner').remove();
      $(this).append('<i id="loader_spinner" class="fa fa-spinner spin loading animation cssanim"></i>');
      if ( $('.item-list-tabs li.selected').length ){
        var el = $('.item-list-tabs li.selected');
      }else{
        $('#course-all').addClass('selected');
        var el = $('#course-all');
      }

      var css_id = el.attr('id').split('-');
      var object = css_id[0];
      var scope = css_id[1];
      var filter = jq(this).val();
      var search_terms = false;

      if ( jq('.dir-search input').length )
        search_terms = jq('.dir-search input').val();

      if ( 'friends' == object )
        object = 'members';

      bp_course_extras_cookies();
      bp_filter_request( object, filter, scope, 'div.' + object, search_terms, 1, jq.cookie('bp-' + object + '-extras') );
      bp_course_category_filter_cookie();
        jq('#buddypress').on('bp_filter_request',function(){
          $('#loader_spinner').remove();
      });
    });
  }
  

/*=========================================================================*/

  $('.category_filter li > span,.category_filter li > label').click(function(event){
    var parent= $(this).parent();
    $(this).parent().find('span').toggleClass('active');
    parent.find('ul.sub_categories').toggle(300);
  });
  
  $('#submit_filters').on('click',function(event){ 
      var jq = jQuery;


      $('#loader_spinner').remove();
      $(this).append('<i id="loader_spinner" class="fa fa-spinner spin loading animation cssanim"></i>');
      
      $('.course_filters').trigger('course_filter');

      return false;
  });

  $('.course_filters').on('course_filter',function(){
      var jq = jQuery;
      if ( $('.item-list-tabs li.selected').length ){
        var el = $('.item-list-tabs li.selected');
      }else{
        $('#course-all').addClass('selected');
        var el = $('#course-all');
      }
      var css_id = el.attr('id').split('-');
      var object = css_id[0];
      var scope = css_id[1];
      var filter = jq(this).val();
      var search_terms = false;

      if ( jq('.dir-search input').length )
        search_terms = jq('.dir-search input').val();

      if ( 'friends' == object )
        object = 'members';

      bp_course_extras_cookies();
      bp_filter_request( object, filter, scope, 'div.' + object, search_terms, 1, jq.cookie('bp-' + object + '-extras') );
      bp_course_category_filter_cookie();
      jq('#buddypress').on('bp_filter_request',function(){
        $('#loader_spinner').remove();
      });

  });
  /*===== Quiz Results Popup ===*/
  $('.quiz_results_popup').magnificPopup({
      type: 'ajax',
      alignTop: true,
      ajax: {
        settings: {cache:false},
      },
      callbacks: {
          parseAjax: function( mfpResponse ) {
                mfpResponse.data = $(mfpResponse.data).find('#item-body');
              },
          ajaxContentAdded: function() {        
                $('#prev_results a').on('click',function(event){
                    event.preventDefault();
                    $(this).toggleClass('show');
                    $('.prev_quiz_results').toggleClass('show');
                });
                $('.print_results').click(function(event){
                    event.preventDefault();
                    $('.quiz_result').print();
                });
                $('.quiz_retake_form.start_quiz').on('click',function(e){
                    e.preventDefault();
                    var qid=$('#unit.quiz_title').attr('data-unit');
                    $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: { action: 'retake_inquiz', 
                                security: $('#hash').val(),
                                quiz_id:qid,
                              },
                        cache: false,
                        success: function (html) {
                           $('a.unit[data-unit="'+qid+'"]').trigger('click');
                           $.magnificPopup.close();
                           $('#unit'+qid).removeClass('done');
                           $('body').find('.course_progressbar').removeClass('increment_complete');
                           $('body').find('.course_progressbar').trigger('decrement');
                        }
                      });
                    
                });
            }
      }      
  });    
  $('#grid_view').click(function(){
    if(!$('.item-list').hasClass('grid')){
      $('.item-list').addClass('grid');
    }
    var cookie_name = 'bp-'+$('.item-list').attr('id');
    $.cookie(cookie_name, 'grid', { expires: 2 ,path: '/'});
    $('#list_view').removeClass('active');
    $(this).addClass('active');
  });
  $('#list_view').click(function(){
    $('.item-list').removeClass('grid');
    var cookie_name = 'bp-'+$('.item-list').attr('id');
    $.cookie(cookie_name, 'list', { expires: 2 ,path: '/'});
    $('#grid_view').removeClass('active');
    $(this).addClass('active');
  });
  $('.dial').each(function(){
    $(this).knob({
        'readOnly': true, 
        'width': 120, 
        'height': 120, 
        'fgColor': vibe_course_module_strings.theme_color, 
        'bgColor': '#f6f6f6',   
        'thickness': 0.1
    });
  });

  $('#apply_course_button').on('click',function(){
    var $this = $(this);
    var default_html = $this.html();
    $this.html('<i class="fa fa-spinner animated spin"></i>');
      $.confirm({
          text: vibe_course_module_strings.confirm_apply,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'apply_for_course',
                            security: $this.attr('data-security'),
                            course_id:$this.attr('data-id'),
                          },
                    cache: false,
                    success: function (html) {
                        $this.html(html);
                    }
            });
          },
          cancel: function() {
              $this.html(default_html);
          },
          confirmButton: vibe_course_module_strings.confirm,
          cancelButton: vibe_course_module_strings.cancel
      });
  });

$('#applications ul li span').on('click',function(){
  var $this = $(this);
  var action = 'reject';
  if($this.hasClass('approve')){
    action = 'approve';
  }
  $this.addClass('loading');
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'manage_user_application',
                act:action,
                security: $this.parent().attr('data-security'),
                user_id:$this.parent().attr('data-id'),
                course_id:$this.parent().attr('data-course'),
              },
        cache: false,
        success: function (html) {
            $this.removeClass('loading');
            $this.addClass('active');
            setTimeout(function(){$this.parent().remove(); }, 1000);
        }
    });
});

  //RESET Ajx
$( 'body' ).delegate( '.remove_user_course','click',function(event){
      event.preventDefault();
      var course_id=$(this).attr('data-course');
      var user_id=$(this).attr('data-user');
      $(this).addClass('animated spin');
      var $this = $(this);
      $.confirm({
          text: vibe_course_module_strings.remove_user_text,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'remove_user_course',
                            security: $('#bulk_action').val(),
                            id: course_id,
                            user: user_id
                          },
                    cache: false,
                    success: function (html) {
                        $(this).removeClass('animated');
                        $(this).removeClass('spin');
                        runnecessaryfunctions();
                        $('#message').html(html);
                        $('#s'+user_id).fadeOut('fast');
                    }
            });
          },
          cancel: function() {
              $this.removeClass('animated');
              $this.removeClass('spin');
          },
          confirmButton: vibe_course_module_strings.remove_user_button,
          cancelButton: vibe_course_module_strings.cancel
      });
  });

$( 'body' ).delegate( '.reset_course_user','click',function(event){
      event.preventDefault();
      var course_id=$(this).attr('data-course');
      var user_id=$(this).attr('data-user');
      $(this).addClass('animated spin');
      var $this = $(this);
      $.confirm({
        text: vibe_course_module_strings.reset_user_text,
          confirm: function() {
          $.ajax({
                  type: "POST",
                  url: ajaxurl,
                  data: { action: 'reset_course_user', 
                          security: $('#bulk_action').val(),
                          id: course_id,
                          user: user_id
                        },
                  cache: false,
                  success: function (html) {
                      $this.removeClass('animated');
                      $this.removeClass('spin');

                      var cookie_id = 'course_progress'+course_id;
                      $.removeCookie(cookie_id,{ path: '/' });

                      $('#message').html(html);
                  }
          });
         }, 
         cancel: function() {
              $this.removeClass('animated');
              $this.removeClass('spin');
          },
          confirmButton: vibe_course_module_strings.reset_user_button,
          cancelButton: vibe_course_module_strings.cancel
        });
  });

  
$( 'body' ).delegate( '.tip.course_stats_user', 'click', function(event){
      event.preventDefault();
      var $this=$(this);
      var course_id=$this.attr('data-course');
      var user_id=$this.attr('data-user');
      
      if($this.hasClass('already')){
        $('#s'+user_id).find('.course_stats_user').fadeIn('fast');
      }else{
          $this.addClass('animated spin');    
        $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'course_stats_user', 
                        security: $('#bulk_action').val(),
                        id: course_id,
                        user: user_id
                      },
                cache: false,
                success: function (html) {
                    $this.removeClass('animated');
                    $this.removeClass('spin');
                    $this.addClass('already');
                    $('#s'+user_id).append(html);
                    $('.course_students').trigger('load_quiz_results');
                    $(".dial").knob({
                      'readOnly': true, 
                      'width': 160, 
                      'height': 160, 
                      'fgColor': vibe_course_module_strings.theme_color, 
                      'bgColor': '#f6f6f6',   
                      'thickness': 0.1 
                    });

                    $('#s'+user_id+' .curriculum_check li span').click(function(){
                      var $span = $(this);
                      var action;
                      var text;
                      if($(this).hasClass('done')){
                        action = 'instructor_uncomplete_unit';
                        text = vibe_course_module_strings.instructor_uncomplete_unit;
                      }else{
                        action = 'instructor_complete_unit';
                        text = vibe_course_module_strings.instructor_complete_unit;
                      }

                      $.confirm({
                            text: text,
                            confirm: function() {
                            $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                async: true,
                                data: { action: action, 
                                        security: $('#bulk_action').val(),
                                        course_id: course_id,
                                        id:$span.attr('data-id'),
                                        user_id: user_id
                                      },
                                cache: false,
                                success: function (html) {
                                  console.log(html);
                                  if($span.hasClass('done')){
                                    $span.removeClass('done');
                                  }else{
                                    $span.addClass('done');
                                  }
                                }
                            });
                        }, 
                         cancel: function() {
                          },
                          confirmButton: vibe_course_module_strings.confirm,
                          cancelButton: vibe_course_module_strings.cancel
                        });
                    }); // End span click
                }
        });
      }
  });
  
$('.course_students').on('load_quiz_results',function(){
    $('.check_user_quiz_results').click(function(){
        $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action    : 'check_user_quiz_results',
                        quiz      : $(this).attr('data-quiz'),
                        user      : $(this).attr('data-user'),
                        course_id : $('#course_user_ajax_search_results').attr('data-id'),
                        security  : $('#bulk_action').val()
                      },
                cache: false,
                success: function (html) {
                    //$('.check_user_quiz_results').append('<div class="quiz_results_wrapper hide">'+html+'</div>');
                    $.magnificPopup.open({
                        items: {
                            src: $('<div id="item-body">'+html+'</div>'),
                            type: 'inline'
                        }
                    });
                    $('.print_results').click(function(event){
                        event.preventDefault();
                        $('.quiz_result').print();
                    });
                }
        });
    });
});
  
  $('body').delegate('.data_stats li','click',function(event){
    event.preventDefault();
    var defaultxt = $(this).html();
    var content = $('.main_content');
    if($('.main_unit_content.in_quiz') && $('.main_unit_content.in_quiz').length){
      var content = $('.main_unit_content.in_quiz') ;
    }
    var $this = $(this);
    var id = $(this).attr('id');

    if(id == 'desc'){
      content.show();
      $('.stats_content').hide();
    }else{
      if($(this).hasClass('loaded')){
        content.hide();
        $('.stats_content').show();
      }else{
         $this.addClass('loaded');  
         content.hide();
         $(this).html('<i class="fa fa-spinner"></i>');
         var quiz_id = $this.parent().attr('data-id');
         var cpttype = $this.parent().attr('data-type');
         $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'load_stats', 
                        cpttype: cpttype,
                        id: quiz_id
                      },
                cache: false,
                success: function (html) {

                  content.after(html);
                  console.log(cptchatjs);
                  
                      $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        dataType: 'json',
                        data: { action: 'cpt_stats_graph', 
                                cpttype: cpttype,
                                id: quiz_id
                              },
                        cache: false,
                        success: function (json) {
                          console.log('loading cpt stats')

                          jQuery.getScript(cptchatjs).done(function(){
                            console.log('loade');
                              new Chart(document.getElementById("stats_chart"),
                                {
                                  "type":"doughnut",
                                  "data":{
                                    "labels":json.labels,
                                    "datasets":[
                                      { 
                                        "label":"My First Dataset",
                                        "data":json.data,
                                        "backgroundColor":["rgb(255, 99, 132)","rgb(54, 162, 235)","rgb(255, 205, 86)","rgb(112, 201, 137)"]
                                      }
                                    ]
                                  }
                                });
                            })
                        }

                      });

                  $('#load_more_cpt_user_results').on('click',function(){
                    var loadmore = $(this);

                    if(loadmore.hasClass('loading'))
                      return;

                    $(this).addClass('loading');

                      $.ajax({
                      type: "POST",
                      url: ajaxurl,
                      data: { action: 'load_more_stats', 
                              cpttype: cpttype,
                              id: quiz_id,
                              starting_point:loadmore.attr('data-starting_point')
                            },
                      cache: false,
                      success:function(html){
                        loadmore.removeClass('loading');
                        loadmore.hide(200);
                        $('.stats_content ol.marks').append(html);  
                      }
                    });
                  });    
                  
                  setTimeout(function(){$this.html(defaultxt); }, 1000);
                }
        });
      }
    }
    $this.parent().find('.active').removeClass('active');
    $this.addClass('active');
  });

  $('#calculate_avg_course').click(function(event){
      event.preventDefault();
      var course_id=$(this).attr('data-courseid');
      $(this).addClass('animated spin');

      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'calculate_stats_course', 
                      security: $('#security').val(),
                      id: course_id
                    },
              cache: false,
              success: function (html) {
                  $(this).removeClass('animated');
                  $(this).removeClass('spin');
                  $('#message').html(html);
                   setTimeout(function(){location.reload();}, 3000);
              }
      });

  });


$('.course.submissions #quiz,.course.submissions #course').on('loaded',function(){
    $('.tip').tooltip();
});

$( 'body' ).delegate( '.reset_quiz_user', 'click', function(event){
  event.preventDefault();
  var course_id=$(this).attr('data-quiz');
  var user_id=$(this).attr('data-user');
  $(this).addClass('animated spin');
  var $this = $(this);
  $.confirm({
      text: vibe_course_module_strings.quiz_reset,
      confirm: function() {

  $.ajax({
          type: "POST",
          url: ajaxurl,
          data: { action: 'reset_quiz', 
                  security: $('#qsecurity').val(),
                  id: course_id,
                  user: user_id
                },
          cache: false,
          success: function (html) {
              $(this).removeClass('animated');
              $(this).removeClass('spin');
              $('#message').html(html);
              $('#qs'+user_id).fadeOut('fast');
          }
  });
  }, 
   cancel: function() {
        $this.removeClass('animated');
        $this.removeClass('spin');
    },
    confirmButton: vibe_course_module_strings.quiz_rest_button,
    cancelButton: vibe_course_module_strings.cancel
  });
});

$( 'body' ).delegate( '.evaluate_quiz_user', 'click', function(event){
  event.preventDefault();
  var quiz_id=$(this).attr('data-quiz');
  var user_id=$(this).attr('data-user');
  $(this).addClass('animated spin');

  $.ajax({
          type: "POST",
          url: ajaxurl,
          data: { action: 'evaluate_quiz', 
                  security: $('#qsecurity').val(),
                  id: quiz_id,
                  user: user_id
                },
          cache: false,
          success: function (html) {
              $(this).removeClass('animated');
              $(this).removeClass('spin');
              $('.quiz_students').html(html);
              calculate_total_marks();
              $('#total_marks>strong>span').on('click',function(){
                var $this = $(this);$('#set_quiz_marks').remove();
                 $('#total_marks').append('<input type="number" id="set_quiz_marks">');
                 $('#set_quiz_marks').on('blur',function(){
                  var val = $(this).val();
                    $this.text(val);
                    $(this).remove();
                 });
              });
          }
  });
});

$( 'body' ).delegate( '.evaluate_course_user', 'click', function(event){
    event.preventDefault();
    var course_id=$(this).attr('data-course');
    var user_id=$(this).attr('data-user');
    $(this).addClass('animated spin');

    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'evaluate_course', 
                    security: $('#security').val(),
                    id: course_id,
                    user: user_id
                  },
            cache: false,
            success: function (html) {
                $(this).removeClass('animated');
                $(this).removeClass('spin');
                $('.course_students').html(html);
                calculate_total_marks();
            }
    });
});



$( 'body' ).delegate( '.reset_answer', 'click', function(event){
       event.preventDefault();
      var ques_id=$('#comment-status').attr('data-quesid');
      var $this = $(this);
      var qid = $('#comment-status').attr('data-quesid');
      $this.prepend('<i class="fa fa-spinner animated spin"></i>');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'reset_question_answer', 
                      security: $this.attr('data-security'),
                      ques_id: ques_id,
                    },
              cache: false,
              success: function (html) {
                  $this.find('i').remove();
                   $('#comment-status').html(html);
                   $('#ques'+qid).removeClass('done');
                   setTimeout(function(){ $this.addClass('hide');}, 500);
              }
      });
});

$( 'body' ).delegate( '#course_complete', 'click', function(event){
      event.preventDefault();
      var $this=$(this);
      var user_id=$this.attr('data-user');
      var course = $this.attr('data-course');
      var marks = parseInt($('#course_marks_field').val());
      if(marks <= 0){
        alert('Enter Marks for User');
        return;
      }

      $this.prepend('<i class="fa fa-spinner animated spin"></i>');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'complete_course_marks', 
                      security: $('#security').val(),
                      course: course,
                      user: user_id,
                      marks:marks
                    },
              cache: false,
              success: function (html) {
                  $this.find('i').remove();
                  $this.html(html);
              }
      });
});

  // Registeration BuddyPress
  $('.register-section h4').click(function(){
      $(this).toggleClass('show');
      $(this).parent().find('.editfield').toggle('fast');
  });

});

$( 'body' ).delegate( '.hide_parent', 'click', function(event){
  $(this).parent().fadeOut('fast');
});


$( 'body' ).delegate( '.give_marks', 'click', function(event){
      event.preventDefault();
      var $this=$(this);
      var ansid=$this.attr('data-ans-id');
      var aval = $('#'+ansid).val();
      $this.prepend('<i class="fa fa-spinner animated spin"></i>');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'give_marks', 
                      //security:
                      //qid:  
                      aid: ansid,
                      aval: aval
                    },
              cache: false,
              success: function (html) {
                  $this.find('i').remove();
                  $this.html(vibe_course_module_strings.marks_saved);
              }
      });
});

$( 'body' ).delegate( '#mark_complete', 'click', function(event){
    event.preventDefault();
    var $this=$(this);
    var quiz_id=$this.attr('data-quiz');
    var user_id = $this.attr('data-user');
    var marks = parseInt($('#total_marks strong > span').text());
    $this.prepend('<i class="fa fa-spinner animated spin"></i>');

    tinyMCE.triggerSave();

    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'save_quiz_marks', 
                    quiz_id: quiz_id,
                    user_id: user_id,
                    marks: marks,
                    remarks:$('#quiz_remarks').val()
                  },
            cache: false,
            success: function (html) {
                $this.find('i').remove();
                $this.html(vibe_course_module_strings.quiz_marks_saved);
            }
    });
});

function calculate_total_marks(){
  $('.question_marks').on('keyup',function(){
      var marks=parseInt(0);
      var $this = $('#total_marks strong > span');
      $('.question_marks').each(function(){
          if($(this).val())
            marks = marks + parseInt($(this).val());
        });
      $this.html(marks);
  });
}


$( 'body' ).delegate( '.submit_quiz', 'click', function(event){
    event.preventDefault();
    $('#question').css('opacity',0.2);
    $('#ajaxloader').removeClass('disabled');
    if($(this).hasClass('disabled') || $(this).hasClass('activity-item')){
      return false;
    }
    var $this = $(this);

    
  $this.prepend('<i class="fa fa-spinner animated spin"></i>');
  $('#question').addClass('quiz_submitted_fade');

  var quiz_id=$(this).attr('data-quiz');  
  var answers=[];
  var unanswered_flag=0;
  var security = $('#start_quiz').val();
  if(typeof all_questions_json !== 'undefined'){
    $.each(all_questions_json, function(key, val) {
        var ans = localStorage.getItem(val);
        localStorage.removeItem('question_result_'+val);
        if(ans){
          var answer={'id':val,'value':ans};
          answers.push(answer); 
        }else{
          unanswered_flag++;
        }
    });
  }

  if(unanswered_flag){
      $.confirm({
        text: vibe_course_module_strings.unanswered_questions,
        confirm: function() {
          $.confirm({
            text: vibe_course_module_strings.submit_quiz_notification,
            confirm: function() {
              //Add progress bar
              //run a recursive option
              //set submit quiz status
              wplms_submit_quiz(quiz_id,security);
            },
            cancel: function() {
              $('#question').css('opacity',1);
              $('#ajaxloader').addClass('disabled');
            },
            confirmButton: vibe_course_module_strings.confirm,
            cancelButton: vibe_course_module_strings.cancel
          });
        },
        cancel: function() {
          $('#question').css('opacity',1);
          $('#ajaxloader').addClass('disabled');
          $this.remove('.fa');
          return false;
        },
        confirmButton: vibe_course_module_strings.confirm,
        cancelButton: vibe_course_module_strings.cancel
    });
  }else{
    $.confirm({
        text: vibe_course_module_strings.submit_quiz_notification,
        confirm: function() {
           wplms_submit_quiz(quiz_id,security);
        },
        cancel: function() {
          $('#question').css('opacity',1);
          $this.remove('.fa');
          $('#ajaxloader').addClass('disabled');
        },
        confirmButton: vibe_course_module_strings.confirm,
        cancelButton: vibe_course_module_strings.cancel
      });
  } 
});

function wplms_submit_quiz(quiz_id,security){


    $('#ajaxloader').removeClass('disabled');
    $('#ajaxloader').append('<div class="submit_quiz_progress"><span class="save_progress_wrap"><span class="save_progress_label"></span><span class="save_progress_bar"><span class="save_progress_inner"></span></span></span></div>');
    $('body').append('<div id="fullbody_mask"></div>');
    var defferred =[];
    var current = 0;
    var per = 0;
    if(all_questions_json.length){
        $.each(all_questions_json,function(i,item){
          var value = localStorage.getItem(item);
          if(value !=null){
              var data = {action: 'save_question_marked_answer',security:security,quiz_id: quiz_id,question_id:item,answer:value};
              defferred.push(data);
          }else{
              per++;
          }
          if(all_questions_json.length == per){
              end_quiz_submission(quiz_id);
          }
        });
      save_question_answer(current,defferred,per,'submit');
    }else{
       end_quiz_submission(quiz_id);
    }

    $('body').on('all_question_answers_saved',function(event,data){
      if(data.trigger == 'submit'){
        end_quiz_submission(quiz_id);
      }
    });
}


function end_quiz_submission(quiz_id){

    $.ajax({
            type: "POST",
            url: ajaxurl,
            async: true,
            data: { action: 'submit_quiz', 
                    security: $('#start_quiz').val(),
                    quiz_id:quiz_id,
                    },
            cache: false,
            success: function (html) {
                $('#content').append(html);
                $('#fullbody_mask').remove();
                if(typeof all_questions_json !== 'undefined'){
                    $.each(all_questions_json, function(key, val) {
                        localStorage.removeItem(val);
                        localStorage.removeItem('question_result_'+val);
                    });
                }
                window.location.assign(document.URL);
            }
        });
}

function wplms_submit_inquiz(){

    if($('.unit_button').hasClass('quiz_results_popup')){ // Quiz already submitted
        return;
    }

    $('#ajaxloader').removeClass('disabled');
    $('#ajaxloader').append('<div class="submit_quiz_progress"><span class="save_progress_wrap"><span class="save_progress_label"></span><span class="save_progress_bar"><span class="save_progress_inner"></span></span></span></div>');
    $('body').append('<div id="fullbody_mask"></div>');
    var defferred =[];
    var current = 0;
    var per = 0;
    var quiz_id = $('#unit.quiz_title').attr('data-unit');
    var security = $('#hash').val();
    $.each(all_questions_json,function(i,item){
        var value = localStorage.getItem(item);
        if(value !=null){
            var data = {action: 'save_question_marked_answer',security:security,quiz_id: quiz_id,question_id:item,answer:value};
            defferred.push(data);
        }else{
            per++;
        }
        var width = 100*(per/all_questions_json.length);
        $('.save_progress_inner').css('width',width+'%');

        if(all_questions_json.length == per){
            end_inquiz_submission();
        }
    });

    save_question_answer(current,defferred,per,'submit');

    $('body').on('all_question_answers_saved',function(event,data){
      console.log(' data ='+data.trigger);
        if(data.trigger == 'submit'){
          end_inquiz_submission(); 
        }        
    });
}

function end_inquiz_submission(){
    
    var $this = $('.submit_inquiz');
    if($this.hasClass('processing'))
        return;

    $this.addClass('processing');
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'in_submit_quiz', 
              security: $('#hash').val(),
              quiz_id:$('#unit.quiz_title').attr('data-unit'),
            },
        cache: false,
        success: function (html) {
            $('#fullbody_mask').remove();
            $('#ajaxloader').addClass('disabled');
            $('#ajaxloader').remove('.submit_quiz_progress');
            $('#unit_content').removeClass('loading');
            $this.removeClass('processing');

            html = $.trim(html);
            if(html.indexOf('##') > 0){
                var nextunit = html.substr(0, html.indexOf('##')); 
                html = html.substr((html.indexOf('##')+2));
                if(nextunit.length>0){
                    $('#next_unit').removeClass('hide');
                    $('#next_unit').attr('data-unit',nextunit);  
                    $('#next_quiz').removeClass('hide');
                    $('#next_quiz').attr('data-unit',nextunit); 
                    $('#unit'+nextunit).find('a').addClass('unit');
                    $('#unit'+nextunit).find('a').attr('data-unit',nextunit);
                }
            }else{ 
                if(html.indexOf('##') == 0){ 
                    html = html.substr(2);
                    console.log(html);
                }else{
                    $('#next_unit').removeClass('hide');
                }
            }

            $('.main_unit_content').html(html);
            $('.quiz_title .inquiz_timer').trigger('deactivate');
            $('.in_quiz').trigger('question_loaded');
            $this.removeClass('submit_inquiz');
            $('.quiz_title .quiz_meta').addClass('hide');
            $this.addClass('quiz_results_popup');
            $this.attr('href',$('#results_link').val());
            runnecessaryfunctions();
            if(typeof all_quiz_questions_local  !== 'undefined'){
                $.each(all_quiz_questions_local , function(key, val) { 
                    localStorage.removeItem(val);
                    localStorage.removeItem('question_result_'+val);
                });
            }
            
            $this.text(vibe_course_module_strings.check_results);
            $this.parent().find('.save_quiz_progress').remove();
            $('#unit'+$('#unit.quiz_title').attr('data-unit')).addClass('done');
            $('body').find('.course_progressbar').removeClass('increment_complete');
            $('body').find('.course_progressbar').trigger('increment');
            $('body,html').animate({
                scrollTop: 0
            }, 1200);  
        }
    });
}
// QUIZ RELATED FUCNTIONS
// START QUIZ AJAX
jQuery(document).ready( function($) {
  $('.begin_quiz').click(function(event){
      var $this = $(this);
      if(!$this.hasClass('begin_quiz'))
        return;

      event.preventDefault();
      var quiz_id=$(this).attr('data-quiz');
      $this.prepend('<i class="fa fa-spinner animated spin"></i>');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'begin_quiz', 
                      start_quiz: $('#start_quiz').val(),
                      id: quiz_id
                    },
              cache: false,
              success: function (html) {
                  $this.find('i').remove();
                  $('.content').fadeOut("fast");
                  $('.content').html(html);
                  $('.content').fadeIn("fast");
                  var ques=$($.parseHTML(html)).filter("#question");
                  var q='#ques'+ques.attr('data-ques');
                  if(typeof all_questions_json != 'undefined' && !$this.hasClass('continue')){
                    $.each(all_questions_json,function(k,v){
                      localStorage.removeItem('question_result_'+v);
                      localStorage.removeItem(v);
                    });
                  }
                  var checkquestions = [];
                  $('.quiz_question').each(function(){
                      var qid = $(this).attr('data-qid');
                      var value = localStorage.getItem(qid);
                      if(value !=null){
                        $('#ques'+qid).addClass('done');
                      }else{
                        var question_id={'id':qid};
                        checkquestions.push(question_id);
                      }
                  });
                  if(checkquestions.length){
                    $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: { action: 'check_unanswered_questions', 
                                id: quiz_id,
                                questions:JSON.stringify(checkquestions),
                              },
                        cache: false,
                        success: function(json){ 
                          json = jQuery.parseJSON(json);
                          $.each(json,function(i,item){
                              $('#ques'+item.question_id).addClass('done');
                              localStorage.setItem(item.question_id,item.value);
                          });
                        }
                    });                    
                  }

                  $('.quiz_timeline').find('.active').removeClass('active');
                  $(q).addClass('active');
                  $('#question').trigger('question_loaded');
                  if(ques != 'undefined'){
                    $('.quiz_timer').trigger('activate');
                  }
                  runnecessaryfunctions();
                  $('.begin_quiz').each(function(){
                      $(this).removeClass('begin_quiz');
                      $(this).addClass('submit_quiz');
                      $(this).text(vibe_course_module_strings.submit_quiz);
                      if(!$(this).parent().find('.save_quiz_progress').length){
                        $(this).after('<a class="save_quiz_progress button full"><span class="save_progress_wrap"><span class="save_progress_label">'+vibe_course_module_strings.save_quiz+'</span><span class="save_progress_bar"><span class="save_progress_inner"></span></span></span></a>');
                      }
                  });
            }
        });
  });
});


function save_question_answer(current,defferred,per,data){
    
    if(current < defferred.length && all_questions_json.length >= per){
        console.log(current);
        if(defferred.length > 100 && (defferred.length - current) > 100){
            var record = 0;
            while(record < 9){
                // If more than 100 calls, run 9 more ajax calls
                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    async:true,
                    data: defferred[current],
                });
                current++;
                per++;
                record++;
            }
        }else if(defferred.length > 50 && (defferred.length - current) > 50){
            var record = 0;
            while(record < 4){
                // If more than 50 calls, run 4 more ajax calls
                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    async:true,
                    data: defferred[current],
                });
                current++;
                per++;
                record++;
            }
        }else if(defferred.length > 10 && (defferred.length - current) > 10){
            // If more than 10 calls, run 1 more ajax call
            $.ajax({
                type: "POST",
                url: ajaxurl,
                async:true,
                data: defferred[current],
            });
            current++;
            per++;
        }

       
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: defferred[current],
            cache: false,
            success: function(){ 
                per++;
                width = 100*per/all_questions_json.length;
                setTimeout(function(){
                    $('.save_progress_inner').css('width',width+'%');
                },per*100);

                if(all_questions_json.length == per){
                    $('body').trigger('all_question_answers_saved',[{'trigger':data}]);
                }else{
                    current++;
                    save_question_answer(current,defferred,per,data);
                }
            }
        });
    }
}//End of function

$( 'body' ).delegate( '.save_quiz_progress', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    if($this.hasClass('loading'))
        return;
    if(typeof all_questions_json != 'undefined'){
        $this.addClass('loading');
        var quiz_id;
        var per = 0;
        var width = 0;
        var security;
         $this.find('.save_progress_inner').css('width',width+'%');
        if($('body').hasClass('single-quiz')){
            security = $('#start_quiz').val();
            quiz_id=$('.submit_quiz').attr('data-quiz');
        }else{
            security = $('#hash').val();
            quiz_id = $('#unit.quiz_title').attr('data-unit');
        }
        var defferred =[];
        var current = 0;
        if(all_questions_json.length){
          $.each(all_questions_json,function(i,item){
              var value = localStorage.getItem(item);
              
              if(value !=null){
                  var data = {action: 'save_question_marked_answer',security:security,quiz_id: quiz_id,question_id:item,answer:value};
                  defferred.push(data);
              }else{
                  per++;
                  width = 100*per/all_questions_json.length
                  setTimeout(function(){
                      $this.find('.save_progress_inner').css('width',width+'%');
                  },100);
              }
              
              if(all_questions_json.length == per){
                  $this.find('.save_progress_label').text(vibe_course_module_strings.saved_quiz_progress);
                  $this.addClass('done');
                  $this.removeClass('loading');
                  setTimeout(function(){
                      $this.removeClass('done');
                      $this.find('.save_progress_label').text(vibe_course_module_strings.save_quiz);
                  },3000);
              }
          });
          
          save_question_answer(current,defferred,per,'save');
        }else{
          $this.find('.save_progress_inner').css('width','100%');
          $this.find('.save_progress_label').text(vibe_course_module_strings.saved_quiz_progress);
          $this.addClass('done');
          $this.removeClass('loading');
          setTimeout(function(){
              $this.removeClass('done');
              $this.find('.save_progress_label').text(vibe_course_module_strings.save_quiz);
          },3000);
        }
        

    }else{
        alert('No questions found in quiz !');
    }

    $('body').on('all_question_answers_saved',function(event,data){

        $this.find('.save_progress_label').text(vibe_course_module_strings.saved_quiz_progress);
        $this.removeClass('loading');
        $this.addClass('done');
        if(data.trigger == 'save'){
          setTimeout(function(){
            $this.removeClass('done');
            $this.find('.save_progress_label').text(vibe_course_module_strings.save_quiz);
          },3000);
        }
    });
});

$( 'body' ).delegate( '.show_hint', 'click', function(event){
  event.preventDefault();
  $(this).toggleClass('active');
  $(this).parent().find('.hint').toggle(400);
});

$( 'body' ).delegate( '.show_explaination', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    $this.toggleClass('active');
    $this.closest('li').find('.explaination').toggle();
});

$( 'body' ).delegate( '.quiz_question', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    var quiz_id=$(this).attr('data-quiz');
    var ques_id=$(this).attr('data-qid');
    $this.prepend('<i class="fa fa-spinner animated spin"></i>');
    $('#ajaxloader').removeClass('disabled');
    $('#question').css('opacity',0.2);
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'quiz_question', 
                    start_quiz: $('#start_quiz').val(),
                    quiz_id: quiz_id,
                    ques_id: ques_id
                  },
            cache: false,
            success: function (html) {
                $this.find('i').remove();
                $('.content').html(html);
                $('#ajaxloader').addClass('disabled');
                $('#question').css('opacity',1);
                var ques=$($.parseHTML(html)).filter("#question");
                var q='#ques'+ques.attr('data-ques');
                $('.quiz_timeline').find('.active').removeClass('active');
                $(q).addClass('active');
                $('#question').trigger('question_loaded');
                $('.tip').tooltip();
                $('audio,video').each( function() { if($(this).parents('.flowplayer').length) return; $(this).mediaelementplayer() } );
                if($('.timeline_wrapper').height() > $('.quiz_timeline').height()){
                  console.log($('.timeline_wrapper').height()+' > '+$('.quiz_timeline').height());
                  console.log($(q).position().top);
                  var sctop = $('.quiz_timeline').scrollTop() + $(q).position().top- $('.quiz_timeline').height()/2 + $(q).height()/2;
                  $('.quiz_timeline').animate({scrollTop: sctop}, 'slow');
                }
            }
      });
});



$( 'body' ).delegate( '#question', 'question_loaded',function(){
    runnecessaryfunctions();
    var $this = $(this);
    var question_id = $this.attr('data-ques');
    var marked_value = $this.find('#question_marked_answer'+question_id).val();
    var value = localStorage.getItem(question_id);
    if(value == null && typeof marked_value != 'undefined' && marked_value.length){
      value = marked_value;
      localStorage.setItem(question_id,marked_value);
      if(!$('#ques'+question_id).hasClass('done'))
          $('#ques'+question_id).addClass('done');
    }

    
    if($(this).find('.question_options.truefalse').length){
      
      if(value!= null){
        $(this).find('input[value="'+value+'"]').attr('checked','checked');
      }

      $('.question_options.truefalse').click(function(){
        $this = $('.question_options.truefalse');
        if($this.find('input[type="radio"]:checked').length)
        $this.find('input[type="radio"]:checked').each(function(){
          value = $(this).val();
          localStorage.setItem(question_id,value);
          $('#ques'+question_id).addClass('done');
        });
      });
    }

    if($(this).find('.question_options.single').length){
      
      if(value!= null){
        $(this).find('input[value="'+value+'"]').attr('checked','checked');
      }

      $('.question_options.single,.question_options.survey').click(function(){
        $this = $('.question_options.single');
        if($this.find('input[type="radio"]:checked').length)
        $this.find('input[type="radio"]:checked').each(function(){
          value = $(this).val();
          localStorage.setItem(question_id,value);
          $('#ques'+question_id).addClass('done');
        });
      });
    }
    
     if($(this).find('.question_options.multiple').length){

      
      if(value!=null){
        var new_vals = value.split(',');
        $.each(new_vals,function(k,vals){
          $this.find('input[value="'+vals+'"]').prop( "checked", true );
        });
      }

        $('.question_options.multiple').click(function(){
          $this = $('.question_options.multiple');
          value = '';
          if($this.find('input[type="checkbox"]:checked').length)
          $this.find('input[type="checkbox"]:checked').each(function(){
            value= $(this).val()+','+value;
          });
          localStorage.setItem(question_id,value);
          $('#ques'+question_id).addClass('done');
          });
      }
    
    if($this.find('.single_text').length){
        if(value != null){
            $this.find('.single_text input[type="text"]').val(value);
        }
    }
    
    $('.single_text input[type="text"]').on('keyup',function(){
        var value = $(this).val();
        localStorage.setItem(question_id,value);
        $('#ques'+question_id).addClass('done');
    });
    
    if($this.find('.vibe_fillblank').length){
        if(value != null ){
            //Check if value has |
            if (value.indexOf('|') >= 0){
                var result = value.split('|');
                $this.find('.vibe_fillblank').each(function(k,val){
                    $(this).text(result[k]);
                });
            }else{
                $(this).find('.vibe_fillblank').text(value);
            }
        }
    }
    $('.vibe_fillblank').on('keyup',function(){ 
      value='';
      $('.vibe_fillblank').each(function(){
          value += $(this).text().trim(); 
          if(value != null ){
              value +='|';
          }
      });
      localStorage.setItem(question_id,value);
      $('#ques'+question_id).addClass('done');
    });

 
    if($this.find('.essay_text textarea').length){
      if(value != null ){
        $this.find('.essay_text textarea').val(value);
      }
    } 

    $('.essay_text textarea').on('keyup',function(){
        var value = $(this).val();
        localStorage.setItem(question_id,value);
        $('#ques'+question_id).addClass('done');
     });
     
    
    if($this.find('.vibe_select_dropdown').length){
        if(value != null){
          if (value.indexOf('|') >= 0){
            var result = value.split('|');
            $this.find('.vibe_select_dropdown').each(function(k,val){
                $(this).val(result[k]);
            });
          }else{
              $('.vibe_select_dropdown').val(value);
          }
       }else{
          $('.vibe_select_dropdown').val('');
       }

       $('.vibe_select_dropdown').on('change',function(){
          var value = '';
          $('.vibe_select_dropdown').each(function(){
              if(value){value+='|';}
              value += $(this).val();
          });
          localStorage.setItem(question_id,value);
          $('#ques'+question_id).addClass('done');
        });
    }
   

    if($(this).find('.matchgrid_options li.match_option').length){

        $('.matchgrid_options li.match_option').each(function(){
          var id = $(this).attr('id');
          if( jQuery.isNumeric(id))
            value +=id+',';
        });  
        localStorage.setItem(question_id,value);
    }

  jQuery('.question_options.sort').each(function(){

    if(value != null){
      var new_vals = value.split(',');
      var $ul = $(".question_options"),
          $items = $(".question_options").children();
      for (var i = new_vals[new_vals.length - 1]; i >= 0; i--) {
          $ul.prepend( $items.get(new_vals[i] - 1));
      }
    }else{
        var defaultanswer='1';
        var lastindex = $('ul.question_options li').size();
        if(lastindex>1)
        for(var i=2;i<=lastindex;i++){
          defaultanswer = defaultanswer+','+i;
        }
        localStorage.setItem(question_id,defaultanswer);
    }
    $('#comment').val(defaultanswer);
    $('#comment').trigger('change');
    jQuery(this).sortable({
      revert: true,
      cursor: 'move',
      refreshPositions: true, 
      opacity: 0.6,
      scroll:true,
      containment: 'parent',
      placeholder: 'placeholder',
      tolerance: 'pointer',
      update: function( event, ui ) {
          var order = jQuery(this).sortable('toArray').toString();
          localStorage.setItem(question_id,order);
          $('#ques'+question_id).addClass('done');
      }
    }).disableSelection();

  });
  //Fill in the Blank Live EDIT

  $(".live-edit").liveEdit({
      afterSaveAll: function(params) {
        return false;
      }
  });

  if($('.question_options.match').length){

    //Match question type
    $('.question_options.match').droppable({
      drop: function( event, ui ){
        $(ui.draggable).removeAttr('style');
        $( this )
              .addClass( "ui-state-highlight" )
              .append($(ui.draggable))
      }
    });
    $('.question_options.match li').draggable({
      revert: "invalid",
      containment:'#question'
    });
    $( ".matchgrid_options li" ).droppable({
        activeClass: "ui-state-default",
        hoverClass: "ui-state-hover",
        drop: function( event, ui ){
          childCount = $(this).find('li').length;
          $('#ques'+question_id).addClass('done');
          $(ui.draggable).removeAttr('style');
          if (childCount !=0){
              return;
          }  
          
           $( this )
              .addClass( "ui-state-highlight" )
              .append($(ui.draggable))
         var value='';
         var a = [];
          $(this).parent().find('li.match_option').each(function(){
              var id = $(this).attr('id');
              if( jQuery.isNumeric(id))
                a.push(id);
          });  
          value = a.join(',');
          localStorage.setItem(question_id,value);     
        }

      });
  
      var id;
      $('.matchgrid_options li').each(function(index,value){
          id = $('.matchgrid_options').attr('data-match'+index);
          $(this).append($('#'+id));
          $('#ques'+question_id).addClass('done');
      });

      if(value != null){
        var new_vals = value.split(',');

        var $ul = $(".question_options.match"),
            $items = $(".question_options.match").children();
        for (var i = (new_vals.length - 1); i >= 0; i--) { 
           $('.matchgrid_options ul li').eq(i).append($items.get(new_vals[i]-1));
        }
      }
    } // End match answers

    //Check answers fix
    var check_answer = localStorage.getItem('question_result_'+question_id);
    if(check_answer != null){
        var json = $.parseJSON(check_answer);
        $('#question').after('<div class="question_wrapper"><div class="result"><div class="'+json.status+'"><span></span><strong>'+json.marks+'</strong></div></div></div>');
        if(json.correct){
          var jc = json.correct;
          jc = jc.split('|').join(' '+vibe_course_module_strings.And+' ');
          if(json.explanation){
            $('#question').append('<div class="checked_answer '+json.status+'"><strong>'+vibe_course_module_strings.correct_answer+' : '+jc+'<strong><br>'+vibe_course_module_strings.explanation+' : '+json.explanation+'</div>');
          }else{
            $('#question').append('<div class="checked_answer '+json.status+'"><strong>'+vibe_course_module_strings.correct_answer+' : '+jc+'<strong></div>');    
          }
        }
        var new_questions_json = new Array();;
        if($(all_questions_json).length){
            $.each(all_questions_json,function(i,val){
                if(val != question_id){
                    new_questions_json.push(val);
                }
            });
            all_questions_json = new_questions_json;
        }
        setTimeout(function(){$('#ques'+question_id).addClass(json.status);$('#question').addClass(json.status);$('#question+.question_wrapper').addClass('loaded');},100);
        setTimeout(function(){$('#question').addClass('check_answer_loaded');},500);
        $('.check_question_answer').remove();
    }
});



jQuery(document).ready( function($) {
 

  $('.quiz_timer').one('activate',function(){

    var qtime = parseInt($(this).attr('data-time'));
    var quiz_id=$(this).attr('data-quiz');  
    var security = $('#start_quiz').val();
    var $timer =$(this).find('.timer');
    var $this=$(this);
    
    $timer.timer({
      'timer': qtime,
      'width' : 200 ,
      'height' : 200 ,
      'unit'   : $this.attr('data-unit'),
      'fgColor' : vibe_course_module_strings.theme_color ,
      'bgColor' : vibe_course_module_strings.single_dark_color 
    });

    var $timer =$(this).find('.timer');

    $timer.on('change',function(){
        var countdown= $this.find('.countdown');
        var val = parseInt($timer.attr('data-timer'));
        if(val > 0){
          val--;
          $timer.attr('data-timer',val);
          var $text='';
          if(val >= 10800){
            $text = Math.floor(val/3600) + ':' + ((Math.floor((val%3600)/60) < 10)?'0'+Math.floor((val%3600)/60):Math.floor((val%3600)/60)) + '';
          }else if(val > 60){
            if($this.find('.timer_hours_labels').length){
              $this.find('.timer_hours_labels').remove();
              $this.find('.timer_mins_labels').toggle();
            }
            $text = Math.floor(val/60) + ':' + ((parseInt(val%60) < 10)?'0'+parseInt(val%60):parseInt(val%60)) + '';
          }else{
            $text = '00:'+ ((val < 10)?'0'+val:val);
          }

          countdown.html($text);
        }else{
            countdown.html(vibe_course_module_strings.timeout);

            if(!$('.submit_quiz').hasClass('triggerred')){
                var quiz_id=$('.submit_quiz').attr('data-quiz');  
                var security = $('#start_quiz').val();
                $('.submit_quiz').addClass('triggerred');
                if(typeof all_questions_json !== 'undefined'){  
                    wplms_submit_quiz(quiz_id,security);
                }
            } 
        }  
    });
    
  });

  $('.quiz_timer').one('deactivate',function(){
    var qtime = parseInt($(this).attr('data-time'));
    var $timer =$(this).find('.timer');
    var $this=$(this);
    
    $timer.knob({
        'readonly':true,
        'max': qtime,
        'width' : 200 ,
        'height' : 200 ,
        'fgColor' : vibe_course_module_strings.theme_color ,
        'bgColor' : vibe_course_module_strings.single_dark_color,
        'thickness': 0.2 ,
        'readonly':true 
      });
    event.stopPropagation();
  });

  $('.quiz_timer').one('end',function(event){
    var qtime = parseInt($(this).attr('data-time'));
    var $timer =$(this).find('.timer');
    var $this=$(this);
    
    $timer.knob({
        'readonly':true,
        'max': qtime,
        'width' : 200 ,
        'height' : 200 ,
        'fgColor' : vibe_course_module_strings.theme_color ,
        'bgColor' : vibe_course_module_strings.single_dark_color,
        'thickness': 0.2 ,
        'readonly':true 
      });
    event.stopPropagation();
  });
// Timer function runs after Trigger event definition
$('.quiz_timer').each(function(){
    var qtime = parseInt($(this).attr('data-time'));
    var $timer =$(this).find('.timer');
    $timer.knob({
      'readonly':true,
      'max': qtime,
      'width' : 200 ,
      'height' : 200 ,
      'fgColor' : vibe_course_module_strings.theme_color ,
      'bgColor' : vibe_course_module_strings.single_dark_color,
      'thickness': 0.2 ,
      'readonly':true 
    });
    if($(this).hasClass('start')){
      $('.quiz_timer').trigger('activate');
    }
});

jQuery('.question_options.sort').each(function(){
    var defaultanswer='1';
    var lastindex = $('ul.question_options li').size();
    if(lastindex>1)
    for(var i=2;i<=lastindex;i++){
      defaultanswer = defaultanswer+','+i;
    }
    $('#comment').val(defaultanswer);
    $('#comment').trigger('change');
    jQuery('.question_options.sort').sortable({
      revert: true,
      cursor: 'move',
      refreshPositions: true, 
      opacity: 0.6,
      scroll:true,
      containment: 'parent',
      placeholder: 'placeholder',
      tolerance: 'pointer',
      update: function( event, ui ) {
          var order = jQuery(this).sortable('toArray').toString();
          $('#comment').val(order);
          $('#comment').trigger('change');
      }
    }).disableSelection();
  });

  $('.selectusers').each(function(){
    var $this = $(this);
    $this.select2({
        minimumInputLength: 4,
        placeholder: $(this).attr('data-placeholder'),
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
                    return  {   action: 'select_users', 
                                security: $('#bulk_action').val(),
                                course: $('#add_student_to_course').attr('data-course'),
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
    }).on('select2:open',function(){
      if($('.select2-container .select2-dropdown').hasClass('select2-dropdown--below')){
        var topmargin = 45;
        $('.select2-container:not(.select2)').css('top', '+='+ topmargin +'px');
          //$('.select2-container:not(.select2) .select2-dropdown--below').css('margin-top','45px');
      }
    });
  });  

}); 

$( 'body' ).on( 'click','.expand_message',function(event){
  event.preventDefault();
  $('.bulk_message').toggle('slow');
});

$('body').on('click','.expand_change_status',function(event){
  event.preventDefault();
  $('.bulk_change_status').toggle('slow');
  $('#status_action').on('change',function(){
      if($(this).val() === 'finish_course' ){
          $('#finish_marks').removeClass('hide');
      }else{
        $('#finish_marks').addClass('hide');
      }
  });
});

$( 'body' ).on( 'click','.expand_add_students',function(event){
  event.preventDefault();
  $('.bulk_add_students').toggle('slow');
});

$( 'body' ).on( 'click','.expand_assign_students', function(event){
  event.preventDefault();
  $('.bulk_assign_students').toggle('slow');
});

$( 'body' ).on( 'click','.extend_subscription_students', function(event){
  event.preventDefault();
  $('.bulk_extend_subscription_students').toggle('slow');
});


$( 'body' ).delegate( '#send_course_message', 'click', function(event){
  event.preventDefault();
  var members=[];

  var $this = $(this);
  var defaultxt=$this.html();
  $this.html('<i class="fa fa-spinner animated spin"></i> '+vibe_course_module_strings.sending_messages);
  var i=0;
  $('.member').each(function(){
    if($(this).is(':checked')){
      members[i]=$(this).val();
      i++;
    }
  });
  $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'send_bulk_message',
                security: $('#bulk_action').val(),
                course:$this.attr('data-course'),
                sender: $('#sender').val(),
                all: $('#all_bulk_students:checked').val(),
                members: JSON.stringify(members),
                subject: $('#bulk_subject').val(),
                message: $('#bulk_message').val(),
              },
        cache: false,
        success: function (html) {
            $('#send_course_message').html(html);
            setTimeout(function(){$this.html(defaultxt);}, 5000);
        }
    });    
});

$( 'body' ).delegate( '#add_student_to_course', 'click', function(event){
  event.preventDefault();
  var $this = $(this);
  var defaultxt=$this.html();
  var students = $('#student_usernames').val();

  if(students.length <= 0){ 
    $('#add_student_to_course').html(vibe_course_module_strings.unable_add_students);
    setTimeout(function(){$this.html(defaultxt);}, 2000);
    return;
  }

  $this.html('<i class="fa fa-spinner animated spin"></i>'+vibe_course_module_strings.adding_students);
  var i=0;
  $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'add_bulk_students', 
                security: $('#bulk_action').val(),
                course:$this.attr('data-course'),
                members: students,
              },
        cache: false,
        success: function (html) {
          if(html.length && html !== '0'){
            $('#add_student_to_course').html(vibe_course_module_strings.successfuly_added_students);
            $('ul.course_students').prepend(html);
            $('ul.course_students #message').remove();
          }else{
            $('#add_student_to_course').html(vibe_course_module_strings.unable_add_students);
          }
            $('.selectusers').select2('val', '');
            setTimeout(function(){$this.html(defaultxt);}, 3000);
        }
    });    
});

$( 'body' ).delegate( '#download_stats', 'click', function(event){
  event.preventDefault();
  var $this = $(this);
  var defaultxt=$this.html();
  var i=0;
  var fields=[]; 
  $('.field:checked').each(function(){
      fields[i]=$(this).attr('id');//$(this).val();
      i++;
  });
  
  if(i==0){
    $this.html(vibe_course_module_strings.select_fields);
    setTimeout(function(){$this.html(defaultxt);}, 13000);
    return false;
  }else{
    $this.html('<i class="fa fa-spinner animated spin"></i> '+vibe_course_module_strings.processing);
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'download_stats', 
                security: $('#stats_security').val(),
                course:$this.attr('data-course'),
                fields: JSON.stringify(fields),
                type:$('#stats_students').val()
              },
        cache: false,
        success: function (html) {
            $this.attr('href',html);
            $this.attr('id','download');
            $this.html(vibe_course_module_strings.download)
            //setTimeout(function(){$this.html(defaultxt);}, 5000);
        }
    });  
  }
});

$('body').delegate('#download_mod_stats','click',function(event){
  event.preventDefault();
  var $this = $(this);
  var defaultxt=$this.html();
  var i=0;
  var fields=[]; 
  $('.field:checked').each(function(){
      fields[i]=$(this).attr('id');//$(this).val();
      i++;
  });
  
  if(i==0){
    $this.html(vibe_course_module_strings.select_fields);
    setTimeout(function(){$this.html(defaultxt);}, 13000);
    return false;
  }else{
    $this.html('<i class="fa fa-spinner animated spin"></i> '+vibe_course_module_strings.processing);
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'download_mod_stats', 
                security: $('#stats_security').val(),
                type:$this.attr('data-type'),
                id:$this.attr('data-id'),
                fields: JSON.stringify(fields),
                select:$('#stats_students').val()
              },
        cache: false,
        success: function (html) {
            $this.attr('href',html);
            $this.attr('id','download');
            $this.html(vibe_course_module_strings.download)
            //setTimeout(function(){$this.html(defaultxt);}, 5000);
        }
    });  
  }
});

$( 'body' ).delegate( '#assign_course_badge_certificate', 'click', function(event){
  event.preventDefault();
  var members=[]; 

  var $this = $(this);
  var defaultxt=$this.html();
  $this.html('<i class="fa fa-spinner animated spin"></i> '+vibe_course_module_strings.processing);
  var i=0;
  $('.member').each(function(){
    if($(this).is(':checked')){
      members[i]=$(this).val();
      i++;
    }
  });

  $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'assign_badge_certificates', 
                security: $('#bulk_action').val(),
                course: $this.attr('data-course'),
                members: JSON.stringify(members),
                assign_action: $('#assign_action').val(),
              },
        cache: false,
        success: function (html) {
            $this.html(html);
            setTimeout(function(){$this.html(defaultxt);}, 5000);
        }
    });    
});

$( 'body' ).delegate( '#change_course_status', 'click', function(event){
  event.preventDefault();
  var members=[]; 

  var $this = $(this);
  var defaultxt=$this.html();
  $this.html('<i class="fa fa-spinner animated spin"></i> '+vibe_course_module_strings.processing);
  var i=0;
  $('.member').each(function(){
    if($(this).is(':checked')){
      members[i]=$(this).val();
      i++;
    }
  });

  $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'change_course_status', 
                security: $('#bulk_action').val(),
                course: $this.attr('data-course'),
                members: JSON.stringify(members),
                status_action: $('#status_action').val(),
                data: $('#finish_marks').val()
              },
        cache: false,
        success: function (html) {
            $this.html(html);
            setTimeout(function(){$this.html(defaultxt);}, 5000);
        }
    });    
});


$( 'body' ).delegate( '#extend_course_subscription', 'click', function(event){
  event.preventDefault();
  var members=[];

  var $this = $(this);
  var defaultxt=$this.html();
  $this.html('<i class="fa fa-spinner animated spin"></i> '+vibe_course_module_strings.processing);
  var i=0;
  $('.member').each(function(){
    if($(this).is(':checked')){
      members[i]=$(this).val();
      i++;
    }
  });

  $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'extend_course_subscription', 
                security: $('#bulk_action').val(),
                course: $this.attr('data-course'),
                members: JSON.stringify(members),
                extend_amount: $('#extend_amount').val(),
              },
        cache: false,
        success: function (html) {
            $this.html(html);
            setTimeout(function(){$this.html(defaultxt);}, 5000);
        }
    });    
});



$( 'body' ).delegate( '#mark-complete', 'media_loaded', function(event){

  event.preventDefault(); 
  if($(this).hasClass('tip')){
    $(this).addClass('disabled');
  }

  var unit_id = $('#mark-complete').data('unit');
  $('.unit_content').find('audio,video').each(function(){ 

    var _player = this.player;
    var key = 'time_'+unit_id+'_'+_player.id;
    var playedtime = localStorage.getItem(key);
    var old = _player.media.setCurrentTime;

    if(playedtime != null){
      old.apply(this,[playedtime]); 
    }

    _player.media.addEventListener('timeupdate', function(e) {
        localStorage.setItem(key,_player.media.currentTime);
    }, false);
    
  });

});

$( 'body' ).delegate( '#mark-complete', 'media_complete', function(event){ 
  event.preventDefault();
  if($(this).hasClass('tip')){
    $(this).removeClass('disabled');
    $(this).removeClass('tip');
    $(this).tooltip('destroy');
    jQuery('.tip').tooltip();
  }  
});


$( 'body' ).delegate( '#mark-complete', 'click', function(event){
    event.preventDefault();
    if($(this).hasClass('disabled')){
      return false;
    }
    $(this).addClass('disabled');
    var $this = $(this);
    var unit_id=$(this).attr('data-unit');
    $this.prepend('<i class="fa fa-spinner animated spin"></i>');
    $('body').find('.course_progressbar').removeClass('increment_complete');
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'complete_unit', 
                    security: $('#hash').val(),
                    course_id: $('#course_id').val(),
                    id: unit_id
                  },
            cache: false,
            success: function (html) {
                $this.find('i').remove();
                $this.html('<i class="icon-check"></i>');
                $('.course_timeline').find('.active').addClass('done');
                
                if(html.length > 0 && jQuery.isNumeric(html)){
                    $('#next_unit').removeClass('hide');
                    $('#next_unit').attr('data-unit',html);  
                    $('#next_quiz').removeClass('hide');
                    $('#next_quiz').attr('data-unit',html); 
                    $('#unit'+html).find('a').addClass('unit');
                    $('#unit'+html).find('a').attr('data-unit',html);
                }
                $('body').find('.course_progressbar').trigger('increment');
                if(typeof unit != 'undefined')
                  $('.unit_timer').trigger('finish');
            }
    });
});


$('.course_progressbar').on('increment',function(event){

  if($(this).hasClass('increment_complete')){
    event.stopPropagation();
    return false;
  }else{
    var total_units = parseInt($('.unit_line').length);
    var done_units = parseInt($('.unit_line.done').length);
    if(total_units == 0){total_units = 1;}
  //  var iunit = parseFloat(1/total_units);//parseFloat($(this).attr('data-increase-unit'));
    var per = parseFloat(done_units/total_units);
//alert('total_units = '+total_units+' done_units ='+done_units+' | per ='+per+' | iunit = '+iunit);
    newper = per*100;
    newper = newper.toFixed(2);

    //Boundary Conditions
    if(newper > 100 || newper > (100-per))
      newper=100;

    if(newper<0)
      newper=0;
    
    $('.course_timeline').each(function(){
        if($(this).find('.unit_line').length == $(this).find('.done').length){
           newper = 100;
        }
        if($(this).find('.done').length == 0){
          newper = 0;
        }
    });
    /*== Boundary Conditions check ==*/

    $(this).find('.bar').css('width',newper+'%');
    $(this).find('.bar span').html(newper + '%');
    $(this).addClass('increment_complete');
    $(this).attr('data-value',newper);
    $.ajax({
            type: "POST",
            url: ajaxurl,
            async:true,
            data: { action: 'record_course_progress', 
                    security: $('#hash').val(),
                    course_id: $('#course_id').val(),
                    progress: newper
                  },
            cache: false,
          });
  }
  event.stopPropagation();
  return false;
  
});

$('.course_progressbar').on('decrement',function(event){

  if($(this).hasClass('increment_complete')){
    event.stopPropagation();
    return false;
  }else{
    var iunit = parseFloat($(this).attr('data-increase-unit'));
    var per = parseFloat($(this).attr('data-value'));
    newper =  per-iunit;
    newper = newper.toFixed(2);
    if(newper<0)
      newper=0;
    $(this).find('.bar').css('width',newper+'%');
    $(this).find('.bar span').html(newper + '%');
    $(this).addClass('increment_complete');
    $(this).attr('data-value',newper);
    $.ajax({
            type: "POST",
            url: ajaxurl,
            async:true,
            data: { action: 'record_course_progress', 
                    security: $('#hash').val(),
                    course_id: $('#course_id').val(),
                    progress: newper
                  },
            cache: false,
            success: function (html) {
              /*var cookie_id ='course_progress'+$('#course_id').val();
              $.cookie(cookie_id,newper, { path: '/' }); */
            }
          });
  }
  event.stopPropagation();
  return false;
  
});

jQuery(document).ready(function($){
  $('.showhide_indetails').click(function(event){
    event.preventDefault();
    $(this).find('i').toggleClass('icon-minus');
    $(this).parent().find('.in_details').toggle();
  });

$('.ajax-certificate').each(function(){
    var $this = $(this);
    if($this.hasClass('certificate_image')){

      $this.magnificPopup({
          type: 'image',
          gallery: {enabled: true},
          closeOnContentClick: false,
          closeBtnInside: false,
          fixedContentPos: true,
          mainClass: 'mfp-no-margins mfp-with-zoom', 
          image: {
            verticalFit: true,
            cursor: 'mfp-zoom-out-cur',
            markup: '<div class="mfp-figure">'+
            '<div class="mfp-close"></div>'+
            '<div id="certificate">'+
            '<div class="extra_buttons">'+
            '<a href="#" class="certificate_close"><i class="fa fa-times"></i></a>'+
            '<a href="#" class="certificate_print"><i class="fa fa-print"></i></a>'+
            '<a href="#" class="certificate_pdf"><i class="fa fa-file-pdf-o"></i></a>'+
            '<a href="#" class="certificate_download"><i class="fa fa-download"></i></a>'+
            '<a href="https://www.facebook.com/share.php?u='+$(this).attr('href')+'" target="_blank"><i class="fa fa-facebook"></i></a>'+
            '<a href="https://twitter.com/share?url='+$(this).attr('href')+'" target="_blank"><i class="fa fa-twitter"></i></a>'+
            '</div>'+
            '<div id="certificate_image" class="mfp-img"></div>'+
            '<div class="mfp-bottom-bar">'+
              '<div class="mfp-title"></div>'+
              '<div class="mfp-counter"></div>'+
            '</div>'+
            '</div>'+
          '</div>', 
          },
          callbacks: {
            open : function (){
              $('.extra_buttons').show();
              var mp = $.magnificPopup.instance;
              console.log(mp);
              var img = new Image();
              img.onload = function() {
                var canvas = document.createElement("canvas");
                canvas.width = this.width;
                canvas.height = this.height;

                var ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0);
                var dataURL = canvas.toDataURL("image/jpeg");

                $('.certificate_pdf').click(function(){
                  var doc = new jsPDF();
                  doc.addImage(dataURL, 'JPEG',0,0, this.width,this.height);
                  doc.save('certificate.pdf');
                });
              }
              img.src = $this.attr('href');

              
            }
          }
      });
    }else{

      $this.magnificPopup({
          type: 'ajax',
          fixedContentPos: true,
          alignTop:false,
          preloader: false,
          midClick: true,
          removalDelay: 300,
          showCloseBtn:false,
          mainClass: 'mfp-with-zoom',
          callbacks: {
            parseAjax: function( mfpResponse ) {
              mfpResponse.data = $(mfpResponse.data).find('#certificate');
            },
            ajaxContentAdded: function() {
              var node = $('#certificate');
              if($('#certificate').find('.certificate.type-certificate').length){
                node = $('#certificate .certificate.type-certificate');
              }
              if($('#certificate .certificate_content').attr('data-width').length){
                var certificate_width = $('#certificate .certificate_content').attr('data-width');
                var fullwidth = $(window).width();
                console.log(certificate_width+' vs '+fullwidth);
                var ratio = fullwidth/certificate_width;
                if(ratio >= 1){ratio = 1;}else{
                  ratio=ratio-0.1;
                  $('section#certificate').removeAttr('style');
                  $('section#certificate').css('overflow','hidden');
                  $('section#certificate').css('transform','scale('+ratio+')');
                  node = $('section#certificate');
                  node.removeAttr('style');
                }
                
              }
              
                if(!$('section#certificate').hasClass('stopscreenshot')){
                    $('.extra_buttons').hide();
                    html2canvas(node, {
                        backgrounnd:'#ffffff',
                        onrendered: function(canvas) {
                            node.find('#certificate .certificate_content').removeAttr('style');
                            var data = canvas.toDataURL("image/jpeg");
                            if(ratio >= 1){
                                $('#certificate .certificate_content').html('<img src="'+data+'" width="'+$('#certificate .certificate_content').attr('data-width')+'" height="'+$('#certificate .certificate_content').attr('data-height')+'" />');
                            }else{
                                $('#certificate .certificate_content').html('<img src="'+data+'" />');
                            }
                            $('#certificate').trigger('generate_certificate');
                          
                            $('.certificate_pdf').click(function(){
                                var doc = new jsPDF();
                                var width = 210;
                                var height = 80;
                                if($('#certificate .certificate_content').attr('data-width').length){
                                    height = Math.round(210*parseInt($('#certificate .certificate_content').attr('data-height'))/parseInt($('#certificate .certificate_content').attr('data-width')));
                                }
                                doc.addImage(data, 'JPEG',0,0, 210,height);
                                doc.save('certificate.pdf');
                            });
                            if($this.hasClass('regenerate_certificate')){
                              $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: { action: 'save_certificate_image', 
                                        image:data,
                                        security: $this.attr('data-security'),
                                        user_id:$this.attr('data-user'),
                                        course_id:$this.attr('data-course')
                                      },
                                cache: false,
                                success: function(html){
                                  console.log(html);
                                  $('body').find('.certificate_download').attr('data-url',html);
                                  $('.extra_buttons').show();
                                }
                              });
                            }
                        }
                    });
                }else{
                    $('.extra_buttons').show();
                    $('.certificate_pdf').hide();
                }
            },
          }
      });
    }
});

$('.ajax-badge').each(function(){
  var $this=$(this);
  var img=$this.find('img');
  $(this).magnificPopup({
        items: {
            src: '<div class="badge-popup"><img src="'+img.attr('src')+'" /><h3>'+$this.attr('title')+'</h3><strong>'+vibe_course_module_strings.for_course+' '+$this.attr('data-course')+'</strong></div>',
            type: 'inline'
        },
        fixedContentPos: false,
        alignTop:false,
        preloader: false,
        midClick: true,
        removalDelay: 300,
        showCloseBtn:false,
        mainClass: 'mfp-with-zoom center-aligned'
    });
});

$( 'body' ).delegate( '.print_unit', 'click', function(event){
    $('.unit_content').print();
});

$( 'body' ).delegate( '.printthis', 'click', function(event){
    $(this).parent().print();
});

$( 'body' ).delegate( '#certificate', 'generate_certificate', function(event){
    $(this).addClass('certificate_generated');
});

function PrintElem(elem){
    Popup($(elem).html());
}

function Popup(data) {
    var mywindow = window.open('', 'my div', 'height=800,width=1000');

    mywindow.document.head.innerHTML = '<title>PressReleases</title><link rel="stylesheet" href="css/main.css" type="text/css" />'; 
    mywindow.document.body.innerHTML = '<body>' + data + '</body>'; 

    mywindow.document.close();
    mywindow.focus(); // necessary for IE >= 10
    mywindow.print();
    mywindow.close();

    return true;
}

$( 'body' ).delegate( '.certificate_print', 'click', function(event){
    event.preventDefault();
    PrintElem('#certificate');
});
$( 'body' ).delegate( '.certificate_download', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    if($(this).data('url')){
        var img = $(this).data('url');
        imgWindow = window.open(img, 'imgWindow');
    }else{
        var img = $('#certificate img').attr('src');
        imgWindow = window.open(img, 'imgWindow');
    }
});
$( 'body' ).delegate( '.certificate_close', 'click', function(event){
    event.preventDefault();
    $.magnificPopup.close();
});

$('.widget_carousel').each(function(){
    var $this = $(this);
    var auto = false;
    if($this.hasClass('auto')){
      auto = true;
    }
    $this.flexslider({
      animation: "slide",
      controlNav: false,
      directionNav: true,
      animationLoop: true,
      slideshow: auto,
      prevText: "<i class='icon-arrow-1-left'></i>",
      nextText: "<i class='icon-arrow-1-right'></i>",
    });
});

  /*=== Quick tags ===*/
  $( 'body' ).delegate( '.unit-page-links a', 'click', function(event){
        if($('body').hasClass('single-unit'))
          return;

        event.preventDefault();
        
        var $this=$(this);
        $('#ajaxloader').removeClass('disabled');
        $('.unit_content').addClass("loading");
        $('#discussion').remove(); // Unit comments remove
        $('.main_unit_content+.widget').remove(); //DWQA addon fix
        $( ".main_unit_content" ).load( $this.attr('href') +" .single_unit_content" ,function(){
          $('.unit_content').trigger('unit_traverse');
          $('body').trigger('unit_loaded');
          $('#ajaxloader').addClass('disabled');
          $('.unit_content').removeClass("loading");
        });
        $this.find('i').remove();
        $( ".main_unit_content" ).trigger('unit_reload');
  });
  

 

   $('body').delegate('.pricing_course .drop label','click',function(){
        var labelText = $(this).find('.font-text').html();
         var value = $(this).attr('data-value');
         var parent = $(this).parent().parent();
         $(parent).find('.result').html(labelText);
        if($('.course_button').length){
          $('.course_button').attr('href',value);
        }
    });

    $('body').delegate('.pricing_course .result','click',function() {
      var parent = $(this).parent();
        $(parent).find('.drop').slideToggle('fast');
    });

    $('body').delegate('.pricing_course .drop','click',function() {
        var parent = $(this).parent();
        $(parent).find('.drop').slideUp('fast');
    });
}); 



$('.unit_content').on('unit_traverse',function(){
  runnecessaryfunctions();
  $('body,html').animate({
    scrollTop: 0
  }, 1200);
  $('.unit-page-links').each(function(){
    if(!$('.page-link>span').length){
      var link_html=$('.page-link a').first().html();$('.page-link a').first().remove();$('.page-link').prepend(link_html);
    }
  });
});

// Course Unit Traverse

$( 'body' ).delegate( '.unit', 'click', function(event){
    event.preventDefault();
    if($(this).hasClass('disabled')){
      return false;
    }
    
    var $this = $(this);
    var unit_id=$(this).attr('data-unit');
    if($this.prev().is('span')){
      $this.prev().addClass('loading');
    }else{
      $this.prepend('<i class="fa fa-spinner animated spin"></i>');  
    }
    
    $('#ajaxloader').removeClass('disabled');
    $('.unit_content').addClass("loading");


    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'unit_traverse', 
                    security: $('#hash').val(),
                    course_id: $('#course_id').val(),
                    id: unit_id
                  },
            cache: false,
            success: function (html) {

                if($this.prev().is('span')){
                  $this.prev().removeClass('loading');
                }else{
                  $this.find('i').remove();
                } 
                $('#ajaxloader').addClass('disabled');
                $('.unit_content').removeClass("loading");
                $('.unit_content').html(html);

                var unit=$($.parseHTML(html)).filter("#unit");
                var u='#unit'+unit.attr('data-unit');
                $('.course_timeline').find('.active').removeClass('active');
                $(u).addClass('active');

                $('.unit_content').trigger('unit_traverse');
                runnecessaryfunctions();
                //=== UNIT COMMENTS ======
                if($('.unit_wrap').hasClass('enable_comments')){ 
                  $('.unit_content').trigger('load_comments');
                }

                if(typeof unit != 'undefined')
                  $('.unit_timer').trigger('activate');
            }
    });
});
 
/*==============================================================*/
/*======================= UNIT COMMENTS ========================*/
/*==============================================================*/

$( 'body' ).delegate( '.unit_content', 'load_comments', function(event){ 
        if($(this).find('.main_unit_content').hasClass('stop_notes') || $('body').hasClass('wp-fee-body'))
          return;

       var unit_id=$('#unit').attr('data-unit');
      $('.unit_content p').each(function(index){
            if (!$(this).parents('.question').length){
              $(this).attr('data-section-id',index);
              $(this).append('<span id="c'+index+'" class="side_comment">+</span>');
            }
        });

        $('.unit_content .side_comment').on('click',function(event){
            event.preventDefault();
            if($('.unit_content').hasClass('open')){
              $('.unit_content').removeClass('open');
            }else{
              $('.unit_content').addClass('open');
              $('body').trigger('side_comments_open');
            }
            event.stopPropagation();
        });

        $('body').on('side_comments_open',function(){
          $(document).on('click',function(e){
            console.log(e);
              if(!$(e.target).closest('.side_comments').length){
                $('.unit_content').removeClass('open');  
              }
          });
        });

         $.ajax({
            type: "POST",
            dataType: "json",
            url: ajaxurl,
            data: { action: 'get_unit_comment_count', 
                    security: $('#hash').val(),
                    unit_id: unit_id,
                  },
            cache: false,
            success: function (response) {
              $.each(response, function(idx, obj) {
                $('#'+obj.id).text(obj.count);
              });
              }
            });
        $('.side_comment').on('click',function(){ 
          if($(this).hasClass('active'))
            return false;
          var $this = $(this);
          var section = $('.side_comment.active').attr('id');
          $('.side_comment').removeClass('active');
          $('.side_comments .main_comments>li:not(".hide")').remove();
          $(this).addClass('active');
          var id = $(this).attr('id');
          $('.add-comment').fadeIn();
          $('.add-comment').next().fadeOut();
          var check = $(this).text();
          var href= "#";
          $('.side_comments .main_comments').find('.loaded').remove();
          if( jQuery.isNumeric(check)){
            var comment_html ='';
            var cookie_id='unit_comments'+unit_id;
            //var unit_comments = $.cookie(cookie_id);
            var unit_comments = sessionStorage.getItem(cookie_id);
            //CHeck cookie
            if (unit_comments !== null){ 
               unit_comments = JSON.parse(unit_comments);
               $.each(unit_comments, function(idxx, objStr) { 
               $.each(objStr, function(idx, obj){ 
                  if(id == idx){ 
                    comment_html += '<li class="loaded"><div class="'+obj.type+' user'+obj.author.user_id+'" data-id="'+obj.ID+'"><img src="'+obj.author.img+'"><a href="'+obj.author.link+'" class="unit_comment_author">'+obj.author.name+'</a><div class="unit_comment_content">'+obj.content+'</div><ul class="actions" data-pid="'+$this.attr('id')+'">';
                    
                      jQuery.each(obj.controls, function(i,o) { 
                        if(o>1){
                         jQuery('.side_comments li.hide').find('.'+i).addClass('meta_info').attr('data-meta',o);
                        }
                        var control = jQuery('.side_comments li.hide').find('.'+i).parent()[0].outerHTML;
                        if(o>1){
                          jQuery('.side_comments li.hide').find('.'+i).removeClass('meta_info').removeAttr('data-meta');
                        }
                        comment_html +=control;
                      });
                    
                    comment_html +='</ul></div></li>';
                    href=$(comment_html).find('.popup_unit_comment').removeClass('meta_info').attr('data-href');
                    href +='?unit_id='+unit_id+'&section='+idx;
                  } 
                });
               });
               $('.side_comments .main_comments').append(comment_html);
               $('.side_comments .main_comments .popup_unit_comment').attr('href',href);
               jQuery('.tip').tooltip();
               $('.popup_unit_comment').magnificPopup({
                    type: 'ajax',
                    alignTop: true,
                    fixedContentPos: true,
                    fixedBgPos: true,
                    overflowY: 'auto',
                    closeBtnInside: true,
                    preloader: false,
                    midClick: true,
                    removalDelay: 300,
                    mainClass: 'my-mfp-zoom-in',
                    callbacks: {
                             parseAjax: function( mfpResponse ) {
                              mfpResponse.data = $(mfpResponse.data).find('.content');
                            }
                          }
                });
            }else{ //ajax request and grab the json from ajax
                section =$('.side_comment.active').attr('id');
                
                $.ajax({
                  type: "POST",
                  dataType: "json",
                  url: ajaxurl,
                  data: { action: 'unit_section_comments', 
                          security: $('#hash').val(),
                          unit_id: unit_id,
                          section: section,
                          num:$('.side_comment').length
                        },
                  cache: false,
                  success: function (jsonStr){
                     var cookie_value =JSON.stringify(jsonStr);
                     sessionStorage.setItem(cookie_id,cookie_value);
                      $.each(jsonStr, function(idxx, objStr){ 
                         $.each(objStr, function(idx, obj){ 
                          if(id == idx){
                            comment_html += '<li class="loaded"><div class="'+obj.type+' user'+obj.author.user_id+'" data-id="'+obj.ID+'"><img src="'+obj.author.img+'"><a href="'+obj.author.link+'" class="unit_comment_author">'+obj.author.name+'</a><div class="unit_comment_content">'+obj.content+'</div><ul class="actions" data-pid="'+$this.attr('id')+'">';

                              jQuery.each(obj.controls, function(i,o) { 
                                if(o>1){
                                 jQuery('.side_comments li.hide').find('.'+i).addClass('meta_info').attr('data-meta',o);
                                }
                                var control = jQuery('.side_comments li.hide').find('.'+i).parent()[0].outerHTML;
                                if(o>1){
                                  jQuery('.side_comments li.hide').find('.'+i).removeClass('meta_info').removeAttr('data-meta');
                                }
                                comment_html +=control;
                              });

                            comment_html +='</ul></div></li>';
                            var href=$(comment_html).find('.popup_unit_comment').attr('href');
                            href +='?unit_id='+unit_id+'&section='+idx;
                            $(comment_html).find('.popup_unit_comment').attr('href',href);
                          }
                        });
                      });   
                      $('.side_comments .main_comments').append(comment_html);
                      jQuery('.tip').tooltip();
                      $('.popup_unit_comment').magnificPopup({
                          type: 'ajax',
                          alignTop: true,
                          fixedContentPos: true,
                          fixedBgPos: true,
                          overflowY: 'auto',
                          closeBtnInside: true,
                          preloader: false,
                          midClick: true,
                          removalDelay: 300,
                          mainClass: 'my-mfp-zoom-in',
                          callbacks: {
                                   parseAjax: function( mfpResponse ) { 
                                    mfpResponse.data = $(mfpResponse.data).find('.content');
                                  }
                                }
                      });
                  }
              });
            } //end else 
          } // end if numeric check
          var all_href=$('#all_comments_link').attr('data-href');
          all_href +='?unit_id='+unit_id+'&section='+$('.side_comment.active').attr('id');
          $('#all_comments_link').attr('href',all_href);

          var top = $(this).offset().top; 
          var content_top=$('#unit_content').offset().top; 
          var height = $('.side_comments').height();
          var limit = $('.unit_prevnext').offset().top;
          if((top+height) > limit){
            top = limit - content_top - height;
          }else{
            top = top - content_top;
          }
          if(top >0){
            $('.side_comments').css('top',top+'px');
            $('.side_comments').removeClass('scroll');
          }else{
            $('.side_comments').addClass('scroll');
            var h=$('.main_unit_content').height();
            $('.side_comments').css('height',h+'px');
          }
        }); 
      /*=== END UNIT COMMENTS ======*/
  });
/* ===== UNIT COMMENTS =====*/
jQuery(document).ready(function($){

  

  $('.add-comment').on('click',function(){
      $(this).fadeOut(0);
      $(this).next('.comment-form').fadeIn(100);
  });

  $('.new_side_comment').on('click',function(){ 
    if(!$(this).hasClass('cleared')){
      $(this).html('');$(this).addClass('cleared');
      $(this).parent().parent().addClass('active');
      $(this).parent().parent().parent().find('.add-comment').addClass('deactive');
    }
  });

  $('.remove_side_comment').on('click',function(){
    $(this).closest('.side_comments').find('.add-comment').fadeIn(100);
    $(this).closest('.comment-form').fadeOut();
    $('.new_side_comment').removeClass('cleared');
    $('.new_side_comment').text(vibe_course_module_strings.add_comment);
  });
}); // End Ready


$( 'body' ).delegate( '.get_results_pagination', 'click', function(event){
  event.preventDefault();
  var $this = $(this);
  $this.append('<i class="fa fa-spinner"></i>');
   $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'get_results_page', 
                    security: $('#security').val(),
                    type:$this.attr('data-type'),
                    page: $this.text()
                  },
            cache: false,
            success: function (html) {
                $this.find('fa').remove();
                $this.parent().find('.current').removeClass('current');
                $this.addClass('current');
                $this.closest('.user_results').html(html);
            }
    });
});

$( 'body' ).delegate( '.public_unit_comment', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    var id =$this.closest('li.loaded>div').attr('data-id');
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'public_user_comment', 
                    security: $('#hash').val(),
                    id: id
                  },
            cache: false,
            success: function (html) {
                $this.removeClass('public_unit_comment');
                $this.addClass('private_unit_comment');
                $this.find('i').removeClass().addClass('icon-fontawesome-webfont-4');
                $this.attr('data-original-title',vibe_course_module_strings.private_comment);
                var unit_id = $('#unit').attr('data-unit');
                var cookie_id='unit_comments'+unit_id;
                sessionStorage.removeItem(cookie_id);
            }
    });
});
$( 'body' ).delegate( '.private_unit_comment', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    var id =$this.closest('li.loaded>div').attr('data-id');
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'private_user_comment', 
                    security: $('#hash').val(),
                    id: id
                  },
            cache: false,
            success: function (html) {
                $this.removeClass('private_unit_comment');
                $this.addClass('public_unit_comment');
                $this.find('i').removeClass().addClass('icon-fontawesome-webfont-3');
                $this.attr('data-original-title',vibe_course_module_strings.public_comment);
                var unit_id = $('#unit').attr('data-unit');
                var cookie_id='unit_comments'+unit_id;
                sessionStorage.removeItem(cookie_id);
            }
    });
});
$( 'body' ).delegate( '.edit_unit_comment', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    var content = $this.parent().parent().parent();
    var form = $('.comment-form').first().clone();
    var img = content.find('img').clone();
    var unit_comment_author = content.find('.unit_comment_author').clone();
    var id = content.attr('data-id');
    form.find('img').replaceWith(function(){return img;});
    form.find('span').replaceWith(function(){return unit_comment_author;});
    var new_content = content.find('.unit_comment_content');
    form.find('.new_side_comment').html(new_content.html());
    //console.log(id+'#');
    form.find('.post_unit_comment').removeClass().addClass('edit_form_unit_comment').attr('data-id',id);
    form.find('.remove_side_comment').removeClass().addClass('remove_form_edit_unit_comment');
    content.parent().append(form);    
    content.hide();
    content.parent().find('.comment-form').show();
});

$( 'body' ).delegate( '.remove_form_edit_unit_comment', 'click', function(event){
   $(this).parent().parent().parent().parent().find('.note,.public').show();
   $(this).closest('.comment-form').remove();
   $('.new_side_comment').removeClass('cleared');
});
$( 'body' ).delegate( '.reply_unit_comment', 'click', function(event){
    event.preventDefault();
    var parent_li = $(this).parent().parent().parent().parent();
    var $this = $(this);
    if($(this).hasClass('meta_info')){  
      var id =$(this).attr('data-meta');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'get_user_reply', 
                      security: $('#hash').val(),
                      id: id
                    },
              cache: false,
              success: function (html) {
                if(!jQuery.isNumeric(html)){
                  parent_li.after(html);
                  $this.removeClass('reply_unit_comment');
                }
              }
      });
    }else{
      $('.add-comment').trigger('click');
      $('.comment-form').addClass('creply');
      $('.comment-form').attr('data-cid',$(this).closest('.actions').parent().attr('data-id'));
    }
});

$( 'body' ).delegate( '.instructor_reply_unit_comment', 'click', function(event){
    event.preventDefault();
    if($(this).hasClass('call'))
      return false;

    var $ithis=$(this);
    var message = $ithis.parent().parent().parent().find('.unit_comment_content').html();
    var unit_id =$('#unit').attr('data-unit');
    //console.log(unit_id);
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'instructor_reply_user_comment', 
                    security: $('#hash').val(),
                    message:message,
                    id: unit_id,
                    course_id:$('#course_id').val(),
                    section:$('.side_comment.active').attr('id')
                  },
            cache: false,
            success: function (html) {
              $ithis.addClass('call');
            }
    });
});
$( 'body' ).delegate( '.edit_form_unit_comment', 'click', function(event){
    event.preventDefault();
    var $new_this = $(this);
    var id =$new_this.attr('data-id');
    var new_content = $('.new_side_comment').html();
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'edit_user_comment', 
                    security: $('#hash').val(),
                    content:new_content,
                    id: id
                  },
            cache: false,
            success: function (html) {
              var unit_id = $('#unit').attr('data-unit');
              var cookie_id='unit_comments'+unit_id;
              sessionStorage.removeItem(cookie_id);
              var new_parent =$new_this.closest('.comment-form').prev().parent();
              new_parent.find('.unit_comment_content').html(new_content);
              $new_this.closest('.comment-form').remove();
              new_parent.find('.note,.public').show();
            }
    });
});

$( 'body' ).delegate( '.remove_unit_comment', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    var id =$this.parent().parent().closest('li>div').attr('data-id');
    var note = $this.parent().parent().closest('li>div').parent();
    $this.addClass('animated spin');
    $.confirm({
        text: vibe_course_module_strings.remove_comment,
        confirm: function() {
           $.ajax({
                  type: "POST",
                  url: ajaxurl,
                  data: { action: 'remove_user_comment', 
                          security: $('#hash').val(),
                          id: id
                        },
                  cache: false,
                  success: function (html) {
                      $this.removeClass('animated');
                      $this.removeClass('spin');
                      note.remove();
                      var cid = $this.closest('.actions').attr('data-pid');
                      var count=parseInt($('#'+cid).text());
                      count--;
                      $('#'+cid).text(count);
                      $this.closest('li.loaded').fadeOut(200,function(){$(this).remove();});
                      var unit_id = $('#unit').attr('data-unit');
                      var cookie_id='unit_comments'+unit_id;
                      $('.new_side_comment').removeClass('cleared');
                      sessionStorage.removeItem(cookie_id);
                  }
          });
        },
        cancel: function() {
            $this.removeClass('animated');
            $this.removeClass('spin');
        },
        confirmButton: vibe_course_module_strings.remove_comment_button,
        cancelButton: vibe_course_module_strings.cancel
    });
});

$( 'body' ).delegate( '.post_unit_comment', 'click', function(event){
    event.preventDefault();
    if($(this).hasClass('disabled')){
      return false;
    }
    var reply =0;
    if($(this).closest('.comment-form').hasClass('creply')){
      reply = $(this).closest('.comment-form').attr('data-cid');
    }

    var $this = $(this);
    var section = $('.side_comment.active').attr('id');
    var unit_id = $('#unit').attr('data-unit');
    var list =$this.closest('.side_comments').find('ul.main_comments');
    var list_html = list.find('li.hide').clone();
    var content = $(this).closest('.comment-form').find('.new_side_comment').html();
    var cookie_id='unit_comments'+unit_id;

    $this.addClass('disabled');
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'post_unit_comment', 
                    security: $('#hash').val(),
                    course_id: $('#course_id').val(),
                    unit_id: unit_id,
                    content:content,
                    section:section,
                    reply:reply
                  },
            cache: false,
            success: function (id) {
              $this.removeClass('disabled');
               if( jQuery.isNumeric(id)){
                 var cookie_id='unit_comments'+unit_id;
                 var unit_comments = $.cookie(cookie_id);
                 var comment={
                  section:{
                    'content':content,
                    'type':'note',
                    'author':{
                      'img':list_html.find('img').attr('src'),
                      'name':list_html.find('.unit_comment_author').text(),                    
                      'link':list_html.find('.unit_comment_author').attr('href'),
                    },
                    'controls':{
                      'edit_unit_comment':1,
                      'public_unit_comment':1,
                      'instructor_reply_unit_comment':1,
                      'popup_unit_comment':1,
                      'remove_unit_comment':1
                    }
                  }
                };
                 sessionStorage.removeItem(cookie_id);
                 list_html.find('.unit_comment_content').html(content);
                 list_html.find('.note').attr('data-id',id);
                 list_html.removeClass();
                 list_html.find('.actions .private_unit_comment').parent().remove();
                 list.append(list_html);
                 var href=$(list_html).find('.popup_unit_comment').attr('data-href');
                 href +='?unit_id='+unit_id+'&section='+$('.side_comment.active').attr('id');
                 $(list_html).find('.popup_unit_comment').attr('href',href);
                 jQuery('.tip').tooltip();
                 var count=$('#'+section).text();
                 if( jQuery.isNumeric(count)){
                    count=parseInt(count)+1;
                 }else{
                   count=1;
                 }
                 $('.new_side_comment').removeClass('cleared');
                 $('#'+section).text(count);
                 $('.add-comment').fadeIn();
                 $('.comment-form').removeClass('active').fadeOut();
                 $('.new_side_comment').text(vibe_course_module_strings.add_comment);
                 $('.popup_unit_comment').magnificPopup({
                    type: 'ajax',
                    alignTop: true,
                    fixedContentPos: true,
                    fixedBgPos: true,
                    overflowY: 'auto',
                    closeBtnInside: true,
                    preloader: false,
                    midClick: true,
                    removalDelay: 300,
                    mainClass: 'my-mfp-zoom-in',
                    callbacks: {
                             parseAjax: function( mfpResponse ) {
                              mfpResponse.data = $(mfpResponse.data).find('.content');
                            }
                          }
                });
               }else{
                $this.closest('.comment-form').append('<div class="error">'+id+'</div>');
               }
            }
    });
});

$( 'body' ).delegate( '.note-tabs li', 'click', function(event){
  event.preventDefault();
  $(this).parent().find('.selected').removeClass('selected');
  $(this).addClass('selected');
   var action = $(this).attr('id');
   $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: action, 
                    security: $('#hash').val(),
                    unit_id:$.urlParam('unit_id'),
                    section:$.urlParam('section')
                  },
            cache: false,
            success: function (html) {
              $('.content').html(html);
              $(".live-edit").liveEdit({
                  afterSaveAll: function(params) {
                    return false;
                  }
              });
            }
          });
});
$( 'body' ).delegate( '#load_more_notes', 'click', function(event){
   var json = $('#notes_query').html();
   $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'load_more_notes', 
                    security: $('#hash').val(),
                    json:json
                  },
            cache: false,
            success: function (html) {
              if( jQuery.isNumeric(html)){
                $('#load_more_notes').hide();
              }else{
                var newjson = $(html).filter('#new_notes_query').html();
                $('#notes_query').html(newjson);
                $('#notes_discussions .notes_list').append(html);
                $('#new_notes_query').remove();
              }
              $(".live-edit").liveEdit({
                  afterSaveAll: function(params) {
                    return false;
                  }
              });
            }
          });
});
jQuery(document).ready(function($){
  
   jQuery('.course_curriculum.accordion .course_section').click(function(event){
       jQuery(this).toggleClass('show');
       jQuery(this).nextUntil('.course_section','.unit_description').hide(100);
       jQuery(this).nextUntil('.course_section','.course_lesson').toggleClass('show');
   });
   jQuery('.unit_description_expander').each(function(){
      var course_lesson = $(this).closest('.course_lesson');
      if(!course_lesson.next('.unit_description').length){
        $(this).remove();
      }else{
        jQuery(this).on('click',function(){
          course_lesson.next('.unit_description').toggle(200);
        });
      }
      
   });
   
   jQuery('.course_timeline.accordion .section').on('click',function(event){
           jQuery(this).toggleClass('show');
           jQuery(this).nextUntil('.section').toggleClass('show');
   });

    jQuery('.course_timeline.accordion').each(function(){
      var $this = $(this);
      var prevSections = $this.find('.unit_line.active').prevUntil('.section');
      prevSections.prev().trigger('click');
    });

   
  $('body').delegate('.retake_submit','click',function(){
    var $this = $(this);
      $.confirm({
        text: vibe_course_module_strings.confirm_course_retake,
        confirm: function() {
          $this.parent().submit();
        },
        cancel: function() {
        },
        confirmButton: vibe_course_module_strings.confirm,
        cancelButton: vibe_course_module_strings.cancel
      });
  });

  $('.unit_content').on('unit_traverse',function(){ 

    var section = $('.course_timeline.accordion').find('.unit_line.active').prev('li.section');
    if($('.course_timeline.accordion').find('.unit_line.active').prev().hasClass('section')){
      jQuery('.course_timeline.accordion .section.show').trigger('click'); // Close the open one
    }

    $('.unit_content').find('audio,video').each(function(){ 
      
      if(typeof this.player !== "undefined"){
        $('#mark-complete').trigger('media_loaded');
      }
      this.addEventListener('ended', function (e) { 
        $('#mark-complete').trigger('media_complete');
      });
    });

    if(!section.hasClass('show')){
      section.trigger('click');
    }
  });

});
/*==============================================================*/
/*======================= In Course Quiz  ========================*/
/*==============================================================*/

// IN QUIZ TIMER
$('.unit_content').on('unit_traverse',function(){
  $('.inquiz_timer').each(function(){
    $('.inquiz_timer').one('activate',function(){
        var qtime = parseInt($(this).attr('data-time'));

        var $timer =$(this).find('.timer');
        var $this=$(this);

        $timer.timer({
          'timer': qtime,
          'width' : 72 ,
          'height' : 72 ,
          'fgColor' : vibe_course_module_strings.theme_color 
        });

        $timer.on('change',function(){ 
            var countdown= $this.find('.countdown');
            var val = parseInt($timer.attr('data-timer'));
            if(val > 0){
              val--;
              $timer.attr('data-timer',val);
              var $text='';
              if(val >= 10800){
                $text = Math.floor(val/3600) + ':' + ((Math.floor((val%3600)/60) < 10)?'0'+Math.floor((val%3600)/60):Math.floor((val%3600)/60)) + '';
              }else if(val > 60){
                $text = Math.floor(val/60) + ':' + ((parseInt(val%60) < 10)?'0'+parseInt(val%60):parseInt(val%60)) + '';
              }else{
                $text = '00:'+ ((val < 10)?'0'+val:val);
              }
              countdown.html($text);
            }else{
                countdown.html(vibe_course_module_strings.timeout);
                if(!$('.submit_inquiz').hasClass('triggerred')){
                    $('.submit_inquiz').addClass('triggerred');
                    if(typeof all_questions_json !== 'undefined') {
                        wplms_submit_inquiz();
                        $timer.knob({
                            'readonly':true,
                            'max': 0,
                            'width' : 72 ,
                            'height' : 72 ,
                            'fgColor' : vibe_course_module_strings.theme_color ,
                            'readonly':true 
                        });
                    }
                } 
                $('.inquiz_timer').trigger('deactivate');
            }  
        });
        
    });

    $('.inquiz_timer').one('deactivate',function(event){
      var qtime = 0;
      var $timer =$(this).find('.timer');
      var $this=$(this);

      $timer.knob({
        'readonly':true,
        'max': qtime,
        'width' : 72 ,
        'height' : 72 ,
        'fgColor' : vibe_course_module_strings.theme_color ,
        'readonly':true 
      });
      event.stopPropagation();
    });
    // END IN QUIZ TIMER

      var qtime = parseInt($(this).attr('data-time'));
      var $timer =$(this).find('.timer');
      $timer.knob({
        'readonly':true,
        'max': qtime,
        'width' : 72 ,
        'height' : 72 ,
        'fgColor' : vibe_course_module_strings.theme_color ,
        'thickness': 0.1 ,
        'readonly':true 
      });
      if($(this).hasClass('start')){
        $('.inquiz_timer').trigger('activate');
      }
  });  
});

$( 'body' ).delegate( '.unit_button.start_quiz', 'click', function(event){
  event.preventDefault();
   var $this=$(this);
   $('#ajaxloader').removeClass('disabled');
   $('#unit_content').addClass('loading');
   if($this.hasClass('continue')){
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'in_start_quiz', 
                      security: $('#hash').val(),
                      quiz_id:$('#unit.quiz_title').attr('data-unit'),
                    },
              cache: false,
              success: function (html) {
                $('.main_unit_content').html(html);
                $('.in_quiz').trigger('question_loaded');
                $this.removeClass('start_quiz continue');
                $this.attr('href','#');
                $this.addClass('submit_inquiz');
                runnecessaryfunctions();
                $('body,html').animate({
                  scrollTop: 0
                }, 1200);
                $this.text(vibe_course_module_strings.submit_quiz);
                $this.after('<a class="save_quiz_progress"><span class="save_progress_wrap"><span class="save_progress_label">'+vibe_course_module_strings.save_quiz+'</span><span class="save_progress_bar"><span class="save_progress_inner"></span></span></span></a>');
                $('.quiz_title .inquiz_timer').trigger('activate');
                $('#ajaxloader').addClass('disabled');
                $('#unit_content').removeClass('loading');
                $('.quiz_meta').trigger('progress_check');
              }
            });
   }else{
      $.confirm({
        text: vibe_course_module_strings.start_quiz_notification,
        confirm: function() {
           $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'in_start_quiz', 
                      security: $('#hash').val(),
                      quiz_id:$('#unit.quiz_title').attr('data-unit'),
                    },
              cache: false,
              success: function (html) {
                $('#ajaxloader').addClass('disabled');
                $('#unit_content').removeClass('loading');
                $('.main_unit_content').html(html);
                $('.in_quiz').trigger('question_loaded');
                $this.removeClass('start_quiz');
                if($('.quiz_meta').hasClass('show_progress') && $(all_questions_json).length){
                  $('.quiz_meta.show_progress').attr('data-progress-ques',JSON.stringify(all_questions_json));
                }
                $('body,html').animate({
                  scrollTop: 0
                }, 1200);
                $this.attr('href','#');
                $this.addClass('submit_inquiz');
                runnecessaryfunctions();
                $this.text(vibe_course_module_strings.submit_quiz);
                $this.after('<a class="save_quiz_progress"><span class="save_progress_wrap"><span class="save_progress_label">'+vibe_course_module_strings.save_quiz+'</span><span class="save_progress_bar"><span class="save_progress_inner"></span></span></span></a>');
                $('.quiz_title .inquiz_timer').trigger('activate');
                $('.quiz_meta').trigger('progress_check');
              }
            });
        },
        cancel: function() {
          $('#ajaxloader').addClass('disabled');
          $('#unit_content').removeClass('loading');
        },
        confirmButton: vibe_course_module_strings.confirm,
        cancelButton: vibe_course_module_strings.cancel
    });
   }
});


$( 'body' ).delegate( '.unit_button.submit_inquiz', 'click', function(event){
   event.preventDefault();
   var $this=$(this);
   var answers=[];

    $('#ajaxloader').removeClass('disabled');
    $('#unit_content').addClass('loading');
    if(typeof all_questions_json !== 'undefined') {

        var unanswered_flag=0;

        if(all_questions_json.length){

          $.each(all_questions_json, function(key, value) {
              var ans = localStorage.getItem(value);
              if(ans){
                var answer={'id':value,'value':ans};
                answers.push(answer);
              }else{
                unanswered_flag++;
              }
          });

          if(unanswered_flag){
              $.confirm({
                  text: vibe_course_module_strings.unanswered_questions,
                  confirm: function() {
                      $.confirm({
                          text: vibe_course_module_strings.submit_quiz_notification,
                          confirm: function() {
                              wplms_submit_inquiz();
                          },
                          cancel: function() {
                              $('#ajaxloader').addClass('disabled');
                              $('#unit_content').removeClass('loading');
                          },
                          confirmButton: vibe_course_module_strings.confirm,
                          cancelButton: vibe_course_module_strings.cancel
                      });
                  },
                  cancel: function() {
                      $('#ajaxloader').addClass('disabled');
                      $('#unit_content').removeClass('loading');
                      return false;
                  },
                  confirmButton: vibe_course_module_strings.confirm,
                  cancelButton: vibe_course_module_strings.cancel
              });
          }else{
            $.confirm({
                text: vibe_course_module_strings.submit_quiz_notification,
                confirm: function() {
                    wplms_submit_inquiz();
                },
                cancel: function() {
                    $('#ajaxloader').addClass('disabled');
                    $('#unit_content').removeClass('loading');
                },
                confirmButton: vibe_course_module_strings.confirm,
                cancelButton: vibe_course_module_strings.cancel
            });
          }
      }else{
        $('.save_progress_inner').css('width','100%');
        end_inquiz_submission();
      } 
    }else{
        $('#ajaxloader').addClass('disabled');
        $('#unit_content').removeClass('loading');
        alert(vibe_course_module_strings.submit_quiz_error);
    }
});

$( 'body' ).delegate( '.in_quiz .pagination ul li a.quiz_page', 'click', function(event){
  event.preventDefault();
   var $this=$(this);
   var page = $(this).text();
   $('#ajaxloader').removeClass('disabled');
   $('#unit_content').addClass('loading');
   $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'in_start_quiz', 
                    security: $('#hash').val(),
                    quiz_id:$('#unit.quiz_title').attr('data-unit'),
                    page: page
                  },
            cache: false,
            success: function (html) {
              $('#ajaxloader').addClass('disabled');
              $('#unit_content').removeClass('loading');
              $('.main_unit_content').html(html);
              $('.in_quiz').trigger('question_loaded');
              $this.removeClass('start_quiz');
              $this.addClass('submit_quiz');
              runnecessaryfunctions();
              $this.text(vibe_course_module_strings.submit_quiz);
              $this.after('<a class="save_quiz_progress"><span class="save_progress_wrap"><span class="save_progress_label">'+vibe_course_module_strings.save_quiz+'</span><span class="save_progress_bar"><span class="save_progress_inner"></span></span></span></a>');
              $('body,html').animate({
                  scrollTop: 0
                }, 1200);
            }
          });
});

$( 'body' ).delegate( '.in_quiz', 'question_loaded',function(){
  runnecessaryfunctions();
  $('.quiz_meta').trigger('progress_check');
  if($('.matchgrid_options').hasClass('saved_answer')){
        var id;
        $('.matchgrid_options li').each(function(index,value){
            id = $('.matchgrid_options').attr('data-match'+index);
            $(this).append($('#'+id));
        });
    }

  jQuery('.in_question .question_options.sort').each(function(){
    var defaultanswer='1';
    var lastindex = $('ul.question_options li').size();
    if(lastindex>1)
    for(var i=2;i<=lastindex;i++){
      defaultanswer = defaultanswer+','+i;
    }

    jQuery(this).sortable({
      revert: true,
      cursor: 'move',
      refreshPositions: true, 
      opacity: 0.6,
      scroll:true,
      containment: 'parent',
      placeholder: 'placeholder',
      tolerance: 'pointer',
      update: function( event, ui ) {
          var order = jQuery(this).sortable('toArray').toString();
          var id = $(this).parent().attr('data-ques');
          localStorage.setItem(id,order);
          $('.quiz_meta').trigger('progress_check');
      }
    }).disableSelection();
  });
  //Fill in the Blank Live EDIT
  $(".live-edit").liveEdit({
      afterSaveAll: function(params) {
        return false;
      }
  });

  $('.vibe_fillblank').on('keyup',function(){ 
    value='';
    $(this).closest('.in_question').find('.vibe_fillblank').each(function(){
        value += $(this).text().trim(); 
        if(value != null ){
            value +='|';
        }
    });
    var id = $(this).closest('.in_question').attr('data-ques');
    localStorage.setItem(id,value);
    $('.quiz_meta').trigger('progress_check');
  });

  //Match question type
  $('.in_question .question_options.match').each(function(){
      $(this).droppable({
        drop: function( event, ui ){
          $(ui.draggable).removeAttr('style');
          $( this )
                .addClass( "ui-state-highlight" )
                .append($(ui.draggable))
        }
      });
      $(this).find('li').draggable({
        revert: "invalid",
        containment:$(this).parent()
      });
  });
  
  $( ".matchgrid_options li" ).droppable({
      activeClass: "ui-state-default",
      hoverClass: "ui-state-hover",
      drop: function( event, ui ){
        childCount = $(this).find('li').length;
        $(ui.draggable).removeAttr('style');
        if (childCount !=0){
            return;
        }  
        
         $( this )
            .addClass( "ui-state-highlight" )
            .append($(ui.draggable));
        var value='';
        $(this).parent().find('li.match_option').each(function(){
            var id = $(this).attr('id');
            if( jQuery.isNumeric(id))
              value +=id+',';
        });     
        var id = $(this).closest('.in_question').attr('data-ques');
        localStorage.setItem(id,value);
        $('.quiz_meta').trigger('progress_check');
      }
    });

  $('.question.largetext+textarea').on('keyup',function(){
      var value = $(this).val();
      var id = $(this).closest('.in_question').attr('data-ques');
      localStorage.setItem(id,value);
      $('.quiz_meta').trigger('progress_check');
  });
  $('.question.smalltext+input').on('keyup',function(){
      var value = $(this).val();
      var id = $(this).closest('.in_question').attr('data-ques');
      localStorage.setItem(id,value);
      $('.quiz_meta').trigger('progress_check');
  });
  $('.vibe_select_dropdown').on('change',function(){
      var id = $(this).closest('.in_question').attr('data-ques');
      var value ='';
      $(this).closest('.in_question').find('.vibe_select_dropdown').each(function(){console.log('->'+value);
        if(value){value +='|';}
        value +=$(this).val();
      });
      localStorage.setItem(id,value); 
      $('.quiz_meta').trigger('progress_check');
  });
  $('.question_options.truefalse li').click(function(){
      var id = $(this).find('input:checked').attr('name');
      var value = $(this).find('input:checked').val();
      localStorage.setItem(id,value);
      $('.quiz_meta').trigger('progress_check');
  });
  $('.question_options.single li,.question_options.survey li').click(function(){
      var id = $(this).find('input:checked').attr('name');
      var value = $(this).find('input:checked').val();
      localStorage.setItem(id,value);
      $('.quiz_meta').trigger('progress_check');
  });
  $('.question_options.multiple li').click(function(){
      var iclass= $(this).find('input:checked').attr('class');
      var id=$(this).closest('.in_question').attr('data-ques');
      var value = '';
      $(this).parent().find('input:checked').each(function(){
        value += $(this).val()+',';
      });
      localStorage.setItem(id,value);
      $('.quiz_meta').trigger('progress_check');
  });


});


$( 'body' ).delegate( '.in_quiz', 'question_loaded',function(){
  if(typeof questions_json !== 'undefined') {
    $.each(questions_json, function(key, value) { 
        $('.in_question[data-ques='+value+']').each(function(){
            var $this = $(this);
            var type = $(this).find('.question').attr('class');
            switch(type){
              case 'question match':
                var sval =localStorage.getItem(value);
                if(sval !== null){
                  var new_vals = sval.split(',');
                  $.each(new_vals,function(k,vals){
                    if($.isNumeric(vals))
                      $this.find('.matchgrid_options>ul>li').eq(k).append($('#'+vals+'.ques'+value));
                  });
                }
              break;
              case 'question sort':
                var sval =localStorage.getItem(value);
                if(sval !== null){
                  var new_vals = sval.split(',');
                  console.log(new_vals);
                  $.each(new_vals,function(k,vals){ 
                    if($.isNumeric(vals)){
                      var $option = $this.find('.question_options.sort>li#'+vals+'.ques'+value);
                      $option.remove();
                      $this.find('.question_options.sort').append($option);
                      
                    }
                  });
                }
              break;
              case 'question survey':
              case 'question single':
                var sval =localStorage.getItem(value);
                if(sval !== null)
                $(this).find('input[value="'+sval+'"]').prop( "checked", true );
              break;
              case 'question multiple':
                var sval =localStorage.getItem(value);
                if(sval !== null){
                  var new_vals = sval.split(',');
                  $.each(new_vals,function(k,vals){
                    $this.find('input[value="'+vals+'"]').prop( "checked", true );
                  });
                }
              break;
              case 'question select':
                var saved = localStorage.getItem(value);
                if(saved !== 'null' && saved){
                  if (saved.indexOf('|') >= 0){
                      var result = saved.split('|');
                      $(this).find('select').each(function(k,val){
                          $(this).val(result[k]);
                      });
                  }else{
                    $(this).find('select').val(localStorage.getItem(value));
                  }
                }else{
                  $('.vibe_select_dropdown').val('');
                }
              break;
              case 'question truefalse':
                var sval =localStorage.getItem(value);
                if(sval !== null)
                $(this).find('input[value="'+sval+'"]').prop( "checked", true );
              break;
              case 'question fillblank':
              var saved = localStorage.getItem(value);
              if(saved !== 'null' && saved){
                if (saved.indexOf('|') >= 0){
                    var result = saved.split('|');
                    $(this).find('.vibe_fillblank').each(function(k,val){
                        $(this).text(result[k]);
                    });
                }else{
                    $(this).find('.vibe_fillblank').text(saved);
                }
              }
              break;
              case 'question smalltext':
                $(this).find('input').val(localStorage.getItem(value));
              break;
              case 'question largetext':
                $(this).find('textarea').val(localStorage.getItem(value));
              break;
            }
        });
    

        //Check answers fix
        var question_id = value;
        var check_answer = localStorage.getItem('question_result_'+question_id);
        if(check_answer != null){
            var json = $.parseJSON(check_answer);
            $('.in_question[data-ques="'+question_id+'"]').addClass(json.status);
            $('.in_question[data-ques="'+question_id+'"]').append('<div class="question_wrapper loaded"><div class="result"><div class="'+json.status+'"><span></span><strong>'+json.marks+'</strong></div></div></div>');
            if(json.correct){
              var jc = json.correct;
              jc = jc.split('|').join(' '+vibe_course_module_strings.And+' ');
              if(json.explanation){
                $('.in_question[data-ques="'+question_id+'"]').append('<div class="checked_answer '+json.status+'"><strong>'+vibe_course_module_strings.correct_answer+' : '+jc+'<strong><br>'+vibe_course_module_strings.explanation+' : '+json.explanation+'</div>');
              }else{
                $('.in_question[data-ques="'+question_id+'"]').append('<div class="checked_answer '+json.status+'"><strong>'+vibe_course_module_strings.correct_answer+' : '+jc+'<strong></div>');    
              }
            }
            var new_questions_json = new Array();
            if($(all_questions_json).length){
                $.each(all_questions_json,function(i,val){
                    if(val != question_id){
                        new_questions_json.push(val);
                    }
                });
                all_questions_json = new_questions_json;
            }
            $('.in_question[data-ques="'+question_id+'"] .check_question_answer').remove();
            setTimeout(function(){$('.in_question[data-ques="'+question_id+'"]').addClass('check_answer_loaded');},100);
        }
        
        });// End each
    }
});
$( 'body' ).delegate( '.quiz_meta', 'progress_check',function(){
     if(typeof all_progress_json !== 'undefined') {
       var num = all_progress_json.length;
       var progress=0;
        $.each(all_progress_json, function(key, value) { 
          if(localStorage.getItem(value) !== null){
            progress++;
          }
        });

       if(!$('.quiz_meta').hasClass('show_progress')){
        $('.quiz_meta').addClass('show_progress');
       }
       $('.quiz_meta i span').text(progress+'/'+num);
       var percentage = Math.round(100*(progress/num));
       $('.quiz_meta .progress .bar').css('width',percentage+'%');
    }
});
/*=== In Unit Questions ===*/
$(document).ready(function($){
  
  $('.unit_content').on('unit_traverse',function(){
      
    $('.question').each(function(){
      var $this = $(this);
      jQuery('.question_options.sort').each(function(){
        jQuery(this).sortable({
          revert: true,
          cursor: 'move',
          refreshPositions: true, 
          opacity: 0.6,
          scroll:true,
          containment: 'parent',
          placeholder: 'placeholder',
          tolerance: 'pointer',
        }).disableSelection();
    });
    //Fill in the Blank Live EDIT
    $(".live-edit").liveEdit({
        afterSaveAll: function(params) {
          return false;
        }
    });

    //Match question type
    $('.question_options.match').droppable({
      drop: function( event, ui ){
      $(ui.draggable).removeAttr('style');
      $( this )
            .addClass( "ui-state-highlight" )
            .append($(ui.draggable))
      }
    });
    $('.question_options.match li').draggable({
      revert: "invalid",
      containment:'#question'
    });
    $( ".matchgrid_options li" ).droppable({
        activeClass: "ui-state-default",
        hoverClass: "ui-state-hover",
        drop: function( event, ui ){
          childCount = $(this).find('li').length;
          $(ui.draggable).removeAttr('style');
          if (childCount !=0){
              return;
          }  
          
           $( this )
              .addClass( "ui-state-highlight" )
              .append($(ui.draggable))
        }
      });
    });
  });

  //Standalone questions
  $('body').delegate('.check_answer','click',function(){ 
        var $this = $(this).closest('.question');
        $this.find('.message').remove();
        var id = $(this).attr('data-id');
        var answers = eval('ans_json'+id);
        var value;
        switch(answers['type']){
          case 'truefalse':
          case 'single':
          case 'sort':
          case 'match':
            value ='';
            if($this.find('input[type="radio"]:checked').length){
              value = $this.find('input[type="radio"]:checked').val();
            }
            $this.find('.question_options.sort li.sort_option').each(function(){
              var id = $(this).attr('id');
              if( jQuery.isNumeric(id))
                value +=id+',';
            });

            $this.find('.matchgrid_options li.match_option').each(function(){
              var id = $(this).attr('id');
              if( jQuery.isNumeric(id))
                value +=id+',';
            });

            if(answers['type'] == 'sort' || answers['type'] == 'match'){
              value = value.slice(0,-1);
            }
            answers['answer'] = answers['answer'].toLowerCase();
            if( value == answers['answer']){
                $this.append('<div class="message success">'+vibe_course_module_strings.correct+'</div>');
            }else{
                $this.append('<div class="message error">'+vibe_course_module_strings.incorrect+'</div>');
            }
          break;
          case 'smalltext':
          case 'fillblank':
          case 'select':
            value ='';
            if($this.find('input.ques').length){
              value = $this.find('input.ques').val();
            }else if($this.find('.vibe_fillblank').length){
              var textValues = $this.find('.vibe_fillblank').map(function() {
                  return $(this).text();
              }).get();
              value = textValues.join('|');
            }else if($this.find('select').length){
              var textValues = $this.find('select').map(function() {
                  return $(this).val();
              }).get();
              value = textValues.join('|');
            }
            
            value = value.toLowerCase();
            if(answers['answer'].indexOf('|') < 0){
              answers['answer'] =answers['answer'].toLowerCase();
            }
            if( value == answers['answer']){
                $this.append('<div class="message success">'+vibe_course_module_strings.correct+'</div>');
            }else{
                var temp = new Array();
                var temp2 = new Array();
                value2 = new Array();
                temp = answers['answer'].split("|");                
                var check = false;
                $.each( temp, function( key, val ) {
                  value2 = value.split("|");
                  if(typeof value2[key] ==='undefined' || value2[key] ==''){
                    check = false;
                  }
                  if(val.search(',')){
                    temp2 = val.split(",");
                  }
                  if($.inArray(value2[key],temp2) != '-1'){
                     check = true;
                   }else{
                      check = false;
                       return false;
                   }
                });
                if(check){
                  $this.append('<div class="message success">'+vibe_course_module_strings.correct+'</div>');
                }else{
                  $this.append('<div class="message error">'+vibe_course_module_strings.incorrect+'</div>');
                }
            }

          break;
          case 'multiple':
            var temp = 1;
            if($this.find('input[type="checkbox"]:checked').length){
              if($this.find('input[type="checkbox"]:checked').length == answers['answer'].length){
                $this.find('input[type="checkbox"]:checked').each(function(){
                if ($.inArray($(this).val(), answers['answer']) == -1){
                  $this.append('<div class="message error">'+vibe_course_module_strings.incorrect+'</div>');
                  temp = 0;
                  return false;
                }
            });
            
              if(temp == 1)
                $this.append('<div class="message success">'+vibe_course_module_strings.correct+'</div>');
              }else{
                $this.append('<div class="message error">'+vibe_course_module_strings.incorrect+'</div>');
              }
          }
          break;

        }
    });
  /* === simple notes and discussion ===*/
  $('.unit_content').on('unit_traverse',function(){
    $('#discussion').each(function(){

        var $this = $(this);
        $('.add_comment').click(function(){
          $('.add_unit_comment_text').toggleClass('hide');
        });
      $('body').delegate('.unit_content .commentlist li .reply','click',function(){
          var $reply = $(this);
          $reply.addClass('hide');
          $('.unit_content .commentlist li .add_unit_comment_text').remove();
          var form = $('#add_unit_comment').clone().removeClass('hide').attr('id','').appendTo($reply.parent());
          form.find('.post_question').attr('data-parent',$reply.parent().parent().parent().attr('data-id'));
          form.find('.post_question').addClass('comment_reply').text($reply.text());
          form.find('.post_question').removeClass('post_question');
          $('#discussion').trigger('ready');
      });
      $('body').delegate('.unit_content .commentlist li .cancel','click',function(e){
          if($(this).parent().parent().find('.reply').length){
            $(this).parent().parent().find('.reply').removeClass('hide');
            $(this).parent().parent().find('.add_unit_comment_text').remove();
          }
      });
      $('#add_unit_comment .cancel').click(function(){
        $('#add_unit_comment').addClass('hide');
      });
       $('.post_question').on('click',function(e){ 
            e.preventDefault(); 
            var textarea=$(this).parent().find('textarea');
            var val = textarea.val();

            $this.addClass('loading');

            if(val.length){ 
               $.ajax({
                      type: "POST",
                      url: ajaxurl,
                      data: { action: 'add_unit_comment', 
                              security: $('#hash').val(),
                              text: val,
                              unit_id: $this.attr('data-unit')
                            },
                      cache: false,
                      success: function (html) {
                          $this.removeClass('loading');
                          if(html.indexOf('<li') == 0){
                              $this.find('ol.commentlist').append(html);
                              textarea.val('');
                              $('.add_unit_comment_text').addClass('hide');
                          }else{
                            $this.append(html);
                          }
                      }
              });
            }else{
              $this.append('<div class="message">'+vibe_course_module_strings.incorrect+'</div>');
            }
        });
        $('#discussion').on('ready',function(){
            $('.comment_reply').on('click',function(e){ 
              e.preventDefault(); 
              var textarea=$(this).parent().find('textarea');
              var val = textarea.val();
              var parent = $(this).attr('data-parent');

              $this.addClass('loading');

              if(val.length){ 
                 $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: { action: 'add_unit_comment', 
                                security: $('#hash').val(),
                                text: val,
                                parent:parent,
                                unit_id: $this.attr('data-unit')
                              },
                        cache: false,
                        success: function (html) {
                            $this.removeClass('loading');
                            if(html.indexOf('<li') == 0){
                              
                                $('#comment-'+parent).append('<ul class="children">'+html+'</ul>');
                                $('#comment-'+parent +' .add_unit_comment_text').remove();
                                $('.unit_content .commentlist li .reply').removeClass('hide');
                              
                              textarea.val('');
                              $('.add_unit_comment_text').addClass('hide');
                            }else{
                              $this.append(html);
                            }
                        }
                });
              }else{
                $this.append('<div class="message">'+vibe_course_module_strings.incorrect+'</div>');
              }
          });
        });
        $('.load_more_comments').click(function(){
            var page = parseInt($(this).attr('data-page'));
            var max = parseInt($(this).attr('data-max'));
            var $load = $(this);
            $this.addClass('loading');
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'load_unit_comments', 
                            security: $('#hash').val(),
                            page: page,
                            unit_id: $this.attr('data-unit')
                          },
                    cache: false,
                    success: function (html) {
                        $this.removeClass('loading');
                        $this.find('.commentlist').append(html);
                        var count = parseInt($load.find('span').text());
                        var per = parseInt($load.attr('data-per'));
                        count = count -per;
                        page++;
                        $load.attr('data-page',page);
                        $load.find('span').text(count);
                        if(max <= page)
                          $load.hide(200);
                    }
            });
        });


    });
  });
  if($('.unit_content').length){
    $('.unit_content').trigger('unit_traverse');
  }    
});

/*== COURSE LIVE SEARCH ==*/
$('#course_user_ajax_search_results').each(function(){
    var xhr;
    var $this = $(this);
    var view = 0;
    if($('body').hasClass('admin')){view='admin';}
    $('#active_status,#course_status').on('change',function(event){
        var value = $(this).val();
        $('ul.course_students').addClass('loading');
        xhr = $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'search_course_members', 
                    security: $('#bulk_action').val(),
                    active_status:$('#active_status').val(),
                    course_status:$('#course_status').val(),
                    s: $('#search_course_member input').val(),
                    course_id: $('#course_user_ajax_search_results').attr('data-id'),
                    view: view
                  },
            cache: false,
            success: function (html) {
                $('ul.course_students').removeClass('loading');
                $('ul.course_students').html(html);
            }
        });
    });
    
    $('#search_course_member input').on('keyup',function(event){
        var value = $(this).val();
        if(xhr && xhr.readyState != 4){
            xhr.abort();
        }
        if(value.length >= 4){
            $this.addClass('loading');
            $('ul.course_students').addClass('loading');
            xhr = $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'search_course_members', 
                      security: $('#bulk_action').val(),
                      active_status:$('#active_status').val(),
                      course_status:$('#course_status').val(),
                      s: $('#search_course_member input').val(),
                      course_id: $('#course_user_ajax_search_results').attr('data-id'),
                      view: view
                    },
                cache: false,
                success: function (html) {
                    $('ul.course_students').removeClass('loading');
                    $this.removeClass('loading');
                    $('ul.course_students').html(html);
                }
            });
        }
    }); 
    $('#search_course_member input').on('blur',function(event){
        var value = $(this).val();
        $('ul.course_students').addClass('loading');
        xhr = $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'search_course_members', 
                    security: $('#bulk_action').val(),
                    active_status:$('#active_status').val(),
                    course_status:$('#course_status').val(),
                    s: $('#search_course_member input').val(),
                    course_id: $('#course_user_ajax_search_results').attr('data-id'),
                    view: view
                  },
            cache: false,
            success: function (html) {
                $('ul.course_students').removeClass('loading');
                $('ul.course_students').html(html);
            }
        });
    });
});

$('body').on('click','.course_admin_paged',function(){
    $('ul.course_students').addClass('loading');
    var view = '';
    if($('body').hasClass('admin')){view='admin';}
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'search_course_members', 
                security: $('#bulk_action').val(),
                active_status:$('#active_status').val(),
                course_status:$('#course_status').val(),
                s: $('#search_course_member input').val(),
                page:$(this).text(),
                course_id: $('#course_user_ajax_search_results').attr('data-id'),
                view: view
              },
        cache: false,
        success: function (html) {
            $('ul.course_students').removeClass('loading');
            $('ul.course_students').html(html);
        }
    });
});

  $( 'body' ).delegate( '#question,.in_quiz', 'question_loaded',function(){

      $('.check_question_answer').on('click',function(event){
          event.preventDefault();
          var $this = $(this);
          if($this.hasClass('disabled'))
            return;

          $this.addClass('disabled');
          $this.prepend('<i class="fa fa-spinner animate load spin"></i>');
          var answer = localStorage.getItem($this.attr('data-question'));
          var question_id = $this.attr('data-question');
          $.ajax({
              type: "POST",
              url: ajaxurl,
              dataType: "json",
              data: { action: 'check_question_answer', 
                      security: $this.attr('data-security'),
                      quiz_id:$this.attr('data-quiz'),
                      question_id:question_id,
                      answer:answer
                    },
              cache: false,
              success: function (json) {

                  if(json.hasOwnProperty('status')){
                    localStorage.setItem('question_result_'+question_id,JSON.stringify(json));
                    $('#question,.in_question').addClass(json.status);
                    $('.quiz_meta').trigger('progress_check');
                    

                    $('#ques'+question_id).addClass(json.status);
                    $('.in_question[data-ques="'+question_id+'"]').addClass(json.status);

                    $('#question').after('<div class="question_wrapper"><div class="result"><div class="'+json.status+'"><span></span><strong>'+json.marks+'</strong></div></div></div>');
                    if(json.correct){
                      var jc = json.correct;
                      jc = jc.split('|').join(' '+vibe_course_module_strings.And+' ');
                      if(json.explanation){
                        $('#question,.in_question[data-ques="'+question_id+'"]').append('<div class="checked_answer '+json.status+'"><strong>'+vibe_course_module_strings.correct_answer+' : '+jc+'<strong><br>'+vibe_course_module_strings.explanation+' : '+json.explanation+'</div>');
                      }else{
                        $('#question,.in_question[data-ques="'+question_id+'"]').append('<div class="checked_answer '+json.status+'"><strong>'+vibe_course_module_strings.correct_answer+' : '+jc+'<strong></div>');    
                      }
                    }

                    $('.in_question[data-ques="'+question_id+'"]').append('<div class="question_wrapper"><div class="result"><div class="'+json.status+'"><span></span><strong>'+json.marks+'</strong></div></div></div>');
                    setTimeout(function(){$('#question+.question_wrapper, .in_question[data-ques="'+question_id+'"] .question_wrapper').addClass('loaded');$('#question,.in_question').addClass(json.status);},100);
                    setTimeout(function(){$('#question,.in_question[data-ques="'+question_id+'"]').addClass('check_answer_loaded');},500);
                    $this.remove();
                    }else{
                      $('#question,.in_question[data-ques="'+question_id+'"]').append('<div class="message">'+json.message+'</div>');
                      $this.remove();
                    }    

                    var new_questions_json = new Array();;
                    if($(all_questions_json).length){
                        $.each(all_questions_json,function(i,val){
                            if(val != question_id){
                                new_questions_json.push(val);
                            }
                        });
                        all_questions_json = new_questions_json;
                        console.log(all_questions_json);
                    }  
                }
            });
        });
    });

//tours code
  //end start tour calls in profile
  $(document).ready(function(){
    //end tour call
    $('body').on('click','.end_tour',function(event){
      event.preventDefault();
      var $this = $(this);
      if($this.hasClass('disabled')) return false;
      $this.prepend('<i class="fa fa-spinner animated spin"></i>');

      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'end_tour_for_user', 
                vibe_tour_security: vibe_course_module_strings.security,
                tour_name:$this.attr('data-action-point')
              },
        cache: false,
        success: function (html) {
          $this.addClass('disabled');
          $this.html('<i class="fa fa-check"></i>');
        }
      });
    });

    //start tour call
    $('body').on('click','.start_tour',function(event){
      event.preventDefault();
      var $this = $(this);
      $this.prepend('<i class="fa fa-spinner animated spin"></i>');
      var courses = '';
      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'start_tour_for_user', 
                vibe_tour_security: vibe_course_module_strings.security,
                tour_name:$this.attr('data-action-point')
              },
        cache: false,
        success: function (html) {
          courses = html;
        }

        
      }).done(function(){
        localStorage.removeItem($this.attr('data-action-point')+'_current_step');
        localStorage.removeItem($this.attr('data-action-point')+'_end');
        $this.find('i.fa-spinner').remove();
        $.magnificPopup.open({
            items: {
                src: '<div class="white-popup">'+courses+'</div>',
                type: 'inline'
            }
        });
      });
    });
    
    //trigger start tour go button 
    $('body').delegate('.start_course_tour','click',function(){
      var $this = $(this);
      var link = $this.parent().find('.course_select').val();
      window.location.href = link;
    });
  });
})(jQuery);
function end_tour_wplms(tour_name){
    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        async: true,
        data: { action: 'end_tour_for_user', 
                vibe_tour_security: vibe_course_module_strings.security,
                tour_name:tour_name
              },
    });
}
/*course video */
function init_maginific_popup_course_video(){
  jQuery('.course_video_popup').each(function(){
    var $this = jQuery(this);
     $this.magnificPopup({
        type: 'iframe',
        mainClass: 'mfp-fade',
        removalDelay: 160,
        preloader: true,
        fixedContentPos: false
      });
  });
}

jQuery(document).ready(function(){
  init_maginific_popup_course_video();
  jQuery('.course_filters').on('course_filter',function(){
    init_maginific_popup_course_video();
  });
  jQuery('#buddypress').on('bp_filter_request',function(){
    init_maginific_popup_course_video();
  });
  jQuery('body').on('grid_scroll_done',function(){
    init_maginific_popup_course_video();
  });
  
});
