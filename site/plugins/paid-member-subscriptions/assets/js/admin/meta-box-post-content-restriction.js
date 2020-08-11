jQuery( function(){
    /* Display custom redirect URL section if type of restriction is "Redirect" */
    jQuery( 'input[type=radio][name=pms-content-restrict-type]' ).click( function() {
        if( jQuery(this).is(':checked') && jQuery(this).val() == 'redirect' )
            jQuery('#pms-meta-box-fields-wrapper-restriction-redirect-url').addClass('pms-enabled');
        else
            jQuery('#pms-meta-box-fields-wrapper-restriction-redirect-url').removeClass('pms-enabled');
    });

    /* Display custom redirect URL field */
    jQuery( '#pms-content-restrict-custom-redirect-url-enabled' ).click( function() {
        if( jQuery(this).is(':checked') )
            jQuery('.pms-meta-box-field-wrapper-custom-redirect-url').addClass('pms-enabled');
        else
            jQuery('.pms-meta-box-field-wrapper-custom-redirect-url').removeClass('pms-enabled');
    });

    /* Display custom messages editors */
    jQuery( '#pms-content-restrict-messages-enabled' ).click( function() {
    	if( jQuery(this).is(':checked') )
    		jQuery('.pms-meta-box-field-wrapper-custom-messages').addClass('pms-enabled');
    	else
    		jQuery('.pms-meta-box-field-wrapper-custom-messages').removeClass('pms-enabled');
    });

});
