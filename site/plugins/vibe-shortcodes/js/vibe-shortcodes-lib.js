jQuery(document).ready(function($) {

	$(".vibe-tabs").tabs({ fx: { opacity: 'show' } });
	
	$(".vibe-toggle").each( function () {
		if($(this).attr('data-id') == 'closed') {
			$(this).accordion({ header: '.vibe-toggle-title', collapsible: true, active: false  });
		} else {
			$(this).accordion({ header: '.vibe-toggle-title', collapsible: true});
		}
	});
	
	
});