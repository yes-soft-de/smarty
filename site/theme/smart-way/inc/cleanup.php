<?php
/*
 * Description: this file to remove the generator wordpress file from our page to prevent display wordpress version
 * to the user which could be weakness point
	=========================================
		REMOVE GENERATOR VERSION NUMBER
	=========================================
*/

// function to remove the wordpress version from all js and css that wordpress created in our site
function sunset_remove_wp_version_strings( $src ) {
	// $wp_version: use to fetch and store our actual wordpress version
	global $wp_version;
	/*
	 *  first catch the url from $src param using parse_url and fetch the version from it using PHP_URL_QUERY
	 * PHP_URL_QUERY: is the version from url ex: (https://fonts.googleapis.com/css?family=Raleway%3A200%2C300%2C500&ver=4.9.13) the PHP_URL_QUERY = ver=4.9.13
	 * third : convert the prev url to and variable as ($family, $ver ....) and store the result in $query variable
	*/
	parse_str( parse_url( $src, PHP_URL_QUERY ), $query );
	// check if the variable $ver is exists and equal to our wordpress version
	if ( ! empty( $query['ver'] ) && $query['ver'] === $wp_version ) {
		// remove the variable from our $query
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
// call all the scripts in the footer
add_filter( 'script_loader_src', 'sunset_remove_wp_version_strings' );
// call all the styles in the header
add_filter( 'style_loader_src', 'sunset_remove_wp_version_strings' );

// function to remove the meta tag ( <meta name="generator" content="WordPress 4.9.13"> ) from the header
function sunset_remove_meta_version() {
	return '';
}
add_filter( 'the_generator', 'sunset_remove_meta_version' );
