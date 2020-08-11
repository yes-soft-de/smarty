/*
 * JavaScript for Subscription Plan User Roles meta-box that is attached to the
 * Subscription Plan custom post type
 *
 */
jQuery( function($) {

    // Define meta-box jQuery object
    $metaBox = $('#pms_subscription_user_roles');

    /*
     * Enable user role select drop-down when clicking on the correct radio button
     *
     */
    $(document).on( 'click', 'input[name=pms_subscription_plan_user_role_option]', function() {

        $metaBox.find('select[name=pms_subscription_plan_user_role_existing]').attr( 'disabled', true );

        if( $(this).is(':checked') && $(this).parents('.pms-meta-box-field-wrapper').find('select').length > 0 ) {

            $(this).parents('.pms-meta-box-field-wrapper').find('select').attr( 'disabled', false );

        }

    });


    $(document).ready( function() {
        if( $metaBox.find('input[value=existing]').is(':checked') ) {
            $metaBox.find('select[name=pms_subscription_plan_user_role_existing]').attr( 'disabled', false );
        }
    });

});