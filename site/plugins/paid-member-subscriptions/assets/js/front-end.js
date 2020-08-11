/**
 * Define global variables so that the core plugin and 3rd party plugins can use them
 *
 */

// Paid Member Subscription submit buttons
var pms_payment_buttons;

// Field wrappers
var $pms_auto_renew_field;

// Checked Subscription
var $pms_checked_subscription;
var $pms_checked_paygate;

// Unavailable gateways message
var $pms_gateways_not_available;

// Text placeholder for the payment buttons while processing
var pms_payment_button_loading_placeholder_text;

/**
 * Core plugin
 *
 */
jQuery( function($) {

    if( window.history.replaceState ) {

        currentURL = window.location.href;

        currentURL = pms_remove_query_arg( 'pmsscscd', currentURL );
        currentURL = pms_remove_query_arg( 'pmsscsmsg', currentURL );
        currentURL = pms_remove_query_arg( 'pms_gateway_payment_action', currentURL );
        currentURL = pms_remove_query_arg( 'pms_gateway_payment_id', currentURL );

        if ( currentURL != window.location.href )
            window.history.replaceState( null, null, currentURL );
    }


    /*
     * Strips one query argument from a given URL string
     *
     */
    function pms_remove_query_arg( key, sourceURL ) {

        var rtn = sourceURL.split("?")[0],
            param,
            params_arr = [],
            queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";

        if (queryString !== "") {
            params_arr = queryString.split("&");
            for (var i = params_arr.length - 1; i >= 0; i -= 1) {
                param = params_arr[i].split("=")[0];
                if (param === key) {
                    params_arr.splice(i, 1);
                }
            }

            rtn = rtn + "?" + params_arr.join("&");

        }

        if(rtn.split("?")[1] == "") {
            rtn = rtn.split("?")[0];
        }

        return rtn;
    }

    // Paid Member Subscriptions submit buttons
    pms_payment_buttons  = 'input[name=pms_register], ';
    pms_payment_buttons += 'input[name=pms_new_subscription], ';
    pms_payment_buttons += 'input[name=pms_upgrade_subscription], ';
    pms_payment_buttons += 'input[name=pms_renew_subscription], ';
    pms_payment_buttons += 'input[name=pms_confirm_retry_payment_subscription], ';

    // Profile Builder submit buttons
    pms_payment_buttons += '.wppb-register-user input[name=register]';

    // Subscription pland ans payment gateway selectors
    var subscription_plan_selector = 'input[name=subscription_plans]';
    var paygate_selector           = 'input.pms_pay_gate';

    var settings_recurring = $('input[name="pms_default_recurring"]').val();

    // Field wrappers
    $pms_auto_renew_field = jQuery( '.pms-subscription-plan-auto-renew' );

    // Checked Subscription
    $pms_checked_subscription = jQuery( subscription_plan_selector + '[type=radio]' ).length > 0 ? jQuery( subscription_plan_selector + '[type=radio]:checked' ) : jQuery( subscription_plan_selector + '[type=hidden]' );
    $pms_checked_paygate      = jQuery( paygate_selector + '[type=radio]' ).length > 0 ? jQuery( paygate_selector + '[type=radio]:checked' ) : jQuery( paygate_selector + '[type=hidden]' );

    // Unavailable gateways message
    $pms_gateways_not_available = jQuery( '#pms-gateways-not-available' );

    pms_payment_button_loading_placeholder_text = $('#pms-submit-button-loading-placeholder-text').text();

    /*
     * Hide "automatically renew subscription" checkbox for manual payment gateway
     *
     */
    jQuery(document).ready( function() {

        /**
         * Handle the auto renew checkbox field display in the page
         *
         */
        function handle_auto_renew_field_display() {

            if( $pms_checked_subscription.data('recurring') == 1 && $pms_checked_paygate.data('recurring') != 'undefined' )
                $pms_auto_renew_field.show();
            else
                $pms_auto_renew_field.hide();


            if( $pms_checked_subscription.data('recurring') == 0 ) {

                if( settings_recurring == 1 )
                    $pms_auto_renew_field.show();

            }


            if( $pms_checked_subscription.data('recurring') == 2 || $pms_checked_subscription.data('recurring') == 3 ) {
                $pms_auto_renew_field.hide();
            }

            if ( $pms_checked_subscription.data('duration') == 0 || $pms_checked_subscription.data('price') == 0 ) {
                $pms_auto_renew_field.hide();
            }

        }


        /**
         * Handle the payment gateways radio buttons field display in the page
         *
         */
        function handle_payment_gateways_display() {

            // Before anything we display all gateways
            $('#pms-paygates-wrapper').show();
            $( paygate_selector ).removeAttr( 'disabled' );
            $( paygate_selector ).closest( 'label' ).show();


            // Support for "trial"
            if( $pms_checked_subscription.data('trial') && $pms_checked_subscription.data('trial') != 0 ) {

                $( paygate_selector + ':not([data-trial])' ).attr( 'disabled', true );
                $( paygate_selector + ':not([data-trial])' ).closest('label').hide();

            }


            // Support for "sign_up_fee"
            if( $pms_checked_subscription.data('sign_up_fee') && $pms_checked_subscription.data('sign_up_fee') != 0 ) {

                $( paygate_selector + ':not([data-sign_up_fee])' ).attr( 'disabled', true );
                $( paygate_selector + ':not([data-sign_up_fee])' ).closest('label').hide();

            }


            // Support for "recurring"
            if( $pms_checked_subscription.data('recurring') == 2 ) {

                $( paygate_selector + ':not([data-recurring])' ).attr( 'disabled', true );
                $( paygate_selector + ':not([data-recurring])' ).closest('label').hide();


            } else if( $pms_checked_subscription.data('recurring') == 1 ) {

                if( $pms_auto_renew_field.find('input[type=checkbox]').is(':checked') ) {
                    $( paygate_selector + ':not([data-recurring])' ).attr( 'disabled', true );
                    $( paygate_selector + ':not([data-recurring])' ).closest('label').hide();
                }

            } else if( ! $pms_checked_subscription.data('recurring') ) {

                if( settings_recurring == 1 ) {
                    if( $pms_auto_renew_field.find('input[type=checkbox]').is(':checked') ) {
                        $( paygate_selector + ':not([data-recurring])' ).attr( 'disabled', true );
                        $( paygate_selector + ':not([data-recurring])' ).closest('label').hide();
                    }
                } else if( settings_recurring == 2 ) {

                    $( paygate_selector + ':not([data-recurring])' ).attr( 'disabled', true );
                    $( paygate_selector + ':not([data-recurring])' ).closest('label').hide();

                }

            }


            // Select the first first available payment gateway by default after hiding the gateways
            if( $( paygate_selector + ':not([disabled]):checked' ).length == 0 )
                $( paygate_selector + ':not([disabled])' ).first().trigger('click');



            if( $( paygate_selector ).length > 0 ) {

                /**
                 * Handle case where no payment gateways are available
                 *
                 */
                if( $( paygate_selector + ':not([disabled])' ).length == 0 ) {

                    // Display the "no payment gateways are available" message
                    $pms_gateways_not_available.show();

                    // Hide credit card fields
                    $('.pms-credit-card-information').hide();
                    $('.pms-billing-details').hide();

                    // Disable submit button
                    if( $pms_checked_subscription.data( 'price' ) != 0 ) {

                        if( $pms_checked_subscription.length != 0 )
                            $( pms_payment_buttons ).attr( 'disabled', true ).addClass( 'pms-submit-disabled' );

                    }

                /**
                 * Handle case where payment gateways are available for selection
                 *
                 */
                } else {

                    // Hide the "no payment gateways are available" message
                    $pms_gateways_not_available.hide();

                    // Show credit card fields if the selected payment gateway supports credit cards
                    if( $( paygate_selector + ':not([disabled]):checked[data-type="credit_card"]' ).length > 0 ) {
                        $('.pms-credit-card-information').show();
                        $('.pms-billing-details').show();
                    }

                    // Enable submit button
                    if( $pms_checked_subscription.length != 0 )
                        $( pms_payment_buttons ).attr( 'disabled', false ).removeClass( 'pms-submit-disabled' );

                }

            }


            // Hide credit card fields if it's a free plan
            if( $pms_checked_subscription.data( 'price' ) == 0 && ( typeof $pms_checked_subscription.data('sign_up_fee') == 'undefined' || $pms_checked_subscription.data('sign_up_fee') == 0 ) ) {

                $('#pms-paygates-wrapper').hide();
                $( paygate_selector ).attr( 'disabled', true );
                $( paygate_selector ).closest( 'label' ).hide();

                $('.pms-credit-card-information').hide();
                $('.pms-billing-details').hide();

            }

        }


        /**
         * Set checked payment gateway when clicking on a payment gateway radio
         *
         */
        jQuery( document ).on( 'click', paygate_selector, function() {

            if( jQuery(this).is(':checked') )
                $pms_checked_paygate = jQuery(this);

            // Show / hide the credit card details
            if( $pms_checked_paygate.data('type') == 'credit_card' ) {

                $('.pms-credit-card-information').show();
                $('.pms-billing-details').show();

            } else {

                $('.pms-credit-card-information').hide();
                $('.pms-billing-details').hide();

            }

        });


        /**
         * Handle auto-renew checkbox and payment gateways display when clicking on a subscription plan
         *
         */
        jQuery( document ).on( 'click', subscription_plan_selector + '[type=radio]', function() {

            if( jQuery(this).is(':checked') )
                $pms_checked_subscription = jQuery(this);

            handle_auto_renew_field_display();
            handle_payment_gateways_display();

        });


        /**
         * Disable the payment button when clicking on it so that only one request
         * is sent to the server
         *
         */
        jQuery( document ).on( 'click', pms_payment_buttons, function(e) {

            if( $(this).hasClass('pms-submit-disabled') )
                return false;

            $(this).data( 'original-value', $(this).val() );

            // Replace the button text with the placeholder
            if( pms_payment_button_loading_placeholder_text.length > 0 )
                $(this).addClass( 'pms-submit-disabled' ).val( pms_payment_button_loading_placeholder_text );

        });


        /**
         * Trigger a click on the checked subscription plan when checking / unchecking the
         * auto-renew checkbox as this also takes into account whether the auto-renew field
         * is checked, thus hiding the unneeded payment gateways
         *
         */
        $pms_auto_renew_field.click( function() {

            handle_auto_renew_field_display();
            handle_payment_gateways_display();

        });


        /**
         * Trigger a click on the selected subscription plan so that
         * the rest of the checkout interfacte changes
         *
         */
        handle_auto_renew_field_display();
        handle_payment_gateways_display();

        /**
         * Show the paygates inner wrapper
         *
         */
        $( '#pms-paygates-inner' ).css( 'visibility', 'visible' );

    });


    /*
     * Add field error for a given element name
     *
     */
    $.pms_add_field_error = function( error, field_name ) {

        if( error == '' || error == 'undefined' || field_name == '' || field_name == 'undefined' )
            return false;

        $field          = $('[name=' + field_name + ']');
        $field_wrapper  = $field.closest('.pms-field');

        error = '<p>' + error + '</p>';

        if( $field_wrapper.find('.pms_field-errors-wrapper').length > 0 )
            $field_wrapper.find('.pms_field-errors-wrapper').html( error );
        else
            $field_wrapper.append('<div class="pms_field-errors-wrapper pms-is-js">' + error + '</div>');

    };

    $.pms_add_general_error = function( error ){
        if( error == '' || error == 'undefined' )
            return false

        var target = $('.pms-form')

        target.prepend( '<div class="pms_field-errors-wrapper pms-is-js"><p>' + error + '</p></div>' )
    }

    $.pms_add_subscription_plans_error = function( error ){
        if( error == '' || error == 'undefined' )
            return false

        $('<div class="pms_field-errors-wrapper pms-is-js"><p>' + error + '</p></div>').insertBefore( '#pms-paygates-wrapper' )
    }

    /*
     * Clear all field errors added with js
     *
     */
    $.pms_clean_field_errors = function() {

        $('.pms_field-errors-wrapper.pms-is-js').remove();

    };

    /*
    * GDPR Delete button
     */
    jQuery("#pms-delete-account").on("click", function (e) {
        e.preventDefault();

        var pmsDeleteUser = prompt(pmsGdpr.delete_text);
        if( pmsDeleteUser === "DELETE" ) {
            window.location.replace(pmsGdpr.delete_url);
        }
        else{
            alert( pmsGdpr.delete_error_text );
        }
    });

});


