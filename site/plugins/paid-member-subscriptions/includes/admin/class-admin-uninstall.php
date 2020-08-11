<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends core PMS_Submenu_Page base class to create and add an uninstall page where the admin can 
 * uninstall everything PMS related from the DB
 *
 */
Class PMS_Submenu_Page_Uninstall extends PMS_Submenu_Page {


    /*
     * Method that initializes the class
     *
     */
    public function init() {

        // Hook the output method to the parent's class action for output instead of overwriting the
        // output method
        add_action( 'pms_output_content_submenu_page_' . $this->menu_slug, array( $this, 'output' ) );

        // Hook the validation of the data early on the init
        add_action( 'admin_init', array( $this, 'process_data' ) );

    }


    /*
     * Validates the nonces and proceedes with the Uninstall
     *
     */
    public function process_data() {

    	if( !empty( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'pms_uninstall_page_nonce' ) && !empty( $_POST['pmstkn'] ) && wp_verify_nonce( $_POST['pmstkn'], 'pms_uninstall_nonce' ) ) {

    		// The user must be an admin
    		if( !current_user_can( 'manage_options' ) )
    			return;

    		// The admin must enter the word REMOVE in order for the uninstall to be handled
    		if( empty( $_POST['pms-confirm-uninstall'] ) || $_POST['pms-confirm-uninstall'] != 'REMOVE' )
    			return;

    		// Inluce the Uninstaller and handle the install process
    		if( file_exists( PMS_PLUGIN_DIR_PATH . 'includes/class-uninstaller.php' ) ) {
    			include_once PMS_PLUGIN_DIR_PATH . 'includes/class-uninstaller.php';

	    		$uninstaller = new PMS_Uninstaller( $_POST['pmstkn'] );

	    		// Run uninstaller and redirect to the plugins page
	    		if( $uninstaller->run() )
					wp_redirect( admin_url( 'plugins.php' ) );

    		}

    	}

    }


    /*
     * Method to output content in the custom page
     *
     */
    public function output() {

        if( empty( $_GET['_wpnonce'] ) || !wp_verify_nonce( $_GET['_wpnonce'], 'pms_uninstall_page_nonce' ) )
        	return;

        include_once 'views/view-page-uninstall.php';

    }


}

$pms_submenu_page_uninstall = new PMS_Submenu_Page_Uninstall( null, __( 'Uninstall', 'paid-member-subscriptions' ), __( 'Uninstall', 'paid-member-subscriptions' ), 'manage_options', 'pms-uninstall-page', 9);
$pms_submenu_page_uninstall->init();