<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class PMS_Submenu_Page {

    /**
     * Parent page slug
     *
     * @access public
     * @var string
     */
    public $parent_slug;

    /**
     * Page title
     *
     * @access public
     * @var string
     */
    public $page_title;

    /**
     * Menu title
     *
     * @access public
     * @var string
     */
    public $menu_title;

    /**
     * User capability that will be able to view/edit this page
     *
     * @access public
     * @var string
     */
    public $capability;

    /**
     * Hook priority
     *
     * @access public
     * @var int
     */
    public $priority;

    /**
     * Options/settings page slug
     *
     * @access public
     * @var string
     */
    public $settings_slug;

    /**
	 * Option values
	 *
     * Holds the settings option values, used for options/settings pages
     *
     * @access protected
     * @var string
     */
    protected $options;


    public function __construct( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $priority = 10, $settings_slug = '' ) {

        $this->parent_slug   = $parent_slug;
        $this->page_title    = $page_title;
        $this->menu_title    = $menu_title;
        $this->capability    = $capability;
        $this->menu_slug     = $menu_slug;
        $this->priority      = $priority;
        $this->settings_slug = $settings_slug;

        add_action( 'admin_menu', array( $this, 'add_submenu_page' ), $this->priority );

        add_action( 'init', array( $this, 'catch_admin_notice' ) );

        add_action( 'admin_notices', array( $this, 'print_admin_notices_messages' ) );

        // Enqueue scripts
        if( is_admin() )
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        // Register settings if they exist
        if( ! empty( $this->settings_slug ) )
            add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );

    }


	/*
	 * Adds the sub-menu page
	 *
	 */
	public function add_submenu_page() {

		$page_hook = add_submenu_page( $this->parent_slug, $this->page_title, $this->menu_title, apply_filters( 'pms_submenu_page_capability', $this->capability, $this->menu_slug ), $this->menu_slug, array( $this, 'output_content' ) );

		// Action that loads Screen Options
		add_action( 'load-' . $page_hook, array( $this, 'add_screen_options' ) );

	}


	/*
     * Public function that has to be replaced by child class
     *
     */
	public function add_screen_options() {

		return;

	}


	/*
	 * Public function used to save screen options values
	 *
	 */
	public function set_screen_option( $status, $option, $value ) {

		return $value;

	}


    /*
     * Output callback for the page
     * Can be overwritten by subclass
     *
     */
    public function output_content() {

        do_action( 'pms_output_content_submenu_page_' . $this->menu_slug );

    }


    /*
     * Registers option page settings on admin init
     *
     */
    public function register_settings() {

        register_setting( $this->settings_slug, $this->settings_slug, array( $this, 'sanitize_settings' ) );

    }


    /*
     * Callback to sanitize the settings before saving
     */
    public function sanitize_settings( $options ) {
        return $options;
    }


    /**
     * Returns a message by the provided code.
     *
     * Should be overwritten by the extending class
     *
     * @param int $code
     *
     * @return string
     *
     */
    protected function get_message_by_code( $code = 0 ) {

        return '';

    }


    /**
     * Catches messages sent through the URL
     *
     */
    public function catch_admin_notice() {

        // Exit if we're not on a subpage
        if( empty( $_GET['page'] ) )
            return;

        // Return if the current page is not the one accessed
        if( $_GET['page'] != $this->menu_slug )
            return;

        // Return if no message exists
        if( empty( $_GET['message'] ) )
            return;

        $message_code = (int)$_GET['message'];

        $type = '';

        if( isset( $_GET['error'] ) )
            $type = 'error';
        elseif( isset( $_GET['updated'] ) )
            $type = 'updated';

        $message = $this->get_message_by_code( $message_code );

        if( ! empty( $message ) )
            $this->add_admin_notice( $message, $type );

    }


    /*
     * Method to easily add admin notices
     *
     */
    public function add_admin_notice( $message = '', $class = 'update-nag' ) {

        if( !empty( $message ) ) {
            $this->admin_notices[] = array(
                'message'   => $message,
                'type'      => $class
            );
        }

    }


    /**
     * Method that allows insertion of multiple error messages at once
     *
     */
    public function add_admin_notices( $notices = array() ) {

        if( empty( $notices ) )
            return;

        foreach( $notices as $notice ) {
            $notice_type = key( $notice );
            $notice_message = $notice[ $notice_type ];

            $this->add_admin_notice( $notice_message, $notice_type );
        }

    }


    /*
     * Method that returns the admin notices, optional by their type
     *
     * @param string $notice_type   - The type of the notice
     *
     * @return array
     *
     */
    public function get_admin_notices( $notice_type = '' ) {

        if( !isset( $this->admin_notices ) )
            return array();

        if( empty( $notice_type ) )
            return $this->admin_notices;

        $type_notices = array();

        foreach( $this->admin_notices as $notice ) {
            if( $notice['type'] == trim( $notice_type ) ) {
                $type_notices[] = $notice;
            }
        }

        return $type_notices;

    }


    /*
     * Method that checks if there are admin notices, optional by their type
     *
     * @param string $notice_type   - The type of the notice
     *
     * @return bool
     *
     */
    public function has_admin_notice( $notice_type = '' ) {

        if( !isset( $this->admin_notices ) )
            return false;

        if( empty( $notice_type ) )
            return true;

        foreach( $this->admin_notices as $notice ) {

            if( $notice['type'] == trim( $notice_type ) )
                return true;

        }

        return false;

    }


    /**
     * Method to display the admin notices to the user
     *
     */
    public function print_admin_notices_messages() {

        if( !isset( $this->admin_notices ) )
            return;

        foreach( $this->admin_notices as $notice ) {
            echo '<div class="' . $notice['type'] . ' pms-admin-notice">';
                echo '<p>' . $notice['message'] . '</p>';
            echo '</div>';
        }

    }


    /*
     * Method to enqueue scripts on the admin side
     *
     */
    public function enqueue_admin_scripts( $hook ) {

        if( strpos( $hook, $this->menu_slug ) === false )
            return;

        // Sanitize the filename by removing the prefix and changing the underscores to dashes

        // In case we have dashes in the post type slug, replace them with underscores
        $js_file_name = str_replace( '-', '_', $this->menu_slug );
        $js_file_name = 'submenu-page-' . str_replace( '_', '-', str_replace( 'pms_', '', $js_file_name ) ) . '.js';

        do_action( 'pms_submenu_page_enqueue_admin_scripts_before_' . $this->menu_slug, $this->menu_slug );

        // If the file exists where it should be, enqueue it
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'assets/js/admin/' . $js_file_name ) )
            wp_enqueue_script( $this->menu_slug . '-js', PMS_PLUGIN_DIR_URL . 'assets/js/admin/' . $js_file_name, array( 'jquery', 'jquery-ui-core' ), PMS_VERSION );

        do_action( 'pms_submenu_page_enqueue_admin_scripts_' . $this->menu_slug, $this->menu_slug );

    }

}
