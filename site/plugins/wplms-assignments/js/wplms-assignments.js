window.jQuery&&function(e){e.fn.MultiFile=function(t){if(0==this.length)return this;if("string"==typeof arguments[0]){if(this.length>1){var i=arguments;return this.each(function(){e.fn.MultiFile.apply(e(this),i)})}return e.fn.MultiFile[arguments[0]].apply(this,e.makeArray(arguments).slice(1)||[]),this}var t=e.extend({},e.fn.MultiFile.options,t||{});e("form").not("MultiFile-intercepted").addClass("MultiFile-intercepted").submit(e.fn.MultiFile.disableEmpty),e.fn.MultiFile.options.autoIntercept&&(e.fn.MultiFile.intercept(e.fn.MultiFile.options.autoIntercept),e.fn.MultiFile.options.autoIntercept=null),this.not(".MultiFile-applied").addClass("MultiFile-applied").each(function(){window.MultiFile=(window.MultiFile||0)+1;var i=window.MultiFile,a={e:this,E:e(this),clone:e(this).clone()};"number"==typeof t&&(t={max:t});var n=e.extend({},e.fn.MultiFile.options,t||{},(e.metadata?a.E.metadata():e.meta?a.E.data():null)||{},{});n.max>0||(n.max=a.E.attr("maxlength")),n.max>0||(n.max=(String(a.e.className.match(/\b(max|limit)\-([0-9]+)\b/gi)||[""]).match(/[0-9]+/gi)||[""])[0],n.max=n.max>0?String(n.max).match(/[0-9]+/gi)[0]:-1),n.max=new Number(n.max),n.accept=n.accept||a.E.attr("accept")||"",n.accept||(n.accept=a.e.className.match(/\b(accept\-[\w\|]+)\b/gi)||"",n.accept=new String(n.accept).replace(/^(accept|ext)\-/i,"")),e.extend(a,n||{}),a.STRING=e.extend({},e.fn.MultiFile.options.STRING,a.STRING),e.extend(a,{n:0,slaves:[],files:[],instanceKey:a.e.id||"MultiFile"+String(i),generateID:function(e){return a.instanceKey+(e>0?"_F"+String(e):"")},trigger:function(t,i){var n=a[t],l=e(i).attr("value");if(n){var r=n(i,l,a);if(null!=r)return r}return!0}}),String(a.accept).length>1&&(a.accept=a.accept.replace(/\W+/g,"|").replace(/^\W|\W$/g,""),a.rxAccept=new RegExp("\\.("+(a.accept?a.accept:"")+")$","gi")),a.wrapID=a.instanceKey+"_wrap",a.E.wrap('<div class="MultiFile-wrap" id="'+a.wrapID+'"></div>'),a.wrapper=e("#"+a.wrapID),a.e.name=a.e.name||"file"+i+"[]",a.list||(a.wrapper.append('<div class="MultiFile-list" id="'+a.wrapID+'_list"></div>'),a.list=e("#"+a.wrapID+"_list")),a.list=e(a.list),a.addSlave=function(t,n){a.n++,t.MultiFile=a,n>0&&(t.id=t.name=""),n>0&&(t.id=a.generateID(n)),t.name=String(a.namePattern.replace(/\$name/gi,e(a.clone).attr("name")).replace(/\$id/gi,e(a.clone).attr("id")).replace(/\$g/gi,i).replace(/\$i/gi,n)),a.max>0&&a.n-1>a.max&&(t.disabled=!0),a.current=a.slaves[n]=t,t=e(t),t.val("").attr("value","")[0].value="",t.addClass("MultiFile-applied"),t.change(function(){if(e(this).blur(),!a.trigger("onFileSelect",this,a))return!1;var i="",l=String(this.value||"");a.accept&&l&&!l.match(a.rxAccept)&&(i=a.STRING.denied.replace("$ext",String(l.match(/\.\w{1,4}$/gi))));for(var r in a.slaves)a.slaves[r]&&a.slaves[r]!=this&&a.slaves[r].value==l&&(i=a.STRING.duplicate.replace("$file",l.match(/[^\/\\]+$/gi)));var c=e(a.clone).clone();return c.addClass("MultiFile"),""!=i?(a.error(i),a.n--,a.addSlave(c[0],n),t.parent().prepend(c),t.remove(),!1):(e(this).css({position:"absolute",top:"-3000px"}),t.after(c),a.addToList(this,n),a.addSlave(c[0],n+1),a.trigger("afterFileSelect",this,a)?void 0:!1)}),e(t).data("MultiFile",a)},a.addToList=function(t,i){if(!a.trigger("onFileAppend",t,a))return!1;var n=e('<div class="MultiFile-label"></div>'),l=String(t.value||""),r=e('<span class="MultiFile-title" title="'+a.STRING.selected.replace("$file",l)+'">'+a.STRING.file.replace("$file",l.match(/[^\/\\]+$/gi)[0])+"</span>"),c=e('<a class="MultiFile-remove" href="#'+a.wrapID+'">'+a.STRING.remove+"</a>");return a.list.append(n.append(c," ",r)),c.click(function(){return a.trigger("onFileRemove",t,a)?(a.n--,a.current.disabled=!1,a.slaves[i]=null,e(t).remove(),e(this).parent().remove(),e(a.current).css({position:"",top:""}),e(a.current).reset().val("").attr("value","")[0].value="",a.trigger("afterFileRemove",t,a)?!1:!1):!1}),a.trigger("afterFileAppend",t,a)?void 0:!1},a.MultiFile||a.addSlave(a.e,0),a.n++,a.E.data("MultiFile",a)})},e.extend(e.fn.MultiFile,{reset:function(){var t=e(this).data("MultiFile");return t&&t.list.find("a.MultiFile-remove").click(),e(this)},disableEmpty:function(t){t=("string"==typeof t?t:"")||"mfD";var i=[];return e("input:file.MultiFile").each(function(){""==e(this).val()&&(i[i.length]=this)}),e(i).each(function(){this.disabled=!0}).addClass(t)},reEnableEmpty:function(t){return t=("string"==typeof t?t:"")||"mfD",e("input:file."+t).removeClass(t).each(function(){this.disabled=!1})},intercepted:{},intercept:function(t,i,a){var n,l;if(a=a||[],a.constructor.toString().indexOf("Array")<0&&(a=[a]),"function"==typeof t)return e.fn.MultiFile.disableEmpty(),l=t.apply(i||window,a),setTimeout(function(){e.fn.MultiFile.reEnableEmpty()},1e3),l;t.constructor.toString().indexOf("Array")<0&&(t=[t]);for(var r=0;r<t.length;r++)n=t[r]+"",n&&function(t){e.fn.MultiFile.intercepted[t]=e.fn[t]||function(){},e.fn[t]=function(){return e.fn.MultiFile.disableEmpty(),l=e.fn.MultiFile.intercepted[t].apply(this,arguments),setTimeout(function(){e.fn.MultiFile.reEnableEmpty()},1e3),l}}(n)}}),e.fn.MultiFile.options={accept:"",max:-1,namePattern:"$name",STRING:{remove:"x",denied:"You cannot select a $ext file.\nTry again...",file:"$file",selected:"File selected: $file",duplicate:"This file has already been selected:\n$file"},autoIntercept:["submit","ajaxSubmit","ajaxForm","validate","valid"],error:function(e){alert(e)}},e.fn.reset=function(){return this.each(function(){try{this.reset()}catch(e){}})}}(jQuery);

