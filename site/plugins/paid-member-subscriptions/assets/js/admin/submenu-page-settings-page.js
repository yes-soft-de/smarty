/*
 * JavaScript for Settings Submenu Page
 *
 */
jQuery( function($) {


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


    /*
     * Adds a argument name, value pair to a given URL string
     *
     */
    function pms_add_query_arg( key, value, sourceURL ) {

        return sourceURL + '&' + key + '=' + value;

    }

    if( $.fn.chosen != undefined )
        $('.pms-chosen').chosen( { search_contains: true } )



    /*
     * Change settings sub-tabs when clicking on navigation sub-tabs
     */
    $(document).ready( function() {

        $('.nav-sub-tab').click( function(e) {
            e.preventDefault();

            $navTab = $(this);
            $navTab.blur();

            $('.nav-sub-tab').removeClass('current');
            $navTab.addClass('current');

            // Update the http referer with the current tab info
            $_wp_http_referer = $('input[name=_wp_http_referer]');

            var _wp_http_referer = $_wp_http_referer.val();
            _wp_http_referer = pms_remove_query_arg( 'nav_sub_tab', _wp_http_referer );
            _wp_http_referer = pms_add_query_arg( 'message', 1, _wp_http_referer );
            $_wp_http_referer.val( pms_add_query_arg( 'nav_sub_tab', $navTab.data('sub-tab-slug'), _wp_http_referer ) );

            $('.pms-sub-tab').removeClass('tab-active');
            $('.pms-sub-tab[data-sub-tab-slug="' + $navTab.data('sub-tab-slug') + '"]').addClass('tab-active');

        });

    });


    /*
     * Handle default payment gateways select options
     *
     */
    $activePaymetGateways = $('.pms-form-field-active-payment-gateways input[type=checkbox]');

    if( $activePaymetGateways.length > 0 ) {

        $(document).ready( function() {
            activateDefaultPaymentGatewayOptions();
        });

        $activePaymetGateways.click( function() {
            activateDefaultPaymentGatewayOptions();
        });

        /*
         * Activates the correct default payment gateway options in the select field
         * based on the active payment gateways
         *
         */
        function activateDefaultPaymentGatewayOptions() {
            var activeGateways = [];

            setTimeout( function() {

                $('.pms-form-field-active-payment-gateways input[type=checkbox]:checked').each( function() {
                    activeGateways.push( $(this).val() );
                });

                $('#default-payment-gateway').find('option').each( function() {
                    if( activeGateways.indexOf( $(this).val() ) == -1 )
                        $(this).attr('disabled', true);
                    else
                        $(this).attr('disabled', false);
                });

            }, 200 );
        }
    }


    /*
     * Position the Available tags div from the e-mail settings tab
     *
     */
    function positionAvailableTags() {
        $availableTags   = $('#pms-available-tags');
        $emailsTabs      = $('#pms-settings-emails');
        $formTabsWrapper = $emailsTabs.closest('form');

        if ( $emailsTabs.length > 0 ) {
            $availableTags.css( 'top', $formTabsWrapper.offset().top + 60 );
            $availableTags.css( 'left', $emailsTabs.closest('.wrap').offset().left + $formTabsWrapper.width() - 280 );
        }

    }

    $(document).ready( function() {
        positionAvailableTags();
        $availableTags.css( 'opacity', 1 );
    });

    $(window).on( 'resize', function() {
        positionAvailableTags();
    });

    $(window).on( 'scroll', function() {
        $formTabsWrapper = $('#pms-settings-emails').closest('form');

        if ( $formTabsWrapper.length > 0 ) {

            if( $(window).scrollTop() < $formTabsWrapper.offset().top ) {
                $('#pms-available-tags').css( 'top', $formTabsWrapper.offset().top + 60 - $(window).scrollTop() );
            } else {
                $('#pms-available-tags').css( 'top', '60px' );
            }

        }

    });

    /*
     * Show the individual email toggles when administrator emails are enabled
     *
     */
     $(document).on( 'change', '#emails-admin-on', function () {
         if ( this.checked )
            $( '.pms-sub-tab-admin .pms-heading-wrap label' ).show();
        else
            $( '.pms-sub-tab-admin .pms-heading-wrap label' ).hide();
     });

     $(document).ready( function() {
         if ( $('input[name="pms_emails_settings[admin_emails_on]"]').prop('checked') )
            $( '.pms-sub-tab-admin .pms-heading-wrap label' ).show();
     });

});
