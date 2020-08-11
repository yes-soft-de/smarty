<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class that adds notices
 *
 * @since v.1.0
 *
 * @return void
 */
class PMS_Add_General_Notices{

    /**
     * Notification id
     *
     * @access public
     * @var string
     */
    public $notificationId = '';

    /**
     * Notification message
     *
     * @access public
     * @var string
     */
    public $notificationMessage = '';

    /**
     * Notification class
     *
     * @access public
     * @var string
     */
    public $notificationClass = '';

    /**
     * Ignores the dismiss meta
     *
     * @access public
     * @var bool
     */
    public $forceShow = false;

    /**
     * Start date
     *
     * @access public
     * @var string
     */
    public $startDate = '';

    /**
     * End date
     *
     * @access public
     * @var string
     */
    public $endDate = '';

    function __construct( $notificationId, $notificationMessage, $notificationClass = 'updated' , $startDate = '', $endDate = '', $forceShow = false ){
        $this->notificationId = $notificationId;
        $this->notificationMessage = $notificationMessage;
        $this->notificationClass = $notificationClass;
        $this->forceShow = $forceShow;

        if( !empty( $startDate ) && time() < strtotime( $startDate ) )
            return;

        if( !empty( $endDate ) && time() > strtotime( $endDate ) )
            return;

        add_action( 'admin_notices', array( $this, 'add_admin_notice' ) );
        add_action( 'admin_init', array( $this, 'dismiss_notification' ) );
        add_action( 'admin_init', array( $this, 'remove_other_plugin_notices' ), 1001 );
    }


    // Display a notice that can be dismissed
    function add_admin_notice() {
        global $current_user ;
        global $pagenow;

        $user_id = $current_user->ID;
        do_action( $this->notificationId.'_before_notification_displayed', $current_user, $pagenow );

        if ( current_user_can( 'manage_options' ) ){
            // Check that the user hasn't already clicked to ignore the message
            if ( ! get_user_meta($user_id, $this->notificationId.'_dismiss_notification' ) || $this->forceShow) {
                echo $finalMessage = apply_filters($this->notificationId.'_notification_message','<div id="'.$this->notificationId.'" class="notice '. $this->notificationClass .' notice-'. $this->notificationClass .'" ><p>'.$this->notificationMessage.'</p></div>', $this->notificationMessage);
            }
            do_action( $this->notificationId.'_notification_displayed', $current_user, $pagenow );
        }
        do_action( $this->notificationId.'_after_notification_displayed', $current_user, $pagenow );
    }

    function dismiss_notification() {
        global $current_user;

        $user_id = $current_user->ID;

        do_action( $this->notificationId.'_before_notification_dismissed', $current_user );

        // If user clicks to ignore the notice, add that to their user meta
        if ( isset( $_GET[$this->notificationId.'_dismiss_notification']) && '0' == $_GET[$this->notificationId.'_dismiss_notification'] )
            add_user_meta( $user_id, $this->notificationId.'_dismiss_notification', 'true', true );

        do_action( $this->notificationId.'_after_notification_dismissed', $current_user );
    }

    protected function is_plugin_page() {

        if( ! empty( $_GET['page'] ) && false !== strpos( $_GET['page'], 'pms-' ) )
            return true;

        if( ! empty( $_GET['post_type'] ) && false !== strpos( $_GET['post_type'], 'pms-' ) )
            return true;

        if( ! empty( $_GET['post'] ) && false !== strpos( get_post_type( (int)$_GET['post'] ), 'pms-' ) )
            return true;

        return false;

    }

    function remove_other_plugin_notices() {

        //remove all notifications from start page
        if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'pms-basic-info-page' ) ) {
            remove_all_actions('admin_notices');
        }

        /* remove all other plugin notifications except our own from the rest of the PB pages */
        if( $this->is_plugin_page() ) {

            global $wp_filter;

            if (!empty($wp_filter['admin_notices'])) {

                if (!empty($wp_filter['admin_notices']->callbacks)) {

                    foreach ($wp_filter['admin_notices']->callbacks as $priority => $callbacks_level) {

                        if (!empty($callbacks_level)) {
                            foreach ($callbacks_level as $key => $callback) {

                                if( is_array( $callback['function'] ) ){
                                    if( is_object($callback['function'][0])) {//object here
                                        if (strpos(get_class($callback['function'][0]), 'PMS_') !== 0 && strpos(get_class($callback['function'][0]), 'WPPB_') !== 0 && strpos(get_class($callback['function'][0]), 'TRP_') !== 0 && strpos(get_class($callback['function'][0]), 'WCK_') !== 0) {
                                            unset($wp_filter['admin_notices']->callbacks[$priority][$key]);//unset everything that doesn't come from our plugins
                                        }
                                    }
                                } else if( is_string( $callback['function'] ) ) {//it should be a function name
                                    if (strpos($callback['function'], 'pms_') !== 0 && strpos($callback['function'], 'wppb_') !== 0 && strpos($callback['function'], 'trp_') !== 0 && strpos($callback['function'], 'wck_') !== 0) {
                                        unset($wp_filter['admin_notices']->callbacks[$priority][$key]);//unset everything that doesn't come from our plugins
                                    }
                                }

                            }
                        }

                    }

                }

            }
        }

    }

}
