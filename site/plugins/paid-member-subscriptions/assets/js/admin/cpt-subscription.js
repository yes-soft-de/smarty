/*
 * JavaScript for Subscription Plan cpt screen
 *
 */
jQuery( function($) {

    /*
     * When publishing or updating the Subscription Plan must have a name
     *
     */
    $(document).on( 'click', '#publish, #save-post', function() {

        var subscriptionPlanTitle = $('#title').val().trim();

        if( subscriptionPlanTitle == '' ) {

            alert( 'Subscription Plan must have a name.' );

            return false;

        }

    });

    /*
     * Remove the default "Move to Trash button"
     * Remove the "Edit" link for Subscription Plan status
     * Remove the "Visibility" box for discount codes
     * Remove the "Save Draft" button
     * Remove the "Status" div
     * Remove the "Published on.." section
     * Rename metabox "Save Subscription Plan"
     * Change "Publish" button to "Save Subscription"
     *
     */
   $(document).ready( function() {
        $('#delete-action').remove();
        $('.edit-post-status').remove();
        $('#visibility').remove();
        $('#minor-publishing-actions').remove();
        $('div.misc-pub-post-status').remove();
        $('#misc-publishing-actions').hide();
        $('#submitdiv h3 span').html('Save Subscription Plan');
        $('input#publish').val('Save Subscription');
    });


    /*
     * Move the "Add Upgrade" and "Add Downgrade" buttons from the submit box
     * next to the "Add New" button next to the title of the page
     *
     */
    $(document).ready( function() {

        $buttonsWrapper = $('#pms-upgrade-downgrade-buttons-wrapper');

        $buttons = $buttonsWrapper.children();

        $('.wrap h1').first().append( $buttons );

        $buttonsWrapper.remove();

    });

    $(document).on( 'click', '.pms-delete-subscription a', function(e) {

        var pmsDeleteUser = prompt( 'Are you sure you want to delete this plan ? Deleting plans with active subscribers can have unexpected results. \nPlease type DELETE in order to delete this plan:' )

        if( pmsDeleteUser === "DELETE" )
            window.location.replace(pmsGdpr.delete_url);
        else
            return false

    });

});
