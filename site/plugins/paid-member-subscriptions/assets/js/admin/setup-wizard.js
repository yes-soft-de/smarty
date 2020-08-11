/*
 * JavaScript for Subscription Plan cpt screen
 *
 */
jQuery( function($){

    /**
     * Adds a spinner after the element
     */
    $.fn.pms_addSpinner = function(){

        $this = $(this)

        if( $this.siblings('.spinner').length == 0 )
            $this.after('<div class="spinner"></div>')

        $spinner = $this.siblings('.spinner')
        $spinner.css('visibility', 'visible').animate({opacity: 1})

    }


    /**
     * Removes the spinners next to the element
     */
    $.fn.pms_removeSpinner = function(){

        if( $this.siblings('.spinner').length > 0 )
            $this.siblings('.spinner').remove()

    }


    $(document).ready( function(){

        if( $('#pms_gateway_paypal_standard').prop( 'checked' ) )
            $('.pms-setup-gateway-extra').css( 'display', 'flex' )
            
        $('label[for="pms_gateway_paypal_standard"]').click( function(){
            var value = $('#pms_gateway_paypal_standard').prop( 'checked' )

            if( value === false )
                $('.pms-setup-gateway-extra').css( 'display', 'flex' )
            else
                $('.pms-setup-gateway-extra').css( 'display', 'none' )
        })

        $('#pms_create_subscription_pages').click( function(){
            $(this).pms_addSpinner()
            $(this).prop( 'disabled', true )

            $.post( ajaxurl, { action: 'pms_create_subscription_pages' }, function( response ){
                if( response == 'success' ){
                    $(this).pms_removeSpinner()
                    $('.pms-setup-pages__line button').hide()
                    $('.pms-setup-pages__success').fadeIn(200)

                    $('label[for="pms_redirect_default"]').css( 'opacity', 1 )
                    $('#pms_redirect_default').prop( 'disabled', false )
                }
            })
        })
    })

})
