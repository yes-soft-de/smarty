jQuery(document).ready(function ($) {

    /**
     * Export screen JS
     */
    var PMS_Export = {

        init: function () {
            this.submit();
            this.dismiss_message();
        },

        submit: function () {

            var self = this;

            $(document.body).on('submit', '.pms-export-form', function (e) {
                e.preventDefault();

                var submitButton = $(this).find('input[type="submit"]');

                if (!submitButton.hasClass('button-disabled')) {

                    var data = $(this).serialize();

                    submitButton.addClass('button-disabled');
                    $(this).find('.notice-wrap').remove();
                    $(this).append('<div class="notice-wrap"><span class="spinner is-active"></span><div class="pms-progress"><div></div></div></div>');

                    // start the process
                    self.process_step(1, data, self);

                }

            });
        },

        process_step: function (step, data, self) {

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    form: data,
                    action: 'pms_do_ajax_export',
                    step: step,
                },
                dataType: "json",
                success: function (response) {
                    if ('done' == response.step || response.error || response.success) {

                        // We need to get the actual in progress form, not all forms on the page
                        var export_form = $('.pms-export-form').find('.pms-progress').parent().parent();
                        var notice_wrap = export_form.find('.notice-wrap');

                        export_form.find('.button-disabled').removeClass('button-disabled');

                        if (response.error) {

                            var error_message = response.message;
                            notice_wrap.html('<div class="updated error"><p>' + error_message + '</p></div>');

                        } else if (response.success) {

                            var success_message = response.message;
                            notice_wrap.html('<div id="pms-batch-success" class="updated notice is-dismissible"><p>' + success_message + '<span class="notice-dismiss"></span></p></div>');

                        } else {

                            notice_wrap.remove();
                            //console.log(response);
                            window.location = response.url;

                        }

                    } else {
                        $('.pms-progress div').animate({
                            width: response.percentage + '%',
                        }, 50, function () {
                            // Animation complete.
                        });
                        self.process_step(parseInt(response.step), data, self);
                    }

                }
            }).fail(function (response) {
                if (window.console && window.console.log) {
                    console.log(response);
                }
            });

        },

        dismiss_message: function () {
            $(document.body).on('click', '#pms-batch-success .notice-dismiss', function () {
                $('#pms-batch-success').parent().slideUp('fast');
            });
        }

    };
    PMS_Export.init();

    /**
     * Export screen JS
     */
    var PmsAddUserMetaToExport = {
        init: function () {
            this.init_add();
            this.init_remove();
            this.init_chosen();
        },
        init_add: function() {
            var self = this;
            var selector = document.querySelector('#pms-add-meta-button');
            if(selector !== null) {
                selector.onclick = function(e){
                    e.preventDefault();

                    var container = document.querySelector('#pms-add-meta-key-container');
                    var template = document.querySelector('#pms-add-meta-row-tpl');
                    var clone = document.importNode(template.content, true);
                    container.appendChild(clone);

                    // re-initialize the remove functionality for the new dom element.
                    self.init_remove();
                    self.init_chosen();
                };
            }
        }, //end add
        init_remove: function(){
            var selectors = document.querySelectorAll('.pms-remove-meta-from-export span');
            if(selectors !== null) {
                selectors.forEach(function(selector){
                    selector.onclick = function (e) {
                        e.preventDefault();
                        e.target.parentNode.parentNode.remove();
                    };
                });
            }
        }, //end init_remove
        init_chosen: function(){
            if( jQuery.fn.chosen !== undefined ) {
                jQuery('.pms-chosen').chosen();
            }
        }
    };

    PmsAddUserMetaToExport.init();

});
