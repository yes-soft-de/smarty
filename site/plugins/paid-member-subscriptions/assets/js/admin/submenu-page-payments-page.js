/*
 * JavaScript for Payments Submenu Page
 *
 */
jQuery( function($) {

    /**
     * Initialize datepicker
     */
    $(document).on( 'focus', '.datepicker', function() {
        $(this).datepicker({
            dateFormat : 'yy-mm-dd',

            // Maintain the Time when switching dates
            onSelect   : function( dateText, inst ) {

                date = inst.lastVal.split(" ");
                dateTime = ( date[1] ? date[1] : '' );

                $(this).val( dateText + " " + dateTime );

            }

        });
    });

    /**
     * Chosen
     */
    if( $.fn.chosen != undefined ) {

        $('.pms-chosen').chosen();
    }


    /**
     * Adds a spinner after the element
     */
    $.fn.pms_addSpinner = function( animation_speed ) {

        if( typeof animation_speed == 'undefined' )
            animation_speed = 100;

        $this = $(this);

        if( $this.siblings('.spinner').length == 0 )
            $this.after('<div class="spinner"></div>');

        $spinner = $this.siblings('.spinner');
        $spinner.css('visibility', 'visible').animate({opacity: 1}, animation_speed );

    };


    /**
     * Removes the spinners next to the element
     */
    $.fn.pms_removeSpinner = function( animation_speed ) {

        if( typeof animation_speed == 'undefined' )
            animation_speed = 100;

        if( $this.siblings('.spinner').length > 0 ) {

            $spinner = $this.siblings('.spinner');
            $spinner.animate({opacity: 0}, animation_speed );

            setTimeout( function() {
                $spinner.remove();
            }, animation_speed );

        }

    };


    /**
     * Automatically populate the subscription price based on selected subscription when adding a new payment
     * with the expiration date calculated from the duration of the subscription plan selected
     */
    $(document).on('change', '#pms-form-add-payment select[name=pms-payment-subscription-id]', function() {

        $subscriptionSelect = $(this);
        $amountInput = $('#pms-form-add-payment input[name=pms-payment-amount]');

        if ( $subscriptionSelect.val() == 0 )
            return false;

        // De-focus the subscription plan select
        $subscriptionSelect.blur();

        // Add the spinner
        $amountInput.pms_addSpinner( 200 );

        $amountInputSpinner = $amountInput.siblings('.spinner');
        $amountInputSpinner.animate({opacity: 1}, 200);

        // Disable the amount input
        $amountInput.attr( 'disabled', true );

        // Get the subscription plan price and populate the Amount field
        $.post( ajaxurl, { action: 'populate_subscription_price', subscription_plan_id: $subscriptionSelect.val() }, function( response ) {

            // Populate the amount field
            $amountInput.val( response );

            // Remove spinner and enable the amount field
            $amountInput.pms_removeSpinner( 100 );
            $amountInput.attr( 'disabled', false).trigger('change');

        });

    });


    $(document).on( 'click', '#payment-log-details', function() {
        var row = $(this).closest( 'tr' )

        $( '.pms-modal__holder' ).html( '<a class="pms-modal__close"></a>' )
        $( '.pms-modal__holder' ).append( $( 'td.column-modal_data', row ).html() )
        $( '.pms-modal' ).show()
    });

    $(document).on( 'click', '.pms-modal__close', function() {
        $( '.pms-modal' ).hide()
    });
});
