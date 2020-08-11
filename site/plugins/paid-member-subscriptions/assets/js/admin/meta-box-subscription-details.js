/*
 * JavaScript for Subscription Plan Details meta-box that is attached to the
 * Subscription Plan custom post type
 *
 */
jQuery( function($) {

    /*
     * Validates the duration value introduced, this value must be a whole number
     *
     */
    $(document).on( 'click', '#publish', function() {

        var subscription_plan_duration = $('#pms-subscription-plan-duration').val().trim();

        if( ( parseInt( subscription_plan_duration ) != subscription_plan_duration ) || ( parseFloat( subscription_plan_duration ) == 0 && subscription_plan_duration.length > 1 ) ) {

            alert( 'Subscription Plan duration must be a whole number.' );

            return false;
        }

    });

});