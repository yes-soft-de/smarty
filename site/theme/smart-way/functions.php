<?php

// Include Mobile Detect Third Party File
require get_template_directory() . '/inc/vendor/Mobile_Detect.php';
// Include Cleanup file for remove wordpress version form our css and js file and other
require get_template_directory() . '/inc/cleanup.php';
// Include Our Function admin file
require get_template_directory() . '/inc/function-admin.php';
// Include Enqueue File That Handle
require get_template_directory() . '/inc/enqueue.php';
require get_template_directory() . '/inc/theme-support.php';
require get_template_directory() . '/inc/custom-post-type.php';
// Include NavWalker Class For Bootstrap Navigation Menu
require_once get_template_directory() . '/inc/wp-bootstrap-navwalker.php';
// Include Ajax File
require_once get_template_directory() . '/inc/ajax.php';
// Include Shortcode File
require_once get_template_directory() . '/inc/shortcode.php';
// Include Custom Widget File
require_once get_template_directory() . '/inc/widgets.php';
