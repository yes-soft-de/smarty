

/*
 * Function that handles the sorting of the user roles from the Select (User Role)
 * extra field
 *
 */
function pms_pb_handle_sorting_subscription_plans_field( container_name ) {

    if( jQuery(container_name).length == 0 )
        return;

    jQuery( container_name + ' ' + '.row-subscription-plans .wck-checkboxes').sortable({

        //Assign a custom handle for the drag and drop
        handle: '.sortable-handle',

        create: function( event, ui ) {

            //Add the custom handle for drag and drop
            jQuery(this).find('div').each( function() {
                jQuery(this).prepend('<span class="sortable-handle"></span>');
            });

            $sortOrderInput = jQuery(this).parents('.row-subscription-plans').siblings('.row-subscription-plans-sort-order').find('input[type=text]');

            if( $sortOrderInput.val() == '' ) {
                jQuery(this).find('input[type=checkbox]').each( function() {
                    $sortOrderInput.val( $sortOrderInput.val() + ', ' + jQuery(this).val() );
                });
            } else {
                sortOrderElements = $sortOrderInput.val().split(', ');
                sortOrderElements.shift();

                for( var i=0; i < sortOrderElements.length; i++ ) {
                    jQuery( container_name + ' ' + '.row-subscription-plans .wck-checkboxes').append( jQuery( container_name + ' ' + '.row-subscription-plans .wck-checkboxes input[value=' + sortOrderElements[i] + ']').parent().parent().get(0) );
                }
            }
        },

        update: function( event, ui ) {
            $sortOrderInput = ui.item.parents('.row-subscription-plans').siblings('.row-subscription-plans-sort-order').find('input[type=text]');
            $sortOrderInput.val('');

            ui.item.parent().find('input[type=checkbox]').each( function() {
                $sortOrderInput.val( $sortOrderInput.val() + ', ' + jQuery(this).val() );
            });
        }
    });
}


/**
 * Function that adds the Subscription Plans field to the global fields object
 * declared in Profile Builder: assets/js/jquery-manage-fields-live-change.js
 *
 */
function pms_pb_add_field() {
    if (typeof fields == "undefined") {
        return false;
    }

    fields["Subscription Plans"] = {
        'show_rows'	:	[
            '.row-field-title',
            '.row-field',
            '.row-description',
            '.row-subscription-plans',
            '.row-subscription-plan-selected'
        ],
        'properties':	{
            'meta_name_value' : ''
        }
    };
}

jQuery( function() {
    pms_pb_add_field();

    pms_pb_handle_sorting_subscription_plans_field( '#wppb_manage_fields' );
});