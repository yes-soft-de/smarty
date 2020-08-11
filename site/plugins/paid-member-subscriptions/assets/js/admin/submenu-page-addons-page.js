jQuery(function(){
    /*
     * Function to download/activate add-ons on button click
     */
    jQuery('.pms-add-on .button').on( 'click', function(e) {
        if( jQuery(this).attr('disabled') ) {
            return false;
        }

        // Activate add-on
        if( jQuery(this).hasClass('pms-add-on-activate') ) {
            e.preventDefault();
            pms_add_on_activate( jQuery(this) );
        }

        // Deactivate add-on
        if( jQuery(this).hasClass('pms-add-on-deactivate') ) {
            e.preventDefault();
            pms_add_on_deactivate( jQuery(this) );
        }
    });

    /* show save serial button */
    jQuery('.pms-add-on-serial-number, .button.save-serial').on( 'focus', function(){
        jQuery(this).next('a').css('opacity', 1);
    });

    jQuery('.pms-add-on-serial-number, .button.save-serial').on( 'blur', function(){
        jQuery(this).next('a').css('opacity', 0);
    });

    /* save serial ajax */
    jQuery( '.button.save-serial').on( 'click', function(e){
        e.preventDefault();
        input = jQuery(this).siblings('input');
        var pms_add_on_slug = input.attr('data-slug');
        var pms_add_on_unique_name = input.attr('data-unique-name');
        var pms_serial_value = input.attr('value');
        jQuery.post( ajaxurl, { action: 'pms_add_on_save_serial', pms_add_on_slug: pms_add_on_slug, pms_add_on_unique_name:pms_add_on_unique_name, pms_serial_value: pms_serial_value }, function( response ) {

            if( response != 'found' ){
                input.removeClass( 'pms-found' );
                input.addClass( 'pms-error' );

                input.parent().removeClass( 'pms-found' );
                input.parent().addClass( 'pms-error' );
            }
            else{
                input.removeClass( 'pms-error' );
                input.addClass( 'pms-found' );

                input.parent().removeClass( 'pms-error' );
                input.parent().addClass( 'pms-found' );
            }
        });
    });

    /*
     * Make deactivate button from Add-On is Active message button
     */
    jQuery('.pms-add-on').on( 'hover', function() {

        $button = jQuery(this).find('.pms-add-on-deactivate');

        if( $button.length > 0 ) {
            $button
                .animate({
                    opacity: 1
                }, 100);
        }
    });

    /*
     * Make Add-On is Active message button from deactivate button
     */
    jQuery('.pms-add-on').on( 'mouseleave', function() {

        $button = jQuery(this).find('.pms-add-on-deactivate');

        if( $button.length > 0 ) {
            $button
                .animate({
                    opacity: 0
                }, 100);
        }
    });
});


/*
 * Function that activates the add-on
 */
function pms_add_on_activate( $button ) {
    $activate_button = $button;

    var fade_in_out_speed = 300;
    var plugin = $activate_button.attr('href');
    var add_on_index = $activate_button.parents('.pms-add-on').index('.pms-add-on');
    var nonce = $activate_button.data('nonce');

    $activate_button
        .attr('disabled', true);

    $spinner = $activate_button.siblings('.spinner');

    $spinner.animate({
        opacity: 0.7
    }, 100);

    // Remove the current displayed message
    pms_add_on_remove_status_message( $activate_button, fade_in_out_speed);

    jQuery.post( ajaxurl, { action: 'pms_add_on_activate', pms_add_on_to_activate: plugin, pms_add_on_index: add_on_index, nonce: nonce }, function( response ) {

        add_on_index = response;

        $activate_button = jQuery('.pms-add-on').eq( add_on_index ).find('.plugin-card-bottom .button');

        $activate_button
            .blur()
            .removeClass('pms-add-on-activate')
            .addClass('pms-add-on-deactivate')
            .removeAttr('disabled')
            .text( jQuery('#pms-add-on-deactivate-button-text').text() );

        $spinner = $activate_button.siblings('.spinner');

        $spinner.animate({
            opacity: 0
        }, 0);

        // Set status confirmation message
        pms_add_on_set_status_message( $activate_button, 'dashicons-yes', jQuery('#pms-add-on-activated-message-text').text(), fade_in_out_speed, 0, true );
        pms_add_on_remove_status_message( $activate_button, fade_in_out_speed, 2000 );

        // Set is active message
        pms_add_on_set_status_message( $activate_button, 'dashicons-yes', jQuery('#pms-add-on-is-active-message-text').html(), fade_in_out_speed, 2000 + fade_in_out_speed );
    });
}



