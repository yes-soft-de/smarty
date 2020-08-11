/*
 * JavaScript for Members Submenu Page
 *
 */
jQuery( function($) {

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


    if( $.fn.chosen != undefined ) {

        $('.pms-chosen').chosen();

    }


    /*
     * Function that checks to see if any field from a row is empty
     *
     */
    function checkEmptyRow( $field_wrapper ) {

        is_field_empty = false;

        $field_wrapper.find('.pms-subscription-field').each( function() {

            $field = $(this);

            if( typeof $field.attr('required') == 'undefined' )
                return true;

            var field_value = $field.val().trim();

            if( $field.is('select') && field_value == 0 )
                field_value = '';

            if( field_value == '' ) {
                $field.addClass('pms-field-error');
                is_field_empty = true;
            } else {
                $field.removeClass('pms-field-error');
            }

        });

        return is_field_empty;

    }


    var validation_errors = [];

    /**
     * Displays any errors as an admin notice under the page's title
     *
     */
    function displayErrors() {

        if( validation_errors.length == 0 )
            return false;

        errors_output = '';
        for( var i = 0; i < validation_errors.length; i++ ) {
            errors_output += '<p>' + validation_errors[i] + '</p>';
        }

        if( $('.wrap h2').first().siblings('.pms-admin-notice').length > 0 ) {

            $('.wrap h2').first().siblings('.pms-admin-notice').html( errors_output );

        } else {
            $('.wrap h2').first().after( '<div class="error pms-admin-notice">' + errors_output + '</div>' )
        }

    }


    /**
     * Initialize datepicker
     *
     */
    $(document).on( 'focus', '.datepicker', function() {
        $(this).datepicker({ dateFormat: 'yy-mm-dd'});
    });


    /**
     * Populate the expiration date field when changing the subscription plan field
     * with the expiration date calculated from the duration of the subscription plan selected
     */
    $(document).on( 'change', '#pms-form-add-member-subscription select[name=subscription_plan_id]', function() {

        $subscriptionPlanSelect = $(this);
        $expirationDateInput    = $subscriptionPlanSelect.closest('.pms-meta-box-field-wrapper').siblings('.pms-meta-box-field-wrapper').find('input[name=expiration_date]');

        // Exit if no subscription plan was selected
        if( $subscriptionPlanSelect.val() == 0 )
            return false;

        // De-focus the subscription plan select
        $subscriptionPlanSelect.blur();

        // Add the spinner
        $expirationDateInput.pms_addSpinner( 200 );

        $expirationDateSpinner = $expirationDateInput.siblings('.spinner');
        $expirationDateSpinner.animate({opacity: 1}, 200);

        // Disable the datepicker
        $expirationDateInput.attr( 'disabled', true );

        // Get the expiration date and set it the expiration date field
        $.post( ajaxurl, { action: 'populate_expiration_date', subscription_plan_id: $subscriptionPlanSelect.val() }, function( response ) {

            // Populate expiration date field
            $expirationDateInput.val( response );

            // Remove spinner and enable the expiration date field
            $expirationDateInput.pms_removeSpinner( 100 );
            $expirationDateInput.attr( 'disabled', false).trigger('change');

        });

    });


    /**
     * Shows / hides the payment gateway's extra fields when changing the payment gateway
     *
     */
    $(document).on( 'change', 'input[name=payment_gateway]', function() {

        /**
         * Display fields from Stripe gateway for Stripe Payment Intents
         */
        var value = $(this).val()

        if( value == 'stripe_intents' )
            value = 'stripe'

        $('#pms-meta-box-fields-wrapper-payment-gateways > div').hide();
        $('#pms-meta-box-fields-wrapper-payment-gateways > div[data-payment-gateway=' + value + ']').show();

    });

    $('input[name=payment_gateway]').trigger('change');


    /**
     * Selecting the username
     *
     */
    $(document).on( 'change', '#pms-member-username', function() {

        $select = $(this);

        if( $select.val().trim() == '' )
            return false;

        var user_id = $select.val().trim();

        $('#pms-member-user-id').val( user_id );

    });

    /**
     * Fired when an username is entered manually by the admin
     */
    $(document).on( 'change', '#pms-member-username-input', function() {

        $( '.pms-member-details-error' ).remove()

        if( $(this).val().trim() == '' )
            return

        $( '#pms-member-username-input' ).pms_addSpinner()

        $.post( ajaxurl, { action: 'check_username', username: $(this).val() }, function( response ) {

            if( response != 0 ) {

                $('#pms-member-user-id').val( response )
                $('#pms-member-username-input').pms_removeSpinner()

            } else {
                $('#pms-member-username-input').after('<span class="pms-member-details-error">Invalid username</span>')
                $('#pms-member-username-input').pms_removeSpinner()
            }

        });

    });


    /**
     * Validate empty fields
     *
     */
    $(document).on( 'click', '.pms-edit-subscription-details', function(e) {
        e.preventDefault();

        $button = $(this);

        if( !$button.hasClass('button-primary') )
            return false;

        $row = $button.parents('tr');

        is_field_empty = checkEmptyRow( $row );

        if( is_field_empty )
            $row.addClass('pms-field-error');
        else
            $row.removeClass('pms-field-error');

    });


    /*
     * Validate form before submitting
     *
     */
    $('.pms-form input[type=submit]').click( function(e) {

        var errors = false;
        validation_errors = [];

        // Check to see if the user id exists
        if( $('#pms-member-user-id').length > 0 && $('#pms-member-user-id').val().trim() == 0 ) {
            errors = true;
            validation_errors.push( 'Please select a user.' );
        }

        // If no subscription plan is to be found return
        if( $('#pms-member-subscription-details select[name=subscription_plan_id]').val() == 0 ) {
            errors = true;
            validation_errors.push( 'Please select a subscription plan.' );
        }


        // Check to see if any fields are left empty and return if so
        is_empty = false;
        $('#pms-member-subscription-details .pms-meta-box-field-wrapper').each( function() {
            if( checkEmptyRow( $(this) ) == true )
                is_empty = true;
        });

        if( is_empty ) {
            errors = true;
            validation_errors.push( 'Please fill all the required fields.' );
        }


        if( errors ) {
            displayErrors();
            return false;
        }

    });


    /**
     * When adding a new member subscription populate the member subscription data
     * when an admin selects the subscription plan.
     *
     */
    $(document).on( 'change', '#pms-form-add-edit-member-subscription select[name=subscription_plan_id]', function() {

        if( $('input[name=action]').val() != 'add_subscription' )
            return false;

        if( $(this).val() == 0 )
            return false;

        // Cache form elements
        $this        = $(this);
        $form        = $this.closest( 'form' );
        $form_fields = $form.find( 'input, select, textarea' );
        $spinner     = $this.siblings( '.spinner' );

        // Disable all fields
        $form_fields.attr( 'disabled', true );
        $spinner.css( 'visibility', 'visible' );


        $.post( ajaxurl, { action: 'populate_member_subscription_fields', subscription_plan_id: $this.val() }, function( response ) {

            if( response != 0 ) {

                fields = JSON.parse( response );

                // Populate fields with returned values
                for( var key in fields ) {

                    $field = $form.find('[name=' + key + ']');

                    if( $field.is( 'select' ) ) {
                        $field.find( 'option[value=' + fields[key] + ']' ).attr( 'selected', true );
                    }

                    if( $field.is( 'input' ) ) {
                        $field.val( fields[key] );
                    }

                }

                // Re-enable all fields
                $form_fields.attr( 'disabled', false );
                $spinner.css( 'visibility', 'hidden' );

            }

        });

    });

    // Add log entry manually
    $(document).on( 'click', '#pms_add_log_entry', function(e) {
        e.preventDefault()
        pms_add_log_entry()
    });

    $(document).on('keypress', 'input', function (e) {
        if (e.which == 13 && document.activeElement && document.activeElement.name == 'pms_admin_log' ) {
            e.preventDefault();

            pms_add_log_entry()
        }
    });

    function pms_add_log_entry(){
        var subscription_id = jQuery('#pms-member-subscription-logs input[name="pms_subscription_id"]').val(),
            log = jQuery('#pms-member-subscription-logs input[name="pms_admin_log"]').val()

        if( subscription_id && log ){
            jQuery('#pms_add_log_entry').pms_addSpinner( 200 )

            $.post( ajaxurl, {
                action: 'add_log_entry',
                subscription_id: subscription_id,
                log: log }, function( response ) {

                    response = JSON.parse( response )

                    if( response.status && response.status == 'success' )
                        jQuery('#pms-member-subscription-logs .pms-logs-holder' ).html( response.data )

                    jQuery('#pms-member-subscription-logs input[name="pms_admin_log"]').val('')

                    jQuery('#pms_add_log_entry').pms_removeSpinner( 200 )

            })
        }
    }

    // Billing Details
    $(document).on( 'click', '#pms-member-billing-details #edit', function(e) {
        e.preventDefault()

        $('#pms-member-billing-details .billing-details').hide()
        $('#pms-member-billing-details .form').show()

        if( !PMS_States )
            return

        pms_handle_billing_state_field_display()

        if( $.fn.chosen != undefined ){
            $('#pms-member-billing-details .form #pms_billing_country').chosen( { search_contains: true } )

            if( $('#pms-member-billing-details .form #pms_billing_state option').length > 0 )
                $('#pms-member-billing-details .form #pms_billing_state').chosen( { search_contains: true } )
        }

    })

    $(document).on( 'change', '#pms_billing_country', function() {

        pms_handle_billing_state_field_display()

    })

    $(document).on( 'click', '#pms-member-billing-details #save', function(e) {
        e.preventDefault()

        jQuery(this).pms_addSpinner( 200 )

        if( !pms_billing_details || !pms_billing_details.fields )
            return;

        var data = {}
            data.action     = 'pms_edit_member_billing_details'
            data.security   = pms_billing_details.edit_member_details_nonce
            data.member_id  = jQuery( 'input[name=pms_member_id]' ).val()

        pms_billing_details.fields.forEach( function( field ){
            data[field] = jQuery( 'input[name=' + field + ']' ).val()
        })

        if( jQuery( 'select[name=pms_billing_country]' ).length > 0 )
            data.pms_billing_country = jQuery( 'select[name=pms_billing_country]' ).val()

        if( PMS_States && PMS_States[data.pms_billing_country] )
            data.pms_billing_state = jQuery( '.pms-billing-state__select' ).val()

        $.post( ajaxurl, data, function( response ){

            response = JSON.parse( response )

            if( response.status && response.status == 'success' && response.address_output ){

                jQuery('#pms-member-billing-details .billing-details p').html( response.address_output )

                jQuery( '.billing-details__action span' ).show().fadeOut( 3500 )

            }
        })

        jQuery(this).pms_removeSpinner( 200 )

        $('#pms-member-billing-details .form').hide()
        $('#pms-member-billing-details .billing-details').show()

    })

    function pms_handle_billing_state_field_display(){

        var country = $('#pms-member-billing-details .form #pms_billing_country').val()

        if( PMS_States[country] ){

            if( $.fn.chosen != undefined )
                $('.pms-billing-state__select').chosen('destroy')

            $('.pms-billing-state__select option').remove()
            $('.pms-billing-state__select').append('<option value=""></option>');

            for( var key in PMS_States[country] ){
                if( PMS_States[country].hasOwnProperty(key) )
                    $('.pms-billing-state__select').append('<option value="'+ key +'">'+ PMS_States[country][key] +'</option>');
            }

            var prevValue = $('.pms-billing-state__input').val()

            if( prevValue != '' )
                $('.pms-billing-state__select').val( prevValue )

            $('.pms-billing-state__input').removeAttr('name').removeAttr('id').hide()
            $('.pms-billing-state__select').attr('name','pms_billing_state').attr('id','pms_billing_state').show()

            if( $.fn.chosen != undefined )
                $('#pms-member-billing-details .form .pms-billing-state__select').chosen( { search_contains: true } )

        } else {

            if( $.fn.chosen != undefined )
                $('.pms-billing-state__select').chosen('destroy')

            $('.pms-billing-state__select').removeAttr('name').removeAttr('id').hide()
            $('.pms-billing-state__input').attr('name','pms_billing_state').attr('id','pms_billing_state').show()

        }

    }

});
