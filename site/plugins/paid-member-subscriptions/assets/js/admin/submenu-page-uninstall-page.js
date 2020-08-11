jQuery( function($) {

	// Close the Uninstall confirmation thickbox
	$(document).on( 'click', '#pms-confirm-uninstall-cancel', function(e) {
		e.preventDefault();

		$('#TB_closeWindowButton').trigger('click');
	});

	// Enables/disables the Uninstall button from the thickbox
	$(document).on( 'keyup', 'input[name=pms-confirm-uninstall]', function() {

		$this 	= $(this);
		$submit	= $this.siblings('.pms-uninstall-thickbox-footer').find('input[type=submit]');

		if( $(this).val() == 'REMOVE' )
			$submit.attr('disabled', false );
		else
			$submit.attr('disabled', true );

	});

});