/*
 * Function that deactivates the add-on
 */
function pms_add_on_deactivate( $button ) {

    var fade_in_out_speed = 300;
    var plugin = $button.attr('href');
    var add_on_index = $button.parents('.pms-add-on').index('.pms-add-on');
    var nonce = $button.data('nonce');

    $button
        .removeClass('pms-add-on-deactivate')
        .attr('disabled', true);

    $spinner = $button.siblings('.spinner');

    $spinner.animate({
        opacity: 0.7
    }, 100);

    // Remove the current displayed message
    pms_add_on_remove_status_message( $button, fade_in_out_speed );

    jQuery.post( ajaxurl, { action: 'pms_add_on_deactivate', pms_add_on_to_deactivate: plugin, pms_add_on_index: add_on_index, nonce: nonce }, function( response ) {

        add_on_index = response;

        $button = jQuery('.pms-add-on').eq( add_on_index ).find('.plugin-card-bottom .button');

        $button
            .blur()
            .removeClass('pms-add-on-is-active')
            .addClass('pms-add-on-activate')
            .attr( 'disabled', false )
            .text( jQuery('#pms-add-on-activate-button-text').text() );

        $spinner = $button.siblings('.spinner');

        $spinner.animate({
            opacity: 0
        }, 0);

        // Set status confirmation message
        pms_add_on_set_status_message( $button, 'dashicons-yes', jQuery('#pms-add-on-deactivated-message-text').text(), fade_in_out_speed, 0, true );
        pms_add_on_remove_status_message( $button, fade_in_out_speed, 2000 );

        // Set is active message
        pms_add_on_set_status_message( $button, 'dashicons-no-alt', jQuery('#pms-add-on-is-not-active-message-text').html(), fade_in_out_speed, 2000 + fade_in_out_speed );

    });
}


/*
 * Function used to remove the status message of an add-on
 *
 * @param object $button            - The jQuery object of the add-on box button that was pressed
 * @param int fade_in_out_speed     - The speed of the fade in and out animations
 * @param int delay                 - Delay removing of the message
 *
 */
function pms_add_on_remove_status_message( $button, fade_in_out_speed, delay ) {

    if( typeof( delay ) == 'undefined' ) {
        delay = 0;
    }

    setTimeout( function() {

        $button.siblings('.dashicons')
            .animate({
                opacity: 0
            }, fade_in_out_speed );

        $button.siblings('.pms-add-on-message')
            .animate({
                opacity: 0
            }, fade_in_out_speed );

    }, delay);

}

/*
 * Function used to remove the status message of an add-on
 *
 * @param object $button                - The jQuery object of the add-on box button that was pressed
 * @param string message_icon_class     - The string name of the class we want the icon to have
 * @param string message_text           - The text we want the user to see
 * @param int fade_in_out_speed         - The speed of the fade in and out animations
 * @param bool success                  - If true adds a class to style the message as a success one, if false adds a class to style the message as a failure
 *
 */
function pms_add_on_set_status_message( $button, message_icon_class, message_text, fade_in_out_speed, delay, success ) {

    if( typeof( delay ) == 'undefined' ) {
        delay = 0;
    }

    setTimeout(function() {

        $button.siblings('.dashicons')
            .css('opacity', 0)
            .attr('class', 'dashicons')
            .addClass( message_icon_class )
            .animate({ opacity: 1}, fade_in_out_speed);

        $button.siblings('.pms-add-on-message')
            .css('opacity', 0)
            .attr( 'class', 'pms-add-on-message' )
            .html( message_text )
            .animate({ opacity: 1}, fade_in_out_speed);

        if( typeof( success ) != 'undefined' ) {
            if( success == true ) {
                $button.siblings('.dashicons')
                    .addClass('pms-confirmation-success');
                $button.siblings('.pms-add-on-message')
                    .addClass('pms-confirmation-success');
            } else if( success == false ) {
                $button.siblings('.dashicons')
                    .addClass('pms-confirmation-error');
                $button.siblings('.pms-add-on-message')
                    .addClass('pms-confirmation-error');
            }
        }

    }, delay );

}