jQuery(document).ready(function($){

  $('input[type="file"].multi').MultiFile({ 
      STRING: { 
        remove:'<i class="icon-x"></i>',
        denied:wplms_assignment_messages.incorrect_file_format+' $ext',
        file:'$file',
        selected:'File selected: $file',
        duplicate:wplms_assignment_messages.duplicate_file 
      }, 
  }); 
  $('.assignment_timer').each(function(){
      var qtime = parseInt($(this).attr('data-time'));
      var $timer =$(this).find('.timer');
      $timer.knob({
        'readonly':true,
        'max': qtime,
        'width' : 200 ,
        'height' : 200 ,
        'fgColor' : vibe_course_module_strings.theme_color,
        'bgColor' : vibe_course_module_strings.single_dark_color,
        'thickness': 0.2 ,
        'readonly':true 
      });
      if($(this).hasClass('start')){
        $(this).trigger('activate');
      }
  });

  $('.assignment_timer.start').each(function(){
    var qtime = parseInt($(this).attr('data-time'));

    var $timer =$(this).find('.timer');
    var $this=$(this);
    
    $timer.timer({
      'timer': qtime,
      'width' : 200 ,
      'height' : 200 ,
      'fgColor' : vibe_course_module_strings.theme_color, 
      'bgColor' : vibe_course_module_strings.single_dark_color, 
    });

    var $timer =$(this).find('.timer');

    $timer.on('change',function(){
        var countdown= $this.find('.countdown');
        var val = parseInt($timer.attr('data-timer'));
        if(val > 0){
          val--;
          $timer.attr('data-timer',val);

          var $text='';

          if(val > 3600){
            var mins = Math.floor((val%3600)/60);  
            $text = Math.floor(val/3600) + ':' + ((mins < 10)?'0'+mins:mins) + '';
          }else{
            var mins = Math.floor((val%3600)/60);  
            $text = '00:'+ ((mins < 10)?'0'+mins:mins);
          }
          countdown.html($text);
        }else{
            countdown.html(vibe_course_module_strings.timeout);
            $('#submit').hide(200).remove();
            $('.assignment_timer').trigger('end');
        }  
    });
    
  });

  $('.assignment_timer').one('end',function(){
    var qtime = parseInt($(this).attr('data-time'));
    var $timer =$(this).find('.timer');
    var $this=$(this);
    
    $timer.knob({
        'readonly':true,
        'max': qtime,
        'width' : 200 ,
        'height' : 200 ,
        'fgColor' : vibe_course_module_strings.theme_color, 
        'bgColor' : "#232b2d",
        'thickness': 0.2 ,
        'readonly':true 
      });
    event.stopPropagation();
  });



  $('#clear_previous_submissions').click(function(event){
      event.preventDefault();
      var $this = $(this);
      var defaulttxt = $this.html();
      $this.prepend('<i class="fa fa-spinner animated spin"></i>');
      $.confirm({
          text: wplms_assignment_messages.remove_attachment,
          confirm: function() {
            $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'clear_previous_submissions', 
                      id: $this.attr('data-id'),
                      security: $this.attr('data-security')
                    },
              cache: false,
              success: function (html) {
                  $this.find('i.fa').remove();
                  $this.html(html);
                   setTimeout(function(){location.reload();}, 3000);
              }
            });
          },
          cancel: function() {
              $this.find('i.fa').remove();
          },
          confirmButton: vibe_course_module_strings.confirm,
          cancelButton: vibe_course_module_strings.cancel
      });
      

  });

  $('#assignment').on('loaded',function(){
      $('.reset_assignment_user').click(function(event){
        event.preventDefault();
        var assignment_id=$(this).attr('data-assignment');
        var user_id=$(this).attr('data-user');
        $(this).addClass('animated spin');
        var $this = $(this);
        $.confirm({
            text: wplms_assignment_messages.assignment_reset,
            confirm: function() {

        $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'wplms_reset_assignment', 
                        security: $('#asecurity').val(),
                        id: assignment_id,
                        user: user_id
                      },
                cache: false,
                success: function (html) {
                    $(this).removeClass('animated');
                    $(this).removeClass('spin');
                    $('#message').html(html);
                    $('#as'+user_id).fadeOut('fast');
                }
        });
        }, 
         cancel: function() {
              $this.removeClass('animated');
              $this.removeClass('spin');
          },
          confirmButton: wplms_assignment_messages.assignment_reset_button,
          cancelButton: wplms_assignment_messages.cancel
        });
    });

    $('.evaluate_assignment_user').click(function(event){
      event.preventDefault();
      var assignment_id=$(this).attr('data-assignment');
      var user_id=$(this).attr('data-user');
      $(this).addClass('animated spin');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'evaluate_assignment', 
                      security: $('#asecurity').val(),
                      id: assignment_id,
                      user: user_id
                    },
              cache: false,
              success: function (html) {
                  $(this).removeClass('animated');
                  $(this).removeClass('spin');
                  $('.assignment_students').html(html);
                  $('.tab-pane#assignment').trigger('evaluate_assignment_loaded');
              }
        });
    }); 
  });

  $('body').delegate('#give_assignment_marks','click',function(event){
    event.preventDefault();
    var $this=$(this);
      var ansid=$this.attr('data-ans-id');
      var aval = $('#assignment_marks').val();
      var message = $('#remarks_message').val();
      $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'give_assignment_marks', 
                      id: ansid,
                      aval: aval,
                      message:message
                    },
              cache: false,
              success: function (html) {
                  $this.find('i').remove();
                  $this.html(wplms_assignment_messages.marks_saved);
              }
      });
  });
});

