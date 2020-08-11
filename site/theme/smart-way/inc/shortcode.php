<?php
/*
 * Description: ShortCode ex( [shortCodeKeywordHere] ) is that code we write in admin post editor which we use it to insert post content
	======================
		SHORT CODE OPTIONS
	======================
*/

/*
 * add_shortcode: is the hook we use to activate our short code
 * [tooltip][/tooltip]: it's for Example the shortcode for bootstrap 'tooltip'
 * - always remember to use only lowercase and not use camel capital[toolTip] or all uppercase[TOOLTIP] and avoid to use
 * dash[tool-tip] or underscore[tool_tip]
 *  but if we use these (uppercase, dash, ...) most likely our shortcode could not work properly
 * So if we want to create <button type="button" class="btn btn-default" data-toggle="tooltip" data-placement="left" title="Tooltip on left">Tooltip on left</button>
 * We need 3 arguments: (placement, title, buttonContent)
 */
function sunset_shortcode_tooltip( $attributes, $content = null ) {
	// The Shortcode we will create is : [tooltip placement="top" title="This is the title"]This is the content[/tooltip]
/*
 * shortcode_atts: function to access the attributes and assigned these attribute to some specific variable that we defined
 */
	$attrs = shortcode_atts(
		array(
			'placement'   => 'top',
			'title'       => ''
		),
		$attributes,        // the array that contain our variable and to check inside it for these value we insert
		'tooltip' // actual String for our custom shortcode
	);
	$title = ( $attrs['title'] == '' ? $content : $attrs['title'] );
	/*
	 * Return Html, Always use return and never use echo because we can't echo something inside the content
	 * otherwise we're going to destroy any generation of the content in Wordpress
	 */
	return '<span class="sunset-tooltip" data-toggle="tooltip" data-placement="' . $attrs['placement'] . '" title="' . $title . '">' . $content . '</span>';
}

add_shortcode( 'tooltip', 'sunset_shortcode_tooltip' );



function sunset_shortcode_popover( $attributes, $content = null ) {
	/*
	 * The Button That We Want To Create a Popover Like It :
	 * <button type="button" class="btn btn-secondary" data-container="body" data-toggle="popover" data-placement="top" data-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus.">
	      Popover on top
			</button>
	 * The Result:  [popover title="popover title" placement="top" content="" trigger="click"][/popover]
	 */
	$attrs = shortcode_atts(
		array (
			'placement' => 'top',
			'title'     => '',
			'trigger'   => 'click',
			'content'   => ''
		),
		$attributes,
		'popover'
	);
	return '<span class="sunset-popover" data-toggle="popover" data-placement="' . $attrs['placement'] . '" data-content="' . $attrs['content'] . '" data-trigger="' . $attrs['trigger'] . '" title="' . $attrs['title'] . '">' . $content . '</span>';
}
add_shortcode( 'popover', 'sunset_shortcode_popover' );



	function sunset_shortcode_contact_form( $attributes, $content = null ) {
		// The Shortcode we will create is : [contact_form]
		$attrs = shortcode_atts(
			array(),
			$attributes,            // the array that contain our variable and to check inside it for these value we insert
			'contact_form' // actual String for our custom shortcode
		);

		/*
		 * to prevent php from include the file before everything we use the output buffering ob_start to prevent any output sent from the script
		 * to be output immediately
		 * ob_get_clean : it return the output buffer content and delete the output buffer content
		 */
		ob_start();
		include 'template/contact-form.php';
		return ob_get_clean();

	}

	add_shortcode( 'contact_form', 'sunset_shortcode_contact_form' );