/*
 * Profile Builder Compatibility
 *
 */
jQuery( function($) {

    $(document).ready( function() {

        /**
         * Hide email confirmation payment message if no subscription plan is checked, or a free subscription is selected
         */

        // Handle on document ready
        if ( $('.pms-subscription-plan input[type=radio][data-price="0"]').is(':checked') || $('.pms-subscription-plan input[type=hidden]').attr( 'data-price' ) == '0' ||
            $('.pms-subscription-plan input[type=radio]').prop('checked') == false ) {

            $('.pms-email-confirmation-payment-message').hide();
        }

        if( $('.pms-subscription-plan input[type=radio]').length > 0 ) {

            var has_paid_subscription = false;

            $('.pms-subscription-plan input[type=radio]').each( function() {
                if( $(this).data('price') != 0 )
                    has_paid_subscription = true;
            });

            if( !has_paid_subscription )
                $('.pms-email-confirmation-payment-message').hide();

        }

        // Handle clicking on the subscription plans
        $('.pms-subscription-plan input[type=radio]').click(function(){

            if ($('.pms-subscription-plan input[type=radio][data-price="0"]').is(':checked')) {
                $('.pms-email-confirmation-payment-message').hide();
            }
            else {
                $('.pms-email-confirmation-payment-message').show();
            }
        });

        $('.wppb-edit-user input[required]').on('invalid', function(e){
            pms_reset_submit_button( $('.wppb-edit-user .wppb-subscription-plans input[type="submit"]').first() )
        });

    });

    function pms_reset_submit_button( target ) {

        setTimeout( function() {
            target.attr( 'disabled', false ).removeClass( 'pms-submit-disabled' ).val( target.data( 'original-value' ) ).blur();
        }, 1 );

    }

});


