<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function pms_so_widgets_folder( $folders ) {
    $folders[] = plugin_dir_path(__FILE__) . 'widgets/';

    return $folders;
}
add_filter( 'siteorigin_widgets_widget_folders', 'pms_so_widgets_folder' );
