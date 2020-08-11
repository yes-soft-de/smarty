/*
 * Callback that is executed when Google's reCaptcha script loads
 *
 */
pms_recaptcha_callback = function() {

    "use strict";

    // Get all elements where the reCaptcha should render
    var elements = document.getElementsByClassName( 'pms-recaptcha' );

    // Render all reCaptchas
    if( elements.length > 0 ) {
        for( var i = 0; i < elements.length; i++ ) {

            var id       = elements[i]['id'];

            var site_key = elements[i]['dataset']['sitekey'];
            var theme    = elements[i]['dataset']['theme'];
            var size     = elements[i]['dataset']['size'];

            if( id != '' && site_key != '' )
                grecaptcha.render( id, {
                    'sitekey' : site_key,
                    'theme'   : theme,
                    'size'    : size
                });

        }
    }

};