/**
 * Billing Fields
 */
jQuery( function($) {

    $(document).ready( function() {

        if( !PMS_States )
            return

        var $chosen_options = {
            search_contains : true
        }

        if( typeof PMS_ChosenStrings !== 'undefined' ){
            $chosen_options.placeholder_text_single = PMS_ChosenStrings.placeholder_text
            $chosen_options.no_results_text         = PMS_ChosenStrings.no_results_text
        }

        pms_handle_billing_state_field_display( $chosen_options )

        $(document).on( 'change', '#pms_billing_country', function() {

            pms_handle_billing_state_field_display( $chosen_options )

        })

        if( $.fn.chosen != undefined ){
            $('#pms_billing_country').chosen( $chosen_options )

            if( $('#pms_billing_state option').length > 0 )
                $('#pms_billing_state').chosen( $chosen_options )
        }

    });

    function pms_handle_billing_state_field_display( chosen_options ){

        var country = $('.pms-billing-details #pms_billing_country').val()

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
                $('.pms-billing-state__select').chosen( chosen_options )

        } else {

            if( $.fn.chosen != undefined )
                $('.pms-billing-state__select').chosen('destroy')

            $('.pms-billing-state__select').removeAttr('name').removeAttr('id').hide()
            $('.pms-billing-state__input').attr('name','pms_billing_state').attr('id','pms_billing_state').show()

        }

    }

});
