/**
 * JavaScript for Membership Discounts meta-box attached to per Product or Subscription Plan
 */
jQuery( function($){

    /**
     *  Discounts behaviour, hide discounts table when "Exclude this product from all membership discounts" is selected
     */
    $('#pms_woo_product_membership_discounts').on( 'change', '#pms_woo_product_membership_discounts_behaviour', function(){

        $('p.default_discount').hide();
        $('p.ignore_discount').hide();
        $('p.exclude_discount').hide();

        if ( $(this).val() == 'exclude' ) {
            $('#pms-woo-product-membership-discounts').hide();
            $('#pms-woo-product-add-membership-discount').hide();
            $('p.exclude_discount').show();
        }
        else{
            $('#pms-woo-product-membership-discounts').show();
            $('#pms-woo-product-add-membership-discount').show();
            if ( $(this).val() == 'ignore' ){
                $('p.ignore_discount').show();
            }
            else{
                $('p.default_discount').show();
            }
        }
    });


    if ( $('#pms_woo_product_membership_discounts_behaviour').val() == 'exclude' ) {
        $('#pms-woo-product-membership-discounts').hide();
        $('#pms-woo-product-add-membership-discount').hide();
    }
    else {
        $('#pms-woo-product-membership-discounts').show();
        $('#pms-woo-product-add-membership-discount').show();
    }




    /**
     * Add new product membership discount ( Membership Discounts meta-box - located under Products in WooCommerce )
     */
    var meta_name = "pms-woo-product-membership-discounts";
    var $wooProductMembershipDiscounts = $('#pms-woo-product-membership-discounts tbody');

    $('#pms-woo-product-add-membership-discount').click( function(e) {
        e.preventDefault();

        // Hide "No discounts yet" message
        $('.pms-woo-no-discounts-message').remove();

        var output;

        output  = '<tr class="pms-woo-product-membership-discount">';

        // Add "Subscription plan" row cell
        output += '<td>';
        output += '<select name="' + meta_name + '[][subscription-plan]" class="widefat pms-select-subscription-plan">';
        output += '<option value="0">' + pms_woo_admin_vars.strings['Choose'] + '</option>';

        for( var key in pmsSubscriptionPlans )
            output += '<option value="' + key + '">' + pmsSubscriptionPlans[key] + '</option>';

        output += '</select>';
        output += '</td>';

        // Add "Type" row cell
        output += '<td>';
        output += '<select name="' + meta_name + '[][type]" class="widefat pms-select-discount-type">';

        output += '<option value="percent" >' + pms_woo_admin_vars.strings['Percent'] + ' (%)</option>';
        output += '<option value="fixed" >' + pms_woo_admin_vars.strings['Fixed'] + ' (' + pms_woo_admin_vars.currency_symbol + ')</option>';

        output += '</select>';
        output += '</td>';

        // Add "Amount" row cell
        output += '<td>';
        output += '<input type="text" name="' + meta_name + '[][amount]" value="" class="widefat pms-input-discount-amount">';
        output += '</td>';

        // Add "Status" row cell
        output += '<td>';
        output += '<select name="' + meta_name + '[][status]" class="widefat pms-select-discount-status">';
        output += '<option value="active">' + pms_woo_admin_vars.strings['Active'] + '</option>';
        output += '<option value="inactive">' + pms_woo_admin_vars.strings['Inactive'] + '</option>';
        output += '</select>';
        output += '</td>';

        // Add close link
        output += '<td>';
        output += '<a href="#" class="pms-woo-product-remove-membership-discount" title="' + pms_woo_admin_vars.strings['Remove this discount'] + '"><span class="dashicons dashicons-no"></span></a>';
        output += '</td>';

        output += '</tr>';

        // Append output and recalculate row indexes
        $wooProductMembershipDiscounts.append( output );
        calculateRowIndexesDiscounts( $wooProductMembershipDiscounts );

    });




    /**
     * Add new subscription plan product discount ( Product Discounts meta-box - located under each Subscription Plan in PMS )
     */
    var meta_name_subscription = "pms-woo-subscription-product-discounts";
    var $wooSubscriptionProductDiscounts = $('#pms-woo-subscription-product-discounts tbody');

    $('#pms-woo-subscription-add-product-discount').click( function(e) {
        e.preventDefault();

        // Hide "No discounts yet" message
        $('.pms-woo-no-discounts-message').remove();

        var output;

        output  = '<tr class="pms-woo-subscription-product-discount">';

        // Add "Discount for" row cell
        output += '<td>';
        output += '<select name="' + meta_name_subscription + '[][discount-for]" class="widefat pms-select-discount-for">';
        output += '<option value="products">' + pms_woo_admin_vars.strings['Products'] + '</option>';
        output += '<option value="product-categories">' + pms_woo_admin_vars.strings['Product Categories'] + '</option>';
        output += '</select>';
        output += '</td>';

        // Add "Name" row cell
        output += '<td><select name="' + meta_name_subscription + '[][name][]" multiple data-placeholder="' + pms_woo_admin_vars.strings['Select...'] + '" class="widefat pms-chosen pms-select-name">';
        for( var key in pmsWooProducts )
            output += '<option value="' + key + '">' + pmsWooProducts[key] + '</option>';
        output += '</select></td>';

        // Add "Type" row cell
        output += '<td>';
        output += '<select name="' + meta_name_subscription + '[][type]" class="widefat pms-select-discount-type">';
        output += '<option value="percent" >' + pms_woo_admin_vars.strings['Percent'] + ' (%)</option>';
        output += '<option value="fixed" >' + pms_woo_admin_vars.strings['Fixed'] + ' (' + pms_woo_admin_vars.currency_symbol + ')</option>';
        output += '</select>';
        output += '</td>';

        // Add "Amount" row cell
        output += '<td>';
        output += '<input type="text" name="' + meta_name_subscription + '[][amount]" value="" class="widefat pms-input-discount-amount">';
        output += '</td>';

        // Add "Status" row cell
        output += '<td>';
        output += '<select name="' + meta_name_subscription + '[][status]" class="widefat pms-select-discount-status">';
        output += '<option value="active">' + pms_woo_admin_vars.strings['Active'] + '</option>';
        output += '<option value="inactive">' + pms_woo_admin_vars.strings['Inactive'] + '</option>';
        output += '</select>';
        output += '</td>';

        // Add close link
        output += '<td>';
        output += '<a href="#" class="pms-woo-subscription-remove-product-discount" title="' + pms_woo_admin_vars.strings['Remove this discount'] + '"><span class="dashicons dashicons-no"></span></a>';
        output += '</td>';

        output += '</tr>';

        // Append output and recalculate row indexes
        $wooSubscriptionProductDiscounts.append( output );
        $('#pms-woo-subscription-product-discounts').trigger('chosen-init');
        calculateRowIndexesDiscounts( $wooSubscriptionProductDiscounts );

    });


    /**
     * Load options based on "Discount for" selection (Products or Product Categories)
     */
    $('#pms-woo-subscription-product-discounts').on('change', '.pms-select-discount-for', function(){

        var contents = '';
        $(this).closest('tr').find('.pms-chosen').html('');

        if ( $(this).val() == 'product-categories') {

            for( var key in pmsWooProductCategories )
                contents += '<option value="' + key + '">' + pmsWooProductCategories[key] + '</option>';
        }
        else {

            for ( var key in pmsWooProducts )
                contents += '<option value="' + key + ' ">' + pmsWooProducts[key] + '</option>';
        }

        $(this).closest('tr').find('.pms-chosen').html(contents);
        $(this).closest('tr').find('.pms-chosen').trigger('chosen:updated');

    });


    /**
     * Add indexes to all discount codes so we save each discount data in an array based on this index
     */
    function calculateRowIndexesDiscounts( $wooProductDiscount ) {
        $wooProductDiscount.children('tr').each( function( index ) {
            $(this).attr( 'data-index', index );

            $(this).find('select').each( function() {
                var element_attr_name = $(this).attr('name');

                var element_attr_name_beg = element_attr_name.substr( 0, element_attr_name.indexOf('[') + 1 );
                var element_attr_name_end = element_attr_name.substr( element_attr_name.indexOf(']'), element_attr_name.length - 1 );

                $(this).attr( 'name', element_attr_name_beg + index + element_attr_name_end );
            });

            $(this).find('input.pms-input-discount-amount').each( function() {

                var element_attr_name = $(this).attr('name');

                var element_attr_name_beg = element_attr_name.substr( 0, element_attr_name.indexOf('[') + 1 );
                var element_attr_name_end = element_attr_name.substr( element_attr_name.indexOf(']'), element_attr_name.length - 1 );

                $(this).attr( 'name', element_attr_name_beg + index + element_attr_name_end );
            });

        });
    }


    /**
     * Initialise chosen
     */

    $('#pms-woo-subscription-product-discounts .pms-chosen').chosen();

    $('#pms-woo-subscription-product-discounts').on('chosen-init', function() {

        if( $.fn.chosen != undefined ) {
            $('.pms-chosen').chosen();
        }

    });

    /**
     * Remove discount
     *
     * @param element the remove button
     * @param discountRow class name of the discount row
     * @param discountTable table id of the discount table
     */
    function pmsWooRemoveDiscounts(element, discountRow, discountTable){

        if (window.confirm(pms_woo_admin_vars.strings['Are you sure you want to remove this discount?'])) {

            $(element).closest('tr.' + discountRow).remove();

            if ( $('.' + discountRow).length == 0 ) {

                var message;

                message =  '<tr class="pms-woo-no-discounts-message">';
                message += '<td colspan="6">' + pms_woo_admin_vars.strings['No discounts yet'] + '</td>';
                message += '</tr>';

                $('#' + discountTable + ' tbody').append( message );
            }
        }
    }


    /**
     * Remove product discount ( Membership Discounts meta-box - located under Products in WooCommerce )
     */
    $('#pms-woo-product-membership-discounts').on('click', '.pms-woo-product-remove-membership-discount', function(e) {
        e.preventDefault();

        pmsWooRemoveDiscounts(this, 'pms-woo-product-membership-discount', 'pms-woo-product-membership-discounts' );

    });


    /**
     * Remove Subscription plan discount ( Products Discount meta-box - located under Subscription Plan details )
     */
    $('#pms-woo-subscription-product-discounts').on('click', '.pms-woo-subscription-remove-product-discount', function(e) {
        e.preventDefault();

        pmsWooRemoveDiscounts(this, 'pms-woo-subscription-product-discount', 'pms-woo-subscription-product-discounts' );

    });


});

