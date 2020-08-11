jQuery(document).ready(function(){
    if( jQuery("#wppb-register-user").length ) {
        jQuery('#wppb-register-user').submit(function (e) {
            //stop submitting the form to see the disabled button effect
            e.preventDefault();
            //disable the submit button
            jQuery('.form-submit #register').attr('disabled', true);
            this.submit();
        });
    }

    //scroll to top on success message
    if( jQuery("#wppb_form_general_message").length ){
        jQuery([document.documentElement, document.body]).animate({ scrollTop: jQuery("#wppb_form_general_message").offset().top }, 500);
    }
});