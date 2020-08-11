<?php
/*
	===================
		ADMIN PAGE
	===================
*/

function sunset_add_admin_page() {
	// Use Hook From Wordpress to activate the administration page
	add_menu_page(
		__('Sunset Theme Options'),
		__('Theme Options'),
		'manage_options',
		'talal_sunset_theme',
		'sunset_theme_support_page',
		'',
		110 );

	// Generate Sunset Theme Support Submenu Page
	add_submenu_page(
		'talal_sunset_theme',
		__('Sunset Theme Options'),
		__('Theme Options'),
		'manage_options',
		'talal_sunset_theme',
		'sunset_theme_support_page');

	// Generate Sunset Theme Contact Submenu Page
	add_submenu_page(
		'talal_sunset_theme',
		__('Sunset Contact Form'),
		__('Contact Form'),
		'manage_options',
		'talal_sunset_theme_contact',
		'sunset_contact_form_page');

	// Generate Sunset Css Options Another Sub Menu Page
	add_submenu_page(
		'talal_sunset_theme',
		__('Sunset Css Options'),
		__('Custom Css'),
		'manage_options',
		'talal_sunset_css',
		'sunset_theme_settings_pages');

	/** Activate Custom Settings
	* We Use this inside sunset_add_admin_page function as a safety way and to prevent
	* the system to execute it and generate it if we don't actually create the page
	*/
	add_action( 'admin_init', 'sunset_custom_settings' );
}
add_action( 'admin_menu', 'sunset_add_admin_page' );


// function to generate our admin html page
function sunset_theme_support_page() {
	// Generate of Our Admin Page
	require_once get_template_directory() . '/inc/template/sunset-theme-support.php';
}

// function to generate our admin html page
function sunset_contact_form_page() {
	// Generate of Our Admin Page
	require_once get_template_directory() . '/inc/template/sunset-contact-form.php';
}

function sunset_theme_settings_pages() {
	// Generate of Our Admin Sub Pages
	echo '<h1>Sunset Custom Css</h1>';
}

// function related with the above admin_init hook
function sunset_custom_settings() {
	/*** Start Theme Support Options ***/
	register_setting( 'sunset-theme-support', 'post_formats' );
	register_setting( 'sunset-theme-support', 'custom_header' );
	register_setting( 'sunset-theme-support', 'custom_background' );

	add_settings_section( 'sunset-theme-options', 'Theme Options', 'sunset_theme_options', 'talal_sunset_theme' );

	add_settings_field( 'post-formats', __('Post Formats'), 'sunset_post_formats', 'talal_sunset_theme', 'sunset-theme-options' );
	add_settings_field( 'custom-header', __('Custom Header'), 'sunset_custom_header', 'talal_sunset_theme', 'sunset-theme-options' );
	add_settings_field( 'custom-background', __('Custom background'), 'sunset_custom_background', 'talal_sunset_theme', 'sunset-theme-options' );

	/*** Start Theme Contact Form Options ***/
	register_setting( 'sunset-contact-options', 'activate_contact' );

	add_settings_section( 'sunset-contact-section', __('Contact Form'), 'sunset_contact_section', 'talal_sunset_theme_contact' );

	add_settings_field( 'activate-form', __('Activate Contact Form'), 'sunset_activate_contact', 'talal_sunset_theme_contact', 'sunset-contact-section' );

}

/***** Start Sidebar Options Callback Function *****/

/***** End Sidebar Options Callback Function *****/

/***** Start Theme Support Options Callback Function *****/
function sunset_theme_options() {
	_e('Activate and Deactivate Specific Theme Support Options');
}

function sunset_post_formats() {
	$options = get_option( 'post_formats' );
	// The Format That Builtin Wordpress
	$formats = array( 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' );
	$output = '';
	foreach ( $formats as $format ) {
		$checked = ( @$options[$format] == 1 ? 'checked' : '' );
		$output .= '<label><input type="checkbox" id="' . $format . '" name="post_formats[' . $format . ']" value="1" ' . $checked . '/>' . $format . '</label><br />';
	}
	echo $output;
}

function sunset_custom_header() {
	$header = get_option( 'custom_header' );
	$checked = ( @$header == 1 ? 'checked' : '' );
	echo '<label><input type="checkbox" id="custom_header" name="custom_header" value="1" ' . $checked . '/>'.__('Activate The Custom Header').'</label><br />';
}

function sunset_custom_background() {
	$background = get_option( 'custom_background' );
	$checked = ( @$background == 1 ? 'checked' : '' );
	echo '<label><input type="checkbox" id="custom_background" name="custom_background" value="1" ' . $checked . '/>'.__('Activate The Custom Background').'</label><br />';
}
/***** End Theme Support Options Callback Function *****/

/***** Start Theme Contact Form Options Callback Function *****/
function sunset_contact_section() {
	_e('Activate and Deactivate The Build-in Contact Form');
}

function sunset_activate_contact() {
	$contact = get_option( 'activate_contact' );
	$checked = ( @$contact == 1 ? 'checked' : '' );
	echo '<label><input type="checkbox" id="activate_contact" name="activate_contact" value="1" ' . $checked . '/></label><br />';
}
/***** End Theme Contact Form Options Callback Function *****/

