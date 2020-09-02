jQuery( document ).ready(function() {

    // toggle check/uncheck all courses for integration
    jQuery('#tlms-integrate-all').toggle(function () {
        jQuery('.tlms-products').attr('checked','checked');
        jQuery(this).html(translations.unselect_all_message);
    }, function () {
        jQuery('.tlms-products').removeAttr('checked');
        jQuery(this).html(translations.select_all_message);
    });


    jQuery('.tlms-reset-course').click(function () {

        jQuery(this).parent().parent().parent().append('<div class="progress-message">'+translations.progress_message+'</div>');
        var data = {
            'action': 'tlms_resynch',
            'course_id': jQuery(this).data('course-id')
        }
        jQuery.post(ajaxurl, data)
        .done(function(response){
			var parsed_response = JSON.parse(response);
            if(parsed_response.api_limitation === 'none'){
				jQuery('.progress-message').html(translations.success_message);
            } else {
				jQuery('.progress-message').html(parsed_response.api_limitation);
            }
			setTimeout(function(){ jQuery('.progress-message').remove(); }, 3000);
        })
        .fail(function(jqXHR, textStatus, error){
			jQuery('.progress-message').html(jqXHR.responseText);
        });
    });

    jQuery(function(){
        jQuery("#tlms-integrations-table").dataTable({
            "order": [[ 0, "asc" ]] ,
            "columns": [
                { "orderable": true },
                { "orderable": false }
            ]
        });
    });

});