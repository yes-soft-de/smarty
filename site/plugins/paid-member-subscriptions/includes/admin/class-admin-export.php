<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends core PMS_Submenu_Page base class to create and add a basic information page
 *
 * The basic information page will contain a quick walk through the plugin features
 *
 */
Class PMS_Submenu_Page_Export extends PMS_Submenu_Page {


    /*
     * Method that initializes the class
     *
     */
    public function init() {

        // Enqueue admin scripts
        add_action( 'pms_submenu_page_enqueue_admin_scripts_' . $this->menu_slug, array( $this, 'admin_scripts' ) );

        // Hook the output method to the parent's class action for output instead of overwriting the
        // output method
        add_action( 'pms_output_content_submenu_page_' . $this->menu_slug, array( $this, 'output' ) );
        add_action( 'wp_ajax_pms_do_ajax_export',  array( $this, 'pms_do_ajax_export' ) );
        add_action( 'init', array( $this, 'pms_process_batch_export_download' ) );

    }

    /*
     * Process Ajax requests from pms-export-page
     *
     */
    public function pms_do_ajax_export(){

        require_once PMS_PLUGIN_DIR_PATH . 'includes/admin/export/class-export.php';
        require_once PMS_PLUGIN_DIR_PATH . 'includes/admin/export/class-batch-export.php';
        require_once PMS_PLUGIN_DIR_PATH . 'includes/admin/export/class-batch-export-members.php';
        require_once PMS_PLUGIN_DIR_PATH . 'includes/admin/export/class-batch-export-payments.php';

        parse_str( $_POST['form'], $form );

        $_REQUEST = $form = (array) $form;


        if( ! wp_verify_nonce( $_REQUEST['pms_ajax_export'], 'pms_ajax_export' ) ) {
            die( '-2' );
        }

        $step     = absint( $_POST['step'] );
        $class    = sanitize_text_field( $form['pms-export-class'] );
        $export   = new $class( $step );

        if( ! $export->can_export() ) {
            die( '-1' );
        }

        if ( ! $export->is_writable ) {
            echo json_encode( array( 'error' => true, 'message' => __( 'Export location or file not writable', 'paid-member-subscriptions' ) ) ); exit;
        }

        $export->set_properties( $_REQUEST );

        // Added in 2.5 to allow a bulk processor to pre-fetch some data to speed up the remaining steps and cache data
        $export->pre_fetch();

        $ret = $export->process_step( $step );

        $percentage = $export->get_percentage_complete();

        if( $ret ) {

            $step += 1;
            echo json_encode( array( 'step' => $step, 'percentage' => $percentage ) ); exit;

        } elseif ( true === $export->is_empty ) {

            echo json_encode( array( 'error' => true, 'message' => __( 'No data found for export parameters', 'paid-member-subscriptions' ) ) ); exit;

        } elseif ( true === $export->done && true === $export->is_void ) {

            $message = ! empty( $export->message ) ? $export->message : __( 'Batch Processing Complete', 'paid-member-subscriptions' );
            echo json_encode( array( 'success' => true, 'message' => $message ) ); exit;

        } else {

            $args = array_merge( $_REQUEST, array(
                'step'       => $step,
                'class'      => $class,
                'nonce'      => wp_create_nonce( 'pms-batch-export' ),
                'pms_action' => 'download_batch_export',
            ) );

            $download_url = add_query_arg( $args, admin_url() );

            echo json_encode( array( 'step' => 'done', 'url' => $download_url ) ); exit;

        }
    }

    public function pms_process_batch_export_download() {

        $key = ! empty( $_GET['pms_action'] ) && $_GET['pms_action'] == 'download_batch_export' ? sanitize_key( $_GET['pms_action'] ) : false;

        if ( empty( $key ) ) {
            return;
        }

        if( ! wp_verify_nonce( $_REQUEST['nonce'], 'pms-batch-export' ) ) {
            wp_die( __( 'Nonce verification failed', 'paid-member-subscriptions' ), __( 'Error', 'paid-member-subscriptions' ), array( 'response' => 403 ) );
        }

        require_once PMS_PLUGIN_DIR_PATH . 'includes/admin/export/class-export.php';
        require_once PMS_PLUGIN_DIR_PATH . 'includes/admin/export/class-batch-export.php';
        require_once PMS_PLUGIN_DIR_PATH . 'includes/admin/export/class-batch-export-members.php';
        require_once PMS_PLUGIN_DIR_PATH . 'includes/admin/export/class-batch-export-payments.php';

        $export = new $_REQUEST['class'];
        $export->export();

    }



    /*
     * Method to output content in the custom page
     *
     */
    public function output() {

        // Set options
        $this->options = get_option( $this->settings_slug, array() );
        $active_tab = 'pms-export-page';
        include_once 'views/view-page-export.php';

    }

    /**
     * Get all the usermeta names that are possible in the database.
     *
     * @since 1.7.6
     * @return array
     */
    static function get_all_user_meta_keys() {
        global $wpdb;
        $select = "SELECT distinct $wpdb->usermeta.meta_key FROM $wpdb->usermeta";
        $usermeta = $wpdb->get_results($select, ARRAY_A);
        return $usermeta;
    }


    /*
     * Method to enqueue admin scripts
     *
     */
    public function admin_scripts() {
        global $wp_scripts;

        // Try to detect if chosen has already been loaded
        $found_chosen = false;

        foreach( $wp_scripts as $wp_script ) {
            if( !empty( $wp_script['src'] ) && strpos($wp_script['src'], 'chosen') !== false )
                $found_chosen = true;
        }

        if( !$found_chosen ) {
            wp_enqueue_script( 'pms-chosen', PMS_PLUGIN_DIR_URL . 'assets/libs/chosen/chosen.jquery.min.js', array( 'jquery' ), PMS_VERSION );
            wp_enqueue_style( 'pms-chosen', PMS_PLUGIN_DIR_URL . 'assets/libs/chosen/chosen.css', array(), PMS_VERSION );
        }

    }

}

$pms_submenu_page_basic_info = new PMS_Submenu_Page_Export( 'paid-member-subscriptions', __( 'Export Data', 'paid-member-subscriptions' ), __( 'Export Data', 'paid-member-subscriptions' ), 'manage_options', 'pms-export-page', 9);
$pms_submenu_page_basic_info->init();
