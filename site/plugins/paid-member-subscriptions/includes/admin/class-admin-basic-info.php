<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends core PMS_Submenu_Page base class to create and add a basic information page
 *
 * The basic information page will contain a quick walk through the plugin features
 *
 */
Class PMS_Submenu_Page_Basic_Info extends PMS_Submenu_Page {


    /*
     * Method that initializes the class
     *
     */
    public function init() {

        // Hook the output method to the parent's class action for output instead of overwriting the
        // output method
        add_action( 'pms_output_content_submenu_page_' . $this->menu_slug, array( $this, 'output' ) );

    }


    /*
     * Method to output content in the custom page
     *
     */
    public function output() {

        // Set options
        $this->options = get_option( $this->settings_slug, array() );

        include_once 'views/view-page-basic-info.php';

    }


}

$pms_submenu_page_basic_info = new PMS_Submenu_Page_Basic_Info( 'paid-member-subscriptions', __( 'Basic Information', 'paid-member-subscriptions' ), __( 'Basic Information', 'paid-member-subscriptions' ), 'manage_options', 'pms-basic-info-page', 9);
$pms_submenu_page_basic_info->init();