(function ($) {
    'use strict';

    $(document).ready(function () {
        var BadgeOS_Template_Admin = {
            init: function () {
                $("#carouselTicker").carouselTicker();

                $('.btn_badgeos_install_template').click(function () {
                    var self = $(this);
                    self.attr("disabled", true);
                    self.parent().find('#btn_badgeos_install_template_loader').css('visibility', 'visible');
                    var template_id = self.parent().find('.badgeos_template_id').val();
                    var ajaxURL = self.data('ajax_url');

                    var data = {
                        'action': 'badgeos_install_template',
                        'template_id': template_id
                    };

                    jQuery.post(ajaxURL, data, function (response) {
                        var popup_id = self.parent().parent().find('.badgeos_template_info_log').attr('id');
                        self.parent().find('#btn_badgeos_install_template_loader').css('visibility', 'hidden');
                        self.parent().parent().find('.badgeos_template_message').css('visibility', 'visible').find('.badgeos_template_info_log .badgeos_template_info_log_content').html('<style>.badgeos_template_info_log_content a{ display:none; }</style>' + response).find('a').css('display', 'none');
                        self.attr("disabled", false);
                        tb_show(admin_js.badgeos_templates_log, "#TB_inline?inlineId=" + popup_id + "&height=300&width=400");
                    });

                    return false;
                });

                $(".badgeos_template_response_open_popup").on('click', function () {
                    var popup = $(this).data('popup_id');
                    tb_show(admin_js.badgeos_templates_log, "#TB_inline?inlineId=" + popup + "&height=300&width=400");

                });

                $(".badgeos-admin-carousel-ticker-item .badgeos-item_seemore").on('click', function () {
                    var url = $(this).data('url');
                    document.location = url;
                });
            }
        }
        BadgeOS_Template_Admin.init();
    });
})(jQuery);   