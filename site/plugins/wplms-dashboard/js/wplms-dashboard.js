
jQuery(document).ready(function($){

  $('.announcements li').each(function(){

      var count = parseInt($('.announcement_message > p > strong').text());
      var course_id=$(this).attr('data-course');
      var cookieValue = $.cookie('announcement'+course_id);
      if(cookieValue != null && cookieValue == $(this).text()){
        $(this).remove();
        count--;
        $('.announcement_message > p > strong').text(count);
        $('.announcement_message').attr('data-count',count);
        if($('.announcements li').length <=0)
          $('.announcements').remove();
        if(count <= 0)
          $('.announcement_message').remove();
      }
    
  });
});
jQuery(document).ready(function($){

  $('body').delegate('.usergroup-dropdown','change',function(event){
    event.preventDefault();
      $.ajax({
              dataType: "json",
              type: "POST",
              url: ajaxurl,
              data: { action: $(this).val(), 
                      security: $('#security').val()
                    },
              cache: false,
              success: function (data) {
                if($('.ms-ctn.form-control').length){ 
                  var element='<input type="text" name="to" class="input-text to usergroup-filter"/>';
                  $('.input-text.subject').before(element);
                  $('.ms-ctn.form-control').remove();
                }
                $('.usergroup-filter').magicSuggest({
                  data:data,
                  placeholder: wplms_dashboard_strings.select_recipients,
                  renderer: function(data){
                        return '<div style="select_element">' +
                            '<div class="select_thumb">'+data.pic+'</div>' +
                            '<div class="select_member">' +
                                '<div class="select_member_name">' + data.name + '</div>' +
                            '</div>' +
                        '</div><div style="clear:both;"></div>'; // make sure we have closed our dom stuff
                    },
                  valueField: 'id',
                  resultAsString: true  
              });
              }
      });
  });

  $('body').delegate('#dash_contact_form_submit','click',function(event){
   event.preventDefault();
   var $this=$(this);
   var default_text=$this.text();
    var $form = $('.dash-content-form');
    var to=[];
    $form.find('input[name="to[]"]').each(function(){
       to.push($(this).val());
    });
    //console.log(JSON.stringify(to));
    
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'dash_contact_message', 
                security: $('#security').val(),
                to : JSON.stringify(to),
                subject: $('.subject').val(),
                message: $('.form_message').val()
              },
        cache: false,
        success: function (data) {
          $this.html(data);
          if(data.indexOf('Message sent to') != -1){
              $('.subject').val('');$('.form_message').val('');
          }
          setTimeout(function(){$this.text(default_text);}, 2000);
        }
      });
  });
  $('body').delegate('.remove_announcement','click',function(event){
      var $this = $(this);
     $.confirm({
        text: vibe_course_module_strings.remove_announcement,
        confirm: function() {
           $.ajax({
                  type: "POST",
                  url: ajaxurl,
                  data: { action: 'remove_announcement', 
                          security: $('#security').val(),
                          id: $this.parent().attr('data-course')
                        },
                  cache: false,
                  success: function (html) {
                    if( jQuery.isNumeric(html)){
                      $this.parent().remove();
                    }else{
                      $('.my_anouncements').before('<p class="error">'+html+'</p>');
                    }
                  }
          });
        },
        cancel: function() {
        },
        confirmButton: vibe_course_module_strings.confirm,
        cancelButton: vibe_course_module_strings.cancel
    });
  });
  // TASK LIST
  $('.add_new a').click(function(){
      var val =$(this).parent().find('.add_new_task').val();
      if(val.length){
        var list_item = '<li><a class="task-status normal"></a><p>'+val+'</p><span>'+$(this).parent().find('span').text()+'</span>';
        $('.task_list').append(list_item);
        $(this).parent().find('.add_new_task').val('');
      }
  });

  $('.add_new_task').keypress(function (e) {
   var key = e.which;
   if(key == 13){
      $('.add_new a').click();
      return false;  
    }
  }); 

  $('body').delegate('.task-status','click',function(){
      var $clone = $('.select-task-status').clone();
      $(this).before($clone);
  });
  $('body').delegate('.task_list .select-task-status a','click',function(event){
    event.preventDefault();

      var capture=$(this).attr('class');
      if(capture === 'remove'){
          $(this).parent().parent().parent().remove();
      }else{  
        var taskstatus = $(this).parent().parent().parent().find('.task-status');
        taskstatus.removeClass();
        taskstatus.addClass('task-status');
        taskstatus.addClass(capture);
        $(this).closest('.select-task-status').remove();
      }
  });
  $('.dash-task-list ul.task_list').sortable({
          revert: true,
          cursor: 'move',
          refreshPositions: true, 
          opacity: 0.6,
          scroll:true,
          containment: 'parent',
          placeholder: 'placeholder',
          tolerance: 'pointer',
    });
  $('.save_tasks').click(function(){
      var $this = $(this);
      var defaulttxt = $this.html();
      $this.addClass('loading');
      $this.text(wplms_dashboard_strings.saving);

      var data;
      var tasks = [];
      $('.task_list li').each(function(){
        var status=$(this).find('.task-status').attr('class').split(' ');
         data = { 
                 status:status[1],
                 text:$(this).find('p').text(),
                 date:$(this).find('span').text()
             };
          tasks.push(data);   
      });
      
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { 
                      action: 'save_tasks', 
                      security: $('#security').val(),
                      tasks:JSON.stringify(tasks)
                    },
              cache: false,
              success: function (html) {
                $this.removeClass('loading');
                if($.isNumeric(html)){
                    $this.text(wplms_dashboard_strings.saved);
                }else{
                    $this.text(html);
                }
                setTimeout(function(){$this.html(defaulttxt);}, 2000);
              }
      });
  });

  $('.news_block').flexslider({
    animation: "slide",
    controlNav: false,
    directionNav: true,
    animationLoop: false,
    slideshow: false,
    itemMargin: 30,
    prevText: "<i class='icon-arrow-1-left'></i>",
    nextText: "<i class='icon-arrow-1-right'></i>",
     start: function() {
               $(this).removeClass('loading');
           }    
  });

  
    $('body').delegate('.instructor-stats-courses .list-stats','click',function(event){
      event.preventDefault();
      $('.list-stats').removeClass('active');
      var $this = $(this);
      var id = $(this).attr('data-id');
      $('#instructor_stats').addClass('loading');
      $('#instructor_stats').html('');
      $.ajax({
              dataType: 'JSON',
              type: 'POST',
              url: ajaxurl,
              data: { 
                      action: 'generate_ranges', 
                      security: $('#security').val(),
                      id:id,
                      range:$('#stats-title').attr('data-range')
                    },
              cache: false,
              success: function (data) {
                console.log(data);
                $('#instructor_stats').removeClass('loading');
                $this.addClass('active');
                if($.isArray(data)){
                new Morris.Bar({
                      element: 'instructor_stats',
                      data: data,
                      xkey: 'range',
                      ykeys: ['value'],
                      labels: [wplms_dashboard_strings.students],
                      barColors: ['#23b7e5'],
                      xLabelAngle: 60,
                      lineWidth: 1,
                      resize:true,
                      parseTime: false
                    });
                }else{
                  $('#instructor_stats').html(data);
                }
            } 
      });
  });
  $('body').delegate('.instructor-stats-courses .list-recalculate-stats','click',function(event){
      event.preventDefault();
      var $this = $(this);
      var course_id = $(this).attr('data-id');
      $.ajax({
              type: 'POST',
              url: ajaxurl,
              data: { action: 'calculate_stats_course', 
                      security: $('#security').val(),
                      id: course_id
                    },
              cache: false,
              success: function (data) {
                if (data.indexOf("Unable") >= 0){
                  $this.addClass('unable');
                }else{
                  $this.addClass('success');
                }
            } 
      });
  });
  $('body').delegate('.instructor-stats-courses li.list-sub:not(.loaded)','click',function(){
        var id = $(this).attr('data-id');
        var $this=$(this);
        $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: { 
                        action: 'load_quiz_assignment_list', 
                        security: $('#security').val(),
                        id:id
                      },
                cache: false,
                success: function (html) {
                  $this.parent().after(html);
                  $this.addClass('loaded');
                  $this.find('i').addClass('icon-minus');
                }
        });
    });
  $('body').delegate('.instructor-stats-courses li.list-sub.loaded','click',function(event){
      $(this).parent().parent().find('.qa_list').toggle(200);
      $(this).find('i').toggleClass('icon-minus');
  });
  $('#submit_announcement').click(function(){
      var $this=$(this);
      if($this.hasClass('disabled'))
        return;
      var default_text=$this.text();
      $this.addClass('disabled');
      $this.html('<i class="fa fa-spinner animated spin"></i> '+default_text);
      /*var email = 0;
      if($('#email_announcement:checked').length)
        email=1;
      */
        $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: { 
                        action: 'send_announcements', 
                        security: $('#security').val(),
                        announcement:$('#add_announcement').val(),
                        course_list:$('#course_list').val(),
                        student_type: $('#student_type').val()
                        //email:email
                      },
                cache: false,
                success: function (html) {
                  $this.text(html);
                  setTimeout(function(){$this.text(default_text);}, 2000);
                }
        });
  });

  $('.announcement_message > span').click(function(){
    $('.announcements').toggle('300');
  });
  $('.announcements li > span').click(function(){
    var count = $('.announcement_message').attr('data-count');
    console.log(count);
    var li = $(this).parent();
    var course_id = li.attr('data-course');
    $.cookie('announcement'+course_id,li.text(), { expires: 1 ,path: '/'});
    li.remove();
    count--;
    $('.announcement_message').attr('data-count',count);
    if(count <= 0)
       $('.announcement_message').remove();
  });
  if(jQuery('#instructor_commissions').length){
        Morris.Line({
            element: 'instructor_commissions',
            data: instructor_commission_data,
            xkey: 'date',
            ykeys: ['sales','commission'],
            labels: [wplms_dashboard_strings.earnings,wplms_dashboard_strings.payout],
            lineColors: ['#23b7e5','#fa7252'],
            lineWidth: 1,
            resize:true,
            parseTime: false
          });
        Morris.Donut({
          element: 'commission_breakup',
          data: commission_breakup,
          colors:['#7266ba','#23b7e5','#f05050','#fad733','#27c24c','#fa7252']
        });
  }

  $('body').delegate('.commission_reload','click',function(event){
    var $this=$(this);
    event.preventDefault();
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'generate_commission_data', 
                      security: $('#security').val()
                    },
              cache: false,
              success: function (data) {
                  if($.isNumeric(data)){
                    setTimeout(function(){$this.append(wplms_dashboard_strings.stats_calculated);}, 2000);
                    window.location.reload();
                  }else{
                    setTimeout(function(){$this.append(data);}, 2000);
                  }
              }
      });
  });
});


